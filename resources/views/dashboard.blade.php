@extends('layouts.app')

@section('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Inter:wght@300;400;500;600&display=swap');

        .home-page-content {
            margin-bottom: 20px;
            overflow: hidden;
        }

        .hero-section {
            position: relative;
            width: 100%;
            /* 100vh minus velzon topbar (70px) */
            height: calc(100vh - 70px);
            min-height: 500px;
            overflow: hidden;
            font-family: 'Outfit', sans-serif;
        }

        /* Background image */
        .hero-bg {
            position: absolute;
            inset: 0;
            background-image: url('{{ asset('assets/images/gudang_home.png') }}');
            background-size: cover;
            background-position: center 60%;
            background-repeat: no-repeat;
            transform: scale(1.04);
            animation: heroZoom 18s ease-in-out infinite alternate;
            will-change: transform;
        }

        @keyframes heroZoom {
            0% {
                transform: scale(1.04) translateX(0);
            }

            100% {
                transform: scale(1.10) translateX(-12px);
            }
        }

        /* Left dark reddish overlay */
        .hero-overlay-left {
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg,
                    rgba(10, 10, 10, 0.88) 0%,
                    rgba(120, 20, 10, 0.60) 38%,
                    rgba(0, 0, 0, 0.15) 65%,
                    rgba(0, 0, 0, 0.30) 100%);
        }

        /* Bottom vignette */
        .hero-overlay-bottom {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top,
                    rgba(5, 5, 5, 0.80) 0%,
                    rgba(5, 5, 5, 0.00) 45%);
        }

        .logos-wrapper {
            position: absolute;
            top: 28px;
            right: 5%;
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 20;
            opacity: 0;
            animation: fadeIn 0.8s 0.4s ease forwards;
        }

        .logo-glass {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 7px 11px;
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
                transform: translateY(-7px);
            }
        }

        /* ──────────────────────────────────────────────
                                                                                                                   Content
                                                                                                                ────────────────────────────────────────────── */
        .hero-content {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 6%;
            z-index: 10;
        }

        /* Badge */
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(220, 53, 69, 0.18);
            border: 1px solid rgba(220, 53, 69, 0.50);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #ff8c7f;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 30px;
            width: fit-content;
            margin-bottom: 18px;
            opacity: 0;
            animation: fadeSlideUp 0.7s 0.3s ease forwards;
        }

        .hero-badge .pulse-dot {
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

        /* Welcome */
        .hero-welcome {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(14px, 2vw, 22px);
            font-weight: 300;
            color: rgba(255, 255, 255, 0.72);
            letter-spacing: 0.06em;
            margin-bottom: 2px;
            opacity: 0;
            animation: fadeSlideUp 0.7s 0.5s ease forwards;
        }

        /* Name */
        .hero-name {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(30px, 5.5vw, 70px);
            font-weight: 900;
            color: #ffffff;
            line-height: 1.0;
            text-transform: uppercase;
            margin-bottom: 18px;
            word-break: break-word;
            max-width: 680px;
            opacity: 0;
            animation: fadeSlideUp 0.7s 0.65s ease forwards;
        }

        /* Accent line */
        .hero-name-accent {
            display: block;
            width: 64px;
            height: 5px;
            border-radius: 3px;
            background: linear-gradient(90deg, #e63946, #f4a261);
            margin-bottom: 22px;
            opacity: 0;
            animation: fadeSlideUp 0.7s 0.80s ease forwards;
        }

        /* Tagline */
        .hero-tagline {
            font-family: 'Inter', sans-serif;
            font-size: clamp(12px, 1.2vw, 15px);
            font-weight: 400;
            color: rgba(255, 255, 255, 0.58);
            max-width: 400px;
            line-height: 1.75;
            margin-bottom: 32px;
            opacity: 0;
            animation: fadeSlideUp 0.7s 0.95s ease forwards;
        }

        .hero-stats {
            position: absolute;
            bottom: 17%;
            left: 6%;
            right: 6%;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            z-index: 10;
            opacity: 0;
            animation: fadeSlideUp 0.7s 1.30s ease forwards;
        }

        .stat-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 12px;
            padding: 10px 16px;
            transition: background 0.25s;
        }

        .stat-pill:hover {
            background: rgba(255, 255, 255, 0.14);
        }

        .stat-pill-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: linear-gradient(135deg, #c0392b, #e67e22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: #fff;
            flex-shrink: 0;
        }

        .stat-pill-label {
            font-family: 'Inter', sans-serif;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.50);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .stat-pill-value {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
        }



        /* ──────────────────────────────────────────────
                                                                                                                   Shared animations
                                                                                                                ────────────────────────────────────────────── */
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

        /* ──────────────────────────────────────────────
                                                                                                                   Responsive tweaks
                                                                                                                   Velzon hides sidebar on ≤ 767px (margin-left: 0)
                                                                                                                   Topbar stays at 70px on all sizes
                                                                                                                ────────────────────────────────────────────── */
        @media (max-width: 992px) {
            .hero-name {
                font-size: clamp(26px, 5vw, 52px);
            }

            .hero-tagline {
                max-width: 340px;
            }

            .logos-wrapper img {
                height: 28px !important;
            }
        }

        @media (max-width: 767px) {

            /* On mobile Velzon footer/footer disappears, topbar shrinks slightly */
            .hero-section {
                height: calc(100vh - 70px);
            }

            .logos-wrapper {
                right: 3%;
                gap: 10px;
            }

            .hero-content {
                padding: 0 5%;
            }

            .hero-stats {
                left: 5%;
                right: 5%;
                bottom: 48px;
                gap: 8px;
            }

            .stat-pill {
                padding: 8px 12px;
                gap: 8px;
            }

            .stat-pill-icon {
                width: 28px;
                height: 28px;
                font-size: 13px;
                border-radius: 7px;
            }

            .stat-pill-value {
                font-size: 12px;
            }

            .stat-pill-label {
                font-size: 9px;
            }
        }

        @media (max-width: 480px) {
            .hero-name {
                font-size: clamp(24px, 8vw, 38px);
            }

            .hero-tagline {
                display: none;
            }

            .logos-wrapper {
                top: 16px;
            }

            .hero-badge {
                font-size: 10px;
                padding: 4px 12px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content home-page-content">
        <div class="container-fluid">
            <section class="hero-section">

                {{-- Background --}}
                <div class="hero-bg"></div>

                {{-- Overlays --}}
                <div class="hero-overlay-left"></div>
                <div class="hero-overlay-bottom"></div>

                {{-- Logos top-right --}}
                <div class="logos-wrapper">
                    <div class="logo-glass">
                        <img src="{{ asset('assets/images/logo/wings.png') }}" alt="Wings" style="height:32px;width:auto;">
                    </div>
                    <div class="logo-glass">
                        <img src="{{ asset('assets/images/logo/logo-no-teks.png') }}" alt="BAS"
                            style="height:32px;width:auto;">
                    </div>
                </div>

                {{-- Main content --}}
                <div class="hero-content">

                    <p class="hero-welcome">Selamat datang,</p>
                    <h1 class="hero-name">
                        {{ Auth::user()->nama_lengkap ?? Auth::user()->username }}
                    </h1>
                    <span class="hero-name-accent"></span>

                    {{-- <p class="hero-tagline">
                        Pantau aktivitas gudang secara real-time. Kelola stok,&nbsp;inbound, outbound, dan laporan
                        operasional dalam satu platform terintegrasi.
                    </p> --}}

                </div>

                {{-- Stat pills --}}
                <div class="hero-stats">
                    <div class="stat-pill">
                        <div class="stat-pill-icon"><i class="mdi mdi-warehouse"></i></div>
                        <div>
                            <div class="stat-pill-label">Gudang</div>
                            <div class="stat-pill-value">PT Bumi Alam Segar</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-pill-icon"><i class="mdi mdi-calendar-today"></i></div>
                        <div>
                            <div class="stat-pill-label">Tanggal</div>
                            <div class="stat-pill-value" id="heroDate">–</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-pill-icon"><i class="mdi mdi-clock-outline"></i></div>
                        <div>
                            <div class="stat-pill-label">Waktu</div>
                            <div class="stat-pill-value" id="heroClock">–</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-pill-icon"><i class="mdi mdi-account-outline"></i></div>
                        <div>
                            <div class="stat-pill-label">Jabatan</div>
                            <div class="stat-pill-value">{{ ucwords(Auth::user()->jabatan ?? 'User') }}</div>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Live clock
        function updateClock() {
            const now = new Date();
            const locale = 'id-ID';
            document.getElementById('heroDate').textContent = now.toLocaleDateString(locale, {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
            document.getElementById('heroClock').textContent = now.toLocaleTimeString(locale, {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        updateClock();
        setInterval(updateClock, 1000);

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
