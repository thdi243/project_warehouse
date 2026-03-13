@extends('layouts.app')

@section('title', ' | Outbound Stock Raw Material')

@section('styles')
    <style>
        .pickItem {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #198754;
            transform: scale(1.2);
        }

        #tableStock tbody tr.row-picked {
            background-color: #e9f7ef;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Outbound Raw Material</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-3">

                        <div class="col-md-2">
                            <label class="form-label">MID</label>
                            <input type="text" class="form-control" id="filterMid">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Group</label>
                            <input type="text" class="form-control" id="filterGroup">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" id="filterStatus">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Qty Request</label>
                            <input type="number" class="form-control" id="qty">
                        </div>

                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button class="btn btn-outline-primary w-100" id="btnSearch">
                                <i class="mdi mdi-magnify me-2"></i> Search
                            </button>
                            <button class="btn btn-danger w-100" id="btnReset">
                                <i class="mdi mdi-refresh me-2"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <div class="alert alert-info d-flex justify-content-between">
                            <div>
                                Qty Request :
                                <b id="reqQty">0</b>
                            </div>
                            <div>
                                Total Pick :
                                <b id="totalPick">0</b>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped table-hover" id="tableStock">

                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>No SPB</th>
                                    <th>Pallet ID</th>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th>Group</th>
                                    <th>Status</th>
                                    <th>Qty</th>
                                    <th>Location</th>
                                    <th>Incoming Date</th>
                                    <th width="120" class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        Silahkan lakukan pencarian
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-3 text-end">
                            <button class="btn btn-success" id="btnSubmitOutbound">
                                <i class="mdi mdi-send me-2"></i> Submit Outbound
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            function runSearch() {

                let mid = $('#filterMid').val();
                let status = $('#filterStatus').val();
                let group = $('#filterGroup').val();

                if (!mid && !status && !group) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Minimal isi salah satu filter untuk melakukan pencarian.'
                    });

                    return;
                }

                loadData();
            }

            $('#btnSearch').click(runSearch);

            let searchTimer;

            $('#filterMid, #filterStatus, #filterGroup').on('keyup', function() {

                clearTimeout(searchTimer);

                searchTimer = setTimeout(runSearch, 500);

            });

            function loadData() {
                let mid = $('#filterMid').val();
                let status = $('#filterStatus').val();
                let group = $('#filterGroup').val();

                $.get("{{ route('wrm.inventory.search-outbound') }}", {
                    // page: page,
                    mid: mid,
                    status: status,
                    group: group,
                }, function(res) {

                    let html = '';
                    let data = res.data;
                    let no = 1;

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

                        data.forEach(v => {

                            html += `
                            <tr>

                                <td class="text-center">${no++}</td>
                                <td>${v.inbound.no_spb}</td>
                                <td>${v.pallet_id}</td>
                                <td>${v.barang.mid}</td>
                                <td>${v.barang.nama_barang}</td>
                                <td>${v.group}</td>
                                <td>${v.status}</td>
                                <td>${v.qty}</td>
                                <td>${v.location.plant} - ${v.location.gudang} - ${v.location.bin}</td>
                                <td>${v.inbound.incoming_date}</td>
                                <td class="text-center">
                                    <input type="checkbox"
                                        class="form-check-input pickItem"
                                        data-id="${v.id}"
                                        data-qty="${v.qty}">
                                </td>

                            </tr>
                        `;

                        });

                    }

                    $('#tableStock tbody').html(html);

                });

            }

            $(document).on('change', '.pickItem', function() {

                let total = 0;

                $('.pickItem:checked').each(function() {
                    total += parseFloat($(this).data('qty'));
                });

                $('#totalPick').text(total);

                // ambil qty request dari input
                let reqQty = parseFloat($('#qty').val()) || 0;

                // tampilkan di summary
                $('#reqQty').text(reqQty);

                if (reqQty && total > reqQty) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Qty Melebihi',
                        text: 'Total pick melebihi qty yang diminta'
                    });

                    $(this).prop('checked', false);

                    hitungUlang();
                }
            });

            function hitungUlang() {

                let total = 0;

                $('.pickItem:checked').each(function() {
                    total += parseFloat($(this).data('qty'));
                });

                $('#totalPick').text(total);

            }

            $(document).on('change', '.pickItem', function() {

                if ($(this).is(':checked')) {
                    $(this).closest('tr').addClass('row-picked');
                } else {
                    $(this).closest('tr').removeClass('row-picked');
                }

            });

            // Submit
            $('#btnSubmitOutbound').click(function() {

                let selected = [];

                $('.pickItem:checked').each(function() {

                    selected.push({
                        id: $(this).data('id'),
                        qty: $(this).data('qty')
                    });

                });

                if (selected.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak ada pallet dipilih',
                        text: 'Silahkan pilih pallet terlebih dahulu'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Submit Outbound?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Submit'
                }).then((result) => {

                    if (result.isConfirmed) {

                        $.ajax({
                            url: "{{ route('wrm.inventory.submit-outbound') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                items: selected
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message ??
                                        'Outbound berhasil disimpan',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    resetForm();
                                });
                                loadData();
                            }
                        });

                    }

                });

            });

            function resetForm() {

                // reset filter
                $('#filterMid').val('');
                $('#filterStatus').val('');
                $('#filterGroup').val('');
                $('#qty').val('');

                // reset summary
                $('#reqQty').text('0');
                $('#totalPick').text('0');

                // uncheck semua pick
                $('.pickItem').prop('checked', false);

                // reset table
                $('#tableStock tbody').html(`
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            Silahkan lakukan pencarian
                        </td>
                    </tr>
                `);

            }

            $('#btnReset').click(function() {
                resetForm();
            });
        });
    </script>
@endsection
