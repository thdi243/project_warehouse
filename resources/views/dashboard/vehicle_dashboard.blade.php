<!DOCTYPE html>
<html lang="en" data-layout-mode="dark">

    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Real-time Vehicle Monitoring Dashboard</title>

        {{-- favicon --}}
        <link rel="shortcut icon" href="{{ asset('assets/images/logo/kecap.png') }}">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Share+Tech+Mono&display=swap"
            rel="stylesheet">

        <!-- Stylesheets -->
        <link href="{{ asset('material/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

        <style>
            :root {
                --bg-dark: #070a13;
                --card-bg: rgba(15, 23, 42, 0.6);
                --border-color: rgba(255, 255, 255, 0.06);
                --text-primary: #f8fafc;
                --text-muted: #64748b;

                --primary: #3b82f6;
                --success: #10b981;
                --warning: #f59e0b;
                --danger: #ef4444;
                --info: #06b6d4;
                --secondary: #475569;
            }

            body {
                background-color: var(--bg-dark);
                color: var(--text-primary);
                font-family: 'Outfit', sans-serif;
                overflow-x: hidden;
                min-height: 100vh;
            }

            /* Glassmorphism Cards */
            .premium-card {
                background: var(--card-bg);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid var(--border-color);
                border-radius: 16px;
                box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .premium-card:hover {
                border-color: rgba(59, 130, 246, 0.25);
                box-shadow: 0 15px 40px -12px rgba(59, 130, 246, 0.12);
            }

            /* Glowing texts */
            .glow-blue {
                text-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
            }

            .glow-green {
                text-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
            }

            .glow-amber {
                text-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
            }

            .glow-red {
                text-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
            }

            /* Clock */
            .digital-clock {
                font-family: 'Share Tech Mono', monospace;
                font-size: 22px;
                color: var(--primary);
                text-shadow: 0 0 10px rgba(59, 130, 246, 0.6);
                background: rgba(15, 23, 42, 0.8);
                padding: 6px 16px;
                border-radius: 10px;
                border: 1px solid rgba(59, 130, 246, 0.3);
                letter-spacing: 1px;
            }

            /* Header logo */
            .logo-title {
                font-weight: 800;
                font-size: 24px;
                background: linear-gradient(135deg, #fff 30%, #3b82f6 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                letter-spacing: -0.5px;
            }

            /* Tables */
            .table-responsive {
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid var(--border-color);
                min-height: 230px;
                max-height: 230px !important;
            }

            .premium-table {
                width: 100%;
                margin-bottom: 0;
                color: #cbd5e1;
                font-size: 11px;
            }

            .premium-table th {
                background-color: rgba(30, 41, 59, 0.9) !important;
                color: #94a3b8;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.05em;
                padding: 10px 12px;
                border-bottom: 2px solid var(--border-color);
            }

            .premium-table td {
                background-color: transparent !important;
                padding: 8px 12px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.03);
                vertical-align: middle;
            }

            .premium-table tbody tr {
                transition: background-color 0.2s ease;
            }

            .premium-table tbody tr:hover td {
                background-color: rgba(255, 255, 255, 0.02) !important;
            }

            /* Timer badge */
            .timer-badge {
                font-size: 12px;
                font-weight: 600;
                padding: 6px 12px;
                border-radius: 30px;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: #94a3b8;
            }

            .timer-badge.warning-limit {
                background: rgba(245, 158, 11, 0.15);
                border-color: rgba(245, 158, 11, 0.3);
                color: #fbbf24;
                text-shadow: 0 0 8px rgba(245, 158, 11, 0.3);
            }

            .timer-badge.danger-limit {
                background: rgba(239, 68, 68, 0.15);
                border-color: rgba(239, 68, 68, 0.3);
                color: #f87171;
                text-shadow: 0 0 8px rgba(239, 68, 68, 0.3);
                animation: pulse-badge 2s infinite;
            }

            @keyframes pulse-badge {
                0% {
                    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3);
                }

                70% {
                    box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
                }
            }

            /* Card Headers */
            .card-hdr {
                border-bottom: 1px solid var(--border-color);
                padding: 16px 20px;
                border-top-left-radius: 15px;
                border-top-right-radius: 15px;
            }

            .card-hdr.wpm {
                background: linear-gradient(90deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.02) 100%);
            }

            .card-hdr.wrm {
                background: linear-gradient(90deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.02) 100%);
            }

            .card-hdr.wfg {
                background: linear-gradient(90deg, rgba(6, 180, 212, 0.15) 0%, rgba(6, 180, 212, 0.02) 100%);
            }

            .card-hdr.smu {
                background: linear-gradient(90deg, rgba(100, 116, 139, 0.15) 0%, rgba(100, 116, 139, 0.02) 100%);
            }

            .status-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                display: inline-block;
                margin-right: 8px;
                box-shadow: 0 0 8px currentColor;
            }

            /* KPI Stat boxes */
            .kpi-box {
                padding: 12px 18px;
                text-align: left;
                position: relative;
                overflow: hidden;
            }

            .kpi-icon {
                position: absolute;
                right: 20px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 40px;
                opacity: 0.15;
                color: var(--primary);
                transition: all 0.3s ease;
            }

            .kpi-box:hover .kpi-icon {
                transform: translateY(-50%) scale(1.1);
                opacity: 0.3;
            }

            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }

            ::-webkit-scrollbar-track {
                background: rgba(0, 0, 0, 0.2);
            }

            ::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.1);
                border-radius: 4px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: rgba(255, 255, 255, 0.25);
            }

            /* Blink class */
            .blink {
                animation: blinker 1.5s linear infinite;
            }

            @keyframes blinker {
                50% {
                    opacity: 0.3;
                }
            }

            /* Badge status styling */
            .badge-status {
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 0.05em;
                padding: 4px 8px;
                border-radius: 4px;
                text-transform: uppercase;
            }

            .badge-status.waiting {
                background-color: rgba(245, 158, 11, 0.15);
                color: #fbbf24;
                border: 1px solid rgba(245, 158, 11, 0.25);
            }

            .badge-status.process {
                background-color: rgba(6, 180, 212, 0.15);
                color: #22d3ee;
                border: 1px solid rgba(6, 180, 212, 0.25);
            }

            .badge-status.success {
                background-color: rgba(16, 185, 129, 0.15);
                color: #34d399;
                border: 1px solid rgba(16, 185, 129, 0.25);
            }

            /* Rocket Container */
            .rocket-container {
                position: fixed;
                bottom: -150px;
                left: -150px;
                z-index: 999;
                pointer-events: none;
                opacity: 0;
                transform: rotate(45deg);
                width: 80px;
                height: 80px;
            }

            .rocket-container.launch {
                animation: rocketLaunch 5s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
            }

            @keyframes rocketLaunch {
                0% {
                    bottom: -150px;
                    left: -150px;
                    opacity: 0;
                    transform: rotate(45deg) scale(0.6);
                }

                5% {
                    opacity: 0.9;
                }

                95% {
                    opacity: 0.9;
                }

                100% {
                    bottom: 110%;
                    left: 110%;
                    opacity: 0;
                    transform: rotate(45deg) scale(1.1);
                }
            }

            .rocket-svg {
                width: 100%;
                height: 100%;
                filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.6)) drop-shadow(0 0 20px rgba(239, 68, 68, 0.4));
            }

            .rocket-trail {
                position: absolute;
                bottom: -45px;
                left: 32px;
                width: 16px;
                height: 50px;
                background: linear-gradient(to bottom,
                        #ef4444,
                        #f97316 30%,
                        #eab308 60%,
                        transparent 100%);
                filter: blur(3px);
                border-radius: 50% 50% 0 0;
                transform-origin: top center;
                animation: flameBurn 0.15s infinite alternate;
            }

            @keyframes flameBurn {
                0% {
                    transform: scaleY(0.9) scaleX(0.9);
                    filter: blur(2px) brightness(1.2);
                }

                100% {
                    transform: scaleY(1.3) scaleX(1.1);
                    filter: blur(4px) brightness(1.5);
                }
            }

            /* Rocket Smoke Particles */
            .smoke-particle {
                position: fixed;
                width: 12px;
                height: 12px;
                background: rgba(255, 255, 255, 0.15);
                border-radius: 50%;
                filter: blur(3px);
                pointer-events: none;
                z-index: 998;
                animation: smokeFade 1.2s ease-out forwards;
            }

            @keyframes smokeFade {
                0% {
                    transform: scale(1);
                    opacity: 0.4;
                    background: rgba(249, 115, 22, 0.4);
                }

                50% {
                    background: rgba(148, 163, 184, 0.2);
                }

                100% {
                    transform: scale(3);
                    opacity: 0;
                }
            }

            /* Pagination indicator dots */
            .pagination-dot {
                width: 6px;
                height: 6px;
                background-color: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                display: inline-block;
                margin: 0 4px;
                transition: all 0.3s ease;
            }

            .pagination-dot.active.wpm {
                background-color: var(--success);
                box-shadow: 0 0 8px var(--success);
                transform: scale(1.3);
            }

            .pagination-dot.active.wrm {
                background-color: var(--warning);
                box-shadow: 0 0 8px var(--warning);
                transform: scale(1.3);
            }

            .pagination-dot.active.wfg {
                background-color: var(--info);
                box-shadow: 0 0 8px var(--info);
                transform: scale(1.3);
            }

            .pagination-dot.active.smu {
                background-color: #94a3b8;
                box-shadow: 0 0 8px #94a3b8;
                transform: scale(1.3);
            }

            .pagination-info {
                font-size: 10px;
                color: var(--text-muted);
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-weight: 600;
            }
        </style>
    </head>

    <body>
        <div id="rocket" class="rocket-container">
            <div class="rocket-trail"></div>
            <!-- Rocket SVG -->
            <svg class="rocket-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <defs>
                    <linearGradient id="rocketBodyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#ffffff" />
                        <stop offset="100%" stop-color="#94a3b8" />
                    </linearGradient>
                    <linearGradient id="rocketFinsGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#3b82f6" />
                        <stop offset="100%" stop-color="#1d4ed8" />
                    </linearGradient>
                    <linearGradient id="rocketNoseGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#ef4444" />
                        <stop offset="100%" stop-color="#b91c1c" />
                    </linearGradient>
                </defs>
                <path
                    d="M128 320c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h64V320h-32zm256 0h-32v128h64c17.7 0 32-14.3 32-32v-32c0-35.3-28.7-64-64-64z"
                    fill="url(#rocketFinsGrad)" />
                <path
                    d="M256 64c-53 0-96 43-96 96v192c0 35.3 28.7 64 64 64h64c35.3 0 64-28.7 64-64V160c0-53-43-96-96-96z"
                    fill="url(#rocketBodyGrad)" />
                <path d="M256 0c-53 0-96 43-96 96v64h192V96c0-53-43-96-96-96z" fill="url(#rocketNoseGrad)" />
                <circle cx="256" cy="224" r="32" fill="#0f172a" />
                <circle cx="256" cy="224" r="24" fill="#38bdf8" />
            </svg>
        </div>

        <!-- Top Navbar Standalone -->
        <nav class="navbar py-3 px-4 border-bottom border-secondary-subtle"
            style="background-color: rgba(11, 15, 25, 0.85); backdrop-filter: blur(10px);">
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('assets/images/logo/kecap.png') }}" alt="logo" height="36" class="me-3">
                    <div>
                        <h1 class="logo-title mb-0">REAL-TIME VEHICLE MONITORING</h1>
                        <small class="text-muted text-uppercase tracking-wider fs-10">Warehouse Logistics
                            Operations</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span
                        class="badge bg-success-subtle text-success fs-12 px-3 py-2 d-flex align-items-center gap-1 border border-success-subtle rounded-pill">
                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;"></span>
                        Live Updates Active
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-toggle="fullscreen">
                        <i class="bx bx-fullscreen me-1 align-middle" style="font-size: 14px;"></i>
                        <span class="fullscreen-text">Fullscreen</span>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary px-3" id="btnRefreshData">
                        <i class="ri-refresh-line me-1 align-middle"></i> Reload
                    </button>
                    <div class="digital-clock" id="live-clock">00:00:00</div>
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4 px-4">
            <!-- KPI Counters Grid -->
            <!-- Row 1: Gula & Import -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-2 mb-2">
                <!-- Gula Tebu -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">Gula Tebu</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-primary" id="kpi-gulatebu-ton">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TON</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-success" id="kpi-gulatebu-truck">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TRUCK</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Gula Kelapa -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">Gula Kelapa</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-primary" id="kpi-gulakelapa-ton">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TON</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-success" id="kpi-gulakelapa-truck">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TRUCK</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Gula Kelapa Grade B -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">Gula Kelapa B</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-primary" id="kpi-gulakelapab-ton">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TON</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-success" id="kpi-gulakelapab-truck">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TRUCK</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Gula Pasir -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">Gula Pasir</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-primary" id="kpi-gulapasir-ton">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TON</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-success" id="kpi-gulapasir-truck">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TRUCK</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Import -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">Import</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-primary" id="kpi-import-ton">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TON</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-success" id="kpi-import-truck">0</span>
                                <span class="text-muted" style="font-size: 9px;"> TRUCK</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Transaction Types & Area Queues -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-2 mb-3">
                <!-- Slipsheet -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">Slipsheet
                            (Truck)</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-info" id="kpi-slipsheet-in">0</span>
                                <span class="text-muted" style="font-size: 9px;"> IN</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-muted" id="kpi-slipsheet-out">0</span>
                                <span class="text-muted" style="font-size: 9px;"> OUT</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Curah -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">Curah (Truck)</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-info" id="kpi-curah-in">0</span>
                                <span class="text-muted" style="font-size: 9px;"> IN</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-muted" id="kpi-curah-out">0</span>
                                <span class="text-muted" style="font-size: 9px;"> OUT</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- SMU -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">SMU Area
                            (Truck)</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-info" id="kpi-smu-in">0</span>
                                <span class="text-muted" style="font-size: 9px;"> IN</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-muted" id="kpi-smu-out">0</span>
                                <span class="text-muted" style="font-size: 9px;"> OUT</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- WPM -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">WPM Area
                            (Truck)</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-info" id="kpi-wpm-in">0</span>
                                <span class="text-muted" style="font-size: 9px;"> IN</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-muted" id="kpi-wpm-out">0</span>
                                <span class="text-muted" style="font-size: 9px;"> OUT</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- WRM -->
                <div class="col">
                    <div class="premium-card kpi-box h-100 py-2 px-3">
                        <span class="text-muted text-uppercase fw-semibold fs-10 tracking-wider">WRM Area
                            (Truck)</span>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div>
                                <span class="fs-16 fw-bold text-info" id="kpi-wrm-in">0</span>
                                <span class="text-muted" style="font-size: 9px;"> IN</span>
                            </div>
                            <div class="border-start ps-2 border-secondary-subtle">
                                <span class="fs-16 fw-bold text-muted" id="kpi-wrm-out">0</span>
                                <span class="text-muted" style="font-size: 9px;"> OUT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="row g-4">
                <!-- WPM Area -->
                <div class="col-xl-6">
                    <div class="premium-card h-100">
                        <div class="card-hdr wpm d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold fs-15 text-primary"><span class="status-dot text-primary"
                                    style="color: var(--primary);"></span>WPM AREA (BONGKAR)</h5>
                            <span
                                class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 fs-12 rounded-pill"
                                id="badge-wpm">0</span>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table class="table premium-table" id="table-wpm">
                                    <thead>
                                        <tr>
                                            <th>Check In</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Item</th>
                                            <th>Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-wpm">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Mengambil data...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 px-1"
                                id="pagination-wpm"></div>
                        </div>
                    </div>
                </div>

                <!-- WRM Unload -->
                <div class="col-xl-6">
                    <div class="premium-card h-100">
                        <div class="card-hdr wrm d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold fs-15 text-warning"><span class="status-dot text-warning"
                                    style="color: var(--warning);"></span>WRM (UNLOAD BONGKAR)</h5>
                            <span
                                class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 fs-12 rounded-pill"
                                id="badge-wrm">0</span>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table class="table premium-table" id="table-wrm">
                                    <thead>
                                        <tr>
                                            <th>Check In</th>
                                            <th>No. SPB</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>QC Status</th>
                                            <th>Unloading</th>
                                            <th>Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-wrm">
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Mengambil data...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 px-1"
                                id="pagination-wrm"></div>
                        </div>
                    </div>
                </div>

                <!-- WFG Finished Goods -->
                <div class="col-xl-6">
                    <div class="premium-card h-100">
                        <div class="card-hdr wfg d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold fs-15 text-info"><span class="status-dot text-info"
                                    style="color: var(--info);"></span>WFG (BONGKAR MUAT)</h5>
                            <span
                                class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 fs-12 rounded-pill"
                                id="badge-wfg">0</span>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table class="table premium-table" id="table-wfg">
                                    <thead>
                                        <tr>
                                            <th>Check In</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Item</th>
                                            <th>Status</th>
                                            <th>Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-wfg">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Mengambil data...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 px-1"
                                id="pagination-wfg"></div>
                        </div>
                    </div>
                </div>

                <!-- SMU Area -->
                <div class="col-xl-6">
                    <div class="premium-card h-100">
                        <div class="card-hdr smu d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold fs-15 text-secondary"><span class="status-dot text-secondary"
                                    style="color: #94a3b8;"></span>SMU AREA</h5>
                            <span
                                class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 fs-12 rounded-pill"
                                id="badge-smu">0</span>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table class="table premium-table" id="table-smu">
                                    <thead>
                                        <tr>
                                            <th>Check In</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Item</th>
                                            <th>Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-smu">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Mengambil data...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 px-1"
                                id="pagination-smu"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts Section -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="{{ asset('material/assets/libs/moment/min/moment.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

        <script>
            $(document).ready(function() {
                // Live clock ticking
                function updateClock() {
                    const now = new Date();
                    const timeString = now.toLocaleTimeString('en-US', {
                        hour12: false
                    });
                    $('#live-clock').text(timeString);
                }
                setInterval(updateClock, 1000);
                updateClock();

                // Fullscreen toggling logic
                $('[data-toggle="fullscreen"]').on('click', function(e) {
                    e.preventDefault();
                    if (!document.fullscreenElement &&
                        !document.mozFullScreenElement && !document.webkitFullscreenElement && !document
                        .msFullscreenElement) {
                        if (document.documentElement.requestFullscreen) {
                            document.documentElement.requestFullscreen();
                        } else if (document.documentElement.msRequestFullscreen) {
                            document.documentElement.msRequestFullscreen();
                        } else if (document.documentElement.mozRequestFullScreen) {
                            document.documentElement.mozRequestFullScreen();
                        } else if (document.documentElement.webkitRequestFullscreen) {
                            document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        } else if (document.mozCancelFullScreen) {
                            document.mozCancelFullScreen();
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        }
                    }
                });

                function exitHandler() {
                    const icon = $('[data-toggle="fullscreen"] i');
                    const text = $('[data-toggle="fullscreen"] .fullscreen-text');
                    if (document.fullscreenElement || document.webkitFullscreenElement || document
                        .mozFullScreenElement || document.msFullscreenElement) {
                        icon.removeClass('bx-fullscreen').addClass('bx-exit-fullscreen');
                        text.text('Exit Fullscreen');
                    } else {
                        icon.removeClass('bx-exit-fullscreen').addClass('bx-fullscreen');
                        text.text('Fullscreen');
                    }
                }

                document.addEventListener('fullscreenchange', exitHandler);
                document.addEventListener('webkitfullscreenchange', exitHandler);
                document.addEventListener('mozfullscreenchange', exitHandler);
                document.addEventListener('MSFullscreenChange', exitHandler);

                const itemsPerPage = 5;
                let currentPages = {
                    'WPM': 0,
                    'WRM': 0,
                    'WFG': 0,
                    'SMU': 0
                };
                let globalQueues = {
                    'WPM': [],
                    'WRM': [],
                    'WFG': [],
                    'SMU': []
                };

                // Fetch and render dashboard queues
                function loadDashboardData() {
                    $.ajax({
                        url: "{{ route('dashboard.vehicle.data') }}",
                        type: "GET",
                        dataType: "json",
                        success: function(response) {
                            globalQueues = response.queues || {
                                'WPM': [],
                                'WRM': [],
                                'WFG': [],
                                'SMU': []
                            };

                            // Sanitize page indexes in case data size shrank
                            Object.keys(globalQueues).forEach(key => {
                                const totalPages = Math.ceil(globalQueues[key].length /
                                    itemsPerPage);
                                if (currentPages[key] >= totalPages) {
                                    currentPages[key] = 0;
                                }
                            });

                            renderDashboardPages();
                            renderCounters(response.counts);
                        },
                        error: function(xhr) {
                            console.error("Gagal memuat data dashboard", xhr);
                        }
                    });
                }

                // Render KPI counters
                function renderCounters(counts) {
                    // Update table headers badges
                    $('#badge-wpm').text(counts.wpm);
                    $('#badge-wrm').text(counts.wrm);
                    $('#badge-wfg').text(counts.wfg);
                    $('#badge-smu').text(counts.smu);

                    // Row 1: Gula & Import
                    const items = counts.item_kpis || {};

                    $('#kpi-gulatebu-ton').text(parseFloat(items.gula_tebu?.ton || 0).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }));
                    $('#kpi-gulatebu-truck').text(items.gula_tebu?.truck || 0);

                    $('#kpi-gulakelapa-ton').text(parseFloat(items.gula_kelapa?.ton || 0).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }));
                    $('#kpi-gulakelapa-truck').text(items.gula_kelapa?.truck || 0);

                    $('#kpi-gulakelapab-ton').text(parseFloat(items.gula_kelapa_grade_b?.ton || 0).toLocaleString(
                        'id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2
                        }));
                    $('#kpi-gulakelapab-truck').text(items.gula_kelapa_grade_b?.truck || 0);

                    $('#kpi-gulapasir-ton').text(parseFloat(items.gula_pasir?.ton || 0).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }));
                    $('#kpi-gulapasir-truck').text(items.gula_pasir?.truck || 0);

                    $('#kpi-import-ton').text(parseFloat(items.import?.ton || 0).toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }));
                    $('#kpi-import-truck').text(items.import?.truck || 0);

                    // Row 2: Slipsheet, Curah, SMU, WPM, WRM
                    $('#kpi-slipsheet-in').text(counts.slipsheet?.in || 0);
                    $('#kpi-slipsheet-out').text(counts.slipsheet?.out || 0);

                    $('#kpi-curah-in').text(counts.curah?.in || 0);
                    $('#kpi-curah-out').text(counts.curah?.out || 0);

                    $('#kpi-smu-in').text(counts.smu_details?.in || 0);
                    $('#kpi-smu-out').text(counts.smu_details?.out || 0);

                    $('#kpi-wpm-in').text(counts.wpm_details?.in || 0);
                    $('#kpi-wpm-out').text(counts.wpm_details?.out || 0);

                    $('#kpi-wrm-in').text(counts.wrm_details?.in || 0);
                    $('#kpi-wrm-out').text(counts.wrm_details?.out || 0);
                }

                // Render active vehicles slice into respective tables based on current page
                function renderDashboardPages() {
                    const tables = {
                        'WPM': $('#body-wpm'),
                        'WRM': $('#body-wrm'),
                        'WFG': $('#body-wfg'),
                        'SMU': $('#body-smu')
                    };

                    Object.keys(tables).forEach(key => {
                        const tbody = tables[key];
                        if (!tbody) return;

                        tbody.empty();

                        const list = globalQueues[key] || [];
                        const currentPage = currentPages[key];
                        const totalCount = list.length;
                        const totalPages = Math.ceil(totalCount / itemsPerPage);

                        if (totalCount === 0) {
                            let colsCount = 5;
                            if (key === 'WPM') colsCount = 5;
                            if (key === 'WRM') colsCount = 7;
                            if (key === 'WFG') colsCount = 6;
                            tbody.html(
                                `<tr><td colspan="${colsCount}" class="text-center text-muted py-4 small text-uppercase" style="height: 225px; vertical-align: middle;">Kosong</td></tr>`
                            );

                            // Clear pagination indicator
                            $(`#pagination-${key.toLowerCase()}`).empty();
                            return;
                        }

                        // Get slice for the current page
                        const start = currentPage * itemsPerPage;
                        const end = start + itemsPerPage;
                        const slice = list.slice(start, end);

                        // Render rows
                        slice.forEach(tx => {
                            // Determine background class for timers
                            let durationClass = 'timer-badge';
                            let warningRow = '';

                            if (tx.is_bottleneck) {
                                durationClass = 'timer-badge danger-limit';
                                warningRow = 'table-danger-custom';
                            }

                            let tglMasuk = moment(tx.check_in_time).format('DD-MM-YYYY');
                            let jamMasuk = moment(tx.check_in_time).format('HH:mm');

                            let rowHtml = '';
                            if (key === 'WPM') {
                                rowHtml = `
                                    <tr class="${warningRow}" id="row-tx-${tx.id}">
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fs-12 text-muted">${tglMasuk}</span>
                                                <span class="fs-12 fw-bold text-light">${jamMasuk}</span>
                                            </div>
                                        </td>
                                        <td><span class="fw-semibold">${tx.no_pol}</span></td>
                                        <td>${tx.vendor}</td>
                                        <td><strong>${tx.item}</strong></td>
                                        <td>
                                            <span class="dashboard-timer ${durationClass}" data-start="${tx.arrival_time}" data-limit="${tx.limit_minutes}">
                                                Calculated...
                                            </span>
                                        </td>
                                    </tr>
                                `;
                            } else if (key === 'WRM') {
                                let qcStatusBadge = '';
                                if (tx.qc_status === 'waiting_dokumen') {
                                    qcStatusBadge =
                                        `<span class="badge-status waiting">Waiting Dokumen</span>`;
                                } else if (tx.qc_status === 'on_check') {
                                    qcStatusBadge =
                                        `<span class="badge-status process">On Check</span>`;
                                } else if (tx.qc_status === 'released') {
                                    qcStatusBadge =
                                        `<span class="badge-status success">Released</span>`;
                                } else if (tx.qc_status === 'rejected') {
                                    qcStatusBadge = `<span class="badge-status danger">Rejected</span>`;
                                } else {
                                    qcStatusBadge =
                                        `<span class="badge bg-soft-secondary text-secondary">${tx.qc_status}</span>`;
                                }

                                let unloadingStatusBadge = '';
                                if (tx.status === 'antri_sampling') {
                                    unloadingStatusBadge =
                                        `<span class="badge-status waiting">Menunggu QC</span>`;
                                } else if (tx.status === 'sampling') {
                                    unloadingStatusBadge =
                                        `<span class="badge-status process">Proses Sampling</span>`;
                                } else if (tx.status === 'wrm_bongkar') {
                                    unloadingStatusBadge =
                                        `<span class="badge-status process">Proses Bongkar</span>`;
                                } else {
                                    unloadingStatusBadge =
                                        `<span class="badge bg-soft-secondary text-secondary">${tx.unloading_status}</span>`;
                                }

                                rowHtml = `
                                    <tr class="${warningRow}" id="row-tx-${tx.id}">
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fs-12 text-muted">${tglMasuk}</span>
                                                <span class="fs-12 fw-bold text-light">${jamMasuk}</span>
                                            </div>
                                        </td>
                                        <td><span class="fw-semibold">${tx.no_spb}</span></td>
                                        <td><span class="fw-semibold">${tx.no_pol}</span></td>
                                        <td>${tx.vendor}</td>
                                        <td>${qcStatusBadge}</td>
                                        <td>${unloadingStatusBadge}</td>
                                        <td>
                                            <span class="dashboard-timer ${durationClass}" data-start="${tx.arrival_time}" data-limit="${tx.limit_minutes}">
                                                Calculated...
                                            </span>
                                        </td>
                                    </tr>
                                `;
                            } else if (key === 'WFG') {
                                let statusWfgBadge;
                                if (tx.status === 'wfg_muat') {
                                    statusWfgBadge =
                                        `<span class="badge-status waiting">Antri Muat</span>`;
                                } else {
                                    statusWfgBadge =
                                        `<span class="badge-status process">Proses Muat</span>`;
                                }
                                rowHtml = `
                                    <tr class="${warningRow}" id="row-tx-${tx.id}">
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fs-12 text-muted">${tglMasuk}</span>
                                                <span class="fs-12 fw-bold text-light">${jamMasuk}</span>
                                            </div>
                                        </td>
                                        <td><span class="fw-semibold">${tx.no_pol}</span></td>
                                        <td>${tx.vendor}</td>
                                        <td><strong>${tx.item}</strong></td>
                                        <td>${statusWfgBadge}</td>
                                        <td>
                                            <span class="dashboard-timer ${durationClass}" data-start="${tx.arrival_time}" data-limit="${tx.limit_minutes}">
                                                Calculated...
                                            </span>
                                        </td>
                                    </tr>
                                `;
                            } else if (key === 'SMU') {
                                rowHtml = `
                                    <tr class="${warningRow}" id="row-tx-${tx.id}">
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fs-12 text-muted">${tglMasuk}</span>
                                                <span class="fs-12 fw-bold text-light">${jamMasuk}</span>
                                            </div>
                                        </td>
                                        <td><span class="fw-semibold">${tx.no_pol}</span></td>
                                        <td>${tx.vendor}</td>
                                        <td><strong>${tx.item}</strong></td>
                                        <td>
                                            <span class="dashboard-timer ${durationClass}" data-start="${tx.arrival_time}" data-limit="${tx.limit_minutes}">
                                                Calculated...
                                            </span>
                                        </td>
                                    </tr>
                                `;
                            }

                            tbody.append(rowHtml);
                        });

                        // Pad table to prevent layout shifts
                        const currentRenderedRows = slice.length;
                        if (currentRenderedRows < itemsPerPage) {
                            const paddingRowsNeeded = itemsPerPage - currentRenderedRows;
                            let colsCount = 5;
                            if (key === 'WPM') colsCount = 5;
                            if (key === 'WRM') colsCount = 7;
                            if (key === 'WFG') colsCount = 6;
                            for (let i = 0; i < paddingRowsNeeded; i++) {
                                tbody.append(`
                                    <tr style="height: 38px; border-bottom: 1px solid rgba(255, 255, 255, 0.015);">
                                        <td colspan="${colsCount}" class="py-2">&nbsp;</td>
                                    </tr>
                                `);
                            }
                        }

                        // Render pagination indicator
                        renderPaginationIndicator(key, currentPage, totalPages, start, end, totalCount);
                    });
                }

                function renderPaginationIndicator(key, currentPage, totalPages, start, end, totalCount) {
                    const pagContainer = $(`#pagination-${key.toLowerCase()}`);
                    if (!pagContainer.length) return;

                    pagContainer.empty();

                    if (totalPages <= 1) {
                        pagContainer.html(`
                            <span class="pagination-info">Total ${totalCount} Truk</span>
                            <div></div>
                        `);
                        return;
                    }

                    const displayEnd = Math.min(end, totalCount);
                    const infoText = `Showing ${start + 1}-${displayEnd} of ${totalCount}`;

                    let dotsHtml = '<div class="d-flex align-items-center">';
                    for (let i = 0; i < totalPages; i++) {
                        const activeClass = i === currentPage ? `active ${key.toLowerCase()}` : '';
                        dotsHtml +=
                            `<span class="pagination-dot ${activeClass}" data-page="${i}" style="cursor: pointer;"></span>`;
                    }
                    dotsHtml += '</div>';

                    pagContainer.html(`
                        <span class="pagination-info">${infoText}</span>
                        ${dotsHtml}
                    `);

                    // Allow manual click on dots to switch pages instantly
                    pagContainer.find('.pagination-dot').on('click', function() {
                        const page = $(this).data('page');
                        currentPages[key] = page;
                        renderDashboardPages();
                    });
                }

                // Auto switch pages every 8 seconds
                function autoSwitchPages() {
                    let changed = false;

                    Object.keys(globalQueues).forEach(key => {
                        const list = globalQueues[key] || [];
                        const totalPages = Math.ceil(list.length / itemsPerPage);

                        if (totalPages > 1) {
                            currentPages[key] = (currentPages[key] + 1) % totalPages;
                            changed = true;
                        }
                    });

                    if (changed) {
                        renderDashboardPages();
                    }
                }
                setInterval(autoSwitchPages, 10000);

                // Timer update logic for dashboards
                function updateDashboardTimers() {
                    $('.dashboard-timer').each(function() {
                        const startString = $(this).data('start'); // Format: Y-m-d H:i:s
                        const limitMinutes = parseInt($(this).data('limit')) || 0;

                        const arrivalTime = moment(startString, "YYYY-MM-DD HH:mm:ss");
                        const diffSeconds = moment().diff(arrivalTime, 'seconds');

                        const hours = Math.floor(diffSeconds / 3600);
                        const minutes = Math.floor((diffSeconds % 3600) / 60);
                        const seconds = diffSeconds % 60;

                        let timeStr = '';
                        if (hours > 0) {
                            timeStr += hours + 'j ';
                        }
                        timeStr += minutes + 'm ' + seconds + 'd';

                        $(this).text(timeStr);

                        // Check if it's exceeded limits
                        if (limitMinutes > 0 && minutes >= limitMinutes) {
                            $(this).removeClass('timer-badge').addClass('timer-badge danger-limit');
                            $(this).closest('tr').css('background-color', 'rgba(239, 68, 68, 0.08)');
                        }
                    });
                }

                // Real-time Event Listener with Laravel Echo
                function setupRealtimeEcho() {
                    if (typeof window.Echo === 'function') {
                        window.Pusher = Pusher;
                        window.Echo = new window.Echo({
                            broadcaster: 'reverb',
                            key: '{{ config('broadcasting.connections.reverb.key') }}',
                            wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}' || window
                                .location.hostname,
                            wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                            wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                            forceTLS: '{{ config('broadcasting.connections.reverb.options.scheme', 'http') }}' ===
                                'https',
                            enabledTransports: ['ws', 'wss'],
                        });
                    }

                    if (window.Echo && typeof window.Echo.channel === 'function') {
                        console.log('Listening for vehicle updates on Echo channel in standalone dashboard...');
                        window.Echo.channel('vehicle-tracking')
                            .listen('.vehicle.updated', (payload) => {
                                console.log('Echo event received in dashboard:', payload);
                                if (window.toastr) {
                                    toastr.info(payload.message, 'Update Lokasi Truk');
                                }
                                triggerRocketLaunch();
                                loadDashboardData();
                            });
                    } else {
                        setTimeout(setupRealtimeEcho, 100);
                    }
                }

                function triggerRocketLaunch() {
                    const rocket = $('#rocket');
                    if (rocket.hasClass('launch')) return; // Prevent double launch

                    rocket.addClass('launch');

                    // Emit smoke particles while launching
                    const particleInterval = setInterval(() => {
                        const offset = rocket.offset();
                        if (offset.top < -50 || offset.left > $(window).width() + 50) {
                            clearInterval(particleInterval);
                            return;
                        }

                        // Emit particle from bottom-left part of rocket
                        createSmokeParticle(offset.left + 15, offset.top + 55);
                    }, 80);

                    // Remove class after animation finishes
                    setTimeout(() => {
                        rocket.removeClass('launch');
                        clearInterval(particleInterval);
                    }, 5000);
                }

                function createSmokeParticle(x, y) {
                    const particle = $('<div class="smoke-particle"></div>');
                    particle.css({
                        left: x + 'px',
                        top: y + 'px'
                    });
                    $('body').append(particle);

                    // Remove after animation finishes
                    setTimeout(() => {
                        particle.remove();
                    }, 1200);
                }

                // Initial calls
                loadDashboardData();
                setupRealtimeEcho();

                // Launch rocket on load with a small delay
                setTimeout(triggerRocketLaunch, 1500);

                // Auto-update timers every second
                setInterval(updateDashboardTimers, 1000);

                // Launch rocket periodically every 30s
                setInterval(triggerRocketLaunch, 30000);

                // Manual Refresh
                $('#btnRefreshData').on('click', function() {
                    triggerRocketLaunch();
                    loadDashboardData();
                    if (window.toastr) {
                        toastr.success('Data dashboard berhasil diperbarui.');
                    }
                });
            });
        </script>
    </body>

</html>
