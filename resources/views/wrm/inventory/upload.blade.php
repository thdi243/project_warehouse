@extends('layouts.app')

@section('title', ' | Inventory Stock Upload')

@section('styles')
<style>
    .upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .upload-area:hover {
        border-color: #0d6efd;
        background-color: #f8f9fa;
    }

    .hover-lift {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .modal-header.bg-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    }

    .modal-header.bg-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0bacbe 100%);
    }

    .modal-header.bg-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }
</style>
@endsection

@section('content')
<div class="page-content d-flex align-items-center" style="min-height:100vh;">
    <div class="container-fluid">

        <div class="text-center mb-5" data-aos="fade-down">
            <h3 class="fw-bold">Inventory Data Upload</h3>
            <p class="text-muted">Pilih jenis data yang akan diunggah ke sistem</p>
        </div>

        <div class="row justify-content-center g-4">
            <!-- Inbound Gula Card -->
            @can('permission', 'wrm-inventory-upload-inbound')
            <div class="col-12 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card shadow-sm border-0 h-100 overflow-hidden hover-lift">
                    <div class="card-body text-center p-4 p-xl-5 d-flex flex-column">
                        <div class="mb-4">
                            <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                                style="width: 90px; height: 90px;">
                                <i class="mdi mdi-database-import display-4"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-3">Inbound Gula</h4>
                        <p class="text-muted mb-4 flex-grow-1">Proses penambahan stok gula baru ke gudang menggunakan template file Excel.</p>

                        <button type="button" class="btn btn-primary btn-lg w-100 py-3 shadow-sm mt-auto" data-bs-toggle="modal"
                            data-bs-target="#modalInbound">
                            <i class="mdi mdi-tray-arrow-down me-2"></i>
                            Upload Inbound
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inbound Non Gula Card -->
            <div class="col-12 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card shadow-sm border-0 h-100 overflow-hidden hover-lift">
                    <div class="card-body text-center p-4 p-xl-5 d-flex flex-column">
                        <div class="mb-4">
                            <div class="bg-info-subtle text-info rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                                style="width: 90px; height: 90px;">
                                <i class="mdi mdi-package-variant-closed display-4"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-3">Inbound Non Gula</h4>
                        <p class="text-muted mb-4 flex-grow-1">Input stok non-gula (Garam, SKM, dll) secara manual per Nomor SPB.</p>

                        <button type="button" class="btn btn-info btn-lg w-100 py-3 text-white shadow-sm mt-auto" data-bs-toggle="modal"
                            data-bs-target="#modalNonGula">
                            <i class="mdi mdi-plus-circle-outline me-2"></i>
                            Input Manual
                        </button>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Outbound Card -->
            @can('permission', 'wrm-inventory-upload-outbound')
            <div class="col-12 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card shadow-sm border-0 h-100 overflow-hidden hover-lift">
                    <div class="card-body text-center p-4 p-xl-5 d-flex flex-column">
                        <div class="mb-4">
                            <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm"
                                style="width: 90px; height: 90px;">
                                <i class="mdi mdi-database-export display-4"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-3">Stock Transfer</h4>
                        <p class="text-muted mb-4 flex-grow-1">Pencatatan riwayat transfer stok (shrinkage) secara massal via Excel.</p>

                        <button type="button" class="btn btn-warning btn-lg w-100 py-3 shadow-sm mt-auto" data-bs-toggle="modal"
                            data-bs-target="#modalOutbound">
                            <i class="mdi mdi-tray-arrow-up me-2"></i>
                            Upload Outbound
                        </button>
                    </div>
                </div>
            </div>
            @endcan
        </div>

    </div>
</div>

