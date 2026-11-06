<?php

namespace App\Modules\PensionAccountGuard\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePensionGuardEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('pension_guard')->user();

        if (! $user) {
            return redirect()->route('pension-guard.login');
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route(
                'pension-guard.verification.notice'
            );
        }

        return $next($request);
    }
}
