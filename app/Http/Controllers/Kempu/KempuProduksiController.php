<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
use App\Models\Kempu\KempuTrackingHistoryModel;
use App\Models\Kempu\MasterKempuModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KempuProduksiController extends Controller
{
    /**
     * Konfigurasi 5 Card di Menu Produksi
     */
    public static function getCards(): array
    {
        return [
            'transfer-in-from-wpm' => [
                'key'             => 'transfer-in-from-wpm',
                'title'           => 'Transfer in from WPM',
                'subtitle'        => 'Penerimaan dari WPM',
                'status_name'     => MasterKempuModel::STATUS_PROD_TRANSFER_IN_WPM,
                'stage'           => 'PRODUKSI',
                'location'        => MasterKempuModel::LOC_PRODUKSI,
                'from_loc'        => MasterKempuModel::LOC_WPM,
                'to_loc'          => MasterKempuModel::LOC_PRODUKSI,
                'next_status'     => MasterKempuModel::STATUS_PROD_TRANSFER_IN_WPM,
                'icon'            => 'ri-arrow-left-down-line',
                'badge_color'     => 'primary',
                'btn_text'        => 'Buka Scanner Transfer In WPM',
                'target_statuses' => [
                    MasterKempuModel::STATUS_WPM_TRANSFER_OUT_PROD,
                    'WPM_TRANSFER_OUT_PROD',
                    'IN_TRANSIT_PRODUKSI',
                    'Transfer Out To Produksi',
                    'Transfer Out to Produksi',
                ],
                'description'     => 'Penerimaan kempu dari WPM menuju Produksi. Status selanjutnya diteruskan ke pengecekan QC Pre Cuci.',
            ],
            'cuci-kempu' => [
                'key'             => 'cuci-kempu',
                'title'           => 'Cuci Kempu',
                'subtitle'        => 'Pencucian & Pembersihan Kempu',
                'status_name'     => MasterKempuModel::STATUS_PROD_CUCI_KEMPU,
                'stage'           => 'PRODUKSI',
                'location'        => MasterKempuModel::LOC_PRODUKSI,
                'from_loc'        => MasterKempuModel::LOC_PRODUKSI,
                'to_loc'          => MasterKempuModel::LOC_PRODUKSI,
                'next_status'     => MasterKempuModel::STATUS_PROD_CUCI_KEMPU,
                'icon'            => 'ri-water-flash-line',
                'badge_color'     => 'info',
                'btn_text'        => 'Buka Scanner Cuci Kempu',
                'target_statuses' => [
                    MasterKempuModel::STATUS_QC_PRE_CUCI_RELEASE,
                    MasterKempuModel::STATUS_QC_PRE_CUCI_PASSED,
                    'QC_PRE_CUCI_RELEASE',
                    'QC_PRE_CUCI_PASSED',
                    'QC Pre Cuci Release',
                    'QC Pre Cuci Passed',
                    'QC Pre Cuci Lolos (OK)',
                    MasterKempuModel::STATUS_PROD_CUCI_KEMPU,
                    'CUCI_KEMPU_COMPLETED',
                    'Cuci Kempu Selesai',
                ],
                'description'     => 'Pencucian dan pembersihan kempu yang telah dinyatakan lolos QC Pre Cuci atau pencucian ulang kempu yang kadaluarsa (> H+3).',
            ],
            'scan-1-filling-kempu' => [
                'key'             => 'scan-1-filling-kempu',
                'title'           => 'Filling Kempu',
                'subtitle'        => 'Pengisian Muatan Kempu',
                'status_name'     => MasterKempuModel::STATUS_PROD_FILLING_KEMPU,
                'stage'           => 'PRODUKSI',
                'location'        => MasterKempuModel::LOC_PRODUKSI,
                'from_loc'        => MasterKempuModel::LOC_PRODUKSI,
                'to_loc'          => MasterKempuModel::LOC_PRODUKSI,
                'next_status'     => MasterKempuModel::STATUS_PROD_FILLING_KEMPU,
                'icon'            => 'ri-battery-2-charge-line',
                'badge_color'     => 'success',
                'btn_text'        => 'Buka Scanner Scan 1 Filling',
                'target_statuses' => [
                    MasterKempuModel::STATUS_PROD_CUCI_KEMPU,
                    'CUCI_KEMPU_COMPLETED',
                    'Cuci Kempu Selesai',
                ],
                'description'     => 'Pengisian muatan kempu pasca pencucian (Wajib H+1 s/d H+3 dari waktu Cuci Kempu).',
            ],
            'transfer-out-to-wfg' => [
                'key'             => 'transfer-out-to-wfg',
                'title'           => 'Transfer Out to WFG',
                'subtitle'        => 'Pengiriman ke WFG',
                'status_name'     => MasterKempuModel::STATUS_PROD_TRANSFER_OUT_WFG,
                'stage'           => 'PRODUKSI',
                'location'        => MasterKempuModel::LOC_PRODUKSI,
                'from_loc'        => MasterKempuModel::LOC_PRODUKSI,
                'to_loc'          => MasterKempuModel::LOC_WFG,
                'next_status'     => MasterKempuModel::STATUS_PROD_TRANSFER_OUT_WFG,
                'icon'            => 'ri-arrow-right-up-line',
                'badge_color'     => 'primary',
                'btn_text'        => 'Buka Scanner Transfer Out WFG',
                'target_statuses' => [
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_RELEASE,
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_PASSED,
                    'QC_AFTER_FILLING_RELEASE',
                    'QC_AFTER_FILLING_PASSED',
                    'QC After Filling Release',
                    'QC After Filling Passed',
                    'QC After Filling Lolos (OK)',
                ],
                'description'     => 'Pengeluaran kempu yang telah lolos QC After Filling dari Produksi menuju Warehouse Finished Goods (WFG).',
            ],
            'repro-kempu' => [
                'key'             => 'repro-kempu',
                'title'           => 'Repro Kempu',
                'subtitle'        => 'Pengosongan Produk Reject',
                'status_name'     => MasterKempuModel::STATUS_PROD_REPRO_KEMPU,
                'stage'           => 'PRODUKSI',
                'location'        => MasterKempuModel::LOC_PRODUKSI,
                'from_loc'        => MasterKempuModel::LOC_PRODUKSI,
                'to_loc'          => MasterKempuModel::LOC_ENG,
                'next_status'     => MasterKempuModel::STATUS_ENG_REPAIR,
                'icon'            => 'ri-recycle-line',
                'badge_color'     => 'warning',
                'btn_text'        => 'Buka Scanner Repro Kempu',
                'target_statuses' => [
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_REPRO,
                    'QC_AFTER_FILLING_REPRO',
                    'QC After Filling Repro',
                    'QC After Filling Repro (Produk Reject)',
                    'REPRO',
                ],
                'description'     => 'Pengosongan muatan produk reject hasil QC After Filling. Setelah discan, kempu otomatis diteruskan ke Workshop Engineering (Repair).',
            ],
            // 'transfer-in-from-wfg' => [
            //     'key'             => 'transfer-in-from-wfg',
            //     'title'           => 'Transfer in From WFG',
            //     'subtitle'        => 'Penerimaan Retur / Reject WFG',
            //     'status_name'     => MasterKempuModel::STATUS_PROD_TRANSFER_IN_WFG,
            //     'stage'           => 'PRODUKSI',
            //     'location'        => MasterKempuModel::LOC_PRODUKSI,
            //     'from_loc'        => MasterKempuModel::LOC_WFG,
            //     'to_loc'          => MasterKempuModel::LOC_PRODUKSI,
            //     'next_status'     => MasterKempuModel::STATUS_PROD_TRANSFER_IN_WFG,
            //     'icon'            => 'ri-reply-line',
            //     'badge_color'     => 'warning',
            //     'btn_text'        => 'Buka Scanner Transfer In WFG',
            //     'target_statuses' => [
            //         MasterKempuModel::STATUS_WFG_REJECT_PROD,
            //         'WFG_REJECT_PRODUKSI',
            //         'WFG_REJECT_TO_PROD',
            //         'Reject ke Produksi',
            //         'Transfer Out to Produksi',
            //         'IN_TRANSIT_PRODUKSI',
            //         'NTI_PRODUKSI',
            //     ],
            //     'description'     => 'Penerimaan kempu reject/retur dari WFG untuk evaluasi ulang oleh QC Proses atau pembuatan BA Scrap.',
            // ],
            'create-ba-scrap' => [
                'key'             => 'create-ba-scrap',
                'title'           => 'Create BA Scrap',
                'subtitle'        => 'Berita Acara Scrap',
                'status_name'     => MasterKempuModel::STATUS_SCRAPPED,
                'stage'           => 'PRODUKSI',
                'location'        => MasterKempuModel::LOC_PRODUKSI,
                'from_loc'        => MasterKempuModel::LOC_PRODUKSI,
                'to_loc'          => MasterKempuModel::LOC_SCRAP,
                'next_status'     => MasterKempuModel::STATUS_SCRAPPED,
                'icon'            => 'ri-delete-bin-line',
                'badge_color'     => 'danger',
                'btn_text'        => 'Buka Scanner BA Scrap',
                'target_statuses' => [
                    MasterKempuModel::STATUS_ENG_SCRAP_PROD,
                    'ENG_SCRAP_PRODUKSI',
                    'NTI_PRODUKSI',
                    'ENG_REPAIR_SCRAP',
                    'Engineering Workshop (Tidak OK / Scrap)',
                    'NOT_OK',
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_REJECT,
                    MasterKempuModel::STATUS_SCRAPPED,
                    'SCRAPPED',
                    'SCRAP',
                    'Scrap',
                ],
                'description'     => 'Pembuatan Berita Acara (BA) Scrap untuk kempu rusak permanen yang dinyatakan Scrap oleh Engineering Workshop / QC.',
            ],
            'prod-force' => [
                'key'             => 'prod-force',
                'title'           => 'Force Scan Produksi',
                'subtitle'        => 'Manual Override Bebas Alur',
                'status_name'     => 'PROD_FORCE',
                'stage'           => 'PRODUKSI_FORCE',
                'location'        => MasterKempuModel::LOC_PRODUKSI,
                'from_loc'        => MasterKempuModel::LOC_PRODUKSI,
                'to_loc'          => MasterKempuModel::LOC_PRODUKSI,
                'next_status'     => 'PROD_FORCE',
                'icon'            => 'ri-shield-flash-line',
                'badge_color'     => 'danger',
                'btn_text'        => 'Buka Scanner Force Produksi',
                'target_statuses' => ['*'],
                'description'     => 'Eksekusi paksa alur dan status kempu di Produksi tanpa terikat urutan alur normal atau jeda waktu cuci (Khusus Otoritas Produksi).',
                'count'           => 'Otoritas',
                'count_label'     => 'Akses Khusus',
            ],
        ];
    }

    /**
     * Cek apakah user memiliki otoritas Force Scan di Produksi
     */
    public static function canForceScan($user = null): bool
    {
        $roleName = strtolower(trim(request()->input('user_role', request()->input('operator_role', ''))));
        if ($roleName === 'operator') {
            return false;
        }

        $user = $user ?? Auth::user();
        if ($user) {
            if (method_exists($user, 'hasAnyPermission')) {
                try {
                    if ($user->hasAnyPermission(['super-admin', 'kempu-prod-force', 'kempu-qc-force'])) {
                        return true;
                    }
                } catch (\Throwable $e) {
                }
            }
            if (method_exists($user, 'hasRole')) {
                try {
                    if ($user->hasRole('super-admin')) {
                        return true;
                    }
                    if ($user->hasRole('operator')) {
                        return false;
                    }
                } catch (\Throwable $e) {
                }
            }
        }

        return true;
    }

    /**
     * API: Get Data Card Produksi dan Jumlah Pending
     */
    public function cardsApi()
    {
        $cards = self::getCards();

        foreach ($cards as $key => &$card) {
            $targetStatuses = $card['target_statuses'];
            if ($targetStatuses === ['*'] || in_array('*', $targetStatuses)) {
                $card['count'] = 'Otoritas';
                $card['count_label'] = 'Akses Khusus';
                continue;
            }
            $card['count'] = MasterKempuModel::whereHas('main', function ($q) use ($targetStatuses, $key) {
                $q->whereIn('current_status', $targetStatuses);
                if ($key !== 'create-ba-scrap') {
                    $q->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
                }
            })->where('status', '!=', 'nonaktif')->count();
        }
        unset($card);

        $totalProduksi = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_PRODUKSI)
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->where('status', '!=', 'nonaktif')->count();

        return response()->json([
            'status' => true,
            'data'   => [
                'total_in_produksi' => $totalProduksi,
                'cards'             => array_values($cards),
            ],
        ]);
    }

    /**
     * Halaman Hub Index Menu Produksi (Cards)
     */
    public function index()
    {
        $cards = self::getCards();

        // Hitung kempu pada masing-masing proses
        foreach ($cards as $key => &$card) {
            $targetStatuses = $card['target_statuses'];
            if ($targetStatuses === ['*'] || in_array('*', $targetStatuses)) {
                $card['count'] = 'Otoritas';
                $card['count_label'] = 'Akses Khusus';
                continue;
            }
            $card['count'] = MasterKempuModel::whereHas('main', function ($q) use ($targetStatuses, $key) {
                $q->whereIn('current_status', $targetStatuses);
                if ($key !== 'create-ba-scrap') {
                    $q->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
                }
            })->where('status', '!=', 'nonaktif')->count();
        }
        unset($card);

        $totalProduksi = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_PRODUKSI);
        })->count();

        return view('kempu.produksi.index', compact('cards', 'totalProduksi'));
    }

    /**
     * Halaman Scanner Khusus Card di Produksi
     */
    public function scan($cardKey)
    {
        $cards = self::getCards();

        if (!array_key_exists($cardKey, $cards)) {
            return redirect()->route('kempu.produksi.index')->with('error', 'Pilihan proses Produksi tidak valid.');
        }

        $card = $cards[$cardKey];
        $canManualInput = MasterKempuModel::canManualInput();

        return view('kempu.produksi.scan', compact('card', 'cards', 'canManualInput'));
    }

    /**
     * Memeriksa riwayat waktu Cuci Kempu dan menghitung selisih hari (H+X)
     * Aturan SOP:
     * - Wajib H+1 setelah Cuci Kempu baru bisa di-Filling (H+0 belum bisa)
     * - Maksimal H+3 setelah Cuci Kempu. Jika > H+3 maka kadaluarsa dan WAJIB dicuci ulang.
     */
    public static function getCuciKempuTiming($kempu): array
    {
        // 1. Cari riwayat Cuci Kempu terbaru dari tracking history
        $cuciLog = KempuTrackingHistoryModel::where(function ($q) use ($kempu) {
            $q->where('kempu_master_id', $kempu->id)
                ->orWhere('id_kempu', $kempu->id_kempu);
        })
            ->where(function ($q) {
                $q->where('action', 'Cuci Kempu')
                    ->orWhere('action', 'LIKE', '%Cuci Kempu%')
                    ->orWhere('action', 'LIKE', '%cuci%');
            })
            ->latest('id')
            ->first();

        $cuciAt = null;
        if ($cuciLog && $cuciLog->created_at) {
            $cuciAt = Carbon::parse($cuciLog->created_at);
        } elseif ($kempu->main?->last_action && stripos($kempu->main->last_action, 'cuci') !== false && $kempu->main->last_scanned_at) {
            $cuciAt = Carbon::parse($kempu->main->last_scanned_at);
        } elseif ($kempu->main?->last_scanned_at && in_array($kempu->main->current_status, [
            MasterKempuModel::STATUS_CUCI_KEMPU_COMPLETED,
            MasterKempuModel::STATUS_PROD_CUCI_KEMPU,
            'PROD_CUCI_KEMPU',
            'CUCI_KEMPU_COMPLETED',
            'Cuci Kempu Selesai',
        ])) {
            $cuciAt = Carbon::parse($kempu->main->last_scanned_at);
        } elseif ($kempu->main?->updated_at && in_array($kempu->main->current_status, [
            MasterKempuModel::STATUS_CUCI_KEMPU_COMPLETED,
            MasterKempuModel::STATUS_PROD_CUCI_KEMPU,
            'PROD_CUCI_KEMPU',
            'CUCI_KEMPU_COMPLETED',
            'Cuci Kempu Selesai',
        ])) {
            $cuciAt = Carbon::parse($kempu->main->updated_at);
        }

        if (!$cuciAt) {
            return [
                'has_cuci'       => false,
                'cuci_at'        => null,
                'cuci_date'      => '-',
                'diff_days'      => null,
                'h_label'        => '-',
                'earliest_date'  => '-',
                'max_date'       => '-',
                'status'         => 'NO_CUCI',
                'is_valid_time'  => false,
                'message'        => "Data riwayat Cuci Kempu untuk '{$kempu->id_kempu}' tidak ditemukan. Kempu wajib melalui proses Cuci Kempu terlebih dahulu.",
            ];
        }

        $cuciDate     = $cuciAt->copy()->startOfDay();
        $today        = Carbon::now()->startOfDay();
        $diffDays     = (int) $cuciDate->diffInDays($today, false);
        $earliestDate = $cuciDate->copy()->addDay()->format('d/m/Y');
        $maxDate      = $cuciDate->copy()->addDays(3)->format('d/m/Y');
        $cuciDateStr  = $cuciAt->format('d/m/Y H:i');

        if ($diffDays < 1) {
            // H+0 (Hari yang sama dengan pencucian)
            return [
                'has_cuci'       => true,
                'cuci_at'        => $cuciAt->toDateTimeString(),
                'cuci_date'      => $cuciDateStr,
                'diff_days'      => $diffDays,
                'h_label'        => 'H+0 (Hari ini)',
                'earliest_date'  => $earliestDate,
                'max_date'       => $maxDate,
                'status'         => 'TOO_EARLY',
                'is_valid_time'  => false,
                'message'        => "Belum Memenuhi Syarat Waktu (Wajib H+1): Kempu {$kempu->id_kempu} baru dicuci pada {$cuciDateStr} (Hari ini). Pengisian (Filling) baru dapat dilakukan minimal H+1 setelah pencucian (Mulai besok: {$earliestDate}) agar kempu benar-benar kering dan higienis.",
            ];
        } elseif ($diffDays > 3) {
            // > H+3 (Sudah lebih dari 3 hari, kadaluarsa dan wajib cuci ulang)
            return [
                'has_cuci'       => true,
                'cuci_at'        => $cuciAt->toDateTimeString(),
                'cuci_date'      => $cuciDateStr,
                'diff_days'      => $diffDays,
                'h_label'        => "H+{$diffDays} (Kadaluarsa > H+3)",
                'earliest_date'  => $earliestDate,
                'max_date'       => $maxDate,
                'status'         => 'EXPIRED',
                'is_valid_time'  => false,
                'message'        => "Batas Waktu Terlewati (> H+3): Kempu {$kempu->id_kempu} dicuci pada {$cuciDateStr} ({$diffDays} hari yang lalu, batas maksimal H+3 adalah tanggal {$maxDate}). Kempu WAJIB dicuci ulang di menu 'Cuci Kempu' sebelum dapat dilakukan pengisian (Filling).",
            ];
        } else {
            // H+1, H+2, H+3 (Memenuhi syarat)
            return [
                'has_cuci'       => true,
                'cuci_at'        => $cuciAt->toDateTimeString(),
                'cuci_date'      => $cuciDateStr,
                'diff_days'      => $diffDays,
                'h_label'        => "H+{$diffDays}",
                'earliest_date'  => $earliestDate,
                'max_date'       => $maxDate,
                'status'         => 'VALID',
                'is_valid_time'  => true,
                'message'        => null,
            ];
        }
    }

    /**
     * Validasi alur kempu untuk card tertentu di Produksi
     */
    public static function validateProduksiFlow($kempu, string $cardKey): array
    {
        $currentStatus   = trim($kempu->main?->current_status ?? $kempu->current_status ?? '');
        $currentLocation = trim($kempu->main?->current_location ?? $kempu->current_location ?? '');
        $idKempu         = $kempu->id_kempu;

        // Force Scan Produksi: Membebaskan urutan normal, KECUALI jika kempu sedang berstatus REJECT / REPAIR di Workshop Engineering
        if ($cardKey === 'prod-force') {
            $isReject = (
                strcasecmp($currentStatus, MasterKempuModel::STATUS_ENG_REPAIR) === 0 ||
                strcasecmp($currentLocation, MasterKempuModel::LOC_ENG) === 0 ||
                str_contains(strtoupper($currentStatus), 'REJECT') ||
                str_contains(strtoupper($currentStatus), 'REPAIR') ||
                in_array(strtolower($kempu->status ?? ''), ['maintenance', 'damaged', 'reject'])
            );

            if ($isReject) {
                return [
                    'valid'   => false,
                    'message' => "Alur Wajib: Kempu {$idKempu} saat ini sedang berstatus REJECT / REPAIR di Workshop Engineering ('{$currentStatus}' - Lokasi: {$currentLocation}). Kempu reject wajib diperbaiki oleh Engineering dan melalui verifikasi ulang di QC PM serta WPM terlebih dahulu sebelum dapat diproses kembali di Produksi. Status reject tidak boleh di-force scan.",
                ];
            }

            return ['valid' => true, 'message' => null];
        }

        if ($cardKey !== 'create-ba-scrap') {
            if (strcasecmp($currentStatus, MasterKempuModel::STATUS_SCRAPPED) === 0 || strcasecmp($kempu->status ?? '', 'nonaktif') === 0) {
                return [
                    'valid'   => false,
                    'message' => "Kempu {$idKempu} sudah berstatus SCRAP / Nonaktif dan tidak dapat diproses lagi.",
                ];
            }
        } else {
            // Khusus Create BA Scrap:
            // Jika kempu sudah berstatus nonaktif (BA Scrap sudah pernah dibuat dan dinonaktifkan):
            if (strcasecmp($kempu->status ?? '', 'nonaktif') === 0) {
                return [
                    'valid'   => false,
                    'message' => "Kempu {$idKempu} sudah berstatus NONAKTIF (BA Scrap sudah pernah dibuat sebelumnya).",
                ];
            }
        }

        $cards = self::getCards();
        if (!isset($cards[$cardKey])) {
            return ['valid' => false, 'message' => 'Tipe proses Produksi tidak valid.'];
        }

        $card = $cards[$cardKey];
        $allowedStatuses = $card['target_statuses'];

        $isMatch = false;
        foreach ($allowedStatuses as $st) {
            if (strcasecmp($currentStatus, $st) === 0) {
                $isMatch = true;
                break;
            }
        }

        // Khusus Create BA Scrap: izinkan juga jika master kempu berstatus scrap/damaged
        if ($cardKey === 'create-ba-scrap') {
            if (in_array(strtolower($kempu->status ?? ''), ['scrap', 'damaged'])) {
                $isMatch = true;
            }
        }

        if (!$isMatch) {
            if ($cardKey === 'transfer-in-from-wpm') {
                $qcPmPassedStatuses = [
                    MasterKempuModel::STATUS_QC_PM_RELEASE,
                    MasterKempuModel::STATUS_QC_PM_PASSED,
                    'QC_PM_RELEASE',
                    'QC_PM_PASSED',
                    'QC PM Release',
                    'QC PM Passed',
                    'QC PM Lolos (OK)',
                ];
                foreach ($qcPmPassedStatuses as $st) {
                    if (strcasecmp($currentStatus, $st) === 0) {
                        return [
                            'valid'   => false,
                            'message' => "Alur Tidak Sesuai: Kempu {$idKempu} baru selesai QC PM (Release) dan belum dikirim oleh WPM. Kempu wajib melalui proses 'Transfer Out to Produksi' di Warehouse WPM terlebih dahulu sebelum dapat di-Transfer In di Produksi.",
                        ];
                    }
                }

                $qcPendingStatuses = [
                    MasterKempuModel::STATUS_QC_PM_PENDING,
                    'QC_PM_PENDING',
                    'QC PM Pending',
                    MasterKempuModel::STATUS_GR_COMPLETED,
                    'GR_COMPLETED',
                    MasterKempuModel::STATUS_REGISTERED,
                    'REGISTERED',
                    MasterKempuModel::STATUS_WPM_TRANSFER_IN_PAS,
                    'WPM_TRANSFER_IN_PAS',
                    'Transfer In From PAS',
                ];
                foreach ($qcPendingStatuses as $st) {
                    if (strcasecmp($currentStatus, $st) === 0) {
                        return [
                            'valid'   => false,
                            'message' => "Alur Tidak Sesuai: Kempu {$idKempu} masih berada pada tahap awal WPM/QC PM (status: '{$currentStatus}'). Kempu harus dinyatakan Lolos oleh QC PM dan melalui proses 'Transfer Out to Produksi' dari WPM terlebih dahulu.",
                        ];
                    }
                }

                $alreadyInProdStatuses = [
                    MasterKempuModel::STATUS_PROD_TRANSFER_IN_WPM,
                    'PROD_TRANSFER_IN_WPM',
                    'PROD_RECEIVED',
                    'Transfer in from WPM',
                    'Transfer In from WPM',
                    MasterKempuModel::STATUS_QC_PRE_CUCI_PENDING,
                    MasterKempuModel::STATUS_QC_PRE_CUCI_RELEASE,
                    'QC_PRE_CUCI_REJECT',
                    MasterKempuModel::STATUS_PROD_CUCI_KEMPU,
                    MasterKempuModel::STATUS_PROD_FILLING_KEMPU,
                    MasterKempuModel::STATUS_SCAN1_FILLED,
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_PENDING,
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_RELEASE,
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_HOLD,
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_REJECT,
                    MasterKempuModel::STATUS_PROD_TRANSFER_OUT_WFG,
                ];
                foreach ($alreadyInProdStatuses as $st) {
                    if (strcasecmp($currentStatus, $st) === 0) {
                        return [
                            'valid'   => false,
                            'message' => "Alur Tidak Sesuai: Kempu {$idKempu} sudah berada di area Produksi (status saat ini: '{$currentStatus}'). Kempu tidak perlu di-Transfer In ulang dari WPM.",
                        ];
                    }
                }

                return [
                    'valid'   => false,
                    'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Untuk menjalankan 'Transfer in from WPM', kempu harus berstatus 'WPM_TRANSFER_OUT_PROD' (sudah melalui Transfer Out to Produksi dari WPM).",
                ];
            } elseif ($cardKey === 'transfer-out-to-wfg') {
                $fillingStatuses = [
                    MasterKempuModel::STATUS_SCAN1_FILLED,
                    'SCAN1_FILLED',
                    'Scan 1: Filling / Pengisian Kempu',
                    'Scan 1 Filling Kempu',
                ];
                foreach ($fillingStatuses as $st) {
                    if (strcasecmp($currentStatus, $st) === 0) {
                        return [
                            'valid'   => false,
                            'message' => "Alur Tidak Sesuai: Kempu {$idKempu} baru selesai pengisian (Filling) dan belum diperiksa oleh QC Proses. Kempu wajib menunggu hasil inspeksi dan dinyatakan Lolos (OK) oleh QC After Filling terlebih dahulu sebelum dapat di-Transfer Out ke WFG.",
                        ];
                    }
                }

                $holdStatuses = [
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_HOLD,
                    'QC_AFTER_FILLING_HOLD',
                    'QC After Filling Hold',
                    'QC After Filling Tahan (Hold)',
                ];
                foreach ($holdStatuses as $st) {
                    if (strcasecmp($currentStatus, $st) === 0) {
                        return [
                            'valid'   => false,
                            'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus TAHAN (Hold) oleh QC After Filling. Kempu tidak dapat dikirim ke WFG sampai statusnya dinyatakan Lolos (OK).",
                        ];
                    }
                }

                $rejectStatuses = [
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_REJECT,
                    'QC_AFTER_FILLING_REJECT',
                    'QC After Filling Reject',
                    'QC After Filling Tidak OK (Reject)',
                ];
                foreach ($rejectStatuses as $st) {
                    if (strcasecmp($currentStatus, $st) === 0) {
                        return [
                            'valid'   => false,
                            'message' => "Alur Tidak Sesuai: Kempu {$idKempu} berstatus REJECT (Tidak OK) oleh QC After Filling dan tidak dapat dikirim ke WFG.",
                        ];
                    }
                }

                return [
                    'valid'   => false,
                    'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Transfer Out ke WFG hanya dapat dilakukan setelah kempu dinyatakan Lolos (OK) oleh QC After Filling.",
                ];
            } elseif ($cardKey === 'cuci-kempu') {
                return [
                    'valid'   => false,
                    'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Cuci Kempu hanya dapat diproses setelah kempu dinyatakan Lolos (OK) oleh QC Pre Cuci.",
                ];
            } elseif ($cardKey === 'scan-1-filling-kempu') {
                $preCuciPassedStatuses = [
                    MasterKempuModel::STATUS_QC_PRE_CUCI_RELEASE,
                    MasterKempuModel::STATUS_QC_PRE_CUCI_PASSED,
                    'QC_PRE_CUCI_RELEASE',
                    'QC_PRE_CUCI_PASSED',
                    'QC Pre Cuci Release',
                    'QC Pre Cuci Passed',
                    'QC Pre Cuci Lolos (OK)',
                ];
                foreach ($preCuciPassedStatuses as $st) {
                    if (strcasecmp($currentStatus, $st) === 0) {
                        return [
                            'valid'   => false,
                            'message' => "Alur Tidak Sesuai: Kempu {$idKempu} baru lolos QC Pre Cuci dan belum melalui proses Cuci Kempu. Kempu wajib dicuci terlebih dahulu di menu 'Cuci Kempu' sebelum dapat dilakukan pengisian (Filling).",
                        ];
                    }
                }

                return [
                    'valid'   => false,
                    'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Pengisian (Filling) hanya dapat diproses setelah kempu selesai melalui tahap Cuci Kempu.",
                ];
            } elseif ($cardKey === 'repro-kempu') {
                return [
                    'valid'   => false,
                    'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Menu 'Repro Kempu' hanya untuk kempu yang dinyatakan Repro (Produk Reject) pada pemeriksaan QC After Filling.",
                ];
            }

            return [
                'valid'   => false,
                'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Untuk menjalankan '{$card['title']}', kempu harus memiliki status yang sesuai.",
            ];
        }

        // Pengecekan Khusus untuk Cuci Kempu:
        // Jika kempu sudah berstatus Cuci Kempu, HANYA boleh dicuci ulang jika sudah lewat batas waktu (> H+3 / Kadaluarsa).
        // Jika masih dalam rentang masa berlaku higienis (H+0 s/d H+3), maka ditolak karena DUPLIKAT.
        if ($cardKey === 'cuci-kempu') {
            $cuciStatuses = [
                MasterKempuModel::STATUS_PROD_CUCI_KEMPU,
                MasterKempuModel::STATUS_CUCI_KEMPU_COMPLETED,
                'PROD_CUCI_KEMPU',
                'CUCI_KEMPU_COMPLETED',
                'Cuci Kempu Selesai',
            ];

            $isAlreadyCuci = false;
            foreach ($cuciStatuses as $cs) {
                if (strcasecmp($currentStatus, $cs) === 0) {
                    $isAlreadyCuci = true;
                    break;
                }
            }

            if ($isAlreadyCuci) {
                $timing = self::getCuciKempuTiming($kempu);
                // Jika masih dalam masa berlaku (H+0 s/d H+3), tolak karena duplikat
                if ($timing['has_cuci'] && $timing['diff_days'] !== null && $timing['diff_days'] <= 3) {
                    $hLabel      = $timing['h_label'] ?? "H+{$timing['diff_days']}";
                    $cuciDateStr = $timing['cuci_date'] ?? '-';
                    $maxDate     = $timing['max_date'] ?? '-';

                    return [
                        'valid'   => false,
                        'message' => "Duplikat Scan (Sudah Dicuci): Kempu {$idKempu} sudah selesai dicuci pada {$cuciDateStr} ({$hLabel}). Masa higienis kempu masih berlaku hingga tanggal {$maxDate} (maksimal H+3). Kempu tidak perlu dicuci ulang, silakan lanjutkan ke proses 'Filling Kempu'.",
                    ];
                }
                // Jika $timing['diff_days'] > 3 (sudah kadaluarsa > H+3), lolos validasi untuk cuci ulang
            }
        }

        // Pengecekan Waktu Khusus untuk Scan Filling Kempu (Wajib H+1 s/d H+3 dari Cuci Kempu)
        if ($cardKey === 'scan-1-filling-kempu') {
            $timing = self::getCuciKempuTiming($kempu);
            if (!$timing['is_valid_time']) {
                return [
                    'valid'   => false,
                    'message' => $timing['message'],
                ];
            }
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Lookup Barcode / RFID saat di-scan di Produksi
     */
    public function lookup(Request $request)
    {
        $idKempu = strtoupper(trim($request->input('id_kempu', $request->input('barcode', ''))));
        $cardKey = $request->input('card_key', $request->input('card', ''));

        if (!$idKempu || !$cardKey) {
            return response()->json([
                'status'  => false,
                'message' => 'Parameter barcode atau tipe proses tidak lengkap.',
            ], 400);
        }

        $cards = self::getCards();
        if (!isset($cards[$cardKey])) {
            return response()->json([
                'status'  => false,
                'message' => 'Tipe proses Produksi tidak valid.',
            ], 400);
        }

        $card = $cards[$cardKey];

        $kempu = MasterKempuModel::with('main')
            ->where('id_kempu', $idKempu)
            ->orWhere('rfid', $idKempu)
            ->first();

        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu '{$idKempu}' tidak ditemukan dalam database Master Kempu. Harap daftarkan kempu terlebih dahulu di WPM.",
            ], 404);
        }

        if (!$kempu->main) {
            $kempu->main()->create([
                'id_kempu'         => $kempu->id_kempu,
                'current_location' => MasterKempuModel::LOC_WPM,
                'current_status'   => $kempu->status ?? MasterKempuModel::STATUS_REGISTERED,
                'reused_count'     => 0,
                'max_reused'       => 21,
                'condition'        => 'OK',
            ]);
            $kempu->load('main');
        }

        $flowValidation = self::validateProduksiFlow($kempu, $cardKey);

        $isManual = $request->boolean('is_manual') || $request->input('input_type') === 'manual';
        if ($isManual) {
            $callerRole = strtolower(trim($request->input('operator_role', $request->input('user_role', ''))));
            $isAuthorized = MasterKempuModel::canManualInput();
            if ($callerRole && $callerRole === 'operator' && !auth()->user()) {
                $isAuthorized = false;
            } elseif ($callerRole && $callerRole !== 'operator') {
                $isAuthorized = true;
            }

            if (!$isAuthorized) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Akses ditolak: Operator tidak memiliki izin untuk mengetik ID kempu secara manual. Wajib menggunakan pemindai kamera/barcode.',
                ], 403);
            }

            $opName = $request->input('operator_name', $request->input('user_name', Auth::user()?->nama_lengkap ?? Auth::user()?->username ?? 'User'));
            $opRole = $callerRole ?: (Auth::user()?->role ?? 'Staff');
            $portalUserId = $request->input('operator_id', $request->input('user_id', Auth::id()));

            $kempu->recordTracking(
                stage: ($cardKey === 'prod-force' ? 'PRODUKSI_FORCE' : 'PRODUKSI'),
                action: 'Input Manual ID (' . ($card['title'] ?? 'Lookup') . ')',
                actionResult: 'MANUAL_SCAN',
                fromLocation: $kempu->main?->current_location ?? 'PRODUKSI',
                toLocation: $kempu->main?->current_location ?? 'PRODUKSI',
                condition: $kempu->main?->condition ?? 'OK',
                notes: "ID Kempu diketik manual oleh {$opName} ({$opRole})",
                userId: $portalUserId,
                metadata: [
                    'input_method'  => 'MANUAL',
                    'is_manual'     => true,
                    'card_key'      => $cardKey,
                    'action_title'  => $card['title'],
                    'operator_name' => $opName,
                    'operator_role' => $opRole,
                    'app_source'    => $request->input('app_source', 'warehouse'),
                ]
            );
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'id'               => $kempu->id,
                'id_kempu'         => $kempu->id_kempu,
                'rfid'             => $kempu->rfid ?? '-',
                'current_location' => $kempu->main->current_location ?? 'PRODUKSI',
                'current_status'   => $kempu->main->current_status ?? '-',
                'reused_count'     => (int)($kempu->main->reused_count ?? 0),
                'max_reused'       => (int)($kempu->main->max_reused ?? 21),
                'condition'        => $kempu->main->condition ?? 'OK',
                'card_title'       => $card['title'],
                'is_force_scan'    => ($cardKey === 'prod-force'),
                'is_flow_valid'    => $flowValidation['valid'],
                'flow_error'       => $flowValidation['message'],
                'cuci_info'        => in_array($cardKey, ['scan-1-filling-kempu', 'cuci-kempu']) ? self::getCuciKempuTiming($kempu) : null,
            ],
        ]);
    }

    /**
     * Konfirmasi Eksekusi Aksi Produksi
     */
    public function confirm(Request $request)
    {
        $idKempu = strtoupper(trim($request->input('id_kempu', $request->input('barcode', ''))));
        $cardKey = $request->input('card_key', $request->input('card', ''));
        $notes   = trim($request->input('notes', ''));
        $isManual = $request->boolean('is_manual');

        if ($isManual) {
            $callerRole = strtolower(trim($request->input('operator_role', $request->input('user_role', ''))));
            $isAuthorized = MasterKempuModel::canManualInput();
            if ($callerRole && $callerRole === 'operator' && !auth()->user()) {
                $isAuthorized = false;
            } elseif ($callerRole && $callerRole !== 'operator') {
                $isAuthorized = true;
            }

            if (!$isAuthorized) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Akses ditolak: Operator tidak memiliki izin untuk mengonfirmasi transaksi dari pengetikan ID manual.',
                ], 403);
            }

            if (!str_contains($notes, '[Input Manual]')) {
                $notes = $notes ? $notes . ' [Input Manual]' : '[Input Manual]';
            }
        }

        if (!$idKempu || !$cardKey) {
            return response()->json([
                'status'  => false,
                'message' => 'Parameter ID kempu atau proses tidak lengkap.',
            ], 400);
        }

        $cards = self::getCards();
        if (!isset($cards[$cardKey])) {
            return response()->json([
                'status'  => false,
                'message' => 'Tipe proses Produksi tidak valid.',
            ], 400);
        }

        $card = $cards[$cardKey];

        $kempu = MasterKempuModel::with('main')->where('id_kempu', $idKempu)->first();

        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu '{$idKempu}' tidak ditemukan.",
            ], 404);
        }

        // Validasi alur
        $flowValidation = self::validateProduksiFlow($kempu, $cardKey);
        if (!$flowValidation['valid']) {
            return response()->json([
                'status'  => false,
                'message' => $flowValidation['message'],
            ], 422);
        }

        $currentReused = (int)($kempu->main?->reused_count ?? 0);
        $fromLocation  = $kempu->main?->current_location ?? $card['from_loc'];
        $toLocation    = $card['to_loc'];
        $nextStatus    = $card['next_status'];
        $condition     = $kempu->main?->condition ?? 'OK';
        $actionTitle   = $card['title'];
        $actionResult  = 'OK';
        $resultMessage = "Aksi '{$card['title']}' untuk kempu {$idKempu} berhasil diproses.";

        // Logika khusus per Card
        if ($cardKey === 'prod-force') {
            if (!$notes) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Catatan/alasan wajib diisi untuk eksekusi Force Scan Produksi.',
                ], 422);
            }

            $forceTarget = strtoupper(trim($request->input('force_target', $request->input('target_status', ''))));

            switch ($forceTarget) {
                case 'PROD_TRANSFER_IN_WPM':
                case 'TRANSFER-IN-FROM-WPM':
                    $nextStatus    = MasterKempuModel::STATUS_QC_PRE_CUCI_PENDING;
                    $toLocation    = MasterKempuModel::LOC_PRODUKSI;
                    $actionResult  = 'RECEIVED';
                    $actionTitle   = '[FORCE SCAN] Transfer in from WPM';
                    $resultMessage = "Force Scan: Kempu {$idKempu} berhasil dipaksa Transfer In dari WPM (Status: Menuju QC Pre Cuci).";
                    $notes         = $notes ?: 'Force Decision: Paksa Transfer In dari WPM';
                    break;

                case 'PROD_CUCI_KEMPU':
                case 'CUCI-KEMPU':
                    $nextStatus    = MasterKempuModel::STATUS_PROD_CUCI_KEMPU;
                    $toLocation    = MasterKempuModel::LOC_PRODUKSI;
                    $condition     = 'OK';
                    $actionResult  = 'OK';
                    $actionTitle   = '[FORCE SCAN] Cuci Kempu Selesai';
                    $resultMessage = "Force Scan: Pencucian kempu {$idKempu} berhasil dipaksa selesai (Siap Filling).";
                    $notes         = $notes ?: 'Force Decision: Paksa Selesai Cuci Kempu';
                    break;

                case 'PROD_FILLING_KEMPU':
                case 'SCAN-1-FILLING-KEMPU':
                    $nextStatus    = MasterKempuModel::STATUS_PROD_FILLING_KEMPU;
                    $toLocation    = MasterKempuModel::LOC_PRODUKSI;
                    $condition     = 'OK';
                    $actionResult  = 'OK';
                    $actionTitle   = '[FORCE SCAN] Filling Kempu (Scan 1)';
                    $resultMessage = "Force Scan: Pengisian (Filling) kempu {$idKempu} berhasil dipaksa selesai (Bypass Waktu Cuci, Siap QC After Filling).";
                    $notes         = $notes ?: 'Force Decision: Paksa Filling Kempu (Bypass Aturan Waktu Cuci)';
                    break;

                case 'PROD_TRANSFER_OUT_WFG':
                case 'TRANSFER-OUT-TO-WFG':
                    $nextStatus    = MasterKempuModel::STATUS_PROD_TRANSFER_OUT_WFG;
                    $toLocation    = MasterKempuModel::LOC_WFG;
                    $actionResult  = 'TRANSFERRED';
                    $actionTitle   = '[FORCE SCAN] Transfer Out to WFG';
                    $resultMessage = "Force Scan: Kempu {$idKempu} berhasil dipaksa Transfer Out ke Gudang Jadi (WFG).";
                    $notes         = $notes ?: 'Force Decision: Paksa Transfer Out ke WFG';
                    break;

                case 'PROD_TRANSFER_IN_WFG':
                case 'TRANSFER-IN-FROM-WFG':
                    $nextStatus    = MasterKempuModel::STATUS_PROD_TRANSFER_IN_WFG;
                    $toLocation    = MasterKempuModel::LOC_PRODUKSI;
                    $actionResult  = 'RECEIVED';
                    $actionTitle   = '[FORCE SCAN] Transfer in from WFG (Retur)';
                    $resultMessage = "Force Scan: Kempu retur/reject dari WFG {$idKempu} berhasil dipaksa diterima di Produksi.";
                    $notes         = $notes ?: 'Force Decision: Paksa Terima Retur WFG ke Produksi';
                    break;

                case 'SCRAPPED':
                case 'CREATE-BA-SCRAP':
                    $nextStatus    = MasterKempuModel::STATUS_SCRAPPED;
                    $toLocation    = MasterKempuModel::LOC_SCRAP;
                    $condition     = 'NOT_OK';
                    $actionResult  = 'BA_SCRAP';
                    $actionTitle   = '[FORCE SCAN] Create BA Scrap';
                    $resultMessage = "Force Scan: Berita Acara Scrap kempu {$idKempu} berhasil dibuat. Status kempu resmi menjadi SCRAP.";
                    $notes         = $notes ?: 'Force Decision: Paksa Pembuatan BA Scrap';
                    break;

                case 'PROD_REPRO_KEMPU':
                case 'REPRO-KEMPU':
                case 'REPRO':
                    $nextStatus    = MasterKempuModel::STATUS_ENG_REPAIR;
                    $toLocation    = MasterKempuModel::LOC_ENG;
                    $condition     = 'NOT_OK';
                    $actionResult  = 'REPRO_COMPLETED';
                    $actionTitle   = '[FORCE SCAN] Repro Kempu (Kirim Repair)';
                    $resultMessage = "Force Scan: Repro Kempu {$idKempu} berhasil dipaksa selesai dan diteruskan ke Workshop Engineering (Repair).";
                    $notes         = $notes ?: 'Force Decision: Paksa Repro Kempu ke Repair';
                    break;

                default:
                    return response()->json([
                        'status'  => false,
                        'message' => 'Pilihan target keputusan Force Scan Produksi tidak valid.',
                    ], 400);
            }
        } elseif ($cardKey === 'scan-1-filling-kempu') {
            // Reused telah ditambah saat QC Pre Cuci. Di sini mengisi muatan kempu dan lanjut ke QC After Filling
            $nextStatus    = MasterKempuModel::STATUS_PROD_FILLING_KEMPU;
            $toLocation    = MasterKempuModel::LOC_PRODUKSI;
            $condition     = 'OK';
            $actionResult  = 'OK';
            $resultMessage = "Scan 1 Filling Kempu berhasil. Kempu siap untuk pemeriksaan QC After Filling.";
        } elseif ($cardKey === 'cuci-kempu') {
            $nextStatus    = MasterKempuModel::STATUS_PROD_CUCI_KEMPU;
            $toLocation    = MasterKempuModel::LOC_PRODUKSI;
            $condition     = 'OK';
            $actionResult  = 'OK';
            $resultMessage = "Cuci Kempu berhasil diselesaikan. Kempu siap untuk proses Scan 1 Filling.";
        } elseif ($cardKey === 'create-ba-scrap') {
            $nextStatus    = MasterKempuModel::STATUS_SCRAPPED;
            $toLocation    = MasterKempuModel::LOC_SCRAP;
            $condition     = 'NOT_OK';
            $actionResult  = 'BA_SCRAP';
            $resultMessage = "Berita Acara (BA) Scrap berhasil dibuat di Produksi. Status kempu resmi menjadi NONAKTIF (Scrap).";
        } elseif ($cardKey === 'transfer-in-from-wpm') {
            $nextStatus    = MasterKempuModel::STATUS_QC_PRE_CUCI_PENDING;
            $toLocation    = MasterKempuModel::LOC_PRODUKSI;
            $actionResult  = 'RECEIVED';
            $resultMessage = "Kempu berhasil diterima di Produksi (Transfer In). Menunggu pengecekan QC Pre Cuci.";
        } elseif ($cardKey === 'transfer-in-from-wfg') {
            $nextStatus    = MasterKempuModel::STATUS_PROD_TRANSFER_IN_WFG;
            $toLocation    = MasterKempuModel::LOC_PRODUKSI;
            $actionResult  = 'RECEIVED';
            $resultMessage = "Kempu retur/reject dari WFG berhasil diterima di Produksi (Transfer In). Menunggu evaluasi QC.";
        } elseif ($cardKey === 'transfer-out-to-wfg') {
            $nextStatus    = MasterKempuModel::STATUS_PROD_TRANSFER_OUT_WFG;
            $toLocation    = MasterKempuModel::LOC_WFG;
            $actionResult  = 'TRANSFERRED';
            $resultMessage = "Kempu berhasil di-Transfer Out dari Produksi menuju WFG.";
        } elseif ($cardKey === 'repro-kempu') {
            $nextStatus    = MasterKempuModel::STATUS_ENG_REPAIR;
            $toLocation    = MasterKempuModel::LOC_ENG;
            $condition     = 'NOT_OK';
            $actionResult  = 'REPRO_COMPLETED';
            $actionTitle   = 'Repro Kempu (Kirim ke Repair)';
            $resultMessage = "Proses Repro Kempu {$idKempu} selesai (produk dikosongkan). Kempu berhasil diteruskan ke Workshop Engineering (Repair).";
        }

        DB::beginTransaction();
        try {
            if (!$kempu->main) {
                $kempu->main()->create([
                    'id_kempu'         => $kempu->id_kempu,
                    'current_location' => $toLocation,
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
                    'current_location' => $toLocation,
                    'reused_count'     => $currentReused,
                    'condition'        => $condition,
                    'last_scanned_at'  => now(),
                    'last_action'      => $actionTitle,
                ]);
            }

            // Identitas Operator dari berbagai portal (Production, Warehouse, dll)
            $operatorName  = trim($request->input('operator_name', $request->input('user_name', '')));
            $operatorEmail = trim($request->input('operator_email', $request->input('user_email', '')));
            $operatorRole  = trim($request->input('operator_role', $request->input('user_role', '')));
            $operatorNik   = trim($request->input('operator_nik', $request->input('user_nik', '')));
            $appSource     = $request->input('app_source', Auth::check() ? 'warehouse' : 'production');

            // Cek apakah user juga terdaftar di tabel users Warehouse berdasarkan EMAIL (BUKAN ID integer karena tabel user berbeda!)
            $warehouseUser = $operatorEmail ? User::where('email', $operatorEmail)->first() : null;

            if (empty($operatorName)) {
                $operatorName = $warehouseUser?->nama_lengkap
                    ?? $warehouseUser?->username
                    ?? (Auth::user()?->nama_lengkap ?? Auth::user()?->username ?? Auth::user()?->name ?? 'Operator Produksi');
            }

            // created_by HANYA diisi jika user terverifikasi ada di tabel users Warehouse (via Auth::check() atau email match).
            // JANGAN gunakan request->user_id langsung dari portal luar untuk menghindari salah relasi ke user Warehouse lain!
            $creatorId = $warehouseUser?->id ?? (Auth::check() ? Auth::id() : null);

            // Update data master kempu (kempu_master)
            // Keputusan resmi SCRAP dan penonaktifan kempu berada di tangan Produksi saat scan Create BA Scrap
            $masterUpdate = [
                'updated_by' => $creatorId,
            ];
            if ($cardKey === 'create-ba-scrap' || $nextStatus === MasterKempuModel::STATUS_SCRAPPED) {
                $masterUpdate['status']     = 'nonaktif';
                $masterUpdate['keterangan'] = $notes ?: 'Berita Acara Scrap (Nonaktif) oleh Produksi';
            }
            $kempu->update($masterUpdate);

            $trackingMetadata = [
                'app_source'     => $appSource,
                'operator_name'  => $operatorName,
                'operator_email' => $operatorEmail ?: null,
                'operator_role'  => $operatorRole ?: null,
                'operator_nik'   => $operatorNik ?: null,
                'portal_user_id' => $request->input('portal_user_id', $request->input('operator_id', $request->input('user_id'))),
                'is_force_scan'  => ($cardKey === 'prod-force'),
                'force_target'   => $forceTarget ?? null,
                'input_method'   => $isManual ? 'MANUAL' : 'SCANNER',
                'is_manual'      => $isManual,
            ];

            KempuTrackingHistoryModel::create([
                'kempu_master_id' => $kempu->id,
                'id_kempu'        => $kempu->id_kempu,
                'stage'           => ($cardKey === 'prod-force' ? 'PRODUKSI_FORCE' : 'PRODUKSI'),
                'action'          => $actionTitle,
                'action_result'   => $actionResult,
                'from_location'   => $fromLocation,
                'to_location'     => $toLocation,
                'reused_count'    => $currentReused,
                'condition'       => $condition,
                'notes'           => $notes ?: null,
                'metadata'        => $trackingMetadata,
                'created_by'      => $creatorId,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => $resultMessage,
                'data'    => [
                    'id_kempu'      => $kempu->id_kempu,
                    'new_status'    => $nextStatus,
                    'new_location'  => $toLocation,
                    'reused_count'  => $currentReused,
                    'new_condition' => $condition,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memproses konfirmasi Produksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Halaman Web Report Scan Kempu Produksi
     */
    public function report(Request $request)
    {
        $cards = self::getCards();
        return view('kempu.produksi.report', compact('cards'));
    }

    /**
     * API: Statistik KPI untuk Report Produksi Kempu
     */
    public function reportStatsApi(Request $request)
    {
        $today = today();
        $stages = ['PRODUKSI', 'PRODUKSI_FORCE'];
        $baseHistory = KempuTrackingHistoryModel::whereIn('stage', $stages);

        $totalToday = (clone $baseHistory)->whereDate('created_at', $today)->count();
        $totalAll   = (clone $baseHistory)->count();

        // 1. Transfer In WPM
        $transferInWpmQuery = KempuTrackingHistoryModel::whereIn('stage', $stages)
            ->where('action', 'like', '%Transfer in from WPM%');
        $totalTransferInWpmToday = (clone $transferInWpmQuery)->whereDate('created_at', $today)->count();
        $totalTransferInWpmAll   = (clone $transferInWpmQuery)->count();

        // 2. Cuci Kempu
        $cuciQuery = KempuTrackingHistoryModel::whereIn('stage', $stages)
            ->where('action', 'like', '%Cuci Kempu%');
        $totalCuciToday = (clone $cuciQuery)->whereDate('created_at', $today)->count();
        $totalCuciAll   = (clone $cuciQuery)->count();

        // 3. Filling Kempu
        $fillingQuery = KempuTrackingHistoryModel::whereIn('stage', $stages)
            ->where(function ($q) {
                $q->where('action', 'like', '%Filling Kempu%')
                    ->orWhere('action', 'like', '%Scan 1 Filling%');
            });
        $totalFillingToday = (clone $fillingQuery)->whereDate('created_at', $today)->count();
        $totalFillingAll   = (clone $fillingQuery)->count();

        // 4. Transfer Out to WFG
        $transferOutWfgQuery = KempuTrackingHistoryModel::whereIn('stage', $stages)
            ->where('action', 'like', '%Transfer Out to WFG%');
        $totalTransferOutWfgToday = (clone $transferOutWfgQuery)->whereDate('created_at', $today)->count();
        $totalTransferOutWfgAll   = (clone $transferOutWfgQuery)->count();

        // 5. Transfer In from WFG (Retur)
        $transferInWfgQuery = KempuTrackingHistoryModel::whereIn('stage', $stages)
            ->where('action', 'like', '%Transfer in from WFG%');
        $totalTransferInWfgToday = (clone $transferInWfgQuery)->whereDate('created_at', $today)->count();
        $totalTransferInWfgAll   = (clone $transferInWfgQuery)->count();

        // 6. Create BA Scrap
        $scrapQuery = KempuTrackingHistoryModel::whereIn('stage', $stages)
            ->where(function ($q) {
                $q->where('action', 'like', '%BA Scrap%')
                    ->orWhere('action_result', 'BA_SCRAP');
            });
        $totalScrapToday = (clone $scrapQuery)->whereDate('created_at', $today)->count();
        $totalScrapAll   = (clone $scrapQuery)->count();

        // 7. Repro Kempu
        $reproQuery = KempuTrackingHistoryModel::whereIn('stage', $stages)
            ->where(function ($q) {
                $q->where('action', 'like', '%Repro Kempu%')
                    ->orWhere('action_result', 'REPRO_COMPLETED');
            });
        $totalReproToday = (clone $reproQuery)->whereDate('created_at', $today)->count();
        $totalReproAll   = (clone $reproQuery)->count();

        // 8. Force Scan Produksi
        $forceScanQuery = KempuTrackingHistoryModel::where(function ($q) {
            $q->where('stage', 'PRODUKSI_FORCE')
                ->orWhere('action', 'like', '%[FORCE SCAN]%')
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.is_force_scan')) = 'true'");
        });
        $totalForceToday = (clone $forceScanQuery)->whereDate('created_at', $today)->count();
        $totalForceAll   = (clone $forceScanQuery)->count();

        // 9. Total Kempu Saat Ini di Produksi
        $totalCurrentProduksi = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_PRODUKSI)
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        // 10. Warning Reused
        $totalWarningReused = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_PRODUKSI)
                ->where('reused_count', '>=', 18)
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        return response()->json([
            'status' => true,
            'data'   => [
                'total_current_produksi'        => $totalCurrentProduksi,
                'total_today'                   => $totalToday,
                'total_all'                     => $totalAll,
                'total_transfer_in_wpm_today'   => $totalTransferInWpmToday,
                'total_transfer_in_wpm_all'     => $totalTransferInWpmAll,
                'total_cuci_today'              => $totalCuciToday,
                'total_cuci_all'                => $totalCuciAll,
                'total_filling_today'           => $totalFillingToday,
                'total_filling_all'             => $totalFillingAll,
                'total_transfer_out_wfg_today'  => $totalTransferOutWfgToday,
                'total_transfer_out_wfg_all'    => $totalTransferOutWfgAll,
                'total_repro_today'             => $totalReproToday,
                'total_repro_all'               => $totalReproAll,
                'total_transfer_in_wfg_today'   => $totalTransferInWfgToday,
                'total_transfer_in_wfg_all'     => $totalTransferInWfgAll,
                'total_scrap_today'             => $totalScrapToday,
                'total_scrap_all'               => $totalScrapAll,
                'total_force_today'             => $totalForceToday,
                'total_force_all'               => $totalForceAll,
                'total_warning_reused'          => $totalWarningReused,
            ],
        ]);
    }

    /**
     * API: Data Report Produksi Kempu (Server-side Pagination & Filtering)
     */
    public function reportDataApi(Request $request)
    {
        $viewMode = $request->input('view_mode', 'history'); // 'history' | 'current'
        $perPage  = min(100, max(5, (int) $request->input('per_page', 20)));

        if ($viewMode === 'current') {
            // Data kempu yang saat ini berada di lokasi PRODUKSI
            $query = MasterKempuModel::whereHas('main', function ($q) {
                $q->where('current_location', MasterKempuModel::LOC_PRODUKSI);
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

            if ($request->filled('status_filter') && $request->status_filter !== 'all') {
                $statusFilter = $request->status_filter;
                $query->whereHas('main', function ($q) use ($statusFilter) {
                    $q->where('current_status', $statusFilter);
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

        // View Mode: History (Log Transaksi Scan Produksi)
        $query = KempuTrackingHistoryModel::whereIn('stage', ['PRODUKSI', 'PRODUKSI_FORCE'])
            ->with([
                'createdBy:id,username,nama_lengkap',
                'masterKempu:id,id_kempu,rfid,no_spb,status',
            ]);

        // Filter Proses / Action
        if ($request->filled('action_filter') && $request->action_filter !== 'all') {
            $actionFilter = $request->action_filter;
            if ($actionFilter === 'prod-force' || $actionFilter === 'force') {
                $query->where(function ($q) {
                    $q->where('stage', 'PRODUKSI_FORCE')
                        ->orWhere('action', 'like', '%[FORCE SCAN]%')
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.is_force_scan')) = 'true'");
                });
            } elseif ($actionFilter === 'transfer-in-from-wpm') {
                $query->where('action', 'like', '%Transfer in from WPM%');
            } elseif ($actionFilter === 'cuci-kempu') {
                $query->where('action', 'like', '%Cuci Kempu%');
            } elseif ($actionFilter === 'scan-1-filling-kempu') {
                $query->where(function ($q) {
                    $q->where('action', 'like', '%Filling Kempu%')
                        ->orWhere('action', 'like', '%Scan 1 Filling%');
                });
            } elseif ($actionFilter === 'transfer-out-to-wfg') {
                $query->where('action', 'like', '%Transfer Out to WFG%');
            } elseif ($actionFilter === 'repro-kempu') {
                $query->where(function ($q) {
                    $q->where('action', 'like', '%Repro Kempu%')
                        ->orWhere('action_result', 'REPRO_COMPLETED');
                });
            } elseif ($actionFilter === 'transfer-in-from-wfg') {
                $query->where('action', 'like', '%Transfer in from WFG%');
            } elseif ($actionFilter === 'create-ba-scrap') {
                $query->where(function ($q) {
                    $q->where('action', 'like', '%BA Scrap%')
                        ->orWhere('action_result', 'BA_SCRAP');
                });
            }
        }

        // Filter Hanya Force Scan
        if ($request->filled('is_force_scan')) {
            if ($request->is_force_scan === 'yes') {
                $query->where(function ($q) {
                    $q->where('stage', 'PRODUKSI_FORCE')
                        ->orWhere('action', 'like', '%[FORCE SCAN]%')
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.is_force_scan')) = 'true'");
                });
            } elseif ($request->is_force_scan === 'no') {
                $query->where('stage', '!=', 'PRODUKSI_FORCE')
                    ->where('action', 'not like', '%[FORCE SCAN]%');
            }
        }

        // Filter Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter Pencarian
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('id_kempu', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhere('action', 'like', "%{$s}%")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.operator_name')) LIKE ?", ["%{$s}%"])
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
     * API: Export CSV untuk Report Produksi Kempu
     */
    public function exportReportApi(Request $request)
    {
        $query = KempuTrackingHistoryModel::whereIn('stage', ['PRODUKSI', 'PRODUKSI_FORCE'])
            ->with([
                'createdBy:id,username,nama_lengkap',
                'masterKempu:id,id_kempu,rfid,no_spb,status',
            ]);

        if ($request->filled('action_filter') && $request->action_filter !== 'all') {
            $actionFilter = $request->action_filter;
            if ($actionFilter === 'prod-force' || $actionFilter === 'force') {
                $query->where(function ($q) {
                    $q->where('stage', 'PRODUKSI_FORCE')
                        ->orWhere('action', 'like', '%[FORCE SCAN]%');
                });
            } elseif ($actionFilter === 'transfer-in-from-wpm') {
                $query->where('action', 'like', '%Transfer in from WPM%');
            } elseif ($actionFilter === 'cuci-kempu') {
                $query->where('action', 'like', '%Cuci Kempu%');
            } elseif ($actionFilter === 'scan-1-filling-kempu') {
                $query->where(function ($q) {
                    $q->where('action', 'like', '%Filling Kempu%')
                        ->orWhere('action', 'like', '%Scan 1 Filling%');
                });
            } elseif ($actionFilter === 'transfer-out-to-wfg') {
                $query->where('action', 'like', '%Transfer Out to WFG%');
            } elseif ($actionFilter === 'repro-kempu') {
                $query->where(function ($q) {
                    $q->where('action', 'like', '%Repro Kempu%')
                        ->orWhere('action_result', 'REPRO_COMPLETED');
                });
            } elseif ($actionFilter === 'transfer-in-from-wfg') {
                $query->where('action', 'like', '%Transfer in from WFG%');
            } elseif ($actionFilter === 'create-ba-scrap') {
                $query->where(function ($q) {
                    $q->where('action', 'like', '%BA Scrap%')
                        ->orWhere('action_result', 'BA_SCRAP');
                });
            }
        }

        if ($request->filled('is_force_scan')) {
            if ($request->is_force_scan === 'yes') {
                $query->where(function ($q) {
                    $q->where('stage', 'PRODUKSI_FORCE')
                        ->orWhere('action', 'like', '%[FORCE SCAN]%');
                });
            } elseif ($request->is_force_scan === 'no') {
                $query->where('stage', '!=', 'PRODUKSI_FORCE')
                    ->where('action', 'not like', '%[FORCE SCAN]%');
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('id_kempu', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhere('action', 'like', "%{$s}%")
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.operator_name')) LIKE ?", ["%{$s}%"]);
            });
        }

        $records = $query->latest('id')->get();

        $filename = 'Report_Scan_Produksi_Kempu_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header kolom CSV
            fputcsv($file, [
                'No',
                'Tanggal & Waktu',
                'ID Kempu',
                'RFID',
                'Proses / Tindakan',
                'Tipe Scan',
                'Hasil Keputusan',
                'Dari Lokasi',
                'Ke Lokasi',
                'Siklus Reused',
                'Kondisi',
                'Operator',
                'Catatan',
            ]);

            $no = 1;
            foreach ($records as $item) {
                $isForce = ($item->stage === 'PRODUKSI_FORCE' || stripos($item->action, '[FORCE SCAN]') !== false);
                fputcsv($file, [
                    $no++,
                    $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '-',
                    $item->id_kempu ?? '-',
                    $item->masterKempu->rfid ?? '-',
                    $item->action ?? '-',
                    $isForce ? 'FORCE SCAN' : 'NORMAL SCAN',
                    $item->action_result ?? '-',
                    $item->from_location ?? '-',
                    $item->to_location ?? '-',
                    $item->reused_count ?? '0',
                    $item->condition ?? 'OK',
                    $item->operator_display_name,
                    $item->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
