@can('permission', 'wsp-menu')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('stock.*') ? '' : 'collapsed' }}" href="#sideBarRak"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('stock.*') ? 'true' : 'false' }}"
            aria-controls="sideBarRak">
            <i class="mdi mdi-package-variant"></i><span data-key="t-stock">WSP</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('stock.*') ? 'show' : '' }}" id="sideBarRak">
            <ul class="nav nav-sm flex-column">
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
                @can('permission', 'wsp-riwayat-pr')
                    <li class="nav-item">
                        <a href="{{ route('stock.pr.history') }}"
                            class="nav-link menu-link {{ request()->routeIs('stock.pr.history') ? 'active' : '' }}">
                            <i class="bx bx-git-commit fs-12"></i>Riwayat PR</a>
                    </li>
                @endcan
                @can('permission', 'wsp-data-pr')
                    <li class="nav-item">
                        <a href="{{ route('stock.pr.index') }}"
                            class="nav-link menu-link {{ request()->routeIs('stock.pr.index') ? 'active' : '' }}">
                            <i class="bx bx-git-commit fs-12"></i>Data PR</a>
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
            </ul>
        </div>
    </li>
@endcan
