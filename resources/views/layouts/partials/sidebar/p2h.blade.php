@can('permission', 'p2h')
    <li class="nav-item">
        <a class="nav-link menu-link  {{ request()->routeIs('p2h.*') ? '' : 'collapsed' }}" href="#sideBarP2h"
            data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('p2h.*') ? 'true' : 'false' }}"
            aria-controls="sideBarP2h">
            <i class="mdi mdi-clipboard-check-multiple"></i> <span data-key="t-p2h">P2H</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('p2h.*') ? 'show' : '' }}" id="sideBarP2h">
            <ul class="nav nav-sm flex-column">

                @if ($jabatan === 'operator')
                    <li class="nav-item">
                        <a href="{{ route('p2h.online.index') }}"
                            class="nav-link {{ request()->routeIs('p2h.online.index') ? 'active' : '' }}"
                            data-key="t-input-p2h">
                            <i class="bx bx-git-commit fs-12"></i>Form P2H
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('p2h.online.data') }}"
                            class="nav-link {{ request()->routeIs('p2h.online.data') ? 'active' : '' }}" data-key="t-chat">
                            <i class="bx bx-git-commit fs-12"></i>Data P2H
                        </a>
                    </li>
                    @can('permission', 'p2h-summary')
                        <li class="nav-item">
                            <a href="{{ route('p2h.online.summary') }}"
                                class="nav-link {{ request()->routeIs('p2h.online.summary') ? 'active' : '' }}"
                                data-key="t-summary-p2h">
                                <i class="bx bx-git-commit fs-12"></i>Summary P2H
                            </a>
                        </li>
                    @endcan
                @else
                    <li class="nav-item">
                        <a href="#" data-bs-target="#sidebarP2hOnline" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('p2h.online.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarP2hOnline" class="nav-link" data-key="t-m-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>P2H Online
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('p2h.online.*') ? 'show' : '' }}"
                            id="sidebarP2hOnline">
                            <ul class="nav nav-sm flex-column">
                                @can('permission', 'p2h-form')
                                    <li class="nav-item">
                                        <a href="{{ route('p2h.online.index') }}"
                                            class="nav-link {{ request()->routeIs('p2h.online.index') ? 'active' : '' }}"
                                            data-key="t-input-p2h">
                                            Form P2H </a>
                                    </li>
                                @endcan
                                @can('permission', 'p2h-data')
                                    <li class="nav-item">
                                        <a href="{{ route('p2h.online.data') }}"
                                            class="nav-link {{ request()->routeIs('p2h.online.data') ? 'active' : '' }}"
                                            data-key="t-chat">
                                            Data P2H </a>
                                    </li>
                                @endcan
                                @can('permission', 'p2h-summary')
                                    <li class="nav-item">
                                        <a href="{{ route('p2h.online.summary') }}"
                                            class="nav-link {{ request()->routeIs('p2h.online.summary') ? 'active' : '' }}"
                                            data-key="t-summary-p2h">
                                            Summary P2H </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif

                @can('permission', 'p2h-unit-regis')
                    <li class="nav-item">
                        <a href="#" data-bs-target="#sidebarRegUnitP2h" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('p2h.registration.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarRegUnitP2h" class="nav-link" data-key="t-m-tkbm">
                            <i class="bx bx-git-commit fs-12"></i>Registrasi Unit P2H
                        </a>
                        <div class="collapse menu-dropdown {{ request()->routeIs('p2h.registration.*') ? 'show' : '' }}"
                            id="sidebarRegUnitP2h">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('p2h.registration.forklift') }}"
                                        class="nav-link {{ request()->routeIs('p2h.registration.forklift') ? 'active' : '' }}"
                                        data-key="t-fees">Registrasi Forklift</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('p2h.registration.pallet-mover') }}"
                                        class="nav-link {{ request()->routeIs('p2h.registration.pallet-mover') ? 'active' : '' }}"
                                        data-key="t-h-produk">Registrasi Pallet Mover</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endcan
            </ul>
        </div>
    </li>
@endcan
