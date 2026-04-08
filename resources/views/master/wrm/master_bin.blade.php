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
                        <thead class="table-light align-middle">
                            <tr>
                                <th class="text-center align-middle">No</th>
                                <th class="align-middle">Location</th>
                                <th class="align-middle">Zona</th>
                                <th class="align-middle">Bin Location</th>
                                <th class="align-middle">Bin
                                    <br>
                                    <small class="text-muted">Kolom.Level</small>
                                </th>
                                <th class="text-center align-middle">Aksi Bin</th>
                                <th class="text-center align-middle">Aksi Semua</th>
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
                        Pilih zona-bin dari location, lalu masukkan jumlah kolom dan level.
                        Sistem akan otomatis membuat kombinasi bin matrix.
                        <br><br>
                        <strong>Contoh:</strong> Kolom 5, Level 4<br>
                        → Akan membuat: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, ... sampai 5.4<br>
                        → <strong>Total: 20 bin</strong>
                    </div>

                    <div class="mb-3">
                        <label>Zona - Bin <span class="text-danger">*</span></label>
                        <select class="form-control" id="loc_id" required>
                            <option value="">-- Pilih Zona - Bin --</option>
                            @foreach ($locations as $location)
                            <option value="{{ $location->id }}">
                                {{ $location->plant }} | {{ $location->s_loc }} | {{ $location->gudang }} | Zona: {{ $location->zona }} | Bin: {{ $location->bin }}
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
        let currentPage = 1;

        loadData(currentPage);

        function loadData(page = 1) {
            currentPage = page;
            $.get(`/master/wrm/bin/get-data?page=${page}`, function(res) {
                let html = '';
                let data = res.data.data; // Paginated data

                let groupedData = {};
                $.each(data, function(i, v) {
                    let locId = v.loc_id || 'unknown';
                    if (!groupedData[locId]) {
                        groupedData[locId] = [];
                    }
                    groupedData[locId].push(v);
                });

                let no = res.data.from || 1;
                $.each(groupedData, function(locId, bins) {
                    let loc = bins[0].location ?? {};
                    let rowspan = bins.length;

                    $.each(bins, function(index, v) {
                        html += `<tr>`;

                        if (index === 0) {
                            html += `
                                <td class="text-center align-top" rowspan="${rowspan}">${no++}</td>
                                <td class="align-top" rowspan="${rowspan}">${loc.plant ?? '-'} - ${loc.s_loc ?? '-'}</td>
                                <td class="align-top" rowspan="${rowspan}">${loc.zona ?? '-'}</td>
                                <td class="align-top" rowspan="${rowspan}">${loc.bin ?? '-'}</td>
                            `;
                        }

                        html += `
                                <td class="align-top">${v.kolom}.${v.level}</td>
                                <td class="text-center align-top">
                                    <button class="btn btn-danger btn-sm btnHapus" data-id="${v.id}" title="Hapus bin ini">
                                        <i class="mdi mdi-delete"></i> Hapus
                                    </button>
                                </td>
                        `;

                        if (index === 0) {
                            html += `
                                <td class="text-center align-top" rowspan="${rowspan}">
                                    <button class="btn btn-outline-danger btn-sm btnHapusGrup" data-loc-id="${v.loc_id}" title="Hapus semua bin pada zona-bin ini">
                                        <i class="mdi mdi-delete-sweep"></i> Hapus Semua
                                    </button>
                                </td>
                            `;
                        }

                        html += `</tr>`;
                    });
                });
                $('#tableMasterBin tbody').html(html);
                renderPagination(res.data);
            });
        }

        function renderPagination(meta) {
            let html = `<div>Showing ${meta.from ?? 0} to ${meta.to ?? 0} of ${meta.total} entries</div>`;
            html += `<nav><ul class="pagination pagination-sm mb-0">`;

            // Previous
            html += `<li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadData(${meta.current_page - 1})">Previous</a>
                    </li>`;

            // Page Numbers (Simple logic for now: show surrounding pages)
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

            // Next
            html += `<li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadData(${meta.current_page + 1})">Next</a>
                    </li>`;

            html += `</ul></nav>`;
            $('#pagination-container').html(html);
        }

        window.loadData = loadData; // Make it global so onclick works

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
                url: `/master/wrm/bin/store`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    loc_id: $('#loc_id').val(),
                    kolom: $('#kolom').val(),
                    level: $('#level').val(),
                },
                success: function(res) {
                    $('#modalBin').modal('hide');
                    // loadData();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    window.location.reload();
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

        // Hapus single bin
        $(document).on('click', '.btnHapus', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Hapus bin ini?',
                text: 'Bin ini akan dihapus dari sistem',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/master/wrm/bin/delete/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        loadData(currentPage);
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus',
                            text: res.message || 'Bin berhasil dihapus',
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
            });
        });

        // Hapus semua bin dalam satu zona-bin (loc_id)
        $(document).on('click', '.btnHapusGrup', function() {
            let locId = $(this).data('loc-id');

            Swal.fire({
                title: 'Hapus semua bin?',
                text: 'Semua bin dalam zona-bin ini akan dihapus. Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus semua',
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/master/wrm/bin/delete-by-loc/${locId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus',
                            text: res.message || 'Semua bin berhasil dihapus',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        window.location.reload();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                        });
                    }
                });
            });
        });
    });
</script>
@endsection