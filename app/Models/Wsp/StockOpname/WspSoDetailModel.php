<?php

namespace App\Models\Wsp\StockOpname;

use App\Models\Wsp\StockOpname\WspSoModel;
use App\Models\Wsp\BarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WspSoDetailModel extends Model
{
    use HasFactory;

    protected $table = 'wsp_so_detail';

    protected $fillable = [
        'so_id',
        'barang_id',
        'qty_full',
        'qty_receh',
        'created_at',
    ];

    public function so()
    {
        return $this->belongsTo(WspSoModel::class, 'so_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangModel::class, 'barang_id');
    }
}
