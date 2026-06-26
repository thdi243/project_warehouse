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
                                    <input type="text" class="form-control" id="no_pol" name="no_pol" required
                                        placeholder="Contoh: B1234CD" style="text-transform: uppercase;">
                                    <small class="text-muted">Ketik untuk mencari nopol yang sudah terdaftar.</small>
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
                                                <option value="{{ $loc->id }}">{{ $loc->s_loc }} -
                                                    {{ $loc->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="vendor" class="form-label">Nama Vendor</label>
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
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
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
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary"><i
                                            class="ri-save-line me-1 align-middle"></i>Submit</button>
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
                                    class="ri-table-line me-2 align-middle text-success"></i>Data Check-In Harian
                            </h4>
                            <div class="flex-shrink-0">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="filter_date" class="form-label mb-0 text-muted small text-nowrap">Pilih
                                        Tanggal:</label>
                                    <input type="date" class="form-control form-control-sm" id="filter_date"
                                        name="date" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. Transaksi</th>
                                            <th>No. Polisi</th>
                                            <th>Jenis / SKU</th>
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
                                        <option value="{{ $loc->id }}">{{ $loc->s_loc }} - {{ $loc->name }}
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
                                        <option value="{{ $item->id }}">{{ $item->sku }} - {{ $item->name }}
                                        </option>
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
                theme: 'bootstrap-5',
                dropdownParent: $('#editModal'),
                allowClear: true,
                width: '100%'
            });

            $('#edit_vendor').select2({
                theme: 'bootstrap-5',
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

            // Autocomplete plate numbers
            $("#no_pol").autocomplete({
                source: "{{ route('vehicle.monitoring.timbangan.autocomplete_vehicle') }}",
                minLength: 2,
                select: function(event, ui) {
                    if (ui.item.vendor) {
                        if ($("#vendor").find("option[value='" + ui.item.vendor + "']").length === 0) {
                            var newOption = new Option(ui.item.vendor, ui.item.vendor, true, true);
                            $("#vendor").append(newOption).trigger('change');
                        } else {
                            $("#vendor").val(ui.item.vendor).trigger('change');
                        }
                    } else {
                        $("#vendor").val(null).trigger('change');
                    }
                }
            });

            fetchTransactions();

            // Fetch transaction data via AJAX
            function fetchTransactions() {
                const selectedDate = $('#filter_date').val();
                $.ajax({
                    url: "{{ route('vehicle.monitoring.timbangan.data') }}",
                    type: 'GET',
                    data: {
                        date: selectedDate
                    },
                    success: function(data) {
                        renderTransactions(data, selectedDate);
                    },
                    error: function(xhr) {
                        console.error('Failed to load transaction data', xhr);
                    }
                });
            }

            // Render transaction table rows
            function renderTransactions(transactions, selectedDate) {
                const tbody = $('#timbanganTableBody');
                tbody.empty();

                if (transactions.length === 0) {
                    let dateObj = moment(selectedDate, "YYYY-MM-DD");
                    let formattedDate = dateObj.isValid() ? dateObj.format("DD-MM-YYYY") : selectedDate;

                    tbody.html(`
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Belum ada transaksi pada tanggal ${formattedDate}.</td>
                        </tr>
                    `);
                    return;
                }

                transactions.forEach(function(tx) {
                    const statusBadge = tx.status === 'completed' ?
                        '<span class="badge bg-success">Out</span>' :
                        `<span class="badge bg-warning">${tx.status.toUpperCase()}</span>`;

                    const row = `
                        <tr>
                            <td><small class="fw-bold">${tx.no_transaction}</small></td>
                            <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                            <td>
                                <strong>${tx.jenis}</strong><br>
                                <small class="text-muted">${tx.sku}</small>
                            </td>
                            <td>${tx.vendor || '-'}</td>
                            <td>
                                <strong>${tx.no_spb || '-'}</strong><br>
                                <small class="text-muted">${tx.qty_spb || '-'}</small>
                            </td>
                            <td><span class="badge bg-soft-info text-info">${tx.target_sloc}</span></td>
                            <td>${statusBadge}</td>
                            <td>${tx.check_in_time}</td>
                            <td>${tx.check_out_time}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button"
                                        class="btn btn-soft-warning btn-sm btn-edit-ajax"
                                        data-id="${tx.id}"
                                        data-nopol="${tx.no_pol}"
                                        data-jenis="${tx.jenis.toLowerCase()}"
                                        data-target-loc="${tx.target_loc}"
                                        data-item-id="${tx.item_id}"
                                        data-vendor="${tx.vendor || ''}"
                                        data-no-spb="${tx.no_spb === '-' ? '' : tx.no_spb}"
                                        data-qty-spb="${tx.qty_spb === '-' ? '' : tx.qty_spb}">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete-ajax" data-id="${tx.id}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }

            // On Date Picker change
            $('#filter_date').on('change', function() {
                fetchTransactions();
            });

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
                        $('#no_pol').val('');
                        $('#jenis').val('').trigger('change');
                        $('#target_location_id').val('').trigger('change');
                        $('#item_id').val(null).trigger('change');
                        $('#vendor').val(null).trigger('change');
                        $('#no_spb').val('');
                        $('#qty_spb').val('');

                        Swal.fire('Berhasil!', response.message, 'success');
                        fetchTransactions();
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
                const nopol = $(this).data('nopol');
                const jenis = $(this).data('jenis');
                const targetLoc = $(this).data('target-loc');
                const itemId = $(this).data('item-id');
                const vendor = $(this).data('vendor');
                const noSpb = $(this).data('no-spb');
                const qtySpb = $(this).data('qty-spb');

                $('#edit_no_pol').val(nopol);
                $('#edit_jenis').val(jenis);
                $('#edit_target_location_id').val(targetLoc);
                $('#edit_item_id').val(itemId).trigger('change');

                if (vendor) {
                    if ($("#edit_vendor").find("option[value='" + vendor + "']").length === 0) {
                        var newOption = new Option(vendor, vendor, true, true);
                        $("#edit_vendor").append(newOption).trigger('change');
                    } else {
                        $("#edit_vendor").val(vendor).trigger('change');
                    }
                } else {
                    $("#edit_vendor").val(null).trigger('change');
                }

                $('#edit_no_spb').val(noSpb);
                $('#edit_qty_spb').val(qtySpb);

                // Set form action route dynamically with date query parameter if present
                const selectedDate = $('#filter_date').val();
                const actionUrl = `{{ url('vehicle-monitoring/timbangan/update') }}/${id}` + (
                    selectedDate ? `?date=${selectedDate}` : '');
                $('#editForm').attr('action', actionUrl);

                // Show modal
                var myModal = new bootstrap.Modal(document.getElementById('editModal'));
                myModal.show();
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
                const selectedDate = $('#filter_date').val();

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
                                _token: "{{ csrf_token() }}",
                                date: selectedDate
                            },
                            success: function(response) {
                                Swal.fire('Dihapus!', response.message, 'success');
                                fetchTransactions();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal menghapus transaksi.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
