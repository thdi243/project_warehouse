<?php

namespace App\Models\Wrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterLocationModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_master_location';

    protected $fillable = [
        'plant',
        's_loc',
        'gudang',
        'zona',
        'bin',
        'created_by',
        'updated_by',
    ];

    public function bins()
    {
        return $this->hasMany(MasterBinModel::class, 'loc_id', 'id');
    }

    public function barangs()
    {
        return $this->hasMany(MasterBarangModel::class, 'loc_id', 'id');
    }
}
