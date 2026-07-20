@extends('layouts.app')

@section('title', 'Change Password')

@section('styles')
    <style>
        .change-pwd-card {
            border-radius: 12px;
            overflow: hidden;
            max-width: 550px;
            margin: auto;
        }

        .change-pwd-header {
            background: linear-gradient(135deg, #536976, #292E49);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="page-content d-flex align-items-center min-vh-100 mb-5">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-outline me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- CARD CHANGE PASSWORD -->
                    <div class="card shadow change-pwd-card border-0">

                        <!-- Header -->
                        <div class="change-pwd-header">
                            <h4 class="mb-0 text-white"><i class="mdi mdi-key-variant me-2"></i>Change Password</h4>
                            <p class="mb-0 text-white-50">Perbarui kata sandi untuk menjaga keamanan akun Anda</p>
                        </div>

                        <!-- Body -->
                        <form action="{{ route('user.profile.update-password') }}" method="POST" id="changePasswordForm">
                            @csrf
                            @method('PUT')
                            
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-semibold">Password Saat Ini <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Masukkan password saat ini">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Minimal 6 karakter">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required placeholder="Ulangi password baru">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password_confirmation">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                    </div>
                                    @error('new_password_confirmation')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Footer -->
                            <div class="card-footer bg-light p-4 d-flex justify-content-between">
                                <a href="{{ route('user.profile') }}" class="btn btn-secondary px-4">
                                    <i class="mdi mdi-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-danger px-4">
                                    <i class="mdi mdi-key-change me-2"></i>Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Password Visibility Toggle
            $('.toggle-password').on('click', function() {
                const targetId = $(this).data('target');
                const targetInput = $('#' + targetId);
                const icon = $(this).find('i');

                if (targetInput.attr('type') === 'password') {
                    targetInput.attr('type', 'text');
                    icon.removeClass('mdi-eye-outline').addClass('mdi-eye-off-outline');
                } else {
                    targetInput.attr('type', 'password');
                    icon.removeClass('mdi-eye-off-outline').addClass('mdi-eye-outline');
                }
            });
        });
    </script>
@endsection
