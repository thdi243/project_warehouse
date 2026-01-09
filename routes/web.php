<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Events\ShowPortalNotification;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Wrm\P2HController;
use App\Http\Controllers\Wsp\TkbmController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\Wsp\WspRakController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Wsp\WspBarangController;
use App\Http\Controllers\Wsp\StockOpnameController;
use App\Http\Controllers\Wsp\stock\StockOnHandController;
use App\Http\Controllers\Wsp\stock\StockLocationController;
use App\Http\Controllers\Wfg\stock_opname\BarangWfgController;
use App\Http\Controllers\Wsp\stock_move\WspIncomingController;
use App\Http\Controllers\Wsp\stock_move\WspOutgoingController;
use App\Http\Controllers\Wfg\stock_opname\StockOnHandWfgController;
use App\Http\Controllers\Wfg\stock_opname\StockOpnameWfgController;
use App\Http\Controllers\Wsp\purchase_requesition\WspPurchaseRequesitionController;

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

    Route::middleware('auth')->get('/me', function (Request $request) {
        return response()->json([
            'id' => $request->user()->id,
            'nama_lengkap' => $request->user()->nama_lengkap,
            'username' => $request->user()->username,
            'bagian' => $request->user()->bagian,
            'image' => $request->user()->image
                ? asset('storage/' . $request->user()->image)
                : null,
        ]);
    });

    // Free access
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('maintenance', [WarehouseController::class, 'maintenanceView'])->name('maintenance');

    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profileIndex'])->name('user.profile');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/update/{id}', [UserController::class, 'update'])->name('user.update');
    });

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

        // Stock WSP
        Route::prefix('stock')->group(function () {
            Route::prefix('stock_manage')->group(function () {
                Route::get('/dashboard', [WarehouseController::class, 'dashboardStockWsp'])->name('stock.dashboard');
                Route::get('/stock-on-hand', [WarehouseController::class, 'stockOnHandView'])->name('stock.stock-on-hand');
                Route::get('/location', [WarehouseController::class, 'stockLocView'])->name('stock.stock-location');
                Route::post('/loc/store', [StockLocationController::class, 'store'])->name('stock.loc_store');
                Route::get('/loc/show/{id}', [StockLocationController::class, 'show'])->name('stock.loc_show');
                Route::put('/loc/update/{id}', [StockLocationController::class, 'update'])->name('stock.loc_update');
                Route::delete('/loc/delete/{id}', [StockLocationController::class, 'destroy'])->name('stock.loc_delete');
                Route::get('/loc/data', [StockLocationController::class, 'getDataStockLocation'])->name('stock.loc_data');
                Route::get('/loc/download', [StockLocationController::class, 'downloadTemplate'])->name('stock.loc_download');
                Route::post('/loc/upload', [StockLocationController::class, 'upload'])->name('stock.loc_upload');
                Route::get('/loc/data-barang', [StockLocationController::class, 'getBarang'])->name('stock.loc_data_barang');

                Route::get('/stock-on-hand/index', [WarehouseController::class, 'sohView'])->name('stock.soh.index');
                Route::get('/soh/data', [StockOnHandController::class, 'getDataSOH'])->name('stock.soh_data');
                Route::get('/soh/data-barang', [StockOnHandController::class, 'getBarang'])->name('stock.data_barang');
                Route::get('/soh/show/{id}', [StockOnHandController::class, 'show'])->name('stock.soh_show');
                Route::post('/soh/store', [StockOnHandController::class, 'store'])->name('stock.soh_store');
                Route::put('/soh/update/{id}', [StockOnHandController::class, 'update'])->name('stock.soh_update');
                Route::delete('/soh/delete/{id}', [StockOnHandController::class, 'destroy'])->name('stock.soh_delete');
                Route::get('/soh/download', [StockOnHandController::class, 'downloadTemplate'])->name('stock.soh_download');
                Route::post('/soh/upload', [StockOnHandController::class, 'upload'])->name('stock.soh_upload');
                Route::get('/opname', [WarehouseController::class, 'opnameIndex'])->name('stock.opname');
                // Route::get('/rak/list', [WarehouseController::class, 'rakList'])->name('wsp.rak.list');
                Route::get('/inventory', [WarehouseController::class, 'rakInventory'])->name('stock.inventory');
            });

            Route::prefix('stock_move')->group(function () {
                Route::get('/index', [WarehouseController::class, 'viewStockMove'])->name('stock.move.index');

                Route::prefix('incoming')->group(function () {
                    Route::get('/index', [WspIncomingController::class, 'viewIncoming'])->name('stock.move.incoming.index');
                    Route::get('/download', [WspIncomingController::class, 'downloadTemplate'])->name('stock.move.incoming.download');
                    Route::post('/upload', [WspIncomingController::class, 'upload'])->name('stock.move.incoming.upload');
                    Route::get('/show/{id}', [WspIncomingController::class, 'show'])->name('stock.move.incoming.show');
                    Route::post('/store', [WspIncomingController::class, 'store'])->name('stock.move.incoming.store');
                    Route::put('/update/{id}', [WspIncomingController::class, 'update'])->name('stock.move.incoming.update');
                    Route::delete('/delete/{id}', [WspIncomingController::class, 'destroy'])->name('stock.move.incoming.delete');
                });

                Route::prefix('outgoing')->group(function () {
                    Route::get('/index', [WspOutgoingController::class, 'viewOutgoing'])->name('stock.move.outgoing.index');
                    Route::get('/download', [WspOutgoingController::class, 'downloadTemplate'])->name('stock.move.outgoing.download');
                    Route::post('/upload', [WspOutgoingController::class, 'upload'])->name('stock.move.outgoing.upload');
                    Route::get('/show/{id}', [WspOutgoingController::class, 'show'])->name('stock.move.outgoing.show');
                    Route::post('/store', [WspOutgoingController::class, 'store'])->name('stock.move.outgoing.store');
                    Route::put('/update/{id}', [WspOutgoingController::class, 'update'])->name('stock.move.outgoing.update');
                    Route::delete('/delete/{id}', [WspOutgoingController::class, 'destroy'])->name('stock.move.outgoing.delete');
                });
            });
        });

        // Purchase Requesition
        Route::prefix('purchase-requesition')->group(function () {
            Route::post('/store', [WspPurchaseRequesitionController::class, 'store'])->name('stock.pr.store');
            Route::post('/reserved', [WspPurchaseRequesitionController::class, 'reserved'])->name('stock.pr.reserved');
            Route::get('/my-reservations', [WspPurchaseRequesitionController::class, 'myReservations']);
            Route::delete('/release/{id}', [WspPurchaseRequesitionController::class, 'release'])->name('stock.pr.release');
            Route::delete('/release-session/{id}', [WspPurchaseRequesitionController::class, 'releaseSession'])->name('stock.pr.release-session');
            Route::get('/index', [WspPurchaseRequesitionController::class, 'index'])->name('stock.pr.index');
            Route::delete('/delete/{id}', [WspPurchaseRequesitionController::class, 'destroy'])->name('stock.pr.delete');
            Route::get('/show/{id}', [WspPurchaseRequesitionController::class, 'show'])->name('stock.pr.show');
            Route::get('/getRiwayat', [WspPurchaseRequesitionController::class, 'getRiwayatPR'])->name('stock.pr.riwayat');
            Route::get('/approval-action/{id}', [WspPurchaseRequesitionController::class, 'getRiwayatPR'])->name('stock.pr.approval-action');
        });
    });

    // Warehouse Raw Material
    Route::middleware(['auth', 'access:warehouse_raw_material,warehouse_finish_goods'])->group(function () {
        // P2H
        Route::prefix('p2h')->group(function () {
            Route::get('/online/index', [P2HController::class, 'index'])->name('p2h.online.index');
            Route::post('/store/forklift/assignment', [P2HController::class, 'storeForkliftAssignment']);
            Route::post('/update/forklift/assignment', [P2HController::class, 'updateForkliftAssignment']);
            Route::get('/online/data', [WarehouseController::class, 'p2hData'])->name('p2h.online.data');
            Route::get('/registration/forklift', [WarehouseController::class, 'showRegForklift'])->name('p2h.registration.forklift');

            // Pallet Mover
            Route::post('/store/pallet-mover/assignment', [P2HController::class, 'storePallMovAssignment']);
            Route::get('/registration/pallet-mover', [WarehouseController::class, 'showRegPalletMover'])->name('p2h.registration.pallet-mover');
            Route::post('/update/pallet-mover/assignment/{id}', [P2HController::class, 'updatePallMovAssignment']);
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

    // Master Data Management
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
            Route::get('/statistik', [UserController::class, 'statisktik'])->name('user.statistik');
        });
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/notif', [NotificationController::class, 'index'])->name('notifications');
        // Route::post('/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/delete/{id}', [NotificationController::class, 'destroy'])->name('notifications.delete');
        Route::delete('/notifications/delete-all', [NotificationController::class, 'destroyAll'])->name('notifications.delete-all');
    });

    Route::get('/app/{any?}', function () {
        return view('app');
    })->where('any', '.*');
});
