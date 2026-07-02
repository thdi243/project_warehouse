<?php

namespace App\Models\Wpm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpmMasterBarangModel extends Model
{
    use HasFactory;

    protected $table = 'wpm_master_barang';

    protected $fillable = [
        'mid',
        'nama_barang',
        'uom',
        'qty_pallet',
        'created_by',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
