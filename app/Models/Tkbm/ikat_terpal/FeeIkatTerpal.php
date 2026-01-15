<?php

namespace App\Models\Tkbm\ikat_terpal;

use Illuminate\Database\Eloquent\Model;

class FeeIkatTerpal extends Model
{
    protected $table = 'tkbm_fee_ikat_terpal';

    protected $fillable = [
        'fee',
        'ppn',
        'pph',
        'keterangan',
        'aktif',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
