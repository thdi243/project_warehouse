@extends('layouts.app')
@section('styles')
    <style>
        @media (max-width: 992px) {
            #cancelBtn {
                margin-left: 3rem !important;
            }
        }
    </style>
@endsection
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        {{-- <h4 class="mb-sm-0">Data TKBM</h4> --}}

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">TKBM</a></li>
                                <li class="breadcrumb-item active">Fees & taxes</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Manage Fee | PPn | PPh TKBM</h4>
                        </div>
                        <div class="card-body">
                            <div class="live-preview">
                                <div class="row gy-4">
                                    <div class="col-xxl-3 col-md-4">
                                        <div>
                                            <label for="fee" class="form-label">Fee</label>
                                            <input type="number" class="form-control" id="fee" name="fee">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-4">
                                        <div>
                                            <label for="ppn" class="form-label">PPn</label>
                                            <input type="number" class="form-control" id="ppn" name="ppn">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-4">
                                        <div>
                                            <label for="pph" class="form-label">PPh</label>
                                            <input type="number" class="form-control" id="pph" name="pph">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-1">
                                        <button class="btn btn-primary" type="submit" id="simpanBtn">Simpan</button>
                                    </div>
                                    <div class="col-1 ms-4">
                                        <button class="btn btn-light" type="submit" id="cancelBtn">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">History</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-stripped mb-0" id="dataTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Fee</th>
                                            <th>PPn</th>
                                            <th>PPh</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Diisi oleh js --}}
                                    </tbody>
                                </table>
                            </div>
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
            loadHistory();

            $('#simpanBtn').click(function() {
                var fee = $('#fee').val();
                var ppn = $('#ppn').val();
                var pph = $('#pph').val();

                // Kirim data ke server (contoh menggunakan AJAX)
                $.ajax({
                    url: "{{ route('tkbm.fee.simpan') }}",
                    method: "POST",
                    data: {
                        fee: fee || 0,
                        ppn: ppn || 0,
                        pph: pph || 0,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data berhasil disimpan',
                            showConfirmButton: false,
                            timer: 700
                        });
                        // Reset form
                        $('#fee').val('');
                        $('#ppn').val('');
                        $('#pph').val('');

                        // Muat ulang data history
                        loadHistory();
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal menyimpan data',
                            text: 'Terjadi kesalahan saat menyimpan data.',
                        });
                    }
                });
            });

            $('#cancelBtn').click(function() {
                // Reset form
                $('#fee').val('');
                $('#ppn').val('');
                $('#pph').val('');
            });

            // Load data history dari server
            function loadHistory() {
                $.ajax({
                    url: "{{ route('tkbm.fee.history') }}",
                    method: "GET",
                    success: function(response) {
                        var tbody = $('#dataTable tbody');
                        tbody.empty(); // Kosongkan tabel sebelum mengisi ulang

                        response.data.forEach(function(item, index) {
                            var rowClass = (index === 0) ? 'table-success' : '';

                            var row = '<tr class="' + rowClass + '">' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + item.fee + '%</td>' +
                                '<td>' + item.ppn + '%</td>' +
                                '<td>' + item.pph + '%</td>' +
                                '<td>' + new Date(item.created_at).toLocaleString() + '</td>' +
                                '</tr>';
                            tbody.append(row);
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal memuat data history',
                            text: 'Terjadi kesalahan saat memuat data history.',
                        });
                    }
                });
            }
        });
    </script>
@endsection
