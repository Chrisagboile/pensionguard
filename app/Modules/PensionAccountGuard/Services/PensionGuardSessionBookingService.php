<?php

namespace App\Modules\PensionAccountGuard\Services;

use App\Modules\PensionAccountGuard\Models\PensionGuardSessionBooking;
use Stripe\StripeClient;
use RuntimeException;

class PensionGuardSessionBookingService
{
    public function createCheckoutSession(
        PensionGuardSessionBooking $booking
    ): \Stripe\Checkout\Session {
        $secret = config('pension_guard_billing.stripe.secret');

        if (!$secret) {
            throw new RuntimeException(
                'Pension Guard Stripe secret is not configured.'
            );
        }

        $booking->loadMissing('user');

        $stripe = new StripeClient($secret);

        $user = $booking->user;

        $customerId = $user->stripe_customer_id;

        if (!$customerId) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'pension_guard_user_id' => (string) $user->id,
                ],
            ]);

            $customerId = $customer->id;

            $user->update([
                'stripe_customer_id' => $customerId,
            ]);
        }

        $minutes = $booking->duration_minutes;

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'customer' => $customerId,
            'client_reference_id' => (string) $booking->id,

            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'gbp',
                    'unit_amount' => $booking->amount_pence,
                    'product_data' => [
                        'name' => 'Pension Protection Session',
                        'description' => $minutes . '-minute ' .
                            $booking->channel . ' session',
                    ],
                ],
            ]],

            'metadata' => [
                'product_type' => 'pension_guard_session',
                'booking_id' => (string) $booking->id,
                'user_id' => (string) $user->id,
            ],

            'payment_intent_data' => [
                'metadata' => [
                    'product_type' => 'pension_guard_session',
                    'booking_id' => (string) $booking->id,
                    'user_id' => (string) $user->id,
                ],
            ],

            'success_url' => route(
                'pension-guard.sessions.success',
                ['booking' => $booking->id]
            ) . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => route(
                'pension-guard.sessions.cancelled',
                ['booking' => $booking->id]
            ),

            // Give the customer time to complete checkout.
            'expires_at' => now()->addMinutes(35)->timestamp,
        ]);

        return $session;
    }
}