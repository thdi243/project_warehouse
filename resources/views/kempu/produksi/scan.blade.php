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
                        <h4 class="mb-sm-0">Scanner {{ $card['title'] }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="#">Kempu</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('kempu.produksi.index') }}">Produksi</a></li>
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
                        <a href="{{ route('kempu.produksi.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Kembali ke Menu Produksi
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
                                        <i class="ri-camera-lens-line text-primary me-1"></i> Pemindai Kamera Produksi
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
                                    $canManualInput = $canManualInput ?? \App\Models\Kempu\MasterKempuModel::canManualInput();
                                @endphp
                                @if ($canManualInput)
                                    <label class="form-label fs-13 fw-semibold text-body mb-2 d-flex align-items-center justify-content-between">
                                        <span><i class="ri-keyboard-line text-muted me-1"></i> Masukkan ID Kempu Manual:</span>
                                        <span class="badge bg-info-subtle text-info fs-11"><i class="ri-shield-user-line me-1"></i> Otoritas Khusus Aktif</span>
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
                                @else
                                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-0 py-2 px-3">
                                        <i class="ri-lock-line fs-20 text-warning flex-shrink-0"></i>
                                        <div class="fs-12 text-muted">
                                            <strong class="text-body">Pengetikan Manual Terkunci:</strong> Operator wajib memindai kempu via kamera / barcode scanner. Pengetikan ID manual hanya diperuntukkan bagi Foreman / Leader / Supervisor.
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

    <!-- MODAL CONFIRM KEMPU -->
    <div class="modal fade" id="modalKempuConfirm" tabindex="-1" aria-labelledby="modalKempuConfirmLabel"
        aria-hidden="true" data-bs-backdrop="static">
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
                        <h5 class="modal-title fw-bold text-body fs-16" id="modalKempuConfirmLabel">
                            Konfirmasi {{ $card['title'] }}
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Flow Error Alert Box -->
                    <div id="alertFlowErrorBox" class="alert alert-warning mb-3" style="display:none;"></div>

                    <!-- Special Reused Alert for Scan 1 Filling -->
                    <div id="alertReusedNotice" class="alert alert-info mb-3" style="display:none;"></div>

                    <!-- Barcode Banner -->
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

                    @if ($card['key'] === 'prod-force')
                        <div
                            class="alert alert-danger py-2 px-3 mb-3 fs-12 d-flex align-items-start gap-2 border-danger-subtle bg-danger-subtle text-danger">
                            <i class="ri-alert-line fs-18 flex-shrink-0 mt-1"></i>
                            <div>
                                <strong>Mode Force Scan Produksi (Otoritas Khusus):</strong><br>
                                Fitur ini mengizinkan Anda menentukan status atau mengeksekusi tahapan alur kempu di
                                Produksi secara manual tanpa terhalang urutan alur normal atau jeda waktu cuci.
                            </div>
                        </div>

                        <!-- Dropdown Pilihan Keputusan Force Scan Produksi -->
                        <div class="mb-3">
                            <label for="modalForceTarget" class="form-label fs-12 fw-bold text-danger mb-1">
                                <i class="ri-git-branch-line me-1"></i> Pilih Alur / Target Keputusan Produksi:
                            </label>
                            <select class="form-select form-select-lg border-danger fw-semibold fs-14"
                                id="modalForceTarget">
                                <option value="PROD_TRANSFER_IN_WPM">&#x1F7E2; Transfer in from WPM (Paksa Terima dari WPM
                                    &rarr; Menuju QC Pre Cuci)</option>
                                <option value="PROD_CUCI_KEMPU">&#x1F535; Cuci Kempu Selesai (Paksa Selesai Cuci &rarr;
                                    Siap Filling)</option>
                                <option value="PROD_FILLING_KEMPU">&#x1F7E2; Filling Kempu (Scan 1) (Paksa Pengisian &rarr;
                                    Bypass Jeda Cuci)</option>
                                <option value="PROD_TRANSFER_OUT_WFG">&#x1F7E2; Transfer Out to WFG (Paksa Kirim ke Gudang
                                    Jadi WFG)</option>
                                <option value="PROD_REPRO_KEMPU">&#x1F504; Repro Kempu (Paksa Selesai Repro &rarr; Kirim ke
                                    Repair)</option>
                                <option value="PROD_TRANSFER_IN_WFG">&#x1F7E1; Transfer in from WFG (Paksa Terima
                                    Retur/Reject WFG)</option>
                                <option value="SCRAPPED">&#x26AB; Create BA Scrap (Paksa Afkir / Kempu Rusak Permanen)
                                </option>
                            </select>
                        </div>
                    @endif

                    @if ($card['key'] === 'scan-1-filling-kempu')
                        <!-- Field Nomor PO (Wajib) -->
                        <div class="mb-3">
                            <label for="modalInputNoPo" class="form-label fs-12 fw-bold text-body mb-1">
                                <i class="ri-file-list-3-line text-success me-1"></i> Nomor PO (Purchase Order): <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg fw-semibold font-monospace" id="modalInputNoPo"
                                placeholder="Contoh: PO-2026-00123" required autocomplete="off">
                            <div class="form-text fs-11 text-muted">Nomor PO wajib diisi saat proses pengisian muatan kempu.</div>
                        </div>

                        <!-- Upload 4 Foto Kempu -->
                        <div class="mb-3">
                            <label class="form-label fs-12 fw-bold text-body mb-2 d-flex align-items-center justify-content-between">
                                <span><i class="ri-camera-lens-line text-success me-1"></i> Dokumentasi Foto Kempu:</span>
                                <span class="text-muted fs-11 fw-normal">Maksimal 4 Foto</span>
                            </label>
                            <div class="row g-2">
                                @for ($i = 1; $i <= 4; $i++)
                                    <div class="col-6 col-sm-3">
                                        <!-- Hidden Inputs (Kamera langsung & Galeri) -->
                                        <input type="file" id="modalInputFoto{{ $i }}" class="d-none input-foto-kempu" data-index="{{ $i }}" accept="image/*" capture="environment">
                                        <input type="file" id="modalInputFotoGallery{{ $i }}" class="d-none input-foto-kempu" data-index="{{ $i }}" accept="image/*">

                                        <div class="border rounded-3 p-2 text-center position-relative photo-upload-box bg-light"
                                             id="photoBox{{ $i }}" data-index="{{ $i }}" style="min-height: 118px; cursor: pointer; overflow: hidden;">
                                            <div class="photo-placeholder d-flex flex-column align-items-center justify-content-center py-1" id="placeholderFoto{{ $i }}">
                                                <i class="ri-camera-fill fs-22 text-success mb-1"></i>
                                                <span class="fs-12 fw-semibold text-dark">Foto {{ $i }}</span>
                                                <span class="fs-10 text-muted mb-2"><i class="ri-camera-line me-1"></i>Kamera</span>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 btn-choose-gallery"
                                                        data-index="{{ $i }}" style="font-size: 10px; border-radius: 8px;">
                                                    <i class="ri-image-line me-1"></i>Galeri
                                                </button>
                                            </div>
                                            <img src="" id="previewFoto{{ $i }}" class="img-fluid rounded d-none" style="max-height: 100px; width: 100%; object-fit: cover;">
                                            <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-1 rounded-circle p-0 d-none btn-remove-photo"
                                                    data-index="{{ $i }}" style="line-height: 1; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; z-index: 5;"
                                                    title="Hapus Foto">
                                                <i class="ri-close-line fs-12"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <div class="form-text fs-11 text-muted mt-1">
                                <i class="ri-information-line text-info me-1"></i>Klik kotak foto untuk langsung membuka <strong>Kamera</strong>, atau klik tombol <strong>Galeri</strong> untuk memilih file.
                            </div>
                        </div>
                    @endif

                    <!-- Notes Input (Opsional) -->
                    <div class="mb-3">
                        <label for="modalInputNotes" class="form-label fs-12 fw-semibold text-body mb-1">
                            Catatan (Opsional):
                        </label>
                        <input type="text" class="form-control" id="modalInputNotes"
                            placeholder="Tuliskan keterangan jika ada..." autocomplete="off">
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2 border-top">
                        <button type="button"
                            class="btn btn-{{ $card['badge_color'] }} btn-lg w-100 py-3 fw-bold fs-15 shadow-sm"
                            id="btnModalConfirm">
                            <i
                                class="{{ $card['key'] === 'prod-force' ? 'ri-shield-flash-line' : 'ri-checkbox-circle-line' }} me-1"></i>
                            {{ $card['key'] === 'prod-force' ? 'Eksekusi Force Decision' : 'Konfirmasi ' . $card['title'] }}
                        </button>
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
            const CARD_KEY = "{{ $card['key'] }}";
            const CARD_TITLE = "{{ $card['title'] }}";

            // State file foto terkompresi (Slot 1 - 4)
            const selectedPhotos = { 1: null, 2: null, 3: null, 4: null };
            let activeCompressions = 0;

            // Fungsi kompresi gambar di browser (HTML5 Canvas)
            async function compressImage(file, maxDimension = 1600, quality = 0.8) {
                if (!file || !file.type.match(/image.*/)) return file;

                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = new Image();
                        img.onload = function() {
                            let width = img.width;
                            let height = img.height;

                            if (width > maxDimension || height > maxDimension) {
                                if (width > height) {
                                    height = Math.round((height * maxDimension) / width);
                                    width = maxDimension;
                                } else {
                                    width = Math.round((width * maxDimension) / height);
                                    height = maxDimension;
                                }
                            }

                            const canvas = document.createElement('canvas');
                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);

                            canvas.toBlob(
                                function(blob) {
                                    if (!blob) return resolve(file);

                                    // Jika masih > 1.8MB, kompres ulang dengan kualitas 0.6
                                    if (blob.size > 1.8 * 1024 * 1024 && quality > 0.5) {
                                        canvas.toBlob(function(secondBlob) {
                                            const resBlob = secondBlob || blob;
                                            const cleanName = (file.name || 'foto.jpg').replace(/\.[^/.]+$/, "") + ".jpg";
                                            resolve(new File([resBlob], cleanName, { type: 'image/jpeg', lastModified: Date.now() }));
                                        }, 'image/jpeg', 0.6);
                                        return;
                                    }

                                    const cleanName = (file.name || 'foto.jpg').replace(/\.[^/.]+$/, "") + ".jpg";
                                    const compressedFile = new File([blob], cleanName, {
                                        type: 'image/jpeg',
                                        lastModified: Date.now()
                                    });
                                    resolve(compressedFile);
                                },
                                'image/jpeg',
                                quality
                            );
                        };
                        img.onerror = () => resolve(file);
                        img.src = e.target.result;
                    };
                    reader.onerror = () => resolve(file);
                    reader.readAsDataURL(file);
                });
            }

            function updateSubmitButtonState() {
                if (activeCompressions > 0) {
                    $('#btnModalConfirm').prop('disabled', true).addClass('disabled').html(
                        '<i class="ri-loader-4-line ri-spin me-1"></i> Mengompres foto...'
                    );
                } else {
                    $('#btnModalConfirm').prop('disabled', false).removeClass('disabled').html(
                        CARD_KEY === 'prod-force' ?
                        '<i class="ri-shield-flash-line me-1"></i> Eksekusi Force Decision' :
                        `<i class="ri-checkbox-circle-line me-1"></i> Konfirmasi ${CARD_TITLE}`
                    );
                }
            }

            let qrScanner = null;
            let currentFacing = "environment";
            let availableCameras = [];
            let currentCameraIndex = 0;
            let isScannerActive = false;
            let currentKempu = null;

            const confirmModal = new bootstrap.Modal(document.getElementById('modalKempuConfirm'), {
                backdrop: 'static',
                keyboard: false
            });

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

                if (currentKempu && currentKempu.id_kempu === decodedText.trim().toUpperCase()) {
                    return;
                }

                // playBeep('success');
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
                    url: "{{ route('kempu.produksi.lookup') }}",
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
                            openConfirmModal(res.data);
                        } else {
                            // playBeep('error');
                            Swal.fire('Tidak Ditemukan', res.message || 'Kempu tidak ditemukan.',
                                'error').then(() => {
                                resumeScanner();
                            });
                        }
                    },
                    error: function(xhr) {
                        // playBeep('error');
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

            // Buka Modal Konfirmasi
            function openConfirmModal(k) {
                currentKempu = k;

                $('#modalKempuId').text(k.id_kempu);
                $('#modalKempuRfid').text(k.rfid || '-');
                $('#modalKempuLoc').text(k.current_location);
                $('#modalKempuStatus').text(k.current_status);
                $('#modalKempuReused').text(`${k.reused_count} / ${k.max_reused}x`);
                $('#modalInputNotes').val('');

                // Reset Alerts
                $('#alertFlowErrorBox').hide().empty();
                $('#alertReusedNotice').hide().empty();

                // Validasi Alur
                if (!k.is_flow_valid) {
                    // playBeep('error');
                    let iconClass = 'ri-alert-line text-danger';
                    let titleText = 'Peringatan Alur Kempu:';
                    if (CARD_KEY === 'scan-1-filling-kempu') {
                        iconClass = 'ri-time-line text-danger';
                        titleText = 'Syarat Waktu Cuci & Filling:';
                    } else if (CARD_KEY === 'cuci-kempu' && k.flow_error && k.flow_error.includes('Duplikat')) {
                        iconClass = 'ri-error-warning-line text-danger';
                        titleText = 'Duplikat Scan (Sudah Dicuci):';
                    }
                    $('#alertFlowErrorBox').html(`
                        <div class="d-flex align-items-start gap-2">
                            <i class="${iconClass} fs-20 mt-1 flex-shrink-0"></i>
                            <div>
                                <div class="fw-bold fs-13 text-danger mb-1">${titleText}</div>
                                <div class="fs-12 text-dark lh-base">${k.flow_error}</div>
                            </div>
                        </div>
                    `).removeClass('alert-warning').addClass('alert-danger').show();
                    $('#btnModalConfirm').prop('disabled', true).addClass('disabled');
                } else {
                    // playBeep('success');
                    $('#btnModalConfirm').prop('disabled', false).removeClass('disabled');

                    if (CARD_KEY === 'cuci-kempu') {
                        let targetReused = Math.min(21, (k.reused_count || 0) + 1);
                        let noteText =
                            `Kempu telah lolos <strong>Cek Incoming & Pre Cuci</strong>. Pada konfirmasi Cuci Kempu ini, <strong>Siklus Reused otomatis bertambah (+1)</strong> menjadi <strong>${targetReused}/21x</strong>.`;
                        if (k.current_status && k.current_status.toUpperCase().includes('CUCI')) {
                            noteText =
                                `Kempu dicuci ulang karena melewati batas waktu (> H+3). Siklus Reused akan bertambah (+1) menjadi <strong>${targetReused}/21x</strong>.`;
                        }
                        $('#alertReusedNotice').html(`
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-water-flash-line fs-20 text-info"></i>
                                <div>${noteText}</div>
                            </div>
                        `).removeClass('alert-danger alert-warning alert-success').addClass('alert-info').show();
                    } else if (CARD_KEY === 'scan-1-filling-kempu') {
                        $('#modalInputNoPo').val(k.no_po || '');
                        for (let i = 1; i <= 4; i++) {
                            selectedPhotos[i] = null;
                            $('#modalInputFoto' + i).val('');
                            $('#modalInputFotoGallery' + i).val('');
                            const existingPhoto = (k.cycle_photos && k.cycle_photos['foto_' + i]) ? k.cycle_photos['foto_' + i] : null;
                            if (existingPhoto) {
                                $('#previewFoto' + i).attr('src', existingPhoto).removeClass('d-none');
                                $('#placeholderFoto' + i).addClass('d-none');
                                $(`.btn-remove-photo[data-index="${i}"]`).removeClass('d-none');
                            } else {
                                $('#previewFoto' + i).attr('src', '').addClass('d-none');
                                $(`#placeholderFoto${i}`).html(`
                                    <i class="ri-camera-fill fs-22 text-success mb-1"></i>
                                    <span class="fs-12 fw-semibold text-dark">Foto ${i}</span>
                                    <span class="fs-10 text-muted mb-2"><i class="ri-camera-line me-1"></i>Kamera</span>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 btn-choose-gallery"
                                            data-index="${i}" style="font-size: 10px; border-radius: 8px;">
                                        <i class="ri-image-line me-1"></i>Galeri
                                    </button>
                                `).removeClass('d-none');
                                $(`.btn-remove-photo[data-index="${i}"]`).addClass('d-none');
                            }
                        }

                        let cuciBadge = '';
                        if (k.cuci_info && k.cuci_info.cuci_date && k.cuci_info.cuci_date !== '-') {
                            cuciBadge = `
                                <div class="p-2 mb-2 rounded bg-soft-success text-success border border-success-subtle fs-12">
                                    <i class="ri-time-line me-1"></i> Waktu Cuci: <strong>${k.cuci_info.cuci_date}</strong> 
                                    <span class="badge bg-success ms-1">${k.cuci_info.h_label}</span>
                                    <span class="d-block text-muted mt-1 fs-11">Syarat waktu terpenuhi (H+1 s/d H+3). Batas akhir pengisian: <strong>${k.cuci_info.max_date}</strong>.</span>
                                </div>
                            `;
                        }
                        $('#alertReusedNotice').html(`
                            <div>
                                ${cuciBadge}
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ri-checkbox-circle-line fs-18 text-success flex-shrink-0"></i>
                                    <div>Kempu siap untuk proses <strong>Filling</strong>. Siklus pemakaian kempu: <strong>${k.reused_count}/21 Reused</strong>. Silakan isi Nomor PO dan lampirkan foto dokumentasi kempu.</div>
                                </div>
                            </div>
                        `).removeClass('alert-danger alert-warning').addClass('alert-success').show();
                    } else if (CARD_KEY === 'transfer-in-from-wpm') {
                        $('#alertReusedNotice').html(`
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-information-line fs-18"></i>
                                <div>Setelah konfirmasi Transfer In, kempu akan diarahkan ke pemeriksaan <strong>Cek Incoming & Pre Cuci</strong> oleh QC.</div>
                            </div>
                        `).removeClass('alert-danger alert-warning').addClass('alert-info').show();
                    } else if (CARD_KEY === 'transfer-out-to-wfg') {
                        $('#alertReusedNotice').html(`
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-truck-line fs-18"></i>
                                <div>Kempu telah lolos <strong>QC After Filling</strong> dan siap dikirim ke Warehouse Finished Goods (WFG).</div>
                            </div>
                        `).removeClass('alert-danger alert-warning').addClass('alert-info').show();
                    } else if (CARD_KEY === 'create-ba-scrap') {
                        $('#alertReusedNotice').html(`
                            <div class="d-flex align-items-center gap-2 text-danger">
                                <i class="ri-delete-bin-line fs-18"></i>
                                <div>Kempu ini akan resmi dibuatkan Berita Acara Scrap dan dialihkan ke status <b>NONAKTIF</b>.</div>
                            </div>
                        `).removeClass('alert-info').addClass('alert-danger').show();
                    }
                }

                if (CARD_KEY === 'prod-force') {
                    $('#btnModalConfirm').html('<i class="ri-shield-flash-line me-1"></i> Eksekusi Force Decision');
                } else {
                    $('#btnModalConfirm').html(
                        `<i class="ri-checkbox-circle-line me-1"></i> Konfirmasi ${CARD_TITLE}`);
                }
                confirmModal.show();
            }

            // Photo Upload Interaction Handlers
            $(document).on('click', '.photo-upload-box', function(e) {
                if ($(e.target).closest('.btn-remove-photo').length) return;
                if ($(e.target).closest('.btn-choose-gallery').length) return;
                if ($(e.target).is('input[type="file"]')) return;

                const idx = $(this).data('index');
                // Klik kotak langsung membuka kamera
                $(`#modalInputFoto${idx}`).trigger('click');
            });

            // Klik tombol galeri membuka file picker tanpa capture kamera langsung
            $(document).on('click', '.btn-choose-gallery', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const idx = $(this).data('index');
                $(`#modalInputFotoGallery${idx}`).trigger('click');
            });

            $(document).on('change', '.input-foto-kempu', async function() {
                const input = this;
                const idx = $(input).data('index');
                if (input.files && input.files[0]) {
                    const rawFile = input.files[0];

                    if (input.id === 'modalInputFoto' + idx) {
                        $('#modalInputFotoGallery' + idx).val('');
                    } else {
                        $('#modalInputFoto' + idx).val('');
                    }

                    activeCompressions++;
                    updateSubmitButtonState();

                    // Tampilkan indikator proses kompresi
                    $(`#placeholderFoto${idx}`).html(`
                        <div class="spinner-border spinner-border-sm text-success mb-1" role="status"></div>
                        <span class="fs-11 text-muted d-block">Mengompres...</span>
                    `).removeClass('d-none');

                    try {
                        const compressedFile = await compressImage(rawFile, 1600, 0.8);
                        selectedPhotos[idx] = compressedFile;

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $(`#previewFoto${idx}`).attr('src', e.target.result).removeClass('d-none');
                            $(`#placeholderFoto${idx}`).addClass('d-none');
                            $(`.btn-remove-photo[data-index="${idx}"]`).removeClass('d-none');
                        };
                        reader.readAsDataURL(compressedFile);
                    } catch (err) {
                        console.error('Gagal kompresi foto:', err);
                        selectedPhotos[idx] = rawFile;
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $(`#previewFoto${idx}`).attr('src', e.target.result).removeClass('d-none');
                            $(`#placeholderFoto${idx}`).addClass('d-none');
                            $(`.btn-remove-photo[data-index="${idx}"]`).removeClass('d-none');
                        };
                        reader.readAsDataURL(rawFile);
                    } finally {
                        activeCompressions = Math.max(0, activeCompressions - 1);
                        updateSubmitButtonState();
                    }
                }
            });

            $(document).on('click', '.btn-remove-photo', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const idx = $(this).data('index');
                selectedPhotos[idx] = null;
                $(`#modalInputFoto${idx}`).val('');
                $(`#modalInputFotoGallery${idx}`).val('');
                $(`#previewFoto${idx}`).attr('src', '').addClass('d-none');
                $(`#placeholderFoto${idx}`).html(`
                    <i class="ri-camera-fill fs-22 text-success mb-1"></i>
                    <span class="fs-12 fw-semibold text-dark">Foto ${idx}</span>
                    <span class="fs-10 text-muted mb-2"><i class="ri-camera-line me-1"></i>Kamera</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 btn-choose-gallery"
                            data-index="${idx}" style="font-size: 10px; border-radius: 8px;">
                        <i class="ri-image-line me-1"></i>Galeri
                    </button>
                `).removeClass('d-none');
                $(this).addClass('d-none');
            });

            // Reset saat modal ditutup
            $('#modalKempuConfirm').on('hidden.bs.modal', function() {
                resumeScanner();
                $('#inputManualId').val('').focus();
            });

            // Tombol Confirm Action di Dalam Modal
            $('#btnModalConfirm').on('click', function() {
                if (!currentKempu) return;

                if (activeCompressions > 0) {
                    Swal.fire('Mohon Tunggu', 'Foto masih dalam proses kompresi otomatis...', 'info');
                    return;
                }

                if (CARD_KEY === 'scan-1-filling-kempu') {
                    const noPoVal = $('#modalInputNoPo').val().trim();
                    if (!noPoVal) {
                        Swal.fire('Validasi Gagal', 'Nomor PO (no_po) wajib diisi untuk proses Filling Kempu.', 'warning');
                        $('#modalInputNoPo').focus();
                        return;
                    }
                }

                const btn = $(this);
                btn.prop('disabled', true).html(
                    '<i class="ri-loader-4-line ri-spin me-1"></i> Memproses...');

                const formData = new FormData();
                formData.append('_token', "{{ csrf_token() }}");
                formData.append('id_kempu', currentKempu.id_kempu);
                formData.append('card_key', CARD_KEY);
                formData.append('notes', $('#modalInputNotes').val().trim());
                formData.append('is_manual', (currentKempu && currentKempu.is_manual) ? 1 : 0);

                if (CARD_KEY === 'prod-force') {
                    formData.append('force_target', $('#modalForceTarget').val());
                }

                if (CARD_KEY === 'scan-1-filling-kempu') {
                    formData.append('no_po', $('#modalInputNoPo').val().trim());
                    for (let i = 1; i <= 4; i++) {
                        if (selectedPhotos[i]) {
                            formData.append('foto_' + i, selectedPhotos[i]);
                        }
                    }
                }

                $.ajax({
                    url: "{{ route('kempu.produksi.confirm') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        btn.prop('disabled', false).html(
                            CARD_KEY === 'prod-force' ?
                            '<i class="ri-shield-flash-line me-1"></i> Eksekusi Force Decision' :
                            `<i class="ri-checkbox-circle-line me-1"></i> Konfirmasi ${CARD_TITLE}`
                        );
                        if (res.status) {
                            confirmModal.hide();
                            // playBeep('confirm');

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: res.message,
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });
                        } else {
                            // playBeep('error');
                            Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(
                            `<i class="ri-checkbox-circle-line me-1"></i> Konfirmasi ${CARD_TITLE}`
                        );
                        // playBeep('error');
                        let msg = 'Terjadi kesalahan server saat memproses konfirmasi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal', msg, 'error');
                    }
                });
            });

            // Manual Input Triggers
            $('#btnLookupManual').on('click', function() {
                const val = $('#inputManualId').val().trim();
                if (!val) {
                    Swal.fire('Peringatan', 'Silakan masukkan ID Kempu atau Scan Barcode terlebih dahulu.',
                        'warning');
                    $('#inputManualId').focus();
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

            // Inisialisasi awal scanner
            startScanner();
        });
    </script>
@endsection
