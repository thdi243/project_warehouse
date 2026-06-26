@extends('layouts.app')

@section('title', '| WRM (Unloading)')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">WRM Unloading Queue</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Vehicle Monitoring</a></li>
                                <li class="breadcrumb-item active">WRM</li>
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
                                    class="ri-download-2-line me-2 align-middle text-primary"></i>Antrian Pembongkaran Raw
                                Material</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-nowrap" id="wrmTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Check In</th>
                                            <th>No. Polisi</th>
                                            <th>Vendor</th>
                                            <th>Item</th>
                                            <th>No. SPB</th>
                                            <th>Qty SPB</th>
                                            <th>Durasi Bongkar</th>
                                            <th class="text-center">Aksi Bongkar</th>
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

                    // Highlight if unloading too long (e.g. over 60 mins limit in WRM)
                    if (minutes >= 60) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-danger text-danger border border-danger-subtle');
                    } else if (minutes >= 45) {
                        $(this).removeClass('bg-soft-light text-muted').addClass(
                            'bg-soft-warning text-warning');
                    }
                });
            }

            // AJAX Data Loader
            function loadWrmData() {
                $.ajax({
                    url: "{{ route('vehicle.monitoring.wrm.data') }}",
                    type: 'GET',
                    success: function(response) {
                        let html = '';
                        if (response.queue.length === 0) {
                            html = `<tr>
                                <td colspan="9" class="text-center py-4 text-muted">Tidak ada kendaraan dalam proses bongkar WRM.</td>
                            </tr>`;
                        } else {
                            response.queue.forEach(function(tx, index) {
                                html += `<tr id="row-${tx.id}">
                                    <td class="text-center fw-bold" width="80">${index + 1}</td>
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
                                        <button type="button" class="btn btn-sm btn-primary btn-complete-unloading" 
                                            data-id="${tx.id}" 
                                            data-nopol="${tx.no_pol}">
                                            <i class="ri-checkbox-circle-line me-1 align-middle"></i> Selesai Bongkar
                                        </button>
                                    </td>
                                </tr>`;
                            });
                        }
                        $('#wrmTable tbody').html(html);
                        updateTimers();
                    },
                    error: function(xhr) {
                        console.error('Gagal mengambil data WRM:', xhr);
                    }
                });
            }

            // Initial load
            loadWrmData();

            // Run timers every second
            setInterval(updateTimers, 1000);

            // Real-time Event Listener with Laravel Echo
            function setupRealtimeEcho() {
                if (window.Echo && typeof window.Echo.channel === 'function') {
                    console.log('Listening for vehicle updates on Echo channel in WRM...');
                    window.Echo.channel('vehicle-tracking')
                        .listen('.vehicle.updated', (payload) => {
                            console.log('Echo event received in WRM:', payload);
                            if (window.toastr) {
                                toastr.info(payload.message, 'Update Lokasi Truk');
                            }
                            loadWrmData();
                        });
                } else {
                    setTimeout(setupRealtimeEcho, 100);
                }
            }
            setupRealtimeEcho();

            // Complete unloading
            $(document).on('click', '.btn-complete-unloading', function() {
                const id = $(this).data('id');
                const nopol = $(this).data('nopol');

                Swal.fire({
                    title: 'Selesai Bongkar?',
                    text: `Apakah truk ${nopol} telah menyelesaikan proses pembongkaran muatan?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3577f1',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Selesai!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('vehicle-monitoring/wrm/update-unloading') }}/${id}`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Selesai!', response.message, 'success').then(
                                    () => {
                                        $(`#row-${id}`).fadeOut(300, function() {
                                            loadWrmData();
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
