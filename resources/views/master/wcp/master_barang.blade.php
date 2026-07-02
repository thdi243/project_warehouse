@extends('layouts.app')

@section('title', '| Master Barang Co Product')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <h5 class="mb-0">Master Barang Co Product (WCP)</h5>
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
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari MID atau Nama Barang...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped text-nowrap" id="tableBarang">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th>UOM</th>
                                    <th class="text-end">Qty Pallet</th>
                                    <th>Dibuat</th>
                                    <th class="text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center" id="pagination"></ul>
                    </nav>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL TAMBAH/EDIT -->
    <div class="modal fade" id="modalBarang" tabindex="-1">
        <div class="modal-dialog">
            <form id="formBarang">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Master Barang WCP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="id">

                        <div class="mb-2">
                            <label for="mid" class="form-label">MID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mid" required>
                        </div>

                        <div class="mb-2">
                            <label for="nama_barang" class="form-label">Nama Barang <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_barang" required>
                        </div>

                        <div class="mb-2">
                            <label for="uom" class="form-label">UOM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="uom" required>
                        </div>

                        <div class="mb-2">
                            <label for="qty_pallet" class="form-label">Qty Full Pallet <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" id="qty_pallet" required>
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
                    <h5 class="modal-title">Upload Master Barang WCP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="d-grid gap-2 mb-3">
                        <small class="fst-italic">Belum punya template?</small>
                        <a href="{{ route('master.wcp.barang.template') }}" class="btn btn-outline-success">
                            <i class="mdi mdi-download"></i> Download Template
                        </a>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="fileUpload">Pilih File Excel</label>
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

            let currentPage = 1;

            loadData();

            function loadData(page = 1, search = '') {
                currentPage = page;
                $.get(`{{ route('master.wcp.barang.get-data') }}`, {
                    page: page,
                    search: search
                }, function(res) {
                    let formNum = new Intl.NumberFormat('id-ID', {
                        maximumFractionDigits: 8
                    });
                    let html = '';
                    let data = res.data.data;
                    if (data && data.length > 0) {
                        $.each(data, function(i, v) {
                            let no = (res.data.current_page - 1) * res.data.per_page + i + 1;
                            html += `
                            <tr>
                                <td class="text-center">${no}</td>
                                <td>${v.mid}</td>
                                <td>${v.nama_barang}</td>
                                <td>${v.uom}</td>
                                <td class="text-end">${formNum.format(v.qty_pallet)}</td>
                                <td>${v.created_by?.nama_lengkap ?? '-'}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm btnEdit" data-data='${JSON.stringify(v)}'>Edit</button>
                                    <button class="btn btn-danger btn-sm btnHapus" data-id="${v.id}">Hapus</button>
                                </td>
                            </tr>
                        `;
                        });
                    } else {
                        html = `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Tidak ada data master barang</td>
                        </tr>
                    `;
                    }
                    $('#tableBarang tbody').html(html);
                    renderPagination(res.data);
                });
            }

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

            $(document).on('click', '#pagination a', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page) {
                    loadData(page, $('#searchInput').val());
                }
            });

            $('#searchInput').on('keyup', function() {
                loadData(1, $(this).val());
            });

            $('#btnTambah').click(function() {
                $('#formBarang')[0].reset();
                $('#id').val('');
                $('#modalBarang').modal('show');
            });

            $(document).on('click', '.btnEdit', function() {
                let data = $(this).data('data');
                let formNum = new Intl.NumberFormat('id-ID', {
                    maximumFractionDigits: 8
                });

                $('#id').val(data.id);
                $('#mid').val(data.mid);
                $('#nama_barang').val(data.nama_barang);
                $('#uom').val(data.uom);
                $('#qty_pallet').val(formNum.format(data.qty_pallet));

                $('#modalBarang').modal('show');
            });

            $('#formBarang').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = `{{ route('master.wcp.barang.store') }}`;
                let method = 'POST';

                if (id) {
                    url = `{{ url('/master/wcp/barang/update') }}/${id}`;
                    method = 'PUT';
                }

                $.ajax({
                    url: url,
                    type: method,
                    data: {
                        _token: '{{ csrf_token() }}',
                        mid: $('#mid').val(),
                        nama_barang: $('#nama_barang').val(),
                        uom: $('#uom').val(),
                        qty_pallet: $('#qty_pallet').val(),
                    },
                    success: function(res) {
                        $('#modalBarang').modal('hide');
                        if (!id) {
                            $('#searchInput').val('');
                            loadData(1, '');
                        } else {
                            loadData(currentPage, $('#searchInput').val());
                        }

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
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan'
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
                        url: `{{ route('master.wcp.barang.upload') }}`,
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
                            $('#searchInput').val('');
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
                        url: `{{ url('/master/wcp/barang/delete') }}/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            loadData(currentPage, $('#searchInput').val());

                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus',
                                text: res.message || 'Data berhasil dihapus',
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
