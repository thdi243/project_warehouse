@extends('layouts.app')

@section('title', 'Profile User')

@section('styles')
    <style>
        .profile-card {
            border-radius: 12px;
            overflow: hidden;
            max-width: 650px;
            margin: auto;
        }

        .profile-header {
            background: linear-gradient(135deg, #536976, #292E49);
            padding: 50px 20px 70px;
            text-align: center;
            color: white;
            position: relative;
        }

        .profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
            position: absolute;
            left: 50%;
            bottom: -55px;
            transform: translateX(-50%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .info-label {
            font-weight: 600;
        }

        .info-value {
            font-size: 15px;
        }

        .signature-container {
            position: relative;
            width: 100%;
        }

        .signature-container canvas {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .signature-container {
            aspect-ratio: 4 / 1;
        }

        @container (max-width: 768px)

            {
            .signature-container {
                aspect-ratio: 3 / 1;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content d-flex align-items-center min-vh-100 mb-5">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-outline me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- CARD PROFILE -->
                    <div class="card shadow profile-card border-0">

                        <!-- Header -->
                        <div class="profile-header">
                            <h3 class="mb-1 text-capitalize text-white">{{ $user->nama_lengkap ?? Auth::user()->username }}</h3>
                            <span class="badge bg-light text-dark">{{ ucwords(str_replace('_', ' ', $user->jabatan)) }}</span>
                            <img src="{{ $user->image_url }}" class="profile-avatar" alt="User">
                        </div>

                        <!-- Body -->
                        <div class="card-body mt-5 px-5">

                            <div class="row mb-3 pb-2 border-bottom border-light">
                                <div class="col-sm-4 info-label text-muted">Email:</div>
                                <div class="col-sm-8 info-value fw-medium">{{ $user->email }}</div>
                            </div>

                            <div class="row mb-3 pb-2 border-bottom border-light">
                                <div class="col-sm-4 info-label text-muted">NIK:</div>
                                <div class="col-sm-8 info-value fw-medium">{{ $user->nik }}</div>
                            </div>

                            <div class="row mb-3 pb-2 border-bottom border-light">
                                <div class="col-sm-4 info-label text-muted">Departemen:</div>
                                <div class="col-sm-8 info-value fw-medium">{{ ucfirst($user->departemen) }}</div>
                            </div>

                            <div class="row mb-3 pb-2 border-bottom border-light">
                                <div class="col-sm-4 info-label text-muted">Bagian:</div>
                                <div class="col-sm-8 info-value fw-medium text-capitalize">{{ str_replace('_', ' ', $user->bagian) }}</div>
                            </div>

                            @if (Auth::user()->id == $user->id)
                                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4 pt-3">
                                    <a href="{{ route('user.profile.change-password') }}" class="btn btn-outline-danger px-4">
                                        <i class="mdi mdi-key-change me-2"></i> Change Password
                                    </a>
                                    <a href="{{ route('user.profile.edit') }}" class="btn btn-primary px-4">
                                        <i class="mdi mdi-account-edit-outline me-2"></i> Edit Profile
                                    </a>
                                </div>
                            @endif

                        </div>

                    </div>
                    <!-- END CARD -->

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
