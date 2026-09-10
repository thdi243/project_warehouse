@extends('layouts.app')

@section('title', '| Laporan History Kendaraan')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0 text-dark fw-bold">Laporan History Kendaraan</h4>
                            <p class="text-muted mb-0 small">Histori lengkap perpindahan lokasi, aktivitas, dan rincian
                                durasi (tunggu, antri, aksi, total).</p>
                        </div>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">Laporan History</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <div class="row align-items-end g-3">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Cari Kendaraan /
                                        Dokumen</label>
                                    <div class="search-box">
                                        <input type="text" id="reportSearch" class="form-control"
                                            placeholder="Plat No, SPB, Transaksi, Driver, Vendor...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Lokasi Tujuan</label>
                                    <select id="reportLocation" class="form-select">
                                        <option value="">Semua Lokasi</option>
                                        @foreach ($locations as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->s_loc }} - {{ $loc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Jenis
                                        Aktivitas</label>
                                    <select id="reportJenis" class="form-select">
                                        <option value="">Semua Jenis</option>
                                        <option value="bongkaran">Bongkaran</option>
                                        <option value="slipsheet">Slipsheet</option>
                                        <option value="curah">Curah</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Mulai Tanggal</label>
                                    <input type="date" id="reportStartDate" class="form-control">
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Sampai Tanggal</label>
                                    <input type="date" id="reportEndDate" class="form-control">
                                </div>
                                <div class="col-lg-1 col-md-4">
                                    <button type="button" class="btn btn-soft-danger w-100" id="btnReportReset"
                                        title="Reset Filter">
                                        <i class="ri-refresh-line"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="reportTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Kendaraan & Vendor</th>
                                            <th>Jenis & Item</th>
                                            <th>No. SPB / Qty</th>
                                            <th>Tujuan</th>
                                            <th>Check-In & Out</th>
                                            <th class="text-center">Durasi Tunggu</th>
                                            <th class="text-center">Durasi Antri</th>
                                            <th class="text-center">Durasi Aksi</th>
                                            <th class="text-center">Total Durasi</th>
                                            <th>Rute Perpindahan</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data populated via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail History Modal -->
    <div class="modal fade" id="detailHistoryModal" tabindex="-1" aria-labelledby="detailHistoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0 py-3">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="detailHistoryModalLabel">
                            <i class="ri-route-line text-primary me-2"></i>Detail Histori & Perpindahan Lokasi
                        </h5>
                        <small class="text-muted" id="modalSubtitle">Memuat data transaksi...</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- KPI Duration Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm bg-soft-secondary mb-0 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="ri-time-line text-secondary fs-18 me-2"></i>
                                        <span class="text-uppercase fs-11 fw-bold text-muted">Durasi Tunggu</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold text-secondary" id="modalKpiTunggu">-</h4>
                                    <small class="text-muted fs-11">Check-in s/d Ambil Antrian</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm bg-soft-warning mb-0 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="ri-hourglass-2-line text-warning fs-18 me-2"></i>
                                        <span class="text-uppercase fs-11 fw-bold text-warning">Durasi Antri</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold text-warning" id="modalKpiAntri">-</h4>
                                    <small class="text-muted fs-11">Ambil Antrian s/d Mulai Aksi</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm bg-soft-info mb-0 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="ri-truck-line text-info fs-18 me-2"></i>
                                        <span class="text-uppercase fs-11 fw-bold text-info">Durasi Aksi</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold text-info" id="modalKpiAksi">-</h4>
                                    <small class="text-muted fs-11">Sampling QC + Bongkar/Muat</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm bg-soft-success mb-0 h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="ri-timer-line text-success fs-18 me-2"></i>
                                        <span class="text-uppercase fs-11 fw-bold text-success">Total Durasi</span>
                                    </div>
                                    <h4 class="mb-0 fw-bold text-success" id="modalKpiTotal">-</h4>
                                    <small class="text-muted fs-11">Check-in s/d Check-out</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Left: Info & Timestamps -->
                        <div class="col-lg-5">
                            <!-- Info Card -->
                            <div class="card border mb-3">
                                <div class="card-header bg-light py-2 px-3">
                                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">
                                        <i class="ri-information-line me-1 text-primary"></i> Informasi Kendaraan & Dokumen
                                    </h6>
                                </div>
                                <div class="card-body p-3 fs-13">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">No. Polisi:</td>
                                            <td><span class="badge bg-soft-primary text-primary fs-12 fw-bold"
                                                    id="modalPlat">-</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Vendor:</td>
                                            <td class="fw-bold" id="modalVendor">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Driver / HP:</td>
                                            <td id="modalDriver">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Jenis Aktivitas:</td>
                                            <td id="modalJenis">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Item Barang:</td>
                                            <td class="fw-bold" id="modalItem">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">No. SPB / Qty:</td>
                                            <td id="modalSpb">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Lokasi Tujuan:</td>
                                            <td id="modalTujuan">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Timestamps Card -->
                            <div class="card border mb-3">
                                <div class="card-header bg-light py-2 px-3">
                                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">
                                        <i class="ri-time-line me-1 text-info"></i> Catatan Waktu Aktivitas (Timestamps)
                                    </h6>
                                </div>
                                <div class="card-body p-3 fs-13">
                                    <div class="d-flex flex-column gap-2" id="modalTimestampsContainer">
                                        <!-- Timestamps populated via JS -->
                                    </div>
                                </div>
                            </div>

                            <!-- Action Breakdown Card -->
                            <div class="card border mb-0" id="modalActionCard">
                                <div class="card-header bg-light py-2 px-3">
                                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">
                                        <i class="ri-flashlight-line me-1 text-warning"></i> Rincian Aksi
                                    </h6>
                                </div>
                                <div class="card-body p-3 fs-13" id="modalActionBreakdown">
                                    <!-- Action breakdown populated via JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Right: Movement Checkpoint Logs -->
                        <div class="col-lg-7">
                            <div class="card border h-100">
                                <div
                                    class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0 fs-13 fw-bold text-dark">
                                        <i class="ri-road-map-line me-1 text-success"></i> Riwayat Perpindahan Lokasi
                                        (Tracking Steps)
                                    </h6>
                                    <span class="badge bg-soft-primary text-primary" id="modalStepCount">0
                                        Checkpoints</span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="timeline-wrapper" id="modalTrackingSteps">
                                        <!-- Steps populated via JS -->
                                    </div>
                                </div>
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
            let transactionsData = [];

            function jenisBadge(jenis) {
                switch (jenis) {
                    case 'bongkaran':
                        return 'soft-warning text-warning';
                    case 'slipsheet':
                        return 'soft-success text-success';
                    case 'curah':
                        return 'soft-primary text-primary';
                    default:
                        return 'soft-secondary text-secondary';
                }
            }

            function loadReports() {
                const search = $('#reportSearch').val();
                const targetLocationId = $('#reportLocation').val();
                const jenis = $('#reportJenis').val();
                const startDate = $('#reportStartDate').val();
                const endDate = $('#reportEndDate').val();
                const tbody = $('#reportTable tbody');

                tbody.html(
                    '<tr><td colspan="12" class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div><span class="text-muted">Memuat data histori kendaraan...</span></td></tr>'
                );

                $.ajax({
                    url: "{{ route('vehicle.monitoring.history.data') }}",
                    type: "GET",
                    data: {
                        search: search,
                        target_location_id: targetLocationId,
                        jenis: jenis,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        transactionsData = response || [];
                        tbody.empty();

                        if (transactionsData.length === 0) {
                            tbody.html(
                                '<tr><td colspan="12" class="text-center py-5 text-muted"><i class="ri-inbox-line fs-24 d-block mb-2 text-secondary"></i>Tidak ada riwayat kendaraan yang sesuai dengan filter.</td></tr>'
                            );
                            return;
                        }

                        transactionsData.forEach((tx, idx) => {
                            // Format route chips
                            let routeChips = '-';
                            if (tx.tracking_steps && tx.tracking_steps.length > 0) {
                                routeChips = tx.tracking_steps.map((st, sIdx) => {
                                    return `<span class="badge bg-light text-dark border me-1 mb-1">${st.location_code} <small class="text-muted">(${st.duration_label})</small></span>`;
                                }).join(
                                    '<i class="ri-arrow-right-s-line text-muted me-1"></i>');
                            }

                            const row = `
                                <tr>
                                    <td><span class="fw-bold text-dark font-monospace">${idx + 1}</span></td>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary fs-12 fw-bold">${tx.no_pol}</span><br>
                                        <span class="fw-semibold text-dark">${tx.vendor}</span><br>
                                        <small class="text-muted"><i class="ri-user-line me-1"></i>${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-${jenisBadge(tx.jenis)} text-uppercase">${tx.jenis}</span><br>
                                        <small class="text-muted fw-semibold">${tx.item_name}</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">${tx.no_spb ?? '-'}</span><br>
                                        <small class="text-muted">${tx.qty_spb ? tx.qty_spb + ' Qty' : '-'}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info text-info fs-11">${tx.target_code} - ${tx.target_name}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1 fs-12">
                                            <span class="text-success" title="Check-In"><i class="ri-login-box-line me-1"></i>${tx.check_in}</span>
                                            <span class="text-danger" title="Check-Out"><i class="ri-logout-box-line me-1"></i>${tx.check_out}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-soft-secondary text-secondary fs-12 px-2 py-1" title="Waktu tunggu sebelum antri/aksi">
                                            <i class="ri-time-line me-1"></i>${tx.durasi_tunggu_label}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-soft-warning text-warning fs-12 px-2 py-1" title="Waktu antri">
                                            <i class="ri-hourglass-2-line me-1"></i>${tx.durasi_antri_label}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-soft-info text-info fs-12 px-2 py-1" title="Waktu aksi (Sampling / Bongkar / Muat)">
                                            <i class="ri-truck-line me-1"></i>${tx.durasi_aksi_label}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-soft-success text-success fs-12 fw-bold px-2 py-1" title="Total Durasi Siklus (Check-In sd Check-Out)">
                                            <i class="ri-timer-line me-1"></i>${tx.total_durasi_label}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="max-width: 250px; white-space: normal;">
                                            ${routeChips}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-soft-primary btn-detail-history" data-index="${idx}">
                                            <i class="ri-road-map-line me-1"></i>Detail
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    },
                    error: function(xhr) {
                        tbody.html(
                            '<tr><td colspan="12" class="text-center py-4 text-danger"><i class="ri-error-warning-line me-1"></i>Gagal memuat histori data. Silakan refresh halaman.</td></tr>'
                        );
                    }
                });
            }

            // Click Handler for Detail Modal
            $(document).on('click', '.btn-detail-history', function() {
                const idx = $(this).data('index');
                const tx = transactionsData[idx];
                if (!tx) return;

                // Title & Subtitle
                $('#modalSubtitle').html(
                    `<strong>${tx.no_transaction}</strong> | Plat: <span class="badge bg-soft-primary text-primary">${tx.no_pol}</span> | Driver: <strong>${tx.nama_driver || '-'}</strong>`
                    );

                // KPI Durations
                $('#modalKpiTunggu').text(tx.durasi_tunggu_label);
                $('#modalKpiAntri').text(tx.durasi_antri_label);
                $('#modalKpiAksi').text(tx.durasi_aksi_label);
                $('#modalKpiTotal').text(tx.total_durasi_label);

                // Vehicle & SPB Info
                $('#modalPlat').text(tx.no_pol);
                $('#modalVendor').text(tx.vendor);
                $('#modalDriver').text(`${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})`);
                $('#modalJenis').html(
                    `<span class="badge bg-${jenisBadge(tx.jenis)} text-uppercase">${tx.jenis}</span>`);
                $('#modalItem').text(tx.item_name);
                $('#modalSpb').text(`${tx.no_spb ?? '-'} / ${tx.qty_spb ?? '-'} Qty`);
                $('#modalTujuan').html(
                    `<span class="badge bg-soft-info text-info">${tx.target_code} - ${tx.target_name}</span>`
                    );

                // Timestamps Container
                const ts = tx.timestamps || {};
                let tsHtml = `
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="text-muted"><i class="ri-login-box-line text-success me-1"></i> Check-In (Timbangan)</span>
                        <span class="fw-bold text-dark">${ts.check_in || '-'}</span>
                    </div>
                `;

                if (ts.queue_taken && ts.queue_taken !== '-') {
                    tsHtml += `
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted"><i class="ri-coupon-3-line text-warning me-1"></i> Ambil Nomor Antrian</span>
                            <span class="fw-bold text-dark">${ts.queue_taken} ${tx.no_antrian ? `(#${tx.no_antrian})` : ''}</span>
                        </div>
                    `;
                }

                if (ts.start_sampling && ts.start_sampling !== '-') {
                    tsHtml += `
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted"><i class="ri-flask-line text-primary me-1"></i> Mulai Sampling QC</span>
                            <span class="fw-bold text-dark">${ts.start_sampling}</span>
                        </div>
                    `;
                }

                if (ts.finish_sampling && ts.finish_sampling !== '-') {
                    tsHtml += `
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted"><i class="ri-checkbox-circle-line text-info me-1"></i> Selesai Sampling QC</span>
                            <span class="fw-bold text-dark">${ts.finish_sampling}</span>
                        </div>
                    `;
                }

                if (ts.start_loading && ts.start_loading !== '-') {
                    const actionLabel = tx.jenis === 'bongkaran' ? 'Bongkar' : 'Muat';
                    tsHtml += `
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted"><i class="ri-play-circle-line text-info me-1"></i> Mulai ${actionLabel}</span>
                            <span class="fw-bold text-dark">${ts.start_loading}</span>
                        </div>
                    `;
                }

                if (ts.finish_loading && ts.finish_loading !== '-') {
                    const actionLabel = tx.jenis === 'bongkaran' ? 'Bongkar' : 'Muat';
                    tsHtml += `
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-muted"><i class="ri-stop-circle-line text-success me-1"></i> Selesai ${actionLabel}</span>
                            <span class="fw-bold text-dark">${ts.finish_loading}</span>
                        </div>
                    `;
                }

                tsHtml += `
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span class="text-muted"><i class="ri-logout-box-line text-danger me-1"></i> Check-Out (Timbangan)</span>
                        <span class="fw-bold text-dark">${ts.check_out || '-'}</span>
                    </div>
                `;
                $('#modalTimestampsContainer').html(tsHtml);

                // Action Breakdown
                if (tx.action_details && tx.action_details.length > 0) {
                    let actHtml = tx.action_details.map(act => `
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <div>
                                <strong class="text-dark">${act.label}</strong>
                                <small class="text-muted d-block">${act.start} s/d ${act.finish}</small>
                            </div>
                            <span class="badge bg-soft-info text-info fs-12 fw-bold">${act.duration_label}</span>
                        </div>
                    `).join('');
                    $('#modalActionBreakdown').html(actHtml);
                    $('#modalActionCard').show();
                } else {
                    $('#modalActionBreakdown').html(
                        '<span class="text-muted fst-italic">Tidak ada catatan durasi aksi terpisah.</span>'
                        );
                }

                // Tracking Steps (Perpindahan Lokasi)
                const steps = tx.tracking_steps || [];
                $('#modalStepCount').text(`${steps.length} Checkpoints`);

                if (steps.length === 0) {
                    $('#modalTrackingSteps').html(
                        '<div class="text-center text-muted py-4">Belum ada riwayat perpindahan checkpoint.</div>'
                        );
                } else {
                    let stepsHtml = `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="fs-12 text-muted">
                                        <th style="width: 50px;">#</th>
                                        <th>Lokasi Checkpoint</th>
                                        <th>Waktu Masuk</th>
                                        <th>Waktu Keluar</th>
                                        <th class="text-center">Durasi</th>
                                        <th>Catatan Status / Aktivitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    steps.forEach((st, sIdx) => {
                        let locBadge = 'bg-soft-primary text-primary';
                        if (st.location_code === 'TMB') locBadge =
                            'bg-soft-secondary text-secondary';
                        else if (st.location_code === 'QC') locBadge =
                            'bg-soft-warning text-warning';
                        else if (st.location_code === 'B006') locBadge = 'bg-soft-info text-info';
                        else if (st.location_code === 'A001') locBadge =
                            'bg-soft-success text-success';

                        stepsHtml += `
                            <tr>
                                <td><span class="badge rounded-pill bg-light text-dark border">${sIdx + 1}</span></td>
                                <td>
                                    <span class="badge ${locBadge} fw-bold me-1">${st.location_code}</span>
                                    <span class="fw-semibold text-dark fs-12">${st.location_name}</span>
                                </td>
                                <td><span class="fs-12 text-dark font-monospace">${st.arrival_full}</span></td>
                                <td><span class="fs-12 text-dark font-monospace">${st.departure_full}</span></td>
                                <td class="text-center">
                                    <span class="badge bg-soft-info text-info fs-11 fw-bold">${st.duration_label}</span>
                                </td>
                                <td>
                                    <small class="text-muted" style="white-space: normal;">${st.status_notes || '-'}</small>
                                </td>
                            </tr>
                        `;
                    });

                    stepsHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    $('#modalTrackingSteps').html(stepsHtml);
                }

                // Show Modal
                const modal = new bootstrap.Modal(document.getElementById('detailHistoryModal'));
                modal.show();
            });

            // Filter Handlers
            let reportSearchTimer;
            $('#reportSearch').on('keyup', function() {
                clearTimeout(reportSearchTimer);
                reportSearchTimer = setTimeout(loadReports, 400);
            });

            $('#reportLocation, #reportJenis, #reportStartDate, #reportEndDate').on('change', function() {
                loadReports();
            });

            $('#btnReportReset').on('click', function() {
                $('#reportSearch').val('');
                $('#reportLocation').val('');
                $('#reportJenis').val('');
                $('#reportStartDate').val('');
                $('#reportEndDate').val('');
                loadReports();
            });

            // Initial load
            loadReports();
        });
    </script>
@endsection
