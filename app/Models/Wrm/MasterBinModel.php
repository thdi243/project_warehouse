<?php

namespace App\Models\Wrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBinModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_master_bin';

    protected $fillable = [
        'loc_id',
        'kolom',
        'level',
        'created_by',
        'updated_by',
    ];

    public function location()
    {
        return $this->belongsTo(MasterLocationModel::class, 'loc_id', 'id');
    }
}
