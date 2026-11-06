<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PensionGuardPasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('pension-guard.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $status = Password::broker('pension_guard')
            ->sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors([
                'email' => 'Please wait before requesting another reset link.',
            ]);
        }

        return back()->with(
            'status',
            'If an account exists for that email address, a password-reset link has been sent.'
        );
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('pension-guard.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $status = Password::broker('pension_guard')->reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user) use ($request) {
                $user->password = $request->input('password');
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('pension-guard.login')
                ->with(
                    'status',
                    'Your password has been reset. You can now sign in.'
                );
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'This password-reset link is invalid or has expired. Please request another one.',
            ]);
    }
}
