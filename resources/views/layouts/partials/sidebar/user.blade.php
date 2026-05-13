@can('permission', 'manage-users')
    <li class="nav-item">
        <a href="{{ route('user.index') }}"
            class="nav-link menu-link {{ request()->routeIs('user.*') ? 'active' : '' }}">
            <i class="mdi mdi-folder-account"></i> <span data-key="t-tkbm">User</span>
        </a>
    </li>
@endcan
