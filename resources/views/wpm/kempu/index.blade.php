@extends('layouts.app')

@section('title', '| Kempu WPM')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Breadcrumb Header -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Operasional Kempu WPM</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="#">WPM</a></li>
                                <li class="breadcrumb-item active">Kempu</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-2 rounded-pill bg-soft-primary text-primary fw-semibold fs-12">
                    <i class="bx bx-package"></i> Warehouse Packaging Material (WPM)
                </div>
                <h3 class="fw-bold mb-1 text-body">Siklus Pemindaian Kempu WPM</h3>
                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                    <span class="badge bg-soft-primary text-primary px-3 py-2 fs-12 border border-primary-subtle">
                        <i class="ri-building-line me-1"></i> Total Kempu di Area WPM:
                        <strong>{{ $totalWpm }}</strong>
                    </span>
                </div>
            </div>

            <!-- Cards Grid: GR Kempu + Cards WPM -->
            <div class="row g-4 justify-content-center mb-4">
                <!-- GR Kempu -->
                <div class="col-xl-4 col-lg-5 col-md-6 col-sm-10">
                    <a href="{{ route('kempu.master.index') }}" class="card card-animate text-decoration-none shadow-sm h-100">
                        <div class="card-body p-4 text-center d-flex flex-column align-items-center justify-content-center">
                            <!-- Circular Icon -->
                            <div class="avatar-md mx-auto mb-3">
                                <span class="avatar-title bg-soft-info text-info rounded-circle fs-24">
                                    <i class="ri-qr-scan-2-line"></i>
                                </span>
                            </div>

                            <!-- Title -->
                            <h5 class="fs-16 fw-bold text-body mb-3">GR Kempu</h5>

                            <!-- Counter Badge -->
                            <div class="badge bg-soft-info text-info rounded-pill px-3 py-2 fs-12 mb-2">
                                <i class="ri-time-line me-1"></i>
                                <span>GR kempu</span>
                            </div>

                            <!-- Click Hint -->
                            <div class="text-muted fs-11 d-flex align-items-center justify-content-center gap-1">
                                <span>Klik untuk GR Kempu</span>
                                <i class="ri-arrow-right-line"></i>
                            </div>
                        </div>
                    </a>
                </div>

                @php
                    $colorMap = [
                        'teal'      => 'success',
                        'purple'    => 'primary',
                        'primary'   => 'primary',
                        'success'   => 'success',
                        'warning'   => 'warning',
                        'info'      => 'info',
                        'secondary' => 'secondary',
                        'danger'    => 'danger',
                    ];
                @endphp

                @foreach ($cards as $key => $card)
                    @php
                        $themeColor = $colorMap[$card['badge_color'] ?? 'primary'] ?? 'primary';
                    @endphp
                    <div class="col-xl-4 col-lg-5 col-md-6 col-sm-10">
                        <a href="{{ route('wpm.kempu.scan', $key) }}" class="card card-animate text-decoration-none shadow-sm h-100">
                            <div class="card-body p-4 text-center d-flex flex-column align-items-center justify-content-center">
                                <!-- Circular Icon -->
                                <div class="avatar-md mx-auto mb-3">
                                    <span class="avatar-title bg-soft-{{ $themeColor }} text-{{ $themeColor }} rounded-circle fs-24">
                                        <i class="{{ $card['icon'] }}"></i>
                                    </span>
                                </div>

                                <!-- Title -->
                                <h5 class="fs-16 fw-bold text-body mb-3">{{ $card['title'] }}</h5>

                                <!-- Counter Badge -->
                                <div class="badge bg-soft-{{ $themeColor }} text-{{ $themeColor }} rounded-pill px-3 py-2 fs-12 mb-2">
                                    <i class="ri-time-line me-1"></i>
                                    <span>{{ $card['count'] }} kempu</span>
                                </div>

                                <!-- Click Hint -->
                                <div class="text-muted fs-11 d-flex align-items-center justify-content-center gap-1">
                                    <span>Klik untuk scan</span>
                                    <i class="ri-arrow-right-line"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
@endsection
