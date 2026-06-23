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
            $(document).ready(function() {

                // Initialize AOS
                AOS.init({
                    duration: 1200,
                });

                // --- CUSTOM PREMIUM SIDEBAR CONTROL LOGIC ---

                // Toggle sidebar collapse state (Desktop/Mobile)
                $('#topnav-hamburger-icon').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const isMobile = window.innerWidth < 992;
                    if (isMobile) {
                        $('html').toggleClass('sidebar-mobile-show');
                    } else {
                        const html = $('html');
                        html.toggleClass('sidebar-collapsed');
                        const isCollapsed = html.hasClass('sidebar-collapsed');
                        localStorage.setItem('custom-sidebar-collapsed', isCollapsed ? 'true' : 'false');
                    }
                });

                // Close mobile sidebar when clicking overlay
                $(document).on('click', '.custom-sidebar-overlay', function() {
                    $('html').removeClass('sidebar-mobile-show');
                });

                // Close mobile sidebar on clicking any navigation link (except toggles)
                $(document).on('click', '.custom-sidebar-nav .nav-link:not([data-bs-toggle="collapse"])', function() {
                    if (window.innerWidth < 992) {
                        $('html').removeClass('sidebar-mobile-show');
                    }
                });

                // Custom Accordion logic for submenus in expanded state
                $(document).on('click', '.custom-sidebar .nav-link[data-bs-toggle="collapse"]', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // If in collapsed mode, clicking shouldn't expand/collapse accordion style
                    if ($('html').hasClass('sidebar-collapsed')) {
                        return;
                    }

                    const $this = $(this);
                    const targetSelector = $this.attr('href') || $this.attr('data-bs-target');
                    const $target = $(targetSelector);

                    if ($target.length === 0) return;

                    const isOpening = !$target.is(':visible');

                    // Toggle the clicked submenu with slide animation
                    $target.slideToggle(250, function() {
                        $this.attr('aria-expanded', isOpening ? 'true' : 'false');
                        $target.toggleClass('show', isOpening);
                        $this.toggleClass('collapsed', !isOpening);
                    });

                    // Accordion mode: Close other open menus
                    if (isOpening) {
                        $('.custom-sidebar .menu-dropdown').not($target).slideUp(250, function() {
                            $(this).removeClass('show');
                            const targetId = $(this).attr('id');
                            const parentLink = $(
                                `.custom-sidebar .nav-link[href="#${targetId}"], .custom-sidebar .nav-link[data-bs-target="#${targetId}"]`
                            );
                            parentLink.attr('aria-expanded', 'false').addClass('collapsed');
                        });
                    }
                });

                // Csrf Token setup
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $(document).ajaxError(function(event, xhr) {
                    if (xhr.status === 401) {
                        window.location.href = '/login';
                    }
                });

                // number type input agar tidak ke trigger scroll
                $(document).on('wheel', 'input[type=number]', function() {
                    $(this).blur();
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

                let lastNotificationId = null;

                let activeFollowUpSwalId = null;

                function showCheckerFollowUpSwal(notification) {
                    if (!notification || notification.is_read || activeFollowUpSwalId === notification.id) return;

                    const dismissedKey = `checker-follow-up-dismissed-${notification.id}`;
                    if (sessionStorage.getItem(dismissedKey)) return;

                    activeFollowUpSwalId = notification.id;

                    Swal.fire({
                        icon: 'warning',
                        title: notification.title,
                        text: notification.message,
                        showCancelButton: true,
                        confirmButtonText: 'Buka Form',
                        cancelButtonText: 'Nanti',
                        allowOutsideClick: false
                    }).then((result) => {
                        activeFollowUpSwalId = null;

                        if (!result.isConfirmed) {
                            sessionStorage.setItem(dismissedKey, '1');
                            return;
                        }

                        sessionStorage.setItem(dismissedKey, '1');
                        window.location.href = notification.url;
                    });
                }

                function fetchNotifications(showToast = false) {
                    $.ajax({
                        url: "{{ route('notifications') }}",
                        method: "GET",
                        dataType: "json",
                        success: function(response) {
                            const notifList = $('#notifList');
                            const template = $('#notifTemplate .notif-item');
                            const notifBadge = $('#notifBadge');

                            notifList.empty();

                            // Jika tidak ada notif
                            if (response.length === 0) {
                                notifList.html(
                                    '<p class="text-center text-muted py-3 mb-0">Tidak ada notifikasi</p>'
                                );
                                notifBadge.hide();
                                return;
                            }

                            // Hitung unread
                            const unreadCount = response.filter(n => !n.is_read).length;
                            const checkerFollowUp = response.find(n =>
                                !n.is_read &&
                                n.title === 'Info Bongkar Muat' &&
                                n.url
                            );

                            showCheckerFollowUpSwal(checkerFollowUp);

                            if (unreadCount > 0) {
                                notifBadge.text(unreadCount).show();
                            } else {
                                notifBadge.hide();
                            }

                            if (showToast && unreadCount > 0) {
                                const newestUnread = response.find(n => !n.is_read);

                                if (newestUnread && newestUnread.id !== lastNotificationId) {
                                    toastr.info(newestUnread.message, newestUnread.title);
                                    lastNotificationId = newestUnread
                                        .id;
                                }
                            }

                            // Render semua notif
                            response.forEach(n => {
                                const clone = template.clone();

                                clone.attr('data-id', n.id);
                                clone.attr('data-url', n.url);

                                clone.find('.notif-title').text(n.title);
                                clone.find('.notif-message').text(n.message);
                                clone.find('.notif-time').text(n.created_at);

                                const icon = clone.find('.notif-icon');
                                if (n.is_read) {
                                    icon.removeClass().addClass('bx bx-check-circle text-success');
                                    clone.removeClass('bg-white fw-semibold').addClass(
                                        'bg-light text-muted');
                                } else {
                                    icon.removeClass().addClass('bx bx-bell text-warning');
                                    clone.removeClass('bg-light text-muted').addClass(
                                        'bg-white fw-semibold');
                                }

                                notifList.append(clone);
                            });
                        },
                        error: function(xhr) {
                            toastr.error("Gagal memuat notifikasi");
                        }
                    });
                }

                $('#notifList').on('click', '.notif-item', function(e) {
                    if ($(e.target).closest('.btn-delete-notif').length) return;

                    const item = $(this);
                    const id = item.data('id');
                    const url = item.data('url');

                    if (!url) return;

                    if (item.hasClass('bg-light')) {
                        openNotificationUrl(url);
                        return;
                    }

                    $.ajax({
                        url: `{{ url('api/notifications/read') }}/` + id,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function() {
                            item.fadeTo(200, 0.5, function() {
                                item
                                    .removeClass('bg-white fw-semibold text-dark')
                                    .addClass('bg-light text-muted')
                                    .css('opacity', 1);

                                item.find('.bx-bell')
                                    .removeClass('bx-bell text-warning')
                                    .addClass('bx-check-circle text-success');
                            });

                            // update badge
                            const currentCount = parseInt($('#notifBadge').text()) || 0;
                            const newCount = Math.max(currentCount - 1, 0);

                            if (newCount === 0) $('#notifBadge').hide();
                            else $('#notifBadge').text(newCount);

                            // buka URL setelah efek animasi
                            setTimeout(() => openNotificationUrl(url), 300);
                        },
                        error: function() {
                            toastr.error('Gagal menandai notifikasi sebagai dibaca.');
                        }
                    });
                });

                function openNotificationUrl(url) {
                    // if (url.startsWith('http://') || url.startsWith('https://')) {
                    //     window.open(url);
                    // } else {
                    //     window.location.href = url;
                    // }

                    window.location.href = url;
                }

                $(document).on('click', '.btn-delete-notif', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const item = $(this).closest('.notif-item');
                    const id = item.data('id');

                    $.ajax({
                        url: "{{ url('notifications/delete') }}/" + id,
                        type: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            item.remove();

                            const count = $('.notif-item').length;
                            if (count > 0) $('#notifBadge').text(count);
                            else $('#notifBadge').hide();
                        }
                    });
                });

                $('#clearNotifBtn').on('click', function() {
                    Swal.fire({
                        title: "Hapus semua notifikasi?",
                        text: "Tindakan ini tidak dapat dibatalkan!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Ya, hapus",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: "{{ url('notifications/delete-all') }}",
                            type: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function() {

                                // Hapus semua item dari DOM
                                $('#notifList').html(
                                    '<p class="text-center text-muted py-3 mb-0">Tidak ada notifikasi baru</p>'
                                );

                                // Sembunyikan badge
                                $('#notifBadge').hide();

                                toastr.success('Semua notifikasi berhasil dihapus.');
                            },
                            error: function() {
                                toastr.error('Gagal menghapus semua notifikasi.');
                            }
                        });
                    });
                });

                fetchNotifications(true);

                setInterval(() => {
                    fetchNotifications(true);
                }, 180000);

                function setupRealtimeNotifications() {
                    // Check if Echo is loaded from CDN and not yet initialized
                    if (typeof window.Echo === 'function') {
                        window.Pusher = Pusher;

                        window.Echo = new window.Echo({
                            broadcaster: 'reverb',
                            key: '{{ config('broadcasting.connections.reverb.key') }}',
                            wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}' || window.location.hostname,
                            wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                            wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
                            forceTLS: '{{ config('broadcasting.connections.reverb.options.scheme', 'http') }}' === 'https',
                            enabledTransports: ['ws', 'wss'],
                        });
                    }

                    if (window.Echo && typeof window.Echo.channel === 'function') {
                        const currentUserId = $('meta[name="current-user-id"]').attr('content');
                        if (!currentUserId) return;

                        console.log('connecting to channel...');

                        window.Echo.channel('portal-notifications')
                            .subscribed(() => {
                                console.log('SUBSCRIBED SUCCESS');
                            })
                            .error((err) => {
                                console.log('SUBSCRIBE ERROR:', err);
                            })
                            .listen('.new-notification', (payload) => {
                                console.log('REVERB TRIGGERED:', payload);
                                console.log('Realtime notification received (Blade):', payload);
                                console.log('RAW PAYLOAD:', payload);
                                const userId = payload.user_id || (payload.data && payload.data.user_id);
                                if (userId && parseInt(userId) === parseInt(currentUserId)) {
                                    // Parse notification object from payload
                                    const n = {
                                        id: payload.id || (payload.data && payload.data.id),
                                        title: payload.title || (payload.data && payload.data.title),
                                        message: payload.message || (payload.data && payload.data.message),
                                        url: payload.url || (payload.data && payload.data.url),
                                        created_at: payload.created_at || (payload.data && payload.data
                                            .created_at) || moment().format('DD MMMM YYYY, HH:mm'),
                                        is_read: payload.is_read || (payload.data && payload.data.is_read) ||
                                            false
                                    };

                                    // 1. Show Toast if not read and not already toasted
                                    if (!n.is_read && n.id !== lastNotificationId) {
                                        toastr.info(n.message, n.title);
                                        lastNotificationId = n.id;
                                    }

                                    // 2. Trigger Checker Follow Up Swal if applicable
                                    const isCheckerFollowUp = !n.is_read && n.title === 'Info Bongkar Muat' && n
                                        .url;
                                    if (isCheckerFollowUp) {
                                        showCheckerFollowUpSwal(n);
                                    }

                                    // 3. Build UI item and insert into UI
                                    const notifList = $('#notifList');
                                    const template = $('#notifTemplate .notif-item');
                                    const notifBadge = $('#notifBadge');

                                    // Remove empty message if present
                                    notifList.find('p.text-center').remove();

                                    // Check if this notification already exists in the DOM to avoid duplication
                                    if (notifList.find(`[data-id="${n.id}"]`).length === 0) {
                                        const clone = template.clone();
                                        clone.attr('data-id', n.id);
                                        clone.attr('data-url', n.url);

                                        clone.find('.notif-title').text(n.title);
                                        clone.find('.notif-message').text(n.message);
                                        clone.find('.notif-time').text(n.created_at);

                                        const icon = clone.find('.notif-icon');
                                        if (n.is_read) {
                                            icon.removeClass().addClass('bx bx-check-circle text-success');
                                            clone.removeClass('bg-white fw-semibold').addClass(
                                                'bg-light text-muted');
                                        } else {
                                            icon.removeClass().addClass('bx bx-bell text-warning');
                                            clone.removeClass('bg-light text-muted').addClass(
                                                'bg-white fw-semibold');
                                        }

                                        // Prepend to show the newest at the top
                                        notifList.prepend(clone);

                                        // Update badge count
                                        if (!n.is_read) {
                                            const currentCount = parseInt(notifBadge.text()) || 0;
                                            const newCount = currentCount + 1;
                                            notifBadge.text(newCount).show();
                                        }
                                    }
                                }
                            });
                    } else {
                        setTimeout(setupRealtimeNotifications, 100);
                    }
                }
                setupRealtimeNotifications();
            });

            // window.Echo.channel('test-channel')
        </script>

        @yield('scripts')
    </body>

</html>
