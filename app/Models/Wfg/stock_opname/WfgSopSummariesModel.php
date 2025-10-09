<?php

namespace App\Models\Wfg\stock_opname;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WfgSopSummariesModel extends Model
{
    use HasFactory;

    protected $table = 'wfg_sop_summaries';

    protected $fillable = [
        'sop_id',
        'barang_id',
        'qty_fisik',
        'qty_sistem',
        'selisih',
        'keterangan',
        'status'
    ];

    public function sop()
    {
        return $this->belongsTo(WfgSopModel::class, 'sop_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangWfgModel::class, 'barang_id');
    }

    public function soh()
    {
        return $this->belongsTo(StockOnHandModel::class, 'barang_id', 'barang_id');
    }
}
