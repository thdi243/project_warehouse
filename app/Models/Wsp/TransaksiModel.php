<?php

namespace App\Models\Wsp;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransaksiModel extends Model
{
    use HasFactory;

    protected $table = 'transaksi_wsp';

    protected $fillable = [
        'barang_id',
        'user_id',
        'qty',
        'tgl_transaksi',
        'jenis_transaksi',
        'keterangan'
    ];

    public function barang()
    {
        return $this->belongsTo(BarangModel::class);
    }

    public function rak()
    {
        return $this->belongsTo(RakModel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stock()
    {
        return $this->belongsTo(StockOnHandModel::class, 'stock_id');
    }
}
