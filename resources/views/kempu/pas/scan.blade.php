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
                                <li class="breadcrumb-item"><a href="#">Warehouse PAS</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('kempu.pas.index') }}">Kempu</a></li>
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
                        <a href="{{ route('kempu.pas.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                        <i class="ri-camera-lens-line text-primary me-1"></i> Pemindai Kamera Warehouse PAS
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

    <!-- MODAL INFORMASI DASAR KEMPU & KONFIRMASI -->
    <div class="modal fade" id="modalKempuConfirm" tabindex="-1" aria-labelledby="modalKempuConfirmLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Modal Header -->
                <div class="modal-header bg-light border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-xs flex-shrink-0">
                            <span class="avatar-title rounded-circle bg-primary-subtle fs-16">
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
                                <div id="reusedDisplayMode">
                                    <span class="fw-bold text-body" id="modalKempuReused">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Status Reused / Peringatan Otoritas -->
                    <div id="alertReusedBox" style="display: none;"></div>

                    <!-- Alert Validasi Alur Status -->
                    <div id="alertFlowErrorBox" style="display: none;"></div>

                    <!-- Target Status Baru Box -->
                    <div id="targetStatusBox"
                        class="alert alert-info border-info-subtle align-items-center gap-3 mb-3 py-2 px-3 d-none">
                        <i class="ri-arrow-right-circle-line fs-24 text-info flex-shrink-0"></i>
                        <div>
                            <div class="fs-11 text-muted text-uppercase fw-semibold">Status Baru yang Akan Disimpan:</div>
                            <div class="fs-15 fw-bold text-info">{{ $card['status_name'] }}</div>
                        </div>
                    </div>

                    <!-- Catatan Tambahan (Opsional) -->
                    <div id="notesBox" class="mb-2">
                        <label class="form-label fs-12 text-muted fw-medium mb-1">Catatan Tambahan (Opsional):</label>
                        <textarea id="modalInputNotes" class="form-control form-control-sm" rows="2"
                            placeholder="Contoh: Muatan lengkap, no surat jalan, kondisi kempu, dll..."></textarea>
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

                playBeep('success');
                $('#scanTargetFrame').addClass('scanned');

                // Pause camera scan during modal inspection
                if (qrScanner && isScannerActive) {
                    qrScanner.pause();
                }

                lookupKempu(decodedText.trim().toUpperCase());
            }

            // Lookup Kempu via AJAX
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
                    url: "{{ route('kempu.pas.lookup') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_kempu: idKempu,
                        card_key: CARD_KEY
                    },
                    success: function(res) {
                        if (res.status && res.data) {
                            currentKempu = res.data;
                            if (res.data.is_flow_valid === false) {
                                playBeep('error');
                            }
                            openConfirmModal(res.data);
                        } else {
                            handleLookupNotFound(res.message || 'Data kempu tidak ditemukan.');
                        }
                    },
                    error: function(xhr) {
                        playBeep('error');
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
                $('#modalKempuStatus').text(k.current_status);
                $('#modalKempuReused').text(`${k.reused_count} / ${k.max_reused}x`);
                $('#modalKempuCondition').text(k.condition);
                $('#modalInputNotes').val('');

                // Tampilkan Badge Tipe Kempu
                if (k.is_new_kempu) {
                    $('#modalKempuTypeBadge').html(
                        '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-11"><i class="ri-sparkling-line me-1"></i> Kempu Baru</span>'
                    );
                } else {
                    $('#modalKempuTypeBadge').html(
                        '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-11"><i class="ri-history-line me-1"></i> Kempu Lama</span>'
                    );
                }

                // Reset Alert & Input Reused Box & Mode Edit Inline
                $('#reusedEditMode').addClass('d-none');
                $('#reusedDisplayMode').removeClass('d-none');
                $('#alertReusedBox').hide().empty();
                $('#alertFlowErrorBox').hide().empty();
                $('#targetStatusBox').removeClass('d-none').addClass('d-flex');
                $('#notesBox').show();

                // Cek Tombol Koreksi Reused Cepat (Hanya jika user berwenang)
                if (k.can_edit_reused) {
                    $('#btnEditReusedQuick').show();
                } else {
                    $('#btnEditReusedQuick').hide();
                }

                // Cek Validasi Alur Status (Urutan & Duplikat Scan)
                if (k.is_flow_valid === false) {
                    playBeep('error');
                    $('#alertFlowErrorBox').html(`
                        <div class="alert alert-danger d-flex align-items-start gap-2 mb-3 py-2 px-3">
                            <i class="ri-error-warning-fill fs-20 text-danger flex-shrink-0 mt-1"></i>
                            <div>
                                <div class="fw-bold fs-13 text-danger">Validasi Alur Status Gagal</div>
                                <div class="fs-12">${k.flow_error}</div>
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
                lookupKempu(val.toUpperCase());
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

                const btn = $(this);
                btn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');

                const payload = {
                    _token: "{{ csrf_token() }}",
                    id_kempu: currentKempu.id_kempu,
                    card_key: CARD_KEY,
                    notes: $('#modalInputNotes').val(),
                };

                $.ajax({
                    url: "{{ route('kempu.pas.confirm') }}",
                    method: "POST",
                    data: payload,
                    success: function(res) {
                        playBeep('confirm');

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
                        playBeep('error');
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
                    url: "{{ route('kempu.pas.update_reused') }}",
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
                            currentKempu.has_reused = true;

                            $('#modalKempuReused').text(
                                `${newCount} / ${currentKempu.max_reused}x`);

                            $('#reusedEditMode').addClass('d-none');
                            $('#reusedDisplayMode').removeClass('d-none');

                            playBeep('confirm');
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
