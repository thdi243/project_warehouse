@extends('layouts.app')

@section('sidebar-size', 'sm')

@section('title', ' | Pekerjaan Forklift Driver')

@section('styles')
    <style>
        .job-checkbox {
            width: 22px;
            height: 22px;
            cursor: pointer;
            accent-color: #0d6efd;
            transform: scale(1.1);
        }

        #tableJobs tbody tr.row-selected {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .summary-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .summary-card-secondary {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .complete-actions {
            position: sticky;
            bottom: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.85);
            padding: 15px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid #cbd5e1;
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.05);
            display: none;
        }

        .search-container {
            position: relative;
        }

        .search-container .btn-clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            display: none;
        }

        .search-container .btn-clear:hover {
            color: #64748b;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Title -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">Pekerjaan Forklift Driver</h4>
                        <p class="text-muted mb-0">Self-service pemindahan stock outbound yang ditugaskan ke Anda.</p>
                    </div>
                    <div>
                        <button class="btn btn-primary btn-md" id="btnRefresh">
                            <i class="mdi mdi-refresh me-2"></i> Refresh Data
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Dashboard Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card summary-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-white-50 text-uppercase fw-bold mb-1 fs-12">Total Item Ditugaskan</h6>
                                <h3 class="text-white fw-bold mb-0" id="totalTasks">0</h3>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-white-10 rounded-circle fs-3">
                                    <i class="mdi mdi-clipboard-text-outline text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card summary-card-secondary h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-white-50 text-uppercase fw-bold mb-1 fs-12">Total Berat Item</h6>
                                <h3 class="text-white fw-bold mb-0"><span id="totalWeight">0</span> <span
                                        class="fs-14">KG</span></h3>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-white-10 rounded-circle fs-3">
                                    <i class="mdi mdi-weight-kilogram text-white"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Board Card -->
            <div class="card">
                <div class="card-header bg-light d-flex align-items-center justify-content-between py-3">
                    <h5 class="mb-0 fw-bold card-title">Daftar Pemindahan Barang</h5>
                    <div class="col-md-4 col-sm-6">
                        <div class="search-container">
                            <input type="text" class="form-control" id="searchJob"
                                placeholder="Cari SPB, Pallet, MID, Reservasi...">
                            <button class="btn-clear" id="btnClearSearch" type="button">
                                <i class="mdi mdi-close-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle text-nowrap"
                            id="tableJobs">
                            <thead class="table-light">
                                <tr>
                                    {{-- <th class="text-center" style="width: 50px;">
                                        <input type="checkbox" id="checkAllJobs" class="form-check-input job-checkbox">
                                    </th> --}}
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Info Outbound</th>
                                    <th>No SPB</th>
                                    <th>Pallet ID</th>
                                    <th>Info Barang</th>
                                    <th class="text-start">Qty (KG)</th>
                                    <th class="text-center">Lokasi</th>
                                    <th>Supplier</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 mb-0">Sedang memuat daftar tugas...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Floating Action Bar for Completion -->
            {{-- <div class="complete-actions mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="fs-15 fw-bold text-dark">
                        Terpilih: <span class="text-primary" id="selectedJobsCount">0</span> Item
                    </span>
                    <span class="mx-2 text-muted">|</span>
                    <span class="fs-15 fw-bold text-dark">
                        Total Berat: <span class="text-success" id="selectedJobsWeight">0</span> KG
                    </span>
                </div>
                <div>
                    <button class="btn btn-success btn-lg px-4" id="btnCompleteSelected">
                        <i class="mdi mdi-check-circle me-2"></i> Konfirmasi Selesai Pemindahan
                    </button>
                </div>
            </div> --}}

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let allJobs = [];

            function numberFormat(x) {
                if (x === null || x === undefined) return '0';
                let val = parseFloat(x);
                return val.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            }

            // Load data from server
            function loadJobs() {
                let searchVal = $('#searchJob').val();

                // Show clear search button if search input has value
                if (searchVal.trim() !== '') {
                    $('#btnClearSearch').show();
                } else {
                    $('#btnClearSearch').hide();
                }

                $.ajax({
                    url: "{{ route('wrm.inventory.forklift-jobs-data') }}",
                    method: "GET",
                    data: {
                        search: searchVal
                    },
                    success: function(res) {
                        if (res.status) {
                            allJobs = res.data;
                            renderJobs();
                        } else {
                            toastr.error('Gagal mengambil data pekerjaan.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Koneksi database bermasalah.');
                    }
                });
            }

            // Render tasks to the table
            function renderJobs() {
                let html = '';
                let totalTasksCount = allJobs.length;
                let totalTasksWeight = 0;

                // Reset checklist selections
                $('#checkAllJobs').prop('checked', false);
                $('.complete-actions').hide();

                if (totalTasksCount === 0) {
                    html = `
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="mdi mdi-checkbox-marked-circle-outline" style="font-size: 48px; color: #10b981;"></i>
                                    <h5 class="mt-2 fw-bold text-dark">Semua Tugas Selesai!</h5>
                                    <p class="text-muted">Tidak ada tugas pemindahan barang yang di-assign ke Anda saat ini.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    allJobs.forEach((job, index) => {
                        let qty = parseFloat(job.qty) || 0;
                        totalTasksWeight += qty;

                        let outboundInfo = '';
                        if (job.outbound) {
                            outboundInfo = `
                                <div><b class="text-primary">${job.outbound.no_reservasi}</b></div>
                                <div class="fs-11 text-muted">Shift ${job.outbound.shift} | ${job.outbound.reservasi_date}</div>
                            `;
                        } else {
                            outboundInfo = '<span class="text-muted">-</span>';
                        }

                        let locationInfo = '';
                        if (job.bin && job.bin.location) {
                            locationInfo = `
                                <div class="d-flex align-items-center justify-content-around">
                                    <div>
                                        <div class="fw-semibold">
                                            ${job.bin.location.plant} - ${job.bin.location.gudang} - ${job.bin.location.zona}
                                        </div>
                                        <div class="fs-11 text-muted">
                                            Kolom: ${job.bin.kolom}, Level: ${job.bin.level}
                                        </div>
                                    </div>

                                    <div class="fs-5 fw-bold ms-3 badge badge-soft-primary">
                                        ${job.bin.location.bin}
                                    </div>
                                </div>
                            `;
                        } else {
                            locationInfo = '<span class="text-muted">-</span>';
                        }

                        html += `
                            <tr data-id="${job.id}">
                                <td class="text-center fw-medium">${index + 1}</td>
                                <td>${outboundInfo}</td>
                                <td><b class="text-dark">${job.no_spb ?? '-'}</b></td>
                                <td class="text-center"><span class="badge badge-soft-secondary fs-12">${job.pallet_id ?? '-'}</span></td>
                                <td>
                                    <div class="fw-bold">${job.barang ? job.barang.mid : '-'}</div>
                                    <div style="font-size: 11px; white-space: normal;">${job.barang ? job.barang.nama_barang : '-'}</div>
                                    <div style="font-size: 11px; white-space: normal;">${job.group ? job.group : '-'}</div>
                                </td>
                                <td class="text-start fw-bold text-dark">${numberFormat(qty)}</td>
                                <td>${locationInfo}</td>
                                <td style="font-size: 11px; white-space: normal;">
                                    ${job.supplier ?? '<span class="text-muted">-</span>'}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-success btn-complete-single" data-id="${job.id}" title="Selesaikan Pemindahan">
                                        <i class="mdi mdi-check"></i> Selesai
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#tableJobs tbody').html(html);
                $('#totalTasks').text(totalTasksCount);
                $('#totalWeight').text(numberFormat(totalTasksWeight));
            }

            // Load jobs initial
            loadJobs();

            // Refresh button
            $('#btnRefresh').on('click', function() {
                loadJobs();
            });

            // Search inputs
            let searchTimeout;
            $('#searchJob').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(loadJobs, 500);
            });

            // Clear search
            $('#btnClearSearch').on('click', function() {
                $('#searchJob').val('');
                loadJobs();
            });

            // Check All Jobs handler
            $('#checkAllJobs').on('change', function() {
                let checked = $(this).prop('checked');
                $('.select-job').prop('checked', checked);

                // Toggle row selection class
                if (checked) {
                    $('#tableJobs tbody tr').addClass('row-selected');
                } else {
                    $('#tableJobs tbody tr').removeClass('row-selected');
                }

                updateCompletionBar();
            });

            // Single checkbox handler
            $(document).on('change', '.select-job', function() {
                let row = $(this).closest('tr');
                if ($(this).prop('checked')) {
                    row.addClass('row-selected');
                } else {
                    row.removeClass('row-selected');
                }

                // Sync the "check all" state
                let allChecked = $('.select-job:checked').length === $('.select-job').length;
                $('#checkAllJobs').prop('checked', allChecked);

                updateCompletionBar();
            });

            // Update details on the floating action bar
            function updateCompletionBar() {
                let checkedCount = $('.select-job:checked').length;
                let checkedWeight = 0;

                $('.select-job:checked').each(function() {
                    checkedWeight += parseFloat($(this).data('qty')) || 0;
                });

                if (checkedCount > 0) {
                    $('#selectedJobsCount').text(checkedCount);
                    $('#selectedJobsWeight').text(numberFormat(checkedWeight));
                    $('.complete-actions').fadeIn(200);
                } else {
                    $('.complete-actions').fadeOut(200);
                }
            }

            // Complete selected jobs click handler
            $('#btnCompleteSelected').on('click', function() {
                let selectedIds = [];

                $('.select-job:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });

                if (selectedIds.length === 0) return;

                Swal.fire({
                    title: 'Konfirmasi Pemindahan',
                    text: 'Apakah Anda sudah memindahkan ' + selectedIds.length +
                        ' item terpilih ke area pemrosesan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Ya, Sudah Dipindah',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wrm.inventory.forklift-jobs-complete') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                ids: selectedIds
                            },
                            beforeSend: function() {
                                Swal.fire({
                                    title: 'Menyimpan perubahan...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                            },
                            success: function(res) {
                                Swal.close();
                                if (res.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: res.message ||
                                            'Item berhasil diselesaikan.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        loadJobs();
                                    });
                                } else {
                                    Swal.fire('Error', res.message ||
                                        'Terjadi kesalahan.', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.close();
                                let errMsg = xhr.responseJSON?.message ||
                                    'Terjadi kesalahan sistem.';
                                Swal.fire('Gagal', errMsg, 'error');
                            }
                        });
                    }
                });
            });

            // Complete single job click handler
            $(document).on('click', '.btn-complete-single', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Konfirmasi Pemindahan',
                    text: 'Apakah Anda sudah memindahkan item ini ke area pemrosesan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Ya, Sudah Dipindah',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('wrm.inventory.forklift-jobs-complete') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                ids: [id]
                            },
                            beforeSend: function() {
                                Swal.fire({
                                    title: 'Menyimpan perubahan...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                            },
                            success: function(res) {
                                Swal.close();
                                if (res.status) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: res.message ||
                                            'Item berhasil diselesaikan.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        loadJobs();
                                    });
                                } else {
                                    Swal.fire('Error', res.message ||
                                        'Terjadi kesalahan.', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.close();
                                let errMsg = xhr.responseJSON?.message ||
                                    'Terjadi kesalahan sistem.';
                                Swal.fire('Gagal', errMsg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
