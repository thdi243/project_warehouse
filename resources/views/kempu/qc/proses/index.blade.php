@extends('layouts.app')

@section('title', '| QC Proses Produksi')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Breadcrumb Header -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">QC Proses Produksi</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="#">Kempu</a></li>
                                <li class="breadcrumb-item active">QC Proses</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="text-center mb-4">
                <div
                    class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-2 rounded-pill bg-soft-success text-success fw-semibold fs-12">
                    <i class="ri-flask-line"></i> Quality Control Proses Produksi
                </div>
                <h3 class="fw-bold mb-1 text-body">Pemeriksaan Kualitas Kempu (QC Proses)</h3>
                <p class="text-muted fs-14 mb-2">Pilih tahapan inspeksi kempu di area Produksi</p>
                <div class="d-flex align-items-center justify-content-center gap-3 mt-2 flex-wrap">
                    <span class="badge bg-soft-primary text-primary px-3 py-2 fs-12 border border-primary-subtle">
                        <i class="ri-shield-check-line me-1"></i> Siap Cek Incoming & Pre Cuci:
                        <strong>{{ $totalPreCuciPending }}</strong>
                    </span>
                    <span class="badge bg-soft-success text-success px-3 py-2 fs-12 border border-success-subtle">
                        <i class="ri-flask-line me-1"></i> Siap Cek After Filling:
                        <strong>{{ $totalAfterFillingPending }}</strong>
                    </span>
                </div>
            </div>

            <!-- Cards Grid: 2 Cards (QC Pre Cuci & QC After Filling) -->
            <div class="row g-4 justify-content-center mb-4">
                @foreach ($cards as $key => $card)
                    @php
                        $themeColor = $card['badge_color'] ?? 'primary';
                    @endphp
                    <div class="col-xl-4 col-lg-5 col-md-6 col-sm-10">
                        <a href="{{ $card['route'] }}"
                            class="card card-animate text-decoration-none shadow-sm h-100 rounded border-top border-3 border-{{ $themeColor }}">
                            <div
                                class="card-body p-4 text-center d-flex flex-column align-items-center justify-content-between">
                                <div>
                                    <!-- Circular Icon -->
                                    <div class="avatar-lg mx-auto mb-3">
                                        <span
                                            class="avatar-title bg-soft-{{ $themeColor }} text-{{ $themeColor }} rounded-circle fs-48">
                                            <i class="{{ $card['icon'] }}"></i>
                                        </span>
                                    </div>

                                    <!-- Title & Subtitle -->
                                    <h4 class="fs-18 fw-bold text-body mb-1">{{ $card['title'] }}</h4>
                                    <span class="badge bg-soft-secondary text-secondary rounded-pill px-3 py-1 fs-11 mb-2">
                                        {{ $card['subtitle'] }}
                                    </span>
                                </div>

                                <div class="w-100 pt-2 border-top border-light">
                                    <!-- Counter Badge -->
                                    <div
                                        class="badge bg-soft-{{ $themeColor }} text-{{ $themeColor }} rounded-pill px-3 py-2 fs-12 mb-2">
                                        <i class="ri-information-line me-1"></i>
                                        <span>{{ $card['count'] }} {{ $card['count_label'] }}</span>
                                    </div>

                                    <!-- Click Hint -->
                                    <div
                                        class="text-muted fs-12 d-flex align-items-center justify-content-center gap-1 mt-1">
                                        <span class="fw-medium text-{{ $themeColor }}">Buka Scanner</span>
                                        <i class="ri-arrow-right-line text-{{ $themeColor }}"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
@endsection
