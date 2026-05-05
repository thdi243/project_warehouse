@extends('layouts.app')

@section('title', '- Monitoring Purchasing')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Procurement & Purchasing Monitoring</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">WRM Inventory</a></li>
                            <li class="breadcrumb-item active">Monitoring Purchasing</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="row">
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate bg-warning">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Items to Reorder</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-reorder">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-cart-alert text-white"></i>
                                </span>
                            </div>
                        </div>
                        <p class="text-white-50 mt-2 mb-0">Below reorder point (ROP)</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate bg-info">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Outstanding PO (Incoming)</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-outstanding">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-truck-delivery-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                        <p class="text-white-50 mt-2 mb-0">Total Qty in-transit</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card card-animate bg-danger">
                    <div class="card-body">
                        <p class="text-uppercase fw-medium text-white-50 mb-0">Overdue Deliveries</p>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-white"><span id="sum-overdue">0</span></h4>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-calendar-alert text-white"></i>
                                </span>
                            </div>
                        </div>
                        <p class="text-white-50 mt-2 mb-0">Pending past incoming date</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header align-items-xl-center d-xl-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Procurement Status</h4>
                        <div class="flex-shrink-0 mt-2 mt-xl-0">
                            <ul class="nav nav-pills card-header-pills" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-procurement" role="tab">Reorder List</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-pending-po" role="tab">Outstanding Inbound</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab-procurement" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-procurement" class="table table-bordered table-striped table-hover nowrap align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Material</th>
                                                <th>MID</th>
                                                <th>UOM</th>
                                                <th>Available</th>
                                                <th>Incoming (PO)</th>
                                                <th>Total Expected</th>
                                                <th>Min Stock</th>
                                                <th>Reorder Point</th>
                                                <th>Action Plan</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab-pending-po" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-pending-po" class="table table-bordered table-striped table-hover nowrap align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No. SPB</th>
                                                <th>Material</th>
                                                <th>MID</th>
                                                <th>Supplier</th>
                                                <th>Qty</th>
                                                <th>Status</th>
                                                <th>Incoming Date</th>
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

        $('#table-procurement').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('wrm.inventory.monitoring.purchasing.stock-data') }}",
            columns: [
                { data: 'nama_barang', name: 'nama_barang' },
                { data: 'mid', name: 'mid' },
                { data: 'uom', name: 'uom' },
                { data: 'available', render: $.fn.dataTable.render.number('.', ',', 0) },
                { data: 'outstanding_po', render: $.fn.dataTable.render.number('.', ',', 0) },
                { data: 'total_expected', render: $.fn.dataTable.render.number('.', ',', 0) },
                { data: 'min_stock', render: $.fn.dataTable.render.number('.', ',', 0) },
                { data: 'reorder_point', render: $.fn.dataTable.render.number('.', ',', 0) },
                { data: 'status_label', orderable: false, searchable: false }
            ]
        });

        function loadSummary() {
            $.get("{{ route('wrm.inventory.monitoring.summary.purchasing') }}", function(res) {
                if (res.status) {
                    $('#sum-reorder').text(res.data.reorder_count);
                    $('#sum-outstanding').text(res.data.outstanding_po);
                    $('#sum-overdue').text(res.data.overdue_po);
                }
            });
        }

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            let target = $(e.target).attr("href");
            if (target === '#tab-pending-po' && !$.fn.DataTable.isDataTable('#table-pending-po')) {
                $('#table-pending-po').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('wrm.inventory.monitoring.inbound') }}",
                    columns: [
                        { data: 'no_spb', name: 'no_spb' },
                        { data: 'material', name: 'material' },
                        { data: 'mid', name: 'mid' },
                        { data: 'supplier', name: 'supplier' },
                        { data: 'qty', render: $.fn.dataTable.render.number('.', ',', 0) },
                        { data: 'status', name: 'status' },
                        { data: 'tanggal_datang', name: 'tanggal_datang' },
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