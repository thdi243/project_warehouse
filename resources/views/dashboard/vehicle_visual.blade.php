<!DOCTYPE html>
<html lang="id" data-layout-mode="dark">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Vehicle Yard & Loading Docks — Real-Time Monitoring</title>

    <link rel="shortcut icon" href="{{ asset('assets/images/logo/kecap.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('material/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('material/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        :root {
            --bg-deep: #090d16;
            --bg-card: rgba(15, 23, 42, 0.85);
            --border-subtle: rgba(255, 255, 255, 0.1);
            --border-accent: rgba(59, 130, 246, 0.4);
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--bg-deep);
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        /* ── Top Header Navigation Bar ── */
        .top-navbar {
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 14px 28px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-title {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 40%, #60a5fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-switch-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-subtle);
            color: var(--text-sub);
            border-radius: 10px;
            padding: 7px 14px;
            font-size: 12.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .nav-switch-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-color: var(--border-accent);
        }

        .nav-switch-btn.active {
            background: rgba(59, 130, 246, 0.25);
            border-color: #3b82f6;
            color: #60a5fa;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
        }

        /* ── Yard Buildings Container ── */
        .yard-container {
            max-width: 1400px;
            margin: 24px auto;
            padding: 0 20px 60px 20px;
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        /* ── Building Block + Loading Dock Card (Image 1 Blueprint) ── */
        .building-card-wrapper {
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45);
            transition: all 0.3s ease;
        }

        /* Building Top Header Block (Warehouse Industrial Corrugated Roof Style) */
        .building-header-block {
            background-color: #1e293b;
            /* Corrugated metal roof pattern with ridge highlights, valleys, and shadows */
            background-image: 
                linear-gradient(to bottom, rgba(15, 23, 42, 0.5) 0%, transparent 25%, transparent 75%, rgba(15, 23, 42, 0.7) 100%),
                repeating-linear-gradient(90deg, 
                    #0f172a 0px, 
                    #1e293b 4px, 
                    #334155 10px, 
                    #475569 14px, 
                    #1e293b 18px, 
                    #0f172a 20px
                );
            border-bottom: 3px solid #0f172a;
            padding: 32px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.15), inset 0 -4px 8px rgba(0, 0, 0, 0.5);
        }

        /* Top Roof Ridge Cap */
        .building-header-block::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 7px;
            background: linear-gradient(90deg, #475569, #94a3b8, #cbd5e1, #94a3b8, #475569);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        /* Bottom Eaves Shadow */
        .building-header-block::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: rgba(15, 23, 42, 0.9);
            z-index: 1;
        }

        .building-title-pill {
            display: inline-block;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255, 255, 255, 0.18);
            padding: 8px 28px;
            border-radius: 8px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.55);
            position: relative;
            z-index: 2;
        }

        .building-title-text {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
        }

        .building-badge-count {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            z-index: 3;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        /* Loading Dock Attached Lower Section */
        .loading-dock-section {
            background: #ffffff;
            padding: 16px 20px 20px 20px;
            position: relative;
        }

        .dock-section-label {
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            text-align: right;
            margin-bottom: 12px;
        }

        /* Grid of Dock Bays (Fits 10 Docks in a single row) */
        .dock-bays-grid {
            display: grid;
            grid-template-columns: repeat(10, minmax(0, 1fr));
            gap: 10px;
            background: #ffffff;
            border: 1.5px solid #0f172a;
            padding: 14px 12px;
            border-radius: 8px;
        }

        @media (max-width: 1250px) {
            .dock-bays-grid {
                grid-template-columns: repeat(auto-fit, minmax(105px, 1fr));
            }
        }

        /* Single Dock Bay */
        .dock-bay {
            height: 165px;
            border: 1.5px dashed #cbd5e1;
            border-radius: 6px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            cursor: pointer;
            transition: all 0.25s ease;
            overflow: hidden;
            padding: 8px 4px;
        }

        .dock-bay:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .dock-bay.occupied {
            border-style: solid;
            border-color: #0f172a;
            background: #ffffff;
        }

        .dock-bay-num {
            position: absolute;
            top: 6px;
            left: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 800;
            color: #64748b;
            z-index: 5;
        }

        .dock-empty-placeholder {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* ── Truck Sprite in Dock ── */
        .dock-truck-sprite {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        /* ══════════════════════════════════════════════════════════════════
           ANIMASI MAJU MASUK DOCK & MUNDUR KELUAR DOCK
           ══════════════════════════════════════════════════════════════════ */

        /* 1. Animasi Maju Masuk ke Dock (Drive In Forward) */
        .truck-anim-enter {
            animation: driveInForward 1.8s cubic-bezier(0.2, 0.9, 0.3, 1) forwards;
        }

        @keyframes driveInForward {
            0% {
                transform: translateY(110px) scale(0.85);
                opacity: 0;
                filter: blur(2px);
            }
            35% {
                opacity: 1;
                filter: blur(0px);
            }
            85% {
                transform: translateY(-4px) scale(1.02);
            }
            100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        /* 2. Animasi Mundur Keluar dari Dock (Drive Out Backward / Reverse) */
        .truck-anim-exit {
            animation: driveOutBackward 2.0s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            pointer-events: none;
        }

        @keyframes driveOutBackward {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            25% {
                transform: translateY(-5px) scale(1);
                filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.8));
            }
            60% {
                transform: translateY(45px) scale(0.95);
                opacity: 0.8;
            }
            100% {
                transform: translateY(130px) scale(0.8);
                opacity: 0;
            }
        }

        /* Status Indicator Pill */
        .anim-status-tag {
            position: absolute;
            bottom: 6px;
            font-family: 'Outfit', sans-serif;
            font-size: 8.5px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            z-index: 6;
        }

        .anim-status-tag.entering {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
        }

        .anim-status-tag.exiting {
            background: #ef4444;
            color: #ffffff;
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);
            animation: pulseBlink 0.4s infinite alternate;
        }

        @keyframes pulseBlink {
            from { opacity: 1; }
            to { opacity: 0.4; }
        }

        /* SVG Top-down Vehicle Graphic */
        .truck-svg-icon {
            width: 36px;
            height: 60px;
            flex-shrink: 0;
            filter: drop-shadow(0 3px 5px rgba(0, 0, 0, 0.35));
        }

        .truck-plate-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 800;
            background: #0f172a;
            color: #38bdf8;
            border: 1.5px solid rgba(56, 189, 248, 0.4);
            padding: 2.5px 6px;
            border-radius: 5px;
            margin-top: 4px;
            white-space: nowrap;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
            letter-spacing: 0.4px;
            text-align: center;
        }

        .truck-queue-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9.5px;
            font-weight: 800;
            background: #f59e0b;
            color: #000000;
            padding: 2px 5px;
            border-radius: 3px;
            margin-top: 2px;
            box-shadow: 0 2px 5px rgba(245, 158, 11, 0.3);
            white-space: nowrap;
            text-align: center;
        }

        /* Inspector Modal */
        .truck-inspector-modal {
            position: fixed;
            top: 80px;
            right: 24px;
            width: 360px;
            z-index: 200;
            display: none;
            background: var(--bg-card);
            backdrop-filter: blur(25px);
            border: 1px solid var(--border-accent);
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7);
            animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideInRight {
            from { transform: translateX(40px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .inspector-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 12px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
        }
        .inspector-row .label { color: var(--text-sub); }
        .inspector-row .val { font-weight: 600; color: #ffffff; text-align: right; }
    </style>
</head>

<body>

    <!-- ── Top Navigation Bar ── -->
    <div class="top-navbar">
        <div class="brand-logo">
            <div class="brand-title">VEHICLE YARD MONITORING</div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size:11px;">
                <i class="ri-radar-line me-1"></i> Live Docks Animation
            </span>
        </div>

        <!-- View Navigation Switcher & Simulation Controls -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard.vehicle.visual') }}" class="nav-switch-btn active">
                <i class="ri-building-line"></i> 1. Vehicle Yard Docks
            </a>
            <a href="{{ route('dashboard.vehicle.parkir') }}" class="nav-switch-btn">
                <i class="ri-parking-box-line"></i> 2. Kantong Parkir
            </a>
            <a href="{{ route('dashboard.vehicle') }}" class="nav-switch-btn">
                <i class="ri-table-line"></i> Table View
            </a>

            <!-- Interactive Simulation Buttons -->
            <button class="nav-switch-btn text-success border-success-subtle" id="btnSimulateEnter" title="Simulasi Truk Masuk Maju ke Dock">
                <i class="ri-arrow-up-circle-fill text-success"></i> Simulasi Masuk
            </button>
            <button class="nav-switch-btn text-danger border-danger-subtle" id="btnSimulateExit" title="Simulasi Truk Mundur Keluar Dock">
                <i class="ri-arrow-down-circle-fill text-danger"></i> Simulasi Keluar
            </button>

            <button class="nav-switch-btn" id="btnRefresh" title="Refresh Data">
                <i class="ri-refresh-line"></i>
            </button>
        </div>

        <!-- Status / Clock -->
        <div class="d-flex align-items-center gap-3">
            <div style="font-size:11px;color:var(--text-sub);">
                <span>Clock: </span>
                <strong id="live-clock" style="font-family:'JetBrains Mono';color:#ffffff;font-size:12px;">00:00:00</strong>
            </div>
            <div style="font-size:11px;color:var(--text-sub);">
                <span>Total Yard: </span>
                <strong id="total-yard-trucks" style="color:#60a5fa;font-family:'JetBrains Mono';font-size:13px;">0 Truk</strong>
            </div>
        </div>
    </div>

    <!-- ── Yard Buildings List (Exact Match to User Blueprint Sketch) ── -->
    <div class="yard-container">

        <!-- 1. GEDUNG WRM (Raw Material) -->
        <div class="building-card-wrapper" id="card-wrm">
            <div class="building-header-block">
                <div class="building-title-pill">
                    <div class="building-title-text">GEDUNG WRM</div>
                </div>
                <div class="building-badge-count" id="count-wrm">0 TRUK AKTIF</div>
            </div>
            <div class="loading-dock-section">
                <div class="dock-section-label">LOADING DOCK</div>
                <div class="dock-bays-grid" id="docks-wrm">
                    <!-- Dynamic dock bays rendered here by DockAnimationService -->
                </div>
            </div>
        </div>

        <!-- 2. GEDUNG WPM (Packaging Material) -->
        <div class="building-card-wrapper" id="card-wpm">
            <div class="building-header-block">
                <div class="building-title-pill">
                    <div class="building-title-text">GEDUNG WPM</div>
                </div>
                <div class="building-badge-count" id="count-wpm">0 TRUK AKTIF</div>
            </div>
            <div class="loading-dock-section">
                <div class="dock-section-label">LOADING DOCK</div>
                <div class="dock-bays-grid" id="docks-wpm">
                    <!-- Dynamic dock bays rendered here by DockAnimationService -->
                </div>
            </div>
        </div>

        <!-- 3. GEDUNG WFG (Finished Goods) -->
        <div class="building-card-wrapper" id="card-wfg">
            <div class="building-header-block">
                <div class="building-title-pill">
                    <div class="building-title-text">GEDUNG WFG</div>
                </div>
                <div class="building-badge-count" id="count-wfg">0 TRUK AKTIF</div>
            </div>
            <div class="loading-dock-section">
                <div class="dock-section-label">LOADING DOCK</div>
                <div class="dock-bays-grid" id="docks-wfg">
                    <!-- Dynamic dock bays rendered here by DockAnimationService -->
                </div>
            </div>
        </div>

        <!-- 4. GEDUNG SMU & WSP (Utility & Spareparts) -->
        <div class="building-card-wrapper" id="card-smu">
            <div class="building-header-block">
                <div class="building-title-pill">
                    <div class="building-title-text">GEDUNG SMU & WSP</div>
                </div>
                <div class="building-badge-count" id="count-smu">0 TRUK AKTIF</div>
            </div>
            <div class="loading-dock-section">
                <div class="dock-section-label">LOADING DOCK</div>
                <div class="dock-bays-grid" id="docks-smu">
                    <!-- Dynamic dock bays rendered here by DockAnimationService -->
                </div>
            </div>
        </div>

        <!-- 5. TIMBANGAN AREA -->
        <div class="building-card-wrapper" id="card-tmb">
            <div class="building-header-block">
                <div class="building-title-pill">
                    <div class="building-title-text">AREA TIMBANGAN (WEIGHBRIDGE)</div>
                </div>
                <div class="building-badge-count" id="count-tmb">0 TRUK AKTIF</div>
            </div>
            <div class="loading-dock-section">
                <div class="dock-section-label">SCALE PLATFORMS</div>
                <div class="dock-bays-grid" id="docks-tmb">
                    <!-- Dynamic dock bays rendered here by DockAnimationService -->
                </div>
            </div>
        </div>

        <!-- 6. AREA PARKIR / BUFFER (MENUNGGU ANTRIAN / UNKNOWN PARKIR) -->
        <div class="building-card-wrapper" id="card-parkir">
            <div class="building-header-block" style="background-color: #1e1b4b; background-image: linear-gradient(to bottom, rgba(15, 23, 42, 0.6) 0%, transparent 25%, transparent 75%, rgba(15, 23, 42, 0.7) 100%), repeating-linear-gradient(90deg, #0f172a 0px, #1e1b4b 4px, #312e81 10px, #4338ca 14px, #1e1b4b 18px, #0f172a 20px);">
                <div class="building-title-pill" style="border-color: rgba(168, 85, 247, 0.4);">
                    <div class="building-title-text" style="color: #d8b4fe;"><i class="ri-parking-box-line me-2"></i>AREA PARKIR & BUFFER (MENUNGGU PANGGILAN ANTRIAN)</div>
                </div>
                <div class="building-badge-count" id="count-parkir" style="background: rgba(168, 85, 247, 0.25); border-color: #a855f7;">0 TRUK MENUNGGU</div>
            </div>
            <div class="loading-dock-section">
                <div class="dock-section-label" style="color: #a855f7;">PARKING BAYS / BUFFER SLOTS</div>
                <div class="dock-bays-grid" id="docks-parkir">
                    <!-- Dynamic dock bays rendered here by DockAnimationService -->
                </div>
            </div>
        </div>

    </div>

    <!-- ── TRUCK INSPECTOR MODAL DRAWER ── -->
    <div class="truck-inspector-modal" id="truckInspector">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-25">
            <div>
                <div style="font-size:10px;text-transform:uppercase;color:var(--text-sub);font-weight:700;">Detail Kendaraan</div>
                <div id="insp-plat" style="font-family:'JetBrains Mono';font-size:20px;font-weight:800;color:#60a5fa;">-</div>
            </div>
            <button class="btn-close btn-close-white" id="btnCloseInspector" style="font-size:10px;"></button>
        </div>
        <div>
            <div class="inspector-row"><span class="label">No. Antrian</span><span class="val" id="insp-antrian" style="color:#f59e0b;font-weight:800;font-size:14px;">-</span></div>
            <div class="inspector-row"><span class="label">Status</span><span class="val" id="insp-status">-</span></div>
            <div class="inspector-row"><span class="label">Lokasi Saat Ini</span><span class="val" id="insp-loc" style="color:#38bdf8;">-</span></div>
            <div class="inspector-row"><span class="label">Gedung Tujuan</span><span class="val" id="insp-target" style="color:#10b981;">-</span></div>
            <div class="inspector-row"><span class="label">Driver</span><span class="val" id="insp-driver">-</span></div>
            <div class="inspector-row"><span class="label">Vendor</span><span class="val" id="insp-vendor">-</span></div>
            <div class="inspector-row"><span class="label">Item</span><span class="val" id="insp-item">-</span></div>
            <div class="inspector-row"><span class="label">Check-In</span><span class="val" id="insp-checkin">-</span></div>
            <div class="inspector-row" style="border:none;"><span class="label">Durasi</span><span class="val" id="insp-durasi" style="color:#f43f5e;font-weight:700;">-</span></div>
        </div>
    </div>

    <!-- ── SCRIPTS ── -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('material/assets/libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('material/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
    const API_DASHBOARD_DATA = "{{ route('dashboard.vehicle.data') }}";
    let activeVehicles = {};

    function updateClock() {
        $('#live-clock').text(moment().format('HH:mm:ss'));
    }
    setInterval(updateClock, 1000);
    updateClock();

    /**
     * Top-Down Truck SVG Vector (Silhouette with details)
     */
    function getTruckSVG() {
        return `
            <svg viewBox="0 0 50 90" class="truck-svg-icon">
                <!-- Drop shadow -->
                <rect x="7" y="10" width="36" height="74" rx="5" fill="rgba(0,0,0,0.25)" />
                <!-- Truck Trailer Container -->
                <rect x="8" y="24" width="34" height="58" rx="3" fill="#111827" stroke="#000000" stroke-width="1.5" />
                <line x1="8" y1="42" x2="42" y2="42" stroke="#374151" stroke-width="1" />
                <line x1="8" y1="60" x2="42" y2="60" stroke="#374151" stroke-width="1" />
                <!-- Truck Cab -->
                <rect x="11" y="6" width="28" height="22" rx="4" fill="#1f2937" stroke="#000000" stroke-width="1.5" />
                <!-- Windshield Front -->
                <path d="M 14 11 Q 25 8 36 11 L 34 16 Q 25 14 16 16 Z" fill="#60a5fa" />
                <!-- Roof -->
                <rect x="16" y="17" width="18" height="8" rx="1.5" fill="#111827" />
                <!-- Mirrors -->
                <rect x="7" y="11" width="3" height="6" rx="1" fill="#000000" />
                <rect x="40" y="11" width="3" height="6" rx="1" fill="#000000" />
                <!-- Headlights -->
                <circle cx="14" cy="7" r="1.5" fill="#fef08a" />
                <circle cx="36" cy="7" r="1.5" fill="#fef08a" />
                <!-- Wheels -->
                <rect x="5" y="14" width="3" height="8" rx="1" fill="#000000" />
                <rect x="42" y="14" width="3" height="8" rx="1" fill="#000000" />
                <rect x="5" y="64" width="3" height="9" rx="1" fill="#000000" />
                <rect x="42" y="64" width="3" height="9" rx="1" fill="#000000" />
                <rect x="5" y="74" width="3" height="9" rx="1" fill="#000000" />
                <rect x="42" y="74" width="3" height="9" rx="1" fill="#000000" />
            </svg>
        `;
    }

    // ══════════════════════════════════════════════════════════════════
    // DOCK ANIMATION & TRANSITION SERVICE
    // Manages Enter (Drive-In Forward) and Exit (Drive-Out Reverse) Delays
    // ══════════════════════════════════════════════════════════════════
    const DockAnimationService = {
        // Persistent dock bay slots per building key (10 bays per building)
        buildings: {
            WRM: { containerId: 'docks-wrm', countId: 'count-wrm', minSlots: 10, slots: [] },
            WPM: { containerId: 'docks-wpm', countId: 'count-wpm', minSlots: 10, slots: [] },
            WFG: { containerId: 'docks-wfg', countId: 'count-wfg', minSlots: 10, slots: [] },
            SMU: { containerId: 'docks-smu', countId: 'count-smu', minSlots: 10, slots: [] },
            TMB: { containerId: 'docks-tmb', countId: 'count-tmb', minSlots: 6, slots: [] },
            PARKIR: { containerId: 'docks-parkir', countId: 'count-parkir', minSlots: 10, slots: [] }
        },

        init() {
            // Pre-populate empty slots for each building
            Object.keys(this.buildings).forEach(bKey => {
                const b = this.buildings[bKey];
                b.slots = [];
                for (let i = 0; i < b.minSlots; i++) {
                    b.slots.push({
                        bayIndex: i,
                        truckId: null,
                        truck: null,
                        state: 'empty', // 'empty' | 'entering' | 'parked' | 'exiting'
                        animTimer: null
                    });
                }
                this.renderBuilding(bKey);
            });
        },

        /**
         * Update building slots with latest server transactions
         * Handles Drive-In Animation on arrival, and Delayed Drive-Out Animation on exit
         */
        syncBuildingData(buildingKey, latestTrucks) {
            const b = this.buildings[buildingKey];
            if (!b) return;

            const latestIds = new Set(latestTrucks.map(t => t.id));
            const latestMap = {};
            latestTrucks.forEach(t => { latestMap[t.id] = t; });

            // 1. Detect Exits: Any truck currently in a slot that is NO LONGER in latestTrucks
            b.slots.forEach(slot => {
                if (slot.truckId && !latestIds.has(slot.truckId) && slot.state !== 'exiting') {
                    // Start delayed drive-out reverse animation
                    this.triggerDriveOut(buildingKey, slot.bayIndex, slot.truck);
                }
            });

            // 2. Detect Entries: Any truck in latestTrucks not yet assigned to a slot in this building
            latestTrucks.forEach(t => {
                const existingSlot = b.slots.find(s => s.truckId === t.id);
                if (existingSlot) {
                    // Update metadata if already parked or entering
                    existingSlot.truck = t;
                    activeVehicles[t.id] = t;
                } else {
                    // Find first available empty slot
                    let targetSlot = b.slots.find(s => s.state === 'empty');
                    if (!targetSlot) {
                        // Expand dock bays if capacity exceeded
                        const newIdx = b.slots.length;
                        targetSlot = {
                            bayIndex: newIdx,
                            truckId: null,
                            truck: null,
                            state: 'empty',
                            animTimer: null
                        };
                        b.slots.push(targetSlot);
                    }

                    // Start delayed drive-in forward animation
                    this.triggerDriveIn(buildingKey, targetSlot.bayIndex, t);
                }
            });

            // Update badge count
            const activeCount = b.slots.filter(s => s.state === 'parked' || s.state === 'entering').length;
            $(`#${b.countId}`).text(`${activeCount} TRUK ${buildingKey === 'PARKIR' ? 'MENUNGGU' : 'AKTIF'}`);
        },

        /**
         * Trigger Maju Masuk (Drive-In Forward) Animation
         */
        triggerDriveIn(buildingKey, bayIndex, truckData) {
            const b = this.buildings[buildingKey];
            const slot = b.slots[bayIndex];
            if (!slot) return;

            if (slot.animTimer) clearTimeout(slot.animTimer);

            slot.truckId = truckData.id;
            slot.truck = truckData;
            slot.state = 'entering';
            activeVehicles[truckData.id] = truckData;

            this.renderSlotDOM(buildingKey, bayIndex);

            // Complete entry animation after 1.8s
            slot.animTimer = setTimeout(() => {
                slot.state = 'parked';
                this.renderSlotDOM(buildingKey, bayIndex);
            }, 1800);
        },

        /**
         * Trigger Mundur Keluar (Drive-Out Reverse) Animation with Delay
         */
        triggerDriveOut(buildingKey, bayIndex, truckData) {
            const b = this.buildings[buildingKey];
            const slot = b.slots[bayIndex];
            if (!slot || slot.state === 'empty' || slot.state === 'exiting') return;

            if (slot.animTimer) clearTimeout(slot.animTimer);

            slot.state = 'exiting';
            this.renderSlotDOM(buildingKey, bayIndex);

            // Complete exit animation after 2.0s delay, then free up slot
            slot.animTimer = setTimeout(() => {
                slot.truckId = null;
                slot.truck = null;
                slot.state = 'empty';
                slot.animTimer = null;
                this.renderSlotDOM(buildingKey, bayIndex);

                const activeCount = b.slots.filter(s => s.state === 'parked' || s.state === 'entering').length;
                $(`#${b.countId}`).text(`${activeCount} TRUK ${buildingKey === 'PARKIR' ? 'MENUNGGU' : 'AKTIF'}`);
            }, 2000);
        },

        /**
         * Render single dock bay slot in DOM
         */
        renderSlotDOM(buildingKey, bayIndex) {
            const b = this.buildings[buildingKey];
            const slot = b.slots[bayIndex];
            const bayEl = $(`#bay-${buildingKey}-${bayIndex}`);
            if (!bayEl.length) {
                this.renderBuilding(buildingKey);
                return;
            }

            const bayNum = buildingKey === 'PARKIR' ? `SLOT ${bayIndex + 1}` : (buildingKey === 'TMB' ? `SCALE ${bayIndex + 1}` : `DOCK ${bayIndex + 1}`);

            if (slot.state === 'empty') {
                bayEl.attr('class', 'dock-bay').removeAttr('onclick');
                bayEl.html(`
                    <span class="dock-bay-num">${bayNum}</span>
                    <span class="dock-empty-placeholder">KOSONG</span>
                `);
            } else {
                const tx = slot.truck;
                let animClass = '';
                let statusTagHtml = '';

                if (slot.state === 'entering') {
                    animClass = 'truck-anim-enter';
                    statusTagHtml = `<span class="anim-status-tag entering"><i class="ri-arrow-up-line"></i> MASUK</span>`;
                } else if (slot.state === 'exiting') {
                    animClass = 'truck-anim-exit';
                    statusTagHtml = `<span class="anim-status-tag exiting"><i class="ri-arrow-down-line"></i> KELUAR</span>`;
                }

                bayEl.attr('class', 'dock-bay occupied')
                     .attr('onclick', `inspectTruck(${tx.id})`)
                     .attr('title', `Klik untuk detail ${tx.no_pol}`);

                // Hide Queue Number when at Timbangan (in/out) or before queue number is called
                const isTimbangan = (buildingKey === 'TMB' || tx.current_location_code === 'TMB' || ['timbangan_in', 'timbangan_out'].includes(tx.status));
                const isParkir = (buildingKey === 'PARKIR');
                const showQueueBadge = tx.no_antrian && !isTimbangan && !isParkir;

                let bottomBadgeHtml = '';
                if (showQueueBadge) {
                    bottomBadgeHtml = `<div class="truck-queue-badge">ANTRIAN ${tx.no_antrian}</div>`;
                } else if (isParkir) {
                    bottomBadgeHtml = `<div class="truck-queue-badge" style="background:#6366f1;color:#ffffff;font-size:9px;">${tx.parking_status_label || 'UNKNOWN PARKIR'}</div>`;
                }

                bayEl.html(`
                    <span class="dock-bay-num">${bayNum}</span>
                    <div class="dock-truck-sprite ${animClass}">
                        ${getTruckSVG()}
                        <div class="truck-plate-tag">${tx.no_pol || 'TRUK'}</div>
                        ${bottomBadgeHtml}
                    </div>
                    ${statusTagHtml}
                `);
            }
        },

        /**
         * Render entire building grid
         */
        renderBuilding(buildingKey) {
            const b = this.buildings[buildingKey];
            const container = $(`#${b.containerId}`);
            let html = '';

            b.slots.forEach(slot => {
                const bayNum = buildingKey === 'PARKIR' ? `SLOT ${slot.bayIndex + 1}` : (buildingKey === 'TMB' ? `SCALE ${slot.bayIndex + 1}` : `DOCK ${slot.bayIndex + 1}`);
                if (slot.state === 'empty') {
                    html += `
                        <div id="bay-${buildingKey}-${slot.bayIndex}" class="dock-bay">
                            <span class="dock-bay-num">${bayNum}</span>
                            <span class="dock-empty-placeholder">KOSONG</span>
                        </div>
                    `;
                } else {
                    const tx = slot.truck;
                    let animClass = slot.state === 'entering' ? 'truck-anim-enter' : (slot.state === 'exiting' ? 'truck-anim-exit' : '');
                    let statusTagHtml = '';
                    if (slot.state === 'entering') statusTagHtml = `<span class="anim-status-tag entering"><i class="ri-arrow-up-line"></i> MASUK</span>`;
                    else if (slot.state === 'exiting') statusTagHtml = `<span class="anim-status-tag exiting"><i class="ri-arrow-down-line"></i> KELUAR</span>`;

                    const isTimbangan = (buildingKey === 'TMB' || tx.current_location_code === 'TMB' || ['timbangan_in', 'timbangan_out'].includes(tx.status));
                    const isParkir = (buildingKey === 'PARKIR');
                    const showQueueBadge = tx.no_antrian && !isTimbangan && !isParkir;

                    let bottomBadgeHtml = '';
                    if (showQueueBadge) {
                        bottomBadgeHtml = `<div class="truck-queue-badge">ANTRIAN ${tx.no_antrian}</div>`;
                    } else if (isParkir) {
                        bottomBadgeHtml = `<div class="truck-queue-badge" style="background:#6366f1;color:#ffffff;font-size:9px;">${tx.parking_status_label || 'UNKNOWN PARKIR'}</div>`;
                    }

                    html += `
                        <div id="bay-${buildingKey}-${slot.bayIndex}" class="dock-bay occupied" onclick="inspectTruck(${tx.id})" title="Klik untuk detail ${tx.no_pol}">
                            <span class="dock-bay-num">${bayNum}</span>
                            <div class="dock-truck-sprite ${animClass}">
                                ${getTruckSVG()}
                                <div class="truck-plate-tag">${tx.no_pol || 'TRUK'}</div>
                                ${bottomBadgeHtml}
                            </div>
                            ${statusTagHtml}
                        </div>
                    `;
                }
            });

            container.html(html);
        }
    };

    /**
     * Load & Sync Active Vehicle Data from Server
     */
    function loadYardData() {
        $.ajax({
            url: API_DASHBOARD_DATA,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const transactions = res.transactions || [];

                // Group transactions by building
                const wrmTrucks = [];
                const wpmTrucks = [];
                const wfgTrucks = [];
                const smuTrucks = [];
                const tmbTrucks = [];
                const parkirTrucks = [];

                transactions.forEach(tx => {
                    const targetLoc = (tx.target_location_code || tx.target_sloc || '').toUpperCase();
                    const status = (tx.status || '').toLowerCase();
                    const currentLoc = (tx.current_location_code || '').toUpperCase();
                    const hasQueue = !!tx.no_antrian;

                    if (currentLoc === 'TMB' || status === 'timbangan_in' || status === 'timbangan_out') {
                        // Truck currently at Timbangan scale
                        tmbTrucks.push(tx);
                    } else if (hasQueue || ['wrm_bongkar', 'wfg', 'smu', 'wpm'].includes(status)) {
                        // Truck ONLY enters building loading dock if it has a queue number or is actively unloading/loading
                        if (targetLoc.includes('B006') || targetLoc.includes('WRM') || status.includes('wrm')) {
                            wrmTrucks.push(tx);
                        } else if (targetLoc.includes('C001') || targetLoc.includes('WPM') || status.includes('wpm')) {
                            wpmTrucks.push(tx);
                        } else if (targetLoc.includes('A001') || targetLoc.includes('WFG') || status.includes('wfg')) {
                            wfgTrucks.push(tx);
                        } else if (targetLoc.includes('SMU') || targetLoc.includes('WSP') || status.includes('smu')) {
                            smuTrucks.push(tx);
                        } else {
                            wrmTrucks.push(tx);
                        }
                    } else {
                        // Has NOT been called / no queue number yet -> Parked in Kantong Parkir / Buffer
                        tx.parking_status_label = tx.current_slot || 'UNKNOWN PARKIR';
                        parkirTrucks.push(tx);
                    }
                });

                // Feed into Animation Service
                DockAnimationService.syncBuildingData('WRM', wrmTrucks);
                DockAnimationService.syncBuildingData('WPM', wpmTrucks);
                DockAnimationService.syncBuildingData('WFG', wfgTrucks);
                DockAnimationService.syncBuildingData('SMU', smuTrucks);
                DockAnimationService.syncBuildingData('TMB', tmbTrucks);
                DockAnimationService.syncBuildingData('PARKIR', parkirTrucks);

                $('#total-yard-trucks').text(`${transactions.length} Truk`);
            }
        });
    }

    /**
     * Inspector Modal Drawer
     */
    window.inspectTruck = function(txId) {
        const tx = activeVehicles[txId];
        if (!tx) return;

        const isTimbangan = (tx.current_location_code === 'TMB' || ['timbangan_in', 'timbangan_out'].includes(tx.status));

        $('#insp-plat').text(tx.no_pol || '-');
        $('#insp-antrian').text(isTimbangan ? 'Proses Timbangan (Tanpa Antrian)' : (tx.no_antrian ? `NO. ${tx.no_antrian}` : 'Menunggu / Belum Dipanggil'));
        $('#insp-status').text(tx.status ? tx.status.toUpperCase() : '-');
        $('#insp-loc').text(tx.current_location_name || tx.current_location_code || '-');
        $('#insp-target').text(tx.target_location_name || tx.target_location_code || '-');
        $('#insp-driver').text(`${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})`);
        $('#insp-vendor').text(tx.vendor || '-');
        $('#insp-item').text(tx.item || '-');
        $('#insp-checkin').text(tx.check_in_time || '-');
        $('#insp-durasi').text(tx.duration_seconds ? `${Math.floor(tx.duration_seconds / 60)} Menit` : '-');

        $('#truckInspector').fadeIn(200);
    };

    $('#btnCloseInspector').on('click', () => { $('#truckInspector').fadeOut(150); });
    $('#btnRefresh').on('click', () => {
        loadYardData();
        toastr.success('Data Yard Loading Docks diperbarui', 'Refreshed');
    });

    /**
     * Interactive Simulation Buttons
     */
    let simCounter = 900;
    $('#btnSimulateEnter').on('click', function() {
        simCounter++;
        const demoPlat = `B ${Math.floor(1000 + Math.random() * 9000)} DEMO`;
        const demoTx = {
            id: simCounter,
            no_pol: demoPlat,
            no_antrian: `Q-0${(simCounter % 9) + 1}`,
            status: 'wrm_bongkar',
            target_location_name: 'Gedung WRM',
            current_location_name: 'Loading Dock WRM',
            nama_driver: 'Driver Demo',
            no_hp_driver: '08123456789',
            vendor: 'PT Demo Logistics',
            item: 'Kedelai Import Premium',
            check_in_time: moment().format('YYYY-MM-DD HH:mm:ss'),
            duration_seconds: 120
        };

        // Find empty slot in WRM
        const emptySlot = DockAnimationService.buildings.WRM.slots.find(s => s.state === 'empty');
        if (emptySlot) {
            toastr.info(`Truk ${demoPlat} sedang meluncur maju ke Dock ${emptySlot.bayIndex + 1}...`, 'Simulasi Masuk Dock');
            DockAnimationService.triggerDriveIn('WRM', emptySlot.bayIndex, demoTx);
        } else {
            toastr.warning('Semua dock WRM sedang terisi.', 'Perhatian');
        }
    });

    $('#btnSimulateExit').on('click', function() {
        // Find occupied slot in WRM
        const occupiedSlot = DockAnimationService.buildings.WRM.slots.find(s => s.state === 'parked');
        if (occupiedSlot) {
            toastr.warning(`Truk ${occupiedSlot.truck.no_pol} sedang mundur keluar dari Dock ${occupiedSlot.bayIndex + 1}...`, 'Simulasi Keluar Dock');
            DockAnimationService.triggerDriveOut('WRM', occupiedSlot.bayIndex, occupiedSlot.truck);
        } else {
            toastr.info('Tidak ada truk yang sedang parkir di dock WRM untuk simulasi keluar.', 'Info');
        }
    });

    /**
     * Laravel Reverb WebSocket Listener
     */
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
                    toastr.info(payload.message || 'Pergerakan kendaraan diperbarui', 'Vehicle Tracking');
                    setTimeout(loadYardData, 500);
                });
        } catch (e) {
            console.warn('Reverb init failed:', e);
        }
    }

    $(function() {
        DockAnimationService.init();
        loadYardData();
        initReverb();
        setInterval(loadYardData, 5000);
    });
    </script>
</body>

</html>
