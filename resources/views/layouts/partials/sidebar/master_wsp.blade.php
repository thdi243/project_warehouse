@can('permission', 'master-wsp')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('wsp.master.*') ? 'collapsed' : '' }}" href="#sidebarMasterWsp"
            data-bs-toggle="collapse" role="button"
            aria-expanded="{{ request()->routeIs('wsp.master.*') ? 'true' : 'false' }}" aria-controls="sidebarMasterWsp">
            <i class="mdi mdi-tools"></i>
            <span data-key="t-stock_op_wfg">Master WSP</span>
        </a>

        <div class="collapse menu-dropdown {{ request()->routeIs('wsp.master.*') ? 'show' : '' }}" id="sidebarMasterWsp">
            <ul class="nav nav-sm flex-column">
                @can('permission', 'master-wsp-fee')
                    <li class="nav-item">
                        <a href="{{ route('wsp.master.fee') }}"
                            class="nav-link {{ request()->routeIs('wsp.master.fee') ? 'active' : '' }}" data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Manage Fees & Harga
                        </a>
                    </li>
                @endcan

                @can('permission', 'master-wsp-barang')
                    <li class="nav-item">
                        <a href="{{ route('wsp.master.barang') }}"
                            class="nav-link {{ request()->routeIs('wsp.master.barang') ? 'active' : '' }}"
                            data-key="t-input-mst_brg_wfg">
                            <i class="bx bx-git-commit fs-12"></i>
                            Master Barang
                        </a>
                    </li>
                @endcan

                @can('permission', 'master-wsp-rak')
                    <li class="nav-item">
                        <a href="{{ route('wsp.master.rak') }}"
                            class="nav-link {{ request()->routeIs('wsp.master.rak') ? 'active' : '' }}"
                            data-key="t-input-mst_rak_wfg">
                            <i class="bx bx-git-commit fs-12"></i>
                            Master Rak
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
