@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Header + Breadcrumb + Action Buttons -->
            <div class="row align-items-center mb-3 g-3">
                <!-- Judul Halaman -->
                <div class="col-12 col-md-6 d-flex align-items-center">
                    <i class="mdi mdi-cube-outline text-success fs-4 me-2"></i>
                    <h4 class="fw-semibold mb-0">Data Barang</h4>
                </div>

                <!-- Tombol Aksi -->
                <div class="col-12 col-md-6">
                    <div class="d-flex flex-column flex-sm-row justify-content-md-end gap-2">
                        <button class="btn btn-success flex-fill d-flex justify-content-center align-items-center"
                            data-bs-toggle="modal" data-bs-target="#modalImport">
                            <i class="mdi mdi-database-import-outline me-1"></i> Import
                        </button>

                        <button class="btn btn-primary flex-fill d-flex justify-content-center align-items-center"
                            data-bs-toggle="modal" data-bs-target="#modalRegistrasi">
                            <i class="mdi mdi-plus me-1"></i> Tambah
                        </button>
                    </div>
                </div>

            </div>

            <!-- Card Data Barang -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="wspTable" class="table table-striped table-hover align-middle mb-0 nowrap"
                            style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Mid Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Uom</th>
                                    <th>SLoc</th>
                                    <th>Plant</th>
                                    @if (Session::get('jabatan') !== 'operator')
                                        <th class="text-center" data-orderable="false">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Diisi oleh JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Import -->
    <div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportLabel">
                        <i class="mdi mdi-upload"></i> Import / Template Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Gunakan file template resmi untuk memastikan format data sesuai sebelum melakukan import.
                    </p>

                    <!-- Form Import -->
                    <form id="formImport" action="{{ route('wsp.barang.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="fileImport" class="form-label fw-bold">Pilih File (.csv / .xlsx)</label>
                            <input type="file" class="form-control" id="fileImport" name="file" accept=".csv,.xlsx"
                                required>
                        </div>

                        <!-- Tombol Download & Upload sebaris -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('wsp.barang.download.template') }}" class="btn btn-outline-info w-50 me-2">
                                <i class="mdi mdi-download"></i> Download Template
                            </a>
                            <button type="submit" id="btnUpload" class="btn btn-success w-50">
                                <i class="mdi mdi-upload"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Registasi Barang --}}
    <div class="modal fade" id="modalRegistrasi" tabindex="-1" aria-labelledby="modalRegistrasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRegistrasiLabel"> <i class="mdi mdi-plus-circle me-2"></i>Registrasi
                        Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formRegistrasiBarang" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-xxl-4 col-md-6">
                                <label for="mid_barang" class="form-label">MID Barang</label>
                                <input type="number" class="form-control" id="mid_barang" name="mid_barang">
                            </div>
                            <div class="col-xxl-4 col-md-6">
                                <label for="nama_barang" class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang">
                            </div>
                            <div class="col-xxl-4 col-md-6">
                                <label for="uom" class="form-label">Uom</label>
                                <input type="text" class="form-control" id="uom" name="uom">
                            </div>
                            <div class="col-xxl-4 col-md-6">
                                <label for="s_loc" class="form-label">Storage Location</label>
                                <input type="text" class="form-control" id="s_loc" name="s_loc">
                            </div>
                            <div class="col-xxl-4 col-md-6">
                                <label for="plant" class="form-label">Plant</label>
                                <input type="text" class="form-control" id="plant" name="plant">
                            </div>
                            <div class="col-xxl-6 col-md-6">
                                <label for="image" class="form-label">Foto Barang (Opsional)</label>
                                <input type="file" class="form-control" id="image" name="image"
                                    accept=".jpeg,.jpg,.png,.gif,.svg">
                                <small class="form-text text-muted">File types: jpeg, jpg, png. Max size: 2MB</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Preview Image</label>
                                <div>
                                    <img id="imagePreview" src="" alt="Image Preview"
                                        style="max-width: 200px; max-height: 150px; display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Simpan</button>
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- modal edit --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Data Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-6">
                                <label for="midBarangEdit" class="form-label">MID Barang</label>
                                <input type="text" class="form-control" id="midBarangEdit" name="midBarangEdit">

                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="namaBarangEdit" class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" id="namaBarangEdit" name="namaBarangEdit">
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="uomEdit" class="form-label">Uom</label>
                                <input type="text" class="form-control" id="uomEdit" name="uomEdit">
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="sLocEdit" class="form-label">Storage Location</label>
                                <input type="text" class="form-control" id="sLocEdit" name="sLocEdit">
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="plantEdit" class="form-label">Plant</label>
                                <input type="text" class="form-control" id="plantEdit" name="plantEdit">
                            </div>
                            <div class="col-xxl-6 col-md-6">
                                <div class="mb-3">
                                    <label for="imageEdit" class="form-label">Foto Barang</label>
                                    <input type="file" class="form-control" id="imageEdit" name="imageEdit"
                                        accept=".jpeg,.jpg,.png,.gif,.svg">
                                    <small class="form-text text-muted">File types: jpeg, jpg, png. Max
                                        size:
                                        2MB</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Current/Preview Image</label>
                                        <div>
                                            <img id="imagePreviewEdit" src="" alt="Image Preview"
                                                style="max-width: 200px; max-height: 150px; display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal detail --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Data Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-2">
                        <div class="col-md-4">
                            <strong>MID Barang:</strong>
                            <p id="detailMid"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Nama Barang:</strong>
                            <p id="detailNama"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Uom:</strong>
                            <p id="detailUom"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>SLoc:</strong>
                            <p id="detailSLoc"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Plant:</strong>
                            <p id="detailPlant"></p>
                        </div>
                        <div class="col-md-12">
                            <strong>Foto Barang:</strong>
                            <div>
                                <img id="detailImage" src="" alt="Foto Barang"
                                    style="max-width: 200px; max-height: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#wspTable').DataTable({
                processing: true,
                serverSide: false,
                responsive: true,
                scrollX: true,
                ajax: {
                    url: `{{ url('api/wsp/data/barang') }}`,
                    type: 'GET',
                    dataSrc: 'data'
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1; // otomatis nomor urut
                        }
                    },
                    {
                        data: 'mid_barang',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'nama_barang',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'uom',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 's_loc',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'plant',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    @if (Session::get('jabatan') !== 'operator')
                        {
                            data: null,
                            orderable: false,
                            className: 'text-center',
                            render: function(data, type, row) {
                                return `
                                    <button class="btn btn-sm btn-primary detail-btn" data-id="${row.id}" title="Detail Data">
                                        <i class="mdi mdi-eye me-2"></i>Detail
                                    </button>
                                    <button class="btn btn-sm btn-info edit-btn" data-id="${row.id}" title="Edit Data">
                                        <i class="mdi mdi-pencil me-2"></i>Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Delete Data">
                                        <i class="mdi mdi-delete me-2"></i>Delete
                                    </button>
                                `;
                            }
                        }
                    @endif
                ],
                order: [
                    [0, 'asc']
                ],
                language: {
                    lengthMenu: "Show _MENU_ entries",
                }
            });

            // kode preview gambar registrasi
            $("#image").change(function() {
                let file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $("#imagePreview")
                            .attr("src", e.target.result)
                            .show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $("#imagePreview").hide().attr("src", "");
                }
            });

            // kode preview gambar edit
            $("#imageEdit").change(function() {
                let file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $("#imagePreviewEdit")
                            .attr("src", e.target.result)
                            .show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $("#imagePreviewEdit").hide().attr("src", "");
                }
            });

            // Handle submit form registrasi barang
            $('#formRegistrasiBarang').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('wsp.store.barang') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        // Optional: ubah tombol jadi loading
                        form.find('button[type="submit"]')
                            .prop('disabled', true)
                            .html('<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...');
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: response.message || 'Barang berhasil ditambahkan.'
                        });

                        // Reset form dan tampilan
                        form[0].reset();
                        form.find('input[type="file"]').val('');
                        $('#imagePreview').hide().attr('src', '');
                        $('#modalRegistrasi').modal('hide');
                        $('#wspTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        let errorMsg = 'Terjadi kesalahan tak terduga.';
                        let icon = 'error';
                        let title = 'Error';

                        if (xhr.status === 422 && res?.errors) {
                            // Validation error
                            errorMsg = Object.values(res.errors).flat().join('<br>');
                            icon = 'warning';
                            title = 'Perhatian!';
                        } else if (res?.message) {
                            errorMsg = res.message;
                        }

                        Swal.fire({
                            icon: icon,
                            title: title,
                            html: errorMsg
                        });
                    },
                    complete: function() {
                        // Balikin tombol ke kondisi semula
                        form.find('button[type="submit"]')
                            .prop('disabled', false)
                            .html('Simpan');
                    }
                });
            });

            // edit button click event
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');

                // Misalnya buka modal edit dan load data dari backend
                $.ajax({
                    url: `{{ url('api/wsp/show/barang') }}/${id}`, // sesuaikan dengan route kamu
                    type: 'GET',
                    success: function(res) {
                        // tampilkan datanya di modal form
                        $('#editModal').modal('show');
                        $('#editId').val(res.data.id);
                        $('#midBarangEdit').val(res.data.mid_barang);
                        $('#namaBarangEdit').val(res.data.nama_barang);
                        $('#uomEdit').val(res.data.uom);
                        $('#sLocEdit').val(res.data.s_loc);
                        $('#plantEdit').val(res.data.plant);
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memuat data barang');
                    }
                });
            });

            // handle form edit submit
            $('#editForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#editId').val();
                const formData = new FormData(this);

                $.ajax({
                    url: `{{ route('wsp.update.barang', '') }}/` + id,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-HTTP-Method-Override': 'PUT' // penting untuk Laravel agar dikenali sebagai PUT
                    },
                    success: function(res) {
                        toastr.success(res.message || 'Data barang berhasil diperbarui');
                        $('#editModal').modal('hide');
                        $('#editForm')[0].reset();
                        $('#wspTable').DataTable().ajax.reload();
                    },
                    error: function(err) {
                        let errorMsg = 'There was an error updating the data.';
                        if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire('Error!', errorMsg, 'error');
                    }
                });
            });

            // delete button click event
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('api/wsp/delete/barang') }}/${id}`, // sesuaikan dengan route kamu
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr(
                                    'content')
                            },
                            success: function(res) {
                                toastr.success(res.message || 'Data berhasil dihapus');
                                $('#wspTable').DataTable().ajax.reload(null,
                                    false);
                            },
                            error: function(xhr) {
                                toastr.error('Gagal menghapus data');
                            }
                        });
                    }
                });
            });

            // Tombol Upload diklik
            $('#btnUpload').on('click', function(e) {
                e.preventDefault();

                let form = $('#formImport')[0];
                let fileInput = $('#fileImport').val();

                if (!fileInput) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File belum dipilih!',
                        text: 'Silakan pilih file terlebih dahulu sebelum mengunggah.'
                    });
                    return;
                }

                let formData = new FormData(form);

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#btnUpload')
                            .prop('disabled', true)
                            .html(
                                '<i class="mdi mdi-loading mdi-spin"></i> Mengunggah...'
                            );
                    },
                    success: function(response) {
                        const {
                            status,
                            message,
                            errors = []
                        } = response;

                        if (status === true) {
                            // Sukses total (errors kosong)
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: message || 'Import selesai tanpa error.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            // Ada error (atau partial)
                            let errorListHtml = errors.length > 0 ?
                                errors.map(err => {
                                    // Sesuaikan format error dari WSP (baris + error)
                                    if (typeof err === 'object') {
                                        return `<li>Baris ${err.baris}: ${err.error}</li>`;
                                    }
                                    return `<li>${err}</li>`;
                                }).join('') :
                                '<li>Tidak ada rincian error.</li>';

                            Swal.fire({
                                icon: 'warning',
                                title: 'Sebagian Gagal!',
                                html: `
                                    <p>${message || 'Import selesai dengan beberapa error.'}</p>
                                    <hr>
                                    <ul style="text-align:left; max-height: 200px; overflow-y: auto;">${errorListHtml}</ul>
                                `,
                                width: 600
                            });
                        }

                        // Reset & reload table
                        $('#modalImport').modal('hide');
                        $('#formImport')[0].reset();
                        $('#wspTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat import.'
                        });
                    },
                    complete: function() {
                        $('#btnUpload')
                            .prop('disabled', false)
                            .html('<i class="mdi mdi-upload"></i> Upload');
                    }
                });
            });

            $(document).on('click', '.detail-btn', function() {
                const id = $(this).data('id');

                // Misalnya buka modal edit dan load data dari backend
                $.ajax({
                    url: `{{ url('api/wsp/show/barang') }}/${id}`, // sesuaikan dengan route kamu
                    type: 'GET',
                    success: function(res) {
                        // tampilkan datanya di modal form
                        $('#detailModal').modal('show');
                        $('#detailMid').text(res.data.mid_barang);
                        $('#detailNama').text(res.data.nama_barang);
                        $('#detailUom').text(res.data.uom);
                        $('#detailSLoc').text(res.data.s_loc);
                        $('#detailPlant').text(res.data.plant);
                        if (res.data.image) {
                            $('#detailImage')
                                .attr('src', `{{ asset('storage/') }}/${res.data.image}`)
                                .show();
                        } else {
                            $('#detailImage').hide();
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memuat data barang');
                    }
                });
            });
        });
    </script>
@endsection
