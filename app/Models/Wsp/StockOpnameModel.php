<?php

namespace App\Models\Wsp;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockOpnameModel extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_wsp';

    protected $fillable = [
        'rak_id',
        'barang_id',
        'user_id',
        'stock_sistem',
        'stock_fisik',
        'selisih',
        'keterangan',
        'tgl_opname'
    ];

    public function rak()
    {
        return $this->belongsTo(RakModel::class, 'rak_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
