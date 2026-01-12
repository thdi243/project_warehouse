@extends('layouts.app')

@section('styles')
    <style>
        .welcome-card {
            background: linear-gradient(135deg, #4b38b3, #7d6cfa);
            color: #fff;
        }

        .welcome-card h1,
        .welcome-card p {
            color: #fff;
        }

        .img-dashboard {
            width: 110px;
            height: 110px;
            object-fit: cover
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <h4 class="mb-4 fw-bold">Dashboard Pemeriksaan P2H OKE MANTAP TES Banget INI</h4>

            {{-- Widget --}}
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Total Forklift Aktif</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: rgba(181, 254, 215, 0.6);">
                                        <i class="mdi mdi-forklift text-success fs-1"></i>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2">
                                        <span class="counter-value text-success" id="totalForklift">0</span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Total Pallet Mover Aktif</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: rgba(181, 193, 254, 0.6);">
                                        <i class="mdi mdi-dolly text-primary fs-1"></i>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2">
                                        <span class="counter-value text-primary" id="totalPalletMover">0</span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Operator Forklift Aktif</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: rgba(254, 181, 181, 0.6);">
                                        <i class="bx bx-user-circle text-danger fs-1"></i>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2">
                                        <span class="counter-value text-danger" id="opForklift">0</span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Operator Pallet Mover Aktif</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: rgba(243, 254, 181, 0.6);">
                                        <i class="mdi mdi-human-dolly text-warning fs-1"></i>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2">
                                        <span class="counter-value text-warning" id="opPalletMover">0</span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Forklift --}}
            <div class="row">
                <!-- Grafik Kelayakan -->
                <div class="col-md-6">
                    <div class="" data-aos="fade-up">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Distribusi Kelayakan Forklift Monthly</h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #F2C36B">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanChart1" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanChart1" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filterBulanChart1">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="chartKelayakan" dir="ltr" style="height: 350px;"></div>
                                {{-- <div id="tkbmQtyTerpal" class="apex-charts" dir="ltr"></div> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Komponen Masalah Terbanyak -->
                <div class="col-md-6">
                    <div class="" data-aos="fade-up">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Komponen Masalah Forklift Monthly</h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #D73535">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanChart2" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanChart2" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filterBulanChart2">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="chartTopMasalah" dir="ltr" style="height: 350px;"></div>
                                {{-- <div id="tkbmQtyTerpal" class="apex-charts" dir="ltr"></div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="" data-aos="fade-up">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Operator Pemeriksa Forklift Monthly</h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #1C4D8D">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanChart3" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanChart3" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filterBulanChart3">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="chartOperator" dir="ltr" style="height: 350px;"></div>
                                {{-- <div id="tkbmQtyTerpal" class="apex-charts" dir="ltr"></div> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unit Forklift distribusi -->
                <div class="col-md-6">
                    <div class="" data-aos="fade-up">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Unit Forklift Bermasalah Monthly</h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #0C7779">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanChart4" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanChart4" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filterBulanChart4">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="chartUnitForklift" dir="ltr" style="height: 350px;"></div>
                                {{-- <div id="tkbmQtyTerpal" class="apex-charts" dir="ltr"></div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="mb-4 text-center fw-bold">Pallet Mover</h4>

            {{-- Chart Pallet Mover --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="" data-aos="fade-up">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Distribusi Kelayakan Pallet Mover Monthly</h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #F2C36B">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanChart5" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanChart5" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filterBulanChart5">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="chartKelayakanPallMov" dir="ltr" style="height: 350px;"></div>
                                {{-- <div id="tkbmQtyTerpal" class="apex-charts" dir="ltr"></div> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Komponen Masalah Terbanyak -->
                <div class="col-md-6">
                    <div class="" data-aos="fade-up">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Komponen Masalah Pallet Mover Monthly</h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #D73535">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanChart6" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanChart6" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filterBulanChart6">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="chartTopMasalahPallMov" dir="ltr" style="height: 350px;"></div>
                                {{-- <div id="tkbmQtyTerpal" class="apex-charts" dir="ltr"></div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="" data-aos="fade-up">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Operator Pemeriksa Pallet Mover Monthly</h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #1C4D8D">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanChart7" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanChart7" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filterBulanChart7">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="chartOperatorPallMov" dir="ltr" style="height: 350px;"></div>
                                {{-- <div id="tkbmQtyTerpal" class="apex-charts" dir="ltr"></div> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unit Pallet Mover distribusi -->
                <div class="col-md-6">
                    <div class="" data-aos="fade-up">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Unit Pallet Mover Bermasalah Monthly</h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #0C7779">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanChart8" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanChart8" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filterBulanChart8">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="chartUnitPallMov" dir="ltr" style="height: 350px;"></div>
                                {{-- <div id="tkbmQtyTerpal" class="apex-charts" dir="ltr"></div> --}}
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
            loadSummary();
            loadKelayakanChart();
            loadTopMasalahChart();
            loadOperatorChart();
            loadUnitForkliftMasalah();
            loadKelayakanChartPallMov();
            loadTopMasalahChartPallMov();
            loadOperatorChartPallMov();
            loadUnitPallMovMasalah();

            // Load summary
            function loadSummary() {
                $.ajax({
                    url: "{{ url('api/dashboard/p2h/summary') }}",
                    method: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        $('#totalForklift').text(res.forklift_aktif);
                        $('#totalPalletMover').text(res.pallet_mover_aktif);
                        $('#opForklift').text(res.operator_forklift_aktif);
                        $('#opPalletMover').text(res.operator_pallet_mover_aktif);
                    },
                    error: function(xhr) {
                        console.error('Gagal load summary:', xhr.responseText);
                    }
                });
            }

            // Kelayakan - Doughnut Chart
            function loadKelayakanChart(bulan = null) {
                $.ajax({
                    url: "{{ url('api/dashboard/p2h/kelayakan') }}",
                    method: 'GET',
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    dataType: 'json',
                    success: function(res) {

                        Highcharts.chart('chartKelayakan', {
                            chart: {
                                type: 'pie',
                                backgroundColor: null
                            },
                            title: {
                                text: null
                            },
                            subtitle: {
                                text: res.bulan ? 'Periode: ' + res.bulan : null
                            },
                            plotOptions: {
                                pie: {
                                    innerSize: '60%', // donut
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        format: '<b>{point.name}</b>: {point.y}'
                                    }
                                }
                            },
                            tooltip: {
                                pointFormat: '<b>{point.y}</b> unit'
                            },
                            series: [{
                                name: 'Jumlah',
                                colorByPoint: true,
                                data: [{
                                        name: 'Layak (≥ 95%)',
                                        y: res.kategori.layak,
                                        color: '#28a745'
                                    },
                                    {
                                        name: 'Perlu Perhatian (85–94%)',
                                        y: res.kategori.perlu_perhatian,
                                        color: '#ffc107'
                                    },
                                    {
                                        name: 'Tidak Layak (< 85%)',
                                        y: res.kategori.tidak_layak,
                                        color: '#dc3545'
                                    }
                                ]
                            }]
                        });
                    },
                    error: function(xhr) {
                        console.error('Gagal load kelayakan:', xhr.responseText);
                    }
                });
            }

            // Masalah Terbanyak - Horizontal Bar Chart
            function loadTopMasalahChart(bulan = null) {
                $.ajax({
                    url: "{{ url('api/dashboard/p2h/masalah-terbanyak') }}",
                    method: 'GET',
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    dataType: 'json',
                    success: function(res) {

                        // 🔥 AMBIL DATA YANG BENAR
                        const rawData = res.data;

                        const categories = Object.keys(rawData).map(item =>
                            item.replace(/_/g, ' ').toUpperCase()
                        );
                        const values = Object.values(rawData);

                        Highcharts.chart('chartTopMasalah', {
                            chart: {
                                type: 'column',
                                backgroundColor: null
                            },
                            title: {
                                text: null
                            },
                            subtitle: {
                                text: res.bulan ? 'Periode: ' + res.bulan : null
                            },
                            xAxis: {
                                categories: categories,
                                crosshair: true
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Jumlah Masalah'
                                }
                            },
                            tooltip: {
                                headerFormat: '<b>{point.key}</b><br>',
                                pointFormat: '{point.y} temuan'
                            },
                            plotOptions: {
                                column: {
                                    borderRadius: 4,
                                    dataLabels: {
                                        enabled: true
                                    }
                                }
                            },
                            series: [{
                                name: 'Masalah',
                                data: values,
                                color: '#D73535'
                            }],
                            credits: {
                                enabled: false
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error('Gagal load masalah terbanyak:', xhr.responseText);
                    }
                });
            }

            // Operator - Vertical Bar Chart
            function loadOperatorChart(bulan = null) {
                $.ajax({
                    url: "{{ url('api/dashboard/p2h/operator') }}",
                    method: 'GET',
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    dataType: 'json',
                    success: function(res) {

                        const data = res.data ?? [];

                        const categories = data.map(item =>
                            item.operator ?? 'Tidak Diketahui'
                        );

                        const seriesData = data.map(item => ({
                            y: item.jumlah,
                            rata_kelayakan: item.rata_kelayakan
                        }));

                        Highcharts.chart('chartOperator', {
                            chart: {
                                type: 'column',
                                backgroundColor: null
                            },
                            title: {
                                text: null
                            },
                            subtitle: {
                                text: res.bulan ? 'Periode: ' + res.bulan : null
                            },
                            xAxis: {
                                categories: categories,
                                title: {
                                    text: 'Operator'
                                }
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Jumlah Pemeriksaan'
                                }
                            },
                            tooltip: {
                                formatter: function() {
                                    return `
                                        <b>${this.x}</b><br>
                                        Jumlah Pemeriksaan: <b>${this.y}</b><br>
                                        Rata-rata Kelayakan: <b>${this.point.rata_kelayakan}%</b>
                                    `;
                                }
                            },
                            plotOptions: {
                                column: {
                                    borderRadius: 6,
                                    dataLabels: {
                                        enabled: true
                                    }
                                }
                            },
                            series: [{
                                name: 'Jumlah Pemeriksaan',
                                data: seriesData,
                                color: '#1C4D8D'
                            }],
                            credits: {
                                enabled: false
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error('Gagal load operator:', xhr.responseText);
                    }
                });
            }

            function loadUnitForkliftMasalah(bulan = null) {
                $.ajax({
                    url: "{{ url('api/dashboard/p2h/masalah/unit-forklift') }}",
                    method: 'GET',
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    dataType: 'json',
                    success: function(res) {

                        const data = res.data ?? [];

                        const categories = data.map(item => item.nomor_unit);
                        const seriesData = data.map(item => item.jumlah_masalah);

                        Highcharts.chart('chartUnitForklift', {
                            chart: {
                                type: 'column',
                                backgroundColor: null
                            },
                            title: {
                                text: null
                            },
                            subtitle: {
                                text: res.bulan ? 'Periode: ' + res.bulan : null
                            },
                            xAxis: {
                                categories: categories,
                                title: {
                                    text: 'Nomor Unit Forklift'
                                }
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Jumlah Masalah'
                                }
                            },
                            tooltip: {
                                pointFormat: '<b>{point.y}</b> masalah'
                            },
                            plotOptions: {
                                column: {
                                    borderRadius: 8,
                                    dataLabels: {
                                        enabled: true
                                    }
                                }
                            },
                            series: [{
                                name: 'Jumlah Masalah',
                                data: seriesData,
                                color: '#0C7779' // soft red
                            }],
                            credits: {
                                enabled: false
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error('Gagal load unit forklift bermasalah:', xhr.responseText);
                    }
                });
            }

            // Shift - Pie Chart
            // $.ajax({
            //     url: "{{ url('api/dashboard/p2h/shift') }}",
            //     method: 'GET',
            //     dataType: 'json',
            //     success: function(res) {
            //         // Ganti null jadi 'Tidak Diisi' dan tambahkan label "Shift x"
            //         var labels = res.map(x => x.shift === null ? 'Tidak Diisi' : 'Shift ' + x.shift);
            //         var data = res.map(x => x.total);

            //         var options = {
            //             chart: {
            //                 type: 'pie',
            //                 height: 350
            //             },
            //             labels: labels,
            //             series: data,
            //             fill: {
            //                 type: 'gradient',
            //             },
            //             colors: [
            //                 '#8EC5FC', // soft blue
            //                 '#9EE6B8', // soft green
            //                 '#FFE29A', // soft yellow
            //                 '#F4A6A6' // soft red
            //             ],
            //             legend: {
            //                 position: 'bottom'
            //             },
            //             responsive: [{
            //                 breakpoint: 480,
            //                 options: {
            //                     chart: {
            //                         height: 250
            //                     },
            //                     legend: {
            //                         position: 'bottom'
            //                     }
            //                 }
            //             }]
            //         };
            //         var chart = new ApexCharts(document.querySelector("#chartShift"), options);
            //         chart.render();
            //     },
            //     error: function(xhr, status, error) {
            //         console.error('Gagal load shift:', error);
            //     }
            // });

            function loadKelayakanChartPallMov(bulan = null) {
                $.ajax({
                    url: "{{ url('api/dashboard/p2h/pallet-mover/kelayakan') }}",
                    method: 'GET',
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    dataType: 'json',
                    success: function(res) {

                        Highcharts.chart('chartKelayakanPallMov', {
                            chart: {
                                type: 'pie',
                                backgroundColor: null
                            },
                            title: {
                                text: null
                            },
                            subtitle: {
                                text: res.bulan ? 'Periode: ' + res.bulan : null
                            },
                            plotOptions: {
                                pie: {
                                    innerSize: '60%', // donut
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        format: '<b>{point.name}</b>: {point.y}'
                                    }
                                }
                            },
                            tooltip: {
                                pointFormat: '<b>{point.y}</b> unit'
                            },
                            series: [{
                                name: 'Jumlah',
                                colorByPoint: true,
                                data: [{
                                        name: 'Layak (≥ 95%)',
                                        y: res.kategori.layak,
                                        color: '#28a745'
                                    },
                                    {
                                        name: 'Perlu Perhatian (85–94%)',
                                        y: res.kategori.perlu_perhatian,
                                        color: '#ffc107'
                                    },
                                    {
                                        name: 'Tidak Layak (< 85%)',
                                        y: res.kategori.tidak_layak,
                                        color: '#dc3545'
                                    }
                                ]
                            }]
                        });
                    },
                    error: function(xhr) {
                        console.error('Gagal load kelayakan:', xhr.responseText);
                    }
                });
            }

            function loadTopMasalahChartPallMov(bulan = null) {
                $.ajax({
                    url: "{{ url('api/dashboard/p2h/pallet-mover/part-masalah') }}",
                    method: 'GET',
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    dataType: 'json',
                    success: function(res) {

                        // 🔥 AMBIL DATA YANG BENAR
                        const rawData = res.data;

                        const categories = Object.keys(rawData).map(item =>
                            item.replace(/_/g, ' ').toUpperCase()
                        );
                        const values = Object.values(rawData);

                        Highcharts.chart('chartTopMasalahPallMov', {
                            chart: {
                                type: 'column',
                                backgroundColor: null
                            },
                            title: {
                                text: null
                            },
                            subtitle: {
                                text: res.bulan ? 'Periode: ' + res.bulan : null
                            },
                            xAxis: {
                                categories: categories,
                                crosshair: true
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Jumlah Masalah'
                                }
                            },
                            tooltip: {
                                headerFormat: '<b>{point.key}</b><br>',
                                pointFormat: '{point.y} temuan'
                            },
                            plotOptions: {
                                column: {
                                    borderRadius: 4,
                                    dataLabels: {
                                        enabled: true
                                    }
                                }
                            },
                            series: [{
                                name: 'Masalah',
                                data: values,
                                color: '#D73535'
                            }],
                            credits: {
                                enabled: false
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error('Gagal load masalah terbanyak:', xhr.responseText);
                    }
                });
            }

            function loadOperatorChartPallMov(bulan = null) {
                $.ajax({
                    url: "{{ url('api/dashboard/p2h/pallet-mover/operator') }}",
                    method: 'GET',
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    dataType: 'json',
                    success: function(res) {

                        const data = res.data ?? [];

                        const categories = data.map(item =>
                            item.operator ?? 'Tidak Diketahui'
                        );

                        const seriesData = data.map(item => ({
                            y: item.jumlah,
                            rata_kelayakan: item.rata_kelayakan
                        }));

                        Highcharts.chart('chartOperatorPallMov', {
                            chart: {
                                type: 'column',
                                backgroundColor: null
                            },
                            title: {
                                text: null
                            },
                            subtitle: {
                                text: res.bulan ? 'Periode: ' + res.bulan : null
                            },
                            xAxis: {
                                categories: categories,
                                title: {
                                    text: 'Operator'
                                }
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Jumlah Pemeriksaan'
                                }
                            },
                            tooltip: {
                                formatter: function() {
                                    return `
                                        <b>${this.x}</b><br>
                                        Jumlah Pemeriksaan: <b>${this.y}</b><br>
                                        Rata-rata Kelayakan: <b>${this.point.rata_kelayakan}%</b>
                                    `;
                                }
                            },
                            plotOptions: {
                                column: {
                                    borderRadius: 6,
                                    dataLabels: {
                                        enabled: true
                                    }
                                }
                            },
                            series: [{
                                name: 'Jumlah Pemeriksaan',
                                data: seriesData,
                                color: '#1C4D8D'
                            }],
                            credits: {
                                enabled: false
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error('Gagal load operator:', xhr.responseText);
                    }
                });
            }

            function loadUnitPallMovMasalah(bulan = null) {
                $.ajax({
                    url: "{{ url('api/dashboard/p2h/masalah/unit-pallet-mover') }}",
                    method: 'GET',
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    dataType: 'json',
                    success: function(res) {

                        const data = res.data ?? [];

                        const categories = data.map(item => item.nomor_unit);
                        const seriesData = data.map(item => item.jumlah_masalah);

                        Highcharts.chart('chartUnitPallMov', {
                            chart: {
                                type: 'column',
                                backgroundColor: null
                            },
                            title: {
                                text: null
                            },
                            subtitle: {
                                text: res.bulan ? 'Periode: ' + res.bulan : null
                            },
                            xAxis: {
                                categories: categories,
                                title: {
                                    text: 'Nomor Unit Forklift'
                                }
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Jumlah Masalah'
                                }
                            },
                            tooltip: {
                                pointFormat: '<b>{point.y}</b> masalah'
                            },
                            plotOptions: {
                                column: {
                                    borderRadius: 8,
                                    dataLabels: {
                                        enabled: true
                                    }
                                }
                            },
                            series: [{
                                name: 'Jumlah Masalah',
                                data: seriesData,
                                color: '#0C7779' // soft red
                            }],
                            credits: {
                                enabled: false
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error('Gagal load unit forklift bermasalah:', xhr.responseText);
                    }
                });
            }

            // Filter
            $('#filterBulanChart1').on('click', function() {
                var bulan = $('#bulanChart1').val();
                loadKelayakanChart(bulan);
            });

            $('#filterBulanChart2').on('click', function() {
                var bulan = $('#bulanChart2').val();
                loadTopMasalahChart(bulan);
            });

            $('#filterBulanChart3').on('click', function() {
                var bulan = $('#bulanChart3').val();
                loadOperatorChart(bulan);
            });

            $('#filterBulanChart4').on('click', function() {
                var bulan = $('#bulanChart4').val();
                loadUnitForkliftMasalah(bulan);
            });

            $('#filterBulanChart5').on('click', function() {
                var bulan = $('#bulanChart5').val();
                loadKelayakanChartPallMov(bulan);
            });

            $('#filterBulanChart6').on('click', function() {
                var bulan = $('#bulanChart6').val();
                loadTopMasalahChartPallMov(bulan);
            });

            $('#filterBulanChart7').on('click', function() {
                var bulan = $('#bulanChart7').val();
                loadOperatorChartPallMov(bulan);
            });

            $('#filterBulanChart8').on('click', function() {
                var bulan = $('#bulanChart8').val();
                loadUnitPallMovMasalah(bulan);
            });
        });
    </script>
@endsection
