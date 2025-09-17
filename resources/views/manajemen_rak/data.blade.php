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
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Rak</a></li>
                                <li class="breadcrumb-item active">List Data</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Data Barang di Rak</h4>
                        </div>
                        <div class="card-body">
                            <table class="nowrap table table-striped dt-responsive" id="wspTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
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
                                <div>
                                    <label for="midBarang" class="form-label">MID Barang</label>
                                    <input type="text" class="form-control" id="midBarang" name="midBarang">
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="namaBarang" class="form-label">Nama Barang</label>
                                    <input type="text" class="form-control" id="namaBarang" name="namaBarang">
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="kodeRak" class="form-label">Kode Rak</label>
                                    <select name="kodeRak" id="kodeRak" class="form-select">
                                    </select>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="namaRak" class="form-label">Nama Rak</label>
                                    <select name="namaRak" id="namaRak" class="form-select">
                                    </select>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="kolomRak" class="form-label">Kolom Rak</label>
                                    <select name="kolomRak" id="kolomRak" class="form-select">

                                    </select>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="levelRak" class="form-label">Level Rak</label>
                                    <select name="levelRak" id="levelRak" class="form-select">

                                    </select>
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="boxRak" class="form-label">Box Rak</label>
                                    <input type="number" class="form-control" id="boxRak" name="boxRak">
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="qtyBarang" class="form-label">Qty Barang</label>
                                    <input type="number" class="form-control" id="qtyBarang" name="qtyBarang">
                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Foto Barang</label>
                                    <input type="file" class="form-control" id="image" name="image"
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
                                            <img id="imagePreview" src="" alt="Image Preview"
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
                            <strong>Qty Barang:</strong>
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
                    url: `{{ url('api/wsp/data') }}`,
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
                        data: 'tgl_transaksi'
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
                    // {
                    //     data: 'jenis_transaksi',
                    //     render: function(data, type, row) {
                    //         return data || '-';
                    //     }
                    // },
                    @if (Session::get('jabatan') !== 'operator')
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `
                                    <button class="btn btn-sm btn-primary edit-btn" data-id="${row.barang_id}" title="Edit Data">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${row.barang_id}" title="Delete Data">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info detail-btn" data-id="${row.barang_id}" title="Detail Data">
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

            // kode preview gambar
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

            // edit button click event
            $('#wspTable').on('click', '.edit-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `{{ url('api/wsp/show') }}/${id}`,
                    method: 'GET',
                    success: function(response) {
                        const data = response.data;

                        // Isi field barang
                        $('#editId').val(data.id);
                        $('#midBarang').val(data.mid_barang);
                        $('#namaBarang').val(data.nama_barang);
                        $('#qtyBarang').val(data.qty);
                        $('#boxRak').val(data.box_rak && data.box_rak ? data.box_rak : 0);
                        if (data.image) {
                            $('#imagePreview').attr('src',
                                    `{{ asset('storage/') }}/${data.image}`)
                                .show();
                        } else {
                            $('#imagePreview').hide();
                        }

                        $('#image').val(''); // reset input file

                        // --- Select ---
                        let kodeRakSelect = $('#kodeRak');
                        let namaRakSelect = $('#namaRak');
                        let kolomRakSelect = $('#kolomRak');
                        let levelRakSelect = $('#levelRak');

                        // Clear semua option
                        kodeRakSelect.empty().append(
                            '<option disabled>Pilih Kode Rak</option>');
                        namaRakSelect.empty().append(
                            '<option disabled>Pilih Nama Rak</option>');
                        kolomRakSelect.empty().append(
                            '<option disabled>Pilih Kolom Rak</option>');
                        levelRakSelect.empty().append(
                            '<option disabled>Pilih Level Rak</option>');

                        // Populate kode rak (selalu 3 pilihan)
                        ['FLR01', 'FLR02', 'FLR03'].forEach(kode => {
                            kodeRakSelect.append(new Option(kode, kode));
                        });

                        // Aturan nama rak & kolom sesuai kode rak
                        let namaRakList = [];
                        let maxKolom = 0;

                        if (data.kode_rak === 'FLR01') {
                            namaRakList = ['A', 'B', 'C', 'D'];
                            maxKolom = 2;
                        } else if (data.kode_rak === 'FLR02') {
                            namaRakList = ['E', 'F', 'G', 'H'];
                            maxKolom = 4;
                        } else if (data.kode_rak === 'FLR03') {
                            namaRakList = ['I', 'J', 'K', 'L'];
                            maxKolom = 4;
                        }

                        // isi nama rak
                        namaRakList.forEach(nama => {
                            namaRakSelect.append(new Option(nama, nama));
                        });

                        // isi kolom rak
                        for (let i = 1; i <= maxKolom; i++) {
                            kolomRakSelect.append(new Option(i, i));
                        }

                        // isi level rak (manual 1–7)
                        for (let i = 1; i <= 7; i++) {
                            levelRakSelect.append(new Option(i, i));
                        }

                        // --- Set value sesuai data yang ada ---
                        kodeRakSelect.val(data.kode_rak);
                        namaRakSelect.val(data.nama_rak);
                        kolomRakSelect.val(data.kolom_rak);
                        levelRakSelect.val(data.level_rak);

                        // buka modal
                        $('#editModal').modal('show');
                    },
                    error: function(err) {
                        console.error("Error fetching data:", err);
                        Swal.fire('Error!', 'There was an error fetching the data.', 'error');
                    }
                });
            });

            // handle form submit
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
                            url: `{{ url('api/wsp/delete') }}/${id}`,
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
                    url: `{{ url('api/wsp/show') }}/${id}`,
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

        });
    </script>
@endsection
