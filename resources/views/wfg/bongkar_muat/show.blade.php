@extends('layouts.app')

@section('sidebar-size', 'sm')

@section('title', '| Verifikasi & Approval Bongkar Muat')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Bongkar Muat Approval & Verification</h4>
                        <div class="page-title-right">
                            <a href="{{ route('wfg.bongkar_muat.index') }}" class="btn btn-soft-secondary btn-sm">Back to
                                List</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5>Bongkar Muat: {{ $order->no_dokumen }}</h5>
                                <p class="text-muted mb-0">
                                    @if ($order->wavepick_smu)
                                        <span class="badge bg-soft-primary text-primary">SMU:
                                            {{ $order->wavepick_smu }}</span>
                                    @endif
                                    @if ($order->wavepick_bas)
                                        <span class="badge bg-soft-info text-info">BAS: {{ $order->wavepick_bas }}</span>
                                    @endif
                                </p>
                            </div>

                            <div class="row mb-4 bg-light p-3 rounded mx-2">
                                <div class="col-md-6 border-end">
                                    <p class="mb-1 text-muted small uppercase">Vehicle & Gate</p>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>No. Mobil:</span> <span class="fw-bold">{{ $order->no_mobil ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Gate:</span> <span class="fw-bold">{{ $order->gate ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Jam Muat:</span> <span class="fw-bold">{{ $order->jam_muat ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Tujuan:</span> <span
                                            class="fw-bold">{{ $order->destinasi->destinasi ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 ps-md-4">
                                    <p class="mb-1 text-muted small uppercase">Container & Seals</p>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>No. Kontainer:</span> <span
                                            class="fw-bold">{{ $order->no_kontainer ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Segel BAS:</span> <span
                                            class="fw-bold text-info">{{ $order->no_segel_bas ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Segel Vendor:</span> <span
                                            class="fw-bold text-info">{{ $order->no_segel_vendor ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Slipsheet:</span> <span
                                            class="fw-bold">{{ $order->jumlah_slipsheet ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Stepper Visual -->
                            <div class="position-relative m-4">
                                <div class="progress" style="height: 2px;">
                                    @php
                                        $progress = 0;
                                        if ($order->status == 'submitted') {
                                            $progress = 33;
                                        } elseif ($order->status == 'approved') {
                                            $progress = 66;
                                        } elseif (in_array($order->status, ['finished', 'verified'])) {
                                            $progress = 100;
                                        }
                                    @endphp
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                <div class="d-flex justify-content-between position-absolute top-0 w-100"
                                    style="margin-top: -12px;">
                                    <div class="text-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white"
                                            style="width: 25px; height: 25px; margin: 0 auto;">
                                            <i class="ri-check-line"></i>
                                        </div>
                                        <div class="mt-2 small fw-medium">Submitted</div>
                                    </div>
                                    <div class="text-center">
                                        @php $isCheckerDone = in_array($order->status, ['approved', 'finished', 'verified']); @endphp
                                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ $isCheckerDone ? 'bg-success text-white' : ($order->status == 'submitted' ? 'bg-primary text-white' : 'bg-light text-muted') }}"
                                            style="width: 25px; height: 25px; margin: 0 auto;">
                                            {!! $isCheckerDone ? '<i class="ri-check-line"></i>' : '2' !!}
                                        </div>
                                        <div class="mt-2 small fw-medium">Checker Apprv</div>
                                    </div>
                                    <div class="text-center">
                                        @php $isDriverDone = in_array($order->status, ['finished', 'verified']); @endphp
                                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ $isDriverDone ? 'bg-success text-white' : ($order->status == 'approved' ? 'bg-primary text-white' : 'bg-light text-muted') }}"
                                            style="width: 25px; height: 25px; margin: 0 auto;">
                                            {!! $isDriverDone ? '<i class="ri-check-line"></i>' : '3' !!}
                                        </div>
                                        <div class="mt-2 small fw-medium">Driver Apprv</div>
                                    </div>
                                    <div class="text-center">
                                        @php $isVerified = $order->status == 'verified'; @endphp
                                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ $isVerified ? 'bg-success text-white' : 'bg-light text-muted' }}"
                                            style="width: 25px; height: 25px; margin: 0 auto;">
                                            {!! $isVerified ? '<i class="ri-check-line"></i>' : '4' !!}
                                        </div>
                                        <div class="mt-2 small fw-medium">Verificator</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="mt-5 mb-4 border-dashed">

                            <!-- Material Summary Table (Grouped by MID) -->
                            <div class="mb-5 {{ in_array($order->status, ['finished', 'verified']) ? 'd-none' : '' }}">
                                <h6 class="text-uppercase fw-bold mb-3"><i class="ri-pie-chart-line me-1"></i> Bongkar Muat
                                    Summary (Per Material)</h6>
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-info text-center">
                                            <tr>
                                                <th>MID / Material</th>
                                                <th>Total Qty Full</th>
                                                <th>Total Qty Receh</th>
                                                <th>Total Item</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $summaries = $order->details->groupBy('material_id');
                                            @endphp
                                            @foreach ($summaries as $materialId => $items)
                                                @php
                                                    $material = $items->first()->material;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold">{{ $material->mid_barang ?? '-' }}</div>
                                                        <div class="small text-muted">{{ $material->nama_barang ?? '-' }}
                                                        </div>
                                                    </td>
                                                    <td class="text-center fw-bold text-primary">
                                                        {{ $items->where('jenis', 'P')->sum('qty') }}</td>
                                                    <td class="text-center fw-bold text-success">
                                                        {{ $items->where('jenis', 'R')->sum('qty') }}</td>
                                                    <td class="text-center">{{ $items->count() }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @if ($order->status == 'finished' && auth()->user()->hasRole('verificator-bongkar-muat-wfg'))
                                <form action="{{ route('wfg.bongkar_muat.validate', $order->id) }}" method="POST">
                                    @csrf
                            @endif

                            @if (in_array($order->status, ['finished', 'verified']))
                                <!-- Item Details Table -->
                                <div class="mb-5">
                                    <h6 class="text-uppercase fw-bold mb-3 text-muted"><i
                                            class="ri-list-settings-line me-1"></i> Individual Item Details</h6>
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light text-center text-nowrap" style="font-size: 13px;">
                                                <tr>
                                                    <th>MID</th>
                                                    <th>Batch</th>
                                                    <th>Type</th>
                                                    <th>Qty</th>
                                                    <th>TO Dummy</th>
                                                    <th>TO SAP</th>
                                                    <th>Flags</th>
                                                    <th width="180">No. TO</th>
                                                    <th width="130">Qty TO</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($order->details as $index => $detail)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                {{-- <span
                                                                    class="fw-medium text-dark">{{ $detail->material->nama_barang ?? '-' }}</span> --}}
                                                                <span
                                                                    class="text-dark">{{ $detail->material->mid_barang ?? '-' }}</span>
                                                            </div>
                                                            <input type="hidden" name="details[{{ $index }}][id]"
                                                                value="{{ $detail->id }}">
                                                        </td>
                                                        <td class="text-center">{{ $detail->batch_number ?? '-' }}</td>
                                                        <td class="text-center">
                                                            @if ($detail->jenis === 'P')
                                                                <span class="badge badge-soft-success">P</span>
                                                            @elseif ($detail->jenis === 'R')
                                                                <span class="badge badge-soft-danger">R</span>
                                                            @else
                                                                <span
                                                                    class="badge badge-soft-primary">{{ $detail->jenis }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center fw-medium">{{ $detail->qty }}</td>
                                                        <td class="text-center text-muted small">
                                                            {{ $detail->to_dummy ?? '-' }}
                                                        </td>
                                                        <td class="text-center text-muted small">
                                                            {{ $detail->to_sap ?? '-' }}
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($detail->double_po)
                                                                <span class="badge bg-soft-warning text-warning mb-1">2
                                                                    PO</span>
                                                            @endif
                                                            @if ($detail->cancel_to)
                                                                <span class="badge bg-soft-danger text-danger mb-1">Cancel
                                                                    TO</span>
                                                            @endif
                                                            @if ($detail->manual_picking)
                                                                <span class="badge bg-soft-success text-success">Manual
                                                                    Picking</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center" style="min-width: 150px;">
                                                            @if ($order->status == 'finished' && auth()->user()->hasRole('verificator-bongkar-muat-wfg'))
                                                                @if ($detail->double_po || $detail->cancel_to || $detail->manual_picking)
                                                                    <input type="number"
                                                                        name="details[{{ $index }}][no_to]"
                                                                        class="form-control form-control-sm text-center border-warning fw-bold"
                                                                        placeholder="No. TO" value="{{ $detail->no_to }}"
                                                                        required pattern="[0-9]+">
                                                                @else
                                                                    <span class="text-muted small">-</span>
                                                                @endif
                                                            @else
                                                                <span
                                                                    class="fw-bold text-primary">{{ $detail->no_to ?? '-' }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center" style="min-width: 150px;">
                                                            @if ($order->status == 'finished' && auth()->user()->hasRole('verificator-bongkar-muat-wfg'))
                                                                @if ($detail->cancel_to)
                                                                    <input type="number"
                                                                        name="details[{{ $index }}][qty_to]"
                                                                        class="form-control form-control-sm text-center border-danger fw-bold"
                                                                        placeholder="Qty TO"
                                                                        value="{{ $detail->qty_to }}" min="1"
                                                                        required>
                                                                @else
                                                                    <span class="text-muted small">-</span>
                                                                @endif
                                                            @else
                                                                <span
                                                                    class="fw-bold text-success">{{ $detail->qty_to ?? '-' }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Step Content -->
                            <div class="step-content px-3">
                                @if ($order->status == 'submitted')
                                    <div class="text-center mb-4">
                                        <h5 class="text-primary"><i class="ri-user-search-line me-2"></i>Step 1: Checker
                                            Approval</h5>
                                        <p class="text-muted">Persetujuan Checker menggunakan akun login Anda.</p>
                                    </div>
                                    <form id="checker-approval-form"
                                        action="{{ route('wfg.bongkar_muat.approve_checker', $order->id) }}"
                                        method="POST" class="w-75 mx-auto">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Checker</label>
                                            <input type="text" class="form-control text-dark bg-light fw-bold"
                                                value="{{ auth()->user()->username }}" readonly disabled>
                                            <input type="hidden" name="checker_id" value="{{ auth()->user()->id }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Signature <span class="text-danger">*</span></label>
                                            <div class="signature-container border border-dark shadow-sm"
                                                style="width: 100%; height: 200px; position: relative;">
                                                <canvas id="checker-signature-pad" class="signature-pad"
                                                    style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
                                                <button type="button"
                                                    class="btn btn-sm btn-light position-absolute top-0 end-0 m-2"
                                                    id="clear-checker-sig">Clear</button>
                                            </div>
                                            <input type="hidden" name="signature" id="checker-signature-data">
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary px-5">Approve Checker</button>
                                        </div>
                                    </form>
                                @elseif($order->status == 'approved')
                                    <div class="text-center mb-4">
                                        <h5 class="text-primary"><i class="ri-steering-2-line me-2"></i>Step 2: Driver
                                            Approval</h5>
                                        <p class="text-muted">Masukkan nama driver yang akan membawa muatan.</p>
                                    </div>
                                    <form id="driver-approval-form"
                                        action="{{ route('wfg.bongkar_muat.approve_driver', $order->id) }}"
                                        method="POST" class="w-75 mx-auto">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Nama Driver <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="driver_name" class="form-control" required
                                                placeholder="Contoh: Budi Susanto">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Signature Driver <span
                                                    class="text-danger">*</span></label>
                                            <div class="signature-container border border-dark rounded"
                                                style="width: 100%; height: 200px; position: relative;">
                                                <canvas id="driver-signature-pad" class="signature-pad"
                                                    style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
                                                <button type="button"
                                                    class="btn btn-sm btn-light position-absolute top-0 end-0 m-2"
                                                    id="clear-driver-sig">Clear</button>
                                            </div>
                                            <input type="hidden" name="signature" id="driver-signature-data">
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary px-5">Approve Driver</button>
                                        </div>
                                    </form>
                                @elseif($order->status == 'finished')
                                    <div class="text-center mb-4">
                                        <h5 class="text-primary"><i class="ri-shield-check-line me-2"></i>Step 3: Final
                                            Verification</h5>
                                        <p class="text-muted">Order ini sudah di-approve oleh Checker dan Driver.
                                            Verificator silakan mengisi kolom input di rincian item di atas
                                            (jika ada flag Double PO/Cancel TO), lalu klik tombol di bawah untuk verifikasi.
                                        </p>
                                    </div>

                                    @if (auth()->user()->hasRole('verificator-bongkar-muat-wfg'))
                                        <div class="w-80 mx-auto text-start bg-light p-4 rounded border mb-4">
                                            @php
                                                $userSignature = auth()->user()->signature;
                                            @endphp

                                            @if ($userSignature && $userSignature->signature)
                                                @php
                                                    $sigPath = $userSignature->signature;
                                                    if (
                                                        !Str::startsWith($sigPath, 'storage/') &&
                                                        !Str::startsWith($sigPath, 'http') &&
                                                        !Str::startsWith($sigPath, '/storage/')
                                                    ) {
                                                        $sigPath = 'storage/' . $sigPath;
                                                    }
                                                @endphp
                                                <div class="mb-3 text-center border-bottom pb-3">
                                                    <label class="form-label d-block fw-bold text-muted">TTD Profil Anda
                                                        Saat Ini:</label>
                                                    <div class="p-2 border rounded bg-white d-inline-block shadow-sm mb-3">
                                                        <img src="{{ asset($sigPath) }}" alt="Saved Signature"
                                                            style="filter: grayscale(1) contrast(2);">
                                                    </div>

                                                    <div
                                                        class="form-check form-switch justify-content-center d-flex align-items-center">
                                                        <input class="form-check-input me-2" type="checkbox"
                                                            name="use_stored_signature" id="useStoredSignature"
                                                            value="1" checked>
                                                        <label class="form-check-label fw-bold text-success"
                                                            for="useStoredSignature">
                                                            Gunakan TTD Tersimpan dari Profil
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif

                                            <div id="verificator-pad-wrapper"
                                                style="{{ $userSignature && $userSignature->signature ? 'display: none;' : '' }}">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Signature Verifikator <span
                                                            class="text-danger">*</span></label>
                                                    <div class="signature-container border rounded bg-white shadow-sm"
                                                        style="width: 100%; height: 200px; position: relative;">
                                                        <canvas id="verificator-signature-pad" class="signature-pad"
                                                            style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
                                                        <button type="button"
                                                            class="btn btn-sm btn-light position-absolute top-0 end-0 m-2"
                                                            id="clear-verificator-sig">Clear</button>
                                                    </div>
                                                    <input type="hidden" name="signature"
                                                        id="verificator-signature-data">
                                                </div>
                                            </div>

                                            <div class="mb-3 mt-3">
                                                <label class="form-label fw-bold">Catatan Verifikator (Optional)</label>
                                                <textarea name="verified_note" class="form-control" rows="3"
                                                    placeholder="Masukkan catatan tambahan jika diperlukan..."></textarea>
                                            </div>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="submit" id="btn-submit-verification"
                                                class="btn btn-success px-5 fs-5 shadow-sm">
                                                <i class="ri-check-double-line"></i> Verifikasi Data
                                            </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="text-center mt-4">
                                            <div class="alert alert-warning d-inline-block px-4">
                                                <i class="ri-lock-line me-2"></i> Menunggu proses verifikasi akhir. Anda
                                                tidak memiliki akses (role: <b>verificator-bongkar-muat-wfg</b>) untuk
                                                melakukan proses ini.
                                            </div>
                                        </div>
                                    @endif
                                @elseif($order->status == 'verified')
                                    <div class="text-center py-4">
                                        <div class="avatar-lg mx-auto mb-3">
                                            <div class="avatar-title bg-soft-success text-success rounded-circle fs-1">
                                                <i class="ri-checkbox-circle-line"></i>
                                            </div>
                                        </div>
                                        <h5>Approval & Verifikasi Selesai!</h5>
                                        <div class="row mt-4 mb-4">
                                            <div class="col-md-4 text-center border-end">
                                                <p class="mb-1 text-muted small">Checker:
                                                    <b>{{ $order->checker->username ?? '-' }}</b>
                                                </p>
                                                @if ($order->checker_signature)
                                                    <img src="{{ asset($order->checker_signature) }}" alt="Checker Sig"
                                                        style="max-height: 80px; filter: grayscale(1) contrast(2);">
                                                @else
                                                    <p class="text-muted small italic">No signature</p>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-center border-end">
                                                <p class="mb-1 text-muted small">Driver:
                                                    <b>{{ $order->driver_name ?? '-' }}</b>
                                                </p>
                                                @if ($order->driver_signature)
                                                    <img src="{{ asset($order->driver_signature) }}" alt="Driver Sig"
                                                        style="max-height: 80px; filter: grayscale(1) contrast(2);">
                                                @else
                                                    <p class="text-muted small italic">No signature</p>
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <p class="mb-1 text-muted small">Verificator:
                                                    <b>{{ $order->verificator->username ?? '-' }}</b>
                                                </p>
                                                @if ($order->verified_signature)
                                                    @php
                                                        $vSigPath = $order->verified_signature;
                                                        if (
                                                            !Str::startsWith($vSigPath, 'storage/') &&
                                                            !Str::startsWith($vSigPath, 'http') &&
                                                            !Str::startsWith($vSigPath, '/storage/')
                                                        ) {
                                                            $vSigPath = 'storage/' . $vSigPath;
                                                        }
                                                    @endphp
                                                    <img src="{{ asset($vSigPath) }}" alt="Verificator Sig"
                                                        style="max-height: 80px; filter: grayscale(1) contrast(2);">
                                                @else
                                                    <p class="text-muted small italic">No signature</p>
                                                @endif
                                                @if ($order->verified_note)
                                                    <div class="mt-2 small text-muted italic">
                                                        "{{ $order->verified_note }}"</div>
                                                @endif
                                            </div>
                                        </div>
                                        <a href="{{ route('wfg.bongkar_muat.index') }}"
                                            class="btn btn-light mt-2">Kembali
                                            ke Data</a>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <h5 class="text-danger">Status Tidak Valid</h5>
                                        <p class="text-muted">Order ini berada dalam status <b>{{ $order->status }}</b>
                                            dan
                                            tidak dapat di-approve melalui form ini.</p>
                                    </div>
                                @endif
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
            @if (session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: "{{ session('info') }}",
                    timer: 3000,
                    showConfirmButton: true
                });
            @endif

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "{{ session('success') }}",
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    @if (session('success') === 'Driver approved successfully.')
                        window.location.href = "{{ route('wfg.bongkar_muat.index') }}";
                    @endif
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: "{{ session('error') }}",
                    showConfirmButton: true
                });
            @endif

            // Signature Pads Initialization
            function initSignature(canvasId, inputId, clearBtnId) {
                const canvas = document.getElementById(canvasId);
                if (!canvas) return null;

                // Handle retina displays
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);

                const signaturePad = new SignaturePad(canvas, {
                    penColor: 'rgb(0, 0, 0)'
                });

                document.getElementById(clearBtnId).addEventListener('click', function() {
                    signaturePad.clear();
                    document.getElementById(inputId).value = '';
                });

                return signaturePad;
            }

            function trimCanvas(canvas) {
                const ctx = canvas.getContext('2d');
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;
                const imageData = ctx.getImageData(0, 0, imgWidth, imgHeight);
                const data = imageData.data;

                let minX = imgWidth;
                let minY = imgHeight;
                let maxX = -1;
                let maxY = -1;

                for (let y = 0; y < imgHeight; y++) {
                    for (let x = 0; x < imgWidth; x++) {
                        const alphaIndex = ((y * imgWidth) + x) * 4 + 3;
                        const alpha = data[alphaIndex];

                        if (alpha > 0) {
                            if (x < minX) minX = x;
                            if (y < minY) minY = y;
                            if (x > maxX) maxX = x;
                            if (y > maxY) maxY = y;
                        }
                    }
                }

                if (maxX === -1) {
                    return canvas.toDataURL();
                }

                const padding = 25;
                minX = Math.max(0, minX - padding);
                minY = Math.max(0, minY - padding);
                maxX = Math.min(imgWidth - 1, maxX + padding);
                maxY = Math.min(imgHeight - 1, maxY + padding);

                const croppedWidth = maxX - minX + 1;
                const croppedHeight = maxY - minY + 1;

                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = croppedWidth;
                tempCanvas.height = croppedHeight;

                const tempCtx = tempCanvas.getContext('2d');
                tempCtx.drawImage(canvas, minX, minY, croppedWidth, croppedHeight, 0, 0, croppedWidth, croppedHeight);

                return tempCanvas.toDataURL();
            }

            @if ($order->status == 'submitted')
                const checkerPad = initSignature('checker-signature-pad', 'checker-signature-data',
                    'clear-checker-sig');
                $('#checker-approval-form').on('submit', function(e) {
                    if (checkerPad.isEmpty()) {
                        e.preventDefault();
                        Swal.fire('Error', 'Signature is required.', 'error');
                    } else {
                        document.getElementById('checker-signature-data').value = trimCanvas(document.getElementById('checker-signature-pad'));
                    }
                });
            @elseif ($order->status == 'approved')
                const driverPad = initSignature('driver-signature-pad', 'driver-signature-data',
                    'clear-driver-sig');
                $('#driver-approval-form').on('submit', function(e) {
                    if (driverPad.isEmpty()) {
                        e.preventDefault();
                        Swal.fire('Error', 'Signature is required.', 'error');
                    } else {
                        document.getElementById('driver-signature-data').value = trimCanvas(document.getElementById('driver-signature-pad'));
                    }
                });
            @elseif ($order->status == 'finished' && auth()->user()->hasRole('verificator-bongkar-muat-wfg'))
                const verificatorPad = initSignature('verificator-signature-pad', 'verificator-signature-data',
                    'clear-verificator-sig');

                // Toggle signature pad wrapper based on checkbox state
                $('#useStoredSignature').on('change', function() {
                    if (this.checked) {
                        $('#verificator-pad-wrapper').slideUp();
                    } else {
                        $('#verificator-pad-wrapper').slideDown();
                        // Re-initialize pad size when displayed
                        setTimeout(() => {
                            if (verificatorPad) {
                                const canvas = document.getElementById('verificator-signature-pad');
                                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                                canvas.width = canvas.offsetWidth * ratio;
                                canvas.height = canvas.offsetHeight * ratio;
                                canvas.getContext("2d").scale(ratio, ratio);
                                verificatorPad.clear();
                            }
                        }, 200);
                    }
                });

                // Validate on submit (only one form exists on this page when status is finished)
                $('form').on('submit', function(e) {
                    const useStored = $('#useStoredSignature').is(':checked');
                    if (!useStored) {
                        if (!verificatorPad || verificatorPad.isEmpty()) {
                            e.preventDefault();
                            Swal.fire('Peringatan', 'Tanda tangan wajib diisi.', 'warning');
                        } else {
                            document.getElementById('verificator-signature-data').value = trimCanvas(document.getElementById('verificator-signature-pad'));
                        }
                    }
                });
            @endif
        });
    </script>
@endsection
