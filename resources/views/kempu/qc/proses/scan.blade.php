@extends('layouts.app')

@section('title', '| Scanner ' . $card['title'])

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
                        <h4 class="mb-sm-0">Scanner {{ $card['title'] }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="#">Kempu</a></li>
                                @if ($card['key'] === 'qc-pm')
                                    <li class="breadcrumb-item"><a href="{{ route('kempu.qc.pm.index') }}">QC PM</a></li>
                                @else
                                    <li class="breadcrumb-item"><a href="{{ route('kempu.qc.proses.index') }}">QC Proses</a>
                                    </li>
                                @endif
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
                            <h5 class="mb-1 fw-bold text-body">{{ $card['title'] }} - {{ $card['subtitle'] }}</h5>
                            <p class="mb-0 text-muted fs-12">{{ $card['description'] }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ $card['key'] === 'qc-pm' ? route('kempu.qc.pm.index') : route('kempu.qc.proses.index') }}"
                            class="btn btn-sm btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i>
                            Kembali{{ $card['key'] === 'qc-pm' ? ' ke QC PM' : ' ke QC Proses' }}
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
                                        <i class="ri-camera-lens-line text-primary me-1"></i> Pemindai Kamera
                                        {{ $card['title'] }}
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
                                <label class="form-label fs-13 fw-semibold text-body mb-2">
                                    <i class="ri-keyboard-line text-muted me-1"></i> Atau Masukkan ID Kempu Manual:
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light text-muted"><i
                                            class="ri-barcode-line"></i></span>
                                    <input type="text" id="inputManualId" class="form-control font-monospace"
                                        placeholder="Contoh: KMP-001 lalu tekan Enter..." autocomplete="off">
                                    <button class="btn btn-primary px-4 fw-semibold" type="button" id="btnLookupManual">
                                        <i class="ri-search-line me-1"></i> Cari Kempu
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL DECISION QC (OK ATAU TIDAK OK) -->
    <div class="modal fade" id="modalQcDecision" tabindex="-1" aria-labelledby="modalQcDecisionLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Modal Header -->
                <div class="modal-header bg-light border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-xs flex-shrink-0">
                            <span
                                class="avatar-title rounded-circle bg-soft-{{ $card['badge_color'] }} text-{{ $card['badge_color'] }} fs-16">
                                <i class="{{ $card['icon'] }}"></i>
                            </span>
                        </div>
                        <h5 class="modal-title fw-bold text-body fs-16" id="modalQcDecisionLabel">
                            Pemeriksaan {{ $card['title'] }}
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Barcode Title Banner -->
                    <div
                        class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-3 bg-light border border-dashed">
                        <div>
                            <div class="text-muted fs-11 text-uppercase fw-semibold">ID / Barcode Kempu</div>
                            <div class="fs-22 fw-bold font-monospace text-primary" id="modalKempuId">-</div>
                        </div>
                        <div class="text-end">
                            <span
                                class="badge bg-soft-info text-info border border-info-subtle px-2 py-1 fs-12 mb-1 d-inline-block"
                                id="modalKempuLoc">
                                -
                            </span>
                        </div>
                    </div>

                    <!-- Detail Info -->
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <div class="row g-2 fs-13">
                            <div class="col-4 text-center">
                                <span class="text-muted d-block fs-11">RFID:</span>
                                <span class="fw-semibold font-monospace text-body" id="modalKempuRfid">-</span>
                            </div>
                            <div class="col-4 text-center">
                                <span class="text-muted d-block fs-11">Status Saat Ini:</span>
                                <span class="badge bg-light text-body border" id="modalKempuStatus">-</span>
                            </div>
                            <div class="col-4 text-center">
                                <span class="text-muted d-block fs-11">Siklus Reused:</span>
                                <span class="fw-bold text-body" id="modalKempuReused">-</span>
                            </div>
                        </div>
                    </div>

                    @if ($card['key'] === 'qc-pre-cuci' || $card['key'] === 'qc-proses')
                        <div class="alert alert-info py-2 px-3 mb-3 fs-12 d-flex align-items-center gap-2">
                            <i class="ri-information-line fs-16 flex-shrink-0 text-primary"></i>
                            <div><strong>Cek Incoming & Pre Cuci:</strong> Pemeriksaan fisik incoming kempu sekaligus
                                verifikasi kelayakan pre-cuci. Keputusan <strong>OK (Lolos)</strong> akan menambah
                                <strong>+1 siklus pemakaian (Reused)</strong> kempu sebelum proses pencucian.</div>
                        </div>
                    @elseif ($card['key'] === 'qc-after-filling')
                        <div class="alert alert-info py-2 px-3 mb-3 fs-12 d-flex align-items-center gap-2">
                            <i class="ri-flask-line fs-16 flex-shrink-0 text-success"></i>
                            <div><strong>After Filling:</strong> Pemeriksaan kempu setelah pengisian muatan (Filling).
                                Tentukan keputusan: <strong>OK (Lolos)</strong>, <strong>Hold (Tahan)</strong>, atau
                                <strong>Tidak OK (Reject)</strong>.</div>
                        </div>
                    @endif

                    <!-- Notes Input (Opsional) -->
                    <div class="mb-3">
                        <label for="modalInputNotes" class="form-label fs-12 fw-semibold text-body mb-1">
                            Catatan Pemeriksaan (Opsional):
                        </label>
                        <input type="text" class="form-control" id="modalInputNotes"
                            placeholder="Tuliskan keterangan jika ada catatan khusus atau reject/hold..."
                            autocomplete="off">
                    </div>

                    <!-- Action Decision Buttons -->
                    @if ($card['key'] === 'qc-after-filling')
                        <div class="row g-2 pt-2 border-top">
                            <div class="col-4">
                                <button type="button" class="btn btn-success btn-lg w-100 py-3 fw-bold fs-14 shadow-sm"
                                    id="btnDecisionOk">
                                    <i class="ri-checkbox-circle-line me-1"></i> Release (OK)
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button"
                                    class="btn btn-warning btn-lg w-100 py-3 fw-bold fs-14 shadow-sm text-dark"
                                    id="btnDecisionHold">
                                    <i class="ri-pause-circle-line me-1"></i> Hold
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-danger btn-lg w-100 py-3 fw-bold fs-14 shadow-sm"
                                    id="btnDecisionNotOk">
                                    <i class="ri-close-circle-line me-1"></i> Tidak OK
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="row g-2 pt-2 border-top">
                            <div class="col-6">
                                <button type="button" class="btn btn-success btn-lg w-100 py-3 fw-bold fs-15 shadow-sm"
                                    id="btnDecisionOk">
                                    <i class="ri-checkbox-circle-line me-1"></i> Release (OK)
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-danger btn-lg w-100 py-3 fw-bold fs-15 shadow-sm"
                                    id="btnDecisionNotOk">
                                    <i class="ri-close-circle-line me-1"></i> Tidak OK
                                </button>
                            </div>
                        </div>
                    @endif
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
            const QC_TYPE = "{{ $card['key'] }}";
            let qrScanner = null;
            let currentFacing = "environment";
            let availableCameras = [];
            let currentCameraIndex = 0;
            let isScannerActive = false;
            let currentKempu = null;

            const decisionModal = new bootstrap.Modal(document.getElementById('modalQcDecision'), {
                backdrop: 'static',
                keyboard: false
            });

            // Inisialisasi Audio Beep (Web Audio API)
            let audioCtx = null;

            function playBeep(type = 'beep') {
                try {
                    if (!audioCtx) {
                        audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                    }
                    if (audioCtx.state === 'suspended') {
                        audioCtx.resume();
                    }
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);

                    if (type === 'success') {
                        osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.18);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.18);
                    } else if (type === 'error') {
                        osc.frequency.setValueAtTime(220, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.4, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.35);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.35);
                    } else {
                        osc.frequency.setValueAtTime(600, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.1);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.1);
                    }
                } catch (e) {
                    console.warn('AudioContext error:', e);
                }
            }

            // Inisialisasi Scanner Kamera dengan Auto-Filter Kamera Fisik (Abaikan OBS Virtual Camera)
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
                        // Filter kamera virtual (OBS Virtual Camera, dsb) agar langsung memilih webcam fisik asli
                        let realCameras = devices.filter(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return !lbl.includes('obs') && !lbl.includes('virtual') && !lbl
                                .includes('fake');
                        });

                        availableCameras = realCameras.length > 0 ? realCameras : devices;

                        // Jika ada kamera belakang (misal di HP), utamakan kamera belakang
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
                            function(err) {} // silent on continuous frame
                        );
                    } else {
                        // Fallback jika daftar device belum terenumerasi
                        return qrScanner.start({
                                facingMode: currentFacing
                            },
                            config,
                            onScanSuccess,
                            function(err) {}
                        );
                    }
                }).catch(err => {
                    // Fallback ke facingMode
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

                if (currentKempu && currentKempu.id_kempu === decodedText.trim().toUpperCase()) {
                    return;
                }

                playBeep('success');
                $('#scanTargetFrame').addClass('scanned');

                if (qrScanner && isScannerActive) {
                    qrScanner.pause();
                }

                lookupKempu(decodedText.trim().toUpperCase());
            }

            function pauseScanner() {
                if (qrScanner && isScannerActive) {
                    try {
                        qrScanner.pause();
                    } catch (e) {}
                }
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

            // Callback ketika Barcode terdeteksi
            let lastScanTime = 0;

            function onBarcodeDetected(code) {
                const now = Date.now();
                if (now - lastScanTime < 2000) return; // Debounce 2 detik
                lastScanTime = now;

                $('#scanTargetFrame').addClass('scanned');
                playBeep('beep');
                pauseScanner();
                lookupKempu(code);
            }

            // Manual Input
            $('#btnLookupManual').on('click', function() {
                const val = $('#inputManualId').val().trim();
                if (val) {
                    pauseScanner();
                    lookupKempu(val);
                } else {
                    $('#inputManualId').focus();
                }
            });

            $('#inputManualId').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnLookupManual').click();
                }
            });

            // Lookup Kempu ke Server
            function lookupKempu(code) {
                Swal.fire({
                    title: 'Memeriksa Kempu...',
                    text: 'ID: ' + code,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('kempu.qc.lookup') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_kempu: code,
                        qc_type: QC_TYPE
                    },
                    success: function(res) {
                        Swal.close();
                        if (res.status && res.data) {
                            const k = res.data;

                            // Cek Validasi Alur
                            if (k.is_flow_valid === false) {
                                playBeep('error');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Alur Status Tidak Sesuai',
                                    html: k.flow_error,
                                    confirmButtonText: 'Tutup & Scan Lain'
                                }).then(() => {
                                    resumeScanner();
                                });
                                return;
                            }

                            // Alur Valid -> Tampilkan Modal Keputusan
                            playBeep('success');
                            currentKempu = k;
                            showDecisionModal(k);
                        } else {
                            playBeep('error');
                            Swal.fire('Gagal', res.message || 'Kempu tidak ditemukan.', 'error').then(
                            () => {
                                    resumeScanner();
                                });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        playBeep('error');
                        let msg = 'Kempu tidak ditemukan atau terjadi kesalahan server.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Tidak Ditemukan', msg, 'error').then(() => {
                            resumeScanner();
                        });
                    }
                });
            }

            // Tampilkan Modal Keputusan QC
            function showDecisionModal(k) {
                $('#modalKempuId').text(k.id_kempu);
                $('#modalKempuRfid').text(k.rfid || '-');
                $('#modalKempuLoc').text(k.current_location || '-');
                $('#modalKempuStatus').text(k.current_status || '-');
                $('#modalKempuReused').text((k.reused_count || 0) + 'x');
                $('#modalInputNotes').val('');

                $('#btnDecisionOk').prop('disabled', false).html(
                    '<i class="ri-checkbox-circle-line me-1"></i> Release (OK)');
                if ($('#btnDecisionHold').length) {
                    $('#btnDecisionHold').prop('disabled', false).html(
                        '<i class="ri-pause-circle-line me-1"></i> Hold');
                }
                $('#btnDecisionNotOk').prop('disabled', false).html(
                    '<i class="ri-close-circle-line me-1"></i> Tidak OK');

                decisionModal.show();
            }

            // Reset saat modal ditutup
            $('#modalQcDecision').on('hidden.bs.modal', function() {
                resumeScanner();
                $('#inputManualId').val('').focus();
            });

            // Eksekusi Keputusan QC
            function submitDecision(decision) {
                if (!currentKempu) return;

                const btnOk = $('#btnDecisionOk');
                const btnHold = $('#btnDecisionHold');
                const btnNotOk = $('#btnDecisionNotOk');
                btnOk.prop('disabled', true);
                if (btnHold.length) btnHold.prop('disabled', true);
                btnNotOk.prop('disabled', true);

                if (decision === 'OK') {
                    btnOk.html('<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');
                } else if (decision === 'HOLD') {
                    btnHold.html('<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');
                } else {
                    btnNotOk.html('<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');
                }

                $.ajax({
                    url: "{{ route('kempu.qc.decision') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_kempu: currentKempu.id_kempu,
                        qc_type: QC_TYPE,
                        decision: decision,
                        notes: $('#modalInputNotes').val().trim()
                    },
                    success: function(res) {
                        btnOk.prop('disabled', false).html(
                            '<i class="ri-checkbox-circle-line me-1"></i> Release (OK)');
                        if (btnHold.length) btnHold.prop('disabled', false).html(
                            '<i class="ri-pause-circle-line me-1"></i> Hold');
                        btnNotOk.prop('disabled', false).html(
                            '<i class="ri-close-circle-line me-1"></i> Tidak OK');

                        if (res.status) {
                            decisionModal.hide();
                            playBeep('success');

                            let badgeColor = 'success';
                            let label = 'Release (OK)';
                            let iconType = 'success';

                            if (decision === 'HOLD') {
                                badgeColor = 'warning text-dark';
                                label = 'HOLD (Tahan)';
                                iconType = 'warning';
                            } else if (decision === 'NOT_OK') {
                                badgeColor = 'danger';
                                label = 'TIDAK OK (Reject)';
                                iconType = 'error';
                            }

                            let infoReused = '';
                            if (res.data && res.data.reused_count !== undefined && (QC_TYPE ===
                                    'qc-pre-cuci' || QC_TYPE === 'qc-proses')) {
                                infoReused =
                                    `<br><span class="badge bg-primary fs-12 mt-2 px-3 py-1">Siklus Reused: ${res.data.reused_count}/21x</span>`;
                            }

                            Swal.fire({
                                icon: iconType,
                                title: `Hasil QC: ${label}`,
                                html: `Kempu <b>${currentKempu.id_kempu}</b> berhasil diperbarui ke status:<br><span class="badge bg-${badgeColor} fs-13 mt-2 px-3 py-2">${res.data.new_status}</span>${infoReused}`,
                                timer: 2200,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        } else {
                            playBeep('error');
                            Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                        }
                    },
                    error: function(xhr) {
                        btnOk.prop('disabled', false).html(
                            '<i class="ri-checkbox-circle-line me-1"></i> Release (OK)');
                        if (btnHold.length) btnHold.prop('disabled', false).html(
                            '<i class="ri-pause-circle-line me-1"></i> Hold');
                        btnNotOk.prop('disabled', false).html(
                            '<i class="ri-close-circle-line me-1"></i> Tidak OK');
                        playBeep('error');

                        let msg = 'Terjadi kesalahan server saat menyimpan hasil QC.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal', msg, 'error');
                    }
                });
            }

            // Tombol Decision OK
            $('#btnDecisionOk').on('click', function() {
                submitDecision('OK');
            });

            // Tombol Decision Hold
            $('#btnDecisionHold').on('click', function() {
                submitDecision('HOLD');
            });

            // Tombol Decision Tidak OK
            $('#btnDecisionNotOk').on('click', function() {
                submitDecision('NOT_OK');
            });

            // Inisialisasi awal
            startScanner();
        });
    </script>
@endsection
