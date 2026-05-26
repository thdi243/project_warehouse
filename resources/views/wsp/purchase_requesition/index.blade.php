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
            <div class="page-header mb-2" data-aos="fade-down">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="fw-bold fs-3">Purchase Requesition</h3>
                        <p class="fw-normal fs-6">Kelola Data Purchase Requesition Perusahaan</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="justify-content-md-end">
                            <a href="{{ url('/app/purchase-requesition/form') }}" target="_blank" class="btn btn-primary">
                                <i class="mdi mdi-file-document-edit me-2"></i>
                                <span>Form PR</span>
                            </a>
                            <button class="btn btn-outline-primary" id="btnRefresh">
                                <i class="mdi mdi-refresh me-2"></i>
                                <span>Refresh</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3" data-aos="fade-up">
                <div class="card-body p-auto">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <label class="form-label mb-1 small text-muted">Departemen</label>
                            <select id="filterDepartemen" class="form-select">
                                <option value="all">Semua Departemen</option>
                                @foreach ($departemen as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1 small text-muted">Jenis Item</label>
                            <select id="filterJenisPR" class="form-select">
                                <option value="all">Semua Jenis Item</option>
                                <option value="pr">PR</option>
                                <option value="blocked">Blocked/Reservasi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1 small text-muted">Status</label>
                            <select id="filterStatusPR" class="form-select">
                                <option value="all">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="finished">Finished</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1 small text-muted">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Cari User / No Doc ...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="card shadow-sm" data-aos="fade-up">
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
                                Klik badge status untuk melihat tracking approval PR
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
                        <table class="table table-hover table-borderedless align-middle" id="IncomingTable">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th class="text-center" style="width: 60px;">NO</th>
                                    <th>PR DATE</th>
                                    <th>NO DOC</th>
                                    <th>NO PR</th>
                                    <th>NAMA PEMINTA</th>
                                    <th>DEPARTEMEN</th>
                                    <th>STATUS</th>
                                    <th>FLAG</th>
                                    <th class="text-center">AKSI</th>
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
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th>Jenis</th>
                                    <td id="d_jenis">-</td>
                                </tr>
                                <tr>
                                    <th>Detail Jenis</th>
                                    <td id="d_detail_jenis">-</td>
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
                        <table class="table table-borderedless table-sm">
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

                currentPage = page;

                $.ajax({
                    url: "{{ url('api/purchase-requesition/getData') }}",
                    type: "GET",
                    data: {
                        page: page,
                        search: search,
                        jenis: jenis,
                        status: status,
                        departemen: departemen
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
                        if (res.success && res.data && Array.isArray(res.data.data) && res.data.data
                            .length > 0) {
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
                    const badgeClass = badgeStatus[pr.status] ?? 'secondary';
                    const isFinished = pr.status === 'finished';
                    const canConfirm = pr.status === 'approved';

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
                            <td>${pr.department}</td>
                            <td>
                                <span 
                                    class="badge badge-soft-${badgeClass}" 
                                    style="cursor:pointer"
                                    onclick="showApprovalTracking(${pr.id})"
                                >
                                    ${pr.status}
                                </span>
                            </td>
                            <td>${flagText}</td>
                            <td>
                                <div class="d-flex justify-content-center flex-wrap gap-1">

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

                                        <!-- Edit -->
                                        <button 
                                            class="btn btn-secondary btn-sm"
                                            onclick="editPR(${pr.id})"
                                            title="Edit"
                                        >
                                            <i class="mdi mdi-pencil"></i>
                                        </button>

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

            // Refresh button
            $('#btnRefresh').on('click', function() {
                $('#searchInput').val('');
                $('#filterJenisPR').val('all');
                $('#filterStatusPR').val('all');
                $('#filterDepartemen').val('all');
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
                $('#d_department').text(pr.department ?? '-');
                $('#d_jenis').text(pr.jenis ?? '-');
                $('#d_detail_jenis').text(pr.detail_jenis ?? '-');
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
                                <td>${item.keterangan ?? '-'}</td>
                                <td><span class="badge ${badgeClass}">${jenisText}</span></td>
                                <td>${item.alasan ?? '-'}</td>
                                <td class="text-center">${formatBadge(statusUser)}</td>
                                <td class="text-center">${formatBadge(statusWrh)}</td>
                            </tr>
                        `);
                    });
                }
            }

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
                        prNumber
                    ].join('\t');

                    rows.push(row);
                });

                const textToCopy = rows.join('\n');

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(textToCopy)
                        .then(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Format SAP berhasil disalin ke clipboard',
                                timer: 1500,
                                showConfirmButton: false
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
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Format SAP berhasil disalin ke clipboard',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            }
        });
    </script>
@endsection
