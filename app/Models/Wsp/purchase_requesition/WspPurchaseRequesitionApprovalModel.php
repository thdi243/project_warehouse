<?php

namespace App\Models\Wsp\purchase_requesition;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WspPurchaseRequesitionApprovalModel extends Model
{
    protected $table = 'wsp_purchase_requesition_approval';

    protected $fillable = [
        'pr_id',
        'level',
        'role',
        'approver_id',
        'status',
        'action_at',
        'action_by',
        'catatan',
        'ttd',
        'last_wa_follow_up_at'
    ];

    protected $casts = [
        'last_wa_follow_up_at' => 'datetime',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(WspPurchaseRequesitionModel::class, 'pr_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function waLogs()
    {
        return $this->hasMany(WspPrWaLogModel::class, 'approval_id');
    }
}
