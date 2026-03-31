@extends('layouts.app')

@section('title', '| Master Supplier')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="mb-0">Master Supplier Raw Material</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" id="btnTambah">
                        <i class="mdi mdi-plus"></i> Tambah Supplier
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap" id="tableMasterSupplier">
                        <thead class="table-light align-middle">
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th>Nama Supplier</th>
                                <th>Lokasi</th>
                                <th class="text-center" width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div id="pagination-container" class="d-flex justify-content-between align-items-center mt-3"></div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalSupplier" tabindex="-1">
    <div class="modal-dialog">
        <form id="formSupplier">
            @csrf
            <input type="hidden" id="supplier_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama supplier">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" required placeholder="Masukkan lokasi supplier">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let currentPage = 1;

        loadData(currentPage);

        function loadData(page = 1) {
            currentPage = page;
            $.get(`/master/wrm/supplier/get-data?page=${page}`, function(res) {
                let html = '';
                let data = res.data.data;
                let no = res.data.from || 1;

                if (data.length > 0) {
                    $.each(data, function(i, v) {
                        html += `<tr>
                            <td class="text-center">${no++}</td>
                            <td>${v.nama}</td>
                            <td>${v.lokasi}</td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btnEdit" data-id="${v.id}" data-nama="${v.nama}" data-lokasi="${v.lokasi}">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btnHapus" data-id="${v.id}">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center">Data tidak ditemukan</td></tr>';
                }

                $('#tableMasterSupplier tbody').html(html);
                renderPagination(res.data);
            });
        }

        function renderPagination(meta) {
            if (!meta.last_page || meta.last_page <= 1) {
                $('#pagination-container').html('');
                return;
            }

            let html = `<div>Showing ${meta.from ?? 0} to ${meta.to ?? 0} of ${meta.total} entries</div>`;
            html += `<nav><ul class="pagination pagination-sm mb-0">`;

            html += `<li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadData(${meta.current_page - 1})">Prev</a>
                    </li>`;

            let start = Math.max(1, meta.current_page - 2);
            let end = Math.min(meta.last_page, meta.current_page + 2);

            if (start > 1) {
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadData(1)">1</a></li>`;
                if (start > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }

            for (let i = start; i <= end; i++) {
                html += `<li class="page-item ${i === meta.current_page ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0)" onclick="loadData(${i})">${i}</a>
                        </li>`;
            }

            if (end < meta.last_page) {
                if (end < meta.last_page - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadData(${meta.last_page})">${meta.last_page}</a></li>`;
            }

            html += `<li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadData(${meta.current_page + 1})">Next</a>
                    </li>`;

            html += `</ul></nav>`;
            $('#pagination-container').html(html);
        }

        window.loadData = loadData;

        $('#btnTambah').click(function() {
            $('#formSupplier')[0].reset();
            $('#supplier_id').val('');
            $('#modalTitle').text('Tambah Supplier');
            $('#modalSupplier').modal('show');
        });

        $(document).on('click', '.btnEdit', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let lokasi = $(this).data('lokasi');

            $('#supplier_id').val(id);
            $('#nama').val(nama);
            $('#lokasi').val(lokasi);
            $('#modalTitle').text('Edit Supplier');
            $('#modalSupplier').modal('show');
        });

        $('#formSupplier').submit(function(e) {
            e.preventDefault();
            let id = $('#supplier_id').val();
            let url = id ? `/master/wrm/supplier/update/${id}` : `/master/wrm/supplier/store`;
            let method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: method,
                    nama: $('#nama').val(),
                    lokasi: $('#lokasi').val(),
                },
                success: function(res) {
                    $('#modalSupplier').modal('hide');
                    loadData(id ? currentPage : 1);
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan';
                    if (xhr.responseJSON?.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMsg
                    });
                }
            });
        });

        $(document).on('click', '.btnHapus', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Hapus supplier?',
                text: "Data ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/master/wrm/supplier/delete/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            loadData(currentPage);
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endsection