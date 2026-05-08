@extends('layouts.app')

@section('title', ' | WRM Inventory Dashboard')

@section('sidebar-size', 'sm')

@section('styles')
<style>
    :root {
        --kpi-total: #3b82f6;
        /* blue-500 */
        --kpi-item: #8b5cf6;
        /* violet-500 */
        --kpi-pallet: #06b6d4;
        /* cyan-500 */
        --kpi-inbound: #22c55e;
        /* green-500 */
        --kpi-draft: #ef4444;
        /* red-500 */
        --kpi-transfer: #f59e0b;
        /* amber-500 */
    }

    .dashboard-card {
        /* background: #ffffff; */
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }

    .kpi-icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 24px;
        color: #fff;
    }

    .dashboard-header-title {
        font-weight: 700;
        /* color: #1e293b; */
        letter-spacing: -0.025em;
    }

    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }

    .bg-soft-blue {
        background: rgba(59, 130, 246, 0.1);
        color: var(--kpi-total);
    }

    .bg-soft-violet {
        background: rgba(139, 92, 246, 0.1);
        color: var(--kpi-item);
    }

    .bg-soft-cyan {
        background: rgba(6, 182, 212, 0.1);
        color: var(--kpi-pallet);
    }

    .bg-soft-green {
        background: rgba(34, 197, 94, 0.1);
        color: var(--kpi-inbound);
    }

    .bg-soft-red {
        background: rgba(239, 68, 68, 0.1);
        color: var(--kpi-draft);
    }

    .bg-soft-amber {
        background: rgba(245, 158, 11, 0.1);
        color: var(--kpi-transfer);
    }

    /* === Warehouse Location Map Styles === */
    .zona-box {
        width: 140px;
        height: 120px;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        display: flex;
        flex-direction: column;
    }

    .zona-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    .zona-box-colors {
        flex-grow: 1;
        width: 100%;
        display: flex;
    }

    .zona-box-color-stripe {
        height: 100%;
        flex: 1;
    }

    .zona-box-empty {
        background: #f8fafc;
        flex-grow: 1;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #cbd5e1;
        border-top: 1px dashed #e2e8f0;
    }

    .zona-box-label {
        background: #ffffff;
        padding: 8px;
        text-align: center;
        font-weight: 700;
        font-size: 14px;
        color: #1e293b;
        border-bottom: 1px solid #e2e8f0;
        z-index: 2;
    }

    .zona-box-stats {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: rgba(255, 255, 255, 0.9);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        color: #475569;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .rack-section {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        background: #fff;
    }

    .rack-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .rack-zone-badge,
    .rack-bin-badge {
        font-size: 11px;
        border-radius: 6px;
        padding: 3px 10px;
        font-weight: 600;
    }

    .rack-zone-badge {
        background: #f1f5f9;
        color: #475569;
    }

    .rack-bin-badge {
        background: #eff6ff;
        color: #3b82f6;
        border: 1px solid #bfdbfe;
    }

    .rack-cell {
        width: 52px;
        height: 52px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
        position: relative;
        border: 2px solid transparent;
    }

    .rack-cell:hover {
        transform: scale(1.15);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 10;
    }

    .rack-cell.empty {
        background: #f8fafc;
        color: #cbd5e1;
        border-color: #e2e8f0;
        border-style: dashed;
    }

    .rack-cell.reserved {
        background: #fdf2f2;
        color: #ef4444;
        border-color: #fca5a5;
        border-style: dashed;
        box-shadow: inset 0 0 0 1px #fee2e2;
    }

    .rack-cell.reserved::after {
        content: 'RE';
        position: absolute;
        font-size: 8px;
        top: 2px;
        right: 4px;
        opacity: 0.6;
    }

    .rack-cell-tooltip {
        display: none;
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: #f8fafc;
        font-size: 11px;
        padding: 8px 12px;
        border-radius: 6px;
        white-space: nowrap;
        z-index: 100;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        pointer-events: none;
        font-weight: normal;
    }

    .rack-cell-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #1e293b;
    }

    .rack-cell:hover .rack-cell-tooltip {
        display: block;
    }

    .rack-row-label {
        font-size: 10px;
        color: #94a3b8;
        font-weight: 600;
        writing-mode: vertical-rl;
        text-orientation: mixed;
        margin-right: 4px;
        align-self: center;
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Header & Filters --}}
        <div class="row mb-4 align-items-center">
            <div class="col-xl-4 col-lg-3 mb-3 mb-lg-0">
                <h3 class="mb-1 dashboard-header-title">Raw Material Overview</h3>
                <p class="text-muted mb-0 small"><i class="bx bx-info-circle me-1"></i>Live enterprise inventory tracking</p>
            </div>

            <div class="col-xl-8 col-lg-9 text-end">
                <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
                    <div class="input-group input-group-sm" style="width: auto;">
                        <span class="input-group-text border-end-0 text-muted"><i class="bx bx-calendar"></i></span>
                        <input type="date" id="filterStartDate" class="form-control border-start-0 ps-0" placeholder="Start Date">
                    </div>
                    <div class="input-group input-group-sm" style="width: auto;">
                        <span class="input-group-text border-end-0 text-muted"><i class="bx bx-calendar"></i></span>
                        <input type="date" id="filterEndDate" class="form-control border-start-0 ps-0" placeholder="End Date">
                    </div>

                    <div>
                        <select id="filterGudang" class="form-select form-select-sm">
                            <option value="">All Gudang</option>
                            @foreach ($locations as $loc)
                            @if ($loc->gudang)
                            <option value="{{ $loc->gudang }}">{{ $loc->gudang }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select id="filterSupplier" class="form-select form-select-sm">
                            <option value="">All Suppliers</option>
                            @foreach ($suppliers as $sup)
                            @if ($sup->nama)
                            <option value="{{ $sup->nama }}">{{ $sup->nama }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <button id="btnFilter" class="btn btn-primary btn-sm px-3 shadow-sm"><i class="bx bx-filter-alt me-1"></i>Apply</button>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="row mb-4 g-3">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card dashboard-card kpi-card h-100 p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="kpi-icon-box bg-soft-blue me-3">
                            <i class="bx bx-package"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase">Total Stock (KG)</h6>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0" id="kpiTotalStock">0</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card dashboard-card kpi-card h-100 p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="kpi-icon-box bg-soft-violet me-3">
                            <i class="bx bx-cube-alt"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase">SKU (ITEMS)</h6>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0" id="kpiTotalItem">0</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card dashboard-card kpi-card h-100 p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="kpi-icon-box bg-soft-cyan me-3">
                            <i class="bx bx-grid-horizontal"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase">Active Pallets</h6>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0" id="kpiActivePallet">0</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card dashboard-card kpi-card h-100 p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="kpi-icon-box bg-soft-green me-3">
                            <i class="bx bx-trending-up"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase">Inbound Tdy (KG)</h6>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0" id="kpiInboundToday">0</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card dashboard-card kpi-card h-100 p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="kpi-icon-box bg-soft-red me-3">
                            <i class="bx bx-arrow-from-bottom"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase">Draft Out Tdy (KG)</h6>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0" id="kpiDraftOutboundToday">0</h3>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card dashboard-card kpi-card h-100 p-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="kpi-icon-box bg-soft-amber me-3">
                            <i class="bx bx-transfer-alt"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-semibold text-uppercase">Transfer Tdy (KG)</h6>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0" id="kpiTransferToday">0</h3>
                </div>
            </div>
        </div>

        {{-- Charts Row 1 --}}
        <div class="row mb-4 g-3">
            <div class="col-xl-8">
                <div class="card dashboard-card h-100 p-3">
                    <h6 class="card-title fw-bold mb-0"><i class="bx bx-line-chart me-2 text-primary"></i>Stock Movement Overview</h6>
                    <div id="chartMovement" style="height: 320px; width: 100%;"></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card dashboard-card h-100 p-3">
                    <h6 class="card-title fw-bold mb-0"><i class="bx bx-pie-chart-alt-2 me-2 text-primary"></i>Stock by Gudang</h6>
                    <div id="chartPie" style="height: 320px; width: 100%;"></div>
                </div>
            </div>
        </div>

        {{-- Charts Row 2 --}}
        <div class="row mb-4 g-3">
            <div class="col-xl-8">
                <div class="card dashboard-card h-100 p-3">
                    <h6 class="card-title fw-bold mb-0"><i class="bx bx-bar-chart-alt me-2 text-primary"></i>Top 5 Material by Qty</h6>
                    <div id="chartBar" style="height: 300px; width: 100%;"></div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card dashboard-card h-100 p-3">
                    <h6 class="card-title fw-bold mb-0"><i class="bx bx-time-five me-2 text-primary"></i>Aging Stock</h6>
                    <div id="chartAging" style="height: 300px; width: 100%;"></div>
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card dashboard-card h-100 p-0 overflow-hidden">
                    <div class="card-header border-bottom p-3 d-flex align-items-center gap-2">
                        <i class="bx bx-list-ol fs-5 text-primary"></i>
                        <h6 class="card-title mb-0 fw-bold">Recent Activities Log</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 350px;">
                            <table class="table table-hover table-striped mb-0 align-middle border-top-0">
                                <thead class="bg-light" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th class="border-top-0">Date</th>
                                        <th class="border-top-0">Type</th>
                                        <th class="border-top-0">Item Name</th>
                                        <th class="border-top-0 text-end">Qty (KG)</th>
                                        <th class="border-top-0">Location</th>
                                    </tr>
                                </thead>
                                <tbody id="tableRecent">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted"><i class="bx bx-loader bx-spin me-2"></i>Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Warehouse Location Map --}}
        <div class="row mt-2" id="sectionLocationMap">
            <div class="col-12">
                <div class="card dashboard-card border-0">
                    <div class="card-header border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="card-title mb-0 fw-bold"><i class="bx bx-grid-alt me-2 text-primary"></i>Warehouse Location Map</h6>
                                <p class="text-muted small mb-0 mt-1">Real-time zone utilization visualization. Click a zone to view detailed bins.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        {{-- Summary Chips --}}
                        <div class="d-flex gap-2 mb-4 flex-wrap" id="locationSummary">
                            <div class="px-3 py-1 rounded-pill text-white shadow-sm d-flex align-items-center gap-1" style="background:#1e293b;font-size:12px;">
                                <i class="bx bx-border-all"></i> Total: <b id="sumTotal">-</b>
                            </div>
                            <div class="px-3 py-1 rounded-pill text-white shadow-sm d-flex align-items-center gap-1" style="background:#3b82f6;font-size:12px;">
                                <i class="bx bx-package"></i> Occupied: <b id="sumOccupied">-</b>
                            </div>
                            <div class="px-3 py-1 rounded-pill shadow-sm d-flex align-items-center gap-1" style="background:#def7ec;color:#03543f;border:1px solid #84e1bc;font-size:12px;">
                                <i class="bx bx-check-circle"></i> Available (Reuse): <b id="sumAvailable">-</b>
                            </div>
                            <div class="px-3 py-1 rounded-pill shadow-sm d-flex align-items-center gap-1" style="background:#fdf2f2;color:#9b1c1c;border:1px solid #f8b4b4;font-size:12px;opacity:0.8;">
                                <i class="bx bx-time"></i> Pending Outbound: <b id="sumReserved">-</b>
                            </div>
                            <div class="px-3 py-1 rounded-pill shadow-sm d-flex align-items-center gap-1" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:12px;display:none;">
                                <i class="bx bx-grid-empty"></i> Empty: <b id="sumEmpty">-</b>
                            </div>
                        </div>

                        {{-- Dynamic MID Legend --}}
                        <div id="midLegendContainer" class="d-flex flex-wrap gap-2 mb-4 p-3 rounded-3 bg-light" style="border:1px solid #e2e8f0; display:none;">
                            <!-- populated by JS -->
                        </div>

                        {{-- Grid Container --}}
                        <div id="locationMapContainer" class="p-2">
                            <div class="text-center text-muted py-5">
                                <i class="bx bx-loader bx-spin" style="font-size:32px;"></i>
                                <p class="mt-2 mb-0">Rendering warehouse map...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Rack Detail Modal --}}
