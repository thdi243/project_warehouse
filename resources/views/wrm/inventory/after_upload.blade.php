@extends('layouts.app')

@section('title', ' | Inventory Stock Location')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="mdi mdi-warehouse"></i>
                        Penentuan Lokasi Gudang
                    </h5>
                </div>

                <div class="card-body">

                    <form id="locationForm" method="POST" action="{{ route('wrm.inventory.store-upload') }}">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">

                                <thead class="table-secondary">
                                    <tr>
                                        <th>No</th>
                                        <th>Barcode</th>
                                        <th>No SPB</th>
                                        <th>MID</th>
                                        <th>Pallet ID</th>
                                        <th>Qty</th>
                                        <th>Group</th>
                                        <th>Supplier</th>
                                        <th>Status</th>
                                        <th>Pallet</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($data as $i => $row)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>{{ $row->barcode }}</td>
                                            <td>{{ $row->no_spb }}</td>
                                            <td>{{ $row->mid }}</td>
                                            <td>{{ $row->pallet_id }}</td>
                                            <td>{{ $row->qty }}</td>
                                            <td>{{ $row->group }}</td>
                                            <td>{{ $row->supplier }}</td>
                                            <td>{{ $row->status }}</td>
                                            <td>{{ $row->pallet }}</td>

                                            <td>
                                                {{-- <select name="loc_id[{{ $row->id }}]" class="form-select" required> --}}
                                                <select name="loc_id[{{ $row->id }}]" class="form-select">
                                                    <option value="">Pilih Location</option>

                                                    @foreach ($locations as $loc)
                                                        <option value="{{ $loc->id }}">
                                                            {{ $loc->plant }} - {{ $loc->s_loc }} -
                                                            {{ $loc->gudang }} - {{ $loc->bin }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i>
                                Simpan Lokasi
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $('#locationForm').on('submit', function(e) {

                e.preventDefault();

                $('select[name^="loc_id"]').each(function() {
                    if (!$(this).val()) {
                        $(this).val('1');
                    }
                });

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Sedang memproses data',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,

                    success: function(res) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message ?? 'Lokasi berhasil disimpan',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href =
                                "{{ route('wrm.inventory.index') }}";
                        });

                    },

                    error: function(xhr) {

                        Swal.close();

                        let message = 'Terjadi kesalahan pada server';

                        if (xhr.status === 422) {

                            // validation error
                            let errors = xhr.responseJSON.errors;
                            message = Object.values(errors).map(err => err[0]).join('<br>');

                        } else if (xhr.responseJSON?.message) {

                            message = xhr.responseJSON.message;

                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: message
                        });

                    }
                });

            });
        })
    </script>
@endsection
