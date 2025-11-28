<?php

namespace App\Models\Wfg\stock_opname;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WfgSopStatusModel extends Model
{
    use HasFactory;

    protected $table = 'wfg_sop_status';

    protected $fillable = [
        'tgl_opname',
        'user_id',
        'status',
        'mode',
        'principal',
    ];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getMode()
    {
        return self::first()->mode ?? 'normal';
    }
}
