@extends('layouts.app')

@section('title', '- Role Management')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Role Management</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-primary" onclick="addRole()">
                            <i class="ri-add-line me-1"></i> Add New Role
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse($roles as $role)
            <div class="col-xl-4 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">{{ $role->display_name }}</h5>
                                <small class="text-muted">{{ $role->name }}</small>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="dropdown">
                                    <button class="btn btn-ghost-primary btn-icon btn-sm dropdown shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="editRole({{ $role->id }}, '{{ $role->name }}', '{{ $role->display_name }}', '{{ $role->description }}')"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit Role</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="assignPermissions({{ $role->id }}, '{{ $role->display_name }}')"><i class="ri-shield-keyhole-line align-bottom me-2 text-muted"></i> Permissions</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteRole({{ $role->id }})"><i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mb-4">{{ $role->description ?? 'No description provided.' }}</p>

                        <div class="d-flex align-items-center justify-content-between">
                            <span class="badge bg-soft-info text-info"><i class="ri-user-line me-1"></i> {{ $role->users_count }} Users</span>
                            <span class="badge bg-soft-success text-success"><i class="ri-lock-2-line me-1"></i> {{ $role->permissions_count }} Permissions</span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="ri-shield-user-line fs-1 text-muted"></i>
                <p class="text-muted mt-2">No roles defined yet. Create one to start managing access.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Role Modal --}}
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="form-role">
            @csrf
            <input type="hidden" name="id" id="role-id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">Add Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role Key Name (System) <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="role-name" class="form-control" placeholder="e.g. admin, checker, operator" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="display_name" id="role-display-name" class="form-control" placeholder="e.g. Administrator, Warehouse Checker" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="role-description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Permissions Modal --}}
<div class="modal fade" id="permissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="form-assign-permissions">
            @csrf
            <input type="hidden" name="role_id" id="assign-role-id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Permissions for <span id="assign-role-name" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">
                        @foreach($permissions as $section => $perms)
                        <div class="col-12 mb-4">
                            <h6 class="text-uppercase fw-bold text-muted border-bottom pb-2 mb-3">{{ $section ?: 'General' }}</h6>
                            <div class="row">
                                @foreach($perms as $perm)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm-{{ $perm->id }}">
                                        <label class="form-check-label" for="perm-{{ $perm->id }}">
                                            {{ $perm->name }}
                                            <small class="d-block text-muted">{{ $perm->description }}</small>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="flex-grow-1">
                        <button type="button" class="btn btn-soft-secondary btn-sm" onclick="toggleAllPermissions()">Select All/None</button>
                    </div>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Sync Permissions</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const roleModal = new bootstrap.Modal('#roleModal');
    const permissionModal = new bootstrap.Modal('#permissionModal');

    function addRole() {
        $('#form-role')[0].reset();
        $('#role-id').val('');
        $('#roleModalLabel').text('Add Role');
        $('#role-name').prop('readonly', false);
        roleModal.show();
    }

    function editRole(id, name, display, desc) {
        $('#role-id').val(id);
        $('#role-name').val(name);
        $('#role-display-name').val(display);
        $('#role-description').val(desc);
        $('#roleModalLabel').text('Edit Role');
        roleModal.show();
    }

    $('#form-role').on('submit', function(e) {
        e.preventDefault();
        let id = $('#role-id').val();
        let url = id ? `/admin/roles/update/${id}` : "/admin/roles/store";

        $.post(url, $(this).serialize(), function(res) {
            if (res.status) {
                Swal.fire('Success', res.message, 'success').then(() => location.reload());
            }
        }).fail(function(xhr) {
            Swal.fire('Error', xhr.responseJSON.message, 'error');
        });
    });

    function assignPermissions(id, name) {
        $('#assign-role-id').val(id);
        $('#assign-role-name').text(name);
        $('.permission-checkbox').prop('checked', false);

        $.get(`/admin/roles/permissions/${id}`, function(res) {
            if (res.status) {
                res.permissions.forEach(pid => {
                    $(`#perm-${pid}`).prop('checked', true);
                });
                permissionModal.show();
            }
        });
    }

    $('#form-assign-permissions').on('submit', function(e) {
        e.preventDefault();
        let id = $('#assign-role-id').val();
        $.post(`/admin/roles/assign-permissions/${id}`, $(this).serialize(), function(res) {
            if (res.status) {
                Swal.fire('Success', res.message, 'success').then(() => location.reload());
            }
        });
    });

    function deleteRole(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will remove the role and its assignments.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/permission/roles/destroy/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        Swal.fire('Deleted!', res.message, 'success').then(() => location.reload());
                    }
                });
            }
        });
    }

    function toggleAllPermissions() {
        let allChecked = $('.permission-checkbox:checked').length === $('.permission-checkbox').length;
        $('.permission-checkbox').prop('checked', !allChecked);
    }
</script>
@endsection