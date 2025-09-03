<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <div class="navbar-brand-box ">
            <a href="{{ session('jabatan') != 'operator' ? route('main.dashboard') : route('dashboard.tkbm') }}"
                class="logo logo-dark">
                <span class="logo-sm">
                    <img src="{{ asset('assets/images/logo/kecap.png') }}" alt="" height="22">
                </span>
                <span class="logo-lg">
                    <img src="{{ asset('assets/images/logo/kecap.png') }}" alt="" height="100">
                </span>
            </a>
            <a href="{{ session('jabatan') != 'operator' ? route('main.dashboard') : route('dashboard.tkbm') }}"
                class="logo logo-light">
                <span class="logo-sm">
                    <img src="{{ asset('assets/images/logo/kecap.png') }}" alt="" height="22">
                </span>
                <span class="logo-lg">
                    <img src="{{ asset('assets/images/logo/kecap.png') }}" alt="" height="100">
                </span>
            </a>
            <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
                id="vertical-hover">
                <i class="ri-record-circle-line"></i>
            </button>
        </div>
    </div>
    {{-- </div> --}}

    <div id="scrollbar" class="p-3">
        <div class="container-fluid">
            <div id="two-column-menu">
            </div>
            @if (in_array(Session::get('jabatan'), ['dept_head', 'foreman', 'operator', 'supervisor']))
                <ul class="navbar-nav" id="navbar-nav">
                    @if (Session::get('jabatan') !== 'operator')
                        <li class="menu-title"><span data-key="t-menu">Dashboard</span></li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.p2h') }}"
                                class="nav-link menu-link {{ request()->routeIs('dashboard.p2h') ? 'active' : '' }}">
                                <i class="mdi mdi-chart-box"></i> <span data-key="p2h-dashboard">P2H
                                    Dashboard</span>
                            </a>
                            <a href="{{ route('dashboard.tkbm') }}"
                                class="nav-link menu-link {{ request()->routeIs('dashboard.tkbm') ? 'active' : '' }}">
                                <i class="mdi mdi-account-hard-hat"></i> <span data-key="tkbm-dashboard">TKBM
                                    Dashboard</span>
                            </a>
                    @endif
                    </li>
                    <li class="menu-title"><span data-key="t-menu">Warehouse Menu</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link  {{ request()->routeIs('tkbm.*') ? '' : 'collapsed' }}"
                            href="#sideBarTkbm" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('tkbm.*') ? 'true' : 'false' }}"
                            aria-controls="sideBarTkbm">
                            <i class="mdi mdi-human-dolly"></i> <span data-key="t-tkbm">TKBM</span>
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('tkbm.*') ? 'show' : '' }}"
                            id="sideBarTkbm">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('tkbm.stock') }}"
                                        class="nav-link {{ request()->routeIs('tkbm.stock') ? 'active' : '' }}"
                                        data-key="t-input-tkbm">
                                        Form BPS </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('tkbm.data') }}"
                                        class="nav-link {{ request()->routeIs('tkbm.data') ? 'active' : '' }}"
                                        data-key="t-tkbm">
                                        Data TKBM </a>
                                </li>
                                @if (Session::get('jabatan') !== 'operator')
                                    <li class="nav-item">
                                        <a href="{{ route('tkbm.master.fee') }}"
                                            class="nav-link {{ request()->routeIs('tkbm.master.fee') ? 'active' : '' }}"
                                            data-key="t-input-tkbm">
                                            Manage Fees & Harga </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link  {{ request()->routeIs('p2h.*') ? '' : 'collapsed' }}"
                            href="#sideBarP2h" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('p2h.*') ? 'true' : 'false' }}"
                            aria-controls="sideBarP2h">
                            <i class="mdi mdi-clipboard-check-multiple"></i> <span data-key="t-p2h">P2H</span>
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('p2h.*') ? 'show' : '' }}"
                            id="sideBarP2h">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="#" data-bs-target="#sidebarP2hOnline" data-bs-toggle="collapse"
                                        role="button"
                                        aria-expanded="{{ request()->routeIs('p2h.online.*') ? 'true' : 'false' }}"
                                        aria-controls="sidebarP2hOnline" class="nav-link" {{-- class="nav-link {{ request()->routeIs('p2h.online.*') ? 'active' : '' }}" --}}
                                        data-key="t-m-tkbm">
                                        P2H Online
                                    </a>
                                    <div class="collapse menu-dropdown {{ request()->routeIs('p2h.online.*') ? 'show' : '' }}"
                                        id="sidebarP2hOnline">
                                        <ul class="nav nav-sm flex-column">
                                            <li class="nav-item">
                                                <a href="{{ route('p2h.online.index') }}"
                                                    class="nav-link {{ request()->routeIs('p2h.online.index') ? 'active' : '' }}"
                                                    data-key="t-input-p2h">
                                                    Form P2H </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('p2h.online.data') }}"
                                                    class="nav-link {{ request()->routeIs('p2h.online.data') ? 'active' : '' }}"
                                                    data-key="t-chat">
                                                    Data P2H </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                @if (Session::get('jabatan') !== 'operator')
                                    <li class="nav-item">
                                        <a href="#" data-bs-target="#sidebarRegUnitP2h"
                                            data-bs-toggle="collapse" role="button"
                                            aria-expanded="{{ request()->routeIs('p2h.registration.*') ? 'true' : 'false' }}"
                                            aria-controls="sidebarRegUnitP2h"
                                            class="nav-link {{ request()->routeIs('p2h.registration.*') ? 'active' : '' }}"
                                            data-key="t-m-tkbm">
                                            Registrasi Unit P2H
                                        </a>
                                        <div class="collapse menu-dropdown {{ request()->routeIs('p2h.registration.*') ? 'show' : '' }}"
                                            id="sidebarRegUnitP2h">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="{{ route('p2h.registration.forklift') }}"
                                                        class="nav-link {{ request()->routeIs('p2h.registration.forklift') ? 'active' : '' }}"
                                                        data-key="t-fees">Registrasi Forklift</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ route('p2h.registration.pallet-mover') }}"
                                                        class="nav-link {{ request()->routeIs('p2h.registration.pallet-mover') ? 'active' : '' }}"
                                                        data-key="t-h-produk">Registrasi Pallet Mover</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                    @if (Session::get('jabatan') !== 'operator')
                        <li class="menu-title"><span data-key="t-menu">Data Master User</span></li>
                        <li class="nav-item">
                            <a href="{{ route('user.index') }}"
                                class="nav-link menu-link {{ request()->routeIs('user.*') ? 'active' : '' }}">
                                <i class="mdi mdi-folder-account"></i> <span data-key="t-tkbm">Manage User</span>
                            </a>
                        </li>
                    @endif
                </ul>
            @endif
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>

<div class="vertical-overlay"></div>
