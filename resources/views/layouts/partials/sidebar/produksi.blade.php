@can('permission', 'produksi-kempu')
    <li class="nav-item">
        <a href="{{ route('kempu.produksi.index') }}"
            class="nav-link menu-link {{ request()->routeIs('kempu.produksi.*') ? 'active' : '' }}">
            <i class="ri-settings-4-line"></i> <span data-key="t-produksi">Produksi</span>
        </a>
    </li>
@endcan
