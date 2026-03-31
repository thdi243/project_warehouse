@extends('layouts.app')

@section('styles')
<style>
    .img-fixed {
        height: 250px;
        object-fit: cover;
    }

    .user-img {
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title d-sm-flex align-items-center justify-content-between">
                    {{-- <h4 class="mb-sm-0">Form Input TKBM</h4> --}}

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">User</a></li>
                            <li class="breadcrumb-item active">List</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-sm-4">
                        <div class="search-box">
                            <input type="text" class="form-control" id="searchMemberList"
                                placeholder="Search for name or etc..." />
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                    <div class="col-sm-auto ms-auto">
                        <button type="button" id="btnStatistikUser"
                            class="btn btn-primary filter-button shadow-none"><i
                                class="ri-bar-chart-fill me-1 align-bottom"></i>
                            Statistik Users
                        </button>
                        <button type="button" id="grid-view-button" title="Grid View"
                            class="btn btn-soft-info nav-link btn-icon fs-14 active filter-button shadow-none">
                            <i class="ri-grid-fill"></i>
                        </button>
                        <button type="button" id="list-view-button" title="List View"
                            class="btn btn-soft-info nav-link btn-icon fs-14 filter-button shadow-none">
                            <i class="ri-list-unordered"></i>
                        </button>
                        <button type="button" class="btn btn-success addMembers-modal" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">
                            <i class="ri-add-fill me-1 align-bottom"></i> Add Users
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- chart statistik --}}
        <div id="statistikUser" style="display:none;">
            <div class="row">
                <div class="col-xl-4 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">Total Users</h5>
                                <h2 id="totalUsers" class="fw-bold text-info">0</h2>
                                <p class="mb-0 text-muted">
                                    Warehouse User Total
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">Total Jabatan</h5>
                                <h2 id="totalJabatan" class="fw-bold text-primary">0</h2>
                                <p class="mb-0 text-muted">
                                    Warehouse User Jabatan
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card card-height-100">
                        <div class="d-flex">
                            <div class="flex-grow-1 p-3">
                                <h5 class="mb-3">Total Bagian</h5>
                                <h2 id="totalBagian" class="fw-bold text-danger">0</h2>
                                <p class="mb-0 text-muted">
                                    Warehouse User Bagian
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Row -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div id="chartJabatan"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div id="chartBagian"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- card user --}}
        <div class="row" id="userRow">
            {{-- diisi ajax --}}
        </div>

        {{-- List user --}}
        <div id="team-member-list"></div>
    </div>
</div>

