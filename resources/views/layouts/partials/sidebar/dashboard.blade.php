@can('permission', 'dashboard')
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
                @can('permission', 'dashboard-bongkar-muat')
                    <li class="nav-item">
                        <a href="{{ route('dashboard.wfg.bongkar-muat') }}"
                            class="nav-link {{ request()->routeIs('dashboard.wfg.bongkar-muat') ? 'active' : '' }}">
                            <i class="mdi mdi-truck-cargo-container"></i>Bongkar Muat WFG </a>
                    </li>
                @endcan
                @can('permission', 'vehicle-monitoring-menu')
                    <li class="nav-item">
                        <a href="{{ route('dashboard.vehicle') }}"
                            class="nav-link {{ request()->routeIs('dashboard.vehicle') ? 'active' : '' }}">
                            <i class="mdi mdi-truck-delivery"></i> Monitoring Kendaraan </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
