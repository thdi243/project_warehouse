<?php

namespace App\Models\Tkbm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TkbmModel extends Model
{
    use HasFactory;

    protected $table = 'tkbm';

    protected $fillable = [
        'date',
        'petugas',
        'shift',
        'qty_terpal',
        'qty_slipsheet',
        'qty_pallet',
        'jml_tkbm',
        'keterangan',
        'total_qty',
        'total_fee',
        'fee_id',
        // 'ppn',
        // 'pph',
    ];

    public function fee()
    {
        return $this->belongsTo(TkbmFeeModel::class, 'fee_id');
    }
}
