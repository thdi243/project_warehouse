<?php

namespace App\Models\Wrm\Inventory;

use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_balance';

    protected $fillable = [
        'barang_id',
        'qty',
        'created_by',
        'updated_by',
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    /**
     * Recalculates the current stock balance of a specific item based on active StockOnHand records
     */
    public static function recalculate($barangId)
    {
        $qty = StockOnHand::where('barang_id', $barangId)
            ->whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->sum('qty');

        return self::updateOrCreate(
            ['barang_id' => $barangId],
            [
                'qty' => $qty,
                'created_by' => 1, // default system user
            ]
        );
    }

    /**
     * Recalculates stock balances for all master barang items
     */
    public static function syncAll()
    {
        $barangIds = MasterBarangModel::pluck('id');
        foreach ($barangIds as $barangId) {
            self::recalculate($barangId);
        }
    }
}
