<?php
use App\Http\Controllers\PhishingCampaignController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LearningPlanController;
use App\Http\Controllers\ManagerAssessmentController;
use App\Http\Controllers\ManagerDepartmentReportsController;
use App\Http\Controllers\ManagerRiskDashboardController;
use App\Http\Controllers\ManagerTeamReadinessController;
use App\Http\Controllers\OrganisationAdminDashboardController;
use App\Http\Controllers\OrganisationAssessmentController;
use App\Http\Controllers\OrganisationReportController;
use App\Http\Controllers\OrganisationTrainingController;
use App\Http\Controllers\OrganisationUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminAnalyticsController;
use App\Http\Controllers\SuperAdminBillingController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdminOrganisationController;
use App\Http\Controllers\SuperAdminSubscriptionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackReportController;

// use  Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.store');

Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register.store');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

Route::get(
    '/feedback-reports',
    [FeedbackReportController::class, 'index']
)->name('feedback-reports.index');

Route::get(
    '/feedback-reports/{feedback}',
    [FeedbackReportController::class, 'show']
)->name('feedback-reports.show');

/*
|--------------------------------------------------------------------------
| Authenticated Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

Route::get('/dashboard', function () {
    $user = auth()->user();

    return match ($user->role?->slug) {

        'super-admin' =>
            app(\App\Http\Controllers\SuperAdminDashboardController::class)
                ->index(request()),

        'organisation-admin' =>
            app(\App\Http\Controllers\OrganisationAdminDashboardController::class)
                ->index(request()),

        'manager' =>
            view('dashboard.manager'),

        'employee' =>
            view('dashboard.employee'),

        default =>
            abort(403, 'No valid role assigned to this account.'),
    };

})->name('dashboard');



});


Route::middleware(['auth', 'verified'])->prefix('phishing')->name('phishing.')->group(function () {
    // Campaign management: controller enforces role and organisation access.
    Route::get('/campaigns', [PhishingCampaignController::class, 'index'])
        ->name('index');

    Route::get('/campaigns/create', [PhishingCampaignController::class, 'create'])
        ->name('create');

    Route::post('/campaigns', [PhishingCampaignController::class, 'store'])
        ->name('store');

    Route::get('/campaigns/{campaign}', [PhishingCampaignController::class, 'show'])
        ->name('show');

    Route::post('/campaigns/{campaign}/launch', [PhishingCampaignController::class, 'launch'])
        ->name('launch');

    // Employee simulations.
    Route::get('/my-simulations', [PhishingCampaignController::class, 'employeeIndex'])
        ->name('employee');

    Route::get('/simulation/{recipient}', [PhishingCampaignController::class, 'openSimulation'])
        ->name('open');

    Route::post('/simulation/{recipient}/report', [PhishingCampaignController::class, 'reportSimulation'])
        ->name('report');
});



/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:super-admin'])
    ->prefix('admin')
    ->group(function () {

        Route::get(
            '/dashboard',
            [SuperAdminDashboardController::class, 'index']
        )->name('admin.dashboard');
        Route::get(
            '/tenants',
            [SuperAdminOrganisationController::class, 'index']
        )->name('tenants.index');

        Route::get(
            '/tenants/create',
            [SuperAdminOrganisationController::class, 'create']
        )->name('tenants.create');

        Route::post(
            '/tenants',
            [SuperAdminOrganisationController::class, 'store']
        )->name('tenants.store');

        Route::get(
            '/tenants/{organisation}/edit',
            [SuperAdminOrganisationController::class, 'edit']
        )->name('tenants.edit');

        Route::put(
            '/tenants/{organisation}',
            [SuperAdminOrganisationController::class, 'update']
        )->name('tenants.update');

        Route::patch(
            '/tenants/{organisation}/toggle-status',
            [SuperAdminOrganisationController::class, 'toggleStatus']
        )->name('tenants.toggle-status');

    Route::get(
        '/subscriptions',
        [SuperAdminSubscriptionController::class, 'index']
    )->name('subscriptions.index');

    Route::get(
        '/subscriptions/create',
        [SuperAdminSubscriptionController::class, 'create']
    )->name('subscriptions.create');

    Route::post(
        '/subscriptions',
        [SuperAdminSubscriptionController::class, 'store']
    )->name('subscriptions.store');

    Route::get(
        '/subscriptions/{subscription}/edit',
        [SuperAdminSubscriptionController::class, 'edit']
    )->name('subscriptions.edit');

    Route::put(
        '/subscriptions/{subscription}',
        [SuperAdminSubscriptionController::class, 'update']
    )->name('subscriptions.update');

    Route::patch(
        '/subscriptions/{subscription}/cancel',
        [SuperAdminSubscriptionController::class, 'cancel']
    )->name('subscriptions.cancel');
    Route::get(
        '/analytics',
        [SuperAdminAnalyticsController::class, 'index']
    )->name('analytics.index');

    Route::get(
        '/billing',
        [SuperAdminBillingController::class, 'index']
    )->name('billing.index');

    });


/*
|--------------------------------------------------------------------------
| Organisation Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:organisation-admin'])
    ->prefix('organisation')
    ->group(function () {

        Route::get(
            '/dashboard',
            [OrganisationAdminDashboardController::class, 'index']
        )->name('organisation.dashboard');

        Route::patch(
            '/users/{user}/toggle-status',
            [OrganisationUserController::class, 'toggleStatus']
        )->name('organisation.users.toggle-status');

        Route::get(
            '/users',
            [OrganisationUserController::class, 'index']
        )->name('organisation.users');

        Route::get(
            '/users/create',
            [OrganisationUserController::class, 'create']
        )->name('organisation.users.create');

        Route::post(
            '/users',
            [OrganisationUserController::class, 'store']
        )->name('organisation.users.store');

        Route::get(
            '/users/{user}/edit',
            [OrganisationUserController::class, 'edit']
        )->name('organisation.users.edit');

        Route::put(
            '/users/{user}',
            [OrganisationUserController::class, 'update']
        )->name('organisation.users.update');

        Route::delete(
            '/users/{user}',
            [OrganisationUserController::class, 'destroy']
        )->name('organisation.users.destroy');

        Route::get(
            '/assessments',
            [OrganisationAssessmentController::class, 'index']
        )->name('organisation.assessments');

        Route::get(
            '/assessments/create',
            [OrganisationAssessmentController::class, 'create']
        )->name('organisation.assessments.create');

        Route::post(
            '/assessments',
            [OrganisationAssessmentController::class, 'generate']
        )->name('organisation.assessments.generate');


        Route::get(
            '/reports',
            [OrganisationReportController::class, 'index']
        )->name('organisation.reports');

        Route::get(
            '/training/employee/{employee}',
            [OrganisationTrainingController::class, 'employee']
        )->name('organisation.training.employee');

        Route::get(
            '/training',
            [OrganisationTrainingController::class, 'index']
        )->name('organisation.training');

    });
/*
|--------------------------------------------------------------------------
| Manager Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:manager'])
    ->prefix('manager')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('dashboard.manager');
        })->name('manager.dashboard');
        Route::get(
            '/department-reports',
            [ManagerDepartmentReportsController::class, 'index']
        )->name('manager.department-reports');

        Route::get(
            '/risk-dashboard',
            [ManagerRiskDashboardController::class, 'index']
        )->name('manager.risk-dashboard');

        Route::get('/team-readiness', [ManagerTeamReadinessController::class, 'index'])
            ->name('manager.team-readiness');

        Route::get('/learning-plans/{employee}', [LearningPlanController::class, 'managerIndex'])
            ->name('manager.learning-plans');
            });


/*
|--------------------------------------------------------------------------
| Employee Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:employee'])
    ->prefix('employee')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('dashboard.employee');
        })->name('employee.dashboard');

        Route::get('/assessments', function () {
            return redirect()->route('assessment.index');
        })->name('employee.assessments');

        Route::get('/score', [EmployeeController::class, 'score'])
         ->name('employee.score');


	Route::get('/learning-plans', [LearningPlanController::class, 'index'])
	    ->name('employee.learning-plans');
    });

    Route::patch('/employee/learning-plans/{learningPlan}/status', [LearningPlanController::class, 'updateStatus'])
        ->name('employee.learning-plans.status');
    /*
    |--------------------------------------------------------------------------
    | Assessment
    |--------------------------------------------------------------------------
    */

 // Employee assessment routes
    Route::middleware([
        'auth',
        'verified',
        'role:employee',
    ])->group(function () {

        Route::get(
            '/assessments',
            [AssessmentController::class, 'index']
        )->name('assessment.index');

        Route::get(
            '/assessment/{assessment}/start',
            [AssessmentController::class, 'start']
        )->name('assessment.start');

        Route::get(
            '/assessment/{attempt}/question/{question}/previous',
            [AssessmentController::class, 'previous']
        )->name('assessment.previous');

        Route::get(
            '/assessment/{attempt}/question/{question}',
            [AssessmentController::class, 'question']
        )->name('assessment.question');

        Route::post(
            '/assessment/{attempt}/question/{question}',
            [AssessmentController::class, 'answer']
        )->name('assessment.answer');

        Route::get(
            '/assessment/{attempt}/review',
            [AssessmentController::class, 'review']
        )->name('assessment.review');

        Route::post(
            '/assessment/{attempt}/submit',
            [AssessmentController::class, 'submit']
        )->name('assessment.submit');
    });


    // Assessment result routes
    Route::middleware([
        'auth',
        'verified',
    ])->group(function () {

        Route::get(
            '/assessment/{attempt}/result',
            [AssessmentController::class, 'result']
        )->name('assessment.result');
    });
 /*
 /==================================================
 /  Password: reset and management
 /
 /==================================================
 */

