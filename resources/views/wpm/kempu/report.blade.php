@extends('layouts.app')

@section('title', '| Report Scan Kempu WPM')

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
            color: #1d4ed8;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .nav-custom-pills .nav-link.active .badge-counter {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }

        .filter-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
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
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }

        .date-chip:hover {
            background-color: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
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

        .timeline-dot.transfer-in {
            background: #16a34a;
            box-shadow: 0 0 0 2px #bbf7d0;
        }

        .timeline-dot.warning {
            background: #f59e0b;
            box-shadow: 0 0 0 2px #fde68a;
        }

        .timeline-dot.danger {
            background: #ef4444;
            box-shadow: 0 0 0 2px #fca5a5;
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
                            <h4 class="mb-sm-0 fw-bold">Laporan Scan Kempu WPM</h4>
                            <span class="text-muted fs-12">Monitoring alur Transfer In & Out kempu pada area Warehouse Packaging Material</span>
                        </div>
                        <div class="page-title-right d-flex align-items-center gap-2 mt-2 mt-sm-0">
                            <a href="{{ route('wpm.kempu.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-qr-scan-2-line me-1"></i> Buka Scanner WPM
                            </a>
                            <button type="button" class="btn btn-success btn-sm" id="btnExportCsv">
                                <i class="ri-file-excel-2-line me-1"></i> Export CSV / Excel
                            </button>
                            <button type="button" class="btn btn-light btn-sm" onclick="window.print()">
                                <i class="ri-printer-line me-1"></i> Cetak
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Metric Cards -->
            <div class="row g-3 mb-4">
                <!-- Card 1: Stok Kempu Terkini di WPM -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-left: 4px solid #2563eb !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Kempu di WPM Saat Ini</p>
                                    <h3 class="fw-bold mb-0 text-dark">{{ $totalCurrentWpm }} <span class="fs-13 fw-normal text-muted">Unit</span></h3>
                                    <span class="text-muted fs-11 mt-1 d-block">Stok fisik kempu di Packaging Material</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title rounded-circle fs-20" style="background: #dbeafe; color: #1e40af;">
                                        <i class="bx bx-package"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Transfer In Dari Warehouse PAS -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-left: 4px solid #16a34a !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Transfer In (Dari PAS)</p>
                                    <h3 class="fw-bold mb-0 text-success">{{ $totalTransferInToday }} <span class="fs-13 fw-normal text-muted">Hari Ini</span></h3>
                                    <span class="badge bg-soft-success text-success fs-11 mt-1">Total: {{ $totalTransferInAll }} Scan</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title rounded-circle fs-20" style="background: #dcfce7; color: #16a34a;">
                                        <i class="ri-arrow-left-down-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Transfer Out Ke Produksi -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-left: 4px solid #3b82f6 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Transfer Out (Ke Produksi)</p>
                                    <h3 class="fw-bold mb-0 text-primary">{{ $totalTransferOutToday }} <span class="fs-13 fw-normal text-muted">Hari Ini</span></h3>
                                    <span class="badge bg-soft-primary text-primary fs-11 mt-1">Total: {{ $totalTransferOutAll }} Scan</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title rounded-circle fs-20" style="background: #dbeafe; color: #1d4ed8;">
                                        <i class="ri-arrow-right-up-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Reused Alert -->
                <div class="col-xl-3 col-md-6">
                    <div class="card card-animate border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%); border-left: 4px solid #f59e0b !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Peringatan Reused (&gt;=18x)</p>
                                    <h3 class="fw-bold mb-0 text-warning">{{ $totalWarning }} <span class="fs-13 fw-normal text-muted">Unit</span></h3>
                                    <span class="text-muted fs-11 mt-1 d-block">Mendekati limit siklus 21x di WPM</span>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title rounded-circle fs-20" style="background: #fef3c7; color: #b45309;">
                                        <i class="ri-alarm-warning-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation & Main Content Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <!-- Navigation Custom Pills -->
                        <ul class="nav nav-pills nav-custom-pills" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tabHistoryLink" data-bs-toggle="pill" href="#tabHistory" role="tab">
                                    <i class="ri-history-line fs-15"></i>
                                    <span>Log Riwayat Scan WPM</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tabCurrentLink" data-bs-toggle="pill" href="#tabCurrent" role="tab">
                                    <i class="bx bx-package fs-15"></i>
                                    <span>Stok Kempu di WPM Saat Ini</span>
                                    <span class="badge-counter">{{ $totalCurrentWpm }}</span>
                                </a>
                            </li>
                        </ul>

                        <!-- Right Header Info -->
                        <div class="d-none d-md-flex align-items-center gap-2 text-muted fs-12">
                            <i class="ri-information-line text-primary"></i>
                            <span>Klik ID Kempu untuk melihat riwayat perjalanan alur</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- FILTER BAR CONTAINER -->
                    <div class="filter-container mb-4">
                        <div class="row g-3 align-items-end">
                            <!-- Search -->
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="filter-label">Pencarian</label>
                                <div class="position-relative">
                                    <input type="text" id="filterSearch" class="form-control form-control-sm ps-4"
                                        placeholder="Cari ID, RFID, Catatan...">
                                    <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-2 text-muted fs-14"></i>
                                </div>
                            </div>

                            <!-- Filter Aksi (khusus Log History) -->
                            <div class="col-xl-3 col-lg-4 col-md-6" id="wrapperFilterAction">
                                <label class="filter-label">Tipe Aksi / Flow</label>
                                <select id="filterAction" class="form-select form-select-sm">
                                    <option value="all">Semua Aksi (Transfer In & Out)</option>
                                    @foreach ($cards as $c)
                                        <option value="{{ $c['title'] }}">{{ $c['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Reused (khusus Stok Terkini) -->
                            <div class="col-xl-3 col-lg-4 col-md-6 d-none" id="wrapperFilterReused">
                                <label class="filter-label">Status Siklus Reused</label>
                                <select id="filterReusedStatus" class="form-select form-select-sm">
                                    <option value="">Semua Siklus</option>
                                    <option value="normal">Normal (&lt; 18x)</option>
                                    <option value="warning">Peringatan (18x - 20x)</option>
                                    <option value="max">Maksimal (&ge; 21x)</option>
                                </select>
                            </div>

                            <!-- Date Range (khusus Log History) -->
                            <div class="col-xl-4 col-lg-5 col-md-8" id="wrapperFilterDate">
                                <label class="filter-label">Rentang Tanggal Scan</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" id="filterStartDate" class="form-control">
                                    <span class="input-group-text bg-light border-start-0 border-end-0 text-muted px-2">s/d</span>
                                    <input type="date" id="filterEndDate" class="form-control">
                                </div>
                            </div>

                            <!-- Reset Button -->
                            <div class="col-xl-2 col-lg-3 col-md-4 d-flex">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="btnResetFilter" title="Reset Semua Filter">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </button>
                            </div>
                        </div>

                        <!-- Quick Date Presets (khusus Log History) -->
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-2 border-top" id="wrapperQuickDate">
                            <span class="fs-11 text-muted fw-semibold me-1"><i class="ri-calendar-event-line me-1"></i>Preset:</span>
                            <span class="date-chip" data-preset="today">Hari Ini</span>
                            <span class="date-chip" data-preset="yesterday">Kemarin</span>
                            <span class="date-chip" data-preset="last7">7 Hari Terakhir</span>
                            <span class="date-chip" data-preset="month">Bulan Ini</span>
                            <span class="date-chip active" data-preset="all">Semua Riwayat</span>
                        </div>
                    </div>

                    <!-- TAB CONTENT -->
                    <div class="tab-content">
                        <!-- TAB 1: LOG RIWAYAT SCAN WPM -->
                        <div class="tab-pane fade show active" id="tabHistory" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-nowrap mb-0" id="tableHistory">
                                    <thead class="table-light fs-12 text-uppercase text-muted">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Waktu Scan</th>
                                            <th>ID Kempu</th>
                                            <th>RFID</th>
                                            <th>Aksi / Status Flow</th>
                                            <th>Asal &rarr; Tujuan</th>
                                            <th>Reused</th>
                                            <th>Kondisi & Kelengkapan</th>
                                            <th>Catatan / Info</th>
                                            <th>Petugas</th>
                                            <th class="text-center" style="width: 80px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyHistory">
                                        <tr>
                                            <td colspan="11" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                <span class="ms-2 text-muted">Memuat data scan WPM...</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination History -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top" id="paginationHistory">
                                <div class="text-muted fs-12" id="infoPaginationHistory">-</div>
                                <ul class="pagination pagination-sm mb-0" id="pagesHistory"></ul>
                            </div>
                        </div>

                        <!-- TAB 2: STOK KEMPU DI WPM SAAT INI -->
                        <div class="tab-pane fade" id="tabCurrent" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-nowrap mb-0" id="tableCurrent">
                                    <thead class="table-light fs-12 text-uppercase text-muted">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>ID Kempu</th>
                                            <th>RFID</th>
                                            <th>No SPB</th>
                                            <th>Status Siklus Saat Ini</th>
                                            <th>Terakhir Di-Scan</th>
                                            <th>Siklus Reused</th>
                                            <th>Aksi Terakhir</th>
                                            <th class="text-center" style="width: 100px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyCurrent">
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                <span class="ms-2 text-muted">Memuat stok kempu WPM...</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Current -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top" id="paginationCurrent">
                                <div class="text-muted fs-12" id="infoPaginationCurrent">-</div>
                                <ul class="pagination pagination-sm mb-0" id="pagesCurrent"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Riwayat Lifecycle Kempu (Timeline) -->
    <div class="modal fade" id="modalKempuHistory" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-xs">
                            <span class="avatar-title bg-primary text-white rounded-circle fs-14">
                                <i class="ri-route-line"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="modal-title fs-15 text-white mb-0" id="modalTitle">Riwayat Perjalanan Kempu</h5>
                            <span class="fs-12 text-white-50" id="modalSubtitle">ID Kempu: -</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Info Summary Box -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-sm-3 col-6">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">ID Kempu</div>
                                    <div class="fw-bold fs-14 text-dark font-monospace" id="modalInfoId">-</div>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">RFID Tag</div>
                                    <div class="fw-semibold fs-13 text-dark font-monospace" id="modalInfoRfid">-</div>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">Lokasi Terkini</div>
                                    <div id="modalInfoLocation">-</div>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">Siklus Reused</div>
                                    <div id="modalInfoReused">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-dark d-flex align-items-center">
                        <i class="ri-history-line text-primary me-2"></i> Alur Audit Trail & Riwayat Scan
                    </h6>

                    <div class="timeline-container" id="timelineList">
                        <div class="text-center py-4 text-muted">Memuat riwayat...</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let activeTab = 'history'; // 'history' | 'current'
            let historyPage = 1;
            let currentPage = 1;
            let searchTimeout = null;

            // Helper format date d-m-Y H:i
            function formatDateTime(dStr) {
                if (!dStr) return '-';
                const d = new Date(dStr);
                if (isNaN(d.getTime())) return dStr;
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                const hours = String(d.getHours()).padStart(2, '0');
                const mins = String(d.getMinutes()).padStart(2, '0');
                return `${day}/${month}/${year} ${hours}:${mins}`;
            }

            // Tab Switching Handler
            $('a[data-bs-toggle="pill"], a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('href');
                if (target === '#tabHistory') {
                    activeTab = 'history';
                    $('#wrapperFilterAction').removeClass('d-none');
                    $('#wrapperFilterDate').removeClass('d-none');
                    $('#wrapperQuickDate').removeClass('d-none');
                    $('#wrapperFilterReused').addClass('d-none');
                    loadHistory(historyPage);
                } else if (target === '#tabCurrent') {
                    activeTab = 'current';
                    $('#wrapperFilterAction').addClass('d-none');
                    $('#wrapperFilterDate').addClass('d-none');
                    $('#wrapperQuickDate').addClass('d-none');
                    $('#wrapperFilterReused').removeClass('d-none');
                    loadCurrent(currentPage);
                }
            });

            // Quick Date Buttons
            $('.date-chip').on('click', function() {
                $('.date-chip').removeClass('active');
                $(this).addClass('active');

                const preset = $(this).data('preset');
                const now = new Date();
                const todayStr = now.toISOString().split('T')[0];

                if (preset === 'today') {
                    $('#filterStartDate').val(todayStr);
                    $('#filterEndDate').val(todayStr);
                } else if (preset === 'yesterday') {
                    const y = new Date(now);
                    y.setDate(y.getDate() - 1);
                    const yStr = y.toISOString().split('T')[0];
                    $('#filterStartDate').val(yStr);
                    $('#filterEndDate').val(yStr);
                } else if (preset === 'last7') {
                    const past7 = new Date(now);
                    past7.setDate(past7.getDate() - 6);
                    $('#filterStartDate').val(past7.toISOString().split('T')[0]);
                    $('#filterEndDate').val(todayStr);
                } else if (preset === 'month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
                    $('#filterStartDate').val(firstDay);
                    $('#filterEndDate').val(todayStr);
                } else if (preset === 'all') {
                    $('#filterStartDate').val('');
                    $('#filterEndDate').val('');
                }

                if (activeTab === 'history') {
                    loadHistory(1);
                }
            });

            // Trigger filters
            $('#filterSearch').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    if (activeTab === 'history') {
                        loadHistory(1);
                    } else {
                        loadCurrent(1);
                    }
                }, 400);
            });

            $('#filterAction, #filterStartDate, #filterEndDate').on('change', function() {
                if (activeTab === 'history') {
                    loadHistory(1);
                }
            });

            $('#filterReusedStatus').on('change', function() {
                if (activeTab === 'current') {
                    loadCurrent(1);
                }
            });

            $('#btnResetFilter').on('click', function() {
                $('#filterSearch').val('');
                $('#filterAction').val('all');
                $('#filterStartDate').val('');
                $('#filterEndDate').val('');
                $('#filterReusedStatus').val('');
                $('.date-chip').removeClass('active');

                if (activeTab === 'history') {
                    loadHistory(1);
                } else {
                    loadCurrent(1);
                }
            });

            // Export CSV
            $('#btnExportCsv').on('click', function() {
                const params = new URLSearchParams({
                    start_date: $('#filterStartDate').val() || '',
                    end_date: $('#filterEndDate').val() || '',
                    action: $('#filterAction').val() || 'all',
                    search: $('#filterSearch').val() || ''
                });
                window.location.href = "{{ route('wpm.kempu.report_export') }}?" + params.toString();
            });

            // Load Data: Tab 1 (Log History WPM)
            function loadHistory(page = 1) {
                historyPage = page;
                const tbody = $('#tbodyHistory');
                tbody.html('<tr><td colspan="11" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> <span class="ms-2 text-muted">Memuat data...</span></td></tr>');

                $.ajax({
                    url: "{{ route('wpm.kempu.report_data') }}",
                    type: "GET",
                    data: {
                        view_mode: 'history',
                        page: page,
                        per_page: 20,
                        start_date: $('#filterStartDate').val(),
                        end_date: $('#filterEndDate').val(),
                        action: $('#filterAction').val(),
                        search: $('#filterSearch').val()
                    },
                    success: function(res) {
                        if (!res.status || !res.data || res.data.length === 0) {
                            tbody.html('<tr><td colspan="11" class="text-center py-4 text-muted"><i class="ri-inbox-line fs-24 d-block mb-1"></i>Belum ada data scan sesuai filter.</td></tr>');
                            $('#infoPaginationHistory').text('Menampilkan 0 data');
                            $('#pagesHistory').empty();
                            return;
                        }

                        let html = '';
                        const startIdx = ((res.pagination.current_page - 1) * res.pagination.per_page) + 1;

                        res.data.forEach((row, index) => {
                            const isTransferIn = (row.action || '').toLowerCase().includes('in');
                            const badgeStyle = isTransferIn ? 'background:#dcfce7; color:#15803d;' : 'background:#dbeafe; color:#1d4ed8;';

                            const reusedVal = row.reused_count ?? 0;
                            let reusedBadge = `<span class="badge bg-light text-dark border">${reusedVal}/21x</span>`;
                            if (reusedVal >= 21) {
                                reusedBadge = `<span class="badge bg-danger text-white">${reusedVal}/21x (Max)</span>`;
                            } else if (reusedVal >= 18) {
                                reusedBadge = `<span class="badge bg-warning text-dark">${reusedVal}/21x (Warning)</span>`;
                            }

                            // Meta items (barcode, rfid, nti)
                            const meta = row.metadata || {};
                            let metaBadges = '';
                            if (meta.has_barcode) metaBadges += '<span class="badge bg-light text-muted border me-1" title="Barcode OK"><i class="ri-barcode-line text-success"></i> Barcode</span>';
                            if (meta.has_rfid) metaBadges += '<span class="badge bg-light text-muted border me-1" title="RFID OK"><i class="ri-rfid-line text-primary"></i> RFID</span>';
                            if (meta.has_nti) metaBadges += '<span class="badge bg-light text-muted border" title="NTI Segel"><i class="ri-shield-check-line text-info"></i> NTI</span>';

                            html += `
                                <tr>
                                    <td><span class="text-muted fs-12">${startIdx + index}</span></td>
                                    <td>
                                        <div class="fw-semibold text-dark fs-12">${formatDateTime(row.created_at)}</div>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" class="fw-bold font-monospace text-primary btn-view-history" data-id="${row.id_kempu}">
                                            ${row.id_kempu}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="font-monospace fs-12 text-muted">${(row.master_kempu && row.master_kempu.rfid) ? row.master_kempu.rfid : '-'}</span>
                                    </td>
                                    <td>
                                        <span class="badge px-2 py-1 fs-11" style="${badgeStyle}">
                                            ${row.action}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1 fs-12">
                                            <span class="badge bg-light text-dark border">${row.from_location || '-'}</span>
                                            <i class="ri-arrow-right-line text-muted"></i>
                                            <span class="badge bg-light text-dark border">${row.to_location || '-'}</span>
                                        </div>
                                    </td>
                                    <td>${reusedBadge}</td>
                                    <td>
                                        <div class="d-flex flex-wrap align-items-center gap-1">
                                            <span class="badge ${row.condition === 'NOT_OK' ? 'bg-danger' : 'bg-success-subtle text-success'} fs-11">${row.condition || 'OK'}</span>
                                            ${metaBadges}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block text-muted fs-12" style="max-width: 160px;" title="${row.notes || '-'}">
                                            ${row.notes || '-'}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fs-12 text-dark">${row.created_by ? (row.created_by.nama_lengkap || row.created_by.username) : '-'}</div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-ghost-primary btn-view-history" data-id="${row.id_kempu}" title="Lihat Riwayat Lifecycle">
                                            <i class="ri-eye-line fs-14"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        tbody.html(html);
                        renderPagination(res.pagination, 'History');
                    },
                    error: function() {
                        tbody.html('<tr><td colspan="11" class="text-center py-4 text-danger">Gagal memuat data log history.</td></tr>');
                    }
                });
            }

            // Load Data: Tab 2 (Stok Kempu di WPM Saat Ini)
            function loadCurrent(page = 1) {
                currentPage = page;
                const tbody = $('#tbodyCurrent');
                tbody.html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> <span class="ms-2 text-muted">Memuat stok...</span></td></tr>');

                $.ajax({
                    url: "{{ route('wpm.kempu.report_data') }}",
                    type: "GET",
                    data: {
                        view_mode: 'current',
                        page: page,
                        per_page: 20,
                        reused_status: $('#filterReusedStatus').val(),
                        search: $('#filterSearch').val()
                    },
                    success: function(res) {
                        if (!res.status || !res.data || res.data.length === 0) {
                            tbody.html('<tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-inbox-line fs-24 d-block mb-1"></i>Tidak ada kempu di lokasi WPM sesuai filter.</td></tr>');
                            $('#infoPaginationCurrent').text('Menampilkan 0 data');
                            $('#pagesCurrent').empty();
                            return;
                        }

                        let html = '';
                        const startIdx = ((res.pagination.current_page - 1) * res.pagination.per_page) + 1;

                        res.data.forEach((row, index) => {
                            const main = row.main || {};
                            const reusedVal = main.reused_count ?? 0;
                            const reusedPercent = Math.min(100, Math.round((reusedVal / 21) * 100));

                            let progressBarColor = 'bg-success';
                            if (reusedVal >= 21) {
                                progressBarColor = 'bg-danger';
                            } else if (reusedVal >= 18) {
                                progressBarColor = 'bg-warning';
                            }

                            html += `
                                <tr>
                                    <td><span class="text-muted fs-12">${startIdx + index}</span></td>
                                    <td>
                                        <a href="javascript:void(0);" class="fw-bold font-monospace text-primary btn-view-history" data-id="${row.id_kempu}">
                                            ${row.id_kempu}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="font-monospace fs-12 text-muted">${row.rfid || '-'}</span>
                                    </td>
                                    <td>
                                        <span class="fs-12 fw-semibold text-dark">${row.no_spb || '-'}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary border border-primary-subtle px-2 py-1 fs-11">
                                            ${main.current_status || 'WPM_TRANSFER_IN_FROM_PAS'}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fs-12 text-muted">${formatDateTime(main.last_scanned_at || row.updated_at)}</span>
                                    </td>
                                    <td style="min-width: 140px;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fs-11 fw-semibold text-dark">${reusedVal} / 21x</span>
                                            <span class="fs-10 text-muted">${reusedPercent}%</span>
                                        </div>
                                        <div class="progress-reused">
                                            <div class="progress-bar ${progressBarColor}" role="progressbar" style="width: ${reusedPercent}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block text-muted fs-12" style="max-width: 150px;">
                                            ${main.last_action || '-'}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-view-history" data-id="${row.id_kempu}">
                                            <i class="ri-history-line me-1"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        tbody.html(html);
                        renderPagination(res.pagination, 'Current');
                    },
                    error: function() {
                        tbody.html('<tr><td colspan="9" class="text-center py-4 text-danger">Gagal memuat data stok kempu.</td></tr>');
                    }
                });
            }

            // Pagination Renderer Helper
            function renderPagination(pg, type) {
                const infoEl = $(`#infoPagination${type}`);
                const pagesEl = $(`#pages${type}`);

                const from = ((pg.current_page - 1) * pg.per_page) + 1;
                const to = Math.min(pg.current_page * pg.per_page, pg.total);
                infoEl.text(`Menampilkan ${from} - ${to} dari total ${pg.total} data`);

                pagesEl.empty();

                if (pg.last_page <= 1) return;

                // Prev Button
                pagesEl.append(`
                    <li class="page-item ${pg.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${pg.current_page - 1}">&laquo;</a>
                    </li>
                `);

                const maxBtns = 5;
                let startPage = Math.max(1, pg.current_page - 2);
                let endPage = Math.min(pg.last_page, startPage + maxBtns - 1);
                if (endPage - startPage < maxBtns - 1) {
                    startPage = Math.max(1, endPage - maxBtns + 1);
                }

                for (let p = startPage; p <= endPage; p++) {
                    pagesEl.append(`
                        <li class="page-item ${p === pg.current_page ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${p}">${p}</a>
                        </li>
                    `);
                }

                // Next Button
                pagesEl.append(`
                    <li class="page-item ${pg.current_page === pg.last_page ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${pg.current_page + 1}">&raquo;</a>
                    </li>
                `);

                pagesEl.find('a').on('click', function() {
                    const p = parseInt($(this).data('page'));
                    if (p && p >= 1 && p <= pg.last_page && p !== pg.current_page) {
                        if (type === 'History') {
                            loadHistory(p);
                        } else {
                            loadCurrent(p);
                        }
                    }
                });
            }

            // Lifecycle Timeline Modal Handler
            $(document).on('click', '.btn-view-history', function() {
                const idKempu = $(this).data('id');
                if (!idKempu) return;

                $('#modalTitle').text('Riwayat Perjalanan Kempu ' + idKempu);
                $('#modalSubtitle').text('ID: ' + idKempu);
                $('#modalInfoId').text(idKempu);
                $('#modalInfoRfid').text('-');
                $('#modalInfoLocation').text('-');
                $('#modalInfoReused').text('-');
                $('#timelineList').html('<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat riwayat alur...</div>');

                $('#modalKempuHistory').modal('show');

                $.ajax({
                    url: "{{ url('/kempu/traceability/history') }}/" + encodeURIComponent(idKempu),
                    type: "GET",
                    success: function(res) {
                        if (!res.status || !res.kempu) {
                            $('#timelineList').html('<div class="text-center py-4 text-danger">Data riwayat kempu tidak ditemukan.</div>');
                            return;
                        }

                        const k = res.kempu;
                        const main = k.main || {};
                        $('#modalInfoRfid').text(k.rfid || '-');
                        $('#modalInfoLocation').html(`<span class="badge bg-primary-subtle text-primary border px-2 py-1">${main.current_location || '-'}</span>`);

                        const reusedVal = main.reused_count ?? 0;
                        let reusedHtml = `<span class="badge bg-light text-dark border">${reusedVal} / 21x</span>`;
                        if (reusedVal >= 21) {
                            reusedHtml = `<span class="badge bg-danger text-white">${reusedVal} / 21x (Limit Tercapai)</span>`;
                        } else if (reusedVal >= 18) {
                            reusedHtml = `<span class="badge bg-warning text-dark">${reusedVal} / 21x (Peringatan)</span>`;
                        }
                        $('#modalInfoReused').html(reusedHtml);

                        const histories = res.histories || [];
                        if (histories.length === 0) {
                            $('#timelineList').html('<div class="text-center py-4 text-muted">Belum ada riwayat pergerakan yang tercatat.</div>');
                            return;
                        }

                        let tHtml = '';
                        histories.forEach((h) => {
                            const act = (h.action || '').toLowerCase();
                            let dotClass = '';
                            if (act.includes('in')) dotClass = 'transfer-in';
                            if (h.condition === 'NOT_OK') dotClass = 'danger';

                            tHtml += `
                                <div class="timeline-item">
                                    <div class="timeline-dot ${dotClass}"></div>
                                    <div class="bg-white p-3 rounded border shadow-none">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-1">
                                            <span class="fw-bold text-dark fs-13">${h.action || 'Pergerakan Kempu'}</span>
                                            <span class="text-muted fs-11">${formatDateTime(h.created_at)}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 mb-2 fs-12">
                                            <span class="badge bg-light text-dark border">${h.from_location || '-'}</span>
                                            <i class="ri-arrow-right-line text-muted"></i>
                                            <span class="badge bg-light text-dark border">${h.to_location || '-'}</span>
                                            <span class="badge bg-light text-muted border ms-auto">Reused: ${h.reused_count ?? 0}x</span>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 text-muted fs-11">
                                            <span><i class="ri-user-line me-1"></i>${h.created_by ? (h.created_by.nama_lengkap || h.created_by.username) : 'Sistem'}</span>
                                            ${h.notes ? `<span><i class="ri-chat-1-line me-1"></i>${h.notes}</span>` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        $('#timelineList').html(tHtml);
                    },
                    error: function() {
                        $('#timelineList').html('<div class="text-center py-4 text-danger">Gagal mengambil data dari server.</div>');
                    }
                });
            });

            // Initial load
            loadHistory(1);
        });
    </script>
@endsection
