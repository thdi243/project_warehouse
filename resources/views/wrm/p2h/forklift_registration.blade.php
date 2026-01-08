@extends('layouts.app')

@section('styles')
    <style>
        /* Custom CSS untuk responsif */
        @media (max-width: 768px) {
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .card-header {
                padding: 0.75rem;
            }

            .card-header .btn {
                font-size: 0.8rem;
                padding: 0.375rem 0.75rem;
            }

            /* Pastikan form tetap rapi di mobile */
            .modal-body .row>.col-md-6 {
                margin-bottom: 1rem;
            }
        }

        /* Perbaikan untuk tombol aksi */
        .btn-group {
            display: flex;
            flex-wrap: nowrap;
        }

        .btn-group .btn {
            border-radius: 0;
            margin-right: 1px;
        }

        .btn-group .btn:first-child {
            border-top-left-radius: 0.25rem;
            border-bottom-left-radius: 0.25rem;
        }

        .btn-group .btn:last-child {
            border-top-right-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
            margin-right: 0;
        }

        /* Responsive table wrapper */
        .table-responsive {
            border-radius: 0.375rem;
        }

        /* DataTables responsive styling */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        @media (max-width: 576px) {

            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                text-align: center;
                margin-bottom: 0.5rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-title-box">
                <h4 class="mb-0">📦 Forklift Registration & Assignment</h4>
            </div>

            {{-- Section: Tambah Forklift --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Tambah Forklift</h5>
                    <button type="submit" form="addForkliftForm" class="btn btn-primary">
                        <i class="mdi mdi-plus me-2"></i>Simpan</button>
                </div>
                <div class="card-body">
                    <form id="addForkliftForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Nomor Unit</label>
                                <input type="text" name="nomor_unit" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Departemen</label>
                                <select name="departemen" class="form-select" required>
                                    <option value="warehouse">Warehouse</option>
                                    <option value="produksi">Produksi</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Section</label>
                                <select name="section" class="form-select" required>
                                    <option value="warehouse_raw_material">Warehouse Raw Material</option>
                                    <option value="warehouse_finish_goods">Warehouse Finish Goods</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Deskripsi</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Section: Data Forklift --}}
            <div class="card">
                <div class="card-header">
                    <h5>Daftar Forklift Terdaftar</h5>
                </div>
                <div class="card-body">
                    <!-- Tambahkan wrapper untuk horizontal scroll di mobile -->
                    <div class="table-responsive">
                        <table id="forkliftTable" class="table table-bordered table-striped text-nowrap">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Departemen</th>
                                    <th>Section</th>
                                    <th>Deskripsi</th>
                                    <th>Operator 1</th>
                                    <th>Operator 2</th>
                                    <th>Operator 3</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- AJAX akan inject data di sini --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Assignment Operator --}}
    <div class="modal fade" id="assignmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="assignmentForm" class="modal-content">
                @csrf
                <input type="hidden" name="forklift_id" id="forkliftId">
                <div class="modal-header">
                    <h5 class="modal-title">🛠️ Assign Operator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Pilih Operator</label>
                        <select class="form-select" name="user_id" id="userSelect">
                            @foreach ($operators as $user)
                                <option value="{{ $user->id }}">{{ $user->nama_lengkap ?? $user->username }}
                                    ({{ $user->nik }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="operator" class="form-label">Tipe Assignment</label>
                        <select name="operator" class="form-select">
                            <option value="1">Operator 1</option>
                            <option value="2">Operator 2</option>
                            <option value="3">Operator 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editForkliftModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editForkliftForm" class="modal-content">
                @csrf
                @method('PUT') <!-- Tambahkan ini kalau pakai RESTful update -->
                <input type="hidden" name="forklift_id" id="editForkliftId">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Edit Forklift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3 px-3">
                    <div class="col-md-6">
                        <label class="form-label">Nomor Unit</label>
                        <input type="text" name="nomor_unit" id="editNomorUnit" class="form-control" required
                            maxlength="10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Departemen</label>
                        <select name="departemen" id="editDepartemen" class="form-select" required>
                            <option value="warehouse">Warehouse</option>
                            <option value="produksi">Produksi</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Section</label>
                        <select name="section" id="editSection" class="form-select" required>
                            <option value="warehouse_raw_material">Warehouse Raw Material</option>
                            <option value="warehouse_finish_goods">Warehouse Finish Goods</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" id="editDescription" class="form-control"
                            placeholder="Opsional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-content-save"></i> Update
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Cadangan -->
    <div class="modal fade" id="backupModal" tabindex="-1" aria-labelledby="backupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="backupModalLabel">Cadangan Forklift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" id="backupModalBody">
                    <p>Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="editAssignmentForm" class="modal-content">
                @csrf
                <input type="hidden" name="forklift_id" id="editAssignmentForkliftId">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAssignmentLabel">Edit Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Operator 1</label>
                        <select name="operator_1" id="operator_1" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operator 2</label>
                        <select name="operator_2" id="operator_2" class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operator 3</label>
                        <select name="operator_3" id="operator_3" class="form-select"></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadForkliftData();

            function loadForkliftData() {
                $('#loadingRow').show();
                $('#emptyState').addClass('d-none');
                $('#forkliftTable tbody tr:not(#loadingRow)').remove(); // clear rows kecuali loading

                $.get("{{ url('api/p2h/data/registration/forklift') }}")
                    .done(function(response) {
                        const data = response.data || response;

                        $('#loadingRow').hide();

                        if (data.length === 0) {
                            $('#emptyState').removeClass('d-none');
                            return;
                        }

                        let rows = '';

                        data.forEach(function(row) {
                            // Status badge
                            let statusBadge = 'bg-success';
                            let statusText = row.status.charAt(0).toUpperCase() + row.status.slice(1);
                            if (row.status === 'maintenance') statusBadge = 'bg-warning text-dark';
                            if (row.status === 'inactive') statusBadge = 'bg-danger';

                            // Operator 1 highlight
                            let op1 = row.operator_1 && row.operator_1 !== '-' ?
                                `<strong>${row.operator_1}</strong>` :
                                '<span class="text-danger fw-bold">Belum Ditentukan</span>';

                            let op2 = row.operator_2 && row.operator_2 !== '-' ? row.operator_2 :
                                '<em class="text-muted">-</em>';
                            let op3 = row.operator_3 && row.operator_3 !== '-' ? row.operator_3 :
                                '<em class="text-muted">-</em>';

                            // Deskripsi
                            let notes = row.notes && row.notes !== '-' ? row.notes :
                                '<em class="text-muted">-</em>';

                            // Section display
                            let sectionDisplay = row.section === 'warehouse_raw_material' ?
                                'Warehouse Raw Material' :
                                row.section === 'warehouse_finish_goods' ? 'Warehouse Finish Goods' :
                                row.section;

                            rows += `
                                <tr>
                                    <td class="text-center fw-bold">${row.nomor_unit.toUpperCase()}</td>
                                    <td class="text-center">
                                        <span class="badge ${statusBadge}">${statusText}</span>
                                    </td>
                                    <td class="text-center">${row.departemen.charAt(0).toUpperCase() + row.departemen.slice(1)}</td>
                                    <td class="text-center">${sectionDisplay}</td>
                                    <td>${notes}</td>
                                    <td class="text-center">${op1}</td>
                                    <td class="text-center">${op2}</td>
                                    <td class="text-center">${op3}</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-primary assign-btn" 
                                                    data-id="${row.id}" 
                                                    data-unit="${row.nomor_unit}" 
                                                    title="Assign Operator">
                                                <i class="mdi mdi-account-plus me-1"></i>Assign
                                            </button>
                                            <button class="btn btn-sm btn-info edit-assignment-btn" 
                                                    data-id="${row.id}" 
                                                    data-unit="${row.nomor_unit}" 
                                                    title="Edit Assignment">
                                                <i class="mdi mdi-account-edit me-1"></i>Edit Assign
                                            </button>
                                            <button class="btn btn-sm btn-warning edit-btn" 
                                                    data-id="${row.id}" 
                                                    title="Edit Forklift">
                                                <i class="mdi mdi-pencil me-1"></i>Edt
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-btn" 
                                                    data-id="${row.id}" 
                                                    data-unit="${row.nomor_unit}" 
                                                    title="Hapus">
                                                <i class="mdi mdi-delete me-1"></i>Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        $('#forkliftTable tbody').append(rows);
                    })
                    .fail(function() {
                        $('#loadingRow').hide();
                        Swal.fire('Error', 'Gagal memuat data forklift', 'error');
                    });
            }

            // Event handlers tetap sama seperti sebelumnya
            $('#forkliftTable').on('click', '.backup-detail-btn', function() {
                const forkliftId = $(this).data('id');
                const unit = $(this).data('unit');

                $.ajax({
                    url: "{{ url('api/p2h/backups/forklift') }}/" + forkliftId,
                    method: 'GET',
                    success: function(response) {
                        let list = '';
                        response.backups.forEach(op => {
                            list += `<li>${op.username} (${op.nik})</li>`;
                        });

                        $('#backupModalLabel').text(`Cadangan - ${unit}`);
                        $('#backupModalBody').html(`<ul>${list}</ul>`);
                        $('#backupModal').modal('show');
                    },
                    error: function() {
                        $('#backupModalBody').html(
                            '<p class="text-danger">Gagal memuat data cadangan.</p>'
                        );
                        $('#backupModal').modal('show');
                    }
                });
            });

            // Open modal assignment
            $(document).on('click', '.assign-btn', function() {
                $('#forkliftId').val($(this).data('id'));
                $('#assignmentModal').modal('show');
            });

            // Handle submit assignment
            $('#assignmentForm').on('submit', function(e) {
                e.preventDefault();

                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('Menyimpan...');

                $.post("{{ url('/p2h/store/forklift/assignment') }}", $(this).serialize())
                    .done(function(res) {
                        if (res.success) {
                            Swal.fire('Berhasil!', res.message, 'success');
                            // $('#forkliftTable').DataTable().ajax.reload(null, false);
                            loadForkliftData();
                            $('#assignmentModal').modal('hide');
                            $('#assignmentForm')[0].reset();
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal menyimpan', 'error');
                        }
                    })
                    .fail(function(xhr) {
                        let msg = 'Terjadi kesalahan server';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', msg, 'error');
                    })
                    .always(function() {
                        $btn.prop('disabled', false).text('Simpan');
                    });
            });

            // Forklift creation handler
            $('#addForkliftForm').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const $btn = $('#submitForkliftBtn');
                const formData = new FormData($form[0]); // Lebih reliable dari serialize()

                // Loading state
                $btn.prop('disabled', true).html(
                    '<i class="mdi mdi-loading mdi-spin me-2"></i>Menyimpan...');

                $.ajax({
                    url: "{{ url('api/p2h/store/forklift/registration') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadForkliftData();
                            $form[0].reset();
                            $('select').prop('selectedIndex', 0); // Reset dropdown
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: res.message || res.error ||
                                    'Forklift gagal disimpan',
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan server';
                        try {
                            const res = JSON.parse(xhr.responseText);
                            errorMsg = res.message || res.error || errorMsg;
                        } catch (e) {}

                        Swal.fire({
                            title: 'Error!',
                            text: errorMsg,
                            icon: 'error'
                        });
                    },
                    complete: function() {
                        // Reset button
                        $btn.prop('disabled', false).html(
                            '<i class="mdi mdi-plus me-2"></i>Simpan');
                    }
                });
            });

            // Open Edit Modal
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');

                $.get("{{ url('api/p2h/show/forklift') }}/" + id)
                    .done(function(res) {
                        const data = res.data || res;

                        $('#editForkliftId').val(data.id);
                        $('#editNomorUnit').val(data.nomor_unit);
                        $('#editDepartemen').val(data.departemen);
                        $('#editSection').val(data.section);
                        $('#editStatus').val(data.status);
                        $('#editDescription').val(data.description || '');

                        $('#editForkliftModal').modal('show');
                    })
                    .fail(function() {
                        Swal.fire('Error', 'Gagal memuat data forklift', 'error');
                    });
            });

            $('#editForkliftForm').on('submit', function(e) {
                e.preventDefault();

                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...');

                const id = $('#editForkliftId').val();
                const formData = $(this).serialize();

                $.ajax({
                    url: "{{ url('api/p2h/update/forklift') }}/" + id,
                    type: 'POST',
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadForkliftData();
                            // $('#forkliftTable').DataTable().ajax.reload(null, false);
                            $('#editForkliftModal').modal('hide');
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal update forklift', 'error');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan server';

                        if (xhr.status === 422 && xhr.responseJSON) {
                            // Validation error Laravel
                            if (xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                // Ambil error pertama
                                const firstError = Object.values(xhr.responseJSON.errors)[0];
                                msg = Array.isArray(firstError) ? firstError[0] : firstError;
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        Swal.fire('Error!', msg, 'error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('Update');
                    }
                });
            });

            // Handle Delete Forklift
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                const unit = $(this).data('unit');

                Swal.fire({
                    title: `Hapus ${unit}?`,
                    text: "Forklift akan dihapus permanen beserta semua assignment-nya.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('api/p2h/delete/forklift') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire('Dihapus', res.message,
                                        'success');
                                    loadForkliftData();
                                    // $('#forkliftTable').DataTable().ajax
                                    //     .reload();
                                } else {
                                    Swal.fire('Gagal', res.message ||
                                        'Gagal menghapus', 'error');
                                }
                            },
                            error: function(xhr) {
                                const res = xhr.responseJSON;
                                Swal.fire('Error', res?.error ||
                                    'Terjadi kesalahan saat menghapus',
                                    'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.edit-assignment-btn', function() {
                const forkliftId = $(this).data('id');
                const unit = $(this).data('unit');
                $('#editAssignmentForkliftId').val(forkliftId);
                $('#editAssignmentLabel').text(`Edit Assignment - ${unit}`);

                $.get("{{ url('api/p2h/show/forklift/assignment') }}/" + forkliftId,
                    function(res) {
                        let operatorOptions = '';
                        res.operators.forEach(op => {
                            operatorOptions +=
                                `<option value="${op.id}">${op.username} (${op.nik})</option>`;
                        });

                        $('#operator_1').html(operatorOptions).val(res
                            .operator_1);
                        $('#operator_2').html(operatorOptions).val(res
                            .operator_2);
                        $('#operator_3').html(operatorOptions).val(res
                            .operator_3);
                        $('#editAssignmentModal').modal('show');
                    });
            });

            $('#editAssignmentForm').on('submit', function(e) {
                e.preventDefault();

                const $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).text('Menyimpan...');

                $.post("{{ url('/p2h/update/forklift/assignment') }}", $(this).serialize())
                    .done(function(res) {
                        if (res.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadForkliftData();
                            // $('#forkliftTable').DataTable().ajax.reload(null, false);
                            $('#editAssignmentModal').modal('hide');
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal update assignment', 'error');
                        }
                    })
                    .fail(function(xhr) {
                        let msg = 'Terjadi kesalahan server';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', msg, 'error');
                    })
                    .always(function() {
                        $btn.prop('disabled', false).text('Simpan Perubahan');
                    });
            });
        });
    </script>
@endsection
