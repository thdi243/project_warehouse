<?php

namespace App\Models\Wfg\stock_opname;

use App\Models\User;
use App\Models\Wfg\BarangWfgModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WfgSopTempModel extends Model
{
    use HasFactory;

    protected $table = 'wfg_sop_temp';

    protected $fillable = [
        'soh_id',
        'barang_id',
        'qty_full',
        'qty_receh',
        'summary',
        'created_by',
        'tgl_opname',
        'principal'
    ];

    public function barang()
    {
        return $this->belongsTo(BarangWfgModel::class, 'barang_id');
    }

    public function soh()
    {
        return $this->belongsTo(StockOnHandModel::class, 'soh_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
