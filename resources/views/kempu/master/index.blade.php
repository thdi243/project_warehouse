@extends('layouts.app')

@section('title', '| Master Kempu')

@section('styles')
    <style>
        .badge-status-active {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-status-in_use {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-status-maintenance {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-status-damaged,
        .badge-status-scrap {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .qr-label-card {
            border: 2px solid #334155;
            border-radius: 8px;
            padding: 15px;
            background: #ffffff;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .qr-label-header {
            border-bottom: 2px solid #334155;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .sortable-th {
            cursor: pointer;
            user-select: none;
        }

        .sortable-th:hover {
            background-color: #e2e8f0;
        }

        .sortable-th i {
            font-size: 11px;
            margin-left: 4px;
            color: #94a3b8;
        }

        .sortable-th.active i {
            color: #2563eb;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Breadcrumb & Header -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Master Data Kempu (IBC Tank)</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item">Kempu</li>
                                <li class="breadcrumb-item active">Master Kempu</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card shadow-sm border-0">
                <div
                    class="card-header bg-transparent border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-3">
                    <div>
                        <h5 class="card-title fw-bold mb-1">Data Master Kempu & GR Kempu </h5>
                        <p class="text-muted small mb-0">Kelola master wadah kempu dan cetak label QR Code</p>
                    </div>

                    <div class="d-flex gap-2 align-items-center">
                        <!-- Upload Button -->
                        <button type="button" class="btn btn-outline-success btn-sm" id="btnUpload">
                            <i class="ri-upload-cloud-2-line align-bottom me-1"></i> Upload Data
                        </button>

                        <!-- Add Button -->
                        <button type="button" class="btn btn-primary btn-sm" id="btnTambah">
                            <i class="ri-add-line align-bottom me-1"></i> Tambah Kempu
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Controls Bar: Filters, Print, Per Page & Live Search -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                        <!-- Left Controls: Status Filter & Print Actions -->
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <!-- Status Filter -->
                            <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                                <option value="all">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="scrap">Scrap</option>
                                <option value="trashed">Nonaktif (Deleted)</option>
                            </select>

                            <!-- Bulk Print Button -->
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnBulkPrint" disabled>
                                <i class="ri-printer-line align-bottom me-1"></i> Cetak Terpilih (<span
                                    id="selectedCount">0</span>)
                            </button>

                            <!-- Print All Button -->
                            <button type="button" class="btn btn-sm btn-outline-info" id="btnPrintAll">
                                <i class="ri-printer-fill align-bottom me-1"></i> Cetak Semua QR
                            </button>
                        </div>

                        <!-- Right Controls: Per Page & Live Search (Always inline) -->
                        <div class="d-flex align-items-center gap-2 flex-nowrap">
                            <div class="d-flex align-items-center gap-1 text-nowrap">
                                <label class="text-muted small mb-0">Tampilkan:</label>
                                <select id="perPage" class="form-select form-select-sm" style="width: 70px;">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text bg-light"><i class="ri-search-line"></i></span>
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Cari ID, RFID, tanggal...">
                                <button class="btn btn-outline-secondary" type="button" id="btnClearSearch"
                                    title="Reset Pencarian" style="display: none;">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle table-nowrap mb-0" id="tableKempu"
                            style="width: 100%;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll">
                                        </div>
                                    </th>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th class="sortable-th" data-sort="id_kempu">
                                        ID Kempu <i class="ri-arrow-up-down-line"></i>
                                    </th>
                                    <th class="sortable-th" data-sort="no_spb">
                                        No SPB <i class="ri-arrow-up-down-line"></i>
                                    </th>
                                    <th class="sortable-th" data-sort="gr_date">
                                        Tanggal GR <i class="ri-arrow-up-down-line"></i>
                                    </th>
                                    <th class="sortable-th" data-sort="rfid">
                                        RFID <i class="ri-arrow-up-down-line"></i>
                                    </th>
                                    <th class="sortable-th text-center" data-sort="status">
                                        Status <i class="ri-arrow-up-down-line"></i>
                                    </th>
                                    <th class="sortable-th text-center" data-sort="print_count">
                                        Print <i class="ri-arrow-up-down-line"></i>
                                    </th>
                                    <th data-sort="keterangan">
                                        Keterangan
                                    </th>
                                    <th class="text-center" style="width: 130px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                        </div>
                                        <span>Memuat data...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="row align-items-center justify-content-between mt-3 g-2">
                        <div class="col-12 col-sm-auto text-muted small text-center text-sm-start" id="paginationInfo">
                            Menampilkan 0 sampai 0 dari 0 data
                        </div>
                        <div class="col-12 col-sm-auto d-flex justify-content-center">
                            <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL FORM CREATE / EDIT -->
    <div class="modal fade" id="modalKempu" tabindex="-1" aria-labelledby="modalKempuLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="formKempu" class="needs-validation" novalidate>
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="modalKempuLabel">Tambah Master Kempu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <input type="hidden" id="kempu_id" name="id">

                        <div class="row g-3">
                            <!-- Mode Pendaftaran: Single / Incoming Banyak (hanya tampil saat Add) -->
                            <div class="col-12" id="boxInputMode">
                                <label class="form-label fw-semibold text-muted small text-uppercase mb-2">
                                    <i class="ri-settings-4-line me-1"></i> Mode Pendaftaran
                                </label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="input_mode" id="modeSingle" value="single" checked autocomplete="off">
                                        <label class="btn btn-outline-primary w-100 text-start py-2 px-3 h-100" for="modeSingle">
                                            <div class="fw-bold"><i class="ri-add-circle-line me-1"></i> Input Tunggal (1 Kempu)</div>
                                            <div class="small text-muted" style="font-size: 11px;">Input 1 unit kempu (bisa custom ID / RFID)</div>
                                        </label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="input_mode" id="modeBulk" value="bulk" autocomplete="off">
                                        <label class="btn btn-outline-primary w-100 text-start py-2 px-3 h-100" for="modeBulk">
                                            <div class="fw-bold"><i class="ri-stack-line me-1"></i> Incoming Banyak (Bulk)</div>
                                            <div class="small text-muted" style="font-size: 11px;">Auto-generate urutan ID kempu sekaligus</div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- No SPB (Surat Perintah Bongkar) -->
                            <div class="col-md-6">
                                <label for="no_spb" class="form-label fw-semibold">
                                    No SPB <span class="text-muted fw-normal">(Surat Perintah Bongkar)</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-file-text-line"></i></span>
                                    <input type="text" class="form-control" id="no_spb" name="no_spb"
                                        placeholder="Cth: SPB-2026/09/001">
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Nomor SPB untuk pelacakan incoming kempu.</small>
                            </div>

                            <!-- Tanggal GR -->
                            <div class="col-md-6">
                                <label for="gr_date" class="form-label fw-semibold">Tanggal GR <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                    <input type="date" class="form-control" id="gr_date" name="gr_date"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="invalid-feedback">Tanggal GR wajib dipilih.</div>
                            </div>

                            <!-- Fields untuk Bulk Mode -->
                            <div class="col-12" id="colBulkFields" style="display: none;">
                                <div class="card border border-primary-subtle bg-primary-subtle bg-opacity-10 mb-0">
                                    <div class="card-body p-3">
                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-5">
                                                <label for="bulk_qty" class="form-label fw-bold text-primary mb-1">
                                                    <i class="ri-stack-line me-1"></i> Jumlah Kempu (Qty) <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control fw-bold fs-16 text-center" id="bulk_qty" name="bulk_qty"
                                                        value="10" min="1" max="500" placeholder="10">
                                                    <span class="input-group-text">Unit</span>
                                                </div>
                                                <small class="text-muted" style="font-size: 11px;">Maksimal 500 unit sekali generate.</small>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="p-2 bg-white rounded border">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="small fw-bold text-muted">Preview Rentang ID:</span>
                                                        <span class="badge bg-primary-subtle text-primary" id="bulkQtyBadge">10 Unit</span>
                                                    </div>
                                                    <div class="font-monospace fw-bold text-dark text-truncate" id="bulkRangePreview">
                                                        <span id="bulkStartText">...</span> s/d <span id="bulkEndText">...</span>
                                                    </div>
                                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                                        <i class="ri-information-line me-1 text-primary"></i>ID dibuat otomatis berurutan sesuai Tanggal GR.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fields untuk Single Mode: ID Kempu & RFID -->
                            <div class="col-md-6" id="colSingleIdField">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="id_kempu" class="form-label fw-semibold mb-0">
                                        ID Kempu <span class="text-danger">*</span>
                                    </label>
                                    <div class="form-check form-check-inline me-0">
                                        <input class="form-check-input" type="checkbox" id="checkCustomId"
                                            style="cursor: pointer;">
                                        <label class="form-check-label small fw-medium text-primary" for="checkCustomId"
                                            style="cursor: pointer;">
                                            <i class="ri-edit-line me-1"></i>Input Custom / ID Lama
                                        </label>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-qr-code-line"></i></span>
                                    <input type="text" class="form-control bg-light font-monospace" id="id_kempu" name="id_kempu"
                                        placeholder="Format: YYMMDD0001 (cth: 2609220001)" readonly>
                                    <button type="button" class="btn btn-outline-secondary" id="btnRefreshId"
                                        title="Generate ulang ID">
                                        <i class="ri-refresh-line"></i>
                                    </button>
                                </div>
                                <small class="text-muted" id="idKempuHelp" style="font-size: 11px;">Format baru: [YYMMDD]
                                    + [Urutan 0001]. Otomatis terisi saat Tanggal GR dipilih.</small>
                                <div class="invalid-feedback">ID Kempu wajib diisi.</div>
                            </div>

                            <div class="col-md-6" id="colRfidField">
                                <label for="rfid" class="form-label fw-semibold">
                                    RFID <span class="text-muted fw-normal">(Opsional)</span>
                                </label>
                                <input type="text" class="form-control" id="rfid" name="rfid"
                                    placeholder="Masukkan Kode RFID kempu">
                            </div>

                            <!-- Status Operasional (hidden default active) -->
                            <div class="col-md-6 d-none">
                                <label for="status" class="form-label fw-semibold">
                                    Status Operasional <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" selected>Active (Siap Pakai)</option>
                                    <option value="maintenance">Maintenance (Perbaikan/Cuci)</option>
                                    <option value="scrap">Scrap (Rusak / Rongsok)</option>
                                </select>
                                <div class="invalid-feedback">Status operasional wajib dipilih.</div>
                            </div>

                            <!-- Keterangan -->
                            <div class="col-12">
                                <label for="keterangan" class="form-label fw-semibold">Keterangan / Catatan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2" placeholder="Catatan opsional..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSave">
                            <i class="ri-save-line me-1 align-bottom"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL UPLOAD EXCEL -->
    <div class="modal fade" id="modalUpload" tabindex="-1" aria-labelledby="modalUploadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formUpload" enctype="multipart/form-data">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="modalUploadLabel">
                            <i class="ri-upload-cloud-2-line text-success me-1"></i> Upload Master Kempu
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="alert alert-info d-flex align-items-center justify-content-between p-2 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-file-excel-2-line fs-20 text-success"></i>
                                <span class="small me-2">Gunakan template Excel resmi agar format sesuai</span>
                            </div>
                            <a href="{{ route('kempu.master.template') }}" class="btn btn-sm btn-success text-nowrap">
                                <i class="ri-download-2-line me-1"></i> Template
                            </a>
                        </div>

                        <div class="mb-3">
                            <label for="fileExcel" class="form-label fw-semibold">Pilih File Excel (.xlsx / .xls) <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="fileExcel" name="file"
                                accept=".xlsx, .xls" required>
                            <small class="text-muted">Maksimal ukuran file: 5 MB</small>
                        </div>

                        <div id="uploadErrorAlert" class="alert alert-danger p-2 small mt-2"
                            style="display: none; max-height: 180px; overflow-y: auto;"></div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success" id="btnSubmitUpload">
                            <i class="ri-upload-2-line me-1"></i> Mulai Upload
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PREVIEW QR CODE -->
    <div class="modal fade" id="modalQrPreview" tabindex="-1" aria-labelledby="modalQrPreviewLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalQrPreviewLabel">
                        <i class="ri-qr-code-line text-primary me-1"></i> Label QR Code Kempu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 d-flex flex-column align-items-center">
                    <!-- Preview Card Layout -->
                    <div class="qr-label-card text-center" id="printableCard"
                        style="border: 2px solid #000; border-radius: 12px; padding: 20px; max-width: 340px; width: 100%;">
                        <div class="mb-3">
                            <h3 class="mb-0 fw-black text-dark" id="previewIdKempu"
                                style="letter-spacing: 2px; font-weight: 900;">-</h3>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div id="qrcodeContainer" class="bg-white p-2 border border-2 border-dark rounded"
                                style="width: 140px; height: 140px; display:flex; align-items:center; justify-content:center;">
                            </div>
                        </div>
                        <div id="previewPrintMeta" class="w-100"></div>
                    </div>
                </div>

                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDownloadQr">
                        <i class="ri-download-2-line me-1"></i> Unduh Gambar QR
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary btn-sm" id="btnPrintSingle">
                            <i class="ri-printer-line me-1"></i> Cetak Label
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- QRCode.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        $(document).ready(function() {
            let allData = [];
            let filteredData = [];
            let currentPage = 1;
            let perPage = 25;
            let sortField = 'id_kempu';
            let sortDirection = 'desc';
            let currentKempuData = null;

            // Helper format date d-m-Y
            function formatDate(dStr) {
                if (!dStr) return '-';
                const d = new Date(dStr);
                if (isNaN(d.getTime())) return dStr;
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                return `${day}-${month}-${year}`;
            }

            // Helper format date & time d-m-Y H:i
            function formatDateTime(dStr) {
                if (!dStr) return '-';
                const d = new Date(dStr);
                if (isNaN(d.getTime())) return dStr;
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                const hours = String(d.getHours()).padStart(2, '0');
                const mins = String(d.getMinutes()).padStart(2, '0');
                return `${day}-${month}-${year} ${hours}:${mins}`;
            }

            // Fetch auto-generated ID Kempu from server (supports single & bulk preview)
            function fetchAutoId(grDate) {
                const dateVal = grDate || $('#gr_date').val() || '{{ date('Y-m-d') }}';
                const isBulk = $('#modeBulk').is(':checked');
                const qtyVal = parseInt($('#bulk_qty').val()) || 1;

                $.ajax({
                    url: "{{ route('kempu.master.generate-id') }}",
                    type: "GET",
                    data: {
                        gr_date: dateVal,
                        qty: isBulk ? qtyVal : 1
                    },
                    success: function(res) {
                        if (res.status) {
                            if (res.id_kempu) {
                                $('#id_kempu').val(res.id_kempu);
                            }
                            if (res.start_id && res.end_id) {
                                $('#bulkStartText').text(res.start_id);
                                $('#bulkEndText').text(res.end_id);
                                $('#bulkQtyBadge').text(`${res.count || qtyVal} Unit`);
                            }
                        }
                    }
                });
            }

            // Mode Selector: Single vs Bulk Incoming
            function setInputMode(mode) {
                if (mode === 'bulk') {
                    $('#modeBulk').prop('checked', true);
                    $('#colBulkFields').slideDown(150);
                    $('#colSingleIdField').hide();
                    $('#colRfidField').hide();
                    $('#id_kempu').prop('required', false);
                    $('#bulk_qty').prop('required', true);
                    fetchAutoId();
                } else {
                    $('#modeSingle').prop('checked', true);
                    $('#colBulkFields').slideUp(150);
                    $('#colSingleIdField').show();
                    $('#colRfidField').show();
                    $('#id_kempu').prop('required', true);
                    $('#bulk_qty').prop('required', false);
                    if (!$('#kempu_id').val() && !$('#checkCustomId').is(':checked')) {
                        fetchAutoId();
                    }
                }
            }

            $('input[name="input_mode"]').on('change', function() {
                setInputMode($(this).val());
            });

            $('#bulk_qty').on('input change', function() {
                let val = parseInt($(this).val()) || 1;
                if (val < 1) val = 1;
                if (val > 500) val = 500;
                fetchAutoId();
            });

            // Toggle mode Custom / Auto-generate ID Kempu
            function setCustomIdMode(isCustom) {
                if (isCustom) {
                    $('#checkCustomId').prop('checked', true);
                    $('#id_kempu').prop('readonly', false).removeClass('bg-light');
                    $('#id_kempu').attr('placeholder', 'Masukkan ID Kempu lama (cth: KMP-01, IBC-OLD)');
                    $('#btnRefreshId').prop('disabled', true).addClass('opacity-50');
                    $('#idKempuHelp').html(
                        '<span class="text-primary fw-semibold"><i class="ri-information-line me-1"></i>Mode Custom / ID Lama aktif: Masukkan ID kempu lama secara manual.</span>'
                    );
                } else {
                    $('#checkCustomId').prop('checked', false);
                    $('#id_kempu').prop('readonly', true).addClass('bg-light');
                    $('#id_kempu').attr('placeholder', 'Format: YYMMDD0001 (cth: 2609220001)');
                    $('#btnRefreshId').prop('disabled', false).removeClass('opacity-50');
                    $('#idKempuHelp').text(
                        'Format baru: [YYMMDD] + [Urutan 0001]. Otomatis terisi saat Tanggal GR dipilih.');
                    if (!$('#kempu_id').val()) {
                        fetchAutoId($('#gr_date').val());
                    }
                }
            }

            $('#checkCustomId').on('change', function() {
                const isCustom = $(this).is(':checked');
                setCustomIdMode(isCustom);
                if (isCustom && !$('#kempu_id').val()) {
                    $('#id_kempu').val('').focus();
                }
            });

            // When gr_date changes in Add mode, refresh id_kempu (jika bukan mode custom)
            $('#gr_date').on('change', function() {
                if (!$('#kempu_id').val() && !$('#checkCustomId').is(':checked')) {
                    fetchAutoId($(this).val());
                }
            });

            $('#btnRefreshId').on('click', function() {
                if (!$('#checkCustomId').is(':checked')) {
                    fetchAutoId($('#gr_date').val());
                }
            });

            // Load Data via AJAX
            function loadData() {
                $('#tableBody').html(`
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            <span>Memuat data...</span>
                        </td>
                    </tr>
                `);

                const status = $('#statusFilter').val();

                $.ajax({
                    url: "{{ route('kempu.master.data') }}",
                    type: "GET",
                    data: {
                        status: status
                    },
                    success: function(res) {
                        if (res.status) {
                            allData = res.data || [];
                            applyFilterAndRender();
                        } else {
                            $('#tableBody').html(
                                `<tr><td colspan="10" class="text-center text-danger py-4">Gagal memuat data</td></tr>`
                            );
                        }
                    },
                    error: function() {
                        $('#tableBody').html(
                            `<tr><td colspan="10" class="text-center text-danger py-4">Terjadi kesalahan koneksi server</td></tr>`
                        );
                    }
                });
            }

            // Filter, Sort, & Render Table
            function applyFilterAndRender() {
                const keyword = $('#searchInput').val().toLowerCase().trim();

                // 1. Search Filter
                if (keyword === '') {
                    filteredData = [...allData];
                    $('#btnClearSearch').hide();
                } else {
                    $('#btnClearSearch').show();
                    filteredData = allData.filter(item => {
                        const idKempu = (item.id_kempu || '').toLowerCase();
                        const noSpb = (item.no_spb || '').toLowerCase();
                        const rfid = (item.rfid || '').toLowerCase();
                        const grDate = (item.gr_date || '').toLowerCase();
                        const status = (item.status || '').toLowerCase();
                        const ket = (item.keterangan || '').toLowerCase();

                        return idKempu.includes(keyword) ||
                            noSpb.includes(keyword) ||
                            rfid.includes(keyword) ||
                            grDate.includes(keyword) ||
                            status.includes(keyword) ||
                            ket.includes(keyword);
                    });
                }

                // 2. Sorting
                filteredData.sort((a, b) => {
                    let valA = a[sortField] ?? '';
                    let valB = b[sortField] ?? '';

                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();

                    if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
                    if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
                    return 0;
                });

                // Reset page if out of bounds
                const totalRows = filteredData.length;
                const totalPages = Math.ceil(totalRows / perPage) || 1;
                if (currentPage > totalPages) {
                    currentPage = 1;
                }

                // 3. Slice for Current Page
                const startIndex = (currentPage - 1) * perPage;
                const endIndex = Math.min(startIndex + perPage, totalRows);
                const pageRows = filteredData.slice(startIndex, endIndex);

                // 4. Render Table Rows
                renderTableRows(pageRows, startIndex);

                // 5. Render Pagination Controls & Info
                renderPagination(totalRows, totalPages, startIndex, endIndex);

                // 6. Reset / Update Checkboxes
                updateSelectedCount();
            }

            function renderTableRows(rows, startIndex) {
                if (rows.length === 0) {
                    $('#tableBody').html(`
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="ri-inbox-line fs-24 d-block mb-1"></i>
                                Tidak ada data yang ditemukan
                            </td>
                        </tr>
                    `);
                    return;
                }

                let html = '';
                rows.forEach((row, index) => {
                    const rowNum = startIndex + index + 1;
                    const isTrashed = Boolean(row.deleted_at);

                    // Status Badge
                    let statusBadge = '';
                    if (isTrashed) {
                        statusBadge = '<span class="badge bg-danger">Nonaktif (Deleted)</span>';
                    } else if (row.status === 'scrap' || row.status === 'damaged' || row.current_status ===
                        'SCRAPPED') {
                        statusBadge = '<span class="badge badge-status-scrap">Scrap</span>';
                    } else {
                        const statusMap = {
                            'active': '<span class="badge badge-status-active">Active</span>',
                            'in_use': '<span class="badge badge-status-in_use">In Use</span>',
                            'maintenance': '<span class="badge badge-status-maintenance">Maintenance</span>',
                            'damaged': '<span class="badge badge-status-damaged">Scrap</span>',
                            'scrap': '<span class="badge badge-status-scrap">Scrap</span>'
                        };
                        statusBadge = statusMap[row.status] ||
                            `<span class="badge bg-secondary">${row.status || '-'}</span>`;
                    }

                    // Print Status Badge
                    let printBadge = '';
                    if (row.print_count && row.print_count > 0) {
                        const printerName = row.printed_by ? (row.printed_by.nama_lengkap || row.printed_by.username) : 'System';
                        const printTime = formatDateTime(row.printed_at);
                        printBadge = `
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2"
                                  title="Dicetak ${row.print_count}x • Terakhir oleh: ${printerName} (${printTime})"
                                  style="cursor: help;">
                                <i class="ri-printer-line me-1"></i>Ke-${row.print_count}
                            </span>
                        `;
                    } else {
                        printBadge = `
                            <span class="badge bg-light text-muted border py-1 px-2"
                                  title="Belum pernah dicetak"
                                  style="cursor: help;">
                                <i class="ri-printer-line me-1"></i>Belum
                            </span>
                        `;
                    }

                    // Action buttons
                    let actionButtons = '';
                    if (isTrashed) {
                        actionButtons = `
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-soft-success btn-restore" data-id="${row.id}" title="Pulihkan">
                                    <i class="ri-refresh-line"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-soft-danger btn-force-delete" data-id="${row.id}" title="Hapus Permanen">
                                    <i class="ri-delete-bin-fill"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        actionButtons = `
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-soft-dark btn-qr" data-id="${row.id}" title="Lihat & Cetak QR">
                                    <i class="ri-qr-code-line"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-soft-primary btn-edit" data-id="${row.id}" title="Edit Data">
                                    <i class="ri-pencil-fill"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-soft-danger btn-delete" data-id="${row.id}" title="Hapus">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        `;
                    }

                    const grDateFormatted = formatDate(row.gr_date);

                    html += `
                        <tr data-id="${row.id}">
                            <td class="text-center">
                                <div class="form-check">
                                    <input class="form-check-input row-checkbox" type="checkbox" value="${row.id}">
                                </div>
                            </td>
                            <td class="text-center">${rowNum}</td>
                            <td><span class="fw-bold text-dark font-monospace">${row.id_kempu || '-'}</span></td>
                            <td><span class="badge bg-light text-dark border font-monospace">${row.no_spb || '-'}</span></td>
                            <td><span class="text-dark">${grDateFormatted}</span></td>
                            <td>${row.rfid || '-'}</td>
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-center">${printBadge}</td>
                            <td><small class="text-muted text-truncate d-inline-block" style="max-width: 200px;">${row.keterangan || '-'}</small></td>
                            <td class="text-center">${actionButtons}</td>
                        </tr>
                    `;
                });

                $('#tableBody').html(html);
            }

            function renderPagination(totalRows, totalPages, startIndex, endIndex) {
                if (totalRows === 0) {
                    $('#paginationInfo').text('Menampilkan 0 sampai 0 dari 0 data');
                    $('#paginationControls').empty();
                    return;
                }

                $('#paginationInfo').text(
                    `Menampilkan ${startIndex + 1} sampai ${endIndex} dari ${totalRows} data`);

                let pagHtml = '';

                // Previous button
                pagHtml += `
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                    </li>
                `;

                // Page numbers
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                if (startPage > 1) {
                    pagHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                    if (startPage > 2) pagHtml +=
                        `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }

                for (let p = startPage; p <= endPage; p++) {
                    pagHtml += `
                        <li class="page-item ${p === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${p}">${p}</a>
                        </li>
                    `;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) pagHtml +=
                        `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    pagHtml +=
                        `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
                }

                // Next button
                pagHtml += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                    </li>
                `;

                $('#paginationControls').html(pagHtml);
            }

            // Pagination click handler
            $(document).on('click', '#paginationControls a.page-link', function(e) {
                e.preventDefault();
                const targetPage = parseInt($(this).data('page'));
                if (targetPage && targetPage !== currentPage) {
                    currentPage = targetPage;
                    applyFilterAndRender();
                }
            });

            // Per page change
            $('#perPage').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                applyFilterAndRender();
            });

            // Live Search
            let searchTimeout = null;
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    applyFilterAndRender();
                }, 200);
            });

            $('#btnClearSearch').on('click', function() {
                $('#searchInput').val('');
                currentPage = 1;
                applyFilterAndRender();
            });

            // Sorting Click
            $('.sortable-th').on('click', function() {
                const field = $(this).data('sort');
                if (sortField === field) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortField = field;
                    sortDirection = 'asc';
                }

                $('.sortable-th').removeClass('active');
                $('.sortable-th i').attr('class', 'ri-arrow-up-down-line');

                $(this).addClass('active');
                $(this).find('i').attr('class', sortDirection === 'asc' ? 'ri-arrow-up-line' :
                    'ri-arrow-down-line');

                applyFilterAndRender();
            });

            // Status Filter Change
            $('#statusFilter').on('change', function() {
                currentPage = 1;
                loadData();
            });

            // Checkbox logic
            $('#checkAll').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.row-checkbox').prop('checked', isChecked);
                updateSelectedCount();
            });

            $(document).on('change', '.row-checkbox', function() {
                const total = $('.row-checkbox').length;
                const checked = $('.row-checkbox:checked').length;
                $('#checkAll').prop('checked', total > 0 && total === checked);
                updateSelectedCount();
            });

            function updateSelectedCount() {
                const checked = $('.row-checkbox:checked').length;
                $('#selectedCount').text(checked);
                $('#btnBulkPrint').prop('disabled', checked === 0);
            }

            // Tombol Upload Modal
            $('#btnUpload').on('click', function() {
                $('#formUpload')[0].reset();
                $('#uploadErrorAlert').hide().empty();
                $('#modalUpload').modal('show');
            });

            // Submit Form Upload Excel
            $('#formUpload').on('submit', function(e) {
                e.preventDefault();

                const fileInput = $('#fileExcel')[0];
                if (!fileInput.files || fileInput.files.length === 0) {
                    Swal.fire('Perhatian', 'Pilih file Excel terlebih dahulu.', 'warning');
                    return;
                }

                const formData = new FormData(this);
                formData.append('_token', "{{ csrf_token() }}");

                $('#btnSubmitUpload').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah...');
                $('#uploadErrorAlert').hide().empty();

                $.ajax({
                    url: "{{ route('kempu.master.upload') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#btnSubmitUpload').prop('disabled', false).html(
                            '<i class="ri-upload-2-line me-1"></i> Mulai Upload');
                        if (res.status) {
                            $('#modalUpload').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                            });
                            loadData();
                        } else {
                            Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat upload.',
                                'error');
                        }
                    },
                    error: function(xhr) {
                        $('#btnSubmitUpload').prop('disabled', false).html(
                            '<i class="ri-upload-2-line me-1"></i> Mulai Upload');
                        const response = xhr.responseJSON;
                        const errorMsg = response?.message || 'Gagal memproses file Excel.';

                        if (response?.errors && Array.isArray(response.errors)) {
                            let errorList = '<ul class="mb-0 ps-3 mt-1">';
                            response.errors.forEach(err => {
                                errorList += `<li>${err}</li>`;
                            });
                            errorList += '</ul>';
                            $('#uploadErrorAlert').html(
                                `<strong>${errorMsg}</strong>${errorList}`).show();
                        } else if (response?.errors && typeof response.errors === 'object') {
                            let errorList = '<ul class="mb-0 ps-3 mt-1">';
                            Object.values(response.errors).forEach(err => {
                                errorList += `<li>${err}</li>`;
                            });
                            errorList += '</ul>';
                            $('#uploadErrorAlert').html(
                                `<strong>${errorMsg}</strong>${errorList}`).show();
                        } else {
                            $('#uploadErrorAlert').html(`<strong>${errorMsg}</strong>`).show();
                        }
                    }
                });
            });

            // Tombol Tambah
            $('#btnTambah').on('click', function() {
                $('#formKempu')[0].reset();
                $('#formKempu').removeClass('was-validated');
                $('#kempu_id').val('');
                $('#no_spb').val('');
                $('#gr_date').val('{{ date('Y-m-d') }}');
                $('#status').val('active');
                $('#modalKempuLabel').text('Tambah Master Kempu');

                // Tampilkan mode switcher dan reset ke mode single
                $('#boxInputMode').show();
                setInputMode('single');

                // Reset mode custom ID ke default (auto generate)
                setCustomIdMode(false);

                // Fetch auto generated ID for today
                fetchAutoId('{{ date('Y-m-d') }}');

                $('#modalKempu').modal('show');
            });

            // Tombol Edit
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                const rowData = allData.find(item => item.id == id);
                if (!rowData) return;

                $('#formKempu')[0].reset();
                $('#formKempu').removeClass('was-validated');

                $('#kempu_id').val(rowData.id);
                $('#id_kempu').val(rowData.id_kempu);
                $('#no_spb').val(rowData.no_spb || '');

                // Sembunyikan mode switcher saat edit, pastikan mode single
                $('#boxInputMode').hide();
                setInputMode('single');

                // Cek apakah data ini bertipe kempu lama
                const isOld = rowData.is_old_kempu || !(/^\d{10}$/.test(rowData.id_kempu || ''));
                setCustomIdMode(isOld);

                if (rowData.gr_date) {
                    const rawDate = rowData.gr_date.substr(0, 10);
                    $('#gr_date').val(rawDate);
                } else {
                    $('#gr_date').val('{{ date('Y-m-d') }}');
                }

                $('#rfid').val(rowData.rfid || '');
                $('#status').val(rowData.status || 'active');

                $('#keterangan').val(rowData.keterangan);

                $('#modalKempuLabel').text('Edit Master Kempu: ' + rowData.id_kempu);
                $('#modalKempu').modal('show');
            });

            // Simpan Data
            $('#formKempu').on('submit', function(e) {
                e.preventDefault();

                if (!this.checkValidity()) {
                    e.stopPropagation();
                    $(this).addClass('was-validated');
                    return;
                }

                const id = $('#kempu_id').val();
                const isEdit = id !== '';
                const isBulk = !isEdit && $('#modeBulk').is(':checked');

                const url = isEdit ? `{{ url('kempu/master/update') }}/${id}` :
                    `{{ route('kempu.master.store') }}`;
                const method = isEdit ? 'PUT' : 'POST';

                let formData = {
                    gr_date: $('#gr_date').val(),
                    no_spb: $('#no_spb').val().trim().toUpperCase(),
                    status: $('#status').val() || 'active',
                    keterangan: $('#keterangan').val().trim(),
                    _token: "{{ csrf_token() }}"
                };

                if (isBulk) {
                    formData.is_bulk = 1;
                    formData.qty = parseInt($('#bulk_qty').val()) || 1;
                } else {
                    formData.id_kempu = $('#id_kempu').val().trim().toUpperCase();
                    formData.rfid = $('#rfid').val().trim().toUpperCase();
                }

                $('#btnSave').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(res) {
                        $('#btnSave').prop('disabled', false).html(
                            '<i class="ri-save-line me-1 align-bottom"></i> Simpan');
                        if (res.status) {
                            $('#modalKempu').modal('hide');

                            if (isBulk && res.generated_ids && res.generated_ids.length > 0) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Generate Massal!',
                                    html: `
                                        <div class="text-start">
                                            <p class="mb-2">${res.message}</p>
                                            <div class="alert alert-info py-2 px-3 small mb-0">
                                                <strong>Rentang ID:</strong> ${res.start_id} s/d ${res.end_id}<br>
                                                <strong>Total:</strong> ${res.count} unit kempu
                                            </div>
                                        </div>
                                    `,
                                    showCancelButton: true,
                                    confirmButtonColor: '#0ab39c',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: '<i class="ri-printer-line me-1"></i> Cetak Label QR Sekarang',
                                    cancelButtonText: 'Tutup'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        const printUrl = `{{ route('kempu.master.print-qr') }}?ids=${res.generated_ids.join(',')}`;
                                        window.open(printUrl, '_blank');
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                            loadData();
                        } else {
                            Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                        }
                    },
                    error: function(xhr) {
                        $('#btnSave').prop('disabled', false).html(
                            '<i class="ri-save-line me-1 align-bottom"></i> Simpan');
                        const errorMsg = xhr.responseJSON?.message ||
                            'Terjadi kesalahan pada server.';
                        Swal.fire('Gagal', errorMsg, 'error');
                    }
                });
            });

            // Delete (Soft Delete)
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                const rowData = allData.find(item => item.id == id);

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menonaktifkan kempu ${rowData?.id_kempu || ''}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Nonaktifkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('kempu/master/delete') }}/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire('Berhasil', res.message, 'success');
                                    loadData();
                                } else {
                                    Swal.fire('Gagal', res.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Gagal', xhr.responseJSON?.message ||
                                    'Gagal menghapus data', 'error');
                            }
                        });
                    }
                });
            });

            // Restore
            $(document).on('click', '.btn-restore', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Pulihkan Data?',
                    text: 'Kempu ini akan kembali aktif di sistem.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Pulihkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('kempu/master/restore') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire('Berhasil', res.message, 'success');
                                    loadData();
                                }
                            }
                        });
                    }
                });
            });

            // Force Delete
            $(document).on('click', '.btn-force-delete', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Hapus Permanen?',
                    text: 'Data yang dihapus permanen tidak dapat dipulihkan!',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Hapus Selamanya',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('kempu/master/force-delete') }}/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire('Berhasil', res.message, 'success');
                                    loadData();
                                }
                            }
                        });
                    }
                });
            });

            // Generate & Preview QR Code Modal
            $(document).on('click', '.btn-qr', function() {
                const id = $(this).data('id');
                const rowData = allData.find(item => item.id == id);
                if (!rowData) return;

                currentKempuData = rowData;

                $('#previewIdKempu').text(rowData.id_kempu);

                // Generate QR Code
                $('#qrcodeContainer').empty();
                new QRCode(document.getElementById("qrcodeContainer"), {
                    text: rowData.id_kempu,
                    width: 120,
                    height: 120,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });

                // Display print info
                if (rowData.print_count && rowData.print_count > 0) {
                    const pName = rowData.printed_by ? (rowData.printed_by.nama_lengkap || rowData.printed_by.username) : 'System';
                    const pTime = formatDateTime(rowData.printed_at);
                    $('#previewPrintMeta').html(`
                        <div class="border-top pt-2 mt-2">
                            <span class="badge bg-success-subtle text-success py-1 px-2 mb-1">
                                <i class="ri-printer-line me-1"></i>Cetakan Ke-${rowData.print_count}
                            </span>
                            <div class="small text-muted">Dicetak oleh: <strong>${pName}</strong></div>
                            <div class="small text-muted">Waktu: ${pTime}</div>
                        </div>
                    `);
                } else {
                    $('#previewPrintMeta').html(`
                        <div class="border-top pt-2 mt-2">
                            <span class="badge bg-light text-muted border py-1 px-2">
                                <i class="ri-printer-line me-1"></i>Belum pernah dicetak
                            </span>
                        </div>
                    `);
                }

                $('#modalQrPreview').modal('show');
            });

            // Print Single from Modal
            $('#btnPrintSingle').on('click', function() {
                if (!currentKempuData) return;
                const printUrl = `{{ route('kempu.master.print-qr') }}?ids=${currentKempuData.id}`;
                window.open(printUrl, '_blank');
            });

            // Download QR as PNG
            $('#btnDownloadQr').on('click', function() {
                const img = $('#qrcodeContainer img').attr('src') || $('#qrcodeContainer canvas')[0]
                    ?.toDataURL("image/png");
                if (!img) return;

                const link = document.createElement('a');
                link.download = `QR_${currentKempuData.id_kempu}.png`;
                link.href = img;
                link.click();
            });

            // Bulk Print Selected
            $('#btnBulkPrint').on('click', function() {
                const selectedIds = [];
                $('.row-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    Swal.fire('Perhatian', 'Pilih minimal satu kempu untuk dicetak.', 'info');
                    return;
                }

                const printUrl = `{{ route('kempu.master.print-qr') }}?ids=${selectedIds.join(',')}`;
                window.open(printUrl, '_blank');
            });

            // Print All
            $('#btnPrintAll').on('click', function() {
                const printUrl = `{{ route('kempu.master.print-qr') }}?all=1`;
                window.open(printUrl, '_blank');
            });

            // Initial load
            loadData();
        });
    </script>
@endsection
