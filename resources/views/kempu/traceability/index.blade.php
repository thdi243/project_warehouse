@extends('layouts.app')

@section('title', '| Traceability & Reused Kempu')

@section('styles')
    <style>
        .location-badge-WPM {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .location-badge-QC_PM {
            background-color: #fef3c7;
            color: #92400e;
        }

        .location-badge-ENGINEERING_WORKSHOP {
            background-color: #ffedd5;
            color: #9a3412;
        }

        .location-badge-PRODUKSI {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .location-badge-QC_PROSES {
            background-color: #fef08a;
            color: #854d0e;
        }

        .location-badge-WFG {
            background-color: #ccfbf1;
            color: #115e59;
        }

        .location-badge-WAREHOUSE_PAS {
            background-color: #f3e8ff;
            color: #6b21a8;
        }

        .location-badge-SCRAP {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .progress-reused {
            height: 8px;
            border-radius: 4px;
            background-color: #e2e8f0;
            overflow: hidden;
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
            box-shadow: 0 0 0 2px #93c5fd;
        }

        .timeline-dot.scrap {
            background: #ef4444;
            box-shadow: 0 0 0 2px #fca5a5;
        }

        .timeline-dot.warning {
            background: #f59e0b;
            box-shadow: 0 0 0 2px #fde68a;
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

            <!-- Page Title Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Traceability Kempu & Siklus Reused 21x</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('kempu.master.index') }}">Kempu</a></li>
                                <li class="breadcrumb-item active">Traceability</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Action & Statistics -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate border-start border-primary border-3 h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted text-truncate mb-1 fs-12">Total Kempu
                                        Aktif</p>
                                    <h4 class="fs-22 fw-bold mb-0 text-primary" id="statTotalActive">0</h4>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-primary-subtle text-white rounded fs-3">
                                        <i class="ri-database-2-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate border-start border-info border-3 h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted text-truncate mb-1 fs-12">Di Produksi /
                                        Proses</p>
                                    <h4 class="fs-22 fw-bold mb-0 text-info" id="statProduksi">0</h4>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-info-subtle text-info rounded fs-3">
                                        <i class="ri-settings-4-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate border-start border-warning border-3 h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted text-truncate mb-1 fs-12">Mendekati Max
                                        (>=18x)</p>
                                    <h4 class="fs-22 fw-bold mb-0 text-warning" id="statNearMax">0</h4>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-warning-subtle text-warning rounded fs-3">
                                        <i class="ri-alarm-warning-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate border-start border-danger border-3 h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-uppercase fw-semibold text-muted text-truncate mb-1 fs-12">Scrap / Afkir
                                    </p>
                                    <h4 class="fs-22 fw-bold mb-0 text-danger" id="statScrap">0</h4>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-danger-subtle text-danger rounded fs-3">
                                        <i class="ri-delete-bin-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-3 mb-4">
                <!-- Chart 1: Distribusi Lokasi Kempu -->
                <div class="col-xl-6 col-lg-6">
                    <div class="card shadow-sm border-0 h-100 mb-0">
                        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                            <h5 class="card-title mb-0 fs-14 fw-bold">
                                <i class="ri-pie-chart-2-line text-primary me-1"></i> Distribusi Kempu per Lokasi
                            </h5>
                            <span class="badge bg-light text-muted border fs-11">Semua Kempu</span>
                        </div>
                        <div class="card-body">
                            <div id="chartLocationDistribution" style="min-height: 290px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Chart 2: Siklus Reused Breakdown -->
                <div class="col-xl-6 col-lg-6">
                    <div class="card shadow-sm border-0 h-100 mb-0">
                        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                            <h5 class="card-title mb-0 fs-14 fw-bold">
                                <i class="ri-bar-chart-grouped-line text-success me-1"></i> Distribusi Siklus Reused (Maks. 21x)
                            </h5>
                            <span class="badge bg-light text-muted border fs-11">Kempu Aktif</span>
                        </div>
                        <div class="card-body">
                            <div id="chartReusedDistribution" style="min-height: 290px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Action Bar -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                            <!-- Search -->
                            <div class="position-relative" style="min-width: 220px;">
                                <input type="text" id="searchInput" class="form-control form-control-sm ps-4"
                                    placeholder="Cari Barcode / ID Kempu...">
                                <i
                                    class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-2 text-muted fs-14"></i>
                            </div>

                            <!-- Filter Lokasi -->
                            <select id="filterLocation" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Semua Lokasi</option>
                                @foreach ($locations as $locKey => $locName)
                                    <option value="{{ $locKey }}">{{ $locName }}</option>
                                @endforeach
                            </select>

                            <!-- Filter Reused -->
                            <select id="filterReused" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Semua Siklus Reused</option>
                                <option value="normal">Normal (&lt; 18x)</option>
                                <option value="warning">Mendekati Batas (18 - 20x)</option>
                                <option value="max">Maksimal (&ge; 21x)</option>
                            </select>

                            <!-- Filter Status -->
                            <select id="filterStatus" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Semua Status</option>
                                <option value="active">Kempu Aktif (Beroperasi)</option>
                                <option value="scrap">Scrap / Afkir</option>
                            </select>

                            <button id="btnResetFilter" class="btn btn-sm btn-light border text-muted">
                                <i class="ri-refresh-line"></i> Reset
                            </button>
                        </div>

                        <!-- Shortcut -->
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('kempu.master.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="ri-list-check me-1"></i> Master Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Data Kempu -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap mb-0" id="tableTraceability">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>ID / Barcode Kempu</th>
                                    <th>Tipe & Merk</th>
                                    <th>Lokasi Sekarang</th>
                                    <th>Status Saat Ini</th>
                                    <th style="min-width: 150px;">Siklus Reused (Max 21x)</th>
                                    <th>Kondisi</th>
                                    <th>Terakhir Diproses</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="traceabilityTbody">
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                        </div>
                                        Memuat data traceability kempu...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-2">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="text-muted fs-13" id="tableInfo">Menampilkan data...</div>
                        <div id="tablePagination"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Detail & History Timeline -->
    <div class="modal fade" id="modalTimeline" tabindex="-1" aria-labelledby="modalTimelineLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ri-route-line fs-20"></i>
                        <div>
                            <h5 class="modal-title text-white mb-0" id="modalTimelineLabel">Traceability & Riwayat Kempu
                            </h5>
                            <span class="fs-12 text-white-50" id="modalSubtitle">ID Kempu: -</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Info Kempu Card -->
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-sm-3">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">ID Barcode</div>
                                    <div class="fw-bold fs-15 text-dark font-monospace" id="modalInfoId">-</div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">Lokasi Terkini</div>
                                    <div id="modalInfoLocation">-</div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">Status Siklus</div>
                                    <div id="modalInfoStatus">-</div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">Siklus Reused</div>
                                    <div id="modalInfoReused">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-dark d-flex align-items-center">
                        <i class="ri-history-line text-primary me-2"></i> Perjalanan & Log Riwayat (Audit Trail)
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
            let allData = [];
            let filteredData = [];
            let chartLocation = null;
            let chartReused = null;

            // Inisialisasi Charts
            function initCharts(stats) {
                if (!stats || !stats.charts) return;

                // 1. Chart Lokasi (Donut)
                const locData = stats.charts.locations || { labels: [], series: [] };
                const locOptions = {
                    series: locData.series || [],
                    labels: locData.labels || [],
                    chart: {
                        type: 'donut',
                        height: 290,
                        toolbar: { show: false }
                    },
                    colors: ['#4f46e5', '#f59e0b', '#2563eb', '#84cc16', '#0d9488', '#9333ea', '#ea580c', '#dc2626'],
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        fontSize: '12px',
                        markers: { radius: 12 }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Kempu',
                                        fontSize: '13px',
                                        fontWeight: 600,
                                        color: '#64748b',
                                        formatter: function(w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { width: 2, colors: ['#ffffff'] },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + ' kempu';
                            }
                        }
                    }
                };

                if (chartLocation) {
                    chartLocation.destroy();
                }
                const locEl = document.querySelector("#chartLocationDistribution");
                if (locEl) {
                    chartLocation = new ApexCharts(locEl, locOptions);
                    chartLocation.render();
                }

                // 2. Chart Siklus Reused (Bar)
                const reusedData = stats.charts.reused || { labels: [], series: [] };
                const reusedOptions = {
                    series: [{
                        name: 'Jumlah Kempu',
                        data: reusedData.series || []
                    }],
                    chart: {
                        type: 'bar',
                        height: 290,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            distributed: true,
                            borderRadius: 6,
                            columnWidth: '50%',
                            dataLabels: { position: 'top' }
                        }
                    },
                    colors: ['#10b981', '#06b6d4', '#3b82f6', '#f59e0b', '#ef4444'],
                    dataLabels: {
                        enabled: true,
                        offsetY: -20,
                        style: {
                            fontSize: '12px',
                            colors: ["#304758"]
                        }
                    },
                    legend: { show: false },
                    xaxis: {
                        categories: reusedData.labels || [],
                        labels: {
                            style: { fontSize: '11px' }
                        }
                    },
                    yaxis: {
                        title: { text: 'Jumlah Kempu' },
                        labels: {
                            formatter: function(val) {
                                return Math.round(val);
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + ' kempu';
                            }
                        }
                    }
                };

                if (chartReused) {
                    chartReused.destroy();
                }
                const reusedEl = document.querySelector("#chartReusedDistribution");
                if (reusedEl) {
                    chartReused = new ApexCharts(reusedEl, reusedOptions);
                    chartReused.render();
                }
            }

            function fetchStats() {
                $.ajax({
                    url: "{{ route('kempu.traceability.stats') }}",
                    type: "GET",
                    dataType: "json",
                    success: function(res) {
                        if (res.status && res.data) {
                            initCharts(res.data);
                        }
                    }
                });
            }

            // Load Data
            function loadData() {
                $('#traceabilityTbody').html(`
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            Memuat data traceability kempu...
                        </td>
                    </tr>
                `);

                $.ajax({
                    url: "{{ route('kempu.traceability.data') }}",
                    type: "GET",
                    data: {
                        location: $('#filterLocation').val(),
                        status_siklus: $('#filterStatus').val(),
                        reused_status: $('#filterReused').val(),
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.status && res.data) {
                            allData = res.data;
                            applyClientFilter();
                            updateStatistics(allData);
                            fetchStats();
                        } else {
                            renderEmpty('Gagal memuat data kempu.');
                        }
                    },
                    error: function(xhr) {
                        renderEmpty('Terjadi kesalahan saat memuat data: ' + (xhr.responseJSON
                            ?.message || xhr.statusText));
                    }
                });
            }

            // Client-side search and render
            function applyClientFilter() {
                const keyword = $('#searchInput').val().toLowerCase().trim();

                filteredData = allData.filter(item => {
                    const idMatch = (item.id_kempu || '').toLowerCase().includes(keyword);
                    const merkMatch = (item.merk || '').toLowerCase().includes(keyword);
                    const tipeMatch = (item.tipe_kempu || '').toLowerCase().includes(keyword);
                    const actionMatch = (item.last_action || '').toLowerCase().includes(keyword);
                    return idMatch || merkMatch || tipeMatch || actionMatch;
                });

                renderTable(filteredData);
            }

            // Update stats
            function updateStatistics(data) {
                let totalActive = 0;
                let totalProd = 0;
                let nearMax = 0;
                let scrap = 0;

                data.forEach(item => {
                    if (item.current_status === 'SCRAPPED') {
                        scrap++;
                    } else {
                        totalActive++;
                    }

                    if (item.current_location === 'PRODUKSI' || item.current_location === 'QC_PROSES') {
                        totalProd++;
                    }

                    if (item.reused_count >= 18 && item.current_status !== 'SCRAPPED') {
                        nearMax++;
                    }
                });

                $('#statTotalActive').text(totalActive);
                $('#statProduksi').text(totalProd);
                $('#statNearMax').text(nearMax);
                $('#statScrap').text(scrap);
            }

            // Render Table Rows
            function renderTable(data) {
                if (data.length === 0) {
                    renderEmpty('Tidak ada data kempu yang sesuai dengan filter.');
                    $('#tableInfo').text('Menampilkan 0 data');
                    return;
                }

                let html = '';
                data.forEach((item, index) => {
                    const reused = item.reused_count || 0;
                    const maxReused = item.max_reused || 21;
                    const percent = Math.min(100, Math.round((reused / maxReused) * 100));

                    // Reused Progress Color
                    let progressColor = 'bg-success';
                    let badgeColor = 'badge bg-success-subtle text-success';
                    if (reused >= 21) {
                        progressColor = 'bg-danger';
                        badgeColor = 'badge bg-danger-subtle text-danger';
                    } else if (reused >= 18) {
                        progressColor = 'bg-warning';
                        badgeColor = 'badge bg-warning-subtle text-warning';
                    }

                    // Location Badge
                    const locClass = `location-badge-${item.current_location || 'WPM'}`;
                    const locLabel = formatLocation(item.current_location);

                    // Status Badge
                    const statusBadge = formatStatusBadge(item.current_status);

                    // Condition Badge
                    const isConditionOk = item.condition === 'OK';
                    const conditionBadge = isConditionOk ?
                        `<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="ri-check-line"></i> OK</span>` :
                        `<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="ri-close-line"></i> Tidak OK</span>`;

                    // Last action date
                    const lastUpdated = item.last_scanned_at ?
                        formatDate(item.last_scanned_at) :
                        formatDate(item.updated_at);

                    html += `
                        <tr>
                            <td class="text-muted">${index + 1}</td>
                            <td>
                                <span class="fw-bold font-monospace text-primary fs-14">${item.id_kempu}</span>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">${item.tipe_kempu || '-'}</div>
                                <div class="text-muted fs-12">${item.merk || '-'}</div>
                            </td>
                            <td>
                                <span class="badge ${locClass} px-2 py-1 fs-12">
                                    <i class="ri-map-pin-2-line me-1"></i>${locLabel}
                                </span>
                            </td>
                            <td>${statusBadge}</td>
                            <td>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="${badgeColor} fw-bold fs-11">${reused} / ${maxReused}x</span>
                                    <span class="text-muted fs-11">${percent}%</span>
                                </div>
                                <div class="progress progress-reused">
                                    <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${percent}%;"></div>
                                </div>
                            </td>
                            <td>${conditionBadge}</td>
                            <td>
                                <div class="fs-12 text-dark fw-medium">${item.last_action || 'Pendaftaran Master'}</div>
                                <div class="text-muted fs-11">${lastUpdated}</div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info btn-icon btnDetailTimeline" data-id="${item.id}" title="Lihat History & Timeline">
                                    <i class="ri-history-line"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                $('#traceabilityTbody').html(html);
                $('#tableInfo').text(`Menampilkan ${data.length} dari ${allData.length} kempu`);
            }

            function renderEmpty(message) {
                $('#traceabilityTbody').html(`
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="ri-inbox-line fs-36 text-muted mb-2 d-block"></i>
                            ${message}
                        </td>
                    </tr>
                `);
            }

            // Helpers formatting
            function formatLocation(loc) {
                const map = {
                    'WPM': 'WPM BAS',
                    'QC_PM': 'QC Packaging Mat.',
                    'ENGINEERING_WORKSHOP': 'Eng Workshop',
                    'PRODUKSI': 'Produksi',
                    'QC_PROSES': 'QC Proses',
                    'WFG': 'WFG BAS',
                    'WAREHOUSE_PAS': 'Warehouse PT PAS',
                    'SCRAP': 'Scrap / Afkir',
                };
                return map[loc] || loc || '-';
            }

            function formatStatusBadge(status) {
                if (!status) return '<span class="badge bg-secondary">Unknown</span>';

                const map = {
                    'REGISTERED': '<span class="badge bg-secondary"><i class="ri-file-add-line me-1"></i>Master Terdaftar</span>',
                    'GR_COMPLETED': '<span class="badge bg-info text-dark"><i class="ri-inbox-archive-line me-1"></i>GR Selesai</span>',
                    'QC_PM_PENDING': '<span class="badge bg-warning text-dark"><i class="ri-time-line me-1"></i>Menunggu QC PM</span>',
                    'QC_PM_RELEASE': '<span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>QC PM Release</span>',
                    'QC_PM_PASSED': '<span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>QC PM Release</span>',
                    'ENG_REPAIR': '<span class="badge bg-danger"><i class="ri-tools-line me-1"></i>Perbaikan Workshop</span>',
                    'ENG_SCRAP_PRODUKSI': '<span class="badge bg-danger"><i class="ri-alert-line me-1"></i>Menunggu BA Scrap</span>',
                    'WPM_TRANSFER_OUT_PROD': '<span class="badge bg-primary"><i class="ri-arrow-right-up-line me-1"></i>WPM: Transfer Out Produksi</span>',
                    'WPM_TRANSFER_IN_PAS': '<span class="badge bg-success"><i class="ri-arrow-left-down-line me-1"></i>WPM: Transfer In PAS</span>',
                    'PROD_TRANSFER_IN_WPM': '<span class="badge bg-primary"><i class="ri-inbox-archive-line me-1"></i>Produksi: Transfer In WPM</span>',
                    'QC_PRE_CUCI_PENDING': '<span class="badge bg-warning text-dark"><i class="ri-time-line me-1"></i>Menunggu QC Pre Cuci</span>',
                    'QC_PRE_CUCI_RELEASE': '<span class="badge bg-success"><i class="ri-shield-check-line me-1"></i>QC Pre Cuci Release</span>',
                    'QC_PRE_CUCI_PASSED': '<span class="badge bg-success"><i class="ri-shield-check-line me-1"></i>QC Pre Cuci Release</span>',
                    'PROD_CUCI_KEMPU': '<span class="badge bg-info text-dark"><i class="ri-water-flash-line me-1"></i>Cuci Kempu Selesai</span>',
                    'PROD_FILLING_KEMPU': '<span class="badge bg-primary"><i class="ri-battery-2-charge-line me-1"></i>Filling Kempu Selesai</span>',
                    'QC_AFTER_FILLING_PENDING': '<span class="badge bg-warning text-dark"><i class="ri-time-line me-1"></i>Menunggu QC After Filling</span>',
                    'QC_AFTER_FILLING_RELEASE': '<span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>QC After Filling Release</span>',
                    'QC_AFTER_FILLING_PASSED': '<span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>QC After Filling Release</span>',
                    'QC_AFTER_FILLING_HOLD': '<span class="badge bg-warning text-dark"><i class="ri-pause-circle-line me-1"></i>QC After Filling Hold</span>',
                    'QC_AFTER_FILLING_REJECT': '<span class="badge bg-danger"><i class="ri-close-circle-line me-1"></i>QC After Filling Reject</span>',
                    'PROD_TRANSFER_OUT_WFG': '<span class="badge bg-primary"><i class="ri-truck-line me-1"></i>Produksi: Transfer Out WFG</span>',
                    'PROD_TRANSFER_IN_WFG': '<span class="badge bg-warning text-dark"><i class="ri-reply-line me-1"></i>Produksi: Transfer In WFG (Retur)</span>',
                    'WFG_TRANSFER_IN_PROD': '<span class="badge bg-teal text-white" style="background:#0d9488;"><i class="ri-inbox-archive-line me-1"></i>WFG: Transfer In Produksi</span>',
                    'WFG_PICKING_FG': '<span class="badge bg-warning text-dark"><i class="ri-checkbox-multiple-line me-1"></i>WFG: Picking FG</span>',
                    'WFG_TRANSFER_OUT_PAS': '<span class="badge bg-purple text-white" style="background:#7c3aed;"><i class="ri-truck-line me-1"></i>WFG: Transfer Out PAS</span>',
                    'WFG_REJECT_PRODUKSI': '<span class="badge bg-danger"><i class="ri-reply-line me-1"></i>WFG: Reject ke Produksi</span>',
                    'PAS_TRANSFER_IN_BAS': '<span class="badge bg-teal text-white" style="background:#0d9488;"><i class="ri-inbox-archive-line me-1"></i>PAS: Transfer In BAS</span>',
                    'PAS_TRANSFER_OUT_BAS': '<span class="badge bg-purple text-white" style="background:#7c3aed;"><i class="ri-truck-line me-1"></i>PAS: Transfer Out BAS</span>',
                    'SCRAPPED': '<span class="badge bg-danger text-white"><i class="ri-delete-bin-line me-1"></i>SCRAP / AFKIR</span>',
                };

                if (map[status]) {
                    return map[status];
                }

                if (status === 'SCRAPPED') {
                    return '<span class="badge bg-danger text-white"><i class="ri-delete-bin-line me-1"></i>SCRAP / AFKIR</span>';
                }
                if (status.includes('PENDING')) {
                    return `<span class="badge bg-warning text-dark"><i class="ri-time-line me-1"></i>${status.replace(/_/g, ' ')}</span>`;
                }
                if (status.includes('RELEASE') || status.includes('PASSED') || status.includes('OK') || status.includes('FILLED')) {
                    return `<span class="badge bg-success"><i class="ri-check-double-line me-1"></i>${status.replace(/_/g, ' ')}</span>`;
                }
                if (status.includes('TRANSFER') || status.includes('IN_TRANSIT')) {
                    return `<span class="badge bg-primary"><i class="ri-truck-line me-1"></i>${status.replace(/_/g, ' ')}</span>`;
                }
                return `<span class="badge bg-info text-dark">${status.replace(/_/g, ' ')}</span>`;
            }

            function formatDate(dt) {
                if (!dt) return '-';
                try {
                    const d = new Date(dt);
                    return d.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    return dt;
                }
            }

            // Filter Event Listeners
            $('#searchInput').on('input', applyClientFilter);
            $('#filterLocation, #filterReused, #filterStatus').on('change', loadData);

            $('#btnResetFilter').on('click', function() {
                $('#searchInput').val('');
                $('#filterLocation').val('');
                $('#filterReused').val('');
                $('#filterStatus').val('');
                loadData();
            });

            // Modal Detail Timeline
            $(document).on('click', '.btnDetailTimeline', function() {
                const id = $(this).data('id');
                $('#modalTimeline').modal('show');
                $('#timelineList').html(`
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat riwayat audit kempu...
                    </div>
                `);

                $.ajax({
                    url: `{{ url('kempu/traceability/history') }}/${id}`,
                    type: "GET",
                    dataType: "json",
                    success: function(res) {
                        if (res.status && res.kempu) {
                            const k = res.kempu;
                            $('#modalSubtitle').text(
                                `ID: ${k.id_kempu} | Tipe: ${k.tipe_kempu || '-'}`);
                            $('#modalInfoId').text(k.id_kempu);
                            $('#modalInfoLocation').html(
                                `<span class="badge location-badge-${k.current_location}">${formatLocation(k.current_location)}</span>`
                                );
                            $('#modalInfoStatus').html(formatStatusBadge(k.current_status));
                            $('#modalInfoReused').html(`
                                <span class="fw-bold">${k.reused_count} / ${k.max_reused || 21}x</span>
                            `);


                            // Render Timeline
                            const histories = res.histories || [];
                            if (histories.length === 0) {
                                $('#timelineList').html(
                                    '<div class="text-center py-4 text-muted">Belum ada catatan riwayat untuk kempu ini.</div>'
                                    );
                                return;
                            }

                            let tHtml = '';
                            histories.forEach(h => {
                                let dotClass = 'success';
                                if (h.action_result === 'SCRAP' || h.action_result ===
                                    'MAX_REUSED_SCRAP' || h.action_result === 'BA_SCRAP'
                                    ) {
                                    dotClass = 'scrap';
                                } else if (h.action_result === 'NOT_OK' || h
                                    .action_result === 'NOT_OK_NTI') {
                                    dotClass = 'warning';
                                }

                                const op = h.created_by?.nama_lengkap || h.created_by
                                    ?.username || 'Sistem / Admin';
                                const timeStr = formatDate(h.created_at);

                                tHtml += `
                                    <div class="timeline-item">
                                        <div class="timeline-dot ${dotClass}"></div>
                                        <div class="card border border-light shadow-none mb-0">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-1">
                                                    <span class="fw-bold text-dark fs-14">${h.action.replace(/_/g, ' ')}</span>
                                                    <span class="text-muted fs-11">${timeStr}</span>
                                                </div>
                                                <div class="text-muted fs-12 mb-2">
                                                    <span class="badge bg-light text-dark border me-1">Tahap: ${h.stage}</span>
                                                    <span class="badge bg-light text-dark border me-1">Lokasi: ${formatLocation(h.from_location)} &rarr; ${formatLocation(h.to_location)}</span>
                                                    <span class="badge bg-light text-dark border">Reused: ${h.reused_count}x</span>
                                                </div>
                                                ${h.notes ? `<div class="p-2 bg-light rounded text-dark fs-12 mb-2"><i class="ri-chat-1-line me-1 text-muted"></i>${h.notes}</div>` : ''}
                                                <div class="text-muted fs-11">
                                                    <i class="ri-user-line me-1"></i>Operator: <span class="fw-medium">${op}</span>
                                                    <span class="ms-2 badge ${h.action_result === 'OK' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}">${h.action_result}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            $('#timelineList').html(tHtml);
                        }
                    },
                    error: function(xhr) {
                        $('#timelineList').html(
                            '<div class="text-center py-4 text-danger">Gagal memuat riwayat.</div>'
                            );
                    }
                });
            });

            // Initial load
            loadData();
        });
    </script>
@endsection
