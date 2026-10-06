<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kempu\MasterKempuModel;
use App\Models\Kempu\KempuMainModel;
use App\Models\Kempu\KempuTrackingHistoryModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class KempuDashboardController extends Controller
{
    /**
     * Render Halaman Dashboard Monitoring Kempu
     */
    public function index()
    {
        $locations = [
            MasterKempuModel::LOC_WPM          => 'WPM (Packaging Material)',
            MasterKempuModel::LOC_QC_PM        => 'QC Packaging Material',
            MasterKempuModel::LOC_PRODUKSI     => 'Produksi',
            MasterKempuModel::LOC_QC_PROSES    => 'QC Proses',
            MasterKempuModel::LOC_WFG          => 'WFG (Finished Goods)',
            MasterKempuModel::LOC_PAS          => 'Warehouse PT PAS',
            MasterKempuModel::LOC_ENG          => 'Engineering Workshop',
            MasterKempuModel::LOC_SCRAP        => 'Scrap / Afkir',
        ];

        return view('dashboard.kempu_dashboard', compact('locations'));
    }

    /**
     * Mengambil KPI Stat & Metrics secara Real-time
     */
    public function getKpi(Request $request)
    {
        try {
            $totalKempu = MasterKempuModel::count();

            // Status Operasional
            $totalScrap = MasterKempuModel::whereHas('main', function ($q) {
                $q->where('current_status', MasterKempuModel::STATUS_SCRAPPED)
                  ->orWhere('current_location', MasterKempuModel::LOC_SCRAP);
            })->count();

            $totalActive = max(0, $totalKempu - $totalScrap);

            // Per Lokasi
            $locCounts = [
                'wpm'       => MasterKempuModel::whereHas('main', fn($q) => $q->where('current_location', MasterKempuModel::LOC_WPM))->count(),
                'qc_pm'     => MasterKempuModel::whereHas('main', fn($q) => $q->where('current_location', MasterKempuModel::LOC_QC_PM))->count(),
                'produksi'  => MasterKempuModel::whereHas('main', fn($q) => $q->where('current_location', MasterKempuModel::LOC_PRODUKSI))->count(),
                'qc_proses' => MasterKempuModel::whereHas('main', fn($q) => $q->where('current_location', MasterKempuModel::LOC_QC_PROSES))->count(),
                'wfg'       => MasterKempuModel::whereHas('main', fn($q) => $q->where('current_location', MasterKempuModel::LOC_WFG))->count(),
                'pas'       => MasterKempuModel::whereHas('main', fn($q) => $q->where('current_location', MasterKempuModel::LOC_PAS))->count(),
                'eng'       => MasterKempuModel::whereHas('main', fn($q) => $q->where('current_location', MasterKempuModel::LOC_ENG))->count(),
                'scrap'     => $totalScrap,
            ];

            // Reused Lifespan Metrics
            $reusedNormal = MasterKempuModel::whereHas('main', function ($q) {
                $q->where('reused_count', '<', 18)
                  ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
            })->count();

            $reusedWarning = MasterKempuModel::whereHas('main', function ($q) {
                $q->whereBetween('reused_count', [18, 20])
                  ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
            })->count();

            $reusedMax = MasterKempuModel::whereHas('main', function ($q) {
                $q->where('reused_count', '>=', 21);
            })->count();

            // Avg Reused (Hanya kempu aktif)
            $avgReused = KempuMainModel::where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED)
                ->avg('reused_count');
            $avgReused = round($avgReused ?? 0, 1);

            // Kondisi Fisik & Kelengkapan
            $conditionNotOk = KempuMainModel::where('condition', '!=', 'OK')->count();
            $missingBarcode = KempuMainModel::where('has_barcode', false)->count();
            $missingRfid    = KempuMainModel::where('has_rfid', false)->count();
            $missingNti     = KempuMainModel::where('has_nti', false)->count();

            // Aktivitas Hari Ini (Today)
            $today = Carbon::today();
            $todayScans = KempuTrackingHistoryModel::whereDate('created_at', $today)->count();

            // Scan per Stage Hari Ini
            $stageScansToday = [
                'WPM'       => KempuTrackingHistoryModel::whereDate('created_at', $today)->where('stage', 'WPM')->count(),
                'QC_PM'     => KempuTrackingHistoryModel::whereDate('created_at', $today)->where('stage', 'QC_PM')->count(),
                'PRODUKSI'  => KempuTrackingHistoryModel::whereDate('created_at', $today)->where('stage', 'PRODUKSI')->count(),
                'QC_PROSES' => KempuTrackingHistoryModel::whereDate('created_at', $today)->where('stage', 'QC_PROSES')->count(),
                'WFG'       => KempuTrackingHistoryModel::whereDate('created_at', $today)->where('stage', 'WFG')->count(),
                'PAS'       => KempuTrackingHistoryModel::whereDate('created_at', $today)->where('stage', 'PAS')->count(),
                'ENG'       => KempuTrackingHistoryModel::whereDate('created_at', $today)->where('stage', 'ENG')->count(),
            ];

            // Inflow & Outflow Hari Ini
            // Inflow: Good Receipt WPM atau Transfer In dari PAS ke WPM
            $inflowToday = KempuTrackingHistoryModel::whereDate('created_at', $today)
                ->where(function ($q) {
                    $q->where('action', 'LIKE', '%GR%')
                      ->orWhere('action', 'LIKE', '%TRANSFER_IN%')
                      ->orWhere('to_location', MasterKempuModel::LOC_WPM);
                })->count();

            // Outflow: Transfer Out ke PAS
            $outflowToday = KempuTrackingHistoryModel::whereDate('created_at', $today)
                ->where(function ($q) {
                    $q->where('action', 'LIKE', '%TRANSFER_OUT%')
                      ->orWhere('to_location', MasterKempuModel::LOC_PAS);
                })->count();

            return response()->json([
                'status' => true,
                'data'   => [
                    'total_kempu'     => $totalKempu,
                    'total_active'    => $totalActive,
                    'total_scrap'     => $totalScrap,
                    'locations'       => $locCounts,
                    'reused'          => [
                        'normal'  => $reusedNormal,
                        'warning' => $reusedWarning,
                        'max'     => $reusedMax,
                        'average' => $avgReused,
                    ],
                    'health'          => [
                        'not_ok'          => $conditionNotOk,
                        'missing_barcode' => $missingBarcode,
                        'missing_rfid'    => $missingRfid,
                        'missing_nti'     => $missingNti,
                    ],
                    'today'           => [
                        'scans'        => $todayScans,
                        'stages'       => $stageScansToday,
                        'inflow'       => $inflowToday,
                        'outflow'      => $outflowToday,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memuat KPI data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil Data Charts (Distribusi Lokasi, Reused, Tren Scan, Keputusan QC)
     */
    public function getCharts(Request $request)
    {
        try {
            $days = (int) ($request->get('days', 7));
            if ($days < 3 || $days > 60) {
                $days = 7;
            }

            // 1. Distribusi Lokasi
            $locationLabels = ['WPM', 'QC PM', 'Produksi', 'QC Proses', 'WFG', 'PT PAS', 'Workshop ENG', 'Scrap'];
            $locationKeys   = [
                MasterKempuModel::LOC_WPM,
                MasterKempuModel::LOC_QC_PM,
                MasterKempuModel::LOC_PRODUKSI,
                MasterKempuModel::LOC_QC_PROSES,
                MasterKempuModel::LOC_WFG,
                MasterKempuModel::LOC_PAS,
                MasterKempuModel::LOC_ENG,
                MasterKempuModel::LOC_SCRAP,
            ];

            $locationSeries = [];
            foreach ($locationKeys as $key) {
                $locationSeries[] = MasterKempuModel::whereHas('main', function ($q) use ($key) {
                    if ($key === MasterKempuModel::LOC_SCRAP) {
                        $q->where('current_status', MasterKempuModel::STATUS_SCRAPPED)
                          ->orWhere('current_location', MasterKempuModel::LOC_SCRAP);
                    } else {
                        $q->where('current_location', $key)
                          ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
                    }
                })->count();
            }

            // 2. Distribusi Kelompok Reused 21x
            $reusedLabels = ['0 - 5x (Baru)', '6 - 12x (Sedang)', '13 - 17x (Lanjut)', '18 - 20x (Warning)', '≥ 21x (Maksimal)'];
            $reusedSeries = [
                MasterKempuModel::whereHas('main', fn($q) => $q->whereBetween('reused_count', [0, 5])->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED))->count(),
                MasterKempuModel::whereHas('main', fn($q) => $q->whereBetween('reused_count', [6, 12])->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED))->count(),
                MasterKempuModel::whereHas('main', fn($q) => $q->whereBetween('reused_count', [13, 17])->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED))->count(),
                MasterKempuModel::whereHas('main', fn($q) => $q->whereBetween('reused_count', [18, 20])->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED))->count(),
                MasterKempuModel::whereHas('main', fn($q) => $q->where('reused_count', '>=', 21))->count(),
            ];

            // 3. Tren Scan Aktivitas N Hari Terakhir per Departemen
            $startDate = Carbon::today()->subDays($days - 1);
            $trendDates = [];
            for ($i = 0; $i < $days; $i++) {
                $trendDates[] = $startDate->copy()->addDays($i)->format('Y-m-d');
            }

            $trendRaw = KempuTrackingHistoryModel::whereDate('created_at', '>=', $startDate)
                ->select(DB::raw('DATE(created_at) as scan_date'), 'stage', DB::raw('COUNT(*) as total'))
                ->groupBy('scan_date', 'stage')
                ->get();

            $stages = ['WPM', 'QC_PM', 'PRODUKSI', 'QC_PROSES', 'WFG', 'PAS', 'ENG'];
            $trendSeries = [];
            foreach ($stages as $stage) {
                $seriesData = [];
                foreach ($trendDates as $dt) {
                    $match = $trendRaw->first(fn($item) => $item->scan_date === $dt && $item->stage === $stage);
                    $seriesData[] = $match ? (int)$match->total : 0;
                }
                $trendSeries[] = [
                    'name' => str_replace('_', ' ', $stage),
                    'data' => $seriesData,
                ];
            }

            $formattedDates = array_map(function ($d) {
                return Carbon::parse($d)->translatedFormat('d M');
            }, $trendDates);

            // 4. Hasil Keputusan QC (Quality Gate)
            $qcOk      = KempuTrackingHistoryModel::whereIn('action_result', ['OK', 'RELEASE'])->count();
            $qcHold    = KempuTrackingHistoryModel::where('action_result', 'HOLD')->count();
            $qcRepair  = KempuTrackingHistoryModel::whereIn('action_result', ['NOT_OK', 'REJECT', 'REPRO'])->count();
            $qcScrap   = KempuTrackingHistoryModel::where('action_result', 'SCRAPPED')->count();

            return response()->json([
                'status' => true,
                'data'   => [
                    'location' => [
                        'labels' => $locationLabels,
                        'series' => $locationSeries,
                    ],
                    'reused' => [
                        'labels' => $reusedLabels,
                        'series' => $reusedSeries,
                    ],
                    'trend' => [
                        'categories' => $formattedDates,
                        'series'     => $trendSeries,
                    ],
                    'qc' => [
                        'labels' => ['Release / OK', 'Hold (Evaluasi)', 'Reject / Repair', 'Scrap / Afkir'],
                        'series' => [$qcOk, $qcHold, $qcRepair, $qcScrap],
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memuat charts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil Data Tabel List Kempu dengan Filter Lengkap
     */
    public function getData(Request $request)
    {
        try {
            $query = MasterKempuModel::query()->with([
                'main',
                'createdBy:id,username,nama_lengkap',
            ]);

            // Search Filter
            if ($request->filled('search')) {
                $s = trim($request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('id_kempu', 'like', "%{$s}%")
                      ->orWhere('rfid', 'like', "%{$s}%")
                      ->orWhere('no_spb', 'like', "%{$s}%")
                      ->orWhere('keterangan', 'like', "%{$s}%");
                });
            }

            // Lokasi Filter
            if ($request->filled('location') && $request->location !== 'all') {
                $loc = $request->location;
                if ($loc === 'SCRAP') {
                    $query->whereHas('main', function ($q) {
                        $q->where('current_status', MasterKempuModel::STATUS_SCRAPPED)
                          ->orWhere('current_location', MasterKempuModel::LOC_SCRAP);
                    });
                } else {
                    $query->whereHas('main', function ($q) use ($loc) {
                        $q->where('current_location', $loc)
                          ->where('current_status', '!=', MasterKempuModel::STATUS_SCRAPPED);
                    });
                }
            }

            // Status Reused Filter
            if ($request->filled('reused_status') && $request->reused_status !== 'all') {
                $st = $request->reused_status;
                $query->whereHas('main', function ($q) use ($st) {
                    if ($st === 'max') {
                        $q->where('reused_count', '>=', 21);
                    } elseif ($st === 'warning') {
                        $q->whereBetween('reused_count', [18, 20]);
                    } elseif ($st === 'normal') {
                        $q->where('reused_count', '<', 18);
                    }
                });
            }

            // Kondisi Fisik Filter
            if ($request->filled('condition') && $request->condition !== 'all') {
                $cond = $request->condition;
                $query->whereHas('main', fn($q) => $q->where('condition', $cond));
            }

            // Kelengkapan Filter
            if ($request->filled('tag_issue') && $request->tag_issue !== 'all') {
                $tag = $request->tag_issue;
                $query->whereHas('main', function ($q) use ($tag) {
                    if ($tag === 'missing_barcode') {
                        $q->where('has_barcode', false);
                    } elseif ($tag === 'missing_rfid') {
                        $q->where('has_rfid', false);
                    } elseif ($tag === 'missing_nti') {
                        $q->where('has_nti', false);
                    } elseif ($tag === 'any_missing') {
                        $q->where(fn($sub) => $sub->where('has_barcode', false)->orWhere('has_rfid', false)->orWhere('has_nti', false));
                    }
                });
            }

            // Status Master
            if ($request->filled('status_master') && $request->status_master !== 'all') {
                $query->where('status', $request->status_master);
            }

            $perPage = (int) ($request->get('per_page', 25));
            if ($perPage < 5 || $perPage > 200) {
                $perPage = 25;
            }

            $paginated = $query->latest('updated_at')->paginate($perPage);

            // Format data items
            $items = collect($paginated->items())->map(function ($kempu) {
                $main = $kempu->main;
                $reused = $main?->reused_count ?? 0;
                $maxReused = $main?->max_reused ?? 21;
                $pctReused = min(100, round(($reused / max(1, $maxReused)) * 100));

                $lastScannedAt = $main?->last_scanned_at ? Carbon::parse($main->last_scanned_at) : null;
                $idleDays = $lastScannedAt ? $lastScannedAt->diffInDays(now()) : null;

                return [
                    'id'               => $kempu->id,
                    'id_kempu'         => $kempu->id_kempu,
                    'rfid'             => $kempu->rfid ?: '-',
                    'no_spb'           => $kempu->no_spb ?: '-',
                    'current_location' => $main?->current_location ?? 'WPM',
                    'current_status'   => $main?->current_status ?? 'UNKNOWN',
                    'condition'        => $main?->condition ?? 'OK',
                    'reused_count'     => $reused,
                    'max_reused'       => $maxReused,
                    'reused_pct'       => $pctReused,
                    'has_barcode'      => (bool)($main?->has_barcode ?? true),
                    'has_rfid'         => (bool)($main?->has_rfid ?? true),
                    'has_nti'          => (bool)($main?->has_nti ?? true),
                    'last_action'      => $main?->last_action ?: '-',
                    'last_scanned_at'  => $lastScannedAt ? $lastScannedAt->format('d/m/Y H:i') : '-',
                    'last_scanned_diff'=> $lastScannedAt ? $lastScannedAt->diffForHumans() : 'Belum pernah scan',
                    'idle_days'        => $idleDays,
                    'status_master'    => $kempu->status,
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => [
                    'items'        => $items,
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                    'from'         => $paginated->firstItem(),
                    'to'           => $paginated->lastItem(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memuat list kempu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil 15 Aktivitas Scan Terkini (Real-time Feed)
     */
    public function getRecentScans()
    {
        try {
            $scans = KempuTrackingHistoryModel::with([
                'createdBy:id,username,nama_lengkap',
                'masterKempu:id,id_kempu,rfid',
            ])
            ->latest('id')
            ->take(15)
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'id_kempu'      => $item->id_kempu,
                    'stage'         => $item->stage,
                    'action'        => $item->action,
                    'action_result' => $item->action_result ?: 'OK',
                    'from_location' => $item->from_location ?: '-',
                    'to_location'   => $item->to_location ?: '-',
                    'reused_count'  => $item->reused_count,
                    'condition'     => $item->condition ?: 'OK',
                    'operator'      => $item->operator_display_name,
                    'notes'         => $item->notes ?: '',
                    'scanned_at'    => $item->created_at ? $item->created_at->format('d/m/Y H:i:s') : '-',
                    'time_ago'      => $item->created_at ? $item->created_at->diffForHumans() : '-',
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $scans,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memuat recent scans: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil Riwayat Timeline Traceability Kempu untuk Modal
     */
    public function getHistory($id)
    {
        try {
            $kempu = MasterKempuModel::with([
                'main',
                'trackingHistories' => function ($q) {
                    $q->with('createdBy:id,username,nama_lengkap')->latest('id');
                },
            ])
            ->where('id', $id)
            ->orWhere('id_kempu', $id)
            ->first();

            if (!$kempu) {
                return response()->json([
                    'status'  => false,
                    'message' => "Kempu ID '{$id}' tidak ditemukan.",
                ], 404);
            }

            return response()->json([
                'status'    => true,
                'kempu'     => $kempu,
                'histories' => $kempu->trackingHistories,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memuat timeline history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ekspor Data Kempu ke Format Microsoft Excel (.xlsx)
     */
    public function exportData(Request $request): StreamedResponse
    {
        $query = MasterKempuModel::query()->with(['main', 'createdBy:id,username,nama_lengkap']);

        if ($request->filled('location') && $request->location !== 'all') {
            $loc = $request->location;
            if ($loc === 'SCRAP') {
                $query->whereHas('main', function ($q) {
                    $q->where('current_status', MasterKempuModel::STATUS_SCRAPPED)
                      ->orWhere('current_location', MasterKempuModel::LOC_SCRAP);
                });
            } else {
                $query->whereHas('main', fn($q) => $q->where('current_location', $loc));
            }
        }

        if ($request->filled('reused_status') && $request->reused_status !== 'all') {
            $st = $request->reused_status;
            $query->whereHas('main', function ($q) use ($st) {
                if ($st === 'max') {
                    $q->where('reused_count', '>=', 21);
                } elseif ($st === 'warning') {
                    $q->whereBetween('reused_count', [18, 20]);
                } elseif ($st === 'normal') {
                    $q->where('reused_count', '<', 18);
                }
            });
        }

        if ($request->filled('condition') && $request->condition !== 'all') {
            $cond = $request->condition;
            $query->whereHas('main', fn($q) => $q->where('condition', $cond));
        }

        if ($request->filled('tag_issue') && $request->tag_issue !== 'all') {
            $tag = $request->tag_issue;
            $query->whereHas('main', function ($q) use ($tag) {
                if ($tag === 'missing_barcode') {
                    $q->where('has_barcode', false);
                } elseif ($tag === 'missing_rfid') {
                    $q->where('has_rfid', false);
                } elseif ($tag === 'missing_nti') {
                    $q->where('has_nti', false);
                } elseif ($tag === 'any_missing') {
                    $q->where(fn($sub) => $sub->where('has_barcode', false)->orWhere('has_rfid', false)->orWhere('has_nti', false));
                }
            });
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('id_kempu', 'like', "%{$s}%")
                  ->orWhere('rfid', 'like', "%{$s}%")
                  ->orWhere('no_spb', 'like', "%{$s}%");
            });
        }

        $records = $query->latest('updated_at')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monitoring Kempu');

        // Document Title
        $sheet->setCellValue('A1', 'PT BUMI ALAM SEGAR - WAREHOUSE MANAGEMENT SYSTEM');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E3A8A'));

        $sheet->setCellValue('A2', 'LAPORAN MONITORING & TRACEABILITY KEMPU (IBC TANK)');
        $sheet->getStyle('A2')->getFont()->setSize(12)->setBold(true);

        $filterDesc = 'Total Data: ' . $records->count() . ' Unit | Tanggal Unduh: ' . now()->format('d/m/Y H:i');
        if ($request->filled('location') && $request->location !== 'all') {
            $filterDesc .= ' | Filter Lokasi: ' . $request->location;
        }
        $sheet->setCellValue('A3', $filterDesc);
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Headers (Row 5)
        $headers = [
            'A5' => 'No',
            'B5' => 'ID Kempu',
            'C5' => 'RFID Tag',
            'D5' => 'No SPB',
            'E5' => 'Tgl GR',
            'F5' => 'Lokasi Terkini',
            'G5' => 'Status Operasional',
            'H5' => 'Siklus Reused',
            'I5' => 'Maks Reused',
            'J5' => '% Siklus',
            'K5' => 'Kondisi Fisik',
            'L5' => 'Barcode',
            'M5' => 'RFID',
            'N5' => 'NTI',
            'O5' => 'Aksi Terakhir',
            'P5' => 'Scan Terakhir',
            'Q5' => 'Status Master',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header Styling
        $headerRange = 'A5:Q5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2563EB');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(24);

        $rowNum = 6;
        foreach ($records as $index => $r) {
            $main = $r->main;
            $reused = $main?->reused_count ?? 0;
            $maxReused = $main?->max_reused ?? 21;
            $pctReused = round(($reused / max(1, $maxReused)) * 100) . '%';

            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValueExplicit('B' . $rowNum, $r->id_kempu, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $rowNum, $r->rfid ?: '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $rowNum, $r->no_spb ?: '-', DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $rowNum, $r->gr_date ? Carbon::parse($r->gr_date)->format('d/m/Y') : '-');
            $sheet->setCellValue('F' . $rowNum, $main?->current_location ?? 'WPM');
            $sheet->setCellValue('G' . $rowNum, $main?->current_status ?? '-');
            $sheet->setCellValue('H' . $rowNum, $reused);
            $sheet->setCellValue('I' . $rowNum, $maxReused);
            $sheet->setCellValue('J' . $rowNum, $pctReused);
            $sheet->setCellValue('K' . $rowNum, $main?->condition ?? 'OK');
            $sheet->setCellValue('L' . $rowNum, ($main?->has_barcode ?? true) ? 'OK' : 'RUSAK');
            $sheet->setCellValue('M' . $rowNum, ($main?->has_rfid ?? true) ? 'OK' : 'RUSAK');
            $sheet->setCellValue('N' . $rowNum, ($main?->has_nti ?? true) ? 'OK' : 'RUSAK');
            $sheet->setCellValue('O' . $rowNum, $main?->last_action ?: '-');
            $sheet->setCellValue('P' . $rowNum, $main?->last_scanned_at ? Carbon::parse($main->last_scanned_at)->format('d/m/Y H:i') : '-');
            $sheet->setCellValue('Q' . $rowNum, strtoupper($r->status ?? 'ACTIVE'));

            // Alternating row background
            if ($rowNum % 2 == 0) {
                $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8FAFC');
            }

            $rowNum++;
        }

        $lastRow = $rowNum - 1;

        // Border Styling for table
        if ($lastRow >= 5) {
            $sheet->getStyle('A5:Q' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CBD5E1'));
            $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E6:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H6:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K6:N' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('P6:Q' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // AutoSize columns
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Monitoring_Traceability_Kempu_' . now()->format('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
