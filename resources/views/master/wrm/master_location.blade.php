@extends('layouts.app')

@section('title', '| Master Location Raw Material')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="mb-0">Master Lokasi Raw Material</h5>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari zona/bin..." style="width: 200px;">
                    <button class="btn btn-outline-primary" id="btnUpload" data-bs-toggle="modal"
                        data-bs-target="#uploadModal">
                        <i class="mdi mdi-upload"></i> Upload
                    </button>
                    <button class="btn btn-primary" id="btnTambah">
                        <i class="mdi mdi-plus"></i> Tambah
                    </button>
                </div>
            </div>


            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap" id="tableGroupStock">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Plant</th>
                                <th>S Loc</th>
                                <th>Gudang</th>
                                <th>Zona</th>
                                <th>Bin</th>
                                <th class="text-center">Aksi</th>
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
<div class="modal fade" id="modalLoc" tabindex="-1">
    <div class="modal-dialog">
        <form id="formLocation">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form Lokasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="id">
                    <div class="mb-2">
                        <label>Plant <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="plant" required>
                    </div>
                    <div class="mb-2">
                        <label>S Loc <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="s_loc" required>
                    </div>
                    <div class="mb-2">
                        <label>Gudang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="gudang" required>
                    </div>
                    <div class="mb-2">
                        <label>Zona <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="zona" required>
                    </div>
                    <div class="mb-2">
                        <label>Bin <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bin" required>
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

{{-- Modal Upload --}}
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="mdi mdi-cloud-upload me-1"></i> Upload File Master Lokasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formUploadBarang" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        Hanya izinkan file <b>.xlsx, .xls.</b> Ukuran maksimal <b>5MB</b>.
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label fw-bold">Pilih File Master Lokasi</label>
                        <input class="form-control" type="file" id="file" name="file" required
                            accept=".xlsx, .xls, .csv">
                    </div>
                </div>

                <div class="modal-footer p-2 d-flex justify-content-between">
                    <a href="{{ asset('assets/templates/excel/template_location_inventory_wrm.xlsx') }}" target="_blank"
                        class="btn btn-info flex-fill ms-0 me-1">
                        <i class="mdi mdi-download me-1"></i> Unduh Template
                    </a>

                    <button type="submit" class="btn btn-success flex-fill me-0 ms-1">
                        <i class="mdi mdi-check-bold me-1"></i> Unggah Sekarang
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

        loadData(currentPage);

        // Pencarian dengan delay typing
        let typingTimer;
        $('#searchInput').on('keyup', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                loadData(1);
            }, 500);
        });

        function loadData(page = 1) {
            currentPage = page;
            let search = $('#searchInput').val() || '';

            $.get(`/master/wrm/location/get-data?page=${page}&search=${search}`, function(res) {
                let html = '';
                let data = res.data.data;
                let no = res.data.from || 1;

                if (data.length > 0) {
                    $.each(data, function(i, v) {
                        html += `
                            <tr>
                                <td class="text-center">${no++}</td>
                                <td>${v.plant}</td>
                                <td>${v.s_loc}</td>
                                <td>${v.gudang}</td>
                                <td>${v.zona}</td>
                                <td>${v.bin}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btnEdit" data-data='${JSON.stringify(v)}'>Edit</button>
                                    <button class="btn btn-danger btn-sm btnHapus" data-id="${v.id}">Hapus</button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center">Data tidak ditemukan</td></tr>';
                }

                $('#tableGroupStock tbody').html(html);
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
            $('#formLocation')[0].reset();
            $('#id').val('');
            $('#modalLoc').modal('show');
        });

        $(document).on('click', '.btnEdit', function() {
            let data = $(this).data('data');

            $('#id').val(data.id);
            $('#gudang').val(data.gudang);
            $('#s_loc').val(data.s_loc);
            $('#plant').val(data.plant);
            $('#zona').val(data.zona);
            $('#bin').val(data.bin);

            $('#modalLoc').modal('show');
        });

        $('#formLocation').submit(function(e) {
            e.preventDefault();

            let id = $('#id').val();
            let url = `/master/wrm/location`;
            let method = 'POST';

            if (id) {
                url += `/update/${id}`;
                method = 'PUT';
            } else {
                url += `/store`;
            }

            $.ajax({
                url: url,
                type: method,
                data: {
                    _token: '{{ csrf_token() }}',
                    gudang: $('#gudang').val(),
                    s_loc: $('#s_loc').val(),
                    plant: $('#plant').val(),
                    zona: $('#zona').val(),
                    bin: $('#bin').val(),
                },
                success: function(res) {
                    $('#modalLoc').modal('hide');
                    loadData(id ? currentPage : 1);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || 'Data berhasil disimpan',
                        timer: 1500,
                        showConfirmButton: false
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
        });

        $(document).on('click', '.btnHapus', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Yakin hapus?',
                text: 'Data tidak bisa dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/master/wrm/location/delete/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        loadData(currentPage);

                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus',
                            text: res.message || 'Data berhasil dihapus',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data'
                        });
                    }
                });
            });
        });

        $('#formUploadBarang').on('submit', function(e) {

            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: `/master/wrm/location/upload`,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {

                    Swal.fire({
                        title: 'Uploading...',
                        text: 'Sedang memproses file',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                },
                success: function(res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message
                    });

                    $('#formUploadBarang')[0].reset();
                    $('#uploadModal').modal('hide');
                    loadData(1);
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan';

                    if (xhr.status === 422) {
                        let res = xhr.responseJSON;
                        if (res.errors && res.errors.length > 0) {
                            message = res.errors.join('<br>');
                        } else if (res.message) {
                            message = res.message;
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Gagal',
                        html: message
                    });

                }
            });

        });

    });
</script>
@endsection