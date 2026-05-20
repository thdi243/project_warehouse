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

        /* Modern Checkbox Style */
        .checkbox-xl {
            width: 1.4rem;
            height: 1.4rem;
            cursor: pointer;
            border: 2px solid #ced4da;
        }

        .checkbox-xl:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        /* Bulk Action Toolbar */
        #bulkToolbar {
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: none;
            animation: slideIn 0.3s ease-out;
            border-left: 5px solid #0d6efd;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .row-selected {
            background-color: rgba(13, 110, 253, 0.04) !important;
            transition: background-color 0.2s;
        }

        #tableStock tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
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

        /* Selection Banner */
        .selection-banner {
            display: none;
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
            color: #0d47a1;
            padding: 8px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            text-align: center;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('title', ' | Stock On Hand RM')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- Card Summary --}}
            <div class="row g-3 mb-3" id="summarySection">
                <div class="col-md-3">
                    <!-- <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);"> -->
                    <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-primary">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2"
                                        style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Total
                                        Inventory</h6>
                                    <h3 class="mb-1 fw-bold" id="totalQty">0</h3>
                                    <div class="small">
                                        <i class="mdi mdi-layers-outline me-1"></i> <span id="totalPalletsDisplay">0</span>
                                        Pallets
                                    </div>
                                </div>
                                <div class="bg-soft-primary rounded-3 p-2">
                                    <i class="mdi mdi-database-outline text-white mdi-36px"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <!-- <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);"> -->
                    <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-success">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2"
                                        style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Unrest
                                    </h6>
                                    <h3 class="mb-1 fw-bold" id="unrestQty">0</h3>
                                    <div class="small">
                                        <i class="mdi mdi-layers-outline me-1"></i> <span id="unrestPallets">0</span>
                                        Pallets
                                    </div>
                                </div>
                                <div class="bg-soft-success rounded-3 p-2">
                                    <i class="mdi mdi-check-circle-outline mdi-36px text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-info">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2"
                                        style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">QI</h6>
                                    <h3 class="mb-1 fw-bold" id="qiQty">0</h3>
                                    <div class="small">
                                        <i class="mdi mdi-layers-outline me-1"></i> <span id="qiPallets">0</span> Pallets
                                    </div>
                                </div>
                                <div class="bg-soft-info rounded-3 p-2">
                                    <i class="mdi mdi-flask-outline text-white mdi-36px"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-danger">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2"
                                        style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Blocked
                                    </h6>
                                    <h3 class="mb-1 fw-bold" id="blockedQty">0</h3>
                                    <div class="small">
                                        <i class="mdi mdi-layers-outline me-1"></i> <span id="blockedPallets">0</span>
                                        Pallets
                                    </div>
                                </div>
                                <div class="bg-soft-danger rounded-3 p-2">
                                    <i class="mdi mdi-alert-octagon-outline text-white mdi-36px"></i>
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
                                    placeholder="Cari barcode, MID, atau catatan...">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted mb-2">No SPB</label>
                            <select class="form-select select2-filter" id="filterNoSpb" multiple>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted mb-2">MID</label>
                            <select class="form-select select2-filter" id="filterMid" multiple>
                            </select>
                        </div>
                        {{-- <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted mb-2">Nama Barang</label>
                            <select class="form-select select2-filter" id="filterNamaBarang" multiple>
                            </select>
                        </div> --}}
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
                                    <select class="form-select select2-filter" id="filterSupplier" multiple>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-muted mb-2">Group</label>
                                    <select class="form-select select2-filter" id="filterGroup" multiple>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-muted mb-2">Status</label>
                                    <select class="form-select select2-filter" id="filterStatus" multiple>
                                        <option value="UNREST">UNREST</option>
                                        <option value="QI">QI</option>
                                        <option value="BLOCKED">BLOCKED</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-muted mb-2">Lokasi</label>
                                    <select class="form-select select2-filter" id="filterLocation" multiple>
                                    </select>
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
                    <h5 class="mb-0 fw-bold">Raw Material Stock On Hand</h5>
                    @can('permission', 'wrm-inventory-soh-plus')
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success" id="btnExportExcel">
                                <i class="mdi mdi-file-excel"></i> Export Excel
                            </button>
                            <a href="{{ route('wrm.inventory.index-upload') }}" class="btn btn-outline-primary"
                                id="btnUpload">
                                <i class="mdi mdi-upload"></i> Upload
                            </a>
                            <a href="{{ route('wrm.inventory.draft-outbound') }}" class="btn btn-primary" id="btnUpload">
                                <i class="mdi mdi-upload"></i> Transfer
                            </a>
                        </div>
                    @endcan
                </div>
                <div class="card-body">
                    @can('permission', 'wrm-inventory-soh-plus')
                        {{-- Bulk Action Toolbar --}}
                        <div id="bulkToolbar" class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fs-5 fw-semibold"><i class="mdi mdi-check-all me-1"></i> <span
                                        id="selectedCount">0</span> Items Selected</span>
                                <p class="text-muted small mb-0" id="selectionText">Choose an action to apply to all selected
                                    records</p>
                            </div>
                            <div class="d-flex gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="mdi mdi-square-edit-outline me-1"></i> Bulk Action
                                    </button>
                                    <ul class="dropdown-menu shadow dropdown-menu-end">
                                        <li>
                                            <h6 class="dropdown-header">Update Status</h6>
                                        </li>
                                        <li><a class="dropdown-item bulk-status" href="#" data-status="UNREST"><i
                                                    class="mdi mdi-check-circle-outline text-success me-2"></i>Set to <span
                                                    class="badge bg-success">UNREST</span></a></li>
                                        <li><a class="dropdown-item bulk-status" href="#" data-status="QI"><i
                                                    class="mdi mdi-flask-outline text-info me-2"></i>Set to <span
                                                    class="badge bg-info">QI</span></a></li>
                                        <li><a class="dropdown-item bulk-status" href="#" data-status="BLOCKED"><i
                                                    class="mdi mdi-alert-octagon-outline text-danger me-2"></i>Set to <span
                                                    class="badge bg-danger">BLOCKED</span></a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <h6 class="dropdown-header">Other Actions</h6>
                                        </li>
                                        <li><a class="dropdown-item" href="#" id="bulkGroup"><i
                                                    class="mdi mdi-folder-outline text-primary me-2"></i>Change Group</a></li>
                                        <li><a class="dropdown-item text-danger" href="#" id="bulkDelete"><i
                                                    class="mdi mdi-delete-outline me-2"></i>Delete Selected</a></li>
                                    </ul>
                                </div>
                                <button class="btn btn-outline-secondary" id="clearSelection">
                                    <i class="mdi mdi-close"></i> Clear
                                </button>
                            </div>
                        </div>
                    @endcan

                    {{-- Selection Banner --}}
                    <div id="selectAllBanner" class="selection-banner">
                        All <span class="fw-bold" id="pageCountText">0</span> items on this page are selected.
                        <a href="javascript:void(0)" class="ms-2 fw-bold" id="btnSelectAllTotal">Select all <span
                                id="totalCountText">0</span> items in stock</a>
                    </div>
                    <div id="allSelectedBanner" class="selection-banner"
                        style="background-color: #e8f5e9; border-color: #c8e6c9; color: #1b5e20;">
                        All <span class="fw-bold" id="totalCountText2">0</span> items in stock are selected.
                        <a href="javascript:void(0)" class="ms-2 fw-bold" id="btnClearTotalSelection">Clear selection</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap" id="tableStock">
                            <thead class="table-light">
                                <tr>
                                    @can('permission', 'wrm-inventory-soh-plus')
                                        <th class="text-center align-middle" style="width: 50px;">
                                            <input type="checkbox" id="checkAll" class="form-check-input checkbox-xl">
                                        </th>
                                    @endcan
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
                                    <th class="text-nowrap cursor-pointer" id="sortDate">
                                        Expired Date <i class="mdi mdi-sort ms-1" id="sortIcon"></i>
                                    </th>
                                    @can('permission', 'wrm-inventory-soh-plus')
                                        <th class="text-center">Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <label class="mb-0 text-muted small">Show</label>
                            <select class="form-select form-select-sm" id="perPage" style="width: auto;">
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                            </select>
                            <label class="mb-0 text-muted small">entries</label>
                        </div>
                        <div id="pagination"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalFormEdit">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="titleForm">Edit Stock Gula</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="formStockEdit">

                    <div class="modal-body">

                        <input type="hidden" id="id" name="id">

                        <div class="mb-2">
                            <label>Mid</label>
                            <input type="hidden" name="barang_id" id="barangIdEdit">
                            <input type="text" class="form-control bg-light" id="midEdit" readonly>
                        </div>

                        <div class="mb-2">
                            <label>No SPB</label>
                            <input type="number" class="form-control" name="no_spb" id="noSpbEdit">
                        </div>

                        <div class="mb-2">
                            <label>Qty</label>
                            <input type="number" class="form-control" name="qty" id="qtyEdit">
                        </div>

                        <div class="mb-2">
                            <label>Incoming Date</label>
                            <input type="date" class="form-control" name="incoming_date" id="incomingEdit">
                        </div>

                        <div class="mb-2">
                            <label>Status</label>
                            <select class="form-select" name="status" id="statusEdit">
                                <option value="UNREST">UNREST</option>
                                <option value="QI">QI</option>
                                <option value="BLOCKED">BLOCKED</option>
                                <!-- <option value="TRANSFER">TRANSFER</option> -->
                                <!-- <option value="ISSUED">ISSUED</option> -->
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Group</label>
                            <input type="text" class="form-control" name="group" id="groupEdit">
                        </div>

                        <div class="mb-2">
                            <label>Supplier</label>
                            <select class="form-select" name="supplier" id="supplierEdit">
                                <option value="">Pilih Supplier</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->nama }}">{{ $sup->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Location</label>
                            <select class="form-select" id="locEdit" name="loc_id">
                                <option value="">Pilih Location</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Pallet ID</label>
                            <input type="text" class="form-control" name="pallet_id" id="palletEdit">
                        </div>

                        <div class="mb-2">
                            <label>Catatan</label>
                            <textarea class="form-control" name="catatan" id="catatan"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- Modal Export Excel --}}
    <div class="modal fade" id="exportExcelModal" tabindex="-1" aria-labelledby="exportExcelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportExcelModalLabel"><i
                            class="mdi mdi-file-excel me-1 text-success"></i> Export Excel Opname</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="exportExcelForm" action="{{ route('wrm.inventory.export-excel') }}" method="POST">
                    @csrf
                    <!-- Hidden filter inputs to apply currently active UI filters to the export -->
                    <input type="hidden" name="group" id="exportGroup">
                    <input type="hidden" name="status" id="exportStatus">
                    <input type="hidden" name="jenis_bahan" id="exportJenisBahan">
                    <input type="hidden" name="supplier" id="exportSupplier">
                    <input type="hidden" name="location" id="exportLocation">
                    <input type="hidden" name="no_spb" id="exportNoSpb">
                    <input type="hidden" name="start_date" id="exportStartDate">
                    <input type="hidden" name="end_date" id="exportEndDate">
                    <input type="hidden" name="catatan" id="exportCatatan">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih MID yang ingin di-export</label>
                            <select class="form-select" name="mids[]" id="exportMids" multiple style="width: 100%;">
                                <!-- Dynamically populated -->
                            </select>
                            <div class="form-text">Biarkan kosong untuk mengekspor seluruh MID.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="mdi mdi-download me-1"></i>
                            Export</button>
                    </div>
                </form>
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
            // Modal edit selects
            $('#supplierEdit').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#modalFormEdit'),
                placeholder: 'Pilih...',
                allowClear: true
            });

            // Filter selects
            $('.select2-filter').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Pilih...',
                allowClear: true,
                closeOnSelect: false
            });

            // Export Excel click handler
            $('#btnExportExcel').click(function() {
                // Copy filter values from page to hidden inputs
                const getFilterVal = (sel) => {
                    const val = $(sel).val();
                    return val && val.length > 0 ? JSON.stringify(val) : '';
                };

                $('#exportGroup').val(getFilterVal('#filterGroup'));
                $('#exportStatus').val(getFilterVal('#filterStatus'));
                $('#exportSupplier').val(getFilterVal('#filterSupplier'));
                $('#exportLocation').val(getFilterVal('#filterLocation'));
                $('#exportNoSpb').val(getFilterVal('#filterNoSpb'));
                $('#exportStartDate').val($('#filterStartDate').val() || '');
                $('#exportEndDate').val($('#filterEndDate').val() || '');
                $('#exportCatatan').val($('#filterCatatan').val() || '');

                // Populate modal select options from filterMid options
                const exportSelect = $('#exportMids');
                exportSelect.empty();

                $('#filterMid option').each(function() {
                    const opt = $(this);
                    if (opt.val()) {
                        exportSelect.append(new Option(opt.text(), opt.val(), false, false));
                    }
                });

                // Initialize select2 inside modal
                exportSelect.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#exportExcelModal'),
                    placeholder: 'Pilih MID (kosongkan untuk semua)',
                    allowClear: true,
                    closeOnSelect: false
                });

                // Show modal
                $('#exportExcelModal').modal('show');
            });

            $('#locEdit').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#modalFormEdit'),
                placeholder: 'Cari Location...',
                allowClear: true,
                ajax: {
                    url: "{{ route('wrm.inventory.getLocationsAjax') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            no_spb: $('#noSpbEdit').val(),
                            mid_id: $('#barangIdEdit').val(),
                            id: $('#id').val()
                        };
                    },
                    processResults: function(res) {
                        return {
                            results: res.data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });

            let filterTimeout;
            let currentSortDir = 'desc';
            let selectAllTotal = false;
            let totalRecords = 0;

            loadData();
            loadFilter();

            function getQueryParams() {
                let params = {
                    per_page: $('#perPage').val() || 25,
                    sort_dir: currentSortDir
                };

                const addFilter = (key, selector) => {
                    let val = $(selector).val();
                    if (val) {
                        if (Array.isArray(val)) {
                            val = val.filter(v => v !== null && v !== undefined && String(v).trim() !== "");
                            if (val.length > 0) params[key] = val;
                        } else if (String(val).trim() !== "") {
                            params[key] = val;
                        }
                    }
                };

                addFilter('group', '#filterGroup');
                addFilter('mid', '#filterMid');
                addFilter('start_date', '#filterStartDate');
                addFilter('end_date', '#filterEndDate');
                addFilter('supplier', '#filterSupplier');
                addFilter('status', '#filterStatus');
                addFilter('no_spb', '#filterNoSpb');
                addFilter('location', '#filterLocation');
                addFilter('catatan', '#filterCatatan');

                return params;
            }

            function loadData(page = 1) {
                let params = getQueryParams();
                params.page = page;

                $('#tableStock tbody').html(`
                    <tr>
                        <td colspan="16" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="mt-2 text-muted">Memuat data...</span>
                            </div>
                        </td>
                    </tr>
                `);

                $.get("{{ route('wrm.inventory.getData') }}", params, function(res) {

                    let html = '';
                    let data = res.data.data;
                    let startNo = res.data.from;

                    if (data.length === 0) {

                        html = `
                            <tr>
                                <td colspan="16" class="text-center text-muted py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="mdi mdi-database-off-outline" style="font-size:32px"></i>
                                        <span class="mt-2">Data tidak ditemukan</span>
                                    </div>
                                </td>
                            </tr>
                        `;

                    } else {

                        data.forEach((d, index) => {

                            html += `
                                <tr>
                                    @can('permission', 'wrm-inventory-soh-plus')
                                    <td class="text-center align-middle">
                                        <input type="checkbox" class="form-check-input checkItem checkbox-xl" value="${d.id}">
                                    </td>
                                    @endcan
                                    <td class="text-center">${startNo + index}</td>
                                    <td>${d.barcode ?? '-'}</td>
                                    <td>${d.no_spb}</td>
                                    <td>${d.barang.mid}</td>
                                    <td>${d.barang.nama_barang}</td>
                                    <td>${d.barang.uom}</td>
                                    <td>${d.group ?? '-'}</td>
                                    <td>${d.pallet_id}</td>
                                    <td>${numberFormat(d.qty)}</td>
                                    <td>${d.status.toUpperCase()}</td>
                                    <td>${d.bin.location.plant} - ${d.bin.location.s_loc} - ${d.bin.location.gudang} - ${d.bin.location.zona} - ${d.bin.location.bin} - ${d.bin.kolom}.${d.bin.level}</td>
                                    <td>${d.supplier}</td>
                                    <td>${d.incoming_date}</td>
                                    <td>${d.catatan ?? ''}</td>
                                    <td>${d.expired_date ?? ''}</td>

                                    @can('permission', 'wrm-inventory-soh-plus')
                                    <td class="text-center">

                                        <button class="btn btn-sm btn-warning btnEdit"
                                            data-data="${encodeURIComponent(JSON.stringify(d))}">
                                            Edit
                                        </button>

                                        <button class="btn btn-sm btn-danger btnDelete"
                                            data-id="${d.id}">
                                            Hapus
                                        </button>

                                    </td>
                                    @endcan
                                </tr>
                            `;
                        });

                    }

                    $('#tableStock tbody').html(html);

                    totalRecords = res.summary.total_pallet;

                    // Re-apply selection state
                    if (selectAllTotal) {
                        $('.checkItem').prop('checked', true);
                        $('#checkAll').prop('checked', true);
                    } else {
                        $('#checkAll').prop('checked', false);
                    }

                    toggleBulkButton();

                    renderPagination(res.data);
                    updateSummary(res.summary);

                });
            }

            // --- Bulk Action Logic ---
            $(document).on('change', '#checkAll', function() {
                if (!$(this).prop('checked')) {
                    selectAllTotal = false;
                }
                $('.checkItem').prop('checked', $(this).prop('checked'));
                toggleBulkButton();
            });

            $(document).on('change', '.checkItem', function() {
                if (!$(this).prop('checked')) {
                    selectAllTotal = false;
                }

                if ($('.checkItem:checked').length === $('.checkItem').length) {
                    $('#checkAll').prop('checked', true);
                } else {
                    $('#checkAll').prop('checked', false);
                }
                toggleBulkButton();
            });

            function toggleBulkButton() {
                const checkedCount = $('.checkItem:checked').length;
                const totalOnPage = $('.checkItem').length;

                if (selectAllTotal) {
                    $('#selectedCount').text(numberFormat(totalRecords));
                    $('#selectionText').html(
                        `<span class="text-success fw-bold">All ${numberFormat(totalRecords)} items in stock are selected.</span>`
                    );
                    $('#bulkToolbar').show();
                    $('#selectAllBanner').hide();
                    $('#allSelectedBanner').show();
                    $('#totalCountText2').text(numberFormat(totalRecords));
                } else {
                    $('#selectedCount').text(checkedCount);
                    $('#selectionText').text('Choose an action to apply to all selected records');
                    $('#allSelectedBanner').hide();

                    if (checkedCount > 0) {
                        $('#bulkToolbar').show();

                        if (checkedCount === totalOnPage && totalRecords > totalOnPage) {
                            $('#selectAllBanner').show();
                            $('#pageCountText').text(checkedCount);
                            $('#totalCountText').text(numberFormat(totalRecords));
                        } else {
                            $('#selectAllBanner').hide();
                        }
                    } else {
                        $('#bulkToolbar').hide();
                        $('#selectAllBanner').hide();
                    }
                }

                // Highlighting rows
                $('.checkItem').each(function() {
                    if ($(this).prop('checked')) {
                        $(this).closest('tr').addClass('row-selected');
                    } else {
                        $(this).closest('tr').removeClass('row-selected');
                    }
                });
            }

            function checkBulkSelection() {
                if (selectAllTotal) return true;
                if ($('.checkItem:checked').length > 0) return true;

                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silahkan pilih minimal satu item terlebih dahulu!'
                });
                return false;
            }

            $(document).on('click', '#btnSelectAllTotal', function() {
                selectAllTotal = true;
                $('.checkItem, #checkAll').prop('checked', true);
                toggleBulkButton();
            });

            $(document).on('click', '#btnClearTotalSelection', function() {
                selectAllTotal = false;
                $('.checkItem, #checkAll').prop('checked', false);
                toggleBulkButton();
            });

            $(document).on('click', '#clearSelection', function() {
                selectAllTotal = false;
                $('.checkItem, #checkAll').prop('checked', false);
                toggleBulkButton();
            });

            $(document).on('click', '.bulk-status', function(e) {
                e.preventDefault();

                if (!checkBulkSelection()) return;

                const status = $(this).data('status');
                const selectedIds = [];

                if (!selectAllTotal) {
                    $('.checkItem:checked').each(function() {
                        selectedIds.push($(this).val());
                    });
                }

                const countText = selectAllTotal ? totalRecords : selectedIds.length;

                Swal.fire({
                    title: 'Konfirmasi Massal',
                    text: `Apakah Anda yakin ingin mengubah status ${countText} item menjadi ${status}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Update!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        let ajaxData = {
                            _token: "{{ csrf_token() }}",
                            status: status,
                            select_all: selectAllTotal ? 1 : 0
                        };

                        if (selectAllTotal) {
                            ajaxData.group = $('#filterGroup').val();
                            ajaxData.mid = $('#filterMid').val();
                            ajaxData.start_date = $('#filterStartDate').val();
                            ajaxData.end_date = $('#filterEndDate').val();
                            ajaxData.supplier = $('#filterSupplier').val();
                            ajaxData.no_spb = $('#filterNoSpb').val();
                            ajaxData.location = $('#filterLocation').val();
                            ajaxData.catatan = $('#filterCatatan').val();
                        } else {
                            ajaxData.ids = selectedIds;
                        }

                        $.ajax({
                            url: "{{ route('wrm.inventory.mass-update-status') }}",
                            method: 'POST',
                            data: ajaxData,
                            success: function(res) {
                                Swal.fire('Berhasil', res.message, 'success');
                                selectAllTotal = false;
                                loadData();
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message ??
                                    'Terjadi kesalahan', 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#bulkGroup', function(e) {
                e.preventDefault();

                if (!checkBulkSelection()) return;

                const selectedIds = [];

                if (!selectAllTotal) {
                    $('.checkItem:checked').each(function() {
                        selectedIds.push($(this).val());
                    });
                }

                const countText = selectAllTotal ? totalRecords : selectedIds.length;

                Swal.fire({
                    title: 'Update Group Massal',
                    text: `Masukkan nama group baru untuk ${countText} item terpilih:`,
                    input: 'text',
                    inputPlaceholder: 'Nama Group...',
                    showCancelButton: true,
                    confirmButtonText: 'Update Group',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Nama group tidak boleh kosong!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const newGroup = result.value;
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        let ajaxData = {
                            _token: "{{ csrf_token() }}",
                            group: newGroup,
                            select_all: selectAllTotal ? 1 : 0
                        };

                        if (selectAllTotal) {
                            ajaxData.group_filter = $('#filterGroup')
                                .val(); // avoid conflict with group to update
                            ajaxData.mid = $('#filterMid').val();
                            ajaxData.start_date = $('#filterStartDate').val();
                            ajaxData.end_date = $('#filterEndDate').val();
                            ajaxData.supplier = $('#filterSupplier').val();
                            ajaxData.no_spb = $('#filterNoSpb').val();
                            ajaxData.location = $('#filterLocation').val();
                            ajaxData.catatan = $('#filterCatatan').val();
                        } else {
                            ajaxData.ids = selectedIds;
                        }

                        $.ajax({
                            url: "{{ route('wrm.inventory.mass-update-group') }}",
                            method: 'POST',
                            data: ajaxData,
                            success: function(res) {
                                Swal.fire('Berhasil', res.message, 'success');
                                selectAllTotal = false;
                                loadData();
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message ??
                                    'Terjadi kesalahan', 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#bulkDelete', function(e) {
                e.preventDefault();

                if (!checkBulkSelection()) return;

                const selectedIds = [];

                if (!selectAllTotal) {
                    $('.checkItem:checked').each(function() {
                        selectedIds.push($(this).val());
                    });
                }

                const countText = selectAllTotal ? totalRecords : selectedIds.length;

                Swal.fire({
                    title: 'Hapus Massal',
                    text: `Apakah Anda yakin ingin menghapus ${countText} item terpilih? Tindakan ini tidak dapat dibatalkan!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        let ajaxData = {
                            _token: "{{ csrf_token() }}",
                            select_all: selectAllTotal ? 1 : 0
                        };

                        if (selectAllTotal) {
                            ajaxData.group = $('#filterGroup').val();
                            ajaxData.mid = $('#filterMid').val();
                            ajaxData.start_date = $('#filterStartDate').val();
                            ajaxData.end_date = $('#filterEndDate').val();
                            ajaxData.supplier = $('#filterSupplier').val();
                            ajaxData.no_spb = $('#filterNoSpb').val();
                            ajaxData.location = $('#filterLocation').val();
                            ajaxData.catatan = $('#filterCatatan').val();
                        } else {
                            ajaxData.ids = selectedIds;
                        }

                        $.ajax({
                            url: "{{ route('wrm.inventory.mass-delete') }}",
                            method: 'POST',
                            data: ajaxData,
                            success: function(res) {
                                Swal.fire('Berhasil', res.message, 'success');
                                selectAllTotal = false;
                                loadData();
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message ??
                                    'Terjadi kesalahan', 'error');
                            }
                        });
                    }
                });
            });

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

                $('#totalQty').text(numberFormat(summary.total_qty));
                $('#totalPalletsDisplay').text(numberFormat(summary.total_pallet));

                $('#unrestQty').text(numberFormat(unrest.total_qty));
                $('#unrestPallets').text(numberFormat(unrest.count));

                $('#qiQty').text(numberFormat(qi.total_qty));
                $('#qiPallets').text(numberFormat(qi.count));

                $('#blockedQty').text(numberFormat(blocked.total_qty));
                $('#blockedPallets').text(numberFormat(blocked.count));
            }

            $('#btnTambah').click(() => {
                $('#formStock')[0].reset();
                $('#id').val('');
                $('#modalForm').modal('show');
            });


            $(document).on('click', '.btnEdit', function() {

                let detail = JSON.parse(decodeURIComponent($(this).data('data')));

                $('#titleForm').text('Edit Stock Gula');

                $('#id').val(detail.id);

                $('#noSpbEdit').val(detail.no_spb);
                $('#supplierEdit').val(detail.supplier ?? '').trigger('change');

                // Format date to YYYY-MM-DD for HTML date input
                if (detail.incoming_date) {
                    $('#incomingEdit').val(detail.incoming_date.substring(0, 10));
                } else {
                    $('#incomingEdit').val('');
                }

                $('#barangIdEdit').val(detail.barang.id);
                $('#midEdit').val(`${detail.barang.mid} - ${detail.barang.nama_barang}`);
                $('#qtyEdit').val(parseFloat(detail.qty));
                $('#statusEdit').val(detail.status);
                $('#groupEdit').val(detail.group);
                $('#palletEdit').val(detail.pallet_id ?? '');
                $('#catatan').val(detail.catatan ?? '');

                // Handle AJAX value for location
                let locSelect = $('#locEdit');
                if (detail.bin) {
                    let bin = detail.bin;
                    let loc = bin.location;
                    let optionText =
                        `${loc.plant} - ${loc.s_loc} - ${loc.gudang} - ${loc.zona} - ${loc.bin} - (${bin.kolom}.${bin.level})`;

                    // Append and select the option for AJAX select2
                    let newOption = new Option(optionText, detail.loc_id, true, true);
                    locSelect.append(newOption).trigger('change');
                } else {
                    locSelect.val(null).trigger('change');
                }

                $('#modalFormEdit').modal('show');
            });

            $('#formStockEdit').on('submit', function(e) {

                e.preventDefault();

                let id = $('#id').val();

                $.ajax({
                    url: `{{ route('wrm.inventory.update', '') }}/` + id,
                    method: 'POST',
                    data: $(this).serialize() + '&_method=PUT',
                    beforeSend() {
                        Swal.fire({
                            title: 'Menyimpan...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },

                    success(res) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message
                        });

                        $('#modalFormEdit').modal('hide');

                        loadData();

                    },

                    error(xhr) {

                        let message = 'Terjadi kesalahan';

                        if (xhr.status === 422) {

                            let errors = xhr.responseJSON.errors;

                            message = Object.values(errors)
                                .map(v => v[0])
                                .join('<br>');

                        }

                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: message
                        });

                    }

                });

            });

            $(document).on('click', '.btnDelete', function() {

                let id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin hapus?',
                    text: 'Data tidak bisa dikembalikan',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus'
                }).then((result) => {

                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '/wrm/inventory/delete/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        beforeSend() {
                            Swal.fire({
                                title: 'Menghapus...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                        },
                        success: function(res) {
                            loadData();

                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.message || 'Data berhasil dihapus',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            let message = 'Terjadi kesalahan pada server';

                            if (xhr.status === 404) {
                                message = 'Data tidak ditemukan';
                            }

                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON?.errors;
                                if (errors) {
                                    message = Object.values(errors)
                                        .map(v => v[0])
                                        .join('<br>');
                                }
                            }

                            if (xhr.responseJSON?.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: message
                            });
                        }
                    });
                });
            });

            // Event listener for all filters
            $('.select2-filter, #filterStartDate, #filterEndDate').on('change', function() {
                // Check if this change was triggered manually or by select2 update
                if ($(this).hasClass('updating') || $('body').hasClass('is-resetting')) return;

                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    loadFilter();
                    loadData();
                }, 300);
            });

            $('#filterCatatan').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    loadData();
                    return;
                }
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    loadData();
                }, 500);
            });

            $('#btnFilter').click(function() {
                loadData();
            });

            $('#sortDate').click(function() {
                currentSortDir = currentSortDir === 'desc' ? 'asc' : 'desc';

                // Update Icon
                if (currentSortDir === 'asc') {
                    $('#sortIcon').removeClass('mdi-sort mdi-sort-descending').addClass(
                        'mdi-sort-ascending');
                } else {
                    $('#sortIcon').removeClass('mdi-sort mdi-sort-ascending').addClass(
                        'mdi-sort-descending');
                }

                loadData();
            });

            $('#btnReset').click(function() {
                $('body').addClass('is-resetting');

                $('.select2-filter').val(null).trigger('change');
                $('#filterStartDate').val('');
                $('#filterEndDate').val('');
                $('#filterCatatan').val('');

                selectAllTotal = false;

                setTimeout(() => {
                    $('body').removeClass('is-resetting');
                    loadFilter();
                    loadData(1);
                }, 100);
            });

            function loadFilter() {
                let params = getQueryParams();

                $.get("{{ route('wrm.inventory.getFilter') }}", params, function(res) {
                    updateDropdown('#filterGroup', res.groups);
                    updateDropdown('#filterNamaBarang', res.jenis_bahan);
                    updateDropdown('#filterSupplier', res.suppliers);
                    updateDropdown('#filterNoSpb', res.no_spbs);
                    updateDropdown('#filterMid', res.mids, true);
                    updateDropdown('#filterLocation', res.locations, false, true);
                });
            }

            function updateDropdown(selector, data, isMid = false, isLocation = false) {
                let $el = $(selector);
                let currentValues = $el.val() || [];

                $el.addClass('updating');
                $el.empty();

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
                    let option = new Option(safeText, safeVal, isSelected, isSelected);
                    $el.append(option);
                });

                $el.trigger('change');
                $el.removeClass('updating');
            }

            function renderPagination(data) {

                let html = '';

                let current = data.current_page;
                let last = data.last_page;

                html +=
                    `<button class="btn btn-sm btn-light page-btn" data-page="${current-1}" ${current==1?'disabled':''}>Prev</button>`;

                let start = Math.max(1, current - 2);
                let end = Math.min(last, current + 2);

                if (start > 1) {
                    html += `<button class="btn btn-sm btn-light page-btn" data-page="1">1</button>`;
                    if (start > 2) html += `<span class="mx-1">...</span>`;
                }

                for (let i = start; i <= end; i++) {

                    html += `
                        <button class="btn btn-sm ${i==current?'btn-primary':'btn-light'} page-btn"
                        data-page="${i}">
                        ${i}
                        </button>
                    `;
                }

                if (end < last) {

                    if (end < last - 1) html += `<span class="mx-1">...</span>`;

                    html += `<button class="btn btn-sm btn-light page-btn" data-page="${last}">${last}</button>`;
                }

                html +=
                    `<button class="btn btn-sm btn-light page-btn" data-page="${current+1}" ${current==last?'disabled':''}>Next</button>`;

                $('#pagination').html(html);
            }

            $(document).on('click', '.page-btn', function() {

                let page = $(this).data('page');

                loadData(page);

            });
            $(document).on('change', '#perPage', function() {
                loadData(1);
            });
        })
    </script>
@endsection
