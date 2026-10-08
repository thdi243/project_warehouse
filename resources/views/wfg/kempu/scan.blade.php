@extends('layouts.app')

@section('title', '| Scanner Kempu - ' . $card['title'])

@section('styles')
    <style>
        #kempuReader {
            width: 100%;
            min-height: 360px;
            background-color: #0b0f19;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        #kempuReader video {
            width: 100% !important;
            height: auto !important;
            border-radius: 12px;
            object-fit: cover;
        }

        .scan-target-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: min(88%, 380px);
            height: min(72%, 280px);
            border: 2.5px dashed rgba(255, 255, 255, 0.85);
            border-radius: 16px;
            pointer-events: none;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45);
            transition: all 0.25s ease;
            z-index: 5;
        }

        .scan-target-frame::after {
            content: '';
            position: absolute;
            top: 0;
            left: 6%;
            right: 6%;
            height: 2.5px;
            background: linear-gradient(90deg, transparent, #22c55e, #10b981, transparent);
            box-shadow: 0 0 10px #22c55e;
            border-radius: 2px;
            animation: laserScan 2.4s ease-in-out infinite alternate;
        }

        @keyframes laserScan {
            0% {
                top: 8%;
                opacity: 0.3;
            }

            50% {
                opacity: 1;
            }

            100% {
                top: 92%;
                opacity: 0.3;
            }
        }

        .scan-target-frame.scanned {
            border-color: #22c55e !important;
            border-style: solid !important;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45), 0 0 16px rgba(34, 197, 94, 0.6) !important;
        }

        .scan-target-frame.scanned::after {
            display: none !important;
        }

        @media (max-width: 991.98px) {
            #kempuReader {
                min-height: 380px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Breadcrumb Header -->
            <div class="row mb-2">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Scanner Kempu - {{ $card['title'] }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="#">WFG</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('wfg.kempu.index') }}">Kempu</a></li>
                                <li class="breadcrumb-item active">{{ $card['title'] }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Action Bar -->
            <div class="card shadow-sm mb-4">
                <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm flex-shrink-0">
                            <span
                                class="avatar-title rounded-circle fs-20 bg-soft-{{ $card['badge_color'] }} text-{{ $card['badge_color'] }}">
                                <i class="{{ $card['icon'] }}"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-body">{{ $card['title'] }}</h5>
                            <p class="mb-0 text-muted fs-12">
                                Status yang akan diterapkan:
                                <span class="badge bg-{{ $card['badge_color'] }} px-2 py-1 fs-12">
                                    {{ $card['status_name'] }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('wfg.kempu.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Kembali ke Menu Kempu
                        </a>
                    </div>
                </div>
            </div>

            <!-- Centered Scanner Card -->
            <div class="row justify-content-center">
                <div class="col-xl-7 col-lg-9 col-md-11">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="fw-bold text-body fs-14">
                                        <i class="ri-camera-lens-line text-primary me-1"></i> Pemindai Kamera WFG
                                    </span>
                                    <div class="text-muted fs-12">Arahkan kamera tepat ke kode barcode/QR Kempu</div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSwitchCamera"
                                        title="Ganti Kamera Depan/Belakang">
                                        <i class="ri-camera-switch-line me-1"></i> Ganti Kamera
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnRestartCamera"
                                        title="Reset Scanner">
                                        <i class="ri-refresh-line me-1"></i> Reset
                                    </button>
                                </div>
                            </div>

                            <!-- Viewport Scanner -->
                            <div id="kempuReader">
                                <div id="cameraPlaceholder" class="text-center p-4 text-white-50">
                                    <div class="spinner-border spinner-border-sm text-light mb-2" role="status"></div>
                                    <div class="fs-13">Mengaktifkan kamera pemindai...</div>
                                </div>
                                <div id="scanTargetFrame" class="scan-target-frame d-none"></div>
                            </div>

                            <!-- Manual Input Box -->
                            <div class="mt-4 pt-2 border-top">
                                @php
                                    $canManualInput =
                                        $canManualInput ?? \App\Models\Kempu\MasterKempuModel::canManualInput();
                                @endphp
                                @if ($canManualInput)
                                    <label
                                        class="form-label fs-13 fw-semibold text-body mb-2 d-flex align-items-center justify-content-between">
                                        <span><i class="ri-keyboard-line text-muted me-1"></i> Masukkan ID Kempu
                                            Manual:</span>
                                        <span class="badge bg-info-subtle text-info fs-11"><i
                                                class="ri-shield-user-line me-1"></i> Otoritas Khusus Aktif</span>
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light text-muted"><i
                                                class="ri-barcode-line"></i></span>
                                        <input type="text" id="inputManualId" class="form-control font-monospace"
                                            placeholder="Contoh: KMP-001 lalu tekan Enter..." autocomplete="off">
                                        <button class="btn btn-primary px-4 fw-semibold" type="button"
                                            id="btnLookupManual">
                                            <i class="ri-search-line me-1"></i> Cari Kempu
                                        </button>
                                    </div>
                                @else
                                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 py-2 px-3">
                                        <i class="ri-lock-line fs-20 text-warning flex-shrink-0"></i>
                                        <div class="fs-12 text-muted">
                                            <strong class="text-body">Pengetikan Manual Terkunci:</strong> Operator wajib
                                            memindai kempu via kamera / barcode scanner. Pengetikan ID manual hanya
                                            diperuntukkan bagi Foreman / Leader / Supervisor.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>



        </div>
    </div>

    <!-- MODAL INFORMASI DASAR KEMPU & KONFIRMASI -->
    <div class="modal fade" id="modalKempuConfirm" tabindex="-1" aria-labelledby="modalKempuConfirmLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Modal Header -->
                <div class="modal-header bg-light border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-xs flex-shrink-0">
                            <span class="avatar-title rounded-circle bg-teal-subtle fs-16"
                                style="background-color: #ccfbf1; color: #0f766e;">
                                <i class="ri-qr-scan-2-line"></i>
                            </span>
                        </div>
                        <h5 class="modal-title fw-bold text-body fs-16" id="modalKempuConfirmLabel">
                            Konfirmasi Kempu - {{ $card['title'] }}
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body: Informasi Dasar Kempu -->
                <div class="modal-body p-4">
                    <!-- Barcode Title Banner -->
                    <div
                        class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-3 bg-light border border-dashed">
                        <div>
                            <div class="text-muted fs-11 text-uppercase fw-semibold">ID / Barcode Kempu</div>
                            <div class="fs-20 fw-bold font-monospace text-primary" id="modalKempuId">-</div>
                        </div>
                        <div class="text-end">
                            <div id="modalKempuTypeBadge"></div>
                        </div>
                    </div>

                    <!-- Detail Grid -->
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <!-- Baris 1: Data Ringkas (RFID, PO, Reused) -->
                        <div class="row g-2 fs-13 text-center mb-2 pb-2 border-bottom">
                            <div class="col-4">
                                <span class="text-muted d-block fs-11">RFID:</span>
                                <span class="fw-semibold font-monospace text-body" id="modalKempuRfid">-</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block fs-11">Nomor PO:</span>
                                <span class="fw-bold font-monospace text-success" id="modalKempuNoPo">-</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block fs-11">Siklus Reused:</span>
                                <div id="reusedDisplayMode">
                                    <span class="fw-bold text-body" id="modalKempuReused">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 2: Status Saat Ini (Full-Width) -->
                        <div class="text-center">
                            <span class="text-muted d-block fs-11 mb-1">Status Saat Ini:</span>
                            <span
                                class="badge bg-white text-secondary border px-3 py-2 text-wrap text-break font-monospace fs-12"
                                id="modalKempuStatus">-</span>
                        </div>
                    </div>

                    <!-- Alert Auto Increment Reused -->
                    <div id="alertAutoIncrementBox" style="display: none;"></div>

                    <!-- Alert Status Reused / Peringatan Otoritas -->
                    <div id="alertReusedBox" style="display: none;"></div>

                    <!-- Alert Validasi Alur Status -->
                    <div id="alertFlowErrorBox" style="display: none;"></div>

                    <!-- Input Reused Fisik jika belum ada data reused dan user punya otoritas -->
                    <div id="boxInputReused" class="p-3 rounded-3 mb-3 border border-warning bg-warning-subtle"
                        style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="modalInputReused" class="form-label fw-bold text-body fs-12 mb-0">
                                <i class="ri-edit-circle-line text-warning me-1 fs-14 align-middle"></i>
                                Tentukan Siklus Reused Fisik <span class="text-danger">*</span>
                            </label>
                            <span class="badge bg-warning text-dark" style="font-size: 10px;">Wajib Diisi</span>
                        </div>
                        <div class="input-group input-group-sm mb-1">
                            <span class="input-group-text"><i class="ri-repeat-line"></i></span>
                            <input type="number" class="form-control font-monospace fw-bold" id="modalInputReused"
                                min="1" max="21" placeholder="Masukkan siklus pemakaian (1 - 21)">
                            <span class="input-group-text fw-bold">/ 21x</span>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Kempu ini belum memiliki catatan Reused.
                            Masukkan siklus fisik saat ini lalu klik Simpan Nilai Reused.</small>
                    </div>

                    <!-- Target Status Baru Box -->
                    <div id="targetStatusBox"
                        class="alert alert-info border-info-subtle align-items-center gap-3 mb-3 py-2 px-3 d-none">
                        <i class="ri-arrow-right-circle-line fs-24 text-info flex-shrink-0"></i>
                        <div>
                            <div class="fs-11 text-muted text-uppercase fw-semibold">Status Baru yang Akan Disimpan:</div>
                            <div class="fs-15 fw-bold text-info">{{ $card['status_name'] }}</div>
                        </div>
                    </div>

                    <!-- Checklist Kelengkapan Fisik (Barcode, RFID, NTI) -->
                    <div id="physicalChecklistCard" class="p-3 rounded-3 mb-3 border bg-light">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fs-12 fw-bold text-body mb-0">
                                <i class="ri-checkbox-multiple-line text-primary me-1 fs-14 align-middle"></i>
                                Pemeriksaan Fisik Kempu:
                            </label>
                            <span class="text-muted" style="font-size: 11px;">Centang jika fisik ada</span>
                        </div>
                        <div class="row g-2">
                            <!-- Check Barcode -->
                            <div class="col-4">
                                <div class="card h-100 mb-0 border shadow-none p-2 text-center physical-card bg-white"
                                    style="cursor: pointer;"
                                    onclick="$('#checkBarcode').prop('checked', !$('#checkBarcode').prop('checked')).trigger('change')">
                                    <div class="form-check form-switch d-flex justify-content-center mb-1">
                                        <input class="form-check-input physical-check" type="checkbox" id="checkBarcode"
                                            checked onclick="event.stopPropagation()">
                                    </div>
                                    <div class="fw-bold fs-12 text-body"><i class="ri-barcode-line me-1"></i>Barcode</div>
                                    <span class="badge bg-success-subtle text-success fs-10 mt-1 physical-badge"
                                        id="badgeCheckBarcode">Ada</span>
                                </div>
                            </div>
                            <!-- Check RFID -->
                            <div class="col-4">
                                <div class="card h-100 mb-0 border shadow-none p-2 text-center physical-card bg-white"
                                    style="cursor: pointer;"
                                    onclick="$('#checkRfid').prop('checked', !$('#checkRfid').prop('checked')).trigger('change')">
                                    <div class="form-check form-switch d-flex justify-content-center mb-1">
                                        <input class="form-check-input physical-check" type="checkbox" id="checkRfid"
                                            checked onclick="event.stopPropagation()">
                                    </div>
                                    <div class="fw-bold fs-12 text-body"><i class="ri-rfid-line me-1"></i>RFID</div>
                                    <span class="badge bg-success-subtle text-success fs-10 mt-1 physical-badge"
                                        id="badgeCheckRfid">Ada</span>
                                </div>
                            </div>
                            <!-- Check NT -->
                            <div class="col-4">
                                <div class="card h-100 mb-0 border shadow-none p-2 text-center physical-card bg-white"
                                    style="cursor: pointer;"
                                    onclick="$('#checkNti').prop('checked', !$('#checkNti').prop('checked')).trigger('change')">
                                    <div class="form-check form-switch d-flex justify-content-center mb-1">
                                        <input class="form-check-input physical-check" type="checkbox" id="checkNti"
                                            checked onclick="event.stopPropagation()">
                                    </div>
                                    <div class="fw-bold fs-12 text-body"><i class="ri-price-tag-3-line me-1"></i>NTI
                                    </div>
                                    <span class="badge bg-success-subtle text-success fs-10 mt-1 physical-badge"
                                        id="badgeCheckNti">Ada</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Tambahan (Opsional) -->
                    <div id="notesBox" class="mb-2">
                        <label class="form-label fs-12 text-muted fw-medium mb-1">Catatan Tambahan (Opsional):</label>
                        <textarea id="modalInputNotes" class="form-control form-control-sm" rows="2"
                            placeholder="Contoh: No Surat Jalan, nomor lot, dll..."></textarea>
                    </div>
                </div>

                <!-- Modal Footer: Action Buttons -->
                <div class="modal-footer bg-light border-top d-flex align-items-center justify-content-between p-3">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Batal / Scan Ulang
                    </button>
                    <button type="button" class="btn fw-bold px-4 shadow-sm" id="btnModalConfirm"
                        style="background-color: {{ $card['btn_color'] }}; color: #ffffff;">
                        <i class="ri-check-double-line me-1"></i> Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- html5-qrcode Library CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        $(document).ready(function() {
            const CARD_KEY = "{{ $card['key'] }}";
            const CARD_TITLE = "{{ $card['title'] }}";
            const CARD_STATUS_NAME = "{{ $card['status_name'] }}";

            let qrScanner = null;
            let currentFacing = "environment";
            let availableCameras = [];
            let currentCameraIndex = 0;
            let isScannerActive = false;
            let currentKempu = null;
            const confirmModal = new bootstrap.Modal(document.getElementById('modalKempuConfirm'));

            // Web Audio API Beep Sound Synth
            function playBeep(type = 'success') {
                try {
                    const ctx = new(window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    if (type === 'success') {
                        osc.frequency.setValueAtTime(850, ctx.currentTime);
                        gain.gain.setValueAtTime(0.25, ctx.currentTime);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.15);
                    } else if (type === 'confirm') {
                        osc.frequency.setValueAtTime(600, ctx.currentTime);
                        osc.frequency.exponentialRampToValueAtTime(1000, ctx.currentTime + 0.2);
                        gain.gain.setValueAtTime(0.3, ctx.currentTime);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.25);
                    } else {
                        osc.frequency.setValueAtTime(280, ctx.currentTime);
                        gain.gain.setValueAtTime(0.3, ctx.currentTime);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.3);
                    }
                    if (navigator.vibrate) navigator.vibrate(80);
                } catch (e) {
                    console.log('Audio feedback fallback');
                }
            }

            // Update Tampilan Badge Checklist Fisik
            function updatePhysicalBadges() {
                if ($('#checkBarcode').is(':checked')) {
                    $('#badgeCheckBarcode').removeClass('bg-danger-subtle text-danger').addClass(
                        'bg-success-subtle text-success').text('Ada');
                } else {
                    $('#badgeCheckBarcode').removeClass('bg-success-subtle text-success').addClass(
                        'bg-danger-subtle text-danger').text('Tidak Ada');
                }

                if ($('#checkRfid').is(':checked')) {
                    $('#badgeCheckRfid').removeClass('bg-danger-subtle text-danger').addClass(
                        'bg-success-subtle text-success').text('Ada');
                } else {
                    $('#badgeCheckRfid').removeClass('bg-success-subtle text-success').addClass(
                        'bg-danger-subtle text-danger').text('Tidak Ada');
                }

                if ($('#checkNti').is(':checked')) {
                    $('#badgeCheckNti').removeClass('bg-danger-subtle text-danger').addClass(
                        'bg-success-subtle text-success').text('Ada');
                } else {
                    $('#badgeCheckNti').removeClass('bg-success-subtle text-success').addClass(
                        'bg-danger-subtle text-danger').text('Tidak Ada');
                }
            }

            $(document).on('change', '.physical-check', function() {
                updatePhysicalBadges();
            });

            // Inisialisasi Scanner Kamera dengan Auto-Filter Kamera Fisik
            function startScanner() {
                if (qrScanner) {
                    qrScanner.stop().catch(() => {}).finally(() => {
                        initScannerInstance();
                    });
                } else {
                    initScannerInstance();
                }
            }

            function initScannerInstance() {
                if (!qrScanner) {
                    qrScanner = new Html5Qrcode("kempuReader");
                }
                const config = {
                    fps: 15,
                    qrbox: function(viewfinderWidth, viewfinderHeight) {
                        const w = Math.min(Math.floor(viewfinderWidth * 0.88), 380);
                        const h = Math.min(Math.floor(viewfinderHeight * 0.75), 280);
                        return {
                            width: Math.max(w, 220),
                            height: Math.max(h, 180)
                        };
                    },
                    aspectRatio: 1.333333,
                };

                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length > 0) {
                        let realCameras = devices.filter(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return !lbl.includes('obs') && !lbl.includes('virtual') && !lbl
                                .includes('fake');
                        });

                        availableCameras = realCameras.length > 0 ? realCameras : devices;

                        let backCamIdx = availableCameras.findIndex(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return lbl.includes('back') || lbl.includes('rear') || lbl.includes(
                                'environment') || lbl.includes('belakang');
                        });

                        if (backCamIdx !== -1 && currentCameraIndex === 0) {
                            currentCameraIndex = backCamIdx;
                        }

                        if (currentCameraIndex >= availableCameras.length) {
                            currentCameraIndex = 0;
                        }

                        const targetCameraId = availableCameras[currentCameraIndex].id;
                        return qrScanner.start(
                            targetCameraId,
                            config,
                            onScanSuccess,
                            function(err) {}
                        );
                    } else {
                        return qrScanner.start({
                                facingMode: currentFacing
                            },
                            config,
                            onScanSuccess,
                            function(err) {}
                        );
                    }
                }).catch(err => {
                    return qrScanner.start({
                            facingMode: currentFacing
                        },
                        config,
                        onScanSuccess,
                        function(err) {}
                    );
                }).then(() => {
                    isScannerActive = true;
                    $('#cameraPlaceholder').addClass('d-none');
                    $('#scanTargetFrame').removeClass('d-none').removeClass('scanned');
                }).catch(err => {
                    console.warn("Kamera tidak dapat diakses:", err);
                    $('#cameraPlaceholder').html(`
                        <i class="ri-camera-off-line fs-32 text-warning mb-2 d-block"></i>
                        <div class="fs-13 text-light">Akses kamera tidak aktif atau diblokir.</div>
                        <div class="fs-12 text-muted mt-1">Anda dapat menggunakan input manual barcode di bawah.</div>
                        <button class="btn btn-xs btn-primary mt-2" onclick="location.reload()">Coba Lagi</button>
                    `);
                });
            }

            // Callback Scan Kamera Berhasil
            function onScanSuccess(decodedText, decodedResult) {
                if (!decodedText) return;

                // Cegah duplicate scan beruntun untuk kempu yang sama saat modal aktif
                if (currentKempu && currentKempu.id_kempu === decodedText.trim().toUpperCase()) {
                    return;
                }

                // playBeep('success');
                $('#scanTargetFrame').addClass('scanned');

                // Pause camera scan during modal inspection
                if (qrScanner && isScannerActive) {
                    qrScanner.pause();
                }

                lookupKempu(decodedText.trim().toUpperCase());
            }

            // Lookup Kempu via AJAX
            function lookupKempu(idKempu, isManual = false) {
                const loadingToast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                loadingToast.fire({
                    icon: 'info',
                    title: 'Memeriksa barcode ' + idKempu + '...'
                });

                $.ajax({
                    url: "{{ route('wfg.kempu.lookup') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_kempu: idKempu,
                        card_key: CARD_KEY,
                        is_manual: isManual ? 1 : 0
                    },
                    success: function(res) {
                        if (res.status && res.data) {
                            currentKempu = res.data;
                            currentKempu.is_manual = isManual;
                            if (res.data.is_flow_valid === false) {
                                // playBeep('error');
                            }
                            openConfirmModal(res.data);
                        } else {
                            handleLookupNotFound(res.message || 'Data kempu tidak ditemukan.');
                        }
                    },
                    error: function(xhr) {
                        // playBeep('error');
                        let msg =
                            'Kempu dengan barcode tersebut tidak ditemukan dalam database Master Kempu.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        handleLookupNotFound(msg);
                    }
                });
            }

            // Buka Modal Konfirmasi Kempu
            function openConfirmModal(k) {
                $('#modalKempuId').text(k.id_kempu);
                $('#modalKempuRfid').text(k.rfid || '-');
                $('#modalKempuNoPo').text(k.no_po || '-');
                $('#modalKempuLoc').text(k.current_location);
                $('#modalKempuStatus').text(k.current_status);
                $('#modalKempuCondition').text(k.condition);
                $('#modalInputNotes').val('');

                // Tampilkan Badge Tipe Kempu (Kempu Baru YYMMDD vs Kempu Lama)
                if (k.is_new_kempu) {
                    $('#modalKempuTypeBadge').html(
                        '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-11"><i class="ri-sparkling-line me-1"></i> Kempu Baru (YYMMDD)</span>'
                    );
                } else {
                    $('#modalKempuTypeBadge').html(
                        '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-11"><i class="ri-history-line me-1"></i> Kempu Lama</span>'
                    );
                }

                // Reset Alert & Input Reused Box & Mode Edit Inline
                $('#reusedEditMode').addClass('d-none');
                $('#reusedDisplayMode').removeClass('d-none');
                $('#alertAutoIncrementBox').hide().empty();
                $('#alertReusedBox').hide().empty();
                $('#alertFlowErrorBox').hide().empty();
                $('#boxInputReused').hide();
                $('#targetStatusBox').removeClass('d-flex').addClass('d-none');
                $('#physicalChecklistCard').hide();
                $('#notesBox').hide();
                $('#modalInputReused').val('');

                // Tampilan Siklus Reused (dengan visual Auto +1 jika berlaku DAN alur valid)
                if (k.is_flow_valid !== false && k.will_increment_reused) {
                    $('#modalKempuReused').html(`
                        <span class="text-muted text-decoration-line-through me-1 fs-12">${k.reused_count}x</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-12">
                            <i class="ri-arrow-up-line me-1"></i>${k.target_reused_count} / ${k.max_reused} <span class="fw-normal">(Auto +1)</span>
                        </span>
                    `);

                    $('#alertAutoIncrementBox').html(`
                        <div class="alert alert-success border border-success-subtle d-flex align-items-center gap-2 mb-3 py-2 px-3">
                            <i class="ri-add-circle-fill fs-18 text-success flex-shrink-0"></i>
                            <div class="fs-12 text-success">
                                <strong>Siklus Reused Baru (1x):</strong> Kempu baru tiba membawa muatan Finished Goods, siklus pemakaian diinisialisasi menjadi <strong>${k.target_reused_count}x</strong> saat dikonfirmasi.
                            </div>
                        </div>
                    `).show();
                } else {
                    $('#modalKempuReused').text(`${k.reused_count} / ${k.max_reused}`);
                    $('#alertAutoIncrementBox').hide().empty();
                }

                // Cek Tombol Koreksi Reused Cepat (Hanya jika user berwenang)
                if (k.can_edit_reused) {
                    $('#btnEditReusedQuick').show();
                } else {
                    $('#btnEditReusedQuick').hide();
                }

                // Logika jika kempu BELUM memiliki data reused (hanya berlaku untuk kempu LAMA)
                if (!k.has_reused) {
                    $('#targetStatusBox').removeClass('d-flex').addClass('d-none');
                    $('#physicalChecklistCard').hide();
                    $('#notesBox').hide();

                    if (!k.can_edit_reused) {
                        // User TIDAK berwenang -> Kunci proses & disable tombol konfirmasi
                        $('#alertReusedBox').html(`
                            <div class="alert alert-danger d-flex align-items-start gap-2 mb-3 py-2 px-3">
                                <i class="ri-error-warning-fill fs-20 text-danger flex-shrink-0 mt-1"></i>
                                <div>
                                    <div class="fw-bold fs-13 text-danger">Akses Ditolak: Kempu Lama Belum Memiliki Siklus Reused</div>
                                    <div class="fs-12 text-muted">Kempu tipe lama (${k.id_kempu}) belum memiliki data siklus pemakaian fisik (Reused masih 0x). Akun Anda tidak memiliki hak otorisasi untuk menginput nilai Reused. Silakan hubungi <strong>Foreman / Supervisor</strong> untuk menginput Reused kempu terlebih dahulu.</div>
                                </div>
                            </div>
                        `).show();
                        $('#btnModalConfirm').prop('disabled', true).html(
                            `<i class="ri-lock-fill me-1"></i> Butuh Otoritas Reused`);
                    } else {
                        // User MEMILIKI otoritas -> Tampilkan input Reused
                        $('#alertReusedBox').html(`
                            <div class="alert alert-warning d-flex align-items-start gap-2 mb-3 py-2 px-3">
                                <i class="ri-alert-fill fs-20 text-warning flex-shrink-0 mt-1"></i>
                                <div>
                                    <div class="fw-bold fs-13 text-dark">Registrasi Siklus Reused Diperlukan (Kempu Lama)</div>
                                    <div class="fs-12 text-muted">Kempu tipe lama (${k.id_kempu}) belum memiliki data siklus Reused (masih 0x). Silakan tentukan siklus pemakaian fisik kempu (1 - 21x) di bawah ini lalu <strong>Simpan Nilai Reused</strong>.</div>
                                </div>
                            </div>
                        `).show();
                        $('#boxInputReused').show();
                        $('#btnModalConfirm').prop('disabled', false).css({
                            'background-color': "{{ $card['btn_color'] }}",
                            'border-color': "{{ $card['btn_color'] }}",
                            'color': '#ffffff'
                        }).html(`<i class="ri-save-line me-1"></i> Simpan Nilai Reused`);
                        setTimeout(() => $('#modalInputReused').focus(), 400);
                    }
                } else {
                    $('#targetStatusBox').removeClass('d-none').addClass('d-flex');
                    $('#physicalChecklistCard').show();
                    $('#notesBox').show();

                    // Set status checklist kelengkapan fisik
                    $('#checkBarcode').prop('checked', k.has_barcode !== false);
                    $('#checkRfid').prop('checked', k.has_rfid !== false);
                    $('#checkNti').prop('checked', k.has_nti !== false);
                    updatePhysicalBadges();

                    // Cek Validasi Alur Status (Urutan & Duplikat Scan)
                    if (k.is_flow_valid === false) {
                        $('#alertAutoIncrementBox').hide().empty();
                        $('#modalKempuReused').text(`${k.reused_count} / ${k.max_reused}`);
                        $('#alertFlowErrorBox').html(`
                            <div class="alert alert-danger d-flex align-items-start gap-2 mb-3 py-2 px-3">
                                <i class="ri-error-warning-fill fs-20 text-danger flex-shrink-0 mt-1"></i>
                                <div>
                                    <div class="fw-bold fs-13 text-danger">Validasi Alur Status Gagal</div>
                                    <div class="fs-12 text-dark">${k.flow_error}</div>
                                </div>
                            </div>
                        `).show();

                        $('#btnModalConfirm')
                            .prop('disabled', true)
                            .css({
                                'background-color': '#6c757d',
                                'border-color': '#6c757d',
                                'color': '#ffffff'
                            })
                            .html(`<i class="ri-forbid-line me-1"></i> Alur Tidak Sesuai`);
                    } else {
                        $('#btnModalConfirm')
                            .prop('disabled', false)
                            .css({
                                'background-color': "{{ $card['btn_color'] }}",
                                'border-color': "{{ $card['btn_color'] }}",
                                'color': '#ffffff'
                            })
                            .html(`<i class="ri-check-double-line me-1"></i> Konfirmasi`);
                    }
                }

                confirmModal.show();
            }

            function handleLookupNotFound(message) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Barcode Tidak Ditemukan',
                    text: message,
                    confirmButtonText: 'OK / Scan Ulang',
                    confirmButtonColor: '#2563eb'
                }).then(() => {
                    resumeScanner();
                });
            }

            function resumeScanner() {
                currentKempu = null;
                $('#scanTargetFrame').removeClass('scanned');
                $('#inputManualId').val('').focus();
                if (qrScanner && isScannerActive) {
                    try {
                        qrScanner.resume();
                    } catch (e) {
                        startScanner();
                    }
                }
            }

            // Saat modal ditutup (cancel / dismiss)
            document.getElementById('modalKempuConfirm').addEventListener('hidden.bs.modal', function() {
                resumeScanner();
            });

            // Input Manual Trigger
            $('#btnLookupManual').on('click', function() {
                const val = $('#inputManualId').val().trim();
                if (!val) {
                    Swal.fire('Perhatian', 'Ketik ID / Barcode kempu terlebih dahulu.', 'info');
                    return;
                }
                if (qrScanner && isScannerActive) {
                    qrScanner.pause();
                }
                lookupKempu(val.toUpperCase(), true);
            });

            $('#inputManualId').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnLookupManual').trigger('click');
                }
            });

            // Ganti Kamera
            $('#btnSwitchCamera').on('click', function() {
                if (availableCameras && availableCameras.length > 1) {
                    currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
                    startScanner();
                } else {
                    currentFacing = (currentFacing === "environment") ? "user" : "environment";
                    startScanner();
                }
            });

            // Restart Kamera
            $('#btnRestartCamera').on('click', function() {
                resumeScanner();
                startScanner();
            });

            // Tombol Confirm Action di Dalam Modal
            $('#btnModalConfirm').on('click', function() {
                if (!currentKempu) return;

                // Jika kempu BELUM memiliki data reused (0x):
                // Tombol ini HANYA menyimpan nilai reused ke database dan mereset scanner, BUKAN konfirmasi status/lokasi!
                if (!currentKempu.has_reused) {
                    if (!currentKempu.can_edit_reused) {
                        Swal.fire('Akses Ditolak',
                            'Akun Anda tidak memiliki hak otorisasi untuk menetapkan nilai reused kempu.',
                            'error');
                        return;
                    }

                    const val = $('#modalInputReused').val();
                    if (!val || parseInt(val) < 1 || parseInt(val) > 21) {
                        Swal.fire('Perhatian', 'Siklus Reused wajib diisi angka antara 1 sampai 21x.',
                            'warning');
                        $('#modalInputReused').focus();
                        return;
                    }

                    const newCount = parseInt(val);
                    const kempuId = currentKempu.id_kempu;
                    const btn = $(this);
                    btn.prop('disabled', true).html(
                        '<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');

                    $.ajax({
                        url: "{{ route('wfg.kempu.update_reused') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id_kempu: kempuId,
                            reused_count: newCount
                        },
                        success: function(res) {
                            btn.prop('disabled', false).html(
                                '<i class="ri-save-line me-1"></i> Simpan Nilai Reused');
                            if (res.status) {
                                confirmModal.hide();
                                resumeScanner();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Siklus Reused Berhasil Disimpan',
                                    html: `Nilai Reused kempu <b>${kempuId}</b> berhasil disimpan (<b>${newCount}/21x</b>).<br><small class="text-muted">Scanner telah direset. Silakan scan ulang kempu jika ingin melanjutkan transaksi.</small>`,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: true,
                                    confirmButtonColor: '#0d9488'
                                });
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html(
                                '<i class="ri-save-line me-1"></i> Simpan Nilai Reused');
                            let msg = 'Terjadi kesalahan saat menyimpan nilai Reused.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Gagal', msg, 'error');
                        }
                    });
                    return; // STOP! Jangan lanjut ke confirm lokasi/status
                }

                // Normal Confirm untuk kempu yang sudah punya data reused (has_reused === true)
                const btn = $(this);
                btn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');

                const payload = {
                    _token: "{{ csrf_token() }}",
                    id_kempu: currentKempu.id_kempu,
                    card_key: CARD_KEY,
                    notes: $('#modalInputNotes').val(),
                    has_barcode: $('#checkBarcode').is(':checked') ? 1 : 0,
                    has_rfid: $('#checkRfid').is(':checked') ? 1 : 0,
                    has_nti: $('#checkNti').is(':checked') ? 1 : 0,
                    is_manual: (currentKempu && currentKempu.is_manual) ? 1 : 0,
                };

                $.ajax({
                    url: "{{ route('wfg.kempu.confirm') }}",
                    method: "POST",
                    data: payload,
                    success: function(res) {
                        // playBeep('confirm');

                        // Tutup modal
                        confirmModal.hide();

                        // Tampilkan SweetAlert sukses
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Berhasil Diperbarui!',
                            html: `Kempu <b>${res.data.id_kempu}</b> berhasil diubah ke status:<br><span class="badge bg-primary fs-14 mt-2 px-3 py-2">${res.data.new_status}</span>`,
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        // playBeep('error');
                        btn.prop('disabled', false).html(
                            `<i class="ri-check-double-line me-1"></i> Konfirmasi`
                        );
                        let msg = 'Terjadi kesalahan saat menyimpan perubahan status.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal', msg, 'error');
                    }
                });
            });

            // Toggle Inline Edit Reused oleh User Berwenang
            $('#btnEditReusedQuick').on('click', function(e) {
                e.preventDefault();
                if (!currentKempu) return;

                $('#inputReusedInline').val(currentKempu.reused_count || 0);
                $('#reusedDisplayMode').addClass('d-none');
                $('#reusedEditMode').removeClass('d-none');
                setTimeout(() => {
                    $('#inputReusedInline').focus().select();
                }, 100);
            });

            // Batal Inline Edit Reused
            $('#btnCancelReusedInline').on('click', function() {
                $('#reusedEditMode').addClass('d-none');
                $('#reusedDisplayMode').removeClass('d-none');
            });

            // Enter key pada input inline reused
            $('#inputReusedInline').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnSaveReusedInline').trigger('click');
                }
            });

            // Simpan Perubahan Reused Inline via AJAX
            $('#btnSaveReusedInline').on('click', function() {
                if (!currentKempu) return;

                const val = $('#inputReusedInline').val();
                if (val === '' || isNaN(val) || parseInt(val) < 0 || parseInt(val) > 21) {
                    Swal.fire('Perhatian', 'Nilai Reused harus berupa angka antara 0 hingga 21x.',
                        'warning');
                    $('#inputReusedInline').focus();
                    return;
                }

                const newCount = parseInt(val);
                const kempuId = currentKempu.id_kempu;
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i>');

                $.ajax({
                    url: "{{ route('wfg.kempu.update_reused') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_kempu: kempuId,
                        reused_count: newCount
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="ri-check-line"></i>');
                        if (res.status) {
                            currentKempu.reused_count = newCount;
                            currentKempu.has_reused = (newCount > 0);

                            // Cek apakah kempu berhak auto increment (Transfer In From Produksi dari Transfer Out to Produksi)
                            const isFromTransferOut = (
                                (currentKempu.current_status || '').toLowerCase() ===
                                'transfer out to produksi' ||
                                (currentKempu.current_status || '').toLowerCase() ===
                                'in_transit_produksi'
                            );

                            if (CARD_KEY === 'transfer-in-from-produksi' && isFromTransferOut &&
                                newCount > 0 && currentKempu.is_flow_valid !== false) {
                                currentKempu.will_increment_reused = true;
                                currentKempu.target_reused_count = Math.min(21, newCount + 1);

                                $('#modalKempuReused').html(`
                                    <span class="text-muted text-decoration-line-through me-1 fs-12">${newCount}x</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-12">
                                        <i class="ri-arrow-up-line me-1"></i>${currentKempu.target_reused_count} / ${currentKempu.max_reused}x <span class="fw-normal">(Auto +1)</span>
                                    </span>
                                `);

                                $('#alertAutoIncrementBox').html(`
                                    <div class="alert alert-success border border-success-subtle d-flex align-items-center gap-2 mb-3 py-2 px-3">
                                        <i class="ri-add-circle-fill fs-18 text-success flex-shrink-0"></i>
                                        <div class="fs-12 text-success">
                                            <strong>Siklus Reused Otomatis (+1):</strong> Kempu tiba dari <em>Transfer Out To Produksi</em>, siklus pemakaian bertambah dari <strong>${newCount}x</strong> menjadi <strong>${currentKempu.target_reused_count}x</strong> saat dikonfirmasi.
                                        </div>
                                    </div>
                                `).show();
                            } else {
                                currentKempu.will_increment_reused = false;
                                currentKempu.target_reused_count = newCount;
                                $('#modalKempuReused').text(
                                    `${newCount} / ${currentKempu.max_reused}x`);
                                $('#alertAutoIncrementBox').hide();
                            }

                            // Jika sebelumnya kempu belum punya reused, sekarang sudah terisi
                            if (newCount > 0) {
                                $('#alertReusedBox').hide();
                                $('#boxInputReused').hide();
                                $('#targetStatusBox').removeClass('d-none').addClass('d-flex');
                                $('#physicalChecklistCard').show();
                                $('#notesBox').show();

                                if (currentKempu.is_flow_valid !== false) {
                                    $('#btnModalConfirm').prop('disabled', false).css({
                                        'background-color': "{{ $card['btn_color'] }}",
                                        'border-color': "{{ $card['btn_color'] }}",
                                        'color': '#ffffff'
                                    }).html(
                                        `<i class="ri-check-double-line me-1"></i> Konfirmasi`
                                    );
                                }
                            }

                            $('#reusedEditMode').addClass('d-none');
                            $('#reusedDisplayMode').removeClass('d-none');

                            // playBeep('confirm');
                            Swal.fire({
                                icon: 'success',
                                title: 'Nilai Reused Disimpan',
                                html: `Nilai Reused kempu <b>${kempuId}</b> berhasil disimpan (<b>${newCount}/21x</b>).`,
                                timer: 1800,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="ri-check-line"></i>');
                        Swal.fire('Gagal', xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat memperbarui nilai Reused.', 'error');
                    }
                });
            });

            // Inisialisasi awal
            startScanner();
        });
    </script>
@endsection
