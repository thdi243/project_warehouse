@can('permission', 'master-wrm')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('wrm.master.*') ? 'collapsed' : '' }}"
            href="#sidebarMasterWrm" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ request()->routeIs('wrm.master.*') ? 'true' : 'false' }}"
            aria-controls="sidebarMasterWrm">
            <i class="mdi mdi-store-settings"></i>
            <span data-key="t-stock_op_wfg">Master WRM</span>
        </a>

        <div class="collapse menu-dropdown {{ request()->routeIs('wrm.master.*') ? 'show' : '' }}"
            id="sidebarMasterWrm">
            <ul class="nav nav-sm flex-column">
                @can('permission', 'wrm-master-barang')
                    <li class="nav-item">
                        <a href="{{ route('wrm.master.barang.index') }}"
                            class="nav-link {{ request()->routeIs('wrm.master.barang.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Barang
                        </a>
                    </li>
                @endcan

                @can('permission', 'wrm-master-location')
                    <li class="nav-item">
                        <a href="{{ route('wrm.master.location.index') }}"
                            class="nav-link {{ request()->routeIs('wrm.master.location.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Lokasi
                        </a>
                    </li>
                @endcan
                @can('permission', 'wrm-master-bin')
                    <li class="nav-item">
                        <a href="{{ route('wrm.master.bin.index') }}"
                            class="nav-link {{ request()->routeIs('wrm.master.bin.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Bin
                        </a>
                    </li>
                @endcan

                @can('permission', 'wrm-master-pallet')
                    <li class="nav-item">
                        <a href="{{ route('wrm.master.pallet.index') }}"
                            class="nav-link menu-link {{ request()->routeIs('wrm.master.pallet.*') ? 'active' : '' }}">
                            <i class="bx bx-git-commit fs-12"></i>Master Pallet
                        </a>
                    </li>
                @endcan

                @can('permission', 'wrm-master-supplier')
                    <li class="nav-item">
                        <a href="{{ route('wrm.master.supplier.index') }}"
                            class="nav-link {{ request()->routeIs('wrm.master.supplier.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Supplier
                        </a>
                    </li>
                @endcan

                @can('permission', 'master-ikat-terpal')
                    <li class="nav-item">
                        <a href="{{ route('wrm.master.ikat-terpal.index') }}"
                            class="nav-link {{ request()->routeIs('wrm.master.ikat-terpal.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Ikat Terpal
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
