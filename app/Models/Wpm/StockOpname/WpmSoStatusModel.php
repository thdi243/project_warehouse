<?php

namespace App\Models\Wpm\StockOpname;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpmSoStatusModel extends Model
{
    use HasFactory;

    protected $table = 'wpm_so_status';

    protected $fillable = [
        'user_id',
        'tgl_opname',
        'status',
        'jenis_so',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
