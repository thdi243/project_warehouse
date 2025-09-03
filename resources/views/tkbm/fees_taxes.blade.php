@extends('layouts.app')
@section('styles')
    <style>
        .custom-card {
            border-radius: 20px;
            transition: all 0.3s ease-in-out;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .custom-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
        }

        /* Warna khusus tiap card */
        .fees-card {
            background: linear-gradient(135deg, #f9f9f9, #f0f4ff);
        }

        .fees-card:hover {
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            color: white;
        }

        .price-card {
            background: linear-gradient(135deg, #fff8f8, #ffecec);
        }

        .price-card:hover {
            background: linear-gradient(135deg, #f6c23e, #e74a3b);
            color: white;
        }

        .custom-card:hover h4,
        .custom-card:hover p {
            color: white !important;
        }


        @media (max-width: 992px) {
            .cancelBtn {
                margin-left: 3rem !important;
            }
        }
    </style>
@endsection
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        {{-- <h4 class="mb-sm-0">Data TKBM</h4> --}}

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">TKBM</a></li>
                                <li class="breadcrumb-item active">Manage Fees & Harga</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Unit -->
            <div class="row">
                <!-- Card Manage Fees & Taxes -->
                <div class="col-md-6">
                    <div class="card clickable custom-card fees-card" data-unit="FeesTaxes">
                        <div class="card-body text-center">
                            <h4 class="card-title">Fees & Taxes</h4>
                            <img src="{{ asset('assets/images/fees.svg') }}" alt="fees" height="150"
                                style="border-radius: 20px;">
                            <p class="text-muted">Atur biaya & pajak</p>
                        </div>
                    </div>
                </div>

                <!-- Card Harga Produk -->
                <div class="col-md-6">
                    <div class="card clickable custom-card price-card" data-unit="ProductPrice">
                        <div class="card-body text-center">
                            <h4 class="card-title">Harga Produk</h4>
                            <img src="{{ asset('assets/images/price.svg') }}" alt="harga" height="150"
                                style="border-radius: 20px;">
                            <p class="text-muted">Kelola harga produk</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="row" id="formFeesTaxes" style="display: none;">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Manage Fee | PPn | PPh</h4>
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
                                        <button class="btn btn-light cancelBtn" type="submit">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="formProductPrice" style="display: none;">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Manage Harga Produk</h4>
                        </div>
                        <div class="card-body">
                            <div class="live-preview">
                                <div class="row gy-4">
                                    <div class="col-xxl-3 col-md-4">
                                        <div>
                                            <label for="terpalPrice" class="form-label">Harga Terpal</label>
                                            <input type="number" class="form-control" id="terpalPrice" name="terpalPrice">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-4">
                                        <div>
                                            <label for="slipsheetPrice" class="form-label">Harga Slipsheet</label>
                                            <input type="number" class="form-control" id="slipsheetPrice"
                                                name="slipsheetPrice">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-4">
                                        <div>
                                            <label for="palletPrice" class="form-label">Harga Pallet</label>
                                            <input type="number" class="form-control" id="palletPrice" name="palletPrice">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-1">
                                        <button class="btn btn-primary" type="submit"
                                            id="simpanBtnPrice">Simpan</button>
                                    </div>
                                    <div class="col-1 ms-4">
                                        <button class="btn btn-light cancelBtn" type="submit">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="row" id="tableFeesTaxes" style="display: none;">
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

            <div class="row" id="tableProductPrice" style="display: none;">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">History</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-stripped mb-0" id="dataTablePrice">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Harga Terpal</th>
                                            <th>Harga Slipsheet</th>
                                            <th>Harga Pallet</th>
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
            loadHargaProduk();

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

            $('#simpanBtnPrice').click(function() {
                var terpal = $('#terpalPrice').val();
                var slipsheet = $('#slipsheetPrice').val();
                var pallet = $('#palletPrice').val();

                // Kirim data ke server (contoh menggunakan AJAX)
                $.ajax({
                    url: "{{ route('tkbm.harga-produk.simpan') }}",
                    method: "POST",
                    data: {
                        terpal: terpal || 0,
                        slipsheet: slipsheet || 0,
                        pallet: pallet || 0,
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
                        $('#priceTerpal').val('');
                        $('#priceSlipsheet').val('');
                        $('#pricePallet').val('');

                        // Muat ulang data history
                        loadHargaProduk();
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

            $(document).on("click", ".cancelBtn", function(e) {
                e.preventDefault();

                // Reset form
                $('#fee').val('');
                $('#ppn').val('');
                $('#pph').val('');
                $('#terpalPrice').val('');
                $('#slipsheetPrice').val('');
                $('#palletPrice').val('');

                $("#formFeesTaxes").fadeOut();
                $("#formProductPrice").fadeOut();
                $("#tableFeesTaxes").fadeOut();
                $("#tableProductPrice").fadeOut();
            });

            $(".card-unit, .custom-card").click(function() {
                let unit = $(this).data("unit"); // ambil data-unit dari card

                // Sembunyikan semua form dulu
                $("#formFeesTaxes").hide();
                $("#tableFeesTaxes").hide();
                $("#formProductPrice").hide();
                $("#tableProductPrice").hide();

                // Show form sesuai card yg dipilih
                if (unit === "FeesTaxes") {
                    $("#formFeesTaxes").fadeIn();
                    $("#tableFeesTaxes").fadeIn();
                } else if (unit === "ProductPrice") {
                    $("#formProductPrice").fadeIn();
                    $("#tableProductPrice").fadeIn();
                }
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
                                '<td>' + moment(item.created_at).format('D MMM YYYY HH:mm') +
                                '</td>' +
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

            function loadHargaProduk() {
                $.ajax({
                    url: "{{ route('tkbm.harga-produk.history') }}",
                    method: "GET",
                    success: function(response) {
                        var tbody = $('#dataTablePrice tbody');
                        tbody.empty(); // Kosongkan tabel sebelum mengisi ulang

                        response.data.forEach(function(item, index) {
                            var rowClass = (index === 0) ? 'table-success' : '';

                            var row = '<tr class="' + rowClass + '">' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>Rp ' + item.harga_terpal + '</td>' +
                                '<td>Rp ' + item.harga_slipsheet + '</td>' +
                                '<td>Rp ' + item.harga_pallet + '</td>' +
                                '<td>' + moment(item.created_at).format('D MMM YYYY HH:mm') +
                                '</td>' +
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
