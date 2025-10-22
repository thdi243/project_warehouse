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

                        <div class="sticky-header p-4 mt-2 mb-4 rounded-3 border shadow-sm">
                            <div class="row g-3 align-items-end">

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
                                            <i class="mdi mdi-content-save-outline me-1"></i> Simpan Semua Progress
                                        </button>
                                        <button type="button" class="btn btn-info w-100" id="btnAddRow">
                                            <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah Item
                                        </button>
                                        <button type="button" class="btn btn-danger" id="btnReset">
                                            <i class="mdi mdi-restart me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <h5 class="mb-3 text-dark fw-bold">Daftar Barang untuk Opname</h5>

                        <div class="table-responsive" id="soInputTableContainer">
                            <p class="text-center text-muted py-5">Memuat daftar barang...</p>
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
                    <button type="button" id="saveEditBtn" class="btn btn-primary">Simpan Semua</button>
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
                    <form id="formAddItem">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">MID Barang</label>
                                <input type="number" class="form-control" name="mid_barang" placeholder="Masukkan MID">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" name="nama_barang"
                                    placeholder="Masukkan Nama Barang">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Uom</label>
                                <input type="text" class="form-control" name="uom" placeholder="Masukkan Uom">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Qty Box</label>
                                <input type="number" class="form-control" name="qty_box" value="1"
                                    min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Qty Full</label>
                                <input type="number" class="form-control" name="qty_full" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Qty Receh</label>
                                <input type="number" class="form-control" name="qty_receh" min="0">
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
            loadBarangForOpname();

            function loadBarangForOpname(page = 1, principal) {
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
                        principal: principal
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
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0 text-nowrap" id="tableInputOpname" style="width: 100%;">
                                    <thead class="bg-soft-info text-center align-middle">
                                        <tr>
                                            <th>No</th>
                                            <th>MID (Qty Box)</th>
                                            <th>Qty Full</th>
                                            <th>Qty Receh</th>
                                            <th>Summary</th>
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

                            tableHtml += `
                                <tr class="soh-row"
                                    data-id="${id}"
                                    data-sohid="${sohid}"
                                    data-barangid="${barangId}"
                                    data-qtybox="${qtyBox}">
                                    
                                    <td class="text-center fw-semibold">${index + 1 + ((currentPage - 1) * response.per_page)}</td>
                                    <td>
                                        <strong class="text-dark">${item.mid_barang ?? 'N/A'}</strong><br>
                                        <small class="text-muted">${item.nama_barang ?? 'N/A'}</small><br>
                                        <small class="text-muted">${item.qty_box ?? 'N/A'}</small>
                                    </td>
                                    
                                    <td><input type="number" class="form-control qty_full text-end" min="0" placeholder="0"></td>
                                    <td><input type="number" class="form-control qty_receh text-end" min="0" placeholder="0"></td>
                                    <td class="text-end fw-bold summary-cell">${item.summary ?? '0'}</td>
                                    
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-save-temp">
                                            <i class="mdi mdi-content-save-outline me-1"></i>Simpan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-history" data-bs-toggle="collapse" data-bs-target="#history-${id}">
                                            <i class="mdi mdi-history me-1"></i>History
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-temp">
                                            <i class="mdi mdi-pencil me-1"></i>Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-reset-temp">
                                            <i class="mdi mdi-refresh me-1"></i>Reset
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="history-${id}">
                                    <td colspan="6" class="p-3 text-muted small text-start">
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
                                loadBarangForOpname(targetPage, '');
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

            $('#principal_filter').on('change', function() {
                const principal = $(this).val();
                loadBarangForOpname(1, principal); // nanti backend ambil param principal
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
                console.log(soh_id);

                const qty_full = parseInt(row.find('.qty_full').val()) || 0;
                const qty_receh = parseInt(row.find('.qty_receh').val()) || 0;
                const summary = (qty_full * qty_box) + qty_receh;

                if (qty_full === 0 && qty_receh === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Input tidak valid',
                        text: 'Isi minimal salah satu Qty Full atau Qty Receh sebelum menyimpan.',
                    });
                    return;
                }

                const principal = $('#principal_filter').val();
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...');

                const isTempItem = String(id).startsWith('temp-');

                let url, data;
                if (isTempItem) {
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
                        principal,
                        _token: '{{ csrf_token() }}'
                    };
                } else {
                    url = "{{ route('wfg.stock_opname.save-temp') }}";
                    data = {
                        soh_id,
                        barang_id,
                        qty_full,
                        qty_receh,
                        summary,
                        principal,
                        _token: '{{ csrf_token() }}'
                    };
                }

                $.ajax({
                    url,
                    type: "POST",
                    data,
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Tersimpan!',
                                text: res.message,
                                timer: 1000,
                                showConfirmButton: false
                            });

                            row.find('.qty_full').val('');
                            row.find('.qty_receh').val('');

                            loadAllTempData();
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Gagal',
                                text: res.message || 'Data tidak berhasil disimpan.'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan pada server.'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false)
                            .html('<i class="mdi mdi-content-save-outline me-1"></i> Simpan');
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
                    tempData.history.forEach(h => {
                        const created = new Date(h.updated_at).toLocaleString('id-ID', {
                            dateStyle: 'medium',
                            timeStyle: 'short'
                        });
                        historyHtml += `
                            <div class="col-md-3 col-6">
                                <div class="p-2 border rounded h-100 fade show">
                                    <div class="fw-semibold text-dark mb-1">
                                        Full: ${h.qty_full}, Receh: ${h.qty_receh}
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
            $('#formSopFinal').on('submit', function(e) {
                e.preventDefault();

                const tgl_opname = $('#tgl_opname').val();
                const principal = $('#principal_filter').val();

                if (!tgl_opname) {
                    Swal.fire('Peringatan', 'Tanggal opname wajib diisi!', 'warning');
                    return;
                } else if (!tgl_opname) {
                    Swal.fire('Peringatan', 'principal wajib diisi!', 'warning');
                    return;
                }

                let keteranganData = {};
                $('#tableInputOpname tbody tr').each(function() {
                    const barangId = $(this).data('barangid');
                    const keterangan = $(this).find('.keterangan-input').val() || '';
                    keteranganData[barangId] = keterangan;
                });

                Swal.fire({
                    title: 'Konfirmasi Simpan Final',
                    text: 'Data akan diverifikasi dan disimpan. Lanjutkan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wfg.stock_opname.save-final') }}",
                            method: 'POST',
                            data: {
                                _token: $('input[name="_token"]').val(),
                                tgl_opname: tgl_opname,
                                keterangan: keteranganData,
                                principal: principal
                            },
                            success: function(res) {
                                if (res.status === 'success') {
                                    Swal.fire('Berhasil!', res.message, 'success').then(
                                        () => {
                                            loadBarangForOpname();
                                        });
                                } else if (res.status === 'warning') {
                                    const table = $('#tableInputOpname');
                                    if (table.find('th.keterangan-header').length ===
                                        0) {
                                        table.find('thead tr').append(
                                            '<th class="keterangan-header text-center">Keterangan</th>'
                                        );
                                    }

                                    res.data.forEach(item => {
                                        const row = $(
                                            `tr[data-barangid="${item.barang_id}"]`
                                        );
                                        // row.addClass('table-warning');

                                        // Tambahkan <td> input keterangan jika belum ada
                                        if (row.find('td.keterangan-cell')
                                            .length === 0) {
                                            row.append(`
                                                <td class="keterangan-cell text-center">
                                                    <input type="text" class="form-control keterangan-input" placeholder="Isi keterangan...">
                                                </td>
                                            `);

                                        }
                                    });

                                    // ⚠️ Tampilkan daftar barang yang punya selisih
                                    let listHtml = '<ul class="text-start">';
                                    res.data.forEach(item => {
                                        // $(`tr[data-barangid="${item.barang_id}"]`)
                                        //     .addClass('table-warning');

                                        listHtml += `
                                            <li class="mb-2">
                                                <strong>${item.mid_barang}</strong> (${item.nama_barang})<br>
                                                SAP: ${item.qty_sap}, Fisik: ${item.qty_fisik}, 
                                                <span class="text-danger">Selisih: ${item.selisih}</span>
                                            </li>
                                        `;
                                    });
                                    listHtml += '</ul>';

                                    Swal.fire({
                                        title: 'Terdapat Selisih!',
                                        html: `<p>${res.message}</p>${listHtml}`,
                                        icon: 'warning',
                                        width: 700,
                                        confirmButtonText: 'Perbaiki Data',
                                    });
                                } else {
                                    Swal.fire('Gagal', res.message ||
                                        'Terjadi kesalahan.', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message ||
                                    'Gagal menyimpan data.', 'error');
                            }
                        });
                    }
                });
            });

            // Tombol Edit
            $('#soInputTableContainer').on('click', '.btn-edit-temp', function() {
                const row = $(this).closest('tr.soh-row');
                const barangId = row.data('barangid');

                $.ajax({
                    url: `{{ url('api/wfg/sop/getDataTempEdit') }}/` + barangId,
                    type: 'GET',
                    success: function(res) {
                        if (res.status === 'success') {
                            const items = res.data; // array data temp
                            let html = '';

                            items.forEach(item => {
                                // Format tanggal
                                const dateObj = new Date(item.updated_at.replace(' ',
                                    'T')); // safer
                                const options = {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                };
                                const formattedDate = dateObj.toLocaleString('id-ID',
                                    options);

                                html += `
                                    <div class="mb-3 border p-2 rounded temp-item" data-tempid="${item.id}">
                                        <input type="hidden" class="temp_id" value="${item.id}">
                                        <p><strong>MID: ${item.barang.mid_barang} - ${formattedDate}</strong></p>
                                        <label>Qty Full</label>
                                        <input type="number" class="form-control qty_full mb-2" value="${item.qty_full}">
                                        <label>Qty Receh</label>
                                        <input type="number" class="form-control qty_receh mb-2" value="${item.qty_receh}">
                                        <button type="button" class="btn btn-danger btn-sm btn-delete-temp mt-1">
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
            });

            $('#editModal').on('click', '.btn-delete-temp', function() {
                const container = $(this).closest('.temp-item');
                const tempId = container.data('tempid');

                Swal.fire({
                    title: 'Hapus data?',
                    text: "Data akan dihapus permanen!",
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
                                _token: $('input[name="_token"]').val()
                            },
                            success: function(res) {
                                if (res.status === 'success') {
                                    Swal.fire('Terhapus!', res.message, 'success');
                                    container.remove(); // hapus dari DOM
                                } else {
                                    Swal.fire('Error', res.message ||
                                        'Gagal menghapus data', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Gagal menghapus data', 'error');
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
                    const keterangan = $(this).find('.keterangan-input').val();

                    updates.push({
                        id: tempId,
                        qty_full: qtyFull,
                        qty_receh: qtyReceh,
                        keterangan: keterangan
                    });
                });

                $.ajax({
                    url: "{{ route('wfg.stock_opname.update-temp') }}",
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        updates
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Berhasil', res.message, 'success');
                            $('#editModal').modal('hide');
                            loadBarangForOpname(); // refresh tabel
                        } else {
                            Swal.fire('Error', res.message || 'Gagal menyimpan data', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menyimpan data', 'error');
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
                                    Swal.fire('Berhasil', res.message, 'success');
                                } else if (res.status === 'info') {
                                    Swal.fire('Info', res.message, 'info');
                                } else {
                                    Swal.fire('Error', res.message, 'error');
                                }

                                loadBarangForOpname();
                            },
                            error: function(xhr) {
                                Swal.fire('Error', 'Gagal menghapus data sementara.',
                                    'error');
                                console.error(xhr.responseText);
                            }
                        });
                    }
                });
            });

            // --- Event Reset per Row ---
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
                                    const collapseRow = $(`#history-${row.data('id')}`);
                                    collapseRow.find('td').html(
                                        '<em class="text-muted">Belum ada riwayat input.</em>'
                                    );

                                    // Hapus cache di client
                                    delete window.tempBatchCache[key];

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Data opname barang berhasil direset.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: res.message ||
                                            'Gagal reset data opname.'
                                    });
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
                    mid_barang: form.find('[name="mid_barang"]').val().trim(),
                    nama_barang: form.find('[name="nama_barang"]').val().trim(),
                    uom: form.find('[name="uom"]').val().trim(),
                    qty_box: parseInt(form.find('[name="qty_box"]').val()) || 1,
                    qty_full: parseInt(form.find('[name="qty_full"]').val()) || 0,
                    qty_receh: parseInt(form.find('[name="qty_receh"]').val()) || 0,
                    principal: $('#principal_filter').val() || null,
                    _token: '{{ csrf_token() }}'
                };

                // Validasi form
                if (!data.mid_barang) {
                    Swal.fire('Peringatan', 'MID Barang wajib diisi.', 'warning');
                    return;
                }

                if (data.qty_full === 0 && data.qty_receh === 0) {
                    Swal.fire('Peringatan', 'Isi minimal salah satu Qty Full atau Qty Receh.', 'warning');
                    return;
                }

                // Hitung summary
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
                            form.trigger('reset'); // kosongin form
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

        function loadAllTempData() {
            const rows = $('.soh-row');
            const keyIds = [];

            rows.each(function() {
                const sohId = $(this).data('sohid');
                const barangId = $(this).data('barangid');

                // gunakan soh_id jika ada, kalau tidak ada ambil barang_id
                const key = sohId ? `soh_${sohId}` : `barang_${barangId}`;
                keyIds.push(key);
            });

            const uniqueKeys = [...new Set(keyIds)];
            if (uniqueKeys.length === 0) return;

            // Pisahkan jadi dua array: soh_ids dan barang_ids
            const sohIds = [];
            const barangIds = [];
            uniqueKeys.forEach(k => {
                if (k.startsWith('soh_')) sohIds.push(parseInt(k.replace('soh_', '')));
                else if (k.startsWith('barang_')) barangIds.push(parseInt(k.replace('barang_', '')));
            });

            console.log("Load temp data:", {
                sohIds,
                barangIds
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
                            // grouping berdasarkan soh_id kalau ada, kalau tidak pakai barang_id
                            const groupingKey = tempRecord.soh_id ? `soh_${tempRecord.soh_id}` :
                                `barang_${tempRecord.barang_id}`;
                            const summary = parseInt(tempRecord.summary) || 0;

                            if (!groupedData[groupingKey]) {
                                groupedData[groupingKey] = {
                                    total_summary: 0,
                                    history: []
                                };
                            }

                            groupedData[groupingKey].total_summary += summary;
                            groupedData[groupingKey].history.push(tempRecord);
                        });

                        // update tampilan summary di tabel
                        for (const key in groupedData) {
                            const tempItem = groupedData[key];
                            const totalSummary = tempItem.total_summary;

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
                                row.find('.summary-cell').text(totalSummary.toLocaleString('id-ID'));
                            }

                            window.tempBatchCache[key] = {
                                total_summary: totalSummary,
                                history: tempItem.history
                            };
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
