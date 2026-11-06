<?php

namespace Tests\Feature\PensionAccountGuard;

use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PensionGuardAdminTest extends TestCase
{
    use RefreshDatabase;

    private function createPensionGuardUser(
        bool $isAdmin = false
    ): PensionGuardUser {
        return PensionGuardUser::create([
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'TestPassword123!',
            'is_admin' => $isAdmin,
        ]);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $this->get(route('pension-guard.admin.index'))
            ->assertRedirect('/pension-guard/login');
    }

    public function test_guest_cannot_access_source_management(): void
    {
        $this->get(route('pension-guard.admin.sources.index'))
            ->assertRedirect('/pension-guard/login');
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = $this->createPensionGuardUser();

        $this->actingAs($user, 'pension_guard')
            ->get(route('pension-guard.admin.index'))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_access_source_management(): void
    {
        $user = $this->createPensionGuardUser();

        $this->actingAs($user, 'pension_guard')
            ->get(route('pension-guard.admin.sources.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->createPensionGuardUser(true);

        $this->actingAs($admin, 'pension_guard')
            ->get(route('pension-guard.admin.index'))
            ->assertOk()
            ->assertSee('Pension Account Guard Administration');
    }

    public function test_admin_can_access_source_management(): void
    {
        $admin = $this->createPensionGuardUser(true);

        $this->actingAs($admin, 'pension_guard')
            ->get(route('pension-guard.admin.sources.index'))
            ->assertOk()
            ->assertSee('Manage Official Guidance Sources');
    }


    public function test_guest_cannot_access_audit_log(): void
    {
        $this->get(route('pension-guard.admin.audit.index'))
            ->assertRedirect('/pension-guard/login');
    }

    public function test_non_admin_cannot_access_audit_log(): void
    {
        $user = $this->createPensionGuardUser();

        $this->actingAs($user, 'pension_guard')
            ->get(route('pension-guard.admin.audit.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_audit_log(): void
    {
        $admin = $this->createPensionGuardUser(true);

        $this->actingAs($admin, 'pension_guard')
            ->get(route('pension-guard.admin.audit.index'))
            ->assertOk();
    }

    public function test_guest_cannot_access_security_incident_register(): void
    {
        $this->get(route('pension-guard.admin.incidents.index'))
            ->assertRedirect('/pension-guard/login');
    }

    public function test_non_admin_cannot_access_security_incident_register(): void
    {
        $user = $this->createPensionGuardUser();

        $this->actingAs($user, 'pension_guard')
            ->get(route('pension-guard.admin.incidents.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_security_incident_register(): void
    {
       $admin = $this->createPensionGuardUser(true);

       $this->actingAs($admin, 'pension_guard')
          ->get(route('pension-guard.admin.incidents.index'))
          ->assertOk()
          ->assertSee('Security Incident Register');
    }

    public function test_non_admin_cannot_create_security_incident(): void
    {
        $user = $this->createPensionGuardUser();

        $this->actingAs($user, 'pension_guard')
            ->get(route('pension-guard.admin.incidents.create'))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_submit_security_incident(): void
    {
        $user = $this->createPensionGuardUser();

        $this->actingAs($user, 'pension_guard')
            ->post(route('pension-guard.admin.incidents.store'), [])
            ->assertForbidden();
    }


}
