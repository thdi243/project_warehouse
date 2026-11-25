@extends('layouts.app')

@section('title', '| SMU Area')

@php
    $canPengawasSmu =
        auth()->user()->can('permission', 'vms-pengawas-smu') ||
        auth()->user()->can('permission', 'vms-admin-smu') ||
        auth()->user()->hasRole('super-admin');
    $canAdminSmu = auth()->user()->can('permission', 'vms-admin-smu') || auth()->user()->hasRole('super-admin');
@endphp

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">SMU Area Queue</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">SMU</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                            <h4 class="card-title mb-0 flex-grow-1"><i
                                    class="ri-database-2-line me-2 align-middle text-warning"></i>Antrian Data SMU Area</h4>
                            <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                @can('permission', 'vms-admin-wfg')
                                    <button type="button" class="btn btn-warning btn-sm fw-semibold shadow-sm"
                                        id="btnFollowUpTimbangan">
                                        <i class="ri-alarm-warning-line me-1 align-middle"></i> Follow Up Timbangan
                                    </button>
                                @endcan
                                <div style="width: 250px;">
                                    <input type="text" class="form-control form-control-sm" id="search_table"
                                        placeholder="Cari No. Polisi / Vendor...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info border-0 d-flex align-items-center py-2 px-3 mb-3 fs-13"
                                role="alert">
                                <i class="ri-information-line fs-17 me-2 text-info"></i>
                                <div>
                                    Klik pada <strong>No. Polisi</strong> untuk melihat rincian lengkap kendaraan (waktu,
                                    item, no SPB, status, durasi, dll).
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="smuTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 120px;">No. Antrian</th>
                                            <th>Waktu</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            @if ($canAdminSmu)
                                                <th>Item</th>
                                                <th>No. SPB / Qty</th>
                                                <th>Status</th>
                                                <th>Durasi Aktivitas</th>
                                            @endif
                                            <th class="text-center" style="width: 240px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="{{ $canAdminSmu ? 9 : 5 }}" class="text-center py-4 text-muted">
                                                Loading data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Kendaraan & Aktivitas (Dapat diakses Pengawas & Admin dengan klik No. Polisi) --}}
    <div class="modal fade" id="modalDetailTruck" tabindex="-1" aria-labelledby="modalDetailTruckLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning fs-14 px-3 py-2 fw-semibold text-dark" id="dtNopol">-</span>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalDetailTruckLabel">Detail Rincian Kendaraan
                            (SMU)</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light border h-100">
                                <h6 class="text-muted text-uppercase fs-11 fw-bold mb-2"><i
                                        class="ri-truck-line me-1"></i>Kendaraan & Vendor</h6>
                                <table class="table table-sm table-borderless mb-0 fs-13">
                                    <tr>
                                        <td class="text-muted ps-0" style="width: 110px;">Vendor:</td>
                                        <td class="fw-semibold text-dark" id="dtVendor">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-0">Driver:</td>
                                        <td class="text-dark" id="dtDriver">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-0">Area / Jenis:</td>
                                        <td class="text-dark" id="dtJenis">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light border h-100">
                                <h6 class="text-muted text-uppercase fs-11 fw-bold mb-2"><i
                                        class="ri-file-list-3-line me-1"></i>Dokumen & Item</h6>
                                <table class="table table-sm table-borderless mb-0 fs-13">
                                    <tr>
                                        <td class="text-muted ps-0" style="width: 110px;">Nama Item:</td>
                                        <td class="fw-semibold text-primary" id="dtItem">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-0">No. SPB:</td>
                                        <td class="fw-semibold text-dark" id="dtNoSpb">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted ps-0">Qty SPB:</td>
                                        <td class="fw-bold text-success" id="dtQtySpb">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded border text-center bg-white shadow-sm">
                                <span class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Nomor Antrian</span>
                                <div id="dtAntrianBadge">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded border text-center bg-white shadow-sm">
                                <span class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Status Aktivitas</span>
                                <div id="dtStatusBadge">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded border text-center bg-white shadow-sm">
                                <span class="text-muted fs-11 text-uppercase fw-bold d-block mb-1">Status QC</span>
                                <div id="dtQcBadge"><span class="badge bg-soft-secondary text-secondary">N/A</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="card border mb-0">
                        <div class="card-header bg-soft-light py-2 px-3 border-bottom">
                            <h6 class="mb-0 fs-12 fw-bold text-muted text-uppercase"><i
                                    class="ri-time-line me-1"></i>Timeline Waktu & Durasi</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3 fs-13">
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-muted small">Jam Tiba</div>
                                    <div class="fw-bold text-dark mt-1" id="dtTimeArrival">-</div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-muted small">Ambil Antrian</div>
                                    <div class="fw-bold text-warning mt-1" id="dtTimeQueue">-</div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-muted small">Mulai Bongkar/Muat</div>
                                    <div class="fw-bold text-info mt-1" id="dtTimeStart">-</div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-muted small">Selesai</div>
                                    <div class="fw-bold text-success mt-1" id="dtTimeFinish">-</div>
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-12 text-muted fw-semibold">Durasi Saat Ini:</span>
                                    <span class="fs-13 fw-bold" id="dtLiveDuration">-</span>
                                </div>
                                <div id="dtDurationBreakdown" class="small text-muted"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const canPengawasSmu = {{ $canPengawasSmu ? 'true' : 'false' }};
            const canAdminSmu = {{ $canAdminSmu ? 'true' : 'false' }};
            const totalCols = canAdminSmu ? 9 : 5;

            function formatDuration(diffSeconds) {
                if (isNaN(diffSeconds) || diffSeconds < 0) return '0d';
                const hours = Math.floor(diffSeconds / 3600);
                const minutes = Math.floor((diffSeconds % 3600) / 60);
                const seconds = diffSeconds % 60;
                let res = '';
                if (hours > 0) res += hours + 'j ';
                if (minutes > 0 || hours > 0) res += minutes + 'm ';
                res += seconds + 'd';
                return res;
            }

            // Setup real-time timers
            function updateTimers() {
                $('.timer').each(function() {
                    const startTimestamp = parseInt($(this).data('start'));
                    if (!startTimestamp || isNaN(startTimestamp)) return;
                    const nowTimestamp = Math.floor(Date.now() / 1000);
                    const diffSeconds = Math.max(0, nowTimestamp - startTimestamp);

                    const hours = Math.floor(diffSeconds / 3600);
                    const minutes = Math.floor((diffSeconds % 3600) / 60);
                    const seconds = diffSeconds % 60;

                    let timeStr = '';
                    if (hours > 0) {
                        timeStr += hours + 'j ';
                    }
                    timeStr += minutes + 'm ' + seconds + 'd';

                    $(this).text(timeStr);
                });
            }

            let allSmuData = [];
            let searchQuery = '';

            function renderSmuTable() {
                let filtered = allSmuData;
                if (searchQuery) {
                    filtered = allSmuData.filter(function(tx) {
                        const noPol = (tx.no_pol || '').toLowerCase();
                        const vendor = (tx.vendor || '').toLowerCase();
                        return noPol.includes(searchQuery) || vendor.includes(searchQuery);
                    });
                }

                let html = '';
                if (filtered.length === 0) {
                    html = `<tr>
                        <td colspan="${totalCols}" class="text-center py-4 text-muted">Tidak ada kendaraan yang sesuai pencarian.</td>
                    </tr>`;
                } else {
                    filtered.forEach(function(tx) {
                        const jenis = (tx.jenis || '').toLowerCase().trim();
                        const isMuat = jenis === 'slipsheet' || jenis === 'curah';
                        const actionLabel = isMuat ? 'Muat' : 'Bongkar';
                        const isProcess = tx.unloading_status === 'process';

                        let antrianBadge = '';
                        if (tx.no_antrian) {
                            const overnightTag = tx.is_overnight ?
                                '<span class="badge bg-danger ms-1" title="Truk antrian dari hari sebelumnya (Prioritas)"><i class="ri-history-line me-1"></i>Kemarin</span>' :
                                '';
                            antrianBadge =
                                `<span class="badge bg-soft-success text-success fs-13 px-3 py-2">#${tx.no_antrian}</span>${overnightTag}`;
                        } else {
                            if (canAdminSmu) {
                                antrianBadge = `<button type="button" class="btn btn-sm btn-outline-warning btn-get-queue" data-id="${tx.id}" data-nopol="${tx.no_pol}">
                                    <i class="ri-ticket-2-line me-1 align-middle"></i>Ambil Antrian
                                </button>`;
                            } else {
                                antrianBadge =
                                    `<span class="badge bg-soft-secondary text-secondary">Belum Antri</span>`;
                            }
                        }

                        let statusBadge = '';
                        if (!tx.no_antrian) {
                            statusBadge =
                                `<span class="badge bg-soft-secondary text-secondary"><i class="ri-pause-circle-line me-1 align-middle"></i>Menunggu Antrian</span>`;
                        } else if (!isProcess) {
                            statusBadge =
                                `<span class="badge bg-soft-warning text-warning"><i class="ri-time-line me-1 align-middle"></i>Antrian #${tx.no_antrian}</span>`;
                        } else {
                            statusBadge =
                                `<span class="badge bg-soft-info text-info"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Proses ${actionLabel}</span>`;
                        }

                        let actionBtn = '';
                        const canOperateSmu = canPengawasSmu || canAdminSmu;
                        const hasQueue = tx.no_antrian ? true : false;
                        const waTitle = hasQueue ?
                            'Kirim Notifikasi Panggilan WhatsApp Masuk Dock ke Nomor Pribadi Supir' :
                            'Kirim Pengingat WhatsApp Segera Serahkan Dokumen ke Admin';
                        const waBtn = `<button type="button" class="btn btn-sm btn-push-wa text-white" 
                            style="background-color: #25D366; border-color: #21ba5b;"
                            data-id="${tx.id}" 
                            data-nopol="${tx.no_pol}"
                            data-driver="${tx.nama_driver || ''}"
                            data-phone="${tx.no_hp_driver || ''}"
                            data-has-queue="${hasQueue ? '1' : '0'}"
                            data-antrian="${tx.no_antrian || ''}"
                            data-area="SMU"
                            title="${waTitle}">
                            <i class="ri-whatsapp-line align-middle"></i>
                        </button>`;

                        if (!tx.no_antrian) {
                            // Belum ambil antrian: Pengawas / Admin SMU dapat langsung Mulai atau Batal jika gajadi
                            if (canOperateSmu) {
                                actionBtn = `<div class="d-flex gap-1 justify-content-center">
                                    ${waBtn}
                                    <button type="button" class="btn btn-sm btn-primary btn-start-smu" 
                                        data-id="${tx.id}" 
                                        data-nopol="${tx.no_pol}"
                                        data-action="Mulai ${actionLabel}">
                                        <i class="ri-play-circle-line me-1 align-middle"></i> Mulai ${actionLabel}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-cancel-loading" 
                                        data-id="${tx.id}" 
                                        data-nopol="${tx.no_pol}"
                                        data-action="Batal ${actionLabel}"
                                        title="Batal ${actionLabel}">
                                        <i class="ri-close-circle-line me-1 align-middle"></i> Batal ${actionLabel}
                                    </button>
                                </div>`;
                            } else {
                                actionBtn = `<span class="text-muted small">-</span>`;
                            }
                        } else if (!isProcess) {
                            let cancelQueueBtn = '';
                            if (canAdminSmu) {
                                cancelQueueBtn = `<button type="button" class="btn btn-sm btn-soft-warning btn-cancel-queue" 
                                    data-id="${tx.id}" 
                                    data-nopol="${tx.no_pol}"
                                    data-antrian="${tx.no_antrian}"
                                    title="Batalkan Nomor Antrian (Kembali ke Menunggu Antrian)">
                                    <i class="ri-refresh-line me-1 align-middle"></i> Batal Antrian
                                </button>`;
                            }

                            if (canOperateSmu) {
                                actionBtn = `<div class="d-flex gap-1 justify-content-center">
                                    ${waBtn}
                                    <button type="button" class="btn btn-sm btn-primary btn-start-smu" 
                                        data-id="${tx.id}" 
                                        data-nopol="${tx.no_pol}"
                                        data-action="Mulai ${actionLabel}">
                                        <i class="ri-play-circle-line me-1 align-middle"></i> Mulai ${actionLabel}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-cancel-loading" 
                                        data-id="${tx.id}" 
                                        data-nopol="${tx.no_pol}"
                                        data-action="Batal ${actionLabel}"
                                        title="Batal ${actionLabel}">
                                        <i class="ri-close-circle-line me-1 align-middle"></i> Batal ${actionLabel}
                                    </button>
                                    ${cancelQueueBtn}
                                </div>`;
                            } else {
                                actionBtn = cancelQueueBtn ||
                                    `<span class="text-muted small">Menunggu Mulai</span>`;
                            }
                        } else {
                            // Sedang proses: pengawas / admin dapat menyelesaikan atau membatalkan proses
                            if (canOperateSmu) {
                                actionBtn = `<div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-warning btn-complete-smu" 
                                        data-id="${tx.id}" 
                                        data-nopol="${tx.no_pol}"
                                        data-action="Selesai ${actionLabel}">
                                        <i class="ri-checkbox-circle-line me-1 align-middle"></i> Selesai
                                    </button>
                                    <button type="button" class="btn btn-sm btn-soft-danger btn-cancel-loading" 
                                        data-id="${tx.id}" 
                                        data-nopol="${tx.no_pol}"
                                        data-action="Batal ${actionLabel}"
                                        title="Batal ${actionLabel}">
                                        <i class="ri-close-circle-line me-1 align-middle"></i> Batal ${actionLabel}
                                    </button>
                                </div>`;
                            } else {
                                actionBtn =
                                    `<span class="badge bg-soft-info text-info"><i class="ri-loader-4-line ri-spin me-1"></i>Sedang ${actionLabel}</span>`;
                            }
                        }

                        // Timeline breakdown
                        let timelineHtml =
                            `<div class="d-flex flex-column gap-1">
                            <span class="fs-12 text-muted">Tiba: <strong class="text-dark">${tx.arrival_time}</strong></span>`;
                        if (tx.queue_taken_time) {
                            timelineHtml +=
                                `<span class="fs-12 text-muted">Antri: <strong class="text-warning">${tx.queue_taken_time}</strong></span>`;
                        }
                        if (tx.start_loading_time) {
                            timelineHtml +=
                                `<span class="fs-12 text-muted">Mulai: <strong class="text-info">${tx.start_loading_time}</strong></span>`;
                        }
                        timelineHtml += `</div>`;

                        // Phase Duration breakdown
                        let durasiHtml = '';
                        if (!tx.no_antrian) {
                            durasiHtml = `
                                <div>
                                    <span class="timer badge bg-soft-secondary text-secondary fs-12 px-2 py-1" data-start="${tx.arrival_timestamp}">
                                        0m 0d
                                    </span>
                                    <div style="font-size: 10.5px;" class="text-muted mt-1"><i class="ri-hourglass-line me-1"></i>Tunggu Antri</div>
                                </div>
                            `;
                        } else if (!isProcess) {
                            const waitToQueueSec = tx.queue_taken_timestamp ? Math.max(0, tx
                                .queue_taken_timestamp - tx.arrival_timestamp) : 0;
                            const startTimerFrom = tx.queue_taken_timestamp || tx.arrival_timestamp;
                            durasiHtml = `
                                <div>
                                    <span class="timer badge bg-soft-warning text-warning fs-12 px-2 py-1" data-start="${startTimerFrom}">
                                        0m 0d
                                    </span>
                                    <div style="font-size: 10.5px;" class="text-warning fw-medium mt-1"><i class="ri-time-line me-1"></i>Durasi Antri</div>
                                    ${waitToQueueSec > 0 ? `<div style="font-size: 10px;" class="text-muted">Tunggu Antri: ${formatDuration(waitToQueueSec)}</div>` : ''}
                                </div>
                            `;
                        } else {
                            const antriDurationSec = (tx.start_loading_timestamp && tx
                                    .queue_taken_timestamp) ?
                                Math.max(0, tx.start_loading_timestamp - tx.queue_taken_timestamp) :
                                (tx.start_loading_timestamp ? Math.max(0, tx.start_loading_timestamp - tx
                                    .arrival_timestamp) : 0);
                            const startProcessFrom = tx.start_loading_timestamp || tx.arrival_timestamp;
                            durasiHtml = `
                                <div>
                                    <span class="timer badge bg-soft-info text-info fs-12 px-2 py-1" data-start="${startProcessFrom}">
                                        0m 0d
                                    </span>
                                    <div style="font-size: 10.5px;" class="text-info fw-medium mt-1"><i class="ri-loader-4-line ri-spin me-1"></i>Durasi ${actionLabel}</div>
                                    ${antriDurationSec > 0 ? `<div style="font-size: 10px;" class="text-muted">Durasi Antri: ${formatDuration(antriDurationSec)}</div>` : ''}
                                </div>
                            `;
                        }

                        let detailsHtml = '';
                        if (canAdminSmu) {
                            detailsHtml = `
                                <td>
                                    <strong>${tx.item_name}</strong><br>
                                    <small class="text-muted">${tx.jenis}</small>
                                </td>
                                <td>
                                    <strong>${tx.no_spb}</strong><br>
                                    <small class="text-muted">${tx.qty_spb}</small>
                                </td>
                                <td>${statusBadge}</td>
                                <td>${durasiHtml}</td>
                            `;
                        }

                        html += `<tr id="row-${tx.id}">
                            <td class="text-center">${antrianBadge}</td>
                            <td>${timelineHtml}</td>
                            <td>
                                <span class="badge bg-soft-primary text-primary fs-12 btn-detail-truck" 
                                      data-id="${tx.id}" 
                                      style="cursor: pointer;" 
                                      title="Klik untuk melihat rincian detail kendaraan & aktivitas">
                                    <i class="ri-truck-line me-1"></i><strong>${tx.no_pol}</strong>
                                </span>
                            </td>
                            <td>
                                <strong>${tx.vendor || '-'}</strong><br>
                                <small class="text-muted">Driver: ${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                            </td>
                            ${detailsHtml}
                            <td class="text-center">
                                ${actionBtn}
                            </td>
                        </tr>`;
                    });
                }
                $('#smuTable tbody').html(html);
                updateTimers();
            }

            const handledFollowUps = new Set();

            function playFollowUpNotificationSound() {
                try {
                    const audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, audioCtx.currentTime);
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.15);
                    gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.5);
                } catch (e) {}
            }

            function triggerFollowUpAlert(data) {
                console.log('🔥 FOLLOW UP ALERT TRIGGERED:', data);
                const items = Array.isArray(data) ? data : [data];
                const unhandled = items.filter(item => {
                    const alertKey = (item.transaction_id || item.id) + '_' + (item.time || item
                        .follow_up_time || item.follow_up_timestamp || '');
                    return !handledFollowUps.has(alertKey);
                });

                if (unhandled.length === 0) {
                    return;
                }

                unhandled.forEach(item => {
                    const alertKey = (item.transaction_id || item.id) + '_' + (item.time || item
                        .follow_up_time || item.follow_up_timestamp || '');
                    handledFollowUps.add(alertKey);
                });

                console.log('🔔 Playing notification sound...');
                playFollowUpNotificationSound();

                const cardsHtml = unhandled.map(item => `
                    <div class="card bg-light border-0 mb-2 p-3">
                        <p class="mb-1"><strong>No. Polisi:</strong> <span class="badge bg-primary fs-13">${item.no_pol}</span></p>
                        ${item.no_spb ? `<p class="mb-1"><strong>No. SPB:</strong> ${item.no_spb}</p>` : ''}
                        ${item.notes ? `<p class="mb-1 text-danger"><strong>Pesan:</strong> "${item.notes}"</p>` : ''}
                        <p class="mb-0 text-muted small"><i class="ri-time-line me-1"></i>Waktu: ${item.time || item.follow_up_time || '-'}</p>
                    </div>
                `).join('');

                const countText = unhandled.length > 1 ? `Ada ${unhandled.length} kendaraan membutuhkan` :
                    'Kendaraan berikut membutuhkan';

                Swal.fire({
                    title: '<span class="text-danger fw-bold"><i class="ri-alarm-warning-line me-1"></i> FOLLOW UP TIMBANGAN!</span>',
                    html: `
                        <div class="text-start">
                            <div class="alert alert-danger border-0 mb-3 py-2 px-3">
                                <strong>Peringatan dari Timbangan:</strong> ${countText} konfirmasi / tindak lanjut segera di area SMU!
                            </div>
                            ${cardsHtml}
                            <p class="text-muted small mb-0">Truk dilaporkan sudah berada di timbangan. Mohon segera selesaikan konfirmasi di area SMU.</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: '<i class="ri-check-line me-1"></i> Saya Mengerti, Proses Sekarang',
                    confirmButtonColor: '#3085d6',
                    allowOutsideClick: false
                });
            }

            // AJAX Data Loader
            function loadSmuData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.smu.data') }}",
                    type: 'GET',
                    success: function(response) {
                        allSmuData = response.queue || [];
                        renderSmuTable();

                        // Check for follow up alerts (always pops up on page load/reload, suppressed during active polling once acknowledged)
                        if (allSmuData.length > 0) {
                            const followUps = allSmuData.filter(tx => {
                                return (tx.follow_up_timestamp || tx.follow_up_time) &&
                                    (tx.follow_up_target === 'SMU' || tx.follow_up_target ===
                                        'ALL');
                            }).map(tx => ({
                                id: tx.id,
                                transaction_id: tx.id,
                                no_pol: tx.no_pol,
                                no_spb: tx.no_spb,
                                notes: tx.follow_up_notes,
                                follow_up_time: tx.follow_up_time,
                                time: tx.follow_up_time,
                                follow_up_timestamp: tx.follow_up_timestamp
                            }));

                            if (followUps.length > 0) {
                                triggerFollowUpAlert(followUps);
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Gagal mengambil data SMU:', xhr);
                    }
                });
            }

            // Handle search
            $('#search_table').on('keyup', function() {
                searchQuery = $(this).val().toLowerCase();
                renderSmuTable();
            });

            // Initial load
            loadSmuData();

            // Run timers every second & polling every 15s
            setInterval(updateTimers, 1000);
            setInterval(loadSmuData, 15000);

            // Real-time Event Listener with Laravel Echo
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates on Echo channel in SMU...');
                    window.Echo.channel('vehicle-tracking')
                        .subscribed(() => {
                            console.log('✅ Subscribed successfully to vehicle-tracking channel in SMU');
                        })
                        .error((err) => {
                            console.error('❌ Echo connection error on vehicle-tracking channel:', err);
                        })
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in SMU:', payload);
                            if (payload.type === 'follow_up' && (payload.target_area === 'SMU' || payload
                                    .target_area === 'ALL' || payload.target_sloc === 'SMU')) {
                                triggerFollowUpAlert(payload);
                            } else {
                                if (window.toastr) {
                                    toastr.info(payload.message, 'Update Lokasi Truk');
                                }
                            }
                            loadSmuData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // Start SMU loading/unloading
            $(document).on('click', '.btn-start-smu', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const action = $(this).data('action') || 'Mulai Proses';

                Swal.fire({
                    title: `${action}?`,
                    text: `Konfirmasi untuk ${action.toLowerCase()} untuk truk ${nopol} di SMU?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3577f1',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Mulai!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/smu/start-loading') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Dimulai!', response.message, 'success');
                                loadSmuData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal memulai proses.', 'error');
                            }
                        });
                    }
                });
            });

            // Complete SMU action
            $(document).on('click', '.btn-complete-smu', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const action = $(this).data('action') || 'Selesai';

                Swal.fire({
                    title: `${action}?`,
                    text: `Apakah truk ${nopol} telah menyelesaikan proses di SMU?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f1b44c',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Selesai!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/smu/complete') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Selesai!', response.message, 'success').then(
                                    () => {
                                        $(`#row-${id}`).fadeOut(300, function() {
                                            loadSmuData();
                                        });
                                    });
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal memproses permintaan.', 'error');
                            }
                        });
                    }
                });
            });

            // Push WA ke Supir Click Handler
            $(document).on('click', '.btn-push-wa', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const driver = $(this).data('driver') || 'Supir';
                const phone = $(this).data('phone') || '-';
                const area = $(this).data('area') || 'SMU';
                const hasQueue = $(this).data('has-queue') == '1' || $(this).data('has-queue') === 1 || $(
                    this).data('has-queue') === true;
                const antrian = $(this).data('antrian') || '';

                const modalTitle = hasQueue ? 'Panggil Supir Masuk Dock via WhatsApp?' :
                    'Kirim Pengingat Serah Dokumen via WhatsApp?';
                const actionDesc = hasQueue ?
                    `Kirim notifikasi panggilan ke <b>Nomor WhatsApp Pribadi Supir</b> untuk truk <strong>${nopol}</strong> (Antrian <strong>#${antrian}</strong>) agar segera masuk Loading Dock ${area}?` :
                    `Truk <strong>${nopol}</strong> saat ini <b>belum memiliki nomor antrian</b>.<br>Kirim pesan via WhatsApp agar supir <b>segera menyerahkan dokumen / surat jalan ke Admin</b>?`;
                const btnConfirmText = hasQueue ?
                    '<i class="ri-whatsapp-line me-1"></i> Ya, Panggil Supir!' :
                    '<i class="ri-file-text-line me-1"></i> Ya, Kirim Pengingat Dokumen!';

                Swal.fire({
                    title: modalTitle,
                    html: `${actionDesc}<br><br>
                           <div class="text-start bg-light p-3 rounded border small">
                               <div class="mb-1"><strong>👤 Supir:</strong> ${driver}</div>
                               <div class="mb-1"><strong>📱 No. HP:</strong> ${phone}</div>
                               <div class="mb-1"><strong>📍 Area:</strong> Dock ${area}</div>
                               <div><strong>🎫 Status:</strong> ${hasQueue ? `<span class="badge bg-success">Antrian #${antrian}</span>` : '<span class="badge bg-secondary">Belum Antri</span>'}</div>
                           </div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#25D366',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: btnConfirmText,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Mengirim notifikasi...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: `{{ url('vehicle-monitoring/smu/notify-driver') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                area: area
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire({
                                        title: 'Terkirim!',
                                        text: res.message,
                                        icon: 'success',
                                        timer: 3500,
                                        showConfirmButton: true
                                    });
                                } else {
                                    Swal.fire('Gagal', res.message ||
                                        'Gagal mengirim pesan WhatsApp.', 'error');
                                }
                            },
                            error: function(xhr) {
                                const isCooldown = xhr.status === 429 || xhr
                                    .responseJSON?.is_cooldown;
                                const title = isCooldown ? 'Perhatian (Anti-Spam)' :
                                    'Error';
                                const icon = isCooldown ? 'warning' : 'error';
                                const msg = xhr.responseJSON?.message ||
                                    'Terjadi kesalahan sistem saat mengirim WhatsApp.';
                                Swal.fire(title, msg, icon);
                            }
                        });
                    }
                });
            });

            // Ambil Antrian Click Handler
            $(document).on('click', '.btn-get-queue', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                Swal.fire({
                    title: 'Ambil Nomor Antrian?',
                    text: `Ambil nomor antrian otomatis untuk truk ${nopol}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3577f1',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ambil!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/update-queue') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadSmuData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal mengambil nomor antrian.', 'error');
                            }
                        });
                    }
                });
            });

            // Batal Antrian Click Handler
            $(document).on('click', '.btn-cancel-queue', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const antrian = $(this).data('antrian');

                Swal.fire({
                    title: 'Batalkan Antrian?',
                    text: `Batalkan nomor antrian ${antrian} untuk truk ${nopol}? Truk akan kembali ke status Menunggu Antrian.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/cancel-queue') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadSmuData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal membatalkan nomor antrian.', 'error');
                            }
                        });
                    }
                });
            });

            // Batal Bongkar / Batal Muat Click Handler
            $(document).on('click', '.btn-cancel-loading', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const action = $(this).data('action');

                Swal.fire({
                    title: `${action}?`,
                    html: `Apakah Anda yakin membatalkan proses ${action.toLowerCase().replace('batal ', '')} untuk truk <strong>${nopol}</strong>?<br><span class="text-muted small">Truk akan diarahkan kembali ke Timbangan untuk Check-Out tanpa ${action.toLowerCase().replace('batal ', '')}.</span>`,
                    input: 'text',
                    inputPlaceholder: 'Masukkan alasan pembatalan (opsional)...',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Ya, ${action}!`,
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const reason = result.value || '';
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/smu/cancel-loading') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                reason: reason
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadSmuData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal membatalkan aktivitas.', 'error');
                            }
                        });
                    }
                });
            });

            // Follow Up Timbangan Click Handler (Untuk Truk Belum Terdaftar)
            $('#btnFollowUpTimbangan').on('click', function() {
                const vendorOptions = (@json($vendors ?? [])).map(v =>
                    `<option value="${v.name}">${v.name}</option>`).join('');
                const itemOptions = (@json($items ?? [])).map(i =>
                    `<option value="${i.id}">${i.name}</option>`).join('');

                Swal.fire({
                    title: '<span class="text-warning fw-bold"><i class="ri-alarm-warning-line me-1"></i> Follow Up ke Timbangan</span>',
                    html: `
                        <div class="text-start">
                            <p class="text-muted small mb-3">Lapor ke Timbangan jika ada truk yang <strong>sudah tiba di SMU</strong> namun belum terdaftar di sistem.</p>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">No. Polisi <span class="text-danger">*</span></label>
                                <input type="text" id="fu_nopol" class="form-control text-uppercase" placeholder="Contoh: B1234XYZ" required autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Item / Barang <span class="text-danger">*</span></label>
                                <select id="fu_item_id" class="form-select" required>
                                    <option value="" selected disabled>Pilih Item</option>
                                    ${itemOptions}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Jenis Aktivitas <span class="text-danger">*</span></label>
                                <select id="fu_jenis" class="form-select">
                                    <option value="bongkaran">Bongkaran</option>
                                    <option value="slipsheet">Slipsheet</option>
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">No. SPB (Opsional)</label>
                                    <input type="text" id="fu_no_spb" class="form-control text-uppercase" placeholder="Contoh: SPB-001" autocomplete="off">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Qty SPB (Opsional)</label>
                                    <input type="number" id="fu_qty_spb" step="any" min="0" class="form-control" placeholder="Contoh: 1000">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nama Vendor</label>
                                <input type="text" id="fu_vendor" class="form-control" list="fu_vendor_list" placeholder="Pilih atau ketik vendor..." autocomplete="off">
                                <datalist id="fu_vendor_list">
                                    ${vendorOptions}
                                </datalist>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Catatan / Keterangan (Opsional)</label>
                                <textarea id="fu_notes" class="form-control" rows="2" placeholder="Contoh: Truk sudah standby di area SMU, mohon segera input timbangan."></textarea>
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#ffbb44',
                    confirmButtonText: '<i class="ri-send-plane-fill me-1"></i> Kirim Follow Up',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        const nopol = $('#fu_nopol').val().trim().toUpperCase().replace(/\s+/g,
                            '');
                        const itemId = $('#fu_item_id').val();
                        const jenis = $('#fu_jenis').val();
                        const noSpb = $('#fu_no_spb').val().trim().toUpperCase();
                        const qtySpb = $('#fu_qty_spb').val();
                        const vendor = $('#fu_vendor').val().trim();
                        const notes = $('#fu_notes').val().trim();

                        if (!nopol) {
                            Swal.showValidationMessage('No. Polisi wajib diisi!');
                            return false;
                        }
                        if (!itemId) {
                            Swal.showValidationMessage('Pilih item / barang!');
                            return false;
                        }
                        if (!jenis) {
                            Swal.showValidationMessage('Pilih jenis aktivitas!');
                            return false;
                        }

                        return {
                            no_pol: nopol,
                            item_id: itemId,
                            jenis: jenis,
                            no_spb: noSpb,
                            qty_spb: qtySpb,
                            vendor: vendor,
                            area: 'SMU',
                            notes: notes
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const postData = result.value;
                        $.ajax({
                            url: "{{ route('vehicle.monitoring.follow_up_timbangan') }}",
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                no_pol: postData.no_pol,
                                item_id: postData.item_id,
                                jenis: postData.jenis,
                                no_spb: postData.no_spb,
                                qty_spb: postData.qty_spb,
                                vendor: postData.vendor,
                                area: postData.area,
                                notes: postData.notes
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Terkirim!',
                                    text: response.message,
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal!', xhr.responseJSON?.message ||
                                    'Terjadi kesalahan saat mengirim follow up.',
                                    'error');
                            }
                        });
                    }
                });
            });

            // Handler Modal Detail Kendaraan saat No. Polisi diklik (berlaku untuk Pengawas & Admin)
            $(document).on('click', '.btn-detail-truck', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const tx = allSmuData.find(item => item.id == id);
                if (!tx) return;

                $('#dtNopol').text(tx.no_pol);
                $('#dtVendor').text(tx.vendor || '-');
                $('#dtDriver').text(`${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})`);
                $('#dtJenis').html(
                    `<span class="badge bg-soft-warning text-dark text-capitalize">${tx.jenis || 'Muat'}</span> <span class="badge bg-soft-primary text-primary">Area SMU</span>`
                    );
                $('#dtItem').text(tx.item_name || '-');
                $('#dtNoSpb').text(tx.no_spb || '-');
                $('#dtQtySpb').text(tx.qty_spb || '-');

                // Antrian
                if (tx.no_antrian) {
                    $('#dtAntrianBadge').html(
                        `<span class="badge bg-soft-success text-success fs-14 px-3 py-1">Antrian #${tx.no_antrian}</span>`
                        );
                } else {
                    $('#dtAntrianBadge').html(
                        `<span class="badge bg-soft-secondary text-secondary fs-13 px-2 py-1">Belum Antri</span>`
                        );
                }

                // Status Aktivitas
                const isProcess = tx.unloading_status === 'process';
                const actionLabel = (tx.jenis || '').toLowerCase() === 'bongkaran' ? 'Bongkar' : 'Muat';
                if (!tx.no_antrian) {
                    $('#dtStatusBadge').html(
                        `<span class="badge bg-soft-secondary text-secondary fs-13 px-2 py-1">Menunggu Antrian</span>`
                        );
                } else if (!isProcess) {
                    $('#dtStatusBadge').html(
                        `<span class="badge bg-soft-warning text-warning fs-13 px-2 py-1">Antrian #${tx.no_antrian}</span>`
                        );
                } else {
                    $('#dtStatusBadge').html(
                        `<span class="badge bg-soft-info text-info fs-13 px-2 py-1"><i class="ri-loader-4-line ri-spin me-1"></i>Proses ${actionLabel}</span>`
                        );
                }

                // QC Status (SMU default N/A)
                if (tx.qc_status) {
                    let qcColor = (tx.qc_status === 'passed' || tx.qc_status === 'released') ? 'success' : (
                        tx.qc_status === 'rejected' ? 'danger' : 'warning');
                    $('#dtQcBadge').html(
                        `<span class="badge bg-soft-${qcColor} text-${qcColor} fs-13 px-2 py-1 text-uppercase">${tx.qc_status}</span>`
                        );
                } else {
                    $('#dtQcBadge').html(
                        `<span class="badge bg-soft-secondary text-secondary fs-13 px-2 py-1">N/A</span>`
                        );
                }

                // Timelines
                $('#dtTimeArrival').text(tx.arrival_time || '-');
                $('#dtTimeQueue').text(tx.queue_taken_time || '-');
                $('#dtTimeStart').text(tx.start_loading_time || '-');
                $('#dtTimeFinish').text(tx.finish_loading_time || '-');

                // Durasi Live
                const nowTs = Math.floor(Date.now() / 1000);
                if (!tx.no_antrian) {
                    const diff = Math.max(0, nowTs - (tx.arrival_timestamp || nowTs));
                    $('#dtLiveDuration').html(
                        `<span class="badge bg-soft-secondary text-secondary fs-13 px-2 py-1">${formatDuration(diff)}</span> <span class="text-muted">(Tunggu Antri)</span>`
                        );
                    $('#dtDurationBreakdown').text('');
                } else if (!isProcess) {
                    const waitSec = tx.queue_taken_timestamp ? Math.max(0, tx.queue_taken_timestamp - tx
                        .arrival_timestamp) : 0;
                    const diff = Math.max(0, nowTs - (tx.queue_taken_timestamp || tx.arrival_timestamp ||
                        nowTs));
                    $('#dtLiveDuration').html(
                        `<span class="badge bg-soft-warning text-warning fs-13 px-2 py-1">${formatDuration(diff)}</span> <span class="text-warning fw-medium">(Durasi Antri)</span>`
                        );
                    $('#dtDurationBreakdown').text(waitSec > 0 ?
                        `Waktu Tunggu s/d Antri: ${formatDuration(waitSec)}` : '');
                } else {
                    const startTs = tx.start_loading_timestamp || tx.arrival_timestamp || nowTs;
                    const diff = Math.max(0, nowTs - startTs);
                    const antriSec = (tx.start_loading_timestamp && tx.queue_taken_timestamp) ? Math.max(0,
                        tx.start_loading_timestamp - tx.queue_taken_timestamp) : 0;
                    $('#dtLiveDuration').html(
                        `<span class="badge bg-soft-info text-info fs-13 px-2 py-1">${formatDuration(diff)}</span> <span class="text-info fw-medium">(Durasi ${actionLabel})</span>`
                        );
                    $('#dtDurationBreakdown').text(antriSec > 0 ?
                        `Durasi Antri sebelumnya: ${formatDuration(antriSec)}` : '');
                }

                const modal = new bootstrap.Modal(document.getElementById('modalDetailTruck'));
                modal.show();
            });
        });
    </script>
@endsection
