@extends('layouts.app')

@section('styles')
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-title-box">
                <h4 class="mb-0">🚚 Pallet Mover Registration & Assignment</h4>
            </div>

            {{-- Section: Tambah Pallet Mover --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Tambah Forklift</h5>
                    <button type="submit" form="addPalletForm" class="btn btn-primary">
                        <i class="mdi mdi-plus me-2"></i>Simpan</button>
                </div>
                <div class="card-body">
                    <form id="addPalletForm">
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

            {{-- Section: Data Pallet Mover --}}
            <div class="card">
                <div class="card-header">
                    <h5>Daftar Pallet Mover Terdaftar</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="palletTable" class="table table-bordered table-striped text-nowrap">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Deskripsi</th>
                                    <th>Departemen</th>
                                    <th>Section</th>
                                    <th>Operator 1</th>
                                    <th>Operator 2</th>
                                    <th>Operator 3</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- AJAX inject data --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Assignment Operator --}}
    <div class="modal fade" id="palletAssignmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="palletAssignmentForm" class="modal-content">
                @csrf
                <input type="hidden" name="pallet_mover_id" id="palletMoverId">
                <div class="modal-header">
                    <h5 class="modal-title">🛠️ Assign Operator Pallet Mover</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Operator</label>
                        <select class="form-select" name="user_id" id="palletOperatorSelect">
                            @foreach ($operators as $user)
                                <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->nik }})</option>
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
                        <label class="form-label">Catatan</label>
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

    <div class="modal fade" id="editPalletModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editPalletForm" class="modal-content">
                @csrf
                <input type="hidden" name="pallet_mover_id" id="editPalletId">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Edit Pallet Mover</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3 px-3">
                    <div class="col-md-6">
                        <label class="form-label">Nomor Unit</label>
                        <input type="text" name="nomor_unit" id="editNomorUnit" class="form-control" required>
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
                    <div class="col-md-6">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" id="editDescription" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="palletBackupModal" tabindex="-1" aria-labelledby="palletBackupModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="palletBackupModalLabel">Cadangan Pallet Mover</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" id="palletBackupModalBody">
                    <p>Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPalletAssignmentModal" tabindex="-1" aria-labelledby="editPalletAssignmentLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="editPalletAssignmentForm" class="modal-content">
                @csrf
                <input type="hidden" name="pallet_mover_id" id="editPalletAssignmentId">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPalletAssignmentLabel">Edit Assignment Pallet Mover</h5>
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
            const routeList = {
                data: "{{ url('api/p2h/data/registration/pallet-mover') }}",
                store: "{{ url('api/p2h/store/registration/pallet-mover') }}",
                assignmentStore: "{{ url('/p2h/store/pallet-mover/assignment') }}",
                csrf: '{{ csrf_token() }}',
                detail: "{{ url('api/p2h/detail/pallet-mover/') }}",
                update: "{{ url('api/p2h/update/pallet-mover/') }}",
                delete: "{{ url('api/p2h/delete/pallet-mover/') }}",
                backups: "{{ url('api/p2h/backups/pallet-mover/') }}",
                assignmentDetail: "{{ url('api/p2h/detail/pallet-mover/assignment') }}",
                assignmentUpdate: "{{ url('/p2h/update/pallet-mover/assignment') }}"
            };

            loadPalletMoverData();

            function loadPalletMoverData() {
                $('#loadingRow').show();
                $('#emptyState').addClass('d-none');
                $('#palletTable tbody tr:not(#loadingRow)').remove(); // clear rows kecuali loading

                $.get(routeList.data)
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
                                    <td>${notes}</td>
                                    <td class="text-center">${row.departemen.charAt(0).toUpperCase() + row.departemen.slice(1)}</td>
                                    <td class="text-center">${sectionDisplay}</td>
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
                                                    title="Edit Pallet Mover">
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

                        $('#palletTable tbody').append(rows);
                    })
                    .fail(function() {
                        $('#loadingRow').hide();
                        Swal.fire('Error', 'Gagal memuat data pallet mover', 'error');
                    });
            }

            // Backup detail modal
            $('#palletTable').on('click', '.backup-detail-btn', function() {
                const id = $(this).data('id');
                const unit = $(this).data('unit');

                $.get(`${routeList.backups}/${id}`, function(response) {
                    let list = '';
                    response.backups.forEach(op => {
                        list += `<li>${op.username} (${op.nik})</li>`;
                    });

                    $('#palletBackupModalLabel').text(`Cadangan - ${unit}`);
                    $('#palletBackupModalBody').html(`<ul>${list}</ul>`);
                    $('#palletBackupModal').modal('show');
                }).fail(function() {
                    $('#palletBackupModalBody').html(
                        '<p class="text-danger">Gagal memuat data cadangan.</p>');
                    $('#palletBackupModal').modal('show');
                });
            });

            // Assign
            $(document).on('click', '.assign-btn', function() {
                $('#palletMoverId').val($(this).data('id'));
                $('#palletAssignmentModal').modal('show');
            });

            $('#palletAssignmentForm').on('submit', function(e) {
                e.preventDefault();
                $.post(routeList.assignmentStore, $(this).serialize(), function(res) {
                    if (res.success) {
                        Swal.fire('Success', res.message, 'success');
                        // $('#palletTable').DataTable().ajax.reload();
                        loadPalletMoverData();
                        $('#palletAssignmentModal').modal('hide');
                    } else {
                        Swal.fire('Error', res.message || 'Gagal menyimpan', 'error');
                    }
                });
            });

            // Tambah pallet
            $('#addPalletForm').on('submit', function(e) {
                e.preventDefault();
                $.post(routeList.store, $(this).serialize(), function(res) {
                    if (res.success) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadPalletMoverData();
                        // $('#palletTable').DataTable().ajax.reload();
                        $('#addPalletForm')[0].reset();
                    } else {
                        Swal.fire('Gagal', res.error || 'Pallet mover gagal disimpan',
                            'error');
                    }
                });
            });

            // Edit pallet
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                $.get(`${routeList.detail}/${id}`, function(data) {
                    $('#editPalletId').val(id);
                    $('#editNomorUnit').val(data.data.nomor_unit);
                    $('#editDepartemen').val(data.data.departemen);
                    $('#editSection').val(data.data.section);
                    $('#editStatus').val(data.data.status);
                    $('#editDescription').val(data.data.description);
                    $('#editPalletModal').modal('show');
                });
            });

            $('#editPalletForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#editPalletId').val();
                const formData = $(this).serialize();

                $.ajax({
                    url: `${routeList.update}/${id}`,
                    type: 'PUT',
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Berhasil', res.message, 'success');
                            // $('#palletTable').DataTable().ajax.reload();
                            loadPalletMoverData();
                            $('#editPalletModal').modal('hide');
                        } else {
                            Swal.fire('Gagal', res.error ||
                                'Gagal update pallet mover',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        const res = xhr.responseJSON;
                        Swal.fire('Error', res?.error ||
                            'Terjadi kesalahan saat update',
                            'error');
                    }
                });
            });

            // Delete
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                const unit = $(this).data('unit');

                Swal.fire({
                    title: `Hapus ${unit}?`,
                    text: "Pallet mover akan dihapus beserta semua assignment-nya.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `${routeList.delete}/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: routeList.csrf
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire('Dihapus', res.message,
                                        'success');
                                    loadPalletMoverData();
                                    // $('#palletTable').DataTable().ajax
                                    //     .reload();
                                } else {
                                    Swal.fire('Gagal', res.message ||
                                        'Gagal menghapus',
                                        'error');
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

            // Edit assignment
            $(document).on('click', '.edit-assignment-btn', function() {
                const id = $(this).data('id');
                const unit = $(this).data('unit');
                $('#editPalletAssignmentId').val(id);
                $('#editPalletAssignmentLabel').text(`Edit Assignment - ${unit}`);

                $.get(`${routeList.assignmentDetail}/${id}`, function(res) {
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
                    $('#editPalletAssignmentModal').modal('show');
                });
            });

            $('#editPalletAssignmentForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#editPalletAssignmentId').val();

                $.post(`${routeList.assignmentUpdate}/${id}`, $(this).serialize(), function(
                    res) {
                    if (res.success) {
                        Swal.fire('Berhasil', res.message, 'success');
                        // $('#palletTable').DataTable().ajax.reload();
                        loadPalletMoverData();
                        $('#editPalletAssignmentModal').modal('hide');
                    } else {
                        Swal.fire('Gagal', res.error || 'Gagal update assignment',
                            'error');
                    }
                }).fail(function(xhr) {
                    const res = xhr.responseJSON;
                    Swal.fire('Error', res?.error ||
                        'Terjadi kesalahan saat update assignment',
                        'error');
                });
            });
        });
    </script>
@endsection
