<?php

namespace App\Models\Wsp;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RakModel extends Model
{
    use HasFactory;

    protected $table = 'rak';

    protected $fillable = [
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barang()
    {
        return $this->hasMany(BarangModel::class, 'rak_id');
    }
}
