<?php

namespace App\Models\Wrm\Inventory;

use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterLocationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    use HasFactory;

    protected $table = 'wrm_stock_balance';

    protected $fillable = [
        'barang_id',
        'loc_id',
        'qty',
        'created_by',
        'updated_by',
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }

    public function location()
    {
        return $this->belongsTo(MasterLocationModel::class, 'loc_id');
    }
}
