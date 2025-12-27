<?php

namespace App\Jobs;

use App\Http\Controllers\Wfg\stock_opname\StockOpnameWfgController;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateAndSendSopReportJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(public int $sopId) {}

    public function handle()
    {
        app()->call(
            [app(StockOpnameWfgController::class), 'sendReportAuto'],
            ['sop_id' => $this->sopId]
        );
    }
}
