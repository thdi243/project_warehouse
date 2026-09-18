@extends('layouts.app')

@section('title', '| QC Area')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">QC Area Queue</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">QC Area</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                            <h4 class="card-title mb-0 flex-grow-1">
                                <i class="ri-flask-line me-2 align-middle text-info"></i>Antrian Pemeriksaan QC (Quality
                                Control)
                            </h4>
                            <div class="flex-shrink-0">
                                <div style="width: 280px;">
                                    <input type="text" class="form-control" id="search_table"
                                        placeholder="Cari No. Polisi / Vendor / SPB...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="qcTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 120px;">No. Antrian</th>
                                            <th>Waktu</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Lokasi Tujuan</th>
                                            <th>Item</th>
                                            <th>No. SPB / Qty</th>
                                            <th>Status</th>
                                            <th>Durasi Aktivitas</th>
                                            <th class="text-center" style="width: 240px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="10" class="text-center py-4 text-muted">Loading data...</td>
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

    <!-- QC Update Modal -->
    <div class="modal fade" id="qcModal" tabindex="-1" aria-labelledby="qcModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title" id="qcModalLabel">Input Hasil Sampel / Keputusan QC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="qcForm">
                    @csrf
                    <input type="hidden" id="qc-transaction-id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label d-block text-muted small fw-bold text-uppercase">Kendaraan</label>
                            <h4 id="qc-nopol-text" class="text-primary fw-bold mb-1">-</h4>
                            <span id="qc-vendor-text" class="text-muted small">-</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keputusan QC <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="qc_status" id="status-release"
                                        value="released" required>
                                    <label class="btn btn-outline-success w-100 py-3" for="status-release">
                                        <i class="ri-check-double-line fs-20 d-block mb-1"></i>
                                        RELEASE
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="qc_status" id="status-reject"
                                        value="rejected" required>
                                    <label class="btn btn-outline-danger w-100 py-3" for="status-reject">
                                        <i class="ri-close-circle-line fs-20 d-block mb-1"></i>
                                        REJECT
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="qc-notes" class="form-label fw-semibold">Catatan Pemeriksaan</label>
                            <textarea class="form-control" id="qc-notes" name="notes" rows="3"
                                placeholder="Masukkan detail sampel awal, kelembaban, kadar gula, atau kendala..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnQcSubmit">Simpan Keputusan QC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
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

            let allQcData = [];
            let searchQuery = '';

            function renderQcTable() {
                let filtered = allQcData;
                if (searchQuery) {
                    filtered = allQcData.filter(function(tx) {
                        const noPol = (tx.no_pol || '').toLowerCase();
                        const vendor = (tx.vendor || '').toLowerCase();
                        const noSpb = (tx.no_spb || '').toLowerCase();
                        const item = (tx.item_name || '').toLowerCase();
                        return noPol.includes(searchQuery) || vendor.includes(searchQuery) ||
                            noSpb.includes(searchQuery) || item.includes(searchQuery);
                    });
                }

                let html = '';
                if (filtered.length === 0) {
                    html = `<tr>
                        <td colspan="10" class="text-center py-4 text-muted">Tidak ada kendaraan dalam antrian QC.</td>
                    </tr>`;
                } else {
                    filtered.forEach(function(tx) {
                        const hasQueue = !!tx.no_antrian;
                        const isSampling = tx.qc_status === 'on_check' || !!tx.start_sampling_time;

                        let antrianBadge = '';
                        if (hasQueue) {
                            antrianBadge =
                                `<span class="badge bg-soft-success text-success fs-13 px-3 py-2">#${tx.no_antrian}</span>`;
                        } else {
                            antrianBadge = `<button type="button" class="btn btn-sm btn-outline-warning btn-get-queue" data-id="${tx.id}" data-nopol="${tx.no_pol}">
                                <i class="ri-ticket-2-line me-1 align-middle"></i>Ambil Antrian
                            </button>`;
                        }

                        let statusBadge = '';
                        if (!hasQueue) {
                            statusBadge =
                                `<span class="badge bg-soft-secondary text-secondary"><i class="ri-pause-circle-line me-1 align-middle"></i>Menunggu Antrian</span>`;
                        } else if (!isSampling) {
                            statusBadge =
                                `<span class="badge bg-soft-warning text-warning"><i class="ri-time-line me-1 align-middle"></i>Antrian #${tx.no_antrian}</span>`;
                        } else {
                            statusBadge =
                                `<span class="badge bg-soft-info text-info"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Proses Sampling</span>`;
                        }

                        let actionBtn = '';
                        if (!hasQueue) {
                            // Belum ambil antrian: Berikan opsi Langsung Release / Reject (Skip Antrian)
                            actionBtn = `<button type="button" class="btn btn-sm btn-soft-success btn-qc-update" 
                                data-id="${tx.id}" 
                                data-nopol="${tx.no_pol}"
                                data-vendor="${tx.vendor || '-'}"
                                title="Keputusan Langsung (Skip Antrian)">
                                <i class="ri-scales-3-line me-1 align-middle"></i> Release / Reject
                            </button>`;
                        } else if (!isSampling) {
                            // Sudah ambil antrian, belum mulai sampling: Keputusan langsung hide, tampilkan Mulai Sampling & Batal
                            actionBtn = `<div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-sm btn-primary btn-start-sampling" 
                                    data-id="${tx.id}" 
                                    data-nopol="${tx.no_pol}">
                                    <i class="ri-play-circle-line me-1 align-middle"></i> Mulai Sampling
                                </button>
                                <button type="button" class="btn btn-sm btn-soft-danger btn-cancel-queue" 
                                    data-id="${tx.id}" 
                                    data-nopol="${tx.no_pol}"
                                    data-antrian="${tx.no_antrian}"
                                    title="Batalkan Antrian">
                                    <i class="ri-close-circle-line me-1 align-middle"></i> Batal Antrian
                                </button>
                            </div>`;
                        } else {
                            // Sedang proses sampling: Tampilkan Update QC
                            actionBtn = `<button type="button" class="btn btn-sm btn-success btn-qc-update" 
                                data-id="${tx.id}" 
                                data-nopol="${tx.no_pol}"
                                data-vendor="${tx.vendor || '-'}">
                                <i class="ri-edit-box-line me-1 align-middle"></i> Update QC
                            </button>`;
                        }

                        // Vehicle physical location / status indicator (Antri, Mulai / Proses, Selesai, Checkout di WRM/WPM)
                        let vehicleStatusBadge = '';
                        const actionName = tx.jenis === 'bongkaran' ? 'Bongkar' : (tx.jenis === 'muatan' ?
                            'Muat' : 'Bongkar');

                        if (tx.status === 'completed' || tx.status === 'timbangan_out') {
                            vehicleStatusBadge =
                                `<div class="mt-1"><span class="badge bg-soft-secondary text-secondary" style="font-size: 10px;"><i class="ri-checkbox-circle-line me-1"></i>Truk Checkout</span></div>`;
                        } else if (tx.status === 'wrm_bongkar' || tx.target_sloc === 'B006' || tx
                            .lokasi_tujuan === 'B006') {
                            if (tx.unloading_status === 'process' || (tx.start_loading_time && !tx
                                    .finish_loading_time)) {
                                vehicleStatusBadge =
                                    `<div class="mt-1"><span class="badge bg-soft-info text-info" style="font-size: 10px;"><i class="ri-loader-4-line ri-spin me-1"></i>Truk: Mulai ${actionName}</span></div>`;
                            } else if (tx.unloading_status === 'completed' || tx.finish_loading_time) {
                                vehicleStatusBadge =
                                    `<div class="mt-1"><span class="badge bg-soft-success text-success" style="font-size: 10px;"><i class="ri-check-double-line me-1"></i>Truk: Selesai ${actionName}</span></div>`;
                            } else {
                                vehicleStatusBadge =
                                    `<div class="mt-1"><span class="badge bg-soft-warning text-warning" style="font-size: 10px;"><i class="ri-time-line me-1"></i>Truk: Antri ${actionName}</span></div>`;
                            }
                        } else if (tx.status === 'wpm' || tx.target_sloc === 'C001' || tx.lokasi_tujuan ===
                            'C001') {
                            if (tx.unloading_status === 'process' || (tx.start_loading_time && !tx
                                    .finish_loading_time)) {
                                vehicleStatusBadge =
                                    `<div class="mt-1"><span class="badge bg-soft-primary text-primary" style="font-size: 10px;"><i class="ri-loader-4-line ri-spin me-1"></i>Truk: Mulai ${actionName}</span></div>`;
                            } else if (tx.unloading_status === 'completed' || tx.finish_loading_time) {
                                vehicleStatusBadge =
                                    `<div class="mt-1"><span class="badge bg-soft-success text-success" style="font-size: 10px;"><i class="ri-check-double-line me-1"></i>Truk: Selesai ${actionName}</span></div>`;
                            } else {
                                vehicleStatusBadge =
                                    `<div class="mt-1"><span class="badge bg-soft-warning text-warning" style="font-size: 10px;"><i class="ri-time-line me-1"></i>Truk: Antri ${actionName}</span></div>`;
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
                        if (tx.start_sampling_time) {
                            timelineHtml +=
                                `<span class="fs-12 text-muted">Sampling: <strong class="text-info">${tx.start_sampling_time}</strong></span>`;
                        }
                        timelineHtml += `</div>`;

                        // Phase Duration breakdown
                        let durasiHtml = '';
                        if (!hasQueue) {
                            durasiHtml = `
                                <div>
                                    <span class="timer badge bg-soft-secondary text-secondary fs-12 px-2 py-1" data-start="${tx.arrival_timestamp}">
                                        0m 0d
                                    </span>
                                    <div style="font-size: 10.5px;" class="text-muted mt-1"><i class="ri-hourglass-line me-1"></i>Tunggu Antri</div>
                                </div>
                            `;
                        } else if (!isSampling) {
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
                            const antriDurationSec = (tx.start_sampling_timestamp && tx
                                    .queue_taken_timestamp) ?
                                Math.max(0, tx.start_sampling_timestamp - tx.queue_taken_timestamp) :
                                (tx.start_sampling_timestamp ? Math.max(0, tx.start_sampling_timestamp - tx
                                    .arrival_timestamp) : 0);
                            const startSamplingFrom = tx.start_sampling_timestamp || tx.arrival_timestamp;
                            durasiHtml = `
                                <div>
                                    <span class="timer badge bg-soft-info text-info fs-12 px-2 py-1" data-start="${startSamplingFrom}">
                                        0m 0d
                                    </span>
                                    <div style="font-size: 10.5px;" class="text-info fw-medium mt-1"><i class="ri-loader-4-line ri-spin me-1"></i>Durasi Sampling</div>
                                    ${antriDurationSec > 0 ? `<div style="font-size: 10px;" class="text-muted">Durasi Antri: ${formatDuration(antriDurationSec)}</div>` : ''}
                                </div>
                            `;
                        }

                        html += `<tr id="row-${tx.id}">
                            <td class="text-center">${antrianBadge}</td>
                            <td>${timelineHtml}</td>
                            <td>
                                <span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span>
                                ${vehicleStatusBadge}
                            </td>
                            <td>
                                <strong>${tx.vendor || '-'}</strong><br>
                                <small class="text-muted">Driver: ${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                            </td>
                            <td>
                                <span class="badge bg-soft-dark text-dark">${tx.lokasi_tujuan_name || tx.lokasi_tujuan || '-'}</span>
                            </td>
                            <td>${tx.item_name}</td>
                            <td>
                                <strong>${tx.no_spb}</strong><br>
                                <small class="text-muted">${tx.qty_spb}</small>
                            </td>
                            <td>${statusBadge}</td>
                            <td>${durasiHtml}</td>
                            <td class="text-center">
                                ${actionBtn}
                            </td>
                        </tr>`;
                    });
                }
                $('#qcTable tbody').html(html);
                updateTimers();
            }

            const handledFollowUps = new Set();

            function playFollowUpNotificationSound() {
                try {
                    const audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.15); // A5
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
                                <strong>Peringatan dari Timbangan:</strong> ${countText} konfirmasi / tindak lanjut segera di area QC!
                            </div>
                            ${cardsHtml}
                            <p class="text-muted small mb-0">Truk dilaporkan sudah berada di timbangan. Mohon segera input hasil keputusan QC (Release / Reject).</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: '<i class="ri-check-line me-1"></i> Saya Mengerti, Proses Sekarang',
                    confirmButtonColor: '#3085d6',
                    allowOutsideClick: false
                });
            }

            // AJAX Data Loader
            function loadQcData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.qc.data') }}",
                    type: 'GET',
                    success: function(response) {
                        allQcData = response.queue || [];
                        renderQcTable();

                        // Check for follow up alerts (always pops up on page load/reload, suppressed during active polling once acknowledged)
                        if (allQcData.length > 0) {
                            const followUps = allQcData.filter(tx => {
                                return (tx.follow_up_timestamp || tx.follow_up_time) &&
                                    (tx.follow_up_target === 'QC' || tx.follow_up_target ===
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
                        console.error('Gagal mengambil data QC:', xhr);
                    }
                });
            }

            // Initial load & timer loop
            loadQcData();
            setInterval(updateTimers, 1000);
            setInterval(loadQcData, 15000);

            // Search filter
            $('#search_table').on('keyup', function() {
                searchQuery = $(this).val().toLowerCase().trim();
                renderQcTable();
            });

            // Echo Realtime Listener
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates in QC Area...');
                    window.Echo.channel('vehicle-tracking')
                        .subscribed(() => {
                            console.log('✅ Subscribed successfully to vehicle-tracking channel in QC');
                        })
                        .error((err) => {
                            console.error('❌ Echo connection error on vehicle-tracking channel:', err);
                        })
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in QC:', payload);
                            if (payload.type === 'follow_up' && (payload.target_area === 'QC' || payload
                                    .target_area === 'ALL')) {
                                triggerFollowUpAlert(payload);
                            } else {
                                if (window.toastr) {
                                    toastr.info(payload.message, 'Update QC');
                                }
                            }
                            loadQcData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // 1. Ambil Antrian Click Handler
            $(document).on('click', '.btn-get-queue', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                Swal.fire({
                    title: 'Ambil Nomor Antrian QC?',
                    text: `Ambil nomor antrian sampling untuk truk ${nopol}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3577f1',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ambil Antrian!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/qc/update-queue') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadQcData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON
                                    .message : 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });

            // 1b. Batal Antrian Click Handler
            $(document).on('click', '.btn-cancel-queue', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const antrian = $(this).data('antrian');

                Swal.fire({
                    title: 'Batalkan Antrian QC?',
                    text: `Batalkan nomor antrian #${antrian} untuk truk ${nopol}? Truk akan kembali ke status Menunggu Antrian.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/qc/cancel-queue') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadQcData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON
                                    .message : 'Terjadi kesalahan sistem.', 'error');
                            }
                        });
                    }
                });
            });

            // 2. Mulai Sampling Click Handler
            $(document).on('click', '.btn-start-sampling', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                Swal.fire({
                    title: 'Mulai Proses Sampling?',
                    text: `Mulai proses sampling QC untuk truk ${nopol}? Durasi sampling akan mulai dihitung.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Mulai Sampling!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/qc/start-sampling') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadQcData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON
                                    .message : 'Gagal memulai sampling.', 'error');
                            }
                        });
                    }
                });
            });

            // 3. Update QC Modal Open
            $(document).on('click', '.btn-qc-update', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const vendor = $(this).data('vendor');

                $('#qc-transaction-id').val(id);
                $('#qc-nopol-text').text(nopol);
                $('#qc-vendor-text').text('Vendor: ' + vendor);
                $('#qc-notes').val('');
                $('input[name="qc_status"]').prop('checked', false);

                const modal = new bootstrap.Modal(document.getElementById('qcModal'));
                modal.show();
            });

            // 4. Submit Update QC Form
            $('#qcForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#qc-transaction-id').val();
                const qcStatus = $('input[name="qc_status"]:checked').val();
                const notes = $('#qc-notes').val();

                if (!qcStatus) {
                    Swal.fire('Peringatan', 'Silakan pilih keputusan QC (RELEASE atau REJECT)', 'warning');
                    return;
                }

                $('#btnQcSubmit').prop('disabled', true).html(
                    '<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');

                $.ajax({
                    url: `{{ url('vehicle-monitoring/qc/update-qc') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        qc_status: qcStatus,
                        notes: notes
                    },
                    success: function(response) {
                        $('#btnQcSubmit').prop('disabled', false).text('Simpan Keputusan QC');
                        bootstrap.Modal.getInstance(document.getElementById('qcModal')).hide();
                        Swal.fire('Berhasil!', response.message, 'success');
                        loadQcData();
                    },
                    error: function(xhr) {
                        $('#btnQcSubmit').prop('disabled', false).text('Simpan Keputusan QC');
                        Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON.message :
                            'Gagal mengupdate keputusan QC.', 'error');
                    }
                });
            });
        });
    </script>
@endsection
