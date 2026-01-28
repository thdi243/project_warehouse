@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Master Ikat Terpal</h4>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-pills nav-justified" id="ikatTerpalTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="fee-tab" data-bs-toggle="tab" data-bs-target="#fee" type="button"
                        role="tab" aria-controls="fee" aria-selected="true">
                        Master Fee
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="produk-tab" data-bs-toggle="tab" data-bs-target="#produk" type="button"
                        role="tab" aria-controls="produk" aria-selected="false">
                        Master Harga
                    </button>
                </li>
            </ul>

            <div class="tab-content border border-top-0 p-4 bg-white shadow-sm mb-4" id="ikatTerpalTabsContent">

                <!-- Tab Fee -->
                <div class="tab-pane fade show active" id="fee" role="tabpanel" aria-labelledby="fee-tab">
                    <h5 class="mb-3">Master Fee Ikat Terpal</h5>
                    <p class="text-muted mb-4 small">Nilai sebelumnya akan dinonaktifkan otomatis saat simpan baru.</p>

                    <!-- Nilai Aktif -->
                    <div id="fee-aktif" class="alert alert-info mb-4 d-none">
                        <strong>Nilai Aktif Saat Ini:</strong><br>
                        Fee: <span id="fee-value"></span>%<br>
                        PPN: <span id="ppn-value"></span>%<br>
                        PPh: <span id="pph-value"></span>%<br>
                        Keterangan: <span id="keterangan-value"></span><br>
                        Created By: <span id="user-value"></span><br>
                        <small class="text-muted">Diaktifkan pada: <span id="created-fee"></span></small>
                    </div>

                    <form id="form-fee">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Fee (%)</label>
                                <input type="number" class="form-control" name="fee" step="0.01" min="0"
                                    placeholder="6.5">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">PPN (%)</label>
                                <input type="number" class="form-control" name="ppn" step="0.01" min="0"
                                    placeholder="11">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">PPh (%)</label>
                                <input type="number" class="form-control" name="pph" step="0.01" min="0"
                                    placeholder="2">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan" rows="2" placeholder="Alasan perubahan (opsional)"></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Simpan Fee Baru</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tab Produk -->
                <div class="tab-pane fade" id="produk" role="tabpanel" aria-labelledby="produk-tab">
                    <h5 class="mb-3">Master Harga Produk Ikat Terpal</h5>
                    <p class="text-muted mb-4 small">Harga sebelumnya akan dinonaktifkan otomatis saat simpan baru.</p>

                    <!-- Nilai Aktif -->
                    <div id="produk-aktif" class="alert alert-info mb-4 d-none">
                        <strong>Nilai Aktif Saat Ini:</strong><br>
                        Harga Pallet: Rp <span id="harga-value"></span><br>
                        Satuan: <span id="satuan-value"></span><br>
                        Keterangan: <span id="keterangan-produk-value"></span><br>
                        Created By: <span id="user-produk-value"></span><br>
                        <small class="text-muted">Diaktifkan pada: <span id="created-produk"></span></small>
                    </div>

                    <form id="form-produk">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Harga Pallet (Rp)</label>
                                <input type="number" class="form-control" name="harga_pallet" min="1"
                                    step="1" placeholder="1500000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Satuan</label>
                                <input type="text" class="form-control" name="satuan" placeholder="Pallet / Unit">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" name="keterangan" rows="2" placeholder="Alasan perubahan (opsional)"></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-success">Simpan Harga Baru</button>
                            </div>
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
            // Load data aktif saat halaman dibuka
            loadFeeAktif();
            loadProdukAktif();

            function formatIDNumber(value) {
                if (value === null || value === undefined || value === '') return '-';

                let num = Number(value);

                if (Number.isInteger(num)) {
                    return num.toLocaleString('id-ID');
                }

                return num
                    .toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    })
                    .replace(/,?0+$/, '');
            }


            function loadFeeAktif() {
                $.ajax({
                    url: "{{ url('wrm/master/ikat-terpal/fee-aktif') }}", // pastikan route ini benar
                    type: 'GET',
                    success: function(data) {
                        if (data.fee !== null) {
                            $('#fee-value').text(formatIDNumber(data.fee));
                            $('#ppn-value').text(formatIDNumber(data.ppn));
                            $('#pph-value').text(formatIDNumber(data.pph));
                            $('#keterangan-value').text(data.keterangan || '-');
                            $('#user-value').text(
                                data.user?.nama_lengkap || data.user?.username || '-'
                            );
                            $('#created-fee').text(data.created_at || '-');
                            $('#fee-aktif').removeClass('d-none');
                        }
                    }

                });
            }

            function loadProdukAktif() {
                $.ajax({
                    url: "{{ url('wrm/master/ikat-terpal/produk-aktif') }}",
                    type: 'GET',
                    success: function(data) {
                        if (data.harga_pallet !== null) {
                            $('#harga-value').text(formatIDNumber(data.harga_pallet));
                            $('#satuan-value').text(data.satuan || '-');
                            $('#keterangan-produk-value').text(data.keterangan || '-');
                            $('#user-produk-value').text(
                                data.user?.nama_lengkap || data.user?.username || '-'
                            );
                            $('#created-produk').text(data.created_at || '-');
                            $('#produk-aktif').removeClass('d-none');
                        }
                    }

                });
            }

            // Submit Fee
            $('#form-fee').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: "{{ url('wrm/master/ikat-terpal/store/fee') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content') // kalau pakai meta csrf
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.message || 'Fee berhasil disimpan',
                            timer: 2000
                        });
                        loadFeeAktif(); // refresh nilai aktif
                        $('#form-fee')[0].reset();
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: msg
                        });
                    }
                });
            });

            // Submit Produk (mirip)
            $('#form-produk').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: "{{ url('wrm/master/ikat-terpal/store/produk') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: response.message || 'Harga produk berhasil disimpan',
                            timer: 2000
                        });
                        loadProdukAktif();
                        $('#form-produk')[0].reset();
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: msg
                        });
                    }
                });
            });
        });
    </script>
@endsection
