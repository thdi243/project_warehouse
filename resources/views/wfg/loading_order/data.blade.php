@extends('layouts.app')

@section('title', '| Loading Orders')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">WFG Loading Orders</h4>
                        <div class="page-title-right">
                            <a href="{{ route('wfg.loading_order.form') }}" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i> New Loading Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search Wavepick / Shipment...">
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="loadingOrderTable">
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
                                    <th>Verified By</th>
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

    {{-- Detail Modal --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Loading Order Details</h5>
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
                                    <td width="120" class="text-muted">Driver</td>
                                    <td class="fw-bold">: <span id="detail-driver"></span></td>
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
                const tbody = $('#loadingOrderTable tbody');
                tbody.empty();
                $('#paginationContainer').empty();

                $.ajax({
                    url: "{{ route('wfg.loading_order.data') }}",
                    type: "GET",
                    data: {
                        page: page,
                        search: search
                    },
                    success: function(res) {
                        const paginatedData = res.data;
                        const items = paginatedData.data;

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
                                    case 'loaded':
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

                                const showUrl = "{{ url('wfg/loading-order/show') }}/" +
                                    order.id;

                                let actions = `
                                    <button type="button" class="btn btn-soft-info btn-sm btn-detail" data-order="${orderJson}" title="Quick View Items">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <a href="${showUrl}" class="btn btn-soft-primary btn-sm" title="Approval & Verification Page">
                                        <i class="ri-file-list-3-line"></i>
                                    </a>
                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete" data-id="${order.id}" title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-soft-success btn-sm btn-download" data-id="${order.id}" title="Download">
                                        <i class="ri-download-line"></i>
                                    </button>
                                `;

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
                                        <td>${verificatorName}</td>
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
                                '<tr><td colspan="9" class="text-center py-4">No loading orders found.</td></tr>'
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
            }, 300));

            $(document).on('click', '.btn-detail', function() {
                const order = JSON.parse(decodeURIComponent($(this).data('order')));

                $('#detail-no-dok').text(order.no_dokumen || '-');
                $('#detail-wavepick_smu').text(order.wavepick_smu || '-');
                $('#detail-shipment_smu').text(order.shipment_smu || '-');
                $('#detail-wavepick_bas').text(order.wavepick_bas || '-');
                $('#detail-shipment_bas').text(order.shipment_bas || '-');
                $('#detail-status').text(order.status ? order.status.toUpperCase() : '-');
                $('#detail-driver').text(order.forklift_driver ? order.forklift_driver.username : '-');
                $('#detail-checker').text(order.checker ? order.checker.username : '-');
                $('#detail-destinasi').text(order.destinasi ? order.destinasi.destinasi : '-');
                $('#detail-no_mobil').text(order.no_mobil || '-');
                $('#detail-jam_muat').text(order.jam_muat || '-');
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
                                ${detail.cancel_to ? '<span class="badge bg-soft-danger text-danger">Cancel</span>' : ''}
                            </td>
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
                    text: "Data Loading Order ini akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('wfg/loading-order') }}/" + id,
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

                window.open("{{ url('wfg/loading-order/download') }}/" + id, '_blank');
            });
        });
    </script>
@endsection
