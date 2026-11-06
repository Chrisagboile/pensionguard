<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->validateCsrfTokens(except: [
            'pension-guard/stripe/webhook',
        ]);

        $middleware->alias([
             'pension_guard.admin' =>
                \App\Http\Middleware\PensionGuardAdminMiddleware::class,

            'pension_guard.premium' =>
                \App\Modules\PensionAccountGuard\Middleware\EnsurePensionGuardPremium::class,

            'pension_guard.verified' =>
                \App\Modules\PensionAccountGuard\Middleware\EnsurePensionGuardEmailIsVerified::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if (
                $request->is('pension-guard') ||
                $request->is('pension-guard/*')
            ) {
                return route('pension-guard.login');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

    })
    ->create();
