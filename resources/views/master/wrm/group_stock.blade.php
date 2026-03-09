@extends('layouts.app')

@section('title', '| Master Group Stock Raw Material')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <h5 class="mb-0">Master Group Stock Raw Material</h5>
                    <div class="d-flex gap-2">
                        {{-- <button class="btn btn-outline-primary" id="btnUpload">
                            <i class="mdi mdi-upload"></i> Upload
                        </button> --}}
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
                                    <th>Group</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="modalGroupStock" tabindex="-1">
        <div class="modal-dialog">
            <form id="formGroupStock">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Group Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="id">

                        <div class="mb-2">
                            <label>Group <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="group" required>
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

    <!-- MODAL UPLOAD -->
    {{-- <div class="modal fade" id="modalUpload" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Master Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="d-grid gap-2 mb-3">
                        <small class="fst-italic">Belum punya template?</small>
                        <a href="/wrm/master/barang/template" class="btn btn-outline-success">
                            <i class="mdi mdi-download"></i> Download Template
                        </a>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Pilih File Excel</label>
                        <input type="file" class="form-control" id="fileUpload" accept=".xls,.xlsx">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" id="btnSubmitUpload">Upload</button>
                </div>
            </div>
        </div>
    </div> --}}

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            loadData();

            function loadData() {
                $.get('/wrm/master/group-stock/get-data', function(res) {
                    let html = '';
                    $.each(res.data, function(i, v) {
                        html += `
                            <tr>
                                <td class="text-center">${i + 1}</td>
                                <td>${v.group}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btnEdit" data-data='${JSON.stringify(v)}'>Edit</button>
                                    <button class="btn btn-danger btn-sm btnHapus" data-id="${v.id}">Hapus</button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#tableGroupStock tbody').html(html);
                });
            }

            $('#btnTambah').click(function() {
                $('#formGroupStock')[0].reset();
                $('#id').val('');
                $('#modalGroupStock').modal('show');
            });

            $(document).on('click', '.btnEdit', function() {
                let data = $(this).data('data');

                $('#id').val(data.id);
                $('#group').val(data.group);

                $('#modalGroupStock').modal('show');
            });

            $('#formGroupStock').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = '/wrm/master/group-stock';
                let method = 'POST';

                if (id) {
                    url += '/update/' + id;
                    method = 'PUT';
                } else {
                    url += '/store';
                }

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        group: $('#group').val(),
                    },
                    success: function(res) {
                        $('#modalGroupStock').modal('hide');
                        loadData();

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

            // $('#btnUpload').click(function() {
            //     $('#fileUpload').val('');
            //     $('#modalUpload').modal('show');
            // });

            // $('#btnSubmitUpload').click(function() {

            //     let file = $('#fileUpload')[0].files[0];
            //     if (!file) {
            //         Swal.fire('Oops', 'Pilih file terlebih dahulu', 'warning');
            //         return;
            //     }

            //     let formData = new FormData();
            //     formData.append('file', file);
            //     formData.append('_token', '{{ csrf_token() }}');

            //     Swal.fire({
            //         title: 'Upload data?',
            //         text: file.name,
            //         icon: 'question',
            //         showCancelButton: true,
            //         confirmButtonText: 'Upload'
            //     }).then((result) => {
            //         if (!result.isConfirmed) return;

            //         Swal.fire({
            //             title: 'Uploading...',
            //             allowOutsideClick: false,
            //             didOpen: () => Swal.showLoading()
            //         });

            //         $.ajax({
            //             url: '/wrm/master/barang/upload',
            //             method: 'POST',
            //             data: formData,
            //             processData: false,
            //             contentType: false,
            //             success: function(res) {
            //                 Swal.fire({
            //                     icon: 'success',
            //                     title: 'Berhasil',
            //                     text: res.message,
            //                     timer: 1500,
            //                     showConfirmButton: false
            //                 });

            //                 $('#modalUpload').modal('hide');
            //                 loadData();
            //             },
            //             error: function(xhr) {
            //                 let msg = xhr.responseJSON?.errors?.join('<br>') ??
            //                     xhr.responseJSON?.message ??
            //                     'Upload gagal';

            //                 Swal.fire({
            //                     icon: 'error',
            //                     title: 'Validasi Gagal',
            //                     html: msg
            //                 });
            //             }
            //         });
            //     });
            // });

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
                        url: '/wrm/master/grou-stock/delete/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            loadData();

                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.message ||
                                    'Data berhasil dihapus',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                });
            });

        });
    </script>
@endsection
