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
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0"><i class="mdi mdi-upload me-2"></i>Upload Stock On Hand WPM</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WPM</a></li>
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
                                <div class="col-lg-6 col-md-12 d-flex gap-2 justify-content-lg-end" id="soh-actions-container">
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
                                    <div class="alert alert-warning py-2 px-3 mb-0 w-100 text-center small border-0 shadow-none">
                                        <i class="mdi mdi-alert-circle me-1"></i> Data Master Barang kosong. Silakan isi <a href="{{ route('master.wpm.barang.index') }}" class="alert-link text-decoration-underline fw-bold">Master Barang</a> terlebih dahulu.
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
                            <button class="nav-link active" data-bs-toggle="tab" data-value="cycle_count" type="button" role="tab">
                                <i class="mdi mdi-sync me-1"></i> Cycle Count (Daily)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-value="monthly" type="button" role="tab">
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
                                    <th class="text-end">Total SOH</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
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
                        <i class="mdi mdi-file-excel-outline me-1"></i> Upload File SOH WPM
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
                            <input type="file" class="form-control" id="excel_file" name="file" accept=".xlsx, .xls"
                                required>
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
                        <a href="{{ route('wpm.stock_opname.soh.template') }}" class="btn btn-primary flex-grow-1">
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
                        <div class="mb-3">
                            <label for="barang_id" class="form-label fw-semibold">Barang (MID)</label>
                            <select class="form-select select2" id="barang_id" name="barang_id" required
                                style="width: 100%">
                                <option value="">-- Pilih Barang --</option>
                            </select>
                        </div>
                        <div id="qtyInputWrapper" class="row g-3">
                            <div class="col-md-4">
                                <label for="unrest" class="form-label fw-semibold">UNREST</label>
                                <input type="number" class="form-control" id="unrest" name="unrest" value="0"
                                    min="0" step="any" required>
                            </div>
                            <div class="col-md-4">
                                <label for="qi" class="form-label fw-semibold">QI</label>
                                <input type="number" class="form-control" id="qi" name="qi" value="0"
                                    min="0" step="any" required>
                            </div>
                            <div class="col-md-4">
                                <label for="block" class="form-label fw-semibold">BLOCKED</label>
                                <input type="number" class="form-control" id="block" name="block" value="0"
                                    min="0" step="any" required>
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
            // Initialize select2 on modal elements
            $('#barang_id').select2({
                dropdownParent: $('#sohModal')
            });

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
                
                let postData = $(this).serialize();
                if ($('#soh_jenis_so').is(':disabled')) {
                    postData += '&jenis_so=' + encodeURIComponent($('#soh_jenis_so').val());
                }
                const url = mode === 'add' ?
                    "{{ route('wpm.stock_opname.soh.store') }}" :
                    `{{ route('wpm.stock_opname.soh.update', '') }}/${sohId}`;

                $.ajax({
                    url: url,
                    type: "POST",
                    data: postData,
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
                    url: "{{ route('wpm.stock_opname.soh.import') }}",
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
                    text: 'Seluruh data SOH WPM ' + (jenisSo === 'monthly' ? 'bulan ini' : 'hari ini') + ' akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wpm.stock_opname.soh.reset_all') }}",
                            type: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}",
                                jenis_so: jenisSo
                            },
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire('Berhasil', res.message, 'success');
                                    loadSOHList();
                                } else {
                                    Swal.fire('Gagal', res.message, 'error');
                                }
                            }
                        });
                    }
                });
            });
        });

        function loadSOHList(page = 1) {
            const tableBody = $('#tableSOHList tbody');
            const search = $('#searchSOHInput').val();
            const jenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';

            $.ajax({
                url: "{{ route('wpm.stock_opname.soh.list') }}",
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
                            const barangMid = item.barang ? item.barang.mid : 'N/A';
                            const uom = item.barang ? item.barang.uom : '';
                            const qtySoh = parseFloat(item.qty_soh).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
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
                        const emptyMsg = jenisSo === 'monthly' ? 'Tidak ada data SOH bulan ini.' : 'Tidak ada data SOH hari ini.';
                        tableBody.append(`
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">${emptyMsg}</td>
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
            $('#barang_id').prop('disabled', false);
            const activeJenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';
            $('#soh_jenis_so').val(activeJenisSo).prop('disabled', false);

            loadBarangOptions(() => {
                $('#barang_id').val('').trigger('change');
                $('#sohModal').modal('show');
            });
        }

        function editSOH(id) {
            mode = 'edit';
            $('#sohModalLabel').html('<i class="mdi mdi-pencil-outline me-1"></i>Edit Data SOH');
            $('#btnSaveSOH').html('Update');

            // Close details modal if open
            const modalEl = document.getElementById('modalSOHDetail');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            loadBarangOptions(() => {
                $.ajax({
                    url: `{{ route('wpm.stock_opname.soh.show', '') }}/${id}`,
                    type: 'GET',
                    success: function(res) {
                        if (res.status === 'success') {
                            const data = res.data;
                            $('#soh_id').val(data.id);
                            $('#soh_jenis_so').val(data.jenis_so).prop('disabled', false);
                            $('#barang_id').val(data.barang_id).trigger('change').prop('disabled',
                                true);
                            $('#unrest').val(data.qty_unrest !== null ? parseFloat(data.qty_unrest) : 0);
                            $('#qi').val(data.qty_qi !== null ? parseFloat(data.qty_qi) : 0);
                            $('#block').val(data.qty_block !== null ? parseFloat(data.qty_block) : 0);
                            $('#sohModal').modal('show');
                        }
                    }
                });
            });
        }

        function detailSOH(item) {
            const barangName = item.barang ? item.barang.nama_barang : 'Stock On Hand';
            const barangMid = item.barang ? item.barang.mid : 'N/A';
            const uom = item.barang ? item.barang.uom : '';

            $('#modalTitle').text(barangName);
            $('#modalMID').text(`MID: ${barangMid}`);

            const content = `
                <div class="p-4">
                    <div class="bg-primary bg-gradient rounded-3 p-3 mb-4 text-center shadow">
                        <h6 class="d-block mb-1 text-white text-uppercase small font-bold">Total Stock On Hand</h6>
                        <h1 class="mb-0 fw-bold display-4 text-white">${parseFloat(item.qty_soh).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} ${uom}</h1>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3 border-bottom pb-2">
                            <i class="mdi mdi-information-outline me-1"></i>Kuantitas Detil
                        </h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3 border">
                                    <div class="small text-muted mb-1 text-uppercase">UNREST</div>
                                    <strong class="text-success fs-5">${parseFloat(item.qty_unrest).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} ${uom}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3 border">
                                    <div class="small text-muted mb-1 text-uppercase">QI</div>
                                    <strong class="text-info fs-5">${parseFloat(item.qty_qi).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} ${uom}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded-3 p-3 border">
                                    <div class="small text-muted mb-1 text-uppercase">BLOCKED</div>
                                    <strong class="text-danger fs-5">${parseFloat(item.qty_block).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} ${uom}</strong>
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

        function loadBarangOptions(callback) {
            $('#barang_id').html('<option value="">Memuat data...</option>');

            $.ajax({
                url: "{{ route('wpm.stock_opname.soh.getBarang') }}",
                type: 'GET',
                success: function(response) {
                    $('#barang_id').html('<option value="">-- Pilih Barang --</option>');
                    if (response.status === 'success' && response.data.length > 0) {
                        response.data.forEach(function(item) {
                            $('#barang_id').append(
                                `<option value="${item.id}">${item.mid} - ${item.nama_barang} (${item.uom})</option>`
                            );
                        });
                    } else {
                        $('#barang_id').html('<option value="">Data barang kosong</option>');
                    }
                    if (typeof callback === 'function') callback();
                }
            });
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
                        url: `{{ route('wpm.stock_opname.soh.delete', '') }}/${id}`,
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
