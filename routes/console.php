<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Wsp\purchase_requesition\WspStockReservations;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Release unsubmitted bookings (expired after 15 mins)
    WspStockReservations::where('status', 'booked')
        ->where('expired_at', '<=', now())
        ->update([
            'status' => 'cancelled'
        ]);

    // Release confirmed reservations after 24 hours
    WspStockReservations::where('status', 'confirmed')
        ->where('type', 'reservation')
        ->where('confirmed_at', '<=', now()->subHours(24))
        ->update([
            'status' => 'released'
        ]);
})->everyMinute();

// Update stock status dari QI ke UNREST setelah 14 hari incoming date
// Schedule::command('stock:update-status-qi-to-unrest')->dailyAt('00:00');

// Snapshot stock per tanggal untuk semua barang WRM setiap hari pukul 23:59:59
Schedule::command('wrm:sync-stock-by-date')->dailyAt('23:59');

// Auto update status kempu dari WFG_TRANSFER_OUT_TO_PAS ke PAS_TRANSFER_IN_FROM_BAS setelah H+1 (24 jam)
Schedule::command('kempu:auto-transfer-in-pas')->hourly();

// Monitoring SLA Kendaraan: Overdue proses bongkar/muat > 2 jam
// Berjalan setiap 5 menit pada jam operasional shift (07:00 - 23:59), jeda pada jam 00:00 - 06:59
Schedule::call(function () {
    app(\App\Http\Controllers\Vehicle\VehicleTrackingController::class)->checkOverdueVehicles();
})->name('vehicle-monitor-sla')
    ->everyFiveMinutes()
    ->between('07:00', '23:59')
    ->withoutOverlapping();

// Daily Report SLA Kendaraan: Truck Duration > 7.5 jam ke Dept Head Warehouse
// Berjalan setiap hari pukul 07:00 pagi (merekap hari sebelumnya & truk yang masih open)
Schedule::call(function () {
    app(\App\Http\Controllers\Vehicle\VehicleTrackingController::class)->sendDailyOverdueDurationReport();
})->name('vehicle-daily-overdue-duration-report')
    ->dailyAt('07:00')
    ->withoutOverlapping();

// Command manual untuk menjalankan atau test Daily Report Truck Duration > 7.5 jam
Artisan::command('vehicle:report-duration {--date= : Tanggal periode evaluasi (format: YYYY-MM-DD, default: kemarin)}', function () {
    $date = $this->option('date');
    $this->info("Menjalankan Daily Report Truck Duration (>7.5 jam) untuk tanggal: " . ($date ?: 'kemarin'));
    $result = app(\App\Http\Controllers\Vehicle\VehicleTrackingController::class)->sendDailyOverdueDurationReport($date);
    $this->info("Hasil: " . json_encode($result, JSON_PRETTY_PRINT));
})->purpose('Kirim Daily Report Truck Duration > 7.5 jam ke Dept Head Warehouse');
