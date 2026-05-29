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
                            <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
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
                                                        id="btnReset">
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
                            </div>
                        </div>
                    </div>
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
                            data: 'no_spb'
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

            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('href');
                if (target === '#summary-item-tab') {
                    $('#summary-item-table-tab').addClass('active show');
                    $('#summary-spb-table-tab').removeClass('active show');
                    itemTable.columns.adjust();
                }

                if (target === '#summary-spb-tab') {
                    $('#summary-spb-table-tab').addClass('active show');
                    $('#summary-item-table-tab').removeClass('active show');
                    initSpbTable();
                    spbTable.columns.adjust();
                }
            });

            $('#btn-filter-item').on('click', function() {
                itemTable.ajax.reload();
            });

            $('#btn-filter-spb').on('click', function() {
                initSpbTable();
                spbTable.ajax.reload();
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

            $('#btnReset').on('click', function() {
                $('#filter-mid').val('');
                $('#filter-no-spb').val('');
                $('#filter-mid-spb').val('');
                itemTable.ajax.reload();
                if (spbTable) {
                    spbTable.ajax.reload();
                }
            });
        });
    </script>
@endsection
