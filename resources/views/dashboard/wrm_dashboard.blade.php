@extends('layouts.app')

@section('styles')
    <style>
        .my-tooltip {
            min-width: 200px;
            padding: 12px 16px;
            font-size: 13px;
            line-height: 1.6;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .my-tooltip b {
            color: #333;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Filter Periode --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="fw-bold text-dark">Filter Periode:</span>
                                <select id="filterPeriode" class="form-select form-select-sm" style="width: auto;">
                                    <option value="harian">Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                    <option value="bulanan" selected>Bulanan</option>
                                    <option value="tahunan">Tahunan</option>
                                </select>
                                <input type="date" id="filterTanggalMulai" class="form-control form-control-sm"
                                    style="width: auto;">
                                <span class="text-muted">s/d</span>
                                <input type="date" id="filterTanggalAkhir" class="form-control form-control-sm"
                                    style="width: auto;">
                                <button class="btn btn-primary btn-sm px-4" id="btnApplyFilter">
                                    <i class="bx bx-filter-alt me-1"></i> Terapkan
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="btnResetFilter">
                                    <i class="bx bx-reset me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Widget --}}
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body" style="background: linear-gradient(135deg, #a8e6cf, #dcedc1, #e6f4ea);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Total Inbound</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: rgba(238, 254, 181, 0.6);">
                                        <i class="bx bx-package text-success fs-1"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2">
                                        <span class="counter-value text-success" id="widgetTotalQty">0</span>
                                        <span class="fs-5 ms-2 text-success">KG</span>
                                    </h4>
                                    <p class="small mb-0 opacity-75">
                                        <i class="bx bx-calendar me-1"></i> Total Qty Masuk
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up" data-aos-delay="200">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body" style="background: linear-gradient(135deg, #dbe8ff, #ffffff, #e6ebf4);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Total Pallet</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: #e6ebf4;">
                                        <i class="bx bx-layer text-info fs-1"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2 text-dark">
                                        <span class="counter-value text-info" id="widgetTotalPallet">0</span>
                                        <span class="text-info fs-5 ms-2">Pallet</span>
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        <i class="bx bx-calendar me-1"></i> Total Pallet Masuk
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up" data-aos-delay="400">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body" style="background: linear-gradient(135deg, #fff4db, #ffffff, #f4f2e6);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Total Batch</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: #f4f2e6;">
                                        <i class="bx bx-receipt text-warning fs-1"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2 text-dark">
                                        <span class="counter-value text-warning" id="widgetTotalBatch">0</span>
                                        <span class="text-warning fs-5 ms-2">Batch</span>
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        <i class="bx bx-calendar me-1"></i> Total SPB Masuk
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up" data-aos-delay="600">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body" style="background: linear-gradient(135deg, #fde8e8, #ffffff, #f4e6e6);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Perlu Perhatian</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: #f4e6e6;">
                                        <i class="bx bx-error text-danger fs-1"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2 text-dark">
                                        <span class="counter-value text-danger" id="widgetTotalAlert">0</span>
                                        <span class="text-danger fs-5 ms-2">Item</span>
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        <i class="bx bx-calendar me-1"></i> QI + LELEH
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Inbound Per Bulan --}}
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div data-aos="fade-up" data-aos-delay="800">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Grafik Inbound Per Bulan <span id="labelInboundBulanan"
                                        class="text-muted fs-6"></span></h4>
                            </div>
                            <div class="card-body">
                                <div id="chartInboundBulanan" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Total Stok Per Barang + Distribusi Status --}}
            <div class="row mb-4">
                <div class="col-xl-7">
                    <div data-aos="fade-up" data-aos-delay="1000">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Total Stok Per Barang</h4>
                            </div>
                            <div class="card-body">
                                <div id="chartStokPerBarang" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div data-aos="fade-up" data-aos-delay="1200">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Distribusi Status Stok</h4>
                            </div>
                            <div class="card-body">
                                <div id="chartDistribusiStatus" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Stok Per Gudang --}}
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div data-aos="fade-up" data-aos-delay="1400">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Stok Per Gudang & Lokasi</h4>
                            </div>
                            <div class="card-body">
                                <div id="chartStokPerGudang" class="apex-charts" dir="ltr"></div>
                            </div>
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

            let periode = 'bulanan';
            let tglMulai = null;
            let tglAkhir = null;

            // Set default tanggal
            let today = new Date();
            let firstDay = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            let lastDay = today.toISOString().split('T')[0];
            $('#filterTanggalMulai').val(firstDay);
            $('#filterTanggalAkhir').val(lastDay);

            // Init semua
            loadWidget();
            chartInboundBulanan();
            chartStokPerBarang();
            chartDistribusiStatus();
            chartStokPerGudang();

            // Apply filter
            $('#btnApplyFilter').click(function() {
                periode = $('#filterPeriode').val();
                tglMulai = $('#filterTanggalMulai').val();
                tglAkhir = $('#filterTanggalAkhir').val();

                if (!tglMulai || !tglAkhir) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih rentang tanggal terlebih dahulu.'
                    });
                    return;
                }

                loadWidget();
                chartInboundBulanan();
                chartStokPerBarang();
                chartDistribusiStatus();
                chartStokPerGudang();
            });

            // Reset filter
            $('#btnResetFilter').click(function() {
                periode = 'bulanan';
                tglMulai = null;
                tglAkhir = null;
                $('#filterPeriode').val('bulanan');
                $('#filterTanggalMulai').val(firstDay);
                $('#filterTanggalAkhir').val(lastDay);

                loadWidget();
                chartInboundBulanan();
                chartStokPerBarang();
                chartDistribusiStatus();
                chartStokPerGudang();
            });

            // -------------------------------------------------------
            // WIDGET
            // -------------------------------------------------------
            function loadWidget() {
                $.ajax({
                    url: "{{ url('api/dashboard/wrm/inbound/widget') }}",
                    type: 'GET',
                    data: {
                        periode,
                        tgl_mulai: tglMulai,
                        tgl_akhir: tglAkhir
                    },
                    dataType: 'json',
                    success: function(res) {
                        animateCounter($('#widgetTotalQty'), Number(res.total_qty) || 0);
                        animateCounter($('#widgetTotalPallet'), Number(res.total_pallet) || 0);
                        animateCounter($('#widgetTotalBatch'), Number(res.total_batch) || 0);
                        animateCounter($('#widgetTotalAlert'), Number(res.total_alert) || 0);
                    },
                    error: function(err) {
                        console.error('Widget error:', err);
                    }
                });
            }

            // -------------------------------------------------------
            // CHART 1: Inbound Per Bulan
            // -------------------------------------------------------
            function chartInboundBulanan() {
                $.ajax({
                    url: "{{ url('api/dashboard/wrm/inbound/per-periode') }}",
                    type: 'GET',
                    data: {
                        periode,
                        tgl_mulai: tglMulai,
                        tgl_akhir: tglAkhir
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response || response.length === 0) {
                            $('#chartInboundBulanan').html(noDataHtml());
                            return;
                        }

                        const categories = response.map(item => item.label);
                        const dataQty = response.map(item => parseInt(item.total_qty) || 0);
                        const dataPallet = response.map(item => parseInt(item.total_pallet) || 0);

                        $('#chartInboundBulanan').empty();
                        new ApexCharts(document.querySelector('#chartInboundBulanan'), {
                            chart: {
                                type: 'bar',
                                height: 350,
                                stacked: false
                            },
                            series: [{
                                    name: 'Total QTY (KG)',
                                    data: dataQty
                                },
                                {
                                    name: 'Total Pallet',
                                    data: dataPallet
                                }
                            ],
                            xaxis: {
                                categories: categories,
                                title: {
                                    text: 'Periode'
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'Jumlah'
                                }
                            },
                            colors: ['#4b38b3', '#3FBFBF'],
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'light',
                                    type: 'vertical',
                                    shadeIntensity: 0.4,
                                    gradientToColors: ['#2e2370', '#1a8f8f'],
                                    opacityFrom: 1,
                                    opacityTo: 1,
                                    stops: [0, 100]
                                }
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '40%',
                                    borderRadius: 3,
                                    borderRadiusApplication: 'end'
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                custom: function({
                                    series,
                                    seriesIndex,
                                    dataPointIndex
                                }) {
                                    const item = response[dataPointIndex];
                                    return `
                                        <div class="my-tooltip">
                                            <b>${item.label}</b><br/>
                                            Total QTY: <b>${parseInt(item.total_qty).toLocaleString('id-ID')} KG</b><br/>
                                            Total Pallet: <b>${parseInt(item.total_pallet).toLocaleString('id-ID')} Pallet</b><br/>
                                            Total Batch: <b>${parseInt(item.total_batch).toLocaleString('id-ID')} SPB</b>
                                        </div>`;
                                }
                            }
                        }).render();
                    },
                    error: function(err) {
                        console.error('chartInboundBulanan error:', err);
                    }
                });
            }

            // -------------------------------------------------------
            // CHART 2: Total Stok Per Barang
            // -------------------------------------------------------
            function chartStokPerBarang() {
                $.ajax({
                    url: "{{ url('api/dashboard/wrm/inbound/stok-per-barang') }}",
                    type: 'GET',
                    data: {
                        periode,
                        tgl_mulai: tglMulai,
                        tgl_akhir: tglAkhir
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response || response.length === 0) {
                            $('#chartStokPerBarang').html(noDataHtml());
                            return;
                        }

                        const categories = response.map(item => item.nama_barang);
                        const dataQty = response.map(item => parseInt(item.total_qty) || 0);
                        const dataPallet = response.map(item => parseInt(item.total_pallet) || 0);

                        $('#chartStokPerBarang').empty();
                        new ApexCharts(document.querySelector('#chartStokPerBarang'), {
                            chart: {
                                type: 'bar',
                                height: 320
                            },
                            series: [{
                                    name: 'Total QTY (KG)',
                                    data: dataQty
                                },
                                {
                                    name: 'Total Pallet',
                                    data: dataPallet
                                }
                            ],
                            xaxis: {
                                categories: categories
                            },
                            yaxis: {
                                title: {
                                    text: 'Jumlah'
                                }
                            },
                            colors: ['#F2C36B', '#4968A6'],
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'light',
                                    type: 'vertical',
                                    gradientToColors: ['#8C6228', '#202C47'],
                                    opacityFrom: 1,
                                    opacityTo: 1,
                                    stops: [0, 100]
                                }
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '45%',
                                    borderRadius: 3,
                                    borderRadiusApplication: 'end'
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                custom: function({
                                    series,
                                    seriesIndex,
                                    dataPointIndex
                                }) {
                                    const item = response[dataPointIndex];
                                    return `
                                        <div class="my-tooltip">
                                            <b>${item.nama_barang}</b><br/>
                                            Total QTY: <b>${parseInt(item.total_qty).toLocaleString('id-ID')} KG</b><br/>
                                            Total Pallet: <b>${parseInt(item.total_pallet).toLocaleString('id-ID')} Pallet</b><br/>
                                            Total Batch: <b>${parseInt(item.total_batch).toLocaleString('id-ID')} SPB</b>
                                        </div>`;
                                }
                            }
                        }).render();
                    },
                    error: function(err) {
                        console.error('chartStokPerBarang error:', err);
                    }
                });
            }

            // -------------------------------------------------------
            // CHART 3: Distribusi Status (Donut)
            // -------------------------------------------------------
            function chartDistribusiStatus() {
                $.ajax({
                    url: "{{ url('api/dashboard/wrm/inbound/distribusi-status') }}",
                    type: 'GET',
                    data: {
                        periode,
                        tgl_mulai: tglMulai,
                        tgl_akhir: tglAkhir
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response || response.length === 0) {
                            $('#chartDistribusiStatus').html(noDataHtml());
                            return;
                        }

                        const labels = response.map(item => item.status);
                        const data = response.map(item => parseInt(item.total_pallet) || 0);

                        $('#chartDistribusiStatus').empty();
                        new ApexCharts(document.querySelector('#chartDistribusiStatus'), {
                            chart: {
                                type: 'donut',
                                height: 320
                            },
                            series: data,
                            labels: labels,
                            colors: ['#4b38b3', '#F2C36B', '#e74c3c'],
                            legend: {
                                position: 'bottom'
                            },
                            dataLabels: {
                                formatter: function(val, opts) {
                                    return opts.w.config.labels[opts.seriesIndex] + ': ' +
                                        opts.w.globals.series[opts.seriesIndex]
                                        .toLocaleString('id-ID');
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val.toLocaleString('id-ID') + ' Pallet';
                                    }
                                }
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '65%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total Pallet'
                                            }
                                        }
                                    }
                                }
                            }
                        }).render();
                    },
                    error: function(err) {
                        console.error('chartDistribusiStatus error:', err);
                    }
                });
            }

            // -------------------------------------------------------
            // CHART 4: Stok Per Gudang & Lokasi
            // -------------------------------------------------------
            function chartStokPerGudang() {
                $.ajax({
                    url: "{{ url('api/dashboard/wrm/inbound/stok-per-gudang') }}",
                    type: 'GET',
                    data: {
                        periode,
                        tgl_mulai: tglMulai,
                        tgl_akhir: tglAkhir
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response || response.length === 0) {
                            $('#chartStokPerGudang').html(noDataHtml());
                            return;
                        }

                        const categories = response.map(item => item.gudang);
                        const dataQty = response.map(item => parseInt(item.total_qty) || 0);
                        const dataPallet = response.map(item => parseInt(item.total_pallet) || 0);

                        $('#chartStokPerGudang').empty();
                        new ApexCharts(document.querySelector('#chartStokPerGudang'), {
                            chart: {
                                type: 'bar',
                                height: 300
                            },
                            series: [{
                                    name: 'Total QTY (KG)',
                                    data: dataQty
                                },
                                {
                                    name: 'Total Pallet',
                                    data: dataPallet
                                }
                            ],
                            xaxis: {
                                categories: categories,
                                title: {
                                    text: 'Gudang'
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'Jumlah'
                                }
                            },
                            colors: ['#3FBFBF', '#4968A6'],
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'light',
                                    type: 'vertical',
                                    gradientToColors: ['#1a8f8f', '#202C47'],
                                    opacityFrom: 1,
                                    opacityTo: 1,
                                    stops: [0, 100]
                                }
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '35%',
                                    borderRadius: 3,
                                    borderRadiusApplication: 'end'
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 'bold',
                                    colors: ['#000']
                                },
                                formatter: val => val === 0 ? '' : val.toLocaleString('id-ID')
                            },
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                custom: function({
                                    series,
                                    seriesIndex,
                                    dataPointIndex
                                }) {
                                    const item = response[dataPointIndex];
                                    return `
                                        <div class="my-tooltip">
                                            <b>Gudang: ${item.gudang}</b><br/>
                                            Total QTY: <b>${parseInt(item.total_qty).toLocaleString('id-ID')} KG</b><br/>
                                            Total Pallet: <b>${parseInt(item.total_pallet).toLocaleString('id-ID')} Pallet</b>
                                        </div>`;
                                }
                            }
                        }).render();
                    },
                    error: function(err) {
                        console.error('chartStokPerGudang error:', err);
                    }
                });
            }

            // -------------------------------------------------------
            // HELPERS
            // -------------------------------------------------------
            function animateCounter($el, target) {
                let current = 0;
                let step = Math.ceil(target / 50);
                let interval = setInterval(function() {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(interval);
                    }
                    $el.text(Number(current).toLocaleString('id-ID'));
                }, 30);
            }

            function noDataHtml() {
                return `<div class="text-center p-4 text-muted">
                            <i class="mdi mdi-database-off" style="font-size:48px;"></i>
                            <p class="mt-2 mb-0">Data tidak tersedia</p>
                        </div>`;
            }
        });
    </script>
@endsection
