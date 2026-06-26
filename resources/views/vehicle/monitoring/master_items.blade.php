@extends('layouts.app')

@section('title', '| Master Items')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Master Items</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">Master Items</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
                    <i class="ri-check-line me-2 align-middle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-3" role="alert">
                    <i class="ri-error-warning-line me-2 align-middle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                            <h4 class="card-title mb-0 flex-grow-1">Daftar Item</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="70">No</th>
                                            <th>Item Name</th>
                                            <th class="text-center" width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($items as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                        <button type="button" class="btn btn-soft-primary btn-sm btn-edit"
                                                            data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                                            title="Edit">
                                                            <i class="ri-edit-line"></i>
                                                        </button>
                                                        <form
                                                            action="{{ route('vehicle.monitoring.master.items.delete', $item->id) }}"
                                                            method="POST" class="d-inline form-delete">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-soft-danger btn-sm"
                                                                title="Delete">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Belum ada item
                                                    terdaftar.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card shadow-sm border-0" id="formCard">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                            <h4 class="card-title mb-0 flex-grow-1" id="formTitle">Tambah Item Baru</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vehicle.monitoring.master.items.store') }}" method="POST"
                                id="itemForm">
                                @csrf
                                <input type="hidden" name="_method" id="formMethod" value="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Item Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required
                                        placeholder="Contoh: Gula Pasir">
                                    @error('name')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="d-flex gap-2 justify-content-end mt-4">
                                    <button type="button" class="btn btn-light" id="btnCancel"
                                        style="display: none;">Batal</button>
                                    <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Edit button handler
            $('.btn-edit').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                $('#formTitle').text('Edit Item');
                $('#name').val(name);

                // Change form action to update
                $('#itemForm').attr('action', `{{ url('vehicle-monitoring/master/items/update') }}/${id}`);
                $('#formMethod').val('PUT');

                $('#btnCancel').show();
                $('#btnSubmit').text('Perbarui Item').removeClass('btn-primary').addClass('btn-success');

                // Scroll to form card on mobile
                $('html, body').animate({
                    scrollTop: $("#formCard").offset().top - 100
                }, 500);
            });

            // Cancel button handler
            $('#btnCancel').on('click', function() {
                $('#formTitle').text('Tambah Item Baru');
                $('#name').val('');

                // Restore form action to store
                $('#itemForm').attr('action', `{{ route('vehicle.monitoring.master.items.store') }}`);
                $('#formMethod').val('POST');

                $(this).hide();
                $('#btnSubmit').text('Simpan Item').removeClass('btn-success').addClass('btn-primary');
            });

            // SweetAlert Delete confirmation
            $('.form-delete').on('submit', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Hapus Item?',
                    text: "Apakah Anda yakin ingin menghapus data Item ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
