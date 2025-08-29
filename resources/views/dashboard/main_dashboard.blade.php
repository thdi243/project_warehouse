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
            <div class="row">
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
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="" data-aos="fade-up" data-aos-delay="200">
                        <div class="card card-animate shadow-sm border-0 rounded-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase fw-medium text-muted mb-2">Total User</p>
                                        <h4 class="fs-24 fw-bold ff-secondary mb-0" id="totalUser"></h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-soft-primary rounded-circle fs-3 shadow">
                                            <i class="bx bx-user text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-xl-3 col-md-6">
                    <div class="" data-aos="fade-up" data-aos-delay="200">
                        <div class="card card-animate shadow-sm border-0 rounded-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase fw-medium text-muted mb-2">Total User</p>
                                        <h4 class="fs-24 fw-bold ff-secondary mb-0" id="totalUser"></h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-soft-primary rounded-circle fs-3 shadow">
                                            <i class="bx bx-user text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="col-xl-3 col-md-6">
                    <div class="" data-aos="fade-up" data-aos-delay="200">
                        <div class="card card-animate shadow-sm border-0 rounded-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase fw-medium text-muted mb-2">Total User</p>
                                        <h4 class="fs-24 fw-bold ff-secondary mb-0" id="totalUser"></h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-soft-primary rounded-circle fs-3 shadow">
                                            <i class="bx bx-user text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="" data-aos="fade-up" data-aos-delay="200">
                        <div class="card card-animate shadow-sm border-0 rounded-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="text-uppercase fw-medium text-muted mb-2">Total User</p>
                                        <h4 class="fs-24 fw-bold ff-secondary mb-0" id="totalUser"></h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-soft-primary rounded-circle fs-3 shadow">
                                            <i class="bx bx-user text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="" data-aos="fade-up" data-aos-delay="400">
                        <div class="card card-animate shadow-sm">
                            <div class="card-header">
                                <h4 class="card-title mb-0">TKBM By Month</h4>
                            </div>
                            <div class="card-body">
                                <div id="tkbmChart"></div>
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
            loadUserWidget();

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
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return Math.round(val);
                                    }
                                }
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#tkbmChart"),
                            options);
                        chart.render();
                    },
                    error: function(err) {
                        console.error("Gagal ambil data:", err);
                    }
                });
            }

            function loadUserWidget() {
                $.ajax({
                    url: "{{ url('api/dashboard/user') }}",
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        $("#totalUser").text(response.data);
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        $("#totalUser").text("0");
                    }
                });
            }
        });
    </script>
@endsection
