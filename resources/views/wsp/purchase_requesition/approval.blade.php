@extends('layouts.app')

@section('title', '| PR Approval')

@section('styles')
    <style>
        .signature-canvas {
            width: 100%;
            height: 200px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #f8f9fa;
        }

        .badge-soft-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-soft-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-soft-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-soft-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-3" data-aos="fade-down">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="fw-bold fs-3">Purchase Requisition Approval</h3>
                        <p class="fw-normal fs-6 text-muted">Halaman persetujuan PR secara masal</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button class="btn btn-outline-primary" id="btnRefresh">
                            <i class="mdi mdi-refresh me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="card mb-3 shadow-sm border-0" data-aos="fade-up">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <div class="flex-grow-1">
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchInput"
                                    placeholder="Cari User Peminta / No Doc...">
                            </div>
                        </div>
                        <div class="bulk-actions-wrapper d-flex align-items-center gap-2">
                            <button class="btn btn-outline-secondary" id="btnSelectAll">
                                <i class="mdi mdi-checkbox-multiple-marked-outline me-1"></i> Pilih Semua
                            </button>
                            <div class="bulk-actions d-none">
                                <span class="mx-2 text-muted small selected-count">0 terpilih</span>
                                <button class="btn btn-success" id="btnBulkApprove">
                                    <i class="mdi mdi-check-all me-1"></i> Approve Masal
                                </button>
                                <button class="btn btn-danger" id="btnBulkReject">
                                    <i class="mdi mdi-close-circle me-1"></i> Reject Masal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            @php
                $user = Auth::user();
                $isDeptHead = $user->jabatan === 'dept_head';
                $isWarehouseHead = $isDeptHead && $user->departemen === 'warehouse';
                $isForeman = $user->jabatan === 'foreman' && $user->bagian === 'warehouse_sparepart';
                $firstTab = true;
            @endphp
            @if ($isDeptHead || $isWarehouseHead || $isForeman)
                <ul class="nav nav-pills nav-justified mb-3 shadow-sm rounded bg-white p-1" id="approvalTabs" role="tablist"
                    data-aos="fade-up">

                    @if ($isDeptHead)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $firstTab ? 'active' : '' }} py-2" id="tab-level-2" data-level="2"
                                data-bs-toggle="pill" type="button" role="tab">
                                <i class="mdi mdi-account-tie me-1"></i> Manager User (Level 2)
                                <span class="badge bg-danger ms-2 count-level-2">0</span>
                            </button>
                        </li>
                        @php $firstTab = false; @endphp
                    @endif

                    @if ($isWarehouseHead)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $firstTab ? 'active' : '' }} py-2" id="tab-level-3" data-level="3"
                                data-bs-toggle="pill" type="button" role="tab">
                                <i class="mdi mdi-warehouse me-1"></i> Manager Warehouse (Level 3)
                                <span class="badge bg-danger ms-2 count-level-3">0</span>
                            </button>
                        </li>
                        @php $firstTab = false; @endphp
                    @endif

                    @if ($isForeman)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $firstTab ? 'active' : '' }} py-2" id="tab-level-4" data-level="4"
                                data-bs-toggle="pill" type="button" role="tab">
                                <i class="mdi mdi-account-hard-hat me-1"></i> Foreman WSP (Level 4)
                                <span class="badge bg-danger ms-2 count-level-4">0</span>
                            </button>
                        </li>
                        @php $firstTab = false; @endphp
                    @endif
                </ul>
            @endif

            <!-- Table Card -->
            <div class="card shadow-sm border-0" data-aos="fade-up">
                <div class="card-body">
                    <div class="alert alert-info py-2 px-3 w-100" role="alert">
                        <small>
                            <i class="ri-information-line me-1"></i>
                            Klik <b> detail</b> untuk cheklist item yang ingin di-approve/reject. Jika tidak ada item yang
                            dipilih,
                            maka semua item akan diproses sesuai pilihan approve/reject.
                        </small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="approvalTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;" class="ps-3">NO</th>
                                    <th>PR Date</th>
                                    <th>No Doc</th>
                                    <th>Requested By</th>
                                    <th>Department</th>
                                    <th>Role Approval</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 mb-0 text-muted">Memuat data persetujuan...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Detail Purchase Requisition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detailContent">
                        <!-- Dynamic Content -->
                    </div>

                    <div class="alert alert-info d-none mt-3" id="level4AlertInfo">
                        <i class="mdi mdi-information-outline me-2"></i>
                        Jika item di-checklist, maka data terkonfirmasi naik PR. Jika tidak di-checklist, maka item tersebut dicancel/tidak naik PR.
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold mb-3"><i class="mdi mdi-format-list-bulleted me-2"></i>Daftar Item</h6>
                        <div class="table-responsive" style="min-height: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th class="item-check-col" style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAllItems">
                                            </div>
                                        </th>
                                        <th>MID</th>
                                        <th>Nama Barang</th>
                                        <th>Qty</th>
                                        <th>UoM</th>
                                        <th>Keterangan</th>
                                        <th>Status</th>
                                        <th class="level4-only-col">Jenis</th>
                                        <th class="level4-only-col">Alasan</th>
                                    </tr>
                                </thead>
                                <tbody id="detailItemsBody">
                                    <!-- Dynamic Items -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <div id="detailActionButtons">
                        <button type="button" class="btn btn-danger btn-reject-single">Reject</button>
                        <button type="button" class="btn btn-success btn-approve-single">Approve</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Action (Signature & Comment) -->
    <div class="modal fade" id="modalAction" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="actionModalTitle">Persetujuan PR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAction">
                        <div class="mb-3 d-none" id="noPrWrapper">
                            <label class="form-label fw-bold">No PR <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="actionNoPr" placeholder="Masukkan No PR...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan / Alasan</label>
                            <textarea class="form-control" id="actionComment" rows="3" placeholder="Opsional..."></textarea>
                        </div>
                        <div class="mb-0" id="signatureWrapper">
                            <label class="form-label fw-bold d-block mb-2">Tanda Tangan Digital</label>
                            @if ($signature)
                                @php
                                    $sigPath = $signature->signature;
                                    if (
                                        !Str::startsWith($sigPath, 'storage/') &&
                                        !Str::startsWith($sigPath, 'http') &&
                                        !Str::startsWith($sigPath, '/storage/')
                                    ) {
                                        $sigPath = 'storage/' . $sigPath;
                                    }
                                @endphp
                                <div class="d-flex gap-3 mb-3 pb-2 border-bottom">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="signature_option"
                                            id="sigOptionStored" value="stored" checked>
                                        <label class="form-check-label fw-semibold" for="sigOptionStored"
                                            style="cursor: pointer;">
                                            Gunakan Tanda Tangan Terdaftar
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="signature_option"
                                            id="sigOptionNew" value="new">
                                        <label class="form-check-label fw-semibold" for="sigOptionNew"
                                            style="cursor: pointer;">
                                            Buat Tanda Tangan Baru
                                        </label>
                                    </div>
                                </div>

                                <div id="storedSignatureContainer" class="border rounded p-3 text-center bg-light mb-2">
                                    <p class="small text-muted mb-2">Tanda tangan terdaftar yang akan digunakan:</p>
                                    <img src="{{ asset($sigPath) }}" alt="Signature"
                                        style="max-height: 150px; width: auto;">
                                </div>

                                <div id="newSignatureContainer" class="d-none">
                                    <canvas id="signaturePad" class="signature-canvas"></canvas>
                                    <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="updateSignatureCheckbox"
                                                value="1" checked>
                                            <label class="form-check-label small text-muted" for="updateSignatureCheckbox"
                                                style="cursor: pointer;">
                                                Update tanda tangan terdaftar saya
                                            </label>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="clearSignature">
                                            <i class="mdi mdi-eraser me-1"></i> Bersihkan
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="useStoredSignature" value="1">
                            @else
                                <canvas id="signaturePad" class="signature-canvas"></canvas>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="updateSignatureCheckbox"
                                            value="1" checked>
                                        <label class="form-check-label small text-muted" for="updateSignatureCheckbox"
                                            style="cursor: pointer;">
                                            Simpan sebagai tanda tangan default
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="clearSignature">
                                        <i class="mdi mdi-eraser me-1"></i> Bersihkan
                                    </button>
                                </div>
                                <input type="hidden" id="useStoredSignature" value="0">
                            @endif
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitAction">Proses</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            let allPending = [];
            let selectedIds = [];
            let currentAction = ''; // 'approved' or 'rejected'
            let signaturePad;
            const hasStoredSignature = {{ $signature ? 'true' : 'false' }};

            // Initialize Signature Pad
            const canvas = document.getElementById('signaturePad');
            if (canvas) {
                signaturePad = new SignaturePad(canvas);

                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                    signaturePad.clear();
                }

                window.onresize = resizeCanvas;

                $('#modalAction').on('shown.bs.modal', function() {
                    if (currentAction === 'approved') {
                        if (!hasStoredSignature || $('input[name="signature_option"]:checked').val() ===
                            'new') {
                            resizeCanvas();
                        }
                    }
                });
            }

            let currentFilterLevel = $('#approvalTabs button.active').data('level') || 2;

            $('#approvalTabs button').on('shown.bs.tab', function(e) {
                currentFilterLevel = $(e.target).data('level');
                const val = $('#searchInput').val().toLowerCase();
                filterAndRender(val);
            });

            loadData();

            function loadData() {
                $.ajax({
                    url: "{{ url('api/purchase-requesition/pending-approvals') }}",
                    type: "GET",
                    success: function(res) {
                        if (res.success) {
                            allPending = res.data;
                            updateTabCounts(allPending);
                            filterAndRender($('#searchInput').val().toLowerCase());
                        }
                    },
                    error: function() {
                        $('#tableBody').html(
                            '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data.</td></tr>'
                        );
                    }
                });
            }

            function updateTabCounts(data) {
                const userId = {{ Auth::id() }};

                const count2 = data.filter(pr => {
                    const myApp = pr.approval.find(a => a.approver_id == userId && a.status === 'pending');
                    return myApp && myApp.level == 2;
                }).length;

                const count3 = data.filter(pr => {
                    const myApp = pr.approval.find(a => a.approver_id == userId && a.status === 'pending');
                    return myApp && myApp.level == 3;
                }).length;

                const count4 = data.filter(pr => {
                    const myApp = pr.approval.find(a => a.approver_id == userId && a.status === 'pending');
                    return myApp && myApp.level == 4;
                }).length;

                $('.count-level-2').text(count2);
                $('.count-level-3').text(count3);
                $('.count-level-4').text(count4);
            }

            function filterAndRender(searchTerm) {
                const filtered = allPending.filter(pr =>
                    (pr.no_doc.toLowerCase().includes(searchTerm) ||
                        pr.requested_by.toLowerCase().includes(searchTerm))
                );
                renderTable(filtered);
            }

            function renderTable(data) {
                const tbody = $('#tableBody');
                tbody.empty();
                selectedIds = [];
                updateBulkUI();

                const userId = {{ Auth::id() }};
                const filteredByLevel = data.filter(pr => {
                    const myApp = pr.approval.find(a => a.approver_id == userId && a.status === 'pending');
                    return myApp && myApp.level == currentFilterLevel;
                });

                if (filteredByLevel.length === 0) {
                    tbody.html(
                        `<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada PR yang menunggu persetujuan Anda di Level ${currentFilterLevel}.</td></tr>`
                    );
                    return;
                }

                filteredByLevel.forEach((pr, idx) => {
                    const myApproval = pr.approval.find(a => a.approver_id == userId && a.status ===
                        'pending');
                    const roleName = myApproval ? myApproval.role : '-';
                    const isLevel4 = currentFilterLevel == 4;
                    const approveText = isLevel4 ? 'Confirm' : 'Approve';

                    let checkboxOrIndex = `
                        <div class="form-check">
                            <input class="form-check-input check-item" type="checkbox" value="${pr.id}">
                        </div>
                    `;
                    if (isLevel4) {
                        checkboxOrIndex = idx + 1;
                    }

                    tbody.append(`
                        <tr>
                            <td class="ps-3">${checkboxOrIndex}</td>
                            <td>${pr.pr_date}</td>
                            <td><span class="fw-bold text-primary">${pr.no_doc}</span></td>
                            <td>${pr.requested_by}</td>
                            <td><span class="badge badge-soft-info">${pr.department.toUpperCase()}</span></td>
                            <td><span class="badge badge-soft-warning">${roleName}</span></td>
                            <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-success btn-action-row" data-id="${pr.id}" data-action="approved">
                                    <i class="mdi mdi-check"></i> ${approveText}
                                </button>
                                ${!isLevel4 ? `
                                <button class="btn btn-sm btn-danger btn-action-row" data-id="${pr.id}" data-action="rejected">
                                    <i class="mdi mdi-close"></i> Reject
                                </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `);
                });
            }

            // Checkbox Logic
            $('#btnSelectAll').on('click', function() {
                const anyUnchecked = $('.check-item:not(:checked)').length > 0;
                if (anyUnchecked) {
                    $('.check-item').prop('checked', true);
                    $(this).html(
                        '<i class="mdi mdi-checkbox-multiple-blank-outline me-1"></i> Batal Pilih');
                    $(this).removeClass('btn-outline-secondary').addClass('btn-outline-danger');
                } else {
                    $('.check-item').prop('checked', false);
                    $(this).html(
                        '<i class="mdi mdi-checkbox-multiple-marked-outline me-1"></i> Pilih Semua');
                    $(this).removeClass('btn-outline-danger').addClass('btn-outline-secondary');
                }
                updateSelectedIds();
            });

            $(document).on('change', '.check-item', function() {
                updateSelectedIds();
            });

            function updateSelectedIds() {
                selectedIds = [];
                $('.check-item:checked').each(function() {
                    selectedIds.push($(this).val());
                });
                updateBulkUI();
            }

            function updateBulkUI() {
                if (currentFilterLevel == 4) {
                    $('.bulk-actions-wrapper').addClass('d-none');
                    return;
                } else {
                    $('.bulk-actions-wrapper').removeClass('d-none');
                }

                if (selectedIds.length > 0) {
                    $('.bulk-actions').removeClass('d-none');
                    $('.selected-count').text(`${selectedIds.length} terpilih`);
                } else {
                    $('.bulk-actions').addClass('d-none');
                    $('#btnSelectAll').html(
                        '<i class="mdi mdi-checkbox-multiple-marked-outline me-1"></i> Pilih Semua');
                    $('#btnSelectAll').removeClass('btn-outline-danger').addClass('btn-outline-secondary');
                }
            }

            // Search Logic
            $('#searchInput').on('input', function() {
                const val = $(this).val().toLowerCase();
                filterAndRender(val);
            });

            // Action per row
            $(document).on('click', '.btn-action-row', function() {
                const id = $(this).data('id');
                const action = $(this).data('action');
                const pr = allPending.find(p => p.id == id);
                if (!pr) return;

                currentAction = action;

                $('#detailContent').html(`
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1 text-muted small">NO DOC</p>
                            <h6 class="fw-bold">${pr.no_doc}</h6>
                            <p class="mb-1 text-muted small mt-3">REQUESTED BY</p>
                            <h6 class="fw-bold">${pr.requested_by}</h6>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 text-muted small">DATE</p>
                            <h6 class="fw-bold">${pr.pr_date}</h6>
                            <p class="mb-1 text-muted small mt-3">DEPARTMENT</p>
                            <h6 class="fw-bold">${pr.department.toUpperCase()}</h6>
                        </div>
                        <div class="col-12 mt-3">
                            <p class="mb-1 text-muted small">HAL</p>
                            <h6 class="fw-bold">${pr.hal || '-'}</h6>
                        </div>
                    </div>
                `);

                const userId = {{ Auth::id() }};
                const myApproval = pr.approval.find(a => a.approver_id == userId && a.status === 'pending');
                const isLevel2Or4 = myApproval && (
                    myApproval.level == 2 ||
                    myApproval.level == 4 ||
                    myApproval.role === 'Manager User' ||
                    myApproval.role === 'Foreman Wsp'
                );
                const isLevel4 = myApproval && (myApproval.level == 4 || myApproval.role === 'Foreman Wsp');

                if (isLevel2Or4) {
                    $('.item-check-col').show();
                } else {
                    $('.item-check-col').hide();
                }

                const itemsBody = $('#detailItemsBody');
                itemsBody.empty();
                pr.items.forEach(item => {
                    let checkHtml = '';
                    if (isLevel2Or4) {
                        const isBlocked = isLevel4 && item.jenis === 'blocked';
                        const checkedAttr = isBlocked ? '' : 'checked';
                        checkHtml = `
                        <td class="item-check-col">
                            <div class="form-check">
                                <input class="form-check-input check-sub-item" type="checkbox" value="${item.id}" ${checkedAttr}>
                            </div>
                        </td>
                    `;
                    }
                    let statusHtml = '';
                    if (item.approval && item.approval.length > 0) {
                        item.approval.forEach(app => {
                            const badge = app.status === 'approved' ? 'success' : (app
                                .status === 'rejected' ? 'danger' : 'warning');
                            statusHtml +=
                                `<span class="badge badge-soft-${badge} d-block mb-1">${app.status}</span>`;
                        });
                    }

                    itemsBody.append(`
                    <tr>
                        ${checkHtml}
                        <td>${item.barang?.mid_barang || '-'}</td>
                        <td>${item.barang?.nama_barang || '-'}</td>
                        <td>${item.qty}</td>
                        <td>${item.barang?.uom || '-'}</td>
                        <td>${item.keterangan || '-'}</td>
                        <td>${statusHtml || '<span class="badge badge-soft-warning">pending</span>'}</td>
                        <td class="level4-only-col">${item.jenis || '-'}</td>
                        <td class="level4-only-col">${item.alasan || '-'}</td>
                    </tr>
                `);
                });

                if (isLevel4) {
                    $('.level4-only-col').show();
                    $('#level4AlertInfo').removeClass('d-none');
                } else {
                    $('.level4-only-col').hide();
                    $('#level4AlertInfo').addClass('d-none');
                }

                const allChecked = $('.check-sub-item:not(:checked)').length === 0;
                $('#checkAllItems').prop('checked', allChecked).off('change').on('change', function() {
                    $('.check-sub-item').prop('checked', $(this).is(':checked'));
                });

                $(document).off('change', '.check-sub-item').on('change', '.check-sub-item', function() {
                    const total = $('.check-sub-item').length;
                    const checked = $('.check-sub-item:checked').length;
                    $('#checkAllItems').prop('checked', total === checked);
                });

                const actionLabel = isLevel4 ? (action === 'approved' ? 'Confirm' : 'Reject') : (action === 'approved' ? 'Approve' : 'Reject');

                $('#detailActionButtons').html(`
                    <button type="button" class="btn btn-${action === 'approved' ? 'success' : 'danger'} btn-lanjut-action">Lanjut ${actionLabel}</button>
                `);

                $('.btn-lanjut-action').off('click').on('click', function() {
                    selectedIds = [pr.id];

                    if (isLevel2Or4) {
                        const selectedItems = [];
                        $('.check-sub-item:checked').each(function() {
                            selectedItems.push($(this).val());
                        });

                        if (!isLevel4 && action === 'approved' && selectedItems.length === 0) {
                            Swal.fire('Peringatan', 'Harap pilih minimal satu item untuk diproses.',
                                'warning');
                            return;
                        }
                        window.currentSelectedItems = selectedItems;
                    } else {
                        window.currentSelectedItems = [];
                    }

                    openActionModal(currentAction);
                    $('#modalDetail').modal('hide');
                });

                $('#modalDetail').modal('show');
            });

            // Bulk Action Logic
            $('#btnBulkApprove').on('click', function() {
                window.currentSelectedItems = [];
                openActionModal('approved');
            });

            $('#btnBulkReject').on('click', function() {
                window.currentSelectedItems = [];
                openActionModal('rejected');
            });

            // Signature Option Toggle
            $(document).on('change', 'input[name="signature_option"]', function() {
                if ($(this).val() === 'stored') {
                    $('#storedSignatureContainer').removeClass('d-none');
                    $('#newSignatureContainer').addClass('d-none');
                    $('#useStoredSignature').val('1');
                } else {
                    $('#storedSignatureContainer').addClass('d-none');
                    $('#newSignatureContainer').removeClass('d-none');
                    $('#useStoredSignature').val('0');

                    // Resize canvas when it becomes visible to avoid drawing issues
                    const canvas = document.getElementById('signaturePad');
                    if (canvas && signaturePad) {
                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext("2d").scale(ratio, ratio);
                        signaturePad.clear();
                    }
                }
            });

            function openActionModal(status) {
                currentAction = status;
                const isLevel4 = currentFilterLevel == 4;

                $('#actionModalTitle').text(isLevel4 ? (status === 'approved' ? 'Konfirmasi PR (Confirm)' : 'Konfirmasi Penolakan') : (status === 'approved' ? 'Konfirmasi Approval' : 'Konfirmasi Penolakan'));
                $('#btnSubmitAction').removeClass('btn-primary btn-success btn-danger')
                    .addClass(status === 'approved' ? 'btn-success' : 'btn-danger')
                    .text(isLevel4 ? (status === 'approved' ? 'Confirm Sekarang' : 'Reject Sekarang') : (status === 'approved' ? 'Approve Sekarang' : 'Reject Sekarang'));

                if (status === 'rejected') {
                    $('#signatureWrapper').addClass('d-none');
                } else {
                    $('#signatureWrapper').removeClass('d-none');
                }

                if (isLevel4 && status === 'approved') {
                    $('#noPrWrapper').removeClass('d-none');
                    $('#actionNoPr').val('');
                } else {
                    $('#noPrWrapper').addClass('d-none');
                }

                // Reset signature option to stored if available
                if (hasStoredSignature) {
                    $('#sigOptionStored').prop('checked', true).trigger('change');
                }

                signaturePad?.clear();
                $('#actionComment').val('');
                $('#modalAction').modal('show');
            }

            $('#clearSignature').on('click', function() {
                signaturePad?.clear();
            });

            $('#btnSubmitAction').on('click', function() {
                const useStored = $('#useStoredSignature').val() == '1';
                const isLevel4 = currentFilterLevel == 4;
                const noPr = $('#actionNoPr').val().trim();

                if (currentAction === 'approved' && !useStored && signaturePad.isEmpty()) {
                    Swal.fire('Error', 'Tanda tangan wajib diisi untuk approval.', 'error');
                    return;
                }

                if (isLevel4 && currentAction === 'approved' && !noPr) {
                    Swal.fire('Error', 'No PR wajib diisi.', 'error');
                    return;
                }

                const data = {
                    ids: selectedIds,
                    status: currentAction,
                    comment: $('#actionComment').val(),
                    items: window.currentSelectedItems || [],
                    no_pr: isLevel4 && currentAction === 'approved' ? noPr : null,
                    ttd: (currentAction === 'approved' && !useStored) ? signaturePad
                        .toDataURL() : null,
                    use_stored_signature: (currentAction === 'approved' && useStored) ? 1 : 0,
                    update_signature: $('#updateSignatureCheckbox').is(':checked') ? 1 : 0
                };

                Swal.fire({
                    title: 'Memproses...',
                    text: 'Harap tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ url('api/purchase-requesition/bulk-action') }}",
                    type: "POST",
                    data: data,
                    success: function(res) {
                        Swal.close();
                        if (res.success) {
                            Swal.fire('Berhasil!', res.message, 'success').then(() => {
                                loadData();
                                $('#modalAction').modal('hide');
                            });
                        } else {
                            Swal.fire('Terjadi Kesalahan', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        const msg = xhr.responseJSON?.message || 'Gagal memproses permintaan.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

            $('#btnRefresh').on('click', loadData);
        });
    </script>
@endsection
