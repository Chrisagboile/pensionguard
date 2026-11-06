<?php

namespace Tests\Feature\PensionAccountGuard;

use App\Modules\PensionAccountGuard\Models\PensionGuardTrustedContact;
use App\Notifications\PensionGuardTrustedContactVerification;
use Illuminate\Support\Facades\Notification;
use App\Modules\PensionAccountGuard\Services\PensionGuardReminderService;
use App\Modules\PensionAccountGuard\Notifications\SecurityReminderNotification;
use App\Modules\PensionAccountGuard\Models\PensionGuardSecurityCheck;
//use Illuminate\Notifications\DatabaseNotification;
use App\Modules\PensionAccountGuard\Models\PensionGuardAccount;
use App\Modules\PensionAccountGuard\Models\PensionGuardAssessment;
use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\PensionAccountGuard\Services\PensionGuardTrustedContactAlertService;
use App\Modules\PensionAccountGuard\Notifications\TrustedContactAlertNotification;
use Tests\TestCase;

class PensionGuardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user specifically for Pension Account Guard.
     */
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

    public function test_public_dashboard_and_assessment_are_available(): void
    {
        $this->get(route('pension-guard.dashboard'))
            ->assertOk();

        $this->get(route('pension-guard.assessments.create'))
            ->assertOk();
    }

    public function test_guests_are_redirected_from_pension_accounts(): void
    {
        $this->get(route('pension-guard.accounts.index'))
            ->assertRedirect('/pension-guard/login');
    }

    public function test_user_can_add_a_manual_pension_record(): void
    {
        $user = $this->createPensionGuardUser();

        $response = $this->actingAs($user, 'pension_guard')->post(
            route('pension-guard.accounts.store'),
            [
                'provider_name' => 'Example Pension Provider',
                'account_type' => 'workplace',
                'display_name' => 'Previous employer pension',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('pension_guard_accounts', [
            'user_id' => $user->id,
            'provider_name' => 'Example Pension Provider',
            'account_type' => 'workplace',
        ]);

        $this->assertDatabaseCount('pension_guard_security_checks', 5);
    }

    public function test_user_cannot_view_another_users_pension_record(): void
    {
        $owner = $this->createPensionGuardUser();
        $otherUser = $this->createPensionGuardUser();

        $account = PensionGuardAccount::create([
            'user_id' => $owner->id,
            'provider_name' => 'Private Provider',
            'account_type' => 'personal',
        ]);

        $this->actingAs($otherUser, 'pension_guard')
            ->get(route('pension-guard.accounts.show', $account))
            ->assertNotFound();
    }

    public function test_signed_in_assessment_is_saved_for_that_user(): void
    {
        $user = $this->createPensionGuardUser();

        $answers = [
            'mfa' => 0,
            'passwords' => 0,
            'verification' => 0,
            'pressure' => 0,
            'review' => 0,
        ];

        $response = $this->actingAs($user, 'pension_guard')->post(
            route('pension-guard.assessments.store'),
            [
         'answers' => $answers,
            'age_group' => '50-54',
            'monthly_payment_interest' => 'maybe',
        ]
    //['answers' => $answers]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('pension_guard_assessments', [
            'user_id' => $user->id,
            'score' => 0,
            'risk_band' => 'Lower',
        ]);
    }

    public function test_public_assessment_is_not_saved_to_database(): void
    {
        $answers = [
            'mfa' => 1,
            'passwords' => 1,
            'verification' => 1,
            'pressure' => 1,
            'review' => 1,
        ];

        $this->post(route('pension-guard.assessments.store'), [
            'answers' => $answers,
            'age_group' => '60-64',
            'monthly_payment_interest' => 'no',
    ])->assertRedirect(route('pension-guard.assessments.result'));
        $this->assertDatabaseCount('pension_guard_assessments', 0);
        $this->get(route('pension-guard.assessments.result'))
            ->assertOk()
            ->assertSee('Moderate');
    }

    public function test_user_cannot_view_another_users_saved_assessment(): void
    {
        $owner = $this->createPensionGuardUser();
        $otherUser = $this->createPensionGuardUser();

        $assessment = PensionGuardAssessment::create([
            'user_id' => $owner->id,
            'answers' => [],
            'score' => 2,
            'risk_band' => 'Lower',
        ]);

        $this->actingAs($otherUser, 'pension_guard')
            ->get(route('pension-guard.assessments.show', $assessment))
            ->assertNotFound();
    }


    public function test_pension_guard_user_can_log_out(): void
    {
        $user = $this->createPensionGuardUser();

        $response = $this->actingAs($user, 'pension_guard')
            ->post(route('pension-guard.logout'));

        $response->assertRedirect(route('pension-guard.dashboard'));

        $this->assertGuest('pension_guard');
    }

    public function test_logged_out_user_cannot_access_pension_accounts(): void
    {
        $user = $this->createPensionGuardUser();

        $this->actingAs($user, 'pension_guard')
            ->post(route('pension-guard.logout'));

        $this->get(route('pension-guard.accounts.index'))
            ->assertRedirect('/pension-guard/login');

        $this->assertGuest('pension_guard');
    }

    public function test_user_can_view_their_own_notifications(): void
    {
        $user = $this->createPensionGuardUser();

        $user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'security_reminder',
            'data' => [
                'title' => 'Security reminder',
                'message' => 'Review your pension security checklist.',
            ],
        ]);

        $this->actingAs($user, 'pension_guard')
            ->get(route('pension-guard.notifications.index'))
            ->assertOk()
            ->assertSee('Security reminder')
            ->assertSee('Review your pension security checklist.')
            ->assertSee('Unread notifications:')
            ->assertSee('1');
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = $this->createPensionGuardUser();
        $otherUser = $this->createPensionGuardUser();

        $notification = $owner->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'security_reminder',
            'data' => [
            'title' => 'Private reminder',
            'message' => 'This belongs to the owner.',
            ],
        ]);

        $this->actingAs($otherUser, 'pension_guard')
            ->patch(route('pension-guard.notifications.read', [
                'notification' => $notification->id,
            ]))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    public function test_user_can_mark_their_own_notification_as_read(): void
    {
        $user = $this->createPensionGuardUser();

        $notification = $user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'security_reminder',
            'data' => [
                'title' => 'Security reminder',
                'message' => 'Review your security checklist.',
            ],
        ]);

        $this->actingAs($user, 'pension_guard')
            ->patch(route('pension-guard.notifications.read', [
                'notification' => $notification->id,
            ]))
            ->assertRedirect(route('pension-guard.notifications.index'));

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }


    public function test_notification_service_creates_a_database_notification(): void
    {
        $user = $this->createPensionGuardUser();

        app(\App\Modules\PensionAccountGuard\Services\PensionGuardNotificationService::class)
            ->send(
                $user,
                'Security reminder',
                'Review your pension security checklist.'
            );

        $this->assertDatabaseCount('notifications', 1);

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame('Security reminder', $notification->data['title']);
        $this->assertSame(
            'Review your pension security checklist.',
            $notification->data['message']
        );
    }

    public function test_notification_service_rejects_empty_title_or_message(): void
    {
        $user = $this->createPensionGuardUser();

        $service = app(
            \App\Modules\PensionAccountGuard\Services\PensionGuardNotificationService::class
        );

        try {
            $service->send($user, '', 'A security reminder.');
            $this->fail('An empty title should be rejected.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame(
                'A notification title and message are required.',
                $exception->getMessage()
            );
        }
    }

    public function test_notification_service_rejects_excessively_long_content(): void
    {
        $user = $this->createPensionGuardUser();

        $service = app(
            \App\Modules\PensionAccountGuard\Services\PensionGuardNotificationService::class
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The notification title or message is too long.'
        );

        $service->send(
            $user,
            'Security reminder',
            str_repeat('A', 1001)
        );
    }


    public function test_completing_a_security_check_creates_a_notification(): void
    {
        $user = $this->createPensionGuardUser();

        $account = PensionGuardAccount::create([
            'user_id' => $user->id,
            'provider_name' => 'Example Pension Provider',
            'account_type' => 'personal',
        ]);

        $check = PensionGuardSecurityCheck::create([
            'pension_guard_account_id' => $account->id,
            'check_key' => 'test_security_check',
            'title' => 'Enable two-step verification',
            'guidance' => 'Follow the provider guidance.',
            'is_complete' => false,
        ]);

        $this->actingAs($user, 'pension_guard')
            ->patch(route('pension-guard.checklist.update', [
                'account' => $account->id,
                'check' => $check->id,
            ]), [
                'is_complete' => true,
            ])
            ->assertRedirect(route('pension-guard.checklist.index', $account));

        $this->assertDatabaseHas('pension_guard_security_checks', [
            'id' => $check->id,
            'is_complete' => true,
        ]);

        $this->assertDatabaseCount('notifications', 1);

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame(
            'Security checklist updated',
            $notification->data['title']
        );
    }

    public function test_repeating_completed_security_check_does_not_create_duplicate_notification(): void
    {
        $user = $this->createPensionGuardUser();

        $account = PensionGuardAccount::create([
            'user_id' => $user->id,
            'provider_name' => 'Example Pension Provider',
            'account_type' => 'personal',
        ]);

        $check = PensionGuardSecurityCheck::create([
            'pension_guard_account_id' => $account->id,
            'check_key' => 'test_security_check',
            'title' => 'Enable two-step verification',
            'guidance' => 'Follow the provider guidance.',
            'is_complete' => false,
        ]);

        $url = route('pension-guard.checklist.update', [
            'account' => $account->id,
            'check' => $check->id,
        ]);

        $this->actingAs($user, 'pension_guard')
            ->patch($url, ['is_complete' => true])
            ->assertRedirect();

        $this->patch($url, ['is_complete' => true])
            ->assertRedirect();

        $this->assertDatabaseCount('notifications', 1);
    }

