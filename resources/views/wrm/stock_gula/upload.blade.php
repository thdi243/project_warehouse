@extends('layouts.app')

@section('title', ' | Upload Stock Gula')

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

            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-7">
                    <div class="card shadow-sm border-0" data-aos="fade-up">
                        <div class="card-body text-center p-5">

                            <h4 class="mb-4">
                                <i class="mdi mdi-database-upload-outline me-2"></i>
                                Upload Stock Gula
                            </h4>

                            <form id="uploadForm" action="{{ route('wrm.stock_gula.upload') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <label id="uploadArea" class="upload-area w-100 p-5 mb-4">
                                    <i class="mdi mdi-microsoft-excel display-4 text-success"></i>

                                    <h5 class="mt-3">Klik untuk memilih file</h5>
                                    <p class="text-muted mb-2">
                                        atau drag & drop file Excel di sini
                                    </p>

                                    <small id="fileName" class="text-muted">
                                        Format yang didukung: .xls, .xlsx
                                    </small>

                                    <input id="fileInput" type="file" name="file" accept=".xls,.xlsx" hidden>
                                </label>

                                <div class="d-flex justify-content-center gap-3">
                                    <a href="{{ asset('assets/templates/excel/template_stock_gula_wrm.xlsx') }}"
                                        class="btn btn-success">
                                        <i class="mdi mdi-download me-1"></i>
                                        Download Template
                                    </a>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-upload me-1"></i>
                                        Upload File
                                    </button>
                                </div>

                            </form>

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
            const uploadArea = $('#uploadArea');
            const fileInput = $('#fileInput');
            const fileName = $('#fileName');

            // klik area upload
            uploadArea.on('click', function() {
                fileInput.click();
            });

            // tampilkan nama file saat dipilih
            fileInput.on('change', function() {
                if (this.files.length > 0) {
                    fileName.text('File dipilih: ' + this.files[0].name);
                }
            });

            // drag over
            uploadArea.on('dragover', function(e) {
                e.preventDefault();
                uploadArea.addClass('bg-light border-primary');
            });

            // drag leave
            uploadArea.on('dragleave', function() {
                uploadArea.removeClass('bg-light border-primary');
            });

            // drop file
            uploadArea.on('drop', function(e) {
                e.preventDefault();
                uploadArea.removeClass('bg-light border-primary');

                const files = e.originalEvent.dataTransfer.files;

                if (files.length > 0) {
                    fileInput[0].files = files;
                    fileName.text('File dipilih: ' + files[0].name);
                }
            });

            // SUBMIT FORM AJAX
            $('#uploadForm').on('submit', function(e) {

                e.preventDefault();

                let fileInput = document.getElementById('fileInput');

                // VALIDASI FILE KOSONG
                if (!fileInput.files || fileInput.files.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File belum dipilih',
                        text: 'Silakan pilih file Excel terlebih dahulu'
                    });
                    return;
                }

                let formData = new FormData(this);

                Swal.fire({
                    title: 'Uploading...',
                    text: 'Sedang memproses file Excel',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Upload Berhasil',
                            text: 'Total data: ' + res.total,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href =
                                "{{ route('wrm.stock_gula.select-location') }}";
                        });

                        $('#uploadForm')[0].reset();
                    },
                    error: function(xhr) {

                        let errors = xhr.responseJSON?.errors ?? ['Terjadi kesalahan'];

                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Gagal',
                            html: errors.join('<br>')
                        });
                    }
                });

            });
        })
    </script>
@endsection
