<?php

namespace App\Models\Wrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterBarangModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_master_barang';

    protected $fillable = [
        'mid',
        'nama_barang',
        'uom',
        's_loc',
        'plant',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // relasi ke stock gula
    public function stockGula()
    {
        return $this->hasMany(StockGulaModel::class, 'barang_id');
    }

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
