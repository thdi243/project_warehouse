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
Schedule::command('stock:update-status-qi-to-unrest')->dailyAt('00:00');
