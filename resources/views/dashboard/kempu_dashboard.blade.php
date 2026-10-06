@extends('layouts.app')

@section('title', ' | Dashboard Monitoring Kempu')

@section('sidebar-size', 'sm')

@section('styles')
    <style>
        :root {
            --kempu-primary: #4361ee;
            --kempu-success: #06d6a0;
            --kempu-warning: #f59e0b;
            --kempu-danger: #ef4444;
            --kempu-info: #0ea5e9;
            --kempu-purple: #8b5cf6;
        }

        .dashboard-card {
            border-radius: 12px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        [data-layout-mode="dark"] .dashboard-card {
            border-color: rgba(255, 255, 255, 0.08);
            background: #212529;
        }

        .kpi-card {
            border-radius: 12px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-layout-mode="dark"] .kpi-card {
            border-color: rgba(255, 255, 255, 0.07);
            background: #212529;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08);
        }

        .kpi-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        /* Flow / Pipeline Stepper */
        .pipeline-card {
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.85) 0%, rgba(241, 245, 249, 0.95) 100%);
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 16px;
        }

        [data-layout-mode="dark"] .pipeline-card {
            background: linear-gradient(135deg, rgba(30, 34, 40, 0.9) 0%, rgba(22, 25, 30, 0.95) 100%);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .flow-node {
            border-radius: 10px;
            padding: 12px 14px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            text-align: center;
            min-height: 90px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        [data-layout-mode="dark"] .flow-node {
            background: #282d33;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .flow-node:hover,
        .flow-node.active-filter {
            transform: translateY(-2px);
            border-color: #4361ee !important;
            box-shadow: 0 6px 16px -2px rgba(67, 97, 238, 0.25);
        }

        .flow-node.active-filter::after {
            content: '✓';
            position: absolute;
            top: 4px;
            right: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #4361ee;
        }

        .flow-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 18px;
        }

        /* Live status badge with pulse */
        .live-dot {
            width: 9px;
            height: 9px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 1.8s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Feed list */
        .feed-container {
            max-height: 380px;
            overflow-y: auto;
        }

        .feed-item {
            padding: 10px 14px;
            border-radius: 8px;
            border-bottom: 1px dashed rgba(226, 232, 240, 0.8);
            transition: background 0.15s ease;
        }

        [data-layout-mode="dark"] .feed-item {
            border-bottom-color: rgba(255, 255, 255, 0.08);
        }

        .feed-item:hover {
            background: rgba(67, 97, 238, 0.04);
        }

        /* Progress Reused */
        .reused-progress-track {
            height: 6px;
            background-color: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }

        [data-layout-mode="dark"] .reused-progress-track {
            background-color: #334155;
        }

        .reused-progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        /* Timeline in modal */
        .timeline-box {
            position: relative;
            padding-left: 24px;
        }

        .timeline-box::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 4px;
            bottom: 4px;
            width: 2px;
            background: #cbd5e1;
        }

        [data-layout-mode="dark"] .timeline-box::before {
            background: #475569;
        }

        .timeline-step {
            position: relative;
            margin-bottom: 18px;
        }

        .timeline-dot {
            position: absolute;
            left: -20px;
            top: 3px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
            background: #4361ee;
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.3);
        }

        /* Filter Tab Styles */
        .nav-pills-custom .nav-link {
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            padding: 6px 14px;
            transition: all 0.2s ease;
        }

        .nav-pills-custom .nav-link.active {
            background: #4361ee;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Header Section -->
            <div class="row align-items-center mb-3 g-2">
                <div class="col-md-6 col-12">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-3 fs-20">
                                <i class="mdi mdi-cube-scan"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">Monitoring & Traceability Kempu</h4>
                            <div class="d-flex align-items-center gap-2 text-muted fs-12 mt-1">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span class="live-dot"></span> Live Supply Chain Tracking & Audit Trail
                                </span>
                                <span>•</span>
                                <span id="lastUpdatedText">Memperbarui...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                        <!-- Quick Traceability Search Box -->
                        <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="ri-qr-scan-2-line text-primary"></i>
                            </span>
                            <input type="text" id="quickTraceInput"
                                class="form-control form-control-sm border-start-0 border-end-0"
                                placeholder="Scan / ID Kempu..." style="width: 155px;" autocomplete="off">
                            <button class="btn btn-primary btn-sm" type="button" id="btnQuickTrace">
                                <i class="ri-route-line align-middle me-1"></i> Cek Trace
                            </button>
                        </div>

                        <!-- Auto Refresh Control -->
                        <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="ri-timer-line text-muted"></i>
                            </span>
                            <select id="autoRefreshInterval" class="form-select form-select-sm border-start-0"
                                style="min-width: 105px;">
                                <option value="0">Auto-refresh: Off</option>
                                <option value="15000">15 Detik</option>
                                <option value="30000" selected>30 Detik</option>
                                <option value="60000">60 Detik</option>
                            </select>
                        </div>

                        <!-- Manual Refresh -->
                        <button type="button" id="btnRefreshAll" class="btn btn-sm btn-outline-primary waves-effect"
                            title="Refresh Data">
                            <i class="ri-refresh-line align-middle"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Top KPI Cards -->
            <div class="row g-3 mb-3">
                <!-- KPI 1: Total Aset & Aktif -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card kpi-card shadow-sm h-100 mb-0 border-start border-primary border-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted fs-11 mb-1">Total Kempu Aktif</p>
                                    <h3 class="fw-bold mb-1" id="kpiTotalActive">0</h3>
                                    <p class="text-muted fs-12 mb-0">
                                        Dari total <span class="fw-semibold text-dark" id="kpiTotalAll">0</span> aset
                                        (Scrap: <span class="text-danger fw-semibold" id="kpiTotalScrap">0</span>)
                                    </p>
                                </div>
                                <div class="kpi-icon-box bg-primary-subtle text-primary">
                                    <i class="mdi mdi-cube-outline"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI 2: Scan Transaksi Hari Ini -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card kpi-card shadow-sm h-100 mb-0 border-start border-success border-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted fs-11 mb-1">Scan Operasional Hari Ini
                                    </p>
                                    <h3 class="fw-bold mb-1 text-success" id="kpiTodayScans">0</h3>
                                    <p class="text-muted fs-12 mb-0">
                                        <i class="ri-check-double-line text-success me-1"></i>Aktivitas scan lintas area
                                        stasiun
                                    </p>
                                </div>
                                <div class="kpi-icon-box bg-success-subtle text-success">
                                    <i class="mdi mdi-barcode-scan"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI 3: Reused Health (0-21x) -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card kpi-card shadow-sm h-100 mb-0 border-start border-warning border-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted fs-11 mb-1">Status Siklus Reused (Maks.
                                        21x)</p>
                                    <div class="d-flex align-items-baseline gap-2 mb-1">
                                        <h3 class="fw-bold mb-0 text-warning" id="kpiReusedWarning">0</h3>
                                        <span class="fs-12 text-muted">Warning (≥18x)</span>
                                        <span class="badge bg-danger-subtle text-danger ms-auto fs-11"
                                            id="kpiReusedMaxBadge">Max (≥21x): 0</span>
                                    </div>
                                    <div class="reused-progress-track">
                                        <div class="reused-progress-bar bg-warning" id="kpiReusedProgressBar"
                                            style="width: 0%;"></div>
                                    </div>
                                    <span class="text-muted fs-11 d-block mt-1">Rata-rata siklus aset: <span
                                            class="fw-semibold text-dark" id="kpiAvgReused">0</span>x</span>
                                </div>
                                <div class="kpi-icon-box bg-warning-subtle text-warning">
                                    <i class="mdi mdi-refresh-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI 4: Perputaran Inflow & Outflow Hari Ini -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card kpi-card shadow-sm h-100 mb-0 border-start border-info border-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted fs-11 mb-1">Perputaran Hari Ini</p>
                                    <div class="d-flex align-items-center gap-3 mb-1">
                                        <div>
                                            <span class="fs-11 text-muted d-block">Masuk (Inflow)</span>
                                            <span class="fw-bold fs-16 text-info" id="kpiInflowToday">0</span>
                                        </div>
                                        <div class="vr"></div>
                                        <div>
                                            <span class="fs-11 text-muted d-block">Keluar (Outflow)</span>
                                            <span class="fw-bold fs-16 text-primary" id="kpiOutflowToday">0</span>
                                        </div>
                                    </div>
                                    <p class="text-muted fs-11 mb-0">WPM Receiving vs Kirim ke PAS</p>
                                </div>
                                <div class="kpi-icon-box bg-info-subtle text-info">
                                    <i class="mdi mdi-swap-horizontal-bold"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supply Chain Visual Pipeline Flow -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="pipeline-card shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fs-13 fw-bold text-uppercase text-muted">
                                <i class="ri-flow-chart text-primary me-1"></i> Live Stasiun & Rantai Pasok Kempu (Klik
                                stasiun untuk filter tabel)
                            </span>
                            <button type="button" id="btnResetStationFilter"
                                class="btn btn-xs btn-outline-secondary py-0 px-2 fs-11 d-none">
                                <i class="ri-close-line"></i> Reset Filter Stasiun
                            </button>
                        </div>

                        <div class="row g-2 align-items-center text-center">
                            <!-- 1. WPM -->
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="flow-node" data-location="WPM">
                                    <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                        <span class="badge bg-primary-subtle text-primary fs-10">Step 1</span>
                                        <i class="ri-home-line text-muted"></i>
                                    </div>
                                    <h6 class="fs-13 fw-bold mb-1">Gudang WPM</h6>
                                    <div>
                                        <span class="fs-16 fw-bold text-primary" id="flowCountWpm">0</span>
                                        <span class="fs-11 text-muted"> kempu</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. QC PM -->
                            <div class="col-lg-1 col-md-2 col-6">
                                <div class="flow-node" data-location="QC_PM">
                                    <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                        <span class="badge bg-warning-subtle text-warning fs-10">QC</span>
                                        <i class="ri-microscope-line text-muted"></i>
                                    </div>
                                    <h6 class="fs-12 fw-bold mb-1">QC PM</h6>
                                    <div>
                                        <span class="fs-16 fw-bold text-warning" id="flowCountQcPm">0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Produksi -->
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="flow-node" data-location="PRODUKSI">
                                    <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                        <span class="badge bg-info-subtle text-info fs-10">Step 2</span>
                                        <i class="ri-settings-4-line text-muted"></i>
                                    </div>
                                    <h6 class="fs-13 fw-bold mb-1">Produksi</h6>
                                    <div>
                                        <span class="fs-16 fw-bold text-info" id="flowCountProduksi">0</span>
                                        <span class="fs-11 text-muted"> (Cuci/Fill)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. QC Proses -->
                            <div class="col-lg-1 col-md-2 col-6">
                                <div class="flow-node" data-location="QC_PROSES">
                                    <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                        <span class="badge bg-warning-subtle text-warning fs-10">QC</span>
                                        <i class="ri-microscope-line text-muted"></i>
                                    </div>
                                    <h6 class="fs-12 fw-bold mb-1">QC Proses</h6>
                                    <div>
                                        <span class="fs-16 fw-bold text-warning" id="flowCountQcProses">0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. WFG -->
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="flow-node" data-location="WFG">
                                    <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                        <span class="badge bg-success-subtle text-success fs-10">Step 3</span>
                                        <i class="ri-archive-line text-muted"></i>
                                    </div>
                                    <h6 class="fs-13 fw-bold mb-1">Gudang WFG</h6>
                                    <div>
                                        <span class="fs-16 fw-bold text-success" id="flowCountWfg">0</span>
                                        <span class="fs-11 text-muted"> kempu</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 6. PT PAS -->
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="flow-node" data-location="WAREHOUSE_PAS">
                                    <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                        <span class="badge bg-purple-subtle text-purple fs-10"
                                            style="color:#7c3aed;background:#ede9fe;">Distribusi</span>
                                        <i class="ri-store-2-line text-muted"></i>
                                    </div>
                                    <h6 class="fs-13 fw-bold mb-1">Whs PT PAS</h6>
                                    <div>
                                        <span class="fs-16 fw-bold" style="color:#7c3aed;" id="flowCountPas">0</span>
                                        <span class="fs-11 text-muted"> kempu</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 7. Auxiliary: Workshop ENG & Scrap -->
                            <div class="col-lg-2 col-md-4 col-12">
                                <div class="d-flex gap-2">
                                    <div class="flow-node flex-grow-1" data-location="ENGINEERING_WORKSHOP">
                                        <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                            <span class="badge bg-secondary-subtle text-secondary fs-9">Repair</span>
                                            <i class="ri-tools-line text-muted"></i>
                                        </div>
                                        <h6 class="fs-11 fw-bold mb-1 text-truncate">Workshop ENG</h6>
                                        <div>
                                            <span class="fs-14 fw-bold text-secondary" id="flowCountEng">0</span>
                                        </div>
                                    </div>
                                    <div class="flow-node flex-grow-1" data-location="SCRAP">
                                        <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                            <span class="badge bg-danger-subtle text-danger fs-9">Rusak</span>
                                            <i class="ri-delete-bin-line text-danger"></i>
                                        </div>
                                        <h6 class="fs-11 fw-bold mb-1 text-truncate text-danger">Scrap</h6>
                                        <div>
                                            <span class="fs-14 fw-bold text-danger" id="flowCountScrap">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-3 mb-3">
                <!-- Chart 1: Tren Scan Harian -->
                <div class="col-xxl-8 col-xl-7">
                    <div class="card dashboard-card shadow-sm h-100 mb-0">
                        <div
                            class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-2">
                            <h6 class="card-title mb-0 fs-13 fw-bold">
                                <i class="ri-line-chart-line text-primary me-1"></i> Tren Aktivitas Scan Operasional Kempu
                            </h6>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-trend-range active"
                                    data-days="7">7 Hari</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-trend-range"
                                    data-days="14">14 Hari</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-trend-range"
                                    data-days="30">30 Hari</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="chartScanTrend" style="min-height: 290px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Chart 2: Distribusi Lokasi Real-Time -->
                <div class="col-xxl-4 col-xl-5">
                    <div class="card dashboard-card shadow-sm h-100 mb-0">
                        <div
                            class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-2">
                            <h6 class="card-title mb-0 fs-13 fw-bold">
                                <i class="ri-pie-chart-2-line text-success me-1"></i> Proporsi Lokasi Kempu
                            </h6>
                            <span class="badge bg-light text-muted border fs-11">Live Aset</span>
                        </div>
                        <div class="card-body">
                            <div id="chartLocationDistribution" style="min-height: 290px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2: Reused Breakdown & Quality Results -->
            <div class="row g-3 mb-3">
                <!-- Chart 3: Reused Distribution -->
                <div class="col-xxl-6 col-lg-6">
                    <div class="card dashboard-card shadow-sm h-100 mb-0">
                        <div
                            class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-2">
                            <h6 class="card-title mb-0 fs-13 fw-bold">
                                <i class="ri-bar-chart-grouped-line text-info me-1"></i> Distribusi Umur Siklus Reused
                                (Maks. 21x)
                            </h6>
                            <span class="badge bg-light text-muted border fs-11">Kempu Aktif</span>
                        </div>
                        <div class="card-body">
                            <div id="chartReusedDistribution" style="min-height: 240px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Live Feed Scans Stream -->
                <div class="col-xxl-6 col-lg-6">
                    <div class="card dashboard-card shadow-sm h-100 mb-0">
                        <div
                            class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-2">
                            <h6 class="card-title mb-0 fs-13 fw-bold">
                                <i class="ri-broadcast-line text-danger me-1"></i> Live Activity Feed (15 Transaksi Scan
                                Terakhir)
                            </h6>
                            <span class="badge bg-success-subtle text-success fs-11"><span class="live-dot me-1"></span>
                                Live Feed</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="feed-container" id="recentScansFeed">
                                <div class="text-center py-4 text-muted">
                                    <i class="ri-loader-4-line ri-spin fs-24"></i>
                                    <p class="fs-12 mb-0 mt-1">Memuat aktivitas terbaru...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Datatable Section -->
            <div class="card dashboard-card shadow-sm mb-3">
                <div class="card-header bg-transparent border-bottom py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="card-title mb-0 fs-14 fw-bold">
                            <i class="ri-table-line text-primary me-1"></i> Monitoring Detail Inventaris & Riwayat Kempu
                        </h5>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <!-- Export Button -->
                            <button type="button" id="btnExportData" class="btn btn-sm btn-outline-success">
                                <i class="ri-file-excel-2-line align-middle me-1"></i> Ekspor Excel (.xlsx)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Controls -->
                <div class="card-body border-bottom bg-light bg-opacity-40 py-2">
                    <div class="row g-2 align-items-center">
                        <!-- Search Box -->
                        <div class="col-lg-3 col-md-4 col-12">
                            <div class="position-relative">
                                <input type="text" id="filterSearch" class="form-control form-control-sm ps-4"
                                    placeholder="Cari ID Kempu, RFID, No SPB...">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-2 text-muted fs-13"></i>
                            </div>
                        </div>

                        <!-- Filter Lokasi -->
                        <div class="col-lg-2 col-md-4 col-6">
                            <select id="filterLocation" class="form-select form-select-sm">
                                <option value="all">Semua Lokasi</option>
                                <option value="WPM">Gudang WPM</option>
                                <option value="QC_PM">QC PM</option>
                                <option value="PRODUKSI">Area Produksi</option>
                                <option value="QC_PROSES">QC Proses</option>
                                <option value="WFG">Gudang WFG</option>
                                <option value="WAREHOUSE_PAS">Whs PT PAS</option>
                                <option value="ENGINEERING_WORKSHOP">Workshop ENG</option>
                                <option value="SCRAP">Scrap / Afkir</option>
                            </select>
                        </div>

                        <!-- Filter Reused -->
                        <div class="col-lg-2 col-md-4 col-6">
                            <select id="filterReusedStatus" class="form-select form-select-sm">
                                <option value="all">Semua Siklus</option>
                                <option value="normal">Normal (&lt; 18x)</option>
                                <option value="warning">Warning (18 - 20x)</option>
                                <option value="max">Maksimal (≥ 21x)</option>
                            </select>
                        </div>

                        <!-- Filter Kondisi Fisik -->
                        <div class="col-lg-2 col-md-4 col-6">
                            <select id="filterCondition" class="form-select form-select-sm">
                                <option value="all">Kondisi Fisik: Semua</option>
                                <option value="OK">Kondisi OK</option>
                                <option value="NOT_OK">Kondisi NOT OK</option>
                            </select>
                        </div>

                        <!-- Filter Kelengkapan Fisik -->
                        <div class="col-lg-2 col-md-4 col-6">
                            <select id="filterTagIssue" class="form-select form-select-sm">
                                <option value="all">Kelengkapan Tag: Semua</option>
                                <option value="any_missing">Ada Tag Hilang/Rusak</option>
                                <option value="missing_barcode">Barcode Rusak</option>
                                <option value="missing_rfid">RFID Rusak</option>
                                <option value="missing_nti">NTI Rusak</option>
                            </select>
                        </div>

                        <!-- Reset Filter -->
                        <div class="col-lg-1 col-md-4 col-12 text-end">
                            <button type="button" id="btnResetFilters"
                                class="btn btn-sm btn-light w-100 border text-muted">
                                <i class="ri-refresh-line"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap align-middle mb-0 fs-13" id="tableKempuMonitoring">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 50px;">No</th>
                                    <th>ID Kempu</th>
                                    <th>RFID / SPB</th>
                                    <th>Lokasi Saat Ini</th>
                                    <th>Status Operasional</th>
                                    <th style="min-width: 140px;">Siklus Reused</th>
                                    <th>Kondisi Fisik</th>
                                    <th>Kelengkapan Tag</th>
                                    <th>Aktivitas Terakhir</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableKempuBody">
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="ri-loader-4-line ri-spin fs-24 d-block mb-2"></i>
                                        Memuat data monitoring kempu...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div class="card-footer bg-transparent border-top py-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="text-muted fs-12">
                            Menampilkan <span class="fw-semibold" id="paginationFrom">0</span> sampai <span
                                class="fw-semibold" id="paginationTo">0</span> dari <span class="fw-semibold"
                                id="paginationTotal">0</span> kempu
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                                <!-- Dynamic pagination -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Traceability Timeline -->
    <div class="modal fade" id="modalTraceabilityHistory" tabindex="-1" aria-labelledby="modalHistoryTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-xs flex-shrink-0">
                            <span class="avatar-title rounded bg-white text-primary fs-16">
                                <i class="ri-route-line"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalHistoryTitle">History Kempu</h5>
                            <span class="fs-12 text-white-50" id="modalHistorySubtitle">ID Kempu: -</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        {{-- <a href="javascript:void(0);" id="modalBtnPrintQr" class="btn btn-sm btn-light btn-icon"
                            target="_blank" title="Cetak Label QR Code">
                            <i class="ri-qr-code-line text-dark"></i>
                        </a> --}}
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <!-- Kempu Profile Quick Info Card -->
                    <div class="card border mb-3 bg-light bg-opacity-40">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-sm-3 col-6">
                                    <span class="fs-11 text-muted text-uppercase fw-semibold d-block">ID Kempu</span>
                                    <span class="fw-bold fs-14 text-dark font-monospace" id="modalKempuId">-</span>
                                    <span class="fs-11 text-muted d-block mt-1" id="modalKempuRfid">RFID: -</span>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <span class="fs-11 text-muted text-uppercase fw-semibold d-block">Lokasi Terkini</span>
                                    <span class="badge bg-primary-subtle text-primary fs-12 mt-1"
                                        id="modalKempuLoc">-</span>
                                    <span class="fs-11 text-muted d-block mt-1" id="modalKempuStatus">Status: -</span>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <span class="fs-11 text-muted text-uppercase fw-semibold d-block">Siklus Reused</span>
                                    <span class="fw-bold fs-14 text-dark" id="modalKempuReused">-</span>
                                    <div class="reused-progress-track mt-1">
                                        <div class="reused-progress-bar bg-success" id="modalReusedProgress"
                                            style="width: 0%;"></div>
                                    </div>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <span class="fs-11 text-muted text-uppercase fw-semibold d-block">Kondisi & Tag
                                        Fisik</span>
                                    <span class="badge bg-success-subtle text-success me-1" id="modalKempuCond">OK</span>
                                    <div class="d-flex gap-1 mt-1" id="modalKempuTags">
                                        <!-- Badges Barcode, RFID, NTI -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Detail Row -->
                    <div
                        class="d-flex align-items-center justify-content-between mb-3 text-muted fs-12 pb-2 border-bottom">
                        <div>
                            <i class="ri-file-list-3-line text-primary me-1"></i> SPB: <span class="fw-semibold text-dark"
                                id="modalKempuSpb">-</span>
                            <span class="mx-2">•</span>
                            <i class="ri-calendar-line text-info me-1"></i> GR: <span class="fw-semibold text-dark"
                                id="modalKempuGrDate">-</span>
                        </div>
                        <div id="modalHistoryCountBadge" class="badge bg-light text-muted border">
                            0 Riwayat Tercatat
                        </div>
                    </div>

                    <!-- Timeline Content -->
                    <h6 class="fs-12 fw-bold text-uppercase text-muted mb-3">
                        <i class="ri-history-line me-1 text-primary"></i> Timeline History Kempu (Urut Terkini)
                    </h6>
                    <div class="timeline-box" id="modalTimelineContainer">
                        <!-- Populated by JS -->
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // State
            let currentPage = 1;
            let currentTrendDays = 7;
            let autoRefreshTimer = null;
            let chartScanTrend = null;
            let chartLocation = null;
            let chartReused = null;

            // Lokasi Label Map
            const LOCATION_LABELS = {
                'WPM': 'Gudang WPM',
                'QC_PM': 'QC PM',
                'PRODUKSI': 'Area Produksi',
                'QC_PROSES': 'QC Proses',
                'WFG': 'Gudang WFG',
                'WAREHOUSE_PAS': 'Whs PT PAS',
                'ENGINEERING_WORKSHOP': 'Workshop ENG',
                'SCRAP': 'Scrap / Afkir'
            };

            const LOCATION_BADGES = {
                'WPM': 'bg-primary-subtle text-primary',
                'QC_PM': 'bg-warning-subtle text-warning',
                'PRODUKSI': 'bg-info-subtle text-info',
                'QC_PROSES': 'bg-warning-subtle text-warning',
                'WFG': 'bg-success-subtle text-success',
                'WAREHOUSE_PAS': 'bg-purple-subtle text-purple',
                'ENGINEERING_WORKSHOP': 'bg-secondary-subtle text-secondary',
                'SCRAP': 'bg-danger-subtle text-danger'
            };

            // ---- 1. Load KPI Metrics ----
            function loadKpi() {
                $.ajax({
                    url: "{{ route('dashboard.kempu.kpi') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status && res.data) {
                            const d = res.data;

                            // KPI Cards
                            $('#kpiTotalActive').text(Number(d.total_active || 0).toLocaleString());
                            $('#kpiTotalAll').text(Number(d.total_kempu || 0).toLocaleString());
                            $('#kpiTotalScrap').text(Number(d.total_scrap || 0).toLocaleString());

                            $('#kpiTodayScans').text(Number(d.today?.scans || 0).toLocaleString());

                            $('#kpiReusedWarning').text(Number(d.reused?.warning || 0)
                                .toLocaleString());
                            $('#kpiReusedMaxBadge').text('Max (≥21x): ' + Number(d.reused?.max || 0)
                                .toLocaleString());
                            $('#kpiAvgReused').text(d.reused?.average || 0);

                            // Warning Progress
                            const totalWarnOrMax = (d.reused?.warning || 0) + (d.reused?.max || 0);
                            const warnPct = d.total_active > 0 ? Math.min(100, Math.round((
                                totalWarnOrMax / d.total_active) * 100)) : 0;
                            $('#kpiReusedProgressBar').css('width', warnPct + '%');

                            $('#kpiInflowToday').text(Number(d.today?.inflow || 0).toLocaleString());
                            $('#kpiOutflowToday').text(Number(d.today?.outflow || 0).toLocaleString());

                            // Pipeline Flow Node Counts
                            const loc = d.locations || {};
                            $('#flowCountWpm').text(Number(loc.wpm || 0).toLocaleString());
                            $('#flowCountQcPm').text(Number(loc.qc_pm || 0).toLocaleString());
                            $('#flowCountProduksi').text(Number(loc.produksi || 0).toLocaleString());
                            $('#flowCountQcProses').text(Number(loc.qc_proses || 0).toLocaleString());
                            $('#flowCountWfg').text(Number(loc.wfg || 0).toLocaleString());
                            $('#flowCountPas').text(Number(loc.pas || 0).toLocaleString());
                            $('#flowCountEng').text(Number(loc.eng || 0).toLocaleString());
                            $('#flowCountScrap').text(Number(d.total_scrap || 0).toLocaleString());

                            $('#lastUpdatedText').text('Update: ' + moment().format('HH:mm:ss'));
                        }
                    },
                    error: function(xhr) {
                        console.error('Gagal memuat KPI:', xhr);
                    }
                });
            }

            // ---- 2. Load Charts ----
            function loadCharts() {
                $.ajax({
                    url: "{{ route('dashboard.kempu.charts') }}",
                    type: 'GET',
                    data: {
                        days: currentTrendDays
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status && res.data) {
                            renderScanTrendChart(res.data.trend);
                            renderLocationChart(res.data.location);
                            renderReusedChart(res.data.reused);
                        }
                    },
                    error: function(xhr) {
                        console.error('Gagal memuat charts:', xhr);
                    }
                });
            }

            function renderScanTrendChart(trendData) {
                if (!trendData) return;

                const options = {
                    series: trendData.series || [],
                    chart: {
                        type: 'area',
                        height: 290,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        animations: {
                            enabled: true
                        }
                    },
                    colors: ['#4361ee', '#f59e0b', '#06b6d4', '#10b981', '#14b8a6', '#8b5cf6', '#64748b'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.35,
                            opacityTo: 0.05,
                            stops: [0, 95, 100]
                        }
                    },
                    xaxis: {
                        categories: trendData.categories || [],
                        labels: {
                            style: {
                                fontSize: '11px',
                                colors: '#64748b'
                            }
                        }
                    },
                    yaxis: {
                        min: 0,
                        labels: {
                            style: {
                                fontSize: '11px',
                                colors: '#64748b'
                            }
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: {
                            formatter: (val) => val + ' scan'
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '11px'
                    },
                    grid: {
                        borderColor: '#f1f5f9'
                    }
                };

                if (chartScanTrend) {
                    chartScanTrend.updateOptions(options);
                } else {
                    chartScanTrend = new ApexCharts(document.querySelector("#chartScanTrend"), options);
                    chartScanTrend.render();
                }
            }

            function renderLocationChart(locData) {
                if (!locData) return;

                const options = {
                    series: locData.series || [],
                    labels: locData.labels || [],
                    chart: {
                        type: 'donut',
                        height: 290
                    },
                    colors: ['#4361ee', '#f59e0b', '#06b6d4', '#eab308', '#10b981', '#8b5cf6', '#64748b',
                        '#ef4444'
                    ],
                    legend: {
                        position: 'bottom',
                        fontSize: '11px'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Kempu',
                                        fontSize: '12px',
                                        fontWeight: 600,
                                        formatter: (w) => {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width: 0
                    }
                };

                if (chartLocation) {
                    chartLocation.updateOptions(options);
                } else {
                    chartLocation = new ApexCharts(document.querySelector("#chartLocationDistribution"), options);
                    chartLocation.render();
                }
            }

            function renderReusedChart(reusedData) {
                if (!reusedData) return;

                const options = {
                    series: [{
                        name: 'Jumlah Kempu',
                        data: reusedData.series || []
                    }],
                    chart: {
                        type: 'bar',
                        height: 240,
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            horizontal: false,
                            columnWidth: '45%',
                            distributed: true
                        }
                    },
                    colors: ['#10b981', '#06b6d4', '#3b82f6', '#f59e0b', '#ef4444'],
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    },
                    xaxis: {
                        categories: reusedData.labels || [],
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yaxis: {
                        min: 0,
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    grid: {
                        borderColor: '#f1f5f9'
                    },
                    tooltip: {
                        y: {
                            formatter: (val) => val + ' unit kempu'
                        }
                    }
                };

                if (chartReused) {
                    chartReused.updateOptions(options);
                } else {
                    chartReused = new ApexCharts(document.querySelector("#chartReusedDistribution"), options);
                    chartReused.render();
                }
            }

            // ---- 3. Load Recent Scans Feed ----
            function loadRecentScans() {
                $.ajax({
                    url: "{{ route('dashboard.kempu.recent_scans') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status && res.data) {
                            renderRecentScans(res.data);
                        }
                    },
                    error: function(xhr) {
                        console.error('Gagal memuat feed:', xhr);
                    }
                });
            }

            function renderRecentScans(scans) {
                const container = $('#recentScansFeed');
                if (!scans || scans.length === 0) {
                    container.html(`
                        <div class="text-center py-4 text-muted">
                            <i class="ri-inbox-line fs-24 d-block mb-1"></i>
                            <p class="fs-12 mb-0">Belum ada riwayat scan tercatat.</p>
                        </div>
                    `);
                    return;
                }

                let html = '';
                scans.forEach(item => {
                    const stageBadge = LOCATION_BADGES[item.stage] || 'bg-light text-dark';
                    const resultBadge = item.action_result === 'OK' || item.action_result === 'RELEASE' ?
                        'text-success' :
                        (item.action_result === 'HOLD' ? 'text-warning' : 'text-danger');

                    html += `
                        <div class="feed-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-xs flex-shrink-0">
                                    <span class="avatar-title rounded-circle ${stageBadge} fs-12 fw-bold">
                                        ${item.stage ? item.stage.substring(0, 2) : 'ST'}
                                    </span>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="javascript:void(0);" class="fw-bold text-dark fs-12 btn-show-history" data-id="${item.id_kempu}">
                                            ${item.id_kempu}
                                        </a>
                                        <span class="badge ${stageBadge} fs-10">${item.stage}</span>
                                        <span class="badge bg-light text-muted border fs-10">${item.action}</span>
                                    </div>
                                    <div class="text-muted fs-11 mt-1">
                                        <i class="ri-user-3-line"></i> ${item.operator} • Ke: <span class="fw-semibold text-dark">${item.to_location}</span>
                                        ${item.notes ? ` • <span class="fst-italic text-truncate d-inline-block" style="max-width:200px;">"${item.notes}"</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <span class="fs-11 fw-semibold ${resultBadge} d-block">${item.action_result}</span>
                                <span class="fs-10 text-muted">${item.time_ago}</span>
                            </div>
                        </div>
                    `;
                });

                container.html(html);
            }

            // ---- 4. Load Monitoring Table ----
            function loadTable(page = 1) {
                currentPage = page;

                const params = {
                    page: page,
                    search: $('#filterSearch').val(),
                    location: $('#filterLocation').val(),
                    reused_status: $('#filterReusedStatus').val(),
                    condition: $('#filterCondition').val(),
                    tag_issue: $('#filterTagIssue').val(),
                };

                $('#tableKempuBody').html(`
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="ri-loader-4-line ri-spin fs-24 d-block mb-2"></i>
                            Memuat data monitoring kempu...
                        </td>
                    </tr>
                `);

                $.ajax({
                    url: "{{ route('dashboard.kempu.data') }}",
                    type: 'GET',
                    data: params,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status && res.data) {
                            renderTable(res.data);
                        }
                    },
                    error: function(xhr) {
                        $('#tableKempuBody').html(`
                            <tr>
                                <td colspan="10" class="text-center py-4 text-danger">
                                    <i class="ri-error-warning-line fs-20 d-block mb-1"></i>
                                    Gagal memuat data. Silakan muat ulang.
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            function renderTable(data) {
                const items = data.items || [];
                const tbody = $('#tableKempuBody');

                if (items.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="ri-inbox-archive-line fs-28 d-block mb-2"></i>
                                Tidak ditemukan data kempu sesuai kriteria filter.
                            </td>
                        </tr>
                    `);
                    $('#paginationFrom').text('0');
                    $('#paginationTo').text('0');
                    $('#paginationTotal').text('0');
                    $('#paginationContainer').empty();
                    return;
                }

                let html = '';
                items.forEach((row, idx) => {
                    const rowNum = (data.from || 1) + idx;
                    const locLabel = LOCATION_LABELS[row.current_location] || row.current_location;
                    const locBadge = LOCATION_BADGES[row.current_location] || 'bg-light text-dark';

                    // Reused Bar Color
                    let reusedColor = 'bg-success';
                    if (row.reused_count >= 21) {
                        reusedColor = 'bg-danger';
                    } else if (row.reused_count >= 18) {
                        reusedColor = 'bg-warning';
                    }

                    // Condition Badge
                    const condBadge = row.condition === 'OK' ?
                        '<span class="badge bg-success-subtle text-success">OK</span>' :
                        '<span class="badge bg-danger-subtle text-danger">NOT OK</span>';

                    // Tag Indicators
                    const tagBarcode = row.has_barcode ?
                        '<span class="badge bg-light text-success border" title="Barcode Terpasang"><i class="ri-barcode-line"></i> B</span>' :
                        '<span class="badge bg-danger-subtle text-danger" title="Barcode Rusak/Hilang"><i class="ri-close-line"></i> B</span>';

                    const tagRfid = row.has_rfid ?
                        '<span class="badge bg-light text-success border" title="RFID Terpasang"><i class="ri-rfid-line"></i> R</span>' :
                        '<span class="badge bg-danger-subtle text-danger" title="RFID Rusak/Hilang"><i class="ri-close-line"></i> R</span>';

                    const tagNti = row.has_nti ?
                        '<span class="badge bg-light text-success border" title="NTI Terpasang"><i class="ri-shield-check-line"></i> N</span>' :
                        '<span class="badge bg-danger-subtle text-danger" title="NTI Rusak/Hilang"><i class="ri-close-line"></i> N</span>';

                    html += `
                        <tr>
                            <td class="ps-3 text-muted">${rowNum}</td>
                            <td>
                                <a href="javascript:void(0);" class="fw-bold text-primary btn-show-history" data-id="${row.id_kempu}">
                                    ${row.id_kempu}
                                </a>
                                <span class="d-block text-muted fs-11">${row.last_scanned_diff}</span>
                            </td>
                            <td>
                                <span class="d-block text-dark fw-medium">${row.rfid}</span>
                                <span class="fs-11 text-muted">SPB: ${row.no_spb}</span>
                            </td>
                            <td>
                                <span class="badge ${locBadge} fs-11">${locLabel}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fs-11">${row.current_status}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold ${row.reused_count >= 21 ? 'text-danger' : (row.reused_count >= 18 ? 'text-warning' : 'text-dark')}">
                                        ${row.reused_count} / ${row.max_reused}x
                                    </span>
                                    <span class="fs-10 text-muted">${row.reused_pct}%</span>
                                </div>
                                <div class="reused-progress-track">
                                    <div class="reused-progress-bar ${reusedColor}" style="width: ${row.reused_pct}%"></div>
                                </div>
                            </td>
                            <td>${condBadge}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    ${tagBarcode}
                                    ${tagRfid}
                                    ${tagNti}
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium text-dark d-block fs-12">${row.last_action}</span>
                                <span class="text-muted fs-11">${row.last_scanned_at}</span>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-subtle-secondary btn-icon dropdown-toggle drop-arrow-none" type="button" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item btn-show-history" href="javascript:void(0);" data-id="${row.id_kempu}">
                                                <i class="ri-route-line text-primary me-2"></i> Traceability & Timeline
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                tbody.html(html);

                // Update Pagination Info
                $('#paginationFrom').text(data.from || 0);
                $('#paginationTo').text(data.to || 0);
                $('#paginationTotal').text(Number(data.total || 0).toLocaleString());

                renderPagination(data.current_page, data.last_page);
            }

            function renderPagination(current, last) {
                const container = $('#paginationContainer');
                container.empty();

                if (last <= 1) return;

                // Prev Button
                container.append(`
                    <li class="page-item ${current === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${current - 1}">&laquo;</a>
                    </li>
                `);

                const start = Math.max(1, current - 2);
                const end = Math.min(last, current + 2);

                if (start > 1) {
                    container.append(
                        `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="1">1</a></li>`
                    );
                    if (start > 2) {
                        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                for (let i = start; i <= end; i++) {
                    container.append(`
                        <li class="page-item ${i === current ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `);
                }

                if (end < last) {
                    if (end < last - 1) {
                        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                    container.append(
                        `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="${last}">${last}</a></li>`
                    );
                }

                // Next Button
                container.append(`
                    <li class="page-item ${current === last ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${current + 1}">&raquo;</a>
                    </li>
                `);
            }

            // ---- 5. Traceability History Modal & Quick Trace ----
            function openTraceabilityModal(idKempu) {
                if (!idKempu) return;

                $('#modalKempuId').text(idKempu);
                $('#modalHistorySubtitle').text('ID Kempu: ' + idKempu);
                $('#modalBtnPrintQr').attr('href', "{{ url('/kempu/master/print-qr') }}?search=" +
                    encodeURIComponent(idKempu));
                $('#modalTimelineContainer').html(`
                    <div class="text-center py-4 text-muted">
                        <i class="ri-loader-4-line ri-spin fs-24 d-block mb-1"></i>
                        Memuat riwayat jejak kempu...
                    </div>
                `);

                const modal = new bootstrap.Modal(document.getElementById('modalTraceabilityHistory'));
                modal.show();

                $.ajax({
                    url: "{{ url('/dashboard/kempu/history') }}/" + encodeURIComponent(idKempu),
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.status && res.kempu) {
                            const k = res.kempu;
                            const main = k.main || {};

                            $('#modalKempuId').text(k.id_kempu || idKempu);
                            $('#modalKempuRfid').text('RFID: ' + (k.rfid || '-'));
                            $('#modalKempuSpb').text(k.no_spb || '-');
                            $('#modalKempuGrDate').text(k.gr_date ? moment(k.gr_date).format(
                                'DD/MM/YYYY') : '-');

                            const locLabel = LOCATION_LABELS[main.current_location] || main
                                .current_location || '-';
                            $('#modalKempuLoc').text(locLabel);
                            $('#modalKempuStatus').text('Status: ' + (main.current_status || '-'));

                            const reused = main.reused_count || 0;
                            const maxReused = main.max_reused || 21;
                            const pct = Math.min(100, Math.round((reused / maxReused) * 100));
                            $('#modalKempuReused').text(reused + ' / ' + maxReused + 'x (' + pct +
                                '%)');
                            $('#modalReusedProgress').css('width', pct + '%');

                            if (reused >= 21) {
                                $('#modalReusedProgress').removeClass('bg-success bg-warning').addClass(
                                    'bg-danger');
                            } else if (reused >= 18) {
                                $('#modalReusedProgress').removeClass('bg-success bg-danger').addClass(
                                    'bg-warning');
                            } else {
                                $('#modalReusedProgress').removeClass('bg-warning bg-danger').addClass(
                                    'bg-success');
                            }

                            if (main.condition === 'OK') {
                                $('#modalKempuCond').removeClass('bg-danger-subtle text-danger')
                                    .addClass('bg-success-subtle text-success').text('OK');
                            } else {
                                $('#modalKempuCond').removeClass('bg-success-subtle text-success')
                                    .addClass('bg-danger-subtle text-danger').text('NOT OK');
                            }

                            // Tags checklist
                            const tagB = (main.has_barcode ?? true) ?
                                '<span class="badge bg-light text-success border">✓ Barcode</span>' :
                                '<span class="badge bg-danger-subtle text-danger">✗ Barcode</span>';
                            const tagR = (main.has_rfid ?? true) ?
                                '<span class="badge bg-light text-success border">✓ RFID</span>' :
                                '<span class="badge bg-danger-subtle text-danger">✗ RFID</span>';
                            const tagN = (main.has_nti ?? true) ?
                                '<span class="badge bg-light text-success border">✓ NTI</span>' :
                                '<span class="badge bg-danger-subtle text-danger">✗ NTI</span>';
                            $('#modalKempuTags').html(tagB + tagR + tagN);

                            const histories = res.histories || [];
                            $('#modalHistoryCountBadge').text(histories.length + ' Riwayat Tercatat');

                            if (histories.length === 0) {
                                $('#modalTimelineContainer').html(`
                                    <div class="text-center py-4 text-muted">
                                        <p class="fs-12 mb-0">Belum ada riwayat pergerakan yang tercatat untuk kempu ini.</p>
                                    </div>
                                `);
                                return;
                            }

                            let timelineHtml = '';
                            histories.forEach(h => {
                                const stageBadge = LOCATION_BADGES[h.stage] ||
                                    'bg-light text-dark';
                                const resColor = (h.action_result === 'OK' || h
                                    .action_result === 'RELEASE') ? 'text-success' : (h
                                    .action_result === 'HOLD' ? 'text-warning' :
                                    'text-danger');
                                timelineHtml += `
                                    <div class="timeline-step">
                                        <div class="timeline-dot"></div>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div>
                                                <span class="badge ${stageBadge} fs-10 me-1">${h.stage}</span>
                                                <span class="fw-bold fs-12 text-dark">${h.action}</span>
                                                <span class="badge bg-light ${resColor} border fs-10 ms-1">${h.action_result || 'OK'}</span>
                                            </div>
                                            <span class="fs-11 text-muted">${moment(h.created_at).format('DD/MM/YYYY HH:mm')}</span>
                                        </div>
                                        <div class="fs-12 text-muted mb-1">
                                            Rute: <span class="fw-semibold text-dark">${h.from_location || '-'}</span> ➔ <span class="fw-semibold text-dark">${h.to_location || '-'}</span>
                                            • Operator: <span class="fw-semibold text-dark">${h.operator_display_name || 'System'}</span>
                                            • Siklus Reused: <span class="fw-semibold text-dark">${h.reused_count || 0}x</span>
                                        </div>
                                        ${h.notes ? `<div class="p-2 rounded bg-light fs-11 text-muted border mt-1">${h.notes}</div>` : ''}
                                    </div>
                                `;
                            });

                            $('#modalTimelineContainer').html(timelineHtml);
                        }
                    },
                    error: function() {
                        $('#modalTimelineContainer').html(`
                            <div class="text-center py-4 text-danger">
                                <i class="ri-error-warning-line fs-20 d-block mb-1"></i>
                                <p class="fs-12 mb-0">Kempu dengan ID '${idKempu}' tidak ditemukan.</p>
                            </div>
                        `);
                    }
                });
            }

            $(document).on('click', '.btn-show-history', function() {
                const idKempu = $(this).data('id');
                openTraceabilityModal(idKempu);
            });

            $('#btnQuickTrace').on('click', function() {
                const idKempu = $('#quickTraceInput').val().trim();
                if (idKempu) {
                    openTraceabilityModal(idKempu);
                } else {
                    $('#quickTraceInput').focus();
                }
            });

            $('#quickTraceInput').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnQuickTrace').click();
                }
            });

            // ---- Event Listeners ----
            // Pagination click
            $(document).on('click', '#paginationContainer .page-link', function(e) {
                e.preventDefault();
                const p = $(this).data('page');
                if (p && p !== currentPage) {
                    loadTable(p);
                }
            });

            // Filter changes
            $('#filterLocation, #filterReusedStatus, #filterCondition, #filterTagIssue').on('change', function() {
                loadTable(1);
            });

            let searchTimeout = null;
            $('#filterSearch').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadTable(1);
                }, 400);
            });

            $('#btnResetFilters').on('click', function() {
                $('#filterSearch').val('');
                $('#filterLocation').val('all');
                $('#filterReusedStatus').val('all');
                $('#filterCondition').val('all');
                $('#filterTagIssue').val('all');
                $('.flow-node').removeClass('active-filter');
                $('#btnResetStationFilter').addClass('d-none');
                loadTable(1);
            });

            // Flow Node Click (Filter by Station)
            $('.flow-node').on('click', function() {
                const loc = $(this).data('location');
                $('.flow-node').removeClass('active-filter');
                $(this).addClass('active-filter');
                $('#filterLocation').val(loc);
                $('#btnResetStationFilter').removeClass('d-none');
                loadTable(1);
            });

            $('#btnResetStationFilter').on('click', function() {
                $('.flow-node').removeClass('active-filter');
                $(this).addClass('d-none');
                $('#filterLocation').val('all');
                loadTable(1);
            });

            // Trend range change
            $('.btn-trend-range').on('click', function() {
                $('.btn-trend-range').removeClass('active');
                $(this).addClass('active');
                currentTrendDays = $(this).data('days');
                loadCharts();
            });

            // Refresh All
            $('#btnRefreshAll').on('click', function() {
                loadKpi();
                loadCharts();
                loadRecentScans();
                loadTable(currentPage);
            });

            // Auto Refresh Interval
            $('#autoRefreshInterval').on('change', function() {
                const val = parseInt($(this).val());
                if (autoRefreshTimer) {
                    clearInterval(autoRefreshTimer);
                    autoRefreshTimer = null;
                }
                if (val > 0) {
                    autoRefreshTimer = setInterval(() => {
                        loadKpi();
                        loadRecentScans();
                        loadTable(currentPage);
                    }, val);
                }
            });

            // Export Data ke Excel (.xlsx)
            $('#btnExportData').on('click', function() {
                const params = new URLSearchParams({
                    search: $('#filterSearch').val() || '',
                    location: $('#filterLocation').val() || 'all',
                    reused_status: $('#filterReusedStatus').val() || 'all',
                    condition: $('#filterCondition').val() || 'all',
                    tag_issue: $('#filterTagIssue').val() || 'all',
                });
                window.location.href = "{{ route('dashboard.kempu.export') }}?" + params.toString();
            });

            // ---- Initial Load ----
            loadKpi();
            loadCharts();
            loadRecentScans();
            loadTable(1);

            // Default auto-refresh timer 30s
            autoRefreshTimer = setInterval(() => {
                loadKpi();
                loadRecentScans();
                loadTable(currentPage);
            }, 30000);
        });
    </script>
@endsection
