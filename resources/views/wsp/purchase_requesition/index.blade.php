@extends('layouts.app')

@section('title', '| Purchase Requesition')

@section('styles')
    <style>
        .signature-canvas {
            width: 100%;
            height: 200px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-3">
                <div class="row d-flex ">
                    <div class="col-md-6">
                        <label class="form-label mb-1 small text-muted">Periode Tanggal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="mdi mdi-calendar-range"></i>
                            </span>
                            <input type="date" id="filterStartDate" class="form-control" title="Tanggal Mulai">
                            <span class="input-group-text bg-light border-start-0 border-end-0">s/d</span>
                            <input type="date" id="filterEndDate" class="form-control" title="Tanggal Akhir">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-3">
                <!-- Card 1: Total Pengajuan -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100 overflow-hidden"
                        style="border-left: 4px solid #3b82f6 !important;">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small fw-semibold text-uppercase tracking-wider">Total
                                        Pengajuan</p>
                                    <h3 class="mb-0 fw-bold text-dark" id="summaryTotalDocs">0</h3>
                                </div>
                                <div class="bg-light-blue p-2.5 rounded-3 d-flex align-items-center justify-content-center"
                                    style="background-color: #dbeafe; width: 44px; height: 44px;">
                                    <i class="mdi mdi-file-document-multiple text-primary fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Menunggu Approval -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100 overflow-hidden"
                        style="border-left: 4px solid #f59e0b !important;">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small fw-semibold text-uppercase tracking-wider">Menunggu
                                        Approval</p>
                                    <h3 class="mb-0 fw-bold text-dark" id="summaryPendingDocs">0</h3>
                                </div>
                                <div class="bg-light-warning p-2.5 rounded-3 d-flex align-items-center justify-content-center"
                                    style="background-color: #fef3c7; width: 44px; height: 44px;">
                                    <i class="mdi mdi-clock-alert text-warning fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Total Item PR -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100 overflow-hidden"
                        style="border-left: 4px solid #10b981 !important;">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small fw-semibold text-uppercase tracking-wider">Item Naik PR
                                    </p>
                                    <h3 class="mb-0 fw-bold text-dark" id="summaryItemPR">0</h3>
                                </div>
                                <div class="bg-light-success p-2.5 rounded-3 d-flex align-items-center justify-content-center"
                                    style="background-color: #d1fae5; width: 44px; height: 44px;">
                                    <i class="mdi mdi-cube-send text-success fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Total Item Reservasi -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 h-100 overflow-hidden"
                        style="border-left: 4px solid #06b6d4 !important;">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small fw-semibold text-uppercase tracking-wider">Item
                                        Reservasi</p>
                                    <h3 class="mb-0 fw-bold text-dark" id="summaryItemReservasi">0</h3>
                                </div>
                                <div class="bg-light-info p-2.5 rounded-3 d-flex align-items-center justify-content-center"
                                    style="background-color: #ecfeff; width: 44px; height: 44px;">
                                    <i class="mdi mdi-bookmark-box-multiple text-info fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body p-auto">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <label class="form-label mb-1 small text-muted">Departemen</label>
                            <select id="filterDepartemen" class="form-select">
                                <option value="all">All Departement</option>
                                @foreach ($departemen as $dept)
                                    <option value="{{ $dept }}">{{ strtoupper(str_replace('_', ' ', $dept)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- <div class="col-md-2">
                            <label class="form-label mb-1 small text-muted">Jenis Item</label>
                            <select id="filterJenisPR" class="form-select">
                                <option value="all">All Item</option>
                                <option value="pr">PR</option>
                                <option value="blocked">Blocked/Reservasi</option>
                            </select>
                        </div> --}}
                        <div class="col-md-3">
                            <label class="form-label mb-1 small text-muted">Status</label>
                            <select id="filterStatusPR" class="form-select">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="finished">Finished</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1 small text-muted">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Cari User / No Doc ...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1 small invisible">Action</label>
                            <button class="btn btn-outline-primary w-100" id="btnRefresh">
                                <i class="mdi mdi-refresh me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="card shadow-sm">
                <div class="card-header bg-light py-3">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h5 class="mb-0">
                                <i class="mdi mdi-table me-2"></i>Data Purchase Requesition
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info py-2 px-3 w-100 mb-3" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ri-information-line"></i>
                            <small class="mb-0">
                                Klik <b>Status Approved</b> untuk melihat tracking approval PR
                            </small>
                        </div>

                        <div class="d-flex align-items-start gap-2">
                            <i class="ri-information-line"></i>
                            <small class="mb-0">
                                Kolom <b>Flag</b> dengan nilai <b>"Yes"</b> menandakan terdapat item yang di-cancel atau
                                jenis nya bukan PR tetapi (Blocked/Reservasi).<br>
                                Silakan cek detail PR untuk informasi lebih lanjut.
                            </small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-borderedless align-middle">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th class="text-center" style="width: 60px;">NO</th>
                                    <th>PR DATE</th>
                                    <th>NO DOC</th>
                                    <th>NO PR</th>
                                    <th>NAMA PEMINTA</th>
                                    <th>DEPARTEMEN</th>
                                    <th>STATUS APPROVED</th>
                                    {{-- <th>FLAG</th> --}}
                                    <th class="text-center text-nowrap">AKSI</th>
                                    @can('permission', 'wsp-data-pr-plus')
                                        <th class="text-center">AKSI WSP</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody id="tableBody">

                                <tr class="empty-state-row">
                                    <td colspan="16" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-2"></i>
                                            <h6 class="fw-bold">Belum Ada Data</h6>
                                            <p class="text-muted mb-0">
                                                Klik tombol <strong>"Form PR"</strong> untuk membuat pengajuan pembelian.
                                            </p>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Menampilkan <strong id="showingFrom">0</strong> -
                            <strong id="showingTo">0</strong> dari
                            <strong id="totalRecords">0</strong> data
                        </div>

                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0" id="pagination">
                                <!-- Auto Generate -->
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Edit Purchase Requesition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="formPREdit">
                    <div class="modal-body">
                        <input type="hidden" id="prId">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">PR Date</label>
                                <input type="date" id="prDate" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Peminta</label>
                                <input type="text" id="namaPeminta" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Departemen</label>
                                <input type="text" id="departemen" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">MID</label>
                                <input type="number" id="mid" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qty</label>
                                <input type="number" id="qty" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan</label>
                                <input type="text" id="keterangan" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i>Simpan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail PR -->
    <div class="modal fade" id="modalDetailPR" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detail Purchase Requisition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- DATA UTAMA -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th>PR Date</th>
                                    <td id="d_pr_date">-</td>
                                </tr>
                                <tr>
                                    <th>Requested By</th>
                                    <td id="d_requested_by">-</td>
                                </tr>
                                <tr>
                                    <th>Department</th>
                                    <td id="d_department">-</td>
                                </tr>
                                <tr>
                                    <th>Jenis</th>
                                    <td id="d_jenis">-</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th>Detail Jenis</th>
                                    <td id="d_detail_jenis">-</td>
                                </tr>
                                <tr>
                                    <th>No Io</th>
                                    <td id="d_no_io">-</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td id="d_status">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- TABLE ITEMS -->
                    <h6 class="mb-2">Detail Items</h6>
                    <div class="table-responsive">
                        <table class="table table-borderedless">
                            <thead class="table-light align-middle">
                                <tr>
                                    <th>No</th>
                                    <th>MID Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Qty</th>
                                    <th>UOM</th>
                                    <th>Keterangan</th>
                                    <th>Jenis</th>
                                    <th>Alasan</th>
                                    <th class="text-center">Status Manager User</th>
                                    <th class="text-center">Status Manager WRH</th>
                                </tr>
                            </thead>
                            <tbody id="detailItems">
                                <tr>
                                    <td colspan="9" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal tracking --}}
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Tracking Approval PR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="approvalTracking"></div>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let allPR = [];
            let currentPage = 1;

            window.loadPRData = function(page = 1) {
                const search = $('#searchInput').val().trim();
                const jenis = $('#filterJenisPR').val();
                const status = $('#filterStatusPR').val();
                const departemen = $('#filterDepartemen').val();
                const startDate = $('#filterStartDate').val();
                const endDate = $('#filterEndDate').val();

                currentPage = page;

                $.ajax({
                    url: "{{ url('api/purchase-requesition/getData') }}",
                    type: "GET",
                    data: {
                        page: page,
                        search: search,
                        jenis: jenis,
                        status: status,
                        departemen: departemen,
                        start_date: startDate,
                        end_date: endDate
                    },
                    dataType: "json",
                    beforeSend: function() {
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="16" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0 text-muted">Memuat data...</p>
                                </td>
                            </tr>
                        `);
                    },
                    success: function(res) {
                        if (res.success) {
                            if (res.summary) {
                                $('#summaryTotalDocs').text(res.summary.total_docs);
                                $('#summaryPendingDocs').text(res.summary.total_pending_docs);
                                $('#summaryItemPR').text(res.summary.total_item_pr);
                                $('#summaryItemReservasi').text(res.summary.total_item_reservasi);
                            }

                            if (res.data && Array.isArray(res.data.data) && res.data.data.length >
                                0) {
                                allPR = res.data.data;
                                renderTable(res.data);
                            } else {
                                allPR = [];
                                $('#tableBody').html(
                                    `<tr class="empty-state-row">
                                        <td colspan="16" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-2"></i>
                                                <h6 class="fw-bold">Belum Ada Data</h6>
                                                <p class="text-muted mb-0">
                                                    Data tidak ditemukan.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                `);
                                updatePaginationInfo(0, 0, 0);
                                $('#pagination').empty();
                            }
                        } else {
                            allPR = [];
                            $('#tableBody').html(
                                `<tr class="empty-state-row">
                                    <td colspan="16" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-2"></i>
                                            <h6 class="fw-bold">Belum Ada Data</h6>
                                            <p class="text-muted mb-0">
                                                Gagal memproses data.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            `);
                            updatePaginationInfo(0, 0, 0);
                            $('#pagination').empty();
                            $('#summaryTotalDocs').text(0);
                            $('#summaryPendingDocs').text(0);
                            $('#summaryItemPR').text(0);
                            $('#summaryItemReservasi').text(0);
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="16" class="text-center text-danger py-3">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> Gagal memuat data dari server.
                                </td>
                            </tr>
                        `);
                        $('#summaryTotalDocs').text(0);
                        $('#summaryPendingDocs').text(0);
                        $('#summaryItemPR').text(0);
                        $('#summaryItemReservasi').text(0);
                    }
                });
            }

            loadPRData();

            // Render table
            function renderTable(paginationData) {
                const tbody = $('#tableBody');
                tbody.empty();

                const badgeStatus = {
                    'pending': 'warning',
                    'approved': 'success',
                    'finished': 'info',
                    'rejected': 'danger',
                };

                const startIndex = (paginationData.current_page - 1) * paginationData.per_page;

                allPR.forEach((pr, index) => {
                    let statusText = pr.status;
                    let badgeClass = badgeStatus[pr.status] ?? 'secondary';

                    if (pr.status === 'rejected') {
                        statusText = 'REJECTED';
                        badgeClass = 'danger';
                    } else {
                        let maxApprovedLevel = 1;
                        if (pr.approval && pr.approval.length > 0) {
                            pr.approval.forEach(a => {
                                if (a.status === 'approved' && a.level > maxApprovedLevel) {
                                    maxApprovedLevel = a.level;
                                }
                            });
                        }
                        statusText = `LEVEL ${maxApprovedLevel}`;

                        if (maxApprovedLevel === 4) {
                            badgeClass = 'success';
                        } else if (maxApprovedLevel === 3) {
                            badgeClass = 'info';
                        } else if (maxApprovedLevel === 2) {
                            badgeClass = 'primary';
                        } else {
                            badgeClass = 'warning';
                        }
                    }

                    const hasCancelledItems = pr.items && pr.items.some(item =>
                        item.status === false || item.status === 0 || item.status === '0'
                    );
                    const hasBlockedItems = pr.items && pr.items.some(item =>
                        item.jenis === 'blocked'
                    );

                    let flagText = '-';
                    if (hasCancelledItems || hasBlockedItems) {
                        let tooltipText = '';
                        if (hasCancelledItems && hasBlockedItems) {
                            tooltipText = 'Terdapat item yang di-cancel & Blocked/Reservasi';
                        } else if (hasCancelledItems) {
                            tooltipText = 'Terdapat item yang di-cancel';
                        } else if (hasBlockedItems) {
                            tooltipText = 'Terdapat item Blocked/Reservasi';
                        }
                        flagText =
                            `<span class="badge badge-soft-danger animate-pulse" title="${tooltipText}" style="cursor: help;">yes</span>`;
                    }

                    tbody.append(`
                        <tr>
                            <td class="text-center">${startIndex + index + 1}</td>
                            <td>${pr.pr_date}</td>
                            <td>${pr.no_doc}</td>
                            <td>${pr.pr_number ?? '-'}</td>
                            <td>${pr.requested_by}</td>
                            <td>${(pr.department ?? '').replace(/_/g, ' ').toUpperCase()}</td>
                            <td>
                                <span 
                                    class="badge badge-soft-${badgeClass}" 
                                    style="cursor:pointer"
                                    onclick="showApprovalTracking(${pr.id})"
                                >
                                    ${statusText}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <div class="d-flex justify-content-center gap-1">

                                    <!-- Detail -->
                                    <button 
                                        class="btn btn-info btn-sm"
                                        onclick="detailPR(${pr.id})"
                                        title="Detail"
                                    >
                                        <i class="mdi mdi-eye"></i>
                                    </button>

                                    <!-- Download -->
                                    <button 
                                        class="btn btn-warning btn-sm"
                                        onclick="printPR(${pr.id})"
                                        title="Download PDF"
                                    >
                                        <i class="mdi mdi-printer"></i>
                                    </button>

                                    @can('permission', 'wsp-data-pr-plus')

                                        <!-- Edit 
                                        <button 
                                            class="btn btn-secondary btn-sm"
                                            onclick="editPR(${pr.id})"
                                            title="Edit"
                                        >
                                            <i class="mdi mdi-pencil"></i>
                                        </button> -->

                                        <!-- Delete -->
                                        <button 
                                            class="btn btn-danger btn-sm"
                                            onclick="deletePR(${pr.id})"
                                            title="Delete"
                                        >
                                            <i class="mdi mdi-delete"></i>
                                        </button>

                                    @endcan

                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center flex-wrap gap-1">
                                    @can('permission', 'wsp-data-pr-plus')

                                        <!-- Copy -->
                                        <button 
                                            class="btn btn-primary btn-sm"
                                            onclick="copyFormatted(${pr.id})"
                                            title="Copy Formatted"
                                        >
                                            <i class="mdi mdi-content-copy"></i> Copy
                                        </button>

                                        <!-- Copy Keterangan PR -->
                                        <button 
                                            class="btn btn-success btn-sm"
                                            onclick="copyKeteranganPR(${pr.id})"
                                            title="Copy Keterangan PR"
                                        >
                                            <i class="mdi mdi-content-copy"></i> Copy Keterangan
                                        </button>

                                    @endcan
                                </div>
                            </td>
                        </tr>
                    `);
                });

                updatePaginationInfo(paginationData.from || 0, paginationData.to || 0, paginationData.total || 0);
                renderPagination(paginationData);
            }

            // Update pagination info
            function updatePaginationInfo(from, to, total) {
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#totalRecords').text(total);
            }

            // Render pagination
            function renderPagination(paginationData) {
                const totalPages = paginationData.last_page;
                const pagination = $('#pagination');
                pagination.empty();

                if (totalPages <= 1) return;

                // Previous button
                pagination.append(`
                    <li class="page-item ${paginationData.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="loadPRData(${paginationData.current_page - 1}); return false;">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>
                    </li>
                `);

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= paginationData.current_page - 1 && i <= paginationData
                            .current_page + 1)) {
                        pagination.append(`
                            <li class="page-item ${i === paginationData.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="loadPRData(${i}); return false;">${i}</a>
                            </li>
                        `);
                    } else if (i === paginationData.current_page - 2 || i === paginationData.current_page + 2) {
                        pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                // Next button
                pagination.append(`
                    <li class="page-item ${paginationData.current_page === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="loadPRData(${paginationData.current_page + 1}); return false;">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </li>
                `);
            }

            // Filter logic (Debounced)
            let searchTimeout;

            function applyFilters() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadPRData(1);
                }, 500); // debounce 500ms
            }

            $('#searchInput').on('input', applyFilters);
            $('#filterJenisPR').on('change', () => loadPRData(1));
            $('#filterStatusPR').on('change', () => loadPRData(1));
            $('#filterDepartemen').on('change', () => loadPRData(1));
            $('#filterStartDate, #filterEndDate').on('change', () => loadPRData(1));

            // Refresh button
            $('#btnRefresh').on('click', function() {
                $('#searchInput').val('');
                $('#filterJenisPR').val('all');
                $('#filterStatusPR').val('all');
                $('#filterDepartemen').val('all');
                $('#filterStartDate').val('');
                $('#filterEndDate').val('');
                loadPRData(1);
            });

            // Delete soh
            window.deletePR = function(id) {
                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/purchase-requesition/delete/" + id,
                            type: 'DELETE',
                            success: function(res) {
                                toastr.success(res.message || 'Data berhasil dihapus');
                                loadPRData(); // reload tabel
                            },
                            error: function(xhr) {
                                let msg = 'Gagal menghapus data';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                toastr.error(msg);
                            }
                        });
                    }
                });
            };

            window.detailPR = function(id) {
                const pr = allPR.find(item => item.id === id);

                if (!pr) {
                    alert('Data PR tidak ditemukan');
                    return;
                }

                $('#d_pr_date').text(pr.pr_date ?? '-');
                $('#d_requested_by').text(pr.requested_by ?? '-');
                $('#d_department').text((pr.department ?? '').replace(/_/g, ' ').toUpperCase());
                $('#d_jenis').text(pr.jenis ?? '-');
                $('#d_detail_jenis').text(pr.detail_jenis ?? '-');
                $('#d_no_io').text(pr.no_io ?? '-');
                $('#d_status').html(
                    `<span class="badge bg-warning text-dark">${pr.status}</span>`
                );


                renderDetailItems(pr.items);

                $('#modalDetailPR').modal('show');
            };

            function renderDetailItems(items) {
                const tbody = $('#detailItems');
                tbody.empty();

                if (!items || items.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                Tidak ada item
                            </td>
                        </tr>
                    `);
                } else {
                    $.each(items, function(i, item) {
                        const badgeClass = item.jenis === 'blocked' ? 'bg-danger' : 'bg-success';
                        const jenisText = item.jenis === 'blocked' ? 'Reservasi' : 'PR';

                        let statusUser = '-';
                        let statusWrh = '-';

                        if (item.approval && item.approval.length > 0) {
                            item.approval.forEach(a => {
                                const level = a.approval?.level;
                                if (level == 2) statusUser = a.status;
                                if (level == 3) statusWrh = a.status;
                            });
                        }

                        const formatBadge = (status) => {
                            if (status === '-') return '-';
                            let bg = 'warning';
                            if (status === 'approved') bg = 'success';
                            if (status === 'rejected') bg = 'danger';
                            return `<span class="badge badge-soft-${bg}">${status}</span>`;
                        };

                        tbody.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${item.barang?.mid_barang ?? '-'}</td>
                                <td>${item.barang?.nama_barang ?? '-'}</td>
                                <td>${item.qty}</td>
                                <td>${item.barang?.uom ?? '-'}</td>
                                <td>
                                    ${item.keterangan ? `
                                                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                                                    <span>${item.keterangan}</span>
                                                                                    <button class="btn btn-sm btn-link p-0 text-secondary border-0 btn-copy-keterangan" 
                                                                                            style="flex-shrink: 0;"
                                                                                            data-text="${escapeHtmlAttribute(item.keterangan)}"
                                                                                            title="Copy Keterangan">
                                                                                        <i class="mdi mdi-content-copy"></i>
                                                                                    </button>
                                                                                </div>
                                                                            ` : '-'}
                                </td>
                                <td><span class="badge ${badgeClass}">${jenisText}</span></td>
                                <td>${item.alasan ?? '-'}</td>
                                <td class="text-center">${formatBadge(statusUser)}</td>
                                <td class="text-center">${formatBadge(statusWrh)}</td>
                            </tr>
                        `);
                    });
                }
            }

            window.escapeHtmlAttribute = function(str) {
                if (!str) return '';
                return str
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            };

            window.copyToClipboard = function(text) {
                if (!text) return;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        showCopySuccessToast();
                    }).catch(err => {
                        console.error('Failed to copy: ', err);
                        fallbackCopyText(text);
                    });
                } else {
                    fallbackCopyText(text);
                }
            };

            function fallbackCopyText(text) {
                const textarea = document.createElement("textarea");
                textarea.value = text;
                textarea.style.position = "absolute";
                textarea.style.left = "-9999px";
                textarea.style.width = "2em";
                textarea.style.height = "2em";
                textarea.style.opacity = "0";

                // Append inside active modal to bypass Bootstrap modal focus trap
                const activeModal = document.querySelector('.modal.show');
                if (activeModal) {
                    activeModal.appendChild(textarea);
                } else {
                    document.body.appendChild(textarea);
                }

                textarea.focus();
                textarea.select();
                try {
                    document.execCommand("copy");
                    showCopySuccessToast();
                } catch (err) {
                    console.error('Fallback copy failed', err);
                }

                if (activeModal) {
                    activeModal.removeChild(textarea);
                } else {
                    document.body.removeChild(textarea);
                }
            }

            function showCopySuccessToast() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Keterangan berhasil disalin',
                    showConfirmButton: false,
                    timer: 1500
                });
            }

            // Delegated handler for copy button
            $(document).on('click', '.btn-copy-keterangan', function(e) {
                e.preventDefault();
                const text = $(this).attr('data-text');
                copyToClipboard(text);
            });

            window.printPR = function(id) {
                window.open(
                    `/api/purchase-requesition/print-riwayat/${id}`,
                    "_blank"
                );
            }

            window.showApprovalTracking = function(id) {

                const pr = allPR.find(p => p.id === id);

                if (!pr || !pr.approval) return;

                const approvals = [...pr.approval].sort((a, b) => a.level - b.level);

                let html = '';

                approvals.forEach(a => {

                    let badge = 'secondary';
                    let icon = 'mdi-clock-outline';
                    let text = 'Pending';

                    if (a.status === 'approved') {
                        badge = 'success';
                        icon = 'mdi-check-circle';
                        text = 'Approved';
                    }

                    if (a.status === 'rejected') {
                        badge = 'danger';
                        icon = 'mdi-close-circle';
                        text = 'Rejected';
                    }

                    html += `
                        <div class="d-flex align-items-start mb-3 border-bottom pb-2">
                            
                            <div class="me-3">
                                <i class="mdi ${icon} fs-3 text-${badge}"></i>
                            </div>

                            <div class="flex-grow-1">
                                <div class="fw-bold text-capitalize">
                                    Level ${a.level} - ${a.role.replace('_',' ')}
                                </div>

                                <span class="badge bg-${badge}">
                                    ${text}
                                </span>

                                <div class="small text-muted mt-1">
                                    ${a.action_at ? a.action_at : 'Belum diproses'}
                                </div>

                                <div class="small text-muted mt-1">
                                    ${a.approver ? a.approver.nama_lengkap : '-'} | ${a.approver && a.approver.departemen ? a.approver.departemen.replace(/_/g, ' ').toUpperCase() : '-'}
                                </div>

                                ${a.catatan ? `
                                                                <div class="small mt-1">
                                                                    Catatan: ${a.catatan}
                                                                </div>
                                                            ` : ''}
                            </div>

                        </div>
                    `;
                });

                $('#approvalTracking').html(html);
                $('#approvalModal').modal('show');
            }

            window.copyFormatted = function(prId) {

                const pr = allPR.find(p => p.id === prId);
                if (!pr) return;

                // hanya boleh copy jika approved
                // if (pr.status !== 'finished') {
                //     Swal.fire({
                //         icon: 'warning',
                //         title: 'Belum Bisa Copy',
                //         text: 'PR harus finished terlebih dahulu',
                //         confirmButtonColor: '#f59e0b'
                //     });
                //     return;
                // }

                const deptMap = {
                    engineering: 'BAS-ENG',
                    warehouse: 'BAS-WRH',
                    ite: 'BAS-ITE',
                    produksi: 'BAS-PRD',
                    quality_control: 'BAS-QC'
                };

                const deptCode = deptMap[pr.department] ?? 'BAS-Dept User';

                let rows = [];

                const approvedItems = pr.items.filter(item =>
                    item.jenis === 'pr'
                );

                // (item.status === true || item.status === 1 || item.status === '1') &&

                if (approvedItems.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Ada Item Naik PR',
                        text: 'Tidak ada item yang naik PR untuk dicopy ke SAP'
                    });
                    return;
                }

                approvedItems.forEach(item => {

                    const mid = item.barang?.mid_barang ?? '';
                    const qty = item.qty ?? '';
                    const sLoc = item.barang?.s_loc ?? '';
                    const plant = item.barang?.plant ?? '';
                    const prNumber = pr.pr_number ?? '';
                    const noIo = pr.no_io ?? '';

                    const row = [
                        mid,
                        '',
                        qty,
                        '',
                        '',
                        '',
                        '',
                        plant,
                        sLoc,
                        '',
                        deptCode,
                        '',
                        noIo,
                    ].join('\t');

                    rows.push(row);
                });

                const textToCopy = rows.join('\n');

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(textToCopy)
                        .then(() => {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Format SAP berhasil disalin',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        })
                        .catch(err => {
                            console.error(err);
                        });
                } else {

                    // fallback lama
                    const textarea = document.createElement("textarea");
                    textarea.value = textToCopy;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand("copy");
                    document.body.removeChild(textarea);

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Format SAP berhasil disalin',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }

            window.copyKeteranganPR = function(prId) {
                const pr = allPR.find(p => p.id === prId);
                if (!pr) return;

                const prItems = pr.items.filter(item =>
                    item.jenis === 'pr' && item.keterangan && item.keterangan.trim() !== ''
                );

                if (prItems.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Ada Keterangan',
                        text: 'Tidak ada item jenis PR yang memiliki keterangan',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                const textToCopy = prItems.map(item => item.keterangan.trim()).join('\n');
                window.copyToClipboard(textToCopy);
            }
        });
    </script>
@endsection
