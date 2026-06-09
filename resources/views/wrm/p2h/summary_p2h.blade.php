@extends('layouts.app')

@section('title', ' | Summary P2H Online')

@section('styles')
    <style>
        .card-summary {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: none;
            transition: all 0.3s ease;
        }

        .card-summary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .gradient-header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: #ffffff;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.03);
        }

        .status-badge {
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.85rem;
        }

        .badge-layak {
            background-color: #d1f7d6;
            color: #198754;
        }

        .badge-perhatian {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-tidak-layak {
            background-color: #f8d7da;
            color: #842029;
        }

        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 8px;
        }

        .p2h-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #e2e8f0;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-summary gradient-header mb-4">
                        <div class="card-body px-4 py-2">
                            <div class="row align-items-center">
                                <div class="col-sm-8">
                                    <h2 class="text-white mb-2"><i
                                            class="mdi mdi-chart-bell-curve-cumulative me-2"></i>Summary & Monitoring P2H
                                        Online</h2>
                                    <p class="text-white-50 fs-15 mb-0">Analisis kondisi unit forklift dan pallet mover
                                        secara real-time.</p>
                                </div>
                                <div class="col-sm-4 text-end d-none d-sm-block">
                                    <i class="mdi mdi-shield-check-outline text-white-50" style="font-size: 72px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-pills nav-justified" id="p2hSummaryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-3 fs-15 fw-bold" id="summary-tab" data-bs-toggle="tab"
                                data-bs-target="#summary-pane" type="button" role="tab" aria-controls="summary-pane"
                                aria-selected="true">
                                <i class="mdi mdi-file-document-multiple-outline me-2"></i>P2H Summary & Belum P2H
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-3 fs-15 fw-bold" id="history-tab" data-bs-toggle="tab"
                                data-bs-target="#history-pane" type="button" role="tab" aria-controls="history-pane"
                                aria-selected="false">
                                <i class="mdi mdi-history me-2"></i>History P2H per Unit
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content mb-4" id="p2hSummaryTabContents">

                <!-- Tab 1: P2H Summary & Belum P2H -->
                <div class="tab-pane fade show active" id="summary-pane" role="tabpanel" aria-labelledby="summary-tab">
                    <!-- Filter Card -->
                    <div class="card card-summary mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="mdi mdi-filter-outline me-2"></i>Filter Periode & Tanggal
                                Harian</h5>
                        </div>
                        <div class="card-body">
                            <form id="formFilterSummary">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Tanggal Mulai</label>
                                        <input type="date" class="form-control" id="summaryStartDate"
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold">Tanggal Selesai</label>
                                        <input type="date" class="form-control" id="summaryEndDate"
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100"><i
                                                class="mdi mdi-magnify me-2"></i>Terapkan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Left: Lowest Health P2H -->
                        <div class="col-lg-8">
                            <div class="card card-summary h-100">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><i
                                            class="mdi mdi-alert-circle-outline me-2 text-danger"></i>Kesehatan Unit
                                        Terendah</h5>
                                    <span class="badge bg-soft-info text-info rounded-pill px-3 py-2">Urutan: Terendah ->
                                        Tertinggi</span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle text-nowrap"
                                            id="tableLowestHealth">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Unit No</th>
                                                    <th>Jenis</th>
                                                    <th>Shift</th>
                                                    <th>Operator</th>
                                                    <th class="text-center">Kesehatan</th>
                                                    <th class="text-center">Status Kelayakan</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Uncompleted P2H (Daily Only) -->
                        <div class="col-lg-4">
                            <div class="card card-summary h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0"><i
                                            class="mdi mdi-clock-alert-outline me-2 text-warning"></i>Belum P2H (<span
                                            id="targetCheckDateLabel">{{ date('d M Y') }}</span>)</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">Unit aktif yang belum melakukan pemeriksaan P2H pada
                                        tanggal harian di atas.</p>
                                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                        <table class="table table-bordered table-striped align-middle" id="tableBelumP2H">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Unit No</th>
                                                    <th>Jenis</th>
                                                    <th>Section</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: P2H History per Unit -->
                <div class="tab-pane fade" id="history-pane" role="tabpanel" aria-labelledby="history-tab">
                    <!-- Unit Select & Date Filter Card -->
                    <div class="card card-summary mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="mdi mdi-calendar-clock me-2"></i>Pilih Unit & Periode
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="formFilterHistory">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Pilih Unit Forklift / Pallet Mover</label>
                                        <select class="form-select select2-unit" id="historyUnit" required>
                                            <option value="">-- Cari Nomor Unit --</option>
                                            <optgroup label="Forklift">
                                                @foreach ($forklifts as $f)
                                                    <option value="{{ $f->nomor_unit }}|Forklift">Forklift -
                                                        {{ $f->nomor_unit }}</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="Pallet Mover">
                                                @foreach ($palletMovers as $p)
                                                    <option value="{{ $p->nomor_unit }}|Pallet Mover">Pallet Mover -
                                                        {{ $p->nomor_unit }}</option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Dari Tanggal</label>
                                        <input type="date" class="form-control" id="historyStartDate"
                                            value="{{ date('Y-m-01') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Sampai Tanggal</label>
                                        <input type="date" class="form-control" id="historyEndDate"
                                            value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100"><i
                                                class="mdi mdi-history me-2"></i>Tampilkan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- History Table Card -->
                    <div class="card card-summary">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="mdi mdi-format-list-bulleted me-2"></i>Daftar Riwayat
                                P2H Unit: <span class="text-primary fw-bold" id="historyUnitNameDisplay">-</span></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle text-nowrap"
                                    id="tableHistory">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Shift</th>
                                            <th>Nama Operator</th>
                                            <th class="text-center">Kesehatan (%)</th>
                                            <th class="text-center">Status Kelayakan</th>
                                            <th>Catatan Kerusakan / Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Silakan pilih unit dan
                                                periode terlebih dahulu lalu klik Tampilkan.</td>
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
            // Initialize Select2
            $('.select2-unit').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // 1. AJAX load Summary & Belum P2H
            function loadSummary() {
                let startDate = $('#summaryStartDate').val();
                let endDate = $('#summaryEndDate').val();

                // Format date for label
                let parsedDate = new Date(startDate);
                let options = {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                };
                $('#targetCheckDateLabel').text(parsedDate.toLocaleDateString('id-ID', options));

                $.ajax({
                    url: "{{ route('p2h.online.summary.data') }}",
                    method: "GET",
                    data: {
                        start_date: startDate,
                        end_date: endDate
                    },
                    beforeSend: function() {
                        $('#tableLowestHealth tbody').html(`
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2 text-muted">Memuat data kesehatan unit...</div>
                            </td>
                        </tr>
                    `);
                        $('#tableBelumP2H tbody').html(`
                        <tr>
                            <td colspan="3" class="text-center py-4">
                                <div class="spinner-border text-warning" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    `);
                    },
                    success: function(res) {
                        // Render Lowest Health
                        let healthHtml = '';
                        if (res.records.length === 0) {
                            healthHtml = `
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Tidak ada data pemeriksaan P2H pada periode yang dipilih.
                                </td>
                            </tr>
                        `;
                        } else {
                            res.records.forEach(function(item) {
                                let badgeClass = 'badge-layak';
                                if (item.status === 'Tidak Layak' || item.status.includes(
                                        'Tidak Layak')) {
                                    badgeClass = 'badge-tidak-layak';
                                } else if (item.status === 'Perlu Perhatian') {
                                    badgeClass = 'badge-perhatian';
                                }

                                let progressColor = 'bg-success';
                                if (item.persentase < 70) {
                                    progressColor = 'bg-danger';
                                } else if (item.persentase < 85) {
                                    progressColor = 'bg-warning';
                                }

                                healthHtml += `
                                <tr>
                                    <td>${item.tanggal}</td>
                                    <td><b class="text-primary">${item.nomor_unit}</b></td>
                                    <td>
                                        <span class="badge ${item.jenis_p2h === 'Forklift' ? 'bg-soft-danger text-danger' : 'bg-soft-primary text-primary'}">
                                            ${item.jenis_p2h}
                                        </span>
                                    </td>
                                    <td>Shift ${item.shift}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="p2h-avatar">${item.operator_name.charAt(0).toUpperCase()}</div>
                                            <span>${item.operator_name}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center flex-column" style="min-width: 100px;">
                                            <div class="fw-bold text-dark mb-1">${item.persentase}%</div>
                                            <div class="progress w-100" style="height: 6px; border-radius: 10px;">
                                                <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${item.persentase}%" aria-valuenow="${item.persentase}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge ${badgeClass}">${item.status}</span>
                                    </td>
                                    <td style="max-width: 250px; white-space: normal; font-size: 0.9rem;">
                                        ${item.catatan}
                                    </td>
                                </tr>
                            `;
                            });
                        }
                        $('#tableLowestHealth tbody').html(healthHtml);

                        // Render Belum P2H
                        let belumHtml = '';
                        if (res.belum_p2h.length === 0) {
                            belumHtml = `
                            <tr>
                                <td colspan="3" class="text-center text-success py-4 fw-bold">
                                    <i class="mdi mdi-checkbox-marked-circle-outline me-1 fs-16"></i> Semua unit aktif sudah P2H!
                                </td>
                            </tr>
                        `;
                        } else {
                            res.belum_p2h.forEach(function(item) {
                                belumHtml += `
                                <tr>
                                    <td><b class="text-danger">${item.nomor_unit}</b></td>
                                    <td>
                                        <span class="badge ${item.jenis_p2h === 'Forklift' ? 'bg-soft-danger text-danger' : 'bg-soft-primary text-primary'}">
                                            ${item.jenis_p2h}
                                        </span>
                                    </td>
                                    <td class="small text-muted">${item.section}</td>
                                </tr>
                            `;
                            });
                        }
                        $('#tableBelumP2H tbody').html(belumHtml);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memuat Data',
                            text: 'Terjadi kesalahan sistem saat memuat data summary.'
                        });
                    }
                });
            }

            // Trigger load on submit of filter summary
            $('#formFilterSummary').on('submit', function(e) {
                e.preventDefault();
                loadSummary();
            });

            // Load initially
            loadSummary();

            // 2. AJAX load P2H History per Unit
            $('#formFilterHistory').on('submit', function(e) {
                e.preventDefault();
                let unitSelectVal = $('#historyUnit').val();
                let startDate = $('#historyStartDate').val();
                let endDate = $('#historyEndDate').val();

                if (!unitSelectVal) {
                    Swal.fire('Perhatian', 'Silakan pilih unit terlebih dahulu.', 'warning');
                    return;
                }

                let parts = unitSelectVal.split('|');
                let nomorUnit = parts[0];
                let jenisP2h = parts[1];

                $('#historyUnitNameDisplay').text(`${jenisP2h} - ${nomorUnit}`);

                $.ajax({
                    url: "{{ route('p2h.online.summary.history') }}",
                    method: "GET",
                    data: {
                        nomor_unit: nomorUnit,
                        jenis_p2h: jenisP2h,
                        start_date: startDate,
                        end_date: endDate
                    },
                    beforeSend: function() {
                        $('#tableHistory tbody').html(`
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2 text-muted">Memuat riwayat P2H unit...</div>
                            </td>
                        </tr>
                    `);
                    },
                    success: function(res) {
                        let historyHtml = '';
                        if (res.records.length === 0) {
                            historyHtml = `
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Tidak ada riwayat P2H untuk unit ini pada periode terpilih.
                                </td>
                            </tr>
                        `;
                        } else {
                            res.records.forEach(function(item) {
                                let badgeClass = 'badge-layak';
                                if (item.status === 'Tidak Layak' || item.status
                                    .includes('Tidak Layak')) {
                                    badgeClass = 'badge-tidak-layak';
                                } else if (item.status === 'Perlu Perhatian') {
                                    badgeClass = 'badge-perhatian';
                                }

                                let progressColor = 'bg-success';
                                if (item.persentase < 70) {
                                    progressColor = 'bg-danger';
                                } else if (item.persentase < 85) {
                                    progressColor = 'bg-warning';
                                }

                                historyHtml += `
                                <tr>
                                    <td><b>${item.tanggal}</b></td>
                                    <td>Shift ${item.shift}</td>
                                    <td>${item.operator_name}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center flex-column" style="min-width: 90px; max-width: 130px; margin: 0 auto;">
                                            <span class="fw-bold text-dark mb-1">${item.persentase}%</span>
                                            <div class="progress w-100" style="height: 5px; border-radius: 10px;">
                                                <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${item.persentase}%" aria-valuenow="${item.persentase}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge ${badgeClass}">${item.status}</span>
                                    </td>
                                    <td style="white-space: normal; max-width: 400px;">
                                        ${item.catatan}
                                    </td>
                                </tr>
                            `;
                            });
                        }
                        $('#tableHistory tbody').html(historyHtml);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memuat Riwayat',
                            text: 'Terjadi kesalahan sistem saat memuat riwayat P2H.'
                        });
                    }
                });
            });
        });
    </script>
@endsection
