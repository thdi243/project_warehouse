<?php

namespace App\Models\Wrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterPalletModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wrm_master_pallet';

    protected $fillable = [
        'jenis_bahan',
        'nama_pallet',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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
