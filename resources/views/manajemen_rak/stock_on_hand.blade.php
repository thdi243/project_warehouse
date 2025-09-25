@extends('layouts.app')

@section('styles')
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
                                <li class="breadcrumb-item"><a href="javascript: void(0);">RackMan</a></li>
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Stock</a></li>
                                <li class="breadcrumb-item active">Stock on Hand</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Stock On Hand</h4>
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
                                    <i class="mdi mdi-update"></i> Update Stock
                                </button>
                            </div>
                        </div>


                        <div class="card-body">
                            <table class="nowrap table table-striped dt-responsive" id="wspTableStock" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Mid Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Stock</th>
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
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Stock Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">

                        <div class="mb-3">
                            <label class="form-label">MID Barang</label>
                            <input type="text" class="form-control" id="midBarangEdit" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" id="namaBarangEdit" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi Rak</label>
                            <input type="text" class="form-control" id="lokasiRakEdit" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Stock Fisik (On Hand)</label>
                            <input type="number" class="form-control" id="stockFisikEdit" name="stock_fisik"
                                min="0">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#wspTableStock').DataTable({
                processing: true,
                serverSide: false,
                responsive: true,
                scrollX: true,
                ajax: {
                    url: `{{ url('api/wsp/data/stock/barang') }}`,
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
                            if (!data) return '-';
                            // Capitalize setiap kata
                            return data.replace(/\b\w/g, function(l) {
                                return l.toUpperCase();
                            });
                        }
                    },
                    {
                        data: 'nama_barang',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'stock',
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
                                    <button class="btn btn-sm btn-primary edit-btn" data-id="${row.id}" title="Update Stock">
                                        <i class="mdi mdi-pencil me-2"></i>Update Stock
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

            // edit button click event
            $('#wspTableStock').on('click', '.edit-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: `{{ url('api/wsp/show/stock/barang') }}/${id}`,
                    method: 'GET',
                    success: function(response) {
                        const data = response.data;

                        $('#editId').val(data.id);
                        $('#midBarangEdit').val(data.mid_barang);
                        $('#namaBarangEdit').val(data.nama_barang);
                        $('#lokasiRakEdit').val(data.lokasi);
                        $('#stockFisikEdit').val(data.stock_fisik || 0);

                        $('#editModal').modal('show'); // tampilkan modal
                    },
                    error: function(err) {
                        console.error("Error fetching data:", err);
                        Swal.fire('Error!', 'There was an error fetching the data.', 'error');
                    }
                });
            });
        })
    </script>
@endsection
