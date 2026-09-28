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
