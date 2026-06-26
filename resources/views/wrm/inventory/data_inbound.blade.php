@extends('layouts.app')

@section('styles')
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            font-size: 0.85rem !important;
            min-height: 38px !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options {
            font-size: 0.85rem !important;
            max-height: 250px !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
            padding-left: 0.75rem !important;
        }

        /* Style for Multiple Select choices */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #0d6efd !important;
            color: #fff !important;
            border: none !important;
            font-size: 0.75rem !important;
            padding: 2px 8px !important;
            border-radius: 4px !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff !important;
            margin-right: 5px !important;
        }

        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ffc107 !important;
            background-color: transparent !important;
        }

        /* Soft Buttons */
        .btn-soft-info {
            background-color: rgba(53, 185, 230, 0.1);
            color: #35b9e6;
            border: none;
        }

        .btn-soft-info:hover {
            background-color: #35b9e6;
            color: #fff;
        }

        .btn-soft-danger {
            background-color: rgba(240, 101, 72, 0.1);
            color: #f06548;
            border: none;
        }

        .btn-soft-danger:hover {
            background-color: #f06548;
            color: #fff;
        }

        #tableInbound tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Custom dropdown styles */
        .custom-filter-dropdown .dropdown-toggle {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0.47rem 0.75rem;
            font-size: 0.875rem;
            box-shadow: 0 0 0 0 !important;
        }

        .custom-filter-dropdown .dropdown-menu {
            border-radius: 0.4rem;
            font-size: 0.875rem;
        }

        .custom-filter-dropdown .option-item:hover {
            background-color: #f8f9fa;
        }

        .custom-filter-dropdown .options-list {
            padding-right: 5px;
        }
    </style>
@endsection

