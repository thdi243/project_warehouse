<?php

namespace App\Models\Tkbm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TkbmFeeModel extends Model
{
    use HasFactory;

    protected $table = 'tkbm_fee';

    protected $fillable = [
        'fee',
        'ppn',
        'pph',
    ];

    public function tkbmRecords()
    {
        return $this->hasMany(TkbmModel::class, 'fee_id');
    }
}
