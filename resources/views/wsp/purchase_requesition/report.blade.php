@extends('layouts.app')

@section('title', '| Report Purchase Requisition')

@section('styles')
    <style>
        :root {
            --pr-primary: #1e3a8a;
            --pr-primary-light: #eff6ff;
            --pr-secondary: #0ea5e9;
            --pr-accent: #3b82f6;
            --pr-success: #10b981;
            --pr-warning: #f59e0b;
            --pr-danger: #ef4444;
            --pr-dark: #1e293b;
        }

        .report-header-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
            color: #ffffff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(30, 58, 138, 0.25);
        }

        .metric-card {
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 0.75rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            position: relative;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.12);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .metric-card.card-blue::before {
            background: linear-gradient(90deg, #2563eb, #60a5fa);
        }

        .metric-card.card-amber::before {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .metric-card.card-emerald::before {
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .metric-card.card-purple::before {
            background: linear-gradient(90deg, #8b5cf6, #c084fc);
        }

        .filter-badge-btn {
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-size: 0.775rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .filter-badge-btn.active {
            background-color: #2563eb;
            color: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
        }

        .sortable-th {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.15s ease;
        }

        .sortable-th:hover {
            background-color: #edf2f7;
            color: #1e293b;
        }

        .sort-icon {
            font-size: 0.85rem;
            opacity: 0.4;
            transition: opacity 0.2s ease;
        }

        .sortable-th.active-sort .sort-icon {
            opacity: 1;
            color: #2563eb;
        }

        .badge-type-pr {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-type-blocked {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-status-approved {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .badge-status-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .badge-status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .cell-mono {
            font-family: monospace;
            font-size: 0.8rem;
        }

        .btn-copy-mini {
            padding: 0.1rem 0.35rem;
            font-size: 0.7rem;
            color: #64748b;
            background: transparent;
            border: none;
            border-radius: 4px;
            transition: all 0.15s ease;
        }

        .btn-copy-mini:hover {
            color: #0284c7;
            background: #f1f5f9;
        }

        .tracking-timeline {
            position: relative;
            padding-left: 2rem;
        }

        .tracking-timeline::before {
            content: '';
            position: absolute;
            top: 0.5rem;
            bottom: 0.5rem;
            left: 0.8rem;
            width: 2px;
            background: #e2e8f0;
        }

        .timeline-step {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .timeline-step:last-child {
            margin-bottom: 0;
        }

        .timeline-node {
            position: absolute;
            left: -2rem;
            top: 0.15rem;
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            z-index: 1;
        }

        .timeline-node.node-approved {
            border-color: #10b981;
            background: #10b981;
            color: #ffffff;
        }

        .timeline-node.node-pending {
            border-color: #f59e0b;
            background: #ffffff;
            color: #f59e0b;
            animation: pulse-ring 2s infinite;
        }

        .timeline-node.node-rejected {
            border-color: #ef4444;
            background: #ef4444;
            color: #ffffff;
        }

        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(245, 158, 11, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Banner Header -->
            <div class="report-header-gradient mb-3">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h3 class="fw-bold text-white mb-1">
                            <i class="mdi mdi-file-chart-outline me-2"></i>Laporan Detail Purchase Requisition (PR)
                        </h3>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div class="d-inline-flex flex-wrap gap-2 justify-content-lg-end">
                            <button type="button" class="btn btn-light shadow-sm fw-semibold" id="btnRefresh">
                                <i class="mdi mdi-refresh me-1"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-success shadow-sm fw-semibold" id="btnExportExcel">
                                <i class="mdi mdi-file-excel me-1"></i> Export Excel (.xlsx)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="row g-3 mb-3">
                <!-- Total Items -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card card-blue h-100 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted text-uppercase fs-11 fw-bold tracking-wider mb-1">Total Item
                                        Pengajuan</p>
                                    <h3 class="fw-bold text-dark mb-0" id="summaryTotalItems">0</h3>
                                    <small class="text-muted fs-12">Total Qty: <strong id="summaryTotalQty"
                                            class="text-primary">0</strong></small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-primary-subtle rounded-3 fs-2">
                                        <i class="mdi mdi-package-variant"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total PR Docs -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card card-purple h-100 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted text-uppercase fs-11 fw-bold tracking-wider mb-1">Total Dokumen PR
                                    </p>
                                    <h3 class="fw-bold text-dark mb-0" id="summaryTotalDocs">0</h3>
                                    <small class="text-muted fs-12">Dokumen PR terdaftar</small>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-purple-subtle rounded-3 fs-2">
                                        <i class="mdi mdi-file-document-multiple"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PR vs Reservasi Breakdown -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card card-amber h-100 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted text-uppercase fs-11 fw-bold tracking-wider mb-1">Komposisi
                                        Pengajuan</p>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge badge-type-pr py-1 px-2" title="Pengajuan Item PR Baru">
                                            <i class="mdi mdi-cart-plus me-1"></i>PR: <strong id="summaryTotalPR">0</strong>
                                        </span>
                                        <span class="badge badge-type-blocked py-1 px-2" title="Pengajuan Item PR Baru">
                                            <i class="mdi mdi-warehouse me-1"></i>Reservasi: <strong
                                                id="summaryTotalReservasi">0</strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-warning-subtle text-warning rounded-3 fs-2">
                                        <i class="mdi mdi-layers-outline"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Approval Summary -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card card-emerald h-100 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted text-uppercase fs-11 fw-bold tracking-wider mb-1">Status Dokumen PR
                                    </p>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2"
                                            title="Dokumen Disetujui Penuh">
                                            <i class="mdi mdi-check-circle me-1"></i>Appr: <strong
                                                id="summaryApprovedDocs">0</strong>
                                        </span>
                                        <span
                                            class="badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2"
                                            title="Dokumen Menunggu Approval">
                                            <i class="mdi mdi-clock-outline me-1"></i>Wait: <strong
                                                id="summaryPendingDocs">0</strong>
                                        </span>
                                        <span
                                            class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2"
                                            title="Dokumen Ditolak">
                                            <i class="mdi mdi-close-circle me-1"></i>Rej: <strong
                                                id="summaryRejectedDocs">0</strong>
                                        </span>
                                    </div>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-success-subtle text-success rounded-3 fs-2">
                                        <i class="mdi mdi-check-decagram-outline"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comprehensive Filter Section -->
            <div class="card shadow-sm border-0 rounded-3 mb-3">
                <div class="card-header py-2 px-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center fs-14">
                        <i class="mdi mdi-filter-variant text-primary fs-5 me-2"></i>Filter & Kriteria Pencarian Data PR
                    </h6>
                </div>

                <div class="card-body p-3 bg-light-subtle">
                    <form id="formFilterReport" onsubmit="return false;">
                        <div class="row g-2">
                            <!-- Baris 1: Filter Parameter Dokumen -->
                            <!-- Periode Tanggal -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                    <i class="mdi mdi-calendar-range me-1"></i>Periode Tanggal PR
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="date" id="filterStartDate" class="form-control" title="Tanggal Mulai">
                                    <span class="input-group-text bg-light text-muted px-2">s/d</span>
                                    <input type="date" id="filterEndDate" class="form-control" title="Tanggal Akhir">
                                </div>
                            </div>

                            @if ($canViewAll)
                                <!-- Departemen (Khusus Admin) -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                        <i class="mdi mdi-office-building me-1"></i>Departemen
                                    </label>
                                    <select id="filterDepartemen" class="form-select form-select-sm"
                                        title="Pilih Departemen">
                                        <option value="all" selected>Semua Departemen</option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept }}">
                                                {{ strtoupper(str_replace('_', ' ', $dept)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" id="filterDepartemen" value="{{ $userDept }}">
                            @endif

                            <!-- Status Dokumen PR -->
                            <div class="{{ $canViewAll ? 'col-lg-3' : 'col-lg-4' }} col-md-6">
                                <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                    <i class="mdi mdi-progress-check me-1"></i>Status PR
                                </label>
                                <select id="filterStatusPR" class="form-select form-select-sm">
                                    <option value="all">Semua Status PR</option>
                                    <option value="pending">Menunggu Approval (Semua)</option>
                                    <option value="pending_lvl_2">Pending Level 2 (Supervisor)</option>
                                    <option value="pending_lvl_3">Pending Level 3 (Manager User)</option>
                                    <option value="pending_lvl_4">Pending Level 4 (Manager WH)</option>
                                    <option value="pending_lvl_5">Pending Level 5 (Admin WSP)</option>
                                    <option value="approved">Disetujui Penuh (Approved / Finished)</option>
                                    <option value="finished">Selesai (Finished)</option>
                                    <option value="rejected">Ditolak (Rejected)</option>
                                </select>
                            </div>

                            <!-- Tipe Pengajuan Item -->
                            <div class="{{ $canViewAll ? 'col-lg-3' : 'col-lg-4' }} col-md-6">
                                <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                    <i class="mdi mdi-tag-outline me-1"></i>Tipe Pengajuan Item
                                </label>
                                <select id="filterTipeItem" class="form-select form-select-sm">
                                    <option value="all">Semua Tipe Pengajuan</option>
                                    <option value="pr">PR</option>
                                    <option value="blocked">Reservasi SOH (Ambil Stok Gudang)</option>
                                </select>
                            </div>

                            <!-- Baris 2: Detail Barang, Kategori & Pencarian Bebas -->
                            <!-- Kategori Dokumen (Barang / Jasa) -->
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                    <i class="mdi mdi-format-list-checks me-1"></i>Kategori PR
                                </label>
                                <select id="filterKategoriPR" class="form-select form-select-sm">
                                    <option value="all">Semua Kategori</option>
                                    <option value="barang">Barang</option>
                                    <option value="Jasa">Jasa</option>
                                </select>
                            </div>

                            <!-- Filter MID Barang -->
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                    <i class="mdi mdi-barcode me-1"></i>MID Barang
                                </label>
                                <input type="text" id="filterMID" class="form-control form-control-sm"
                                    placeholder="Kode MID...">
                            </div>

                            <!-- Filter Nama Barang -->
                            <div class="col-lg-3 col-md-4">
                                <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                    <i class="mdi mdi-cube-outline me-1"></i>Nama Barang / Deskripsi
                                </label>
                                <input type="text" id="filterNamaBarang" class="form-control form-control-sm"
                                    placeholder="Nama atau deskripsi...">
                            </div>

                            <!-- Pencarian Universal -->
                            <div class="col-lg-3 col-md-8">
                                <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                    <i class="mdi mdi-magnify me-1"></i>Pencarian Bebas
                                </label>
                                <input type="text" id="filterSearch" class="form-control form-control-sm"
                                    placeholder="No Doc / PR / IO / Peminta...">
                            </div>

                            <!-- Tombol Reset Filter -->
                            <div class="col-lg-2 col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="btnResetFilter"
                                    title="Reset Semua Filter">
                                    <i class="mdi mdi-refresh me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header py-3 border-bottom">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="mdi mdi-table-large text-primary me-2"></i>Rincian Item Purchase Requisition
                            </h5>
                            <span class="badge bg-light text-muted border py-1 px-2"
                                id="badgeFilteredCount">Memuat...</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="fs-12 text-muted mb-0">Tampilkan:</label>
                            <select id="perPageSelect" class="form-select form-select-sm" style="width: 85px;">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0 w-100" id="tableReportPR">
                            <thead class="bg-light text-uppercase text-nowrap">
                                <tr>
                                    <th class="text-center">NO</th>
                                    <th class="sortable-th text-center" data-sort="pr_date">
                                        TGL PR <i class="mdi mdi-sort sort-icon" id="sortIcon-pr_date"></i>
                                    </th>
                                    <th class="sortable-th" data-sort="no_doc">
                                        NO. DOKUMEN <i class="mdi mdi-sort sort-icon" id="sortIcon-no_doc"></i>
                                    </th>
                                    <th class="sortable-th text-center" data-sort="pr_number">
                                        NO. PR <i class="mdi mdi-sort sort-icon" id="sortIcon-pr_number"></i>
                                    </th>
                                    <th class="sortable-th text-center" data-sort="department">
                                        DEPARTEMEN <i class="mdi mdi-sort sort-icon" id="sortIcon-department"></i>
                                    </th>
                                    <th class="sortable-th" data-sort="requested_by">
                                        PEMINTA <i class="mdi mdi-sort sort-icon" id="sortIcon-requested_by"></i>
                                    </th>
                                    <th class="text-center">MID BARANG</th>
                                    <th>NAMA BARANG / DESKRIPSI</th>
                                    <th class="text-center">TIPE ITEM</th>
                                    <th class="sortable-th text-end" data-sort="qty">
                                        QTY <i class="mdi mdi-sort sort-icon" id="sortIcon-qty"></i>
                                    </th>
                                    <th class="text-center">UOM</th>
                                    <th>ALASAN</th>
                                    <th>KETERANGAN</th>
                                    <th class="text-center">STATUS PR / TAHAP</th>
                                    <th class="text-center text-nowrap">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="tableReportBody">
                                <tr>
                                    <td colspan="15" class="text-center py-5">
                                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                                        </div>
                                        <span class="text-muted fs-13">Memuat data laporan PR...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer & Pagination -->
                <div class="card-footer py-3 border-top">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="text-muted fs-12">
                                Menampilkan <strong id="showingFrom" class="text-dark">0</strong> sampai <strong
                                    id="showingTo" class="text-dark">0</strong> dari <strong id="totalRecords"
                                    class="text-dark">0</strong> total item
                            </span>
                        </div>
                        <div class="col-md-6 mt-2 mt-md-0">
                            <nav aria-label="Page navigation" class="d-flex justify-content-md-end">
                                <ul class="pagination pagination-sm mb-0" id="paginationControls">
                                    <!-- Dynamic Pagination -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Tracking Approval Timeline -->
    <div class="modal fade" id="modalTrackingApproval" tabindex="-1" aria-labelledby="modalTrackingLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light py-3 border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="modalTrackingLabel">
                            <i class="mdi mdi-timeline-clock-outline text-primary me-2 fs-4"></i> Tracking Status Approval
                            PR
                        </h5>
                        <small class="text-muted" id="trackingDocSubtitle">Dokumen: -</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-4 d-flex align-items-center">
                        <i class="ri-information-line fs-18 me-2"></i>
                        <span>Proses persetujuan dokumen Purchase Requisition membutuhkan verifikasi berjenjang dari
                            Requester sampai Admin WSP.</span>
                    </div>

                    <div class="tracking-timeline" id="trackingTimelineContent">
                        <!-- Dynamic Steps -->
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Lengkap Dokumen PR -->
    <div class="modal fade" id="modalDetailPR" tabindex="-1" aria-labelledby="modalDetailPRLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center" id="modalDetailPRLabel">
                        <i class="mdi mdi-file-document-box-multiple-outline me-2 fs-4"></i> Detail Dokumen Purchase
                        Requisition
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Info Header Dokumen -->
                    <div class="card border rounded-3 bg-light-subtle mb-4">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-3 col-6">
                                    <small class="text-muted d-block fs-11 text-uppercase fw-semibold">No. Dokumen</small>
                                    <span class="fw-bold text-dark fs-14 cell-mono" id="detail_no_doc">-</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted d-block fs-11 text-uppercase fw-semibold">No. PR (SAP)</small>
                                    <span class="fw-bold text-dark fs-14 cell-mono" id="detail_pr_number">-</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted d-block fs-11 text-uppercase fw-semibold">Tanggal PR</small>
                                    <span class="fw-semibold text-dark fs-14" id="detail_pr_date">-</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted d-block fs-11 text-uppercase fw-semibold">Departemen</small>
                                    <span
                                        class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-12"
                                        id="detail_department">-</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted d-block fs-11 text-uppercase fw-semibold">Diajukan
                                        Oleh</small>
                                    <span class="fw-semibold text-dark fs-13" id="detail_requested_by">-</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted d-block fs-11 text-uppercase fw-semibold">Kategori /
                                        Detail</small>
                                    <span class="fw-medium text-dark fs-13" id="detail_jenis">-</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted d-block fs-11 text-uppercase fw-semibold">No. IO</small>
                                    <span class="fw-semibold text-dark fs-13 cell-mono" id="detail_no_io">-</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <small class="text-muted d-block fs-11 text-uppercase fw-semibold">Status
                                        Dokumen</small>
                                    <span id="detail_status_badge">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Item -->
                    <h6 class="fw-bold text-dark mb-2 d-flex align-items-center">
                        <i class="mdi mdi-format-list-numbered text-primary me-2"></i>Daftar Item pada Dokumen Ini
                    </h6>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-sm table-hover align-middle mb-0" id="detailTableItems">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th class="text-center" style="width: 110px;">MID</th>
                                    <th>Nama Barang / Deskripsi</th>
                                    <th class="text-center" style="width: 110px;">Tipe</th>
                                    <th class="text-end" style="width: 80px;">Qty</th>
                                    <th class="text-center" style="width: 70px;">Satuan</th>
                                    <th>Alasan</th>
                                    <th>Keterangan</th>
                                    <th class="text-center" style="width: 100px;">Status Item</th>
                                </tr>
                            </thead>
                            <tbody id="detailItemsBody">
                                <!-- Dynamic items -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-warning btn-sm" id="btnDetailPrintPdf">
                        <i class="mdi mdi-printer me-1"></i> Cetak PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Follow Up WhatsApp Approver -->
    <div class="modal fade" id="modalFollowUpWa" tabindex="-1" aria-labelledby="modalFollowUpWaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center" id="modalFollowUpWaLabel">
                        <i class="bx bxl-whatsapp fs-20 me-2"></i> Follow Up WhatsApp Approver
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <input type="hidden" id="wa_pr_id">
                    <input type="hidden" id="wa_approval_id">

                    <div class="alert border mb-3 py-2 px-3 bg-light">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">No. Dokumen:</span>
                            <span class="fw-bold text-dark cell-mono" id="wa_no_doc">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Pengaju:</span>
                            <span class="fw-semibold text-dark" id="wa_requested_by">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Departemen:</span>
                            <span class="fw-semibold text-dark" id="wa_department">-</span>
                        </div>
                        <hr class="my-2 border-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Approver Tujuan:</span>
                            <span class="fw-bold text-primary" id="wa_approver_name">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Tahap Menunggu:</span>
                            <span class="badge bg-warning text-dark" id="wa_approver_role">-</span>
                        </div>
                    </div>

                    <div id="wa_rate_limit_alert_container"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted mb-1 small">
                            <i class="bx bxl-whatsapp text-success me-1"></i> Nomor WhatsApp Approver:
                        </label>
                        <div class="p-2 border rounded bg-white" id="wa_target_phone_container">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-phone text-muted me-2 fs-16"></i>
                                    <span class="fw-bold font-monospace text-dark" id="wa_target_phone_display">-</span>
                                </div>
                                <span class="badge" id="wa_target_phone_badge">Terdaftar</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted mb-1 small">Preview Pesan Sistem:</label>
                        <div class="p-2 border rounded bg-light text-muted small font-monospace" id="wa_preview_box"
                            style="white-space: pre-wrap; max-height: 140px; overflow-y: auto;">
                            <!-- Preview message -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-sm fw-semibold" id="btnSendWa">
                        <i class="bx bxl-whatsapp me-1"></i> Kirim WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const canViewAll = {{ $canViewAll ? 'true' : 'false' }};
            const userDept = "{{ $userDept }}";

            let currentPage = 1;
            let currentSortBy = 'pr_date';
            let currentSortDir = 'desc';
            let reportDataCache = [];
            let activePRForModal = null;
            let currentFollowUpData = null;

            // Inisialisasi Periode Default: Bulan Ini
            applyDatePreset('month');

            // Quick Preset Buttons Click
            $('.filter-badge-btn').on('click', function() {
                $('.filter-badge-btn').removeClass('active');
                $(this).addClass('active');
                const preset = $(this).data('preset');
                applyDatePreset(preset);
                loadReportData(1);
            });

            function applyDatePreset(preset) {
                const now = new Date();
                const formatDate = (d) => {
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${y}-${m}-${day}`;
                };

                let start = '';
                let end = '';

                if (preset === 'today') {
                    start = formatDate(now);
                    end = formatDate(now);
                } else if (preset === '7days') {
                    const past = new Date();
                    past.setDate(past.getDate() - 6);
                    start = formatDate(past);
                    end = formatDate(now);
                } else if (preset === 'month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    start = formatDate(firstDay);
                    end = formatDate(lastDay);
                } else if (preset === 'last_month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);
                    start = formatDate(firstDay);
                    end = formatDate(lastDay);
                } else if (preset === 'year') {
                    const firstDay = new Date(now.getFullYear(), 0, 1);
                    const lastDay = new Date(now.getFullYear(), 11, 31);
                    start = formatDate(firstDay);
                    end = formatDate(lastDay);
                } else if (preset === 'all') {
                    start = '';
                    end = '';
                }

                $('#filterStartDate').val(start);
                $('#filterEndDate').val(end);
            }

            // Load Report Data Function
            window.loadReportData = function(page = 1) {
                currentPage = page;

                const params = {
                    page: page,
                    per_page: $('#perPageSelect').val(),
                    start_date: $('#filterStartDate').val(),
                    end_date: $('#filterEndDate').val(),
                    departemen: canViewAll ? ($('#filterDepartemen').val() || 'all') : userDept,
                    status_pr: $('#filterStatusPR').val(),
                    tipe_item: $('#filterTipeItem').val(),
                    kategori_pr: $('#filterKategoriPR').val(),
                    mid: $('#filterMID').val().trim(),
                    nama_barang: $('#filterNamaBarang').val().trim(),
                    search: $('#filterSearch').val().trim(),
                    sort_by: currentSortBy,
                    sort_dir: currentSortDir,
                };

                $('#tableReportBody').html(`
                    <tr>
                        <td colspan="15" class="text-center py-5">
                            <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                            <span class="text-muted fs-13">Memuat data laporan...</span>
                        </td>
                    </tr>
                `);

                $.ajax({
                    url: "{{ route('stock.pr.report.data') }}",
                    type: "GET",
                    data: params,
                    dataType: "json",
                    success: function(res) {
                        if (res.success && res.data) {
                            reportDataCache = res.data.data || [];
                            updateSummaryCards(res.summary);
                            renderTable(res.data);
                            renderPagination(res.data);
                        } else {
                            renderEmptyTable('Gagal memuat data laporan');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching report data:', xhr);
                        renderEmptyTable('Terjadi kesalahan saat memuat data dari server');
                    }
                });
            };

            // Panggil data pertama kali saat halaman dimuat
            loadReportData(1);

            // Update Summary KPI Cards
            function updateSummaryCards(summary) {
                if (!summary) return;
                $('#summaryTotalItems').text(Number(summary.total_items || 0).toLocaleString('id-ID'));
                $('#summaryTotalQty').text(Number(summary.total_qty || 0).toLocaleString('id-ID'));
                $('#summaryTotalDocs').text(Number(summary.total_docs || 0).toLocaleString('id-ID'));
                $('#summaryTotalPR').text(Number(summary.total_pr_type || 0).toLocaleString('id-ID'));
                $('#summaryTotalReservasi').text(Number(summary.total_reservation_type || 0).toLocaleString(
                    'id-ID'));
                $('#summaryApprovedDocs').text(Number(summary.total_approved_docs || 0).toLocaleString('id-ID'));
                $('#summaryPendingDocs').text(Number(summary.total_pending_docs || 0).toLocaleString('id-ID'));
                $('#summaryRejectedDocs').text(Number(summary.total_rejected_docs || 0).toLocaleString('id-ID'));
            }

            // Render Table Rows
            function renderTable(pagination) {
                const tbody = $('#tableReportBody');
                tbody.empty();

                const items = pagination.data || [];
                const total = pagination.total || 0;
                $('#badgeFilteredCount').text(`${Number(total).toLocaleString('id-ID')} item ditemukan`);

                if (items.length === 0) {
                    renderEmptyTable('Tidak ada data item PR yang sesuai dengan kriteria filter.');
                    $('#showingFrom').text(0);
                    $('#showingTo').text(0);
                    $('#totalRecords').text(0);
                    return;
                }

                $('#showingFrom').text(pagination.from || 0);
                $('#showingTo').text(pagination.to || 0);
                $('#totalRecords').text(total);

                const startIndex = (pagination.current_page - 1) * pagination.per_page;

                items.forEach((item, idx) => {
                    const rowNo = startIndex + idx + 1;

                    // Tipe Item Badge
                    const isBlocked = item.tipe_item_raw === 'blocked';
                    const tipeBadge = isBlocked ?
                        `<span class="badge badge-type-blocked py-1 px-2"><i class="mdi mdi-warehouse me-1"></i>Reservasi SOH</span>` :
                        `<span class="badge badge-type-pr py-1 px-2"><i class="mdi mdi-cart-plus me-1"></i>PR</span>`;

                    // Status PR Badge
                    let prStatusBadge = '';
                    if (item.pr_status === 'rejected') {
                        prStatusBadge =
                            `<span class="badge badge-status-rejected py-1 px-2" style="cursor:pointer;" onclick="openTrackingModal(${item.id})" title="Klik untuk lihat tracking approval"><i class="mdi mdi-close-circle me-1"></i>Rejected</span>`;
                    } else if (item.pr_status === 'finished' || item.current_stage === 'Finished') {
                        prStatusBadge =
                            `<span class="badge badge-status-approved py-1 px-2" style="cursor:pointer;" onclick="openTrackingModal(${item.id})" title="Klik untuk lihat tracking approval"><i class="mdi mdi-check-circle me-1"></i>Finished</span>`;
                    } else if (item.pr_status === 'approved' && !item.pending_approval_id) {
                        prStatusBadge =
                            `<span class="badge badge-status-approved py-1 px-2" style="cursor:pointer;" onclick="openTrackingModal(${item.id})" title="Klik untuk lihat tracking approval"><i class="mdi mdi-check-circle me-1"></i>Approved Penuh</span>`;
                    } else {
                        prStatusBadge =
                            `<span class="badge badge-status-pending py-1 px-2" style="cursor:pointer;" onclick="openTrackingModal(${item.id})" title="Klik untuk lihat tracking approval"><i class="mdi mdi-clock-outline me-1"></i>${escapeHtml(item.current_stage)}</span>`;
                    }

                    // Keterangan & Copy
                    const midHtml = item.mid_barang && item.mid_barang !== '-' ?
                        `<div class="d-inline-flex align-items-center gap-1">
                               <span class="cell-mono fw-bold text-dark">${escapeHtml(item.mid_barang)}</span>
                               <button type="button" class="btn-copy-mini" onclick="copyText('${escapeHtml(item.mid_barang)}')" title="Salin MID"><i class="mdi mdi-content-copy"></i></button>
                           </div>` :
                        `<span class="text-muted">-</span>`;

                    const namaHtml = `
                        <div class="d-flex align-items-start justify-content-between gap-1">
                            <span class="fw-medium text-dark">${escapeHtml(item.nama_barang)}</span>
                            <button type="button" class="btn-copy-mini flex-shrink-0" onclick="copyText('${escapeHtml(item.nama_barang)}')" title="Salin Nama"><i class="mdi mdi-content-copy"></i></button>
                        </div>
                    `;

                    const ketHtml = item.keterangan && item.keterangan !== '-' ?
                        `<div class="d-flex align-items-start justify-content-between gap-1">
                               <span class="small">${escapeHtml(item.keterangan)}</span>
                               <button type="button" class="btn-copy-mini flex-shrink-0" onclick="copyText('${escapeHtml(item.keterangan)}')" title="Salin Keterangan"><i class="mdi mdi-content-copy"></i></button>
                           </div>` :
                        `<span class="text-muted">-</span>`;

                    // Action Buttons
                    let actionHtml = `
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-info py-1 px-2" onclick="openTrackingModal(${item.id})" title="Tracking Approval Timeline">
                                <i class="mdi mdi-timeline-clock-outline"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary py-1 px-2" onclick="openDetailPRModal(${item.id})" title="Lihat Detail Dokumen PR">
                                <i class="mdi mdi-eye-outline"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning py-1 px-2" onclick="printPRPdf(${item.pr_id})" title="Cetak PDF PR">
                                <i class="mdi mdi-printer"></i>
                            </button>
                    `;

                    @can('permission', 'wsp-pr-follow-wa')
                        if (item.pr_status === 'pending' && item.pending_approval_id) {
                            const waSentBtnClass = item.wa_sent_today ? 'btn-outline-success' :
                                'btn-success';
                            const waTitle = item.wa_sent_today ? 'Sudah Difollow-up Hari Ini' :
                                'Follow Up WhatsApp Approver';
                            actionHtml += `
                            <button type="button" class="btn btn-sm ${waSentBtnClass} py-1 px-2" onclick="openFollowUpModal(${item.id})" title="${waTitle}">
                                <i class="bx ${item.wa_sent_today ? 'bx-check-double' : 'bxl-whatsapp'}"></i>
                            </button>
                        `;
                        }
                    @endcan

                    actionHtml += `</div>`;

                    tbody.append(`
                        <tr>
                            <td class="text-center text-muted fs-12">${rowNo}</td>
                            <td class="text-center font-monospace">${escapeHtml(item.pr_date)}</td>
                            <td>
                                <span class="fw-bold cell-mono">${escapeHtml(item.no_doc)}</span>
                            </td>
                            <td class="text-center cell-mono">${escapeHtml(item.pr_number)}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border px-2 py-1">${escapeHtml(item.department)}</span></td>
                            <td class="fw-medium">${escapeHtml(item.requested_by)}</td>
                            <td class="text-center">${midHtml}</td>
                            <td>${namaHtml}</td>
                            <td class="text-center">${tipeBadge}</td>
                            <td class="text-end fw-bold text-dark font-monospace">${Number(item.qty).toLocaleString('id-ID')}</td>
                            <td class="text-center text-muted small">${escapeHtml(item.uom)}</td>
                            <td class="text-muted small">${escapeHtml(item.alasan)}</td>
                            <td>${ketHtml}</td>
                            <td class="text-center">${prStatusBadge}</td>
                            <td class="text-center text-nowrap" style="width: 130px; min-width: 130px;">${actionHtml}</td>
                        </tr>
                    `);
                });
            }

            function renderEmptyTable(message) {
                $('#tableReportBody').html(`
                    <tr>
                        <td colspan="15" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="mdi mdi-file-search-outline fs-1 text-muted mb-2"></i>
                                <h6 class="fw-bold text-dark mb-1">Data Tidak Ditemukan</h6>
                                <p class="text-muted mb-0 fs-13">${escapeHtml(message)}</p>
                            </div>
                        </td>
                    </tr>
                `);
            }

            // Render Pagination Links
            function renderPagination(pagination) {
                const totalPages = pagination.last_page || 1;
                const container = $('#paginationControls');
                container.empty();

                if (totalPages <= 1) return;

                // Prev Button
                container.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="loadReportData(${currentPage - 1});" aria-label="Sebelumnya">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>
                    </li>
                `);

                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                        container.append(`
                            <li class="page-item ${i === currentPage ? 'active' : ''}">
                                <a class="page-link" href="javascript:void(0);" onclick="loadReportData(${i});">${i}</a>
                            </li>
                        `);
                    } else if (i === currentPage - 3 || i === currentPage + 3) {
                        container.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                // Next Button
                container.append(`
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="loadReportData(${currentPage + 1});" aria-label="Berikutnya">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </li>
                `);
            }

            // Sorting Headers Click
            $('.sortable-th').on('click', function() {
                const sortCol = $(this).data('sort');
                if (currentSortBy === sortCol) {
                    currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSortBy = sortCol;
                    currentSortDir = 'desc';
                }

                // Update sort icons
                $('.sortable-th').removeClass('active-sort');
                $('[id^="sortIcon-"]').removeClass('mdi-sort-ascending mdi-sort-descending').addClass(
                    'mdi-sort');

                $(this).addClass('active-sort');
                const icon = $(`#sortIcon-${sortCol}`);
                if (currentSortDir === 'asc') {
                    icon.removeClass('mdi-sort').addClass('mdi-sort-ascending');
                } else {
                    icon.removeClass('mdi-sort').addClass('mdi-sort-descending');
                }

                loadReportData(1);
            });

            // Auto-filter on change untuk filter dropdown dan tanggal
            $('#filterStartDate, #filterEndDate, #filterDepartemen, #filterStatusPR, #filterTipeItem, #filterKategoriPR')
                .on('change', function() {
                    loadReportData(1);
                });

            $('#btnResetFilter').on('click', function() {
                $('#formFilterReport')[0].reset();
                $('.filter-badge-btn').removeClass('active');
                $('[data-preset="month"]').addClass('active');
                applyDatePreset('month');
                if (canViewAll) {
                    $('#filterDepartemen').val('all');
                } else {
                    $('#filterDepartemen').val(userDept);
                }
                $('#filterStatusPR').val('all');
                $('#filterTipeItem').val('all');
                $('#filterKategoriPR').val('all');
                currentSortBy = 'pr_date';
                currentSortDir = 'desc';
                loadReportData(1);
            });

            $('#btnRefresh').on('click', function() {
                loadReportData(currentPage);
            });

            $('#perPageSelect').on('change', function() {
                loadReportData(1);
            });

            // Instant search debounce on search input
            let searchTimeout = null;
            $('#filterSearch, #filterMID, #filterNamaBarang').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    loadReportData(1);
                }, 400);
            });

            // Export Excel Handler
            $('#btnExportExcel').on('click', function() {
                const params = new URLSearchParams({
                    start_date: $('#filterStartDate').val(),
                    end_date: $('#filterEndDate').val(),
                    departemen: canViewAll ? ($('#filterDepartemen').val() || 'all') : userDept,
                    status_pr: $('#filterStatusPR').val(),
                    tipe_item: $('#filterTipeItem').val(),
                    kategori_pr: $('#filterKategoriPR').val(),
                    mid: $('#filterMID').val().trim(),
                    nama_barang: $('#filterNamaBarang').val().trim(),
                    search: $('#filterSearch').val().trim(),
                    sort_by: currentSortBy,
                    sort_dir: currentSortDir,
                });

                Swal.fire({
                    title: 'Export Excel Sedang Diproses',
                    text: 'Laporan detail PR Anda sedang digenerate. File akan otomatis terunduh...',
                    icon: 'info',
                    timer: 2500,
                    showConfirmButton: false
                });

                window.location.href = `{{ route('stock.pr.report.export') }}?${params.toString()}`;
            });

            // Modal Tracking Approval
            window.openTrackingModal = function(itemId) {
                const item = reportDataCache.find(x => x.id === itemId);
                if (!item) return;

                $('#trackingDocSubtitle').html(
                    `No. Dokumen: <strong class="text-dark">${escapeHtml(item.no_doc)}</strong> | Peminta: <strong>${escapeHtml(item.requested_by)}</strong> (${escapeHtml(item.department)})`
                );

                const timeline = $('#trackingTimelineContent');
                timeline.empty();

                const approvals = item.approvals || [];
                if (approvals.length === 0) {
                    timeline.html('<p class="text-muted">Tidak ada riwayat approval pada dokumen ini.</p>');
                } else {
                    approvals.forEach((app) => {
                        let nodeClass = 'node-pending';
                        let nodeIcon = '<i class="mdi mdi-clock-outline"></i>';
                        let statusBadge =
                            '<span class="badge bg-warning-subtle text-warning border border-warning-subtle py-1 px-2">Menunggu Approval</span>';

                        if (app.status === 'approved') {
                            nodeClass = 'node-approved';
                            nodeIcon = '<i class="mdi mdi-check"></i>';
                            statusBadge =
                                '<span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">Disetujui</span>';
                        } else if (app.status === 'rejected') {
                            nodeClass = 'node-rejected';
                            nodeIcon = '<i class="mdi mdi-close"></i>';
                            statusBadge =
                                '<span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2">Ditolak</span>';
                        }

                        let catatanHtml = '';
                        if (app.catatan) {
                            catatanHtml =
                                `<div class="bg-light p-2 rounded small text-dark mt-2 border"><strong>Catatan:</strong> ${escapeHtml(app.catatan)}</div>`;
                        }

                        let waInfoHtml = '';
                        @can('permission', 'wsp-pr-follow-wa')
                            if (app.last_wa_follow_up_at) {
                                waInfoHtml =
                                    `<div class="small text-muted mt-1"><i class="bx bxl-whatsapp text-success me-1"></i> Follow up WA terakhir: <span class="fw-semibold text-dark">${escapeHtml(app.last_wa_follow_up_at)}</span></div>`;
                            }
                        @endcan

                        let btnFollowUp = '';
                        @can('permission', 'wsp-pr-follow-wa')
                            if (app.status === 'pending') {
                                btnFollowUp = `
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-success py-1 px-2 fs-12" onclick="$('#modalTrackingApproval').modal('hide'); setTimeout(() => openFollowUpModal(${item.id}), 350);">
                                        <i class="bx bxl-whatsapp me-1"></i> Follow Up WhatsApp
                                    </button>
                                </div>
                            `;
                            }
                        @endcan

                        timeline.append(`
                            <div class="timeline-step">
                                <div class="timeline-node ${nodeClass}">${nodeIcon}</div>
                                <div class="card border mb-0 shadow-none">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <h6 class="fw-bold text-dark mb-0">Level ${app.level} - ${escapeHtml(app.role)}</h6>
                                            ${statusBadge}
                                        </div>
                                        <p class="mb-1 fs-13 text-muted">Approver: <strong class="text-dark">${escapeHtml(app.approver_name)}</strong></p>
                                        <div class="small text-muted"><i class="mdi mdi-clock-time-four-outline me-1"></i> Waktu: ${app.action_at ? escapeHtml(app.action_at) : '<span class="fst-italic text-warning">Belum diproses</span>'}</div>
                                        ${catatanHtml}
                                        ${waInfoHtml}
                                        ${btnFollowUp}
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                }

                $('#modalTrackingApproval').modal('show');
            };

            // Modal Detail PR
            window.openDetailPRModal = function(itemId) {
                const item = reportDataCache.find(x => x.id === itemId);
                if (!item) return;

                activePRForModal = item;

                $('#detail_no_doc').text(item.no_doc);
                $('#detail_pr_number').text(item.pr_number);
                $('#detail_pr_date').text(item.pr_date);
                $('#detail_department').text(item.department);
                $('#detail_requested_by').text(item.requested_by);
                $('#detail_jenis').text(`${item.kategori_pr} (${item.detail_jenis})`);
                $('#detail_no_io').text(item.no_io);

                let badge = '<span class="badge bg-warning text-dark">Pending</span>';
                if (item.pr_status === 'finished' || item.current_stage === 'Finished') {
                    badge = '<span class="badge bg-success">Finished</span>';
                } else if (item.pr_status === 'approved') {
                    badge = '<span class="badge bg-success">Approved</span>';
                } else if (item.pr_status === 'rejected') {
                    badge = '<span class="badge bg-danger">Rejected</span>';
                }
                $('#detail_status_badge').html(badge);

                // Fetch detail lengkap PR via API
                $('#detailItemsBody').html(`
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                            Memuat daftar item dokumen...
                        </td>
                    </tr>
                `);

                $.ajax({
                    url: `{{ url('/purchase-requesition/show') }}/${item.pr_id}`,
                    type: "GET",
                    dataType: "json",
                    success: function(res) {
                        const tbody = $('#detailItemsBody');
                        tbody.empty();

                        const prData = res.data;
                        if (prData && prData.items && prData.items.length > 0) {
                            prData.items.forEach((it, i) => {
                                const mid = it.barang?.mid_barang || '-';
                                const nama = it.barang?.nama_barang || it.desc || '-';
                                const uom = it.barang?.uom || 'PCS';
                                const tipe = it.jenis === 'blocked' ?
                                    '<span class="badge badge-type-blocked py-1 px-2">Reservasi SOH</span>' :
                                    '<span class="badge badge-type-pr py-1 px-2">PR</span>';
                                const alasan = it.alasan || '-';
                                const ket = it.keterangan || '-';

                                let statusItem =
                                    '<span class="badge bg-secondary-subtle text-secondary">Pending</span>';
                                if (it.status === true || it.status === 1) statusItem =
                                    '<span class="badge badge-status-approved">Approved</span>';
                                if (it.status === false || it.status === 0) statusItem =
                                    '<span class="badge badge-status-rejected">Rejected</span>';

                                tbody.append(`
                                    <tr>
                                        <td class="text-center text-muted">${i + 1}</td>
                                        <td class="text-center cell-mono fw-bold">${escapeHtml(mid)}</td>
                                        <td>${escapeHtml(nama)}</td>
                                        <td class="text-center">${tipe}</td>
                                        <td class="text-end fw-bold font-monospace">${Number(it.qty).toLocaleString('id-ID')}</td>
                                        <td class="text-center text-muted small">${escapeHtml(uom)}</td>
                                        <td class="small text-muted">${escapeHtml(alasan)}</td>
                                        <td class="small text-muted">${escapeHtml(ket)}</td>
                                        <td class="text-center">${statusItem}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.html(
                                '<tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data item.</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        $('#detailItemsBody').html(
                            '<tr><td colspan="9" class="text-center text-danger py-3">Gagal memuat detail item dokumen.</td></tr>'
                        );
                    }
                });

                $('#modalDetailPR').modal('show');
            };

            $('#btnDetailPrintPdf').on('click', function() {
                if (activePRForModal && activePRForModal.pr_id) {
                    printPRPdf(activePRForModal.pr_id);
                }
            });

            window.printPRPdf = function(prId) {
                window.open(`{{ url('/api/purchase-requesition/print-riwayat') }}/${prId}`, '_blank');
            };

            // Follow Up WhatsApp Modal Logic
            window.openFollowUpModal = function(itemId) {
                const item = reportDataCache.find(x => x.id === itemId);
                if (!item) return;

                currentFollowUpData = item;

                $('#wa_pr_id').val(item.pr_id);
                $('#wa_approval_id').val(item.pending_approval_id);
                $('#wa_no_doc').text(item.no_doc || '-');
                $('#wa_requested_by').text(item.requested_by || '-');
                $('#wa_department').text(item.department || '-');
                $('#wa_approver_name').text(item.pending_approver_name || 'Approver');
                $('#wa_approver_role').text(item.pending_role || 'Tahap Pending');

                // Dummy / phone status
                $('#wa_target_phone_display').text('Menyesuaikan Akun Approver');
                $('#wa_target_phone_badge').addClass('bg-success-subtle text-success').text('Otomatis Sistem');

                const msg =
                    `Halo Bapak/Ibu ${item.pending_approver_name || ''},\nMohon bantuan persetujuan untuk Purchase Requisition:\n• No Doc: ${item.no_doc}\n• Peminta: ${item.requested_by} (${item.department})\n• Tahap: ${item.pending_role}\n\nTerima kasih.`;
                $('#wa_preview_box').text(msg);

                $('#wa_rate_limit_alert_container').empty();
                if (item.wa_sent_today) {
                    $('#wa_rate_limit_alert_container').html(`
                        <div class="alert alert-warning py-2 px-3 small d-flex align-items-center mb-3">
                            <i class="bx bx-time-five fs-18 me-2 flex-shrink-0 text-warning"></i>
                            <div>Pengingat WhatsApp untuk tahap ini sudah pernah dikirimkan hari ini (${item.last_wa_date}).</div>
                        </div>
                    `);
                    $('#btnSendWa').prop('disabled', true).addClass('disabled').html(
                        '<i class="bx bx-check-double me-1"></i> Sudah Dikirim Hari Ini');
                } else {
                    $('#btnSendWa').prop('disabled', false).removeClass('disabled').html(
                        '<i class="bx bxl-whatsapp me-1"></i> Kirim WhatsApp');
                }

                $('#modalFollowUpWa').modal('show');
            };

            $('#btnSendWa').on('click', function() {
                const prId = $('#wa_pr_id').val();
                const approvalId = $('#wa_approval_id').val();

                Swal.fire({
                    title: 'Kirim Pengingat WhatsApp?',
                    text: 'Pesan notifikasi persetujuan PR akan dikirimkan langsung ke nomor approver via WhatsApp.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="bx bxl-whatsapp me-1"></i> Ya, Kirim Sekarang',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const btn = $('#btnSendWa');
                        const originalHtml = btn.html();
                        btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...'
                        );

                        $.ajax({
                            url: "{{ url('purchase-requesition/follow-up-wa') }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                pr_id: prId,
                                approval_id: approvalId
                            },
                            dataType: 'json',
                            success: function(res) {
                                btn.prop('disabled', false).html(originalHtml);
                                if (res.success) {
                                    $('#modalFollowUpWa').modal('hide');
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil Terkirim!',
                                        text: res.message ||
                                            'Pesan WhatsApp berhasil dikirim ke approver.',
                                        timer: 3000,
                                        showConfirmButton: true
                                    });
                                    loadReportData(currentPage);
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal Mengirim',
                                        text: res.message ||
                                            'Terjadi kesalahan saat pengiriman pesan WhatsApp.'
                                    });
                                }
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html(originalHtml);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: xhr.responseJSON?.message ||
                                        'Gagal menghubungi server untuk pengiriman WhatsApp.'
                                });
                            }
                        });
                    }
                });
            });

            // Global Copy to Clipboard Function with Toast
            window.copyText = function(text) {
                if (!text || text === '-') return;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        showCopyToast();
                    }).catch(function() {
                        fallbackCopy(text);
                    });
                } else {
                    fallbackCopy(text);
                }
            };

            function fallbackCopy(text) {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                try {
                    document.execCommand('copy');
                    showCopyToast();
                } catch (e) {
                    console.error('Copy fallback failed', e);
                }
                document.body.removeChild(ta);
            }

            function showCopyToast() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Berhasil disalin ke clipboard!',
                    showConfirmButton: false,
                    timer: 1400
                });
            }

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }
        });
    </script>
@endsection
