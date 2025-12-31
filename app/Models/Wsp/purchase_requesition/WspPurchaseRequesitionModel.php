<?php

namespace App\Models\Wsp\purchase_requesition;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WspPurchaseRequesitionModel extends Model
{
    protected $table = 'wsp_purchase_requesition';

    protected $fillable = [
        'pr_number',
        'pr_date',
        'hal',
        'no_doc',
        'requested_by',
        'department',
        'jenis',
        'detail_jenis',
        'no_io',
        'status',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(WspPurchaseRequesitionItemsModel::class, 'pr_id');
    }

    public function approval()
    {
        return $this->hasMany(WspPurchaseRequesitionApprovalModel::class, 'pr_id');
    }
}
