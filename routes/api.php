<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\P2hController;
use App\Http\Controllers\Wsp\TkbmController;
use App\Http\Controllers\Wsp\WspRakController;
use App\Http\Controllers\Api\WspManRakController;
use App\Http\Controllers\Wsp\WspBarangController;
use App\Http\Controllers\Wsp\StockOpnameController;
use App\Http\Controllers\Api\P2hDashboardController;
use App\Http\Controllers\Api\RakDashboardController;
use App\Http\Controllers\Wsp\TransaksiWspController;
use App\Http\Controllers\Api\TkbmDashboardController;
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Wfg\stock_opname\BarangWfgController;
use App\Http\Controllers\Wfg\stock_opname\StockOnHandWfgController;

Route::prefix('dashboard')->group(function () {
    Route::get('/user', [TkbmDashboardController::class, 'userDashboard']);

    // TKBM
    Route::prefix('tkbm')->group(function () {
        Route::get('/data/per-month', [TkbmDashboardController::class, 'tkbmDashboard']);
        Route::get('/produk', [TkbmDashboardController::class, 'tkbmDashboardProduk']);
        Route::get('/qty-terpal', [TkbmDashboardController::class, 'qtyTerpalDay']);
        Route::get('/qty-slipsheet', [TkbmDashboardController::class, 'qtySlipsheetDay']);
        Route::get('/qty-pallet', [TkbmDashboardController::class, 'qtyPalletDay']);
        Route::get('/total-qty', [TkbmDashboardController::class, 'tkbmTotalPerhari']);
        Route::get('/grand-total', [TkbmDashboardController::class, 'tkbmDashboardGrandTotal']);
        Route::get('/export-pdf', [TkbmDashboardController::class, 'exportPdf']);
        Route::get('/widget', [TkbmDashboardController::class, 'dataWidget']);
        Route::get('/all_qty_produk', [TkbmDashboardController::class, 'tkbmAllProduk']);
    });

    // p2h
    Route::prefix('p2h')->group(function () {
        Route::get('/summary', [P2hDashboardController::class, 'summary']);
        Route::get('/kelayakan', [P2hDashboardController::class, 'kelayakanSummary']);
        Route::get('/masalah-terbanyak', [P2hDashboardController::class, 'topMasalah']);
        Route::get('/operator', [P2hDashboardController::class, 'operatorStat']);
        Route::get('/shift', [P2hDashboardController::class, 'shiftDistribusi']);
        Route::get('/kelayakan/pallet-mover', [P2hDashboardController::class, 'kelayakanSummaryPalletMover']);
    });

    // WSP
    Route::get('/wsp/rak', [RakDashboardController::class, 'getDataRack']);

    // User
    Route::prefix('user')->group(function () {
        Route::get('/data', [UserDashboardController::class, 'create']);
        Route::get('/statistik', [UserDashboardController::class, 'statistik']);
    });
});

Route::prefix('tkbm')->group(function () {
    Route::get('/data/show', [TkbmController::class, 'show']);
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

Route::prefix('wsp')->group(function () {
    Route::get('/data/barang', [WspBarangController::class, 'getDataBarang']);
    Route::get('/data/rak', [WspBarangController::class, 'getDataRak']);
    Route::get('/rak/filters', [WspRakController::class, 'getFilters']);
    Route::delete('/delete/barang/{id}', [WspBarangController::class, 'destroy']);
    Route::get('/items/search', [WspBarangController::class, 'searchItems']);
    Route::get('/show/barang/{id}', [WspBarangController::class, 'show']);
    Route::post('/store/rak', [WspRakController::class, 'storeRak']);
    Route::get('/data/all/rak', [WspRakController::class, 'getDataRak']);
    Route::get('/show/rak/{id}', [WspRakController::class, 'show']);
    Route::post('/store/rak', [WspRakController::class, 'storeRak']);
    Route::get('/data/stock/barang', [WspManRakController::class, 'getDataBarang']);
    Route::get('/show/stock/barang/{id}', [WspManRakController::class, 'show']);
    Route::post('/store/transaksi', [TransaksiWspController::class, 'store']);
});

Route::prefix('wfg')->group(function () {
    Route::post('/store/barang', [BarangWfgController::class, 'store']);
    Route::post('/store/soh', [StockOnHandWfgController::class, 'store']);
    Route::get('/soh/listData', [StockOnHandWfgController::class, 'getList']);
    Route::get('/soh/show/{id}', [StockOnHandWfgController::class, 'show']);
    Route::get('/soh/getBarang', [StockOnHandWfgController::class, 'getBarang']);
});
