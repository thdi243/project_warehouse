<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\P2hController;
use App\Http\Controllers\Api\P2hDashboardController;
use App\Http\Controllers\Api\TkbmDashboardController;

Route::prefix('dashboard')->group(function () {
    Route::get('/user', [TkbmDashboardController::class, 'userDashboard']);

    // TKBM
    Route::get('/tkbm/produk', [TkbmDashboardController::class, 'tkbmDashboardProduk']);
    Route::get('/tkbm/qty-terpal', [TkbmDashboardController::class, 'qtyTerpalDay']);
    Route::get('/tkbm/qty-slipsheet', [TkbmDashboardController::class, 'qtySlipsheetDay']);
    Route::get('/tkbm/qty-pallet', [TkbmDashboardController::class, 'qtyPalletDay']);
    Route::get('/tkbm/total-qty', [TkbmDashboardController::class, 'tkbmTotalPerhari']);
    Route::get('/tkbm/grand-total', [TkbmDashboardController::class, 'tkbmDashboardGrandTotal']);

    // p2h
    Route::prefix('p2h')->group(function () {
        Route::get('/summary', [P2hDashboardController::class, 'summary']);
        Route::get('/kelayakan', [P2hDashboardController::class, 'kelayakanSummary']);
        Route::get('/masalah-terbanyak', [P2hDashboardController::class, 'topMasalah']);
        Route::get('/operator', [P2hDashboardController::class, 'operatorStat']);
        Route::get('/shift', [P2hDashboardController::class, 'shiftDistribusi']);
    });
});

Route::prefix('p2h')->group(function () {
    // Forklift
    Route::post('/store/forklift', [P2hController::class, 'store']);
    Route::get('/data/forklift-data', [P2hController::class, 'showForklift']);
    Route::get('/data/registration/forklift', [P2hController::class, 'showRegForklift']);
    Route::post('/store/forklift/registration', [P2hController::class, 'storeForkliftRegistration']);
    Route::get('/backups/forklift/{id}', [P2hController::class, 'getBackupForklift']);
    Route::get('/show/forklift/{id}', [P2hController::class, 'showForkliftDetail']);
    Route::put('/update/forklift/{id}', [P2hController::class, 'updateForklift']);
    Route::delete('/delete/forklift/{id}', [P2hController::class, 'destroyForklift']);
    Route::post('/store/forklift/assignment', [P2hController::class, 'storeForkliftAssignment']);
    Route::get('/show/forklift/assignment/{id}', [P2hController::class, 'showForkliftAssignment']);
    Route::post('/update/forklift/assignment', [P2hController::class, 'updateForkliftAssignment']);

    // Pallet Mover
    Route::post('/store/pallet-mover', [P2hController::class, 'storePalletMover']);
    Route::get('/data/pallet-mover', [P2hController::class, 'showPalletMover']);
    Route::get('/data/registration/pallet-mover', [P2hController::class, 'getPalletData']);
    Route::post('/store/registration/pallet-mover', [P2hController::class, 'storePallMovReg']);
    Route::post('/store/pallet-mover/assignment', [P2hController::class, 'storePallMovAssignment']);
    Route::get('/detail/pallet-mover/{id}', [P2hController::class, 'showPallMovDetail']);
    Route::put('/update/pallet-mover/{id}', [P2hController::class, 'updatePallMov']);
    Route::delete('/delete/pallet-mover/{id}', [P2hController::class, 'destroyPallMov']);
    Route::get('/backups/pallet-mover/{id}', [P2hController::class, 'getBackupPallMov']);
    Route::get('/detail/pallet-mover/assignment/{id}', [P2hController::class, 'editPallMovAssignment']);
    Route::post('/update/pallet-mover/assignment/{id}', [P2hController::class, 'updatePallMovAssignment']);
});
