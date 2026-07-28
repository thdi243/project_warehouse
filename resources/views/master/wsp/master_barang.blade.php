@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Master Barang Spareparts</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">RackMan</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Master</a></li>
                                <li class="breadcrumb-item active">Barang</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Data Barang -->
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Data Barang</h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
                            <i class="mdi mdi-database-import-outline"></i> Import Excel
                        </button>

                        <a href="{{ route('wsp.barang.export') }}" class="btn btn-info">
                            <i class="mdi mdi-database-export-outline"></i> Export Excel
                        </a>

                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrasi">
                            <i class="mdi mdi-plus"></i> Tambah Barang
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters & Search -->
                    <div class="row mb-3 g-3">
                        <div class="col-md-3">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari MID / Nama...">
                        </div>
                        <div class="col-md-3">
                            <select id="statusFilter" class="form-select">
                                <option value="active">Barang Aktif</option>
                                <option value="trashed">Barang Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="wspTable" class="table table-striped table-hover align-middle mb-0 nowrap"
                            style="width:100%">
                            <thead class="table-light text-uppercase">
                                <tr>
                                    <th>No</th>
                                    <th>Mid Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Uom</th>
                                    <th>Qty Pallet</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Diisi oleh JS --}}
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

    <!-- Modal Import -->
    <div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportLabel">
                        <i class="mdi mdi-upload"></i> Import / Template Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Gunakan file template resmi untuk memastikan format data sesuai sebelum melakukan import.
                    </p>

                    <!-- Form Import -->
                    <form id="formImport" action="{{ route('wsp.barang.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="fileImport" class="form-label fw-bold">Pilih File (.csv / .xlsx)</label>
                            <input type="file" class="form-control" id="fileImport" name="file" accept=".csv,.xlsx"
                                required>
                        </div>

                        <!-- Tombol Download & Upload sebaris -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('wsp.barang.download.template') }}" class="btn btn-outline-info w-50 me-2">
                                <i class="mdi mdi-download"></i> Download Template
                            </a>
                            <button type="submit" id="btnUpload" class="btn btn-success w-50">
                                <i class="mdi mdi-upload"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Registasi Barang --}}
    <div class="modal fade" id="modalRegistrasi" tabindex="-1" aria-labelledby="modalRegistrasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRegistrasiLabel"> <i class="mdi mdi-plus-circle me-2"></i>Registrasi
                        Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formRegistrasiBarang" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-xxl-4 col-md-6">
                                <label for="mid_barang" class="form-label">MID Barang</label>
                                <input type="number" class="form-control" id="mid_barang" name="mid_barang">
                            </div>
                            <div class="col-xxl-4 col-md-6">
                                <label for="nama_barang" class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang">
                            </div>
                            <div class="col-xxl-4 col-md-6">
                                <label for="uom" class="form-label">Uom</label>
                                <input type="text" class="form-control" id="uom" name="uom">
                            </div>
                            <div class="col-xxl-4 col-md-6">
                                <label for="qty_pallet" class="form-label">Qty Pallet</label>
                                <input type="number" step="0.01" class="form-control" id="qty_pallet"
                                    name="qty_pallet" value="1">
                            </div>
                            <div class="col-xxl-6 col-md-6">
                                <label for="image" class="form-label">Foto Barang (Opsional)</label>
                                <input type="file" class="form-control" id="image" name="image"
                                    accept=".jpeg,.jpg,.png,.gif,.svg">
                                <small class="form-text text-muted">File types: jpeg, jpg, png. Max size: 2MB</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Preview Image</label>
                                <div>
                                    <img id="imagePreview" src="" alt="Image Preview"
                                        style="max-width: 200px; max-height: 150px; display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Simpan</button>
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- modal edit --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Data Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-6">
                                <label for="midBarangEdit" class="form-label">MID Barang</label>
                                <input type="text" class="form-control" id="midBarangEdit" name="midBarangEdit">

                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="namaBarangEdit" class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" id="namaBarangEdit" name="namaBarangEdit">
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="uomEdit" class="form-label">Uom</label>
                                <input type="text" class="form-control" id="uomEdit" name="uomEdit">
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="qtyPalletEdit" class="form-label">Qty Pallet</label>
                                <input type="number" step="0.01" class="form-control" id="qtyPalletEdit"
                                    name="qtyPalletEdit">
                            </div>
                            <div class="col-xxl-6 col-md-6">
                                <div class="mb-3">
                                    <label for="imageEdit" class="form-label">Foto Barang</label>
                                    <input type="file" class="form-control" id="imageEdit" name="imageEdit"
                                        accept=".jpeg,.jpg,.png,.gif,.svg">
                                    <small class="form-text text-muted">File types: jpeg, jpg, png. Max
                                        size:
                                        2MB</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Current/Preview Image</label>
                                        <div>
                                            <img id="imagePreviewEdit" src="" alt="Image Preview"
                                                style="max-width: 200px; max-height: 150px; display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal detail --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Data Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-2">
                        <div class="col-md-4">
                            <strong>MID Barang:</strong>
                            <p id="detailMid"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Nama Barang:</strong>
                            <p id="detailNama"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Uom:</strong>
                            <p id="detailUom"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Qty Pallet:</strong>
                            <p id="detailQtyPallet"></p>
                        </div>
                        <div class="col-md-12">
                            <strong>Foto Barang:</strong>
                            <div>
                                <img id="detailImage" src="" alt="Foto Barang"
                                    style="max-width: 200px; max-height: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let allBarang = [];
            let filteredBarang = [];
            let currentPage = 1;
            const itemsPerPage = 10;

            loadBarang();

            // Load data from backend
            function loadBarang() {
                const status = $('#statusFilter').val();
                $.ajax({
                    url: `/api/wsp/data/barang`,
                    type: 'GET',
                    data: {
                        status: status
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#wspTable tbody').html(`
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0 text-muted">Memuat data...</p>
                                </td>
                            </tr>
                        `);
                    },
                    success: function(res) {
                        if (res && Array.isArray(res.data)) {
                            allBarang = res.data;
                            applyFilters();
                        } else {
                            $('#wspTable tbody').html(
                                '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td></tr>'
                            );
                        }
                    },
                    error: function(err) {
                        console.error(err);
                        $('#wspTable tbody').html(`
                            <tr>
                                <td colspan="6" class="text-center text-danger py-3">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> Gagal memuat data.
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            // Apply filtering locally
            function applyFilters() {
                const search = $('#searchInput').val().toLowerCase().trim();

                filteredBarang = allBarang.filter(item => {
                    let matchSearch = true;

                    if (search) {
                        const mid = (item.mid_barang || '').toString().toLowerCase();
                        const name = (item.nama_barang || '').toLowerCase();
                        matchSearch = mid.includes(search) || name.includes(search);
                    }

                    return matchSearch;
                });

                currentPage = 1;
                renderTable();
            }

            // Render table rows
            function renderTable() {
                const tbody = $('#wspTable tbody');
                tbody.empty();

                if (filteredBarang.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada data yang cocok dengan pencarian.
                            </td>
                        </tr>
                    `);
                    updatePaginationInfo(0, 0, 0);
                    renderPagination();
                    return;
                }

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, filteredBarang.length);
                const pageData = filteredBarang.slice(startIndex, endIndex);

                const status = $('#statusFilter').val();

                pageData.forEach((row, index) => {
                    let actionBtn = '';
                    if (status === 'trashed') {
                        @if (Session::get('jabatan') !== 'operator')
                            actionBtn = `
                                <button class="btn btn-sm btn-success restore-btn" data-id="${row.id}" title="Restore">
                                    <i class="mdi mdi-restore me-1"></i>Restore
                                </button>
                                <button class="btn btn-sm btn-danger force-delete-btn" data-id="${row.id}" title="Hapus Permanen">
                                    <i class="mdi mdi-delete-forever me-1"></i>Hapus
                                </button>
                            `;
                        @endif
                    } else {
                        actionBtn = `
                            <button class="btn btn-sm btn-primary detail-btn" data-id="${row.id}" title="Detail Data">
                                <i class="mdi mdi-eye me-1"></i>Detail
                            </button>
                        `;
                        @if (Session::get('jabatan') !== 'operator')
                            actionBtn += `
                                <button class="btn btn-sm btn-info edit-btn" data-id="${row.id}" title="Edit Data">
                                    <i class="mdi mdi-pencil me-1"></i>Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Delete Data">
                                    <i class="mdi mdi-delete me-1"></i>Delete
                                </button>
                            `;
                        @endif
                    }

                    tbody.append(`
                        <tr>
                            <td class="text-center">${startIndex + index + 1}</td>
                            <td><strong>${row.mid_barang || '-'}</strong></td>
                            <td>${row.nama_barang || '-'}</td>
                            <td>${row.uom || '-'}</td>
                            <td>${Number(row.qty_pallet || 1).toLocaleString('id-ID', {
                                    maximumFractionDigits: 10
                                })}
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    ${actionBtn}
                                </div>
                            </td>
                        </tr>
                    `);
                });

                updatePaginationInfo(startIndex + 1, endIndex, filteredBarang.length);
                renderPagination();
            }

            function updatePaginationInfo(from, to, total) {
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#totalRecords').text(total);
            }

            function renderPagination() {
                const totalPages = Math.ceil(filteredBarang.length / itemsPerPage);
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

            window.changePage = function(page) {
                const totalPages = Math.ceil(filteredBarang.length / itemsPerPage);
                if (page < 1 || page > totalPages) return;
                currentPage = page;
                renderTable();
            }

            // Listeners
            $('#statusFilter').change(function() {
                loadBarang();
            });

            $('#searchInput').on('input', function() {
                applyFilters();
            });

            // Image previews
            $("#image").change(function() {
                let file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $("#imagePreview").attr("src", e.target.result).show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $("#imagePreview").hide().attr("src", "");
                }
            });

            $("#imageEdit").change(function() {
                let file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $("#imagePreviewEdit").attr("src", e.target.result).show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $("#imagePreviewEdit").hide().attr("src", "");
                }
            });

            // Form registrasi submit
            $('#formRegistrasiBarang').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let formData = new FormData(this);

                $.ajax({
                    url: `/master/wsp/store/barang`,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        form.find('button[type="submit"]')
                            .prop('disabled', true)
                            .html('<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...');
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: response.message || 'Barang berhasil ditambahkan.'
                        });

                        form[0].reset();
                        form.find('input[type="file"]').val('');
                        $('#imagePreview').hide().attr('src', '');
                        $('#modalRegistrasi').modal('hide');
                        loadBarang();
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        let errorMsg = 'Terjadi kesalahan tak terduga.';
                        let icon = 'error';
                        let title = 'Error';

                        if (xhr.status === 422 && res?.errors) {
                            errorMsg = Object.values(res.errors).flat().join('<br>');
                            icon = 'warning';
                            title = 'Perhatian!';
                        } else if (res?.message) {
                            errorMsg = res.message;
                        }

                        Swal.fire({
                            icon: icon,
                            title: title,
                            html: errorMsg
                        });
                    },
                    complete: function() {
                        form.find('button[type="submit"]').prop('disabled', false).html(
                            'Simpan');
                    }
                });
            });

            // Edit button click event
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `/api/wsp/show/barang/${id}`,
                    type: 'GET',
                    success: function(res) {
                        $('#editModal').modal('show');
                        $('#editId').val(res.data.id);
                        $('#midBarangEdit').val(res.data.mid_barang);
                        $('#namaBarangEdit').val(res.data.nama_barang);
                        $('#uomEdit').val(res.data.uom);
                        $('#qtyPalletEdit').val(res.data.qty_pallet);

                        if (res.data.image) {
                            $('#imagePreviewEdit')
                                .attr('src', `{{ asset('storage/') }}/${res.data.image}`)
                                .show();
                        } else {
                            $('#imagePreviewEdit').hide().attr('src', '');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memuat data barang');
                    }
                });
            });

            // Handle edit submit
            $('#editForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#editId').val();
                const formData = new FormData(this);

                $.ajax({
                    url: `/master/wsp/update/barang/` + id,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    success: function(res) {
                        toastr.success(res.message || 'Data barang berhasil diperbarui');
                        $('#editModal').modal('hide');
                        $('#editForm')[0].reset();
                        loadBarang();
                    },
                    error: function(err) {
                        let errorMsg = 'There was an error updating the data.';
                        if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire('Error!', errorMsg, 'error');
                    }
                });
            });

            // Delete button click
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/wsp/delete/barang/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                toastr.success(res.message || 'Data berhasil dihapus');
                                loadBarang();
                            },
                            error: function(xhr) {
                                toastr.error('Gagal menghapus data');
                            }
                        });
                    }
                });
            });

            // Restore Barang
            $(document).on('click', '.restore-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Restore Barang?',
                    text: 'Barang ini akan diaktifkan kembali.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Restore!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/wsp/restore/barang/${id}`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Restored!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                loadBarang();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message ??
                                        'Terjadi kesalahan saat merestore data'
                                });
                            }
                        });
                    }
                });
            });

            // Force Delete Barang
            $(document).on('click', '.force-delete-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Permanen?',
                    text: 'Data yang dihapus permanen tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus Permanen!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/wsp/force-delete/barang/${id}`,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                loadBarang();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message ??
                                        'Terjadi kesalahan saat menghapus permanen'
                                });
                            }
                        });
                    }
                });
            });

            // Tombol Upload diklik
            $('#btnUpload').on('click', function(e) {
                e.preventDefault();

                let form = $('#formImport')[0];
                let fileInput = $('#fileImport').val();

                if (!fileInput) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File belum dipilih!',
                        text: 'Silakan pilih file terlebih dahulu sebelum mengunggah.'
                    });
                    return;
                }

                let formData = new FormData(form);

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#btnUpload')
                            .prop('disabled', true)
                            .html('<i class="mdi mdi-loading mdi-spin"></i> Mengunggah...');
                    },
                    success: function(response) {
                        const {
                            status,
                            message,
                            errors = []
                        } = response;

                        if (status === true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: message || 'Import selesai tanpa error.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            let errorListHtml = errors.length > 0 ?
                                errors.map(err => {
                                    if (typeof err === 'object') {
                                        return `<li>Baris ${err.baris}: ${err.error}</li>`;
                                    }
                                    return `<li>${err}</li>`;
                                }).join('') :
                                '<li>Tidak ada rincian error.</li>';

                            Swal.fire({
                                icon: 'warning',
                                title: 'Sebagian Gagal!',
                                html: `
                                    <p>${message || 'Import selesai dengan beberapa error.'}</p>
                                    <hr>
                                    <ul style="text-align:left; max-height: 200px; overflow-y: auto;">${errorListHtml}</ul>
                                `,
                                width: 600
                            });
                        }

                        $('#modalImport').modal('hide');
                        $('#formImport')[0].reset();
                        loadBarang();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat import.'
                        });
                    },
                    complete: function() {
                        $('#btnUpload').prop('disabled', false).html(
                            '<i class="mdi mdi-upload"></i> Upload');
                    }
                });
            });

            // Detail button click
            $(document).on('click', '.detail-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `/api/wsp/show/barang/${id}`,
                    type: 'GET',
                    success: function(res) {
                        $('#detailModal').modal('show');
                        $('#detailMid').text(res.data.mid_barang);
                        $('#detailNama').text(res.data.nama_barang);
                        $('#detailUom').text(res.data.uom);
                        $('#detailQtyPallet').text(res.data.qty_pallet || '1');
                        if (res.data.image) {
                            $('#detailImage')
                                .attr('src', `{{ asset('storage/') }}/${res.data.image}`)
                                .show();
                        } else {
                            $('#detailImage').hide();
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memuat data barang');
                    }
                });
            });
        });
    </script>
@endsection
