$(document).ready(function () {
    // Initialize AOS
    AOS.init({
        duration: 1200,
    });

    // --- CUSTOM PREMIUM SIDEBAR CONTROL LOGIC ---

    // Toggle sidebar collapse state (Desktop/Mobile)
    $("#topnav-hamburger-icon").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const isMobile = window.innerWidth < 992;
        if (isMobile) {
            $("html").toggleClass("sidebar-mobile-show");
        } else {
            const html = $("html");
            html.toggleClass("sidebar-collapsed");
            const isCollapsed = html.hasClass("sidebar-collapsed");
            localStorage.setItem(
                "custom-sidebar-collapsed",
                isCollapsed ? "true" : "false",
            );
        }
    });

    // Close mobile sidebar when clicking overlay
    $(document).on("click", ".custom-sidebar-overlay", function () {
        $("html").removeClass("sidebar-mobile-show");
    });

    // Close mobile sidebar on clicking any navigation link (except toggles)
    $(document).on(
        "click",
        '.custom-sidebar-nav .nav-link:not([data-bs-toggle="collapse"])',
        function () {
            if (window.innerWidth < 992) {
                $("html").removeClass("sidebar-mobile-show");
            }
        },
    );

    // Custom Accordion logic for submenus in expanded state
    $(document).on(
        "click",
        '.custom-sidebar .nav-link[data-bs-toggle="collapse"]',
        function (e) {
            e.preventDefault();
            e.stopPropagation();

            // If in collapsed mode, clicking shouldn't expand/collapse accordion style
            if ($("html").hasClass("sidebar-collapsed")) {
                return;
            }

            const $this = $(this);
            const targetSelector =
                $this.attr("href") || $this.attr("data-bs-target");
            const $target = $(targetSelector);

            if ($target.length === 0) return;

            const isOpening = !$target.is(":visible");

            // Toggle the clicked submenu with slide animation
            $target.slideToggle(250, function () {
                $this.attr("aria-expanded", isOpening ? "true" : "false");
                $target.toggleClass("show", isOpening);
                $this.toggleClass("collapsed", !isOpening);
            });

            // Accordion mode: Close other open menus
            if (isOpening) {
                $(".custom-sidebar .menu-dropdown")
                    .not($target)
                    .slideUp(250, function () {
                        $(this).removeClass("show");
                        const targetId = $(this).attr("id");
                        const parentLink = $(
                            `.custom-sidebar .nav-link[href="#${targetId}"], .custom-sidebar .nav-link[data-bs-target="#${targetId}"]`,
                        );
                        parentLink
                            .attr("aria-expanded", "false")
                            .addClass("collapsed");
                    });
            }
        },
    );

    // Csrf Token setup
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // Session expired or unauthorized AJAX request handling
    $(document).ajaxError(function (event, xhr) {
        if (xhr.status === 401 || xhr.status === 419) {
            window.location.href = "/login";
        }
    });

    // number type input agar tidak ke trigger scroll
    $(document).on("wheel", "input[type=number]", function () {
        $(this).blur();
    });

    // Pada input tipe number agar trigger keyboard numeric di mobile/tab
    $('input[type="number"]').attr("inputmode", "numeric");

    // Replace comma with dot on blur for number inputs
    $(document).on("blur", 'input[type="number"]', function () {
        $(this).val($(this).val().replace(",", "."));
    });

    // Logout button functionality
    $("#logoutButton").on("click", function (e) {
        // e.preventDefault();
        Swal.fire({
            title: "Are you sure?",
            text: "You will be logged out!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, logout!",
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Logging out...",
                    text: "Please wait while we process your request.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading(); // Menampilkan animasi loading
                    },
                });

                $.ajax({
                    url: window.AppConfig.routes.logout,
                    type: "POST",
                    data: {
                        _token: window.AppConfig.csrfToken,
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: "success",
                            title: "Logged out!",
                            text: "You have been logged out successfully.",
                            showConfirmButton: false,
                            timer: 1000,
                        }).then(() => {
                            window.location.href = window.AppConfig.baseUrl;
                        });
                    },
                    error: function (xhr, status, error) {
                        Swal.fire(
                            "Error!",
                            "There was an error logging you out.",
                            "error",
                        );
                    },
                });
            }
        });
    });

    // light dark mode
    if (!localStorage.getItem("theme")) {
        localStorage.setItem("theme", "light");
    }

    const savedTheme = localStorage.getItem("theme");

    // Apply theme
    applyTheme(savedTheme);
    updateThemeIcon(savedTheme === "dark");

    // Event listener untuk button toggle
    $("#btn-darkmode").on("click", function () {
        const currentTheme = localStorage.getItem("theme") || "light";
        const newTheme = currentTheme === "dark" ? "light" : "dark";

        // Apply theme
        applyTheme(newTheme);
        localStorage.setItem("theme", newTheme);
        updateThemeIcon(newTheme === "dark");

        // console.log('Theme changed to:', newTheme); // Debug
    });

    function applyTheme(theme) {
        $("html").attr("data-layout-mode", theme);
        $("body").attr("data-layout-mode", theme);
        // console.log('Theme applied:', theme); // Debug
    }

    function updateThemeIcon(isDark) {
        const $icon = $("#btn-darkmode i");
        if ($icon.length) {
            if (isDark) {
                $icon.attr("class", "bx bx-sun fs-22");
            } else {
                $icon.attr("class", "bx bx-moon fs-22");
            }
            // console.log('Icon updated, isDark:', isDark); // Debug
        }
    }

    // Scroll to top button
    let btn = $("#back-to-top");

    // cek scroll untuk munculin tombol
    $(window).scroll(function () {
        if ($(window).scrollTop() > 200) {
            btn.fadeIn(); // muncul
        } else {
            btn.fadeOut(); // sembunyi
        }
    });

    // klik tombol -> scroll ke atas
    btn.on("click", function () {
        $("html, body").animate(
            {
                scrollTop: 0,
            },
            "smooth",
        );
    });

    let lastNotificationId = null;

    let activeFollowUpSwalId = null;

    function showCheckerFollowUpSwal(notification) {
        if (
            !notification ||
            notification.is_read ||
            activeFollowUpSwalId === notification.id
        )
            return;

        const dismissedKey = `checker-follow-up-dismissed-${notification.id}`;
        if (sessionStorage.getItem(dismissedKey)) return;

        activeFollowUpSwalId = notification.id;

        Swal.fire({
            icon: "warning",
            title: notification.title,
            text: notification.message,
            showCancelButton: true,
            confirmButtonText: "Buka Form",
            cancelButtonText: "Nanti",
            allowOutsideClick: false,
        }).then((result) => {
            activeFollowUpSwalId = null;

            if (!result.isConfirmed) {
                sessionStorage.setItem(dismissedKey, "1");
                return;
            }

            sessionStorage.setItem(dismissedKey, "1");
            window.location.href = notification.url;
        });
    }

    window.fetchNotifications = function (showToast = false) {
        $.ajax({
            url: window.AppConfig.routes.notifications,
            method: "GET",
            dataType: "json",
            success: function (response) {
                const notifList = $("#notifList");
                const template = $("#notifTemplate .notif-item");
                const notifBadge = $("#notifBadge");

                notifList.empty();

                // Jika tidak ada notif
                if (response.length === 0) {
                    notifList.html(
                        '<p class="text-center text-muted py-3 mb-0">Tidak ada notifikasi</p>',
                    );
                    notifBadge.hide();
                    return;
                }

                // Hitung unread
                const unreadCount = response.filter((n) => !n.is_read).length;
                const checkerFollowUp = response.find(
                    (n) =>
                        !n.is_read && n.title === "Info Bongkar Muat" && n.url,
                );

                showCheckerFollowUpSwal(checkerFollowUp);

                if (unreadCount > 0) {
                    notifBadge.text(unreadCount).show();
                } else {
                    notifBadge.hide();
                }

                if (showToast && unreadCount > 0) {
                    const newestUnread = response.find((n) => !n.is_read);

                    if (
                        newestUnread &&
                        newestUnread.id !== lastNotificationId
                    ) {
                        toastr.info(newestUnread.message, newestUnread.title);
                        lastNotificationId = newestUnread.id;
                    }
                }

                // Render semua notif
                response.forEach((n) => {
                    const clone = template.clone();

                    clone.attr("data-id", n.id);
                    clone.attr("data-url", n.url);

                    clone.find(".notif-title").text(n.title);
                    clone.find(".notif-message").text(n.message);
                    clone.find(".notif-time").text(n.created_at);

                    const icon = clone.find(".notif-icon");
                    if (n.is_read) {
                        icon.removeClass().addClass(
                            "bx bx-check-circle text-success",
                        );
                        clone
                            .removeClass("bg-white fw-semibold")
                            .addClass("bg-light text-muted");
                    } else {
                        icon.removeClass().addClass("bx bx-bell text-warning");
                        clone
                            .removeClass("bg-light text-muted")
                            .addClass("bg-white fw-semibold");
                    }

                    notifList.append(clone);
                });
            },
            error: function (xhr) {
                toastr.error("Gagal memuat notifikasi");
            },
        });
    };

    $("#notifList").on("click", ".notif-item", function (e) {
        if ($(e.target).closest(".btn-delete-notif").length) return;

        const item = $(this);
        const id = item.data("id");
        const url = item.data("url");

        if (!url) return;

        if (item.hasClass("bg-light")) {
            openNotificationUrl(url);
            return;
        }

        $.ajax({
            url: window.AppConfig.routes.notificationsRead + "/" + id,
            type: "POST",
            data: {
                _token: window.AppConfig.csrfToken,
            },
            success: function () {
                item.fadeTo(200, 0.5, function () {
                    item.removeClass("bg-white fw-semibold text-dark")
                        .addClass("bg-light text-muted")
                        .css("opacity", 1);

                    item.find(".bx-bell")
                        .removeClass("bx-bell text-warning")
                        .addClass("bx-check-circle text-success");
                });

                // update badge
                const currentCount = parseInt($("#notifBadge").text()) || 0;
                const newCount = Math.max(currentCount - 1, 0);

                if (newCount === 0) $("#notifBadge").hide();
                else $("#notifBadge").text(newCount);

                // buka URL setelah efek animasi
                setTimeout(() => openNotificationUrl(url), 300);
            },
            error: function () {
                toastr.error("Gagal menandai notifikasi sebagai dibaca.");
            },
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

    $(document).on("click", ".btn-delete-notif", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const item = $(this).closest(".notif-item");
        const id = item.data("id");

        $.ajax({
            url: window.AppConfig.routes.notificationsDelete + "/" + id,
            type: "DELETE",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function () {
                item.remove();

                const count = $(".notif-item").length;
                if (count > 0) $("#notifBadge").text(count);
                else $("#notifBadge").hide();
            },
        });
    });

    $("#clearNotifBtn").on("click", function () {
        Swal.fire({
            title: "Hapus semua notifikasi?",
            text: "Tindakan ini tidak dapat dibatalkan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: window.AppConfig.routes.notificationsDeleteAll,
                type: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                success: function () {
                    // Hapus semua item dari DOM
                    $("#notifList").html(
                        '<p class="text-center text-muted py-3 mb-0">Tidak ada notifikasi baru</p>',
                    );

                    // Sembunyikan badge
                    $("#notifBadge").hide();

                    toastr.success("Semua notifikasi berhasil dihapus.");
                },
                error: function () {
                    toastr.error("Gagal menghapus semua notifikasi.");
                },
            });
        });
    });

    fetchNotifications(false);

    setInterval(() => {
        fetchNotifications(true);
    }, 180000);

    function setupRealtimeNotifications() {
        // Check if Echo is loaded from CDN and not yet initialized
        if (typeof window.Echo === "function" && window.AppConfig) {
            window.Pusher = Pusher;

            window.Echo = new window.Echo({
                broadcaster: "reverb",
                key: window.AppConfig.reverb.key,
                wsHost:
                    window.AppConfig.reverb.wsHost || window.location.hostname,
                wsPort: window.AppConfig.reverb.wsPort,
                wssPort: window.AppConfig.reverb.wssPort,
                forceTLS: window.AppConfig.reverb.forceTLS,
                enabledTransports: ["ws", "wss"],
            });
        }

        if (window.Echo && typeof window.Echo.channel === "function") {
            const currentUserId = $('meta[name="current-user-id"]').attr(
                "content",
            );
            if (!currentUserId) return;

            // console.log('connecting to channel...');

            window.Echo.channel("portal-notifications")
                .subscribed(() => {
                    console.log("SUBSCRIBED SUCCESS");
                })
                .error((err) => {
                    console.log("SUBSCRIBE ERROR:", err);
                })
                .listen(".new-notification", (payload) => {
                    // console.log('REVERB TRIGGERED:', payload);
                    // console.log('Realtime notification received (Blade):', payload);
                    // console.log('RAW PAYLOAD:', payload);
                    const userId =
                        payload.user_id ||
                        (payload.data && payload.data.user_id);
                    if (
                        userId &&
                        parseInt(userId) === parseInt(currentUserId)
                    ) {
                        // Parse notification object from payload
                        const n = {
                            id: payload.id || (payload.data && payload.data.id),
                            title:
                                payload.title ||
                                (payload.data && payload.data.title),
                            message:
                                payload.message ||
                                (payload.data && payload.data.message),
                            url:
                                payload.url ||
                                (payload.data && payload.data.url),
                            created_at:
                                payload.created_at ||
                                (payload.data && payload.data.created_at) ||
                                moment().format("DD MMMM YYYY, HH:mm"),
                            is_read:
                                payload.is_read ||
                                (payload.data && payload.data.is_read) ||
                                false,
                        };

                        // 1. Show Toast if not read and not already toasted
                        if (!n.is_read && n.id !== lastNotificationId) {
                            toastr.info(n.message, n.title);
                            lastNotificationId = n.id;
                        }

                        // 2. Trigger Checker Follow Up Swal if applicable
                        const isCheckerFollowUp =
                            !n.is_read &&
                            n.title === "Info Bongkar Muat" &&
                            n.url;
                        if (isCheckerFollowUp) {
                            showCheckerFollowUpSwal(n);
                        }

                        // 3. Build UI item and insert into UI
                        const notifList = $("#notifList");
                        const template = $("#notifTemplate .notif-item");
                        const notifBadge = $("#notifBadge");

                        // Remove empty message if present
                        notifList.find("p.text-center").remove();

                        // Check if this notification already exists in the DOM to avoid duplication
                        if (
                            notifList.find(`[data-id="${n.id}"]`).length === 0
                        ) {
                            const clone = template.clone();
                            clone.attr("data-id", n.id);
                            clone.attr("data-url", n.url);

                            clone.find(".notif-title").text(n.title);
                            clone.find(".notif-message").text(n.message);
                            clone.find(".notif-time").text(n.created_at);

                            const icon = clone.find(".notif-icon");
                            if (n.is_read) {
                                icon.removeClass().addClass(
                                    "bx bx-check-circle text-success",
                                );
                                clone
                                    .removeClass("bg-white fw-semibold")
                                    .addClass("bg-light text-muted");
                            } else {
                                icon.removeClass().addClass(
                                    "bx bx-bell text-warning",
                                );
                                clone
                                    .removeClass("bg-light text-muted")
                                    .addClass("bg-white fw-semibold");
                            }

                            // Prepend to show the newest at the top
                            notifList.prepend(clone);

                            // Update badge count
                            if (!n.is_read) {
                                const currentCount =
                                    parseInt(notifBadge.text()) || 0;
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
