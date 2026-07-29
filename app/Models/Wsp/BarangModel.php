<?php

namespace App\Models\Wsp;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Wsp\stock_manage\StockOnHandWspModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangModel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'wsp_barang';

    protected $fillable = [
        'created_by',
        'mid_barang',
        'nama_barang',
        'uom',
        'qty_pallet',
        'image',
    ];

    public function stock()
    {
        return $this->hasMany(StockOnHandWspModel::class, 'barang_id');
    }

    public function latestStock()
    {
        return $this->hasOne(StockOnHandWspModel::class, 'barang_id')
            ->latestOfMany('last_update');
    }

    public function rak()
    {
        return $this->belongsTo(RakModel::class, 'rak_id');
    }

    public function activeStockLocation()
    {
        return $this->hasOne(\App\Models\Wsp\stock_manage\StockLocationModel::class, 'barang_id')
            ->where('status', 'active');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
