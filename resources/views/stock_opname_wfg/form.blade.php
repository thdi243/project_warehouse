@extends('layouts.app')

@section('styles')
    <style>
        :root {
            --primary-color: #f96060;
            --primary-dark: #4840a6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        .page-header {
            background: linear-gradient(135deg, #f96060 0%, #e37220 100%);
            color: white;
            padding: 2rem 0;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 10px 30px rgba(229, 57, 53, 0.2);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #c03e3e;
            border-color: var(--primary-color);
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {

            font-size: 0.875rem;
            line-height: 1.5;
            color: #6c757d;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            font-size: 0.875rem;
            line-height: 1.5;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header mb-4" data-aos="fade-left">
                <div class="container-fluid">
                    <h1 class="h2 fw-bold mb-2 text-white">
                        <i class="mdi mdi-package-variant-closed me-2"></i>
                        Form Stock Opname WFG
                    </h1>
                    <p class="mb-0 opacity-90">Input stock opname agar data tetap actual</p>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body p-4">
                    <form id="formSop">
                        @csrf

                        <!-- SECTION: Input Utama -->
                        <div class="row g-4 d-lg-flex align-items-lg-stretch">

                            <div class="col-lg-6">
                                <div class="card border-0 bg-light-subtle p-3 rounded-3 h-lg-100">
                                    <h6 class="fw-bold mb-3 text-secondary">
                                        <i class="mdi mdi-clipboard-list-outline me-1"></i> Informasi Barang
                                    </h6>

                                    <div class="mb-3">
                                        <label for="mid" class="form-label fw-semibold">MID Barang</label>
                                        <select id="mid" name="mid" class="form-select" required></select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tgl_opname" class="form-label fw-semibold">Tanggal Opname</label>
                                        <input type="date" id="tgl_opname" name="tgl_opname" class="form-control"
                                            required>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="qty_box" class="form-label fw-semibold">Qty / Box</label>
                                            <input type="text" id="qty_box" class="form-control text-muted" readonly
                                                placeholder="—">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="qty_soh" class="form-label fw-semibold">Qty SOH</label>
                                            <input type="text" id="qty_soh" class="form-control text-muted" readonly
                                                placeholder="—">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card border-0 bg-light-subtle p-3 rounded-3 h-lg-100 d-lg-flex flex-column">
                                    <h6 class="fw-bold mb-3 text-secondary">
                                        <i class="mdi mdi-note-text-outline me-1"></i> Catatan Opname
                                    </h6>

                                    <div class="flex-grow-1 mb-3">
                                        <label for="keterangan" class="form-label fw-semibold">
                                            Keterangan
                                            <small class="text-primary">(Wajib diisi jika ada selisih)</small>
                                        </label>
                                        <textarea id="keterangan" name="keterangan" class="form-control h-lg-100" rows="6"
                                            placeholder="Tulis keterangan untuk opname ini..."></textarea>
                                    </div>

                                    <div class="mt-lg-auto d-flex gap-3 pt-2 border-top text-nowrap">
                                        <div class="w-50">
                                            <button type="button" class="btn btn-info px-4 w-100" id="btnAddRow">
                                                <i class="mdi mdi-plus-thick me-1"></i> Tambah Baris
                                            </button>
                                        </div>
                                        <div class="w-50">
                                            <button type="button" class="btn btn-success px-4 w-100" id="btnSubmit">
                                                <i class="mdi mdi-content-save-outline me-1"></i> Simpan Opname
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- SECTION: Tabel Input -->
                        <hr class="my-4">

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle table-hover mb-0" id="tableInput">
                                <thead class="table-light text-center align-middle">
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 40%">Qty Full</th>
                                        <th style="width: 40%">Qty Receh</th>
                                        <th style="width: 15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center fw-semibold">1</td>
                                        <td>
                                            <input type="number" class="form-control qty_full" name="qty_full[]"
                                                min="0" placeholder="Masukkan qty full" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control qty_receh" name="qty_receh[]"
                                                min="0" placeholder="Masukkan qty receh" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-soft-danger btnRemoveRow text-nowrap">
                                                <i class="mdi mdi-delete me-2"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
            // Inisialisasi Select2 untuk MID Barang
            $('#mid').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih MID Barang...',
                width: '100%',
                ajax: {
                    url: `{{ url('api/wfg/sop/getBarang') }}`,
                    dataType: 'json',
                    delay: 250,

                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(res) {
                        return {
                            results: res.data.map(item => ({
                                id: item.mid_barang,
                                text: item.mid_barang + ' - ' + item.nama_barang,
                                data: item
                            }))
                        };
                    },
                    cache: true
                }
            });

            $('#mid').on('select2:select', function(e) {
                const selectedData = e.params.data.data;
                $('#qty_box').val(selectedData.qty_box ?? '—');

                const qtySoh = selectedData.stock_on_hand?.qty_soh ?? 0;
                $('#qty_soh').val(qtySoh);
            });

            // Tambah baris input
            $("#btnAddRow").on("click", function() {
                let rowCount = $("#tableInput tbody tr").length + 1;
                let newRow = `
                    <tr>
                        <td class="text-center">${rowCount}</td>
                        <td><input type="number" class="form-control qty_full" name="qty_full[]" min="0" required placeholder="Masukkan qty full"></td>
                        <td><input type="number" class="form-control qty_receh" name="qty_receh[]" min="0" required placeholder="Masukkan qty receh"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-soft-danger btnRemoveRow text-nowrap">
                                <i class="mdi mdi-delete me-2"></i>Delete
                            </button>
                        </td>
                    </tr>
                `;
                $("#tableInput tbody").append(newRow);
            });

            // Hapus baris input
            $(document).on("click", ".btnRemoveRow", function() {
                $(this).closest("tr").remove();
                $("#tableInput tbody tr").each(function(index) {
                    $(this).find("td:first").text(index + 1);
                });
            });

            // Submit data ke server
            $("#btnSubmit").on("click", function() {

                let formData = $("#formSop").serialize();

                $.ajax({
                    url: "{{ route('wfg.stock_opname.store') }}",
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $("#btnSubmit").prop("disabled", true).html(
                            '<span class="spinner-border spinner-border-sm"></span> Menyimpan...'
                        );
                    },
                    success: function(response) {
                        $("#btnSubmit").prop("disabled", false).html(
                            '<i class="mdi mdi-content-save-outline me-1"></i> Simpan Opname'
                        );

                        if (response.status === "success") {
                            // ... (Kode Success: Swal.fire & Reset Form) ...
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });

                            $("#formSop")[0].reset();
                            $("#mid").val(null).trigger('change');
                            // ... (Reset table rows) ...
                            $("#tableInput tbody").html(`
                                <tr>
                                    <td class="text-center fw-semibold">1</td>
                                    <td><input type="number" class="form-control qty_full" name="qty_full[]" min="0" required placeholder="Masukkan qty full"></td>
                                    <td><input type="number" class="form-control qty_receh" name="qty_receh[]" min="0" required placeholder="Masukkan qty receh"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-soft-danger btnRemoveRow text-nowrap">
                                            <i class="mdi mdi-delete me-2"></i>Delete
                                        </button>
                                    </td>
                                </tr>
                            `);

                        } else if (response.status === "selisih") {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Wajib Diisi!',
                                text: response.message ||
                                    'Keterangan wajib diisi jika ada selisih.',
                                confirmButtonText: "Mengerti"
                            });
                            $("#keterangan").focus();
                        } else if (response.status === "warning") {
                            Swal.fire({
                                icon: "warning",
                                title: "Data Sudah Ada",
                                text: response.message,
                                showCancelButton: true,
                                confirmButtonText: "Lanjutkan & Timpa Data",
                                cancelButtonText: "Batal",
                                confirmButtonColor: "#556ee6",
                                cancelButtonColor: "#d33",
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // ... (AJAX PUT Request untuk Update) ...
                                    let formData = $("#formSop").serialize();
                                    $.ajax({
                                        url: "{{ route('wfg.stock_opname.update', '') }}/" +
                                            response.sop_id,
                                        type: "PUT",
                                        data: formData,
                                        success: function(res2) {
                                            if (res2.status === "success") {
                                                Swal.fire({
                                                    icon: "success",
                                                    title: "Berhasil!",
                                                    text: res2
                                                        .message,
                                                    showConfirmButton: false,
                                                    timer: 1500
                                                });
                                                $("#formSop")[0].reset();
                                                $("#mid").val(null).trigger(
                                                    "change");
                                                // ... (Reset table rows setelah update) ...
                                                $("#tableInput tbody").html(`
                                                    <tr>
                                                        <td class="text-center fw-semibold">1</td>
                                                        <td><input type="number" class="form-control qty_full" name="qty_full[]" min="0" required placeholder="Masukkan qty full"></td>
                                                        <td><input type="number" class="form-control qty_receh" name="qty_receh[]" min="0" required placeholder="Masukkan qty receh"></td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-soft-danger btnRemoveRow text-nowrap">
                                                                <i class="mdi mdi-delete me-2"></i>Delete
                                                            </button>
                                                        </td>
                                                    </tr>
                                                `);
                                            } else if (res2.status ===
                                                "selisig") {
                                                Swal.fire({
                                                    icon: 'warning',
                                                    title: 'Wajib Diisi!',
                                                    text: res2
                                                        .message ||
                                                        'Keterangan wajib diisi jika ada selisih.',
                                                    confirmButtonText: "Mengerti"
                                                });
                                                $("#keterangan").focus();
                                            } else {
                                                Swal.fire({
                                                    icon: "error",
                                                    title: "Gagal!",
                                                    text: res2
                                                        .message
                                                });
                                            }
                                        },
                                        error: function(xhr) {
                                            Swal.fire({
                                                icon: "error",
                                                title: "Terjadi Kesalahan",
                                                text: xhr
                                                    .responseJSON
                                                    ?.message ||
                                                    "Silakan coba lagi nanti."
                                            });
                                        }
                                    });
                                }
                            });
                        } else {
                            // ... (Kode Error: Jika ada error lain selain validation_error) ...
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        // ... (Kode Error AJAX Umum) ...
                        $("#btnSubmit").prop("disabled", false).html(
                            '<i class="mdi mdi-content-save-outline me-1"></i> Simpan Opname'
                        );

                        // Handle Laravel Validation Errors (422) yang standar
                        if (xhr.status === 422) {
                            let errorMsg = "Terdapat kesalahan pada input Anda.";
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).map(e => e
                                    .join('<br>')).join('<br>');
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal!',
                                html: errorMsg
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan!',
                                text: xhr.responseJSON?.message ||
                                    'Silakan coba lagi nanti.'
                            });
                        }
                    }
                });
            });

            // Function untuk menghitung Total Fisik dari semua baris input
            function calculateTotalFisik() {
                let totalFisik = 0;
                let qtyBox = parseFloat($("#qty_box").val()) || 0;
                $("#tableInput tbody tr").each(function() {
                    let qtyFull = parseFloat($(this).find('.qty_full').val()) || 0;
                    let qtyReceh = parseFloat($(this).find('.qty_receh').val()) || 0;

                    let qtyFisikRow = (qtyFull * qtyBox) + qtyReceh;
                    totalFisik += qtyFisikRow;
                });

                return totalFisik;
            }

            // Function utama untuk menjalankan validasi
            function validateBeforeSubmit() {
                let qtySOH = parseFloat($("#qty_soh").val()) || 0;
                let keterangan = $("#keterangan").val().trim();
                let totalFisik = calculateTotalFisik();
                let selisih = totalFisik - qtySOH;
                if (Math.abs(selisih) > 0 && keterangan === "") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Wajib Diisi!',
                        text: `Terdapat selisih (Fisik: ${totalFisik}, Sistem: ${qtySOH}). Keterangan wajib diisi.`,
                        confirmButtonText: "Mengerti"
                    });
                    $("#keterangan").focus();
                    return false;
                }

                return true;
            }
        });
    </script>
@endsection