public function test_reminder_is_created_when_incomplete_security_checks_exist(): void
{
    $user = $this->createPensionGuardUser();

    $account = PensionGuardAccount::create([
        'user_id' => $user->id,
        'provider_name' => 'Example Pension Provider',
        'account_type' => 'personal',
    ]);

    PensionGuardSecurityCheck::create([
        'pension_guard_account_id' => $account->id,
        'check_key' => 'test_reminder_check',
        'title' => 'Enable two-step verification',
        'guidance' => 'Follow the provider guidance.',
        'is_complete' => false,
    ]);

    app(PensionGuardReminderService::class)
        ->remindAboutIncompleteChecks($user);

    $this->assertDatabaseCount('notifications', 1);

    $notification = $user->notifications()->first();

    $this->assertSame(
        'Security checklist reminder',
        $notification->data['title']
    );
}

public function test_reminder_is_not_created_when_no_incomplete_checks_exist(): void
{
    $user = $this->createPensionGuardUser();

    $account = PensionGuardAccount::create([
        'user_id' => $user->id,
        'provider_name' => 'Example Pension Provider',
        'account_type' => 'personal',
    ]);

    PensionGuardSecurityCheck::create([
        'pension_guard_account_id' => $account->id,
        'check_key' => 'completed_reminder_check',
        'title' => 'Enable two-step verification',
        'guidance' => 'Follow the provider guidance.',
        'is_complete' => true,
        'completed_at' => now(),
    ]);

    app(PensionGuardReminderService::class)
        ->remindAboutIncompleteChecks($user);

    $this->assertDatabaseCount('notifications', 0);
}

