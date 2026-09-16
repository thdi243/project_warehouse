@extends('layouts.app')

@section('title', '| WFG (Muat)')

@section('content')
    <style>
        .nav-custom-pill .nav-link {
            color: #495057;
            background-color: #f8f9fa;
            border: 1px solid #e9ebec !important;
            transition: all 0.2s ease-in-out;
        }

        .nav-custom-pill .nav-link:hover {
            background-color: #eef0f2;
        }

        .nav-custom-pill .nav-link.active#tab-slipsheet {
            color: #fff !important;
            background: linear-gradient(135deg, #3577f1 0%, #2059c2 100%) !important;
            border-color: #2059c2 !important;
            box-shadow: 0 4px 10px rgba(53, 119, 241, 0.25);
        }

        .nav-custom-pill .nav-link.active#tab-curah {
            color: #fff !important;
            background: linear-gradient(135deg, #0ab39c 0%, #088c7a 100%) !important;
            border-color: #088c7a !important;
            box-shadow: 0 4px 10px rgba(10, 179, 156, 0.25);
        }

        .nav-custom-pill .nav-link.active i {
            color: #fff !important;
        }

        .nav-custom-pill .nav-link.active .badge {
            background-color: rgba(255, 255, 255, 0.28) !important;
            color: #fff !important;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">WFG Muat</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">WFG</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div
                            class="card-header align-items-center d-flex flex-wrap gap-2 border-bottom bg-transparent py-3">
                            <div class="flex-grow-1">
                                <h4 class="card-title mb-1"><i
                                        class="ri-upload-2-line me-2 align-middle text-info"></i>Antrian Muat Finished Goods
                                    (WFG)
                                </h4>
                            </div>
                            <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                @can('permission', 'vms-admin-wfg')
                                    <button type="button" class="btn btn-warning btn-sm fw-semibold shadow-sm"
                                        id="btnFollowUpTimbangan">
                                        <i class="ri-alarm-warning-line me-1 align-middle"></i> Follow Up Timbangan
                                    </button>
                                @endcan
                                <div style="width: 200px;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="ri-search-line"></i></span>
                                        <input type="text" class="form-control border-start-0" id="search_table"
                                            placeholder="Cari No. Polisi / Vendor / SPB...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <!-- Nav Tabs Jenis Muatan (Slipsheet & Curah) -->
                            <ul class="nav nav-pills nav-custom-pill gap-2 mb-3" id="wfgTypeTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link active fw-semibold d-flex align-items-center px-3 py-2 rounded-3"
                                        id="tab-slipsheet" data-jenis="slipsheet" type="button" role="tab">
                                        <i class="ri-pages-line fs-16 me-2 text-primary"></i>
                                        <span>Muat Slipsheet</span>
                                        <span class="badge bg-primary text-white rounded-pill ms-2"
                                            id="badge-count-slipsheet">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-semibold d-flex align-items-center px-3 py-2 rounded-3"
                                        id="tab-curah" data-jenis="curah" type="button" role="tab">
                                        <i class="ri-truck-line fs-16 me-2 text-info"></i>
                                        <span>Muat Curah</span>
                                        <span class="badge bg-info text-white rounded-pill ms-2"
                                            id="badge-count-curah">0</span>
                                    </button>
                                </li>
                            </ul>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="wfgTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 120px;">No. Antrian</th>
                                            <th>Waktu</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            @can('permission', 'vms-admin-wfg')
                                                <th>Item</th>
                                                <th>No. SPB</th>
                                                <th>Qty SPB</th>
                                                <th>Status</th>
                                                <th>Durasi Aktivitas</th>
                                            @endcan
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

            let allWfgData = [];
            let searchQuery = '';
            let currentJenisTab = 'slipsheet'; // Default tab: slipsheet

            $('#wfgTypeTabs button[data-jenis]').on('click', function() {
                $('#wfgTypeTabs button').removeClass('active');
                $(this).addClass('active');
                currentJenisTab = $(this).data('jenis');
                renderWfgTable();
            });

            function renderWfgTable() {
                // 1. Update count badges
                let countSlipsheet = 0;
                let countCurah = 0;
                allWfgData.forEach(function(tx) {
                    const j = (tx.jenis || '').toLowerCase().trim();
                    if (j === 'curah') {
                        countCurah++;
                    } else {
                        // slipsheet atau default
                        countSlipsheet++;
                    }
                });
                $('#badge-count-slipsheet').text(countSlipsheet);
                $('#badge-count-curah').text(countCurah);

                // 2. Filter data by active tab
                let tabFiltered = allWfgData.filter(function(tx) {
                    const j = (tx.jenis || '').toLowerCase().trim();
                    if (currentJenisTab === 'curah') {
                        return j === 'curah';
                    } else {
                        return j === 'slipsheet' || (j !== 'curah');
                    }
                });

                // 3. Filter data by search query
                let filtered = tabFiltered;
                if (searchQuery) {
                    filtered = tabFiltered.filter(function(tx) {
                        const noPol = (tx.no_pol || '').toLowerCase();
                        const vendor = (tx.vendor || '').toLowerCase();
                        const noSpb = (tx.no_spb || '').toLowerCase();
                        const driver = (tx.nama_driver || '').toLowerCase();
                        const item = (tx.item_name || '').toLowerCase();
                        return noPol.includes(searchQuery) ||
                            vendor.includes(searchQuery) ||
                            noSpb.includes(searchQuery) ||
                            driver.includes(searchQuery) ||
                            item.includes(searchQuery);
                    });
                }

                const currentTabLabel = currentJenisTab === 'curah' ? 'Curah' : 'Slipsheet';

                let html = '';
                if (filtered.length === 0) {
                    html = `<tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <div class="py-2">
                                <i class="ri-inbox-line display-5 text-muted mb-2"></i>
                                <p class="mb-0 fs-14">Tidak ada antrian muat <strong>${currentTabLabel}</strong> ${searchQuery ? 'yang cocok dengan pencarian' : 'saat ini'}.</p>
                            </div>
                        </td>
                    </tr>`;
                } else {
                    filtered.forEach(function(tx) {
                        const isBongkaran = (tx.jenis || '').toLowerCase() === 'bongkaran';
                        const actionLabel = isBongkaran ? 'Bongkar' : 'Muat';
                        const isProcess = tx.unloading_status === 'process';

                        let antrianBadge = '';
                        if (tx.no_antrian) {
                            antrianBadge =
                                `<span class="badge bg-soft-success text-success fs-13 px-3 py-2 fw-semibold">
                                    <i class="ri-hashtag me-1"></i>${tx.no_antrian}
                                </span>`;
                        } else {
                            @can('permission', 'vms-admin-wfg')
                                antrianBadge = `<button type="button" class="btn btn-sm btn-outline-warning btn-get-queue" data-id="${tx.id}" data-nopol="${tx.no_pol}" data-jenis="${tx.jenis || currentJenisTab}">
                                    <i class="ri-ticket-line me-1"></i>Ambil Antrian
                                </button>`;
                            @endcan
                        }

                        let statusBadge = '';
                        if (!tx.no_antrian) {
                            statusBadge =
                                `<span class="badge bg-soft-secondary text-secondary"><i class="ri-pause-circle-line me-1 align-middle"></i>Menunggu Antrian</span>`;
                        } else if (!isProcess) {
                            statusBadge =
                                `<span class="badge bg-soft-warning text-warning"><i class="ri-time-line me-1 align-middle"></i>Antrian #${tx.no_antrian} (${currentTabLabel})</span>`;
                        } else {
                            statusBadge =
                                `<span class="badge bg-soft-info text-info"><i class="ri-loader-4-line ri-spin me-1 align-middle"></i>Proses ${actionLabel}</span>`;
                        }

                        let actionBtn = '';
                        if (!tx.no_antrian) {
                            actionBtn = `<span class="text-muted small">-</span>`;
                        } else if (!isProcess) {
                            actionBtn = `<div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-sm btn-primary btn-start-loading" 
                                    data-id="${tx.id}" 
                                    data-nopol="${tx.no_pol}"
                                    data-action="Mulai ${actionLabel}">
                                    <i class="ri-play-circle-line me-1 align-middle"></i> Mulai ${actionLabel}
                                </button>
                                @can('permission', 'vms-admin-wfg')
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
                            actionBtn = `<button type="button" class="btn btn-sm btn-success btn-complete-loading" 
                                data-id="${tx.id}" 
                                data-nopol="${tx.no_pol}"
                                data-action="Selesai ${actionLabel}">
                                <i class="ri-checkbox-circle-line me-1 align-middle"></i> Selesai ${actionLabel}
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
                            <td>${timelineHtml}</td>
                            <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                            <td>
                                <strong>${tx.vendor || '-'}</strong><br>
                                <small class="text-muted">Driver: ${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                            </td>
                            @can('permission', 'vms-admin-wfg')
                                <td>${tx.item_name}</td>
                                <td>${tx.no_spb}</td>
                                <td>${tx.qty_spb}</td>
                                <td>${statusBadge}</td>
                                <td>${durasiHtml}</td>
                            @endcan
                            <td class="text-center">
                                ${actionBtn}
                            </td>
                        </tr>`;
                    });
                }
                $('#wfgTable tbody').html(html);
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
                                <strong>Peringatan dari Timbangan:</strong> ${countText} konfirmasi / tindak lanjut segera di area WFG!
                            </div>
                            ${cardsHtml}
                            <p class="text-muted small mb-0">Truk dilaporkan sudah berada di timbangan. Mohon segera selesaikan konfirmasi di area WFG.</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: '<i class="ri-check-line me-1"></i> Saya Mengerti, Proses Sekarang',
                    confirmButtonColor: '#3085d6',
                    allowOutsideClick: false
                });
            }

            // AJAX Data Loader
            function loadWfgData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.wfg.data') }}",
                    type: 'GET',
                    success: function(response) {
                        allWfgData = response.queue || [];
                        renderWfgTable();

                        // Check for follow up alerts (always pops up on page load/reload, suppressed during active polling once acknowledged)
                        if (allWfgData.length > 0) {
                            const followUps = allWfgData.filter(tx => {
                                return (tx.follow_up_timestamp || tx.follow_up_time) &&
                                    (tx.follow_up_target === 'WFG' || tx.follow_up_target ===
                                        'ALL' || tx.sloc === 'A001' || tx.target_sloc === 'A001'
                                    );
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
                        console.error('Gagal mengambil data WFG:', xhr);
                    }
                });
            }

            // Handle search
            $('#search_table').on('keyup', function() {
                searchQuery = $(this).val().toLowerCase();
                renderWfgTable();
            });

            // Initial load
            loadWfgData();

            // Run timers every second & polling every 15s
            setInterval(updateTimers, 1000);
            setInterval(loadWfgData, 15000);

            // Real-time Event Listener with Laravel Echo
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates on Echo channel in WFG...');
                    window.Echo.channel('vehicle-tracking')
                        .subscribed(() => {
                            console.log('✅ Subscribed successfully to vehicle-tracking channel in WFG');
                        })
                        .error((err) => {
                            console.error('❌ Echo connection error on vehicle-tracking channel:', err);
                        })
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in WFG:', payload);
                            if (payload.type === 'follow_up' && (payload.target_area === 'WFG' || payload
                                    .target_area === 'ALL' || payload.target_sloc === 'WFG')) {
                                triggerFollowUpAlert(payload);
                            } else {
                                if (window.toastr) {
                                    toastr.info(payload.message, 'Update Lokasi Truk');
                                }
                            }
                            loadWfgData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // Start loading click handler
            $(document).on('click', '.btn-start-loading', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const action = $(this).data('action') || 'Mulai Proses';

                Swal.fire({
                    title: `${action}?`,
                    text: `Konfirmasi untuk ${action.toLowerCase()} untuk truk ${nopol}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3577f1',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Mulai!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/wfg/start-loading') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Dimulai!', response.message, 'success');
                                loadWfgData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal memulai proses.', 'error');
                            }
                        });
                    }
                });
            });

            // Complete loading
            $(document).on('click', '.btn-complete-loading', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const action = $(this).data('action') || 'Selesai';

                Swal.fire({
                    title: `${action}?`,
                    text: `Apakah truk ${nopol} telah menyelesaikan aktivitas bongkar/muat?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Selesai!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/wfg/update-loading') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Selesai!', response.message, 'success').then(
                                    () => {
                                        $(`#row-${id}`).fadeOut(300, function() {
                                            loadWfgData();
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
                const txJenis = $(this).data('jenis') || currentJenisTab || 'slipsheet';
                const jenisLabel = txJenis.toUpperCase();

                Swal.fire({
                    title: `Ambil Nomor Antrian (${jenisLabel})?`,
                    text: `Ambil nomor antrian muat ${jenisLabel} otomatis untuk truk ${nopol}?`,
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
                                loadWfgData();
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
                const tabLabel = currentJenisTab === 'curah' ? 'Curah' : 'Slipsheet';

                Swal.fire({
                    title: `Batalkan Antrian ${tabLabel}?`,
                    text: `Batalkan nomor antrian #${antrian} untuk truk ${nopol}? Truk akan kembali ke status Menunggu Antrian dan antrian ${tabLabel} lainnya akan bergeser maju.`,
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
                                loadWfgData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal membatalkan nomor antrian.', 'error');
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
                            <p class="text-muted small mb-3">Lapor ke Timbangan jika ada truk yang <strong>sudah tiba di WFG</strong> namun belum terdaftar di sistem.</p>
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
                                <label class="form-label fw-bold small">Jenis Muatan <span class="text-danger">*</span></label>
                                <select id="fu_jenis" class="form-select">
                                    <option value="slipsheet" ${currentJenisTab === 'slipsheet' ? 'selected' : ''}>Slipsheet</option>
                                    <option value="curah" ${currentJenisTab === 'curah' ? 'selected' : ''}>Curah</option>
                                </select>
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
                                <textarea id="fu_notes" class="form-control" rows="2" placeholder="Contoh: Truk sudah standby di loading dock WFG, mohon segera input timbangan."></textarea>
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
                            Swal.showValidationMessage('Pilih jenis muatan!');
                            return false;
                        }

                        return {
                            no_pol: nopol,
                            item_id: itemId,
                            jenis: jenis,
                            vendor: vendor,
                            area: 'WFG',
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
        });
    </script>
@endsection
