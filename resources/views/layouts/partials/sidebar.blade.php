<div class="app-menu navbar-menu">
    <!-- LOGO -->
    @php
        $jabatan = Auth::user()->jabatan;
        $bagian = Auth::user()->bagian;
        $departemen = Auth::user()->departemen;

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
            @if ($departemen != 'warehouse' && $jabatan != 'admin')
                <ul class="navbar-nav" id="navbar-nav">
                    @include('layouts.partials.sidebar-no-warehouse.sidebar_no_warehouse')

                    {{-- Jika user no warehouse akses menu warehouse ada di sini --}}
                    @if (auth()->user()->hasAnyPermission([
                                'dashboard',
                                'tkbm',
                                'wsp-menu',
                                'p2h',
                                'wfg-menu',
                                'wrm-menu',
                                'master-wfg',
                                'master-wsp',
                                'master-wrm',
                                'manage-users',
                                'manage-permissions',
                            ]))
                        @include('layouts.partials.sidebar.dashboard')

                        @if (auth()->user()->hasAnyPermission(['tkbm', 'wsp-menu', 'p2h', 'wfg-menu', 'wrm-menu']))
                            <li class="menu-title"><span data-key="t-menu">Warehouse Menu</span></li>
                            @include('layouts.partials.sidebar.tkbm')
                            @include('layouts.partials.sidebar.wsp')
                            @include('layouts.partials.sidebar.p2h')
                            @include('layouts.partials.sidebar.wfg')
                            @include('layouts.partials.sidebar.wrm')
                        @endif

                        @if (auth()->user()->hasAnyPermission(['master-wfg', 'master-wsp', 'master-wrm']))
                            <li class="menu-title"><span data-key="t-menu">Data Master</span></li>
                            @include('layouts.partials.sidebar.master_wfg')
                            @include('layouts.partials.sidebar.master_wsp')
                            @include('layouts.partials.sidebar.master_wrm')
                        @endif

                        @include('layouts.partials.sidebar.user')
                        @include('layouts.partials.sidebar.permissions')
                    @endif
                </ul>
            @else
                @if (in_array($jabatan, ['dept_head', 'foreman', 'operator', 'supervisor', 'admin']))
                    <ul class="navbar-nav" id="navbar-nav">

                        @include('layouts.partials.sidebar.dashboard')

                        <li class="menu-title"><span data-key="t-menu">Warehouse Menu</span></li>

                        @include('layouts.partials.sidebar.tkbm')
                        @include('layouts.partials.sidebar.wsp')
                        @include('layouts.partials.sidebar.p2h')
                        @include('layouts.partials.sidebar.wfg')
                        @include('layouts.partials.sidebar.wrm')

                        <li class="menu-title"><span data-key="t-menu">Data Master</span></li>

                        @include('layouts.partials.sidebar.master_wfg')
                        @include('layouts.partials.sidebar.master_wsp')
                        @include('layouts.partials.sidebar.master_wrm')

                        @include('layouts.partials.sidebar.user')
                        @include('layouts.partials.sidebar.permissions')
                        {{-- @endif --}}
                    </ul>
                @endif
            @endif
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>

<div class="vertical-overlay"></div>
