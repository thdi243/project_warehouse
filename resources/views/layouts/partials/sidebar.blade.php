<div class="app-menu navbar-menu">
    <!-- LOGO -->
    @php
        $jabatan = Auth::user()->jabatan;
        $bagian = Auth::user()->bagian;

        // dd(Auth::user()->username);

    @endphp
    <div class="navbar-brand-box">
        <div class="navbar-brand-box ">
            <a href="{{ $jabatan != 'operator' ? route('dashboard') : route('dashboard') }}" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="{{ asset('assets/images/logo/kecap.png') }}" alt="" height="22">
                </span>
                <span class="logo-lg">
                    <img src="{{ asset('assets/images/logo/kecap.png') }}" alt="" height="100">
                </span>
            </a>
            <a href="{{ $jabatan != 'operator' ? route('dashboard') : route('dashboard') }}" class="logo logo-light">
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
            @if (in_array($jabatan, ['dept_head', 'foreman', 'operator', 'supervisor', 'admin']))
                <ul class="navbar-nav" id="navbar-nav">

                    @can('permission', 'dashboard-manager')
                        {{-- @if ($jabatan === 'dept_head') --}}
                        <li class="menu-title"><span data-key="t-menu">Dashboard</span></li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.p2h') }}"
                                class="nav-link menu-link {{ request()->routeIs('dashboard.p2h') ? 'active' : '' }}">
                                <i class="mdi mdi-format-list-checks"></i>
                                <span data-key="p2h-dashboard">P2H Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.tkbm') }}"
                                class="nav-link menu-link {{ request()->routeIs('dashboard.tkbm') ? 'active' : '' }}">
                                <i class="mdi mdi-chart-box"></i>
                                <span data-key="tkbm-dashboard">TKBM BPS Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.rak') }}"
                                class="nav-link menu-link {{ request()->routeIs('dashboard.rak') ? 'active' : '' }}">
                                <i class="mdi mdi-view-grid-plus"></i>
                                <span data-key="rak-dashboard">Rak Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.wrm.index') }}"
                                class="nav-link menu-link {{ request()->routeIs('dashboard.wrm.index') ? 'active' : '' }}">
                                <i class="mdi mdi-warehouse"></i>
                                <span data-key="wrm-dashboard">Inventory Dashboard</span>
                            </a>
                        </li>
                    @endcan
                    {{-- Semua dashboard --}}
                    @can('permission', 'dashboard')
                        {{-- @elseif (in_array($jabatan, ['supervisor', 'foreman'])) --}}
                        {{-- Satu menu saja --}}
                        <li class="menu-title"><span data-key="t-menu">Dashboard</span></li>
                        <li class="nav-item">
                            <a href="#sideBarDashboard" data-bs-toggle="collapse" role="button"
                                class="nav-link menu-link {{ request()->routeIs('dashboard.*') ? 'collapsed' : '' }}"
                                aria-controls="sideBarDashboard">
                                <i class="mdi mdi-view-dashboard"></i>
                                <span data-key="main-dashboard">Dashboard</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('dashboard.*') ? 'show' : '' }}"
                                id="sideBarDashboard">
                                <ul class="nav nav-sm flex-column">
                                    @can('permission', 'dashboard-p2h')
                                        <li class="nav-item">
                                            <a href="{{ route('dashboard.p2h') }}"
                                                class="nav-link {{ request()->routeIs('dashboard.p2h') ? 'active' : '' }}">
                                                <i class="mdi mdi-format-list-checks"></i> P2H Dashboard </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'dashboard-bps')
                                        <li class="nav-item">
                                            <a href="{{ route('dashboard.tkbm') }}"
                                                class="nav-link {{ request()->routeIs('dashboard.tkbm') ? 'active' : '' }}">
                                                <i class="mdi mdi-chart-box"></i> TKBM BPS</a>
                                        </li>
                                    @endcan
                                    @can('permission', 'dashboard-ikat-terpal')
                                        <li class="nav-item">
                                            <a href="{{ route('dashboard.ikat-terpal') }}"
                                                class="nav-link {{ request()->routeIs('dashboard.ikat-terpal') ? 'active' : '' }}">
                                                <i class="mdi mdi-chart-box"></i> TKBM Ikat Terpal</a>
                                        </li>
                                    @endcan
                                    @can('permission', 'dashboard-rak')
                                        <li class="nav-item">
                                            <a href="{{ route('dashboard.rak') }}"
                                                class="nav-link {{ request()->routeIs('dashboard.rak') ? 'active' : '' }}">
                                                <i class="mdi mdi-view-grid-plus"></i>Rak Dashboard </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'dashboard-wrm')
                                        <li class="nav-item">
                                            <a href="{{ route('dashboard.wrm.index') }}"
                                                class="nav-link {{ request()->routeIs('dashboard.wrm.index') ? 'active' : '' }}">
                                                <i class="mdi mdi-warehouse"></i>Inventory WRM </a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        {{-- @endif --}}
                    @endcan

                    {{-- Menu --}}
                    {{-- @if ($jabatan !== 'dept_head') --}}
                    <li class="menu-title"><span data-key="t-menu">Warehouse Menu</span></li>

                    {{-- WSP Menu --}}
                    {{-- @if ($jabatan != 'dept_head' && in_array($bagian, ['warehouse', 'warehouse_sparepart'])) --}}
                    {{-- TKBM Menu --}}
                    @can('permission', 'tkbm')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->is('tkbm/*') ? '' : 'collapsed' }}"
                                href="#sideBarTkbm" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->is('tkbm/*') ? 'true' : 'false' }}"
                                aria-controls="sideBarTkbm">
                                <i class="mdi mdi-human-dolly"></i> <span data-key="t-tkbm">TKBM</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->is('tkbm/*') ? 'show' : '' }}"
                                id="sideBarTkbm">
                                <ul class="nav nav-sm flex-column">
                                    @can('permission', 'tkbm-bps')
                                        <li class="nav-item">
                                            <a href="#" data-bs-target="#sidebarTkbmBps" data-bs-toggle="collapse"
                                                role="button"
                                                aria-expanded="{{ request()->is('tkbm/bps/*') ? 'true' : 'false' }}"
                                                aria-controls="sidebarTkbmBps" class="nav-link" {{-- class="nav-link {{ request()->is('tkbm/bps/*') ? 'active' : '' }}" --}}
                                                data-key="t-m-tkbm">
                                                <i class="bx bx-git-commit fs-12"></i>BPS
                                            </a>
                                            <div class="collapse menu-dropdown {{ request()->is('tkbm/bps/*') ? 'show' : '' }}"
                                                id="sidebarTkbmBps">
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
                                                            Report BPS </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    @endcan
                                    @can('permission', 'tkbm-ikat-terpal')
                                        <li class="nav-item">
                                            <a href="#" data-bs-target="#sidebarTkbmIkatTerpal"
                                                data-bs-toggle="collapse" role="button"
                                                aria-expanded="{{ request()->is('tkbm/ikat-terpal/*') ? 'true' : 'false' }}"
                                                aria-controls="sidebarTkbmIkatTerpal" class="nav-link" {{-- class="nav-link {{ request()->is('tkbm.*') ? 'active' : '' }}" --}}
                                                data-key="t-m-tkbm">
                                                <i class="bx bx-git-commit fs-12"></i>Ikat Terpal
                                            </a>
                                            <div class="collapse menu-dropdown {{ request()->is('tkbm/ikat-terpal/*') ? 'show' : '' }}"
                                                id="sidebarTkbmIkatTerpal">
                                                <ul class="nav nav-sm flex-column">
                                                    <li class="nav-item">
                                                        <a href="{{ route('tkbm.ikat-terpal.index') }}"
                                                            class="nav-link {{ request()->routeIs('tkbm.ikat-terpal.index') ? 'active' : '' }}"
                                                            data-key="t-input-tkbm">
                                                            Form Ikat Terpal </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="{{ route('tkbm.ikat-terpal.report') }}"
                                                            class="nav-link {{ request()->routeIs('tkbm.ikat-terpal.report') ? 'active' : '' }}"
                                                            data-key="t-tkbm">
                                                            Report Ikat Terpal </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                    @endcan

                    {{-- WSP Stock Menu --}}
                    @can('permission', 'wsp-menu')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('stock.*') ? '' : 'collapsed' }}"
                                href="#sideBarRak" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('stock.*') ? 'true' : 'false' }}"
                                aria-controls="sideBarRak">
                                <i class="mdi mdi-package-variant"></i><span data-key="t-stock">WSP</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('stock.*') ? 'show' : '' }}"
                                id="sideBarRak">
                                <ul class="nav nav-sm flex-column">
                                    {{-- @can('permission', 'wsp-stock-manage')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.dashboard') }}"
                                                class="nav-link {{ request()->is('stock/stock_manage/*') ? 'active' : '' }}"
                                                data-key="t-input-mst_brg_wfg">
                                                <i class="mdi mdi-clipboard-list-outline"></i>
                                                Stock Manage </a>
                                        </li>
                                    @endcan --}}
                                    @can('permission', 'wsp-stock-location')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.stock-location') }}"
                                                class="nav-link menu-link {{ request()->routeIs('stock.stock-location') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i> <span data-key="t-stock-gula">Stock
                                                    Location</span>
                                            </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wsp-soh')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.stock-on-hand') }}"
                                                class="nav-link menu-link {{ request()->routeIs('stock.stock-on-hand') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i> <span data-key="t-stock-gula">Stock On
                                                    Hand</span>
                                            </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wsp-form-pr')
                                        <li class="nav-item">
                                            <a href="{{ url('/app/purchase-requesition/form') }}" target="_blank"
                                                class="nav-link menu-link {{ request()->Is('/app/purchase-requesition/form') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i> <span data-key="t-stock-gula">Form
                                                    PR</span>
                                            </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wsp-stock-pr')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.pr.index') }}"
                                                class="nav-link {{ request()->routeIs('stock.pr.index') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Purchase Requesition</a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wsp-approval-pr')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.pr.approval') }}"
                                                class="nav-link menu-link {{ request()->routeIs('stock.pr.approval') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Approval PR
                                            </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wsp-incoming')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.move.incoming.index') }}"
                                                class="nav-link menu-link {{ request()->routeIs('stock.move.incoming.index') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Incoming
                                            </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wsp-outgoing')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.move.outgoing.index') }}"
                                                class="nav-link menu-link {{ request()->routeIs('stock.move.outgoing.index') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Outgoing
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- @can('permission', 'wsp-stock-move')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.move.index') }}"
                                                class="nav-link {{ request()->is('stock/stock_move/*') ? 'active' : '' }}"
                                                data-key="t-input-mst_brg_wfg">
                                                <i class="mdi mdi-swap-horizontal"></i>Stock Move</a>
                                        </li>
                                    @endcan --}}
                                </ul>
                            </div>
                        </li>
                    @endcan
                    {{-- @endif --}}

                    {{-- WRM Menu --}}
                    @can('permission', 'p2h')
                        {{-- @if ($jabatan != 'dept_head' && in_array($bagian, ['warehouse', 'warehouse_raw_material', 'warehouse_finish_goods'])) --}}
                        {{-- P2H Menu --}}
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
                                    @can('permission', 'p2h-form')
                                        @if ($jabatan === 'operator')
                                            {{-- langsung form & data tanpa P2H Online --}}
                                            <li class="nav-item">
                                                <a href="{{ route('p2h.online.index') }}"
                                                    class="nav-link {{ request()->routeIs('p2h.online.index') ? 'active' : '' }}"
                                                    data-key="t-input-p2h">
                                                    <i class="bx bx-git-commit fs-12"></i>Form P2H
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('p2h.online.data') }}"
                                                    class="nav-link {{ request()->routeIs('p2h.online.data') ? 'active' : '' }}"
                                                    data-key="t-chat">
                                                    <i class="bx bx-git-commit fs-12"></i>Data P2H
                                                </a>
                                            </li>
                                        @else
                                            <li class="nav-item">
                                                <a href="#" data-bs-target="#sidebarP2hOnline"
                                                    data-bs-toggle="collapse" role="button"
                                                    aria-expanded="{{ request()->routeIs('p2h.online.*') ? 'true' : 'false' }}"
                                                    aria-controls="sidebarP2hOnline" class="nav-link" {{-- class="nav-link {{ request()->routeIs('p2h.online.*') ? 'active' : '' }}" --}}
                                                    data-key="t-m-tkbm">
                                                    <i class="bx bx-git-commit fs-12"></i>P2H Online
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
                                        @endif
                                    @endcan
                                    @can('permission', 'p2h-unit-regis')
                                        <li class="nav-item">
                                            <a href="#" data-bs-target="#sidebarRegUnitP2h" data-bs-toggle="collapse"
                                                role="button"
                                                aria-expanded="{{ request()->routeIs('p2h.registration.*') ? 'true' : 'false' }}"
                                                aria-controls="sidebarRegUnitP2h" class="nav-link" data-key="t-m-tkbm">
                                                <i class="bx bx-git-commit fs-12"></i>Registrasi Unit P2H
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
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        {{-- @endif --}}
                    @endcan

                    {{-- WFG Menu --}}
                    @can('permission', 'wfg-menu')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('wfg.*') ? '' : 'collapsed' }}"
                                href="#sidebarWFG" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('wfg.*') ? 'true' : 'false' }}"
                                aria-controls="sidebarWFG">
                                <i class="mdi mdi-warehouse"></i> <span data-key="t-wfg">WFG</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('wfg.*') ? 'show' : '' }}"
                                id="sidebarWFG">
                                <ul class="nav nav-sm flex-column">
                                    @can('permission', 'loading-order')
                                        <li class="nav-item">
                                            <a href="#sidebarWFGLO" data-bs-toggle="collapse" role="button"
                                                class="nav-link {{ request()->routeIs('wfg.loading_order.*') ? '' : 'collapsed' }}"
                                                aria-expanded="{{ request()->routeIs('wfg.loading_order.*') ? 'true' : 'false' }}"
                                                aria-controls="sidebarWFGLO">
                                                <i class="bx bx-git-commit fs-12"></i> Loading Order
                                            </a>
                                            <div class="collapse menu-dropdown {{ request()->routeIs('wfg.loading_order.*') ? 'show' : '' }}"
                                                id="sidebarWFGLO">
                                                <ul class="nav nav-sm flex-column">
                                                    <li class="nav-item">
                                                        <a href="{{ route('wfg.loading_order.form') }}"
                                                            class="nav-link {{ request()->routeIs('wfg.loading_order.form') ? 'active' : '' }}">
                                                            <i class="bx bx-git-commit fs-12"></i> Form Input
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="{{ route('wfg.loading_order.index') }}"
                                                            class="nav-link {{ request()->routeIs('wfg.loading_order.index') ? 'active' : '' }}">
                                                            <i class="bx bx-git-commit fs-12"></i>Data Monitoring
                                                        </a>
                                                    </li>
                                                    @can('permission', 'approval-loading-order')
                                                        <li class="nav-item">
                                                            <a href="{{ route('wfg.loading_order.approval') }}"
                                                                class="nav-link {{ request()->routeIs('wfg.loading_order.approval') ? 'active' : '' }}">
                                                                <i class="bx bx-git-commit fs-12"></i>Verifikasi
                                                            </a>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </div>
                                        </li>
                                    @endcan

                                    @can('permission', 'stock-opname-wfg')
                                        <li class="nav-item">
                                            <a href="#sidebarWFGSO" data-bs-toggle="collapse" role="button"
                                                class="nav-link {{ request()->routeIs('wfg.stock_opname.*') ? '' : 'collapsed' }}"
                                                aria-expanded="{{ request()->routeIs('wfg.stock_opname.*') ? 'true' : 'false' }}"
                                                aria-controls="sidebarWFGSO">
                                                <i class="bx bx-git-commit fs-12"></i> Stock Opname
                                            </a>
                                            <div class="collapse menu-dropdown {{ request()->routeIs('wfg.stock_opname.*') ? 'show' : '' }}"
                                                id="sidebarWFGSO">
                                                <ul class="nav nav-sm flex-column">
                                                    <li class="nav-item">
                                                        <a href="{{ route('wfg.stock_opname.soh') }}"
                                                            class="nav-link {{ request()->routeIs('wfg.stock_opname.soh') ? 'active' : '' }}">
                                                            <i class="bx bx-git-commit fs-12"></i>SOH Upload
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="{{ route('wfg.stock_opname.form') }}"
                                                            class="nav-link {{ request()->routeIs('wfg.stock_opname.form') ? 'active' : '' }}">
                                                            <i class="bx bx-git-commit fs-12"></i> SO Form
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="{{ route('wfg.stock_opname.report') }}"
                                                            class="nav-link {{ request()->routeIs('wfg.stock_opname.report') ? 'active' : '' }}">
                                                            <i class="bx bx-git-commit fs-12"></i> SO Report
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                    @endcan

                    @can('permission', 'wrm-menu')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('wrm.inventory.*') ? '' : 'collapsed' }}"
                                href="#sideBarWrmStock" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('wrm.inventory.*') ? 'true' : 'false' }}"
                                aria-controls="sideBarWrmStock">
                                <i class="mdi mdi-cube-outline"></i><span data-key="t-wrm.inventory">WRM</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('wrm.inventory.*') ? 'show' : '' }}"
                                id="sideBarWrmStock">
                                <ul class="nav nav-sm flex-column">
                                    {{-- <li class="nav-item">
                                        <a href="#" data-bs-target="#sideBarInventory" data-bs-toggle="collapse"
                                            role="button"
                                            aria-expanded="{{ request()->routeIs('wrm.inventory.*') ? 'true' : 'false' }}"
                    aria-controls="sideBarInventory" class="nav-link" data-key="t-m-tkbm">
                    <i class="mdi mdi-view-grid"></i>Inventory
                    </a> --}}
                                    {{-- <div class="collapse menu-dropdown {{ request()->routeIs('wrm.inventory.*') ? 'show' : '' }}"
                    id="sideBarInventory"> --}}
                                    {{-- <ul class="nav nav-sm flex-column"> --}}
                                    @can('permission', 'wrm-inventory-upload')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.inventory.index-upload') }}"
                                                class="nav-link menu-link {{ request()->routeIs('wrm.inventory.index-upload') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Upload Data
                                            </a>
                                        </li>
                                    @endcan

                                    @can('permission', 'wrm-inventory-inbound')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.inventory.viewInbound') }}"
                                                class="nav-link menu-link {{ request()->routeIs('wrm.inventory.viewInbound') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Data Inbound
                                            </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wrm-inventory-soh')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.inventory.index') }}"
                                                class="nav-link menu-link sub-menu {{ request()->routeIs('wrm.inventory.index') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Stock On Hand
                                            </a>
                                        </li>
                                    @endcan

                                    @can('permission', 'wrm-inventory-draft-outbound')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.inventory.draft-outbound') }}"
                                                class="nav-link menu-link {{ request()->routeIs('wrm.inventory.draft-outbound') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Form Draft Outbound
                                            </a>
                                        </li>
                                    @endcan

                                    @can('permission', 'wrm-inventory-data-draft-outbound')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.inventory.data-outbound') }}"
                                                class="nav-link menu-link {{ request()->routeIs('wrm.inventory.data-outbound') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Data Draft Outbound
                                            </a>
                                        </li>
                                    @endcan

                                    @can('permission', 'wrm-inventory-transfer-history')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.inventory.index-transfer') }}"
                                                class="nav-link menu-link {{ request()->routeIs('wrm.inventory.index-transfer') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Data Transfer/Susut
                                            </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wrm-inventory-monitoring-ppic')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.inventory.monitoring.ppic.index') }}"
                                                class="nav-link menu-link {{ request()->routeIs('wrm.inventory.monitoring.ppic.index') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Monitoring PPIC
                                            </a>
                                        </li>
                                    @endcan

                                    @can('permission', 'wrm-inventory-monitoring-purchasing')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.inventory.monitoring.purchasing.index') }}"
                                                class="nav-link menu-link {{ request()->routeIs('wrm.inventory.monitoring.purchasing.index') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Monitoring Purchasing
                                            </a>
                                        </li>
                                    @endcan
                                    {{-- </ul> --}}
                                    {{-- </div> --}}
                                    {{-- </li> --}}
                                </ul>
                            </div>
                        </li>
                    @endcan

                    {{-- Data Master --}}
                    {{-- @if ($jabatan !== 'operator') --}}
                    <li class="menu-title"><span data-key="t-menu">Data Master</span></li>
                    {{-- WFG Master --}}
                    @can('permission', 'master-wfg')
                        {{-- @if (in_array($bagian, ['warehouse', 'warehouse_finish_goods'])) --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('wfg.master.*') ? 'collapsed' : '' }}"
                                href="#sidebarMasterWfg" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('wfg.master.*') ? 'true' : 'false' }}"
                                aria-controls="sidebarMasterWfg">
                                <i class="mdi mdi-warehouse"></i> <span data-key="t-stock_op_wfg">Master WFG</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('wfg.master.*') ? 'show' : '' }}"
                                id="sidebarMasterWfg">
                                <ul class="nav nav-sm flex-column">
                                    {{-- SO Barang --}}
                                    <li class="nav-item">
                                        <a href="{{ route('wfg.master.barang.index') }}"
                                            class="nav-link {{ request()->routeIs('wfg.master.barang.index') ? 'active' : '' }}"
                                            data-key="t-input-mst_brg_wfg">
                                            <i class="bx bx-git-commit fs-12"></i>
                                            Master Barang</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('wfg.master.destinasi.index') }}"
                                            class="nav-link {{ request()->routeIs('wfg.master.destinasi.index') ? 'active' : '' }}"
                                            data-key="t-input-mst_dest_wfg">
                                            <i class="bx bx-git-commit fs-12"></i>
                                            Master Destinasi</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        {{-- @endif --}}
                    @endcan

                    {{-- WSP Master --}}
                    @can('permission', 'master-wsp')
                        {{-- @if (in_array($bagian, ['warehouse', 'warehouse_sparepart'])) --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('wsp.master.*') ? 'collapsed' : '' }}"
                                href="#sidebarMasterWsp" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('wsp.master.*') ? 'true' : 'false' }}"
                                aria-controls="sidebarMasterWsp">
                                <i class="mdi mdi-tools"></i>
                                <span data-key="t-stock_op_wfg">Master WSP</span>
                            </a>

                            <div class="collapse menu-dropdown {{ request()->routeIs('wsp.master.*') ? 'show' : '' }}"
                                id="sidebarMasterWsp">
                                <ul class="nav nav-sm flex-column">
                                    {{-- Master Fees & taxes TKBM --}}
                                    <li class="nav-item">
                                        <a href="{{ route('wsp.master.fee') }}"
                                            class="nav-link {{ request()->routeIs('wsp.master.fee') ? 'active' : '' }}"
                                            data-key="t-input-tkbm">
                                            <i class="bx bx-git-commit fs-12"></i>Manage Fees & Harga
                                        </a>
                                    </li>

                                    {{-- Master Barang --}}
                                    <li class="nav-item">
                                        <a href="{{ route('wsp.master.barang') }}"
                                            class="nav-link {{ request()->routeIs('wsp.master.barang') ? 'active' : '' }}"
                                            data-key="t-input-mst_brg_wfg">
                                            <i class="bx bx-git-commit fs-12"></i>
                                            Master Barang
                                        </a>
                                    </li>

                                    {{-- Master Rak --}}
                                    <li class="nav-item">
                                        <a href="{{ route('wsp.master.rak') }}"
                                            class="nav-link {{ request()->routeIs('wsp.master.rak') ? 'active' : '' }}"
                                            data-key="t-input-mst_rak_wfg">
                                            <i class="bx bx-git-commit fs-12"></i>
                                            Master Rak
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        {{-- @endif --}}
                    @endcan

                    @can('permission', 'master-wrm')
                        {{-- @if (in_array($bagian, ['warehouse', 'warehouse_sparepart'])) --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('wrm.master.*') ? 'collapsed' : '' }}"
                                href="#sidebarMasterWrm" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('wrm.master.*') ? 'true' : 'false' }}"
                                aria-controls="sidebarMasterWrm">
                                <i class="mdi mdi-store-settings"></i>
                                <span data-key="t-stock_op_wfg">Master WRM</span>
                            </a>

                            <div class="collapse menu-dropdown {{ request()->routeIs('wrm.master.*') ? 'show' : '' }}"
                                id="sidebarMasterWrm">
                                <ul class="nav nav-sm flex-column">
                                    @can('permission', 'wrm-master-barang')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.master.barang.index') }}"
                                                class="nav-link {{ request()->routeIs('wrm.master.barang.index') ? 'active' : '' }}"
                                                data-key="t-input-tkbm">
                                                <i class="bx bx-git-commit fs-12"></i>Master Barang
                                            </a>
                                        </li>
                                    @endcan

                                    @can('permission', 'wrm-master-location')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.master.location.index') }}"
                                                class="nav-link {{ request()->routeIs('wrm.master.location.index') ? 'active' : '' }}"
                                                data-key="t-input-tkbm">
                                                <i class="bx bx-git-commit fs-12"></i>Master Lokasi
                                            </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wrm-master-bin')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.master.bin.index') }}"
                                                class="nav-link {{ request()->routeIs('wrm.master.bin.index') ? 'active' : '' }}"
                                                data-key="t-input-tkbm">
                                                <i class="bx bx-git-commit fs-12"></i>Master Bin
                                            </a>
                                        </li>
                                    @endcan

                                    @can('permission', 'wrm-master-pallet')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.master.pallet.index') }}"
                                                class="nav-link menu-link {{ request()->routeIs('wrm.master.pallet.*') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i>Master Pallet
                                            </a>
                                        </li>
                                    @endcan

                                    @can('permission', 'wrm-master-supplier')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.master.supplier.index') }}"
                                                class="nav-link {{ request()->routeIs('wrm.master.supplier.index') ? 'active' : '' }}"
                                                data-key="t-input-tkbm">
                                                <i class="bx bx-git-commit fs-12"></i>Master Supplier
                                            </a>
                                        </li>
                                    @endcan

                                    {{-- Master Fees & taxes TKBM --}}
                                    @can('permission', 'master-ikat-terpal')
                                        <li class="nav-item">
                                            <a href="{{ route('wrm.master.ikat-terpal.index') }}"
                                                class="nav-link {{ request()->routeIs('wrm.master.ikat-terpal.index') ? 'active' : '' }}"
                                                data-key="t-input-tkbm">
                                                <i class="bx bx-git-commit fs-12"></i>Master Ikat Terpal
                                            </a>
                                        </li>
                                    @endcan

                                </ul>
                            </div>
                        </li>
                        {{-- @endif --}}
                    @endcan

                    {{-- User Management --}}
                    @can('permission', 'manage-users')
                        <li class="nav-item">
                            <a href="{{ route('user.index') }}"
                                class="nav-link menu-link {{ request()->routeIs('user.*') ? 'active' : '' }}">
                                <i class="mdi mdi-folder-account"></i> <span data-key="t-tkbm">User</span>
                            </a>

                        </li>
                    @endcan

                    @can('permission', 'manage-permissions')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('admin.*') ? 'collapsed' : '' }}"
                                href="#sidebarPermissions" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('admin.*') ? 'true' : 'false' }}"
                                aria-controls="sidebarPermissions">
                                <i class="mdi mdi-shield-account"></i>
                                <span data-key="t-stock_op_wfg">Permissions</span>
                            </a>

                            <div class="collapse menu-dropdown {{ request()->routeIs('admin.*') ? 'show' : '' }}"
                                id="sidebarPermissions">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        @can('permission', 'super-admin')
                                            <a href="{{ route('admin.permissions.index') }}"
                                                class="nav-link menu-link {{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i> <span
                                                    data-key="t-tkbm">Permissions</span>
                                            </a>
                                            <a href="{{ route('admin.role.index') }}"
                                                class="nav-link menu-link {{ request()->routeIs('admin.role.*') ? 'active' : '' }}">
                                                <i class="bx bx-git-commit fs-12"></i> <span data-key="t-roles">Roles</span>
                                            </a>
                                        @endcan
                                        <a href="{{ route('admin.permissions.users') }}"
                                            class="nav-link menu-link {{ request()->routeIs('admin.permissions.users') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i> <span data-key="t-tkbm">
                                                Users Permissions</span>
                                        </a>
                                        <a href="{{ route('admin.user.roles_index') }}"
                                            class="nav-link menu-link {{ request()->routeIs('admin.user.roles_index') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i> <span data-key="t-user-roles">
                                                Users Roles</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endcan
                    {{-- @endif --}}
                </ul>
            @endif
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>

<div class="vertical-overlay"></div>