public function test_reminder_is_not_duplicated_within_seven_days(): void
{
    $user = $this->createPensionGuardUser();

    $account = PensionGuardAccount::create([
        'user_id' => $user->id,
        'provider_name' => 'Example Pension Provider',
        'account_type' => 'personal',
    ]);

    PensionGuardSecurityCheck::create([
        'pension_guard_account_id' => $account->id,
        'check_key' => 'duplicate_reminder_check',
        'title' => 'Enable two-step verification',
        'guidance' => 'Follow the provider guidance.',
        'is_complete' => false,
    ]);

    $user->notify(new SecurityReminderNotification(
        'Security checklist reminder',
        'Existing reminder.'
    ));

    app(PensionGuardReminderService::class)
        ->remindAboutIncompleteChecks($user);

    $this->assertDatabaseCount('notifications', 1);
}

public function test_reminder_can_be_created_again_after_seven_days(): void
{
    $user = $this->createPensionGuardUser();

    $account = PensionGuardAccount::create([
        'user_id' => $user->id,
        'provider_name' => 'Example Pension Provider',
        'account_type' => 'personal',
    ]);

    PensionGuardSecurityCheck::create([
        'pension_guard_account_id' => $account->id,
        'check_key' => 'expired_reminder_check',
        'title' => 'Enable two-step verification',
        'guidance' => 'Follow the provider guidance.',
        'is_complete' => false,
    ]);

    $oldNotification = $user->notifications()->create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => SecurityReminderNotification::class,
        'data' => [
            'title' => 'Security checklist reminder',
            'message' => 'An old reminder.',
        ],
        'read_at' => null,
        'created_at' => now()->subDays(8),
        'updated_at' => now()->subDays(8),
    ]);

    app(PensionGuardReminderService::class)
        ->remindAboutIncompleteChecks($user);

    $this->assertDatabaseCount('notifications', 2);
}

