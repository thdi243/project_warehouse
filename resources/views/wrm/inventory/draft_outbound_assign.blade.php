@extends('layouts.app')

@section('title', ' | Assign Driver Forklift')

@section('styles')
    <style>
        .summary-card {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #ffffff;
            border: none;
        }

        .summary-label {
            color: #94a3b8;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .summary-value {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .inline-driver-select {
            min-width: 160px;
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
        }

        .form-actions {
            position: sticky;
            bottom: 0;
            z-index: 3;
            background-color: rgba(255, 255, 255, 0.85);
            padding: 15px;
            backdrop-filter: blur(6px);
            border-top: 1px solid #cbd5e1;
            margin-top: 20px;
            border-radius: 0 0 8px 8px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Back & Title -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('wrm.inventory.data-outbound') }}" class="btn btn-outline-secondary">
                        <i class="mdi mdi-arrow-left me-1"></i> Kembali ke List
                    </a>
                    <h4 class="mb-0 fw-bold">Assign Driver Forklift per Item</h4>
                </div>
            </div>

            <!-- Draft Summary Card -->
            <div class="card summary-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-2 col-6 border-end border-secondary">
                            <div class="summary-label">No Reservasi</div>
                            <div class="summary-value text-info">{{ $outbound->no_reservasi }}</div>
                        </div>
                        <div class="col-md-2 col-6 border-end border-secondary">
                            <div class="summary-label">Tanggal Reservasi</div>
                            <div class="summary-value">
                                {{ \Carbon\Carbon::parse($outbound->reservasi_date)->format('d M Y') }}
                            </div>
                        </div>
                        <div class="col-md-2 col-6 border-end border-secondary">
                            <div class="summary-label">Shift</div>
                            <div class="summary-value">Shift {{ $outbound->shift }}</div>
                        </div>
                        <div class="col-md-2 col-6 border-end border-secondary">
                            <div class="summary-label">Total Qty (KG)</div>
                            <div class="summary-value">
                                {{ number_format($outbound->details->sum('qty'), 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-md-2 col-6 border-end border-secondary">
                            <div class="summary-label">Total Pallet</div>
                            <div class="summary-value">{{ $outbound->details->count() }} Pallet</div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="summary-label">Status Draft</div>
                            <div>
                                @if ($outbound->status_transfer === 'PENDING')
                                    <span class="badge bg-warning text-dark">PENDING</span>
                                @elseif($outbound->status_transfer === 'ASSIGNED')
                                    <span class="badge bg-info">ASSIGNED</span>
                                @elseif($outbound->status_transfer === 'COMPLETED')
                                    <span class="badge bg-success">COMPLETED</span>
                                @else
                                    <span class="badge bg-secondary">{{ $outbound->status_transfer }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Tool & Items Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
                    <h5 class="mb-0 fw-bold">Daftar Item Draft Outbound</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle text-nowrap" id="tableItems">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;">
                                        <input type="checkbox" id="checkAllItems" class="form-check-input">
                                    </th>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>No Barcode</th>
                                    <th>No SPB</th>
                                    <th>Supplier</th>
                                    <th>Pallet ID</th>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th>Group</th>
                                    <th class="text-end">Qty (KG)</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                    <th style="min-width: 180px;">Driver Forklift</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($outbound->details as $index => $detail)
                                    <tr>
                                        <td class="text-center">
                                            @if ($detail->status === 'RESERVED')
                                                <input type="checkbox" class="form-check-input select-item"
                                                    data-id="{{ $detail->id }}">
                                            @else
                                                <input type="checkbox" class="form-check-input" disabled>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $detail->barcode ?? '-' }}</td>
                                        <td>{{ $detail->no_spb ?? '-' }}</td>
                                        <td style="font-size: 11px; white-space: normal; max-width: 150px;">
                                            {{ $detail->supplier ?? '-' }}
                                        </td>
                                        <td class="fw-bold">{{ $detail->pallet_id }}</td>
                                        <td>{{ $detail->barang->mid }}</td>
                                        <td style="font-size: 11px; white-space: normal; max-width: 180px;">
                                            {{ $detail->barang->nama_barang }}
                                        </td>
                                        <td>{{ $detail->group ?? '-' }}</td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($detail->qty, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            @if ($detail->status === 'RESERVED')
                                                <span class="badge bg-soft-warning text-warning">RESERVED</span>
                                            @elseif($detail->status === 'ISSUED')
                                                <span class="badge bg-soft-success text-success">ISSUED</span>
                                            @elseif($detail->status === 'BA WAITING')
                                                <span class="badge bg-soft-danger text-danger">BA WAITING</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $detail->status }}</span>
                                            @endif
                                        </td>
                                        <td style="font-size: 11px;">
                                            @if ($detail->bin && $detail->bin->location)
                                                {{ $detail->bin->location->plant }} -
                                                {{ $detail->bin->location->gudang }} -
                                                {{ $detail->bin->location->bin }} -
                                                ({{ $detail->bin->kolom }}.{{ $detail->bin->level }})
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($detail->status === 'RESERVED')
                                                <select class="form-select form-select-sm inline-driver-select"
                                                    data-id="{{ $detail->id }}">
                                                    <option value="">-- Assign Driver --</option>
                                                    @foreach ($drivers as $driver)
                                                        <option value="{{ $driver->id }}"
                                                            {{ $detail->driver_id == $driver->id ? 'selected' : '' }}>
                                                            {{ $driver->nama_lengkap ?? $driver->username }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <span class="fw-bold text-dark">
                                                    {{ $detail->driver->nama_lengkap ?? '-' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center text-muted py-4">
                                            Tidak ada item pada draft ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Sticky Bottom Action Bar -->
                    <div class="form-actions d-flex justify-content-end align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select form-select-md" id="bulkDriverSelect" style="min-width: 260px;">
                                <option value="">-- Pilih Driver Forklift --</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}">
                                        {{ $driver->nama_lengkap ?? $driver->username }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary text-nowrap" id="btnBulkAssign" disabled>
                                <i class="mdi mdi-account-plus me-1"></i> Assign ke Item Terpilih (<span
                                    id="checkedCount">0</span>)
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
            // Initialize Select2
            $('#bulkDriverSelect').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- Pilih Driver Forklift --',
                allowClear: true,
                dropdownParent: $('.form-actions')
            });

            $('.inline-driver-select').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Checkbox changes and count update
            function updateBulkControls() {
                let checked = $('.select-item:checked').length;
                $('#checkedCount').text(checked);
                $('#btnBulkAssign').prop('disabled', checked === 0);
            }

            $('#checkAllItems').change(function() {
                let isChecked = $(this).prop('checked');
                $('.select-item').prop('checked', isChecked);
                updateBulkControls();
            });

            $(document).on('change', '.select-item', function() {
                if ($('.select-item:checked').length === $('.select-item').length) {
                    $('#checkAllItems').prop('checked', true);
                } else {
                    $('#checkAllItems').prop('checked', false);
                }
                updateBulkControls();
            });

            // Inline individual driver assignment
            $(document).on('change', '.inline-driver-select', function() {
                let itemId = $(this).data('id');
                let driverId = $(this).val();

                if (!driverId) {
                    return; // Avoid hitting API if reset to blank manually (or prompt if necessary)
                }

                $.ajax({
                    url: "{{ route('wrm.inventory.assign-driver-items') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: [itemId],
                        driver_id: driverId
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
                            text: res.message ?? 'Driver forklift berhasil di-assign.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errMsg = xhr.responseJSON?.message ?? 'Terjadi kesalahan sistem';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errMsg
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });

            // Bulk driver assignment
            $('#btnBulkAssign').click(function() {
                let driverId = $('#bulkDriverSelect').val();
                if (!driverId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Driver Belum Dipilih',
                        text: 'Silahkan pilih driver forklift terlebih dahulu.'
                    });
                    return;
                }

                let selectedIds = [];
                $('.select-item:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });

                $.ajax({
                    url: "{{ route('wrm.inventory.assign-driver-items') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: selectedIds,
                        driver_id: driverId
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
                            text: res.message ??
                                'Driver forklift berhasil di-assign ke item terpilih.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errMsg = xhr.responseJSON?.message ?? 'Terjadi kesalahan sistem';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errMsg
                        });
                    }
                });
            });
        });
    </script>
@endsection
