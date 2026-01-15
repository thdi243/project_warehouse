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
                                    <option value="" selected>Pilih Jumlah Buruh</option>
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
                e.preventDefault();

                const $form = $(this);
                const $btn = $('#btn-submit'); // pastikan button punya id="btn-submit"
                const originalBtnText = $btn.text(); // simpan text asli

                // Disable button & tampilkan loading
                $btn.prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...'
                    );

                // Reset error hanya di form ini
                $form.find('.invalid-feedback').remove();
                $form.find('.is-invalid').removeClass('is-invalid');

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Data berhasil disimpan',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            $form[0].reset();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message ||
                                    'Terjadi kesalahan saat menyimpan data'
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};

                        if (xhr.status === 422) {
                            if (response.errors) {
                                $.each(response.errors, function(field, messages) {
                                    const $input = $form.find('[name="' + field + '"]');
                                    if ($input.length) {
                                        $input.addClass('is-invalid');
                                        $input.after(
                                            '<div class="invalid-feedback d-block">' +
                                            messages.join('<br>') + '</div>');
                                    }
                                });

                                const firstErrorField = Object.keys(response.errors)[0];
                                $form.find('[name="' + firstErrorField + '"]').focus();
                            }

                            if (response.message) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Perhatian',
                                    text: response.message
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error Server',
                                text: response.message ||
                                    'Terjadi kesalahan. Silakan coba lagi atau hubungi admin.'
                            });
                        }
                    },
                    complete: function() {
                        // Kembalikan button ke kondisi normal
                        $btn.prop('disabled', false)
                            .html(originalBtnText);
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
