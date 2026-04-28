@extends('layouts.app')

@section('title', ' | Ikat Terpal Dashboard')

@section('sidebar-size', 'sm')

@section('styles')
<style>
    .card-animate {
        transition: all 0.3s ease-in-out;
    }

    .card-animate:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .bg-glass {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .trend-up {
        color: #10b981;
    }

    .trend-down {
        color: #ef4444;
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold">Dashboard TKBM Ikat Terpal</h4>
                    <p class="text-muted mb-0">Statistik Operasional Pengikatan Terpal & Pallet</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('tkbm.ikat-terpal.index') }}" class="btn btn-soft-primary">
                        <i class="bx bx-plus me-1"></i> Input Data
                    </a>
                    <a href="{{ route('tkbm.ikat-terpal.report') }}" class="btn btn-soft-info">
                        <i class="bx bx-file me-1"></i> Lihat Report
                    </a>
                    <input type="month" id="filter-month" class="form-control" value="{{ now()->format('Y-m') }}" style="width: 150px;">
                    <button class="btn btn-primary" id="btn-refresh">
                        <i class="bx bx-refresh me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Widgets -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-glass text-white me-3">
                                <i class="bx bx-box fs-3"></i>
                            </div>
                            <h6 class="text-white mb-0 opacity-75">Total Pallets</h6>
                        </div>
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h3 class="text-white fw-bold mb-1" id="stat-total-pallets">0</h3>
                                <span class="badge bg-glass text-white" id="stat-pallets-trend">
                                    <i class="bx bx-trending-up me-1"></i> 0%
                                </span>
                            </div>
                            <p class="text-white-50 small mb-0">Bulan Ini</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-animate shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body" style="background: linear-gradient(135deg, #10b981, #34d399);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-glass text-white me-3">
                                <i class="bx bx-group fs-3"></i>
                            </div>
                            <h6 class="text-white mb-0 opacity-75">Total Buruh</h6>
                        </div>
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h3 class="text-white fw-bold mb-1" id="stat-total-buruh">0</h3>
                                <span class="text-white-50 small">Orang Terlibat</span>
                            </div>
                            <p class="text-white-50 small mb-0">Bulan Ini</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-animate shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-glass text-white me-3">
                                <i class="bx bx-wallet fs-3"></i>
                            </div>
                            <h6 class="text-white mb-0 opacity-75">Total Fee</h6>
                        </div>
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h3 class="text-white fw-bold mb-1" id="stat-total-fee">0</h3>
                                <span class="text-white-50 small">Belum Pajak</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card card-animate shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body" style="background: linear-gradient(135deg, #ef4444, #f87171);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-glass text-white me-3">
                                <i class="bx bx-money fs-3"></i>
                            </div>
                            <h6 class="text-white mb-0 opacity-75">Grand Total</h6>
                        </div>
                        <div class="d-flex align-items-end justify-content-between">
                            <div>
                                <h3 class="text-white fw-bold mb-1" id="stat-grand-total">0</h3>
                                <span class="text-white-50 small">Final Amount</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <!-- Daily Trend -->
            <div class="col-xl-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="card-title mb-0 fw-bold">Tren Harian (Pallet & Buruh)</h5>
                        <p class="text-muted small mb-0">Produktivitas harian di bulan ini</p>
                    </div>
                    <div class="card-body">
                        <div id="daily-trend-chart" style="min-height: 375px;"></div>
                    </div>
                </div>
            </div>
            <!-- Mini Stats -->
            <div class="col-xl-4">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-bold small mb-3">Summary Financial</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal Barang</span>
                            <span class="fw-bold text-primary" id="val-subtotal">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>PPN</span>
                            <span class="fw-bold" id="val-ppn">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>PPh</span>
                            <span class="fw-bold text-danger" id="val-pph">0</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span>Avg Pallet/Hari</span>
                            <span class="fw-bold text-info" id="val-avg-daily">0</span>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="card-title mb-0 fw-bold">Entri Terakhir</h6>
                    </div>
                    <div class="card-body px-0 pt-0">
                        <div class="table-responsive" style="max-height: 200px;">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                    <tr class="small text-uppercase">
                                        <th class="ps-4">Data / Petugas</th>
                                        <th class="text-center">Subtotal</th>
                                        <th class="text-end pe-4">Fee</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-entries-list">
                                    <!-- Data via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="row mt-4">
            <div class="col-xl-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="card-title mb-0 fw-bold">Perbandingan Bulanan (Total Pallet)</h5>
                    </div>
                    <div class="card-body">
                        <div id="monthly-trend-chart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="card-title mb-0 fw-bold">Perbandingan Bulanan (Grand Total)</h5>
                    </div>
                    <div class="card-body">
                        <div id="monthly-grand-total-chart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let dailyChart = null;
        let monthlyChart = null;
        let monthlyGrandTotalChart = null;

        function loadData() {
            const monthVal = $('#filter-month').val();
            const [year, month] = monthVal.split('-');

            $.ajax({
                url: "/api/dashboard/tkbm/ikat-terpal/get-stats",
                data: {
                    year,
                    month
                },
                success: function(res) {
                    if (res.status === 'success') {
                        updateWidgets(res.stats);
                        renderDailyChart(res.charts.daily);
                        renderMonthlyChart(res.charts.monthly);
                        renderMonthlyGrandTotalChart(res.charts.monthly);
                        renderRecentEntries(res.recent_entries);
                    }
                }
            });
        }

        function updateWidgets(stats) {
            $('#stat-total-pallets').text(stats.total_pallets.value);
            $('#stat-total-buruh').text(stats.total_buruh);
            $('#stat-total-fee').text('Rp ' + stats.total_fee);
            $('#stat-grand-total').text('Rp ' + stats.grand_total);

            $('#val-subtotal').text('Rp ' + stats.total_subtotal);
            $('#val-ppn').text('Rp ' + stats.ppn);
            $('#val-pph').text('Rp ' + stats.pph);
            $('#val-avg-daily').text(stats.avg_daily);

            const trendHtml = stats.total_pallets.trend === 'up' ?
                `<i class="bx bx-trending-up me-1"></i> ${stats.total_pallets.percent}%` :
                `<i class="bx bx-trending-down me-1"></i> ${stats.total_pallets.percent}%`;

            $('#stat-pallets-trend').html(trendHtml);
        }

        function renderDailyChart(data) {
            dailyChart = Highcharts.chart('daily-trend-chart', {
                chart: {
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: data.map(i => i.date.split('-')[2]),
                    title: {
                        text: 'Tanggal'
                    },
                    gridLineWidth: 0
                },
                yAxis: [{ // Primary yAxis
                    title: {
                        text: 'Jumlah Pallet'
                    },
                    gridLineColor: 'rgba(0,0,0,0.05)'
                }, { // Secondary yAxis
                    title: {
                        text: 'Jumlah Buruh'
                    },
                    opposite: true,
                    gridLineWidth: 0
                }],
                tooltip: {
                    shared: true,
                    headerFormat: '<b>Tanggal {point.key}</b><br/>'
                },
                credits: {
                    enabled: false
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.1,
                        marker: {
                            enabled: false,
                            states: {
                                hover: {
                                    enabled: true
                                }
                            }
                        }
                    },
                    line: {
                        marker: {
                            enabled: true,
                            radius: 3
                        }
                    }
                },
                series: [{
                    name: 'Pallet',
                    type: 'areaspline',
                    data: data.map(i => parseFloat(i.total_pallet)),
                    color: '#6366f1',
                    tooltip: {
                        valueSuffix: ' Pallet'
                    }
                }, {
                    name: 'Buruh',
                    type: 'line',
                    yAxis: 1,
                    data: data.map(i => parseFloat(i.total_buruh)),
                    color: '#ef4444',
                    dashStyle: 'ShortDot',
                    tooltip: {
                        valueSuffix: ' Orang'
                    }
                }]
            });
        }

        function renderMonthlyChart(data) {
            monthlyChart = Highcharts.chart('monthly-trend-chart', {
                chart: {
                    type: 'column',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: data.map(i => i.month_name),
                    gridLineWidth: 0
                },
                yAxis: {
                    title: {
                        text: 'Total Pallet'
                    },
                    gridLineColor: 'rgba(0,0,0,0.05)'
                },
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                plotOptions: {
                    column: {
                        borderRadius: 5,
                        color: '#6366f1',
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Total Pallet',
                    data: data.map(i => parseFloat(i.total_pallet))
                }]
            });
        }

        function renderMonthlyGrandTotalChart(data) {
            monthlyGrandTotalChart = Highcharts.chart('monthly-grand-total-chart', {
                chart: {
                    type: 'column',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: data.map(i => i.month_name),
                    gridLineWidth: 0
                },
                yAxis: {
                    title: {
                        text: 'Grand Total (Rp)'
                    },
                    gridLineColor: 'rgba(0,0,0,0.05)',
                    labels: {
                        formatter: function() {
                            return 'Rp ' + Highcharts.numberFormat(this.value, 0, ',', '.');
                        }
                    }
                },
                tooltip: {
                    formatter: function() {
                        return '<b>' + this.x + '</b><br/>' +
                            'Grand Total: Rp ' + Highcharts.numberFormat(this.y, 0, ',', '.');
                    }
                },
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                plotOptions: {
                    column: {
                        borderRadius: 5,
                        color: '#f59e0b',
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Grand Total',
                    data: data.map(i => parseFloat(i.grand_total))
                }]
            });
        }

        function renderRecentEntries(entries) {
            let html = '';
            entries.forEach(item => {
                const formattedQty = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(item.subtotal_barang);

                const formattedFee = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(item.total_fee);

                html += `
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold small">${item.tanggal}</div>
                            <div class="text-muted" style="font-size: 11px;">${item.user ? item.user.nama_lengkap : 'System'}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-soft-info text-info rounded-pill px-2">${formattedQty}</span>
                        </td>
                        <td class="text-end pe-4">
                            <span class="text-success fw-bold small">${formattedFee}</span>
                        </td>
                    </tr>
                `;
            });
            if (entries.length === 0) html = '<tr><td colspan="3" class="text-center py-4 text-muted">No data available</td></tr>';
            $('#recent-entries-list').html(html);
        }

        $('#btn-refresh').on('click', loadData);
        $('#filter-month').on('change', loadData);

        loadData();
    });
</script>
@endsection