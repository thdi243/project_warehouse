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
use App\Models\Wsp\StockOpname\WspSoDetailModel;

// Models WRM
use App\Models\Wrm\StockOpname\WrmSoModel;
use App\Models\Wrm\StockOpname\WrmSoSummariesModel;
use App\Models\Wrm\StockOpname\WrmSohModel;
use App\Models\Wrm\StockOpname\WrmSoStatusModel;
use App\Models\Wrm\StockOpname\WrmSoTempModel;
use App\Models\Wrm\StockOpname\WrmSoDetailModel;

// Models WPM
use App\Models\Wpm\StockOpname\WpmSoModel;
use App\Models\Wpm\StockOpname\WpmSoSummariesModel;
use App\Models\Wpm\StockOpname\WpmSohModel;
use App\Models\Wpm\StockOpname\WpmSoStatusModel;
use App\Models\Wpm\StockOpname\WpmSoTempModel;
use App\Models\Wpm\StockOpname\WpmSoDetailModel;

// Models WCP
use App\Models\Wcp\StockOpname\WcpSoModel;
use App\Models\Wcp\StockOpname\WcpSoSummariesModel;
use App\Models\Wcp\StockOpname\WcpSohModel;
use App\Models\Wcp\StockOpname\WcpSoStatusModel;
use App\Models\Wcp\StockOpname\WcpSoTempModel;
use App\Models\Wcp\StockOpname\WcpSoDetailModel;

