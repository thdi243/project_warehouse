@can('permission', 'wcp-menu')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('wcp.*') ? '' : 'collapsed' }}" href="#sideBarWcp"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('wcp.*') ? 'true' : 'false' }}"
            aria-controls="sideBarWcp">
            <i class="bx bx-package"></i><span data-key="t-wcp">WCP</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('wcp.*') ? 'show' : '' }}" id="sideBarWcp">
            <ul class="nav nav-sm flex-column">
                @can('permission', 'stock-opname-wcp')
                    <li class="nav-item">
                        <a href="#sidebarWCPSo" data-bs-toggle="collapse" role="button"
                            class="nav-link {{ request()->routeIs('wcp.stock_opname.*') ? '' : 'collapsed' }}"
                            aria-expanded="{{ request()->routeIs('wcp.stock_opname.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarWCPSo">
                            <i class="bx bx-git-commit fs-12"></i> Stock Opname
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('wcp.stock_opname.*') ? 'show' : '' }}"
                            id="sidebarWCPSo">
                            <ul class="nav nav-sm flex-column">
                                @can('permission', 'stock-opname-wcp-upload')
                                    <li class="nav-item">
                                        <a href="{{ route('wcp.stock_opname.soh') }}"
                                            class="nav-link {{ request()->routeIs('wcp.stock_opname.soh') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i>SOH Upload
                                        </a>
                                    </li>
                                @endcan
                                @can('permission', 'stock-opname-wcp-form')
                                    <li class="nav-item">
                                        <a href="{{ route('wcp.stock_opname.form') }}"
                                            class="nav-link {{ request()->routeIs('wcp.stock_opname.form') ? 'active' : '' }}">
                                            <i class="bx bx-git-commit fs-12"></i> SO Form
                                        </a>
                                    </li>
                                @endcan
                                @can('permission', 'stock-opname-wcp-report')
                                    <li class="nav-item">
                                        <a href="{{ route('wcp.stock_opname.report') }}"
                                            class="nav-link {{ request()->routeIs('wcp.stock_opname.report') ? 'active' : '' }}">
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