<!-- Modal Inbound -->
<div class="modal fade" id="modalInbound" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="mdi mdi-database-import-outline me-2"></i>
                    Upload Inbound Gula
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-primary border-0 shadow-none mb-4">
                    <i class="mdi mdi-information-outline me-2"></i>
                    Gunakan file Excel sesuai template untuk mengunggah stok gula masuk.
                </div>

                <form id="uploadForm" action="{{ route('wrm.inventory.upload') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih File Template</label>
                        <input type="file" id="fileInput" name="file" class="form-control" accept=".xls,.xlsx"
                            required>
                        <div class="form-text mt-2">Format didukung: .xls, .xlsx</div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ asset('assets/templates/excel/template_inventory_upload_inbound.xlsx') }}"
                            class="btn btn-success flex-grow-1">
                            <i class="mdi mdi-download me-1"></i>
                            Download Template
                        </a>
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="mdi mdi-upload me-1"></i>
                            Upload & Lanjut
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Non Gula -->
<div class="modal fade" id="modalNonGula" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="mdi mdi-plus-circle-outline me-2"></i>
                    Inbound Non Gula
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs-custom nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active py-3 fw-bold" data-bs-toggle="tab" href="#manualInput" role="tab">
                            <i class="mdi mdi-form-select me-1"></i> Input Manual
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3 fw-bold" data-bs-toggle="tab" href="#excelUpload" role="tab">
                            <i class="mdi mdi-file-excel me-1"></i> Upload Excel
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content p-4">
                    <div class="tab-pane active" id="manualInput" role="tabpanel">
                        <form id="nonGulaForm" action="{{ route('wrm.inventory.non-gula-upload') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Material / Barang <span class="text-danger">*</span></label>
                                <select name="mid_id" id="selectMidNonGula" class="form-control select2" required></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nomor SPB <span class="text-danger">*</span></label>
                                <input type="text" name="no_spb" class="form-control" placeholder="Contoh: 9000008999901" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Total Qty (KG) <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="total_qty" class="form-control" placeholder="Masukkan total qty dalam KG" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Expired Date <span class="text-muted small font-size-10">(Opsional)</span></label>
                                <input type="date" step="any" name="expired_date" class="form-control">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-info text-white py-2">
                                    <i class="mdi mdi-check-circle-outline me-1"></i>
                                    Proses & Lanjut
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="excelUpload" role="tabpanel">
                        <div class="alert alert-info border-0 shadow-none mb-4 small">
                            <i class="mdi mdi-information-outline me-2"></i>
                            Gunakan ini untuk migrasi data massal. Pastikan format kolom sesuai template.
                        </div>

                        <form id="nonGulaExcelForm" action="{{ route('wrm.inventory.non-gula-upload-excel') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">Pilih File Excel</label>
                                <input type="file" name="file" class="form-control" accept=".xls,.xlsx" required>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ asset('assets/templates/excel/template_inventory_upload_inbound_non_gula.xlsx') }}"
                                    class="btn btn-success flex-grow-1">
                                    <i class="mdi mdi-download me-1"></i>
                                    Template
                                </a>
                                <button type="submit" class="btn btn-info text-white flex-grow-1">
                                    <i class="mdi mdi-upload me-1"></i>
                                    Upload & Lanjut
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Outbound -->
<div class="modal fade" id="modalOutbound" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="mdi mdi-database-export-outline me-2"></i>
                    Upload Stock Transfer (WRM)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 shadow-none mb-4">
                    <i class="mdi mdi-information-outline me-2"></i>
                    Ambil data stock transfer ini dari <b>WEBSAP</b> pada menu <b>BA Report Susut Reservasi</b>.
                </div>

                <form id="outboundUploadForm" action="{{ route('wrm.inventory.outbound-upload') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih File Template</label>
                        <input type="file" name="file" class="form-control" accept=".xls,.xlsx" required>
                        <div class="form-text mt-2">Format didukung: .xls, .xlsx</div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ asset('assets/templates/excel/template_inventory_upload_susut_simpan.xlsx') }}"
                            class="btn btn-success flex-grow-1">
                            <i class="mdi mdi-download me-1"></i>
                            Download Template
                        </a>
                        <button type="submit" class="btn btn-warning flex-grow-1">
                            <i class="mdi mdi-upload me-1"></i>
                            Upload & Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // SELECT2 MID NON GULA
        $('#selectMidNonGula').select2({
            dropdownParent: $('#modalNonGula'),
            placeholder: 'Cari Material ID atau Nama Barang...',
            allowClear: true,
            ajax: {
                url: "{{ route('wrm.inventory.getBarang') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.data
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        });

        // SUBMIT INBOUND AJAX
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            submitAjax($(this), "{{ route('wrm.inventory.select-location') }}");
        });

        // SUBMIT NON GULA AJAX
        $('#nonGulaForm').on('submit', function(e) {
            e.preventDefault();
            submitAjax($(this), "{{ route('wrm.inventory.select-location') }}");
        });

        // SUBMIT NON GULA EXCEL AJAX
        $('#nonGulaExcelForm').on('submit', function(e) {
            e.preventDefault();
            submitAjax($(this), "{{ route('wrm.inventory.select-location') }}");
        });

        // SUBMIT OUTBOUND AJAX
        $('#outboundUploadForm').on('submit', function(e) {
            e.preventDefault();
            submitAjax($(this), "{{ route('wrm.inventory.index-transfer') }}");
        });

        // Reusable Submit Function
        function submitAjax(form, redirectUrl) {
            let formData = new FormData(form[0]);

            // For nonGulaForm, we don't have file input, but submitAjax checks for it.
            // Let's modify submitAjax to handle both.
            let isFileForm = form.find('input[type="file"]').length > 0;

            if (isFileForm) {
                let fileInput = form.find('input[type="file"]');
                if (!fileInput[0].files || fileInput[0].files.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File belum dipilih',
                        text: 'Silakan pilih file Excel terlebih dahulu'
                    });
                    return;
                }
            }

            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang memproses data',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message || ('Total data: ' + res.total),
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = redirectUrl;
                    });
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors ?? [xhr.responseJSON?.message ??
                        'Terjadi kesalahan'
                    ];

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: Array.isArray(errors) ? errors.join('<br>') : errors
                    });
                }
            });
        }
    })
</script>
@endsection