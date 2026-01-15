<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\{
    DashboardController,
    ReportController,
    DealerController,
    ImportController
};

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Redirect /home → /dashboard
|--------------------------------------------------------------------------
*/
Route::get('/home', fn () => redirect()->route('dashboard'))
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /* -------------------------------------------------
     | Dashboard
     * ------------------------------------------------- */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /* -------------------------------------------------
     | Reports
     * ------------------------------------------------- */
    Route::get('/reports/devices', [ReportController::class, 'devices'])
        ->name('reports.devices');

    Route::get('/reports/activations', [ReportController::class, 'activations'])
        ->name('reports.activations');

    /* -------------------------------------------------
     | Dealer Management (ADMIN)
     * ------------------------------------------------- */
    Route::get('/dealers', [DealerController::class, 'index'])
        ->name('dealers.index');

    Route::post('/dealers', [DealerController::class, 'store'])
        ->name('dealers.store');

    /* -------------------------------------------------
     | INVENTORY IMPORT (MASTER REPLACEMENT)
     * ------------------------------------------------- */
    Route::get('/imports/inventory', [ImportController::class, 'showInventory'])
        ->name('imports.inventory.view');

    Route::post('/imports/inventory', [ImportController::class, 'inventory'])
        ->name('imports.inventory.upload');

    /* -------------------------------------------------
     | SALES TO DEALER (ALLOCATION)
     * ------------------------------------------------- */
    Route::get('/imports/allocation', [ImportController::class, 'showAllocation'])
        ->name('imports.allocation.view');

    Route::post('/imports/allocation', [ImportController::class, 'allocation'])
        ->name('imports.allocation.upload');

    /* -------------------------------------------------
     | ACTIVATION IMPORT
     * ------------------------------------------------- */
    Route::get('/imports/activation', [ImportController::class, 'showActivation'])
        ->name('imports.activation.view');

    Route::post('/imports/activation', [ImportController::class, 'activation'])
        ->name('imports.activation.upload');

    /* -------------------------------------------------
     | IMPORT PROGRESS (SHARED BY ALL IMPORTS)
     * ------------------------------------------------- */
    Route::get(
        '/imports/{importFile}/progress-view',
        [ImportController::class, 'progressView']
    )->name('imports.progress.view');

    Route::get(
        '/imports/{importFile}/progress',
        [ImportController::class, 'progress']
    )->name('imports.progress');
});
