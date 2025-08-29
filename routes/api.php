<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::prefix('dashboard')->group(function () {
    Route::get('/user', [DashboardController::class, 'userDashboard']);
    Route::get('/tkbm/produk', [DashboardController::class, 'tkbmDashboardProduk']);
    Route::get('/tkbm/total-qty', [DashboardController::class, 'tkbmTotalPerhari']);
    Route::get('/tkbm/grand-total', [DashboardController::class, 'tkbmDashboardGrandTotal']);
});
