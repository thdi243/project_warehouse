@extends('layouts.app')

@section('title', '- Summary Stock Inventory')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Summary Stock Inventory</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WRM Inventory</a></li>
                                <li class="breadcrumb-item active">Summary Stock</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header border-0 align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Stock Summary</h4>
                        </div>
                        <div class="card-body border border-dashed border-end-0 border-start-0">
                            <ul class="nav nav-tabs nav-justified nav-tabs-custom nav-success mb-3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#summary-item-tab" role="tab"
                                        aria-selected="true">
                                        Per Item
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#summary-spb-tab" role="tab"
                                        aria-selected="false">
                                        Per No SPB
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" data-bs-toggle="tab" href="#summary-moving-average-tab"
                                        role="tab" aria-selected="false">
                                        Moving Average
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content text-muted">
                                <div class="tab-pane active" id="summary-item-tab" role="tabpanel">
                                    <form id="filter-item-form">
                                        <div class="row g-3">
                                            <div class="col-xxl-3 col-sm-6">
                                                <div class="search-box">
                                                    <input type="text" class="form-control" id="filter-mid"
                                                        placeholder="Search MID...">
                                                    <i class="ri-search-line search-icon"></i>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-sm-6">
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary flex-fill"
                                                        id="btn-filter-item">
                                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                                        Filter
                                                    </button>

                                                    <button type="button" class="btn btn-outline-danger flex-fill"
                                                        id="btnReset">
                                                        <i class="ri-refresh me-1 align-bottom"></i>
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" id="summary-spb-tab" role="tabpanel">
                                    <form id="filter-spb-form">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-xxl-3 col-sm-4">
                                                <div class="search-box">
                                                    <input type="text" class="form-control" id="filter-no-spb"
                                                        placeholder="Search No SPB...">

                                                    <i class="ri-search-line search-icon"></i>
                                                </div>
                                            </div>
                                            <div class="col-xxl-3 col-sm-4">
                                                <div class="search-box">
                                                    <input type="text" class="form-control" id="filter-mid-spb"
                                                        placeholder="Search MID...">
                                                    <i class="ri-search-line search-icon"></i>
                                                </div>
                                            </div>
                                            <div class="col-xxl-2 col-sm-4">
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary flex-fill"
                                                        id="btn-filter-spb">
                                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                                        Filter
                                                    </button>

                                                    <button type="button" class="btn btn-outline-danger flex-fill"
                                                        id="btnResetSpb">
                                                        <i class="ri-refresh me-1 align-bottom"></i>
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" id="summary-moving-average-tab" role="tabpanel">
                                    <form id="filter-moving-average-form">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-xxl-3 col-sm-4">
                                                <div class="search-box">
                                                    <input type="text" class="form-control" id="filter-mid-ma"
                                                        placeholder="Search MID or Nama Barang...">
                                                    <i class="ri-search-line search-icon"></i>
                                                </div>
                                            </div>
                                            <div class="col-xxl-3 col-sm-4">
                                                <select class="form-select" id="filter-days-ma">
                                                    <option value="20">20 Hari</option>
                                                    <option value="30" selected>30 Hari</option>
                                                    <option value="40">40 Hari</option>
                                                </select>
                                            </div>
                                            <div class="col-xxl-2 col-sm-4">
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary flex-fill"
                                                        id="btn-filter-ma">
                                                        <i class="ri-equalizer-fill me-1 align-bottom"></i>
                                                        Filter
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger flex-fill"
                                                        id="btnResetMa">
                                                        <i class="ri-refresh me-1 align-bottom"></i>
                                                        Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tab-content text-muted px-4">
                                <div class="tab-pane active" id="summary-item-table-tab" role="tabpanel">
                                    <div class="table-responsive table-card mb-4">
                                        <table class="table table-stripped align-middle table-nowrap mb-0"
                                            id="table-summary-item">
                                            <thead class="table-light text-muted">
                                                <tr>
                                                    <th>MID</th>
                                                    <th>Nama Barang</th>
                                                    <th>UoM</th>
                                                    <th>Qty Unrest</th>
                                                    <th>Qty QI</th>
                                                    <th>Qty Blocked</th>
                                                    <th>Total Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Data will be loaded via Ajax --}}
                                            </tbody>
                                            <tfoot class="table-light fw-semibold" id="table-item-footer">
                                                {{-- Footer rows will be generated dynamically --}}
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane" id="summary-spb-table-tab" role="tabpanel">
                                    <div class="table-responsive table-card mb-4">
                                        <table class="table align-middle table-nowrap mb-0" id="table-summary-spb">
                                            <thead class="table-light text-muted">
                                                <tr>
                                                    <th>No SPB</th>
                                                    <th>UoM</th>
                                                    <th>Qty Unrest</th>
                                                    <th>Qty QI</th>
                                                    <th>Qty Blocked</th>
                                                    <th>Total Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Data will be loaded via Ajax --}}
                                            </tbody>
                                            <tfoot class="table-light fw-semibold" id="table-spb-footer">
                                                {{-- Footer rows will be generated dynamically --}}
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane" id="summary-moving-average-table-tab" role="tabpanel">
                                    <div class="table-responsive table-card mb-4">
                                        <table class="table align-middle table-nowrap mb-0" id="table-summary-ma"
                                            style="width:100%;">
                                            <thead class="table-light text-muted">
                                                <tr>
                                                    <th>MID</th>
                                                    <th>Nama Barang</th>
                                                    <th>UoM</th>
                                                    <th class="text-end">Stock Transfer</th>
                                                    <th class="text-end">Average</th>
                                                    <th class="text-end">On Hand</th>
                                                    <th class="text-center">Cover Days</th>
                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Data will be loaded via Ajax --}}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SPB Detail Modal -->
    <div class="modal fade" id="modalSpbDetail" tabindex="-1" aria-labelledby="modalSpbDetailLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalSpbDetailLabel">
                        <i class="ri-article-line me-2 text-primary"></i> Detail Stock SPB: <span id="spbDetailNumber"
                            class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="table-responsive rounded shadow-sm">
                        <table class="table table-bordered table-striped align-middle text-nowrap mb-0"
                            id="tableSpbDetail">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="50">No</th>
                                    <th>Pallet ID</th>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th class="text-end">Qty (KG)</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                    <th>Supplier</th>
                                    <th>Tanggal Masuk</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const formatNumber = {
                display: function(data) {

                    if (data === null || data === undefined || data === '') {
                        return '-';
                    }

                    const number = parseFloat(data);

                    // jika desimal = 0
                    if (number % 1 === 0) {
                        return number.toLocaleString('id-ID', {
                            maximumFractionDigits: 0
                        });
                    }

                    // jika ada desimal
                    return number.toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                }
            };

            function renderTotal(data, type, row) {
                const total = parseFloat(row.qty_unrest || 0) + parseFloat(row.qty_qi || 0) +
                    parseFloat(row.qty_blocked || 0);
                return formatNumber.display(total);
            }

            function renderFooter(json, selector, totalColspan) {
                if (!json || !json.grand_total_per_uom) {
                    $(selector).empty();
                    return;
                }

                let footerHtml = '';
                const totalRows = json.grand_total_per_uom.length;

                json.grand_total_per_uom.forEach(function(item, index) {

                    footerHtml += `<tr>`;

                    // hanya tampil di row pertama
                    if (index === 0) {
                        footerHtml += `
                            <td colspan="${totalColspan}"
                                rowspan="${totalRows}"
                                class="text-center align-middle fw-bold">
                                Total
                            </td>
                        `;
                    }

                    footerHtml += `
                            <td class="text-start fw-bold">${item.uom}</td>
                            <td class="text-end">${formatNumber.display(item.unrest)}</td>
                            <td class="text-end">${formatNumber.display(item.qi)}</td>
                            <td class="text-end">${formatNumber.display(item.blocked)}</td>
                            <td class="text-end fw-bold">${formatNumber.display(item.all)}</td>
                        </tr>
                    `;
                });

                $(selector).html(footerHtml);
            }

            let itemTable = $('#table-summary-item').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: "{{ route('wrm.inventory.monitoring.summary-stock.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.summary_type = 'item';
                        d.mid = $('#filter-mid').val();
                    },
                    error: function(xhr, error, thrown) {
                        console.error("DataTable error:", error, thrown);
                        console.error("Response:", xhr.responseText);
                    }
                },

                columns: [{
                        data: 'mid'
                    },
                    {
                        data: 'nama_barang'
                    },
                    {
                        data: 'uom'
                    },
                    {
                        data: 'qty_unrest',
                        render: formatNumber
                    },
                    {
                        data: 'qty_qi',
                        render: formatNumber
                    },
                    {
                        data: 'qty_blocked',
                        render: formatNumber
                    },
                    {
                        data: null,
                        render: renderTotal
                    }
                ],
                drawCallback: function(settings) {
                    renderFooter(settings.json, '#table-item-footer', 2);
                }
            });

            let spbTable = null;

            function initSpbTable() {
                if (spbTable) {
                    return;
                }

                spbTable = $('#table-summary-spb').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    ajax: {
                        url: "{{ route('wrm.inventory.monitoring.summary-stock.data') }}",
                        type: 'GET',
                        data: function(d) {
                            d.summary_type = 'spb';
                            d.no_spb = $('#filter-no-spb').val();
                            d.mid = $('#filter-mid-spb').val();
                        },
                        error: function(xhr, error, thrown) {
                            console.error("DataTable error:", error, thrown);
                            console.error("Response:", xhr.responseText);
                        }
                    },

                    columns: [{
                            data: 'no_spb',
                            render: function(data, type, row) {
                                if (!data) return '-';
                                return `<a href="#" class="fw-bold text-primary show-spb-detail" data-spb="${data}">${data}</a>`;
                            }
                        },
                        {
                            data: 'uom'
                        },
                        {
                            data: 'qty_unrest',
                            render: formatNumber
                        },
                        {
                            data: 'qty_qi',
                            render: formatNumber
                        },
                        {
                            data: 'qty_blocked',
                            render: formatNumber
                        },
                        {
                            data: null,
                            render: renderTotal
                        }
                    ],
                    drawCallback: function(settings) {
                        renderFooter(settings.json, '#table-spb-footer', 1);
                    }
                });
            }

            let maTable = null;

            function initMaTable() {
                if (maTable) {
                    return;
                }

                maTable = $('#table-summary-ma').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    ajax: {
                        url: "{{ route('wrm.inventory.monitoring.moving-average.data') }}",
                        type: 'GET',
                        data: function(d) {
                            d.days = $('#filter-days-ma').val();
                            d.search_mid = $('#filter-mid-ma').val();
                        },
                        error: function(xhr, error, thrown) {
                            console.error("DataTable error:", error, thrown);
                        }
                    },
                    columns: [{
                            data: 'mid'
                        },
                        {
                            data: 'nama_barang'
                        },
                        {
                            data: 'uom'
                        },
                        {
                            data: 'total_used',
                            className: 'text-end',
                            render: formatNumber.display
                        },
                        {
                            data: 'avg_daily',
                            className: 'text-end',
                            render: formatNumber.display
                        },
                        {
                            data: 'available',
                            className: 'text-end',
                            render: formatNumber.display
                        },
                        {
                            data: 'cover_days',
                            className: 'text-center fw-semibold'
                        },
                        {
                            data: 'status_label',
                            className: 'text-center'
                        }
                    ]
                });
            }

            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('href');
                if (target === '#summary-item-tab') {
                    $('#summary-item-table-tab').addClass('active show');
                    $('#summary-spb-table-tab').removeClass('active show');
                    $('#summary-moving-average-table-tab').removeClass('active show');
                    itemTable.columns.adjust();
                }

                if (target === '#summary-spb-tab') {
                    $('#summary-spb-table-tab').addClass('active show');
                    $('#summary-item-table-tab').removeClass('active show');
                    $('#summary-moving-average-table-tab').removeClass('active show');
                    initSpbTable();
                    spbTable.columns.adjust();
                }

                if (target === '#summary-moving-average-tab') {
                    $('#summary-moving-average-table-tab').addClass('active show');
                    $('#summary-item-table-tab').removeClass('active show');
                    $('#summary-spb-table-tab').removeClass('active show');
                    initMaTable();
                    maTable.columns.adjust();
                }
            });

            $('#btn-filter-item').on('click', function() {
                itemTable.ajax.reload();
            });

            $('#btn-filter-spb').on('click', function() {
                initSpbTable();
                spbTable.ajax.reload();
            });

            $('#btn-filter-ma').on('click', function() {
                initMaTable();
                maTable.ajax.reload();
            });

            $('#filter-mid').on('change', function() {
                itemTable.ajax.reload();
            });

            $('#filter-no-spb').on('change', function() {
                initSpbTable();
                spbTable.ajax.reload();
            });

            $('#filter-mid-spb').on('change', function() {
                initSpbTable();
                spbTable.ajax.reload();
            });

            $('#filter-mid-ma').on('change', function() {
                initMaTable();
                maTable.ajax.reload();
            });

            $('#filter-days-ma').on('change', function() {
                initMaTable();
                maTable.ajax.reload();
            });

            $('#btnReset, #btnResetSpb, #btnResetMa').on('click', function() {
                $('#filter-mid').val('');
                $('#filter-no-spb').val('');
                $('#filter-mid-spb').val('');
                $('#filter-mid-ma').val('');
                $('#filter-days-ma').val('30');
                itemTable.ajax.reload();
                if (spbTable) {
                    spbTable.ajax.reload();
                }
                if (maTable) {
                    maTable.ajax.reload();
                }
            });

            // Handle clicking SPB detail link
            $(document).on('click', '.show-spb-detail', function(e) {
                e.preventDefault();
                const spbNumber = $(this).data('spb');

                $('#spbDetailNumber').text(spbNumber);
                const $tbody = $('#tableSpbDetail tbody');
                $tbody.html(
                    '<tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-loader-4-line ri-spin me-2"></i>Loading details...</td></tr>'
                );

                const myModal = new bootstrap.Modal(document.getElementById('modalSpbDetail'));
                myModal.show();

                $.ajax({
                    url: "{{ route('wrm.inventory.monitoring.spb-detail.data') }}",
                    type: 'GET',
                    data: {
                        no_spb: spbNumber
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status && res.data) {
                            $tbody.empty();
                            if (res.data.length === 0) {
                                $tbody.append(
                                    '<tr><td colspan="9" class="text-center py-4 text-muted">No stock items found for this SPB.</td></tr>'
                                );
                            } else {
                                let html = '';
                                res.data.forEach((item, index) => {
                                    let locStr = '-';
                                    if (item.bin && item.bin.location) {
                                        let l = item.bin.location;
                                        locStr =
                                            `${l.plant} - ${l.gudang} - ${l.bin} (${item.bin.kolom}.${item.bin.level})`;
                                    }
                                    let incomingDateStr = item.incoming_date ? item
                                        .incoming_date.substring(0, 10) : '-';
                                    html += `
                                        <tr>
                                            <td class="text-center">${index + 1}</td>
                                            <td><b class="text-primary">${item.pallet_id ?? '-'}</b></td>
                                            <td>${item.barang ? item.barang.mid : '-'}</td>
                                            <td>${item.barang ? item.barang.nama_barang : '-'}</td>
                                            <td class="text-end fw-bold">${formatNumber.display(item.qty)}</td>
                                            <td><span class="badge bg-soft-info text-info">${item.status}</span></td>
                                            <td class="small">${locStr}</td>
                                            <td>${item.supplier ?? '-'}</td>
                                            <td>${incomingDateStr}</td>
                                        </tr>
                                    `;
                                });
                                $tbody.html(html);
                            }
                        } else {
                            $tbody.html(
                                '<tr><td colspan="9" class="text-center text-danger py-4">Gagal memuat detail data.</td></tr>'
                            );
                        }
                    },
                    error: function() {
                        $tbody.html(
                            '<tr><td colspan="9" class="text-center text-danger py-4">Terjadi kesalahan koneksi.</td></tr>'
                        );
                    }
                });
            });
        });
    </script>
@endsection
