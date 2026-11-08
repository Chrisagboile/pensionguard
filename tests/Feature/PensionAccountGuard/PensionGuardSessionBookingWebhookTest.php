<?php

namespace Tests\Feature\PensionAccountGuard;

use App\Modules\PensionAccountGuard\Models\PensionGuardSessionAvailability;
use App\Modules\PensionAccountGuard\Models\PensionGuardSessionBooking;
use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use App\Modules\PensionAccountGuard\Models\PensionGuardWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PensionGuardSessionBookingWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'whsec_test_secret_for_session_bookings';

    private function createPensionGuardUser(): PensionGuardUser
    {
        $user = PensionGuardUser::create([
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'TestPassword123!',
        ]);

        $user->forceFill([
            'stripe_customer_id' => 'cus_session_test_' . $user->id,
        ])->save();

        return $user;
    }

    private function createBooking(
        PensionGuardUser $user,
        string $checkoutSessionId = 'cs_test_session_booking'
    ): PensionGuardSessionBooking {
        $startsAt = now()->addDays(3)->startOfHour();

        $availability = PensionGuardSessionAvailability::create([
            'created_by' => $user->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHours(2),
            'is_active' => true,
        ]);

        return PensionGuardSessionBooking::create([
            'user_id' => $user->id,
            'availability_id' => $availability->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(30),
            'duration_minutes' => 30,
            'channel' => 'video',
            'amount_pence' => 2500,
            'currency' => 'gbp',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'stripe_checkout_session_id' => $checkoutSessionId,
            'checkout_expires_at' => now()->addMinutes(35),
        ]);
    }

    private function sessionEvent(
        string $eventId,
        string $eventType,
        PensionGuardSessionBooking $booking,
        PensionGuardUser $user,
        array $overrides = []
    ): array {
        $object = array_merge([
            'id' => $booking->stripe_checkout_session_id,
            'object' => 'checkout.session',
            'mode' => 'payment',
            'payment_status' => 'paid',
            'customer' => $user->stripe_customer_id,
            'payment_intent' => 'pi_test_session_' . $booking->id,
            'amount_total' => 2500,
            'currency' => 'gbp',
            'metadata' => [
                'product_type' => 'pension_guard_session',
                'booking_id' => (string) $booking->id,
                'user_id' => (string) $user->id,
            ],
        ], $overrides);

        return [
            'id' => $eventId,
            'object' => 'event',
            'api_version' => '2025-03-31.basil',
            'created' => time(),
            'data' => [
                'object' => $object,
            ],
            'livemode' => false,
            'pending_webhooks' => 1,
            'type' => $eventType,
        ];
    }

    private function sendSignedWebhook(array $event): TestResponse
    {
        $payload = json_encode($event, JSON_THROW_ON_ERROR);

        $timestamp = time();

        $signature = hash_hmac(
            'sha256',
            $timestamp . '.' . $payload,
            $this->webhookSecret
        );

        return $this->call(
            'POST',
            route('pension-guard.stripe.webhook'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
            ],
            $payload
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'pension_guard_billing.stripe.webhook_secret' => $this->webhookSecret,
        ]);
    }

    public function test_successful_session_payment_confirms_booking(): void
    {
        $user = $this->createPensionGuardUser();
        $booking = $this->createBooking($user);

        $event = $this->sessionEvent(
            'evt_session_paid_001',
            'checkout.session.completed',
            $booking,
            $user
        );

        $response = $this->sendSignedWebhook($event);

        $response->assertOk();

        $this->assertDatabaseHas('pension_guard_session_bookings', [
            'id' => $booking->id,
            'user_id' => $user->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'stripe_checkout_session_id' => 'cs_test_session_booking',
            'stripe_payment_intent_id' => 'pi_test_session_' . $booking->id,
        ]);

        $this->assertDatabaseHas('pension_guard_webhook_events', [
            'stripe_event_id' => 'evt_session_paid_001',
            'event_type' => 'checkout.session.completed',
            'processing_status' => 'processed',
        ]);
    }

    public function test_completed_checkout_with_unpaid_status_does_not_confirm_booking(): void
    {
        $user = $this->createPensionGuardUser();
        $booking = $this->createBooking($user);

        $event = $this->sessionEvent(
            'evt_session_unpaid_001',
            'checkout.session.completed',
            $booking,
            $user,
            [
                'payment_status' => 'unpaid',
            ]
        );

        $this->sendSignedWebhook($event)->assertOk();

        $this->assertDatabaseHas('pension_guard_session_bookings', [
            'id' => $booking->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_async_payment_success_confirms_booking(): void
    {
        $user = $this->createPensionGuardUser();
        $booking = $this->createBooking($user);

        $event = $this->sessionEvent(
            'evt_session_async_paid_001',
            'checkout.session.async_payment_succeeded',
            $booking,
            $user
        );

        $this->sendSignedWebhook($event)->assertOk();

        $this->assertDatabaseHas('pension_guard_session_bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_async_payment_failure_releases_pending_booking(): void
    {
        $user = $this->createPensionGuardUser();
        $booking = $this->createBooking($user);

        $event = $this->sessionEvent(
            'evt_session_failed_001',
            'checkout.session.async_payment_failed',
            $booking,
            $user,
            [
                'payment_status' => 'unpaid',
                'payment_intent' => null,
            ]
        );

        $this->sendSignedWebhook($event)->assertOk();

        $this->assertDatabaseHas('pension_guard_session_bookings', [
            'id' => $booking->id,
            'status' => 'expired',
            'payment_status' => 'failed',
        ]);
    }

    public function test_expired_checkout_releases_pending_booking(): void
    {
        $user = $this->createPensionGuardUser();
        $booking = $this->createBooking($user);

        $event = $this->sessionEvent(
            'evt_session_expired_001',
            'checkout.session.expired',
            $booking,
            $user,
            [
                'payment_status' => 'unpaid',
                'payment_intent' => null,
            ]
        );

        $this->sendSignedWebhook($event)->assertOk();

        $this->assertDatabaseHas('pension_guard_session_bookings', [
            'id' => $booking->id,
            'status' => 'expired',
            'payment_status' => 'failed',
        ]);
    }

    public function test_duplicate_success_event_does_not_duplicate_processing(): void
    {
        $user = $this->createPensionGuardUser();
        $booking = $this->createBooking($user);

        $event = $this->sessionEvent(
            'evt_session_duplicate_001',
            'checkout.session.completed',
            $booking,
            $user
        );

        $this->sendSignedWebhook($event)->assertOk();
        $this->sendSignedWebhook($event)->assertOk();

        $this->assertSame(
            1,
            PensionGuardWebhookEvent::where(
                'stripe_event_id',
                'evt_session_duplicate_001'
            )->count()
        );

        $this->assertDatabaseHas('pension_guard_session_bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_incorrect_payment_amount_cannot_confirm_booking(): void
    {
        $user = $this->createPensionGuardUser();
        $booking = $this->createBooking($user);

        $event = $this->sessionEvent(
            'evt_session_wrong_amount_001',
            'checkout.session.completed',
            $booking,
            $user,
            [
                'amount_total' => 1000,
            ]
        );

        $this->sendSignedWebhook($event)->assertStatus(500);

        $this->assertDatabaseHas('pension_guard_session_bookings', [
            'id' => $booking->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_wrong_stripe_customer_cannot_confirm_booking(): void
    {
        $user = $this->createPensionGuardUser();
        $booking = $this->createBooking($user);

        $event = $this->sessionEvent(
            'evt_session_wrong_customer_001',
            'checkout.session.completed',
            $booking,
            $user,
            [
                'customer' => 'cus_someone_else',
            ]
        );

        $this->sendSignedWebhook($event)->assertStatus(500);

        $this->assertDatabaseHas('pension_guard_session_bookings', [
            'id' => $booking->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_wrong_booking_user_metadata_cannot_confirm_booking(): void
    {
        $user = $this->createPensionGuardUser();
        $otherUser = $this->createPensionGuardUser();

        $booking = $this->createBooking($user);

        $event = $this->sessionEvent(
            'evt_session_wrong_owner_001',
            'checkout.session.completed',
            $booking,
            $user,
            [
                'metadata' => [
                    'product_type' => 'pension_guard_session',
                    'booking_id' => (string) $booking->id,
                    'user_id' => (string) $otherUser->id,
                ],
            ]
        );

        $this->sendSignedWebhook($event)->assertStatus(500);

        $this->assertDatabaseHas('pension_guard_session_bookings', [
            'id' => $booking->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }
}
