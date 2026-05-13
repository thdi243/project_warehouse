@can('permission', 'master-wfg')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('wfg.master.*') ? 'collapsed' : '' }}"
            href="#sidebarMasterWfg" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ request()->routeIs('wfg.master.*') ? 'true' : 'false' }}"
            aria-controls="sidebarMasterWfg">
            <i class="mdi mdi-warehouse"></i> <span data-key="t-stock_op_wfg">Master WFG</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('wfg.master.*') ? 'show' : '' }}"
            id="sidebarMasterWfg">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('wfg.master.barang.index') }}"
                        class="nav-link {{ request()->routeIs('wfg.master.barang.index') ? 'active' : '' }}"
                        data-key="t-input-mst_brg_wfg">
                        <i class="bx bx-git-commit fs-12"></i>
                        Master Barang</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('wfg.master.destinasi.index') }}"
                        class="nav-link {{ request()->routeIs('wfg.master.destinasi.index') ? 'active' : '' }}"
                        data-key="t-input-mst_dest_wfg">
                        <i class="bx bx-git-commit fs-12"></i>
                        Master Destinasi</a>
                </li>
            </ul>
        </div>
    </li>
@endcan
