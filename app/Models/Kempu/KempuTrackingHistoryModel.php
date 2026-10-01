<?php

namespace App\Models\Kempu;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KempuTrackingHistoryModel extends Model
{
    use HasFactory;

    protected $table = 'kempu_tracking_history';

    protected $fillable = [
        'kempu_master_id',
        'id_kempu',
        'stage',
        'action',
        'action_result',
        'from_location',
        'to_location',
        'reused_count',
        'condition',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'reused_count' => 'integer',
        'metadata'     => 'array',
    ];

    protected $appends = [
        'operator_display_name',
    ];

    public function getOperatorDisplayNameAttribute(): string
    {
        return $this->createdBy?->nama_lengkap
            ?? $this->createdBy?->username
            ?? $this->metadata['operator_name']
            ?? $this->metadata['operator_email']
            ?? 'System';
    }

    public function masterKempu()
    {
        return $this->belongsTo(MasterKempuModel::class, 'kempu_master_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
