@extends('layouts.app')

@section('styles')
    <style>
        .page-header {
            margin-bottom: 2rem;
        }

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

        .table-card {
            /* background: white; */
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-top: 2rem;
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

        .custom-table tbody tr:hover {
            background: #f8f9ff;
            transform: scale(1.01);
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
            .page-header {
                margin-bottom: 1.5rem;
            }

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
            <div class="page-header" data-aos="fade-down">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="page-title">Stock Location</h3>
                        <p class="page-subtitle">Kelola lokasi penyimpanan stok barang</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="action-buttons justify-content-md-end">
                            <button type="button" class="btn btn-action btn-upload" id="btnUpload" data-bs-toggle="modal"
                                data-bs-target="#modalUpload">
                                <i class="mdi mdi-file-upload"></i>
                                <span>Upload</span>
                            </button>
                            {{-- <a href="{{ route('stock.loc_download') }}" target="_blank"
                                class="btn btn-action btn-template" id="btnDownloadTemplate">
                                <i class="mdi mdi-download"></i>
                                <span>Download Template</span>
                            </a>
                            <button type="button" class="btn btn-action btn-upload" id="btnUpload">
                                <i class="mdi mdi-file-upload"></i>
                                <span>Upload Excel</span>
                            </button> --}}
                            <button type="button" class="btn btn-action btn-add" id="btnAdd">
                                <i class="mdi mdi-plus-circle"></i>
                                <span>Tambah Data</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="table-card" data-aos="fade-up">
                <div class="table-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5><i class="mdi mdi-table me-2"></i>Daftar Lokasi Stok</h5>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <div class="search-box ms-auto">
                                <i class="mdi mdi-magnify"></i>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari Mid...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-body">
                    <div class="table-responsive">
                        <table class="table custom-table" id="locationTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Mid Barang</th>
                                    <th>Area Rak</th>
                                    <th>Nama Rak</th>
                                    <th>Kolom Rak</th>
                                    <th>Level Rak</th>
                                    <th>Box Rak</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <!-- Data will be loaded here -->
                                <tr class="empty-state-row">
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <i class="mdi mdi-package-variant-closed"></i>
                                            <h6>Belum Ada Data</h6>
                                            <p>Klik tombol "Tambah Data" atau "Upload Excel" untuk menambahkan lokasi
                                                stok</p>
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

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Lokasi Stok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formLocation">
                    <div class="modal-body">
                        <input type="hidden" id="locationId">

                        <div class="mb-3">
                            <label class="form-label">MID Barang <span class="text-danger">*</span></label>
                            <select id="midBarang" class="form-control" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Area Rak <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="areaRak" placeholder="Contoh: FL1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Rak <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="namaRak" placeholder="Contoh: A" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kolom Rak <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kolomRak" placeholder="Contoh: 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Level Rak <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="levelRak" placeholder="Contoh: 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Box Rak</label>
                            <input type="text" class="form-control" id="boxRak" placeholder="Contoh: 001"
                                required>
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

    <!-- Modal Upload -->
    <div class="modal fade" id="modalUpload" tabindex="-1" aria-labelledby="modalUpload" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpload">Upload Stock Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Upload Form -->
                    <form id="formUploadLoc" enctype="multipart/form-data">
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
                                <a href="{{ route('stock.loc_download') }}" target="_blank"
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadStockLocation();

            let allLocations = [];
            let filteredLocations = [];
            let currentPage = 1;
            const itemsPerPage = 10;

            // Ambil data dari backend
            function loadStockLocation() {
                $.ajax({
                    url: "{{ route('stock.loc_data') }}",
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0 text-muted">Memuat data...</p>
                                </td>
                            </tr>
                        `);
                    },
                    success: function(res) {
                        if (res.success && Array.isArray(res.data)) {
                            allLocations = res.data;
                            filteredLocations = allLocations;
                            renderTable();
                        } else {
                            $('#tableBody').html(
                                '<tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="9" class="text-center text-danger py-3">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> Gagal memuat data dari server.
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            // Render table
            function renderTable() {
                const tbody = $('#tableBody');
                tbody.empty();

                if (filteredLocations.length === 0) {
                    tbody.html(`
                        <tr class="empty-state-row">
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="mdi mdi-package-variant-closed"></i>
                                    <h6>Tidak Ada Data</h6>
                                    <p>Data yang Anda cari tidak ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    `);
                    updatePaginationInfo(0, 0, 0);
                    return;
                }

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, filteredLocations.length);
                const pageData = filteredLocations.slice(startIndex, endIndex);

                pageData.forEach((location, index) => {
                    const statusBadge = location.status === 'active' ?
                        '<span class="badge-status badge-active"><i class="mdi mdi-check-circle"></i> Active</span>' :
                        '<span class="badge-status badge-inactive"><i class="mdi mdi-close-circle"></i> Inactive</span>';

                    tbody.append(`
                        <tr>
                            <td>${startIndex + index + 1}</td>
                            <td><strong>${location.barang.mid_barang}</strong></td>
                            <td>${location.rak.area_rak}</td>
                            <td>${location.rak.nama_rak}</td>
                            <td>${location.rak.kolom_rak}</td>
                            <td>${location.rak.level_rak}</td>
                            <td>${location.rak.box_rak}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-sm-action btn-edit" onclick="editLocation(${location.id})" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm-action btn-delete" onclick="deleteLocation(${location.id})" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                updatePaginationInfo(startIndex + 1, endIndex, filteredLocations.length);
                renderPagination();
            }

            // Update pagination info
            function updatePaginationInfo(from, to, total) {
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#totalRecords').text(total);
            }

            // Render pagination
            function renderPagination() {
                const totalPages = Math.ceil(filteredLocations.length / itemsPerPage);
                const pagination = $('#pagination');
                pagination.empty();

                if (totalPages <= 1) return;

                // Previous button
                pagination.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>
                    </li>
                `);

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                        pagination.append(`
                            <li class="page-item ${i === currentPage ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                            </li>
                        `);
                    } else if (i === currentPage - 2 || i === currentPage + 2) {
                        pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                // Next button
                pagination.append(`
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </li>
                `);
            }

            // Change page
            window.changePage = function(page) {
                const totalPages = Math.ceil(filteredLocations.length / itemsPerPage);
                if (page < 1 || page > totalPages) return;
                currentPage = page;
                renderTable();
            }

            // Search
            $('#searchInput').on('input', function() {
                const keyword = $(this).val().toLowerCase().trim();

                if (keyword === '') {
                    filteredLocations = allLocations; // reset
                } else {
                    filteredLocations = allLocations.filter(item =>
                        item.barang &&
                        item.barang.mid_barang &&
                        item.barang.mid_barang.toLowerCase().includes(keyword)
                    );
                }

                currentPage = 1; // reset ke page 1 saat mencari
                renderTable();
            });

            function initSelectBarang() {
                $('#midBarang').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#modalForm'),
                    placeholder: '-- Pilih Barang --',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: "{{ route('stock.loc_data_barang') }}",
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

            // Add button
            $('#btnAdd').click(function() {
                // reset select2
                $('#midBarang').val(null).trigger('change');
                $('#midBarang').empty();
                if ($('#midBarang').hasClass("select2-hidden-accessible")) {
                    $('#midBarang').select2('destroy');
                }

                // init ulang
                initSelectBarang();

                $('#modalTitle').text('Tambah Lokasi Stok');
                $('#formLocation')[0].reset();
                $('#locationId').val('');
                $('#modalForm').modal('show');
            });

            // Edit location
            window.editLocation = function(id) {
                $('#midBarang').val(null).trigger('change');
                $('#midBarang').empty();
                if ($('#midBarang').hasClass("select2-hidden-accessible")) {
                    $('#midBarang').select2('destroy');
                }
                // initSelectBarang();
                $.ajax({
                    url: "{{ route('stock.loc_show', '') }}/" + id,
                    type: 'GET',
                    success: function(res) {
                        const data = res.data;

                        $('#sohId').val(data.id);
                        $('#midBarang')
                            .append(new Option(
                                `${data.mid_barang}`,
                                data.mid_barang,
                                true,
                                true
                            ))
                            .trigger('change');
                        $('#areaRak').val(data.area_rak);
                        $('#namaRak').val(data.nama_rak);
                        $('#kolomRak').val(data.kolom_rak);
                        $('#levelRak').val(data.level_rak);
                        $('#boxRak').val(data.box_rak);

                        // ubah judul modal & tampilkan modal
                        $('#modalTitle').text('Edit Stock Location');
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

            // Delete location
            window.deleteLocation = function(id) {
                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // const deleteUrl = "{{ route('stock.loc_delete', ':id') }}".replace(':id',
                        //     id);
                        $.ajax({
                            // url: deleteUrl,
                            url: "{{ route('stock.loc_delete', '') }}/" + id,
                            type: 'DELETE',
                            success: function(res) {
                                toastr.success(res.message || 'Data berhasil dihapus');
                                loadStockLocation(); // reload tabel
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

            // submit add & edit form
            $('#formLocation').submit(function(e) {
                e.preventDefault();

                const id = $('#locationId').val();
                const payload = {
                    mid_barang: $('#midBarang').val().trim(),
                    area_rak: $('#areaRak').val().trim(),
                    nama_rak: $('#namaRak').val().trim(),
                    kolom_rak: $('#kolomRak').val().trim(),
                    level_rak: $('#levelRak').val().trim(),
                    box_rak: $('#boxRak').val().trim(),
                };

                if (!payload.mid_barang || !payload.nama_rak) {
                    toastr.warning('MID Barang dan Nama Rak wajib diisi!');
                    return;
                }

                const storeUrl = "{{ route('stock.loc_store') }}";
                const updateUrl = "{{ route('stock.loc_update', '') }}/" + id;

                const method = id ? 'PUT' : 'POST';
                const url = id ? updateUrl : storeUrl;

                $.ajax({
                    url: url,
                    type: method,
                    data: payload,
                    success: function(res) {
                        toastr.success(res.message || 'Data berhasil disimpan');
                        $('#modalForm').modal('hide');
                        loadStockLocation(); // reload data
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

            // Upload button
            $('#btnUpload').click(function() {
                $('#modalUpload').modal('show');
            });

            // Form Upload
            $('#formUploadLoc').submit(function(e) {
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

                // Siapkan form data
                const formData = new FormData();
                formData.append('file', file);

                $.ajax({
                    url: "{{ route('stock.loc_upload') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('#btnUploadSubmit').prop('disabled', true).text('Uploading...');
                    },
                    success: function(res) {
                        toastr.success(res.message);

                        if (res.skipped && res.skipped.length > 0) {
                            let skippedList = res.skipped.map(item => `<li>${item}</li>`).join(
                                '');
                            Swal.fire({
                                icon: 'info',
                                title: 'Beberapa Data Dilewati',
                                html: `<ul class="text-start">${skippedList}</ul>`,
                                width: 500,
                            });
                        }

                        $('#modalUpload').modal('hide');
                        $('#formUploadLoc')[0].reset();
                        $('#btnUploadSubmit').prop('disabled', false).text('Upload');
                        // reload data table / list setelah upload
                        if (typeof loadStockLocation === 'function') loadStockLocation();
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat upload.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                        $('#btnUploadSubmit').prop('disabled', false).text('Upload');
                    }
                });
            });

            renderTable();
        });
    </script>
@endsection
