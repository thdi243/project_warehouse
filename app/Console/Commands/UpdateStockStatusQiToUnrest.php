<?php

namespace App\Console\Commands;

use App\Models\Wrm\Inventory\StockInboundDetail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateStockStatusQiToUnrest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:update-status-qi-to-unrest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update stock status from QI to UNREST after 14 days from incoming date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $fourteenDaysAgo = now()->subDays(14);

            $updated = StockInboundDetail::whereStatus('QI')
                ->join('wrm_stock_inbound', 'wrm_stock_inbound_details.inbound_id', '=', 'wrm_stock_inbound.id')
                ->where('wrm_stock_inbound.incoming_date', '<=', $fourteenDaysAgo)
                ->update([
                    'wrm_stock_inbound_details.status' => 'UNREST'
                ]);

            $this->info("Status status berhasil diupdate: {$updated} records");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
