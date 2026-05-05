@extends('layouts.app')

@section('title', '- Monitoring Purchasing')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">WRM Monitoring (Purchasing)</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">WRM Inventory</a></li>
                            <li class="breadcrumb-item active">Monitoring Purchasing</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate bg-primary">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Total Available Stock</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-soh">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-database-check text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate bg-warning">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Aging Stock (>30 Days)</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-aging">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-clock-alert-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12">
                <div class="card card-animate bg-success">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Active Suppliers</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-suppliers">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-account-group-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header align-items-xl-center d-xl-flex border-0">
                        <h4 class="card-title mb-0 flex-grow-1">Inventory Health (Purchasing)</h4>
                        <div class="flex-shrink-0">
                            <ul class="nav nav-pills card-header-pills" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-soh" role="tab">Stock Health & Aging</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-inbound" role="tab">Inbound Tracking</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab-soh" role="tabpanel">
                                <table id="table-soh" class="table table-bordered table-striped table-hover nowrap align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Material</th>
                                            <th>MID</th>
                                            <th>Supplier</th>
                                            <th>Qty</th>
                                            <th>Location</th>
                                            <th>Aging</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="tab-pane" id="tab-inbound" role="tabpanel">
                                <table id="table-inbound" class="table table-bordered table-striped table-hover nowrap align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. SPB</th>
                                            <th>Material</th>
                                            <th>MID</th>
                                            <th>Supplier</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                            <th>Tanggal Datang</th>
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $.get("{{ route('wrm.inventory.monitoring.summary.purchasing') }}", function(res) {
            if (res.status) {
                $('#sum-soh').text(res.data.total_soh);
                $('#sum-aging').text(res.data.aging_stock);
                $('#sum-suppliers').text(res.data.total_suppliers);
            }
        });

        $('#table-soh').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('wrm.inventory.monitoring.soh') }}",
            columns: [{
                    data: 'material',
                    name: 'material'
                },
                {
                    data: 'mid',
                    name: 'mid'
                },
                {
                    data: 'supplier',
                    name: 'supplier'
                },
                {
                    data: 'qty',
                    name: 'qty',
                    render: $.fn.dataTable.render.number('.', ',', 0)
                },
                {
                    data: 'location',
                    name: 'location'
                },
                {
                    data: 'aging',
                    name: 'aging'
                },
            ]
        });

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            let target = $(e.target).attr("href");
            if (target === '#tab-inbound' && !$.fn.DataTable.isDataTable('#table-inbound')) {
                $('#table-inbound').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('wrm.inventory.monitoring.inbound') }}",
                    columns: [{
                            data: 'no_spb',
                            name: 'no_spb'
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
                            data: 'supplier',
                            name: 'supplier'
                        },
                        {
                            data: 'qty',
                            name: 'qty'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'tanggal_datang',
                            name: 'tanggal_datang'
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