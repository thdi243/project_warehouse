<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
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
                'location'    => MasterKempuModel::LOC_PAS,
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
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini masih berada di WPM BAS (status: 'Transfer In From PAS'). Kempu harus menyelesaikan siklus Produksi dan WFG terlebih dahulu.",
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
                        'message' => "Urutan salah: Kempu {$idKempu} masih berada di WFG BAS (status: '{$currentStatus}'). Harap lakukan 'Transfer Out to PAS' di WFG terlebih dahulu sebelum diterima di PAS.",
                    ];
                }

                // Status yang diperbolehkan untuk Transfer in From BAS (Kempu yang dikirim dari WFG ke PAS)
                $allowedPrev = [
                    'transfer out to pas',
                    strtolower(MasterKempuModel::STATUS_WFG_TRANSFER_OUT_PAS),
                    strtolower(MasterKempuModel::STATUS_IN_TRANSIT_PAS),
                ];

                if (!in_array(strtolower($currentStatus), $allowedPrev)) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} belum diproses 'Transfer Out to PAS' dari WFG (status saat ini: '{$currentStatus}'). Kempu harus berstatus 'Transfer Out to PAS' terlebih dahulu.",
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
                        'message' => "Kempu {$idKempu} sudah berstatus 'Transfer Out to BAS' (duplikat scan). Kempu siap dikirim kembali ke WPM BAS.",
                    ];
                }

                // 2. Prasyarat: Harus sudah diterima di PAS (Transfer in From BAS)
                $allowedPrev = [
                    'transfer in from bas',
                    strtolower(MasterKempuModel::STATUS_PAS_TRANSFER_IN_BAS),
                    strtolower(MasterKempuModel::STATUS_PAS_RECEIVED),
                ];

                if (!in_array(strtolower($currentStatus), $allowedPrev)) {
                    if (
                        strcasecmp($currentStatus, 'Transfer Out to PAS') === 0 ||
                        strcasecmp($currentStatus, MasterKempuModel::STATUS_WFG_TRANSFER_OUT_PAS) === 0 ||
                        strcasecmp($currentStatus, MasterKempuModel::STATUS_IN_TRANSIT_PAS) === 0
                    ) {
                        return [
                            'valid'   => false,
                            'message' => "Urutan salah: Kempu {$idKempu} belum di-scan 'Transfer in From BAS' (status saat ini: 'Transfer Out to PAS'). Harap lakukan 'Transfer in From BAS' terlebih dahulu.",
                        ];
                    }

                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} belum berada di Warehouse PAS (status saat ini: '{$currentStatus}'). Kempu harus melalui 'Transfer in From BAS' terlebih dahulu.",
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

            // Catat aktivitas pengetikan ID manual ke History Scan
            $user = Auth::user();
            $opName = $user?->nama_lengkap ?? $user?->username ?? 'User';
            $opRole = $user?->role ?? ($user?->roles?->first()?->name ?? 'Staff');
            $kempu->recordTracking(
                stage: $card['stage'] ?? 'PAS',
                action: 'Input Manual ID (' . ($card['title'] ?? 'Lookup') . ')',
                actionResult: 'MANUAL_SCAN',
                fromLocation: $kempu->main?->current_location ?? 'PAS',
                toLocation: $kempu->main?->current_location ?? 'PAS',
                condition: $kempu->main?->condition ?? 'OK',
                notes: "ID Kempu diketik manual oleh {$opName} ({$opRole})",
                userId: $user?->id,
                metadata: [
                    'input_method'  => 'MANUAL',
                    'is_manual'     => true,
                    'card_key'      => $cardKey,
                    'action_title'  => $card['title'],
                    'operator_name' => $opName,
                    'operator_role' => $opRole,
                ]
            );
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'id'                     => $kempu->id,
                'id_kempu'               => $kempu->id_kempu,
                'is_old_kempu'           => $isOldKempu,
                'is_new_kempu'           => !$isOldKempu,
                'rfid'                   => $kempu->rfid ?? '-',
                'current_location'       => $kempu->current_location ?? 'WAREHOUSE_PAS',
                'current_status'         => $currentStatus ?: 'REGISTERED',
                'reused_count'           => $reusedCount,
                'target_reused_count'    => $reusedCount,
                'will_increment_reused'  => false,
                'max_reused'             => $kempu->max_reused ?? 21,
                'has_reused'             => true,
                'can_edit_reused'        => $canEditReused,
                'condition'              => $kempu->condition ?? 'OK',
                'has_barcode'            => (bool)($kempu->main?->has_barcode ?? true),
                'has_rfid'               => (bool)($kempu->main?->has_rfid ?? true),
                'has_kitir'              => (bool)($kempu->main?->has_kitir ?? true),
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
                    'last_scanned_at'  => now(),
                    'last_action'      => $card['title'],
                ]);
            } else {
                $kempu->main->update([
                    'current_status'   => $card['status_name'],
                    'current_location' => $card['location'],
                    'reused_count'     => $targetReused,
                    'last_scanned_at'  => now(),
                    'last_action'      => $card['title'],
                ]);
            }

            if ($isManual && !str_contains($notes ?? '', '[Input Manual]')) {
                $notes = $notes ? $notes . ' [Input Manual]' : '[Input Manual]';
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
                'notes'           => $notes,
                'metadata'        => [
                    'input_method' => $isManual ? 'MANUAL' : 'SCANNER',
                    'is_manual'    => $isManual,
                ],
                'created_by'      => Auth::id(),
            ]);

            DB::commit();

            $successMsg = "Kempu {$kempu->id_kempu} berhasil dikonfirmasi ke status '{$card['status_name']}' (Reused: {$targetReused}/21x).";

            return response()->json([
                'status'  => true,
                'message' => $successMsg,
                'data'    => [
                    'id_kempu'     => $kempu->id_kempu,
                    'rfid'         => $kempu->rfid ?? '-',
                    'new_status'   => $card['status_name'],
                    'reused_count' => $targetReused,
                    'timestamp'    => now()->format('d/m/Y H:i:s'),
                    'user'         => Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'Operator',
                    'notes'        => $notes,
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

        if (!$kempu->main) {
            $kempu->main()->create([
                'id_kempu'         => $kempu->id_kempu,
                'current_location' => MasterKempuModel::LOC_PAS,
                'current_status'   => 'REGISTERED',
                'reused_count'     => (int)$newReused,
                'max_reused'       => 21,
                'condition'        => 'OK',
                'last_action'      => 'Koreksi Reused Manual di PAS oleh ' . (Auth::user()->nama_lengkap ?? Auth::user()->username),
            ]);
        } else {
            $kempu->main->update([
                'reused_count' => (int)$newReused,
                'last_action'  => 'Koreksi Reused Manual di PAS oleh ' . (Auth::user()->nama_lengkap ?? Auth::user()->username),
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
}
