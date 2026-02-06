@extends('layouts.app')

@section('title', 'Stock Gula')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">List Inventory Stock Gula</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" id="btnUpload">
                            <i class="mdi mdi-upload"></i> Upload
                        </button>
                        <button class="btn btn-primary" id="btnTambah">
                            <i class="mdi mdi-plus"></i> Tambah
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap" id="tableStock">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>No SPB</th>
                                    <th>Mid</th>
                                    <th>Nama Barang</th>
                                    <th>Uom</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Incoming Date</th>
                                    @can('permission', 'stock-gula-plus')
                                        <th class="text-center">Aksi</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL FORM --}}
    <div class="modal fade" id="modalForm">
        <div class="modal-dialog modal-lg">
            <form id="formStock">
                @csrf
                <input type="hidden" id="id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titleForm">Form Stock Gula</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">MID / Nama Barang <span class="text-danger">*</span></label>
                            <select class="form-select" name="barang_id" id="barang_id" required>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">No SPB <span class="text-danger">*</span></label>
                            <input type="number" name="no_spb" class="form-control" id="no_spb" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Qty <span class="text-danger">*</span></label>
                            <input type="number" name="qty" class="form-control" id="qty" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Incoming Date <span class="text-danger">*</span></label>
                            <input type="date" name="incoming_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="" selected>Pilih Status</option>
                                <option value="unrest">Unrest</option>
                                <option value="blocked">Blocked</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gudang <span class="text-danger">*</span></label>
                            <input type="text" name="gudang" class="form-control" id="gudang" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier <span class="text-danger">*</span></label>
                            <input type="text" name="supplier" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pallet <span class="text-danger">*</span></label>
                            <input type="text" name="pallet" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expired Date</label>
                            <input type="date" name="expired_date" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSave">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL UPLOAD --}}
    <div class="modal fade" id="modalUpload">
        <div class="modal-dialog">
            <form id="formUpload" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Upload Stock Gula</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-grid gap-2 mb-2">
                            <small class="fst-italic">Belum punya template?</small>
                            <a href="{{ route('wrm.stock_gula.template') }}" class="btn btn-outline-success mb-3">
                                <i class="mdi mdi-download"></i>Download Template
                            </a>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Pilih file</label>
                            <input type="file" name="file" class="form-control" accept=".xls,.xlsx">
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-primary">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        $(document).ready(function() {

            $('#barang_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#modalForm'),
                placeholder: 'Cari MID / Nama Barang...',
                allowClear: true,

                ajax: {
                    url: '/stock-gula/get-barang',
                    dataType: 'json',
                    delay: 250,

                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },

                    processResults: function(res) {
                        return {
                            results: res.data
                        };
                    },

                    cache: true
                },

                templateResult: function(data) {
                    if (!data.id) return data.text;

                    return $(`
                        <div>
                            <strong>${data.mid}</strong>
                            <small class="text-muted"> - ${data.nama_barang}</small>
                        </div>
                    `);
                },

                templateSelection: function(data) {
                    return data.mid ?
                        `${data.mid} - ${data.nama_barang}` :
                        data.text;
                }
            });

            loadData();

            function loadData() {
                $.get("{{ route('wrm.stock_gula.getData') }}", function(res) {
                    let html = '';
                    res.data.forEach((v, index) => {
                        html += `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${v.no_spb}</td>
                                <td>${v.barang.mid}</td>
                                <td>${v.barang.nama_barang}</td>
                                <td>${v.barang.uom}</td>
                                <td>${v.qty}</td>
                                <td>${v.status}</td>
                                <td>${v.incoming_date}</td>
                                @can('permission', 'stock-gula-plus')
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning btnEdit" data-id="${v.id}" data-data='${JSON.stringify(v)}'>Edit</button>
                                        <button class="btn btn-sm btn-danger btnDelete" data-id="${v.id}">Hapus</button>
                                    </td>
                                @endcan
                            </tr>
                        `;
                    });
                    $('#tableStock tbody').html(html);
                });
            }

            $('#btnTambah').click(() => {
                $('#formStock')[0].reset();
                $('#id').val('');
                $('#modalForm').modal('show');
            });

            $(document).on('click', '.btnEdit', function() {

                let data = $(this).data('data');

                $('#titleForm').text('Edit Stock Gula');
                $('#id').val(data.id);

                const option = new Option(
                    `${data.barang.mid} - ${data.barang.nama_barang}`,
                    data.barang.id,
                    true,
                    true
                );

                $('#barang_id')
                    .append(option)
                    .trigger('change');

                // ===== INPUT LAIN =====
                $('#no_spb').val(data.no_spb);
                $('#qty').val(data.qty);
                $('input[name="incoming_date"]').val(data.incoming_date);
                $('select[name="status"]').val(data.status);
                $('#gudang').val(data.gudang);
                $('input[name="supplier"]').val(data.supplier);
                $('input[name="location"]').val(data.location);
                $('input[name="pallet"]').val(data.pallet);
                $('input[name="expired_date"]').val(data.expired_date);
                $('textarea[name="catatan"]').val(data.catatan);

                $('#modalForm').modal('show');
            });


            $('#formStock').submit(function(e) {
                e.preventDefault();
                let id = $('#id').val();
                let url = '/stock-gula';
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
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Data berhasil disimpan',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#modalForm').modal('hide');
                        loadData();
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

            $(document).on('click', '.btnDelete', function() {
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
                        url: '/stock-gula/delete/' + id,
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

            $('#btnUpload').click(() => $('#modalUpload').modal('show'));

            $('#formUpload').submit(function(e) {
                e.preventDefault();

                const form = this;
                const btn = $(form).find('.modal-footer .btn-primary');

                let fd = new FormData(this);

                btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Uploading...');
                $.ajax({
                    url: `/stock-gula/upload`,
                    method: 'POST',
                    data: fd,
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
                    },
                    complete: function() {
                        // 🔓 enable lagi (selalu dipanggil)
                        btn.prop('disabled', false)
                            .html('Upload');
                    }
                });
            });
        })
    </script>
@endsection
