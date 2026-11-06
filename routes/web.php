
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pension Account Guard - Main Application
|--------------------------------------------------------------------------
|
| Pension Account Guard is now the sole publicly registered product.
| Legacy CyberReadyAI and PublicTest routes are no longer registered.
| Existing legacy database tables are retained and are not dropped here.
|
*/

Route::redirect('/', '/pension-guard')->name('home');

require base_path('routes/pension_guard.php');
