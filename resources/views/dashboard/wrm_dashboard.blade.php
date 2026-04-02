@extends('layouts.app')

@section('title', ' | WRM Inventory Dashboard')

@section('styles')
<style>
    .dashboard-kpi-card {
        border-radius: 12px;
        border: none;
        transition: transform 0.3s;
    }

    .dashboard-kpi-card:hover {
        transform: translateY(-5px);
    }

    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }

    /* === Warehouse Location Map Styles === */
    .rack-section {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .rack-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 2px dashed #e2e8f0;
    }

    .rack-zone-badge {
        background: #1e293b;
        color: #fff;
        font-size: 11px;
        border-radius: 6px;
        padding: 3px 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .rack-bin-badge {
        background: #3b82f6;
        color: #fff;
        font-size: 11px;
        border-radius: 6px;
        padding: 3px 10px;
        font-weight: 600;
    }

    .rack-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
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
        transform: scale(1.12);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }

    .rack-cell.empty {
        background: #f1f5f9;
        color: #94a3b8;
        border-color: #cbd5e1;
        border-style: dashed;
    }

    .rack-cell-tooltip {
        display: none;
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        background: #0f172a;
        color: #f1f5f9;
        font-size: 11px;
        padding: 6px 10px;
        border-radius: 6px;
        white-space: nowrap;
        z-index: 100;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        pointer-events: none;
    }

    .rack-cell-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #0f172a;
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
        <div class="row mb-4 align-items-end">
            <div class="col-md-4">
                <h4 class="mb-0 fw-bold"><i class="bx bx-bar-chart-alt-2"></i> WRM Dashboard</h4>
                <p class="text-muted mb-0">Analytics & Stock Overview</p>
            </div>
            <div class="col-md-8 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end flex-wrap">
                <div>
                    <input type="date" id="filterStartDate" class="form-control form-control-sm"
                        placeholder="Start Date">
                </div>
                <div>
                    <input type="date" id="filterEndDate" class="form-control form-control-sm"
                        placeholder="End Date">
                </div>
                <div>
                    <select id="filterZona" class="form-select form-select-sm">
                        <option value="">All Zones</option>
                        @foreach ($locations as $loc)
                        @if ($loc->zona)
                        <option value="{{ $loc->zona }}">{{ $loc->zona }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <button id="btnFilter" class="btn btn-primary btn-sm"><i class="bx bx-filter"></i> Apply</button>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card dashboard-kpi-card shadow-sm"
                    style="background: linear-gradient(135deg, #e3f2fd, #bbdefb);">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-semibold mb-2">Total Stock (KG)</h6>
                        <h3 class="fw-bold mb-0 text-primary" id="kpiTotalStock">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card dashboard-kpi-card shadow-sm"
                    style="background: linear-gradient(135deg, #f3e5f5, #e1bee7);">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-semibold mb-2">Total Items</h6>
                        <h3 class="fw-bold mb-0 text-purple" style="color: #6a1b9a;" id="kpiTotalItem">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card dashboard-kpi-card shadow-sm"
                    style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9);">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-semibold mb-2">Inbound Today (KG)</h6>
                        <h3 class="fw-bold mb-0 text-success" id="kpiInboundToday">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="card dashboard-kpi-card shadow-sm"
                    style="background: linear-gradient(135deg, #ffebee, #ffcdd2);">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-semibold mb-2">Outbound Today (KG)</h6>
                        <h3 class="fw-bold mb-0 text-danger" id="kpiOutboundToday">0</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row 1 --}}
        <div class="row mb-4">
            <div class="col-xl-8 mb-4 mb-xl-0">
                <div class="card shadow-sm rounded-4 border-0 h-100">
                    <div class="card-body">
                        <div id="chartMovement" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card shadow-sm rounded-4 border-0 h-100">
                    <div class="card-body">
                        <div id="chartPie" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row 2 --}}
        <div class="row mb-4">
            <!-- Left col for Bar Chart -->
            <div class="col-xl-8">
                <div class="card shadow-sm rounded-4 border-0 h-100">
                    <div class="card-body">
                        <div id="chartBar" style="height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Right col for Donut (Space Utilization) -->
            <div class="col-xl-4">
                <div class="card shadow-sm rounded-4 border-0 h-100">
                    <div class="card-body">
                        <div id="chartCapacity" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm rounded-4 border-0 h-100">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0 text-danger"><i class="bx bx-time"></i> Expiring Within 30 Days</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>No SPB</th>
                                        <th>Qty</th>
                                        <th>Location</th>
                                        <th>Expires On</th>
                                    </tr>
                                </thead>
                                <tbody id="tableExpiring">
                                    <tr>
                                        <td colspan="5" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 mb-4">
                <div class="card shadow-sm rounded-4 border-0 h-100">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0"><i class="bx bx-list-ol"></i> Recent Activities</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Item Name</th>
                                        <th>Qty</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody id="tableRecent">
                                    <tr>
                                        <td colspan="5" class="text-center">Loading...</td>
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
                <div class="card shadow-sm rounded-4 border-0">
                    <div
                        class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title mb-0"><i class="bx bx-grid-alt me-1"></i> Warehouse Location Map
                            </h5>
                            <small class="text-muted">Visualisasi tata letak bin gudang berdasarkan stok aktif</small>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Summary Chips --}}
                        <div class="d-flex gap-3 mb-4 flex-wrap" id="locationSummary">
                            <div class="px-3 py-2 rounded-3 text-white" style="background:#0f172a;font-size:13px;">
                                Total Bins: <b id="sumTotal">-</b>
                            </div>
                            <div class="px-3 py-2 rounded-3 text-white" style="background:#16a34a;font-size:13px;">
                                Occupied: <b id="sumOccupied">-</b>
                            </div>
                            <div class="px-3 py-2 rounded-3 text-white" style="background:#d97706;font-size:13px;">
                                Reserved: <b id="sumReserved">-</b>
                            </div>
                            <div class="px-3 py-2 rounded-3" style="background:#e2e8f0;color:#475569;font-size:13px;">
                                Empty: <b id="sumEmpty">-</b>
                            </div>
                        </div>

                        {{-- Dynamic MID Legend --}}
                        <div id="midLegendContainer" class="d-flex flex-wrap gap-2 mb-4 p-3 rounded-3 bg-light" style="border:1px dashed #cbd5e1; display:none;">
                            <!-- populated by JS -->
                        </div>

                        {{-- Grid Container --}}
                        <div id="locationMapContainer">
                            <div class="text-center text-muted py-5">
                                <i class="bx bx-loader bx-spin" style="font-size:32px;"></i>
                                <p class="mt-2 mb-0">Memuat peta gudang...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
{{-- Highcharts Library --}}
<!-- <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script> -->

