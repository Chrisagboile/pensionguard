<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PensionAccountGuard\Models\PensionGuardPayment;
use App\Modules\PensionAccountGuard\Models\PensionGuardPlan;
use App\Modules\PensionAccountGuard\Models\PensionGuardSessionBooking;
use App\Modules\PensionAccountGuard\Models\PensionGuardSubscription;
use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use App\Modules\PensionAccountGuard\Models\PensionGuardWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;
use UnexpectedValueException;

class PensionGuardStripeWebhookController extends Controller
{
    /**
     * Receive and process verified Stripe webhook events.
     */
    public function handle(Request $request)
    {
        $secret = config('pension_guard_billing.stripe.webhook_secret');

        if (empty($secret)) {
            return response('Webhook is not configured.', 503);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret
            );
        } catch (UnexpectedValueException | SignatureVerificationException $exception) {
            return response('Invalid webhook signature or payload.', 400);
        } catch (Throwable $exception) {
            report($exception);

            return response('Webhook verification failed.', 400);
        }

        try {
            DB::transaction(function () use ($event) {
                $storedEvent = PensionGuardWebhookEvent::where(
                    'stripe_event_id',
                    $event->id
                )->lockForUpdate()->first();

                if ($storedEvent && $storedEvent->processing_status === 'processed') {
                    return;
                }

                if (!$storedEvent) {
                    $storedEvent = PensionGuardWebhookEvent::create([
                        'stripe_event_id' => $event->id,
                        'event_type' => $event->type,
                        'processing_status' => 'pending',
                    ]);
                }

                $payload = json_decode(
                    json_encode($event->data->object),
                    true
                );

                if (!is_array($payload)) {
                    throw new UnexpectedValueException(
                        'Invalid Stripe event object.'
                    );
                }

                $this->processEvent($event->type, $payload);

                $storedEvent->update([
                    'processing_status' => 'processed',
                    'processed_at' => now(),
                    'failure_message' => null,
                ]);
            });

            return response('Webhook received.', 200);
        } catch (Throwable $exception) {
            report($exception);

            PensionGuardWebhookEvent::where(
                'stripe_event_id',
                $event->id
            )->update([
                'processing_status' => 'failed',
                'failure_message' => substr($exception->getMessage(), 0, 1000),
            ]);

            return response('Webhook processing failed.', 500);
        }
    }

    /**
     * Dispatch supported Stripe event types.
     */
    protected function processEvent(string $type, array $object): void
    {
        match ($type) {
            'checkout.session.completed' => $this->checkoutCompleted($object),

            'checkout.session.async_payment_succeeded' =>
                $this->sessionBookingPaymentSucceeded($object),

            'checkout.session.async_payment_failed' =>
                $this->sessionBookingPaymentFailed($object),

            'checkout.session.expired' =>
                $this->sessionBookingCheckoutExpired($object),

            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->subscriptionChanged($object),

            'invoice.paid',
            'invoice.payment_succeeded' => $this->invoicePaid($object),

            'invoice.payment_failed' => $this->invoiceFailed($object),

            default => null,
        };
    }

    /**
     * Process a completed Checkout Session.
     *
     * Subscription processing remains separate from one-off session bookings.
     */
    protected function checkoutCompleted(array $object): void
    {
        if (($object['mode'] ?? null) === 'subscription') {
            $this->subscriptionCheckoutCompleted($object);

            return;
        }

        if (
            ($object['mode'] ?? null) === 'payment'
            && ($object['metadata']['product_type'] ?? null) === 'pension_guard_session'
        ) {
            $this->sessionBookingPaymentSucceeded($object);
        }
    }

    /**
     * Link a completed subscription Checkout Session to the local user.
     *
     * Subscription access is still controlled by subscription webhooks.
     */
    protected function subscriptionCheckoutCompleted(array $object): void
    {
        $customerId = $this->stripeId($object['customer'] ?? null);
        $subscriptionId = $this->stripeId($object['subscription'] ?? null);

        if (!$customerId || !$subscriptionId) {
            return;
        }

        $user = $this->findUser(
            $customerId,
            $object['client_reference_id'] ?? null
        );

        if (!$user) {
            return;
        }

        $user->forceFill([
            'stripe_customer_id' => $customerId,
        ])->save();

        $subscription = PensionGuardSubscription::firstOrNew([
            'stripe_subscription_id' => $subscriptionId,
        ]);

        if (
            $subscription->exists
            && (int) $subscription->user_id !== (int) $user->id
        ) {
            throw new UnexpectedValueException(
                'Stripe subscription is already linked to another user.'
            );
        }

        $subscription->user_id = $user->id;
        $subscription->stripe_customer_id = $customerId;
        $subscription->status = $subscription->status ?: 'incomplete';
        $subscription->billing_interval =
            $object['metadata']['billing_interval'] ?? $subscription->billing_interval;

        $subscription->save();
    }

    /**
     * Confirm a paid Pension Protection Session booking.
     *
     * This method is safe to call for both checkout.session.completed
     * and checkout.session.async_payment_succeeded.
     */
    protected function sessionBookingPaymentSucceeded(array $object): void
    {
        if (
            ($object['mode'] ?? null) !== 'payment'
            || ($object['metadata']['product_type'] ?? null) !== 'pension_guard_session'
        ) {
            return;
        }

        $checkoutSessionId = $this->stripeId($object['id'] ?? null);
        $customerId = $this->stripeId($object['customer'] ?? null);
        $bookingId = $object['metadata']['booking_id'] ?? null;
        $metadataUserId = $object['metadata']['user_id'] ?? null;

        if (
            !$checkoutSessionId
            || !$customerId
            || !$bookingId
            || !ctype_digit((string) $bookingId)
            || !$metadataUserId
            || !ctype_digit((string) $metadataUserId)
        ) {
            throw new UnexpectedValueException(
                'Session booking Checkout metadata is incomplete.'
            );
        }

        // A completed event can arrive before a delayed payment has settled.
        // The separate async_payment_succeeded event will confirm it later.
        if (($object['payment_status'] ?? null) !== 'paid') {
            return;
        }

        DB::transaction(function () use (
            $object,
            $checkoutSessionId,
            $customerId,
            $bookingId,
            $metadataUserId
        ) {
            $booking = PensionGuardSessionBooking::whereKey(
                (int) $bookingId
            )->lockForUpdate()->first();

            if (!$booking) {
                throw new UnexpectedValueException(
                    'The session booking linked to this Stripe payment was not found.'
                );
            }

            if (
                (int) $booking->user_id !== (int) $metadataUserId
            ) {
                throw new UnexpectedValueException(
                    'Stripe session booking user does not match the booking owner.'
                );
            }

            if (
                $booking->stripe_checkout_session_id !== $checkoutSessionId
            ) {
                throw new UnexpectedValueException(
                    'Stripe Checkout Session does not match the stored booking.'
                );
            }

            $user = PensionGuardUser::whereKey(
                $booking->user_id
            )->lockForUpdate()->first();

            if (!$user) {
                throw new UnexpectedValueException(
                    'The Pension Guard booking owner could not be found.'
                );
            }

            if (
                (int) $metadataUserId !== (int) $user->id
                || $user->stripe_customer_id !== $customerId
            ) {
                throw new UnexpectedValueException(
                    'Stripe customer does not match the booking owner.'
                );
            }

            $amountTotal = $object['amount_total'] ?? null;
            $currency = strtolower($object['currency'] ?? '');

            if (
                !is_numeric($amountTotal)
                || (int) $amountTotal !== (int) $booking->amount_pence
            ) {
                throw new UnexpectedValueException(
                    'Stripe payment amount does not match the booking amount.'
                );
            }

            if (
                $currency !== strtolower((string) $booking->currency)
                || $currency !== 'gbp'
            ) {
                throw new UnexpectedValueException(
                    'Stripe payment currency does not match the booking currency.'
                );
            }

            // Do not overwrite a booking that has already been confirmed.
            if (
                $booking->payment_status === 'paid'
                && $booking->status === 'confirmed'
            ) {
                return;
            }

            if ($booking->status !== 'pending') {
                throw new UnexpectedValueException(
                    'This booking is no longer pending and cannot be confirmed.'
                );
            }

            $paymentIntentId = $this->stripeId(
                $object['payment_intent'] ?? null
            );

            $booking->forceFill([
                'stripe_payment_intent_id' => $paymentIntentId,
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'paid_at' => $booking->paid_at ?: now(),
                'confirmed_at' => $booking->confirmed_at ?: now(),
                'metadata' => array_merge(
                    $booking->metadata ?? [],
                    [
                        'stripe_payment_status' => 'paid',
                        'stripe_checkout_session_id' => $checkoutSessionId,
                        'stripe_payment_intent_id' => $paymentIntentId,
                    ]
                ),
            ])->save();
        });
    }

    /**
     * Release a reservation after an asynchronous payment failure.
     */
    protected function sessionBookingPaymentFailed(array $object): void
    {
        $this->releaseSessionBookingReservation($object, 'payment_failed');
    }

    /**
     * Release a reservation when Stripe Checkout expires.
     */
    protected function sessionBookingCheckoutExpired(array $object): void
    {
        $this->releaseSessionBookingReservation($object, 'checkout_expired');
    }

    /**
     * Mark an unpaid pending booking as expired and release its time slot.
     *
     * Already-paid bookings are never expired by a late failure/expiry event.
     */
    protected function releaseSessionBookingReservation(
        array $object,
        string $reason
    ): void {
        if (
            ($object['mode'] ?? null) !== 'payment'
            || ($object['metadata']['product_type'] ?? null) !== 'pension_guard_session'
        ) {
            return;
        }

        $checkoutSessionId = $this->stripeId($object['id'] ?? null);
        $bookingId = $object['metadata']['booking_id'] ?? null;
        $metadataUserId = $object['metadata']['user_id'] ?? null;

        if (
            !$checkoutSessionId
            || !$bookingId
            || !ctype_digit((string) $bookingId)
            || !$metadataUserId
            || !ctype_digit((string) $metadataUserId)
        ) {
            throw new UnexpectedValueException(
                'Session booking expiry metadata is incomplete.'
            );
        }

        DB::transaction(function () use (
            $checkoutSessionId,
            $bookingId,
            $metadataUserId,
            $reason
        ) {
            $booking = PensionGuardSessionBooking::whereKey(
                (int) $bookingId
            )->lockForUpdate()->first();

            if (!$booking) {
                throw new UnexpectedValueException(
                    'The session booking linked to the expired Checkout Session was not found.'
                );
            }

            if (
                (int) $booking->user_id !== (int) $metadataUserId
                || $booking->stripe_checkout_session_id !== $checkoutSessionId
            ) {
                throw new UnexpectedValueException(
                    'Expired Stripe Checkout Session does not match the stored booking.'
                );
            }

            // A late expiry/failure event must never undo a successful payment.
            if (
                $booking->payment_status === 'paid'
                || $booking->status === 'confirmed'
            ) {
                return;
            }

            if ($booking->status !== 'pending') {
                return;
            }

            $booking->forceFill([
                'status' => 'expired',
                'payment_status' => 'failed',
                'metadata' => array_merge(
                    $booking->metadata ?? [],
                    [
                        'stripe_reservation_release_reason' => $reason,
                        'stripe_reservation_released_at' => now()->toIso8601String(),
                    ]
                ),
            ])->save();
        });
    }

    /**
     * Synchronise local subscription status from Stripe.
     */
    protected function subscriptionChanged(array $object): void
    {
        $subscriptionId = $this->stripeId($object['id'] ?? null);
        $customerId = $this->stripeId($object['customer'] ?? null);

        if (!$subscriptionId || !$customerId) {
            return;
        }

        $user = $this->findUser(
            $customerId,
            $object['metadata']['pension_guard_user_id'] ?? null
        );

        if (!$user) {
            return;
        }

        $existing = PensionGuardSubscription::where(
            'stripe_subscription_id',
            $subscriptionId
        )->first();

        if ($existing && (int) $existing->user_id !== (int) $user->id) {
            throw new UnexpectedValueException(
                'Subscription ownership mismatch.'
            );
        }

        $priceId = data_get($object, 'items.data.0.price.id');

        $plan = $priceId
            ? PensionGuardPlan::where('stripe_price_id', $priceId)->first()
            : null;

        $interval = $object['metadata']['billing_interval'] ?? null;

        if (!$interval && $plan) {
            $interval = $plan->billing_interval;
        }

        $status = $object['status'] ?? 'incomplete';

        PensionGuardSubscription::updateOrCreate(
            ['stripe_subscription_id' => $subscriptionId],
            [
                'user_id' => $user->id,
                'plan_id' => $plan?->id,
                'stripe_customer_id' => $customerId,
                'status' => $status,
                'billing_interval' => $interval,
                'current_period_start' => $this->stripeDate(
                    $object['current_period_start'] ?? null
                ),
                'current_period_end' => $this->stripeDate(
                    $object['current_period_end'] ?? null
                ),
                'trial_ends_at' => $this->stripeDate(
                    $object['trial_end'] ?? null
                ),
                'cancel_at' => $this->stripeDate(
                    $object['cancel_at'] ?? null
                ),
                'canceled_at' => $this->stripeDate(
                    $object['canceled_at'] ?? null
                ),
                'ended_at' => $status === 'canceled'
                    ? $this->stripeDate($object['ended_at'] ?? time())
                    : null,
                'cancel_at_period_end' => (bool) (
                    $object['cancel_at_period_end'] ?? false
                ),
            ]
        );
    }

    /**
     * Record a successfully paid subscription invoice.
     */
    protected function invoicePaid(array $object): void
    {
        $customerId = $this->stripeId($object['customer'] ?? null);
        $invoiceId = $object['id'] ?? null;

        if (!$customerId || !$invoiceId) {
            return;
        }

        $user = $this->findUser($customerId);

        if (!$user) {
            return;
        }

        $subscriptionId = $this->invoiceSubscriptionId($object);

        $subscription = $subscriptionId
            ? PensionGuardSubscription::where(
                'stripe_subscription_id',
                $subscriptionId
            )->where('user_id', $user->id)->first()
            : null;

        PensionGuardPayment::updateOrCreate(
            ['stripe_invoice_id' => $invoiceId],
            [
                'user_id' => $user->id,
                'subscription_id' => $subscription?->id,
                'stripe_payment_intent_id' => $this->stripeId(
                    $object['payment_intent'] ?? null
                ),
                'amount_pence' => (int) (
                    $object['amount_paid'] ?? $object['total'] ?? 0
                ),
                'currency' => strtolower($object['currency'] ?? 'gbp'),
                'status' => 'paid',
                'paid_at' => $this->stripeDate(
                    $object['status_transitions']['paid_at'] ?? time()
                ),
                'metadata' => [
                    'invoice_number' => $object['number'] ?? null,
                    'hosted_invoice_url' => $object['hosted_invoice_url'] ?? null,
                ],
            ]
        );
    }

    /**
     * Record a failed subscription invoice payment.
     */
    protected function invoiceFailed(array $object): void
    {
        $customerId = $this->stripeId($object['customer'] ?? null);
        $invoiceId = $object['id'] ?? null;

        if (!$customerId || !$invoiceId) {
            return;
        }

        $user = $this->findUser($customerId);

        if (!$user) {
            return;
        }

        $subscriptionId = $this->invoiceSubscriptionId($object);

        $subscription = $subscriptionId
            ? PensionGuardSubscription::where(
                'stripe_subscription_id',
                $subscriptionId
            )->where('user_id', $user->id)->first()
            : null;

        PensionGuardPayment::updateOrCreate(
            ['stripe_invoice_id' => $invoiceId],
            [
                'user_id' => $user->id,
                'subscription_id' => $subscription?->id,
                'stripe_payment_intent_id' => $this->stripeId(
                    $object['payment_intent'] ?? null
                ),
                'amount_pence' => (int) (
                    $object['amount_due'] ?? $object['total'] ?? 0
                ),
                'currency' => strtolower($object['currency'] ?? 'gbp'),
                'status' => 'failed',
                'paid_at' => null,
                'metadata' => [
                    'attempt_count' => $object['attempt_count'] ?? null,
                ],
            ]
        );
    }

    protected function findUser(
        string $customerId,
        ?string $metadataUserId = null
    ): ?PensionGuardUser {
        $user = PensionGuardUser::where(
            'stripe_customer_id',
            $customerId
        )->first();

        if ($user) {
            return $user;
        }

        if ($metadataUserId && ctype_digit($metadataUserId)) {
            $candidate = PensionGuardUser::find((int) $metadataUserId);

            if ($candidate && (
                empty($candidate->stripe_customer_id)
                || $candidate->stripe_customer_id === $customerId
            )) {
                return $candidate;
            }
        }

        return null;
    }

    protected function stripeId(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value['id'] ?? null;
        }

        return null;
    }

    protected function stripeDate(mixed $timestamp): mixed
    {
        return is_numeric($timestamp)
            ? now()->setTimestamp((int) $timestamp)
            : null;
    }

    protected function invoiceSubscriptionId(array $invoice): ?string
    {
        return $this->stripeId(
            data_get($invoice, 'subscription')
            ?? data_get($invoice, 'parent.subscription_details.subscription')
        );
    }
}
