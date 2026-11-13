<?php

namespace App\Modules\PensionAccountGuard\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\PensionAccountGuard\Models\PensionGuardAccount;
use App\Modules\PensionAccountGuard\Models\PensionGuardAssessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = \App\Modules\PensionAccountGuard\Models\PensionGuardUser::query()
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The supplied credentials are incorrect.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email address before signing in.',
                'code' => 'email_not_verified',
            ], 403);
        }

        $token = $user->createToken(
            $credentials['device_name'],
            ['*'],
            now()->addMinutes(config('sanctum.expiration'))
        );

        return response()->json([
            'message' => 'Signed in successfully.',
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Signed out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function accounts(Request $request): JsonResponse
    {
        $accounts = PensionGuardAccount::query()
            ->where('user_id', $request->user()->getKey())
            ->with(['provider', 'securityChecks'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $accounts->getCollection()->map(fn ($account) => [
                'id' => $account->id,
                'provider_name' => $account->provider_name,
                'display_name' => $account->display_name,
                'account_type' => $account->account_type,
                'notes' => $account->notes,
                'security_checks' => $account->securityChecks->map(fn ($check) => [
                    'id' => $check->id,
                    'check_key' => $check->check_key,
                    'title' => $check->title,
                    'guidance' => $check->guidance,
                    'official_url' => $check->official_url,
                    'is_complete' => (bool) $check->is_complete,
                ])->values(),
                'created_at' => $account->created_at,
            ])->values(),
            'meta' => [
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
            ],
        ]);
    }

    public function assessments(Request $request): JsonResponse
    {
        $assessments = PensionGuardAssessment::query()
            ->where('user_id', $request->user()->getKey())
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $assessments->getCollection()->map(fn ($assessment) => [
                'id' => $assessment->id,
                'answers' => $assessment->answers,
                'score' => $assessment->score,
                'risk_band' => $assessment->risk_band,
                'created_at' => $assessment->created_at,
            ])->values(),
            'meta' => [
                'current_page' => $assessments->currentPage(),
                'last_page' => $assessments->lastPage(),
                'per_page' => $assessments->perPage(),
                'total' => $assessments->total(),
            ],
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->paginate(20);

        return response()->json([
            'data' => $notifications->getCollection()->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ])->values(),
            'unread_count' => $user->unreadNotifications()->count(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    private function userPayload($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => $user->hasVerifiedEmail(),
        ];
    }
}
