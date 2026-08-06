@can('permission', 'vehicle-monitoring-menu')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('vehicle.monitoring.*') ? '' : 'collapsed' }}"
            href="#sidebarVehicleMonitoring" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ request()->routeIs('vehicle.monitoring.*') ? 'true' : 'false' }}"
            aria-controls="sidebarVehicleMonitoring">
            <i class="ri-truck-line"></i> <span data-key="t-vm">Vehicle Monitoring</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('vehicle.monitoring.*') ? 'show' : '' }}"
            id="sidebarVehicleMonitoring">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('vehicle.monitoring.history') }}"
                        class="nav-link menu-link {{ request()->routeIs('vehicle.monitoring.history') ? 'active' : '' }}">
                        <i class="ri-history-line fs-12"></i>Report
                    </a>
                </li>
                @can('permission', 'vehicle-monitoring-timbangan')
                    <li class="nav-item">
                        <a href="{{ route('vehicle.monitoring.timbangan') }}"
                            class="nav-link menu-link {{ request()->routeIs('vehicle.monitoring.timbangan') ? 'active' : '' }}">
                            <i class="ri-scales-3-line fs-12"></i> Timbangan
                        </a>
                    </li>
                @endcan
                @can('permission', 'vehicle-monitoring-qc')
                    <li class="nav-item">
                        <a href="{{ route('vehicle.monitoring.qc') }}"
                            class="nav-link menu-link {{ request()->routeIs('vehicle.monitoring.qc') ? 'active' : '' }}">
                            <i class="ri-flask-line fs-12"></i> QC Area
                        </a>
                    </li>
                @endcan
                @can('permission', 'vehicle-monitoring-wpm')
                    <li class="nav-item">
                        <a href="{{ route('vehicle.monitoring.wpm') }}"
                            class="nav-link menu-link {{ request()->routeIs('vehicle.monitoring.wpm') ? 'active' : '' }}">
                            <i class="ri-download-2-line fs-12"></i> WPM Area
                        </a>
                    </li>
                @endcan
                @can('permission', 'vehicle-monitoring-wrm')
                    <li class="nav-item">
                        <a href="{{ route('vehicle.monitoring.wrm') }}"
                            class="nav-link menu-link {{ request()->routeIs('vehicle.monitoring.wrm') ? 'active' : '' }}">
                            <i class="ri-download-2-line fs-12"></i> WRM Area
                        </a>
                    </li>
                @endcan
                @can('permission', 'vehicle-monitoring-wfg')
                    <li class="nav-item">
                        <a href="{{ route('vehicle.monitoring.wfg') }}"
                            class="nav-link menu-link {{ request()->routeIs('vehicle.monitoring.wfg') ? 'active' : '' }}">
                            <i class="ri-upload-2-line fs-12"></i> WFG Area
                        </a>
                    </li>
                @endcan
                @can('permission', 'vehicle-monitoring-smu')
                    <li class="nav-item">
                        <a href="{{ route('vehicle.monitoring.smu') }}"
                            class="nav-link menu-link {{ request()->routeIs('vehicle.monitoring.smu') ? 'active' : '' }}">
                            <i class="ri-database-2-line fs-12"></i> SMU Area
                        </a>
                    </li>
                @endcan
                @can('permission', 'vehicle-monitoring-master')
                    <li class="nav-item">
                        <a href="{{ route('vehicle.monitoring.master.items') }}"
                            class="nav-link menu-link {{ request()->routeIs('vehicle.monitoring.master.items') ? 'active' : '' }}">
                            <i class="ri-price-tag-3-line fs-12"></i> Master Item & Sloc
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
