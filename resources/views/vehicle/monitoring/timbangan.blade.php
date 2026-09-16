@extends('layouts.app')

@section('title', '| Timbangan (Scales)')

@section('styles')
    <style>
        .select2-container .select2-selection--single {
            height: 37px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 35px !important;
            padding-left: 12px !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #adb5bd !important;
        }

        .select2-container .select2-selection--multiple {
            min-height: 37px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3577f1 !important;
            border: none !important;
            /* color: #fff !important; */
            font-size: 0.85rem !important;
            padding: 2px 8px !important;
            border-radius: 4px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            /* color: #fff !important; */
            margin-right: 5px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #f06548 !important;
            background-color: transparent !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Timbangan (Scales)</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">Timbangan</li>
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

            <div class="row">
                <!-- Check-in Form -->
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                            <h4 class="card-title mb-0 flex-grow-1"><i
                                    class="ri-login-box-line me-2 align-middle text-primary"></i>Check-In Kendaraan Masuk
                            </h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vehicle.monitoring.timbangan.check_in') }}" method="POST"
                                id="checkInForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="no_pol" class="form-label">No. Polisi (Plate Number) <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="no_pol" name="no_pol" required>
                                        <option value="" selected disabled>Pilih atau Ketik No. Polisi</option>
                                    </select>
                                    <small class="text-muted">Pilih no. polisi dari data supplier atau ketik baru.</small>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="jenis" class="form-label">Jenis<span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="jenis" name="jenis" required>
                                            <option value="" selected disabled>Pilih Jenis</option>
                                            <option value="bongkaran">Bongkaran</option>
                                            <option value="slipsheet">Slipsheet</option>
                                            <option value="curah">Curah</option>
                                            <option value="retur">Retur</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="target_location_id" class="form-label">Area (Sloc)
                                            <span class="text-danger">*</span></label>
                                        <select class="form-select" id="target_location_id" name="target_location_id"
                                            required>
                                            <option value="" selected disabled>Pilih Tujuan Area</option>
                                            @foreach ($targetLocations as $loc)
                                                <option value="{{ $loc->id }}" data-sloc="{{ $loc->s_loc }}">
                                                    {{ $loc->s_loc }} -
                                                    {{ $loc->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="vendor" class="form-label">Nama Vendor <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="vendor" name="vendor">
                                            <option value="" selected disabled>Pilih Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{ $vendor->name }}">{{ $vendor->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="item_id" class="form-label">Item <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select select2" id="item_id" name="item_id" required>
                                            <option value="" selected disabled>Pilih Item</option>
                                            @foreach ($items as $item)
                                                <option value="{{ $item->id }}"
                                                    data-location-id="{{ $item->location_id }}">{{ $item->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="no_spb" class="form-label">No. SPB</label>
                                        <input type="text" class="form-control" id="no_spb" name="no_spb"
                                            placeholder="Nomor Surat Perintah Bongkar">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="qty_spb" class="form-label">Qty SPB</label>
                                        <input type="number" class="form-control" id="qty_spb" name="qty_spb"
                                            step="any" placeholder="Kuantitas SPB">
                                    </div>
                                </div>
                                <div class="row">
                                    <input type="hidden" id="nama_driver" name="nama_driver">
                                    <input type="hidden" id="no_hp_driver" name="no_hp_driver">
                                    <input type="hidden" id="checkin_pos1" name="checkin_pos1">
                                    <input type="hidden" id="trnvisitorid" name="trnvisitorid">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary"><i
                                                class="ri-save-line me-1 align-middle"></i>Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Daily Check-In Data -->
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header align-items-center d-flex flex-wrap gap-2 border-0 bg-transparent py-3">
                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                <h4 class="card-title mb-0"><i
                                        class="ri-table-line me-2 align-middle text-success"></i>Data Kendaraan Aktif
                                </h4>
                                <span class="badge bg-soft-success text-success border border-success-subtle px-2 py-1 fs-12 fw-semibold" id="badgeReadyCheckout" style="cursor: pointer;" title="Klik untuk filter Siap Check-Out">
                                    <i class="ri-scales-3-line me-1 align-middle"></i>Siap Check-Out: <strong id="countReadyCheckout">0</strong>
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap flex-shrink-0">
                                <div style="min-width: 175px;">
                                    <select class="form-select form-select-sm" id="filter_status">
                                        <option value="">Semua Status</option>
                                        <option value="siap_checkout">⚡ Siap Check-Out</option>
                                        <option value="timbangan_in">Baru Check-In</option>
                                        <option value="antri_sampling">Antri QC</option>
                                        <option value="sampling">Sampling QC</option>
                                        <option value="wrm_bongkar">WRM Area</option>
                                        <option value="wpm">WPM Area</option>
                                        <option value="wfg">WFG Area</option>
                                        <option value="smu">SMU Area</option>
                                    </select>
                                </div>
                                <div style="width: 240px;">
                                    <input type="text" class="form-control form-control-sm" id="search_table"
                                        placeholder="Cari No. Polisi / Vendor...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>No. Polisi</th>
                                            <th>Jenis</th>
                                            <th>Vendor</th>
                                            <th>No. SPB / Qty</th>
                                            <th>Tujuan Sloc</th>
                                            <th>Status</th>
                                            <th>Waktu Masuk</th>
                                            <th>Waktu Keluar</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="timbanganTableBody">
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small" id="paginationInfo">
                                    Showing 0 to 0 of 0 entries
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-rounded mb-0" id="paginationLinks">
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editModal" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="ri-edit-line me-2 align-middle text-warning"></i>Edit Data Check-In
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_no_pol" class="form-label">No. Polisi <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_no_pol" name="no_pol" required
                                    style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_vendor" class="form-label">Nama Vendor</label>
                                <select class="form-select" id="edit_vendor" name="vendor">
                                    <option value="" selected disabled>Pilih Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->name }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_nama_driver" class="form-label">Nama Driver</label>
                                <input type="text" class="form-control" id="edit_nama_driver" name="nama_driver"
                                    placeholder="Masukkan nama driver">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_no_hp_driver" class="form-label">No. HP Driver</label>
                                <input type="text" class="form-control" id="edit_no_hp_driver" name="no_hp_driver"
                                    placeholder="Contoh: 08123456789">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_jenis" class="form-label">Jenis <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_jenis" name="jenis" required>
                                    <option value="bongkaran">Bongkaran</option>
                                    <option value="slipsheet">Slipsheet</option>
                                    <option value="curah">Curah</option>
                                    <option value="retur">Retur</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_target_location_id" class="form-label">Area (Sloc) <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_target_location_id" name="target_location_id"
                                    required>
                                    @foreach ($targetLocations as $loc)
                                        <option value="{{ $loc->id }}" data-sloc="{{ $loc->s_loc }}">
                                            {{ $loc->s_loc }} - {{ $loc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_item_id" class="form-label">Item <span
                                        class="text-danger">*</span></label>
                                <select class="form-select select2-edit" id="edit_item_id" name="item_id" required>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}" data-location-id="{{ $item->location_id }}">
                                            {{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_no_spb" class="form-label">No. SPB</label>
                                <input type="text" class="form-control" id="edit_no_spb" name="no_spb"
                                    placeholder="Nomor Surat Perintah Bongkar">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_qty_spb" class="form-label">Qty SPB</label>
                                <input type="number" class="form-control" id="edit_qty_spb" name="qty_spb"
                                    step="any" placeholder="10000">
                            </div>
                        </div>
                        <input type="hidden" id="edit_checkin_pos1" name="checkin_pos1">
                        <input type="hidden" id="edit_trnvisitorid" name="trnvisitorid">
                    </div>
                    <div class="modal-footer border-0 bg-light p-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for Check-In Form
            $('#item_id').select2({
                width: '100%'
            });

            $('#vendor').select2({
                tags: true,
                placeholder: 'Pilih atau Ketik Vendor Baru',
                allowClear: true,
                width: '100%'
            });

            // Initialize Select2 for Edit Modal
            $('#edit_item_id').select2({
                dropdownParent: $('#editModal'),
                allowClear: true,
                width: '100%'
            });

            $('#edit_vendor').select2({
                dropdownParent: $('#editModal'),
                tags: true,
                placeholder: 'Pilih atau Ketik Vendor Baru',
                allowClear: true,
                width: '100%'
            });

            function updateVendorDropdowns(vendors) {
                if (!vendors || !Array.isArray(vendors)) return;

                // Save currently selected values
                const currentVal = $('#vendor').val();
                const currentEditVal = $('#edit_vendor').val();

                // Clear and rebuild options
                $('#vendor').empty().append('<option value="" selected disabled>Pilih Vendor</option>');
                $('#edit_vendor').empty().append('<option value="" selected disabled>Pilih Vendor</option>');

                vendors.forEach(function(vendor) {
                    $('#vendor').append(new Option(vendor.name, vendor.name));
                    $('#edit_vendor').append(new Option(vendor.name, vendor.name));
                });

                // Restore selected values
                if (currentVal) $('#vendor').val(currentVal).trigger('change');
                if (currentEditVal) $('#edit_vendor').val(currentEditVal).trigger('change');
            }

            // Initialize Select2 for Plate Number (no_pol) with tagging
            $('#no_pol').select2({
                tags: true,
                placeholder: 'Pilih atau Ketik No. Polisi Baru',
                allowClear: true,
                width: '100%',
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    // Format plate number: uppercase and remove all spaces
                    var cleanedTerm = term.toUpperCase().replace(/\s+/g, '');
                    return {
                        id: cleanedTerm,
                        text: cleanedTerm,
                        newTag: true
                    };
                }
            });

            // Global variable to store supplier data from API
            let supplierDataList = [];

            // Function to fetch supplier data from proxy route
            function loadSupplierData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.timbangan.supplier_data') }}",
                    type: 'GET',
                    success: function(response) {
                        if (response && response.success && Array.isArray(response.data)) {
                            supplierDataList = response.data;

                            // Rebuild select options
                            const noPolSelect = $('#no_pol');
                            const currentVal = noPolSelect.val();

                            noPolSelect.empty().append(
                                '<option value="" selected disabled>Pilih atau Ketik No. Polisi</option>'
                            );

                            supplierDataList.forEach(function(item) {
                                // Clean spaces from plate number
                                const cleanedNopol = item.nopol.toUpperCase().replace(/\s+/g,
                                    '');
                                const optionText = cleanedNopol + ' (' + item.nama_perusahaan +
                                    ')';

                                // Create option if not already exists in the select
                                if (noPolSelect.find("option[value='" + cleanedNopol + "']")
                                    .length === 0) {
                                    const option = new Option(optionText, cleanedNopol, false,
                                        false);
                                    noPolSelect.append(option);
                                }
                            });

                            // Restore value if still valid
                            if (currentVal) {
                                noPolSelect.val(currentVal).trigger('change.select2');
                            } else {
                                noPolSelect.trigger('change.select2');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Gagal mengambil data supplier dari API:', xhr);
                    }
                });
            }

            // Load on init
            loadSupplierData();

            // Handle plate selection change to auto populate vendor name
            $('#no_pol').on('change', function() {
                const selectedNopol = $(this).val();
                if (!selectedNopol) {
                    $('#nama_driver').val('');
                    $('#no_hp_driver').val('');
                    return;
                }

                // Find the matched supplier item from the API response
                const matchedSupplier = supplierDataList.find(function(item) {
                    return item.nopol.toUpperCase().replace(/\s+/g, '') === selectedNopol
                        .toUpperCase().replace(/\s+/g, '');
                });

                if (matchedSupplier) {
                    if (matchedSupplier.nama_perusahaan) {
                        // Clean company name (remove leading '- ' if any)
                        let vendorName = matchedSupplier.nama_perusahaan.trim();
                        if (vendorName.startsWith('- ')) {
                            vendorName = vendorName.substring(2).trim();
                        } else if (vendorName.startsWith('-')) {
                            vendorName = vendorName.substring(1).trim();
                        }

                        // Auto populate vendor Select2
                        if ($("#vendor").find("option[value='" + vendorName + "']").length === 0) {
                            const newOption = new Option(vendorName, vendorName, true, true);
                            $("#vendor").append(newOption).trigger('change');
                        } else {
                            $("#vendor").val(vendorName).trigger('change');
                        }
                    }

                    // Auto populate driver name, phone & supplier check-in pos 1
                    $('#nama_driver').val(matchedSupplier.nama_driver || '');
                    $('#no_hp_driver').val(matchedSupplier.no_hp_driver || '');
                    $('#checkin_pos1').val(matchedSupplier.checkin_pos1 || '');
                    $('#trnvisitorid').val(matchedSupplier.trnvisitorid || '');
                } else {
                    $('#nama_driver').val('');
                    $('#no_hp_driver').val('');
                    $('#checkin_pos1').val('');
                    $('#trnvisitorid').val('');
                }
            });

            fetchTransactions();

            // Handle table search
            $('#search_table').on('keyup', function() {
                searchQuery = $(this).val().toLowerCase();
                currentPage = 1; // Reset to page 1 on search
                renderTransactions();
            });

            // Handle status filter
            $('#filter_status').on('change', function() {
                statusFilter = $(this).val();
                currentPage = 1; // Reset to page 1 on filter
                renderTransactions();
            });

            // Quick toggle for Ready Check-Out badge
            $('#badgeReadyCheckout').on('click', function() {
                if ($('#filter_status').val() === 'siap_checkout') {
                    $('#filter_status').val('');
                } else {
                    $('#filter_status').val('siap_checkout');
                }
                $('#filter_status').trigger('change');
            });

            let allTransactions = [];
            let currentPage = 1;
            const itemsPerPage = 10;
            let searchQuery = '';
            let statusFilter = '';

            // Fetch transaction data via AJAX
            function fetchTransactions() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.timbangan.data') }}",
                    type: 'GET',
                    success: function(response) {
                        if (Array.isArray(response)) {
                            allTransactions = response;
                        } else {
                            allTransactions = response.transactions || response.data || [];
                            if (response.pending_followups && response.pending_followups.length > 0) {
                                response.pending_followups.forEach(function(fu) {
                                    triggerTimbanganFollowUpAlert(fu);
                                });
                            }
                        }
                        renderTransactions();
                    },
                    error: function(xhr) {
                        console.error('Failed to load transaction data', xhr);
                    }
                });
            }

            // Generate detailed multi-stage status badge
            function renderDetailedStatusBadge(tx) {
                let html = '<div class="d-flex flex-column gap-1 align-items-start">';
                const status = (tx.status || '').toLowerCase();
                const jenisRaw = (tx.jenis_raw || tx.jenis || '').toLowerCase();
                const actionLabel = jenisRaw === 'bongkaran' ? 'Bongkar' : 'Muat';

                // 1. Primary Status Badge
                if (status === 'completed') {
                    html +=
                        `<span class="badge bg-soft-success text-success fs-12 px-2 py-1"><i class="ri-checkbox-circle-line me-1 align-middle"></i>Selesai (Out)</span>`;
                } else if (status === 'timbangan_out') {
                    html +=
                        `<span class="badge bg-soft-success text-success fs-12 fw-bold px-2 py-1"><i class="ri-scales-3-line me-1 align-middle"></i>Siap Check-Out</span>`;
                    html +=
                        `<small class="text-muted"><i class="ri-map-pin-line me-1"></i>Timbangan Keluar</small>`;
                } else if (status === 'antri_sampling') {
                    const antrianText = tx.no_antrian ? ` #${tx.no_antrian}` : '';
                    html +=
                        `<span class="badge bg-soft-warning text-warning fs-12 px-2 py-1"><i class="ri-time-line me-1 align-middle"></i>Antri QC${antrianText}</span>`;
                    html += `<small class="text-muted"><i class="ri-flask-line me-1"></i>Menunggu Sampling</small>`;
                } else if (status === 'sampling' || tx.qc_status === 'on_check') {
                    html +=
                        `<span class="badge bg-soft-info text-info fs-12 px-2 py-1"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Sampling QC</span>`;
                    if (tx.start_sampling_time) {
                        html +=
                            `<small class="text-muted"><i class="ri-time-line me-1"></i>Mulai: <strong>${tx.start_sampling_time}</strong></small>`;
                    } else {
                        html +=
                            `<small class="text-muted"><i class="ri-test-tube-line me-1"></i>Sedang Diperiksa</small>`;
                    }
                } else if (status === 'wrm_bongkar' || tx.target_sloc === 'B006') {
                    if (tx.unloading_status === 'process') {
                        html +=
                            `<span class="badge bg-soft-info text-info fs-12 px-2 py-1"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Proses ${actionLabel} WRM</span>`;
                        if (tx.start_loading_time) {
                            html +=
                                `<small class="text-muted"><i class="ri-time-line me-1"></i>Mulai: <strong>${tx.start_loading_time}</strong></small>`;
                        }
                    } else if (tx.unloading_status === 'completed') {
                        html +=
                            `<span class="badge bg-soft-success text-success fs-12 px-2 py-1"><i class="ri-checkbox-circle-line me-1 align-middle"></i>Selesai ${actionLabel} WRM</span>`;
                    } else {
                        html +=
                            `<span class="badge bg-soft-secondary text-secondary fs-12 px-2 py-1"><i class="ri-hourglass-line me-1 align-middle"></i>Menunggu ${actionLabel} WRM</span>`;
                        html +=
                            `<small class="text-muted"><i class="ri-map-pin-line me-1"></i>B006 - WRM Area</small>`;
                    }
                } else if (status === 'wpm' || tx.target_sloc === 'C001') {
                    if (tx.unloading_status === 'process') {
                        html +=
                            `<span class="badge bg-soft-info text-info fs-12 px-2 py-1"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Proses Bongkar WPM</span>`;
                        if (tx.start_loading_time) {
                            html +=
                                `<small class="text-muted"><i class="ri-time-line me-1"></i>Mulai: <strong>${tx.start_loading_time}</strong></small>`;
                        }
                    } else if (tx.unloading_status === 'completed') {
                        html +=
                            `<span class="badge bg-soft-success text-success fs-12 px-2 py-1"><i class="ri-checkbox-circle-line me-1 align-middle"></i>Selesai Bongkar WPM</span>`;
                    } else {
                        html +=
                            `<span class="badge bg-soft-secondary text-secondary fs-12 px-2 py-1"><i class="ri-hourglass-line me-1 align-middle"></i>Menunggu Bongkar WPM</span>`;
                        html +=
                            `<small class="text-muted"><i class="ri-map-pin-line me-1"></i>C001 - WPM Area</small>`;
                    }
                } else if (status === 'wfg' || tx.target_sloc === 'A001') {
                    if (tx.unloading_status === 'process') {
                        html +=
                            `<span class="badge bg-soft-info text-info fs-12 px-2 py-1"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Proses ${actionLabel} WFG</span>`;
                        if (tx.start_loading_time) {
                            html +=
                                `<small class="text-muted"><i class="ri-time-line me-1"></i>Mulai: <strong>${tx.start_loading_time}</strong></small>`;
                        }
                    } else if (tx.no_antrian) {
                        html +=
                            `<span class="badge bg-soft-warning text-warning fs-12 px-2 py-1"><i class="ri-time-line me-1 align-middle"></i>Antri WFG #${tx.no_antrian}</span>`;
                        html +=
                            `<small class="text-muted"><i class="ri-map-pin-line me-1"></i>A001 - WFG Area</small>`;
                    } else {
                        html +=
                            `<span class="badge bg-soft-secondary text-secondary fs-12 px-2 py-1"><i class="ri-hourglass-line me-1 align-middle"></i>Menunggu Antri WFG</span>`;
                        html +=
                            `<small class="text-muted"><i class="ri-map-pin-line me-1"></i>A001 - WFG Area</small>`;
                    }
                } else if (status === 'smu' || tx.target_sloc === 'SMU') {
                    if (tx.unloading_status === 'process') {
                        html +=
                            `<span class="badge bg-soft-info text-info fs-12 px-2 py-1"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Proses ${actionLabel} SMU</span>`;
                        if (tx.start_loading_time) {
                            html +=
                                `<small class="text-muted"><i class="ri-time-line me-1"></i>Mulai: <strong>${tx.start_loading_time}</strong></small>`;
                        }
                    } else if (tx.no_antrian) {
                        html +=
                            `<span class="badge bg-soft-warning text-warning fs-12 px-2 py-1"><i class="ri-time-line me-1 align-middle"></i>Antri SMU #${tx.no_antrian}</span>`;
                        html += `<small class="text-muted"><i class="ri-map-pin-line me-1"></i>SMU Area</small>`;
                    } else {
                        html +=
                            `<span class="badge bg-soft-secondary text-secondary fs-12 px-2 py-1"><i class="ri-hourglass-line me-1 align-middle"></i>Menunggu Antri SMU</span>`;
                        html += `<small class="text-muted"><i class="ri-map-pin-line me-1"></i>SMU Area</small>`;
                    }
                } else if (status === 'timbangan_in') {
                    html +=
                        `<span class="badge bg-soft-primary text-primary fs-12 px-2 py-1"><i class="ri-login-box-line me-1 align-middle"></i>Baru Check-In</span>`;
                    html += `<small class="text-muted"><i class="ri-map-pin-line me-1"></i>Timbangan Masuk</small>`;
                } else {
                    html +=
                        `<span class="badge bg-soft-secondary text-secondary fs-12 px-2 py-1">${(tx.status || '').toUpperCase()}</span>`;
                }

                // 2. Sub-badge QC Status (if relevant and not already covered by primary sampling status)
                if (tx.qc_status && tx.qc_status !== 'not_required') {
                    if (tx.qc_status === 'released') {
                        html +=
                            `<span class="badge bg-soft-success text-success fs-10 px-2 py-1 mt-1"><i class="ri-checkbox-circle-line me-1"></i>QC: Released</span>`;
                    } else if (tx.qc_status === 'rejected') {
                        html +=
                            `<span class="badge bg-soft-danger text-danger fs-10 px-2 py-1 mt-1"><i class="ri-close-circle-line me-1"></i>QC: Rejected</span>`;
                    } else if (tx.qc_status === 'on_check' && status !== 'sampling') {
                        html +=
                            `<span class="badge bg-soft-info text-info fs-10 px-2 py-1 mt-1"><i class="ri-loader-4-line ri-spin me-1"></i>QC: Sampling</span>`;
                    } else if (tx.qc_status === 'waiting_sampling' && status !== 'antri_sampling') {
                        html +=
                            `<span class="badge bg-soft-warning text-warning fs-10 px-2 py-1 mt-1"><i class="ri-time-line me-1"></i>QC: Antri</span>`;
                    } else if (tx.qc_status === 'waiting_dokumen' && status !== 'antri_sampling') {
                        html +=
                            `<span class="badge bg-soft-secondary text-secondary fs-10 px-2 py-1 mt-1"><i class="ri-file-list-line me-1"></i>QC: Waiting Dokumen</span>`;
                    }
                }

                html += '</div>';
                return html;
            }

            // Render transaction table rows
            function renderTransactions() {
                const tbody = $('#timbanganTableBody');
                tbody.empty();

                if (allTransactions.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">Belum ada transaksi aktif saat ini.</td>
                        </tr>
                    `);
                    $('#paginationInfo').text('Showing 0 to 0 of 0 entries');
                    $('#paginationLinks').empty();
                    return;
                }

                // Update ready check-out count and badge state
                const readyCount = allTransactions.filter(tx => (tx.status || '').toLowerCase() === 'timbangan_out').length;
                $('#countReadyCheckout').text(readyCount);

                if (statusFilter === 'siap_checkout') {
                    $('#badgeReadyCheckout').removeClass('bg-soft-success text-success').addClass('bg-success text-white shadow-sm');
                } else {
                    $('#badgeReadyCheckout').removeClass('bg-success text-white shadow-sm').addClass('bg-soft-success text-success');
                }

                // Filter transactions based on status filter and search query
                let filteredTransactions = allTransactions;

                if (statusFilter) {
                    if (statusFilter === 'siap_checkout' || statusFilter === 'timbangan_out') {
                        filteredTransactions = filteredTransactions.filter(function(tx) {
                            return (tx.status || '').toLowerCase() === 'timbangan_out';
                        });
                    } else if (statusFilter === 'timbangan_in') {
                        filteredTransactions = filteredTransactions.filter(function(tx) {
                            return (tx.status || '').toLowerCase() === 'timbangan_in';
                        });
                    } else if (statusFilter === 'antri_sampling') {
                        filteredTransactions = filteredTransactions.filter(function(tx) {
                            return (tx.status || '').toLowerCase() === 'antri_sampling';
                        });
                    } else if (statusFilter === 'sampling') {
                        filteredTransactions = filteredTransactions.filter(function(tx) {
                            const status = (tx.status || '').toLowerCase();
                            return status === 'sampling' || tx.qc_status === 'on_check';
                        });
                    } else if (statusFilter === 'wrm_bongkar') {
                        filteredTransactions = filteredTransactions.filter(function(tx) {
                            const status = (tx.status || '').toLowerCase();
                            return status === 'wrm_bongkar' || tx.target_sloc === 'B006';
                        });
                    } else if (statusFilter === 'wpm') {
                        filteredTransactions = filteredTransactions.filter(function(tx) {
                            const status = (tx.status || '').toLowerCase();
                            return status === 'wpm' || tx.target_sloc === 'C001';
                        });
                    } else if (statusFilter === 'wfg') {
                        filteredTransactions = filteredTransactions.filter(function(tx) {
                            const status = (tx.status || '').toLowerCase();
                            return status === 'wfg' || tx.target_sloc === 'A001';
                        });
                    } else if (statusFilter === 'smu') {
                        filteredTransactions = filteredTransactions.filter(function(tx) {
                            const status = (tx.status || '').toLowerCase();
                            return status === 'smu' || tx.target_sloc === 'SMU';
                        });
                    }
                }

                if (searchQuery) {
                    filteredTransactions = filteredTransactions.filter(function(tx) {
                        const nopol = (tx.no_pol || '').toLowerCase();
                        const vendor = (tx.vendor || '').toLowerCase();
                        const driver = (tx.nama_driver || '').toLowerCase();
                        const spb = (tx.no_spb || '').toLowerCase();
                        const item = (tx.item_name || '').toLowerCase();
                        return nopol.includes(searchQuery) || vendor.includes(searchQuery) || driver
                            .includes(searchQuery) || spb.includes(searchQuery) || item.includes(
                                searchQuery);
                    });
                }

                if (filteredTransactions.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">Tidak ditemukan data yang cocok dengan pencarian Anda.</td>
                        </tr>
                    `);
                    $('#paginationInfo').text('Showing 0 to 0 of 0 entries');
                    $('#paginationLinks').empty();
                    return;
                }

                // Calculate pagination ranges
                const totalItems = filteredTransactions.length;
                const totalPages = Math.ceil(totalItems / itemsPerPage);

                // Adjust currentPage if out of bounds
                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }
                if (currentPage < 1) {
                    currentPage = 1;
                }

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

                const paginatedItems = filteredTransactions.slice(startIndex, endIndex);

                paginatedItems.forEach(function(tx, index) {
                    const statusBadgeHtml = renderDetailedStatusBadge(tx);

                    let jenisBadge = 'bg-soft-secondary text-secondary';
                    if (tx.jenis_raw === 'bongkaran') jenisBadge = 'bg-soft-warning text-warning';
                    else if (tx.jenis_raw === 'slipsheet') jenisBadge = 'bg-soft-success text-success';
                    else if (tx.jenis_raw === 'curah') jenisBadge = 'bg-soft-primary text-primary';
                    else if (tx.jenis_raw === 'retur') jenisBadge = 'bg-soft-danger text-danger';

                    const checkOutButton = tx.status.toLowerCase() === 'timbangan_out' ?
                        `<button type="button" class="btn btn-success btn-sm btn-checkout-ajax shadow-sm fw-medium" data-id="${tx.id}" data-nopol="${tx.no_pol}" title="Check-Out Kendaraan">
                            <i class="ri-logout-box-r-line me-1 align-middle"></i>Check-Out
                        </button>` : '';

                    const followUpButton = (tx.status.toLowerCase() !== 'completed' && tx.status
                            .toLowerCase() !== 'timbangan_out') ?
                        `<button type="button" class="btn btn-soft-info btn-sm btn-followup-ajax" 
                            data-id="${tx.id}" 
                            data-nopol="${tx.no_pol}" 
                            data-target-sloc="${tx.target_sloc || ''}" 
                            data-target-name="${tx.target_name || ''}"
                            data-status="${tx.status}" 
                            data-qc-status="${tx.qc_status || ''}" 
                            title="Follow Up ke Area">
                            <i class="ri-notification-3-line me-1 align-middle"></i>Follow Up
                        </button>` : '';

                    const followUpInfo = tx.follow_up_time ?
                        `<div class="text-end mt-1"><span class="badge bg-soft-warning text-warning fs-11" title="Terakhir di-follow up ke ${tx.follow_up_target || 'Area'}"><i class="ri-time-line me-1"></i>Follow-up: ${tx.follow_up_time} (${tx.follow_up_target || 'Area'})</span></div>` :
                        '';

                    const row = `
                        <tr>
                            <td class="text-center"><small class="fw-bold">${index + (currentPage - 1) * itemsPerPage + 1}</small></td>
                            <td><span class="badge bg-soft-primary text-primary fs-12 fw-bold">${tx.no_pol}</span></td>
                            <td>
                                ${tx.item_name && tx.item_name !== '-' ? `<small class="text-dark fw-semibold">${tx.item_name}</small>` : ''}
                                <br><small class="text-muted">${tx.jenis}</small>
                            </td>
                            <td>
                                <strong>${tx.vendor || '-'}</strong><br>
                                <small class="text-muted"><i class="ri-user-line me-1"></i>${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                            </td>
                            <td>
                                <strong>${tx.no_spb || '-'}</strong><br>
                                <small class="text-muted">
                                    ${tx.qty_spb != null ? parseFloat(String(tx.qty_spb).replace(/,/g, '')) : '-'} Kg
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-soft-info text-info fs-11 px-2 py-1">${tx.target_sloc}</span>
                                ${tx.target_name && tx.target_name !== '-' ? `<br><small class="text-muted">${tx.target_name}</small>` : ''}
                            </td>
                            <td>${statusBadgeHtml}</td>
                            <td>
                                ${tx.check_in_date && tx.check_in_date !== '-' ? `<span class="fw-medium text-dark">${tx.check_in_date}</span><br><small class="text-muted"><i class="ri-time-line me-1"></i>${tx.check_in_clock}</small>` : (tx.check_in_time || '-')}
                                ${tx.checkin_pos1 && tx.checkin_pos1 !== '-' ? `<br><small class="text-info" title="Check-In Pos 1"><i class="ri-shield-check-line me-1"></i>Pos 1: ${tx.checkin_pos1_clock || tx.checkin_pos1}</small>` : ''}
                            </td>
                            <td>
                                ${tx.check_out_date && tx.check_out_date !== '-' ? `<span class="fw-medium text-dark">${tx.check_out_date}</span><br><small class="text-muted"><i class="ri-time-line me-1"></i>${tx.check_out_clock}</small>` : (tx.status.toLowerCase() === 'timbangan_out' && tx.timbangan_out_clock && tx.timbangan_out_clock !== '-' ? `<span class="badge bg-soft-warning text-warning fs-11 px-2 py-1" title="Tiba di Timbangan Out (Antre)"><i class="ri-time-line me-1"></i>Antre: ${tx.timbangan_out_clock}</span>` : (tx.check_out_time || '-'))}
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-end align-items-center">
                                    ${checkOutButton}
                                    ${followUpButton}
                                    <button type="button"
                                        class="btn btn-outline-warning btn-sm btn-edit-ajax"
                                        data-id="${tx.id}"
                                        title="Edit Transaksi">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-ajax" data-id="${tx.id}" title="Hapus Transaksi">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                ${followUpInfo}
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Update pagination info
                $('#paginationInfo').text(`Showing ${startIndex + 1} to ${endIndex} of ${totalItems} entries`);

                // Build pagination links
                const paginationLinks = $('#paginationLinks');
                paginationLinks.empty();

                // Previous button
                const prevDisabled = currentPage === 1 ? 'disabled' : '';
                paginationLinks.append(`
                    <li class="page-item ${prevDisabled}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}">Previous</a>
                    </li>
                `);

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    const activeClass = i === currentPage ? 'active' : '';
                    paginationLinks.append(`
                        <li class="page-item ${activeClass}">
                            <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `);
                }

                // Next button
                const nextDisabled = currentPage === totalPages ? 'disabled' : '';
                paginationLinks.append(`
                    <li class="page-item ${nextDisabled}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}">Next</a>
                    </li>
                `);
            }

            // Intercept Check-In Form Submit
            $('#checkInForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.vendors) {
                            updateVendorDropdowns(response.vendors);
                        }

                        // Clear form inputs
                        $('#no_pol').val(null).trigger('change');
                        $('#jenis').val('').trigger('change');
                        $('#target_location_id').val('').trigger('change');
                        $('#item_id').val(null).trigger('change');
                        $('#vendor').val(null).trigger('change');
                        $('#no_spb').val('');
                        $('#qty_spb').val('');
                        $('#nama_driver').val('');
                        $('#no_hp_driver').val('');
                        $('#checkin_pos1').val('');
                        $('#trnvisitorid').val('');

                        Swal.fire('Berhasil!', response.message, 'success');
                        fetchTransactions();
                        loadSupplierData();
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message ||
                            'Gagal melakukan check-in.', 'error');
                    }
                });
            });

            // Edit button handler (supports dynamically rendered elements)
            $(document).on('click', '.btn-edit-ajax', function() {
                const id = $(this).data('id');
                const btn = $(this);

                // Disable button to prevent double clicks
                btn.prop('disabled', true);

                $.ajax({
                    url: `{{ url('vehicle-monitoring/timbangan/show') }}/${id}`,
                    type: 'GET',
                    success: function(response) {
                        btn.prop('disabled', false);

                        if (response.success && response.data) {
                            const tx = response.data;

                            $('#edit_no_pol').val(tx.no_pol);
                            $('#edit_jenis').val(tx.jenis.toLowerCase());
                            filterTargetLocations('#edit_jenis', '#edit_target_location_id');
                            $('#edit_target_location_id').val(tx.target_loc);
                            filterItemsByLocation('#edit_target_location_id', '#edit_item_id');
                            $('#edit_item_id').val(tx.item_id).trigger('change');

                            if (tx.vendor) {
                                if ($("#edit_vendor").find("option[value='" + tx.vendor + "']")
                                    .length === 0) {
                                    var newOption = new Option(tx.vendor, tx.vendor, true,
                                        true);
                                    $("#edit_vendor").append(newOption).trigger('change');
                                } else {
                                    $("#edit_vendor").val(tx.vendor).trigger('change');
                                }
                            } else {
                                $("#edit_vendor").val(null).trigger('change');
                            }

                            $('#edit_no_spb').val(tx.no_spb === '-' ? '' : tx.no_spb);
                            $('#edit_qty_spb').val(tx.qty_spb === '-' ? '' : tx.qty_spb);
                            $('#edit_nama_driver').val(tx.nama_driver || '');
                            $('#edit_no_hp_driver').val(tx.no_hp_driver || '');
                            $('#edit_checkin_pos1').val(tx.checkin_pos1 || '');
                            $('#edit_trnvisitorid').val(tx.trnvisitorid || '');

                            // Set form action route dynamically
                            const actionUrl =
                                `{{ url('vehicle-monitoring/timbangan/update') }}/${id}`;
                            $('#editForm').attr('action', actionUrl);

                            // Show modal
                            var myModal = new bootstrap.Modal(document.getElementById(
                                'editModal'));
                            myModal.show();
                        } else {
                            Swal.fire('Error!', response.message ||
                                'Gagal memuat data transaksi.', 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        Swal.fire('Error!', xhr.responseJSON?.message ||
                            'Gagal mengambil data dari server.', 'error');
                    }
                });
            });

            // Intercept Edit Form Submit
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const actionUrl = form.attr('action');

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        // Close modal
                        const modalEl = document.getElementById('editModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        if (response.vendors) {
                            updateVendorDropdowns(response.vendors);
                        }

                        Swal.fire('Berhasil!', response.message, 'success');
                        fetchTransactions();
                        loadSupplierData();
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message ||
                            'Gagal memperbarui transaksi.', 'error');
                    }
                });
            });

            // AJAX Delete Confirmation (supports dynamically rendered elements)
            $(document).on('click', '.btn-delete-ajax', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Transaksi?',
                    text: "Apakah Anda yakin ingin menghapus data check-in kendaraan ini? Seluruh riwayat perpindahan juga akan terhapus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e0a800',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/timbangan/delete') }}/${id}`,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Dihapus!', response.message, 'success');
                                fetchTransactions();
                                loadSupplierData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal menghapus transaksi.', 'error');
                            }
                        });
                    }
                });
            });

            // Function to filter target locations based on selected jenis
            function filterTargetLocations(jenisSelectId, targetSelectId) {
                const jenis = $(jenisSelectId).val();
                const targetSelect = $(targetSelectId);
                const currentVal = targetSelect.val();
                let hasValidSelection = false;

                // Show/hide options based on logic
                targetSelect.find('option').each(function() {
                    const option = $(this);
                    const sloc = option.data('sloc');

                    if (!sloc) {
                        // Keep placeholder option
                        option.prop('disabled', false).show();
                        return;
                    }

                    let isAllowed = true;
                    if (jenis === 'bongkaran') {
                        if (sloc === 'A001') {
                            isAllowed = false;
                        }
                    } else if (jenis === 'slipsheet' || jenis === 'curah') {
                        if (sloc !== 'A001' && sloc !== 'SMU' && sloc !== 'A002' && sloc !== 'B006') {
                            isAllowed = false;
                        }
                    } else if (jenis === 'retur') {
                        if (sloc !== 'B006' && sloc !== 'C001') {
                            isAllowed = false;
                        }
                    }

                    if (isAllowed) {
                        option.prop('disabled', false).show();
                        if (currentVal && option.val() == currentVal) {
                            hasValidSelection = true;
                        }
                    } else {
                        option.prop('disabled', true).hide();
                    }
                });

                // If currently selected value is no longer allowed, reset selection
                if (currentVal && !hasValidSelection) {
                    targetSelect.val('').trigger('change');
                }
            }

            // Register change handlers for both check-in form and edit modal
            $('#jenis').on('change', function() {
                filterTargetLocations('#jenis', '#target_location_id');
            });

            $('#edit_jenis').on('change', function() {
                filterTargetLocations('#edit_jenis', '#edit_target_location_id');
            });

            // Register change handlers for filtering items by Sloc
            $('#target_location_id').on('change', function() {
                filterItemsByLocation('#target_location_id', '#item_id');
            });

            $('#edit_target_location_id').on('change', function() {
                filterItemsByLocation('#edit_target_location_id', '#edit_item_id');
            });

            const allItemsList = @json($items);

            function filterItemsByLocation(targetSelectId, itemSelectId) {
                const targetLocId = $(targetSelectId).val();
                const itemSelect = $(itemSelectId);
                const currentVal = itemSelect.val();

                // Clear all options
                itemSelect.empty();
                itemSelect.append('<option value="" selected disabled>Pilih Item</option>');

                // Filter matching options from cache
                let matchedItems = allItemsList;
                if (targetLocId) {
                    matchedItems = allItemsList.filter(function(item) {
                        return !item.location_id || String(item.location_id) === String(targetLocId);
                    });
                }

                matchedItems.forEach(function(item) {
                    const isSelected = (currentVal && String(item.id) === String(currentVal));
                    const option = new Option(item.name, item.id, isSelected, isSelected);
                    $(option).attr('data-location-id', item.location_id);
                    itemSelect.append(option);
                });

                itemSelect.trigger('change.select2');
            }

            // AJAX Check-Out Confirmation
            $(document).on('click', '.btn-checkout-ajax', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                Swal.fire({
                    title: 'Check-Out Kendaraan?',
                    text: `Apakah Anda yakin ingin melakukan Timbang Keluar (Check-Out) untuk kendaraan ${nopol}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Check-Out!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/timbangan/check-out') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message ||
                                    'Truk berhasil Check-Out.', 'success');
                                fetchTransactions();
                                loadSupplierData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal melakukan check-out.', 'error');
                            }
                        });
                    }
                });
            });

            // AJAX Follow Up Confirmation
            $(document).on('click', '.btn-followup-ajax', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const targetSloc = $(this).data('target-sloc') || '';
                const qcStatus = $(this).data('qc-status') || '';
                const status = $(this).data('status') || '';

                // Auto determine default area
                let defaultArea = 'QC';
                if (qcStatus === 'waiting_dokumen' || qcStatus === 'waiting_sampling' || qcStatus ===
                    'on_check') {
                    defaultArea = 'QC';
                } else if (targetSloc === 'A001' || status === 'wfg') {
                    defaultArea = 'WFG';
                } else if (targetSloc === 'C001' || status === 'wpm') {
                    defaultArea = 'WPM';
                } else if (targetSloc === 'B006' || status === 'wrm_bongkar' || status === 'wrm') {
                    defaultArea = 'WRM';
                } else if (targetSloc === 'SMU' || status === 'smu') {
                    defaultArea = 'SMU';
                } else {
                    defaultArea = targetSloc || 'QC';
                }

                Swal.fire({
                    title: 'Follow Up ke Area',
                    html: `
                        <div class="text-start">
                            <p class="mb-2">Kirim notifikasi peringatan ke operator area untuk truk <strong class="text-primary">${nopol}</strong>:</p>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Pilih Area Tujuan <span class="text-danger">*</span></label>
                                <select id="followup_target_area" class="form-select">
                                    <option value="QC" ${defaultArea === 'QC' ? 'selected' : ''}>QC (Sampling / Hasil Keputusan QC)</option>
                                    <option value="WFG" ${defaultArea === 'WFG' ? 'selected' : ''}>WFG (Bongkar / Muat Finished Goods)</option>
                                    <option value="SMU" ${defaultArea === 'SMU' ? 'selected' : ''}>SMU (Bongkaran / Curah / Slipsheet)</option>
                                    <option value="WPM" ${defaultArea === 'WPM' ? 'selected' : ''}>WPM (Unloading Packaging Material)</option>
                                    <option value="WRM" ${defaultArea === 'WRM' ? 'selected' : ''}>WRM (Unloading Raw Material)</option>
                                    <option value="ALL">Semua Area Terkait</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Pesan / Catatan Tambahan (Opsional)</label>
                                <textarea id="followup_notes" class="form-control" rows="2" placeholder="Contoh: Truk sudah di timbangan, mohon segera selesaikan konfirmasi di sistem."></textarea>
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#f06548',
                    confirmButtonText: '<i class="ri-send-plane-line me-1"></i> Kirim Notifikasi',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const targetArea = $('#followup_target_area').val();
                        const notes = $('#followup_notes').val();
                        if (!targetArea) {
                            Swal.showValidationMessage('Silakan pilih area tujuan!');
                            return false;
                        }
                        return {
                            target_area: targetArea,
                            notes: notes
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const postData = result.value;
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/timbangan/follow-up-area') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                target_area: postData.target_area,
                                notes: postData.notes
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Notifikasi Terkirim!',
                                    text: response.message,
                                    timer: 2500,
                                    showConfirmButton: false
                                });
                                fetchTransactions();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal mengirim follow up.', 'error');
                            }
                        });
                    }
                });
            });

            // Handle page clicks for pagination
            $(document).on('click', '#paginationLinks .page-link', function(e) {
                e.preventDefault();
                const targetPage = $(this).data('page');

                // Do nothing if disabled or parent is disabled
                if ($(this).closest('.page-item').hasClass('disabled')) {
                    return;
                }

                if (targetPage) {
                    currentPage = parseInt(targetPage);
                    renderTransactions();
                }
            });

            // Audio Notification for incoming follow up from area
            function playTimbanganFollowUpSound() {
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(440, audioCtx.currentTime);
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.15);
                    osc.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.3);
                    gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.6);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.6);
                } catch (e) {}
            }

            const handledTimbanganFollowUps = new Set();

            function triggerTimbanganFollowUpAlert(payload) {
                const alertKey = (payload.no_pol || '') + '_' + (payload.timestamp || payload.time || '');
                if (handledTimbanganFollowUps.has(alertKey)) return;
                handledTimbanganFollowUps.add(alertKey);

                playTimbanganFollowUpSound();

                const sourceArea = payload.source_area || 'AREA';
                const noPol = payload.no_pol || 'N/A';
                const vendor = payload.vendor && payload.vendor !== '-' ? payload.vendor : '';
                const jenis = payload.jenis || 'bongkaran';
                const itemName = payload.item_name && payload.item_name !== '-' ? payload.item_name : '';
                const itemId = payload.item_id || '';
                const noSpb = payload.no_spb || '';
                const qtySpb = payload.qty_spb != null ? payload.qty_spb : '';
                const notes = payload.notes || '';
                const time = payload.time || '';

                Swal.fire({
                    title: `<span class="text-danger fw-bold"><i class="ri-alarm-warning-line me-1"></i> FOLLOW UP DARI ${sourceArea}!</span>`,
                    html: `
                        <div class="text-start">
                            <div class="alert alert-danger border-0 mb-3 py-2 px-3">
                                <strong>Peringatan dari ${sourceArea}:</strong> Kendaraan sudah berada di area <strong>${sourceArea}</strong> namun <u>belum terdaftar di Timbangan</u>!
                            </div>
                            <div class="card bg-light border-0 mb-3 p-3">
                                <p class="mb-1"><strong>No. Polisi:</strong> <span class="badge bg-primary fs-13">${noPol}</span></p>
                                <p class="mb-1"><strong>Jenis:</strong> <span class="badge bg-soft-info text-info text-capitalize">${jenis}</span></p>
                                ${itemName ? `<p class="mb-1"><strong>Item:</strong> <span class="fw-semibold text-dark">${itemName}</span></p>` : ''}
                                ${noSpb ? `<p class="mb-1"><strong>No. SPB:</strong> <span class="fw-semibold text-dark">${noSpb}</span></p>` : ''}
                                ${qtySpb ? `<p class="mb-1"><strong>Qty SPB:</strong> <span class="fw-semibold text-dark">${parseFloat(qtySpb).toLocaleString('id-ID')} Kg</span></p>` : ''}
                                ${vendor ? `<p class="mb-1"><strong>Vendor:</strong> ${vendor}</p>` : ''}
                                ${notes ? `<p class="mb-1 text-danger"><strong>Catatan:</strong> "${notes}"</p>` : ''}
                                <p class="mb-0 text-muted small"><i class="ri-time-line me-1"></i>Waktu Lapor: ${time}</p>
                            </div>
                            <p class="text-muted small mb-0">Klik tombol di bawah untuk langsung mengisi data form Check-In Timbangan secara otomatis.</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0ab39c',
                    confirmButtonText: '<i class="ri-login-box-line me-1"></i> Proses Check-In Sekarang',
                    cancelButtonColor: '#6c757d',
                    cancelButtonText: 'Tutup',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // 1. Set No Polisi
                        if ($('#no_pol').find("option[value='" + noPol + "']").length === 0) {
                            var newOption = new Option(noPol, noPol, true, true);
                            $('#no_pol').append(newOption).trigger('change');
                        } else {
                            $('#no_pol').val(noPol).trigger('change');
                        }

                        // 2. Set Jenis
                        $('#jenis').val(jenis).trigger('change');

                        // 3. Set Target Location based on source area
                        let targetSlocCode = '';
                        if (sourceArea === 'WFG') targetSlocCode = 'A001';
                        else if (sourceArea === 'SMU') targetSlocCode = 'SMU';
                        else if (sourceArea === 'WPM') targetSlocCode = 'C001';
                        else if (sourceArea === 'WRM') targetSlocCode = 'B006';

                        if (targetSlocCode) {
                            $('#target_location_id option').each(function() {
                                if ($(this).data('sloc') === targetSlocCode) {
                                    $('#target_location_id').val($(this).val()).trigger('change');
                                }
                            });
                        }

                        // 4. Set Item if available
                        if (itemId) {
                            $('#item_id').val(itemId).trigger('change');
                        }

                        // 5. Set No. SPB & Qty SPB if available
                        if (noSpb) {
                            $('#no_spb').val(noSpb);
                        }
                        if (qtySpb) {
                            $('#qty_spb').val(qtySpb);
                        }

                        // 6. Set Vendor
                        if (vendor) {
                            if ($('#vendor').find("option[value='" + vendor + "']").length === 0) {
                                var newVendorOpt = new Option(vendor, vendor, true, true);
                                $('#vendor').append(newVendorOpt).trigger('change');
                            } else {
                                $('#vendor').val(vendor).trigger('change');
                            }
                        }

                        // Scroll smoothly to check-in form
                        $('html, body').animate({
                            scrollTop: $('#checkInForm').offset().top - 70
                        }, 500);

                        if (window.toastr) {
                            toastr.success(`Form Check-In berhasil diisi untuk Truk ${noPol}. Silakan lengkapi lalu Simpan.`, 'Follow Up Diproses');
                        }
                    }
                });
            }

            // Real-time Event Listener with Laravel Echo / Reverb
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates on Echo channel in Timbangan...');
                    window.Echo.channel('vehicle-tracking')
                        .subscribed(() => {
                            console.log('✅ Subscribed successfully to vehicle-tracking channel in Timbangan');
                        })
                        .error((err) => {
                            console.error('❌ Echo connection error on vehicle-tracking channel in Timbangan:', err);
                        })
                        .listen('.vehicle.updated', function(data) {
                            console.log('Echo event received in Timbangan:', data);
                            if (data.type === 'follow_up_timbangan' || (data.target_area === 'TIMBANGAN' && data.type === 'follow_up_timbangan') || data.action === 'unregistered_vehicle') {
                                triggerTimbanganFollowUpAlert(data);
                            }
                            fetchTransactions();
                            loadSupplierData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();
        });
    </script>
@endsection
