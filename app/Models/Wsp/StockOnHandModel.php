<?php

namespace App\Models\Wsp;

use Illuminate\Database\Eloquent\Model;

class StockOnHandModel extends Model
{
    protected $table = 'stock_on_hand';

    protected $fillable = [
        'barang_id',
        'qty',
        'last_updated',
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiModel::class, 'stock_id', 'barang_id', 'rak_id');
    }
}