{{-- Modal add user --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" class="needs-validation" enctype="multipart/form-data" novalidate>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center mb-5">
                                <div class="position-relative d-inline-block mb-4">
                                    <!-- Avatar Circle -->
                                    <div class="avatar-lg">
                                        <img id="preview-image"
                                            src="{{ asset('material/assets/images/users/user-dummy-img.jpg') }}"
                                            class="avatar-md rounded-circle border-2 border-light shadow"
                                            style="width: 120px; height: 120px; object-fit: cover;" />
                                    </div>

                                    <!-- Upload Button -->
                                    <div class="position-absolute bottom-0 end-0">
                                        <label for="member-image-input" class="mb-0" title="Ganti Foto">
                                            <div class="avatar-xs">
                                                <div
                                                    class="avatar-title bg-primary text-white rounded-circle shadow-sm cursor-pointer">
                                                    <i class="ri-camera-fill"></i>
                                                </div>
                                            </div>
                                        </label>
                                        <input type="file" class="d-none" id="member-image-input" name="image"
                                            accept="image/png, image/jpeg" />
                                    </div>
                                </div>
                                <p class="text-muted mt-2 mb-0">Klik ikon kamera untuk upload foto</p>
                            </div>
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                    placeholder="Enter full name" required />
                                <div class="invalid-feedback">Please enter a member name.</div>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="Enter name" required />
                                <div class="invalid-feedback">Please enter a member name.</div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Enter email" required />
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                            <div class="mb-3">
                                <label for="nik" class="form-label">NIK <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="nik" name="nik"
                                    placeholder="Enter nik" required />
                                <div class="invalid-feedback">Please enter nik.</div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Enter password" required />
                                <div class="invalid-feedback">Please enter a password</div>
                            </div>
                            <div class="mb-3">
                                <label for="jabatan" class="form-label">Jabatan <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="jabatan" name="jabatan" required>
                                    <option value="" disabled selected>Pilih Jabatan</option>
                                    <option value="dept_head">Head of Departemen</option>
                                    <option value="foreman">Foreman</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="operator">Operator</option>
                                </select>
                                {{-- <div class="invalid-feedback">Please select a Jabatan.</div> --}}
                            </div>

                            <div class="mb-3">
                                <label for="departemen" class="form-label">Departemen <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="departemen" name="departemen" required>
                                    <option value="" disabled selected>Pilih Departemen</option>
                                    <option value="warehouse">Warehouse</option>
                                    <option value="engineering">Engineering</option>
                                    <option value="quality_control">Quality Control</option>
                                    <option value="produksi">Produksi</option>
                                </select>
                                {{-- <div class="invalid-feedback">Please select a Departemen.</div> --}}
                            </div>

                            <div class="mb-3">
                                <label for="bagian" class="form-label">Bagian <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="bagian" name="bagian" required>
                                    <option value="" disabled selected>Pilih Bagian</option>
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

                            <div class="mb-3">
                                <label for="principal" class="form-label">Principal</label>
                                <input type="text" class="form-control" id="principal" name="principal"
                                    placeholder="Enter prncipal (opsional)" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanda Tangan</label>
                                <div class="border rounded bg-light text-center p-2">
                                    <canvas id="signature-pad"
                                        style="width: 100%; height: 200px; border: 1px solid #ccc;"></canvas>
                                </div>
                                <div class="mt-2 d-flex justify-content-between">
                                    <button type="button" id="clear-signature"
                                        class="btn btn-sm btn-outline-secondary">Hapus</button>
                                </div>
                                <input type="hidden" name="signature" id="signature-input">
                            </div>


                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">Add User</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal edit --}}
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
                                <input type="text" class="form-control" id="editNamaLengkap"
                                    name="editNamaLengkap" required>
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
                                <label for="editNik" class="form-label">NIK <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editNik" name="editNik" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editJabatan" class="form-label">Jabatan <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="editJabatan" name="editJabatan" required>
                                    <option value="">Pilih Jabatan</option>
                                    <option value="dept_head">Head of Departement</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="foreman">Foreman</option>
                                    <option value="operator">Operator</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editDepartemen" class="form-label">Departemen <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="editDepartemen" name="editDepartemen" required>
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
                                <select class="form-select" id="editBagian" name="editBagian" required>
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editPrincipal" class="form-label">Principal <small
                                        class="text-muted">(opsional)</small></label>
                                <input type="text" class="form-control" id="editPrincipal" name="editPrincipal">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Tanda Tangan</label>
                                <div class="border rounded bg-light text-center p-2">
                                    <canvas id="edit-signature-pad"
                                        style="width: 100%; height: 200px; border: 1px solid #ccc;"></canvas>
                                </div>
                                <div class="mt-2 d-flex justify-content-between">
                                    <button type="button" id="edit-clear-signature"
                                        class="btn btn-sm btn-outline-secondary">Hapus</button>
                                </div>
                                <input type="hidden" name="signature" id="edit-signature-input">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Preview Gambar -->
                        <div class="col-md-6 text-center">
                            <label class="form-label fw-semibold">Preview Gambar</label>
                            <div>
                                <img id="imagePreview" src="" alt="Image Preview"
                                    style="max-width: 200px; max-height: 200px; display: none;" class="img-thumbnail">
                                <img id="currentImage" src="" alt="Current Image"
                                    style="max-width: 200px; max-height: 200px; display: none;" class="img-thumbnail">
                            </div>
                        </div>

                        <!-- Preview Signature -->
                        <div class="col-md-6 text-center">
                            <label class="form-label fw-semibold">Preview Tanda Tangan</label>
                            <div id="edit-signature-preview" style="display: none;">
                                <img id="edit-signature-image" src="" alt="Preview Signature"
                                    style="max-width: 200px; border: 1px solid #ccc;" class="img-thumbnail">
                            </div>
                        </div>
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

{{-- overlay img --}}
<div id="imgPreviewOverlay"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
            background:rgba(0,0,0,0.7); z-index:1050; 
            justify-content:center; align-items:center;">
    <img id="imgPreview" src=""
        style="max-width:90%; max-height:90%; border-radius:8px; box-shadow:0 4px 15px rgba(0,0,0,0.5);" />
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let addSignaturePad, editSignaturePad;

        function initSignaturePad(canvasSelector) {
            const $canvas = $(canvasSelector);
            const canvas = $canvas[0];
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            const ctx = canvas.getContext('2d');
            ctx.scale(ratio, ratio);

            return new SignaturePad(canvas, {
                backgroundColor: 'rgba(0, 0, 0, 0)', // HARUS transparan
                penColor: 'rgb(0, 0, 0)'
            });
        }

        async function trimSignature(dataURL) {
            return new Promise((resolve) => {
                const img = new Image();
                img.src = dataURL;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);

                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const pixels = imageData.data;

                    let top = null,
                        bottom = null,
                        left = null,
                        right = null;

                    for (let y = 0; y < canvas.height; y++) {
                        for (let x = 0; x < canvas.width; x++) {
                            const idx = (y * canvas.width + x) * 4;
                            if (pixels[idx + 3] > 0) {
                                if (top === null) top = y;
                                bottom = y;
                                if (left === null || x < left) left = x;
                                if (right === null || x > right) right = x;
                            }
                        }
                    }

                    if (top === null) {
                        resolve(''); // kosong (gak ada tanda tangan)
                        return;
                    }

                    const trimmedWidth = right - left + 1;
                    const trimmedHeight = bottom - top + 1;
                    const trimmedCanvas = document.createElement('canvas');
                    trimmedCanvas.width = trimmedWidth;
                    trimmedCanvas.height = trimmedHeight;

                    const trimmedCtx = trimmedCanvas.getContext('2d');
                    trimmedCtx.drawImage(
                        canvas,
                        left, top, trimmedWidth, trimmedHeight,
                        0, 0, trimmedWidth, trimmedHeight
                    );

                    resolve(trimmedCanvas.toDataURL());
                };
            });
        }

        // ========== ADD USER ==========
        $('#addUserModal').on('shown.bs.modal', function() {
            addSignaturePad = initSignaturePad('#signature-pad');
        });

        $('#addUserModal').on('hidden.bs.modal', function() {
            if (addSignaturePad) addSignaturePad.clear();
            $('#signature-input').val('');
        });

        $('#clear-signature').on('click', function() {
            if (addSignaturePad) addSignaturePad.clear();
            $('#signature-input').val('');
        });

        // ========== EDIT USER ==========
        $('#editUserModal').on('shown.bs.modal', function() {
            editSignaturePad = initSignaturePad('#edit-signature-pad');

            // tampilkan tanda tangan lama kalau ada
            const existingSignature = $('#edit-signature-input').val();
            if (existingSignature) {
                const img = new Image();
                img.src = existingSignature;
                img.onload = function() {
                    const ctx = $('#edit-signature-pad')[0].getContext('2d');
                    ctx.drawImage(img, 0, 0, ctx.canvas.width / (window.devicePixelRatio || 1),
                        ctx.canvas.height / (window.devicePixelRatio || 1));
                };
            }
        });

        $('#editUserModal').on('hidden.bs.modal', function() {
            if (editSignaturePad) editSignaturePad.clear();
            $('#edit-signature-input').val('');
            $('#edit-signature-preview').hide();
        });

        $('#edit-clear-signature').on('click', function() {
            if (editSignaturePad) editSignaturePad.clear();
            $('#edit-signature-input').val('');
        });

        // kode preview gambar add
        $("#member-image-input").on("change", function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $("#preview-image").attr("src", e.target.result);
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        // kode preview gambar edit
        $("#imgEdit").on("change", function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $("#previewImgEdit").attr("src", e.target.result);
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        // search fitur
        $("#searchMemberList").on("keyup", function() {
            let searchText = $(this).val().toLowerCase();
            let visibleCount = 0;

            $(".team-card").each(function() {
                let username = $(this).find(".username").text().toLowerCase();
                let jabatan = $(this).find(".jabatan").text().toLowerCase();
                let nik = $(this).find(".nik").text().toLowerCase();
                let email = $(this).find(".email").text().toLowerCase();
                let bagian = $(this).find(".bagian").text().toLowerCase(); // grid view

                if (
                    username.includes(searchText) ||
                    jabatan.includes(searchText) ||
                    nik.includes(searchText) ||
                    email.includes(searchText) ||
                    bagian.includes(searchText)
                ) {
                    $(this).closest("[class*='col-']").show();
                    visibleCount++;
                } else {
                    $(this).closest("[class*='col-']").hide();
                }
            });

            $("#emptyState").remove();

            // Kalau tidak ada hasil yang cocok
            if (visibleCount === 0) {
                $("#userRow").append(`
                        <div id="emptyState" class="col-12 text-center my-2">
                            <div class="card border-0 shadow-sm py-3">
                                <div class="card-body">
                                    <img src="{{ asset('assets/images/empty_state.png') }}" alt="Empty" style="width:150px;">
                                    <h5 class="text-muted">Tidak ada hasil yang cocok</h5>
                                    <p class="text-secondary">Coba kata kunci lain atau periksa kembali ejaan pencarianmu.</p>
                                </div>
                            </div>
                        </div>
                    `);
            }

            AOS.refresh();
        });

        getData();

        const jabatanLevel = {
            operator: 1,
            foreman: 2,
            supervisor: 3,
            dept_head: 4,
            admin: 5
        };

        const currentUserJabatan = "{{ auth()->user()->jabatan }}";
        const currentUserId = "{{ auth()->user()->id }}";
        const currentUserLevel = jabatanLevel[currentUserJabatan] || 0;

        function getData() {
            $.ajax({
                url: `/master/user/get-data`,
                method: "GET",
                success: function(res) {
                    let users = res.data || res;

                    if (!users || users.length === 0) {
                        $("#userRow").html(`
                                <div class="col-12 d-flex justify-content-center">
                                <div class="card border-0 shadow-sm py-3">
                                        <div class="card-body">
                                            <img src="{{ asset('assets/images/empty_state.png') }}" alt="Empty" style="width:150px;">
                                            <h5 class="text-muted">Tidak ada hasil yang cocok</h5>
                                            <p class="text-secondary">Coba kata kunci lain atau periksa kembali ejaan pencarianmu.</p>
                                        </div>
                                    </div>
                                </div>
                            `);
                        return;
                    }

                    users.forEach((user, index) => {
                        let badgeClass = "";
                        switch ((user.jabatan || "").toLowerCase()) {
                            case "dept_head":
                                badgeClass = "bg-danger";
                                break;
                            case "supervisor":
                                badgeClass = "bg-success";
                                break;
                            case "foreman":
                                badgeClass = "bg-warning";
                                break;
                            case "operator":
                                badgeClass = "bg-info";
                                break;
                            default:
                                badgeClass = "bg-secondary";
                        }

                        const bagianFormatted = user.bagian ?
                            user.bagian.replace(/_/g, " ").replace(/\b\w/g, c => c
                                .toUpperCase()) :
                            "-";

                        const imgSrc = user.image_url || "/default.png";
                        const delay = (index * 200) % 1000;

                        // Cek apakah user adalah admin → disable tombol edit & delete
                        const targetLevel = jabatanLevel[(user.jabatan || "")
                            .toLowerCase()] || 0;

                        // admin bebas segalanya
                        let canModify = false;

                        if (currentUserLevel === 5) {
                            canModify = true;
                        } else if (parseInt(user.id) === parseInt(currentUserId)) {
                            canModify = true;
                        } else {
                            // hanya boleh edit/delete user dengan level lebih rendah
                            canModify = currentUserLevel > targetLevel;
                        }

                        const editDisabled = canModify ? "" : "disabled";
                        const deleteDisabled = canModify ? "" : "disabled";
                        const btnClassDisabled = canModify ? "" :
                            "opacity-50 cursor-not-allowed";

                        const card = `
                                <div class="col-md-3 mb-3">
                                    <div data-aos="fade-up" data-aos-delay="${delay}" data-aos-anchor-placement="top-bottom">
                                        <div class="card card-animate shadow-sm border-0 rounded-3 team-card">
                                            <img src="${imgSrc}" class="card-img-top rounded-top img-fixed user-img" 
                                                alt="foto ${user.nama_lengkap || user.username}" style="height:200px; object-fit:cover;">
                                            <div class="card-body">
                                                <h4 class="card-title text-capitalize username">${user.nama_lengkap || user.username}</h4>
                                                <span class="badge ${badgeClass} px-3 py-2 mb-2 fs-7 jabatan">${user.jabatan}</span>
                                                <p class="card-text text-muted mb-1 email"><i class="bi bi-envelope"></i> ${user.email}</p>
                                                <p class="card-text text-muted mb-1 nik"><i class="bi bi-telephone"></i> ${user.nik}</p>
                                                <p class="card-text text-muted mb-1 bagian"><i class="bi bi-building"></i> ${bagianFormatted}</p>
                                            </div>
                                            <div class="card-footer border-0 d-flex justify-content-between">
                                                <button class="btn btn-outline-primary btn-sm editBtn ${btnClassDisabled}" 
                                                    data-id="${user.id}" ${editDisabled}>Edit</button>
                                                <button class="btn btn-outline-danger btn-sm deleteBtn ${btnClassDisabled}" 
                                                    data-id="${user.id}" ${deleteDisabled}>Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        $("#userRow").append(card);
                    });

                    AOS.refresh();
                    $('#team-member-list').empty();
                },
                error: function(err) {
                    console.error("Error load data:", err);
                }
            });
        }

        // Grid view toggle
        $("#grid-view-button").on("click", function() {
            $(this).addClass("active");
            $("#list-view-button").removeClass("active");
            $("#btnStatistikUser").removeClass("active");
            $("#statistikUser").hide();
            getData(); // tampilkan grid
        });

        // List view toggle
        $("#list-view-button").on("click", function() {
            $(this).addClass("active");
            $("#grid-view-button").removeClass("active");
            $("#btnStatistikUser").removeClass("active");
            $("#statistikUser").hide();

            $.ajax({
                url: `/master/user/get-data`,
                type: "GET",
                success: function(res) {
                    let userList = '';

                    res.data.forEach((user, index) => {
                        const imgSrc = user.image_url || "/default.png";
                        const delay = (index * 200) % 1000;

                        userList += `
                                <div class="col-lg-12 mb-3">
                                    <div data-aos="fade-up" data-aos-delay="${delay}" data-aos-anchor-placement="top-bottom">
                                        <div class="card team-box team-card">
                                            <div class="card-body p-3">
                                                <div class="row align-items-center">
                                                    <!-- Profile Image -->
                                                    <div class="col-auto">
                                                        <div class="avatar-lg img-thumbnail rounded-circle flex-shrink-0">
                                                            <img src="${imgSrc}" alt="" 
                                                                class="member-img img-fluid d-block rounded-circle" 
                                                                style="height:85px; width:85px; object-fit:cover;">
                                                        </div>
                                                    </div>

                                                    <!-- Name & Position -->
                                                    <div class="col">
                                                        <a class="member-name" data-bs-toggle="offcanvas" href="#member-overview" aria-controls="member-overview">
                                                            <h5 class="fs-16 mb-1 username">${user.username}</h5>
                                                        </a>
                                                        <p class="text-muted mb-0 jabatan">${user.jabatan}</p>
                                                    </div>

                                                    <!-- Email -->
                                                    <div class="col text-center">
                                                        <h5 class="mb-1 email">${user.email}</h5>
                                                        <p class="text-muted mb-0">Email</p>
                                                    </div>

                                                    <!-- Departemen -->
                                                    <div class="col text-center">
                                                        <h5 class="mb-1 bagian">${user.bagian ?? "-"}</h5>
                                                        <p class="text-muted mb-0">Bagian</p>
                                                    </div>

                                                    <!-- NIK -->
                                                    <div class="col text-center">
                                                        <h5 class="mb-1 nik">${user.nik ?? "-"}</h5>
                                                        <p class="text-muted mb-0">NIK</p>
                                                    </div>

                                                    <!-- Actions -->
                                                    <div class="col-auto">
                                                        <button class="btn btn-sm btn-primary editBtn" data-id="${user.id}">
                                                            <i class="ri-pencil-line"></i> Edit
                                                        </button>
                                                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${user.id}">
                                                            <i class="ri-delete-bin-5-line"></i> Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                    });

                    AOS.refresh();
                    // render ke container list
                    $('#team-member-list').html(userList);
                    $("#userRow").empty();
                },
                error: function(xhr) {
                    console.log("Error fetching data:", xhr.responseText);
                }
            });
        });

        // add users
        $('#addUserForm').submit(async function(e) {
            e.preventDefault();

            // Cek validasi form
            var form = this;
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                $('member-image-input').add('was-validated');

                Swal.fire({
                    title: "Data Belum Lengkap",
                    text: "Silakan lengkapi semua field yang wajib diisi.",
                    icon: "warning"
                });

                return;
            }

            // Pastikan tanda tangan tidak kosong
            // if (addSignaturePad.isEmpty()) {
            //     Swal.fire('Tanda Tangan Diperlukan', 'Silakan isi tanda tangan terlebih dahulu.',
            //         'warning');
            //     return;
            // }

            const dataURL = addSignaturePad.toDataURL();
            const trimmedDataURL = await trimSignature(dataURL);

            // if (!trimmedDataURL) {
            //     Swal.fire('Tanda Tangan Kosong', 'Tanda tangan tidak terdeteksi.', 'warning');
            //     return;
            // }

            // Siapkan form data
            const formData = new FormData(form);
            formData.append('signature', trimmedDataURL);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: "/master/user/store",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire({
                        title: "Success!",
                        text: 'User berhasil ditambahkan!',
                        icon: "success",
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => {
                        $('#addUserModal').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.error("Error Response:", xhr);

                    let errorMsg = "Failed to add user.";
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat()
                                .join(
                                    "\n");
                        } else if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                    }

                    Swal.fire({
                        title: "Error!",
                        text: errorMsg,
                        icon: "error"
                    });
                }
            });
        });

        // delete btn
        $(document).on('click', '.deleteBtn', function() {
            let id = $(this).data('id');

            // console.log(id);

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/master/user/delete/${id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Your data has been deleted.',
                                showConfirmButton: false,
                                timer: 1000
                            }).then(() => {
                                location.reload();
                            })
                        },
                        error: function(err) {
                            console.error("Error deleting data:", err);
                            Swal.fire(
                                'Error!',
                                'There was an error deleting the data.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // edit modal
        function editUser(userId) {
            // Ambil data user dari server
            $.ajax({
                url: `/master/user/edit/${userId}`, // atau endpoint yang sesuai
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    let user = response.data || response;

                    // Isi form dengan data user
                    $("#editId").val(user.id);
                    $("#editNamaLengkap").val(user.nama_lengkap);
                    $("#editUsername").val(user.username);
                    $("#editEmail").val(user.email);
                    $("#editNik").val(user.nik);
                    $("#editJabatan").val(user.jabatan);
                    $("#editDepartemen").val(user.departemen);
                    $("#editBagian").val(user.bagian);
                    $("#editPrincipal").val(user.principal?.principal || '-');

                    // Reset password field
                    $("#editPassword").val('');

                    // Show current image if exists
                    if (user.image) {
                        let imagePath = "{{ asset('storage') }}/" + user.image;
                        $("#currentImage").attr('src', imagePath).show();
                        $("#imagePreview").hide();
                    } else {
                        $("#currentImage").hide();
                        $("#imagePreview").hide();
                    }

                    // Reset file input
                    $("#imgEdit").val('');

                    // Show existing signature if available
                    if (user.signature && user.signature.signature) {
                        let sigPath = "{{ asset('storage') }}/" + user.signature.signature +
                            '?v=' +
                            new Date().getTime();
                        $('#edit-signature-image').attr('src', sigPath);
                        $('#edit-signature-preview').show();
                        $('#edit-signature-old').val(sigPath);
                    } else {
                        $('#edit-signature-preview').hide();
                        $('#edit-signature-old').val('');
                    }


                    // Show modal
                    $("#editUserModal").modal('show');
                },
                error: function(xhr) {
                    console.error("Error fetching user data:", xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load user data',
                    });
                }
            });
        }

        // Event handler untuk button edit (contoh penggunaan)
        $(document).on('click', '.editBtn', function() {
            let userId = $(this).data('id');
            editUser(userId);
        });

        // Reset form ketika modal ditutup
        $('#editUserModal').on('hidden.bs.modal', function() {
            $('#editUserForm')[0].reset();
            $("#imagePreview").hide();
            $("#currentImage").hide();
        });

        // edit submit
        $("#editUserForm").submit(async function(e) {
            e.preventDefault();

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
            formData.append('principal', $("#editPrincipal").val());

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

            // Tambahkan signature ke formData
            if (editSignaturePad && !editSignaturePad.isEmpty()) {
                const dataURL = editSignaturePad.toDataURL();
                const trimmedDataURL = await trimSignature(dataURL);
                formData.append('signature', trimmedDataURL || '');
            } else {
                // kirim tanda tangan lama kalau gak digambar ulang
                const oldSignature = $('#edit-signature-image').attr('src') || '';
                formData.append('signature', oldSignature);
            }

            // Laravel membutuhkan method spoofing untuk PUT
            formData.append('_method', 'PUT');

            console.log('FormData entries:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: `/master/user/update/${id}`,
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

        // Preview image sebelum upload
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

        // Klik gambar card → tampilkan preview
        $(document).on("click", ".user-img", function() {
            const imgSrc = $(this).attr("src");
            $("#imgPreview").attr("src", imgSrc);
            $("#imgPreviewOverlay").css("display", "flex").hide().fadeIn(200);
        });

        // Klik overlay → tutup preview
        $("#imgPreviewOverlay").on("click", function() {
            $(this).fadeOut(200);
        });

        // Statistik user
        $("#btnStatistikUser").on("click", function() {
            $(this).addClass("active");
            $("#grid-view-button").removeClass("active");
            $("#list-view-button").removeClass("active");

            $.ajax({
                url: "/api/dashboard/user/statistik",
                method: "GET",
                success: function(res) {
                    $("#statistikUser").show();
                    $("#userRow").empty();
                    $('#team-member-list').empty();

                    // update cards
                    $("#totalUsers").text(res.total_users);
                    $("#totalJabatan").text(res.total_jabatan);
                    $("#totalBagian").text(res.total_bagian);

                    // render charts
                    renderChartJabatan(res.by_jabatan);
                    renderChartBagian(res.by_bagian);

                    AOS.refresh();
                },
                error: function(err) {
                    console.error("Error load statistik:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load statistik data',
                    });
                }
            });
        });

        function renderChartJabatan(data) {
            let labels = data.map(item => item.jabatan ?? "Tidak Ada");
            let values = data.map(item => item.total);

            let options = {
                chart: {
                    type: 'bar',
                    height: 300
                },
                series: [{
                    name: 'Jumlah User',
                    data: values
                }],
                xaxis: {
                    categories: labels
                },
                colors: ['#1E90FF'],
                plotOptions: {
                    bar: {
                        borderRadius: 6
                    }
                }
            };

            // destroy chart lama kalau ada (biar nggak numpuk)
            if ($("#chartJabatan").data("apexchart")) {
                $("#chartJabatan").data("apexchart").destroy();
            }

            let chart = new ApexCharts(document.querySelector("#chartJabatan"), options);
            chart.render();
            $("#chartJabatan").data("apexchart", chart);
        }

        function renderChartBagian(data) {
            let labels = data.map(item => item.bagian ?? "Tidak Ada");
            let values = data.map(item => item.total);

            let options = {
                chart: {
                    type: 'donut',
                    height: 300
                },
                series: values,
                labels: labels,
                colors: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                legend: {
                    position: 'bottom'
                }
            };

            if ($("#chartBagian").data("apexchart")) {
                $("#chartBagian").data("apexchart").destroy();
            }

            let chart = new ApexCharts(document.querySelector("#chartBagian"), options);
            chart.render();
            $("#chartBagian").data("apexchart", chart);
        }


    })
</script>
@endsection