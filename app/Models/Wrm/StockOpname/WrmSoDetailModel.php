<?php

namespace App\Models\Wrm\StockOpname;

use App\Models\Wrm\MasterBarangModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrmSoDetailModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_so_detail';

    protected $fillable = [
        'so_id',
        'barang_id',
        'no_spb',
        'qty_full',
        'qty_receh',
        'created_at',
    ];

    public function so()
    {
        return $this->belongsTo(WrmSoModel::class, 'so_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'barang_id');
    }
}
