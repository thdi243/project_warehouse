@extends('layouts.app')

@section('title', ' | Outbound Inventory Data')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            {{-- Card Filter --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Filter Data</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Group</label>
                            <select class="form-select" id="filterGroup">
                                <option value="">Semua Group</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Jenis Bahan</label>
                            <select class="form-select" id="filterJenisBahan">
                                <option value="">Semua Jenis Bahan</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">MID</label>
                            <input type="text" class="form-control" id="filterMid" placeholder="Cari MID">
                        </div>

                        <div class="col-md-3 d-flex align-items-end gap-2 text-nowrap">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="collapse"
                                data-bs-target="#advancedFilter">
                                <i class="mdi mdi-filter-plus"></i>
                            </button>
                            <button class="btn btn-primary w-100" id="btnReset">
                                <i class="mdi mdi-refresh me-2"></i>Reset
                            </button>
                        </div>
                    </div>

                    <div class="collapse mt-3" id="advancedFilter">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <label class="form-label">Issued Date</label>
                                <input type="date" class="form-control" id="filterDate" placeholder="Cari MID">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Supplier</label>
                                <input type="text" class="form-control" id="filterSupplier">
                            </div>

                            {{-- <div class="col-md-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" id="filterLocation">
                            </div> --}}

                        </div>

                    </div>
                </div>

            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Raw Material Stock Outbound</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap" id="tableStock">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>No SPB</th>
                                    <th>Supplier</th>
                                    {{-- <th>Mid</th>
                                    <th>UoM</th> --}}
                                    <th>Incoming Date</th>
                                    <th>Issued Date</th>
                                    <th>Qty Request</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-end" id="pagination"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal detail --}}
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Detail Outbound
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Pallet ID</th>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th>Group</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- isi dari ajax -->
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadData();
            loadFilter();

            function loadData(page = 1) {

                let group = $('#filterGroup').val();
                let jenisBahan = $('#filterJenisBahan').val();
                let mid = $('#filterMid').val();
                let date = $('#filterDate').val();
                let supplier = $('#filterSupplier').val();

                $.get("{{ route('wrm.inventory.get-data-outbound') }}", {
                    page: page,
                    group: group,
                    jenis_bahan: jenisBahan,
                    mid: mid,
                    date: date,
                    supplier: supplier,
                }, function(res) {

                    let html = '';
                    let data = res.data.data;
                    let startNo = res.data.from;

                    if (data.length === 0) {

                        html = `
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="mdi mdi-database-off-outline" style="font-size:32px"></i>
                                        <span class="mt-2">Data tidak ditemukan</span>
                                    </div>
                                </td>
                            </tr>
                        `;

                    } else {

                        data.forEach((d, index) => {

                            html += `
                                <tr>
                                    <td class="text-center">${startNo + index}</td>
                                    <td>${d.no_spb}</td>
                                    <td>${d.supplier}</td>
                                    <td>${d.incoming_date}</td>
                                    <td>${d.issued_date}</td>
                                    <td>${d.qty_request}</td>
                                    <td>${d.catatan}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info btnDetail"
                                            data-id="${d.id}">
                                            <i class="mdi mdi-eye"></i> Detail
                                        </button>

                                        <button class="btn btn-sm btn-danger btnCancel"
                                            data-id="${d.id}">
                                            <i class="mdi mdi-close"></i> Cancel Trans
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $('#tableStock tbody').html(html);

                    renderPagination(res.data);

                });
            }

            function renderPagination(data) {

                let html = '';

                let current = data.current_page;
                let last = data.last_page;

                html +=
                    `<button class="btn btn-sm btn-light page-btn" data-page="${current-1}" ${current==1?'disabled':''}>Prev</button>`;

                let start = Math.max(1, current - 2);
                let end = Math.min(last, current + 2);

                if (start > 1) {
                    html += `<button class="btn btn-sm btn-light page-btn" data-page="1">1</button>`;
                    if (start > 2) html += `<span class="mx-1">...</span>`;
                }

                for (let i = start; i <= end; i++) {

                    html += `
                        <button class="btn btn-sm ${i==current?'btn-primary':'btn-light'} page-btn"
                        data-page="${i}">
                        ${i}
                        </button>
                    `;
                }

                if (end < last) {

                    if (end < last - 1) html += `<span class="mx-1">...</span>`;

                    html += `<button class="btn btn-sm btn-light page-btn" data-page="${last}">${last}</button>`;
                }

                html +=
                    `<button class="btn btn-sm btn-light page-btn" data-page="${current+1}" ${current==last?'disabled':''}>Next</button>`;

                $('#pagination').html(html);
            }

            $(document).on('click', '.page-btn', function() {

                let page = $(this).data('page');

                loadData(page);

            });

            $('#filterGroup, #filterJenisBahan, #filterDate, #filterSupplier').on('change', function() {
                loadData();
            });

            let typingTimer;

            $('#filterMid').on('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    loadData();
                }, 500);
            });

            function loadFilter() {

                $.get("{{ route('wrm.inventory.getFilter') }}", function(res) {

                    let groupHtml = `<option value="">Semua Group</option>`;
                    res.groups.forEach(g => {
                        groupHtml += `<option value="${g}">${g}</option>`;
                    });

                    $('#filterGroup').html(groupHtml);

                    let jenisHtml = `<option value="">Semua Jenis Bahan</option>`;
                    res.jenis_bahan.forEach(j => {
                        jenisHtml += `<option value="${j}">${j}</option>`;
                    });

                    $('#filterJenisBahan').html(jenisHtml);

                });

            }

            $(document).on('click', '.btnDetail', function() {

                let id = $(this).data('id');

                $.get(`/wrm/inventory/detail-data-outbound/${id}`, function(res) {

                    let html = '';

                    res.data.forEach((d, i) => {

                        html += `
                            <tr>
                                <td>${i+1}</td>
                                <td>${d.pallet_id}</td>
                                <td>${d.barang.mid}</td>
                                <td>${d.barang.nama_barang}</td>
                                <td>${d.group}</td>
                                <td>${d.qty}</td>
                                <td>${d.status}</td>
                                <td>${d.bin.location.plant} - ${d.bin.location.gudang} - ${d.bin.bin}</td>
                            </tr>
                        `;
                    });

                    $('#modalDetail tbody').html(html);

                    $('#modalDetail').modal('show');

                });

            });

            $('#btnReset').click(function() {

                $('#filterGroup').val('');
                $('#filterJenisBahan').val('');
                $('#filterMid').val('');

                loadData();

            });

            $(document).on('click', '.btnCancel', function() {

                let id = $(this).data('id');

                Swal.fire({
                    title: 'Cancel Transfer?',
                    icon: 'warning',
                    showCancelButton: true
                }).then((r) => {

                    if (r.isConfirmed) {

                        $.post(`/wrm/inventory/cancel-outbound/${id}`, {
                            _token: "{{ csrf_token() }}"
                        }, function(res) {

                            Swal.fire('Berhasil', res.message, 'success');

                            loadData();

                        });

                    }

                });

            });
        })
    </script>
@endsection
