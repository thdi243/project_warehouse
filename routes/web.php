<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TkbmController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\TkbmDashboardController;
use App\Http\Controllers\P2hController;
use App\Http\Controllers\WarehouseController;

Route::get('/', function () {
    return view('auth.login');
});

// Auth
Route::middleware('web')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('signin');
    Route::post('/signin', [AuthController::class, 'login'])->name('signin');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('auth')->group(function () {

    Route::prefix('dashboard')->group(function () {
        // Main
        Route::view('/main', 'dashboard.main_dashboard')->name('main.dashboard');

        // TKBM
        Route::view('/tkbm', 'dashboard.tkbm_dashboard')->name('dashboard.tkbm');
        Route::get('/tkbm/get-data', [TkbmDashboardController::class, 'tkbmDashboard'])->name('dashboard.tkbm.data');
        Route::view('/p2h', 'dashboard.p2h_dashboard')->name('dashboard.p2h');
    });

    // TKBM
    Route::prefix('tkbm')->group(function () {
        Route::get('/input', [WarehouseController::class, 'stock'])->name('tkbm.stock');
        Route::post('/simpan', [TkbmController::class, 'store'])->name('tkbm.store');
        Route::get('/data', [TkbmController::class, 'index'])->name('tkbm.data');
        Route::get('/data/show', [TkbmController::class, 'show'])->name('tkbm.data.show');
        Route::get('/data/edit/{id}', [TkbmController::class, 'edit'])->name('tkbm.data.edit');
        Route::put('/data/update/{id}', [TkbmController::class, 'update'])->name('tkbm.data.update');
        Route::delete('/data/delete/{id}', [TkbmController::class, 'destroy'])->name('tkbm.data.delete');
        Route::get('/data/export', [TkbmController::class, 'export'])->name('tkbm.data.export');
        Route::get('/master/fee', [WarehouseController::class, 'feeTkbm'])->name('tkbm.master.fee');
        Route::get('/master/harga-produk', [WarehouseController::class, 'feeTkbm'])->name('tkbm.master.harga-produk');
        Route::post('/fee/simpan', [TkbmController::class, 'simpanFeeTkbm'])->name('tkbm.fee.simpan');
        Route::get('/fee/history', [TkbmController::class, 'historyFeeTkbm'])->name('tkbm.fee.history');
        Route::post('/harga-produk/simpan', [TkbmController::class, 'simpanHargaProduk'])->name('tkbm.harga-produk.simpan');
        Route::get('/harga-produk/history', [TkbmController::class, 'historyProductPrice'])->name('tkbm.harga-produk.history');
    });

    // P2H
    Route::prefix('p2h')->group(function () {
        Route::get('/online/index', [P2hController::class, 'index'])->name('p2h.online.index');
        Route::get('/online/data', [WarehouseController::class, 'p2hData'])->name('p2h.online.data');
        Route::get('/registration/forklift', [WarehouseController::class, 'showRegForklift'])->name('p2h.registration.forklift');
        Route::get('/registration/pallet-mover', [WarehouseController::class, 'showRegPalletMover'])->name('p2h.registration.pallet-mover');
    });

    // User
    Route::prefix('user')->group(function () {
        Route::get('/index', [UserController::class, 'index'])->name('user.index');
        Route::get('/get-data', [UserController::class, 'create'])->name('user.getData');
        Route::post('/store', [UserController::class, 'store'])->name('user.store');
        Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/update/{id}', [UserController::class, 'update'])->name('user.update');
    });
});
