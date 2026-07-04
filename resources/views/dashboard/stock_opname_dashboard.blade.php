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
                                <h6 class="text-muted mb-0 small fw-bold text-uppercase">Akurasi Stock (Accuracy)</h6>
                            </div>
                        </div>
                        <h3 class="huge-number mb-2 text-success" id="lblStockAccuracy">100%</h3>
                        <p class="text-muted small mb-0">Tingkat akurasi fisik vs sistem</p>
                    </div>
                </div>

                <!-- 4. Total Selisih Qty (+ / -) -->
                <div class="col-xl-3 col-md-6">
                    <div class="card dashboard-card h-100 p-4 kpi-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-box bg-rose-gradient me-3">
                                <i class="mdi mdi-calculator-variant"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small fw-bold text-uppercase">Total Selisih Kuantitas</h6>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-3 d-flex gap-2 align-items-center" style="font-size: 26px;">
                            <span class="qty-plus" id="lblQtyPlus">+0</span>
                            <span class="text-muted">/</span>
                            <span class="qty-minus" id="lblQtyMinus">-0</span>
                        </h3>
                        <p class="text-muted small mb-0">Akumulasi kelebihan & kekurangan qty</p>
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

            <!-- Ringkasan per Section Table -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card dashboard-card p-4">
                        <h5 class="card-title fw-bold mb-3">Ringkasan Selisih & Akurasi per Section</h5>
                        <div class="table-responsive">
                            <table class="table-custom" id="tableSectionSummary">
                                <thead>
                                    <tr>
                                        <th>Nama Section / Gudang</th>
                                        <th class="text-center">Total Diopname</th>
                                        <th class="text-center">Match (Sesuai)</th>
                                        <th class="text-center">Selisih Item</th>
                                        <th class="text-center">Akurasi (%)</th>
                                        <th class="text-center">Selisih Kuantitas (+ / -)</th>
                                        <th class="text-center">Status</th>
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
        let accuracyChart, sectionAccuracyChart;

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
                        updateKPIs(res.data.kpis);
                        renderAccuracyChart(res.data.charts.accuracy);
                        renderSectionAccuracyChart(res.data.ringkasanSections);
                        renderSectionSummaryTable(res.data.ringkasanSections);
                        renderTop10Table(res.data.top10);
                    }
                },
                error: function(xhr) {
                    console.error("Error loading dashboard data:", xhr);
                }
            });
        }

        function updateKPIs(kpis) {
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

            $('#lblQtyPlus').text(kpis.selisih_qty_pos);
            $('#lblQtyMinus').text(kpis.selisih_qty_neg);
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

        function renderSectionAccuracyChart(sections) {
            const categories = sections.map(s => s.name);
            const data = sections.map(s => s.accuracy);

            sectionAccuracyChart = Highcharts.chart('chartSectionAccuracy', {
                chart: {
                    type: 'column',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    max: 100,
                    title: {
                        text: 'Accuracy (%)'
                    },
                    labels: {
                        format: '{value}%'
                    }
                },
                tooltip: {
                    valueSuffix: '%'
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0,
                        borderRadius: 6,
                        colorByPoint: true,
                        colors: ['#3b82f6', '#8b5cf6', '#06b6d4', '#22c55e', '#ec4899', '#f59e0b', '#10b981']
                    }
                },
                series: [{
                    name: 'Akurasi Section',
                    data: data
                }]
            });
        }

        function renderSectionSummaryTable(data) {
            let html = '';
            if (data.length === 0) {
                html =
                    `<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data section ditemukan.</td></tr>`;
            } else {
                data.forEach(function(row) {
                    let badgeClass = 'badge-belum';
                    if (row.status === 'started') badgeClass = 'badge-progress';
                    if (row.status === 'finished') badgeClass = 'badge-selesai';

                    let qtyPlus = row.qty_lebih > 0 ? `+${row.qty_lebih.toLocaleString('id-ID')}` : '0';
                    let qtyMinus = row.qty_kurang < 0 ? row.qty_kurang.toLocaleString('id-ID') : '0';

                    let accuracyBadgeClass = 'accuracy-badge-low';
                    if (parseFloat(row.accuracy) >= 80.00) {
                        accuracyBadgeClass = 'accuracy-badge-high';
                    } else if (parseFloat(row.accuracy) >= 50.00) {
                        accuracyBadgeClass = 'accuracy-badge-mid';
                    }

                    html += `
                        <tr>
                            <td><strong>${row.name}</strong></td>
                            <td class="text-center">${row.diopname.toLocaleString('id-ID')}</td>
                            <td class="text-center">${row.match.toLocaleString('id-ID')}</td>
                            <td class="text-center text-danger">${row.selisih.toLocaleString('id-ID')}</td>
                            <td class="text-center">
                                <span class="${accuracyBadgeClass}">${row.accuracy}%</span>
                            </td>
                            <td class="text-center">
                                <span class="qty-plus small">${qtyPlus}</span> / <span class="qty-minus small">${qtyMinus}</span>
                            </td>
                            <td class="text-center">
                                <span class="status-badge ${badgeClass}">${row.status}</span>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#tableSectionSummary tbody').html(html);
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
