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

        .offcanvas-header {
            background: linear-gradient(135deg, #f96060 0%, #e37220 100%);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #c03e3e;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .card-variant-1 .soh-box {
            background: linear-gradient(135deg, #f96060 0%, #e37220 100%);
            color: white;
            border-radius: 12px;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        .card-variant-1 .soh-box::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }

        .detail-row {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }

        .detail-value {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .info-badge {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.5rem;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header mb-3" data-aos="fade-left">
                <div class="container-fluid">
                    <h1 class="h2 fw-bold mb-2 text-white">
                        <i class="mdi mdi-upload me-2"></i>
                        Upload Stock On Hand (SOH)
                    </h1>
                    <p class="mb-0 opacity-90">Kelola dan update stock on hand setiap hari agar up to date</p>
                </div>
            </div>

            <div class="mb-3" data-aos="fade-right" data-aos-delay="100">
                <div class="card">
                    <div class="card-body">
                        <div class="row my-3 align-items-center">
                            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                                <form id="searchSOHForm" class="d-flex" role="search">
                                    <div class="input-group">
                                        <input type="search" class="form-control" id="searchSOHInput"
                                            placeholder="Cari SOH berdasarkan nama barang atau kode..." aria-label="Search">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="mdi mdi-magnify"></i> Cari
                                        </button>
                                    </div>
                                </form>
                            </div>


                            <div class="col-lg-6 col-md-12 d-flex justify-content-between">
                                <button class="btn btn-success w-100 me-2" data-bs-toggle="modal"
                                    data-bs-target="#uploadModal">
                                    <i class="mdi mdi-upload me-1"></i> Upload
                                </button>

                                <button class="btn btn-info w-100" onclick="openAddSOH()">
                                    <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4" id="soh-list">
                <!-- Data SOH akan dimuat di sini lewat AJAX -->
            </div>

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Peringatan!</strong> {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    {{-- modal upload soh --}}
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        <i class="mdi mdi-cloud-upload me-1"></i> Upload File SOH
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formUploadSOH" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info" role="alert">
                            Hanya izinkan file <b>.xlsx, .xls, atau .csv.</b> Ukuran maksimal <b>5MB</b>.
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label fw-bold">Pilih File Stock On Hand</label>
                            <input class="form-control" type="file" id="file" name="file" required
                                accept=".xlsx, .xls, .csv">
                        </div>
                    </div>

                    <div class="modal-footer p-2 d-flex justify-content-between">
                        <a href="{{ route('wfg.stock_opname.soh.template') }}" target="_blank"
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

    {{-- modal add soh --}}
    <div class="modal fade" id="sohModal" tabindex="-1" aria-labelledby="sohModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formSOH">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sohModalLabel">
                            <i class="mdi mdi-plus-circle me-1"></i>Tambah Data SOH Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="soh_id" name="id">

                        <div class="mb-3">
                            <label for="barang_id" class="form-label fw-semibold">Barang</label>
                            <select id="barang_id" name="barang_id" class="form-select" required>
                                <option value="">-- Pilih Barang --</option>
                                <!-- barang akan dimuat lewat AJAX -->
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Qty SOH</label>
                                <input type="number" class="form-control" id="qty_soh" name="qty_soh">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qty PAL</label>
                                <input type="number" class="form-control" id="qty_pal" name="qty_pal">
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Qty UNREST</label>
                                <input type="number" class="form-control" id="unrest" name="unrest">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qty QI</label>
                                <input type="number" class="form-control" id="qi" name="qi">
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Qty BLOCK</label>
                                <input type="number" class="form-control" id="block" name="block">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qty Scan 2</label>
                                <input type="number" class="form-control" id="scan_2" name="scan_2">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-info" id="btnSaveSOH">
                            <i class="mdi mdi-content-save me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Offcanvas Detail SOH -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSOHDetail" aria-labelledby="offcanvasTitle">
        <div class="offcanvas-header border-bottom">
            <div>
                <h5 class="offcanvas-title mb-0 text-white" id="offcanvasTitle">
                    <i class="mdi mdi-information-outline me-2"></i>Detail Stock On Hand
                </h5>
                <small id="offcanvasMID" class="text-white d-block mt-1"></small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-2" id="offcanvasContent">
            <!-- Konten detail akan dimuat lewat JavaScript -->
            <div class="text-center text-muted">
                <i class="mdi mdi-database-search-outline fs-1 mb-2"></i>
                <p>Memuat detail data...</p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadSOHList();

            function loadSOHList(search = '') {
                const ajaxData = {};
                if (search) {
                    ajaxData.search = search;
                }

                $.ajax({
                    url: `{{ url('api/wfg/soh/listData') }}`,
                    type: "GET",
                    data: ajaxData,
                    beforeSend: function() {
                        $('#soh-list').html(`
                            <div class="col-12">
                                <div class="d-flex flex-column align-items-center py-5">
                                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-3 text-primary fw-semibold">Memuat data Stock On Hand...</p>
                                </div>
                            </div>
                        `);
                    },
                    success: function(data) {
                        const finalData = data;

                        if (finalData.length === 0) {

                            let noDataMessage = search ?
                                `Tidak ditemukan data SOH untuk pencarian: "<strong>${search}</strong>"` :
                                `Belum ada data Stock On Hand yang tercatat untuk hari ini.`;

                            $('#soh-list').html(`
                                <div class="col-12">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                                        <i class="mdi mdi-package-variant-closed-remove" style="font-size: 72px;"></i>
                                        <h4 class="fw-light mt-3">${search ? 'Pencarian Tidak Ditemukan' : 'Stok Kosong Hari Ini'}</h4>
                                        <p class="text-center">${noDataMessage}</p>
                                    </div>
                                </div>
                            `);
                            return;
                        }

                        let html = '';
                        finalData.forEach(item => {
                            html += `
                               <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xxl-3" data-aos="fade-up" data-aos-delay="100">
                                    <div class="card h-100 border-0 shadow-sm card-hover card-variant-1 position-relative overflow-hidden d-flex flex-column">
                                        <div class="card-body p-3 flex-grow-1 d-flex flex-column justify-content-between">
                                            <!-- Header -->
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold text-dark mb-0 line-clamp-2 flex-grow-1 pe-2">
                                                    ${item.barang?.nama_barang ?? 'Nama Barang Tidak Diketahui'}
                                                </h6>
                                                <span class="badge badge-soft-primary px-3 py-2">
                                                    MID: ${item.barang?.mid_barang ?? 'N/A'}
                                                </span>
                                            </div>


                                            <!-- SOH Box -->
                                            <div class="soh-box text-center mb-2 position-relative">
                                                <small class="d-block mb-1 opacity-75">Stock On Hand</small>
                                                <h4 class="fw-bold mb-0 text-white">${item.qty_soh}</h4>
                                            </div>

                                            <!-- Info Grid -->
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <div class="info-badge text-center">
                                                        <i class="mdi mdi-cube-outline text-primary d-block mb-1"></i>
                                                        <small class="text-muted d-block">Qty/Box</small>
                                                        <strong class="text-dark">${item.barang.qty_box ?? 0}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="info-badge text-center">
                                                        <i class="mdi mdi-warehouse text-info d-block mb-1"></i>
                                                        <small class="text-muted d-block">PAL</small>
                                                        <strong class="text-dark">${item.qty_pal ?? 0}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer Actions -->
                                        <div class="card-footer mt-auto border-top p-3">
                                            <div class="d-flex justify-content-between gap-2 flex-nowrap">
                                                <button class="btn btn-outline-primary flex-fill"
                                                    onclick='detailSOH(${JSON.stringify(item)})'>
                                                    <i class="mdi mdi-eye-outline me-1"></i>Detail
                                                </button>
                                                <button class="btn btn-outline-warning flex-fill"
                                                    onclick="editSOH(${item.id})">
                                                    <i class="mdi mdi-pencil-outline me-1"></i>Edit
                                                </button>
                                                <button class="btn btn-outline-danger flex-fill"
                                                    onclick="deleteSOH(${item.id})">
                                                    <i class="mdi mdi-trash-can-outline me-1"></i>Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        $('#soh-list').html(html);
                    },
                    error: function(xhr, status, error) {
                        $('#soh-list').html(`
                            <div class="col-12">
                                <div class="d-flex flex-column align-items-center justify-content-center py-5 text-danger">
                                    <i class="mdi mdi-alert-octagon-outline" style="font-size: 72px;"></i>
                                    <h4 class="fw-bold mt-3">Gagal Memuat Data</h4>
                                    <p class="text-center">Terjadi kesalahan saat mengambil data SOH.<br><small class="text-muted">Error: ${status}</small></p>
                                </div>
                            </div>
                        `);
                    }
                });
            }

            $('#formUploadSOH').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('wfg.stock_opname.soh.import') }}",
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message ??
                                'File Stock On Hand berhasil diunggah.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $('#uploadModal').modal('hide');
                        $('#file').val('');

                        // Refresh DataTable kalau ada
                        loadSOHList();
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat mengunggah file.';
                        let listNotFound = '';

                        if (xhr.responseJSON) {
                            const res = xhr.responseJSON;

                            // Jika ada pesan utama
                            if (res.message) msg = res.message;

                            // Jika ada daftar MID yang tidak ditemukan
                            if (res.not_found && res.not_found.length > 0) {
                                listNotFound = '<ul style="text-align:left; margin-top:10px;">';
                                res.not_found.forEach(mid => {
                                    listNotFound += `<li>${mid}</li>`;
                                });
                                listNotFound += '</ul>';
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengunggah!',
                            html: `${msg}${listNotFound}`
                        });

                        loadSOHList();
                    }
                });
            });

            // submit add/edit
            $('#formSOH').on('submit', function(e) {
                e.preventDefault();

                const id = $('#soh_id').val();
                const formData = $(this).serialize();
                const url = mode === 'add' ?
                    `{{ route('wfg.stock_opname.soh.store') }}` :
                    `{{ route('wfg.stock_opname.soh.update', '') }}/${id}`;

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(res) {
                        Swal.close();
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message || (mode === 'add' ?
                                    'Data berhasil ditambahkan.' :
                                    'Data berhasil diupdate.'),
                                timer: 1800,
                                showConfirmButton: false
                            });
                            $('#sohModal').modal('hide');
                            loadSOHList();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: res.message || 'Terjadi kesalahan.',
                            });
                        }
                    },
                    error: function(xhr) {
                        // Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menyimpan data.',
                        });
                    }
                });
            });

            // Search
            $('#searchSOHForm').on('submit', function(e) {
                e.preventDefault(); // cegah reload page
                const query = $('#searchSOHInput').val().trim();
                loadSOHList(query); // panggil fungsi loadSOHList dengan keyword
            });

            // Optional: search realtime saat mengetik
            $('#searchSOHInput').on('keyup', function() {
                const query = $(this).val().trim();
                loadSOHList(query);
            });

            window.loadSOHList = loadSOHList;
        });

        // default mode
        let mode = 'add';

        function openAddSOH() {
            mode = 'add';
            $('#formSOH')[0].reset();
            $('#soh_id').val('');
            $('#sohModalLabel').html('<i class="mdi mdi-plus-circle me-1"></i>Tambah Data SOH Baru');
            $('#btnSaveSOH').html('<i class="mdi mdi-content-save me-1"></i>Simpan');

            loadBarangOptions(() => {
                $('#barang_id').val('');
                $('#sohModal').modal('show');
            });
        }

        function editSOH(id) {
            mode = 'edit';
            $('#sohModalLabel').html('<i class="mdi mdi-pencil-outline me-1"></i>Edit Data SOH');
            $('#btnSaveSOH').html('<i class="mdi mdi-content-save-edit me-1"></i>Update');

            loadBarangOptions(() => {
                $.ajax({
                    url: `{{ url('api/wfg/soh/show') }}/${id}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.data) {
                            const data = res.data;
                            $('#soh_id').val(data.id);
                            // $('#barang_id').val(data.barang_id);
                            $('#barang_id').val(data.barang_id).prop('disabled', true);
                            $('#qty_soh').val(data.qty_soh);
                            $('#qty_pal').val(data.qty_pal);
                            $('#unrest').val(data.qty_unrest);
                            $('#qi').val(data.qty_qi);
                            $('#block').val(data.qty_block);
                            $('#scan_2').val(data.qty_scan_2);
                            $('#sohModal').modal('show');
                        } else {
                            alert('Gagal mengambil data SOH!');
                        }
                    },
                    error: function(err) {
                        console.error(err);
                        alert('Terjadi kesalahan saat mengambil data.');
                    }
                });
            });
        }

        const formatDate = (dateString) => {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        };

        function detailSOH(item) {
            // Set title
            $('#offcanvasTitle').text(item.barang?.nama_barang ?? 'Detail Stock On Hand');
            $('#offcanvasMID').text(`MID: ${item.barang?.mid_barang ?? 'N/A'}`);

            // Set content
            const content = `
                <div class="p-4">
                    <div class="bg-info bg-gradient rounded-3 p-3 mb-4 text-center shadow">
                        <h6 class="d-block mb-1 text-white">Stock On Hand</h6>
                        <h1 class="mb-0 fw-bold display-4 text-white">${item.qty_soh.toLocaleString('id-ID')}</h1>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3 border-bottom pb-2">
                            <i class="mdi mdi-information-outline me-1"></i>Detail Kuantitas
                        </h6>
                        <div class="row g-3">
                            
                            <div class="col-6">
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 border">
                                    <i class="mdi mdi-package-variant text-info fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1 text-uppercase">PAL</div>
                                        <strong class="text-info fs-5">${item.qty_pal}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 border">
                                    <i class="mdi mdi-alert-circle-outline text-warning fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1 text-uppercase">UNREST</div>
                                        <strong class="text-warning fs-5">${item.qty_unrest}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 border">
                                    <i class="mdi mdi-check-decagram-outline text-success fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1 text-uppercase">QI</div>
                                        <strong class="text-success fs-5">${item.qty_qi}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 border">
                                    <i class="mdi mdi-lock text-danger fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1 text-uppercase">BLOCK</div>
                                        <strong class="text-danger fs-5">${item.qty_block}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3 border-bottom pb-2">
                            <i class="mdi mdi-swap-horizontal me-1"></i>Transaksi
                        </h6>
                        <div class="row g-3">
                            
                            <div class="col-6">
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 border">
                                    <i class="mdi mdi-arrow-down-bold text-success fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1">In</div>
                                        <strong class="text-success fs-5">${item.qty_in}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 border">
                                    <i class="mdi mdi-arrow-up-bold text-danger fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1">Out</div>
                                        <strong class="text-danger fs-5">${item.qty_out}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 border">
                                    <i class="mdi mdi-cart text-primary fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1">Penjualan</div>
                                        <strong class="text-primary fs-5">${item.qty_penjualan}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 border">
                                    <i class="mdi mdi-barcode-scan text-secondary fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1">Scan 2</div>
                                        <strong class="text-secondary fs-5">${item.qty_scan_2}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top p-3 bg-white sticky-bottom">
                    <div class="row g-2">
                        <div class="col-4">
                            <button class="btn btn-soft-warning w-100" onclick="editSOH(${item.id})">
                                <i class="mdi mdi-pencil me-1"></i>Edit
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-soft-danger w-100" onclick="deleteSOH(${item.id})">
                                <i class="mdi mdi-trash-can me-1"></i>Hapus
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-soft-secondary w-100" data-bs-dismiss="offcanvas">
                                <i class="mdi mdi-close me-1"></i>Tutup
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#offcanvasContent').html(content);

            // Show offcanvas
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasSOHDetail'));
            offcanvas.show();
        }

        function loadBarangOptions(callback) {
            $('#barang_id').html('<option value="">Memuat data...</option>');

            $.ajax({
                url: `{{ url('api/wfg/soh/getBarang') }}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#barang_id').html('<option value="">-- Pilih Barang --</option>');

                    // ✅ jika response langsung array
                    if (Array.isArray(response) && response.length > 0) {
                        $.each(response, function(index, item) {
                            $('#barang_id').append(
                                `<option value="${item.id}">${item.mid_barang}</option>`
                            );
                        });
                    }
                    // ✅ jika response punya struktur {status:'success', data:[...]}
                    else if (response.status === 'success' && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            $('#barang_id').append(
                                `<option value="${item.id}">${item.mid_barang}</option>`
                            );
                        });
                    } else {
                        $('#barang_id').html('<option value="">Data barang tidak ditemukan</option>');
                    }

                    if (typeof callback === 'function') callback();
                },
                error: function(xhr, status, error) {
                    console.error('Gagal memuat data barang:', error);
                    $('#barang_id').html('<option value="">Gagal memuat data</option>');
                    if (typeof callback === 'function') callback();
                }
            });
        }

        function deleteSOH(id) {
            Swal.fire({
                title: 'Hapus Data?',
                text: 'Data SOH ini akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ route('wfg.stock_opname.soh.delete', '') }}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === true) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: data.message || 'Data berhasil dihapus',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                loadSOHList();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: data.message || 'Gagal menghapus data'
                                });
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan pada server'
                            });
                        });
                }
            });
        }
    </script>
@endsection
