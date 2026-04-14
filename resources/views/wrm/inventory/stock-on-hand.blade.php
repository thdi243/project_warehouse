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
</style>
@endsection

@section('title', ' | Stock On Hand RM')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Card Summary --}}
        <div class="row g-3 mb-3" id="summarySection">
            <div class="col-md-3">
                <!-- <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);"> -->
                <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-primary">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Total Inventory</h6>
                                <h3 class="mb-1 fw-bold" id="totalQty">0</h3>
                                <div class="small">
                                    <i class="mdi mdi-layers-outline me-1"></i> <span id="totalPalletsDisplay">0</span> Pallets
                                </div>
                            </div>
                            <div class="bg-soft-primary rounded-3 p-2">
                                <i class="mdi mdi-database-outline text-white mdi-36px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <!-- <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);"> -->
                <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-success">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Unrest</h6>
                                <h3 class="mb-1 fw-bold" id="unrestQty">0</h3>
                                <div class="small">
                                    <i class="mdi mdi-layers-outline me-1"></i> <span id="unrestPallets">0</span> Pallets
                                </div>
                            </div>
                            <div class="bg-soft-success rounded-3 p-2">
                                <i class="mdi mdi-check-circle-outline mdi-36px text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-info">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">QI</h6>
                                <h3 class="mb-1 fw-bold" id="qiQty">0</h3>
                                <div class="small">
                                    <i class="mdi mdi-layers-outline me-1"></i> <span id="qiPallets">0</span> Pallets
                                </div>
                            </div>
                            <div class="bg-soft-info rounded-3 p-2">
                                <i class="mdi mdi-flask-outline text-white mdi-36px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-danger">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Blocked</h6>
                                <h3 class="mb-1 fw-bold" id="blockedQty">0</h3>
                                <div class="small">
                                    <i class="mdi mdi-layers-outline me-1"></i> <span id="blockedPallets">0</span> Pallets
                                </div>
                            </div>
                            <div class="bg-soft-danger rounded-3 p-2">
                                <i class="mdi mdi-alert-octagon-outline text-white mdi-36px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Filter --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">Filter Data</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nama Barang</label>
                        <select class="form-select select2-filter" id="filterNamaBarang" multiple>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">MID</label>
                        <select class="form-select select2-filter" id="filterMid" multiple>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select select2-filter" id="filterSupplier" multiple>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">No SPB</label>
                        <select class="form-select select2-filter" id="filterNoSpb" multiple>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label">Group</label>
                        <select class="form-select select2-filter" id="filterGroup" multiple>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select select2-filter" id="filterStatus" multiple>
                            <option value="UNREST">UNREST</option>
                            <option value="QI">QI</option>
                            <option value="BLOCKED">BLOCKED</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Incoming Date</label>
                        <input type="date" class="form-control" id="filterDate">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Catatan / Cari</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="filterCatatan" placeholder="Cari catatan...">
                            <button class="btn btn-primary" id="btnFilter">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                            <button class="btn btn-outline-danger" id="btnReset">
                                <i class="mdi mdi-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Raw Material Stock On Hand</h5>
                @can('permission', 'wrm-inventory-soh-plus')
                <div class="d-flex gap-2">
                    <a href="{{ route('wrm.inventory.index-upload') }}" class="btn btn-outline-primary" id="btnUpload">
                        <i class="mdi mdi-upload"></i> Upload
                    </a>
                    <a href="{{ route('wrm.inventory.draft-outbound') }}" class="btn btn-primary" id="btnUpload">
                        <i class="mdi mdi-upload"></i> Transfer
                    </a>
                    {{-- <button class="btn btn-primary" id="btnTambah">
                            <i class="mdi mdi-plus"></i> Tambah
                        </button> --}}
                </div>
                @endcan
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-nowrap" id="tableStock">
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
                                @can('permission', 'wrm-inventory-soh-plus')
                                <th class="text-center">Aksi</th>
                                @endcan
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

