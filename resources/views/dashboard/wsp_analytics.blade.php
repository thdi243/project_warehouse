<!DOCTYPE html>
<html lang="id" data-layout-mode="dark">

    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>WSP ANALYTICS & INTELLIGENCE DASHBOARD</title>

        <link rel="shortcut icon" href="{{ asset('assets/images/logo/kecap.png') }}">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap"
            rel="stylesheet">

        <!-- Stylesheets -->
        <link href="{{ asset('material/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

        <style>
            :root {
                --bg-body: #060b17;
                --card-bg: #0b1426;
                --card-inner: #0e1a32;
                --card-border: rgba(255, 255, 255, 0.07);
                --border-highlight: rgba(56, 189, 248, 0.2);
                --text-main: #f8fafc;
                --text-sub: #64748b;
                --text-cyan: #38bdf8;
                --primary: #3b82f6;
                --success: #10b981;
                --warning: #f59e0b;
                --danger: #ef4444;
                --purple: #8b5cf6;
            }

            body {
                background-color: var(--bg-body);
                color: var(--text-main);
                font-family: 'Outfit', sans-serif;
                min-height: 100vh;
                overflow-x: hidden;
                letter-spacing: -0.1px;
            }

            .mono {
                font-family: 'JetBrains Mono', monospace;
            }

            /* Top Header Container */
            .wwtp-header-box {
                background-color: #091224;
                border: 1px solid var(--border-highlight);
                border-radius: 12px;
                padding: 12px 20px;
                margin-bottom: 14px;
                box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.5);
            }

            .header-logo-icon {
                width: 44px;
                height: 44px;
                background: linear-gradient(135deg, rgba(56, 189, 248, 0.15) 0%, rgba(37, 99, 235, 0.25) 100%);
                border: 1px solid rgba(56, 189, 248, 0.35);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #38bdf8;
                font-size: 22px;
                flex-shrink: 0;
            }

            .header-title-main {
                font-size: 19px;
                font-weight: 900;
                color: #ffffff;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
                text-transform: uppercase;
            }

            .header-subtitle-cyan {
                font-size: 11px;
                font-weight: 700;
                color: #38bdf8;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }

            .header-tagline {
                font-size: 11px;
                color: #94a3b8;
                font-style: italic;
            }

            .clock-badge {
                font-family: 'JetBrains Mono', monospace;
                font-size: 12px;
                font-weight: 600;
                background: #0f1d38;
                color: #cbd5e1;
                padding: 6px 14px;
                border-radius: 6px;
                border: 1px solid rgba(255, 255, 255, 0.08);
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            /* Filter Toolbar Card */
            .filter-toolbar-box {
                background-color: var(--card-bg);
                border: 1px solid var(--card-border);
                border-radius: 10px;
                padding: 10px 16px;
                margin-bottom: 14px;
            }

            .period-btn {
                font-size: 11px;
                font-weight: 700;
                padding: 5px 12px;
                border-radius: 6px;
                background: #0e1a32;
                border: 1px solid rgba(255, 255, 255, 0.08);
                color: #94a3b8;
                cursor: pointer;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                transition: all 0.15s ease;
            }

            .period-btn:hover {
                color: #ffffff;
                border-color: rgba(56, 189, 248, 0.4);
            }

            .period-btn.active {
                background: #2563eb;
                color: #ffffff;
                border-color: #38bdf8;
                box-shadow: 0 0 10px rgba(37, 99, 235, 0.4);
            }

            .form-select-wwtp,
            .form-input-wwtp {
                background-color: #0e1a32 !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                color: #f1f5f9 !important;
                font-size: 11.5px;
                border-radius: 6px;
                padding: 5px 10px;
            }

            .form-select-wwtp:focus,
            .form-input-wwtp:focus {
                border-color: #38bdf8 !important;
                box-shadow: none !important;
            }

            /* Standard WWTP Dashboard Cards */
            .wwtp-card {
                background-color: var(--card-bg);
                border: 1px solid var(--card-border);
                border-radius: 10px;
                position: relative;
                box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.4);
                transition: border-color 0.2s ease, transform 0.2s ease;
            }

            .wwtp-card:hover {
                border-color: rgba(56, 189, 248, 0.25);
            }

            .wwtp-card-header {
                padding: 12px 16px;
                border-bottom: 1px solid var(--card-border);
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .card-header-icon {
                width: 28px;
                height: 28px;
                border-radius: 6px;
                background: rgba(56, 189, 248, 0.1);
                color: #38bdf8;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 15px;
                flex-shrink: 0;
                margin-right: 8px;
            }

            .card-header-icon.green {
                background: rgba(16, 185, 129, 0.12);
                color: #10b981;
            }

            .card-header-icon.amber {
                background: rgba(245, 158, 11, 0.12);
                color: #f59e0b;
            }

            .card-header-icon.red {
                background: rgba(239, 68, 68, 0.12);
                color: #ef4444;
            }

            .card-header-icon.purple {
                background: rgba(139, 92, 246, 0.12);
                color: #a78bfa;
            }

            .card-title-text {
                font-size: 11.5px;
                font-weight: 800;
                color: #ffffff;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin-bottom: 0;
                line-height: 1.2;
            }

            .card-subtitle-text {
                font-size: 9.5px;
                font-weight: 600;
                color: var(--text-sub);
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .wwtp-card-body {
                padding: 14px 16px;
            }

            /* Top KPI Value Block */
            .kpi-main-number {
                font-family: 'JetBrains Mono', monospace;
                font-size: 23px;
                font-weight: 800;
                color: #ffffff;
                line-height: 1.1;
            }

            .kpi-target-tag {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                display: flex;
                align-items: center;
                gap: 4px;
                margin-top: 8px;
            }

            .kpi-target-tag.green {
                color: #10b981;
            }

            .kpi-target-tag.amber {
                color: #f59e0b;
            }

            .kpi-target-tag.red {
                color: #ef4444;
            }

            .kpi-target-tag.cyan {
                color: #38bdf8;
            }

            .kpi-target-tag.purple {
                color: #c084fc;
            }

            /* Tables styling */
            .wwtp-table {
                width: 100%;
                margin-bottom: 0;
                color: #cbd5e1;
                font-size: 11px;
            }

            .wwtp-table thead th {
                background: #091224;
                color: #94a3b8;
                font-weight: 800;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                padding: 9px 12px;
                border-bottom: 1px solid var(--card-border);
                white-space: nowrap;
            }

            .wwtp-table tbody td {
                padding: 8px 12px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.04);
                vertical-align: middle;
            }

            .wwtp-table tbody tr:hover {
                background-color: rgba(56, 189, 248, 0.04);
            }

            .status-pill {
                display: inline-block;
                font-size: 9.5px;
                font-weight: 800;
                padding: 2px 7px;
                border-radius: 4px;
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }

            .status-pill.green {
                background: rgba(16, 185, 129, 0.15);
                color: #34d399;
                border: 1px solid rgba(16, 185, 129, 0.3);
            }

            .status-pill.amber {
                background: rgba(245, 158, 11, 0.15);
                color: #fbbf24;
                border: 1px solid rgba(245, 158, 11, 0.3);
            }

            .status-pill.red {
                background: rgba(239, 68, 68, 0.15);
                color: #f87171;
                border: 1px solid rgba(239, 68, 68, 0.3);
            }

            .status-pill.cyan {
                background: rgba(56, 189, 248, 0.15);
                color: #38bdf8;
                border: 1px solid rgba(56, 189, 248, 0.3);
            }

            .status-pill.gray {
                background: rgba(148, 163, 184, 0.15);
                color: #94a3b8;
                border: 1px solid rgba(148, 163, 184, 0.3);
            }

            .pulse-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                display: inline-block;
                animation: pulse-glow 2s infinite;
            }

            .pulse-dot.red {
                background: #ef4444;
                box-shadow: 0 0 8px #ef4444;
            }

            .pulse-dot.green {
                background: #10b981;
                box-shadow: 0 0 8px #10b981;
            }

            @keyframes pulse-glow {
                0% {
                    transform: scale(0.95);
                    opacity: 0.8;
                }

                50% {
                    transform: scale(1.2);
                    opacity: 1;
                }

                100% {
                    transform: scale(0.95);
                    opacity: 0.8;
                }
            }

            .stat-badge-sub {
                background: #0e1a32;
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 6px;
                padding: 6px 10px;
            }

            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 5px;
                height: 5px;
            }

            ::-webkit-scrollbar-track {
                background: #060b17;
            }

            ::-webkit-scrollbar-thumb {
                background: #1a2942;
                border-radius: 3px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #263c61;
            }

            /* Card Loading Overlay & Glowing Spinners */
            .card-loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(11, 20, 38, 0.88);
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 25;
                border-radius: 10px;
                opacity: 1;
                transition: opacity 0.25s ease;
                pointer-events: none;
            }

            .card-loading-overlay.fade-out {
                opacity: 0;
            }

            .spinner-glow {
                width: 2.2rem;
                height: 2.2rem;
                border: 2.5px solid rgba(56, 189, 248, 0.2);
                border-top-color: #38bdf8;
                border-radius: 50%;
                animation: spin-glow 0.8s linear infinite;
                box-shadow: 0 0 14px rgba(56, 189, 248, 0.4);
            }

            @keyframes spin-glow {
                to { transform: rotate(360deg); }
            }

            .loading-text-glow {
                font-size: 10px;
                font-weight: 700;
                font-family: 'JetBrains Mono', monospace;
                letter-spacing: 0.08em;
                color: #38bdf8;
                text-transform: uppercase;
                margin-top: 10px;
            }

            .kpi-loader-spinner {
                display: inline-block;
                width: 1.1rem;
                height: 1.1rem;
                border: 2px solid rgba(56, 189, 248, 0.25);
                border-top-color: #38bdf8;
                border-radius: 50%;
                animation: spin-glow 0.8s linear infinite;
                vertical-align: middle;
            }
        </style>
    </head>

    <body>
        <div class="container-fluid py-3 px-4">

            <!-- Top Header Box -->
            <div class="wwtp-header-box d-flex align-items-center justify-content-between flex-wrap gap-3">
                <!-- Left Icon & Entity -->
                <div class="d-flex align-items-center gap-3">
                    <div class="header-logo-icon">
                        <i class="mdi mdi-package-variant-closed"></i>
                    </div>
                    <div>
                        <div class="header-title-main">WAREHOUSE SPAREPART (WSP)</div>
                        <div class="header-subtitle-cyan">INVENTORY & PURCHASE REQUISITION ANALYTICS</div>
                    </div>
                </div>

                <div class="text-center d-none d-lg-block">
                    <div class="header-title-main" style="letter-spacing: 1px;">WSP INTELLIGENCE DASHBOARD</div>
                    <div class="header-subtitle-cyan">STOCK ON HAND • PR FULFILLMENT • APPROVAL SLA</div>
                    <div class="header-tagline">&ldquo;Real-time Stock Reliability, Demand Visibility & Bottleneck
                        Tracking&rdquo;</div>
                </div>

                <!-- Right Controls: Period Selector & Clock Pill -->
                <div class="d-flex align-items-center gap-2">
                    <div class="clock-badge">
                        <i class="ri-time-line text-cyan"></i>
                        <span id="liveClockDate">--</span>
                        <span class="text-white" id="liveClockTime">00:00:00</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2" data-toggle="fullscreen"
                        title="Fullscreen">
                        <i class="bx bx-fullscreen align-middle fs-15"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info px-2" id="btnReloadData"
                        title="Reload Data">
                        <i class="ri-refresh-line align-middle fs-15"></i>
                    </button>
                </div>
            </div>

            <!-- Filter & Control Toolbar -->
            <div class="filter-toolbar-box">
                <div class="row g-2 align-items-center">
                    <!-- Period Selector -->
                    <div class="col-12 col-xl-5 d-flex align-items-center gap-1 flex-wrap">
                        <span class="text-muted fs-11 fw-bold text-uppercase me-1">PERIODE:</span>
                        <button type="button" class="period-btn" data-period="today">HARI INI</button>
                        <button type="button" class="period-btn" data-period="7days">7 HARI</button>
                        <button type="button" class="period-btn" data-period="month">BULAN INI</button>
                        <button type="button" class="period-btn active" data-period="30days">30 HARI</button>
                        <button type="button" class="period-btn" data-period="all">SEMUA</button>
                        <button type="button" class="period-btn" data-period="custom" id="pillCustom">KUSTOM</button>
                    </div>

                    <!-- Custom Range Inputs -->
                    <div class="col-12 col-md-5 col-xl-3 d-none align-items-center gap-1" id="customDateRangeBox">
                        <input type="date" class="form-control form-input-wwtp py-1" id="filterStartDate"
                            value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                        <span class="text-muted fs-11">S/D</span>
                        <input type="date" class="form-control form-input-wwtp py-1" id="filterEndDate"
                            value="{{ date('Y-m-d') }}">
                    </div>

                    <!-- Dimension Filters: Department, Jenis PR, Rak -->
                    <div
                        class="col-12 col-xl-7 d-flex align-items-center justify-content-xl-end gap-2 flex-wrap ms-auto">
                        <!-- Department -->
                        <div style="min-width: 140px;">
                            <select class="form-select form-select-wwtp" id="filterDepartment">
                                <option value="all">SEMUA DEPARTEMEN</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept }}">{{ strtoupper(str_replace('_', ' ', $dept)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Jenis PR -->
                        <div style="min-width: 120px;">
                            <select class="form-select form-select-wwtp" id="filterJenis">
                                <option value="all">SEMUA JENIS PR</option>
                                @foreach ($jenisList as $j)
                                    <option value="{{ $j }}">{{ strtoupper($j) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rak Storage -->
                        <div style="min-width: 130px;">
                            <select class="form-select form-select-wwtp" id="filterRak">
                                <option value="all">SEMUA LOKASI RAK</option>
                                @foreach ($raks as $rak)
                                    <option value="{{ $rak->id }}">
                                        {{ strtoupper($rak->detail_loc ?: $rak->s_loc) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" class="btn btn-sm btn-primary px-3 fw-bold fs-11" id="btnApplyFilter">
                            <i class="ri-filter-3-line align-middle me-1"></i> FILTER
                        </button>
                    </div>
                </div>
            </div>

            <!-- CLUSTER 1: EXECUTIVE KPI SUMMARY ROW -->
            <div class="row g-2 mb-3">
                <!-- 1. Total SOH Units -->
                <div class="col-6 col-md-4 col-xl">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="card-subtitle-text">TOTAL STOCK ON HAND</span>
                                <div class="card-header-icon green mb-0"><i class="mdi mdi-cube-outline"></i></div>
                            </div>
                            <div class="kpi-main-number" id="kpiTotalSohQty">0</div>
                            <div class="kpi-target-tag cyan">
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span id="kpiUnrestQty">0</span> UNREST
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Active Master SKUs -->
                <div class="col-6 col-md-4 col-xl">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="card-subtitle-text">ACTIVE SPAREPART SKUs</span>
                                <div class="card-header-icon mb-0"><i class="mdi mdi-barcode-scan"></i></div>
                            </div>
                            <div class="kpi-main-number" id="kpiTotalSkus">0</div>
                            <div class="kpi-target-tag green">
                                <i class="mdi mdi-shield-check-outline"></i>
                                <span id="kpiInStockRate">0%</span> IN-STOCK RATIO
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Stockout Warning Count -->
                <div class="col-6 col-md-4 col-xl">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="card-subtitle-text">ZERO-STOCK ALERT</span>
                                <div class="card-header-icon amber mb-0"><i class="mdi mdi-alert-circle-outline"></i>
                                </div>
                            </div>
                            <div class="kpi-main-number text-warning" id="kpiZeroStockCount">0</div>
                            <div class="kpi-target-tag amber">
                                <i class="mdi mdi-alert-outline"></i>
                                <span>STOCKOUT CRITICAL</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Total PR Documents -->
                <div class="col-6 col-md-4 col-xl">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="card-subtitle-text">PR DOKUMEN</span>
                                <div class="card-header-icon purple mb-0"><i
                                        class="mdi mdi-file-document-edit-outline"></i></div>
                            </div>
                            <div class="kpi-main-number text-white" id="kpiTotalPr">0</div>
                            <div class="kpi-target-tag green">
                                <i class="mdi mdi-check"></i>
                                <span id="kpiPrApproved">0</span> APPROVED / FINISHED
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. PR Fulfillment Rate -->
                <div class="col-6 col-md-4 col-xl">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="card-subtitle-text">PR APPROVAL RATE</span>
                                <div class="card-header-icon green mb-0"><i class="mdi mdi-percent-outline"></i></div>
                            </div>
                            <div class="kpi-main-number text-success" id="kpiApprovalRate">0%</div>
                            <div class="kpi-target-tag red">
                                <i class="mdi mdi-close-circle-outline"></i>
                                <span id="kpiPrRejected">0</span> REJECTED
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Pending Bottlenecks (>24h) -->
                <div class="col-6 col-md-4 col-xl">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="card-subtitle-text">PENDING &gt; 24 JAM</span>
                                <div class="card-header-icon red mb-0"><i class="mdi mdi-timer-alert-outline"></i>
                                </div>
                            </div>
                            <div class="kpi-main-number text-danger" id="kpiPendingBottlenecks">0</div>
                            <div class="kpi-target-tag red">
                                <span class="pulse-dot red me-1"></span>
                                <span id="kpiPrPending">0</span> TOTAL PENDING
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 7. Avg Approval SLA Lead Time -->
                <div class="col-6 col-md-4 col-xl">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="card-subtitle-text">RERATA SLA APPROVAL</span>
                                <div class="card-header-icon cyan mb-0"><i class="mdi mdi-clock-fast"></i></div>
                            </div>
                            <div class="kpi-main-number text-info" id="kpiOverallTat">0 Jam</div>
                            <div class="kpi-target-tag cyan">
                                <i class="mdi mdi-clipboard-text-clock-outline"></i>
                                <span id="kpiActiveReservations">0</span> RESERVASI AKTIF
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CLUSTER 2: STOCK ON HAND (SOH) INTELLIGENCE -->
            <div class="row g-2 mb-3">
                <!-- 2.1 SOH Allocation & Usability Donut -->
                <div class="col-12 col-lg-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="mdi mdi-chart-donut"></i></div>
                                <div>
                                    <h6 class="card-title-text">KOMPOSISI STOCK ON HAND</h6>
                                    <span class="card-subtitle-text">UNREST VS BLOCKED VS QI</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartSohComposition" style="min-height: 250px;"></div>
                            <div class="row g-2 mt-2 pt-2 border-top border-secondary border-opacity-10">
                                <div class="col-6">
                                    <div class="stat-badge-sub text-center">
                                        <small class="text-muted d-block fs-10 fw-bold">BLOCKED / QUARANTINE</small>
                                        <span class="mono fw-bold text-danger fs-13" id="badgeBlockedStock">0
                                            UNIT</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-badge-sub text-center">
                                        <small class="text-muted d-block fs-10 fw-bold">QUALITY INSPECTION</small>
                                        <span class="mono fw-bold text-warning fs-13" id="badgeQiStock">0 UNIT</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2.2 SOH Health & Availability Ratio -->
                <div class="col-12 col-lg-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon"><i class="mdi mdi-shield-check"></i></div>
                                <div>
                                    <h6 class="card-title-text">KETERSEDIAAN KATALOG SPAREPART</h6>
                                    <span class="card-subtitle-text">IN-STOCK VS ZERO-STOCK RATIO</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartStockHealth" style="min-height: 250px;"></div>
                            <div class="row g-2 mt-2 pt-2 border-top border-secondary border-opacity-10">
                                <div class="col-6">
                                    <div class="stat-badge-sub text-center">
                                        <small class="text-muted d-block fs-10 fw-bold">IN-STOCK READY</small>
                                        <span class="mono fw-bold text-success fs-13" id="badgeInStockSkus">0
                                            SKU</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-badge-sub text-center">
                                        <small class="text-muted d-block fs-10 fw-bold">OUT-OF-STOCK</small>
                                        <span class="mono fw-bold text-warning fs-13" id="badgeZeroStockSkus">0
                                            SKU</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2.3 SOH Rak / Storage Distribution -->
                <div class="col-12 col-lg-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon purple"><i class="mdi mdi-archive-outline"></i></div>
                                <div>
                                    <h6 class="card-title-text">DISTRIBUSI STOK PER LOKASI RAK</h6>
                                    <span class="card-subtitle-text">TOP RAK DENGAN VOLUME TERTINGGI</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartRakDistribution" style="min-height: 290px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2.4 Top Inventory Items & Critical Stock Watchlist -->
            <div class="row g-2 mb-3">
                <!-- Left: Top 10 High Inventory Spareparts -->
                <div class="col-12 col-lg-7">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="mdi mdi-format-list-numbered"></i></div>
                                <div>
                                    <h6 class="card-title-text">TOP 10 SPAREPART KUANTITAS STOK TERBESAR</h6>
                                    <span class="card-subtitle-text">POPULASI STOK ON HAND AKTIF</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body p-0">
                            <div class="table-responsive" style="max-height: 320px;">
                                <table class="wwtp-table">
                                    <thead>
                                        <tr>
                                            <th>MID / MAT CODE</th>
                                            <th>NAMA SPAREPART</th>
                                            <th class="text-center">UOM</th>
                                            <th class="text-end">UNREST</th>
                                            <th class="text-end">BLOCKED</th>
                                            <th class="text-end">TOTAL SOH</th>
                                        </tr>
                                    </thead>
                                    <tbody id="topStockTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">MEMUAT DATA STOK...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Critical / Zero-Stock Watchlist -->
                <div class="col-12 col-lg-5">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon amber"><i class="mdi mdi-alert-octagon-outline"></i>
                                </div>
                                <div>
                                    <h6 class="card-title-text">ZERO-STOCK WATCHLIST</h6>
                                    <span class="card-subtitle-text">ITEM KRITIS KOSONG (PERLU REPLENISHMENT)</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body p-0">
                            <div class="table-responsive" style="max-height: 320px;">
                                <table class="wwtp-table">
                                    <thead>
                                        <tr>
                                            <th>MID</th>
                                            <th>NAMA ITEM</th>
                                            <th class="text-center">STATUS</th>
                                            <th class="text-end">LAST UPDATE</th>
                                        </tr>
                                    </thead>
                                    <tbody id="zeroStockTableBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">MEMUAT DATA...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CLUSTER 3: PURCHASE REQUISITION (PR) INTELLIGENCE -->
            <div class="row g-2 mb-3">
                <!-- 3.1 PR Inflow Trend Over Time -->
                <div class="col-12 col-lg-8">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon cyan"><i class="mdi mdi-chart-timeline-variant"></i>
                                </div>
                                <div>
                                    <h6 class="card-title-text">TREN PENGAJUAN DOKUMEN PR</h6>
                                    <span class="card-subtitle-text">VOLUME PERMOHONAN: APPROVED VS PENDING VS
                                        REJECTED</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success bg-opacity-25 text-success mono fs-10">APPROVED / FINISHED</span>
                                <span class="badge bg-warning bg-opacity-25 text-warning mono fs-10">PENDING</span>
                                <span class="badge bg-danger bg-opacity-25 text-danger mono fs-10">REJECTED</span>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartPrTrend" style="min-height: 280px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 3.2 PR Breakdown by Department -->
                <div class="col-12 col-lg-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon purple"><i class="mdi mdi-domain"></i></div>
                                <div>
                                    <h6 class="card-title-text">PR PER DEPARTEMEN</h6>
                                    <span class="card-subtitle-text">PROPORSI KEBUTUHAN UNIT KERJA</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartPrDepartment" style="min-height: 280px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3.3 Top Requested Spareparts & Classification -->
            <div class="row g-2 mb-3">
                <!-- Top Requested Spareparts -->
                <div class="col-12 col-lg-7">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="mdi mdi-cart-arrow-down"></i></div>
                                <div>
                                    <h6 class="card-title-text">TOP 10 SPAREPART PALING SERING DIMINTA DALAM PR</h6>
                                    <span class="card-subtitle-text">DEMAND FREQUENCY &amp; REQUESTED QUANTITY</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body p-0">
                            <div class="table-responsive" style="max-height: 290px;">
                                <table class="wwtp-table">
                                    <thead>
                                        <tr>
                                            <th>MID</th>
                                            <th>NAMA SPAREPART</th>
                                            <th class="text-center">FREKUENSI PR</th>
                                            <th class="text-end">TOTAL QTY REQUESTED</th>
                                        </tr>
                                    </thead>
                                    <tbody id="topRequestedTableBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">MEMUAT ITEM PR...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PR Classification by Purpose & Reservations -->
                <div class="col-12 col-lg-5">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon amber"><i class="mdi mdi-shape-plus"></i></div>
                                <div>
                                    <h6 class="card-title-text">KLASIFIKASI &amp; STOCK RESERVATION</h6>
                                    <span class="card-subtitle-text">JENIS PERMOHONAN &amp; ALOKASI STOK</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="stat-badge-sub text-center">
                                        <small class="text-muted d-block fs-10 fw-bold">TOTAL RESERVASI</small>
                                        <span class="mono fw-bold text-white fs-13" id="badgeTotalRes">0</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-badge-sub text-center">
                                        <small class="text-muted d-block fs-10 fw-bold">ACTIVE LOCKED</small>
                                        <span class="mono fw-bold text-info fs-13" id="badgeActiveRes">0</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-badge-sub text-center">
                                        <small class="text-muted d-block fs-10 fw-bold">CONFIRMED / DONE</small>
                                        <span class="mono fw-bold text-success fs-13" id="badgeConfirmedRes">0</span>
                                    </div>
                                </div>
                            </div>
                            <div id="chartPrJenis" style="min-height: 180px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CLUSTER 4: APPROVAL WORKFLOW & BOTTLENECK ANALYSIS (LIKE VEHICLE STAGE CYCLE TIME) -->
            <div class="row g-2 mb-3">
                <!-- 4.1 Pending Bottlenecks per Role / Level -->
                <div class="col-12 col-lg-6">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon red"><i class="mdi mdi-filter-variant-remove"></i></div>
                                <div>
                                    <h6 class="card-title-text">BOTTLENECK TAHAPAN APPROVAL PR</h6>
                                    <span class="card-subtitle-text">ANTRIAN TERTUNDA DI TAHAP AKTIF SAAT INI</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartApprovalBottlenecks" style="min-height: 250px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 4.2 Turnaround Time (TAT) / Lead Time per Level -->
                <div class="col-12 col-lg-6">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon cyan"><i class="mdi mdi-clock-check-outline"></i></div>
                                <div>
                                    <h6 class="card-title-text">RERATA WAKTU RESPON (TAT) ANTAR LEVEL</h6>
                                    <span class="card-subtitle-text">SPEED OF ACTION LEVEL KE LEVEL (L1 &rarr; L2 &rarr; L3 &rarr; L4 &rarr; L5)</span>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartApprovalTat" style="min-height: 250px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4.3 Longest Pending PRs Attention List -->
            <div class="row g-2 mb-4">
                <div class="col-12">
                    <div class="wwtp-card">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon amber"><i class="mdi mdi-clock-alert-outline"></i></div>
                                <div>
                                    <h6 class="card-title-text">PRIORITY ATTENTION: DOKUMEN PR MENUNGGU PALING LAMA
                                    </h6>
                                    <span class="card-subtitle-text">AGING TRACKER &amp; ESCALATION (DURASI MENUNGGU DI LEVEL AKTIF)</span>
                                </div>
                            </div>
                            <div>
                                <span class="status-pill amber mono"><span class="pulse-dot red me-1"></span> ACTION
                                    REQUIRED</span>
                            </div>
                        </div>
                        <div class="wwtp-card-body p-0">
                            <div class="table-responsive">
                                <table class="wwtp-table">
                                    <thead>
                                        <tr>
                                            <th>NO DOKUMEN PR</th>
                                            <th>PEMOHON</th>
                                            <th>DEPARTEMEN</th>
                                            <th>KLASIFIKASI</th>
                                            <th>ITEM</th>
                                            <th>TANGGAL PENGAJUAN</th>
                                            <th class="text-center">MENUNGGU DI LEVEL AKTIF</th>
                                            <th>TAHAP PENDING SAAT INI</th>
                                        </tr>
                                    </thead>
                                    <tbody id="longestPendingTableBody">
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">MEMUAT DAFTAR
                                                ANTRIAN...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="{{ asset('material/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="{{ asset('material/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
        <script>
            if (typeof ApexCharts === 'undefined') {
                document.write('<script src="https://cdn.jsdelivr.net/npm/apexcharts"><\/script>');
            }
        </script>

        <script>
            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // Live Clock
                function updateClock() {
                    const now = new Date();
                    const day = String(now.getDate()).padStart(2, '0');
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const month = months[now.getMonth()];
                    const year = now.getFullYear();
                    $('#liveClockDate').text(`${day} ${month} ${year}`);
                    $('#liveClockTime').text(now.toLocaleTimeString('id-ID', {
                        hour12: false
                    }));
                }
                setInterval(updateClock, 1000);
                updateClock();

                // Fullscreen Toggle
                $('[data-toggle="fullscreen"]').on('click', function(e) {
                    e.preventDefault();
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    } else {
                        document.exitFullscreen().catch(() => {});
                    }
                });

                // Global Chart Handles
                let chartSohComposition = null;
                let chartStockHealth = null;
                let chartRakDistribution = null;
                let chartPrTrend = null;
                let chartPrDepartment = null;
                let chartPrJenis = null;
                let chartApprovalBottlenecks = null;
                let chartApprovalTat = null;

                // Period Selector
                $('.period-btn').on('click', function() {
                    $('.period-btn').removeClass('active');
                    $(this).addClass('active');

                    const period = $(this).data('period');
                    if (period === 'custom') {
                        $('#customDateRangeBox').removeClass('d-none').addClass('d-flex');
                    } else {
                        $('#customDateRangeBox').removeClass('d-flex').addClass('d-none');
                        loadDashboardData();
                    }
                });

                $('#btnApplyFilter').on('click', function() {
                    loadDashboardData();
                });

                $('#btnReloadData').on('click', function() {
                    loadDashboardData(true);
                });

                // XHR handles for lazy request cancellation
                let xhrKpi = null;
                let xhrSoh = null;
                let xhrPr = null;
                let xhrWorkflow = null;

                // Helper to get active filter parameters
                function getFilterParams() {
                    const activePeriod = $('.period-btn.active').data('period') || '30days';
                    const params = {
                        period: activePeriod,
                        department: $('#filterDepartment').val(),
                        jenis: $('#filterJenis').val(),
                        rak_id: $('#filterRak').val(),
                    };

                    if (activePeriod === 'custom') {
                        params.start_date = $('#filterStartDate').val();
                        params.end_date = $('#filterEndDate').val();
                    }

                    return params;
                }

                // Loader helpers for individual card overlays
                function showCardLoader($el, message) {
                    if (!$el || !$el.length) return;
                    $el.each(function() {
                        const $target = $(this);
                        const $card = $target.is('.wwtp-card') ? $target : $target.closest('.wwtp-card');
                        if (!$card.length) return;

                        let $overlay = $card.children('.card-loading-overlay');
                        if ($overlay.length) {
                            $overlay.removeClass('fade-out');
                            $overlay.find('.loading-text-glow').text(message || 'MEMUAT DATA...');
                        } else {
                            $card.append(`
                                <div class="card-loading-overlay">
                                    <div class="spinner-glow"></div>
                                    <div class="loading-text-glow">${message || 'MEMUAT DATA...'}</div>
                                </div>
                            `);
                        }
                    });
                }

                function hideCardLoader($el) {
                    if (!$el || !$el.length) return;
                    $el.each(function() {
                        const $target = $(this);
                        const $card = $target.is('.wwtp-card') ? $target : $target.closest('.wwtp-card');
                        if (!$card.length) return;

                        const $overlay = $card.children('.card-loading-overlay');
                        if ($overlay.length) {
                            $overlay.addClass('fade-out');
                            setTimeout(() => {
                                $overlay.remove();
                            }, 250);
                        }
                    });
                }

                // Cluster loader controls
                function showClusterLoader(cluster) {
                    if (cluster === 'kpi') {
                        $('#kpiTotalSohQty, #kpiTotalSkus, #kpiZeroStockCount, #kpiTotalPr, #kpiApprovalRate, #kpiPendingBottlenecks, #kpiOverallTat')
                            .html('<span class="kpi-loader-spinner"></span>');
                        $('#kpiUnrestQty, #kpiPrApproved, #kpiPrRejected, #kpiPrPending, #kpiActiveReservations').text('--');
                        $('#kpiInStockRate').text('--%');
                    } else if (cluster === 'soh') {
                        showCardLoader($('#chartSohComposition'), 'MEMUAT KOMPOSISI SOH...');
                        showCardLoader($('#chartStockHealth'), 'MEMUAT KATALOG & RATIO...');
                        showCardLoader($('#chartRakDistribution'), 'MEMUAT DISTRIBUSI RAK...');
                        showCardLoader($('#topStockTableBody'), 'MEMUAT TOP SPAREPART...');
                        showCardLoader($('#zeroStockTableBody'), 'MEMERIKSA ZERO-STOCK...');
                    } else if (cluster === 'pr') {
                        showCardLoader($('#chartPrTrend'), 'MEMUAT TREN PENGAJUAN PR...');
                        showCardLoader($('#chartPrDepartment'), 'MEMUAT PR PER DEPARTEMEN...');
                        showCardLoader($('#topRequestedTableBody'), 'MEMUAT PERMINTAAN SPAREPART...');
                        showCardLoader($('#chartPrJenis'), 'MEMUAT KLASIFIKASI & RESERVASI...');
                    } else if (cluster === 'workflow') {
                        showCardLoader($('#chartApprovalBottlenecks'), 'MENGANALISIS BOTTLENECK...');
                        showCardLoader($('#chartApprovalTat'), 'MENGHITUNG LEAD TIME TAT...');
                        showCardLoader($('#longestPendingTableBody'), 'MEMUAT DOKUMEN AGING...');
                    }
                }

                function hideClusterLoader(cluster) {
                    if (cluster === 'soh') {
                        hideCardLoader($('#chartSohComposition'));
                        hideCardLoader($('#chartStockHealth'));
                        hideCardLoader($('#chartRakDistribution'));
                        hideCardLoader($('#topStockTableBody'));
                        hideCardLoader($('#zeroStockTableBody'));
                    } else if (cluster === 'pr') {
                        hideCardLoader($('#chartPrTrend'));
                        hideCardLoader($('#chartPrDepartment'));
                        hideCardLoader($('#topRequestedTableBody'));
                        hideCardLoader($('#chartPrJenis'));
                    } else if (cluster === 'workflow') {
                        hideCardLoader($('#chartApprovalBottlenecks'));
                        hideCardLoader($('#chartApprovalTat'));
                        hideCardLoader($('#longestPendingTableBody'));
                    }
                }

                // Lazy asynchronous data fetchers per section
                function fetchKpiSection(params, onDone) {
                    showClusterLoader('kpi');
                    if (xhrKpi && xhrKpi.readyState !== 4) xhrKpi.abort();
                    xhrKpi = $.ajax({
                        url: "{{ route('dashboard.wsp.data') }}",
                        type: "GET",
                        data: Object.assign({}, params, { section: 'kpi' }),
                        dataType: "json",
                        success: function(res) {
                            if (res.success && res.kpi) {
                                renderKpiSection(res.kpi);
                            }
                        },
                        error: function(err) {
                            if (err.statusText !== 'abort') {
                                console.error('Failed to load WSP KPI section:', err);
                                $('#kpiTotalSohQty, #kpiTotalSkus, #kpiZeroStockCount, #kpiTotalPr, #kpiApprovalRate, #kpiPendingBottlenecks, #kpiOverallTat').text('-');
                            }
                        },
                        complete: function() {
                            if (typeof onDone === 'function') onDone();
                        }
                    });
                }

                function fetchSohSection(params, onDone) {
                    showClusterLoader('soh');
                    if (xhrSoh && xhrSoh.readyState !== 4) xhrSoh.abort();
                    xhrSoh = $.ajax({
                        url: "{{ route('dashboard.wsp.data') }}",
                        type: "GET",
                        data: Object.assign({}, params, { section: 'soh' }),
                        dataType: "json",
                        success: function(res) {
                            if (res.success && res.soh) {
                                renderSohSection(res.soh);
                            }
                        },
                        error: function(err) {
                            if (err.statusText !== 'abort') {
                                console.error('Failed to load WSP SOH section:', err);
                            }
                        },
                        complete: function() {
                            hideClusterLoader('soh');
                            if (typeof onDone === 'function') onDone();
                        }
                    });
                }

                function fetchPrSection(params, onDone) {
                    showClusterLoader('pr');
                    if (xhrPr && xhrPr.readyState !== 4) xhrPr.abort();
                    xhrPr = $.ajax({
                        url: "{{ route('dashboard.wsp.data') }}",
                        type: "GET",
                        data: Object.assign({}, params, { section: 'pr' }),
                        dataType: "json",
                        success: function(res) {
                            if (res.success && res.pr) {
                                renderPrSection(res.pr, res.reservations);
                            }
                        },
                        error: function(err) {
                            if (err.statusText !== 'abort') {
                                console.error('Failed to load WSP PR section:', err);
                            }
                        },
                        complete: function() {
                            hideClusterLoader('pr');
                            if (typeof onDone === 'function') onDone();
                        }
                    });
                }

                function fetchWorkflowSection(params, onDone) {
                    showClusterLoader('workflow');
                    if (xhrWorkflow && xhrWorkflow.readyState !== 4) xhrWorkflow.abort();
                    xhrWorkflow = $.ajax({
                        url: "{{ route('dashboard.wsp.data') }}",
                        type: "GET",
                        data: Object.assign({}, params, { section: 'workflow' }),
                        dataType: "json",
                        success: function(res) {
                            if (res.success && res.workflow) {
                                renderWorkflowSection(res.workflow);
                            }
                        },
                        error: function(err) {
                            if (err.statusText !== 'abort') {
                                console.error('Failed to load WSP Workflow section:', err);
                            }
                        },
                        complete: function() {
                            hideClusterLoader('workflow');
                            if (typeof onDone === 'function') onDone();
                        }
                    });
                }

                // Main Loader: loads sections concurrently so whichever finishes first displays immediately
                function loadDashboardData(isReload = false) {
                    const params = getFilterParams();

                    if (isReload && window.toastr) {
                        toastr.info('Memperbarui data analitik WSP...', 'Refresh');
                    }

                    $('#btnReloadData i').addClass('ri-spin');

                    // Reset table placeholders while loading
                    $('#topStockTableBody').html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="ri-loader-4-line ri-spin fs-16 text-cyan me-1"></i> Memuat data stok sparepart...</td></tr>');
                    $('#zeroStockTableBody').html('<tr><td colspan="4" class="text-center text-muted py-4"><i class="ri-loader-4-line ri-spin fs-16 text-warning me-1"></i> Memeriksa zero-stock watchlist...</td></tr>');
                    $('#topRequestedTableBody').html('<tr><td colspan="4" class="text-center text-muted py-4"><i class="ri-loader-4-line ri-spin fs-16 text-cyan me-1"></i> Memuat item permintaan PR...</td></tr>');
                    $('#longestPendingTableBody').html('<tr><td colspan="8" class="text-center text-muted py-4"><i class="ri-loader-4-line ri-spin fs-16 text-danger me-1"></i> Memuat antrian dokumen PR...</td></tr>');

                    let completedCount = 0;
                    const onSectionDone = () => {
                        completedCount++;
                        if (completedCount === 4) {
                            $('#btnReloadData i').removeClass('ri-spin');
                            if (isReload && window.toastr) {
                                toastr.success('Semua klaster data WSP selesai dimutakhirkan!', 'Selesai');
                            }
                        }
                    };

                    // Fire all 4 section queries in parallel - completed cards appear immediately without waiting
                    fetchKpiSection(params, onSectionDone);
                    fetchSohSection(params, onSectionDone);
                    fetchPrSection(params, onSectionDone);
                    fetchWorkflowSection(params, onSectionDone);
                }

                // 1. KPI Rendering
                function renderKpiSection(kpi) {
                    $('#kpiTotalSohQty').text(Number(kpi.total_soh_qty || 0).toLocaleString('id-ID'));
                    $('#kpiUnrestQty').text(Number(kpi.total_unrest_qty || 0).toLocaleString('id-ID'));
                    $('#kpiTotalSkus').text(Number(kpi.total_skus || 0).toLocaleString('id-ID'));
                    $('#kpiInStockRate').text((kpi.in_stock_rate || 0) + '%');
                    $('#kpiZeroStockCount').text(Number(kpi.zero_stock_count || 0).toLocaleString('id-ID'));
                    $('#kpiTotalPr').text(Number(kpi.total_pr_count || 0).toLocaleString('id-ID'));
                    $('#kpiPrApproved').text(Number(kpi.pr_approved_count || 0).toLocaleString('id-ID'));
                    $('#kpiApprovalRate').text((kpi.pr_approval_rate || 0) + '%');
                    $('#kpiPrRejected').text(Number(kpi.pr_rejected_count || 0).toLocaleString('id-ID'));
                    $('#kpiPendingBottlenecks').text(Number(kpi.pending_bottlenecks || 0).toLocaleString('id-ID'));
                    $('#kpiPrPending').text(Number(kpi.pr_pending_count || 0).toLocaleString('id-ID'));
                    $('#kpiOverallTat').text(kpi.overall_tat_formatted || '0 Jam');
                    $('#kpiActiveReservations').text(Number(kpi.active_reservations || 0).toLocaleString('id-ID'));
                }

                // 2. SOH Intelligence Rendering
                function renderSohSection(soh) {
                    const comp = soh.composition || {};
                    $('#badgeBlockedStock').text(Number(comp.blocked || 0).toLocaleString('id-ID') + ' UNIT');
                    $('#badgeQiStock').text(Number(comp.qual_insp || 0).toLocaleString('id-ID') + ' UNIT');

                    // Donut 1: SOH Usability Composition
                    const optComp = {
                        series: [comp.unrestricted || 0, comp.qual_insp || 0, comp.blocked || 0, comp.transf || 0],
                        labels: ['Unrest', 'QI', 'Blocked', 'Transf'],
                        chart: {
                            type: 'donut',
                            height: 250,
                            background: 'transparent'
                        },
                        colors: ['#10b981', '#f59e0b', '#ef4444', '#38bdf8'],
                        stroke: {
                            colors: ['#0b1426'],
                            width: 2
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                colors: '#94a3b8'
                            },
                            fontSize: '11px',
                            fontFamily: 'Outfit'
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: val => Number(val).toLocaleString('id-ID') + ' Unit'
                            }
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '68%',
                                    labels: {
                                        show: true,
                                        name: {
                                            color: '#94a3b8',
                                            fontSize: '11px'
                                        },
                                        value: {
                                            color: '#ffffff',
                                            fontSize: '16px',
                                            fontFamily: 'JetBrains Mono',
                                            fontWeight: 800,
                                            formatter: val => Number(val).toLocaleString('id-ID')
                                        },
                                        total: {
                                            show: true,
                                            label: 'TOTAL SOH',
                                            color: '#38bdf8',
                                            fontSize: '10px',
                                            fontWeight: 700,
                                            formatter: w => {
                                                const tot = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                return Number(tot).toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    };

                    if (chartSohComposition) chartSohComposition.destroy();
                    chartSohComposition = new ApexCharts(document.querySelector("#chartSohComposition"), optComp);
                    chartSohComposition.render();

                    // Donut 2: In-Stock vs Zero-Stock
                    const stats = soh.stats || {};
                    const inStock = Number(stats.in_stock || 0);
                    const zeroStock = Number(stats.zero_stock || 0);
                    const inStockRateText = (stats.in_stock_rate !== undefined) ? stats.in_stock_rate + '%' : '0%';

                    $('#badgeInStockSkus').text(inStock.toLocaleString('id-ID') + ' SKU');
                    $('#badgeZeroStockSkus').text(zeroStock.toLocaleString('id-ID') + ' SKU');

                    const optHealth = {
                        series: [inStock, zeroStock],
                        labels: ['Ready In-Stock', 'Zero-Stock Alert'],
                        chart: {
                            type: 'donut',
                            height: 250,
                            background: 'transparent'
                        },
                        colors: ['#3b82f6', '#f59e0b'],
                        stroke: {
                            colors: ['#0b1426'],
                            width: 2
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                colors: '#94a3b8'
                            },
                            fontSize: '11px',
                            fontFamily: 'Outfit'
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: val => Number(val).toLocaleString('id-ID') + ' SKU'
                            }
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '68%',
                                    labels: {
                                        show: true,
                                        name: {
                                            color: '#94a3b8',
                                            fontSize: '11px'
                                        },
                                        value: {
                                            color: '#ffffff',
                                            fontSize: '16px',
                                            fontFamily: 'JetBrains Mono',
                                            fontWeight: 800,
                                            formatter: val => Number(val).toLocaleString('id-ID') + ' SKU'
                                        },
                                        total: {
                                            show: true,
                                            label: 'READY RATIO',
                                            color: '#10b981',
                                            fontSize: '10px',
                                            formatter: () => inStockRateText
                                        }
                                    }
                                }
                            }
                        }
                    };

                    if (chartStockHealth) chartStockHealth.destroy();
                    chartStockHealth = new ApexCharts(document.querySelector("#chartStockHealth"), optHealth);
                    chartStockHealth.render();

                    // Bar: SOH per Rak
                    const rakData = soh.rak_distribution || [];
                    const rakCategories = rakData.map(r => (r.detail_loc || r.s_loc || 'RAK').toUpperCase());
                    const rakSeries = rakData.map(r => Number(r.total_qty || 0));

                    const optRak = {
                        series: [{
                            name: 'TOTAL UNIT SOH',
                            data: rakSeries
                        }],
                        chart: {
                            type: 'bar',
                            height: 290,
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 4,
                                barHeight: '52%',
                                distributed: true
                            }
                        },
                        colors: ['#8b5cf6', '#3b82f6', '#0ea5e9', '#06b6d4', '#10b981', '#f59e0b', '#ec4899',
                            '#6366f1'
                        ],
                        dataLabels: {
                            enabled: true,
                            style: {
                                colors: ['#ffffff'],
                                fontFamily: 'JetBrains Mono',
                                fontSize: '10px',
                                fontWeight: 700
                            },
                            formatter: val => Number(val).toLocaleString('id-ID')
                        },
                        xaxis: {
                            categories: rakCategories,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '10px',
                                    fontFamily: 'JetBrains Mono'
                                },
                                formatter: val => Number(val).toLocaleString('id-ID')
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#cbd5e1',
                                    fontSize: '10px',
                                    fontWeight: 700
                                },
                                maxWidth: 120
                            }
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: val => Number(val).toLocaleString('id-ID') + ' Unit'
                            }
                        },
                        legend: {
                            show: false
                        }
                    };

                    if (chartRakDistribution) chartRakDistribution.destroy();
                    chartRakDistribution = new ApexCharts(document.querySelector("#chartRakDistribution"), optRak);
                    chartRakDistribution.render();

                    // Top Stock Table
                    const tbodyTop = $('#topStockTableBody');
                    tbodyTop.empty();
                    if (!soh.top_items || soh.top_items.length === 0) {
                        tbodyTop.append(
                            '<tr><td colspan="6" class="text-center text-muted py-4">TIDAK ADA DATA STOK</td></tr>');
                    } else {
                        soh.top_items.forEach(item => {
                            tbodyTop.append(`
                            <tr>
                                <td><span class="mono fw-bold text-cyan">${item.mid_barang || '-'}</span></td>
                                <td class="fw-semibold text-white fs-11">${item.nama_barang || '-'}</td>
                                <td class="text-center"><span class="badge bg-dark text-muted mono">${item.uom || 'PCS'}</span></td>
                                <td class="text-end mono fw-bold text-success">${Number(item.total_unrest || 0).toLocaleString('id-ID')}</td>
                                <td class="text-end mono fw-bold text-danger">${Number(item.total_blocked || 0).toLocaleString('id-ID')}</td>
                                <td class="text-end mono fw-bold text-white fs-12">${Number(item.total_qty || 0).toLocaleString('id-ID')}</td>
                            </tr>
                        `);
                        });
                    }

                    // Zero Stock Table
                    const tbodyZero = $('#zeroStockTableBody');
                    tbodyZero.empty();
                    if (!soh.zero_stock_items || soh.zero_stock_items.length === 0) {
                        tbodyZero.append(
                            '<tr><td colspan="4" class="text-center text-muted py-4">SEMUA ITEM MEMILIKI STOK</td></tr>'
                        );
                    } else {
                        soh.zero_stock_items.forEach(item => {
                            tbodyZero.append(`
                            <tr>
                                <td><span class="mono fw-bold text-warning">${item.mid_barang || '-'}</span></td>
                                <td class="text-white fs-11">${item.nama_barang || '-'}</td>
                                <td class="text-center"><span class="status-pill red mono">ZERO SOH</span></td>
                                <td class="text-end mono text-muted fs-10">${item.last_update ? item.last_update.split(' ')[0] : '-'}</td>
                            </tr>
                        `);
                        });
                    }
                }

                // 3. PR Intelligence Rendering
                function renderPrSection(pr, reservations = null) {
                    if (reservations) {
                        renderReservations(reservations);
                    }
                    const trend = pr.trend || {};
                    const optTrend = {
                        series: [{
                                name: 'APPROVED / FINISHED',
                                data: trend.approved || []
                            },
                            {
                                name: 'PENDING',
                                data: trend.pending || []
                            },
                            {
                                name: 'REJECTED',
                                data: trend.rejected || []
                            }
                        ],
                        chart: {
                            type: 'bar',
                            height: 280,
                            stacked: true,
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#10b981', '#f59e0b', '#ef4444'],
                        plotOptions: {
                            bar: {
                                borderRadius: 3,
                                columnWidth: '45%'
                            }
                        },
                        xaxis: {
                            categories: trend.categories || [],
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '10px',
                                    fontFamily: 'JetBrains Mono'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '10px',
                                    fontFamily: 'JetBrains Mono'
                                }
                            }
                        },
                        legend: {
                            show: false
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: val => val + ' Dokumen'
                            }
                        }
                    };

                    if (chartPrTrend) chartPrTrend.destroy();
                    chartPrTrend = new ApexCharts(document.querySelector("#chartPrTrend"), optTrend);
                    chartPrTrend.render();

                    // PR by Department Donut
                    const depts = pr.by_department || [];
                    const deptSeries = depts.map(d => d.count);
                    const deptLabels = depts.map(d => d.department);

                    const optDept = {
                        series: deptSeries.length ? deptSeries : [1],
                        labels: deptLabels.length ? deptLabels : ['Tidak Ada Data'],
                        chart: {
                            type: 'donut',
                            height: 280,
                            background: 'transparent'
                        },
                        colors: ['#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ec4899'],
                        stroke: {
                            colors: ['#0b1426'],
                            width: 2
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                colors: '#94a3b8'
                            },
                            fontSize: '10px',
                            fontFamily: 'Outfit'
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: val => val + ' PR'
                            }
                        }
                    };

                    if (chartPrDepartment) chartPrDepartment.destroy();
                    chartPrDepartment = new ApexCharts(document.querySelector("#chartPrDepartment"), optDept);
                    chartPrDepartment.render();

                    // Top Requested Items Table
                    const tbodyReq = $('#topRequestedTableBody');
                    tbodyReq.empty();
                    if (!pr.top_requested_items || pr.top_requested_items.length === 0) {
                        tbodyReq.append(
                            '<tr><td colspan="4" class="text-center text-muted py-4">BELUM ADA DATA PERMOHONAN ITEM</td></tr>'
                        );
                    } else {
                        pr.top_requested_items.forEach(item => {
                            tbodyReq.append(`
                            <tr>
                                <td><span class="mono fw-bold text-cyan">${item.mid_code}</span></td>
                                <td class="fw-semibold text-white fs-11">${item.item_name}</td>
                                <td class="text-center"><span class="badge bg-primary bg-opacity-25 text-info mono">${item.request_freq}x Diajukan</span></td>
                                <td class="text-end mono fw-bold text-success fs-12">${Number(item.total_qty || 0).toLocaleString('id-ID')}</td>
                            </tr>
                        `);
                        });
                    }

                    // PR Jenis Bar
                    const jenisList = pr.by_jenis || [];
                    const jenisCategories = jenisList.map(j => j.jenis);
                    const jenisValues = jenisList.map(j => j.count);

                    const optJenis = {
                        series: [{
                            name: 'TOTAL PR',
                            data: jenisValues
                        }],
                        chart: {
                            type: 'bar',
                            height: 180,
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#38bdf8'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                horizontal: true,
                                barHeight: '40%'
                            }
                        },
                        xaxis: {
                            categories: jenisCategories,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '9.5px',
                                    fontFamily: 'JetBrains Mono'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#cbd5e1',
                                    fontSize: '10px',
                                    fontWeight: 700
                                }
                            }
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: val => val + ' PR'
                            }
                        }
                    };

                    if (chartPrJenis) chartPrJenis.destroy();
                    chartPrJenis = new ApexCharts(document.querySelector("#chartPrJenis"), optJenis);
                    chartPrJenis.render();
                }

                // 4. Workflow & Bottlenecks Rendering
                function renderWorkflowSection(wf) {
                    const bottlenecks = wf.bottlenecks || [];
                    const bCategories = bottlenecks.map(b => b.role);
                    const bSeries = bottlenecks.map(b => b.pending_count);

                    const optB = {
                        series: [{
                            name: 'ANTRIAN PENDING',
                            data: bSeries
                        }],
                        chart: {
                            type: 'bar',
                            height: 250,
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 4,
                                barHeight: '48%',
                                distributed: true
                            }
                        },
                        colors: ['#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#06b6d4'],
                        dataLabels: {
                            enabled: true,
                            style: {
                                colors: ['#ffffff'],
                                fontFamily: 'JetBrains Mono',
                                fontSize: '11px',
                                fontWeight: 800
                            },
                            formatter: val => val + ' PR'
                        },
                        xaxis: {
                            categories: bCategories,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '10px',
                                    fontFamily: 'JetBrains Mono'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#cbd5e1',
                                    fontSize: '10.5px',
                                    fontWeight: 700
                                }
                            }
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: val => val + ' Dokumen PR Tertahan'
                            }
                        },
                        legend: {
                            show: false
                        }
                    };

                    if (chartApprovalBottlenecks) chartApprovalBottlenecks.destroy();
                    chartApprovalBottlenecks = new ApexCharts(document.querySelector("#chartApprovalBottlenecks"),
                        optB);
                    chartApprovalBottlenecks.render();

                    // TAT Chart
                    const tatData = wf.stage_tat || [];
                    const tatCategories = tatData.map(t => t.role);
                    const tatSeries = tatData.map(t => t.avg_hours);

                    const optTat = {
                        series: [{
                            name: 'RERATA DURASI (JAM)',
                            data: tatSeries
                        }],
                        chart: {
                            type: 'bar',
                            height: 250,
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 4,
                                barHeight: '48%',
                                distributed: true
                            }
                        },
                        colors: ['#10b981', '#06b6d4', '#3b82f6', '#f59e0b', '#8b5cf6'],
                        dataLabels: {
                            enabled: true,
                            style: {
                                colors: ['#ffffff'],
                                fontFamily: 'JetBrains Mono',
                                fontSize: '10.5px',
                                fontWeight: 700
                            },
                            formatter: (val, opt) => tatData[opt.dataPointIndex] ? tatData[opt.dataPointIndex]
                                .formatted : val + ' jam'
                        },
                        xaxis: {
                            categories: tatCategories,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '10px',
                                    fontFamily: 'JetBrains Mono'
                                },
                                formatter: val => val + 'j'
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#cbd5e1',
                                    fontSize: '10.5px',
                                    fontWeight: 700
                                }
                            }
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: (val, opt) => {
                                    const item = tatData[opt.dataPointIndex];
                                    return `${item.formatted} (Sampel: ${item.sample_count} PR)`;
                                }
                            }
                        },
                        legend: {
                            show: false
                        }
                    };

                    if (chartApprovalTat) chartApprovalTat.destroy();
                    chartApprovalTat = new ApexCharts(document.querySelector("#chartApprovalTat"), optTat);
                    chartApprovalTat.render();

                    // Longest Pending PRs Table
                    const tbodyPend = $('#longestPendingTableBody');
                    tbodyPend.empty();
                    if (!wf.longest_pending_prs || wf.longest_pending_prs.length === 0) {
                        tbodyPend.append(
                            '<tr><td colspan="8" class="text-center text-success py-4">TIDAK ADA PR YANG TERTUNDA</td></tr>'
                        );
                    } else {
                        wf.longest_pending_prs.forEach(pr => {
                            const isSevere = pr.aging_hours >= 24;
                            tbodyPend.append(`
                            <tr>
                                <td><span class="mono fw-bold text-white">${pr.no_doc}</span></td>
                                <td><span class="fw-semibold text-info fs-11">${pr.requested_by}</span></td>
                                <td><span class="status-pill gray">${pr.department}</span></td>
                                <td><span class="badge bg-dark text-muted mono">${pr.jenis}</span></td>
                                <td><span class="mono fw-bold">${pr.item_count} Item</span></td>
                                <td class="mono fs-10 text-muted">${pr.created_at}</td>
                                <td class="text-center">
                                    <span class="status-pill ${isSevere ? 'red' : 'amber'} mono" title="Total usia PR sejak diajukan: ${pr.total_age_formatted}">
                                        <i class="mdi ${isSevere ? 'mdi-alert' : 'mdi-clock'} me-0.5"></i> ${pr.aging_formatted}
                                    </span>
                                    <small class="d-block text-muted mono mt-1" style="font-size: 8.5px;">TOTAL: ${pr.total_age_formatted}</small>
                                </td>
                                <td><span class="status-pill cyan fw-bold">${pr.current_role}</span></td>
                            </tr>
                        `);
                        });
                    }
                }

                // 5. Reservations Section
                function renderReservations(res) {
                    $('#badgeTotalRes').text(Number(res.total || 0).toLocaleString('id-ID'));
                    $('#badgeActiveRes').text(Number(res.active || 0).toLocaleString('id-ID'));
                    $('#badgeConfirmedRes').text(Number(res.confirmed || 0).toLocaleString('id-ID'));
                }

                // Initial Load
                loadDashboardData();
            });
        </script>
    </body>

</html>
