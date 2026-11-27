@extends('layouts.app')

@section('sidebar-size', 'sm')

@section('title', '| Detail & Approval Bongkar WRM')

@section('content')
    @php
        $insp = is_array($order->inspection_data) ? $order->inspection_data : [];
    @endphp
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Detail & Approval Bongkar WRM</h4>
                        <div class="page-title-right d-flex gap-2">
                            <a href="{{ route('wrm.bongkar_muat.index') }}" class="btn btn-soft-secondary btn-sm">
                                <i class="ri-arrow-left-line me-1"></i> Kembali ke Daftar
                            </a>
                            <a href="{{ route('wrm.bongkar_muat.download', $order->id) }}" target="_blank" class="btn btn-soft-danger btn-sm">
                                <i class="ri-file-pdf-line me-1"></i> Cetak PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5>
                                    <span class="badge bg-warning text-dark me-1">BONGKAR WRM</span>
                                    {{ $order->no_dokumen ?? 'Draft #' . $order->id }}
                                </h5>
                                <p class="text-muted mb-0">
                                    @if ($order->no_spkb)
                                        <span class="badge bg-soft-warning text-warning me-1">SPKB: {{ $order->no_spkb }}</span>
                                    @endif
                                    @if ($order->no_ecp)
                                        <span class="badge bg-soft-info text-info me-1">ECP: {{ $order->no_ecp }}</span>
                                    @endif
                                    @if ($order->supplier_vendor)
                                        <span class="badge bg-soft-primary text-primary">Vendor: {{ $order->supplier_vendor }}</span>
                                    @endif
                                </p>
                            </div>

                            <div class="row mb-4 bg-light p-3 rounded mx-2">
                                <div class="col-md-6 border-end">
                                    <p class="mb-2 text-muted small text-uppercase fw-bold">Informasi Dokumen & Pengiriman</p>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>No. SPKB / SPB:</span> <span class="fw-bold">{{ $order->no_spkb ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>No. ECP:</span> <span class="fw-bold text-primary">{{ $order->no_ecp ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Sloc:</span> <span class="fw-bold">{{ $order->sloc ?? 'B006' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Supplier / Vendor:</span> <span class="fw-bold">{{ $order->supplier_vendor ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>No. Segel:</span> <span class="fw-bold">{{ $order->no_segel_bas ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Jumlah TKBM:</span> <span class="fw-bold text-primary">{{ $order->jumlah_tkbm ? $order->jumlah_tkbm . ' Orang' : '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 ps-md-4">
                                    <p class="mb-2 text-muted small text-uppercase fw-bold">Kendaraan & Operasional</p>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>No. Polisi (Jenis):</span> <span class="fw-bold">{{ ($order->no_mobil ?? '-') . ' (' . ($order->jenis_pengiriman ?? 'Lokal') . ')' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Gate / Dock:</span> <span class="fw-bold">{{ $order->gate ? 'Gate ' . $order->gate : '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Forklift Driver:</span> <span class="fw-bold">{{ $order->forkliftDriver->username ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Mulai / Selesai Bongkar:</span> <span class="fw-bold">{{ ($order->jam_muat ?? '-') . ' / ' . ($order->jam_selesai ?? '-') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Jam Serah Terima Dokumen ke QC:</span> <span class="fw-bold text-primary">{{ $order->jam_serah_terima_qc ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Jam Release / Unrelease QC:</span> <span class="fw-bold text-success">{{ $order->jam_release_qc ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>COA / Kartu Checkpoint:</span> <span class="fw-bold">{{ ($insp['doc_coa'] ?? 'Ada') . ' / ' . ($insp['doc_kartu_checkpoint'] ?? 'Ada') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Rincian Item WRM --}}
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 50px;">No</th>
                                            <th>MID</th>
                                            <th>Nama Barang</th>
                                            <th class="text-center" style="width: 70px;">Jenis</th>
                                            <th class="text-center">UoM</th>
                                            <th class="text-end">Jumlah</th>
                                            <th>Batch / Lot</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalQty = 0; @endphp
                                        @forelse ($order->details as $idx => $d)
                                            @php $totalQty += $d->qty; @endphp
                                            <tr>
                                                <td class="text-center">{{ $idx + 1 }}</td>
                                                <td class="fw-bold text-center">{{ $d->mid ?: ($d->material->mid ?? '-') }}</td>
                                                <td>{{ $d->nama_barang ?: ($d->material->nama_barang ?? '-') }}</td>
                                                <td class="text-center fw-bold">
                                                    <span class="badge {{ ($d->jenis ?? 'P') === 'P' ? 'bg-soft-primary text-primary' : 'bg-soft-warning text-warning' }}">{{ $d->jenis ?? 'P' }}</span>
                                                </td>
                                                <td class="text-center">{{ $d->uom }}</td>
                                                <td class="text-end fw-bold">{{ number_format($d->qty, 0, ',', '.') }}</td>
                                                <td class="text-center">{{ $d->batch_number ?? '-' }}</td>
                                                <td>{{ $d->keterangan ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-3 text-muted">Tidak ada rincian item</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="5" class="text-end">Total (Jumlah Keseluruhan):</td>
                                            <td class="text-end">{{ number_format($totalQty, 0, ',', '.') }}</td>
                                            <td colspan="2">{{ $order->details->count() }} Baris / Pallet</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Card Status Approval TTD --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <div class="card border text-center p-3 h-100 {{ $order->checker_signature ? 'border-success bg-soft-success' : 'border-warning bg-soft-warning' }}">
                                        <h6 class="fw-bold mb-1">Pengawas (Checker)</h6>
                                        <small class="text-muted d-block mb-2">{{ $order->checker->username ?? '-' }}</small>
                                        @if ($order->checker_signature)
                                            <img src="{{ asset($order->checker_signature) }}" style="max-height: 50px;" class="mx-auto my-1">
                                            <span class="badge bg-success mt-2">Approved</span>
                                        @else
                                            <div class="py-2 text-warning"><i class="ri-time-line fs-24"></i><div class="small">Menunggu TTD</div></div>
                                            @if ($order->status === 'submitted')
                                                <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalTtdChecker">
                                                    TTD Pengawas
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card border text-center p-3 h-100 {{ $order->qc_signature ? 'border-success bg-soft-success' : 'border-info bg-soft-info' }}">
                                        <h6 class="fw-bold mb-1">Quality Control (QC)</h6>
                                        <small class="text-muted d-block mb-2">{{ $order->qcUser->username ?? '-' }}</small>
                                        @if ($order->qc_signature)
                                            <img src="{{ asset($order->qc_signature) }}" style="max-height: 50px;" class="mx-auto my-1">
                                            <span class="badge bg-success mt-2">QC Approved</span>
                                        @else
                                            <div class="py-2 text-info"><i class="ri-shield-check-line fs-24"></i><div class="small">Menunggu QC</div></div>
                                            <button type="button" class="btn btn-sm btn-info text-white mt-2" data-bs-toggle="modal" data-bs-target="#modalTtdQc">
                                                TTD Quality Control
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card border text-center p-3 h-100 {{ $order->driver_signature ? 'border-success bg-soft-success' : 'border-secondary bg-soft-secondary' }}">
                                        <h6 class="fw-bold mb-1">Sopir (Driver)</h6>
                                        <small class="text-muted d-block mb-2">{{ $order->driver_name ?? '-' }}</small>
                                        @if ($order->driver_signature)
                                            <img src="{{ asset($order->driver_signature) }}" style="max-height: 50px;" class="mx-auto my-1">
                                            <span class="badge bg-success mt-2">Approved</span>
                                        @else
                                            <div class="py-2 text-muted"><i class="ri-steering-line fs-24"></i><div class="small">Menunggu TTD</div></div>
                                            @if ($order->status === 'approved' || $order->checker_signature)
                                                <button type="button" class="btn btn-sm btn-warning text-dark mt-2" data-bs-toggle="modal" data-bs-target="#modalTtdDriver">
                                                    TTD Sopir
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card border text-center p-3 h-100 {{ $order->verified_signature ? 'border-success bg-soft-success' : 'border-primary bg-soft-primary' }}">
                                        <h6 class="fw-bold mb-1">Administrasi / Verifikasi</h6>
                                        <small class="text-muted d-block mb-2">{{ $order->verificator->username ?? '-' }}</small>
                                        @if ($order->verified_signature)
                                            <img src="{{ asset($order->verified_signature) }}" style="max-height: 50px;" class="mx-auto my-1">
                                            <span class="badge bg-success mt-2">Verified</span>
                                        @else
                                            <div class="py-2 text-primary"><i class="ri-check-double-line fs-24"></i><div class="small">Menunggu Verifikasi</div></div>
                                            @if ($order->status === 'finished')
                                                <button type="button" class="btn btn-sm btn-success mt-2" data-bs-toggle="modal" data-bs-target="#modalTtdVerificator">
                                                    Verifikasi Akhir
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if ($order->status == 'verified')
                                <div class="alert alert-success text-center">
                                    <i class="ri-checkbox-circle-fill me-2 fs-18 align-middle"></i>
                                    Form Bongkar WRM telah <b>SELESAI DIVERIFIKASI</b> secara penuh.
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal TTD Checker --}}
    <div class="modal fade" id="modalTtdChecker" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tanda Tangan Pengawas (Checker)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('wrm.bongkar_muat.approve_checker', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tanda Tangan:</label>
                            <div class="border rounded bg-white shadow-sm position-relative" style="height: 200px;">
                                <canvas id="pad-checker" style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
                                <button type="button" class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" id="clear-checker">Clear</button>
                            </div>
                            <input type="hidden" name="signature" id="sig-data-checker" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-submit-checker">Simpan Tanda Tangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal TTD QC --}}
    <div class="modal fade" id="modalTtdQc" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tanda Tangan Quality Control (QC)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('wrm.bongkar_muat.approve_qc', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tanda Tangan QC:</label>
                            <div class="border rounded bg-white shadow-sm position-relative" style="height: 200px;">
                                <canvas id="pad-qc" style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
                                <button type="button" class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" id="clear-qc">Clear</button>
                            </div>
                            <input type="hidden" name="signature" id="sig-data-qc" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info text-white" id="btn-submit-qc">Simpan Tanda Tangan QC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal TTD Driver --}}
    <div class="modal fade" id="modalTtdDriver" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tanda Tangan Sopir (Driver)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('wrm.bongkar_muat.approve_driver', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Sopir <span class="text-danger">*</span></label>
                            <input type="text" name="driver_name" class="form-control" value="{{ $order->driver_name ?? '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanda Tangan:</label>
                            <div class="border rounded bg-white shadow-sm position-relative" style="height: 200px;">
                                <canvas id="pad-driver" style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
                                <button type="button" class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" id="clear-driver">Clear</button>
                            </div>
                            <input type="hidden" name="signature" id="sig-data-driver" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-dark" id="btn-submit-driver">Simpan TTD Sopir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Verifikator --}}
    <div class="modal fade" id="modalTtdVerificator" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Verifikasi Akhir Administrasi (WRM)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('wrm.bongkar_muat.validate', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Catatan Verifikator (Opsional):</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Catatan..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanda Tangan Verifikator:</label>
                            <div class="border rounded bg-white shadow-sm position-relative" style="height: 200px;">
                                <canvas id="pad-verificator" style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
                                <button type="button" class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" id="clear-verificator">Clear</button>
                            </div>
                            <input type="hidden" name="signature" id="sig-data-verificator" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success" id="btn-submit-verificator">Verifikasi Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            function setupPad(canvasId, clearBtnId, hiddenInputId, submitBtnId) {
                let canvas = document.getElementById(canvasId);
                if (!canvas) return null;

                function resize() {
                    let ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                }

                let pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });

                $('#' + clearBtnId).on('click', function() { pad.clear(); });

                let modal = $(canvas).closest('.modal');
                modal.on('shown.bs.modal', function() {
                    resize();
                    pad.clear();
                });

                $(submitBtnId).closest('form').on('submit', function(e) {
                    if (pad.isEmpty()) {
                        e.preventDefault();
                        Swal.fire({ icon: 'warning', title: 'Tanda tangan kosong!' });
                        return false;
                    }
                    $('#' + hiddenInputId).val(pad.toDataURL());
                });

                return pad;
            }

            setupPad('pad-checker', 'clear-checker', 'sig-data-checker', '#btn-submit-checker');
            setupPad('pad-qc', 'clear-qc', 'sig-data-qc', '#btn-submit-qc');
            setupPad('pad-driver', 'clear-driver', 'sig-data-driver', '#btn-submit-driver');
            setupPad('pad-verificator', 'clear-verificator', 'sig-data-verificator', '#btn-submit-verificator');
        });
    </script>
@endsection
