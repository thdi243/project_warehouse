@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="page-title d-sm-flex align-items-center justify-content-between">
                        {{-- <h4 class="mb-sm-0">Form Input TKBM</h4> --}}

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">RackMan</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Master</a></li>
                                <li class="breadcrumb-item active">Barang</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Data Barang</h4>
                            <div>
                                {{-- btn download template --}}
                                <a href="{{ route('wsp.barang.download.template') }}" class="btn btn-info me-2">
                                    <i class="mdi mdi-download"></i> Download Template
                                </a>

                                <!-- Tombol Import -->
                                <form id="formImport" action="{{ route('wsp.barang.import') }}" method="POST"
                                    enctype="multipart/form-data" class="d-inline me-2">
                                    @csrf
                                    <input type="file" name="file" id="fileImport" accept=".csv, .xlsx"
                                        style="display: none;">
                                    <button type="button" class="btn btn-success" id="btnImport">
                                        <i class="mdi mdi-upload"></i> Import File
                                    </button>
                                </form>

                                <!-- Tombol Registrasi -->
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrasi">
                                    <i class="mdi mdi-plus"></i> Registrasi Barang
                                </button>
                            </div>
                        </div>


                        <div class="card-body">
                            <table class="nowrap table table-striped dt-responsive" id="wspTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Petugas</th>
                                        <th>Mid Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Lokasi</th>
                                        {{-- <th>Jenis Transaksi</th> --}}
                                        @if (Session::get('jabatan') !== 'operator')
                                            <th data-orderable="false">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Di isi oleh js --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Registasi Barang --}}
    <div class="modal fade" id="modalRegistrasi" tabindex="-1" aria-labelledby="modalRegistrasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRegistrasiLabel">Registrasi Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formRegistrasiBarang" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-6">
                                <label for="user_id" class="form-label">Petugas</label>
                                <input type="text" class="form-control" id="user_id" name="user_id"
                                    value="{{ Auth::user()->username }}" readonly>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="mid_barang" class="form-label">MID Barang</label>
                                <input type="number" class="form-control" id="mid_barang" name="mid_barang">
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="nama_barang" class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang">
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="kode_rak" class="form-label">Kode Rak</label>
                                <select name="kode_rak" id="kode_rak" class="form-select">
                                    {{-- <option value="" disabled selected>Pilih kode rak</option>
                                    <option value="FL1">FL1</option>
                                    <option value="FL2">FL2</option>
                                    <option value="FL3">FL3</option> --}}
                                </select>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="nama_rak" class="form-label">Nama Rak</label>
                                <select name="nama_rak" id="nama_rak" class="form-select"></select>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="kolom_rak" class="form-label">Kolom Rak</label>
                                <select name="kolom_rak" id="kolom_rak" class="form-select"></select>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="level_rak" class="form-label">Level Rak</label>
                                <select name="level_rak" id="level_rak" class="form-select"></select>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="box_rak" class="form-label">Box Rak</label>
                                <input type="number" class="form-control" id="box_rak" name="box_rak">
                            </div>
                            <div class="col-xxl-6 col-md-6">
                                <label for="image" class="form-label">Foto Barang</label>
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
                                <label for="user_id" class="form-label">Petugas</label>
                                <input type="text" class="form-control" id="user_id" name="user_id"
                                    placeholder="Masukkan Nama Petugas" value="{{ Auth::user()->username }}" readonly>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="midBarangEdit" class="form-label">MID Barang</label>
                                <input type="text" class="form-control" id="midBarangEdit" name="midBarangEdit">

                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="namaBarangEdit" class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" id="namaBarangEdit" name="namaBarangEdit">
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="kodeRakEdit" class="form-label">Kode Rak</label>
                                <select class="form-select" id="kodeRakEdit" name="kodeRakEdit" required></select>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="namaRakEdit" class="form-label">Nama Rak</label>
                                <select class="form-select" id="namaRakEdit" name="namaRakEdit"></select>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="kolomRakEdit" class="form-label">Kolom Rak</label>
                                <select class="form-select" id="kolomRakEdit" name="kolomRakEdit"></select>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="levelRakEdit" class="form-label">Level Rak</label>
                                <select class="form-select" id="levelRakEdit" name="levelRakEdit"></select>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="boxRakEdit" class="form-label">Box Rak</label>
                                <input type="text" class="form-control" id="boxRakEdit" name="boxRakEdit">
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
                            <strong>Kode Rak:</strong>
                            <p id="detailKodeRak"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Nama Rak:</strong>
                            <p id="detailNamaRak"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Kolom Rak:</strong>
                            <p id="detailKolomRak"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Level Rak:</strong>
                            <p id="detailLevelRak"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Box Rak:</strong>
                            <p id="detailBoxRak"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Petugas:</strong>
                            <p id="detailUser"></p>
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
                        data: 'username',
                        render: function(data, type, row) {
                            if (!data) return '-';
                            // Capitalize setiap kata
                            return data.replace(/\b\w/g, function(l) {
                                return l.toUpperCase();
                            });
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
                        data: 'lokasi',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    @if (Session::get('jabatan') !== 'operator')
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <button class="btn btn-sm btn-primary edit-btn" data-id="${row.id}" title="Edit Data">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Delete Data">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info detail-btn" data-id="${row.id}" title="Detail Data">
                                        <i class="mdi mdi-eye"></i>
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

            // registrasi btn modal
            $('#modalRegistrasi').on('shown.bs.modal', function() {
                let kodeRakSelect = $("#kode_rak");
                kodeRakSelect.empty().append('<option value="">Pilih Kode Rak</option>');

                fetch("{{ url('/api/wsp/data/rak') }}")
                    .then(response => response.json())
                    .then(res => {
                        if (res.status === 'empty') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Data Rak Kosong',
                                text: res.message,
                            });
                        } else {
                            res.data.forEach(kode => {
                                kodeRakSelect.append(
                                    `<option value="${kode}">${kode}</option>`);
                            });
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching kode rak:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal mengambil data rak dari server.',
                        });
                    });
            });

            const rakData = {
                FL1: {
                    nama_rak: ["A", "B", "C", "D", "E", "F", "G"],
                    kolom_rak: [1, 2, 3, 4]
                },
                FL2: {
                    nama_rak: ["H", "I", "J", "K"],
                    kolom_rak: [1, 2]
                },
                FL3: {
                    nama_rak: ["L", "M", "N", "O"],
                    kolom_rak: [1, 2, 3, 4]
                }
            };
            const levelRak = [1, 2, 3, 4, 5, 6, 7];

            // ketika kode rak berubah
            $("#kode_rak").on("change", function() {
                let kode = $(this).val();

                // reset dulu semua
                $("#nama_rak").empty();
                $("#kolom_rak").empty();
                $("#level_rak").empty();

                if (kode && rakData[kode]) {
                    // isi nama rak
                    rakData[kode].nama_rak.forEach(function(nama) {
                        $("#nama_rak").append(`<option value="${nama}">${nama}</option>`);
                    });

                    // isi kolom rak
                    rakData[kode].kolom_rak.forEach(function(kolom) {
                        $("#kolom_rak").append(`<option value="${kolom}">${kolom}</option>`);
                    });

                    // isi level rak (selalu 1-7)
                    levelRak.forEach(function(lvl) {
                        $("#level_rak").append(`<option value="${lvl}">${lvl}</option>`);
                    });
                }
            });

            // save button
            $('#formRegistrasiBarang').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $.ajax({
                    url: "{{ route('wsp.store.barang') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            html: response.message || 'Barang berhasil ditambahkan!'
                        });

                        $('#formRegistrasiBarang')[0].reset();
                        $('#kode_rak').val('');
                        $('#nama_rak').val('');
                        $('#kolom_rak').val('');
                        $('#level_rak').val('');
                        $('#box_rak').val('');
                        $('#formRegistrasiBarang').find('input[type="file"]').val('');
                        // $('#formRegistrasiBarang select').prop('selectedIndex', 0);
                        $('#imagePreview').hide().attr('src', '');
                        $('#modalRegistrasi').modal('hide');
                        $('#wspTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        let errorMsg = 'Terjadi kesalahan tak terduga.';
                        let icon = 'error';

                        if (res && res.message) {
                            errorMsg = res.message;
                            // Kalau status 422 (validation/duplicate), pakai warning
                            if (xhr.status === 422) {
                                icon = 'warning';
                            }
                        } else if (res && res.errors) {
                            errorMsg = Object.values(res.errors).flat().join('\n');
                            icon = 'warning';
                        }

                        Swal.fire({
                            icon: icon,
                            title: xhr.status === 422 ? 'Perhatian' : 'Error',
                            text: errorMsg
                        });
                    }
                });
            });
            // end registrasi btn modal

            // edit button click event
            $('#wspTable').on('click', '.edit-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `{{ url('api/wsp/show/barang') }}/${id}`,
                    method: 'GET',
                    success: function(response) {
                        const data = response.data;

                        $('#editId').val(data.id);
                        $('#midBarangEdit').val(data.mid_barang);
                        $('#namaBarangEdit').val(data.nama_barang);
                        $('#boxRakEdit').val(data.box_rak || 0);

                        if (data.image) {
                            $('#imagePreviewEdit')
                                .attr('src', `{{ asset('storage') }}/${data.image}`)
                                .show();
                        }

                        // Ambil list kode_rak dari backend
                        let kodeRakSelect = $("#kodeRakEdit");
                        kodeRakSelect.empty().append(
                            '<option value="" disabled>Pilih Kode Rak</option>');

                        fetch("{{ url('/api/wsp/data/rak') }}")
                            .then(res => res.json())
                            .then(resData => {
                                if (resData.status === 'empty') {
                                    Swal.fire('Warning', 'Data Rak Kosong', 'warning');
                                } else {
                                    resData.data.forEach(kode => {
                                        kodeRakSelect.append(
                                            `<option value="${kode}">${kode}</option>`
                                        );
                                    });

                                    // Prefill kode rak sesuai data barang
                                    $('#kodeRakEdit').val(data.kode_rak).trigger('change');

                                    // Setelah trigger change, set namaRakEdit, kolomRakEdit, levelRakEdit
                                    $('#namaRakEdit').val(data.nama_rak);
                                    $('#kolomRakEdit').val(data.kolom_rak);
                                    $('#levelRakEdit').val(data.level_rak);
                                }

                                // buka modal setelah semua selesai
                                $('#editModal').modal('show');
                            })
                            .catch(err => {
                                console.error("Error fetching kode rak:", err);
                                Swal.fire('Error', 'Gagal mengambil data rak dari server',
                                    'error');
                            });
                    },
                    error: function(err) {
                        console.error("Error fetching data:", err);
                        Swal.fire('Error!', 'There was an error fetching the data.', 'error');
                    }
                });
            });

            // ketika kode rak berubah di modal edit
            $("#kodeRakEdit").on("change", function() {
                let kode = $(this).val();

                $("#namaRakEdit").empty();
                $("#kolomRakEdit").empty();
                $("#levelRakEdit").empty();

                if (kode && rakData[kode]) {
                    rakData[kode].nama_rak.forEach(nama => {
                        $("#namaRakEdit").append(`<option value="${nama}">${nama}</option>`);
                    });
                    rakData[kode].kolom_rak.forEach(kolom => {
                        $("#kolomRakEdit").append(`<option value="${kolom}">${kolom}</option>`);
                    });
                    levelRak.forEach(lvl => {
                        $("#levelRakEdit").append(`<option value="${lvl}">${lvl}</option>`);
                    });
                }
            });


            // handle form edit submit
            $('#editForm').submit(function(e) {
                e.preventDefault();

                const id = $('#editId').val();

                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: `{{ route('wsp.update.barang', '') }}/` + id,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Success!', 'Data updated successfully.', 'success');
                        $('#editModal').modal('hide');
                        $('#wspTable').DataTable().ajax.reload();
                    },
                    error: function(err) {
                        console.error("Error updating data:", err);
                        let errorMsg = 'There was an error updating the data.';
                        if (err.responseJSON && err.responseJSON.message) {
                            errorMsg = err.responseJSON.message;
                        }
                        Swal.fire('Error!', errorMsg, 'error');
                    }
                });
            });

            // delete button click event
            $('#wspTable').on('click', '.delete-btn', function() {
                const id = $(this).data('id');
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
                            url: `{{ url('api/wsp/delete/barang') }}/${id}`,
                            // url: `/data/tkbm/delete/${id}`,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                );
                                $('#wspTable').DataTable().ajax.reload();
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

            // detail button click event
            $('#wspTable').on('click', '.detail-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `{{ url('api/wsp/show/barang') }}/${id}`,
                    method: 'GET',
                    success: function(response) {
                        const data = response.data;

                        $('#detailMid').text(data.mid_barang);
                        $('#detailNama').text(data.nama_barang);
                        $('#detailKodeRak').text(data.kode_rak);
                        $('#detailNamaRak').text(data.nama_rak);
                        $('#detailKolomRak').text(data.kolom_rak);
                        $('#detailLevelRak').text(data.level_rak);
                        $('#detailBoxRak').text(data.box_rak ?? '0');
                        $('#detailQty').text(data.qty);
                        $('#detailUser').text(
                            data.username ?
                            data.username.replace(/\b\w/g, function(l) {
                                return l.toUpperCase();
                            }) :
                            '-'
                        );
                        $('#detailTanggal').text(data.tgl_transaksi);
                        // $('#jenisTransaksi').text(data.jenis_transaksi);

                        if (data.image) {
                            $('#detailImage')
                                .attr('src', `{{ asset('storage/') }}/${data.image}`)
                                .show();
                        } else {
                            $('#detailImage').hide();
                        }

                        $('#detailModal').modal('show');
                    },
                    error: function(err) {
                        console.error("Error fetching detail:", err);
                        Swal.fire('Error!', 'Gagal mengambil detail data.', 'error');
                    }
                });
            });

            //////// import /////////
            // Klik tombol import -> trigger input file
            $('#btnImport').click(function() {
                $('#fileImport').click();
            });

            // Setelah pilih file -> submit form via AJAX
            $('#fileImport').change(function() {
                var formData = new FormData($('#formImport')[0]);

                $.ajax({
                    url: $('#formImport').attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'File berhasil diimport.'
                        });

                        $('#wspTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat import.'
                        });
                    }
                });
            });
        });
    </script>
@endsection
