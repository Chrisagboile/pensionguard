<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pension Account Guard Premium Billing
    |--------------------------------------------------------------------------
    */

    'enabled' => env('PENSION_GUARD_PREMIUM_ENABLED', false),

    'currency' => env('PENSION_GUARD_CURRENCY', 'gbp'),

    /*
    |--------------------------------------------------------------------------
    | Subscription Prices
    |--------------------------------------------------------------------------
    */

    'prices' => [
        'monthly' => env('PENSION_GUARD_STRIPE_MONTHLY_PRICE'),
        'annual' => env('PENSION_GUARD_STRIPE_ANNUAL_PRICE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dedicated Pension Guard Stripe Credentials
    |--------------------------------------------------------------------------
    |
    | These variables are separate from the existing CyberReadyAI Stripe
    | credentials. Do not replace the existing STRIPE_KEY or STRIPE_SECRET.
    |
    */

     'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkout Redirect Routes
    |--------------------------------------------------------------------------
    */

    'checkout' => [
        'success_route' => 'pension-guard.subscriptions.success',
        'cancel_route' => 'pension-guard.subscriptions.cancelled',
    ],

];
