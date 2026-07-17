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
                    <p>Upload file Excel atau CSV untuk memperbarui data stok atau langsung ke
                        <strong><a href="{{ route('stock.soh.index') }}" class="badge badge-soft-primary">Tabel</a></strong>
                    </p>
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
                    <a href="{{ route('stock.soh_download') }}" target="_blank" class="btn btn-download-template">
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

            function uploadFile(file) {
                // Validasi cepat client-side (biar user nggak nunggu server reject)
                const allowed = ['.xlsx', '.xls', '.csv'];
                const ext = file.name.toLowerCase().slice(file.name.lastIndexOf('.'));

                if (!allowed.includes(ext)) {
                    toastr.error('Hanya file .xlsx, .xls, atau .csv yang boleh.');
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    toastr.error('File maksimal 10 MB.');
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                loadingSpinner.show();
                uploadArea.addClass('loading');

                $.ajax({
                    url: "{{ route('stock.soh_upload') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 180000, // 180 detik (3 menit)
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        // Sukses 200 → cek apakah ada not_found (meski seharusnya tidak, tapi safety)
                        if (res.status === 'success') {
                            if (res.not_found && res.not_found.length > 0) {
                                showNotFoundWarning(res);
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Upload Berhasil',
                                    html: `<p>${res.message || 'Data stok berhasil diperbarui.'}</p>`,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: '#28a745',
                                    allowOutsideClick: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        } else {
                            let msg = res.message || 'Upload gagal.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload Gagal',
                                html: `<p>${msg}</p>`,
                                confirmButtonText: 'Oke',
                                confirmButtonColor: '#d33',
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function(xhr) {
                        let res = {};
                        try {
                            res = JSON.parse(xhr.responseText || '{}');
                        } catch (e) {}

                        if (xhr.status === 422 && res.not_found && res.not_found.length > 0) {
                            // Ini yang paling penting: tangani kasus MID not found dari 422
                            showNotFoundWarning(res);
                        } else {
                            // Error lain (template salah, exception, dll)
                            let msg = res.message || 'Terjadi kesalahan saat proses file';
                            if (xhr.status === 413) msg = 'File terlalu besar (max 10 MB)';
                            if (xhr.status === 0 || xhr.status === 504) msg =
                                'Koneksi lambat atau server timeout';
                            toastr.error(msg);
                        }
                    },
                    complete: function() {
                        loadingSpinner.hide();
                        uploadArea.removeClass('loading');
                        fileInput.val('');
                    }
                });
            }

            let currentMidsToCopy = '';
            window.copyMidsToClipboard = async function(link) {
                const textToCopy = currentMidsToCopy;
                if (!textToCopy) return;

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(textToCopy);
                    } else {
                        // Fallback for non-secure HTTP context
                        const textArea = document.createElement("textarea");
                        textArea.value = textToCopy;
                        textArea.style.position = "fixed";
                        textArea.style.left = "-999999px";
                        textArea.style.top = "-999999px";
                        // Append to the active SweetAlert popup to bypass focus trap
                        const container = Swal.getPopup() || document.body;
                        container.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        const success = document.execCommand('copy');
                        textArea.remove();
                        if (!success) {
                            throw new Error('execCommand copy failed');
                        }
                    }

                    Swal.update({
                        title: 'Daftar MID berhasil di-copy!',
                        icon: 'success'
                    });

                    const activeLink = document.getElementById('copy-mid-link');
                    if (activeLink) {
                        activeLink.textContent = '✔ MID sudah di-copy';
                        activeLink.style.color = '#28a745';
                    }

                    setTimeout(() => {
                        const activeLinkReset = document.getElementById('copy-mid-link');
                        if (activeLinkReset) {
                            activeLinkReset.textContent = '📋 Copy daftar MID';
                            activeLinkReset.style.color = '#6c757d';
                        }

                        Swal.update({
                            title: 'MID Barang Tidak Ditemukan',
                            icon: 'warning'
                        });
                    }, 3000);

                } catch (e) {
                    Swal.showValidationMessage(
                        'Gagal copy. Silakan Ctrl+C manual.'
                    );
                }
            };

            function showNotFoundWarning(res) {
                const notFoundList = res.not_found || [];
                const count = notFoundList.length;
                const totalChecked = res.total_checked || '–';
                currentMidsToCopy = notFoundList.join('\n');

                Swal.fire({
                    icon: 'warning',
                    title: 'MID Barang Tidak Ditemukan',
                    html: `
                        <p style="text-align:center">
                            <strong>${count}</strong> mid barang tidak ditemukan.<br>
                            <strong style="color:#d32f2f">Data TIDAK disimpan!</strong>
                        </p>

                        <div style="max-height:220px; overflow:auto; text-align:left;
                            background:#fff; padding:12px; border:1px solid #ddd; border-radius:6px;">
                            <ul style="padding-left:20px; margin:0">
                                ${notFoundList.map(id => `<li><code>${id}</code></li>`).join('')}
                            </ul>
                        </div>
                    `,
                    footer: `
                        <a href="javascript:void(0)" id="copy-mid-link" onclick="copyMidsToClipboard(this)"
                        style="font-weight:600; color:#6c757d; text-decoration:none;">
                            📋 Copy daftar MID
                        </a>
                    `,
                    confirmButtonText: 'Oke, Saya Perbaiki',
                    confirmButtonColor: '#f0ad4e',
                    allowOutsideClick: false
                });
            }
        });
    </script>
@endsection
