<!DOCTYPE html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable" data-layout-mode="dark">

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
        <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('material/assets/libs/aos/aos.css') }}" rel="stylesheet" type="text/css" />

        <!-- jQuery should be included before DataTables -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="{{ asset('material/assets/css/datatables.min.css') }}" rel="stylesheet" type="text/css" />
        <script src="{{ asset('material/assets/js/datatables.min.js') }}"></script>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
            rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">


        {{-- Jquery UI --}}
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
        {{-- @vite(['resources/js/app.js']) --}}
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}"></script>

        @yield('styles')

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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script><!-- Signature Pad -->
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>

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

                // Csrf Token setup
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
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

                // Scroll to top button
                let btn = $("#back-to-top");

                // cek scroll untuk munculin tombol
                $(window).scroll(function() {
                    if ($(window).scrollTop() > 200) {
                        btn.fadeIn(); // muncul
                    } else {
                        btn.fadeOut(); // sembunyi
                    }
                });

                // klik tombol -> scroll ke atas
                btn.on("click", function() {
                    $("html, body").animate({
                        scrollTop: 0
                    }, "smooth");
                });

                // Notifikasi
                function fetchNotifications() {
                    $.ajax({
                        url: "{{ route('notifications') }}",
                        method: "GET",
                        dataType: "json",
                        success: function(response) {
                            const notifList = $('#notifList');
                            const notifBadge = $('#notifBadge');

                            notifList.empty();

                            if (response.length === 0) {
                                notifList.html(
                                    '<p class="text-center text-muted py-3 mb-0">Tidak ada notifikasi</p>'
                                );
                                notifBadge.hide();
                                return;
                            }

                            const unread = response.filter(n => !n.is_read);
                            if (unread.length > 0) notifBadge.text(unread.length).show();
                            else notifBadge.hide();

                            response.forEach(n => {
                                const item = $(`
                                    <a href="${n.url}" 
                                    class="list-group-item list-group-item-action notif-item d-flex align-items-start ${n.is_read ? 'bg-light text-muted' : 'bg-white'}"
                                    data-id="${n.id}">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 ${n.is_read ? '' : 'fw-semibold'}">
                                                ${n.title}
                                            </h6>
                                            <p class="mb-1 small">${n.message}</p>
                                            <small class="text-muted">${n.created_at}</small>
                                        </div>
                                        <div class="ms-2">
                                            ${n.is_read 
                                                ? '<i class="bx bx-check-circle text-success fs-5"></i>' 
                                                : '<i class="bx bx-bell text-warning fs-5"></i>'}
                                        </div>
                                    </a>
                                `);

                                notifList.append(item);

                                if (!n.is_read) {
                                    toastr.info(n.message, n.title);
                                }
                            });

                        },
                        error: function() {
                            console.error("Gagal memuat notifikasi");
                        }
                    });
                }

                window.Echo.channel('portal-notifications')
                    .listen('.new-notification', (e) => {
                        toastr.info(e.data.message, e.data.title);

                        const notifList = $('#notifList');
                        const notifBadge = $('#notifBadge');

                        const item = $(`
                            <a href="${e.data.url}" 
                            class="list-group-item list-group-item-action notif-item d-flex align-items-start bg-white"
                            data-id="temp-${Date.now()}">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">${e.data.title}</h6>
                                    <p class="mb-1 small">${e.data.message}</p>
                                    <small class="text-muted">Baru saja</small>
                                </div>
                                <div class="ms-2">
                                    <i class="bx bx-bell text-warning fs-5"></i>
                                </div>
                            </a>
                        `);

                        notifList.prepend(item);
                        notifBadge.text(parseInt(notifBadge.text() || 0) + 1).show();
                    });


                $('#notifList').on('click', '.notif-item', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    const url = $(this).attr('href');
                    const item = $(this);

                    // 🔹 Kalau tidak ada ID numerik (contohnya 'barang_baru_warning'), langsung buka URL
                    if (!id || !/^\d+$/.test(id)) {
                        window.location.href = url;
                        return;
                    }

                    // 🔹 Kalau sudah dibaca, langsung buka halaman
                    if (item.hasClass('bg-light')) {
                        window.location.href = url;
                        return;
                    }

                    // 🔹 Kalau belum dibaca dan punya ID valid, tandai read
                    $.ajax({
                        url: `{{ url('api/notifications/read') }}/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            // Animasi lembut biar halus
                            item.fadeTo(200, 0.5, function() {
                                item
                                    .removeClass('bg-white fw-semibold text-dark')
                                    .addClass('bg-light text-muted')
                                    .css('opacity', 1);

                                item.find('.bx-bell')
                                    .removeClass('bx-bell text-warning')
                                    .addClass('bx-check-circle text-success');
                            });

                            // Update badge counter
                            const currentCount = parseInt($('#notifBadge').text()) || 0;
                            const newCount = Math.max(currentCount - 1, 0);
                            if (newCount === 0) $('#notifBadge').hide();
                            else $('#notifBadge').text(newCount);

                            setTimeout(() => {
                                window.location.href = url;
                            }, 300);
                        },
                        error: function() {
                            toastr.error('Gagal menandai notifikasi sebagai dibaca.');
                        }
                    });
                });

                fetchNotifications();

                setInterval(fetchNotifications, 180000);
            });
        </script>

        @yield('scripts')
    </body>

</html>