public function test_adding_trusted_contact_sends_verification_and_stores_only_hash(): void
{
    Notification::fake();

    $user = $this->createPensionGuardUser();

    $this->actingAs($user, 'pension_guard')
        ->post(route('pension-guard.trusted-contacts.store'), [
            'name' => 'Trusted Family Member',
            'email' => 'family@example.com',
            'relationship' => 'Family',
        ])
        ->assertRedirect(route('pension-guard.trusted-contacts.index'));

    $contact = PensionGuardTrustedContact::query()
        ->where('user_id', $user->id)
        ->where('email', 'family@example.com')
        ->firstOrFail();

    $this->assertNotNull($contact->verification_token_hash);
    $this->assertSame(64, strlen($contact->verification_token_hash));
    $this->assertNotNull($contact->verification_expires_at);
    $this->assertTrue($contact->verification_expires_at->isFuture());

    $this->assertNull($contact->email_verified_at);
    $this->assertFalse($contact->consent_given);
    $this->assertNull($contact->consent_given_at);

    Notification::assertSentOnDemand(
        PensionGuardTrustedContactVerification::class
    );
}

public function test_valid_trusted_contact_token_verifies_email_without_granting_consent(): void
{
    $user = $this->createPensionGuardUser();
    $token = \Illuminate\Support\Str::random(64);

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Trusted Contact',
        'email' => 'trusted@example.com',
        'relationship' => 'Family',
        'verification_token_hash' => hash('sha256', $token),
        'verification_expires_at' => now()->addMinutes(60),
        'email_verified_at' => null,
        'consent_given' => false,
        'alert_categories' => [],
        'consent_given_at' => null,
        'consent_revoked_at' => null,
    ]);
    $response = $this->get(route('pension-guard.trusted-contacts.verify', [
        'contact' => $contact->id,
        'token' => $token,
    ]));

   // dump($response->status(), $response->headers->get('Location'));

    $response->assertOk();

    $contact->refresh();

    $this->assertNotNull($contact->email_verified_at);
    $this->assertNull($contact->verification_token_hash);
    $this->assertNull($contact->verification_expires_at);

    // Verification must never grant consent automatically.
    $this->assertFalse($contact->consent_given);
    $this->assertNull($contact->consent_given_at);
}

public function test_expired_trusted_contact_token_is_rejected(): void
{
    $user = $this->createPensionGuardUser();
    $token = \Illuminate\Support\Str::random(64);

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Trusted Contact',
        'email' => 'expired@example.com',
        'relationship' => 'Family',
        'verification_token_hash' => hash('sha256', $token),
        'verification_expires_at' => now()->subMinute(),
        'email_verified_at' => null,
        'consent_given' => false,
        'alert_categories' => [],
    ]);

    $this->get(route('pension-guard.trusted-contacts.verify', [
        'contact' => $contact->id,
        'token' => $token,
    ]))->assertForbidden();

    $this->assertNull($contact->fresh()->email_verified_at);
}

