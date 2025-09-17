<?php

namespace App\Models\Wsp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RakModel extends Model
{
    use HasFactory;

    protected $table = 'rak';

    protected $fillable = [
        'kode_rak',
        'nama_rak',
        'kolom_rak',
        'level_rak',
        'box_rak'
    ];

    public function transaksi()
    {
        return $this->hasMany(TransaksiModel::class);
    }

    public function stock()
    {
        return $this->hasMany(StockBarangRakModel::class);
    }
}
