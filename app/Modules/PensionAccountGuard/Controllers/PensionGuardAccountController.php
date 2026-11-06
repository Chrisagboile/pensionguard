<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PensionAccountGuard\Models\PensionGuardAccount;
use App\Modules\PensionAccountGuard\Models\PensionGuardProvider;
use App\Modules\PensionAccountGuard\Services\PensionGuardAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PensionGuardAccountController extends Controller
{
    /**
     * Display the signed-in user's pension records.
     */
    public function index()
    {
        $userId = Auth::guard('pension_guard')->id();

        $accounts = PensionGuardAccount::where('user_id', $userId)
            ->with(['provider', 'securityChecks'])
            ->latest()
            ->get();

        return view('pension-guard.accounts.index', compact('accounts'));
    }

    /**
     * Show the form for adding a pension record.
     */
    public function create()
    {
        $providers = PensionGuardProvider::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pension-guard.accounts.create', compact('providers'));
    }

    /**
     * Save a pension record and create its security checklist.
     */
    public function store(
        Request $request,
        PensionGuardAuditService $audit
    ) {
        $validated = $request->validate([
            'provider_id' => [
                'nullable',
                'integer',
                Rule::exists('pension_guard_providers', 'id')
                    ->where('is_active', true),
            ],
            'provider_name' => [
                'nullable',
                'string',
                'max:150',
                'required_without:provider_id',
            ],
            'display_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'account_type' => [
                'required',
                Rule::in([
                    'workplace',
                    'personal',
                    'sipp',
                    'defined_benefit',
                    'other',
                ]),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $userId = Auth::guard('pension_guard')->id();

        $provider = !empty($validated['provider_id'])
            ? PensionGuardProvider::findOrFail($validated['provider_id'])
            : null;

        $account = DB::transaction(function () use (
            $validated,
            $provider,
            $userId
        ) {
            $account = PensionGuardAccount::create([
                'user_id' => $userId,
                'provider_id' => $provider?->id,
                'provider_name' => $provider?->name
                    ?? trim($validated['provider_name']),
                'display_name' => $validated['display_name'] ?? null,
                'account_type' => $validated['account_type'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $checks = [
                [
                    'check_key' => 'mfa',
                    'title' => 'Enable two-step verification',
                    'guidance' => 'Check whether your provider offers multi-factor authentication and enable it where available.',
                ],
                [
                    'check_key' => 'email',
                    'title' => 'Secure your linked email account',
                    'guidance' => 'Use a unique password, enable multi-factor authentication and review your email recovery options.',
                ],
                [
                    'check_key' => 'contact',
                    'title' => 'Review contact details',
                    'guidance' => 'Check that your provider has your current contact details using its official website or another verified contact channel.',
                ],
                [
                    'check_key' => 'recovery',
                    'title' => 'Review account recovery settings',
                    'guidance' => 'Review the available recovery methods and make sure they are current and protected.',
                ],
                [
                    'check_key' => 'alerts',
                    'title' => 'Review security notifications',
                    'guidance' => 'Check whether your provider offers account activity or security alerts and enable useful notifications.',
                ],
            ];

            foreach ($checks as $check) {
                $account->securityChecks()->create([
                    'check_key' => $check['check_key'],
                    'title' => $check['title'],
                    'guidance' => $check['guidance'],
                    'official_url' => $provider?->security_url
                        ?? $provider?->website_url,
                    'is_complete' => false,
                ]);
            }

            return $account;
        });

        // Record successful account creation after the transaction.
        $audit->record(
            'account.created',
            'pension_guard_account',
            $account->id,
            [
                'account_type' => $account->account_type,
                'provider_id' => $account->provider_id,
            ]
        );

        return redirect()
            ->route('pension-guard.accounts.show', $account)
            ->with(
                'status',
                'Pension record added. Start with your security checklist.'
            );
    }

    /**
     * Display a pension record belonging to the signed-in user.
     */
    public function show(PensionGuardAccount $account)
    {
        $account = PensionGuardAccount::where(
            'user_id',
            Auth::guard('pension_guard')->id()
        )
            ->with(['provider', 'securityChecks'])
            ->findOrFail($account->id);

        return view('pension-guard.accounts.show', compact('account'));
    }

    /**
     * Display the account editing form.
     */
    public function edit(PensionGuardAccount $account)
    {
        $userId = Auth::guard('pension_guard')->id();

        $account = PensionGuardAccount::where('user_id', $userId)
            ->findOrFail($account->id);

        $providers = PensionGuardProvider::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'pension-guard.accounts.edit',
            compact('account', 'providers')
        );
    }

    /**
     * Update an existing pension account.
     */
    public function update(
        Request $request,
        PensionGuardAccount $account,
        PensionGuardAuditService $audit
    ) {
        $userId = Auth::guard('pension_guard')->id();

        $account = PensionGuardAccount::where('user_id', $userId)
            ->findOrFail($account->id);

        $validated = $request->validate([
            'provider_id' => [
                'nullable',
                'integer',
                Rule::exists('pension_guard_providers', 'id')
                    ->where('is_active', true),
            ],
            'provider_name' => [
                'required_without:provider_id',
                'nullable',
                'string',
                'max:150',
            ],
            'display_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'account_type' => [
                'required',
                Rule::in([
                    'workplace',
                    'personal',
                    'sipp',
                    'defined_benefit',
                    'other',
                ]),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $provider = !empty($validated['provider_id'])
            ? PensionGuardProvider::findOrFail($validated['provider_id'])
            : null;

        $newValues = [
            'provider_id' => $provider?->id,
            'provider_name' => $provider?->name
                ?? trim($validated['provider_name'] ?? ''),
            'display_name' => $validated['display_name'] ?? null,
            'account_type' => $validated['account_type'],
            'notes' => $validated['notes'] ?? null,
        ];

        $changedFields = [];

        foreach ($newValues as $field => $value) {
            if ($account->{$field} != $value) {
                $changedFields[] = $field;
            }
        }

        if ($changedFields !== []) {
            $account->update($newValues);

            $audit->record(
                'account.updated',
                'pension_guard_account',
                $account->id,
                [
                    'fields_changed' => $changedFields,
                    'account_type' => $account->account_type,
                    'provider_id' => $account->provider_id,
                ]
            );
        }

        return redirect()
            ->route('pension-guard.accounts.show', $account)
            ->with('success', 'Pension record updated successfully.');
    }

    /**
     * Delete an account owned by the authenticated user.
     */
    public function destroy(
        PensionGuardAccount $account,
        PensionGuardAuditService $audit
    ) {
        $userId = Auth::guard('pension_guard')->id();

        $account = PensionGuardAccount::where('user_id', $userId)
            ->findOrFail($account->id);

        DB::transaction(function () use ($account, $audit) {
            // Record the event before deletion because checklist records
            // are removed automatically through the foreign-key cascade.
            $audit->record(
                'account.deleted',
                'pension_guard_account',
                $account->id,
                [
                    'account_type' => $account->account_type,
                    'provider_id' => $account->provider_id,
                ]
            );

            $account->delete();
        });

        return redirect()
            ->route('pension-guard.accounts.index')
            ->with('success', 'Pension record deleted successfully.');
    }
}