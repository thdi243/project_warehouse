<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex align-items-center">
                <button type="button"
                    class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <div class="title flex-grow-1">
                    <h5 class="mb-0 fw-bold d-none d-md-block">Digitalization Warehouse Management</h5>
                    <h5 class="mb-0 fw-bold d-block d-md-none fst-italic">DWM</h5>
                </div>

                <!-- Spacer biar kanan & kiri balance -->
                <div style="width:40px;"></div>
            </div>

            <div class="d-flex align-items-center">

                <!-- Notifikasi -->
                <div class="dropdown ms-1 header-item topbar-notification">
                    <button type="button"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle shadow-none"
                        id="page-header-notification-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="bx bx-bell fs-22"></i>
                        <span id="notifBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            style="display:none;">0</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-notification-dropdown">
                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 fw-semibold text-white">Notifikasi</h6>
                                    <button id="clearNotifBtn" class="btn btn-sm btn-soft-danger fw-bold">
                                        Delete All
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TEMPLATE (disembunyikan) -->
                        <div id="notifTemplate" class="d-none">
                            <div class="list-group-item list-group-item-action notif-item d-flex align-items-start"
                                data-id="" role="button">

                                <div class="flex-grow-1">
                                    <h6 class="mb-1 notif-title"></h6>
                                    <p class="mb-1 small notif-message"></p>
                                    <small class="text-muted notif-time"></small>
                                </div>

                                <div class="ms-2 d-flex flex-column align-items-center">
                                    <i class="notif-icon fs-5"></i>
                                    <button class="btn btn-sm btn-soft-danger mt-2 btn-delete-notif">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>

                            </div>
                        </div>

                        <div class="tab-content position-relative" id="notificationItemsTabContent">
                            <div id="notifList" class="list-group list-group-flush"
                                style="max-height: 300px; overflow-y:auto;">
                                <p class="text-center text-muted py-3 mb-0">Tidak ada notifikasi baru</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle shadow-none"
                        data-toggle="fullscreen">
                        <i class="bx bx-fullscreen fs-22"></i>
                    </button>
                </div>

                <!-- Tombol toggle dark mode -->
                <div class="ms-1 header-item d-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode shadow-none"
                        id="btn-darkmode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn shadow-none" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user profil-avt"
                                src="{{ Auth::user()->image_url }}" alt="Header Avatar" />
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">
                                    {{ Auth::user()->nama_lengkap ?? Auth::user()->username }}</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">
                            Welcome! {{ Auth::user()->nama_lengkap ?? Auth::user()->username }}
                        </h6>
                        <a class="dropdown-item" href="{{ route('user.profile') }}"><i
                                class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                                class="align-middle me-2">Profile</span><span class="badge bg-success">New</span></a>
                        <a id="logoutButton" class="dropdown-item cursor-pointer"><i
                                class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle" data-key="t-logout">Logout</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- <script>
    $(document).ready(function() {


    });
</script> --}}
