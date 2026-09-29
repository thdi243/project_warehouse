<?php

namespace App\Http\Controllers\Wfg;

use App\Http\Controllers\Controller;
use App\Models\Kempu\KempuTrackingHistoryModel;
use App\Models\Kempu\MasterKempuModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WfgKempuController extends Controller
{
    /**
     * Konfigurasi Card di WFG
     */
    public static function getCards(): array
    {
        return [
            'transfer-in-from-produksi' => [
                'key'         => 'transfer-in-from-produksi',
                'title'       => 'Transfer in From Produksi',
                'status_name' => MasterKempuModel::STATUS_WFG_TRANSFER_IN_PROD,
                'location'    => MasterKempuModel::LOC_WFG,
                'from_loc'    => MasterKempuModel::LOC_PRODUKSI,
                'to_loc'      => MasterKempuModel::LOC_WFG,
                'stage'       => 'WFG',
                'description' => 'Penerimaan kempu yang telah terisi (filling) dari bagian Produksi ke Warehouse Finished Goods (WFG).',
                'icon'        => 'ri-inbox-archive-line',
                'badge_color' => 'teal',
                'bg_tint'     => '#f0fdfa',
                'icon_color'  => '#0d9488',
                'btn_color'   => '#0f766e',
                'btn_text'    => 'Buka Scanner Transfer In',
            ],
            'transfer-out-to-pas' => [
                'key'         => 'transfer-out-to-pas',
                'title'       => 'Transfer Out to PAS',
                'status_name' => MasterKempuModel::STATUS_WFG_TRANSFER_OUT_PAS,
                'location'    => MasterKempuModel::LOC_WFG,
                'from_loc'    => MasterKempuModel::LOC_WFG,
                'to_loc'      => MasterKempuModel::LOC_PAS,
                'stage'       => 'WFG',
                'description' => 'Pengiriman kempu muatan Finished Goods dari WFG menuju Warehouse PT PAS.',
                'icon'        => 'ri-truck-line',
                'badge_color' => 'purple',
                'bg_tint'     => '#f5f3ff',
                'icon_color'  => '#7c3aed',
                'btn_color'   => '#6d28d9',
                'btn_text'    => 'Buka Scanner Transfer Out',
            ],
        ];
    }

    /**
     * Halaman Card Hub Kempu WFG
     */
    public function index()
    {
        $cards = self::getCards();

        // Hitung kempu dengan status masing-masing card
        foreach ($cards as $key => &$card) {
            $card['count'] = MasterKempuModel::whereHas('main', function ($q) use ($card) {
                $q->where('current_status', $card['status_name']);
            })->count();
        }

        $totalWfg = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_WFG);
        })->count();

        return view('wfg.kempu.index', compact('cards', 'totalWfg'));
    }

    /**
     * Halaman Scanner Khusus Card di WFG
     */
    public function scan($cardKey)
    {
        $cards = self::getCards();

        if (!array_key_exists($cardKey, $cards)) {
            return redirect()->route('wfg.kempu.index')->with('error', 'Pilihan proses kempu tidak valid.');
        }

        $card = $cards[$cardKey];

        return view('wfg.kempu.scan', compact('card', 'cards'));
    }

    /**
     * Cek apakah user saat ini memiliki wewenang untuk input/koreksi reused kempu
     */
    public static function canEditReused($user = null): bool
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return false;
        }

        // Permission berwenang
        if ($user->hasAnyPermission(['super-admin', 'wfg-kempu-reused'])) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah status kempu saat ini adalah status yang belum melalui Scan 1 Filling di Produksi
     * (misal datang langsung dari WPM, QC PM, atau tahap awal Produksi) sehingga perlu +1 reused saat tiba di WFG.
     */
    public static function isPreScan1Status(string $status): bool
    {
        $statusUpper = strtoupper(trim($status));
        $statusNormalized = str_replace([' ', '-'], '_', $statusUpper);

        $allowedStatuses = [
            // 1. WPM Transfer Out to Produksi
            MasterKempuModel::STATUS_WPM_TRANSFER_OUT_PROD, // 'WPM_TRANSFER_OUT_TO_PROD'
            'WPM_TRANSFER_OUT_TO_PROD',
            'WPM_TRANSFER_OUT_PROD',
            'TRANSFER OUT TO PRODUKSI',
            'TRANSFER_OUT_TO_PRODUKSI',
            'TRANSFER OUT TO PROD',
            'TRANSFER_OUT_TO_PROD',
            'WPM TRANSFER OUT TO PRODUKSI',
            'WPM_TRANSFER_OUT_TO_PRODUKSI',
            'IN_TRANSIT_PRODUKSI',
            'IN TRANSIT PRODUKSI',
            'IN_TRANSIT_PROD',
            MasterKempuModel::STATUS_IN_TRANSIT_PROD,

            // 2. Produksi Penerimaan (Transfer In)
            MasterKempuModel::STATUS_PROD_TRANSFER_IN_WPM, // 'PROD_TRANSFER_IN_FROM_WPM'
            'PROD_TRANSFER_IN_FROM_WPM',
            'PROD_TRANSFER_IN_WPM',
            'TRANSFER IN FROM WPM',
            'TRANSFER_IN_FROM_WPM',
            'PROD_RECEIVED',
            MasterKempuModel::STATUS_PROD_RECEIVED,

            // 3. QC Pre Cuci & Produksi Cuci
            MasterKempuModel::STATUS_QC_PRE_CUCI_PENDING,
            MasterKempuModel::STATUS_QC_PRE_CUCI_RELEASE,
            MasterKempuModel::STATUS_QC_PRE_CUCI_PASSED,
            'QC_PRE_CUCI_PENDING',
            'QC_PRE_CUCI_RELEASE',
            'QC_PRE_CUCI_PASSED',
            'QC PRE CUCI RELEASE',
            'QC PRE CUCI PASSED',
            'QC PRE CUCI LOLOS (OK)',
            'QC_PRE_CUCI_LOLOS_(OK)',
            'QC_PROD_PENDING',
            MasterKempuModel::STATUS_QC_PROD_PENDING,
            'QC_PROD_PASSED',
            MasterKempuModel::STATUS_QC_PROD_PASSED,
            'QC PROSES PASSED',
            'QC PROSES LOLOS (OK)',
            'QC_PROSES_PASSED',
            'QC_PROSES_LOLOS_(OK)',
            MasterKempuModel::STATUS_PROD_CUCI_KEMPU,
            'PROD_CUCI_KEMPU',
            'CUCI KEMPU',
            'CUCI_KEMPU',
            'CUCI_KEMPU_COMPLETED',
            'CUCI KEMPU COMPLETED',
            'CUCI KEMPU SELESAI',

            // 4. Produksi Filling & QC After Filling
            MasterKempuModel::STATUS_PROD_FILLING_KEMPU,
            'PROD_FILLING_KEMPU',
            'FILLING KEMPU',
            'FILLING_KEMPU',
            MasterKempuModel::STATUS_QC_AFTER_FILLING_PENDING,
            MasterKempuModel::STATUS_QC_AFTER_FILLING_RELEASE,
            MasterKempuModel::STATUS_QC_AFTER_FILLING_PASSED,
            MasterKempuModel::STATUS_QC_AFTER_FILLING_HOLD,
            'QC_AFTER_FILLING_PENDING',
            'QC_AFTER_FILLING_RELEASE',
            'QC_AFTER_FILLING_PASSED',
            'QC_AFTER_FILLING_HOLD',
            'QC AFTER FILLING RELEASE',
            'QC AFTER FILLING PASSED',
            'QC AFTER FILLING LOLOS (OK)',
            'QC_AFTER_FILLING_LOLOS_(OK)',

            // 5. Produksi Transfer Out to WFG
            MasterKempuModel::STATUS_PROD_TRANSFER_OUT_WFG,
            'PROD_TRANSFER_OUT_TO_WFG',
            'PROD_TRANSFER_OUT_WFG',
            'TRANSFER OUT TO WFG',
            'TRANSFER_OUT_TO_WFG',
            'PRODUKSI TRANSFER OUT TO WFG',
            'PRODUKSI_TRANSFER_OUT_TO_WFG',

            // 6. QC PM & Registrasi Awal / Repair
            MasterKempuModel::STATUS_QC_PM_PENDING,
            MasterKempuModel::STATUS_QC_PM_RELEASE,
            MasterKempuModel::STATUS_QC_PM_PASSED,
            'QC_PM_PENDING',
            'QC_PM_RELEASE',
            'QC PM RELEASE',
            'QC PM PASSED',
            'QC_PM_PASSED',
            MasterKempuModel::STATUS_REGISTERED,
            'REGISTERED',
            MasterKempuModel::STATUS_GR_COMPLETED,
            'GR_COMPLETED',
            MasterKempuModel::STATUS_ENG_REPAIR,
            'ENG_REPAIR',
        ];

        return in_array($statusUpper, $allowedStatuses, true) ||
            in_array($statusNormalized, $allowedStatuses, true);
    }

    /**
     * Validasi alur urutan status dan pencegahan duplikat scan di WFG
     */
    public static function validateStatusFlow($kempu, string $cardKey): array
    {
        $currentStatus = trim($kempu->main?->current_status ?? $kempu->current_status ?? '');
        $idKempu = $kempu->id_kempu;

        // Cek jika kempu berstatus SCRAP
        if (strcasecmp($currentStatus, MasterKempuModel::STATUS_SCRAPPED) === 0) {
            return [
                'valid'   => false,
                'message' => "Kempu {$idKempu} berstatus SCRAP / Afkir dan tidak dapat diproses.",
            ];
        }

        switch ($cardKey) {
            case 'transfer-in-from-produksi':
                // 1. Cek duplikat scan
                if (
                    strcasecmp($currentStatus, 'Transfer in From Produksi') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WFG_TRANSFER_IN_PROD) === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WFG_RECEIVED) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Kempu {$idKempu} sudah berstatus 'Transfer in From Produksi' (duplikat scan). Silakan lanjutkan ke tahap 'Transfer Out to PAS'.",
                    ];
                }

                // 2. Cek jika sudah melangkah lebih jauh (Transfer Out to PAS)
                if (
                    strcasecmp($currentStatus, 'Transfer Out to PAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WFG_TRANSFER_OUT_PAS) === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_IN_TRANSIT_PAS) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Kempu {$idKempu} saat ini sudah berstatus 'Transfer Out to PAS'. Kempu harus menyelesaikan pengiriman ke PAS dan kembali ke WPM terlebih dahulu.",
                    ];
                }

                // 3. Cek jika masih di WPM belum transfer out ke produksi
                if (
                    strcasecmp($currentStatus, 'Transfer In From PAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WPM_TRANSFER_IN_PAS) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} masih berada di WPM (status: 'Transfer In From PAS'). Kempu harus melalui 'Transfer Out To Produksi' terlebih dahulu sebelum tiba di WFG.",
                    ];
                }

                // 4. Khusus KEMPU BARU (Format YYMMDD####) dengan Reused = 0: Wajib melewati proses 'Transfer Out To Produksi' dari WPM terlebih dahulu
                $isOldKempu  = MasterKempuModel::isOldKempu($idKempu);
                $reusedCount = (int)($kempu->main?->reused_count ?? $kempu->reused_count ?? 0);

                if (!$isOldKempu && $reusedCount <= 0) {
                    $initialStatuses = [
                        'registered',
                        strtolower(MasterKempuModel::STATUS_REGISTERED),
                        strtolower(MasterKempuModel::STATUS_QC_PM_PENDING),
                        strtolower(MasterKempuModel::STATUS_QC_PM_RELEASE),
                        strtolower(MasterKempuModel::STATUS_QC_PM_PASSED),
                        strtolower(MasterKempuModel::STATUS_GR_COMPLETED),
                    ];

                    $hasTransferOutHistory = $kempu->trackingHistories()
                        ->where(function ($q) {
                            $q->where('action', 'Transfer Out To Produksi')
                                ->orWhere('action', 'LIKE', '%Transfer Out To Produksi%')
                                ->orWhere('action', 'LIKE', '%WPM_TRANSFER_OUT%');
                        })
                        ->exists();

                    if (in_array(strtolower($currentStatus), $initialStatuses) || !$hasTransferOutHistory) {
                        return [
                            'valid'   => false,
                            'message' => "Urutan salah: Kempu baru {$idKempu} (siklus Reused masih 0x) belum melalui proses 'Transfer Out To Produksi' dari WPM (status saat ini: '{$currentStatus}'). Harap lakukan proses 'Transfer Out To Produksi' di WPM terlebih dahulu sebelum tiba di WFG.",
                        ];
                    }
                }
                break;

            case 'transfer-out-to-pas':
                // 1. Cek duplikat scan
                if (
                    strcasecmp($currentStatus, 'Transfer Out to PAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WFG_TRANSFER_OUT_PAS) === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_IN_TRANSIT_PAS) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Kempu {$idKempu} sudah berstatus 'Transfer Out to PAS' (duplikat scan).",
                    ];
                }

                // 2. Wajib dari Transfer in From Produksi
                $allowedPrev = [
                    'transfer in from produksi',
                    strtolower(MasterKempuModel::STATUS_WFG_TRANSFER_IN_PROD),
                    strtolower(MasterKempuModel::STATUS_WFG_RECEIVED),
                ];
                if (!in_array(strtolower($currentStatus), $allowedPrev)) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} belum melalui tahap 'Transfer in From Produksi' (status saat ini: '{$currentStatus}'). Harap lakukan 'Transfer in From Produksi' terlebih dahulu sebelum Transfer Out to PAS.",
                    ];
                }
                break;
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Lookup Barcode / ID Kempu (Menampilkan info dasar & validasi status reused)
     */
    public function lookup(Request $request)
    {
        $idKempu = strtoupper(trim($request->input('id_kempu', '')));
        $cardKey = $request->input('card_key');

        if (!$idKempu) {
            return response()->json([
                'status'  => false,
                'message' => 'Barcode / ID Kempu tidak boleh kosong.',
            ], 400);
        }

        $cards = self::getCards();
        if (!isset($cards[$cardKey])) {
            return response()->json([
                'status'  => false,
                'message' => 'Tipe proses kempu tidak valid.',
            ], 400);
        }
        $card = $cards[$cardKey];

        $kempu = MasterKempuModel::with('main')->where('id_kempu', $idKempu)->first();

        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu dengan barcode '{$idKempu}' tidak ditemukan dalam Master Kempu.",
            ], 404);
        }

        // Auto-create main jika belum ada
        if (!$kempu->main) {
            $kempu->main()->create([
                'id_kempu'         => $kempu->id_kempu,
                'current_location' => MasterKempuModel::LOC_WFG,
                'current_status'   => 'REGISTERED',
                'reused_count'     => 0,
                'max_reused'       => 21,
                'condition'        => 'OK',
            ]);
            $kempu->load('main');
        }

        $currentStatus = trim($kempu->main?->current_status ?? $kempu->current_status ?? '');
        $reusedCount   = (int)($kempu->main->reused_count ?? 0);
        $isOldKempu    = MasterKempuModel::isOldKempu($kempu->id_kempu);
        $canEditReused = self::canEditReused();
        $flowValidation = self::validateStatusFlow($kempu, $cardKey);

        // Cek apakah kempu berstatus sebelum Scan 1 Filling Produksi (misal bypass langsung dari WPM/QC)
        $isPreScan1 = self::isPreScan1Status($currentStatus);

        $willIncrementReused = false;
        $targetReused = $reusedCount;

        // Hanya hitung rencana penambahan reused jika validasi alur status berhasil (valid)
        if ($flowValidation['valid']) {
            if ($cardKey === 'transfer-in-from-produksi') {
                if ($reusedCount > 0) {
                    // Kempu sudah punya siklus pemakaian (> 0x)
                    $hasReused = true;
                    if ($isPreScan1) {
                        $willIncrementReused = true;
                        $targetReused = min(21, $reusedCount + 1);
                    }
                } else {
                    // reusedCount == 0
                    if (!$isOldKempu) {
                        // Kempu Baru (YYMMDD) -> Siklus baru, tidak perlu registrasi reused
                        $hasReused = true;
                        if ($isPreScan1) {
                            $willIncrementReused = true;
                            $targetReused = 1;
                        } else {
                            $targetReused = 0;
                        }
                    } else {
                        // Kempu Lama -> jika masih 0, wajib registrasi reused oleh otoritator
                        $hasReused = false;
                    }
                }
            } else {
                // Transfer Out to PAS
                $hasReused = (!$isOldKempu || $reusedCount > 0);
            }
        } else {
            $hasReused = (!$isOldKempu || $reusedCount > 0);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'id'                     => $kempu->id,
                'id_kempu'               => $kempu->id_kempu,
                'is_old_kempu'           => $isOldKempu,
                'is_new_kempu'           => !$isOldKempu,
                'rfid'                   => $kempu->rfid ?? '-',
                'current_location'       => $kempu->current_location ?? 'WFG',
                'current_status'         => $currentStatus ?: 'REGISTERED',
                'reused_count'           => $reusedCount,
                'target_reused_count'    => $targetReused,
                'will_increment_reused'  => $willIncrementReused,
                'max_reused'             => $kempu->max_reused ?? 21,
                'has_reused'             => $hasReused,
                'can_edit_reused'        => $canEditReused,
                'condition'              => $kempu->condition ?? 'OK',
                'has_barcode'            => (bool)($kempu->main?->has_barcode ?? true),
                'has_rfid'               => (bool)($kempu->main?->has_rfid ?? true),
                'has_nti'                => (bool)($kempu->main?->has_nti ?? true),
                'last_scanned_at'        => $kempu->last_scanned_at ? $kempu->last_scanned_at->format('d/m/Y H:i') : '-',
                'last_action'            => $kempu->last_action ?? '-',
                'target_status'          => $card['status_name'],
                'card_title'             => $card['title'],
                'is_flow_valid'          => $flowValidation['valid'],
                'flow_error'             => $flowValidation['message'],
            ],
        ]);
    }

    /**
     * Konfirmasi Perubahan Status Kempu sesuai Nama Card
     */
    public function confirm(Request $request)
    {
        $idKempu        = strtoupper(trim($request->input('id_kempu', '')));
        $cardKey        = $request->input('card_key');
        $notes          = $request->input('notes');
        $newReusedCount = $request->input('new_reused_count');

        if (!$idKempu || !$cardKey) {
            return response()->json([
                'status'  => false,
                'message' => 'Data input barcode atau proses tidak lengkap.',
            ], 400);
        }

        $cards = self::getCards();
        if (!isset($cards[$cardKey])) {
            return response()->json([
                'status'  => false,
                'message' => 'Tipe proses kempu tidak valid.',
            ], 400);
        }
        $card = $cards[$cardKey];

        $kempu = MasterKempuModel::with('main')->where('id_kempu', $idKempu)->first();

        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu dengan barcode '{$idKempu}' tidak ditemukan.",
            ], 404);
        }

        // Validasi Alur Status (Urutan & Cegah Duplikat Scan)
        $flowValidation = self::validateStatusFlow($kempu, $cardKey);
        if (!$flowValidation['valid']) {
            return response()->json([
                'status'  => false,
                'message' => $flowValidation['message'],
            ], 422);
        }

        $currentStatus = trim($kempu->main?->current_status ?? $kempu->current_status ?? '');
        $currentReused = (int)($kempu->main?->reused_count ?? 0);
        $isOldKempu    = MasterKempuModel::isOldKempu($kempu->id_kempu);

        $isPreScan1 = self::isPreScan1Status($currentStatus);

        $targetReused = $currentReused;

        if ($cardKey === 'transfer-in-from-produksi') {
            if ($currentReused > 0) {
                // Jika belum melalui Scan 1 Filling Produksi -> auto +1
                if ($isPreScan1) {
                    $targetReused = min(21, $currentReused + 1);
                }
            } else {
                // currentReused == 0
                if (!$isOldKempu) {
                    // Kempu baru siklus baru -> otomatis menjadi 1 jika belum melalui Scan 1 Filling
                    $targetReused = $isPreScan1 ? 1 : 0;
                } else {
                    // Kempu lama belum punya nilai reused
                    if ($newReusedCount !== null && $newReusedCount !== '') {
                        if (!self::canEditReused()) {
                            return response()->json([
                                'status'  => false,
                                'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk menetapkan siklus Reused kempu lama. Harap hubungi Foreman / Supervisor.',
                            ], 403);
                        }

                        if (!is_numeric($newReusedCount) || $newReusedCount < 1 || $newReusedCount > 21) {
                            return response()->json([
                                'status'  => false,
                                'message' => 'Nilai siklus Reused harus berupa angka antara 1 sampai 21x.',
                            ], 422);
                        }

                        $targetReused = (int)$newReusedCount;
                    } else {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Kempu lama belum memiliki data siklus Reused. Silakan input nilai Reused fisik terlebih dahulu oleh user yang berwenang.',
                        ], 422);
                    }
                }
            }
        } else {
            // Transfer Out to PAS
            if ($currentReused <= 0 && $isOldKempu) {
                if ($newReusedCount !== null && $newReusedCount !== '') {
                    if (!self::canEditReused()) {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk menetapkan siklus Reused kempu lama.',
                        ], 403);
                    }
                    $targetReused = (int)$newReusedCount;
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Kempu lama belum memiliki data siklus Reused.',
                    ], 422);
                }
            }
        }

        $hasBarcode     = filter_var($request->input('has_barcode', true), FILTER_VALIDATE_BOOLEAN);
        $hasRfid        = filter_var($request->input('has_rfid', true), FILTER_VALIDATE_BOOLEAN);
        $hasNti         = filter_var($request->input('has_nti', true), FILTER_VALIDATE_BOOLEAN);

        // Ringkasan Checklist Fisik
        $physicalCheck = [];
        $physicalCheck[] = 'Barcode: ' . ($hasBarcode ? 'Ada' : 'Tidak Ada');
        $physicalCheck[] = 'RFID: ' . ($hasRfid ? 'Ada' : 'Tidak Ada');
        $physicalCheck[] = 'NTI: ' . ($hasNti ? 'Ada' : 'Tidak Ada');
        $checklistStr = '[Fisik: ' . implode(', ', $physicalCheck) . ']';
        $finalNotes = $notes ? $checklistStr . ' - ' . $notes : $checklistStr;

        DB::beginTransaction();
        try {
            if (!$kempu->main) {
                $kempu->main()->create([
                    'id_kempu'         => $kempu->id_kempu,
                    'current_location' => $card['location'],
                    'current_status'   => $card['status_name'],
                    'reused_count'     => $targetReused,
                    'max_reused'       => 21,
                    'condition'        => 'OK',
                    'has_barcode'      => $hasBarcode,
                    'has_rfid'         => $hasRfid,
                    'has_nti'        => $hasNti,
                    'last_scanned_at'  => now(),
                    'last_action'      => $card['title'],
                ]);
            } else {
                $kempu->main->update([
                    'current_status'   => $card['status_name'],
                    'current_location' => $card['location'],
                    'reused_count'     => $targetReused,
                    'has_barcode'      => $hasBarcode,
                    'has_rfid'         => $hasRfid,
                    'has_nti'        => $hasNti,
                    'last_scanned_at'  => now(),
                    'last_action'      => $card['title'],
                ]);
            }

            KempuTrackingHistoryModel::create([
                'kempu_master_id' => $kempu->id,
                'id_kempu'        => $kempu->id_kempu,
                'stage'           => $card['stage'],
                'action'          => $card['title'],
                'action_result'   => 'SUCCESS',
                'from_location'   => $card['from_loc'],
                'to_location'     => $card['to_loc'],
                'reused_count'    => $targetReused,
                'condition'       => $kempu->main->condition ?? 'OK',
                'notes'           => $finalNotes,
                'metadata'        => [
                    'has_barcode' => $hasBarcode,
                    'has_rfid'    => $hasRfid,
                    'has_nti'   => $hasNti,
                ],
                'created_by'      => Auth::id(),
            ]);

            DB::commit();

            $successMsg = "Kempu {$kempu->id_kempu} berhasil dikonfirmasi ke status '{$card['status_name']}'";
            if ($targetReused > $currentReused) {
                $successMsg .= " (Siklus Reused otomatis bertambah: {$currentReused}x → {$targetReused}/21x).";
            } else {
                $successMsg .= " (Reused: {$targetReused}/21x).";
            }

            return response()->json([
                'status'  => true,
                'message' => $successMsg,
                'data'    => [
                    'id_kempu'        => $kempu->id_kempu,
                    'rfid'            => $kempu->rfid ?? '-',
                    'new_status'      => $card['status_name'],
                    'reused_count'    => $targetReused,
                    'timestamp'       => now()->format('d/m/Y H:i:s'),
                    'user'            => Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'Operator',
                    'notes'           => $notes,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengonfirmasi kempu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update/Koreksi Nilai Reused Khusus User Berwenang
     */
    public function updateReused(Request $request)
    {
        if (!self::canEditReused()) {
            return response()->json([
                'status'  => false,
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah siklus Reused kempu.',
            ], 403);
        }

        $idKempu = strtoupper(trim($request->input('id_kempu', '')));
        $newReused = $request->input('reused_count');

        if (!$idKempu || !is_numeric($newReused) || $newReused < 0 || $newReused > 21) {
            return response()->json([
                'status'  => false,
                'message' => 'ID Kempu dan nilai Reused (0 - 21x) wajib valid.',
            ], 422);
        }

        $kempu = MasterKempuModel::with('main')->where('id_kempu', $idKempu)->first();
        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu {$idKempu} tidak ditemukan.",
            ], 404);
        }

        if (!$kempu->main) {
            $kempu->main()->create([
                'id_kempu'         => $kempu->id_kempu,
                'current_location' => MasterKempuModel::LOC_WFG,
                'current_status'   => 'REGISTERED',
                'reused_count'     => (int)$newReused,
                'max_reused'       => 21,
                'condition'        => 'OK',
                'last_action'      => 'Koreksi Reused Manual oleh ' . (Auth::user()->nama_lengkap ?? Auth::user()->username),
            ]);
        } else {
            $kempu->main->update([
                'reused_count' => (int)$newReused,
                'last_action'  => 'Koreksi Reused Manual oleh ' . (Auth::user()->nama_lengkap ?? Auth::user()->username),
            ]);
        }

        return response()->json([
            'status'       => true,
            'message'      => "Nilai Reused kempu {$idKempu} berhasil diperbarui menjadi {$newReused}x.",
            'reused_count' => (int)$newReused,
            'data'         => [
                'id_kempu'     => $idKempu,
                'reused_count' => (int)$newReused,
            ],
        ]);
    }

    /**
     * Riwayat scan sesi terkini di WFG
     */
    public function recentScans(Request $request)
    {
        $cardKey = $request->input('card_key');
        $query = KempuTrackingHistoryModel::where('stage', 'WFG')
            ->with(['createdBy:id,username,nama_lengkap', 'masterKempu:id,id_kempu,rfid'])
            ->latest('id')
            ->take(20);

        if ($cardKey) {
            $cards = self::getCards();
            if (isset($cards[$cardKey])) {
                $query->where('action', $cards[$cardKey]['title']);
            }
        }

        $list = $query->get();

        return response()->json([
            'status' => true,
            'data'   => $list,
        ]);
    }

    /**
     * Halaman Report Scan Kempu WFG
     */
    public function report(Request $request)
    {
        $cards = self::getCards();

        $totalCurrentWfg = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_WFG);
        })->count();

        $totalTransferInToday = KempuTrackingHistoryModel::where('stage', 'WFG')
            ->where('action', 'like', '%Transfer in%')
            ->whereDate('created_at', today())
            ->count();

        $totalTransferInAll = KempuTrackingHistoryModel::where('stage', 'WFG')
            ->where('action', 'like', '%Transfer in%')
            ->count();

        $totalTransferOutToday = KempuTrackingHistoryModel::where('stage', 'WFG')
            ->where('action', 'like', '%Transfer Out%')
            ->whereDate('created_at', today())
            ->count();

        $totalTransferOutAll = KempuTrackingHistoryModel::where('stage', 'WFG')
            ->where('action', 'like', '%Transfer Out%')
            ->count();

        $totalWarning = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_WFG)
                ->where('reused_count', '>=', 18);
        })->count();

        return view('wfg.kempu.report', compact(
            'cards',
            'totalCurrentWfg',
            'totalTransferInToday',
            'totalTransferInAll',
            'totalTransferOutToday',
            'totalTransferOutAll',
            'totalWarning'
        ));
    }

    /**
     * AJAX Endpoint untuk Data Report Kempu WFG (Server-side Pagination & Filtering)
     */
    public function reportData(Request $request)
    {
        $viewMode = $request->input('view_mode', 'history'); // 'history' | 'current'
        $perPage  = min(100, max(5, (int) $request->input('per_page', 20)));

        if ($viewMode === 'current') {
            // Data kempu yang saat ini berada di lokasi WFG
            $query = MasterKempuModel::whereHas('main', function ($q) {
                $q->where('current_location', MasterKempuModel::LOC_WFG);
            })->with(['main', 'createdBy:id,username,nama_lengkap']);

            if ($request->filled('search')) {
                $s = trim($request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('id_kempu', 'like', "%{$s}%")
                        ->orWhere('rfid', 'like', "%{$s}%")
                        ->orWhere('no_spb', 'like', "%{$s}%")
                        ->orWhere('keterangan', 'like', "%{$s}%");
                });
            }

            if ($request->filled('reused_status')) {
                $query->whereHas('main', function ($q) use ($request) {
                    if ($request->reused_status === 'warning') {
                        $q->whereBetween('reused_count', [18, 20]);
                    } elseif ($request->reused_status === 'max') {
                        $q->where('reused_count', '>=', 21);
                    } elseif ($request->reused_status === 'normal') {
                        $q->where('reused_count', '<', 18);
                    }
                });
            }

            $paginated = $query->latest('updated_at')->paginate($perPage);

            return response()->json([
                'status'     => true,
                'view_mode'  => 'current',
                'data'       => $paginated->items(),
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'total'        => $paginated->total(),
                    'per_page'     => $paginated->perPage(),
                ],
            ]);
        }

        // Default: Log Riwayat Scan WFG
        $query = KempuTrackingHistoryModel::where(function ($q) {
            $q->where('stage', 'WFG')
              ->orWhere('from_location', MasterKempuModel::LOC_WFG)
              ->orWhere('to_location', MasterKempuModel::LOC_WFG);
        })->with([
            'createdBy:id,username,nama_lengkap',
            'masterKempu:id,id_kempu,rfid,no_spb,status',
        ]);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('id_kempu', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhereHas('masterKempu', function ($mq) use ($s) {
                        $mq->where('rfid', 'like', "%{$s}%")
                           ->orWhere('no_spb', 'like', "%{$s}%");
                    })
                    ->orWhereHas('createdBy', function ($uq) use ($s) {
                        $uq->where('username', 'like', "%{$s}%")
                           ->orWhere('nama_lengkap', 'like', "%{$s}%");
                    });
            });
        }

        $paginated = $query->latest('id')->paginate($perPage);

        return response()->json([
            'status'     => true,
            'view_mode'  => 'history',
            'data'       => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
            ],
        ]);
    }

    /**
     * Export Report CSV untuk Scan Kempu WFG
     */
    public function exportReport(Request $request)
    {
        $query = KempuTrackingHistoryModel::where(function ($q) {
            $q->where('stage', 'WFG')
              ->orWhere('from_location', MasterKempuModel::LOC_WFG)
              ->orWhere('to_location', MasterKempuModel::LOC_WFG);
        })->with([
            'createdBy:id,username,nama_lengkap',
            'masterKempu:id,id_kempu,rfid,no_spb,status',
        ]);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('id_kempu', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhereHas('masterKempu', function ($mq) use ($s) {
                        $mq->where('rfid', 'like', "%{$s}%")
                           ->orWhere('no_spb', 'like', "%{$s}%");
                    })
                    ->orWhereHas('createdBy', function ($uq) use ($s) {
                        $uq->where('username', 'like', "%{$s}%")
                           ->orWhere('nama_lengkap', 'like', "%{$s}%");
                    });
            });
        }

        $records = $query->latest('id')->get();
        $filename = 'Report_Scan_Kempu_WFG_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header
            fputcsv($file, [
                'No',
                'Tanggal & Waktu',
                'ID Kempu',
                'RFID',
                'Aksi / Status Flow',
                'Dari Lokasi',
                'Menuju Lokasi',
                'Siklus Reused',
                'Kondisi',
                'Catatan / Surat Jalan',
                'Petugas Scan',
            ]);

            $no = 1;
            foreach ($records as $row) {
                fputcsv($file, [
                    $no++,
                    $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '-',
                    $row->id_kempu,
                    $row->masterKempu->rfid ?? '-',
                    $row->action,
                    $row->from_location ?? '-',
                    $row->to_location ?? '-',
                    ($row->reused_count ?? 0) . ' / 21x',
                    $row->condition ?? 'OK',
                    $row->notes ?? '-',
                    $row->createdBy->nama_lengkap ?? $row->createdBy->username ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
