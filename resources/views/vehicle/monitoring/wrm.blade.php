@extends('layouts.app')

@section('title', '| WRM Area')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">WRM Area (Bongkar & Muat)</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">WRM</li>
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
                                <i class="ri-truck-line me-2 align-middle text-primary"></i>Aktivitas Bongkar & Muat Raw
                                Material (WRM)
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
                                <table class="table table-hover align-middle text-nowrap" id="wrmTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Waktu</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Item</th>
                                            <th>No. SPB / Qty</th>
                                            <th>Jenis</th>
                                            <th>Status QC</th>
                                            <th>Status Aktivitas</th>
                                            <th>Durasi Aktivitas</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="11" class="text-center py-4 text-muted">Loading data...</td>
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

            let allWrmData = [];
            let searchQuery = '';

            function renderWrmTable() {
                let filtered = allWrmData;
                if (searchQuery) {
                    filtered = allWrmData.filter(function(tx) {
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
                        <td colspan="11" class="text-center py-4 text-muted">Tidak ada kendaraan aktif di area WRM.</td>
                    </tr>`;
                } else {
                    filtered.forEach(function(tx, index) {
                        const actionLabel = tx.action_label || (tx.jenis === 'bongkaran' ? 'Bongkar' :
                            'Muat');
                        const isProcess = tx.unloading_status === 'process';

                        // Badge Jenis (Bongkar / Muat)
                        let jenisBadge = '';
                        if (tx.jenis === 'bongkaran') {
                            jenisBadge =
                                `<span class="badge bg-soft-primary text-primary px-2 py-1"><i class="ri-download-2-line me-1 align-middle"></i>Bongkar</span>`;
                        } else {
                            jenisBadge =
                                `<span class="badge bg-soft-success text-success px-2 py-1"><i class="ri-upload-2-line me-1 align-middle"></i>Muat (${tx.jenis || 'Muat'})</span>`;
                        }

                        // Badge Status QC
                        let qcBadge = '';
                        if (tx.qc_status === 'released') {
                            qcBadge =
                                `<span class="badge bg-soft-success text-success"><i class="ri-checkbox-circle-line me-1 align-middle"></i>Released</span>`;
                        } else if (tx.qc_status === 'rejected') {
                            qcBadge =
                                `<span class="badge bg-soft-danger text-danger"><i class="ri-close-circle-line me-1 align-middle"></i>Rejected</span>`;
                        } else if (tx.qc_status === 'on_check') {
                            qcBadge =
                                `<span class="badge bg-soft-info text-info"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Sampling QC</span>`;
                        } else if (tx.qc_status === 'waiting_sampling') {
                            qcBadge =
                                `<span class="badge bg-soft-warning text-warning"><i class="ri-time-line me-1 align-middle"></i>Antri QC</span>`;
                        } else if (tx.qc_status === 'waiting_dokumen') {
                            qcBadge =
                                `<span class="badge bg-soft-secondary text-secondary"><i class="ri-file-list-line me-1 align-middle"></i>Waiting Dokumen</span>`;
                        } else {
                            qcBadge = `<span class="badge bg-soft-light text-muted">-</span>`;
                        }

                        // Badge Status Aktivitas
                        let statusBadge = '';
                        if (!isProcess) {
                            statusBadge =
                                `<span class="badge bg-soft-secondary text-secondary"><i class="ri-hourglass-line me-1 align-middle"></i>Menunggu ${actionLabel}</span>`;
                        } else {
                            statusBadge =
                                `<span class="badge bg-soft-info text-info"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Proses ${actionLabel}</span>`;
                        }

                        // Tombol Aksi
                        let actionBtn = '';
                        if (!isProcess) {
                            actionBtn = `<button type="button" class="btn btn-sm btn-primary btn-start-loading" 
                                data-id="${tx.id}" 
                                data-nopol="${tx.no_pol}"
                                data-qc="${tx.qc_status || ''}"
                                data-action="${actionLabel}">
                                <i class="ri-play-circle-line me-1 align-middle"></i> Mulai ${actionLabel}
                            </button>`;
                        } else {
                            actionBtn = `<button type="button" class="btn btn-sm btn-success btn-complete-loading" 
                                data-id="${tx.id}" 
                                data-nopol="${tx.no_pol}"
                                data-action="${actionLabel}">
                                <i class="ri-checkbox-circle-line me-1 align-middle"></i> Selesai ${actionLabel}
                            </button>`;
                        }

                        // Timeline breakdown
                        let timelineHtml =
                            `<div class="d-flex flex-column gap-1">
                            <span class="fs-12 text-muted">Tiba: <strong class="text-dark">${tx.arrival_time}</strong></span>`;
                        if (tx.start_loading_time) {
                            timelineHtml +=
                                `<span class="fs-12 text-muted">Mulai: <strong class="text-info">${tx.start_loading_time}</strong></span>`;
                        }
                        timelineHtml += `</div>`;

                        // Phase Duration breakdown
                        let durasiHtml = '';
                        if (!isProcess) {
                            durasiHtml = `
                                <div>
                                    <span class="timer badge bg-soft-secondary text-secondary fs-12 px-2 py-1" data-start="${tx.arrival_timestamp}">
                                        0m 0d
                                    </span>
                                    <div style="font-size: 10.5px;" class="text-muted mt-1"><i class="ri-hourglass-line me-1"></i>Tunggu ${actionLabel}</div>
                                </div>
                            `;
                        } else {
                            const waitToStartSec = tx.start_loading_timestamp ? Math.max(0, tx
                                .start_loading_timestamp - tx.arrival_timestamp) : 0;
                            const startProcessFrom = tx.start_loading_timestamp || tx.arrival_timestamp;
                            durasiHtml = `
                                <div>
                                    <span class="timer badge bg-soft-info text-info fs-12 px-2 py-1" data-start="${startProcessFrom}">
                                        0m 0d
                                    </span>
                                    <div style="font-size: 10.5px;" class="text-info fw-medium mt-1"><i class="ri-loader-4-line ri-spin me-1"></i>Durasi ${actionLabel}</div>
                                    ${waitToStartSec > 0 ? `<div style="font-size: 10px;" class="text-muted">Tunggu: ${formatDuration(waitToStartSec)}</div>` : ''}
                                </div>
                            `;
                        }

                        html += `
                            <tr id="row-${tx.id}">
                            <td class="text-center">${index + 1}</td>
                            <td>${timelineHtml}</td>
                            <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                            <td>
                                <strong>${tx.vendor || '-'}</strong><br>
                                <small class="text-muted">Driver: ${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                            </td>
                            <td>${tx.item_name}</td>
                            <td>
                                <strong>${tx.no_spb}</strong><br>
                                <small class="text-muted">${tx.qty_spb}</small>
                            </td>
                            <td>${jenisBadge}</td>
                            <td>${qcBadge}</td>
                            <td>${statusBadge}</td>
                            <td>${durasiHtml}</td>
                            <td class="text-center">
                                ${actionBtn}
                            </td>
                        </tr>`;
                    });
                }
                $('#wrmTable tbody').html(html);
                updateTimers();
            }

            const handledFollowUps = new Set();

            function playFollowUpNotificationSound() {
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
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
                } catch(e) {}
            }

            function triggerFollowUpAlert(data) {
                const alertKey = (data.transaction_id || data.id) + '_' + (data.time || data.follow_up_time || Date.now());
                if (handledFollowUps.has(alertKey)) return;
                handledFollowUps.add(alertKey);

                playFollowUpNotificationSound();

                Swal.fire({
                    title: '<span class="text-danger fw-bold"><i class="ri-alarm-warning-line me-1"></i> FOLLOW UP TIMBANGAN!</span>',
                    html: `
                        <div class="text-start">
                            <div class="alert alert-danger border-0 mb-3 py-2 px-3">
                                <strong>Peringatan dari Timbangan:</strong> Kendaraan berikut membutuhkan konfirmasi / tindak lanjut segera di area WRM!
                            </div>
                            <div class="card bg-light border-0 mb-2 p-3">
                                <p class="mb-1"><strong>No. Polisi:</strong> <span class="badge bg-primary fs-13">${data.no_pol}</span></p>
                                ${data.no_spb ? `<p class="mb-1"><strong>No. SPB:</strong> ${data.no_spb}</p>` : ''}
                                ${data.notes ? `<p class="mb-1 text-danger"><strong>Pesan:</strong> "${data.notes}"</p>` : ''}
                                <p class="mb-0 text-muted small"><i class="ri-time-line me-1"></i>Waktu: ${data.time || data.follow_up_time || '-'}</p>
                            </div>
                            <p class="text-muted small mb-0">Truk dilaporkan sudah berada di timbangan. Mohon segera selesaikan konfirmasi di area WRM.</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: '<i class="ri-check-line me-1"></i> Saya Mengerti, Proses Sekarang',
                    confirmButtonColor: '#3085d6',
                    allowOutsideClick: false
                });
            }

            // AJAX Data Loader
            function loadWrmData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.wrm.data') }}",
                    type: 'GET',
                    success: function(response) {
                        allWrmData = response.queue || [];
                        renderWrmTable();

                        // Check for recent follow up via polling
                        if (allWrmData.length > 0) {
                            const nowSec = Math.floor(Date.now() / 1000);
                            allWrmData.forEach(tx => {
                                if (tx.follow_up_timestamp && (nowSec - tx.follow_up_timestamp < 600)) {
                                    if (tx.follow_up_target === 'WRM' || tx.follow_up_target === 'ALL' || tx.sloc === 'B006') {
                                        triggerFollowUpAlert({
                                            id: tx.id,
                                            transaction_id: tx.id,
                                            no_pol: tx.no_pol,
                                            no_spb: tx.no_spb,
                                            notes: tx.follow_up_notes,
                                            follow_up_time: tx.follow_up_time,
                                            time: tx.follow_up_time
                                        });
                                    }
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Gagal mengambil data WRM:', xhr);
                    }
                });
            }

            // Initial load & loops
            loadWrmData();
            setInterval(updateTimers, 1000);
            setInterval(loadWrmData, 15000);

            // Search filter
            $('#search_table').on('keyup', function() {
                searchQuery = $(this).val().toLowerCase().trim();
                renderWrmTable();
            });

            // Echo Realtime Listener
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates in WRM Area...');
                    window.Echo.channel('vehicle-tracking')
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in WRM:', payload);
                            if (payload.type === 'follow_up' && (payload.target_area === 'WRM' || payload.target_area === 'ALL' || payload.target_sloc === 'B006' || payload.target_sloc === 'WRM')) {
                                triggerFollowUpAlert(payload);
                            } else {
                                if (window.toastr) {
                                    toastr.info(payload.message, 'Update WRM');
                                }
                            }
                            loadWrmData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // Mulai Bongkar / Mulai Muat Click Handler
            $(document).on('click', '.btn-start-loading', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const action = $(this).data('action');
                const qcStatus = $(this).data('qc');

                let warningNote = '';
                if (qcStatus && qcStatus !== 'released') {
                    warningNote =
                        `<br><span class="text-warning small"><i class="ri-alert-line me-1"></i>Catatan: Status QC saat ini adalah <strong>${qcStatus.toUpperCase()}</strong>.</span>`;
                }

                Swal.fire({
                    title: `Mulai ${action}?`,
                    html: `Mulai proses ${action.toLowerCase()} untuk truk <strong>${nopol}</strong>? Durasi aktivitas akan mulai dihitung.${warningNote}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Ya, Mulai ${action}!`,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/wrm/start-loading') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadWrmData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON
                                    .message : 'Gagal memulai aktivitas.', 'error');
                            }
                        });
                    }
                });
            });

            // Selesai Bongkar / Selesai Muat Click Handler
            $(document).on('click', '.btn-complete-loading', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const action = $(this).data('action');

                Swal.fire({
                    title: `Selesaikan ${action}?`,
                    text: `Konfirmasi bahwa proses ${action.toLowerCase()} truk ${nopol} telah selesai? Truk akan diarahkan kembali ke Timbangan untuk Check-Out.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3577f1',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Ya, Selesai ${action}!`,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/wrm/update-unloading') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadWrmData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON
                                    .message : 'Gagal menyelesaikan aktivitas.',
                                    'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
