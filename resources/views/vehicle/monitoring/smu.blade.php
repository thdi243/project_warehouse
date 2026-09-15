@extends('layouts.app')

@section('title', '| SMU Area')

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
                            <div class="flex-shrink-0">
                                <div style="width: 250px;">
                                    <input type="text" class="form-control" id="search_table"
                                        placeholder="Cari No. Polisi / Vendor...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="smuTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 120px;">No. Antrian</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            @can('permission', 'vms-admin-smu')
                                                <th>Item</th>
                                                <th>No. SPB / Qty</th>
                                                <th>Waktu</th>
                                                <th>Status</th>
                                                <th>Durasi Aktivitas</th>
                                            @endcan
                                            <th class="text-center" style="width: 240px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">Loading data...</td>
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
                        <td colspan="9" class="text-center py-4 text-muted">Tidak ada kendaraan yang sesuai pencarian.</td>
                    </tr>`;
                } else {
                    filtered.forEach(function(tx) {
                        const isSlipsheet = (tx.jenis || '').toLowerCase() === 'slipsheet';
                        const actionLabel = isSlipsheet ? 'Muat' : 'Bongkar';
                        const isProcess = tx.unloading_status === 'process';

                        let antrianBadge = '';
                        if (tx.no_antrian) {
                            antrianBadge =
                                `<span class="badge bg-soft-success text-success fs-13 px-3 py-2">${tx.no_antrian}</span>`;
                        } else {
                            @can('permission', 'vms-admin-smu')
                                antrianBadge = `<button type="button" class="btn btn-sm btn-outline-warning btn-get-queue" data-id="${tx.id}" data-nopol="${tx.no_pol}">
                                    Ambil Antrian
                                </button>`;
                            @endcan
                        }

                        let statusBadge = '';
                        if (!tx.no_antrian) {
                            statusBadge =
                                `<span class="badge bg-soft-secondary text-secondary"><i class="ri-pause-circle-line me-1 align-middle"></i>Menunggu Antrian</span>`;
                        } else if (!isProcess) {
                            statusBadge =
                                `<span class="badge bg-soft-warning text-warning"><i class="ri-time-line me-1 align-middle"></i>Antrian ${tx.no_antrian}</span>`;
                        } else {
                            statusBadge =
                                `<span class="badge bg-soft-info text-info"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Proses ${actionLabel}</span>`;
                        }

                        let actionBtn = '';
                        if (!tx.no_antrian) {
                            actionBtn = `<span class="text-muted small">-</span>`;
                        } else if (!isProcess) {
                            actionBtn = `<div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-sm btn-primary btn-start-smu" 
                                    data-id="${tx.id}" 
                                    data-nopol="${tx.no_pol}"
                                    data-action="Mulai ${actionLabel}">
                                    <i class="ri-play-circle-line me-1 align-middle"></i> Mulai ${actionLabel}
                                </button>
                                @can('permission', 'vms-admin-smu')
                                    <button type="button" class="btn btn-sm btn-soft-danger btn-cancel-queue" 
                                        data-id="${tx.id}" 
                                        data-nopol="${tx.no_pol}"
                                        data-antrian="${tx.no_antrian}"
                                        title="Batalkan Antrian">
                                        <i class="ri-close-circle-line me-1 align-middle"></i> Batal Antrian
                                    </button>
                                @endcan
                            </div>`;
                        } else {
                            actionBtn = `<button type="button" class="btn btn-sm btn-warning btn-complete-smu" 
                                data-id="${tx.id}" 
                                data-nopol="${tx.no_pol}">
                                <i class="ri-checkbox-circle-line me-1 align-middle"></i> Selesai
                            </button>`;
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

                        html += `<tr id="row-${tx.id}">
                            <td class="text-center">${antrianBadge}</td>
                            <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                            <td>
                                <strong>${tx.vendor || '-'}</strong><br>
                                <small class="text-muted">Driver: ${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                            </td>
                            @can('permission', 'vms-admin-smu')
                                <td>${tx.item_name}</td>
                                <td>
                                    <strong>${tx.no_spb}</strong><br>
                                    <small class="text-muted">${tx.qty_spb}</small>
                                </td>
                                <td>${timelineHtml}</td>
                                <td>${statusBadge}</td>
                                <td>${durasiHtml}</td>
                            @endcan
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
        });
    </script>
@endsection
