@extends('layouts.app')

@section('title', '| Report SO WCP')

@section('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.2);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        }

        .select2-container--default .select2-selection--single {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: .375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0"><i class="mdi mdi-file-document-outline me-2"></i>Report Stock Opname WCP
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WCP</a></li>
                                <li class="breadcrumb-item active">Stock Opname</li>
                                <li class="breadcrumb-item active">Report Stock Opname</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="card shadow-sm border-0 rounded-3 mb-3" data-aos="fade-right" data-aos-delay="100">
                <div class="card-body">
                    <form id="formFilterReport">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-3 col-md-6">
                                <label for="tgl_opname" class="form-label fw-semibold">Pilih Tanggal Opname</label>
                                <input type="date" id="tgl_opname" name="tgl_opname" class="form-control"
                                    value="{{ request('tgl_opname', now()->toDateString()) }}" required>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <label for="searchReport" class="form-label fw-semibold">Cari MID / Nama Barang</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="mdi mdi-magnify text-muted"></i>
                                    </span>
                                    <input type="text" id="searchReport" class="form-control"
                                        placeholder="Ketik MID atau nama barang...">
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-magnify me-1"></i>
                                        Tampilkan
                                    </button>

                                    <button type="button" class="btn btn-outline-danger" id="btnExportPdf">
                                        <i class="mdi mdi-file-pdf-box me-1"></i>
                                        PDF
                                    </button>

                                    @can('permission', 'stock-opname-wcp-form')
                                        <button type="button" class="btn btn-success d-none" id="btnSendApproval">
                                            <i class="mdi mdi-send me-1"></i>
                                            Kirim Approval
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Approval Wrapper -->
            <div id="approvalWrapper" class="mb-3" data-aos="fade-up" data-aos-delay="150"></div>

            <!-- Report Content -->
            <div class="card shadow-sm border-0 rounded-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-pills report-tabs mb-3" id="reportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active shadow-none" id="data-tab" data-bs-toggle="pill"
                                data-bs-target="#tabData" type="button" role="tab" aria-controls="tabData"
                                aria-selected="true">
                                <i class="mdi mdi-table me-1"></i> Data Opname
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link shadow-none" id="approval-tab" data-bs-toggle="pill"
                                data-bs-target="#tabApproval" type="button" role="tab" aria-controls="tabApproval"
                                aria-selected="false">
                                <i class="mdi mdi-account-clock-outline me-1"></i> Belum Approve
                                <span id="pendingApprovalBadge" class="badge bg-warning text-dark ms-1">0</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab 1: Data Opname -->
                        <div class="tab-pane fade show active" id="tabData" role="tabpanel" aria-labelledby="data-tab">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0" id="tableReportList">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>MID</th>
                                            <th class="text-start">Nama Barang</th>
                                            <th class="text-end">Qty Sistem</th>
                                            <th class="text-end">Qty Fisik</th>
                                            <th class="text-end">Selisih</th>
                                            <th>Status</th>
                                            <th class="text-start">Catatan / Keterangan</th>
                                            <th style="width: 120px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">
                                                Silakan tentukan tanggal lalu klik <strong>Tampilkan Laporan</strong>.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Belum Approve -->
                        <div class="tab-pane fade" id="tabApproval" role="tabpanel" aria-labelledby="approval-tab">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0 text-nowrap"
                                    id="tablePendingApproval">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 50px;">No</th>
                                            <th>Tanggal Opname</th>
                                            <th>Stock Control</th>
                                            <th>Tanggal Request</th>
                                            <th>Approver</th>
                                            <th>Jabatan</th>
                                            <th class="text-center">Status</th>
                                            <th>Keterangan / Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pendingApprovalBody">
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                Memuat data pending approval...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="approvalEmptyState" class="text-center py-5 d-none">
                                <i class="mdi mdi-check-circle-outline text-success" style="font-size: 56px;"></i>
                                <p class="text-muted mt-2 mb-0">Tidak ada approval yang masih pending.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Send Approval -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-account-check-outline me-2"></i>Pilih Approver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formApproval">
                        @csrf
                        <div class="mb-3">
                            <label for="selectForeman" class="form-label fw-semibold">Pilih Foreman</label>
                            <select id="selectForeman" name="foreman_id" class="form-select" required>
                                <option value="">-- Pilih Foreman --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="selectSupervisor" class="form-label fw-semibold">Pilih Supervisor / Dept
                                Head</label>
                            <select id="selectSupervisor" name="supervisor_id" class="form-select" required>
                                <option value="">-- Pilih Supervisor / Dept Head --</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnSubmitApproval" class="btn btn-success">Kirim Approval</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Report -->
    <div class="modal fade" id="detailReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="mdi mdi-eye-outline me-2"></i>Detail Stock Opname</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 200px;">MID</th>
                                <td id="detailMid"></td>
                            </tr>
                            <tr>
                                <th>Nama Barang</th>
                                <td id="detailNamaBarang"></td>
                            </tr>
                            <tr>
                                <th>Qty Sistem</th>
                                <td id="detailQtySistem" class="text-end text-primary fw-semibold"></td>
                            </tr>
                            <tr>
                                <th>Riwayat Input Fisik</th>
                                <td>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover mb-0 align-middle">
                                            <thead class="table-light text-center">
                                                <tr>
                                                    <th style="width: 50px;">No</th>
                                                    <th>Waktu Input</th>
                                                    <th>Qty Full Pallet</th>
                                                    <th>Qty Receh</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detailInputsList">
                                                <!-- Dynamic detail rows -->
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Qty Fisik</th>
                                <td id="detailQtyFisik" class="text-end fw-bold text-success"></td>
                            </tr>
                            <tr>
                                <th>Selisih</th>
                                <td id="detailSelisih" class="text-end fw-bold"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="detailStatus" class="text-center"></td>
                            </tr>
                            <tr>
                                <th>Keterangan</th>
                                <td id="detailKeterangan"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Report Row -->
    <div class="modal fade" id="editReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="mdi mdi-pencil-outline me-2"></i>Edit Hasil Opname</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditReport">
                    @csrf
                    <input type="hidden" id="editReportId">
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama Barang</label>
                                <input type="text" id="editReportNama" class="form-control bg-light" readonly>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Daftar Detail Input Fisik</h6>
                        <div id="editReportItemsList">
                            <!-- Dynamic items list -->
                        </div>

                        <div class="mb-3 mt-3">
                            <label for="editKeterangan" class="form-label fw-semibold">Catatan / Keterangan</label>
                            <textarea id="editKeterangan" name="keterangan" class="form-control" rows="3"
                                placeholder="Alasan selisih..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- <script src="{{ asset('js/app.js') }}"></script> --}}
    <script>
        $(document).ready(function() {
            @if (session('error'))
                toastr.error("{{ session('error') }}", "Peringatan!");
            @endif

            // Automatically pull report for today on load
            loadReport();

            $('#formFilterReport').on('submit', function(e) {
                e.preventDefault();
                loadReport();
            });

            $('#btnExportPdf').on('click', function() {
                const date = $('#tgl_opname').val();

                if (!date) {
                    Swal.fire('Perhatian', 'Silakan pilih tanggal opname terlebih dahulu.', 'warning');
                    return;
                }

                const url = `{{ route('wcp.stock_opname.export') }}?tgl_opname=${date}`;
                window.open(url, '_blank');
            });

            // Open Send Approval Modal
            $('#btnSendApproval').on('click', function() {
                loadApproverOptions(() => {
                    $('#approvalModal').modal('show');
                });
            });

            // Submit Send Approval
            $('#btnSubmitApproval').on('click', function() {
                const btn = $(this);
                const soId = $('#approvalWrapper').data('so-id');
                const foremanId = $('#selectForeman').val();
                const supervisorId = $('#selectSupervisor').val();

                if (!foremanId || !supervisorId) {
                    Swal.fire('Peringatan', 'Silakan pilih Foreman dan Supervisor.', 'warning');
                    return;
                }

                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...');

                $.ajax({
                    url: "{{ route('wcp.stock_opname.send-approval') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        so_id: soId,
                        foreman_id: foremanId,
                        supervisor_id: supervisorId
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html('Kirim Approval');
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            $('#approvalModal').modal('hide');
                            loadReport();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('Kirim Approval');
                        Swal.fire('Error', xhr.responseJSON?.message ||
                            'Gagal mengirim approval.', 'error');
                    }
                });
            });

            // Load pending approval report on init
            loadPendingApprovals();

            // Click handler for pending approval row to navigate/filter by date
            $(document).on('click', '#pendingApprovalBody tr', function() {
                const date = $(this).data('date');
                if (date) {
                    $('#tgl_opname').val(date);
                    // Trigger the Data Opname tab pill show to switch back
                    var triggerEl = document.querySelector('#reportTabs button[id="data-tab"]');
                    if (triggerEl) {
                        var tab = bootstrap.Tab.getInstance(triggerEl) || new bootstrap.Tab(triggerEl);
                        tab.show();
                    }
                    loadReport();
                }
            });
        });

        function loadReport() {
            const tableBody = $('#tableReportList tbody');
            const date = $('#tgl_opname').val();

            if (!date) return;

            tableBody.html(`
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat laporan...
                    </td>
                </tr>
            `);

            $.ajax({
                url: "{{ route('wcp.stock_opname.report.getData') }}",
                type: "GET",
                data: {
                    tgl_opname: date
                },
                success: function(res) {
                    tableBody.empty();

                    // Reload pending approvals count/state
                    loadPendingApprovals();

                    if (res.status === 'success' && res.data.length > 0) {
                        res.data.forEach((item, index) => {
                            const barangName = item.barang ? item.barang.nama_barang : 'N/A';
                            const barangMid = item.barang ? item.barang.mid : 'N/A';
                            const uom = item.barang ? item.barang.uom : '';
                            const qtySistem = item.qty_sistem.toLocaleString('id-ID');
                            const qtyFisik = item.qty_fisik.toLocaleString('id-ID');
                            const selisih = item.selisih.toLocaleString('id-ID');
                            const note = item.keterangan ? item.keterangan : '-';

                            let statusBadge = '';
                            if (item.status === 'lebih') {
                                statusBadge = '<span class="badge bg-warning px-2 py-1">LEBIH</span>';
                            } else if (item.status === 'kurang') {
                                statusBadge = '<span class="badge bg-danger px-2 py-1">KURANG</span>';
                            } else {
                                statusBadge = '<span class="badge bg-success px-2 py-1">MATCH</span>';
                            }

                            const isDraft = res.sop && res.sop.status === 'draft';
                            const isSpv = res.sop && res.sop.user_id == {{ Auth::user()->id }};

                            tableBody.append(`  
                                <tr>
                                    <td class="text-center font-semibold">${index + 1}</td>
                                    <td class="text-center">${barangMid}</td>
                                    <td>${barangName}</td>
                                    <td class="text-end">${qtySistem}</td>
                                    <td class="text-end">${qtyFisik}</td>
                                    <td class="text-end fw-bold">${selisih}</td>
                                    <td class="text-center">${statusBadge}</td>
                                    <td>${note}</td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="viewDetailReport(${item.id})" title="Detail">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editReportRow(${item.id})" title="Edit" ${!isDraft ? 'disabled' : ''}>
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteReportRow(${item.id})" title="Hapus" ${!isDraft ? 'disabled' : ''}>
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                        checkApprovalStatus(res.sop, date);
                    } else {
                        tableBody.append(`
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    Laporan SO tidak ditemukan untuk tanggal: <strong>${date}</strong>
                                </td>
                            </tr>
                        `);
                        checkApprovalStatus(null, date);
                    }
                },
                error: function(xhr) {
                    tableBody.html(`
                        <tr>
                            <td colspan="9" class="text-center py-4 text-danger">Gagal memuat data laporan dari server.</td>
                        </tr>
                    `);
                    checkApprovalStatus(null, date);
                    loadPendingApprovals();
                }
            });
        }

        function loadPendingApprovals() {
            $.ajax({
                url: "{{ route('wcp.stock_opname.report.pending-approval') }}",
                type: "GET",
                dataType: "json",
                success: function(res) {
                    if (res.status === 'success') {
                        const summary = res.approval_summary;
                        const pendingCount = summary.pending_count || 0;
                        const items = summary.items || [];

                        $('#pendingApprovalBadge').text(pendingCount);

                        const tableBody = $('#pendingApprovalBody');
                        const emptyState = $('#approvalEmptyState');

                        if (items.length === 0) {
                            tableBody.empty().closest('.table-responsive').addClass('d-none');
                            emptyState.removeClass('d-none');
                            return;
                        }

                        emptyState.addClass('d-none');
                        tableBody.empty().closest('.table-responsive').removeClass('d-none');

                        items.forEach((item, index) => {
                            let badgeClass = 'bg-secondary';
                            let statusText = item.status;
                            if (item.status === 'pending') {
                                badgeClass = 'bg-warning text-dark';
                                statusText = 'Waiting';
                            } else if (item.status === 'read') {
                                badgeClass = 'bg-info';
                                statusText = 'Not Send';
                            } else if (item.status === 'rejected') {
                                badgeClass = 'bg-danger';
                                statusText = 'Rejected';
                            } else if (item.status === 'approved') {
                                badgeClass = 'bg-success';
                                statusText = 'Approved';
                            }

                            const note = item.catatan ? item.catatan : '-';
                            const reqAt = item.requested_at ? item.requested_at : '-';

                            tableBody.append(`
                                <tr style="cursor: pointer;" data-date="${item.tgl_opname}">
                                    <td class="text-center font-semibold">${index + 1}</td>
                                    <td>${item.tgl_opname}</td>
                                    <td>${item.operator}</td>
                                    <td>${reqAt}</td>
                                    <td><strong>${item.nama}</strong></td>
                                    <td>${item.jabatan}</td>
                                    <td class="text-center">
                                        <span class="badge ${badgeClass} text-uppercase px-2 py-1">${statusText}</span>
                                    </td>
                                    <td>${note}</td>
                                </tr>
                            `);
                        });
                    }
                },
                error: function() {
                    $('#pendingApprovalBadge').text('0');
                    $('#pendingApprovalBody').html(`
                        <tr>
                            <td colspan="8" class="text-center py-4 text-danger">Gagal memuat data pending approval.</td>
                        </tr>
                    `);
                }
            });
        }

        // Search filter handler
        $('#searchReport').on('keyup', function() {
            const search = $(this).val().toLowerCase();
            $('#tableReportList tbody tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                if ($(this).find('td').length > 1) {
                    if (rowText.includes(search)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                }
            });
        });

        // View detail report
        window.viewDetailReport = function(id) {
            $.ajax({
                url: "{{ route('wcp.stock_opname.report.detail', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const sum = res.summary;
                        const uom = sum.barang ? sum.barang.uom : '';

                        $('#detailMid').text(sum.barang ? sum.barang.mid : '-');
                        $('#detailNamaBarang').text(sum.barang ? sum.barang.nama_barang : '-');
                        $('#detailQtySistem').text(sum.qty_sistem.toLocaleString('id-ID') + ' ' + uom);

                        let detailRowsHtml = '';
                        if (res.details && res.details.length > 0) {
                            res.details.forEach((det, idx) => {
                                const inputTime = det.created_at ? det.created_at.substring(11,
                                    16) : '-';
                                detailRowsHtml += `
                                    <tr>
                                        <td class="text-center fw-semibold">${idx + 1}</td>
                                        <td class="text-center">${inputTime}</td>
                                        <td class="text-end">${det.qty_full.toLocaleString('id-ID')}</td>
                                        <td class="text-end">${det.qty_receh.toLocaleString('id-ID')}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            detailRowsHtml += `
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-2">
                                        <em>Tidak ada detail input fisik</em>
                                    </td>
                                </tr>
                            `;
                        }
                        $('#detailInputsList').html(detailRowsHtml);

                        $('#detailQtyFisik').text(sum.qty_fisik.toLocaleString('id-ID') + ' ' + uom);
                        $('#detailSelisih').text(sum.selisih.toLocaleString('id-ID') + ' ' + uom);

                        let badge = '';
                        if (sum.status === 'lebih') {
                            badge = '<span class="badge bg-warning px-2 py-1">LEBIH</span>';
                        } else if (sum.status === 'kurang') {
                            badge = '<span class="badge bg-danger px-2 py-1">KURANG</span>';
                        } else {
                            badge = '<span class="badge bg-success px-2 py-1">MATCH</span>';
                        }
                        $('#detailStatus').html(badge);
                        $('#detailKeterangan').text(sum.keterangan ? sum.keterangan : '-');

                        const modalEl = document.getElementById('detailReportModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        toastr.error(res.message || 'Gagal memuat detail.');
                    }
                },
                error: function() {
                    toastr.error('Gagal mengambil data detail.');
                }
            });
        };

        // Open edit report modal
        window.editReportRow = function(id) {
            $.ajax({
                url: "{{ route('wcp.stock_opname.report.detail', ':id') }}"
                    .replace(':id', id),
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const sum = res.summary;

                        $('#editReportId').val(sum.id);
                        $('#editReportNama').val(sum.barang ? sum.barang
                            .nama_barang : '-');
                        $('#editKeterangan').val(sum.keterangan ? sum
                            .keterangan : '');

                        let editHtml = '';
                        if (res.details && res.details.length > 0) {
                            res.details.forEach((det, idx) => {
                                const inputTime = det.created_at ?
                                    det.created_at.substring(11,
                                        16) : '-';
                                editHtml += `
                                    <div class="mb-3 border border-info p-3 rounded report-detail-item bg-light" data-id="${det.id}">
                                        <input type="hidden" name="items[${idx}][id]" value="${det.id}">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <p class="mb-0 fw-semibold text-dark">
                                                Input ke-${idx + 1} <span class="text-muted fw-normal">(${inputTime})</span>
                                            </p>
                                            <span class="badge bg-info">Detail Qty</span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label small mb-1">Qty Full Pallet</label>
                                                <input type="number" class="form-control qty_full" name="items[${idx}][qty_full]" value="${det.qty_full}" min="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small mb-1">Qty Receh</label>
                                                <input type="number" class="form-control qty_receh" name="items[${idx}][qty_receh]" value="${det.qty_receh}" min="0" required>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="deleteReportDetail(${det.id}, this)">
                                            <i class="mdi mdi-delete"></i> Hapus
                                        </button>
                                    </div>
                                `;
                            });
                        } else {
                            editHtml +=
                                '<div class="alert alert-warning">Tidak ada item detail untuk diedit.</div>';
                        }
                        $('#editReportItemsList').html(editHtml);

                        const modalEl = document.getElementById(
                            'editReportModal');
                        const modal = bootstrap.Modal.getInstance(
                            modalEl) || new bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        toastr.error(res.message ||
                            'Gagal memuat data edit.');
                    }
                },
                error: function() {
                    toastr.error('Gagal mengambil data edit.');
                }
            });
        };

        // Live check for negative inputs in edit report modal
        $(document).on('input', '#editReportModal .qty_full, #editReportModal .qty_receh',
            function() {
                if ($(this).val() !== '' && parseInt($(this).val()) < 0) {
                    toastr.warning('Jumlah kuantitas tidak boleh negatif/minus!');
                    $(this).val('');
                }
            });

        // Handle edit form submit
        $('#formEditReport').on('submit', function(e) {
            e.preventDefault();
            const id = $('#editReportId').val();
            let hasNegative = false;
            let hasZeroBoth = false;

            $('#editReportItemsList .report-detail-item').each(function() {
                const qtyFullVal = parseInt($(this).find('.qty_full')
                    .val()) || 0;
                const qtyRecehVal = parseInt($(this).find('.qty_receh')
                    .val()) || 0;

                if (qtyFullVal < 0 || qtyRecehVal < 0) {
                    hasNegative = true;
                }

                if (qtyFullVal === 0 && qtyRecehVal === 0) {
                    hasZeroBoth = true;
                }
            });

            if (hasNegative) {
                toastr.warning('Jumlah kuantitas tidak boleh negatif/minus!');
                return;
            }

            if (hasZeroBoth) {
                toastr.warning(
                    'Kuantitas tidak boleh 0. Minimal salah satu harus terisi dengan nilai positif!'
                );
                return;
            }

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...'
            );

            $.ajax({
                url: "{{ route('wcp.stock_opname.report.update', ':id') }}"
                    .replace(':id', id),
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text(
                        'Simpan Perubahan');
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        const modalEl = document.getElementById(
                            'editReportModal');
                        const modal = bootstrap.Modal.getInstance(
                            modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                        loadReport();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text(
                        'Simpan Perubahan');
                    const err = xhr.responseJSON;
                    Swal.fire('Gagal', err.message ||
                        'Gagal memperbarui data.', 'error');
                }
            });
        });

        // Handle delete report row detail
        window.deleteReportDetail = function(detailId, buttonEl) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Hapus detail input fisik ini secara permanen?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('wcp.stock_opname.report.detail.delete', ':id') }}"
                            .replace(':id', detailId),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                toastr.success(res.message);
                                $(buttonEl).closest(
                                        '.report-detail-item')
                                    .remove();
                                loadReport();
                                if ($(
                                        '#editReportItemsList .report-detail-item')
                                    .length === 0) {
                                    const modalEl = document
                                        .getElementById(
                                            'editReportModal');
                                    const modal = bootstrap.Modal
                                        .getInstance(modalEl);
                                    if (modal) modal.hide();
                                }
                            } else {
                                Swal.fire('Gagal', res.message,
                                    'error');
                            }
                        },
                        error: function(xhr) {
                            const err = xhr.responseJSON;
                            Swal.fire('Gagal', err.message ||
                                'Gagal menghapus detail.',
                                'error');
                        }
                    });
                }
            });
        };

        // Handle delete report row
        window.deleteReportRow = function(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Item laporan ini akan dihapus dari data Stock Opname!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('wcp.stock_opname.report.delete', ':id') }}"
                            .replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                toastr.success(res.message);
                                loadReport();
                            } else {
                                Swal.fire('Gagal', res.message,
                                    'error');
                            }
                        },
                        error: function(xhr) {
                            const err = xhr.responseJSON;
                            Swal.fire('Gagal', err.message ||
                                'Gagal menghapus data.', 'error'
                            );
                        }
                    });
                }
            });
        };

        function checkApprovalStatus(sop, date) {
            const wrapper = $('#approvalWrapper');
            const btnSend = $('#btnSendApproval');

            if (!sop) {
                wrapper.html(`
                    <div class="alert alert-info rounded-3">
                        <i class="mdi mdi-information-outline me-2"></i>
                        <strong>Tidak ada Stock Opname untuk tanggal ${date}</strong>
                    </div>
                `);
                btnSend.addClass('d-none');
                return;
            }

            wrapper.data('so-id', sop.id);

            $.get("{{ route('wcp.stock_opname.approval.show', '') }}/" + sop.id, function(
                res) {
                const isCreator = sop && sop.user_id == {{ Auth::user()->id }};
                const status = res.approval_status;
                const statusSo = res.status_sop;
                const note = res.approval_note || '';
                const isApprover = res.is_approver;
                const isOperator = res.is_operator;
                const tracking = res.approver_tracking || [];

                let trackingHtml = '';
                if (tracking.length > 0) {
                    trackingHtml = `
                        <div class="mt-3 border-top pt-3">
                            <h6 class="fw-semibold mb-2">
                                <i class="mdi mdi-history text-primary me-2"></i>Riwayat Persetujuan
                            </h6>
                            <ul class="list-unstyled mb-0 pl-3">
                    `;
                    tracking.forEach(t => {
                        let icon =
                            '<i class="mdi mdi-timer-sand text-warning me-1"></i>';
                        let badgeClass = 'bg-warning';
                        let statusLabel = 'Menunggu';

                        if (t.status === 'approved') {
                            icon =
                                '<i class="mdi mdi-check-circle text-success me-1"></i>';
                            badgeClass = 'bg-success';
                            statusLabel = 'Disetujui';
                        } else if (t.status === 'rejected') {
                            icon =
                                '<i class="mdi mdi-close-circle text-danger me-1"></i>';
                            badgeClass = 'bg-danger';
                            statusLabel = 'Ditolak';
                        }

                        trackingHtml += `
                            <li class="mb-2">
                                ${icon} <strong>${t.nama}</strong> (${t.jabatan})
                                <span class="badge ${badgeClass} ms-2">${statusLabel}</span>
                                ${t.catatan ? `<br><small class="text-muted ms-4">Catatan: ${t.catatan}</small>` : ''}
                                ${t.action_at ? `<br><small class="text-muted ms-4">Pada: ${t.action_at}</small>` : ''}
                            </li>
                        `;
                    });
                    trackingHtml += `</ul></div>`;
                }

                let cardHtml = '';

                if (statusSo === 'draft') {
                    if (isCreator) {
                        btnSend.removeClass('d-none');
                    } else {
                        btnSend.addClass('d-none');
                    }
                    cardHtml = `
                        <div class="card shadow-sm border-0 border-start border-3 border-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0 me-3">
                                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3" style="padding: 10px;">
                                            <i class="mdi mdi-file-document-edit-outline"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 text-info fw-bold">Status SO: Draft</h5>
                                        <p class="text-muted mb-0 small">Sesi Stock Opname telah ditutup. Silakan pilih approver dan kirim persetujuan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else if (statusSo === 'pending') {
                    btnSend.addClass('d-none');
                    cardHtml = `
                        <div class="card shadow-sm border-0 border-start border-3 border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0 me-3">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-3" style="padding: 10px;">
                                            <i class="mdi mdi-clock-outline"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 text-warning fw-bold">Status SO: Menunggu Persetujuan</h5>
                                        <p class="text-muted mb-0 small">Menunggu approval dari tim verifikasi.</p>
                                    </div>
                                </div>
                                ${trackingHtml}
                            </div>
                        </div>
                    `;
                } else if (statusSo === 'approved') {
                    btnSend.addClass('d-none');
                    cardHtml = `
                        <div class="card shadow-sm border-0 border-start border-3 border-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0 me-3">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3" style="padding: 10px;">
                                            <i class="mdi mdi-check-decagram-outline"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 text-success fw-bold">Status SO: Disetujui</h5>
                                        <p class="text-muted mb-0 small">Stock Opname telah disetujui sepenuhnya oleh tim verifikasi.</p>
                                    </div>
                                </div>
                                ${trackingHtml}
                            </div>
                        </div>
                    `;
                } else if (statusSo === 'rejected') {
                    if (isCreator) {
                        btnSend.removeClass('d-none');
                    } else {
                        btnSend.addClass('d-none');
                    }
                    cardHtml = `
                        <div class="card shadow-sm border-0 border-start border-3 border-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0 me-3">
                                        <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-3" style="padding: 10px;">
                                            <i class="mdi mdi-close-octagon-outline"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 text-danger fw-bold">Status SO: Ditolak / Direvisi</h5>
                                        <p class="text-muted mb-0 small">Stock opname ditolak. Silakan periksa komentar di bawah, lakukan revisi, lalu kirim approval kembali.</p>
                                    </div>
                                </div>
                                ${trackingHtml}
                            </div>
                        </div>
                    `;
                }

                if (isApprover && status === 'pending') {
                    cardHtml += `
                        <div class="card shadow-sm border-0 mt-3 bg-light-subtle border">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="mdi mdi-check-all text-success me-2"></i>Persetujuan Stock Opname</h6>
                                <div class="mb-3">
                                    <label for="approvalNote" class="form-label fw-semibold">Catatan / Komentar (Wajib jika menolak)</label>
                                    <textarea id="approvalNote" class="form-control" rows="2" placeholder="Masukkan komentar..."></textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-success px-4" onclick="actionApproval('approved')">
                                        <i class="mdi mdi-check me-1"></i> Setujui (Approve)
                                    </button>
                                    <button class="btn btn-danger px-4" onclick="actionApproval('rejected')">
                                        <i class="mdi mdi-close me-1"></i> Tolak (Reject)
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }

                wrapper.html(cardHtml);
            });
        }

        function actionApproval(actionStatus) {
            const soId = $('#approvalWrapper').data('so-id');
            const note = $('#approvalNote').val().trim();

            if (actionStatus === 'rejected' && note === '') {
                Swal.fire('Peringatan', 'Catatan wajib diisi jika menolak data!',
                    'warning');
                return;
            }

            Swal.fire({
                title: actionStatus === 'approved' ? 'Setujui data ini?' : 'Tolak data ini?',
                text: actionStatus === 'approved' ?
                    'Data stock opname akan disetujui.' : 'Data akan ditolak.',
                icon: actionStatus === 'approved' ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonText: actionStatus === 'approved' ? 'Ya, Approve' : 'Ya, Reject',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        url: "{{ route('wcp.stock_opname.update.status-approval') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            so_id: soId,
                            status: actionStatus,
                            catatan: note
                        },
                        success: function(res) {
                            Swal.close();
                            toastr.success(res.message);
                            loadReport();
                        },
                        error: function(xhr) {
                            Swal.close();
                            Swal.fire('Error', xhr.responseJSON
                                ?.message ||
                                'Gagal memproses approval.', 'error'
                            );
                        }
                    });
                }
            });
        }

        function loadApproverOptions(callback) {
            const foremanSelect = $('#selectForeman');
            const supervisorSelect = $('#selectSupervisor');

            foremanSelect.html('<option value="">Memuat...</option>');
            supervisorSelect.html('<option value="">Memuat...</option>');

            $.ajax({
                url: "{{ route('wcp.stock_opname.getDataApproval') }}",
                type: 'GET',
                success: function(res) {
                    foremanSelect.html(
                        '<option value="">-- Pilih Foreman --</option>');
                    supervisorSelect.html(
                        '<option value="">-- Pilih Supervisor / Dept Head --</option>'
                    );

                    if (res.foreman && res.foreman.length > 0) {
                        res.foreman.forEach(function(item) {
                            foremanSelect.append(
                                `<option value="${item.id}">${item.nama_lengkap} (${item.username})</option>`
                            );
                        });
                    }
                    if (res.supervisors && res.supervisors.length > 0) {
                        res.supervisors.forEach(function(item) {
                            supervisorSelect.append(
                                `<option value="${item.id}">${item.nama_lengkap} (${item.username} - ${item.jabatan.toUpperCase()})</option>`
                            );
                        });
                    }
                    if (typeof callback === 'function') callback();
                }
            });
        }
    </script>
@endsection
