@extends('layouts.app')

@section('title', '- Monitoring PPIC')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Production Planning & Inventory Control (PPIC)</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">WRM Inventory</a></li>
                            <li class="breadcrumb-item active">Monitoring PPIC</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-primary">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Available to Use</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-available">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-check-decagram text-white"></i>
                                </span>
                            </div>
                        </div>
                        <p class="text-white-50 mt-2 mb-0">Total On-Hand: <span id="sum-onhand">0</span></p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-danger">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Allocated / Reserved</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-reserved">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-lock-clock text-white"></i>
                                </span>
                            </div>
                        </div>
                        <p class="text-white-50 mt-2 mb-0">Items locked for orders</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-success">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Today's Consumption</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-usage">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-trending-down text-white"></i>
                                </span>
                            </div>
                        </div>
                        <p class="text-white-50 mt-2 mb-0">Total outgoing today</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-info">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Upcoming Inbound</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-incoming">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-truck-check-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                        <p class="text-white-50 mt-2 mb-0">In-transit / To be putaway</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Dashboard --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header align-items-xl-center d-xl-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Production Material Status</h4>
                        <div class="flex-shrink-0 mt-2 mt-xl-0">
                            <ul class="nav nav-pills card-header-pills" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-stock-status" role="tab">Stock Projection</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-reservasi" role="tab">Active Reservations</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-movements" role="tab">Recent Movements</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content text-muted">
                            <div class="tab-pane active" id="tab-stock-status" role="tabpanel">
                                <div class="alert alert-soft-info mb-4">
                                    <strong>💡 Stock Cover:</strong> Estimasi berapa hari stok akan habis berdasarkan rata-rata pemakaian 30 hari terakhir.
                                </div>
                                <div class="table-responsive">
                                    <table id="table-stock-status" class="table table-striped table-bordered table-hover nowrap align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Material</th>
                                                <th>MID</th>
                                                <th>UOM</th>
                                                <th>On Hand</th>
                                                <th>Reserved</th>
                                                <th>Available</th>
                                                <th>Avg Daily Usage</th>
                                                <th>Stock Cover</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab-reservasi" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-reservasi" class="table table-striped table-bordered table-hover nowrap align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No. Reservasi</th>
                                                <th>Material</th>
                                                <th>MID</th>
                                                <th>Qty</th>
                                                <th>Status</th>
                                                <th>Tanggal Reservasi</th>
                                                <th>Destinasi</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab-movements" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-movements" class="table table-striped table-bordered table-hover nowrap align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Material</th>
                                                <th>MID</th>
                                                <th>Qty</th>
                                                <th>Group</th>
                                                <th>No Reservasi</th>
                                            </tr>
                                        </thead>
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
        loadSummary();

        // Stock Status Table (Main)
        $('#table-stock-status').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('wrm.inventory.monitoring.ppic.stock-data') }}",
            columns: [{
                    data: 'nama_barang',
                    name: 'nama_barang'
                },
                {
                    data: 'mid',
                    name: 'mid'
                },
                {
                    data: 'uom',
                    name: 'uom'
                },
                {
                    data: 'on_hand',
                    render: $.fn.dataTable.render.number('.', ',', 0)
                },
                {
                    data: 'reserved',
                    render: $.fn.dataTable.render.number('.', ',', 0)
                },
                {
                    data: 'available',
                    render: $.fn.dataTable.render.number('.', ',', 0)
                },
                {
                    data: 'avg_daily',
                    render: $.fn.dataTable.render.number('.', ',', 2)
                },
                {
                    data: 'cover_days',
                    className: 'fw-bold'
                },
                {
                    data: 'status_label',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        function loadSummary() {
            $.get("{{ route('wrm.inventory.monitoring.summary.ppic') }}", function(res) {
                if (res.status) {
                    $('#sum-available').text(res.data.total_available);
                    $('#sum-onhand').text(res.data.total_onhand);
                    $('#sum-reserved').text(res.data.total_reserved);
                    $('#sum-usage').text(res.data.today_usage);
                }
            });
        }

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            let target = $(e.target).attr("href");

            if (target === '#tab-reservasi' && !$.fn.DataTable.isDataTable('#table-reservasi')) {
                $('#table-reservasi').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('wrm.inventory.monitoring.outbound') }}",
                    columns: [{
                            data: 'no_outbound',
                            name: 'no_outbound'
                        },
                        {
                            data: 'material',
                            name: 'material'
                        },
                        {
                            data: 'mid',
                            name: 'mid'
                        },
                        {
                            data: 'qty',
                            render: $.fn.dataTable.render.number('.', ',', 0)
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'tanggal_reservasi',
                            name: 'tanggal_reservasi'
                        },
                        {
                            data: 'destinasi',
                            name: 'destinasi'
                        },
                    ]
                });
            } else if (target === '#tab-movements' && !$.fn.DataTable.isDataTable('#table-movements')) {
                // We can reuse transfer or create a dedicated movement route if needed
                // For now reuse transfer as movement indicator
                $('#table-movements').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('wrm.inventory.monitoring.transfer') }}",
                    columns: [{
                            data: 'tanggal',
                            name: 'tanggal'
                        },
                        {
                            data: 'material',
                            name: 'material'
                        },
                        {
                            data: 'mid',
                            name: 'mid'
                        },
                        {
                            data: 'qty_actual',
                            render: $.fn.dataTable.render.number('.', ',', 0)
                        },
                        {
                            data: 'grade',
                            name: 'grade'
                        },
                        {
                            data: 'no_reservasi',
                            name: 'no_reservasi'
                        },
                    ]
                });
            }
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust();
        });
    });
</script>
@endsection