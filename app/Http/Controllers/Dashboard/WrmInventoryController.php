<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wrm\Inventory\StockOutbound;
use App\Models\Wrm\Inventory\StockTransferDetail;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterBinModel;
use App\Models\Wrm\MasterLocationModel;
use App\Models\Wrm\MasterSupplierModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WrmInventoryController extends Controller
{
    public function index()
    {
        // Get locations grouped by gudang for filter dropdown
        $locations = MasterLocationModel::select('gudang')->whereNotNull('gudang')->distinct()->get();

        $mid = MasterBarangModel::select('mid')->distinct()->get();
        $noSpb = StockOnHand::select('no_spb')
            ->whereNotNull('no_spb')
            ->whereNotIn('status', ['ISSUED', 'RESERVED'])
            ->distinct()
            ->get();

        return view('dashboard.wrm_dashboard', compact('locations', 'mid', 'noSpb'));
    }

    private function getFilterDates(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        return [$startDate, $endDate];
    }

    private function getBaseStockBalanceQuery(Request $request)
    {
        $gudang = $request->gudang;
        $query = clone StockBalance::query();
        if ($gudang) {
            $query->whereHas('location', function ($q) use ($gudang) {
                $q->where('gudang', $gudang);
            });
        }

        return $query;
    }

    // --- 1. KPI Cards ---
    public function getKpi(Request $request)
    {
        // Total Stock: ambil dari StockOnHand (aktif, bukan ISSUED)
        $inboundDetailQuery = clone StockOnHand::query();
        if ($request->gudang) {
            $inboundDetailQuery->whereHas('bin.location', function ($q) use ($request) {
                $q->where('gudang', $request->gudang);
            });
        }

        if ($request->mid) {
            $inboundDetailQuery->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', $request->mid);
            });
        }

        if ($request->spb) {
            $inboundDetailQuery->where('no_spb', $request->spb);
        }

        $totalStock = (clone $inboundDetailQuery)->whereNotIn('status', ['ISSUED', 'RESERVED'])->sum('qty');
        $activePalletCount = (clone $inboundDetailQuery)->whereNotIn('status', ['ISSUED', 'RESERVED'])->count();

        // Draft Outbound (Today) - Only count items still in RESERVED status
        $draftOutboundToday = StockOutbound::join('wrm_stock_draft_outbound_details', 'wrm_stock_draft_outbound.id', '=', 'wrm_stock_draft_outbound_details.outbound_id')
            ->whereDate('wrm_stock_draft_outbound.reservasi_date', Carbon::today())
            ->where('wrm_stock_draft_outbound_details.status', 'RESERVED')
            ->sum('wrm_stock_draft_outbound_details.qty');

        // Transfer (Today)
        $transferToday = StockTransferDetail::whereHas('header', function ($q) {
            $q->whereDate('tgl_gi', Carbon::today());
        })->sum('qty_actual');

        // Inbound Today
        $inboundTodayQuery = StockMovement::whereDate('tanggal', Carbon::today())->where('jenis', 'in')->where('ref_type', 'inbound');
        if ($request->gudang) {
            $inboundTodayQuery->whereHas('location', function ($q) use ($request) {
                $q->where('gudang', $request->gudang);
            });
        }

        $kpi = [
            'total_stock' => $totalStock,
            'total_item' => MasterBarangModel::count(),
            'active_pallet' => $activePalletCount,
            'inbound_today' => $inboundTodayQuery->sum('qty'),
            'draft_outbound_today' => $draftOutboundToday,
            'transfer_today' => $transferToday,
        ];

        return response()->json(['status' => true, 'data' => $kpi]);
    }

    // --- 2. Chart Movement: Inbound, Draft Outbound, Transfer per day ---
    public function getChartMovement(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);

        /*
        |--------------------------------------------------------------------------
        | INBOUND
        |--------------------------------------------------------------------------
        */

        $inboundQuery = StockMovement::whereBetween('tanggal', [$startDate, $endDate])
            ->where('jenis', 'in')
            ->where('ref_type', 'inbound');

        if ($request->gudang) {
            $inboundQuery->whereHas('location', function ($q) use ($request) {
                $q->where('gudang', $request->gudang);
            });
        }

        if ($request->mid) {
            $inboundQuery->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', $request->mid);
            });
        }

        $inboundDaily = $inboundQuery
            ->selectRaw('DATE(tanggal) as date, COUNT(id) as total_pallet, SUM(qty) as total_qty')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        /*
        |--------------------------------------------------------------------------
        | TRANSFER
        |--------------------------------------------------------------------------
        */

        $transferQuery = StockTransferDetail::join(
            'wrm_stock_transfers',
            'wrm_stock_transfer_details.transfer_id',
            '=',
            'wrm_stock_transfers.id'
        )
            ->join(
                'wrm_master_barang',
                'wrm_stock_transfer_details.barang_id',
                '=',
                'wrm_master_barang.id'
            )
            ->whereBetween(
                'wrm_stock_transfers.tgl_reservasi',
                [$startDate, $endDate]
            );

        if ($request->mid) {
            $transferQuery->where('wrm_master_barang.mid', $request->mid);
        }

        if ($request->no_spb) {
            $transferQuery->where(
                'wrm_stock_transfer_details.no_spb',
                $request->no_spb
            );
        }

        $transferDaily = $transferQuery
            ->selectRaw('DATE(wrm_stock_transfers.tgl_reservasi) as date, COUNT(wrm_stock_transfer_details.id) as total_pallet, SUM(wrm_stock_transfer_details.qty_actual) as total_qty')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        /*
        |--------------------------------------------------------------------------
        | FORMAT CHART
        |--------------------------------------------------------------------------
        */

        $categories = [];
        $inboundSeries = [];
        $transferSeries = [];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');

            $categories[] = $date->format('d M');

            // Inbound data point
            if (isset($inboundDaily[$dateStr])) {
                $inboundSeries[] = [
                    'y' => (int) $inboundDaily[$dateStr]->total_pallet,
                    'qty' => (float) $inboundDaily[$dateStr]->total_qty
                ];
            } else {
                $inboundSeries[] = [
                    'y' => 0,
                    'qty' => 0.0
                ];
            }

            // Transfer data point
            if (isset($transferDaily[$dateStr])) {
                $transferSeries[] = [
                    'y' => (int) $transferDaily[$dateStr]->total_pallet,
                    'qty' => (float) $transferDaily[$dateStr]->total_qty
                ];
            } else {
                $transferSeries[] = [
                    'y' => 0,
                    'qty' => 0.0
                ];
            }
        }

        return response()->json([
            'status' => true,
            'data' => [
                'categories' => $categories,
                'series' => [
                    [
                        'name' => 'Inbound',
                        'data' => $inboundSeries,
                        'color' => '#22c55e',
                        'type' => 'spline'
                    ],
                    [
                        'name' => 'Transfer',
                        'data' => $transferSeries,
                        'color' => '#3b82f6',
                        'type' => 'spline'
                    ],
                ],
            ],
        ]);
    }

    // --- 3. Pie Chart: Stock by Gudang ---
    public function getChartPie(Request $request)
    {
        $query = StockOnHand::query()
            ->whereNotIn('status', ['ISSUED', 'RESERVED']);

        if ($request->gudang) {
            $query->whereHas('bin.location', function ($q) use ($request) {
                $q->where('gudang', $request->gudang);
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', $request->mid);
            });
        }

        if ($request->spb) {
            $query->where('no_spb', $request->spb);
        }

        $stockByGudangRaw = $query
            ->join('wrm_master_bin', 'wrm_stock_on_hand.loc_id', '=', 'wrm_master_bin.id')
            ->join('wrm_master_location', 'wrm_master_bin.loc_id', '=', 'wrm_master_location.id')
            ->select(
                'wrm_master_location.gudang',
                DB::raw('COUNT(wrm_stock_on_hand.id) as total_pallet'),
                DB::raw('SUM(wrm_stock_on_hand.qty) as total_qty')
            )
            ->groupBy('wrm_master_location.gudang')
            ->get();

        $chartPie = [];
        foreach ($stockByGudangRaw as $sz) {
            $chartPie[] = [
                'name' => $sz->gudang ?? 'Unknown',
                'y' => (int) $sz->total_pallet,
                'qty' => (float) $sz->total_qty
            ];
        }

        return response()->json(['status' => true, 'data' => $chartPie]);
    }

    // --- 4. Bar Chart: Top 5 Material by Qty (replacing old Fast Moving Items) ---
    public function getChartBar(Request $request)
    {
        $query = clone StockOnHand::query();
        $query->where('qty', '>', 0)->whereNotIn('status', ['ISSUED', 'RESERVED']);

        if ($request->gudang) {
            $query->whereHas('bin.location', function ($q) use ($request) {
                $q->where('gudang', $request->gudang);
            });
        }


        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', $request->mid);
            });
        }

        if ($request->spb) {
            $query->where('no_spb', $request->spb);
        }

        $topMaterials = $query
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->select(
                'wrm_master_barang.nama_barang',
                DB::raw('COUNT(wrm_stock_on_hand.id) as total_pallet'),
                DB::raw('SUM(wrm_stock_on_hand.qty) as total_qty')
            )
            ->groupBy('wrm_master_barang.id', 'wrm_master_barang.nama_barang')
            ->orderByDesc('total_pallet')
            ->limit(5)
            ->get();

        $categories = [];
        $data = [];
        foreach ($topMaterials as $tm) {
            $categories[] = \Illuminate\Support\Str::limit($tm->nama_barang, 25);
            $data[] = [
                'y' => (int) $tm->total_pallet,
                'qty' => (float) $tm->total_qty,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'categories' => $categories,
                'series' => [
                    ['name' => 'Pallet', 'data' => $data, 'color' => '#6366f1', 'type' => 'bar'],
                ],
            ],
        ]);
    }

    // --- 5. Donut Chart: Aging Stock (replacing Space Utilization) ---
    public function getChartCapacity(Request $request)
    {
        $query = StockOnHand::where('qty', '>', 0)->whereNotIn('status', ['ISSUED', 'RESERVED']);

        if ($request->gudang) {
            $query->whereHas('bin.location', function ($q) use ($request) {
                $q->where('gudang', $request->gudang);
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', $request->mid);
            });
        }

        if ($request->spb) {
            $query->where('no_spb', $request->spb);
        }

        $details = $query->get();

        $agingPallets = [
            '0-30 Days' => 0,
            '30-90 Days' => 0,
            '> 90 Days' => 0,
        ];

        $agingQty = [
            '0-30 Days' => 0,
            '30-90 Days' => 0,
            '> 90 Days' => 0,
        ];

        $today = Carbon::today();
        foreach ($details as $detail) {
            $days = 999;
            if ($detail->incoming_date) {
                $incomingDate = Carbon::parse($detail->incoming_date);
                $days = $incomingDate->diffInDays($today);
            }

            if ($days <= 30) {
                $agingPallets['0-30 Days']++;
                $agingQty['0-30 Days'] += $detail->qty;
            } elseif ($days <= 90) {
                $agingPallets['30-90 Days']++;
                $agingQty['30-90 Days'] += $detail->qty;
            } else {
                $agingPallets['> 90 Days']++;
                $agingQty['> 90 Days'] += $detail->qty;
            }
        }

        return response()->json([
            'status' => true,
            'data' => [
                [
                    'name' => '0-30 Days',
                    'y' => $agingPallets['0-30 Days'],
                    'qty' => $agingQty['0-30 Days'],
                    'color' => '#22c55e'
                ],
                [
                    'name' => '30-90 Days',
                    'y' => $agingPallets['30-90 Days'],
                    'qty' => $agingQty['30-90 Days'],
                    'color' => '#f59e0b'
                ],
                [
                    'name' => '> 90 Days',
                    'y' => $agingPallets['> 90 Days'],
                    'qty' => $agingQty['> 90 Days'],
                    'color' => '#ef4444'
                ],
            ],
        ]);
    }

    public function getAgingDetail(Request $request)
    {
        $range = $request->range; // '0-30 Days', '30-90 Days', '> 90 Days'

        $query = StockOnHand::with([
            'barang:id,mid,nama_barang,uom',
            'bin:id,loc_id,kolom,level',
            'bin.location:id,plant,s_loc,gudang,zona,bin',
        ])
            ->where('qty', '>', 0)
            ->whereNotIn('status', ['ISSUED', 'RESERVED']);

        if ($request->gudang) {
            $query->whereHas('bin.location', function ($q) use ($request) {
                $q->where('gudang', $request->gudang);
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', $request->mid);
            });
        }

        if ($request->spb) {
            $query->where('no_spb', $request->spb);
        }

        $today = Carbon::today();

        if ($range === '0-30 Days') {
            $query->whereBetween('incoming_date', [$today->copy()->subDays(30)->startOfDay(), $today->endOfDay()]);
        } elseif ($range === '30-90 Days') {
            $query->whereBetween('incoming_date', [$today->copy()->subDays(90)->startOfDay(), $today->copy()->subDays(31)->endOfDay()]);
        } elseif ($range === '> 90 Days') {
            $query->where(function ($q) use ($today) {
                $q->where('incoming_date', '<', $today->copy()->subDays(90)->startOfDay())
                    ->orWhereNull('incoming_date');
            });
        }

        $data = $query->orderBy('incoming_date', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    // --- 6. Table Recent Activities ---
    public function getTableRecent(Request $request)
    {
        $query = clone StockMovement::with(['barang', 'location', 'location.bins']);

        if ($request->gudang) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('gudang', $request->gudang);
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', $request->mid);
            });
        }

        $recentActivities = $query
            ->latest('tanggal')
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(function ($mov) {
                return [
                    'tanggal' => Carbon::parse($mov->tanggal)->format('d M Y H:i'),
                    'jenis' => strtoupper($mov->jenis),
                    'barang' => $mov->barang->nama_barang ?? 'Unknown',
                    'qty' => $mov->qty,
                    'lokasi' => $mov->location ? implode(' - ', array_filter([$mov->location->gudang, $mov->location->zona, $mov->location->bin])) : 'Unknown',
                    'tipe' => $mov->ref_type,
                ];
            });

        return response()->json(['status' => true, 'data' => $recentActivities]);
    }

    // --- 7. Warehouse Location Layout ---
    public function getLocationLayout(Request $request)
    {
        $gudang = $request->gudang;

        // Get all bins with their location, grouped by location
        $bins = MasterBinModel::with('location')
            ->when($gudang, function ($q) use ($gudang) {
                $q->whereHas('location', function ($q2) use ($gudang) {
                    $q2->where('gudang', $gudang);
                });
            })
            ->get();

        // Get occupied bins and their barang info from active stock
        $occupiedDetails = clone StockOnHand::with(['barang'])
            ->whereIn('status', ['UNREST', 'QI', 'BLOCKED'])
            ->where('qty', '>', 0);

        if ($request->mid) {
            $occupiedDetails->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', $request->mid);
            });
        }

        if ($request->spb) {
            $occupiedDetails->where('no_spb', $request->spb);
        }

        $occupiedDetails = $occupiedDetails->get();

        $occupiedMap = [];
        foreach ($occupiedDetails as $detail) {
            if (! isset($occupiedMap[$detail->loc_id])) {
                $occupiedMap[$detail->loc_id] = [
                    'mid' => $detail->barang ? $detail->barang->mid : 'UNKNOWN',
                    'nama_barang' => $detail->barang ? $detail->barang->nama_barang : 'Unknown',
                    'qty' => 0,
                    'pallet_id' => $detail->pallet_id,
                    'no_spb' => $detail->no_spb ?? '-',
                    'incoming_date' => $detail->incoming_date ?? '-',
                ];
            }
            $occupiedMap[$detail->loc_id]['qty'] += $detail->qty;
        }
        $occupiedIds = array_keys($occupiedMap);

        $reservedIdsQuery = clone StockOnHand::where('status', 'RESERVED')->where('qty', '>', 0);
        if ($request->mid) {
            $reservedIdsQuery->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', $request->mid);
            });
        }
        // if ($request->supplier) {
        //     $reservedIdsQuery->where('supplier', $request->supplier);
        // }

        $reservedIds = $reservedIdsQuery->distinct('loc_id')->pluck('loc_id')->toArray();

        // Group bins by location label
        $locations = [];
        foreach ($bins as $bin) {
            $loc = $bin->location;
            if (! $loc) {
                continue;
            }
            $locKey = $loc->plant . ' - ' . $loc->gudang . ' - ' . ($loc->zona ?? '') . ' - ' . $loc->bin;

            if (! isset($locations[$locKey])) {
                $locations[$locKey] = [
                    'label' => $locKey,
                    'plant' => $loc->plant,
                    'gudang' => $loc->gudang,
                    'zona' => $loc->zona ?? '-',
                    'bin' => $loc->bin,
                    'cells' => [],
                ];
            }

            $status = 'empty';
            $mid = null;
            $nama_barang = null;
            $qty = 0;
            $palletId = null;
            $noSpb = '-';
            $incomingDate = '-';
            if (in_array($bin->id, $reservedIds)) {
                $status = 'reserved';
            }
            if (in_array($bin->id, $occupiedIds)) {
                $status = 'occupied';
                $mid = $occupiedMap[$bin->id]['mid'];
                $nama_barang = $occupiedMap[$bin->id]['nama_barang'];
                $qty = $occupiedMap[$bin->id]['qty'];
                $palletId = $occupiedMap[$bin->id]['pallet_id'];
                $noSpb = $occupiedMap[$bin->id]['no_spb'];
                $incomingDate = $occupiedMap[$bin->id]['incoming_date'];
            }

            $locations[$locKey]['cells'][] = [
                'id' => $bin->id,
                'kolom' => $bin->kolom,
                'level' => $bin->level,
                'label' => $bin->kolom . '.' . $bin->level,
                'status' => $status,
                'mid' => $mid,
                'nama_barang' => $nama_barang,
                'qty' => $qty,
                'pallet_id' => $palletId,
                'no_spb' => $noSpb,
                'incoming_date' => $incomingDate,
            ];
        }

        // Sort cells per location by kolom then level
        foreach ($locations as &$loc) {
            usort($loc['cells'], function ($a, $b) {
                if ($a['kolom'] != $b['kolom']) {
                    return $a['kolom'] <=> $b['kolom'];
                }

                return $a['level'] <=> $b['level'];
            });
        }

        // Summary stats
        $totalBins = count($bins);
        $occupiedCount = count($occupiedIds);
        $reservedCount = count(array_diff($reservedIds, $occupiedIds));
        $emptyCount = max(0, $totalBins - $occupiedCount - $reservedCount);

        return response()->json([
            'status' => true,
            'summary' => [
                'total' => $totalBins,
                'occupied' => $occupiedCount,
                'reserved' => $reservedCount,
                'empty' => $emptyCount,
                'available' => $emptyCount + $reservedCount,
            ],
            'data' => array_values($locations),
        ]);
    }
}
