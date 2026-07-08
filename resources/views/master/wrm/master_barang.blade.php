@extends('layouts.app')

@section('title', '| Master Barang Raw Material')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <h5 class="mb-0">Master Barang Raw Material</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <select id="statusFilter" class="form-select w-auto">
                            <option value="active">Barang Aktif</option>
                            <option value="trashed">Barang Nonaktif</option>
                        </select>
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
                        <table class="table table-striped text-nowrap" id="tableBarang">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th>UOM</th>
                                    {{-- <th>Loc</th> --}}
                                    <th class="text-end">Qty KG/Pallet</th>
                                    <th>Dibuat</th>
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
    <div class="modal fade" id="modalBarang" tabindex="-1">
        <div class="modal-dialog">
            <form id="formBarang">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Master Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="id">

                        <div class="mb-2">
                            <label>MID <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="mid" required>
                        </div>

                        <div class="mb-2">
                            <label>Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" required>
                        </div>

                        <div class="mb-2">
                            <label>UOM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="uom" required>
                        </div>

                        {{-- <div class="mb-2">
                            <label>Loc <span class="text-danger">*</span></label>

                            <select name="loc_id" id="loc_id" class="form-select" required>
                                <option value="">Pilih Loc</option>

                                @foreach ($location as $loc)
                                    <option value="{{ $loc->id }}">
                    {{ $loc->plant }} - {{ $loc->s_loc }}
                    </option>
                    @endforeach

                    </select>
                </div> --}}

                        <div class="mb-2">
                            <label>Qty KG/Pallet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="qty_kg" required>
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
    <div class="modal fade" id="modalUpload" tabindex="-1">
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
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            loadData();

            $('#statusFilter').change(function() {
                loadData();
            });

            function loadData() {
                let status = $('#statusFilter').val();
                $.get(`/master/wrm/barang/get-data?status=${status}`, function(res) {
                    let html = '';
                    let formNum = new Intl.NumberFormat('id-ID', {
                        maximumFractionDigits: 8
                    });
                    $.each(res.data, function(i, v) {
                        let btnAction = '';
                        if (status === 'trashed') {
                            btnAction = `
                                <button class="btn btn-success btn-sm btnRestore" data-id="${v.id}">Restore</button>
                                <button class="btn btn-danger btn-sm btnForceDelete" data-id="${v.id}">Hapus Permanen</button>
                            `;
                        } else {
                            btnAction = `
                                <button class="btn btn-warning btn-sm btnEdit" data-data='${JSON.stringify(v)}'>Edit</button>
                                <button class="btn btn-danger btn-sm btnHapus" data-id="${v.id}">Hapus</button>
                            `;
                        }

                        html += `
                            <tr>
                                <td class="text-center">${i + 1}</td>
                                <td>${v.mid}</td>
                                <td>${v.nama_barang}</td>
                                <td>${v.uom}</td>
                                <td class="text-end">${v.qty_kg ? formNum.format(v.qty_kg) : '-'}</td>
                                <td>${v.created_by?.nama_lengkap ?? '-'}</td>
                                <td class="text-center">${btnAction}</td>
                            </tr>
                        `;
                    });
                    $('#tableBarang tbody').html(html);
                });
            }

            $('#btnTambah').click(function() {
                $('#formBarang')[0].reset();
                $('#id').val('');
                $('#modalBarang').modal('show');
            });

            $(document).on('click', '.btnEdit', function() {
                let data = $(this).data('data');

                $('#id').val(data.id);
                $('#mid').val(data.mid);
                $('#nama_barang').val(data.nama_barang);
                $('#uom').val(data.uom);
                // $('#loc_id').val(data.loc_id);
                $('#qty_kg').val(data.qty_kg);

                $('#modalBarang').modal('show');
            });

            $('#formBarang').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = `/master/wrm/barang`;
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
                        mid: $('#mid').val(),
                        nama_barang: $('#nama_barang').val(),
                        uom: $('#uom').val(),
                        // loc_id: $('#loc_id').val(),
                        qty_kg: $('#qty_kg').val(),
                    },
                    success: function(res) {
                        $('#modalBarang').modal('hide');
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

            $('#btnUpload').click(function() {
                $('#fileUpload').val('');
                $('#modalUpload').modal('show');
            });

            $('#btnSubmitUpload').click(function() {

                let file = $('#fileUpload')[0].files[0];
                if (!file) {
                    Swal.fire('Oops', 'Pilih file terlebih dahulu', 'warning');
                    return;
                }

                let formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                Swal.fire({
                    title: 'Upload data?',
                    text: file.name,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Upload'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Uploading...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: `/master/wrm/barang/upload`,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            $('#modalUpload').modal('hide');
                            loadData();
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.errors?.join('<br>') ??
                                xhr.responseJSON?.message ??
                                'Upload gagal';

                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal',
                                html: msg
                            });
                        }
                    });
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
                        url: `/master/wrm/barang/delete/${id}`,
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

            $(document).on('click', '.btnRestore', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Pulihkan data?',
                    text: 'Master barang ini akan diaktifkan kembali.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, pulihkan'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/master/wrm/barang/restore/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            loadData();

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message || 'Data berhasil dipulihkan',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });
                });
            });

            $(document).on('click', '.btnForceDelete', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Hapus permanen?',
                    text: 'Data yang dihapus permanen tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus permanen',
                    confirmButtonColor: '#ef4444'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/master/wrm/barang/force-delete/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            loadData();

                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus permanen',
                                text: res.message ||
                                    'Data berhasil dihapus permanen',
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
