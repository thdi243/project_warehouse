<?php

namespace App\Models\Wfg\stock_opname;

use App\Models\Wfg\BarangWfgModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use App\Models\Wfg\stock_opname\WfgSopModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
