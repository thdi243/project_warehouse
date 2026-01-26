<?php

namespace App\Models\Wfg\stock_opname;

use App\Models\User;
use App\Models\NotificationsModel;
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
        'action_at',
        'action_by',
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

    public function notifications()
    {
        return $this->morphMany(NotificationsModel::class, 'notifiable');
    }

    // protected static function booted()
    // {
    //     static::created(function ($approval) {
    //         event(new \App\Events\ShowPortalNotification([
    //             'id' => $approval->id,
    //             'title' => 'Approval Diperlukan',
    //             'message' => 'SOP tanggal ' . $approval->sop->tgl_opname . ' menunggu persetujuan Anda.',
    //             'url' => route('wfg.stock_opname.report') . '?tanggal=' . $approval->sop->tgl_opname .
    //                 '&principal=' . urlencode($approval->sop->principal ?? ''),
    //         ]));
    //     });
    // }
}