public function test_trusted_contact_verification_token_cannot_be_reused(): void
{
    $user = $this->createPensionGuardUser();
    $token = \Illuminate\Support\Str::random(64);

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Trusted Contact',
        'email' => 'one-time@example.com',
        'relationship' => 'Family',
        'verification_token_hash' => hash('sha256', $token),
        'verification_expires_at' => now()->addMinutes(60),
        'email_verified_at' => null,
        'consent_given' => false,
        'alert_categories' => [],
    ]);

    $url = route('pension-guard.trusted-contacts.verify', [
        'contact' => $contact->id,
        'token' => $token,
    ]);

    $this->get($url)->assertOk();

    // The first successful request consumes the token.
    $this->get($url)->assertForbidden();

    $this->assertNotNull($contact->fresh()->email_verified_at);
    $this->assertNull($contact->fresh()->verification_token_hash);
}

public function test_verified_trusted_contact_can_be_given_selected_alert_consent(): void
{
    $user = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Trusted Contact',
        'email' => 'trusted@example.com',
        'relationship' => 'Family',
        'email_verified_at' => now(),
        'consent_given' => false,
        'alert_categories' => [],
    ]);

    $response = $this->actingAs($user, 'pension_guard')
        ->put(route('pension-guard.trusted-contacts.consent.update', $contact->id), [
            'alert_categories' => ['fraud_warnings', 'breach_alerts'],
            'confirm_consent' => '1',
        ]);

    $response->assertSessionHasNoErrors();

    $contact->refresh();

    $this->assertTrue($contact->consent_given);
    $this->assertNotNull($contact->consent_given_at);
    $this->assertNull($contact->consent_revoked_at);
    $this->assertEqualsCanonicalizing(
        ['fraud_warnings', 'breach_alerts'],
        $contact->alert_categories
    );
}

public function test_unverified_trusted_contact_cannot_be_given_consent(): void
{
    $user = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Unverified Contact',
        'email' => 'unverified@example.com',
        'relationship' => 'Family',
        'email_verified_at' => null,
        'consent_given' => false,
        'alert_categories' => [],
    ]);

    $response = $this->actingAs($user, 'pension_guard')
        ->put(route('pension-guard.trusted-contacts.consent.update', $contact->id), [
            'alert_categories' => ['fraud_warnings'],
            'confirm_consent' => '1',
        ]);

    $contact->refresh();

    $this->assertFalse($contact->consent_given);
    $this->assertNull($contact->consent_given_at);
}

public function test_invalid_trusted_contact_alert_category_is_rejected(): void
{
    $user = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Trusted Contact',
        'email' => 'trusted2@example.com',
        'relationship' => 'Family',
        'email_verified_at' => now(),
        'consent_given' => false,
        'alert_categories' => [],
    ]);

    $response = $this->actingAs($user, 'pension_guard')
        ->put(route('pension-guard.trusted-contacts.consent.update', $contact->id), [
            'alert_categories' => ['invalid_category'],
            'confirm_consent' => '1',
        ]);

$response->assertSessionHasErrors('alert_categories.0');
    $contact->refresh();

    $this->assertFalse($contact->consent_given);
    $this->assertEmpty($contact->alert_categories ?? []);
}

public function test_trusted_contact_consent_can_be_revoked(): void
{
    $user = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Trusted Contact',
        'email' => 'trusted3@example.com',
        'relationship' => 'Family',
        'email_verified_at' => now(),
        'consent_given' => true,
        'alert_categories' => ['fraud_warnings', 'breach_alerts'],
        'consent_given_at' => now(),
        'consent_revoked_at' => null,
    ]);

    $response = $this->actingAs($user, 'pension_guard')
        ->delete(route('pension-guard.trusted-contacts.consent.revoke', $contact->id));

    $response->assertSessionHasNoErrors();

    $contact->refresh();

    $this->assertFalse($contact->consent_given);
    $this->assertEmpty($contact->alert_categories ?? []);
    $this->assertNotNull($contact->consent_revoked_at);
    $this->assertFalse($contact->canReceiveAlert('fraud_warnings'));
}

public function test_user_cannot_change_another_users_trusted_contact_consent(): void
{
    $owner = $this->createPensionGuardUser();
    $otherUser = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $owner->id,
        'name' => 'Private Contact',
        'email' => 'private@example.com',
        'relationship' => 'Family',
        'email_verified_at' => now(),
        'consent_given' => false,
        'alert_categories' => [],
    ]);

    $this->actingAs($otherUser, 'pension_guard')
        ->put(route('pension-guard.trusted-contacts.consent.update', $contact->id), [
            'alert_categories' => ['fraud_warnings'],
            'confirm_consent' => '1',
        ]);

    $contact->refresh();

    $this->assertFalse($contact->consent_given);
    $this->assertEmpty($contact->alert_categories ?? []);
}


