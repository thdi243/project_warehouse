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

            {{-- Table --}}
            <div class="card shadow-sm" data-aos="fade-up">
                <div class="card-header bg-light py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                <i class="mdi mdi-table me-2"></i>Data Purchase Requesition
                            </h5>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Cari User Peminta ...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info py-2 px-3 w-100" role="alert">
                        <small>
                            <i class="ri-information-line me-1"></i>
                            Klik badge status untuk melihat tracking approval PR
                        </small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-borderedless align-middle" id="IncomingTable">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th style="width: 60px;">NO</th>
                                    <th>PR DATE</th>
                                    <th>No Doc</th>
                                    <th>NO PR</th>
                                    <th>NAMA PEMINTA</th>
                                    <th>DEPARTEMEN</th>
                                    <th>STATUS</th>
                                    @can('wsp-data-pr-plus')
                                        <th class="text-center">AKSI</th>
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                                    <th class="text-center">Status Manager <br> User, WRH </th>
                                </tr>
                            </thead>
                            <tbody id="detailItems">
                                <tr>
                                    <td colspan="6" class="text-center">Loading...</td>
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

    {{-- Modal Confirm --}}
    <div class="modal fade" id="confirmModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Confirm PR</h5>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="confirm_pr_id">

                    <label>No PR</label>
                    <input type="text" id="no_pr" class="form-control">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" id="btnFinished">Finished</button>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal TTD --}}
    <div class="modal fade" id="signatureModal">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Signature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    @if ($signature)
                        <div class="border rounded p-3 text-center bg-white mb-2">
                            <p class="small text-muted mb-2">Tanda tangan yang akan Anda gunakan:</p>
                            <img src="{{ asset('storage/' . $signature->signature) }}" alt="Signature"
                                style="max-height: 150px; width: auto;">
                        </div>
                        <input type="hidden" id="useStoredSignature" value="1">
                    @else
                        <canvas id="signaturePad" class="signature-canvas"></canvas>
                        <div class="mt-2 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clearSignature">
                                <i class="mdi mdi-eraser me-1"></i> Bersihkan
                            </button>
                        </div>
                        <input type="hidden" id="useStoredSignature" value="0">
                    @endif
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" id="submitSignature">
                        Submit
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

            // Ambil data dari backend
            function loadPRData() {
                $.ajax({
                    url: "{{ url('api/purchase-requesition/getData') }}",
                    type: "GET",
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
                        if (res.success && Array.isArray(res.data)) {
                            allPR = res.data;
                            filteredPR = allPR;
                            renderTable();
                        } else {
                            $('#tableBody').html(
                                `<tr class="empty-state-row">
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
                            `);
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

            // Render table
            function renderTable() {
                const tbody = $('#tableBody');
                tbody.empty();

                if (filteredPR.length === 0) {
                    tbody.html(`
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
                    `);
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
                    const badgeClass = badgeStatus[pr.status] ?? 'secondary';
                    const isFinished = pr.status === 'finished';
                    const canConfirm = pr.status === 'approved';

                    tbody.append(`
                        <tr>
                            <td>${startIndex + index + 1}</td>
                            <td>${pr.pr_date}</td>
                            <td>${pr.no_doc}</td>
                            <td>${pr.pr_number}</td>
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
                            @can('wsp-data-pr-plus')
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button 
                                            class="btn ${isFinished ? 'btn-light' : (canConfirm ? 'btn-success' : 'btn-outline-secondary')} btn-confirm btn-sm"
                                            data-id="${pr.id}"
                                            title="${isFinished ? 'Already Finished' : (canConfirm ? 'Confirm' : 'Waiting for Approval')}"
                                            ${!canConfirm ? 'disabled' : ''}
                                        >
                                            <i class="mdi ${isFinished ? 'mdi-check-all' : 'mdi-check'}"></i>
                                            ${isFinished ? 'Confirmed' : 'Confirm'}
                                        </button>
                                        <button class="btn btn-primary btn-copy btn-sm" onclick="copyFormatted(${pr.id})" title="Copy Formatted">
                                            <i class="mdi mdi-content-copy"></i> Copy
                                        </button>
                                        <button class="btn btn-info btn-edit btn-sm" onclick="detailPR(${pr.id})" title="Detail">
                                            <i class="mdi mdi-eye"></i>
                                        </button>
                                        <button class="btn btn-danger btn-delete btn-sm" onclick="deletePR(${pr.id})" title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                        <button class="btn btn-warning btn-print btn-sm" onclick="printPR(${pr.id})" title="Print">
                                            <i class="mdi mdi-printer"></i>
                                        </button>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    `);
                });

                updatePaginationInfo(startIndex + 1, endIndex, filteredPR.length);
                renderPagination();
            }

            // Update pagination info
            function updatePaginationInfo(from, to, total) {
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#totalRecords').text(total);
            }

            // Render pagination
            function renderPagination() {
                const totalPages = Math.ceil(filteredPR.length / itemsPerPage);
                const pagination = $('#pagination');
                pagination.empty();

                if (totalPages <= 1) return;

                // Previous button
                pagination.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>
                    </li>
                `);

                // Page numbers
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

                // Next button
                pagination.append(`
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </li>
                `);
            }

            // Change page
            window.changePage = function(page) {
                const totalPages = Math.ceil(filteredPR.length / itemsPerPage);
                if (page < 1 || page > totalPages) return;
                currentPage = page;
                renderTable();
            }

            // Event search
            $('#searchInput').on('input', function() {
                const keyword = $(this).val().toLowerCase().trim();

                if (keyword === '') {
                    filteredPR = allPR; // reset
                } else {
                    filteredPR = allPR.filter(item => {
                        // const mid = item.items.barang?.mid_barang ?
                        //     item.items.barang.mid_barang.toString().toLowerCase() :
                        //     '';

                        const requestedBy = item.requested_by ?
                            item.requested_by.toLowerCase() :
                            '';

                        return requestedBy.includes(keyword);
                    });
                }

                currentPage = 1; // reset ke page 1 saat search
                renderTable();
            });

            // Refresh button
            $('#btnRefresh').on('click', function() {
                loadPRData();
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
                            url: "{{ route('stock.pr.delete', '') }}/" + id,
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


                const tbody = $('#detailItems');
                tbody.empty();

                if (!pr.items || pr.items.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Tidak ada item
                            </td>
                        </tr>
                    `);
                } else {
                    $.each(pr.items, function(i, item) {
                        tbody.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${item.barang?.mid_barang ?? '-'}</td>
                                <td>${item.barang?.nama_barang ?? '-'}</td>
                                <td>${item.qty}</td>
                                <td>${item.barang?.uom ?? '-'}</td>
                                <td>${item.keterangan ?? '-'}</td>
                                <td>${item.approval?.map(a => a.status).join(', ') ?? '-'}</td>
                            </tr>
                        `);
                    });
                }

                $('#modalDetailPR').modal('show');
            };

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
                if (pr.status !== 'finished') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Belum Bisa Copy',
                        text: 'PR harus finished terlebih dahulu',
                        confirmButtonColor: '#f59e0b'
                    });
                    return;
                }

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
                    item.approval?.some(a => a.status === 'approved')
                );

                if (approvedItems.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Ada Item Approved',
                        text: 'Tidak ada item yang bisa dicopy ke SAP'
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

            // TTD
            let prID = null;

            // klik confirm
            $(document).on('click', '.btn-confirm', function() {

                prID = $(this).data('id');

                $('#confirm_pr_id').val(prID);

                $('#confirmModal').modal('show');
            });

            $('#btnFinished').click(function() {

                let no_pr = $('#no_pr').val();

                if (!no_pr) {
                    alert('No PR wajib diisi');
                    return;
                }

                $('#confirmModal').modal('hide');
                $('#signatureModal').modal('show');

            });

            const hasStoredSignature = '{{ $signature ? 'true' : 'false' }}' === 'true';
            let signaturePad;
            let canvas = document.getElementById("signaturePad");

            if (!hasStoredSignature) {
                function resizeCanvas() {
                    let ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                }

                signaturePad = new SignaturePad(canvas, {
                    minWidth: 1,
                    maxWidth: 2.5,
                    penColor: "black"
                });

                $('#signatureModal').on('shown.bs.modal', function() {
                    resizeCanvas();
                    signaturePad.clear();
                });

                $('#clearSignature').click(function() {
                    signaturePad.clear();
                });
            }

            function trimCanvas(canvas) {

                const ctx = canvas.getContext("2d");
                const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = pixels.data;

                let top = null,
                    left = null,
                    right = null,
                    bottom = null;

                for (let y = 0; y < canvas.height; y++) {
                    for (let x = 0; x < canvas.width; x++) {

                        let index = (y * canvas.width + x) * 4;

                        if (data[index + 3] > 0) {

                            if (top === null) top = y;
                            if (left === null || x < left) left = x;
                            if (right === null || x > right) right = x;
                            if (bottom === null || y > bottom) bottom = y;

                        }

                    }
                }

                let width = right - left;
                let height = bottom - top;

                let trimmed = ctx.getImageData(left, top, width, height);

                let copy = document.createElement("canvas");
                let copyCtx = copy.getContext("2d");

                copy.width = width;
                copy.height = height;

                copyCtx.putImageData(trimmed, 0, 0);

                return copy;
            }

            $('#submitSignature').click(function() {
                const useStored = $('#useStoredSignature').val() == '1';
                let signature = null;

                if (!useStored) {
                    if (signaturePad.isEmpty()) {
                        Swal.fire({
                            icon: "warning",
                            title: "Tanda tangan kosong",
                            text: "Silakan buat tanda tangan terlebih dahulu"
                        });
                        return;
                    }
                    let trimmedCanvas = trimCanvas(canvas);
                    signature = trimmedCanvas.toDataURL();
                }

                let no_pr = $('#no_pr').val();

                Swal.fire({
                    title: "Konfirmasi PR?",
                    text: "Pastikan data sudah benar",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Submit",
                }).then((result) => {

                    if (result.isConfirmed) {

                        Swal.fire({
                            title: "Menyimpan...",
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.ajax({
                            url: `/purchase-requesition/approval-pr/action/${prID}`,
                            type: "POST",
                            data: {
                                status: "approved",
                                ttd: signature,
                                use_stored_signature: useStored ? 1 : 0,
                                no_pr: no_pr,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {

                                $('#signatureModal').modal('hide');

                                Swal.fire({
                                    icon: "success",
                                    title: "Berhasil",
                                    text: res.message
                                });

                                loadPRData();

                            },
                            error: function(xhr) {

                                Swal.fire({
                                    icon: "error",
                                    title: "Gagal",
                                    text: xhr.responseJSON?.message ??
                                        "Terjadi kesalahan"
                                });

                            }
                        });

                    }

                });

            });
        });
    </script>
@endsection
