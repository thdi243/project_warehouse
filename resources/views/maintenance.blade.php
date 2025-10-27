@extends('layouts.app')

@section('styles')
    <style>
        .icon-wrapper {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content d-flex align-items-center justify-content-center min-vh-100 bg-light">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0 rounded-4 p-4">
                        <div class="card-body">
                            <div class="icon-wrapper mb-4">
                                <i class="mdi mdi-hammer-wrench"></i>
                            </div>

                            <h3 class="fw-bold text-primary mb-2">Fitur Dalam Pengembangan 🚧</h3>
                            <p class="text-muted mb-4">
                                Kami sedang bekerja keras untuk menghadirkan fitur ini agar bisa segera digunakan.
                                Terima kasih atas kesabaran dan dukungan Anda.
                            </p>

                            <!-- Features -->
                            <div class="row mb-2 g-3">
                                <div class="col-12 col-md-4">
                                    <div class="card card-animate border-0 shadow-sm rounded-4 text-center p-2 text-nowrap">
                                        <div class="card-body">
                                            <i class="mdi mdi-lightning-bolt-outline fs-1 text-primary mb-2"></i>
                                            <h6 class="fw-semibold text-dark">Cepat</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="card card-animate border-0 shadow-sm rounded-4 text-center p-2 text-nowrap">
                                        <div class="card-body">
                                            <i class="mdi mdi-shield-check-outline fs-1 text-primary mb-2"></i>
                                            <h6 class="fw-semibold text-dark">Aman</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="card card-animate border-0 shadow-sm rounded-4 text-center p-2 text-nowrap">
                                        <div class="card-body">
                                            <i class="mdi mdi-heart-outline fs-1 text-primary mb-2"></i>
                                            <h6 class="fw-semibold text-dark">Sempurna</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol responsif -->
                            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                <a href="{{ url()->previous() }}" class="btn btn-outline-primary rounded-pill px-4">
                                    <i class="mdi mdi-arrow-left me-1"></i> Kembali
                                </a>
                                <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4">
                                    <i class="mdi mdi-view-dashboard-outline me-1"></i> Ke Dashboard
                                </a>
                            </div>

                            <div class="mt-4 small text-muted">
                                <i class="mdi mdi-clock-outline"></i> Terakhir diperbarui: {{ now()->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
