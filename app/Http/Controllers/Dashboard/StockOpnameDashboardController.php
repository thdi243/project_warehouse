<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Models WSP
use App\Models\Wsp\StockOpname\WspSoModel;
use App\Models\Wsp\StockOpname\WspSoSummariesModel;
use App\Models\Wsp\StockOpname\WspSohModel;
use App\Models\Wsp\StockOpname\WspSoStatusModel;
use App\Models\Wsp\StockOpname\WspSoTempModel;

// Models WRM
use App\Models\Wrm\StockOpname\WrmSoModel;
use App\Models\Wrm\StockOpname\WrmSoSummariesModel;
use App\Models\Wrm\StockOpname\WrmSohModel;
use App\Models\Wrm\StockOpname\WrmSoStatusModel;
use App\Models\Wrm\StockOpname\WrmSoTempModel;

// Models WPM
use App\Models\Wpm\StockOpname\WpmSoModel;
use App\Models\Wpm\StockOpname\WpmSoSummariesModel;
use App\Models\Wpm\StockOpname\WpmSohModel;
use App\Models\Wpm\StockOpname\WpmSoStatusModel;
use App\Models\Wpm\StockOpname\WpmSoTempModel;

// Models WCP
use App\Models\Wcp\StockOpname\WcpSoModel;
use App\Models\Wcp\StockOpname\WcpSoSummariesModel;
use App\Models\Wcp\StockOpname\WcpSohModel;
use App\Models\Wcp\StockOpname\WcpSoStatusModel;
use App\Models\Wcp\StockOpname\WcpSoTempModel;

// Models WFG
use App\Models\Wfg\stock_opname\WfgSopModel;
use App\Models\Wfg\stock_opname\WfgSopSummariesModel;
use App\Models\Wfg\stock_opname\WfgSopStatusModel;
use App\Models\Wfg\stock_opname\WfgSopTempModel;
use App\Models\Wfg\stock_opname\StockOnHandModel as WfgSohModel;

class StockOpnameDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Resolve the latest date with stock opname data
        $today = Carbon::now()->format('Y-m-d');
        $tglOpname = $request->input('tgl_opname', $today);

        $permissions = [
            'stock-opname-wrm-form',
            'stock-opname-wpm-form',
            'stock-opname-wcp-form',
            'stock-opname-wfg',
            'stock-opname-wsp-form',
        ];

        // 2. Fetch list of PICs/Operators
        $pics = User::select('id', 'nama_lengkap', 'username')
            ->where('jabatan', 'operator')
            ->where(function ($query) use ($permissions) {
                $query->whereHas('permissions', function ($q) use ($permissions) {
                    $q->whereIn('name', $permissions);
                })
                    ->orWhereHas('roles.permissions', function ($q) use ($permissions) {
                        $q->whereIn('name', $permissions);
                    });
            })
            ->get();

        // 3. Fetch list of sections
        $sections = $this->getAllSectionsList();

        return view('dashboard.stock_opname_dashboard', compact('tglOpname', 'pics', 'sections'));
    }

    public function getData(Request $request)
    {
        $tglOpname = $request->input('tgl_opname', Carbon::now()->format('Y-m-d'));
        $sectionFilter = $request->input('section'); // WSP, WRM, WPM, WCP, or WFG_principal
        $picFilter = $request->input('pic'); // user_id
        $statusFilter = $request->input('status'); // belum, progress, selesai
        $barangFilter = $request->input('barang'); // search text for MID or Nama Barang

        // 1. Get dynamically all active sections
        $allSections = $this->getAllSectionsList();
        $sectionTargetCount = count($allSections);

        // 2. Query summaries per warehouse/section on $tglOpname (using robust whereDate)
        $wspSummaries = collect();
        $wrmSummaries = collect();
        $wpmSummaries = collect();
        $wcpSummaries = collect();
        $wfgSummaries = collect();

        // Check if section filter allows loading each area
        if (!$sectionFilter || $sectionFilter === 'all' || $sectionFilter === 'WSP') {
            $wspQuery = WspSoSummariesModel::whereHas('so', function ($q) use ($tglOpname, $picFilter) {
                $q->whereDate('tgl_opname', $tglOpname);
                if ($picFilter && $picFilter !== 'all') {
                    $q->where('user_id', $picFilter);
                }
            });
            if ($barangFilter) {
                $wspQuery->whereHas('barang', function ($q) use ($barangFilter) {
                    $q->where('mid_barang', 'like', "%{$barangFilter}%")
                        ->orWhere('nama_barang', 'like', "%{$barangFilter}%");
                });
            }
            $wspSummaries = $wspQuery->get();
        }

        if (!$sectionFilter || $sectionFilter === 'all' || $sectionFilter === 'WRM') {
            $wrmQuery = WrmSoSummariesModel::whereHas('so', function ($q) use ($tglOpname, $picFilter) {
                $q->whereDate('tgl_opname', $tglOpname);
                if ($picFilter && $picFilter !== 'all') {
                    $q->where('user_id', $picFilter);
                }
            });
            if ($barangFilter) {
                $wrmQuery->whereHas('barang', function ($q) use ($barangFilter) {
                    $q->where('mid', 'like', "%{$barangFilter}%")
                        ->orWhere('nama_barang', 'like', "%{$barangFilter}%");
                });
            }
            $wrmSummaries = $wrmQuery->get();
        }

        if (!$sectionFilter || $sectionFilter === 'all' || $sectionFilter === 'WPM') {
            $wpmQuery = WpmSoSummariesModel::whereHas('so', function ($q) use ($tglOpname, $picFilter) {
                $q->whereDate('tgl_opname', $tglOpname);
                if ($picFilter && $picFilter !== 'all') {
                    $q->where('user_id', $picFilter);
                }
            });
            if ($barangFilter) {
                $wpmQuery->whereHas('barang', function ($q) use ($barangFilter) {
                    $q->where('mid', 'like', "%{$barangFilter}%")
                        ->orWhere('nama_barang', 'like', "%{$barangFilter}%");
                });
            }
            $wpmSummaries = $wpmQuery->get();
        }

        if (!$sectionFilter || $sectionFilter === 'all' || $sectionFilter === 'WCP') {
            $wcpQuery = WcpSoSummariesModel::whereHas('so', function ($q) use ($tglOpname, $picFilter) {
                $q->whereDate('tgl_opname', $tglOpname);
                if ($picFilter && $picFilter !== 'all') {
                    $q->where('user_id', $picFilter);
                }
            });
            if ($barangFilter) {
                $wcpQuery->whereHas('barang', function ($q) use ($barangFilter) {
                    $q->where('mid', 'like', "%{$barangFilter}%")
                        ->orWhere('nama_barang', 'like', "%{$barangFilter}%");
                });
            }
            $wcpSummaries = $wcpQuery->get();
        }

        if (!$sectionFilter || $sectionFilter === 'all' || strpos($sectionFilter, 'WFG_') === 0 || $sectionFilter === 'WFG') {
            $wfgQuery = WfgSopSummariesModel::whereHas('sop', function ($q) use ($tglOpname, $picFilter, $sectionFilter) {
                $q->whereDate('tgl_opname', $tglOpname);
                if ($picFilter && $picFilter !== 'all') {
                    $q->where('user_id', $picFilter);
                }
                if ($sectionFilter && strpos($sectionFilter, 'WFG_') === 0) {
                    $q->where('principal', substr($sectionFilter, 4));
                }
            });
            if ($barangFilter) {
                $wfgQuery->whereHas('barang', function ($q) use ($barangFilter) {
                    $q->where('mid_barang', 'like', "%{$barangFilter}%")
                        ->orWhere('nama_barang', 'like', "%{$barangFilter}%");
                });
            }
            $wfgSummaries = $wfgQuery->get();
        }

        // 3. Determine status of each section on $tglOpname
        $sectionSelesaiCount = 0;
        $ringkasanSections = [];

        foreach ($allSections as $key => $name) {
            $status = 'Belum';
            $diopnameCount = 0;
            $matchCount = 0;
            $selisihCount = 0;
            $qtyLebih = 0;
            $qtyKurang = 0;

            if ($key === 'WSP') {
                $hasDoc = WspSoModel::whereDate('tgl_opname', $tglOpname)->exists();
                $statusRecord = WspSoStatusModel::whereDate('tgl_opname', $tglOpname)->first();
                $hasTemp = WspSoTempModel::whereDate('tgl_opname', $tglOpname)->exists();

                if ($hasDoc || ($statusRecord && $statusRecord->status === 'finished')) {
                    $status = 'finished';
                    $sectionSelesaiCount++;
                } elseif (($statusRecord && $statusRecord->status === 'started') || $hasTemp) {
                    $status = 'started';
                }

                $diopnameCount = $wspSummaries->count();
                $matchCount = $wspSummaries->where('status', 'match')->count();
                $selisihCount = $wspSummaries->where('status', '!=', 'match')->count();
                $qtyLebih = $wspSummaries->where('selisih', '>', 0)->sum('selisih');
                $qtyKurang = $wspSummaries->where('selisih', '<', 0)->sum('selisih');
            } elseif ($key === 'WRM') {
                $hasDoc = WrmSoModel::whereDate('tgl_opname', $tglOpname)->exists();
                $statusRecord = WrmSoStatusModel::whereDate('tgl_opname', $tglOpname)->first();
                $hasTemp = WrmSoTempModel::whereDate('tgl_opname', $tglOpname)->exists();

                if ($hasDoc || ($statusRecord && $statusRecord->status === 'finished')) {
                    $status = 'finished';
                    $sectionSelesaiCount++;
                } elseif (($statusRecord && $statusRecord->status === 'started') || $hasTemp) {
                    $status = 'started';
                }

                $diopnameCount = $wrmSummaries->count();
                $matchCount = $wrmSummaries->where('status', 'match')->count();
                $selisihCount = $wrmSummaries->where('status', '!=', 'match')->count();
                $qtyLebih = $wrmSummaries->where('selisih', '>', 0)->sum('selisih');
                $qtyKurang = $wrmSummaries->where('selisih', '<', 0)->sum('selisih');
            } elseif ($key === 'WPM') {
                $hasDoc = WpmSoModel::whereDate('tgl_opname', $tglOpname)->exists();
                $statusRecord = WpmSoStatusModel::whereDate('tgl_opname', $tglOpname)->first();
                $hasTemp = WpmSoTempModel::whereDate('tgl_opname', $tglOpname)->exists();

                if ($hasDoc || ($statusRecord && $statusRecord->status === 'finished')) {
                    $status = 'finished';
                    $sectionSelesaiCount++;
                } elseif (($statusRecord && $statusRecord->status === 'started') || $hasTemp) {
                    $status = 'started';
                }

                $diopnameCount = $wpmSummaries->count();
                $matchCount = $wpmSummaries->where('status', 'match')->count();
                $selisihCount = $wpmSummaries->where('status', '!=', 'match')->count();
                $qtyLebih = $wpmSummaries->where('selisih', '>', 0)->sum('selisih');
                $qtyKurang = $wpmSummaries->where('selisih', '<', 0)->sum('selisih');
            } elseif ($key === 'WCP') {
                $hasDoc = WcpSoModel::whereDate('tgl_opname', $tglOpname)->exists();
                $statusRecord = WcpSoStatusModel::whereDate('tgl_opname', $tglOpname)->first();
                $hasTemp = WcpSoTempModel::whereDate('tgl_opname', $tglOpname)->exists();

                if ($hasDoc || ($statusRecord && $statusRecord->status === 'finished')) {
                    $status = 'finished';
                    $sectionSelesaiCount++;
                } elseif (($statusRecord && $statusRecord->status === 'started') || $hasTemp) {
                    $status = 'started';
                }

                $diopnameCount = $wcpSummaries->count();
                $matchCount = $wcpSummaries->where('status', 'match')->count();
                $selisihCount = $wcpSummaries->where('status', '!=', 'match')->count();
                $qtyLebih = $wcpSummaries->where('selisih', '>', 0)->sum('selisih');
                $qtyKurang = $wcpSummaries->where('selisih', '<', 0)->sum('selisih');
            } elseif (strpos($key, 'WFG_') === 0) {
                $principalName = substr($key, 4);
                $hasDoc = WfgSopModel::whereDate('tgl_opname', $tglOpname)->where('principal', $principalName)->exists();
                $statusRecord = WfgSopStatusModel::whereDate('tgl_opname', $tglOpname)->where('principal', $principalName)->first();
                $hasTemp = WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', $principalName)->exists();

                if ($hasDoc || ($statusRecord && $statusRecord->status === 'finished')) {
                    $status = 'finished';
                    $sectionSelesaiCount++;
                } elseif (($statusRecord && $statusRecord->status === 'started') || $hasTemp) {
                    $status = 'started';
                }

                $filteredWfg = $wfgSummaries->filter(function ($s) use ($principalName) {
                    return optional($s->sop)->principal === $principalName;
                });

                $diopnameCount = $filteredWfg->count();
                $matchCount = $filteredWfg->where('status', 'match')->count();
                $selisihCount = $filteredWfg->where('status', '!=', 'match')->count();
                $qtyLebih = $filteredWfg->where('selisih', '>', 0)->sum('selisih');
                $qtyKurang = $filteredWfg->where('selisih', '<', 0)->sum('selisih');
            }

            // Calculate accuracy rate: 0.00% if not opnamed yet
            $accuracyRate = $diopnameCount > 0 ? round(($matchCount / $diopnameCount) * 100, 2) : 0.00;

            $ringkasanSections[] = [
                'key' => $key,
                'name' => $name,
                'diopname' => $diopnameCount,
                'match' => $matchCount,
                'selisih' => $selisihCount,
                'accuracy' => $accuracyRate,
                'qty_lebih' => $qtyLebih,
                'qty_kurang' => $qtyKurang,
                'status' => $status
            ];
        }

        // Apply filters to sections table & target counts
        if ($sectionFilter && $sectionFilter !== 'all') {
            $ringkasanSections = collect($ringkasanSections)->where('key', $sectionFilter)->values()->all();
            $sectionTargetCount = 1;
            $sectionSelesaiCount = collect($ringkasanSections)->where('status', 'finished')->count();
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $ringkasanSections = collect($ringkasanSections)->filter(function ($s) use ($statusFilter) {
                $statusLower = strtolower($s['status']);
                if ($statusFilter === 'belum' || $statusFilter === 'idle') {
                    return str_contains($statusLower, 'belum') || str_contains($statusLower, 'idle');
                } elseif ($statusFilter === 'progress' || $statusFilter === 'started') {
                    return str_contains($statusLower, 'proses') || str_contains($statusLower, 'started');
                } elseif ($statusFilter === 'selesai' || $statusFilter === 'finished') {
                    return str_contains($statusLower, 'selesai') || str_contains($statusLower, 'finished');
                }
                return true;
            })->values()->all();
        }

        // 4. Calculate total KPIs across the currently filtered data
        $itemDiopname = collect($ringkasanSections)->sum('diopname');
        $itemSelisih = collect($ringkasanSections)->sum('selisih');
        $totalMatch = collect($ringkasanSections)->sum('match');
        $stockAccuracy = $itemDiopname > 0 ? round(($totalMatch / $itemDiopname) * 100, 2) : 0.00;

        $totalQtyLebih = collect($ringkasanSections)->sum('qty_lebih');
        $totalQtyKurang = collect($ringkasanSections)->sum('qty_kurang');

        $progressPct = $sectionTargetCount > 0 ? round(($sectionSelesaiCount / $sectionTargetCount) * 100, 1) : 0;

        // 5. Gather Top 10 Selisih Terbesar
        $allSummaries = collect();

        foreach ($wspSummaries as $s) {
            $allSummaries->push($this->formatSummaryRow('WSP', $s));
        }
        foreach ($wrmSummaries as $s) {
            $allSummaries->push($this->formatSummaryRow('WRM', $s));
        }
        foreach ($wpmSummaries as $s) {
            $allSummaries->push($this->formatSummaryRow('WPM', $s));
        }
        foreach ($wcpSummaries as $s) {
            $allSummaries->push($this->formatSummaryRow('WCP', $s));
        }
        foreach ($wfgSummaries as $s) {
            $allSummaries->push($this->formatSummaryRow('WFG - ' . optional($s->sop)->principal, $s));
        }

        $allowedSectionNames = collect($ringkasanSections)->pluck('name')->toArray();
        $top10 = $allSummaries->filter(function ($s) use ($allowedSectionNames) {
            foreach ($allowedSectionNames as $allowedName) {
                if ($s['section'] === $allowedName || strpos($s['section'], $allowedName) !== false || strpos($allowedName, $s['section']) !== false) {
                    return true;
                }
            }
            return false;
        })->filter(function ($s) {
            return $s['selisih'] != 0;
        })->sortByDesc(function ($s) {
            return abs($s['selisih']);
        })->take(10)->values()->all();

        // 6. Accuracy Harian Trend Chart (Last 15 days ending in $tglOpname)
        $trendCategories = [];
        $trendAccuracy = [];

        $endDateObj = Carbon::parse($tglOpname);
        for ($i = 14; $i >= 0; $i--) {
            $dateObj = (clone $endDateObj)->subDays($i);
            $dStr = $dateObj->format('Y-m-d');
            $trendCategories[] = $dateObj->translatedFormat('d M');

            // Fetch total counted & matched items for this date to calculate accuracy
            $diopname = 0;
            $matched = 0;

            // Check which sections are allowed/present in filtered $ringkasanSections
            $allowedKeys = collect($ringkasanSections)->pluck('key')->toArray();

            // WSP
            if (in_array('WSP', $allowedKeys) || empty($sectionFilter) || $sectionFilter === 'all') {
                $wspSo = WspSoModel::whereDate('tgl_opname', $dStr);
                if ($picFilter && $picFilter !== 'all') {
                    $wspSo->where('user_id', $picFilter);
                }
                $soIds = $wspSo->pluck('id');
                $diopname += WspSoSummariesModel::whereIn('so_id', $soIds)->count();
                $matched += WspSoSummariesModel::whereIn('so_id', $soIds)->where('status', 'match')->count();
            }

            // WRM
            if (in_array('WRM', $allowedKeys) || empty($sectionFilter) || $sectionFilter === 'all') {
                $wrmSo = WrmSoModel::whereDate('tgl_opname', $dStr);
                if ($picFilter && $picFilter !== 'all') {
                    $wrmSo->where('user_id', $picFilter);
                }
                $soIds = $wrmSo->pluck('id');
                $diopname += WrmSoSummariesModel::whereIn('so_id', $soIds)->count();
                $matched += WrmSoSummariesModel::whereIn('so_id', $soIds)->where('status', 'match')->count();
            }

            // WPM
            if (in_array('WPM', $allowedKeys) || empty($sectionFilter) || $sectionFilter === 'all') {
                $wpmSo = WpmSoModel::whereDate('tgl_opname', $dStr);
                if ($picFilter && $picFilter !== 'all') {
                    $wpmSo->where('user_id', $picFilter);
                }
                $soIds = $wpmSo->pluck('id');
                $diopname += WpmSoSummariesModel::whereIn('so_id', $soIds)->count();
                $matched += WpmSoSummariesModel::whereIn('so_id', $soIds)->where('status', 'match')->count();
            }

            // WCP
            if (in_array('WCP', $allowedKeys) || empty($sectionFilter) || $sectionFilter === 'all') {
                $wcpSo = WcpSoModel::whereDate('tgl_opname', $dStr);
                if ($picFilter && $picFilter !== 'all') {
                    $wcpSo->where('user_id', $picFilter);
                }
                $soIds = $wcpSo->pluck('id');
                $diopname += WcpSoSummariesModel::whereIn('so_id', $soIds)->count();
                $matched += WcpSoSummariesModel::whereIn('so_id', $soIds)->where('status', 'match')->count();
            }

            // WFG
            $wfgPrincipalsInFilter = collect($allowedKeys)->filter(function ($k) {
                return strpos($k, 'WFG_') === 0;
            })->map(function ($k) {
                return substr($k, 4);
            })->toArray();

            if (!empty($wfgPrincipalsInFilter) || in_array('WFG', $allowedKeys) || empty($sectionFilter) || $sectionFilter === 'all') {
                $wfgSop = WfgSopModel::whereDate('tgl_opname', $dStr);
                if ($picFilter && $picFilter !== 'all') {
                    $wfgSop->where('user_id', $picFilter);
                }
                if (!empty($wfgPrincipalsInFilter)) {
                    $wfgSop->whereIn('principal', $wfgPrincipalsInFilter);
                }
                $sopIds = $wfgSop->pluck('id');
                $diopname += WfgSopSummariesModel::whereIn('sop_id', $sopIds)->count();
                $matched += WfgSopSummariesModel::whereIn('sop_id', $sopIds)->where('status', 'match')->count();
            }

            $acc = $diopname > 0 ? round(($matched / $diopname) * 100, 2) : 0.00;
            $trendAccuracy[] = $acc;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'kpis' => [
                    'tanggal_formatted' => Carbon::parse($tglOpname)->translatedFormat('d F Y'),
                    'section_target' => $sectionTargetCount,
                    'section_selesai' => $sectionSelesaiCount,
                    'progress_pct' => $progressPct,
                    'item_diopname' => number_format($itemDiopname, 0, ',', '.'),
                    'item_selisih' => number_format($itemSelisih, 0, ',', '.'),
                    'stock_accuracy' => $stockAccuracy,
                    'selisih_qty_pos' => $totalQtyLebih > 0 ? '+' . number_format($totalQtyLebih, 0, ',', '.') : '0',
                    'selisih_qty_neg' => $totalQtyKurang < 0 ? number_format($totalQtyKurang, 0, ',', '.') : '0',
                ],
                'ringkasanSections' => $ringkasanSections,
                'top10' => $top10,
                'charts' => [
                    'accuracy' => [
                        'categories' => $trendCategories,
                        'data' => $trendAccuracy
                    ]
                ]
            ]
        ]);
    }

    private function getAllSectionsList()
    {
        $sections = [
            'WSP' => 'Warehouse Sparepart (WSP)',
            'WRM' => 'Warehouse Raw Material (WRM)',
            'WPM' => 'Warehouse Packaging Material (WPM)',
            'WCP' => 'Warehouse Co Product (WCP)',
        ];

        try {
            $wfgPrincipals = DB::table('wfg_barang')
                ->whereNotNull('principal')
                ->where('principal', '!=', '')
                ->distinct()
                ->pluck('principal')
                ->toArray();

            foreach ($wfgPrincipals as $p) {
                $sections['WFG_' . $p] = 'WFG - ' . $p;
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return $sections;
    }

    private function formatSummaryRow($sectionName, $s)
    {
        return [
            'section' => $sectionName,
            'mid' => optional($s->barang)->mid_barang ?? (optional($s->barang)->mid ?? 'N/A'),
            'name' => optional($s->barang)->nama_barang ?? 'N/A',
            'qty_sistem' => (int) $s->qty_sistem,
            'qty_fisik' => (int) $s->qty_fisik,
            'selisih' => (int) $s->selisih,
            'keterangan' => $s->keterangan ?? '-'
        ];
    }
}
