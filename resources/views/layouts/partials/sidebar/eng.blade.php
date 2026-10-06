@can('permission', 'eng-kempu')
    @php
        $isEngKempuActive =
            request()->routeIs('kempu.eng.*') ||
            request()->routeIs('kempu.produksi.*') ||
            request()->routeIs('kempu.qc.*');
    @endphp
    <li class="nav-item">
        <a class="nav-link menu-link {{ $isEngKempuActive ? '' : 'collapsed' }}" href="#sidebarEng" data-bs-toggle="collapse"
            role="button" aria-expanded="{{ $isEngKempuActive ? 'true' : 'false' }}" aria-controls="sidebarEng">
            <i class="mdi mdi-database"></i> <span data-key="t-eng">Kempu</span>
        </a>
        <div class="collapse menu-dropdown {{ $isEngKempuActive ? 'show' : '' }}" id="sidebarEng">
            <ul class="nav nav-sm flex-column">
                @can('permission', 'produksi-kempu')
                    <li class="nav-item">
                        <a href="{{ route('kempu.produksi.index') }}"
                            class="nav-link menu-link {{ request()->routeIs('kempu.produksi.*') ? 'active' : '' }}">
                            <i class="ri-settings-4-line"></i> <span data-key="t-produksi">Produksi</span>
                        </a>
                    </li>
                @endcan
                <li class="nav-item">
                    <a href="{{ route('kempu.eng.scan') }}"
                        class="nav-link {{ request()->routeIs('kempu.eng.scan') ? 'active' : '' }}" data-key="t-eng-repair">
                        <i class="ri-hammer-line fs-14 me-1"></i> Eng
                    </a>
                </li>
                @can('permission', 'qc-kempu')
                    <li class="nav-item">
                        <a href="{{ route('kempu.qc.pm.index') }}"
                            class="nav-link menu-link {{ request()->routeIs('kempu.qc.pm.*') || (request()->routeIs('kempu.qc.scan') && request()->route('type') === 'qc-pm') ? 'active' : '' }}">
                            <i class="ri-shield-check-line"></i> <span data-key="t-qc-pm">QC PM</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('kempu.qc.proses.index') }}"
                            class="nav-link menu-link {{ request()->routeIs('kempu.qc.proses.*') || (request()->routeIs('kempu.qc.scan') && in_array(request()->route('type'), ['qc-proses', 'qc-pre-cuci', 'qc-after-filling'])) ? 'active' : '' }}">
                            <i class="ri-flask-line"></i> <span data-key="t-qc-proses">QC Proses</span>
                        </a>
                    </li>
                @endcan
                <li class="nav-item">
                    <a href="{{ route('dashboard.kempu') }}"
                        class="nav-link menu-link {{ request()->routeIs('dashboard.kempu*') ? 'active' : '' }}">
                        <i class="ri-route-line"></i> <span data-key="t-traceability">Traceability & Monitoring</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endcan
