<?php

namespace App\Http\Controllers\Wpm;

use App\Http\Controllers\Controller;
use App\Models\Kempu\KempuTrackingHistoryModel;
use App\Models\Kempu\MasterKempuModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WpmKempuController extends Controller
{
    /**
     * Konfigurasi Card di WPM
     */
    public static function getCards(): array
    {
        return [
            'transfer-out-to-produksi' => [
                'key'         => 'transfer-out-to-produksi',
                'title'       => 'Transfer Out To Produksi',
                'status_name' => MasterKempuModel::STATUS_WPM_TRANSFER_OUT_PROD,
                'location'    => MasterKempuModel::LOC_WPM,
                'from_loc'    => MasterKempuModel::LOC_WPM,
                'to_loc'      => MasterKempuModel::LOC_PRODUKSI,
                'stage'       => 'WPM',
                'description' => 'Pengeluaran kempu kosong atau siap pakai dari Warehouse Packaging Material (WPM) menuju bagian Produksi.',
                'icon'        => 'ri-arrow-right-up-line',
                'badge_color' => 'primary',
                'bg_tint'     => '#eff6ff',
                'icon_color'  => '#2563eb',
                'btn_color'   => '#1d4ed8',
                'btn_text'    => 'Buka Scanner Transfer Out',
            ],
            'transfer-in-from-pas' => [
                'key'         => 'transfer-in-from-pas',
                'title'       => 'Transfer In From PAS',
                'status_name' => MasterKempuModel::STATUS_WPM_TRANSFER_IN_PAS,
                'location'    => MasterKempuModel::LOC_WPM,
                'from_loc'    => MasterKempuModel::LOC_PAS,
                'to_loc'      => MasterKempuModel::LOC_WPM,
                'stage'       => 'WPM',
                'description' => 'Penerimaan kempu kosong kembali dari Warehouse PT PAS ke WPM untuk siap digunakan pada siklus baru.',
                'icon'        => 'ri-arrow-left-down-line',
                'badge_color' => 'success',
                'bg_tint'     => '#f0fdf4',
                'icon_color'  => '#16a34a',
                'btn_color'   => '#15803d',
                'btn_text'    => 'Buka Scanner Transfer In',
            ],
        ];
    }

    /**
     * Halaman Card Hub Kempu WPM
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

        $totalWpm = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_WPM);
        })->count();

        return view('wpm.kempu.index', compact('cards', 'totalWpm'));
    }

    /**
     * Halaman Scanner Khusus Card di WPM
     */
    public function scan($cardKey)
    {
        $cards = self::getCards();

        if (!array_key_exists($cardKey, $cards)) {
            return redirect()->route('wpm.kempu.index')->with('error', 'Pilihan proses kempu tidak valid.');
        }

        $card = $cards[$cardKey];
        $canManualInput = MasterKempuModel::canManualInput();

        return view('wpm.kempu.scan', compact('card', 'cards', 'canManualInput'));
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
        if ($user->hasAnyPermission(['super-admin', 'wpm-kempu-reused'])) {
            return true;
        }

        return false;
    }

    /**
     * Validasi alur urutan status dan pencegahan duplikat scan di WPM
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
            case 'transfer-in-from-pas':
                // 1. Cek duplikat scan
                if (
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WPM_TRANSFER_IN_PAS) === 0 ||
                    strcasecmp($currentStatus, 'Transfer In From PAS') === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Kempu {$idKempu} sudah berstatus 'Transfer In From PAS' (duplikat scan). Silakan lanjutkan ke tahap pemeriksaan 'QC PM'.",
                    ];
                }

                // 2. Cek jika kempu sudah berstatus QC_PM_RELEASE / QC_PM_PASSED
                // Kempu yang sudah lolos QC PM wajib melanjutkan ke 'Transfer Out To Produksi', tidak boleh scan Transfer In From PAS!
                $qcPmReleaseStatuses = [
                    strtolower(MasterKempuModel::STATUS_QC_PM_RELEASE),
                    strtolower(MasterKempuModel::STATUS_QC_PM_PASSED),
                    'qc_pm_release',
                    'qc pm release',
                    'qc pm passed',
                    'qc pm lolos (ok)',
                ];
                if (in_array(strtolower($currentStatus), $qcPmReleaseStatuses)) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini sudah berstatus '{$currentStatus}' (Lolos QC PM). Kempu tidak dapat di-Transfer In From PAS lagi dan harus dilanjutkan ke tahap 'Transfer Out to Produksi'.",
                    ];
                }

                // 3. Cek jika kempu sedang dalam antrean/pemeriksaan QC PM (QC_PM_PENDING / QC_PM_HOLD)
                $qcPmPendingStatuses = [
                    strtolower(MasterKempuModel::STATUS_QC_PM_PENDING),
                    strtolower(MasterKempuModel::STATUS_QC_PM_HOLD),
                    'qc_pm_pending',
                    'qc_pm_hold',
                ];
                if (in_array(strtolower($currentStatus), $qcPmPendingStatuses)) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini berstatus '{$currentStatus}'. Kempu sedang dalam antrean/pemeriksaan QC PM dan tidak dapat di-Transfer In From PAS. Silakan selesaikan pemeriksaan QC PM terlebih dahulu.",
                    ];
                }

                // 4. Cek jika kempu berstatus REPAIR di Workshop Engineering
                if (
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_ENG_REPAIR) === 0 ||
                    strcasecmp($currentStatus, 'ENG_REPAIR') === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini berstatus REPAIR di Workshop Engineering. Kempu harus diselesaikan perbaikannya terlebih dahulu.",
                    ];
                }

                // 5. Cek kempu baru dan reused count masih 0 (hanya boleh Transfer Out To Produksi)
                $isOldKempu  = MasterKempuModel::isOldKempu($idKempu);
                $reusedCount = (int)($kempu->main?->reused_count ?? $kempu->reused_count ?? 0);

                if (!$isOldKempu && $reusedCount <= 0) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} merupakan kempu baru (siklus Reused masih 0x). Kempu baru belum pernah dikirim ke Produksi/PAS, sehingga hanya dapat diproses pada tahap 'Transfer Out To Produksi'.",
                    ];
                }

                // 6. Khusus kempu baru: Cek jika status masih awal (REGISTERED, QC_PM_PENDING, QC_PM_RELEASE, GR_COMPLETED) dan belum pernah melewati siklus WFG/PAS
                $initialStatuses = [
                    'registered',
                    strtolower(MasterKempuModel::STATUS_REGISTERED),
                    strtolower(MasterKempuModel::STATUS_QC_PM_PENDING),
                    strtolower(MasterKempuModel::STATUS_QC_PM_RELEASE),
                    strtolower(MasterKempuModel::STATUS_QC_PM_PASSED),
                    strtolower(MasterKempuModel::STATUS_GR_COMPLETED),
                ];
                if (!$isOldKempu && in_array(strtolower($currentStatus), $initialStatuses) && !$kempu->hasPassedWfg()) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} masih berada pada tahap awal (status: '{$currentStatus}') dan belum pernah dikirim ke Produksi/PAS. Kempu harus melalui 'Transfer Out To Produksi' terlebih dahulu.",
                    ];
                }

                // 7. Cek jika kempu masih dalam perjalanan ke PAS (Transfer Out to PAS dari WFG)
                if (
                    strcasecmp($currentStatus, 'Transfer Out to PAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WFG_TRANSFER_OUT_PAS) === 0 ||
                    strcasecmp($currentStatus, 'WFG_TRANSFER_OUT_TO_PAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_IN_TRANSIT_PAS) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini berstatus 'Transfer Out to PAS' (sedang dalam perjalanan menuju PT PAS) dan belum diterima di PT PAS ('Transfer in From BAS'). Kempu harus menyelesaikan siklus penerimaan dan pengembalian di PT PAS terlebih dahulu sebelum dapat di-Transfer In ke WPM.",
                    ];
                }

                // 8. Cek jika masih di area WFG
                if (in_array(strtolower($currentStatus), [
                    'transfer in from produksi',
                    strtolower(MasterKempuModel::STATUS_WFG_TRANSFER_IN_PROD),
                    strtolower(MasterKempuModel::STATUS_WFG_RECEIVED),
                ])) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini masih berada di area WFG (status: '{$currentStatus}'). Kempu harus dikirim ke PT PAS terlebih dahulu.",
                    ];
                }

                // 9. Cek jika kempu masih berada di Warehouse PT PAS (belum di-Transfer Out dari PAS)
                if (
                    strcasecmp($currentStatus, 'Transfer in From BAS') === 0 ||
                    strcasecmp($currentStatus, 'Transfer In From BAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_PAS_TRANSFER_IN_BAS) === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_PAS_RECEIVED) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} masih berada di Warehouse PT PAS (status: 'Transfer in From BAS'). Kempu harus menyelesaikan pengiriman 'Transfer Out to BAS' dari PAS terlebih dahulu sebelum dapat di-Transfer In ke WPM.",
                    ];
                }

                // 10. Cek jika masih di status Transfer Out To Produksi atau area Produksi
                if (
                    strcasecmp($currentStatus, 'Transfer Out To Produksi') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WPM_TRANSFER_OUT_PROD) === 0 ||
                    strcasecmp($currentStatus, 'IN_TRANSIT_PRODUKSI') === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini berstatus 'Transfer Out To Produksi' (sedang menuju Produksi/WFG). Harus menyelesaikan siklus hingga PAS sebelum dapat di-Transfer In ke WPM.",
                    ];
                }

                if (in_array(strtolower($currentStatus), [
                    strtolower(MasterKempuModel::STATUS_PROD_TRANSFER_IN_WPM),
                    'prod_transfer_in_from_wpm',
                    'transfer in from wpm',
                    strtolower(MasterKempuModel::STATUS_PROD_CUCI_KEMPU),
                    'prod_cuci_kempu',
                    strtolower(MasterKempuModel::STATUS_PROD_FILLING_KEMPU),
                    'prod_filling_kempu',
                    strtolower(MasterKempuModel::STATUS_PROD_TRANSFER_OUT_WFG),
                    'prod_transfer_out_to_wfg',
                    strtolower(MasterKempuModel::STATUS_PROD_REPRO_KEMPU),
                    'prod_repro_kempu',
                ])) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini masih berada di area Produksi (status: '{$currentStatus}').",
                    ];
                }

                // 11. Whitelist: HANYA kempu yang sudah di-Transfer Out dari PAS yang sah untuk di-Transfer In ke WPM
                $allowedFromPas = [
                    'transfer out to bas',
                    strtolower(MasterKempuModel::STATUS_PAS_TRANSFER_OUT_BAS),
                    'pas_transfer_out_to_bas',
                    strtolower(MasterKempuModel::STATUS_IN_TRANSIT_WPM),
                ];

                $isFromPas = in_array(strtolower($currentStatus), $allowedFromPas);

                // Pengecualian khusus Kempu Lama:
                // HANYA kempu lama yang baru didaftarkan di sistem (status awal REGISTERED / GR_COMPLETED) dan siklus reused masih 0
                // yang diizinkan untuk diinput reused manual pertama kali di 'Transfer In From PAS'.
                // Jika kempu sudah memiliki siklus reused (> 0) atau statusnya sudah melewati registrasi awal (seperti QC_PM_RELEASE), kempu WAJIB mengikuti alur normal dari PAS ($isFromPas).
                $isInitialRegistration = in_array(strtolower($currentStatus), [
                    'registered',
                    strtolower(MasterKempuModel::STATUS_REGISTERED),
                    strtolower(MasterKempuModel::STATUS_GR_COMPLETED),
                    'gr_completed',
                ]);
                $isOldKempuInitialAllowed = ($isOldKempu && $isInitialRegistration && $reusedCount <= 0 && !$kempu->hasPassedWfg());

                if (!$isFromPas && !$isOldKempuInitialAllowed) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini berstatus '{$currentStatus}'. Untuk menjalankan 'Transfer In From PAS', kempu harus berstatus 'Transfer Out to BAS' dari Warehouse PT PAS.",
                    ];
                }
                break;

            case 'transfer-out-to-produksi':
                // 1. Cek duplikat scan
                if (
                    strcasecmp($currentStatus, 'Transfer Out To Produksi') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WPM_TRANSFER_OUT_PROD) === 0 ||
                    strcasecmp($currentStatus, 'WPM_TRANSFER_OUT_TO_PROD') === 0 ||
                    strcasecmp($currentStatus, 'IN_TRANSIT_PRODUKSI') === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Kempu {$idKempu} sudah berstatus 'Transfer Out To Produksi' (duplikat scan). Silakan lanjutkan proses di Produksi / WFG.",
                    ];
                }

                // 2. Cek urutan WPM: Jika kempu baru kembali dari PAS, wajib scan Transfer In From PAS dulu
                if (
                    strcasecmp($currentStatus, 'Transfer Out to PAS') === 0 ||
                    strcasecmp($currentStatus, 'Transfer Out to BAS') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_PAS_TRANSFER_OUT_BAS) === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} baru kembali dari PAS (status saat ini: '{$currentStatus}'). Kempu harus melalui tahap 'Transfer In From PAS' terlebih dahulu sebelum dapat di-Transfer Out ke Produksi.",
                    ];
                }

                // 3. Cek jika kempu baru selesai di-Transfer In From PAS (belum diperiksa & disetujui oleh QC PM)
                if (
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_WPM_TRANSFER_IN_PAS) === 0 ||
                    strcasecmp($currentStatus, 'WPM_TRANSFER_IN_PAS') === 0 ||
                    strcasecmp($currentStatus, 'WPM_TRANSFER_IN_FROM_PAS') === 0 ||
                    strcasecmp($currentStatus, 'Transfer In From PAS') === 0 ||
                    strcasecmp($currentStatus, 'Transfer in From PAS') === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} baru diterima dari PAS (status: '{$currentStatus}'). Kempu wajib melewati proses pemeriksaan 'QC PM' terlebih dahulu dan dinyatakan Lolos (Release) sebelum dapat di-Transfer Out ke Produksi.",
                    ];
                }

                // 4. Cek jika kempu baru masih berstatus awal pendaftaran / GR (belum diperiksa oleh QC PM)
                if (
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_REGISTERED) === 0 ||
                    strcasecmp($currentStatus, 'REGISTERED') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_GR_COMPLETED) === 0 ||
                    strcasecmp($currentStatus, 'GR_COMPLETED') === 0 ||
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_QC_PM_PENDING) === 0 ||
                    strcasecmp($currentStatus, 'QC_PM_PENDING') === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} belum diperiksa oleh QC PM (status saat ini: '{$currentStatus}'). Kempu wajib melewati proses pemeriksaan 'QC PM' terlebih dahulu dan dinyatakan Lolos (Release) sebelum dapat di-Transfer Out ke Produksi.",
                    ];
                }

                // 5. Cek jika masih berada di area WFG
                if (in_array(strtolower($currentStatus), [
                    'transfer in from produksi',
                    strtolower(MasterKempuModel::STATUS_WFG_TRANSFER_IN_PROD),
                    'wfg_transfer_in_from_prod',
                ])) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini masih berada di area WFG (status: '{$currentStatus}').",
                    ];
                }

                // 6. Cek jika masih berada di area Produksi
                if (in_array(strtolower($currentStatus), [
                    strtolower(MasterKempuModel::STATUS_PROD_TRANSFER_IN_WPM),
                    'prod_transfer_in_from_wpm',
                    'transfer in from wpm',
                    strtolower(MasterKempuModel::STATUS_PROD_CUCI_KEMPU),
                    'prod_cuci_kempu',
                    strtolower(MasterKempuModel::STATUS_PROD_FILLING_KEMPU),
                    'prod_filling_kempu',
                    strtolower(MasterKempuModel::STATUS_PROD_TRANSFER_OUT_WFG),
                    'prod_transfer_out_to_wfg',
                    strtolower(MasterKempuModel::STATUS_PROD_REPRO_KEMPU),
                    'prod_repro_kempu',
                ])) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini masih berada di area Produksi (status: '{$currentStatus}').",
                    ];
                }

                // 7. Cek jika kempu berstatus Reject / Menunggu Perbaikan
                if (
                    strcasecmp($currentStatus, MasterKempuModel::STATUS_ENG_REPAIR) === 0 ||
                    strcasecmp($currentStatus, 'ENG_REPAIR') === 0 ||
                    strcasecmp($currentStatus, 'QC_PM_REJECT') === 0
                ) {
                    return [
                        'valid'   => false,
                        'message' => "Kempu {$idKempu} berstatus Reject / Menunggu Perbaikan di Engineering Workshop (status: '{$currentStatus}') dan tidak dapat dikirim ke Produksi.",
                    ];
                }

                // 8. Seluruh kempu (baik baru maupun lama yang datang dari PAS) WAJIB dinyatakan Lolos (Release) oleh QC PM
                $passedQcPmStatuses = [
                    MasterKempuModel::STATUS_QC_PM_RELEASE,
                    MasterKempuModel::STATUS_QC_PM_PASSED,
                    'QC_PM_RELEASE',
                    'QC_PM_PASSED',
                    'QC PM Release',
                    'QC PM Passed',
                    'QC PM Lolos (OK)',
                    'QC PM Lolos (Release)',
                ];

                $isQcPmReleased = false;
                foreach ($passedQcPmStatuses as $st) {
                    if (strcasecmp($currentStatus, $st) === 0) {
                        $isQcPmReleased = true;
                        break;
                    }
                }

                if (!$isQcPmReleased) {
                    return [
                        'valid'   => false,
                        'message' => "Urutan salah: Kempu {$idKempu} saat ini berstatus '{$currentStatus}'. Kempu (baru maupun lama dari PAS) wajib dinyatakan Lolos (Release) oleh QC PM terlebih dahulu sebelum dapat di-Transfer Out ke Produksi.",
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
                'current_location' => MasterKempuModel::LOC_WPM,
                'current_status'   => 'REGISTERED',
                'reused_count'     => 0,
                'max_reused'       => 21,
                'condition'        => 'OK',
            ]);
            $kempu->load('main');
        }

        $isOldKempu = MasterKempuModel::isOldKempu($kempu->id_kempu);
        $reusedCount = (int)($kempu->main->reused_count ?? 0);
        $canEditReused = self::canEditReused();
        $flowValidation = self::validateStatusFlow($kempu, $cardKey);

        // Aturan Siklus Reused di WPM (berlaku untuk Transfer Out To Produksi dan Transfer In From PAS):
        // - Kempu Baru (format YYMMDD / 10 digit): Siklus baru, TIDAK PERLU registrasi reused ($hasReused = true).
        // - Kempu Lama: Jika reused masih 0, MUNCULKAN peringatan / wajib registrasi reused ($hasReused = false).
        if (!$isOldKempu) {
            // Kempu Baru (format YYMMDD) -> Siklus baru, tidak perlu registrasi reused
            $hasReused = true;
        } else {
            // Kempu Lama -> Jika masih 0, wajib registrasi reused oleh otoritator
            $hasReused = $reusedCount > 0;
        }

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
                stage: $card['stage'] ?? 'WPM',
                action: 'Input Manual ID (' . ($card['title'] ?? 'Lookup') . ')',
                actionResult: 'MANUAL_SCAN',
                fromLocation: $kempu->main?->current_location ?? 'WPM',
                toLocation: $kempu->main?->current_location ?? 'WPM',
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
                'id'               => $kempu->id,
                'id_kempu'         => $kempu->id_kempu,
                'is_old_kempu'     => $isOldKempu,
                'is_new_kempu'     => !$isOldKempu,
                'rfid'             => $kempu->rfid ?? '-',
                'current_location' => $kempu->current_location ?? 'WPM',
                'current_status'   => $kempu->current_status ?? 'REGISTERED',
                'reused_count'     => $reusedCount,
                'max_reused'       => $kempu->max_reused ?? 21,
                'has_reused'       => $hasReused,
                'can_edit_reused'  => $canEditReused,
                'condition'        => $kempu->condition ?? 'OK',
                'has_barcode'      => (bool)($kempu->main?->has_barcode ?? true),
                'has_rfid'         => (bool)($kempu->main?->has_rfid ?? true),
                'has_nti'        => (bool)($kempu->main?->has_nti ?? true),
                'last_scanned_at'  => $kempu->last_scanned_at ? $kempu->last_scanned_at->format('d/m/Y H:i') : '-',
                'last_action'      => $kempu->last_action ?? '-',
                'target_status'    => $card['status_name'],
                'card_title'       => $card['title'],
                'is_flow_valid'    => $flowValidation['valid'],
                'flow_error'       => $flowValidation['message'],
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

        $isManual       = $request->boolean('is_manual');

        if ($isManual && !MasterKempuModel::canManualInput()) {
            return response()->json([
                'status'  => false,
                'message' => 'Akses ditolak: Operator tidak memiliki izin untuk mengonfirmasi transaksi dari pengetikan ID manual.',
            ], 403);
        }

        // Validasi Alur Status (Urutan & Cegah Duplikat Scan)
        $flowValidation = self::validateStatusFlow($kempu, $cardKey);
        if (!$flowValidation['valid']) {
            return response()->json([
                'status'  => false,
                'message' => $flowValidation['message'],
            ], 422);
        }

        $isOldKempu    = MasterKempuModel::isOldKempu($kempu->id_kempu);
        $currentReused = (int)($kempu->main?->reused_count ?? 0);

        // Validasi Siklus Reused di WPM:
        // Kempu LAMA jika reused masih 0x wajib registrasi reused terlebih dahulu oleh user yang berwenang.
        // Kempu baru (YYMMDD) TIDAK mewajibkan registrasi reused.
        if ($isOldKempu && $currentReused <= 0) {
            // Cek apakah ada input nilai reused baru
            if ($newReusedCount !== null && $newReusedCount !== '') {
                // Wajib memiliki hak otorisasi
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

                $currentReused = (int)$newReusedCount;
            } else {
                // Kempu lama belum punya reused dan tidak diisi nilai reused
                return response()->json([
                    'status'  => false,
                    'message' => 'Kempu lama belum memiliki data siklus Reused. Silakan input nilai Reused fisik terlebih dahulu oleh user yang berwenang.',
                ], 422);
            }
        }

        $hasBarcode     = filter_var($request->input('has_barcode', true), FILTER_VALIDATE_BOOLEAN);
        $hasRfid        = filter_var($request->input('has_rfid', true), FILTER_VALIDATE_BOOLEAN);
        $hasNti       = filter_var($request->input('has_nti', true), FILTER_VALIDATE_BOOLEAN);

        // Ringkasan Checklist Fisik
        $physicalCheck = [];
        $physicalCheck[] = 'Barcode: ' . ($hasBarcode ? 'Ada' : 'Tidak Ada');
        $physicalCheck[] = 'RFID: ' . ($hasRfid ? 'Ada' : 'Tidak Ada');
        $physicalCheck[] = 'Kitir: ' . ($hasNti ? 'Ada' : 'Tidak Ada');
        $checklistStr = '[Fisik: ' . implode(', ', $physicalCheck) . ']';
        $finalNotes = $notes ? $checklistStr . ' - ' . $notes : $checklistStr;
        if ($isManual) {
            $finalNotes .= ' [Input Manual]';
        }

        DB::beginTransaction();
        try {
            if (!$kempu->main) {
                $kempu->main()->create([
                    'id_kempu'         => $kempu->id_kempu,
                    'current_location' => $card['location'],
                    'current_status'   => $card['status_name'],
                    'reused_count'     => $currentReused,
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
                    'reused_count'     => $currentReused,
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
                'reused_count'    => $currentReused,
                'condition'       => $kempu->main->condition ?? 'OK',
                'notes'           => $finalNotes,
                'metadata'        => [
                    'has_barcode'  => $hasBarcode,
                    'has_rfid'     => $hasRfid,
                    'has_nti'      => $hasNti,
                    'input_method' => $isManual ? 'MANUAL' : 'SCANNER',
                    'is_manual'    => $isManual,
                ],
                'created_by'      => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Kempu {$kempu->id_kempu} berhasil dikonfirmasi ke status '{$card['status_name']}' (Reused: {$currentReused}/21x).",
                'data'    => [
                    'id_kempu'        => $kempu->id_kempu,
                    'rfid'            => $kempu->rfid ?? '-',
                    'new_status'      => $card['status_name'],
                    'reused_count'    => $currentReused,
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
                'current_location' => MasterKempuModel::LOC_WPM,
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
     * Riwayat scan sesi terkini di WPM
     */
    public function recentScans(Request $request)
    {
        $cardKey = $request->input('card_key');
        $query = KempuTrackingHistoryModel::where('stage', 'WPM')
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
     * Halaman Report Scan Kempu WPM
     */
    public function report(Request $request)
    {
        $cards = self::getCards();

        $totalCurrentWpm = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_WPM);
        })->count();

        $totalTransferInToday = KempuTrackingHistoryModel::where('stage', 'WPM')
            ->where('action', 'like', '%Transfer in%')
            ->whereDate('created_at', today())
            ->count();

        $totalTransferInAll = KempuTrackingHistoryModel::where('stage', 'WPM')
            ->where('action', 'like', '%Transfer in%')
            ->count();

        $totalTransferOutToday = KempuTrackingHistoryModel::where('stage', 'WPM')
            ->where('action', 'like', '%Transfer Out%')
            ->whereDate('created_at', today())
            ->count();

        $totalTransferOutAll = KempuTrackingHistoryModel::where('stage', 'WPM')
            ->where('action', 'like', '%Transfer Out%')
            ->count();

        $totalWarning = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_WPM)
                ->where('reused_count', '>=', 18);
        })->count();

        return view('wpm.kempu.report', compact(
            'cards',
            'totalCurrentWpm',
            'totalTransferInToday',
            'totalTransferInAll',
            'totalTransferOutToday',
            'totalTransferOutAll',
            'totalWarning'
        ));
    }

    /**
     * AJAX Endpoint untuk Data Report Kempu WPM (Server-side Pagination & Filtering)
     */
    public function reportData(Request $request)
    {
        $viewMode = $request->input('view_mode', 'history'); // 'history' | 'current'
        $perPage  = min(100, max(5, (int) $request->input('per_page', 20)));

        if ($viewMode === 'current') {
            // Data kempu yang saat ini berada di lokasi WPM
            $query = MasterKempuModel::whereHas('main', function ($q) {
                $q->where('current_location', MasterKempuModel::LOC_WPM);
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

        // Default: Log Riwayat Scan WPM
        $query = KempuTrackingHistoryModel::where(function ($q) {
            $q->where('stage', 'WPM')
                ->orWhere('from_location', MasterKempuModel::LOC_WPM)
                ->orWhere('to_location', MasterKempuModel::LOC_WPM);
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
     * Export Report CSV untuk Scan Kempu WPM
     */
    public function exportReport(Request $request)
    {
        $query = KempuTrackingHistoryModel::where(function ($q) {
            $q->where('stage', 'WPM')
                ->orWhere('from_location', MasterKempuModel::LOC_WPM)
                ->orWhere('to_location', MasterKempuModel::LOC_WPM);
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
        $filename = 'Report_Scan_Kempu_WPM_' . now()->format('Ymd_His') . '.csv';

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
