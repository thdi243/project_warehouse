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
                        <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                            <h4 class="card-title mb-0 flex-grow-1"><i
                                    class="ri-table-line me-2 align-middle text-success"></i>Data Kendaraan Aktif
                            </h4>
                            <div class="flex-shrink-0">
                                <div style="width: 250px;">
                                    <input type="text" class="form-control" id="search_table"
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
                                            <th>Jam Masuk</th>
                                            <th>Jam Keluar</th>
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
        <div class="modal-dialog modal-dialog-centered">
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
                        <div class="mb-3">
                            <label for="edit_no_pol" class="form-label">No. Polisi <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_no_pol" name="no_pol" required
                                style="text-transform: uppercase;">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_jenis" class="form-label">Jenis <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_jenis" name="jenis" required>
                                    <option value="bongkaran">Bongkaran</option>
                                    <option value="slipsheet">Slipsheet</option>
                                    <option value="curah">Curah</option>
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
                            <div class="col-md-6 mb-3">
                                <label for="edit_item_id" class="form-label">Item <span
                                        class="text-danger">*</span></label>
                                <select class="form-select select2-edit" id="edit_item_id" name="item_id" required>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}" data-location-id="{{ $item->location_id }}">
                                            {{ $item->name }}</option>
                                    @endforeach
                                </select>
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
                                <label for="edit_no_spb" class="form-label">No. SPB</label>
                                <input type="text" class="form-control" id="edit_no_spb" name="no_spb"
                                    placeholder="Nomor Surat Perintah Bongkar">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_qty_spb" class="form-label">Qty SPB</label>
                                <input type="number" class="form-control" id="edit_qty_spb" name="qty_spb"
                                    step="any" placeholder="Kuantitas SPB">
                            </div>
                        </div>
                        <input type="hidden" id="edit_nama_driver" name="nama_driver">
                        <input type="hidden" id="edit_no_hp_driver" name="no_hp_driver">
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

                    // Auto populate driver name & phone
                    $('#nama_driver').val(matchedSupplier.nama_driver || '');
                    $('#no_hp_driver').val(matchedSupplier.no_hp_driver || '');
                } else {
                    $('#nama_driver').val('');
                    $('#no_hp_driver').val('');
                }
            });

            fetchTransactions();

            // Handle table search
            $('#search_table').on('keyup', function() {
                searchQuery = $(this).val().toLowerCase();
                currentPage = 1; // Reset to page 1 on search
                renderTransactions();
            });

            let allTransactions = [];
            let currentPage = 1;
            const itemsPerPage = 10;
            let searchQuery = '';

            // Fetch transaction data via AJAX
            function fetchTransactions() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.timbangan.data') }}",
                    type: 'GET',
                    success: function(data) {
                        allTransactions = data;
                        renderTransactions();
                    },
                    error: function(xhr) {
                        console.error('Failed to load transaction data', xhr);
                    }
                });
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

                // Filter transactions based on search query
                let filteredTransactions = allTransactions;
                if (searchQuery) {
                    filteredTransactions = allTransactions.filter(function(tx) {
                        const nopol = (tx.no_pol || '').toLowerCase();
                        const vendor = (tx.vendor || '').toLowerCase();
                        return nopol.includes(searchQuery) || vendor.includes(searchQuery);
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
                    const statusBadge = tx.status === 'completed' ?
                        '<span class="badge bg-success">Out</span>' :
                        `<span class="badge bg-warning">${tx.status.toUpperCase()}</span>`;

                    const checkOutButton = tx.status.toLowerCase() === 'timbangan_out' ?
                        `<button type="button" class="btn btn-outline-success btn-sm btn-checkout-ajax" data-id="${tx.id}" data-nopol="${tx.no_pol}">
                            Check-Out
                        </button>` : '';

                    const row = `
                        <tr>
                            <td class="text-center"><small class="fw-bold">${index + (currentPage - 1) * itemsPerPage + 1}</small></td>
                            <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                            <td>${tx.jenis}</td>
                            <td>
                                <strong>${tx.vendor || '-'}</strong><br>
                                <small class="text-muted">Driver: ${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                            </td>
                            <td>
                                <strong>${tx.no_spb || '-'}</strong><br>
                                <small class="text-muted">${tx.qty_spb || '-'}</small>
                            </td>
                            <td><span class="badge bg-soft-info text-info">${tx.target_sloc}</span></td>
                            <td>${statusBadge}</td>
                            <td>${tx.check_in_time}</td>
                            <td>${tx.check_out_time}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-end">
                                    ${checkOutButton}
                                    <button type="button"
                                        class="btn btn-outline-warning btn-sm btn-edit-ajax"
                                        data-id="${tx.id}">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete-ajax" data-id="${tx.id}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
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
                        if (sloc !== 'A001' && sloc !== 'SMU' && sloc !== 'A002') {
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
        });
    </script>
@endsection
