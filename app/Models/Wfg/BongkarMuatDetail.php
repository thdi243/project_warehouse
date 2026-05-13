<?php

namespace App\Models\Wfg;

use App\Models\Wfg\stock_opname\BarangWfgModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BongkarMuatDetail extends Model
{
    use HasFactory;

    protected $table = 'wfg_bongkar_muat_details';

    protected $fillable = [
        'bongkar_muat_id',
        'material_id',
        'batch_number',
        'jenis',
        'qty',
        'to_dummy',
        'to_sap',
        'double_po',
        'cancel_to',
    ];

    protected $casts = [
        'double_po' => 'boolean',
        'cancel_to' => 'boolean',
    ];

    public function header()
    {
        return $this->belongsTo(BongkarMuat::class, 'bongkar_muat_id');
    }

    public function material()
    {
        return $this->belongsTo(BarangWfgModel::class, 'material_id');
    }
}
