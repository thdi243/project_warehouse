@extends('layouts.app')

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
                <div class="col-xl-10 col-lg-12">
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
                                        } elseif (in_array($order->status, ['loaded', 'verified'])) {
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
                                        @php $isCheckerDone = in_array($order->status, ['approved', 'loaded', 'verified']); @endphp
                                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ $isCheckerDone ? 'bg-success text-white' : ($order->status == 'submitted' ? 'bg-primary text-white' : 'bg-light text-muted') }}"
                                            style="width: 25px; height: 25px; margin: 0 auto;">
                                            {!! $isCheckerDone ? '<i class="ri-check-line"></i>' : '2' !!}
                                        </div>
                                        <div class="mt-2 small fw-medium">Checker Apprv</div>
                                    </div>
                                    <div class="text-center">
                                        @php $isDriverDone = in_array($order->status, ['loaded', 'verified']); @endphp
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
                            <div class="mb-5">
                                <h6 class="text-uppercase fw-bold mb-3"><i class="ri-pie-chart-line me-1"></i> Bongkar Muat
                                    Summary (Per Material)</h6>
                                <div class="table-responsive">
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

                            <!-- Item Details Table -->
                            {{-- <div class="mb-5">
                            <h6 class="text-uppercase fw-bold mb-3 text-muted"><i class="ri-list-settings-line me-1"></i> Individual Item Details (Checker/Driver Data)</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>Material</th>
                                            <th>Batch</th>
                                            <th>Type</th>
                                            <th>Qty Full</th>
                                            <th>Qty Receh</th>
                                            <th>TO Dummy</th>
                                            <th>TO SAP</th>
                                            <th>Flags</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->details as $detail)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium">{{ $detail->material->nama_barang ?? '-' }}</span>
                                                    <small class="text-muted">{{ $detail->material->mid_barang ?? '-' }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $detail->batch_number ?? '-' }}</td>
                                            <td class="text-center"><span class="badge bg-soft-primary text-primary">{{ $detail->jenis }}</span></td>
                                            <td class="text-center">{{ $detail->qty_full }}</td>
                                            <td class="text-center">{{ $detail->qty_receh }}</td>
                                            <td class="text-center text-muted small">{{ $detail->to_dummy ?? '-' }}</td>
                                            <td class="text-center text-muted small">{{ $detail->to_sap ?? '-' }}</td>
                                            <td class="text-center">
                                                @if ($detail->double_po)
                                                    <span class="badge bg-soft-warning text-warning" title="Double PO">2 PO</span>
                                                @endif
                                                @if ($detail->cancel_to)
                                                    <span class="badge bg-soft-danger text-danger" title="Cancel TO">Cancel</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div> --}}

                            <!-- Step Content -->
                            <div class="step-content px-3">
                                @if ($order->status == 'submitted')
                                    <div class="text-center mb-4">
                                        <h5 class="text-primary"><i class="ri-user-search-line me-2"></i>Step 1: Checker
                                            Approval</h5>
                                        <p class="text-muted">Pilih checker yang melakukan proses loading.</p>
                                    </div>
                                    <form id="checker-approval-form"
                                        action="{{ route('wfg.bongkar_muat.approve_checker', $order->id) }}"
                                        method="POST" class="w-75 mx-auto">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Checker <span class="text-danger">*</span></label>
                                            <select name="checker_id" class="form-select" required>
                                                <option value="">-- Pilih Checker --</option>
                                                @foreach ($checkers as $checker)
                                                    <option value="{{ $checker->id }}">{{ $checker->username }}</option>
                                                @endforeach
                                            </select>
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
                                            <div class="signature-container border rounded"
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
                                            <button type="submit" class="btn btn-primary px-5">Approve Driver & Route to
                                                Verificator</button>
                                        </div>
                                    </form>
                                @elseif($order->status == 'loaded')
                                    <div class="text-center mb-4">
                                        <h5 class="text-primary"><i class="ri-shield-check-line me-2"></i>Step 3: Final
                                            Verification</h5>
                                        <p class="text-muted">Order ini sudah di-approve oleh Checker dan Driver, dan siap
                                            untuk diverifikasi akhir oleh Verificator Bongkar Muat WFG.</p>
                                    </div>
                                    <div class="text-center mt-4">
                                        @if (auth()->user()->hasRole('verificator-bongkar-muat-wfg'))
                                            <form action="{{ route('wfg.bongkar_muat.validate', $order->id) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success px-5 fs-5 shadow-sm">
                                                    <i class="ri-check-double-line"></i> Validasi Order
                                                </button>
                                            </form>
                                        @else
                                            <div class="alert alert-warning d-inline-block px-4">
                                                <i class="ri-lock-line me-2"></i> Menunggu proses verifikasi akhir. Anda
                                                tidak memiliki akses (role: <b>verificator-bongkar-muat-wfg</b>) untuk melakukan proses ini.
                                            </div>
                                        @endif
                                    </div>
                                @elseif($order->status == 'verified')
                                    <div class="text-center py-4">
                                        <div class="avatar-lg mx-auto mb-3">
                                            <div class="avatar-title bg-soft-success text-success rounded-circle fs-1">
                                                <i class="ri-checkbox-circle-line"></i>
                                            </div>
                                        </div>
                                        <h5>Approval & Verifikasi Selesai!</h5>
                                        <div class="row mt-4 mb-4">
                                            <div class="col-md-6 text-center border-end">
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
                                            <div class="col-md-6 text-center">
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
                    timer: 5000,
                    showConfirmButton: true
                });
            @endif

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false
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

            @if ($order->status == 'submitted')
                const checkerPad = initSignature('checker-signature-pad', 'checker-signature-data',
                    'clear-checker-sig');
                $('#checker-approval-form').on('submit', function(e) {
                    if (checkerPad.isEmpty()) {
                        e.preventDefault();
                        Swal.fire('Error', 'Signature is required.', 'error');
                    } else {
                        document.getElementById('checker-signature-data').value = checkerPad.toDataURL();
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
                        document.getElementById('driver-signature-data').value = driverPad.toDataURL();
                    }
                });
            @endif
        });
    </script>
@endsection
