@extends('layouts.app')

@section('title', '| Riwayat Purchase Requisition')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-2" data-aos="fade-down">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="fw-bold fs-3">Riwayat Purchase Requisition</h3>
                        <p class="fw-normal fs-6">Data pengajuan PR Departemen Anda</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="justify-content-md-end">
                            {{-- <a href="{{ url('/app/purchase-requesition/form') }}" target="_blank" class="btn btn-primary">
                                <i class="mdi mdi-file-document-edit me-2"></i>
                                <span>Form PR</span>
                            </a> --}}
                            <button class="btn btn-outline-primary" id="btnRefresh">
                                <i class="mdi mdi-refresh me-2"></i>
                                <span>Refresh</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="card shadow-sm" data-aos="fade-up">
                <div class="card-header bg-light py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                <i class="mdi mdi-history me-2"></i>Data Riwayat PR
                            </h5>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Cari No Doc / Peminta / MID / Nama Barang">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info py-2 px-3 w-100" role="alert">
                        <small>
                            <i class="ri-information-line me-1"></i>
                            Klik <b>Status Approved</b> untuk melihat tracking approval PR
                        </small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-borderedless align-middle">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th style="width: 60px;">NO</th>
                                    <th>PR DATE</th>
                                    <th>NO DOC</th>
                                    <th>NO PR</th>
                                    <th>NAMA PEMINTA</th>
                                    <th>DEPARTEMEN</th>
                                    <th>STATUS APPROVED</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr class="empty-state-row">
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-2"></i>
                                            <h6 class="fw-bold">Belum Ada Data</h6>
                                            <p class="text-muted mb-0">
                                                Belum ada pengajuan PR dari departemen Anda.
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

    <!-- Modal Detail PR -->
    <div class="modal fade" id="modalDetailPR" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Detail Purchase Requisition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th style="width:150px;">PR Date</th>
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
                                    <th style="width:150px;">Jenis</th>
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
                    <h6 class="mb-2 fw-bold"><i class="mdi mdi-format-list-bulleted me-2"></i>Detail Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light align-middle">
                                <tr>
                                    <th>No</th>
                                    <th>MID</th>
                                    <th>Desc</th>
                                    <th>Qty</th>
                                    <th>UOM</th>
                                    <th>Jenis</th>
                                    <th>Alasan</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Manager User</th>
                                    <th class="text-center">Manager WRH</th>
                                </tr>
                            </thead>
                            <tbody id="detailItems">
                                <tr>
                                    <td colspan="10" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal tracking --}}
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Tracking Approval PR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="approvalTracking"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Follow Up WhatsApp Approver --}}
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

                    <div class="alert border mb-3 py-2 px-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">No. Dokumen:</span>
                            <span class="fw-bold text-dark" id="wa_no_doc">-</span>
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
                        <div class="p-2 border rounded bg-light" id="wa_target_phone_container">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-phone text-muted me-2 fs-16"></i>
                                    <span class="fw-bold font-monospace text-dark" id="wa_target_phone_display">-</span>
                                </div>
                                <span class="badge" id="wa_target_phone_badge">Terdaftar</span>
                            </div>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">
                            *Nomor diambil otomatis dari data akun user approver untuk mencegah penyalahgunaan.
                        </small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-muted mb-1 small">
                            <i class="mdi mdi-information-outline me-1"></i> Format Pesan (Otomatis By Sistem):
                        </label>
                        <div class="p-2 border rounded bg-light text-dark font-monospace" id="wa_preview_box"
                            style="font-size: 12px; white-space: pre-wrap; max-height: 175px; overflow-y: auto;">
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Pesan dikirim langsung via Fonnte WhatsApp
                            Gateway ke nomor di atas.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success d-flex align-items-center" id="btnSendWa">
                        <i class="bx bxl-whatsapp fs-16 me-1"></i>
                        <span id="btnSendWaText">Kirim WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadPRData();

            let allPR = [];
            let filteredPR = [];
            let currentPage = 1;
            const itemsPerPage = 10;

            function loadPRData() {
                $.ajax({
                    url: "{{ url('api/purchase-requesition/getRiwayat') }}",
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0 text-muted">Memuat riwayat...</p>
                                </td>
                            </tr>
                        `);
                    },
                    success: function(res) {
                        if (res.success && Array.isArray(res.data)) {
                            allPR = res.data;
                            filteredPR = allPR;
                            renderTable();
                        } else {
                            showEmptyState();
                        }
                    },
                    error: function(xhr) {
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="8" class="text-center text-danger py-3">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> Gagal memuat data dari server.
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            function showEmptyState() {
                $('#tableBody').html(`
                    <tr class="empty-state-row">
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-2"></i>
                                <h6 class="fw-bold">Belum Ada Data</h6>
                                <p class="text-muted mb-0">
                                    Belum ada pengajuan PR dari departemen Anda.
                                </p>
                            </div>
                        </td>
                    </tr>
                `);
            }

            function renderTable() {
                const tbody = $('#tableBody');
                tbody.empty();

                if (filteredPR.length === 0) {
                    showEmptyState();
                    updatePaginationInfo(0, 0, 0);
                    return;
                }

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, filteredPR.length);
                const pageData = filteredPR.slice(startIndex, endIndex);

                const badgeStatus = {
                    'pending': 'warning',
                    'approved': 'success',
                    'finished': 'info',
                    'rejected': 'danger',
                };

                pageData.forEach((pr, index) => {
                    let statusText = pr.status.toUpperCase();
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

                        if (maxApprovedLevel === 5) {
                            badgeClass = 'success';
                        } else if (maxApprovedLevel === 4) {
                            badgeClass = 'info';
                        } else if (maxApprovedLevel === 3) {
                            badgeClass = 'primary';
                        } else if (maxApprovedLevel === 2) {
                            badgeClass = 'secondary';
                        } else {
                            badgeClass = 'warning';
                        }
                    }

                    let waSentToday = false;
                    if (pr.approval && pr.approval.length > 0) {
                        const pendingApproval = pr.approval.find(a => a.status === 'pending');
                        if (pendingApproval && pendingApproval.last_wa_follow_up_at) {
                            const d = new Date(pendingApproval.last_wa_follow_up_at);
                            const now = new Date();
                            if (d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth() &&
                                d.getDate() === now.getDate()) {
                                waSentToday = true;
                            }
                        }
                    }

                    tbody.append(`
                        <tr>
                            <td>${startIndex + index + 1}</td>
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
                                    title="Klik untuk melihat detail approval"
                                >
                                    ${statusText}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" onclick="showDetailPR(${pr.id})" title="Lihat Detail">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                                <button class="btn btn-warning btn-print btn-sm" onclick="printPR(${pr.id})" title="Download PDF">
                                    <i class="mdi mdi-printer"></i>
                                </button>
                                @can('permission', 'wsp-pr-follow-wa')
                                    <button class="btn ${waSentToday ? 'btn-outline-success' : 'btn-success'} btn-sm btn-wa-followup" onclick="openFollowUpModal(${pr.id})" title="${waSentToday ? 'Follow Up WhatsApp (Sudah dikirim hari ini)' : 'Follow Up WhatsApp Approver'}">
                                        <i class="bx ${waSentToday ? 'bx-check-double' : 'bxl-whatsapp'}"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    `);
                });

                updatePaginationInfo(startIndex + 1, endIndex, filteredPR.length);
                renderPagination();
            }


            function updatePaginationInfo(from, to, total) {
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#totalRecords').text(total);
            }

            window.showDetailPR = function(id) {
                const pr = allPR.find(p => p.id === id);
                if (!pr) return;

                $('#d_pr_date').text(pr.pr_date);
                $('#d_requested_by').text(pr.requested_by);
                $('#d_department').text((pr.department ?? '').replace(/_/g, ' ').toUpperCase());
                $('#d_jenis').text(pr.jenis || '-');
                $('#d_detail_jenis').text(pr.detail_jenis || '-');
                $('#d_status').text(pr.status.toUpperCase());

                const tbody = $('#detailItems');
                tbody.empty();

                if (!pr.items || pr.items.length === 0) {
                    tbody.html('<tr><td colspan="10" class="text-center">Tidak ada item.</td></tr>');
                } else {
                    $.each(pr.items, function(i, item) {
                        const badgeClass = item.jenis === 'blocked' ? 'badge-soft-primary' :
                            'badge-soft-success';
                        const jenisText = item.jenis === 'blocked' ? 'Reservasi' : 'PR';

                        let statusUser = '-';
                        let statusWrh = '-';

                        if (item.approval && item.approval.length > 0) {
                            item.approval.forEach(a => {
                                const level = a.approval?.level;
                                if (level == 3) statusUser = a.status;
                                if (level == 4) statusWrh = a.status;
                            });
                        }

                        const formatBadge = (status) => {
                            if (status === '-') return '-';
                            let bg = 'warning';
                            if (status === 'approved') bg = 'success';
                            if (status === 'rejected') bg = 'danger';
                            return `<span class="badge badge-soft-${bg}">${status}</span>`;
                        };

                        const desc = pr.jenis === 'Jasa' ? (item.desc || '-') : (item.barang
                            ?.nama_barang || item.desc || '-');

                        let keteranganHtml = '-';
                        if (item.keterangan) {
                            keteranganHtml = `
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <span>${item.keterangan}</span>
                                    <button class="btn btn-sm btn-link p-0 text-secondary border-0 btn-copy-keterangan" 
                                            style="flex-shrink: 0;"
                                            data-text="${escapeHtmlAttribute(item.keterangan)}"
                                            title="Copy Keterangan">
                                        <i class="mdi mdi-content-copy"></i>
                                    </button>
                                </div>
                            `;
                        }

                        tbody.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${item.barang?.mid_barang ?? '-'}</td>
                                <td>${desc}</td>
                                <td>${item.qty}</td>
                                <td>${item.barang?.uom ?? '-'}</td>
                                <td><span class="badge ${badgeClass}">${jenisText}</span></td>
                                <td>${item.alasan ?? '-'}</td>
                                <td>${keteranganHtml}</td>
                                <td class="text-center">${formatBadge(statusUser)}</td>
                                <td class="text-center">${formatBadge(statusWrh)}</td>
                            </tr>
                        `);
                    });
                }

                $('#modalDetailPR').modal('show');
            };

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

            function renderPagination() {
                const totalPages = Math.ceil(filteredPR.length / itemsPerPage);
                const pagination = $('#pagination');
                pagination.empty();

                if (totalPages <= 1) return;

                pagination.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>
                    </li>
                `);

                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                        pagination.append(`
                            <li class="page-item ${i === currentPage ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                            </li>
                        `);
                    } else if (i === currentPage - 2 || i === currentPage + 2) {
                        pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                pagination.append(`
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </li>
                `);
            }

            window.changePage = function(page) {
                const totalPages = Math.ceil(filteredPR.length / itemsPerPage);
                if (page < 1 || page > totalPages) return;
                currentPage = page;
                renderTable();
            }

            $('#searchInput').on('input', function() {
                const keyword = $(this).val().toLowerCase().trim();
                if (keyword === '') {
                    filteredPR = allPR;
                } else {
                    filteredPR = allPR.filter(item => {
                        const requestedBy = item.requested_by ? item.requested_by.toLowerCase() :
                            '';
                        const noDoc = item.no_doc ? item.no_doc.toLowerCase() : '';
                        const prNumber = item.pr_number ? item.pr_number.toLowerCase() : '';
                        const department = item.department ? item.department.toLowerCase() : '';

                        const hasMatchingItem = item.items && item.items.some(prItem => {
                            const mid = prItem.barang?.mid_barang ? String(prItem.barang
                                .mid_barang).toLowerCase() : '';
                            const namaBarang = prItem.barang?.nama_barang ? String(prItem
                                .barang.nama_barang).toLowerCase() : '';
                            const desc = prItem.desc ? String(prItem.desc).toLowerCase() :
                                '';
                            const keterangan = prItem.keterangan ? String(prItem.keterangan)
                                .toLowerCase() : '';

                            return mid.includes(keyword) || namaBarang.includes(keyword) ||
                                desc.includes(keyword) || keterangan.includes(keyword);
                        });

                        return requestedBy.includes(keyword) || noDoc.includes(keyword) || prNumber
                            .includes(keyword) || department.includes(keyword) || hasMatchingItem;
                    });
                }
                currentPage = 1;
                renderTable();
            });

            $('#btnRefresh').on('click', function() {
                loadPRData();
            });

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
                    } else if (a.status === 'rejected') {
                        badge = 'danger';
                        icon = 'mdi-close-circle';
                        text = 'Rejected';
                    }

                    let catatanHtml = '';
                    if (a.catatan) {
                        catatanHtml =
                            `<div class="small mt-1 text-dark bg-light p-2 rounded">Catatan: ${a.catatan}</div>`;
                    }

                    let lastWaHtml = '';
                    if (a.last_wa_follow_up_at) {
                        const formattedWaDate = new Date(a.last_wa_follow_up_at).toLocaleString(
                            'id-ID');
                        lastWaHtml = `
                            <div class="small text-muted mt-1">
                                <i class="bx bxl-whatsapp text-success me-1"></i> Follow up WA terakhir: <span class="fw-semibold text-dark">${formattedWaDate}</span>
                            </div>
                        `;
                    }

                    let btnFollowUpHtml = '';
                    if (a.status === 'pending') {
                        btnFollowUpHtml = `
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 fs-12" onclick="$('#approvalModal').modal('hide'); setTimeout(() => openFollowUpModal(${pr.id}, ${a.id}), 350);" title="Follow Up WhatsApp">
                                    <i class="bx bxl-whatsapp me-1"></i> Follow Up WhatsApp
                                </button>
                            </div>
                        `;
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
                                <span class="badge bg-${badge}">${text}</span>
                                <div class="small text-muted mt-1">
                                    ${a.action_at ? a.action_at : 'Belum diproses'}
                                </div>
                                <div class="small text-muted mt-1">
                                    ${a.approver ? a.approver.nama_lengkap : '-'} | ${a.approver && a.approver.departemen ? a.approver.departemen.replace(/_/g, ' ').toUpperCase() : '-'}
                                </div>
                                ${catatanHtml}
                                @can('permission', 'wsp-pr-follow-wa')
                                    ${lastWaHtml}
                                    ${btnFollowUpHtml}
                                @endcan
                            </div>
                        </div>
                    `;
                });

                $('#approvalTracking').html(html);
                $('#approvalModal').modal('show');
            }

            let currentFollowUpPr = null;
            let currentFollowUpApproval = null;

            function generateDefaultWaMessage(pr, approval) {
                const approverName = approval?.approver?.nama_lengkap || approval?.role || 'Bapak/Ibu Approver';
                const role = approval?.role ? approval.role.replace(/_/g, ' ') : 'Approver';
                const level = approval?.level || '-';
                const noDoc = pr.no_doc || '-';
                const prNumber = pr.pr_number || '-';
                const reqBy = pr.requested_by || '-';
                const dept = (pr.department || '').replace(/_/g, ' ').toUpperCase();
                // Gunakan URL alias singkat /pr-app
                const urlShort = 'https://tinyurl.com/ApprovalPR';

                return `*Halo Bapak/Ibu ${approverName},*\n\n` +
                    `Pengingat persetujuan (approval) Purchase Requisition di sistem *PR Online*:\n` +
                    `• *No. Dokumen*: ${noDoc}\n` +
                    `• *Pengaju*: ${reqBy}\n` +
                    `• *Departemen*: ${dept}\n` +
                    `• *Tahap*: Level ${level} - ${role}\n\n` +
                    `Mohon kesediaan Bapak/Ibu untuk memeriksa notifikasi di *Inbox* atau folder *Spam* email Anda.\n\n` +
                    `👉 *Silakan setujui di sini / tinjau:*\n` +
                    `${urlShort}\n\n` +
                    `Terima kasih atas kerja samanya.\n` +
                    `_Sistem PR Online_`;
            }

            window.openFollowUpModal = function(id, preferredApprovalId = null) {
                const pr = allPR.find(p => p.id === id);
                if (!pr) {
                    Swal.fire('Error', 'Data PR tidak ditemukan', 'error');
                    return;
                }

                const approvals = pr.approval ? [...pr.approval].sort((a, b) => a.level - b.level) : [];

                // Cari approver pending yang aktif saat ini (level approve sekarang)
                let targetApproval = null;
                if (preferredApprovalId) {
                    targetApproval = approvals.find(a => a.id === preferredApprovalId);
                }
                if (!targetApproval) {
                    targetApproval = approvals.find(a => a.status === 'pending');
                }

                if (!targetApproval) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak Ada Tahap Pending',
                        text: 'Semua persetujuan pada dokumen PR ini sudah diproses atau selesai.',
                    });
                    return;
                }

                currentFollowUpPr = pr;
                currentFollowUpApproval = targetApproval;

                $('#wa_pr_id').val(pr.id);
                $('#wa_approval_id').val(targetApproval.id);
                $('#wa_no_doc').text(pr.no_doc || '-');
                $('#wa_requested_by').text(pr.requested_by || '-');
                $('#wa_department').text((pr.department || '').replace(/_/g, ' ').toUpperCase());

                const approverName = targetApproval.approver ? targetApproval.approver.nama_lengkap :
                    'Belum Ditentukan';
                const approverRole =
                    `Level ${targetApproval.level} - ${targetApproval.role.replace(/_/g, ' ')}`;
                $('#wa_approver_name').text(approverName);
                $('#wa_approver_role').text(approverRole);

                // Ambil nomor whatsapp user approver jika ada, kosongkan jika belum ada
                const phone = (targetApproval.approver?.no_hp || '').trim();
                const hasPhone = phone.length > 0;

                if (hasPhone) {
                    $('#wa_target_phone_display').text(phone).removeClass('text-danger fst-italic').addClass(
                        'text-dark');
                    $('#wa_target_phone_badge')
                        .removeClass('bg-soft-danger text-danger border-danger')
                        .addClass('bg-soft-success text-success border border-success')
                        .text('Terdaftar');
                } else {
                    $('#wa_target_phone_display').html(
                        '<span class="text-danger fst-italic">Belum Terdaftar</span>');
                    $('#wa_target_phone_badge')
                        .removeClass('bg-soft-success text-success border-success')
                        .addClass('bg-soft-danger text-danger border border-danger')
                        .text('Belum Terdaftar');
                }

                // Preview pesan otomatis by sistem
                const msg = generateDefaultWaMessage(pr, targetApproval);
                $('#wa_preview_box').text(msg);

                // Reset alert dan status tombol kirim
                $('#wa_rate_limit_alert_container').empty();
                $('#btnSendWa').prop('disabled', false).removeClass('disabled').html(
                    '<i class="bx bxl-whatsapp fs-16 me-1"></i> <span id="btnSendWaText">Kirim WhatsApp</span>'
                );

                if (!hasPhone) {
                    $('#wa_rate_limit_alert_container').html(`
                        <div class="alert alert-danger py-2 px-3 small d-flex align-items-start mb-3">
                            <i class="bx bx-error-circle fs-20 me-2 flex-shrink-0 text-danger mt-1"></i>
                            <div>
                                <strong>Nomor WhatsApp Belum Terdaftar!</strong><br>
                                Akun approver <b>${approverName}</b> belum memiliki nomor WhatsApp di sistem.<br>
                                Silakan hubungi <b>Tim IT</b> atau <b>Admin WSP</b> untuk mendaftarkan nomor telepon.
                            </div>
                        </div>
                    `);
                    $('#btnSendWa').prop('disabled', true).addClass('disabled').html(
                        '<i class="bx bx-x-circle me-1"></i> Nomor Belum Terdaftar'
                    );
                } else if (targetApproval.last_wa_follow_up_at) {
                    // Cek batas pengiriman: maksimal 1x sehari untuk tahap approval ini
                    const lastDate = new Date(targetApproval.last_wa_follow_up_at);
                    const now = new Date();
                    const isSameDay = lastDate.getFullYear() === now.getFullYear() &&
                        lastDate.getMonth() === now.getMonth() &&
                        lastDate.getDate() === now.getDate();

                    if (isSameDay) {
                        const timeStr = lastDate.toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        $('#wa_rate_limit_alert_container').html(`
                            <div class="alert alert-warning py-2 px-3 small d-flex align-items-center mb-3">
                                <i class="bx bx-time-five fs-20 me-2 flex-shrink-0 text-warning"></i>
                                <div>
                                    <strong>Pengingat Sudah Terkirim Hari Ini</strong><br>
                                    Pesan WhatsApp untuk tahap persetujuan ini sudah dikirim hari ini pada pukul <b>${timeStr} WIB</b>.<br>
                                    Untuk mencegah spam, pengiriman dibatasi maksimal <b>1 kali sehari</b>.
                                </div>
                            </div>
                        `);
                        $('#btnSendWa').prop('disabled', true).addClass('disabled').html(
                            '<i class="bx bx-check-double me-1"></i> Sudah Dikirim Hari Ini'
                        );
                    }
                }

                $('#modalFollowUpWa').modal('show');
            };

            $('#btnSendWa').on('click', function() {
                const prId = $('#wa_pr_id').val();
                const approvalId = $('#wa_approval_id').val();
                const phone = (currentFollowUpApproval?.approver?.no_hp || '').trim();

                if (!phone) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nomor WhatsApp Belum Terdaftar',
                        text: 'Nomor WhatsApp approver belum terdaftar di sistem. Silakan hubungi Tim IT atau Admin WSP untuk mendaftarkan nomor telepon.',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Kirim Pengingat WhatsApp?',
                    text: `Pesan follow up akan dikirim ke nomor ${phone} via Fonnte.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bx bxl-whatsapp me-1"></i> Ya, Kirim Sekarang',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const btn = $('#btnSendWa');
                        const originalHtml = btn.html();
                        btn.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Mengirim...'
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
                                    if (currentFollowUpApproval) {
                                        currentFollowUpApproval.last_wa_follow_up_at =
                                            new Date().toISOString();
                                    }

                                    // Refresh tampilan tabel jika ada
                                    if (typeof renderTable === 'function') {
                                        renderTable();
                                    }

                                    $('#modalFollowUpWa').modal('hide');
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil Terkirim!',
                                        text: res.message ||
                                            'Pesan WhatsApp berhasil dikirim ke approver.',
                                        timer: 3500,
                                        showConfirmButton: true
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal Mengirim',
                                        text: res.message ||
                                            'Terjadi kesalahan saat mengirim pesan WhatsApp.'
                                    });
                                }
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html(originalHtml);
                                const err = xhr.responseJSON?.message ||
                                    'Gagal menghubungi server WhatsApp Fonnte.';
                                const isRateLimit = (xhr.status === 429) || (xhr
                                    .responseJSON?.already_sent_today);

                                Swal.fire({
                                    icon: isRateLimit ? 'warning' : 'error',
                                    title: isRateLimit ?
                                        'Batas Pengiriman Tercapai' : 'Gagal',
                                    text: err
                                });

                                if (isRateLimit && currentFollowUpApproval) {
                                    currentFollowUpApproval.last_wa_follow_up_at =
                                        new Date().toISOString();
                                    $('#wa_rate_limit_alert_container').html(`
                                        <div class="alert alert-warning py-2 px-3 small d-flex align-items-center mb-3">
                                            <i class="bx bx-time-five fs-20 me-2 flex-shrink-0 text-warning"></i>
                                            <div>
                                                <strong>Batas Pengiriman Tercapai (1x Sehari)</strong><br>
                                                ${err}
                                            </div>
                                        </div>
                                    `);
                                    btn.prop('disabled', true).addClass('disabled')
                                        .html(
                                            '<i class="bx bx-check-double me-1"></i> Sudah Dikirim Hari Ini'
                                        );
                                    if (typeof renderTable === 'function') {
                                        renderTable();
                                    }
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
