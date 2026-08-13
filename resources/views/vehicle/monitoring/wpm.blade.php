@extends('layouts.app')

@section('title', '| WPM Area')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">WPM Area Queue</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">WPM</li>
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
                                    class="ri-download-2-line me-2 align-middle text-warning"></i>Antrian Bongkar WPM Area
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="wpmTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Antrian</th>
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

                    // Highlight if waiting too long (e.g. over 30 mins limit in WPM)
                    if (minutes >= 30) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-danger text-danger border border-danger-subtle');
                    } else if (minutes >= 20) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-warning text-warning');
                    }
                });
            }

            // AJAX Data Loader
            function loadWpmData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.wpm.data') }}",
                    type: 'GET',
                    success: function(response) {
                        let html = '';
                        if (response.queue.length === 0) {
                            html = `<tr>
                                <td colspan="8" class="text-center py-4 text-muted">Tidak ada kendaraan aktif di area WPM.</td>
                            </tr>`;
                        } else {
                            response.queue.forEach(function(tx, index) {
                                html += `<tr id="row-${tx.id}">
                                    <td class="text-center fw-bold" width="80">#${index + 1}</td>
                                    <td><span class="badge bg-soft-primary text-primary fs-12">${tx.no_pol}</span></td>
                                    <td>
                                        <strong>${tx.vendor || '-'}</strong><br>
                                        <small class="text-muted">Driver: ${tx.nama_driver || '-'} (${tx.no_hp_driver || '-'})</small>
                                    </td>
                                    <td>${tx.item_name}</td>
                                    <td>
                                        <strong>${tx.no_spb}</strong><br>
                                        <small class="text-muted">${parseFloat(tx.qty_spb)}</small>
                                    </td>
                                    <td>${tx.arrival_time}</td>
                                    <td>
                                        <span class="timer badge bg-soft-light text-muted" data-start="${tx.arrival_timestamp}">
                                            Calculated...
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning btn-complete-wpm" 
                                            data-id="${tx.id}" 
                                            data-nopol="${tx.no_pol}">
                                            <i class="ri-checkbox-circle-line me-1 align-middle"></i> Selesai
                                        </button>
                                    </td>
                                </tr>`;
                            });
                        }
                        $('#wpmTable tbody').html(html);
                        updateTimers();
                    },
                    error: function(xhr) {
                        console.error('Gagal mengambil data WPM:', xhr);
                    }
                });
            }

            // Initial load
            loadWpmData();

            // Run timers every second
            setInterval(updateTimers, 1000);

            // Real-time Event Listener with Laravel Echo
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates on Echo channel in WPM...');
                    window.Echo.channel('vehicle-tracking')
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in WPM:', payload);
                            if (window.toastr) {
                                toastr.info(payload.message, 'Update Lokasi Truk');
                            }
                            loadWpmData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // Complete WPM action
            $(document).on('click', '.btn-complete-wpm', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                Swal.fire({
                    title: 'Selesai di WPM?',
                    text: `Apakah truk ${nopol} telah menyelesaikan proses bongkar di WPM?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f1b44c',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Selesai!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/wpm/complete') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Selesai!', response.message, 'success').then(
                                    () => {
                                        $(`#row-${id}`).fadeOut(300, function() {
                                            loadWpmData();
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
        });
    </script>
@endsection
