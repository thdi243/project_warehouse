@extends('layouts.app')

@section('title' . ' | Issued Stock Raw Material')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Transfer Stock</h5>
                </div>

                <div class="card-body">

                    <!-- PILIH SPB -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">No SPB</label>
                            <select class="form-select" id="spbSelect">
                                <option value="">Pilih SPB</option>
                            </select>
                        </div>
                    </div>

                    <!-- TABLE LIST STOCK -->
                    <form id="formTransfer">

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tableTransfer">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="checkAll">
                                        </th>
                                        <th>Pallet ID</th>
                                        <th>MID</th>
                                        <th>Nama Barang</th>
                                        <th>Qty</th>
                                        <th>Group</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody></tbody>

                            </table>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-primary">
                                Transfer
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadSpb();

            function loadSpb() {
                $.get('/wrm/stock-gula/get-spb', function(res) {

                    let html = `<option value="">Pilih SPB</option>`;

                    res.data.forEach(v => {
                        html += `<option value="${v}">${v}</option>`;
                    });

                    $('#spbSelect').html(html);
                });
            }

            $('#spbSelect').on('change', function() {

                let spb = $(this).val();

                if (!spb) return;

                $.get('/wrm/stock-gula/by-spb', {
                    spb: spb
                }, function(res) {
                    let html = '';
                    res.data.forEach((v, i) => {
                        html += `
                            <tr>
                                <td>
                                    <input type="checkbox" name="ids[]" value="${v.id}">
                                </td>
                                <td>${v.pallet_id}</td>
                                <td>${v.barang.mid}</td>
                                <td>${v.barang.nama_barang}</td>
                                <td>${v.qty}</td>
                                <td>${v.group}</td>
                                <td>${v.status}</td>
                            </tr>
                        `;
                    });

                    $('#tableTransfer tbody').html(html);
                });
            });

            $('#checkAll').on('change', function() {
                $('input[name="ids[]"]').prop('checked', $(this).prop('checked'));
            });

            $('#formTransfer').on('submit', function(e) {

                e.preventDefault();

                let data = $(this).serialize();

                $.post('/wrm/stock-gula/transfer', data, function(res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message
                    });

                    $('#tableTransfer tbody').html('');
                    $('#spbSelect').val('');
                });
            });
        })
    </script>
@endsection
