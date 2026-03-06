<?php

namespace App\Models\Wsp\purchase_requesition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WspPurchaseRequesitionItemApprovalModel extends Model
{
    use HasFactory;

    protected $table = 'wsp_purchase_requesition_item_approval';

    protected $fillable = [
        'pr_item_id',
        'approval_id',
        'status',
        'catatan',
    ];

    public function prItem()
    {
        return $this->belongsTo(WspPurchaseRequesitionItemsModel::class, 'pr_item_id');
    }

    public function approval()
    {
        return $this->belongsTo(WspPurchaseRequesitionApprovalModel::class, 'approval_id');
    }
}
