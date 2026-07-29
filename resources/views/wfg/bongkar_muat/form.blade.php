@extends('layouts.app')

@section('title', '| Form Muat')

@section('sidebar-size', 'sm')

@section('styles')
    <style>
        .select2-container .select2-selection--single {
            height: 37px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 35px !important;
            padding-left: 12px !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #adb5bd !important;
        }

        .select2-container .select2-selection--multiple {
            min-height: 37px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3577f1 !important;
            border: none !important;
            /* color: #fff !important; */
            font-size: 0.85rem !important;
            padding: 2px 8px !important;
            border-radius: 4px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            /* color: #fff !important; */
            margin-right: 5px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #f06548 !important;
            background-color: transparent !important;
        }

        .table-input {
            border: 1px solid transparent !important;
            background-color: transparent !important;
            padding: 0.25rem 0.5rem !important;
            border-radius: 4px !important;
            transition: all 0.2s ease-in-out !important;
        }

        .table-input:hover {
            border-color: #ced4da !important;
            background-color: #fff !important;
        }

        .table-input:focus {
            border-color: #86b7fe !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
            outline: 0 !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Form Bongkar Muat</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WFG</a></li>
                                <li class="breadcrumb-item active">Form Bongkar Muat</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs for Active Drafts --}}
            <div class="row mb-3">
                <div class="col-12">
                    <ul class="nav nav-tabs nav-tabs-custom nav-success" role="tablist">
                        @foreach ($allDrafts as $d)
                            @php
                                $isCurrent = ($draft && $draft->id == $d->id);
                                $tabUrl = $d->status === 'draft'
                                    ? route('wfg.bongkar_muat.form', ['draft_id' => $d->id])
                                    : route('wfg.bongkar_muat.show', $d->id);
                                
                                $statusBadge = '';
                                if ($d->status === 'submitted') {
                                    $statusBadge = '<span class="badge bg-warning-subtle text-warning rounded-pill">Submitted</span>';
                                } elseif ($d->status === 'approved') {
                                    $statusBadge = '<span class="badge bg-primary-subtle text-primary rounded-pill">Approved</span>';
                                }
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link {{ $isCurrent ? 'active' : '' }} d-flex align-items-center gap-2"
                                    href="{{ $tabUrl }}">
                                    <i class="ri-draft-line"></i>
                                    <span>Draft #{{ $d->id }}</span>
                                    @if ($d->no_mobil)
                                        <span class="badge bg-info-subtle text-info rounded-pill">{{ $d->no_mobil }}</span>
                                    @endif
                                    {!! $statusBadge !!}
                                </a>
                            </li>
                        @endforeach
                        <li class="nav-item ms-auto">
                            <a class="nav-link fw-medium bg-primary text-white"
                                href="{{ route('wfg.bongkar_muat.form', ['create_new' => 1]) }}">
                                <i class="ri-add-line me-1"></i> Baru
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <form id="form-bongkar-muat">
                @csrf
                <input type="hidden" name="id" value="{{ $draft->id }}">
                <div class="row">
                    {{-- Header Section --}}
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-primary bg-gradient text-white">
                                <h5 class="card-title mb-0 text-white"><i class="ri-information-line me-2"></i>Basic
                                    Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal" class="form-control"
                                            value="{{ isset($draft->tanggal) ? (is_string($draft->tanggal) ? $draft->tanggal : $draft->tanggal->format('Y-m-d')) : date('Y-m-d') }}"
                                            required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Shipment SMU</label>
                                        <input type="text" name="shipment_smu" class="form-control"
                                            placeholder="Shipment SMU..." value="{{ $draft->shipment_smu ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Wavepick SMU</label>
                                        <input type="text" name="wavepick_smu" class="form-control"
                                            placeholder="Wavepick SMU..." value="{{ $draft->wavepick_smu ?? '' }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Shipment BAS</label>
                                        <input type="text" name="shipment_bas" class="form-control"
                                            placeholder="Shipment BAS..." value="{{ $draft->shipment_bas ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Wavepick BAS</label>
                                        <input type="text" name="wavepick_bas" class="form-control"
                                            placeholder="Wavepick BAS..." value="{{ $draft->wavepick_bas ?? '' }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Forklift Driver <span class="text-danger">*</span></label>
                                        <select name="forklift_driver_id" class="form-select select2" required>
                                            <option value="">-- Select Driver --</option>
                                            @foreach ($forkliftDrivers as $driver)
                                                <option value="{{ $driver->id }}"
                                                    {{ isset($draft) && $draft->forklift_driver_id == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->username }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Jam Muat</label>
                                        <input type="text" name="jam_muat" id="jam" class="form-control"
                                            placeholder="Auto-set on first item" value="{{ $draft->jam_muat ?? '' }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-info bg-gradient text-white">
                                <h5 class="card-title mb-0 text-white"><i class="ri-truck-line me-2"></i>Vehicle & Gate</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">No. Mobil</label>
                                        <input type="text" name="no_mobil" class="form-control"
                                            value="{{ $draft->no_mobil ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gate</label>
                                        <select name="gate" id="gate" class="form-select select2">
                                            <option value="">-- Select Gate --</option>

                                            @foreach (range(1, 30) as $g)
                                                <option value="{{ $g }}" @selected(isset($draft) && $draft->gate == $g)
                                                    @disabled(in_array($g, $bookedGates) && (!isset($draft) || $draft->gate != $g))>
                                                    {{ $g }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tujuan <span class="text-danger">*</span></label>
                                    <select name="destinasi_id" id="destinasi_id" class="form-select select2" required>
                                        <option value="">Pilih Tujuan</option>
                                        @foreach ($destinations as $destination)
                                            <option value="{{ $destination->id }}"
                                                {{ isset($draft) && $draft->destinasi_id == $destination->id ? 'selected' : '' }}>
                                                {{ $destination->destinasi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">No. Kontainer</label>
                                        <input type="text" name="no_kontainer" class="form-control"
                                            value="{{ $draft->no_kontainer ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">No. Segel BAS</label>
                                        <input type="text" name="no_segel_bas" class="form-control"
                                            value="{{ $draft->no_segel_bas ?? '' }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">No. Segel Vendor</label>
                                        <input type="text" name="no_segel_vendor" class="form-control"
                                            value="{{ $draft->no_segel_vendor ?? '' }}">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Jumlah Slipsheet</label>
                                    <input type="number" name="jumlah_slipsheet" class="form-control"
                                        value="{{ $draft->jumlah_slipsheet ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detail Section --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1"><i class="ri-truck-line me-2"></i>Add Items</h5>
                                <div class="flex-shrink-0 d-flex gap-2">
                                    <button type="button" class="btn btn-soft-primary btn-sm" onclick="addEmptyRow()">
                                        <i class="ri-add-line me-1"></i> Add Row
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="showManualModal()">
                                        <i class="ri-list-check-2 me-1"></i> Add List
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">
                                    <table class="table table-hover align-middle" id="table-items">
                                        <thead class="table-light sticky-top" style="z-index: 1;">
                                            <tr>
                                                <th>Material</th>
                                                <th>Batch</th>
                                                <th>Jenis</th>
                                                <th class="text-center" width="100px">Qty</th>
                                                <th>TO Dummy</th>
                                                <th>TO SAP</th>
                                                <th class="text-center">Flags</th>
                                                <th width="50px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Rows will be added dynamically --}}
                                        </tbody>
                                    </table>
                                </div>

                                <div id="empty-state" class="text-center py-5">
                                    <i class="ri-truck-line fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No items have been selected yet. Click Add Row or Add List
                                        to
                                        add an item.</p>
                                </div>

                                <div class="row mt-4 px-3 d-none" id="summary-section">
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light border shadow-none h-100 mb-0">
                                            <div class="card-body py-2 px-3">
                                                <h6 class="text-info fw-bold mb-2"><i
                                                        class="ri-checkbox-circle-fill me-1 text-info"></i> Summary SMU
                                                </h6>
                                                <div id="summary-smu-list" class="small text-muted"
                                                    style="line-height: 1.6;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card bg-light border shadow-none h-100 mb-0">
                                            <div class="card-body py-2 px-3">
                                                <h6 class="text-success fw-bold mb-2"><i
                                                        class="ri-checkbox-circle-fill me-1 text-success"></i> Summary BAS
                                                </h6>
                                                <div id="summary-bas-list" class="small text-muted"
                                                    style="line-height: 1.6;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <div class="card bg-light border shadow-none h-100 mb-0">
                                            <div
                                                class="card-body py-3 px-3 d-flex align-items-center justify-content-start gap-5">
                                                <h6 class="mb-0">Total Items (<span class="text-primary">Semua
                                                        Row</span>): <span id="total-items"
                                                        class="text-primary fw-bold">0</span></h6>
                                                <h6 class="mb-0">Total Full Pallet: <span id="total-full-pallet"
                                                        class="text-info fw-bold">0</span></h6>
                                                <h6 class="mb-0">Total Pallet Receh: <span id="total-pallet-receh"
                                                        class="text-warning fw-bold">0</span></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="card-footer d-flex flex-column flex-md-row justify-content-end align-items-center gap-3 bg-light p-3">
                                <div
                                    class="d-flex flex-column flex-sm-row gap-2 w-md-auto justify-content-center justify-content-md-end mb-2">
                                    @if (isset($draft))
                                        <button type="button" class="btn btn-danger px-4 shadow w-100 text-nowrap"
                                            id="btnCancelDraft">
                                            <i class="ri-delete-bin-line me-1"></i> CANCEL DRAFT
                                        </button>
                                    @endif
                                    <button type="submit" class="btn btn-success px-5 shadow w-100 text-nowrap">
                                        <i class="ri-save-line me-1"></i> SUBMIT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Manual Input Modal --}}
    <div class="modal fade" id="manualItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <h5 class="modal-title">Item Entry</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="alert alert-info py-2 px-3 mt-2 mb-0 w-100" role="alert">
                        <small>
                            <i class="ri-information-line me-1"></i>
                            Jika MID belum ada di search material, maka belum ada di master barang
                        </small>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Search Material <span class="text-danger">*</span></label>
                        <select id="manual-material-select" class="form-select"></select>
                        {{-- </div>
                    {{-- <div class="mb-3">
                        <label class="form-label">Banyak Item <span class="text-danger">*</span></label>
                        <input type="number" id="manual-row-count" class="form-control" min="0" value="0"
                            required>
                        <small class="text-muted">Banyak data item yang akan dimuat</small> --}}
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Batch Number</label>
                            <input type="text" id="manual-batch" class="form-control" placeholder="Batch...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis</label>
                            <select id="manual-jenis" class="form-select">
                                <option value="P">P (Full Pallet)</option>
                                <option value="R">R (Receh)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" id="manual-qty" class="form-control" min="1" readonly>
                            <small class="text-muted" id="qty-hint">Ambil dari Qty Box Master</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">TO Dummy</label>
                            <input type="text" id="manual-to-dummy" class="form-control" placeholder="...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">TO SAP</label>
                            <input type="text" id="manual-to-sap" class="form-control" placeholder="...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" onclick="addManualItem()">Add to
                        List</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Global items state
        let items = @json($draft->details ?? []);

        if (items.length > 0) {
            items = items.map(i => ({
                material_id: i.material_id,
                mid: i.material ? i.material.mid_barang : '',
                nama_barang: i.material ? i.material.nama_barang : '',
                batch: i.batch_number || '',
                jenis: i.jenis,
                qty: i.qty,
                qty_box: i.material ? i.material.qty_box : 0,
                to_dummy: i.to_dummy || '',
                to_sap: i.to_sap || '',
                double_po: i.double_po,
                cancel_to: i.cancel_to,
                manual_picking: i.manual_picking,
                principal: i.material ? i.material.principal : ''
            }));
        }

        function normalizeMaterialData(data) {
            return {
                material_id: data.id || data.material_id,
                mid: data.mid || data.mid_barang || '',
                nama_barang: data.nama || data.nama_barang || '',
                qty_box: parseInt(data.qty_box) || 0,
                principal: data.principal || ''
            };
        }

        function isRowComplete(item) {
            return item.material_id && item.jenis && parseInt(item.qty) > 0;
        }

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        // Function definitions
        function addItem(data) {
            let item = {
                material_id: data.material_id,
                mid: data.mid,
                nama_barang: data.nama_barang,
                batch: data.batch || '',
                jenis: data.jenis,
                qty: data.qty || 0,
                qty_box: data.qty_box || 0,
                to_dummy: data.to_dummy || '',
                to_sap: data.to_sap || '',
                double_po: data.double_po || false,
                cancel_to: data.cancel_to || false,
                manual_picking: data.manual_picking || false,
                principal: data.principal || ''
            };

            items.push(item);
            renderTable();
            saveProgress();
        }

        let currentSaveRequest = null;
        let previousGate = '';
        let isReverting = false;

        function saveProgress() {
            let formData = $('#form-bongkar-muat').serializeArray();

            // Filter out existing details inputs to avoid duplicates
            formData = formData.filter(item => !item.name.startsWith('details['));

            items.forEach((item, index) => {
                formData.push({
                    name: `details[${index}][material_id]`,
                    value: item.material_id || ''
                });
                formData.push({
                    name: `details[${index}][batch_number]`,
                    value: item.batch || ''
                });
                formData.push({
                    name: `details[${index}][jenis]`,
                    value: item.jenis
                });
                formData.push({
                    name: `details[${index}][qty]`,
                    value: item.qty
                });
                if (item.to_dummy) formData.push({
                    name: `details[${index}][to_dummy]`,
                    value: item.to_dummy
                });
                if (item.to_sap) formData.push({
                    name: `details[${index}][to_sap]`,
                    value: item.to_sap
                });
                if (item.double_po) formData.push({
                    name: `details[${index}][double_po]`,
                    value: 1
                });
                if (item.cancel_to) formData.push({
                    name: `details[${index}][cancel_to]`,
                    value: 1
                });
                if (item.manual_picking) formData.push({
                    name: `details[${index}][manual_picking]`,
                    value: 1
                });
            });

            if (currentSaveRequest) {
                currentSaveRequest.abort();
            }

            currentSaveRequest = $.post("{{ route('wfg.bongkar_muat.save_draft') }}", formData, function(res) {
                if (res.status) {
                    $('#jam').val(res.jam_muat || '');
                    if (res.tanggal) {
                        $('input[name="tanggal"]').val(res.tanggal);
                    }
                    previousGate = $('#gate').val();
                    console.log('Progress saved automatically');
                }
            }).fail(function(xhr) {
                if (xhr.statusText === 'abort') return;

                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan draft.';
                Swal.fire('Bongkar Muat', msg, 'error');

                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message && xhr.responseJSON.message.includes('Gate')) {
                    isReverting = true;
                    $('#gate').val(previousGate).trigger('change');
                    isReverting = false;
                }
            }).always(function() {
                currentSaveRequest = null;
            });
        }

        function renderTable() {
            let html = '';
            items.forEach((item, index) => {

                html += `
                    <tr>
                        <td style="min-width: 180px;">
                            <select class="form-select form-select-sm table-material-select" data-index="${index}">
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm table-input" value="${escapeHtml(item.batch)}" onchange="updateItemField(${index}, 'batch', this.value)" placeholder="Batch...">
                        </td>
                        <td>
                            <select class="form-select form-select-sm fw-semibold text-center text-nowrap" style="min-width: 60px;" onchange="updateItemJenis(${index}, this.value)">
                                <option value="P" ${item.jenis === 'P' ? 'selected' : ''} class="text-primary fw-semibold">P</option>
                                <option value="R" ${item.jenis === 'R' ? 'selected' : ''} class="text-success fw-semibold">R</option>
                            </select>
                        </td>
                        <td class="text-center" style="min-width: 80px;">
                            <input type="number" class="form-control form-control-sm table-input text-center" value="${item.qty}" ${item.jenis === 'P' ? 'readonly' : ''} onchange="updateItemQty(${index}, this.value)" min="1">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm table-input" value="${escapeHtml(item.to_dummy)}" onchange="updateItemField(${index}, 'to_dummy', this.value)" placeholder="...">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm table-input" value="${escapeHtml(item.to_sap)}" onchange="updateItemField(${index}, 'to_sap', this.value)" placeholder="...">
                        </td>
                        <td>
                            <div class="d-flex gap-2 justify-content-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="details[${index}][double_po]" value="1" ${item.double_po ? 'checked' : ''} onchange="updateItemFlag(${index}, 'double_po', this.checked)">
                                    <label class="form-label mb-0 small text-nowrap">2 PO</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="details[${index}][cancel_to]" value="1" ${item.cancel_to ? 'checked' : ''} onchange="updateItemFlag(${index}, 'cancel_to', this.checked)">
                                    <label class="form-label mb-0 small text-nowrap">Cancel TO</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="details[${index}][manual_picking]" value="1" ${item.manual_picking ? 'checked' : ''} onchange="updateItemFlag(${index}, 'manual_picking', this.checked)">
                                    <label class="form-label mb-0 small text-nowrap">Manual</label>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-soft-danger btn-sm" onclick="removeItem(${index})">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            $('#table-items tbody').html(html);

            setTimeout(() => {
                initTableMaterialSelects();
            }, 0);

            const completedItems = items.filter(isRowComplete);
            const totalItems = items.length;
            const totalFullPallet = completedItems.filter(item => item.jenis === 'P').length;
            const totalPalletReceh = completedItems.filter(item => item.jenis === 'R').length;

            $('#total-items').text(totalItems);
            $('#total-full-pallet').text(totalFullPallet);
            $('#total-pallet-receh').text(totalPalletReceh);

            // Dynamic Principal Summary calculation
            let summarySMU = {};
            let summaryBAS = {};

            completedItems.forEach(item => {
                let mid = item.mid || '-';
                let qty = parseInt(item.qty) || 0;
                let principal = item.principal ? item.principal.toUpperCase() : '';

                if (principal === 'BAS') {
                    summaryBAS[mid] = (summaryBAS[mid] || 0) + qty;
                } else {
                    summarySMU[mid] = (summarySMU[mid] || 0) + qty;
                }
            });

            let hasSMU = Object.keys(summarySMU).length > 0;
            let hasBAS = Object.keys(summaryBAS).length > 0;

            if (hasSMU || hasBAS) {
                $('#summary-section').removeClass('d-none');
            } else {
                $('#summary-section').addClass('d-none');
            }

            let htmlSMU = '';
            if (hasSMU) {
                Object.keys(summarySMU).forEach(mid => {
                    htmlSMU +=
                        `<div><strong>${mid}</strong> : ${summarySMU[mid].toLocaleString('id-ID')} Box</div>`;
                });
            } else {
                htmlSMU = '<div class="text-center text-muted py-2">-</div>';
            }
            $('#summary-smu-list').html(htmlSMU);

            let htmlBAS = '';
            if (hasBAS) {
                Object.keys(summaryBAS).forEach(mid => {
                    htmlBAS +=
                        `<div><strong>${mid}</strong> : ${summaryBAS[mid].toLocaleString('id-ID')} Box</div>`;
                });
            } else {
                htmlBAS = '<div class="text-center text-muted py-2">-</div>';
            }
            $('#summary-bas-list').html(htmlBAS);

            if (items.length > 0) {
                $('#empty-state').addClass('d-none');
            } else {
                $('#empty-state').removeClass('d-none');
            }
        }

        function materialLabel(item) {
            if (!item.material_id) return '';

            const mid = item.mid || `${item.material_id}`;
            const nama = item.nama_barang || '';
            return nama ? `${mid}` : mid;
        }

        // Expose functions to window for HTML events
        window.addEmptyRow = function() {
            items.push({
                material_id: null,
                mid: '',
                nama_barang: '',
                batch: '',
                jenis: 'P',
                qty: 0,
                qty_box: 0,
                to_dummy: '',
                to_sap: '',
                double_po: false,
                cancel_to: false,
                manual_picking: false,
                principal: ''
            });

            renderTable();
            saveProgress();
        };

        window.removeItem = function(index) {
            items.splice(index, 1);
            renderTable();
            saveProgress();
        };

        window.updateItemFlag = function(index, flag, value) {
            if (items[index]) {
                if (flag === 'cancel_to' && value === true) {
                    items[index]['double_po'] = false;
                    items[index]['manual_picking'] = false;
                    items[index]['cancel_to'] = true;
                } else if ((flag === 'double_po' || flag === 'manual_picking') && value === true) {
                    items[index]['cancel_to'] = false;
                    items[index][flag] = true;
                } else {
                    items[index][flag] = value;
                }
                renderTable();
                saveProgress();
            }
        };

        window.showManualModal = function() {
            $('#manual-batch').val('');
            $('#manual-qty').val(0);
            $('#manual-jenis').val('P').trigger('change');
            $('#manual-material-select').val(null).trigger('change');
            // $('#manual-row-count').val(0);
            $('#manual-to-dummy').val('');
            $('#manual-to-sap').val('');
            new bootstrap.Modal('#manualItemModal').show();
        };

        window.updateItemField = function(index, field, value) {
            if (items[index]) {
                items[index][field] = value;
                saveProgress();
            }
        };

        window.updateItemJenis = function(index, value) {
            if (items[index]) {
                items[index].jenis = value;
                if (value === 'P') {
                    items[index].qty = items[index].qty_box || 0;
                }
                renderTable();
                saveProgress();
            }
        };

        window.updateItemQty = function(index, value) {
            if (items[index]) {
                let qty = parseInt(value) || 0;
                let qtyBox = parseInt(items[index].qty_box) || 0;
                if (items[index].jenis === 'R' && qty > qtyBox) {
                    Swal.fire('Invalid Quantity!', 'Quantity untuk Receh (R) tidak boleh melebihi Qty Box Master (' +
                        qtyBox + ').', 'warning');
                    qty = qtyBox;
                }
                items[index].qty = qty;
                renderTable();
                saveProgress();
            }
        };

        window.addManualItem = function() {
            let materialData = $('#manual-material-select').select2('data')[0];
            let batch = $('#manual-batch').val();
            let jenis = $('#manual-jenis').val();
            let qty = $('#manual-qty').val();
            let to_dummy = $('#manual-to-dummy').val();
            let to_sap = $('#manual-to-sap').val();
            // let rowCount = parseInt($('#manual-row-count').val()) || 1;

            if (!materialData) {
                Swal.fire('Required!', 'Please select material.', 'warning');
                return;
            }

            if (!qty || qty <= 0) {
                Swal.fire('Required!', 'Please enter a valid quantity.', 'warning');
                return;
            }

            let qtyBox = materialData.qty_box ? parseInt(materialData.qty_box) : 0;
            if (jenis === 'R' && parseInt(qty) > qtyBox) {
                Swal.fire('Invalid Quantity!', 'Quantity untuk Receh (R) tidak boleh melebihi Qty Box Master (' +
                    qtyBox + ').', 'warning');
                return;
            }

            // for (let i = 0; i < rowCount; i++) {
            let data = {
                material_id: materialData.id,
                mid: materialData.mid || materialData.mid_barang,
                nama_barang: materialData.nama || materialData.nama_barang,
                batch: batch,
                jenis: jenis,
                qty: qty,
                qty_box: qtyBox,
                to_dummy: to_dummy,
                to_sap: to_sap,
                principal: materialData.principal || ''
            };

            addItem(data);
            // }

            bootstrap.Modal.getInstance('#manualItemModal').hide();
        };

        function initTableMaterialSelects() {
            $('.table-material-select').each(function() {
                const select = $(this);
                const index = parseInt(select.data('index'));
                const item = items[index];

                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }

                select.select2({
                    placeholder: 'Search material...',
                    width: '100%',
                    minimumInputLength: 2,
                    templateSelection: function(data) {
                        // Untuk AJAX select2, data.text sudah berisi teks option dari DOM
                        if (data.text && data.text.trim() !== '') {
                            return data.text;
                        }
                        // Fallback ke label dari items state
                        if (item && item.material_id) {
                            return materialLabel(item);
                        }
                        return data.text || data.id;
                    },
                    ajax: {
                        url: "{{ route('wfg.bongkar_muat.search_materials') }}",
                        dataType: 'json',
                        data: params => ({
                            q: params.term
                        }),
                        processResults: data => ({
                            results: data
                        })
                    }
                });

                // CARA RESMI Select2 AJAX untuk preload nilai yang sudah ada:
                // Buat Option baru setelah Select2 init, lalu trigger change.
                // Ini memastikan Select2 membaca dan menampilkan nilai dengan benar.
                if (item && item.material_id) {
                    var option = new Option(materialLabel(item), item.material_id, true, true);
                    select.append(option).trigger('change');
                }
            });
        }

        $(document).ready(function() {
            renderTable();

            $('.select2').select2({
                width: '100%'
            });

            previousGate = $('#gate').val();

            let saveTimeout;
            $('#form-bongkar-muat input, #form-bongkar-muat select').on('change input', function() {
                if ($(this).closest('#table-items').length) return;
                if (isReverting) return;

                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(function() {
                    saveProgress();
                }, 500);
            });

            $('#manual-material-select').select2({
                dropdownParent: $('#manualItemModal'),
                placeholder: 'Search material...',
                width: '100%',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('wfg.bongkar_muat.search_materials') }}",
                    dataType: 'json',
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: data
                    })
                }
            }).on('select2:select', function(e) {
                let data = e.params.data;
                let jenis = $('#manual-jenis').val();
                if (jenis === 'P' && data.qty_box) {
                    $('#manual-qty').val(data.qty_box);
                }
            });

            $('#manual-jenis').on('change', function() {
                let jenis = $(this).val();
                let qtyInput = $('#manual-qty');
                let materialData = $('#manual-material-select').select2('data')[0];

                if (jenis === 'P') {
                    qtyInput.attr('readonly', true);
                    $('#qty-hint').text('Ambil dari Qty Box Master');
                    qtyInput.val(materialData && materialData.qty_box ? materialData.qty_box : 0);
                } else {
                    qtyInput.attr('readonly', false);
                    $('#qty-hint').text('Input quantity manual');
                }
            });

            $(document).on('select2:select', '.table-material-select', function(e) {
                const index = $(this).data('index');
                const material = normalizeMaterialData(e.params.data);

                if (!items[index]) return;

                items[index] = {
                    ...items[index],
                    ...material,
                    qty: items[index].jenis === 'P' ? material.qty_box : items[index].qty
                };

                renderTable();
                saveProgress();
            });

            $('#form-bongkar-muat').on('submit', function(e) {
                e.preventDefault();
                const completedItems = items.filter(isRowComplete);

                if (completedItems.length === 0) {
                    Swal.fire('Empty!', 'Please scan at least one item.', 'warning');
                    return;
                }

                if (completedItems.length !== items.length) {
                    Swal.fire('Incomplete Row',
                        'Masih ada row item yang belum lengkap. Pilih material dan isi qty terlebih dahulu, atau hapus row kosong.',
                        'warning');
                    return;
                }

                let formDataArr = $(this).serializeArray();
                // Filter out any default details entries to prevent duplicates or out-of-sync data
                formDataArr = formDataArr.filter(item => !item.name.startsWith('details['));

                completedItems.forEach((item, index) => {
                    formDataArr.push({
                        name: `details[${index}][material_id]`,
                        value: item.material_id
                    });
                    formDataArr.push({
                        name: `details[${index}][batch_number]`,
                        value: item.batch || ''
                    });
                    formDataArr.push({
                        name: `details[${index}][jenis]`,
                        value: item.jenis
                    });
                    formDataArr.push({
                        name: `details[${index}][qty]`,
                        value: item.qty
                    });
                    if (item.to_dummy) formDataArr.push({
                        name: `details[${index}][to_dummy]`,
                        value: item.to_dummy
                    });
                    if (item.to_sap) formDataArr.push({
                        name: `details[${index}][to_sap]`,
                        value: item.to_sap
                    });
                    if (item.double_po) formDataArr.push({
                        name: `details[${index}][double_po]`,
                        value: 1
                    });
                    if (item.cancel_to) formDataArr.push({
                        name: `details[${index}][cancel_to]`,
                        value: 1
                    });
                    if (item.manual_picking) formDataArr.push({
                        name: `details[${index}][manual_picking]`,
                        value: 1
                    });
                });

                let formData = $.param(formDataArr);

                Swal.fire({
                    title: 'Confirm Submission?',
                    text: "This will create a new Bongkar Muat records.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Submit!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ route('wfg.bongkar_muat.store') }}", formData, function(res) {
                            if (res.status) {
                                Swal.fire('Success', res.message, 'success').then(() => {
                                    window.location.href = res.redirect;
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        }).fail(xhr => {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message :
                                'Internal Server Error';
                            Swal.fire('Error', msg, 'error');
                        });
                    }
                });
            });

            $('#btnCancelDraft').click(function() {
                Swal.fire({
                    title: 'Batalkan Draft?',
                    text: "Semua data yang sudah diisi akan dihapus dan form akan direset.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("{{ route('wfg.bongkar_muat.cancel_draft') }}", {
                            _token: "{{ csrf_token() }}",
                            id: "{{ $draft->id }}"
                        }, function(res) {
                            if (res.status) {
                                Swal.fire('Berhasil', res.message, 'success').then(() => {
                                    window.location.href =
                                        "{{ route('wfg.bongkar_muat.form') }}";
                                });
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        });
                    }
                })
            });
        });
    </script>
@endsection
