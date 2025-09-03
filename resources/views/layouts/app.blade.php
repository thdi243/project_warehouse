<!DOCTYPE html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable">

    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Monitoring Warehouse</title>
        <meta content="Themesbrand" name="author" />

        <script>
            (function() {
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark') {
                    document.documentElement.setAttribute('data-layout-mode', 'dark');
                } else {
                    document.documentElement.setAttribute('data-layout-mode', 'light');
                }
            })();
        </script>

        {{-- app favicon --}}
        <link rel="shortcut icon" href="{{ asset('assets/images/logo/kecap.png') }}">

        {{-- SweetAlert2 CSS --}}
        <link href="{{ asset('material/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet"
            type="text/css" />

        <!-- Layout config Js -->
        <script src="{{ asset('material/assets/js/layout.js') }}"></script>
        <!-- Bootstrap Css -->
        <link href="{{ asset('material/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="{{ asset('material/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ asset('material/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- custom Css-->
        <link href="{{ asset('material/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/libs/aos/aos.css') }}" rel="stylesheet" type="text/css" />

        <!-- jQuery should be included before DataTables -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="{{ asset('material/assets/css/datatables.min.css') }}" rel="stylesheet" type="text/css" />
        <script src="{{ asset('material/assets/js/datatables.min.js') }}"></script>

        @yield('styles')
        <style>
            .profil-avt {
                object-fit: cover;
            }

            /* Hilangkan bullet / simbol di nav-item dan sub-menu */
            .navbar-nav .nav-item,
            .navbar-nav .nav-item::before,
            .navbar-nav .nav-link::before {
                list-style: none !important;
                content: none !important;
                /* hilangkan pseudo-element */
            }

            /* Pastikan padding/indent sub-menu tetap rapi */
            .navbar-nav .nav-item ul {
                padding-left: 0;
                margin: 0;
            }


            /* Hover menu link */
            .navbar-nav .nav-link {
                transition: all 0.3s ease;
                /* transisi semua properti lebih halus */
                display: flex;
                align-items: center;
            }

            /* Hover effect umum */
            .navbar-nav .nav-link:hover {
                background-color: #4bb2f3;
                color: #fff !important;
                border-radius: 8px;
                padding-left: 1rem;
                padding-right: 1rem;
                margin: 2px 0;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                transition: all 0.2s ease-in-out;
            }

            /* Active effect untuk menu utama (level 1) */
            .navbar-nav>.nav-item>.nav-link.active {
                background-color: #4bb2f3;
                color: #fff !important;
                border-radius: 8px;
                padding-left: 1rem;
                padding-right: 1rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            /* Submenu level 2 (misal: P2H Online) → tidak ada background */
            .navbar-nav .menu-dropdown>.nav-item>.nav-link.active {
                background: transparent !important;
                color: #4bb2f3 !important;
                font-weight: 600;
                box-shadow: none !important;
            }

            /* Sub-submenu level 3 → hanya item diklik yg aktif */
            .navbar-nav .menu-dropdown .nav-sm .nav-link.active {
                background-color: #4bb2f3 !important;
                color: #fff !important;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
        </style>

    </head>

    <body class="dark">
        {{-- Begin page --}}
        <div class="layout-wrapper">
            @include('layouts.partials.topbar')

            @include('layouts.partials.sidebar')

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">
                @yield('content')

                @include('layouts.partials.footer')
            </div>
        </div>



        <!-- JAVASCRIPT -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
        <script src="{{ asset('material/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('material/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
        <script src="{{ asset('/material/assets/js/plugins.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

        <!-- Sweet Alerts js -->
        <script src="{{ asset('material/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

        <!-- Sweet alert init js-->
        <script src="{{ asset('material/assets/js/pages/sweetalerts.init.js') }}"></script>

        {{-- Chart --}}
        <script src="{{ asset('material/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('material/assets/js/highcharts.js') }}"></script>
        <!-- App js -->
        <script src="{{ asset('material/assets/libs/aos/aos.js') }}"></script>
        <script src="{{ asset('material/assets/js/pages/animation-aos.init.js') }}"></script>
        <script src="{{ asset('material/assets/js/app.js') }}"></script>

        <script>
            $(document).ready(function() {

                // Initialize AOS
                AOS.init({
                    duration: 1200,
                });

                // Logout button functionality
                $('#logoutButton').on('click', function(e) {
                    // e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You will be logged out!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, logout!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Logging out...',
                                text: 'Please wait while we process your request.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading(); // Menampilkan animasi loading
                                }
                            });

                            $.ajax({
                                url: "{{ route('logout') }}",
                                type: "POST",
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Logged out!',
                                        text: 'You have been logged out successfully.',
                                        showConfirmButton: false,
                                        timer: 1000
                                    }).then(() => {
                                        window.location.href =
                                            "{{ url('/') }}";
                                    });
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire(
                                        'Error!',
                                        'There was an error logging you out.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });

                // light dark mode
                if (!localStorage.getItem('theme')) {
                    localStorage.setItem('theme', 'light');
                }

                const savedTheme = localStorage.getItem('theme');

                // Apply theme
                applyTheme(savedTheme);
                updateThemeIcon(savedTheme === 'dark');

                // Event listener untuk button toggle
                $('#btn-darkmode').on('click', function() {
                    const currentTheme = localStorage.getItem('theme') || 'light';
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    // Apply theme
                    applyTheme(newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateThemeIcon(newTheme === 'dark');

                    // console.log('Theme changed to:', newTheme); // Debug
                });

                function applyTheme(theme) {
                    $('html').attr('data-layout-mode', theme);
                    $('body').attr('data-layout-mode', theme);
                    // console.log('Theme applied:', theme); // Debug
                }

                function updateThemeIcon(isDark) {
                    const $icon = $('#btn-darkmode i');
                    if ($icon.length) {
                        if (isDark) {
                            $icon.attr('class', 'bx bx-sun fs-22');
                        } else {
                            $icon.attr('class', 'bx bx-moon fs-22');
                        }
                        // console.log('Icon updated, isDark:', isDark); // Debug
                    }
                }
            });
        </script>

        @yield('scripts')
    </body>

</html>
