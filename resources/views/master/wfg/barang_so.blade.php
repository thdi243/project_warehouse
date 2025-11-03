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
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem 0;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.2);
        }

        .search-bar {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .search-bar input {
            transition: all 0.3s ease;
        }

        .search-bar input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .item-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            height: 100%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .item-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .item-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .item-card:hover::before {
            transform: scaleY(1);
        }

        .card-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background: var(--gray-100);
        }

        .item-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.75rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5rem;
        }

        .item-meta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .meta-item i {
            color: var(--primary-color);
            font-size: 1rem;
        }

        .btn-add {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 0.875rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            color: #e7e7e7ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }


        /* Offcanvas Custom Styles */
        .offcanvas-detail {
            width: 500px !important;
        }

        .offcanvas-header {
            /* background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); */
            color: white;
        }

        .offcanvas-title {
            font-weight: 600;
        }

        .detail-image-large {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .detail-info-section {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
        }

        .info-row {
            display: flex;
            align-items: start;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            border: 1px solid var(--gray-200);
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 0.5rem;
            flex-shrink: 0;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .status-badge-large {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }

        .form-check {
            display: flex;
            align-items: center;
            padding-left: 0 !important;
        }

        .form-check-input {
            position: static !important;
            margin-top: 0 !important;
            margin-left: 0 !important;
            margin-right: 0.5rem !important;
            float: none !important;
            transform: none !important;
        }

        .collapse-button {
            color: #495057;
            /* Warna default ikon */
        }

        .collapse-button:hover {
            color: #007bff;
            /* Warna hover ikon */
        }

        /* Opsional: pastikan baris detail memiliki background berbeda */
        .collapse-row td {
            border-top: none !important;
        }

        .collapse-row .bg-light {
            background-color: #f8f9fa !important;
            /* Warna yang membedakan detail */
        }

        @media (max-width: 1200px) {
            .offcanvas-detail {
                width: 400px !important;
            }
        }

        @media (max-width: 768px) {
            .offcanvas-detail {
                width: 100% !important;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="page-header mb-4" data-aos="fade-down">
                <div class="container-fluid">
                    <h1 class="h2 fw-bold mb-2 text-white">
                        <i class="mdi mdi-warehouse me-2"></i>
                        Master Data Barang WFG
                    </h1>
                    <p class="mb-0 opacity-90">Kelola dan monitor daftar barang dengan mudah</p>
                </div>
            </div>

            <div class="search-bar mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="position-relative">
                            <i class="mdi mdi-magnify position-absolute"
                                style="left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-600);"></i>
                            <input type="text" id="searchInput" placeholder="Cari MID atau Nama Barang..."
                                class="form-control ps-5">
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-2">
                        <select id="statusFilter" class="form-select w-100">
                            <option value="active">Barang Aktif</option>
                            <option value="trashed">Barang Nonaktif</option>
                        </select>
                    </div>

                    <!-- 🔽 Tambahkan dropdown principal di sini -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <select id="principalFilter" class="form-select w-100">
                            <option value="">Semua Principal</option>
                            @foreach ($principals as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-2">
                        <button data-bs-toggle="modal" data-bs-target="#uploadModal"
                            class="btn btn-success w-100 text-nowrap">
                            <i class="mdi mdi-cloud-upload-outline me-2"></i>
                            Upload
                        </button>
                    </div>

                    <div class="col-12 col-md-6 col-lg-2">
                        <button onclick="openModal()" data-bs-toggle="modal" data-bs-target="#itemModal"
                            class="btn btn-primary w-100 text-nowrap">
                            <i class="mdi mdi-plus-circle me-2"></i>
                            Tambah
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🔔 Card Barang Baru -->
            <div class="card border-warning mb-4 shadow-sm" id="newItemsCard" style="display: none;">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="mdi mdi-alert-circle-outline me-2"></i> Barang Baru Ditemukan
                    </h5>
                    <small class="text-muted">Perlu konfirmasi sebelum digunakan</small>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>MID</th>
                                    <th>Principal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="newItemsBody">
                                <!-- Data barang baru akan diisi via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="200" id="itemTableCard">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-nowrap" id="itemTable">
                            <thead class="table-info">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>MID</th>
                                    <th>Principal</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemTableBody">
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-4" id="paginationContainer">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="card border-0 shadow-sm rounded-3 text-center p-4 mb-4" style="display: none;"
                data-aos="fade-left" data-aos-delay="100">
                <div class="card-body">
                    <img src="{{ asset('assets/images/empty_state.png') }}" alt="Empty" style="width:150px;">
                    <h5 class="fw-bold mb-2">Belum Ada Data Barang</h5>
                    <p class="text-muted mb-3">
                        Mulai tambahkan barang baru untuk mengelola inventory Anda
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Add/Edit Barang --}}
    <div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formBarang" enctype="multipart/form-data" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalLabel">
                        <i class="mdi mdi-package-variant-closed me-2"></i>
                        Tambah Barang Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="id" id="itemId">

                    <div class="mb-3">
                        <label for="midBarang" class="form-label">MID Barang</label>
                        <input type="text" name="mid_barang" id="midBarang" class="form-control"
                            placeholder="Masukkan 1-8 digit MID">
                    </div>

                    <div class="mb-3">
                        <label for="namaBarang" class="form-label">
                            Nama Barang <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_barang" id="namaBarang" class="form-control"
                            placeholder="Masukkan nama barang" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="qtyBox" class="form-label">
                                Qty Box/Pallet <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="qty_box" id="qtyBox" class="form-control"
                                placeholder="Contoh: 12" required min="1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tipeKemasan" class="form-label">Principal</label>
                            <input type="text" name="principal" id="tipeKemasan" class="form-control"
                                placeholder="Contoh: BAS, SMU">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="satuan" class="form-label">Uom</label>
                        <input type="text" name="uom" id="satuan" class="form-control"
                            placeholder="contoh: pcs, box, kg">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-content-save me-1"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Offcanvas Detail Barang --}}
    <div class="offcanvas offcanvas-end offcanvas-detail" tabindex="-1" id="detailOffcanvas"
        aria-labelledby="detailOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="detailOffcanvasLabel">
                <i class="mdi mdi-information-outline me-2"></i>
                Detail Barang
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div id="detailContent">
            </div>
        </div>
    </div>

    {{-- Modal upload barang --}}
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        <i class="mdi mdi-cloud-upload me-1"></i> Upload File Master Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formUploadBarang" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info" role="alert">
                            Hanya izinkan file <b>.xlsx, .xls.</b> Ukuran maksimal <b>5MB</b>.
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label fw-bold">Pilih File Master Barang</label>
                            <input class="form-control" type="file" id="file" name="file" required
                                accept=".xlsx, .xls, .csv">
                        </div>
                    </div>

                    <div class="modal-footer p-2 d-flex justify-content-between">
                        <a href="{{ route('wfg.master.barang.template') }}" target="_blank"
                            class="btn btn-soft-warning flex-fill ms-0 me-1">
                            <i class="mdi mdi-download me-1"></i> Unduh Template
                        </a>

                        <button type="submit" class="btn btn-soft-success flex-fill me-0 ms-1">
                            <i class="mdi mdi-check-bold me-1"></i> Unggah Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            loadNewItems();

            // debounce for search/delay
            function debounce(func, delay) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), delay);
                };
            }

            // Tambahkan parameter 'page' dengan default 1
            window.loadBarang = function(page = 1) {
                const status = $('#statusFilter').val();
                const searchTerm = $('#searchInput').val();
                const principal = $('#principalFilter').val();

                let container = $("#itemTableBody");
                container.empty();
                $("#emptyState").hide();
                $("#paginationContainer").empty();

                $.ajax({
                    url: `{{ route('wfg.master.barang.data') }}`,
                    method: 'GET',
                    dataType: 'json',

                    data: {
                        status: status,
                        search: searchTerm,
                        page: page,
                        principal: principal
                    },
                    success: function(res) {

                        // TANGKAP SELURUH OBJEK PAGINATOR DARI BACKEND
                        const paginatedData = res.data;
                        const items = paginatedData.data;
                        currentBarangData = {};

                        if (res.status === true && items.length > 0) {
                            $("#emptyState").hide();
                            $("#itemTable").show();
                            $("#itemTableCard").show();

                            // 3. Loop dan render baris tabel (TR)
                            $.each(items, function(i, item) {
                                currentBarangData[item.id] = item;
                                const perPage = paginatedData
                                    .per_page;
                                const currentPage = paginatedData
                                    .current_page;
                                const noUrut = ((currentPage - 1) * perPage) + (i + 1);
                                const statusClass = item.status === 'aktif' ?
                                    'badge-soft-success' : 'badge-soft-danger';

                                const isTrashedView = (status === 'trashed' || status ===
                                    'trashed');

                                let actionButton = `
                                    <button class="btn btn-sm btn-outline-warning" onclick="editBarang(${item.id})" title="Edit Data">
                                        <i class="mdi mdi-pencil me-2"></i>Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete" title="Hapus Data" data-id="${item.id}">
                                        <i class="mdi mdi-delete me-2"></i>Delete
                                    </button>
                                `;

                                // Jika sedang lihat barang trashed → ubah tombol jadi Restore + Force Delete
                                if (isTrashedView) {
                                    actionButton = `
                                        <button class="btn btn-sm btn-outline-success" onclick="restoreBarang(${item.id})" title="Pulihkan Data">
                                            <i class="mdi mdi-backup-restore me-2"></i>Restore
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="forceDeleteBarang(${item.id})" title="Hapus Permanen">
                                            <i class="mdi mdi-delete-forever me-2"></i>Hapus Permanen
                                        </button>
                                    `;
                                }

                                let row = `
                                     <tr id="row-${item.id}">
                                        <td>${noUrut}</td>
                                        <td><strong>${item.nama_barang}</strong></td>
                                        <td>${item.mid_barang ?? '-'}</td>
                                        <td>${item.principal ?? '-'}</td>
                                        <td>
                                            <span class="badge ${statusClass}">${item.status}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                
                                                <button class="btn btn-sm btn-outline-info collapse-trigger"
                                                        type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#detail-${item.id}" 
                                                        aria-expanded="false" 
                                                        aria-controls="detail-${item.id}"
                                                        title="Lihat Detail">
                                                    <i id="collapse-icon-${item.id}" class="mdi mdi-chevron-down me-2"></i>Detail
                                                </button>

                                                ${actionButton}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="collapse-row">
                                        <td colspan="6" class="p-0 border-0"> 
                                            <div class="collapse p-0" id="detail-${item.id}"> 
                                                <div class="p-3 bg-light border-bottom" id="detailContent-${item.id}">
                                                    </div>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                container.append(row);

                            });

                            // 4. Render tombol paginasi
                            renderPagination(paginatedData);

                            $('#itemTableBody').find('.collapse').off('show.bs.collapse').on(
                                'show.bs.collapse',
                                function() {
                                    const itemId = this.id.replace('detail-', '');
                                    // Putar ikon panah ke atas dan panggil muat detail
                                    $(`#collapse-icon-${itemId}`).addClass('rotated');
                                    showDetailCollapse(parseInt(itemId));
                                }).off('hide.bs.collapse').on('hide.bs.collapse', function() {
                                const itemId = this.id.replace('detail-', '');
                                // Kembalikan ikon panah ke bawah
                                $(`#collapse-icon-${itemId}`).removeClass('rotated');
                            });

                            if (typeof AOS !== 'undefined') {
                                AOS.refresh();
                            }
                        } else {
                            $("#itemTable").hide();
                            $("#itemTableCard").hide();
                            $("#emptyState").show();
                        }

                        if (typeof AOS !== 'undefined') {
                            AOS.refresh();
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#itemTable").hide();
                        $("#itemTableCard").hide();
                        $("#paginationContainer").empty();
                        console.error("Gagal memuat data barang:", xhr.responseJSON ? xhr
                            .responseJSON
                            .message : error);
                        $("#emptyState").show().text('Gagal memuat data. Silakan coba lagi.');
                    }
                });
            }

            loadBarang();

            $('#statusFilter').on('change', function() {
                loadBarang(1);
            });

            // Search functionality
            $("#searchInput").on("keyup", debounce(function() {
                loadBarang(1);
            }, 300));

            $('#principalFilter').on('change', function() {
                loadBarang(1);
            });

            function renderPagination(data) {
                const container = $("#paginationContainer");
                container.empty();

                if (!data || data.last_page <= 1) return;

                let paginationHtml = '<nav aria-label="Page navigation"><ul class="pagination">';

                // Tombol Previous
                const prevDisabled = data.current_page === 1 ? 'disabled' : '';
                const prevPage = data.current_page - 1;
                paginationHtml += `
                    <li class="page-item ${prevDisabled}">
                        <a class="page-link" href="#" data-page="${prevPage}">Previous</a>
                    </li>
                `;

                // --- Batas jumlah tombol yang ditampilkan ---
                const maxVisible = 1; // tampilkan 1 di kiri dan 1 di kanan dari halaman aktif
                let startPage = Math.max(1, data.current_page - maxVisible);
                let endPage = Math.min(data.last_page, data.current_page + maxVisible);

                // Tampilkan tombol pertama jika perlu
                if (startPage > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="1">1</a>
                        </li>
                    `;
                    if (startPage > 2) paginationHtml +=
                        `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }

                // Tampilkan range dinamis
                for (let i = startPage; i <= endPage; i++) {
                    const activeClass = data.current_page === i ? 'active' : '';
                    paginationHtml += `
                        <li class="page-item ${activeClass}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                // Tampilkan tombol terakhir jika perlu
                if (endPage < data.last_page) {
                    if (endPage < data.last_page - 1) paginationHtml +=
                        `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="#" data-page="${data.last_page}">${data.last_page}</a>
                        </li>
                    `;
                }

                // Tombol Next
                const nextDisabled = data.current_page === data.last_page ? 'disabled' : '';
                const nextPage = data.current_page + 1;
                paginationHtml += `
                    <li class="page-item ${nextDisabled}">
                        <a class="page-link" href="#" data-page="${nextPage}">Next</a>
                    </li>
                `;

                paginationHtml += '</ul></nav>';
                container.append(paginationHtml);

                // Event listener
                $('#paginationContainer').off('click', '.page-link').on('click', '.page-link', function(e) {
                    e.preventDefault();
                    const page = $(this).data('page');
                    if (!page || $(this).closest('.page-item').hasClass('disabled')) return;
                    loadBarang(page); // panggil ulang data barang sesuai halaman
                });
            }

            // Submit form tambah/edit barang
            $("#formBarang").on("submit", function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let itemId = $("#itemId").val();

                // Tentukan URL dan method berdasarkan mode (add/edit)
                let url = "{{ route('wfg.master.barang.store') }}";
                let method = "POST";

                if (itemId) {
                    url = "{{ route('wfg.master.barang.update', '') }}/" + itemId;
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        toastr.success(res.message, 'Berhasil');

                        $("#itemModal").modal("hide");
                        $("#formBarang")[0].reset();
                        $("#itemId").val('');
                        loadBarang();
                    },
                    error: function(xhr) {
                        let title = 'Oops!';
                        let msg = 'Terjadi kesalahan tidak diketahui.';
                        let icon = 'error';

                        if (xhr.status === 422) {
                            // Validasi gagal
                            if (xhr.responseJSON?.errors) {
                                const errorMessages = Object.values(xhr.responseJSON.errors)
                                    .flat()
                                    .join('\n');
                                msg = errorMessages;
                            } else {
                                msg = xhr.responseJSON?.message || 'Validasi gagal.';
                            }
                        } else if (xhr.status === 400) {
                            msg = xhr.responseJSON?.message || 'Permintaan tidak valid.';
                        } else if (xhr.status === 500) {
                            msg = xhr.responseJSON?.message || 'Terjadi kesalahan pada server.';
                        } else {
                            msg = xhr.responseJSON?.message || msg;
                        }

                        toastr.error(msg, title);
                    }
                });
            });

            // upload handle
            $('#formUploadBarang').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('wfg.master.barang.import') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Mengunggah...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        let listErrors = '';

                        // kalau backend kasih error parsial
                        if (response.errors && response.errors.length > 0) {
                            listErrors +=
                                '<div style="max-height:200px;overflow-y:auto;text-align:left;margin-top:10px;">';
                            listErrors += '<ul class="mb-0">';
                            response.errors.forEach(function(e) {
                                listErrors += '<li><strong>Baris ' + e.baris +
                                    ':</strong> ' + e.error + '</li>';
                            });
                            listErrors += '</ul></div>';
                        }

                        // lalu tampilkan alert-nya
                        Swal.fire({
                            icon: response.errors && response.errors.length > 0 ?
                                'warning' : 'success',
                            title: response.errors && response.errors.length > 0 ?
                                'Berhasil dengan Catatan!' : 'Berhasil!',
                            html: (response.message ?? 'File berhasil diunggah.') +
                                listErrors
                        });

                        $('#uploadModal').modal('hide');
                        $('#file').val('');
                        loadBarang();
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat mengunggah file.';
                        let listError = '';

                        if (xhr.responseJSON) {
                            const res = xhr.responseJSON;

                            // Pesan utama
                            if (res.message) msg = res.message;

                            // Detail error dari backend (baris & pesan)
                            if (res.errors && res.errors.length > 0) {
                                listError +=
                                    '<div style="max-height: 200px; overflow-y: auto; text-align:left; margin-top:10px;">';
                                listError += '<ul class="mb-0">';
                                res.errors.forEach(function(e) {
                                    listError += '<li><strong>Baris ' + e.baris +
                                        ':</strong> ' + e.error + '</li>';
                                });
                                listError += '</ul></div>';
                            }
                        }
                        // Tampilkan semua error (baik umum atau detail)
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengunggah!',
                            html: msg + listError
                        });

                        loadSOHList();
                    }
                });
            });

            // Reset form when modal is closed
            $('#itemModal').on('hidden.bs.modal', function() {
                $("#formBarang")[0].reset();
                $("#itemId").val('');
                $("#itemModalLabel").html(
                    '<i class="mdi mdi-package-variant-closed me-2"></i>Tambah Barang Baru');
            });

            // Hapus Barang
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).closest('[data-id]').data('id');
                Swal.fire({
                    title: 'Hapus Barang?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wfg.master.barang.delete', '') }}/" + id,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                loadBarang();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: 'Terjadi kesalahan saat menghapus data'
                                });
                            }
                        });
                    }
                });
            });

            // Ambil barang baru (is_new = 1)
            function loadNewItems() {
                $.ajax({
                    url: "{{ route('wfg.master.barang.new') }}", // endpoint baru
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        const card = $("#newItemsCard");
                        const tbody = $("#newItemsBody");
                        tbody.empty();

                        if (response.length === 0) {
                            card.hide();
                            return;
                        }

                        response.forEach(item => {
                            const row = `
                                <tr>
                                    <td>${item.nama_barang}</td>
                                    <td>${item.mid_barang}</td>
                                    <td>${item.principal}</td>
                                    <td class="text-center">
                                        <button class="btn btn-success btn-sm me-2 approve-item" data-id="${item.id}">
                                            <i class="mdi mdi-check"></i> Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm reject-item" data-id="${item.id}">
                                            <i class="mdi mdi-close"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            `;
                            tbody.append(row);
                        });

                        card.show();
                    },
                    error: function() {
                        console.error("Gagal memuat barang baru.");
                    }
                });
            }

            // Handle tombol approve
            $(document).on("click", ".approve-item", function() {
                const id = $(this).data("id");

                $.ajax({
                    url: `{{ url('wfg/master/barang/new/approve') }}/${id}`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        toastr.success("Barang telah disetujui!");
                        loadNewItems(); // refresh daftar barang baru
                        loadBarang();
                    },
                    error: function() {
                        toastr.error("Gagal menyetujui barang.");
                    }
                });
            });

            // Handle tombol reject
            $(document).on("click", ".reject-item", function() {
                const id = $(this).data("id");

                $.ajax({
                    url: `{{ url('wfg/master/barang/new/reject') }}/${id}`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function() {
                        toastr.info("Barang telah ditolak.");
                        loadNewItems(); // refresh daftar barang baru
                        loadBarang();
                    },
                    error: function() {
                        toastr.error("Gagal menolak barang.");
                    }
                });
            });
        });

        function openModal() {
            $("#formBarang")[0].reset();
            $("#itemId").val('');
            $("#itemModalLabel").html('<i class="mdi mdi-package-variant-closed me-2"></i>Tambah Barang Baru');
        }

        let currentBarangData = {};

        function showDetailCollapse(id) {
            const item = currentBarangData[id];
            const targetElement = $(`#detailContent-${id}`);

            if (!item) {
                targetElement.html(
                    '<div class="alert alert-danger mb-0">Detail data tidak ditemukan di memori lokal.</div>');
                return;
            }

            // Helper function untuk menampilkan badge status
            const statusBadge =
                `<span class="badge ${item.status === 'aktif' ? 'badge-soft-success' : 'badge-soft-danger'}">${item.status}</span>`;

            // Konten Detail yang Ringkas dan Terbaca
            let detailHTML = `
                <div class="row g-3 text-wrap">
                    <div class="col-md-4">
                        <strong>MID:</strong> ${item.mid_barang ?? '-'}
                    </div>
                    <div class="col-md-4">
                        <strong>Nama Barang:</strong> ${item.nama_barang ?? '-'}
                    </div>
                    <div class="col-md-4">
                        <strong>Principal:</strong> ${item.principal ?? '-'}
                    </div>
                    <div class="col-md-4">
                        <strong>Qty per Box:</strong> ${item.qty_box ?? '-'}
                    </div>
                    <div class="col-md-4">
                        <strong>Uom:</strong> ${item.uom ?? '-'}
                    </div>
                    <div class="col-md-12 mt-3">
                        <strong>Status Saat Ini:</strong> ${statusBadge}
                        ${item.deleted_at ? '<span class="text-danger ms-2">(Telah di-Soft Delete)</span>' : ''}
                    </div>
                </div>
            `;

            targetElement.html(detailHTML);
        }

        function editBarang(id) {
            $.get(`{{ url('api/wfg/show/barang') }}/` + id, function(res) {
                let item = res.data;
                if (item) {
                    $("#itemId").val(item.id);
                    $("#midBarang").val(item.mid_barang);
                    $("#namaBarang").val(item.nama_barang);
                    $("#qtyBox").val(item.qty_box);
                    $("#tipeKemasan").val(item.principal);
                    $("#satuan").val(item.uom);

                    if (item.status === 'aktif') {
                        $("#status").prop('checked', true);
                    } else {
                        $("#status").prop('checked', false);
                    }

                    $("#itemModalLabel").html('<i class="mdi mdi-pencil me-2"></i>Edit Barang');
                    $("#itemModal").modal('show');
                }
            });
        }

        function restoreBarang(id) {
            Swal.fire({
                title: "Pulihkan Data?",
                text: "Barang ini akan diaktifkan kembali.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, Pulihkan",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('wfg.master.barang.restore', ':id') }}".replace(':id',
                            id),
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            loadBarang(1);
                        },
                        error: function(xhr) {
                            let msg = "Terjadi kesalahan.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    });
                }
            });
        }

        function forceDeleteBarang(id) {
            Swal.fire({
                title: "Hapus Permanen?",
                text: "Data ini akan dihapus secara permanen dan tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('wfg.master.barang.forceDelete', ':id') }}".replace(
                            ':id', id),
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            loadBarang(1);
                        },
                        error: function(xhr) {
                            let msg = "Terjadi kesalahan.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    });
                }
            });
        }

        // Toastr Notifications error
        @if (session('error'))
            toastr.options = {
                "closeButton": true,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "0",
                "extendedTimeOut": "0",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut",
                "tapToDismiss": false
            }


            toastr.error("{{ session('error') }}", "Peringatan!");
        @endif

        @if (session('success'))
            toastr.success("{{ session('success') }}", "Berhasil!");
        @endif
    </script>
@endsection
