<?php

use App\Events\ShowPortalNotification;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard\BpsDashboardController;
use App\Http\Controllers\Dashboard\IkatTerpalDashboardController;
use App\Http\Controllers\Dashboard\WfgBongkarMuatDashboardController;
use App\Http\Controllers\Dashboard\WrmInventoryController;
use App\Http\Controllers\Dashboard\StockOpnameDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Tkbm\ikat_terpal\IkatTerpalController;
use App\Http\Controllers\Tkbm\ikat_terpal\MasterIkatTerpalController;
use App\Http\Controllers\TokenAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Vehicle\VehicleTrackingController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\Wfg\BongkarMuatController;
use App\Http\Controllers\Wfg\MasterDestinasiController;
use App\Http\Controllers\Wfg\BarangWfgController;
use App\Http\Controllers\Wfg\stock_opname\StockOnHandWfgController;
use App\Http\Controllers\Wfg\stock_opname\StockOpnameWfgController;
use App\Http\Controllers\Wrm\Inventory\InboundController;
use App\Http\Controllers\Wrm\Inventory\MonitoringController;
use App\Http\Controllers\Wrm\Inventory\OutboundController;
use App\Http\Controllers\Wrm\Inventory\StockTransferController;
use App\Http\Controllers\Wrm\StockOpname\WrmStockOnHandController;
use App\Http\Controllers\Wrm\StockOpname\WrmStockOpnameController;
use App\Http\Controllers\Wrm\MasterBarangController;
use App\Http\Controllers\Wrm\MasterBinController;
use App\Http\Controllers\Wrm\MasterLocationController;
use App\Http\Controllers\Wrm\MasterPalletController;
use App\Http\Controllers\Wrm\MasterSupplierController;
use App\Http\Controllers\Wrm\P2HController;
use App\Http\Controllers\Wcp\StockOpname\WcpStockOnHandController;
use App\Http\Controllers\Wcp\StockOpname\WcpStockOpnameController;
use App\Http\Controllers\Wsp\purchase_requesition\WspPurchaseRequesitionController;
use App\Http\Controllers\Wsp\stock_move\WspIncomingController;
use App\Http\Controllers\Wsp\stock_move\WspOutgoingController;
use App\Http\Controllers\Wsp\stock\StockLocationController;
use App\Http\Controllers\Wsp\stock\StockOnHandController;
use App\Http\Controllers\Wsp\StockOpname\WspStockOpnameController;
use App\Http\Controllers\Wsp\StockOpname\WspStockOnHandController as WspStockOnHandSOController;
use App\Http\Controllers\Wsp\TkbmController;
use App\Http\Controllers\Wsp\WspBarangController;
use App\Http\Controllers\Wsp\WspRakController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\Api\TkbmDashboardController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/temp-login', function () {
    Auth::loginUsingId(3);
    return redirect('/wrm/inventory/draft-outbound/15/assign-driver');
});

