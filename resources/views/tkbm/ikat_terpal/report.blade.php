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
                                @can('permission', 'tkbm-ikat-terpal-plus')
                                    <button type="button" class="btn btn-success" id="btn-print-pdf">
                                        <i class="mdi mdi-printer"></i> Print PDF
                                    </button>
                                @endcan
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
                                            @can('permission', 'tkbm-ikat-terpal-plus')
                                                <th class="text-center">Aksi</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="loadingRow" class="d-none">
                                            <td colspan="8" class="text-center py-5">
                                                <div class="spinner-border text-primary"></div>
                                                <p class="mt-2 mb-0">Memuat data...</p>
                                            </td>
                                        </tr>

                                        <tr id="emptyRow" class="d-none">
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                Tidak ada data
                                            </td>
                                        </tr>
                                    </tbody>
                                    @can('permission', 'tkbm-ikat-terpal-plus')
                                        <tfoot class="table-bordered table-light mt-4">
                                            <tr>
                                                <th colspan="2" class="text-center">Total</th>
                                                <th><span id="qtyPallet">0</span></th>
                                                <th></th> {{-- Harga / Pallet --}}
                                                <th></th> {{-- Jml Buruh --}}
                                                <th>Rp <span id="totalQty">0</span></th>
                                                <th>Rp <span id="totalFee">0</span></th>
                                                <th colspan="2"></th>
                                            </tr>

                                            <tr>
                                                <th colspan="6" class="text-center">PPn {{ $feeMaster->ppn }}%</th>
                                                <th>Rp <span id="tPpnAct">0</span></th>
                                                <th colspan="3"></th>
                                            </tr>

                                            <tr>
                                                <th colspan="6" class="text-center">PPh {{ $feeMaster->pph }}%</th>
                                                <th class="text-danger">(Rp <span id="tPphAct">0</span>)</th>
                                                <th colspan="3"></th>
                                            </tr>

                                            <tr>
                                                <th colspan="6" class="text-center">Grand Total BPS</th>
                                                <th>Rp <span id="tGrandTotal">0</span></th>
                                                <th colspan="3"></th>
                                            </tr>
                                        </tfoot>
                                    @endcan
                                </table>
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

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel">Edit Data Ikat Terpal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form-edit">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="edit-id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" id="edit-tanggal" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qty Pallet</label>
                                <input type="number" class="form-control" name="qty_pallet" id="edit-qty_pallet"
                                    min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jumlah Buruh</label>
                                <select class="form-select" name="jml_buruh" id="edit-jml_buruh">
                                    <option value="" selected>Pilih Jumlah Buruh</option>
                                    @for ($i = 1; $i <= 3; $i++)
                                        <option value="{{ $i }}" {{ old('jml_buruh') == $i ? 'selected' : '' }}>
                                            {{ $i }} Buruh</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="catatan" id="edit-catatan" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-update">Update Data</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const tableBody = $('#table-ikat-terpal tbody');
            const formFilter = $('#form-filter');
            let deleteId = null;

            loadData();

            const fmtID = n => Number(n || 0).toLocaleString('id-ID');
            const PPN = {{ $feeMaster->ppn ?? 0 }};
            const PPH = {{ $feeMaster->pph ?? 0 }};

            function loadData(params = {}) {
                $('#tableBody .data-row').remove();
                showLoading();

                $.ajax({
                    url: `{{ url('api/tkbm/get-data/ikat-terpal') }}`, // atau '/api/ikat-terpal' kalau pakai api
                    method: 'GET',
                    data: params,
                    dataType: 'json',
                    success: function(response) {
                        hideState();


                        let sumQtyPallet = 0;
                        let sumTotalQty = 0;
                        let sumTotalFee = 0;

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
                                        @can('permission', 'tkbm-ikat-terpal-plus')
                                            <td class="text-nowrap text-center">
                                                <button class="btn btn-sm btn-warning btn-edit" data-id="${item.id}">
                                                    <i class="mdi mdi-pencil"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-delete" data-id="${item.id}">
                                                    <i class="mdi mdi-delete"></i> Hapus
                                                </button>
                                            </td>
                                        @endcan
                                    </tr>
                                `;
                                tableBody.append(row);

                                sumQtyPallet += Number(item.qty_pallet || 0);
                                sumTotalQty += Number(item.subtotal_barang || 0);
                                sumTotalFee += Number(item.total_fee || 0);
                            });

                            const ppnAct = sumTotalFee * (PPN / 100);
                            const pphAct = sumTotalFee * (PPH / 100);
                            const grandTotal = sumTotalQty + sumTotalFee + ppnAct - pphAct;

                            $('#qtyPallet').text(fmtID(sumQtyPallet));
                            $('#totalQty').text(fmtID(sumTotalQty));
                            $('#totalFee').text(fmtID(sumTotalFee));
                            $('#tPpnAct').text(fmtID(ppnAct));
                            $('#tPphAct').text(fmtID(pphAct));
                            $('#tGrandTotal').text(fmtID(grandTotal));
                        } else {
                            showEmpty();
                        }
                    },
                    error: function() {
                        showEmpty();
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
                            url: "{{ url('/tkbm/ikat-terpal/destroy') }}/" + deleteId,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Terhapus!',
                                        text: response.message ||
                                            'Data berhasil dihapus.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });

                                    loadData();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: response.message ||
                                            'Terjadi kesalahan.'
                                    });
                                }
                            },
                            error: function(xhr) {
                                let response = xhr.responseJSON || {};
                                let msg = 'Gagal menghapus data. Silakan coba lagi.';

                                if (xhr.status === 404) {
                                    msg = response.message || 'Data tidak ditemukan.';
                                } else if (xhr.status === 403) {
                                    msg = response.message ||
                                        'Anda tidak memiliki izin untuk menghapus data ini.';
                                } else if (response.message) {
                                    msg = response.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: msg
                                });
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

            // Handler tombol Edit
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                const url = `{{ url('/tkbm/ikat-terpal/show') }}/${id}`; // sesuaikan route show/edit

                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const data = response.data;

                            $('#edit-id').val(data.id);
                            $('#edit-tanggal').val(data.tanggal);
                            $('#edit-qty_pallet').val(data.qty_pallet);
                            $('#edit-jml_buruh').val(data.jml_buruh || '');
                            $('#edit-catatan').val(data.catatan || '');

                            // Tampilkan modal
                            $('#modalEdit').modal('show');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal memuat data untuk edit.', 'error');
                    }
                });
            });

            // Handler tombol Update di modal
            $('#btn-update').on('click', function() {
                const id = $('#edit-id').val();
                const url = `{{ url('/tkbm/ikat-terpal/update') }}/${id}`; // route update

                const formData = $('#form-edit').serialize();

                $.ajax({
                    url: url,
                    method: 'PUT',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Data berhasil diupdate',
                                timer: 2000
                            });

                            $('#modalEdit').modal('hide');
                            loadData(); // refresh table
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Gagal update data';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: msg
                        });
                    }
                });
            });

            function showLoading() {
                $('#tableBody tr').addClass('d-none');
                $('#loadingRow').removeClass('d-none');
            }

            function showEmpty() {
                $('#tableBody tr').addClass('d-none');
                $('#emptyRow').removeClass('d-none');
            }

            function hideState() {
                $('#loadingRow, #emptyRow').addClass('d-none');
            }

        });
    </script>
@endsection
