<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
use App\Models\Kempu\KempuTrackingHistoryModel;
use App\Models\Kempu\MasterKempuModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KempuQcController extends Controller
{
    /**
     * Cek apakah user saat ini memiliki otoritas Force Scan QC
     * Syarat: Bukan operator (roles != operator), ATAU memiliki permission 'kempu-qc-force' / 'super-admin'
     */
    public static function canForceScan($user = null): bool
    {
        // 1. Cek jika request datang dari API eksternal terpercaya (Digimon / QC App)
        $appSource = request()->input('app_source') ?? request()->header('X-App-Source');
        $qcRole    = strtolower(trim(request()->input('operator_role', request()->input('role', ''))));
        $secret    = request()->header('X-QC-App-Secret') ?? request()->input('app_secret');
        $expectedSecret = env('QC_API_SECRET', 'BAS_QC_SECRET_2026');

        if (($appSource === 'digimon_v2' || request()->hasHeader('X-QC-App-Secret')) && $secret === $expectedSecret) {
            // Otoritas valid jika role di Digimon BUKAN operator
            return ($qcRole !== '' && $qcRole !== 'operator');
        }

        $user = $user ?? Auth::user();
        if (!$user) {
            $userEmail = request()->input('operator_email', request()->input('email'));
            if ($userEmail) {
                $user = User::where('email', $userEmail)->first();
            }
            if (!$user) {
                $userId = request()->input('user_id', request()->input('operator_id'));
                if ($userId) {
                    $user = User::find($userId);
                }
            }
        }

        if (!$user) {
            return false;
        }

        // 1. Punya permission eksplisit kempu-qc-force atau super-admin
        if (method_exists($user, 'hasAnyPermission')) {
            try {
                if ($user->hasAnyPermission(['super-admin', 'kempu-qc-force'])) {
                    return true;
                }
            } catch (\Throwable $e) {
            }
        }
        if (method_exists($user, 'hasPermission')) {
            try {
                if ($user->hasPermission('kempu-qc-force')) {
                    return true;
                }
            } catch (\Throwable $e) {
            }
        }

        // 2. Role super-admin
        if (method_exists($user, 'hasRole')) {
            try {
                if ($user->hasRole('super-admin')) {
                    return true;
                }
            } catch (\Throwable $e) {
            }
        }

        // 3. Cek apakah user adalah operator (otoritas harus != operator)
        // Cek via Spatie hasRole('operator')
        if (method_exists($user, 'hasRole')) {
            try {
                if ($user->hasRole('operator')) {
                    return false;
                }
            } catch (\Throwable $e) {
            }
        }

        // Cek via atribut / relasi role (misal di digimon_v2 atau kolom role)
        $roleName = '';
        if (isset($user->role) && is_string($user->role)) {
            $roleName = strtolower(trim($user->role));
        } elseif (isset($user->roles) && $user->roles instanceof \Illuminate\Support\Collection && $user->roles->isNotEmpty()) {
            $roleName = strtolower(trim($user->roles->first()->name ?? ''));
        }

        if ($roleName === 'operator') {
            return false;
        }

        return true;
    }

    /**
     * Konfigurasi Tipe QC
     */
    public static function getQcConfig(): array
    {
        return [
            'qc-pm' => [
                'key'         => 'qc-pm',
                'title'       => 'QC PM',
                'subtitle'    => 'QC Packaging Material',
                'description' => 'Pemeriksaan kempu baru (GR) atau kempu masuk dari PAS di WPM.',
                'icon'        => 'ri-shield-check-line',
                'badge_color' => 'primary',
                'stage'       => 'QC_PM',
                'location'    => MasterKempuModel::LOC_QC_PM,
                'target_statuses' => [
                    MasterKempuModel::STATUS_QC_PM_PENDING,
                    MasterKempuModel::STATUS_GR_COMPLETED,
                    MasterKempuModel::STATUS_REGISTERED,
                    MasterKempuModel::STATUS_WPM_TRANSFER_IN_PAS,
                    'REGISTERED',
                    'Transfer In From PAS',
                ],
            ],
            'qc-pre-cuci' => [
                'key'         => 'qc-pre-cuci',
                'title'       => 'Cek Incoming & Pre Cuci',
                'subtitle'    => 'Pemeriksaan Masuk & Pre-Cuci (+1 Reused)',
                'description' => 'Pemeriksaan kempu masuk dari WPM sekaligus inspeksi kelayakan pre-cuci. Keputusan OK akan menambahkan siklus pemakaian (+1 Reused).',
                'icon'        => 'ri-shield-check-line',
                'badge_color' => 'primary',
                'stage'       => 'QC_PROSES',
                'location'    => MasterKempuModel::LOC_QC_PROSES,
                'target_statuses' => [
                    MasterKempuModel::STATUS_QC_PRE_CUCI_PENDING,
                    MasterKempuModel::STATUS_PROD_TRANSFER_IN_WPM,
                    'QC_PRE_CUCI_PENDING',
                    'Transfer in from WPM',
                    'Transfer In From WPM',
                    'PROD_RECEIVED',
                ],
            ],
            'qc-after-filling' => [
                'key'         => 'qc-after-filling',
                'title'       => 'Cek After Filling',
                'subtitle'    => 'Pemeriksaan Pasca Pengisian (OK / Hold / Reject)',
                'description' => 'Pemeriksaan kempu setelah proses Scan 1 Filling. Tentukan status Lolos (OK), Tahan (Hold), atau Tidak OK (Reject).',
                'icon'        => 'ri-flask-line',
                'badge_color' => 'success',
                'stage'       => 'QC_PROSES',
                'location'    => MasterKempuModel::LOC_QC_PROSES,
                'target_statuses' => [
                    MasterKempuModel::STATUS_PROD_FILLING_KEMPU,
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_PENDING,
                    MasterKempuModel::STATUS_QC_AFTER_FILLING_HOLD,
                    'SCAN1_FILLED',
                    'Scan 1: Filling / Pengisian Kempu',
                    'Scan 1 Filling Kempu',
                    'QC_AFTER_FILLING_HOLD',
                    'QC After Filling Hold',
                ],
            ],
            'qc-force' => [
                'key'         => 'qc-force',
                'title'       => 'Force Scan QC',
                'subtitle'    => 'Decision Bebas Kapanpun & Dimanapun',
                'description' => 'Inspeksi & manual override keputusan QC untuk kempu kapanpun dan dimanapun tanpa terikat alur urutan status normal (Khusus Otoritas QC / Non-Operator).',
                'icon'        => 'ri-shield-flash-line',
                'badge_color' => 'danger',
                'stage'       => 'QC_FORCE',
                'location'    => MasterKempuModel::LOC_QC_PROSES,
                'target_statuses' => ['*'],
            ],
            // Alias backward compatibility untuk 'qc-proses'
            'qc-proses' => [
                'key'         => 'qc-pre-cuci',
                'title'       => 'Cek Incoming & Pre Cuci',
                'subtitle'    => 'Pemeriksaan Masuk & Pre-Cuci (+1 Reused)',
                'description' => 'Pemeriksaan kempu masuk dari WPM sekaligus inspeksi kelayakan pre-cuci. Keputusan OK akan menambahkan siklus pemakaian (+1 Reused).',
                'icon'        => 'ri-shield-check-line',
                'badge_color' => 'primary',
                'stage'       => 'QC_PROSES',
                'location'    => MasterKempuModel::LOC_QC_PROSES,
                'target_statuses' => [
                    MasterKempuModel::STATUS_QC_PRE_CUCI_PENDING,
                    MasterKempuModel::STATUS_PROD_TRANSFER_IN_WPM,
                    'QC_PRE_CUCI_PENDING',
                    'Transfer in from WPM',
                    'Transfer In From WPM',
                    'PROD_RECEIVED',
                ],
            ],
        ];
    }

    /**
     * API: Daftar Konfigurasi Tipe QC
     */
    public function getConfigsApi()
    {
        return response()->json([
            'status' => true,
            'data'   => self::getQcConfig(),
        ]);
    }

    /**
     * API: Data Kartu & Pending Count untuk QC PM
     */
    public function pmCardsApi()
    {
        $configs = self::getQcConfig();
        $pmConfig = $configs['qc-pm'];

        $totalQcPmPending = MasterKempuModel::whereHas('main', function ($q) use ($pmConfig) {
            $q->whereIn('current_status', $pmConfig['target_statuses'])
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        $totalSpbPending = MasterKempuModel::whereNotNull('no_spb')
            ->whereHas('main', function ($q) {
                $q->whereIn('current_status', [
                    MasterKempuModel::STATUS_QC_PM_PENDING,
                    MasterKempuModel::STATUS_GR_COMPLETED,
                    'REGISTERED',
                ]);
            })
            ->distinct('no_spb')
            ->count('no_spb');

        return response()->json([
            'status' => true,
            'data'   => [
                'total_qc_pm_pending' => $totalQcPmPending,
                'total_spb_pending'   => $totalSpbPending,
                'cards'               => [
                    'biasa' => [
                        'key'         => 'biasa',
                        'title'       => 'Cek Incoming',
                        'subtitle'    => 'Scan Satu per Satu',
                        'description' => 'Pemeriksaan kempu secara individual menggunakan camera scanner atau barcode scanner (Keputusan OK / Reject per kempu).',
                        'count'       => $totalQcPmPending,
                        'count_label' => 'kempu siap periksa',
                    ],
                    'bulk' => [
                        'key'         => 'bulk',
                        'title'       => 'Cek Massal Incoming',
                        'subtitle'    => 'Pemeriksaan Masal Berdasarkan No SPB',
                        'description' => 'Scan salah satu barcode kempu untuk menarik seluruh kempu dalam SPB incoming terkait, lalu tentukan keputusan secara serentak.',
                        'count'       => $totalSpbPending,
                        'count_label' => 'SPB Incoming aktif',
                    ],
                    'qc-force' => [
                        'key'         => 'qc-force',
                        'title'       => 'Force Scan QC',
                        'subtitle'    => 'Decision Bebas Kapanpun & Dimanapun',
                        'description' => 'Inspeksi darurat & manual override keputusan QC untuk kempu kapanpun dan dimanapun (Khusus Otoritas QC / Non-Operator).',
                        'badge_color' => 'danger',
                        'icon'        => 'ri-shield-flash-line',
                        'count'       => 'Otoritas',
                        'count_label' => 'Akses Terbatas',
                    ],
                ],
            ],
        ]);
    }

    /**
     * API: Data Kartu & Pending Count untuk QC Proses
     */
    public function prosesCardsApi()
    {
        $configs = self::getQcConfig();
        $preCuciConfig = $configs['qc-pre-cuci'];
        $afterFillingConfig = $configs['qc-after-filling'];

        $totalPreCuciPending = MasterKempuModel::whereHas('main', function ($q) use ($preCuciConfig) {
            $q->whereIn('current_status', $preCuciConfig['target_statuses'])
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        $totalAfterFillingPending = MasterKempuModel::whereHas('main', function ($q) use ($afterFillingConfig) {
            $q->whereIn('current_status', $afterFillingConfig['target_statuses'])
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        return response()->json([
            'status' => true,
            'data'   => [
                'total_pre_cuci_pending'     => $totalPreCuciPending,
                'total_after_filling_pending' => $totalAfterFillingPending,
                'cards'                      => [
                    'qc-pre-cuci' => [
                        'key'         => 'qc-pre-cuci',
                        'title'       => 'Cek Incoming & Pre Cuci',
                        'subtitle'    => 'Pemeriksaan Masuk & Pre-Cuci (+1 Reused)',
                        'description' => 'Pemeriksaan kempu masuk (Incoming dari WPM) sekaligus verifikasi kelayakan Pre-Cuci.',
                        'count'       => $totalPreCuciPending,
                        'count_label' => 'kempu siap periksa',
                    ],
                    'qc-after-filling' => [
                        'key'         => 'qc-after-filling',
                        'title'       => 'Cek After Filling',
                        'subtitle'    => 'Pemeriksaan Pasca Pengisian (OK / Hold / Reject)',
                        'description' => 'Pemeriksaan kempu setelah pengisian muatan (Scan 1 Filling).',
                        'count'       => $totalAfterFillingPending,
                        'count_label' => 'kempu siap periksa',
                    ],
                    'qc-force' => [
                        'key'         => 'qc-force',
                        'title'       => 'Force Scan QC',
                        'subtitle'    => 'Decision Bebas Kapanpun & Dimanapun',
                        'description' => 'Inspeksi darurat & manual override keputusan QC untuk kempu kapanpun dan dimanapun (Khusus Otoritas QC / Non-Operator).',
                        'badge_color' => 'danger',
                        'icon'        => 'ri-shield-flash-line',
                        'count'       => 'Otoritas',
                        'count_label' => 'Akses Terbatas',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Halaman Menu Utama QC (Redirect ke QC PM)
     */
    public function index()
    {
        return redirect()->route('kempu.qc.pm.index');
    }

    /**
     * Halaman Menu Utama QC PM (Cek Incoming, Cek Incoming Bulk, & Force Scan)
     */
    public function pmIndex()
    {
        $configs = self::getQcConfig();
        $pmConfig = $configs['qc-pm'];

        // Jumlah kempu yang siap di-QC PM
        $totalQcPmPending = MasterKempuModel::whereHas('main', function ($q) use ($pmConfig) {
            $q->whereIn('current_status', $pmConfig['target_statuses'])
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        // Jumlah SPB Incoming yang memiliki kempu siap di-QC PM
        $totalSpbPending = MasterKempuModel::whereNotNull('no_spb')
            ->whereHas('main', function ($q) {
                $q->whereIn('current_status', [
                    MasterKempuModel::STATUS_QC_PM_PENDING,
                    MasterKempuModel::STATUS_GR_COMPLETED,
                    'REGISTERED',
                ]);
            })
            ->distinct('no_spb')
            ->count('no_spb');

        $cards = [
            'biasa' => [
                'key'         => 'biasa',
                'title'       => 'Cek Incoming',
                'subtitle'    => 'Scan Satu per Satu',
                'description' => 'Pemeriksaan kempu secara individual menggunakan camera scanner atau barcode scanner (Keputusan OK / Reject per kempu).',
                'icon'        => 'ri-qr-scan-2-line',
                'badge_color' => 'primary',
                'route'       => route('kempu.qc.scan', 'qc-pm'),
                'count'       => $totalQcPmPending,
                'count_label' => 'kempu siap periksa',
            ],
            'bulk' => [
                'key'         => 'bulk',
                'title'       => 'Cek Massal Incoming',
                'subtitle'    => 'Pemeriksaan Masal Berdasarkan No SPB',
                'description' => 'Scan salah satu barcode kempu untuk menarik seluruh kempu dalam SPB incoming terkait, lalu tentukan keputusan secara serentak.',
                'icon'        => 'ri-stack-line',
                'badge_color' => 'success',
                'route'       => route('kempu.qc.pm.bulk'),
                'count'       => $totalSpbPending,
                'count_label' => 'SPB Incoming aktif',
            ],
            'qc-force' => [
                'key'         => 'qc-force',
                'title'       => 'Force Scan QC',
                'subtitle'    => 'Decision Bebas Kapanpun & Dimanapun',
                'description' => 'Inspeksi darurat & manual override keputusan QC untuk kempu kapanpun dan dimanapun (Khusus Otoritas QC / Non-Operator).',
                'icon'        => 'ri-shield-flash-line',
                'badge_color' => 'danger',
                'route'       => route('kempu.qc.scan', 'qc-force'),
                'count'       => 'Otoritas',
                'count_label' => 'Akses Terbatas',
            ],
        ];

        if (!self::canForceScan()) {
            unset($cards['qc-force']);
        }

        return view('kempu.qc.pm.index', compact('cards', 'totalQcPmPending', 'totalSpbPending'));
    }

    /**
     * Halaman Menu Utama QC Proses (QC Pre Cuci, QC After Filling, & Force Scan)
     */
    public function prosesIndex()
    {
        $configs = self::getQcConfig();
        $preCuciConfig = $configs['qc-pre-cuci'];
        $afterFillingConfig = $configs['qc-after-filling'];

        // Jumlah kempu yang siap di-QC Pre Cuci
        $totalPreCuciPending = MasterKempuModel::whereHas('main', function ($q) use ($preCuciConfig) {
            $q->whereIn('current_status', $preCuciConfig['target_statuses'])
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        // Jumlah kempu yang siap di-QC After Filling
        $totalAfterFillingPending = MasterKempuModel::whereHas('main', function ($q) use ($afterFillingConfig) {
            $q->whereIn('current_status', $afterFillingConfig['target_statuses'])
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        $cards = [
            'qc-pre-cuci' => [
                'key'         => 'qc-pre-cuci',
                'title'       => 'Cek Incoming & Pre Cuci',
                'subtitle'    => 'Pemeriksaan Masuk & Pre-Cuci (+1 Reused)',
                'description' => 'Pemeriksaan kempu masuk (Incoming dari WPM) sekaligus verifikasi kelayakan Pre-Cuci. Keputusan OK akan menambah +1 siklus pemakaian (Reused).',
                'icon'        => 'ri-shield-check-line',
                'badge_color' => 'primary',
                'route'       => route('kempu.qc.scan', 'qc-pre-cuci'),
                'count'       => $totalPreCuciPending,
                'count_label' => 'kempu siap periksa',
            ],
            'qc-after-filling' => [
                'key'         => 'qc-after-filling',
                'title'       => 'Cek After Filling',
                'subtitle'    => 'Pemeriksaan Pasca Pengisian (OK / Hold / Reject)',
                'description' => 'Pemeriksaan kempu setelah pengisian muatan (Scan 1 Filling). Tentukan status Lolos (OK), Tahan (Hold), atau Tidak OK (Reject).',
                'icon'        => 'ri-flask-line',
                'badge_color' => 'success',
                'route'       => route('kempu.qc.scan', 'qc-after-filling'),
                'count'       => $totalAfterFillingPending,
                'count_label' => 'kempu siap periksa',
            ],
            'qc-force' => [
                'key'         => 'qc-force',
                'title'       => 'Force Scan QC',
                'subtitle'    => 'Decision Bebas Kapanpun & Dimanapun',
                'description' => 'Inspeksi darurat & manual override keputusan QC untuk kempu kapanpun dan dimanapun (Khusus Otoritas QC / Non-Operator).',
                'icon'        => 'ri-shield-flash-line',
                'badge_color' => 'danger',
                'route'       => route('kempu.qc.scan', 'qc-force'),
                'count'       => 'Otoritas',
                'count_label' => 'Akses Terbatas',
            ],
        ];

        if (!self::canForceScan()) {
            unset($cards['qc-force']);
        }

        return view('kempu.qc.proses.index', compact('cards', 'totalPreCuciPending', 'totalAfterFillingPending'));
    }

    /**
     * Halaman Scanner / Input Cek Incoming Bulk (QC PM)
     */
    public function bulkView()
    {
        return view('kempu.qc.pm.bulk');
    }

    /**
     * AJAX Lookup Barcode untuk Incoming Bulk
     * Menemukan no_spb dari barcode kempu dan memuat semua kempu dalam SPB tersebut
     */
    public function bulkLookup(Request $request)
    {
        $barcode = strtoupper(trim($request->input('barcode', $request->input('id_kempu', ''))));

        if (!$barcode) {
            return response()->json([
                'status'  => false,
                'message' => 'Barcode / ID Kempu tidak boleh kosong.',
            ], 400);
        }

        $kempu = MasterKempuModel::with('main')
            ->where(function ($q) use ($barcode) {
                $q->where('id_kempu', $barcode)
                    ->orWhere('rfid', $barcode)
                    ->orWhere('no_spb', $barcode);
            })
            ->first();

        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu dengan barcode/RFID '{$barcode}' tidak ditemukan dalam sistem Master Kempu.",
            ], 404);
        }

        $noSpb = trim($kempu->no_spb ?? '');

        if (!$noSpb) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu {$barcode} tidak memiliki No SPB terkait (bukan dari pendaftaran incoming SPB). Silakan gunakan menu 'Cek Incoming'.",
            ], 422);
        }

        // Ambil semua kempu yang memiliki nomor SPB yang sama
        $allKempu = MasterKempuModel::with('main')
            ->where('no_spb', $noSpb)
            ->orderBy('id_kempu', 'asc')
            ->get();

        if ($allKempu->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => "Tidak ada kempu yang terdaftar dengan No SPB '{$noSpb}'.",
            ], 404);
        }

        $items = $allKempu->map(function ($item) {
            $status = trim($item->main?->current_status ?? $item->current_status ?? 'REGISTERED');
            $isRelease = (
                strcasecmp($status, 'QC_PM_RELEASE') === 0 ||
                strcasecmp($status, 'QC PM Release') === 0 ||
                strcasecmp($status, MasterKempuModel::STATUS_QC_PM_RELEASE) === 0 ||
                strcasecmp($status, 'QC PM Passed') === 0 ||
                strcasecmp($status, 'QC_PM_PASSED') === 0
            );
            $isReject = (strcasecmp($status, MasterKempuModel::STATUS_ENG_REPAIR) === 0 || strcasecmp($status, 'ENG_REPAIR') === 0);
            $isScrapped = (strcasecmp($status, MasterKempuModel::STATUS_SCRAPPED) === 0);

            return [
                'id'             => $item->id,
                'id_kempu'       => $item->id_kempu,
                'rfid'           => $item->rfid ?? '-',
                'current_status' => $status,
                'reused_count'   => (int)($item->main?->reused_count ?? $item->reused_count ?? 0),
                'is_release'     => $isRelease,
                'is_passed'      => $isRelease,
                'is_reject'      => $isReject,
                'is_scrapped'    => $isScrapped,
                // Default checked: true kecuali jika memang berstatus reject/scrapped
                'default_checked' => !$isReject && !$isScrapped,
            ];
        });

        $grDate = $kempu->gr_date ? \Carbon\Carbon::parse($kempu->gr_date)->format('d/m/Y') : '-';

        return response()->json([
            'status'  => true,
            'message' => "Ditemukan {$allKempu->count()} kempu untuk No SPB '{$noSpb}'.",
            'data'    => [
                'scanned_id'  => $kempu->id_kempu,
                'no_spb'      => $noSpb,
                'gr_date'     => $grDate,
                'total_count' => $allKempu->count(),
                'items'       => $items,
            ],
        ]);
    }

    /**
     * AJAX Eksekusi Keputusan Bulk QC PM untuk satu SPB
     */
    public function bulkDecision(Request $request)
    {
        $noSpb      = strtoupper(trim($request->input('no_spb', '')));
        $allIds     = (array)$request->input('all_ids', []);
        $checkedIds = (array)$request->input('checked_ids', []);
        $notes      = trim($request->input('notes', ''));

        if (!$noSpb) {
            return response()->json([
                'status'  => false,
                'message' => 'No SPB tidak valid atau kosong.',
            ], 400);
        }

        if (empty($allIds)) {
            return response()->json([
                'status'  => false,
                'message' => 'Daftar ID Kempu yang akan diproses tidak boleh kosong.',
            ], 400);
        }

        // Ambil data kempu dari database
        $kempuList = MasterKempuModel::with('main')
            ->where('no_spb', $noSpb)
            ->whereIn('id_kempu', $allIds)
            ->get();

        if ($kempuList->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => "Data kempu untuk No SPB '{$noSpb}' tidak ditemukan.",
            ], 404);
        }

        $checkedLookup = array_flip($checkedIds);
        $okCount = 0;
        $rejectCount = 0;
        $now = now();
        $operatorEmail = $request->input('operator_email');
        $warehouseUser = $operatorEmail ? User::where('email', $operatorEmail)->first() : null;
        if (!$warehouseUser && $request->filled('user_id')) {
            $warehouseUser = User::find($request->input('user_id'));
        }
        $creatorId = $warehouseUser?->id ?? (Auth::check() ? Auth::id() : null);

        $trackingMetadata = [
            'app_source'     => $request->input('app_source', 'warehouse'),
            'operator_name'  => $request->input('operator_name', $warehouseUser?->nama_lengkap ?? ($warehouseUser?->username ?? 'System QC')),
            'operator_role'  => $request->input('operator_role', 'QC'),
            'operator_email' => $operatorEmail,
        ];

        DB::beginTransaction();
        try {
            foreach ($kempuList as $kempu) {
                $isOk = isset($checkedLookup[$kempu->id_kempu]);
                $currentReused = (int)($kempu->main?->reused_count ?? $kempu->reused_count ?? 0);
                $fromLocation  = $kempu->current_location ?? MasterKempuModel::LOC_WPM;

                if ($isOk) {
                    $nextStatus   = MasterKempuModel::STATUS_QC_PM_RELEASE;
                    $nextLocation = MasterKempuModel::LOC_WPM;
                    $actionResult = 'OK';
                    $condition    = 'OK';
                    $actionTitle  = 'QC PM Incoming Bulk Lolos (Release)';
                    $actionNote   = $notes ?: "Pemeriksaan Incoming Bulk SPB {$noSpb}: Lolos (Release)";
                    $okCount++;
                } else {
                    $nextStatus   = MasterKempuModel::STATUS_ENG_REPAIR;
                    $nextLocation = MasterKempuModel::LOC_ENG;
                    $actionResult = 'NOT_OK';
                    $condition    = 'NOT_OK';
                    $actionTitle  = 'QC PM Incoming Bulk Reject (Kirim Workshop)';
                    $actionNote   = $notes ?: "Pemeriksaan Incoming Bulk SPB {$noSpb}: Reject (NOT OK) - Kirim ke Workshop";
                    $rejectCount++;
                }

                if (!$kempu->main) {
                    $kempu->main()->create([
                        'id_kempu'         => $kempu->id_kempu,
                        'current_location' => $nextLocation,
                        'current_status'   => $nextStatus,
                        'reused_count'     => $currentReused,
                        'max_reused'       => 21,
                        'condition'        => $condition,
                        'last_scanned_at'  => $now,
                        'last_action'      => $actionTitle,
                    ]);
                } else {
                    $kempu->main->update([
                        'current_status'   => $nextStatus,
                        'current_location' => $nextLocation,
                        'condition'        => $condition,
                        'last_scanned_at'  => $now,
                        'last_action'      => $actionTitle,
                    ]);
                }

                KempuTrackingHistoryModel::create([
                    'kempu_master_id' => $kempu->id,
                    'id_kempu'        => $kempu->id_kempu,
                    'stage'           => MasterKempuModel::LOC_QC_PM,
                    'action'          => $actionTitle,
                    'action_result'   => $actionResult,
                    'from_location'   => $fromLocation,
                    'to_location'     => $nextLocation,
                    'reused_count'    => $currentReused,
                    'condition'       => $condition,
                    'notes'           => $actionNote,
                    'metadata'        => $trackingMetadata,
                    'created_by'      => $creatorId,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Pemeriksaan Incoming Bulk untuk No SPB '{$noSpb}' berhasil disimpan. Total: {$kempuList->count()} kempu ({$okCount} Lolos OK, {$rejectCount} Reject).",
                'data'    => [
                    'no_spb'       => $noSpb,
                    'total'        => $kempuList->count(),
                    'ok_count'     => $okCount,
                    'reject_count' => $rejectCount,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memproses keputusan bulk: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Halaman Scanner QC (QC PM / QC Proses / Force Scan)
     */
    public function scan($type)
    {
        if ($type === 'qc-proses') {
            return redirect()->route('kempu.qc.proses.index');
        }

        if ($type === 'qc-force') {
            if (!self::canForceScan()) {
                return redirect()->route('kempu.qc.pm.index')
                    ->with('error', 'Akses ditolak: Fitur Force Scan hanya diperuntukkan bagi Supervisor / Leader atau user dengan hak akses kempu-qc-force.');
            }
        }

        $cards = self::getQcConfig();

        if (!array_key_exists($type, $cards)) {
            return redirect()->route('kempu.qc.index')->with('error', 'Tipe QC tidak valid.');
        }

        $card = $cards[$type];

        return view('kempu.qc.proses.scan', compact('card', 'cards'));
    }

    /**
     * Validasi alur kempu untuk QC
     */
    public static function validateQcFlow($kempu, string $qcType): array
    {
        $currentStatus = trim($kempu->main?->current_status ?? $kempu->current_status ?? '');
        $currentLocation = trim($kempu->main?->current_location ?? $kempu->current_location ?? '');
        $idKempu = $kempu->id_kempu;

        // Force Scan membebaskan validasi urutan flow untuk decision kapanpun dan dimanapun
        if ($qcType === 'qc-force') {
            return ['valid' => true, 'message' => null];
        }

        if (strcasecmp($currentStatus, MasterKempuModel::STATUS_SCRAPPED) === 0) {
            return [
                'valid'   => false,
                'message' => "Kempu {$idKempu} berstatus SCRAP / Afkir dan tidak dapat diproses di QC.",
            ];
        }

        $configs = self::getQcConfig();
        if (!isset($configs[$qcType])) {
            return ['valid' => false, 'message' => 'Tipe QC tidak valid.'];
        }

        $card = $configs[$qcType];
        $allowedStatuses = $card['target_statuses'];

        $isMatch = false;
        foreach ($allowedStatuses as $st) {
            if (strcasecmp($currentStatus, $st) === 0) {
                $isMatch = true;
                break;
            }
        }

        if (!$isMatch) {
            if ($qcType === 'qc-pm') {
                return [
                    'valid'   => false,
                    'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). QC PM hanya menerima kempu baru hasil GR atau kempu dari 'Transfer In From PAS' di WPM.",
                ];
            } elseif ($qcType === 'qc-pre-cuci' || $qcType === 'qc-proses') {
                // Beri pesan khusus jika kempu masih berstatus Transfer Out dari WPM / In Transit ke Produksi
                $inTransitStatuses = [
                    MasterKempuModel::STATUS_IN_TRANSIT_PROD,
                    'IN_TRANSIT_PRODUKSI',
                    'Transfer Out To Produksi',
                    'Transfer Out to Produksi',
                    MasterKempuModel::STATUS_QC_PM_RELEASE,
                    MasterKempuModel::STATUS_QC_PM_PASSED,
                    'QC PM Release',
                    'QC PM Passed',
                    'QC PM Lolos (OK)',
                ];
                foreach ($inTransitStatuses as $st) {
                    if (strcasecmp($currentStatus, $st) === 0) {
                        return [
                            'valid'   => false,
                            'message' => "Alur Tidak Sesuai: Kempu {$idKempu} belum di-Transfer In oleh bagian Produksi (Status saat ini: '{$currentStatus}'). Operator Produksi wajib melakukan scan 'Transfer in from WPM' terlebih dahulu sebelum kempu dapat di-QC Pre Cuci.",
                        ];
                    }
                }

                return [
                    'valid'   => false,
                    'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Cek Incoming & Pre Cuci hanya dapat diproses setelah kempu di-Transfer In dari WPM oleh bagian Produksi.",
                ];
            } elseif ($qcType === 'qc-after-filling') {
                return [
                    'valid'   => false,
                    'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). QC After Filling hanya menerima kempu setelah proses Scan 1 Filling di Produksi.",
                ];
            } else {
                return [
                    'valid'   => false,
                    'message' => "Alur Tidak Sesuai: Kempu {$idKempu} saat ini berstatus '{$currentStatus}' (Lokasi: {$currentLocation}). Status tidak memenuhi syarat untuk {$card['title']}.",
                ];
            }
        }

        // Cek jika kempu di QC Pre Cuci sudah mencapai batas 21x Reused
        if ($qcType === 'qc-pre-cuci' || $qcType === 'qc-proses') {
            $reused = (int)($kempu->main?->reused_count ?? 0);
            if ($reused >= 21) {
                return [
                    'valid'   => false,
                    'message' => "Batas Maksimal Tercapai: Kempu {$idKempu} telah mencapai batas pemakaian 21x reused (saat ini: {$reused}x). Kempu tidak dapat digunakan lagi dan harus dialihkan ke SCRAP / Engineering.",
                ];
            }
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Lookup Barcode/RFID Kempu saat di-scan
     */
    public function lookup(Request $request)
    {
        $idKempu = strtoupper(trim($request->input('id_kempu', $request->input('barcode', ''))));
        $qcType  = $request->input('qc_type', $request->input('scan_type'));

        if (!$idKempu || !$qcType) {
            return response()->json([
                'status'  => false,
                'message' => 'Parameter scan tidak lengkap.',
            ], 400);
        }

        if ($qcType === 'qc-force') {
            if (!self::canForceScan()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk melakukan Force Scan QC.',
                ], 403);
            }
        }

        $configs = self::getQcConfig();
        if (!isset($configs[$qcType])) {
            return response()->json([
                'status'  => false,
                'message' => 'Tipe QC tidak valid.',
            ], 400);
        }

        $card = $configs[$qcType];

        $kempu = MasterKempuModel::with('main')
            ->where(function ($q) use ($idKempu) {
                $q->where('id_kempu', $idKempu)
                    ->orWhere('rfid', $idKempu);
            })
            ->first();

        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu dengan barcode/RFID '{$idKempu}' tidak ditemukan.",
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

        $flowValidation = self::validateQcFlow($kempu, $qcType);

        return response()->json([
            'status' => true,
            'data'   => [
                'id'               => $kempu->id,
                'id_kempu'         => $kempu->id_kempu,
                'rfid'             => $kempu->rfid ?? '-',
                'current_location' => $kempu->current_location ?? 'WPM',
                'current_status'   => $kempu->current_status ?? 'REGISTERED',
                'reused_count'     => (int)($kempu->main->reused_count ?? 0),
                'condition'        => $kempu->condition ?? 'OK',
                'qc_title'         => $card['title'],
                'is_force_scan'    => ($qcType === 'qc-force'),
                'is_flow_valid'    => $flowValidation['valid'],
                'flow_error'       => $flowValidation['message'],
            ],
        ]);
    }

    /**
     * Simpan Keputusan QC (OK atau TIDAK OKE / Force Decision)
     */
    public function decision(Request $request)
    {
        $idKempu  = strtoupper(trim($request->input('id_kempu', $request->input('barcode', ''))));
        $qcType   = $request->input('qc_type', $request->input('scan_type'));
        $decision = strtoupper(trim($request->input('decision', ''))); // 'OK', 'HOLD', 'NOT_OK'
        $notes    = trim($request->input('notes', ''));

        if (!$idKempu || !$qcType) {
            return response()->json([
                'status'  => false,
                'message' => 'Parameter ID kempu atau tipe QC tidak lengkap.',
            ], 400);
        }

        if ($qcType === 'qc-force') {
            if (!self::canForceScan()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk melakukan Force Decision QC.',
                ], 403);
            }
        }

        // Validasi keputusan per tipe QC
        if ($qcType === 'qc-force') {
            $validDecisions = [
                'OK',
                'HOLD',
                'NOT_OK',
                'RELEASE_PM',
                'RELEASE_PRE_CUCI',
                'RELEASE_AFTER_FILLING',
                'REJECT_WORKSHOP',
                'SCRAP'
            ];
        } elseif ($qcType === 'qc-after-filling') {
            $validDecisions = ['OK', 'HOLD', 'NOT_OK'];
        } else {
            $validDecisions = ['OK', 'NOT_OK'];
        }

        if (!in_array($decision, $validDecisions)) {
            return response()->json([
                'status'  => false,
                'message' => 'Keputusan QC tidak valid untuk tipe pemeriksaan ini.',
            ], 400);
        }

        $configs = self::getQcConfig();
        if (!isset($configs[$qcType])) {
            return response()->json([
                'status'  => false,
                'message' => 'Tipe QC tidak valid.',
            ], 400);
        }

        $card = $configs[$qcType];

        $kempu = MasterKempuModel::with('main')->where('id_kempu', $idKempu)->first();

        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu '{$idKempu}' tidak ditemukan.",
            ], 404);
        }

        // Validasi Alur
        $flowValidation = self::validateQcFlow($kempu, $qcType);
        if (!$flowValidation['valid']) {
            return response()->json([
                'status'  => false,
                'message' => $flowValidation['message'],
            ], 422);
        }

        $currentReused = (int)($kempu->main?->reused_count ?? 0);
        $fromLocation  = $kempu->main?->current_location ?? $kempu->current_location ?? 'PRODUKSI';
        $currentStatus = trim($kempu->main?->current_status ?? $kempu->current_status ?? '');

        // Tentukan Status dan Lokasi Tujuan Berdasarkan Tipe QC & Keputusan
        if ($qcType === 'qc-pm') {
            if ($decision === 'OK') {
                $nextStatus   = MasterKempuModel::STATUS_QC_PM_RELEASE;
                $nextLocation = MasterKempuModel::LOC_WPM;
                $actionResult = 'OK';
                $condition    = 'OK';
                $actionTitle  = 'QC PM Lolos (Release)';
                $notes        = $notes ?: 'Lolos QC PM (Release)';
            } else {
                $nextStatus   = MasterKempuModel::STATUS_ENG_REPAIR;
                $nextLocation = MasterKempuModel::LOC_ENG;
                $actionResult = 'NOT_OK';
                $condition    = 'NOT_OK';
                $actionTitle  = 'QC PM Reject (Kirim Workshop)';
                $notes        = $notes ?: 'Reject QC PM - Menunggu Perbaikan';
            }
        } elseif ($qcType === 'qc-pre-cuci' || $qcType === 'qc-proses') {
            if ($decision === 'OK') {
                // Di QC Pre Cuci: Reused +1!
                if ($currentReused >= 21) {
                    return response()->json([
                        'status'  => false,
                        'message' => "Kempu {$idKempu} telah mencapai batas 21x Reused. Tidak dapat digunakan lagi.",
                    ], 422);
                }
                $currentReused += 1;
                $nextStatus   = MasterKempuModel::STATUS_QC_PRE_CUCI_RELEASE;
                $nextLocation = MasterKempuModel::LOC_PRODUKSI;
                $actionResult = 'OK';
                $condition    = 'OK';
                $actionTitle  = 'Cek Incoming & Pre Cuci Lolos (Release)';
                $notes        = $notes ?: "Lolos Cek Incoming & Pre Cuci (Release) - Siklus Reused ke-{$currentReused}/21";
            } else {
                $nextStatus   = MasterKempuModel::STATUS_ENG_REPAIR;
                $nextLocation = MasterKempuModel::LOC_ENG;
                $actionResult = 'NOT_OK';
                $condition    = 'NOT_OK';
                $actionTitle  = 'Cek Incoming & Pre Cuci Reject (Kirim Workshop)';
                $notes        = $notes ?: 'Reject Cek Incoming & Pre Cuci - Menunggu Perbaikan Engineering';
            }
        } elseif ($qcType === 'qc-after-filling') {
            if ($decision === 'OK') {
                $nextStatus   = MasterKempuModel::STATUS_QC_AFTER_FILLING_RELEASE;
                $nextLocation = MasterKempuModel::LOC_PRODUKSI;
                $actionResult = 'OK';
                $condition    = 'OK';
                $actionTitle  = 'After Filling Lolos (Release)';
                $notes        = $notes ?: 'Lolos After Filling (Release) - Siap Transfer Out ke WFG';
            } elseif ($decision === 'HOLD') {
                $nextStatus   = MasterKempuModel::STATUS_QC_AFTER_FILLING_HOLD;
                $nextLocation = MasterKempuModel::LOC_PRODUKSI;
                $actionResult = 'HOLD';
                $condition    = 'HOLD';
                $actionTitle  = 'After Filling Tahan (HOLD)';
                $notes        = $notes ?: 'After Filling Ditahan (Hold) - Menunggu Evaluasi Lanjutan';
            } else {
                $nextStatus   = MasterKempuModel::STATUS_QC_AFTER_FILLING_REJECT;
                $nextLocation = MasterKempuModel::LOC_ENG;
                $actionResult = 'NOT_OK';
                $condition    = 'NOT_OK';
                $actionTitle  = 'After Filling Reject';
                $notes        = $notes ?: 'Reject After Filling - Penanganan Produksi/Engineering';
            }
        } elseif ($qcType === 'qc-force') {
            $forceTarget = strtoupper(trim($request->input('force_target', $decision)));

            switch ($forceTarget) {
                case 'RELEASE_PM':
                    $nextStatus   = MasterKempuModel::STATUS_QC_PM_RELEASE;
                    $nextLocation = MasterKempuModel::LOC_WPM;
                    $actionResult = 'OK';
                    $condition    = 'OK';
                    $actionTitle  = '[FORCE SCAN] QC PM Lolos (Release)';
                    $notes        = $notes ?: 'Force Decision: Lolos QC PM (Release ke WPM)';
                    break;

                case 'RELEASE_PRE_CUCI':
                    if ($currentReused < 21) {
                        $currentReused += 1;
                    }
                    $nextStatus   = MasterKempuModel::STATUS_QC_PRE_CUCI_RELEASE;
                    $nextLocation = MasterKempuModel::LOC_PRODUKSI;
                    $actionResult = 'OK';
                    $condition    = 'OK';
                    $actionTitle  = '[FORCE SCAN] Cek Pre Cuci Lolos (Release)';
                    $notes        = $notes ?: "Force Decision: Lolos Pre Cuci - Siklus Reused {$currentReused}/21";
                    break;

                case 'RELEASE_AFTER_FILLING':
                    $nextStatus   = MasterKempuModel::STATUS_QC_AFTER_FILLING_RELEASE;
                    $nextLocation = MasterKempuModel::LOC_PRODUKSI;
                    $actionResult = 'OK';
                    $condition    = 'OK';
                    $actionTitle  = '[FORCE SCAN] After Filling Lolos (Release)';
                    $notes        = $notes ?: 'Force Decision: Lolos After Filling (Release ke WFG)';
                    break;

                case 'HOLD':
                    $nextStatus   = MasterKempuModel::STATUS_QC_AFTER_FILLING_HOLD;
                    $nextLocation = MasterKempuModel::LOC_PRODUKSI;
                    $actionResult = 'HOLD';
                    $condition    = 'HOLD';
                    $actionTitle  = '[FORCE SCAN] QC Ditahan (HOLD)';
                    $notes        = $notes ?: 'Force Decision: Ditahan (HOLD) untuk evaluasi lanjutan';
                    break;

                case 'SCRAP':
                    $nextStatus   = MasterKempuModel::STATUS_SCRAPPED;
                    $nextLocation = MasterKempuModel::LOC_SCRAP;
                    $actionResult = 'SCRAPPED';
                    $condition    = 'NOT_OK';
                    $actionTitle  = '[FORCE SCAN] QC Afkir (Scrap)';
                    $notes        = $notes ?: 'Force Decision: Afkir / Scrap (Kempu Rusak Berat)';
                    break;

                case 'NOT_OK':
                case 'REJECT_WORKSHOP':
                    $nextStatus   = MasterKempuModel::STATUS_ENG_REPAIR;
                    $nextLocation = MasterKempuModel::LOC_ENG;
                    $actionResult = 'NOT_OK';
                    $condition    = 'NOT_OK';
                    $actionTitle  = '[FORCE SCAN] QC Reject (Kirim Workshop)';
                    $notes        = $notes ?: 'Force Decision: Reject QC - Kirim Workshop Engineering';
                    break;

                default:
                    if ($decision === 'OK') {
                        if (strtoupper($fromLocation) === MasterKempuModel::LOC_WPM || in_array(strtoupper($currentStatus), ['REGISTERED', 'QC_PM_PENDING', 'QC_PM_RELEASE'])) {
                            $nextStatus   = MasterKempuModel::STATUS_QC_PM_RELEASE;
                            $nextLocation = MasterKempuModel::LOC_WPM;
                            $actionResult = 'OK';
                            $condition    = 'OK';
                            $actionTitle  = '[FORCE SCAN] QC PM Lolos (Release)';
                            $notes        = $notes ?: 'Force Decision: Lolos QC PM (Release ke WPM)';
                        } else {
                            if ($currentReused < 21) {
                                $currentReused += 1;
                            }
                            $nextStatus   = MasterKempuModel::STATUS_QC_PRE_CUCI_RELEASE;
                            $nextLocation = MasterKempuModel::LOC_PRODUKSI;
                            $actionResult = 'OK';
                            $condition    = 'OK';
                            $actionTitle  = '[FORCE SCAN] QC Lolos (Release)';
                            $notes        = $notes ?: "Force Decision: Lolos QC - Reused {$currentReused}/21";
                        }
                    } elseif ($decision === 'HOLD') {
                        $nextStatus   = MasterKempuModel::STATUS_QC_AFTER_FILLING_HOLD;
                        $nextLocation = MasterKempuModel::LOC_PRODUKSI;
                        $actionResult = 'HOLD';
                        $condition    = 'HOLD';
                        $actionTitle  = '[FORCE SCAN] QC Ditahan (HOLD)';
                        $notes        = $notes ?: 'Force Decision: Ditahan (HOLD)';
                    } else {
                        $nextStatus   = MasterKempuModel::STATUS_ENG_REPAIR;
                        $nextLocation = MasterKempuModel::LOC_ENG;
                        $actionResult = 'NOT_OK';
                        $condition    = 'NOT_OK';
                        $actionTitle  = '[FORCE SCAN] QC Reject (Kirim Workshop)';
                        $notes        = $notes ?: 'Force Decision: Reject QC - Kirim Workshop Engineering';
                    }
                    break;
            }
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
                    'reused_count'     => $currentReused,
                    'condition'        => $condition,
                    'last_scanned_at'  => now(),
                    'last_action'      => $actionTitle,
                ]);
            }

            $operatorEmail = $request->input('operator_email');
            $warehouseUser = $operatorEmail ? User::where('email', $operatorEmail)->first() : null;
            if (!$warehouseUser && $request->filled('user_id')) {
                $warehouseUser = User::find($request->input('user_id'));
            }
            $creatorId = $warehouseUser?->id ?? (Auth::check() ? Auth::id() : null);

            $trackingMetadata = [
                'app_source'     => $request->input('app_source', 'warehouse'),
                'operator_name'  => $request->input('operator_name', $warehouseUser?->nama_lengkap ?? ($warehouseUser?->username ?? 'System QC')),
                'operator_role'  => $request->input('operator_role', 'QC'),
                'operator_email' => $operatorEmail,
            ];

            KempuTrackingHistoryModel::create([
                'kempu_master_id' => $kempu->id,
                'id_kempu'        => $kempu->id_kempu,
                'stage'           => $card['stage'],
                'action'          => $actionTitle,
                'action_result'   => $actionResult,
                'from_location'   => $fromLocation,
                'to_location'     => $nextLocation,
                'reused_count'    => $currentReused,
                'condition'       => $condition,
                'notes'           => $notes,
                'metadata'        => $trackingMetadata,
                'created_by'      => $creatorId,
            ]);

            DB::commit();

            $decisionLabel = $decision === 'OK' ? 'OK (Lolos)' : ($decision === 'HOLD' ? 'HOLD (Tahan)' : 'TIDAK OK (Reject)');

            return response()->json([
                'status'  => true,
                'message' => "Kempu {$kempu->id_kempu} berhasil dicatat: {$decisionLabel}",
                'data'    => [
                    'id_kempu'     => $kempu->id_kempu,
                    'decision'     => $decision,
                    'new_status'   => $nextStatus,
                    'new_location' => $nextLocation,
                    'reused_count' => $currentReused,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menyimpan hasil QC: ' . $e->getMessage(),
            ], 500);
        }
    }
}
