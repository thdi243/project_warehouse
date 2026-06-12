<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSohTransferStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync-transfer-soh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync StockOnHand and Draft Outbound Status with Completed Transfers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info("Starting sync between StockOnHand/Draft Outbound and StockTransferDetail...");

            $updatedSoh = 0;
            $updatedDraft = 0;

            // --- 1. SYNC USING BARCODE (For items with non-empty barcodes) ---
            $this->info("Syncing records using barcode...");
            
            $updatedSoh += DB::table('wrm_stock_on_hand as soh')
                ->join('wrm_stock_transfer_details as std', function($join) {
                    $join->on('soh.barcode', '=', 'std.no_barcode')
                         ->on('soh.barang_id', '=', 'std.barang_id');
                })
                ->whereNotNull('std.no_barcode')
                ->where('std.no_barcode', '>', 0)
                ->where('soh.status', '!=', 'ISSUED')
                ->update([
                    'soh.status' => 'ISSUED',
                    'soh.updated_at' => now(),
                ]);

            $updatedDraft += DB::table('wrm_stock_draft_outbound_details as dod')
                ->join('wrm_stock_transfer_details as std', function($join) {
                    $join->on('dod.barcode', '=', 'std.no_barcode')
                         ->on('dod.barang_id', '=', 'std.barang_id');
                })
                ->whereNotNull('std.no_barcode')
                ->where('std.no_barcode', '>', 0)
                ->where('dod.status', '!=', 'ISSUED')
                ->update([
                    'dod.status' => 'ISSUED',
                    'dod.updated_at' => now(),
                ]);

            // --- 2. SYNC BARCODE-LESS ITEMS (By no_spb and pallet count) ---
            $this->info("Syncing barcode-less records using no_spb and pallet counts...");

            $noBarcodeTransfers = DB::table('wrm_stock_transfer_details')
                ->select('no_spb', 'barang_id', DB::raw('count(*) as transfer_count'))
                ->where(function($q) {
                    $q->whereNull('no_barcode')->orWhere('no_barcode', '=', '');
                })
                ->groupBy('no_spb', 'barang_id')
                ->get();

            foreach ($noBarcodeTransfers as $transfer) {
                // SOH sync
                $issuedCountSoh = DB::table('wrm_stock_on_hand')
                    ->where('no_spb', $transfer->no_spb)
                    ->where('barang_id', $transfer->barang_id)
                    ->where('status', 'ISSUED')
                    ->count();

                if ($issuedCountSoh < $transfer->transfer_count) {
                    $needed = $transfer->transfer_count - $issuedCountSoh;

                    $sohIds = DB::table('wrm_stock_on_hand')
                        ->where('no_spb', $transfer->no_spb)
                        ->where('barang_id', $transfer->barang_id)
                        ->where('status', '!=', 'ISSUED')
                        ->orderBy('pallet_id', 'asc')
                        ->limit($needed)
                        ->pluck('id')
                        ->toArray();

                    if (!empty($sohIds)) {
                        $updatedSoh += DB::table('wrm_stock_on_hand')
                            ->whereIn('id', $sohIds)
                            ->update([
                                'status' => 'ISSUED',
                                'updated_at' => now(),
                            ]);
                    }
                }

                // Draft details sync
                $issuedCountDraft = DB::table('wrm_stock_draft_outbound_details')
                    ->where('no_spb', $transfer->no_spb)
                    ->where('barang_id', $transfer->barang_id)
                    ->where('status', 'ISSUED')
                    ->count();

                if ($issuedCountDraft < $transfer->transfer_count) {
                    $needed = $transfer->transfer_count - $issuedCountDraft;

                    $draftIds = DB::table('wrm_stock_draft_outbound_details')
                        ->where('no_spb', $transfer->no_spb)
                        ->where('barang_id', $transfer->barang_id)
                        ->where('status', '!=', 'ISSUED')
                        ->orderBy('pallet_id', 'asc')
                        ->limit($needed)
                        ->pluck('id')
                        ->toArray();

                    if (!empty($draftIds)) {
                        $updatedDraft += DB::table('wrm_stock_draft_outbound_details')
                            ->whereIn('id', $draftIds)
                            ->update([
                                'status' => 'ISSUED',
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            $this->info("Sync completed successfully!");
            $this->info("- Total Updated: {$updatedSoh} records in wrm_stock_on_hand to ISSUED.");
            $this->info("- Total Updated: {$updatedDraft} records in wrm_stock_draft_outbound_details to ISSUED.");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error during sync: ' . $e->getMessage());
            return 1;
        }
    }
}
