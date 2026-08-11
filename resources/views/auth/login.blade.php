<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Login | DWM - BAS</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="DWM - BAS — Digital Warehouse Management System" />

        <!-- Google Fonts -->
        <link
            href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Inter:wght@300;400;500;600&display=swap"
            rel="stylesheet">

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- App Assets -->
        <link rel="shortcut icon" href="{{ asset('assets/images/logo/kecap.png') }}">
        <link href="{{ asset('material/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet"
            type="text/css" />
        <link href="{{ asset('material/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

        <script src="{{ asset('material/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <style>
            *,
            *::before,
            *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-family: 'Inter', sans-serif;
                min-height: 100vh;
                overflow-x: hidden;
            }

            /* Full-page background (same as dashboard hero-bg) */
            .login-bg {
                position: fixed;
                inset: 0;
                background-image: url('{{ asset('assets/images/gudang_home.png') }}');
                background-size: cover;
                background-position: center 60%;
                background-repeat: no-repeat;
                transform: scale(1.04);
                animation: heroBgZoom 18s ease-in-out infinite alternate;
                will-change: transform;
                z-index: 0;
            }

            @keyframes heroBgZoom {
                0% {
                    transform: scale(1.04) translateX(0);
                }

                100% {
                    transform: scale(1.10) translateX(-12px);
                }
            }

            /* Overlays (identical to dashboard) */
            .login-overlay-left {
                position: fixed;
                inset: 0;
                background: linear-gradient(105deg,
                        rgba(10, 10, 10, 0.92) 0%,
                        rgba(120, 20, 10, 0.65) 38%,
                        rgba(0, 0, 0, 0.20) 65%,
                        rgba(0, 0, 0, 0.35) 100%);
                z-index: 1;
            }

            .login-overlay-bottom {
                position: fixed;
                inset: 0;
                background: linear-gradient(to top,
                        rgba(5, 5, 5, 0.85) 0%,
                        rgba(5, 5, 5, 0.00) 50%);
                z-index: 1;
            }

            /* Page wrapper */
            .login-page {
                position: relative;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 2;
                padding: 80px 16px 100px;
            }


            /* Logos top-right (glass, same as dashboard) */
            .logos-wrapper {
                position: fixed;
                top: 24px;
                right: 32px;
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 10;
                opacity: 0;
                animation: fadeIn 0.8s 0.4s ease forwards;
            }

            .logo-glass {
                background: rgba(255, 255, 255, 0.12);
                border: 1px solid rgba(255, 255, 255, 0.22);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border-radius: 12px;
                padding: 6px 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: floatLogo 4s ease-in-out infinite;
            }

            .logo-glass:nth-child(2) {
                animation-delay: 0.9s;
            }

            @keyframes floatLogo {

                0%,
                100% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-6px);
                }
            }

            /* Left headline (visible on lg+) */
            .login-headline {
                display: none;
                flex-direction: column;
                justify-content: center;
                max-width: 460px;
                padding-right: 52px;
                opacity: 0;
                animation: fadeSlideUp 0.8s 0.4s ease forwards;
            }

            @media (min-width: 992px) {
                .login-headline {
                    display: flex;
                }
            }

            .login-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: rgba(220, 53, 69, 0.18);
                border: 1px solid rgba(220, 53, 69, 0.50);
                backdrop-filter: blur(8px);
                color: #ff8c7f;
                font-family: 'Inter', sans-serif;
                font-size: 11px;
                font-weight: 600;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                padding: 5px 14px;
                border-radius: 30px;
                width: fit-content;
                margin-bottom: 20px;
            }

            .pulse-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #ff4d4d;
                animation: pulseDot 1.5s infinite;
                flex-shrink: 0;
            }

            @keyframes pulseDot {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(255, 77, 77, 0.7);
                }

                50% {
                    box-shadow: 0 0 0 6px rgba(255, 77, 77, 0);
                }
            }

            .login-headline h1 {
                font-family: 'Outfit', sans-serif;
                font-size: clamp(32px, 3.8vw, 58px);
                font-weight: 900;
                color: #ffffff;
                line-height: 1.05;
                text-transform: uppercase;
                margin-bottom: 16px;
            }

            .headline-accent {
                display: block;
                width: 56px;
                height: 5px;
                border-radius: 3px;
                background: linear-gradient(90deg, #e63946, #f4a261);
                margin-bottom: 20px;
            }

            .login-headline p {
                font-family: 'Inter', sans-serif;
                font-size: 14px;
                font-weight: 400;
                color: rgba(255, 255, 255, 0.55);
                line-height: 1.75;
                max-width: 360px;
            }


            /* Login card (glassmorphism) */
            .login-card-wrap {
                opacity: 0;
                animation: fadeSlideUp 0.8s 0.5s ease forwards;
                width: 100%;
                max-width: 420px;
            }

            .login-card {
                background: rgba(15, 15, 18, 0.72);
                border: 1px solid rgba(255, 255, 255, 0.12);
                backdrop-filter: blur(24px);
                -webkit-backdrop-filter: blur(24px);
                border-radius: 20px;
                padding: 40px 36px;
                box-shadow: 0 32px 80px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255, 255, 255, 0.06) inset;
            }

            /* Card header */
            .card-logo {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 22px;
            }

            .card-logo img {
                height: 44px;
                width: auto;
                filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.4));
            }

            .card-logo-name {
                font-family: 'Outfit', sans-serif;
                font-size: 16px;
                font-weight: 700;
                color: #fff;
                line-height: 1.2;
            }

            .card-logo-tagline {
                font-family: 'Inter', sans-serif;
                font-size: 10px;
                font-weight: 400;
                color: rgba(255, 255, 255, 0.45);
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .card-divider {
                height: 1px;
                background: linear-gradient(90deg, rgba(220, 53, 69, 0.5), rgba(244, 162, 97, 0.3), transparent);
                margin-bottom: 24px;
            }

            .card-title {
                font-family: 'Outfit', sans-serif;
                font-size: 22px;
                font-weight: 700;
                color: #fff;
                margin-bottom: 4px;
            }

            .card-subtitle {
                font-family: 'Inter', sans-serif;
                font-size: 13px;
                color: rgba(255, 255, 255, 0.45);
                margin-bottom: 28px;
            }

            /* Form */
            .form-label-custom {
                font-family: 'Inter', sans-serif;
                font-size: 11px;
                font-weight: 500;
                color: rgba(255, 255, 255, 0.55);
                letter-spacing: 0.07em;
                text-transform: uppercase;
                margin-bottom: 7px;
                display: block;
            }

            .form-control-custom {
                width: 100%;
                background: rgba(255, 255, 255, 0.06) !important;
                border: 1px solid rgba(255, 255, 255, 0.13) !important;
                border-radius: 10px !important;
                color: #fff !important;
                font-family: 'Inter', sans-serif;
                font-size: 14px;
                padding: 12px 16px;
                transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
                outline: none;
            }

            .form-control-custom::placeholder {
                color: rgba(255, 255, 255, 0.22);
            }

            .form-control-custom:focus {
                background: rgba(255, 255, 255, 0.10) !important;
                border-color: rgba(230, 57, 70, 0.60) !important;
                box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.18) !important;
            }

            .input-icon-wrap {
                position: relative;
            }

            .input-icon-wrap .form-control-custom {
                padding-right: 44px;
            }

            .btn-eye {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: rgba(255, 255, 255, 0.32);
                cursor: pointer;
                padding: 4px;
                font-size: 16px;
                transition: color 0.2s;
            }

            .btn-eye:hover {
                color: rgba(255, 255, 255, 0.75);
            }

            .mb-form {
                margin-bottom: 20px;
            }

            /* Submit button */
            .btn-signin {
                width: 100%;
                padding: 13px;
                border: none;
                border-radius: 10px;
                background: linear-gradient(135deg, #c0392b 0%, #e67e22 100%);
                color: #fff;
                font-family: 'Outfit', sans-serif;
                font-size: 15px;
                font-weight: 700;
                letter-spacing: 0.04em;
                cursor: pointer;
                transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
                box-shadow: 0 6px 24px rgba(192, 57, 43, 0.40);
                margin-top: 8px;
                position: relative;
                overflow: hidden;
            }

            .btn-signin:hover {
                opacity: 0.92;
                transform: translateY(-1px);
                box-shadow: 0 10px 32px rgba(192, 57, 43, 0.55);
            }

            .btn-signin:active {
                transform: translateY(0);
                box-shadow: 0 4px 14px rgba(192, 57, 43, 0.35);
            }

            /* Footer note */
            .card-footer-note {
                margin-top: 22px;
                text-align: center;
                font-family: 'Inter', sans-serif;
                font-size: 11px;
                color: rgba(255, 255, 255, 0.25);
            }

            /* Shared animations */
            @keyframes fadeSlideUp {
                from {
                    opacity: 0;
                    transform: translateY(22px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }



            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            /* Responsive */
            @media (max-width: 767px) {
                .logos-wrapper {
                    right: 16px;
                    top: 16px;
                }

                .login-card {
                    padding: 30px 22px;
                }
            }
        </style>
    </head>

    <body>

        <!-- Background layers (same as dashboard) -->
        <div class="login-bg"></div>
        <div class="login-overlay-left"></div>
        <div class="login-overlay-bottom"></div>

        <!-- Logos top-right (glass pills) -->
        <div class="logos-wrapper">
            <div class="logo-glass">
                <img src="{{ asset('assets/images/logo/wings.png') }}" alt="Wings" style="height:28px;width:auto;">
            </div>
            <div class="logo-glass">
                <img src="{{ asset('assets/images/logo/logo-no-teks.png') }}" alt="BAS"
                    style="height:28px;width:auto;">
            </div>
        </div>

        <!-- Main page -->
        <div class="login-page">
            <div class="d-flex align-items-center justify-content-center w-100"
                style="gap:0; max-width:960px; margin:0 auto;">

                <!-- Left headline (desktop only) -->
                {{-- <div class="login-headline">
                    <h1>Digital<br>Warehouse<br>Management</h1>
                    <span class="headline-accent"></span>
                    <p>Pantau aktivitas gudang secara real-time. Kelola stok, inbound, outbound, dan laporan operasional
                        dalam satu platform terintegrasi.</p>
                </div> --}}

                <!-- Login card -->
                <div class="login-card-wrap">
                    <div class="login-card">

                        <!-- Logo + Name -->
                        <div class="card-logo d-flex justify-content-center align-items-center">
                            <img src="{{ asset('assets/images/logo/logo-no-teks.png') }}" alt="PT Bumi Alam Segar">
                            <div>
                                <div class="card-logo-name">PT Bumi Alam Segar</div>
                            </div>
                        </div>

                        <div class="card-divider"></div>

                        <div class="card-title">Selamat Datang</div>
                        <div class="card-subtitle">Masuk ke akun Anda untuk melanjutkan</div>

                        <!-- Form -->
                        <form id="loginForm">
                            @csrf
                            <div class="mb-form">
                                <label for="username" class="form-label-custom">Username / NIK</label>
                                <input type="text" class="form-control-custom" id="username" name="username"
                                    required placeholder="Masukkan username atau NIK" autocomplete="username">
                            </div>

                            <div class="mb-form">
                                <label for="password" class="form-label-custom">Password</label>
                                <div class="input-icon-wrap">
                                    <input type="password" class="form-control-custom password-input" id="password"
                                        name="password" required placeholder="••••••••" autocomplete="current-password">
                                    <button type="button" class="btn-eye" id="password-addon">
                                        <i class="ri-eye-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn-signin" id="btnSignIn">
                                Sign In &nbsp;&#8594;
                            </button>
                        </form>

                        <div class="card-footer-note">
                            &copy; {{ date('Y') }} PT Bumi Alam Segar &middot; All rights reserved
                        </div>

                    </div>
                </div>

            </div>
        </div>


        <!-- JAVASCRIPT -->
        <script src="{{ asset('material/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('material/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
        <script src="{{ asset('material/assets/js/pages/password-addon.init.js') }}"></script>

        <script>
            // Password toggle
            document.getElementById('password-addon').addEventListener('click', function() {
                const input = document.getElementById('password');
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('ri-eye-fill', 'ri-eye-off-fill');
                } else {
                    input.type = 'password';
                    icon.classList.replace('ri-eye-off-fill', 'ri-eye-fill');
                }
            });

            // Login form
            $(document).ready(function() {
                $('#loginForm').submit(function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('signin') }}",
                        method: "POST",
                        data: {
                            username: $('#username').val(),
                            password: $('#password').val(),
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Login Berhasil!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = response.redirect;
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Login Gagal!',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            const msg = xhr.responseJSON ? xhr.responseJSON.message :
                                'Terjadi kesalahan pada server.';
                            let title = 'Terjadi Kesalahan!';
                            if (xhr.status === 401) title = 'Unauthorized!';
                            if (xhr.status === 403) title = 'Akses Ditolak!';
                            Swal.fire({
                                icon: 'error',
                                title: title,
                                text: msg
                            });
                        }
                    });
                });
            });
        </script>

    </body>

</html>
