@extends('layouts.app')

@section('title', ' | Data Draft Outbound')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            {{-- Card Filter --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Filter Data</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Group</label>
                            <select class="form-select" id="filterGroup">
                                <option value="">Semua Group</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Jenis Bahan</label>
                            <select class="form-select" id="filterJenisBahan">
                                <option value="">Semua Jenis Bahan</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">MID</label>
                            <input type="text" class="form-control" id="filterMid" placeholder="Cari MID">
                        </div>

                        <div class="col-md-3 d-flex align-items-end gap-2 text-nowrap">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="collapse"
                                data-bs-target="#advancedFilter">
                                <i class="mdi mdi-filter-plus"></i>
                            </button>
                            <button class="btn btn-primary w-100" id="btnReset">
                                <i class="mdi mdi-refresh me-2"></i>Reset
                            </button>
                        </div>
                    </div>

                    <div class="collapse mt-3" id="advancedFilter">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <label class="form-label">Issued Date</label>
                                <input type="date" class="form-control" id="filterDate" placeholder="Cari MID">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">No Reservasi</label>
                                <input type="text" class="form-control" id="filterNoReserved"
                                    placeholder="Cari No Reservasi">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Supplier</label>
                                <select class="form-select" id="filterSupplier">
                                    <option value="">Semua Supplier</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->nama }}">{{ $sup->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- <div class="col-md-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" id="filterLocation">
                            </div> --}}

                        </div>

                    </div>
                </div>

            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Data Draft Outbound Raw Material</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-nowrap" id="tableStock">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>No Reservasi</th>
                                    <th>Shift</th>
                                    <th>Draft Date</th>
                                    <th>Qty Request (KG)</th>
                                    <th>Status Transfer</th>
                                    <th>Catatan</th>
                                    <th class="text-center">Aksi</th>
                                    <th class="text-center">Assign Driver</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-end" id="pagination"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal detail --}}
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Detail Outbound
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3 p-3 bg-light rounded" id="checklistSection" style="display: none;">
                        <h6 class="fw-bold mb-2">Checklist Kondisi Barang:</h6>
                        <div id="checklistContent" class="d-flex flex-wrap gap-2"></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-md align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 40px;"><input type="checkbox" id="checkAllItems"
                                            class="form-check-input"></th>
                                    <th>No</th>
                                    <th>No Barcode</th>
                                    <th>No SPB</th>
                                    <th>Supplier</th>
                                    <th>Pallet ID</th>
                                    <th>MID</th>
                                    <th>Nama Barang</th>
                                    <th>Group</th>
                                    <th>Qty (KG)</th>
                                    <th>Status</th>
                                    <th>Driver Forklift</th>
                                    <th>Lokasi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- isi dari ajax -->
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-danger me-auto" id="btnBulkCancel" style="display: none;">
                        <i class="mdi mdi-close"></i> Cancel Terpilih (<span id="selectedCount">0</span>)
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Header Draft Outbound</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEdit">
                    @csrf
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="editNoReservasi">No Reservasi <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editNoReservasi" name="no_reservasi"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editTglReservasi">Tgl Reservasi <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="editTglReservasi" name="tgl_reservasi"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editShift">Shift <span class="text-danger">*</span></label>
                            <select class="form-select" id="editShift" name="shift" required>
                                <option value="1">Shift 1</option>
                                <option value="2">Shift 2</option>
                                <option value="3">Shift 3</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editCatatan">Catatan</label>
                            <textarea class="form-control" id="editCatatan" name="catatan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitEdit">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            function numberFormat(x) {
                if (x === null || x === undefined) return '0';
                let val = parseFloat(x);
                return val.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            }

            loadData();
            loadFilter();

            function loadData(page = 1) {

                let group = $('#filterGroup').val();
                let jenisBahan = $('#filterJenisBahan').val();
                let mid = $('#filterMid').val();
                let date = $('#filterDate').val();
                let supplier = $('#filterSupplier').val();
                let no_reservasi = $('#filterNoReserved').val();

                $.get("{{ route('wrm.inventory.get-data-outbound') }}", {
                    page: page,
                    group: group,
                    jenis_bahan: jenisBahan,
                    mid: mid,
                    date: date,
                    supplier: supplier,
                    no_reservasi: no_reservasi,
                }, function(res) {

                    let html = '';
                    let data = res.data.data;
                    let startNo = res.data.from;

                    if (data.length === 0) {

                        html = `
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="mdi mdi-database-off-outline" style="font-size:32px"></i>
                                        <span class="mt-2">Data tidak ditemukan</span>
                                    </div>
                                </td>
                            </tr>
                        `;

                    } else {

                        data.forEach((d, index) => {
                            let driverName = d.driver ? d.driver.nama_lengkap || d.driver.username :
                                '<span class="text-muted">Belum di-assign</span>';

                            let statusBadge = '';
                            if (d.status_transfer === 'PENDING') {
                                statusBadge = '<span class="badge bg-warning">PENDING</span>';
                            } else if (d.status_transfer === 'ASSIGNED') {
                                statusBadge = '<span class="badge bg-info">ASSIGNED</span>';
                            } else if (d.status_transfer === 'COMPLETED') {
                                statusBadge = '<span class="badge bg-success">COMPLETED</span>';
                            } else {
                                statusBadge =
                                    `<span class="badge bg-secondary">${d.status_transfer ?? 'PENDING'}</span>`;
                            }

                            let btnAssignText = d.status_transfer === 'ASSIGNED' ?
                                '<i class="mdi mdi-account-switch"></i> Ganti Driver' :
                                '<i class="mdi mdi-account-plus"></i> Assign Driver';

                            let actionButtons = `
                                <button class="btn btn-sm btn-success btnMagicNumber"
                                    data-id="${d.id}"
                                    title="Print List Draft untuk Forklift">
                                    <i class="mdi mdi-printer"></i> List
                                </button>
                            `;

                            let isDisabled = d.status_transfer === 'COMPLETED' ? 'disabled' : '';
                            let actionButtonsAssign = `
                                <a href="/wrm/inventory/draft-outbound/${d.id}/assign-driver"
                                   class="btn btn-sm ${isDisabled ? 'btn-secondary disabled' : 'btn-primary'}"
                                   title="Assign Driver Forklift"
                                   ${isDisabled ? 'onclick="return false;"' : ''}>
                                    ${btnAssignText}
                                </a>
                            `;

                            if (d.status_transfer === 'ASSIGNED') {
                                actionButtonsAssign += `
                                    <button class="btn btn-sm btn-success btnCompleteTransfer"
                                        data-id="${d.id}"
                                        title="Selesai Dipindah">
                                        <i class="mdi mdi-check"></i> Selesai Transfer
                                    </button>
                                `;
                            }

                            actionButtons += `
                                <button class="btn btn-sm btn-warning btnEdit text-white"
                                    data-id="${d.id}">
                                    <i class="mdi mdi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-info btnDetail"
                                    data-id="${d.id}">
                                    <i class="mdi mdi-eye"></i> Detail
                                </button>
                                <button class="btn btn-sm btn-danger btnCancel"
                                    data-id="${d.id}">
                                    <i class="mdi mdi-close"></i> Cancel Draft
                                </button>
                            `;

                            html += `
                                <tr>
                                    <td class="text-center">${startNo + index}</td>
                                    <td><b class="text-primary">${d.no_reservasi ?? '-'}</b></td>
                                    <td>Shift ${d.shift ?? '-'}</td>
                                    <td>${d.reservasi_date}</td>
                                    <td>${numberFormat(d.qty_request)}</td>
                                    <td>${statusBadge}</td>
                                    <td>${d.catatan ?? '-'}</td>
                                    <td class="text-center">
                                        ${actionButtons}
                                    </td>
                                    <td class="text-start">
                                        ${actionButtonsAssign}
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $('#tableStock tbody').html(html);

                    renderPagination(res.data);

                });
            }

            function renderPagination(data) {

                let html = '';

                let current = data.current_page;
                let last = data.last_page;

                html +=
                    `<button class="btn btn-sm btn-light page-btn" data-page="${current-1}" ${current==1?'disabled':''}>Prev</button>`;

                let start = Math.max(1, current - 2);
                let end = Math.min(last, current + 2);

                if (start > 1) {
                    html += `<button class="btn btn-sm btn-light page-btn" data-page="1">1</button>`;
                    if (start > 2) html += `<span class="mx-1">...</span>`;
                }

                for (let i = start; i <= end; i++) {

                    html += `
                        <button class="btn btn-sm ${i==current?'btn-primary':'btn-light'} page-btn"
                        data-page="${i}">
                        ${i}
                        </button>
                    `;
                }

                if (end < last) {

                    if (end < last - 1) html += `<span class="mx-1">...</span>`;

                    html += `<button class="btn btn-sm btn-light page-btn" data-page="${last}">${last}</button>`;
                }

                html +=
                    `<button class="btn btn-sm btn-light page-btn" data-page="${current+1}" ${current==last?'disabled':''}>Next</button>`;

                $('#pagination').html(html);
            }

            $(document).on('click', '.page-btn', function() {

                let page = $(this).data('page');

                loadData(page);

            });

            $('#filterGroup, #filterJenisBahan, #filterDate, #filterSupplier').on('change', function() {
                loadData();
            });

            $('#filterNoReserved').on('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    loadData();
                }, 500);
            });

            let typingTimer;

            $('#filterMid').on('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    loadData();
                }, 500);
            });

            function loadFilter() {

                $.get("{{ route('wrm.inventory.getFilter') }}", function(res) {

                    let groupHtml = `<option value="">Semua Group</option>`;
                    res.groups.forEach(g => {
                        groupHtml += `<option value="${g}">${g}</option>`;
                    });

                    $('#filterGroup').html(groupHtml);

                    let jenisHtml = `<option value="">Semua Jenis Bahan</option>`;
                    res.jenis_bahan.forEach(j => {
                        jenisHtml += `<option value="${j}">${j}</option>`;
                    });

                    $('#filterJenisBahan').html(jenisHtml);

                });

            }

            function showOutboundDetail(id) {
                $('#checkAllItems').prop('checked', false);
                $('#btnBulkCancel').hide();
                $('#btnBulkAssignDriver').hide();
                $('#selectedCount').text('0');
                $('#selectedAssignCount').text('0');

                $.get(`/wrm/inventory/detail-data-outbound/${id}`, function(res) {

                    let html = '';

                    // Tampilkan Checklist
                    if (res.header && res.header.checklist_kondisi) {
                        let checklist = JSON.parse(res.header.checklist_kondisi);
                        if (checklist && checklist.length > 0) {
                            let checkHtml = '';
                            checklist.forEach(val => {
                                checkHtml +=
                                    `<span class="badge bg-success"><i class="mdi mdi-check-circle me-1"></i>${val}</span>`;
                            });
                            $('#checklistContent').html(checkHtml);
                            $('#checklistSection').show();
                        } else {
                            $('#checklistSection').hide();
                        }
                    } else {
                        $('#checklistSection').hide();
                    }

                    res.data.forEach((d, i) => {
                        let driverName = d.driver ? d.driver.nama_lengkap || d.driver.username :
                            '<span class="text-muted">Belum di-assign</span>';

                        let statusBadge = d.status === 'BA WAITING' ?
                            '<span class="badge bg-success">BA WAITING</span>' :
                            '<span class="badge bg-secondary">' + d.status + '</span>';

                        html += `
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input select-item" 
                                        data-id="${d.id}" 
                                        data-outbound-id="${d.outbound_id}" 
                                        data-status="${d.status}">
                                </td>
                                <td>${i+1}</td>
                                <td>${d.barcode ?? '-'}</td>
                                <td>${d.no_spb ?? '-'}</td>
                                <td style="font-size: 11px;">${d.supplier ?? '-'}</td>
                                <td>${d.pallet_id}</td>
                                <td>${d.barang.mid}</td>
                                <td style="font-size: 11px;">${d.barang.nama_barang}</td>
                                <td>${d.group ?? '-'}</td>
                                <td>${numberFormat(d.qty)}</td>
                                <td>${statusBadge}</td>
                                <td>${driverName}</td>
                                <td style="font-size: 11px;">${d.bin.location.plant} - ${d.bin.location.gudang} - ${d.bin.location.bin} - (${d.bin.kolom}.${d.bin.level})</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-danger btnCancelItem"
                                        data-id="${d.id}"
                                        data-outbound-id="${d.outbound_id}"
                                        title="Cancel Item draft ini">
                                        <i class="mdi mdi-close"></i> Cancel
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    $('#modalDetail tbody').html(html);

                    $('#modalDetail').modal('show');

                });
            }

            $(document).on('click', '.btnDetail', function() {

                let id = $(this).data('id');
                showOutboundDetail(id);

            });

            // Edit draft outbound header
            $(document).on('click', '.btnEdit', function() {
                let id = $(this).data('id');

                $.get(`/wrm/inventory/detail-data-outbound/${id}`, function(res) {
                    if (res.status && res.header) {
                        $('#editId').val(res.header.id);
                        $('#editNoReservasi').val(res.header.no_reservasi);

                        // Extract only date string (YYYY-MM-DD)
                        let dateVal = '';
                        if (res.header.reservasi_date) {
                            dateVal = res.header.reservasi_date.substring(0, 10);
                        }
                        $('#editTglReservasi').val(dateVal);
                        $('#editShift').val(res.header.shift);
                        $('#editCatatan').val(res.header.catatan);

                        $('#modalEdit').modal('show');
                    } else {
                        Swal.fire('Error', 'Gagal mengambil data header.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Koneksi gagal saat mengambil data.', 'error');
                });
            });

            $('#formEdit').on('submit', function(e) {
                e.preventDefault();
                let id = $('#editId').val();

                $.ajax({
                    url: `/wrm/inventory/update-outbound/${id}`,
                    method: "POST",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Menyimpan...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message ??
                                'Header draft outbound berhasil diperbarui',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#modalEdit').modal('hide');
                            loadData();
                        });
                    },
                    error: function(xhr) {
                        let errMsg = xhr.responseJSON?.message ?? 'Terjadi kesalahan sistem';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errMsg
                        });
                    }
                });
            });

            $('#btnReset').click(function() {

                $('#filterGroup').val('');
                $('#filterJenisBahan').val('');
                $('#filterMid').val('');

                loadData();

            });

            $(document).on('click', '.btnMagicNumber', function() {

                let id = $(this).data('id');

                // Buka magic number di tab baru
                window.open(`/wrm/inventory/magic-number/${id}`, '_blank');

            });

            $(document).on('click', '.btnCancel', function() {

                let id = $(this).data('id');

                Swal.fire({
                    title: 'Cancel Reservasi?',
                    text: 'Semua item pada no reservasi ini akan dihapus dari draft outbound dan dikembalikan ke Stock on Hand (SOH).',
                    icon: 'warning',
                    showCancelButton: true
                }).then((r) => {

                    if (r.isConfirmed) {

                        $.post(`/wrm/inventory/cancel-outbound/${id}`, {
                            _token: "{{ csrf_token() }}"
                        }, function(res) {

                            Swal.fire('Berhasil', res.message, 'success');

                            loadData();

                        });

                    }

                });

            });

            $(document).on('click', '.btnCancelItem', function() {
                let itemId = $(this).data('id');
                let outboundId = $(this).data('outbound-id');

                Swal.fire({
                    title: 'Cancel Item Draft?',
                    text: 'Item ini akan dihapus dari draft outbound dan dikembalikan ke Stock on Hand (SOH).',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Cancel!',
                    cancelButtonText: 'Batal'
                }).then((r) => {
                    if (r.isConfirmed) {
                        $.post(`/wrm/inventory/cancel-outbound-item/${itemId}`, {
                            _token: "{{ csrf_token() }}"
                        }, function(res) {
                            Swal.fire('Berhasil', res.message, 'success');

                            loadData();

                            if (res.deleted_header) {
                                $('#modalDetail').modal('hide');
                            } else {
                                showOutboundDetail(outboundId);
                            }
                        }).fail(function(xhr) {
                            let errMsg = xhr.responseJSON ? xhr.responseJSON.message :
                                'Terjadi kesalahan saat membatalkan item.';
                            Swal.fire('Error', errMsg, 'error');
                        });
                    }
                });
            });

            // Check/Uncheck all items
            $(document).on('change', '#checkAllItems', function() {
                let checked = $(this).prop('checked');
                $('.select-item').prop('checked', checked);
                updateBulkCancelButton();
            });

            // Single item checkbox change
            $(document).on('change', '.select-item', function() {
                if ($('.select-item:checked').length === $('.select-item').length) {
                    $('#checkAllItems').prop('checked', true);
                } else {
                    $('#checkAllItems').prop('checked', false);
                }
                updateBulkCancelButton();
            });

            function updateBulkCancelButton() {
                let checkedCount = $('.select-item:checked').length;
                if (checkedCount > 0) {
                    $('#selectedCount').text(checkedCount);
                    $('#btnBulkCancel').fadeIn(200);
                } else {
                    $('#btnBulkCancel').fadeOut(200);
                }
            }

            // Bulk cancel action
            $(document).on('click', '#btnBulkCancel', function() {
                let selectedIds = [];
                let outboundId = null;

                $('.select-item:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                    if (!outboundId) {
                        outboundId = $(this).data('outbound-id');
                    }
                });

                if (selectedIds.length === 0) return;

                Swal.fire({
                    title: 'Cancel Item Terpilih?',
                    text: `${selectedIds.length} item terpilih akan dihapus dari draft outbound dan dikembalikan ke Stock on Hand (SOH).`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Cancel!',
                    cancelButtonText: 'Batal'
                }).then((r) => {
                    if (r.isConfirmed) {
                        $.post(`/wrm/inventory/cancel-outbound-items`, {
                            _token: "{{ csrf_token() }}",
                            ids: selectedIds
                        }, function(res) {
                            Swal.fire('Berhasil', res.message, 'success');

                            loadData();

                            if (res.deleted_headers && res.deleted_headers
                                .includes(outboundId)) {
                                $('#modalDetail').modal('hide');
                            } else {
                                showOutboundDetail(outboundId);
                            }
                        }).fail(function(xhr) {
                            let errMsg = xhr.responseJSON ? xhr.responseJSON
                                .message :
                                'Terjadi kesalahan saat membatalkan item.';
                            Swal.fire('Error', errMsg, 'error');
                        });
                    }
                });
            });

            // Complete Transfer click
            $(document).on('click', '.btnCompleteTransfer', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Selesai Dipindah?',
                    text: 'Aksi ini akan memproses pemindahan stock dan memperbarui inventory balance.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Selesai!',
                    cancelButtonText: 'Batal'
                }).then((r) => {
                    if (r.isConfirmed) {
                        $.ajax({
                            url: `/wrm/inventory/complete-transfer/${id}`,
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            beforeSend: function() {
                                Swal.fire({
                                    title: 'Memproses...',
                                    allowOutsideClick: false,
                                    didOpen: () => Swal
                                        .showLoading()
                                });
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message ??
                                        'Proses pemindahan selesai.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    loadData();
                                });
                            },
                            error: function(xhr) {
                                let errMsg = xhr.responseJSON?.message ??
                                    'Terjadi kesalahan sistem';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: errMsg
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