@section('title', ' | Data Inbound RM')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Card Summary --}}
            <div class="row g-3 mb-3" id="summarySection">
                <div class="col-md">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-primary">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2 text-primary"
                                        style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Total
                                        Inbound</h6>
                                    <h3 class="mb-1 fw-bold text-primary" id="totalQty">0</h3>
                                    <div class="small text-primary">
                                        <i class="mdi mdi-layers-outline me-1"></i> <span id="totalPalletsDisplay">0</span>
                                        Pallets
                                    </div>
                                </div>
                                <div class="bg-soft-primary rounded-3 p-2">
                                    <i class="mdi mdi-database-outline text-primary mdi-36px"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-success">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2 text-success"
                                        style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Unrest
                                    </h6>
                                    <h3 class="mb-1 fw-bold text-success" id="unrestQty">0</h3>
                                    <div class="small text-success">
                                        <i class="mdi mdi-layers-outline me-1"></i> <span id="unrestPallets">0</span>
                                        Pallets
                                    </div>
                                </div>
                                <div class="bg-soft-success rounded-3 p-2">
                                    <i class="mdi mdi-check-circle-outline mdi-36px text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-info">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2 text-info"
                                        style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">QI</h6>
                                    <h3 class="mb-1 fw-bold text-info" id="qiQty">0</h3>
                                    <div class="small text-info">
                                        <i class="mdi mdi-layers-outline me-1"></i> <span id="qiPallets">0</span> Pallets
                                    </div>
                                </div>
                                <div class="bg-soft-info rounded-3 p-2">
                                    <i class="mdi mdi-flask-outline text-info mdi-36px"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-danger">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2 text-danger"
                                        style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Blocked
                                    </h6>
                                    <h3 class="mb-1 fw-bold text-danger" id="blockedQty">0</h3>
                                    <div class="small text-danger">
                                        <i class="mdi mdi-layers-outline me-1"></i> <span id="blockedPallets">0</span>
                                        Pallets
                                    </div>
                                </div>
                                <div class="bg-soft-danger rounded-3 p-2">
                                    <i class="mdi mdi-alert-octagon-outline text-danger mdi-36px"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Filter --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted mb-2">Pencarian Cepat</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="mdi mdi-magnify text-primary"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" id="filterCatatan"
                                    placeholder="Cari barcode atau catatan...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted mb-2">No SPB</label>
                            <div class="dropdown custom-filter-dropdown" id="dropdown-no-spb">
                                <button
                                    class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                    type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    <span class="dropdown-placeholder text-muted" data-placeholder="Pilih No SPB...">Pilih
                                        No SPB...</span>
                                    <span class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                </button>
                                <div class="dropdown-menu p-3 border-0"
                                    style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                    <div class="mb-2">
                                        <input type="text" class="form-control form-control-sm search-options"
                                            placeholder="Cari No SPB...">
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                            All</button>
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                            All</button>
                                    </div>
                                    <hr class="dropdown-divider my-2">
                                    <div class="options-list" style="max-height: 250px; overflow-y: auto;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted mb-2">MID</label>
                            <div class="dropdown custom-filter-dropdown" id="dropdown-mid">
                                <button
                                    class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                    type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    <span class="dropdown-placeholder text-muted" data-placeholder="Pilih MID...">Pilih
                                        MID...</span>
                                    <span class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                </button>
                                <div class="dropdown-menu p-3 border-0"
                                    style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                    <div class="mb-2">
                                        <input type="text" class="form-control form-control-sm search-options"
                                            placeholder="Cari MID...">
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                            All</button>
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                            All</button>
                                    </div>
                                    <hr class="dropdown-divider my-2">
                                    <div class="options-list" style="max-height: 250px; overflow-y: auto;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button class="btn btn-soft-info w-100" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseAdvancedFilter" aria-expanded="false"
                                    aria-controls="collapseAdvancedFilter">
                                    <i class="mdi mdi-tune-vertical me-1"></i> Filter Lanjutan
                                </button>
                                <button class="btn btn-soft-danger w-100" id="btnReset" title="Reset Filter">
                                    <i class="mdi mdi-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Collapsible Advanced Filters --}}
                    <div class="collapse" id="collapseAdvancedFilter">
                        <div class="pt-4 mt-4 border-top">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-muted mb-2">Supplier</label>
                                    <div class="dropdown custom-filter-dropdown" id="dropdown-supplier">
                                        <button
                                            class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                            type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                            aria-expanded="false">
                                            <span class="dropdown-placeholder text-muted"
                                                data-placeholder="Pilih Supplier...">Pilih Supplier...</span>
                                            <span class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                        </button>
                                        <div class="dropdown-menu p-3 border-0"
                                            style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                            <div class="mb-2">
                                                <input type="text" class="form-control form-control-sm search-options"
                                                    placeholder="Cari Supplier...">
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                    All</button>
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                    All</button>
                                            </div>
                                            <hr class="dropdown-divider my-2">
                                            <div class="options-list" style="max-height: 250px; overflow-y: auto;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-muted mb-2">Group</label>
                                    <div class="dropdown custom-filter-dropdown" id="dropdown-group">
                                        <button
                                            class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                            type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                            aria-expanded="false">
                                            <span class="dropdown-placeholder text-muted"
                                                data-placeholder="Pilih Group...">Pilih Group...</span>
                                            <span class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                        </button>
                                        <div class="dropdown-menu p-3 border-0"
                                            style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                            <div class="mb-2">
                                                <input type="text" class="form-control form-control-sm search-options"
                                                    placeholder="Cari Group...">
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                    All</button>
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                    All</button>
                                            </div>
                                            <hr class="dropdown-divider my-2">
                                            <div class="options-list" style="max-height: 250px; overflow-y: auto;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-muted mb-2">Status</label>
                                    <div class="dropdown custom-filter-dropdown" id="dropdown-status">
                                        <button
                                            class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                            type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                            aria-expanded="false">
                                            <span class="dropdown-placeholder text-muted"
                                                data-placeholder="Pilih Status...">Pilih Status...</span>
                                            <span class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                        </button>
                                        <div class="dropdown-menu p-3 border-0"
                                            style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                            <div class="mb-2">
                                                <input type="text" class="form-control form-control-sm search-options"
                                                    placeholder="Cari Status...">
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                    All</button>
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                    All</button>
                                            </div>
                                            <hr class="dropdown-divider my-2">
                                            <div class="options-list" style="max-height: 250px; overflow-y: auto;">
                                                <div class="form-check mb-2 option-item" data-value="UNREST"
                                                    data-text="UNREST">
                                                    <input class="form-check-input option-checkbox" type="checkbox"
                                                        value="UNREST" id="chk-status-UNREST">
                                                    <label class="form-check-label text-truncate w-100"
                                                        for="chk-status-UNREST">UNREST</label>
                                                </div>
                                                <div class="form-check mb-2 option-item" data-value="QI" data-text="QI">
                                                    <input class="form-check-input option-checkbox" type="checkbox"
                                                        value="QI" id="chk-status-QI">
                                                    <label class="form-check-label text-truncate w-100"
                                                        for="chk-status-QI">QI</label>
                                                </div>
                                                <div class="form-check mb-2 option-item" data-value="BLOCKED"
                                                    data-text="BLOCKED">
                                                    <input class="form-check-input option-checkbox" type="checkbox"
                                                        value="BLOCKED" id="chk-status-BLOCKED">
                                                    <label class="form-check-label text-truncate w-100"
                                                        for="chk-status-BLOCKED">BLOCKED</label>
                                                </div>
                                                <div class="form-check mb-2 option-item" data-value="ISSUED"
                                                    data-text="ISSUED">
                                                    <input class="form-check-input option-checkbox" type="checkbox"
                                                        value="ISSUED" id="chk-status-ISSUED">
                                                    <label class="form-check-label text-truncate w-100"
                                                        for="chk-status-ISSUED">ISSUED</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-muted mb-2">Lokasi</label>
                                    <div class="dropdown custom-filter-dropdown" id="dropdown-location">
                                        <button
                                            class="btn btn-outline-secondary dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center bg-white border-light-subtle"
                                            type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                            aria-expanded="false">
                                            <span class="dropdown-placeholder text-muted"
                                                data-placeholder="Pilih Lokasi...">Pilih Lokasi...</span>
                                            <span class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                        </button>
                                        <div class="dropdown-menu p-3 border-0"
                                            style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden;">
                                            <div class="mb-2">
                                                <input type="text" class="form-control form-control-sm search-options"
                                                    placeholder="Cari Lokasi...">
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 select-all-options text-decoration-none fw-semibold">Select
                                                    All</button>
                                                <button type="button"
                                                    class="btn btn-link btn-sm p-0 text-danger clear-all-options text-decoration-none fw-semibold">Clear
                                                    All</button>
                                            </div>
                                            <hr class="dropdown-divider my-2">
                                            <div class="options-list" style="max-height: 250px; overflow-y: auto;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-muted mb-2">Incoming Date (Start)</label>
                                    <input type="date" class="form-control" id="filterStartDate">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-muted mb-2">Incoming Date (End)</label>
                                    <input type="date" class="form-control" id="filterEndDate">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Historical Inbound Data</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('wrm.inventory.index-upload') }}" class="btn btn-primary">
                            <i class="mdi mdi-upload me-1"></i> New Inbound
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive py-2">
                        <table class="table table-striped align-middle text-nowrap" id="tableInbound">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Barcode</th>
                                    <th>No SPB</th>
                                    <th>Mid</th>
                                    <th>Nama Barang</th>
                                    <th>Uom</th>
                                    <th>Group</th>
                                    <th>Pallet ID</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Supplier</th>
                                    <th class="text-nowrap cursor-pointer" id="sortDate">
                                        Incoming Date <i class="mdi mdi-sort ms-1" id="sortIcon"></i>
                                    </th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-end" id="pagination"></div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function numberFormat(x) {
            if (x === null || x === undefined) return '0';
            let val = parseFloat(x);
            return val.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }

        $(document).ready(function() {
            let currentSortDir = 'desc';
            let isResetting = false;
            let filterTimeout;

            // Initialize custom dropdowns
            const onFilterChange = function() {
                if (isResetting) return;
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    loadData(1);
                }, 300);
            };

            initDynamicDropdown('dropdown-no-spb', 'Pilih No SPB...', onFilterChange);
            initDynamicDropdown('dropdown-mid', 'Pilih MID...', onFilterChange);
            initDynamicDropdown('dropdown-supplier', 'Pilih Supplier...', onFilterChange);
            initDynamicDropdown('dropdown-group', 'Pilih Group...', onFilterChange);
            initDynamicDropdown('dropdown-status', 'Pilih Status...', onFilterChange);
            initDynamicDropdown('dropdown-location', 'Pilih Lokasi...', onFilterChange);

            loadData();
            loadFilter();

            function initDynamicDropdown(id, placeholder, onChange) {
                const $dropdown = $('#' + id);

                // Search options
                $dropdown.off('input', '.search-options').on('input', '.search-options', function() {
                    const query = $(this).val().toLowerCase();
                    $dropdown.find('.option-item').each(function() {
                        const text = $(this).data('text').toString().toLowerCase();
                        const val = $(this).data('value').toString().toLowerCase();
                        if (text.indexOf(query) > -1 || val.indexOf(query) > -1) {
                            $(this).removeClass('d-none');
                        } else {
                            $(this).addClass('d-none');
                        }
                    });
                });

                // Checkbox changes
                $dropdown.off('change', '.option-checkbox').on('change', '.option-checkbox', function() {
                    updateLabel();
                });

                // Select All
                $dropdown.off('click', '.select-all-options').on('click', '.select-all-options', function(e) {
                    e.preventDefault();
                    $dropdown.find('.option-item:not(.d-none) .option-checkbox').prop('checked', true);
                    updateLabel();
                });

                // Clear All
                $dropdown.off('click', '.clear-all-options').on('click', '.clear-all-options', function(e) {
                    e.preventDefault();
                    $dropdown.find('.option-checkbox').prop('checked', false);
                    updateLabel();
                });

                function updateLabel() {
                    const selected = [];
                    $dropdown.find('.option-checkbox:checked').each(function() {
                        selected.push($(this).val());
                    });

                    const $placeholderSpan = $dropdown.find('.dropdown-placeholder');
                    const $badge = $dropdown.find('.selected-count');
                    if (selected.length === 0) {
                        $placeholderSpan.text(placeholder);
                        $badge.addClass('d-none').text('0');
                    } else {
                        $placeholderSpan.text(`${selected.length} Terpilih`);
                        $badge.removeClass('d-none').text(selected.length);
                    }

                    if (onChange && !isResetting) {
                        onChange(selected);
                    }
                }

                // Attach methods
                $dropdown.data('getValues', function() {
                    const selected = [];
                    $dropdown.find('.option-checkbox:checked').each(function() {
                        selected.push($(this).val());
                    });
                    return selected;
                });

                $dropdown.data('reset', function() {
                    $dropdown.find('.option-checkbox').prop('checked', false);
                    $dropdown.find('.search-options').val('').trigger('input');
                    updateLabel();
                });
            }

            function updateDropdownOptions(id, data, placeholder, isMid = false, isLocation = false) {
                const $dropdown = $('#' + id);
                const currentValues = $dropdown.data('getValues') ? $dropdown.data('getValues')() : [];
                let html = '';

                data.forEach(item => {
                    let val, text;
                    if (isMid || isLocation) {
                        val = item.id ?? item.mid;
                        text = item.text;
                    } else {
                        val = item;
                        text = item;
                    }

                    let safeVal = val ?? '';
                    let safeText = text ?? '';
                    if (safeText === '') {
                        safeText = safeVal === '' ? '-' : safeVal;
                    }

                    let isSelected = currentValues.includes(safeVal.toString());
                    let checkedAttr = isSelected ? 'checked' : '';
                    html += `
                        <div class="form-check mb-2 option-item" data-value="${safeVal}" data-text="${safeText}">
                            <input class="form-check-input option-checkbox" type="checkbox" value="${safeVal}" id="chk-${id}-${safeVal}" ${checkedAttr}>
                            <label class="form-check-label text-truncate w-100" for="chk-${id}-${safeVal}">
                                ${safeText}
                            </label>
                        </div>
                    `;
                });

                $dropdown.find('.options-list').html(html);

                // Update the label and selected count badge
                const selectedCount = $dropdown.find('.option-checkbox:checked').length;
                const $placeholderSpan = $dropdown.find('.dropdown-placeholder');
                const $badge = $dropdown.find('.selected-count');
                if (selectedCount === 0) {
                    $placeholderSpan.text(placeholder);
                    $badge.addClass('d-none').text('0');
                } else {
                    $placeholderSpan.text(`${selectedCount} Terpilih`);
                    $badge.removeClass('d-none').text(selectedCount);
                }
            }

            function loadData(page = 1) {
                let params = {
                    page: page,
                    group: $('#dropdown-group').data('getValues') ? $('#dropdown-group').data('getValues')() :
                    [],
                    mid: $('#dropdown-mid').data('getValues') ? $('#dropdown-mid').data('getValues')() : [],
                    start_date: $('#filterStartDate').val(),
                    end_date: $('#filterEndDate').val(),
                    supplier: $('#dropdown-supplier').data('getValues') ? $('#dropdown-supplier').data(
                        'getValues')() : [],
                    status: $('#dropdown-status').data('getValues') ? $('#dropdown-status').data('getValues')
                        () : [],
                    no_spb: $('#dropdown-no-spb').data('getValues') ? $('#dropdown-no-spb').data('getValues')
                        () : [],
                    location: $('#dropdown-location').data('getValues') ? $('#dropdown-location').data(
                        'getValues')() : [],
                    catatan: $('#filterCatatan').val(),
                    sort_dir: currentSortDir,
                };

                $.get("{{ route('wrm.inventory.dataInbound') }}", params, function(res) {
                    let html = '';
                    let data = res.data.data;
                    let startNo = res.data.from;

                    if (data.length === 0) {
                        html = `
                        <tr>
                            <td colspan="14" class="text-center text-muted py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="mdi mdi-database-off-outline" style="font-size:32px"></i>
                                    <span class="mt-2">Data tidak ditemukan</span>
                                </div>
                            </td>
                        </tr>
                    `;
                    } else {
                        data.forEach((d, index) => {
                            let locationText = 'N/A';
                            if (d.bin && d.bin.location) {
                                let loc = d.bin.location;
                                locationText =
                                    `${loc.plant} - ${loc.s_loc} - ${loc.gudang} - ${loc.bin} (${d.bin.kolom}.${d.bin.level})`;
                            }

                            html += `
                            <tr>
                                <td class="text-center">${startNo + index}</td>
                                <td>${d.barcode ?? ''}</td>
                                <td>${d.inbound ? d.inbound.no_spb : ''}</td>
                                <td>${d.barang ? d.barang.mid : ''}</td>
                                <td>${d.barang ? d.barang.nama_barang : ''}</td>
                                <td>${d.barang ? d.barang.uom : ''}</td>
                                <td>${d.group ?? ''}</td>
                                <td>${d.pallet_id ?? ''}</td>
                                <td>${numberFormat(d.qty)}</td>
                                <td><span class="badge ${getStatusBadge(d.status)}">${d.status}</span></td>
                                <td>${locationText}</td>
                                <td>${d.inbound ? (d.inbound.supplier ?? '') : ''}</td>
                                <td>${d.inbound ? d.inbound.incoming_date : ''}</td>
                                <td>${d.catatan ?? ''}</td>
                            </tr>
                        `;
                        });
                    }

                    $('#tableInbound tbody').html(html);
                    renderPagination(res.data);
                    updateSummary(res.summary);
                });
            }

            function getStatusBadge(status) {
                switch (status) {
                    case 'UNREST':
                        return 'bg-success';
                    case 'QI':
                        return 'bg-info';
                    case 'BLOCKED':
                        return 'bg-danger';
                    case 'ISSUED':
                        return 'bg-secondary';
                    default:
                        return 'bg-light text-dark';
                }
            }

            function updateSummary(summary) {
                const unrest = summary.status_breakdown.UNREST || {
                    count: 0,
                    total_qty: 0
                };
                const qi = summary.status_breakdown.QI || {
                    count: 0,
                    total_qty: 0
                };
                const blocked = summary.status_breakdown.BLOCKED || {
                    count: 0,
                    total_qty: 0
                };
                const issued = summary.status_breakdown.ISSUED || {
                    count: 0,
                    total_qty: 0
                };

                $('#totalQty').text(numberFormat(summary.total_qty));
                $('#totalPalletsDisplay').text(numberFormat(summary.total_pallet));

                $('#unrestQty').text(numberFormat(unrest.total_qty));
                $('#unrestPallets').text(numberFormat(unrest.count));

                $('#qiQty').text(numberFormat(qi.total_qty));
                $('#qiPallets').text(numberFormat(qi.count));

                $('#blockedQty').text(numberFormat(blocked.total_qty));
                $('#blockedPallets').text(numberFormat(blocked.count));
            }

            function loadFilter() {
                $.get("{{ route('wrm.inventory.getFilterInbound') }}", function(res) {
                    updateDropdownOptions('dropdown-no-spb', res.no_spbs, 'Pilih No SPB...');
                    updateDropdownOptions('dropdown-mid', res.mids, 'Pilih MID...', true);
                    updateDropdownOptions('dropdown-supplier', res.suppliers, 'Pilih Supplier...');
                    updateDropdownOptions('dropdown-group', res.groups, 'Pilih Group...');
                    updateDropdownOptions('dropdown-location', res.locations, 'Pilih Lokasi...', false,
                        true);
                });
            }

            function renderPagination(data) {
                let html = `
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Previous -->
                                <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                                    <a class="page-link" href="#" data-page="${data.current_page - 1}">
                                        Previous
                                    </a>
                                </li>
                    `;

                let start = Math.max(1, data.current_page - 2);
                let end = Math.min(data.last_page, data.current_page + 2);

                // Show first page + dots
                if (start > 1) {
                    html += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="1">1</a>
                        </li>
                    `;

                    if (start > 2) {
                        html += `
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        `;
                    }
                }

                // Middle pages
                for (let i = start; i <= end; i++) {
                    html += `
                        <li class="page-item ${i === data.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">
                                ${i}
                            </a>
                        </li>
                    `;
                }

                // Show last page + dots
                if (end < data.last_page) {

                    if (end < data.last_page - 1) {
                        html += `
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        `;
                    }

                    html += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${data.last_page}">
                                ${data.last_page}
                            </a>
                        </li>
                    `;
                }

                html += `
                            <!-- Next -->
                            <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${data.current_page + 1}">
                                    Next
                                </a>
                            </li>
                        </ul>
                    </nav>
                `;

                $('#pagination').html(html);
            }

            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page) loadData(page);
            });

            // Trigger dates
            $('#filterStartDate, #filterEndDate').on('change', onFilterChange);

            $('#filterCatatan').on('keyup', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => loadData(1), 500);
            });

            $('#btnReset').click(function() {
                isResetting = true;
                $('#dropdown-no-spb').data('reset')();
                $('#dropdown-mid').data('reset')();
                $('#dropdown-supplier').data('reset')();
                $('#dropdown-group').data('reset')();
                $('#dropdown-status').data('reset')();
                $('#dropdown-location').data('reset')();
                $('#filterStartDate, #filterEndDate').val('');
                $('#filterCatatan').val('');
                isResetting = false;
                loadData(1);
            });

            $('#sortDate').click(function() {
                currentSortDir = currentSortDir === 'desc' ? 'asc' : 'desc';
                $('#sortIcon').attr('class',
                    `mdi mdi-sort-${currentSortDir === 'asc' ? 'ascending' : 'descending'} ms-1`);
                loadData(1);
            });
        });
    </script>
@endsection
