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

            $ba_waiting_mids = ['20000812', '20000860', '20001270'];
            $deletedSoh = 0;
            $updatedSohBa = 0;
            $updatedDraft = 0;

            // --- 1. SYNC USING BARCODE (For items with non-empty barcodes) ---
            $this->info("Syncing records using barcode...");

            $matches = DB::table('wrm_stock_on_hand as soh')
                ->join('wrm_stock_transfer_details as std', function($join) {
                    $join->on('soh.barcode', '=', 'std.no_barcode')
                         ->on('soh.barang_id', '=', 'std.barang_id');
                })
                ->join('wrm_master_barang as b', 'soh.barang_id', '=', 'b.id')
                ->select('soh.id as soh_id', 'b.mid')
                ->whereNotNull('std.no_barcode')
                ->where('std.no_barcode', '>', 0)
                ->get();

            $sohIdsToDelete = [];
            $sohIdsToUpdateBa = [];

            foreach ($matches as $match) {
                $isBaWaiting = in_array(trim((string)$match->mid), $ba_waiting_mids);
                if ($isBaWaiting) {
                    $sohIdsToUpdateBa[] = $match->soh_id;
                } else {
                    $sohIdsToDelete[] = $match->soh_id;
                }
            }

            if (!empty($sohIdsToDelete)) {
                $deletedSoh += DB::table('wrm_stock_on_hand')->whereIn('id', $sohIdsToDelete)->delete();
            }
            if (!empty($sohIdsToUpdateBa)) {
                $updatedSohBa += DB::table('wrm_stock_on_hand')
                    ->whereIn('id', $sohIdsToUpdateBa)
                    ->where('status', '!=', 'BA WAITING')
                    ->update([
                        'status' => 'BA WAITING',
                        'updated_at' => now(),
                    ]);
            }

            // Sync Draft Details using barcode
            $draftMatches = DB::table('wrm_stock_draft_outbound_details as dod')
                ->join('wrm_stock_transfer_details as std', function($join) {
                    $join->on('dod.barcode', '=', 'std.no_barcode')
                         ->on('dod.barang_id', '=', 'std.barang_id');
                })
                ->join('wrm_master_barang as b', 'dod.barang_id', '=', 'b.id')
                ->select('dod.id as dod_id', 'b.mid', 'dod.status as current_status')
                ->whereNotNull('std.no_barcode')
                ->where('std.no_barcode', '>', 0)
                ->get();

            $dodIdsToIssued = [];
            $dodIdsToBa = [];

            foreach ($draftMatches as $match) {
                $isBaWaiting = in_array(trim((string)$match->mid), $ba_waiting_mids);
                $targetStatus = $isBaWaiting ? 'BA WAITING' : 'ISSUED';
                if ($match->current_status !== $targetStatus) {
                    if ($isBaWaiting) {
                        $dodIdsToBa[] = $match->dod_id;
                    } else {
                        $dodIdsToIssued[] = $match->dod_id;
                    }
                }
            }

            if (!empty($dodIdsToIssued)) {
                $updatedDraft += DB::table('wrm_stock_draft_outbound_details')->whereIn('id', $dodIdsToIssued)->update([
                    'status' => 'ISSUED',
                    'updated_at' => now(),
                ]);
            }
            if (!empty($dodIdsToBa)) {
                $updatedDraft += DB::table('wrm_stock_draft_outbound_details')->whereIn('id', $dodIdsToBa)->update([
                    'status' => 'BA WAITING',
                    'updated_at' => now(),
                ]);
            }

            // --- 2. SYNC BARCODE-LESS ITEMS (By no_spb and pallet count) ---
            $this->info("Syncing barcode-less records using no_spb and pallet counts...");

            $noBarcodeTransfers = DB::table('wrm_stock_transfer_details as std')
                ->join('wrm_master_barang as b', 'std.barang_id', '=', 'b.id')
                ->select('std.no_spb', 'std.barang_id', 'b.mid', DB::raw('count(*) as transfer_count'))
                ->where(function($q) {
                    $q->whereNull('std.no_barcode')->orWhere('std.no_barcode', '=', '');
                })
                ->groupBy('std.no_spb', 'std.barang_id', 'b.mid')
                ->get();

            foreach ($noBarcodeTransfers as $transfer) {
                $isBaWaiting = in_array(trim((string)$transfer->mid), $ba_waiting_mids);

                if ($isBaWaiting) {
                    $baCountSoh = DB::table('wrm_stock_on_hand')
                        ->where('no_spb', $transfer->no_spb)
                        ->where('barang_id', $transfer->barang_id)
                        ->where('status', 'BA WAITING')
                        ->count();

                    if ($baCountSoh < $transfer->transfer_count) {
                        $needed = $transfer->transfer_count - $baCountSoh;

                        $sohIds = DB::table('wrm_stock_on_hand')
                            ->where('no_spb', $transfer->no_spb)
                            ->where('barang_id', $transfer->barang_id)
                            ->where('status', '!=', 'BA WAITING')
                            ->orderBy('pallet_id', 'asc')
                            ->limit($needed)
                            ->pluck('id')
                            ->toArray();

                        if (!empty($sohIds)) {
                            $updatedSohBa += DB::table('wrm_stock_on_hand')
                                ->whereIn('id', $sohIds)
                                ->update([
                                    'status' => 'BA WAITING',
                                    'updated_at' => now(),
                                ]);
                        }
                    }
                } else {
                    // Delete standard completed transfer items from SOH
                    $sohIdsToDelete = DB::table('wrm_stock_on_hand')
                        ->where('no_spb', $transfer->no_spb)
                        ->where('barang_id', $transfer->barang_id)
                        ->orderBy('pallet_id', 'asc')
                        ->limit($transfer->transfer_count)
                        ->pluck('id')
                        ->toArray();

                    if (!empty($sohIdsToDelete)) {
                        $deletedSoh += DB::table('wrm_stock_on_hand')
                            ->whereIn('id', $sohIdsToDelete)
                            ->delete();
                    }
                }

                // Sync Draft Details status
                $targetStatus = $isBaWaiting ? 'BA WAITING' : 'ISSUED';
                $statusCountDraft = DB::table('wrm_stock_draft_outbound_details')
                    ->where('no_spb', $transfer->no_spb)
                    ->where('barang_id', $transfer->barang_id)
                    ->where('status', $targetStatus)
                    ->count();

                if ($statusCountDraft < $transfer->transfer_count) {
                    $needed = $transfer->transfer_count - $statusCountDraft;

                    $draftIds = DB::table('wrm_stock_draft_outbound_details')
                        ->where('no_spb', $transfer->no_spb)
                        ->where('barang_id', $transfer->barang_id)
                        ->where('status', '!=', $targetStatus)
                        ->orderBy('pallet_id', 'asc')
                        ->limit($needed)
                        ->pluck('id')
                        ->toArray();

                    if (!empty($draftIds)) {
                        $updatedDraft += DB::table('wrm_stock_draft_outbound_details')
                            ->whereIn('id', $draftIds)
                            ->update([
                                'status' => $targetStatus,
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            $this->info("Sync completed successfully!");
            $this->info("- Deleted: {$deletedSoh} completed transfer records from wrm_stock_on_hand.");
            $this->info("- Updated: {$updatedSohBa} records in wrm_stock_on_hand to BA WAITING.");
            $this->info("- Updated: {$updatedDraft} records in wrm_stock_draft_outbound_details.");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error during sync: ' . $e->getMessage());
            return 1;
        }
    }
}
