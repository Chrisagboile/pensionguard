<?php

namespace App\Modules\PensionAccountGuard\Services;

use App\Modules\PensionAccountGuard\Models\PensionGuardAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PensionGuardAuditService
{
    /**
     * Record an audit event using explicitly selected metadata.
     */
    public function record(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        array $metadata = [],
        ?Request $request = null
    ): void {
        try {
            $request ??= request();

            $actorId = Auth::guard('pension_guard')->id();

            $safeMetadata = $this->filterMetadata($metadata);

            PensionGuardAuditLog::create([
                'actor_id' => $actorId,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'metadata' => $safeMetadata,
                'ip_address' => $request->ip(),
                'user_agent' => substr(
                    (string) $request->userAgent(),
                    0,
                    500
                ),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Allow only explicitly approved metadata fields.
     */
    private function filterMetadata(array $metadata): array
    {
        $allowedKeys = [
                'source_name',
                'source_type',
                'is_active',
                'changed_fields',
                'incident_title',
                'affected_organisation',
                'status',

                // Account events
                'account_type',
                'provider_id',
                'fields_changed',

                // Checklist events
                'check_key',

                // Threat-check events
                'scenario',
                'warning_sign_count',
                'risk_level',

                // Trusted-contact events
                'alert_categories',
                'verification_status',

                // Authentication events
                'outcome',
                'authentication_method',

                // Subscription and payment events
                'billing_interval',
                'subscription_status',
                'payment_status',
                'amount_pence',
                'currency',
                'stripe_event_type',
            ];
        $safe = [];

        foreach ($allowedKeys as $key) {
            if (!array_key_exists($key, $metadata)) {
                continue;
            }

            $value = $metadata[$key];


        // Store only approved, bounded text fields.
        $textLimits = [
            'incident_title' => 150,
            'affected_organisation' => 150,
            'source_name' => 120,
            'source_type' => 50,
            'status' => 50,
        ];

        if (array_key_exists($key, $textLimits) && is_string($value)) {
            $value = trim(strip_tags($value));
            $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

            $safe[$key] = mb_substr($value, 0, $textLimits[$key]);

            continue;
        }

            if ($key === 'changed_fields' && is_array($value)) {
                // Keep field names only; never store submitted field values.
                $safe[$key] = array_values(array_filter(
                    $value,
                    fn ($field) => is_string($field)
                        && in_array($field, [
                            'title',
                            'affected_organisation',
                            'description',
                            'source_name',
                            'source_url',
                            'reported_at',
                            'status',
                            'review_notes',
                        ], true)
                ));

                continue;
            }

            if (
                $key === 'is_active' && is_bool($value)
            ) {
                $safe[$key] = $value;
                continue;
            }

            if (
    in_array($key, [
        'provider_id',
        'warning_sign_count',
        'amount_pence',
    ], true)
    && is_numeric($value)
) {
    $safe[$key] = (int) $value;
    continue;
}

if (
    in_array($key, [
        'fields_changed',
        'alert_categories',
    ], true)
    && is_array($value)
) {
    $safe[$key] = array_values(array_filter(
        $value,
        fn ($field) => is_string($field)
            && preg_match('/^[a-z_]{1,60}$/', $field)
    ));
    continue;
}

    if (
        in_array($key, [
            'outcome',
            'authentication_method',
            'account_type',
            'check_key',
            'scenario',
            'risk_level',
            'verification_status',
            'billing_interval',
            'subscription_status',
            'payment_status',
            'currency',
            'stripe_event_type',
        ], true)
        && is_string($value)
    ) {
        $safe[$key] = mb_substr($value, 0, 100);
        continue;
    }

    }

        return $safe;
    }
}
