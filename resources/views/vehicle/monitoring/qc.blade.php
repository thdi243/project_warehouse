@extends('layouts.app')

@section('title', '| QC Area')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">QC Area Queue</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">QC Area</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- 1. Antrian Dokumen (Waiting Dokumen) -->
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                            <h4 class="card-title mb-0 flex-grow-1">
                                <i class="ri-file-list-3-line me-2 align-middle text-warning"></i>Antrian Dokumen (Waiting
                                Dokumen)
                            </h4>
                            <div class="flex-shrink-0">
                                <div style="width: 250px;">
                                    <input type="text" class="form-control" id="search_waiting"
                                        placeholder="Cari No. Polisi / Vendor / SPB...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="waitingTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Check In</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Lokasi Tujuan</th>
                                            <th>No SPB</th>
                                            <th>Qty SPB</th>
                                            <th>Item</th>
                                            <th>Durasi Tunggu</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">Loading data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small" id="waiting-pagination-info">
                                    Showing 0 to 0 of 0 entries
                                </div>
                                <nav aria-label="Waiting Table Pagination">
                                    <ul class="pagination pagination-rounded justify-content-end mb-0"
                                        id="waiting-pagination">
                                        <!-- Dynamic links -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Proses Sampling -->
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                            <h4 class="card-title mb-0 flex-grow-1">
                                <i class="ri-flask-line me-2 align-middle text-success"></i>Proses Sampling QC
                            </h4>
                            <div class="flex-shrink-0">
                                <div style="width: 250px;">
                                    <input type="text" class="form-control" id="search_qc"
                                        placeholder="Cari No. Polisi / Vendor / SPB...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="qcTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. Antrian</th>
                                            <th>Check In</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Lokasi Tujuan</th>
                                            <th>No SPB</th>
                                            <th>Qty SPB</th>
                                            <th>Item</th>
                                            <th>Durasi Tunggu</th>
                                            <th class="text-center">Aksi QC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">Loading data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small" id="qc-pagination-info">
                                    Showing 0 to 0 of 0 entries
                                </div>
                                <nav aria-label="QC Table Pagination">
                                    <ul class="pagination pagination-rounded justify-content-end mb-0" id="qc-pagination">
                                        <!-- Dynamic links -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QC Update Modal -->
    <div class="modal fade" id="qcModal" tabindex="-1" aria-labelledby="qcModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title" id="qcModalLabel">Input Hasil Sampel Awal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="qcForm">
                    @csrf
                    <input type="hidden" id="qc-transaction-id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label d-block text-muted small fw-bold text-uppercase">Kendaraan</label>
                            <h4 id="qc-nopol-text" class="text-primary">-</h4>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keputusan QC <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="qc_status" id="status-release"
                                        value="released" required>
                                    <label class="btn btn-outline-success w-100 py-3" for="status-release">
                                        <i class="ri-check-double-line fs-20 d-block mb-1"></i>
                                        RELEASE
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="qc_status" id="status-reject"
                                        value="rejected" required>
                                    <label class="btn btn-outline-danger w-100 py-3" for="status-reject">
                                        <i class="ri-close-circle-line fs-20 d-block mb-1"></i>
                                        REJECT
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="qc-notes" class="form-label">Catatan Pemeriksaan</label>
                            <textarea class="form-control" id="qc-notes" name="notes" rows="3"
                                placeholder="Masukkan detail sampel awal, kelembaban, atau kendala..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btnQcSubmit">Simpan Status QC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Setup real-time timers
            function updateTimers() {
                $('.timer').each(function() {
                    const startTimestamp = parseInt($(this).data('start'));
                    const nowTimestamp = Math.floor(Date.now() / 1000);
                    const diffSeconds = nowTimestamp - startTimestamp;

                    const hours = Math.floor(diffSeconds / 3600);
                    const minutes = Math.floor((diffSeconds % 3600) / 60);
                    const seconds = diffSeconds % 60;

                    let timeStr = '';
                    if (hours > 0) {
                        timeStr += hours + 'j ';
                    }
                    timeStr += minutes + 'm ' + seconds + 'd';

                    $(this).text(timeStr);

                    // Highlight if waiting too long (e.g. over 30 mins limit in QC)
                    if (minutes >= 30) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-danger text-danger border border-danger-subtle');
                    } else if (minutes >= 20) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-warning text-warning');
                    }
                });
            }

            let allWaitingData = [];
            let allQcData = [];
            let waitingCurrentPage = 1;
            let qcCurrentPage = 1;
            const itemsPerPage = 10;
            let waitingSearchQuery = '';
            let qcSearchQuery = '';

            function buildPaginationHTML(totalItems, currentPage, itemsPerPage) {
                const totalPages = Math.ceil(totalItems / itemsPerPage);
                let html = '';
                if (totalPages <= 1) return '';

                html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}">Previous</a>
                </li>`;

                for (let i = 1; i <= totalPages; i++) {
                    html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                    </li>`;
                }

                html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}">Next</a>
                </li>`;

                return html;
            }

            function renderWaitingTable() {
                let filtered = allWaitingData;
                if (waitingSearchQuery) {
                    filtered = allWaitingData.filter(function(tx) {
                        const noPol = (tx.no_pol || '').toLowerCase();
                        const vendor = (tx.vendor || '').toLowerCase();
                        const noSpb = (tx.no_spb || '').toLowerCase();
                        return noPol.includes(waitingSearchQuery) || vendor.includes(waitingSearchQuery) ||
                            noSpb.includes(waitingSearchQuery);
                    });
                }

                const totalItems = filtered.length;
                const totalPages = Math.ceil(totalItems / itemsPerPage);

                if (waitingCurrentPage > totalPages) {
                    waitingCurrentPage = totalPages;
                }
                if (waitingCurrentPage < 1) {
                    waitingCurrentPage = 1;
                }

                const startIndex = (waitingCurrentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
                const paginated = filtered.slice(startIndex, endIndex);

                let waitingHtml = '';
                if (paginated.length === 0) {
                    waitingHtml = `<tr>
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada kendaraan yang menunggu dokumen.</td>
                    </tr>`;
                    $('#waiting-pagination-info').text('Showing 0 to 0 of 0 entries');
                } else {
                    paginated.forEach(function(tx) {
                        waitingHtml += `<tr id="row-${tx.id}">
                            <td>${tx.arrival_time}</td>
                            <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                            <td>${tx.vendor || '-'}</td>
                            <td>${tx.lokasi_tujuan || '-'}</td>
                            <td>${tx.no_spb || '-'}</td>
                            <td>${tx.qty_spb || '-'}</td>
                            <td>${tx.item_name}</td>
                            <td>
                                <span class="timer badge bg-soft-light text-muted" data-start="${tx.arrival_timestamp}">
                                    Calculated...
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-warning btn-get-queue"
                                    data-id="${tx.id}"
                                    data-nopol="${tx.no_pol}">
                                    <i class="ri-edit-box-line me-1 align-middle"></i>
                                    Ambil Antrian
                                </button>
                            </td>
                        </tr>`;
                    });
                    $('#waiting-pagination-info').text(
                        `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} entries`);
                }
                $('#waitingTable tbody').html(waitingHtml);
                $('#waiting-pagination').html(buildPaginationHTML(totalItems, waitingCurrentPage, itemsPerPage));
                updateTimers();
            }

            function renderQcTable() {
                let filtered = allQcData;
                if (qcSearchQuery) {
                    filtered = allQcData.filter(function(tx) {
                        const noPol = (tx.no_pol || '').toLowerCase();
                        const vendor = (tx.vendor || '').toLowerCase();
                        const noSpb = (tx.no_spb || '').toLowerCase();
                        return noPol.includes(qcSearchQuery) || vendor.includes(qcSearchQuery) || noSpb
                            .includes(qcSearchQuery);
                    });
                }

                const totalItems = filtered.length;
                const totalPages = Math.ceil(totalItems / itemsPerPage);

                if (qcCurrentPage > totalPages) {
                    qcCurrentPage = totalPages;
                }
                if (qcCurrentPage < 1) {
                    qcCurrentPage = 1;
                }

                const startIndex = (qcCurrentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
                const paginated = filtered.slice(startIndex, endIndex);

                let qcHtml = '';
                if (paginated.length === 0) {
                    qcHtml = `<tr>
                        <td colspan="9" class="text-center py-4 text-muted">Tidak ada kendaraan dalam proses sampling QC.</td>
                    </tr>`;
                    $('#qc-pagination-info').text('Showing 0 to 0 of 0 entries');
                } else {
                    paginated.forEach(function(tx) {
                        qcHtml += `<tr id="row-${tx.id}">
                            <td class="text-center fw-bold fs-13 text-primary" width="100">
                                #${tx.no_antrian}
                            </td>
                            <td>${tx.arrival_time}</td>
                            <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                            <td>${tx.vendor || '-'}</td>
                            <td>${tx.lokasi_tujuan || '-'}</td>
                            <td>${tx.no_spb || '-'}</td>
                            <td>${tx.qty_spb || '-'}</td>
                            <td>${tx.item_name}</td>
                            <td>
                                <span class="timer badge bg-soft-light text-muted" data-start="${tx.arrival_timestamp}">
                                    Calculated...
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-success btn-qc-update"
                                    data-id="${tx.id}"
                                    data-nopol="${tx.no_pol}">
                                    <i class="ri-edit-box-line me-1 align-middle"></i> Update QC
                                </button>
                            </td>
                        </tr>`;
                    });
                    $('#qc-pagination-info').text(
                        `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} entries`);
                }
                $('#qcTable tbody').html(qcHtml);
                $('#qc-pagination').html(buildPaginationHTML(totalItems, qcCurrentPage, itemsPerPage));
                updateTimers();
            }

            // AJAX Data Loader
            function loadQcData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.qc.data') }}",
                    type: 'GET',
                    success: function(response) {
                        allWaitingData = response.antriSampling;
                        allQcData = response.prosesSample;
                        renderWaitingTable();
                        renderQcTable();
                    },
                    error: function(xhr) {
                        console.error('Gagal mengambil data QC:', xhr);
                    }
                });
            }

            // Initial load
            loadQcData();

            // Run timers every second
            setInterval(updateTimers, 1000);

            // Real-time Event Listener with Laravel Echo
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates on Echo channel in QC Area...');
                    window.Echo.channel('vehicle-tracking')
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in QC:', payload);
                            if (window.toastr) {
                                toastr.info(payload.message, 'Update Lokasi Truk');
                            }
                            loadQcData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // Ambil Antrian Click Handler
            $(document).on('click', '.btn-get-queue', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                Swal.fire({
                    title: 'Ambil Nomor Antrian?',
                    text: `Ambil nomor antrian otomatis untuk truk ${nopol}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3577f1',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ambil!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/qc/update-queue') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadQcData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal mengambil nomor antrian.', 'error');
                            }
                        });
                    }
                });
            });

            // Open QC modal
            $(document).on('click', '.btn-qc-update', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                $('#qc-transaction-id').val(id);
                $('#qc-nopol-text').text(nopol);
                $('#qc-notes').val('');
                $('input[name="qc_status"]').prop('checked', false);

                $('#qcModal').modal('show');
            });

            // Submit QC form
            $('#qcForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#qc-transaction-id').val();
                const status = $('input[name="qc_status"]:checked').val();
                const notes = $('#qc-notes').val();

                $('#btnQcSubmit').prop('disabled', true).text('Loading...');

                $.ajax({
                    url: `{{ url('vehicle-monitoring/qc/update-qc') }}/${id}`,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        qc_status: status,
                        notes: notes
                    },
                    success: function(response) {
                        $('#qcModal').modal('hide');
                        Swal.fire({
                            icon: response.success ? 'success' : 'error',
                            title: response.success ? 'Berhasil' : 'Gagal',
                            text: response.message
                        }).then(() => {
                            $(`#row-${id}`).fadeOut(300, function() {
                                loadQcData();
                            });
                        });
                    },
                    error: function(xhr) {
                        $('#qcModal').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan sistem.'
                        });
                    },
                    complete: function() {
                        $('#btnQcSubmit').prop('disabled', false).text('Simpan Status QC');
                    }
                });
            });

            // Handle searches
            $('#search_waiting').on('keyup', function() {
                waitingSearchQuery = $(this).val().toLowerCase();
                waitingCurrentPage = 1;
                renderWaitingTable();
            });

            $('#search_qc').on('keyup', function() {
                qcSearchQuery = $(this).val().toLowerCase();
                qcCurrentPage = 1;
                renderQcTable();
            });

            // Handle pagination clicks
            $(document).on('click', '#waiting-pagination .page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page) {
                    waitingCurrentPage = parseInt(page);
                    renderWaitingTable();
                }
            });

            $(document).on('click', '#qc-pagination .page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page) {
                    qcCurrentPage = parseInt(page);
                    renderQcTable();
                }
            });
        });
    </script>
@endsection
