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
                                <span data-key="tkbm-dashboard">TKBM Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard.rak') }}"
                                class="nav-link menu-link {{ request()->routeIs('dashboard.rak') ? 'active' : '' }}">
                                <i class="mdi mdi-view-grid-plus"></i>
                                <span data-key="rak-dashboard">Rak Dashboard</span>
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
                                                <i class="mdi mdi-chart-box"></i> TKBM BPS Dashboard</a>
                                        </li>
                                    @endcan
                                    @can('permission', 'dashboard-rak')
                                        <li class="nav-item">
                                            <a href="{{ route('dashboard.rak') }}"
                                                class="nav-link {{ request()->routeIs('dashboard.rak') ? 'active' : '' }}">
                                                <i class="mdi mdi-view-grid-plus"></i>Rak Dashboard </a>
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
                            <a class="nav-link menu-link {{ request()->routeIs('tkbm.*') ? '' : 'collapsed' }}"
                                href="#sideBarTkbm" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('tkbm.*') ? 'true' : 'false' }}"
                                aria-controls="sideBarTkbm">
                                <i class="mdi mdi-human-dolly"></i> <span data-key="t-tkbm">TKBM</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('tkbm.*') ? 'show' : '' }}"
                                id="sideBarTkbm">
                                <ul class="nav nav-sm flex-column">
                                    @can('permission', 'tkbm-bps')
                                        <li class="nav-item">
                                            <a href="#" data-bs-target="#sidebarTkbmBps" data-bs-toggle="collapse"
                                                role="button"
                                                aria-expanded="{{ request()->routeIs('tkbm.*') ? 'true' : 'false' }}"
                                                aria-controls="sidebarTkbmBps" class="nav-link" {{-- class="nav-link {{ request()->routeIs('tkbm.*') ? 'active' : '' }}" --}}
                                                data-key="t-m-tkbm">
                                                <i class="mdi mdi-view-grid"></i>BPS
                                            </a>
                                            <div class="collapse menu-dropdown {{ request()->routeIs('tkbm.*') ? 'show' : '' }}"
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
                                                aria-expanded="{{ request()->routeIs('tkbm.*') ? 'true' : 'false' }}"
                                                aria-controls="sidebarTkbmIkatTerpal" class="nav-link" {{-- class="nav-link {{ request()->routeIs('tkbm.*') ? 'active' : '' }}" --}}
                                                data-key="t-m-tkbm">
                                                <i class="mdi mdi-view-grid"></i>Ikat Terpal
                                            </a>
                                            <div class="collapse menu-dropdown {{ request()->routeIs('tkbm.*') ? 'show' : '' }}"
                                                id="sidebarTkbmIkatTerpal">
                                                <ul class="nav nav-sm flex-column">
                                                    <li class="nav-item">
                                                        <a href="{{ route('tkbm.stock') }}"
                                                            class="nav-link {{ request()->routeIs('tkbm.stock') ? 'active' : '' }}"
                                                            data-key="t-input-tkbm">
                                                            Form Ikat Terpal </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a href="{{ route('tkbm.data') }}"
                                                            class="nav-link {{ request()->routeIs('tkbm.data') ? 'active' : '' }}"
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
                    @can('permission', 'wsp-stock')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('stock.*') ? '' : 'collapsed' }}"
                                href="#sideBarRak" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('stock.*') ? 'true' : 'false' }}"
                                aria-controls="sideBarRak">
                                <i class="mdi mdi-package-variant"></i><span data-key="t-stock">WSP Stock</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('stock.*') ? 'show' : '' }}"
                                id="sideBarRak">
                                <ul class="nav nav-sm flex-column">
                                    @can('permission', 'wsp-stock-manage')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.dashboard') }}"
                                                class="nav-link {{ request()->is('stock/stock_manage/*') ? 'active' : '' }}"
                                                data-key="t-input-mst_brg_wfg">
                                                <i class="mdi mdi-clipboard-list-outline"></i>
                                                Stock Manage </a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wsp-stock-pr')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.pr.index') }}"
                                                class="nav-link {{ request()->is('purchase-requesition/*') ? 'active' : '' }}"
                                                data-key="t-input-mst_brg_wfg">
                                                <i class="mdi mdi-note-plus"></i>Purchase Requesition</a>
                                        </li>
                                    @endcan
                                    @can('permission', 'wsp-stock-move')
                                        <li class="nav-item">
                                            <a href="{{ route('stock.move.index') }}"
                                                class="nav-link {{ request()->is('stock/stock_move/*') ? 'active' : '' }}"
                                                data-key="t-input-mst_brg_wfg">
                                                <i class="mdi mdi-swap-horizontal"></i>Stock Move</a>
                                        </li>
                                    @endcan
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
                                                    Form P2H
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="{{ route('p2h.online.data') }}"
                                                    class="nav-link {{ request()->routeIs('p2h.online.data') ? 'active' : '' }}"
                                                    data-key="t-chat">
                                                    Data P2H
                                                </a>
                                            </li>
                                        @else
                                            <li class="nav-item">
                                                <a href="#" data-bs-target="#sidebarP2hOnline"
                                                    data-bs-toggle="collapse" role="button"
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
                                        @endif
                                    @endcan
                                    @can('permission', 'p2h-unit-regis')
                                        <li class="nav-item">
                                            <a href="#" data-bs-target="#sidebarRegUnitP2h" data-bs-toggle="collapse"
                                                role="button"
                                                aria-expanded="{{ request()->routeIs('p2h.registration.*') ? 'true' : 'false' }}"
                                                aria-controls="sidebarRegUnitP2h" class="nav-link" data-key="t-m-tkbm">
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
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        {{-- @endif --}}
                    @endcan

                    {{-- WFG Menu --}}
                    @can('permission', 'stock-opname-wfg')
                        {{-- @if ($jabatan != 'dept_head' && in_array($bagian, ['warehouse', 'warehouse_finish_goods'])) --}}
                        {{-- Stock Opname --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('wfg.stock_opname.*') ? '' : 'collapsed' }}"
                                href="#sidebarSOp" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('wfg.stock_opname.*') ? 'true' : 'false' }}"
                                aria-controls="sidebarSOp">
                                <i class="mdi mdi-clipboard-check-outline"></i> <span data-key="t-stock_op_wfg">Stock
                                    Opname
                                    WFG</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('wfg.stock_opname.*') ? 'show' : '' }}"
                                id="sidebarSOp">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('wfg.stock_opname.soh') }}"
                                            class="nav-link {{ request()->routeIs('wfg.stock_opname.soh') ? 'active' : '' }}"
                                            data-key="t-stock_op_wfg">
                                            SOH Upload</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('wfg.stock_opname.form') }}"
                                            class="nav-link {{ request()->routeIs('wfg.stock_opname.form') ? 'active' : '' }}"
                                            data-key="t-input-stock_op_wfg">
                                            SO Form</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('wfg.stock_opname.report') }}"
                                            class="nav-link {{ request()->routeIs('wfg.stock_opname.report') ? 'active' : '' }}"
                                            data-key="t-stock_op_wfg">
                                            SO Report</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        {{-- @endif --}}
                    @endcan
                    {{-- @endif --}}

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
                                <i class="mdi mdi-warehouse"></i> <span data-key="t-stock_op_wfg">WFG</span>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('wfg.master.*') ? 'show' : '' }}"
                                id="sidebarMasterWfg">
                                <ul class="nav nav-sm flex-column">
                                    {{-- SO Barang --}}
                                    <li class="nav-item">
                                        <a href="{{ route('wfg.master.barang.index') }}"
                                            class="nav-link {{ request()->routeIs('wfg.master.barang.index') ? 'active' : '' }}"
                                            data-key="t-input-mst_brg_wfg">
                                            <i class="mdi mdi-package"></i>
                                            Master Barang SO </a>
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
                                <span data-key="t-stock_op_wfg">WSP</span>
                            </a>

                            <div class="collapse menu-dropdown {{ request()->routeIs('wsp.master.*') ? 'show' : '' }}"
                                id="sidebarMasterWsp">
                                <ul class="nav nav-sm flex-column">
                                    {{-- Master Fees & taxes TKBM --}}
                                    <li class="nav-item">
                                        <a href="{{ route('wsp.master.fee') }}"
                                            class="nav-link {{ request()->routeIs('wsp.master.fee') ? 'active' : '' }}"
                                            data-key="t-input-tkbm">
                                            <i class="mdi mdi-credit-card-outline"></i>Manage Fees & Harga
                                        </a>
                                    </li>

                                    {{-- Master Barang --}}
                                    <li class="nav-item">
                                        <a href="{{ route('wsp.master.barang') }}"
                                            class="nav-link {{ request()->routeIs('wsp.master.barang') ? 'active' : '' }}"
                                            data-key="t-input-mst_brg_wfg">
                                            <i class="mdi mdi-package-variant-closed"></i>
                                            Master Barang
                                        </a>
                                    </li>

                                    {{-- Master Rak --}}
                                    <li class="nav-item">
                                        <a href="{{ route('wsp.master.rak') }}"
                                            class="nav-link {{ request()->routeIs('wsp.master.rak') ? 'active' : '' }}"
                                            data-key="t-input-mst_rak_wfg">
                                            <i class="mdi mdi-view-grid-outline"></i>
                                            Master Rak
                                        </a>
                                    </li>


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
                                                <i class="mdi mdi-view-grid"></i> <span data-key="t-tkbm">Permissions</span>
                                            </a>
                                        @endcan
                                        <a href="{{ route('admin.permissions.users') }}"
                                            class="nav-link menu-link {{ request()->routeIs('admin.permissions.users') ? 'active' : '' }}">
                                            <i class="mdi mdi-view-grid"></i> <span data-key="t-tkbm">
                                                Users Permissions</span>
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
