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
</style>
@endsection

@section('content')
<div class="page-content d-flex align-items-center" style="min-height:100vh;">
    <div class="container-fluid">

        <div class="text-center mb-5" data-aos="fade-down">
            <h3 class="fw-bold">Inventory Data Upload</h3>
            <p class="text-muted">Pilih jenis data yang akan diunggah ke sistem</p>
        </div>

        <div class="row justify-content-center gap-2 py-3 px-2">
            <!-- Inbound Card -->
            @can('permission', 'wrm-inventory-upload-inbound')
            <div class="col-11 col-sm-8 col-md-5 col-lg-4">
                <div class="card shadow-sm border-0 h-100" data-aos="fade-right">
                    <div class="card-body text-center p-4 p-lg-5 d-flex flex-column">
                        <div class="mb-4">
                            <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="mdi mdi-database-import display-4"></i>
                            </div>
                        </div>
                        <h4 class="mb-2">Upload Data Inbound </h4>
                        <p class="text-muted mb-4 small">Gunakan ini untuk menambah stok baru ke gudang melalui proses mapping
                            lokasi.</p>

                        <div class="d-grid gap-2 mt-auto">
                            <button type="button" class="btn btn-primary py-3" data-bs-toggle="modal"
                                data-bs-target="#modalInbound">
                                <i class="mdi mdi-tray-arrow-down me-1"></i>
                                Upload Inbound
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            <!-- Outbound Card -->
            @can('permission', 'wrm-inventory-upload-outbound')
            <div class="col-11 col-sm-8 col-md-5 col-lg-4">
                <div class="card shadow-sm border-0 h-100" data-aos="fade-left">
                    <div class="card-body text-center p-4 p-lg-5 d-flex flex-column">
                        <div class="mb-4">
                            <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="mdi mdi-database-export display-4"></i>
                            </div>
                        </div>
                        <h4 class="mb-2">Upload Data Stock Transfer <br><small class="text-muted fs-6">(Susut/GI)</small></h4>
                        <p class="text-muted mb-4 small">Gunakan ini untuk mencatat riwayat transfer stok (shrinkage) secara massal menggunakan template baru.</p>

                        <div class="d-grid gap-2 mt-auto">
                            <button type="button" class="btn btn-warning py-3" data-bs-toggle="modal"
                                data-bs-target="#modalOutbound">
                                <i class="mdi mdi-tray-arrow-up me-1"></i>
                                Upload Outbound
                            </button>
                        </div>
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
            <div class="modal-header text-white border-0">
                <h5 class="modal-title">
                    <i class="mdi mdi-database-import-outline me-2"></i>
                    Upload Inbound (New Stock)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-primary border-0 shadow-none mb-4">
                    <i class="mdi mdi-information-outline me-2"></i>
                    Gunakan file Excel sesuai template untuk mengunggah stok masuk.
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

<!-- Modal Outbound -->
<div class="modal fade" id="modalOutbound" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white border-0">
                <h5 class="modal-title">
                    <i class="mdi mdi-database-export-outline me-2"></i>
                    Upload Stock Transfer (WRM)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 shadow-none mb-4">
                    <i class="mdi mdi-information-outline me-2"></i>
                    Ambil data stock transfer ini dari <b>WEBSAP</b> pada menu report susut reservasi.
                    <br><i class="mdi mdi-information-outline me-2"></i>
                    Pastikan data Material ID dan No Barcode sesuai dengan template baru (17 kolom).
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
        // SUBMIT INBOUND AJAX
        $('#uploadForm').on('submit', function(e) {
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

            let fileInput = form.find('input[type="file"]');
            if (!fileInput[0].files || fileInput[0].files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File belum dipilih',
                    text: 'Silakan pilih file Excel terlebih dahulu'
                });
                return;
            }

            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang memproses file Excel',
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
                        title: 'Upload Berhasil',
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
                        title: 'Upload Gagal',
                        html: Array.isArray(errors) ? errors.join('<br>') : errors
                    });
                }
            });
        }
    })
</script>
@endsection