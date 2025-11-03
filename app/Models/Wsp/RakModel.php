<?php

namespace App\Models\Wsp;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Wsp\stock\StockOnHandWspModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RakModel extends Model
{
    use HasFactory;

    protected $table = 'wsp_rak';

    protected $fillable = [
        'area_rak',
        'nama_rak',
        'kolom_rak',
        'level_rak',
        'box_rak',
        'created_by',
    ];

    public function transaksi()
    {
        return $this->hasMany(TransaksiModel::class);
    }

    public function stock()
    {
        return $this->hasMany(StockOnHandWspModel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function barang()
    {
        return $this->hasMany(BarangModel::class, 'rak_id');
    }
}
