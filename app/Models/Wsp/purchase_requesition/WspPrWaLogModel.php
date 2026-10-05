<?php

namespace App\Models\Wsp\purchase_requesition;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WspPrWaLogModel extends Model
{
    protected $table = 'wsp_pr_wa_logs';

    protected $fillable = [
        'pr_id',
        'approval_id',
        'user_id',
        'phone_number',
        'status',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pr()
    {
        return $this->belongsTo(WspPurchaseRequesitionModel::class, 'pr_id');
    }

    public function approval()
    {
        return $this->belongsTo(WspPurchaseRequesitionApprovalModel::class, 'approval_id');
    }
}
