@extends('layouts.app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        {{-- <h4 class="mb-sm-0">Data TKBM</h4> --}}

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">TKBM</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">Info</h4>
                        <p>Halaman ini menampilkan data TKBM yang telah diinput. Gunakan tombol "Export Excel" untuk
                            mengunduh data berdasarkan bulan yang dipilih.</p>
                    </div>
                </div>
            </div> --}}
            <div class="row mb-3">
                <!-- Start Date -->
                <div class="col-md-3 mb-3">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" id="startDate" class="form-control">
                </div>

                <!-- End Date -->
                <div class="col-md-3">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" id="endDate" class="form-control">
                </div>

                <!-- Button Filter -->
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-info w-100" id="applyFilter">Filter</button>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Data TKBM</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-stripped" id="tkbmTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Petugas</th>
                                            <th>Qty Terpal</th>
                                            <th>Qty Slipsheet</th>
                                            <th>Qty Pallet</th>
                                            <th>Total</th>
                                            <th>Keterangan Fee</th>
                                            <th></th>
                                            @if (Session::get('jabatan') !== 'operator')
                                                <th data-orderable="false">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- di isi oleh js --}}
                                    </tbody>
                                    <tfoot class="table-bordered table-light mt-4">
                                        <tr>
                                            <th colspan="3" class="text-center">Total</th>
                                            <th><span id="tQtyTerpal">0</span></th>
                                            <th><span id="tQtySlipsheet">0</span></th>
                                            <th><span id="tQtyPallet">0</span></th>
                                            <th>Rp <span id="tTotalQty">0</span></th>
                                            <th colspan="3">Rp <span id="tFee">0</span></th>
                                        </tr>
                                        <tr>
                                            <th colspan="7" class="text-center">PPn {{ $ppn }}%</th>
                                            <th colspan="3">Rp <span id="tPpnAct">0</span></th>
                                        </tr>
                                        <tr>
                                            <th colspan="7" class="text-center">PPh {{ $pph }}%</th>
                                            <th colspan="3">Rp <span id="tPphAct">0</span></th>
                                        </tr>
                                        <tr>
                                            <th colspan="7" class="text-center">Grand Total BPS</th>
                                            <th colspan="3">Rp <span id="tGrandTotal">0</span></th>
                                        </tr>
                                    </tfoot>

                                </table>
                                @if (Session::get('jabatan') !== 'operator')
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-success" id="exportExcel">Export Excel</button>
                                        <button class="btn btn-danger" id="downloadPdf">Download PDF</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pilih Bulan Export -->
    <div class="modal fade" id="bulanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Date Range for Export</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="startDateModal" class="form-label">Start Date</label>
                            <input type="date" id="startDateModal" class="form-control">
                        </div>
                        <div class="col-6">
                            <label for="endDateModal" class="form-label">Start Date</label>
                            <input type="date" id="endDateModal" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" id="confirmExport">Download</button>
                </div>
            </div>
        </div>
    </div>


    {{-- Modal Edit Button --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data TKBM</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        {{-- <div class="row"> --}}
                        <input type="hidden" id="editId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editDate" class="form-label">Tanggal</label>
                                <input type="date" id="editDate" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editPetugas" class="form-label">Petugas</label>
                                <input type="text" id="editPetugas" class="form-control" required>
                            </div>
                        </div>
                        {{-- </div> --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editShift" class="form-label">Shift</label>
                                <select name="editShift" id="editShift" class="form-select">
                                    <option value="" disabled>Pilih Shift</option>
                                    <option value="1">Shift 1</option>
                                    <option value="2">Shift 2</option>
                                    <option value="3">Shift 3</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 ">
                                <label for="editQtyTerpal" class="form-label">Qty Terpal</label>
                                <input type="number" id="editQtyTerpal" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editQtySlipsheet" class="form-label">Qty Slipsheet</label>
                                <input type="number" id="editQtySlipsheet" class="form-control" min="0"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editQtyPallet" class="form-label">Qty Pallet</label>
                                <input type="number" id="editQtyPallet" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="jmlTkbm" class="form-label">Jumlah TKBM</label>
                                <input type="number" id="jmlTkbm" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editKeterangan" class="form-label">Keterangan</label>
                                <textarea id="editKeterangan" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            getData();

            // inisialisasi bulan filter ke bulan sekarang
            let now = new Date();
            let month = (now.getMonth() + 1).toString().padStart(2, '0');
            let year = now.getFullYear();
            $("#bulanFilter").val(`${year}-${month}`);

            const fmtID = n => Number(n || 0).toLocaleString('id-ID');

            // data table
            const PPN = {{ $ppn ?? 11 }};
            const PPH = {{ $pph ?? 2 }};

            function getData(startDate = null, endDate = null) {
                const tbody = $("#tkbmTable tbody");

                $.ajax({
                    url: "{{ route('tkbm.data.show') }}",
                    method: "GET",
                    data: (startDate && endDate) ? {
                        start_date: startDate,
                        end_date: endDate
                    } : {},
                    success: function(response) {
                        tbody.empty();

                        // Akumulator total
                        let sumQtyTerpal = 0;
                        let sumQtySlipsheet = 0;
                        let sumQtyPallet = 0;
                        let sumTotalQty = 0;
                        let sumFee = 0;

                        if (!response.data || response.data.length === 0) {
                            tbody.append(
                                `<tr><td colspan="10" class="text-center bg-danger text-white">No data found.</td></tr>`
                            );
                        } else {
                            response.data.forEach((item, index) => {
                                const row = `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${moment(item.date).format("DD-MM-YYYY")}</td>
                                        <td>${item.petugas ? (item.petugas.charAt(0).toUpperCase() + item.petugas.slice(1)) : '-'}</td>
                                        <td>${item.qty_terpal ?? 0}</td>
                                        <td>${item.qty_slipsheet ?? 0}</td>
                                        <td>${item.qty_pallet ?? 0}</td>
                                        <td>Rp ${fmtID(item.total_qty)}</td>
                                        <td>Rp ${fmtID(item.total_fee)}</td>
                                        <td class="text-white"><span class="badge bg-success px-2 py-2 rounded-pill">${item.fee_id}%</span></td>
                                    @if (Session::get('jabatan') === 'dept_head' ||
                                            Session::get('jabatan') === 'supervisor' ||
                                            Session::get('jabatan') === 'foreman')
                                            <td class="text-center gap-2 d-flex justify-content-center">
                                                <button class="btn btn-sm btn-info editBtn" data-id="${item.id}">Edit</button>
                                                <button class="btn btn-sm btn-danger deleteBtn" data-id="${item.id}">Delete</button>
                                            </td>
                                    @endif     
                                    </tr>
                                `;
                                tbody.append(row);

                                // Akumulasi
                                sumQtyTerpal += Number(item.qty_terpal || 0);
                                sumQtySlipsheet += Number(item.qty_slipsheet || 0);
                                sumQtyPallet += Number(item.qty_pallet || 0);
                                sumTotalQty += Number(item.total_qty || 0);
                                sumFee += Number(item.total_fee || 0);
                            });
                        }

                        // Hitung pajak (asumsi dari total fee)
                        const ppnAct = sumFee * (PPN / 100);
                        const pphAct = sumFee * (PPH / 100);

                        const grandTotal = sumTotalQty + sumFee + ppnAct + pphAct;

                        // Isi footer
                        $('#tQtyTerpal').text(fmtID(sumQtyTerpal));
                        $('#tQtySlipsheet').text(fmtID(sumQtySlipsheet));
                        $('#tQtyPallet').text(fmtID(sumQtyPallet));
                        $('#tTotalQty').text(fmtID(sumTotalQty));
                        $('#tFee').text(fmtID(sumFee));
                        $('#tPpnAct').text(fmtID(ppnAct));
                        $('#tPphAct').text(fmtID(pphAct));
                        $('#tGrandTotal').text(fmtID(grandTotal));
                    },
                    error: function(xhr, status, error) {
                        console.error("Error load data:", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: xhr.responseJSON?.message ||
                                "Terjadi kesalahan saat mengambil data.",
                        });
                    }
                });
            }


            // modal export excel
            $("#exportExcel").click(function() {
                $("#bulanModal").modal("show");
            });

            // tombol confirm export
            $("#confirmExport").click(function() {
                let startDate = $("#startDateModal").val();
                let endDate = $("#endDateModal").val();

                // validasi kalau kosong
                if (!startDate || !endDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih tanggal awal dan akhir terlebih dahulu.',
                    });
                    return;
                }

                // validasi kalau endDate < startDate
                if (new Date(endDate) < new Date(startDate)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tanggal akhir harus lebih besar atau sama dengan tanggal awal.',
                    });
                    return;
                }

                // arahkan ke route export dengan query string
                window.location.href =
                    "{{ route('tkbm.data.export') }}" +
                    "?start_date=" +
                    startDate +
                    "&end_date=" +
                    endDate;

                // tutup modal
                $("#bulanModal").modal("hide");
            });


            // Tombol filter
            $("#applyFilter").click(function() {
                let startDate = $("#startDate").val();
                let endDate = $("#endDate").val();

                if (!startDate || !endDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Start date dan End date terlebih dahulu.',
                    });
                    return;
                }

                // Kirim ke fungsi getData
                getData(startDate, endDate);
            });


            // tombol delete
            $(document).on('click', '.deleteBtn', function() {
                let id = $(this).data('id');

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
                            url: "{{ route('tkbm.data.delete', '') }}/" + id,
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
                                getData($("#bulanFilter").val());
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

            // tombol edit
            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');

                // ambil data berdasarkan id
                $.ajax({
                    url: "{{ route('tkbm.data.edit', '') }}/" + id,
                    method: 'GET',
                    success: function(response) {
                        if (response.data) {
                            const item = response.data;
                            $("#editId").val(item.id);
                            $("#editDate").val(item.date);
                            $("#editPetugas").val(item.petugas);
                            $("#editShift").val(item.shift);
                            $("#editQtyTerpal").val(item.qty_terpal);
                            $("#editQtySlipsheet").val(item.qty_slipsheet);
                            $("#editQtyPallet").val(item.qty_pallet);
                            $("#jmlTkbm").val(fmtID(item.jml_tkbm));
                            $("#editKeterangan").val(item.keterangan || '');

                            $("#editModal").modal('show');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Data not found.',
                            });
                        }
                    },
                    error: function(err) {
                        console.error("Error fetching data:", err);
                        Swal.fire({
                            icon: 'error',
                            title: 'There was an error fetching the data.',
                        });
                    }
                });
            });

            // submit edit form
            $("#editForm").submit(function(e) {
                e.preventDefault();

                let id = $("#editId").val();
                let updatedData = {
                    date: $("#editDate").val(),
                    petugas: $("#editPetugas").val(),
                    shift: $("#editShift").val(),
                    qty_terpal: $("#editQtyTerpal").val(),
                    qty_slipsheet: $("#editQtySlipsheet").val(),
                    qty_pallet: $("#editQtyPallet").val(),
                    jml_tkbm: $("#jmlTkbm").val(),
                    keterangan: $("#editKeterangan").val(),
                };

                $.ajax({
                    url: "{{ route('tkbm.data.update', '') }}/" + id,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: updatedData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data updated successfully.',
                        });
                        $("#editModal").modal('hide');
                        getData($("#bulanFilter").val());
                    },
                    error: function(err) {
                        console.error("Error updating data:", err);
                        Swal.fire({
                            icon: 'error',
                            title: 'There was an error updating the data.',
                        });
                    }
                });
            });
        });
    </script>
@endsection
