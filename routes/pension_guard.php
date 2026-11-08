
<?php

use App\Modules\PensionAccountGuard\Controllers\PensionGuardAccountController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardAdminController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardAssessmentController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardAuditController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardAuthController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardChecklistController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardContactController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardEmailVerificationController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardNotificationController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardPasswordResetController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardSecurityIncidentController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardSessionAvailabilityController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardSessionBookingController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardSourceAdminController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardSourceController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardSubscriptionController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardThreatController;
use App\Modules\PensionAccountGuard\Controllers\PensionGuardTrustedContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pension Account Guard Routes
|--------------------------------------------------------------------------
*/

Route::prefix('pension-guard')
    ->name('pension-guard.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Stripe Webhook
        |--------------------------------------------------------------------------
        */

        Route::post('/stripe/webhook', [
            \App\Modules\PensionAccountGuard\Controllers\PensionGuardStripeWebhookController::class,
            'handle',
        ])->name('stripe.webhook');


        /*
        |--------------------------------------------------------------------------
        | Public Pages
        |--------------------------------------------------------------------------
        */

        Route::get('/', fn () => view('pension-guard.dashboard'))
            ->name('dashboard');

        Route::get('/threat-checker', [
            PensionGuardThreatController::class,
            'create',
        ])->name('threat-checker.create');

        Route::post('/threat-checker', [
            PensionGuardThreatController::class,
            'store',
        ])->middleware('throttle:10,1')
          ->name('threat-checker.store');

        Route::get('/sources', [
            PensionGuardSourceController::class,
            'index',
        ])->name('sources.index');


        /*
        |--------------------------------------------------------------------------
        | Public Trusted Contact Verification
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/trusted-contacts/{contact}/verify/{token}',
            [PensionGuardTrustedContactController::class, 'verify']
        )
            ->whereNumber('contact')
            ->name('trusted-contacts.verify');


        /*
        |--------------------------------------------------------------------------
        | Login and Registration
        |--------------------------------------------------------------------------
        */

        Route::get('/login', [
            PensionGuardAuthController::class,
            'showLogin',
        ])->middleware('guest:pension_guard')
          ->name('login');

        Route::post('/login', [
            PensionGuardAuthController::class,
            'login',
        ])->middleware(['guest:pension_guard', 'throttle:10,1'])
          ->name('login.submit');

        Route::get('/register', [
            PensionGuardAuthController::class,
            'showRegister',
        ])->middleware('guest:pension_guard')
          ->name('register');

        Route::post('/register', [
            PensionGuardAuthController::class,
            'register',
        ])->middleware(['guest:pension_guard', 'throttle:5,1'])
          ->name('register.submit');


        /*
        |--------------------------------------------------------------------------
        | Password Reset
        |--------------------------------------------------------------------------
        */

        Route::get('/forgot-password', [
            PensionGuardPasswordResetController::class,
            'showLinkRequestForm',
        ])->middleware('guest:pension_guard')
          ->name('password.request');

        Route::post('/forgot-password', [
            PensionGuardPasswordResetController::class,
            'sendResetLinkEmail',
        ])->middleware(['guest:pension_guard', 'throttle:5,1'])
          ->name('password.email');

        Route::get('/reset-password/{token}', [
            PensionGuardPasswordResetController::class,
            'showResetForm',
        ])->middleware('guest:pension_guard')
          ->name('password.reset');

        Route::post('/reset-password', [
            PensionGuardPasswordResetController::class,
            'reset',
        ])->middleware(['guest:pension_guard', 'throttle:5,1'])
          ->name('password.update');


        /*
        |--------------------------------------------------------------------------
        | Email Verification
        |--------------------------------------------------------------------------
        */

        Route::get('/email/verify/{id}/{hash}', [
            PensionGuardEmailVerificationController::class,
            'verify',
        ])->middleware('signed')
          ->whereNumber('id')
          ->name('verification.verify');

        Route::middleware('auth:pension_guard')->group(function () {

            Route::get('/email/verify', [
                PensionGuardEmailVerificationController::class,
                'notice',
            ])->name('verification.notice');

            Route::post('/email/verification-notification', [
                PensionGuardEmailVerificationController::class,
                'resend',
            ])->middleware('throttle:6,1')
              ->name('verification.send');
        });


        /*
        |--------------------------------------------------------------------------
        | Public Pension Security Assessments
        |--------------------------------------------------------------------------
        */

        Route::get('/assessments/create', [
            PensionGuardAssessmentController::class,
            'create',
        ])->name('assessments.create');

        Route::post('/assessments', [
            PensionGuardAssessmentController::class,
            'store',
        ])->middleware('throttle:10,1')
          ->name('assessments.store');

        Route::get('/assessments/result', [
            PensionGuardAssessmentController::class,
            'result',
        ])->name('assessments.result');


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', [
            PensionGuardAuthController::class,
            'logout',
        ])->middleware('auth:pension_guard')
          ->name('logout');


        /*
        |--------------------------------------------------------------------------
        | Administrator Routes
        |--------------------------------------------------------------------------
        */

        Route::get('/admin', [
            PensionGuardAdminController::class,
            'index',
        ])->middleware([
            'auth:pension_guard',
            'pension_guard.verified',
            'pension_guard.admin',
        ])->name('admin.index');


        // Administrator audit log

        Route::get('/admin/audit', [
            PensionGuardAuditController::class,
            'index',
        ])->middleware([
            'auth:pension_guard',
            'pension_guard.verified',
            'pension_guard.admin',
        ])->name('admin.audit.index');


        // Administrator session availability management

        Route::prefix('/admin/session-availability')
            ->name('admin.availability.')
            ->middleware([
                'auth:pension_guard',
                'pension_guard.verified',
                'pension_guard.admin',
            ])
            ->controller(PensionGuardSessionAvailabilityController::class)
            ->group(function () {

                Route::get('/', 'index')
                    ->name('index');

                Route::post('/', 'store')
                    ->middleware('throttle:10,1')
                    ->name('store');

                Route::post('/{availability}/toggle', 'toggle')
                    ->whereNumber('availability')
                    ->name('toggle');
            });


        // Administrator official-source management

        Route::prefix('/admin/sources')
            ->name('admin.sources.')
            ->middleware([
                'auth:pension_guard',
                'pension_guard.verified',
                'pension_guard.admin',
            ])
            ->controller(PensionGuardSourceAdminController::class)
            ->group(function () {

                Route::get('/', 'index')->name('index');

                Route::get('/create', 'create')->name('create');

                Route::post('/', 'store')->name('store');

                Route::get('/{source}/edit', 'edit')->name('edit');

                Route::put('/{source}', 'update')->name('update');
            });


        // Administrator security incident register

        Route::prefix('/admin/incidents')
            ->name('admin.incidents.')
            ->middleware([
                'auth:pension_guard',
                'pension_guard.verified',
                'pension_guard.admin',
            ])
            ->controller(PensionGuardSecurityIncidentController::class)
            ->group(function () {

                Route::get('/', 'index')->name('index');

                Route::get('/create', 'create')->name('create');

                Route::post('/', 'store')
                    ->middleware('throttle:10,1')
                    ->name('store');

                Route::get('/{incident}/edit', 'edit')
                    ->whereNumber('incident')
                    ->name('edit');

                Route::put('/{incident}', 'update')
                    ->whereNumber('incident')
                    ->middleware('throttle:10,1')
                    ->name('update');
            });


        /*
        |--------------------------------------------------------------------------
        | Authenticated and Email-Verified Customer Routes
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'auth:pension_guard',
            'pension_guard.verified',
        ])->group(function () {


            /*
            |--------------------------------------------------------------------------
            | Pension Protection Session Bookings
            |--------------------------------------------------------------------------
            */

            Route::prefix('/sessions')
                ->name('sessions.')
                ->controller(PensionGuardSessionBookingController::class)
                ->group(function () {

                    // Customer booking history
                    Route::get('/', 'index')
                        ->name('index');

                    // Booking calendar page
                    Route::get('/book', 'create')
                        ->name('create');

                    // Calendar availability data (must be before {booking})
                    Route::get('/availability', 'availability')
                        ->middleware('throttle:60,1')
                        ->name('availability');

                    // Submit appointment and start Stripe Checkout
                    Route::post('/book', 'store')
                        ->middleware('throttle:5,1')
                        ->name('store');

                    // Stripe payment success return
                    Route::get('/{booking}/success', 'success')
                        ->whereNumber('booking')
                        ->name('success');

                    // Stripe payment cancellation return
                    Route::get('/{booking}/cancelled', 'cancelled')
                        ->whereNumber('booking')
                        ->name('cancelled');

                    // Individual booking details
                    Route::get('/{booking}', 'show')
                        ->whereNumber('booking')
                        ->name('show');
                });


            /*
            |--------------------------------------------------------------------------
            | Pension Accounts
            |--------------------------------------------------------------------------
            */

            Route::get('/accounts', [
                PensionGuardAccountController::class,
                'index',
            ])->name('accounts.index');

            Route::get('/accounts/create', [
                PensionGuardAccountController::class,
                'create',
            ])->name('accounts.create');

            Route::post('/accounts', [
                PensionGuardAccountController::class,
                'store',
            ])->name('accounts.store');

            Route::get('/accounts/{account}', [
                PensionGuardAccountController::class,
                'show',
            ])->whereNumber('account')
              ->name('accounts.show');

            Route::get('/accounts/{account}/edit', [
                PensionGuardAccountController::class,
                'edit',
            ])->whereNumber('account')
              ->name('accounts.edit');

            Route::put('/accounts/{account}', [
                PensionGuardAccountController::class,
                'update',
            ])->whereNumber('account')
              ->name('accounts.update');

            Route::delete('/accounts/{account}', [
                PensionGuardAccountController::class,
                'destroy',
            ])->whereNumber('account')
              ->name('accounts.destroy');


            /*
            |--------------------------------------------------------------------------
            | Pension Account Security Checklist
            |--------------------------------------------------------------------------
            */

            Route::get('/accounts/{account}/checklist', [
                PensionGuardChecklistController::class,
                'index',
            ])->whereNumber('account')
              ->name('checklist.index');

            Route::patch('/accounts/{account}/checklist/{check}', [
                PensionGuardChecklistController::class,
                'update',
            ])->whereNumber('account')
              ->whereNumber('check')
              ->name('checklist.update');


            /*
            |--------------------------------------------------------------------------
            | Saved Security Assessments
            |--------------------------------------------------------------------------
            */

            Route::get('/assessments', [
                PensionGuardAssessmentController::class,
                'history',
            ])->name('assessments.history');

            Route::get('/assessments/{assessment}', [
                PensionGuardAssessmentController::class,
                'show',
            ])->whereNumber('assessment')
              ->name('assessments.show');


            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            Route::get('/notifications', [
                PensionGuardNotificationController::class,
                'index',
            ])->name('notifications.index');

            Route::patch('/notifications/{notification}/read', [
                PensionGuardNotificationController::class,
                'markAsRead',
            ])->name('notifications.read');


            /*
            |--------------------------------------------------------------------------
            | Trusted Contacts
            |--------------------------------------------------------------------------
            */

            Route::get('/trusted-contacts', [
                PensionGuardTrustedContactController::class,
                'index',
            ])->name('trusted-contacts.index');

            Route::get('/trusted-contacts/create', [
                PensionGuardTrustedContactController::class,
                'create',
            ])->name('trusted-contacts.create');

            Route::post('/trusted-contacts', [
                PensionGuardTrustedContactController::class,
                'store',
            ])->name('trusted-contacts.store');

            Route::delete('/trusted-contacts/{contact}', [
                PensionGuardTrustedContactController::class,
                'destroy',
            ])->whereNumber('contact')
              ->name('trusted-contacts.destroy');

            Route::post(
                '/trusted-contacts/{contact}/resend-verification',
                [PensionGuardTrustedContactController::class, 'resendVerification']
            )
                ->whereNumber('contact')
                ->middleware('throttle:5,1')
                ->name('trusted-contacts.resend-verification');

            Route::put('/trusted-contacts/{contact}/consent', [
                PensionGuardTrustedContactController::class,
                'updateConsent',
            ])->whereNumber('contact')
              ->name('trusted-contacts.consent.update');

            Route::delete('/trusted-contacts/{contact}/consent', [
                PensionGuardTrustedContactController::class,
                'revokeConsent',
            ])->whereNumber('contact')
              ->name('trusted-contacts.consent.revoke');


            /*
            |--------------------------------------------------------------------------
            | Premium Subscriptions
            |--------------------------------------------------------------------------
            */

            Route::get('/subscriptions', [
                PensionGuardSubscriptionController::class,
                'index',
            ])->name('subscriptions.index');

            Route::post('/subscriptions/checkout', [
                PensionGuardSubscriptionController::class,
                'checkout',
            ])->middleware('throttle:5,1')
              ->name('subscriptions.checkout');

            Route::get('/subscriptions/success', [
                PensionGuardSubscriptionController::class,
                'success',
            ])->name('subscriptions.success');

            Route::get('/subscriptions/cancelled', [
                PensionGuardSubscriptionController::class,
                'cancelled',
            ])->name('subscriptions.cancelled');

            Route::post('/subscriptions/billing-portal', [
                PensionGuardSubscriptionController::class,
                'billingPortal',
            ])->middleware('throttle:5,1')
              ->name('subscriptions.billing-portal');

            Route::post(
                '/subscriptions/{subscription}/cancel',
                [PensionGuardSubscriptionController::class, 'cancel']
            )->whereNumber('subscription')
              ->middleware('throttle:5,1')
              ->name('subscriptions.cancel');
        });
    });


/*
|--------------------------------------------------------------------------
| Public Legal Pages
|--------------------------------------------------------------------------
*/

Route::view('/privacy-policy', 'pension-guard.legal.privacy-policy')
    ->name('privacy-policy');

Route::view('/terms-and-conditions', 'pension-guard.legal.terms-and-conditions')
    ->name('terms-and-conditions');


/*
|--------------------------------------------------------------------------
| Public Company Information Pages
|--------------------------------------------------------------------------
*/

Route::view('/about', 'pension-guard.public.about')
    ->name('about');

Route::view('/pricing', 'pension-guard.public.pricing')
    ->name('pricing');

Route::get('/contact', [
    PensionGuardContactController::class,
    'show',
])->name('contact');

Route::post('/contact', [
    PensionGuardContactController::class,
    'submit',
])->middleware('throttle:5,1')
  ->name('contact.submit');
