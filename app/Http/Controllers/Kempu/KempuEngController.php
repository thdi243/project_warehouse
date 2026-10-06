<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
use App\Models\Kempu\KempuTrackingHistoryModel;
use App\Models\Kempu\MasterKempuModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KempuEngController extends Controller
{
    /**
     * Tampilkan halaman Pemindai Scanner Engineering Repair
     */
    public function scan()
    {
        $canManualInput = MasterKempuModel::canManualInput();
        return view('kempu.eng.scan', compact('canManualInput'));
    }

    /**
     * API: Summary status kempu di Engineering Repair
     */
    public function summaryApi()
    {
        $totalRepairPending = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_status', MasterKempuModel::STATUS_ENG_REPAIR)
                ->orWhere('current_location', MasterKempuModel::LOC_ENG);
        })->count();

        $recentRepairs = KempuTrackingHistoryModel::where('stage', 'ENGINEERING_WORKSHOP')
            ->latest('id')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'total_repair_pending' => $totalRepairPending,
                'recent_repairs'       => $recentRepairs,
            ],
        ]);
    }

    /**
     * Validasi alur kempu untuk Engineering Repair
     */
    public static function validateEngFlow($kempu): array
    {
        $currentStatus   = trim($kempu->main?->current_status ?? $kempu->current_status ?? '');
        $currentLocation = trim($kempu->main?->current_location ?? $kempu->current_location ?? '');
        $idKempu         = $kempu->id_kempu;

        if (strcasecmp($currentStatus, MasterKempuModel::STATUS_SCRAPPED) === 0) {
            return [
                'valid'   => false,
                'message' => "Kempu {$idKempu} berstatus SCRAP / Afkir dan tidak dapat diproses lagi.",
            ];
        }

        // Status yang diizinkan masuk ke Engineering Repair
        $allowedStatuses = [
            MasterKempuModel::STATUS_ENG_REPAIR,
            'ENG_REPAIR',
            'REPAIR',
            'QC PM Reject',
            'QC Proses Reject',
            'NOT_OK',
        ];

        $isMatch = false;
        foreach ($allowedStatuses as $st) {
            if (strcasecmp($currentStatus, $st) === 0) {
                $isMatch = true;
                break;
            }
        }

        // Juga izinkan jika lokasi saat ini berada di ENGINEERING_WORKSHOP
        if (!$isMatch && (strcasecmp($currentLocation, MasterKempuModel::LOC_ENG) === 0 || strcasecmp($currentLocation, 'ENG') === 0)) {
            $isMatch = true;
        }

        if (!$isMatch) {
            return [
                'valid'   => false,
                'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Menu Engineering Repair hanya menerima kempu yang berstatus 'ENG_REPAIR' (reject dari QC PM atau QC Proses).",
            ];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Lookup Barcode/RFID Kempu saat di-scan di modul Engineering
     */
    public function lookup(Request $request)
    {
        $idKempu = strtoupper(trim($request->input('id_kempu', $request->input('barcode', ''))));

        if (!$idKempu) {
            return response()->json([
                'status'  => false,
                'message' => 'Parameter ID kempu tidak boleh kosong.',
            ], 400);
        }

        $kempu = MasterKempuModel::with('main')
            ->where('id_kempu', $idKempu)
            ->orWhere('rfid', $idKempu)
            ->first();

        if (!$kempu) {
            // Auto registrasi jika format YYMMDD baru
            if (preg_match('/^\d{6}/', $idKempu)) {
                $kempu = MasterKempuModel::create([
                    'id_kempu'   => $idKempu,
                    'status'     => MasterKempuModel::STATUS_ENG_REPAIR,
                    'gr_date'    => now()->toDateString(),
                    'created_by' => auth()->id() ?? 1,
                ]);
                $kempu->main()->create([
                    'id_kempu'         => $idKempu,
                    'current_location' => MasterKempuModel::LOC_ENG,
                    'current_status'   => MasterKempuModel::STATUS_ENG_REPAIR,
                    'reused_count'     => 0,
                    'max_reused'       => 21,
                    'condition'        => 'NOT_OK',
                    'last_scanned_at'  => now(),
                    'last_action'      => 'Registrasi Otomatis Engineering',
                ]);
                $kempu->load('main');
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => "Kempu '{$idKempu}' tidak ditemukan dalam database Master Kempu.",
                ], 404);
            }
        }

        // Pastikan relasi main ada
        if (!$kempu->main) {
            $kempu->main()->create([
                'id_kempu'         => $kempu->id_kempu,
                'current_location' => MasterKempuModel::LOC_ENG,
                'current_status'   => $kempu->status ?? MasterKempuModel::STATUS_ENG_REPAIR,
                'reused_count'     => 0,
                'max_reused'       => 21,
                'condition'        => 'NOT_OK',
            ]);
            $kempu->load('main');
        }

        $flowValidation = self::validateEngFlow($kempu);

        $isManual = $request->boolean('is_manual') || $request->input('input_type') === 'manual';
        if ($isManual) {
            if (!MasterKempuModel::canManualInput()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Akses ditolak: Operator tidak memiliki izin untuk mengetik ID kempu secara manual. Wajib menggunakan pemindai kamera/barcode.',
                ], 403);
            }

            // Catat aktivitas pengetikan ID manual ke History Scan
            $user = auth()->user();
            $opName = $user?->nama_lengkap ?? $user?->username ?? 'User';
            $opRole = $user?->role ?? ($user?->roles?->first()?->name ?? 'ENG Staff');
            $kempu->recordTracking(
                stage: 'ENGINEERING_WORKSHOP',
                action: 'Input Manual ID (Lookup Repair)',
                actionResult: 'MANUAL_SCAN',
                fromLocation: $kempu->main?->current_location ?? 'ENG',
                toLocation: $kempu->main?->current_location ?? 'ENG',
                condition: $kempu->main?->condition ?? 'NOT_OK',
                notes: "ID Kempu diketik manual oleh {$opName} ({$opRole})",
                userId: $user?->id,
                metadata: [
                    'input_method'  => 'MANUAL',
                    'is_manual'     => true,
                    'action_title'  => 'Lookup Repair',
                    'operator_name' => $opName,
                    'operator_role' => $opRole,
                ]
            );
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'id'               => $kempu->id,
                'id_kempu'         => $kempu->id_kempu,
                'rfid'             => $kempu->rfid ?? '-',
                'current_location' => $kempu->main->current_location ?? 'ENGINEERING_WORKSHOP',
                'current_status'   => $kempu->main->current_status ?? 'ENG_REPAIR',
                'reused_count'     => (int)($kempu->main->reused_count ?? 0),
                'condition'        => $kempu->main->condition ?? 'NOT_OK',
                'is_flow_valid'    => $flowValidation['valid'],
                'flow_error'       => $flowValidation['message'],
            ],
        ]);
    }

    /**
     * Simpan Keputusan Engineering Repair (Bisa Repair vs Tidak Bisa Repair)
     */
    public function decision(Request $request)
    {
        $idKempu  = strtoupper(trim($request->input('id_kempu', $request->input('barcode', ''))));
        $decision = strtoupper(trim($request->input('decision', ''))); // 'BISA_REPAIR' atau 'TIDAK_BISA_REPAIR'
        $notes    = trim($request->input('notes', ''));
        $isManual = $request->boolean('is_manual');

        if ($isManual) {
            if (!MasterKempuModel::canManualInput()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Akses ditolak: Operator tidak memiliki izin untuk mengonfirmasi transaksi dari pengetikan ID manual.',
                ], 403);
            }

            if (!str_contains($notes, '[Input Manual]')) {
                $notes = $notes ? $notes . ' [Input Manual]' : '[Input Manual]';
            }
        }

        if (!$idKempu || !in_array($decision, ['BISA_REPAIR', 'TIDAK_BISA_REPAIR'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Data input tidak lengkap atau keputusan tidak valid.',
            ], 400);
        }

        $kempu = MasterKempuModel::with('main')->where('id_kempu', $idKempu)->first();

        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu '{$idKempu}' tidak ditemukan.",
            ], 404);
        }

        // Validasi Alur
        $flowValidation = self::validateEngFlow($kempu);
        if (!$flowValidation['valid']) {
            return response()->json([
                'status'  => false,
                'message' => $flowValidation['message'],
            ], 422);
        }

        $currentReused = (int)($kempu->main?->reused_count ?? 0);
        $fromLocation  = $kempu->main?->current_location ?? MasterKempuModel::LOC_ENG;

        if ($decision === 'BISA_REPAIR') {
            // Bisa Repair -> Dikembalikan ke QC PM (WPM) untuk verifikasi ulang
            $nextStatus    = MasterKempuModel::STATUS_QC_PM_PENDING;
            $nextLocation  = MasterKempuModel::LOC_WPM;
            $condition     = 'OK';
            $actionTitle   = 'Engineering Repair Selesai (Bisa Repair)';
            $actionResult  = 'BISA_REPAIR';
            $resultMessage = 'Kempu dinyatakan BISA REPAIR dan dikembalikan ke antrean QC PM untuk verifikasi ulang.';
        } else {
            // Tidak Bisa Repair -> Diteruskan ke Produksi untuk Berita Acara Scrap
            $nextStatus    = MasterKempuModel::STATUS_ENG_SCRAP_PROD;
            $nextLocation  = MasterKempuModel::LOC_PRODUKSI;
            $condition     = 'NOT_OK';
            $actionTitle   = 'Engineering Repair Gagal (Tidak Bisa Repair / Scrap)';
            $actionResult  = 'TIDAK_BISA_REPAIR';
            $resultMessage = 'Kempu dinyatakan TIDAK BISA REPAIR dan diteruskan ke Produksi untuk proses BA Scrap.';
        }

        DB::beginTransaction();
        try {
            if (!$kempu->main) {
                $kempu->main()->create([
                    'id_kempu'         => $kempu->id_kempu,
                    'current_location' => $nextLocation,
                    'current_status'   => $nextStatus,
                    'reused_count'     => $currentReused,
                    'max_reused'       => 21,
                    'condition'        => $condition,
                    'last_scanned_at'  => now(),
                    'last_action'      => $actionTitle,
                ]);
            } else {
                $kempu->main->update([
                    'current_status'   => $nextStatus,
                    'current_location' => $nextLocation,
                    'condition'        => $condition,
                    'last_scanned_at'  => now(),
                    'last_action'      => $actionTitle,
                ]);
            }

            // Update data master kempu (kempu_master)
            $userId = auth()->id() ?? $request->input('user_id') ?? 1;
            if ($decision === 'BISA_REPAIR') {
                $kempu->update([
                    'status'     => 'active',
                    'keterangan' => $notes ? 'Selesai Repair Workshop: ' . $notes : 'Selesai Repair Engineering Workshop',
                    'updated_by' => $userId,
                ]);
            } else {
                // Keputusan resmi SCRAP ada di tangan Produksi saat scan Create BA Scrap,
                // maka di Workshop status master tetap 'maintenance' (menunggu BA Scrap di Produksi).
                $kempu->update([
                    'status'     => 'maintenance',
                    'keterangan' => $notes ? 'Tidak Bisa Repair (Menunggu BA Scrap Produksi): ' . $notes : 'Tidak Bisa Repair (Menunggu BA Scrap Produksi)',
                    'updated_by' => $userId,
                ]);
            }

            KempuTrackingHistoryModel::create([
                'kempu_master_id' => $kempu->id,
                'id_kempu'        => $kempu->id_kempu,
                'stage'           => 'ENGINEERING_WORKSHOP',
                'action'          => $actionTitle,
                'action_result'   => $actionResult,
                'from_location'   => $fromLocation,
                'to_location'     => $nextLocation,
                'notes'           => $notes ?: null,
                'metadata'        => [
                    'input_method' => $isManual ? 'MANUAL' : 'SCANNER',
                    'is_manual'    => $isManual,
                ],
                'created_by'      => auth()->id() ?? $request->input('user_id') ?? 1,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => $resultMessage,
                'data'    => [
                    'id_kempu'      => $kempu->id_kempu,
                    'decision'      => $decision,
                    'new_status'    => $nextStatus,
                    'new_location'  => $nextLocation,
                    'new_condition' => $condition,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memproses keputusan Engineering: ' . $e->getMessage(),
            ], 500);
        }
    }
}
