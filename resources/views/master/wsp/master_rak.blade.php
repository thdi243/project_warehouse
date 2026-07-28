@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Master Rack Spareparts</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">RackMan</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Master</a></li>
                                <li class="breadcrumb-item active">Rack</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Data Rak</h4>
                            <div class="d-flex gap-2">
                                <a href="{{ route('wsp.rak.download-template') }}" class="btn btn-success">
                                    <i class="mdi mdi-download"></i> Download Template
                                </a>
                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalUpload">
                                    <i class="mdi mdi-upload"></i> Upload Excel
                                </button>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrasi">
                                    <i class="mdi mdi-plus"></i> Tambah Rack
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <select id="filterArea" class="form-select">
                                        <option value="">-- All Area --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filterNama" class="form-select">
                                        <option value="">-- All Nama --</option>
                                    </select>
                                </div>
                            </div>
                            <table class="table table-striped table-hover text-nowrap" id="wspRakTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Plant</th>
                                        <th>S Loc</th>
                                        <th>Area Rak</th>
                                        <th>Nama Rak</th>
                                        <th>Kolom Rak</th>
                                        <th>Level Rak</th>
                                        <th>Box Rak</th>
                                        @if (Session::get('jabatan') !== 'operator')
                                            <th data-orderable="false" class="text-center">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Di isi oleh js --}}
                                </tbody>
                            </table>

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
        </div>
    </div>

    {{-- Modal Registasi Rack --}}
    <div class="modal fade" id="modalRegistrasi" tabindex="-1" aria-labelledby="modalRegistrasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRegistrasiLabel">Registrasi Rack</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formRegistrasiRack" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <label for="plant" class="form-label"> <span class="text-danger">*</span> Plant</label>
                                <input type="text" class="form-control" id="plant" name="plant"
                                    placeholder="Cth: 1006" required>
                            </div>

                            <div class="col-md-6">
                                <label for="sLoc" class="form-label"><span class="text-danger">*</span> S Loc</label>
                                <input type="text" class="form-control" id="sLoc" name="sLoc"
                                    placeholder="Cth: G001" required>
                            </div>

                            <div class="col-md-6">
                                <label for="areaRak" class="form-label"><span class="text-danger">*</span> Area Rak</label>
                                <input type="text" class="form-control" id="areaRak" name="areaRak"
                                    placeholder="Cth: FL1" required>
                            </div>

                            <div class="col-md-6">
                                <label for="namaRak" class="form-label"><span class="text-danger">*</span> Nama
                                    Rak</label>
                                <input type="text" class="form-control" id="namaRak" name="namaRak"
                                    placeholder="Cth: A" required>
                            </div>

                            <div class="col-md-6">
                                <label for="kolomRak" class="form-label">Kolom Rak</label>
                                <input type="number" class="form-control" id="kolomRak" name="kolomRak"
                                    placeholder="Cth: 1" min="1">
                            </div>

                            <div class="col-md-6">
                                <label for="levelRak" class="form-label">Level Rak</label>
                                <input type="number" class="form-control" id="levelRak" name="levelRak"
                                    placeholder="Cth: 1" min="1">
                            </div>

                            <div class="col-md-6">
                                <label for="boxRak" class="form-label">Box Rak</label>
                                <input type="text" class="form-control" id="boxRak" name="boxRak"
                                    placeholder="Cth: 000">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit" id="simpanBtn">Simpan</button>
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal"
                            id="cancelBtn">Cancel</button>
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
                    <h5 class="modal-title">Update Data Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <label for="plantEdit" class="form-label"><span class="text-danger">*</span>
                                    Plant</label>
                                <input type="text" class="form-control" id="plantEdit" name="plantEdit" required>
                            </div>
                            <div class="col-md-6">
                                <label for="sLocEdit" class="form-label"><span class="text-danger">*</span> S Loc</label>
                                <input type="text" class="form-control" id="sLocEdit" name="sLocEdit" required>
                            </div>
                            <div class="col-md-6">
                                <label for="areaRakEdit" class="form-label"><span class="text-danger">*</span> Area
                                    Rak</label>
                                <input type="text" class="form-control" id="areaRakEdit" name="areaRakEdit" required>
                            </div>
                            <div class="col-md-6">
                                <label for="namaRakEdit" class="form-label"><span class="text-danger">*</span> Nama
                                    Rak</label>
                                <input type="text" class="form-control" id="namaRakEdit" name="namaRakEdit" required>
                            </div>
                            <div class="col-md-6">
                                <label for="kolomRakEdit" class="form-label">Kolom Rak</label>
                                <input type="number" class="form-control" id="kolomRakEdit" name="kolomRakEdit"
                                    min="1">
                            </div>
                            <div class="col-md-6">
                                <label for="levelRakEdit" class="form-label">Level Rak</label>
                                <input type="number" class="form-control" id="levelRakEdit" name="levelRakEdit"
                                    min="1">
                            </div>
                            <div class="col-md-6">
                                <label for="boxRakEdit" class="form-label">Box Rak</label>
                                <input type="text" class="form-control" id="boxRakEdit" name="boxRakEdit">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Upload Excel --}}
    <div class="modal fade" id="modalUpload" tabindex="-1" aria-labelledby="modalUploadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUploadLabel">Upload Excel Data Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formUploadRak" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="excelFile" class="form-label">File Excel (.xlsx, .xls)</label>
                            <input class="form-control" type="file" id="excelFile" name="file"
                                accept=".xlsx, .xls" required>
                        </div>
                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="mdi mdi-information-outline me-1"></i>
                                Pastikan format kolom Excel sesuai template: <br>
                                <strong>A: Plant | B: S Loc | C: Area Rak | D: Nama Rak | E: Kolom | F: Level | G:
                                    Box</strong>
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="btnProsesUpload">Upload</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let allRacks = [];
            let filteredRacks = [];
            let currentPage = 1;
            const itemsPerPage = 10;

            getFilters();
            loadRacks();

            // Load data from backend
            function loadRacks() {
                $.ajax({
                    url: `/api/wsp/data/all/rak`,
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        $('#wspRakTable tbody').html(`
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
                        if (Array.isArray(res)) {
                            allRacks = res;
                            applyFilters();
                        } else {
                            $('#wspRakTable tbody').html(
                                '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td></tr>'
                            );
                        }
                    },
                    error: function(err) {
                        console.error(err);
                        $('#wspRakTable tbody').html(`
                        <tr>
                            <td colspan="6" class="text-center text-danger py-3">
                                <i class="mdi mdi-alert-circle-outline me-1"></i> Gagal memuat data.
                            </td>
                        </tr>
                    `);
                    }
                });
            }

            // Apply filtering
            function applyFilters() {
                const filterArea = $('#filterArea').val();
                const filterNama = $('#filterNama').val();

                filteredRacks = allRacks.filter(item => {
                    let matchArea = true;
                    let matchNama = true;

                    if (filterArea) {
                        matchArea = item.area_rak === filterArea;
                    }
                    if (filterNama) {
                        matchNama = item.nama_rak === filterNama;
                    }

                    return matchArea && matchNama;
                });

                currentPage = 1;
                renderTable();
            }

            // Render table rows
            function renderTable() {
                const tbody = $('#wspRakTable tbody');
                tbody.empty();

                if (filteredRacks.length === 0) {
                    tbody.html(`
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Tidak ada data yang cocok dengan filter.
                        </td>
                    </tr>
                `);
                    updatePaginationInfo(0, 0, 0);
                    renderPagination();
                    return;
                }

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, filteredRacks.length);
                const pageData = filteredRacks.slice(startIndex, endIndex);

                pageData.forEach((rak, index) => {
                    let actionBtn = '';
                    @if (Session::get('jabatan') !== 'operator')
                        actionBtn = `
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${rak.id}" title="Edit Data">
                            <i class="mdi mdi-pencil me-2"></i>Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${rak.id}" title="Delete Data">
                            <i class="mdi mdi-delete me-2"></i>Delete
                        </button>
                    `;
                    @endif

                    tbody.append(`
                    <tr>
                        <td class="text-center">${startIndex + index + 1}</td>
                        <td>${rak.plant || '-'}</td>
                        <td>${rak.s_loc || '-'}</td>
                        <td>${rak.area_rak || '-'}</td>
                        <td>${rak.nama_rak || '-'}</td>
                        <td>${rak.kolom_rak || '-'}</td>
                        <td>${rak.level_rak || '-'}</td>
                        <td>${rak.box_rak || '-'}</td>
                        @if (Session::get('jabatan') !== 'operator')
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    ${actionBtn}
                                </div>
                            </td>
                        @endif
                    </tr>
                `);
                });

                updatePaginationInfo(startIndex + 1, endIndex, filteredRacks.length);
                renderPagination();
            }

            function updatePaginationInfo(from, to, total) {
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#totalRecords').text(total);
            }

            function renderPagination() {
                const totalPages = Math.ceil(filteredRacks.length / itemsPerPage);
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
                const totalPages = Math.ceil(filteredRacks.length / itemsPerPage);
                if (page < 1 || page > totalPages) return;
                currentPage = page;
                renderTable();
            }

            // Submit registrasi rack data
            $('#formRegistrasiRack').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: `/master/wsp/store/rak`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Rack berhasil ditambahkan!'
                        });
                        $('#formRegistrasiRack')[0].reset();
                        $('#modalRegistrasi').modal('hide');
                        loadRacks(); // reload data
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        let errorMsg = 'Terjadi kesalahan tak terduga.';
                        let icon = 'error';

                        if (res && res.message) {
                            errorMsg = res.message;
                            if (xhr.status === 422) {
                                icon = 'warning';
                            }
                        } else if (res && res.errors) {
                            errorMsg = Object.values(res.errors).flat().join('\n');
                            icon = 'warning';
                        }

                        Swal.fire({
                            icon: icon,
                            title: xhr.status === 422 ? 'Perhatian' : 'Error',
                            text: errorMsg
                        });
                    }
                });
            });

            // Edit button click event
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `/api/wsp/show/rak/${id}`,
                    method: 'GET',
                    success: function(response) {
                        const data = response.data;

                        $('#editId').val(data.id);
                        $('#plantEdit').val(data.plant);
                        $('#sLocEdit').val(data.s_loc);
                        $('#areaRakEdit').val(data.area_rak);
                        $('#namaRakEdit').val(data.nama_rak);
                        $('#kolomRakEdit').val(data.kolom_rak);
                        $('#levelRakEdit').val(data.level_rak);
                        $('#boxRakEdit').val(data.box_rak);

                        // buka modal
                        $('#editModal').modal('show');
                    },
                    error: function(err) {
                        console.error("Error fetching data:", err);
                        Swal.fire('Error!', 'There was an error fetching the data.', 'error');
                    }
                });
            });

            // Submit form edit
            $('#editForm').submit(function(e) {
                e.preventDefault();

                const id = $('#editId').val();
                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: `/master/wsp/update/rak/` + id,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Success!', 'Data updated successfully.', 'success');
                        $('#editModal').modal('hide');
                        loadRacks(); // reload data
                    },
                    error: function(err) {
                        console.error("Error updating data:", err);
                        let errorMsg = 'There was an error updating the data.';
                        if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire('Error!', errorMsg, 'error');
                    }
                });
            });

            // Delete button
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/master/wsp/delete/rak/${id}`,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                );
                                loadRacks(); // reload data
                            },
                            error: function(err) {
                                console.error("Error deleting data:", err);
                                Swal.fire(
                                    'Error!',
                                    'There was an error deleting the data.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Filtering
            function getFilters() {
                $('#filterArea').empty().append('<option value="">-- All Area --</option>');
                $('#filterNama').empty().append('<option value="">-- All Nama --</option>');

                $.get("/api/wsp/rak/filters", function(res) {
                    res.area.forEach(function(item) {
                        $('#filterArea').append(`<option value="${item}">${item}</option>`);
                    });

                    res.nama.forEach(function(item) {
                        $('#filterNama').append(`<option value="${item}">${item}</option>`);
                    });
                });
            }

            // Apply filter
            $('#filterArea, #filterNama').on('change', function() {
                applyFilters();
            });

            // Submit upload excel
            $('#formUploadRak').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $.ajax({
                    url: '{{ route('wsp.rak.upload') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $('#btnProsesUpload').prop('disabled', true).text('Uploading...');
                    },
                    success: function(response) {
                        $('#btnProsesUpload').prop('disabled', false).text('Upload');
                        $('#excelFile').val('');
                        $('#modalUpload').modal('hide');

                        let message = response.message;
                        if (response.skipped && response.skipped.length > 0) {
                            message += '\n\nBeberapa baris dilewati:\n' + response.skipped.join(
                                '\n');
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Upload Selesai',
                            text: message
                        });

                        loadRacks(); // reload data table
                    },
                    error: function(xhr) {
                        $('#btnProsesUpload').prop('disabled', false).text('Upload');
                        let res = xhr.responseJSON;
                        let errorMsg = 'Terjadi kesalahan saat mengupload file.';

                        if (res && res.message) {
                            errorMsg = res.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Upload',
                            text: errorMsg
                        });
                    }
                });
            });
        });
    </script>
@endsection
