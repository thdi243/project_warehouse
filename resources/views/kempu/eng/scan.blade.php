@extends('layouts.app')

@section('title', '| Scanner Engineering Repair')

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
            background: linear-gradient(90deg, transparent, #3b82f6, #6366f1, transparent);
            box-shadow: 0 0 10px #3b82f6;
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
                        <h4 class="mb-sm-0">Engineering Repair</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="#">Kempu</a></li>
                                <li class="breadcrumb-item"><a href="#">ENG</a></li>
                                <li class="breadcrumb-item active">Repair</li>
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
                            <span class="avatar-title rounded-circle fs-20 bg-soft-primary text-primary">
                                <i class="ri-tools-line"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-body">Engineering Workshop - Repair Kempu</h5>
                            <p class="mb-0 text-muted fs-12">Pemeriksaan dan perbaikan fisik kempu reject (Bisa Repair / Tidak Bisa Repair)</p>
                        </div>
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
                                        <i class="ri-camera-lens-line text-primary me-1"></i> Pemindai Kamera Repair
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
                                    <span class="input-group-text bg-light text-muted"><i class="ri-barcode-line"></i></span>
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

    <!-- MODAL DECISION ENGINEERING (BISA REPAIR ATAU TIDAK BISA REPAIR) -->
    <div class="modal fade" id="modalEngDecision" tabindex="-1" aria-labelledby="modalEngDecisionLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Modal Header -->
                <div class="modal-header bg-light border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-xs flex-shrink-0">
                            <span class="avatar-title rounded-circle bg-soft-primary text-primary fs-16">
                                <i class="ri-tools-line"></i>
                            </span>
                        </div>
                        <h5 class="modal-title fw-bold text-body fs-16" id="modalEngDecisionLabel">
                            Pemeriksaan Engineering Repair
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Barcode Title Banner -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-3 bg-light border border-dashed">
                        <div>
                            <div class="text-muted fs-11 text-uppercase fw-semibold">ID / Barcode Kempu</div>
                            <div class="fs-22 fw-bold font-monospace text-primary" id="modalKempuId">-</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-soft-info text-info border border-info-subtle px-2 py-1 fs-12 mb-1 d-inline-block" id="modalKempuLoc">
                                -
                            </span>
                        </div>
                    </div>

                    <!-- Detail Info -->
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <div class="row g-2 fs-13">
                            <div class="col-4">
                                <span class="text-muted d-block fs-11">RFID:</span>
                                <span class="fw-semibold font-monospace text-body" id="modalKempuRfid">-</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block fs-11">Status Saat Ini:</span>
                                <span class="badge bg-light text-body border" id="modalKempuStatus">-</span>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block fs-11">Siklus Reused:</span>
                                <span class="fw-bold text-body" id="modalKempuReused">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Input (Opsional) -->
                    <div class="mb-3">
                        <label for="modalInputNotes" class="form-label fs-12 fw-semibold text-body mb-1">
                            Catatan Tindakan / Kerusakan (Opsional):
                        </label>
                        <input type="text" class="form-control" id="modalInputNotes"
                            placeholder="Contoh: Ganti kran valve, las rangka bawah, bocor parah..." autocomplete="off">
                    </div>

                    <!-- Action Decision Buttons: Bisa Repair vs Tidak Bisa Repair -->
                    <div class="row g-2 pt-2 border-top">
                        <div class="col-6">
                            <button type="button" class="btn btn-success btn-lg w-100 py-3 fw-bold fs-15 shadow-sm" id="btnDecisionBisa">
                                <i class="ri-checkbox-circle-line me-1"></i> Bisa Repair
                            </button>
                            <div class="text-muted fs-11 text-center mt-1">Kembalikan ke QC PM</div>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-danger btn-lg w-100 py-3 fw-bold fs-15 shadow-sm" id="btnDecisionTidakBisa">
                                <i class="ri-close-circle-line me-1"></i> Tidak Bisa Repair
                            </button>
                            <div class="text-muted fs-11 text-center mt-1">Teruskan BA Scrap</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Html5Qrcode Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        $(document).ready(function() {
            let qrScanner = null;
            let currentFacing = "environment";
            let availableCameras = [];
            let currentCameraIndex = 0;
            let isScannerActive = false;
            let currentKempu = null;

            const decisionModal = new bootstrap.Modal(document.getElementById('modalEngDecision'), {
                backdrop: 'static',
                keyboard: false
            });

            // Inisialisasi Audio Beep (Web Audio API)
            let audioCtx = null;
            function playBeep(type = 'beep') {
                try {
                    if (!audioCtx) {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
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
                        // Filter kamera virtual (OBS Virtual Camera, dsb)
                        let realCameras = devices.filter(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return !lbl.includes('obs') && !lbl.includes('virtual') && !lbl.includes('fake');
                        });

                        availableCameras = realCameras.length > 0 ? realCameras : devices;

                        // Jika ada kamera belakang (misal di HP), utamakan kamera belakang
                        let backCamIdx = availableCameras.findIndex(d => {
                            const lbl = (d.label || '').toLowerCase();
                            return lbl.includes('back') || lbl.includes('rear') || lbl.includes('environment') || lbl.includes('belakang');
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
                        return qrScanner.start(
                            { facingMode: currentFacing },
                            config,
                            onScanSuccess,
                            function(err) {}
                        );
                    }
                }).catch(err => {
                    return qrScanner.start(
                        { facingMode: currentFacing },
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

            // Lookup Barcode via AJAX
            function lookupKempu(idKempu) {
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
                    url: "{{ route('kempu.eng.lookup') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_kempu: idKempu
                    },
                    success: function(res) {
                        if (res.status && res.data) {
                            currentKempu = res.data;

                            if (!res.data.is_flow_valid) {
                                playBeep('error');
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Alur Tidak Sesuai',
                                    html: res.data.flow_error,
                                    confirmButtonText: 'Tutup'
                                }).then(() => {
                                    resumeScanner();
                                });
                                return;
                            }

                            playBeep('success');
                            showDecisionModal(res.data);
                        } else {
                            playBeep('error');
                            Swal.fire('Tidak Ditemukan', res.message || 'Data kempu tidak ditemukan.', 'error').then(() => {
                                resumeScanner();
                            });
                        }
                    },
                    error: function(xhr) {
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

            // Tampilkan Modal Keputusan Engineering
            function showDecisionModal(k) {
                $('#modalKempuId').text(k.id_kempu);
                $('#modalKempuRfid').text(k.rfid || '-');
                $('#modalKempuLoc').text(k.current_location || '-');
                $('#modalKempuStatus').text(k.current_status || '-');
                $('#modalKempuReused').text((k.reused_count || 0) + 'x');
                $('#modalInputNotes').val('');

                $('#btnDecisionBisa').prop('disabled', false).html('<i class="ri-checkbox-circle-line me-1"></i> Bisa Repair');
                $('#btnDecisionTidakBisa').prop('disabled', false).html('<i class="ri-close-circle-line me-1"></i> Tidak Bisa Repair');

                decisionModal.show();
            }

            // Reset saat modal ditutup
            $('#modalEngDecision').on('hidden.bs.modal', function() {
                resumeScanner();
                $('#inputManualId').val('').focus();
            });

            // Eksekusi Keputusan Engineering (BISA_REPAIR atau TIDAK_BISA_REPAIR)
            function submitDecision(decision) {
                if (!currentKempu) return;

                const btnBisa = $('#btnDecisionBisa');
                const btnTidakBisa = $('#btnDecisionTidakBisa');
                btnBisa.prop('disabled', true);
                btnTidakBisa.prop('disabled', true);

                if (decision === 'BISA_REPAIR') {
                    btnBisa.html('<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');
                } else {
                    btnTidakBisa.html('<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');
                }

                $.ajax({
                    url: "{{ route('kempu.eng.decision') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_kempu: currentKempu.id_kempu,
                        decision: decision,
                        notes: $('#modalInputNotes').val().trim()
                    },
                    success: function(res) {
                        btnBisa.prop('disabled', false).html('<i class="ri-checkbox-circle-line me-1"></i> Bisa Repair');
                        btnTidakBisa.prop('disabled', false).html('<i class="ri-close-circle-line me-1"></i> Tidak Bisa Repair');

                        if (res.status) {
                            decisionModal.hide();
                            playBeep('success');

                            const badgeColor = decision === 'BISA_REPAIR' ? 'success' : 'danger';
                            const label = decision === 'BISA_REPAIR' ? 'BISA REPAIR' : 'TIDAK BISA REPAIR (SCRAP)';

                            Swal.fire({
                                icon: decision === 'BISA_REPAIR' ? 'success' : 'warning',
                                title: `Hasil: ${label}`,
                                html: `Kempu <b>${currentKempu.id_kempu}</b> berhasil diperbarui ke status:<br><span class="badge bg-${badgeColor} fs-13 mt-2 px-3 py-2">${res.data.new_status}</span><br><div class="fs-12 text-muted mt-2">${res.message}</div>`,
                                timer: 2500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        } else {
                            playBeep('error');
                            Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                        }
                    },
                    error: function(xhr) {
                        btnBisa.prop('disabled', false).html('<i class="ri-checkbox-circle-line me-1"></i> Bisa Repair');
                        btnTidakBisa.prop('disabled', false).html('<i class="ri-close-circle-line me-1"></i> Tidak Bisa Repair');
                        playBeep('error');

                        let msg = 'Terjadi kesalahan server saat menyimpan keputusan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal', msg, 'error');
                    }
                });
            }

            // Tombol Decision Bisa Repair
            $('#btnDecisionBisa').on('click', function() {
                submitDecision('BISA_REPAIR');
            });

            // Tombol Decision Tidak Bisa Repair
            $('#btnDecisionTidakBisa').on('click', function() {
                submitDecision('TIDAK_BISA_REPAIR');
            });

            // Manual Lookup Triggers
            $('#btnLookupManual').on('click', function() {
                const val = $('#inputManualId').val().trim();
                if (!val) {
                    Swal.fire('Peringatan', 'Silakan masukkan ID Kempu atau Scan Barcode terlebih dahulu.', 'warning');
                    $('#inputManualId').focus();
                    return;
                }
                pauseScanner();
                lookupKempu(val.toUpperCase());
            });

            $('#inputManualId').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnLookupManual').trigger('click');
                }
            });

            // Inisialisasi awal scanner
            startScanner();
        });
    </script>
@endsection
