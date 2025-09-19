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
                                <li class="breadcrumb-item active">Rack</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Data Rak</h4>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrasi">
                                <i class="mdi mdi-plus"></i> Registrasi Rack
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="nowrap table table-striped dt-responsive" id="wspRakTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Petugas</th>
                                        <th>Kode Rak</th>
                                        <th>Nama Rak</th>
                                        <th>Kolom Rak</th>
                                        <th>Level Rak</th>
                                        <th>Box Rak</th>
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

    {{-- Modal Registasi Rack --}}
    <div class="modal fade" id="modalRegistrasi" tabindex="-1" aria-labelledby="modalRegistrasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRegistrasiLabel">Registrasi Rack</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formRegistrasiRack" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-6">
                                <label for="kodeRak" class="form-label">Kode Rak</label>
                                <input type="text" class="form-control" id="kodeRak" name="kodeRak"
                                    placeholder="Cth: FL1" required>
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="namaRak" class="form-label">Nama Rak</label>
                                <input type="text" class="form-control" id="namaRak" name="namaRak"
                                    placeholder="Cth: A">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="kolomRak" class="form-label">Kolom Rak</label>
                                <input type="text" class="form-control" id="kolomRak" name="kolomRak"
                                    placeholder="Cth: 1">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="levelRak" class="form-label">Level Rak</label>
                                <input type="text" class="form-control" id="levelRak" name="levelRak"
                                    placeholder="Cth: 2">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="boxRak" class="form-label">Box Rak</label>
                                <input type="text" class="form-control" id="boxRak" name="boxRak"
                                    placeholder="Cth: 001">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit" id="simpanBtn">Simpan</button>
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal" id="cancelBtn">Cancel</button>
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
                    <h5 class="modal-title">Update Data Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="row gy-4">
                            <div class="col-xxl-3 col-md-6">
                                <label for="kodeRakEdit" class="form-label">Kode Rak</label>
                                <input type="text" class="form-control" id="kodeRakEdit" name="kodeRakEdit" required>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <label for="namaRakEdit" class="form-label">Nama Rak</label>
                                <input type="text" class="form-control" id="namaRakEdit" name="namaRakEdit">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="kolomRakEdit" class="form-label">Kolom Rak</label>
                                <input type="text" class="form-control" id="kolomRakEdit" name="kolomRakEdit">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="levelRakEdit" class="form-label">Level Rak</label>
                                <input type="text" class="form-control" id="levelRakEdit" name="levelRakEdit">
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <label for="boxRakEdit" class="form-label">Box Rak</label>
                                <input type="text" class="form-control" id="boxRakEdit" name="boxRakEdit">
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
                    <h5 class="modal-title">Detail Data Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row gy-2">
                        <div class="col-md-4">
                            <strong>MID Rak:</strong>
                            <p id="detailMid"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Nama Rak:</strong>
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
                            <strong>Qty Rak:</strong>
                            <p id="detailQty"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Petugas:</strong>
                            <p id="detailUser"></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Tanggal Transaksi:</strong>
                            <p id="detailTanggal"></p>
                        </div>
                        {{-- <div class="col-md-4">
                            <strong>Jenis Transaksi:</strong>
                            <p id="jenisTransaksi"></p>
                        </div> --}}
                        <div class="col-md-12">
                            <strong>Foto Rak:</strong>
                            <div>
                                <img id="detailImage" src="" alt="Foto Rak"
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
            // Load table
            $('#wspRakTable').DataTable({
                processing: true,
                serverSide: false,
                responsive: true,
                scrollX: true,
                ajax: {
                    url: `{{ url('api/wsp/data/all/rak') }}`,
                    type: 'GET',
                    dataSrc: ''
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1; // otomatis nomor urut
                        }
                    },
                    {
                        data: 'name',
                        render: function(data, type, row) {
                            if (!data) return '-';
                            // Capitalize setiap kata
                            return data.replace(/\b\w/g, function(l) {
                                return l.toUpperCase();
                            });
                        }
                    },
                    {
                        data: 'kode_rak',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'nama_rak',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'kolom_rak',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'level_rak',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'box_rak',
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

            // Submit registrasi rack data
            $('#formRegistrasiRack').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('wsp.store.rak') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Rack berhasil ditambahkan!'
                        });
                        $('#formRegistrasiRack')[0].reset();
                        $('#modalRegistrasi').modal('hide');
                        $('#wspRakTable').DataTable().ajax.reload();
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

            // edit button click event
            $('#wspRakTable').on('click', '.edit-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `{{ url('api/wsp/show/rak') }}/${id}`,
                    method: 'GET',
                    success: function(response) {
                        const data = response.data;

                        $('#editId').val(data.id);
                        // Isi field barang
                        $('#kodeRakEdit').val(data.kode_rak);
                        $('#namaRakEdit').val(data.nama_rak);
                        $('#kolomRakEdit').val(data.kolom_rak);
                        $('#levelRakEdit').val(data.level_rak);
                        $('#boxRakEdit').val(data.box_rak && data.box_rak ? data.box_rak : 0);

                        // buka modal
                        $('#editModal').modal('show');
                    },
                    error: function(err) {
                        console.error("Error fetching data:", err);
                        Swal.fire('Error!', 'There was an error fetching the data.', 'error');
                    }
                });
            });

            // submit form edit
            $('#editForm').submit(function(e) {
                e.preventDefault();

                const id = $('#editId').val();
                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: `{{ route('wsp.rak.update', '') }}/` + id,
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
                        $('#wspRakTable').DataTable().ajax.reload();
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

            // delete button
            $('#wspRakTable').on('click', '.delete-btn', function() {
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
                            url: `{{ route('wsp.delete.rak', '') }}/` + id,
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
                                $('#wspRakTable').DataTable().ajax.reload();
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
        })
    </script>
@endsection
