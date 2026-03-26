@extends('layouts.app')

@section('title', ' | Inventory Stock Location')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="mdi mdi-warehouse"></i>
                            Penentuan Lokasi Gudang
                        </h5>
                        <small class="text-muted d-block mt-2">
                            No SPB: <strong>{{ $currentNoSpb }}</strong>
                            @if ($remainingCount > 0)
                                | <span class="badge bg-info">Masih ada {{ $remainingCount }} no_spb lain</span>
                            @else
                                | <span class="badge bg-success">Ini adalah no_spb terakhir</span>
                            @endif
                        </small>
                    </div>
                </div>

                <div class="card-body">

                    <form id="locationForm" method="POST" action="{{ route('wrm.inventory.store-upload') }}">
                        @csrf

                        <div class="row gy-2 mb-3">
                            <div class="col-md-4">
                                <label for="supplier" class="form-label fw-bold">Supplier</label>
                                <input type="text" name="supplier" id="supplier" class="form-control"
                                    placeholder="Masukkan Supplier" required>
                            </div>

                            <div class="col-md-4">
                                <label for="pallet" class="form-label fw-bold">Pallet</label>
                                <select name="pallet" id="pallet" class="form-select" required>
                                    <option value="">Pilih Pallet</option>
                                    @foreach ($pallet as $plt)
                                        <option value="{{ $plt->nama_pallet }}">{{ $plt->nama_pallet }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="location" class="form-label fw-bold">Lokasi</label>
                                <select id="zoneSelect" class="form-select">

                                    <option value="">Pilih Lokasi by Zona</option>

                                    @foreach ($zones as $zone)
                                        <option value="{{ $zone['zona'] }}">
                                            {{ $zone['plant'] }} - {{ $zone['s_loc'] }} - {{ $zone['zona'] }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light align-middle">
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Barcode</th>
                                        <th>No SPB</th>
                                        <th>MID</th>
                                        <th>Pallet ID</th>
                                        <th>Qty</th>
                                        <th>Group</th>
                                        <th>Lokasi
                                            <br>
                                            <small class="text-muted">Plant - S Loc - Zona - Bin</small>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($data as $i => $row)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $row->barcode }}</td>
                                            <td>{{ $row->no_spb }}</td>
                                            <td>{{ $row->mid }}</td>
                                            <td>{{ $row->pallet_id }}</td>
                                            <td>{{ $row->qty }}</td>
                                            <td>{{ $row->group }}</td>
                                            <td class="bin-location">
                                                -
                                                <input type="hidden" name="loc_id[{{ $row->id }}]" class="loc-id">
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <button type="button" id="btnCancel" class="btn btn-danger me-2">
                                <i class="mdi mdi-close"></i>
                                Batal Upload
                            </button>
                            <button type="submit" class="btn btn-primary"
                                @if ($locationError) disabled @endif>
                                <i class="mdi mdi-content-save"></i>
                                @if ($remainingCount > 0)
                                    Simpan & Lanjut ke No SPB Berikutnya
                                @else
                                    Simpan Lokasi (Selesai)
                                @endif
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($locationError)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: '{{ $locationError }}',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <script>
        $(document).ready(function() {
            $('#locationForm').on('submit', function(e) {
                e.preventDefault();

                // Check if location is selected
                let allLocationsFilled = true;
                $('.loc-input').each(function() {
                    if (!$(this).val()) {
                        allLocationsFilled = false;
                        return false;
                    }
                });

                if (!allLocationsFilled) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Lokasi Belum Dipilih',
                        text: 'Silahkan pilih lokasi terlebih dahulu'
                    });
                    return;
                }

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Sedang memproses data no_spb {{ $currentNoSpb }}',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,

                    success: function(res) {
                        // Cek apakah ada no_spb berikutnya
                        if (res.hasNext) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message ?? 'Data berhasil disimpan',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload untuk ambil no_spb berikutnya
                                window.location.href =
                                    "{{ route('wrm.inventory.select-location') }}";
                            });
                        } else {
                            // Semua no_spb sudah selesai
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message ?? 'Semua data berhasil disimpan',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href =
                                    "{{ route('wrm.inventory.index') }}";
                            });
                        }
                    },

                    error: function(xhr) {
                        Swal.close();

                        let message = 'Terjadi kesalahan pada server';

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            message = Object.values(errors).map(err => err[0]).join('<br>');
                        } else if (xhr.responseJSON?.message) {
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

            $('#btnCancel').on('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin membatalkan upload? Semua data yang tersimpan akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Batalkan',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('wrm.inventory.cancel-upload') }}',
                            method: 'POST',
                            data: {
                                '_token': $('meta[name="csrf-token"]').attr('content')
                            },

                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message ??
                                        'Upload berhasil dibatalkan',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href =
                                        "{{ route('wrm.inventory.index-upload') }}";
                                });
                            },

                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: xhr.responseJSON?.message ??
                                        'Terjadi kesalahan'
                                });
                            }
                        });
                    }
                });
            });

            $('#zoneSelect').change(function() {
                let zona = $(this).val();
                if (!zona) return;

                $.get("{{ route('wrm.inventory.plot-location') }}", {
                    zona: zona
                }, function(res) {
                    // Create a mapping by temp_id for fast lookup
                    let locationMap = {};
                    res.data.forEach(function(item) {
                        locationMap[item.temp_id] = item;
                    });

                    // Update each row based on temp_id (which is $row->id from Blade)
                    $('tbody tr').each(function() {
                        let tempId = $(this).find('.loc-id').attr('name');

                        // Extract temp_id from name="loc_id[123]" format
                        let match = tempId.match(/\[(\d+)\]/);
                        if (!match) return;

                        tempId = parseInt(match[1]);

                        if (locationMap[tempId]) {
                            let loc = locationMap[tempId];
                            let lokasi =
                                `${loc.plant} - ${loc.s_loc} - ${loc.zona} - ${loc.bin}`;

                            $(this).find('.bin-location').contents().first().replaceWith(
                                lokasi);
                            $(this).find('.loc-id').val(loc.loc_id);
                        } else {
                            // If no location assigned (bins ran out), show warning
                            $(this).find('.bin-location').contents().first().replaceWith(
                                '❌ Tidak ada bin');
                            $(this).find('.loc-id').val('');
                        }
                    });
                });
            });
        });
    </script>
@endsection
