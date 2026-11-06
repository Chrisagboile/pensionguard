<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PensionAccountGuard\Models\PensionGuardPayment;
use App\Modules\PensionAccountGuard\Models\PensionGuardSubscription;
use App\Modules\PensionAccountGuard\Services\StripeSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Throwable;

class PensionGuardSubscriptionController extends Controller
{
    /**
     * Display subscription plans and the user's billing history.
     *
     * Stripe is deliberately not initialised here.
     * This page must work even when billing is not configured.
     */
    public function index()
    {
        $user = Auth::guard('pension_guard')->user();

        $subscriptions = PensionGuardSubscription::where(
            'user_id',
            $user->id
        )->with('plan')->latest()->get();

        $payments = PensionGuardPayment::where(
            'user_id',
            $user->id
        )->latest()->limit(20)->get();

        $plans = [
            'monthly' => [
                'name' => 'Monthly Premium',
                'interval' => 'monthly',
                'price_id' => config('pension_guard_billing.prices.monthly'),
            ],
            'annual' => [
                'name' => 'Annual Premium',
                'interval' => 'annual',
                'price_id' => config('pension_guard_billing.prices.annual'),
            ],
        ];

        return view('pension-guard.subscriptions.index', compact(
            'subscriptions',
            'payments',
            'plans'
        ));
    }

    /**
     * Start Stripe Checkout.
     */
    public function checkout(Request $request)
    {
        if (!config('pension_guard_billing.enabled')) {
            return back()->with(
                'error',
                'Premium subscriptions are temporarily unavailable.'
            );
        }

        $validated = $request->validate([
            'interval' => ['required', 'in:monthly,annual'],
        ]);

        $user = Auth::guard('pension_guard')->user();

        try {
            // Initialise Stripe only when checkout is requested.
            $stripe = app(StripeSubscriptionService::class);

            $session = $stripe->createCheckoutSession(
                $user,
                $validated['interval']
            );

            return redirect()->away($session->url);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'We could not start checkout. Please try again later.'
            );
        }
    }

    /**
     * Display the return page after Stripe Checkout.
     *
     * This page does not activate a subscription.
     */
    public function success()
    {
        return view('pension-guard.subscriptions.success');
    }

    /**
     * Display the checkout cancellation page.
     */
    public function cancelled()
    {
        return view('pension-guard.subscriptions.cancelled');
    }

    /**
     * Redirect the authenticated user to their Stripe Billing Portal.
     */
    public function billingPortal()
    {
        if (!config('pension_guard_billing.enabled')) {
            return redirect()
                ->route('pension-guard.subscriptions.index')
                ->with(
                    'error',
                    'Premium subscriptions are temporarily unavailable.'
                );
        }

        $user = Auth::guard('pension_guard')->user();

        try {
            // Initialise Stripe only when the billing portal is requested.
            $stripe = app(StripeSubscriptionService::class);

            $session = $stripe->createBillingPortalSession($user);

            return redirect()->away($session->url);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('pension-guard.subscriptions.index')
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('pension-guard.subscriptions.index')
                ->with('error', 'Unable to open the billing portal.');
        }
    }

    /**
     * Schedule cancellation of the user's own subscription.
     */
    public function cancel(int $subscription)
    {
        if (!config('pension_guard_billing.enabled')) {
            return redirect()
                ->route('pension-guard.subscriptions.index')
                ->with(
                    'error',
                    'Premium subscriptions are temporarily unavailable.'
                );
        }

        $user = Auth::guard('pension_guard')->user();

        // Only retrieve a subscription owned by this user.
        $subscriptionRecord = PensionGuardSubscription::where(
            'user_id',
            $user->id
        )->findOrFail($subscription);

        try {
            // Initialise Stripe only when cancellation is requested.
            $stripe = app(StripeSubscriptionService::class);

            $stripe->cancelAtPeriodEnd(
                $user,
                $subscriptionRecord
            );

            return redirect()
                ->route('pension-guard.subscriptions.index')
                ->with(
                    'success',
                    'Your cancellation request has been submitted. Your Premium access will continue until the end of your current billing period.'
                );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('pension-guard.subscriptions.index')
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('pension-guard.subscriptions.index')
                ->with(
                    'error',
                    'We could not process your cancellation. Please try again later.'
                );
        }
    }
}
