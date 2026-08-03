@extends('layouts.app')

@section('styles')
    <style>
        :root {
            --primary-color: #f96060;
            --primary-dark: #4840a6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        .page-header {
            background: linear-gradient(135deg, #f96060 0%, #e37220 100%);
            color: white;
            padding: 2rem 0;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 10px 30px rgba(229, 57, 53, 0.2);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #c44141;
            border-color: #c44141;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 0.75rem;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        .btn-action {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .stat-card {
            border-left: 3px solid;
            padding: 1rem;
            border-radius: 0.5rem;
            background: #f8f9fa;
        }

        .stat-card.success {
            border-left-color: #28a745;
        }

        .stat-card.danger {
            border-left-color: #dc3545;
        }

        .stat-card.secondary {
            border-left-color: #6c757d;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
            cursor: pointer;
        }

        .modal-xl {
            max-width: 1200px;
        }

        .detail-header {
            background: linear-gradient(135deg, #f96060 0%, #e37220 100%);
            color: white;
        }

        .approval-section {
            background-color: #f9fafb;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
        }

        .report-tabs .nav-link {
            color: var(--gray-600);
            border: 1px solid var(--gray-200);
            margin-right: 0.5rem;
            font-weight: 600;
        }

        .report-tabs .nav-link.active {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .approval-stat {
            border-radius: 0.5rem;
            padding: 0.85rem 1rem;
            min-height: 82px;
        }

        .approval-stat .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .approval-note {
            max-width: 320px;
            white-space: normal;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-3" data-aos="fade-down">
                <div class="container-fluid">
                    <h1 class="h2 fw-bold mb-2 text-white">
                        <i class="mdi mdi-file-document-outline me-2"></i>
                        Laporan Stock Opname
                    </h1>
                    <p class="mb-0 opacity-90">Kelola dan pantau stock opname</p>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card shadow-sm mb-4 p-2" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body">
                    <div class="row g-3 align-items-end">

                        {{-- Filter Tanggal --}}
                        <div class="@if (Auth::user()->jabatan != 'operator') col-md-3 @else col-md-3 @endif col-12">
                            <div>
                                <label class="form-label" for="filter_tanggal">Tanggal</label>
                                <input type="date" id="filter_tanggal" class="form-control"
                                    value="{{ request('tanggal', now()->toDateString()) }}">
                            </div>
                        </div>

                        {{-- Filter Jenis SO --}}
                        <div class="col-md-2 col-12">
                            <label class="form-label fw-semibold" for="jenis_so">Jenis SO</label>
                            <select id="jenis_so" class="form-select">
                                <option value="cycle_count">Cycle Count</option>
                                <option value="monthly">Monthly SO</option>
                            </select>
                        </div>

                        {{-- Filter Principal untuk non-operator --}}
                        @if (Auth::user()->jabatan != 'operator')
                            <div class="col-md-3 col-12">
                                <select id="principal_filter" class="form-select">
                                    @foreach ($principals as $p)
                                        <option value="{{ $p }}"
                                            {{ request('principal') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-3">
                            <label for="searchBarang" class="form-label fw-semibold">Cari Data</label>
                            <div class="input-group">
                                <input type="text" id="searchBarang" class="form-control" placeholder="Ketik MID...">
                                <button type="button" id="btnSearchBarang" class="btn btn-outline-primary">
                                    <i class="mdi mdi-magnify"></i> Cari
                                </button>
                            </div>
                        </div>

                        {{-- Tombol Export & Approval --}}
                        <div class="col-md-4 col-12">
                            <div class="d-flex gap-2">
                                <button type="button" id="btn_export" class="btn btn-outline-warning d-none flex-fill">
                                    <i class="mdi mdi-export me-2"></i> Export PDF
                                </button>

                                @if (Auth::user()->jabatan == 'operator')
                                    <button type="button" id="btn_approval"
                                        class="btn btn-outline-success d-none flex-fill">
                                        <i class="mdi mdi-send me-2"></i> Send Approval
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div id="approvalWrapper"></div>
                </div>
            </div>

            <!-- Report Card -->
            <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body">
                    <ul class="nav nav-pills report-tabs mb-3" id="reportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="data-tab" data-bs-toggle="pill" data-bs-target="#tabData"
                                type="button" role="tab" aria-controls="tabData" aria-selected="true">
                                <i class="mdi mdi-table me-1"></i> Data Opname
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link d-none" id="approval-tab" data-bs-toggle="pill"
                                data-bs-target="#tabApproval" type="button" role="tab" aria-controls="tabApproval"
                                aria-selected="false">
                                <i class="mdi mdi-account-clock-outline me-1"></i> Belum Approve
                                <span id="pendingApprovalBadge" class="badge bg-warning text-dark ms-1">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pallet-counter-tab" data-bs-toggle="pill"
                                data-bs-target="#tabPalletCounter" type="button" role="tab"
                                aria-controls="tabPalletCounter" aria-selected="false">
                                <i class="mdi mdi-counter me-1"></i> Pallet Counter
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tabData" role="tabpanel" aria-labelledby="data-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle shadow-sm rounded-3 text-nowrap sortable"
                                    id="tableOpname">
                                    <thead class="bg-soft-info text-dark border-bottom">
                                        <tr>
                                            <th class="text-center no-sort" style="width: 70px;">ID</th>
                                            <th class="no-sort">Tanggal Opname</th>
                                            <th class="no-sort">MID Barang</th>
                                            <th class="no-sort">Nama Barang</th>
                                            <th>Qty SAP</th>
                                            <th>Qty Fisik</th>
                                            <th>Selisih</th>
                                            <th>Keterangan</th>
                                            <th class="text-center no-sort" style="width: 130px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <!-- Data akan dimuat di sini -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Loading State -->
                            <div id="loading_state" class="text-center py-5" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2">Memuat data...</p>
                            </div>

                            <!-- Empty State -->
                            <div id="empty_state" class="text-center py-5" style="display: none;">
                                <img src="{{ asset('assets/images/empty_state.png') }}" alt="Empty"
                                    style="width:150px;">
                                <p class="text-muted">Tidak ada data yang ditemukan</p>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tabApproval" role="tabpanel" aria-labelledby="approval-tab">
                            <div class="row g-3 mb-3" id="approvalStats">
                                <div class="col-md-3 col-6">
                                    <div class="approval-stat bg-light">
                                        <div class="text-muted small mb-2">SO Pending</div>
                                        <div class="stat-value" id="approvalTotal">0</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="approval-stat bg-light">
                                        <div class="text-muted small mb-2">Belum Approve</div>
                                        <div class="stat-value text-warning" id="approvalPending">0</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="approval-stat bg-light">
                                        <div class="text-muted small mb-2">Dibaca</div>
                                        <div class="stat-value text-info" id="approvalApproved">0</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="approval-stat bg-light">
                                        <div class="text-muted small mb-2">Menunggu</div>
                                        <div class="stat-value text-secondary" id="approvalRejected">0</div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table stripped table-hover align-middle text-nowrap">
                                    <thead class="bg-light border-bottom">
                                        <tr>
                                            <th style="width: 70px;" class="text-center">No</th>
                                            <th>Tanggal Opname</th>
                                            <th>Principal</th>
                                            <th>Stock Control</th>
                                            <th>Tanggal Request</th>
                                            <th>Approver</th>
                                            <th>Jabatan</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pendingApprovalBody">
                                        <!-- Data approval akan dimuat di sini -->
                                    </tbody>
                                </table>
                            </div>

                            <div id="approval_empty_state" class="text-center py-5" style="display:none;">
                                <i class="mdi mdi-check-circle-outline text-success" style="font-size: 56px;"></i>
                                <p class="text-muted mb-0">Tidak ada approval yang masih pending.</p>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tabPalletCounter" role="tabpanel"
                            aria-labelledby="pallet-counter-tab">
                            <div class="row g-3 mb-3" id="palletCounterStats">
                                <div class="col-md-3 col-6">
                                    <div class="approval-stat bg-soft-primary">
                                        <div class="text-primary small mb-2 fw-semibold">Total Pallet Terhitung</div>
                                        <div class="stat-value text-primary" id="sumPalletCount">0</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="approval-stat bg-soft-success">
                                        <div class="text-success small mb-2 fw-semibold">Total Box</div>
                                        <div class="stat-value text-success" id="sumBoxCount">0</div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle shadow-sm rounded-3 text-nowrap"
                                    id="tablePalletCounter">
                                    <thead class="bg-soft-info text-dark border-bottom">
                                        <tr>
                                            <th class="text-center" style="width: 70px;">No</th>
                                            <th>MID Barang</th>
                                            <th>Nama Barang</th>
                                            <th>UOM</th>
                                            <th class="text-end">Total Pallet</th>
                                            <th class="text-end">Total Box</th>
                                            <th class="text-center" style="width: 100px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="palletCounterBody">
                                        <!-- Data akan dimuat di sini -->
                                    </tbody>
                                </table>
                            </div>

                            <div id="pallet_counter_empty_state" class="text-center py-5" style="display:none;">
                                <i class="mdi mdi-database-off-outline text-muted" style="font-size: 56px;"></i>
                                <p class="text-muted mb-0">Tidak ada data pallet counter (UOM BOX) untuk opname ini.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header detail-header">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-clipboard-list"></i> Detail Stock Opname
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Modal content will be loaded here -->
                    <div id="modalContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Send Approval -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Approver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formApproval">
                        <div id="approverList">
                            <p class="text-muted text-center">Memuat daftar approver...</p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnSendApproval" class="btn btn-success">Kirim Approval</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Send Report --}}
    <div class="modal fade" id="sendReportModal" tabindex="-1" aria-labelledby="sendReportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="sendReportModalLabel">Kirim Report ke Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="form_send_report">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tanggal_send" class="form-label fw-semibold">Tanggal</label>
                            <input type="date" id="tanggal_send" name="tanggal_send" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="principal_send" class="form-label fw-semibold">Principal</label>
                            <select id="principal_send" name="principal_send" class="form-select" required>
                                <option value="">-- Pilih Principal --</option>
                                @foreach ($principals as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="managerContainer">
                            <p class="text-muted">Memuat data manager...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-send"></i> Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data Temp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Data edit akan dimasukkan di sini -->
                </div>
                <div class="modal-footer">
                    <button type="button" id="saveEditBtn" class="btn btn-success">Simpan Semua</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // trigger filter
            const params = new URLSearchParams(window.location.search);
            const tanggal = params.get('tanggal');
            const principalFromUrl = params.get('principal');
            const jenisSoFromUrl = params.get('jenis_so');

            if (jenisSoFromUrl) {
                $('#jenis_so').val(jenisSoFromUrl);
            }

            // Adjust date input type based on current selected jenis_so
            const initialJenisSo = $('#jenis_so').val();
            const dateInput = $('#filter_tanggal');
            const approvalTabBtn = $('#approval-tab');
            const btnApproval = $('#btn_approval');
            const btnExport = $('#btn_export');

            if (initialJenisSo === 'monthly') {
                dateInput.attr('type', 'month');
                approvalTabBtn.removeClass('d-none');
                btnApproval.removeClass('d-none');
                btnExport.removeClass('d-none');
            } else {
                dateInput.attr('type', 'date');
                approvalTabBtn.addClass('d-none');
                btnApproval.addClass('d-none');
                btnExport.addClass('d-none');
            }

            if (tanggal) {
                let formattedTanggal = tanggal;
                if (initialJenisSo === 'monthly' && tanggal.length === 10) {
                    formattedTanggal = tanggal.substring(0, 7);
                } else if (initialJenisSo === 'cycle_count' && tanggal.length === 7) {
                    formattedTanggal = tanggal + '-01';
                }
                dateInput.val(formattedTanggal);
            }

            let currentSearch = '';
            let currentPrincipal = '';

            if (principalFromUrl) {
                currentPrincipal = principalFromUrl;
                $('#principal_filter').val(currentPrincipal);
            } else {
                currentPrincipal = $('#principal_filter').val() || '';
            }

            // jenis_so change handler
            $('#jenis_so').on('change', function() {
                const type = $(this).val();
                const dateInput = $('#filter_tanggal');
                const approvalTabBtn = $('#approval-tab');
                const btnApproval = $('#btn_approval');
                const btnExport = $('#btn_export');
                let val = dateInput.val();

                if (type === 'monthly') {
                    dateInput.attr('type', 'month');
                    if (val && val.length === 10) dateInput.val(val.substring(0, 7));
                    else if (!val) {
                        const t = new Date();
                        dateInput.val(t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0'));
                    }
                    approvalTabBtn.removeClass('d-none');
                    btnApproval.removeClass('d-none');
                    btnExport.removeClass('d-none');
                } else {
                    dateInput.attr('type', 'date');
                    const t = new Date();
                    const todayStr = t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0');
                    if (val && val.length === 10) dateInput.val(val);
                    else dateInput.val(todayStr);
                    approvalTabBtn.addClass('d-none');
                    btnApproval.addClass('d-none');
                    btnExport.addClass('d-none');
                    if (approvalTabBtn.hasClass('active')) {
                        $('#data-tab').tab('show');
                    }
                }
                loadReportData(currentPrincipal, currentSearch);
                checkApprovalStatus(null, dateInput.val());
            });

            loadReportData(currentPrincipal, currentSearch);
            checkApprovalStatus(null, $('#filter_tanggal').val());

            $(document).on('keyup change', '#filter_tanggal', function() {
                loadReportData($('#principal_filter').val() || '');
            });

            // principal filter
            $('#principal_filter').on('keyup change', function() {
                currentPrincipal = $(this).val();
                loadReportData(currentPrincipal,
                    currentSearch);
                const tanggal = $('#filter_tanggal').val();
                checkApprovalStatus(null, tanggal);
            });

            $('#searchBarang').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    currentSearch = $(this).val().trim();
                    loadReportData(currentPrincipal, currentSearch);
                }
            });

            $('#btnSearchBarang').on('click', function() {
                currentSearch = $('#searchBarang').val().trim();
                loadReportData(currentPrincipal, currentSearch);
            });

            function loadReportData(principal = '', search = '') {
                $('#loading_state').show();
                $('#empty_state').hide();
                $('#tableBody').html('');
                renderApprovalSummary(null);
                if ($('#jenis_so').val() === 'monthly') {
                    loadPendingApprovals(principal);
                }
                const tanggal = $('#filter_tanggal').val() || new Date().toISOString().slice(0,
                    10);

                $.ajax({
                    url: "{{ route('wfg.stock_opname.report.getData') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        tanggal: $('#filter_tanggal').val(),
                        principal: principal,
                        search: search,
                        jenis_so: $('#jenis_so').val()
                    },
                    success: function(response) {
                        $('#loading_state').hide();

                        checkApprovalStatus(response, tanggal);

                        if (!response.summaries || response.summaries.length === 0) {
                            $('#empty_state').show();
                            renderPalletCounter(null);
                        } else {
                            renderTable(response);
                            renderPalletCounter(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loading_state').hide();
                        renderApprovalSummary(null);
                        renderPalletCounter(null);
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="9" class="text-center text-danger py-4">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Gagal memuat data: ${error}
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            function loadPendingApprovals(principal = '') {
                $.ajax({
                    url: "{{ route('wfg.stock_opname.report.pending-approval') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        principal: principal
                    },
                    success: function(response) {
                        renderApprovalSummary(response);
                    },
                    error: function() {
                        renderApprovalSummary(null);
                    }
                });
            }

            function renderApprovalSummary(response) {
                const summary = response?.approval_summary || null;
                const pending = summary?.pending || [];
                const items = summary?.items || [];
                const pendingCount = summary?.pending_count || 0;

                $('#pendingApprovalBadge').text(pendingCount);
                $('#approvalTotal').text(summary?.total_sop || 0);
                $('#approvalPending').text(pendingCount);
                $('#approvalApproved').text(summary?.read_count || 0);
                $('#approvalRejected').text(summary?.waiting_count || 0);

                if (!summary || items.length === 0) {
                    $('#pendingApprovalBody').html('');
                    $('#pendingApprovalBody').closest('.table-responsive').hide();
                    $('#approval_empty_state').show().find('p').text('Belum ada data approval pending.');
                    return;
                }

                if (pending.length === 0) {
                    $('#pendingApprovalBody').html('');
                    $('#pendingApprovalBody').closest('.table-responsive').hide();
                    $('#approval_empty_state').show().find('p').text('Tidak ada approval yang masih pending.');
                    return;
                }

                $('#approval_empty_state').hide();
                $('#pendingApprovalBody').closest('.table-responsive').show();

                const rows = pending.map((approval, index) => {
                    const status = (approval.status || 'pending').toLowerCase();
                    const statusLabel = status === 'read' ? 'Not Send Approval' : 'Waiting';
                    const statusClass = status === 'read' ? 'info' : 'warning';

                    return `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${escapeHtml(formatDate(approval.tgl_opname || '-'))}</td>
                            <td>${escapeHtml(approval.principal || '-')}</td>
                            <td>${escapeHtml(approval.operator || '-')}</td>
                            <td>${escapeHtml(approval.requested_at || '-')}</td>
                            <td>
                                <div class="fw-semibold">${escapeHtml(approval.nama || '-')}</div>
                            </td>
                            <td>${escapeHtml(approval.jabatan || '-')}</td>
                            <td>
                                <span class="badge badge-soft-${statusClass}">
                                    ${statusLabel}
                                </span>
                            </td>
                            <td class="approval-note">${escapeHtml(approval.catatan || '-')}</td>
                        </tr>
                    `;
                }).join('');

                $('#pendingApprovalBody').html(rows);
            }

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"'`=\/]/g, function(char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '`': '&#96;',
                        '=': '&#61;',
                        '/': '&#47;'
                    } [char];
                });
            }

            function checkApprovalStatus(data = null, tanggal) {
                const wrapper = $('#approvalWrapper');
                const jenisSo = $('#jenis_so').val();

                console.log(jenisSo);

                if (jenisSo === 'cycle_count') {
                    wrapper.html('');
                    $('#btn_approval').hide();
                    $('#filter_tanggal').removeData('sop-id');
                    return;
                }

                // Jika data SOP tidak ada
                if (!data?.sop) {
                    wrapper.html(`
                        <div class="alert alert-info rounded-3 mt-3">
                            <i class="mdi mdi-information-outline me-2"></i>
                            <strong>Belum ada SO untuk tanggal ${tanggal}</strong>
                        </div>
                    `);
                    $('#btn_approval').hide();
                    $('#filter_tanggal').removeData('sop-id');
                    return;
                }

                // SOP ada
                $('#filter_tanggal').data('sop-id', data.sop.id);
                const sopId = $('#filter_tanggal').data('sop-id');
                const tanggalFilter = tanggal;
                // const principalFilter = $('#principal_filter').val();

                $.get("{{ route('wfg.stock_opname.approval.show', '') }}/" + sopId, {
                    tanggal: tanggalFilter,
                    principal: currentPrincipal,
                    jenisSo: jenisSo
                }, function(res) {
                    console.log('Approval status response:', res);
                    const status = res.approval_status;
                    const statusSop = res.status_sop;
                    const note = res.approval_note || '';
                    const isApprover = res.is_approver || false;
                    const isOperator = res.is_operator;
                    // const isOperator = @json(Auth::user()->jabatan === 'operator');

                    if (!status) {
                        wrapper.html('');
                        if (isOperator) $('#btn_approval').hide();
                        return;
                    }

                    console.log('isOperator:', isOperator);

                    // ===================== OPERATOR =====================
                    let trackingHtml = '';

                    if (res.approver_tracking && res.approver_tracking.length > 0) {
                        trackingHtml = `
                            <div class="mt-4">
                                <h6 class="fw-semibold mb-3">
                                    <i class="mdi mdi-account-check-outline text-primary me-2"></i>
                                    Riwayat Persetujuan
                                </h6>
                                <ul class="list-unstyled mb-0">
                        `;

                        res.approver_tracking.forEach(a => {
                            const s = a.status?.toLowerCase() || '';
                            let icon = '<i class="mdi mdi-timer-sand text-warning me-1"></i>';
                            let badgeClass = 'warning';
                            let statusLabel = s.charAt(0).toUpperCase() + s.slice(1); // capitalize

                            if (s === 'approved') {
                                icon = '<i class="mdi mdi-check-circle text-success me-1"></i>';
                                badgeClass = 'success';
                            } else if (s === 'rejected') {
                                icon = '<i class="mdi mdi-close-circle text-danger me-1"></i>';
                                badgeClass = 'danger';
                            } else if (s === 'read') {
                                icon = '<i class="mdi mdi-eye text-info me-1"></i>';
                                badgeClass = 'info';
                                statusLabel = 'Dibaca';
                            } else if (s === 'pending') {
                                statusLabel = 'Menunggu';
                            }

                            trackingHtml += `
                                <li class="mb-3 ps-1">
                                    ${icon}
                                    <strong>${a.nama || '-'}</strong> 
                                    <span class="text-muted">(${a.jabatan || '-'})</span>
                                    <span class="badge bg-${badgeClass} ms-2">${statusLabel}</span>
                                    ${a.catatan ? `<br><small class="text-muted ms-4 d-block mt-1">Catatan: ${a.catatan}</small>` : ''}
                                    ${a.action_at ? `<br><small class="text-muted ms-4">Pada: ${a.action_at}</small>` : ''}
                                </li>
                            `;
                        });

                        trackingHtml += `
                                </ul>
                            </div>
                        `;
                    }

                    if (isOperator) {
                        const btn = $('#btn_approval');
                        btn.show();

                        //  Status Sop draft
                        if (statusSop === 'draft') {
                            btn.removeClass('btn-secondary btn-soft-success')
                                .addClass('btn-soft-success')
                                .prop('disabled', false)
                                .html('<i class="mdi mdi-send-outline me-2"></i> Send Approval');

                            wrapper.html('');
                        }

                        //  Status pending / read
                        else if (status === 'pending' || status === 'read') {
                            btn.removeClass('btn-soft-success').addClass('btn-secondary')
                                .prop('disabled', true)
                                .html('<i class="mdi mdi-timer-sand me-2"></i> Menunggu Approve');

                            wrapper.html(`
                                <div class="alert alert-warning rounded-3 mt-3">
                                    <i class="mdi mdi-timer-sand me-2"></i>
                                    <strong>Menunggu Persetujuan</strong>
                                    <br><small class="text-muted">Data sedang dalam proses approval oleh Foreman/Supervisor.</small>
                                </div>
                                ${trackingHtml}
                            `);
                        }

                        // 🔸 Status approved
                        else if (status === 'approved') {
                            btn.removeClass('btn-soft-success').addClass('btn-secondary')
                                .prop('disabled', true)
                                .html('<i class="mdi mdi-check me-2"></i> Approval Selesai');

                            wrapper.html(`
                                <div class="alert alert-success rounded-3 mt-3">
                                    <i class="mdi mdi-check-decagram-outline me-2"></i>
                                    <strong>Sudah Disetujui</strong>
                                    ${note ? `<br><small class="text-muted">Catatan: ${note}</small>` : ''}
                                </div>
                                ${trackingHtml}
                            `);
                        }

                        // Status rejected
                        else if (status === 'rejected') {
                            btn.removeClass('btn-secondary').addClass('btn-soft-success')
                                .prop('disabled', false)
                                .html('<i class="mdi mdi-send-outline me-2"></i> Send Approval');

                            wrapper.html(`
                                <div class="alert alert-danger rounded-3 mt-3">
                                    <i class="mdi mdi-close-octagon-outline me-2"></i>
                                    <strong>Ditolak</strong>
                                    ${note ? `<br><small class="text-muted">Catatan: ${note}</small>` : ''}
                                </div>
                                ${trackingHtml}
                            `);
                        }
                    }

                    // ===================== NON-OPERATOR =====================
                    else {
                        // Jika user adalah approver
                        if (isApprover && (status === 'pending' || status === 'read')) {
                            wrapper.html(`
                            <hr class="my-4">
                            <div class="approval-section">
                                <h6 class="fw-semibold mb-3">
                                    <i class="mdi mdi-check-decagram-outline text-success me-2"></i>
                                    Persetujuan SO
                                </h6>
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-8">
                                        <label for="approval_note" class="form-label small text-muted mb-1">
                                            Keterangan / Komentar
                                        </label>
                                        <textarea id="approval_note" class="form-control" rows="2" placeholder="Tulis catatan Anda (opsional)..."></textarea>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" id="btn_approve" class="btn btn-success w-50">
                                                <i class="mdi mdi-check me-1"></i> Approve
                                            </button>
                                            <button type="button" id="btn_reject" class="btn btn-danger w-50">
                                                <i class="mdi mdi-close me-1"></i> Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                        }

                        // Jika user approver tapi ingin lihat status saja
                        else if (isApprover && (status === 'approved' || status === 'rejected')) {
                            wrapper.html(`
                                <div class="alert ${status === 'approved' ? 'alert-success' : 'alert-danger'} rounded-3 mt-3">
                                    <i class="mdi ${status === 'approved' ? 'mdi-check-decagram-outline' : 'mdi-close-octagon-outline'} me-2"></i>
                                    <strong>${status === 'approved' ? 'Sudah Disetujui' : 'Ditolak'}</strong>
                                    ${note ? `<br><small class="text-muted">Catatan: ${note}</small>` : ''}
                                </div>
                                ${trackingHtml}
                            `);
                        } else if (status === 'approved' || status === 'rejected') {
                            const labelHtml = `
                                <div class="alert ${status === 'approved' ? 'alert-success' : 'alert-danger'} rounded-3 mt-3">
                                    <i class="mdi ${status === 'approved' ? 'mdi-check-decagram-outline' : 'mdi-close-octagon-outline'} me-2"></i>
                                    <strong>${status === 'approved' ? 'Sudah Disetujui' : 'Ditolak'}</strong>
                                    ${note ? `<br><small class="text-muted">Catatan: ${note}</small>` : ''}
                                </div>
                            `;
                            wrapper.html(labelHtml);
                        }

                        // Jika user approver & status masih pending → hanya lihat alert
                        else if (status === 'pending' || status === 'read') {
                            wrapper.html(`
                                <div class="alert alert-warning rounded-3 mt-3">
                                    <i class="mdi mdi-timer-sand me-2"></i>
                                    <strong>Menunggu Persetujuan</strong>
                                    <br><small class="text-muted">Data sedang dalam proses approval oleh Foreman/Supervisor.</small>
                                </div>
                            `);
                        } else {
                            wrapper.html('');
                        }
                    }
                });
            }

            function renderTable(response) {
                const {
                    sop,
                    summaries,
                    details
                } = response;

                if (!summaries.length) {
                    $('#empty_state').show();
                    return;
                }

                $('#filter_tanggal').data('sop-id', sop.id);

                // checkApprovalStatus();

                let html = '';

                summaries.forEach((summary, index) => {
                    const barang = summary.barang || {};
                    const mid_barang = barang.mid_barang || '-';
                    const nama_barang = barang.nama_barang || '-';
                    const qty_sistem = summary.qty_sistem ?? 0;
                    const qty_fisik = summary.qty_fisik ?? 0;
                    const selisih = summary.selisih ?? 0;
                    const keterangan = summary.keterangan || '-';
                    const selisihNum = parseFloat(summary.selisih ?? 0);

                    // Tentukan status
                    let statusClass, statusIcon, statusText;
                    switch (summary.status.toLowerCase()) {
                        case 'lebih':
                            statusClass = 'success';
                            statusText = 'Lebih';
                            break;
                        case 'kurang':
                            statusClass = 'danger';
                            statusText = 'Kurang';
                            break;
                        default:
                            statusClass = 'secondary';
                            statusText = 'Sesuai';
                            break;
                    }

                    const statusBadge = `
                        <span class="badge badge-soft-${statusClass} fs-6" title="${statusText}">
                            ${formatNumber(selisih)} 
                        </span>
                    `;

                    const isOperator = sop.is_operator;
                    // console.log('isOperator:', isOperator);
                    const sopStatus = sop.status;
                    const allowEdit = (!isOperator || sopStatus === 'rejected');

                    // tombol edit dibuat tanpa nested backtick
                    const editButton = allowEdit ?
                        '<button class="btn btn-outline-info btn-sm" onclick="showEdit(' + (barang.id ??
                            summary.barang_id) + ')">' +
                        '<i class="mdi mdi-pencil-outline"></i> Edit' +
                        '</button>' :
                        '';

                    html += `
                        <tr class="table-row-hover">
                            <td class="text-center">${index + 1}</td>
                            <td>${formatDate(sop.tgl_opname)}</td>
                            <td>${mid_barang}</td>
                            <td>${nama_barang}</td>
                            <td class="text-end">${formatNumber(qty_sistem)}</td>
                            <td class="text-end">${formatNumber(qty_fisik)}</td>
                            <td class="text-center" data-sort="${selisihNum}">${statusBadge}</td>
                            <td class="text-start text-wrap">${keterangan}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-2 flex-nowrap">
                                    <button class="btn btn-outline-primary btn-sm"  onclick="showDetail(${summary.id})">
                                        <i class="mdi mdi-eye-outline"></i> Detail
                                    </button>
                                    ${editButton}
                                </div>
                            </td>
                        </tr>
                    `;
                });

                $('#tableBody').html(html);

            }

            function renderPalletCounter(response) {
                if (!response || !response.details || response.details.length === 0) {
                    $('#palletCounterBody').html('');
                    $('#sumPalletCount').text('0');
                    $('#sumBoxCount').text('0');
                    $('#tablePalletCounter').closest('.table-responsive').hide();
                    $('#pallet_counter_empty_state').show();
                    return;
                }

                // Filter details for UOM BOX only (case-insensitive)
                const boxDetails = response.details.filter(item => {
                    return item.barang && item.barang.uom && item.barang.uom.trim().toUpperCase() === 'BOX';
                });

                if (boxDetails.length === 0) {
                    $('#palletCounterBody').html('');
                    $('#sumPalletCount').text('0');
                    $('#sumBoxCount').text('0');
                    $('#tablePalletCounter').closest('.table-responsive').hide();
                    $('#pallet_counter_empty_state').show();
                    return;
                }

                $('#pallet_counter_empty_state').hide();
                $('#tablePalletCounter').closest('.table-responsive').show();

                // Group by barang_id
                const grouped = {};
                boxDetails.forEach(item => {
                    const barangId = item.barang_id;
                    const qtyFull = parseFloat(item.qty_full || 0);
                    const qtyReceh = parseFloat(item.qty_receh || 0);
                    const qtyBox = parseFloat(item.barang.qty_box || 0);

                    // Pallet used = sum of qty_full + (1 if qty_receh > 0)
                    const palletCount = qtyFull + (qtyReceh > 0 ? 1 : 0);
                    // Total boxes = (qty_full * qty_box) + qty_receh
                    const totalBoxes = (qtyFull * qtyBox) + qtyReceh;

                    if (!grouped[barangId]) {
                        grouped[barangId] = {
                            mid_barang: item.barang.mid_barang,
                            nama_barang: item.barang.nama_barang,
                            uom: item.barang.uom || '-',
                            total_pallet: 0,
                            total_box: 0
                        };
                    }
                    grouped[barangId].total_pallet += palletCount;
                    grouped[barangId].total_box += totalBoxes;
                });

                let html = '';
                let totalPalletSum = 0;
                let totalBoxSum = 0;
                let index = 1;

                for (const barangId in grouped) {
                    const group = grouped[barangId];
                    totalPalletSum += group.total_pallet;
                    totalBoxSum += group.total_box;

                    const matchedSummary = response.summaries ? response.summaries.find(s => s.barang_id ==
                        barangId) : null;
                    const summaryId = matchedSummary ? matchedSummary.id : null;
                    const detailButton = summaryId ?
                        `<button class="btn btn-outline-primary btn-sm" onclick="showDetail(${summaryId})">
                            <i class="mdi mdi-eye-outline"></i> Detail
                         </button>` : '';

                    html += `
                        <tr>
                            <td class="text-center">${index++}</td>
                            <td><strong>${escapeHtml(group.mid_barang)}</strong></td>
                            <td>${escapeHtml(group.nama_barang)}</td>
                            <td>${escapeHtml(group.uom)}</td>
                            <td class="text-end fw-semibold text-primary">${formatNumber(group.total_pallet)}</td>
                            <td class="text-end fw-semibold text-success">${formatNumber(group.total_box)}</td>
                            <td class="text-center">${detailButton}</td>
                        </tr>
                    `;
                }

                $('#palletCounterBody').html(html);
                $('#sumPalletCount').text(formatNumber(totalPalletSum));
                $('#sumBoxCount').text(formatNumber(totalBoxSum));
            }

            window.showDetail = function(summaryId) {
                $('#modalContent').html(
                    '<p class="text-center py-4"><i class="mdi mdi-loading mdi-spin"></i> Memuat data...</p>'
                );
                const modalEl = document.getElementById('detailModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                const tanggal = $('#filter_tanggal').val();
                const jenisSo = $('#jenis_so').val();
                $.ajax({
                    // url: `{{ url('api/wfg/sop/report/getData') }}`,
                    url: "{{ route('wfg.stock_opname.report.getData') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        tanggal: tanggal,
                        principal: currentPrincipal,
                        jenis_so: jenisSo
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            const summaries = response.summaries || [];
                            const details = response.details || [];
                            const sop = response.sop || {
                                id: '-',
                                tgl_opname: '-'
                            };

                            const matchedSummary = summaries.find(s => s.id == summaryId);

                            if (matchedSummary) {
                                const foundItem = {
                                    id: sop.id,
                                    tgl_opname: sop.tgl_opname,
                                    user: sop.username,
                                    summaries: [matchedSummary],
                                    details: details.filter(d => d.barang_id == matchedSummary
                                        .barang_id)
                                };
                                renderDetailModal(foundItem);
                            } else {
                                $('#modalContent').html(
                                    '<p class="text-center text-danger py-4">Data tidak ditemukan.</p>'
                                );
                            }
                        } else {
                            $('#modalContent').html(
                                `<p class="text-center text-danger py-4">${response.message}</p>`
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#modalContent').html(
                            `<p class="text-center text-danger py-4">Gagal memuat data: ${error}</p>`
                        );
                    }
                });
            };

            function renderDetailModal(data) {
                // console.log(data);
                let html = `
                    <!-- Header Info -->
                    <div class="row mb-4">
                        <h6 class="text-muted mb-2">Informasi Opname</h6>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td width="120">Principal</td>
                                    <td><strong>${data.summaries[0].barang.principal}</strong></td>
                                </tr>
                                <tr>
                                    <td width="120">Tanggal</td>
                                    <td><strong>${formatDate(data.tgl_opname)}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td width="120">User</td> 
                                    <td><strong>${data.user}</strong></td>
                                </tr>
                                <tr>
                                    <td width="120">UOM</td> 
                                    <td><strong>${data.summaries[0].barang.uom}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Summary Table -->
                    <h6 class="mb-3"><i class="mdi mdi-chart-bar text-muted"></i> Ringkasan Selisih</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">Qty Box/Pallet</th>
                                    <th class="text-end">Qty Fisik</th>
                                    <th class="text-end">Qty SAP</th>
                                    <th class="text-end">Selisih</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${renderSummaryRows(data.summaries)}
                            </tbody>
                        </table>
                    </div>

                    <!-- Detail Input Table -->
                    <h6 class="mb-3"><i class="mdi mdi-format-list-bulleted text-muted"></i> Detail Input</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th class="text-end">Full Pallet</th>
                                    <th class="text-end">Receh (Box)</th>
                                    <th class="text-end">Total (Box)</th>
                                    <th class="text-end">Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${renderDetailRows(data.details)}
                            </tbody>
                        </table>
                    </div>
                `;

                $('#modalContent').html(html);
            }

            function renderSummaryRows(summaries) {
                let html = '';
                summaries.forEach(function(item) {
                    const selisih = parseFloat(item.selisih);
                    const status = item.status;
                    let statusClass, statusIcon, statusText;

                    if (status === 'kurang') {
                        statusClass = 'danger';
                        statusIcon = 'arrow-down-bold-circle';
                        statusText = 'Selisih Kurang';
                    } else if (status === 'lebih') {
                        statusClass = 'success';
                        statusIcon = 'arrow-up-bold-circle';
                        statusText = 'Selisih Lebih';
                    } else {
                        statusClass = 'secondary';
                        statusIcon = 'check';
                        statusText = 'Sesuai';
                    }

                    html += `
                        <tr>
                            <td><strong>${item.barang.mid_barang}</strong></td>
                            <td>${item.barang.nama_barang}</td>
                            <td class="text-center">${item.barang.qty_box}</td>
                            <td class="text-end">${formatNumber(item.qty_fisik)}</td>
                            <td class="text-end">${formatNumber(item.qty_sistem)}</td>
                            <td class="text-center"> 
                                <span class="badge badge-soft-${statusClass}">
                                    <strong>${selisih > 0 ? '+' : ''}${formatNumber(item.selisih)}</strong>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-${statusClass}">
                                    <i class="mdi mdi-${statusIcon} me-2"></i> ${statusText}
                                </span>
                            </td>
                        </tr>
                    `;
                });
                return html;
            }

            function renderDetailRows(details) {
                let html = '';
                details.forEach(function(item) {
                    const qtyFull = parseFloat(item.qty_full);
                    const qtyReceh = parseFloat(item.qty_receh);
                    const qtyBox = parseFloat(item.barang.qty_box);
                    const total = (qtyFull * qtyBox) + qtyReceh;

                    const createdAt = item.created_at ? item.created_at.replace(' ', 'T') :
                        new Date();
                    const dateObj = new Date(createdAt);
                    const options = {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    const formattedDate = dateObj.toLocaleString('id-ID', options);

                    html += `
                        <tr>
                            <td><strong>${item.barang.mid_barang}</strong></td>
                            <td>${item.barang.nama_barang}</td>
                            <td class="text-end">${formatNumber(item.qty_full)}</td>
                            <td class="text-end">${formatNumber(item.qty_receh)}</td> 
                            <td class="text-end"><strong>${formatNumber(total)}</strong></td>
                            <td class="text-end"><strong>${formattedDate}</strong></td>
                        </tr>
                    `;
                });
                return html;
            }

            function formatDate(dateString) {
                if (!dateString || dateString === '-') return '-';
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return '-';
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                return date.toLocaleDateString('id-ID', options);
            }

            function formatNumber(value) {
                const num = parseFloat(value);
                if (isNaN(num)) return '0';
                // Bulatkan ke integer terdekat
                const rounded = Math.round(num);
                return rounded.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            $('#btn_export').on('click', async function() {
                const tanggal = $('#filter_tanggal').val();
                const principal = $('#principal_filter').val();

                if (!tanggal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal wajib diisi',
                        text: 'Silakan pilih tanggal untuk export laporan.',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu, sedang menyiapkan data export',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                try {
                    let checkUrl = `{{ route('wfg.stock_opname.export') }}?tanggal=${tanggal}`;
                    if (principal) checkUrl += `&principal=${encodeURIComponent(principal)}`;
                    checkUrl += `&jenis_so=${encodeURIComponent($('#jenis_so').val())}`;

                    // 🔹 Cek dulu apakah backend siap (pakai ?check=true)
                    const checkResponse = await fetch(checkUrl + '&check=true');

                    const data = await checkResponse.json().catch(() => ({}));
                    Swal.close();

                    if (!checkResponse.ok) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengekspor',
                            text: data.message || 'Terjadi kesalahan saat menyiapkan export.',
                        });
                        return;
                    }

                    // 🔹 Kalau semua oke, baru buka tab baru
                    window.open(checkUrl, '_blank');
                    $('#exportDateModal').modal('hide');

                } catch (error) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal',
                        text: 'Tidak dapat terhubung ke server: ' + error.message,
                    });
                }
            });


            // Approval
            $('#btn_approval').on('click', function() {
                const selectedDate = $('#filter_tanggal').val();
                if (!selectedDate) {
                    Swal.fire('Peringatan', 'Silakan pilih tanggal terlebih dahulu.', 'warning');
                    return;
                }

                const modalEl = document.getElementById('approvalModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                $.ajax({
                    url: `{{ url('api/wfg/sop/users/approval') }}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let html = '';

                        // Foreman select
                        html += `
                        <div class="mb-3">
                            <label class="form-label">Foreman</label>
                            <select id="selectForeman" class="form-select">
                                <option value="">-- Pilih Foreman --</option>`;
                        data.foreman.forEach(f => {
                            const label = f.nama_lengkap && f.nama_lengkap.trim() !==
                                "" ?
                                f.nama_lengkap :
                                f.username;

                            html += `
                                <option value="${f.id}">${label}</option>`;
                        });
                        html += `
                            </select>
                        </div>`;

                        // Supervisor select
                        html += `
                        <div class="mb-3">
                            <label class="form-label">Supervisor</label>
                            <select id="selectSupervisor" class="form-select">
                                <option value="">-- Pilih Supervisor --</option>`;
                        data.supervisors.forEach(s => {
                            const label = s.nama_lengkap && s.nama_lengkap.trim() !==
                                "" ?
                                s.nama_lengkap :
                                s.username;

                            html += `
                                <option value="${s.id}">${label}</option>`;
                        });
                        html += `
                            </select>
                        </div>`;

                        $('#approverList').html(html);
                    },
                    error: function() {
                        $('#approverList').html(
                            '<p class="text-center text-danger">Gagal memuat approver.</p>');
                    }
                });
            });

            // Kirim data approval
            $('#btnSendApproval').on('click', function() {
                const sopId = $('#filter_tanggal').data('sop-id');
                const foremanId = $('#selectForeman').val();
                const supervisorId = $('#selectSupervisor').val();
                // const principal = $('#principal_export').val();

                if (!foremanId || !supervisorId) {
                    Swal.fire('Peringatan', 'Silakan pilih Foreman dan Supervisor.', 'warning');
                    return;
                }

                $('#btnSendApproval').prop('disabled', true);

                Swal.fire({
                    title: 'Mengirim...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('wfg.stock_opname.send-approval') }}",
                    method: 'POST',
                    data: {
                        sop_id: sopId,
                        foreman_id: foremanId,
                        supervisor_id: supervisorId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.close();

                        if (res.status === 'success') {
                            Swal.fire('Sukses', res.message, 'success');
                            $('#approvalModal').modal('hide');
                            // location.reload();
                            loadReportData(currentPrincipal, currentSearch);
                            // loadReportData(currentPrincipal);
                        }

                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan',
                            'error');
                    },
                    complete: function() {
                        $('#btnSendApproval').prop('disabled', false);
                    }
                });
            });

            function handleApproval(status) {
                const note = $('#approval_note').val().trim();
                const sopId = $('#filter_tanggal').data('sop-id');

                if (status === 'rejected' && note === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Catatan wajib diisi!',
                        text: 'Mohon tuliskan alasan penolakan sebelum menolak data.',
                    });
                    return;
                }

                Swal.fire({
                    title: status === 'approved' ? 'Setujui data ini?' : 'Tolak data ini?',
                    text: status === 'approved' ?
                        'Data akan disetujui' : 'Data akan ditolak dan dikembalikan ke operator.',
                    icon: status === 'approved' ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonText: status === 'approved' ? 'Ya, Approve' : 'Ya, Reject',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Tunggu sebentar ya..',
                            html: 'Mohon tunggu, sistem sedang memproses data Anda.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ route('wfg.stock_opname.update.status-approval') }}",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                sop_id: sopId,
                                status: status,
                                catatan: note
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: res.message || (status === 'approved' ?
                                        'Data disetujui!' :
                                        'Data ditolak!'),
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#approval_note').val('');

                                // Ganti UI approval jadi label status
                                loadReportData(currentPrincipal);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message ||
                                        'Terjadi kesalahan pada server.',
                                });
                            },
                            complete: function() {
                                $btn.prop('disabled', false).html(originalHtml);
                            }
                        });
                    }
                });
            }

            $(document).on('click', '#btn_approve', function() {
                handleApproval('approved');
            });

            $(document).on('click', '#btn_reject', function() {
                handleApproval('rejected');
            });
            // end approval

            // Save edit
            $('#saveEditBtn').on('click', function() {
                const items = [];
                const tanggal = $('#filter_tanggal').val(); // ambil tanggal dari filter utama

                $('#editModal .temp-item').each(function() {
                    const id = $(this).find('.temp_id').val();
                    const qtyFull = $(this).find('.qty_full').val();
                    const qtyReceh = $(this).find('.qty_receh').val();

                    items.push({
                        id: id,
                        qty_full: qtyFull,
                        qty_receh: qtyReceh,
                        tanggal: tanggal // tambahkan tanggal ke tiap item
                    });
                });

                if (items.length === 0) {
                    Swal.fire('Info', 'Tidak ada data untuk disimpan.', 'info');
                    return;
                }

                const note = $('#editModal .temp_note').val()?.trim() || null;

                $.ajax({
                    url: "{{ route('wfg.stock_opname.edit.update') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        items: items,
                        note: note,
                        tanggal: tanggal // bisa dikirim global juga untuk jaga-jaga
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Berhasil', res.message, 'success');
                            $('#editModal').modal('hide');
                            // bisa panggil ulang renderTable() kalau mau refresh data
                        } else {
                            Swal.fire('Error', res.message || 'Gagal menyimpan data', 'error');
                        }
                        loadReportData(currentPrincipal, currentSearch);
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan.',
                            'error');
                    }
                });
            });

            // Hapus edit
            $(document).on('click', '.btn-delete-edit', function() {
                const tempItem = $(this).closest('.temp-item');
                const tempId = tempItem.data('tempid');

                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: "Data tidak bisa dikembalikan setelah dihapus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wfg.stock_opname.edit.delete', ['id' => 'TEMP_ID']) }}"
                                .replace('TEMP_ID', tempId),
                            type: 'DELETE',
                            success: function(res) {
                                if (res.status === 'success') {
                                    tempItem.remove();
                                    Swal.fire('Berhasil', res.message, 'success');
                                } else {
                                    Swal.fire('Gagal', res.message ||
                                        'Gagal menghapus data', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error',
                                    xhr.responseJSON?.message ||
                                    'Terjadi kesalahan server',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });

        // edit data
        function showEdit(barangId) {
            const tanggal = $('#filter_tanggal').val();
            const jenisSo = $('#jenis_so').val();
            $.ajax({
                url: `{{ url('api/wfg/sop/detail/edit') }}/` + barangId,
                type: 'GET',
                data: {
                    tanggal: tanggal,
                    jenis_so: jenisSo
                },
                success: function(res) {
                    if (res.status === 'success') {
                        const items = res.data;
                        let html = '';
                        let noteText = '';
                        const firstItemWithSummary = items.find(item =>
                            item.sop?.summaries && item.sop.summaries.length > 0
                        );
                        if (firstItemWithSummary) {
                            noteText = firstItemWithSummary.sop.summaries[0].keterangan?.trim() || '';
                        }

                        items.forEach(item => {
                            const createdAt = item.created_at ? item.created_at.replace(' ', 'T') :
                                new Date();
                            const dateObj = new Date(createdAt);
                            const options = {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            };
                            const formattedDate = dateObj.toLocaleString('id-ID', options);

                            const formatQty = (val) => {
                                if (val == null) return 0;
                                return parseFloat(val) % 1 === 0 ? parseInt(val) : parseFloat(val);
                            };

                            html += `
                                <div class="mb-3 border p-2 rounded temp-item" data-tempid="${item.id}">
                                    <input type="hidden" class="temp_id" value="${item.id}">
                                    <p><strong>MID: ${item.barang.mid_barang} - ${formattedDate}</strong></p>
                                    <label>Qty Full</label>
                                    <input type="number" class="form-control qty_full mb-2" value="${formatQty(item.qty_full)}">
                                    <label>Qty Receh</label>
                                    <input type="number" class="form-control qty_receh mb-2" value="${formatQty(item.qty_receh)}">
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-edit mt-1">
                                        <i class="mdi mdi-delete"></i> Hapus
                                    </button>
                                </div>
                            `;

                        });

                        html += `
                            <hr>
                            <div class="mb-3 border border-info p-2 rounded temp-note" data-barangid="${items.barang_id}">
                                <label>Catatan</label>
                                <textarea class="form-control temp_note bg-light" rows="3" placeholder="Belum ada catatan">${noteText}</textarea>
                            </div>
                        `;

                        $('#editModal .modal-body').html(html);
                        $('#editModal').modal('show');
                    } else {
                        Swal.fire('Error', res.message || 'Gagal mengambil data', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire(
                        'Error',
                        xhr.responseJSON?.message || 'Terjadi kesalahan.',
                        'error'
                    );
                }
            });
        }

        @if (session('error'))
            toastr.options = {
                "progressBar": true,
                "positionClass": "toast-top-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "4000",
                "extendedTimeOut": "1000",
                "tapToDismiss": true
            }
            toastr.error("{{ session('error') }}", "Peringatan!");
        @endif
    </script>
@endsection
