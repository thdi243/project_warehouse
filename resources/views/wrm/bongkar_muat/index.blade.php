@extends('layouts.app')

@section('title', '| Data Bongkar WRM')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0">Data Bongkar WRM</h4>
                            <small class="text-muted">Form Perintah dan Pemeriksaan Bongkar (WRM -
                                FRM/WRM/01/000/001-01)</small>
                        </div>
                        <div class="page-title-right d-flex gap-2">
                            <a href="{{ route('wrm.bongkar_muat.form') }}" class="btn btn-warning text-dark fw-semibold">
                                <i class="ri-add-line me-1"></i> Form Bongkar WRM
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Search</label>
                                    <div class="search-box">
                                        <input type="text" id="searchInput" class="form-control"
                                            placeholder="No Dokumen, No SPKB, ECP, No Mobil, Vendor...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Start Date</label>
                                    <input type="date" id="startDate" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">End Date</label>
                                    <input type="date" id="endDate" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Tipe</label>
                                    <select id="tipeFilter" class="form-select">
                                        <option value="">Semua Tipe</option>
                                        <option value="bongkar">Bongkar</option>
                                        <option value="muat">Muat</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Status</label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="approved">Approved</option>
                                        <option value="finished">Finished</option>
                                        <option value="verified">Verified</option>
                                    </select>
                                </div>
                                <div class="col-md-1 mt-md-4 d-flex gap-2">
                                    <button type="button" class="btn btn-soft-danger flex-fill" id="btnReset">
                                        <i class="ri-refresh-line"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap" id="wrmBongkarTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th style="width: 100px;">Tipe</th>
                                    <th>No. Dokumen</th>
                                    <th>No. Mobil</th>
                                    <th>Jam Mulai/Selesai</th>
                                    <th>Status</th>
                                    <th>Verified By</th>
                                    <th>Flags</th>
                                    <th class="text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4" id="paginationContainer"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Modal WRM --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="ri-file-list-3-line me-1 text-primary"></i> Detail Bongkar Muat WRM</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded flex-wrap gap-2">
                        <div>
                            <span class="text-muted small">Tipe:</span> <span id="detail-tipe-badge"></span>
                            <span class="ms-3 text-muted small">Status:</span> <span id="detail-status"></span>
                        </div>
                        <div class="d-flex gap-2">
                            <a id="detail-full-page-btn" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ri-external-link-line me-1"></i> Buka Halaman Penuh
                            </a>
                            <a id="detail-pdf-btn" href="#" target="_blank" class="btn btn-sm btn-soft-danger">
                                <i class="ri-file-pdf-line me-1"></i> PDF
                            </a>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card border border-light-subtle shadow-none mb-0">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-muted text-uppercase fs-12 mb-2"><i class="ri-file-info-line me-1"></i> Dokumen & Partner</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td width="140" class="text-muted">No. Dokumen</td>
                                            <td class="fw-bold">: <span id="detail-no-dok"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Tanggal</td>
                                            <td class="fw-bold">: <span id="detail-tanggal"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">No. SPKB</td>
                                            <td class="fw-bold">: <span id="detail-no-spkb"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">No. ECP</td>
                                            <td class="fw-bold">: <span id="detail-no-ecp"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Supplier / Vendor</td>
                                            <td class="fw-bold">: <span id="detail-supplier-vendor"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Forklift Driver</td>
                                            <td class="fw-bold">: <span id="detail-forklift"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Checker</td>
                                            <td class="fw-bold">: <span id="detail-checker"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">QC Inspector</td>
                                            <td class="fw-bold">: <span id="detail-qc"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Verified By</td>
                                            <td class="fw-bold">: <span id="detail-verificator"></span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border border-light-subtle shadow-none mb-0">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-muted text-uppercase fs-12 mb-2"><i class="ri-truck-line me-1"></i> Kendaraan & Waktu</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td width="140" class="text-muted">No. Mobil</td>
                                            <td class="fw-bold">: <span id="detail-no-mobil"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Driver</td>
                                            <td class="fw-bold">: <span id="detail-driver"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Sloc / Gate</td>
                                            <td class="fw-bold">: <span id="detail-sloc-gate"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">No. Kontainer</td>
                                            <td class="fw-bold">: <span id="detail-no-kontainer"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">No. Segel BAS</td>
                                            <td class="fw-bold">: <span id="detail-no-segel"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Jam Mulai</td>
                                            <td class="fw-bold">: <span id="detail-jam-mulai"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Jam Selesai</td>
                                            <td class="fw-bold">: <span id="detail-jam-selesai"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Jam Serah QC</td>
                                            <td class="fw-bold">: <span id="detail-jam-serah-qc"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Jam Release QC</td>
                                            <td class="fw-bold">: <span id="detail-jam-release-qc"></span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2 mt-4">
                        <h6 class="mb-0 fw-bold"><i class="ri-shopping-basket-2-line me-1 text-primary"></i> Detail Item Material</h6>
                        <span class="badge bg-soft-info text-info" id="detail-item-count">0 Item</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="detailItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;">No</th>
                                    <th>MID / Nama Barang</th>
                                    <th>Batch / Lot</th>
                                    <th class="text-center" style="width: 120px;">Jenis</th>
                                    <th class="text-center" style="width: 80px;">UoM</th>
                                    <th class="text-end" style="width: 100px;">Jumlah</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- items loaded via JS -->
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end">Total:</td>
                                    <td class="text-end" id="detail-total-qty">0</td>
                                    <td id="detail-total-pallets">0 Pallet</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3 p-2 bg-light rounded" id="detail-catatan-container">
                        <small class="text-muted fw-bold d-block">Catatan:</small>
                        <span id="detail-catatan" class="fst-italic">-</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Header Modal --}}
    <div class="modal fade" id="editHeaderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-edit-line me-1 text-primary"></i> Edit Header Bongkar Muat WRM</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-edit-header">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-header-id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="edit-header-tanggal" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. Dokumen</label>
                                <input type="text" id="edit-header-no_dokumen" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. Mobil</label>
                                <input type="text" name="no_mobil" id="edit-header-no_mobil" class="form-control text-uppercase" placeholder="Contoh: B 1234 ABC">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Supir</label>
                                <input type="text" name="driver_name" id="edit-header-driver_name" class="form-control" placeholder="Nama pengemudi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Supplier / Vendor</label>
                                <input type="text" name="supplier_vendor" id="edit-header-supplier_vendor" class="form-control" placeholder="Nama vendor / supplier">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. SPKB / PO</label>
                                <input type="text" name="no_spkb" id="edit-header-no_spkb" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. ECP</label>
                                <input type="text" name="no_ecp" id="edit-header-no_ecp" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sloc</label>
                                <input type="text" name="sloc" id="edit-header-sloc" class="form-control" placeholder="Contoh: 1001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gate</label>
                                <input type="text" name="gate" id="edit-header-gate" class="form-control" placeholder="Contoh: Gate 1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. Kontainer</label>
                                <input type="text" name="no_kontainer" id="edit-header-no_kontainer" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. Segel BAS</label>
                                <input type="text" name="no_segel_bas" id="edit-header-no_segel_bas" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Catatan</label>
                                <textarea name="catatan" id="edit-header-catatan" class="form-control" rows="2" placeholder="Catatan tambahan jika ada..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-save-header"><i class="ri-save-line me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            function debounce(func, delay) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), delay);
                };
            }

            window.loadData = function(page = 1) {
                const search = $('#searchInput').val();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();
                const tipe = $('#tipeFilter').val();
                const status = $('#statusFilter').val();
                const tbody = $('#wrmBongkarTable tbody');

                tbody.html(
                    '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>'
                );
                $('#paginationContainer').empty();

                $.ajax({
                    url: "{{ route('wrm.bongkar_muat.data') }}",
                    type: "GET",
                    data: {
                        page: page,
                        search: search,
                        start_date: startDate,
                        end_date: endDate,
                        tipe: tipe,
                        status: status
                    },
                    success: function(res) {
                        const paginatedData = res.data;
                        const items = paginatedData.data;

                        tbody.empty();

                        if (res.status && items.length > 0) {
                            $.each(items, function(index, order) {
                                const noUrut = ((paginatedData.current_page - 1) *
                                    paginatedData.per_page) + (index + 1);

                                let statusClass = 'bg-secondary';
                                switch (order.status) {
                                    case 'draft':
                                        statusClass = 'bg-soft-secondary text-secondary';
                                        break;
                                    case 'submitted':
                                        statusClass = 'bg-soft-info text-info';
                                        break;
                                    case 'approved':
                                        statusClass = 'bg-soft-primary text-primary';
                                        break;
                                    case 'finished':
                                        statusClass = 'bg-soft-success text-success';
                                        break;
                                    case 'verified':
                                        statusClass = 'bg-success';
                                        break;
                                }

                                const isMuat = order.tipe === 'muat';
                                const tipeBadge = isMuat ?
                                    '<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="ri-upload-2-line me-1"></i>MUAT</span>' :
                                    '<span class="badge bg-warning text-dark"><i class="ri-download-2-line me-1"></i>BONGKAR</span>';

                                const docNum = order.no_dokumen || (order.no_spkb ? order
                                    .no_spkb :
                                    '<span class="text-muted fst-italic">Draft #' +
                                    order.id + '</span>');
                                const tanggalStr = order.tanggal ? order.tanggal.substring(
                                    0, 10) : '-';

                                const mobilBadge = order.no_mobil ?
                                    `<span class="badge bg-soft-dark text-dark fw-bold">${order.no_mobil}</span>` :
                                    '<span class="text-muted">-</span>';
                                const driverSmall = order.driver_name ?
                                    `<small class="text-muted d-block">${order.driver_name}</small>` :
                                    '';

                                const jamMulai = order.jam_muat ? order.jam_muat.substring(
                                    0, 5) : '-';
                                const jamSelesai = order.jam_selesai ? order.jam_selesai
                                    .substring(0, 5) : '-';

                                const verificatorName = order.verificator ? order
                                    .verificator.username : '-';

                                let flagBadges = [];
                                if (order.details && order.details.length > 0) {
                                    if (order.details.some(d => d.jenis === 'R')) {
                                        flagBadges.push(
                                            '<span class="badge bg-warning text-dark">Receh</span>'
                                            );
                                    }
                                }
                                const flagsHtml = flagBadges.length > 0 ? flagBadges.join(
                                    '') : '<span class="text-muted">-</span>';

                                const orderJson = encodeURIComponent(JSON.stringify(order));
                                const showUrl = "{{ url('wrm/bongkar-muat/show') }}/" +
                                    order.id;
                                const downloadUrl =
                                    "{{ url('wrm/bongkar-muat/download') }}/" + order.id;
                                const editDraftUrl =
                                    "{{ route('wrm.bongkar_muat.form') }}?draft_id=" + order
                                    .id;

                                let actionBtns = `
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-soft-info btn-sm btn-detail" data-order="${orderJson}" title="Quick View Detail">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        ${order.status === 'draft' ? 
                                            `<a href="${editDraftUrl}" class="btn btn-soft-primary btn-sm" title="Resume Draft"><i class="ri-edit-line"></i></a>` : 
                                            `<button type="button" class="btn btn-soft-primary btn-sm btn-edit" data-order="${orderJson}" title="Edit Data"><i class="ri-edit-line"></i></button>`
                                        }
                                        <button type="button" class="btn btn-soft-danger btn-sm btn-delete" data-id="${order.id}" title="Delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-soft-warning btn-sm btn-follow-up-checker" data-id="${order.id}" title="Follow Up Checker" ${(order.status === 'submitted' || order.status === 'draft' || order.status === 'approved') && order.checker_id ? '' : 'disabled'}>
                                            <i class="ri-notification-3-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-soft-success btn-sm btn-download" data-id="${order.id}" title="Download PDF">
                                            <i class="ri-download-line"></i>
                                        </button>
                                    </div>
                                `;

                                const row = `
                                    <tr>
                                        <td class="text-center fw-medium">${noUrut}</td>
                                        <td>${tipeBadge}</td>
                                        <td>
                                            <div class="fw-bold">${docNum}</div>
                                            <small class="text-muted">${tanggalStr}</small>
                                        </td>
                                        <td>
                                            ${mobilBadge}
                                            ${driverSmall}
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-info text-dark fw-bold">${jamMulai} WIB</span><br>
                                            <span class="badge bg-soft-info text-dark fw-bold">${jamSelesai} WIB</span>
                                        </td>
                                        <td><span class="badge ${statusClass} text-uppercase">${order.status}</span></td>
                                        <td>${verificatorName}</td>
                                        <td>${flagsHtml}</td>
                                        <td class="text-center">${actionBtns}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });

                            renderPagination(paginatedData);
                        } else {
                            tbody.html(
                                '<tr><td colspan="10" class="text-center py-5 text-muted"><i class="ri-inbox-line fs-24 d-block mb-2"></i>Tidak ada data bongkar WRM</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        tbody.html(
                            '<tr><td colspan="10" class="text-center py-4 text-danger"><i class="ri-error-warning-line me-1"></i> Gagal memuat data.</td></tr>'
                        );
                    }
                });
            };

            function renderPagination(data) {
                if (data.total <= data.per_page) return;

                let paginationHtml = '<ul class="pagination pagination-separated mb-0">';
                if (data.prev_page_url) {
                    paginationHtml +=
                        `<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="loadData(${data.current_page - 1})"><i class="ri-arrow-left-s-line"></i></a></li>`;
                } else {
                    paginationHtml +=
                        `<li class="page-item disabled"><span class="page-link"><i class="ri-arrow-left-s-line"></i></span></li>`;
                }

                for (let i = 1; i <= data.last_page; i++) {
                    if (i === 1 || i === data.last_page || (i >= data.current_page - 1 && i <= data.current_page +
                            1)) {
                        paginationHtml +=
                            `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a href="javascript:void(0);" class="page-link" onclick="loadData(${i})">${i}</a></li>`;
                    } else if (i === data.current_page - 2 || i === data.current_page + 2) {
                        paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }

                if (data.next_page_url) {
                    paginationHtml +=
                        `<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="loadData(${data.current_page + 1})"><i class="ri-arrow-right-s-line"></i></a></li>`;
                } else {
                    paginationHtml +=
                        `<li class="page-item disabled"><span class="page-link"><i class="ri-arrow-right-s-line"></i></span></li>`;
                }
                paginationHtml += '</ul>';

                $('#paginationContainer').html(paginationHtml);
            }

            $(document).on('click', '.btn-detail', function() {
                const order = JSON.parse(decodeURIComponent($(this).data('order')));

                const isMuat = order.tipe === 'muat';
                const tipeBadge = isMuat
                    ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="ri-upload-2-line me-1"></i>MUAT</span>'
                    : '<span class="badge bg-warning text-dark"><i class="ri-download-2-line me-1"></i>BONGKAR</span>';

                let statusClass = 'bg-secondary';
                switch (order.status) {
                    case 'draft': statusClass = 'bg-soft-secondary text-secondary'; break;
                    case 'submitted': statusClass = 'bg-soft-info text-info'; break;
                    case 'approved': statusClass = 'bg-soft-primary text-primary'; break;
                    case 'finished': statusClass = 'bg-soft-success text-success'; break;
                    case 'verified': statusClass = 'bg-success'; break;
                }

                $('#detail-tipe-badge').html(tipeBadge);
                $('#detail-status').html(`<span class="badge ${statusClass} text-uppercase">${order.status || '-'}</span>`);

                const showUrl = "{{ url('wrm/bongkar-muat/show') }}/" + order.id;
                const downloadUrl = "{{ url('wrm/bongkar-muat/download') }}/" + order.id;
                $('#detail-full-page-btn').attr('href', showUrl);
                $('#detail-pdf-btn').attr('href', downloadUrl);

                $('#detail-no-dok').text(order.no_dokumen || ('Draft #' + order.id));
                $('#detail-tanggal').text(order.tanggal ? order.tanggal.substring(0, 10) : '-');
                $('#detail-no-spkb').text(order.no_spkb || '-');
                $('#detail-no-ecp').text(order.no_ecp || '-');
                $('#detail-supplier-vendor').text(order.supplier_vendor || '-');
                $('#detail-forklift').text(order.forklift_driver ? order.forklift_driver.username : '-');
                $('#detail-checker').text(order.checker ? order.checker.username : '-');
                $('#detail-qc').text(order.qc_user ? order.qc_user.username : (order.qc_signature ? 'QC Approved' : '-'));
                $('#detail-verificator').text(order.verificator ? order.verificator.username : '-');

                $('#detail-no-mobil').text(order.no_mobil || '-');
                $('#detail-driver').text(order.driver_name || '-');
                $('#detail-sloc-gate').text((order.sloc || '-') + ' / Gate ' + (order.gate || '-'));
                $('#detail-no-kontainer').text(order.no_kontainer || '-');
                $('#detail-no-segel').text(order.no_segel_bas || '-');
                $('#detail-jam-mulai').text(order.jam_muat ? order.jam_muat.substring(0, 5) : '-');
                $('#detail-jam-selesai').text(order.jam_selesai ? order.jam_selesai.substring(0, 5) : '-');
                $('#detail-jam-serah-qc').text(order.jam_serah_terima_qc ? order.jam_serah_terima_qc.substring(0, 5) : '-');
                $('#detail-jam-release-qc').text(order.jam_release_qc ? order.jam_release_qc.substring(0, 5) : '-');
                $('#detail-catatan').text(order.catatan || '-');

                // Render detail items
                const tbody = $('#detailItemsTable tbody');
                tbody.empty();
                let totalQty = 0;
                let totalPallets = 0;

                if (order.details && order.details.length > 0) {
                    $('#detail-item-count').text(order.details.length + ' Item');
                    $.each(order.details, function(idx, item) {
                        const mid = item.mid || (item.material ? item.material.mid : '-');
                        const nama = item.nama_barang || (item.material ? item.material.nama_barang : '-');
                        const jenisBadge = item.jenis === 'P'
                            ? '<span class="badge bg-soft-primary text-primary">Full Pallet (P)</span>'
                            : '<span class="badge bg-soft-warning text-warning">Receh (R)</span>';
                        
                        const qtyVal = parseFloat(item.qty) || 0;
                        totalQty += qtyVal;
                        totalPallets += 1;

                        const rowHtml = `
                            <tr>
                                <td class="text-center">${idx + 1}</td>
                                <td><span class="fw-bold">${mid}</span> - ${nama}</td>
                                <td>${item.batch_number || '-'}</td>
                                <td class="text-center">${jenisBadge}</td>
                                <td class="text-center">${item.uom || '-'}</td>
                                <td class="text-end fw-bold">${parseFloat(qtyVal.toFixed(3))}</td>
                                <td>${item.keterangan || '-'}</td>
                            </tr>
                        `;
                        tbody.append(rowHtml);
                    });
                } else {
                    $('#detail-item-count').text('0 Item');
                    tbody.html('<tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada detail item</td></tr>');
                }

                $('#detail-total-qty').text(parseFloat(totalQty.toFixed(3)));
                $('#detail-total-pallets').text(totalPallets + ' Pallet');

                $('#detailModal').modal('show');
            });

            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Data Bongkar Muat WRM ini akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('wrm/bongkar-muat') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data', 'error');
                            }
                        });
                    }
                });
            });

            // Edit Header Logic
            $(document).on('click', '.btn-edit', function() {
                const order = JSON.parse(decodeURIComponent($(this).data('order')));
                if (!order) return;

                $('#edit-header-id').val(order.id);
                $('#edit-header-tanggal').val(order.tanggal ? order.tanggal.substring(0, 10) : '');
                $('#edit-header-no_dokumen').val(order.no_dokumen || '-');
                $('#edit-header-no_mobil').val(order.no_mobil || '');
                $('#edit-header-driver_name').val(order.driver_name || '');
                $('#edit-header-supplier_vendor').val(order.supplier_vendor || '');
                $('#edit-header-no_spkb').val(order.no_spkb || '');
                $('#edit-header-no_ecp').val(order.no_ecp || '');
                $('#edit-header-sloc').val(order.sloc || '');
                $('#edit-header-gate').val(order.gate || '');
                $('#edit-header-no_kontainer').val(order.no_kontainer || '');
                $('#edit-header-no_segel_bas').val(order.no_segel_bas || '');
                $('#edit-header-catatan').val(order.catatan || '');

                $('#editHeaderModal').modal('show');
            });

            $('#form-edit-header').on('submit', function(e) {
                e.preventDefault();
                const id = $('#edit-header-id').val();
                const formData = $(this).serialize();
                const btnSave = $('#btn-save-header');

                btnSave.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');

                $.ajax({
                    url: "{{ url('wrm/bongkar-muat/update') }}/" + id,
                    type: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.status) {
                            $('#editHeaderModal').modal('hide');
                            Swal.fire('Berhasil', response.message, 'success');
                            loadData();
                        } else {
                            Swal.fire('Gagal', response.message || 'Gagal memperbarui header', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memperbarui data', 'error');
                    },
                    complete: function() {
                        btnSave.prop('disabled', false).html('<i class="ri-save-line me-1"></i> Simpan Perubahan');
                    }
                });
            });

            $(document).on('click', '.btn-download', function() {
                const id = $(this).data('id');
                window.open("{{ url('wrm/bongkar-muat/download') }}/" + id);
            });

            $(document).on('click', '.btn-follow-up-checker', function() {
                const id = $(this).data('id');
                const button = $(this);

                Swal.fire({
                    title: 'Kirim follow up?',
                    text: 'Checker akan menerima notifikasi approval Bongkar Muat WRM.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Kirim',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    button.prop('disabled', true);

                    $.ajax({
                        url: "{{ url('wrm/bongkar-muat/follow-up-checker') }}/" + id,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire('Berhasil', response.message, 'success');
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal', xhr.responseJSON?.message || 'Follow up gagal dikirim.', 'error');
                        },
                        complete: function() {
                            button.prop('disabled', false);
                        }
                    });
                });
            });

            $('#searchInput').on('keyup', debounce(function() {
                loadData(1);
            }, 400));
            $('#startDate, #endDate, #tipeFilter, #statusFilter').on('change', function() {
                loadData(1);
            });
            $('#btnReset').on('click', function() {
                $('#searchInput').val('');
                $('#startDate').val('');
                $('#endDate').val('');
                $('#tipeFilter').val('');
                $('#statusFilter').val('');
                loadData(1);
            });

            loadData(1);
        });
    </script>
@endsection
