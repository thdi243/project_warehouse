<?php

namespace App\Models\Wsp\purchase_requesition;

use Illuminate\Database\Eloquent\Model;

class WspPurchaseRequesitionApprovalModel extends Model
{
    protected $table = 'wsp_purchase_requesition_approval';

    protected $fillable = [
        'pr_id',
        'approver_id',
        'status',
        'action_at',
        'action_by',
        'catatan'
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(WspPurchaseRequesitionModel::class, 'pr_id');
    }
}
