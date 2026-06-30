<?php

namespace App\Models\Wcp\StockOpname;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WcpSoStatusModel extends Model
{
    use HasFactory;

    protected $table = 'wcp_so_status';

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
