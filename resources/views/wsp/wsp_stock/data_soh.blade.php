@extends('layouts.app')

@section('styles')
    <style>
        .page-title {
            /* color: #2d3748; */
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            /* color: #718096; */
            font-size: 0.95rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 0.625rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-upload {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 123, 255, 0.5);
            color: white;
        }

        .btn-add {
            background: linear-gradient(135deg, #198754, #146c43);
            color: white;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.5);
            color: white;
        }

        .btn-export {
            background: linear-gradient(135deg, #6f42c1, #563d7c);
            color: white;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.3);
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(111, 66, 193, 0.5);
            color: white;
        }

        .btn-template {
            background: white;
            color: #6c757d;
            border: 2px solid #dee2e6;
        }

        .btn-template:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
            transform: translateY(-2px);
            color: #495057;
        }

        .table-header {
            /* background: linear-gradient(135deg, #f8f9fa, #e9ecef); */
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid #dee2e6;
        }

        .table-header h5 {
            margin: 0;
            /* color: #2d3748; */
            font-weight: 600;
            font-size: 1.1rem;
        }

        .table-body {
            padding: 1.5rem;
        }

        .custom-table {
            margin: 0;
        }

        .custom-table thead th {
            /* background: #f8f9fa; */
            /* color: #495057; */
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem 0.75rem;
            white-space: nowrap;
        }

        .custom-table tbody tr {
            transition: all 0.2s ease;
        }

        .custom-table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            /* color: #4a5568; */
            font-size: 0.875rem;
        }

        .badge-status {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm-action {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-edit {
            background: #fff3cd;
            color: #997404;
        }

        .btn-edit:hover {
            background: #ffe69c;
            transform: scale(1.05);
        }

        .btn-delete {
            background: #f8d7da;
            color: #842029;
        }

        .btn-delete:hover {
            background: #f1aeb5;
            transform: scale(1.05);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 4rem;
            /* color: #cbd5e0; */
            margin-bottom: 1rem;
        }

        .empty-state h6 {
            /* color: #4a5568; */
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            /* color: #a0aec0; */
            font-size: 0.875rem;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            /* background: linear-gradient(135deg, #f8f9fa, #e9ecef); */
            border-bottom: 2px solid #dee2e6;
            border-radius: 1rem 1rem 0 0;
            padding: 1.25rem 1.5rem;
        }

        .modal-title {
            /* color: #2d3748; */
            font-weight: 700;
            font-size: 1.25rem;
        }

        .form-label {
            /* color: #4a5568; */
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
        }

        .modal-footer {
            border-top: 2px solid #e9ecef;
            padding: 1rem 1.5rem;
        }

        /* Search Box */
        .search-box {
            position: relative;
            max-width: 300px;
        }

        .search-box input {
            padding-left: 2.5rem;
            border-radius: 0.75rem;
            border: 2px solid #e2e8f0;
        }

        .search-box i {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }

        /* Pagination */
        .pagination {
            margin: 0;
        }

        .page-link {
            color: #007bff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            margin: 0 0.25rem;
            font-weight: 600;
        }

        .page-link:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .page-item.active .page-link {
            background: #007bff;
            border-color: #007bff;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .page-title {
                font-size: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }

            .table-responsive {
                border-radius: 0.5rem;
            }

            .custom-table thead th {
                font-size: 0.75rem;
                padding: 0.75rem 0.5rem;
            }

            .custom-table tbody td {
                font-size: 0.8rem;
                padding: 0.75rem 0.5rem;
            }

            .search-box {
                max-width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="page-header mb-2" data-aos="fade-down">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="page-title">Stock On Hand</h3>
                        <p class="page-subtitle">Kelola Stock On Hand</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="action-buttons justify-content-md-end">

                            @can('permission', 'wsp-soh-plus')
                                <!-- 🔥 Satu tombol untuk Upload + Download -->
                                <button type="button" class="btn btn-action btn-upload" id="btnUpload" data-bs-toggle="modal"
                                    data-bs-target="#modalUpload">
                                    <i class="mdi mdi-file-upload"></i>
                                    <span>Upload Data</span>
                                </button>
                            @endcan

                            <!-- Button Download Excel -->
                            <a href="{{ route('stock.soh_export') }}" class="btn btn-action btn-export" id="btnExport">
                                <i class="mdi mdi-file-excel"></i>
                                <span>Download Excel</span>
                            </a>

                            @can('permission', 'wsp-soh-plus')
                                <!-- Button Tambah Data tetap -->
                                <button type="button" class="btn btn-action btn-add" id="btnAdd">
                                    <i class="mdi mdi-plus-circle"></i>
                                    <span>Tambah Data</span>
                                </button>
                            @endcan

                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card shadow-sm" data-aos="fade-up">
                <div class="table-header">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h5><i class="mdi mdi-table me-2"></i>Data Stock On Hand</h5>
                        </div>
                        <div class="col-md-7 mt-3 mt-md-0">
                            <div class="d-flex align-items-center justify-content-md-end gap-3 flex-wrap">
                                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 bg-light border rounded-3 px-3 py-1 shadow-sm">
                                    <input class="form-check-input mt-0" type="checkbox" id="filterExcludeZero" style="cursor: pointer;">
                                    <label class="form-check-label small fw-semibold text-secondary mb-0" for="filterExcludeZero" style="cursor: pointer; user-select: none;">
                                        <i class="mdi mdi-filter-remove-outline me-1 text-danger"></i>Exclude Stock 0
                                    </label>
                                </div>
                                <div class="d-flex align-items-center gap-2 bg-light border rounded-3 px-2 py-1 shadow-sm">
                                    <label class="small fw-semibold text-secondary mb-0" for="perPageSelect">Baris:</label>
                                    <select class="form-select form-select-sm border-0 bg-transparent py-0 px-1 fw-semibold text-secondary" id="perPageSelect" style="width: auto; cursor: pointer;">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="search-box">
                                    <i class="mdi mdi-magnify"></i>
                                    <input type="text" class="form-control" id="searchInput"
                                        placeholder="Cari Mid atau desc...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-body">
                    {{-- <div class="alert alert-info py-2 px-3 w-100" role="alert">
                        <small>
                            <i class="ri-information-line me-1"></i>
                            <strong>Total Qty SOH</strong> Merupakan Qty  yang belum di konfirmasi karena sedang
                            dalam proses PR
                        </small>
                    </div> --}}
                    <div class="table-responsive">
                        <table class="table table-hover custom-table" id="sohTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Mid</th>
                                    <th>DESC</th>
                                    <th>UoM</th>
                                    <th>Total Qty SOH</th>
                                    <th>Unrest</th>
                                    <th>Qual Insp</th>
                                    <th>Blocked</th>
                                    <th>Transf</th>
                                    <th>Last Updated</th>
                                    @can('permission', 'wsp-soh-plus')
                                        <th>Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- Data will be loaded here -->
                                <tr class="empty-state-row">
                                    <td colspan="11">
                                        <div class="empty-state">
                                            <i class="mdi mdi-package-variant-closed"></i>
                                            <h6>Belum Ada Data</h6>
                                            <p>Klik tombol "Tambah Data" atau "Upload Excel" untuk menambahkan Stok On Hand
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Menampilkan <strong id="showingFrom">0</strong> sampai <strong id="showingTo">0</strong>
                            dari <strong id="totalRecords">0</strong> data
                        </div>
                        <nav>
                            <ul class="pagination mb-0" id="pagination">
                                <!-- Pagination will be generated here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Upload dan Download Template -->
    <div class="modal fade" id="modalUpload" tabindex="-1" aria-labelledby="modalUpload" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpload">Upload Stock On Hand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Upload Form -->
                    <form id="formUploadSoh" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Upload File Excel</label>
                            <input type="file" name="file" id="fileUpload" class="form-control" required>
                            <div class="form-text">Format: .xlsx, .xls, .csv (Maks. 10MB)</div>
                        </div>
                        <div class="alert alert-info mb-3">
                            <i class="mdi mdi-information-outline me-2"></i>
                            <small>Pastikan format file sesuai dengan template yang telah disediakan</small>
                        </div>
                        <!-- Download Template -->
                        <div class="row g-2 mb-2 text-nowrap">

                            <!-- Download Template -->
                            <div class="col-6">
                                <a href="{{ route('stock.soh_download') }}" target="_blank"
                                    class="btn btn-outline-info w-100" id="btnDownloadTemplate">
                                    <i class="mdi mdi-download"></i>
                                    <span>Download Template</span>
                                </a>
                            </div>

                            <!-- Upload -->
                            <div class="col-6">
                                <button type="submit" class="btn btn-outline-primary w-100" id="btnUploadSubmit">
                                    <i class="mdi mdi-upload"></i>Upload Sekarang
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Stock On Hand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="formStockOnHand">
                    <div class="modal-body">
                        <input type="hidden" id="sohId">

                        <div class="mb-3">
                            <label class="form-label">MID Barang <span class="text-danger">*</span></label>
                            <select id="midBarang" class="form-control" required></select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unrest</label>
                            <input type="number" class="form-control" id="unrest" placeholder="Contoh: 10"
                                min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Qual Insp</label>
                            <input type="number" class="form-control" id="qualInsp" placeholder="Contoh: 5"
                                min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Blocked</label>
                            <input type="number" class="form-control" id="blocked" placeholder="Contoh: 2"
                                min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Transf</label>
                            <input type="number" class="form-control" id="transf" placeholder="Contoh: 3"
                                min="0">
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadStockLocation();

            let currentPage = 1;
            let itemsPerPage = 10;
            let searchTimer = null;

            // Ambil data dari backend (Server-side Pagination & Filtering)
            function loadStockLocation(page = 1) {
                currentPage = page;
                const keyword = $('#searchInput').val().trim();
                const excludeZero = $('#filterExcludeZero').is(':checked');

                $.ajax({
                    url: "{{ route('stock.soh_data') }}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        page: currentPage,
                        per_page: itemsPerPage,
                        search: keyword,
                        exclude_zero: excludeZero ? 1 : 0
                    },
                    beforeSend: function() {
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0 text-muted">Memuat data...</p>
                                </td>
                            </tr>
                        `);
                    },
                    success: function(res) {
                        if (res.success && Array.isArray(res.data) && res.data.length > 0) {
                            renderTable(res.data, res.pagination);
                        } else {
                            $('#tableBody').html(`
                                <tr class="empty-state-row">
                                    <td colspan="11">
                                        <div class="empty-state">
                                            <i class="mdi mdi-package-variant-closed"></i>
                                            <h6>Tidak Ada Data</h6>
                                            <p>Data yang Anda cari tidak ditemukan</p>
                                        </div>
                                    </td>
                                </tr>
                            `);
                            updatePaginationInfo(0, 0, 0);
                            $('#pagination').empty();
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="11" class="text-center text-danger py-3">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> Gagal memuat data dari server.
                                </td>
                            </tr>
                        `);
                        updatePaginationInfo(0, 0, 0);
                        $('#pagination').empty();
                    }
                });
            }

            // Render table
            function renderTable(data, pagination) {
                const tbody = $('#tableBody');
                tbody.empty();

                data.forEach((soh, index) => {
                    const rowNumber = (pagination && pagination.from) ? (pagination.from + index) : (index + 1);
                    tbody.append(`
                        <tr>
                            <td>${rowNumber}</td>
                            <td><strong>${soh.mid_barang ?? '-'}</strong></td>
                            <td><strong>${soh.nama_barang ?? '-'}</strong></td>
                            <td><strong>${soh.uom ?? '-'}</strong></td>
                            <td>${soh.qty_soh ?? 0}</td>
                            <td>${soh.unrest ?? 0}</td>
                            <td>${soh.qual_insp ?? 0}</td>
                            <td>${soh.blocked ?? 0}</td>
                            <td>${soh.transf ?? 0}</td>
                            <td>${soh.last_update ?? '-'}</td>
                            @can('permission', 'wsp-soh-plus')
                                <td>
                                    <div class="action-btns">
                                        <button class="btn btn-sm-action btn-edit" onclick="editSOH(${soh.id})" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm-action btn-delete" onclick="deleteSOH(${soh.id})" title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    `);
                });

                if (pagination) {
                    updatePaginationInfo(pagination.from, pagination.to, pagination.total);
                    renderPagination(pagination);
                }
            }

            // Update pagination info
            function updatePaginationInfo(from, to, total) {
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#totalRecords').text(total);
            }

            // Render pagination
            function renderPagination(pagination) {
                const totalPages = pagination.last_page;
                const current = pagination.current_page;
                const $pagination = $('#pagination');
                $pagination.empty();

                if (totalPages <= 1) return;

                // Previous button
                $pagination.append(`
                    <li class="page-item ${current === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${current - 1}); return false;">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>
                    </li>
                `);

                // Smart page numbers
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= current - 1 && i <= current + 1)) {
                        $pagination.append(`
                            <li class="page-item ${i === current ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                            </li>
                        `);
                    } else if (i === current - 2 || i === current + 2) {
                        $pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                // Next button
                $pagination.append(`
                    <li class="page-item ${current === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${current + 1}); return false;">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </li>
                `);
            }

            // Change page
            window.changePage = function(page) {
                loadStockLocation(page);
            };

            // Debounced Search
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    loadStockLocation(1);
                }, 350);
            });

            // Filter Exclude Stock 0
            $('#filterExcludeZero').on('change', function() {
                loadStockLocation(1);
            });

            // Per page limit selector
            $('#perPageSelect').on('change', function() {
                itemsPerPage = parseInt($(this).val()) || 10;
                loadStockLocation(1);
            });

            // submit add & edit form
            $('#formStockOnHand').submit(function(e) {
                e.preventDefault();

                const id = $('#sohId').val();

                const payload = {
                    mid_barang: $('#midBarang').val().trim(),
                    // qty_soh: $('#qtySoh').val().trim(),
                    unrest: $('#unrest').val().trim(),
                    qual_insp: $('#qualInsp').val().trim(),
                    blocked: $('#blocked').val().trim(),
                    transf: $('#transf').val().trim(),
                };

                // Validasi sederhana
                if (!payload.mid_barang) {
                    toastr.warning('MID Barang wajib diisi!');
                    return;
                }

                const storeUrl = "{{ route('stock.soh_store') }}";
                const updateUrl = "{{ route('stock.soh_update', '') }}/" + id;

                const method = id ? 'PUT' : 'POST';
                const url = id ? updateUrl : storeUrl;

                $.ajax({
                    url: url,
                    type: method,
                    data: payload,
                    success: function(res) {
                        toastr.success(res.message || 'Data berhasil disimpan');
                        $('#modalForm').modal('hide');
                        loadStockLocation(currentPage); // reload data
                    },
                    error: function(xhr) {
                        let msg = 'Gagal menyimpan data';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            });

            // Form Upload
            $('#formUploadSoh').submit(function(e) {
                e.preventDefault();

                const file = $('#fileUpload')[0].files[0];

                if (!file) {
                    toastr.warning('Silakan pilih file terlebih dahulu!');
                    return;
                }

                // Validasi ukuran file (maks 10MB)
                if (file.size > 10 * 1024 * 1024) {
                    toastr.error('Ukuran file terlalu besar! Maksimal 10MB');
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                const btnSubmit = $('#btnUploadSubmit');
                const originalHtml = btnSubmit.html();

                // Show loading state
                btnSubmit.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Uploading...'
                );

                $.ajax({
                    url: "{{ route('stock.soh_upload') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 45000, // 45 detik
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        // Sukses 200 → cek apakah ada not_found (meski seharusnya tidak, tapi safety)
                        if (res.status === 'success') {
                            if (res.not_found && res.not_found.length > 0) {
                                showNotFoundWarning(res);
                            } else {
                                toastr.success(res.message || 'Upload berhasil');
                                setTimeout(() => location.reload(), 1400);
                            }
                        } else {
                            toastr.error(res.message || 'Upload gagal');
                        }
                    },
                    error: function(xhr) {
                        let res = {};
                        try {
                            res = JSON.parse(xhr.responseText || '{}');
                        } catch (e) {}

                        if (xhr.status === 422 && res.not_found && res.not_found.length > 0) {
                            // Ini yang paling penting: tangani kasus MID not found dari 422
                            showNotFoundWarning(res);
                        } else {
                            // Error lain (template salah, exception, dll)
                            let msg = res.message || 'Terjadi kesalahan saat proses file';
                            if (xhr.status === 413) msg = 'File terlalu besar (max 10 MB)';
                            if (xhr.status === 0 || xhr.status === 504) msg =
                                'Koneksi lambat atau server timeout';
                            toastr.error(msg);
                        }
                    },
                    complete: function() {
                        btnSubmit.prop('disabled', false).html(originalHtml);
                        $('#fileUpload').val('');
                    }
                });
            });

            async function copyTextToClipboard(text) {
                if (!text) return false;

                // Coba navigator.clipboard
                try {
                    if (navigator.clipboard) {
                        await navigator.clipboard.writeText(text);
                        return true;
                    }
                } catch (err) {
                    console.warn("navigator.clipboard failed, using fallback", err);
                }

                // Fallback: textarea
                try {
                    const textArea = document.createElement("textarea");
                    textArea.value = text;
                    textArea.style.position = "fixed";
                    textArea.style.top = "0";
                    textArea.style.left = "0";
                    textArea.style.width = "2em";
                    textArea.style.height = "2em";
                    textArea.style.padding = "0";
                    textArea.style.border = "none";
                    textArea.style.outline = "none";
                    textArea.style.boxShadow = "none";
                    textArea.style.background = "transparent";
                    textArea.style.opacity = "0";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    const successful = document.execCommand('copy');
                    document.body.removeChild(textArea);
                    return successful;
                } catch (err) {
                    console.error("Fallback copy failed: ", err);
                    return false;
                }
            }

            let currentMidsToCopy = '';
            window.copyMidsToClipboard = async function(link) {
                const textToCopy = currentMidsToCopy;
                if (!textToCopy) return;

                const copied = await copyTextToClipboard(textToCopy);

                if (copied) {
                    try {
                        Swal.update({
                            title: 'Daftar MID berhasil di-copy!',
                            icon: 'success'
                        });

                        const activeLink = document.getElementById('copy-mid-link');
                        if (activeLink) {
                            activeLink.textContent = '✔ MID sudah di-copy';
                            activeLink.style.color = '#28a745';
                        }

                        setTimeout(() => {
                            const activeLinkReset = document.getElementById('copy-mid-link');
                            if (activeLinkReset) {
                                activeLinkReset.textContent = '📋 Copy daftar MID';
                                activeLinkReset.style.color = '#6c757d';
                            }

                            Swal.update({
                                title: 'MID Barang Tidak Ditemukan',
                                icon: 'warning'
                            });
                        }, 3000);
                    } catch (swalErr) {
                        console.warn("Swal.update failed: ", swalErr);
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Daftar MID berhasil di-copy!');
                        }
                    }
                } else {
                    Swal.showValidationMessage(
                        'Gagal copy. Silakan Ctrl+C manual.'
                    );
                }
            };

            async function showNotFoundWarning(res) {
                const notFoundList = res.not_found || [];
                const count = notFoundList.length;
                const totalChecked = res.total_checked || '–';
                currentMidsToCopy = notFoundList.join('\n');

                // Salin otomatis langsung dari data response
                const copiedAuto = await copyTextToClipboard(currentMidsToCopy);

                Swal.fire({
                    icon: 'warning',
                    title: 'MID Barang Tidak Ditemukan',
                    html: `
                        <p style="text-align:center">
                            <strong>${count}</strong> kode barang tidak ditemukan.<br>
                            <strong style="color:#d32f2f">Data TIDAK disimpan!</strong>
                        </p>

                        <div style="max-height:220px; overflow:auto; text-align:left;
                            background:#fff; padding:12px; border:1px solid #ddd; border-radius:6px;
                            user-select: text !important; -webkit-user-select: text !important; -moz-user-select: text !important; -ms-user-select: text !important;">
                            <ul style="padding-left:20px; margin:0; user-select: text !important; -webkit-user-select: text !important;">
                                ${notFoundList.map(id => `<li><code style="user-select: text !important; -webkit-user-select: text !important;">${id}</code></li>`).join('')}
                            </ul>
                        </div>
                    `,
                    footer: `
                        <a href="javascript:void(0)" id="copy-mid-link" onclick="copyMidsToClipboard(this)"
                        style="font-weight:600; color:${copiedAuto ? '#28a745' : '#6c757d'}; text-decoration:none;">
                            ${copiedAuto ? '✔ MID sudah di-copy otomatis' : '📋 Copy daftar MID'}
                        </a>
                    `,
                    confirmButtonText: 'Oke, Saya Perbaiki',
                    confirmButtonColor: '#f0ad4e',
                    allowOutsideClick: false
                });
            }

            // Add button
            function initSelectBarang() {
                $('#midBarang').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalForm'),
                    placeholder: '-- Pilih Barang --',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: "{{ route('stock.data_barang') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        }
                    },
                    escapeMarkup: function(markup) {
                        return markup;
                    },
                    templateResult: function(data) {
                        if (!data.id) return data.text;
                        return `<strong>${data.mid_barang}</strong> — ${data.nama_barang}`;
                    },
                    templateSelection: function(data) {
                        if (!data.id) return data.text;
                        return `<strong>${data.mid_barang}</strong> — ${data.nama_barang}`;
                    }
                });
            }

            $('#btnAdd').click(function() {
                $('#midBarang').val(null).trigger('change');
                $('#midBarang').empty();
                if ($('#midBarang').hasClass("select2-hidden-accessible")) {
                    $('#midBarang').select2('destroy');
                }

                // init ulang
                initSelectBarang();

                $('#modalTitle').text('Tambah Stock On Hand');
                $('#formStockOnHand')[0].reset();
                $('#sohId').val('');
                $('#modalForm').modal('show');
            });

            // Edit soh
            window.editSOH = function(id) {
                $('#midBarang').val(null).trigger('change');
                $('#midBarang').empty();
                if ($('#midBarang').hasClass("select2-hidden-accessible")) {
                    $('#midBarang').select2('destroy');
                }

                $.ajax({
                    url: "{{ route('stock.soh_show', '') }}/" + id,
                    type: 'GET',
                    success: function(res) {
                        const data = res.data;

                        // isi form modal dengan data dari backend
                        $('#sohId').val(data.id);
                        $('#midBarang')
                            .append(new Option(
                                `${data.mid_barang}`,
                                data.mid_barang,
                                true,
                                true
                            ))
                            .trigger('change');
                        $('#qtySoh').val(data.qty_soh);
                        $('#unrest').val(data.unrest);
                        $('#qualInsp').val(data.qual_insp);
                        $('#blocked').val(data.blocked);
                        $('#transf').val(data.transf);

                        // ubah judul modal & tampilkan modal
                        $('#modalFormLabel').text('Edit Stock Location');
                        $('#modalForm').modal('show');
                    },
                    error: function(xhr) {
                        let msg = 'Gagal mengambil data lokasi';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            };

            // Delete soh
            window.deleteSOH = function(id) {
                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('stock.soh_delete', '') }}/" + id,
                            type: 'DELETE',
                            success: function(res) {
                                toastr.success(res.message || 'Data berhasil dihapus');
                                loadStockLocation(currentPage); // reload tabel
                            },
                            error: function(xhr) {
                                let msg = 'Gagal menghapus data';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                toastr.error(msg);
                            }
                        });
                    }
                });
            };
        });
    </script>
@endsection
