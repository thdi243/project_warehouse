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

    public static function getModeByUser($userId, $principal, $tglOpname = null)
    {
        $tglOpname = $tglOpname ?? now()->toDateString();

        return self::where('user_id', $userId)
            ->where('principal', $principal)
            ->whereDate('tgl_opname', $tglOpname)  // 🔹 Tambahkan ini!
            ->value('mode') ?? 'normal';
    }
}
