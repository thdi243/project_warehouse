@extends('layouts.app')

@section('styles')
    <style>
        #user-table td.text-wrap {
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
            hyphens: auto;
            max-width: 280px;
        }

        /* Override nowrap bawaan badge Velzon/Bootstrap */
        #user-table .badge {
            white-space: normal !important;
            word-break: break-word !important;
            display: inline-block !important;
            max-width: 100% !important;
            line-height: 1.4;
            padding: 0.4em 0.8em;
        }

        /* Kalau badge terlalu panjang, biar rapi di mobile */
        @media (max-width: 768px) {
            #user-table td.text-wrap {
                max-width: 180px;
                font-size: 0.85rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Manajemen User & Permission</h4>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Daftar User -->
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0">Daftar User</h5>

                    <!-- Search Bar -->
                    <div class="input-group" style="max-width: 350px;">
                        <input type="text" id="search-user" class="form-control"
                            placeholder="Cari nama, username atau nik...">
                        <button class="btn btn-outline-secondary" type="button" id="btn-clear-search">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle wrap" id="user-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Jabatan</th>
                                    <th class="text-center">Permission Saat Ini</th>
                                    <th style="width: 140px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="user-tbody">
                                <tr>
                                    <td colspan="5" class="text-center py-4">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="card-footer border-top-0 pt-3">
                    <nav aria-label="User pagination" id="pagination-container">
                        <!-- Pagination dimuat via AJAX -->
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Assign Permission -->
    <div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissionModalLabel">Set Permission User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="permission-form">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <div class="mb-4 d-flex flex-column flex-sm-row gap-2 align-items-start align-items-sm-center">
                            <!-- Search tetap full width -->
                            <div class="flex-grow-1 w-100 w-sm-auto">
                                <input type="text" id="search-perm" class="form-control"
                                    placeholder="Cari nama permission...">
                            </div>

                            <!-- Tombol Check All & Uncheck All di sebelah kanan (desktop) atau bawah (mobile) -->
                            <div class="d-flex gap-2 w-100 w-sm-auto">
                                <button type="button" id="check-all" class="btn btn-outline-success flex-fill">
                                    <i class="mdi mdi-check-all me-1"></i> Check All
                                </button>
                                <button type="button" id="uncheck-all" class="btn btn-outline-danger flex-fill">
                                    <i class="mdi mdi-close me-1"></i> Uncheck All
                                </button>
                            </div>
                        </div>
                        <div class="row g-3 overflow-auto" id="permission-checkboxes">
                            <!-- Checkbox akan di-load via AJAX -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="permission-form" class="btn btn-primary" id="btn-submit">Simpan</button>
                    <div class="spinner-border text-primary ms-2 d-none" id="loading" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal View Permissions -->
    <div class="modal fade" id="viewPermissionsModal" tabindex="-1" aria-labelledby="viewPermissionsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title" id="viewPermissionsModalLabel">Daftar Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <h6 class="text-muted fw-normal" id="view-perm-user-name">User Name</h6>
                    </div>
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-sm table-striped table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Nama Permission</th>
                                </tr>
                            </thead>
                            <tbody id="view-permissions-tbody">
                                <!-- Data dimuat via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadUsers();

            // Fungsi load data user via AJAX
            function loadUsers(query = '', page = 1) {
                $('#table-loading').removeClass('d-none');

                let url = "{{ route('admin.permissions.users.data') }}?query=" + encodeURIComponent(query) +
                    "&page=" + page;

                const currentUserId = parseInt($('meta[name="current-user-id"]').attr('content')) || 0;

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(res) {
                        let tbodyHtml = '';

                        if (res.users && res.users.length > 0) {
                            res.users.forEach(function(user, index) {
                                let globalIndex = index + 1 + (res.current_page - 1) * res
                                    .per_page;

                                let permsData = JSON.stringify(user.permissions || []);
                                let permissionsBadge = user.permissions && user.permissions
                                    .length > 0 ?
                                    `<button class="btn btn-sm btn-soft-info btn-view-permissions" 
                                        data-permissions='${permsData}' 
                                        data-name="${user.nama_lengkap || user.username}">
                                        <i class="mdi mdi-eye me-1"></i> Lihat (${user.permissions.length})
                                    </button>` :
                                    `<span class="badge badge-soft-danger">Nothing</span>`;

                                // Cek apakah user ini adalah admin
                                const isAdmin = (user.jabatan || '').toLowerCase() ===
                                    'admin' ||
                                    (user.jabatan || '').toLowerCase() === 'super-admin';

                                // Tombol disabled hanya jika: 
                                // - user adalah admin/super-admin DAN bukan user login sendiri
                                const shouldDisable = isAdmin && (user.id !== currentUserId);

                                const btnDisabled = shouldDisable ? 'disabled' : '';
                                const btnClassDisabled = shouldDisable ?
                                    'opacity-50 cursor-not-allowed' : '';

                                tbodyHtml += `
                        <tr data-id="${user.id}">
                            <td class="text-center">${globalIndex}</td>
                            <td>${user.nama_lengkap || user.username}</td>
                            <td>${user.nik || '-'}</td>
                            <td>${user.jabatan || '-'}</td>
                            <td class="text-center" id="permissions-${user.id}">${permissionsBadge}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary btn-atur-permission ${btnClassDisabled}" 
                                    data-id="${user.id}" ${btnDisabled}>
                                    <i class="mdi mdi-pencil me-2"></i>Set Permission
                                </button>
                            </td>
                        </tr>
                    `;
                            });
                        } else {
                            tbodyHtml =
                                '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada user ditemukan.</td></tr>';
                        }

                        $('#user-tbody').html(tbodyHtml);
                        $('#pagination-container').html(res.links || '');

                        $('#table-loading').addClass('d-none');
                    },
                    error: function() {
                        $('#user-tbody').html(
                            '<tr><td colspan="6" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>'
                        );
                        $('#table-loading').addClass('d-none');
                    }
                });
            }

            // Fungsi View Permissions
            $(document).on('click', '.btn-view-permissions', function(e) {
                e.preventDefault();
                const perms = $(this).data('permissions');
                const name = $(this).data('name');
                let html = '';

                $('#view-perm-user-name').text(name);

                if (Array.isArray(perms) && perms.length > 0) {
                    perms.forEach((p, i) => {
                        html += `<tr>
                            <td class="text-center text-muted small">${i+1}</td>
                            <td class="fw-medium">${p}</td>
                        </tr>`;
                    });
                } else {
                    html =
                        '<tr><td colspan="2" class="text-center py-3 text-muted text-italic">Tidak ada permission yang diberikan.</td></tr>';
                }

                $('#view-permissions-tbody').html(html);

                // Coba buka modal dengan bootstrap instance jika jQuery plugin gagal
                try {
                    let modalEl = document.getElementById('viewPermissionsModal');
                    let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                } catch (err) {
                    console.error("Bootstrap Modal Error:", err);
                    $('#viewPermissionsModal').modal('show');
                }
            });

            // Search real-time (debounce)
            let searchTimeout;
            $('#search-user').on('input', function() {
                clearTimeout(searchTimeout);
                let query = $(this).val().trim();

                searchTimeout = setTimeout(() => {
                    loadUsers(query);
                }, 400);
            });

            // Clear search
            $('#btn-clear-search').click(function() {
                $('#search-user').val('').trigger('input');
            });

            // Pagination click
            $(document).on('click', '#pagination-container a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                let params = new URLSearchParams(url.split('?')[1] || '');
                let page = params.get('page') || 1;
                let query = $('#search-user').val().trim();
                loadUsers(query, page);
            });

            // Buka modal & load checkbox permission
            $(document).on('click', '.btn-atur-permission', function() {
                let userId = $(this).data('id');

                $.get("{{ route('admin.permissions.users.get', ':id') }}".replace(':id', userId), function(
                    response) {
                    $('#permissionModalLabel').text('Set Permission untuk: ' + response.user.name +
                        ' (' + response.user.jabatan + ')');

                    let checkboxes = '';
                    response.permissions.forEach(function(perm) {
                        let checked = perm.checked ? 'checked' : '';
                        checkboxes += `
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" 
                                           value="${perm.id}" id="perm-${perm.id}" ${checked}>
                                    <label class="form-check-label" for="perm-${perm.id}">
                                        ${perm.name}
                                        <small class="text-muted">(${perm.description})</small>
                                    </label>
                                </div>
                            </div>
                        `;
                    });

                    $('#permission-checkboxes').html(checkboxes);
                    $('#permission-form').data('user-id', userId); // simpan user id untuk update
                    $('#permissionModal').modal('show');
                }).fail(function() {
                    Swal.fire('Error', 'Gagal memuat permission user', 'error');
                });
            });

            // Saat modal dibuka (setelah load checkboxes)
            $('#permissionModal').on('shown.bs.modal', function() {
                // Search permission
                $('#search-perm').on('input', function() {
                    let val = $(this).val().toLowerCase();
                    $('#permission-checkboxes .col-md-4').each(function() {
                        let label = $(this).find('label').text().toLowerCase();
                        $(this).toggle(label.includes(val));
                    });
                });

                $('#check-all').click(() => $('#permission-checkboxes input[type="checkbox"]').prop(
                    'checked', true));
                $('#uncheck-all').click(() => $('#permission-checkboxes input[type="checkbox"]').prop(
                    'checked', false));
            });

            // Check All / Uncheck All
            $('#check-all').click(function() {
                $('#permission-checkboxes input[type="checkbox"]').prop('checked', true);
            });

            $('#uncheck-all').click(function() {
                $('#permission-checkboxes input[type="checkbox"]').prop('checked', false);
            });

            // Submit form AJAX
            $('#permission-form').submit(function(e) {
                e.preventDefault();
                $('#loading').removeClass('d-none');
                $('#btn-submit').prop('disabled', true);

                let userId = $(this).data('user-id');
                let url = "{{ route('admin.permissions.users.update', ':user') }}".replace(':user',
                    userId);

                $.ajax({
                    url: url,
                    method: 'POST', // selalu POST, spoof dengan _method=PUT
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                title: 'Sukses!',
                                text: res.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Update summary permission di tabel tanpa reload
                            loadUsers('', 1);

                            $('#permissionModal').modal('hide');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan', 'error');
                    },
                    complete: function() {
                        $('#loading').addClass('d-none');
                        $('#btn-submit').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
