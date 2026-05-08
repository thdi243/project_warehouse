@extends('layouts.app')

@section('title', '- User Role Assignment')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">User Role Assignment</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Permission</a></li>
                            <li class="breadcrumb-item active">User Roles</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-hover align-middle w-100" id="table-user-roles">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Username</th>
                            <th>Jabatan</th>
                            <th>Bagian</th>
                            <th>Current Roles</th>
                            <th class="text-center" width="100">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Assignment Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="form-assign-roles">
            @csrf
            <input type="hidden" name="user_id" id="assign-user-id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Roles for <span id="assign-user-name" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @foreach($roles as $role)
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input role-checkbox" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}">
                                <label class="form-check-label" for="role-{{ $role->id }}">
                                    {{ $role->display_name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Roles</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const table = $('#table-user-roles').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.user.data') }}",
            columns: [{
                    data: 'nama_lengkap',
                    name: 'nama_lengkap'
                },
                {
                    data: 'username',
                    name: 'username'
                },
                {
                    data: 'jabatan',
                    name: 'jabatan'
                },
                {
                    data: 'bagian',
                    name: 'bagian',
                    render: function(data) {
                        return data ? data.replace(/_/g, ' ').toUpperCase() : '-';
                    }
                },
                {
                    data: 'roles',
                    render: function(data) {
                        if (data.length === 0) return '<span class="text-muted italic">No Roles</span>';
                        return data.map(r => `<span class="badge bg-soft-primary text-primary me-1">${r}</span>`).join('');
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data) {
                        return `<button class="btn btn-soft-info btn-sm" onclick="manageRoles(${data.id}, '${data.nama_lengkap}', '${data.role_ids.join(',')}')">
                        <i class="ri-shield-user-line me-1"></i> Manage
                    </button>`;
                    }
                }
            ]
        });

        window.manageRoles = function(id, name, currentRoles) {
            $('#assign-user-id').val(id);
            $('#assign-user-name').text(name);
            $('.role-checkbox').prop('checked', false);

            if (currentRoles) {
                currentRoles.split(',').forEach(rid => {
                    $(`#role-${rid}`).prop('checked', true);
                });
            }

            new bootstrap.Modal('#assignModal').show();
        };

        $('#form-assign-roles').on('submit', function(e) {
            e.preventDefault();
            let id = $('#assign-user-id').val();
            $.post(`/admin/user-roles/assign/${id}`, $(this).serialize(), function(res) {
                if (res.status) {
                    Swal.fire('Success', res.message, 'success').then(() => {
                        bootstrap.Modal.getInstance('#assignModal').hide();
                        table.ajax.reload(null, false);
                    });
                }
            });
        });
    });
</script>
@endsection