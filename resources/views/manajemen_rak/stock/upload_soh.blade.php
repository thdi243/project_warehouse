@extends('layouts.app')

@section('styles')
    <style>
        .upload-container {
            max-width: 700px;
            margin: 2rem auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-header h4 {
            color: #2d3748;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #718096;
            font-size: 0.9rem;
        }

        .upload-area {
            border: 3px dashed #cbd5e0;
            border-radius: 1rem;
            padding: 2rem 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .upload-area:hover {
            border-color: #007bff;
            background: #f8f9ff;
            transform: translateY(-2px);
        }

        .upload-area.dragover {
            border-color: #007bff;
            background: #e3f2fd;
            border-style: solid;
        }

        .upload-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .upload-area:hover .upload-icon {
            transform: scale(1.1);
            color: #0056b3;
        }

        .upload-text h5 {
            color: #2d3748;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .upload-text p {
            color: #718096;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .upload-text .file-types {
            color: #a0aec0;
            font-size: 0.75rem;
            font-weight: 500;
            margin-top: 0.75rem;
        }

        .btn-upload {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 0.625rem 1.75rem;
            border-radius: 1.5rem;
            border: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 123, 255, 0.5);
        }

        .template-download {
            text-align: center;
            margin-top: 1.5rem;
            padding: 1.25rem;
            border-radius: 0.75rem;
        }

        .template-download p {
            color: #495057;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
        }

        .btn-download-template {
            color: #007bff;
            border: 2px solid #007bff;
            padding: 0.5rem 1.5rem;
            border-radius: 1.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-download-template:hover {
            background: #007bff;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .file-input {
            display: none;
        }

        /* Loading State */
        .upload-area.loading {
            pointer-events: none;
            opacity: 0.6;
        }

        .loading-spinner {
            display: none;
            margin: 0.75rem auto;
        }

        .upload-area.loading .loading-spinner {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .upload-container {
                max-width: 100%;
                margin: 1rem auto;
            }

            .page-header h4 {
                font-size: 1.3rem;
            }

            .upload-area {
                padding: 1.75rem 1.25rem;
            }

            .upload-icon {
                font-size: 2.5rem;
            }

            .upload-text h5 {
                font-size: 1rem;
            }

            .upload-text p {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .page-header h4 {
                font-size: 1.2rem;
            }

            .upload-area {
                padding: 1.5rem 1rem;
            }

            .upload-icon {
                font-size: 2.25rem;
                margin-bottom: 0.75rem;
            }

            .btn-upload {
                padding: 0.55rem 1.5rem;
                font-size: 0.8rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="upload-container">
                <!-- Page Header -->
                <div class="page-header" data-aos="fade-down">
                    <h4>Stock On Hand Hari Ini Belum Update</h4>
                    <p>Upload file Excel atau CSV untuk memperbarui data stok</p>
                </div>

                <!-- Upload Area -->
                <div class="upload-area bg-light shadow-sm" id="uploadArea" data-aos="fade-up">
                    <div class="upload-icon">
                        <i class="mdi mdi-cloud-upload"></i>
                    </div>
                    <div class="upload-text">
                        <h5>Drag & Drop File Anda di Sini</h5>
                        <p>atau klik untuk memilih file dari komputer Anda</p>
                        <button type="button" class="btn btn-upload"
                            onclick="document.getElementById('fileInput').click()">
                            <i class="mdi mdi-file-upload me-2"></i>Pilih File
                        </button>
                        <p class="file-types">Format: .xlsx, .xls, .csv (Maks. 10MB)</p>
                    </div>
                    <input type="file" id="fileInput" class="file-input" accept=".xlsx,.xls,.csv">

                    <!-- Loading Spinner -->
                    <div class="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted small">Mengupload file...</p>
                    </div>
                </div>

                <!-- Template Download -->
                <div class="template-download shadow-sm bg-light" data-aos="fade-up" data-aos-delay="100">
                    <p><i class="mdi mdi-information-outline me-1"></i> Belum punya template? Download template Excel di
                        bawah ini</p>
                    <a href="{{ route('rack.stock.soh_download') }}" target="_blank" class="btn btn-download-template">
                        <i class="mdi mdi-download me-2"></i>Download Template
                    </a>
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
            const loadingSpinner = $('.loading-spinner');

            // Drag & Drop prevent defaults
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.on(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            // Highlight area on drag
            uploadArea.on('dragenter dragover', function() {
                uploadArea.addClass('dragover');
            });

            uploadArea.on('dragleave drop', function() {
                uploadArea.removeClass('dragover');
            });

            // Handle drop
            uploadArea.on('drop', function(e) {
                const files = e.originalEvent.dataTransfer.files;
                handleFiles(files);
            });

            // Handle input file select
            fileInput.on('change', function() {
                handleFiles(this.files);
            });

            // Handle file upload
            function handleFiles(files) {
                if (!files.length) return;

                const file = files[0];
                const allowedExtensions = ['xlsx', 'xls', 'csv'];
                const fileExt = file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExt)) {
                    toastr.error('Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv');
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    toastr.error('Ukuran file maksimal 10MB');
                    return;
                }

                uploadFile(file);
            }

            // Upload ke backend
            function uploadFile(file) {
                const formData = new FormData();
                formData.append('file', file);

                loadingSpinner.show();
                uploadArea.addClass('loading');

                $.ajax({
                    url: "{{ route('rack.stock.soh_upload') }}", // Ganti dengan route upload-mu
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.status === 'success') {

                            // Jika ada barang tidak ditemukan → tampilkan Swal
                            if (res.not_found && res.not_found.length > 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Sebagian Data Tidak Ditemukan',
                                    html: `
                                        <p><strong>${res.saved}</strong> dari <strong>${res.total}</strong> baris berhasil disimpan.</p>
                                        <hr>
                                        <p class="text-start"><strong>Barang tidak ditemukan di Master Barang (${res.not_found.length}):</strong></p>
                                        <div style="max-height: 200px; overflow-y: auto; text-align: left;">
                                            <ul style="padding-left: 20px; margin-bottom: 0;">
                                                ${res.not_found.map(id => `<li>${id}</li>`).join('')}
                                            </ul>
                                        </div>
                                    `,
                                    confirmButtonText: 'OK',
                                    confirmButtonColor: '#f0ad4e'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                toastr.success(res.message);
                                setTimeout(() => location.reload(), 1500);
                            }
                        } else {
                            toastr.error(res.message || 'Upload gagal.');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat upload file';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    },
                    complete: function() {
                        loadingSpinner.hide();
                        uploadArea.removeClass('loading');
                        fileInput.val('');
                    }
                });
            }

        });
    </script>
@endsection
