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

            @keyframes spinLoader {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            .spin-loader {
                animation: spinLoader 1s linear infinite;
                display: inline-block;
            }

            @media (min-width: 1200px) {
                .row-cols-xl-7>* {
                    flex: 0 0 auto;
                    width: 14.285714%;
                }
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
                        <div class="header-subtitle-cyan">VEHICLE ANALYTICS</div>
                    </div>
                </div>

                <div class="text-center d-none d-lg-block">
                    <div class="header-title-main" style="letter-spacing: 1px;">VEHICLE ANALYTICS DASHBOARD</div>
                    <div class="header-subtitle-cyan">VEHICLE MONITORING & TRAFFIC ANALYTICS</div>
                    <div class="header-tagline">&ldquo;Visibility, Control, and Operational Efficiency&rdquo;</div>
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
            <div class="filter-toolbar-box d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
                <!-- Sisi Kiri: Pilihan Periode & Box Kustom Ramping -->
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="text-muted fs-11 fw-bold text-uppercase me-1"><i
                            class="ri-calendar-line align-middle me-0.5"></i> PERIODE:</span>
                    <button type="button" class="period-btn" data-period="today">HARI INI</button>
                    <button type="button" class="period-btn" data-period="7days">7 HARI</button>
                    <button type="button" class="period-btn" data-period="month">BULAN INI</button>
                    <button type="button" class="period-btn active" data-period="30days">30 HARI</button>
                    <button type="button" class="period-btn" data-period="custom" id="pillCustom">KUSTOM</button>

                    <!-- Custom Range Inputs (Bersebelahan pas dan ramping) -->
                    <div class="d-none align-items-center gap-1 ms-1" id="customDateRangeBox">
                        <input type="date" class="form-control form-input-wwtp py-0 px-2 fs-11" id="filterStartDate"
                            style="width: 122px; height: 26px;" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                        <span class="text-muted fs-10 fw-bold">-</span>
                        <input type="date" class="form-control form-input-wwtp py-0 px-2 fs-11" id="filterEndDate"
                            style="width: 122px; height: 26px;" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <!-- Sisi Kanan: Dimension Filters (Auto trigger on-change tanpa tombol Terapkan) -->
                <div class="d-flex align-items-center gap-1 flex-wrap ms-auto">
                    <!-- Area Target -->
                    <div style="min-width: 110px;">
                        <select class="form-select form-select-wwtp py-0 px-2 fs-11" id="filterArea"
                            style="height: 26px;">
                            <option value="all">SEMUA AREA</option>
                            <option value="smu_wfg">SMU &amp; WFG AREA</option>
                            @foreach ($locations as $loc)
                                @php
                                    $locNameUpper = strtoupper($loc->name);
                                    $locSlocUpper = strtoupper($loc->s_loc);
                                    $isSmuWfg =
                                        in_array($loc->id, [4, 5]) ||
                                        in_array($locSlocUpper, ['A001', 'A002']) ||
                                        str_contains($locNameUpper, 'SMU') ||
                                        str_contains($locNameUpper, 'WFG');
                                @endphp
                                @if (!$isSmuWfg)
                                    <option value="{{ $loc->id }}">{{ strtoupper($loc->name) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Jenis Operasi -->
                    <div style="min-width: 100px;">
                        <select class="form-select form-select-wwtp py-0 px-2 fs-11" id="filterJenis"
                            style="height: 26px;">
                            <option value="all">SEMUA JENIS</option>
                            <option value="bongkaran">BONGKARAN</option>
                            <option value="muatan">MUATAN</option>
                            <option value="retur">RETUR</option>
                        </select>
                    </div>

                    <!-- Vendor -->
                    <div style="min-width: 115px; max-width: 150px;">
                        <select class="form-select form-select-wwtp py-0 px-2 fs-11" id="filterVendor"
                            style="height: 26px;">
                            <option value="all">SEMUA VENDOR</option>
                            @foreach ($vendors as $vnd)
                                <option value="{{ $vnd }}">{{ strtoupper($vnd) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Material -->
                    <div style="min-width: 115px; max-width: 145px;">
                        <select class="form-select form-select-wwtp py-0 px-2 fs-11" id="filterItem"
                            style="height: 26px;">
                            <option value="all">SEMUA MATERIAL</option>
                            @foreach ($items as $it)
                                <option value="{{ $it->id }}">{{ strtoupper($it->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Duration Truck Threshold -->
                    <div class="d-flex align-items-center gap-1 bg-dark px-2 py-0 rounded"
                        style="border: 1px solid rgba(255,255,255,0.08); height: 26px;">
                        <span class="text-muted fs-10 fw-bold">DURATION:</span>
                        <input type="number" id="filterSlaLimit"
                            class="form-control form-input-wwtp py-0 px-1 text-center fw-bold text-warning"
                            style="width: 48px; height: 20px; font-size: 11px;" value="450" min="30"
                            max="1440" step="30" title="Target Duration Truck (menit)">
                        <span class="text-muted fs-10">M</span>
                    </div>
                </div>
            </div>

            <!-- Top KPI Cards Row (7 Cards: Volume, Duration, Check-in, Over SLA, Batal Bongkar, Tonase, Bottleneck) -->
            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-xl-6 g-2 mb-2">
                <!-- 1. Total Volume -->
                <div class="col">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header py-2">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon"><i class="ri-truck-line"></i></div>
                                <div>
                                    <div class="card-title-text">TOTAL TRUK</div>
                                    <div class="card-subtitle-text">JUMLAH KENDARAAN</div>
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

                <!-- 2. Avg Duration Truck -->
                <div class="col">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header py-2">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="ri-time-line"></i></div>
                                <div>
                                    <div class="card-title-text">AVG. DURATION TRUCK</div>
                                    <div class="card-subtitle-text">POS 1 &rarr; TIMBANGAN OUT</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body py-2">
                            <div class="kpi-main-number text-white" id="kpiAvgTAT">--</div>
                            <div class="kpi-target-tag green">
                                <i class="ri-arrow-down-line"></i> TARGET &le; <span class="sla-num">450</span> M
                                (7.5J)
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
                                    <div class="card-title-text">AVG. CHECK-IN</div>
                                    <div class="card-subtitle-text">KENDARAAN / HARI</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body py-2">
                            <div class="kpi-main-number text-white" id="kpiAvgDailyCheckin">0</div>
                            <div class="kpi-target-tag cyan">
                                <i class="ri-pulse-line"></i> KENDARAAN / HARI
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Duration Truck (> 7.5 Jam) -->
                <div class="col">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header py-2">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon red"><i class="ri-alarm-warning-line"></i></div>
                                <div>
                                    <div class="card-title-text">DURATION TRUCK</div>
                                    <div class="card-subtitle-text">&gt; 7.5 JAM (450 MNT)</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body py-2">
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="kpi-main-number text-white" id="kpiOverSlaCount">0</span>
                                <span class="status-pill red mono" id="kpiOverSlaRate">0%</span>
                            </div>
                            <div class="kpi-target-tag red">
                                <i class="ri-error-warning-line"></i> &gt; <span class="sla-num">450</span> MENIT
                                (7.5J)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Total Tonnage -->
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

                <!-- 7. Primary Bottleneck Stage -->
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

            <!-- Row Tren Kedatangan & Pembatalan Harian (Pos 1, Timbangan, Batal Bongkar & Muat) (col-xl-4 each) -->
            <div class="row g-2 mb-3">
                <!-- 2.1 Stage Bottleneck Dwell Time (Pure Vehicle Flow - No Sampling) -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon amber"><i class="ri-timer-line"></i></div>
                                <div>
                                    <div class="card-title-text">DURASI TAHAPAN OPERASIONAL</div>
                                    <div class="card-subtitle-text">PERGERAKAN KENDARAAN (POS 1 &rarr; CHECK-OUT)</div>
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

                <!-- 1.2 Tren Check-in Timbangan Harian & Rata-rata -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon"><i class="ri-scales-line"></i></div>
                                <div>
                                    <div class="card-title-text">TREN CHECK-IN TIMBANGAN HARIAN &amp; RATA-RATA</div>
                                    <div class="card-subtitle-text">VOLUME CHECK-IN TIMBANGAN MASUK VS RERATA</div>
                                </div>
                            </div>
                            <div>
                                <span class="status-pill cyan mono" id="avgDailyCheckinBadge">RERATA: 0 / HARI</span>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartDailyCheckin" style="min-height: 250px;"></div>
                            <div
                                class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle fs-11 text-muted">
                                <span>Total Timbangan: <strong class="text-white mono"
                                        id="totalCheckinsBadge">0</strong> Truk</span>
                                <span class="mono text-muted fs-10" id="periodDaysLabel"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1.1 Tren Check-in Pos 1 Harian & Rata-rata -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon cyan"><i class="ri-shield-user-line"></i></div>
                                <div>
                                    <div class="card-title-text">DISTRIBUSI JAM CHECK-IN POS 1</div>
                                    <div class="card-subtitle-text">KLASIFIKASI KEDATANGAN TRUK (JAM 1 - 24)</div>
                                </div>
                            </div>
                            <div>
                                <span class="status-pill cyan mono" id="avgDailyCheckinPos1Badge">TERBANYAK: JAM
                                    --</span>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartDailyCheckinPos1" style="min-height: 250px;"></div>
                            <div
                                class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle fs-11 text-muted">
                                <span>Total Pos 1: <strong class="text-white mono"
                                        id="totalCheckinsPos1Badge">0</strong> Truk</span>
                                <span class="mono text-muted fs-10" id="periodDaysLabelPos1">Peak: Jam --</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2.2 Durasi Bongkar / Muat Riil -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="ri-truck-fill"></i></div>
                                <div>
                                    <div class="card-title-text">DURASI BONGKAR / MUAT</div>
                                    <div class="card-subtitle-text">RATA-RATA WAKTU BONGKAR / MUAT DOCK</div>
                                </div>
                            </div>
                            <div>
                                <span class="status-pill green mono" id="overallAvgLoadingBadge">RERATA: 0 mnt</span>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartLoadingBayTime" style="min-height: 250px;"></div>
                            <div
                                class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle fs-11 text-muted">
                                <span>Sampel Riil: <strong class="text-white mono"
                                        id="totalLoadingSamplesBadge">0</strong> Truk</span>
                                <span class="fs-10 text-muted">Filter start &amp; finish loading</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3.3 Truck Duration Mingguan -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="ri-calendar-line"></i></div>
                                <div>
                                    <div class="card-title-text">TRUCK DURATION MINGGUAN</div>
                                    <div class="card-subtitle-text">TREN RERATA DURASI TRUK VS TARGET 7.5 JAM</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartWeeklyDuration" style="min-height: 240px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 2.3 Tonase & Truk per Area -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon"><i class="ri-building-line"></i></div>
                                <div>
                                    <div class="card-title-text">TONASE &amp; TRUK PER AREA</div>
                                    <div class="card-subtitle-text">DISTRIBUSI BEBAN KERJA AREA DOCK</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartBayThroughput" style="min-height: 220px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 2.4 Tren Jenis Truk Harian (Bongkaran, Muatan, Retur) -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon blue"><i class="ri-line-chart-line"></i></div>
                                <div>
                                    <div class="card-title-text">TREN JENIS TRUK HARIAN</div>
                                    <div class="card-subtitle-text">BONGKARAN, MUATAN &amp; RETUR</div>
                                </div>
                            </div>
                            <div>
                                <span class="status-pill blue mono" id="totalFleetTrendBadge">TOTAL: 0 TRUK</span>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartFleetTrend" style="min-height: 220px;"></div>
                            <div
                                class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle fs-11 text-muted">
                                <span>Bongkar: <strong class="text-info mono" id="trendBongkarBadge">0</strong>
                                    | Muat: <strong class="text-success mono" id="trendMuatBadge">0</strong>
                                    | Retur: <strong class="text-warning mono" id="trendReturBadge">0</strong></span>
                                <span class="mono text-muted fs-10" id="periodDaysLabelFleetTrend"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3.1 Distribusi Jenis Truk (Bongkaran, Muatan, Retur) -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon purple"><i class="ri-pie-chart-line"></i></div>
                                <div>
                                    <div class="card-title-text">DISTRIBUSI JENIS TRUK</div>
                                    <div class="card-subtitle-text">BONGKARAN, MUATAN, RETUR</div>
                                </div>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartFleetTypes" style="min-height: 240px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 1.3 Tren Pembatalan Truk Harian (Batal Bongkar & Batal Muat) -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon red"><i class="ri-close-circle-line"></i></div>
                                <div>
                                    <div class="card-title-text">TREN PEMBATALAN TRUK</div>
                                    <div class="card-subtitle-text">BATAL BONGKAR VS BATAL MUAT</div>
                                </div>
                            </div>
                            <div>
                                <span class="status-pill red mono" id="totalCancelledBadge">TOTAL: 0 TRUK</span>
                            </div>
                        </div>
                        <div class="wwtp-card-body">
                            <div id="chartDailyCancelled" style="min-height: 250px;"></div>
                            <div
                                class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary-subtle fs-11 text-muted">
                                <span>Bongkar: <strong class="text-danger mono" id="totalBatalBongkarBadge">0</strong>
                                    | Muat: <strong class="text-warning mono"
                                        id="totalBatalMuatBadge">0</strong></span>
                                <span class="mono text-muted fs-10" id="periodDaysLabelCancelled"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3.2 Detail Data Vendor Melebihi Truck Duration (> 7.5 Jam) -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon red"><i class="ri-error-warning-line"></i></div>
                                <div>
                                    <div class="card-title-text">DETAIL VENDOR - DURATION TRUCK &gt; 7.5 JAM</div>
                                    <div class="card-subtitle-text">TOTAL TRUK MELEBIHI TRUCK DURATION (450 MNT)</div>
                                </div>
                            </div>
                            <div>
                                <input type="text" id="searchVendorOverDuration"
                                    class="form-control form-input-wwtp py-0 px-2 fs-11" placeholder="CARI VENDOR..."
                                    style="width: 120px; height: 24px;">
                            </div>
                        </div>
                        <div class="table-responsive p-0" style="max-height: 275px;">
                            <table class="wwtp-table" id="vendorOverDurationTable">
                                <thead>
                                    <tr>
                                        <th>VENDOR</th>
                                        <th class="text-center">&gt; 7.5 JAM</th>
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">% OVER</th>
                                        <th>RERATA</th>
                                        <th class="text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody id="vendorOverDurationTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">MEMUAT DATA VENDOR...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4.1 Truk Paling Sering Berkunjung -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon purple"><i class="ri-roadster-line"></i></div>
                                <div>
                                    <div class="card-title-text">TRUK PALING SERING BERKUNJUNG</div>
                                    <div class="card-subtitle-text">TOP FREQUENT TRUCKS &amp; DURATION</div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive p-0" style="max-height: 320px;">
                            <table class="wwtp-table">
                                <thead>
                                    <tr>
                                        <th>PLAT NOMOR</th>
                                        <th>VENDOR</th>
                                        <th class="text-center">TRIP</th>
                                        <th>AVG. DURATION</th>
                                    </tr>
                                </thead>
                                <tbody id="frequentTrucksTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">MEMUAT DATA TRUK...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4.2 Produktivitas Petugas (Nama Lengkap / Username) -->
                <div class="col-12 col-xl-4">
                    <div class="wwtp-card h-100">
                        <div class="wwtp-card-header">
                            <div class="d-flex align-items-center">
                                <div class="card-header-icon green"><i class="ri-user-star-line"></i></div>
                                <div>
                                    <div class="card-title-text">PRODUKTIVITAS PETUGAS</div>
                                    <div class="card-subtitle-text">AUDIT KINERJA OPERASIONAL POS &amp; TIMBANGAN</div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive p-0" style="max-height: 320px;">
                            <table class="wwtp-table">
                                <thead>
                                    <tr>
                                        <th>NAMA PETUGAS / USERNAME</th>
                                        <th>POS OPERASIONAL</th>
                                        <th class="text-center">TOTAL TIKET</th>
                                        <th>RERATA WAKTU</th>
                                    </tr>
                                </thead>
                                <tbody id="operatorTableBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">MEMUAT DATA PETUGAS...
                                        </td>
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
                let chartDailyPos1 = null;
                let chartDailyTmb = null;
                let chartDailyCancelled = null;
                let chartLoadingBay = null;
                let chartBay = null;
                let chartFleet = null;
                let chartFleetTrend = null;
                let chartWeekly = null;

                // Period Selection
                $('.period-btn').on('click', function() {
                    $('.period-btn').removeClass('active');
                    $(this).addClass('active');

                    const period = $(this).data('period');
                    if (period === 'custom') {
                        $('#customDateRangeBox').removeClass('d-none').addClass('d-flex');
                    } else {
                        $('#customDateRangeBox').removeClass('d-flex').addClass('d-none');
                    }
                    loadAnalyticsData();
                });

                // Auto-trigger filter on change (Tanpa tombol Terapkan)
                $('#filterArea, #filterJenis, #filterVendor, #filterItem').on('change', function() {
                    loadAnalyticsData();
                });

                // Auto-trigger on custom date change
                $('#filterStartDate, #filterEndDate').on('change', function() {
                    if ($('.period-btn.active').data('period') === 'custom') {
                        loadAnalyticsData();
                    }
                });

                // Auto-trigger on duration limit change (debounced)
                let slaLimitTimeout = null;
                $('#filterSlaLimit').on('input change', function() {
                    clearTimeout(slaLimitTimeout);
                    slaLimitTimeout = setTimeout(function() {
                        loadAnalyticsData();
                    }, 400);
                });

                $('#btnReloadData').on('click', function() {
                    loadAnalyticsData(true);
                });

                // Search Vendor Over Duration
                $('#searchVendorOverDuration').on('keyup', function() {
                    const q = $(this).val().toLowerCase();
                    $('#vendorOverDurationTableBody tr').each(function() {
                        const rowText = $(this).text().toLowerCase();
                        $(this).toggle(rowText.indexOf(q) > -1);
                    });
                });

                // Get Current Filter Parameters
                function getAnalyticsParams() {
                    const activePeriod = $('.period-btn.active').data('period') || '30days';
                    const slaLimit = $('#filterSlaLimit').val() || 450;
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

                    return params;
                }

                // Show Skeleton/Loading State
                function showSectionLoaders() {
                    const spinnerHtml =
                        '<span class="spinner-border spinner-border-sm text-info opacity-75" style="width: 13px; height: 13px; border-width: 2px;" role="status"></span>';
                    $('#kpiTotalVehicles').html(spinnerHtml);
                    $('#kpiAvgTAT').html(spinnerHtml);
                    $('#kpiAvgDailyCheckin').html(spinnerHtml);
                    $('#kpiOverSlaCount').html(spinnerHtml);
                    $('#kpiOverSlaRate').html(spinnerHtml);
                    $('#kpiCancelledCount').html(spinnerHtml);
                    $('#kpiCancelledRate').html(spinnerHtml);
                    $('#kpiCancelledBongkar').text('0');
                    $('#kpiCancelledMuat').text('0');
                    $('#kpiTotalTonnage').html(spinnerHtml);
                    $('#kpiPrimaryBottleneck').html(spinnerHtml);

                    $('#totalCancelledBadge').text('TOTAL: 0 TRUK');
                    $('#totalBatalBongkarBadge').text('0');
                    $('#totalBatalMuatBadge').text('0');
                    $('#chartDailyCancelled').html(
                        '<div class="text-center text-muted py-5 fs-11"><i class="ri-loader-4-line spin-loader align-middle me-1"></i> MEMUAT TREN PEMBATALAN...</div>'
                    );

                    $('#totalFleetTrendBadge').text('TOTAL: 0 TRUK');
                    $('#trendBongkarBadge').text('0');
                    $('#trendMuatBadge').text('0');
                    $('#trendReturBadge').text('0');
                    $('#chartFleetTrend').html(
                        '<div class="text-center text-muted py-5 fs-11"><i class="ri-loader-4-line spin-loader align-middle me-1"></i> MEMUAT TREN JENIS TRUK...</div>'
                    );

                    $('#frequentTrucksTableBody').html(
                        '<tr><td colspan="4" class="text-center text-muted py-4 fs-11"><i class="ri-loader-4-line spin-loader align-middle me-1"></i> MEMUAT DATA KUNJUNGAN TRUK...</td></tr>'
                    );
                    $('#vendorOverDurationTableBody').html(
                        '<tr><td colspan="6" class="text-center text-muted py-4 fs-11"><i class="ri-loader-4-line spin-loader align-middle me-1"></i> MEMUAT DETAIL VENDOR...</td></tr>'
                    );
                    $('#operatorTableBody').html(
                        '<tr><td colspan="4" class="text-center text-muted py-4 fs-11"><i class="ri-loader-4-line spin-loader align-middle me-1"></i> MEMUAT AKTIVITAS PETUGAS...</td></tr>'
                    );
                }

                // Section 1: KPI & Daily Trends (Pos 1, Timbangan & Pembatalan) - High Priority (Fastest)
                function loadSectionKpiTrends(params) {
                    $.ajax({
                        url: "{{ route('dashboard.vehicle.analytics_data') }}",
                        type: 'GET',
                        data: {
                            ...params,
                            section: 'kpi_trends'
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                renderTopKpis(res.kpi);
                                renderDailyCheckinPos1(res.daily_checkin_pos1);
                                renderDailyCheckinTimbangan(res.daily_checkin_timbangan || res
                                    .daily_checkin);
                                renderDailyCancelled(res.daily_cancelled);
                            }
                        },
                        error: function() {
                            $('#kpiTotalVehicles').text('0');
                            $('#kpiAvgTAT').text('--');
                            $('#kpiCancelledCount').text('0');
                            $('#kpiCancelledRate').text('0%');
                            $('#kpiCancelledBongkar').text('0');
                            $('#kpiCancelledMuat').text('0');
                        }
                    });
                }

                // Section 2: Operations (Stages, Dock Loading Times, Frequent Trucks)
                function loadSectionOperations(params) {
                    $.ajax({
                        url: "{{ route('dashboard.vehicle.analytics_data') }}",
                        type: 'GET',
                        data: {
                            ...params,
                            section: 'operations'
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                renderStageBreakdown(res.cluster_1 || res);
                                renderLoadingBayChart(res.loading_time);
                                renderFrequentTrucks(res.frequent_trucks);
                            }
                        }
                    });
                }

                // Section 3: Vendor & Capacity (Bay Throughput, Fleet Donut, Vendor Over Duration, Weekly Duration)
                function loadSectionVendorCapacity(params) {
                    $.ajax({
                        url: "{{ route('dashboard.vehicle.analytics_data') }}",
                        type: 'GET',
                        data: {
                            ...params,
                            section: 'vendor_capacity'
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                renderCapacitySection(res.capacity_throughput);
                                renderFleetSection(res.vendor_fleet || res);
                                renderFleetTrendChart(res.fleet_trend || (res.vendor_fleet && res
                                    .vendor_fleet.fleet_trend));
                                renderVendorOverDurationTable(res.vendor_over_duration);
                                renderWeeklyDurationChart(res.weekly_duration);
                            }
                        }
                    });
                }

                // Section 4: Operator Productivity Audit
                function loadSectionOperators(params) {
                    $.ajax({
                        url: "{{ route('dashboard.vehicle.analytics_data') }}",
                        type: 'GET',
                        data: {
                            ...params,
                            section: 'operators'
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                const list = res.operator_list ||
                                    (res.operators && res.operators.operator_list) ||
                                    (res.operators && res.operators.operators) ||
                                    (res.cluster_6 && res.cluster_6.operators) ||
                                    (Array.isArray(res.operators) ? res.operators : []);
                                renderOperatorSection(list);
                            }
                        },
                        error: function() {
                            $('#operatorTableBody').html(
                                '<tr><td colspan="4" class="text-center text-muted py-4 fs-11">GAGAL MEMUAT DATA PETUGAS</td></tr>'
                            );
                        }
                    });
                }

                // Unified Orchestrator: Progressive Modular Async Loading
                function loadAnalyticsData(isReload = false) {
                    const params = getAnalyticsParams();
                    showSectionLoaders();

                    if (isReload && window.toastr) {
                        toastr.info('Memuat ulang modul analitik secara paralel...');
                    }

                    // Parallel non-blocking execution per section
                    loadSectionKpiTrends(params);
                    loadSectionOperations(params);
                    loadSectionVendorCapacity(params);
                    loadSectionOperators(params);
                }

                // Top KPIs
                function renderTopKpis(kpi) {
                    $('#kpiTotalVehicles').text((kpi.total_vehicles || 0).toLocaleString('id-ID'));
                    $('#kpiAvgTAT').text(kpi.avg_tat_formatted || '--');
                    $('#kpiAvgDailyCheckin').text(kpi.avg_daily_checkin !== undefined ? kpi.avg_daily_checkin : 0);
                    $('#kpiOverSlaCount').text((kpi.over_sla_count || 0).toLocaleString('id-ID'));
                    $('#kpiOverSlaRate').text((kpi.over_sla_rate || 0) + '%');
                    $('#kpiCancelledCount').text((kpi.cancelled_count || 0).toLocaleString('id-ID'));
                    $('#kpiCancelledRate').text((kpi.cancelled_rate || 0) + '%');
                    $('#kpiCancelledBongkar').text((kpi.cancelled_bongkar_count || 0).toLocaleString('id-ID'));
                    $('#kpiCancelledMuat').text((kpi.cancelled_muat_count || 0).toLocaleString('id-ID'));
                    $('#kpiTotalTonnage').text((kpi.total_tonnage || 0).toLocaleString('id-ID', {
                        minimumFractionDigits: 1,
                        maximumFractionDigits: 2
                    }) + ' TON');
                    if (kpi.primary_bottleneck_stage) {
                        $('#kpiPrimaryBottleneck').text(kpi.primary_bottleneck_stage.toUpperCase());
                    }
                }

                // 1.1 Stage Dwell Time Breakdown (Operasional murni tanpa QC)
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
                        colors: ['#3b82f6', '#0ea5e9', '#06b6d4', '#10b981'],
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

                    const bottleneck = (c1.primary_bottleneck && c1.primary_bottleneck !== 'N/A') ? c1
                        .primary_bottleneck : 'N/A';
                    $('#stageInsightText').html(
                        `<span>BOTTLENECK UTAMA: <strong class="text-warning">${bottleneck.toUpperCase()}</strong> (${c1.max_stage_avg_formatted || '--'})</span>`
                    );
                    $('#kpiPrimaryBottleneck').text(bottleneck.toUpperCase());
                }

                // 1.2 Distribusi Jam Check-in Pos 1 (Klasifikasi Bongkaran, Muatan, Retur & Jam Puncak)
                function renderDailyCheckinPos1(pos1Data) {
                    if (!pos1Data || !pos1Data.series || pos1Data.series.length === 0) {
                        $('#chartDailyCheckinPos1').html(
                            '<div class="text-center text-muted py-5 fs-11">TIDAK ADA DATA KEDATANGAN POS 1</div>');
                        return;
                    }

                    const labels = pos1Data.series.map(d => d.short_label || d.label);
                    const bongkarCounts = pos1Data.series.map(d => d.bongkaran || 0);
                    const muatCounts = pos1Data.series.map(d => d.muatan || 0);
                    const returCounts = pos1Data.series.map(d => d.retur || 0);

                    // Kebanyakan truk di jam berapa (Jam Puncak / Peak Hour)
                    const peakText = (pos1Data.peak_hour && pos1Data.peak_hour !== '-' && (pos1Data.peak_count || 0) > 0)
                        ? `TERBANYAK: ${pos1Data.peak_hour.toUpperCase()} (${pos1Data.peak_count} TRUK)`
                        : 'TERBANYAK: JAM --';
                    $('#avgDailyCheckinPos1Badge').text(peakText);
                    $('#totalCheckinsPos1Badge').text((pos1Data.total_checkins || 0).toLocaleString('id-ID'));

                    const bCount = (pos1Data.total_bongkaran || 0).toLocaleString('id-ID');
                    const mCount = (pos1Data.total_muatan || 0).toLocaleString('id-ID');
                    const rCount = (pos1Data.total_retur || 0).toLocaleString('id-ID');
                    $('#periodDaysLabelPos1').html(
                        `<span style="color:#06b6d4;" class="fw-semibold" title="Bongkaran">Bongkar: ${bCount}</span> &bull; ` +
                        `<span style="color:#10b981;" class="fw-semibold" title="Muatan">Muat: ${mCount}</span> &bull; ` +
                        `<span style="color:#f59e0b;" class="fw-semibold" title="Retur">Retur: ${rCount}</span>`
                    );

                    const optionsPos1 = {
                        series: [{
                                name: 'BONGKARAN',
                                data: bongkarCounts
                            },
                            {
                                name: 'MUATAN',
                                data: muatCounts
                            },
                            {
                                name: 'RETUR',
                                data: returCounts
                            }
                        ],
                        chart: {
                            height: 250,
                            type: 'bar',
                            stacked: true,
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#06b6d4', '#10b981', '#f59e0b'],
                        plotOptions: {
                            bar: {
                                borderRadius: 2,
                                columnWidth: '55%',
                                dataLabels: {
                                    total: {
                                        enabled: true,
                                        style: {
                                            fontSize: '8px',
                                            fontFamily: 'JetBrains Mono',
                                            fontWeight: 700,
                                            color: '#e2e8f0'
                                        },
                                        formatter: val => (val > 0 ? val : ''),
                                        offsetY: -4
                                    }
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: val => (val > 0 ? val : ''),
                            style: {
                                fontSize: '7.5px',
                                fontFamily: 'JetBrains Mono',
                                fontWeight: 600,
                                colors: ['#ffffff']
                            },
                            dropShadow: {
                                enabled: true,
                                top: 1,
                                left: 1,
                                blur: 1,
                                color: '#000',
                                opacity: 0.9
                            }
                        },
                        stroke: {
                            width: 1,
                            colors: ['#0b1426']
                        },
                        xaxis: {
                            categories: labels,
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '8.5px',
                                    fontFamily: 'JetBrains Mono'
                                },
                                rotate: -45
                            },
                            axisBorder: {
                                color: 'rgba(255,255,255,0.06)'
                            }
                        },
                        yaxis: {
                            max: max => (max > 0 ? Math.ceil(max * 1.15) : undefined),
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
                            labels: {
                                colors: '#94a3b8'
                            },
                            fontSize: '9.5px',
                            fontFamily: 'JetBrains Mono'
                        },
                        tooltip: {
                            theme: 'dark',
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: val => `${val || 0} Truk`
                            }
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)',
                            strokeDashArray: 3
                        }
                    };

                    if (chartDailyPos1) chartDailyPos1.destroy();
                    $('#chartDailyCheckinPos1').empty();
                    chartDailyPos1 = new ApexCharts(document.querySelector("#chartDailyCheckinPos1"), optionsPos1);
                    chartDailyPos1.render();
                }

                // 1.3 Tren Check-in Timbangan Harian & Rata-rata
                function renderDailyCheckinTimbangan(dailyData) {
                    if (!dailyData || !dailyData.series || dailyData.series.length === 0) {
                        $('#chartDailyCheckin').html(
                            '<div class="text-center text-muted py-5 fs-11">TIDAK ADA DATA KEDATANGAN TIMBANGAN</div>'
                        );
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
                        series: [{
                                name: 'CHECK-IN TIMBANGAN',
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
                            toolbar: {
                                show: false
                            },
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
                                columnWidth: '50%',
                                dataLabels: {
                                    position: 'top'
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            enabledOnSeries: [0],
                            formatter: val => (val > 0 ? val : ''),
                            offsetY: -6,
                            style: {
                                fontSize: '8px',
                                fontFamily: 'JetBrains Mono',
                                fontWeight: 600,
                                colors: ['#38bdf8']
                            },
                            background: {
                                enabled: false
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
                            max: max => (max > 0 ? Math.ceil(max * 1.15) : undefined),
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
                            labels: {
                                colors: '#94a3b8'
                            },
                            fontSize: '9.5px',
                            fontFamily: 'JetBrains Mono'
                        },
                        tooltip: {
                            theme: 'dark',
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: (val, opt) => {
                                    if (opt.seriesIndex === 0) return `${val} Kendaraan`;
                                    return `${val} Kendaraan / Hari`;
                                }
                            }
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)',
                            strokeDashArray: 3
                        }
                    };

                    if (chartDailyTmb) chartDailyTmb.destroy();
                    $('#chartDailyCheckin').empty();
                    chartDailyTmb = new ApexCharts(document.querySelector("#chartDailyCheckin"), optionsDaily);
                    chartDailyTmb.render();
                }

                // 1.4 Tren Pembatalan Truk Harian (Batal Bongkar & Batal Muat)
                function renderDailyCancelled(cancelledData) {
                    if (!cancelledData || !cancelledData.series || cancelledData.series.length === 0) {
                        $('#chartDailyCancelled').html(
                            '<div class="text-center text-muted py-5 fs-11">TIDAK ADA DATA PEMBATALAN TRUK</div>'
                        );
                        return;
                    }

                    const labels = cancelledData.series.map(d => d.label);
                    const bongkarCounts = cancelledData.series.map(d => d.batal_bongkar || 0);
                    const muatCounts = cancelledData.series.map(d => d.batal_muat || 0);

                    $('#totalCancelledBadge').text(
                        `TOTAL: ${(cancelledData.total_cancelled || 0).toLocaleString('id-ID')} TRUK`);
                    $('#totalBatalBongkarBadge').text((cancelledData.total_batal_bongkar || 0).toLocaleString('id-ID'));
                    $('#totalBatalMuatBadge').text((cancelledData.total_batal_muat || 0).toLocaleString('id-ID'));
                    $('#periodDaysLabelCancelled').text(`${cancelledData.total_days || 0} Hari Terakhir`);

                    const optionsCancelled = {
                        series: [{
                                name: 'BATAL BONGKAR',
                                type: 'column',
                                data: bongkarCounts
                            },
                            {
                                name: 'BATAL MUAT',
                                type: 'column',
                                data: muatCounts
                            }
                        ],
                        chart: {
                            height: 250,
                            type: 'bar',
                            stacked: true,
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#ef4444', '#f59e0b'],
                        stroke: {
                            width: 1,
                            colors: ['#0b1426']
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 3,
                                columnWidth: '50%'
                            }
                        },
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
                            labels: {
                                colors: '#94a3b8'
                            },
                            fontSize: '9.5px',
                            fontFamily: 'JetBrains Mono'
                        },
                        tooltip: {
                            theme: 'dark',
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: val => `${val} Truk`
                            }
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)',
                            strokeDashArray: 3
                        }
                    };

                    if (chartDailyCancelled) chartDailyCancelled.destroy();
                    $('#chartDailyCancelled').empty();
                    chartDailyCancelled = new ApexCharts(document.querySelector("#chartDailyCancelled"),
                        optionsCancelled);
                    chartDailyCancelled.render();
                }

                // 2.1 Durasi Bongkar / Muat Riil per Dock
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
                                distributed: true,
                                dataLabels: {
                                    position: 'bottom'
                                }
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
                                maxWidth: 140
                            }
                        },
                        legend: {
                            show: false
                        },
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

                // 2.2 Truk Paling Sering Berkunjung
                function renderFrequentTrucks(trucks) {
                    const tbody = $('#frequentTrucksTableBody');
                    tbody.empty();

                    if (!trucks || trucks.length === 0) {
                        tbody.append(
                            '<tr><td colspan="4" class="text-center text-muted py-4 fs-11">TIDAK ADA DATA KUNJUNGAN KENDARAAN</td></tr>'
                        );
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

                // 2.3 Tonase & Truk per Area
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
                                name: 'KENDARAAN SELESAI',
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
                                columnWidth: '40%',
                                dataLabels: {
                                    position: 'top'
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            enabledOnSeries: [0, 1],
                            formatter: (val, opt) => {
                                if (val === undefined || val === null) return '';
                                if (opt.seriesIndex === 0) {
                                    return val > 0 ? Math.round(val).toLocaleString('id-ID') + ' T' : '';
                                }
                                return val > 0 ? Math.round(val) + ' Truk' : '';
                            },
                            offsetY: -5,
                            style: {
                                fontSize: '9px',
                                fontFamily: 'JetBrains Mono',
                                fontWeight: 700,
                                colors: ['#60a5fa', '#34d399']
                            },
                            background: {
                                enabled: false
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
                                max: max => (max > 0 ? Math.ceil(max * 1.15) : undefined),
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
                                max: max => (max > 0 ? Math.ceil(max * 1.15) : undefined),
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
                            shared: true,
                            intersect: false
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

                // 3.1 Distribusi Jenis Truk (Bongkaran, Muatan, Retur)
                function renderFleetSection(c4) {
                    const fleets = c4.fleet_distribution;
                    if (!fleets || fleets.length === 0) {
                        $('#chartFleetTypes').html(
                            '<div class="text-center text-muted py-5 fs-11">TIDAK ADA DATA KENDARAAN</div>');
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
                            colors: ['#3b82f6', '#10b981', '#f59e0b', '#06b6d4'],
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

                // 3.1b Tren Jenis Truk Harian (Bongkaran, Muatan, Retur)
                function renderFleetTrendChart(trendData) {
                    if (!trendData || !trendData.series || trendData.series.length === 0) {
                        $('#chartFleetTrend').html(
                            '<div class="text-center text-muted py-5 fs-11">TIDAK ADA DATA TREN JENIS TRUK</div>'
                        );
                        return;
                    }

                    const labels = trendData.series.map(d => d.label);
                    const bongkarCounts = trendData.series.map(d => d.bongkaran || 0);
                    const muatCounts = trendData.series.map(d => d.muatan || 0);
                    const returCounts = trendData.series.map(d => d.retur || 0);

                    $('#totalFleetTrendBadge').text(
                        `TOTAL: ${(trendData.total_all || 0).toLocaleString('id-ID')} TRUK`);
                    $('#trendBongkarBadge').text((trendData.total_bongkaran || 0).toLocaleString('id-ID'));
                    $('#trendMuatBadge').text((trendData.total_muatan || 0).toLocaleString('id-ID'));
                    $('#trendReturBadge').text((trendData.total_retur || 0).toLocaleString('id-ID'));
                    $('#periodDaysLabelFleetTrend').text(`${trendData.total_days || 0} Hari Terakhir`);

                    const optionsTrend = {
                        series: [{
                                name: 'BONGKARAN',
                                type: 'column',
                                data: bongkarCounts
                            },
                            {
                                name: 'MUATAN',
                                type: 'column',
                                data: muatCounts
                            },
                            {
                                name: 'RETUR',
                                type: 'column',
                                data: returCounts
                            }
                        ],
                        chart: {
                            height: 220,
                            type: 'bar',
                            stacked: true,
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#3b82f6', '#10b981', '#f59e0b'],
                        stroke: {
                            width: 1,
                            colors: ['#0b1426']
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 3,
                                columnWidth: '55%'
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
                            labels: {
                                colors: '#94a3b8'
                            },
                            fontSize: '9.5px',
                            fontFamily: 'JetBrains Mono'
                        },
                        tooltip: {
                            theme: 'dark',
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: val => `${val} Truk`
                            }
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)',
                            strokeDashArray: 3
                        }
                    };

                    if (chartFleetTrend) chartFleetTrend.destroy();
                    $('#chartFleetTrend').empty();
                    chartFleetTrend = new ApexCharts(document.querySelector("#chartFleetTrend"), optionsTrend);
                    chartFleetTrend.render();
                }

                // 3.2 Detail Data Vendor Melebihi Truck Duration (> 7.5 Jam)
                function renderVendorOverDurationTable(vendorList) {
                    const tbody = $('#vendorOverDurationTableBody');
                    tbody.empty();

                    if (!vendorList || vendorList.length === 0) {
                        tbody.append(
                            '<tr><td colspan="6" class="text-center text-muted py-4 fs-11">SEMUA VENDOR MEMENUHI TARGET DURATION (&le; 7.5 JAM)</td></tr>'
                        );
                        return;
                    }

                    vendorList.forEach(v => {
                        let statusBadge = '<span class="status-pill green">SESUAI</span>';
                        if (v.status === 'Kritis' || (v.over_sla_count && v.over_sla_count > 0 && v
                                .over_sla_rate >= 50)) {
                            statusBadge = '<span class="status-pill red">KRITIS</span>';
                        } else if (v.status === 'Waspada' || (v.over_sla_count && v.over_sla_count > 0)) {
                            statusBadge = '<span class="status-pill amber">WASPADA</span>';
                        }

                        const overCount = v.over_sla_count !== undefined ? v.over_sla_count : (v.over_count ||
                            0);
                        const overRate = v.over_sla_rate !== undefined ? v.over_sla_rate : (v.over_rate || 0);
                        const trips = v.total_trips || v.trips || 0;
                        const avgTat = v.avg_duration_formatted || v.avg_tat_formatted || '--';

                        const row = `
                            <tr>
                                <td><div class="fw-bold text-white fs-11">${v.vendor}</div></td>
                                <td class="text-center mono fw-bold text-danger fs-12">${overCount}</td>
                                <td class="text-center mono fw-bold text-white">${trips}</td>
                                <td class="text-center mono fw-bold ${overRate > 0 ? 'text-warning' : 'text-success'}">${overRate}%</td>
                                <td><span class="mono fw-bold text-info">${avgTat}</span></td>
                                <td class="text-center">${statusBadge}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }

                // 3.3 Truck Duration Mingguan
                function renderWeeklyDurationChart(weeklyData) {
                    if (!weeklyData || weeklyData.length === 0) {
                        $('#chartWeeklyDuration').html(
                            '<div class="text-center text-muted py-5 fs-11">BELUM ADA DATA MINGGUAN KENDARAAN</div>'
                        );
                        return;
                    }

                    const labels = weeklyData.map(w => w.label || w.week_label || w.short_label || 'Mgg');
                    const avgHours = weeklyData.map(w => w.avg_duration_hours || 0);
                    const overCounts = weeklyData.map(w => w.over_duration_count !== undefined ? w.over_duration_count :
                        (w.over_sla_count || 0));

                    const optionsWeekly = {
                        series: [{
                                name: 'RERATA DURASI (JAM)',
                                type: 'column',
                                data: avgHours
                            },
                            {
                                name: 'TRUK > 7.5 JAM',
                                type: 'line',
                                data: overCounts
                            }
                        ],
                        chart: {
                            height: 240,
                            type: 'line',
                            toolbar: {
                                show: false
                            },
                            background: 'transparent'
                        },
                        colors: ['#0284c7', '#ef4444'],
                        stroke: {
                            width: [0, 2],
                            curve: 'smooth'
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 3,
                                columnWidth: '45%',
                                dataLabels: {
                                    position: 'top'
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            enabledOnSeries: [0, 1],
                            formatter: (val, opt) => {
                                if (val === undefined || val === null) return '';
                                if (opt.seriesIndex === 0) {
                                    return val > 0 ? `${val}j` : '';
                                }
                                return val > 0 ? `${val}` : '';
                            },
                            offsetY: -5,
                            style: {
                                fontSize: '9px',
                                fontFamily: 'JetBrains Mono',
                                fontWeight: 700,
                                colors: ['#38bdf8', '#ef4444']
                            },
                            background: {
                                enabled: false
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
                                rotate: -30
                            },
                            axisBorder: {
                                color: 'rgba(255,255,255,0.06)'
                            }
                        },
                        yaxis: [{
                                max: max => (max > 0 ? Math.ceil(max * 1.15) : undefined),
                                title: {
                                    text: 'DURASI (JAM)',
                                    style: {
                                        color: '#0284c7',
                                        fontSize: '9px'
                                    }
                                },
                                labels: {
                                    style: {
                                        colors: '#0284c7',
                                        fontSize: '9.5px',
                                        fontFamily: 'JetBrains Mono'
                                    },
                                    formatter: val => val + 'j'
                                }
                            },
                            {
                                opposite: true,
                                max: max => (max > 0 ? Math.ceil(max * 1.15) : undefined),
                                title: {
                                    text: 'TRUK OVER',
                                    style: {
                                        color: '#ef4444',
                                        fontSize: '9px'
                                    }
                                },
                                labels: {
                                    style: {
                                        colors: '#ef4444',
                                        fontSize: '9.5px',
                                        fontFamily: 'JetBrains Mono'
                                    },
                                    formatter: val => Math.round(val)
                                }
                            }
                        ],
                        tooltip: {
                            theme: 'dark',
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: (val, opt) => {
                                    const w = weeklyData[opt.dataPointIndex];
                                    if (opt.seriesIndex === 0) {
                                        return `${val} Jam (${w ? w.avg_duration_formatted : val + 'j'})`;
                                    }
                                    return `${val} Truk melebihi 7.5 Jam (${w ? w.over_duration_rate : 0}%)`;
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            labels: {
                                colors: '#94a3b8'
                            },
                            fontSize: '9.5px',
                            fontFamily: 'JetBrains Mono'
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)',
                            strokeDashArray: 3
                        }
                    };

                    if (chartWeekly) chartWeekly.destroy();
                    $('#chartWeeklyDuration').empty();
                    chartWeekly = new ApexCharts(document.querySelector("#chartWeeklyDuration"), optionsWeekly);
                    chartWeekly.render();
                }

                // 4.1 Produktivitas Petugas (Nama Lengkap / Username)
                function renderOperatorSection(operators) {
                    const tbodyOp = $('#operatorTableBody');
                    tbodyOp.empty();

                    let list = [];
                    if (Array.isArray(operators)) {
                        list = operators;
                    } else if (operators && Array.isArray(operators.operator_list)) {
                        list = operators.operator_list;
                    } else if (operators && Array.isArray(operators.operators)) {
                        list = operators.operators;
                    } else if (operators && typeof operators === 'object') {
                        list = Object.values(operators).find(v => Array.isArray(v)) || [];
                    }

                    if (!list || list.length === 0) {
                        tbodyOp.append(
                            '<tr><td colspan="4" class="text-center text-muted py-4 fs-11">BELUM ADA RIWAYAT AKTIVITAS PETUGAS</td></tr>'
                        );
                        return;
                    }

                    list.forEach(op => {
                        const displayName = op.user_name || op.nama_lengkap || 'PETUGAS';
                        const usernameTag = op.username ?
                            `<small class="text-muted d-block mono fs-10 mt-0.5">@${op.username}</small>` : '';

                        const row = `
                        <tr>
                            <td>
                                <div class="fw-bold text-white fs-11">${displayName.toUpperCase()}</div>
                                ${usernameTag}
                            </td>
                            <td><span class="status-pill green">${(op.pos || '-').toUpperCase()}</span></td>
                            <td class="text-center mono fw-bold text-white fs-12">${op.total_tickets}</td>
                            <td><span class="mono fw-semibold text-info fs-11">${op.avg_duration_formatted ? op.avg_duration_formatted.toUpperCase() : '--'}</span></td>
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
