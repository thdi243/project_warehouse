<?php

namespace App\Models\Wrm\StockOpname;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrmSoStatusModel extends Model
{
    use HasFactory;

    protected $table = 'wrm_so_status';

    protected $fillable = [
        'user_id',
        'tgl_opname',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
