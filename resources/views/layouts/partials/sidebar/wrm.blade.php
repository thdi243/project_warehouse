@can('permission', 'wrm-menu')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('wrm.*') ? '' : 'collapsed' }}" href="#sideBarWrmStock"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('wrm.*') ? 'true' : 'false' }}"
            aria-controls="sideBarWrmStock">
            <i class="mdi mdi-cube-outline"></i><span data-key="t-wrm">WRM</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('wrm.*') ? 'show' : '' }}" id="sideBarWrmStock">
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

                @can('permission', 'stock-opname-wrm')
                    <li class="nav-item">
                        <a href="#sidebarWRMSO" data-bs-toggle="collapse" role="button"
                            class="nav-link {{ request()->routeIs('wrm.stock_opname.*') ? '' : 'collapsed' }}"
                            aria-expanded="{{ request()->routeIs('wrm.stock_opname.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarWRMSO">
                            <i class="bx bx-git-commit fs-12"></i> Stock Opname
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('wrm.stock_opname.*') ? 'show' : '' }}"
                            id="sidebarWRMSO">
                            <ul class="nav nav-sm flex-column">
                                @can('permission', 'stock-opname-wrm-upload')
                                    <li class="nav-item">
                                        <a href="{{ route('wrm.stock_opname.soh') }}"
                                            class="nav-link {{ request()->routeIs('wrm.stock_opname.soh') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i>SOH Upload
                                        </a>
                                    </li>
                                @endcan
                                @can('permission', 'stock-opname-wrm-form')
                                    <li class="nav-item">
                                        <a href="{{ route('wrm.stock_opname.form') }}"
                                            class="nav-link {{ request()->routeIs('wrm.stock_opname.form') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i> SO Form
                                        </a>
                                    </li>
                                @endcan
                                @can('permission', 'stock-opname-wrm-report')
                                    <li class="nav-item">
                                        <a href="{{ route('wrm.stock_opname.report') }}"
                                            class="nav-link {{ request()->routeIs('wrm.stock_opname.report') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i> SO Report
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
