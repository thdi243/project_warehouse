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
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
                padding: 12px 24px;
                position: sticky;
                top: 0;
                z-index: 100;
            }

            .brand-logo {
                height: 42px;
                width: auto;
                object-fit: contain;
            }

            /* Area Switch Tabs */
            .area-pill-group {
                background: #e2e8f0;
                border-radius: 9999px;
                padding: 4px;
                display: inline-flex;
                gap: 4px;
            }

            .area-pill-btn {
                padding: 8px 22px;
                border-radius: 9999px;
                font-weight: 700;
                font-size: 14px;
                color: #475569;
                border: none;
                background: transparent;
                transition: all 0.25s ease;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
            }

            .area-pill-btn:hover {
                color: #0f172a;
                background: rgba(255, 255, 255, 0.6);
            }

            .area-pill-btn.active {
                color: #ffffff;
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            }

            .area-pill-btn.active.area-wrm {
                background: linear-gradient(135deg, #059669 0%, #047857 100%);
                box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
            }

            /* Clean White Cards */
            .content-card {
                background: #ffffff;
                border: 1px solid var(--border-subtle);
                border-radius: 16px;
                box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
                transition: all 0.25s ease;
            }

            .content-card:hover {
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
            }

            /* Slot Boxes inside Dock */
            .slot-box {
                border-radius: 12px;
                padding: 10px 14px;
                min-height: 85px;
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
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s ease;
                cursor: pointer;
            }

            .btn-ctrl:hover {
                background: #e2e8f0;
                color: #0f172a;
            }

            /* Mobile Adjustments */
            @media (max-width: 768px) {
                .top-navbar {
                    padding: 10px 14px;
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
            <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Brand & Title -->
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('assets/images/logo/kecap.png') }}" alt="PT BAS" class="brand-logo">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2"
                            style="letter-spacing: 0.5px;">
                            MONITOR ANTRIAN GUDANG
                            <span class="badge bg-primary text-uppercase" style="font-size: 11px; padding: 4px 8px;">TV
                                & Supir</span>
                        </h5>
                        <small class="text-secondary" style="font-size: 12px;">PT Bumi Alam Segar — Loading Yard
                            Real-Time Queue System</small>
                    </div>
                </div>

                <!-- Area Switch Selector (WFG&SMU vs WRM&WPM) -->
                <div class="area-pill-group">
                    <button type="button" class="area-pill-btn {{ $area === 'wfg' ? 'active' : '' }}"
                        onclick="switchArea('wfg')">
                        <i class="ri-truck-line"></i>
                        <span>WFG & SMU <small class="opacity-75">(Finish Goods)</small></span>
                    </button>
                    <button type="button" class="area-pill-btn area-wrm {{ $area === 'wrm' ? 'active area-wrm' : '' }}"
                        onclick="switchArea('wrm')">
                        <i class="ri-archive-line"></i>
                        <span>WRM & WPM <small class="opacity-75">(Bahan & Kemasan)</small></span>
                    </button>
                </div>

                <!-- Clock, Status, & TV Fullscreen Actions -->
                <div class="d-flex align-items-center gap-3">
                    <!-- Live status -->
                    <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-pill"
                        style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                        <span class="live-dot"></span>
                        <span class="fw-bold text-success" id="ws-status-text"
                            style="font-size: 12px; letter-spacing: 0.5px;">LIVE</span>
                        <span class="text-secondary" style="font-size: 11px;">• <span
                                id="refresh-timer">10</span>s</span>
                    </div>

                    <!-- Clock & Date -->
                    <div class="text-end hide-mobile">
                        <div id="live-clock" class="mono fw-bold text-dark fs-5 lh-1">--:--:--</div>
                        <small id="live-date" class="text-secondary" style="font-size: 11px;">--</small>
                    </div>

                    <!-- Chime Audio Toggle -->
                    <button type="button" class="btn-ctrl" id="btn-audio-toggle" onclick="toggleAudio()"
                        title="Suara Panggilan">
                        <i class="ri-volume-up-line fs-6 text-primary" id="audio-icon"></i>
                        <span class="hide-mobile" id="audio-text">Suara: ON</span>
                    </button>

                    <!-- Fullscreen Toggle -->
                    <button type="button" class="btn-ctrl" onclick="toggleFullscreen()" title="Fullscreen Mode (TV)">
                        <i class="ri-fullscreen-line fs-6"></i>
                        <span class="hide-mobile">Layar Penuh</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Container -->
        <main class="container-fluid py-2 py-lg-3 px-lg-4">

            <!-- ======================================================== -->
            <!-- VIEW 1: WFG & SMU (FINISH GOODS & SMU) -->
            <!-- ======================================================== -->
            <div id="view-wfg" style="{{ $area === 'wfg' ? '' : 'display: none;' }}">
                <!-- Dock Quota & Slot Status (3 Columns) -->
                <div class="row g-3 mb-3">
                    <!-- 1. Dock Slipsheet (Kuota: 2 Mobil) -->
                    <div class="col-lg-4 col-md-6">
                        <div class="content-card p-3 h-100">
                            <div
                                class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary fs-6 p-2"><i class="ri-truck-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Dock Slipsheet</h6>
                                        <small class="text-secondary">WFG Muat • Kuota: 2 Truk</small>
                                    </div>
                                </div>
                                <span id="badge-slipsheet-status"
                                    class="badge bg-success-subtle text-success fs-6 px-3 py-2 fw-bold">
                                    Terisi: <span id="slipsheet-occupied">0</span>/2
                                </span>
                            </div>

                            <!-- 2 Slots Slipsheet -->
                            <div class="d-flex flex-column gap-2" id="slots-slipsheet">
                                <div class="slot-box available text-center">
                                    <span class="text-secondary">Memuat data slot...</span>
                                </div>
                            </div>

                            <div
                                class="mt-3 pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                                <small class="text-secondary fw-semibold">Antrian Menunggu Slipsheet:</small>
                                <span id="count-queue-slipsheet" class="fw-bold text-warning mono fs-6">0 Truk</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Dock Curah (Kuota: 4 Mobil) -->
                    <div class="col-lg-4 col-md-6">
                        <div class="content-card p-3 h-100">
                            <div
                                class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-warning fs-6 p-2 text-dark"><i
                                            class="ri-stack-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Dock Curah</h6>
                                        <small class="text-secondary">WFG Muat • Kuota: 4 Truk</small>
                                    </div>
                                </div>
                                <span id="badge-curah-status"
                                    class="badge bg-success-subtle text-success fs-6 px-3 py-2 fw-bold">
                                    Terisi: <span id="curah-occupied">0</span>/4
                                </span>
                            </div>

                            <!-- 4 Slots Curah -->
                            <div class="row g-2" id="slots-curah">
                                <div class="col-12 slot-box available text-center">
                                    <span class="text-secondary">Memuat data slot...</span>
                                </div>
                            </div>

                            <div
                                class="mt-3 pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                                <small class="text-secondary fw-semibold">Antrian Menunggu Curah:</small>
                                <span id="count-queue-curah" class="fw-bold text-warning mono fs-6">0 Truk</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Dock Bongkaran & SMU (Kuota: 2 Mobil) -->
                    <div class="col-lg-4 col-md-12">
                        <div class="content-card p-3 h-100">
                            <div
                                class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge fs-6 p-2 text-white" style="background: #7c3aed;"><i
                                            class="ri-inbox-unarchive-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Dock Bongkaran & SMU</h6>
                                        <small class="text-secondary">WFG Bongkar + SMU • Kuota: 2 Truk</small>
                                    </div>
                                </div>
                                <span id="badge-bongkaran-status"
                                    class="badge bg-success-subtle text-success fs-6 px-3 py-2 fw-bold">
                                    Terisi: <span id="bongkaran-occupied">0</span>/2
                                </span>
                            </div>

                            <!-- 2 Slots Bongkaran & SMU -->
                            <div class="d-flex flex-column gap-2" id="slots-bongkaran">
                                <div class="slot-box available text-center">
                                    <span class="text-secondary">Memuat data slot...</span>
                                </div>
                            </div>

                            <div
                                class="mt-3 pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                                <small class="text-secondary fw-semibold">Antrian Menunggu Bongkaran/SMU:</small>
                                <span id="count-queue-bongkaran" class="fw-bold text-warning mono fs-6">0 Truk</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Antrian Menunggu (Standby Parkir) -->
                <div class="content-card p-3 p-lg-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-parking-box-line fs-3 text-warning"></i>
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">Antrian Menunggu di Kantong Parkir (WFG & SMU)</h5>
                                <small class="text-secondary">Supir harap standby di kendaraan / ruang tunggu. Anda
                                    akan dipanggil otomatis via WhatsApp dan layar monitor ini.</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light border border-secondary border-opacity-25 text-dark px-3 py-2">
                                Total Menunggu: <strong id="total-wfg-waiting"
                                    class="text-primary mono fs-6">0</strong> Truk
                            </span>
                        </div>
                    </div>

                    <!-- 3 Kolom Antrian Sesuai Jenis (Slipsheet | Curah | Bongkaran) -->
                    <div class="row g-3">
                        <!-- Kolom 1: Slipsheet -->
                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 rounded-3 border bg-light h-100 shadow-sm">
                                <div
                                    class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary p-2"><i class="ri-truck-fill"></i></span>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">Slipsheet</h6>
                                            <small class="text-secondary" style="font-size: 11px;">Antrian Muat
                                                Slipsheet</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary fw-bold fs-6"
                                        id="badge-col-queue-slipsheet">0 Truk</span>
                                </div>
                                <div id="col-queue-slipsheet" class="d-flex flex-column gap-2"
                                    style="max-height: 520px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian slipsheet...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 2: Curah -->
                        <div class="col-lg-4 col-md-6">
                            <div class="p-3 rounded-3 border bg-light h-100 shadow-sm">
                                <div
                                    class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-warning text-dark p-2"><i
                                                class="ri-stack-fill"></i></span>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">Curah</h6>
                                            <small class="text-secondary" style="font-size: 11px;">Antrian Muat
                                                Curah</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning fw-bold fs-6"
                                        id="badge-col-queue-curah">0 Truk</span>
                                </div>
                                <div id="col-queue-curah" class="d-flex flex-column gap-2"
                                    style="max-height: 520px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian curah...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 3: Bongkaran & SMU -->
                        <div class="col-lg-4 col-md-12">
                            <div class="p-3 rounded-3 border bg-light h-100 shadow-sm">
                                <div
                                    class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge text-white p-2" style="background: #7c3aed;"><i
                                                class="ri-inbox-unarchive-fill"></i></span>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">Bongkaran (& SMU)</h6>
                                            <small class="text-secondary" style="font-size: 11px;">Antrian WFG Bongkar
                                                + SMU</small>
                                        </div>
                                    </div>
                                    <span class="badge fw-bold fs-6" style="background: #ede9fe; color: #6d28d9;"
                                        id="badge-col-queue-bongkaran">0 Truk</span>
                                </div>
                                <div id="col-queue-bongkaran" class="d-flex flex-column gap-2"
                                    style="max-height: 520px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian bongkaran...
                                    </div>
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
                <div class="row g-3 mb-4">
                    <!-- 1. Active Docks in WRM & WPM -->
                    <div class="col-lg-6">
                        <div class="content-card p-3 h-100">
                            <div
                                class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-success fs-6 p-2"><i
                                            class="ri-inbox-archive-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Dock WRM (Bahan Baku)</h6>
                                        <small class="text-secondary">Truk Sedang Proses Bongkar</small>
                                    </div>
                                </div>
                                <span id="badge-wrm-active-count" class="badge bg-primary fs-6 px-3 py-2 fw-bold">
                                    Aktif: <span id="wrm-active-count">0</span> Truk
                                </span>
                            </div>

                            <div id="wrm-active-container" class="d-flex flex-column gap-2">
                                <div class="slot-box available text-center">
                                    <span class="text-secondary">Memuat data aktif WRM...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="content-card p-3 h-100">
                            <div
                                class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info fs-6 p-2 text-white"><i
                                            class="ri-archive-stack-fill"></i></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">Dock WPM (Bahan Kemasan)</h6>
                                        <small class="text-secondary">Truk Sedang Proses Muat / Bongkar</small>
                                    </div>
                                </div>
                                <span id="badge-wpm-active-count" class="badge bg-primary fs-6 px-3 py-2 fw-bold">
                                    Aktif: <span id="wpm-active-count">0</span> Truk
                                </span>
                            </div>

                            <div id="wpm-active-container" class="d-flex flex-column gap-2">
                                <div class="slot-box available text-center">
                                    <span class="text-secondary">Memuat data aktif WPM...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Antrian Menunggu di Kantong Parkir (WRM & WPM) per Kolom -->
                <div class="content-card p-3 p-lg-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-parking-box-line fs-3 text-warning"></i>
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">Antrian Menunggu di Kantong Parkir (WRM & WPM)</h5>
                                <small class="text-secondary">Supir harap standby di kendaraan / ruang tunggu.
                                    Panggilan masuk via WhatsApp & layar monitor.</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light border border-secondary border-opacity-25 text-dark px-3 py-2">
                                Total Menunggu: <strong id="total-wrm-wpm-waiting"
                                    class="text-primary mono fs-6">0</strong> Truk
                            </span>
                        </div>
                    </div>

                    <!-- 2 Kolom Antrian Sesuai Jenis (WRM | WPM) -->
                    <div class="row g-3">
                        <!-- Kolom 1: WRM (Bahan Baku) -->
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3 border bg-light h-100 shadow-sm">
                                <div
                                    class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success p-2"><i
                                                class="ri-inbox-archive-fill"></i></span>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">Antrian WRM (Bahan Baku)</h6>
                                            <small class="text-secondary" style="font-size: 11px;">Kendaraan menunggu
                                                bongkar bahan baku</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success fw-bold fs-6"
                                        id="badge-col-queue-wrm">0 Truk</span>
                                </div>
                                <div id="col-queue-wrm" class="d-flex flex-column gap-2"
                                    style="max-height: 520px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian WRM...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 2: WPM (Bahan Kemasan) -->
                        <div class="col-lg-6">
                            <div class="p-3 rounded-3 border bg-light h-100 shadow-sm">
                                <div
                                    class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-10">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info p-2 text-white"><i
                                                class="ri-archive-stack-fill"></i></span>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">Antrian WPM (Bahan Kemasan)</h6>
                                            <small class="text-secondary" style="font-size: 11px;">Kendaraan menunggu
                                                muat / bongkar kemasan</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-info-subtle text-info fw-bold fs-6"
                                        id="badge-col-queue-wpm">0 Truk</span>
                                </div>
                                <div id="col-queue-wpm" class="d-flex flex-column gap-2"
                                    style="max-height: 520px; overflow-y: auto;">
                                    <div class="text-center py-4 text-secondary small">Memuat antrian WPM...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Footer Info / Instruksi Supir -->
        <footer class="py-3 px-4 border-top mt-3" stylpe="background: #ffffff;">
            <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between gap-3 text-secondary"
                style="font-size: 13px;">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-information-line text-warning fs-5"></i>
                    <span><strong>Perhatian Supir:</strong> Pastikan Anda telah <strong>menyimpan nomor WhatsApp
                            Gateway</strong> dan <strong>membalas dengan OKE</strong> agar notifikasi panggilan dock
                        terkirim lancar ke HP Anda.</span>
                </div>
                <div>
                    <span>Sistem Antrian Terintegrasi &copy; {{ date('Y') }} PT BAS Warehouse Monitoring</span>
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
                        <div class="slot-box occupied">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge bg-primary text-uppercase" style="font-size: 11px;">Slot ${idx + 1} • PROSES</span>
                                <span class="badge bg-light text-dark border mono" style="font-size: 11px;">⏱ ${slot.duration}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-1">
                                <span class="badge-plat">${slot.no_pol}</span>
                                <span class="badge-queue-no" style="font-size: 14px; padding: 2px 8px;">#${slot.no_antrian}</span>
                            </div>
                            <div class="text-truncate text-dark small mt-1">
                                <strong>${slot.nama_driver}</strong> <span class="text-secondary">(${slot.vendor})</span>
                            </div>
                        </div>
                    `;
                    } else {
                        slipSlotsHtml += `
                        <div class="slot-box available text-center">
                            <div class="fw-bold text-success mb-1" style="font-size: 15px;">
                                <i class="ri-checkbox-circle-fill"></i> SLOT ${idx + 1} TERSEDIA
                            </div>
                            <small class="text-secondary">Dock Siap Digunakan</small>
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
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="badge bg-warning text-dark text-uppercase" style="font-size: 10px;">Slot ${idx + 1}</span>
                                    <span class="text-secondary mono" style="font-size: 10px;">${slot.duration}</span>
                                </div>
                                <div class="text-center my-1">
                                    <span class="badge-plat" style="font-size: 18px;">${slot.no_pol}</span>
                                </div>
                                <div class="text-truncate text-dark small text-center" style="font-size: 11px;">
                                    #${slot.no_antrian} • ${slot.nama_driver}
                                </div>
                            </div>
                        </div>
                    `;
                    } else {
                        curahSlotsHtml += `
                        <div class="col-6">
                            <div class="slot-box available text-center h-100 p-2">
                                <div class="fw-bold text-success" style="font-size: 13px;">
                                    <i class="ri-check-line"></i> SLOT ${idx + 1}
                                </div>
                                <small class="text-secondary small">KOSONG</small>
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
                        <div class="slot-box occupied">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge text-uppercase text-white" style="font-size: 11px; background: #7c3aed;">Slot ${idx + 1} • BONGKAR/SMU</span>
                                <span class="badge bg-light text-dark border mono" style="font-size: 11px;">⏱ ${slot.duration}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-1">
                                <span class="badge-plat">${slot.no_pol}</span>
                                <span class="badge-queue-no" style="font-size: 14px; padding: 2px 8px;">#${slot.no_antrian}</span>
                            </div>
                            <div class="text-truncate text-dark small mt-1">
                                <strong>${slot.nama_driver}</strong> <span class="text-secondary">(${slot.vendor})</span>
                            </div>
                        </div>
                    `;
                    } else {
                        bongkarSlotsHtml += `
                        <div class="slot-box available text-center">
                            <div class="fw-bold text-success mb-1" style="font-size: 15px;">
                                <i class="ri-checkbox-circle-fill"></i> SLOT ${idx + 1} TERSEDIA
                            </div>
                            <small class="text-secondary">Dock Siap Digunakan</small>
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
                    <div class="text-center py-4 px-2 text-secondary bg-white rounded-3 border border-dashed">
                        <i class="ri-checkbox-circle-line fs-3 text-success d-block mb-1"></i>
                        <span class="small fw-semibold">Tidak ada antrian menunggu</span>
                    </div>
                `;
                }

                let html = '';
                items.forEach(q => {
                    let overnightBadge = q.is_overnight ?
                        '<span class="badge bg-danger ms-1" style="font-size: 10px;">Menginap</span>' : '';
                    let statusBadge = q.is_called ?
                        '<span class="badge bg-success text-white small animate-pulse shadow-sm"><i class="ri-notification-3-fill"></i> DIPANGGIL</span>' :
                        '<span class="badge bg-light border text-secondary small">Standby</span>';

                    html += `
                    <div class="p-2 rounded-3 bg-white border shadow-sm ${q.is_called ? 'border-success border-2' : ''}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span class="badge-queue-no" style="font-size: 14px; padding: 2px 9px;">#${q.no_antrian}</span>
                                ${overnightBadge}
                            </div>
                            <span class="badge-plat fs-5">${q.no_pol}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-light-subtle">
                            <div class="text-truncate me-2" style="max-width: 65%;">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 13px;">${q.nama_driver}</div>
                                <small class="text-secondary text-truncate d-block" style="font-size: 11px;">${q.vendor}</small>
                            </div>
                            <div class="text-end">
                                ${statusBadge}
                                <small class="text-secondary mono d-block mt-1" style="font-size: 11px;">⏱ ${q.queue_time}</small>
                            </div>
                        </div>
                    </div>
                `;
                });
                return html;
            }

            // Render WRM & WPM View
            function renderWrmView(data) {
                // WRM Active
                document.getElementById('wrm-active-count').innerText = data.wrm?.active_count || 0;
                const wrmActiveContainer = document.getElementById('wrm-active-container');
                if (data.wrm?.active && data.wrm.active.length > 0) {
                    let html = '';
                    data.wrm.active.forEach(item => {
                        html += `
                        <div class="slot-box occupied">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-success">BONGKAR WRM</span>
                                <span class="text-dark mono small">⏱ ${item.duration}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-1">
                                <span class="badge-plat">${item.no_pol}</span>
                                <span class="badge-queue-no" style="font-size: 14px; padding: 2px 8px;">#${item.no_antrian}</span>
                            </div>
                            <div class="text-dark small mt-1">
                                <strong>${item.nama_driver}</strong> (${item.vendor}) • <span class="text-primary">${item.item_name}</span>
                            </div>
                        </div>
                    `;
                    });
                    wrmActiveContainer.innerHTML = html;
                } else {
                    wrmActiveContainer.innerHTML = `
                    <div class="slot-box available text-center">
                        <div class="text-success fw-bold"><i class="ri-check-circle-line"></i> DOCK WRM TERSEDIA</div>
                        <small class="text-secondary">Tidak ada proses bongkar aktif</small>
                    </div>
                `;
                }

                // WPM Active
                document.getElementById('wpm-active-count').innerText = data.wpm?.active_count || 0;
                const wpmActiveContainer = document.getElementById('wpm-active-container');
                if (data.wpm?.active && data.wpm.active.length > 0) {
                    let html = '';
                    data.wpm.active.forEach(item => {
                        html += `
                        <div class="slot-box occupied">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-info">PROSES WPM</span>
                                <span class="text-dark mono small">⏱ ${item.duration}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-1">
                                <span class="badge-plat">${item.no_pol}</span>
                                <span class="badge-queue-no" style="font-size: 14px; padding: 2px 8px;">#${item.no_antrian}</span>
                            </div>
                            <div class="text-dark small mt-1">
                                <strong>${item.nama_driver}</strong> (${item.vendor}) • <span class="text-primary">${item.item_name}</span>
                            </div>
                        </div>
                    `;
                    });
                    wpmActiveContainer.innerHTML = html;
                } else {
                    wpmActiveContainer.innerHTML = `
                    <div class="slot-box available text-center">
                        <div class="text-success fw-bold"><i class="ri-check-circle-line"></i> DOCK WPM TERSEDIA</div>
                        <small class="text-secondary">Tidak ada proses aktif</small>
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
                    window.Echo = new Echo({
                        broadcaster: 'reverb',
                        key: "{{ env('REVERB_APP_KEY') }}",
                        wsHost: "{{ env('REVERB_HOST') ?: request()->getHost() }}",
                        wsPort: {{ env('REVERB_PORT') ?: 8080 }},
                        wssPort: {{ env('REVERB_PORT') ?: 8080 }},
                        forceTLS: {{ env('REVERB_SCHEME') === 'https' ? 'true' : 'false' }},
                        enabledTransports: ['ws', 'wss'],
                    });

                    window.Echo.channel('vehicle-tracking')
                        .listen('.vehicle.updated', (payload) => {
                            console.log('[Reverb] Live update received:', payload);
                            fetchQueueData();
                        });

                    const wsIndicator = document.getElementById('ws-status-text');
                    if (wsIndicator) wsIndicator.innerText = 'LIVE';
                    console.log('[Reverb] WebSocket connection established.');
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
