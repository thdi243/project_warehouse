<?php

namespace App\Models\Wfg;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class MasterDestinasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wfg_master_destinasi';

    protected $fillable = [
        'destinasi',
        'active',
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
