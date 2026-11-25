<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDepartemenModel extends Model
{
    use HasFactory;

    protected $table = 'master_departemens';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bagians()
    {
        return $this->hasMany(MasterBagianModel::class, 'departemen_id');
    }
}
