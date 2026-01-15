@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0 fw-bold">Input Data Ikat Terpal</h4>
                </div>
                <div class="card-body">
                    <form id="form-ikat-terpal" method="POST" action="{{ url('tkbm/ikat-terpal/store') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="tanggal" class="col-sm-3 col-form-label">Tanggal</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="tanggal" name="tanggal"
                                    value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                                @error('tanggal')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="qty_pallet" class="col-sm-3 col-form-label">Qty Pallet</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="qty_pallet" name="qty_pallet" min="0"
                                    step="1" value="{{ old('qty_pallet') }}" required>
                                @error('qty_pallet')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="jml_buruh" class="col-sm-3 col-form-label">Jumlah Buruh</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="jml_buruh" id="jml_buruh">
                                    <option value="" disabled selected>Pilih Jumlah Buruh</option>
                                    @for ($i = 1; $i <= 3; $i++)
                                        <option value="{{ $i }}" {{ old('jml_buruh') == $i ? 'selected' : '' }}>
                                            {{ $i }} Buruh</option>
                                    @endfor
                                </select>
                                @error('jml_buruh')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="catatan" class="col-sm-3 col-form-label">Catatan</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="catatan" name="catatan" rows="3">{{ old('catatan') }}</textarea>
                                @error('catatan')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary" id="btn-submit">
                                    Simpan Data
                                </button>
                                <button type="button" class="btn btn-secondary ms-2 btn-cancel">
                                    Batal
                                </button>
                            </div>
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

            $('#form-ikat-terpal').on('submit', function(e) {
                e.preventDefault(); // cegah submit biasa

                const $form = $(this);
                const $btn = $('#btn-submit');
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...');

                // Reset error sebelumnya
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            $form[0].reset();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message ||
                                    'Terjadi kesalahan. Silakan coba lagi.'
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;

                        if (xhr.status === 422) {
                            // Validasi Laravel error
                            $.each(response.errors, function(field, messages) {
                                const $input = $('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                $input.after('<div class="invalid-feedback d-block">' +
                                    messages.join('<br>') + '</div>');
                            });
                        } else if (response && response.message) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan. Silakan coba lagi.'
                            });
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Simpan Data');
                    }
                });
            });

            $('.btn-cancel').on('click', function() {
                // Reset form
                $('#form-ikat-terpal')[0].reset();
            });
        });
    </script>
@endsection