<script>
    $(document).ready(function() {
        // Color palette for MIDs
        const palette = [
            '#e6194b', '#3cb44b', '#e5b217', '#4363d8', '#f58231',
            '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe',
            '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000',
            '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
        ];
        let midColorMap = {};
        let colorIndex = 0;

        function getMidColor(mid) {
            if (!mid) return '#16a34a'; // default green
            if (midColorMap[mid]) return midColorMap[mid];

            const color = palette[colorIndex % palette.length];
            midColorMap[mid] = color;
            colorIndex++;
            return color;
        }

        // Default dates
        const today = new Date();
        const start30 = new Date();
        start30.setDate(today.getDate() - 30);

        $('#filterStartDate').val(start30.toISOString().split('T')[0]);
        $('#filterEndDate').val(today.toISOString().split('T')[0]);

        let movementChart, pieChart, barChart, capacityChart;

        function initCharts() {
            movementChart = Highcharts.chart('chartMovement', {
                chart: {
                    type: 'column',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: 'Inbound vs Outbound Movement'
                },
                xAxis: {
                    categories: [],
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Quantity (KG)'
                    }
                },
                tooltip: {
                    shared: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
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
                    text: 'Stock Distribution by Zone'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y} KG</b> ({point.percentage:.1f}%)'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                        }
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
                    text: 'Top 10 Fast Moving Items'
                },
                xAxis: {
                    categories: [],
                    title: {
                        text: null
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Quantity (KG)',
                        align: 'high'
                    },
                    labels: {
                        overflow: 'justify'
                    }
                },
                tooltip: {
                    valueSuffix: ' KG'
                },
                plotOptions: {
                    bar: {
                        dataLabels: {
                            enabled: true
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                series: [{
                    name: 'Outbound',
                    data: []
                }]
            });

            capacityChart = Highcharts.chart('chartCapacity', {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: 'Space Utilization'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b> ({point.y} Bins)'
                },
                plotOptions: {
                    pie: {
                        innerSize: '60%',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                        }
                    }
                },
                series: [{
                    name: 'Bins',
                    colorByPoint: true,
                    data: []
                }]
            });
        }

        function fetchKpi(params) {
            $.ajax({
                url: "{{ route('dashboard.wrm.data.kpi') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        animCounter($('#kpiTotalStock'), res.data.total_stock || 0);
                        animCounter($('#kpiTotalItem'), res.data.total_item || 0);
                        animCounter($('#kpiInboundToday'), res.data.inbound_today || 0);
                        animCounter($('#kpiOutboundToday'), res.data.outbound_today || 0);
                    }
                }
            });
        }

        function fetchChartMovement(params) {
            if (movementChart) movementChart.showLoading('Loading...');
            $.ajax({
                url: "{{ route('dashboard.wrm.data.chart-movement') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
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
            if (pieChart) pieChart.showLoading('Loading...');
            $.ajax({
                url: "{{ route('dashboard.wrm.data.chart-pie') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        pieChart.series[0].setData(res.data, true);
                    }
                },
                complete: function() {
                    if (pieChart) pieChart.hideLoading();
                }
            });
        }

        function fetchChartBar(params) {
            if (barChart) barChart.showLoading('Loading...');
            $.ajax({
                url: "{{ route('dashboard.wrm.data.chart-bar') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
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

        function fetchChartCapacity(params) {
            if (capacityChart) capacityChart.showLoading('Loading...');
            $.ajax({
                url: "{{ route('dashboard.wrm.data.chart-capacity') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        capacityChart.series[0].setData(res.data, true);
                    }
                },
                complete: function() {
                    if (capacityChart) capacityChart.hideLoading();
                }
            });
        }

        function fetchTableExpiring(params) {
            const $t = $('#tableExpiring');
            $t.html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
            $.ajax({
                url: "{{ route('dashboard.wrm.data.table-expiring') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        $t.empty();
                        if (res.data.length === 0) {
                            $t.append(
                                '<tr><td colspan="5" class="text-center text-muted">No items expiring soon.</td></tr>'
                            );
                        } else {
                            let html = '';
                            res.data.forEach(item => {
                                html += `
                                    <tr>
                                        <td class="fw-bold">${item.barang}</td>
                                        <td>${item.no_spb}</td>
                                        <td>${Number(item.qty).toLocaleString('id-ID')}</td>
                                        <td><span class="badge bg-info">${item.lokasi}</span></td>
                                        <td>
                                            ${item.expired_date} 
                                            <small class="text-danger d-block">(${item.days_left} days left)</small>
                                        </td>
                                    </tr>
                                `;
                            });
                            $t.html(html);
                        }
                    }
                }
            });
        }

        function fetchTableRecent(params) {
            const $t = $('#tableRecent');
            $t.html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
            $.ajax({
                url: "{{ route('dashboard.wrm.data.table-recent') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        $t.empty();
                        if (res.data.length === 0) {
                            $t.append(
                                '<tr><td colspan="5" class="text-center text-muted">No recent activities.</td></tr>'
                            );
                        } else {
                            let html = '';
                            res.data.forEach(item => {
                                const typeBadge = item.jenis === 'IN' ? 'bg-success' :
                                    'bg-danger';
                                html += `
                                    <tr>
                                        <td>${item.tanggal}</td>
                                        <td><span class="badge ${typeBadge}">${item.jenis}</span></td>
                                        <td>${item.barang}</td>
                                        <td class="fw-bold">${Number(item.qty).toLocaleString('id-ID')}</td>
                                        <td>${item.lokasi}</td>
                                    </tr>
                                `;
                            });
                            $t.html(html);
                        }
                    }
                }
            });
        }

        function fetchAllData() {
            const params = {
                start_date: $('#filterStartDate').val(),
                end_date: $('#filterEndDate').val(),
                zona: $('#filterZona').val()
            };

            fetchKpi(params);
            fetchChartMovement(params);
            fetchChartPie(params);
            fetchChartBar(params);
            fetchChartCapacity(params);
            fetchTableExpiring(params);
            fetchTableRecent(params);
            fetchLocationLayout(params);
        }

        function fetchLocationLayout(params) {
            const $container = $('#locationMapContainer');
            $container.html(`
                    <div class="text-center text-muted py-5">
                        <i class="bx bx-loader bx-spin" style="font-size:32px;"></i>
                        <p class="mt-2 mb-0">Memuat peta gudang...</p>
                    </div>
                `);

            $.ajax({
                url: "{{ route('dashboard.wrm.data.location-layout') }}",
                type: 'GET',
                data: params,
                dataType: 'json',
                success: function(res) {
                    if (!res.status) return;

                    // Update summary chips
                    $('#sumTotal').text(res.summary.total);
                    $('#sumOccupied').text(res.summary.occupied);
                    $('#sumReserved').text(res.summary.reserved);
                    $('#sumEmpty').text(res.summary.empty);

                    if (res.data.length === 0) {
                        $container.html(
                            `<div class="text-center text-muted py-4"><i class="bx bx-data" style="font-size:32px;"></i><p class="mt-2">Belum ada data lokasi bin.</p></div>`
                        );
                        return;
                    }

                    // Group locations by gudang and keep track of used MIDs
                    const byGudang = {};
                    const usedMids = {};
                    res.data.forEach(loc => {
                        const key = loc.plant + ' - ' + loc.gudang;
                        if (!byGudang[key]) byGudang[key] = [];
                        byGudang[key].push(loc);
                    });

                    let html = '';
                    for (const [gudangKey, locs] of Object.entries(byGudang)) {
                        html += `<div class="mb-4">`;
                        html += `<div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bx bx-buildings" style="font-size:20px;color:#3b82f6;"></i>
                            <h6 class="mb-0 fw-bold">${gudangKey}</h6>
                            <div style="flex:1;height:2px;background:linear-gradient(to right,#3b82f6,#e2e8f0);border-radius:2px;"></div>
                        </div>`;
                        html += `<div class="row g-3">`;

                        locs.forEach(loc => {
                            // Find max kolom and level for this rack
                            const maxKolom = Math.max(...loc.cells.map(c => parseInt(c
                                .kolom) || 0), 0);
                            const maxLevel = Math.max(...loc.cells.map(c => parseInt(c
                                .level) || 0), 0);

                            // Build a cell map for easy lookup
                            const cellMap = {};
                            loc.cells.forEach(c => {
                                cellMap[c.kolom + '_' + c.level] = c;
                            });

                            // Build grid HTML: rows = levels (top to bottom), cols = kolom
                            let gridHtml =
                                `<div style="display:flex;flex-direction:column;gap:5px;">`;

                            for (let lvl = maxLevel; lvl >= 1; lvl--) {
                                gridHtml +=
                                    `<div style="display:flex;align-items:center;gap:5px;">`;
                                gridHtml +=
                                    `<span style="width:26px;font-size:10px;font-weight:700;text-align:right;flex-shrink:0;">L${lvl}</span>`;

                                for (let kol = 1; kol <= maxKolom; kol++) {
                                    const cell = cellMap[kol + '_' + lvl];
                                    if (cell) {
                                        const statusLabel = cell.status === 'occupied' ?
                                            '🟢 Berisi Stok' : (cell.status === 'reserved' ?
                                                '🟡 Reserved' : '⬜ Kosong');

                                        let styleAttr = '';
                                        let tooltipExtras = '';
                                        if (cell.status === 'occupied') {
                                            const bgColor = getMidColor(cell.mid);
                                            usedMids[cell.mid] = {
                                                nama: cell.nama_barang,
                                                color: bgColor
                                            };
                                            styleAttr = `style="background: ${bgColor}; border-color: ${bgColor};"`;
                                            tooltipExtras = `<br>Barang: ${cell.nama_barang || '-'}<br>MID: ${cell.mid || '-'}<br>Qty: ${Number(cell.qty || 0).toLocaleString('id-ID')}<br>Pallet ID: ${cell.pallet_id || '-'}`;
                                        }

                                        gridHtml += `
                                        <div class="rack-cell ${cell.status}" title="" ${styleAttr}>
                                            ${cell.label}
                                            <div class="rack-cell-tooltip">${statusLabel}<br>Bin: ${loc.bin}<br>Kolom ${cell.kolom}, Lvl ${cell.level}${tooltipExtras}</div>
                                        </div>`;
                                    } else {
                                        gridHtml +=
                                            `<div style="width:52px;height:52px;"></div>`;
                                    }
                                }

                                gridHtml += `</div>`; // end row
                            }

                            // Kolom labels at the bottom
                            gridHtml +=
                                `<div style="display:flex;align-items:center;gap:5px;margin-top:4px;">`;
                            gridHtml += `<span style="width:26px;"></span>`;
                            for (let kol = 1; kol <= maxKolom; kol++) {
                                gridHtml +=
                                    `<div style="width:52px;text-align:center;font-size:10px;font-weight:600;">K${kol}</div>`;
                            }
                            gridHtml += `</div>`;

                            gridHtml += `</div>`; // end grid

                            html += `
                            <div class="col-auto">
                                <div class="rack-section bg-light">
                                    <div class="rack-section-header">
                                        <span class="rack-zone-badge">${loc.zona}</span>
                                        <span class="rack-bin-badge">Bin: ${loc.bin}</span>
                                        <span style="font-size:11px;">${loc.cells.length} posisi</span>
                                    </div>
                                    ${gridHtml}
                                </div>
                            </div>`;
                        });

                        html += `</div></div>`; // end row + gudang section
                    }

                    // Render Dynamic Legend
                    if (Object.keys(usedMids).length > 0) {
                        let legendHtml = `<div class="fw-bold mb-1 w-100" style="font-size:12px;"><i class="bx bx-palette"></i> Panduan Warna MID:</div>`;
                        for (const [m, info] of Object.entries(usedMids)) {
                            legendHtml += `
                                <div class="d-flex align-items-center gap-1" style="font-size:11px; padding:2px 6px; border:1px solid #e2e8f0; border-radius:4px;">
                                    <span class="d-inline-block rounded-1" style="width:12px;height:12px;background:${info.color};"></span>
                                    <span class="text-truncate" style="max-width: 150px;" title="${m} - ${info.nama}">${m} (${info.nama})</span>
                                </div>
                            `;
                        }
                        $('#midLegendContainer').html(legendHtml).show();
                    } else {
                        $('#midLegendContainer').hide();
                    }

                    $container.html(html);
                }
            });
        }

        function animCounter($el, max) {
            const duration = 1000;
            const start = performance.now();
            const num = parseFloat(max) || 0;

            requestAnimationFrame(function animate(time) {
                const elapsed = time - start;
                const progress = Math.min(elapsed / duration, 1);
                const current = progress * num;
                $el.text(Math.floor(current).toLocaleString('id-ID'));
                if (progress < 1) requestAnimationFrame(animate);
            });
        }

        // Init actions
        initCharts();
        fetchAllData();

        $('#btnFilter').on('click', fetchAllData);
    });
</script>
@endsection