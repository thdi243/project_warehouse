<?php

namespace App\Models\Wfg\stock_opname;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarangWfgModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wfg_barang';

    protected $fillable = [
        'mid_barang',
        'nama_barang',
        'qty_box',
        'tipe_kemasan',
        'status',
        'satuan',
        'gambar'
    ];

    public function stockOnHand()
    {
        return $this->hasOne(StockOnHandModel::class, 'barang_id');
    }
}
