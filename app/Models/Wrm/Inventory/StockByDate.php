<?php

namespace App\Models\Wrm\Inventory;

use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
     */
    public static function updateStockByDate($barangId, $date)
    {
        $dateStr = Carbon::parse($date)->toDateString();

        // Calculate cumulative stock up to target date from movements
        $inQty = StockMovement::where('barang_id', $barangId)
            ->where('jenis', 'in')
            ->whereDate('tanggal', '<=', $dateStr)
            ->sum('qty');

        $outQty = StockMovement::where('barang_id', $barangId)
            ->where('jenis', 'out')
            ->whereDate('tanggal', '<=', $dateStr)
            ->sum('qty');

        $qty = $inQty - $outQty;

        self::updateOrCreate(
            ['barang_id' => $barangId, 'tanggal' => $dateStr],
            ['qty' => $qty]
        );

        // Update all subsequent dates that exist in the database for this item
        $subsequentDates = self::where('barang_id', $barangId)
            ->where('tanggal', '>', $dateStr)
            ->pluck('tanggal');

        foreach ($subsequentDates as $subDate) {
            $inQtySub = StockMovement::where('barang_id', $barangId)
                ->where('jenis', 'in')
                ->whereDate('tanggal', '<=', $subDate)
                ->sum('qty');

            $outQtySub = StockMovement::where('barang_id', $barangId)
                ->where('jenis', 'out')
                ->whereDate('tanggal', '<=', $subDate)
                ->sum('qty');

            self::where('barang_id', $barangId)
                ->where('tanggal', $subDate)
                ->update(['qty' => $inQtySub - $outQtySub]);
        }
    }

    /**
     * Reconstructs all daily history records for all items from the movements table
     */
    public static function syncAllHistory()
    {
        self::truncate();

        $barangIds = MasterBarangModel::pluck('id');
        foreach ($barangIds as $barangId) {
            $dates = StockMovement::where('barang_id', $barangId)
                ->selectRaw('DATE(tanggal) as date')
                ->distinct()
                ->pluck('date');

            foreach ($dates as $date) {
                $inQty = StockMovement::where('barang_id', $barangId)
                    ->where('jenis', 'in')
                    ->whereDate('tanggal', '<=', $date)
                    ->sum('qty');

                $outQty = StockMovement::where('barang_id', $barangId)
                    ->where('jenis', 'out')
                    ->whereDate('tanggal', '<=', $date)
                    ->sum('qty');

                self::create([
                    'barang_id' => $barangId,
                    'tanggal'   => $date,
                    'qty'       => $inQty - $outQty,
                ]);
            }
        }
    }
}
