@extends('layouts.app')

@section('title', 'Edit Profile')

@section('styles')
    <style>
        .edit-profile-card {
            border-radius: 12px;
            overflow: hidden;
            max-width: 800px;
            margin: auto;
        }

        .edit-profile-header {
            background: linear-gradient(135deg, #536976, #292E49);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }

        .signature-container {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 1;
        }

        @media (max-width: 768px) {
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
                <div class="col-md-10 col-lg-8">

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-outline me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- CARD PROFILE -->
                    <div class="card shadow edit-profile-card border-0">

                        <!-- Header -->
                        <div class="edit-profile-header">
                            <h4 class="mb-0 text-white"><i class="mdi mdi-account-edit me-2"></i>Edit Profile</h4>
                            <p class="mb-0 text-white-50">Perbarui data informasi akun dan tanda tangan Anda</p>
                        </div>

                        <!-- Body -->
                        <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data"
                            id="editProfileForm">
                            @csrf
                            @method('PUT')

                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nama_lengkap" class="form-label fw-semibold">Nama Lengkap <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                                value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
                                            @error('nama_lengkap')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="username" class="form-label fw-semibold">Username <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                value="{{ old('username', $user->username) }}" required>
                                            @error('username')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-semibold">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="{{ old('email', $user->email) }}" required>
                                            @error('email')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nik" class="form-label fw-semibold">NIK <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="nik" name="nik"
                                                value="{{ old('nik', $user->nik) }}" required>
                                            @error('nik')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jabatan" class="form-label fw-semibold">Jabatan</label>
                                            <select class="form-select text-capitalize" id="jabatan" name="jabatan"
                                                required @if (Auth::user()->jabatan === 'operator') disabled @endif>
                                                <option value="">Pilih Jabatan</option>
                                                <option value="dept_head"
                                                    {{ old('jabatan', $user->jabatan) == 'dept_head' ? 'selected' : '' }}>
                                                    Head of Departement</option>
                                                <option value="supervisor"
                                                    {{ old('jabatan', $user->jabatan) == 'supervisor' ? 'selected' : '' }}>
                                                    Supervisor</option>
                                                <option value="foreman"
                                                    {{ old('jabatan', $user->jabatan) == 'foreman' ? 'selected' : '' }}>
                                                    Foreman</option>
                                                <option value="operator"
                                                    {{ old('jabatan', $user->jabatan) == 'operator' ? 'selected' : '' }}>
                                                    Operator</option>
                                                @if (Auth::user()->jabatan === 'admin')
                                                    <option value="admin"
                                                        {{ old('jabatan', $user->jabatan) == 'admin' ? 'selected' : '' }}>
                                                        Admin</option>
                                                    <option value="fm"
                                                        {{ old('jabatan', $user->jabatan) == 'fm' ? 'selected' : '' }}>
                                                        Factory Manager</option>
                                                @endif
                                            </select>
                                            @if (Auth::user()->jabatan === 'operator')
                                                <input type="hidden" name="jabatan" value="{{ $user->jabatan }}">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="departemen" class="form-label fw-semibold">Departemen</label>
                                            <select class="form-select text-capitalize" id="departemen" name="departemen"
                                                required @if (Auth::user()->jabatan === 'operator') disabled @endif>
                                                <option value="">Pilih Departemen</option>
                                                <option value="warehouse"
                                                    {{ old('departemen', $user->departemen) == 'warehouse' ? 'selected' : '' }}>
                                                    Warehouse</option>
                                                <option value="engineering"
                                                    {{ old('departemen', $user->departemen) == 'engineering' ? 'selected' : '' }}>
                                                    Engineering</option>
                                                <option value="quality_control"
                                                    {{ old('departemen', $user->departemen) == 'quality_control' ? 'selected' : '' }}>
                                                    Quality Control</option>
                                                <option value="produksi"
                                                    {{ old('departemen', $user->departemen) == 'produksi' ? 'selected' : '' }}>
                                                    Produksi</option>
                                                <option value="ppic"
                                                    {{ old('departemen', $user->departemen) == 'ppic' ? 'selected' : '' }}>
                                                    PPIC</option>
                                                <option value="purchasing"
                                                    {{ old('departemen', $user->departemen) == 'purchasing' ? 'selected' : '' }}>
                                                    Purchasing</option>
                                                <option value="hrga"
                                                    {{ old('departemen', $user->departemen) == 'hrga' ? 'selected' : '' }}>
                                                    HRGA</option>
                                                <option value="expedisi"
                                                    {{ old('departemen', $user->departemen) == 'expedisi' ? 'selected' : '' }}>
                                                    Expedisi</option>
                                                <option value="timbangan"
                                                    {{ old('departemen', $user->departemen) == 'timbangan' ? 'selected' : '' }}>
                                                    Timbangan</option>
                                                @if (Auth::user()->jabatan == 'admin')
                                                    <option value="FM"
                                                        {{ old('departemen', $user->departemen) == 'FM' ? 'selected' : '' }}>
                                                        Factory Manager</option>
                                                    <option value="IT"
                                                        {{ old('departemen', $user->departemen) == 'IT' ? 'selected' : '' }}>
                                                        ITE</option>
                                                @endif
                                            </select>
                                            @if (Auth::user()->jabatan === 'operator')
                                                <input type="hidden" name="departemen" value="{{ $user->departemen }}">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bagian" class="form-label fw-semibold">Bagian</label>
                                            <select class="form-select text-capitalize" id="bagian" name="bagian"
                                                required @if (Auth::user()->jabatan === 'operator') disabled @endif>
                                                <option value="" disabled>Pilih Bagian</option>
                                                <option value="warehouse"
                                                    {{ old('bagian', $user->bagian) == 'warehouse' ? 'selected' : '' }}>
                                                    Warehouse</option>
                                                <option value="warehouse_co_product"
                                                    {{ old('bagian', $user->bagian) == 'warehouse_co_product' ? 'selected' : '' }}>
                                                    Warehouse Co Product</option>
                                                <option value="warehouse_finish_goods"
                                                    {{ old('bagian', $user->bagian) == 'warehouse_finish_goods' ? 'selected' : '' }}>
                                                    Warehouse Finish Good</option>
                                                <option value="warehouse_raw_material"
                                                    {{ old('bagian', $user->bagian) == 'warehouse_raw_material' ? 'selected' : '' }}>
                                                    Warehouse Raw Material</option>
                                                <option value="warehouse_sparepart"
                                                    {{ old('bagian', $user->bagian) == 'warehouse_sparepart' ? 'selected' : '' }}>
                                                    Warehouse Sparepart</option>
                                                <option value="warehouse_packaging_material"
                                                    {{ old('bagian', $user->bagian) == 'warehouse_packaging_material' ? 'selected' : '' }}>
                                                    Warehouse Packaging Material</option>
                                                <option value="engineering"
                                                    {{ old('bagian', $user->bagian) == 'engineering' ? 'selected' : '' }}>
                                                    Engineering</option>
                                                <option value="engineering_utility"
                                                    {{ old('bagian', $user->bagian) == 'engineering_utility' ? 'selected' : '' }}>
                                                    Engineering Utility</option>
                                                <option value="engineering_production"
                                                    {{ old('bagian', $user->bagian) == 'engineering_production' ? 'selected' : '' }}>
                                                    Engineering Production</option>
                                                <option value="engineering_project"
                                                    {{ old('bagian', $user->bagian) == 'engineering_project' ? 'selected' : '' }}>
                                                    Engineering Project</option>
                                                <option value="quality_control"
                                                    {{ old('bagian', $user->bagian) == 'quality_control' ? 'selected' : '' }}>
                                                    Quality Control</option>
                                                <option value="quality_control_rmpm"
                                                    {{ old('bagian', $user->bagian) == 'quality_control_rmpm' ? 'selected' : '' }}>
                                                    Quality Control RMPM</option>
                                                <option value="quality_control_proses"
                                                    {{ old('bagian', $user->bagian) == 'quality_control_proses' ? 'selected' : '' }}>
                                                    Quality Control Proses</option>
                                                <option value="produksi"
                                                    {{ old('bagian', $user->bagian) == 'produksi' ? 'selected' : '' }}>
                                                    Produksi</option>
                                                <option value="ppic"
                                                    {{ old('bagian', $user->bagian) == 'ppic' ? 'selected' : '' }}>PPIC
                                                </option>
                                                <option value="purchasing"
                                                    {{ old('bagian', $user->bagian) == 'purchasing' ? 'selected' : '' }}>
                                                    Purchasing</option>
                                                <option value="hrga"
                                                    {{ old('bagian', $user->bagian) == 'hrga' ? 'selected' : '' }}>HRGA
                                                </option>
                                                <option value="expedisi"
                                                    {{ old('bagian', $user->bagian) == 'expedisi' ? 'selected' : '' }}>
                                                    Expedisi</option>
                                                <option value="timbangan"
                                                    {{ old('bagian', $user->bagian) == 'timbangan' ? 'selected' : '' }}>
                                                    Timbangan</option>
                                                @if (Auth::user()->jabatan == 'admin')
                                                    <option value="FM"
                                                        {{ old('bagian', $user->bagian) == 'FM' ? 'selected' : '' }}>
                                                        Factory Manager</option>
                                                    <option value="IT"
                                                        {{ old('bagian', $user->bagian) == 'IT' ? 'selected' : '' }}>ITE
                                                    </option>
                                                @endif
                                            </select>
                                            @if (Auth::user()->jabatan === 'operator')
                                                <input type="hidden" name="bagian" value="{{ $user->bagian }}">
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="imgEdit" class="form-label fw-semibold">Photo Profile</label>
                                            <input type="file" class="form-control" id="imgEdit" name="image"
                                                accept=".jpeg,.jpg,.png,.gif,.svg">
                                            <small class="form-text text-muted d-block">Tipe file: jpeg, jpg, png, gif,
                                                svg. Ukuran maks: 2MB</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Signature Section -->
                                @if (Auth::user()->jabatan != 'operator' || Auth::user()->principal != null)
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Tanda Tangan Digital <small
                                                        class="text-muted">(opsional)</small></label>
                                                <div class="border rounded p-3 bg-light position-relative">
                                                    <div class="signature-container mb-2">
                                                        <canvas id="signatureCanvas"
                                                            style="border: 1px solid #ccc; border-radius: 4px; background: white; touch-action: none; width: 100%; height: 100%;">
                                                        </canvas>
                                                    </div>
                                                    <div>
                                                        <button type="button" id="clearSignature"
                                                            class="btn btn-sm btn-outline-danger">
                                                            <i class="mdi mdi-eraser me-1"></i> Hapus Coretan
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted d-block mt-1">
                                                    Gambarkan tanda tangan Anda menggunakan mouse atau layar sentuh.
                                                </small>
                                                <input type="hidden" name="signature" id="signatureData">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Preview Section -->
                                <div class="row mt-4 pt-3 border-top border-light">
                                    <div class="col-md-6 text-center mb-3">
                                        <label class="form-label fw-semibold d-block">Preview Foto Profil</label>
                                        <div class="border rounded p-3 bg-light d-inline-block">
                                            <img id="imagePreview" src="" alt="Image Preview"
                                                style="max-width: 150px; max-height: 150px; display: none;"
                                                class="img-thumbnail shadow-sm">
                                            <img id="currentImage" src="{{ $user->image_url }}" alt="Current Image"
                                                style="max-width: 150px; max-height: 150px;"
                                                class="img-thumbnail shadow-sm">
                                        </div>
                                    </div>

                                    @if (Auth::user()->jabatan != 'operator')
                                        <div class="col-md-6 text-center mb-3">
                                            <label class="form-label fw-semibold d-block">Tanda Tangan Saat Ini</label>
                                            <div class="border rounded p-3 bg-light d-inline-block"
                                                style="min-width: 200px;">
                                                @if ($user->signature && $user->signature->signature)
                                                    @php
                                                        $rawSig = $user->signature->signature;
                                                        $sigPath = '';
                                                        if (
                                                            str_starts_with($rawSig, 'storage/') ||
                                                            str_starts_with($rawSig, '/storage/')
                                                        ) {
                                                            $rawSig = ltrim($rawSig, '/');
                                                            $sigPath = asset($rawSig);
                                                        } else {
                                                            $sigPath = asset('storage/' . $rawSig);
                                                        }
                                                    @endphp
                                                    <img id="currentSignature" src="{{ $sigPath }}"
                                                        alt="Tanda Tangan" style="max-width: 200px; max-height: 100px;"
                                                        class="img-thumbnail shadow-sm">
                                                    <p id="noSignatureText" class="text-muted mb-0 mt-2 d-none">Belum ada
                                                        tanda tangan tersimpan</p>
                                                @else
                                                    <p id="noSignatureText" class="text-muted mb-0 py-4">Belum ada tanda
                                                        tangan tersimpan</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="card-footer bg-light p-4 d-flex justify-content-between">
                                <a href="{{ route('user.profile') }}" class="btn btn-secondary px-4">
                                    <i class="mdi mdi-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-content-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let canvas, ctx;
        let canvasWidth = 800;
        let canvasHeight = 200;

        function initSignatureCanvas() {
            canvas = document.getElementById('signatureCanvas');
            if (!canvas) return;
            ctx = canvas.getContext('2d');

            function resizeCanvas() {
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                canvas.width = canvasWidth;
                canvas.height = canvasHeight;
                canvas.style.width = '100%';
                canvas.style.height = 'auto';
                ctx.putImageData(imageData, 0, 0);

                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
            }

            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            document.getElementById('clearSignature').addEventListener('click', clearSignature);

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

                const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
                const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);

                const x = clientX - rect.left;
                const y = clientY - rect.top;

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

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            canvas.addEventListener('touchstart', startDrawing);
            canvas.addEventListener('touchmove', draw);
            canvas.addEventListener('touchend', stopDrawing);
        }

        function clearSignature() {
            if (!ctx) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signatureData').value = '';
        }

        $(document).ready(function() {
            initSignatureCanvas();

            $("#imgEdit").change(function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $("#currentImage").hide();
                        $("#imagePreview").attr('src', e.target.result).show();
                    };
                    reader.readAsDataURL(file);
                }
            });

            $("#editProfileForm").submit(function(e) {
                const rawSignature = document.getElementById('signatureData') ? document.getElementById(
                    'signatureData').value : '';

                if (rawSignature && rawSignature.trim() !== '') {
                    const cropped = getCroppedSignature();
                    if (cropped && cropped.trim() !== '') {
                        document.getElementById('signatureData').value = cropped;
                    }
                }
            });
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

                    if (alpha > 10) {
                        minX = Math.min(minX, x);
                        maxX = Math.max(maxX, x);
                        minY = Math.min(minY, y);
                        maxY = Math.max(maxY, y);
                    }
                }
            }

            if (maxX <= minX || maxY <= minY) {
                return '';
            }

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
