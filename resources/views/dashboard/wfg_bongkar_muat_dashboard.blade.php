@extends('layouts.app')

@section('title', ' | WFG Dashboard Bongkar Muat')

@section('sidebar-size', 'sm')

@section('styles')
    <style>
        .kpi-card {
            transition: transform .2s, box-shadow .2s;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .08);
        }

        .kpi-icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 22px;
        }

        .dashboard-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-draft {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-submitted {
            background: #eff6ff;
            color: #2563eb;
        }

        .badge-approved {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .badge-loaded {
            background: #fffbeb;
            color: #d97706;
        }

        .badge-verified {
            background: #f0fdf4;
            color: #16a34a;
        }

        .badge-rejected {
            background: #fef2f2;
            color: #dc2626;
        }

        .filter-tabs .nav-link {
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            padding: 6px 14px;
        }

        .filter-tabs .nav-link.active {
            background: #6366f1;
            color: #fff;
        }

        .table-wavepick td {
            vertical-align: middle;
            font-size: 13px;
        }

        .table-wavepick th {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
        }

        .bg-soft-indigo {
            background: rgba(99, 102, 241, .1);
            color: #6366f1;
        }

        .bg-soft-green {
            background: rgba(34, 197, 94, .1);
            color: #16a34a;
        }

        .bg-soft-amber {
            background: rgba(245, 158, 11, .1);
            color: #d97706;
        }

        .bg-soft-cyan {
            background: rgba(6, 182, 212, .1);
            color: #0891b2;
        }

        .bg-soft-violet {
            background: rgba(168, 85, 247, .1);
            color: #7c3aed;
        }

        .bg-soft-red {
            background: rgba(239, 68, 68, .1);
            color: #dc2626;
        }

        .summary-stat {
            text-align: center;
            padding: 10px 14px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .summary-stat .val {
            font-size: 22px;
            font-weight: 700;
        }

        .summary-stat .lbl {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Truck Visualization Styles */
        .live-dot {
            width: 10px;
            height: 10px;
            background: #ef4444;
            border-radius: 50%;
            display: inline-block;
            animation: livePulse 1s infinite;
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.7);
        }

        @keyframes livePulse {
            0% {
                transform: scale(1);
                opacity: 1;
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1.3);
                opacity: 0.7;
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(1);
                opacity: 1;
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .truck-item {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 15px;
            min-width: 280px;
            flex: 0 0 auto;
            position: relative;
            transition: all 0.3s ease;
        }

        .truck-item:hover {
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1);
            border-color: #6366f1;
        }

        .truck-icon-wrapper {
            font-size: 38px;
            color: #6366f1;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 60px;
            border-radius: 10px;
        }

        .truck-anim {
            animation: truck-move 2s infinite ease-in-out;
        }

        @keyframes truck-move {

            0%,
            100% {
                transform: translateX(-5px);
            }

            50% {
                transform: translateX(5px);
            }
        }

        .gate-label {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #6366f1;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .truck-info {
            min-width: 0;
        }

        .item-list-mini {
            font-size: 11px;
            color: #64748b;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e2e8f0;

            height: 100px;
            /* ganti max-height */
            overflow-y: auto;

            display: block;
            padding-right: 4px;
        }

        /* Optional: scrollbar lebih kecil */
        .item-list-mini::-webkit-scrollbar {
            width: 4px;
        }

        .item-list-mini::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .item-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #cbd5e1;
            margin-right: 5px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Header --}}
            <div class="row mb-4 align-items-center">
                <div class="col-md-5 mb-3 mb-md-0">
                    <h3 class="mb-1 fw-bold" style="letter-spacing:-.02em">WFG Dashboard Bongkar Muat</h3>
                    <p class="text-muted mb-0 small"><i class="bx bx-loader-circle me-1"></i>Monitoring real-time aktivitas
                        bongkar muat & wavepick</p>
                </div>
                <div class="col-md-7">
                    <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                        <div class="input-group input-group-sm" style="width:auto">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="date" id="filterStart" class="form-control">
                        </div>
                        <div class="input-group input-group-sm" style="width:auto">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="date" id="filterEnd" class="form-control">
                        </div>
                        <button id="btnFilter" class="btn btn-primary btn-sm px-3">
                            <i class="bx bx-filter-alt me-1"></i>Apply
                        </button>
                        <button id="btnRefresh" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-refresh"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-indigo me-3"><i class="bx bx-box"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Total QTY
                                Box</h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiTotalBox">0</h3>
                        <small class="text-muted">Full + Receh</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-green me-3"><i class="bx bx-package"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">QTY Full
                                Palet</h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiQtyFull">0</h3>
                        <small class="text-muted">Palet penuh (box)</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-amber me-3"><i class="mdi mdi-distribute-horizontal-left"></i>
                            </div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">QTY Receh
                            </h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiQtyReceh">0</h3>
                        <small class="text-muted">Box</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-cyan me-3"><i class="bx bx-calendar-check"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Orders
                                Hari Ini</h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiToday">0</h3>
                        <small class="text-muted" id="kpiTodayQty">QTY Full: 0</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-violet me-3"><i class="bx bx-check-shield"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Verified
                            </h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiVerified">0</h3>
                        <small class="text-muted">Selesai diverifikasi</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-red me-3"><i class="bx bx-error-circle"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Pending
                            </h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiPending">0</h3>
                        <small class="text-muted">Submitted + Approved</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-indigo me-3"><i class="mdi mdi-truck"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Truck
                                Finish</h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiTruckFinish">0</h3>
                        <small class="text-muted">Truck Slipsheet + Curah</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-cyan me-3"><i class="bx bx-receipt"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Truck
                                Slipsheet</h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiTruckSlipsheet">0</h3>
                        <small class="text-muted">Jml Slipsheet > 0</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-amber me-3"><i class="bx bx-box"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Truck
                                Curah</h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiTruckCurah">0</h3>
                        <small class="text-muted">Tanpa Slipsheet</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-violet me-3"><i class="bx bx-package"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Outbound
                                BAS</h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiOutboundBAS">0</h3>
                        <small class="text-muted">Pallet (Principal BAS)</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-red me-3"><i class="bx bx-package"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Outbound
                                SMU</h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiOutboundSMU">0</h3>
                        <small class="text-muted">Pallet (Non-BAS)</small>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="card dashboard-card kpi-card h-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-soft-green me-3"><i class="bx bx-layer"></i></div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase" style="font-size:10.5px">Total
                                Outbound</h6>
                        </div>
                        <h3 class="fw-bold mb-0 fs-4" id="kpiTotalOutbound">0</h3>
                        <small class="text-muted">Total Pallet BAS + SMU</small>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-8">
                    <div class="card dashboard-card h-100 p-3">
                        <h6 class="fw-bold mb-0"><i class="bx bx-bar-chart-alt-2 me-2 text-primary"></i>Trend Bongkar Muat
                            & QTY Harian</h6>
                        <div id="chartTrend" style="height:310px; width:100%"></div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card dashboard-card h-100 p-3">
                        <h6 class="fw-bold mb-0"><i class="bx bx-pie-chart-alt-2 me-2 text-primary"></i>Distribusi Status
                        </h6>
                        <div id="chartStatus" style="height:310px; width:100%"></div>
                        {{-- Status summary pills --}}
                        <div class="d-flex flex-wrap gap-1 justify-content-center mt-2" id="statusPills"></div>
                    </div>
                </div>
            </div>

            {{-- Live Loading Monitor (Draft) --}}
            <div class="row">
                <div class="col-12">
                    <div class="card dashboard-card border-0 bg-transparent shadow-none mb-0">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0 text-uppercase tracking-wider align-items-center"
                                style="font-size:12px">
                                <span class="live-dot me-2"></span>
                                <i class="mdi mdi-truck-delivery me-2 text-primary fs-5 "></i>Live Loading Monitor (Draft)
                            </h6>
                            <span class="badge bg-soft-indigo text-indigo px-3">Status: DRAFT</span>
                        </div>
                        <div class="d-flex gap-3 overflow-auto pb-3" id="loadingVisualContainer"
                            style="scrollbar-width: thin;">
                            {{-- Content loaded via JS --}}
                            <div class="text-center w-100 py-5 text-muted">
                                <i class="bx bx-loader bx-spin fs-2 mb-2"></i>
                                <p class="mb-0">Memuat visualisasi truk...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Wavepick List by Status + Destination Chart --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-8">
                    <div class="card dashboard-card h-100 p-0 overflow-hidden">
                        <div
                            class="card-header p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="fw-bold mb-0"><i class="bx bx-list-ul me-2 text-primary"></i>List Wavepick Belum
                                Diverifikasi</h6>
                            <ul class="nav filter-tabs gap-1 flex-nowrap" id="statusTabs">
                                <li class="nav-item"><a class="nav-link active" href="#"
                                        data-status="all">Semua</a></li>
                                {{-- <li class="nav-item"><a class="nav-link" href="#" data-status="draft">Draft</a>
                                </li> --}}
                                <li class="nav-item"><a class="nav-link" href="#"
                                        data-status="submitted">Submitted</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"
                                        data-status="approved">Approved</a></li>
                                <li class="nav-item"><a class="nav-link" href="#"
                                        data-status="finished">Finished</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:380px">
                                <table class="table table-hover align-middle mb-0 table-wavepick">
                                    <thead class="bg-light" style="position:sticky;top:0;z-index:1">
                                        <tr>
                                            <th class="ps-3">Tanggal</th>
                                            <th>Wavepick SMU</th>
                                            <th>Wavepick BAS</th>
                                            <th>Destinasi</th>
                                            <th>Gate</th>
                                            <th class="text-center">Full</th>
                                            <th class="text-center">Receh</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="wavepickTable">
                                        <tr>
                                            <td colspan="10" class="text-center py-4 text-muted">
                                                <i class="bx bx-loader bx-spin me-1"></i>Loading...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card dashboard-card h-100 p-3">
                        <h6 class="fw-bold mb-0"><i class="bx bx-map-pin me-2 text-primary"></i>Top Destinasi</h6>
                        <div id="chartDestination" style="height:380px; width:100%"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const API = {
            kpi: '/api/dashboard/wfg/bongkar-muat/kpi',
            wavepick: '/api/dashboard/wfg/bongkar-muat/wavepick-status',
            trend: '/api/dashboard/wfg/bongkar-muat/chart-trend',
            status: '/api/dashboard/wfg/bongkar-muat/chart-status',
            destination: '/api/dashboard/wfg/bongkar-muat/chart-destination',
            visual: '/api/dashboard/wfg/bongkar-muat/loading-visual',
        };

        const STATUS_COLOR = {
            draft: '#94a3b8',
            submitted: '#3b82f6',
            approved: '#a855f7',
            finished: '#f59e0b',
            verified: '#16a34a',
            rejected: '#dc2626'
        };

        const STATUS_LABEL = {
            draft: 'Draft',
            submitted: 'Submitted',
            approved: 'Approved',
            finished: 'Finished',
            verified: 'Verified',
            rejected: 'Rejected'
        };

        function fmt(n) {
            return Number(n || 0).toLocaleString('id-ID');
        }

        function animCount($el, end) {
            $({
                v: 0
            }).animate({
                v: end
            }, {
                duration: 900,
                easing: 'swing',
                step() {
                    $el.text(fmt(Math.round(this.v)));
                },
                complete() {
                    $el.text(fmt(end));
                }
            });
        }

        function getParams() {
            return {
                start_date: $('#filterStart').val(),
                end_date: $('#filterEnd').val()
            };
        }

        // ---- Init date defaults ----
        $(function() {
            const today = new Date(),
                s = new Date();
            s.setDate(today.getDate() - 29);
            $('#filterStart').val(s.toISOString().split('T')[0]);
            $('#filterEnd').val(today.toISOString().split('T')[0]);

            Highcharts.setOptions({
                lang: {
                    thousandsSep: '.'
                },
                credits: {
                    enabled: false
                },
                chart: {
                    style: {
                        fontFamily: "'Inter', sans-serif"
                    }
                }
            });

            let trendChart, statusChart, destChart;
            let activeStatus = 'all';

            // ---- KPI ----
            function loadKpi() {
                $.get(API.kpi, getParams(), function(r) {
                    if (!r.status) return;
                    const d = r.data;
                    animCount($('#kpiTotalBox'), d.total_qty_box);
                    animCount($('#kpiQtyFull'), d.total_qty_full);
                    animCount($('#kpiQtyReceh'), d.total_qty_receh);
                    animCount($('#kpiToday'), d.today_count);
                    $('#kpiTodayQty').text('QTY Full: ' + fmt(d.today_qty_full));
                    animCount($('#kpiVerified'), d.status_counts.verified || 0);
                    const pending = (d.status_counts.submitted || 0) + (d.status_counts.approved || 0);
                    animCount($('#kpiPending'), pending);

                    animCount($('#kpiTruckFinish'), d.truck_finish);
                    animCount($('#kpiTruckSlipsheet'), d.truck_slipsheet);
                    animCount($('#kpiTruckCurah'), d.truck_curah);
                    animCount($('#kpiOutboundBAS'), d.outbound_bas);
                    animCount($('#kpiOutboundSMU'), d.outbound_smu);
                    animCount($('#kpiTotalOutbound'), d.total_outbound);

                    // Status pills
                    let pills = '';
                    $.each(d.status_counts, function(s, v) {
                        if (!v) return;
                        pills +=
                            `<span class="status-badge badge-${s}">${STATUS_LABEL[s] || s}: <b>${v}</b></span>`;
                    });
                    $('#statusPills').html(pills);
                });
            }

            // ---- Loading Visual ----
            function loadVisual() {
                $.get(API.visual, getParams(), function(r) {
                    if (!r.status || !r.data.length) {
                        $('#loadingVisualContainer').html(
                            '<div class="text-center w-100 py-4 text-muted"><p class="mb-0 small italic text-muted">Tidak ada data wavepick draft saat ini.</p></div>'
                        );
                        return;
                    }

                    let html = '';
                    r.data.forEach(function(o) {
                        let itemsHtml = '';

                        o.items.forEach(function(it) {
                            itemsHtml += `
                                <div class="mb-1">
                                    <span class="item-dot"></span>
                                    ${it.material} (${fmt(it.qty)})
                                </div>
                            `;
                        });

                        html += `
                            <div class="card shadow-sm truck-item">
                                <div class="gate-label">Gate ${o.gate}</div>
                                <div class="truck-icon-wrapper bg-light">
                                    <i class="mdi mdi-truck-delivery truck-anim"></i>
                                </div>
                                <div class="truck-info">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-bold text-dark">${o.wavepick}</span>
                                        <span class="text-primary fw-semibold">${o.no_mobil}</span>
                                    </div>
                                    <div class="text-muted mb-1"><i class="bx bx-map-pin me-1"></i>${o.destinasi}</div>
                                    <div class="text-muted mb-2"><i class="bx bx-user me-1"></i>Checker: <span class="text-dark fw-medium">${o.checker}</span></div>
                                    <div class="text-muted mb-2"><i class="bx bx-user me-1"></i>Forklift Driver: <span class="text-dark fw-medium">${o.forklift_driver}</span></div>
                                    
                                    <div class="d-flex gap-2 mb-2">
                                        <div class="badge bg-light text-dark border w-50 py-2">Full: ${fmt(o.total_full)}</div>
                                        <div class="badge bg-light text-dark border w-50 py-2">Receh: ${fmt(o.total_receh)}</div>
                                    </div>

                                    <div class="item-list-mini">
                                        <div class="fw-semibold mb-1 text-uppercase" style="font-size:9px">
                                            Items Sample:
                                        </div>
                                        ${itemsHtml}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    $('#loadingVisualContainer').html(html);
                });
            }

            // ---- Wavepick Table ----
            function loadWavepick(status) {
                const params = Object.assign(getParams(), {
                    status
                });
                $('#wavepickTable').html(
                    '<tr><td colspan="10" class="text-center py-3 text-muted"><i class="bx bx-loader bx-spin"></i> Loading...</td></tr>'
                );
                $.get(API.wavepick, params, function(r) {
                    if (!r.status || !r.data.length) {
                        $('#wavepickTable').html(
                            '<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data</td></tr>'
                        );
                        return;
                    }
                    let html = '';
                    r.data.forEach(function(o) {
                        html += `<tr>
                            <td class="ps-3">${o.tanggal}</td>
                            <td><span class="fw-semibold">${o.wavepick_smu}</span></td>
                            <td>${o.wavepick_bas}</td>
                            <td>${o.destinasi}</td>
                            <td><span class="badge bg-light text-dark border">${o.gate}</span></td>
                            <td class="text-center fw-semibold text-success">${fmt(o.total_qty_full)}</td>
                            <td class="text-center fw-semibold text-warning">${fmt(o.total_qty_receh)}</td>
                            <td class="text-center fw-bold">${fmt(o.total_qty_box)}</td>
                            <td class="text-center"><span class="status-badge badge-${o.status}">${STATUS_LABEL[o.status] || o.status}</span></td>
                            <td class="text-center">
                                <a href="/wfg/bongkar-muat/show/${o.id}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size:11px">
                                    <i class="bx bx-show-alt"></i>
                                </a>
                            </td>
                        </tr>`;
                    });
                    $('#wavepickTable').html(html);
                });
            }

            // ---- Trend Chart ----
            function loadTrend() {
                $.get(API.trend, getParams(), function(r) {
                    if (!r.status) return;
                    const d = r.data;
                    if (!trendChart) {
                        trendChart = Highcharts.chart('chartTrend', {
                            chart: {
                                type: 'column',
                                backgroundColor: 'transparent',
                                marginTop: 30
                            },
                            title: {
                                text: null
                            },
                            xAxis: {
                                categories: d.categories,
                                crosshair: true,
                                tickWidth: 0,
                                lineColor: '#e2e8f0'
                            },
                            yAxis: [{
                                    min: 0,
                                    title: {
                                        text: 'QTY (Box/Pcs)',
                                        style: {
                                            color: '#64748b',
                                            fontSize: '11px'
                                        }
                                    },
                                    gridLineColor: '#f1f5f9'
                                },
                                {
                                    min: 0,
                                    title: {
                                        text: 'Orders',
                                        style: {
                                            color: '#6366f1',
                                            fontSize: '11px'
                                        }
                                    },
                                    opposite: true,
                                    gridLineWidth: 0
                                }
                            ],
                            tooltip: {
                                shared: true
                            },
                            legend: {
                                verticalAlign: 'top',
                                itemStyle: {
                                    fontWeight: 'normal',
                                    color: '#64748b'
                                }
                            },
                            plotOptions: {
                                column: {
                                    borderRadius: 4,
                                    groupPadding: 0.15
                                },
                                spline: {
                                    marker: {
                                        radius: 3
                                    },
                                    lineWidth: 2.5
                                }
                            },
                            series: d.series
                        });
                    } else {
                        trendChart.xAxis[0].setCategories(d.categories, false);
                        d.series.forEach((s, i) => {
                            trendChart.series[i].setData(s.data, false);
                        });
                        trendChart.redraw();
                    }
                });
            }

            // ---- Status Donut Chart ----
            function loadStatusChart() {
                $.get(API.status, getParams(), function(r) {
                    if (!r.status) return;
                    if (!statusChart) {
                        statusChart = Highcharts.chart('chartStatus', {
                            chart: {
                                type: 'pie',
                                backgroundColor: 'transparent'
                            },
                            title: {
                                text: null
                            },
                            tooltip: {
                                pointFormat: '{series.name}: <b>{point.y}</b> ({point.percentage:.1f}%)'
                            },
                            plotOptions: {
                                pie: {
                                    innerSize: '60%',
                                    borderWidth: 2,
                                    borderColor: '#fff',
                                    dataLabels: {
                                        enabled: true,
                                        format: '<b>{point.name}</b>',
                                        style: {
                                            fontWeight: 'normal'
                                        }
                                    }
                                }
                            },
                            series: [{
                                name: 'Orders',
                                colorByPoint: true,
                                data: r.data
                            }]
                        });
                    } else {
                        statusChart.series[0].setData(r.data);
                    }
                });
            }

            // ---- Destination Bar Chart ----
            function loadDestChart() {
                $.get(API.destination, getParams(), function(r) {
                    if (!r.status) return;
                    const d = r.data;
                    if (!destChart) {
                        destChart = Highcharts.chart('chartDestination', {
                            chart: {
                                type: 'bar',
                                backgroundColor: 'transparent'
                            },
                            title: {
                                text: null
                            },
                            xAxis: {
                                categories: d.categories,
                                gridLineWidth: 0,
                                lineColor: '#e2e8f0'
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: null
                                },
                                gridLineColor: '#f1f5f9'
                            },
                            tooltip: {
                                valueSuffix: ' LO'
                            },
                            legend: {
                                enabled: false
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 5,
                                    dataLabels: {
                                        enabled: true,
                                        style: {
                                            fontWeight: 'normal'
                                        }
                                    }
                                }
                            },
                            series: d.series
                        });
                    } else {
                        destChart.xAxis[0].setCategories(d.categories, false);
                        d.series.forEach((s, i) => {
                            destChart.series[i].setData(s.data, false);
                        });
                        destChart.redraw();
                    }
                });
            }

            // ---- Load All ----
            function loadAll() {
                loadKpi();
                loadVisual();
                loadWavepick(activeStatus);
                loadTrend();
                loadStatusChart();
                loadDestChart();
            }

            loadAll();

            setInterval(() => {
                loadAll();
            }, 120000); // Refresh every 2 minutes
            // }, 300000); // Refresh every 5 minutes

            // ---- Events ----
            $('#btnFilter, #btnRefresh').on('click', loadAll);

            $('#statusTabs').on('click', '.nav-link', function(e) {
                e.preventDefault();
                $('#statusTabs .nav-link').removeClass('active');
                $(this).addClass('active');
                activeStatus = $(this).data('status');
                loadWavepick(activeStatus);
            });
        });
    </script>
@endsection
