@extends('layouts.app')

@section('title', 'Stock Outgoing')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-3" data-aos="fade-down">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="fw-bold fs-3">Stock Outgoing</h3>
                        <p class="fw-normal fs-6">Kelola Stock Outgoing Anda Tiap Hari</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="justify-content-md-end">
                            <button type="button" class="btn btn-success btn-upload" id="btnUpload" data-bs-toggle="modal"
                                data-bs-target="#modalUpload">
                                <i class="mdi mdi-file-upload me-2"></i>
                                <span>Upload</span>
                            </button>

                            <button type="button" class="btn btn-primary btn-add" id="btnAdd"
                                data-bs-target="#modalForm" data-bs-toggle="modal">
                                <i class="mdi mdi-plus-circle"></i>
                                <span>Tambah Data</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="card shadow-sm" data-aos="fade-up">
                <div class="card-header bg-light py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                <i class="mdi mdi-table me-2"></i>Data outgoing per hari ini
                            </h5>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Cari MID barang...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderedless align-middle" id="IncomingTable">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th style="width: 60px;">NO</th>
                                    <th>UPLOADED BY</th>
                                    <th>MID</th>
                                    <th>NAMA BARANG</th>
                                    <th>S LOC</th>
                                    <th>UNIT</th>
                                    <th>MATERIAL DOC</th>
                                    <th>POSTING DATE</th>
                                    <th>QTY</th>
                                    <th>MVT</th>
                                    <th>VENDOR</th>
                                    <th>BATCH</th>
                                    <th style="width: 120px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">

                                <tr class="empty-state-row">
                                    <td colspan="16">
                                        <div class="empty-state text-center py-3">
                                            <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-2"></i>
                                            <h6 class="fw-bold">Belum Ada Data</h6>
                                            <p class="text-muted mb-0">
                                                Klik tombol <strong>"Upload"</strong> untuk menambahkan lokasi stok.
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
                            Menampilkan <strong id="showingFrom">0</strong> -
                            <strong id="showingTo">0</strong> dari
                            <strong id="totalRecords">0</strong> data
                        </div>

                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0" id="pagination">
                                <!-- Auto Generate -->
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal Upload --}}
    <div class="modal fade" id="modalUpload" tabindex="-1" aria-labelledby="modalUpload" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpload">Upload Incoming</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Upload Form -->
                    <form id="formUploadOutgoing" enctype="multipart/form-data">
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
                                <a href="{{ route('stock.move.outgoing.download') }}" target="_blank"
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

    <!-- Modal Add/Edit Incoming -->
    <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Outgoing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="formOutgoing">
                    <div class="modal-body">
                        <input type="hidden" id="outgoingId">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">MID</label>
                                <input type="number" id="mid" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" id="namaBarang" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">S Loc</label>
                                <input type="text" id="sLoc" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Unit</label>
                                <input type="text" id="unit" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Material Doc</label>
                                <input type="number" id="materialDoc" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Posting Date</label>
                                <input type="date" id="postDate" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qty</label>
                                <input type="number" id="qty" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mvt</label>
                                <input type="number" id="mvt" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Vendor</label>
                                <input type="text" id="vendor" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Batch</label>
                                <input type="number" id="batch" class="form-control">
                            </div>
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
            loadOutgoingData();

            let allOutoging = [];
            let filteredOutgoing = [];
            let currentPage = 1;
            const itemsPerPage = 10;

            // Ambil data dari backend
            function loadOutgoingData() {
                $.ajax({
                    url: "{{ url('api/wsp/outgoing/getData') }}",
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="16" class="text-center py-4">
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
                            allOutoging = res.data;
                            filteredOutgoing = allOutoging;
                            renderTable();
                        } else {
                            $('#tableBody').html(
                                '<tr><td colspan="16" class="text-center text-muted py-3">Tidak ada data.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="16" class="text-center text-danger py-3">
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

                if (filteredOutgoing.length === 0) {
                    tbody.html(`
                        <tr class="empty-state-row">
                            <td colspan="16">
                                <div class="empty-state text-center py-3">
                                    <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-3"></i>
                                    <h6 class="fw-semibold mb-1">Tidak Ada Data</h6>
                                    <p class="text-muted mb-0">Data yang Anda cari tidak ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    `);
                    updatePaginationInfo(0, 0, 0);
                    return;
                }

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, filteredOutgoing.length);
                const pageData = filteredOutgoing.slice(startIndex, endIndex);

                pageData.forEach((out, index) => {
                    tbody.append(`
                        <tr>
                            <td>${startIndex + index + 1}</td>
                            <td>${out.user.nama_lengkap}</td>
                            <td>${out.mid}</td>
                            <td>${out.nama_barang}</td>
                            <td>${out.s_loc ?? '-'}</td>
                            <td>${out.unit ?? '-'}</td>
                            <td>${out.material_doc ?? '-'}</td>
                            <td>${out.posting_date ?? '-'}</td>
                            <td>${out.qty ?? '-'}</td>
                            <td>${out.mvt ?? '-'}</td>
                            <td>${out.vendor ?? '-'}</td>
                            <td>${out.batch ?? '-'}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-info btn-edit" onclick="editSOH(${out.id})" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger btn-delete" onclick="deleteSOH(${out.id})" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                updatePaginationInfo(startIndex + 1, endIndex, filteredOutgoing.length);
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
                const totalPages = Math.ceil(filteredOutgoing.length / itemsPerPage);
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
                const totalPages = Math.ceil(filteredOutgoing.length / itemsPerPage);
                if (page < 1 || page > totalPages) return;
                currentPage = page;
                renderTable();
            }

            // Event search
            $('#searchInput').on('input', function() {
                const keyword = $(this).val().toLowerCase().trim();

                if (keyword === '') {
                    filteredOutgoing = allOutoging;
                } else {
                    filteredOutgoing = allOutoging.filter(item => {
                        const midMatch = item.mid && String(item.mid).toLowerCase().includes(
                            keyword);
                        const nameMatch = item.nama_barang && item.nama_barang.toLowerCase()
                            .includes(keyword);

                        return midMatch || nameMatch;
                    });
                }

                currentPage = 1;
                renderTable();
            });

            // Form Upload
            $('#formUploadOutgoing').submit(function(e) {
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
                    url: "{{ route('stock.move.outgoing.upload') }}",
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
                        $('#formUploadOutgoing')[0].reset();
                        $('#btnUploadSubmit').prop('disabled', false).text('Upload');
                        // reload data table / list setelah upload
                        loadOutgoingData();
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

            // submit add & edit form
            $('#formOutgoing').submit(function(e) {
                e.preventDefault();

                const id = $('#outgoingId').val();

                const payload = {
                    mid: $('#mid').val(),
                    nama_barang: $('#namaBarang').val(),
                    s_loc: $('#sLoc').val(),
                    unit: $('#unit').val(),
                    material_doc: $('#materialDoc').val(),
                    posting_date: $('#postDate').val(),
                    qty: $('#qty').val(),
                    mvt: $('#mvt').val(),
                    vendor: $('#vendor').val(),
                    batch: $('#batch').val(),
                };

                // Validasi sederhana
                if (!payload.mid) {
                    toastr.warning('MID wajib diisi!');
                    return;
                }

                const storeUrl = "{{ route('stock.move.outgoing.store') }}";
                const updateUrl = "{{ route('stock.move.outgoing.update', '') }}/" + id;

                const method = id ? 'PUT' : 'POST';
                const url = id ? updateUrl : storeUrl;

                $.ajax({
                    url: url,
                    type: method,
                    data: payload,
                    success: function(res) {
                        toastr.success(res.message || 'Data berhasil disimpan');
                        $('#modalForm').modal('hide');
                        loadOutgoingData(); // reload data
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

            $('#btnAdd').click(function() {
                $('#modalTitle').text('Tambah Outgoing');
                $('#formOutgoing')[0].reset();
                $('#outgoingId').val('');
                $('#modalForm').modal('show');
            });

            // Edit soh
            window.editSOH = function(id) {
                $.ajax({
                    url: "{{ route('stock.move.outgoing.show', '') }}/" + id,
                    type: 'GET',
                    success: function(res) {
                        const data = res.data;

                        $('#outgoingId').val(data.id);
                        $('#mid').val(data.mid);
                        $('#namaBarang').val(data.nama_barang);
                        $('#sLoc').val(data.s_loc);
                        $('#unit').val(data.unit);
                        $('#materialDoc').val(data.material_doc);
                        $('#postDate').val(data.posting_date);
                        $('#qty').val(data.qty);
                        $('#mvt').val(data.mvt);
                        $('#vendor').val(data.vendor);
                        $('#batch').val(data.batch);

                        $('#modalTitle').text('Edit Outgoing');
                        $('#modalForm').modal('show');
                    },
                    error: function(xhr) {
                        let msg = 'Gagal mengambil data outgoing';
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
                            url: "{{ route('stock.move.outgoing.delete', '') }}/" + id,
                            type: 'DELETE',
                            success: function(res) {
                                toastr.success(res.message || 'Data berhasil dihapus');
                                loadOutgoingData(); // reload tabel
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
        })
    </script>
@endsection
