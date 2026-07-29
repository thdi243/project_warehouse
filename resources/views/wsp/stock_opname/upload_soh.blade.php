@extends('layouts.app')

@section('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.2);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
        }

        .detail-row {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }

        .detail-value {
            font-weight: 700;
            font-size: 1.1rem;
        }

        @media (max-width: 767.98px) {
            #soh-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                font-size: 11px;
                white-space: nowrap;
            }

            table th,
            table td {
                padding: 0.5rem;
            }
        }

        .select2-container--default .select2-selection--single {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: .375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        /* Custom dropdown styles */
        .custom-filter-dropdown .dropdown-toggle {
            border-radius: 0.25rem;
            padding: 0.47rem 0.75rem;
            font-size: 0.875rem;
            box-shadow: 0 0 0 0 !important;
            background-color: var(--vz-input-bg) !important;
            border: 1px solid var(--vz-border-color) !important;
            color: var(--vz-body-color) !important;
            min-height: calc(1.5em + .94rem + 2px);
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

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0"><i class="mdi mdi-upload me-2"></i>Upload Stock On Hand WSP</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WSP</a></li>
                                <li class="breadcrumb-item active">Stock Opname</li>
                                <li class="breadcrumb-item active">Upload Stock On Hand</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar & Search -->
            <div class="mb-3" data-aos="fade-right" data-aos-delay="100">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                                <form id="searchSOHForm" class="position-relative w-100" role="search">
                                    <input type="search" class="form-control ps-5" id="searchSOHInput"
                                        placeholder="Cari MID atau nama barang..." aria-label="Search">
                                    <i
                                        class="mdi mdi-magnify position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                </form>
                            </div>

                            @if ($barangCount > 0)
                                <div class="col-lg-6 col-md-12 d-flex gap-2 justify-content-lg-end"
                                    id="soh-actions-container">
                                    <div id="soh-actions-wrapper" class="d-flex gap-2 w-100 justify-content-lg-end">
                                        <button class="btn btn-success px-4 w-100" data-bs-toggle="modal"
                                            data-bs-target="#uploadModal">
                                            <i class="mdi mdi-upload me-1"></i> Upload Excel
                                        </button>
                                        <button class="btn btn-primary px-4 w-100" onclick="openAddSOH()">
                                            <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah Manual
                                        </button>
                                        <button class="btn btn-danger px-4 w-100" id="btnDeleteAll">
                                            <i class="mdi mdi-delete me-1"></i> Kosongkan Hari Ini
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="col-lg-6 col-md-12">
                                    <div
                                        class="alert alert-warning py-2 px-3 mb-0 w-100 text-center small border-0 shadow-none">
                                        <i class="mdi mdi-alert-circle me-1"></i> Data Master Barang kosong. Silakan isi <a
                                            href="{{ route('wsp.master.barang') }}"
                                            class="alert-link text-decoration-underline fw-bold">Master Barang</a> terlebih
                                        dahulu.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card shadow-sm border-0" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body">
                    <!-- Tab Pemilihan Jenis SO -->
                    <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" id="jenisSoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-value="cycle_count" type="button"
                                role="tab">
                                <i class="mdi mdi-sync me-1"></i> Cycle Count (Daily)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-value="monthly" type="button"
                                role="tab">
                                <i class="mdi mdi-calendar-month me-1"></i> Monthly SO
                            </button>
                        </li>
                    </ul>
                    <div class="table-responsive table-card p-2" id="soh-table-container">
                        <table class="table table-striped align-middle mb-0" id="tableSOHList">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th class="text-center">MID</th>
                                    <th class="text-start">Nama Barang</th>
                                    <th class="text-start">Location</th>
                                    <th class="text-end">Total SOH</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                        </div>
                                        Memuat data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="tableInfo" class="text-muted small"></div>
                        <div id="paginationContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload Excel -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="uploadModalLabel">
                        <i class="mdi mdi-file-excel-outline me-1"></i> Upload File SOH WSP
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="uploadSOHForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 shadow-none mb-4 small">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Gunakan template untuk proses unggah data SOH. Pastikan format kolom sesuai template.
                        </div>
                        <div class="mb-3">
                            <label for="excel_file" class="form-label fw-semibold">Pilih File Excel (.xlsx, .xls)</label>
                            <input type="file" class="form-control" id="excel_file" name="file"
                                accept=".xlsx, .xls" required>
                        </div>
                        <div class="mb-3">
                            <label for="upload_jenis_so" class="form-label fw-semibold">Jenis SO</label>
                            <select class="form-select" id="upload_jenis_so" name="jenis_so" required>
                                <option value="cycle_count">Cycle Count (Daily)</option>
                                <option value="monthly">Monthly SO</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light d-flex gap-2">
                        <a href="{{ route('wsp.stock_opname.soh.template') }}" class="btn btn-primary flex-grow-1">
                            <i class="mdi mdi-download me-1"></i>
                            Template
                        </a>
                        <button type="submit" class="btn btn-success flex-grow-1" id="btnSubmitUpload">
                            <i class="mdi mdi-upload me-1"></i> Unggah Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Form SOH Manual (Add/Edit) -->
    <div class="modal fade" id="sohModal" tabindex="-1" aria-labelledby="sohModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="sohModalLabel">Tambah Data SOH</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="formSOH">
                    @csrf
                    <input type="hidden" id="soh_id" name="soh_id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="soh_jenis_so" class="form-label fw-semibold">Jenis SO</label>
                            <select class="form-select" id="soh_jenis_so" name="jenis_so" required>
                                <option value="cycle_count">Cycle Count (Daily)</option>
                                <option value="monthly">Monthly SO</option>
                            </select>
                        </div>

                        <!-- ADD SECTION -->
                        <div id="addSection">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Area</label>
                                <div class="dropdown custom-filter-dropdown" id="dropdown-area">
                                    <button
                                        class="btn btn-light dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center"
                                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false">
                                        <span class="dropdown-placeholder text-muted">Pilih Area...</span>
                                        <span class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                    </button>
                                    <div class="dropdown-menu p-3 border"
                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                        <div class="mb-2">
                                            <input type="text" class="form-control form-control-sm search-options"
                                                placeholder="Cari Area...">
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
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Nama Rak</label>
                                <div class="dropdown custom-filter-dropdown" id="dropdown-rak">
                                    <button
                                        class="btn btn-light dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center"
                                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false">
                                        <span class="dropdown-placeholder text-muted">Pilih Nama Rak...</span>
                                        <span class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                    </button>
                                    <div class="dropdown-menu p-3 border"
                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                        <div class="mb-2">
                                            <input type="text" class="form-control form-control-sm search-options"
                                                placeholder="Cari Nama Rak...">
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
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Barang (MID)</label>
                                <div class="dropdown custom-filter-dropdown" id="dropdown-barang">
                                    <button
                                        class="btn btn-light dropdown-toggle text-start w-100 d-flex justify-content-between align-items-center"
                                        type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        aria-expanded="false">
                                        <span class="dropdown-placeholder text-muted">Pilih Barang...</span>
                                        <span class="badge bg-success rounded-pill ms-2 selected-count d-none">0</span>
                                    </button>
                                    <div class="dropdown-menu p-3 border"
                                        style="min-width: 320px; max-width: 400px; max-height: 400px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                        <div class="mb-2">
                                            <input type="text" class="form-control form-control-sm search-options"
                                                placeholder="Cari Barang...">
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
                        </div>

                        <!-- EDIT SECTION -->
                        <div id="editSection" style="display: none;">
                            <input type="hidden" id="edit_loc_id" name="loc_id">
                            <div class="mb-3">
                                <label for="edit_mid_barang" class="form-label fw-semibold">MID Barang</label>
                                <input type="text" class="form-control" id="edit_mid_barang" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Lokasi</label>
                                <div id="edit_location_info" class="form-control bg-light text-muted"
                                    style="min-height:38px;">
                                    <span class="badge badge-soft-warning">Not Yet</span>
                                </div>
                            </div>
                            <div id="qtyInputWrapper" class="row g-3">
                                <div class="col-md-4">
                                    <label for="unrest" class="form-label fw-semibold">UNREST</label>
                                    <input type="number" class="form-control" id="unrest" name="unrest"
                                        value="0" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="qi" class="form-label fw-semibold">QI</label>
                                    <input type="number" class="form-control" id="qi" name="qi"
                                        value="0" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="block" class="form-label fw-semibold">BLOCKED</label>
                                    <input type="number" class="form-control" id="block" name="block"
                                        value="0" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveSOH">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalSOHDetail" tabindex="-1" aria-labelledby="modalSOHDetailLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-md">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title text-white fw-bold" id="modalTitle">
                            Detail Stock On Hand
                        </h5>
                        <span class="text-white opacity-75 small" id="modalMID">
                            MID: N/A
                        </span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="modalContent">
                    <!-- Dynamic Content -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let mode = 'add';

        $(document).ready(function() {
            // Initialize custom dropdowns
            initDynamicDropdown('dropdown-area', 'Pilih Area...', loadNamaRakOptionsForAdd);
            initDynamicDropdown('dropdown-rak', 'Pilih Nama Rak...', loadBarangOptionsForAdd);
            initDynamicDropdown('dropdown-barang', 'Pilih Barang...', null);

            // Load SOH list initially
            loadSOHList();

            // Search event
            $('#searchSOHInput').on('keyup', function() {
                loadSOHList();
            });

            // Tab Event for SOH type
            $('#jenisSoTabs button').on('shown.bs.tab', function(e) {
                loadSOHList();
            });

            // Form Submit for Add/Edit
            $('#formSOH').on('submit', function(e) {
                e.preventDefault();
                const sohId = $('#soh_id').val();

                let postData;
                let contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
                let processData = true;

                if (mode === 'add') {
                    const barangId = $('#dropdown-barang').data('getValues')();
                    const area = $('#dropdown-area').data('getValues')();
                    const namaRak = $('#dropdown-rak').data('getValues')();

                    if (!barangId || barangId.length === 0 || !area || area.length === 0 || !namaRak ||
                        namaRak.length === 0) {
                        Swal.fire('Peringatan', 'Barang, Area, dan Nama Rak harus dipilih!', 'warning');
                        return;
                    }

                    postData = JSON.stringify({
                        _token: "{{ csrf_token() }}",
                        jenis_so: $('#soh_jenis_so').val(),
                        barang_id: barangId,
                        area: area,
                        nama_rak: namaRak
                    });
                    contentType = 'application/json';
                    processData = false;
                } else {
                    postData = {
                        _token: "{{ csrf_token() }}",
                        jenis_so: $('#soh_jenis_so').val(),
                        unrest: $('#unrest').val(),
                        qi: $('#qi').val(),
                        block: $('#block').val()
                    };
                }

                const url = mode === 'add' ?
                    "{{ route('wsp.stock_opname.soh.store') }}" :
                    `{{ route('wsp.stock_opname.soh.update', '') }}/${sohId}`;

                $.ajax({
                    url: url,
                    type: "POST",
                    data: postData,
                    contentType: contentType,
                    processData: processData,
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            $('#sohModal').modal('hide');
                            loadSOHList();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const err = xhr.responseJSON;
                        Swal.fire('Gagal', err.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            });

            // Automatically pre-select jenis_so in upload modal based on active tab
            $('#uploadModal').on('show.bs.modal', function() {
                const activeJenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';
                $('#upload_jenis_so').val(activeJenisSo).prop('disabled', false);
            });

            // Form Submit for Upload
            $('#uploadSOHForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                if ($('#upload_jenis_so').is(':disabled')) {
                    formData.append('jenis_so', $('#upload_jenis_so').val());
                }
                const btn = $('#btnSubmitUpload');
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Mengunggah...');

                $.ajax({
                    url: "{{ route('wsp.stock_opname.soh.import') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        btn.prop('disabled', false).html(
                            '<i class="mdi mdi-upload me-1"></i> Unggah Data');
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('#uploadModal').modal('hide');
                            $('#uploadSOHForm')[0].reset();
                            loadSOHList();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(
                            '<i class="mdi mdi-upload me-1"></i> Unggah Data');
                        const err = xhr.responseJSON;
                        Swal.fire('Gagal', err.message || 'Format file Excel tidak valid',
                            'error');
                    }
                });
            });

            // Delete All Event
            $('#btnDeleteAll').on('click', function() {
                const jenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';
                Swal.fire({
                    title: 'Kosongkan Data SOH?',
                    text: 'Seluruh data SOH WSP ' + (jenisSo === 'monthly' ? 'bulan ini' : 'hari ini') + ' akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performReset(false);
                    }
                });

                function performReset(confirmTemp) {
                    $.ajax({
                        url: "{{ route('wsp.stock_opname.soh.reset_all') }}",
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}",
                            jenis_so: jenisSo,
                            confirm_temp: confirmTemp ? 1 : 0
                        },
                        success: function(res) {
                            if (res.status === 'confirm_temp') {
                                Swal.fire({
                                    title: 'Hapus Semua Data?',
                                    text: res.message,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya, Hapus Semua',
                                    cancelButtonText: 'Batal',
                                    confirmButtonColor: '#d33'
                                }).then((finalResult) => {
                                    if (finalResult.isConfirmed) {
                                        performReset(true);
                                    }
                                });
                            } else if (res.status) {
                                Swal.fire('Berhasil', res.message, 'success');
                                loadSOHList();
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                Swal.fire('Gagal', xhr.responseJSON.message, 'error');
                            } else {
                                Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data SOH.', 'error');
                            }
                        }
                    });
                }
            });
        });

        function loadAreaOptionsForAdd() {
            $('#dropdown-area').data('reset')();
            $('#dropdown-rak').data('reset')();
            $('#dropdown-barang').data('reset')();

            $.ajax({
                url: "{{ route('wsp.stock_opname.soh.getAreaList') }}",
                type: "GET",
                success: function(res) {
                    if (res.status === 'success' && res.data) {
                        updateDropdownOptions('dropdown-area', res.data, 'Pilih Area...', 'area');
                    }
                }
            });
        }

        function loadNamaRakOptionsForAdd() {
            const area = $('#dropdown-area').data('getValues')();
            $('#dropdown-rak').data('reset')();
            $('#dropdown-barang').data('reset')();

            if (!area || area.length === 0) return;

            $.ajax({
                url: "{{ route('wsp.stock_opname.soh.getNamaRakList') }}",
                type: "GET",
                data: {
                    area: area
                },
                success: function(res) {
                    if (res.status === 'success' && res.data) {
                        updateDropdownOptions('dropdown-rak', res.data, 'Pilih Nama Rak...', 'nama_rak');
                    }
                }
            });
        }

        function loadBarangOptionsForAdd() {
            const area = $('#dropdown-area').data('getValues')();
            const namaRak = $('#dropdown-rak').data('getValues')();
            $('#dropdown-barang').data('reset')();

            if (!area || area.length === 0 || !namaRak || namaRak.length === 0) return;

            $.ajax({
                url: "{{ route('wsp.stock_opname.soh.getBarangListByLocation') }}",
                type: "GET",
                data: {
                    area: area,
                    nama_rak: namaRak
                },
                success: function(res) {
                    if (res.status === 'success' && res.data) {
                        updateDropdownOptions('dropdown-barang', res.data, 'Pilih Barang...', 'barang');
                    }
                }
            });
        }

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

                if (onChange) {
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

        function updateDropdownOptions(id, data, placeholder, type) {
            const $dropdown = $('#' + id);
            const currentValues = $dropdown.data('getValues') ? $dropdown.data('getValues')() : [];
            let html = '';

            data.forEach(item => {
                let val, text;
                if (type === 'barang') {
                    val = item.id;
                    text = `${item.mid_barang} - ${item.nama_barang} (${item.uom})`;
                } else if (type === 'area' || type === 'nama_rak') {
                    val = item;
                    text = item;
                } else {
                    val = item.rak_id;
                    text = item.text;
                }

                let safeVal = val ?? '';
                let safeText = text ?? '';
                let safeId = safeVal.toString().replace(/[^a-zA-Z0-9_\-]/g, '_');

                let isSelected = currentValues.includes(safeVal.toString());
                let checkedAttr = isSelected ? 'checked' : '';
                html += `
                    <div class="form-check mb-2 option-item" data-value="${safeVal}" data-text="${safeText}">
                        <input class="form-check-input option-checkbox" type="checkbox" value="${safeVal}" id="chk-${id}-${safeId}" ${checkedAttr}>
                        <label class="form-check-label text-truncate w-100" for="chk-${id}-${safeId}">
                            ${safeText}
                        </label>
                    </div>
                `;
            });

            $dropdown.find('.options-list').html(html);

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

        function loadSOHList(page = 1) {
            const tableBody = $('#tableSOHList tbody');
            const search = $('#searchSOHInput').val();
            const jenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';

            $.ajax({
                url: "{{ route('wsp.stock_opname.soh.list') }}",
                type: "GET",
                data: {
                    page: page,
                    search: search,
                    jenis_so: jenisSo
                },
                success: function(res) {
                    tableBody.empty();
                    if (res.data && res.data.length > 0) {
                        res.data.forEach((item, index) => {
                            const globalIndex = (res.current_page - 1) * res.per_page + index + 1;
                            const barangName = item.barang ? item.barang.nama_barang : 'N/A';
                            const barangMid = item.barang ? item.barang.mid_barang : 'N/A';
                            const uom = item.barang ? item.barang.uom : '';
                            const qtySoh = parseFloat(item.qty_soh).toLocaleString('id-ID');

                            let locationText = '';
                            const rak = item.location && item.location.rak ? item.location.rak : null;
                            if (!rak) {
                                locationText =
                                    '<span class="badge badge-soft-warning">Not Yet</span>';
                            } else {
                                locationText =
                                    `${rak.plant || ''} - ${rak.s_loc || ''} - ${rak.area_rak} - ${rak.nama_rak} - (${rak.kolom_rak || '-'}.${rak.level_rak || '-'}.${rak.box_rak || '-'})`;
                            }

                            const editBtn = `
                                <button class="btn btn-sm btn-outline-primary me-1"
                                    onclick="editSOH(${item.id})"
                                    title="Edit">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                            `;
                            const deleteBtn = `
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteSOH(${item.id})"
                                    title="Hapus">
                                    <i class="mdi mdi-trash-can"></i>
                                </button>
                            `;

                            tableBody.append(`
                                <tr>
                                    <td class="text-center">${globalIndex}</td>
                                    <td class="text-center">${barangMid}</td>
                                    <td>${barangName}</td>
                                    <td class="text-start">${locationText}</td>
                                    <td class="text-end fw-bold text-primary">${qtySoh} ${uom}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-info me-1"
                                            onclick='detailSOH(${JSON.stringify(item)})'
                                            title="Detail">
                                            <i class="mdi mdi-eye"></i>
                                        </button>

                                        ${editBtn} ${deleteBtn}
                                    </td>
                                </tr>
                            `);
                        });
                        $('#tableInfo').text(`Showing ${res.from} to ${res.to} of ${res.total} entries`);
                        renderPagination(res);
                    } else {
                        const emptyMsg = jenisSo === 'monthly' ? 'Tidak ada data SOH bulan ini.' :
                            'Tidak ada data SOH hari ini.';
                        tableBody.append(`
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">${emptyMsg}</td>
                            </tr>
                        `);
                        $('#tableInfo').text('');
                        $('#paginationContainer').empty();
                    }
                }
            });
        }

        function openAddSOH() {
            mode = 'add';
            $('#formSOH')[0].reset();
            $('#soh_id').val('');
            $('#sohModalLabel').html('<i class="mdi mdi-plus-circle me-1"></i>Tambah Data SOH');
            $('#btnSaveSOH').html('Simpan');

            $('#addSection').show();
            $('#editSection').hide();

            const activeJenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';
            $('#soh_jenis_so').val(activeJenisSo).prop('disabled', false);

            loadAreaOptionsForAdd();
            $('#sohModal').modal('show');
        }

        function editSOH(id) {
            mode = 'edit';
            $('#sohModalLabel').html('<i class="mdi mdi-pencil-outline me-1"></i>Edit Data SOH');
            $('#btnSaveSOH').html('Update');

            // Close details modal if open
            const modalEl = document.getElementById('modalSOHDetail');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            $('#addSection').hide();
            $('#editSection').show();

            $.ajax({
                url: `{{ route('wsp.stock_opname.soh.show', '') }}/${id}`,
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const data = res.data;
                        $('#soh_id').val(data.id);
                        $('#soh_jenis_so').val(data.jenis_so).prop('disabled', false);

                        $('#edit_mid_barang').val(data.barang ?
                            `${data.barang.mid_barang} - ${data.barang.nama_barang}` : '');

                        // Populate loc_id and location info
                        const rak = data.location && data.location.rak ? data.location.rak : null;
                        $('#edit_loc_id').val(data.loc_id || '');
                        if (rak) {
                            $('#edit_location_info').html(
                                `<span class="badge bg-primary-subtle text-primary me-1">${rak.plant || ''} - ${rak.s_loc || ''}</span> <strong>${rak.area_rak}</strong> &gt; ${rak.nama_rak} &gt; ${rak.kolom_rak || '-'}.${rak.level_rak || '-'}.${rak.box_rak || '-'}`
                            );
                        } else {
                            $('#edit_location_info').html(
                                '<span class="badge badge-soft-warning">Not Yet</span>');
                        }

                        $('#unrest').val(parseFloat(data.qty_unrest));
                        $('#qi').val(parseFloat(data.qty_qi));
                        $('#block').val(parseFloat(data.qty_block));
                        $('#sohModal').modal('show');
                    }
                }
            });
        }

        function detailSOH(item) {
            const barangName = item.barang ? item.barang.nama_barang : 'Stock On Hand';
            const barangMid = item.barang ? item.barang.mid_barang : 'N/A';
            const uom = item.barang ? item.barang.uom : '';

            $('#modalTitle').text(barangName);
            $('#modalMID').text(`MID: ${barangMid}`);

            const content = `
                <div class="p-4">
                    <div class="bg-primary bg-gradient rounded-3 p-3 mb-4 text-center shadow">
                        <h6 class="d-block mb-1 text-white text-uppercase small font-bold">Total Stock On Hand</h6>
                        <h1 class="mb-0 fw-bold display-4 text-white">${parseFloat(item.qty_soh).toLocaleString('id-ID')} ${uom}</h1>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3 border-bottom pb-2">
                            <i class="mdi mdi-map-marker-outline me-1"></i>Lokasi Rak
                        </h6>
                        ${(() => {
                            const rak = item.location && item.location.rak ? item.location.rak : null;
                            if (!rak) return '<div class="text-center py-2"><span class="badge badge-soft-warning fs-6">Not Yet</span></div>';
                            return `<div class="row g-3">
                                                <div class="col-6">
                                                    <div class="bg-light rounded-3 p-2 border text-center">
                                                        <div class="small text-muted mb-1 text-uppercase">Plant</div>
                                                        <strong>${rak.plant || '-'}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-3 p-2 border text-center">
                                                        <div class="small text-muted mb-1 text-uppercase">S.Loc</div>
                                                        <strong>${rak.s_loc || '-'}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="bg-light rounded-3 p-2 border text-center">
                                                        <div class="small text-muted mb-1 text-uppercase">Area</div>
                                                        <strong>${rak.area_rak || '-'}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="bg-light rounded-3 p-2 border text-center">
                                                        <div class="small text-muted mb-1 text-uppercase">Rak</div>
                                                        <strong>${rak.nama_rak || '-'}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="bg-light rounded-3 p-2 border text-center">
                                                        <div class="small text-muted mb-1 text-uppercase">Kolom</div>
                                                        <strong>${rak.kolom_rak || '-'}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-3 p-2 border text-center">
                                                        <div class="small text-muted mb-1 text-uppercase">Level</div>
                                                        <strong>${rak.level_rak || '-'}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="bg-light rounded-3 p-2 border text-center">
                                                        <div class="small text-muted mb-1 text-uppercase">Bin</div>
                                                        <strong>${rak.box_rak || '-'}</strong>
                                                    </div>
                                                </div>
                                            </div>`;
                        })()}
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3 border-bottom pb-2">
                            <i class="mdi mdi-information-outline me-1"></i>Kuantitas Detil
                        </h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3 border">
                                    <div class="small text-muted mb-1 text-uppercase">UNREST</div>
                                    <strong class="text-success fs-5">${parseFloat(item.qty_unrest).toLocaleString('id-ID')} ${uom}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3 border">
                                    <div class="small text-muted mb-1 text-uppercase">QI</div>
                                    <strong class="text-info fs-5">${parseFloat(item.qty_qi).toLocaleString('id-ID')} ${uom}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3 border">
                                    <div class="small text-muted mb-1 text-uppercase">BLOCKED</div>
                                    <strong class="text-danger fs-5">${parseFloat(item.qty_block).toLocaleString('id-ID')} ${uom}</strong>
                                </div>
                            </div>
                        </div>
                    </div>                  
                </div>
                <div class="border-top p-3 bg-white sticky-bottom">
                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100" onclick="editSOH(${item.id})">
                                <i class="mdi mdi-pencil me-1"></i>Edit
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-danger w-100" onclick="deleteSOH(${item.id})">
                                <i class="mdi mdi-trash-can me-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#modalContent').html(content);
            const modal = new bootstrap.Modal(document.getElementById('modalSOHDetail'));
            modal.show();
        }

        function deleteSOH(id) {
            Swal.fire({
                title: 'Hapus Data SOH?',
                text: 'Data SOH ini akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('wsp.stock_opname.soh.delete', '') }}/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.status) {
                                Swal.fire('Hapus Berhasil', res.message, 'success');
                                const modalEl = document.getElementById('modalSOHDetail');
                                const modal = bootstrap.Modal.getInstance(modalEl);
                                if (modal) modal.hide();
                                loadSOHList();
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                Swal.fire('Gagal', xhr.responseJSON.message,
                                    'error');
                            } else {
                                Swal.fire('Gagal',
                                    'Terjadi kesalahan saat menghapus data SOH.',
                                    'error');
                            }
                        }
                    });
                }
            });
        }

        function renderPagination(data) {
            const container = $("#paginationContainer");
            container.empty();

            if (data.last_page <= 1) return;

            let html = '<nav><ul class="pagination mb-0">';
            const prevDisabled = data.current_page === 1 ? 'disabled' : '';
            html +=
                `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" onclick="loadSOHList(${data.current_page - 1})">Previous</a></li>`;

            for (let i = 1; i <= data.last_page; i++) {
                const active = data.current_page === i ? 'active' : '';
                html +=
                    `<li class="page-item ${active}"><a class="page-link" href="#" onclick="loadSOHList(${i})">${i}</a></li>`;
            }

            const nextDisabled = data.current_page === data.last_page ? 'disabled' : '';
            html +=
                `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" onclick="loadSOHList(${data.current_page + 1})">Next</a></li>`;
            html += '</ul></nav>';

            container.append(html);
        }
    </script>
@endsection
