<?php

namespace App\Models\Tkbm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TotalsTkbmModel extends Model
{
    use HasFactory;

    protected $table = 'totals_tkbm';

    protected $fillable = [
        'month',
        'year',
        'tkbm_id',
        'total_terpal',
        'total_slipsheet',
        'total_pallet',
        'total_produk',
        'total_fee',
        'total_ppn',
        'total_pph',
        'grand_total',
    ];

    public function tkbm()
    {
        return $this->belongsTo(TkbmModel::class, 'tkbm_id');
    }
}
