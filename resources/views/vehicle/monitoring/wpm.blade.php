@extends('layouts.app')

@section('title', '| WPM Area')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">WPM Area (Bongkar)</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">WPM</li>
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
                                <i class="ri-download-2-line me-2 align-middle text-warning"></i>Aktivitas Pembongkaran WPM
                                Area
                            </h4>
                            <div class="flex-shrink-0 d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-warning btn-sm fw-semibold shadow-sm"
                                    id="btnFollowUpTimbangan">
                                    <i class="ri-alarm-warning-line me-1 align-middle"></i> Follow Up Timbangan
                                </button>
                                <div style="width: 250px;">
                                    <input type="text" class="form-control form-control-sm" id="search_table"
                                        placeholder="Cari No. Polisi / Vendor / SPB...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="wpmTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Waktu</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Item</th>
                                            <th>No. SPB / Qty</th>
                                            <th>Status QC</th>
                                            <th>Status Aktivitas</th>
                                            <th>Durasi Aktivitas</th>
                                            <th class="text-center">Aksi</th>
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

            let allWpmData = [];
            let searchQuery = '';

            function renderWpmTable() {
                let filtered = allWpmData;
                if (searchQuery) {
                    filtered = allWpmData.filter(function(tx) {
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
                        <td colspan="10" class="text-center py-4 text-muted">Tidak ada kendaraan aktif di area WPM.</td>
                    </tr>`;
                } else {
                    filtered.forEach(function(tx, index) {
                        const actionLabel = tx.action_label || (tx.jenis === 'bongkaran' ? 'Bongkar' :
                            'Muat');
                        const isProcess = tx.unloading_status === 'process';

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
                        } else if (tx.qc_status === 'not_required') {
                            qcBadge =
                                `<span class="badge bg-soft-light text-muted"><i class="ri-subtract-line me-1 align-middle"></i>Tanpa QC</span>`;
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

                        html += `<tr id="row-${tx.id}">
                            <td>${index + 1}</td>
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
                            <td>${qcBadge}</td>
                            <td>${statusBadge}</td>
                            <td>${durasiHtml}</td>
                            <td class="text-center">
                                ${actionBtn}
                            </td>
                        </tr>`;
                    });
                }
                $('#wpmTable tbody').html(html);
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
                                <strong>Peringatan dari Timbangan:</strong> ${countText} konfirmasi / tindak lanjut segera di area WPM!
                            </div>
                            ${cardsHtml}
                            <p class="text-muted small mb-0">Truk dilaporkan sudah berada di timbangan. Mohon segera selesaikan konfirmasi di area WPM.</p>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: '<i class="ri-check-line me-1"></i> Saya Mengerti, Proses Sekarang',
                    confirmButtonColor: '#3085d6',
                    allowOutsideClick: false
                });
            }

            // AJAX Data Loader
            function loadWpmData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.wpm.data') }}",
                    type: 'GET',
                    success: function(response) {
                        allWpmData = response.queue || [];
                        renderWpmTable();

                        // Check for follow up alerts (always pops up on page load/reload, suppressed during active polling once acknowledged)
                        if (allWpmData.length > 0) {
                            const followUps = allWpmData.filter(tx => {
                                return (tx.follow_up_timestamp || tx.follow_up_time) &&
                                    (tx.follow_up_target === 'WPM' || tx.follow_up_target ===
                                        'ALL' || tx.sloc === 'C001' || tx.target_sloc === 'C001'
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
                        console.error('Gagal mengambil data WPM:', xhr);
                    }
                });
            }

            // Initial load & loops
            loadWpmData();
            setInterval(updateTimers, 1000);
            setInterval(loadWpmData, 15000);

            // Search filter
            $('#search_table').on('keyup', function() {
                searchQuery = $(this).val().toLowerCase().trim();
                renderWpmTable();
            });

            // Echo Realtime Listener
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates in WPM Area...');
                    window.Echo.channel('vehicle-tracking')
                        .subscribed(() => {
                            console.log('✅ Subscribed successfully to vehicle-tracking channel in WPM');
                        })
                        .error((err) => {
                            console.error('❌ Echo connection error on vehicle-tracking channel:', err);
                        })
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in WPM:', payload);
                            if (payload.type === 'follow_up' && (payload.target_area === 'WPM' || payload
                                    .target_area === 'ALL' || payload.target_sloc === 'C001' || payload
                                    .target_sloc === 'WPM')) {
                                triggerFollowUpAlert(payload);
                            } else {
                                if (window.toastr) {
                                    toastr.info(payload.message, 'Update WPM');
                                }
                            }
                            loadWpmData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // Mulai Loading/Unloading Click Handler
            $(document).on('click', '.btn-start-loading', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const qcStatus = $(this).data('qc');
                const actionLabel = $(this).data('action') || 'Bongkar';

                let warningNote = '';
                if (qcStatus && qcStatus !== 'released' && qcStatus !== 'not_required') {
                    warningNote =
                        `<br><span class="text-warning small"><i class="ri-alert-line me-1"></i>Catatan: Status QC saat ini adalah <strong>${qcStatus.toUpperCase()}</strong>.</span>`;
                }

                Swal.fire({
                    title: `Mulai ${actionLabel}?`,
                    html: `Mulai proses ${actionLabel.toLowerCase()} untuk truk <strong>${nopol}</strong> di area WPM? Durasi aktivitas akan mulai dihitung.${warningNote}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Ya, Mulai ${actionLabel}!`,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/wpm/start-loading') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadWpmData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON
                                    .message :
                                    `Gagal memulai ${actionLabel.toLowerCase()}.`,
                                    'error');
                            }
                        });
                    }
                });
            });

            // Selesai Loading/Unloading Click Handler
            $(document).on('click', '.btn-complete-loading', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');
                const actionLabel = $(this).data('action') || 'Bongkar';

                Swal.fire({
                    title: `Selesaikan ${actionLabel}?`,
                    text: `Konfirmasi bahwa proses ${actionLabel.toLowerCase()} truk ${nopol} di area WPM telah selesai? Truk akan diarahkan kembali ke Timbangan untuk Check-Out.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3577f1',
                    cancelButtonColor: '#d33',
                    confirmButtonText: `Ya, Selesai ${actionLabel}!`,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/wpm/complete') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadWpmData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON
                                    .message :
                                    `Gagal menyelesaikan ${actionLabel.toLowerCase()}.`,
                                    'error');
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
                            <p class="text-muted small mb-3">Lapor ke Timbangan jika ada truk yang <strong>sudah tiba di WPM</strong> namun belum terdaftar di sistem.</p>
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
                                    <option value="retur">Retur</option>
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
                                <textarea id="fu_notes" class="form-control" rows="2" placeholder="Contoh: Truk sudah standby di area WPM, mohon segera input timbangan."></textarea>
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
                            Swal.showValidationMessage('Pilih jenis aktivitas!');
                            return false;
                        }

                        return {
                            no_pol: nopol,
                            item_id: itemId,
                            jenis: jenis,
                            vendor: vendor,
                            area: 'WPM',
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