{{-- Modal Edit --}}
<div class="modal fade" id="modalFormEdit">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleForm">Edit Stock Gula</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formStockEdit">

                <div class="modal-body">

                    <input type="hidden" id="id" name="id">

                    <div class="mb-2">
                        <label>Mid</label>
                        <input type="hidden" name="barang_id" id="barangIdEdit">
                        <input type="text" class="form-control bg-light" id="midEdit" readonly>
                    </div>

                    <div class="mb-2">
                        <label>No SPB</label>
                        <input type="number" class="form-control" name="no_spb" id="noSpbEdit">
                    </div>

                    <div class="mb-2">
                        <label>Qty</label>
                        <input type="number" class="form-control" name="qty" id="qtyEdit">
                    </div>

                    <div class="mb-2">
                        <label>Incoming Date</label>
                        <input type="date" class="form-control" name="incoming_date" id="incomingEdit">
                    </div>

                    <div class="mb-2">
                        <label>Status</label>
                        <select class="form-select" name="status" id="statusEdit">
                            <option value="UNREST">UNREST</option>
                            <option value="QI">QI</option>
                            <option value="TRANSFER">TRANSFER</option>
                            <option value="BLOCKED">BLOCKED</option>
                            <option value="ISSUED">ISSUED</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Group</label>
                        <input type="text" class="form-control" name="group" id="groupEdit">
                    </div>

                    <div class="mb-2">
                        <label>Supplier</label>
                        <select class="form-select" name="supplier" id="supplierEdit">
                            <option value="">Pilih Supplier</option>
                            @foreach ($suppliers as $sup)
                            <option value="{{ $sup->nama }}">{{ $sup->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Location</label>
                        <select class="form-select" id="locEdit" name="loc_id">
                            <option value="">Pilih Location</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Pallet ID</label>
                        <input type="text" class="form-control" name="pallet_id" id="palletEdit">
                    </div>

                    <div class="mb-2">
                        <label>Catatan</label>
                        <textarea class="form-control" name="catatan" id="catatan"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">
                        Simpan
                    </button>
                </div>

            </form>

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
        // Modal edit selects
        $('#supplierEdit').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#modalFormEdit'),
            placeholder: 'Pilih...',
            allowClear: true
        });

        // Filter selects
        $('.select2-filter').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih...',
            allowClear: true,
            closeOnSelect: false
        });

        $('#locEdit').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#modalFormEdit'),
            placeholder: 'Cari Location...',
            allowClear: true,
            ajax: {
                url: "{{ route('wrm.inventory.getLocationsAjax') }}",
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
            minimumInputLength: 0
        });

        let filterTimeout;
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
                catatan: $('#filterCatatan').val(),
                sort_dir: currentSortDir,
            };

            $.get("{{ route('wrm.inventory.getData') }}", params, function(res) {

                let html = '';
                let data = res.data.data;
                let startNo = res.data.from;

                if (data.length === 0) {

                    html = `
                            <tr>
                                <td colspan="15" class="text-center text-muted py-4">
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
                                    <td>${d.barcode}</td>
                                    <td>${d.inbound.no_spb}</td>
                                    <td>${d.barang.mid}</td>
                                    <td>${d.barang.nama_barang}</td>
                                    <td>${d.barang.uom}</td>
                                    <td>${d.group}</td>
                                    <td>${d.pallet_id}</td>
                                    <td>${numberFormat(d.qty)}</td>
                                    <td>${d.status.toUpperCase()}</td>
                                    <td>${d.bin.location.plant} - ${d.bin.location.s_loc} - ${d.bin.location.gudang} - ${d.bin.location.zona} - ${d.bin.location.bin} - ${d.bin.kolom}.${d.bin.level}</td>
                                    <td>${d.inbound.supplier}</td>
                                    <td>${d.inbound.incoming_date}</td>
                                    <td>${d.catatan ?? ''}</td>

                                    @can('permission', 'wrm-inventory-soh-plus')
                                    <td class="text-center">

                                        <button class="btn btn-sm btn-warning btnEdit"
                                            data-data="${encodeURIComponent(JSON.stringify(d))}">
                                            Edit
                                        </button>

                                        <button class="btn btn-sm btn-danger btnDelete"
                                            data-id="${d.id}">
                                            Hapus
                                        </button>

                                    </td>
                                    @endcan
                                </tr>
                            `;
                    });

                }

                $('#tableStock tbody').html(html);

                renderPagination(res.data);
                updateSummary(res.summary);

            });
        }

        function updateSummary(summary) {


            const unrest = summary.status_breakdown.UNREST || {
                count: 0,
                total_qty: 0
            };

            const reserved = summary.status_breakdown.RESERVED || {
                count: 0,
                total_qty: 0
            };

            const issued = summary.status_breakdown.ISSUED || {
                count: 0,
                total_qty: 0
            };

            $('#totalQty').text(numberFormat(unrest.total_qty + reserved.total_qty + issued.total_qty));
            $('#totalPalletsDisplay').text(numberFormat(summary.total_pallet));
            $('#unrestQty').text(numberFormat(unrest.total_qty));
            $('#unrestPallets').text(numberFormat(unrest.count));

            const qi = summary.status_breakdown.QI || {
                count: 0,
                total_qty: 0
            };
            $('#qiQty').text(numberFormat(qi.total_qty));
            $('#qiPallets').text(numberFormat(qi.count));

            const blocked = summary.status_breakdown.BLOCKED || {
                count: 0,
                total_qty: 0
            };
            $('#blockedQty').text(numberFormat(blocked.total_qty));
            $('#blockedPallets').text(numberFormat(blocked.count));
        }

        $('#btnTambah').click(() => {
            $('#formStock')[0].reset();
            $('#id').val('');
            $('#modalForm').modal('show');
        });


        $(document).on('click', '.btnEdit', function() {

            let detail = JSON.parse(decodeURIComponent($(this).data('data')));
            let header = detail.inbound;

            $('#titleForm').text('Edit Stock Gula');

            $('#id').val(detail.id);

            $('#noSpbEdit').val(header.no_spb);
            $('#supplierEdit').val(header.supplier ?? '').trigger('change');

            // Format date to YYYY-MM-DD for HTML date input
            if (header.incoming_date) {
                $('#incomingEdit').val(header.incoming_date.substring(0, 10));
            } else {
                $('#incomingEdit').val('');
            }

            $('#barangIdEdit').val(detail.barang.id);
            $('#midEdit').val(`${detail.barang.mid} - ${detail.barang.nama_barang}`);
            $('#qtyEdit').val(parseFloat(detail.qty));
            $('#statusEdit').val(detail.status);
            $('#groupEdit').val(detail.group);
            $('#palletEdit').val(detail.pallet_id ?? '');
            $('#catatan').val(detail.catatan ?? '');

            // Handle AJAX value for location
            let locSelect = $('#locEdit');
            if (detail.bin) {
                let bin = detail.bin;
                let loc = bin.location;
                let optionText = `${loc.plant} - ${loc.s_loc} - ${loc.gudang} - ${loc.zona} - ${loc.bin} - (${bin.kolom}.${bin.level})`;

                // Append and select the option for AJAX select2
                let newOption = new Option(optionText, detail.loc_id, true, true);
                locSelect.append(newOption).trigger('change');
            } else {
                locSelect.val(null).trigger('change');
            }

            $('#modalFormEdit').modal('show');
        });

        $('#formStockEdit').on('submit', function(e) {

            e.preventDefault();

            let id = $('#id').val();

            $.ajax({
                url: `{{ route('wrm.inventory.update', '') }}/` + id,
                method: 'POST',
                data: $(this).serialize() + '&_method=PUT',
                beforeSend() {
                    Swal.fire({
                        title: 'Menyimpan...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },

                success(res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message
                    });

                    $('#modalFormEdit').modal('hide');

                    loadData();

                },

                error(xhr) {

                    let message = 'Terjadi kesalahan';

                    if (xhr.status === 422) {

                        let errors = xhr.responseJSON.errors;

                        message = Object.values(errors)
                            .map(v => v[0])
                            .join('<br>');

                    }

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: message
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
                    url: '/wrm/inventory/delete/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend() {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
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
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan pada server';

                        if (xhr.status === 404) {
                            message = 'Data tidak ditemukan';
                        }

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON?.errors;
                            if (errors) {
                                message = Object.values(errors)
                                    .map(v => v[0])
                                    .join('<br>');
                            }
                        }

                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: message
                        });
                    }
                });
            });
        });

        // Event listener for all filters
        $('.select2-filter, #filterDate').on('change', function() {
            // Check if this change was triggered manually or by select2 update
            if ($(this).hasClass('updating')) return;

            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                loadFilter();
                loadData();
            }, 300);
        });

        $('#filterCatatan').on('keyup', function(e) {
            if (e.key === 'Enter') {
                loadData();
                return;
            }
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                loadData();
            }, 500);
        });

        $('#btnFilter').click(function() {
            loadData();
        });

        $('#sortDate').click(function() {
            currentSortDir = currentSortDir === 'desc' ? 'asc' : 'desc';

            // Update Icon
            if (currentSortDir === 'asc') {
                $('#sortIcon').removeClass('mdi-sort mdi-sort-descending').addClass('mdi-sort-ascending');
            } else {
                $('#sortIcon').removeClass('mdi-sort mdi-sort-ascending').addClass('mdi-sort-descending');
            }

            loadData();
        });

        $('#btnReset').click(function() {
            $('.select2-filter').val(null).trigger('change');
            $('#filterMid').val(null).trigger('change');
            $('#filterDate').val('');
            $('#filterCatatan').val('');
            loadFilter();
            loadData();
        });

        function loadFilter() {
            let params = {
                group: $('#filterGroup').val(),
                jenis_bahan: $('#filterNamaBarang').val(),
                mid: $('#filterMid').val(),
                supplier: $('#filterSupplier').val(),
                status: $('#filterStatus').val(),
                no_spb: $('#filterNoSpb').val(),
            };

            $.get("{{ route('wrm.inventory.getFilter') }}", params, function(res) {
                updateDropdown('#filterGroup', res.groups);
                updateDropdown('#filterNamaBarang', res.jenis_bahan);
                updateDropdown('#filterSupplier', res.suppliers);
                updateDropdown('#filterNoSpb', res.no_spbs);
                updateDropdown('#filterMid', res.mids, true);
                // Optionally update status too, if you want it chained
                // updateDropdown('#filterStatus', res.statuses);
            });
        }

        function updateDropdown(selector, data, isMid = false) {
            let $el = $(selector);
            let currentValues = $el.val() || [];

            $el.addClass('updating');
            $el.empty();

            data.forEach(item => {
                let val, text;
                if (isMid) {
                    val = item.mid;
                    text = item.text;
                } else {
                    val = item;
                    text = item;
                }

                let isSelected = currentValues.includes(val.toString());
                let option = new Option(text, val, isSelected, isSelected);
                $el.append(option);
            });

            $el.trigger('change');
            $el.removeClass('updating');
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
    })
</script>
@endsection