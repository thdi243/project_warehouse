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
                    <div class="card-body text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Total Inventory</h6>
                                <h3 class="mb-0 fw-bold" id="totalQty">0</h3>
                            </div>
                            <div class="bg-soft-primary rounded-3 p-2">
                                <i class="mdi mdi-database-outline mdi-36px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <!-- <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);"> -->
                <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-success">
                    <div class="card-body text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Unrest</h6>
                                <h3 class="mb-0 fw-bold" id="unrestQty">0</h3>
                            </div>
                            <div class="bg-soft-success rounded-3 p-2">
                                <i class="mdi mdi-check-circle-outline mdi-36px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <!-- <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(135deg, #f09819 0%, #edde5d 100%);"> -->
                <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-info">
                    <div class="card-body text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Total Pallets</h6>
                                <h3 class="mb-0 fw-bold" id="totalPalletsDisplay">0</h3>
                            </div>
                            <div class="bg-soft-info rounded-3 p-2">
                                <i class="mdi mdi-layers-outline mdi-36px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <!-- <div class="card border-0 shadow-sm h-100 overflow-hidden" style="background: linear-gradient(135deg, #cb2d3e 0%, #ef473a 100%);"> -->
                <div class="card border-0 shadow-sm h-100 overflow-hidden bg-soft-danger">
                    <div class="card-body text-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Blocked</h6>
                                <h3 class="mb-0 fw-bold" id="blockedQty">0</h3>
                            </div>
                            <div class="bg-soft-danger rounded-3 p-2">
                                <i class="mdi mdi-alert-octagon-outline mdi-36px"></i>
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
                            data-bs-target="#advancedFilter" title="Filter lanjutan">
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
                            <label class="form-label">Incoming Date</label>
                            <input type="date" class="form-control" id="filterDate">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="UNREST">UNREST</option>
                                <option value="QI">QI</option>
                                <option value="TRANSFER">TRANSFER</option>
                                <option value="BLOCKED">BLOCKED</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Supplier</label>
                            <select class="form-select" id="filterSupplier">
                                <option value="">Semua Supplier</option>
                                @foreach ($suppliers as $sup)
                                <option value="{{ $sup->nama }}">{{ $sup->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">No SPB</label>
                            <input type="text" class="form-control" id="filterNoSpb" placeholder="Cari No SPB">
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
                                <th>Incoming Date</th>
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
                        <input type="text" class="form-control bg-light" name="pallet_id" id="palletEdit" readonly>
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
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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
        $('#filterGroup, #filterJenisBahan, #filterSupplier, #filterStatus').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih...',
            allowClear: true
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

        loadData();
        loadFilter();

        function loadData(page = 1) {

            let group = $('#filterGroup').val();
            let jenisBahan = $('#filterJenisBahan').val();
            let mid = $('#filterMid').val();
            let date = $('#filterDate').val();
            let supplier = $('#filterSupplier').val();
            let status = $('#filterStatus').val();
            let noSpb = $('#filterNoSpb').val();

            $.get("{{ route('wrm.inventory.getData') }}", {
                page: page,
                group: group,
                jenis_bahan: jenisBahan,
                mid: mid,
                date: date,
                supplier: supplier,
                status: status,
                no_spb: noSpb,
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
            $('#totalQty').text(numberFormat(summary.total_qty));
            $('#totalPalletsDisplay').text(numberFormat(summary.total_pallet));

            const unrest = summary.status_breakdown.UNREST || {
                count: 0,
                total_qty: 0
            };
            $('#unrestQty').text(numberFormat(unrest.total_qty));

            const blocked = summary.status_breakdown.BLOCKED || {
                count: 0,
                total_qty: 0
            };
            $('#blockedQty').text(numberFormat(blocked.total_qty));
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
            $('#qtyEdit').val(detail.qty);
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

        $('#filterGroup, #filterJenisBahan, #filterDate, #filterSupplier, #filterStatus').on('change', function() {
            loadData();
        });

        $('#filterNoSpb').on('keyup', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                loadData();
            }, 500);
        });

        let typingTimer;

        $('#filterMid').on('keyup', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                loadData();
            }, 500);
        });

        $('#btnReset').click(function() {

            $('#filterGroup').val('').trigger('change');
            $('#filterJenisBahan').val('').trigger('change');
            $('#filterMid').val('');
            $('#filterDate').val('');
            $('#filterSupplier').val('').trigger('change');
            $('#filterStatus').val('').trigger('change');
            $('#filterNoSpb').val('');

            loadData();

        });

        function loadFilter() {

            $.get("{{ route('wrm.inventory.getFilter') }}", function(res) {

                let groupHtml = `<option value="">Semua Group</option>`;
                res.groups.forEach(g => {
                    groupHtml += `<option value="${g}">${g}</option>`;
                });

                $('#filterGroup').html(groupHtml).trigger('change');

                let jenisHtml = `<option value="">Semua Jenis Bahan</option>`;
                res.jenis_bahan.forEach(j => {
                    jenisHtml += `<option value="${j}">${j}</option>`;
                });

                $('#filterJenisBahan').html(jenisHtml).trigger('change');

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
    })
</script>
@endsection