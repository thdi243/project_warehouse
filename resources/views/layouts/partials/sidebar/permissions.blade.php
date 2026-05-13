@can('permission', 'manage-permissions')
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('admin.*') ? 'collapsed' : '' }}"
            href="#sidebarPermissions" data-bs-toggle="collapse" role="button"
            aria-expanded="{{ request()->routeIs('admin.*') ? 'true' : 'false' }}"
            aria-controls="sidebarPermissions">
            <i class="mdi mdi-shield-account"></i>
            <span data-key="t-stock_op_wfg">Permissions</span>
        </a>

        <div class="collapse menu-dropdown {{ request()->routeIs('admin.*') ? 'show' : '' }}"
            id="sidebarPermissions">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    @can('permission', 'super-admin')
                        <a href="{{ route('admin.permissions.index') }}"
                            class="nav-link menu-link {{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}">
                            <i class="bx bx-git-commit fs-12"></i> <span
                                data-key="t-tkbm">Permissions</span>
                        </a>
                        <a href="{{ route('admin.role.index') }}"
                            class="nav-link menu-link {{ request()->routeIs('admin.role.*') ? 'active' : '' }}">
                            <i class="bx bx-git-commit fs-12"></i> <span data-key="t-roles">Roles</span>
                        </a>
                    @endcan
                    <a href="{{ route('admin.permissions.users') }}"
                        class="nav-link menu-link {{ request()->routeIs('admin.permissions.users') ? 'active' : '' }}">
                        <i class="bx bx-git-commit fs-12"></i> <span data-key="t-tkbm">
                            Users Permissions</span>
                    </a>
                    <a href="{{ route('admin.user.roles_index') }}"
                        class="nav-link menu-link {{ request()->routeIs('admin.user.roles_index') ? 'active' : '' }}">
                        <i class="bx bx-git-commit fs-12"></i> <span data-key="t-user-roles">
                            Users Roles</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
@endcan
