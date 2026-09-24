@can('permission', 'master-kempu')
    <li class="nav-item">
        <a href="{{ route('kempu.master.index') }}"
            class="nav-link menu-link {{ request()->routeIs('kempu.master.index') ? 'active' : '' }}">
            <i class="ri-database-2-line"></i> <span data-key="t-tkbm">Master Kempu</span>
        </a>
    </li>
    {{-- <li class="nav-item">
        <a class="nav-link menu-link {{ request()->is('kempu*') ? 'active' : '' }}" href="#sidebarMasterKempu"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('kempu*') ? 'true' : 'false' }}"
            aria-controls="sidebarMasterKempu">
            <i class="ri-database-2-line"></i>
            <span data-key="t-kempu">Master Kempu</span>
        </a>

        <div class="collapse menu-dropdown {{ request()->is('kempu*') ? 'show' : '' }}" id="sidebarMasterKempu">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('kempu.master.index') }}"
                        class="nav-link {{ request()->routeIs('kempu.master.*') ? 'active' : '' }}"
                        data-key="t-master-kempu">
                        <i class="bx bx-git-commit fs-12"></i><span>Master Kempu</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kempu.traceability.hub') }}"
                        class="nav-link {{ request()->routeIs('kempu.traceability.*') ? 'active' : '' }}"
                        data-key="t-trace-kempu">
                        <i class="bx bx-git-commit fs-12"></i><span>Tracebility</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kempu.qc.index') }}"
                        class="nav-link {{ request()->routeIs('kempu.qc.*') ? 'active' : '' }}"
                        data-key="t-qc-kempu">
                        <i class="bx bx-git-commit fs-12"></i><span>QC</span>
                    </a>
                </li>
            </ul>
        </div>
    </li> --}}
@endcan