Route::middleware('guest')->group(function () {

    Route::get(
        '/forgot-password',
        [PasswordResetLinkController::class, 'create']
    )->name('password.request');

    Route::post(
        '/forgot-password',
        [PasswordResetLinkController::class, 'store']
    )->name('password.email');

    Route::get(
        '/reset-password/{token}',
        [NewPasswordController::class, 'create']
    )->name('password.reset');

    Route::post(
        '/reset-password',
        [NewPasswordController::class, 'store']
    )->name('password.update');
});

/*
/========================================================
/
/  Manager: assessment question generation
/=========================================================
*/
    Route::middleware([
        'auth',
        'verified',
        'role:manager'
    ])->prefix('manager')->name('manager.')->group(function () {
        Route::get(
            '/assessments',
            [ManagerAssessmentController::class, 'index']
        )->name('assessments.index');

        Route::get(
            '/assessments/create',
            [ManagerAssessmentController::class, 'create']
        )->name('assessments.create');

        Route::post(
            '/assessments',
            [ManagerAssessmentController::class, 'generate']
        )->name('assessments.generate');

    });

/*
/=============================================================
/
/ Profile management
/==============================================================
*/

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::put(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::put(
        '/profile/password',
        [ProfileController::class, 'updatePassword']
    )->name('profile.password.update');

    Route::resource('departments', DepartmentController::class);

});
/*
|--------------------------------------------------------------------------
| Public CyberReadyAI Test
|--------------------------------------------------------------------------
*/
require base_path('routes/public_test.php');

require base_path('routes/pension_guard.php');
