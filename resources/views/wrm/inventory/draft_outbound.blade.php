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
                <h5 class="mb-0 fw-bold">Form Draft Outbound Raw Material</h5>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">No Reservasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="no_reservasi" placeholder="Contoh: 204506143">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Qty Request (KG) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="qty" placeholder="Jumlah yang diminta">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Shift <span class="text-danger">*</span></label>
                        <select class="form-select" id="shift">
                            <option value="1">Shift 1</option>
                            <option value="2">Shift 2</option>
                            <option value="3">Shift 3</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input checklist-item" type="checkbox" value="Barang yang dikirim dalam kondisi bersih dan utuh" id="cond1" checked>
                            <label class="form-check-label" for="cond1">Barang yang dikirim dalam kondisi bersih dan utuh</label>
                        </div>
                    </div>
                </div>

                <div class="rounded border border-2 border-primary p-3 mb-4 bg-soft-info shadow-sm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="mdi mdi-magnify"></i></span>
                                <input type="text" class="form-control" id="filterMid" placeholder="Cari MID ...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="mdi mdi-tag-outline"></i></span>
                                <input type="text" class="form-control" id="filterGroup" placeholder="Cari Group ...">
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button class="btn btn-primary w-100" id="btnSearch">
                                <i class="mdi mdi-magnify me-2"></i> Tampilkan Data
                            </button>
                            <button class="btn btn-light w-100 border" id="btnReset">
                                <i class="mdi mdi-refresh me-2"></i> Reset
                            </button>
                        </div>
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
                                <th>Qty (KG)</th>
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
                    <div class="mt-3 d-flex justify-content-end">
                        <button class="btn btn-success" id="btnSubmitOutbound">
                            <i class="mdi mdi-send me-2"></i> Submit Draft Outbound (Reservation)
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
        function numberFormat(x) {
            if (x === null || x === undefined) return '0';
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function runSearch() {

            let mid = $('#filterMid').val();
            let group = $('#filterGroup').val();

            if (!mid && !group) {

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
            let group = $('#filterGroup').val();

            $.get("{{ route('wrm.inventory.search-outbound') }}", {
                // page: page,
                mid: mid,
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
                                <td>${numberFormat(v.qty)}</td>
                                <td class="text-primary fw-bold">${v.bin.location.plant} - ${v.bin.location.gudang} - ${v.bin.location.bin} - ${v.bin.kolom}.${v.bin.level}</td>
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

            // ambil qty request dari input
            let reqQtyInput = $('#qty').val();
            let reqQty = parseFloat(reqQtyInput) || 0;

            // Validasi jika Qty Request masih kosong
            if (!reqQtyInput || reqQty <= 0) {
                $(this).prop('checked', false);
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silahkan isi Qty Request terlebih dahulu sebelum memilih pallet.'
                });
                return;
            }

            let total = 0;

            $('.pickItem:checked').each(function() {
                total += parseFloat($(this).data('qty'));
            });

            $('#totalPick').text(numberFormat(total));

            // tampilkan di summary
            $('#reqQty').text(numberFormat(reqQty));

            if (total > reqQty) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Qty Melebihi',
                    text: 'Total pick melebihi qty yang diminta. Silahkan batalkan beberapa pilihan.'
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

            $('#totalPick').text(numberFormat(total));

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

            let no_reservasi = $('#no_reservasi').val();
            let shift = $('#shift').val();
            let qtyRequest = $('#qty').val();

            if (!no_reservasi) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    text: 'Nomor Reservasi wajib diisi'
                });
                return;
            }

            let checklist = [];
            $('.checklist-item:checked').each(function() {
                checklist.push($(this).val());
            });

            Swal.fire({
                title: 'Submit Outbound',
                text: 'Apakah Anda yakin ingin menyimpan draft reservasi ini?',
                input: 'textarea',
                inputLabel: 'Catatan Tambahan',
                inputPlaceholder: 'Masukkan catatan tambahan...',
                showCancelButton: true,
                confirmButtonText: 'Submit'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('wrm.inventory.submit-outbound') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            no_reservasi: no_reservasi,
                            shift: shift,
                            items: selected,
                            catatan: result.value,
                            qty_request: qtyRequest,
                            checklist_kondisi: checklist
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Menyimpan...',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message ?? 'Draft Outbound berhasil disimpan',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                resetForm();
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message ?? 'Terjadi kesalahan sistem'
                            });
                        }
                    });

                }

            });

        });

        function resetForm() {

            // reset filter
            $('#filterMid').val('');
            $('#filterGroup').val('');
            $('#qty').val('');
            $('#no_reservasi').val('');

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