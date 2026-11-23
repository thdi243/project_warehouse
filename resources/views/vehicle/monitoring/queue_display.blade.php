<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ function_exists('csrf_token') && request()->hasSession() ? csrf_token() : '' }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Display Antrian Kendaraan & Loading Dock — PT BAS</title>

        <link rel="shortcut icon" href="{{ asset('assets/images/logo/kecap.png') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700;800&display=swap"
            rel="stylesheet">

        <link href="{{ asset('material/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Laravel Reverb & Echo (Realtime WebSocket) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

        <style>
            :root {
                --bg-page: #f1f5f9;
                --bg-card: #ffffff;
                --border-subtle: #e2e8f0;
                --border-card: #cbd5e1;
                --text-main: #0f172a;
                --text-sub: #64748b;
                --color-success: #16a34a;
                --color-warning: #d97706;
                --color-danger: #dc2626;
                --color-info: #2563eb;
                --color-purple: #7c3aed;
            }

            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                background-color: var(--bg-page);
                color: var(--text-main);
                font-family: 'Outfit', sans-serif;
                min-height: 100vh;
                overflow-x: hidden;
            }

            .mono {
                font-family: 'JetBrains Mono', monospace;
            }

            /* Top Bar */
            .top-navbar {
                background: #ffffff;
                border-bottom: 1px solid var(--border-subtle);
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
                padding: 0 14px;
                height: 38px;
                max-height: 38px;
                display: flex;
                align-items: center;
                position: sticky;
                top: 0;
                z-index: 100;
                overflow: hidden;
            }

            .brand-logo {
                height: 20px;
                width: auto;
                object-fit: contain;
            }

            /* Area Switch Tabs */
            .area-pill-group {
                background: #e2e8f0;
                border-radius: 20px;
                padding: 2px;
                display: inline-flex;
                gap: 2px;
            }

            .area-pill-btn {
                padding: 2px 10px;
                border-radius: 20px;
                font-weight: 700;
                font-size: 11px;
                color: #475569;
                border: none;
                background: transparent;
                transition: all 0.25s ease;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                cursor: pointer;
                white-space: nowrap;
            }

            .area-pill-btn:hover {
                color: #0f172a;
                background: rgba(255, 255, 255, 0.6);
            }

            .area-pill-btn.active {
                color: #ffffff;
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
            }

            .area-pill-btn.active.area-wrm {
                background: linear-gradient(135deg, #059669 0%, #047857 100%);
                box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);
            }

            /* Clean White Cards */
            .content-card {
                background: #ffffff;
                border: 1px solid var(--border-subtle);
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
                transition: all 0.25s ease;
            }

            .content-card:hover {
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
            }

            /* Slot Boxes inside Dock */
            .slot-box {
                border-radius: 10px;
                padding: 10px 14px;
                min-height: 64px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                transition: all 0.3s ease;
                position: relative;
            }

            .slot-box.available {
                border: 2px dashed #22c55e;
                background: #f0fdf4;
            }

            .slot-box.occupied {
                border: 1.5px solid #93c5fd;
                background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
                box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);
            }

            /* Badges */
            .badge-plat {
                color: #0f172a;
                font-weight: 900;
                letter-spacing: 1.5px;
                font-size: 22px;
                border: none;
                background: transparent;
                box-shadow: none;
                padding: 0;
                display: inline-block;
                line-height: 1.2;
            }

            .badge-queue-no {
                background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
                color: #ffffff;
                font-weight: 900;
                font-size: 18px;
                padding: 4px 14px;
                border-radius: 8px;
                box-shadow: 0 3px 8px rgba(217, 119, 6, 0.3);
                display: inline-block;
            }

            /* Table custom (Clean Light) */
            .queue-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0 8px;
            }

            .queue-table thead th {
                color: #475569;
                background: #f8fafc;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 12px 16px;
                font-weight: 700;
                border-top: 1px solid var(--border-subtle);
                border-bottom: 2px solid var(--border-subtle);
            }

            .queue-table tbody tr {
                background: #ffffff;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
                border: 1px solid var(--border-subtle);
                transition: all 0.2s ease;
            }

            .queue-table tbody tr:hover {
                background: #f8fafc;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            .queue-table tbody td {
                padding: 14px 16px;
                vertical-align: middle;
                border-top: 1px solid #f1f5f9;
                border-bottom: 1px solid #f1f5f9;
            }

            .queue-table tbody tr td:first-child {
                border-top-left-radius: 10px;
                border-bottom-left-radius: 10px;
                border-left: 1px solid var(--border-subtle);
            }

            .queue-table tbody tr td:last-child {
                border-top-right-radius: 10px;
                border-bottom-right-radius: 10px;
                border-right: 1px solid var(--border-subtle);
            }

            /* Pulse live dot */
            .live-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background-color: #16a34a;
                box-shadow: 0 0 10px #16a34a;
                animation: pulse-live 1.8s infinite;
                display: inline-block;
            }

            @keyframes pulse-live {

                0%,
                100% {
                    transform: scale(1);
                    opacity: 1;
                }

                50% {
                    transform: scale(1.35);
                    opacity: 0.4;
                }
            }

            /* Category badge colors */
            .badge-cat-slipsheet {
                background: #dbeafe;
                color: #1d4ed8;
                border: 1px solid #93c5fd;
            }

            .badge-cat-curah {
                background: #fef3c7;
                color: #b45309;
                border: 1px solid #fcd34d;
            }

            .badge-cat-bongkaran {
                background: #ede9fe;
                color: #6d28d9;
                border: 1px solid #c4b5fd;
            }

            /* TV Controls */
            .btn-ctrl {
                background: #f1f5f9;
                border: 1px solid var(--border-subtle);
                color: #334155;
                padding: 2px 8px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                transition: all 0.2s ease;
                cursor: pointer;
                white-space: nowrap;
            }

            .btn-ctrl:hover {
                background: #e2e8f0;
                color: #0f172a;
            }

            /* Mobile Adjustments */
            @media (max-width: 768px) {
                .top-navbar {
                    padding: 0 8px;
                    height: 38px;
                }

                .badge-plat {
                    font-size: 18px;
                    padding: 0;
                }

                .badge-queue-no {
                    font-size: 15px;
                    padding: 3px 10px;
                }

                .hide-mobile {
                    display: none !important;
                }
            }
        </style>
    </head>

    <body>
        <!-- Top Navigation -->
        <header class="top-navbar">
            <div class="container-fluid d-flex flex-nowrap align-items-center justify-content-between p-0 gap-2">
                <!-- Brand & Title -->
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <img src="{{ asset('assets/images/logo/kecap.png') }}" alt="PT BAS" class="brand-logo">
                    <span class="fw-bold text-dark text-nowrap" style="font-size: 12px; letter-spacing: 0.3px;">ANTRIAN GUDANG</span>
                    <span class="badge bg-primary text-uppercase" style="font-size: 9px; padding: 2px 5px;">TV</span>
                </div>

                <!-- Area Switch Selector (WFG&SMU vs WRM&WPM) -->
                <div class="area-pill-group flex-shrink-0">
                    <button type="button" class="area-pill-btn {{ $area === 'wfg' ? 'active' : '' }}"
                        onclick="switchArea('wfg')">
                        <i class="ri-truck-line"></i>
                        <span>WFG & SMU</span>
                    </button>
                    <button type="button" class="area-pill-btn area-wrm {{ $area === 'wrm' ? 'active area-wrm' : '' }}"
                        onclick="switchArea('wrm')">
                        <i class="ri-archive-line"></i>
                        <span>WRM & WPM</span>
                    </button>
                </div>

                <!-- Clock, Status, & TV Fullscreen Actions -->
                <div class="d-flex align-items-center gap-2 flex-shrink-0 text-nowrap">
                    <!-- Live status -->
                    <div class="d-flex align-items-center gap-1 px-2 py-0.5 rounded-pill"
                        style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                        <span class="live-dot" style="width: 7px; height: 7px;"></span>
                        <span class="fw-bold text-success" id="ws-status-text"
                            style="font-size: 10px; letter-spacing: 0.3px;">LIVE</span>
                        <span class="text-secondary" style="font-size: 10px;">• <span
                                id="refresh-timer">10</span>s</span>
                    </div>

                    <!-- Clock & Date -->
                    <div class="text-end d-flex align-items-baseline gap-1">
                        <span id="live-clock" class="mono fw-bold text-dark lh-1" style="font-size: 12px;">--:--:--</span>
                        <small id="live-date" class="text-secondary d-none d-xl-inline" style="font-size: 10px;">--</small>
                    </div>

                    <!-- Chime Audio Toggle -->
                    <button type="button" class="btn-ctrl" id="btn-audio-toggle" onclick="toggleAudio()"
                        title="Suara Panggilan">
                        <i class="ri-volume-up-line text-primary" id="audio-icon" style="font-size: 12px;"></i>
                        <span id="audio-text">Suara: ON</span>
                    </button>

                    <!-- Fullscreen Toggle -->
                    <button type="button" class="btn-ctrl" onclick="toggleFullscreen()" title="Fullscreen Mode (TV)">
                        <i class="ri-fullscreen-line" style="font-size: 12px;"></i>
                        <span>Layar Penuh</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Container -->
        <main class="container-fluid py-1 px-lg-3">

            <!-- ======================================================== -->
            <!-- VIEW 1: WFG & SMU (FINISH GOODS & SMU) -->
            <!-- ======================================================== -->
            <div id="view-wfg" style="{{ $area === 'wfg' ? '' : 'display: none;' }}">
                <!-- Dock Quota & Slot Status (3 Columns) -->
                <div class="row g-2 mb-2">
                    <!-- 1. Dock Slipsheet (Kuota: 2 Mobil) -->
                    <div class="col-lg-4 col-md-6">
                        <div class="content-card p-2 px-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary p-1 fs-6"><i class="ri-truck-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">Dock Slipsheet</h6>
                                        <small class="text-secondary" style="font-size: 11px;">Kuota: 2 • <span class="text-warning fw-bold">Antrian: <span id="count-queue-slipsheet">0</span></span></small>
                                    </div>
                                </div>
                                <span id="badge-slipsheet-status" class="badge bg-success-subtle text-success fs-7 px-2 py-1 fw-bold">
                                    Terisi: <span id="slipsheet-occupied">0</span>/2
                                </span>
                            </div>

                            <!-- 2 Slots Slipsheet (Side by Side) -->
                            <div class="row g-2" id="slots-slipsheet">
                                <div class="col-12 slot-box available text-center py-2">
                                    <span class="text-secondary small">Memuat data slot...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Dock Curah (Kuota: 4 Mobil) -->
                    <div class="col-lg-4 col-md-6">
                        <div class="content-card p-2 px-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-warning p-1 fs-6 text-dark"><i class="ri-stack-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">Dock Curah</h6>
                                        <small class="text-secondary" style="font-size: 11px;">Kuota: 4 • <span class="text-warning fw-bold">Antrian: <span id="count-queue-curah">0</span></span></small>
                                    </div>
                                </div>
                                <span id="badge-curah-status" class="badge bg-success-subtle text-success fs-7 px-2 py-1 fw-bold">
                                    Terisi: <span id="curah-occupied">0</span>/4
                                </span>
                            </div>

                            <!-- 4 Slots Curah (2x2 Grid) -->
                            <div class="row g-2" id="slots-curah">
                                <div class="col-12 slot-box available text-center py-2">
                                    <span class="text-secondary small">Memuat data slot...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Dock Bongkaran & SMU (Kuota: 2 Mobil) -->
                    <div class="col-lg-4 col-md-12">
                        <div class="content-card p-2 px-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge p-1 fs-6 text-white" style="background: #7c3aed;"><i class="ri-inbox-unarchive-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">Dock Bongkaran & SMU</h6>
                                        <small class="text-secondary" style="font-size: 11px;">Kuota: 2 • <span class="text-warning fw-bold">Antrian: <span id="count-queue-bongkaran">0</span></span></small>
                                    </div>
                                </div>
                                <span id="badge-bongkaran-status" class="badge bg-success-subtle text-success fs-7 px-2 py-1 fw-bold">
                                    Terisi: <span id="bongkaran-occupied">0</span>/2
                                </span>
                            </div>

                            <!-- 2 Slots Bongkaran & SMU (Side by Side) -->
                            <div class="row g-2" id="slots-bongkaran">
                                <div class="col-12 slot-box available text-center py-2">
                                    <span class="text-secondary small">Memuat data slot...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Antrian Menunggu (Standby Parkir) -->
                <div class="content-card p-2 px-3 mt-1">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-parking-box-line fs-5 text-warning"></i>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 13px;">Antrian Menunggu di Kantong Parkir (WFG & SMU)</h6>
                        </div>

                        <span class="badge bg-light border border-secondary border-opacity-25 text-dark px-2 py-1" style="font-size: 11px;">
                            Total Menunggu: <strong id="total-wfg-waiting" class="text-primary mono">0</strong> Truk
                        </span>
                    </div>

                    <!-- 3 Kolom Antrian Sesuai Jenis (Slipsheet | Curah | Bongkaran) -->
                    <div class="row g-2">
                        <!-- Kolom 1: Slipsheet -->
                        <div class="col-lg-4 col-md-6">
                            <div class="p-2 rounded-2 border bg-light h-100 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-primary p-1"><i class="ri-truck-fill"></i></span>
                                        <span class="fw-bold text-dark" style="font-size: 13px;">Slipsheet</span>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 11px;" id="badge-col-queue-slipsheet">0 Truk</span>
                                </div>
                                <div id="col-queue-slipsheet" class="d-flex flex-column gap-1"
                                    style="max-height: calc(100vh - 290px); min-height: 250px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian slipsheet...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 2: Curah -->
                        <div class="col-lg-4 col-md-6">
                            <div class="p-2 rounded-2 border bg-light h-100 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-warning text-dark p-1"><i class="ri-stack-fill"></i></span>
                                        <span class="fw-bold text-dark" style="font-size: 13px;">Curah</span>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning fw-bold" style="font-size: 11px;" id="badge-col-queue-curah">0 Truk</span>
                                </div>
                                <div id="col-queue-curah" class="d-flex flex-column gap-1"
                                    style="max-height: calc(100vh - 290px); min-height: 250px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian curah...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 3: Bongkaran & SMU -->
                        <div class="col-lg-4 col-md-12">
                            <div class="p-2 rounded-2 border bg-light h-100 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge text-white p-1" style="background: #7c3aed;"><i class="ri-inbox-unarchive-fill"></i></span>
                                        <span class="fw-bold text-dark" style="font-size: 13px;">Bongkaran (& SMU)</span>
                                    </div>
                                    <span class="badge fw-bold" style="background: #ede9fe; color: #6d28d9; font-size: 11px;" id="badge-col-queue-bongkaran">0 Truk</span>
                                </div>
                                <div id="col-queue-bongkaran" class="d-flex flex-column gap-1"
                                    style="max-height: calc(100vh - 290px); min-height: 250px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian bongkaran...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- VIEW 2: WRM & WPM (RAW MATERIAL & PACKAGING) -->
            <!-- ======================================================== -->
            <div id="view-wrm" style="{{ $area === 'wrm' ? '' : 'display: none;' }}">
                <div class="row g-2 mb-2">
                    <!-- 1. Active Docks in WRM & WPM -->
                    <div class="col-lg-6">
                        <div class="content-card p-2 px-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success p-1 fs-6"><i class="ri-inbox-archive-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">Dock WRM (Bahan Baku)</h6>
                                        <small class="text-secondary" style="font-size: 11px;">Truk Sedang Proses Bongkar</small>
                                    </div>
                                </div>
                                <span id="badge-wrm-active-count" class="badge bg-primary fs-7 px-2 py-1 fw-bold">
                                    Aktif: <span id="wrm-active-count">0</span> Truk
                                </span>
                            </div>

                            <div id="wrm-active-container" class="d-flex flex-column gap-1" style="max-height: calc(100vh - 450px); overflow-y: auto;">
                                <div class="slot-box available text-center py-2">
                                    <span class="text-secondary small">Memuat data aktif WRM...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="content-card p-2 px-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info p-1 fs-6 text-white"><i class="ri-archive-stack-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;">Dock WPM (Bahan Kemasan)</h6>
                                        <small class="text-secondary" style="font-size: 11px;">Truk Sedang Proses Muat / Bongkar</small>
                                    </div>
                                </div>
                                <span id="badge-wpm-active-count" class="badge bg-primary fs-7 px-2 py-1 fw-bold">
                                    Aktif: <span id="wpm-active-count">0</span> Truk
                                </span>
                            </div>

                            <div id="wpm-active-container" class="d-flex flex-column gap-1" style="max-height: calc(100vh - 450px); overflow-y: auto;">
                                <div class="slot-box available text-center py-2">
                                    <span class="text-secondary small">Memuat data aktif WPM...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Antrian Menunggu di Kantong Parkir (WRM & WPM) per Kolom -->
                <div class="content-card p-2 px-3 mt-1">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-parking-box-line fs-5 text-warning"></i>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 13px;">Antrian Menunggu di Kantong Parkir (WRM & WPM)</h6>
                        </div>

                        <span class="badge bg-light border border-secondary border-opacity-25 text-dark px-2 py-1" style="font-size: 11px;">
                            Total Menunggu: <strong id="total-wrm-wpm-waiting" class="text-primary mono">0</strong> Truk
                        </span>
                    </div>

                    <!-- 2 Kolom Antrian Sesuai Jenis (WRM | WPM) -->
                    <div class="row g-2">
                        <!-- Kolom 1: WRM (Bahan Baku) -->
                        <div class="col-lg-6">
                            <div class="p-2 rounded-2 border bg-light h-100 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-success p-1"><i class="ri-inbox-archive-fill"></i></span>
                                        <span class="fw-bold text-dark" style="font-size: 13px;">Antrian WRM (Bahan Baku)</span>
                                    </div>
                                    <span class="badge bg-success-subtle text-success fw-bold" style="font-size: 11px;" id="badge-col-queue-wrm">0 Truk</span>
                                </div>
                                <div id="col-queue-wrm" class="d-flex flex-column gap-1"
                                    style="max-height: calc(100vh - 290px); min-height: 250px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian WRM...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 2: WPM (Bahan Kemasan) -->
                        <div class="col-lg-6">
                            <div class="p-2 rounded-2 border bg-light h-100 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-info p-1 text-white"><i class="ri-archive-stack-fill"></i></span>
                                        <span class="fw-bold text-dark" style="font-size: 13px;">Antrian WPM (Bahan Kemasan)</span>
                                    </div>
                                    <span class="badge bg-info-subtle text-info fw-bold" style="font-size: 11px;" id="badge-col-queue-wpm">0 Truk</span>
                                </div>
                                <div id="col-queue-wpm" class="d-flex flex-column gap-1"
                                    style="max-height: calc(100vh - 290px); min-height: 250px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian WPM...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Footer Info / Instruksi Supir -->
        <footer class="py-1 px-4 border-top mt-1" style="background: #ffffff;">
            <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between gap-2 text-secondary"
                style="font-size: 11px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-information-line text-warning fs-6"></i>
                    <span><strong>Perhatian Supir:</strong> Pastikan nomor WhatsApp selalu aktif untuk menerima informasi giliran masuk loading dock.</span>
                </div>
                <div>
                    <span>PT BAS Warehouse Queue System &copy; {{ date('Y') }}</span>
                </div>
            </div>
        </footer>

        <!-- Bootstrap & Icons JS -->
        <script src="{{ asset('material/assets/js/bootstrap.bundle.min.js') }}"></script>

        <script>
            // State variables
            let currentArea = "{{ $area ?? 'wfg' }}";
            let previousCalledIds = new Set();
            let soundEnabled = true;
            let refreshSeconds = 5;
            let refreshTimerInterval = null;

            // Clock & Date updater
            function updateClock() {
                const now = new Date();
                const timeStr = now.toLocaleTimeString('id-ID', {
                    hour12: false,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                const dateStr = now.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });

                const clockEl = document.getElementById('live-clock');
                const dateEl = document.getElementById('live-date');
                if (clockEl) clockEl.innerText = timeStr;
                if (dateEl) dateEl.innerText = dateStr;
            }
            setInterval(updateClock, 1000);
            updateClock();

            // Synthesize pleasant chime using Web Audio API
            function playChime() {
                if (!soundEnabled) return;
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    const ctx = new AudioContext();

                    const playTone = (freq, time, duration) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(freq, ctx.currentTime + time);
                        gain.gain.setValueAtTime(0, ctx.currentTime + time);
                        gain.gain.linearRampToValueAtTime(0.3, ctx.currentTime + time + 0.05);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + time + duration);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(ctx.currentTime + time);
                        osc.stop(ctx.currentTime + time + duration);
                    };

                    playTone(587.33, 0.0, 0.8); // D5
                    playTone(880.00, 0.2, 1.2); // A5
                } catch (e) {
                    console.log('Audio chime error:', e);
                }
            }

            // Toggle Audio
            function toggleAudio() {
                soundEnabled = !soundEnabled;
                const icon = document.getElementById('audio-icon');
                const text = document.getElementById('audio-text');
                if (soundEnabled) {
                    if (icon) icon.className = 'ri-volume-up-line fs-6 text-primary';
                    if (text) text.innerText = 'Suara: ON';
                    playChime();
                } else {
                    if (icon) icon.className = 'ri-volume-mute-line fs-6 text-secondary';
                    if (text) text.innerText = 'Suara: OFF';
                }
            }

            // Toggle Fullscreen (TV Mode)
            function toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        alert(`Gagal masuk layar penuh: ${err.message}`);
                    });
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                }
            }

            // Switch Area Tab (WFG&SMU vs WRM&WPM)
            function switchArea(newArea) {
                currentArea = newArea;

                // Update URL without reload
                const url = new URL(window.location);
                url.searchParams.set('area', newArea);
                window.history.pushState({}, '', url);

                // Update UI buttons
                const btns = document.querySelectorAll('.area-pill-btn');
                btns.forEach(b => b.classList.remove('active'));
                if (newArea === 'wfg') {
                    btns[0].classList.add('active');
                    document.getElementById('view-wfg').style.display = 'block';
                    document.getElementById('view-wrm').style.display = 'none';
                } else {
                    btns[1].classList.add('active');
                    document.getElementById('view-wfg').style.display = 'none';
                    document.getElementById('view-wrm').style.display = 'block';
                }

                // Immediately fetch data
                fetchQueueData();
            }

            // Fetch Queue Data via AJAX (Stateless API)
            async function fetchQueueData() {
                try {
                    const dataUrl = `{{ route('api.vehicle.dashboard_antrian.data') }}?area=${currentArea}`;
                    const response = await fetch(dataUrl);
                    if (!response.ok) throw new Error('Network response not ok');
                    const data = await response.json();

                    if (currentArea === 'wfg') {
                        renderWfgView(data);
                    } else {
                        renderWrmView(data);
                    }

                    // Check for new calls & play chime
                    handleCallingAlerts(data.calling || []);

                } catch (err) {
                    console.error('Error fetching queue data:', err);
                }
            }

            // Check calling alerts and sound chime (tanpa card banner agar tampilan dock & antrian muat 1 layar)
            function handleCallingAlerts(callingList) {
                if (!callingList || callingList.length === 0) return;

                let newCallDetected = false;
                callingList.forEach(item => {
                    if (!previousCalledIds.has(item.id)) {
                        newCallDetected = true;
                        previousCalledIds.add(item.id);
                    }
                });

                if (newCallDetected) {
                    playChime();
                }
            }

            // Render WFG & SMU View
            function renderWfgView(data) {
                if (!data.docks) return;

                // 1. Slipsheet Dock
                const slip = data.docks.slipsheet;
                document.getElementById('slipsheet-occupied').innerText = slip.occupied;
                document.getElementById('count-queue-slipsheet').innerText = `${slip.queue_count} Truk`;
                const slipBadge = document.getElementById('badge-slipsheet-status');
                if (slip.occupied >= slip.quota) {
                    slipBadge.className = 'badge bg-danger-subtle text-danger fs-6 px-3 py-2 fw-bold';
                } else {
                    slipBadge.className = 'badge bg-success-subtle text-success fs-6 px-3 py-2 fw-bold';
                }

                let slipSlotsHtml = '';
                slip.slots.forEach((slot, idx) => {
                    if (slot) {
                        slipSlotsHtml += `
                        <div class="col-12">
                            <div class="slot-box occupied h-100 p-2 px-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-primary text-uppercase" style="font-size: 11px; padding: 2px 6px;">Slot ${idx + 1}</span>
                                    <span class="badge bg-light text-dark border mono" style="font-size: 11px;">⏱ ${slot.duration}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between my-1">
                                    <span class="badge-plat fs-4 fw-bold text-dark">${slot.no_pol}</span>
                                    <div class="text-end text-truncate ms-2" style="max-width: 55%;">
                                        <div class="fw-bold text-primary" style="font-size: 14px;">#${slot.no_antrian}</div>
                                        <div class="text-truncate text-secondary" style="font-size: 12px;">${slot.nama_driver || '-'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    } else {
                        slipSlotsHtml += `
                        <div class="col-12">
                            <div class="slot-box available text-center h-100 py-3">
                                <div class="fw-bold text-success mb-1" style="font-size: 14px;">
                                    <i class="ri-checkbox-circle-fill"></i> SLOT ${idx + 1} TERSEDIA
                                </div>
                                <small class="text-secondary" style="font-size: 11px;">Dock Siap Digunakan</small>
                            </div>
                        </div>
                    `;
                    }
                });
                document.getElementById('slots-slipsheet').innerHTML = slipSlotsHtml;

                // 2. Curah Dock
                const curah = data.docks.curah;
                document.getElementById('curah-occupied').innerText = curah.occupied;
                document.getElementById('count-queue-curah').innerText = `${curah.queue_count} Truk`;
                const curahBadge = document.getElementById('badge-curah-status');
                if (curah.occupied >= curah.quota) {
                    curahBadge.className = 'badge bg-danger-subtle text-danger fs-6 px-3 py-2 fw-bold';
                } else {
                    curahBadge.className = 'badge bg-success-subtle text-success fs-6 px-3 py-2 fw-bold';
                }

                let curahSlotsHtml = '';
                curah.slots.forEach((slot, idx) => {
                    if (slot) {
                        curahSlotsHtml += `
                        <div class="col-6">
                            <div class="slot-box occupied h-100 p-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-warning text-dark text-uppercase" style="font-size: 10px; padding: 2px 5px;">Slot ${idx + 1}</span>
                                    <span class="text-secondary mono" style="font-size: 10px;">⏱ ${slot.duration}</span>
                                </div>
                                <div class="text-center my-1">
                                    <span class="badge-plat fs-5 fw-bold text-dark">${slot.no_pol}</span>
                                </div>
                                <div class="text-truncate text-dark text-center fw-medium" style="font-size: 12px;">
                                    <span class="text-warning-emphasis fw-bold">#${slot.no_antrian}</span> • ${slot.nama_driver || '-'}
                                </div>
                            </div>
                        </div>
                    `;
                    } else {
                        curahSlotsHtml += `
                        <div class="col-6">
                            <div class="slot-box available text-center h-100 py-3 px-1">
                                <div class="fw-bold text-success mb-1" style="font-size: 13px;">
                                    <i class="ri-check-line"></i> SLOT ${idx + 1}
                                </div>
                                <small class="text-secondary fw-semibold" style="font-size: 11px;">KOSONG</small>
                            </div>
                        </div>
                    `;
                    }
                });
                document.getElementById('slots-curah').innerHTML = curahSlotsHtml;

                // 3. Bongkaran & SMU Dock
                const bongkar = data.docks.bongkaran;
                document.getElementById('bongkaran-occupied').innerText = bongkar.occupied;
                document.getElementById('count-queue-bongkaran').innerText = `${bongkar.queue_count} Truk`;
                const bongkarBadge = document.getElementById('badge-bongkaran-status');
                if (bongkar.occupied >= bongkar.quota) {
                    bongkarBadge.className = 'badge bg-danger-subtle text-danger fs-6 px-3 py-2 fw-bold';
                } else {
                    bongkarBadge.className = 'badge bg-success-subtle text-success fs-6 px-3 py-2 fw-bold';
                }

                let bongkarSlotsHtml = '';
                bongkar.slots.forEach((slot, idx) => {
                    if (slot) {
                        bongkarSlotsHtml += `
                        <div class="col-12">
                            <div class="slot-box occupied h-100 p-2 px-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge text-uppercase text-white" style="font-size: 11px; padding: 2px 6px; background: #7c3aed;">Slot ${idx + 1}</span>
                                    <span class="badge bg-light text-dark border mono" style="font-size: 11px;">⏱ ${slot.duration}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between my-1">
                                    <span class="badge-plat fs-4 fw-bold text-dark">${slot.no_pol}</span>
                                    <div class="text-end text-truncate ms-2" style="max-width: 55%;">
                                        <div class="fw-bold" style="color: #7c3aed; font-size: 14px;">#${slot.no_antrian}</div>
                                        <div class="text-truncate text-secondary" style="font-size: 12px;">${slot.nama_driver || '-'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    } else {
                        bongkarSlotsHtml += `
                        <div class="col-12">
                            <div class="slot-box available text-center h-100 py-3">
                                <div class="fw-bold text-success mb-1" style="font-size: 14px;">
                                    <i class="ri-checkbox-circle-fill"></i> SLOT ${idx + 1} TERSEDIA
                                </div>
                                <small class="text-secondary" style="font-size: 11px;">Dock Siap Digunakan</small>
                            </div>
                        </div>
                    `;
                    }
                });
                document.getElementById('slots-bongkaran').innerHTML = bongkarSlotsHtml;

                // 3 Kolom Antrian Menunggu Sesuai Jenis (Slipsheet | Curah | Bongkaran)
                document.getElementById('total-wfg-waiting').innerText = data.total_waiting || 0;

                document.getElementById('badge-col-queue-slipsheet').innerText = `${slip.queue_count} Truk`;
                document.getElementById('col-queue-slipsheet').innerHTML = renderQueueColumnCards(slip.queue);

                document.getElementById('badge-col-queue-curah').innerText = `${curah.queue_count} Truk`;
                document.getElementById('col-queue-curah').innerHTML = renderQueueColumnCards(curah.queue);

                document.getElementById('badge-col-queue-bongkaran').innerText = `${bongkar.queue_count} Truk`;
                document.getElementById('col-queue-bongkaran').innerHTML = renderQueueColumnCards(bongkar.queue);
            }

            // Helper untuk render row kartu antrian di kolom masing-masing
            function renderQueueColumnCards(items) {
                if (!items || items.length === 0) {
                    return `
                    <div class="text-center py-3 px-2 text-secondary bg-white rounded-2 border border-dashed">
                        <i class="ri-checkbox-circle-line fs-4 text-success d-block mb-1"></i>
                        <span class="small fw-semibold" style="font-size: 11px;">Tidak ada antrian menunggu</span>
                    </div>
                `;
                }

                let html = '';
                items.forEach(q => {
                    let overnightBadge = q.is_overnight ?
                        '<span class="badge bg-danger ms-1" style="font-size: 9px; padding: 1px 4px;">Menginap</span>' : '';
                    let statusBadge = q.is_called ?
                        '<span class="badge bg-success text-white animate-pulse shadow-sm" style="font-size: 10px; padding: 2px 6px;"><i class="ri-notification-3-fill"></i> DIPANGGIL</span>' :
                        '<span class="badge bg-light border text-secondary" style="font-size: 10px; padding: 2px 5px;">Standby</span>';

                    html += `
                    <div class="p-2 rounded-2 bg-white border shadow-sm ${q.is_called ? 'border-success border-2 bg-success-subtle bg-opacity-25' : ''}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge-queue-no" style="font-size: 12px; padding: 1px 6px;">#${q.no_antrian}</span>
                                <span class="badge-plat fs-6 fw-bold">${q.no_pol}</span>
                                ${overnightBadge}
                            </div>
                            <div>
                                ${statusBadge}
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1 pt-1 border-top border-light-subtle" style="font-size: 11px;">
                            <span class="text-dark fw-semibold text-truncate me-2" style="max-width: 68%;">
                                ${q.nama_driver} <span class="text-secondary fw-normal">(${q.vendor})</span>
                            </span>
                            <span class="text-secondary mono">⏱ ${q.queue_time}</span>
                        </div>
                    </div>
                `;
                });
                return html;
            }

            // Helper to render active dock cards (responsive 2-column if active count >= 3)
            function renderActiveDockCards(items, typeBadge, badgeBgClass) {
                const isMultiCol = items && items.length >= 3;
                let html = '';
                items.forEach(item => {
                    const cardHtml = `
                        <div class="slot-box occupied p-2 px-2 px-md-3 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1 gap-1">
                                <span class="badge ${badgeBgClass}" style="font-size: ${isMultiCol ? '10px' : '11px'}; padding: 2px 6px;">${typeBadge}</span>
                                <span class="badge bg-light text-dark border mono flex-shrink-0" style="font-size: ${isMultiCol ? '10px' : '11px'};">⏱ ${item.duration}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-1 gap-1">
                                <span class="badge-plat ${isMultiCol ? 'fs-5' : 'fs-4'} fw-bold text-dark text-truncate">${item.no_pol}</span>
                                <span class="badge-queue-no flex-shrink-0" style="font-size: ${isMultiCol ? '13px' : '14px'}; padding: 2px ${isMultiCol ? '6px' : '8px'};">#${item.no_antrian}</span>
                            </div>
                            <div class="text-dark small text-truncate" style="font-size: ${isMultiCol ? '11px' : '12px'};">
                                <strong>${item.nama_driver}</strong> (${item.vendor}) • <span class="text-primary fw-medium">${item.item_name}</span>
                            </div>
                        </div>
                    `;

                    if (isMultiCol) {
                        html += `<div class="col-sm-6 col-12 p-1">${cardHtml}</div>`;
                    } else {
                        html += cardHtml;
                    }
                });

                return { html, isMultiCol };
            }

            // Render WRM & WPM View
            function renderWrmView(data) {
                // WRM Active
                document.getElementById('wrm-active-count').innerText = data.wrm?.active_count || 0;
                const wrmActiveContainer = document.getElementById('wrm-active-container');
                if (data.wrm?.active && data.wrm.active.length > 0) {
                    const { html, isMultiCol } = renderActiveDockCards(data.wrm.active, 'BONGKAR WRM', 'bg-success');
                    wrmActiveContainer.className = isMultiCol ? 'row g-0 m-0' : 'd-flex flex-column gap-1';
                    wrmActiveContainer.innerHTML = html;
                } else {
                    wrmActiveContainer.className = 'd-flex flex-column gap-1';
                    wrmActiveContainer.innerHTML = `
                        <div class="slot-box available text-center py-3 px-2">
                            <div class="text-success fw-bold mb-1" style="font-size: 14px;"><i class="ri-check-circle-line"></i> DOCK WRM TERSEDIA</div>
                            <small class="text-secondary" style="font-size: 11px;">Tidak ada proses bongkar aktif</small>
                        </div>
                    `;
                }

                // WPM Active
                document.getElementById('wpm-active-count').innerText = data.wpm?.active_count || 0;
                const wpmActiveContainer = document.getElementById('wpm-active-container');
                if (data.wpm?.active && data.wpm.active.length > 0) {
                    const { html, isMultiCol } = renderActiveDockCards(data.wpm.active, 'PROSES WPM', 'bg-info text-white');
                    wpmActiveContainer.className = isMultiCol ? 'row g-0 m-0' : 'd-flex flex-column gap-1';
                    wpmActiveContainer.innerHTML = html;
                } else {
                    wpmActiveContainer.className = 'd-flex flex-column gap-1';
                    wpmActiveContainer.innerHTML = `
                        <div class="slot-box available text-center py-3 px-2">
                            <div class="text-success fw-bold mb-1" style="font-size: 14px;"><i class="ri-check-circle-line"></i> DOCK WPM TERSEDIA</div>
                            <small class="text-secondary" style="font-size: 11px;">Tidak ada proses aktif</small>
                        </div>
                    `;
                }

                // Waiting Queue in 2 Columns (WRM & WPM)
                document.getElementById('total-wrm-wpm-waiting').innerText = data.total_waiting || 0;

                document.getElementById('badge-col-queue-wrm').innerText = `${data.wrm?.queue_count || 0} Truk`;
                document.getElementById('col-queue-wrm').innerHTML = renderQueueColumnCards(data.wrm?.queue || []);

                document.getElementById('badge-col-queue-wpm').innerText = `${data.wpm?.queue_count || 0} Truk`;
                document.getElementById('col-queue-wpm').innerHTML = renderQueueColumnCards(data.wpm?.queue || []);
            }

            // Laravel Reverb WebSocket Listener
            function initReverb() {
                try {
                    window.Pusher = Pusher;
                    const wsHost = window.location.hostname || "{{ request()->getHost() }}";
                    window.Echo = new Echo({
                        broadcaster: 'reverb',
                        key: "{{ env('REVERB_APP_KEY') }}",
                        wsHost: wsHost,
                        wsPort: {{ env('REVERB_PORT', 8080) }},
                        wssPort: {{ env('REVERB_PORT', 8080) }},
                        forceTLS: {{ env('REVERB_SCHEME') === 'https' ? 'true' : 'false' }},
                        enabledTransports: ['ws', 'wss'],
                    });

                    window.Echo.channel('vehicle-tracking')
                        .listen('.vehicle.updated', (payload) => {
                            console.log('[Reverb] Live update received:', payload);
                            fetchQueueData();
                        });

                    if (window.Echo.connector && window.Echo.connector.pusher) {
                        window.Echo.connector.pusher.connection.bind('connected', () => {
                            const wsIndicator = document.getElementById('ws-status-text');
                            if (wsIndicator) {
                                wsIndicator.innerText = 'LIVE (WS)';
                                wsIndicator.classList.remove('text-warning');
                                wsIndicator.classList.add('text-success');
                            }
                            console.log(`[Reverb] WebSocket connected to ws://${wsHost}:{{ env('REVERB_PORT', 8080) }}`);
                        });

                        window.Echo.connector.pusher.connection.bind('unavailable', () => {
                            const wsIndicator = document.getElementById('ws-status-text');
                            if (wsIndicator) {
                                wsIndicator.innerText = 'LIVE (POLL)';
                                wsIndicator.classList.remove('text-success');
                                wsIndicator.classList.add('text-warning');
                            }
                            console.warn('[Reverb] WebSocket unavailable, falling back to 10s polling.');
                        });
                    }
                } catch (e) {
                    console.warn('[Reverb] WebSocket init failed, fallback to polling:', e);
                }
            }

            // Polling Timer with countdown (Fallback jika WebSocket disconnect)
            function startPollingTimer() {
                refreshSeconds = 10;
                const timerEl = document.getElementById('refresh-timer');
                if (timerEl) timerEl.innerText = refreshSeconds;

                if (refreshTimerInterval) clearInterval(refreshTimerInterval);

                refreshTimerInterval = setInterval(() => {
                    refreshSeconds--;
                    if (timerEl) timerEl.innerText = refreshSeconds;

                    if (refreshSeconds <= 0) {
                        fetchQueueData();
                        refreshSeconds = 10;
                        if (timerEl) timerEl.innerText = refreshSeconds;
                    }
                }, 1000);
            }

            // Initialize on DOM load
            document.addEventListener('DOMContentLoaded', () => {
                fetchQueueData();
                initReverb();
                startPollingTimer();
            });
        </script>
    </body>

</html>
