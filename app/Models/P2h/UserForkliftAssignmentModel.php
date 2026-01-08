<?php

namespace App\Models\P2h;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserForkliftAssignmentModel extends Model
{
    protected $table = 'user_forklift_assignments';

    protected $fillable = [
        'user_id',
        'forklift_id',
        'operator_type',
        'assigned_date',
        'assigned_by',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'assigned_date' => 'date',
        'operator_type' => 'integer'
    ];

    /* ================= RELATION ================= */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forklift(): BelongsTo
    {
        return $this->belongsTo(ForkliftModel::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /* ================= SCOPES ================= */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('operator', 1);
    }

    public function scopeBackup($query)
    {
        return $query->where('operator', '>', 1);
    }

    public function scopeByForklift($query, $forkliftId)
    {
        return $query->where('forklift_id', $forkliftId);
    }

    /* ================= HELPERS ================= */

    public function isPrimary(): bool
    {
        return $this->operator === 1;
    }

    public function isBackup(): bool
    {
        return $this->operator > 1;
    }

    public function promoteToPrimary()
    {
        // geser operator lain
        self::where('forklift_id', $this->forklift_id)
            ->where('id', '!=', $this->id)
            ->increment('operator');

        $this->update(['operator' => 1]);
    }

    /* ================= BOOT ================= */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assignment) {
            if (!$assignment->assigned_date) {
                $assignment->assigned_date = Carbon::today();
            }
        });
    }
}
