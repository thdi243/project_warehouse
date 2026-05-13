<?php

use App\Http\Controllers\Dashboard\P2hDashboardController;
use App\Http\Controllers\Dashboard\RakDashboardController;
use App\Http\Controllers\Dashboard\BpsDashboardController;
use App\Http\Controllers\Dashboard\UserDashboardController;
use App\Http\Controllers\Dashboard\WspManRakController;
use App\Http\Controllers\Dashboard\WrmInventoryController;
use App\Http\Controllers\Dashboard\WfgBongkarMuatDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Tkbm\ikat_terpal\IkatTerpalController;
use App\Http\Controllers\Wfg\stock_opname\BarangWfgController;
use App\Http\Controllers\Wfg\stock_opname\StockOnHandWfgController;
use App\Http\Controllers\Wfg\stock_opname\StockOpnameWfgController;
use App\Http\Controllers\Wrm\P2HController;
use App\Http\Controllers\Wsp\purchase_requesition\WspPurchaseRequesitionController;
use App\Http\Controllers\Wsp\stock_move\WspIncomingController;
use App\Http\Controllers\Wsp\stock_move\WspOutgoingController;
use App\Http\Controllers\Wsp\stock\StockOnHandController;
use App\Http\Controllers\Wsp\TkbmController;
use App\Http\Controllers\Wsp\WspBarangController;
use App\Http\Controllers\Wsp\WspRakController;
use App\Http\Controllers\Dashboard\IkatTerpalDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function () {
    Route::get('/user', [BpsDashboardController::class, 'userDashboard']);

    // TKBM
    Route::prefix('tkbm')->group(function () {

        Route::get('/bps/get-stats', [BpsDashboardController::class, 'getStats']);

        // ikat terpal
        Route::get('/ikat-terpal/get-stats', [IkatTerpalDashboardController::class, 'getStats']);
    });

    // p2h
    Route::prefix('p2h')->group(function () {
        Route::get('/summary', [P2hDashboardController::class, 'summary']);
        Route::get('/kelayakan', [P2hDashboardController::class, 'kelayakanSummary']);
        Route::get('/masalah-terbanyak', [P2hDashboardController::class, 'topMasalah']);
        Route::get('/operator', [P2hDashboardController::class, 'operatorStat']);
        Route::get('/shift', [P2hDashboardController::class, 'shiftDistribusi']);
        Route::get('/masalah/unit-forklift', [P2hDashboardController::class, 'unitForkliftMasalah']);
        Route::get('/pallet-mover/kelayakan', [P2hDashboardController::class, 'kelayakanSummaryPalletMover']);
        Route::get('/pallet-mover/part-masalah', [P2hDashboardController::class, 'topMasalahPalletMover']);
        Route::get('/pallet-mover/operator', [P2hDashboardController::class, 'operatorStatPalletMover']);
        Route::get('/masalah/unit-pallet-mover', [P2hDashboardController::class, 'unitPalletMoverMasalah']);
        Route::get('/daily-status', [P2hDashboardController::class, 'getDailyStatusTable']);
    });

    // WRM
    Route::prefix('wrm')->group(function () {
        Route::get('/inventory/kpi', [WrmInventoryController::class, 'getKpi']);
        Route::get('/inventory/chart-movement', [WrmInventoryController::class, 'getChartMovement']);
        Route::get('/inventory/chart-pie', [WrmInventoryController::class, 'getChartPie']);
        Route::get('/inventory/chart-bar', [WrmInventoryController::class, 'getChartBar']);
        Route::get('/inventory/chart-capacity', [WrmInventoryController::class, 'getChartCapacity']);
        Route::get('/inventory/table-expiring', [WrmInventoryController::class, 'getTableExpiring']);
        Route::get('/inventory/table-recent', [WrmInventoryController::class, 'getTableRecent']);
        Route::get('/inventory/location-layout', [WrmInventoryController::class, 'getLocationLayout']);
    });

    // WFG Bongkar Muat Dashboard
    Route::prefix('wfg')->group(function () {
        Route::get('/bongkar-muat/kpi',          [WfgBongkarMuatDashboardController::class, 'getKpi']);
        Route::get('/bongkar-muat/wavepick-status', [WfgBongkarMuatDashboardController::class, 'getWavepickByStatus']);
        Route::get('/bongkar-muat/chart-trend',  [WfgBongkarMuatDashboardController::class, 'getChartTrend']);
        Route::get('/bongkar-muat/chart-status', [WfgBongkarMuatDashboardController::class, 'getChartStatus']);
        Route::get('/bongkar-muat/chart-destination', [WfgBongkarMuatDashboardController::class, 'getChartDestination']);
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
    Route::get('/get-data/ikat-terpal', [IkatTerpalController::class, 'getDataReport']);
});

Route::prefix('p2h')->group(function () {
    // Forklift
    Route::post('/store/forklift', [P2HController::class, 'store']);
    Route::get('/data/forklift-data', [P2HController::class, 'showForklift']);
    Route::get('/data/registration/forklift', [P2HController::class, 'showRegForklift']);
    Route::post('/store/forklift/registration', [P2HController::class, 'storeForkliftRegistration']);
    Route::get('/backups/forklift/{id}', [P2HController::class, 'getBackupForklift']);
    Route::get('/show/forklift/{id}', [P2HController::class, 'showForkliftDetail']);
    Route::put('/update/forklift/{id}', [P2HController::class, 'updateForklift']);

    Route::delete('/delete/forklift/{id}', [P2HController::class, 'destroyForklift']);
    Route::get('/show/forklift/assignment/{id}', [P2HController::class, 'showForkliftAssignment']);

    // Pallet Mover
    Route::post('/store/pallet-mover', [P2HController::class, 'storePalletMover']);
    Route::get('/data/pallet-mover', [P2HController::class, 'showPalletMover']);
    Route::get('/data/registration/pallet-mover', [P2HController::class, 'getPalletData']);
    Route::post('/store/registration/pallet-mover', [P2HController::class, 'storePallMovReg']);
    Route::get('/detail/pallet-mover/{id}', [P2HController::class, 'showPallMovDetail']);
    Route::put('/update/pallet-mover/{id}', [P2HController::class, 'updatePallMov']);
    Route::delete('/delete/pallet-mover/{id}', [P2HController::class, 'destroyPallMov']);
    Route::get('/backups/pallet-mover/{id}', [P2HController::class, 'getBackupPallMov']);
    Route::get('/detail/pallet-mover/assignment/{id}', [P2HController::class, 'editPallMovAssignment']);
});

Route::prefix('wsp')->group(function () {
    Route::get('/data/barang', [WspBarangController::class, 'getDataBarang']);
    Route::get('/barang', [WspBarangController::class, 'getDataBarangWsp']);
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

    Route::get('incoming/getData', [WspIncomingController::class, 'getDataIncoming']);
    Route::get('outgoing/getData', [WspOutgoingController::class, 'getDataOutgoing']);
    Route::get('stock-on-hand/getData', [StockOnHandController::class, 'getDataSOH']);
    Route::get('purchase_requisition/getData', [WspPurchaseRequesitionController::class, 'getDataPR']);
});

Route::prefix('wfg')->group(function () {
    Route::post('/store/barang', [BarangWfgController::class, 'store']);
    Route::get('/show/barang/{id}', [BarangWfgController::class, 'show'])->name('wfg.master.barang.show');
    Route::post('/store/soh', [StockOnHandWfgController::class, 'store']);
    Route::get('/soh/listData', [StockOnHandWfgController::class, 'getList']);
    Route::get('/soh/show/{id}', [StockOnHandWfgController::class, 'show']);
    Route::get('/soh/getBarang', [StockOnHandWfgController::class, 'getBarang']);
    Route::post('/sop/store', [StockOpnameWfgController::class, 'store']);
    Route::get('/sop/getData', [StockOpnameWfgController::class, 'getData']);
    Route::get('/sop/getDataTempBatch', [StockOpnameWfgController::class, 'getDataTempBatch']);
    Route::get('/sop/getDataTempEdit/{id}', [StockOpnameWfgController::class, 'getDataTempEdit']);
    Route::get('/sop/getDataNewTempEdit/{id}', [StockOpnameWfgController::class, 'getDataNewTempEdit']);
    Route::get('/sop/report/getData', [StockOpnameWfgController::class, 'getDataReport']);
    Route::get('/sop/report/export', [StockOpnameWfgController::class, 'getDataReport']);
    Route::get('/sop/users/approval', [StockOpnameWfgController::class, 'getDataApproval']);
    Route::get('/sop/detail/edit/{id}', [StockOpnameWfgController::class, 'getDataDetailEdit']);
});

Route::prefix('notifications')->group(function () {
    Route::get('/index', [NotificationController::class, 'index']);
    Route::post('/read/{id}', [NotificationController::class, 'markAsRead']);
    Route::post('/show/kalibrasi', [NotificationController::class, 'showNotification'])->name('notifications.kalibrasi');
    Route::get('/external/get-data', [NotificationController::class, 'getNotificationsByExternalUser']);
});

Route::prefix('purchase-requesition')->middleware('web')->group(function () {
    Route::get('/getData', [WspPurchaseRequesitionController::class, 'getDataPR']);
    Route::get('/getRiwayat', [WspPurchaseRequesitionController::class, 'getRiwayatPR']);
    // Route::post('/store', [WspPurchaseRequesitionController::class, 'store']);
    Route::get('/print-riwayat/{id}', [WspPurchaseRequesitionController::class, 'printRiwayat']);
    Route::get('/getBarang/search', [WspPurchaseRequesitionController::class, 'searchSOH']);
    // Route::post('/reserved', [WspPurchaseRequesitionController::class, 'reserved']);
    Route::post('/release-item', [WspPurchaseRequesitionController::class, 'releaseItem']);
    Route::get('/pr-data/approval/{id}', [WspPurchaseRequesitionController::class, 'getDataApproval']);
    Route::get('/pending-approvals', [WspPurchaseRequesitionController::class, 'getPendingApprovals']);
    Route::post('/bulk-action', [WspPurchaseRequesitionController::class, 'bulkAction']);
    Route::post('/approval-pr/action/{id}', [WspPurchaseRequesitionController::class, 'action']);
});

// SSO callback is now in web.php as a GET request
// Route::post('/auth/validate-token', [TokenAuthController::class, 'receiveToken']);
