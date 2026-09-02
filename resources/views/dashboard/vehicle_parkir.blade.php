<!DOCTYPE html>
<html lang="id" data-layout-mode="dark">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kantong Parkir Monitoring — Real-Time Slots</title>

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
            background: linear-gradient(135deg, #ffffff 40%, #a855f7 100%);
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
        }

        .nav-switch-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-color: var(--border-accent);
        }

        .nav-switch-btn.active {
            background: rgba(168, 85, 247, 0.25);
            border-color: #a855f7;
            color: #d8b4fe;
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.3);
        }

        /* ── Main Container ── */
        .parkir-container {
            max-width: 1400px;
            margin: 24px auto;
            padding: 0 20px 60px 20px;
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        /* Summary Header Bar */
        .summary-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }

        .summary-card {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .summary-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-sub);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .summary-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            margin-top: 4px;
        }

        /* ── Dynamic Zona Parking Block (Matching Image 2 Blueprint) ── */
        .zone-card-block {
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 24px 28px 28px 28px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.45);
        }

        .zone-header-title {
            text-align: center;
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        /* Zone Enclosure Outer Box */
        .zone-slots-enclosure {
            border: 2px solid #0f172a;
            background: #ffffff;
            padding: 18px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        /* Grid of Slots */
        .slots-row-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(115px, 1fr));
            gap: 14px;
        }

        /* Individual Parking Slot Box */
        .slot-box {
            height: 185px;
            border: 2px solid #0f172a;
            border-radius: 6px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            cursor: pointer;
            transition: all 0.25s ease;
            padding: 10px 6px;
        }

        .slot-box:hover {
            border-color: #a855f7;
            background: #faf5ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(168, 85, 247, 0.25);
        }

        .slot-box.occupied {
            background: #ffffff;
        }

        .slot-box.empty {
            background: #f8fafc;
        }

        /* Truck Sprite inside slot */
        .slot-truck-sprite {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.4s ease;
        }

        .slot-truck-icon {
            width: 44px;
            height: 72px;
            flex-shrink: 0;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.35));
        }

        .slot-plate-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 800;
            background: #0f172a;
            color: #d8b4fe;
            border: 1.5px solid rgba(168, 85, 247, 0.5);
            padding: 4px 10px;
            border-radius: 6px;
            margin-top: 6px;
            white-space: nowrap;
            max-width: 110px;
            overflow: hidden;
            text-overflow: ellipsis;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.35);
            letter-spacing: 0.6px;
        }

        /* Bottom Row Labels (Kode Slot) */
        .kode-slot-footer-row {
            display: flex;
            align-items: center;
        }

        .kode-slot-title-col {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            width: 70px;
            flex-shrink: 0;
            padding-right: 10px;
        }

        .kode-slot-labels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
            gap: 12px;
            flex-grow: 1;
            text-align: center;
        }

        .slot-code-label-item {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
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
            <div class="brand-title">KANTONG PARKIR MONITORING</div>
            <span class="badge bg-purple-subtle text-white border border-purple-subtle px-2 py-1" style="font-size:11px;background:rgba(168,85,247,0.2);">
                <i class="ri-parking-box-line me-1"></i> Live Parking Slots
            </span>
        </div>

        <!-- View Switcher -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard.vehicle.visual') }}" class="nav-switch-btn">
                <i class="ri-building-line"></i> 1. Vehicle Yard Docks
            </a>
            <a href="{{ route('dashboard.vehicle.parkir') }}" class="nav-switch-btn active">
                <i class="ri-parking-box-line"></i> 2. Kantong Parkir
            </a>
            <a href="{{ route('dashboard.vehicle') }}" class="nav-switch-btn">
                <i class="ri-table-line"></i> Table View
            </a>
            <button class="nav-switch-btn" id="btnRefresh" title="Refresh Data">
                <i class="ri-refresh-line"></i>
            </button>
        </div>

        <!-- Status & Clock -->
        <div class="d-flex align-items-center gap-3">
            <div style="font-size:11px;color:var(--text-sub);">
                <span>Clock: </span>
                <strong id="live-clock" style="font-family:'JetBrains Mono';color:#ffffff;font-size:12px;">00:00:00</strong>
            </div>
            <div style="font-size:11px;color:var(--text-sub);">
                <span>API Status: </span>
                <strong id="api-status-badge" style="color:#10b981;">Online</strong>
            </div>
        </div>
    </div>

    <!-- ── Main Parkir Container ── -->
    <div class="parkir-container">

        <!-- Summary KPIs -->
        <div class="summary-bar">
            <div class="summary-card">
                <span class="summary-label">Total Zona Parkir</span>
                <span class="summary-val" id="sum-total-zona">0</span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Total Kapasitas Slot</span>
                <span class="summary-val" id="sum-total-slot">0</span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Slot Terisi (Mobil)</span>
                <span class="summary-val" id="sum-total-terisi" style="color:#f43f5e;">0</span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Slot Kosong (Tersedia)</span>
                <span class="summary-val" id="sum-total-kosong" style="color:#10b981;">0</span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Occupancy Rate</span>
                <span class="summary-val" id="sum-occupancy-pct" style="color:#a855f7;">0%</span>
            </div>
        </div>

        <!-- Dynamic Parking Zones Container (Image 2 Blueprint) -->
        <div id="dynamic-zones-list" class="d-flex flex-column gap-4">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <div>Memuat data kantong parkir langsung dari server...</div>
            </div>
        </div>

    </div>

    <!-- ── TRUCK INSPECTOR MODAL DRAWER ── -->
    <div class="truck-inspector-modal" id="truckInspector">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-25">
            <div>
                <div style="font-size:10px;text-transform:uppercase;color:var(--text-sub);font-weight:700;">Detail Slot & Kendaraan</div>
                <div id="insp-plat" style="font-family:'JetBrains Mono';font-size:20px;font-weight:800;color:#60a5fa;">-</div>
            </div>
            <button class="btn-close btn-close-white" id="btnCloseInspector" style="font-size:10px;"></button>
        </div>
        <div>
            <div class="inspector-row"><span class="label">Kode Slot</span><span class="val" id="insp-slot" style="color:#a855f7;font-weight:800;font-size:14px;">-</span></div>
            <div class="inspector-row"><span class="label">Status Slot</span><span class="val" id="insp-status">-</span></div>
            <div class="inspector-row"><span class="label">No. Antrian</span><span class="val" id="insp-antrian" style="color:#f59e0b;">-</span></div>
            <div class="inspector-row"><span class="label">Gedung Tujuan</span><span class="val" id="insp-target" style="color:#10b981;">-</span></div>
            <div class="inspector-row"><span class="label">Driver</span><span class="val" id="insp-driver">-</span></div>
            <div class="inspector-row"><span class="label">Vendor</span><span class="val" id="insp-vendor">-</span></div>
            <div class="inspector-row"><span class="label">Item Muatan</span><span class="val" id="insp-item">-</span></div>
            <div class="inspector-row"><span class="label">Waktu Masuk</span><span class="val" id="insp-checkin">-</span></div>
            <div class="inspector-row" style="border:none;"><span class="label">Zona</span><span class="val" id="insp-zona">-</span></div>
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
    const API_KANTONG_PARKIR = "{{ route('dashboard.vehicle.kantong_parkir') }}";
    let allSlotsState = {};

    function updateClock() {
        $('#live-clock').text(moment().format('HH:mm:ss'));
    }
    setInterval(updateClock, 1000);
    updateClock();

    /**
     * Top-Down Vehicle SVG Silhouette matching Image 2 sketch
     */
    function getTruckSVG() {
        return `
            <svg viewBox="0 0 50 90" class="slot-truck-icon">
                <rect x="7" y="10" width="36" height="74" rx="5" fill="rgba(0,0,0,0.25)" />
                <rect x="8" y="24" width="34" height="58" rx="3" fill="#111827" stroke="#000000" stroke-width="1.5" />
                <line x1="8" y1="42" x2="42" y2="42" stroke="#374151" stroke-width="1" />
                <line x1="8" y1="60" x2="42" y2="60" stroke="#374151" stroke-width="1" />
                <rect x="11" y="6" width="28" height="22" rx="4" fill="#1f2937" stroke="#000000" stroke-width="1.5" />
                <path d="M 14 11 Q 25 8 36 11 L 34 16 Q 25 14 16 16 Z" fill="#60a5fa" />
                <rect x="16" y="17" width="18" height="8" rx="1.5" fill="#111827" />
                <rect x="7" y="11" width="3" height="6" rx="1" fill="#000000" />
                <rect x="40" y="11" width="3" height="6" rx="1" fill="#000000" />
                <circle cx="14" cy="7" r="1.5" fill="#fef08a" />
                <circle cx="36" cy="7" r="1.5" fill="#fef08a" />
                <rect x="5" y="14" width="3" height="8" rx="1" fill="#000000" />
                <rect x="42" y="14" width="3" height="8" rx="1" fill="#000000" />
                <rect x="5" y="64" width="3" height="9" rx="1" fill="#000000" />
                <rect x="42" y="64" width="3" height="9" rx="1" fill="#000000" />
                <rect x="5" y="74" width="3" height="9" rx="1" fill="#000000" />
                <rect x="42" y="74" width="3" height="9" rx="1" fill="#000000" />
            </svg>
        `;
    }

    /**
     * Render Dynamic Parking Zones & Slots based purely on API response
     */
    function renderZones(data, summary) {
        const container = $('#dynamic-zones-list');
        container.empty();

        if (summary) {
            $('#sum-total-zona').text(summary.total_zona || data.length || 0);
            $('#sum-total-slot').text(summary.total_slot || 0);
            $('#sum-total-terisi').text(summary.total_terisi || 0);
            $('#sum-total-kosong').text(summary.total_kosong || 0);
            $('#sum-occupancy-pct').text(`${summary.global_occupancy_percentage || 0}%`);
        }

        if (!data || !data.length) {
            container.html(`
                <div class="alert alert-warning text-center" role="alert">
                    <i class="ri-alert-line me-1"></i> Tidak ada data zona parkir yang diterima dari API.
                </div>
            `);
            return;
        }

        data.forEach((zone, zIdx) => {
            const zoneName = zone.nama_zona || `ZONA ${zIdx + 1}`;
            const slots = zone.slots || [];

            let slotsHtml = '';
            let labelsHtml = '';

            slots.forEach(s => {
                const isOccupied = s.status_slot === 'terisi' || (s.active_vehicle && s.active_vehicle.no_pol);
                allSlotsState[s.id] = { slot: s, zone: zone };

                slotsHtml += `
                    <div class="slot-box ${isOccupied ? 'occupied' : 'empty'}" onclick="inspectSlot(${s.id})" title="Slot ${s.kode_slot} (${isOccupied ? 'Terisi' : 'Kosong'})">
                        ${isOccupied ? `
                            <div class="slot-truck-sprite">
                                ${getTruckSVG()}
                                <div class="slot-plate-text">${s.active_vehicle ? s.active_vehicle.no_pol : 'TERISI'}</div>
                            </div>
                        ` : ''}
                    </div>
                `;

                labelsHtml += `
                    <div class="slot-code-label-item">${s.kode_slot || '-'}</div>
                `;
            });

            const zoneCardHtml = `
                <div class="zone-card-block">
                    <!-- Parent Zone Title on Top (Image 2) -->
                    <div class="zone-header-title">${zoneName}</div>

                    <!-- Enclosure Box for Slots -->
                    <div class="zone-slots-enclosure">
                        <div class="slots-row-grid">
                            ${slotsHtml}
                        </div>
                    </div>

                    <!-- Bottom Labels Row: "Kode Slot" + Slot IDs -->
                    <div class="kode-slot-footer-row">
                        <div class="kode-slot-title-col">
                            <div>Kode</div>
                            <div>Slot</div>
                        </div>
                        <div class="kode-slot-labels-grid">
                            ${labelsHtml}
                        </div>
                    </div>
                </div>
            `;

            container.append(zoneCardHtml);
        });
    }

    /**
     * Fetch Live Data from Kantong Parkir API
     */
    function loadParkirData() {
        $.ajax({
            url: API_KANTONG_PARKIR,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    $('#api-status-badge').text('Online').css('color', '#10b981');
                    renderZones(res.data, res.summary_all);
                } else {
                    $('#api-status-badge').text('Warning').css('color', '#f59e0b');
                    renderZones(res.data || [], res.summary_all || null);
                }
            },
            error: function(err) {
                $('#api-status-badge').text('Offline').css('color', '#ef4444');
                $('#dynamic-zones-list').html(`
                    <div class="alert alert-danger text-center" role="alert">
                        <i class="ri-error-warning-line me-1"></i> Gagal terhubung ke API Kantong Parkir (10.11.11.10:8093). Pastikan jaringan atau server API aktif.
                    </div>
                `);
            }
        });
    }

    /**
     * Inspect Slot / Vehicle
     */
    window.inspectSlot = function(slotId) {
        const item = allSlotsState[slotId];
        if (!item) return;

        const s = item.slot;
        const z = item.zone;
        const v = s.active_vehicle;

        $('#insp-slot').text(s.kode_slot || '-');
        $('#insp-zona').text(z.nama_zona || '-');
        $('#insp-status').text(s.status_slot ? s.status_slot.toUpperCase() : 'KOSONG');

        if (v) {
            $('#insp-plat').text(v.no_pol || '-');
            $('#insp-antrian').text(v.no_antrian ? `NO. ${v.no_antrian}` : 'Belum Dipanggil');
            $('#insp-target').text(v.target_location || '-');
            $('#insp-driver').text(`${v.nama_driver || '-'} (${v.no_hp_driver || '-'})`);
            $('#insp-vendor').text(v.vendor || '-');
            $('#insp-item').text(v.item || '-');
            $('#insp-checkin').text(v.check_in_time || '-');
        } else {
            $('#insp-plat').text('SLOT KOSONG');
            $('#insp-antrian').text('-');
            $('#insp-target').text('-');
            $('#insp-driver').text('-');
            $('#insp-vendor').text('-');
            $('#insp-item').text('-');
            $('#insp-checkin').text('-');
        }

        $('#truckInspector').fadeIn(200);
    };

    $('#btnCloseInspector').on('click', () => { $('#truckInspector').fadeOut(150); });
    $('#btnRefresh').on('click', () => {
        loadParkirData();
        toastr.success('Data Kantong Parkir diperbarui', 'Refreshed');
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
                    toastr.info(payload.message || 'Status parkir diperbarui', 'Vehicle Tracking');
                    setTimeout(loadParkirData, 800);
                });
        } catch (e) {
            console.warn('Reverb init failed:', e);
        }
    }

    $(function() {
        loadParkirData();
        initReverb();
        setInterval(loadParkirData, 5000);
    });
    </script>
</body>

</html>
