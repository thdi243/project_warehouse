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
            <div class="page-header mb-3" data-aos="fade-left">
                <div class="container-fluid">
                    <h1 class="h2 fw-bold mb-2 text-white">
                        <i class="mdi mdi-upload me-2"></i>
                        Upload Stock On Hand - SAP
                    </h1>
                    <p class="mb-0 opacity-90">Kelola dan update stock on hand setiap hari agar up to date</p>
                </div>
            </div>

            <div class="mb-3" data-aos="fade-right" data-aos-delay="100">
                <div class="card">
                    <div class="card-body">
                        <div class="row my-3 align-items-center">
                            <div
                                class="@if (Auth::user()->jabatan != 'operator') col-lg-3 @else col-lg-6 @endif col-md-12 mb-3 mb-lg-0">
                                <form id="searchSOHForm" class="position-relative w-100" role="search">
                                    <input type="search" class="form-control ps-5" id="searchSOHInput"
                                        placeholder="Cari MID..." aria-label="Search">
                                    <i
                                        class="mdi mdi-magnify position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                </form>

                            </div>

                            {{-- Filter principal hanya untuk non-operator --}}
                            @if (Auth::user()->jabatan != 'operator')
                                <div class="col-lg-3 col-md-12 mb-3 mb-lg-0">
                                    <select id="filterPrincipal" class="form-select">
                                        <option value="">All Principal</option>
                                        @foreach ($principals as $p)
                                            <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                             @if ($barangCount > 0)
                                <div
                                    class="@if (Auth::user()->jabatan != 'operator') col-lg-6 @else col-lg-6 @endif col-md-4 d-flex justify-content-between" id="soh-actions-container">
                                    <div id="soh-actions-wrapper" class="d-flex gap-2 w-100 justify-content-between">
                                    <button class="btn btn-success w-100 me-2" data-bs-toggle="modal"
                                        data-bs-target="#uploadModal">
                                        <i class="mdi mdi-upload me-1"></i> Upload
                                    </button>
                                    <button class="btn btn-info w-100 me-2" onclick="openAddSOH()">
                                        <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah
                                    </button>

                                    <button class="btn btn-danger w-100 me-2" id="btnDeleteAll">
                                        <i class="mdi mdi-refresh me-1"></i> Delete All
                                    </button>
                                    </div>
                                </div>
                            @else
                                <div class="col-lg-6 col-md-4">
                                    <div class="alert alert-warning py-2 px-3 mb-0 w-100 text-center small border-0 shadow-none">
                                        <i class="mdi mdi-alert-circle me-1"></i> Data Master Barang kosong. Silakan isi <a href="{{ route('master.wfg.barang.index') }}" class="alert-link text-decoration-underline fw-bold">Master Barang</a> terlebih dahulu.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body">
                    <!-- Tab Pemilihan Jenis SO -->
                    <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" id="jenisSoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-value="cycle_count" type="button" role="tab">
                                <i class="mdi mdi-sync me-1"></i> Cycle Count (Daily)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-value="monthly" type="button" role="tab">
                                <i class="mdi mdi-calendar-month me-1"></i> Monthly SO
                            </button>
                        </li>
                    </ul>
                    <div id="soh-table-container" class="table-responsive">
                    </div>
                </div>
            </div>
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
                            Hanya izinkan file <b>.xlsx, .xls</b> Ukuran maksimal <b>5MB</b>.
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label fw-bold">Pilih File Stock On Hand</label>
                            <input class="form-control" type="file" id="file" name="file" required
                                accept=".xlsx, .xls">
                        </div>
                        <div class="mb-3">
                            <label for="upload_jenis_so" class="form-label fw-bold">Jenis SO</label>
                            <select class="form-select" id="upload_jenis_so" name="jenis_so" required>
                                <option value="cycle_count">Cycle Count (Daily)</option>
                                <option value="monthly">Monthly SO</option>
                            </select>
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
                            <label for="soh_jenis_so" class="form-label fw-semibold">Jenis SO</label>
                            <select class="form-select" id="soh_jenis_so" name="jenis_so" required>
                                <option value="cycle_count">Cycle Count (Daily)</option>
                                <option value="monthly">Monthly SO</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="barang_id" class="form-label fw-semibold">Barang</label>
                            <select id="barang_id" name="barang_id" class="form-select" required>
                                <option value="">-- Pilih Barang --</option>
                                <!-- barang akan dimuat lewat AJAX -->
                            </select>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Qty UNREST</label>
                                <input type="number" class="form-control" id="unrest" name="unrest"
                                    placeholder="500">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qty Qual Insp</label>
                                <input type="number" class="form-control" id="qi" name="qi"
                                    placeholder="2">
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Qty BLOCKED</label>
                                <input type="number" class="form-control" id="block" name="block"
                                    placeholder="5">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success" id="btnSaveSOH">
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
                <small id="principalBarang" class="text-white d-block mt-1"></small>
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

    {{-- @php
        dd(session()->all());
    @endphp --}}

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            loadSOHList();

            // Tab Event for SOH type
            $('#jenisSoTabs button').on('shown.bs.tab', function(e) {
                loadSOHList();
            });

            // Automatically pre-select jenis_so in upload modal based on active tab
            $('#uploadModal').on('show.bs.modal', function() {
                const activeJenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';
                $('#upload_jenis_so').val(activeJenisSo).prop('disabled', false);
            });

            function loadSOHList(page = 1, search = '', principal = '') {
                const jenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';
                const ajaxData = {
                    page: page,
                    per_page: 25,
                    jenis_so: jenisSo
                };

                if (search) {
                    ajaxData.search = search;
                }

                if (principal) {
                    ajaxData.principal = principal;
                }

                const container = $('#soh-table-container');

                $.ajax({
                    // url: `{{ url('api/wfg/soh/listData') }}`,
                    url: "{{ route('wfg.stock_opname.soh.list') }}",
                    type: "GET",
                    data: ajaxData,
                    beforeSend: function() {
                        // Tampilkan loading state di dalam container
                        container.html(`
                            <div class="d-flex flex-column align-items-center py-5">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-primary fw-semibold">Memuat data Stock On Hand...</p>
                            </div>
                        `);
                    },
                    success: function(response) {
                        const paginatedData = response;
                        const finalData = response.data; // Array data item
                        const isFinished = response.is_finished || false;
                        let html = '';

                        if (finalData.length === 0) {
                            // Empty State
                            let noDataMessage = search ?
                                `Tidak ditemukan data SOH untuk pencarian: "<strong>${search}</strong>"` :
                                `Belum ada data Stock On Hand yang tercatat untuk hari ini.`;

                            container.html(`
                                <div class="d-flex flex-column align-items-center justify-content-center py-3 text-muted">
                                    <img src="{{ asset('assets/images/empty_state.png') }}" alt="Empty" style="width:150px;">
                                    <h4 class="fw-light mt-3">${search ? 'Pencarian Tidak Ditemukan' : 'Stok Kosong Hari Ini'}</h4>
                                    <p class="text-center">${noDataMessage}</p>
                                </div>
                            `);

                            $("#paginationContainer").empty();

                            if (typeof AOS !== 'undefined') {
                                AOS.refresh();
                            }

                            return;
                        }

                        // Mulai membangun struktur tabel
                        html += `
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-nowrap mb-0 w-100">
                                    <thead class="bg-soft-info">
                                        <tr>
                                            <th>No</th>
                                            <th>MID</th>
                                            <th class="text-end">Qty SAP</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        // Hitung nomor urut yang benar untuk setiap halaman
                        const perPage = paginatedData.per_page;
                        const currentPage = paginatedData.current_page;
                        const startNumber = (currentPage - 1) * perPage;

                        // Loop dan render baris tabel
                        finalData.forEach((item, index) => {
                            const noUrut = startNumber + index +
                                1; // Nomor urut berbasis halaman
                            html += `
                                <tr> 
                                    <td>${noUrut}</td>
                                    <td>${item.barang?.mid_barang ?? 'N/A'}</td>
                                    <td class="text-end text-primary fw-bold">${item.qty_soh.toLocaleString('id-ID')}</td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-outline-info"
                                                onclick='detailSOH(${JSON.stringify(item)})' title="Detail">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                            <button class="btn btn-outline-warning"
                                                onclick="editSOH(${item.id})" title="Edit">
                                                <i class="mdi mdi-pencil-outline"></i>
                                            </button>
                                            <button class="btn btn-outline-danger"
                                                onclick="deleteSOH(${item.id})" title="Hapus">
                                                <i class="mdi mdi-trash-can-outline"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        // Tutup tabel
                        html += `
                                    </tbody>
                                </table>
                                
                            </div>
                            <div class="d-flex justify-content-center mt-4" id="paginationContainer">
                            </div>
                        `;

                        container.html(html);

                        renderPagination(paginatedData);

                        // Jika Anda menggunakan AOS, panggil refresh di sini
                        if (typeof AOS !== 'undefined') {
                            AOS.refresh();
                        }

                    },
                    error: function(xhr, status, error) {
                        // Error State
                        container.html(`
                            <div class="d-flex flex-column align-items-center justify-content-center py-5 text-danger">
                                <i class="mdi mdi-alert-octagon-outline" style="font-size: 72px;"></i>
                                <h4 class="fw-bold mt-3">Gagal Memuat Data</h4>
                                <p class="text-center">Terjadi kesalahan saat mengambil data SOH.<br><small class="text-muted">Error: ${status}</small></p>
                            </div>
                        `);
                    }
                });
            }

            $('#formUploadSOH').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                const principal = $('#filterPrincipal').val() ?? '';
                formData.append('principal', principal);
                if ($('#upload_jenis_so').is(':disabled')) {
                    formData.append('jenis_so', $('#upload_jenis_so').val());
                }

                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                if (csrfToken) {
                    formData.append('_token', csrfToken);
                }

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
                        let listNotFound = '';

                        if (response.not_found && response.not_found.length > 0) {
                            listNotFound = '<ul style="text-align:left; margin-top:10px;">';
                            response.not_found.forEach(mid => {
                                listNotFound += `<li>${mid}</li>`;
                            });
                            listNotFound += '</ul>';
                        }

                        Swal.fire({
                            icon: response.not_found && response.not_found.length > 0 ?
                                'warning' : 'success',
                            title: response.not_found && response.not_found.length > 0 ?
                                'Beberapa MID Tidak Ditemukan' : 'Berhasil!',
                            html: `${response.message ?? 'File Stock On Hand berhasil diunggah.'}${listNotFound}`
                        });

                        $('#uploadModal').modal('hide');
                        $('#file').val('');
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
                const principal = $('#filterPrincipal').val() ?? '';
                const url = mode === 'add' ?
                    `{{ route('wfg.stock_opname.soh.store') }}` :
                    `{{ route('wfg.stock_opname.soh.update', '') }}/${id}`;

                let formData = $(this).serialize() + `&principal=${principal}`;
                if ($('#soh_jenis_so').is(':disabled')) {
                    formData += '&jenis_so=' + encodeURIComponent($('#soh_jenis_so').val());
                }

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
                            loadSOHList();
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
                const principal = $('#filterPrincipal').val() ?? '';
                loadSOHList(1, query, principal); // panggil fungsi loadSOHList dengan keyword
            });

            // Optional: search realtime saat mengetik
            $('#searchSOHInput').on('keyup', function() {
                const query = $(this).val().trim();
                const principal = $('#filterPrincipal').val() ?? '';
                loadSOHList(1, query, principal);
            });

            $('#filterPrincipal').on('change', function() {
                const principal = $(this).val();
                const searchQuery = $('#searchSOHInput').val().trim();
                loadSOHList(1, searchQuery, principal);
            });

            $('#btnDeleteAll').on('click', function(e) {
                e.preventDefault();
                const jenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';

                Swal.fire({
                    title: 'Reset Data SOH?',
                    text: 'Semua data Stock On Hand jenis ' + (jenisSo === 'monthly' ? 'Monthly SO' : 'Cycle Count') + ' ' + (jenisSo === 'monthly' ? 'bulan ini' : 'hari ini') + ' akan dihapus. Tindakan ini tidak bisa dibatalkan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus data',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wfg.stock_opname.soh.reset_all') }}",
                            type: "DELETE",
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                jenis_so: jenisSo
                            },
                            beforeSend: function() {
                                Swal.fire({
                                    title: 'Menghapus data...',
                                    text: 'Mohon tunggu sebentar',
                                    allowOutsideClick: false,
                                    didOpen: () => Swal.showLoading()
                                });
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message ??
                                        'Data SOH hari ini berhasil dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Reset filter dan refresh tabel
                                $('#searchSOHInput').val('');
                                $('#filterPrincipal').val('').trigger('change');
                                $('#barang_id').val('').trigger('change');

                                loadSOHList();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message ??
                                        'Terjadi kesalahan saat menghapus data.'
                                });
                            }
                        });
                    }
                });
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
            $('#barang_id').prop('disabled', false);
            const activeJenisSo = $('#jenisSoTabs button.active').data('value') || 'cycle_count';
            $('#soh_jenis_so').val(activeJenisSo).prop('disabled', false);

            loadBarangOptions(() => {
                $('#barang_id').val('');

                $('#barang_id').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#sohModal'),
                    placeholder: '-- Pilih Barang --',
                    allowClear: true,
                    width: '100%'
                });

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
                            $('#soh_jenis_so').val(data.jenis_so).prop('disabled', false);
                            // $('#barang_id').val(data.barang_id);
                            $('#barang_id').val(data.barang_id).prop('disabled', true);
                            $('#qty_soh').val(data.qty_soh);
                            $('#unrest').val(data.qty_unrest);
                            $('#qi').val(data.qty_qi);
                            $('#block').val(data.qty_block);
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
            $('#principalBarang').text(`Principal: ${item.barang?.principal ?? 'N/A'}`);

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
                                    <i class="mdi mdi-alert-circle-outline text-warning fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1 text-uppercase">UNREST</div>
                                        <strong class="text-warning fs-5">${item.qty_unrest.toLocaleString('id-ID')}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="d-flex align-items-center bg-light rounded-3 p-3 border">
                                    <i class="mdi mdi-check-decagram-outline text-success fs-4 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="small text-muted mb-1 text-uppercase">QI</div>
                                        <strong class="text-success fs-5">${item.qty_qi.toLocaleString('id-ID')}</strong>
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
                url: "{{ route('wfg.stock_opname.soh.getBarang') }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#barang_id').html('<option value="">-- Pilih Barang --</option>');

                    // jika response langsung array
                    if (Array.isArray(response) && response.length > 0) {
                        $.each(response, function(index, item) {
                            $('#barang_id').append(
                                `<option value="${item.id}">${item.mid_barang}</option>`
                            );
                        });
                    }
                    // jika response punya struktur {status:'success', data:[...]}
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

        function renderPagination(data) {
            const container = $("#paginationContainer");
            container.empty();

            if (container.length === 0) {
                console.warn("Pagination container #paginationContainer tidak ditemukan di DOM.");
                return;
            }

            if (data.last_page <= 1) {
                return;
            }

            let paginationHtml = '<nav aria-label="Page navigation"><ul class="pagination">';

            const prevDisabled = data.current_page === 1 ? 'disabled' : '';
            const prevPage = data.current_page - 1;
            paginationHtml += `
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" data-page="${prevPage}">Previous</a>
                </li>
            `;

            for (let i = 1; i <= data.last_page; i++) {
                const activeClass = data.current_page === i ? 'active' : '';
                paginationHtml += `
                    <li class="page-item ${activeClass}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }

            // 5. Tombol Next
            const nextDisabled = data.current_page === data.last_page ? 'disabled' : '';
            const nextPage = data.current_page + 1;
            paginationHtml += `
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" data-page="${nextPage}">Next</a>
                </li>
            `;

            paginationHtml += '</ul></nav>';

            container.append(paginationHtml);


            container.off('click', '.page-link').on('click', '.page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');

                if ($(this).closest('.page-item').hasClass('disabled')) {
                    return;
                }

                loadSOHList(page);
            });
        }

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
    </script>
@endsection
