<?php

namespace App\Models\Wfg\stock_opname;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WfgSopModel extends Model
{
    use HasFactory;

    protected $table = 'wfg_sop';

    protected $fillable = [
        'tgl_opname',
        'user_id',
        'status',
        'principal',
        'no_doc'
    ];

    public function details()
    {
        return $this->hasMany(WfgSopDetailModel::class, 'sop_id');
    }

    public function summaries()
    {
        return $this->hasMany(WfgSopSummariesModel::class, 'sop_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvals()
    {
        return $this->hasMany(WfgSopApprovalModel::class, 'sop_id');
    }
}
