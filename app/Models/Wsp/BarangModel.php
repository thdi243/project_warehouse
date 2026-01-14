<?php

namespace App\Models\Wsp;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Wsp\stock_manage\StockOnHandWspModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangModel extends Model
{
    use HasFactory;
    protected $table = 'wsp_barang';

    protected $fillable = [
        'created_by',
        'mid_barang',
        'nama_barang',
        'uom',
        's_loc',
        'image',
    ];

    public function stock()
    {
        return $this->hasMany(StockOnHandWspModel::class);
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
        return $this->belongsTo(User::class, 'created_by');
    }
}
