@extends('layouts.app')

@section('title', 'Profile User')

@section('styles')
<style>
    .profile-card {
        border-radius: 12px;
        overflow: hidden;
        max-width: 650px;
        margin: auto;
    }

    .profile-header {
        background: linear-gradient(135deg, #536976, #292E49);
        padding: 50px 20px 70px;
        text-align: center;
        color: white;
        position: relative;
    }

    .profile-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        border: 4px solid white;
        object-fit: cover;
        position: absolute;
        left: 50%;
        bottom: -55px;
        transform: translateX(-50%);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .info-label {
        font-weight: 600;
    }

    .info-value {
        font-size: 15px;
    }

    .signature-container {
        position: relative;
        width: 100%;
    }

    .signature-container canvas {
        max-width: 100%;
        height: auto;
        display: block;
    }

    .signature-container {
        aspect-ratio: 4 / 1;
    }

    @container (max-width: 768px) {
        .signature-container {
            aspect-ratio: 3 / 1;
        }
    }
</style>
@endsection

@section('content')
<div class="page-content d-flex align-items-center min-vh-100 mb-5">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">

                <!-- CARD PROFILE -->
                <div class="card shadow profile-card">

                    <!-- Header -->
                    <div class="profile-header">
                        <h3 class="mb-1 text-capitalize text-white">{{ $user->nama_lengkap ?? Auth::user()->username }}
                        </h3>
                        <span
                            class="badge bg-light text-dark">{{ ucwords(str_replace('_', ' ', $user->jabatan)) }}</span>
                        <img src="{{ $user->image_url }}" class="profile-avatar" alt="User">
                    </div>

                    <!-- Body -->
                    <div class="card-body mt-5 px-5">

                        <div class="row mb-3">
                            <div class="col-sm-4 info-label">Email:</div>
                            <div class="col-sm-8 info-value">{{ $user->email }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 info-label">NIK:</div>
                            <div class="col-sm-8 info-value">{{ $user->nik }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 info-label">Departemen:</div>
                            <div class="col-sm-8 info-value">{{ ucfirst($user->departemen) }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 info-label">Bagian:</div>
                            <div class="col-sm-8 info-value">{{ ucwords(str_replace('_', ' ', $user->bagian)) }}</div>
                        </div>

                        @if (Auth::user()->id == $user->id)
                        <div class="text-end mt-4">
                            <button class="btn btn-primary px-4 editBtn" data-id="{{ $user->id }}">
                                <i class="mdi mdi-pencil me-2"></i> Edit Profile
                            </button>
                        </div>
                        @endif

                    </div>

                </div>
                <!-- END CARD -->

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editNamaLengkap" class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editNamaLengkap" name="editNamaLengkap"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editUsername" class="form-label">Username <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editUsername" name="editUsername"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editEmail" name="editEmail" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editPassword" class="form-label">Password <small
                                        class="text-muted">(kosongkan jika tidak ingin mengubah)</small></label>
                                <input type="password" class="form-control" id="editPassword" name="editPassword"
                                    placeholder="Masukkan password baru">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editNik" class="form-label">NIK <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editNik" name="editNik" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editJabatan" class="form-label">Jabatan <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="editJabatan" name="editJabatan" required
                                    @if (Auth::user()->jabatan === 'operator') disabled @endif>
                                    <option value="">Pilih Jabatan</option>
                                    <option value="dept_head">Head of Departement</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="foreman">Foreman</option>
                                    <option value="operator">Operator</option>
                                    @if (Auth::user()->jabatan === 'admin')
                                    <option value="admin">Admin</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editDepartemen" class="form-label">Departemen</label>
                                <select class="form-select" id="editDepartemen" name="editDepartemen" required
                                    @if (Auth::user()->jabatan === 'operator') disabled @endif>
                                    <option value="">Pilih Departemen</option>
                                    <option value="warehouse">Warehouse</option>
                                    <option value="engineering">Engineering</option>
                                    <option value="quality_control">Quality Control</option>
                                    <option value="produksi">Produksi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editBagian" class="form-label">Bagian <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="editBagian" name="editBagian" required
                                    @if (Auth::user()->jabatan === 'operator') disabled @endif>
                                    <option value="" disabled>Pilih Bagian</option>
                                    <option value="warehouse">Warehouse</option>
                                    <option value="warehouse_co_product">Warehouse Co Product</option>
                                    <option value="warehouse_finish_goods">Warehouse Finish Good</option>
                                    <option value="warehouse_raw_material">Warehouse Raw Material</option>
                                    <option value="warehouse_sparepart">Warehouse Sparepart</option>
                                    <option value="engineering">Engineering</option>
                                    <option value="quality_control">Quality Control</option>
                                    <option value="produksi">Produksi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="imgEdit" class="form-label">Photo Profile</label>
                                <input type="file" class="form-control" id="imgEdit" name="image"
                                    accept=".jpeg,.jpg,.png,.gif,.svg">
                                <small class="form-text text-muted">File types: jpeg, jpg, png, gif, svg. Max size:
                                    2MB</small>
                            </div>
                        </div>
                    </div>
                    @if (Auth::user()->jabatan != 'operator' || Auth::user()->principal != null)
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanda Tangan <small
                                    class="text-muted">(opsional)</small></label>

                            <div class="border rounded p-3 bg-light signature-container">
                                <canvas id="signatureCanvas"
                                    style="border: 1px solid #ccc; border-radius: 4px; background: white; touch-action: none; width: 100%; height: auto;">
                                </canvas>

                                <div class="mt-2">
                                    <button type="button" id="clearSignature"
                                        class="btn btn-sm btn-outline-danger">
                                        <i class="bx bx-trash"></i> Hapus Tanda Tangan
                                    </button>
                                </div>
                            </div>

                            <small class="form-text text-muted d-block mt-2">
                                Gambar tanda tangan Anda menggunakan mouse atau sentuhan jari
                            </small>

                            <!-- Hidden input untuk base64 -->
                            <input type="hidden" name="signature" id="signatureData">
                        </div>
                    </div>
                    @endif

                    <div class="row mt-3">
                        <!-- Preview Gambar -->
                        <div class="col-md-6 text-center">
                            <label class="form-label fw-semibold">Preview Profile</label>
                            <div>
                                <img id="imagePreview" src="" alt="Image Preview"
                                    style="max-width: 200px; max-height: 200px; display: none;" class="img-thumbnail">
                                <img id="currentImage" src="" alt="Current Image"
                                    style="max-width: 200px; max-height: 200px; display: none;" class="img-thumbnail">
                            </div>
                        </div>
                        @if (Auth::user()->jabatan != 'operator')
                        <div class="col-md-6 text-center">
                            <label class="form-label fw-semibold">Tanda Tangan Saat Ini</label>
                            <div class="border rounded p-3 bg-light">
                                <img id="currentSignature" src="" alt="Tanda Tangan Saat Ini"
                                    style="max-width: 200px; max-height: 200px; display: none;"
                                    class="img-thumbnail shadow-sm img-thumbnail">
                                <p id="noSignatureText" class="text-muted mt-3 mb-0">
                                    Belum ada tanda tangan tersimpan
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // edit modal
        function editUser(userId) {
            $.ajax({
                url: `/user/profile/edit/${userId}`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    let user = response.data || response;

                    // Isi form fields...
                    $("#editId").val(user.id);
                    $("#editNamaLengkap").val(user.nama_lengkap);
                    $("#editUsername").val(user.username);
                    $("#editEmail").val(user.email);
                    $("#editNik").val(user.nik);
                    $("#editJabatan").val(user.jabatan);
                    $("#editDepartemen").val(user.departemen);
                    $("#editBagian").val(user.bagian);
                    $("#editPrincipal").val(user.principal?.principal || '-');

                    $("#editPassword").val('');

                    // Handle foto profil
                    if (user.image) {
                        let imagePath = "{{ asset('storage') }}/" + user.image;
                        $("#currentImage").attr('src', imagePath).show();
                        $("#imagePreview").hide();
                    } else {
                        $("#currentImage").hide();
                        $("#imagePreview").hide();
                    }

                    // Handle signature (FIX PATH DISINI)
                    if (user.signature && user.signature.signature) {
                        // Pakai asset('storage') + path dari database
                        let sigPath = "{{ asset('storage') }}/" + user.signature.signature;
                        console.log('Signature path:', sigPath); // debug: cek di console

                        $('#currentSignature')
                            .attr('src', sigPath)
                            .show()
                            .on('error', function() {
                                console.log('Gagal load signature:', sigPath);
                                $(this).hide();
                                $("#noSignatureText").text(
                                    'Gambar tanda tangan tidak ditemukan').show();
                            });

                        $("#noSignatureText").hide();
                    } else {
                        $('#currentSignature').hide();
                        $("#noSignatureText").text('Tidak ada tanda tangan').show();
                    }

                    // Reset file input
                    $("#imgEdit").val('');

                    $("#editUserModal").modal('show');
                },
                error: function(xhr) {
                    console.error("Error fetching user data:", xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data user',
                    });
                }
            });
        }

        // Event handler untuk button edit (contoh penggunaan)
        $(document).on('click', '.editBtn', function() {
            let userId = $(this).data('id');
            editUser(userId);
            initSignatureCanvas();
            clearSignature();
        });

        // Reset form ketika modal ditutup
        $('#editUserModal').on('hidden.bs.modal', function() {
            $('#editUserForm')[0].reset();
            $("#imagePreview").hide();
            $("#currentImage").hide();
        });

        $("#imgEdit").change(function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Jika ada element preview image
                    $("#imagePreview").attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });

        $("#editUserForm").submit(async function(e) {
            e.preventDefault();

            const rawSignature = document.getElementById('signatureData').value;

            if (rawSignature && rawSignature.trim() !== '') {
                const cropped = getCroppedSignature();

                if (cropped && cropped.trim() !== '') {
                    document.getElementById('signatureData').value = cropped;
                } else {
                    document.getElementById('signatureData').value = canvas.toDataURL('image/png');
                }
            }

            let id = $("#editId").val();

            // Gunakan FormData untuk handle file upload
            let formData = new FormData();

            // Tambahkan data text
            formData.append('nama_lengkap', $("#editNamaLengkap").val());
            formData.append('username', $("#editUsername").val());
            formData.append('email', $("#editEmail").val());
            formData.append('jabatan', $("#editJabatan").val());
            formData.append('nik', $("#editNik").val());
            formData.append('departemen', $("#editDepartemen").val());
            formData.append('bagian', $("#editBagian").val());

            // Tambahkan password jika diisi
            let password = $("#editPassword").val();
            if (password && password.trim() !== '') {
                formData.append('password', password);
            }

            // Tambahkan file image jika ada yang dipilih
            let imageFile = $("#imgEdit")[0].files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }

            let signatureData = $("#signatureData").val();

            if (signatureData && signatureData.trim() !== '') {
                formData.append('signature', signatureData);
            } else {
                formData.append('signature', '');
            }

            // Laravel membutuhkan method spoofing untuk PUT
            formData.append('_method', 'PUT');

            console.log('FormData entries:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: `/user/profile/update/${id}`,
                method: 'POST', // Gunakan POST dengan method spoofing
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: formData,
                processData: false, // Penting! Jangan process data
                contentType: false, // Penting! Biar browser yang set content type
                success: function(response) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message || 'User berhasil diupdated!',
                        icon: "success",
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#editUserModal').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.error("Error updating data:", xhr);

                    if (xhr.status === 422) {
                        // Validation errors
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = 'Validation errors:\n';

                        Object.keys(errors).forEach(field => {
                            errorMessage +=
                                `- ${field}: ${errors[field].join(', ')}\n`;
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorMessage,
                            showConfirmButton: true
                        });
                    } else {
                        let message = xhr.responseJSON?.message ||
                            'There was an error updating the data.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Error',
                            text: message,
                            showConfirmButton: true
                        });
                    }
                }
            });
        });
    })

    let canvas, ctx;
    let canvasWidth = 800;
    let canvasHeight = 200;

    function initSignatureCanvas() {
        canvas = document.getElementById('signatureCanvas');
        ctx = canvas.getContext('2d');

        // Fungsi resize canvas
        function resizeCanvas() {
            // Simpan gambar sementara
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

            // Set ukuran display sesuai container
            const container = canvas.parentElement;
            canvas.width = canvasWidth; // resolusi tinggi tetap
            canvas.height = canvasHeight;

            // Scale CSS agar pas di container
            canvas.style.width = '100%';
            canvas.style.height = 'auto';

            // Kembalikan gambar yang sudah digambar (biar tidak hilang saat resize)
            ctx.putImageData(imageData, 0, 0);

            // Reset style garis
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        // Resize saat load dan saat window resize
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        // Clear canvas
        document.getElementById('clearSignature').addEventListener('click', clearSignature);

        // Drawing events (mouse + touch)
        let isDrawing = false;

        const startDrawing = (e) => {
            isDrawing = true;
            draw(e);
        };

        const draw = (e) => {
            if (!isDrawing) return;
            e.preventDefault();

            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;

            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;

            ctx.lineTo(x * scaleX, y * scaleY);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x * scaleX, y * scaleY);
        };

        const stopDrawing = () => {
            if (isDrawing) {
                isDrawing = false;
                ctx.beginPath();

                const fullDataUrl = canvas.toDataURL('image/png');
                document.getElementById('signatureData').value = fullDataUrl;
            }
        };

        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch events
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);
    }

    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('signatureData').value = '';
    }

    // Panggil saat modal muncul
    $('#editUserModal').on('shown.bs.modal', function() {
        initSignatureCanvas();
    });

    // Bersihkan saat modal ditutup (opsional)
    $('#editUserModal').on('hidden.bs.modal', function() {
        if (canvas) clearSignature();
    });

    function getCroppedSignature() {
        if (!canvas || !ctx) return '';

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;

        let minX = canvas.width;
        let minY = canvas.height;
        let maxX = 0;
        let maxY = 0;

        for (let y = 0; y < canvas.height; y++) {
            for (let x = 0; x < canvas.width; x++) {
                const index = (y * canvas.width + x) * 4;
                const alpha = data[index + 3];

                if (alpha > 10) { // garis hitam pasti alpha = 255
                    minX = Math.min(minX, x);
                    maxX = Math.max(maxX, x);
                    minY = Math.min(minY, y);
                    maxY = Math.max(maxY, y);
                }
            }
        }

        // Jika tidak ada pixel terdeteksi
        if (maxX <= minX || maxY <= minY) {
            return '';
        }

        // Padding
        const padding = 30;
        minX = Math.max(minX - padding, 0);
        minY = Math.max(minY - padding, 0);
        maxX = Math.min(maxX + padding, canvas.width);
        maxY = Math.min(maxY + padding, canvas.height);

        const width = maxX - minX;
        const height = maxY - minY;

        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = width;
        tempCanvas.height = height;
        const tempCtx = tempCanvas.getContext('2d');

        tempCtx.drawImage(canvas, minX, minY, width, height, 0, 0, width, height);

        return tempCanvas.toDataURL('image/png');
    }
</script>
@endsection