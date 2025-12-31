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
        'ttd'
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(WspPurchaseRequesitionModel::class, 'pr_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
