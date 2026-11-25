<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBagianModel extends Model
{
    use HasFactory;

    protected $table = 'master_bagians';

    protected $fillable = [
        'departemen_id',
        'kode',
        'nama',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function departemen()
    {
        return $this->belongsTo(MasterDepartemenModel::class, 'departemen_id');
    }
}
