@extends('layouts.app')

@section('title', '| Approval Bongkar WRM')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Approval & Verifikasi Bongkar WRM</h4>
                        <div class="page-title-right">
                            <a href="{{ route('wrm.bongkar_muat.index') }}" class="btn btn-soft-secondary btn-sm">
                                <i class="ri-arrow-left-line me-1"></i> Data Bongkar WRM
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
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Search</label>
                                    <div class="search-box">
                                        <input type="text" id="searchInput" class="form-control"
                                            placeholder="No Dokumen, SPKB, ECP, No Polisi...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Start Date</label>
                                    <input type="date" id="startDate" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small fw-bold text-uppercase">End Date</label>
                                    <input type="date" id="endDate" class="form-control">
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
                        <table class="table table-hover align-middle text-nowrap" id="wrmApprovalTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Tanggal</th>
                                    <th>No. Dokumen</th>
                                    <th>No. SPKB</th>
                                    <th>No. ECP</th>
                                    <th>No. Polisi</th>
                                    <th>Supplier / Vendor</th>
                                    <th>Checker</th>
                                    <th>QC</th>
                                    <th>Driver</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
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
                const tbody = $('#wrmApprovalTable tbody');

                tbody.html(
                    '<tr><td colspan="12" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>'
                );
                $('#paginationContainer').empty();

                $.ajax({
                    url: "{{ route('wrm.bongkar_muat.approval_data') }}",
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
                                const noUrut = ((paginatedData.current_page - 1) * paginatedData.per_page) + (index + 1);

                                const checkerName = order.checker ? order.checker.username : '-';
                                const qcName = order.qc_user ? order.qc_user.username : (order.qc_signature ? 'QC Approved' : '-');
                                const driverName = order.driver_name ? order.driver_name : '-';
                                const tanggalStr = order.tanggal ? order.tanggal.substring(0, 10) : '-';
                                const showUrl = "{{ url('wrm/bongkar-muat/show') }}/" + order.id;

                                const row = `
                                    <tr>
                                        <td class="text-center fw-medium">${noUrut}</td>
                                        <td>${tanggalStr}</td>
                                        <td class="fw-bold">${order.no_dokumen || '-'}</td>
                                        <td>${order.no_spkb || '-'}</td>
                                        <td><span class="badge bg-soft-info text-info">${order.no_ecp || '-'}</span></td>
                                        <td><span class="badge bg-soft-dark text-dark fw-bold">${order.no_mobil || '-'}</span></td>
                                        <td>${order.supplier_vendor || '-'}</td>
                                        <td>${checkerName}</td>
                                        <td>${qcName}</td>
                                        <td>${driverName}</td>
                                        <td><span class="badge bg-soft-success text-success text-uppercase">${order.status}</span></td>
                                        <td class="text-center">
                                            <a href="${showUrl}" class="btn btn-sm btn-success">
                                                <i class="ri-check-double-line me-1"></i> Verifikasi Sekarang
                                            </a>
                                        </td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });

                            renderPagination(paginatedData);
                        } else {
                            tbody.html(
                                '<tr><td colspan="12" class="text-center py-5 text-muted"><i class="ri-checkbox-circle-line fs-24 d-block mb-2 text-success"></i>Semua order bongkar WRM sudah selesai diverifikasi.</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        tbody.html(
                            '<tr><td colspan="12" class="text-center py-4 text-danger"><i class="ri-error-warning-line me-1"></i> Gagal memuat data.</td></tr>'
                        );
                    }
                });
            };

            function renderPagination(data) {
                if (data.total <= data.per_page) return;

                let paginationHtml = '<ul class="pagination pagination-separated mb-0">';
                if (data.prev_page_url) {
                    paginationHtml += `<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="loadData(${data.current_page - 1})"><i class="ri-arrow-left-s-line"></i></a></li>`;
                } else {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link"><i class="ri-arrow-left-s-line"></i></span></li>`;
                }

                for (let i = 1; i <= data.last_page; i++) {
                    if (i === 1 || i === data.last_page || (i >= data.current_page - 1 && i <= data.current_page + 1)) {
                        paginationHtml += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a href="javascript:void(0);" class="page-link" onclick="loadData(${i})">${i}</a></li>`;
                    } else if (i === data.current_page - 2 || i === data.current_page + 2) {
                        paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }

                if (data.next_page_url) {
                    paginationHtml += `<li class="page-item"><a href="javascript:void(0);" class="page-link" onclick="loadData(${data.current_page + 1})"><i class="ri-arrow-right-s-line"></i></a></li>`;
                } else {
                    paginationHtml += `<li class="page-item disabled"><span class="page-link"><i class="ri-arrow-right-s-line"></i></span></li>`;
                }
                paginationHtml += '</ul>';

                $('#paginationContainer').html(paginationHtml);
            }

            $('#searchInput').on('keyup', debounce(function() { loadData(1); }, 400));
            $('#startDate, #endDate').on('change', function() { loadData(1); });
            $('#btnReset').on('click', function() {
                $('#searchInput').val('');
                $('#startDate').val('');
                $('#endDate').val('');
                loadData(1);
            });

            loadData(1);
        });
    </script>
@endsection
