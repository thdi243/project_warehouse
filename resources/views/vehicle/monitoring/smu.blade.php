@extends('layouts.app')

@section('title', '| SMU Area')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">SMU Area Queue</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">SMU</li>
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
                                    class="ri-database-2-line me-2 align-middle text-warning"></i>Antrian Data SMU Area</h4>
                            <div class="flex-shrink-0">
                                <div style="width: 250px;">
                                    <input type="text" class="form-control" id="search_table" placeholder="Cari No. Polisi / Vendor...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="smuTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 120px;">No. Antrian</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Item</th>
                                            <th>No. SPB / Qty</th>
                                            <th>Waktu Tiba</th>
                                            <th>Durasi Aktivitas</th>
                                            <th class="text-center">Aksi Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">Loading data...</td>
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

                    // Highlight if waiting too long (e.g. over 30 mins limit in SMU)
                    if (minutes >= 30) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-danger text-danger border border-danger-subtle');
                    } else if (minutes >= 20) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-warning text-warning');
                    }
                });
            }

            let allSmuData = [];
            let searchQuery = '';

            function renderSmuTable() {
                let filtered = allSmuData;
                if (searchQuery) {
                    filtered = allSmuData.filter(function(tx) {
                        const noPol = (tx.no_pol || '').toLowerCase();
                        const vendor = (tx.vendor || '').toLowerCase();
                        return noPol.includes(searchQuery) || vendor.includes(searchQuery);
                    });
                }

                let html = '';
                if (filtered.length === 0) {
                    html = `<tr>
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada kendaraan yang sesuai pencarian.</td>
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
                            `<button type="button" class="btn btn-sm btn-warning btn-complete-smu" 
                                data-id="${tx.id}" 
                                data-nopol="${tx.no_pol}">
                                <i class="ri-checkbox-circle-line me-1 align-middle"></i> Selesai
                            </button>` : '';

                        html += `<tr id="row-${tx.id}">
                            <td class="text-center">${antrianBadge}</td>
                            <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                            <td>
                                <strong>${tx.vendor || '-'}</strong><br>
                                <small class="text-muted">Driver: ${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                            </td>
                            <td>${tx.item_name}</td>
                            <td>
                                <strong>${tx.no_spb}</strong><br>
                                <small class="text-muted">${tx.qty_spb}</small>
                            </td>
                            <td>${tx.arrival_time}</td>
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
                $('#smuTable tbody').html(html);
                updateTimers();
            }

            // AJAX Data Loader
            function loadSmuData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.smu.data') }}",
                    type: 'GET',
                    success: function(response) {
                        allSmuData = response.queue;
                        renderSmuTable();
                    },
                    error: function(xhr) {
                        console.error('Gagal mengambil data SMU:', xhr);
                    }
                });
            }

            // Handle search
            $('#search_table').on('keyup', function() {
                searchQuery = $(this).val().toLowerCase();
                renderSmuTable();
            });

            // Initial load
            loadSmuData();

            // Run timers every second
            setInterval(updateTimers, 1000);

            // Real-time Event Listener with Laravel Echo
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates on Echo channel in SMU...');
                    window.Echo.channel('vehicle-tracking')
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in SMU:', payload);
                            if (window.toastr) {
                                toastr.info(payload.message, 'Update Lokasi Truk');
                            }
                            loadSmuData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // Complete SMU action
            $(document).on('click', '.btn-complete-smu', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                Swal.fire({
                    title: 'Selesai di SMU?',
                    text: `Apakah truk ${nopol} telah menyelesaikan proses di SMU?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f1b44c',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Selesai!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/smu/complete') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Selesai!', response.message, 'success').then(
                                    () => {
                                        $(`#row-${id}`).fadeOut(300, function() {
                                            loadSmuData();
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
                                loadSmuData();
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
