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
                            {{-- <div class="list-grid-nav hstack gap-1"> --}}
                            <button type="button" class="btn btn-success addMembers-modal" data-bs-toggle="modal"
                                data-bs-target="#addUserModal">
                                <i class="ri-add-fill me-1 align-bottom"></i> Add Users
                            </button>
                            {{-- </div> --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- card user --}}
            <div class="row" id="userRow">
                {{-- diisi ajax --}}
            </div>
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
                                    <p class="text-muted mt-2 mb-0">Klik ikon kamera untuk mengubah foto</p>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nama</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="Enter name" required />
                                    <div class="invalid-feedback">Please enter a member name.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter email" required />
                                    <div class="invalid-feedback">Please enter a valid email.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="nik" class="form-label">NIK</label>
                                    <input type="number" class="form-control" id="nik" name="nik"
                                        placeholder="Enter nik" required />
                                    <div class="invalid-feedback">Please enter nik.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Enter password" required />
                                    <div class="invalid-feedback">Please enter a password</div>
                                </div>
                                <div class="mb-3">
                                    <label for="jabatan" class="form-label">Jabatan</label>
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
                                    <label for="departemen" class="form-label">Departemen</label>
                                    <input type="text" class="form-control" id="departemen" name="departemen"
                                        value="warehouse" disabled>
                                    {{-- <div class="invalid-feedback">Please select a Departemen.</div> --}}
                                </div>

                                <div class="mb-3">
                                    <label for="bagian" class="form-label">Bagian</label>
                                    <select class="form-select" id="bagian" name="bagian" required>
                                        <option value="" disabled selected>Pilih Departemen</option>
                                        <option value="warehouse">Warehouse</option>
                                        <option value="warehouse_co_product">Warehouse Co Product</option>
                                        <option value="warehouse_finish_good">Warehouse Finish Good</option>
                                        <option value="warehouse_raw_material">Warehouse Raw Material</option>
                                        <option value="warehouse_sparepart">Warehouse Sparepart</option>
                                    </select>
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
                        </div>

                        <div class="row">
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
                        </div>

                        <div class="row">
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
                                    <label for="editDepartemen" class="form-label">Departemen</label>
                                    <select class="form-select" id="editDepartemen" name="editDepartemen" required>
                                        <option value="">Pilih Departemen</option>
                                        <option value="warehouse">Warehouse</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editBagian" class="form-label">Bagian <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="editBagian" name="editBagian" required>
                                        <option value="" disabled>Pilih Bagian</option>
                                        <option value="warehouse">Warehouse</option>
                                        <option value="warehouse_co_product">Warehouse Co Product</option>
                                        <option value="warehouse_finish_good">Warehouse Finish Good</option>
                                        <option value="warehouse_raw_material">Warehouse Raw Material</option>
                                        <option value="warehouse_sparepart">Warehouse Sparepart</option>
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

                        <!-- Image Preview -->
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Current/Preview Image</label>
                                    <div>
                                        <img id="imagePreview" src="" alt="Image Preview"
                                            style="max-width: 200px; max-height: 200px; display: none;"
                                            class="img-thumbnail">
                                        <img id="currentImage" src="" alt="Current Image"
                                            style="max-width: 200px; max-height: 200px; display: none;"
                                            class="img-thumbnail">
                                    </div>
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
                console.log("Search Text:", searchText); // Debug

                $(".team-card").each(function() {
                    let username = $(this).find(".username").text().toLowerCase().trim();
                    let jabatan = $(this).find(".badge.jabatan").text().toLowerCase().trim()
                        .replace(/_/g, ' ');
                    let bagian = $(this).find(".card-text i.bagian").parent().text().toLowerCase()
                    let nik = $(this).find(".card-text i.nik").parent().text().toLowerCase()
                    let email = $(this).find(".card-text i.email").parent().text().toLowerCase()
                        .trim();

                    username = username.trim();
                    jabatan = jabatan.trim();
                    bagian = bagian.trim();
                    nik = nik.trim();
                    email = email.trim();

                    if (username.includes(searchText) || jabatan.includes(searchText) || bagian
                        .includes(searchText) || nik.includes(searchText) || email.includes(
                            searchText)) {
                        $(this).closest(".col-md-4").show(); // pastikan elemen kolom tampil
                    } else {
                        $(this).closest(".col-md-4").hide();
                    }
                });

                AOS.refresh();
            });

            getData();

            function getData() {
                $.ajax({
                    url: "{{ route('user.getData') }}",
                    method: "GET",

                    success: function(res) {
                        res.data.forEach((user, index) => {
                            let badgeClass = '';
                            switch (user.jabatan.toLowerCase()) {
                                case 'dept_head':
                                    badgeClass = 'bg-danger';
                                    break;
                                case 'supervisor':
                                    badgeClass = 'bg-success';
                                    break;
                                case 'foreman':
                                    badgeClass = 'bg-warning';
                                    break;
                                case 'operator':
                                    badgeClass = 'bg-info';
                                    break;
                                default:
                                    badgeClass =
                                        'bg-secondary';
                            }

                            const bagianFormatted = user.bagian ?
                                user.bagian
                                .replace(/_/g, " ") // ganti semua "_" jadi spasi
                                .replace(/\b\w/g, c => c
                                    .toUpperCase()) // huruf awal jadi kapital
                                :
                                "-";
                            // kapital tiap kata

                            const imgSrc = user.image_url;
                            const delay = (index * 200) % 1000; // delay muter tiap 1000ms

                            const card = `
                                <div class="col-md-3">
                                    <div data-aos="fade-up" data-aos-delay="${delay}" data-aos-anchor-placement="top-bottom">
                                        <div class="card card-animate shadow-sm border-0 rounded-3 team-card">
                                           <img src="${imgSrc}" class="card-img-top rounded-top img-fixed user-img" alt="foto ${user.username}">
                                            <div class="card-body">
                                                <h4 class="card-title text-capitalize username">${user.username}</h4>
                                                <span class="badge ${badgeClass} px-3 py-2 mb-2 fs-7 jabatan">${user.jabatan}</span>
                                                <p class="card-text text-muted mb-1"><i class="bi bi-envelope email"></i> ${user.email}</p>
                                                <p class="card-text text-muted mb-1"><i class="bi bi-telephone nik"></i> ${user.nik}</p>
                                                <p class="card-text text-muted mb-1"><i class="bi bi-envelope bagian"></i> ${bagianFormatted}</p>
                                            </div>
                                            <div class="card-footer border-0 d-flex justify-content-between">
                                                <button class="btn btn-outline-primary btn-sm editBtn" data-id="${user.id}">Edit</button>
                                                <button class="btn btn-outline-danger btn-sm deleteBtn" data-id="${user.id}">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $("#userRow").append(card);
                        });

                        AOS.refresh();
                    },
                    error: function(err) {
                        console.error("Error load data:", err);
                    }
                });
            }

            // add users
            $('#addUserForm').submit(function(e) {
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

                var formData = new FormData(form);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                $.ajax({
                    url: "{{ route('user.store') }}",
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
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join(
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

                console.log(id);

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
                            url: "{{ route('user.delete', '') }}/" + id,
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
                    url: "{{ route('user.edit', '') }}/" + userId, // atau endpoint yang sesuai
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        let user = response.data || response;

                        // Isi form dengan data user
                        $("#editId").val(user.id);
                        $("#editUsername").val(user.username);
                        $("#editEmail").val(user.email);
                        $("#editNik").val(user.nik);
                        $("#editJabatan").val(user.jabatan);
                        $("#editDepartemen").val(user.departemen);
                        $("#editBagian").val(user.bagian);

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
            $("#editUserForm").submit(function(e) {
                e.preventDefault();

                let id = $("#editId").val();

                // Gunakan FormData untuk handle file upload
                let formData = new FormData();

                // Tambahkan data text
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

                // Laravel membutuhkan method spoofing untuk PUT
                formData.append('_method', 'PUT');

                console.log('FormData entries:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                $.ajax({
                    url: "{{ route('user.update', '') }}/" + id,
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
                            timer: 3000,
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
        })
    </script>
@endsection
