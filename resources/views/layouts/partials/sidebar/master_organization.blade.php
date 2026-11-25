@can('permission', 'manage-users')
    <li class="nav-item">
        <a href="{{ route('master.organization.index') }}"
            class="nav-link menu-link {{ request()->routeIs('master.organization.*') ? 'active' : '' }}">
            <i class="mdi mdi-sitemap-outline"></i> <span data-key="t-organization">Master Organisasi</span>
        </a>
    </li>
@endcan
