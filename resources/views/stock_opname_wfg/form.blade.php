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
            background-color: #c03e3e;
            border-color: var(--primary-color);
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {

            font-size: 0.875rem;
            line-height: 1.5;
            color: #6c757d;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .sticky-header {
            position: sticky;
            top: 70px;
            z-index: 1020;
            background-color: #f8f9fa;
            box-shadow: 0 4px 6px -6px #666;
        }

        /* Perhalus transisi collapse */
        .collapse {
            transition: height 0.15s ease-in-out !important;
        }

        .collapsing {
            transition: height 0.05s ease-in-out !important;
        }

        #tableInputOpname td:first-child {
            position: sticky;
            left: 0;
            z-index: 102;
            background: #fff;
            color: var(--md-sys-color-on-surface, var(--bs-table-color, #212529));
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        #tableInputOpname thead th:first-child {
            position: sticky;
            left: 0;
            z-index: 202;
            background: #f3f6f9;
        }

        #tableInputOpname td:nth-child(2) {
            position: sticky;
            left: 35px;
            z-index: 101;
            background: #fff;
            color: var(--md-sys-color-on-surface, var(--bs-table-color, #212529));
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        #tableInputOpname thead th:nth-child(2) {
            position: sticky;
            left: 35px;
            z-index: 201;
            background: #f3f6f9;
        }

        #soInputTableContainer {
            overflow-x: auto;
        }

        /* --- Dark Mode Overrides (Memastikan background solid dan z-index sesuai) --- */

        html[data-layout-mode="dark"] #tableInputOpname thead th {
            background: #2f333a;
            color: #e9ecef;
            z-index: 200;
        }

        /* Dark Mode - Kolom Pertama (Header) */
        html[data-layout-mode="dark"] #tableInputOpname th:first-child {
            background: #2a2f34;
            color: #e9ecef;
            z-index: 202;
        }

        html[data-layout-mode="dark"] #tableInputOpname td:first-child {
            background: #272b30;
            color: #e9ecef;
            z-index: 102;
        }

        html[data-layout-mode="dark"] #tableInputOpname th:nth-child(2) {
            background: #2a2f34;
            color: #e9ecef;
            z-index: 201;
        }

        html[data-layout-mode="dark"] #tableInputOpname td:nth-child(2) {
            background: #272b30;
            color: #e9ecef;
            z-index: 101;
        }

        @media (max-width: 767.98px) {
            #soh-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                font-size: 10px;
                /* kecilkan font biar muat */
                white-space: nowrap;
                /* biar teks gak turun baris */
            }

            table th,
            table td {
                padding: 0.4rem 0.6rem;
                /* rapatkan sedikit */
            }

            /* Jika pakai tombol di kolom aksi, biar nggak tumpuk */
            table .btn {
                padding: 0.2rem 0.3rem;
                font-size: 10px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header mb-4" data-aos="fade-left">
                <div class="container-fluid">
                    <h1 class="h2 fw-bold mb-2 text-white">
                        <i class="mdi mdi-package-variant-closed me-2"></i>
                        Form Stock Opname WFG
                    </h1>
                    <p class="mb-0 opacity-90">Input stock opname agar data tetap actual</p>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body p-3">
                    <form id="formSopFinal" method="POST">
                        @csrf

                        <div class="p-4 mt-2 mb-4 rounded-3 border shadow-sm">
                            <div class="row g-3 align-items-end mb-3">

                                <!-- Kolom Tanggal Opname -->
                                <div class="@if (Auth::user()->jabatan != 'operator') col-md-3 @else col-md-3 @endif col-12">
                                    <label for="tgl_opname" class="form-label fw-semibold">Tanggal Opname</label>
                                    <input type="date" id="tgl_opname" name="tgl_opname" class="form-control"
                                        value="{{ now()->toDateString() }}" required disabled>
                                    <input type="hidden" name="existing_soh_ids" id="existingSohIds">
                                </div>

                                <!-- Filter Principal untuk non-operator -->
                                @if (Auth::user()->jabatan != 'operator')
                                    <div class="col-md-3 col-12">
                                        <label for="principal_filter" class="form-label fw-semibold">Filter
                                            Principal</label>
                                        <select id="principal_filter" class="form-select">
                                            {{-- <option value="" selected disabled>All principal</option> --}}
                                            @foreach ($principals as $p)
                                                <option value="{{ $p }}">{{ $p }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- Kolom Tombol Aksi -->
                                <div class="@if (Auth::user()->jabatan != 'operator') col-md-6 @else col-md-9 @endif col-12">
                                    <div class="d-flex flex-column flex-md-row gap-2 text-nowrap">
                                        <button type="submit" class="btn btn-success w-100" id="btnSaveFinal">
                                            <i class="mdi mdi-content-save-outline me-1"></i> Check & Submit
                                        </button>
                                        <button type="button" class="btn btn-info w-100" id="btnAddRow">
                                            <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah Item
                                        </button>
                                        <button type="button" class="btn btn-danger w-100" id="btnReset">
                                            <i class="mdi mdi-restart me-1"></i> Reset All
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-8 col-12">
                                    <label for="searchBarang" class="form-label fw-semibold">Cari Barang</label>
                                    <div class="input-group">
                                        <input type="text" id="searchBarang" class="form-control"
                                            placeholder="Ketik MID...">
                                        <button type="button" id="btnSearchBarang" class="btn btn-primary">
                                            <i class="mdi mdi-magnify"></i> Cari
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12 text-center">
                                    <div>
                                        <button type="button" id="btnStartOpname" class="btn btn-outline-primary w-100">
                                            <i class="mdi mdi-play-circle me-2"></i> Start Opname
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div id="principalTabsContainer" class="mb-3"></div>
                        <div class="table-responsive" id="soInputTableContainer">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit-->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data Temp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Data temp akan dimasukkan di sini -->
                </div>
                <div class="modal-footer">
                    <button type="button" id="saveEditBtn" class="btn btn-success">Simpan Semua</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Item Baru -->
    <div class="modal fade" id="modalAddItem" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-plus-circle-outline me-2"></i>Tambah Item Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="formAddItem" class="p-1">

                        <!-- ========== SECTION: Master Barang ========== -->
                        <div class="p-3 rounded-3 mb-3"
                            style="background: var(--vz-secondary-bg, var(--bs-secondary-bg-subtle, #f1f3f5));">
                            <h6 class="fw-bold mb-3 text-secondary">
                                <i class="mdi mdi-database-outline me-1"></i> Data Barang
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">MID Barang</label>
                                    <input type="number" class="form-control" name="mid_barang"
                                        placeholder="Masukkan MID">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nama Barang</label>
                                    <input type="text" class="form-control" name="nama_barang"
                                        placeholder="Masukkan Nama Barang">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Uom</label>
                                    <input type="text" class="form-control" name="uom"
                                        placeholder="Masukkan Uom">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Qty Box</label>
                                    <input type="number" class="form-control" name="qty_box" placeholder="100"
                                        min="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Principal</label>
                                    <input type="text" class="form-control" name="principal" placeholder="100"
                                        min="1">
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION: Data SOH ========== -->
                        <div class="p-3 rounded-3 mb-3"
                            style="background: var(--vz-danger-bg, var(--bs-danger-bg-subtle, #fff5f5));">
                            <h6 class="fw-bold mb-3 text-danger">
                                <i class="mdi mdi-database-lock-outline me-1"></i> Data SOH
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Unrest</label>
                                    <input type="number" class="form-control" name="unrest" placeholder="100"
                                        min="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">QI</label>
                                    <input type="number" class="form-control" name="qi" placeholder="100"
                                        min="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Blocked</label>
                                    <input type="number" class="form-control" name="blocked" placeholder="100"
                                        min="1">
                                </div>
                            </div>
                        </div>

                        <!-- ========== SECTION: Data SOP ========== -->
                        <div class="p-3 rounded-3 mb-2"
                            style="background: var(--vz-warning-bg, var(--bs-warning-bg-subtle, #fff9e6));">
                            <h6 class="fw-bold mb-3 text-warning">
                                <i class="mdi mdi-package-variant-closed me-1"></i> Data SOP
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Qty Full</label>
                                    <input type="number" class="form-control" name="qty_full" min="0"
                                        placeholder="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Qty Receh</label>
                                    <input type="number" class="form-control" name="qty_receh" min="0"
                                        placeholder="100">
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info" id="btnSaveNewItem">
                        <i class="mdi mdi-content-save-outline me-1"></i> Simpan Item
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $.get("url('/wfg/stock_opname/sop/status')", function(res) {
                const btn = $('#btnStartOpname');

                if (res.status === 'started') {
                    btn.prop('disabled', true)
                        .removeClass('btn-outline-success')
                        .addClass('btn-success')
                        .html('<i class="mdi mdi-loading mdi-spin me-2"></i> Opname...');
                    generatePrincipalTabs(true);
                } else if (res.status === 'finished') {
                    btn.prop('disabled', true)
                        .removeClass('btn-outline-success btn-success')
                        .addClass('btn-secondary')
                        .html('<i class="mdi mdi-check-circle-outline me-2"></i> Opname Done');

                    $('#formSopFinal button').prop('disabled', true).addClass('disabled');
                    $('#searchBarang').prop('disabled', true).addClass('disabled');
                    $('#btnSearchBarang').prop('disabled', true).addClass('disabled');

                    $('#soInputTableContainer').html(`
                        <div class="d-flex justify-content-center align-items-center py-2">
                            <div class="card border-0 shadow-sm text-center p-4" style="max-width: 700px;">
                                <div class="mb-3">
                                    <i class="mdi mdi-check-circle-outline text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h4 class="fw-semibold text-dark mb-2">Opname Selesai</h4>
                                <p class="text-muted mb-4">
                                    Silakan hubungi <strong>Foreman</strong> untuk tindakan lanjutan.
                                </p>
                            </div>
                        </div>
                    `);

                    generatePrincipalTabs(false);
                }
            });

            $('#btnStartOpname').on('click', function() {
                const $btn = $(this);

                // Disable tombol biar tidak diklik berkali-kali
                $btn.prop('disabled', true)
                    .removeClass('btn-outline-success')
                    .addClass('btn-success')
                    .html('<i class="mdi mdi-loading mdi-spin me-2"></i> Opname...');

                $.ajax({
                    url: '/wfg/stock_opname/sop/start',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.status) {
                            toastr.success(res.message);
                            generatePrincipalTabs(true);
                        } else {
                            toastr.warning(res.message);
                        }
                    },
                    error: function() {
                        toastr.error('Gagal memulai opname');
                        $btn.prop('disabled', false)
                            .removeClass('btn-success')
                            .addClass('btn-outline-success')
                            .html('<i class="mdi mdi-play-circle"></i> Start Opname');
                    }
                });
            });

            function loadBarangForOpname(page = 1, principal, search = '') {
                const container = $('#soInputTableContainer');

                container.html(`
                    <div class="d-flex flex-column align-items-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-primary fw-semibold">Mengambil data barang...</p>
                    </div>
                `);

                $.ajax({
                    // url: `{{ url('api/wfg/sop/getData') }}`,
                    url: "{{ route('wfg.stock_opname.getData') }}",
                    type: "GET",
                    data: {
                        page: page,
                        per_page: 25,
                        principal: principal,
                        search: search
                    },
                    success: function(response) {
                        const items = response.data;
                        const currentPage = response.current_page;
                        const lastPage = response.last_page;
                        const total = response.total;


                        if (items.length === 0) {
                            container.html(`
                                <div class="text-center py-5 text-muted">
                                    <h4 class="fw-light">Tidak ada barang untuk opname.</h4>
                                    <p>Pastikan data sudah tersedia.</p>
                                </div>
                            `);
                            return;
                        }

                        let tableHtml = `
                            <h5 class="mb-3 text-dark fw-bold mb-2">Daftar Barang untuk Opname</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0 text-nowrap" id="tableInputOpname" style="width: 100%;">
                                    <thead class="bg-light text-center align-middle">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th >MID (Qty Box)</th>
                                            <th style="min-width: 80px;">Qty Full</th>
                                            <th style="min-width: 80px;">Qty Receh</th>
                                            <th>Summary</th>
                                            <th style="min-width: 80px;">Catatan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        items.forEach((item, index) => {
                            // id sama soh_id sama saja
                            const id = item.id;
                            const barangId = item.barang_id;
                            const sohid = item.soh_id ?? '';
                            const qtyBox = item.qty_box ?? 1;

                            // Tentukan warna badge berdasarkan diff_status
                            let badgeColor = '';
                            let badge = '';
                            let badgeHtml = '';
                            let textColor = '';

                            if (item.diff_status === 'lebih') {
                                textColor = 'text-white';
                                badge = 'badge';
                                badgeColor = 'bg-warning';
                            } else if (item.diff_status === 'kurang') {
                                textColor = 'text-white';
                                badge = 'badge';
                                badgeColor = 'bg-danger';
                            } else if (item.diff_status === 'match') {
                                textColor = 'text-white';
                                badge = 'badge';
                                badgeColor = 'bg-success';
                            } else {
                                textColor = 'text-dark';
                            }

                            // Kalau ada diff_status, baru tampilkan badge
                            if (item.diff_status) {
                                badgeHtml =
                                    `<span class="badge rounded-circle ${badgeColor}" style="width: 12px; height: 12px; display: inline-block;"></span>`;
                            } else {
                                badgeHtml = ''; // tidak tampil apa pun
                            }

                            tableHtml += `
                                <tr class="soh-row"
                                    data-id="${id}"
                                    data-sohid="${sohid}"
                                    data-barangid="${barangId}"
                                    data-qtybox="${qtyBox}">
                                    
                                    <td class="text-center fw-semibold">${index + 1 + ((currentPage - 1) * response.per_page)}</td>
                                    <td>
                                        <strong class="${textColor} ${badge} ${badgeColor}">${item.mid_barang ?? 'N/A'}</strong><br>
                                        <small class="text-muted">${item.qty_box ?? 'N/A'}</small>
                                    </td>
                                    <td><input type="number" class="form-control qty_full text-end" min="0" placeholder="0"></td>
                                    <td><input type="number" class="form-control qty_receh text-end" min="0" placeholder="0"></td>
                                    <td class="text-end fw-bold summary-cell">${item.summary ?? '0'}</td>
                                    <td><input type="text" class="form-control keterangan" placeholder="Isi Catatan"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-success btn-save-temp"  title="Simpan">
                                            <i class="mdi mdi-content-save-outline"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-history" data-bs-toggle="collapse" data-bs-target="#history-${id}" title="History">
                                            <i class="mdi mdi-history"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-temp" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-reset-temp" title="Reset">
                                            <i class="mdi mdi-delete-outline"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="history-${id}">
                                    <td colspan="7" class="p-3 text-muted small text-start">
                                        <em>Belum ada riwayat input.</em>
                                    </td>
                                </tr>
                            `;
                        });

                        tableHtml += `</tbody></table></div>`;

                        // --- Pagination ---
                        tableHtml += `
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center flex-wrap">
                                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                                        <a class="page-link" href="#" data-page="${currentPage - 1}">
                                            <i class="mdi mdi-chevron-left"></i> Prev
                                        </a>
                                    </li>
                        `;

                        const maxVisible = 5; // tampil maksimal 5 halaman di tengah
                        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
                        let endPage = Math.min(lastPage, startPage + maxVisible - 1);
                        if (endPage - startPage < maxVisible - 1) {
                            startPage = Math.max(1, endPage - maxVisible + 1);
                        }

                        for (let i = startPage; i <= endPage; i++) {
                            tableHtml += `
                                <li class="page-item ${i === currentPage ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>
                            `;
                        }

                        tableHtml += `
                                    <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                                        <a class="page-link" href="#" data-page="${currentPage + 1}">
                                            Next <i class="mdi mdi-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                                <div class="text-center text-muted small mt-2">
                                    Menampilkan ${(currentPage - 1) * response.per_page + 1} - ${Math.min(currentPage * response.per_page, total)} dari ${total} barang
                                </div>
                            </nav>
                        `;

                        container.html(tableHtml);

                        loadAllTempData();

                        // --- Event pagination ---
                        container.find('.page-link').on('click', function(e) {
                            e.preventDefault();
                            const targetPage = $(this).data('page');
                            if (targetPage >= 1 && targetPage <= lastPage) {
                                loadBarangForOpname(targetPage, activePrincipal);
                            }
                        });

                        if (typeof AOS !== 'undefined') AOS.refresh();
                    },
                    error: function() {
                        container.html(`
                            <div class="text-center py-5 text-danger">
                                <h4 class="fw-bold">Gagal Memuat Data</h4>
                                <p>Terjadi kesalahan saat mengambil daftar barang.</p>
                            </div>
                        `);
                    }
                });
            }

            let activePrincipal = ''; // default

            function generatePrincipalTabs(shouldLoad = true) {
                if (!shouldLoad) {
                    $('#principalTabsContainer').empty();
                    return;
                }

                $.ajax({
                    url: "{{ route('wfg.stock_opname.principal-list') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.status !== 'success') return;

                        const principalsRaw = response.principals || [];
                        const isSMU = !!response.is_smu;

                        // normalize: jadi array string ['SMU', 'ABC']
                        const principals = principalsRaw.map(p => {
                            if (typeof p === 'string') return p;
                            if (p && typeof p === 'object' && p.principal) return p.principal;
                            return null;
                        }).filter(Boolean);

                        // kalau bukan SMU -> langsung load, ambil principal pertama kalau ada
                        if (!isSMU) {
                            const principal = principals[0] || '';
                            loadBarangForOpname(1, principal);
                            return;
                        }

                        // kalau SMU tapi principals kosong -> load tanpa filter
                        if (principals.length === 0) {
                            loadBarangForOpname(1, '');
                            return;
                        }

                        // build tabs dengan loop (lebih aman daripada nested template backticks)
                        let tabsHtml =
                            '<ul class="nav nav-pills nav-justified mb-3" id="principalTabs" role="tablist">';
                        principals.forEach((p, i) => {
                            const activeClass = i === 0 ? 'active' : '';
                            // tambahin escape sederhana untuk safety (hindari backtick issues)
                            const safeP = String(p).replace(/"/g, '&quot;');
                            tabsHtml += `
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link ${activeClass}" 
                                            data-principal="${safeP}" 
                                            type="button" role="tab">
                                        ${safeP}
                                    </button>
                                </li>
                            `;
                        });
                        tabsHtml += '</ul>';

                        $('#principalTabsContainer').html(tabsHtml);

                        // gunakan event delegation agar handler tetap bekerja walau re-render
                        $('#principalTabsContainer').off('click', '#principalTabs button');
                        $('#principalTabsContainer').on('click', '#principalTabs button', function() {
                            $('#principalTabs button').removeClass('active');
                            $(this).addClass('active');
                            activePrincipal = $(this).data('principal') || '';
                            console.log(activePrincipal);
                            loadBarangForOpname(1, activePrincipal);
                        });

                        // auto-load pertama
                        activePrincipal = principals[0] || '';
                        loadBarangForOpname(1, activePrincipal);
                    },
                    error: function() {
                        loadBarangForOpname(1, '');
                    }
                });
            }

            // Tombol search diklik
            let currentPrincipal = '';
            let currentSearch = '';

            $('#btnSearchBarang').on('click', function() {
                currentSearch = $('#searchBarang').val().trim();
                loadBarangForOpname(1, currentPrincipal, currentSearch);
            });

            // Tekan Enter di input
            $('#searchBarang').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    currentSearch = $(this).val().trim();
                    loadBarangForOpname(1, currentPrincipal, currentSearch);
                }
            });

            $('#principal_filter').on('change', function() {
                currentPrincipal = $(this).val();
                loadBarangForOpname(1, currentPrincipal,
                    currentSearch);
            });

            // Save temp
            $(document).on('click', '.btn-save-temp', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');
                const soh_id = row.data('sohid');
                const barang_id = row.data('barangid'); // bisa angka atau "temp-xxx"
                const qty_box = parseInt(row.data('qtybox')) || 1;

                let barang = {};
                try {
                    const raw = row.attr('data-barang');
                    barang = raw ? JSON.parse(raw) : {};
                } catch (e) {
                    console.error('Gagal parse data-barang:', e);
                }

                const qty_full_raw = row.find('.qty_full').val();
                const qty_receh_raw = row.find('.qty_receh').val();
                const keterangan = row.find('.keterangan').val()?.trim() || '';

                // tolak kalau semuanya kosong
                if (qty_full_raw === '' && qty_receh_raw === '' && keterangan === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input belum diisi',
                        text: 'Isi minimal salah satu: Qty atau Keterangan.',
                    });
                    return;
                }

                // tentukan mode otomatis
                const qty_full = parseInt(qty_full_raw || 0, 10);
                const qty_receh = parseInt(qty_receh_raw || 0, 10);
                const summary = (qty_full * qty_box) + qty_receh;
                const principal = $('#principal_filter').val();

                // Tentukan mode
                let mode = '';
                const hasQty = !isNaN(qty_full_raw) && qty_full_raw !== '' || !isNaN(qty_receh_raw) &&
                    qty_receh_raw !== '';

                if (hasQty && keterangan !== '') {
                    mode = 'both';
                } else if (hasQty) {
                    mode = 'qty';
                } else if (keterangan !== '') {
                    mode = 'note';
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input belum diisi',
                        text: 'Isi minimal salah satu: Qty atau Keterangan.',
                    });
                    return;
                }


                const btn = $(this);
                const isTempItem = String(id).startsWith('temp-');

                let url, data;
                if (isTempItem) {
                    // barang baru (belum punya id dari soh)
                    const mid_barang = barang.mid_barang || '';
                    const nama_barang = barang.nama_barang || '';

                    url = "{{ route('wfg.stock_opname.save-temp-new') }}";
                    data = {
                        mid_barang,
                        nama_barang,
                        qty_box,
                        qty_full,
                        qty_receh,
                        summary,
                        keterangan,
                        principal,
                        mode, // kirim mode
                        _token: '{{ csrf_token() }}'
                    };
                } else {
                    // barang dari soh
                    url = "{{ route('wfg.stock_opname.save-temp') }}";
                    data = {
                        soh_id,
                        barang_id,
                        qty_full,
                        qty_receh,
                        summary,
                        keterangan,
                        principal,
                        mode, // kirim mode juga
                        _token: '{{ csrf_token() }}'
                    };
                }

                $.ajax({
                    url,
                    type: "POST",
                    data,
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message, 'Tersimpan!');

                            // kalau bukan catatan (mode qty), reset input qty
                            if (mode === 'qty' || mode === 'both') {
                                row.find('.qty_full').val('');
                                row.find('.qty_receh').val('');
                            }

                            // kalau note, biarkan input keterangan tetap biar user bisa edit lanjut
                            // loadBarangForOpname(1, '');
                            loadAllTempData();
                        } else {
                            toastr.warning(res.message, 'Gagal!');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(
                            xhr.responseJSON?.message || 'Terjadi kesalahan pada server.',
                            'Kesalahan!'
                        );
                    },
                    complete: function() {
                        btn.prop('disabled', false)
                            .html('<i class="mdi mdi-content-save-outline"></i>');
                    }
                });
            });

            // isi konten setelah collapse benar-benar terbuka
            $(document).on('shown.bs.collapse', '.collapse', function() {
                const row = $(this).prev('.soh-row');
                const sohId = row.data('sohid');
                const barangId = row.data('barangid');

                // Pastikan pakai key yang sama seperti waktu di-cache
                const key = sohId ? `soh_${sohId}` : `barang_${barangId}`;
                const tempData = window.tempBatchCache?.[key];
                const td = $(this).find('td');

                if (tempData?.history?.length > 0) {
                    let historyHtml = `<div class="border rounded-3 p-3 shadow-sm"><div class="row g-2">`;
                    tempData.history.forEach((h, index) => {
                        const created = new Date(h.updated_at).toLocaleString('id-ID', {
                            dateStyle: 'medium',
                            timeStyle: 'short'
                        });
                        historyHtml += `
                            <div class="col-md-3 col-6">
                                <div class="p-2 border border-info rounded h-100 fade show bg-light">
                                    <div class="fw-semibold text-dark mb-1 d-flex justify-content-between align-items-center">
                                        <span>Full: ${h.qty_full}, Receh: ${h.qty_receh}</span>
                                        <span class="badge bg-info">${index + 1}</span>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="mdi mdi-clock-outline me-1"></i>${created}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    historyHtml += `</div></div>`;

                    td.hide().html(historyHtml).fadeIn(150);
                } else {
                    td.html('<em class="text-muted">Belum ada riwayat input.</em>');
                }
            });

            // kosongkan isi setelah collapse tertutup sepenuhnya
            $(document).on('hidden.bs.collapse', '.collapse', function() {
                $(this).find('td').html('');
            });

            // save final
            $('#btnSaveFinal').on('click', function(e) {
                e.preventDefault();

                const tgl_opname = $('#tgl_opname').val();
                const principal = $('#principal_filter').val();

                if (!tgl_opname) {
                    Swal.fire('Peringatan', 'Tanggal opname wajib diisi!', 'warning');
                    return;
                }

                // Step 1: Check Progress
                $.ajax({
                    url: "{{ route('wfg.stock_opname.process') }}",
                    method: "POST",
                    data: {
                        _token: $('input[name="_token"]').val(),
                        tgl_opname,
                        principal,
                        mode: 'check'
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            let listHtml = '';

                            if (res.data && res.data.length > 0) {
                                listHtml = `
                                        <div style="max-height: 400px; overflow-y: auto; text-align: left;">
                                            <ul class="list-unstyled mb-0">
                                    `;
                                res.data.forEach(item => {
                                    listHtml += `
                                            <li class="mb-3">
                                                <strong>${item.mid_barang}</strong>
                                                Selisih: <span class="text-danger fw-bold">${item.selisih}</span>
                                            </li>
                                        `;
                                });
                                listHtml += `
                                            </ul>
                                        </div>
                                    `;
                            }

                            Swal.fire({
                                title: res.data?.length > 0 ? 'Terdapat Selisih!' :
                                    'Data Lengkap!',
                                html: `
                                        <p class="mb-3">${res.message}</p>
                                        ${listHtml}
                                    `,
                                icon: res.data?.length > 0 ? 'info' : 'success',
                                width: 600,
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Submit',
                                cancelButtonText: 'Batal'
                            }).then(result => {
                                if (result.isConfirmed) {
                                    // Step 2: Finalisasi
                                    $.ajax({
                                        url: "{{ route('wfg.stock_opname.process') }}",
                                        method: "POST",
                                        data: {
                                            _token: $('input[name="_token"]')
                                                .val(),
                                            tgl_opname,
                                            principal,
                                            mode: 'final'
                                        },
                                        success: function(finalRes) {
                                            if (finalRes.status ===
                                                'success') {
                                                Swal.fire({
                                                    title: 'Berhasil!',
                                                    text: finalRes
                                                        .message,
                                                    icon: 'success',
                                                    confirmButtonText: 'OK'
                                                }).then(() => {
                                                    location
                                                        .reload();
                                                });
                                            } else if (finalRes.status ===
                                                'belum_opname') {
                                                let listHtml =
                                                    '<ul class="list-unstyled mb-0">';
                                                finalRes.data.forEach(
                                                    item => {
                                                        listHtml +=
                                                            `<li><strong>${item.mid_barang}</strong></li>`;
                                                    });
                                                listHtml += '</ul>';

                                                Swal.fire({
                                                    title: 'Belum di-Opname!',
                                                    html: `
                                                            <p class="mb-3">${finalRes.message}</p>
                                                            <div style="max-height: 400px; overflow-y: auto;">${listHtml}</div>
                                                        `,
                                                    icon: 'info',
                                                    width: 600,
                                                    confirmButtonText: 'OK'
                                                });
                                            } else {
                                                Swal.fire('Gagal', finalRes
                                                    .message, 'error');
                                            }
                                        },
                                        error: () => Swal.fire('Error',
                                            'Gagal menyimpan final.',
                                            'error')
                                    });
                                }
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: () => Swal.fire('Error', 'Gagal memeriksa progres.', 'error')
                });
            });

            $('#soInputTableContainer').on('click', '.btn-edit-temp', function() {
                const row = $(this).closest('tr.soh-row');
                const barangId = row.data('barangid');

                $.ajax({
                    url: `{{ url('api/wfg/sop/getDataTempEdit') }}/${barangId}`,
                    type: 'GET',
                    success: function(res) {
                        if (res.status === 'success') {
                            const items = res.data_qty || [];
                            const note = res.data_note?.catatan || '';

                            let html = '';

                            items.forEach((item, index) => {
                                const dateObj = new Date(item.updated_at.replace(' ',
                                    'T'));
                                const formattedDate = dateObj.toLocaleString('id-ID', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });

                                html += `
                                    <div class="mb-3 border border-info p-2 rounded temp-item" data-tempid="${item.id}">
                                        <input type="hidden" class="temp_id" value="${item.id}">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <p class="mb-0 fw-semibold">
                                                MID: ${item.barang.mid_barang} - ${formattedDate}
                                            </p>
                                            <span class="badge bg-info ms-2">${index + 1}</span>
                                        </div>
                                        <label>Qty Full</label>
                                        <input type="number" class="form-control qty_full mb-2 bg-light" value="${item.qty_full}">
                                        <label>Qty Receh</label>
                                        <input type="number" class="form-control qty_receh mb-2 bg-light" value="${item.qty_receh}">
                                        <button type="button" class="btn btn-danger btn-sm btn-delete-temp mt-1" data-type="qty">
                                            <i class="mdi mdi-delete"></i> Hapus
                                        </button>
                                    </div>
                                `;

                            });

                            // Tambahkan catatan di bawah
                            html += `
                                <hr>
                                <div class="mb-3 border border-info p-2 rounded temp-note" data-barangid="${barangId}">
                                    <label>Catatan</label>
                                    <textarea class="form-control temp_note bg-light" rows="3">${note}</textarea>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-temp mt-1" data-type="note">
                                        <i class="mdi mdi-delete"></i> Hapus
                                    </button>
                                </div>
                            `;

                            $('#editModal .modal-body').html(html);
                            $('#editModal').modal('show');
                        } else {
                            toastr.error(res.message || 'Gagal mengambil data',
                                'Error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan.',
                            'error');
                    }
                });
            });

            $('#editModal').on('click', '.btn-delete-temp', function() {
                const tipe = $(this).data('type'); // 'qty' atau 'note'
                const container = $(this).closest(tipe === 'qty' ? '.temp-item' : '.temp-note');
                const tempId = tipe === 'qty' ? container.data('tempid') : container.data('barangid');

                Swal.fire({
                    title: `Hapus ${tipe === 'note' ? 'catatan' : 'data qty'}?`,
                    text: `Data ${tipe === 'note' ? 'catatan' : 'sementara'} akan dihapus permanen!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wfg.stock_opname.delete-temp', '') }}/" +
                                tempId,
                            type: 'DELETE',
                            data: {
                                _token: $('input[name="_token"]').val(),
                                tipe: tipe
                            },
                            success: function(res) {
                                if (res.status === 'success') {
                                    toastr.success(res.message, 'Terhapus');
                                    container.remove();
                                } else {
                                    toastr.error(res.message || 'Gagal menghapus data',
                                        'Error');
                                }
                            },
                            error: function() {
                                toastr.error('Gagal menghapus data', 'Error');
                            }
                        });
                    }
                });
            });

            $('#saveEditBtn').on('click', function() {
                const updates = [];

                $('#editModal .modal-body > div').each(function() {
                    const tempId = $(this).find('.temp_id').val();
                    const qtyFull = $(this).find('.qty_full').val();
                    const qtyReceh = $(this).find('.qty_receh').val();

                    updates.push({
                        id: tempId,
                        qty_full: qtyFull,
                        qty_receh: qtyReceh,
                    });
                });

                const catatan = $('#editModal .temp_note').val();

                $.ajax({
                    url: "{{ route('wfg.stock_opname.update-temp') }}",
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        items: updates,
                        catatan: catatan
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message, 'Berhasil');
                            $('#editModal').modal('hide');

                            loadBarangForOpname(); // refresh tabel
                        } else {
                            toastr.error(res.message || 'Gagal menyimpan data',
                                'Error');
                        }
                    },
                    error: function() {
                        toastr.error(res.message || 'Gagal menyimpan data',
                            'Error');
                    }
                });
            });

            // Reset btn Temp All
            $('#btnReset').on('click', function() {
                const tglOpname = $('#tgl_opname').val();

                if (!tglOpname) {
                    Swal.fire('Peringatan', 'Tanggal opname belum dipilih.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Reset',
                    text: 'Apakah kamu yakin ingin menghapus semua data penyimpanan sementara?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wfg.stock_opname.reset-temp') }}",
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                tgl_opname: tglOpname
                            },
                            success: function(res) {
                                if (res.status === 'success') {
                                    toastr.success(res.message, 'Berhasil');
                                } else if (res.status === 'info') {
                                    toastr.info(res.message, 'Info');
                                } else {
                                    toastr.error(res.message, 'Error');
                                }

                                generatePrincipalTabs();
                            },
                            error: function(xhr) {
                                toastr.error('Gagal menghapus data sementara.');
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });
            });

            // Reset temp per Row
            $(document).on('click', '.btn-reset-temp', function() {
                const row = $(this).closest('.soh-row');
                const sohId = row.data('sohid');
                const barangId = row.data('barangid');

                const key = sohId ? `soh_${sohId}` : `barang_${barangId}`;

                Swal.fire({
                    title: 'Reset Data Opname Sementara?',
                    text: 'Data opname sementara untuk barang ini akan dihapus dan tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, reset',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wfg.stock_opname.reset-temp-row') }}",
                            type: "DELETE",
                            data: {
                                soh_id: sohId,
                                barang_id: barangId,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                if (res.status === "success") {
                                    // Kosongkan tampilan summary dan history
                                    row.find('.summary-cell').text('0');
                                    row.find('.keterangan').val('');
                                    const collapseRow = $(`#history-${row.data('id')}`);
                                    collapseRow.find('td').html(
                                        '<em class="text-muted">Belum ada riwayat input.</em>'
                                    );

                                    // Hapus cache di client
                                    delete window.tempBatchCache[key];

                                    toastr.success(res.message, 'Berhasil');
                                } else {
                                    toastr.error(res.message ||
                                        'Gagal reset data opname',
                                        'Error');
                                }
                            },
                            error: function(err) {
                                console.error("Error reset opname:", err);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops!',
                                    text: 'Terjadi kesalahan saat reset data.'
                                });
                            }
                        });
                    }
                });
            });

            // Add new row
            $('#btnAddRow').on('click', function() {
                $('#formAddItem')[0].reset();
                $('#modalAddItem').modal('show');
            });

            // Save temp new item 
            $('#btnSaveNewItem').on('click', function() {
                const form = $('#formAddItem');
                const btn = $(this);

                const data = {
                    // === data Barang ===
                    mid_barang: form.find('[name="mid_barang"]').val().trim(),
                    nama_barang: form.find('[name="nama_barang"]').val().trim(),
                    uom: form.find('[name="uom"]').val().trim(),
                    qty_box: parseInt(form.find('[name="qty_box"]').val()) || 1,
                    principal_barang: form.find('[name="principal"]').val().trim(),

                    // === Data SOH ===
                    unrest: parseInt(form.find('[name="unrest"]').val()) || 0,
                    qi: parseInt(form.find('[name="qi"]').val()) || 0,
                    blocked: parseInt(form.find('[name="blocked"]').val()) || 0,

                    // === Data SOP ===
                    qty_full: parseInt(form.find('[name="qty_full"]').val()) || 0,
                    qty_receh: parseInt(form.find('[name="qty_receh"]').val()) || 0,

                    // === Meta Tambahan ===
                    principal: $('#principal_filter').val() || null,
                    _token: '{{ csrf_token() }}'
                };

                // Validasi form
                if (!data.mid_barang) {
                    Swal.fire('Peringatan', 'MID Barang wajib diisi.', 'warning');
                    return;
                }

                if (data.unreset === 0 && data.qi === 0 && data.blocked === 0) {
                    Swal.fire('Peringatan', 'Isi minimal salah satu Qty Unrest, Qi atau Blocked.',
                        'warning');
                    return;
                }

                if (data.qty_full === 0 && data.qty_receh === 0) {
                    Swal.fire('Peringatan', 'Isi minimal salah satu Qty Full atau Qty Receh.', 'warning');
                    return;
                }
                data.summary = (data.qty_full * data.qty_box) + data.qty_receh;

                // Tombol disable selama proses
                btn.prop('disabled', true)
                    .html('<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...');

                // AJAX request
                $.ajax({
                    url: "{{ route('wfg.stock_opname.save-temp-new') }}", // endpoint backend
                    type: "POST",
                    data: data,
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Item Ditambahkan',
                                text: res.message,
                                timer: 1200,
                                showConfirmButton: false
                            });

                            $('#modalAddItem').modal('hide');
                            loadBarangForOpname(1, $('#principal_filter').val());
                        } else {
                            Swal.fire('Gagal', res.message ||
                                'Tidak dapat menambahkan item baru.', 'warning');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message ||
                            'Terjadi kesalahan di server.', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false)
                            .html(
                                '<i class="mdi mdi-content-save-outline me-1"></i> Simpan Item'
                            );
                    }
                });
            });
        });

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

        function loadAllTempData() {
            const rows = $('.soh-row');
            const keyIds = [];

            rows.each(function() {
                const sohId = $(this).data('sohid');
                const barangId = $(this).data('barangid');
                const key = sohId ? `soh_${sohId}` : `barang_${barangId}`;
                keyIds.push(key);
            });

            const uniqueKeys = [...new Set(keyIds)];
            if (uniqueKeys.length === 0) return;

            const sohIds = [];
            const barangIds = [];
            uniqueKeys.forEach(k => {
                if (k.startsWith('soh_')) sohIds.push(parseInt(k.replace('soh_', '')));
                else if (k.startsWith('barang_')) barangIds.push(parseInt(k.replace('barang_', '')));
            });

            $.ajax({
                url: "{{ route('wfg.stock_opname.getTempBatch') }}",
                type: "GET",
                data: {
                    soh_ids: sohIds,
                    barang_ids: barangIds
                },
                success: function(res) {
                    if (res.status === "success" && Array.isArray(res.data)) {
                        window.tempBatchCache = {};
                        const groupedData = {};

                        res.data.forEach(tempRecord => {
                            const groupingKey = tempRecord.soh_id ?
                                `soh_${tempRecord.soh_id}` :
                                `barang_${tempRecord.barang_id}`;

                            if (!groupedData[groupingKey]) {
                                groupedData[groupingKey] = {
                                    total_summary: 0,
                                    history: [],
                                    latestNote: null
                                };
                            }

                            const group = groupedData[groupingKey];

                            // 🔍 Pisahkan mode berdasarkan sumber data
                            if (tempRecord.mode === 'note') {

                                // simpan catatan terbaru
                                if (tempRecord.keterangan && tempRecord.keterangan.trim() !== '') {
                                    if (
                                        !group.latestNote ||
                                        new Date(tempRecord.created_at) > new Date(group.latestNote
                                            .created_at)
                                    ) {
                                        group.latestNote = {
                                            text: tempRecord.keterangan.trim(),
                                            created_at: tempRecord.created_at
                                        };
                                    }
                                }
                            } else {
                                // mode qty
                                group.history.push(tempRecord);

                                // hanya qty yg dihitung summary
                                const summary = parseInt(tempRecord.summary) || 0;
                                group.total_summary += summary;
                            }
                        });

                        // 🧩 Update tampilan tabel
                        for (const key in groupedData) {
                            const tempItem = groupedData[key];
                            const totalSummary = tempItem.total_summary;
                            const latestNote = tempItem.latestNote ? tempItem.latestNote.text : '';

                            let selector;
                            if (key.startsWith('soh_')) {
                                const sohId = key.replace('soh_', '');
                                selector = `.soh-row[data-sohid="${sohId}"]`;
                            } else {
                                const barangId = key.replace('barang_', '');
                                selector = `.soh-row[data-barangid="${barangId}"]`;
                            }

                            const row = $(selector);
                            if (row.length) {
                                // tampilkan total summary
                                row.find('.summary-cell').text(totalSummary.toLocaleString('id-ID'));

                                // isi input keterangan dengan note terbaru saja
                                if (latestNote) {
                                    row.find('.keterangan').val(latestNote);
                                }

                                // 🚀 simpan semua data ke cache
                                window.tempBatchCache[key] = {
                                    total_summary: totalSummary,
                                    history: tempItem.history,
                                    latestNote: tempItem.latestNote
                                };
                            }
                        }
                    }
                },
                error: function(err) {
                    console.error("Gagal load data temp batch:", err);
                }
            });
        }
    </script>
@endsection
