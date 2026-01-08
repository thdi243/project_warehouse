<?php

namespace App\Models\P2h;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PalletAssignmentModel extends Model
{
    use HasFactory;

    protected $table = 'user_pallet_assignments';

    protected $fillable = [
        'pallet_mover_id',
        'user_id',
        'assigned_by',
        'assigned_date',
        'operator_type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'assigned_date' => 'date',
        'operator_type' => 'integer'
    ];

    /* ================= RELATIONS ================= */

    public function palletMover(): BelongsTo
    {
        return $this->belongsTo(PalletMoverModel::class, 'pallet_mover_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->assignedBy();
    }

    /* ================= SCOPES ================= */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopePrimary($query)
    {
        return $query->where('operator', 1);
    }

    public function scopeBackup($query)
    {
        return $query->where('operator', '>', 1);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPallet($query, $palletId)
    {
        return $query->where('pallet_mover_id', $palletId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('assigned_date', Carbon::today());
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('assigned_date', [$start, $end]);
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

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Jadikan assignment ini sebagai operator utama
     * operator lain otomatis geser ke bawah
     */
    public function promoteToPrimary()
    {
        self::where('pallet_mover_id', $this->pallet_mover_id)
            ->where('id', '!=', $this->id)
            ->increment('operator');

        $this->update(['operator' => 1]);
    }

    public function getDurationInDays(): int
    {
        return Carbon::parse($this->assigned_date)->diffInDays(now());
    }

    public function getFormattedAssignedDate(): string
    {
        return $this->assigned_date->format('d M Y');
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
