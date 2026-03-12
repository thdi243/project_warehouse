@extends('layouts.app')

@section('title', '| Master Location Raw Material')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <h5 class="mb-0">Master Lokasi Raw Material</h5>
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
                                    <th>Plant</th>
                                    <th>S Loc</th>
                                    <th>Gudang</th>
                                    <th>Bin</th>
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

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            loadData();

            function loadData() {
                $.get('/wrm/master/location/get-data', function(res) {
                    let html = '';
                    $.each(res.data, function(i, v) {
                        html += `
                            <tr>
                                <td class="text-center">${i + 1}</td>
                                <td>${v.plant}</td>
                                <td>${v.s_loc}</td>
                                <td>${v.gudang}</td>
                                <td>${v.bin}</td>
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
                $('#formLocation')[0].reset();
                $('#id').val('');
                $('#modalLoc').modal('show');
            });

            $(document).on('click', '.btnEdit', function() {
                let data = $(this).data('data');

                $('#id').val(data.id);
                $('#gudang').val(data.gudang);
                $('#bin').val(data.bin);
                $('#s_loc').val(data.s_loc);
                $('#plant').val(data.plant);

                $('#modalLoc').modal('show');
            });

            $('#formLocation').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = '/wrm/master/location';
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
                        gudang: $('#gudang').val(),
                        bin: $('#bin').val(),
                        s_loc: $('#s_loc').val(),
                        plant: $('#plant').val(),
                    },
                    success: function(res) {
                        $('#modalLoc').modal('hide');
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
                        url: '/wrm/master/location/delete/' + id,
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
