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

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu">
            </div>
            @if (in_array(Session::get('jabatan'), ['dept_head', 'foreman', 'operator', 'supervisor']))
                <ul class="navbar-nav" id="navbar-nav">
                    <li class="menu-title"><span data-key="t-menu">Dashboard</span></li>
                    <li class="nav-item">
                        @if (Session::get('jabatan') !== 'operator')
                            <a href="{{ route('main.dashboard') }}"
                                class="nav-link menu-link {{ request()->routeIs('main.dashboard') ? 'active' : '' }}">
                                <i class="mdi mdi-chart-bar"></i> <span data-key="main-dashboard">Main
                                    Dashboard</span>
                            </a>
                        @endif
                        @if (Session::get('jabatan') == 'operator')
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
                                        Input TKBM </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('tkbm.data') }}"
                                        class="nav-link {{ request()->routeIs('tkbm.data') ? 'active' : '' }}"
                                        data-key="t-chat">
                                        Data TKBM </a>
                                </li>
                                @if (Session::get('jabatan') !== 'operator')
                                    <li class="nav-item">
                                        <a href="{{ route('tkbm.fee') }}"
                                            class="nav-link {{ request()->routeIs('tkbm.fee') ? 'active' : '' }}"
                                            data-key="t-chat"> Fees & Taxes
                                        </a>
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
