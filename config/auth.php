<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'pension_guard'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'pension_guard'),
    ],

    'guards' => [
        'pension_guard' => [
            'driver' => 'session',
            'provider' => 'pension_guard_users',
        ],
    ],

    'providers' => [
        'pension_guard_users' => [
            'driver' => 'eloquent',
            'model' => App\Modules\PensionAccountGuard\Models\PensionGuardUser::class,
        ],
    ],

    'passwords' => [
        'pension_guard' => [
            'provider' => 'pension_guard_users',
            'table' => 'pension_guard_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
