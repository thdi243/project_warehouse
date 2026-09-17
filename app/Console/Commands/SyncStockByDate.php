<?php

namespace App\Console\Commands;

use App\Models\Wrm\Inventory\StockByDate;
use Illuminate\Console\Command;

class SyncStockByDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wrm:sync-stock-by-date 
                            {--date= : Specific date to sync (YYYY-MM-DD, defaults to today)} 
                            {--all : Reconstruct all historical dates from movements}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Snapshot and record daily WRM stock by date for all items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting WRM Stock By Date sync...');

        try {
            if ($this->option('all')) {
                $this->info('Reconstructing all historical records...');
                StockByDate::syncAllHistory();
                $this->info('Successfully reconstructed all historical stock by date records.');
            } else {
                $date = $this->option('date') ?? now()->toDateString();
                $this->info("Syncing stock by date for: {$date}");
                $count = StockByDate::syncDailyStock($date);
                $this->info("Successfully synced {$count} items for date {$date}.");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to sync stock by date: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
