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
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
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

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
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

            <!-- Search & Add Button -->
            <div class="search-bar mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="row align-items-center g-3">
                    <div class="col-md-7">
                        <div class="position-relative">
                            <i class="mdi mdi-magnify position-absolute"
                                style="left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-600);"></i>
                            <input type="text" id="searchInput" placeholder="Cari MID atau Nama Barang..."
                                class="form-control ps-5">
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <div class="d-flex gap-2 justify-content-end"> <select id="statusFilter" class="form-select w-auto">
                                <option value="active">Barang Aktif</option>
                                <option value="trashed">Barang Dihapus (Arsip)</option>
                                <option value="all">Semua Barang</option>
                            </select>

                            <button onclick="openModal()" data-bs-toggle="modal" data-bs-target="#itemModal"
                                class="btn btn-add w-100 w-md-auto">
                                <i class="mdi mdi-plus-circle me-2"></i>
                                Tambah
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Grid Container -->
            <div class="row g-4 mb-4" id="itemCardContainer">
                <!-- Cards akan dimuat via AJAX -->
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state mb-4" style="display: none;" data-aos="fade-up" data-aos-delay="200">
                <i class="mdi mdi-package-variant"></i>
                <h5 class="fw-bold mb-2">Belum Ada Data Barang</h5>
                <p class="text-muted mb-3">Mulai tambahkan barang baru untuk mengelola inventory Anda</p>
                <button onclick="openModal()" data-bs-toggle="modal" data-bs-target="#itemModal" class="btn btn-add">
                    <i class="mdi mdi-plus-circle me-2"></i>
                    Tambah Barang Pertama
                </button>
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
                                Qty/Box <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="qty_box" id="qtyBox" class="form-control"
                                placeholder="Contoh: 12" required min="1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tipeKemasan" class="form-label">Tipe Kemasan</label>
                            <input type="text" name="tipe_kemasan" id="tipeKemasan" class="form-control"
                                placeholder="Contoh: Pouch, Sachet, Jeriken">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" name="satuan" id="satuan" class="form-control"
                            placeholder="contoh: pcs, box, kg">
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input fs-4" type="radio" name="status" id="statusAktif"
                                    value="aktif" checked required>
                                <label class="form-check-label fw-semibold" for="statusAktif">
                                    Aktif
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input fs-4" type="radio" name="status" id="statusNonaktif"
                                    value="nonaktif" required>
                                <label class="form-check-label fw-semibold" for="statusNonaktif">
                                    Nonaktif
                                </label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">Pilih status ketersediaan barang.</small>
                    </div>
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar</label>
                        <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, maksimal 2MB</small>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize AOS
            AOS.init({
                duration: 600,
                easing: 'ease-in-out',
                once: true,
                offset: 50
            });

            loadBarang();

            // Search functionality
            $("#searchInput").on("keyup", function() {
                let value = $(this).val().toLowerCase();
                $(".card-data").filter(function() {
                    $(this).toggle(
                        $(this).find('.item-title').text().toLowerCase().indexOf(value) > -1 ||
                        $(this).find('.item-meta').text().toLowerCase().indexOf(value) > -1
                    );
                });

                loadBarang();
            });

            $('#statusFilter').on('change', function() {
                loadBarang();
            });

            function loadBarang() {
                // 1. Ambil nilai filter dan search term dari elemen HTML
                const status = $('#statusFilter').val();
                const searchTerm = $('#searchInput').val();

                let container = $("#itemCardContainer");
                container.empty();
                $("#emptyState").hide();
                // Tambahkan logika loading state jika ada

                $.ajax({
                    url: `{{ route('wfg.master.barang.data') }}`,
                    method: 'GET', // Tetap GET
                    dataType: 'json',
                    // 2. Kirim data filter ke backend
                    data: {
                        status: status,
                        search: searchTerm
                    },
                    success: function(res) {
                        // Hapus logika pencarian DOM front-end jika Anda menggunakan filter sisi server

                        if (res.status === true && res.data.length > 0) {
                            $("#emptyState").hide();

                            // 3. Loop dan render card (Logika rendering Anda dipertahankan)
                            $.each(res.data, function(i, item) {
                                let card = `
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4 card-data" data-aos="fade-up" data-aos-delay="${i * 50}">
                            <div class="item-card">
                                <h3 class="item-title">${item.nama_barang}</h3>
                                <div class="item-meta">
                                    <div class="meta-item">
                                        <i class="mdi mdi-barcode"></i>
                                        <span>MID: ${item.mid_barang ?? '-'}</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="mdi mdi-scale-balance"></i>
                                        <span>Satuan: ${item.satuan ?? '-'}</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="mdi mdi-check-circle"></i>
                                        <span class="badge ${item.status === 'aktif' ? 'badge-soft-success' : 'badge-soft-danger'}">
                                            ${item.status}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2 mt-auto pt-3 border-top text-nowrap justify-content-between">
                                    <button class="btn btn-outline-info w-100" onclick="showDetail(${item.id})" title="Lihat Detail">
                                        <i class="mdi mdi-eye me-2"></i>Detail
                                    </button>
                                    <button class="btn btn-outline-warning w-100" onclick="editBarang(${item.id})" title="Edit Data">
                                        <i class="mdi mdi-pencil me-2"></i>Edit
                                    </button>
                                    <button class="btn btn-outline-danger btn-delete w-100" title="Hapus Data" data-id="${item.id}">
                                        <i class="mdi mdi-delete me-2"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                                container.append(card);
                            });

                        } else {
                            $("#emptyState").show();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Gagal memuat data barang:", xhr.responseJSON ? xhr.responseJSON
                            .message : error);
                        $("#emptyState").show().text('Gagal memuat data. Silakan coba lagi.');
                    }
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
                        Swal.fire({
                            icon: 'success',
                            text: res.message,
                            title: 'Berhasil',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $("#itemModal").modal("hide");
                        $("#formBarang")[0].reset();
                        $("#itemId").val('');
                        loadBarang();
                    },
                    error: function(xhr) {
                        let msg = "Terjadi kesalahan";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: msg
                        });
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

        });

        function openModal() {
            $("#formBarang")[0].reset();
            $("#itemId").val('');
            $("#itemModalLabel").html('<i class="mdi mdi-package-variant-closed me-2"></i>Tambah Barang Baru');
        }

        function showDetail(id) {
            // Catatan: Asumsikan route ini mengembalikan JSON dengan array 'data'
            $.get("{{ route('wfg.master.barang.data') }}", function(res) {
                let item = res.data.find(x => x.id === id);

                if (item) {
                    // Helper function untuk menampilkan badge status
                    const statusBadge = `
                        <span class="status-badge-large ${item.status === 'aktif' ? 'badge-soft-success' : 'badge-soft-danger'}">
                            <i class="mdi mdi-circle-small"></i>
                            ${item.status}
                        </span>
                    `;

                    // HTML Baru dengan penambahan qty_box dan tipe_kemasan
                    let detailHTML = `
                        ${item.gambar ? `<img src="${item.gambar}" alt="${item.nama_barang}" class="detail-image-large mb-3">` : ''}
                        <div class="detail-info-section">
                            <div class="row g-2">
                                <div class="col-md-12 col-12">
                                    <div class="info-row">
                                        <div class="info-icon">
                                            <i class="mdi mdi-barcode fs-5"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">MID Barang</div>
                                            <div class="info-value">${item.mid_barang ?? '-'}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-12">
                                    <div class="info-row">
                                        <div class="info-icon">
                                            <i class="mdi mdi-tag fs-5"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Nama Barang</div>
                                            <div class="info-value">${item.nama_barang}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-12">
                                    <div class="info-row">
                                        <div class="info-icon">
                                            <i class="mdi mdi-package-variant-closed fs-5"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Qty per Box</div>
                                            <div class="info-value">${item.qty_box ?? '-'}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-12">
                                    <div class="info-row">
                                        <div class="info-icon">
                                            <i class="mdi mdi-cube-send fs-5"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Tipe Kemasan</div>
                                            <div class="info-value">${item.tipe_kemasan ?? '-'}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-12">
                                    <div class="info-row">
                                        <div class="info-icon">
                                            <i class="mdi mdi-scale-balance fs-5"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Satuan</div>
                                            <div class="info-value">${item.satuan ?? '-'}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-12">
                                    <div class="info-row">
                                        <div class="info-icon">
                                            <i class="mdi mdi-check-circle fs-5"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Status</div>
                                            <div class="info-value">
                                                ${statusBadge}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button class="btn btn-warning flex-fill" onclick="editBarang(${item.id}); $('#detailOffcanvas').offcanvas('hide');">
                                <i class="mdi mdi-pencil me-1"></i>
                                Edit
                            </button>
                            <button class="btn btn-danger flex-fill" onclick="deleteBarang(${item.id}); $('#detailOffcanvas').offcanvas('hide');">
                                <i class="mdi mdi-delete me-1"></i>
                                Hapus
                            </button>
                        </div>
                    `;
                    $("#detailContent").html(detailHTML);
                    $("#detailOffcanvas").offcanvas('show');
                } else {
                    // Tambahkan notifikasi jika item tidak ditemukan
                    Swal.fire({
                        icon: 'error',
                        text: 'Detail barang tidak ditemukan.',
                        title: 'Error',
                    });
                }
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    text: 'Gagal mengambil data dari server.',
                    title: 'Error',
                });
            });
        }

        function editBarang(id) {
            $.get("{{ route('wfg.master.barang.data') }}", function(res) {
                let item = res.data.find(x => x.id === id);
                if (item) {
                    $("#itemId").val(item.id);
                    $("#midBarang").val(item.mid_barang);
                    $("#namaBarang").val(item.nama_barang);
                    $("#qtyBox").val(item.qty_box);
                    $("#tipeKemasan").val(item.tipe_kemasan);
                    $("#satuan").val(item.satuan);

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
    </script>
@endsection
