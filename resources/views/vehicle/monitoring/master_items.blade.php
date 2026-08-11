@extends('layouts.app')

@section('title', '| Master Item, Sloc & Vendor')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
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

            <div class="row mb-3">
                <div class="col-12">
                    <ul class="nav nav-tabs nav-tabs-custom nav-success" id="masterTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'items' ? 'active' : '' }}" data-bs-toggle="tab"
                                href="#itemsTab" role="tab">
                                <i class="ri-price-tag-3-line align-bottom me-1"></i> Master Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'sloc' ? 'active' : '' }}" data-bs-toggle="tab"
                                href="#slocTab" role="tab">
                                <i class="ri-map-pin-line align-bottom me-1"></i> Master Sloc
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'vendor' ? 'active' : '' }}" data-bs-toggle="tab"
                                href="#vendorTab" role="tab">
                                <i class="ri-store-2-line align-bottom me-1"></i> Master Vendor
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content text-muted">
                <!-- Items Tab Pane -->
                <div class="tab-pane {{ $activeTab == 'items' ? 'active' : '' }}" id="itemsTab" role="tabpanel">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card shadow-sm border-0">
                                <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                                    <h4 class="card-title mb-0 flex-grow-1">Daftar Item</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle text-nowrap" id="itemsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" width="70">No</th>
                                                    <th>Item Name</th>
                                                    <th>Area (Sloc)</th>
                                                    <th class="text-center" width="120">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($items as $index => $item)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>{{ $item->name }}</td>
                                                        <td>
                                                            @if ($item->location)
                                                                <span class="badge bg-soft-info text-info">{{ $item->location->s_loc }} - {{ $item->location->name }}</span>
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
                                                                    data-location-id="{{ $item->location_id }}" title="Edit">
                                                                    <i class="ri-edit-line"></i>
                                                                </button>
                                                                <form
                                                                    action="{{ route('vehicle.monitoring.master.items.delete', $item->id) }}"
                                                                    method="POST" class="d-inline form-delete">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn btn-soft-danger btn-sm" title="Delete">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">Belum ada
                                                            item terdaftar.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
 
                        <div class="col-md-5">
                            <div class="card shadow-sm border-0" id="formCard">
                                <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                                    <h4 class="card-title mb-0 flex-grow-1" id="formTitle">Tambah Item Baru</h4>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('vehicle.monitoring.master.items.store') }}" method="POST"
                                        id="itemForm">
                                        @csrf
                                        <input type="hidden" name="_method" id="formMethod" value="POST">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Item Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                required placeholder="Contoh: Gula Pasir">
                                            @error('name')
                                                @if (!$errors->has('s_loc') && !$errors->has('vendor_name'))
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @endif
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="location_id" class="form-label">Area (Sloc)</label>
                                            <select class="form-select" id="location_id" name="location_id">
                                                <option value="" selected>Semua Area / General</option>
                                                @foreach ($locations as $loc)
                                                    <option value="{{ $loc->id }}">{{ $loc->s_loc }} - {{ $loc->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('location_id')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="d-flex gap-2 justify-content-end mt-4">
                                            <button type="button" class="btn btn-light" id="btnCancel"
                                                style="display: none;">Batal</button>
                                            <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan
                                                Item</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sloc Tab Pane -->
                <div class="tab-pane {{ $activeTab == 'sloc' ? 'active' : '' }}" id="slocTab" role="tabpanel">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card shadow-sm border-0">
                                <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                                    <h4 class="card-title mb-0 flex-grow-1">Daftar Sloc (Storage Locations)</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle text-nowrap" id="slocsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" width="70">No</th>
                                                    <th width="120">Sloc Code</th>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th class="text-center" width="120">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($locations as $index => $loc)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td><span
                                                                class="badge bg-soft-info text-info fs-12">{{ $loc->s_loc }}</span>
                                                        </td>
                                                        <td>{{ $loc->name }}</td>
                                                        <td class="text-wrap">{{ $loc->description ?? '-' }}</td>
                                                        <td class="text-center">
                                                            <div class="d-flex gap-1 justify-content-center">
                                                                <button type="button"
                                                                    class="btn btn-soft-primary btn-sm btn-edit-sloc"
                                                                    data-id="{{ $loc->id }}"
                                                                    data-sloc="{{ $loc->s_loc }}"
                                                                    data-name="{{ $loc->name }}"
                                                                    data-description="{{ $loc->description }}"
                                                                    title="Edit">
                                                                    <i class="ri-edit-line"></i>
                                                                </button>
                                                                <form
                                                                    action="{{ route('vehicle.monitoring.master.sloc.delete', $loc->id) }}"
                                                                    method="POST" class="d-inline form-delete-sloc">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn btn-soft-danger btn-sm" title="Delete">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">Belum ada
                                                            Sloc terdaftar.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card shadow-sm border-0" id="slocFormCard">
                                <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                                    <h4 class="card-title mb-0 flex-grow-1" id="slocFormTitle">Tambah Sloc Baru</h4>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('vehicle.monitoring.master.sloc.store') }}" method="POST"
                                        id="slocForm">
                                        @csrf
                                        <input type="hidden" name="_method" id="slocFormMethod" value="POST">

                                        <div class="mb-3">
                                            <label for="s_loc" class="form-label">Sloc Code <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="s_loc" name="s_loc"
                                                required placeholder="Contoh: TMB" style="text-transform: uppercase;">
                                            @error('s_loc')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="sloc_name" class="form-label">Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="sloc_name" name="name"
                                                required placeholder="Contoh: Timbangan (Scales)">
                                            @error('name')
                                                @if ($errors->has('s_loc'))
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @endif
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"
                                                placeholder="Keterangan area (opsional)"></textarea>
                                            @error('description')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="d-flex gap-2 justify-content-end mt-4">
                                            <button type="button" class="btn btn-light" id="btnCancelSloc"
                                                style="display: none;">Batal</button>
                                            <button type="submit" class="btn btn-primary" id="btnSubmitSloc">Simpan
                                                Sloc</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Tab Pane -->
                <div class="tab-pane {{ $activeTab == 'vendor' ? 'active' : '' }}" id="vendorTab" role="tabpanel">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card shadow-sm border-0">
                                <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                                    <h4 class="card-title mb-0 flex-grow-1">Daftar Vendor</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle text-nowrap" id="vendorsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" width="70">No</th>
                                                    <th>Vendor Name</th>
                                                    <th>Description</th>
                                                    <th class="text-center" width="120">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($vendors as $index => $v)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td><strong class="text-primary">{{ $v->name }}</strong></td>
                                                        <td class="text-wrap">{{ $v->description ?? '-' }}</td>
                                                        <td class="text-center">
                                                            <div class="d-flex gap-1 justify-content-center">
                                                                <button type="button"
                                                                    class="btn btn-soft-primary btn-sm btn-edit-vendor"
                                                                    data-id="{{ $v->id }}"
                                                                    data-name="{{ $v->name }}"
                                                                    data-description="{{ $v->description }}"
                                                                    title="Edit">
                                                                    <i class="ri-edit-line"></i>
                                                                </button>
                                                                <form
                                                                    action="{{ route('vehicle.monitoring.master.vendor.delete', $v->id) }}"
                                                                    method="POST" class="d-inline form-delete-vendor">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn btn-soft-danger btn-sm" title="Delete">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">Belum ada
                                                            vendor terdaftar.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card shadow-sm border-0" id="vendorFormCard">
                                <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                                    <h4 class="card-title mb-0 flex-grow-1" id="vendorFormTitle">Tambah Vendor Baru</h4>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('vehicle.monitoring.master.vendor.store') }}" method="POST"
                                        id="vendorForm">
                                        @csrf
                                        <input type="hidden" name="_method" id="vendorFormMethod" value="POST">

                                        <div class="mb-3">
                                            <label for="vendor_name" class="form-label">Vendor Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="vendor_name" name="vendor_name"
                                                required placeholder="Contoh: PT. Fast Transport">
                                            @error('vendor_name')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="vendor_description" class="form-label">Description</label>
                                            <textarea class="form-control" id="vendor_description" name="description" rows="3"
                                                placeholder="Keterangan vendor (opsional)"></textarea>
                                            @error('description')
                                                @if($errors->has('vendor_name'))
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @endif
                                            @enderror
                                        </div>

                                        <div class="d-flex gap-2 justify-content-end mt-4">
                                            <button type="button" class="btn btn-light" id="btnCancelVendor"
                                                style="display: none;">Batal</button>
                                            <button type="submit" class="btn btn-primary" id="btnSubmitVendor">Simpan
                                                Vendor</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
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

            // Local data cache
            let allItems = [];
            let allLocations = [];
            let allVendors = [];

            // Helper to render Items Table
            function renderItemsTable() {
                let html = '';
                if (allItems.length === 0) {
                    html = `<tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada item terdaftar.</td>
                    </tr>`;
                } else {
                    allItems.forEach(function(item, index) {
                        const areaText = item.location ? 
                            `<span class="badge bg-soft-info text-info">${item.location.s_loc} - ${item.location.name}</span>` : 
                            `<span class="text-muted">-</span>`;

                        html += `<tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${item.name}</td>
                            <td>${areaText}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-soft-primary btn-sm btn-edit"
                                        data-id="${item.id}"
                                        data-name="${item.name}"
                                        data-location-id="${item.location_id || ''}" title="Edit">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete-item"
                                        data-id="${item.id}" title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                $('#itemsTable tbody').html(html);
            }

            // Helper to render Slocs Table
            function renderSlocsTable() {
                let html = '';
                if (allLocations.length === 0) {
                    html = `<tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada Sloc terdaftar.</td>
                    </tr>`;
                } else {
                    allLocations.forEach(function(loc, index) {
                        html += `<tr>
                            <td class="text-center">${index + 1}</td>
                            <td><span class="badge bg-soft-info text-info fs-12">${loc.s_loc}</span></td>
                            <td>${loc.name}</td>
                            <td class="text-wrap">${loc.description || '-'}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-soft-primary btn-sm btn-edit-sloc"
                                        data-id="${loc.id}"
                                        data-sloc="${loc.s_loc}"
                                        data-name="${loc.name}"
                                        data-description="${loc.description || ''}"
                                        title="Edit">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete-sloc"
                                        data-id="${loc.id}" title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                $('#slocsTable tbody').html(html);
            }

            // Helper to render Vendors Table
            function renderVendorsTable() {
                let html = '';
                if (allVendors.length === 0) {
                    html = `<tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada vendor terdaftar.</td>
                    </tr>`;
                } else {
                    allVendors.forEach(function(v, index) {
                        html += `<tr>
                            <td class="text-center">${index + 1}</td>
                            <td><strong class="text-primary">${v.name}</strong></td>
                            <td class="text-wrap">${v.description || '-'}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-soft-primary btn-sm btn-edit-vendor"
                                        data-id="${v.id}"
                                        data-name="${v.name}"
                                        data-description="${v.description || ''}"
                                        title="Edit">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete-vendor"
                                        data-id="${v.id}" title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                $('#vendorsTable tbody').html(html);
            }

            // AJAX Data Loader
            function loadMasterData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.master.items.data') }}",
                    type: "GET",
                    success: function(response) {
                        allItems = response.items;
                        allLocations = response.locations;
                        allVendors = response.vendors;
                        renderItemsTable();
                        renderSlocsTable();
                        renderVendorsTable();
                    },
                    error: function(xhr) {
                        console.error("Gagal memuat data master:", xhr);
                    }
                });
            }

            // Load initial master data via AJAX
            loadMasterData();

            // Edit Item button handler
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const locationId = $(this).data('location-id');

                $('#formTitle').text('Edit Item');
                $('#name').val(name);
                $('#location_id').val(locationId || '');

                // Change form action to update
                $('#itemForm').attr('action', `{{ url('vehicle-monitoring/master/items/update') }}/${id}`);
                $('#formMethod').val('PUT');

                $('#btnCancel').show();
                $('#btnSubmit').text('Perbarui Item').removeClass('btn-primary').addClass('btn-success');

                // Scroll to form card on mobile
                $('html, body').animate({
                    scrollTop: $("#formCard").offset().top - 100
                }, 500);
            });

            // Cancel Item button handler
            $('#btnCancel').on('click', function() {
                $('#formTitle').text('Tambah Item Baru');
                $('#name').val('');
                $('#location_id').val('');

                // Restore form action to store
                $('#itemForm').attr('action', `{{ route('vehicle.monitoring.master.items.store') }}`);
                $('#formMethod').val('POST');

                $(this).hide();
                $('#btnSubmit').text('Simpan Item').removeClass('btn-success').addClass('btn-primary');
            });

            // Submit Item Form via AJAX
            $('#itemForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const method = $('#formMethod').val();
                
                $('#btnSubmit').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        });
                        $('#btnCancel').click();
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
                        $('#btnSubmit').prop('disabled', false).text(method === 'PUT' ? 'Perbarui Item' : 'Simpan Item');
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
                                    text: response.message
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

            // Edit Sloc button handler
            $(document).on('click', '.btn-edit-sloc', function() {
                const id = $(this).data('id');
                const sloc = $(this).data('sloc');
                const name = $(this).data('name');
                const description = $(this).data('description');

                $('#slocFormTitle').text('Edit Sloc');
                $('#s_loc').val(sloc);
                $('#sloc_name').val(name);
                $('#description').val(description);

                // Change form action to update
                $('#slocForm').attr('action', `{{ url('vehicle-monitoring/master/sloc/update') }}/${id}`);
                $('#slocFormMethod').val('PUT');

                $('#btnCancelSloc').show();
                $('#btnSubmitSloc').text('Perbarui Sloc').removeClass('btn-primary').addClass('btn-success');

                // Scroll to form card on mobile
                $('html, body').animate({
                    scrollTop: $("#slocFormCard").offset().top - 100
                }, 500);
            });

            // Cancel Sloc button handler
            $('#btnCancelSloc').on('click', function() {
                $('#slocFormTitle').text('Tambah Sloc Baru');
                $('#s_loc').val('');
                $('#sloc_name').val('');
                $('#description').val('');

                // Restore form action to store
                $('#slocForm').attr('action', `{{ route('vehicle.monitoring.master.sloc.store') }}`);
                $('#slocFormMethod').val('POST');

                $(this).hide();
                $('#btnSubmitSloc').text('Simpan Sloc').removeClass('btn-success').addClass('btn-primary');
            });

            // Submit Sloc Form via AJAX
            $('#slocForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const method = $('#slocFormMethod').val();

                $('#btnSubmitSloc').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        });
                        $('#btnCancelSloc').click();
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
                        $('#btnSubmitSloc').prop('disabled', false).text(method === 'PUT' ? 'Perbarui Sloc' : 'Simpan Sloc');
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
                                    text: response.message
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

            // Edit Vendor button handler
            $(document).on('click', '.btn-edit-vendor', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const description = $(this).data('description');

                $('#vendorFormTitle').text('Edit Vendor');
                $('#vendor_name').val(name);
                $('#vendor_description').val(description);

                // Change form action to update
                $('#vendorForm').attr('action', `{{ url('vehicle-monitoring/master/vendor/update') }}/${id}`);
                $('#vendorFormMethod').val('PUT');

                $('#btnCancelVendor').show();
                $('#btnSubmitVendor').text('Perbarui Vendor').removeClass('btn-primary').addClass('btn-success');

                // Scroll to form card on mobile
                $('html, body').animate({
                    scrollTop: $("#vendorFormCard").offset().top - 100
                }, 500);
            });

            // Cancel Vendor button handler
            $('#btnCancelVendor').on('click', function() {
                $('#vendorFormTitle').text('Tambah Vendor Baru');
                $('#vendor_name').val('');
                $('#vendor_description').val('');

                // Restore form action to store
                $('#vendorForm').attr('action', `{{ route('vehicle.monitoring.master.vendor.store') }}`);
                $('#vendorFormMethod').val('POST');

                $(this).hide();
                $('#btnSubmitVendor').text('Simpan Vendor').removeClass('btn-success').addClass('btn-primary');
            });

            // Submit Vendor Form via AJAX
            $('#vendorForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const method = $('#vendorFormMethod').val();

                $('#btnSubmitVendor').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        });
                        $('#btnCancelVendor').click();
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
                        $('#btnSubmitVendor').prop('disabled', false).text(method === 'PUT' ? 'Perbarui Vendor' : 'Simpan Vendor');
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
                                    text: response.message
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
        });</script>
@endsection
