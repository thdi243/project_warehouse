@extends('layouts.app')

@section('title', ' | Master Pallet WRM')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="mdi mdi-palette"></i>
                        Master Pallet
                    </h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="mdi mdi-plus"></i>
                        Tambah Pallet
                    </button>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama pallet...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="palletTable">
                            <thead class="table-secondary">
                                <tr>
                                    <th>No</th>
                                    {{-- <th>Jenis Bahan</th> --}}
                                    <th>Nama Pallet</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Tanggal Dibuat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center" id="pagination">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Master Pallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="palletForm">
                    @csrf
                    <input type="hidden" id="palletId" name="id">
                    <div class="modal-body">
                        {{-- <div class="mb-3">
                            <label for="jenisBahan" class="form-label fw-bold">Jenis Bahan</label>
                            <input type="text" class="form-control" id="jenisBahan" name="jenis_bahan"
                                placeholder="Contoh: Gula" required>
                        </div> --}}
                        <div class="mb-3">
                            <label for="namaPallet" class="form-label fw-bold">Nama Pallet</label>
                            <input type="text" class="form-control" id="namaPallet" name="nama_pallet"
                                placeholder="Contoh: Hollow Gula" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i>
                            Simpan
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
            let currentPage = 1;

            // Load data
            function loadData(page = 1, search = '') {
                $.ajax({
                    url: '{{ route('wrm.master.pallet.getData') }}',
                    method: 'GET',
                    data: {
                        page: page,
                        search: search
                    },

                    success: function(res) {
                        let html = '';
                        let data = res.data.data;

                        if (data.length === 0) {
                            html =
                                '<tr><td colspan="6" class="text-center">Data tidak ditemukan</td></tr>';
                        } else {
                            data.forEach((item, index) => {
                                let no = (res.data.current_page - 1) * res.data.per_page +
                                    index + 1;
                                html += `
                                    <tr>
                                        <td>${no}</td>
                                        <td>${item.nama_pallet}</td>
                                        <td>${item.created_by}</td>
                                        <td>${new Date(item.created_at).toLocaleDateString('id-ID')}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-warning btn-sm btnEdit" data-id="${item.id}" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btnDelete" data-id="${item.id}" title="Hapus">
                                                <i class="mdi mdi-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                        }

                        $('#palletTable tbody').html(html);
                        renderPagination(res.data);
                    }
                });
            }

            // Render pagination
            function renderPagination(data) {
                let html = '';

                if (data.current_page > 1) {
                    html += `<li class="page-item"><a class="page-link" href="#" data-page="1">First</a></li>`;
                    html +=
                        `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page - 1}">Previous</a></li>`;
                }

                for (let i = 1; i <= data.last_page; i++) {
                    if (i === data.current_page) {
                        html += `<li class="page-item active"><a class="page-link" href="#">${i}</a></li>`;
                    } else if (i >= data.current_page - 2 && i <= data.current_page + 2) {
                        html +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    }
                }

                if (data.current_page < data.last_page) {
                    html +=
                        `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a></li>`;
                    html +=
                        `<li class="page-item"><a class="page-link" href="#" data-page="${data.last_page}">Last</a></li>`;
                }

                $('#pagination').html(html);
            }

            // Initial load
            loadData();

            // Search
            $('#searchInput').on('keyup', function() {
                loadData(1, $(this).val());
            });

            // Pagination click
            $(document).on('click', '#pagination a', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page) {
                    loadData(page, $('#searchInput').val());
                }
            });

            // Reset form
            function resetForm() {
                $('#palletForm')[0].reset();
                $('#palletId').val('');
                $('#createModalLabel').text('Tambah Master Pallet');
            }

            // Open create modal
            $('#createModal').on('hidden.bs.modal', function() {
                resetForm();
            });

            // Submit form
            $('#palletForm').on('submit', function(e) {
                e.preventDefault();

                let id = $('#palletId').val();
                let url = id ? '{{ route('wrm.master.pallet.update', ':id') }}'.replace(':id', id) :
                    '{{ route('wrm.master.pallet.store') }}';
                let method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    method: method,
                    data: $(this).serialize(),

                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            resetForm();
                            $('#createModal').modal('hide');
                            loadData(1, '');
                        });
                    },

                    error: function(xhr) {
                        let message = 'Terjadi kesalahan';

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            message = Object.values(errors).map(err => err[0]).join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: message
                        });
                    }
                });
            });

            // Edit
            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('id');
                let row = $(`.btnEdit[data-id="${id}"]`).closest('tr');
                // let jenisBahan = row.find('td:eq(1)').text();
                let namaPallet = row.find('td:eq(2)').text();

                $('#palletId').val(id);
                // $('#jenisBahan').val(jenisBahan);
                $('#namaPallet').val(namaPallet);
                $('#createModalLabel').text('Edit Master Pallet');

                let modal = new bootstrap.Modal(document.getElementById('createModal'));
                modal.show();
            });

            // Delete
            $(document).on('click', '.btnDelete', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah Anda yakin ingin menghapus data ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('wrm.master.pallet.destroy', ':id') }}'.replace(
                                ':id',
                                id),
                            method: 'DELETE',
                            data: {
                                '_token': $('meta[name="csrf-token"]').attr('content')
                            },

                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    loadData(1, '');
                                });
                            },

                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: xhr.responseJSON?.message ??
                                        'Terjadi kesalahan'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
