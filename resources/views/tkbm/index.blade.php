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
                    <div class="page-title d-sm-flex align-items-center justify-content-between">
                        {{-- <h4 class="mb-sm-0">Form Input TKBM</h4> --}}

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">TKBM</a></li>
                                <li class="breadcrumb-item active">Input TKBM</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Form Input TKBM</h4>
                        </div>
                        <div class="card-body">
                            <div class="live-preview">
                                <div class="row gy-4">
                                    <div class="col-xxl-3 col-md-6">
                                        <div>
                                            <label for="date" class="form-label">Tanggal</label>
                                            <input type="date" class="form-control" id="date" name="date">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-6">
                                        <div>
                                            <label for="petugas" class="form-label">Petugas</label>
                                            <input type="text" class="form-control" id="petugas" name="petugas"
                                                placeholder="Masukkan Nama Petugas" value="{{ Auth::user()->username }}"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-6">
                                        <div>
                                            <label for="shift" class="form-label">Shift</label>
                                            <select name="shift" id="shift" class="form-select">
                                                <option value="" selected disabled>Pilih shift</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-6">
                                        <div>
                                            <label for="qtyTerpal" class="form-label">Cuci Terpal</label>
                                            <input type="number" class="form-control" id="qtyTerpal" name="qtyTerpal"
                                                placeholder="Masukkan Qty Terpal">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-6">
                                        <div>
                                            <label for="qtySlipsheet" class="form-label">Cuci Slipsheet</label>
                                            <input type="number" class="form-control" id="qtySlipsheet" name="qtySlipsheet"
                                                placeholder="Masukkan Qty Slipsheet">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-6">
                                        <div>
                                            <label for="qtyPallet" class="form-label">Cuci Pallet</label>
                                            <input type="number" class="form-control" id="qtyPallet" name="qtyPallet"
                                                placeholder="Masukkan Qty Pallet">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-6">
                                        <div>
                                            <label for="jmlTkbm" class="form-label">Jumlah TKBM</label>
                                            <input type="number" class="form-control" id="jmlTkbm" name="jmlTkbm"
                                                placeholder="Masukkan Jumlah TKBM">
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-md-6">
                                        <div>
                                            <label for="keterangan" class="form-label">Keterangan</label>
                                            <input type="text" class="form-control" id="keterangan" name="keterangan"
                                                placeholder="Masukkan Keterangan">
                                        </div>
                                    </div>
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
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // simpan data form
            $('#simpanBtn').click(function(e) {
                e.preventDefault();

                let date = $('#date').val();
                let petugas = $('#petugas').val();
                let shift = $('#shift').val();
                let qtyTerpal = $('#qtyTerpal').val();
                let qtySlipsheet = $('#qtySlipsheet').val();
                let qtyPallet = $('#qtyPallet').val();
                let jmlTkbm = $('#jmlTkbm').val();
                let keterangan = $('#keterangan').val();

                // Validasi input
                if (!date || !petugas || !shift || !jmlTkbm) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Field harus diisi!',
                    });
                    return;
                }

                // Kirim data ke server (contoh menggunakan AJAX)
                $.ajax({
                    url: "{{ route('tkbm.store') }}",
                    method: "POST",
                    data: {
                        date: date,
                        petugas: petugas,
                        shift: shift,
                        qtyTerpal: qtyTerpal || 0,
                        qtySlipsheet: qtySlipsheet || 0,
                        qtyPallet: qtyPallet || 0,
                        jml_tkbm: jmlTkbm,
                        keterangan: keterangan,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    headers: {
                        'Accept': 'application/json',
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data TKBM berhasil disimpan!',
                        });

                        // Reset form
                        $('#date').val('');
                        $('#petugas').val('');
                        $('#shift').val('shift_1');
                        $('#qtyTerpal').val('');
                        $('#qtySlipsheet').val('');
                        $('#qtyPallet').val('');
                        $('#jmlTkbm').val('');
                        $('#keterangan').val('');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let msg = 'Data duplikat atau tidak valid.';
                            // Jika controller kirim {message: "..."}
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                // Kumpulkan pesan validasi Laravel (jika ada)
                                const firstField = Object.keys(xhr.responseJSON.errors)[0];
                                msg = xhr.responseJSON.errors[firstField][0];
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg,
                            });
                            return;
                        }

                        // Error lain (500, 403, dll)
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menyimpan data.',
                        });
                    }
                });
            });

            // cancel button
            $('#cancelBtn').click(function(e) {
                e.preventDefault();
                // Reset form
                $('#date').val('');
                $('#petugas').val('');
                $('#shift').val('shift_1');
                $('#qtyTerpal').val('');
                $('#qtySlipsheet').val('');
                $('#qtyPallet').val('');
                $('#jmlTkbm').val('');
                $('#keterangan').val('');
            });
        });
    </script>
@endsection
