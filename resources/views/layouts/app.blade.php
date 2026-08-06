<!DOCTYPE html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="@yield('sidebar-size', 'lg')"
    data-sidebar-image="none" data-preloader="disable" data-layout-mode="dark">

    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Warehouse @yield('title')</title>
        <meta content="Themesbrand" name="author" />
        <meta name="current-user-id" content="{{ Auth::id() }}">

        <script>
            (function() {
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark') {
                    document.documentElement.setAttribute('data-layout-mode', 'dark');
                } else {
                    document.documentElement.setAttribute('data-layout-mode', 'light');
                }

                // Restore sidebar collapsed status early to prevent flashing layout shifts
                if (localStorage.getItem('custom-sidebar-collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
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
        <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/libs/aos/aos.css') }}" rel="stylesheet" type="text/css" />

        <!-- jQuery should be included before DataTables -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="{{ asset('material/assets/css/datatables.min.css') }}" rel="stylesheet" type="text/css" />
        <script src="{{ asset('material/assets/js/datatables.min.js') }}"></script>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
            rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <link href="https://cdn.jsdelivr.net/gh/tofsjonas/sortable@latest/sortable-base.min.css" rel="stylesheet">

        {{-- Jquery UI --}}
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

        <!-- custom CSS overrides to bypass cache -->
        <style>
            /* Active / Expanded level 1 menu links (only on the parent link itself) */
            .navbar-menu .navbar-nav .nav-item a.nav-link.menu-link.active,
            .navbar-menu .navbar-nav .nav-item:has(.menu-dropdown .active)>a.nav-link.menu-link,
            .navbar-menu .navbar-nav .nav-item a.nav-link.menu-link[aria-expanded="true"] {
                background: rgba(192, 57, 43, 0.1) !important;
                color: #c0392b !important;
                border-left: 4px solid #c0392b !important;
                border-radius: 6px !important;
            }

            /* Ensure active parent icons are also orange */
            .navbar-menu .navbar-nav .nav-item a.nav-link.menu-link.active i,
            .navbar-menu .navbar-nav .nav-item:has(.menu-dropdown .active)>a.nav-link.menu-link i,
            .navbar-menu .navbar-nav .nav-item a.nav-link.menu-link[aria-expanded="true"] i {
                color: #c0392b !important;
            }

            /* Submenu active items - soft orange background for active child */
            .navbar-menu .navbar-nav .menu-dropdown .nav-link.active {
                background: rgba(192, 57, 43, 0.1) !important;
                color: #c0392b !important;
                font-weight: 600 !important;
                border-radius: 6px !important;
            }

            /* Ensure nested active submenu collapses also have orange text */
            .navbar-menu .navbar-nav .menu-dropdown .nav-link[aria-expanded="true"] {
                color: #c0392b !important;
                font-weight: 600 !important;
            }
        </style>

        @yield('styles')

        <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
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

                {{-- Btn click to up --}}
                <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
                    <i class="ri-arrow-up-line "></i>
                </button>

                @include('layouts.partials.footer')
            </div>
        </div>



        <!-- JAVASCRIPT -->
        {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script> --}}
        <script src="{{ asset('material/assets/libs/moment/min/moment.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="{{ asset('material/assets/libs/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('material/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
        <script src="{{ asset('/material/assets/js/plugins.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

        <!-- Sweet Alerts js -->
        <script src="{{ asset('material/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

        {{-- Custom Tambahan --}}
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/gh/tofsjonas/sortable@latest/sortable.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
            window.AppConfig = {
                routes: {
                    notifications: "{{ route('notifications') }}",
                    logout: "{{ route('logout') }}",
                    notificationsDeleteAll: "{{ url('notifications/delete-all') }}",
                    notificationsDelete: "{{ url('notifications/delete') }}",
                    notificationsRead: "{{ url('api/notifications/read') }}"
                },
                reverb: {
                    key: "{{ config('broadcasting.connections.reverb.key') }}",
                    wsHost: "{{ config('broadcasting.connections.reverb.options.host') }}",
                    wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                    wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                    forceTLS: {{ config('broadcasting.connections.reverb.options.scheme', 'http') === 'https' ? 'true' : 'false' }}
                },
                csrfToken: "{{ csrf_token() }}",
                baseUrl: "{{ url('/') }}"
            };
        </script>

        <script src="{{ asset('js/app.js') }}"></script>

        @yield('scripts')
    </body>

</html>
