@extends('layouts.app')

@section('title', '| Form SO WCP')
@section('sidebar-size', 'sm')

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

        .table-scroll {
            max-height: 70vh;
            overflow-y: auto;
            overflow-x: auto;
        }

        #tableInputOpname thead th {
            position: sticky;
            top: 0;
            z-index: 200;
            background: #f3f6f9;
        }

        html[data-layout-mode="dark"] #tableInputOpname thead th {
            background: #2f333a;
            color: #e9ecef;
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

        /* Smooth Collapse Animation & Styling */
        .collapsing {
            transition: height 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .collapse {
            transition: height 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .history-card {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid #3b82f6 !important;
        }

        .history-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15) !important;
        }

        /* Scrollable container for history inputs */
        .history-list-wrapper {
            max-height: 220px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 5px;
        }

        /* Custom scrollbar for premium aesthetics */
        .history-list-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        .history-list-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }

        .history-list-wrapper::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 4px;
        }

        .history-list-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        html[data-layout-mode="dark"] .history-list-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
        }

        html[data-layout-mode="dark"] .history-list-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0"><i class="mdi mdi-upload me-2"></i>Form Stock Opname WCP</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WCP</a></li>
                                <li class="breadcrumb-item active">Stock Opname</li>
                                <li class="breadcrumb-item active">Form Stock Opname</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="card shadow-sm border-0 rounded-3 mb-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="tgl_opname" class="form-label fw-semibold">
                                Tanggal Opname
                            </label>
                            <input type="date" id="tgl_opname" name="tgl_opname" class="form-control"
                                value="{{ now()->toDateString() }}" disabled>
                        </div>

                        <div class="col-md-3">
                            <label for="jenis_so" class="form-label fw-semibold">
                                Jenis SO
                            </label>
                            <select id="jenis_so" name="jenis_so" class="form-select">
                                <option value="cycle_count">Cycle Count (Daily)</option>
                                <option value="monthly">Monthly SO</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-success" id="btnStartOpname">
                                    <i class="mdi mdi-play-circle me-1"></i> Mulai Opname
                                </button>

                                <button type="button" class="btn btn-success d-none" id="btnSaveFinal">
                                    <i class="mdi mdi-content-save-outline me-1"></i> Periksa & Kirim
                                </button>

                                <button type="button" class="btn btn-info d-none" id="btnAddRow">
                                    <i class="mdi mdi-plus-circle-outline me-1"></i> Tambah Item
                                </button>
                            </div>
                        </div>

                        {{-- <div class="col-md-3">
                            <label class="form-label fw-semibold">Search</label>

                            <div class="position-relative d-none" id="searchContainer">
                                <input type="text" id="searchBarang" class="form-control ps-5"
                                    placeholder="Ketik MID atau nama barang...">

                                <i
                                    class="mdi mdi-magnify position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>

            <!-- Active Form Section -->
            <div class="card shadow-sm border-0 rounded-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body p-3">
                    <form id="formSoFinal">
                        @csrf
                        <div id="soInputTableContainer" class="table-scroll">
                            <div class="text-center py-5 text-muted" id="idlePlaceholder">
                                <i class="mdi mdi-play-circle-outline display-3 text-primary mb-3 d-block"></i>
                                <h4>Opname belum dimulai</h4>
                                <p>Klik tombol <strong>Mulai Opname</strong> di atas untuk memulai sesi perhitungan hari
                                    ini.</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Item Baru (Manual SOH addition on-the-fly) -->
    <div class="modal fade" id="modalAddItem" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white"><i class="mdi mdi-plus-circle-outline me-2"></i>Tambah Item Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formAddItem">
                        @csrf
                        <div class="p-3 rounded-3 mb-3 bg-light">
                            <h6 class="fw-bold mb-3 text-secondary"><i class="mdi mdi-database-outline me-1"></i> Data
                                Barang</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Pilih MID Barang</label>
                                    <select class="form-select select2" id="mid_barang" name="mid_barang" required
                                        style="width:100%">
                                        <option value="">-- Pilih MID --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 rounded-3 mb-3 bg-light-subtle border">
                            <h6 class="fw-bold mb-3 text-danger"><i class="mdi mdi-file-document-outline me-1"></i> Saldo
                                System (SOH)</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">UNREST</label>
                                    <input type="number" class="form-control" name="unrest" value="0" min="0"
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">QI</label>
                                    <input type="number" class="form-control" name="qi" value="0"
                                        min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">BLOCKED</label>
                                    <input type="number" class="form-control" name="blocked" value="0"
                                        min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 rounded-3 bg-light border-warning border">
                            <h6 class="fw-bold mb-3 text-warning"><i
                                    class="mdi mdi-checkbox-marked-circle-outline me-1"></i> Perhitungan Fisik</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Qty Fisik</label>
                                    <input type="number" class="form-control" name="qty_receh" value="0"
                                        min="0" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info text-white" id="btnSaveNewItem">
                        <i class="mdi mdi-content-save-outline me-1"></i> Tambahkan
                    </button>
                </div>
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
                    <!-- Data edit akan dimasukkan di sini via AJAX -->
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
            // Initialize select2 on modal elements
            $('#mid_barang').select2({
                dropdownParent: $('#modalAddItem')
            });

            function updateTglOpnameForm() {
                const jenis = $('#jenis_so').val();
                const tglInput = $('#tgl_opname');
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');

                if (jenis === 'monthly') {
                    tglInput.attr('type', 'month').val(`${year}-${month}`);
                } else {
                    tglInput.attr('type', 'date').val(`${year}-${month}-${day}`);
                }
            }

            updateTglOpnameForm();
            checkSessionStatus();

            // Trigger checkSessionStatus when jenis_so changes
            $('#jenis_so').on('change', function() {
                updateTglOpnameForm();
                checkSessionStatus();
            });

            $('#btnStartOpname').on('click', function() {
                const btn = $(this);
                const jenisSo = $('#jenis_so').val();
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Memulai...');

                $.ajax({
                    url: "{{ route('wcp.stock_opname.startOpname') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        jenis_so: jenisSo
                    },
                    success: function(res) {
                        if (res.status) {
                            toastr.success(res.message);
                            checkSessionStatus();
                        } else {
                            toastr.warning(res.message);
                            btn.prop('disabled', false).html(
                                '<i class="mdi mdi-play-circle me-1"></i> Mulai Opname');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Gagal memulai opname.');
                        }
                        btn.prop('disabled', false).html(
                            '<i class="mdi mdi-play-circle me-1"></i> Mulai Opname');
                    }
                });
            });

            $('#searchBarang').on('keyup', function() {
                const search = $(this).val().toLowerCase();
                $('.soh-row').each(function() {
                    const rowText = $(this).text().toLowerCase();
                    if (rowText.includes(search)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            $('#btnAddRow').on('click', function() {
                loadBarangOptions(() => {
                    const modalEl = document.getElementById('modalAddItem');
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(
                        modalEl);
                    modal.show();
                });
            });

            $('#btnSaveNewItem').on('click', function() {
                const qtyRecehVal = parseInt($('#formAddItem [name="qty_receh"]').val()) || 0;

                if (qtyRecehVal < 0) {
                    toastr.warning('Jumlah kuantitas tidak boleh negatif/minus!');
                    return;
                }

                if (qtyRecehVal === 0) {
                    toastr.warning(
                        'Kuantitas tidak boleh 0!'
                    );
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

                let formData = $('#formAddItem').serialize();
                formData += '&jenis_so=' + encodeURIComponent($('#jenis_so').val());

                $.ajax({
                    url: "{{ route('wcp.stock_opname.save-temp-new') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {
                        btn.prop('disabled', false).html(
                            '<i class="mdi mdi-content-save-outline me-1"></i> Tambahkan');
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            const modalEl = document.getElementById('modalAddItem');
                            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap
                                .Modal(modalEl);
                            modal.hide();
                            $('#formAddItem')[0].reset();
                            loadItems();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(
                            '<i class="mdi mdi-content-save-outline me-1"></i> Tambahkan');
                        const err = xhr.responseJSON;
                        Swal.fire('Gagal', err.message || 'Gagal menambahkan barang baru',
                            'error');
                    }
                });
            });

            $('#btnSaveFinal').on('click', function() {
                const btn = $(this);
                const jenisSo = $('#jenis_so').val();
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>Memeriksa...');

                // Run precheck validation
                $.ajax({
                    url: "{{ route('wcp.stock_opname.process') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        tgl_opname: "{{ now()->toDateString() }}",
                        mode: 'check',
                        jenis_so: jenisSo
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html(
                            '<i class="mdi mdi-content-save-outline me-1"></i> Periksa & Kirim'
                        );

                        // Update indicators based on variance status
                        if (res.analysis && Array.isArray(res.analysis)) {
                            res.analysis.forEach(item => {
                                const row = $(`.soh-row[data-id="${item.soh_id}"]`);
                                if (row.length) {
                                    const dot = row.find('.diff-indicator-dot');
                                    dot.removeClass(
                                        'bg-secondary bg-success bg-danger bg-warning'
                                    );
                                    if (item.selisih === 0 || (item.keterangan && item
                                            .keterangan.trim() !== '')) {
                                        dot.addClass('bg-success');
                                    } else if (item.status === 'lebih') {
                                        dot.addClass('bg-warning');
                                    } else {
                                        dot.addClass('bg-danger');
                                    }
                                }
                            });
                        }

                        if (res.status === 'success') {
                            // Perfect count / explained. Show analysis summary of differences.
                            let diffList = res.analysis.filter(item => item.selisih !== 0).map(
                                item =>
                                `<tr>
                                    <td class="font-monospace">${item.mid}</td>
                                    <td class="text-end font-monospace">${item.qty_sistem.toLocaleString('id-ID')}</td>
                                    <td class="text-end font-monospace">${item.qty_fisik.toLocaleString('id-ID')}</td>
                                    <td class="text-center font-monospace fw-bold ${item.selisih > 0 ? 'text-warning' : 'text-danger'}">
                                        ${item.selisih > 0 ? '+' : ''}${item.selisih.toLocaleString('id-ID')}
                                    </td>
                                    <td>${item.keterangan ? item.keterangan : '-'}</td>
                                </tr>`
                            ).join('');

                            let htmlContent =
                                '<p class="text-muted small mb-3">Berikut adalah ringkasan perbedaan (selisih) perhitungan fisik vs sistem untuk hari ini:</p>';
                            if (diffList === '') {
                                htmlContent +=
                                    '<div class="alert alert-success border-0 small mb-3"><i class="mdi mdi-check-circle-outline me-1"></i>Semua item cocok (tidak ada selisih).</div>';
                            } else {
                                htmlContent += `
                                    <div class="table-responsive mb-3" style="max-height: 250px;">
                                        <table class="table table-sm table-bordered text-start align-middle small mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>MID</th>
                                                    <th class="text-end">Sistem</th>
                                                    <th class="text-end">Fisik</th>
                                                    <th class="text-center">Selisih</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${diffList}
                                            </tbody>
                                        </table>
                                    </div>
                                `;
                            }
                            htmlContent +=
                                '<p class="mb-0 text-dark fw-semibold">Kirim hasil opname final sekarang?</p>';

                            Swal.fire({
                                title: 'Kirim Final Stock Opname?',
                                html: `<div class="text-start">${htmlContent}</div>`,
                                icon: 'question',
                                width: '650px',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Kirim',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#10b981'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: "{{ route('wcp.stock_opname.process') }}",
                                        method: "POST",
                                        data: {
                                            _token: "{{ csrf_token() }}",
                                            tgl_opname: "{{ now()->toDateString() }}",
                                            mode: 'final_prepare',
                                            jenis_so: jenisSo
                                        },
                                        success: function(finalRes) {
                                            if (finalRes.status ===
                                                'need_comment') {
                                                Swal.fire({
                                                    title: "Tambahkan Komentar",
                                                    input: "textarea",
                                                    inputLabel: "Komentar",
                                                    inputPlaceholder: "Tuliskan komentar di sini...",
                                                    inputAttributes: {
                                                        rows: 4
                                                    },
                                                    showCancelButton: true,
                                                    confirmButtonText: "Lanjutkan Submit"
                                                }).then(resComment => {
                                                    if (!resComment
                                                        .isConfirmed
                                                    ) return;
                                                    submitFinal(
                                                        resComment
                                                        .value);
                                                });
                                            } else {
                                                Swal.fire('Error', finalRes
                                                    .message, 'error');
                                            }
                                        },
                                        error: function(xhr) {
                                            Swal.fire('Error', xhr
                                                .responseJSON
                                                ?.message ||
                                                'Gagal menyiapkan data final.',
                                                'error');
                                        }
                                    });
                                }
                            });
                        } else if (res.status === 'warning') {
                            // Uncounted items
                            let list = res.uncounted.map(item =>
                                `<li>MID: ${item.mid} | SOH: ${item.qty_system}</li>`
                            ).join('');
                            let listSelisih = res.issues.map(item =>
                                `<li>MID: ${item.mid} | <span class="text-danger fw-semibold">Selisih: ${item.selisih}</span></li>`
                            ).join('');
                            Swal.fire({
                                title: 'Ada item belum di-opname!',
                                html: `<div class="text-start"><p>Berikut item dengan selisih:</p><div class="d-block"><ul>${listSelisih}</ul></div><p>Berikut item yang belum diisi nilainya:</p><div class="d-block"><ul>${list}</ul></div><p>Semua item wajib diisi sebelum submit final.</p></div>`,
                                icon: 'warning'
                            });
                        } else if (res.status === 'variance_unexplained') {
                            // Variance but missing explanation
                            let list = res.issues.map(item =>
                                `<li><span class="text-danger fw-semibold">MID: ${item.mid} | Selisih: ${item.selisih}</span></li>`
                            ).join('');
                            Swal.fire({
                                title: 'Catatan Selisih Diperlukan!',
                                html: `<div class="text-start"><p>Ada item dengan selisih:</p><ul>${list}</ul><p>Silakan isi kolom Catatan pada baris tersebut terlebih dahulu.</p></div>`,
                                icon: 'warning'
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html(
                            '<i class="mdi mdi-content-save-outline me-1"></i> Periksa & Kirim'
                        );
                        toastr.error('Gagal melakukan verifikasi.');
                    }
                });
            });
        });

        function checkSessionStatus() {
            const jenisSo = $('#jenis_so').val();
            $.get("{{ route('wcp.stock_opname.getStatusOpname') }}", {
                jenis_so: jenisSo
            }, function(res) {
                if (res.status === 'started') {
                    $('#jenis_so').prop('disabled', true);
                    if (!res.is_owner) {
                        $('#btnStartOpname').removeClass('d-none').prop('disabled', true).addClass('btn-secondary')
                            .html('<i class="mdi mdi-clock-outline me-1"></i> Sedang Opname');
                        $('#btnSaveFinal').addClass('d-none');
                        $('#btnAddRow').addClass('d-none');
                        $('#searchContainer').addClass('d-none');
                        $('#soInputTableContainer').html(`
                            <div class="text-center py-5">
                                <i class="mdi mdi-clock-outline text-warning display-3 mb-3 d-block"></i>
                                <h4>Stock Opname Sedang Berjalan</h4>
                                <p>Proses stock opname hari ini sedang dilakukan oleh <strong>${res.started_by}</strong>.</p>
                            </div>
                        `);
                    } else {
                        $('#btnStartOpname').addClass('d-none');
                        $('#btnSaveFinal').removeClass('d-none');
                        $('#btnAddRow').removeClass('d-none');
                        $('#searchContainer').removeClass('d-none');
                        loadItems();
                    }
                } else if (res.status === 'finished') {
                    $('#jenis_so').prop('disabled', false);
                    $('#btnStartOpname').removeClass('d-none').prop('disabled', true).addClass('btn-secondary')
                        .html('<i class="mdi mdi-check-circle-outline me-1"></i> Opname Selesai');
                    $('#btnSaveFinal').addClass('d-none');
                    $('#btnAddRow').addClass('d-none');
                    $('#searchContainer').addClass('d-none');
                    $('#soInputTableContainer').html(`
                        <div class="text-center py-5">
                            <i class="mdi mdi-check-circle-outline text-success display-3 mb-3 d-block"></i>
                            <h4>Stock Opname Selesai</h4>
                            <p>Seluruh perhitungan fisik hari ini telah disimpan final. Silakan lihat di tab <strong>SO Report</strong>.</p>
                        </div>
                    `);
                } else {
                    // idle
                    $('#jenis_so').prop('disabled', false);
                    $('#btnStartOpname').removeClass('d-none').prop('disabled', false).removeClass('btn-secondary')
                        .addClass('btn-outline-success').html(
                            '<i class="mdi mdi-play-circle me-1"></i> Mulai Opname');
                    $('#btnSaveFinal').addClass('d-none');
                    $('#btnAddRow').addClass('d-none');
                    $('#searchContainer').addClass('d-none');
                    $('#soInputTableContainer').html(`
                        <div class="text-center py-5 text-muted">
                            <h4>Silakan Klik Mulai Opname</h4>
                            <p>Pilih Jenis SO dan klik tombol Mulai Opname di atas untuk memulai input data fisik.</p>
                        </div>
                    `);
                }
            });
        }

        function loadItems() {
            const container = $('#soInputTableContainer');
            const jenisSo = $('#jenis_so').val();
            container.html(`
                    <div class="text-center py-5 text-primary">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="fw-semibold">Mengambil data opname Co Product...</p>
                    </div>
                `);

            $.ajax({
                url: "{{ route('wcp.stock_opname.getData') }}",
                type: "GET",
                data: {
                    jenis_so: jenisSo
                },
                success: function(res) {
                    if (res.status === 'success' && res.data.length > 0) {
                        let html = `
                            <table class="table align-middle mb-0 text-nowrap" id="tableInputOpname">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th class="text-start">MID (Barang)</th>
                                        <th style="width: 150px;">Qty Fisik</th>
                                        <th>Summary</th>
                                        <th class="d-none">Selisih</th>
                                        <th>Catatan</th>
                                        <th style="width: 80px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        res.data.forEach((item, index) => {
                            const isCounted = item.qty_receh !== null;
                            const summaryVal = isCounted ? item.summary : 0;
                            const diffVal = isCounted ? (summaryVal - item.qty_soh) : 0;
                            const notesVal = item.catatan ? item.catatan : '';

                            let badgeColor = 'bg-secondary';
                            if (isCounted) {
                                if (diffVal === 0 || (notesVal && notesVal.trim() !== '')) {
                                    badgeColor = 'bg-success';
                                } else if (diffVal > 0) {
                                    badgeColor = 'bg-warning';
                                } else {
                                    badgeColor = 'bg-danger';
                                }
                            }

                            html += `
                                <tr class="soh-row align-middle" data-id="${item.soh_id}" data-qty-pallet="${item.qty_pallet}" data-qty-soh="${item.qty_soh}" data-summary-db="${summaryVal}">
                                    <td class="text-center font-semibold">${index + 1}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="diff-indicator-dot me-2 rounded-circle ${badgeColor}" style="width: 10px; height: 10px; min-width: 10px; display: inline-block;" title="Status Selisih"></span>
                                            <div>
                                                <span class="d-block fw-bold">${item.mid}</span>
                                                <span class="d-block text-muted small">${item.nama_barang}</span>
                                                
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" class="form-control text-center qty-receh" value="" min="0" placeholder="0">
                                    </td>
                                    <td class="text-end fw-bold physical-summary">${isCounted ? summaryVal.toLocaleString('id-ID') : '-'}</td>
                                    <td class="text-center d-none">
                                        <span class="badge ${badgeColor} px-2 py-1 diff-value">${isCounted ? diffVal.toLocaleString('id-ID') : '-'}</span>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control notes-field" value="${notesVal}" placeholder="Catatan selisih...">
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-history me-1" data-bs-toggle="collapse" data-bs-target="#history-${item.soh_id}" title="Riwayat Input">
                                            <i class="mdi mdi-history"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editTemp(${item.soh_id})" title="Edit Detail">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="resetRow(${item.soh_id})" title="Reset Baris">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="history-${item.soh_id}">
                                    <td colspan="8" class="p-3 text-muted small text-start history-container">
                                        <em>Belum ada riwayat input.</em>
                                    </td>
                                </tr>
                            `;
                        });

                        html += '</tbody></table>';
                        container.html(html);

                        // Attach event listeners for dynamic updates
                        attachRowEvents();

                        // Load all temp data batches
                        loadAllTempData();
                    } else {
                        container.html(`
                            <div class="text-center py-5 text-muted">
                                <h4>Data SOH kosong</h4>
                                <p>Silakan unggah data SOH Co Product terlebih dahulu sebelum melakukan opname.</p>
                            </div>
                        `);
                    }
                }
            });
        }

        function attachRowEvents() {
            // Enter key triggers blur (which triggers change)
            $('.qty-receh, .notes-field').on('keypress', function(e) {
                if (e.which === 13) {
                    $(this).blur();
                }
            });

            // Live validation & preview on input
            $('.qty-receh, .notes-field').on('input', function() {
                const row = $(this).closest('tr');
                const dbSummary = parseFloat(row.attr('data-summary-db')) || 0;

                const recehValStr = row.find('.qty-receh').val();

                if (recehValStr !== '' && parseInt(recehValStr) < 0) {
                    toastr.warning('Jumlah kuantitas tidak boleh negatif/minus!');
                    $(this).val('');
                    return;
                }

                const receh = parseInt(recehValStr) || 0;

                if (receh === 0 && recehValStr !== '') {
                    toastr.warning('Kuantitas tidak boleh 0!');
                    row.find('.qty-receh').val('');
                    return;
                }

                // Calculate preview values
                const physicalSummary = dbSummary + receh;
                row.find('.physical-summary').text(physicalSummary > 0 ? physicalSummary.toLocaleString('id-ID') :
                    '-');
            });

            // Save on change (blur / enter)
            $('.qty-receh, .notes-field').on('change', function() {
                const row = $(this).closest('tr');
                const sohId = row.data('id');

                const recehValStr = row.find('.qty-receh').val();
                const noteVal = row.find('.notes-field').val();

                // Don't save if all are empty
                if (recehValStr === '' && noteVal === '') {
                    return;
                }

                const receh = parseInt(recehValStr) || 0;

                if (receh === 0 && recehValStr !== '') {
                    return;
                }

                $.ajax({
                    url: "{{ route('wcp.stock_opname.save-temp') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        soh_id: sohId,
                        qty_receh: recehValStr !== '' ? receh : null,
                        keterangan: noteVal,
                        jenis_so: $('#jenis_so').val()
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            toastr.success('Perubahan draft disimpan.', '', {
                                timeOut: 800
                            });
                            if (recehValStr !== '') {
                                row.find('.qty-receh').val('');
                            }
                            loadAllTempData();
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan data.');
                    }
                });
            });
        }

        function resetRow(sohId) {
            Swal.fire({
                title: 'Reset Baris Ini?',
                text: 'Draft perhitungan fisik pada baris ini akan dikosongkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reset',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59e0b'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('wcp.stock_opname.reset-temp-row') }}",
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}",
                            soh_id: sohId,
                            jenis_so: $('#jenis_so').val()
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                toastr.success(res.message);
                                loadItems();
                            }
                        }
                    });
                }
            });
        }

        function submitFinal(komentarFinal) {
            $.ajax({
                url: "{{ route('wcp.stock_opname.process') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    tgl_opname: "{{ now()->toDateString() }}",
                    mode: 'final_submit',
                    komentar_final: komentarFinal,
                    jenis_so: $('#jenis_so').val()
                },
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Berhasil!', res.message, 'success').then(() => {
                            checkSessionStatus();
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    const err = xhr.responseJSON;
                    Swal.fire('Gagal', err.message || 'Gagal mengirim stock opname', 'error');
                }
            });
        }

        function loadBarangOptions(callback) {
            const select = $('#mid_barang');
            select.html('<option value="">Memuat data...</option>');

            $.ajax({
                url: "{{ route('wcp.stock_opname.soh.getBarang') }}",
                type: 'GET',
                success: function(response) {
                    select.html('<option value="">-- Pilih MID --</option>');
                    if (response.status === 'success' && response.data.length > 0) {
                        response.data.forEach(function(item) {
                            select.append(
                                `<option value="${item.mid}">${item.mid} - ${item.nama_barang} (${item.uom})</option>`
                            );
                        });
                    }
                    if (typeof callback === 'function') callback();
                }
            });
        }

        function loadAllTempData() {
            const rows = $('.soh-row');
            const sohIds = [];

            rows.each(function() {
                const sohId = $(this).data('id');
                if (sohId) sohIds.push(sohId);
            });

            if (sohIds.length === 0) return;

            $.ajax({
                url: "{{ route('wcp.stock_opname.getTempBatch') }}",
                type: "GET",
                data: {
                    soh_ids: sohIds,
                    jenis_so: $('#jenis_so').val()
                },
                success: function(res) {
                    if (res.status === "success" && Array.isArray(res.data)) {
                        window.tempBatchCache = {};
                        const groupedData = {};

                        res.data.forEach(tempRecord => {
                            const groupingKey = `soh_${tempRecord.soh_id}`;

                            if (!groupedData[groupingKey]) {
                                groupedData[groupingKey] = {
                                    total_summary: 0,
                                    history: [],
                                    latestNote: null
                                };
                            }

                            const group = groupedData[groupingKey];

                            if (tempRecord.mode === 'note') {
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
                                group.history.push(tempRecord);
                                const summary = parseInt(tempRecord.summary) || 0;
                                group.total_summary += summary;
                            }
                        });

                        // Clear all summary cells, notes, and indicators in rows first (to avoid stale data)
                        rows.each(function() {
                            const row = $(this);
                            const uom = row.find('td:nth-child(2) .badge').text().split(' ').pop();
                            row.find('.physical-summary').text('- ' + uom);
                            row.attr('data-summary-db', 0);
                            row.find('.diff-indicator-dot').removeClass(
                                'bg-success bg-danger bg-warning').addClass('bg-secondary');
                        });

                        // Update table display
                        for (const key in groupedData) {
                            const tempItem = groupedData[key];
                            const totalSummary = tempItem.total_summary;
                            const latestNote = tempItem.latestNote ? tempItem.latestNote.text : '';

                            const sohId = key.replace('soh_', '');
                            const row = $(`.soh-row[data-id="${sohId}"]`);
                            if (row.length) {
                                const uom = row.find('td:nth-child(2) .badge').text().split(' ').pop();
                                row.find('.physical-summary').text(totalSummary > 0 ? totalSummary
                                    .toLocaleString('id-ID') + ' ' + uom : '- ' + uom);
                                row.attr('data-summary-db', totalSummary);

                                if (latestNote) {
                                    row.find('.notes-field').val(latestNote);
                                }

                                // Update indicator dot
                                const qtySoh = parseInt(row.attr('data-qty-soh')) || 0;
                                const diff = totalSummary - qtySoh;
                                const dot = row.find('.diff-indicator-dot');
                                dot.removeClass('bg-secondary bg-success bg-danger bg-warning');
                                if (diff === 0 || (latestNote && latestNote.trim() !== '')) {
                                    dot.addClass('bg-success'); // match or explained
                                } else if (diff > 0) {
                                    dot.addClass('bg-warning'); // lebih
                                } else {
                                    dot.addClass('bg-danger'); // kurang
                                }

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

        function editTemp(sohId) {
            $.ajax({
                url: "{{ route('wcp.stock_opname.getDataTempEdit', ':id') }}".replace(':id', sohId),
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const items = res.data_qty || [];
                        const note = res.data_note ? res.data_note.catatan : '';

                        let html = '';

                        items.forEach((item, index) => {
                            const created = new Date(item.updated_at).toLocaleString('id-ID', {
                                dateStyle: 'medium',
                                timeStyle: 'short'
                            });

                            const qtyPallet = item.barang?.qty_pallet ?? 1;

                            html += `
                                <div class="mb-3 border border-info p-3 rounded temp-item bg-light" data-id="${item.id}" data-qty-pallet="${qtyPallet}">
                                    <input type="hidden" class="temp_id" value="${item.id}">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <p class="mb-0 fw-semibold text-dark">
                                            Input ke-${index + 1} (${created})
                                        </p>
                                        <span class="badge bg-info">Qty</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-12">
                                            <label class="form-label small mb-1">Qty Fisik</label>
                                            <input type="number" class="form-control qty_receh" value="${item.qty_receh}" min="0">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-temp mt-2" data-type="qty" data-id="${item.id}">
                                        <i class="mdi mdi-delete"></i> Hapus
                                    </button>
                                </div>
                            `;
                        });

                        // Catatan
                        html += `
                                <hr>
                                <div class="mb-3 border border-warning p-3 rounded temp-note" data-sohid="${sohId}">
                                    <label class="form-label fw-semibold">Catatan Selisih</label>
                                    <textarea class="form-control temp_note mb-2" rows="3" placeholder="Alasan jika ada selisih...">${note}</textarea>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-temp" data-type="note" data-id="${sohId}">
                                        <i class="mdi mdi-delete"></i> Hapus Catatan
                                    </button>
                                </div>
                            `;

                        $('#editModal .modal-body').html(html);
                        const modalEl = document.getElementById('editModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        toastr.error('Gagal memuat data edit.');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        }

        // Collapse events to show/hide history rows
        $(document).on('shown.bs.collapse', '.collapse', function() {
            const row = $(this).prev('.soh-row');
            const sohId = row.data('id');
            const key = `soh_${sohId}`;
            const tempData = window.tempBatchCache?.[key];
            const container = $(this).find('.history-container');

            if (tempData?.history?.length > 0) {
                let historyHtml =
                    `<div class="border rounded-3 p-3 shadow-sm history-list-wrapper"><div class="row g-2">`;
                tempData.history.forEach((h, index) => {
                    const created = new Date(h.updated_at).toLocaleString('id-ID', {
                        dateStyle: 'medium',
                        timeStyle: 'short'
                    });
                    historyHtml += `
                        <div class="col-md-3">
                            <div class="p-2 border border-info rounded h-100 bg-light fade show history-card">
                                <div class="fw-semibold text-dark mb-1 d-flex justify-content-between align-items-center">
                                    <span>Fisik: ${h.qty_receh}</span>
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
                container.hide().html(historyHtml).fadeIn(150);
            } else {
                container.html('<em class="text-muted">Belum ada riwayat input.</em>');
            }
        });

        $(document).on('hidden.bs.collapse', '.collapse', function() {
            $(this).find('.history-container').html('<em>Belum ada riwayat input.</em>');
        });

        // Delete button click handler inside edit modal
        $(document).on('click', '#editModal .btn-delete-temp', function() {
            const btn = $(this);
            const type = btn.data('type'); // 'qty' atau 'note'
            const id = btn.data('id');
            const container = btn.closest(type === 'qty' ? '.temp-item' : '.temp-note');

            Swal.fire({
                title: `Hapus ${type === 'note' ? 'catatan' : 'data qty'}?`,
                text: `Data ${type === 'note' ? 'catatan' : 'sementara'} ini akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('wcp.stock_opname.delete-temp', ':id') }}".replace(':id',
                            id),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}",
                            tipe: type
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                toastr.success(res.message, 'Terhapus');
                                container.remove();
                                loadAllTempData();
                            } else {
                                toastr.error(res.message || 'Gagal menghapus data', 'Error');
                            }
                        },
                        error: function() {
                            toastr.error('Gagal menghapus data', 'Error');
                        }
                    });
                }
            });
        });

        // Live check for negative inputs in edit modal
        $(document).on('input', '#editModal .qty_receh', function() {
            const val = $(this).val();
            if (val !== '' && parseInt(val) < 0) {
                toastr.warning('Jumlah kuantitas tidak boleh negatif/minus!');
                $(this).val('');
                return;
            }
        });

        // Save edit button click handler
        $('#saveEditBtn').on('click', function() {
            const updates = [];
            let hasNegative = false;
            let hasZeroBoth = false;

            $('#editModal .temp-item').each(function() {
                const tempId = $(this).find('.temp_id').val();
                const qtyReceh = $(this).find('.qty_receh').val();

                const qtyRecehVal = parseInt(qtyReceh) || 0;

                if (qtyRecehVal < 0) {
                    hasNegative = true;
                }

                if (qtyRecehVal === 0) {
                    hasZeroBoth = true;
                }

                updates.push({
                    id: tempId,
                    qty_receh: qtyReceh,
                });
            });

            if (hasNegative) {
                toastr.warning('Jumlah kuantitas tidak boleh negatif/minus!');
                return;
            }

            if (hasZeroBoth) {
                toastr.warning('Kuantitas tidak boleh 0!');
                return;
            }

            const catatan = $('#editModal .temp_note').val();

            $.ajax({
                url: "{{ route('wcp.stock_opname.update-temp') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    items: updates,
                    catatan: catatan
                },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message, 'Berhasil');
                        const modalEl = document.getElementById('editModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(
                            modalEl);
                        modal.hide();
                        loadAllTempData();
                    } else {
                        toastr.error(res.message || 'Gagal menyimpan data', 'Error');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan data', 'Error');
                    loadAllTempData();
                }
            });
        });
    </script>
@endsection
