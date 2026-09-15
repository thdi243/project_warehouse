@extends('layouts.app')

@section('title', '| Master Item, Sloc & Vendor')

@section('content')
    <style>
        .pagination .page-link {
            color: #495057;
            border-color: #e9ebec;
            padding: 0.35rem 0.75rem;
            font-size: 0.8125rem;
            border-radius: 4px;
            margin: 0 2px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }
        .pagination .page-item.active .page-link {
            background-color: #3577f1;
            border-color: #3577f1;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(53, 119, 241, 0.3);
        }
        .pagination .page-item.disabled .page-link {
            color: #878a99;
            background-color: #f3f6f9;
            border-color: #e9ebec;
            cursor: not-allowed;
            pointer-events: none;
        }
        .pagination .page-link:hover:not(.disabled) {
            background-color: #eef0f2;
            color: #3577f1;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <!-- Breadcrumb -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Master Item, Sloc & Vendor</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">Master Data</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
                    <i class="ri-check-line me-2 align-middle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
                    <i class="ri-error-warning-line me-2 align-middle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @php
                $activeTab = 'items';
                if ($errors->has('s_loc') || session('tab') == 'sloc') {
                    $activeTab = 'sloc';
                } elseif ($errors->has('vendor_name') || session('tab') == 'vendor') {
                    $activeTab = 'vendor';
                }
            @endphp

            <!-- Tabs Navigation -->
            <div class="row mb-3">
                <div class="col-12">
                    <ul class="nav nav-tabs nav-tabs-custom nav-success" id="masterTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'items' ? 'active' : '' }}" data-bs-toggle="tab"
                                href="#itemsTab" role="tab">
                                <i class="ri-price-tag-3-line align-bottom me-1"></i> Master Items
                            </a>
                        </li>
                        @can('permission', 'super-admin')
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab == 'sloc' ? 'active' : '' }}" data-bs-toggle="tab"
                                    href="#slocTab" role="tab">
                                    <i class="ri-map-pin-line align-bottom me-1"></i> Master Sloc
                                </a>
                            </li>
                        @endcan
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'vendor' ? 'active' : '' }}" data-bs-toggle="tab"
                                href="#vendorTab" role="tab">
                                <i class="ri-store-2-line align-bottom me-1"></i> Master Vendor
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tabs Content -->
            <div class="tab-content text-muted">
                <!-- Items Tab Pane -->
                <div class="tab-pane {{ $activeTab == 'items' ? 'active' : '' }}" id="itemsTab" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header align-items-center d-flex flex-wrap gap-2 border-0 bg-transparent py-3">
                                    <h4 class="card-title mb-0 flex-grow-1">Daftar Item</h4>
                                    <div class="d-flex gap-2">
                                        <div class="search-box">
                                            <input type="text" class="form-control form-control-sm" id="searchItem"
                                                placeholder="Cari item atau area...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnAddItem">
                                            <i class="ri-add-line align-bottom me-1"></i> Tambah Item
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle text-nowrap" id="itemsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" width="70">No</th>
                                                    <th>Item Name</th>
                                                    <th>Area (Sloc)</th>
                                                    <th class="text-center" width="160">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($items as $index => $item)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td class="fw-medium">{{ $item->name }}</td>
                                                        <td>
                                                            @if ($item->location)
                                                                <span class="badge bg-soft-info text-info">
                                                                    {{ $item->location->s_loc }} - {{ $item->location->name }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="d-flex gap-1 justify-content-center">
                                                                <button type="button"
                                                                    class="btn btn-soft-primary btn-sm btn-edit"
                                                                    data-id="{{ $item->id }}"
                                                                    data-name="{{ $item->name }}"
                                                                    data-location-id="{{ $item->location_id }}"
                                                                    title="Edit">
                                                                    <i class="ri-edit-line me-1"></i> Edit
                                                                </button>
                                                                <button type="button"
                                                                    class="btn btn-soft-danger btn-sm btn-delete-item"
                                                                    data-id="{{ $item->id }}" title="Delete">
                                                                    <i class="ri-delete-bin-line me-1"></i> Delete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">Belum ada item terdaftar.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination Footer Items -->
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-3 border-top">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted small">Tampilkan:</span>
                                            <select class="form-select form-select-sm" id="itemsPerPageSelect" style="width: 75px;">
                                                <option value="10" selected>10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                            <span class="text-muted small" id="itemsPaginationInfo">Menampilkan 0 dari 0 item</span>
                                        </div>
                                        <nav aria-label="Navigasi Halaman Item">
                                            <ul class="pagination pagination-sm mb-0 justify-content-end" id="itemsPagination">
                                                <!-- Dynamic Pagination Generated via JS -->
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sloc Tab Pane -->
                @can('permission', 'super-admin')
                    <div class="tab-pane {{ $activeTab == 'sloc' ? 'active' : '' }}" id="slocTab" role="tabpanel">
                        <div class="row">
                            <div class="col-12">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header align-items-center d-flex flex-wrap gap-2 border-0 bg-transparent py-3">
                                        <h4 class="card-title mb-0 flex-grow-1">Daftar Sloc (Storage Locations)</h4>
                                        <div class="d-flex gap-2">
                                            <div class="search-box">
                                                <input type="text" class="form-control form-control-sm" id="searchSloc"
                                                    placeholder="Cari kode, nama, deskripsi...">
                                                <i class="ri-search-line search-icon"></i>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm" id="btnAddSloc">
                                                <i class="ri-add-line align-bottom me-1"></i> Tambah Sloc
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle text-nowrap" id="slocsTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-center" width="70">No</th>
                                                        <th width="140">Sloc Code</th>
                                                        <th>Name</th>
                                                        <th>Description</th>
                                                        <th class="text-center" width="160">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($locations as $index => $loc)
                                                        <tr>
                                                            <td class="text-center">{{ $index + 1 }}</td>
                                                            <td>
                                                                <span class="badge bg-soft-info text-info fs-12">{{ $loc->s_loc }}</span>
                                                            </td>
                                                            <td class="fw-medium">{{ $loc->name }}</td>
                                                            <td class="text-wrap text-muted small">{{ $loc->description ?? '-' }}</td>
                                                            <td class="text-center">
                                                                <div class="d-flex gap-1 justify-content-center">
                                                                    <button type="button"
                                                                        class="btn btn-soft-primary btn-sm btn-edit-sloc"
                                                                        data-id="{{ $loc->id }}"
                                                                        data-sloc="{{ $loc->s_loc }}"
                                                                        data-name="{{ $loc->name }}"
                                                                        data-description="{{ $loc->description }}"
                                                                        title="Edit">
                                                                        <i class="ri-edit-line me-1"></i> Edit
                                                                    </button>
                                                                    <button type="button"
                                                                        class="btn btn-soft-danger btn-sm btn-delete-sloc"
                                                                        data-id="{{ $loc->id }}" title="Delete">
                                                                        <i class="ri-delete-bin-line me-1"></i> Delete
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">Belum ada Sloc terdaftar.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Pagination Footer Sloc -->
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-3 border-top">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small">Tampilkan:</span>
                                                <select class="form-select form-select-sm" id="slocsPerPageSelect" style="width: 75px;">
                                                    <option value="10" selected>10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                                <span class="text-muted small" id="slocsPaginationInfo">Menampilkan 0 dari 0 sloc</span>
                                            </div>
                                            <nav aria-label="Navigasi Halaman Sloc">
                                                <ul class="pagination pagination-sm mb-0 justify-content-end" id="slocsPagination">
                                                    <!-- Dynamic Pagination Generated via JS -->
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                <!-- Vendor Tab Pane -->
                <div class="tab-pane {{ $activeTab == 'vendor' ? 'active' : '' }}" id="vendorTab" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header align-items-center d-flex flex-wrap gap-2 border-0 bg-transparent py-3">
                                    <h4 class="card-title mb-0 flex-grow-1">Daftar Vendor</h4>
                                    <div class="d-flex gap-2">
                                        <div class="search-box">
                                            <input type="text" class="form-control form-control-sm" id="searchVendor"
                                                placeholder="Cari vendor...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnAddVendor">
                                            <i class="ri-add-line align-bottom me-1"></i> Tambah Vendor
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle text-nowrap" id="vendorsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" width="70">No</th>
                                                    <th>Vendor Name</th>
                                                    <th>Description</th>
                                                    <th class="text-center" width="160">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($vendors as $index => $v)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td><strong class="text-primary">{{ $v->name }}</strong></td>
                                                        <td class="text-wrap text-muted small">{{ $v->description ?? '-' }}</td>
                                                        <td class="text-center">
                                                            <div class="d-flex gap-1 justify-content-center">
                                                                <button type="button"
                                                                    class="btn btn-soft-primary btn-sm btn-edit-vendor"
                                                                    data-id="{{ $v->id }}"
                                                                    data-name="{{ $v->name }}"
                                                                    data-description="{{ $v->description }}"
                                                                    title="Edit">
                                                                    <i class="ri-edit-line me-1"></i> Edit
                                                                </button>
                                                                <button type="button"
                                                                    class="btn btn-soft-danger btn-sm btn-delete-vendor"
                                                                    data-id="{{ $v->id }}" title="Delete">
                                                                    <i class="ri-delete-bin-line me-1"></i> Delete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">Belum ada vendor terdaftar.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination Footer Vendor -->
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-3 border-top">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted small">Tampilkan:</span>
                                            <select class="form-select form-select-sm" id="vendorsPerPageSelect" style="width: 75px;">
                                                <option value="10" selected>10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                            <span class="text-muted small" id="vendorsPaginationInfo">Menampilkan 0 dari 0 vendor</span>
                                        </div>
                                        <nav aria-label="Navigasi Halaman Vendor">
                                            <ul class="pagination pagination-sm mb-0 justify-content-end" id="vendorsPagination">
                                                <!-- Dynamic Pagination Generated via JS -->
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Master Item -->
    <div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="itemModalTitle">Tambah Item Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('vehicle.monitoring.master.items.store') }}" method="POST" id="itemForm">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                placeholder="Contoh: Gula Pasir">
                        </div>
                        <div class="mb-3">
                            <label for="location_id" class="form-label">Area (Sloc)</label>
                            <select class="form-select" id="location_id" name="location_id">
                                <option value="" selected>Semua Area / General</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->s_loc }} - {{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Master Sloc -->
    @can('permission', 'super-admin')
        <div class="modal fade" id="slocModal" tabindex="-1" aria-labelledby="slocModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light p-3">
                        <h5 class="modal-title" id="slocModalTitle">Tambah Sloc Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('vehicle.monitoring.master.sloc.store') }}" method="POST" id="slocForm">
                        @csrf
                        <input type="hidden" name="_method" id="slocFormMethod" value="POST">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="s_loc" class="form-label">Sloc Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="s_loc" name="s_loc" required
                                    placeholder="Contoh: TMB" style="text-transform: uppercase;">
                            </div>
                            <div class="mb-3">
                                <label for="sloc_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sloc_name" name="name" required
                                    placeholder="Contoh: Timbangan (Scales)">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Keterangan area (opsional)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 bg-light p-3">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="btnSubmitSloc">Simpan Sloc</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <!-- Modal Master Vendor -->
    <div class="modal fade" id="vendorModal" tabindex="-1" aria-labelledby="vendorModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="vendorModalTitle">Tambah Vendor Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('vehicle.monitoring.master.vendor.store') }}" method="POST" id="vendorForm">
                    @csrf
                    <input type="hidden" name="_method" id="vendorFormMethod" value="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="vendor_name" class="form-label">Vendor Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vendor_name" name="vendor_name" required
                                placeholder="Contoh: PT. Fast Transport">
                        </div>
                        <div class="mb-3">
                            <label for="vendor_description" class="form-label">Description</label>
                            <textarea class="form-control" id="vendor_description" name="description" rows="3"
                                placeholder="Keterangan vendor (opsional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitVendor">Simpan Vendor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Helper function to safely escape HTML
            function escapeHtml(text) {
                if (text === null || text === undefined) return '';
                return $('<div>').text(text).html();
            }

            // Remember active tab in localStorage
            $('#masterTabs a').on('shown.bs.tab', function(e) {
                localStorage.setItem('activeMasterTab', $(e.target).attr('href'));
            });

            // Restore active tab from localStorage if no session tab override or validation errors
            @if (!session('tab') && !$errors->has('s_loc') && !$errors->has('vendor_name'))
                const activeTab = localStorage.getItem('activeMasterTab');
                if (activeTab) {
                    const tabTriggerEl = document.querySelector(`#masterTabs a[href="${activeTab}"]`);
                    if (tabTriggerEl) {
                        const tab = new bootstrap.Tab(tabTriggerEl);
                        tab.show();
                    }
                }
            @endif

            // Local data cache bootstrapped with server data
            let allItems = @json($items);
            let allLocations = @json($locations);
            let allVendors = @json($vendors);

            let currentItemPage = 1;
            let itemsPerPage = 10;
            let totalItemPages = 1;

            let currentSlocPage = 1;
            let slocsPerPage = 10;
            let totalSlocPages = 1;

            let currentVendorPage = 1;
            let vendorsPerPage = 10;
            let totalVendorPages = 1;

            // Smart pagination generator: prev 1 ... 4 5 6 ... n next
            function getPaginationPages(currentPage, totalPages) {
                if (totalPages <= 7) {
                    let pages = [];
                    for (let i = 1; i <= totalPages; i++) pages.push(i);
                    return pages;
                }

                if (currentPage <= 4) {
                    return [1, 2, 3, 4, 5, '...', totalPages];
                } else if (currentPage >= totalPages - 3) {
                    return [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
                } else {
                    return [1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages];
                }
            }

            // Render Pagination Links Generic
            function renderPagination(containerId, currentPage, totalPages) {
                if (totalPages <= 0) {
                    $(containerId).html('');
                    return;
                }

                let paginationHtml = '';

                // Prev button
                const isPrevDisabled = currentPage <= 1;
                paginationHtml += `<li class="page-item ${isPrevDisabled ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}" ${isPrevDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>
                        <i class="ri-arrow-left-s-line me-1 align-middle"></i>Prev
                    </a>
                </li>`;

                // Page numbers
                const pages = getPaginationPages(currentPage, totalPages);
                pages.forEach(p => {
                    if (p === '...') {
                        paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    } else {
                        const isActive = p === currentPage;
                        paginationHtml += `<li class="page-item ${isActive ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${p}">${p}</a>
                        </li>`;
                    }
                });

                // Next button
                const isNextDisabled = currentPage >= totalPages;
                paginationHtml += `<li class="page-item ${isNextDisabled ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}" ${isNextDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>
                        Next<i class="ri-arrow-right-s-line ms-1 align-middle"></i>
                    </a>
                </li>`;

                $(containerId).html(paginationHtml);
            }

            // Helper to render Items Table with Pagination
            function renderItemsTable(query = '') {
                const q = query.trim().toLowerCase();
                const filtered = allItems.filter(item => {
                    if (!q) return true;
                    const nameMatch = (item.name || '').toLowerCase().includes(q);
                    const locMatch = item.location && (
                        (item.location.s_loc || '').toLowerCase().includes(q) ||
                        (item.location.name || '').toLowerCase().includes(q)
                    );
                    return nameMatch || locMatch;
                });

                const totalItems = filtered.length;
                totalItemPages = Math.ceil(totalItems / itemsPerPage) || 1;

                if (currentItemPage > totalItemPages) {
                    currentItemPage = totalItemPages;
                }
                if (currentItemPage < 1) {
                    currentItemPage = 1;
                }

                const startIndex = (currentItemPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
                const paginatedItems = filtered.slice(startIndex, endIndex);

                let html = '';
                if (totalItems === 0) {
                    html = `<tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            ${q ? 'Tidak ada data item yang sesuai dengan pencarian.' : 'Belum ada item terdaftar.'}
                        </td>
                    </tr>`;
                } else {
                    paginatedItems.forEach(function(item, index) {
                        const areaText = item.location ?
                            `<span class="badge bg-soft-info text-info">${escapeHtml(item.location.s_loc)} - ${escapeHtml(item.location.name)}</span>` :
                            `<span class="text-muted">-</span>`;

                        const rowNumber = startIndex + index + 1;

                        html += `<tr>
                            <td class="text-center">${rowNumber}</td>
                            <td class="fw-medium">${escapeHtml(item.name)}</td>
                            <td>${areaText}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-soft-primary btn-sm btn-edit"
                                        data-id="${item.id}"
                                        data-name="${escapeHtml(item.name)}"
                                        data-location-id="${item.location_id || ''}" title="Edit">
                                        <i class="ri-edit-line me-1"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete-item"
                                        data-id="${item.id}" title="Delete">
                                        <i class="ri-delete-bin-line me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                $('#itemsTable tbody').html(html);

                // Update pagination info & links
                if (totalItems === 0) {
                    $('#itemsPaginationInfo').text('Menampilkan 0 dari 0 item');
                } else {
                    $('#itemsPaginationInfo').text(`Menampilkan ${startIndex + 1} - ${endIndex} dari ${totalItems} item`);
                }

                renderPagination('#itemsPagination', currentItemPage, totalItemPages);
            }

            // Helper to render Slocs Table with Pagination
            function renderSlocsTable(query = '') {
                const q = query.trim().toLowerCase();
                const filtered = allLocations.filter(loc => {
                    if (!q) return true;
                    const codeMatch = (loc.s_loc || '').toLowerCase().includes(q);
                    const nameMatch = (loc.name || '').toLowerCase().includes(q);
                    const descMatch = (loc.description || '').toLowerCase().includes(q);
                    return codeMatch || nameMatch || descMatch;
                });

                const totalSlocs = filtered.length;
                totalSlocPages = Math.ceil(totalSlocs / slocsPerPage) || 1;

                if (currentSlocPage > totalSlocPages) {
                    currentSlocPage = totalSlocPages;
                }
                if (currentSlocPage < 1) {
                    currentSlocPage = 1;
                }

                const startIndex = (currentSlocPage - 1) * slocsPerPage;
                const endIndex = Math.min(startIndex + slocsPerPage, totalSlocs);
                const paginatedSlocs = filtered.slice(startIndex, endIndex);

                let html = '';
                if (totalSlocs === 0) {
                    html = `<tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            ${q ? 'Tidak ada data Sloc yang sesuai dengan pencarian.' : 'Belum ada Sloc terdaftar.'}
                        </td>
                    </tr>`;
                } else {
                    paginatedSlocs.forEach(function(loc, index) {
                        const rowNumber = startIndex + index + 1;
                        html += `<tr>
                            <td class="text-center">${rowNumber}</td>
                            <td><span class="badge bg-soft-info text-info fs-12">${escapeHtml(loc.s_loc)}</span></td>
                            <td class="fw-medium">${escapeHtml(loc.name)}</td>
                            <td class="text-wrap text-muted small">${escapeHtml(loc.description || '-')}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-soft-primary btn-sm btn-edit-sloc"
                                        data-id="${loc.id}"
                                        data-sloc="${escapeHtml(loc.s_loc)}"
                                        data-name="${escapeHtml(loc.name)}"
                                        data-description="${escapeHtml(loc.description || '')}"
                                        title="Edit">
                                        <i class="ri-edit-line me-1"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete-sloc"
                                        data-id="${loc.id}" title="Delete">
                                        <i class="ri-delete-bin-line me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                $('#slocsTable tbody').html(html);

                // Update pagination info & links
                if (totalSlocs === 0) {
                    $('#slocsPaginationInfo').text('Menampilkan 0 dari 0 sloc');
                } else {
                    $('#slocsPaginationInfo').text(`Menampilkan ${startIndex + 1} - ${endIndex} dari ${totalSlocs} sloc`);
                }

                renderPagination('#slocsPagination', currentSlocPage, totalSlocPages);
            }

            // Helper to render Vendors Table with Pagination
            function renderVendorsTable(query = '') {
                const q = query.trim().toLowerCase();
                const filtered = allVendors.filter(v => {
                    if (!q) return true;
                    const nameMatch = (v.name || '').toLowerCase().includes(q);
                    const descMatch = (v.description || '').toLowerCase().includes(q);
                    return nameMatch || descMatch;
                });

                const totalVendors = filtered.length;
                totalVendorPages = Math.ceil(totalVendors / vendorsPerPage) || 1;

                if (currentVendorPage > totalVendorPages) {
                    currentVendorPage = totalVendorPages;
                }
                if (currentVendorPage < 1) {
                    currentVendorPage = 1;
                }

                const startIndex = (currentVendorPage - 1) * vendorsPerPage;
                const endIndex = Math.min(startIndex + vendorsPerPage, totalVendors);
                const paginatedVendors = filtered.slice(startIndex, endIndex);

                let html = '';
                if (totalVendors === 0) {
                    html = `<tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            ${q ? 'Tidak ada data vendor yang sesuai dengan pencarian.' : 'Belum ada vendor terdaftar.'}
                        </td>
                    </tr>`;
                } else {
                    paginatedVendors.forEach(function(v, index) {
                        const rowNumber = startIndex + index + 1;
                        html += `<tr>
                            <td class="text-center">${rowNumber}</td>
                            <td><strong class="text-primary">${escapeHtml(v.name)}</strong></td>
                            <td class="text-wrap text-muted small">${escapeHtml(v.description || '-')}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-soft-primary btn-sm btn-edit-vendor"
                                        data-id="${v.id}"
                                        data-name="${escapeHtml(v.name)}"
                                        data-description="${escapeHtml(v.description || '')}"
                                        title="Edit">
                                        <i class="ri-edit-line me-1"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete-vendor"
                                        data-id="${v.id}" title="Delete">
                                        <i class="ri-delete-bin-line me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                $('#vendorsTable tbody').html(html);

                // Update pagination info & links
                if (totalVendors === 0) {
                    $('#vendorsPaginationInfo').text('Menampilkan 0 dari 0 vendor');
                } else {
                    $('#vendorsPaginationInfo').text(`Menampilkan ${startIndex + 1} - ${endIndex} dari ${totalVendors} vendor`);
                }

                renderPagination('#vendorsPagination', currentVendorPage, totalVendorPages);
            }

            // AJAX Data Loader
            function loadMasterData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.master.items.data') }}",
                    type: "GET",
                    success: function(response) {
                        allItems = response.items || [];
                        allLocations = response.locations || [];
                        allVendors = response.vendors || [];

                        // Refresh item select options in modal
                        let locationOptions = '<option value="" selected>Semua Area / General</option>';
                        allLocations.forEach(function(loc) {
                            locationOptions += `<option value="${loc.id}">${escapeHtml(loc.s_loc)} - ${escapeHtml(loc.name)}</option>`;
                        });
                        $('#location_id').html(locationOptions);

                        renderItemsTable($('#searchItem').val() || '');
                        renderSlocsTable($('#searchSloc').val() || '');
                        renderVendorsTable($('#searchVendor').val() || '');
                    },
                    error: function(xhr) {
                        console.error("Gagal memuat data master:", xhr);
                    }
                });
            }

            // Load initial master data via AJAX
            loadMasterData();

            // Realtime search inputs
            $('#searchItem').on('input', function() {
                currentItemPage = 1;
                renderItemsTable($(this).val());
            });

            $('#searchSloc').on('input', function() {
                currentSlocPage = 1;
                renderSlocsTable($(this).val());
            });

            $('#searchVendor').on('input', function() {
                currentVendorPage = 1;
                renderVendorsTable($(this).val());
            });

            // Pagination page-link click handlers
            $(document).on('click', '#itemsPagination .page-link', function(e) {
                e.preventDefault();
                const targetPage = parseInt($(this).data('page'));
                if (targetPage && targetPage >= 1 && targetPage <= totalItemPages && targetPage !== currentItemPage) {
                    currentItemPage = targetPage;
                    renderItemsTable($('#searchItem').val() || '');
                }
            });

            $(document).on('click', '#slocsPagination .page-link', function(e) {
                e.preventDefault();
                const targetPage = parseInt($(this).data('page'));
                if (targetPage && targetPage >= 1 && targetPage <= totalSlocPages && targetPage !== currentSlocPage) {
                    currentSlocPage = targetPage;
                    renderSlocsTable($('#searchSloc').val() || '');
                }
            });

            $(document).on('click', '#vendorsPagination .page-link', function(e) {
                e.preventDefault();
                const targetPage = parseInt($(this).data('page'));
                if (targetPage && targetPage >= 1 && targetPage <= totalVendorPages && targetPage !== currentVendorPage) {
                    currentVendorPage = targetPage;
                    renderVendorsTable($('#searchVendor').val() || '');
                }
            });

            // Page size change handlers
            $('#itemsPerPageSelect').on('change', function() {
                itemsPerPage = parseInt($(this).val()) || 10;
                currentItemPage = 1;
                renderItemsTable($('#searchItem').val() || '');
            });

            $('#slocsPerPageSelect').on('change', function() {
                slocsPerPage = parseInt($(this).val()) || 10;
                currentSlocPage = 1;
                renderSlocsTable($('#searchSloc').val() || '');
            });

            $('#vendorsPerPageSelect').on('change', function() {
                vendorsPerPage = parseInt($(this).val()) || 10;
                currentVendorPage = 1;
                renderVendorsTable($('#searchVendor').val() || '');
            });

            // Initial render
            renderItemsTable();
            renderSlocsTable();
            renderVendorsTable();

            /* ========================================================
               ITEM MODAL & ACTIONS
               ======================================================== */
            // Add Item button handler
            $('#btnAddItem').on('click', function() {
                $('#itemModalTitle').text('Tambah Item Baru');
                $('#itemForm')[0].reset();
                $('#name').val('');
                $('#location_id').val('');

                $('#itemForm').attr('action', "{{ route('vehicle.monitoring.master.items.store') }}");
                $('#formMethod').val('POST');
                $('#btnSubmit').text('Simpan Item').removeClass('btn-success').addClass('btn-primary');

                $('#itemModal').modal('show');
            });

            // Edit Item button handler
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const locationId = $(this).data('location-id');

                $('#itemModalTitle').text('Edit Item');
                $('#name').val(name);
                $('#location_id').val(locationId || '');

                $('#itemForm').attr('action', `{{ url('vehicle-monitoring/master/items/update') }}/${id}`);
                $('#formMethod').val('PUT');
                $('#btnSubmit').text('Perbarui Item').removeClass('btn-primary').addClass('btn-success');

                $('#itemModal').modal('show');
            });

            // Submit Item Form via AJAX
            $('#itemForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const method = $('#formMethod').val();

                $('#btnSubmit').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...'
                );

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        $('#itemModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadMasterData();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Gagal menyimpan item.'
                        });
                    },
                    complete: function() {
                        $('#btnSubmit').prop('disabled', false).text(method === 'PUT' ?
                            'Perbarui Item' : 'Simpan Item');
                    }
                });
            });

            // SweetAlert Delete confirmation for Item
            $(document).on('click', '.btn-delete-item', function() {
                const id = $(this).data('id');
                const url = `{{ url('vehicle-monitoring/master/items/delete') }}/${id}`;

                Swal.fire({
                    title: 'Hapus Item?',
                    text: "Apakah Anda yakin ingin menghapus data Item ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: "DELETE"
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                loadMasterData();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'Gagal menghapus item.'
                                });
                            }
                        });
                    }
                });
            });

            /* ========================================================
               SLOC MODAL & ACTIONS
               ======================================================== */
            // Add Sloc button handler
            $('#btnAddSloc').on('click', function() {
                $('#slocModalTitle').text('Tambah Sloc Baru');
                $('#slocForm')[0].reset();
                $('#s_loc').val('');
                $('#sloc_name').val('');
                $('#description').val('');

                $('#slocForm').attr('action', "{{ route('vehicle.monitoring.master.sloc.store') }}");
                $('#slocFormMethod').val('POST');
                $('#btnSubmitSloc').text('Simpan Sloc').removeClass('btn-success').addClass('btn-primary');

                $('#slocModal').modal('show');
            });

            // Edit Sloc button handler
            $(document).on('click', '.btn-edit-sloc', function() {
                const id = $(this).data('id');
                const sloc = $(this).data('sloc');
                const name = $(this).data('name');
                const description = $(this).data('description');

                $('#slocModalTitle').text('Edit Sloc');
                $('#s_loc').val(sloc);
                $('#sloc_name').val(name);
                $('#description').val(description);

                $('#slocForm').attr('action', `{{ url('vehicle-monitoring/master/sloc/update') }}/${id}`);
                $('#slocFormMethod').val('PUT');
                $('#btnSubmitSloc').text('Perbarui Sloc').removeClass('btn-primary').addClass('btn-success');

                $('#slocModal').modal('show');
            });

            // Submit Sloc Form via AJAX
            $('#slocForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const method = $('#slocFormMethod').val();

                $('#btnSubmitSloc').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...'
                );

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        $('#slocModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadMasterData();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Gagal menyimpan Sloc.'
                        });
                    },
                    complete: function() {
                        $('#btnSubmitSloc').prop('disabled', false).text(method === 'PUT' ?
                            'Perbarui Sloc' : 'Simpan Sloc');
                    }
                });
            });

            // SweetAlert Delete confirmation for Sloc
            $(document).on('click', '.btn-delete-sloc', function() {
                const id = $(this).data('id');
                const url = `{{ url('vehicle-monitoring/master/sloc/delete') }}/${id}`;

                Swal.fire({
                    title: 'Hapus Sloc?',
                    text: "Apakah Anda yakin ingin menghapus data Sloc ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: "DELETE"
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                loadMasterData();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'Gagal menghapus Sloc.'
                                });
                            }
                        });
                    }
                });
            });

            /* ========================================================
               VENDOR MODAL & ACTIONS
               ======================================================== */
            // Add Vendor button handler
            $('#btnAddVendor').on('click', function() {
                $('#vendorModalTitle').text('Tambah Vendor Baru');
                $('#vendorForm')[0].reset();
                $('#vendor_name').val('');
                $('#vendor_description').val('');

                $('#vendorForm').attr('action', "{{ route('vehicle.monitoring.master.vendor.store') }}");
                $('#vendorFormMethod').val('POST');
                $('#btnSubmitVendor').text('Simpan Vendor').removeClass('btn-success').addClass('btn-primary');

                $('#vendorModal').modal('show');
            });

            // Edit Vendor button handler
            $(document).on('click', '.btn-edit-vendor', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const description = $(this).data('description');

                $('#vendorModalTitle').text('Edit Vendor');
                $('#vendor_name').val(name);
                $('#vendor_description').val(description);

                $('#vendorForm').attr('action', `{{ url('vehicle-monitoring/master/vendor/update') }}/${id}`);
                $('#vendorFormMethod').val('PUT');
                $('#btnSubmitVendor').text('Perbarui Vendor').removeClass('btn-primary').addClass('btn-success');

                $('#vendorModal').modal('show');
            });

            // Submit Vendor Form via AJAX
            $('#vendorForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const method = $('#vendorFormMethod').val();

                $('#btnSubmitVendor').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...'
                );

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        $('#vendorModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadMasterData();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Gagal menyimpan vendor.'
                        });
                    },
                    complete: function() {
                        $('#btnSubmitVendor').prop('disabled', false).text(method === 'PUT' ?
                            'Perbarui Vendor' : 'Simpan Vendor');
                    }
                });
            });

            // SweetAlert Delete confirmation for Vendor
            $(document).on('click', '.btn-delete-vendor', function() {
                const id = $(this).data('id');
                const url = `{{ url('vehicle-monitoring/master/vendor/delete') }}/${id}`;

                Swal.fire({
                    title: 'Hapus Vendor?',
                    text: "Apakah Anda yakin ingin menghapus data Vendor ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: "DELETE"
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                loadMasterData();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'Gagal menghapus vendor.'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
