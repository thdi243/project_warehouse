@extends('layouts.app')

@section('title', '- Monitoring PPIC')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">WRM Monitoring (PPIC)</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">WRM Inventory</a></li>
                            <li class="breadcrumb-item active">Monitoring PPIC</li>
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
                                    <i class="mdi mdi-cube-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate bg-info">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Today's Inbound</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-inbound">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-truck-delivery-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12">
                <div class="card card-animate bg-danger">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Active Draft Outbound</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-outbound">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-clipboard-text-outline text-white"></i>
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
                        <h4 class="card-title mb-0 flex-grow-1">Operational Data (PPIC)</h4>
                        <div class="flex-shrink-0">
                            <ul class="nav nav-pills card-header-pills" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-outbound" role="tab">Planned Outbound</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-soh" role="tab">Stock Availability</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-transfer" role="tab">Transfer History</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab-outbound" role="tabpanel">
                                <table id="table-outbound" class="table table-striped table-bordered table-hover nowrap align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. Reservasi</th>
                                            <th>Desc</th>
                                            <th>MID</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                            <th>Tanggal Reservasi</th>
                                            <th>Destinasi</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="tab-pane" id="tab-soh" role="tabpanel">
                                <table id="table-soh" class="table table-striped table-bordered table-hover nowrap align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Material</th>
                                            <th>MID</th>
                                            <th>Qty</th>
                                            <th>Status</th>
                                            <th>Location</th>
                                            <th>Pallet ID</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="tab-pane" id="tab-transfer" role="tabpanel">
                                <table id="table-transfer" class="table table-striped table-bordered table-hover nowrap align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. Reservasi</th>
                                            <th>No. BA</th>
                                            <th>Material</th>
                                            <th>MID</th>
                                            <th>Qty Actual</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
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
        $.get("{{ route('wrm.inventory.monitoring.summary.ppic') }}", function(res) {
            if (res.status) {
                $('#sum-soh').text(res.data.total_soh);
                $('#sum-inbound').text(res.data.today_inbound);
                $('#sum-outbound').text(res.data.draft_outbound);
            }
        });

        $('#table-outbound').DataTable({
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
                    name: 'qty'
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

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            let target = $(e.target).attr("href");
            if (target === '#tab-soh' && !$.fn.DataTable.isDataTable('#table-soh')) {
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
                            data: 'qty',
                            name: 'qty'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'location',
                            name: 'location'
                        },
                        {
                            data: 'pallet_id',
                            name: 'pallet_id'
                        },
                    ]
                });
            } else if (target === '#tab-transfer' && !$.fn.DataTable.isDataTable('#table-transfer')) {
                $('#table-transfer').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('wrm.inventory.monitoring.transfer') }}",
                    columns: [{
                            data: 'no_reservasi',
                            name: 'no_reservasi'
                        },
                        {
                            data: 'no_ba',
                            name: 'no_ba'
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
                            name: 'qty_actual'
                        },
                        {
                            data: 'grade',
                            name: 'grade'
                        },
                        {
                            data: 'tanggal',
                            name: 'tanggal'
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