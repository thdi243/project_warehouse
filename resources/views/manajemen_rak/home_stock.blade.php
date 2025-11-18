@extends('layouts.app')

@section('styles')
    <style>
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h3 {
            color: #393939;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #6c757d;
            font-size: 1rem;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            height: 100%;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            border-color: transparent;
        }

        .feature-card.stock-location {
            --gradient-start: #6f42c1;
            --gradient-end: #a78bfa;
        }

        .feature-card.stock-hand {
            --gradient-start: #007bff;
            --gradient-end: #60a5fa;
        }

        .feature-card.stock-opname {
            --gradient-start: #198754;
            --gradient-end: #6edc93;
        }

        .card-body-custom {
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .feature-icon {
            font-size: 2.5rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            margin-bottom: 1.25rem;
            position: relative;
            transition: all 0.4s ease;
        }

        .feature-card:hover .feature-icon {
            transform: rotate(360deg) scale(1.1);
        }

        .bg-stock-location {
            background: linear-gradient(135deg, #6f42c1, #a78bfa);
            box-shadow: 0 6px 16px rgba(111, 66, 193, 0.25);
        }

        .bg-stock-hand {
            background: linear-gradient(135deg, #007bff, #60a5fa);
            box-shadow: 0 6px 16px rgba(0, 123, 255, 0.25);
        }

        .bg-stock-opname {
            background: linear-gradient(135deg, #198754, #6edc93);
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.25);
        }

        .feature-card h5 {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            color: #2d3748;
        }

        .feature-card p {
            color: #718096;
            font-size: 0.875rem;
            line-height: 1.6;
            margin-bottom: 1.25rem;
        }

        .btn-custom {
            padding: 0.625rem 1.75rem;
            border-radius: 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn-custom::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-custom:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-custom span {
            position: relative;
            z-index: 1;
        }

        .btn-purple-custom {
            background: linear-gradient(135deg, #6f42c1, #5a32a3);
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.3);
        }

        .btn-purple-custom:hover {
            box-shadow: 0 6px 16px rgba(111, 66, 193, 0.5);
            transform: translateY(-2px);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #007bff, #0056b3);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .btn-primary-custom:hover {
            box-shadow: 0 6px 16px rgba(0, 123, 255, 0.5);
            transform: translateY(-2px);
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #198754, #146c43);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }

        .btn-success-custom:hover {
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.5);
            transform: translateY(-2px);
        }

        .stats-badge {
            position: absolute;
            top: 0.875rem;
            right: 0.875rem;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.35rem 0.75rem;
            border-radius: 0.875rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: #2d3748;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .row.justify-content-center {
                justify-content: center !important;
            }

            .col-lg-4 {
                max-width: 400px;
            }
        }

        @media (max-width: 768px) {
            .page-header h3 {
                font-size: 1.75rem;
            }

            .page-header p {
                font-size: 0.95rem;
            }

            .feature-icon {
                width: 65px;
                height: 65px;
                font-size: 2.25rem;
            }

            .card-body-custom {
                padding: 1.75rem 1.25rem;
            }

            .feature-card h5 {
                font-size: 1.15rem;
            }

            .feature-card p {
                font-size: 0.85rem;
            }

            .btn-custom {
                padding: 0.55rem 1.5rem;
                font-size: 0.825rem;
            }
        }

        @media (max-width: 576px) {
            .page-header {
                margin-bottom: 2rem;
            }

            .page-header h3 {
                font-size: 1.5rem;
            }

            .page-header p {
                font-size: 0.875rem;
            }

            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }

            .card-body-custom {
                padding: 1.5rem 1rem;
            }

            .feature-card h5 {
                font-size: 1.1rem;
            }

            .feature-card p {
                font-size: 0.8rem;
            }

            .btn-custom {
                padding: 0.5rem 1.25rem;
                font-size: 0.8rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="page-header" data-aos="fade-down">
                <h3 class="fw-bold mb-1">🏷️ Home Stock - WSP</h3>
                <p>Kelola dan pantau data stok barang Anda dengan mudah</p>
            </div>

            <div class="row justify-content-center g-4 mb-3">
                <!-- Stock Location -->
                <div class="col-lg-4 col-md-6 col-sm-12" data-aos="fade-up" data-aos-delay="100">
                    <div class="card card-animate feature-card stock-location">
                        <span class="stats-badge">
                            <i class="mdi mdi-map-marker-outline me-1"></i> Active
                        </span>
                        <div class="card-body-custom">
                            <div class="feature-icon bg-stock-location text-white mx-auto">
                                <i class="mdi mdi-map-marker-radius"></i>
                            </div>
                            <h5>Stock Location</h5>
                            <p>Kelola dan pantau lokasi penyimpanan stok barang secara akurat dan terstruktur</p>
                            <a href="{{ route('rack.stock.stock-location') }}"
                                class="btn btn-custom btn-purple-custom text-white">
                                <span>
                                    <i class="mdi mdi-map-search-outline me-2"></i>Kelola Lokasi
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stock On Hand -->
                <div class="col-lg-4 col-md-6 col-sm-12" data-aos="fade-up" data-aos-delay="150">
                    <div class="card card-animate feature-card stock-hand">
                        <span class="stats-badge">
                            <i class="mdi mdi-check-outline me-1"></i> Ready
                        </span>
                        <div class="card-body-custom">
                            <div class="feature-icon bg-stock-hand text-white mx-auto">
                                <i class="mdi mdi-package-variant"></i>
                            </div>
                            <h5>Stock On Hand</h5>
                            <p>Upload dan lihat daftar stok barang yang tersedia saat ini secara real-time dan detail</p>
                            <a href="{{ route('rack.stock.stock-on-hand') }}"
                                class="btn btn-custom btn-primary-custom text-white">
                                <span>
                                    <i class="mdi mdi-upload me-2"></i>Upload Data
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stock Opname -->
                <div class="col-lg-4 col-md-6 col-sm-12" data-aos="fade-up" data-aos-delay="200">
                    <div class="card card-animate feature-card stock-opname">
                        <span class="stats-badge">
                            <i class="mdi mdi-lock-clock me-1"></i> Soon
                        </span>
                        <div class="card-body-custom">
                            <div class="feature-icon bg-stock-opname text-white mx-auto">
                                <i class="mdi mdi-clipboard-check-outline"></i>
                            </div>
                            <h5>Stock Opname</h5>
                            <p>Lakukan pengecekan fisik stok dan sesuaikan dengan sistem secara akurat</p>
                            <a href="{{ route('maintenance') }}"
                                class="btn btn-custom btn-success-custom text-white disabled" tabindex="-1"
                                aria-disabled="true">
                                <span>
                                    <i class="mdi mdi-play-circle-outline me-2"></i>Mulai Opname
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
