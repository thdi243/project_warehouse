@can('permission', 'tkbm')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->is('tkbm/*') ? '' : 'collapsed' }}" href="#sideBarTkbm"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('tkbm/*') ? 'true' : 'false' }}"
            aria-controls="sideBarTkbm">
            <i class="mdi mdi-human-dolly"></i> <span data-key="t-tkbm">TKBM</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->is('tkbm/*') ? 'show' : '' }}" id="sideBarTkbm">
            <ul class="nav nav-sm flex-column">
                @can('permission', 'tkbm-bps')
                    <li class="nav-item">
                        <a href="#" data-bs-target="#sidebarTkbmBps" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->is('tkbm/bps/*') ? 'true' : 'false' }}" aria-controls="sidebarTkbmBps"
                            class="nav-link" data-key="t-m-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>BPS
                        </a>
                        <div class="collapse menu-dropdown {{ request()->is('tkbm/bps/*') ? 'show' : '' }}" id="sidebarTkbmBps">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('tkbm.stock') }}"
                                        class="nav-link menu-link {{ request()->routeIs('tkbm.stock') ? 'active' : '' }}"
                                        data-key="t-input-tkbm">
                                        Form BPS </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('tkbm.data') }}"
                                        class="nav-link menu-link {{ request()->routeIs('tkbm.data') ? 'active' : '' }}"
                                        data-key="t-tkbm">
                                        Report BPS </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endcan
                @can('permission', 'tkbm-ikat-terpal')
                    <li class="nav-item">
                        <a href="#" data-bs-target="#sidebarTkbmIkatTerpal" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->is('tkbm/ikat-terpal/*') ? 'true' : 'false' }}"
                            aria-controls="sidebarTkbmIkatTerpal" class="nav-link" data-key="t-m-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Ikat Terpal
                        </a>
                        <div class="collapse menu-dropdown {{ request()->is('tkbm/ikat-terpal/*') ? 'show' : '' }}"
                            id="sidebarTkbmIkatTerpal">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('tkbm.ikat-terpal.index') }}"
                                        class="nav-link menu-link {{ request()->routeIs('tkbm.ikat-terpal.index') ? 'active' : '' }}"
                                        data-key="t-input-tkbm">
                                        Form Ikat Terpal </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('tkbm.ikat-terpal.report') }}"
                                        class="nav-link menu-link {{ request()->routeIs('tkbm.ikat-terpal.report') ? 'active' : '' }}"
                                        data-key="t-tkbm">
                                        Report Ikat Terpal </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
