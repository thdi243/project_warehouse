@extends('layouts.app')

@section('title', '| Laporan Scan Kempu Produksi')

@section('styles')
    <style>
        .progress-reused {
            height: 6px;
            border-radius: 3px;
            background-color: #e2e8f0;
            overflow: hidden;
        }

        .nav-custom-pills .nav-link {
            color: #475569;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-custom-pills .nav-link:hover {
            background-color: #e2e8f0;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        .nav-custom-pills .nav-link.active {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.25);
        }

        .nav-custom-pills .nav-link .badge-counter {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 9999px;
            background-color: #dbeafe;
            color: #1e40af;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .nav-custom-pills .nav-link.active .badge-counter {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }

        .filter-container {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            background-color: #ffffff;
        }

        .filter-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
            display: block;
        }

        .date-chip {
            cursor: pointer;
            font-size: 11px;
            font-weight: 500;
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }

        .date-chip:hover {
            border-color: #cbd5e1;
            background-color: #f8fafc;
        }

        .date-chip.active {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
            font-weight: 600;
        }

        .timeline-container {
            position: relative;
            padding-left: 28px;
        }

        .timeline-container::before {
            content: '';
            position: absolute;
            top: 5px;
            bottom: 5px;
            left: 10px;
            width: 2px;
            background: #cbd5e1;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -24px;
            top: 4px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #2563eb;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 2px #bfdbfe;
        }

        .timeline-dot.force {
            background: #ef4444;
            box-shadow: 0 0 0 2px #fecaca;
        }

        .timeline-dot.success {
            background: #10b981;
            box-shadow: 0 0 0 2px #a7f3d0;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Breadcrumb Header -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0 fw-bold">Laporan Scan Kempu Produksi</h4>
                            <span class="text-muted fs-12">Rekapitulasi seluruh siklus pemindaian kempu, pengisian, dan audit Force Scan Produksi</span>
                        </div>
                        <div class="page-title-right d-flex align-items-center gap-2 mt-2 mt-sm-0">
                            <a href="{{ route('kempu.produksi.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-qr-scan-2-line me-1"></i> Buka Menu Scanner
                            </a>
                            <button type="button" class="btn btn-success btn-sm" id="btnExportCsv">
                                <i class="ri-file-excel-2-line me-1"></i> Export CSV
                            </button>
                            <button type="button" class="btn btn-light btn-sm" id="btnRefreshData">
                                <i class="ri-refresh-line me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Metric Cards Row -->
            <div class="row g-3 mb-4">
                <!-- Card 1: Stok Kempu Terkini di Produksi -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate border-0 shadow-sm h-100" style="border-left: 4px solid #2563eb !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Stok di Area Produksi</p>
                                    <h3 class="fw-bold mb-0 text-dark" id="statCurrentProduksi">- <span class="fs-13 fw-normal text-muted">Unit</span></h3>
                                    <span class="text-muted fs-11 mt-1 d-block">Sedang aktif dalam alur produksi</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-20">
                                        <i class="ri-building-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Total Scan Hari Ini & Keseluruhan -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Total Scan Produksi</p>
                                    <h3 class="fw-bold mb-0 text-success" id="statTodayScan">- <span class="fs-13 fw-normal text-muted">Hari Ini</span></h3>
                                    <span class="badge bg-soft-success text-success fs-11 mt-1" id="statAllScan">Total: - Scan</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-success text-success rounded-circle fs-20">
                                        <i class="ri-barcode-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Filling Kempu Selesai -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate border-0 shadow-sm h-100" style="border-left: 4px solid #06b6d4 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Pengisian (Filling) Kempu</p>
                                    <h3 class="fw-bold mb-0 text-info" id="statFillingToday">- <span class="fs-13 fw-normal text-muted">Hari Ini</span></h3>
                                    <span class="badge bg-soft-info text-info fs-11 mt-1" id="statFillingAll">Total: - Selesai</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded-circle fs-20">
                                        <i class="ri-battery-2-charge-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Force Scan Produksi (Otoritas Khusus) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate border-0 shadow-sm h-100" style="border-left: 4px solid #ef4444 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold fs-12 text-danger mb-1"><i class="ri-shield-flash-line me-1"></i> Force Scan Produksi</p>
                                    <h3 class="fw-bold mb-0 text-danger" id="statForceToday">- <span class="fs-13 fw-normal text-muted">Hari Ini</span></h3>
                                    <span class="badge bg-soft-danger text-danger fs-11 mt-1" id="statForceAll">Total: - Override</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-danger text-danger rounded-circle fs-20">
                                        <i class="ri-shield-flash-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Filter & Table Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <!-- Nav Tabs Mode Switcher -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
                        <ul class="nav nav-custom-pills gap-2" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tabHistoryLink" href="javascript:void(0)" onclick="switchViewMode('history')">
                                    <i class="ri-history-line"></i> Riwayat Transaksi Scan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tabCurrentLink" href="javascript:void(0)" onclick="switchViewMode('current')">
                                    <i class="ri-inbox-archive-line"></i> Kempu di Produksi Saat Ini
                                </a>
                            </li>
                        </ul>

                        <div class="text-muted fs-12">
                            Menampilkan data per <span class="fw-bold text-dark">{{ now()->format('d M Y') }}</span>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="filter-container mb-4">
                        <div class="row g-3 align-items-end">
                            <!-- Filter 1: Proses Produksi -->
                            <div class="col-xl-3 col-md-4 col-sm-6" id="filterProcessContainer">
                                <label class="filter-label"><i class="ri-filter-3-line me-1"></i> Proses / Alur:</label>
                                <select class="form-select form-select-sm" id="filterAction">
                                    <option value="all">Semua Alur Produksi</option>
                                    <option value="transfer-in-from-wpm">Transfer in from WPM</option>
                                    <option value="cuci-kempu">Cuci Kempu</option>
                                    <option value="scan-1-filling-kempu">Filling Kempu (Scan 1)</option>
                                    <option value="transfer-out-to-wfg">Transfer Out to WFG</option>
                                    <option value="transfer-in-from-wfg">Transfer in from WFG (Retur)</option>
                                    <option value="create-ba-scrap">Create BA Scrap</option>
                                    <option value="prod-force" class="fw-bold text-danger">⚡ Force Scan Produksi Saja</option>
                                </select>
                            </div>

                            <!-- Filter 2: Opsi Force Scan Saja -->
                            <div class="col-xl-2 col-md-3 col-sm-6" id="filterForceScanContainer">
                                <label class="filter-label"><i class="ri-shield-flash-line me-1"></i> Tipe Scan:</label>
                                <select class="form-select form-select-sm" id="filterIsForce">
                                    <option value="all">Semua Tipe Scan</option>
                                    <option value="no">Normal Scan Saja</option>
                                    <option value="yes" class="text-danger fw-bold">⚡ Khusus Force Scan</option>
                                </select>
                            </div>

                            <!-- Filter 3: Quick Date Chips -->
                            <div class="col-xl-4 col-md-5 col-sm-12" id="filterDateContainer">
                                <label class="filter-label"><i class="ri-calendar-line me-1"></i> Filter Tanggal:</label>
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="date-chip active" data-range="today">Hari Ini</span>
                                    <span class="date-chip" data-range="7d">7 Hari</span>
                                    <span class="date-chip" data-range="30d">30 Hari</span>
                                    <span class="date-chip" data-range="month">Bulan Ini</span>
                                    <span class="date-chip" data-range="all">Semua</span>
                                </div>
                                <div class="row g-1 mt-1">
                                    <div class="col-6">
                                        <input type="date" class="form-control form-control-sm" id="filterStartDate" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" class="form-control form-control-sm" id="filterEndDate" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Filter 4: Search Input -->
                            <div class="col-xl-3 col-md-6 col-sm-12">
                                <label class="filter-label"><i class="ri-search-line me-1"></i> Cari Barcode / Operator:</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="filterSearch" placeholder="Barcode, RFID, user...">
                                    <button class="btn btn-primary" type="button" id="btnApplyFilter">
                                        <i class="ri-search-2-line"></i> Cari
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" id="btnResetFilter" title="Reset Filter">
                                        <i class="ri-refresh-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Responsive Area -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap mb-0" id="tableProduksiReport">
                            <thead class="table-light text-muted">
                                <tr id="tableHeaderRow">
                                    <th scope="col" style="width: 50px;">No</th>
                                    <th scope="col">Waktu & Tanggal</th>
                                    <th scope="col">ID Kempu / RFID</th>
                                    <th scope="col">Proses / Aksi</th>
                                    <th scope="col">Alur Lokasi</th>
                                    <th scope="col">Reused</th>
                                    <th scope="col">Kondisi</th>
                                    <th scope="col">Operator</th>
                                    <th scope="col">Catatan</th>
                                    <th scope="col" class="text-center" style="width: 90px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableProduksiBody">
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Memuat data laporan...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Section -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 pt-3 border-top">
                        <div class="text-muted fs-12" id="paginationInfo">
                            Menampilkan 0 data
                        </div>
                        <ul class="pagination pagination-sm mb-0" id="paginationControls">
                            <!-- Dynamic pagination links -->
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- MODAL TIMELINE TRACEABILITY KEMPU -->
    <div class="modal fade" id="modalTimeline" tabindex="-1" aria-labelledby="modalTimelineLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-15 fw-bold" id="modalTimelineLabel">
                        <i class="ri-history-line me-1"></i> Traceability Riwayat Kempu: <span id="modalTimelineId" class="font-monospace">-</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Quick Info Kempu -->
                    <div class="row g-2 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-3 text-center">
                            <span class="text-muted fs-11 d-block text-uppercase">Lokasi Terkini</span>
                            <span class="badge bg-soft-primary text-primary fs-12 fw-bold" id="kempuDetailLoc">-</span>
                        </div>
                        <div class="col-3 text-center">
                            <span class="text-muted fs-11 d-block text-uppercase">Status Saat Ini</span>
                            <span class="badge bg-light text-dark border fs-12" id="kempuDetailStatus">-</span>
                        </div>
                        <div class="col-3 text-center">
                            <span class="text-muted fs-11 d-block text-uppercase">Siklus Reused</span>
                            <span class="fw-bold fs-13 text-dark" id="kempuDetailReused">-</span>
                        </div>
                        <div class="col-3 text-center">
                            <span class="text-muted fs-11 d-block text-uppercase">Kondisi Fisik</span>
                            <span class="badge bg-soft-success text-success fs-12" id="kempuDetailCond">-</span>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-body fs-13"><i class="ri-git-commit-line me-1"></i> Log Perjalanan (Audit Trail):</h6>
                    
                    <div class="timeline-container" id="timelineList">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let currentViewMode = 'history';
            let currentPage = 1;
            let currentPerPage = 20;

            // Load KPI stats awal
            loadStats();

            // Load Table awal
            loadTableData();

            // Switch Tab Mode (History vs Current)
            window.switchViewMode = function(mode) {
                if (currentViewMode === mode) return;
                currentViewMode = mode;
                currentPage = 1;

                if (mode === 'history') {
                    $('#tabHistoryLink').addClass('active');
                    $('#tabCurrentLink').removeClass('active');
                    $('#filterProcessContainer').show();
                    $('#filterForceScanContainer').show();
                    $('#filterDateContainer').show();

                    // Header untuk History
                    $('#tableHeaderRow').html(`
                        <th scope="col" style="width: 50px;">No</th>
                        <th scope="col">Waktu & Tanggal</th>
                        <th scope="col">ID Kempu / RFID</th>
                        <th scope="col">Proses / Aksi</th>
                        <th scope="col">Alur Lokasi</th>
                        <th scope="col">Reused</th>
                        <th scope="col">Kondisi</th>
                        <th scope="col">Operator</th>
                        <th scope="col">Catatan</th>
                        <th scope="col" class="text-center" style="width: 90px;">Aksi</th>
                    `);
                } else {
                    $('#tabCurrentLink').addClass('active');
                    $('#tabHistoryLink').removeClass('active');
                    $('#filterProcessContainer').hide();
                    $('#filterForceScanContainer').hide();
                    $('#filterDateContainer').hide();

                    // Header untuk Current
                    $('#tableHeaderRow').html(`
                        <th scope="col" style="width: 50px;">No</th>
                        <th scope="col">ID Kempu</th>
                        <th scope="col">RFID</th>
                        <th scope="col">Status Saat Ini</th>
                        <th scope="col">Lokasi</th>
                        <th scope="col">Siklus Reused</th>
                        <th scope="col">Kondisi</th>
                        <th scope="col">Terakhir Diperbarui</th>
                        <th scope="col" class="text-center" style="width: 90px;">Aksi</th>
                    `);
                }

                loadTableData();
            };

            // Load KPI Stats
            function loadStats() {
                $.ajax({
                    url: "{{ route('kempu.produksi.report.stats') }}",
                    method: "GET",
                    success: function(res) {
                        if (res.status && res.data) {
                            const d = res.data;
                            $('#statCurrentProduksi').html(`${d.total_current_produksi ?? 0} <span class="fs-13 fw-normal text-muted">Unit</span>`);
                            $('#statTodayScan').html(`${d.total_today ?? 0} <span class="fs-13 fw-normal text-muted">Hari Ini</span>`);
                            $('#statAllScan').text(`Total: ${d.total_all ?? 0} Scan`);
                            $('#statFillingToday').html(`${d.total_filling_today ?? 0} <span class="fs-13 fw-normal text-muted">Hari Ini</span>`);
                            $('#statFillingAll').text(`Total: ${d.total_filling_all ?? 0} Selesai`);
                            $('#statForceToday').html(`${d.total_force_today ?? 0} <span class="fs-13 fw-normal text-muted">Hari Ini</span>`);
                            $('#statForceAll').text(`Total: ${d.total_force_all ?? 0} Override`);
                        }
                    }
                });
            }

            // Load Table Data
            function loadTableData(page = 1) {
                currentPage = page;
                const tbody = $('#tableProduksiBody');
                tbody.html(`
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            Memuat data laporan...
                        </td>
                    </tr>
                `);

                const params = {
                    view_mode: currentViewMode,
                    page: currentPage,
                    per_page: currentPerPage,
                    action_filter: $('#filterAction').val(),
                    is_force_scan: $('#filterIsForce').val(),
                    start_date: $('#filterStartDate').val(),
                    end_date: $('#filterEndDate').val(),
                    search: $('#filterSearch').val().trim(),
                };

                $.ajax({
                    url: "{{ route('kempu.produksi.report.data') }}",
                    method: "GET",
                    data: params,
                    success: function(res) {
                        if (!res.status || !res.data || res.data.length === 0) {
                            tbody.html(`
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="ri-inbox-line fs-24 d-block mb-1"></i>
                                        Tidak ada data yang sesuai dengan filter pencarian.
                                    </td>
                                </tr>
                            `);
                            $('#paginationInfo').text('Menampilkan 0 data');
                            $('#paginationControls').empty();
                            return;
                        }

                        renderTable(res.data, res.pagination);
                    },
                    error: function() {
                        tbody.html(`
                            <tr>
                                <td colspan="10" class="text-center py-4 text-danger">
                                    <i class="ri-error-warning-line fs-20 me-1"></i> Gagal memuat data laporan.
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            // Render Rows
            function renderTable(items, pagination) {
                const tbody = $('#tableProduksiBody');
                tbody.empty();

                const startIdx = (pagination.current_page - 1) * pagination.per_page;

                items.forEach((item, index) => {
                    const rowNo = startIdx + index + 1;

                    if (currentViewMode === 'history') {
                        const isForce = item.stage === 'PRODUKSI_FORCE' || (item.action && item.action.includes('[FORCE SCAN]'));
                        
                        let badgeAction = `<span class="badge bg-soft-primary text-primary px-2 py-1">${item.action ?? '-'}</span>`;
                        if (isForce) {
                            badgeAction = `<span class="badge bg-danger text-white px-2 py-1"><i class="ri-shield-flash-line me-1"></i>${item.action ?? 'FORCE SCAN'}</span>`;
                        } else if (item.action && item.action.includes('Filling')) {
                            badgeAction = `<span class="badge bg-soft-success text-success px-2 py-1"><i class="ri-battery-2-charge-line me-1"></i>${item.action}</span>`;
                        } else if (item.action && item.action.includes('Cuci')) {
                            badgeAction = `<span class="badge bg-soft-info text-info px-2 py-1"><i class="ri-water-flash-line me-1"></i>${item.action}</span>`;
                        } else if (item.action && item.action.includes('BA Scrap')) {
                            badgeAction = `<span class="badge bg-soft-danger text-danger px-2 py-1"><i class="ri-delete-bin-line me-1"></i>${item.action}</span>`;
                        }

                        const waktuStr = item.created_at ? new Date(item.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';
                        const reused = item.reused_count ?? 0;
                        const operator = item.operator_display_name || (item.created_by ? item.created_by.nama_lengkap || item.created_by.username : 'System');

                        tbody.append(`
                            <tr>
                                <td class="text-center fw-medium">${rowNo}</td>
                                <td>${waktuStr}</td>
                                <td>
                                    <span class="fw-bold font-monospace text-primary">${item.id_kempu ?? '-'}</span>
                                    ${item.master_kempu && item.master_kempu.rfid ? `<br><small class="text-muted font-monospace">${item.master_kempu.rfid}</small>` : ''}
                                </td>
                                <td>${badgeAction}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">${item.from_location ?? '-'}</span>
                                    <i class="ri-arrow-right-line mx-1 text-muted"></i>
                                    <span class="badge bg-light text-dark border">${item.to_location ?? '-'}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">${reused}/21</span>
                                    <div class="progress progress-reused mt-1" style="width: 50px;">
                                        <div class="progress-bar ${reused >= 18 ? 'bg-danger' : 'bg-primary'}" role="progressbar" style="width: ${(reused/21)*100}%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge ${item.condition === 'NOT_OK' ? 'bg-danger' : 'bg-success'}">${item.condition ?? 'OK'}</span>
                                </td>
                                <td><small class="fw-medium">${operator}</small></td>
                                <td><small class="text-muted text-wrap d-inline-block" style="max-width: 200px;">${item.notes ?? '-'}</small></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" onclick="viewTimeline('${item.id_kempu}')" title="Lihat Riwayat Lengkap">
                                        <i class="ri-history-line"></i> Riwayat
                                    </button>
                                </td>
                            </tr>
                        `);
                    } else {
                        // Current View Mode
                        const main = item.main || {};
                        const reused = main.reused_count ?? 0;
                        const waktuStr = item.updated_at ? new Date(item.updated_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';

                        tbody.append(`
                            <tr>
                                <td class="text-center fw-medium">${rowNo}</td>
                                <td><span class="fw-bold font-monospace text-primary">${item.id_kempu}</span></td>
                                <td><span class="font-monospace text-muted">${item.rfid ?? '-'}</span></td>
                                <td><span class="badge bg-soft-primary text-primary px-2 py-1">${main.current_status ?? '-'}</span></td>
                                <td><span class="badge bg-light text-dark border">${main.current_location ?? 'PRODUKSI'}</span></td>
                                <td>
                                    <span class="fw-bold">${reused}/21</span>
                                    <div class="progress progress-reused mt-1" style="width: 50px;">
                                        <div class="progress-bar ${reused >= 18 ? 'bg-danger' : 'bg-primary'}" role="progressbar" style="width: ${(reused/21)*100}%"></div>
                                    </div>
                                </td>
                                <td><span class="badge ${main.condition === 'NOT_OK' ? 'bg-danger' : 'bg-success'}">${main.condition ?? 'OK'}</span></td>
                                <td>${waktuStr}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" onclick="viewTimeline('${item.id_kempu}')" title="Lihat Riwayat">
                                        <i class="ri-history-line"></i> Riwayat
                                    </button>
                                </td>
                            </tr>
                        `);
                    }
                });

                // Update pagination summary
                const startNum = startIdx + 1;
                const endNum = Math.min(startIdx + pagination.per_page, pagination.total);
                $('#paginationInfo').text(`Menampilkan ${startNum} - ${endNum} dari ${pagination.total} data`);

                renderPagination(pagination);
            }

            // Render Pagination Buttons
            function renderPagination(pagination) {
                const nav = $('#paginationControls');
                nav.empty();

                if (pagination.last_page <= 1) return;

                const cur = pagination.current_page;
                const last = pagination.last_page;

                // Tombol Prev
                nav.append(`
                    <li class="page-item ${cur === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadTableData(${cur - 1})"><i class="ri-arrow-left-s-line"></i></a>
                    </li>
                `);

                let startPage = Math.max(1, cur - 2);
                let endPage = Math.min(last, cur + 2);

                for (let p = startPage; p <= endPage; p++) {
                    nav.append(`
                        <li class="page-item ${p === cur ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0)" onclick="loadTableData(${p})">${p}</a>
                        </li>
                    `);
                }

                // Tombol Next
                nav.append(`
                    <li class="page-item ${cur === last ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadTableData(${cur + 1})"><i class="ri-arrow-right-s-line"></i></a>
                    </li>
                `);
            }

            // Quick Date Chips Click Handler
            $('.date-chip').on('click', function() {
                $('.date-chip').removeClass('active');
                $(this).addClass('active');

                const range = $(this).data('range');
                const today = new Date();
                let start = new Date();
                let end = new Date();

                if (range === 'today') {
                    // start & end hari ini
                } else if (range === '7d') {
                    start.setDate(today.getDate() - 7);
                } else if (range === '30d') {
                    start.setDate(today.getDate() - 30);
                } else if (range === 'month') {
                    start = new Date(today.getFullYear(), today.getMonth(), 1);
                } else if (range === 'all') {
                    $('#filterStartDate').val('');
                    $('#filterEndDate').val('');
                    loadTableData(1);
                    return;
                }

                const formatDate = (d) => d.toISOString().split('T')[0];
                $('#filterStartDate').val(formatDate(start));
                $('#filterEndDate').val(formatDate(end));

                loadTableData(1);
            });

            // Filter Apply
            $('#btnApplyFilter').on('click', function() {
                loadTableData(1);
            });

            $('#filterAction, #filterIsForce, #filterStartDate, #filterEndDate').on('change', function() {
                loadTableData(1);
            });

            $('#filterSearch').on('keypress', function(e) {
                if (e.which === 13) {
                    loadTableData(1);
                }
            });

            // Reset Filter
            $('#btnResetFilter').on('click', function() {
                $('#filterAction').val('all');
                $('#filterIsForce').val('all');
                $('#filterSearch').val('');
                $('.date-chip[data-range="today"]').trigger('click');
            });

            $('#btnRefreshData').on('click', function() {
                loadStats();
                loadTableData(currentPage);
            });

            // Export CSV
            $('#btnExportCsv').on('click', function() {
                const params = new URLSearchParams({
                    action_filter: $('#filterAction').val(),
                    is_force_scan: $('#filterIsForce').val(),
                    start_date: $('#filterStartDate').val(),
                    end_date: $('#filterEndDate').val(),
                    search: $('#filterSearch').val().trim(),
                });

                window.location.href = "{{ route('kempu.produksi.report.export') }}?" + params.toString();
            });

            // Timeline Modal Handler
            window.viewTimeline = function(idKempu) {
                $('#modalTimelineId').text(idKempu);
                $('#timelineList').html(`
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat riwayat...
                    </div>
                `);

                const timelineModal = new bootstrap.Modal(document.getElementById('modalTimeline'));
                timelineModal.show();

                $.ajax({
                    url: `/api/kempu/traceability/history/${idKempu}`,
                    method: "GET",
                    success: function(res) {
                        if (!res.status || !res.kempu) {
                            $('#timelineList').html('<div class="alert alert-warning">Data riwayat tidak ditemukan.</div>');
                            return;
                        }

                        const kempu = res.kempu;
                        const main = kempu.main || {};

                        $('#kempuDetailLoc').text(main.current_location || '-');
                        $('#kempuDetailStatus').text(main.current_status || '-');
                        $('#kempuDetailReused').text(`${main.reused_count ?? 0}/21`);
                        $('#kempuDetailCond').text(main.condition || 'OK').removeClass('bg-soft-danger text-danger bg-soft-success text-success').addClass(main.condition === 'NOT_OK' ? 'bg-soft-danger text-danger' : 'bg-soft-success text-success');

                        const histories = res.histories || [];
                        if (histories.length === 0) {
                            $('#timelineList').html('<div class="text-muted text-center py-3">Belum ada riwayat aktivitas.</div>');
                            return;
                        }

                        let html = '';
                        histories.forEach(h => {
                            const isForce = (h.stage === 'PRODUKSI_FORCE' || (h.action && h.action.includes('[FORCE SCAN]')));
                            const dotClass = isForce ? 'force' : (h.action_result === 'OK' ? 'success' : '');
                            const timeStr = h.created_at ? new Date(h.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';
                            const opName = h.created_by ? (h.created_by.nama_lengkap || h.created_by.username) : (h.metadata?.operator_name || 'System');

                            html += `
                                <div class="timeline-item">
                                    <div class="timeline-dot ${dotClass}"></div>
                                    <div class="bg-light p-3 rounded border">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold ${isForce ? 'text-danger' : 'text-primary'} fs-13">
                                                ${isForce ? '<i class="ri-shield-flash-line me-1"></i>' : ''} ${h.action}
                                            </span>
                                            <small class="text-muted">${timeStr}</small>
                                        </div>
                                        <div class="fs-12 text-muted mb-2">
                                            <span><i class="ri-map-pin-line me-1"></i> ${h.from_location ?? '-'} &rarr; ${h.to_location ?? '-'}</span>
                                            <span class="mx-2">&bull;</span>
                                            <span><i class="ri-user-line me-1"></i> ${opName}</span>
                                        </div>
                                        ${h.notes ? `<div class="p-2 bg-white rounded border fs-12 text-dark">${h.notes}</div>` : ''}
                                    </div>
                                </div>
                            `;
                        });

                        $('#timelineList').html(html);
                    },
                    error: function() {
                        $('#timelineList').html('<div class="alert alert-danger">Gagal menghubungi server untuk riwayat.</div>');
                    }
                });
            };
        });
    </script>
@endsection
