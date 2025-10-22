<?php

namespace App\Models\Wfg\stock_opname;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WfgSopApprovalModel extends Model
{
    use HasFactory;

    protected $table = 'wfg_sop_approvals';

    protected $fillable = [
        'sop_id',
        'approver_id',
        'status',
        'catatan'
    ];

    public function sop()
    {
        return $this->belongsTo(WfgSopModel::class, 'sop_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
