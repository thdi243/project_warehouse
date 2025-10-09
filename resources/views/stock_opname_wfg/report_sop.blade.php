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
            <div class="card shadow-sm mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Filter Tanggal</label>
                            <div class="input-group">
                                <input type="date" id="filter_tanggal" class="form-control">
                                <button type="button" id="btn_filter" class="btn btn-primary">
                                    <i class="mdi mdi-filter me-2"></i>Filter
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" id="btn_reset" class="btn btn-soft-secondary w-100">
                                <i class="mdi mdi-undo me-2"></i> Reset
                            </button>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" id="btn_export" class="btn btn-soft-success w-100">
                                <i class="mdi mdi-export me-2"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle shadow-sm rounded-3" id="tableOpname">
                            <thead class="bg-light text-dark border-bottom">
                                <tr>
                                    <th class="text-center" style="width: 70px;">ID</th>
                                    <th>Tanggal Opname</th>
                                    <th>MID Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Qty Sistem</th>
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
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
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

    {{-- Modal export pdf --}}
    <div class="modal fade" id="exportDateModal" tabindex="-1" role="dialog" aria-labelledby="exportDateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportDateModalLabel">Pilih Tanggal Export</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="export_tanggal" class="form-label">Tanggal Opname</label>
                        <input type="date" id="export_tanggal" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn_confirm_export">Export PDF</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadReportData();

            $('#btn_filter').on('click', function() {
                loadReportData();
            });

            $('#btn_reset').on('click', function() {
                $('#filter_tanggal').val('');
                loadReportData();
            });

            function loadReportData() {
                $('#loading_state').show();
                $('#empty_state').hide();
                $('#tableBody').html('');

                $.ajax({
                    url: `{{ url('api/wfg/sop/report/getData') }}`,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        tanggal: $('#filter_tanggal').val()
                    },
                    success: function(response) {
                        $('#loading_state').hide();

                        if (response.status === 'success' && response.data.length > 0) {
                            renderTable(response.data);
                        } else {
                            $('#empty_state').show();
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

            function renderTable(data) {
                let html = '';

                data.forEach(function(item, index) {
                    // Ambil summary pertama (jika ada)
                    const summary = item.summaries?.[0];
                    const mid_barang = summary?.barang?.mid_barang ?? '-';
                    const nama_barang = summary?.barang?.nama_barang ?? '-';
                    const qty_sistem = summary?.qty_sistem != null ? parseInt(summary.qty_sistem) : '-';
                    const qty_fisik = summary?.qty_fisik != null ? parseInt(summary.qty_fisik) : '-';
                    const selisihVal = summary?.selisih != null ? parseInt(summary.selisih) : 0;
                    const keterangan = summary?.keterangan ?? '';

                    let statusClass;
                    let statusIcon;
                    let statusText;

                    if (selisihVal < 0) {
                        statusClass = 'danger';
                        statusIcon = 'arrow-down-bold-circle';
                        statusText = 'Selisih Kurang';
                    } else if (selisihVal > 0) {
                        statusClass = 'warning';
                        statusIcon = 'arrow-up-bold-circle';
                        statusText = 'Selisih Lebih';
                    } else {
                        statusClass = 'success';
                        statusIcon = 'check';
                        statusText = 'Sesuai';
                    }

                    // Buat badge status menggunakan variabel yang telah ditentukan
                    const statusBadge = `
                        <span class="badge badge-soft-${statusClass} fs-6" title="${statusText}">
                            <i class="mdi mdi-${statusIcon} me-2"></i> ${formatNumber(selisihVal)}
                        </span>
                    `;

                    html += `
                        <tr class="table-row-hover">
                            <td class="text-center text-primary fw-bold">${index + 1}</td>
                            <td>
                                <div class="d-flex align-items-center text-nowrap">
                                    <i class="mdi mdi-calendar-blank text-muted me-2"></i>
                                    <span>${formatDate(item.tgl_opname)}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-barcode text-muted me-2"></i>
                                    <span class="fw-semibold">${mid_barang}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-nowrap">
                                    <span class="fw-semibold">${nama_barang}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-nowrap">
                                    <span class="fw-semibold">${qty_sistem}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-nowrap">
                                    <span class="fw-semibold">${qty_fisik}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                    ${statusBadge}
                            </td>
                            
                            <td>
                                <div class="d-flex align-items-center text-nowrap">
                                    <span class="fw-semibold">${keterangan}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-outline-primary text-nowrap" onclick="showDetail(${item.id})">
                                    <i class="mdi mdi-eye-outline"></i> Detail
                                </button>
                            </td>
                        </tr>
                    `;
                });

                $('#tableBody').html(html);
            }

            window.showDetail = function(id) {
                $.ajax({
                    url: `{{ url('api/wfg/sop/report/getData') }}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const item = response.data.find(d => d.id === id);
                            if (item) {
                                renderDetailModal(item);
                                const modal = new bootstrap.Modal(document.getElementById(
                                    'detailModal'));
                                modal.show();
                            }
                        }
                    }
                });
            }

            function renderDetailModal(data) {
                let html = `
                    <!-- Header Info -->
                    <div class="row mb-4">
                        <h6 class="text-muted mb-2">Informasi Opname</h6>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td width="120">ID Opname</td>
                                    <td><strong>#${data.id}</strong></td>
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
                                    <td><strong>${data.user.username}</strong></td>
                                </tr>
                                <tr>
                                    <td width="120">UOM</td> 
                                    <td><strong>${data.summaries[0].barang.satuan}</strong></td>
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
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">Qty/Box</th>
                                    <th class="text-end">Qty Fisik</th>
                                    <th class="text-end">Qty Sistem</th>
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
                                    <th>Kode</th>
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

            function getStatClass(summaries) {
                const hasDiscrepancy = summaries.some(s => parseFloat(s.selisih) !== 0);
                return hasDiscrepancy ? 'danger' : 'success';
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
                                    <strong>${selisih > 0 ? '+' : ''}${formatNumber(Math.abs(item.selisih))}</strong>
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
                if (isNaN(num)) {
                    return '0';
                }

                return num.toLocaleString('id-ID', {
                    // PERUBAHAN UTAMA: Atur kedua properti ke 0 untuk menghilangkan desimal
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            // Export PDF
            $('#btn_export').on('click', function() {
                $('#exportDateModal').modal('show');
            });

            $('#btn_confirm_export').on('click', async function() {
                const tanggal = $('#export_tanggal').val();
                if (!tanggal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal wajib diisi',
                        text: 'Silakan pilih tanggal untuk export laporan.',
                    });
                    return;
                }

                // Tampilkan loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu, sedang mengekspor data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch("{{ route('wfg.stock_opname.export') }}?tanggal=" +
                        tanggal, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                    Swal.close();

                    const contentType = response.headers.get('Content-Type');

                    if (response.ok && contentType.includes('application/pdf')) {

                        const blob = await response.blob();
                        const fileURL = URL.createObjectURL(blob);
                        window.open(fileURL, '_blank');
                        $('#exportDateModal').modal('hide');
                    } else {
                        const jsonResponse = await response.json();
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Tidak Ditemukan',
                            text: jsonResponse.message || 'Terjadi kesalahan pada server.',
                        });
                    }
                } catch (error) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal',
                        text: 'Tidak dapat terhubung ke server: ' + error.message,
                    });
                }
            });
        });
    </script>
@endsection
