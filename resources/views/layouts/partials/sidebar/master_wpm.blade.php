@can('permission', 'master-wpm')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->Is('master/wpm/*') ? 'collapsed' : '' }}" href="#sidebarMasterWpm"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->Is('master/wpm/*') ? 'true' : 'false' }}"
            aria-controls="sidebarMasterWpm">
            <i class="bx bx-package"></i>
            <span data-key="t-stock_op_wpm">Master WPM</span>
        </a>

        <div class="collapse menu-dropdown {{ request()->Is('master/wpm/*') ? 'show' : '' }}" id="sidebarMasterWpm">
            <ul class="nav nav-sm flex-column">
                @can('permission', 'wpm-master-barang')
                    <li class="nav-item">
                        <a href="{{ route('master.wpm.barang.index') }}"
                            class="nav-link {{ request()->routeIs('master.wpm.barang.index') ? 'active' : '' }}"
                            data-key="t-input-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Master Barang
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
