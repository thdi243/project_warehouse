<?php

use Illuminate\Support\Facades\Route;
use App\Events\ShowPortalNotification;
use App\Http\Controllers\P2hController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Wsp\TkbmController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\Wsp\WspRakController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Wsp\WspBarangController;
use App\Http\Controllers\Wsp\StockOpnameController;
use App\Http\Controllers\Wsp\stock\StockOnHandController;
use App\Http\Controllers\Wsp\stock\StockLocationController;
use App\Http\Controllers\Wfg\stock_opname\BarangWfgController;
use App\Http\Controllers\Wfg\stock_opname\StockOnHandWfgController;
use App\Http\Controllers\Wfg\stock_opname\StockOpnameWfgController;

// use App\Http\Controllers\Api\TkbmDashboardController;

Route::get('/', function () {
    return view('auth.login');
});

// Auth
Route::middleware('web')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/signin', [AuthController::class, 'login'])->name('signin');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('auth')->group(function () {

    // Free access
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('user/profile', [UserController::class, 'profileIndex'])->name('user.profile');
    Route::get('maintenance', [WarehouseController::class, 'maintenanceView'])->name('maintenance');

    // Dashboard
    Route::middleware(['auth', 'access'])->group(function () {
        Route::prefix('dashboard')->group(function () {
            // Main
            Route::view('/main', 'dashboard.foreman_spv_home')->name('dashboard.main');
            // TKBM
            Route::view('/tkbm', 'dashboard.tkbm_dashboard')->name('dashboard.tkbm');
            // Route::get('/tkbm/get-data', [TkbmDashboardController::class, 'tkbmDashboard'])->name('dashboard.tkbm.data');
            Route::view('/p2h', 'dashboard.p2h_dashboard')->name('dashboard.p2h');
            // Rak Management
            Route::view('/rak', 'dashboard.rak_dashboard')->name('dashboard.rak');
        });
    });

    // Warehouse Sparepart (TKBM, Rak Management)
    Route::middleware(['auth', 'access:warehouse_sparepart'])->group(function () {
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
            // Route::get('/data/export-pdf', [TkbmController::class, 'exportPdf'])->name('tkbm.data.export-pdf');
            Route::get('/master/harga-produk', [WarehouseController::class, 'feeTkbm'])->name('tkbm.master.harga-produk');
            Route::post('/fee/simpan', [TkbmController::class, 'simpanFeeTkbm'])->name('tkbm.fee.simpan');
            Route::get('/fee/history', [TkbmController::class, 'historyFeeTkbm'])->name('tkbm.fee.history');
            Route::post('/harga-produk/simpan', [TkbmController::class, 'simpanHargaProduk'])->name('tkbm.harga-produk.simpan');
            Route::get('/harga-produk/history', [TkbmController::class, 'historyProductPrice'])->name('tkbm.harga-produk.history');
            Route::get('/sync-totals', [TkbmController::class, 'syncTotalsTkbm']);
        });

        // Rak Management
        Route::prefix('rack')->group(function () {
            Route::get('/stock/dashboard', [WarehouseController::class, 'dashboardStockWsp'])->name('rack.stock.dashboard');
            Route::get('/stock/stock-on-hand', [WarehouseController::class, 'stockOnHandView'])->name('rack.stock.stock-on-hand');
            Route::get('/stock/location', [WarehouseController::class, 'stockLocView'])->name('rack.stock.stock-location');
            Route::post('/stock/loc/store', [StockLocationController::class, 'store'])->name('rack.stock.loc_store');
            Route::get('/stock/loc/show/{id}', [StockLocationController::class, 'show'])->name('rack.stock.loc_show');
            Route::put('/stock/loc/update/{id}', [StockLocationController::class, 'update'])->name('rack.stock.loc_update');
            Route::delete('/stock/loc/delete/{id}', [StockLocationController::class, 'destroy'])->name('rack.stock.loc_delete');
            Route::get('/stock/loc/data', [StockLocationController::class, 'getDataStockLocation'])->name('rack.stock.loc_data');
            Route::get('/stock/loc/download', [StockLocationController::class, 'downloadTemplate'])->name('rack.stock.loc_download');
            Route::post('/stock/loc/upload', [StockLocationController::class, 'upload'])->name('rack.stock.loc_upload');

            Route::get('/stock/soh/data', [StockOnHandController::class, 'getDataSOH'])->name('rack.stock.soh_data');
            Route::get('/stock/soh/show/{id}', [StockOnHandController::class, 'show'])->name('rack.stock.soh_show');
            Route::post('/stock/soh/store', [StockOnHandController::class, 'store'])->name('rack.stock.soh_store');
            Route::put('/stock/soh/update/{id}', [StockOnHandController::class, 'update'])->name('rack.stock.soh_update');
            Route::delete('/stock/soh/delete/{id}', [StockOnHandController::class, 'destroy'])->name('rack.stock.soh_delete');
            Route::get('/stock/soh/download', [StockOnHandController::class, 'downloadTemplate'])->name('rack.stock.soh_download');
            Route::post('/stock/soh/upload', [StockOnHandController::class, 'upload'])->name('rack.stock.soh_upload');
            Route::get('/stock/opname', [WarehouseController::class, 'opnameIndex'])->name('rack.stock.opname');
            // Route::get('/rak/list', [WarehouseController::class, 'rakList'])->name('wsp.rak.list');
            Route::get('/inventory', [WarehouseController::class, 'rakInventory'])->name('rack.inventory');
        });
    });

    // Warehouse Raw Material
    Route::middleware(['auth', 'access:warehouse_raw_material'])->group(function () {
        // P2H
        Route::prefix('p2h')->group(function () {
            Route::get('/online/index', [P2hController::class, 'index'])->name('p2h.online.index');
            Route::get('/online/data', [WarehouseController::class, 'p2hData'])->name('p2h.online.data');
            Route::get('/registration/forklift', [WarehouseController::class, 'showRegForklift'])->name('p2h.registration.forklift');
            Route::get('/registration/pallet-mover', [WarehouseController::class, 'showRegPalletMover'])->name('p2h.registration.pallet-mover');
        });
    });

    // Warehouse Finish Goods
    Route::middleware(['auth', 'access:warehouse_finish_goods'])->group(function () {
        Route::prefix('wfg')->group(function () {

            // Maste Barang SO WFG
            Route::prefix('master')->group(function () {
                Route::get('/barang/index', [BarangWfgController::class, 'index'])->name('wfg.master.barang.index');
                Route::post('/barang/store', [BarangWfgController::class, 'store'])->name('wfg.master.barang.store');
                Route::get('/barang/data', [BarangWfgController::class, 'data'])->name('wfg.master.barang.data');
                Route::put('/barang/update/{id}', [BarangWfgController::class, 'update'])->name('wfg.master.barang.update');
                Route::delete('/barang/delete/{id}', [BarangWfgController::class, 'destroy'])->name('wfg.master.barang.delete');
            });

            // Stock Opname WFG
            Route::prefix('stock_opname')->group(function () {
                // Form SOP WFG
                Route::post('/sop/start', [StockOpnameWfgController::class, 'startOpname'])->name('startOpname');
                Route::get('/sop/status', [StockOpnameWfgController::class, 'getStatusOpname'])->name('getStatusOpname');
                Route::get('/sop/form', [WarehouseController::class, 'formSOWFG'])->name('wfg.stock_opname.form');
                Route::get('/sop/getData', [StockOpnameWfgController::class, 'getData'])->name('wfg.stock_opname.getData');
                Route::post('/sop/store', [StockOpnameWfgController::class, 'store'])->name('wfg.stock_opname.store');
                Route::post('/sop/save-temp', [StockOpnameWfgController::class, 'saveTemp'])->name('wfg.stock_opname.save-temp');
                Route::post('/sop/save-new-temp', [StockOpnameWfgController::class, 'saveTempNew'])->name('wfg.stock_opname.save-temp-new');
                Route::post('/sop/save-final', [StockOpnameWfgController::class, 'processOpname'])->name('wfg.stock_opname.process');
                Route::post('/sop/update-temp', [StockOpnameWfgController::class, 'updateTempBatch'])->name('wfg.stock_opname.update-temp');
                Route::post('/sop/update-temp-new', [StockOpnameWfgController::class, 'updateNewTemp'])->name('wfg.stock_opname.update-temp-new');
                Route::delete('/sop/delete-temp/{id}', [StockOpnameWfgController::class, 'destroyTemp'])->name('wfg.stock_opname.delete-temp');
                Route::delete('/sop/delete-temp-new/{id}', [StockOpnameWfgController::class, 'destroyNewTemp'])->name('wfg.stock_opname.delete-temp-new');
                Route::put('/sop/update/{id}', [StockOpnameWfgController::class, 'update'])->name('wfg.stock_opname.update');
                Route::get('/sop/report', [WarehouseController::class, 'reportSOPWFG'])->name('wfg.stock_opname.report');
                Route::get('/sop/export', [StockOpnameWfgController::class, 'exportPdfSOPWFG'])->name('wfg.stock_opname.export');
                Route::post('/sop/update-keterangan/{id}', [StockOpnameWfgController::class, 'updateKeterangan'])->name('wfg.stock_opname.update-keterangan');
                Route::post('/sop/send-approval', [StockOpnameWfgController::class, 'sendApproval'])->name('wfg.stock_opname.send-approval');
                Route::post('/sop/update/status-approval', [StockOpnameWfgController::class, 'updateStatus'])->name('wfg.stock_opname.update.status-approval');
                Route::get('/sop/approval/show/{id}', [StockOpnameWfgController::class, 'show'])->name('wfg.stock_opname.approval.show');
                Route::get('/sop/report/getData', [StockOpnameWfgController::class, 'getDataReport'])->name('wfg.stock_opname.report.getData');
                Route::get('/sop/getDataTempBatch', [StockOpnameWfgController::class, 'getDataTempBatch'])->name('wfg.stock_opname.getTempBatch');
                Route::delete('/sop/reset-temp', [StockOpnameWfgController::class, 'resetTemp'])->name('wfg.stock_opname.reset-temp');
                Route::delete('/sop/reset-temp-row', [StockOpnameWfgController::class, 'resetTempRow'])->name('wfg.stock_opname.reset-temp-row');
                Route::post('/sop/send-report', [StockOpnameWfgController::class, 'sendReport'])->name('wfg.stock_opname.sendReport');
                Route::post('/sop/edit/update/', [StockOpnameWfgController::class, 'updateEditData'])->name('wfg.stock_opname.edit.update');
                Route::delete('wfg/sop/edit/delete/{id}', [StockOpnameWfgController::class, 'destroyEditData'])->name('wfg.stock_opname.edit.delete');
                Route::get('/sop/principal/list', [StockOpnameWfgController::class, 'getPrincipalList'])->name('wfg.stock_opname.principal-list');

                // Stock on Hand SO WFG
                Route::get('/soh/index', [WarehouseController::class, 'uploadSOHWFG'])->name('wfg.stock_opname.soh');
                Route::post('/soh/store', [StockOnHandWfgController::class, 'store'])->name('wfg.stock_opname.soh.store');
                Route::delete('/soh/delete/{id}', [StockOnHandWfgController::class, 'destroy'])->name('wfg.stock_opname.soh.delete');
                Route::post('/soh/update/{id}', [StockOnHandWfgController::class, 'update'])->name('wfg.stock_opname.soh.update');
                Route::post('/soh/import', [StockOnHandWfgController::class, 'importExcel'])->name('wfg.stock_opname.soh.import');
                Route::get('/soh/template', [StockOnHandWfgController::class, 'downloadTemplate'])->name('wfg.stock_opname.soh.template');
                Route::get('/soh/list', [StockOnHandWfgController::class, 'getList'])->name('wfg.stock_opname.soh.list');
                Route::get('/soh/show', [StockOnHandWfgController::class, 'show'])->name('wfg.stock_opname.soh.show');
                Route::get('/soh/getBarang', [StockOnHandWfgController::class, 'getBarang'])->name('wfg.stock_opname.soh.getBarang');
                Route::delete('/soh/reset-all', [StockOnHandWfgController::class, 'resetAll'])->name('wfg.stock_opname.soh.reset_all');
            });
        });
    });

    // User
    Route::middleware(['auth', 'access'])->group(function () {
        // Master WSP
        Route::prefix('wsp')->group(function () {
            Route::prefix('master')->group(function () {
                // TKBM
                Route::get('/fee', [WarehouseController::class, 'feeTkbm'])->name('wsp.master.fee');

                // Barang
                Route::get('/master/barang', [WarehouseController::class, 'barangIndex'])->name('wsp.master.barang');
                Route::post('/store/barang', [WspBarangController::class, 'store'])->name('wsp.store.barang');
                Route::put('/update/barang/{id}', [WspBarangController::class, 'update'])->name('wsp.update.barang');
                Route::post('/barang/import', [WspBarangController::class, 'import'])->name('wsp.barang.import');
                Route::get('/barang/download-template', [WspBarangController::class, 'downloadTemplate'])->name('wsp.barang.download.template');

                // Rak
                Route::get('/master/rak', [WarehouseController::class, 'rakIndex'])->name('wsp.master.rak');
                Route::put('/update/rak/{id}', [WspRakController::class, 'update'])->name('wsp.rak.update');
                Route::post('/store/rak', [WspRakController::class, 'store'])->name('wsp.store.rak');
                Route::delete('/delete/rak/{id}', [WspRakController::class, 'destroy'])->name('wsp.delete.rak');
                Route::post('/store/opname', [StockOpnameController::class, 'store'])->name('wsp.rak.store.opname');
            });
        });

        // Master WFG
        Route::prefix('wfg')->group(function () {
            Route::prefix('master')->group(function () {
                Route::get('/barang/index', [BarangWfgController::class, 'index'])->name('wfg.master.barang.index');
                Route::get('/barang/new', [BarangWfgController::class, 'getNewItems'])->name('wfg.master.barang.new');
                Route::post('/barang/new/approve/{id}', [BarangWfgController::class, 'approve'])->name('wfg.master.barang.new.approve');
                Route::post('/barang/new/reject/{id}', [BarangWfgController::class, 'reject'])->name('wfg.master.barang.new.reject');
                Route::post('/barang/store', [BarangWfgController::class, 'store'])->name('wfg.master.barang.store');
                Route::get('/barang/data', [BarangWfgController::class, 'data'])->name('wfg.master.barang.data');
                Route::put('/barang/update/{id}', [BarangWfgController::class, 'update'])->name('wfg.master.barang.update');
                Route::delete('/barang/delete/{id}', [BarangWfgController::class, 'destroy'])->name('wfg.master.barang.delete');
                Route::post('/master/barang/restore/{id}', [BarangWfgController::class, 'restore'])->name('wfg.master.barang.restore');
                Route::delete('/master/barang/force-delete/{id}', [BarangWfgController::class, 'forceDelete'])
                    ->name('wfg.master.barang.forceDelete');
                Route::post('/barang/import', [BarangWfgController::class, 'import'])->name('wfg.master.barang.import');
                Route::get('/barang/template', [BarangWfgController::class, 'downloadTemplate'])->name('wfg.master.barang.template');
            });
        });

        // Master User
        Route::prefix('user')->group(function () {
            Route::get('/index', [UserController::class, 'index'])->name('user.index');
            Route::get('/get-data', [UserController::class, 'create'])->name('user.getData');
            Route::post('/store', [UserController::class, 'store'])->name('user.store');
            Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');
            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
            Route::put('/update/{id}', [UserController::class, 'update'])->name('user.update');
            Route::get('/statistik', [UserController::class, 'statisktik'])->name('user.statistik');
        });
    });

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
});
