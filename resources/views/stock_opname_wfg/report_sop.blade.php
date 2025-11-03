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
                        <div class="@if (Auth::user()->jabatan != 'operator') col-md-4 @else col-md-4 @endif col-12">
                            {{-- <label for="filter_tanggal" class="form-label fw-semibold">Filter Tanggal</label> --}}
                            <div>
                                <label class="form-label" for="filter_tanggal">Tanggal</label>
                                <input type="date" id="filter_tanggal" class="form-control"
                                    value="{{ now()->toDateString() }}">
                            </div>
                        </div>

                        {{-- Filter Principal untuk non-operator --}}
                        @if (Auth::user()->jabatan != 'operator')
                            <div class="col-md-4 col-12">
                                <select id="principal_filter" class="form-select">
                                    @foreach ($principals as $p)
                                        <option value="{{ $p }}">{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Tombol Export --}}
                        <div class="@if (Auth::user()->jabatan != 'operator') col-md-4 @else col-md-4 @endif col-12">
                            <button type="button" id="btn_export" class="btn btn-soft-warning w-100">
                                <i class="mdi mdi-export me-2"></i> Export PDF
                            </button>
                        </div>

                        {{-- Tombol Send Approval (hanya untuk operator) --}}
                        @if (Auth::user()->jabatan == 'operator')
                            <div class="@if (Auth::user()->jabatan != 'operator') col-md-3 @else col-md-4 @endif col-12">
                                {{-- <label class="form-label d-block opacity-0">.</label> --}}
                                <button type="button" id="btn_approval" class="btn btn-soft-success w-100"
                                    style="display:none;">
                                    <i class="mdi mdi-send me-2"></i> Send Approval
                                </button>
                            </div>
                        @endif
                    </div>

                    <div id="approvalWrapper"></div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle shadow-sm rounded-3 text-nowrap" id="tableOpname">
                            <thead class="bg-soft-info text-dark border-bottom">
                                <tr>
                                    <th class="text-center" style="width: 70px;">ID</th>
                                    <th>Tanggal Opname</th>
                                    <th>MID Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Qty SAP</th>
                                    <th>Qty Fisik</th>
                                    <th>Selisih</th>
                                    <th>Keterangan</th>
                                    <th class="text-center" style="width: 130px;">Aksi</th>
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
                        <img src="{{ asset('assets/images/empty_state.png') }}" alt="Empty" style="width:150px;">
                        <p class="text-muted">Tidak ada data yang ditemukan</p>
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
            let principal = params.get('principal'); // ✅ pakai let, bukan const

            if (tanggal) {
                $('#filter_tanggal').val(tanggal).trigger('change');
            }

            if (principal) {
                $('#principal_filter').val(principal).trigger('change');
            } else {
                // kalau URL gak ada principal, ambil dari dropdown (biasanya kosong / semua)
                principal = $('#principal_filter').val() || '';
            }
            // end trigger filter

            loadReportData(principal);

            $(document).on('keyup change', '#filter_tanggal', function() {
                // loadReportData(principal);
                loadReportData($('#principal_filter').val() || '');
            });

            // principal filter
            $('#principal_filter').on('change', function() {
                const principal = $(this).val();
                loadReportData(principal);
                const tanggal = $('#filter_tanggal').val();
                checkApprovalStatus(null, tanggal);
            });

            function loadReportData(principal) {
                $('#loading_state').show();
                $('#empty_state').hide();
                $('#tableBody').html('');
                const tanggal = $('#filter_tanggal').val() || new Date().toISOString().slice(0,
                    10);

                $.ajax({
                    url: "{{ route('wfg.stock_opname.report.getData') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        tanggal: $('#filter_tanggal').val(),
                        principal: principal
                    },
                    success: function(response) {
                        $('#loading_state').hide();

                        checkApprovalStatus(response, tanggal);

                        if (!response.summaries || response.summaries.length === 0) {
                            $('#empty_state').show();
                        } else {
                            renderTable(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loading_state').hide();
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="6" class="text-center text-danger py-4">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Gagal memuat data: ${error}
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            function checkApprovalStatus(data = null, tanggal) {
                const wrapper = $('#approvalWrapper');

                // Jika data SOP tidak ada
                if (!data?.sop) {
                    wrapper.html(`
                        <div class="alert alert-info rounded-3 mt-3">
                            <i class="mdi mdi-information-outline me-2"></i>
                            <strong>Belum ada SOP untuk tanggal ${tanggal}</strong>
                        </div>
                    `);
                    $('#btn_approval').hide();
                    return;
                }

                // SOP ada
                $('#filter_tanggal').data('sop-id', data.sop.id);
                const sopId = $('#filter_tanggal').data('sop-id');
                const tanggalFilter = tanggal;
                const principalFilter = $('#principal_filter').val();

                $.get("{{ route('wfg.stock_opname.approval.show', '') }}/" + sopId, {
                    tanggal: tanggalFilter,
                    principal: principalFilter
                }, function(res) {
                    const status = res.approval_status;
                    const note = res.approval_note || '';
                    const isApprover = res.is_approver || false;
                    const isOperator = @json(Auth::user()->jabatan === 'operator');

                    if (!status) {
                        wrapper.html('');
                        if (isOperator) $('#btn_approval').hide();
                        return;
                    }

                    // ===================== OPERATOR =====================
                    if (isOperator) {
                        const btn = $('#btn_approval');
                        btn.show();

                        // 🔹 Buat trackingHtml sekali di awal
                        let trackingHtml = '';
                        if (res.approver_tracking && res.approver_tracking.length > 0) {
                            trackingHtml = `
                                <div class="mt-3">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="mdi mdi-account-check-outline text-primary me-2"></i>
                                        Status Approval
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                            `;

                            res.approver_tracking.forEach(a => {
                                const s = a.status?.toLowerCase() || '';
                                let icon = '<i class="mdi mdi-timer-sand text-warning me-1"></i>';
                                let badgeClass = 'warning';
                                let statusLabel = s;

                                if (s === 'approved') {
                                    icon = '<i class="mdi mdi-check-circle text-success me-1"></i>';
                                    badgeClass = 'success';
                                } else if (s === 'rejected') {
                                    icon = '<i class="mdi mdi-close-circle text-danger me-1"></i>';
                                    badgeClass = 'danger';
                                } else if (s === 'read') {
                                    icon = '<i class="mdi mdi-eye text-info me-1"></i>';
                                    badgeClass = 'info';
                                    statusLabel = 'Read';
                                }

                                trackingHtml += `
                                    <li class="mb-2">
                                        ${icon}
                                        <strong>${a.nama}</strong> <span class="text-muted">(${a.jabatan})</span>
                                        <span class="badge bg-${badgeClass} ms-2 text-uppercase">${statusLabel}</span>
                                        ${a.catatan ? `<br><small class="text-muted ms-4">Catatan: ${a.catatan}</small>` : ''}
                                    </li>
                                `;
                            });

                            trackingHtml += '</ul></div>';
                        }

                        // 🔸 Status draft
                        if (status === 'draft') {
                            btn.removeClass('btn-secondary btn-soft-success')
                                .addClass('btn-soft-success')
                                .prop('disabled', false)
                                .html('<i class="mdi mdi-send-outline me-2"></i> Send Approval');

                            wrapper.html('');
                        }

                        // 🔸 Status pending / read
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

                        // 🔸 Status rejected
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
                                    Persetujuan SOP
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

                        // Jika user bukan approver tapi ingin lihat status saja
                        else if (status === 'approved' || status === 'rejected') {
                            const labelHtml = `
                                <div class="alert ${status === 'approved' ? 'alert-success' : 'alert-danger'} rounded-3 mt-3">
                                    <i class="mdi ${status === 'approved' ? 'mdi-check-decagram-outline' : 'mdi-close-octagon-outline'} me-2"></i>
                                    <strong>${status === 'approved' ? 'Sudah Disetujui' : 'Ditolak'}</strong>
                                    ${note ? `<br><small class="text-muted">Catatan: ${note}</small>` : ''}
                                </div>
                            `;
                            wrapper.html(labelHtml);
                        }

                        // Jika user bukan approver & status masih pending → hanya lihat alert
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

                    // Tentukan status
                    let statusClass, statusIcon, statusText;
                    switch (summary.status.toLowerCase()) {
                        case 'lebih':
                            statusClass = 'warning';
                            statusText = 'Lebih';
                            break;
                        case 'kurang':
                            statusClass = 'danger';
                            statusText = 'Kurang';
                            break;
                        default:
                            statusClass = 'success';
                            statusText = 'Sesuai';
                            break;
                    }

                    const statusBadge = `
                        <span class="badge badge-soft-${statusClass} fs-6" title="${statusText}">${formatNumber(selisih)}</span>
                    `;

                    const isOperator = @json(Auth::user()->jabatan === 'operator');
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
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-start">${keterangan}</td>
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

            window.showDetail = function(summaryId) {
                $('#modalContent').html(
                    '<p class="text-center py-4"><i class="mdi mdi-loading mdi-spin"></i> Memuat data...</p>'
                );
                const modalEl = document.getElementById('detailModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                const tanggal = $('#filter_tanggal').val();
                $.ajax({
                    // url: `{{ url('api/wfg/sop/report/getData') }}`,
                    url: "{{ route('wfg.stock_opname.report.getData') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        tanggal: tanggal,
                        principal: principal
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
                                    <td width="120">ID Data</td>
                                    <td><strong>${data.summaries[0].id}</strong></td>
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
                                    <th class="text-end">Full Box</th>
                                    <th class="text-end">Receh (pcs)</th>
                                    <th class="text-end">Total (pcs)</th>
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
                        statusClass = 'warning';
                        statusIcon = 'arrow-up-bold-circle';
                        statusText = 'Selisih Lebih';
                    } else {
                        statusClass = 'success';
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

                    html += `
                        <tr>
                            <td><strong>${item.barang.mid_barang}</strong></td>
                            <td>${item.barang.nama_barang}</td>
                            <td class="text-end">${formatNumber(item.qty_full)}</td>
                            <td class="text-end">${formatNumber(item.qty_receh)}</td> 
                            <td class="text-end"><strong>${formatNumber(total)}</strong></td>
                        </tr>
                    `;
                });
                return html;
            }

            function formatDate(dateString) {
                const date = new Date(dateString);
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

                // console.log(tanggal);
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
                    text: 'Mohon tunggu, sedang mengekspor data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    let url = `{{ route('wfg.stock_opname.export') }}?tanggal=${tanggal}`;
                    if (principal) {
                        url += `&principal=${encodeURIComponent(principal)}`;
                    }

                    // 🔹 Cek dulu response-nya
                    const response = await fetch(url, {
                        method: 'GET'
                    });

                    if (!response.ok) {
                        const data = await response.json().catch(() => ({}));
                        Swal.close();

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengekspor',
                            text: data.message ||
                                `Terjadi kesalahan (Status ${response.status}).`,
                        });
                        return;
                    }

                    // 🔹 Jika response ok, buka tab PDF
                    Swal.close();
                    window.open(url, '_blank');
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
                        data.foremen.forEach(f => {
                            html += `
                                <option value="${f.id}">${f.username}</option>`;
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
                            html += `
                                <option value="${s.id}">${s.username}</option>`;
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
                        if (res.status === 'success') {
                            Swal.fire('Sukses', res.message, 'success');
                            $('#approvalModal').modal('hide');

                            loadReportData(principal);
                        }

                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan',
                            'error');
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
                            title: 'Memproses...',
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
                                loadReportData(principal);
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

                $.ajax({
                    url: "{{ route('wfg.stock_opname.edit.update') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        items: items,
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
                        loadReportData(principal);
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
            $.ajax({
                url: `{{ url('api/wfg/sop/detail/edit') }}/` + barangId,
                type: 'GET',
                data: {
                    tanggal: tanggal
                },
                success: function(res) {
                    if (res.status === 'success') {
                        const items = res.data; // array
                        let html = '';

                        items.forEach(item => {
                            // Safety check untuk tanggal
                            const updatedAt = item.updated_at ? item.updated_at.replace(' ', 'T') :
                                new Date();
                            const dateObj = new Date(updatedAt);
                            const options = {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            };
                            const formattedDate = dateObj.toLocaleString('id-ID', options);

                            // Format angka tanpa .00
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
    </script>
@endsection
