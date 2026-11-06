<?php

namespace Tests\Feature\PensionAccountGuard;

use App\Modules\PensionAccountGuard\Models\PensionGuardAuditLog;
use App\Modules\PensionAccountGuard\Models\PensionGuardSecurityIncident;
use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PensionGuardSecurityIncidentTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): PensionGuardUser
    {
        return PensionGuardUser::create([
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'TestPassword123!',
            'is_admin' => true,
        ]);
    }

    private function validIncidentData(): array
    {
        return [
            'title' => 'Example pension provider incident',
            'affected_organisation' => 'Example Pension Provider',
            'description' => 'A test incident recorded for validation.',
            'source_name' => 'Official provider notice',
            'source_url' => 'https://example.com/security-notice',
            'reported_at' => '2026-10-20',
            'status' => 'unverified',
            'review_notes' => 'Awaiting independent verification.',
        ];
    }

    public function test_admin_can_create_security_incident(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'pension_guard')
            ->post(
                route('pension-guard.admin.incidents.store'),
                $this->validIncidentData()
            );

        $response->assertRedirect(
            route('pension-guard.admin.incidents.index')
        );

        $this->assertDatabaseHas('pension_guard_security_incidents', [
            'title' => 'Example pension provider incident',
            'affected_organisation' => 'Example Pension Provider',
            'status' => 'unverified',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);
    }

    public function test_admin_cannot_create_incident_with_invalid_url(): void
    {
        $admin = $this->createAdmin();

        $data = $this->validIncidentData();
        $data['source_url'] = 'not-a-valid-url';

        $this->actingAs($admin, 'pension_guard')
            ->post(route('pension-guard.admin.incidents.store'), $data)
            ->assertSessionHasErrors('source_url');

        $this->assertDatabaseCount('pension_guard_security_incidents', 0);
    }

    public function test_admin_cannot_create_incident_with_invalid_status(): void
    {
        $admin = $this->createAdmin();

        $data = $this->validIncidentData();
        $data['status'] = 'approved_without_review';

        $this->actingAs($admin, 'pension_guard')
            ->post(route('pension-guard.admin.incidents.store'), $data)
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('pension_guard_security_incidents', 0);
    }

    public function test_admin_can_update_security_incident(): void
    {
        $admin = $this->createAdmin();

        $incident = PensionGuardSecurityIncident::create(
            array_merge($this->validIncidentData(), [
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ])
        );

        $data = $this->validIncidentData();
        $data['title'] = 'Updated pension provider incident';
        $data['status'] = 'under_review';

        $this->actingAs($admin, 'pension_guard')
            ->put(
                route('pension-guard.admin.incidents.update', $incident),
                $data
            )
            ->assertRedirect(
                route('pension-guard.admin.incidents.index')
            );

        $this->assertDatabaseHas('pension_guard_security_incidents', [
            'id' => $incident->id,
            'title' => 'Updated pension provider incident',
            'status' => 'under_review',
            'updated_by' => $admin->id,
        ]);
    }

public function test_creating_incident_records_audit_event_without_source_url(): void
{
    $admin = $this->createAdmin();

    $this->actingAs($admin, 'pension_guard')
        ->post(
            route('pension-guard.admin.incidents.store'),
            $this->validIncidentData()
        )
        ->assertRedirect(
            route('pension-guard.admin.incidents.index')
        );

    $incident = PensionGuardSecurityIncident::firstOrFail();

    $audit = PensionGuardAuditLog::where(
        'action',
        'security_incident.created'
    )->firstOrFail();

    $this->assertSame($admin->id, $audit->actor_id);
    $this->assertSame('security_incident', $audit->target_type);
    $this->assertSame($incident->id, $audit->target_id);

    $metadata = $audit->metadata ?? [];

    $this->assertSame(
        'Example pension provider incident',
        $metadata['incident_title'] ?? null
    );

    $this->assertArrayNotHasKey('source_url', $metadata);
    $this->assertArrayNotHasKey('description', $metadata);
}

public function test_updating_incident_records_changed_field_names_only(): void
{
    $admin = $this->createAdmin();

    $incident = PensionGuardSecurityIncident::create(
        array_merge($this->validIncidentData(), [
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ])
    );

    $data = $this->validIncidentData();
    $data['title'] = 'Updated pension incident';
    $data['description'] = 'Updated confidential test description.';
    $data['source_url'] = 'https://example.com/updated-notice';

    $this->actingAs($admin, 'pension_guard')
        ->put(
            route('pension-guard.admin.incidents.update', $incident),
            $data
        )
        ->assertRedirect(
            route('pension-guard.admin.incidents.index')
        );

    $audit = PensionGuardAuditLog::where(
        'action',
        'security_incident.updated'
    )->firstOrFail();

    $this->assertSame($admin->id, $audit->actor_id);
    $this->assertSame($incident->id, $audit->target_id);

    $metadata = $audit->metadata ?? [];
    $changedFields = $metadata['changed_fields'] ?? [];

    $this->assertContains('title', $changedFields);
    $this->assertContains('description', $changedFields);
    $this->assertContains('source_url', $changedFields);

    $this->assertArrayNotHasKey('source_url', $metadata);
    $this->assertArrayNotHasKey('description', $metadata);
    $this->assertStringNotContainsString(
        'Updated confidential test description.',
        json_encode($metadata)
    );
}

}
