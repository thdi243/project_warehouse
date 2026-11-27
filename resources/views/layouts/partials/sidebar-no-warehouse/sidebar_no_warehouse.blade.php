@if (strtolower(trim($departemen ?? '')) !== 'warehouse')
    <li class="nav-item">
        <a href="{{ route('stock.soh.user') }}"
            class="nav-link menu-link {{ request()->routeIs('stock.soh.user') ? 'active' : '' }}">
            <i class="bx bx-git-commit fs-12"></i> <span data-key="t-stock-gula">Stock On
                Hand</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ url('/app/purchase-requesition/form') }}" target="_blank"
            class="nav-link menu-link {{ request()->Is('/app/purchase-requesition/form') ? 'active' : '' }}">
            <i class="bx bx-git-commit fs-12"></i> <span data-key="t-tkbm">Form PR</span>
        </a>
    </li>
    {{-- <li class="nav-item">
        <a href="{{ route('stock.pr.history') }}"
            class="nav-link menu-link {{ request()->routeIs('stock.pr.history') ? 'active' : '' }}">
            <i class="bx bx-git-commit fs-12"></i><span> Data Riwayat PR</span>
        </a>
    </li> --}}
    <li class="nav-item">
        <a href="{{ route('stock.pr.report') }}"
            class="nav-link menu-link {{ request()->routeIs('stock.pr.report') ? 'active' : '' }}">
            <i class="bx bx-git-commit fs-12"></i><span> Riwayat & Report PR</span>
        </a>
    </li>
    @if ($jabatan == 'dept_head' || $jabatan == 'supervisor' || Auth::user()->hasRole('level_5_pr'))
        <li class="nav-item">
            <a href="{{ route('stock.pr.approval') }}"
                class="nav-link menu-link {{ request()->routeIs('stock.pr.approval') ? 'active' : '' }}">
                <i class="bx bx-git-commit fs-12"></i><span> Approval PR</span>
            </a>
        </li>
    @endif

    {{-- MRP Sparepart WSP --}}
    <li class="nav-item">
        <a href="#sidebarWSPMrp" data-bs-toggle="collapse" role="button"
            class="nav-link menu-link {{ request()->routeIs('stock.mrp.*') ? '' : 'collapsed' }}"
            aria-expanded="{{ request()->routeIs('stock.mrp.*') ? 'true' : 'false' }}" aria-controls="sidebarWSPMrp">
            <i class="bx bx-git-commit fs-12"></i> <span>MRP Sparepart</span>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('stock.mrp.*') ? 'show' : '' }}" id="sidebarWSPMrp">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('stock.mrp.master') }}"
                        class="nav-link {{ request()->routeIs('stock.mrp.master') ? 'active' : '' }}">
                        <i class="bx bx-git-commit fs-12"></i> <span>Master MRP</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('stock.mrp.request') }}"
                        class="nav-link {{ request()->routeIs('stock.mrp.request') && !request()->routeIs('stock.mrp.request.history') ? 'active' : '' }}">
                        <i class="bx bx-git-commit fs-12"></i> <span>Pengajuan MRP</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('stock.mrp.request.history') }}"
                        class="nav-link {{ request()->routeIs('stock.mrp.request.history') ? 'active' : '' }}">
                        <i class="bx bx-git-commit fs-12"></i> <span>Riwayat Pengajuan MRP</span>
                    </a>
                </li>
                @if ($jabatan == 'dept_head' || $jabatan == 'supervisor' || Auth::user()->hasRole('level_5_pr'))
                    <li class="nav-item">
                        <a href="{{ route('stock.mrp.approval') }}"
                            class="nav-link {{ request()->routeIs('stock.mrp.approval') ? 'active' : '' }}">
                            <i class="bx bx-git-commit fs-12"></i> Approval MRP
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </li>
@endif
