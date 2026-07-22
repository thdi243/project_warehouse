<?php

namespace App\Models\Wsp\StockOpname;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WspSoStatusModel extends Model
{
    use HasFactory;

    protected $table = 'wsp_so_status';

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
