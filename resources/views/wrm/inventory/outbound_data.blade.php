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
                                    <th>Mid</th>
                                    <th>Nama Barang</th>
                                    <th>Uom</th>
                                    <th>Group</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Incoming Date</th>
                                    <th>Issued Date</th>
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
                                    <td>${d.outbound.no_spb}</td>
                                    <td>${d.barang.mid}</td>
                                    <td>${d.barang.nama_barang}</td>
                                    <td>${d.barang.uom}</td>
                                    <td>${d.group}</td>
                                    <td>${d.qty}</td>
                                    <td>${d.status.toUpperCase()}</td>
                                    <td>${d.location.plant} - ${d.location.gudang} - ${d.location.bin}</td>
                                    <td>${d.outbound.incoming_date}</td>
                                    <td>${d.outbound.issued_date}</td>
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

            $('#btnReset').click(function() {

                $('#filterGroup').val('');
                $('#filterJenisBahan').val('');
                $('#filterMid').val('');

                loadData();

            });
        })
    </script>
@endsection
