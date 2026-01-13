@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Manajemen Permission</h4>
                <button id="btn-add-new" class="btn btn-primary">+ Tambah Permission Baru</button>
            </div>

            <!-- Tabel Daftar -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Permission</h5>
                    <div id="table-loading" class="spinner-border spinner-border-sm text-primary d-none" role="status">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle" id="permission-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Nama Permission</th>
                                    <th>Deskripsi</th>
                                    <th style="width: 180px;">Dibuat</th>
                                    <th style="width: 140px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="permission-tbody">
                                <!-- Data dimuat via AJAX -->
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination di luar table, lebih aman & responsive -->
                <div class="card-footer bg-white border-top-0 pt-3">
                    <nav aria-label="User pagination" id="pagination-container">
                        <!-- Pagination dimuat via AJAX -->
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Permission -->
    <div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissionModalLabel">Tambah Permission Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="permission-form">
                        @csrf
                        <input type="hidden" name="id" id="permission_id">
                        <input type="hidden" name="_method" id="form_method" value="POST">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Permission <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required autofocus>
                                <div class="invalid-feedback" id="name-error"></div>
                                <small class="text-muted d-block">(unik, lowercase-with-dash)</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Deskripsi</label>
                                <input type="text" name="description" id="description" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="permission-form" class="btn btn-success" id="btn-submit">
                        Simpan
                    </button>
                    <div class="spinner-border text-primary ms-2 d-none" id="loading" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadTable();

            function loadTable(page = 1) {
                $('#table-loading').removeClass('d-none');

                let url = "{{ route('admin.permissions.data') }}?page=" + page;

                $.get(url, function(response) {
                    let tbody = $('#permission-tbody');
                    tbody.empty();

                    if (response.data && Array.isArray(response.data) && response.data.length > 0) {
                        response.data.forEach(function(item, index) {
                            let globalIndex = index + 1 + (response.current_page - 1) * response
                                .per_page;

                            let row = `
                                <tr data-id="${item.id}">
                                    <td class="text-center">${globalIndex}</td>
                                    <td><code>${item.name}</code></td>
                                    <td>${item.description || '-'}</td>
                                    <td>${new Date(item.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info btn-edit">Edit</button>
                                        <button class="btn btn-sm btn-danger btn-delete">Hapus</button>
                                    </td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    } else {
                        tbody.html(
                            '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data permission.</td></tr>'
                        );
                    }

                    // Render pagination HTML dari response.links
                    $('#pagination-container').html(response.links ||
                        '<p class="text-center text-muted">Tidak ada halaman lain.</p>');

                    $('#table-loading').addClass('d-none');
                }).fail(function(xhr, status, error) {
                    console.log(xhr.responseText); // debug kalau error
                    $('#permission-tbody').html(
                        '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>'
                    );
                    $('#table-loading').addClass('d-none');
                    Swal.fire('Error', 'Gagal memuat data tabel', 'error');
                });
            }

            // Intercept klik pagination (karena links Laravel pakai <a href>)
            $(document).on('click', '#pagination-container a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                if (url) {
                    let page = new URLSearchParams(url.split('?')[1]).get('page') || 1;
                    loadTable(page);
                }
            });

            // Reset form ke mode create
            function resetForm() {
                $('#permission-form')[0].reset();
                $('#permission_id, #form_method').val('');
                $('#permissionModalLabel').text('Tambah Permission Baru');
                $('#btn-submit').text('Simpan');
                $('.invalid-feedback').text('').removeClass('d-block');
            }

            // Buka modal tambah baru
            $('#btn-add-new').click(function() {
                resetForm();
                $('#permissionModal').modal('show');
            });

            // Submit form via AJAX
            $('#permission-form').submit(function(e) {
                e.preventDefault();
                $('#loading').removeClass('d-none');
                $('#btn-submit').prop('disabled', true);

                let id = $('#permission_id').val();
                let url = id ?
                    '{{ route('admin.permissions.update', ':id') }}'.replace(':id', id) :
                    '{{ route('admin.permissions.store') }}';

                // Selalu pakai POST, spoof dengan _method
                let ajaxMethod = 'POST';

                $.ajax({
                    url: url,
                    method: ajaxMethod,
                    data: $(this).serialize(), // sudah include _token & _method
                    success: function(res) {
                        Swal.fire({
                            title: 'Sukses!',
                            text: res.message || 'Permission berhasil disimpan!',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#permissionModal').modal('hide');
                        loadTable(1); // reload tabel langsung
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors || {};
                        $('.invalid-feedback').text('').removeClass('d-block');
                        $.each(errors, function(key, val) {
                            $('#' + key + '-error').text(val[0]).addClass('d-block');
                        });
                        Swal.fire('Gagal', 'Periksa input anda', 'error');
                    },
                    complete: function() {
                        $('#loading').addClass('d-none');
                        $('#btn-submit').prop('disabled', false);
                    }
                });
            });

            // Edit: load data ke modal
            $(document).on('click', '.btn-edit', function() {
                let id = $(this).closest('tr').data('id');

                $.get("{{ route('admin.permissions.edit', ':id') }}".replace(':id', id), function(data) {
                    $('#permission_id').val(data.id);
                    $('#form_method').val('PUT'); // <-- GANTI JADI 'PUT' (bukan POST!)
                    $('#name').val(data.name);
                    $('#description').val(data.description);
                    $('#permissionModalLabel').text('Edit Permission: ' + data.name);
                    $('#btn-submit').text('Update');
                    $('#permissionModal').modal('show');
                }).fail(function() {
                    Swal.fire('Error', 'Gagal memuat data permission', 'error');
                });
            });

            // Hapus tetap sama (dengan reload tabel setelah sukses)
            $(document).on('click', '.btn-delete', function() {
                let id = $(this).closest('tr').data('id');

                Swal.fire({
                    title: 'Yakin hapus?',
                    text: "Permission ini akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.permissions.destroy', ':id') }}".replace(
                                ':id', id),
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function() {
                                Swal.fire('Terhapus!', 'Permission dihapus.',
                                    'success');
                                loadTable(1);
                            },
                            error: function() {
                                Swal.fire('Gagal', 'Terjadi kesalahan', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
