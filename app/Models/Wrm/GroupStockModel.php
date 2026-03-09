<?php

namespace App\Models\Wrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupStockModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_group_stock';

    protected $fillable = [
        'group',
        'created_by',
        'updated_by'
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
