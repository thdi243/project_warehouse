@extends('layouts.app')

@section('title', '| Cek Incoming Bulk QC PM')

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

        .kempu-table th,
        .kempu-table td {
            vertical-align: middle;
        }

        .row-ok {
            background-color: rgba(25, 135, 84, 0.04) !important;
        }

        .row-reject {
            background-color: rgba(220, 53, 69, 0.05) !important;
        }

        .cursor-pointer {
            cursor: pointer;
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
                        <h4 class="mb-sm-0">Cek Incoming Bulk (QC PM)</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="#">Kempu</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('kempu.qc.pm.index') }}">QC PM</a></li>
                                <li class="breadcrumb-item active">Incoming Bulk</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="card shadow-sm mb-4">
                <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title rounded-circle fs-20 bg-soft-success text-success">
                                <i class="ri-stack-line"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-body">Pemeriksaan Incoming Bulk (Per No SPB)</h5>
                            <p class="mb-0 text-muted fs-12">Arahkan kamera ke salah satu barcode kempu incoming untuk
                                memuat dan memutuskan status seluruh kempu dalam SPB tersebut sekaligus.</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('kempu.qc.pm.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i> Kembali ke Menu QC PM
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
                                        <i class="ri-camera-lens-line text-success me-1"></i> Pemindai Kamera Incoming Bulk
                                    </span>
                                    <div class="text-muted fs-12">Arahkan kamera ke barcode salah satu kempu dalam SPB</div>
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

                            <!-- Manual / Gun Barcode Input Box -->
                            <div class="mt-4 pt-2 border-top">
                                <label class="form-label fs-13 fw-semibold text-body mb-2">
                                    <i class="ri-barcode-line text-primary me-1"></i> Atau Masukkan Barcode / ID Kempu
                                    Manual:
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light text-muted"><i
                                            class="ri-qr-scan-line"></i></span>
                                    <input type="text" id="inputManualId"
                                        class="form-control font-monospace text-uppercase"
                                        placeholder="Contoh: 2609240001 lalu tekan Enter..." autocomplete="off">
                                    <button class="btn btn-primary px-4 fw-semibold" type="button" id="btnLookupManual">
                                        <i class="ri-search-line me-1"></i> Cari SPB
                                    </button>
                                </div>
                                <div class="form-text mt-2 text-muted">
                                    <i class="ri-information-line me-1"></i> Scan atau masukkan <strong>1 barcode
                                        kempu</strong> dari kiriman incoming. Sistem otomatis menarik semua kempu pada No
                                    SPB terkait.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results & Decision Section (Shown after SPB is loaded) -->
            <div id="panelResults" style="display: none;">
                <!-- SPB Header Info Card -->
                <div class="card shadow-sm border-top border-3 border-success mb-3">
                    <div class="card-body p-4">
                        <div class="row align-items-center gy-3">
                            <div class="col-lg-6 col-md-12">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-md flex-shrink-0">
                                        <span class="avatar-title rounded bg-soft-primary text-primary fs-24">
                                            <i class="ri-file-list-3-line"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-12 text-uppercase fw-semibold">Nomor SPB Terkait</div>
                                        <h3 class="fw-bold mb-0 text-primary font-monospace" id="labelNoSpb">-</h3>
                                        <div class="text-muted fs-12 mt-1">
                                            <span>Tanggal GR: <strong id="labelGrDate" class="text-body">-</strong></span> |
                                            <span>Kempu Pemantik: <strong id="labelScannedId"
                                                    class="font-monospace text-body">-</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="d-flex align-items-center justify-content-lg-end gap-2 flex-wrap">
                                    <div class="px-3 py-2 rounded bg-light border text-center">
                                        <div class="text-muted fs-11 text-uppercase">Total Kempu</div>
                                        <h4 class="mb-0 fw-bold text-body" id="badgeTotalCount">0</h4>
                                    </div>
                                    <div class="px-3 py-2 rounded bg-soft-success border border-success-subtle text-center">
                                        <div class="text-success fs-11 text-uppercase fw-semibold">Lolos (OK)</div>
                                        <h4 class="mb-0 fw-bold text-success" id="badgeOkCount">0</h4>
                                    </div>
                                    <div class="px-3 py-2 rounded bg-soft-danger border border-danger-subtle text-center">
                                        <div class="text-danger fs-11 text-uppercase fw-semibold">Reject (Workshop)</div>
                                        <h4 class="mb-0 fw-bold text-danger" id="badgeRejectCount">0</h4>
                                    </div>
                                    <div class="ms-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            id="btnScanAnother">
                                            <i class="ri-refresh-line me-1"></i> Scan SPB Lain
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table & Action Checklist Card -->
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-success fw-semibold" id="btnCheckAll">
                                    <i class="ri-checkbox-line me-1"></i> Check All (Semua OK)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger fw-semibold"
                                    id="btnUncheckAll">
                                    <i class="ri-checkbox-blank-line me-1"></i> Uncheck All (Semua Reject)
                                </button>
                                <span class="text-muted fs-12 ms-2">
                                    <i class="ri-information-line me-1"></i> Checklist (<i
                                        class="ri-check-line text-success"></i>) = <strong>Lolos (OK)</strong>, Uncheck =
                                    <strong>Reject (Workshop)</strong>
                                </span>
                            </div>
                            <div style="min-width: 220px;">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="ri-search-line"></i></span>
                                    <input type="text" id="tableFilter" class="form-control"
                                        placeholder="Cari ID kempu...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                            <table class="table table-hover table-striped mb-0 kempu-table" id="tableKempu">
                                <thead class="table-light sticky-top" style="z-index: 2;">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">
                                            <input type="checkbox" class="form-check-input" id="checkHeader" checked
                                                title="Pilih Semua">
                                        </th>
                                        <th style="width: 50px;" class="text-center">No</th>
                                        <th>ID Kempu</th>
                                        <th>RFID</th>
                                        <th>Status Saat Ini</th>
                                        <th>Siklus Reused</th>
                                        <th class="text-center" style="width: 200px;">Keputusan QC PM</th>
                                    </tr>
                                </thead>
                                <tbody id="kempuListBody">
                                    <!-- Rendered via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-light p-4">
                        <div class="row align-items-center gy-3">
                            <div class="col-lg-7 col-md-12">
                                <label class="form-label fw-semibold fs-13 mb-1">
                                    <i class="ri-edit-line me-1"></i> Catatan Pemeriksaan QC PM (Opsional):
                                </label>
                                <input type="text" id="bulkNotes" class="form-control"
                                    placeholder="Contoh: Incoming SPB lengkap, kempu dalam kondisi bersih & siap produksi...">
                            </div>
                            <div class="col-lg-5 col-md-12 text-lg-end">
                                <button type="button" class="btn btn-primary btn-lg px-4 shadow fw-bold"
                                    id="btnConfirmBulk">
                                    <i class="ri-check-double-line me-1"></i> Konfirmasi Keputusan Bulk
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <!-- HTML5 QR-Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        $(document).ready(function() {
            let qrScanner = null;
            let currentFacing = "environment";
            let availableCameras = [];
            let currentCameraIndex = 0;
            let isScannerActive = false;
            let currentNoSpb = '';
            let currentItems = [];

            // Focus manual input
            $('#inputManualId').focus();

            // Inisialisasi Audio Beep
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

            // Inisialisasi Scanner Kamera (Auto-Filter OBS / Virtual Camera)
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
                        <div class="fs-12 text-muted mt-1">Anda dapat menggunakan input manual / scanner barcode di bawah.</div>
                        <button class="btn btn-xs btn-primary mt-2" onclick="location.reload()">Coba Lagi</button>
                    `);
                });
            }

            // Callback ketika kamera mendeteksi barcode
            let lastScanTime = 0;

            function onScanSuccess(decodedText, decodedResult) {
                if (!decodedText) return;
                const now = Date.now();
                if (now - lastScanTime < 2000) return; // Debounce 2 detik
                lastScanTime = now;

                playBeep('success');
                $('#scanTargetFrame').addClass('scanned');

                pauseScanner();
                lookupSpb(decodedText.trim().toUpperCase());
            }

            function pauseScanner() {
                if (qrScanner && isScannerActive) {
                    try {
                        qrScanner.pause();
                    } catch (e) {}
                }
            }

            function resumeScanner() {
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

            // Reset Form dan Panel Bulk
            function resetBulkForm() {
                currentNoSpb = '';
                currentItems = [];
                $('#inputManualId').val('');
                $('#bulkNotes').val('');
                $('#tableFilter').val('');
                $('#kempuListBody').empty();
                $('#labelNoSpb').text('-');
                $('#labelGrDate').text('-');
                $('#labelScannedId').text('-');
                $('#badgeTotalCount').text('0');
                $('#badgeOkCount').text('0');
                $('#badgeRejectCount').text('0');
                $('#checkHeader').prop('checked', false);
                $('#panelResults').slideUp(200);
                resumeScanner();
                $('html, body').animate({
                    scrollTop: 0
                }, 250);
            }

            // Tombol Scan SPB Lain
            $('#btnScanAnother').on('click', function() {
                resetBulkForm();
            });

            // Manual Input
            $('#btnLookupManual').on('click', function() {
                const val = $('#inputManualId').val().trim();
                if (val) {
                    pauseScanner();
                    lookupSpb(val);
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

            // AJAX Lookup Data SPB
            function lookupSpb(barcode) {
                Swal.fire({
                    title: 'Memuat Data SPB...',
                    text: 'Mencari kempu: ' + barcode,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('kempu.qc.pm.bulk.lookup') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        barcode: barcode
                    },
                    success: function(res) {
                        Swal.close();
                        if (res.status && res.data) {
                            renderBulkData(res.data);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message || 'Data tidak ditemukan.'
                            }).then(() => {
                                resumeScanner();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        playBeep('error');
                        let msg = 'Terjadi kesalahan saat memuat data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Pemeriksaan Gagal',
                            text: msg
                        }).then(() => {
                            resumeScanner();
                        });
                    }
                });
            }

            // Render Data Bulk ke Table
            function renderBulkData(data) {
                currentNoSpb = data.no_spb;
                currentItems = data.items || [];

                $('#labelNoSpb').text(data.no_spb);
                $('#labelGrDate').text(data.gr_date || '-');
                $('#labelScannedId').text(data.scanned_id || '-');
                $('#badgeTotalCount').text(data.total_count || currentItems.length);

                const $tbody = $('#kempuListBody');
                $tbody.empty();

                currentItems.forEach(function(item, index) {
                    const isChecked = item.default_checked ? 'checked' : '';
                    const rowClass = item.default_checked ? 'row-ok' : 'row-reject';
                    const decisionBadge = item.default_checked ?
                        '<span class="badge bg-success-subtle text-success fs-12 px-2 py-1"><i class="ri-check-line me-1"></i> Lolos (Release)</span>' :
                        '<span class="badge bg-danger-subtle text-danger fs-12 px-2 py-1"><i class="ri-close-line me-1"></i> Reject (Workshop)</span>';

                    let statusBadgeClass = 'bg-soft-primary text-primary';
                    if (item.is_release || item.is_passed) statusBadgeClass = 'bg-soft-success text-success';
                    else if (item.is_reject) statusBadgeClass = 'bg-soft-danger text-danger';

                    const rowHtml = `
                        <tr class="${rowClass}" data-id="${item.id_kempu}">
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input kempu-check" value="${item.id_kempu}" ${isChecked}>
                            </td>
                            <td class="text-center text-muted fs-12">${index + 1}</td>
                            <td>
                                <span class="fw-bold font-monospace fs-13 text-body">${item.id_kempu}</span>
                            </td>
                            <td>
                                <span class="text-muted font-monospace fs-12">${item.rfid || '-'}</span>
                            </td>
                            <td>
                                <span class="badge ${statusBadgeClass} rounded-pill px-2 py-1 fs-11">${item.current_status}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-body border px-2 py-1 fs-11">${item.reused_count}/21x</span>
                            </td>
                            <td class="text-center decision-cell">
                                ${decisionBadge}
                            </td>
                        </tr>
                    `;
                    $tbody.append(rowHtml);
                });

                updateCounters();
                $('#panelResults').slideDown(250, function() {
                    // Scroll ke bagian hasil
                    $('html, body').animate({
                        scrollTop: $('#panelResults').offset().top - 80
                    }, 400);
                });
            }

            // Hitung Ulang Counter OK vs Reject
            function updateCounters() {
                const total = $('.kempu-check').length;
                const checked = $('.kempu-check:checked').length;
                const unchecked = total - checked;

                $('#badgeOkCount').text(checked);
                $('#badgeRejectCount').text(unchecked);

                $('#checkHeader').prop('checked', checked === total && total > 0);
            }

            // Event toggle per checkbox kempu
            $(document).on('change', '.kempu-check', function() {
                const $row = $(this).closest('tr');
                const isChecked = $(this).is(':checked');
                const $decisionCell = $row.find('.decision-cell');

                if (isChecked) {
                    $row.removeClass('row-reject').addClass('row-ok');
                    $decisionCell.html(
                        '<span class="badge bg-success-subtle text-success fs-12 px-2 py-1"><i class="ri-check-line me-1"></i> Lolos (Release)</span>'
                        );
                } else {
                    $row.removeClass('row-ok').addClass('row-reject');
                    $decisionCell.html(
                        '<span class="badge bg-danger-subtle text-danger fs-12 px-2 py-1"><i class="ri-close-line me-1"></i> Reject (Workshop)</span>'
                        );
                }

                updateCounters();
            });

            // Klik baris untuk toggle checkbox (kecuali jika klik langsung checkbox atau link)
            $(document).on('click', '#tableKempu tbody tr', function(e) {
                if ($(e.target).is('input[type="checkbox"]')) return;
                const $chk = $(this).find('.kempu-check');
                $chk.prop('checked', !$chk.prop('checked')).trigger('change');
            });

            // Check All Button
            $('#btnCheckAll').on('click', function() {
                $('.kempu-check').prop('checked', true).trigger('change');
            });

            // Uncheck All Button
            $('#btnUncheckAll').on('click', function() {
                $('.kempu-check').prop('checked', false).trigger('change');
            });

            // Checkbox Header
            $('#checkHeader').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.kempu-check').prop('checked', isChecked).trigger('change');
            });

            // Quick Filter Table by ID Kempu
            $('#tableFilter').on('keyup', function() {
                const query = $(this).val().toLowerCase().trim();
                $('#tableKempu tbody tr').each(function() {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(query) > -1);
                });
            });

            // Konfirmasi Eksekusi Bulk Decision
            $('#btnConfirmBulk').on('click', function() {
                if (!currentNoSpb || currentItems.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Kosong',
                        text: 'Belum ada data kempu yang dimuat untuk dikonfirmasi.'
                    });
                    return;
                }

                const allIds = [];
                const checkedIds = [];

                $('.kempu-check').each(function() {
                    const id = $(this).val();
                    allIds.push(id);
                    if ($(this).is(':checked')) {
                        checkedIds.push(id);
                    }
                });

                const total = allIds.length;
                const okCount = checkedIds.length;
                const rejectCount = total - okCount;
                const notes = $('#bulkNotes').val().trim();

                let summaryHtml = `
                    <div class="text-start fs-14">
                        <p class="mb-2">Nomor SPB: <strong class="font-monospace text-primary">${currentNoSpb}</strong></p>
                        <p class="mb-2">Total Kempu: <strong>${total} unit</strong></p>
                        <ul class="list-unstyled mb-3">
                            <li class="mb-1 text-success"><i class="ri-check-line me-1 fw-bold"></i> Lolos (Release): <strong>${okCount} kempu</strong> (Status: 'QC PM Release')</li>
                            <li class="text-danger"><i class="ri-close-line me-1 fw-bold"></i> Reject (Workshop): <strong>${rejectCount} kempu</strong> (Kirim ke Workshop)</li>
                        </ul>
                        ${notes ? `<p class="text-muted fs-12 mb-0">Catatan: "${notes}"</p>` : ''}
                    </div>
                `;

                Swal.fire({
                    title: 'Konfirmasi Hasil QC PM Bulk?',
                    html: summaryHtml,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="ri-check-line me-1"></i> Ya, Simpan Keputusan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        executeBulkDecision(allIds, checkedIds, notes);
                    }
                });
            });

            // AJAX Eksekusi ke Server
            function executeBulkDecision(allIds, checkedIds, notes) {
                Swal.fire({
                    title: 'Menyimpan Hasil QC Bulk...',
                    text: 'Memproses ' + allIds.length + ' kempu...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('kempu.qc.pm.bulk.decision') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        no_spb: currentNoSpb,
                        all_ids: allIds,
                        checked_ids: checkedIds,
                        notes: notes
                    },
                    success: function(res) {
                        Swal.close();
                        if (res.status) {
                            playBeep('success');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Diproses!',
                                text: res.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                resetBulkForm();
                            });
                        } else {
                            playBeep('error');
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menyimpan',
                                text: res.message || 'Terjadi kesalahan sistem.'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        playBeep('error');
                        let msg = 'Gagal menyimpan keputusan QC Bulk.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
            }

            // Jalankan Scanner Kamera saat Halaman Terbuka
            startScanner();
        });
    </script>
@endsection
