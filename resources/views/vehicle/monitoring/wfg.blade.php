@extends('layouts.app')

@section('title', '| WFG (Muat)')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">WFG Muat</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">WFG</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent py-3">
                            <h4 class="card-title mb-0 flex-grow-1"><i
                                    class="ri-upload-2-line me-2 align-middle text-info"></i>Antrian Muat Finished Goods
                            </h4>
                            <div class="flex-shrink-0">
                                <div style="width: 250px;">
                                    <input type="text" class="form-control" id="search_table"
                                        placeholder="Cari No. Polisi / Vendor...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="wfgTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 120px;">No. Antrian</th>
                                            <th>Check In</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Item</th>
                                            <th>No. SPB</th>
                                            <th>Qty SPB</th>
                                            <th>Durasi Aktivitas</th>
                                            <th class="text-center">Aksi Muat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">Loading data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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

                    // Highlight if loading too long (e.g. over 45 mins limit in WFG)
                    if (minutes >= 45) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-danger text-danger border border-danger-subtle');
                    } else if (minutes >= 30) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-warning text-warning');
                    }
                });
            }

            let allWfgData = [];
            let searchQuery = '';

            function renderWfgTable() {
                let filtered = allWfgData;
                if (searchQuery) {
                    filtered = allWfgData.filter(function(tx) {
                        const noPol = (tx.no_pol || '').toLowerCase();
                        const vendor = (tx.vendor || '').toLowerCase();
                        return noPol.includes(searchQuery) || vendor.includes(searchQuery);
                    });
                }

                let html = '';
                if (filtered.length === 0) {
                    html = `<tr>
                        <td colspan="9" class="text-center py-4 text-muted">Tidak ada kendaraan yang sesuai pencarian.</td>
                    </tr>`;
                } else {
                    filtered.forEach(function(tx) {
                        const antrianBadge = tx.no_antrian ?
                            `<span class="badge bg-soft-success text-success fs-13 px-3 py-2">
                                ${tx.no_antrian}
                            </span>` :
                            `<button type="button" class="btn btn-sm btn-outline-warning btn-get-queue" data-id="${tx.id}" data-nopol="${tx.no_pol}">
                                Ambil Antrian
                            </button>`;

                        const completeBtn = tx.no_antrian ?
                            `<button type="button" class="btn btn-sm btn-info btn-complete-loading" 
                                data-id="${tx.id}" 
                                data-nopol="${tx.no_pol}">
                                <i class="ri-checkbox-circle-line me-1 align-middle"></i> Selesai Muat
                            </button>` : '';

                        html += `<tr id="row-${tx.id}">
                            <td class="text-center">${antrianBadge}</td>
                            <td>${tx.arrival_time}</td>
                            <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                            <td>${tx.vendor || '-'}</td>
                            <td>${tx.item_name}</td>
                            <td>${tx.no_spb}</td>
                            <td>${tx.qty_spb}</td>
                            <td>
                                <span class="timer badge bg-soft-light text-muted" data-start="${tx.arrival_timestamp}">
                                    Calculated...
                                </span>
                            </td>
                            <td class="text-center">
                                ${completeBtn}
                            </td>
                        </tr>`;
                    });
                }
                $('#wfgTable tbody').html(html);
                updateTimers();
            }

            // AJAX Data Loader
            function loadWfgData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.wfg.data') }}",
                    type: 'GET',
                    success: function(response) {
                        allWfgData = response.queue;
                        renderWfgTable();
                    },
                    error: function(xhr) {
                        console.error('Gagal mengambil data WFG:', xhr);
                    }
                });
            }

            // Handle search
            $('#search_table').on('keyup', function() {
                searchQuery = $(this).val().toLowerCase();
                renderWfgTable();
            });

            // Initial load
            loadWfgData();

            // Run timers every second
            setInterval(updateTimers, 1000);

            // Real-time Event Listener with Laravel Echo
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates on Echo channel in WFG...');
                    window.Echo.channel('vehicle-tracking')
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in WFG:', payload);
                            if (window.toastr) {
                                toastr.info(payload.message, 'Update Lokasi Truk');
                            }
                            loadWfgData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // Complete loading
            $(document).on('click', '.btn-complete-loading', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                Swal.fire({
                    title: 'Selesai Bongkar/Muat?',
                    text: `Apakah truk ${nopol} telah menyelesaikan aktivitas bongkar/muat?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0ab39c',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Selesai!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/wfg/update-loading') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Selesai!', response.message, 'success').then(
                                    () => {
                                        $(`#row-${id}`).fadeOut(300, function() {
                                            loadWfgData();
                                        });
                                    });
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Gagal memproses permintaan.', 'error');
                            }
                        });
                    }
                });
            });

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
                            url: `{{ url('vehicle-monitoring/update-queue') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Berhasil!', response.message, 'success');
                                loadWfgData();
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message || 'Gagal mengambil nomor antrian.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
