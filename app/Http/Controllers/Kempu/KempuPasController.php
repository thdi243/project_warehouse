<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
use App\Models\Kempu\KempuCycleFillingModel;
use App\Models\Kempu\KempuTrackingHistoryModel;
use App\Models\Kempu\MasterKempuModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KempuPasController extends Controller
{
    /**
     * Konfigurasi Card di Warehouse PAS
     */
    public static function getCards(): array
    {
        return [
            'transfer-in-from-bas' => [
                'key'         => 'transfer-in-from-bas',
                'title'       => 'Transfer in From BAS',
                'status_name' => MasterKempuModel::STATUS_PAS_TRANSFER_IN_BAS,
                'location'    => MasterKempuModel::LOC_PAS,
                'from_loc'    => MasterKempuModel::LOC_WFG,
                'to_loc'      => MasterKempuModel::LOC_PAS,
                'stage'       => 'PAS',
                'description' => 'Penerimaan kempu Finished Goods dari Warehouse PT BAS (WFG) di Warehouse PAS.',
                'icon'        => 'ri-inbox-archive-line',
                'badge_color' => 'teal',
                'bg_tint'     => '#f0fdfa',
                'icon_color'  => '#0d9488',
                'btn_color'   => '#0f766e',
                'btn_text'    => 'Buka Scanner Transfer In',
            ],
            'transfer-out-to-bas' => [
                'key'         => 'transfer-out-to-bas',
                'title'       => 'Transfer Out to BAS',
                'status_name' => MasterKempuModel::STATUS_PAS_TRANSFER_OUT_BAS,
                'location'    => MasterKempuModel::LOC_IN_TRANSIT,
                'from_loc'    => MasterKempuModel::LOC_PAS,
                'to_loc'      => MasterKempuModel::LOC_WPM,
                'stage'       => 'PAS',
                'description' => 'Pengiriman kempu kosong dari Warehouse PAS kembali ke Warehouse PT BAS (WPM).',
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
     * Halaman Card Hub Kempu Warehouse PAS
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

        $totalPas = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_PAS);
        })->count();

        return view('kempu.pas.index', compact('cards', 'totalPas'));
    }

    /**
     * Halaman Scanner Khusus Card di Warehouse PAS
     */
    public function scan($cardKey)
    {
        $cards = self::getCards();

        if (!array_key_exists($cardKey, $cards)) {
            return redirect()->route('kempu.pas.index')->with('error', 'Pilihan proses kempu tidak valid.');
        }

        $card = $cards[$cardKey];
        $canManualInput = MasterKempuModel::canManualInput();

        return view('kempu.pas.scan', compact('card', 'cards', 'canManualInput'));
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

        if ($user->hasAnyPermission(['super-admin', 'pas-kempu-reused', 'kempu-manual-reused', 'wfg-kempu-reused', 'wpm-kempu-reused'])) {
            return true;
        }

        return false;
    }

    /**
     * Validasi alur urutan status dan pencegahan duplikat scan di Warehouse PAS
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
            case 'transfer-in-from-bas':
                // 1. Cek duplikat scan
                if (
                    strcasecmp($currentStatus, 'Transfer in From BAS') === 0 ||
                    strcasecmp($currentStatus, 'Transfer In From BAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_PAS_TRANSFER_IN_BAS) === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_PAS_RECEIVED) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Kempu {$idKempu} sudah berstatus 'Transfer in From BAS' (duplikat scan). Silakan lanjutkan ke tahap 'Transfer Out to BAS'.",
                    ];
                }

                // 2. Cek jika sudah melangkah ke Transfer Out to BAS
                if (
                    strcasecmp($currentStatus, 'Transfer Out to BAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_PAS_TRANSFER_OUT_BAS) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Kempu {$idKempu} sudah melewati tahap ini (status saat ini: 'Transfer Out to BAS').",
                    ];
                }

                // 3. Cek jika masih di area WPM / Produksi / belum Transfer Out dari WFG
                if (
                    strcasecmp($currentStatus, 'Transfer In From PAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WPM_TRANSFER_IN_PAS) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Flow Salah! Kempu masih berada di WPM BAS.",
                    ];
                }

                if (in_array(strtolower($currentStatus), [
                    'transfer in from produksi',
                    'picking fg',
                    strtolower(MasterKempuModel::STATUS_WFG_TRANSFER_IN_PROD),
                    strtolower(MasterKempuModel::STATUS_WFG_RECEIVED),
                ])) {
                    return [
                        'valid'   => false,
                        'message' => "Flow Salah! Kempu belum scan Transfer Out to PAS oleh Tim WFG.",
                    ];
                }

                // Status yang diperbolehkan untuk Transfer in From BAS (Kempu yang dikirim dari WFG ke PAS)
                $allowedPrev = [
                    'transfer out to pas',
                    strtolower(MasterKempuModel::STATUS_WFG_TRANSFER_OUT_PAS),
                    strtolower(MasterKempuModel::STATUS_IN_TRANSIT_PAS),
                ];

                if (!in_array(strtolower($currentStatus), $allowedPrev)) {
                    $flowMsg = MasterKempuModel::getNextRequiredStepMessage($currentStatus);
                    return [
                        'valid'   => false,
                        'message' => $flowMsg ?? "Flow Salah! Kempu belum scan Transfer Out to PAS oleh Tim WFG.",
                    ];
                }
                break;

            case 'transfer-out-to-bas':
                // 1. Cek duplikat scan
                if (
                    strcasecmp($currentStatus, 'Transfer Out to BAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_PAS_TRANSFER_OUT_BAS) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Flow Salah! Kempu sudah scan Transfer Out to BAS.",
                    ];
                }

                // 2. Prasyarat: Harus sudah diterima di PAS (Transfer in From BAS)
                $allowedPrev = [
                    'transfer in from bas',
                    strtolower(MasterKempuModel::STATUS_PAS_TRANSFER_IN_BAS),
                    strtolower(MasterKempuModel::STATUS_PAS_RECEIVED),
                ];

                if (!in_array(strtolower($currentStatus), $allowedPrev)) {
                    $flowMsg = MasterKempuModel::getNextRequiredStepMessage($currentStatus);
                    return [
                        'valid'   => false,
                        'message' => $flowMsg ?? "Flow Salah! Kempu belum scan Transfer in From BAS oleh Tim PAS.",
                    ];
                }
                break;
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Lookup Barcode / ID Kempu di Warehouse PAS
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
                'current_location' => MasterKempuModel::LOC_PAS,
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

        $isManual = $request->boolean('is_manual') || $request->input('input_type') === 'manual';
        if ($isManual) {
            if (!MasterKempuModel::canManualInput()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Akses ditolak: Operator tidak memiliki izin untuk mengetik ID kempu secara manual. Wajib menggunakan pemindai kamera/barcode.',
                ], 403);
            }
        }

        $cycleFilling = KempuCycleFillingModel::where('kempu_master_id', $kempu->id)
            ->where('reused_count', $reusedCount)
            ->whereNotNull('no_po')
            ->where('no_po', '!=', '')
            ->first();

        $latestFilledCycle = KempuCycleFillingModel::where('kempu_master_id', $kempu->id)
            ->whereNotNull('no_po')
            ->where('no_po', '!=', '')
            ->latest('id')
            ->first();

        $cycleNoPo = $cycleFilling?->no_po
            ?: ($kempu->main?->no_po
            ?: ($latestFilledCycle?->no_po
            ?: ($kempu->no_spb ?? '')));

        if (empty($cycleNoPo)) {
            $lastTracking = KempuTrackingHistoryModel::where('kempu_master_id', $kempu->id)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.no_po')) IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.no_po')) != ''")
                ->latest('id')
                ->first();
            if ($lastTracking && !empty($lastTracking->metadata['no_po'])) {
                $cycleNoPo = $lastTracking->metadata['no_po'];
            }
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'id'                     => $kempu->id,
                'id_kempu'               => $kempu->id_kempu,
                'is_old_kempu'           => $isOldKempu,
                'is_new_kempu'           => !$isOldKempu,
                'rfid'                   => $kempu->rfid ?? '-',
                'no_po'                  => $cycleNoPo,
                'current_location'       => $kempu->current_location ?? 'WAREHOUSE_PAS',
                'current_status'         => $currentStatus ?: 'REGISTERED',
                'reused_count'           => $reusedCount,
                'target_reused_count'    => $reusedCount,
                'will_increment_reused'  => false,
                'max_reused'             => $kempu->max_reused ?? 21,
                'has_reused'             => true,
                'can_edit_reused'        => $canEditReused,
                'condition'              => $kempu->condition ?? 'OK',
                'has_barcode'            => (bool)($cycleFilling?->has_barcode ?? $kempu->main?->has_barcode ?? true),
                'has_rfid'               => (bool)($cycleFilling?->has_rfid ?? $kempu->main?->has_rfid ?? true),
                'has_kitir'              => (bool)($cycleFilling?->has_nti ?? $kempu->main?->has_nti ?? true),
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
     * Konfirmasi Perubahan Status Kempu di Warehouse PAS
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

        $isManual       = $request->boolean('is_manual');

        if ($isManual && !MasterKempuModel::canManualInput()) {
            return response()->json([
                'status'  => false,
                'message' => 'Akses ditolak: Operator tidak memiliki izin untuk mengonfirmasi transaksi dari pengetikan ID manual.',
            ], 403);
        }

        // Validasi Alur Status
        $flowValidation = self::validateStatusFlow($kempu, $cardKey);
        if (!$flowValidation['valid']) {
            return response()->json([
                'status'  => false,
                'message' => $flowValidation['message'],
            ], 422);
        }

        $currentReused = (int)($kempu->main?->reused_count ?? 0);
        $targetReused  = $currentReused;

        if ($newReusedCount !== null && $newReusedCount !== '') {
            if (self::canEditReused()) {
                if (is_numeric($newReusedCount) && $newReusedCount >= 0 && $newReusedCount <= 21) {
                    $targetReused = (int)$newReusedCount;
                }
            }
        }

        // Penentuan kondisi muatan untuk Transfer Out to BAS (Kosongan ke WPM vs Ada isinya ke WFG)
        $kondisiMuatan = strtolower(trim($request->input('kondisi_muatan', 'kosong')));
        $targetStatus  = $card['status_name'];
        $toLocation    = $card['to_loc'];
        $actionTitle   = $card['title'];

        if ($cardKey === 'transfer-out-to-bas') {
            if ($kondisiMuatan === 'isi') {
                $targetStatus = 'PAS_RETUR_TO_WFG';
                $toLocation   = MasterKempuModel::LOC_WFG;
                $actionTitle  = 'Transfer Out to BAS (Retur Berisi ke WFG)';
            } else {
                $targetStatus = MasterKempuModel::STATUS_PAS_TRANSFER_OUT_BAS;
                $toLocation   = MasterKempuModel::LOC_WPM;
                $actionTitle  = 'Transfer Out to BAS (Kosongan ke WPM)';
            }
        }

        $targetLocation = ($cardKey === 'transfer-out-to-bas' ? MasterKempuModel::LOC_IN_TRANSIT : $toLocation);

        DB::beginTransaction();
        try {
            if (!$kempu->main) {
                $kempu->main()->create([
                    'id_kempu'         => $kempu->id_kempu,
                    'current_location' => $targetLocation,
                    'current_status'   => $targetStatus,
                    'reused_count'     => $targetReused,
                    'max_reused'       => 21,
                    'condition'        => 'OK',
                    'last_scanned_at'  => now(),
                    'last_action'      => $actionTitle,
                ]);
            } else {
                $kempu->main->update([
                    'current_status'   => $targetStatus,
                    'current_location' => $targetLocation,
                    'reused_count'     => $targetReused,
                    'last_scanned_at'  => now(),
                    'last_action'      => $actionTitle,
                ]);
            }

            if ($isManual && !str_contains($notes ?? '', '[Input Manual]')) {
                $notes = $notes ? $notes . ' [Input Manual]' : '[Input Manual]';
            }

            $trackingMetadata = [
                'input_method'   => $isManual ? 'MANUAL' : 'SCANNER',
                'is_manual'      => $isManual,
                'kondisi_muatan' => $kondisiMuatan,
            ];

            KempuTrackingHistoryModel::create([
                'kempu_master_id' => $kempu->id,
                'id_kempu'        => $kempu->id_kempu,
                'stage'           => $card['stage'],
                'action'          => $actionTitle,
                'action_result'   => 'SUCCESS',
                'from_location'   => $card['from_loc'],
                'to_location'     => $toLocation,
                'reused_count'    => $targetReused,
                'condition'       => $kempu->main->condition ?? 'OK',
                'notes'           => $notes,
                'metadata'        => $trackingMetadata,
                'created_by'      => Auth::id(),
            ]);

            DB::commit();

            if ($cardKey === 'transfer-out-to-bas' && $kondisiMuatan === 'isi') {
                $successMsg = "Kempu {$kempu->id_kempu} berhasil di-Transfer Out dari PAS (Kondisi: Ada isinya) menuju Warehouse WFG (Retur).";
            } else {
                $successMsg = "Kempu {$kempu->id_kempu} berhasil dikonfirmasi ke status '{$targetStatus}' (Reused: {$targetReused}/21x).";
            }

            return response()->json([
                'status'  => true,
                'message' => $successMsg,
                'data'    => [
                    'id_kempu'       => $kempu->id_kempu,
                    'rfid'           => $kempu->rfid ?? '-',
                    'new_status'     => $targetStatus,
                    'new_location'   => $targetLocation,
                    'kondisi_muatan' => $kondisiMuatan,
                    'reused_count'   => $targetReused,
                    'timestamp'      => now()->format('d/m/Y H:i:s'),
                    'user'           => Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'Operator',
                    'notes'          => $notes,
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

        $idKempu   = strtoupper(trim($request->input('id_kempu', '')));
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

        $cycleFilling = KempuCycleFillingModel::where('kempu_master_id', $kempu->id)
            ->where('reused_count', (int)$newReused)
            ->first();

        $mainData = [
            'reused_count' => (int)$newReused,
            'has_barcode'  => (bool)($cycleFilling?->has_barcode ?? true),
            'has_rfid'     => (bool)($cycleFilling?->has_rfid ?? true),
            'has_nti'      => (bool)($cycleFilling?->has_nti ?? true),
            'no_po'        => $cycleFilling?->no_po ?? null,
            'foto_1'       => $cycleFilling?->foto_1 ?? null,
            'foto_2'       => $cycleFilling?->foto_2 ?? null,
            'foto_3'       => $cycleFilling?->foto_3 ?? null,
            'foto_4'       => $cycleFilling?->foto_4 ?? null,
            'last_action'  => 'Koreksi Reused Manual di PAS oleh ' . (Auth::user()->nama_lengkap ?? Auth::user()->username),
        ];

        if (!$kempu->main) {
            $mainData['id_kempu']         = $kempu->id_kempu;
            $mainData['current_location'] = MasterKempuModel::LOC_PAS;
            $mainData['current_status']   = 'REGISTERED';
            $mainData['max_reused']       = 21;
            $mainData['condition']        = 'OK';
            $kempu->main()->create($mainData);
        } else {
            $kempu->main->update($mainData);
        }

        $kempu->recordTracking(
            stage: 'PAS',
            action: 'Koreksi Reused Manual',
            actionResult: 'UPDATED',
            fromLocation: $kempu->main?->current_location ?? MasterKempuModel::LOC_PAS,
            toLocation: $kempu->main?->current_location ?? MasterKempuModel::LOC_PAS,
            condition: $kempu->main?->condition ?? 'OK',
            notes: "Siklus Reused disesuaikan manual ke {$newReused}x",
            userId: Auth::id()
        );

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
     * Riwayat scan sesi terkini di Warehouse PAS
     */
    public function recentScans(Request $request)
    {
        $cardKey = $request->input('card_key');
        $query = KempuTrackingHistoryModel::where('stage', 'PAS')
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
     * Halaman Report / Laporan Scan Kempu Warehouse PAS
     */
    public function report(Request $request)
    {
        $cards = self::getCards();

        $totalCurrentPas = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_PAS);
        })->count();

        $totalTransferInToday = KempuTrackingHistoryModel::where('stage', 'PAS')
            ->where('action', 'like', '%Transfer in%')
            ->whereDate('created_at', today())
            ->count();

        $totalTransferInAll = KempuTrackingHistoryModel::where('stage', 'PAS')
            ->where('action', 'like', '%Transfer in%')
            ->count();

        $totalTransferOutToday = KempuTrackingHistoryModel::where('stage', 'PAS')
            ->where('action', 'like', '%Transfer Out%')
            ->whereDate('created_at', today())
            ->count();

        $totalTransferOutAll = KempuTrackingHistoryModel::where('stage', 'PAS')
            ->where('action', 'like', '%Transfer Out%')
            ->count();

        $totalWarning = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_PAS)
                ->where('reused_count', '>=', 18);
        })->count();

        return view('kempu.pas.report', compact(
            'cards',
            'totalCurrentPas',
            'totalTransferInToday',
            'totalTransferInAll',
            'totalTransferOutToday',
            'totalTransferOutAll',
            'totalWarning'
        ));
    }

    /**
     * AJAX Endpoint untuk Data Report Kempu PAS (Server-side Pagination & Filtering)
     */
    public function reportData(Request $request)
    {
        $viewMode = $request->input('view_mode', 'history'); // 'history' | 'current'
        $perPage  = min(100, max(5, (int) $request->input('per_page', 20)));

        if ($viewMode === 'current') {
            // Data kempu yang saat ini berada di lokasi PAS
            $query = MasterKempuModel::whereHas('main', function ($q) {
                $q->where('current_location', MasterKempuModel::LOC_PAS);
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

        // Default: Log Riwayat Scan PAS
        $query = KempuTrackingHistoryModel::where(function ($q) {
            $q->where('stage', 'PAS')
              ->orWhere('from_location', MasterKempuModel::LOC_PAS)
              ->orWhere('to_location', MasterKempuModel::LOC_PAS);
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
     * Export Report CSV untuk Scan Kempu PAS
     */
    public function exportReport(Request $request)
    {
        $query = KempuTrackingHistoryModel::where(function ($q) {
            $q->where('stage', 'PAS')
              ->orWhere('from_location', MasterKempuModel::LOC_PAS)
              ->orWhere('to_location', MasterKempuModel::LOC_PAS);
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
        $filename = 'Report_Scan_Kempu_PAS_' . now()->format('Ymd_His') . '.csv';

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
