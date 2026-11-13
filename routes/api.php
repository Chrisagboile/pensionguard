<?php

use App\Modules\PensionAccountGuard\Controllers\Api\MobileAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/auth/login', [MobileAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [MobileAuthController::class, 'logout'])
            ->name('auth.logout');

        Route::get('/me', [MobileAuthController::class, 'me'])
            ->name('me');

        Route::get('/accounts', [MobileAuthController::class, 'accounts'])
            ->name('accounts.index');

        Route::get('/assessments', [MobileAuthController::class, 'assessments'])
            ->name('assessments.index');

        Route::get('/notifications', [MobileAuthController::class, 'notifications'])
            ->name('notifications.index');
    });
});
