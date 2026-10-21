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
    const LOC_IN_TRANSIT   = 'IN_TRANSIT';

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
    const STATUS_WFG_RETUR_FROM_PAS       = 'WFG_RETUR_FROM_PAS';

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

        // Otomatis ubah status menjadi nonaktif dan update kempu_main saat di-soft delete
        static::deleting(function ($kempu) {
            if (!$kempu->isForceDeleting()) {
                $kempu->status = 'nonaktif';
                $kempu->saveQuietly();

                if ($kempu->main) {
                    $kempu->main->update([
                        'current_status'   => self::STATUS_SCRAPPED,
                        'current_location' => self::LOC_SCRAP,
                        'condition'        => 'NOT_OK',
                        'last_action'      => 'Dinonaktifkan / Dihapus',
                    ]);
                }
            }
        });

        // Otomatis pulihkan status menjadi active dan update kempu_main saat di-restore
        static::restoring(function ($kempu) {
            $kempu->status = 'active';
            $kempu->saveQuietly();

            if ($kempu->main) {
                $kempu->main->update([
                    'current_status'   => self::STATUS_QC_PM_PENDING,
                    'current_location' => self::LOC_WPM,
                    'condition'        => 'OK',
                    'last_action'      => 'Dipulihkan dari Nonaktif',
                ]);
            }
        });
    }

    /**
     * Accessor untuk memastikan status selalu 'nonaktif' jika record di-soft delete
     */
    public function getStatusAttribute($value)
    {
        if ($this->trashed()) {
            return 'nonaktif';
        }
        return $value;
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

    public function cycleFillings()
    {
        return $this->hasMany(KempuCycleFillingModel::class, 'kempu_master_id')->orderBy('reused_count', 'desc');
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

    public function getCurrentCycleFillingAttribute()
    {
        return $this->cycleFillings?->firstWhere('reused_count', $this->reused_count);
    }

    public function getHasBarcodeAttribute()
    {
        return (bool)($this->current_cycle_filling?->has_barcode ?? $this->main?->has_barcode ?? true);
    }

    public function getHasRfidAttribute()
    {
        return (bool)($this->current_cycle_filling?->has_rfid ?? $this->main?->has_rfid ?? true);
    }

    public function getHasNtiAttribute()
    {
        return (bool)($this->current_cycle_filling?->has_nti ?? $this->main?->has_nti ?? true);
    }

    public function getHasKitirAttribute()
    {
        return $this->has_nti;
    }

    public function getNoPoAttribute()
    {
        return $this->main?->no_po;
    }

    public function getFoto1Attribute()
    {
        return $this->main?->foto_1;
    }

    public function getFoto2Attribute()
    {
        return $this->main?->foto_2;
    }

    public function getFoto3Attribute()
    {
        return $this->main?->foto_3;
    }

    public function getFoto4Attribute()
    {
        return $this->main?->foto_4;
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

    /**
     * Cek apakah user berhak mengetik ID kempu secara manual (Bukan operator biasa / memiliki wewenang)
     * Otoritas: role != 'operator' ATAU memiliki permission 'kempu-manual-input' / 'super-admin'
     */
    public static function canManualInput($user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }

        if (method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission(['kempu-manual-input', 'super-admin'])) {
            return true;
        }

        // Cek roles relasi tabel
        if (method_exists($user, 'roles') && $user->roles()->exists()) {
            $roles = $user->roles()->pluck('name')->map(fn($r) => strtolower(trim($r)))->toArray();
            foreach ($roles as $r) {
                if ($r !== 'operator') {
                    return true;
                }
            }
            if (in_array('operator', $roles)) {
                return false;
            }
        }

        // Cek atribut string role
        $role = strtolower(trim($user->role ?? ''));
        if ($role && $role !== 'operator') {
            return true;
        }

        if ($role === 'operator') {
            return false;
        }

        $jabatan = strtolower(trim($user->jabatan ?? ''));
        if ($jabatan && str_contains($jabatan, 'operator') && !str_contains($jabatan, 'leader') && !str_contains($jabatan, 'foreman') && !str_contains($jabatan, 'spv')) {
            return false;
        }

        return true;
    }

    /**
     * Dapatkan pesan petunjuk alur berikutnya berdasarkan status kempu saat ini.
     * Mengikuti rantai urutan siklus operasional kempu secara utuh dan presisi.
     */
    public static function getNextRequiredStepMessage(?string $currentStatus, ?string $currentLocation = null): ?string
    {
        $status = trim($currentStatus ?? '');
        $location = strtoupper(trim($currentLocation ?? ''));

        if (empty($status)) {
            return null;
        }

        $clean = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', $status));

        // 1. Scrap / Afkir / Nonaktif
        if (
            in_array($clean, ['SCRAPPED', 'SCRAP', 'NONAKTIF', 'AFKIR', 'DAMAGED']) ||
            str_contains($clean, 'SCRAP') ||
            str_contains($clean, 'NONAKTIF') ||
            $location === strtoupper(self::LOC_SCRAP)
        ) {
            return "Flow Salah! Kempu sudah berstatus SCRAP / Nonaktif dan tidak dapat diproses lagi.";
        }

        // 2. Engineering Workshop / Perbaikan / Reject
        if (
            in_array($clean, ['ENG_REPAIR', 'ENG_SCRAP_PRODUKSI', 'REPAIR', 'MAINTENANCE', 'QC_PM_REJECT', 'QC_PROSES_REJECT', 'NOT_OK']) ||
            str_contains($clean, 'REPAIR') ||
            str_contains($clean, 'MAINTENANCE') ||
            $location === strtoupper(self::LOC_ENG) ||
            $location === 'ENG'
        ) {
            return "Flow Salah! Kempu belum scan Repair oleh Tim Engineering.";
        }

        // 3. Status Hold oleh QC
        if (str_contains($clean, 'PM_HOLD')) {
            return "Flow Salah! Kempu masih ditahan (HOLD) oleh Tim QC PM.";
        }
        if (str_contains($clean, 'HOLD')) {
            return "Flow Salah! Kempu masih ditahan (HOLD) oleh Tim QC.";
        }

        // 4. Status Reject QC After Filling
        if (str_contains($clean, 'AFTER_FILLING') && str_contains($clean, 'REJECT')) {
            return "Flow Salah! Kempu berstatus REJECT oleh Tim QC.";
        }

        // 5. Repro Flow
        if (str_contains($clean, 'AFTER_FILLING') && str_contains($clean, 'REPRO')) {
            return "Flow Salah! Kempu belum scan Repro Kempu oleh Tim Produksi.";
        }
        if (str_contains($clean, 'REPRO')) {
            return "Flow Salah! Kempu belum scan QC Pre Cuci oleh Tim QC.";
        }

        // 6. Retur Flow dari PAS
        if (
            in_array($clean, ['PAS_RETUR_TO_WFG', 'RETUR_TO_WFG', 'PAS_RETUR_TO_BAS_ISI']) ||
            (str_contains($clean, 'RETUR') && str_contains($clean, 'BERISI'))
        ) {
            return "Flow Salah! Kempu belum scan Penerimaan Retur dari PAS oleh Tim WFG.";
        }
        if (
            in_array($clean, ['WFG_RETUR_FROM_PAS', 'RETUR_FROM_PAS']) ||
            (str_contains($clean, 'RETUR') && str_contains($clean, 'WFG'))
        ) {
            return "Flow Salah! Kempu belum scan QC Retur oleh Tim QC.";
        }

        // 7. Pendaftaran Awal / GR di WPM (Belum lolos QC PM)
        if (
            in_array($clean, ['REGISTERED', 'GR_COMPLETED', 'QC_PM_PENDING']) ||
            str_contains($clean, 'REGISTER') ||
            str_contains($clean, 'GR_COMPLETED') ||
            str_contains($clean, 'PM_PENDING')
        ) {
            return "Flow Salah! Kempu belum scan QC PM oleh Tim QC.";
        }

        // 8. Lolos QC PM (Di WPM, siap dikirim ke Produksi)
        if (
            in_array($clean, ['QC_PM_RELEASE', 'QC_PM_PASSED', 'RELEASE_QC_PM']) ||
            (str_contains($clean, 'QC_PM') && (str_contains($clean, 'RELEASE') || str_contains($clean, 'PASSED') || str_contains($clean, 'LOLOS')))
        ) {
            return "Flow Salah! Kempu belum scan Transfer Out to Produksi oleh Tim WPM.";
        }

        // 9. Transfer Out to Produksi (In Transit dari WPM ke Produksi)
        if (
            in_array($clean, ['WPM_TRANSFER_OUT_TO_PROD', 'WPM_TRANSFER_OUT_FROM_PROD', 'IN_TRANSIT_PRODUKSI', 'TRANSFER_OUT_TO_PRODUKSI']) ||
            (str_contains($clean, 'TRANSFER_OUT') && (str_contains($clean, 'PROD') || str_contains($clean, 'PRODUKSI')))
        ) {
            return "Flow Salah! Kempu belum scan Transfer In from WPM oleh Tim Produksi.";
        }

        // 10. Transfer In from WPM (Sudah diterima di Produksi, belum Pre Cuci)
        if (
            in_array($clean, ['PROD_TRANSFER_IN_FROM_WPM', 'PROD_TRANSFER_IN_WPM', 'PROD_RECEIVED', 'TRANSFER_IN_FROM_WPM', 'QC_PRE_CUCI_PENDING']) ||
            (str_contains($clean, 'TRANSFER_IN') && str_contains($clean, 'WPM')) ||
            str_contains($clean, 'PRE_CUCI_PENDING')
        ) {
            return "Flow Salah! Kempu belum scan QC Pre Cuci oleh Tim QC.";
        }

        // 11. Lolos QC Pre Cuci (Siap dicuci oleh Produksi)
        if (
            in_array($clean, ['QC_PRE_CUCI_RELEASE', 'QC_PRE_CUCI_PASSED', 'QC_PROD_PASSED', 'RELEASE_QC_PRE_CUCI']) ||
            (str_contains($clean, 'PRE_CUCI') && (str_contains($clean, 'RELEASE') || str_contains($clean, 'PASSED') || str_contains($clean, 'LOLOS')))
        ) {
            return "Flow Salah! Kempu belum scan Cuci Kempu oleh Tim Produksi.";
        }

        // 12. Selesai Cuci (Cuci Kempu selesai, siap diisi / filling)
        if (
            in_array($clean, ['PROD_CUCI_KEMPU', 'CUCI_KEMPU_COMPLETED', 'CUCI_KEMPU', 'CUCI_KEMPU_SELESAI']) ||
            (str_contains($clean, 'CUCI') && !str_contains($clean, 'PRE'))
        ) {
            return "Flow Salah! Kempu belum scan Filling Kempu oleh Tim Produksi.";
        }

        // 13. Selesai Filling (Scan 1 Filling selesai, belum di-QC After Filling)
        if (
            in_array($clean, ['PROD_FILLING_KEMPU', 'SCAN1_FILLED', 'SCAN_1_FILLED', 'SCAN_1_FILLING_KEMPU', 'QC_AFTER_FILLING_PENDING']) ||
            str_contains($clean, 'FILLING') ||
            str_contains($clean, 'SCAN1') ||
            str_contains($clean, 'SCAN_1')
        ) {
            return "Flow Salah! Kempu belum scan QC After Filling oleh Tim QC.";
        }

        // 14. Lolos QC After Filling (Siap dikirim ke WFG)
        if (
            in_array($clean, ['QC_AFTER_FILLING_RELEASE', 'QC_AFTER_FILLING_PASSED', 'RELEASE_QC_AFTER_FILLING']) ||
            (str_contains($clean, 'AFTER_FILLING') && (str_contains($clean, 'RELEASE') || str_contains($clean, 'PASSED') || str_contains($clean, 'LOLOS')))
        ) {
            return "Flow Salah! Kempu belum scan Transfer Out to WFG oleh Tim Produksi.";
        }

        // 15. Transfer Out to WFG (In Transit dari Produksi ke WFG)
        if (
            in_array($clean, ['PROD_TRANSFER_OUT_TO_WFG', 'PROD_TRANSFER_OUT_FROM_WFG', 'IN_TRANSIT_WFG', 'TRANSFER_OUT_TO_WFG']) ||
            (str_contains($clean, 'TRANSFER_OUT') && str_contains($clean, 'WFG'))
        ) {
            return "Flow Salah! Kempu belum scan Transfer In from Produksi oleh Tim WFG.";
        }

        // 16. Transfer In from Produksi (Diterima di WFG, siap dikirim ke PAS)
        if (
            in_array($clean, ['WFG_TRANSFER_IN_FROM_PROD', 'WFG_RECEIVED', 'TRANSFER_IN_FROM_PRODUKSI', 'PICKING_FG']) ||
            (str_contains($clean, 'TRANSFER_IN') && (str_contains($clean, 'PROD') || str_contains($clean, 'PRODUKSI')) && !str_contains($clean, 'WPM'))
        ) {
            return "Flow Salah! Kempu belum scan Transfer Out to PAS oleh Tim WFG.";
        }

        // 17. Transfer Out to PAS (In Transit dari WFG ke Warehouse PAS)
        if (
            in_array($clean, ['WFG_TRANSFER_OUT_TO_PAS', 'IN_TRANSIT_PAS', 'TRANSFER_OUT_TO_PAS']) ||
            (str_contains($clean, 'TRANSFER_OUT') && str_contains($clean, 'PAS'))
        ) {
            return "Flow Salah! Kempu belum scan Transfer in From BAS oleh Tim PAS.";
        }

        // 18. Transfer In from BAS (Diterima di Warehouse PAS, siap kirim balik ke BAS kosong)
        if (
            in_array($clean, ['PAS_TRANSFER_IN_FROM_BAS', 'PAS_RECEIVED', 'TRANSFER_IN_FROM_BAS']) ||
            (str_contains($clean, 'PAS') && str_contains($clean, 'TRANSFER_IN'))
        ) {
            return "Flow Salah! Kempu belum scan Transfer Out to BAS oleh Tim PAS.";
        }

        // 19. Transfer Out to BAS (In Transit kempu kosong dari PAS kembali ke BAS WPM)
        if (
            in_array($clean, ['PAS_TRANSFER_OUT_TO_BAS', 'IN_TRANSIT_WPM', 'TRANSFER_OUT_TO_BAS']) ||
            (str_contains($clean, 'PAS') && str_contains($clean, 'TRANSFER_OUT'))
        ) {
            return "Flow Salah! Kempu belum scan Transfer In From PAS oleh Tim WPM.";
        }

        // 20. Transfer In from PAS (Telah diterima kembali di WPM, menunggu QC PM)
        if (
            in_array($clean, ['WPM_TRANSFER_IN_FROM_PAS', 'WPM_TRANSFER_IN_PAS', 'TRANSFER_IN_FROM_PAS']) ||
            (str_contains($clean, 'WPM') && str_contains($clean, 'PAS'))
        ) {
            return "Flow Salah! Kempu belum scan QC PM oleh Tim QC.";
        }

        return null;
    }
}
