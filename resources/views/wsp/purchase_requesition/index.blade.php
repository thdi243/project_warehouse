@extends('layouts.app')

@section('title', '| Purchase Requesition')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-2" data-aos="fade-down">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="fw-bold fs-3">Purchase Requesition</h3>
                        <p class="fw-normal fs-6">Kelola Data Purchase Requesition Perusahaan</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="justify-content-md-end">
                            <a href="{{ url('/app/purchase-requesition/form') }}" target="_blank" class="btn btn-primary">
                                <i class="mdi mdi-file-document-edit me-2"></i>
                                <span>Form PR</span>
                            </a>
                            <button class="btn btn-outline-primary" id="btnRefresh">
                                <i class="mdi mdi-refresh me-2"></i>
                                <span>Refresh</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="card shadow-sm" data-aos="fade-up">
                <div class="card-header bg-light py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-0">
                                <i class="mdi mdi-table me-2"></i>Data Purchase Requesition
                            </h5>
                        </div>
                        <div class="col-md-4 mt-3 mt-md-0">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Cari User Peminta ...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderedless align-middle" id="IncomingTable">
                            <thead class="table-light text-nowrap">
                                <tr>
                                    <th style="width: 60px;">NO</th>
                                    <th>PR DATE</th>
                                    <th>NAMA PEMINTA</th>
                                    <th>DEPARTEMEN</th>
                                    <th>STATUS</th>
                                    <th style="width: 120px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">

                                <tr class="empty-state-row">
                                    <td colspan="16" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-2"></i>
                                            <h6 class="fw-bold">Belum Ada Data</h6>
                                            <p class="text-muted mb-0">
                                                Klik tombol <strong>"Form PR"</strong> untuk membuat pengajuan pembelian.
                                            </p>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Menampilkan <strong id="showingFrom">0</strong> -
                            <strong id="showingTo">0</strong> dari
                            <strong id="totalRecords">0</strong> data
                        </div>

                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0" id="pagination">
                                <!-- Auto Generate -->
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Edit Purchase Requesition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="formPREdit">
                    <div class="modal-body">
                        <input type="hidden" id="prId">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">PR Date</label>
                                <input type="date" id="prDate" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Peminta</label>
                                <input type="text" id="namaPeminta" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Departemen</label>
                                <input type="text" id="departemen" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">MID</label>
                                <input type="number" id="mid" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Qty</label>
                                <input type="number" id="qty" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan</label>
                                <input type="text" id="keterangan" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i>Simpan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail PR -->
    <div class="modal fade" id="modalDetailPR" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detail Purchase Requisition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- DATA UTAMA -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th>PR Date</th>
                                    <td id="d_pr_date">-</td>
                                </tr>
                                <tr>
                                    <th>Requested By</th>
                                    <td id="d_requested_by">-</td>
                                </tr>
                                <tr>
                                    <th>Department</th>
                                    <td id="d_department">-</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th>Jenis</th>
                                    <td id="d_jenis">-</td>
                                </tr>
                                <tr>
                                    <th>Detail Jenis</th>
                                    <td id="d_detail_jenis">-</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td id="d_status">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- TABLE ITEMS -->
                    <h6 class="mb-2">Detail Items</h6>
                    <div class="table-responsive">
                        <table class="table table-borderedless table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>MID Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Qty</th>
                                    <th>UOM</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="detailItems">
                                <tr>
                                    <td colspan="6" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            loadPRData();

            let allPR = [];
            let filteredPR = [];
            let currentPage = 1;
            const itemsPerPage = 10;

            // Ambil data dari backend
            function loadPRData() {
                $.ajax({
                    url: "{{ url('api/purchase-requesition/getData') }}",
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="16" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0 text-muted">Memuat data...</p>
                                </td>
                            </tr>
                        `);
                    },
                    success: function(res) {
                        if (res.success && Array.isArray(res.data)) {
                            allPR = res.data;
                            filteredPR = allPR;
                            renderTable();
                        } else {
                            $('#tableBody').html(
                                `<tr class="empty-state-row">
                                    <td colspan="16" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-2"></i>
                                            <h6 class="fw-bold">Belum Ada Data</h6>
                                            <p class="text-muted mb-0">
                                                Klik tombol <strong>"Form PR"</strong> untuk membuat pengajuan pembelian.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            `);
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        $('#tableBody').html(`
                            <tr>
                                <td colspan="16" class="text-center text-danger py-3">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> Gagal memuat data dari server.
                                </td>
                            </tr>
                        `);
                    }
                });
            }

            // Render table
            function renderTable() {
                const tbody = $('#tableBody');
                tbody.empty();

                if (filteredPR.length === 0) {
                    tbody.html(`
                        <tr class="empty-state-row">
                            <td colspan="16" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="mdi mdi-package-variant-closed fs-1 text-muted mb-2"></i>
                                    <h6 class="fw-bold">Belum Ada Data</h6>
                                    <p class="text-muted mb-0">
                                        Klik tombol <strong>"Form PR"</strong> untuk membuat pengajuan pembelian.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    `);
                    updatePaginationInfo(0, 0, 0);
                    return;
                }

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, filteredPR.length);
                const pageData = filteredPR.slice(startIndex, endIndex);

                const badgeStatus = {
                    'pending': 'warning',
                    'approved': 'success',
                    'rejected': 'danger',
                };

                pageData.forEach((pr, index) => {
                    const badgeClass = badgeStatus[pr.status] ?? 'secondary';
                    tbody.append(`
                        <tr>
                            <td>${startIndex + index + 1}</td>
                            <td>${pr.pr_date}</td>
                            <td>${pr.requested_by}</td>
                            <td>${pr.department}</td>
                            <td>
                                <span class="badge badge-soft-${badgeClass}">
                                    ${pr.status}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-info btn-edit btn-sm" onclick="detailPR(${pr.id})" title="Detail">
                                        <i class="mdi mdi-eye"></i>
                                    </button>
                                    <button class="btn btn-danger btn-delete btn-sm" onclick="deletePR(${pr.id})" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                    <button class="btn btn-primary btn-print btn-sm" onclick="printPR(${pr.id})" title="Print">
                                        <i class="mdi mdi-printer"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                updatePaginationInfo(startIndex + 1, endIndex, filteredPR.length);
                renderPagination();
            }

            // Update pagination info
            function updatePaginationInfo(from, to, total) {
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#totalRecords').text(total);
            }

            // Render pagination
            function renderPagination() {
                const totalPages = Math.ceil(filteredPR.length / itemsPerPage);
                const pagination = $('#pagination');
                pagination.empty();

                if (totalPages <= 1) return;

                // Previous button
                pagination.append(`
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>
                    </li>
                `);

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                        pagination.append(`
                            <li class="page-item ${i === currentPage ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                            </li>
                        `);
                    } else if (i === currentPage - 2 || i === currentPage + 2) {
                        pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                // Next button
                pagination.append(`
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </li>
                `);
            }

            // Change page
            window.changePage = function(page) {
                const totalPages = Math.ceil(filteredPR.length / itemsPerPage);
                if (page < 1 || page > totalPages) return;
                currentPage = page;
                renderTable();
            }

            // Event search
            $('#searchInput').on('input', function() {
                const keyword = $(this).val().toLowerCase().trim();

                if (keyword === '') {
                    filteredPR = allPR; // reset
                } else {
                    filteredPR = allPR.filter(item => {
                        // const mid = item.items.barang?.mid_barang ?
                        //     item.items.barang.mid_barang.toString().toLowerCase() :
                        //     '';

                        const requestedBy = item.requested_by ?
                            item.requested_by.toLowerCase() :
                            '';

                        return requestedBy.includes(keyword);
                    });
                }

                currentPage = 1; // reset ke page 1 saat search
                renderTable();
            });

            // Refresh button
            $('#btnRefresh').on('click', function() {
                loadPRData();
            });

            window.editPR = function(id) {
                $.ajax({
                    url: "{{ route('stock.pr.show', '') }}/" + id,
                    type: 'GET',
                    success: function(res) {
                        const data = res.data;
                        const barang = data.items.barang;

                        $('#outgoingId').val(data.id);
                        $('#mid').val(data.barang?.mid_barang ?? '');
                        $('#prDate').val(data.pr_date);
                        $('#departemen').val(data.department);
                        $('#qty').val(data.qty);
                        $('#keterangan').val(data.keterangan);
                        $('#namaPeminta').val(data.requested_by);
                        $('#batch').val(data.batch);

                        $('#modalTitle').text('Edit Outgoing');
                        $('#modalForm').modal('show');
                    },
                    error: function(xhr) {
                        let msg = 'Gagal mengambil data outgoing';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            };

            // Delete soh
            window.deletePR = function(id) {
                Swal.fire({
                    title: 'Yakin hapus data ini?',
                    text: 'Data yang dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('stock.pr.delete', '') }}/" + id,
                            type: 'DELETE',
                            success: function(res) {
                                toastr.success(res.message || 'Data berhasil dihapus');
                                loadPRData(); // reload tabel
                            },
                            error: function(xhr) {
                                let msg = 'Gagal menghapus data';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                toastr.error(msg);
                            }
                        });
                    }
                });
            };

            window.detailPR = function(id) {
                const pr = allPR.find(item => item.id === id);

                if (!pr) {
                    alert('Data PR tidak ditemukan');
                    return;
                }

                $('#d_pr_date').text(pr.pr_date ?? '-');
                $('#d_requested_by').text(pr.requested_by ?? '-');
                $('#d_department').text(pr.department ?? '-');
                $('#d_jenis').text(pr.jenis ?? '-');
                $('#d_detail_jenis').text(pr.detail_jenis ?? '-');
                $('#d_status').html(
                    `<span class="badge bg-warning text-dark">${pr.status}</span>`
                );


                const tbody = $('#detailItems');
                tbody.empty();

                if (!pr.items || pr.items.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Tidak ada item
                            </td>
                        </tr>
                    `);
                } else {
                    $.each(pr.items, function(i, item) {
                        tbody.append(`
                            <tr>
                                <td>${i + 1}</td>
                                <td>${item.barang?.mid_barang ?? '-'}</td>
                                <td>${item.barang?.nama_barang ?? '-'}</td>
                                <td>${item.qty}</td>
                                <td>${item.barang?.uom ?? '-'}</td>
                                <td>${item.keterangan ?? '-'}</td>
                            </tr>
                        `);
                    });
                }

                $('#modalDetailPR').modal('show');
            };

            window.printPR = function(id) {
                window.open(
                    `/api/purchase-requesition/print-riwayat/${id}`,
                    "_blank"
                );
            }
        });
    </script>
@endsection
