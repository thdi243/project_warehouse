<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
use App\Models\Kempu\KempuTrackingHistoryModel;
use App\Models\Kempu\MasterKempuModel;
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
            'transfer-in-from-wfg' => [
                'key'             => 'transfer-in-from-wfg',
                'title'           => 'Transfer in From WFG',
                'subtitle'        => 'Penerimaan Retur / Reject WFG',
                'status_name'     => MasterKempuModel::STATUS_PROD_TRANSFER_IN_WFG,
                'stage'           => 'PRODUKSI',
                'location'        => MasterKempuModel::LOC_PRODUKSI,
                'from_loc'        => MasterKempuModel::LOC_WFG,
                'to_loc'          => MasterKempuModel::LOC_PRODUKSI,
                'next_status'     => MasterKempuModel::STATUS_PROD_TRANSFER_IN_WFG,
                'icon'            => 'ri-reply-line',
                'badge_color'     => 'warning',
                'btn_text'        => 'Buka Scanner Transfer In WFG',
                'target_statuses' => [
                    MasterKempuModel::STATUS_WFG_REJECT_PROD,
                    'WFG_REJECT_PRODUKSI',
                    'WFG_REJECT_TO_PROD',
                    'Reject ke Produksi',
                    'Transfer Out to Produksi',
                    'IN_TRANSIT_PRODUKSI',
                    'NTI_PRODUKSI',
                ],
                'description'     => 'Penerimaan kempu reject/retur dari WFG untuk evaluasi ulang oleh QC Proses atau pembuatan BA Scrap.',
            ],
            'create-ba-scrap' => [
                'key'             => 'create-ba-scrap',
                'title'           => 'Create BA Scrap',
                'subtitle'        => 'Berita Acara Scrap (Afkir)',
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
                ],
                'description'     => 'Pembuatan Berita Acara (BA) Scrap untuk kempu rusak permanen yang dinyatakan Scrap oleh Engineering Workshop / QC.',
            ],
        ];
    }

    /**
     * API: Get Data Card Produksi dan Jumlah Pending
     */
    public function cardsApi()
    {
        $cards = self::getCards();

        foreach ($cards as $key => &$card) {
            $targetStatuses = $card['target_statuses'];
            $card['count'] = MasterKempuModel::whereHas('main', function ($q) use ($targetStatuses) {
                $q->whereIn('current_status', $targetStatuses)
                    ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
            })->count();
        }
        unset($card);

        $totalProduksi = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_PRODUKSI)
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        return response()->json([
            'status' => true,
            'data'   => [
                'total_in_produksi' => $totalProduksi,
                'cards'             => array_values($cards),
            ],
        ]);
    }

    /**
     * Halaman Hub Index Menu Produksi (5 Cards)
     */
    public function index()
    {
        $cards = self::getCards();

        // Hitung kempu pada masing-masing proses
        foreach ($cards as $key => &$card) {
            $targetStatuses = $card['target_statuses'];
            $card['count'] = MasterKempuModel::whereHas('main', function ($q) use ($targetStatuses) {
                $q->whereIn('current_status', $targetStatuses);
            })->count();
        }

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

        return view('kempu.produksi.scan', compact('card', 'cards'));
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
            'CUCI_KEMPU_COMPLETED',
            'Cuci Kempu Selesai',
        ])) {
            $cuciAt = Carbon::parse($kempu->main->last_scanned_at);
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

        if (strcasecmp($currentStatus, MasterKempuModel::STATUS_SCRAPPED) === 0) {
            return [
                'valid'   => false,
                'message' => "Kempu {$idKempu} sudah berstatus SCRAP (Afkir) dan tidak dapat diproses lagi.",
            ];
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
            }

            return [
                'valid'   => false,
                'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Untuk menjalankan '{$card['title']}', kempu harus memiliki status yang sesuai.",
            ];
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
                'is_flow_valid'    => $flowValidation['valid'],
                'flow_error'       => $flowValidation['message'],
                'cuci_info'        => ($cardKey === 'scan-1-filling-kempu') ? self::getCuciKempuTiming($kempu) : null,
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
        if ($cardKey === 'scan-1-filling-kempu') {
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
            $resultMessage = "Berita Acara (BA) Scrap berhasil dibuat di Produksi. Status kempu resmi menjadi SCRAP (Afkir).";
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

            KempuTrackingHistoryModel::create([
                'kempu_master_id' => $kempu->id,
                'id_kempu'        => $kempu->id_kempu,
                'stage'           => 'PRODUKSI',
                'action'          => $actionTitle,
                'action_result'   => $actionResult,
                'from_location'   => $fromLocation,
                'to_location'     => $toLocation,
                'notes'           => $notes ?: null,
                'created_by'      => Auth::id() ?? $request->input('user_id') ?? 1,
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
}
