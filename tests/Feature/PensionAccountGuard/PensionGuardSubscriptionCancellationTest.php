<?php

namespace Tests\Feature\PensionAccountGuard;

use App\Modules\PensionAccountGuard\Models\PensionGuardSubscription;
use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use App\Modules\PensionAccountGuard\Services\StripeSubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use RuntimeException;

class PensionGuardSubscriptionCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function createPensionGuardUser(): PensionGuardUser
    {
    return PensionGuardUser::create([
        'name' => fake()->name(),
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
         'email_verified_at' => now(),
        'password' => 'TestPassword123!',
        ]);
    }

    private function createSubscription(
        PensionGuardUser $user,
        string $status = 'active'
    ): PensionGuardSubscription {
        return PensionGuardSubscription::create([
            'user_id' => $user->id,
            'stripe_subscription_id' => 'sub_test_123',
            'stripe_customer_id' => 'cus_test_123',
            'status' => $status,
            'billing_interval' => 'monthly',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'cancel_at_period_end' => false,
        ]);
    }

    public function test_user_can_request_cancellation_of_own_subscription(): void
    {
        $user = $this->createPensionGuardUser();

        $subscription = $this->createSubscription($user);

        $this->mock(StripeSubscriptionService::class, function ($mock) use (
            $user,
            $subscription
        ) {
            $mock->shouldReceive('cancelAtPeriodEnd')
                ->once()
                ->withArgs(function ($receivedUser, $receivedSubscription) use (
                    $user,
                    $subscription
                ) {
                    return $receivedUser->id === $user->id
                        && $receivedSubscription->id === $subscription->id;
                })
                ->andReturn((object) [
                    'id' => 'sub_test_123',
                    'cancel_at_period_end' => true,
                ]);
        });

        $response = $this->actingAs($user, 'pension_guard')
            ->post(route(
                'pension-guard.subscriptions.cancel',
                $subscription->id
            ));

        $response->assertRedirect(
            route('pension-guard.subscriptions.index')
        );

        $response->assertSessionHas('success');

        // Local status must remain unchanged until Stripe's webhook arrives.
        $this->assertDatabaseHas('pension_guard_subscriptions', [
            'id' => $subscription->id,
            'status' => 'active',
            'cancel_at_period_end' => false,
        ]);
    }

    public function test_user_cannot_cancel_another_users_subscription(): void
    {
        $owner = $this->createPensionGuardUser();
        $anotherUser = $this->createPensionGuardUser();

        $subscription = $this->createSubscription($owner);

        $this->mock(StripeSubscriptionService::class, function ($mock) {
            $mock->shouldNotReceive('cancelAtPeriodEnd');
        });

        $response = $this->actingAs($anotherUser, 'pension_guard')
            ->post(route(
                'pension-guard.subscriptions.cancel',
                $subscription->id
            ));

        $response->assertNotFound();

        $this->assertDatabaseHas('pension_guard_subscriptions', [
            'id' => $subscription->id,
            'user_id' => $owner->id,
            'status' => 'active',
        ]);
    }

    public function test_cancellation_error_is_displayed_without_changing_subscription(): void
    {
        $user = $this->createPensionGuardUser();

        $subscription = $this->createSubscription($user, 'incomplete');

        $this->mock(StripeSubscriptionService::class, function ($mock) {
            $mock->shouldReceive('cancelAtPeriodEnd')
                ->once()
                ->andThrow(new RuntimeException(
                    'This subscription cannot be cancelled in its current status.'
                ));
        });

        $response = $this->actingAs($user, 'pension_guard')
            ->post(route(
                'pension-guard.subscriptions.cancel',
                $subscription->id
            ));

        $response->assertRedirect(
            route('pension-guard.subscriptions.index')
        );

        $response->assertSessionHas(
            'error',
            'This subscription cannot be cancelled in its current status.'
        );

        $this->assertDatabaseHas('pension_guard_subscriptions', [
            'id' => $subscription->id,
            'status' => 'incomplete',
            'cancel_at_period_end' => false,
        ]);
    }
}
