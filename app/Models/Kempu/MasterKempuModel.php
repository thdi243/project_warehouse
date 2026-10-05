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

    // =========================================================================
    // KONSTANTA STATUS TERSTANDARISASI SESUAI FLOW & CARD SETIAP AREA
    // =========================================================================

    // 1. Master Data & Good Receipt (WPM)
    const STATUS_REGISTERED               = 'REGISTERED';
    const STATUS_GR_COMPLETED             = 'GR_COMPLETED';

    // 2. QC Packaging Material (QC PM)
    const STATUS_QC_PM_PENDING            = 'QC_PM_PENDING';
    const STATUS_QC_PM_RELEASE            = 'QC_PM_RELEASE';
    const STATUS_QC_PM_HOLD               = 'QC_PM_HOLD';

    // 3. Engineering Workshop (Repair)
    const STATUS_ENG_REPAIR               = 'ENG_REPAIR';
    const STATUS_ENG_SCRAP_PROD           = 'ENG_SCRAP_PRODUKSI';

    // 4. Warehouse Packaging Material (WPM) Cards
    const STATUS_WPM_TRANSFER_OUT_PROD    = 'WPM_TRANSFER_OUT_TO_PROD';
    const STATUS_WPM_TRANSFER_IN_PAS      = 'WPM_TRANSFER_IN_FROM_PAS';

    // 5. Produksi Cards
    const STATUS_PROD_TRANSFER_IN_WPM     = 'PROD_TRANSFER_IN_FROM_WPM';
    const STATUS_PROD_CUCI_KEMPU          = 'PROD_CUCI_KEMPU';
    const STATUS_PROD_FILLING_KEMPU       = 'PROD_FILLING_KEMPU';
    const STATUS_PROD_TRANSFER_OUT_WFG    = 'PROD_TRANSFER_OUT_TO_WFG';
    const STATUS_PROD_REPRO_KEMPU         = 'PROD_REPRO_KEMPU';
    const STATUS_PROD_TRANSFER_IN_WFG     = 'PROD_TRANSFER_IN_FROM_WFG';
    const STATUS_SCRAPPED                 = 'SCRAPPED';

    // 6. QC Proses (Pre Cuci & After Filling)
    const STATUS_QC_PRE_CUCI_PENDING      = 'QC_PRE_CUCI_PENDING';
    const STATUS_QC_PRE_CUCI_RELEASE      = 'QC_PRE_CUCI_RELEASE';
    const STATUS_QC_PRE_CUCI_HOLD         = 'QC_PRE_CUCI_HOLD';
    const STATUS_QC_AFTER_FILLING_PENDING = 'QC_AFTER_FILLING_PENDING';
    const STATUS_QC_AFTER_FILLING_RELEASE = 'QC_AFTER_FILLING_RELEASE';
    const STATUS_QC_AFTER_FILLING_HOLD    = 'QC_AFTER_FILLING_HOLD';
    const STATUS_QC_AFTER_FILLING_REPRO   = 'QC_AFTER_FILLING_REPRO';
    const STATUS_QC_AFTER_FILLING_REJECT  = 'QC_AFTER_FILLING_REJECT';

    // 7. Warehouse Finished Goods (WFG) Cards
    const STATUS_WFG_TRANSFER_IN_PROD     = 'WFG_TRANSFER_IN_FROM_PROD';
    const STATUS_WFG_TRANSFER_OUT_PAS     = 'WFG_TRANSFER_OUT_TO_PAS';
    const STATUS_WFG_REJECT_PROD          = 'WFG_REJECT_FROM_PROD';

    // 8. Warehouse PT PAS Cards
    const STATUS_PAS_TRANSFER_IN_BAS      = 'PAS_TRANSFER_IN_FROM_BAS';
    const STATUS_PAS_TRANSFER_OUT_BAS     = 'PAS_TRANSFER_OUT_TO_BAS';

    // Alias Backward Compatibility
    const STATUS_QC_PM_PASSED             = 'QC_PM_RELEASE';
    const STATUS_QC_PRE_CUCI_PASSED       = 'QC_PRE_CUCI_RELEASE';
    const STATUS_QC_AFTER_FILLING_PASSED  = 'QC_AFTER_FILLING_RELEASE';
    const STATUS_IN_TRANSIT_PROD          = 'WPM_TRANSFER_OUT_FROM_PROD';
    const STATUS_PROD_RECEIVED            = 'PROD_TRANSFER_IN_FROM_WPM';
    const STATUS_CUCI_KEMPU_COMPLETED     = 'PROD_CUCI_KEMPU';
    const STATUS_SCAN1_FILLED             = 'PROD_FILLING_KEMPU';
    const STATUS_IN_TRANSIT_WFG           = 'PROD_TRANSFER_OUT_FROM_WFG';
    const STATUS_WFG_RECEIVED             = 'WFG_TRANSFER_IN_FROM_PROD';
    const STATUS_IN_TRANSIT_PAS           = 'WFG_TRANSFER_OUT_TO_PAS';
    const STATUS_PAS_RECEIVED             = 'PAS_TRANSFER_IN_FROM_BAS';
    const STATUS_IN_TRANSIT_WPM           = 'PAS_TRANSFER_OUT_TO_BAS';
    const STATUS_NTI_PRODUKSI             = 'ENG_SCRAP_PRODUKSI';
    const STATUS_QC_PROD_PENDING          = 'QC_PRE_CUCI_PENDING';
    const STATUS_QC_PROD_PASSED           = 'QC_PRE_CUCI_RELEASE';

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

        // Sinkronisasi status SCRAP / NONAKTIF ke kempu_main
        static::saved(function ($kempu) {
            if ($kempu->status === 'scrap' || $kempu->status === 'damaged' || $kempu->status === 'nonaktif') {
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

    public function getHasBarcodeAttribute()
    {
        return (bool)($this->main?->has_barcode ?? true);
    }

    public function getHasRfidAttribute()
    {
        return (bool)($this->main?->has_rfid ?? true);
    }

    public function getHasKitirAttribute()
    {
        return (bool)($this->main?->has_kitir ?? true);
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

        $clean = trim($idKempu);

        // Format baru standar sistem adalah 10 digit angka dengan awalan tanggal YYMMDD (YYMMDD####)
        if (!preg_match('/^\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{4}$/', $clean)) {
            return true;
        }

        $yy = (int) substr($clean, 0, 2);
        $mm = (int) substr($clean, 2, 2);
        $dd = (int) substr($clean, 4, 2);

        // Validasi kebenaran kalender tanggal (misal menghindari tanggal tidak valid)
        if (!checkdate($mm, $dd, 2000 + $yy)) {
            return true;
        }

        return false;
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
