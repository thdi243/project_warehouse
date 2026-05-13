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
                @can('permission', 'bongkar-muat')
                    <li class="nav-item">
                        <a href="#sidebarWFGLO" data-bs-toggle="collapse" role="button"
                            class="nav-link {{ request()->routeIs('wfg.bongkar_muat.*') ? '' : 'collapsed' }}"
                            aria-expanded="{{ request()->routeIs('wfg.bongkar_muat.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarWFGLO">
                            <i class="bx bx-git-commit fs-12"></i> Bongkar Muat
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('wfg.bongkar_muat.*') ? 'show' : '' }}"
                            id="sidebarWFGLO">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('wfg.bongkar_muat.form') }}"
                                        class="nav-link {{ request()->routeIs('wfg.bongkar_muat.form') ? 'active' : '' }}">
                                        <i class="bx bx-git-commit fs-12"></i> Form Input
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('wfg.bongkar_muat.index') }}"
                                        class="nav-link {{ request()->routeIs('wfg.bongkar_muat.index') ? 'active' : '' }}">
                                        <i class="bx bx-git-commit fs-12"></i>Data Monitoring
                                    </a>
                                </li>
                                @can('permission', 'approval-bongkar-muat')
                                    <li class="nav-item">
                                        <a href="{{ route('wfg.bongkar_muat.approval') }}"
                                            class="nav-link {{ request()->routeIs('wfg.bongkar_muat.approval') ? 'active' : '' }}">
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
