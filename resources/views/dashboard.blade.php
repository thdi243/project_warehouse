@extends('layouts.app')

@section('styles')
    <style>
        .hero-card {
            height: 70vh;
            border-radius: 10px;
            background-image: url('{{ asset('assets/images/warehouse_ai.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            clip-path: polygon(0 0, 100% 0, 93% 100%, 0% 100%);
            position: relative;
        }

        .hero-overlay {
            background: linear-gradient(135deg, rgba(130, 2, 0, 1), rgba(255, 255, 255, 0.35));
            border-radius: 10px;
        }

        /* Text Welcome di tengah kiri */
        .hero-text {
            position: absolute;
            top: 50%;
            left: 10%;
            transform: translateY(-50%);
            z-index: 3;
        }

        .footer-text {
            height: 35px;
            position: absolute;
            top: 98%;
            transform: translateY(-50%);
            z-index: 5;
            background: linear-gradient(135deg, rgba(255, 106, 19, 0.85), rgba(255, 255, 255, 0.35));
        }

        /* Logo di kanan atas */
        .bas-logo {
            position: absolute;
            top: 30px;
            right: 7%;
            width: 50px;
            z-index: 4;
            animation: bounce 2s infinite ease-in-out;
            box-shadow:
        }

        .wings-logo {
            position: absolute;
            top: 30px;
            right: 15%;
            width: 90px;
            z-index: 4;
            animation: bounce 3s infinite ease-in-out;
            box-shadow:
        }

        /* Animasi bounce lembut */
        @keyframes bounce {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }

            100% {
                transform: translateY(0);
            }
        }

        /* Tablet */
        @media (max-width: 992px) {
            .bas-logo {
                right: 5%;
                width: 40px;
            }

            .wings-logo {
                right: 15%;
                width: 70px;
            }
        }

        /* HP Landscape & HP Standar */
        @media (max-width: 768px) {
            .bas-logo {
                top: 20px;
                right: 5%;
                width: 35px;
            }

            .wings-logo {
                top: 20px;
                right: 18%;
                width: 60px;
            }

            .hero-text h1 {
                font-size: 28px !important;
            }
        }

        /* HP kecil (<480px) */
        @media (max-width: 480px) {

            /* Geser logo ke atas kanan agak renggang */
            .bas-logo {
                top: 20px;
                right: 5%;
                width: 28px;
            }

            .wings-logo {
                top: 20px;
                right: 20%;
                width: 48px;
            }

            /* Kecilkan teks biar rapi */
            .hero-text h1 {
                font-size: 24px !important;
            }

            .hero-card {
                height: 60vh;
            }

            .footer-text h6 {
                font-size: 12px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content d-flex align-items-center min-vh-100 bg-light">
        <div class="container-fluid">
            <div class="row mb-2">

                {{-- Hero Banner --}}
                <div class="col-12">
                    <div class="hero-card overflow-hidden shadow">

                        <div class="hero-overlay position-absolute w-100 h-100 top-0 start-0"></div>

                        <!-- Logo Bounce -->
                        <img src="{{ asset('assets/images/logo/logo-no-teks.png') }}" class="bas-logo" alt="Logo">
                        <img src="{{ asset('assets/images/logo/wings.png') }}" class="wings-logo" alt="Logo">

                        <!-- Text -->
                        <div class="hero-text">
                            <h1 class="fw-light text-white" style="font-size: 40px;">Welcome,</h1>
                            <h1 class="fw-bold text-uppercase text-white" style="font-size: 50px;">
                                {{ Auth::user()->nama_lengkap ?? Auth::user()->username }}
                            </h1>
                        </div>

                        <div class="footer-text d-flex w-100 justify-content-center align-items-center">
                            <h6 class="fw-bold text-white">PT Bumi Alam Segar</h6>
                        </div>

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
