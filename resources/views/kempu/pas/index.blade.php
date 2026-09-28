@extends('layouts.app')

@section('title', '| Kempu Warehouse PAS')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Breadcrumb Header -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Operasional Kempu Warehouse PAS</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="#">Warehouse PAS</a></li>
                                <li class="breadcrumb-item active">Kempu</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="text-center mb-4">
                <div
                    class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-2 rounded-pill bg-soft-primary text-primary fw-semibold fs-12">
                    <i class="ri-store-2-line"></i> Warehouse PAS (PT PAS)
                </div>
                <h3 class="fw-bold mb-1 text-body">Siklus Pemindaian Kempu Warehouse PAS</h3>
                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                    <span class="badge bg-soft-primary text-primary px-3 py-2 fs-12 border border-primary-subtle">
                        <i class="ri-building-line me-1"></i> Total Kempu di Area PAS:
                        <strong>{{ $totalPas }}</strong>
                    </span>
                </div>
            </div>

            <!-- Cards Grid: 2 Cards for Warehouse PAS -->
            @php
                $colorMap = [
                    'teal' => 'success',
                    'purple' => 'primary',
                    'primary' => 'primary',
                    'success' => 'success',
                    'warning' => 'warning',
                    'info' => 'info',
                    'secondary' => 'secondary',
                    'danger' => 'danger',
                ];
            @endphp

            <div class="row g-4 justify-content-center mb-4">
                @foreach ($cards as $key => $card)
                    @php
                        $themeColor = $colorMap[$card['badge_color'] ?? 'primary'] ?? 'primary';
                    @endphp
                    <div class="col-xl-5 col-lg-6 col-md-6 col-sm-10">
                        <a href="{{ route('kempu.pas.scan', $key) }}"
                            class="card card-animate text-decoration-none shadow-sm h-100 rounded border-0 transition-all hover-shadow">
                            <div
                                class="card-body p-4 text-center d-flex flex-column align-items-center justify-content-center">
                                <!-- Circular Icon -->
                                <div class="avatar-lg mx-auto mb-3">
                                    <span
                                        class="avatar-title bg-soft-{{ $themeColor }} text-{{ $themeColor }} rounded-circle fs-48 shadow-sm">
                                        <i class="{{ $card['icon'] }}"></i>
                                    </span>
                                </div>

                                <!-- Title -->
                                <h4 class="fs-18 fw-bold text-body mb-2">{{ $card['title'] }}</h4>

                                <!-- Counter Badge -->
                                <div
                                    class="badge bg-soft-{{ $themeColor }} text-{{ $themeColor }} rounded-pill px-3 py-2 fs-13 mb-3 border border-{{ $themeColor }}-subtle">
                                    <i class="ri-time-line me-1"></i>
                                    <span><strong>{{ $card['count'] }}</strong> kempu saat ini</span>
                                </div>

                                <!-- Action Button Style Hint -->
                                <div class="text-muted fs-12 d-flex align-items-center justify-content-center gap-1 mt-1">
                                    <span>{{ $card['btn_text'] }}</span>
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
