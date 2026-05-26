@extends('layouts.app')

@section('title', '| Master Destinasi WFG')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Master Destinasi WFG</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Master</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WFG</a></li>
                                <li class="breadcrumb-item active">Destinasi</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card" id="destinasiList">
                        <div class="card-header border-0">
                            <div class="row g-4 align-items-center">
                                <div class="col-sm-auto">
                                    <div>
                                        <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal"
                                            id="create-btn" data-bs-target="#showModal">
                                            <i class="ri-add-line align-bottom me-1"></i> Tambah Destinasi
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">
                                            <input type="text" class="form-control search" placeholder="Search...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div>
                                <div class="table-responsive table-card mb-1">
                                    <table class="table align-middle table-nowrap" id="destinasiTable">
                                        <thead class="table-light text-muted">
                                            <tr>
                                                <th class="sort" data-sort="id">NO</th>
                                                <th class="sort" data-sort="destinasi">Nama Destinasi</th>
                                                <th class="sort" data-sort="status">Status</th>
                                                <th>Dibuat Oleh</th>
                                                <th colspan="2" class="text-center" style="width: 15%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list form-check-all">
                                            <!-- DataTables will populate this -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-center mt-4" id="paginationContainer"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">Tambah Destinasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="close-modal"></button>
                </div>
                <form id="destinasiForm">
                    <div class="modal-body">
                        <input type="hidden" id="id-field">
                        <div class="mb-3">
                            <label for="destinasi-field" class="form-label">Nama Destinasi</label>
                            <input type="text" id="destinasi-field" class="form-control"
                                placeholder="Masukkan nama destinasi" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" id="add-btn">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Modal -->
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            function debounce(func, delay) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), delay);
                };
            }

            window.loadDestinasi = function(page = 1) {
                const searchTerm = $('.search').val();
                let container = $("#destinasiTable tbody");
                container.empty();
                $("#paginationContainer").empty();

                $.ajax({
                    url: "{{ route('wfg.master.destinasi.data') }}",
                    method: 'GET',
                    data: {
                        search: searchTerm,
                        page: page
                    },
                    success: function(res) {
                        const paginatedData = res.data;
                        const items = paginatedData.data;

                        if (res.status === true && items.length > 0) {
                            $.each(items, function(i, item) {
                                const perPage = paginatedData.per_page;
                                const currentPage = paginatedData.current_page;
                                const noUrut = ((currentPage - 1) * perPage) + (i + 1);
                                const statusBadge = item.active ?
                                    '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>' :
                                    '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Inactive</span>';

                                const creator = item.created_by_user ? item.created_by_user
                                    .nama_lengkap : (item.created_by ? item.created_by
                                        .nama_lengkap : '-');

                                let row = `
                                    <tr>
                                        <td>${noUrut}</td>
                                        <td>${item.destinasi}</td>
                                        <td>${statusBadge}</td>
                                        <td>${creator}</td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-toggle-status ${item.active ? 'btn-soft-danger' : 'btn-soft-success'}"
                                                data-id="${item.id}"
                                                data-active="${item.active}">
                                                <i
                                                    class="${item.active ? 'ri-close-circle-fill' : 'ri-check-double-fill'} align-bottom me-1"></i>
                                                ${item.active ? 'Nonaktifkan' : 'Aktifkan'}
                                            </button>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-soft-info btn-edit"
                                                    data-id="${item.id}" data-name="${item.destinasi}" title="Edit">
                                                    <i class="ri-pencil-fill align-bottom"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-soft-danger btn-delete"
                                                    data-id="${item.id}" title="Hapus">
                                                    <i class="ri-delete-bin-fill align-bottom"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                container.append(row);
                            });

                            renderPagination(paginatedData);
                        } else {
                            container.append(
                                '<tr><td colspan="5" class="text-center">Data tidak ditemukan</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error("Gagal memuat data destinasi");
                    }
                });
            }

            loadDestinasi();

            $('.search').keyup(debounce(function() {
                loadDestinasi(1);
            }, 300));

            function renderPagination(data) {
                const container = $("#paginationContainer");
                container.empty();

                if (!data || data.last_page <= 1) return;

                let paginationHtml =
                    '<nav aria-label="Page navigation"><ul class="pagination pagination-separated justify-content-center mb-0">';

                // First
                paginationHtml += `
                    <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="1">First</a>
                    </li>
                `;

                // Previous
                paginationHtml += `
                    <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${data.current_page - 1}">Prev</a>
                    </li>
                `;

                // Pagination number limit
                let startPage = Math.max(1, data.current_page - 2);
                let endPage = Math.min(data.last_page, data.current_page + 2);

                // Adjust biar tetap 5 halaman jika memungkinkan
                if (data.current_page <= 3) {
                    endPage = Math.min(5, data.last_page);
                }

                if (data.current_page >= data.last_page - 2) {
                    startPage = Math.max(1, data.last_page - 4);
                }

                // Number pages
                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${data.current_page === i ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                // Next
                paginationHtml += `
                    <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a>
                    </li>
                `;

                // Last
                paginationHtml += `
                    <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${data.last_page}">Last</a>
                    </li>
                `;

                paginationHtml += '</ul></nav>';

                container.append(paginationHtml);

                $('#paginationContainer .page-link').on('click', function(e) {
                    e.preventDefault();

                    const page = $(this).data('page');

                    if (!page || $(this).closest('.page-item').hasClass('disabled')) {
                        return;
                    }

                    loadDestinasi(page);
                });
            }

            $('#destinasiForm').submit(function(e) {
                e.preventDefault();
                var id = $('#id-field').val();
                var url = id ? "{{ url('master/wfg/destinasi/update') }}/" + id :
                    "{{ route('wfg.master.destinasi.store') }}";
                var method = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        destinasi: $('#destinasi-field').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#showModal').modal('hide');
                        Swal.fire('Berhasil!', response.message, 'success');
                        loadDestinasi();
                        $('#destinasiForm')[0].reset();
                        $('#id-field').val('');
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMsg = '';
                        if (errors) {
                            $.each(errors, function(key, value) {
                                errorMsg += value[0] + '<br>';
                            });
                        } else {
                            errorMsg = 'Terjadi kesalahan sistem';
                        }
                        Swal.fire('Error!', errorMsg, 'error');
                    }
                });
            });

            $(document).on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                $('#id-field').val(id);
                $('#destinasi-field').val(name);
                $('.modal-title').text('Edit Destinasi');
                $('#add-btn').text('Update');
                $('#showModal').modal('show');
            });

            $('#create-btn').click(function() {
                $('#id-field').val('');
                $('#destinasiForm')[0].reset();
                $('.modal-title').text('Tambah Destinasi');
                $('#add-btn').text('Simpan');
            });

            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('master/wfg/destinasi/delete') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadDestinasi();
                            },
                            error: function() {
                                Swal.fire('Error!',
                                    'Terjadi kesalahan saat menghapus data.',
                                    'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.btn-toggle-status', function() {

                const id = $(this).data('id');
                const isActive = $(this).data('active');

                const actionText = isActive ? 'Nonaktifkan' : 'Aktifkan';
                const successText = isActive ? 'dinonaktifkan' : 'diaktifkan';

                Swal.fire({
                    title: `${actionText} Data?`,
                    html: `
                        <div style="font-size:14px">
                            Apakah anda yakin ingin
                            <b>${actionText.toLowerCase()}</b>
                            data destinasi ini?
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isActive ? '#d33' : '#16a34a',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: `Ya, ${actionText}!`,
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {

                    if (result.isConfirmed) {

                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ url('master/wfg/destinasi/toggle-status') }}/" + id,
                            type: 'PATCH',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },

                            success: function(response) {

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: `Data berhasil ${successText}.`,
                                    timer: 1800,
                                    showConfirmButton: false
                                });

                                loadDestinasi();
                            },

                            error: function() {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: `Data gagal ${successText}.`
                                });

                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
