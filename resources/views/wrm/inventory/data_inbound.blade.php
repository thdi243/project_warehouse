@extends('layouts.app')

@section('styles')
<style>
    .select2-container--bootstrap-5 .select2-selection {
        font-size: 0.85rem !important;
        min-height: 38px !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--bootstrap-5 .select2-dropdown .select2-results__options {
        font-size: 0.85rem !important;
        max-height: 250px !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 0.75rem !important;
    }

    /* Style for Multiple Select choices */
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd !important;
        color: #fff !important;
        border: none !important;
        font-size: 0.75rem !important;
        padding: 2px 8px !important;
        border-radius: 4px !important;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff !important;
        margin-right: 5px !important;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffc107 !important;
        background-color: transparent !important;
    }

    /* Soft Buttons */
    .btn-soft-info {
        background-color: rgba(53, 185, 230, 0.1);
        color: #35b9e6;
        border: none;
    }

    .btn-soft-info:hover {
        background-color: #35b9e6;
        color: #fff;
    }

    .btn-soft-danger {
        background-color: rgba(240, 101, 72, 0.1);
        color: #f06548;
        border: none;
    }

    .btn-soft-danger:hover {
        background-color: #f06548;
        color: #fff;
    }

    #tableInbound tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
@endsection

@section('title', ' | Data Inbound RM')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Card Filter --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-muted mb-2">Pencarian Cepat</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify text-primary"></i></span>
                            <input type="text" class="form-control bg-light border-start-0" id="filterCatatan" placeholder="Cari barcode atau catatan...">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-muted mb-2">No SPB</label>
                        <select class="form-select select2-filter" id="filterNoSpb" multiple>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-muted mb-2">Nama Barang</label>
                        <select class="form-select select2-filter" id="filterNamaBarang" multiple>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button class="btn btn-soft-info w-100" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvancedFilter" aria-expanded="false" aria-controls="collapseAdvancedFilter">
                                <i class="mdi mdi-tune-vertical me-1"></i> Filter Lanjutan
                            </button>
                            <button class="btn btn-soft-danger w-100" id="btnReset" title="Reset Filter">
                                <i class="mdi mdi-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Collapsible Advanced Filters --}}
                <div class="collapse" id="collapseAdvancedFilter">
                    <div class="pt-4 mt-4 border-top">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted mb-2">MID</label>
                                <select class="form-select select2-filter" id="filterMid" multiple>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted mb-2">Supplier</label>
                                <select class="form-select select2-filter" id="filterSupplier" multiple>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted mb-2">Group</label>
                                <select class="form-select select2-filter" id="filterGroup" multiple>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted mb-2">Status</label>
                                <select class="form-select select2-filter" id="filterStatus" multiple>
                                    <option value="UNREST">UNREST</option>
                                    <option value="QI">QI</option>
                                    <option value="BLOCKED">BLOCKED</option>
                                    <option value="ISSUED">ISSUED</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted mb-2">Lokasi</label>
                                <select class="form-select select2-filter" id="filterLocation" multiple>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted mb-2">Incoming Date</label>
                                <input type="date" class="form-control" id="filterDate">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Historical Inbound Data</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('wrm.inventory.index-upload') }}" class="btn btn-primary">
                        <i class="mdi mdi-upload me-1"></i> New Inbound
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-nowrap" id="tableInbound">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Barcode</th>
                                <th>No SPB</th>
                                <th>Mid</th>
                                <th>Nama Barang</th>
                                <th>Uom</th>
                                <th>Group</th>
                                <th>Pallet ID</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Supplier</th>
                                <th class="text-nowrap cursor-pointer" id="sortDate">
                                    Incoming Date <i class="mdi mdi-sort ms-1" id="sortIcon"></i>
                                </th>
                                <th>Catatan</th>
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
    function numberFormat(x) {
        if (x === null || x === undefined) return '0';
        let val = parseFloat(x);
        return val.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    $(document).ready(function() {
        // Filter selects
        $('.select2-filter').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih...',
            allowClear: true,
            closeOnSelect: false
        });

        let currentSortDir = 'desc';

        loadData();
        loadFilter();

        function loadData(page = 1) {
            let params = {
                page: page,
                group: $('#filterGroup').val(),
                jenis_bahan: $('#filterNamaBarang').val(),
                mid: $('#filterMid').val(),
                date: $('#filterDate').val(),
                supplier: $('#filterSupplier').val(),
                status: $('#filterStatus').val(),
                no_spb: $('#filterNoSpb').val(),
                location: $('#filterLocation').val(),
                catatan: $('#filterCatatan').val(),
                sort_dir: currentSortDir,
            };

            $.get("{{ route('wrm.inventory.dataInbound') }}", params, function(res) {
                let html = '';
                let data = res.data.data;
                let startNo = res.data.from;

                if (data.length === 0) {
                    html = `
                        <tr>
                            <td colspan="14" class="text-center text-muted py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="mdi mdi-database-off-outline" style="font-size:32px"></i>
                                    <span class="mt-2">Data tidak ditemukan</span>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    data.forEach((d, index) => {
                        let locationText = 'N/A';
                        if (d.bin && d.bin.location) {
                            let loc = d.bin.location;
                            locationText = `${loc.plant} - ${loc.s_loc} - ${loc.gudang} - ${loc.bin} (${d.bin.kolom}.${d.bin.level})`;
                        }

                        html += `
                            <tr>
                                <td class="text-center">${startNo + index}</td>
                                <td>${d.barcode ?? ''}</td>
                                <td>${d.inbound ? d.inbound.no_spb : ''}</td>
                                <td>${d.barang ? d.barang.mid : ''}</td>
                                <td>${d.barang ? d.barang.nama_barang : ''}</td>
                                <td>${d.barang ? d.barang.uom : ''}</td>
                                <td>${d.group ?? ''}</td>
                                <td>${d.pallet_id ?? ''}</td>
                                <td>${numberFormat(d.qty)}</td>
                                <td><span class="badge ${getStatusBadge(d.status)}">${d.status}</span></td>
                                <td>${locationText}</td>
                                <td>${d.inbound ? (d.inbound.supplier ?? '') : ''}</td>
                                <td>${d.inbound ? d.inbound.incoming_date : ''}</td>
                                <td>${d.catatan ?? ''}</td>
                            </tr>
                        `;
                    });
                }

                $('#tableInbound tbody').html(html);
                renderPagination(res.data);
            });
        }

        function getStatusBadge(status) {
            switch(status) {
                case 'UNREST': return 'bg-success';
                case 'QI': return 'bg-info';
                case 'BLOCKED': return 'bg-danger';
                case 'ISSUED': return 'bg-secondary';
                default: return 'bg-light text-dark';
            }
        }

        function loadFilter() {
            $.get("{{ route('wrm.inventory.getFilterInbound') }}", function(res) {
                // No SPB
                let optSpb = '';
                res.no_spbs.forEach(v => optSpb += `<option value="${v}">${v}</option>`);
                $('#filterNoSpb').html(optSpb);

                // Nama Barang
                let optNama = '';
                res.jenis_bahan.forEach(v => optNama += `<option value="${v}">${v}</option>`);
                $('#filterNamaBarang').html(optNama);

                // MID
                let optMid = '';
                res.mids.forEach(v => optMid += `<option value="${v.mid}">${v.text}</option>`);
                $('#filterMid').html(optMid);

                // Supplier
                let optSup = '';
                res.suppliers.forEach(v => optSup += `<option value="${v}">${v}</option>`);
                $('#filterSupplier').html(optSup);

                // Group
                let optGroup = '';
                res.groups.forEach(v => optGroup += `<option value="${v}">${v}</option>`);
                $('#filterGroup').html(optGroup);

                // Location
                let optLoc = '';
                res.locations.forEach(v => optLoc += `<option value="${v.id}">${v.text}</option>`);
                $('#filterLocation').html(optLoc);
            });
        }

        function renderPagination(data) {
            let html = `
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${data.current_page - 1}">Previous</a>
                        </li>
            `;

            let start = Math.max(1, data.current_page - 2);
            let end = Math.min(data.last_page, data.current_page + 2);

            for (let i = start; i <= end; i++) {
                html += `
                    <li class="page-item ${i === data.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }

            html += `
                        <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a>
                        </li>
                    </ul>
                </nav>
            `;
            $('#pagination').html(html);
        }

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            if (page) loadData(page);
        });

        // Trigger filters
        let filterTimeout;
        $('.select2-filter, #filterDate').on('change', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => loadData(1), 500);
        });

        $('#filterCatatan').on('keyup', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => loadData(1), 500);
        });

        $('#btnReset').click(function() {
            $('.select2-filter').val(null).trigger('change');
            $('#filterDate').val('');
            $('#filterCatatan').val('');
            loadData(1);
        });

        $('#sortDate').click(function() {
            currentSortDir = currentSortDir === 'desc' ? 'asc' : 'desc';
            $('#sortIcon').attr('class', `mdi mdi-sort-${currentSortDir === 'asc' ? 'ascending' : 'descending'} ms-1`);
            loadData(1);
        });
    });
</script>
@endsection