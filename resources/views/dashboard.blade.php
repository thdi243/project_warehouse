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
                <div class="col-12">
                    <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="300">
                        <div id="tkbmChart"></div>
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

            function tkbmChart() {
                const options = {
                    chart: {
                        type: 'bar',
                        height: 300
                    },
                    series: [{
                        name: 'Produksi',
                        data: [10, 20, 30, 40, 50]
                    }],
                    xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei']
                    }
                };

                const chart = new ApexCharts(document.querySelector("#tkbmChart"), options);
                chart.render();
            }
        });
    </script>
@endsection