public function test_trusted_contact_alert_is_sent_with_active_consent(): void
{
    Notification::fake();

    $user = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Trusted Contact',
        'email' => 'trusted-alert@example.com',
        'relationship' => 'Family',
        'email_verified_at' => now(),
        'consent_given' => true,
        'alert_categories' => ['fraud_warnings'],
        'consent_given_at' => now(),
        'consent_revoked_at' => null,
    ]);

    $sent = app(PensionGuardTrustedContactAlertService::class)->send(
        $user,
        $contact,
        'fraud_warnings',
        'Pension fraud warning',
        'Please review this relevant pension fraud warning.'
    );

    $this->assertTrue($sent);

    Notification::assertSentOnDemand(
        TrustedContactAlertNotification::class,
        function ($notification, $channels, $notifiable) use ($contact) {
            return $notifiable->routes['mail'] === $contact->email
                && in_array('mail', $channels, true);
        }
    );
}

public function test_unverified_contact_cannot_receive_alert(): void
{
    Notification::fake();

    $user = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Unverified Contact',
        'email' => 'unverified-alert@example.com',
        'relationship' => 'Family',
        'email_verified_at' => null,
        'consent_given' => true,
        'alert_categories' => ['fraud_warnings'],
        'consent_given_at' => now(),
        'consent_revoked_at' => null,
    ]);

    $sent = app(PensionGuardTrustedContactAlertService::class)->send(
        $user,
        $contact,
        'fraud_warnings',
        'Pension fraud warning',
        'Test alert.'
    );

    $this->assertFalse($sent);
    Notification::assertNothingSent();
}

public function test_revoked_consent_blocks_trusted_contact_alert(): void
{
    Notification::fake();

    $user = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Revoked Contact',
        'email' => 'revoked-alert@example.com',
        'relationship' => 'Family',
        'email_verified_at' => now(),
        'consent_given' => false,
        'alert_categories' => [],
        'consent_given_at' => now()->subMinute(),
        'consent_revoked_at' => now(),
    ]);

    $sent = app(PensionGuardTrustedContactAlertService::class)->send(
        $user,
        $contact,
        'fraud_warnings',
        'Pension fraud warning',
        'Test alert.'
    );

    $this->assertFalse($sent);
    Notification::assertNothingSent();
}

public function test_trusted_contact_cannot_receive_unapproved_category(): void
{
    Notification::fake();

    $user = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $user->id,
        'name' => 'Limited Consent Contact',
        'email' => 'limited-alert@example.com',
        'relationship' => 'Family',
        'email_verified_at' => now(),
        'consent_given' => true,
        'alert_categories' => ['security_reminders'],
        'consent_given_at' => now(),
        'consent_revoked_at' => null,
    ]);

    $sent = app(PensionGuardTrustedContactAlertService::class)->send(
        $user,
        $contact,
        'fraud_warnings',
        'Pension fraud warning',
        'Test alert.'
    );

    $this->assertFalse($sent);
    Notification::assertNothingSent();
}

public function test_trusted_contact_alert_rejects_contact_owned_by_another_user(): void
{
    Notification::fake();

    $user = $this->createPensionGuardUser();
    $owner = $this->createPensionGuardUser();

    $contact = PensionGuardTrustedContact::create([
        'user_id' => $owner->id,
        'name' => 'Another User Contact',
        'email' => 'other-owner@example.com',
        'relationship' => 'Family',
        'email_verified_at' => now(),
        'consent_given' => true,
        'alert_categories' => ['fraud_warnings'],
        'consent_given_at' => now(),
        'consent_revoked_at' => null,
    ]);

    $sent = app(PensionGuardTrustedContactAlertService::class)->send(
        $user,
        $contact,
        'fraud_warnings',
        'Pension fraud warning',
        'Test alert.'
    );

    $this->assertFalse($sent);
    Notification::assertNothingSent();
}

}
