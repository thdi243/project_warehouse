@can('permission', 'master-wcp')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->Is('master/wcp/*') ? 'collapsed' : '' }}" href="#sidebarMasterWcp"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->Is('master/wcp/*') ? 'true' : 'false' }}"
            aria-controls="sidebarMasterWcp">
            <i class="bx bx-package"></i>
            <span data-key="t-stock_op_wcp">Master WCP</span>
        </a>

        <div class="collapse menu-dropdown {{ request()->Is('master/wcp/*') ? 'show' : '' }}" id="sidebarMasterWcp">
            <ul class="nav nav-sm flex-column">
                @can('permission', 'wcp-master-barang')
                    <li class="nav-item">
                        <a href="{{ route('master.wcp.barang.index') }}"
                            class="nav-link {{ request()->Is('master/wcp/*') ? 'active' : '' }}" data-key="t-input-tkbm-wcp">
                            <i class="bx bx-git-commit fs-12"></i>Master Barang
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
