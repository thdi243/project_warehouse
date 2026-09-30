<!DOCTYPE html>
<html lang="id" data-layout-mode="dark">

    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>VEHICLE ANALYTICS</title>

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

            /* Top Header Container (Matching WWTP Dashboard) */
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
                transition: border-color 0.2s ease;
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
                font-size: 24px;
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
                border-bottom: 1px solid rgba(255, 255, 255, 0.03);
                vertical-align: middle;
            }

            .wwtp-table tbody tr:hover td {
                background: rgba(56, 189, 248, 0.04);
            }

            .plate-box {
                font-family: 'JetBrains Mono', monospace;
                font-size: 10.5px;
                font-weight: 700;
                background: #050914;
                color: #38bdf8;
                border: 1px solid rgba(56, 189, 248, 0.3);
                padding: 2px 7px;
                border-radius: 4px;
                display: inline-block;
            }

            .status-pill {
                font-size: 9.5px;
                font-weight: 700;
                padding: 2px 7px;
                border-radius: 4px;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .status-pill.green {
                background: rgba(16, 185, 129, 0.15);
                color: #34d399;
            }

            .status-pill.amber {
                background: rgba(245, 158, 11, 0.15);
                color: #fbbf24;
            }

            .status-pill.red {
                background: rgba(239, 68, 68, 0.15);
                color: #f87171;
            }

            .status-pill.gray {
                background: rgba(148, 163, 184, 0.15);
                color: #94a3b8;
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
        </style>
    </head>

    <body>
        <div class="container-fluid py-3 px-4">

            <!-- Top Header Box (Exact Style to WWTP Dashboard) -->
            <div class="wwtp-header-box d-flex align-items-center justify-content-between flex-wrap gap-3">
                <!-- Left Icon & Entity -->
                <div class="d-flex align-items-center gap-3">
                    <div class="header-logo-icon">
                        <i class="ri-truck-line"></i>
                    </div>
                    <div>
                        <div class="header-title-main">VEHICLE MONITORING</div>
                        <div class="header-subtitle-cyan">LOGISTICS & SUPPLY CHAIN INTELLIGENCE</div>
                    </div>
                </div>

                <!-- Center Title Banner -->
                <div class="text-center d-none d-lg-block">
                    <div class="header-title-main" style="letter-spacing: 1px;">WCO &ndash; VEHICLE FLOW DASHBOARD</div>
                    <div class="header-subtitle-cyan">WORLD CLASS OPERATIONAL &ndash; PILLAR LOGISTICS</div>
                    <div class="header-tagline">&ldquo;Safe Traffic, Compliant Process, Zero Bottleneck&rdquo;</div>
                </div>

                <!-- Right Controls: Period Selector & Clock Pill -->
                <div class="d-flex align-items-center gap-2">
                    <div class="clock-badge">
                        <i class="ri-time-line text-cyan"></i>
                        <span id="liveClockDate">30 Sep 2026</span>
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

                    <!-- Dimension Filters: Area, Jenis, Vendor, Material, SLA -->
                    <div
                        class="col-12 col-xl-7 d-flex align-items-center justify-content-xl-end gap-2 flex-wrap ms-auto">
                        <!-- Area Target -->
                        <div style="min-width: 110px;">
                            <select class="form-select form-select-wwtp" id="filterArea">
                                <option value="all">SEMUA AREA</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ strtoupper($loc->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Jenis Operasi -->
                        <div style="min-width: 105px;">
                            <select class="form-select form-select-wwtp" id="filterJenis">
                                <option value="all">SEMUA JENIS</option>
                                <option value="bongkaran">BONGKARAN</option>
                                <option value="curah">CURAH</option>
                                <option value="slipsheet">SLIPSHEET</option>
                                <option value="retur">RETUR</option>
                            </select>
                        </div>

                        <!-- Vendor -->
                        <div style="min-width: 125px; max-width: 165px;">
                            <select class="form-select form-select-wwtp" id="filterVendor">
                                <option value="all">SEMUA VENDOR</option>
                                @foreach ($vendors as $vnd)
                                    <option value="{{ $vnd }}">{{ strtoupper($vnd) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Material -->
                        <div style="min-width: 115px; max-width: 155px;">
                            <select class="form-select form-select-wwtp" id="filterItem">
                                <option value="all">SEMUA MATERIAL</option>
                                @foreach ($items as $it)
                                    <option value="{{ $it->id }}">{{ strtoupper($it->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- SLA Threshold -->
                        <div class="d-flex align-items-center gap-1 bg-dark px-2 py-1 rounded"
                            style="border: 1px solid rgba(255,255,255,0.08);">
                            <span class="text-muted fs-10 fw-bold">SLA:</span>
                            <input type="number" id="filterSlaLimit"
                                class="form-control form-input-wwtp py-0 px-1 text-center fw-bold text-warning"
                                style="width: 48px; height: 24px;" value="120" min="15" max="1440"
                                step="15">
                            <span class="text-muted fs-10">M</span>
                        </div>

                        <button class="btn btn-sm btn-primary px-3 fw-bold fs-11 text-uppercase" id="btnApplyFilter">
                            TERAPKAN
                        </button>
                    </div>
                </div>
            </div>

            <!-- Top KPI Cards Row (Matching WWTP Layout) -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-6 g-2 mb-3">
                <!-- 1. Total Volume -->
                <div class="col">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header py-2">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon"><i class="ri-truck-line"></i></div>
                                <div>
                                    <div class="card-title-text">TOTAL ARMADA</div>
                                    <div class="card-subtitle-text">VOLUME KEDATANGAN</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body py-2">
                            <div class="kpi-main-number text-white" id="kpiTotalVehicles">0</div>
                            <div class="kpi-target-tag cyan">
                                <i class="ri-check-line"></i> TRUK TERLAYANI
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Avg Turnaround Time -->
                <div class="col">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header py-2">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="ri-time-line"></i></div>
                                <div>
                                    <div class="card-title-text">RERATA TAT</div>
                                    <div class="card-subtitle-text">CYCLE TIME TOTAL</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body py-2">
                            <div class="kpi-main-number text-white" id="kpiAvgTAT">--</div>
                            <div class="kpi-target-tag green">
                                <i class="ri-arrow-down-line"></i> TARGET &le; <span class="sla-num">120</span> MNT
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Avg Daily Check-in -->
                <div class="col">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header py-2">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon"><i class="ri-calendar-check-line"></i></div>
                                <div>
                                    <div class="card-title-text">RERATA CHECK-IN</div>
                                    <div class="card-subtitle-text">KEDATANGAN / HARI</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body py-2">
                            <div class="kpi-main-number text-white" id="kpiAvgDailyCheckin">0</div>
                            <div class="kpi-target-tag cyan">
                                <i class="ri-pulse-line"></i> ARMADA / HARI
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Over SLA Count -->
                <div class="col">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header py-2">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon red"><i class="ri-alarm-warning-line"></i></div>
                                <div>
                                    <div class="card-title-text">MELEBIHI SLA</div>
                                    <div class="card-subtitle-text">BOTTLENECK ARMADA</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body py-2">
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="kpi-main-number text-white" id="kpiOverSlaCount">0</span>
                                <span class="status-pill red mono" id="kpiOverSlaRate">0%</span>
                            </div>
                            <div class="kpi-target-tag red">
                                <i class="ri-error-warning-line"></i> &gt; <span class="sla-num">120</span> MENIT
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Total Tonnage -->
                <div class="col">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header py-2">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon purple"><i class="ri-scales-3-line"></i></div>
                                <div>
                                    <div class="card-title-text">TOTAL TONASE</div>
                                    <div class="card-subtitle-text">VOLUME SPB</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body py-2">
                            <div class="kpi-main-number text-white" id="kpiTotalTonnage">0</div>
                            <div class="kpi-target-tag cyan">
                                <i class="ri-archive-line"></i> TONASE TERLAYANI
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Primary Bottleneck Stage -->
                <div class="col">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header py-2">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon amber"><i class="ri-fire-line"></i></div>
                                <div>
                                    <div class="card-title-text">BOTTLENECK UTAMA</div>
                                    <div class="card-subtitle-text">SUMBER KELAMBATAN</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body py-2">
                            <div class="fs-13 fw-bold text-white text-truncate mt-1" id="kpiPrimaryBottleneck">N/A
                            </div>
                            <div class="kpi-target-tag amber">
                                <i class="ri-alert-line"></i> EVALUASI OPERASIONAL
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 1: Bottleneck Tahapan, Daily Checkin & Rata-rata, Durasi Bongkar Muat Riil (col-xl-4 each) -->
            <div class="row g-2 mb-3">
                <!-- 1.1 Stage Bottleneck Dwell Time -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon amber"><i class="ri-timer-line"></i></div>
                                <div>
                                    <div class="card-title-text">DURASI TAHAPAN OPERASIONAL</div>
                                    <div class="card-subtitle-text">BOTTLENECK PER STAGE (POS 1 &rarr; CHECK-OUT)</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartStageBreakdown" style="min-height: 250px;"></div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle fs-11 text-muted"
                                id="stageInsightText">
                                <span>Bottleneck dihitung per sekuens alur kendaraan</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1.2 Daily Check-in & Rata-rata Chart -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon"><i class="ri-bar-chart-grouped-line"></i></div>
                                <div>
                                    <div class="card-title-text">TREN CHECK-IN HARIAN & RATA-RATA</div>
                                    <div class="card-subtitle-text">VOLUME KEDATANGAN VS GARIS RERATA</div>
                                </div>
                            </div>
                            <div>
                                <span class="status-pill cyan mono" id="avgDailyCheckinBadge">RERATA: 0 / HARI</span>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartDailyCheckin" style="min-height: 250px;"></div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle fs-11 text-muted">
                                <span>Total Masuk: <strong class="text-white mono" id="totalCheckinsBadge">0</strong> Truk</span>
                                <span class="mono text-muted fs-10" id="periodDaysLabel"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1.3 Durasi Bongkar / Muat Riil (Hanya yang memiliki time loading) -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="ri-truck-fill"></i></div>
                                <div>
                                    <div class="card-title-text">DURASI BONGKAR / MUAT RIIL</div>
                                    <div class="card-subtitle-text">RERATA WAKTU DENGAN TIMESTAMP AKTIF</div>
                                </div>
                            </div>
                            <div>
                                <span class="status-pill green mono" id="overallAvgLoadingBadge">RERATA: 0 mnt</span>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartLoadingBayTime" style="min-height: 250px;"></div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle fs-11 text-muted">
                                <span>Sampel Riil: <strong class="text-white mono" id="totalLoadingSamplesBadge">0</strong> Truk</span>
                                <span class="fs-10 text-muted">Filter start &amp; finish loading</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Mutu QC (No HOLD), Top 5 Reject Vendors, Frequent Trucks (col-3, col-5, col-4) -->
            <div class="row g-2 mb-3">
                <!-- 2.1 QC Donut (PASS, REJECT, WAITING, NOT REQUIRED - NO HOLD) -->
                <div class="col-12 col-md-5 col-xl-3">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="ri-shield-check-line"></i></div>
                                <div>
                                    <div class="card-title-text">TINGKAT KELULUSAN QC</div>
                                    <div class="card-subtitle-text">STATUS SAMPLING LABORATORIUM</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartQcDonut" style="min-height: 180px;"></div>
                            <div class="d-flex justify-content-between text-center pt-2 border-top border-secondary-subtle fs-10">
                                <div>
                                    <span class="text-muted d-block fw-bold">PASS</span>
                                    <strong class="text-success mono fs-12" id="qcPassCount">0</strong>
                                </div>
                                <div>
                                    <span class="text-muted d-block fw-bold">REJECT</span>
                                    <strong class="text-danger mono fs-12" id="qcRejectCount">0</strong>
                                </div>
                                <div>
                                    <span class="text-muted d-block fw-bold">WAITING</span>
                                    <strong class="text-warning mono fs-12" id="qcWaitingCount">0</strong>
                                </div>
                                <div>
                                    <span class="text-muted d-block fw-bold">NON-QC</span>
                                    <strong class="text-muted mono fs-12" id="qcNotReqCount">0</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2.2 Top 5 Reject Vendors -->
                <div class="col-12 col-md-7 col-xl-5">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon red"><i class="ri-alert-line"></i></div>
                                <div>
                                    <div class="card-title-text">TOP 5 VENDOR REJECTION RATE</div>
                                    <div class="card-subtitle-text">EVALUASI SUPPLIER & KUALITAS BAHAN</div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive p-0" style="max-height: 250px;">
                            <table class="wwtp-table">
                                <thead>
                                    <tr>
                                        <th>NAMA VENDOR</th>
                                        <th class="text-center">TRIP</th>
                                        <th class="text-center">VOL. TOLAK</th>
                                        <th class="text-center">% REJECT</th>
                                        <th>ALASAN UMUM</th>
                                    </tr>
                                </thead>
                                <tbody id="topRejectVendorsTable">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">MEMUAT DATA VENDOR...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 2.3 Frequent Trucks (Armada Paling Sering Berkunjung) -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon purple"><i class="ri-roadster-line"></i></div>
                                <div>
                                    <div class="card-title-text">ARMADA PALING SERING BERKUNJUNG</div>
                                    <div class="card-subtitle-text">TOP FREQUENT TRUCKS & DWELL TIME</div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive p-0" style="max-height: 250px;">
                            <table class="wwtp-table">
                                <thead>
                                    <tr>
                                        <th>PLAT NOMOR</th>
                                        <th>VENDOR</th>
                                        <th class="text-center">KUNJUNGAN</th>
                                        <th>RERATA TAT</th>
                                    </tr>
                                </thead>
                                <tbody id="frequentTrucksTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">MEMUAT DATA ARMADA...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Throughput Dock, Jenis Armada, Durasi Lab Material (col-xl-4 each) -->
            <div class="row g-2 mb-3">
                <!-- 3.1 Throughput Tonase per Loading Bay -->
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon"><i class="ri-building-line"></i></div>
                                <div>
                                    <div class="card-title-text">THROUGHPUT TONASE & ARMADA PER DOCK</div>
                                    <div class="card-subtitle-text">DISTRIBUSI BEBAN KERJA GUDANG</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartBayThroughput" style="min-height: 220px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 3.2 Armada Types Composition -->
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon purple"><i class="ri-pie-chart-line"></i></div>
                                <div>
                                    <div class="card-title-text">DISTRIBUSI JENIS ARMADA</div>
                                    <div class="card-subtitle-text">BONGKARAN, CURAH, SLIPSHEET, RETUR</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartFleetTypes" style="min-height: 220px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 3.3 Lab Testing Duration by Material -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon"><i class="ri-flask-line"></i></div>
                                <div>
                                    <div class="card-title-text">DURASI PENGUJIAN LAB PER MATERIAL</div>
                                    <div class="card-subtitle-text">INSPEKSI & PENGUJIAN SAMPEL QC</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartMaterialDuration" style="min-height: 220px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 4: Vendor TAT Leaderboard, Insiden Eskalasi, Operator Productivity (col-xl-4 each) -->
            <div class="row g-2">
                <!-- 4.1 Vendor TAT Leaderboard -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon amber"><i class="ri-medal-line"></i></div>
                                <div>
                                    <div class="card-title-text">PERINGKAT VENDOR (TAT)</div>
                                    <div class="card-subtitle-text">KEPATUHAN WAKTU TINGGAL VENDOR</div>
                                </div>
                            </div>
                            <div>
                                <input type="text" id="searchVendorTat"
                                    class="form-control form-input-wwtp py-0 px-2 fs-11" placeholder="CARI..."
                                    style="width: 110px; height: 24px;">
                            </div>
                        </div>
                        <div class="table-responsive p-0" style="max-height: 260px;">
                            <table class="wwtp-table" id="vendorTatTable">
                                <thead>
                                    <tr>
                                        <th>VENDOR</th>
                                        <th class="text-center">TRIP</th>
                                        <th>RERATA</th>
                                        <th class="text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody id="vendorTatTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">MEMUAT DATA VENDOR...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4.2 Incidents & Exceptions -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon red"><i class="ri-file-warning-line"></i></div>
                                <div>
                                    <div class="card-title-text">DAFTAR INSIDEN & ESKALASI</div>
                                    <div class="card-subtitle-text">CATATAN KENDALA OPERASIONAL</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="status-pill red">PENDING: <span id="pendingIssuesCount">0</span></span>
                                <span class="status-pill gray">TOTAL: <span id="totalIncidentsCount">0</span></span>
                            </div>
                        </div>
                        <div class="table-responsive p-0" style="max-height: 260px;">
                            <table class="wwtp-table">
                                <thead>
                                    <tr>
                                        <th>PLAT / ANTREAN</th>
                                        <th>TARGET</th>
                                        <th>KENDALA</th>
                                        <th>WAKTU</th>
                                    </tr>
                                </thead>
                                <tbody id="incidentsTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">MEMUAT DAFTAR INSIDEN...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4.3 Operator Productivity -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="ri-user-star-line"></i></div>
                                <div>
                                    <div class="card-title-text">PRODUKTIVITAS PETUGAS</div>
                                    <div class="card-subtitle-text">AUDIT KINERJA POS & TIMBANGAN</div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive p-0" style="max-height: 260px;">
                            <table class="wwtp-table">
                                <thead>
                                    <tr>
                                        <th>PETUGAS</th>
                                        <th>POS</th>
                                        <th class="text-center">TIKET</th>
                                        <th>RERATA</th>
                                    </tr>
                                </thead>
                                <tbody id="operatorTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">MEMUAT DATA PETUGAS...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Scripts Section -->
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
                let chartStage = null;
                let chartDaily = null;
                let chartLoadingBay = null;
                let chartQc = null;
                let chartBay = null;
                let chartFleet = null;
                let chartMaterial = null;

                // Period Selection
                $('.period-btn').on('click', function() {
                    $('.period-btn').removeClass('active');
                    $(this).addClass('active');

                    const period = $(this).data('period');
                    if (period === 'custom') {
                        $('#customDateRangeBox').removeClass('d-none').addClass('d-flex');
                    } else {
                        $('#customDateRangeBox').removeClass('d-flex').addClass('d-none');
                        loadAnalyticsData();
                    }
                });

                $('#btnApplyFilter').on('click', function() {
                    loadAnalyticsData();
                });

                $('#btnReloadData').on('click', function() {
                    loadAnalyticsData(true);
                });

                // Search Vendor TAT
                $('#searchVendorTat').on('keyup', function() {
                    const q = $(this).val().toLowerCase();
                    $('#vendorTatTableBody tr').each(function() {
                        const rowText = $(this).text().toLowerCase();
                        $(this).toggle(rowText.indexOf(q) > -1);
                    });
                });

                // Fetcher function
                function loadAnalyticsData(isReload = false) {
                    const activePeriod = $('.period-btn.active').data('period') || '30days';
                    const slaLimit = $('#filterSlaLimit').val() || 120;
                    $('.sla-num').text(slaLimit);

                    const params = {
                        period: activePeriod,
                        sla_limit: slaLimit,
                        location_id: $('#filterArea').val(),
                        jenis: $('#filterJenis').val(),
                        vendor: $('#filterVendor').val(),
                        item_id: $('#filterItem').val(),
                    };

                    if (activePeriod === 'custom') {
                        params.start_date = $('#filterStartDate').val();
                        params.end_date = $('#filterEndDate').val();
                    }

                    if (isReload && window.toastr) {
                        toastr.info('Memuat ulang data analitik...');
                    }

                    $.ajax({
                        url: "{{ route('dashboard.vehicle.analytics_data') }}",
                        type: 'GET',
                        data: params,
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                renderTopKpis(res.kpi);
                                renderStageBreakdown(res.cluster_1 || res);
                                renderDailyCheckinChart(res.daily_checkin || (res.cluster_1 ? res.cluster_1.daily_checkin : null));
                                renderLoadingBayChart(res.loading_time || (res.cluster_3 ? res.cluster_3.loading_time : null));
                                renderQcSection(res.cluster_2 || res.qc_compliance);
                                renderFrequentTrucks(res.frequent_trucks || (res.cluster_4 ? res.cluster_4.frequent_trucks : []));
                                renderCapacitySection(res.cluster_3 || res.capacity_throughput);
                                renderFleetSection(res.cluster_4 || res.vendor_fleet);
                                renderIncidentsSection(res.cluster_5 || res.incidents);
                                renderOperatorSection(res.cluster_6 || res.operators);

                                if (isReload && window.toastr) {
                                    toastr.success('Data analitik berhasil diperbarui.');
                                }
                            }
                        },
                        error: function() {
                            toastr.error('Gagal mengambil data analitik.');
                        }
                    });
                }

                // Top KPIs
                function renderTopKpis(kpi) {
                    $('#kpiTotalVehicles').text((kpi.total_vehicles || 0).toLocaleString('id-ID'));
                    $('#kpiAvgTAT').text(kpi.avg_tat_formatted || '--');
                    $('#kpiAvgDailyCheckin').text(kpi.avg_daily_checkin !== undefined ? kpi.avg_daily_checkin : 0);
                    $('#kpiOverSlaCount').text((kpi.over_sla_count || 0).toLocaleString('id-ID'));
                    $('#kpiOverSlaRate').text((kpi.over_sla_rate || 0) + '%');
                    $('#kpiTotalTonnage').text((kpi.total_tonnage || 0).toLocaleString('id-ID', {
                        minimumFractionDigits: 1,
                        maximumFractionDigits: 2
                    }) + ' TON');
                    $('#kpiPrimaryBottleneck').text(kpi.primary_bottleneck_stage ? kpi.primary_bottleneck_stage
                        .toUpperCase() : 'N/A');
                }

                // 1.1 Stage Dwell Time Breakdown (6 Operational Stages)
                function renderStageBreakdown(c1) {
                    const stages = c1.stages;
                    if (!stages) return;
                    const stageKeys = Object.keys(stages);
                    const categories = stageKeys.map(k => stages[k].name.toUpperCase());
                    const avgValues = stageKeys.map(k => stages[k].avg_min);

                    const optionsStage = {
                        series: [{
                            name: 'RATA-RATA DURASI',
                            data: avgValues
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
                                barHeight: '52%',
                                distributed: true,
                                dataLabels: {
                                    position: 'bottom'
                                }
                            }
                        },
                        colors: ['#3b82f6', '#0ea5e9', '#06b6d4', '#10b981', '#f59e0b', '#8b5cf6'],
                        dataLabels: {
                            enabled: true,
                            textAnchor: 'start',
                            style: {
                                colors: ['#ffffff'],
                                fontSize: '10px',
                                fontFamily: 'JetBrains Mono',
                                fontWeight: 700
                            },
                            formatter: function(val, opt) {
                                const k = stageKeys[opt.dataPointIndex];
                                return stages[k].avg_formatted || val + ' mnt';
                            },
                            offsetX: 6
                        },
                        xaxis: {
                            categories: categories,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '9.5px',
                                    fontFamily: 'JetBrains Mono'
                                },
                                formatter: val => Math.round(val) + ' m'
                            },
                            axisBorder: {
                                color: 'rgba(255,255,255,0.06)'
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#cbd5e1',
                                    fontSize: '10px',
                                    fontWeight: 700
                                },
                                maxWidth: 155
                            }
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: (val, opt) => {
                                    const k = stageKeys[opt.dataPointIndex];
                                    const s = stages[k];
                                    return `${s.avg_formatted} (Sampel: ${s.sample_count} Truk)`;
                                }
                            }
                        },
                        legend: {
                            show: false
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)',
                            strokeDashArray: 3
                        }
                    };

                    if (chartStage) chartStage.destroy();
                    $('#chartStageBreakdown').empty();
                    chartStage = new ApexCharts(document.querySelector("#chartStageBreakdown"), optionsStage);
                    chartStage.render();

                    $('#stageInsightText').html(
                        `<span>BOTTLENECK UTAMA: <strong class="text-warning">${c1.primary_bottleneck ? c1.primary_bottleneck.toUpperCase() : 'N/A'}</strong> (${c1.max_stage_avg_formatted || '--'})</span>`
                    );
                }

                // 1.2 Daily Check-In & Average Chart
                function renderDailyCheckinChart(dailyData) {
                    if (!dailyData || !dailyData.series || dailyData.series.length === 0) {
                        $('#chartDailyCheckin').html('<div class="text-center text-muted py-5 fs-11">TIDAK ADA DATA KEDATANGAN</div>');
                        return;
                    }

                    const labels = dailyData.series.map(d => d.label);
                    const counts = dailyData.series.map(d => d.count);
                    const avgVal = dailyData.avg_per_day || 0;
                    const avgLines = dailyData.series.map(() => avgVal);

                    $('#avgDailyCheckinBadge').text(`RERATA: ${avgVal} / HARI`);
                    $('#totalCheckinsBadge').text((dailyData.total_checkins || 0).toLocaleString('id-ID'));
                    $('#periodDaysLabel').text(`${dailyData.total_days || 0} Hari Terakhir`);

                    const optionsDaily = {
                        series: [
                            {
                                name: 'CHECK-IN HARIAN',
                                type: 'column',
                                data: counts
                            },
                            {
                                name: 'RERATA HARIAN',
                                type: 'line',
                                data: avgLines
                            }
                        ],
                        chart: {
                            height: 250,
                            type: 'line',
                            toolbar: { show: false },
                            background: 'transparent'
                        },
                        colors: ['#0284c7', '#f59e0b'],
                        stroke: {
                            width: [0, 2],
                            curve: 'smooth',
                            dashArray: [0, 4]
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 3,
                                columnWidth: '50%'
                            }
                        },
                        labels: labels,
                        xaxis: {
                            categories: labels,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '9px',
                                    fontFamily: 'JetBrains Mono'
                                },
                                rotate: -45
                            },
                            axisBorder: {
                                color: 'rgba(255,255,255,0.06)'
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#cbd5e1',
                                    fontSize: '9.5px',
                                    fontFamily: 'JetBrains Mono'
                                },
                                formatter: val => Math.round(val)
                            }
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            labels: { colors: '#94a3b8' },
                            fontSize: '9.5px',
                            fontFamily: 'JetBrains Mono'
                        },
                        tooltip: {
                            theme: 'dark',
                            shared: true,
                            y: {
                                formatter: (val, opt) => {
                                    if (opt.seriesIndex === 0) return `${val} Armada`;
                                    return `${val} Armada / Hari`;
                                }
                            }
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)',
                            strokeDashArray: 3
                        }
                    };

                    if (chartDaily) chartDaily.destroy();
                    $('#chartDailyCheckin').empty();
                    chartDaily = new ApexCharts(document.querySelector("#chartDailyCheckin"), optionsDaily);
                    chartDaily.render();
                }

                // 1.3 Real Loading / Unloading Time per Dock (Filter data start & finish loading)
                function renderLoadingBayChart(loadingData) {
                    const bayList = loadingData ? (loadingData.bay_list || []) : [];
                    $('#overallAvgLoadingBadge').text(`RERATA: ${loadingData.overall_avg_formatted || '0 mnt'}`);
                    $('#totalLoadingSamplesBadge').text((loadingData.total_samples || 0).toLocaleString('id-ID'));

                    if (!bayList || bayList.length === 0) {
                        $('#chartLoadingBayTime').html(
                            '<div class="text-center text-muted py-5 fs-11">BELUM ADA DATA TIMESTAMP BONGKAR MUAT</div>'
                        );
                        return;
                    }

                    const categories = bayList.map(b => b.dock_name.toUpperCase());
                    const avgMinutes = bayList.map(b => b.avg_duration_min);

                    const optionsLoad = {
                        series: [{
                            name: 'RERATA BONGKAR / MUAT',
                            data: avgMinutes
                        }],
                        chart: {
                            type: 'bar',
                            height: 250,
                            toolbar: { show: false },
                            background: 'transparent'
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 4,
                                barHeight: '48%',
                                distributed: true,
                                dataLabels: { position: 'bottom' }
                            }
                        },
                        colors: ['#10b981', '#06b6d4', '#3b82f6', '#8b5cf6', '#f59e0b'],
                        dataLabels: {
                            enabled: true,
                            textAnchor: 'start',
                            style: {
                                colors: ['#ffffff'],
                                fontSize: '10px',
                                fontFamily: 'JetBrains Mono',
                                fontWeight: 700
                            },
                            formatter: (val, opt) => bayList[opt.dataPointIndex].avg_duration_formatted,
                            offsetX: 6
                        },
                        xaxis: {
                            categories: categories,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '9.5px',
                                    fontFamily: 'JetBrains Mono'
                                },
                                formatter: val => Math.round(val) + ' m'
                            },
                            axisBorder: { color: 'rgba(255,255,255,0.06)' }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#cbd5e1',
                                    fontSize: '10px',
                                    fontWeight: 700
                                },
                                maxWidth: 140
                            }
                        },
                        legend: { show: false },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: (val, opt) => {
                                    const b = bayList[opt.dataPointIndex];
                                    return `${b.avg_duration_formatted} (Sampel: ${b.sample_count} Truk | Min: ${b.min_formatted}, Max: ${b.max_formatted})`;
                                }
                            }
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)',
                            strokeDashArray: 3
                        }
                    };

                    if (chartLoadingBay) chartLoadingBay.destroy();
                    $('#chartLoadingBayTime').empty();
                    chartLoadingBay = new ApexCharts(document.querySelector("#chartLoadingBayTime"), optionsLoad);
                    chartLoadingBay.render();
                }

                // 2.1 QC Section (PASS, REJECT, WAITING, NOT REQUIRED - NO HOLD)
                function renderQcSection(c2) {
                    const qcDist = c2.qc_distribution || {};
                    const pass = qcDist.PASS || 0;
                    const reject = qcDist.REJECT || 0;
                    const waiting = qcDist.WAITING || 0;
                    const notReq = qcDist.NOT_REQUIRED || 0;

                    $('#qcPassCount').text(pass);
                    $('#qcRejectCount').text(reject);
                    $('#qcWaitingCount').text(waiting);
                    $('#qcNotReqCount').text(notReq);

                    const optionsQc = {
                        series: [pass, reject, waiting, notReq],
                        labels: ['PASS', 'REJECT', 'WAITING', 'NON-QC'],
                        chart: {
                            type: 'donut',
                            height: 180,
                            background: 'transparent'
                        },
                        colors: ['#10b981', '#ef4444', '#f59e0b', '#475569'],
                        stroke: {
                            width: 1,
                            colors: ['#0b1426']
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '9.5px',
                                fontFamily: 'JetBrains Mono'
                            }
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '68%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            fontSize: '10px',
                                            color: '#64748b'
                                        },
                                        value: {
                                            show: true,
                                            fontSize: '18px',
                                            fontFamily: 'JetBrains Mono',
                                            fontWeight: 800,
                                            color: '#ffffff',
                                            formatter: val => val
                                        },
                                        total: {
                                            show: true,
                                            label: 'TOTAL',
                                            color: '#64748b',
                                            fontSize: '9px',
                                            formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                        }
                                    }
                                }
                            }
                        },
                        legend: {
                            show: false
                        },
                        tooltip: {
                            theme: 'dark'
                        }
                    };

                    if (chartQc) chartQc.destroy();
                    $('#chartQcDonut').empty();
                    chartQc = new ApexCharts(document.querySelector("#chartQcDonut"), optionsQc);
                    chartQc.render();

                    // Top 5 Reject Vendors Table
                    const tbodyReject = $('#topRejectVendorsTable');
                    tbodyReject.empty();

                    if (!c2.top_rejections || c2.top_rejections.length === 0 || c2.top_rejections.every(v => v
                            .reject_count === 0)) {
                        tbodyReject.append(
                            '<tr><td colspan="5" class="text-center text-muted py-4 fs-11">TIDAK ADA CATATAN REJECT VENDOR</td></tr>'
                            );
                    } else {
                        c2.top_rejections.forEach(v => {
                            const row = `
                            <tr>
                                <td><span class="fw-bold text-white fs-11">${v.vendor}</span></td>
                                <td class="text-center mono fw-bold">${v.total_trips}</td>
                                <td class="text-center mono fw-bold text-danger">${v.reject_qty_ton} TON</td>
                                <td class="text-center">
                                    <span class="status-pill ${v.reject_rate > 0 ? 'red' : 'green'} mono">${v.reject_rate}% (${v.reject_count}x)</span>
                                </td>
                                <td class="fs-10 text-muted" style="max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${v.common_reason}">
                                    ${v.common_reason}
                                </td>
                            </tr>
                        `;
                            tbodyReject.append(row);
                        });
                    }

                    // Material Durations
                    const materials = c2.material_durations;
                    if (!materials || materials.length === 0) {
                        $('#chartMaterialDuration').html(
                            '<div class="text-center text-muted py-5 fs-11">BELUM ADA DATA SAMPLING MATERIAL</div>');
                    } else {
                        const mNames = materials.map(m => m.item_name.toUpperCase());
                        const mDurations = materials.map(m => m.avg_duration_min);

                        const optionsMat = {
                            series: [{
                                name: 'RATA-RATA PENGUJIAN',
                                data: mDurations
                            }],
                            chart: {
                                type: 'bar',
                                height: 220,
                                toolbar: {
                                    show: false
                                },
                                background: 'transparent'
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: true,
                                    borderRadius: 3,
                                    barHeight: '48%',
                                    distributed: true
                                }
                            },
                            colors: ['#38bdf8', '#0ea5e9', '#0284c7', '#0369a1'],
                            dataLabels: {
                                enabled: true,
                                textAnchor: 'start',
                                style: {
                                    colors: ['#fff'],
                                    fontSize: '9.5px',
                                    fontFamily: 'JetBrains Mono',
                                    fontWeight: 700
                                },
                                formatter: (val, opt) => materials[opt.dataPointIndex].avg_duration_formatted,
                                offsetX: 6
                            },
                            xaxis: {
                                categories: mNames,
                                labels: {
                                    style: {
                                        colors: '#64748b',
                                        fontSize: '9px',
                                        fontFamily: 'JetBrains Mono'
                                    }
                                },
                                axisBorder: {
                                    color: 'rgba(255,255,255,0.06)'
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
                            legend: {
                                show: false
                            },
                            tooltip: {
                                theme: 'dark',
                                y: {
                                    formatter: (val, opt) =>
                                        `${materials[opt.dataPointIndex].avg_duration_formatted} (${materials[opt.dataPointIndex].sample_count} Sample)`
                                }
                            },
                            grid: {
                                borderColor: 'rgba(255,255,255,0.05)'
                            }
                        };

                        if (chartMaterial) chartMaterial.destroy();
                        $('#chartMaterialDuration').empty();
                        chartMaterial = new ApexCharts(document.querySelector("#chartMaterialDuration"), optionsMat);
                        chartMaterial.render();
                    }
                }

                // 2.3 Frequent Trucks (Armada Paling Sering Berkunjung)
                function renderFrequentTrucks(trucks) {
                    const tbody = $('#frequentTrucksTableBody');
                    tbody.empty();

                    if (!trucks || trucks.length === 0) {
                        tbody.append('<tr><td colspan="4" class="text-center text-muted py-4 fs-11">TIDAK ADA DATA KUNJUNGAN ARMADA</td></tr>');
                        return;
                    }

                    trucks.forEach(t => {
                        const row = `
                            <tr>
                                <td>
                                    <span class="plate-box">${t.no_pol}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-white fs-11">${t.vendor}</span>
                                </td>
                                <td class="text-center">
                                    <span class="mono fw-bold text-white fs-12">${t.trips}x</span>
                                </td>
                                <td>
                                    <span class="mono fw-bold text-info">${t.avg_tat_formatted}</span>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }

                // Capacity & Bay Throughput
                function renderCapacitySection(c3) {
                    const bays = c3.bay_throughput;
                    if (!bays || bays.length === 0) {
                        $('#chartBayThroughput').html(
                            '<div class="text-center text-muted py-5 fs-11">TIDAK ADA DATA THROUGHPUT BAY</div>');
                        return;
                    }

                    const bayNames = bays.map(b => b.location_name.toUpperCase());
                    const tonnages = bays.map(b => b.total_tonnage);
                    const truckCounts = bays.map(b => b.trucks_handled);

                    const optionsBay = {
                        series: [{
                                name: 'TONASE (TON)',
                                type: 'bar',
                                data: tonnages
                            },
                            {
                                name: 'ARMADA SELESAI',
                                type: 'line',
                                data: truckCounts
                            }
                        ],
                        chart: {
                            height: 220,
                            type: 'line',
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#3b82f6', '#10b981'],
                        stroke: {
                            width: [0, 2],
                            curve: 'smooth'
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '40%'
                            }
                        },
                        labels: bayNames,
                        xaxis: {
                            categories: bayNames,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '9.5px',
                                    fontFamily: 'JetBrains Mono'
                                }
                            },
                            axisBorder: {
                                color: 'rgba(255,255,255,0.06)'
                            }
                        },
                        yaxis: [{
                                title: {
                                    text: 'TONASE',
                                    style: {
                                        color: '#3b82f6',
                                        fontSize: '9.5px'
                                    }
                                },
                                labels: {
                                    style: {
                                        colors: '#3b82f6',
                                        fontSize: '9.5px',
                                        fontFamily: 'JetBrains Mono'
                                    }
                                }
                            },
                            {
                                opposite: true,
                                title: {
                                    text: 'TRUK',
                                    style: {
                                        color: '#10b981',
                                        fontSize: '9.5px'
                                    }
                                },
                                labels: {
                                    style: {
                                        colors: '#10b981',
                                        fontSize: '9.5px',
                                        fontFamily: 'JetBrains Mono'
                                    }
                                }
                            }
                        ],
                        tooltip: {
                            theme: 'dark',
                            shared: true
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            labels: {
                                colors: '#94a3b8'
                            },
                            fontSize: '10px'
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)'
                        }
                    };

                    if (chartBay) chartBay.destroy();
                    $('#chartBayThroughput').empty();
                    chartBay = new ApexCharts(document.querySelector("#chartBayThroughput"), optionsBay);
                    chartBay.render();
                }

                // Fleet Composition & Vendor TAT
                function renderFleetSection(c4) {
                    // Vendor TAT Leaderboard
                    const tbodyTat = $('#vendorTatTableBody');
                    tbodyTat.empty();

                    if (!c4.vendor_tat || c4.vendor_tat.length === 0) {
                        tbodyTat.append(
                            '<tr><td colspan="4" class="text-center text-muted py-4 fs-11">TIDAK ADA DATA VENDOR</td></tr>'
                            );
                    } else {
                        c4.vendor_tat.forEach(v => {
                            let statusClass = 'green';
                            if (v.status === 'Sering Terlambat') statusClass = 'red';
                            else if (v.status === 'Waspada') statusClass = 'amber';

                            const row = `
                            <tr>
                                <td><div class="fw-bold text-white fs-11">${v.vendor}</div></td>
                                <td class="text-center mono fw-bold">${v.trips}</td>
                                <td><span class="mono fw-bold text-white">${v.avg_tat_formatted}</span></td>
                                <td class="text-center">
                                    <span class="status-pill ${statusClass}">${v.status.toUpperCase()}</span>
                                </td>
                            </tr>
                        `;
                            tbodyTat.append(row);
                        });
                    }

                    // Fleet Types Pie
                    const fleets = c4.fleet_distribution;
                    if (!fleets || fleets.length === 0) {
                        $('#chartFleetTypes').html(
                            '<div class="text-center text-muted py-5 fs-11">TIDAK ADA DATA ARMADA</div>');
                    } else {
                        const fNames = fleets.map(f => f.jenis.toUpperCase());
                        const fCounts = fleets.map(f => f.count);

                        const optionsFleet = {
                            series: fCounts,
                            labels: fNames,
                            chart: {
                                type: 'donut',
                                height: 220,
                                background: 'transparent'
                            },
                            colors: ['#3b82f6', '#10b981', '#f59e0b', '#06b6d4', '#8b5cf6'],
                            stroke: {
                                width: 1,
                                colors: ['#0b1426']
                            },
                            dataLabels: {
                                enabled: true,
                                style: {
                                    fontSize: '9.5px',
                                    fontFamily: 'JetBrains Mono'
                                }
                            },
                            legend: {
                                position: 'bottom',
                                horizontalAlign: 'center',
                                labels: {
                                    colors: '#94a3b8'
                                },
                                fontSize: '10px'
                            },
                            tooltip: {
                                theme: 'dark',
                                y: {
                                    formatter: (val, opt) =>
                                        `${val} Truk (Avg: ${fleets[opt.seriesIndex].avg_duration_formatted})`
                                }
                            }
                        };

                        if (chartFleet) chartFleet.destroy();
                        $('#chartFleetTypes').empty();
                        chartFleet = new ApexCharts(document.querySelector("#chartFleetTypes"), optionsFleet);
                        chartFleet.render();
                    }
                }

                // Incidents
                function renderIncidentsSection(c5) {
                    $('#pendingIssuesCount').text(c5.pending_issues_count || 0);
                    $('#totalIncidentsCount').text(c5.total_incidents || 0);

                    const tbodyInc = $('#incidentsTableBody');
                    tbodyInc.empty();

                    if (!c5.incident_list || c5.incident_list.length === 0) {
                        tbodyInc.append(
                            '<tr><td colspan="4" class="text-center text-muted py-4 fs-11">TIDAK ADA CATATAN ANOMALI / KENDALA</td></tr>'
                            );
                        return;
                    }

                    c5.incident_list.forEach(inc => {
                        const row = `
                        <tr>
                            <td>
                                <span class="plate-box">${inc.no_pol}</span>
                                <small class="text-muted d-block mono fs-9 mt-0.5">${inc.no_antrian}</small>
                            </td>
                            <td><span class="status-pill gray">${inc.target_unit.toUpperCase()}</span></td>
                            <td class="fs-10 text-warning" style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${inc.notes}">
                                ${inc.notes}
                            </td>
                            <td class="fs-10 text-muted mono">${inc.incident_time}</td>
                        </tr>
                    `;
                        tbodyInc.append(row);
                    });
                }

                // Operator Productivity
                function renderOperatorSection(c6) {
                    const tbodyOp = $('#operatorTableBody');
                    tbodyOp.empty();

                    if (!c6.operators || c6.operators.length === 0) {
                        tbodyOp.append(
                            '<tr><td colspan="4" class="text-center text-muted py-4 fs-11">BELUM ADA RIWAYAT AKTIVITAS PETUGAS</td></tr>'
                            );
                        return;
                    }

                    c6.operators.forEach(op => {
                        const row = `
                        <tr>
                            <td><div class="fw-bold text-white fs-11">${op.user_name.toUpperCase()}</div></td>
                            <td><span class="status-pill green">${op.pos.toUpperCase()}</span></td>
                            <td class="text-center mono fw-bold text-white">${op.total_tickets}</td>
                            <td><span class="mono fw-semibold text-muted fs-10">${op.avg_duration_formatted.toUpperCase()}</span></td>
                        </tr>
                    `;
                        tbodyOp.append(row);
                    });
                }

                // Initial Execution
                loadAnalyticsData();
            });
        </script>
    </body>

</html>
