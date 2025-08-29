@extends('layouts.app')

@section('styles')
    <style>
        .welcome-card {
            background: linear-gradient(135deg, #3ea8eb, #1cc88a);
            /* background: linear-gradient(135deg, #ff9a9e, #fad0c4); */
            /* ungu -> hijau */
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
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-4 welcome-card p-4" data-aos="fade-up">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <div>
                                <h1 class="fw-bold mb-2">
                                    Selamat Datang, {{ Auth::user()->username }} 👋
                                </h1>
                                <p class="mb-0">
                                    Senang melihatmu kembali! Berikut ringkasan aktivitas dan laporan terbarumu.
                                </p>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <img src="{{ session('image_url', asset('material/assets/images/users/user-dummy-img.jpg')) }}"
                                    alt="avatar" class="rounded-circle img-dashboard">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-xl-6">
                    <div class="" data-aos="fade-up" data-aos-delay="300">
                        <div class="card card-animate shadow-sm">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Data Tkbm By Month</h4>
                            </div>

                            <div class="card-body">
                                <div id="tkbmChart" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="" data-aos="fade-up" data-aos-delay="300">
                        <div class="card card-animate shadow-sm">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mb-0">Distribusi Qty Produk By Month</h4>
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle text-muted" id="dropdownFilter"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>Filter</span>
                                    </a>

                                    <div class="dropdown-menu p-3" aria-labelledby="dropdownFilter">
                                        <label for="bulanFilter" class="form-label">Pilih Bulan</label>
                                        <input type="month" id="bulanFilter" class="form-control">
                                        <button class="btn btn-primary mt-2 w-100" id="applyFilter">Terapkan</button>
                                    </div>
                                </div>

                            </div>

                            <div class="card-body">
                                <div id="produkDistribusi" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="" data-aos="fade-up" data-aos-delay="300">
                        <div class="card card-animate shadow-sm">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Total Qty By Date</h4>
                            </div>

                            <div class="card-body">
                                <div id="tkbmTotalQty" class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="" data-aos="fade-up" data-aos-delay="300">
                        <div class="card card-animate shadow-sm">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Grand Total By Month</h4>
                            </div>

                            <div class="card-body">
                                <div id="tkbmGrandTotal" class="apex-charts" dir="ltr"></div>
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


            tkbmChart();
            pieProdukTkbm();
            tkbmTotalQty();
            tkbmGrandTotalChart();

            function tkbmChart() {
                $.ajax({
                    url: "{{ route('dashboard.tkbm.data') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        // Ambil bulan & total
                        const categories = response.map(item => item.bulan);
                        const data = response.map(item => item.banyak_data);

                        const options = {
                            chart: {
                                type: 'bar',
                                height: 300
                            },
                            series: [{
                                name: 'Data TKBM',
                                data: data
                            }],
                            xaxis: {
                                categories: categories
                            },
                            colors: ['#F2C36B'],
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return Math.round(val);
                                    }
                                }
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#tkbmChart"), options);
                        chart.render();
                    },
                    error: function(err) {
                        console.error("Gagal ambil data:", err);
                    }
                });
            }

            function pieProdukTkbm(bulan = null) {
                let chart;

                $.ajax({
                    url: "{{ url('api/dashboard/tkbm/produk') }}",
                    type: "GET",
                    dataType: "json",
                    data: bulan ? {
                        bulan: bulan
                    } : {},
                    success: function(response) {
                        if (chart) {
                            chart.destroy();
                            chart = null;
                        }

                        if (response.status && response.data.length > 0) {
                            let item = response.data[response.data.length - 1];

                            let totalTerpal = parseInt(item.total_terpal);
                            let totalSlipsheet = parseInt(item.total_slipsheet);
                            let totalPallet = parseInt(item.total_pallet);

                            const options = {
                                chart: {
                                    type: 'donut',
                                    height: 300
                                },
                                series: [totalTerpal, totalSlipsheet, totalPallet],
                                labels: ['Terpal', 'Slipsheet', 'Pallet'],
                                colors: ['#F2C36B', '#4968A6', '#3FBFBF'],
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    y: {
                                        formatter: val => `${val} Pcs`
                                    }
                                }
                            };

                            $("#produkDistribusi").html('');

                            chart = new ApexCharts(document.querySelector("#produkDistribusi"),
                                options);
                            chart.render();
                        } else {
                            $("#produkDistribusi").html(`
                                <div class="text-center p-4 text-muted">
                                    <i class="mdi mdi-database-off" style="font-size:48px;"></i>
                                    <p class="mt-2 mb-0">Data tidak tersedia</p>
                                </div>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        $("#produkDistribusi").text("Gagal load chart");
                    }
                });
            }

            function tkbmTotalQty() {
                let chart;
                $.ajax({
                    url: "{{ url('api/dashboard/tkbm/total-qty') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (chart) {
                            chart.destroy();
                            chart = null;
                        }

                        if (response.status && response.data.length > 0) {
                            // Ambil bulan & total
                            const categories = response.data.map(item => {
                                let dateObj = new Date(item.date);
                                return ("0" + dateObj.getDate()).slice(-
                                    2); // format 01, 02, ..., 31
                            });
                            const data = response.data.map(item => item.total_qty);

                            const options = {
                                chart: {
                                    type: 'bar',
                                    height: 300
                                },
                                series: [{
                                    name: 'Data TKBM',
                                    data: data
                                }],
                                xaxis: {
                                    categories: categories,
                                    title: {
                                        text: 'Date',
                                        offsetY: 90, // Tambahkan ini untuk menurunkan posisi title
                                    }
                                },
                                yaxis: {
                                    title: {
                                        text: 'Total Harga (Rp)'
                                    }
                                },
                                dataLabels: {
                                    enabled: true,
                                    style: {
                                        fontSize: '12px',
                                    }
                                },
                                colors: ['#4968A6'],
                                tooltip: {
                                    y: {
                                        formatter: function(val) {
                                            return 'Rp ' + val.toLocaleString("id-ID");
                                        }
                                    }
                                }
                            };

                            $("#tkbmTotalQty").html('');

                            chart = new ApexCharts(document.querySelector("#tkbmTotalQty"),
                                options);
                            chart.render();
                        } else {
                            $("#tkbmTotalQty").html(`
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

            function tkbmGrandTotalChart() {
                $.ajax({
                    url: "{{ url('api/dashboard/tkbm/grand-total') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status && response.data.length > 0) {
                            let categories = [];
                            let grandTotalSeries = [];

                            response.data.forEach(item => {
                                categories.push(item.bulan_nama);
                                grandTotalSeries.push(parseFloat(item.grand_total));
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
                                    // title: {
                                    //     text: 'Bulan'
                                    // }
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
                                        return 'Rp ' + val.toLocaleString("id-ID");
                                    }
                                },
                                tooltip: {
                                    y: {
                                        formatter: function(val) {
                                            return 'Rp ' + val.toLocaleString("id-ID");
                                        }
                                    }
                                }
                            };

                            const chart = new ApexCharts(document.querySelector("#tkbmGrandTotal"),
                                options);
                            chart.render();
                        } else {
                            $("#tkbmGrandTotal").html("<p class='text-center'>Tidak ada data</p>");
                        }
                    },
                    error: function(err) {
                        console.error("Gagal ambil data:", err);
                        $("#tkbmGrandTotal").html(
                            "<p class='text-center text-danger'>Error load data</p>");
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

                pieProdukTkbm(bulan);
            });
        });
    </script>
@endsection
