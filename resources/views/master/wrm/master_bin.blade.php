@extends('layouts.app')

@section('title', '| Master Bin')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <h5 class="mb-0">Master Bin Raw Material</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" id="btnTambah">
                            <i class="mdi mdi-plus"></i> Generate Bin Matrix
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap" id="tableMasterBin">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Location</th>
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
    <div class="modal fade" id="modalBin" tabindex="-1">
        <div class="modal-dialog">
            <form id="formBin">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Bin Matrix</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info" role="alert">
                            <i class="mdi mdi-information-outline me-2"></i>
                            <strong>Cara Kerja:</strong><br>
                            Masukkan jumlah kolom dan level. Sistem akan otomatis membuat kombinasi bin matrix.
                            <br><br>
                            <strong>Contoh:</strong> Kolom 5, Level 4<br>
                            → Akan membuat: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, ... sampai 5.4<br>
                            → <strong>Total: 20 bin</strong>
                        </div>

                        <div class="mb-3">
                            <label>Location <span class="text-danger">*</span></label>
                            <select class="form-control" id="loc_id" required>
                                <option value="">-- Pilih Location --</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}">
                                        {{ $location->plant }} - {{ $location->s_loc }} - {{ $location->zona }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Jumlah Kolom <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="kolom" min="1" placeholder="Contoh: 5"
                                required>
                            <small class="text-muted">Jumlah kolom yang akan dibuat (1, 2, 3, 4, 5)</small>
                        </div>

                        <div class="mb-3">
                            <label>Jumlah Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="level" min="1" placeholder="Contoh: 4"
                                required>
                            <small class="text-muted">Jumlah level per kolom (1.1, 1.2, 1.3, 1.4)</small>
                        </div>

                        <div class="alert alert-warning" role="alert">
                            <small>
                                <i class="mdi mdi-alert me-1"></i>
                                Dengan Kolom <span id="previewKolom">-</span> dan Level <span id="previewLevel">-</span>,
                                akan menghasilkan <strong><span id="previewTotal">0</span> bin</strong>
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Generate</button>
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
                $.get('/wrm/master/bin/get-data', function(res) {
                    let html = '';
                    $.each(res.data, function(i, v) {
                        html += `
                            <tr>
                                <td class="text-center">${i + 1}</td>
                                <td>${v.location?.plant ?? '-'} - ${v.location?.s_loc ?? '-'} - ${v.location?.zona ?? '-'}</td>
                                <td>${v.bin}</td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm btnHapus" data-id="${v.id}">Hapus</button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#tableMasterBin tbody').html(html);
                });
            }

            $('#btnTambah').click(function() {
                $('#formBin')[0].reset();
                updatePreview();
                $('#modalBin').modal('show');
            });

            // Live preview calculation
            $('#kolom, #level').on('input', function() {
                updatePreview();
            });

            function updatePreview() {
                let kolom = parseInt($('#kolom').val()) || 0;
                let level = parseInt($('#level').val()) || 0;
                let total = kolom * level;

                $('#previewKolom').text(kolom || '-');
                $('#previewLevel').text(level || '-');
                $('#previewTotal').text(total);
            }

            $('#formBin').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: '/wrm/master/bin/store',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        loc_id: $('#loc_id').val(),
                        kolom: $('#kolom').val(),
                        level: $('#level').val(),
                    },
                    success: function(res) {
                        $('#modalBin').modal('hide');
                        loadData();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan';
                        if (xhr.responseJSON?.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors)
                                .flat()
                                .join('\n');
                        } else if (xhr.responseJSON?.message) {
                            errorMsg = xhr.responseJSON.message;
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
                    title: 'Yakin hapus?',
                    text: 'Bin ini akan dihapus dari sistem',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '/wrm/master/bin/delete/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            loadData();

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
