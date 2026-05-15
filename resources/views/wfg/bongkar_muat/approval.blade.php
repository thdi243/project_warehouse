@extends('layouts.app')

@section('title', '| Approval Bongkar Muat')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Bongkar Muat Approval & Verification</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4">
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
                                <div class="col-md-4 mt-md-4 d-flex gap-2">
                                    <button type="button" class="btn btn-soft-danger flex-fill" id="btnReset">
                                        <i class="ri-refresh-line"></i> Reset
                                    </button>

                                    <button type="button" class="btn btn-primary flex-fill" id="btnFilter">
                                        <i class="ri-filter-3-line"></i> Filter
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
                        <table class="table table-hover align-middle text-nowrap" id="bongkarMuatApprovalTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
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
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3" id="paginationContainer">
                        <!-- Pagination will be generated via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
                                    <td class="fw-bold">: <span id="detail-wavepick-smu"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Shipment SMU</td>
                                    <td class="fw-bold">: <span id="detail-shipment-smu"></span></td>
                                </tr>
                                <tr>
                                    <td width="120" class="text-muted">Wavepick BAS</td>
                                    <td class="fw-bold">: <span id="detail-wavepick-bas"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Shipment BAS</td>
                                    <td class="fw-bold">: <span id="detail-shipment-bas"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td class="fw-bold">: <span id="detail-status" class="badge bg-primary"></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="120" class="text-muted">Forklift Driver</td>
                                    <td class="fw-bold">: <span id="detail-forklift-driver"></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Checker</td>
                                    <td class="fw-bold">: <span id="detail-checker"></span></td>
                                </tr>
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
                            </table>
                        </div>
                    </div>

                    <h6>Item Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="detail-items">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th>Material</th>
                                    <th>Batch</th>
                                    <th>Jenis</th>
                                    <th>Qty</th>
                                    <th>To Dummy</th>
                                    <th>To SAP</th>
                                    <th>Double PO</th>
                                    <th>Cancel To</th>
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
                const tbody = $('#bongkarMuatApprovalTable tbody');

                tbody.html(
                    '<tr><td colspan="12" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>'
                );
                $('#paginationContainer').empty();

                $.ajax({
                    url: "{{ route('wfg.bongkar_muat.approval_data') }}",
                    type: "GET",
                    data: {
                        page: page,
                        search: search,
                        start_date: startDate,
                        end_date: endDate
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
                                    case 'loaded':
                                        statusClass = 'bg-soft-success text-success';
                                        break;
                                    case 'verified':
                                        statusClass = 'bg-success';
                                        break;
                                }

                                const forkliftDriverName = order.forklift_driver ? order
                                    .forklift_driver.username : '-';
                                const checkerName = order.checker ? order.checker.username :
                                    '-';
                                const driverName = order.driver_name || '-';
                                const viewUrl =
                                    "{{ route('wfg.bongkar_muat.show', ':id') }}".replace(
                                        ':id', order.id);

                                // Store order data for modal
                                const orderJson = encodeURIComponent(JSON.stringify(order));

                                let actions = `
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-info btn-sm btn-detail" data-order="${orderJson}" title="View Details">
                                    <i class="ri-eye-line"></i> Check
                                </button>
                                <a href="${viewUrl}" class="btn btn-primary btn-sm" title="Verification">
                                    <i class="ri-check-double-line"></i> Verify
                                </a>
                            </div>
                        `;

                                const rowHtml = `
                            <tr>
                                <td>${noUrut}</td>
                                <td>${order.tanggal}</td>
                                <td>${order.wavepick_smu ?? '-'}</td>
                                <td>${order.shipment_smu ?? '-'}</td>
                                <td>${order.wavepick_bas ?? '-'}</td>
                                <td>${order.shipment_bas ?? '-'}</td>
                                <td>${forkliftDriverName}</td>
                                <td>${checkerName}</td>
                                <td>${driverName}</td>
                                <td><span class="badge ${statusClass}">${statusText}</span></td>
                                <td>${order.jam_muat || '-'}</td>
                                <td class="text-center">
                                    ${actions}
                                </td>
                            </tr>
                        `;
                                tbody.append(rowHtml);
                            });
                            renderPagination(paginatedData);
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="12" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                            
                                            <i class="ti ti-package-off fs-1 mb-2"></i>

                                            <span class="fw-semibold fs-6">
                                                No orders waiting for verification
                                            </span>

                                            <small class="text-secondary">
                                                Incoming orders will appear here automatically.
                                            </small>
                                        </div>
                                    </td>
                                </tr>
                            `);
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
                loadData(1);
            });

            $('#startDate, #endDate').change(function() {
                loadData(1);
            });

            // Event listener for detail button
            $(document).on('click', '.btn-detail', function() {
                const order = JSON.parse(decodeURIComponent($(this).data('order')));

                $('#detail-no-dok').text(order.no_dokumen || '-');
                $('#detail-wavepick-smu').text(order.wavepick_smu || '-');
                $('#detail-shipment-smu').text(order.shipment_smu || '-');
                $('#detail-wavepick-bas').text(order.wavepick_bas || '-');
                $('#detail-shipment-bas').text(order.shipment_bas || '-');
                $('#detail-status').text(order.status ? order.status.toUpperCase() : '-');
                $('#detail-forklift-driver').text(order.forklift_driver ? order.forklift_driver.username :
                    '-');
                $('#detail-checker').text(order.checker ? order.checker.username : '-');
                $('#detail-driver').text(order.driver_name || '-');
                $('#detail-destinasi').text(order.destinasi ? order.destinasi.destinasi : '-');
                $('#detail-no_mobil').text(order.no_mobil || '-');
                $('#detail-jam_muat').text(order.jam_muat || '-');

                let detailsHtml = '';
                if (order.details && order.details.length > 0) {
                    order.details.forEach(detail => {
                        const materialName = detail.material ? detail.material.nama_barang : '-';
                        const materialCode = detail.material ? detail.material.mid_barang : '-';
                        detailsHtml += `
                        <tr>
                            <td>${materialCode}<br><small class="text-muted">${materialName}</small></td>
                            <td>${detail.batch_number || '-'}</td>
                            <td>${detail.jenis || '-'}</td>
                            <td>${detail.qty || 0}</td>
                            <td>${detail.to_dummy || '-'}</td>
                            <td>${detail.to_sap || '-'}</td>
                            <td>${detail.double_po || '-'}</td>
                            <td>${detail.cancel_to || '-'}</td>
                        </tr>
                    `;
                    });
                } else {
                    detailsHtml = '<tr><td colspan="4" class="text-center">No items found</td></tr>';
                }
                $('#detail-items tbody').html(detailsHtml);

                $('#detailModal').modal('show');
            });
        });
    </script>
@endsection
