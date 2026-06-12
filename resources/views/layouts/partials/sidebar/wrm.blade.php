@can('permission', 'wrm-menu')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('wrm.inventory.*') ? '' : 'collapsed' }}" href="#sideBarWrmStock"
            data-bs-toggle="collapse" role="button"
            aria-expanded="{{ request()->routeIs('wrm.inventory.*') ? 'true' : 'false' }}" aria-controls="sideBarWrmStock">
            <i class="mdi mdi-cube-outline"></i><span data-key="t-wrm.inventory">WRM</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('wrm.inventory.*') ? 'show' : '' }}" id="sideBarWrmStock">
            <ul class="nav nav-sm flex-column">
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
                @can('permission', 'wrm-inventory-summary-stock')
                    <li class="nav-item">
                        <a href="{{ route('wrm.inventory.summary.stock') }}"
                            class="nav-link menu-link sub-menu {{ request()->routeIs('wrm.inventory.summary.stock') ? 'active' : '' }}">
                            <i class="bx bx-git-commit fs-12"></i>Summary Stock
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

                @can('permission', 'wrm-inventory-forklift-jobs')
                    <li class="nav-item">
                        <a href="{{ route('wrm.inventory.forklift-jobs') }}"
                            class="nav-link menu-link {{ request()->routeIs('wrm.inventory.forklift-jobs') ? 'active' : '' }}">
                            <i class="bx bx-git-commit fs-12"></i>Forklift Jobs
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
            </ul>
        </div>
    </li>
@endcan