<div class="modal fade" id="zonaDetailModal" tabindex="-1" aria-labelledby="zonaDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold" id="zonaDetailModalLabel"><i class="bx bx-grid me-2 text-primary"></i> Zona Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light" id="zonaDetailContainer">
                <!-- Racks populated dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function formatQty(x) {
        if (x === null || x === undefined) return '0';
        let val = parseFloat(x);
        return val.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    $(document).ready(function() {
        // Color palette for MIDs
        const palette = [
            '#e6194b', '#3cb44b', '#4363d8', '#46f0f0',
            '#f032e6', '#bcf60c', '#fabebe', '#008080',
            '#9a6324', '#800000', '#aaffc3', '#000075',
            '#e6beff', '#808000', '#ffd8b1', '#808080',
            '#00a8e8', '#ff6b6b', '#06d6a0', '#c77dff'
        ];
        let midColorMap = {};
        let colorIndex = 0;

        function getMidColor(mid) {
            if (!mid) return '#cbd5e1';
            if (midColorMap[mid]) return midColorMap[mid];

            const color = palette[colorIndex % palette.length];
            midColorMap[mid] = color;
            colorIndex++;
            return color;
        }

        if ($.fn.select2) {
            $('#filterGudang, #filterSupplier').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                placeholder: 'Select...'
            });
        }

        const today = new Date();
        const start30 = new Date();
        start30.setDate(today.getDate() - 30);
        $('#filterStartDate').val(start30.toISOString().split('T')[0]);
        $('#filterEndDate').val(today.toISOString().split('T')[0]);

        let movementChart, pieChart, barChart, agingChart;
        let globalLocationData = {}; // Store raw location data for modal logic
        let globalUsedMids = {};

        function initCharts() {
            Highcharts.setOptions({
                lang: {
                    thousandsSep: '.'
                },
                chart: {
                    style: {
                        fontFamily: "'Inter', sans-serif"
                    }
                },
                credits: {
                    enabled: false
                }
            });

            movementChart = Highcharts.chart('chartMovement', {
                chart: {
                    type: 'spline',
                    backgroundColor: 'transparent',
                    marginTop: 30
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: [],
                    crosshair: true,
                    tickWidth: 0,
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
                    shared: true,
                    valueSuffix: ' KG'
                },
                legend: {
                    verticalAlign: 'top',
                    y: -10,
                    itemStyle: {
                        fontWeight: 'normal',
                        color: '#64748b'
                    }
                },
                plotOptions: {
                    spline: {
                        marker: {
                            radius: 3,
                            symbol: 'circle'
                        },
                        lineWidth: 3
                    }
                },
                series: []
            });

            pieChart = Highcharts.chart('chartPie', {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y:,.0f} KG</b> ({point.percentage:.1f}%)'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b><br>{point.percentage:.1f} %',
                            style: {
                                fontWeight: 'normal'
                            }
                        },
                        borderWidth: 2,
                        borderColor: '#fff'
                    }
                },
                series: [{
                    name: 'Stock',
                    colorByPoint: true,
                    data: []
                }]
            });

            barChart = Highcharts.chart('chartBar', {
                chart: {
                    type: 'bar',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: [],
                    title: {
                        text: null
                    },
                    gridLineWidth: 0,
                    lineColor: '#e2e8f0'
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: null
                    },
                    labels: {
                        overflow: 'justify'
                    },
                    gridLineColor: '#f1f5f9'
                },
                tooltip: {
                    valueSuffix: ' KG'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontWeight: 'normal'
                            }
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                series: [{
                    name: 'Quantity',
                    data: []
                }]
            });

            agingChart = Highcharts.chart('chartAging', {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y:,.0f} KG</b> ({point.percentage:.1f}%)'
                },
                plotOptions: {
                    pie: {
                        innerSize: '65%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>',
                            style: {
                                fontWeight: 'normal'
                            }
                        },
                        borderWidth: 2,
                        borderColor: '#fff'
                    }
                },
                series: [{
                    name: 'Stock',
                    colorByPoint: true,
                    data: []
                }]
            });
        }

        function animCounter($el, endVal) {
            $({
                countNum: 0
            }).animate({
                countNum: endVal
            }, {
                duration: 1000,
                easing: 'swing',
                step: function() {
                    $el.text(formatQty(this.countNum));
                },
                complete: function() {
                    $el.text(formatQty(this.countNum));
                }
            });
        }

        function fetchKpi(params) {
            $.ajax({
                url: '/api/dashboard/wrm/inventory/kpi',
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        animCounter($('#kpiTotalStock'), res.data.total_stock || 0);
                        animCounter($('#kpiTotalItem'), res.data.total_item || 0);
                        animCounter($('#kpiActivePallet'), res.data.active_pallet || 0);
                        animCounter($('#kpiInboundToday'), res.data.inbound_today || 0);
                        animCounter($('#kpiDraftOutboundToday'), res.data.draft_outbound_today || 0);
                        animCounter($('#kpiTransferToday'), res.data.transfer_today || 0);
                    }
                }
            });
        }

        function fetchChartMovement(params) {
            if (movementChart) movementChart.showLoading('Fetching data...');
            $.ajax({
                url: '/api/dashboard/wrm/inventory/chart-movement',
                type: 'GET',
                data: params,
                success: function(res) {
                    if (res.status) {
                        movementChart.update({
                            xAxis: {
                                categories: res.data.categories
                            },
                            series: res.data.series
                        }, true, true);
                    }
                },
                complete: function() {
                    if (movementChart) movementChart.hideLoading();
                }
            });
        }

        function fetchChartPie(params) {
            if (pieChart) pieChart.showLoading('Fetching layout...');
            $.ajax({
                url: '/api/dashboard/wrm/inventory/chart-pie',
                type: 'GET',
                data: params,
                success: function(res) {
                    if (res.status) pieChart.series[0].setData(res.data, true);
                },
                complete: function() {
                    if (pieChart) pieChart.hideLoading();
                }
            });
        }

        function fetchChartBar(params) {
            if (barChart) barChart.showLoading('Fetching top materials...');
            $.ajax({
                url: '/api/dashboard/wrm/inventory/chart-bar',
                type: 'GET',
                data: params,
                success: function(res) {
                    if (res.status) {
                        barChart.update({
                            xAxis: {
                                categories: res.data.categories
                            },
                            series: res.data.series
                        }, true, true);
                    }
                },
                complete: function() {
                    if (barChart) barChart.hideLoading();
                }
            });
        }

        function fetchChartAging(params) {
            if (agingChart) agingChart.showLoading('Calculating age...');
            $.ajax({
                url: '/api/dashboard/wrm/inventory/chart-capacity',
                type: 'GET',
                data: params,
                success: function(res) {
                    if (res.status) agingChart.series[0].setData(res.data, true);
                },
                complete: function() {
                    if (agingChart) agingChart.hideLoading();
                }
            });
        }

        function fetchTableRecent(params) {
            const $t = $('#tableRecent');
            $t.html('<tr><td colspan="5" class="text-center py-4 text-muted"><i class="bx bx-loader bx-spin me-2"></i>Loading recent activities...</td></tr>');
            $.ajax({
                url: '/api/dashboard/wrm/inventory/table-recent',
                type: 'GET',
                data: params,
                success: function(res) {
                    if (res.status) {
                        $t.empty();
                        if (res.data.length === 0) {
                            $t.append('<tr><td colspan="5" class="text-center py-4 text-muted">No recent activities on this filter.</td></tr>');
                        } else {
                            let html = '';
                            res.data.forEach(item => {
                                const typeBadge = item.jenis === 'IN' ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
                                html += `
                                    <tr>
                                        <td class="text-muted small">${item.tanggal}</td>
                                        <td>
                                            <span class="badge ${typeBadge} rounded-pill px-2 py-1">${item.jenis}</span>
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 ms-1 small">${item.tipe}</span>
                                        </td>
                                        <td class="fw-medium">${item.barang}</td>
                                        <td class="fw-bold text-end ${item.jenis==='IN'?'text-success':'text-danger'}">${formatQty(item.qty)}</td>
                                        <td class="text-muted small">${item.lokasi}</td>
                                    </tr>
                                `;
                            });
                            $t.html(html);
                        }
                    }
                },
                error: function() {
                    $t.html('<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load activities.</td></tr>');
                }
            });
        }

        function fetchLocationLayout(params) {
            const $container = $('#locationMapContainer');
            $container.html(`<div class="text-center text-muted py-5"><i class="bx bx-loader bx-spin fs-1"></i><p class="mt-2 text-sm">Rendering warehouse visualizer...</p></div>`);

            $.ajax({
                url: '/api/dashboard/wrm/inventory/location-layout',
                type: 'GET',
                data: params,
                success: function(res) {
                    if (!res.status) return;

                    $('#sumTotal').text(res.summary.total);
                    $('#sumOccupied').text(res.summary.occupied);
                    $('#sumReserved').text(res.summary.reserved);
                    $('#sumEmpty').text(res.summary.empty);
                    $('#sumAvailable').text(res.summary.available);

                    if (res.data.length === 0) {
                        $container.html(`<div class="text-center text-muted py-5"><i class="bx bx-cube fs-1 text-light"></i><p class="mt-2 text-sm">No bin visualization data found for these filters.</p></div>`);
                        return;
                    }

                    // Pre-process and categorize data 
                    const byGudang = {};
                    const usedMids = {};

                    res.data.forEach(loc => { // loc is a rack basically from the original API
                        const gKey = loc.plant + ' - ' + loc.gudang;
                        if (!byGudang[gKey]) byGudang[gKey] = {};
                        if (!byGudang[gKey][loc.zona]) byGudang[gKey][loc.zona] = {
                            racks: [],
                            mids: new Set(),
                            totalOccupied: 0
                        };

                        byGudang[gKey][loc.zona].racks.push(loc);

                        loc.cells.forEach(c => {
                            if (c.status === 'occupied') {
                                byGudang[gKey][loc.zona].mids.add(c.mid);
                                byGudang[gKey][loc.zona].totalOccupied++;
                                usedMids[c.mid] = {
                                    name: c.nama_barang,
                                    color: getMidColor(c.mid)
                                };
                            } else if (c.status === 'reserved') {
                                byGudang[gKey][loc.zona].totalOccupied++;
                            }
                        });
                    });

                    // Store global for clicking into modal
                    globalLocationData = byGudang;
                    globalUsedMids = usedMids;

                    // Render Gudang and Zonas
                    let html = '';
                    for (const [gudangKey, zonas] of Object.entries(byGudang)) {
                        html += `<div class="mb-5">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <h6 class="mb-0 fw-bold"><i class="bx bx-buildings text-primary me-2"></i>${gudangKey}</h6>
                                        <div class="flex-grow-1 border-bottom border-dashed border-secondary opacity-25"></div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3">`;

                        // Render each Zona as a box
                        for (const [zonaName, zInfo] of Object.entries(zonas)) {
                            // Determine item colors for stripes
                            let stripesHtml = '';
                            const itemsArr = Array.from(zInfo.mids);

                            if (itemsArr.length > 0) {
                                itemsArr.forEach(mid => {
                                    stripesHtml += `<div class="zona-box-color-stripe" style="background-color: ${usedMids[mid].color};"></div>`;
                                });

                                html += `<div class="zona-box" onclick="openZonaModal('${gudangKey}', '${zonaName}')">
                                            <div class="zona-box-label">ZONA ${zonaName}</div>
                                            <div class="zona-box-colors">
                                                ${stripesHtml}
                                            </div>
                                            <div class="zona-box-stats"><i class="bx bx-package"></i> ${zInfo.totalOccupied}</div>
                                         </div>`;
                            } else {
                                html += `<div class="zona-box" onclick="openZonaModal('${gudangKey}', '${zonaName}')">
                                            <div class="zona-box-label text-muted">ZONA ${zonaName}</div>
                                            <div class="zona-box-empty">
                                                <i class="bx bx-grid-empty" style="font-size:32px;"></i>
                                            </div>
                                         </div>`;
                            }
                        }

                        html += `   </div>
                                 </div>`;
                    }

                    // Render Legend
                    if (Object.keys(usedMids).length > 0) {
                        let legendHtml = '<b class="small text-muted me-2 align-self-center">Item Legend:</b> ';
                        for (const [mid, info] of Object.entries(usedMids)) {
                            legendHtml += `
                                <div class="d-flex align-items-center px-2 py-1 rounded shadow-sm border" style="font-size:11px;">
                                    <div style="width:12px;height:12px;background-color:${info.color};border-radius:3px;margin-right:6px;"></div>
                                    <span class="fw-semibold">${mid}</span><span class="text-muted ms-1 d-none d-sm-inline">- ${info.name}</span>
                                </div>
                            `;
                        }
                        $('#midLegendContainer').html(legendHtml).fadeIn('fast');
                    } else {
                        $('#midLegendContainer').hide();
                    }

                    $container.html(html);
                }
            });
        }

        // --- EXPOSE to Global Context for Inline OnClick ---
        window.openZonaModal = function(gudangKey, zonaName) {
            $('#zonaDetailModalLabel').html(`<i class="bx bx-buildings text-primary me-2"></i> ${gudangKey} <i class="bx bx-chevron-right text-muted mx-1"></i> ZONA <b class=">${zonaName}</b> Details`);

            const zInfo = globalLocationData[gudangKey][zonaName];
            if (!zInfo) return;

            let html = `<div class="row g-3">`;

            zInfo.racks.forEach(loc => { // loc is rack
                const maxKolom = Math.max(...loc.cells.map(c => parseInt(c.kolom) || 0), 0);
                const maxLevel = Math.max(...loc.cells.map(c => parseInt(c.level) || 0), 0);
                const cellMap = {};
                loc.cells.forEach(c => {
                    cellMap[c.kolom + '_' + c.level] = c;
                });

                let gridHtml = `<div class="d-flex flex-column gap-1">`;
                for (let lvl = maxLevel; lvl >= 1; lvl--) {
                    gridHtml += `<div class="d-flex align-items-center gap-1">`;
                    gridHtml += `<span class="rack-row-label" style="width:20px;text-align:right;">L${lvl}</span>`;
                    for (let col = 1; col <= maxKolom; col++) {
                        const c = cellMap[col + '_' + lvl];
                        if (c) {
                            let bg = '',
                                cls = 'rack-cell';
                            if (c.status === 'empty') {
                                cls += ' empty';
                            } else if (c.status === 'reserved') {
                                cls += ' reserved';
                                bg = '#8b5cf6';
                            } else if (c.status === 'occupied') {
                                bg = globalUsedMids[c.mid] ? globalUsedMids[c.mid].color : '#3b82f6';
                            }

                            let tooltip = `<b>Bin ${c.label}</b><br/>Status: ${c.status}`;
                            if (c.status === 'occupied') {
                                tooltip += `<br/>SPB: ${c.no_spb}<br/>Pallet: ${c.pallet_id}<br/>Barang: ${c.mid} - ${c.nama_barang}<br/>Qty: ${c.qty}<br/>In: ${c.incoming_date}`;
                            }

                            gridHtml += `<div class="${cls}" style="${bg ? 'background-color:'+bg+';' : ''}color:${c.status!=='empty'?'#fff':''}">
                                            ${c.label}
                                            <div class="rack-cell-tooltip text-start">${tooltip}</div>
                                         </div>`;
                        } else {
                            gridHtml += `<div style="width:52px;height:52px;border:1px solid transparent;background:transparent;"></div>`;
                        }
                    }
                    gridHtml += `</div>`;
                }

                gridHtml += `<div class="d-flex align-items-center gap-1 mt-1"><span style="width:20px;"></span>`;
                for (let col = 1; col <= maxKolom; col++) {
                    gridHtml += `<div style="width:52px;text-align:center;font-size:10px;font-weight:600;color:#94a3b8;">C${col}</div>`;
                }
                gridHtml += `</div></div>`; // End row grid

                html += `<div class="col-auto">
                            <div class="rack-section shadow-sm text-center">
                                <div class="rack-section-header">
                                    <span class="rack-zone-badge">Z: ${loc.zona}</span>
                                    <span class="rack-bin-badge">Rack: ${loc.bin}</span>
                                </div>
                                ${gridHtml}
                            </div>
                         </div>`;
            });

            html += `</div>`;

            // Status legend
            const legendStatus = `
                <div class="d-flex gap-3 flex-wrap align-items-center mt-3 pt-3 border-top">
                    <span class="text-muted small fw-semibold">Status:</span>
                    <div class="d-flex align-items-center gap-1">
                        <div style="width:14px;height:14px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:4px;"></div>
                        <span style="font-size:11px;">Empty</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <div style="width:14px;height:14px;background:#8b5cf6;border-radius:4px;"></div>
                        <span style="font-size:11px;">Reserved</span>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <div style="width:14px;height:14px;background:#3b82f6;border-radius:4px;"></div>
                        <span style="font-size:11px;">Occupied (warna per item)</span>
                    </div>
                </div>`;

            $('#zonaDetailContainer').html(legendStatus + html);
            var myModal = new bootstrap.Modal(document.getElementById('zonaDetailModal'));
            myModal.show();
        };

        // --- Core ---
        function fetchAllData() {
            const params = {
                start_date: $('#filterStartDate').val(),
                end_date: $('#filterEndDate').val(),
                gudang: $('#filterGudang').val(),
                supplier: $('#filterSupplier').val()
            };

            fetchKpi(params);
            fetchChartMovement(params);
            fetchChartPie(params);
            fetchChartBar(params);
            fetchChartAging(params);
            fetchTableRecent(params);
            fetchLocationLayout(params);
        }

        $('#btnFilter').on('click', function(e) {
            e.preventDefault();
            fetchAllData();
        });

        initCharts();
        fetchAllData(); // Initial load
    });
</script>
@endsection