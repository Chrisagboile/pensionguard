<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use App\Modules\PensionAccountGuard\Models\PensionGuardLegalAcceptance;
use App\Modules\PensionAccountGuard\Services\PensionGuardReminderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class PensionGuardAuthController extends Controller
{
    public function showLogin()
    {
        return view('pension-guard.auth.login');
    }

    public function login(
        Request $request,
        PensionGuardReminderService $reminderService
    ) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('pension_guard')->attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'The email address or password is incorrect.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::guard('pension_guard')->user();

        if (! $user instanceof PensionGuardUser) {
            Auth::guard('pension_guard')->logout();

            return redirect()->route('pension-guard.login');
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route(
                'pension-guard.verification.notice'
            );
        }

        try {
            $reminderService->remindAboutIncompleteChecks($user);
        } catch (Throwable $exception) {
            report($exception);
        }

        return redirect()->intended(
            route('pension-guard.dashboard')
        );
    }

    public function showRegister()
    {
        return view('pension-guard.auth.register');
    }

    public function register(
        Request $request,
        PensionGuardReminderService $reminderService
    ) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('pension_guard_users', 'email'),
            ],

            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
            ],

            'terms_accepted' => [
                'required',
                'accepted',
            ],

            'privacy_acknowledged' => [
                'required',
                'accepted',
            ],
        ]);

        $termsVersion = '1.0';
        $privacyVersion = '1.0';

        $user = DB::transaction(function () use (
            $validated,
            $termsVersion,
            $privacyVersion
        ) {
            $username = self::generateUniqueUsername(
                $validated['name']
            );

            $user = PensionGuardUser::create([
                'name' => $validated['name'],
                'username' => $username,
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            PensionGuardLegalAcceptance::create([
                'pension_guard_user_id' => $user->id,
                'terms_version' => $termsVersion,
                'terms_accepted_at' => now(),
                'privacy_version' => $privacyVersion,
                'privacy_acknowledged_at' => now(),
            ]);

            return $user;
        });

        Auth::guard('pension_guard')->login($user);

        $request->session()->regenerate();

        $user->sendEmailVerificationNotification();

        try {
            $reminderService->remindAboutIncompleteChecks($user);
        } catch (Throwable $exception) {
            report($exception);
        }

        return redirect()->route(
            'pension-guard.verification.notice'
        );
    }

    private static function generateUniqueUsername(string $name): string
    {
        $base = preg_replace(
            '/[^A-Za-z0-9]/',
            '',
            Str::ascii(trim($name))
        );

        if (empty($base)) {
            $base = 'User';
        }

        $base = substr($base, 0, 255);

        $username = $base;
        $suffix = 2;

        while (
            PensionGuardUser::where('username', $username)->exists()
        ) {
            $suffixText = (string) $suffix;

            $username = substr(
                $base,
                0,
                255 - strlen($suffixText)
            ) . $suffixText;

            $suffix++;
        }

        return $username;
    }

    public function logout(Request $request)
    {
        Auth::guard('pension_guard')->logout();

        $request->session()->regenerate();

        return redirect()->route('pension-guard.dashboard');
    }
}