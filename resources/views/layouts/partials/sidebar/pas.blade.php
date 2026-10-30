@can('permission', 'pas-kempu')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('kempu.pas.*') ? '' : 'collapsed' }}" href="#sideBarPas"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('kempu.pas.*') ? 'true' : 'false' }}"
            aria-controls="sideBarPas">
            <i class="ri-store-2-line"></i><span data-key="t-pas">PAS</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('kempu.pas.*') ? 'show' : '' }}" id="sideBarPas">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="#sidebarScanKempuPAS" data-bs-toggle="collapse" role="button"
                        class="nav-link {{ request()->routeIs('kempu.pas.*') ? '' : 'collapsed' }}"
                        aria-expanded="{{ request()->routeIs('kempu.pas.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarScanKempuPAS">
                        <i class="bx bx-git-commit fs-12"></i> Scan Kempu
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('kempu.pas.*') ? 'show' : '' }}"
                        id="sidebarScanKempuPAS">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('kempu.pas.index') }}"
                                    class="nav-link {{ request()->routeIs('kempu.pas.index') || request()->routeIs('kempu.pas.scan') ? 'active' : '' }}">
                                    <i class="bx bx-git-commit fs-12"></i>Scan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('kempu.pas.report') }}"
                                    class="nav-link {{ request()->routeIs('kempu.pas.report') ? 'active' : '' }}">
                                    <i class="bx bx-git-commit fs-12"></i>Report
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </li>
@endcan
