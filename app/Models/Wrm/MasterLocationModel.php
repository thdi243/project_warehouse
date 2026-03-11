<?php

namespace App\Models\Wrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterLocationModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_master_location';

    protected $fillable = [
        'gudang',
        'bin',
        's_loc',
        'plant',
        'created_by',
        'updated_by',
    ];

    public function barangs()
    {
        return $this->hasMany(MasterBarangModel::class, 'loc_id', 'id');
    }
}
