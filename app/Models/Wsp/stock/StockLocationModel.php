<?php

namespace App\Models\Wsp\stock;

use App\Models\User;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\RakModel;
use Illuminate\Database\Eloquent\Model;

class StockLocationModel extends Model
{
    protected $table = 'wsp_stock_location';

    protected $fillable = [
        'barang_id',
        'rak_id',
        'status',
        'created_by',
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }

    public function rak()
    {
        return $this->belongsTo(RakModel::class, 'rak_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
