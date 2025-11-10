@extends('layouts.app')

@section('styles')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }

        .min-vh-75 {
            min-height: 75vh;
        }

        .card {
            border-radius: 15px;
        }

        .alert {
            border-radius: 10px;
        }

        .rounded-circle {
            border-radius: 50% !important;
        }

        .rounded-pill {
            border-radius: 50rem !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="row justify-content-center align-items-center min-vh-75">
                <div class="col-md-6 col-lg-6">
                    <div class="text-center">

                        {{-- Welcome Message --}}
                        <h1 class="fw-bold text-primary mb-3" data-aos="fade-up">Welcome to Warehouse Management System!</h1>
                        <p class="lead text-muted mb-4" data-aos="fade-up" data-aos-delay="100">Hai, <span
                                class="text-primary fw-semibold" data-aos="fade-up"
                                data-aos-delay="100">{{ ucwords(str_replace('_', ' ', Auth::user()->nama_legkap ?? (Auth::user()->username ?? 'Warehouse User'))) }}</span>
                            👋</p>

                        {{-- Simple Info Card --}}
                        <div class="card shadow-sm border-0 mb-4" data-aos="fade-up" data-aos-delay="200">
                            <div class="card-body p-4">
                                @if (session('status'))
                                    <div class="alert alert-success border-0" role="alert">
                                        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                                    </div>
                                @endif

                                <div class="d-flex align-items-center justify-content-center mb-3">
                                    <i class="bi bi-shield-check text-success me-2"></i>
                                    <span class="text-muted">{{ __('You are logged in!') }}</span>
                                </div>

                                {{-- User Info --}}
                                <div class="row text-center" data-aos="fade-up" data-aos-delay="300">
                                    <div class="col-4" data-aos="fade-up"> <small class="text-muted">Jabatan</small>
                                        <p class="mb-0 fw-medium text-info">
                                            {{ ucwords(str_replace('_', ' ', Auth::user()->jabatan ?? '-')) }}
                                        </p>
                                    </div>
                                    <div class="col-4" data-aos="fade-up">
                                        <small class="text-muted">Departemen</small>
                                        <p class="mb-0 fw-medium text-success">
                                            {{ ucwords(str_replace('_', ' ', Auth::user()->departemen ?? '-')) }}</p>
                                    </div>
                                    <div class="col-4" data-aos="fade-up">
                                        <small class="text-muted">Bagian</small>
                                        <p class="mb-0 fw-medium text-danger">
                                            {{ ucwords(str_replace('_', ' ', Auth::user()->bagian ?? '-')) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Simple Footer Text --}}
                        <p class="text-muted small" data-aos="fade-up" data-aos-delay="400">
                            <i class="bi bi-clock me-1"></i>
                            Login: {{ date('d M Y, H:i') }}
                        </p>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        @if (session('error'))
            toastr.options = {
                "closeButton": true,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "0",
                "extendedTimeOut": "0",
                "tapToDismiss": false
            }
            toastr.error("{{ session('error') }}", "Peringatan!");
        @endif

        @if (session('success'))
            toastr.success("{{ session('success') }}", "Berhasil!");
        @endif
    </script>
@endsection
