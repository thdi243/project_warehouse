@extends('layouts.app')

@section('styles')
    {{-- <style>
        @media (max-width: 992px) {
            #cancelBtn {
                margin-left: 3rem !important;
            }
        }
    </style> --}}
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-4 align-items-center">
                <div class="col-8">
                    <div class="page-title d-sm-flex align-items-center justify-content-between">
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Rak</a></li>
                                <li class="breadcrumb-item active">Registrasi Barang</li>
                            </ol>
                        </div>
                    </div>
                </div>
                @if (Auth::user()->jabatan !== 'operator')
                    <div class="col-4 d-flex justify-content-end">
                        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addRackModal">
                            Add Rack
                        </a>
                    </div>
                @endif

            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Form Registrasi Barang</h4>
                            <small class="text-muted">Silakan isi data barang baru untuk dimasukkan ke rak</small>
                        </div>
                        <div class="card-body">
                            <div class="live-preview">
                                <div class="row gy-4">
                                    <div class="col-xxl-3 col-md-6">
                                        <div>
                                            <label for="user_id" class="form-label">Petugas</label>
                                            <input type="text" class="form-control" id="user_id" name="user_id"
                                                placeholder="Masukkan Nama Petugas" value="{{ Auth::user()->username }}"
                                                readonly>
                                        </div>
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
                                                <label class="form-label">Preview Image</label>
                                                <div>
                                                    <img id="imagePreview" src="" alt="Image Preview"
                                                        style="max-width: 200px; max-height: 150px; display: none;">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-lg-12">
                                        <button class="btn btn-primary" type="submit" id="simpanBtn">Simpan</button>
                                        <button class="btn btn-light" type="submit" id="cancelBtn">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal add rack --}}
    <div class="modal fade" id="addRackModal" tabindex="-1" aria-labelledby="addRackModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRackModalLabel">Add Rack Items Kode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rackForm">
                        <div id="inputContainer">
                            <label class="form-label" for="rackCode">Masukkan Kode Rak</label>
                            <div class="mb-2 input-group">
                                <input id="rackCode" name="rackKode" type="text" class="form-control"
                                    placeholder="Cth: FLR01">
                                <button type="button" class="btn btn-success addInput">+</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveRack">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // kode preview gambar add
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

            // save button
            $('#simpanBtn').click(function(e) {
                e.preventDefault();
                // Get form values
                let midBarang = $('#midBarang').val();
                let namaBarang = $('#namaBarang').val();
                let kodeRak = $('#kodeRak').val();
                let namaRak = $('#namaRak').val();
                let kolomRak = $('#kolomRak').val();
                let levelRak = $('#levelRak').val();
                let boxRak = $('#boxRak').val();
                // let qtyBarang = $('#qtyBarang').val();

                // Simple validation
                if (!midBarang || !namaBarang || !kodeRak || !namaRak || !kolomRak || !levelRak) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please fill in all required fields!',
                    });
                    return;
                }

                // Prepare data to send
                let formData = new FormData();

                formData.append('mid_barang', midBarang);
                formData.append('nama_barang', namaBarang);
                formData.append('kode_rak', kodeRak);
                formData.append('nama_rak', namaRak);
                formData.append('kolom_rak', kolomRak);
                formData.append('level_rak', levelRak);
                formData.append('box_rak', boxRak);
                // formData.append('qty', qtyBarang);
                formData.append('_token', '{{ csrf_token() }}');

                let imageFile = $('#image')[0].files[0];
                if (imageFile) {
                    formData.append('image', imageFile);
                }
                // Send data via AJAX
                $.ajax({
                    url: "{{ route('rak.store.barang') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false, // WAJIB
                    processData: false, // WAJIB
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Barang berhasil diregistrasi!',
                        });

                        // Optionally reset the form
                        $('#midBarang').val('');
                        $('#namaBarang').val('');
                        $('#kodeRak').val('');
                        $('#namaRak').val('');
                        $('#kolomRak').val('');
                        $('#levelRak').val('');
                        $('#boxRak').val('');
                        // $('#qtyBarang').val('');
                        $('#image').val('');

                        // Hide image preview
                        $('#imagePreview').hide().attr('src', '');
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;

                        if (res && res.message) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan tak terduga.',
                            });
                        }
                    }
                });
            });

            // cancel button
            $('#cancelBtn').click(function(e) {
                e.preventDefault();
                // Reset form
                // $('#petugas').val('');
                $('#namaBarang').val('');
                $('#midBarang').val('');
                $('#kodeRak').val('');
                $('#namaRak').val('');
                $('#kolomRak').val('');
                $('#levelRak').val('');
                $('#boxRak').val('');
                // $('#qty').val('');
                $('#image').val('');
                $('#imagePreview').hide().attr('src', '');
            });

            getDataRak();

            function getDataRak() {
                $.ajax({
                    url: "{{ url('api/wsp/data/rak') }}",
                    type: 'GET',
                    success: function(response) {
                        let kodeRakSelect = $('#kodeRak');
                        let namaRakSelect = $('#namaRak');
                        let kolomRakSelect = $('#kolomRak');
                        let levelRakSelect = $('#levelRak');

                        // Clear existing options
                        kodeRakSelect.empty().append(
                            '<option value="" disabled selected>Pilih Kode Rak</option>');
                        namaRakSelect.empty().append(
                            '<option value="" disabled selected>Pilih Nama Rak</option>');
                        kolomRakSelect.empty().append(
                            '<option value="" disabled selected>Pilih Kolom Rak</option>');
                        levelRakSelect.empty().append(
                            '<option value="" disabled selected>Pilih Level Rak</option>');

                        let rackData = response.data;

                        // Ambil list kode rak unik
                        let kodeRakList = [...new Set(rackData.map(item => item.kode_rak))];
                        kodeRakList.forEach(kode => {
                            kodeRakSelect.append(new Option(kode, kode));
                        });

                        // Event pilih kode rak
                        kodeRakSelect.on('change', function() {
                            let selectedKode = $(this).val();

                            // Reset dropdown lain
                            namaRakSelect.empty().append(
                                '<option value="" disabled selected>Pilih Nama Rak</option>'
                            );
                            kolomRakSelect.empty().append(
                                '<option value="" disabled selected>Pilih Kolom Rak</option>'
                            );
                            levelRakSelect.empty().append(
                                '<option value="" disabled selected>Pilih Level Rak</option>'
                            );

                            // Tentukan nama rak & kolom sesuai kode rak
                            let namaRakList = [];
                            let maxKolom = 0;

                            if (selectedKode === 'FLR01') {
                                namaRakList = ['A', 'B', 'C', 'D'];
                                maxKolom = 2;
                            } else if (selectedKode === 'FLR02') {
                                namaRakList = ['E', 'F', 'G', 'H'];
                                maxKolom = 4;
                            } else if (selectedKode === 'FLR03') {
                                namaRakList = ['I', 'J', 'K', 'L'];
                                maxKolom = 4;
                            }

                            // Populate nama rak
                            namaRakList.forEach(nama => {
                                namaRakSelect.append(new Option(nama, nama));
                            });

                            // Populate kolom rak sesuai batas
                            for (let i = 1; i <= maxKolom; i++) {
                                kolomRakSelect.append(new Option(i, i));
                            }

                            // Populate level rak langsung dari data (semua level unik untuk kode rak terpilih)
                            let rawLevels = rackData
                                .filter(item => item.kode_rak === selectedKode)
                                .map(item => parseInt(item.level_rak))
                                .filter(Boolean);

                            let levelSet = new Set(rawLevels);

                            // tambahkan level 1-7 jika belum ada
                            for (let i = 1; i <= 7; i++) {
                                levelSet.add(i);
                            }

                            let levelList = Array.from(levelSet).sort((a, b) => a - b);

                            levelList.forEach(level => {
                                levelRakSelect.append(new Option(level, level));
                            });
                        });
                    },
                    error: function(xhr) {
                        console.error('Error fetching rack data:', xhr);
                    }
                });
            }

            // Add input field dynamically
            $('#inputContainer').on('click', '.addInput', function() {
                let newInput = `
                <div class="mb-2 input-group">
                    <input type="text" class="form-control" placeholder="Cth: FLR01">
                    <button type="button" class="btn btn-danger removeInput">-</button>
                </div>`;
                $('#inputContainer').append(newInput);
            });

            // Hapus input field
            $('#inputContainer').on('click', '.removeInput', function() {
                $(this).closest('.input-group').remove();
            });

            // Submit rack data
            $('#saveRack').click(function() {
                let rackValues = [];
                $('#inputContainer input[type="text"]').each(function() {
                    let val = $(this).val().trim();
                    if (val) {
                        rackValues.push(val);
                    }
                });

                if (rackValues.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please enter at least one rack kode.'
                    });
                    return;
                }

                $.ajax({
                    url: "{{ url('api/wsp/store/rak') }}",
                    type: 'POST',
                    data: {
                        // _token: '{{ csrf_token() }}', // hapus kalau memang route API
                        kode_rak: rackValues
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Racks added successfully!'
                        });

                        // Reset form
                        $('#inputContainer').html(`
                        <div class="mb-2 input-group">
                            <input type="text" class="form-control" placeholder="Enter Rack Kode">
                            <button type="button" class="btn btn-success addInput">+</button>
                        </div>
                        `);

                        $('#addRackModal').modal('hide');
                        getDataRak();
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        let errorMsg = res && res.message ? res.message :
                            'An unexpected error occurred.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                });
            });
        })
    </script>
@endsection
