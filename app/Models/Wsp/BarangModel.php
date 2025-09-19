<?php

namespace App\Models\Wsp;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangModel extends Model
{
    use HasFactory;
    protected $table = 'barang';

    protected $fillable = [
        'rak_id',
        'user_id',
        'mid_barang',
        'nama_barang',
        'image',
    ];

    public function stock()
    {
        return $this->hasMany(StockOnHandModel::class);
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiModel::class);
    }

    public function rak()
    {
        return $this->belongsTo(RakModel::class, 'rak_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