// Auth
Route::middleware('web')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/auth/sso/callback', [TokenAuthController::class, 'callback'])->name('auth.sso.callback');
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
            'departemen' => $request->user()->departemen,
            'image' => $request->user()->image
                ? asset('storage/' . $request->user()->image)
                : null,
        ]);
    });

    // Free access
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('maintenance', [WarehouseController::class, 'maintenanceView'])->name('maintenance');
    Route::prefix('wsp')->group(function () {
        // Stock On Hand
        Route::get('/stock-on-hand', [WarehouseController::class, 'stockOnHandView'])->name('stock.soh.user');
        Route::get('/get-data', [StockOnHandController::class, 'getDataSOH'])->name('stock.soh_data');
        Route::get('/export', [StockOnHandController::class, 'exportExcel'])->name('stock.soh_export');
    });
    // End Free access

    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profileIndex'])->name('user.profile');
        Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('user.profile.edit');
        Route::put('/profile/update', [UserController::class, 'updateProfile'])->name('user.profile.update');
        Route::get('/profile/change-password', [UserController::class, 'changePassword'])->name('user.profile.change-password');
        Route::put('/profile/change-password', [UserController::class, 'updatePassword'])->name('user.profile.update-password');
        Route::get('/profile/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
        Route::put('/profile/update/{id}', [UserController::class, 'update'])->name('user.update');
    });

    // Dashboard
    Route::middleware(['auth'])->group(function () {
        Route::prefix('dashboard')->group(function () {
            // Main
            Route::view('/main', 'dashboard.foreman_spv_home')->name('dashboard.main');
            // TKBM
            Route::get('/bps', [BpsDashboardController::class, 'index'])->name('dashboard.tkbm')
                ->middleware(['permission:dashboard-bps']);
            // Route::get('/tkbm/get-data', [TkbmDashboardController::class, 'tkbmDashboard'])->name('dashboard.tkbm.data');
            Route::view('/p2h', 'dashboard.p2h_dashboard')->name('dashboard.p2h')
                ->middleware(['permission:dashboard-p2h']);
            // Rak Management
            Route::view('/rak', 'dashboard.rak_dashboard')->name('dashboard.rak')
                ->middleware(['permission:dashboard-rak']);

            Route::get('/ikat-terpal', [IkatTerpalDashboardController::class, 'index'])->name('dashboard.ikat-terpal');
            Route::get('/wrm/index', [WrmInventoryController::class, 'index'])->name('dashboard.wrm.index')->middleware(['permission:dashboard-wrm']);
            Route::get('/wfg/bongkar-muat', [WfgBongkarMuatDashboardController::class, 'index'])->name('dashboard.wfg.bongkar-muat');

            // Vehicle Monitoring Dashboard
            Route::get('/vehicle', [VehicleTrackingController::class, 'dashboard'])->name('dashboard.vehicle')
                ->middleware(['permission:dashboard-vehicle-monitoring']);
            Route::get('/vehicle/data', [VehicleTrackingController::class, 'dashboardData'])->name('dashboard.vehicle.data')
                ->middleware(['permission:dashboard-vehicle-monitoring']);
            Route::get('/vehicle/visual', [VehicleTrackingController::class, 'visualDashboard'])->name('dashboard.vehicle.visual')
                ->middleware(['permission:dashboard-vehicle-monitoring']);

            // Stock Opname Dashboard
            Route::get('/stock-opname', [StockOpnameDashboardController::class, 'index'])->name('dashboard.stock-opname')
                ->middleware(['permission:dashboard']);
            Route::get('/stock-opname/data', [StockOpnameDashboardController::class, 'getData'])->name('dashboard.stock-opname.data')
                ->middleware(['permission:dashboard']);
        });
    });

    // Warehouse Sparepart (TKBM, PR)
    Route::middleware(['auth'])->group(function () {
        // TKBM
        Route::prefix('tkbm')->middleware(['permission:tkbm'])->group(function () {
            Route::middleware(['permission:tkbm-bps'])->group(function () {
                Route::prefix('bps')->group(function () {
                    Route::get('/input', [WarehouseController::class, 'stock'])->name('tkbm.stock');
                    Route::post('/simpan', [TkbmController::class, 'store'])->name('tkbm.store');
                    Route::get('/data', [TkbmController::class, 'index'])->name('tkbm.data');
                    Route::get('/data/show', [TkbmController::class, 'show'])->name('tkbm.data.show');
                    Route::get('/data/edit/{id}', [TkbmController::class, 'edit'])->name('tkbm.data.edit');
                    Route::put('/data/update/{id}', [TkbmController::class, 'update'])->name('tkbm.data.update');
                    Route::delete('/data/delete/{id}', [TkbmController::class, 'destroy'])->name('tkbm.data.delete');
                    Route::get('/data/export', [TkbmController::class, 'export'])->name('tkbm.data.export');
                    Route::get('/export-pdf', [TkbmController::class, 'exportPdf']);
                    // Route::get('/data/export-pdf', [TkbmController::class, 'exportPdf'])->name('tkbm.data.export-pdf');
                });
            });

            Route::middleware(['permission:tkbm-ikat-terpal'])->group(function () {
                Route::prefix('ikat-terpal')->group(function () {
                    Route::get('/index', [IkatTerpalController::class, 'index'])->name('tkbm.ikat-terpal.index');
                    Route::get('/report', [IkatTerpalController::class, 'report'])->name('tkbm.ikat-terpal.report');
                    Route::get('/report/print-pdf', [IkatTerpalController::class, 'exportPdf']);
                    Route::post('/store/fee', [MasterIkatTerpalController::class, 'storeFee']);
                    Route::post('/store/produk', [MasterIkatTerpalController::class, 'storeProduk']);
                    Route::post('/store', [IkatTerpalController::class, 'store']);
                    Route::delete('/destroy/{id}', [IkatTerpalController::class, 'destroy']);
                    Route::get('/show/{id}', [IkatTerpalController::class, 'show']);
                    Route::put('/update/{id}', [IkatTerpalController::class, 'update']);
                });
            });
        });

        // Stock WSP
        Route::prefix('wsp')->middleware(['permission:wsp-menu'])->group(function () {
            Route::prefix('stock-location')->middleware(['permission:wsp-stock-location'])->group(function () {
                Route::get('/', [WarehouseController::class, 'stockLocView'])->name('stock.stock-location');
                Route::post('/store', [StockLocationController::class, 'store'])->name('stock.loc_store');
                Route::get('/show/{id}', [StockLocationController::class, 'show'])->name('stock.loc_show');
                Route::put('/update/{id}', [StockLocationController::class, 'update'])->name('stock.loc_update');
                Route::delete('/delete/{id}', [StockLocationController::class, 'destroy'])->name('stock.loc_delete');
                Route::get('/data', [StockLocationController::class, 'getDataStockLocation'])->name('stock.loc_data');
                Route::get('/download', [StockLocationController::class, 'downloadTemplate'])->name('stock.loc_download');
                Route::post('/upload', [StockLocationController::class, 'upload'])->name('stock.loc_upload');
                Route::get('/data-barang', [StockLocationController::class, 'getBarang'])->name('stock.loc_data_barang');
            });

            Route::prefix('soh')->middleware(['permission:wsp-soh'])->group(function () {
                Route::get('/', [WarehouseController::class, 'stockOnHandView'])->name('stock.stock-on-hand');
                Route::get('/data', [WarehouseController::class, 'sohView'])->name('stock.soh.index');
                // Route::get('/get-data', [StockOnHandController::class, 'getDataSOH'])->name('stock.soh_data');
                Route::get('/data-barang', [StockOnHandController::class, 'getBarang'])->name('stock.data_barang');
                Route::get('/show/{id}', [StockOnHandController::class, 'show'])->name('stock.soh_show');
                Route::post('/store', [StockOnHandController::class, 'store'])->name('stock.soh_store');
                Route::put('/update/{id}', [StockOnHandController::class, 'update'])->name('stock.soh_update');
                Route::delete('/delete/{id}', [StockOnHandController::class, 'destroy'])->name('stock.soh_delete');
                Route::get('/download', [StockOnHandController::class, 'downloadTemplate'])->name('stock.soh_download');
                Route::post('/upload', [StockOnHandController::class, 'upload'])->name('stock.soh_upload');
                Route::get('/opname', [WarehouseController::class, 'opnameIndex'])->name('stock.opname');
                // Route::get('/rak/list', [WarehouseController::class, 'rakList'])->name('wsp.rak.list');
                Route::get('/inventory', [WarehouseController::class, 'rakInventory'])->name('stock.inventory');
            });

            Route::prefix('incoming')->middleware(['permission:wsp-incoming'])->group(function () {
                Route::get('/', [WspIncomingController::class, 'viewIncoming'])->name('stock.move.incoming.index');
                Route::get('/download', [WspIncomingController::class, 'downloadTemplate'])->name('stock.move.incoming.download');
                Route::post('/upload', [WspIncomingController::class, 'upload'])->name('stock.move.incoming.upload');
                Route::get('/show/{id}', [WspIncomingController::class, 'show'])->name('stock.move.incoming.show');
                Route::post('/store', [WspIncomingController::class, 'store'])->name('stock.move.incoming.store');
                Route::put('/update/{id}', [WspIncomingController::class, 'update'])->name('stock.move.incoming.update');
                Route::delete('/delete/{id}', [WspIncomingController::class, 'destroy'])->name('stock.move.incoming.delete');
            });

            Route::prefix('outgoing')->middleware(['permission:wsp-outgoing'])->group(function () {
                Route::get('/', [WspOutgoingController::class, 'viewOutgoing'])->name('stock.move.outgoing.index');
                Route::get('/download', [WspOutgoingController::class, 'downloadTemplate'])->name('stock.move.outgoing.download');
                Route::post('/upload', [WspOutgoingController::class, 'upload'])->name('stock.move.outgoing.upload');
                Route::get('/show/{id}', [WspOutgoingController::class, 'show'])->name('stock.move.outgoing.show');
                Route::post('/store', [WspOutgoingController::class, 'store'])->name('stock.move.outgoing.store');
                Route::put('/update/{id}', [WspOutgoingController::class, 'update'])->name('stock.move.outgoing.update');
                Route::delete('/delete/{id}', [WspOutgoingController::class, 'destroy'])->name('stock.move.outgoing.delete');
            });
        });

        // Purchase Requesition
        // Route::prefix('purchase-requesition')->middleware(['permission:wsp-form-pr,wsp-approval-pr'])->group(function () {
        Route::prefix('purchase-requesition')->group(function () {
            Route::get('/index', [WspPurchaseRequesitionController::class, 'index'])->name('stock.pr.index');
            Route::get('/history', [WspPurchaseRequesitionController::class, 'history'])->name('stock.pr.history');
            Route::get('/approval', [WspPurchaseRequesitionController::class, 'approvalIndex'])->name('stock.pr.approval');
            Route::post('/store', [WspPurchaseRequesitionController::class, 'store'])->name('stock.pr.store');
            Route::post('/reserved', [WspPurchaseRequesitionController::class, 'reserved'])->name('stock.pr.reserved');
            Route::get('/my-reservations', [WspPurchaseRequesitionController::class, 'myReservations']);
            Route::delete('/release/{id}', [WspPurchaseRequesitionController::class, 'release'])->name('stock.pr.release');
            Route::delete('/release-session/{id}', [WspPurchaseRequesitionController::class, 'releaseSession'])->name('stock.pr.release-session');
            Route::delete('/delete/{id}', [WspPurchaseRequesitionController::class, 'destroy'])->name('stock.pr.delete');
            Route::get('/show/{id}', [WspPurchaseRequesitionController::class, 'show'])->name('stock.pr.show');
            Route::post('/approval-pr/action/{id}', [WspPurchaseRequesitionController::class, 'action'])->name('stock.pr.approval-action');
            Route::get('/getRiwayat', [WspPurchaseRequesitionController::class, 'getRiwayatPR'])->name('stock.pr.riwayat');
        });
    });

    // Warehouse Raw Material
    Route::middleware(['auth'])->group(function () {
        // P2H
        Route::prefix('p2h')->middleware(['permission:p2h,p2h-form,p2h-unit-regis'])->group(function () {
            Route::get('/online/index', [P2HController::class, 'index'])->name('p2h.online.index');
            Route::post('/store/forklift/assignment', [P2HController::class, 'storeForkliftAssignment']);
            Route::post('/update/forklift/assignment', [P2HController::class, 'updateForkliftAssignment']);
            Route::get('/online/data', [WarehouseController::class, 'p2hData'])->name('p2h.online.data');
            Route::get('/online/summary', [P2HController::class, 'summaryView'])->name('p2h.online.summary');
            Route::get('/online/summary/data', [P2HController::class, 'summaryData'])->name('p2h.online.summary.data');
            Route::get('/online/summary/history', [P2HController::class, 'historyData'])->name('p2h.online.summary.history');
            Route::get('/registration/forklift', [WarehouseController::class, 'showRegForklift'])->name('p2h.registration.forklift')
                ->middleware(['permission:p2h-unit-regis']);
            Route::post('/update/multi', [P2HController::class, 'updateMultiShiftP2H']);

            // Pallet Mover
            Route::post('/store/pallet-mover/assignment', [P2HController::class, 'storePallMovAssignment']);
            Route::get('/registration/pallet-mover', [WarehouseController::class, 'showRegPalletMover'])->name('p2h.registration.pallet-mover')
                ->middleware(['permission:p2h-unit-regis']);
            Route::post('/update/pallet-mover/assignment/{id}', [P2HController::class, 'updatePallMovAssignment']);
            Route::post('/update/multi-pallet', [P2HController::class, 'updateMultiShiftPalletMover']);
        });

        Route::prefix('wrm')->middleware(['permission:wrm-menu'])->group(function () {
            // Inventory Raw Material
            Route::prefix('inventory')->middleware(['permission:wrm-inventory-upload,wrm-inventory-soh,wrm-inventory-draft-outbound,wrm-inventory-data-draft-outbound,wrm-inventory-transfer-history,wrm-inventory-forklift-jobs'])->group(function () {
                Route::get('/inbound', [InboundController::class, 'viewInbound'])->name('wrm.inventory.viewInbound');
                Route::get('/data-inbound', [InboundController::class, 'dataInbound'])->name('wrm.inventory.dataInbound');
                Route::get('/get-filter-inbound', [InboundController::class, 'getFilterInbound'])->name('wrm.inventory.getFilterInbound');
                Route::get('/stock-on-hand', [InboundController::class, 'index'])->name('wrm.inventory.index');
                Route::get('/data-upload', [InboundController::class, 'indexUpload'])->name('wrm.inventory.index-upload');
                Route::post('/store', [InboundController::class, 'store'])->name('wrm.inventory.store');
                Route::post('/store-upload', [InboundController::class, 'storeUpload'])->name('wrm.inventory.store-upload');
                Route::get('/data', [InboundController::class, 'getData'])->name('wrm.inventory.getData');
                Route::get('/get-barang', [InboundController::class, 'getBarang'])->name('wrm.inventory.getBarang');
                Route::get('/get-location-ajax', [InboundController::class, 'getLocationAjax'])->name('wrm.inventory.getLocationsAjax');
                Route::get('/get-filter', [InboundController::class, 'getFilter'])->name('wrm.inventory.getFilter');
                Route::put('/update/{id}', [InboundController::class, 'update'])->name('wrm.inventory.update');
                Route::post('/mass-update-status', [InboundController::class, 'massUpdateStatus'])->name('wrm.inventory.mass-update-status');
                Route::post('/mass-update-group', [InboundController::class, 'massUpdateGroup'])->name('wrm.inventory.mass-update-group');
                Route::post('/mass-delete', [InboundController::class, 'massDelete'])->name('wrm.inventory.mass-delete');
                Route::delete('/delete/{id}', [InboundController::class, 'destroy'])->name('wrm.inventory.delete');
                Route::get('/template', [InboundController::class, 'downloadTemplate'])->name('wrm.inventory.template');
                Route::post('/upload', [InboundController::class, 'upload'])->name('wrm.inventory.upload');
                Route::post('/non-gula-upload', [InboundController::class, 'uploadNonGula'])->name('wrm.inventory.non-gula-upload');
                Route::post('/non-gula-upload-excel', [InboundController::class, 'uploadNonGulaExcel'])->name('wrm.inventory.non-gula-upload-excel');
                Route::post('/export-excel', [InboundController::class, 'exportExcel'])->name('wrm.inventory.export-excel');
                Route::post('/export-list-excel', [InboundController::class, 'exportListExcel'])->name('wrm.inventory.export-list-excel');
                Route::get('/transfer-history', [StockTransferController::class, 'index'])->name('wrm.inventory.index-transfer');
                Route::get('/transfer-data', [StockTransferController::class, 'getData'])->name('wrm.inventory.get-transfer-data');
                Route::delete('/transfer-detail/delete/{id}', [StockTransferController::class, 'destroyDetail'])->name('wrm.inventory.delete-transfer-detail');
                Route::get('/outbound-template', [StockTransferController::class, 'downloadTemplate'])->name('wrm.inventory.outbound-template');
                Route::post('/outbound-upload', [StockTransferController::class, 'upload'])->name('wrm.inventory.outbound-upload');
                Route::post('/cancel-upload', [InboundController::class, 'cancelUpload'])->name('wrm.inventory.cancel-upload');
                Route::get('/select-location', [InboundController::class, 'selectLocationView'])->name('wrm.inventory.select-location');
                Route::get('/plot-location', [InboundController::class, 'plotLocation'])->name('wrm.inventory.plot-location');
                Route::get('/draft-outbound', [OutboundController::class, 'formOutbound'])->name('wrm.inventory.draft-outbound');
                Route::get('/draft-outbound/data', [OutboundController::class, 'dataOutbound'])->name('wrm.inventory.data-outbound');
                Route::get('/search-outbound', [OutboundController::class, 'searchOutbound'])->name('wrm.inventory.search-outbound');
                Route::post('/store-outbound', [OutboundController::class, 'submitOutbound'])->name('wrm.inventory.submit-outbound');
                Route::get('/get-data-outbound', [OutboundController::class, 'getData'])->name('wrm.inventory.get-data-outbound');
                Route::get('/detail-data-outbound/{id}', [OutboundController::class, 'getOutboundDetail'])->name('wrm.inventory.get-detail-outbound');
                Route::post('/update-outbound/{id}', [OutboundController::class, 'updateOutbound'])->name('wrm.inventory.update-outbound');
                Route::post('/cancel-outbound/{id}', [OutboundController::class, 'cancelOutbound'])->name('wrm.inventory.cancel-outbound');
                Route::post('/cancel-outbound-item/{id}', [OutboundController::class, 'cancelOutboundItem'])->name('wrm.inventory.cancel-outbound-item');
                Route::post('/cancel-outbound-items', [OutboundController::class, 'cancelOutboundItems'])->name('wrm.inventory.cancel-outbound-items');
                Route::get('/magic-number/{id}', [OutboundController::class, 'printMagicNumber'])->name('wrm.inventory.magic-number');
                Route::post('/assign-driver/{id}', [OutboundController::class, 'assignDriver'])->name('wrm.inventory.assign-driver');
                Route::get('/draft-outbound/{id}/assign-driver', [OutboundController::class, 'assignDriverPage'])->name('wrm.inventory.assign-driver-page');
                Route::post('/assign-driver-items', [OutboundController::class, 'assignDriverItems'])->name('wrm.inventory.assign-driver-items');
                Route::post('/complete-transfer/{id}', [OutboundController::class, 'completeTransfer'])->name('wrm.inventory.complete-transfer');

                // Forklift Self-Service Jobs
                Route::get('/forklift-jobs', [OutboundController::class, 'forkliftJobs'])->name('wrm.inventory.forklift-jobs');
                Route::get('/forklift-jobs/data', [OutboundController::class, 'forkliftJobsData'])->name('wrm.inventory.forklift-jobs-data');
                Route::post('/forklift-jobs/complete', [OutboundController::class, 'forkliftJobsComplete'])->name('wrm.inventory.forklift-jobs-complete');

                // Monitoring PPIC & Purchasing
                Route::prefix('monitoring')->group(function () {
                    Route::get('/', [MonitoringController::class, 'indexSummaryStock'])->name('wrm.inventory.monitoring.index');
                    Route::get('/ppic', [MonitoringController::class, 'indexPpic'])->name('wrm.inventory.monitoring.ppic.index');
                    Route::get('/purchasing', [MonitoringController::class, 'indexPurchasing'])->name('wrm.inventory.monitoring.purchasing.index');
                    Route::get('/summary-stock', [MonitoringController::class, 'indexSummaryStock'])->name('wrm.inventory.summary.stock');

                    Route::get('/summary/ppic', [MonitoringController::class, 'getSummaryPpic'])->name('wrm.inventory.monitoring.summary.ppic');
                    Route::get('/summary/purchasing', [MonitoringController::class, 'getSummaryPurchasing'])->name('wrm.inventory.monitoring.summary.purchasing');

                    Route::get('/data/soh', [MonitoringController::class, 'getSohData'])->name('wrm.inventory.monitoring.soh');
                    Route::get('/data/inbound', [MonitoringController::class, 'getInboundData'])->name('wrm.inventory.monitoring.inbound');
                    Route::get('/data/outbound', [MonitoringController::class, 'getOutboundData'])->name('wrm.inventory.monitoring.outbound');
                    Route::get('/data/transfer', [MonitoringController::class, 'getTransferData'])->name('wrm.inventory.monitoring.transfer');
                    Route::get('/data/ppic-stock', [MonitoringController::class, 'getPpicStockData'])->name('wrm.inventory.monitoring.ppic.stock-data');
                    Route::get('/data/purchasing-stock', [MonitoringController::class, 'getPurchasingStockData'])->name('wrm.inventory.monitoring.purchasing.stock-data');
                    Route::get('/data/summary-stock/item', [MonitoringController::class, 'getSummaryStockItemData'])->name('wrm.inventory.monitoring.summary-stock.item-data');
                    Route::get('/data/summary-stock/spb', [MonitoringController::class, 'getSummaryStockSpbData'])->name('wrm.inventory.monitoring.summary-stock.spb-data');
                    Route::get('/data/summary-stock/group', [MonitoringController::class, 'getSummaryStockGroupData'])->name('wrm.inventory.monitoring.summary-stock.group-data');
                    Route::get('/data/summary-stock/group-meta', [MonitoringController::class, 'getSummaryStockGroupMeta'])->name('wrm.inventory.monitoring.summary-stock.group-meta');
                    Route::get('/data/summary-stock/supplier', [MonitoringController::class, 'getSummaryStockSupplierData'])->name('wrm.inventory.monitoring.summary-stock.supplier-data');
                    Route::get('/data/spb-detail', [MonitoringController::class, 'getSpbDetailData'])->name('wrm.inventory.monitoring.spb-detail.data');
                    Route::get('/data/moving-average', [MonitoringController::class, 'getMovingAverageData'])->name('wrm.inventory.monitoring.moving-average.data');
                    Route::get('/data/summary-stock/inbound-monthly', [MonitoringController::class, 'getSummaryStockInboundMonthlyData'])->name('wrm.inventory.monitoring.summary-stock.inbound-monthly-data');
                    Route::get('/data/summary-stock/inbound-monthly-meta', [MonitoringController::class, 'getSummaryStockInboundMonthlyMeta'])->name('wrm.inventory.monitoring.summary-stock.inbound-monthly-meta');

                    // Summary Stock Transfer
                    Route::get('/summary-transfer', [MonitoringController::class, 'indexSummaryTransfer'])->name('wrm.inventory.summary.transfer');
                    Route::get('/data/summary-transfer/item', [MonitoringController::class, 'getSummaryTransferItemData'])->name('wrm.inventory.monitoring.summary-transfer.item-data');
                    Route::get('/data/summary-transfer/spb', [MonitoringController::class, 'getSummaryTransferSpbData'])->name('wrm.inventory.monitoring.summary-transfer.spb-data');
                    Route::get('/data/summary-transfer/group-meta', [MonitoringController::class, 'getSummaryTransferGroupMeta'])->name('wrm.inventory.monitoring.summary-transfer.group-meta');
                    Route::get('/data/summary-transfer/group', [MonitoringController::class, 'getSummaryTransferGroupData'])->name('wrm.inventory.monitoring.summary-transfer.group-data');
                    Route::get('/data/summary-transfer/monthly-meta', [MonitoringController::class, 'getSummaryTransferMonthlyMeta'])->name('wrm.inventory.monitoring.summary-transfer.monthly-meta');
                    Route::get('/data/summary-transfer/monthly', [MonitoringController::class, 'getSummaryTransferMonthlyData'])->name('wrm.inventory.monitoring.summary-transfer.monthly-data');
                });
            });

            // Stock Opname WRM
            Route::prefix('stock_opname')->middleware(['permission:stock-opname-wrm'])->group(function () {
                Route::post('/so/start', [WrmStockOpnameController::class, 'startOpname'])->name('wrm.startOpname');
                Route::get('/so/status', [WrmStockOpnameController::class, 'getStatusOpname'])->name('wrm.getStatusOpname');
                Route::get('/so/form', [WarehouseController::class, 'formSOWRM'])->name('wrm.stock_opname.form');
                Route::get('/so/getData', [WrmStockOpnameController::class, 'getData'])->name('wrm.stock_opname.getData');
                Route::post('/so/save-temp', [WrmStockOpnameController::class, 'saveTemp'])->name('wrm.stock_opname.save-temp');
                Route::post('/so/save-new-temp', [WrmStockOpnameController::class, 'saveTempNew'])->name('wrm.stock_opname.save-temp-new');
                Route::post('/so/save-final', [WrmStockOpnameController::class, 'processOpname'])->name('wrm.stock_opname.process');
                Route::delete('/so/reset-temp-row', [WrmStockOpnameController::class, 'resetTempRow'])->name('wrm.stock_opname.reset-temp-row');
                Route::get('/so/getDataTempBatch', [WrmStockOpnameController::class, 'getDataTempBatch'])->name('wrm.stock_opname.getTempBatch');
                Route::get('/so/getDataTempEdit/{sohId}', [WrmStockOpnameController::class, 'getDataTempEdit'])->name('wrm.stock_opname.getDataTempEdit');
                Route::post('/so/update-temp', [WrmStockOpnameController::class, 'updateTempBatch'])->name('wrm.stock_opname.update-temp');
                Route::delete('/so/delete-temp/{id}', [WrmStockOpnameController::class, 'destroyTemp'])->name('wrm.stock_opname.delete-temp');
                Route::get('/so/report', [WarehouseController::class, 'reportSOWRM'])->name('wrm.stock_opname.report');
                Route::get('/so/report/getData', [WrmStockOpnameController::class, 'getDataReport'])->name('wrm.stock_opname.report.getData');
                Route::get('/so/report/pending-approval', [WrmStockOpnameController::class, 'getPendingApprovalReport'])->name('wrm.stock_opname.report.pending-approval');
                Route::get('/so/report/detail/{id}', [WrmStockOpnameController::class, 'getReportDetail'])->name('wrm.stock_opname.report.detail');
                Route::post('/so/report/update/{id}', [WrmStockOpnameController::class, 'updateReportRow'])->name('wrm.stock_opname.report.update');
                Route::delete('/so/report/delete/{id}', [WrmStockOpnameController::class, 'deleteReportRow'])->name('wrm.stock_opname.report.delete');
                Route::delete('/so/report/detail/delete/{id}', [WrmStockOpnameController::class, 'deleteReportDetail'])->name('wrm.stock_opname.report.detail.delete');
                Route::get('/so/export', [WrmStockOpnameController::class, 'exportPdfSOWRM'])->name('wrm.stock_opname.export');
                Route::post('/so/send-approval', [WrmStockOpnameController::class, 'sendApproval'])->name('wrm.stock_opname.send-approval');
                Route::post('/so/update/status-approval', [WrmStockOpnameController::class, 'updateStatus'])->name('wrm.stock_opname.update.status-approval');
                Route::get('/so/approval/show/{id}', [WrmStockOpnameController::class, 'showApproval'])->name('wrm.stock_opname.approval.show');
                Route::get('/so/getDataApproval', [WrmStockOpnameController::class, 'getDataApproval'])->name('wrm.stock_opname.getDataApproval');

                // Stock on Hand SO WRM
                Route::get('/soh', [WarehouseController::class, 'uploadSOHWRM'])->name('wrm.stock_opname.soh');
                Route::post('/soh/store', [WrmStockOnHandController::class, 'store'])->name('wrm.stock_opname.soh.store');
                Route::delete('/soh/delete/{id}', [WrmStockOnHandController::class, 'destroy'])->name('wrm.stock_opname.soh.delete');
                Route::post('/soh/update/{id}', [WrmStockOnHandController::class, 'update'])->name('wrm.stock_opname.soh.update');
                Route::post('/soh/import', [WrmStockOnHandController::class, 'importExcel'])->name('wrm.stock_opname.soh.import');
                Route::get('/soh/template', [WrmStockOnHandController::class, 'downloadTemplate'])->name('wrm.stock_opname.soh.template');
                Route::get('/soh/list', [WrmStockOnHandController::class, 'getList'])->name('wrm.stock_opname.soh.list');
                Route::get('/soh/getBarang', [WrmStockOnHandController::class, 'getBarang'])->name('wrm.stock_opname.soh.getBarang');
                Route::get('/soh/getSpbList', [WrmStockOnHandController::class, 'getSpbList'])->name('wrm.stock_opname.soh.getSpbList');
                Route::get('/soh/getPalletList', [WrmStockOnHandController::class, 'getPalletList'])->name('wrm.stock_opname.soh.getPalletList');
                Route::get('/soh/getPalletQty', [WrmStockOnHandController::class, 'getPalletQty'])->name('wrm.stock_opname.soh.getPalletQty');
                Route::get('/soh/fetch-source-details', [WrmStockOnHandController::class, 'fetchSourceDetails'])->name('wrm.stock_opname.soh.fetchSourceDetails');
                Route::get('/soh/show/{id}', [WrmStockOnHandController::class, 'show'])->name('wrm.stock_opname.soh.show');
                Route::delete('/soh/reset-all', [WrmStockOnHandController::class, 'resetAll'])->name('wrm.stock_opname.soh.reset_all');
            });
        });
    });

    // Warehouse Finish Goods
    Route::middleware(['auth'])->group(function () {
        Route::prefix('wfg')->group(function () {

            // Bongkar Muat
            Route::prefix('bongkar-muat')->group(function () {
                Route::get('/', [BongkarMuatController::class, 'index'])->name('wfg.bongkar_muat.index');
                Route::get('/data', [BongkarMuatController::class, 'data'])->name('wfg.bongkar_muat.data');
                Route::get('/approval', [BongkarMuatController::class, 'approval'])->name('wfg.bongkar_muat.approval');
                Route::get('/approval-data', [BongkarMuatController::class, 'approvalData'])->name('wfg.bongkar_muat.approval_data');
                Route::get('/form', [BongkarMuatController::class, 'create'])->name('wfg.bongkar_muat.form');
                Route::post('/store', [BongkarMuatController::class, 'store'])->name('wfg.bongkar_muat.store');
                Route::post('/save-draft', [BongkarMuatController::class, 'saveDraft'])->name('wfg.bongkar_muat.save_draft');
                Route::post('/cancel-draft', [BongkarMuatController::class, 'cancelDraft'])->name('wfg.bongkar_muat.cancel_draft');
                Route::post('/follow-up-checker/{id}', [BongkarMuatController::class, 'followUpChecker'])->name('wfg.bongkar_muat.follow_up_checker');
                Route::delete('/{id}', [BongkarMuatController::class, 'destroy'])->name('wfg.bongkar_muat.destroy');
                Route::get('/show/{id}', [BongkarMuatController::class, 'show'])->name('wfg.bongkar_muat.show');
                Route::post('/approve-checker/{id}', [BongkarMuatController::class, 'approveChecker'])->name('wfg.bongkar_muat.approve_checker');
                Route::post('/approve-driver/{id}', [BongkarMuatController::class, 'approveDriver'])->name('wfg.bongkar_muat.approve_driver');
                Route::post('/validate/{id}', [BongkarMuatController::class, 'validateOrder'])->name('wfg.bongkar_muat.validate');
                Route::get('/scan', [BongkarMuatController::class, 'scanBarcode'])->name('wfg.bongkar_muat.scan');
                Route::get('/search-materials', [BongkarMuatController::class, 'searchMaterials'])->name('wfg.bongkar_muat.search_materials');
                Route::get('/download/{id}', [BongkarMuatController::class, 'download'])->name('wfg.bongkar_muat.download');
                Route::put('/update-item/{id}', [BongkarMuatController::class, 'updateItem'])->name('wfg.bongkar_muat.update_item');
                Route::delete('/delete-item/{id}', [BongkarMuatController::class, 'deleteItem'])->name('wfg.bongkar_muat.delete_item');
                Route::put('/update/{id}', [BongkarMuatController::class, 'update'])->name('wfg.bongkar_muat.update');
            });

            // Maste Barang SO WFG
            Route::prefix('master')->middleware(['permission:master-wfg'])->group(function () {
                Route::get('/barang/index', [BarangWfgController::class, 'index'])->name('wfg.master.barang.index');
                Route::post('/barang/store', [BarangWfgController::class, 'store'])->name('wfg.master.barang.store');
                Route::get('/barang/data', [BarangWfgController::class, 'data'])->name('wfg.master.barang.data');
                Route::put('/barang/update/{id}', [BarangWfgController::class, 'update'])->name('wfg.master.barang.update');
                Route::delete('/barang/delete/{id}', [BarangWfgController::class, 'destroy'])->name('wfg.master.barang.delete');
            });

            // Stock Opname WFG
            Route::prefix('stock_opname')->middleware(['permission:stock-opname-wfg'])->group(function () {
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
                Route::get('/sop/report/pending-approval', [StockOpnameWfgController::class, 'getPendingApprovalReport'])->name('wfg.stock_opname.report.pending-approval');
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

    // Warehouse Packaging Material
    Route::middleware(['auth'])->prefix('wpm')->name('wpm.')->group(function () {
        // Stock Opname WPM
        Route::prefix('stock_opname')->middleware(['permission:stock-opname-wpm'])->name('stock_opname.')->group(function () {
            Route::post('/start', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'startOpname'])->name('startOpname');
            Route::get('/status', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'getStatusOpname'])->name('getStatusOpname');
            Route::get('/form', [WarehouseController::class, 'formSOWPM'])->name('form');
            Route::get('/getData', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'getData'])->name('getData');
            Route::post('/save-temp', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'saveTemp'])->name('save-temp');
            Route::post('/save-new-temp', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'saveTempNew'])->name('save-temp-new');
            Route::post('/save-final', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'processOpname'])->name('process');
            Route::delete('/reset-temp-row', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'resetTempRow'])->name('reset-temp-row');
            Route::get('/getDataTempBatch', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'getDataTempBatch'])->name('getTempBatch');
            Route::get('/getDataTempEdit/{sohId}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'getDataTempEdit'])->name('getDataTempEdit');
            Route::post('/update-temp', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'updateTempBatch'])->name('update-temp');
            Route::delete('/delete-temp/{id}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'destroyTemp'])->name('delete-temp');
            Route::get('/report', [WarehouseController::class, 'reportSOWPM'])->name('report');
            Route::get('/report/getData', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'getDataReport'])->name('report.getData');
            Route::get('/report/pending-approval', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'getPendingApprovalReport'])->name('report.pending-approval');
            Route::get('/report/detail/{id}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'getReportDetail'])->name('report.detail');
            Route::post('/report/update/{id}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'updateReportRow'])->name('report.update');
            Route::delete('/report/delete/{id}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'deleteReportRow'])->name('report.delete');
            Route::delete('/report/detail/delete/{id}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'deleteReportDetail'])->name('report.detail.delete');
            Route::get('/export', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'exportPdfSOWPM'])->name('export');
            Route::post('/send-approval', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'sendApproval'])->name('send-approval');
            Route::post('/update/status-approval', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'updateStatus'])->name('update.status-approval');
            Route::get('/approval/show/{id}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'showApproval'])->name('approval.show');
            Route::get('/getDataApproval', [App\Http\Controllers\Wpm\StockOpname\WpmStockOpnameController::class, 'getDataApproval'])->name('getDataApproval');

            // Stock on Hand SO WPM
            Route::get('/soh', [WarehouseController::class, 'uploadSOHWPM'])->name('soh');
            Route::post('/soh/store', [App\Http\Controllers\Wpm\StockOpname\WpmStockOnHandController::class, 'store'])->name('soh.store');
            Route::delete('/soh/delete/{id}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOnHandController::class, 'destroy'])->name('soh.delete');
            Route::post('/soh/update/{id}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOnHandController::class, 'update'])->name('soh.update');
            Route::post('/soh/import', [App\Http\Controllers\Wpm\StockOpname\WpmStockOnHandController::class, 'importExcel'])->name('soh.import');
            Route::get('/soh/template', [App\Http\Controllers\Wpm\StockOpname\WpmStockOnHandController::class, 'downloadTemplate'])->name('soh.template');
            Route::get('/soh/list', [App\Http\Controllers\Wpm\StockOpname\WpmStockOnHandController::class, 'getList'])->name('soh.list');
            Route::get('/soh/getBarang', [App\Http\Controllers\Wpm\StockOpname\WpmStockOnHandController::class, 'getBarang'])->name('soh.getBarang');
            Route::get('/soh/show/{id}', [App\Http\Controllers\Wpm\StockOpname\WpmStockOnHandController::class, 'show'])->name('soh.show');
            Route::delete('/soh/reset-all', [App\Http\Controllers\Wpm\StockOpname\WpmStockOnHandController::class, 'resetAll'])->name('soh.reset_all');
        });
    });

    // Warehouse Co Product (WCP)
    Route::middleware(['auth'])->prefix('wcp')->name('wcp.')->group(function () {
        // Stock Opname WCP
        Route::prefix('stock_opname')->middleware(['permission:stock-opname-wcp'])->name('stock_opname.')->group(function () {
            Route::post('/start', [WcpStockOpnameController::class, 'startOpname'])->name('startOpname');
            Route::get('/status', [WcpStockOpnameController::class, 'getStatusOpname'])->name('getStatusOpname');
            Route::get('/form', [WarehouseController::class, 'formSOWCP'])->name('form');
            Route::get('/getData', [WcpStockOpnameController::class, 'getData'])->name('getData');
            Route::post('/save-temp', [WcpStockOpnameController::class, 'saveTemp'])->name('save-temp');
            Route::post('/save-new-temp', [WcpStockOpnameController::class, 'saveTempNew'])->name('save-temp-new');
            Route::post('/save-final', [WcpStockOpnameController::class, 'processOpname'])->name('process');
            Route::delete('/reset-temp-row', [WcpStockOpnameController::class, 'resetTempRow'])->name('reset-temp-row');
            Route::get('/getDataTempBatch', [WcpStockOpnameController::class, 'getDataTempBatch'])->name('getTempBatch');
            Route::get('/getDataTempEdit/{sohId}', [WcpStockOpnameController::class, 'getDataTempEdit'])->name('getDataTempEdit');
            Route::post('/update-temp', [WcpStockOpnameController::class, 'updateTempBatch'])->name('update-temp');
            Route::delete('/delete-temp/{id}', [WcpStockOpnameController::class, 'destroyTemp'])->name('delete-temp');
            Route::get('/report', [WarehouseController::class, 'reportSOWCP'])->name('report');
            Route::get('/report/getData', [WcpStockOpnameController::class, 'getDataReport'])->name('report.getData');
            Route::get('/report/pending-approval', [WcpStockOpnameController::class, 'getPendingApprovals'])->name('report.pending-approval');
            Route::get('/report/detail/{id}', [WcpStockOpnameController::class, 'getReportDetail'])->name('report.detail');
            Route::post('/report/update/{id}', [WcpStockOpnameController::class, 'updateReportRow'])->name('report.update');
            Route::delete('/report/delete/{id}', [WcpStockOpnameController::class, 'deleteReportRow'])->name('report.delete');
            Route::delete('/report/detail/delete/{id}', [WcpStockOpnameController::class, 'deleteReportDetail'])->name('report.detail.delete');
            Route::get('/export', [WcpStockOpnameController::class, 'exportPdfSOWCP'])->name('export');
            Route::post('/send-approval', [WcpStockOpnameController::class, 'sendApproval'])->name('send-approval');
            Route::post('/update/status-approval', [WcpStockOpnameController::class, 'updateStatus'])->name('update.status-approval');
            Route::get('/approval/show/{id}', [WcpStockOpnameController::class, 'showApproval'])->name('approval.show');
            Route::get('/getDataApproval', [WcpStockOpnameController::class, 'getDataApproval'])->name('getDataApproval');

            // Stock on Hand SO WCP
            Route::get('/soh', [WarehouseController::class, 'uploadSOHWCP'])->name('soh');
            Route::post('/soh/store', [WcpStockOnHandController::class, 'store'])->name('soh.store');
            Route::delete('/soh/delete/{id}', [WcpStockOnHandController::class, 'destroy'])->name('soh.delete');
            Route::post('/soh/update/{id}', [WcpStockOnHandController::class, 'update'])->name('soh.update');
            Route::post('/soh/import', [WcpStockOnHandController::class, 'importExcel'])->name('soh.import');
            Route::get('/soh/template', [WcpStockOnHandController::class, 'downloadTemplate'])->name('soh.template');
            Route::get('/soh/list', [WcpStockOnHandController::class, 'getList'])->name('soh.list');
            Route::get('/soh/getBarang', [WcpStockOnHandController::class, 'getBarang'])->name('soh.getBarang');
            Route::get('/soh/show/{id}', [WcpStockOnHandController::class, 'show'])->name('soh.show');
            Route::delete('/soh/reset-all', [WcpStockOnHandController::class, 'resetAll'])->name('soh.reset_all');
        });
    });

    // Warehouse Sparepart (WSP) - Stock Opname
    Route::middleware(['auth'])->prefix('wsp')->name('wsp.')->group(function () {
        // Stock Opname WSP
        Route::prefix('stock_opname')->middleware(['permission:stock-opname-wsp'])->name('stock_opname.')->group(function () {
            Route::post('/start', [WspStockOpnameController::class, 'startOpname'])->name('startOpname');
            Route::get('/status', [WspStockOpnameController::class, 'getStatusOpname'])->name('getStatusOpname');
            Route::get('/form', [WarehouseController::class, 'formSOWSP'])->name('form');
            Route::get('/getData', [WspStockOpnameController::class, 'getData'])->name('getData');
            Route::post('/save-temp', [WspStockOpnameController::class, 'saveTemp'])->name('save-temp');
            Route::post('/save-new-temp', [WspStockOpnameController::class, 'saveTempNew'])->name('save-temp-new');
            Route::post('/save-final', [WspStockOpnameController::class, 'processOpname'])->name('process');
            Route::delete('/reset-temp-row', [WspStockOpnameController::class, 'resetTempRow'])->name('reset-temp-row');
            Route::get('/getDataTempBatch', [WspStockOpnameController::class, 'getDataTempBatch'])->name('getTempBatch');
            Route::get('/getDataTempEdit/{sohId}', [WspStockOpnameController::class, 'getDataTempEdit'])->name('getDataTempEdit');
            Route::post('/update-temp', [WspStockOpnameController::class, 'updateTempBatch'])->name('update-temp');
            Route::delete('/delete-temp/{id}', [WspStockOpnameController::class, 'destroyTemp'])->name('delete-temp');
            Route::get('/report', [WarehouseController::class, 'reportSOWSP'])->name('report');
            Route::get('/report/getData', [WspStockOpnameController::class, 'getDataReport'])->name('report.getData');
            Route::get('/report/pending-approval', [WspStockOpnameController::class, 'getPendingApprovals'])->name('report.pending-approval');
            Route::get('/report/detail/{id}', [WspStockOpnameController::class, 'getReportDetail'])->name('report.detail');
            Route::post('/report/update/{id}', [WspStockOpnameController::class, 'updateReportRow'])->name('report.update');
            Route::delete('/report/delete/{id}', [WspStockOpnameController::class, 'deleteReportRow'])->name('report.delete');
            Route::delete('/report/detail/delete/{id}', [WspStockOpnameController::class, 'deleteReportDetail'])->name('report.detail.delete');
            Route::get('/export', [WspStockOpnameController::class, 'exportPdfSOWSP'])->name('export');
            Route::post('/send-approval', [WspStockOpnameController::class, 'sendApproval'])->name('send-approval');
            Route::post('/update/status-approval', [WspStockOpnameController::class, 'updateStatus'])->name('update.status-approval');
            Route::get('/approval/show/{id}', [WspStockOpnameController::class, 'showApproval'])->name('approval.show');
            Route::get('/getDataApproval', [WspStockOpnameController::class, 'getDataApproval'])->name('getDataApproval');

            // Stock on Hand SO WSP
            Route::get('/soh', [WarehouseController::class, 'uploadSOHWSP'])->name('soh');
            Route::post('/soh/store', [WspStockOnHandSOController::class, 'store'])->name('soh.store');
            Route::delete('/soh/delete/{id}', [WspStockOnHandSOController::class, 'destroy'])->name('soh.delete');
            Route::post('/soh/update/{id}', [WspStockOnHandSOController::class, 'update'])->name('soh.update');
            Route::post('/soh/import', [WspStockOnHandSOController::class, 'importExcel'])->name('soh.import');
            Route::get('/soh/template', [WspStockOnHandSOController::class, 'downloadTemplate'])->name('soh.template');
            Route::get('/soh/list', [WspStockOnHandSOController::class, 'getList'])->name('soh.list');
            Route::get('/soh/getBarang', [WspStockOnHandSOController::class, 'getBarang'])->name('soh.getBarang');
            Route::get('/soh/getBarangStockLocation', [WspStockOnHandSOController::class, 'getBarangStockLocation'])->name('soh.getBarangStockLocation');
            Route::get('/soh/getRakList', [WspStockOnHandSOController::class, 'getRakList'])->name('soh.getRakList');
            Route::get('/soh/getAreaList', [WspStockOnHandSOController::class, 'getAreaList'])->name('soh.getAreaList');
            Route::get('/soh/getNamaRakList', [WspStockOnHandSOController::class, 'getNamaRakList'])->name('soh.getNamaRakList');
            Route::get('/soh/getBarangListByLocation', [WspStockOnHandSOController::class, 'getBarangListByLocation'])->name('soh.getBarangListByLocation');
            Route::get('/soh/show/{id}', [WspStockOnHandSOController::class, 'show'])->name('soh.show');
            Route::delete('/soh/reset-all', [WspStockOnHandSOController::class, 'resetAll'])->name('soh.reset_all');
        });
    });

    // Master Data Management
    Route::middleware(['auth'])->group(function () {
        Route::prefix('master')->group(function () {
            // Master WSP
            Route::prefix('wsp')->middleware(['permission:master-wsp'])->group(function () {
                // TKBM
                Route::get('/fee', [WarehouseController::class, 'feeTkbm'])->name('wsp.master.fee');
                Route::get('/harga-produk', [WarehouseController::class, 'feeTkbm'])->name('tkbm.master.harga-produk');
                Route::post('/fee/simpan', [TkbmController::class, 'simpanFeeTkbm'])->name('tkbm.fee.simpan');
                Route::get('/fee/history', [TkbmController::class, 'historyFeeTkbm'])->name('tkbm.fee.history');
                Route::post('/harga-produk/simpan', [TkbmController::class, 'simpanHargaProduk'])->name('tkbm.harga-produk.simpan');
                Route::get('/harga-produk/history', [TkbmController::class, 'historyProductPrice'])->name('tkbm.harga-produk.history');
                Route::get('/sync-totals', [TkbmController::class, 'syncTotalsTkbm']);

                // Barang
                Route::get('/barang', [WarehouseController::class, 'barangIndex'])->name('wsp.master.barang');
                Route::post('/store/barang', [WspBarangController::class, 'store'])->name('wsp.store.barang');
                Route::put('/update/barang/{id}', [WspBarangController::class, 'update'])->name('wsp.update.barang');
                Route::post('/barang/import', [WspBarangController::class, 'import'])->name('wsp.barang.import');
                Route::get('/barang/download-template', [WspBarangController::class, 'downloadTemplate'])->name('wsp.barang.download.template');
                Route::get('/barang/export', [WspBarangController::class, 'export'])->name('wsp.barang.export');

                // Rak
                Route::get('/rak', [WarehouseController::class, 'rakIndex'])->name('wsp.master.rak');
                Route::put('/update/rak/{id}', [WspRakController::class, 'update'])->name('wsp.rak.update');
                Route::post('/store/rak', [WspRakController::class, 'store'])->name('wsp.store.rak');
                Route::delete('/delete/rak/{id}', [WspRakController::class, 'destroy'])->name('wsp.delete.rak');
                Route::post('/upload/rak', [WspRakController::class, 'upload'])->name('wsp.rak.upload');
                Route::get('/download-template/rak', [WspRakController::class, 'downloadTemplate'])->name('wsp.rak.download-template');
            });

            // Master WFG
            Route::prefix('wfg')->middleware(['permission:master-wfg'])->group(function () {
                // Route::prefix('master')->group(function () {
                Route::prefix('barang')->group(function () {
                    Route::get('/index', [BarangWfgController::class, 'index'])->name('master.wfg.barang.index');
                    Route::get('/new', [BarangWfgController::class, 'getNewItems'])->name('master.wfg.barang.new');
                    Route::post('/new/approve/{id}', [BarangWfgController::class, 'approve'])->name('master.wfg.barang.new.approve');
                    Route::post('/new/reject/{id}', [BarangWfgController::class, 'reject'])->name('master.wfg.barang.new.reject');
                    Route::post('/store', [BarangWfgController::class, 'store'])->name('master.wfg.barang.store');
                    Route::get('/data', [BarangWfgController::class, 'data'])->name('master.wfg.barang.data');
                    Route::put('/update/{id}', [BarangWfgController::class, 'update'])->name('master.wfg.barang.update');
                    Route::delete('/delete/{id}', [BarangWfgController::class, 'destroy'])->name('master.wfg.barang.delete');
                    Route::post('/restore/{id}', [BarangWfgController::class, 'restore'])->name('master.wfg.barang.restore');
                    Route::delete('/force-delete/{id}', [BarangWfgController::class, 'forceDelete'])->name('master.wfg.barang.forceDelete');
                    Route::post('/import', [BarangWfgController::class, 'import'])->name('master.wfg.barang.import');
                    Route::get('/template', [BarangWfgController::class, 'downloadTemplate'])->name('master.wfg.barang.template');
                });

                Route::prefix('destinasi')->group(function () {
                    Route::get('/index', [MasterDestinasiController::class, 'index'])->name('master.wfg.destinasi.index');
                    Route::get('/data', [MasterDestinasiController::class, 'data'])->name('master.wfg.destinasi.data');
                    Route::post('/store', [MasterDestinasiController::class, 'store'])->name('master.wfg.destinasi.store');
                    Route::put('/update/{id}', [MasterDestinasiController::class, 'update'])->name('master.wfg.destinasi.update');
                    Route::patch('/toggle-status/{id}', [MasterDestinasiController::class, 'toggleStatus'])->name('master.wfg.destinasi.toggleStatus');
                    Route::delete('/delete/{id}', [MasterDestinasiController::class, 'destroy'])->name('master.wfg.destinasi.delete');
                });
            });

            // Master WRM
            Route::prefix('wrm')->middleware(['permission:master-wrm'])->group(function () {
                // Route::prefix('master')->group(function () {
                Route::prefix('barang')->group(function () {
                    Route::get('/index', [MasterBarangController::class, 'index'])->name('master.wrm.barang.index');
                    Route::get('/get-data', [MasterBarangController::class, 'getData'])->name('wrm.master.barang.get-data');
                    Route::post('/store', [MasterBarangController::class, 'store'])->name('wrm.master.barang.store');
                    Route::put('/update/{id}', [MasterBarangController::class, 'update'])->name('wrm.master.barang.update');
                    Route::delete('/delete/{id}', [MasterBarangController::class, 'destroy'])->name('wrm.master.barang.delete');
                    Route::post('/restore/{id}', [MasterBarangController::class, 'restore'])->name('wrm.master.barang.restore');
                    Route::delete('/force-delete/{id}', [MasterBarangController::class, 'forceDelete'])->name('wrm.master.barang.forceDelete');
                    Route::get('/template', [MasterBarangController::class, 'downloadTemplate'])->name('wrm.master.barang.template');
                    Route::post('/upload', [MasterBarangController::class, 'upload'])->name('wrm.master.barang.upload');
                });

                Route::prefix('ikat-terpal')->group(function () {
                    Route::get('/index', [MasterIkatTerpalController::class, 'index'])->name('master.wrm.ikat-terpal.index');
                    Route::post('/store/fee', [MasterIkatTerpalController::class, 'storeFee']);
                    Route::post('/store/produk', [MasterIkatTerpalController::class, 'storeProduk']);
                    Route::get('/fee-aktif', [MasterIkatTerpalController::class, 'getFeeAktif']);
                    Route::get('/produk-aktif', [MasterIkatTerpalController::class, 'getProdukAktif']);
                });

                Route::prefix('location')->group(function () {
                    Route::get('/index', [MasterLocationController::class, 'index'])->name('master.wrm.location.index');
                    Route::get('/get-data', [MasterLocationController::class, 'getData'])->name('wrm.master.location.get-data');
                    Route::post('/store', [MasterLocationController::class, 'store'])->name('wrm.master.location.store');
                    Route::put('/update/{id}', [MasterLocationController::class, 'update'])->name('wrm.master.location.update');
                    Route::delete('/delete/{id}', [MasterLocationController::class, 'destroy'])->name('wrm.master.location.delete');
                    Route::post('/upload', [MasterLocationController::class, 'upload'])->name('wrm.master.location.upload');
                });

                Route::prefix('bin')->group(function () {
                    Route::get('/index', [MasterBinController::class, 'index'])->name('master.wrm.bin.index');
                    Route::get('/get-data', [MasterBinController::class, 'getData'])->name('wrm.master.bin.get-data');
                    Route::post('/store', [MasterBinController::class, 'store'])->name('wrm.master.bin.store');
                    Route::delete('/delete/{id}', [MasterBinController::class, 'destroy'])->name('wrm.master.bin.delete');
                    Route::delete('/delete-by-loc/{locId}', [MasterBinController::class, 'destroyByLoc'])->name('wrm.master.bin.delete-by-loc');
                });

                Route::prefix('pallet')->group(function () {
                    Route::get('/index', [MasterPalletController::class, 'index'])->name('master.wrm.pallet.index');
                    Route::post('/store', [MasterPalletController::class, 'store'])->name('wrm.master.pallet.store');
                    Route::put('/update/{id}', [MasterPalletController::class, 'update'])->name('wrm.master.pallet.update');
                    Route::delete('/destroy/{id}', [MasterPalletController::class, 'destroy'])->name('wrm.master.pallet.destroy');
                    Route::get('/get-data', [MasterPalletController::class, 'getData'])->name('wrm.master.pallet.get-data');
                });

                Route::prefix('supplier')->group(function () {
                    Route::get('/index', [MasterSupplierController::class, 'index'])->name('master.wrm.supplier.index');
                    Route::get('/get-data', [MasterSupplierController::class, 'getData'])->name('wrm.master.supplier.get-data');
                    Route::get('/get-all', [MasterSupplierController::class, 'getAll'])->name('wrm.master.supplier.get-all');
                    Route::post('/store', [MasterSupplierController::class, 'store'])->name('wrm.master.supplier.store');
                    Route::put('/update/{id}', [MasterSupplierController::class, 'update'])->name('wrm.master.supplier.update');
                    Route::delete('/delete/{id}', [MasterSupplierController::class, 'destroy'])->name('wrm.master.supplier.delete');
                });
                // });
            });

            // Master WPM
            Route::prefix('wpm')->middleware(['permission:master-wpm'])->group(function () {
                Route::prefix('barang')->group(function () {
                    Route::get('/index', [App\Http\Controllers\Wpm\MasterBarangController::class, 'index'])->name('master.wpm.barang.index');
                    Route::get('/get-data', [App\Http\Controllers\Wpm\MasterBarangController::class, 'getData'])->name('master.wpm.barang.get-data');
                    Route::post('/store', [App\Http\Controllers\Wpm\MasterBarangController::class, 'store'])->name('master.wpm.barang.store');
                    Route::put('/update/{id}', [App\Http\Controllers\Wpm\MasterBarangController::class, 'update'])->name('master.wpm.barang.update');
                    Route::delete('/delete/{id}', [App\Http\Controllers\Wpm\MasterBarangController::class, 'destroy'])->name('master.wpm.barang.delete');
                    Route::post('/restore/{id}', [App\Http\Controllers\Wpm\MasterBarangController::class, 'restore'])->name('master.wpm.barang.restore');
                    Route::delete('/force-delete/{id}', [App\Http\Controllers\Wpm\MasterBarangController::class, 'forceDelete'])->name('master.wpm.barang.forceDelete');
                    Route::get('/template', [App\Http\Controllers\Wpm\MasterBarangController::class, 'downloadTemplate'])->name('master.wpm.barang.template');
                    Route::post('/upload', [App\Http\Controllers\Wpm\MasterBarangController::class, 'upload'])->name('master.wpm.barang.upload');
                });
            });

            // Master WCP
            Route::prefix('wcp')->middleware(['permission:master-wcp'])->group(function () {
                Route::prefix('barang')->group(function () {
                    Route::get('/index', [App\Http\Controllers\Wcp\MasterBarangController::class, 'index'])->name('master.wcp.barang.index');
                    Route::get('/get-data', [App\Http\Controllers\Wcp\MasterBarangController::class, 'getData'])->name('master.wcp.barang.get-data');
                    Route::post('/store', [App\Http\Controllers\Wcp\MasterBarangController::class, 'store'])->name('master.wcp.barang.store');
                    Route::put('/update/{id}', [App\Http\Controllers\Wcp\MasterBarangController::class, 'update'])->name('master.wcp.barang.update');
                    Route::delete('/delete/{id}', [App\Http\Controllers\Wcp\MasterBarangController::class, 'destroy'])->name('master.wcp.barang.delete');
                    Route::post('/restore/{id}', [App\Http\Controllers\Wcp\MasterBarangController::class, 'restore'])->name('master.wcp.barang.restore');
                    Route::delete('/force-delete/{id}', [App\Http\Controllers\Wcp\MasterBarangController::class, 'forceDelete'])->name('master.wcp.barang.forceDelete');
                    Route::get('/template', [App\Http\Controllers\Wcp\MasterBarangController::class, 'downloadTemplate'])->name('master.wcp.barang.template');
                    Route::post('/upload', [App\Http\Controllers\Wcp\MasterBarangController::class, 'upload'])->name('master.wcp.barang.upload');
                });
            });

            // Master User
            Route::prefix('user')->middleware(['permission:manage-users'])->group(function () {
                Route::get('/index', [UserController::class, 'index'])->name('user.index');
                Route::get('/get-data', [UserController::class, 'create'])->name('user.getData');
                Route::post('/store', [UserController::class, 'store'])->name('user.store');
                Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');
                Route::get('/statistik', [UserController::class, 'statisktik'])->name('user.statistik');
                Route::get('/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
                Route::put('/update/{id}', [UserController::class, 'update'])->name('user.update');
                Route::patch('/toggle-status/{id}', [UserController::class, 'toggleStatus'])->name('user.toggle-status');
            });
        });
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/notif', [NotificationController::class, 'index'])->name('notifications');
        // Route::post('/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/delete/{id}', [NotificationController::class, 'destroy'])->name('notifications.delete');
        Route::delete('/delete-all', [NotificationController::class, 'destroyAll'])->name('notifications.delete-all');
    });

    Route::get('/app/{any?}', function () {
        return view('app');
    })->where('any', '.*');

    Route::prefix('admin')->name('admin.')->middleware(['auth', 'permission:super-admin,manage-permissions'])->group(function () {

        // Permission CRUD
        Route::get('/permissions/data', [PermissionController::class, 'data'])->name('permissions.data');
        Route::get('/permissions/{id}/edit-ajax', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::resource('permissions', PermissionController::class);

        // Assign permission ke user
        Route::get('users/permissions', [UserPermissionController::class, 'index'])->name('permissions.users');
        Route::get('users/permissions/data', [UserPermissionController::class, 'getUsersData'])->name('permissions.users.data');
        Route::get('users/permissions/search', [UserPermissionController::class, 'searchUsers'])->name('permissions.users.search');
        Route::get('users/permissions/{id}', [UserPermissionController::class, 'getUserPermissions'])->name('permissions.users.get');
        Route::put('users/permissions/{user}', [UserPermissionController::class, 'update'])->name('permissions.users.update');

        // Permission & Role Management

        Route::get('/roles', [RoleController::class, 'index'])->name('role.index');
        Route::post('/roles/store', [RoleController::class, 'store'])->name('role.store');
        Route::post('/roles/update/{id}', [RoleController::class, 'update'])->name('role.update');
        Route::delete('/roles/destroy/{id}', [RoleController::class, 'destroy'])->name('role.destroy');
        Route::get('/roles/permissions/{id}', [RoleController::class, 'getRolePermissions'])->name('role.permissions');
        Route::post('/roles/assign-permissions/{id}', [RoleController::class, 'assignPermissions'])->name('role.assign_permissions');

        Route::get('/users-roles', [RoleController::class, 'userRolesIndex'])->name('user.roles_index');
        Route::get('/users-roles/data', [RoleController::class, 'getUsersData'])->name('user.data');
        Route::get('/user-roles/{userId}', [RoleController::class, 'getUserRoles'])->name('user.roles');
        Route::post('/user-roles/assign/{userId}', [RoleController::class, 'assignUserRoles'])->name('user.assign_roles');
    });

    // Vehicle Monitoring & Tracking
    Route::prefix('vehicle-monitoring')->name('vehicle.monitoring.')->middleware(['auth', 'permission:vehicle-monitoring-menu'])->group(function () {
        // Laporan History Page
        Route::get('/history', [VehicleTrackingController::class, 'historyIndex'])->name('history');

        // Timbangan (Scales)
        Route::middleware(['permission:vehicle-monitoring-timbangan'])->group(function () {
            Route::get('/timbangan', [VehicleTrackingController::class, 'timbanganIndex'])->name('timbangan');
            Route::get('/timbangan/data', [VehicleTrackingController::class, 'timbanganData'])->name('timbangan.data');
            Route::get('/timbangan/show/{id}', [VehicleTrackingController::class, 'timbanganShow'])->name('timbangan.show');
            Route::post('/timbangan/check-in', [VehicleTrackingController::class, 'timbanganCheckIn'])->name('timbangan.check_in');
            Route::put('/timbangan/update/{id}', [VehicleTrackingController::class, 'timbanganUpdate'])->name('timbangan.update');
            Route::delete('/timbangan/delete/{id}', [VehicleTrackingController::class, 'timbanganDestroy'])->name('timbangan.delete');
            Route::post('/timbangan/check-out/{id}', [VehicleTrackingController::class, 'timbanganCheckOut'])->name('timbangan.check_out');
            Route::get('/timbangan/autocomplete-vehicle', [VehicleTrackingController::class, 'autocompleteVehicle'])->name('timbangan.autocomplete_vehicle');
            Route::get('/timbangan/supplier-data', [VehicleTrackingController::class, 'getSupplierData'])->name('timbangan.supplier_data');
        });

        Route::post('/update-queue/{id}', [VehicleTrackingController::class, 'updateQueueNumber'])->name('update_queue');

        // QC Area
        Route::middleware(['permission:vehicle-monitoring-qc'])->group(function () {
            Route::get('/qc', [VehicleTrackingController::class, 'qcIndex'])->name('qc');
            Route::get('/qc/data', [VehicleTrackingController::class, 'qcData'])->name('qc.data');
            Route::post('/qc/update-qc/{id}', [VehicleTrackingController::class, 'qcUpdateQC'])->name('qc.update_qc');
            Route::post('/qc/update-queue/{id}', [VehicleTrackingController::class, 'qcUpdateQueueNumber'])->name('qc.update_queue');
        });

        // WPM (Unloading Area)
        Route::middleware(['permission:vehicle-monitoring-wpm'])->group(function () {
            Route::get('/wpm', [VehicleTrackingController::class, 'wpmIndex'])->name('wpm');
            Route::get('/wpm/data', [VehicleTrackingController::class, 'wpmData'])->name('wpm.data');
            Route::post('/wpm/complete/{id}', [VehicleTrackingController::class, 'wpmComplete'])->name('wpm.complete');
        });

        // WRM (Unloading Area)
        Route::middleware(['permission:vehicle-monitoring-wrm'])->group(function () {
            Route::get('/wrm', [VehicleTrackingController::class, 'wrmIndex'])->name('wrm');
            Route::get('/wrm/data', [VehicleTrackingController::class, 'wrmData'])->name('wrm.data');
            Route::post('/wrm/update-unloading/{id}', [VehicleTrackingController::class, 'wrmUpdateUnloading'])->name('wrm.update_unloading');
        });

        // WFG (Bongkar Muat Finished Goods Area)
        Route::middleware(['permission:vehicle-monitoring-wfg'])->group(function () {
            Route::get('/wfg', [VehicleTrackingController::class, 'wfgIndex'])->name('wfg');
            Route::get('/wfg/data', [VehicleTrackingController::class, 'wfgData'])->name('wfg.data');
            Route::post('/wfg/update-loading/{id}', [VehicleTrackingController::class, 'wfgUpdateLoading'])->name('wfg.update_loading');
        });

        // SMU Area
        Route::middleware(['permission:vehicle-monitoring-smu'])->group(function () {
            Route::get('/smu', [VehicleTrackingController::class, 'smuIndex'])->name('smu');
            Route::get('/smu/data', [VehicleTrackingController::class, 'smuData'])->name('smu.data');
            Route::post('/smu/complete/{id}', [VehicleTrackingController::class, 'smuComplete'])->name('smu.complete');
        });

        // Master Items CRUD (SKUs)
        Route::middleware(['permission:vehicle-monitoring-master'])->group(function () {
            Route::get('/master/items', [VehicleTrackingController::class, 'masterItemsIndex'])->name('master.items');
            Route::get('/master/items/data', [VehicleTrackingController::class, 'masterItemsData'])->name('master.items.data');
            Route::post('/master/items/store', [VehicleTrackingController::class, 'masterItemsStore'])->name('master.items.store');
            Route::put('/master/items/update/{id}', [VehicleTrackingController::class, 'masterItemsUpdate'])->name('master.items.update');
            Route::delete('/master/items/delete/{id}', [VehicleTrackingController::class, 'masterItemsDestroy'])->name('master.items.delete');

            // Sloc CRUD
            Route::post('/master/sloc/store', [VehicleTrackingController::class, 'masterSlocStore'])->name('master.sloc.store');
            Route::put('/master/sloc/update/{id}', [VehicleTrackingController::class, 'masterSlocUpdate'])->name('master.sloc.update');
            Route::delete('/master/sloc/delete/{id}', [VehicleTrackingController::class, 'masterSlocDestroy'])->name('master.sloc.delete');

            // Vendor CRUD
            Route::post('/master/vendor/store', [VehicleTrackingController::class, 'masterVendorStore'])->name('master.vendor.store');
            Route::put('/master/vendor/update/{id}', [VehicleTrackingController::class, 'masterVendorUpdate'])->name('master.vendor.update');
            Route::delete('/master/vendor/delete/{id}', [VehicleTrackingController::class, 'masterVendorDestroy'])->name('master.vendor.delete');
        });

        // General Report/History Data
        Route::get('/history/data', [VehicleTrackingController::class, 'historyData'])->name('history.data');
    });
});
