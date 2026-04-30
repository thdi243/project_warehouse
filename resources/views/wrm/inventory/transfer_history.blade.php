@extends('layouts.app')

@section('title', ' | Stock Transfer History')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Card Filter --}}
        <div class="card mb-3 shadow-none border">
            <div class="card-header bg-transparent">
                <h5 class="mb-0 fw-bold">Filter Riwayat Transfer</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">MID</label>
                        <input type="text" class="form-control" id="filterMid" placeholder="Cari MID">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">No. Reservasi / BA</label>
                        <input type="text" class="form-control" id="filterNoReservasi" placeholder="Cari Res / BA">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Reservasi</label>
                        <input type="date" class="form-control" id="filterDate">
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-primary w-100" id="btnFilter">
                            <i class="mdi mdi-magnify me-1"></i> Cari
                        </button>
                        <button class="btn btn-outline-secondary w-100" id="btnReset">
                            <i class="mdi mdi-refresh me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Riwayat Stock Transfer (WRM)</h5>
                <a href="{{ route('wrm.inventory.index-upload') }}" class="btn btn-primary">
                    <i class="mdi mdi-upload"></i> Upload Baru
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-nowrap" id="tableTransfer">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>No. BA / Tgl</th>
                                <th>No. Reservasi / Tgl</th>
                                <th>No Barcode</th>
                                <th>No SPB</th>
                                <th>Matdoc Scrap / Year</th>
                                <th>Tgl GR</th>
                                <th>Tgl GI</th>
                                <th>Matdoc GI</th>
                                <th>MID</th>
                                <th>Nama Barang</th>
                                <th>Qty Barcode</th>
                                <th>Qty Actual</th>
                                <th>Qty Susut</th>
                                <th>Lama Simpan</th>
                                <th>% Susut</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div id="paginationInfo" class="text-muted small"></div>
                    <div id="pagination"></div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function numberFormat(x) {
        if (x === null || x === undefined) return '0';
        let val = parseFloat(x);
        return val.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    // Move globally for access
    function deleteTransfer(id) {
        Swal.fire({
            title: 'Hapus Data Transfer?',
            text: "Data inventory (Balance, Inbound, Draft) akan dikembalikan ke status sebelumnya.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('/wrm/inventory/transfer-detail/delete') }}/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.loadData();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        window.loadData = function(page = 1) {
            let mid = $('#filterMid').val();
            let noRes = $('#filterNoReservasi').val();
            let date = $('#filterDate').val();

            $.get("{{ route('wrm.inventory.get-transfer-data') }}", {
                page: page,
                mid: mid,
                no_reservasi: noRes,
                date: date
            }, function(res) {
                let html = '';
                let data = res.data.data;
                let startNo = res.data.from || 0;

                if (data.length === 0) {
                    html = `
                        <tr>
                            <td colspan="15" class="text-center text-muted py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="mdi mdi-database-off-outline text-secondary" style="font-size:32px"></i>
                                    <span class="mt-2">Data tidak ditemukan</span>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    data.forEach((d, index) => {
                        let header = d.header || {};
                        let barang = d.barang || {};

                        html += `
                            <tr>
                                <td class="text-center">${startNo + index}</td>
                                <td>
                                    <span class="fw-bold">${header.no_ba || '-'}</span><br>
                                    <small class="text-muted">${header.tgl_ba ? moment(header.tgl_ba).format('DD/MM/YYYY') : '-'}</small>
                                </td>
                                <td>
                                    <span class="fw-bold">${header.no_reservasi || '-'}</span><br>
                                    <small class="text-muted">${header.tgl_reservasi ? moment(header.tgl_reservasi).format('DD/MM/YYYY') : '-'}</small>
                                </td>
                                <td>${d.no_barcode || '-'}</td>
                                <td>${d.no_spb || '-'}</td>
                                <td>${d.matdoc_scrup || '-'} <br><small class="text-muted">${d.matdoc_year || ''}</small></td>
                                <td>${header.tgl_gr ? moment(header.tgl_gr).format('DD/MM/YYYY') : '-'}</td>
                                <td>${header.tgl_gi ? moment(header.tgl_gi).format('DD/MM/YYYY') : '-'}</td>
                                <td>${header.matdoc_gi || '-'}</td>
                                <td>${barang.mid || '-'}</td>
                                <td class="text-wrap" style="min-width:150px">${barang.nama_barang || '-'}</td>
                                <td>${numberFormat(d.qty_barcode)}</td>
                                <td class="fw-bold text-primary">${numberFormat(d.qty_actual)}</td>
                                <td class="text-danger">${numberFormat(d.qty_susut_simpan)}</td>
                                <td>${d.lama_simpan || 0}</td>
                                <td>${d.persen_susut || 0}%</td>
                                <td class="text-center">
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteTransfer(${d.id})">
                                        <i class="mdi mdi-trash-can-outline"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#tableTransfer tbody').html(html);
                renderPagination(res.data);

                if (res.data.total > 0) {
                    $('#paginationInfo').text(`Showing ${res.data.from} to ${res.data.to} of ${res.data.total} entries`);
                } else {
                    $('#paginationInfo').text('');
                }
            });
        }

        // Initial Load
        loadData();

        // Filter Handlers
        $('#btnFilter').click(() => loadData());
        $('#btnReset').click(() => {
            $('#filterMid').val('');
            $('#filterNoReservasi').val('');
            $('#filterDate').val('');
            loadData();
        });

        function renderPagination(data) {
            let html = '';
            let current = data.current_page;
            let last = data.last_page;

            if (last <= 1) {
                $('#pagination').html('');
                return;
            }

            html += `<button class="btn btn-sm btn-light border mx-1 page-btn" data-page="${current-1}" ${current==1?'disabled':''}>Prev</button>`;

            let start = Math.max(1, current - 2);
            let end = Math.min(last, current + 2);

            for (let i = start; i <= end; i++) {
                html += `
                    <button class="btn btn-sm ${i==current?'btn-primary':'btn-light border'} mx-1 page-btn"
                    data-page="${i}">
                    ${i}
                    </button>
                `;
            }

            html += `<button class="btn btn-sm btn-light border mx-1 page-btn" data-page="${current+1}" ${current==last?'disabled':''}>Next</button>`;

            $('#pagination').html(html);
        }

        $(document).on('click', '.page-btn', function() {
            if ($(this).attr('disabled')) return;
            let page = $(this).data('page');
            loadData(page);
        });

    });
</script>
@endsection