<?php

namespace App\Models\Wrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterSupplierModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wrm_master_suppliers';

    protected $fillable = [
        'nama',
        'lokasi',
        'created_by',
        'updated_by',
    ];

    // user pembuat
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // user pengupdate
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