// Models WFG
use App\Models\Wfg\stock_opname\WfgSopModel;
use App\Models\Wfg\stock_opname\WfgSopSummariesModel;
use App\Models\Wfg\stock_opname\WfgSopStatusModel;
use App\Models\Wfg\stock_opname\WfgSopTempModel;
use App\Models\Wfg\stock_opname\WfgSopDetailModel;
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
        $sectionFilter = $request->input('section'); // WSP, WRM, WPM, WCP, WFG_BAS, WFG_SMU
        $picFilter = $request->input('pic'); // user_id
        $statusFilter = $request->input('status'); // belum, progress, selesai
        $barangFilter = $request->input('barang'); // search text for MID or Nama Barang

        // 1. Get dynamically all active sections
        $allSections = $this->getAllSectionsList();
        $sectionTargetCount = count($allSections);

        // 2. Query summaries per warehouse/section on $tglOpname (using robust whereDate and filtered by cycle_count)
        $wspSummaries = collect();
        $wrmSummaries = collect();
        $wpmSummaries = collect();
        $wcpSummaries = collect();
        $wfgSummaries = collect();

        // Check if section filter allows loading each area
        if (!$sectionFilter || $sectionFilter === 'all' || $sectionFilter === 'WSP') {
            $wspQuery = WspSoSummariesModel::whereHas('so', function ($q) use ($tglOpname, $picFilter) {
                $q->whereDate('tgl_opname', $tglOpname)
                  ->where('jenis_so', 'cycle_count');
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
                $q->whereDate('tgl_opname', $tglOpname)
                  ->where('jenis_so', 'cycle_count');
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
                $q->whereDate('tgl_opname', $tglOpname)
                  ->where('jenis_so', 'cycle_count');
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
                $q->whereDate('tgl_opname', $tglOpname)
                  ->where('jenis_so', 'cycle_count');
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
                $q->whereDate('tgl_opname', $tglOpname)
                  ->where('jenis_so', 'cycle_count');
                if ($picFilter && $picFilter !== 'all') {
                    $q->where('user_id', $picFilter);
                }

                if ($sectionFilter) {
                    if ($sectionFilter === 'WFG_BAS') {
                        $q->where('principal', 'BAS');
                    } elseif ($sectionFilter === 'WFG_SMU') {
                        $q->where('principal', '!=', 'BAS');
                    }
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

            // SOH Details & Metadata
            $qtyUnrest = 0;
            $qtyQi = 0;
            $qtyBlock = 0;
            $batches = 0;
            $pallets = 0;
            $workTime = 'Belum Mulai';

            $startTime = null;
            $endTime = null;

            if ($key === 'WSP') {
                $hasDoc = WspSoModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->exists();
                $statusRecord = WspSoStatusModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->first();
                $hasTemp = WspSoTempModel::whereDate('tgl_opname', $tglOpname)
                    ->whereHas('soh', function ($q) {
                        $q->where('jenis_so', 'cycle_count');
                    })
                    ->exists();

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

                $qtyUnrest = (int) WspSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_unrest');
                $qtyQi = (int) WspSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_qi');
                $qtyBlock = (int) WspSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_block');

                $batches = null;
                $pallets = null;

                $wspSoIds = WspSoModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->pluck('id');
                if ($wspSoIds->isNotEmpty()) {
                    $startTime = WspSoDetailModel::whereIn('so_id', $wspSoIds)->min('created_at');
                    $endTime = WspSoDetailModel::whereIn('so_id', $wspSoIds)->max('created_at');
                }
                if (!$startTime) {
                    $startTime = WspSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->min('created_at');
                    $endTime = WspSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->max('created_at');
                }
            } elseif ($key === 'WRM') {
                $hasDoc = WrmSoModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->exists();
                $statusRecord = WrmSoStatusModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->first();
                $hasTemp = WrmSoTempModel::whereDate('tgl_opname', $tglOpname)
                    ->whereHas('soh', function ($q) {
                        $q->where('jenis_so', 'cycle_count');
                    })
                    ->exists();

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

                $qtyUnrest = (int) WrmSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_unrest');
                $qtyQi = (int) WrmSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_qi');
                $qtyBlock = (int) WrmSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_block');

                $wrmSoIdsForMeta = WrmSoModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->pluck('id');
                $batches = 0;
                $pallets = 0;
                if ($wrmSoIdsForMeta->isNotEmpty()) {
                    $batches = WrmSoDetailModel::whereIn('so_id', $wrmSoIdsForMeta)->whereNotNull('no_spb')->where('no_spb', '!=', '')->distinct('no_spb')->count('no_spb');
                    $pallets = WrmSoDetailModel::whereIn('so_id', $wrmSoIdsForMeta)->count();
                }
                if ($batches === 0 && $pallets === 0) {
                    $batches = WrmSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->whereNotNull('no_spb')->where('no_spb', '!=', '')->distinct('no_spb')->count('no_spb');
                    $pallets = WrmSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->count();
                }

                $wrmSoIds = WrmSoModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->pluck('id');
                if ($wrmSoIds->isNotEmpty()) {
                    $startTime = WrmSoDetailModel::whereIn('so_id', $wrmSoIds)->min('created_at');
                    $endTime = WrmSoDetailModel::whereIn('so_id', $wrmSoIds)->max('created_at');
                }
                if (!$startTime) {
                    $startTime = WrmSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->min('created_at');
                    $endTime = WrmSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->max('created_at');
                }
            } elseif ($key === 'WPM') {
                $hasDoc = WpmSoModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->exists();
                $statusRecord = WpmSoStatusModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->first();
                $hasTemp = WpmSoTempModel::whereDate('tgl_opname', $tglOpname)
                    ->whereHas('soh', function ($q) {
                        $q->where('jenis_so', 'cycle_count');
                    })
                    ->exists();

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

                $qtyUnrest = (int) WpmSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_unrest');
                $qtyQi = (int) WpmSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_qi');
                $qtyBlock = (int) WpmSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_block');

                $batches = null;
                $pallets = null;

                $wpmSoIds = WpmSoModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->pluck('id');
                if ($wpmSoIds->isNotEmpty()) {
                    $startTime = WpmSoDetailModel::whereIn('so_id', $wpmSoIds)->min('created_at');
                    $endTime = WpmSoDetailModel::whereIn('so_id', $wpmSoIds)->max('created_at');
                }
                if (!$startTime) {
                    $startTime = WpmSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->min('created_at');
                    $endTime = WpmSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->max('created_at');
                }
            } elseif ($key === 'WCP') {
                $hasDoc = WcpSoModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->exists();
                $statusRecord = WcpSoStatusModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->first();
                $hasTemp = WcpSoTempModel::whereDate('tgl_opname', $tglOpname)
                    ->whereHas('soh', function ($q) {
                        $q->where('jenis_so', 'cycle_count');
                    })
                    ->exists();

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

                $qtyUnrest = (int) WcpSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_unrest');
                $qtyQi = (int) WcpSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_qi');
                $qtyBlock = (int) WcpSohModel::whereDate('created_at', $tglOpname)->where('jenis_so', 'cycle_count')->sum('qty_block');

                $batches = null;
                $pallets = null;

                $wcpSoIds = WcpSoModel::whereDate('tgl_opname', $tglOpname)->where('jenis_so', 'cycle_count')->pluck('id');
                if ($wcpSoIds->isNotEmpty()) {
                    $startTime = WcpSoDetailModel::whereIn('so_id', $wcpSoIds)->min('created_at');
                    $endTime = WcpSoDetailModel::whereIn('so_id', $wcpSoIds)->max('created_at');
                }
                if (!$startTime) {
                    $startTime = WcpSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->min('created_at');
                    $endTime = WcpSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->max('created_at');
                }
            } elseif ($key === 'WFG_BAS') {
                $hasDoc = WfgSopModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')->where('jenis_so', 'cycle_count')->exists();
                $statusRecord = WfgSopStatusModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')->where('jenis_so', 'cycle_count')->first();
                $hasTemp = WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')
                    ->whereHas('soh', function ($q) {
                        $q->where('jenis_so', 'cycle_count');
                    })
                    ->exists();

                if ($hasDoc || ($statusRecord && $statusRecord->status === 'finished')) {
                    $status = 'finished';
                    $sectionSelesaiCount++;
                } elseif (($statusRecord && $statusRecord->status === 'started') || $hasTemp) {
                    $status = 'started';
                }

                $filteredWfg = $wfgSummaries->filter(function ($s) {
                    return optional($s->sop)->principal === 'BAS';
                });

                $diopnameCount = $filteredWfg->count();
                $matchCount = $filteredWfg->where('status', 'match')->count();
                $selisihCount = $filteredWfg->where('status', '!=', 'match')->count();
                $qtyLebih = $filteredWfg->where('selisih', '>', 0)->sum('selisih');
                $qtyKurang = $filteredWfg->where('selisih', '<', 0)->sum('selisih');

                $qtyUnrest = (int) WfgSohModel::whereDate('created_at', $tglOpname)->where('principal', 'BAS')->where('jenis_so', 'cycle_count')->sum('qty_unrest');
                $qtyQi = (int) WfgSohModel::whereDate('created_at', $tglOpname)->where('principal', 'BAS')->where('jenis_so', 'cycle_count')->sum('qty_qi');
                $qtyBlock = (int) WfgSohModel::whereDate('created_at', $tglOpname)->where('principal', 'BAS')->where('jenis_so', 'cycle_count')->sum('qty_block');

                $batches = null;
                $pallets = 0;
                $wfgBasSopIdsForMeta = WfgSopModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')->where('jenis_so', 'cycle_count')->pluck('id');
                if ($wfgBasSopIdsForMeta->isNotEmpty()) {
                    $fullPallets = (int) WfgSopDetailModel::whereIn('sop_id', $wfgBasSopIdsForMeta)->sum('qty_full');
                    $recehPallets = (int) WfgSopDetailModel::whereIn('sop_id', $wfgBasSopIdsForMeta)->where('qty_receh', '>', 0)->count();
                    $pallets = $fullPallets + $recehPallets;
                }
                if ($pallets === 0) {
                    $fullPallets = (int) WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->sum('qty_full');
                    $recehPallets = (int) WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->where('qty_receh', '>', 0)->count();
                    $pallets = $fullPallets + $recehPallets;
                }

                $wfgBasSopIds = WfgSopModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')->where('jenis_so', 'cycle_count')->pluck('id');
                if ($wfgBasSopIds->isNotEmpty()) {
                    $startTime = WfgSopDetailModel::whereIn('sop_id', $wfgBasSopIds)->min('created_at');
                    $endTime = WfgSopDetailModel::whereIn('sop_id', $wfgBasSopIds)->max('created_at');
                }
                if (!$startTime) {
                    $startTime = WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->min('created_at');
                    $endTime = WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->max('created_at');
                }
            } elseif ($key === 'WFG_SMU') {
                $hasFinishedSMU = WfgSopModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')->where('jenis_so', 'cycle_count')->exists()
                    || WfgSopStatusModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')->where('status', 'finished')->where('jenis_so', 'cycle_count')->exists();

                $hasStartedSMU = WfgSopStatusModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')->where('status', 'started')->where('jenis_so', 'cycle_count')->exists()
                    || WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->exists();

                if ($hasFinishedSMU && !$hasStartedSMU) {
                    $status = 'finished';
                    $sectionSelesaiCount++;
                } elseif ($hasStartedSMU || $hasFinishedSMU) {
                    $status = 'started';
                }

                $filteredWfg = $wfgSummaries->filter(function ($s) {
                    return optional($s->sop)->principal !== 'BAS';
                });

                $diopnameCount = $filteredWfg->count();
                $matchCount = $filteredWfg->where('status', 'match')->count();
                $selisihCount = $filteredWfg->where('status', '!=', 'match')->count();
                $qtyLebih = $filteredWfg->where('selisih', '>', 0)->sum('selisih');
                $qtyKurang = $filteredWfg->where('selisih', '<', 0)->sum('selisih');

                $qtyUnrest = (int) WfgSohModel::whereDate('created_at', $tglOpname)->where('principal', '!=', 'BAS')->where('jenis_so', 'cycle_count')->sum('qty_unrest');
                $qtyQi = (int) WfgSohModel::whereDate('created_at', $tglOpname)->where('principal', '!=', 'BAS')->where('jenis_so', 'cycle_count')->sum('qty_qi');
                $qtyBlock = (int) WfgSohModel::whereDate('created_at', $tglOpname)->where('principal', '!=', 'BAS')->where('jenis_so', 'cycle_count')->sum('qty_block');

                $batches = null;
                $pallets = 0;
                $wfgSmuSopIdsForMeta = WfgSopModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')->where('jenis_so', 'cycle_count')->pluck('id');
                if ($wfgSmuSopIdsForMeta->isNotEmpty()) {
                    $fullPallets = (int) WfgSopDetailModel::whereIn('sop_id', $wfgSmuSopIdsForMeta)->sum('qty_full');
                    $recehPallets = (int) WfgSopDetailModel::whereIn('sop_id', $wfgSmuSopIdsForMeta)->where('qty_receh', '>', 0)->count();
                    $pallets = $fullPallets + $recehPallets;
                }
                if ($pallets === 0) {
                    $fullPallets = (int) WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->sum('qty_full');
                    $recehPallets = (int) WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->where('qty_receh', '>', 0)->count();
                    $pallets = $fullPallets + $recehPallets;
                }

                $wfgSmuSopIds = WfgSopModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')->where('jenis_so', 'cycle_count')->pluck('id');
                if ($wfgSmuSopIds->isNotEmpty()) {
                    $startTime = WfgSopDetailModel::whereIn('sop_id', $wfgSmuSopIds)->min('created_at');
                    $endTime = WfgSopDetailModel::whereIn('sop_id', $wfgSmuSopIds)->max('created_at');
                }
                if (!$startTime) {
                    $startTime = WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->min('created_at');
                    $endTime = WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->max('created_at');
                }
            }

            if ($startTime) {
                $startFormatted = Carbon::parse($startTime)->format('H:i');
                if ($status === 'finished') {
                    $endFormatted = $endTime ? Carbon::parse($endTime)->format('H:i') : '17:00';
                    $workTime = "{$startFormatted} - {$endFormatted}";
                } elseif ($status === 'started') {
                    $endFormatted = $endTime ? Carbon::parse($endTime)->format('H:i') : '...';
                    $workTime = "{$startFormatted} - {$endFormatted} (Proses)";
                } else {
                    $workTime = 'Belum Mulai';
                }
            } else {
                $workTime = 'Belum Mulai';
            }

            // Fallback for batches and pallets if 0 but there is diopnameCount
            if ($diopnameCount > 0) {
                if ($batches !== null && $batches === 0) $batches = ceil($diopnameCount / 30);
                if ($pallets !== null && $pallets === 0) $pallets = ceil($diopnameCount / 10);
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
                'qty_unrest' => $qtyUnrest,
                'qty_qi' => $qtyQi,
                'qty_block' => $qtyBlock,
                'batches' => $batches,
                'pallets' => $pallets,
                'work_time' => $workTime,
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
                if ($statusFilter === 'idle') {
                    return $s['status'] === 'Belum';
                } elseif ($statusFilter === 'started') {
                    return $s['status'] === 'started';
                } elseif ($statusFilter === 'finished') {
                    return $s['status'] === 'finished';
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
            $principalName = optional($s->sop)->principal;
            $sectionLabel = ($principalName === 'BAS') ? 'WFG - BAS' : 'WFG - SMU';
            $allSummaries->push($this->formatSummaryRow($sectionLabel, $s));
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
        $trendSections = [
            'WSP' => [],
            'WRM' => [],
            'WPM' => [],
            'WCP' => [],
            'WFG_BAS' => [],
            'WFG_SMU' => []
        ];

        $endDateObj = Carbon::parse($tglOpname);
        for ($i = 14; $i >= 0; $i--) {
            $dateObj = (clone $endDateObj)->subDays($i);
            $dStr = $dateObj->format('Y-m-d');
            $trendCategories[] = $dateObj->translatedFormat('d M');

            // 1. WSP
            $wspSo = WspSoModel::whereDate('tgl_opname', $dStr)->where('jenis_so', 'cycle_count');
            if ($picFilter && $picFilter !== 'all') {
                $wspSo->where('user_id', $picFilter);
            }
            $wspSoIds = $wspSo->pluck('id');
            $wspDi = WspSoSummariesModel::whereIn('so_id', $wspSoIds)->count();
            $wspMa = WspSoSummariesModel::whereIn('so_id', $wspSoIds)->where('status', 'match')->count();
            $trendSections['WSP'][] = [
                'accuracy' => $wspDi > 0 ? round(($wspMa / $wspDi) * 100, 2) : 0.00,
                'diopname' => $wspDi,
                'selisih' => $wspDi - $wspMa
            ];

            // 2. WRM
            $wrmSo = WrmSoModel::whereDate('tgl_opname', $dStr)->where('jenis_so', 'cycle_count');
            if ($picFilter && $picFilter !== 'all') {
                $wrmSo->where('user_id', $picFilter);
            }
            $wrmSoIds = $wrmSo->pluck('id');
            $wrmDi = WrmSoSummariesModel::whereIn('so_id', $wrmSoIds)->count();
            $wrmMa = WrmSoSummariesModel::whereIn('so_id', $wrmSoIds)->where('status', 'match')->count();
            $trendSections['WRM'][] = [
                'accuracy' => $wrmDi > 0 ? round(($wrmMa / $wrmDi) * 100, 2) : 0.00,
                'diopname' => $wrmDi,
                'selisih' => $wrmDi - $wrmMa
            ];

            // 3. WPM
            $wpmSo = WpmSoModel::whereDate('tgl_opname', $dStr)->where('jenis_so', 'cycle_count');
            if ($picFilter && $picFilter !== 'all') {
                $wpmSo->where('user_id', $picFilter);
            }
            $wpmSoIds = $wpmSo->pluck('id');
            $wpmDi = WpmSoSummariesModel::whereIn('so_id', $wpmSoIds)->count();
            $wpmMa = WpmSoSummariesModel::whereIn('so_id', $wpmSoIds)->where('status', 'match')->count();
            $trendSections['WPM'][] = [
                'accuracy' => $wpmDi > 0 ? round(($wpmMa / $wpmDi) * 100, 2) : 0.00,
                'diopname' => $wpmDi,
                'selisih' => $wpmDi - $wpmMa
            ];

            // 4. WCP
            $wcpSo = WcpSoModel::whereDate('tgl_opname', $dStr)->where('jenis_so', 'cycle_count');
            if ($picFilter && $picFilter !== 'all') {
                $wcpSo->where('user_id', $picFilter);
            }
            $wcpSoIds = $wcpSo->pluck('id');
            $wcpDi = WcpSoSummariesModel::whereIn('so_id', $wcpSoIds)->count();
            $wcpMa = WcpSoSummariesModel::whereIn('so_id', $wcpSoIds)->where('status', 'match')->count();
            $trendSections['WCP'][] = [
                'accuracy' => $wcpDi > 0 ? round(($wcpMa / $wcpDi) * 100, 2) : 0.00,
                'diopname' => $wcpDi,
                'selisih' => $wcpDi - $wcpMa
            ];

            // 5. WFG BAS
            $wfgBasSop = WfgSopModel::whereDate('tgl_opname', $dStr)->where('principal', 'BAS')->where('jenis_so', 'cycle_count');
            if ($picFilter && $picFilter !== 'all') {
                $wfgBasSop->where('user_id', $picFilter);
            }
            $wfgBasSopIds = $wfgBasSop->pluck('id');
            $wfgBasDi = WfgSopSummariesModel::whereIn('sop_id', $wfgBasSopIds)->count();
            $wfgBasMa = WfgSopSummariesModel::whereIn('sop_id', $wfgBasSopIds)->where('status', 'match')->count();
            $trendSections['WFG_BAS'][] = [
                'accuracy' => $wfgBasDi > 0 ? round(($wfgBasMa / $wfgBasDi) * 100, 2) : 0.00,
                'diopname' => $wfgBasDi,
                'selisih' => $wfgBasDi - $wfgBasMa
            ];

            // 6. WFG SMU
            $wfgSmuSop = WfgSopModel::whereDate('tgl_opname', $dStr)->where('principal', '!=', 'BAS')->where('jenis_so', 'cycle_count');
            if ($picFilter && $picFilter !== 'all') {
                $wfgSmuSop->where('user_id', $picFilter);
            }
            $wfgSmuSopIds = $wfgSmuSop->pluck('id');
            $wfgSmuDi = WfgSopSummariesModel::whereIn('sop_id', $wfgSmuSopIds)->count();
            $wfgSmuMa = WfgSopSummariesModel::whereIn('sop_id', $wfgSmuSopIds)->where('status', 'match')->count();
            $trendSections['WFG_SMU'][] = [
                'accuracy' => $wfgSmuDi > 0 ? round(($wfgSmuMa / $wfgSmuDi) * 100, 2) : 0.00,
                'diopname' => $wfgSmuDi,
                'selisih' => $wfgSmuDi - $wfgSmuMa
            ];

            // Akurasi Total
            $totalDiopname = $wspDi + $wrmDi + $wpmDi + $wcpDi + $wfgBasDi + $wfgSmuDi;
            $totalMatched = $wspMa + $wrmMa + $wpmMa + $wcpMa + $wfgBasMa + $wfgSmuMa;
            $trendAccuracy[] = $totalDiopname > 0 ? round(($totalMatched / $totalDiopname) * 100, 2) : 0.00;
        }

        // 7. Get Approval Tracking for each section
        $approvalTracking = [];

        foreach ($allSections as $key => $name) {
            $soDoc = null;

            if ($key === 'WSP') {
                $soDoc = WspSoModel::with(['user', 'approvals.approver'])
                    ->whereDate('tgl_opname', $tglOpname)
                    ->where('jenis_so', 'cycle_count')
                    ->first();
            } elseif ($key === 'WRM') {
                $soDoc = WrmSoModel::with(['user', 'approvals.approver'])
                    ->whereDate('tgl_opname', $tglOpname)
                    ->where('jenis_so', 'cycle_count')
                    ->first();
            } elseif ($key === 'WPM') {
                $soDoc = WpmSoModel::with(['user', 'approvals.approver'])
                    ->whereDate('tgl_opname', $tglOpname)
                    ->where('jenis_so', 'cycle_count')
                    ->first();
            } elseif ($key === 'WCP') {
                $soDoc = WcpSoModel::with(['user', 'approvals.approver'])
                    ->whereDate('tgl_opname', $tglOpname)
                    ->where('jenis_so', 'cycle_count')
                    ->first();
            } elseif ($key === 'WFG_BAS') {
                $soDoc = WfgSopModel::with(['user', 'approvals.approver'])
                    ->whereDate('tgl_opname', $tglOpname)
                    ->where('principal', 'BAS')
                    ->where('jenis_so', 'cycle_count')
                    ->first();
            } elseif ($key === 'WFG_SMU') {
                $soDoc = WfgSopModel::with(['user', 'approvals.approver'])
                    ->whereDate('tgl_opname', $tglOpname)
                    ->where('principal', '!=', 'BAS')
                    ->where('jenis_so', 'cycle_count')
                    ->first();
            }

            $statusApproval = 'Belum Mulai';
            $noDoc = '-';
            $operatorName = '-';
            $approvedBy = [];
            $pendingBy = [];
            $rejectedBy = [];

            if ($soDoc) {
                $noDoc = $soDoc->no_doc ?? '-';
                $operatorName = $soDoc->user->nama_lengkap ?? $soDoc->user->username ?? '-';

                $dbStatus = strtolower($soDoc->status ?? '');
                if ($dbStatus === 'approved') {
                    $statusApproval = 'Approved';
                } elseif ($dbStatus === 'rejected') {
                    $statusApproval = 'Rejected';
                } elseif ($dbStatus === 'pending' || $dbStatus === 'waiting') {
                    $statusApproval = 'Pending';
                } else {
                    $statusApproval = ucfirst($dbStatus ?: 'Draft');
                }

                foreach ($soDoc->approvals as $app) {
                    $appUser = $app->approver->nama_lengkap ?? $app->approver->username ?? 'Unknown';
                    $appStatus = strtolower($app->status ?? '');

                    if ($appStatus === 'approved') {
                        $approvedBy[] = $appUser;
                    } elseif ($appStatus === 'rejected') {
                        $rejectedBy[] = $appUser . ($app->catatan ? " ({$app->catatan})" : "");
                    } else {
                        $pendingBy[] = $appUser;
                    }
                }
            } else {
                $hasStarted = false;
                if ($key === 'WSP') {
                    $hasStarted = WspSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->exists()
                        || WspSoStatusModel::whereDate('tgl_opname', $tglOpname)->where('status', 'started')->where('jenis_so', 'cycle_count')->exists();
                } elseif ($key === 'WRM') {
                    $hasStarted = WrmSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->exists()
                        || WrmSoStatusModel::whereDate('tgl_opname', $tglOpname)->where('status', 'started')->where('jenis_so', 'cycle_count')->exists();
                } elseif ($key === 'WPM') {
                    $hasStarted = WpmSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->exists()
                        || WpmSoStatusModel::whereDate('tgl_opname', $tglOpname)->where('status', 'started')->where('jenis_so', 'cycle_count')->exists();
                } elseif ($key === 'WCP') {
                    $hasStarted = WcpSoTempModel::whereDate('tgl_opname', $tglOpname)
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->exists()
                        || WcpSoStatusModel::whereDate('tgl_opname', $tglOpname)->where('status', 'started')->where('jenis_so', 'cycle_count')->exists();
                } elseif ($key === 'WFG_BAS') {
                    $hasStarted = WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->exists()
                        || WfgSopStatusModel::whereDate('tgl_opname', $tglOpname)->where('principal', 'BAS')->where('status', 'started')->where('jenis_so', 'cycle_count')->exists();
                } elseif ($key === 'WFG_SMU') {
                    $hasStarted = WfgSopTempModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')
                        ->whereHas('soh', function ($q) {
                            $q->where('jenis_so', 'cycle_count');
                        })
                        ->exists()
                        || WfgSopStatusModel::whereDate('tgl_opname', $tglOpname)->where('principal', '!=', 'BAS')->where('status', 'started')->where('jenis_so', 'cycle_count')->exists();
                }

                $statusApproval = $hasStarted ? 'On Progress' : 'Belum Mulai';
            }

            $approvalTracking[] = [
                'key' => $key,
                'name' => $name,
                'no_doc' => $noDoc,
                'operator' => $operatorName,
                'status' => $statusApproval,
                'approved_by' => $approvedBy,
                'pending_by' => $pendingBy,
                'rejected_by' => $rejectedBy
            ];
        }

        // Apply filters
        if ($sectionFilter && $sectionFilter !== 'all') {
            $approvalTracking = collect($approvalTracking)->where('key', $sectionFilter)->values()->all();
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $approvalTracking = collect($approvalTracking)->filter(function ($s) use ($statusFilter) {
                if ($statusFilter === 'idle') {
                    return $s['status'] === 'Belum Mulai';
                } elseif ($statusFilter === 'started') {
                    return $s['status'] === 'On Progress';
                } elseif ($statusFilter === 'finished') {
                    return in_array($s['status'], ['Pending', 'Approved', 'Rejected']);
                }
                return true;
            })->values()->all();
        }

        // Yesterday's Stock Accuracy (filtered by cycle_count)
        $yesterday = Carbon::parse($tglOpname)->subDay()->format('Y-m-d');

        // Wsp
        $wspYesSoIds = WspSoModel::whereDate('tgl_opname', $yesterday)->where('jenis_so', 'cycle_count')->pluck('id');
        $wspYesDi = WspSoSummariesModel::whereIn('so_id', $wspYesSoIds)->count();
        $wspYesMa = WspSoSummariesModel::whereIn('so_id', $wspYesSoIds)->where('status', 'match')->count();

        // Wrm
        $wrmYesSoIds = WrmSoModel::whereDate('tgl_opname', $yesterday)->where('jenis_so', 'cycle_count')->pluck('id');
        $wrmYesDi = WrmSoSummariesModel::whereIn('so_id', $wrmYesSoIds)->count();
        $wrmYesMa = WrmSoSummariesModel::whereIn('so_id', $wrmYesSoIds)->where('status', 'match')->count();

        // Wpm
        $wpmYesSoIds = WpmSoModel::whereDate('tgl_opname', $yesterday)->where('jenis_so', 'cycle_count')->pluck('id');
        $wpmYesDi = WpmSoSummariesModel::whereIn('so_id', $wpmYesSoIds)->count();
        $wpmYesMa = WpmSoSummariesModel::whereIn('so_id', $wpmYesSoIds)->where('status', 'match')->count();

        // Wcp
        $wcpYesSoIds = WcpSoModel::whereDate('tgl_opname', $yesterday)->where('jenis_so', 'cycle_count')->pluck('id');
        $wcpYesDi = WcpSoSummariesModel::whereIn('so_id', $wcpYesSoIds)->count();
        $wcpYesMa = WcpSoSummariesModel::whereIn('so_id', $wcpYesSoIds)->where('status', 'match')->count();

        // Wfg Bas & Smu
        $wfgYesSopIds = WfgSopModel::whereDate('tgl_opname', $yesterday)->where('jenis_so', 'cycle_count')->pluck('id');
        $wfgYesDi = WfgSopSummariesModel::whereIn('sop_id', $wfgYesSopIds)->count();
        $wfgYesMa = WfgSopSummariesModel::whereIn('sop_id', $wfgYesSopIds)->where('status', 'match')->count();

        $totalYesDi = $wspYesDi + $wrmYesDi + $wpmYesDi + $wcpYesDi + $wfgYesDi;
        $totalYesMa = $wspYesMa + $wrmYesMa + $wpmYesMa + $wcpYesMa + $wfgYesMa;

        $yesterdayAccuracy = $totalYesDi > 0 ? round(($totalYesMa / $totalYesDi) * 100, 2) : null;

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
                    'yesterday_accuracy' => $yesterdayAccuracy,
                    'selisih_qty_pos' => $totalQtyLebih > 0 ? '+' . number_format($totalQtyLebih, 0, ',', '.') : '0',
                    'selisih_qty_neg' => $totalQtyKurang < 0 ? number_format($totalQtyKurang, 0, ',', '.') : '0',
                ],
                'ringkasanSections' => $ringkasanSections,
                'approvalTracking' => $approvalTracking,
                'top10' => $top10,
                'charts' => [
                    'accuracy' => [
                        'categories' => $trendCategories,
                        'data' => $trendAccuracy
                    ],
                    'sections' => $trendSections
                ]
            ]
        ]);
    }

    private function getAllSectionsList()
    {
        return [
            'WSP' => 'WSP',
            'WRM' => 'WRM',
            'WPM' => 'WPM',
            'WCP' => 'WCP',
            'WFG_BAS' => 'WFG - BAS',
            'WFG_SMU' => 'WFG - SMU',
        ];
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
