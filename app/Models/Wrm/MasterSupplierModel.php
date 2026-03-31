<?php

namespace App\Models\Wrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSupplierModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_master_suppliers';

    protected $fillable = [
        'nama',
        'lokasi',
        'created_by',
        'updated_by',
    ];
}
