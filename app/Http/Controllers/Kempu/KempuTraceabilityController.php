<?php

namespace App\Http\Controllers\Kempu;

use App\Http\Controllers\Controller;
use App\Models\Kempu\MasterKempuModel;
use App\Models\Kempu\KempuTrackingHistoryModel;
use Illuminate\Http\Request;

class KempuTraceabilityController extends Controller
{
    /**
     * Halaman Dashboard / Monitoring Traceability Kempu
     */
    public function index()
    {
        $locations = [
            MasterKempuModel::LOC_WPM          => 'WPM (Packaging Material)',
            MasterKempuModel::LOC_QC_PM        => 'QC Packaging Material',
            MasterKempuModel::LOC_ENG          => 'Engineering Workshop',
            MasterKempuModel::LOC_PRODUKSI     => 'Produksi',
            MasterKempuModel::LOC_QC_PROSES    => 'QC Proses',
            MasterKempuModel::LOC_WFG          => 'WFG (Finished Goods)',
            MasterKempuModel::LOC_PAS          => 'Warehouse PT PAS',
            MasterKempuModel::LOC_SCRAP        => 'Scrap / Afkir',
        ];

        return view('kempu.traceability.index', compact('locations'));
    }

    /**
     * Mengambil statistik KPI & Data Charts untuk Dashboard Traceability Kempu
     */
    public function getDashboardStats()
    {
        $totalKempu = MasterKempuModel::count();
        $totalScrap = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
        })->count();
        $totalActive = max(0, $totalKempu - $totalScrap);

        $totalProduksi = MasterKempuModel::whereHas('main', function ($q) {
            $q->whereIn('current_location', [MasterKempuModel::LOC_PRODUKSI, MasterKempuModel::LOC_QC_PROSES]);
        })->count();

        $totalNearMax = MasterKempuModel::whereHas('main', function ($q) {
            $q->whereBetween('reused_count', [18, 20])
                ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
        })->count();

        $totalMaxReused = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('reused_count', '>=', 21);
        })->count();

        $totalWpm = MasterKempuModel::whereHas('main', function ($q) {
            $q->whereIn('current_location', [MasterKempuModel::LOC_WPM, MasterKempuModel::LOC_QC_PM]);
        })->count();

        $totalWfg = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_WFG);
        })->count();

        $totalRepair = MasterKempuModel::whereHas('main', function ($q) {
            $q->where('current_location', MasterKempuModel::LOC_ENG);
        })->count();

        // 1. Lokasi Kempu Distribution
        $locKeys = [
            MasterKempuModel::LOC_WPM          => 'WPM',
            MasterKempuModel::LOC_QC_PM        => 'QC PM',
            MasterKempuModel::LOC_PRODUKSI     => 'Produksi',
            MasterKempuModel::LOC_QC_PROSES    => 'QC Proses',
            MasterKempuModel::LOC_WFG          => 'WFG',
            MasterKempuModel::LOC_PAS          => 'PT PAS',
            MasterKempuModel::LOC_ENG          => 'Workshop Eng',
            MasterKempuModel::LOC_SCRAP        => 'Scrap',
        ];

        $locationCounts = [];
        foreach ($locKeys as $key => $label) {
            $locationCounts[$label] = MasterKempuModel::whereHas('main', function ($q) use ($key) {
                $q->where('current_location', $key);
            })->count();
        }

        // 2. Reused Distribution Breakdown
        $reusedGroups = [
            '0 - 5x (Baru)'      => MasterKempuModel::whereHas('main', fn($q) => $q->whereBetween('reused_count', [0, 5])->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED))->count(),
            '6 - 12x (Sedang)'   => MasterKempuModel::whereHas('main', fn($q) => $q->whereBetween('reused_count', [6, 12])->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED))->count(),
            '13 - 17x (Lanjut)'  => MasterKempuModel::whereHas('main', fn($q) => $q->whereBetween('reused_count', [13, 17])->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED))->count(),
            '18 - 20x (Warning)' => MasterKempuModel::whereHas('main', fn($q) => $q->whereBetween('reused_count', [18, 20])->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED))->count(),
            '21x (Maksimal)'     => MasterKempuModel::whereHas('main', fn($q) => $q->where('reused_count', '>=', 21))->count(),
        ];

        // 3. Status QC / Hasil Keputusan
        $qcResults = [
            'Release / OK'     => KempuTrackingHistoryModel::whereIn('action_result', ['OK', 'RELEASE'])->count(),
            'Hold (Evaluasi)'  => KempuTrackingHistoryModel::where('action_result', 'HOLD')->count(),
            'Reject / Repair'  => KempuTrackingHistoryModel::where('action_result', 'NOT_OK')->count(),
            'Scrap / Afkir'    => KempuTrackingHistoryModel::where('action_result', 'SCRAPPED')->count(),
        ];

        // 4. Aktivitas Log Terkini (10 riwayat audit trail terakhir)
        $recentActivities = KempuTrackingHistoryModel::with('createdBy:id,username,nama_lengkap')
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'kpi' => [
                    'total_kempu'      => $totalKempu,
                    'total_active'     => $totalActive,
                    'total_produksi'   => $totalProduksi,
                    'total_near_max'   => $totalNearMax,
                    'total_max_reused' => $totalMaxReused,
                    'total_scrap'      => $totalScrap,
                    'total_wpm'        => $totalWpm,
                    'total_wfg'        => $totalWfg,
                    'total_repair'     => $totalRepair,
                ],
                'charts' => [
                    'locations' => [
                        'labels' => array_keys($locationCounts),
                        'series' => array_values($locationCounts),
                    ],
                    'reused' => [
                        'labels' => array_keys($reusedGroups),
                        'series' => array_values($reusedGroups),
                    ],
                    'qc_results' => [
                        'labels' => array_keys($qcResults),
                        'series' => array_values($qcResults),
                    ],
                ],
                'recent_activities' => $recentActivities,
            ],
        ]);
    }

    /**
     * Mengambil data list kempu untuk datatable/monitoring
     */
    public function getData(Request $request)
    {
        $query = MasterKempuModel::query()->with([
            'main',
            'createdBy:id,username,nama_lengkap',
        ]);

        // Search ID / Barcode / Merk / Tipe
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('id_kempu', 'like', "%{$s}%")
                    ->orWhere('rfid', 'like', "%{$s}%")
                    ->orWhere('no_spb', 'like', "%{$s}%")
                    ->orWhere('keterangan', 'like', "%{$s}%");
            });
        }

        // Filter Lokasi
        if ($request->filled('location')) {
            $query->whereHas('main', function ($q) use ($request) {
                $q->where('current_location', $request->location);
            });
        }

        // Filter Status Siklus
        if ($request->filled('status_siklus')) {
            if ($request->status_siklus === 'scrap') {
                $query->whereHas('main', function ($q) {
                    $q->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                });
            } elseif ($request->status_siklus === 'active') {
                $query->whereDoesntHave('main', function ($q) {
                    $q->where('current_status', MasterKempuModel::STATUS_SCRAPPED);
                });
            }
        }

        // Filter Reused Count
        if ($request->filled('reused_status')) {
            $query->whereHas('main', function ($q) use ($request) {
                if ($request->reused_status === 'max') {
                    $q->where('reused_count', '>=', 21);
                } elseif ($request->reused_status === 'warning') {
                    $q->whereBetween('reused_count', [18, 20]);
                } elseif ($request->reused_status === 'normal') {
                    $q->where('reused_count', '<', 18);
                }
            });
        }

        $list = $query->latest('updated_at')->get();

        return response()->json([
            'status' => true,
            'data'   => $list,
        ]);
    }

    /**
     * Mengambil riwayat lengkap tracking per kempu (Timeline)
     */
    public function history($id)
    {
        $kempu = MasterKempuModel::with([
            'main',
            'cycleFillings' => function ($q) {
                $q->with('creator:id,username,nama_lengkap')->orderBy('reused_count', 'desc');
            },
            'trackingHistories' => function ($q) {
                $q->with('createdBy:id,username,nama_lengkap')->latest('id');
            },
        ])->where('id', $id)
          ->orWhere('id_kempu', $id)
          ->first();

        if (!$kempu) {
            return response()->json([
                'status'  => false,
                'message' => "Kempu dengan ID '{$id}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'status'    => true,
            'kempu'     => $kempu,
            'histories' => $kempu->trackingHistories,
        ]);
    }
}
