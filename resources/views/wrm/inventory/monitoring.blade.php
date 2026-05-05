@extends('layouts.app')

@section('title', '- Monitoring PPIC & Purchasing')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Start Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">WRM Monitoring (PPIC & Purchasing)</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">WRM Inventory</a></li>
                            <li class="breadcrumb-item active">Monitoring</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-white-50 text-truncate mb-0">Total Stock (KG/Units)</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-white"><span class="counter-value" id="summary-total-soh">0</span></h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-cube-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-white-50 text-truncate mb-0">Unique Items</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-white"><span class="counter-value" id="summary-total-items">0</span></h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-tag-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-white-50 text-truncate mb-0">Today's Inbound (KG/Units)</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-white"><span class="counter-value" id="summary-pending-inbound">0</span></h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-light rounded fs-3">
                                    <i class="mdi mdi-truck-delivery-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate bg-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 overflow-hidden">
                                <p class="text-uppercase fw-medium text-white-50 text-truncate mb-0">Draft Outbound</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-4">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-white"><span class="counter-value" id="summary-draft-outbound">0</span></h4>
                            </div>
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

        <!-- Main Monitoring Section -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header align-items-xl-center d-xl-flex border-0">
                        <h4 class="card-title mb-0 flex-grow-1">Operational Monitoring</h4>
                        <div class="flex-shrink-0">
                            <ul class="nav nav-pills card-header-pills" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-soh" role="tab">
                                        Stock On Hand
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-inbound" role="tab">
                                        Inbound Monitoring
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-outbound" role="tab">
                                        Draft Outbound
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#tab-transfer" role="tab">
                                        Transfer History
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Tab SOH -->
                            <div class="tab-pane active" id="tab-soh" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-soh" class="table table-bordered table-hover nowrap align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Material</th>
                                                <th>MID</th>
                                                <th>Supplier</th>
                                                <th>Qty</th>
                                                <th>Status</th>
                                                <th>Location</th>
                                                <th>Aging</th>
                                                <th>Pallet ID</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <!-- Tab Inbound -->
                            <div class="tab-pane" id="tab-inbound" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-inbound" class="table table-bordered table-hover nowrap align-middle" style="width:100%">
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
                            <!-- Tab Outbound -->
                            <div class="tab-pane" id="tab-outbound" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-outbound" class="table table-bordered table-hover nowrap align-middle" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No. Outbound</th>
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
                            <!-- Tab Transfer -->
                            <div class="tab-pane" id="tab-transfer" role="tabpanel">
                                <div class="table-responsive">
                                    <table id="table-transfer" class="table table-bordered table-hover nowrap align-middle" style="width:100%">
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
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Load Summary
        function loadSummary() {
            $.get("{{ route('wrm.inventory.monitoring.summary') }}", function(res) {
                if(res.status) {
                    $('#summary-total-soh').text(res.data.total_soh);
                    $('#summary-total-items').text(res.data.total_items);
                    $('#summary-pending-inbound').text(res.data.pending_inbound);
                    $('#summary-draft-outbound').text(res.data.draft_outbound);
                }
            });
        }
        loadSummary();

        // Datatable SOH
        $('#table-soh').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('wrm.inventory.monitoring.soh') }}",
            columns: [
                { data: 'material', name: 'material' },
                { data: 'mid', name: 'mid' },
                { data: 'supplier', name: 'supplier' },
                { data: 'qty', name: 'qty', render: $.fn.dataTable.render.number('.', ',', 0) },
                { data: 'status', name: 'status', render: function(data) {
                    let badge = 'badge-soft-secondary';
                    if(data === 'UNREST') badge = 'badge-soft-success';
                    if(data === 'RESERVED') badge = 'badge-soft-warning';
                    return `<span class="badge ${badge}">${data}</span>`;
                }},
                { data: 'location', name: 'location' },
                { data: 'aging', name: 'aging' },
                { data: 'pallet_id', name: 'pallet_id' },
            ]
        });

        // Initialize other tables when tab is shown
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            let target = $(e.target).attr("href");
            if(target === '#tab-inbound' && !$.fn.DataTable.isDataTable('#table-inbound')) {
                $('#table-inbound').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('wrm.inventory.monitoring.inbound') }}",
                    columns: [
                        { data: 'no_spb', name: 'no_spb' },
                        { data: 'material', name: 'material' },
                        { data: 'mid', name: 'mid' },
                        { data: 'supplier', name: 'supplier' },
                        { data: 'qty', name: 'qty' },
                        { data: 'status', name: 'status' },
                        { data: 'tanggal_datang', name: 'tanggal_datang' },
                    ]
                });
            } else if(target === '#tab-outbound' && !$.fn.DataTable.isDataTable('#table-outbound')) {
                $('#table-outbound').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('wrm.inventory.monitoring.outbound') }}",
                    columns: [
                        { data: 'no_outbound', name: 'no_outbound' },
                        { data: 'material', name: 'material' },
                        { data: 'mid', name: 'mid' },
                        { data: 'qty', name: 'qty' },
                        { data: 'status', name: 'status' },
                        { data: 'tanggal_reservasi', name: 'tanggal_reservasi' },
                        { data: 'destinasi', name: 'destinasi' },
                    ]
                });
            } else if(target === '#tab-transfer' && !$.fn.DataTable.isDataTable('#table-transfer')) {
                $('#table-transfer').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('wrm.inventory.monitoring.transfer') }}",
                    columns: [
                        { data: 'no_reservasi', name: 'no_reservasi' },
                        { data: 'no_ba', name: 'no_ba' },
                        { data: 'material', name: 'material' },
                        { data: 'mid', name: 'mid' },
                        { data: 'qty_actual', name: 'qty_actual', render: $.fn.dataTable.render.number('.', ',', 0) },
                        { data: 'grade', name: 'grade' },
                        { data: 'tanggal', name: 'tanggal' },
                    ]
                });
            }
            // Redraw tables on tab show to fix header alignment
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    });
</script>
@endsection
