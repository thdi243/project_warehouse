@can('permission', 'wpm-menu')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('wpm.*') ? '' : 'collapsed' }}" href="#sideBarWpm"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('wpm.*') ? 'true' : 'false' }}"
            aria-controls="sideBarWpm">
            <i class="bx bx-package"></i><span data-key="t-wpm">WPM</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('wpm.*') ? 'show' : '' }}" id="sideBarWpm">
            <ul class="nav nav-sm flex-column">
                @can('permission', 'stock-opname-wpm')
                    <li class="nav-item">
                        <a href="#sidebarWPMSo" data-bs-toggle="collapse" role="button"
                            class="nav-link {{ request()->routeIs('wpm.stock_opname.*') ? '' : 'collapsed' }}"
                            aria-expanded="{{ request()->routeIs('wpm.stock_opname.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarWPMSo">
                            <i class="bx bx-git-commit fs-12"></i> Stock Opname
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('wpm.stock_opname.*') ? 'show' : '' }}"
                            id="sidebarWPMSo">
                            <ul class="nav nav-sm flex-column">
                                @can('permission', 'stock-opname-wpm-upload')
                                    <li class="nav-item">
                                        <a href="{{ route('wpm.stock_opname.soh') }}"
                                            class="nav-link {{ request()->routeIs('wpm.stock_opname.soh') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i>SOH Upload
                                        </a>
                                    </li>
                                @endcan
                                @can('permission', 'stock-opname-wpm-form')
                                    <li class="nav-item">
                                        <a href="{{ route('wpm.stock_opname.form') }}"
                                            class="nav-link {{ request()->routeIs('wpm.stock_opname.form') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i> SO Form
                                        </a>
                                    </li>
                                @endcan
                                @can('permission', 'stock-opname-wpm-report')
                                    <li class="nav-item">
                                        <a href="{{ route('wpm.stock_opname.report') }}"
                                            class="nav-link {{ request()->routeIs('wpm.stock_opname.report') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i> SO Report
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @can('permission', 'wpm-kempu')
                    <li class="nav-item">
                        <a href="#sidebarScanKempuWPM" data-bs-toggle="collapse" role="button"
                            class="nav-link {{ request()->routeIs('wpm.kempu.*') ? '' : 'collapsed' }}"
                            aria-expanded="{{ request()->routeIs('wpm.kempu.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarScanKempuWPM">
                            <i class="bx bx-git-commit fs-12"></i> Scan Kempu
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('wpm.kempu.*') ? 'show' : '' }}"
                            id="sidebarScanKempuWPM">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('wpm.kempu.index') }}"
                                        class="nav-link {{ request()->routeIs('wpm.kempu.index') ? 'active' : '' }}">
                                        <i class="bx bx-git-commit fs-12"></i>Scan
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('wpm.kempu.report') }}"
                                        class="nav-link {{ request()->routeIs('wpm.kempu.report') ? 'active' : '' }}">
                                        <i class="bx bx-git-commit fs-12"></i>Report
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
