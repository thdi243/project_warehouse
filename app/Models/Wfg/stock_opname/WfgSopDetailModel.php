<?php

namespace App\Models\Wfg\stock_opname;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WfgSopDetailModel extends Model
{
    use HasFactory;

    protected $table = 'wfg_sop_detail';

    protected $fillable = [
        'sop_id',
        'barang_id',
        'qty_full',
        'qty_receh',
        'created_at',
        'updated_at',
    ];

    public function sop()
    {
        return $this->belongsTo(WfgSopModel::class, 'sop_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangWfgModel::class, 'barang_id');
    }
}
