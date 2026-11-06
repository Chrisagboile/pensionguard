<?php

namespace App\Modules\PensionAccountGuard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PensionAccountGuard\Models\PensionGuardUser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PensionGuardEmailVerificationController extends Controller
{
    public function notice()
    {
        $user = Auth::guard('pension_guard')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('pension-guard.dashboard');
        }

        return view('pension-guard.auth.verify-email');
    }

    public function verify(Request $request, string $id, string $hash)
    {
        $user = PensionGuardUser::findOrFail($id);

        if (! hash_equals(
            sha1($user->getEmailForVerification()),
            $hash
        )) {
            abort(403, 'Invalid email verification link.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        return redirect()
            ->route('pension-guard.login')
            ->with(
                'status',
                'Your email address has been verified successfully. You can now sign in.'
            );
    }

    public function resend(Request $request)
    {
        $user = Auth::guard('pension_guard')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('pension-guard.dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with(
            'status',
            'A new verification email has been sent.'
        );
    }
}
