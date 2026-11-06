<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use App\Modules\PensionAccountGuard\Services\PensionGuardReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            'username' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('pension_guard_users', 'username'),
            ],

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
        ]);

        $user = PensionGuardUser::create($validated);

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

    public function logout(Request $request)
    {
        Auth::guard('pension_guard')->logout();

        $request->session()->regenerate();

        return redirect()->route('pension-guard.dashboard');
    }
}
