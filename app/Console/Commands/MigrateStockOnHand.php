<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateStockOnHand extends Command
{
    protected $signature = 'wrm:migrate-stock';
    protected $description = 'Migrate old stock from StockInboundDetail to StockOnHand';

    public function handle()
    {
        $this->info("Starting migration...");

        DB::statement('
            INSERT INTO wrm_stock_on_hand (no_spb, incoming_date, expired_date, supplier, barang_id, barcode, pallet_id, `group`, qty, status, loc_id, catatan, pallet, created_by, updated_by, created_at, updated_at)
            SELECT h.no_spb, h.incoming_date, h.expired_date, h.supplier, d.barang_id, d.barcode, d.pallet_id, d.group, d.qty, d.status, d.loc_id, d.catatan, d.pallet, d.created_by, d.updated_by, d.created_at, d.updated_at
            FROM wrm_stock_inbound_details d
            JOIN wrm_stock_inbound h ON d.inbound_id = h.id
            WHERE d.barcode NOT IN (SELECT barcode FROM wrm_stock_on_hand)
        ');

        $this->info("Migration completed successfully.");
    }
}
