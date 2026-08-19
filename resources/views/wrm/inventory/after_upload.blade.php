@extends('layouts.app')
@section('styles')
    <style>
        .select2-container--default .select2-selection--single {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: .375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endsection

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

                        <div class="row gy-3 mb-3">
                            <div class="col-md-3">
                                <label for="incoming_date" class="form-label fw-bold">Incoming Date <span
                                        class="text-danger">*</span></label>
                                <!-- @if (strtolower(Auth::user()->jabatan ?? '') == 'operator')
    <input type="date" name="incoming_date" id="incoming_date" class="form-control bg-light" value="{{ date('Y-m-d') }}" readonly>
                                        <small class="text-muted">Hanya dapat diubah oleh Admin/Supervisor</small>
@else
    <input type="date" name="incoming_date" id="incoming_date" class="form-control" value="{{ date('Y-m-d') }}" required>
    @endif -->
                                <input type="date" name="incoming_date" id="incoming_date" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-3">
                                <label for="supplier" class="form-label fw-bold">Supplier <span
                                        class="text-danger">*</span></label>
                                <select name="supplier" id="supplier" class="form-select" required>
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->nama }}">{{ $sup->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="pallet" class="form-label fw-bold">Pallet <span
                                        class="text-danger">*</span></label>
                                <select name="pallet" id="pallet" class="form-select" required>
                                    <option value="">Pilih Pallet</option>
                                    @foreach ($pallet as $plt)
                                        <option value="{{ $plt->nama_pallet }}">{{ $plt->nama_pallet }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="location"
                                    class="form-label fw-bold d-flex justify-content-between align-items-center">
                                    <span>Lokasi <span class="text-danger">*</span></span>
                                    <button type="button" id="btnResetLocations"
                                        class="btn btn-link text-danger p-0 text-decoration-none"
                                        style="font-size: 0.8rem;">
                                        <i class="mdi mdi-refresh"></i> Reset Pilihan
                                    </button>
                                </label>
                                <select id="locationSelect" class="form-select">
                                    <option value="">Pilih Lokasi per Bin</option>
                                    @foreach ($zones as $zone)
                                        <option value="{{ $zone['location_id'] }}">
                                            {{ $zone['plant'] }} - {{ $zone['s_loc'] }} - {{ $zone['gudang'] }} -
                                            {{ $zone['zona'] }} - {{ $zone['bin'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light align-middle">
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Barcode</th>
                                        <th>No SPB</th>
                                        <th>MID</th>
                                        <th>Pallet ID</th>
                                        <th>Qty</th>
                                        <th>Group</th>
                                        <th>Status
                                            <select id="globalStatus" class="form-select form-select-sm mt-1"
                                                style="min-width: 100px;">
                                                <option value="UNREST">UNREST</option>
                                                <option value="QI">QI</option>
                                                <option value="BLOCKED">BLOCKED</option>
                                            </select>
                                        </th>
                                        <th>Catatan</th>
                                        <th>Lokasi
                                            <br>
                                            <small class="text-muted">Plant - S Loc - Gudang - Zona - Bin - Bin
                                                Kordinat</small>
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
                                            <td>{{ (float) $row->qty }}</td>
                                            <td>{{ $row->group }}</td>
                                            <td>
                                                <select name="status[{{ $row->id }}]"
                                                    class="form-select form-select-sm item-status">
                                                    <option value="UNREST" selected>UNREST</option>
                                                    <option value="QI">QI</option>
                                                    <option value="BLOCKED">BLOCKED</option>
                                                </select>
                                            </td>
                                            <td style="min-width: 100px;">
                                                <input type="text" name="catatan[{{ $row->id }}]"
                                                    class="form-control form-control-sm" value="{{ $row->catatan }}"
                                                    placeholder="Catatan...">
                                            </td>
                                            <td class="bin-location" style="min-width: 250px;">
                                                <select name="loc_id[{{ $row->id }}]"
                                                    class="form-select form-select-sm manual-loc-select">
                                                    <option value="">- Pilih Lokasi -</option>
                                                </select>
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
                            <button type="submit" id="btnSubmitLocation" class="btn btn-primary"
                                {{ $locationError ? 'disabled' : '' }}>
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
            // Save original options of locationSelect
            const originalLocationOptions = $('#locationSelect').html();

            // Initialize Select2
            $('#supplier, #pallet, #locationSelect').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Pilih...',
                allowClear: true
            });

            $('#globalStatus').on('change', function() {
                const val = $(this).val();
                $('.item-status').val(val);
            });

            // Initialize row-level Select2 for manual location selection
            $('.manual-loc-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari Lokasi/Bin...',
                allowClear: true,
                ajax: {
                    url: "{{ route('wrm.inventory.getLocationsAjax') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        // Collect all currently selected loc_ids to exclude them from the search
                        let selectedIds = [];
                        $('.manual-loc-select').each(function() {
                            let val = $(this).val();
                            if (val) selectedIds.push(val);
                        });

                        return {
                            q: params.term,
                            exclude: selectedIds
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.data
                        };
                    },
                    cache: true
                }
            });

            $('#locationForm').on('submit', function(e) {
                e.preventDefault();

                // Check if location is selected
                let allLocationsFilled = true;
                $('.manual-loc-select').each(function() {
                    if (!$(this).val()) {
                        allLocationsFilled = false;
                        return false;
                    }
                });

                if (!allLocationsFilled) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Lokasi Belum Dipilih',
                        text: 'Silahkan tentukan lokasi untuk semua pallet terlebih dahulu'
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
                            url: "{{ route('wrm.inventory.cancel-upload') }}",
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

            // Handle Reset button click
            $('#btnResetLocations').on('click', function() {
                Swal.fire({
                    title: 'Reset Pilihan?',
                    text: 'Apakah Anda yakin ingin mengosongkan semua pilihan lokasi pada tabel?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Reset',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('.manual-loc-select').val(null).trigger('change');
                        // Restore original options in dropdown
                        $('#locationSelect').html(originalLocationOptions).val('').trigger(
                            'change.select2');
                    }
                });
            });

            $('#locationSelect').change(function() {
                let locationId = $(this).val();
                if (!locationId) return;

                let excludeBinIds = [];
                let emptyTempIds = [];

                $('tbody tr').each(function() {
                    let select = $(this).find('.manual-loc-select');
                    let val = select.val();
                    let name = select.attr('name');
                    if (!name) return;
                    let match = name.match(/\[(\d+)\]/);
                    if (!match) return;
                    let tempId = parseInt(match[1]);

                    if (val) {
                        excludeBinIds.push(val);
                    } else {
                        emptyTempIds.push(tempId);
                    }
                });

                if (emptyTempIds.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Info',
                        text: 'Semua baris sudah memiliki lokasi. Silahkan gunakan "Reset Pilihan" jika ingin mengisi ulang secara massal.'
                    });
                    $('#locationSelect').val('').trigger('change.select2');
                    return;
                }

                $.get("{{ route('wrm.inventory.plot-location') }}", {
                    loc_id: locationId,
                    no_spb: "{{ $currentNoSpb }}",
                    exclude_bin_ids: excludeBinIds,
                    temp_ids: emptyTempIds
                }, function(res) {
                    // Create a mapping by temp_id for fast lookup
                    let locationMap = {};
                    res.data.forEach(function(item) {
                        locationMap[item.temp_id] = item;
                    });

                    // Update each row based on temp_id (which is $row->id from Blade)
                    $('tbody tr').each(function() {
                        let select = $(this).find('.manual-loc-select');
                        let name = select.attr('name');
                        if (!name) return;

                        let match = name.match(/\[(\d+)\]/);
                        if (!match) return;

                        let tempId = parseInt(match[1]);

                        if (locationMap[tempId]) {
                            let loc = locationMap[tempId];
                            let text =
                                `${loc.plant} - ${loc.s_loc} - ${loc.gudang} - ${loc.zona} - ${loc.bin_id} - (${loc.bin_coordinate})`;

                            // Create the option and append to Select2
                            let newOption = new Option(text, loc.loc_id, true, true);
                            select.empty().append(newOption).trigger('change');
                        }
                    });

                    // Remove the plotted location from dropdown options so it won't be selectable again
                    $(`#locationSelect option[value="${locationId}"]`).remove();

                    // Clear dropdown so it can be selected again for another bin
                    $('#locationSelect').val('').trigger('change.select2');
                });
            });
        });
    </script>
@endsection
