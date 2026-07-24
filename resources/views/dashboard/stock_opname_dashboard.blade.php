@extends('layouts.app')

@section('title', ' | Stock Opname Daily Dashboard')

@section('sidebar-size', 'sm')

@section('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --info-gradient: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);

            --card-border: 1px solid rgba(226, 232, 240, 0.8);
            --card-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 12px 30px -4px rgba(99, 102, 241, 0.08);
        }

        .dashboard-card {
            border-radius: 16px;
            border: var(--card-border);
            /* background: #ffffff; */
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .kpi-icon-box {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 24px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .bg-indigo-gradient {
            background: var(--primary-gradient);
        }

        .bg-emerald-gradient {
            background: var(--success-gradient);
        }

        .bg-rose-gradient {
            background: var(--danger-gradient);
        }

        .bg-amber-gradient {
            background: var(--warning-gradient);
        }

        .bg-cyan-gradient {
            background: var(--info-gradient);
        }

        .dashboard-header-title {
            font-weight: 800;
            background: linear-gradient(90deg, #1e293b 0%, #475569 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.03em;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .badge-belum {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .badge-progress {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .badge-selesai {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .table-responsive {
            max-height: 480px;
            overflow-y: auto;
            border-radius: 8px;
        }

        .table-custom {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-custom thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 2px solid #cbd5e1;
            text-transform: uppercase;
        }

        .table-custom tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        .table-custom tbody tr:last-child td {
            border-bottom: none;
        }


        .progress-bar-custom {
            height: 6px;
            border-radius: 10px;
        }

        .btn-refresh {
            transition: all 0.3s ease;
        }

        .btn-refresh:hover {
            transform: rotate(180deg);
        }

        .filter-panel {
            /* background: #ffffff; */
            border-radius: 16px;
            border: var(--card-border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        }

        .filter-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .form-select-custom,
        .form-control-custom {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .form-select-custom:focus,
        .form-control-custom:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            outline: none;
        }

        .huge-number {
            font-size: 36px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        .qty-plus {
            color: #10b981;
            font-weight: 750;
            background: rgba(16, 185, 129, 0.1);
            padding: 2px 8px;
            border-radius: 6px;
        }

        .qty-minus {
            color: #ef4444;
            font-weight: 750;
            background: rgba(239, 68, 68, 0.1);
            padding: 2px 8px;
            border-radius: 6px;
        }

        .accuracy-badge-high {
            color: #10b981;
            background: rgba(16, 185, 129, 0.1);
            padding: 4px 8px;
            border-radius: 8px;
            font-weight: 700;
        }

        .accuracy-badge-mid {
            color: #f59e0b;
            background: rgba(245, 158, 11, 0.1);
            padding: 4px 8px;
            border-radius: 8px;
            font-weight: 700;
        }

        .accuracy-badge-low {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            padding: 4px 8px;
            border-radius: 8px;
            font-weight: 700;
        }

        .nav-pills-custom .nav-link {
            border-radius: 8px;
            color: #475569;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            margin-right: 6px;
        }

        .nav-pills-custom .nav-link.active {
            background-color: #6366f1;
            color: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row align-items-center mb-4">
                <div class="col-sm-8">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="avatar-sm bg-indigo-gradient rounded-3 p-1 text-center d-flex align-items-center justify-content-center"
                                style="width: 56px; height: 56px;">
                                <i class="mdi mdi-checkbox-multiple-marked-circle-outline text-white fs-28"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="dashboard-header-title mb-1">Daily Stock Opname Dashboard</h3>
                            <p class="text-muted mb-0 fw-medium">Analisa progress & akurasi stock gudang secara
                                harian</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
                    <span
                        class="badge bg-primary-subtle text-primary border border-primary-subtle fs-12 px-3 py-2 rounded-3">
                        <i class="mdi mdi-calendar-text me-1"></i> Tanggal Opname : <span id="lblTanggalOpname"
                            class="fw-bold">-</span>
                    </span>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="card filter-panel p-4 mb-4 shadow-sm">
                <form id="filterForm">
                    <div class="row row-cols-lg-5 row-cols-md-3 row-cols-sm-2 row-cols-1 g-3 align-items-end">
                        <!-- 1. Tanggal Opname -->
                        <div>
                            <label class="filter-label">Tanggal Opname</label>
                            <input type="date" class="form-control form-control-custom" id="filterTgl"
                                value="{{ $tglOpname }}">
                        </div>

                        <!-- 2. Section -->
                        <div>
                            <label class="filter-label">Section / Area</label>
                            <select class="form-select form-select-custom" id="filterSection">
                                <option value="all">Semua Section</option>
                                @foreach ($sections as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 3. PIC / Operator -->
                        <div>
                            <label class="filter-label">Stock Control</label>
                            <select class="form-select form-select-custom" id="filterPic">
                                <option value="all">Semua SC</option>
                                @foreach ($pics as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_lengkap ?? $p->username }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 4. Status -->
                        <div>
                            <label class="filter-label">Status</label>
                            <select class="form-select form-select-custom" id="filterStatus">
                                <option value="all">Semua Status</option>
                                <option value="idle">Belum Mulai</option>
                                <option value="started">On Progress</option>
                                <option value="finished">Selesai</option>
                            </select>
                        </div>

                        <!-- 5. Barang / MID -->
                        <div>
                            <label class="filter-label">Barang / MID</label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-custom" id="filterBarang"
                                    placeholder="Cari MID / nama...">
                                <button type="submit" class="btn btn-primary d-flex align-items-center rounded-end-3 px-3">
                                    <i class="ri-refresh-line btn-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- KPI Row -->
            <div class="row mb-4 g-3">
                <!-- 1. Progress Section -->
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card h-100 p-4 kpi-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-indigo-gradient me-3">
                                <i class="mdi mdi-office-building-marker"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small fw-bold text-uppercase">Progress</h6>
                            </div>
                        </div>
                        <h3 class="huge-number mb-2" id="lblProgressPct">0%</h3>
                        <p class="text-muted small mb-3"><span id="lblSectionSelesai" class="fw-bold">0</span> dari <span
                                id="lblSectionTarget" class="fw-bold">0</span> Section Selesai</p>
                        <div class="progress progress-sm" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar bg-indigo progress-bar-striped progress-bar-animated" id="barProgress"
                                style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- 2. Item Diopname vs Selisih -->
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card h-100 p-4 kpi-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-amber-gradient me-3">
                                <i class="mdi mdi-clipboard-list-outline"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small fw-bold text-uppercase">Item Diopname / Selisih</h6>
                            </div>
                        </div>
                        <h3 class="huge-number mb-2" id="lblItemDiopname">0</h3>
                        <p class="text-muted small mb-0"><span class="text-danger fw-bold" id="lblItemSelisih">0</span> Item
                            memiliki selisih fisik</p>
                    </div>
                </div>

                <!-- 3. Stock Accuracy -->
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card h-100 p-4 kpi-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-emerald-gradient me-3">
                                <i class="mdi mdi-bullseye-arrow"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small fw-bold text-uppercase">Akurasi Stock</h6>
                            </div>
                        </div>
                        <h3 class="huge-number mb-2 text-success" id="lblStockAccuracy">100%</h3>
                        <div id="lblStockAccuracyCompare" class="small fw-semibold mb-2"></div>
                        <p class="text-muted small mb-0">Tingkat akurasi fisik vs sistem</p>
                    </div>
                </div>

                <!-- 4. Item Sesuai (Match) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card h-100 p-4 kpi-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-cyan-gradient me-3">
                                <i class="mdi mdi-check-circle-outline"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small fw-bold text-uppercase">Item Sesuai (Match)</h6>
                            </div>
                        </div>
                        <h3 class="huge-number mb-2 text-success" id="lblItemMatch">0</h3>
                        <p class="text-muted small mb-0"><span class="text-success fw-bold" id="lblItemMatchPct">0%</span>
                            dari total diopname</p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-4">
                <!-- Accuracy Harian (Line/Spline Chart) -->
                <div class="col-xl-6">
                    <div class="card dashboard-card p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title fw-bold mb-0">Tren Akurasi Stock Harian (%)</h5>
                            <span class="text-muted small">Melihat tren 15 hari terakhir</span>
                        </div>
                        <div id="chartAccuracy" style="height: 320px;"></div>
                    </div>
                </div>

                <!-- Akurasi per Section (Column Chart) -->
                <div class="col-xl-6">
                    <div class="card dashboard-card p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title fw-bold mb-0">Akurasi Stock per Section (%)</h5>
                            <span class="text-muted small">Perbandingan akurasi harian per area</span>
                        </div>
                        <div id="chartSectionAccuracy" style="height: 320px;"></div>
                    </div>
                </div>
            </div>

            <!-- Detail Performa per Section Grid -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card dashboard-card p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                            <div>
                                <h5 class="card-title fw-bold mb-1">Detail Performa per Section</h5>
                                <p class="text-muted mb-0 small">Analisa akurasi, volume, SOH, dan status aktivitas opname per area</p>
                            </div>
                        </div>
                        <div class="row g-4" id="sectionDetailGrid">
                            <!-- Dynamic Section Cards will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>



            <!-- Top 10 Selisih Terbesar -->
            <div class="row">
                <div class="col-12">
                    <div class="card dashboard-card p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="card-title fw-bold mb-0">Top 10 Selisih Terbesar (Variance Terbesar)</h5>
                            <span class="text-muted small">Deviasi kuantitas fisik vs sistem terbesar hari ini</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table-custom" id="tableTop10">
                                <thead>
                                    <tr>
                                        <th>Section / Area</th>
                                        <th>MID Barang</th>
                                        <th>Nama Barang</th>
                                        <th class="text-end">Qty Sistem</th>
                                        <th class="text-end">Qty Fisik</th>
                                        <th class="text-center">Selisih Qty</th>
                                        <th>Keterangan / Alasan SC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let accuracyChart, sectionAccuracyChart, sectionCompositionChart;

        $(function() {
            // Load initial data
            loadDashboardData();

            // Refresh on filter changes
            $('#filterTgl, #filterSection, #filterPic, #filterStatus').on('change', function() {
                loadDashboardData();
            });

            // Form submit handles search bar refresh
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadDashboardData();
            });

            Highcharts.setOptions({
                lang: {
                    thousandsSep: '.'
                },
                credits: {
                    enabled: false
                },
                chart: {
                    style: {
                        fontFamily: "'Inter', sans-serif"
                    }
                }
            });
        });

        function loadDashboardData() {
            const tgl = $('#filterTgl').val();
            const section = $('#filterSection').val();
            const pic = $('#filterPic').val();
            const status = $('#filterStatus').val();
            const barang = $('#filterBarang').val();

            $.ajax({
                url: "{{ route('dashboard.stock-opname.data') }}",
                type: "GET",
                data: {
                    tgl_opname: tgl,
                    section: section,
                    pic: pic,
                    status: status,
                    barang: barang
                },
                success: function(res) {
                    if (res.status === 'success') {
                        updateKPIs(res.data.kpis, res.data.ringkasanSections);
                        renderAccuracyChart(res.data.charts.accuracy);
                        renderSectionDetailGrid(res.data.ringkasanSections);
                        renderTop10Table(res.data.top10);
                    }
                },
                error: function(xhr) {
                    console.error("Error loading dashboard data:", xhr);
                }
            });
        }

        function updateKPIs(kpis, sections) {
            $('#lblTanggalOpname').text(kpis.tanggal_formatted);
            $('#lblProgressPct').text(kpis.progress_pct + '%');
            $('#lblSectionSelesai').text(kpis.section_selesai);
            $('#lblSectionTarget').text(kpis.section_target);
            $('#barProgress').css('width', kpis.progress_pct + '%');

            $('#lblItemDiopname').text(kpis.item_diopname);
            $('#lblItemSelisih').text(kpis.item_selisih);

            $('#lblStockAccuracy').text(kpis.stock_accuracy + '%');
            if (parseFloat(kpis.stock_accuracy) >= 98.00) {
                $('#lblStockAccuracy').removeClass('text-danger text-warning').addClass('text-success');
            } else if (parseFloat(kpis.stock_accuracy) >= 95.00) {
                $('#lblStockAccuracy').removeClass('text-danger text-success').addClass('text-warning');
            } else {
                $('#lblStockAccuracy').removeClass('text-success text-warning').addClass('text-danger');
            }

            // Update Comparison with Yesterday
            const compareContainer = $('#lblStockAccuracyCompare');
            compareContainer.empty();
            if (kpis.yesterday_accuracy !== null) {
                const diff = parseFloat(kpis.stock_accuracy) - parseFloat(kpis.yesterday_accuracy);
                const diffFormatted = Math.abs(diff).toFixed(2) + '%';
                if (diff > 0) {
                    compareContainer.html(`
                        <span class="text-success fw-bold">
                            <i class="mdi mdi-arrow-up-bold me-1"></i>+${diffFormatted}
                        </span>
                        <span class="text-muted ms-1 small">dari kemarin</span>
                    `);
                } else if (diff < 0) {
                    compareContainer.html(`
                        <span class="text-danger fw-bold">
                            <i class="mdi mdi-arrow-down-bold me-1"></i>-${diffFormatted}
                        </span>
                        <span class="text-muted ms-1 small">dari kemarin</span>
                    `);
                } else {
                    compareContainer.html(`
                        <span class="text-muted fw-bold">
                            <i class="mdi mdi-minus me-1"></i>Sama dengan kemarin
                        </span>
                    `);
                }
            } else {
                compareContainer.html(`
                    <span class="text-muted small">
                        <i class="mdi mdi-information-outline me-1"></i>Data kemarin tidak tersedia
                    </span>
                `);
            }

            // Hitung total match dari data sections
            let totalDiopname = 0;
            let totalMatch = 0;
            if (sections && sections.length > 0) {
                totalDiopname = sections.reduce((sum, item) => sum + item.diopname, 0);
                totalMatch = sections.reduce((sum, item) => sum + item.match, 0);
            }
            const totalMatchPct = totalDiopname > 0 ? ((totalMatch / totalDiopname) * 100).toFixed(1) : 0;

            $('#lblItemMatch').text(totalMatch.toLocaleString('id-ID'));
            $('#lblItemMatchPct').text(totalMatchPct + '%');
        }

        function renderAccuracyChart(chartData) {
            accuracyChart = Highcharts.chart('chartAccuracy', {
                chart: {
                    type: 'spline',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: chartData.categories,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    max: 100,
                    title: {
                        text: 'Stock Accuracy (%)'
                    },
                    labels: {
                        format: '{value}%'
                    }
                },
                tooltip: {
                    valueSuffix: '%'
                },
                plotOptions: {
                    series: {
                        dataLabels: {
                            enabled: true,
                            formatter: function() {
                                return parseFloat(this.y) + '%';
                            }
                        }
                    }
                },
                series: [{
                    name: 'Akurasi Total',
                    data: chartData.data,
                    color: '#6366f1', // Indigo
                    marker: {
                        enabled: true,
                        radius: 4
                    }
                }]
            });
        }

        function renderSectionDetailGrid(sections) {
            let html = '';
            
            if (sections.length === 0) {
                html = '<div class="col-12 text-center text-muted py-5"><i class="mdi mdi-alert-circle-outline fs-1 d-block mb-2"></i>Tidak ada data section ditemukan untuk filter ini.</div>';
            } else {
                sections.forEach(function(s) {
                    // Status Badge
                    let statusBadge = '';
                    if (s.status === 'finished') {
                        statusBadge = '<span class="status-badge badge-selesai"><i class="mdi mdi-check-circle me-1"></i>SELESAI</span>';
                    } else if (s.status === 'started') {
                        statusBadge = '<span class="status-badge badge-progress"><i class="mdi mdi-play-circle me-1"></i>STARTED</span>';
                    } else {
                        statusBadge = '<span class="status-badge badge-belum">BELUM MULAI</span>';
                    }
                    
                    // Icon based on section key
                    let icon = 'mdi-office-building';
                    if (s.key === 'WSP') icon = 'mdi-cog-outline';
                    else if (s.key === 'WRM') icon = 'mdi-archive-outline';
                    else if (s.key === 'WPM') icon = 'mdi-robot-industrial';
                    else if (s.key === 'WCP') icon = 'mdi-toy-brick-outline';
                    else if (s.key.indexOf('WFG') === 0) icon = 'mdi-warehouse';
                    
                    // Calculate SOH Details percentage for stacked progress bar
                    let totalSoh = s.qty_unrest + s.qty_qi + s.qty_block;
                    let unrestPct = totalSoh > 0 ? (s.qty_unrest / totalSoh * 100) : 0;
                    let qiPct = totalSoh > 0 ? (s.qty_qi / totalSoh * 100) : 0;
                    let blockPct = totalSoh > 0 ? (s.qty_block / totalSoh * 100) : 0;
                    
                    // Match vs Selisih color coding
                    let matchClass = s.match > 0 ? 'text-dark' : 'text-muted';
                    let selisihClass = s.selisih > 0 ? 'text-danger fw-bold' : 'text-success';
                    
                    html += `
                        <div class="col-xl-4 col-md-6">
                            <div class="card h-100 border shadow-sm p-4" style="border-radius: 16px; transition: transform 0.2s; background: #fff;">
                                <!-- Card Header -->
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="kpi-icon-box bg-light text-primary me-3" style="width: 44px; height: 44px; font-size: 20px; border-radius: 10px; box-shadow: none;">
                                            <i class="mdi ${icon}"></i>
                                        </div>
                                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 16px;">${s.name} Section</h5>
                                    </div>
                                    <div>
                                        ${statusBadge}
                                    </div>
                                </div>
                                
                                <!-- KPI Row 1: Accuracy & Total Items -->
                                <div class="row mb-3">
                                    <div class="col-6 border-end">
                                        <span class="text-muted small text-uppercase fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.05em;">Akurasi</span>
                                        <h3 class="fw-extrabold text-dark mb-0" style="font-size: 28px; letter-spacing: -0.02em;">${s.accuracy}%</h3>
                                    </div>
                                    <div class="col-6 ps-3">
                                        <span class="text-muted small text-uppercase fw-semibold d-block mb-1" style="font-size: 9px; letter-spacing: 0.05em;">Total Items</span>
                                        <h3 class="fw-extrabold text-dark mb-0" style="font-size: 24px;">${s.diopname.toLocaleString('id-ID')}</h3>
                                        <small class="text-muted" style="font-size: 10px;">${s.batches} Batches | ${s.pallets} Pallets</small>
                                    </div>
                                </div>
                                
                                <!-- KPI Row 2: Match & Selisih -->
                                <div class="bg-light rounded-3 p-3 mb-4 d-flex justify-content-between text-center">
                                    <div class="flex-fill border-end">
                                        <span class="text-muted small text-uppercase fw-semibold d-block mb-1" style="font-size: 9px;">Match</span>
                                        <span class="fs-5 fw-bold ${matchClass}">${s.match.toLocaleString('id-ID')}</span>
                                    </div>
                                    <div class="flex-fill">
                                        <span class="text-muted small text-uppercase fw-semibold d-block mb-1" style="font-size: 9px;">Selisih</span>
                                        <span class="fs-5 fw-bold ${selisihClass}">${s.selisih.toLocaleString('id-ID')}</span>
                                    </div>
                                </div>
                                
                                <!-- SOH Details Stacked Progress Bar -->
                                <div class="mb-4">
                                    <span class="text-muted small text-uppercase fw-bold d-block mb-2" style="font-size: 9.5px; letter-spacing: 0.03em;">SOH Details</span>
                                    <div class="progress" style="height: 8px; border-radius: 4px; background-color: #e9ecef; overflow: hidden;">
                                        <div class="progress-bar" role="progressbar" style="width: ${unrestPct}%; background-color: #1e293b;" title="Unrest: ${s.qty_unrest}"></div>
                                        <div class="progress-bar" role="progressbar" style="width: ${qiPct}%; background-color: #94a3b8;" title="QI: ${s.qty_qi}"></div>
                                        <div class="progress-bar" role="progressbar" style="width: ${blockPct}%; background-color: #dc2626;" title="Blocked: ${s.qty_block}"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 text-muted" style="font-size: 10.5px;">
                                        <span>Unrest: <strong class="text-dark">${s.qty_unrest.toLocaleString('id-ID')}</strong></span>
                                        <span>QI: <strong class="text-dark">${s.qty_qi.toLocaleString('id-ID')}</strong></span>
                                        <span>Blocked: <strong class="text-dark">${s.qty_block.toLocaleString('id-ID')}</strong></span>
                                    </div>
                                </div>
                                
                                <!-- Work Time -->
                                <div class="border-top pt-3">
                                    <span class="text-muted small text-uppercase fw-bold d-block mb-1" style="font-size: 9.5px;">Work Time</span>
                                    <div class="d-flex align-items-center text-dark fw-semibold" style="font-size: 13px;">
                                        <i class="mdi mdi-clock-outline text-muted me-2"></i>
                                        <span>${s.work_time}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            
            $('#sectionDetailGrid').html(html);
        }


        function renderTop10Table(data) {
            let html = '';
            if (data.length === 0) {
                html =
                    `<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada selisih stock ditemukan untuk tanggal ini.</td></tr>`;
            } else {
                data.forEach(function(row) {
                    let selisihText = row.selisih > 0 ? `+${row.selisih}` : row.selisih;
                    let selisihClass = row.selisih > 0 ? 'text-success fw-bold' : 'text-danger fw-bold';

                    html += `
                        <tr>
                            <td><strong>${row.section}</strong></td>
                            <td><code>${row.mid}</code></td>
                            <td>${row.name}</td>
                            <td class="text-end fw-semibold">${row.qty_sistem.toLocaleString('id-ID')}</td>
                            <td class="text-end fw-semibold">${row.qty_fisik.toLocaleString('id-ID')}</td>
                            <td class="text-center ${selisihClass}">${selisihText}</td>
                            <td class="text-muted small">${row.keterangan}</td>
                        </tr>
                    `;
                });
            }
            $('#tableTop10 tbody').html(html);
        }
    </script>
@endsection
