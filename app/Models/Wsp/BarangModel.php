<?php

namespace App\Models\Wsp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangModel extends Model
{
    use HasFactory;
    protected $table = 'barang';

    protected $fillable = [
        'mid_barang',
        'nama_barang',
        'image',
        'deskripsi'
    ];
    public function stock()
    {
        return $this->hasMany(StockBarangRakModel::class);
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiModel::class);
    }
}
