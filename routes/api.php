<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\P2hController;

Route::prefix('dashboard')->group(function () {
    Route::get('/user', [DashboardController::class, 'userDashboard']);
    Route::get('/tkbm/produk', [DashboardController::class, 'tkbmDashboardProduk']);
    Route::get('/tkbm/total-qty', [DashboardController::class, 'tkbmTotalPerhari']);
    Route::get('/tkbm/grand-total', [DashboardController::class, 'tkbmDashboardGrandTotal']);
});

Route::prefix('p2h')->group(function () {
    Route::post('/store/forklift', [P2hController::class, 'store']);
    Route::post('/store/pallet-mover', [P2hController::class, 'storePalletMover']);
    Route::get('/data/forklift-data', [P2hController::class, 'showForklift']);
    Route::get('/data/pallet-mover', [P2hController::class, 'showPalletMover']);
});
