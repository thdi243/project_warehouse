<?php

namespace App\Http\Controllers\Wrm;

use App\Http\Controllers\Controller;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterLocationModel;
use App\Models\Wrm\MasterBinModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get locations for filter dropdown
        $locations = MasterLocationModel::select('zona')->distinct()->get();
        return view('dashboard.wrm_dashboard', compact('locations'));
    }

    private function getFilterDates(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        return [$startDate, $endDate];
    }

    private function getBaseStockBalanceQuery(Request $request)
    {
        $zona = $request->zona;
        $query = StockBalance::query();
        if ($zona) {
            $query->whereHas('location', function ($q) use ($zona) {
                $q->where('zona', $zona);
            });
        }
        return $query;
    }

    private function getBaseStockMovementQuery(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);
        $zona = $request->zona;

        $query = StockMovement::whereBetween('tanggal', [$startDate, $endDate]);
        if ($zona) {
            $query->whereHas('location', function ($q) use ($zona) {
                $q->where('zona', $zona);
            });
        }
        return $query;
    }

    // --- 1. KPI Cards ---
    public function getKpi(Request $request)
    {
        $stockBalanceQuery = $this->getBaseStockBalanceQuery($request);

        $kpi = [
            'total_stock' => (clone $stockBalanceQuery)->sum('qty'),
            'total_item' => MasterBarangModel::count(),
            'inbound_today' => StockMovement::whereDate('tanggal', Carbon::today())->where('jenis', 'in')->sum('qty'),
            'outbound_today' => abs(StockMovement::whereDate('tanggal', Carbon::today())->where('jenis', 'out')->sum('qty')),
        ];

        return response()->json(['status' => true, 'data' => $kpi]);
    }

    // --- 2. Line/Column Chart: Inbound vs Outbound per day ---
    public function getChartMovement(Request $request)
    {
        [$startDate, $endDate] = $this->getFilterDates($request);
        $stockMovementQuery = $this->getBaseStockMovementQuery($request);

        $movementsDaily = $stockMovementQuery
            ->selectRaw('DATE(tanggal) as date, jenis, SUM(qty) as total')
            ->groupBy('date', 'jenis')
            ->orderBy('date')
            ->get();

        $categories = [];
        $inboundSeries = [];
        $outboundSeries = [];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $categories[] = $date->format('d M');
            $inboundSeries[$dateStr] = 0;
            $outboundSeries[$dateStr] = 0;
        }

        foreach ($movementsDaily as $mov) {
            $dateStr = $mov->date;
            if (isset($inboundSeries[$dateStr])) {
                if ($mov->jenis == 'in') $inboundSeries[$dateStr] = (int)$mov->total;
                else if ($mov->jenis == 'out') $outboundSeries[$dateStr] = abs((int)$mov->total);
            }
        }


        return response()->json([
            'status' => true,
            'data' => [
                'categories' => $categories,
                'series' => [
                    ['name' => 'Inbound', 'data' => array_values($inboundSeries), 'color' => '#28a745'],
                    ['name' => 'Outbound', 'data' => array_values($outboundSeries), 'color' => '#dc3545'],
                ]
            ]
        ]);
    }

    // --- 3. Pie Chart: Stock by Zone ---
    public function getChartPie(Request $request)
    {
        $stockBalanceQuery = $this->getBaseStockBalanceQuery($request);

        $stockByZoneRaw = $stockBalanceQuery
            ->join('wrm_master_location', 'wrm_stock_balance.loc_id', '=', 'wrm_master_location.id')
            ->select('wrm_master_location.zona', DB::raw('SUM(wrm_stock_balance.qty) as total'))
            ->groupBy('wrm_master_location.zona')
            ->get();

        $chartPie = [];
        foreach ($stockByZoneRaw as $sz) {
            $chartPie[] = [
                'name' => $sz->zona ?? 'Unknown',
                'y' => (int)$sz->total
            ];
        }

        return response()->json(['status' => true, 'data' => $chartPie]);
    }

    // --- 4. Bar Chart: Top 10 Fast Moving Items ---
    public function getChartBar(Request $request)
    {
        $stockMovementQuery = $this->getBaseStockMovementQuery($request);

        $fastMovingRaw = $stockMovementQuery
            ->where('jenis', 'out')
            ->join('wrm_master_barang', 'wrm_stock_movements.barang_id', '=', 'wrm_master_barang.id')
            ->select('wrm_master_barang.nama_barang', DB::raw('SUM(wrm_stock_movements.qty) as total'))
            ->groupBy('wrm_master_barang.id', 'wrm_master_barang.nama_barang')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $categories = [];
        $data = [];
        foreach ($fastMovingRaw as $fm) {
            $categories[] = \Illuminate\Support\Str::limit($fm->nama_barang, 20);
            $data[] = abs((int)$fm->total);
        }


        return response()->json([
            'status' => true,
            'data' => [
                'categories' => $categories,
                'series' => [
                    ['name' => 'Qty Keluar', 'data' => $data, 'color' => '#17a2b8']
                ]
            ]
        ]);
    }

    // --- 5. Donut Chart: Space Utilization ---
    public function getChartCapacity(Request $request)
    {
        $zona = $request->zona;

        $binQuery = \App\Models\Wrm\MasterBinModel::query();
        if ($zona) {
            $binQuery->whereHas('location', function ($q) use ($zona) {
                $q->where('zona', $zona);
            });
        }
        $totalBins = $binQuery->count();

        $occupiedQuery = StockInboundDetail::where('qty', '>', 0)->distinct('loc_id');
        if ($zona) {
            $occupiedQuery->whereHas('bin.location', function ($q) use ($zona) {
                $q->where('zona', $zona);
            });
        }
        $occupiedBinsCount = $occupiedQuery->count('loc_id');

        $emptyBinsCount = max(0, $totalBins - $occupiedBinsCount);

        return response()->json([
            'status' => true,
            'data' => [
                ['name' => 'Occupied Bins', 'y' => $occupiedBinsCount, 'color' => '#ffc107'],
                ['name' => 'Empty Bins', 'y' => $emptyBinsCount, 'color' => '#e9ecef'],
            ]
        ]);
    }

    // --- 5. Table Expiring ---
    public function getTableExpiring(Request $request)
    {
        $expiringItems = StockInboundDetail::with(['barang', 'inbound', 'bin.location'])
            ->whereHas('inbound', function ($q) {
                // Expiring within next 30 days
                $q->whereNotNull('expired_date')
                    ->whereBetween('expired_date', [Carbon::today(), Carbon::today()->addDays(30)]);
            })
            ->where('qty', '>', 0)
            ->orderBy(StockInboundDetail::select('expired_date')->from('wrm_stock_inbound')->whereColumn('wrm_stock_inbound.id', 'wrm_stock_inbound_details.inbound_id')->limit(1))
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $expiredDate = $item->inbound->expired_date ? Carbon::parse($item->inbound->expired_date) : null;
                $daysLeft = $expiredDate ? Carbon::today()->diffInDays($expiredDate, false) : 0;

                return [
                    'barang' => $item->barang->nama_barang ?? 'Unknown',
                    'no_spb' => $item->inbound->no_spb ?? '-',
                    'qty' => $item->qty,
                    'lokasi' => $item->bin ? ($item->bin->location->zona . '-' . $item->bin->bin) : '-',
                    'expired_date' => $expiredDate ? $expiredDate->format('d M Y') : '-',
                    'days_left' => $daysLeft
                ];
            });

        return response()->json(['status' => true, 'data' => $expiringItems]);
    }

    // --- 6. Table Recent ---
    public function getTableRecent(Request $request)
    {
        $recentActivities = StockMovement::with(['barang', 'location'])
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
                    'lokasi' => $mov->location->zona ?? '-',
                    'tipe' => $mov->ref_type
                ];
            });

        return response()->json(['status' => true, 'data' => $recentActivities]);
    }

    // --- 7. Warehouse Location Layout ---
    public function getLocationLayout(Request $request)
    {
        $zona = $request->zona;

        // Get all bins with their location, grouped by location
        $bins = MasterBinModel::with('location')
            ->when($zona, function ($q) use ($zona) {
                $q->whereHas('location', function ($q2) use ($zona) {
                    $q2->where('zona', $zona);
                });
            })
            ->get();

        // Get occupied bins and their barang info from active stock
        $occupiedDetails = StockInboundDetail::with('barang')
            ->whereIn('status', ['UNREST', 'QI', 'BLOCKED'])
            ->where('qty', '>', 0)
            ->get();

        $occupiedMap = [];
        foreach ($occupiedDetails as $detail) {
            if (!isset($occupiedMap[$detail->loc_id])) {
                $occupiedMap[$detail->loc_id] = [
                    'mid' => $detail->barang ? $detail->barang->mid : 'UNKNOWN',
                    'nama_barang' => $detail->barang ? $detail->barang->nama_barang : 'Unknown',
                    'qty' => 0,
                    'pallet_id' => $detail->pallet_id
                ];
            }
            $occupiedMap[$detail->loc_id]['qty'] += $detail->qty;
        }
        $occupiedIds = array_keys($occupiedMap);

        $reservedIds = StockInboundDetail::where('status', 'RESERVED')
            ->where('qty', '>', 0)
            ->distinct('loc_id')
            ->pluck('loc_id')
            ->toArray();

        // Group bins by location label
        $locations = [];
        foreach ($bins as $bin) {
            $loc = $bin->location;
            if (!$loc) continue;
            $locKey = $loc->plant . ' - ' . $loc->gudang . ' - ' . ($loc->zona ?? '') . ' - ' . $loc->bin;

            if (!isset($locations[$locKey])) {
                $locations[$locKey] = [
                    'label' => $locKey,
                    'plant' => $loc->plant,
                    'gudang' => $loc->gudang,
                    'zona' => $loc->zona ?? '-',
                    'bin' => $loc->bin,
                    'cells' => []
                ];
            }

            $status = 'empty';
            $mid = null;
            $nama_barang = null;
            $qty = 0;
            $palletId = null;
            if (in_array($bin->id, $reservedIds)) $status = 'reserved';
            if (in_array($bin->id, $occupiedIds)) {
                $status = 'occupied';
                $mid = $occupiedMap[$bin->id]['mid'];
                $nama_barang = $occupiedMap[$bin->id]['nama_barang'];
                $qty = $occupiedMap[$bin->id]['qty'];
                $palletId = $occupiedMap[$bin->id]['pallet_id'];
            }

            $locations[$locKey]['cells'][] = [
                'id'     => $bin->id,
                'kolom'  => $bin->kolom,
                'level'  => $bin->level,
                'label'  => $bin->kolom . '.' . $bin->level,
                'status' => $status,
                'mid'    => $mid,
                'nama_barang' => $nama_barang,
                'qty' => $qty,
                'pallet_id' => $palletId
            ];
        }

        // Sort cells per location by kolom then level
        foreach ($locations as &$loc) {
            usort($loc['cells'], function ($a, $b) {
                if ($a['kolom'] != $b['kolom']) return $a['kolom'] <=> $b['kolom'];
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
                'total'    => $totalBins,
                'occupied' => $occupiedCount,
                'reserved' => $reservedCount,
                'empty'    => $emptyCount,
            ],
            'data' => array_values($locations)
        ]);
    }
}
