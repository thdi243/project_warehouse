@extends('layouts.app')

@section('title', '| Bongkar Muat')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">WFG Bongkar Muat</h4>
                        <div class="page-title-right">
                            <a href="{{ route('wfg.bongkar_muat.form') }}" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i> Tambah Bongkar Muat
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
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Search</label>
                                    <div class="search-box">
                                        <input type="text" id="searchInput" class="form-control"
                                            placeholder="Search Document, Wavepick, Shipment...">
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
                                    <label class="form-label text-muted small fw-bold text-uppercase">Status</label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="approved">Approved</option>
                                        <option value="finished">Finished</option>
                                        <option value="verified">Verified</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Flags</label>
                                    <select id="flagsFilter" class="form-select">
                                        <option value="" selected disabled>No Choose</option>
                                        <option value="double_po">2 PO</option>
                                        <option value="cancel_to">Cancel TO</option>
                                        <option value="manual_picking">Manual Picking</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mt-md-4 d-flex gap-2">
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
                        <table class="table table-hover align-middle text-nowrap" id="bongkarMuatTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Tanggal</th>
                                    <th>Wavepick SMU</th>
                                    <th>Shipment SMU</th>
                                    <th>Wavepick BAS</th>
                                    <th>Shipment BAS</th>
                                    <th>Forklift Driver</th>
                                    <th>Checker</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th>Jam Muat</th>
                                    <th>Jam Selesai</th>
                                    <th>Verified By</th>
                                    <th>Flags</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data populated via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4" id="paginationContainer"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Item Modal --}}
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-edit-item">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-item-id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Material</label>
                            <input type="text" class="form-control bg-light" id="edit-item-material" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batch Number</label>
                                <input type="text" name="batch_number" id="edit-item-batch" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis</label>
                                <select name="jenis" id="edit-item-jenis" class="form-select">
                                    <option value="P">Full Pallet (P)</option>
                                    <option value="R">Receh (R)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="qty" id="edit-item-qty" class="form-control"
                                    step="any">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">TO Dummy</label>
                                <input type="text" name="to_dummy" id="edit-item-to-dummy" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">TO SAP</label>
                                <input type="text" name="to_sap" id="edit-item-to-sap" class="form-control">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-4">
                                <div class="form-check form-switch form-switch-warning">
                                    <input class="form-check-input" type="checkbox" name="double_po"
                                        id="edit-item-double-po" value="1">
                                    <label class="form-check-label" for="edit-item-double-po">Double PO</label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check form-switch form-switch-danger">
                                    <input class="form-check-input" type="checkbox" name="cancel_to"
                                        id="edit-item-cancel-to" value="1">
                                    <label class="form-check-label" for="edit-item-cancel-to">Cancel TO</label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check form-switch form-switch-success">
                                    <input class="form-check-input" type="checkbox" name="manual_picking"
                                        id="edit-item-manual-picking" value="1">
                                    <label class="form-check-label" for="edit-item-manual-picking">Manual</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Header Modal --}}
    <div class="modal fade" id="editHeaderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Bongkar Muat Header</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-edit-header">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-header-id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" id="edit-header-tanggal" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Wavepick SMU</label>
                                <input type="text" name="wavepick_smu" id="edit-header-wavepick_smu"
                                    class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shipment SMU</label>
                                <input type="text" name="shipment_smu" id="edit-header-shipment_smu"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Wavepick BAS</label>
                                <input type="text" name="wavepick_bas" id="edit-header-wavepick_bas"
                                    class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shipment BAS</label>
                                <input type="text" name="shipment_bas" id="edit-header-shipment_bas"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Mobil</label>
                                <input type="text" name="no_mobil" id="edit-header-no_mobil" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Kontainer</label>
                                <input type="text" name="no_kontainer" id="edit-header-no_kontainer"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Segel BAS</label>
                                <input type="text" name="no_segel_bas" id="edit-header-no_segel_bas"
                                    class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Segel Vendor</label>
                                <input type="text" name="no_segel_vendor" id="edit-header-no_segel_vendor"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Slipsheet</label>
                                <input type="number" name="jumlah_slipsheet" id="edit-header-slipsheet"
                                    class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Bongkar Muat Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="120" class="text-muted">No Dok</td>
                                    <td class="fw-bold">: <span id="detail-no-dok"></span></td>
                                </tr>
                                <tr>
                                    <td width="120" class="text-muted">Wavepick SMU</td>
                                    <td class="fw-bold">: <span id="detail-wavepick_smu"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Shipment SMU</td>
                                    <td class="fw-bold">: <span id="detail-shipment_smu"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Wavepick BAS</td>
                                    <td class="fw-bold">: <span id="detail-wavepick_bas"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Shipment BAS</td>
                                    <td class="fw-bold">: <span id="detail-shipment_bas"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td class="fw-bold">: <span id="detail-status" class="badge bg-primary"></span></td>
                                </tr>
                                <tr>
                                    <td width="120" class="text-muted">Forklift Driver</td>
                                    <td class="fw-bold">: <span id="detail-forklift-driver"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Checker</td>
                                    <td class="fw-bold">: <span id="detail-checker"></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">Driver</td>
                                    <td class="fw-bold">: <span id="detail-driver"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tujuan</td>
                                    <td class="fw-bold">: <span id="detail-destinasi"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. Mobil</td>
                                    <td class="fw-bold">: <span id="detail-no_mobil"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Jam Muat</td>
                                    <td class="fw-bold">: <span id="detail-jam_muat"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Jam Selesai</td>
                                    <td class="fw-bold">: <span id="detail-jam_selesai"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. Kontainer</td>
                                    <td class="fw-bold">: <span id="detail-no_kontainer"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. Segel BAS</td>
                                    <td class="fw-bold">: <span id="detail-no_segel_bas"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. Segel Vendor</td>
                                    <td class="fw-bold">: <span id="detail-no_segel_vendor"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Slipsheet</td>
                                    <td class="fw-bold">: <span id="detail-jumlah_slipsheet"></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <h6>Item Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="detail-items">
                            <thead class="table-light">
                                <tr>
                                    <th>Material</th>
                                    <th>Batch</th>
                                    <th>Jenis</th>
                                    <th>Qty</th>
                                    <th>TO Dummy</th>
                                    <th>TO SAP</th>
                                    <th>Flags</th>
                                    <th>No TO</th>
                                    <th>Qty TO</th>
                                    @can('permission', 'bongkar-muat-plus')
                                        <th class="text-center">Actions</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '{{ session('success') }}',
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '{{ session('error') }}',
                });
            @endif

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
                const status = $('#statusFilter').val();
                const flags = $('#flagsFilter').val();
                const tbody = $('#bongkarMuatTable tbody');

                tbody.html(
                    '<tr><td colspan="15" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>'
                );
                $('#paginationContainer').empty();

                $.ajax({
                    url: "{{ route('wfg.bongkar_muat.data') }}",
                    type: "GET",
                    data: {
                        page: page,
                        search: search,
                        start_date: startDate,
                        end_date: endDate,
                        status: status,
                        flags: flags
                    },
                    success: function(res) {
                        const paginatedData = res.data;
                        const items = paginatedData.data;

                        tbody.empty();

                        if (res.status && items.length > 0) {
                            $.each(items, function(index, order) {
                                const noUrut = ((paginatedData.current_page - 1) *
                                    paginatedData.per_page) + (index + 1);
                                const typeBadgeClass = order.type_shipment === 'BAS' ?
                                    'bg-info' : 'bg-secondary';

                                let statusClass = 'bg-secondary';
                                let statusText = order.status ? order.status.toUpperCase() :
                                    'UNKNOWN';
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
                                    case 'loading':
                                        statusClass = 'bg-soft-warning text-warning';
                                        break;
                                    case 'finished':
                                        statusClass = 'bg-soft-success text-success';
                                        break;
                                    case 'verified':
                                        statusClass = 'bg-success';
                                        break;
                                    case 'rejected':
                                        statusClass = 'bg-danger';
                                        break;
                                }

                                const forkliftDriverName = order.forklift_driver ? order
                                    .forklift_driver.username : '-';
                                const checkerName = order.checker ? order.checker.username :
                                    '-';
                                const driverName = order.driver_name ? order.driver_name :
                                    '-';
                                const verificatorName = order.verificator ? order
                                    .verificator.username :
                                    '-';


                                // Store order data for modal
                                const orderJson = encodeURIComponent(JSON.stringify(order));

                                const showUrl = "{{ url('wfg/bongkar-muat/show') }}/" +
                                    order.id;

                                let actions = `
                                    <button type="button" class="btn btn-soft-info btn-sm btn-detail" data-order="${orderJson}" title="Quick View Items">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    @can('permission', 'bongkar-muat-plus')
                                        <button type="button" class="btn btn-soft-primary btn-sm btn-edit" data-order="${orderJson}" title="Edit Items">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-soft-danger btn-sm btn-delete" data-id="${order.id}" title="Delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endcan 
                                    <button type="button" class="btn btn-soft-success btn-sm btn-download" data-id="${order.id}" title="Download">
                                        <i class="ri-download-line"></i>
                                    </button>
                                `;

                                const flagBadges = order.details?.some(detail =>
                                        detail.double_po ||
                                        detail.cancel_to ||
                                        detail.manual_picking
                                    ) ?
                                    '<span class="badge bg-warning text-dark">Yes</span>' :
                                    '-';

                                const rowHtml = `
                                    <tr>
                                        <td class="text-center">${noUrut}</td>
                                        <td class="text-center">${order.tanggal}</td>
                                        <td>${order.wavepick_smu ?? '-'}</td>
                                        <td>${order.shipment_smu ?? '-'}</td>
                                        <td>${order.wavepick_bas ?? '-'}</td>
                                        <td>${order.shipment_bas ?? '-'}</td>
                                        <td>${forkliftDriverName}</td>
                                        <td>${checkerName}</td>
                                        <td>${driverName}</td>
                                        <td><span class="badge ${statusClass}">${statusText}</span></td>
                                        <td>${order.jam_muat || '-'}</td>
                                        <td>${order.jam_selesai || '-'}</td>
                                        <td>${verificatorName}</td>
                                        <td>${flagBadges}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                ${actions}
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                tbody.append(rowHtml);
                            });
                            renderPagination(paginatedData);
                        } else {
                            tbody.append(
                                '<tr><td colspan="15" class="text-center py-4">No bongkar muat records found.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error("Error loading data", xhr);
                        tbody.append(
                            '<tr><td colspan="9" class="text-center py-4 text-danger">Failed to load data.</td></tr>'
                        );
                    }
                });
            }

            function renderPagination(data) {
                const container = $("#paginationContainer");
                container.empty();

                if (!data || data.last_page <= 1) return;

                let paginationHtml =
                    '<nav aria-label="Page navigation"><ul class="pagination pagination-separated justify-content-center mb-0">';

                const prevDisabled = data.current_page === 1 ? 'disabled' : '';
                paginationHtml +=
                    `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${data.current_page - 1}">Previous</a></li>`;

                for (let i = 1; i <= data.last_page; i++) {
                    const activeClass = data.current_page === i ? 'active' : '';
                    paginationHtml +=
                        `<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }

                const nextDisabled = data.current_page === data.last_page ? 'disabled' : '';
                paginationHtml +=
                    `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a></li>`;

                paginationHtml += '</ul></nav>';
                container.append(paginationHtml);

                $('#paginationContainer .page-link').on('click', function(e) {
                    e.preventDefault();
                    const page = $(this).data('page');
                    if (!page || $(this).closest('.page-item').hasClass('disabled')) return;
                    loadData(page);
                });
            }

            loadData();

            $('#searchInput').keyup(debounce(function() {
                loadData(1);
            }, 500));

            $('#btnFilter').click(function() {
                loadData(1);
            });

            $('#btnReset').click(function() {
                $('#searchInput').val('');
                $('#startDate').val('');
                $('#endDate').val('');
                $('#statusFilter').val('');
                $('#flagsFilter').val('');
                loadData(1);
            });

            $('#startDate, #endDate, #statusFilter, #flagsFilter').change(function() {
                loadData(1);
            });

            $(document).on('click', '.btn-detail', function() {
                const order = JSON.parse(decodeURIComponent($(this).data('order')));

                // Store order in window for header edit
                window.currentOrder = order;

                $('#detail-no-dok').text(order.no_dokumen || '-');
                $('#detail-wavepick_smu').text(order.wavepick_smu || '-');
                $('#detail-shipment_smu').text(order.shipment_smu || '-');
                $('#detail-wavepick_bas').text(order.wavepick_bas || '-');
                $('#detail-shipment_bas').text(order.shipment_bas || '-');
                $('#detail-status').text(order.status ? order.status.toUpperCase() : '-');
                $('#detail-forklift-driver').text(order.forklift_driver ? order.forklift_driver.username :
                    '-');
                $('#detail-driver').text(order.driver_name ? order.driver_name : '-');
                $('#detail-checker').text(order.checker ? order.checker.username : '-');
                $('#detail-destinasi').text(order.destinasi ? order.destinasi.destinasi : '-');
                $('#detail-no_mobil').text(order.no_mobil || '-');
                $('#detail-jam_muat').text(order.jam_muat || '-');
                $('#detail-jam_selesai').text(order.jam_selesai || '-');
                $('#detail-no_kontainer').text(order.no_kontainer || '-');
                $('#detail-no_segel_bas').text(order.no_segel_bas || '-');
                $('#detail-no_segel_vendor').text(order.no_segel_vendor || '-');
                $('#detail-jumlah_slipsheet').text(order.jumlah_slipsheet || '0');

                let detailsHtml = '';
                if (order.details && order.details.length > 0) {
                    order.details.forEach(detail => {
                        const materialName = detail.material ? detail.material.nama_barang : '-';
                        const materialCode = detail.material ? detail.material.mid_barang : '-';
                        detailsHtml += `
                            <tr>
                                <td>${materialCode}<br><small class="text-muted">${materialName}</small></td>
                                <td>${detail.batch_number || '-'}</td>
                                <td class="text-center">${detail.jenis || '-'}</td>
                                <td class="text-center">${detail.qty || 0}</td>
                                <td class="text-center small">${detail.to_dummy || '-'}</td>
                                <td class="text-center small">${detail.to_sap || '-'}</td>
                                <td class="text-center">
                                    ${detail.double_po ? '<span class="badge bg-soft-warning text-warning">2 PO</span>' : ''}
                                    ${detail.cancel_to ? '<span class="badge bg-soft-danger text-danger">Cancel TO</span>' : ''}
                                    ${detail.manual_picking ? '<span class="badge bg-soft-success text-success">Manual Picking</span>' : ''}
                                </td>
                                <td class="text-center">${detail.no_to || '-'}</td>
                                <td class="text-center">${detail.qty_to || '-'}</td>
                                @can('permission', 'approval-bongkar-muat')
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-soft-info btn-edit-item" 
                                            data-item='${encodeURIComponent(JSON.stringify(detail))}'>
                                            <i class="ri-edit-2-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-soft-danger btn-delete-item" 
                                            data-id="${detail.id}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                @endcan
                            </tr>
                        `;
                    });
                } else {
                    detailsHtml = '<tr><td colspan="4" class="text-center">No items found</td></tr>';
                }
                $('#detail-items tbody').html(detailsHtml);

                $('#detailModal').modal('show');
            });

            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Data Bongkar Muat ini akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('wfg/bongkar-muat') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON ? xhr.responseJSON
                                    .message : 'Gagal menghapus data', 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.btn-download', function() {
                const id = $(this).data('id');

                window.open("{{ url('wfg/bongkar-muat/download') }}/" + id);
            });

            // Edit Item Logic
            $(document).on('click', '.btn-edit-item', function() {
                const detail = JSON.parse(decodeURIComponent($(this).data('item')));

                $('#edit-item-id').val(detail.id);
                $('#edit-item-material').val(
                    `[${detail.material.mid_barang}] ${detail.material.nama_barang}`);
                $('#edit-item-batch').val(detail.batch_number);
                $('#edit-item-jenis').val(detail.jenis);
                $('#edit-item-qty').val(detail.qty);
                $('#edit-item-to-dummy').val(detail.to_dummy);
                $('#edit-item-to-sap').val(detail.to_sap);
                $('#edit-item-double-po').prop('checked', detail.double_po == 1);
                $('#edit-item-cancel-to').prop('checked', detail.cancel_to == 1);
                $('#edit-item-manual-picking').prop('checked', detail.manual_picking == 1);

                $('#detailModal').modal('hide');
                $('#editItemModal').modal('show');
            });

            // Optional: Restore detail modal when edit modal is closed
            $('#editItemModal').on('hidden.bs.modal', function() {
                // If the detail modal was hidden to show this one, we might want to bring it back
                // We check if we are not currently showing a success message or similar
                if (!$('.swal2-container').is(':visible')) {
                    $('#detailModal').modal('show');
                }
            });

            // Mutual exclusivity for Edit Item Modal
            $('#edit-item-cancel-to').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#edit-item-double-po').prop('checked', false);
                    $('#edit-item-manual-picking').prop('checked', false);
                }
            });

            $('#edit-item-double-po').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#edit-item-cancel-to').prop('checked', false);
                }
            });

            $('#edit-item-manual-picking').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#edit-item-cancel-to').prop('checked', false);
                }
            });

            $('#form-edit-item').on('submit', function(e) {
                e.preventDefault();
                const id = $('#edit-item-id').val();
                const formData = $(this).serialize();

                $.ajax({
                    url: "{{ url('wfg/bongkar-muat/update-item') }}/" + id,
                    type: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.status) {
                            $('#editItemModal').modal('hide');
                            Swal.fire('Success', response.message, 'success');
                            loadData(); // Refresh main list
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message :
                            'Failed to update item', 'error');
                    }
                });
            });

            // Delete Item Logic
            $(document).on('click', '.btn-delete-item', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Item?',
                    text: "Item ini akan dihapus dari Bongkar Muat!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('wfg/bongkar-muat/delete-item') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.status) {
                                    $('#detailModal').modal('hide');
                                    Swal.fire('Deleted!', response.message, 'success');
                                    loadData();
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON
                                    .message : 'Failed to delete item', 'error');
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
                $('#edit-header-tanggal').val(order.tanggal);
                $('#edit-header-wavepick_smu').val(order.wavepick_smu);
                $('#edit-header-shipment_smu').val(order.shipment_smu);
                $('#edit-header-wavepick_bas').val(order.wavepick_bas);
                $('#edit-header-shipment_bas').val(order.shipment_bas);
                $('#edit-header-no_mobil').val(order.no_mobil);
                $('#edit-header-no_kontainer').val(order.no_kontainer);
                $('#edit-header-no_segel_bas').val(order.no_segel_bas);
                $('#edit-header-no_segel_vendor').val(order.no_segel_vendor);
                $('#edit-header-slipsheet').val(order.jumlah_slipsheet);

                $('#editHeaderModal').modal('show');
            });

            $('#editHeaderModal').on('hidden.bs.modal', function() {
                // No need to restore detail modal anymore as we open from main table
            });

            $('#form-edit-header').on('submit', function(e) {
                e.preventDefault();
                const id = $('#edit-header-id').val();
                const formData = $(this).serialize();

                $.ajax({
                    url: "{{ url('wfg/bongkar-muat/update') }}/" + id,
                    type: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.status) {
                            $('#editHeaderModal').modal('hide');
                            Swal.fire('Success', response.message, 'success');
                            loadData(); // Refresh list
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message :
                            'Failed to update header', 'error');
                    }
                });
            });
        });
    </script>
@endsection
