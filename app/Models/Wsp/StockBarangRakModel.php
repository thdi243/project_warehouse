<?php

namespace App\Models\Wsp;

use Illuminate\Database\Eloquent\Model;

class StockBarangRakModel extends Model
{
    protected $table = 'stock_barang_rak';

    protected $fillable = [
        'barang_id',
        'rak_id',
        'stock',
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }

    public function rak()
    {
        return $this->belongsTo(RakModel::class, 'rak_id');
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiModel::class, 'stock_id', 'barang_id', 'rak_id');
    }
}
