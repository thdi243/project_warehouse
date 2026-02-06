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

            {{-- Widget --}}
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body" style="background: linear-gradient(135deg, #a8e6cf, #dcedc1, #e6f4ea);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Total Qty Terpal</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: rgba(238, 254, 181, 0.6);">
                                        <i class="bx bx-package text-success fs-1"></i>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2">
                                        <span class="counter-value text-success" id="totalQtyTerpal">0</span>
                                        <span class="fs-5 ms-2 text-success">Pcs</span>
                                    </h4>
                                    <p class="small mb-0 opacity-75">
                                        <i class="bx bx-calendar me-1"></i> Qty Terpal by Month
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
                                    <p class="text-uppercase fw-bold text-dark mb-0">Total Qty Slipsheet</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: #e6ebf4;">
                                        <i class="bx bx-layer text-info fs-1"></i>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2 text-dark">
                                        <span class="counter-value text-info" id="totalQtySlipsheet">0</span>
                                        <span class="text-info fs-5 ms-2">Pcs</span>
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        <i class="bx bx-calendar me-1"></i> Qty Slipsheet by Month
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
                                    <p class="text-uppercase fw-bold text-dark mb-0">Total Qty Pallet</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: #f4f2e6;">
                                        <i class="bx bx-table text-warning fs-1"></i>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2 text-dark">
                                        <span class="counter-value text-warning" id="totalQtyPallet">0</span>
                                        <span class="text-warning fs-5 ms-2">Pcs</span>
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        <i class="bx bx-calendar me-1"></i> Qty Pallet by Month
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div data-aos="fade-up" data-aos-delay="600">
                        <div class="card card-animate shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body" style="background: linear-gradient(135deg, #e7e3ff, #ffffff, #e6e7f4);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-uppercase fw-bold text-dark mb-0">Grand Total BPS</p>
                                    <div class="icon-box d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                                        style="width: 50px; height: 50px; background: #e6e7f4;">
                                        <i class="bx bx-bar-chart text-primary fs-1"></i>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="fs-3 fw-semibold ff-secondary mb-2 text-dark">
                                        <span class="fs-5 text-primary">Rp.</span>
                                        <span class="counter-value text-primary ms-1" id="grandTotal">0</span>
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        <i class="bx bx-calendar me-1"></i> Grand Total by Month
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart 1 --}}
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div data-aos="fade-up" data-aos-delay="800">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Total Qty Produk Per Month</h4>
                            </div>

                            <div class="card-body">
                                <div id="allProdukChart" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <div class="" data-aos="fade-up" data-aos-delay="1000">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Grand Total Per Month</h4>
                            </div>

                            <div class="card-body">
                                <div id="tkbmGrandTotal" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="" data-aos="fade-up" data-aos-delay="1200">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Total Qty Terpal <span id="bulanQtyTerpal"></span></h4>
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
                                            <label for="bulanTerpal" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanTerpal" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filteBulanTerpal">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="tkbmQtyTerpal" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="" data-aos="fade-up" data-aos-delay="1400">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Total Qty Slipsheet <span id="bulanQtySlipsheet"></span></h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #4968A6">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>


                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanSlipsheet" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanSlipsheet" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm"
                                            id="filteBulanSlipsheet">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="tkbmQtySlipsheet" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <div class="" data-aos="fade-up" data-aos-delay="1600">
                        <div class="card card-animate shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Total Qty Pallet <span id="bulanQtyPallet"></span></h4>
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded text-white shadow-sm"
                                        id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="background-color: #3FBFBF">
                                        <i class="bx bx-filter-alt fs-5"></i>
                                        <span>Filter</span>
                                    </a>


                                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-3"
                                        style="min-width: 280px;" aria-labelledby="dropdownFilter">

                                        <h6 class="fw-bold mb-3">Filter Data</h6>

                                        <div class="mb-3">
                                            <label for="bulanPallet" class="form-label">Pilih Bulan</label>
                                            <input type="month" id="bulanPallet" class="form-control shadow-sm">
                                        </div>

                                        <button class="btn btn-primary w-100 rounded-3 shadow-sm" id="filteBulanPallet">
                                            <i class="bx bx-check-circle me-1"></i> Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div id="tkbmQtyPallet" class="apex-charts" dir="ltr"></div>
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
            loadWidgetData();
            barProdukTkbm();
            tkbmGrandTotalChart();
            tkbmQtyTerpal();
            tkbmQtySlipsheet();
            tkbmQtyPallet();

            let today = new Date();
            let bulanNama = today.toLocaleString('id-ID', {
                month: 'long',
                year: 'numeric'
            });

            // Set default span
            $("#bulanQtyProduk").text(`(${bulanNama})`);
            $("#bulanQtyTerpal").text(`(${bulanNama})`);
            $("#bulanQtySlipsheet").text(`(${bulanNama})`);
            $("#bulanQtyPallet").text(`(${bulanNama})`);

            // Simpan semua chart dalam 1 object global
            let charts = {};

            function renderChart(chartId, url, bulan = null, optionsBuilder) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    success: function(response) {
                        // Hancurkan chart lama kalau ada
                        if (charts[chartId]) {
                            charts[chartId].destroy();
                            charts[chartId] = null;
                        }

                        if (response && response.length > 0) {
                            // bikin options chart dari callback
                            const options = optionsBuilder(response);

                            $(`#${chartId}`).html('');
                            charts[chartId] = new ApexCharts(document.querySelector(`#${chartId}`),
                                options);
                            charts[chartId].render();
                        } else {
                            $(`#${chartId}`).html(`
                                <div class="text-center p-4 text-muted">
                                    <i class="mdi mdi-database-off" style="font-size:48px;"></i>
                                    <p class="mt-2 mb-0">Data tidak tersedia</p>
                                </div>
                            `);
                        }
                    },
                    error: function(err) {
                        console.error("Gagal ambil data:", err);
                    }
                });
            }

            function tkbmQtyTerpal(bulan = null) {
                renderChart("tkbmQtyTerpal", "{{ url('api/dashboard/tkbm/qty-terpal') }}", bulan, function(
                    response) {
                    const categories = response.map(item => {
                        let dateObj = new Date(item.tanggal);
                        return ("0" + dateObj.getDate()).slice(-2);
                    });
                    const data = response.map(item => parseInt(item.total_terpal));

                    return {
                        chart: {
                            type: 'bar',
                            height: 300
                        },
                        series: [{
                            name: 'Qty Terpal',
                            data: data
                        }],
                        xaxis: {
                            categories: categories,
                            title: {
                                text: 'Tanggal',
                                offsetY: 90
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Qty Terpal'
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '10px'
                            }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shade: 'light',
                                type: "vertical", // "vertical" or "horizontal"
                                shadeIntensity: 0.5,
                                gradientToColors: ['#8C6228'], // warna tujuan (lebih gelap dari #3FBFBF)
                                inverseColors: false,
                                opacityFrom: 1,
                                opacityTo: 1,
                                stops: [0, 100]
                            }
                        },
                        colors: ['#F2C36B'],
                        tooltip: {
                            custom: function({
                                series,
                                seriesIndex,
                                dataPointIndex,
                                w
                            }) {
                                const item = response[dataPointIndex]; // data API per tanggal

                                let val = series[seriesIndex][dataPointIndex] || 0;
                                let fee = item.fee?.fee ?? 0;
                                let harga = item.harga?.harga_terpal ?? 0;
                                let tkbm = item.total_tkbm ?? 0;

                                return `
                                    <div class="my-tooltip" style="padding:8px; font-size:13px; line-height:1.5;">
                                        <strong>${item.tanggal}</strong><br/>
                                        <span>Qty Terpal: <b>${val.toLocaleString("id-ID")} pcs</b></span><br/>
                                        <span>TKBM: ${tkbm} orang</span><br/>
                                        <span>Fee: ${fee} %</span><br/>
                                        <span>Harga: Rp ${harga.toLocaleString("id-ID")}</span>
                                    </div>
                                `;
                            }
                        }
                    };
                });
            }

            function tkbmQtySlipsheet(bulan = null) {
                renderChart("tkbmQtySlipsheet", "{{ url('api/dashboard/tkbm/qty-slipsheet') }}", bulan, function(
                    response) {
                    const categories = response.map(item => {
                        let dateObj = new Date(item.tanggal);
                        return ("0" + dateObj.getDate()).slice(-2);
                    });
                    const data = response.map(item => parseInt(item.total_slipsheet));

                    return {
                        chart: {
                            type: 'bar',
                            height: 300
                        },
                        series: [{
                            name: 'Qty Slipsheet',
                            data: data
                        }],
                        xaxis: {
                            categories: categories,
                            title: {
                                text: 'Tanggal',
                                offsetY: 90
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Qty Slipsheet'
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '10px'
                            }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shade: 'light',
                                type: "vertical", // "vertical" or "horizontal"
                                shadeIntensity: 0.5,
                                gradientToColors: ['#202C47'], // warna tujuan (lebih gelap dari #3FBFBF)
                                inverseColors: false,
                                opacityFrom: 1,
                                opacityTo: 1,
                                stops: [0, 100]
                            }
                        },
                        colors: ['#4968A6'],
                        tooltip: {
                            custom: function({
                                series,
                                seriesIndex,
                                dataPointIndex,
                                w
                            }) {
                                const item = response[dataPointIndex]; // data API per tanggal

                                let val = series[seriesIndex][dataPointIndex] || 0;
                                let fee = item.fee?.fee ?? 0;
                                let harga = item.harga?.harga_slipsheet ?? 0;
                                let tkbm = item.total_tkbm ?? 0;

                                return `
                                    <div class="my-tooltip" style="padding:8px; font-size:13px; line-height:1.5;">
                                        <strong>${item.tanggal}</strong><br/>
                                        <span>Qty Slipsheet: <b>${val.toLocaleString("id-ID")} pcs</b></span><br/>
                                        <span>TKBM: ${tkbm} orang</span><br/>
                                        <span>Fee: ${fee} %</span><br/>
                                        <span>Harga: Rp ${harga.toLocaleString("id-ID")}</span>
                                    </div>
                                `;
                            }
                        }
                    };
                });
            }

            // Chart Pallet
            function tkbmQtyPallet(bulan = null) {
                renderChart("tkbmQtyPallet", "{{ url('api/dashboard/tkbm/qty-pallet') }}", bulan, function(
                    response) {
                    const categories = response.map(item => {
                        let dateObj = new Date(item.tanggal);
                        return ("0" + dateObj.getDate()).slice(-2);
                    });
                    const data = response.map(item => parseInt(item.total_pallet));

                    return {
                        chart: {
                            type: 'bar',
                            height: 300
                        },
                        series: [{
                            name: 'Qty Pallet',
                            data: data
                        }],
                        xaxis: {
                            categories: categories,
                            title: {
                                text: 'Tanggal',
                                offsetY: 90
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Qty Pallet'
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '10px'
                            }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shade: 'light',
                                type: "vertical", // "vertical" or "horizontal"
                                shadeIntensity: 0.5,
                                gradientToColors: ['#2e2370'], // warna tujuan (lebih gelap dari #3FBFBF)
                                inverseColors: false,
                                opacityFrom: 1,
                                opacityTo: 1,
                                stops: [0, 100]
                            }
                        },
                        colors: ['#3FBFBF'],
                        tooltip: {
                            custom: function({
                                series,
                                seriesIndex,
                                dataPointIndex,
                                w
                            }) {
                                const item = response[dataPointIndex]; // data API per tanggal

                                let val = series[seriesIndex][dataPointIndex] || 0;
                                let fee = item.fee?.fee ?? 0;
                                let harga = item.harga?.harga_pallet ?? 0;
                                let tkbm = item.total_tkbm ?? 0;

                                return `
                                    <div class="my-tooltip" style="padding:8px; font-size:13px; line-height:1.5;">
                                        <strong>${item.tanggal}</strong><br/>
                                        <span>Qty Pallet: <b>${val.toLocaleString("id-ID")} pcs</b></span><br/>
                                        <span>TKBM: ${tkbm} orang</span><br/>
                                        <span>Fee: ${fee} %</span><br/>
                                        <span>Harga: Rp ${harga.toLocaleString("id-ID")}</span>
                                    </div>
                                `;
                            }
                        }
                    };
                });
            }

            function tkbmGrandTotalChart() {
                $.ajax({
                    url: "{{ url('api/dashboard/tkbm/grand-total') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) { // langsung response.length
                        const data = response.data || [];

                        if (data.length === 0) {
                            $("#tkbmGrandTotal").html("<p class='text-center'>Tidak ada data</p>");
                            return;
                        }

                        let categories = [];
                        let grandTotalSeries = [];

                        data.forEach(item => {
                            categories.push(item.bulan.split(" ")[0]); // Jan, Feb, dst
                            grandTotalSeries.push(Number(item.grand_total));
                        });

                        const options = {
                            chart: {
                                type: 'bar',
                                height: 350
                            },
                            series: [{
                                name: 'Grand Total',
                                data: grandTotalSeries
                            }],
                            xaxis: {
                                categories: categories,
                                title: {
                                    text: 'Month',
                                    offsetY: 90, // Tambahkan ini untuk menurunkan posisi title
                                }
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'light',
                                    type: "vertical", // "vertical" or "horizontal"
                                    shadeIntensity: 0.5,
                                    gradientToColors: [
                                        '#2e2370'
                                    ], // warna tujuan (lebih gelap dari #3FBFBF)
                                    inverseColors: false,
                                    opacityFrom: 1,
                                    opacityTo: 1,
                                    stops: [0, 100]
                                }
                            },
                            colors: ['#3FBFBF'],
                            yaxis: {
                                title: {
                                    text: 'Grand Total (Rp)'
                                },
                                labels: {
                                    formatter: function(val) {
                                        return 'Rp ' + val.toLocaleString("id-ID");
                                    }
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    if (val >= 1000000000) {
                                        return 'Rp ' + (val / 1000000000).toFixed(1) + 'M';
                                    } else if (val >= 1000000) {
                                        return 'Rp ' + (val / 1000000).toFixed(1) + 'Jt';
                                    } else if (val >= 1000) {
                                        return 'Rp ' + (val / 1000).toFixed(0) + 'K';
                                    }
                                    return '';
                                },
                                style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    rotation: 90
                                },
                                background: {
                                    enabled: false
                                },
                                offsetY: 0,
                                textAnchor: 'middle'
                            },
                            tooltip: {
                                custom: function({
                                    series,
                                    seriesIndex,
                                    dataPointIndex
                                }) {
                                    let item = response[
                                        dataPointIndex]; // ambil data sesuai bar
                                    return `
                                            <div class="my-tooltip" style="padding:8px;">
                                                <b>${item.bulan}</b><br/>
                                                Produk: Rp ${parseFloat(item.total_produk).toLocaleString("id-ID")}<br/>
                                                Fee: Rp ${parseFloat(item.total_fee).toLocaleString("id-ID")}<br/>
                                                PPN: Rp ${parseFloat(item.total_ppn).toLocaleString("id-ID")}<br/>
                                                PPh: Rp ${parseFloat(item.total_pph).toLocaleString("id-ID")}<br/>
                                                <b>Grand Total: Rp ${parseFloat(item.grand_total).toLocaleString("id-ID")}</b>
                                            </div>
                                        `;
                                }
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#tkbmGrandTotal"),
                            options);
                        chart.render();

                    },
                    error: function(err) {
                        console.error("Gagal ambil data:", err);
                        $("#tkbmGrandTotal").html(
                            "<p class='text-center text-danger'>Error load data</p>");
                    }
                });
            }

            function barProdukTkbm(bulan = null) {
                $.ajax({
                    url: "{{ url('api/dashboard/tkbm/all_qty_produk') }}",
                    type: 'GET',
                    data: {
                        bulan: bulan
                    },
                    dataType: 'json',
                    success: function(response) {

                        if (!response || response.length === 0) {
                            $("#allProdukChart").html(
                                "<p class='text-center'>Tidak ada data</p>"
                            );
                            return;
                        }

                        const data = response.data;

                        const categories = data.map(item => item.bulan.split(" ")[0]);
                        const dataTerpal = data.map(item => parseInt(item.total_terpal) || 0);
                        const dataSlipsheet = data.map(item => parseInt(item.total_slipsheet) || 0);
                        const dataPallet = data.map(item => parseInt(item.total_pallet) || 0);


                        const options = {
                            chart: {
                                type: 'bar',
                                height: 350,
                                stacked: false
                            },
                            series: [{
                                    name: 'Qty Terpal',
                                    data: dataTerpal
                                },
                                {
                                    name: 'Qty Slipsheet',
                                    data: dataSlipsheet
                                },
                                {
                                    name: 'Qty Pallet',
                                    data: dataPallet
                                }
                            ],
                            xaxis: {
                                categories: categories
                            },
                            yaxis: {
                                title: {
                                    text: 'Pcs'
                                }
                            },
                            colors: ['#F2C36B', '#4968A6', '#3FBFBF'],
                            dataLabels: {
                                enabled: true,
                                formatter: val => val === 0 ? '' : `${val} pcs`
                            },
                            plotOptions: {
                                bar: {
                                    columnWidth: '25%',
                                    borderRadius: 0,
                                    borderRadiusApplication: 'end'
                                }
                            },
                            grid: {
                                padding: {
                                    left: 10,
                                    right: 10
                                }
                            },
                            stroke: {
                                show: false
                            },
                            tooltip: {
                                y: {
                                    formatter: val => `${val} pcs`
                                }
                            },
                            legend: {
                                position: 'bottom'
                            },
                            dataLabels: {
                                enabled: true,
                                offsetY: 50,
                                style: {
                                    colors: ['#000'], // 🖤 hitam
                                    fontSize: '12px',
                                    fontWeight: 'bold'
                                },
                                formatter: function(val) {
                                    return val === 0 ? '' : `${val}`;
                                }
                            },

                        };

                        // 🔥 penting biar ga double render
                        $("#allProdukChart").empty();

                        const chart = new ApexCharts(
                            document.querySelector("#allProdukChart"),
                            options
                        );
                        chart.render();
                    },
                    error: function(err) {
                        console.error(err);
                        $("#allProdukChart").html(
                            "<p class='text-center text-danger'>Gagal load data</p>"
                        );
                    }
                });
            }


            // Widget
            function animateCounter($el, target) {
                let current = 0;
                let step = Math.ceil(target / 50); // jumlah step animasi
                let interval = setInterval(function() {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(interval);
                    }
                    $el.text(current.toLocaleString());
                }, 30); // speed animasi (ms)
            }

            function loadWidgetData() {
                $.ajax({
                    url: "{{ url('api/dashboard/tkbm/widget') }}",
                    type: "GET",
                    dataType: "json",
                    success: function(res) {
                        // update card sesuai ID yg kamu taruh di Blade
                        animateCounter($("#totalQtyTerpal"), res.terpal);
                        animateCounter($("#totalQtySlipsheet"), res.slipsheet);
                        animateCounter($("#totalQtyPallet"), res.pallet);
                        animateCounter($("#grandTotal"), res.grand_total);
                    },
                    error: function(xhr) {
                        console.error("Gagal ambil data widget:", xhr);
                    }
                });
            }

            $('#applyFilter').click(function() {
                let bulan = $("#bulanFilter").val();
                if (!bulan) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih bulan terlebih dahulu.',
                    });
                    return;
                }

                let [year, month] = bulan.split("-");
                let bulanNama = new Date(bulan + "-01").toLocaleString('id-ID', {
                    month: 'long',
                    year: 'numeric'
                });

                $("#bulanQtyProduk").text(`(${bulanNama})`);

                pieProdukTkbm(bulan);
            });

            $('#filteBulanTerpal').click(function() {
                let bulan = $("#bulanTerpal").val();
                if (!bulan) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih bulan terlebih dahulu.',
                    });
                    return;
                }

                let [year, month] = bulan.split("-");
                let bulanNama = new Date(bulan + "-01").toLocaleString('id-ID', {
                    month: 'long',
                    year: 'numeric'
                });

                $("#bulanQtyTerpal").text(`(${bulanNama})`);

                // Panggil fungsi chart
                tkbmQtyTerpal(bulan);
            });

            $('#filteBulanSlipsheet').click(function() {
                let bulan = $("#bulanSlipsheet").val();
                if (!bulan) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih bulan terlebih dahulu.',
                    });
                    return;
                }

                let [year, month] = bulan.split("-");
                let bulanNama = new Date(bulan + "-01").toLocaleString('id-ID', {
                    month: 'long',
                    year: 'numeric'
                });

                $("#bulanQtySlipsheet").text(`(${bulanNama})`);

                // Panggil fungsi chart
                tkbmQtySlipsheet(bulan);
            });

            $('#filteBulanPallet').click(function() {
                let bulan = $("#bulanPallet").val();
                if (!bulan) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih bulan terlebih dahulu.',
                    });
                    return;
                }

                let [year, month] = bulan.split("-");
                let bulanNama = new Date(bulan + "-01").toLocaleString('id-ID', {
                    month: 'long',
                    year: 'numeric'
                });

                $("#bulanQtyPallet").text(`(${bulanNama})`);

                // Panggil fungsi chart
                tkbmQtyPallet(bulan);
            });
        });
    </script>
@endsection
