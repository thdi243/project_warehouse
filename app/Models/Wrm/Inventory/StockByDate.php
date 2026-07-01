<?php

namespace App\Models\Wrm\Inventory;

use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\MasterBarangModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockByDate extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_by_date';

    protected $fillable = [
        'barang_id',
        'tanggal',
        'qty',
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    /**
     * Recalculates and saves the cumulative stock balance for a specific date (and propagates to any newer dates)
     * using the active StockOnHand (UNREST, QI, BLOCKED) as the anchor point.
     */
    public static function updateStockByDate($barangId, $date)
    {
        $dateStr = Carbon::parse($date)->toDateString();
        $endOfDay = $dateStr . ' 23:59:59';

        // Current active stock on hand (ground truth - UNREST, QI, BLOCKED only)
        $currentSoh = DB::table('wrm_stock_on_hand')
            ->where('barang_id', $barangId)
            ->whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->sum('qty');

        // Sum of all outbound movements after this date
        $outAfter = StockMovement::where('barang_id', $barangId)
            ->where('jenis', 'out')
            ->where('tanggal', '>', $endOfDay)
            ->sum('qty');

        // Sum of all inbound movements after this date
        $inAfter = StockMovement::where('barang_id', $barangId)
            ->where('jenis', 'in')
            ->where('tanggal', '>', $endOfDay)
            ->sum('qty');

        $qty = $currentSoh + $outAfter - $inAfter;

        self::updateOrCreate(
            ['barang_id' => $barangId, 'tanggal' => $dateStr],
            ['qty' => $qty]
        );

        // Update all subsequent dates that exist in the database for this item
        $subsequentDates = self::where('barang_id', $barangId)
            ->where('tanggal', '>', $dateStr)
            ->pluck('tanggal');

        foreach ($subsequentDates as $subDate) {
            $subDateStr = Carbon::parse($subDate)->toDateString();
            $subEndOfDay = $subDateStr . ' 23:59:59';

            $outAfterSub = StockMovement::where('barang_id', $barangId)
                ->where('jenis', 'out')
                ->where('tanggal', '>', $subEndOfDay)
                ->sum('qty');

            $inAfterSub = StockMovement::where('barang_id', $barangId)
                ->where('jenis', 'in')
                ->where('tanggal', '>', $subEndOfDay)
                ->sum('qty');

            self::where('barang_id', $barangId)
                ->where('tanggal', $subDateStr)
                ->update(['qty' => $currentSoh + $outAfterSub - $inAfterSub]);
        }
    }

    /**
     * Reconstructs all daily history records for all items from the movements table
     * using the active StockOnHand (UNREST, QI, BLOCKED) as the anchor point.
     */
    public static function syncAllHistory()
    {
        self::truncate();

        $barangIds = MasterBarangModel::pluck('id');
        foreach ($barangIds as $barangId) {
            // Current Active SOH (UNREST, QI, BLOCKED only)
            $currentSoh = DB::table('wrm_stock_on_hand')
                ->where('barang_id', $barangId)
                ->whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING'])
                ->sum('qty');

            // Find all dates where movements occurred for this item
            $dates = StockMovement::where('barang_id', $barangId)
                ->selectRaw('DATE(tanggal) as date')
                ->distinct()
                ->pluck('date');

            foreach ($dates as $date) {
                $endOfDay = $date . ' 23:59:59';

                $outAfter = StockMovement::where('barang_id', $barangId)
                    ->where('jenis', 'out')
                    ->where('tanggal', '>', $endOfDay)
                    ->sum('qty');

                $inAfter = StockMovement::where('barang_id', $barangId)
                    ->where('jenis', 'in')
                    ->where('tanggal', '>', $endOfDay)
                    ->sum('qty');

                $qty = $currentSoh + $outAfter - $inAfter;

                self::create([
                    'barang_id' => $barangId,
                    'tanggal'   => $date,
                    'qty'       => $qty
                ]);
            }
        }
    }
}
