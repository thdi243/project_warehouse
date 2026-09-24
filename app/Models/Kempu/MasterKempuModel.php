<?php

namespace App\Models\Kempu;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterKempuModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kempu_master';

    // Konstanta Lokasi
    const LOC_WPM          = 'WPM';
    const LOC_QC_PM        = 'QC_PM';
    const LOC_ENG          = 'ENGINEERING_WORKSHOP';
    const LOC_PRODUKSI     = 'PRODUKSI';
    const LOC_QC_PROSES    = 'QC_PROSES';
    const LOC_WFG          = 'WFG';
    const LOC_PAS          = 'WAREHOUSE_PAS';
    const LOC_SCRAP        = 'SCRAP';

    // Konstanta Status
    const STATUS_GR_COMPLETED       = 'GR_COMPLETED';
    const STATUS_QC_PM_PENDING      = 'QC_PM_PENDING';
    const STATUS_QC_PM_PASSED       = 'QC_PM_PASSED';
    const STATUS_ENG_REPAIR         = 'ENG_REPAIR';
    const STATUS_ENG_SCRAP_PROD     = 'ENG_SCRAP_PRODUKSI';
    const STATUS_IN_TRANSIT_PROD    = 'IN_TRANSIT_PRODUKSI';
    const STATUS_PROD_RECEIVED      = 'PROD_RECEIVED';
    const STATUS_QC_PROD_PENDING    = 'QC_PROD_PENDING';
    const STATUS_QC_PROD_PASSED     = 'QC_PROD_PASSED';
    const STATUS_SCAN1_FILLED       = 'SCAN1_FILLED';
    const STATUS_IN_TRANSIT_WFG     = 'IN_TRANSIT_WFG';
    const STATUS_WFG_RECEIVED       = 'WFG_RECEIVED';
    const STATUS_WFG_REJECT_PROD    = 'WFG_REJECT_PRODUKSI';
    const STATUS_SCAN2_PENDING      = 'SCAN2_PENDING';
    const STATUS_SCAN2_PASSED       = 'SCAN2_PASSED';
    const STATUS_QC_SCAN2_PENDING   = 'QC_SCAN2_PENDING';
    const STATUS_NTI_PRODUKSI       = 'NTI_PRODUKSI';
    const STATUS_FG_PICKED          = 'FG_PICKED';
    const STATUS_IN_TRANSIT_PAS     = 'IN_TRANSIT_PAS';
    const STATUS_PAS_RECEIVED       = 'PAS_RECEIVED';
    const STATUS_IN_TRANSIT_WPM     = 'IN_TRANSIT_WPM';
    const STATUS_SCRAPPED           = 'SCRAPPED';

    protected $fillable = [
        'id_kempu',
        'rfid',
        'status',
        'keterangan',
        'gr_date',
        'no_spb',
        'print_count',
        'printed_at',
        'printed_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'gr_date'     => 'date',
        'printed_at'  => 'datetime',
        'print_count' => 'integer',
    ];

    protected $appends = [
        'current_location',
        'current_status',
        'reused_count',
        'max_reused',
        'condition',
        'last_scanned_at',
        'last_action',
        'is_old_kempu',
    ];

    protected static function booted()
    {
        // Auto create kempu_main saat master kempu baru dibuat
        static::created(function ($kempu) {
            if (!$kempu->main()->exists()) {
                $kempu->main()->create([
                    'id_kempu'         => $kempu->id_kempu,
                    'current_location' => self::LOC_WPM,
                    'current_status'   => self::STATUS_QC_PM_PENDING,
                    'reused_count'     => 0,
                    'max_reused'       => 21,
                    'condition'        => 'OK',
                ]);
            }
        });

        // Sinkronisasi status SCRAP ke kempu_main
        static::saved(function ($kempu) {
            if ($kempu->status === 'scrap' || $kempu->status === 'damaged') {
                if ($kempu->main) {
                    $kempu->main->update([
                        'current_status'   => self::STATUS_SCRAPPED,
                        'current_location' => self::LOC_SCRAP,
                        'condition'        => 'NOT_OK',
                    ]);
                }
            }
        });
    }

    /**
     * Relasi 1-to-1 ke data live operasional kempu
     */
    public function main()
    {
        return $this->hasOne(KempuMainModel::class, 'kempu_master_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function printedBy()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function trackingHistories()
    {
        return $this->hasMany(KempuTrackingHistoryModel::class, 'kempu_master_id')->latest('id');
    }

    // =========================================================================
    // ACCESSOR & MUTATOR DELEGATION KE KEMPU_MAIN (SEAMLESS COMPATIBILITY)
    // =========================================================================

    public function getCurrentLocationAttribute()
    {
        return $this->main?->current_location ?? self::LOC_WPM;
    }

    public function setCurrentLocationAttribute($value)
    {
        if ($this->main) {
            $this->main->current_location = $value;
        }
    }

    public function getCurrentStatusAttribute()
    {
        return $this->main?->current_status ?? self::STATUS_QC_PM_PENDING;
    }

    public function setCurrentStatusAttribute($value)
    {
        if ($this->main) {
            $this->main->current_status = $value;
        }
    }

    public function getReusedCountAttribute()
    {
        return $this->main?->reused_count ?? 0;
    }

    public function setReusedCountAttribute($value)
    {
        if ($this->main) {
            $this->main->reused_count = $value;
        }
    }

    public function getMaxReusedAttribute()
    {
        return $this->main?->max_reused ?? 21;
    }

    public function setMaxReusedAttribute($value)
    {
        if ($this->main) {
            $this->main->max_reused = $value;
        }
    }

    public function getConditionAttribute()
    {
        return $this->main?->condition ?? 'OK';
    }

    public function setConditionAttribute($value)
    {
        if ($this->main) {
            $this->main->condition = $value;
        }
    }

    public function getLastScannedAtAttribute()
    {
        return $this->main?->last_scanned_at;
    }

    public function setLastScannedAtAttribute($value)
    {
        if ($this->main) {
            $this->main->last_scanned_at = $value;
        }
    }

    public function getLastActionAttribute()
    {
        return $this->main?->last_action;
    }

    public function setLastActionAttribute($value)
    {
        if ($this->main) {
            $this->main->last_action = $value;
        }
    }

    /**
     * Mengenali apakah ID Kempu bertipe format lama (bukan format standar 10 digit angka sistem)
     */
    public function getIsOldKempuAttribute(): bool
    {
        return self::isOldKempu($this->id_kempu);
    }

    public static function isOldKempu(?string $idKempu): bool
    {
        if (empty($idKempu)) {
            return false;
        }
        // Format baru standar sistem adalah 10 digit angka (cth: DDMMYY#### atau YYMMDD####)
        return !preg_match('/^\d{10}$/', trim($idKempu));
    }

    /**
     * Memeriksa apakah kempu sudah pernah melewati proses di stasiun WFG
     */
    public function hasPassedWfg(): bool
    {
        // 1. Cek dari tracking history: apakah pernah tercatat aksi di stage WFG, to_location WFG/PAS, atau action WFG_
        $hasWfgHistory = $this->trackingHistories()
            ->where(function ($q) {
                $q->where('stage', self::LOC_WFG)
                    ->orWhere('from_location', self::LOC_WFG)
                    ->orWhere('to_location', self::LOC_WFG)
                    ->orWhere('to_location', self::LOC_PAS)
                    ->orWhere('action', 'LIKE', 'WFG_%');
            })
            ->exists();

        if ($hasWfgHistory) {
            return true;
        }

        // 2. Cek dari lokasi saat ini
        if (in_array($this->current_location, [
            self::LOC_WFG,
            self::LOC_PAS,
        ])) {
            return true;
        }

        // 3. Cek dari status operasional
        $wfgPassedStatuses = [
            self::STATUS_WFG_RECEIVED,
            self::STATUS_SCAN2_PENDING,
            self::STATUS_SCAN2_PASSED,
            self::STATUS_FG_PICKED,
            self::STATUS_IN_TRANSIT_PAS,
            self::STATUS_PAS_RECEIVED,
            self::STATUS_IN_TRANSIT_WPM,
        ];

        if (in_array($this->current_status, $wfgPassedStatuses)) {
            return true;
        }

        return false;
    }

    public function isMaxReused(): bool
    {
        return ($this->reused_count ?? 0) >= ($this->max_reused ?? 21);
    }

    /**
     * Catat log riwayat pergerakan / aksi kempu
     */
    public function recordTracking(
        string $stage,
        string $action,
        ?string $actionResult = 'OK',
        ?string $fromLocation = null,
        ?string $toLocation = null,
        string $condition = 'OK',
        ?string $notes = null,
        ?int $userId = null,
        ?array $metadata = null
    ) {
        return $this->trackingHistories()->create([
            'id_kempu'       => $this->id_kempu,
            'stage'          => $stage,
            'action'         => $action,
            'action_result'  => $actionResult,
            'from_location'  => $fromLocation ?? $this->current_location,
            'to_location'    => $toLocation ?? $this->current_location,
            'reused_count'   => $this->reused_count,
            'condition'      => $condition,
            'notes'          => $notes,
            'metadata'       => $metadata,
            'created_by'     => $userId,
        ]);
    }
}
