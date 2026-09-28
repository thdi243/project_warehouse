@if (auth()->user()->hasRole('super-admin') ||
        auth()->user()->hasAnyPermission(['pas-menu', 'pas-kempu']))
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('kempu.pas.*') ? '' : 'collapsed' }}" href="#sideBarPas"
            data-bs-toggle="collapse" role="button"
            aria-expanded="{{ request()->routeIs('kempu.pas.*') ? 'true' : 'false' }}" aria-controls="sideBarPas">
            <i class="ri-store-2-line"></i><span data-key="t-pas">PAS</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('kempu.pas.*') ? 'show' : '' }}" id="sideBarPas">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('kempu.pas.index') }}"
                        class="nav-link menu-link {{ request()->routeIs('kempu.pas.*') ? 'active' : '' }}">
                        <i class="bx bx-git-commit fs-12"></i>Scan Kempu
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endif
