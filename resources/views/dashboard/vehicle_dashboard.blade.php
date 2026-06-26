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
                padding: 14px 16px;
                border-bottom: 2px solid var(--border-color);
            }

            .premium-table td {
                background-color: transparent !important;
                padding: 12px 16px;
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
                padding: 20px;
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
        </style>
    </head>

    <body>
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
                    <button class="btn btn-sm btn-outline-secondary px-3" id="btnRefreshData">
                        <i class="ri-refresh-line me-1 align-middle"></i> Reload
                    </button>
                    <div class="digital-clock" id="live-clock">00:00:00</div>
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4 px-4">
            <!-- KPI Counters Grid -->
            <div class="row g-4 mb-4">
                <!-- Total Truk -->
                <div class="col-xl-3 col-md-6">
                    <div class="premium-card kpi-box">
                        <div class="kpi-icon"><i class="ri-truck-line text-primary"></i></div>
                        <span class="text-muted text-uppercase fw-semibold fs-11 tracking-wider">Total Truk di
                            Area</span>
                        <h2 class="display-6 fw-bold mt-2 mb-0 glow-blue text-primary" id="count-total">0</h2>
                    </div>
                </div>
                <!-- WPM QC -->
                <div class="col-xl-3 col-md-6">
                    <div class="premium-card kpi-box">
                        <div class="kpi-icon"><i class="ri-flask-line text-success"></i></div>
                        <span class="text-muted text-uppercase fw-semibold fs-11 tracking-wider">Antrian QC WPM</span>
                        <h2 class="display-6 fw-bold mt-2 mb-0 glow-green text-success" id="count-wpm">0</h2>
                    </div>
                </div>
                <!-- Loading -->
                <div class="col-xl-3 col-md-6">
                    <div class="premium-card kpi-box">
                        <div class="kpi-icon"><i class="ri-download-2-line text-info"></i></div>
                        <span class="text-muted text-uppercase fw-semibold fs-11 tracking-wider">Proses
                            Bongkar/Muat</span>
                        <h2 class="display-6 fw-bold mt-2 mb-0 glow-blue text-info" id="count-loading">0</h2>
                    </div>
                </div>
                <!-- Bottlenecks -->
                <div class="col-xl-3 col-md-6">
                    <div class="premium-card kpi-box" id="kpi-bottlenecks-card">
                        <div class="kpi-icon"><i class="ri-error-warning-line text-danger"></i></div>
                        <span class="text-muted text-uppercase fw-semibold fs-11 tracking-wider">Bottlenecks (>
                            Limit)</span>
                        <h2 class="display-6 fw-bold mt-2 mb-0 glow-red text-danger" id="count-bottlenecks">0</h2>
                    </div>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="row g-4">
                <!-- WPM QC Area -->
                <div class="col-xl-6">
                    <div class="premium-card h-100">
                        <div class="card-hdr wpm d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold fs-15 text-success"><span class="status-dot text-success"
                                    style="color: var(--success);"></span>WPM (QC AREA)</h5>
                            <span
                                class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 fs-12 rounded-pill"
                                id="badge-wpm">0</span>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive" style="max-height: 380px;">
                                <table class="table premium-table" id="table-wpm">
                                    <thead>
                                        <tr>
                                            <th>Check In</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>QC Status</th>
                                            <th>Status</th>
                                            <th>Durasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-wpm">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Mengambil data...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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
                            <div class="table-responsive" style="max-height: 380px;">
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
                            <div class="table-responsive" style="max-height: 380px;">
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
                            <div class="table-responsive" style="max-height: 380px;">
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

                // Fetch and render dashboard queues
                function loadDashboardData() {
                    $.ajax({
                        url: "{{ route('dashboard.vehicle.data') }}",
                        type: "GET",
                        dataType: "json",
                        success: function(response) {
                            renderQueues(response.queues);
                            renderCounters(response.counts);
                        },
                        error: function(xhr) {
                            console.error("Gagal memuat data dashboard", xhr);
                        }
                    });
                }

                // Render KPI counters
                function renderCounters(counts) {
                    $('#count-total').text(counts.total);
                    $('#count-wpm').text(counts.wpm);

                    // Unloading process total = WRM + WFG
                    $('#count-loading').text(counts.wrm + counts.wfg);
                    $('#count-bottlenecks').text(counts.bottlenecks);

                    // Add alarm blink if bottlenecks > 0
                    if (counts.bottlenecks > 0) {
                        $('#kpi-bottlenecks-card').addClass('border-danger glow-red').css('background',
                            'rgba(239, 68, 68, 0.08)');
                    } else {
                        $('#kpi-bottlenecks-card').removeClass('border-danger glow-red').css('background',
                            'var(--card-bg)');
                    }

                    // Update table headers badges
                    $('#badge-wpm').text(counts.wpm);
                    $('#badge-wrm').text(counts.wrm);
                    $('#badge-wfg').text(counts.wfg);
                    $('#badge-smu').text(counts.smu);
                }

                // Render active vehicles into respective tables
                function renderQueues(queues) {
                    const tables = {
                        'WPM': $('#body-wpm'),
                        'WRM': $('#body-wrm'),
                        'WFG': $('#body-wfg'),
                        'SMU': $('#body-smu')
                    };

                    // Clear tables
                    Object.values(tables).forEach(tbody => tbody.empty());

                    // Loop and populate rows
                    Object.keys(queues).forEach(key => {
                        const list = queues[key];
                        const tbody = tables[key];

                        if (!tbody) return; // Skip if table is not defined

                        if (list.length === 0) {
                            let colsCount = 5;
                            if (key === 'WPM') colsCount = 6;
                            if (key === 'WRM') colsCount = 7;
                            if (key === 'WFG') colsCount = 6;
                            tbody.html(
                                `<tr><td colspan="${colsCount}" class="text-center text-muted py-4 small text-uppercase">Kosong</td></tr>`
                            );
                            return;
                        }

                        list.forEach(tx => {
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
                                let qcStatusBadge = '';
                                if (tx.qc_status === 'waiting_dokumen') {
                                    qcStatusBadge =
                                        `<span class="badge-status waiting">Waiting Dokumen</span>`;
                                } else {
                                    qcStatusBadge =
                                        `<span class="badge-status process">On Check</span>`;
                                }

                                let statusBadge = '';
                                if (tx.status === 'antri_sampling') {
                                    statusBadge =
                                        `<span class="badge bg-soft-warning text-warning text-uppercase">Antri Sampling</span>`;
                                } else if (tx.status === 'wpm_qc') {
                                    statusBadge =
                                        `<span class="badge bg-soft-info text-info text-uppercase">Proses Sampling</span>`;
                                }

                                rowHtml = `
                                <tr class="${warningRow}" id="row-tx-${tx.id}">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fs-12 text-muted">${tglMasuk}</span>
                                            <span class="fs-12 fw-bold text-light">${jamMasuk}</span>
                                        </div>
                                    </td>
                                    <td><span class="text-primary fw-semibold">${tx.no_pol}</span></td>
                                    <td>${tx.vendor}</td>
                                    <td>${qcStatusBadge}</td>
                                    <td>${statusBadge}</td>
                                    <td>
                                        <span class="dashboard-timer ${durationClass}" data-start="${tx.arrival_time}" data-limit="${tx.limit_minutes}">
                                            Calculated...
                                        </span>
                                    </td>
                                </tr>
                            `;
                            } else if (key === 'WRM') {
                                let qcStatusBadge =
                                    `<span class="badge-status success">${tx.qc_status}</span>`;
                                let unloadingStatusBadge =
                                    `<span class="badge-status process">${tx.unloading_status}</span>`;
                                rowHtml = `
                                <tr class="${warningRow}" id="row-tx-${tx.id}">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fs-12 text-muted">${tglMasuk}</span>
                                            <span class="fs-12 fw-bold text-light">${jamMasuk}</span>
                                        </div>
                                    </td>
                                    <td><span class="text-muted fw-semibold">${tx.no_spb}</span></td>
                                    <td><span class="text-primary fw-semibold">${tx.no_pol}</span></td>
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
                                    <td><span class="text-primary fw-semibold">${tx.no_pol}</span></td>
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
                                    <td><span class="text-primary fw-semibold">${tx.no_pol}</span></td>
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
                    });
                }

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
                                loadDashboardData();
                            });
                    } else {
                        setTimeout(setupRealtimeEcho, 100);
                    }
                }

                // Initial calls
                loadDashboardData();
                setupRealtimeEcho();

                // Auto-update timers every second
                setInterval(updateDashboardTimers, 1000);

                // Manual Refresh
                $('#btnRefreshData').on('click', function() {
                    loadDashboardData();
                    if (window.toastr) {
                        toastr.success('Data dashboard berhasil diperbarui.');
                    }
                });
            });
        </script>
    </body>

</html>
