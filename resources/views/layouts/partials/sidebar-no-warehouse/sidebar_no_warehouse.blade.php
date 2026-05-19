<li class="nav-item">
    <a href="{{ url('/app/purchase-requesition/form') }}" target="_blank"
        class="nav-link menu-link {{ request()->Is('/app/purchase-requesition/form') ? 'active' : '' }}">
        <i class="bx bx-git-commit fs-12"></i> <span data-key="t-tkbm">Form PR</span>
    </a>
</li>
<li class="nav-item">
    <a href="{{ route('stock.pr.history') }}"
        class="nav-link menu-link {{ request()->routeIs('stock.pr.history') ? 'active' : '' }}">
        <i class="bx bx-git-commit fs-12"></i>Data Riwayat PR</a>
</li>
@if ($jabatan == 'dept_head')
    <li class="nav-item">
        <a href="{{ route('stock.pr.approval') }}"
            class="nav-link menu-link {{ request()->routeIs('stock.pr.approval') ? 'active' : '' }}">
            <i class="bx bx-git-commit fs-12"></i>Approval PR
        </a>
    </li>
@endif
