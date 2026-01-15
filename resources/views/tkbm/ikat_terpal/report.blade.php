@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="form-filter" class="row g-3 mb-4">
                                <div class="col-md-4 col-sm-6">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                </div>
                                <div class="col-md-4 col-sm-12 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2 w-100" id="btn-filter">
                                        <i class="mdi mdi-filter"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary w-100" id="btn-reset">
                                        <i class="mdi mdi-replay"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Report Ikat Terpal</h4>
                            <div>
                                <a href="{{ route('tkbm.ikat-terpal.index') }}" class="btn btn-primary me-2">
                                    <i class="mdi mdi-plus"></i> Tambah Data Baru
                                </a>
                                <button type="button" class="btn btn-success" id="btn-print-pdf">
                                    <i class="mdi mdi-printer"></i> Print PDF
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Tabel -->
                            <div class="table-responsive">
                                <table id="table-ikat-terpal" class="table table-hover table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Qty Pallet</th>
                                            <th>Harga / Pallet</th>
                                            <th>Jml Buruh</th>
                                            <th>Total</th>
                                            <th>Keterangan (Fee <span id="fee-value"></span>)</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                    </tfoot>
                                </table>
                                <!-- Loading indicator -->
                                <div id="loading" class="text-center py-5 d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Memuat data...</p>
                                </div>
                            </div>

                            <!-- Jika data kosong -->
                            <div id="no-data" class="text-center py-5 d-none">
                                <i class="mdi mdi-database-off mdi-48px"></i>
                                <p class="mt-2">Tidak ada data ikat terpal ditemukan.</p>
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
            const tableBody = $('#table-ikat-terpal tbody');
            const loadingDiv = $('#loading');
            const noDataDiv = $('#no-data');
            const formFilter = $('#form-filter');
            let deleteId = null;

            loadData();

            function loadData(params = {}) {
                loadingDiv.removeClass('d-none');
                tableBody.empty();
                noDataDiv.addClass('d-none');

                $.ajax({
                    url: `{{ url('api/tkbm/get-data/ikat-terpal') }}`, // atau '/api/ikat-terpal' kalau pakai api
                    method: 'GET',
                    data: params,
                    dataType: 'json',
                    success: function(response) {
                        loadingDiv.addClass('d-none');

                        if (response.status === 'success' && response.data && response.data.length >
                            0) {

                            const feeValue = parseFloat(response.data[0].fee.fee || 0);
                            $('#fee-value').text(
                                feeValue.toLocaleString('id-ID', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 2
                                }) + '%'
                            );

                            response.data.forEach((item, index) => {
                                const grandTotal = (parseFloat(item.subtotal_barang) +
                                    parseFloat(item.total_fee)).toFixed(2);
                                const row = `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.tanggal}</td>
                                        <td>${item.qty_pallet}</td>
                                        <td>Rp ${parseFloat(item.produk.harga_pallet).toLocaleString('id-ID')}</td>
                                        <td>${item.jml_buruh || '-'}</td>
                                        <td>Rp ${parseFloat(item.subtotal_barang).toLocaleString('id-ID')}</td>
                                        <td>Rp ${parseFloat(item.total_fee).toLocaleString('id-ID')}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ url('/ikat-terpal') }}/${item.id}/edit" class="btn btn-sm btn-warning">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                            <button class="btn btn-sm btn-danger btn-delete" data-id="${item.id}">
                                                <i class="mdi mdi-delete"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                tableBody.append(row);
                            });
                        } else {
                            noDataDiv.removeClass('d-none');
                        }
                    },
                    error: function() {
                        loadingDiv.addClass('d-none');
                        Swal.fire('Error', 'Gagal memuat data ikat terpal.', 'error');
                    }
                });
            }

            formFilter.on('submit', function(e) {
                e.preventDefault();
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                const params = {};
                if (startDate) params.start_date = startDate;
                if (endDate) params.end_date = endDate;

                loadData(params);
            });

            // Reset filter
            $('#btn-reset').on('click', function() {
                formFilter[0].reset();
                loadData(); // reload tanpa parameter
            });

            // Delete handler (sama seperti sebelumnya)
            $(document).on('click', '.btn-delete', function() {
                deleteId = $(this).data('id');
                Swal.fire({
                    title: 'Yakin hapus?',
                    text: "Data ini akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('/tkbm/ikat-terpal') }}/' + deleteId,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                Swal.fire('Terhapus!', 'Data berhasil dihapus.',
                                    'success');
                                loadData();
                            },
                            error: function() {
                                Swal.fire('Gagal', 'Tidak bisa menghapus data.',
                                    'error');
                            }
                        });
                    }
                });
            });

            $('#btn-print-pdf').on('click', function() {
                const startDate = $('#start_date').val().trim();
                const endDate = $('#end_date').val().trim();

                if (!startDate || !endDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Filter Tanggal Belum Lengkap',
                        text: 'Harap isi tanggal mulai DAN tanggal akhir.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                let url = '{{ url('tkbm/ikat-terpal/report/print-pdf') }}';

                const queryParams = new URLSearchParams();
                if (startDate) queryParams.append('start_date', startDate);
                if (endDate) queryParams.append('end_date', endDate);

                if (queryParams.toString()) {
                    url += '?' + queryParams.toString();
                }

                window.open(url, '_blank');
            });

        });
    </script>
@endsection
