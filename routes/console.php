<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Wsp\purchase_requesition\WspStockReservations;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    WspStockReservations::where('status', 'booked')
        ->where('expired_at', '<=', now())
        ->update([
            'status' => 'cancelled'
        ]);
})->everyMinute();
