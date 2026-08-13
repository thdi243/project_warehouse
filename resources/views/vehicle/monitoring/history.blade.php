@extends('layouts.app')

@section('title', '| Laporan History Kendaraan')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Laporan History Kendaraan</h4>
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
                            <div class="row align-items-center g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Cari Kendaraan /
                                        SPB</label>
                                    <div class="search-box">
                                        <input type="text" id="reportSearch" class="form-control"
                                            placeholder="Cari Plat Nomor, No. SPB, No. Transaksi...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Mulai Tanggal</label>
                                    <input type="date" id="reportStartDate" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Sampai Tanggal</label>
                                    <input type="date" id="reportEndDate" class="form-control">
                                </div>
                                <div class="col-md-2 mt-md-4 pt-1">
                                    <button type="button" class="btn btn-soft-danger w-100" id="btnReportReset">
                                        <i class="ri-refresh-line me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="reportTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. Transaksi</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Jenis / Item</th>
                                            <th>No. SPB / Qty</th>
                                            <th>Tujuan Asal</th>
                                            <th>Waktu Check-In</th>
                                            <th>Waktu Check-Out</th>
                                            <th>Total Durasi</th>
                                            <th>Rute Perpindahan</th>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            function loadReports() {
                const search = $('#reportSearch').val();
                const startDate = $('#reportStartDate').val();
                const endDate = $('#reportEndDate').val();
                const tbody = $('#reportTable tbody');

                tbody.html(
                    '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>'
                    );

                $.ajax({
                    url: "{{ route('vehicle.monitoring.history.data') }}",
                    type: "GET",
                    data: {
                        search: search,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(response) {
                        tbody.empty();
                        if (response.length === 0) {
                            tbody.html(
                                '<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada histori kendaraan yang cocok.</td></tr>'
                                );
                            return;
                        }

                        response.forEach(tx => {
                            const row = `
                                <tr>
                                    <td><small class="fw-bold">${tx.no_transaction}</small></td>
                                    <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                                    <td>
                                        <strong>${tx.vendor}</strong><br>
                                        <small class="text-muted">Driver: ${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                                    </td>
                                    <td>
                                        <strong>${tx.jenis.toUpperCase()}</strong><br>
                                        <small class="text-muted">${tx.item_name}</small>
                                    </td>
                                    <td>
                                        <strong>${tx.no_spb ?? '-'}</strong><br>
                                        <small class="text-muted">${tx.qty_spb ?? '-'}</small>
                                    </td>
                                    <td><span class="badge bg-soft-info text-info">${tx.target_name}</span></td>
                                    <td>${tx.check_in}</td>
                                    <td>${tx.check_out}</td>
                                    <td><span class="badge bg-soft-success text-success">${tx.duration}</span></td>
                                    <td><small class="text-muted" style="white-space: normal;">${tx.history_path}</small></td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    },
                    error: function(xhr) {
                        tbody.html(
                            '<tr><td colspan="10" class="text-center py-4 text-danger">Gagal memuat histori data.</td></tr>'
                            );
                    }
                });
            }

            // Debouncer for report search input
            let reportSearchTimer;
            $('#reportSearch').on('keyup', function() {
                clearTimeout(reportSearchTimer);
                reportSearchTimer = setTimeout(loadReports, 500);
            });

            $('#reportStartDate, #reportEndDate').on('change', function() {
                loadReports();
            });

            $('#btnReportReset').on('click', function() {
                $('#reportSearch').val('');
                $('#reportStartDate').val('');
                $('#reportEndDate').val('');
                loadReports();
            });

            // Initial load
            loadReports();
        });
    </script>
@endsection
