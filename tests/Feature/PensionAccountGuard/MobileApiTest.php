<?php

namespace Tests\Feature\PensionAccountGuard;

use Illuminate\Support\Facades\Auth;
use App\Modules\PensionAccountGuard\Models\PensionGuardAccount;
use App\Modules\PensionAccountGuard\Models\PensionGuardAssessment;
use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    private function createPensionGuardUser(bool $verified = true): PensionGuardUser
    {
        return PensionGuardUser::create([
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => $verified ? now() : null,
            'password' => 'TestPassword123!',
        ]);
    }

    private function loginAndGetToken(PensionGuardUser $user): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'TestPassword123!',
            'device_name' => 'Mobile API feature test',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email_verified', true);

        return $response->json('access_token');
    }

    public function test_verified_pension_guard_user_can_log_in_and_receives_a_30_day_token(): void
    {
        $user = $this->createPensionGuardUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'TestPassword123!',
            'device_name' => 'My iPhone',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonPath('user.email_verified', true)
            ->assertJsonStructure(['access_token']);

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $token = $user->tokens()->firstOrFail();

        $this->assertSame('My iPhone', $token->name);
        $this->assertNotNull($token->expires_at);
        $this->assertEqualsWithDelta(
            now()->addMinutes(43200)->timestamp,
            $token->expires_at->timestamp,
            2
        );
    }

    public function test_invalid_login_credentials_are_rejected(): void
    {
        $user = $this->createPensionGuardUser();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'IncorrectPassword!',
            'device_name' => 'Test device',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unverified_user_cannot_log_in_to_the_mobile_api(): void
    {
        $user = $this->createPensionGuardUser(verified: false);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'TestPassword123!',
            'device_name' => 'Test device',
        ])->assertForbidden()
            ->assertJsonPath('code', 'email_not_verified');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_mobile_api_endpoints_reject_unauthenticated_requests(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->getJson('/api/v1/accounts')->assertUnauthorized();
        $this->getJson('/api/v1/assessments')->assertUnauthorized();
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
    }

    public function test_mobile_token_can_access_own_profile_without_exposing_private_fields(): void
    {
        $user = $this->createPensionGuardUser();
        $token = $this->loginAndGetToken($user);

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('user.username')
            ->assertJsonMissingPath('user.stripe_customer_id');
    }

    public function test_mobile_user_can_only_retrieve_their_own_pension_accounts(): void
    {
        $owner = $this->createPensionGuardUser();
        $otherUser = $this->createPensionGuardUser();

        $ownAccount = PensionGuardAccount::create([
            'user_id' => $owner->id,
            'provider_name' => 'My Pension Provider',
            'display_name' => 'My workplace pension',
            'account_type' => 'workplace',
            'notes' => 'My private account note',
        ]);

        PensionGuardAccount::create([
            'user_id' => $otherUser->id,
            'provider_name' => 'Other Pension Provider',
            'display_name' => 'Another person pension',
            'account_type' => 'personal',
            'notes' => 'This must not be visible',
        ]);

        $token = $this->loginAndGetToken($owner);

        $response = $this->withToken($token)
            ->getJson('/api/v1/accounts')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $ownAccount->id)
            ->assertJsonPath('data.0.provider_name', 'My Pension Provider');

        $response->assertDontSee('Other Pension Provider')
            ->assertDontSee('This must not be visible');
    }

    public function test_mobile_user_can_only_retrieve_their_own_assessments(): void
    {
        $owner = $this->createPensionGuardUser();
        $otherUser = $this->createPensionGuardUser();

        $ownAssessment = PensionGuardAssessment::create([
            'user_id' => $owner->id,
            'answers' => ['mfa' => 1],
            'score' => 1,
            'risk_band' => 'Lower',
        ]);

        PensionGuardAssessment::create([
            'user_id' => $otherUser->id,
            'answers' => ['mfa' => 5],
            'score' => 5,
            'risk_band' => 'Higher',
        ]);

        $token = $this->loginAndGetToken($owner);

        $response = $this->withToken($token)
            ->getJson('/api/v1/assessments')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $ownAssessment->id)
            ->assertJsonPath('data.0.risk_band', 'Lower');

        $response->assertDontSee('Higher');
    }

    public function test_mobile_logout_revokes_the_current_token(): void
    {
        $user = $this->createPensionGuardUser();
        $token = $this->loginAndGetToken($user);

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Signed out successfully.');

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Clear cached authentication state before simulating a new request.
        Auth::forgetGuards();

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }
    public function test_mobile_token_is_rejected_after_its_expiry(): void
    {
        $user = $this->createPensionGuardUser();
        $token = $this->loginAndGetToken($user);

        $this->travel(31)->days();

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }
}
