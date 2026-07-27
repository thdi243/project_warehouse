@extends('layouts.app')

@section('title', '| Report SO WRM')

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

        @media (min-width: 992px) {
            .col-md-2-4 {
                flex: 0 0 auto;
                width: 20%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0"><i class="mdi mdi-file-document-outline me-2"></i>Report Stock Opname WRM
                        </h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WRM</a></li>
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

                            <div class="col-lg-3 col-md-6">
                                <label for="jenis_so" class="form-label fw-semibold">Jenis SO</label>
                                <select id="jenis_so" name="jenis_so" class="form-select">
                                    <option value="cycle_count"
                                        {{ request('jenis_so') === 'cycle_count' ? 'selected' : '' }}>Cycle Count (Daily)
                                    </option>
                                    <option value="monthly" {{ request('jenis_so') === 'monthly' ? 'selected' : '' }}>
                                        Monthly SO</option>
                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label for="searchReport" class="form-label fw-semibold">Cari MID / No SPB</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="mdi mdi-magnify text-muted"></i>
                                    </span>
                                    <input type="text" id="searchReport" class="form-control"
                                        placeholder="Ketik MID atau SPB...">
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-12">
                                <div class="d-flex flex-wrap gap-2">
                                    {{-- <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-magnify me-1"></i>
                                        Tampilkan
                                    </button> --}}

                                    <button type="button" class="btn btn-outline-danger" id="btnExportPdf">
                                        <i class="mdi mdi-file-pdf-box me-1"></i>
                                        PDF
                                    </button>

                                    @can('permission', 'stock-opname-wrm-form')
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

                            <!-- Summary Cards -->
                            <div class="row g-2 mb-3" id="summaryCardsContainer">
                                <div class="col-md-2-4 col-sm-6 col-12">
                                    <div class="card bg-light border-0 shadow-none m-0 rounded-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs flex-shrink-0 me-3">
                                                    <span
                                                        class="avatar-title bg-primary-subtle text-white rounded-circle fs-4"
                                                        style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bx bx-cube-alt"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="text-muted mb-1 text-uppercase fw-semibold"
                                                        style="font-size: 10px; letter-spacing: 0.5px;">Total Item</h6>
                                                    <h4 class="mb-0 fw-bold" id="cardTotalItem">0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2-4 col-sm-6 col-12">
                                    <div class="card bg-light border-0 shadow-none m-0 rounded-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs flex-shrink-0 me-3">
                                                    <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4"
                                                        style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bx bx-receipt"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="text-muted mb-1 text-uppercase fw-semibold"
                                                        style="font-size: 10px; letter-spacing: 0.5px;">Total SPB</h6>
                                                    <h4 class="mb-0 fw-bold" id="cardTotalSpb">0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2-4 col-sm-6 col-12">
                                    <div class="card bg-light border-0 shadow-none m-0 rounded-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs flex-shrink-0 me-3">
                                                    <span
                                                        class="avatar-title bg-warning-subtle text-warning rounded-circle fs-4"
                                                        style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="mdi mdi-layers-outline"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="text-muted mb-1 text-uppercase fw-semibold"
                                                        style="font-size: 10px; letter-spacing: 0.5px;">Total Pallet</h6>
                                                    <h4 class="mb-0 fw-bold" id="cardTotalPallet">0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2-4 col-sm-6 col-12">
                                    <div class="card bg-light border-0 shadow-none m-0 rounded-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs flex-shrink-0 me-3">
                                                    <span
                                                        class="avatar-title bg-success-subtle text-success rounded-circle fs-4"
                                                        style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="mdi mdi-weight"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="text-muted mb-1 text-uppercase fw-semibold"
                                                        style="font-size: 10px; letter-spacing: 0.5px;">Total Qty Fisik
                                                    </h6>
                                                    <h4 class="mb-0 fw-bold" id="cardTotalQtyFisik">0 Kg</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2-4 col-sm-6 col-12">
                                    <div class="card bg-light border-0 shadow-none m-0 rounded-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs flex-shrink-0 me-3">
                                                    <span
                                                        class="avatar-title bg-danger-subtle text-danger rounded-circle fs-4"
                                                        style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="mdi mdi-calculator"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="text-muted mb-1 text-uppercase fw-semibold"
                                                        style="font-size: 10px; letter-spacing: 0.5px;">Total Selisih</h6>
                                                    <h4 class="mb-0 fw-bold" id="cardTotalSelisih">0 Kg</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0" id="tableReportList">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>MID</th>
                                            <th class="text-start">Nama Barang</th>
                                            <th>No SPB (Batch)</th>
                                            <th>Pallet</th>
                                            <th>Location</th>
                                            <th class="text-end">Qty Sistem (Kg)</th>
                                            <th class="text-end">Qty Fisik (Kg)</th>
                                            <th class="text-end">Selisih (Kg)</th>
                                            <th>Status</th>
                                            <th>Catatan / Keterangan</th>
                                            <th style="width: 120px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="12" class="text-center py-4 text-muted">
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
                            <label for="selectForeman" class="form-label fw-semibold">Pilih Foreman WRM</label>
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
                    <table class="table table-striped mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 200px;">MID</th>
                                <td id="detailMid" class="font-monospace"></td>
                            </tr>
                            <tr>
                                <th>Nama Barang</th>
                                <td id="detailNamaBarang"></td>
                            </tr>
                            <tr>
                                <th>No SPB (Batch)</th>
                                <td id="detailSpb" class="font-monospace"></td>
                            </tr>
                            <tr>
                                <th>Total Qty Sistem</th>
                                <td id="detailQtySistem" class="text-end font-monospace text-primary fw-semibold"></td>
                            </tr>
                            <tr>
                                <th>Total Qty Fisik</th>
                                <td id="detailQtyFisik" class="text-end font-monospace fw-bold text-success"></td>
                            </tr>
                            <tr>
                                <th>Total Selisih</th>
                                <td id="detailSelisih" class="text-end font-monospace fw-bold"></td>
                            </tr>
                        </tbody>
                    </table>

                    <h6 class="fw-bold mt-3 mb-2 border-bottom pb-2"><i class="mdi mdi-layers-outline me-1"></i>Detail per
                        Pallet</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>Pallet</th>
                                    <th>Location</th>
                                    <th class="text-end">Qty Sistem</th>
                                    <th class="text-end">Qty Fisik</th>
                                    <th class="text-end">Selisih</th>
                                    <th>Status</th>
                                    <th>Waktu Input</th>
                                </tr>
                            </thead>
                            <tbody id="detailPalletsList"></tbody>
                        </table>
                    </div>
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
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-pencil-outline me-2"></i>Edit Hasil Opname</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditReport">
                    @csrf
                    <input type="hidden" id="editReportId">
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Nama Barang</label>
                                <input type="text" id="editReportNama" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">No SPB (Batch)</label>
                                <input type="text" id="editReportSpb" class="form-control bg-light font-monospace"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Pallet</label>
                                <input type="text" id="editReportPallet" class="form-control bg-light font-monospace"
                                    readonly>
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

            $('#jenis_so').on('change', function() {
                const type = $(this).val();
                const dateInput = $('#tgl_opname');
                const approvalTab = $('#approval-tab').parent(); // select the li parent
                const btnExportPdf = $('#btnExportPdf');

                let val = dateInput.val();

                if (type === 'monthly') {
                    dateInput.attr('type', 'month');
                    if (val) {
                        if (val.length === 10) {
                            dateInput.val(val.substring(0, 7));
                        } else {
                            dateInput.val(val);
                        }
                    } else {
                        const today = new Date();
                        const month = String(today.getMonth() + 1).padStart(2, '0');
                        dateInput.val(`${today.getFullYear()}-${month}`);
                    }
                    approvalTab.removeClass('d-none');
                    btnExportPdf.removeClass('d-none');
                } else {
                    dateInput.attr('type', 'date');
                    const today = new Date();
                    const day = String(today.getDate()).padStart(2, '0');
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const todayStr = `${today.getFullYear()}-${month}-${day}`;
                    if (val && val.length === 10) {
                        dateInput.val(val);
                    } else {
                        dateInput.val(todayStr);
                    }
                    approvalTab.addClass('d-none');
                    btnExportPdf.addClass('d-none');

                    // If the active tab was approval tab, switch back to data tab
                    if ($('#approval-tab').hasClass('active')) {
                        var triggerEl = document.querySelector('#reportTabs button[id="data-tab"]');
                        if (triggerEl) {
                            var tab = bootstrap.Tab.getInstance(triggerEl) || new bootstrap.Tab(triggerEl);
                            tab.show();
                        }
                    }
                }
                loadReport();
            });

            // Trigger change on load to configure inputs and load report
            $('#jenis_so').trigger('change');

            $('#formFilterReport').on('change', function(e) {
                e.preventDefault();
                loadReport();
            });

            $('#btnExportPdf').on('click', function() {
                const date = $('#tgl_opname').val();

                if (!date) {
                    Swal.fire('Perhatian', 'Silakan pilih tanggal opname terlebih dahulu.', 'warning');
                    return;
                }

                const jenisSo = $('#jenis_so').val();
                const url = `{{ route('wrm.stock_opname.export') }}?tgl_opname=${date}&jenis_so=${jenisSo}`;

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
                    url: "{{ route('wrm.stock_opname.send-approval') }}",
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
            const jenisSo = $('#jenis_so').val();

            if (!date) return;

            tableBody.html(`
                <tr>
                    <td colspan="11" class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat laporan...
                    </td>
                </tr>
            `);

            $.ajax({
                url: "{{ route('wrm.stock_opname.report.getData') }}",
                type: "GET",
                data: {
                    tgl_opname: date,
                    jenis_so: jenisSo
                },
                success: function(res) {
                    tableBody.empty();

                    // Reload pending approvals count/state
                    loadPendingApprovals();

                    let totalItem = 0;
                    let totalSpb = 0;
                    let totalPallet = 0;
                    let totalQtyFisik = 0;
                    let totalSelisih = 0;

                    if (res.status === 'success' && res.data.length > 0) {
                        // Perhitungan summary
                        const uniqueItems = new Set(res.data.map(item => item.barang_id)).size;
                        const uniqueSpbs = new Set(res.data.map(item => item.no_spb).filter(spb => spb)).size;
                        const sumPallets = res.data.reduce((sum, item) => sum + (parseInt(item.pallet_count) ||
                            0), 0);
                        const sumQtyFisik = res.data.reduce((sum, item) => sum + (parseFloat(item.qty_fisik) ||
                            0), 0);
                        const sumSelisih = res.data.reduce((sum, item) => sum + (parseFloat(item.selisih) || 0),
                            0);

                        totalItem = uniqueItems;
                        totalSpb = uniqueSpbs;
                        totalPallet = sumPallets;
                        totalQtyFisik = sumQtyFisik;
                        totalSelisih = sumSelisih;

                        res.data.forEach((item, index) => {
                            const barangName = item.barang ? item.barang.nama_barang : 'N/A';
                            const barangMid = item.barang ? item.barang.mid : 'N/A';
                            const spb = item.no_spb ? item.no_spb : '-';
                            const qtySistem = item.qty_sistem.toLocaleString('id-ID');
                            const qtyFisik = item.qty_fisik.toLocaleString('id-ID');
                            const selisih = item.selisih.toLocaleString('id-ID');
                            const note = item.keterangan ? item.keterangan : '-';
                            const palletCount = item.pallet_count || 0;

                            let statusBadge = '';
                            if (item.status === 'lebih') {
                                statusBadge = '<span class="badge bg-warning px-2 py-1">LEBIH</span>';
                            } else if (item.status === 'kurang') {
                                statusBadge = '<span class="badge bg-danger px-2 py-1">KURANG</span>';
                            } else {
                                statusBadge = '<span class="badge bg-success px-2 py-1">MATCH</span>';
                            }


                            const jenisSo = res.jenis_so || 'cycle_count';
                            const isSpv = res.sop && res.sop.user_id == {{ Auth::user()->id }};
                            const canEdit =
                                {{ Auth::user()->jabatan !== 'operator' ? 'true' : 'false' }};

                            const actioButtons = canEdit ?
                                `<button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editReportRow(${item.id})" title="Edit">
                                    <i class="mdi mdi-pencil-outline"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteReportRow(${item.id})" title="Hapus">
                                    <i class="mdi mdi-trash-can-outline"></i>
                                </button>
                            ` : '';

                             tableBody.append(`  
                                <tr>
                                    <td class="text-center font-semibold">${index + 1}</td>
                                    <td class="text-center">${barangMid}</td>
                                    <td>${barangName}</td>
                                    <td class="text-center">${spb}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">${palletCount} pallet</span></td>
                                    <td class="text-start">${item.location_text ? item.location_text : '-'}</td>
                                    <td class="text-end">${qtySistem}</td>
                                    <td class="text-end">${qtyFisik}</td>
                                    <td class="text-end fw-bold">${selisih}</td>
                                    <td class="text-center">${statusBadge}</td>
                                    <td>${note}</td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="viewDetailReport(${item.id})" title="Detail">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                        ${actioButtons}
                                    </td>
                                </tr>
                            `);
                        });
                        checkApprovalStatus(res.sop, date);
                    } else {
                        tableBody.append(`
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
                                    Laporan SO tidak ditemukan untuk tanggal: <strong>${date}</strong>
                                </td>
                            </tr>
                        `);
                        checkApprovalStatus(null, date);
                    }

                    // Update UI Card
                    $('#cardTotalItem').text(totalItem.toLocaleString('id-ID'));
                    $('#cardTotalSpb').text(totalSpb.toLocaleString('id-ID'));
                    $('#cardTotalPallet').text(totalPallet.toLocaleString('id-ID'));
                    $('#cardTotalQtyFisik').text(totalQtyFisik.toLocaleString('id-ID') + ' Kg');

                    // Format selisih agar ada warna jika minus (-) atau plus (+)
                    const selisihText = (totalSelisih > 0 ? '+' : '') + totalSelisih.toLocaleString('id-ID') +
                        ' Kg';
                    const selisihEl = $('#cardTotalSelisih');
                    selisihEl.text(selisihText);
                    selisihEl.removeClass('text-danger text-success text-warning');
                    if (totalSelisih < 0) {
                        selisihEl.addClass('text-danger');
                    } else if (totalSelisih > 0) {
                        selisihEl.addClass('text-warning');
                    } else {
                        selisihEl.addClass('text-success');
                    }
                },
                error: function(xhr) {
                    tableBody.html(`
                        <tr>
                            <td colspan="11" class="text-center py-4 text-danger">Gagal memuat data laporan dari server.</td>
                        </tr>
                    `);
                    checkApprovalStatus(null, date);
                    loadPendingApprovals();

                    // Reset UI Card
                    $('#cardTotalItem').text('0');
                    $('#cardTotalSpb').text('0');
                    $('#cardTotalPallet').text('0');
                    $('#cardTotalQtyFisik').text('0 Kg');
                    $('#cardTotalSelisih').text('0 Kg').removeClass('text-danger text-success text-warning');
                }
            });
        }

        function loadPendingApprovals() {
            $.ajax({
                url: "{{ route('wrm.stock_opname.report.pending-approval') }}",
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
                url: "{{ route('wrm.stock_opname.report.detail', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const sum = res.summary;

                        $('#detailMid').text(sum.barang ? sum.barang.mid : '-');
                        $('#detailNamaBarang').text(sum.barang ? sum.barang.nama_barang : '-');
                        $('#detailSpb').text(res.no_spb ? res.no_spb : '-');

                        let totalSistem = 0,
                            totalFisik = 0,
                            totalSelisih = 0;
                        let palletRowsHtml = '';

                        if (res.pallets && res.pallets.length > 0) {
                            res.pallets.forEach(p => {
                                totalSistem += p.qty_sistem;
                                totalFisik += p.qty_fisik;
                                totalSelisih += p.selisih;

                                // Build history rows
                                let historyHtml = '<em class="text-muted small">-</em>';
                                if (p.details && p.details.length > 0) {
                                    historyHtml =
                                        '<table class="table table-xs table-sm mb-0"><tbody>';
                                    p.details.forEach((det, idx) => {
                                        const t = det.created_at ?
                                            new Date(det.created_at).toLocaleTimeString(
                                                'id-ID', {
                                                    timeZone: 'Asia/Jakarta',
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                    hour12: false
                                                }) :
                                            '-';
                                        const checked = det.qty_full === 1 ? 'Terhitung' :
                                            'Tidak';
                                        historyHtml +=
                                            `<tr><td class="font-monospace text-center">${t}</td></tr>`;
                                    });
                                    historyHtml += '</tbody></table>';
                                }

                                let sBadge = '';
                                if (p.status === 'lebih') sBadge =
                                    '<span class="badge bg-warning">LEBIH</span>';
                                else if (p.status === 'kurang') sBadge =
                                    '<span class="badge bg-danger">KURANG</span>';
                                else sBadge = '<span class="badge bg-success">MATCH</span>';

                                 palletRowsHtml += `
                                    <tr>
                                        <td class="font-monospace text-center">${p.pallet ?? '-'}</td>
                                        <td class="text-start">${p.location_text ?? '-'}</td>
                                        <td class="text-end">${p.qty_sistem.toLocaleString('id-ID')}</td>
                                        <td class="text-end">${p.qty_fisik.toLocaleString('id-ID')}</td>
                                        <td class="text-end fw-bold">${p.selisih.toLocaleString('id-ID')}</td>
                                        <td class="text-center">${sBadge}</td>
                                        <td>${historyHtml}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            palletRowsHtml =
                                '<tr><td colspan="7" class="text-center text-muted py-2"><em>Tidak ada data pallet</em></td></tr>';
                        }

                        $('#detailPalletsList').html(palletRowsHtml);
                        $('#detailQtySistem').text(totalSistem.toLocaleString('id-ID') + ' Kg');
                        $('#detailQtyFisik').text(totalFisik.toLocaleString('id-ID') + ' Kg');
                        const selisihColor = totalSelisih < 0 ? 'text-danger' : totalSelisih > 0 ?
                            'text-warning' : 'text-success';
                        $('#detailSelisih').removeClass('text-danger text-warning text-success').addClass(
                            selisihColor).text(totalSelisih.toLocaleString('id-ID') + ' Kg');

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
                url: "{{ route('wrm.stock_opname.report.detail', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const sum = res.summary;

                        $('#editReportId').val(sum.id);
                        $('#editReportNama').val(sum.barang ? sum.barang.nama_barang : '-');
                        $('#editReportSpb').val(sum.no_spb ? sum.no_spb : '-');
                        $('#editReportPallet').val(sum.pallet ? sum.pallet : '-');
                        $('#editKeterangan').val(sum.keterangan ? sum.keterangan : '');

                        let editHtml = '';
                        // Flatten semua detail dari semua pallet menjadi satu list
                        let allDetails = [];
                        if (res.pallets && res.pallets.length > 0) {
                            res.pallets.forEach(pallet => {
                                if (pallet.details && pallet.details.length > 0) {
                                    pallet.details.forEach(det => {
                                        allDetails.push({
                                            ...det,
                                            pallet: pallet.pallet
                                        });
                                    });
                                }
                            });
                        }

                        if (allDetails.length > 0) {
                            allDetails.forEach((det, idx) => {
                                const inputTime = det.created_at ?
                                    new Date(det.created_at).toLocaleTimeString('id-ID', {
                                        timeZone: 'Asia/Jakarta',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        hour12: false
                                    }) :
                                    '-';
                                const qtyFullVal = parseInt(det.qty_full);
                                editHtml += `
                                    <div class="mb-3 border border-info p-3 rounded report-detail-item bg-light" data-id="${det.id}">
                                        <input type="hidden" name="items[${idx}][id]" value="${det.id}">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <p class="mb-0 fw-semibold text-dark">
                                                Input ke-${idx + 1}
                                                ${det.pallet ? `<span class="badge bg-secondary ms-1">${det.pallet}</span>` : ''}
                                                <span class="text-muted fw-normal font-monospace">(${inputTime})</span>
                                            </p>
                                            <span class="badge bg-info">Detail Qty</span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-6 d-flex align-items-center">
                                                <div class="form-check form-switch mt-3">
                                                    <input type="hidden" name="items[${idx}][qty_full]" value="0">
                                                    <input class="form-check-input qty_full" type="checkbox"
                                                        name="items[${idx}][qty_full]"
                                                        value="1"
                                                        id="chk_qty_full_${idx}"
                                                        ${qtyFullVal === 1 ? 'checked' : ''}
                                                        style="width: 2.5em; height: 1.3em; cursor: pointer;">
                                                    <label class="form-check-label ms-2 fw-semibold" for="chk_qty_full_${idx}">
                                                        ${qtyFullVal === 1 ? '<span class="text-success">Terhitung</span>' : '<span class="text-danger">Tidak Terhitung</span>'}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small mb-1">Qty (Kg)</label>
                                                <input type="number" class="form-control qty_receh" name="items[${idx}][qty_receh]" value="${det.qty_receh ?? 0}" min="0" required>
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

                        const modalEl = document.getElementById('editReportModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        toastr.error(res.message || 'Gagal memuat data edit.');
                    }
                },
                error: function() {
                    toastr.error('Gagal mengambil data edit.');
                }
            });
        };

        // Live check for negative inputs in edit report modal
        $(document).on('input', '#editReportModal .qty_receh', function() {
            if ($(this).val() !== '' && parseInt($(this).val()) < 0) {
                toastr.warning('Jumlah kuantitas tidak boleh negatif/minus!');
                $(this).val('');
            }
        });

        // Toggle label Terhitung / Tidak Terhitung saat checkbox diubah
        $(document).on('change', '#editReportModal .qty_full[type="checkbox"]', function() {
            const label = $(this).siblings('label');
            if ($(this).is(':checked')) {
                label.html('<span class="text-success">Terhitung</span>');
            } else {
                label.html('<span class="text-danger">Tidak Terhitung</span>');
            }
        });

        // Handle edit form submit
        $('#formEditReport').on('submit', function(e) {
            e.preventDefault();
            const id = $('#editReportId').val();
            let hasNegative = false;

            $('#editReportItemsList .report-detail-item').each(function() {
                const qtyRecehVal = parseInt($(this).find('.qty_receh').val()) || 0;

                if (qtyRecehVal < 0) {
                    hasNegative = true;
                }
            });

            if (hasNegative) {
                toastr.warning('Jumlah kuantitas tidak boleh negatif/minus!');
                return;
            }

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

            $.ajax({
                url: "{{ route('wrm.stock_opname.report.update', ':id') }}".replace(':id', id),
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Simpan Perubahan');
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        const modalEl = document.getElementById('editReportModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(
                            modalEl);
                        modal.hide();
                        loadReport();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Simpan Perubahan');
                    const err = xhr.responseJSON;
                    Swal.fire('Gagal', err.message || 'Gagal memperbarui data.', 'error');
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
                        url: "{{ route('wrm.stock_opname.report.detail.delete', ':id') }}".replace(
                            ':id', detailId),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                toastr.success(res.message);
                                $(buttonEl).closest('.report-detail-item').remove();
                                loadReport();
                                if ($('#editReportItemsList .report-detail-item').length === 0) {
                                    const modalEl = document.getElementById('editReportModal');
                                    const modal = bootstrap.Modal.getInstance(modalEl);
                                    if (modal) modal.hide();
                                }
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            const err = xhr.responseJSON;
                            Swal.fire('Gagal', err.message || 'Gagal menghapus detail.', 'error');
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
                        url: "{{ route('wrm.stock_opname.report.delete', ':id') }}".replace(':id', id),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                toastr.success(res.message);
                                loadReport();
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            const err = xhr.responseJSON;
                            Swal.fire('Gagal', err.message || 'Gagal menghapus data.', 'error');
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

            $.get("{{ route('wrm.stock_opname.approval.show', '') }}/" + sop.id, function(res) {
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
                        let icon = '<i class="mdi mdi-timer-sand text-warning me-1"></i>';
                        let badgeClass = 'bg-warning';
                        let statusLabel = 'Menunggu';

                        if (t.status === 'approved') {
                            icon = '<i class="mdi mdi-check-circle text-success me-1"></i>';
                            badgeClass = 'bg-success';
                            statusLabel = 'Disetujui';
                        } else if (t.status === 'rejected') {
                            icon = '<i class="mdi mdi-close-circle text-danger me-1"></i>';
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
                                        <p class="text-muted mb-0 small">Menunggu approval dari Foreman dan Supervisor.</p>
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
                Swal.fire('Peringatan', 'Catatan wajib diisi jika menolak data!', 'warning');
                return;
            }

            Swal.fire({
                title: actionStatus === 'approved' ? 'Setujui data ini?' : 'Tolak data ini?',
                text: actionStatus === 'approved' ? 'Data stock opname akan disetujui.' : 'Data akan ditolak.',
                icon: actionStatus === 'approved' ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonText: actionStatus === 'approved' ? 'Ya, Approve' : 'Ya, Reject',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        url: "{{ route('wrm.stock_opname.update.status-approval') }}",
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
                            // fetchNotifications(false);
                        },
                        error: function(xhr) {
                            Swal.close();
                            Swal.fire('Error', xhr.responseJSON?.message || 'Gagal memproses approval.',
                                'error');
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
                url: "{{ route('wrm.stock_opname.getDataApproval') }}",
                type: 'GET',
                success: function(res) {
                    foremanSelect.html('<option value="">-- Pilih Foreman --</option>');
                    supervisorSelect.html('<option value="">-- Pilih Supervisor / Dept Head --</option>');

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
