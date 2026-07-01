@can('permission', 'master-wrm')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->Is('master/wrm/*') ? 'collapsed' : '' }}" href="#sidebarMasterWrm"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->Is('master/wrm/*') ? 'true' : 'false' }}"
            aria-controls="sidebarMasterWrm">
            <i class="mdi mdi-store-settings"></i>
            <span data-key="t-stock_op_wfg">Master WRM</span>
        </a>

        <div class="collapse menu-dropdown {{ request()->Is('master/wrm/*') ? 'show' : '' }}" id="sidebarMasterWrm">
            <ul class="nav nav-sm flex-column">
                @can('permission', 'wrm-master-barang')
                    <li class="nav-item">
                        <a href="{{ route('master.wrm.barang.index') }}"
                            class="nav-link {{ request()->routeIs('master.wrm.barang.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Barang
                        </a>
                    </li>
                @endcan

                @can('permission', 'wrm-master-location')
                    <li class="nav-item">
                        <a href="{{ route('master.wrm.location.index') }}"
                            class="nav-link {{ request()->routeIs('master.wrm.location.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Lokasi
                        </a>
                    </li>
                @endcan
                @can('permission', 'wrm-master-bin')
                    <li class="nav-item">
                        <a href="{{ route('master.wrm.bin.index') }}"
                            class="nav-link {{ request()->routeIs('master.wrm.bin.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Bin
                        </a>
                    </li>
                @endcan

                @can('permission', 'wrm-master-pallet')
                    <li class="nav-item">
                        <a href="{{ route('master.wrm.pallet.index') }}"
                            class="nav-link menu-link {{ request()->routeIs('master.wrm.pallet.*') ? 'active' : '' }}">
                            <i class="bx bx-git-commit fs-12"></i>Master Pallet
                        </a>
                    </li>
                @endcan

                @can('permission', 'wrm-master-supplier')
                    <li class="nav-item">
                        <a href="{{ route('master.wrm.supplier.index') }}"
                            class="nav-link {{ request()->routeIs('master.wrm.supplier.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Supplier
                        </a>
                    </li>
                @endcan

                @can('permission', 'master-ikat-terpal')
                    <li class="nav-item">
                        <a href="{{ route('master.wrm.ikat-terpal.index') }}"
                            class="nav-link {{ request()->routeIs('master.wrm.ikat-terpal.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Ikat Terpal
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
