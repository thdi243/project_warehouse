@extends('layouts.app')

@section('title', '| Create Bongkar Muat')

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
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Create Bongkar Muat</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">WFG</a></li>
                                <li class="breadcrumb-item active">Create Bongkar Muat</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <form id="form-bongkar-muat">
                @csrf
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
                                        <select name="gate" class="form-select select2">
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
                                <h5 class="card-title mb-0 flex-grow-1"><i class="ri-barcode-line me-2"></i>Add Items</h5>
                                <div class="flex-shrink-0 d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="showManualModal()">
                                        <i class="ri-add-line me-1"></i> Add
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-hover align-middle" id="table-items">
                                        <thead class="table-light sticky-top" style="z-index: 1;">
                                            <tr>
                                                <th>Material</th>
                                                <th>Batch</th>
                                                <th>Jenis</th>
                                                <th class="text-center" width="100px">Qty</th>
                                                <th class="text-center">TO Dummy</th>
                                                <th class="text-center">TO SAP</th>
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
                                    <i class="ri-barcode-box-line fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No items scanned yet. Start scanning to add materials.</p>
                                </div>

                                <div class="row mt-3 px-3 d-none" id="summary-section">
                                    <div class="col-md-6 mb-2">
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
                                    <div class="col-md-6 mb-2">
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
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center bg-light p-3">
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <h6 class="mb-0">Total Items: <span id="total-items"
                                            class="text-primary fw-bold">0</span></h6>
                                    <h6 class="mb-0">Total Full Pallet: <span id="total-full-pallet"
                                            class="text-info fw-bold">0</span></h6>
                                    <h6 class="mb-0">Total Pallet Receh: <span id="total-pallet-receh"
                                            class="text-warning fw-bold">0</span></h6>
                                </div>
                                <div class="d-flex gap-2">
                                    @if (isset($draft))
                                        <button type="button" class="btn btn-outline-danger px-4 shadow"
                                            id="btnCancelDraft">
                                            <i class="ri-delete-bin-line me-1"></i> CANCEL DRAFT
                                        </button>
                                    @endif
                                    <button type="submit" class="btn btn-success px-5 shadow">
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
                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" id="manual-qty" class="form-control" min="1" readonly>
                        <small class="text-muted" id="qty-hint">Ambil dari Qty Box Master</small>
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
                    <button type="button" class="btn btn-primary px-4" onclick="addManualItem()">Add to List</button>
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
                to_dummy: i.to_dummy || '',
                to_sap: i.to_sap || '',
                double_po: i.double_po,
                cancel_to: i.cancel_to,
                manual_picking: i.manual_picking,
                principal: i.material ? i.material.principal : ''
            }));
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

        function saveProgress() {
            let formData = $('#form-bongkar-muat').serializeArray();
            items.forEach((item, index) => {
                formData.push({
                    name: `details[${index}][material_id]`,
                    value: item.material_id
                });
                formData.push({
                    name: `details[${index}][batch_number]`,
                    value: item.batch
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

            $.post("{{ route('wfg.bongkar_muat.save_draft') }}", formData, function(res) {
                if (res.status && res.jam_muat) {
                    $('#jam').val(res.jam_muat);
                    console.log('Progress saved automatically');
                }
            });
        }

        function renderTable() {
            let html = '';
            items.forEach((item, index) => {
                html += `
                <tr>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold">${item.nama_barang}</span>
                            <small class="text-muted">${item.mid}</small>
                        </div>
                    </td>
                    <td>${item.batch ?? '-'}</td>
                    <td><span class="badge bg-soft-primary text-primary">${item.jenis}</span></td>
                    <td class="text-center">
                        ${item.qty}
                        <input type="hidden" name="details[${index}][qty]" value="${item.qty}">
                        <input type="hidden" name="details[${index}][material_id]" value="${item.material_id}">
                        <input type="hidden" name="details[${index}][batch_number]" value="${item.batch}">
                        <input type="hidden" name="details[${index}][jenis]" value="${item.jenis}">
                    </td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm text-center" value="${item.to_dummy || ''}" onchange="updateItemFlag(${index}, 'to_dummy', this.value)" style="width: 80px;">
                    </td>
                    <td class="text-center">
                        <input type="text" class="form-control form-control-sm text-center" value="${item.to_sap || ''}" onchange="updateItemFlag(${index}, 'to_sap', this.value)" style="width: 80px;">
                    </td>
                    <td>
                        <div class="d-flex gap-2 justify-content-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="details[${index}][double_po]" value="1" ${item.double_po ? 'checked' : ''} onchange="updateItemFlag(${index}, 'double_po', this.checked)">
                                <label class="form-label mb-0 small">2 PO</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="details[${index}][cancel_to]" value="1" ${item.cancel_to ? 'checked' : ''} onchange="updateItemFlag(${index}, 'cancel_to', this.checked)">
                                <label class="form-label mb-0 small">Cancel TO</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="details[${index}][manual_picking]" value="1" ${item.manual_picking ? 'checked' : ''} onchange="updateItemFlag(${index}, 'manual_picking', this.checked)">
                                <label class="form-label mb-0 small">Manual</label>
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

            const totalFullPallet = items.filter(item => item.jenis === 'P').length;
            const totalPalletReceh = items.filter(item => item.jenis === 'R').length;

            $('#total-items').text(items.length);
            $('#total-full-pallet').text(totalFullPallet);
            $('#total-pallet-receh').text(totalPalletReceh);

            // Dynamic Principal Summary calculation
            let summarySMU = {};
            let summaryBAS = {};

            items.forEach(item => {
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

        // Expose functions to window for HTML events
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
            $('#manual-to-dummy').val('');
            $('#manual-to-sap').val('');
            new bootstrap.Modal('#manualItemModal').show();
        };

        window.addManualItem = function() {
            let materialData = $('#manual-material-select').select2('data')[0];
            let batch = $('#manual-batch').val();
            let jenis = $('#manual-jenis').val();
            let qty = $('#manual-qty').val();
            let to_dummy = $('#manual-to-dummy').val();
            let to_sap = $('#manual-to-sap').val();

            if (!materialData || !qty || qty < 0) {
                Swal.fire('Required!', 'Please select material and valid quantity.', 'warning');
                return;
            }

            // Validasi: jika jenis yg dipilih R, maka qty yg diinput tidak boleh melebihi master qty_box
            let qtyBox = materialData.qty_box ? parseInt(materialData.qty_box) : 0;
            if (jenis === 'R' && parseInt(qty) > qtyBox) {
                Swal.fire('Invalid Quantity!', 'Quantity untuk Receh (R) tidak boleh melebihi Qty Box Master (' +
                    qtyBox + ').', 'warning');
                return;
            }

            let data = {
                material_id: materialData.id,
                mid: materialData.mid || materialData.mid_barang,
                nama_barang: materialData.nama || materialData.nama_barang,
                batch: batch,
                jenis: jenis,
                qty: qty,
                to_dummy: to_dummy,
                to_sap: to_sap,
                principal: materialData.principal || ''
            };

            addItem(data);
            bootstrap.Modal.getInstance('#manualItemModal').hide();
        };

        $(document).ready(function() {
            renderTable();

            $('.select2').select2({
                width: '100%'
            });

            $('#form-bongkar-muat input, #form-bongkar-muat select').on('change input', function() {
                saveProgress();
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

            $('#form-bongkar-muat').on('submit', function(e) {
                e.preventDefault();
                if (items.length === 0) {
                    Swal.fire('Empty!', 'Please scan at least one item.', 'warning');
                    return;
                }

                let formData = $(this).serialize();
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
                        _token: "{{ csrf_token() }}"
                    }, function(res) {
                        if (res.status) {
                            Swal.fire('Berhasil', res.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    });
                }
            })
        });
    </script>
@endsection
