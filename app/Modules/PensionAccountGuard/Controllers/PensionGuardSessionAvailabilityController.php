<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PensionAccountGuard\Models\PensionGuardSessionAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PensionGuardSessionAvailabilityController extends Controller
{
    public function index(): View
    {
        $availability = PensionGuardSessionAvailability::query()
            ->with('creator')
            ->withCount('bookings')
            ->orderByDesc('starts_at')
            ->paginate(20);

        return view('pension-guard.admin.availability.index', [
            'availability' => $availability,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $startsAt = $validated['starts_at'];
        $endsAt = $validated['ends_at'];

        $overlap = PensionGuardSessionAvailability::query()
            ->where('is_active', true)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($overlap) {
            return back()
                ->withInput()
                ->withErrors([
                    'starts_at' => 'This time window overlaps with existing active availability.',
                ]);
        }

        PensionGuardSessionAvailability::create([
            'created_by' => Auth::guard('pension_guard')->id(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_active' => true,
        ]);

        return redirect()
            ->route('pension-guard.admin.availability.index')
            ->with('success', 'Availability window created successfully.');
    }

    public function toggle(
        PensionGuardSessionAvailability $availability
    ): RedirectResponse {
        if (!$availability->is_active) {
            $overlap = PensionGuardSessionAvailability::query()
                ->where('id', '!=', $availability->id)
                ->where('is_active', true)
                ->where('starts_at', '<', $availability->ends_at)
                ->where('ends_at', '>', $availability->starts_at)
                ->exists();

            if ($overlap) {
                return back()->with(
                    'error',
                    'This availability cannot be activated because it overlaps another active window.'
                );
            }
        }

        $availability->update([
            'is_active' => !$availability->is_active,
        ]);

        return back()->with(
            'success',
            $availability->is_active
                ? 'Availability activated.'
                : 'Availability deactivated.'
        );
    }
}