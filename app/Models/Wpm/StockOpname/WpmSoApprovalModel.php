<?php

namespace App\Models\Wpm\StockOpname;

use App\Models\User;
use App\Models\NotificationsModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WpmSoApprovalModel extends Model
{
    use HasFactory;

    protected $table = 'wpm_so_approvals';

    protected $fillable = [
        'so_id',
        'approver_id',
        'status',
        'action_at',
        'action_by',
        'catatan'
    ];

    public function so()
    {
        return $this->belongsTo(WpmSoModel::class, 'so_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function notifications()
    {
        return $this->morphMany(NotificationsModel::class, 'notifiable');
    }
}
