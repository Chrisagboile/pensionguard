<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PensionAccountGuard\Models\PensionGuardSessionAvailability;
use App\Modules\PensionAccountGuard\Models\PensionGuardSessionBooking;
use App\Modules\PensionAccountGuard\Services\PensionGuardSessionBookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Stripe\StripeClient;
use Throwable;

class PensionGuardSessionBookingController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('pension_guard')->user();

        $bookings = $user->sessionBookings()
            ->with('availability')
            ->latest('starts_at')
            ->paginate(15);

        return view('pension-guard.sessions.index', [
            'bookings' => $bookings,
        ]);
    }

    public function create(): View
    {
        return view('pension-guard.sessions.create');
    }

    /**
     * Return available appointment slots for a calendar month.
     */
    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $timezone = config('app.timezone', 'Europe/London');
        $monthStart = Carbon::createFromFormat(
            '!Y-m',
            $validated['month'],
            $timezone
        );

        $monthEnd = $monthStart->copy()->addMonth();

        $windows = PensionGuardSessionAvailability::query()
            ->where('is_active', true)
            ->where('starts_at', '<', $monthEnd)
            ->where('ends_at', '>', $monthStart)
            ->orderBy('starts_at')
            ->get();

        $now = Carbon::now($timezone);

        $bookings = PensionGuardSessionBooking::query()
            ->where('starts_at', '<', $monthEnd)
            ->where('ends_at', '>', $monthStart)
            ->where(function ($query) use ($now) {
                $query->where('status', 'confirmed')
                    ->orWhere(function ($pending) use ($now) {
                        $pending->where('status', 'pending')
                            ->where('payment_status', 'unpaid')
                            ->where('checkout_expires_at', '>', $now);
                    });
            })
            ->get(['starts_at', 'ends_at']);

        $dates = [];

        foreach ($windows as $window) {
            $windowStart = $window->starts_at->copy()->max($now);

            // Appointment start times are offered on the hour or half-hour.
            $cursorTimestamp = (int) (
                ceil($windowStart->timestamp / 1800) * 1800
            );

            $cursor = Carbon::createFromTimestamp(
                $cursorTimestamp,
                $timezone
            );

            while ($cursor->copy()->addMinutes(30)->lte($window->ends_at)) {
                $slotStart = $cursor->copy();
                $durations = [];

                for ($minutes = 30; $minutes <= 480; $minutes += 30) {
                    $slotEnd = $slotStart->copy()->addMinutes($minutes);

                    if ($slotEnd->gt($window->ends_at)) {
                        break;
                    }

                    $overlaps = $bookings->contains(function ($booking) use (
                        $slotStart,
                        $slotEnd
                    ) {
                        return $booking->starts_at->lt($slotEnd)
                            && $booking->ends_at->gt($slotStart);
                    });

                    if ($overlaps) {
                        break;
                    }

                    $durations[] = $minutes;
                }

                if ($durations !== []) {
                    $dateKey = $slotStart->format('Y-m-d');

                    $dates[$dateKey][] = [
                        'availability_id' => $window->id,
                        'starts_at' => $slotStart->toIso8601String(),
                        'time' => $slotStart->format('H:i'),
                        'durations' => $durations,
                    ];
                }

                $cursor->addMinutes(30);
            }
        }

        ksort($dates);

        return response()->json([
            'dates' => $dates,
            'timezone' => $timezone,
        ]);
    }

    /**
     * Validate and temporarily reserve an appointment before Stripe Checkout.
     */
    public function store(
        Request $request,
        PensionGuardSessionBookingService $bookingService
    ): RedirectResponse {
        $validated = $request->validate([
            'availability_id' => ['required', 'integer'],
            'starts_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:480'],
            'channel' => ['required', 'in:telephone,video'],
        ]);

        $duration = (int) $validated['duration_minutes'];

        if ($duration % 30 !== 0) {
            throw ValidationException::withMessages([
                'duration_minutes' => 'Choose a duration in 30-minute increments.',
            ]);
        }

        $timezone = config('app.timezone', 'Europe/London');
        $startsAt = Carbon::parse($validated['starts_at'], $timezone)->setTimezone($timezone);
        $endsAt = $startsAt->copy()->addMinutes($duration);
        $now = Carbon::now($timezone);

        if (
            !in_array($startsAt->minute, [0, 30], true)
            || $startsAt->second !== 0
            || $startsAt->lte($now)
        ) {
            throw ValidationException::withMessages([
                'starts_at' => 'Please select a valid future appointment slot from the calendar.',
            ]);
        }

        $user = Auth::guard('pension_guard')->user();

        $booking = DB::transaction(function () use (
            $validated,
            $startsAt,
            $endsAt,
            $duration,
            $user,
            $now
        ) {
            /*
             * Lock active availability rows in a consistent order. This
             * serialises booking attempts against the published windows.
             */
            $windows = PensionGuardSessionAvailability::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $window = $windows->firstWhere(
                'id',
                (int) $validated['availability_id']
            );

            if (
                !$window
                || $startsAt->lt($window->starts_at)
                || $endsAt->gt($window->ends_at)
            ) {
                throw ValidationException::withMessages([
                    'starts_at' => 'That appointment is no longer available. Please select another slot.',
                ]);
            }

            $firstSlotTimestamp = (int) (
                ceil($window->starts_at->timestamp / 1800) * 1800
            );

            if (
                $startsAt->timestamp < $firstSlotTimestamp
                || $startsAt->timestamp % 1800 !== 0
            ) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Please choose one of the appointment times shown on the calendar.',
                ]);
            }

            /*
             * Check all bookings, not only bookings attached to this
             * availability ID. This prevents overlapping appointments.
             */
            $conflict = PensionGuardSessionBooking::query()
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->where(function ($query) use ($now) {
                    $query->where('status', 'confirmed')
                        ->orWhere(function ($pending) use ($now) {
                            $pending->where('status', 'pending')
                                ->where('payment_status', 'unpaid')
                                ->where('checkout_expires_at', '>', $now);
                        });
                })
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'starts_at' => 'That appointment has just been reserved by someone else. Please choose another time.',
                ]);
            }

            return PensionGuardSessionBooking::create([
                'user_id' => $user->id,
                'availability_id' => $window->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'duration_minutes' => $duration,
                'channel' => $validated['channel'],
                'amount_pence' => PensionGuardSessionBooking::calculateAmount($duration),
                'currency' => 'gbp',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'checkout_expires_at' => $now->copy()->addMinutes(35),
            ]);
        });

        $stripeSession = null;

        try {
            $stripeSession = $bookingService->createCheckoutSession($booking);

            if (empty($stripeSession->id) || empty($stripeSession->url)) {
                throw new \RuntimeException(
                    'Stripe did not return a valid Checkout Session.'
                );
            }

            $booking->update([
                'stripe_checkout_session_id' => $stripeSession->id,
                'checkout_expires_at' => isset($stripeSession->expires_at)
                    ? Carbon::createFromTimestamp($stripeSession->expires_at)
                    : now()->addMinutes(35),
            ]);

            return redirect()->away($stripeSession->url);
        } catch (Throwable $exception) {
            report($exception);

            /*
             * If Stripe created a session but saving its ID failed, try to
             * expire that remote session before releasing the appointment.
             */
            $remoteSessionExpired = true;

            if ($stripeSession && !empty($stripeSession->id)) {
                try {
                    $secret = config('pension_guard_billing.stripe.secret');

                    if (!$secret) {
                        throw new \RuntimeException('Stripe secret is not configured.');
                    }

                    $stripe = new StripeClient($secret);
                    $stripe->checkout->sessions->expire($stripeSession->id);
                } catch (Throwable $expiryException) {
                    report($expiryException);
                    $remoteSessionExpired = false;
                }
            }

            if ($remoteSessionExpired) {
                $booking->update([
                    'status' => 'expired',
                    'payment_status' => 'unpaid',
                    'cancelled_at' => now(),
                ]);
            }

            return redirect()
                ->route('pension-guard.sessions.show', $booking)
                ->withErrors([
                    'booking' => $remoteSessionExpired
                        ? 'We could not start your payment. The appointment reservation has been released. Please try again.'
                        : 'We could not complete the payment setup. Your appointment is being held temporarily while the payment session expires. Please check your booking status before trying again.',
                ]);
        }
    }

    public function show(PensionGuardSessionBooking $booking): View
    {
        $this->ensureBookingOwner($booking);

        $booking->load('availability');

        return view('pension-guard.sessions.show', [
            'booking' => $booking,
        ]);
    }

    public function success(PensionGuardSessionBooking $booking): View
    {
        $this->ensureBookingOwner($booking);

        $booking->refresh();

        return view('pension-guard.sessions.success', [
            'booking' => $booking,
        ]);
    }

    public function cancelled(PensionGuardSessionBooking $booking): View
    {
        $this->ensureBookingOwner($booking);

        $booking->refresh();

        return view('pension-guard.sessions.cancelled', [
            'booking' => $booking,
        ]);
    }

    private function ensureBookingOwner(PensionGuardSessionBooking $booking): void
    {
        abort_unless(
            (int) $booking->user_id === (int) Auth::guard('pension_guard')->id(),
            403
        );
    }
}
