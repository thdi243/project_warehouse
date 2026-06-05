<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use App\Models\Wrm\Inventory\StockTransferDetail;
use App\Models\Wrm\MasterBarangModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function indexPpic()
    {
        return view('wrm.inventory.monitoring_ppic');
    }

    public function indexPurchasing()
    {
        return view('wrm.inventory.monitoring_purchasing');
    }

    public function indexSummaryStock()
    {
        return view('wrm.inventory.summary_stock');
    }

    public function getSummaryStockData(Request $request)
    {
        $summaryType = $request->get('summary_type', 'item');

        $query = StockOnHand::query()
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->selectRaw("
                SUM(CASE WHEN wrm_stock_on_hand.status = 'UNREST' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_unrest,
                SUM(CASE WHEN wrm_stock_on_hand.status = 'QI' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_qi,
                SUM(CASE WHEN wrm_stock_on_hand.status = 'BLOCKED' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_blocked
            ")
            ->whereNotIn('status', ['ISSUED', 'RESERVED']);

        if ($summaryType === 'spb') {
            $query->addSelect('wrm_stock_on_hand.no_spb', 'wrm_master_barang.uom')
                ->whereNotNull('wrm_stock_on_hand.no_spb')
                ->groupBy('wrm_stock_on_hand.no_spb', 'wrm_master_barang.uom');
            $sortColumn = 'wrm_stock_on_hand.no_spb';
        } else {
            $query->addSelect('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom')
                ->groupBy('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom');
            $sortColumn = 'wrm_master_barang.mid';
        }

        if ($summaryType === 'item' && $request->mid) {
            $query->where('wrm_master_barang.mid', 'like', $request->mid . '%');
        }

        if ($summaryType === 'spb' && $request->no_spb) {
            $query->where('wrm_stock_on_hand.no_spb', 'like', $request->no_spb . '%');
        }

        if ($summaryType === 'spb' && $request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('wrm_master_barang.mid', 'like', $request->mid . '%');
            });
        }

        // Count for pagination and grand totals
        $recordsTotal = DB::query()
            ->fromSub(clone $query, 'sub')
            ->count();

        $totalsPerUom = DB::query()
            ->fromSub(clone $query, 'sub')
            ->select(
                'uom',
                DB::raw("SUM(qty_unrest) as total_unrest"),
                DB::raw("SUM(qty_qi) as total_qi"),
                DB::raw("SUM(qty_blocked) as total_blocked")
            )
            ->groupBy('uom')
            ->get();

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;

        $data = $query->orderBy($sortColumn)->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
            'grand_total_per_uom' => $totalsPerUom->map(function ($item) {
                return [
                    'uom' => $item->uom,
                    'unrest' => $item->total_unrest ?? 0,
                    'qi' => $item->total_qi ?? 0,
                    'blocked' => $item->total_blocked ?? 0,
                    'all' => ($item->total_unrest ?? 0) + ($item->total_qi ?? 0) + ($item->total_blocked ?? 0)
                ];
            })
        ]);
    }

    public function getSpbDetailData(Request $request)
    {
        $noSpb = $request->no_spb;

        $data = StockOnHand::with([
            'barang:id,mid,nama_barang,uom',
            'bin:id,loc_id,kolom,level',
            'bin.location:id,plant,s_loc,gudang,zona,bin'
        ])
            ->where('no_spb', $noSpb)
            ->whereNotIn('status', ['ISSUED', 'RESERVED'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getSummaryPpic()
    {
        // On Hand (Physical)
        $totalSoh = StockOnHand::whereNotIn('status', ['ISSUED'])->sum('qty');

        // Reserved (Allocated for orders)
        $totalReserved = StockOnHand::where('status', 'RESERVED')->sum('qty');

        // Available
        $totalAvailable = $totalSoh - $totalReserved;

        // Today's Consumption (Outgoing Movements)
        $todayOutgoing = StockMovement::whereDate('tanggal', Carbon::today())
            ->where('jenis', 'out')
            ->sum('qty');

        return response()->json([
            'status' => true,
            'data' => [
                'total_available' => number_format($totalAvailable, 0, ',', '.'),
                'total_onhand' => number_format($totalSoh, 0, ',', '.'),
                'total_reserved' => number_format($totalReserved, 0, ',', '.'),
                'today_usage' => number_format($todayOutgoing, 0, ',', '.'),
            ]
        ]);
    }

    public function getSummaryPurchasing()
    {
        // 1. Stock below a threshold (mock min stock as 100 for now)
        $reorderCount = StockOnHand::select('barang_id', DB::raw('SUM(qty) as total'))
            ->groupBy('barang_id')
            ->having('total', '<', 100)
            ->get()
            ->count();

        // 2. Outstanding Inbound (Not yet ISSUED/COMPLETED)
        $outstandingPo = StockInboundDetail::whereNotIn('status', ['COMPLETED'])->sum('qty');

        // 3. Overdue POs (Incoming date passed but status not completed)
        $overduePo = StockInboundDetail::whereHas('inbound', function ($q) {
            $q->where('incoming_date', '<', Carbon::today());
        })->whereNotIn('status', ['COMPLETED'])->count();

        return response()->json([
            'status' => true,
            'data' => [
                'reorder_count' => $reorderCount,
                'outstanding_po' => number_format($outstandingPo, 0, ',', '.'),
                'overdue_po' => $overduePo,
            ]
        ]);
    }

    public function getSohData(Request $request)
    {
        $query = StockOnHand::with(['barang', 'bin.location'])
            ->whereNotIn('status', ['ISSUED', 'RESERVED']);

        $recordsTotal = $query->count();

        if ($request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('barcode', 'like', "%$search%")
                    ->orWhere('no_spb', 'like', "%$search%")
                    ->orWhereHas('barang', function ($bq) use ($search) {
                        $bq->where('mid', 'like', "%$search%")
                            ->orWhere('nama_barang', 'like', "%$search%");
                    });
            });
        }

        $recordsFiltered = $query->count();

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $data = $query->skip($start)->take($length)->get();

        $formattedData = $data->map(function ($row) {
            $loc = $row->bin->location ?? null;
            return [
                'material' => $row->barang->nama_barang ?? '-',
                'mid' => $row->barang->mid ?? '-',
                'qty' => $row->qty,
                'status' => $row->status,
                'location' => $loc ? $loc->gudang . ' - ' . $loc->bin : '-',
                'pallet_id' => $row->pallet_id,
                'supplier' => $row->supplier,
                'aging' => $row->incoming_date ? floor(Carbon::parse($row->incoming_date)->diffInDays()) . ' Hari' : '-',
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData
        ]);
    }

    public function getInboundData(Request $request)
    {
        $query = StockInboundDetail::with(['inbound', 'barang', 'bin.location']);

        $recordsTotal = $query->count();

        if ($request->search['value']) {
            $search = $request->search['value'];
            $query->whereHas('inbound', function ($q) use ($search) {
                $q->where('no_spb', 'like', "%$search%");
            });
        }

        $recordsFiltered = $query->count();

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $data = $query->latest()->skip($start)->take($length)->get();

        $formattedData = $data->map(function ($row) {
            return [
                'no_spb' => $row->inbound->no_spb ?? '-',
                'material' => $row->barang->nama_barang ?? '-',
                'mid' => $row->barang->mid ?? '-',
                'supplier' => $row->inbound->supplier ?? '-',
                'qty' => $row->qty,
                'status' => $row->status,
                'tanggal_datang' => $row->inbound->incoming_date ? Carbon::parse($row->inbound->incoming_date)->format('d/m/Y') : '-',
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData
        ]);
    }

    public function getOutboundData(Request $request)
    {
        $query = StockOutboundDetail::with(['outbound', 'barang', 'bin.location'])
            ->where('status', 'RESERVED');

        $recordsTotal = $query->count();

        if ($request->search['value']) {
            $search = $request->search['value'];
            $query->whereHas('outbound', function ($q) use ($search) {
                $q->where('no_reservasi', 'like', "%$search%");
            });
        }

        $recordsFiltered = $query->count();

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $data = $query->latest()->skip($start)->take($length)->get();

        $formattedData = $data->map(function ($row) {
            return [
                'no_outbound' => $row->outbound->no_reservasi ?? '-',
                'material' => $row->barang->nama_barang ?? '-',
                'mid' => $row->barang->mid ?? '-',
                'qty' => $row->qty,
                'status' => $row->status,
                'tanggal_reservasi' => $row->outbound->reservasi_date ? Carbon::parse($row->outbound->reservasi_date)->format('d/m/Y') : '-',
                'destinasi' => $row->outbound->destinasi ?? '-',
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData
        ]);
    }

    public function getTransferData(Request $request)
    {
        $query = StockTransferDetail::with(['header', 'barang']);

        $recordsTotal = $query->count();

        if ($request->search['value']) {
            $search = $request->search['value'];
            $query->whereHas('header', function ($hq) use ($search) {
                $hq->where('no_reservasi', 'like', "%$search%");
            });
        }

        $recordsFiltered = $query->count();

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $data = $query->latest()->skip($start)->take($length)->get();

        $formattedData = $data->map(function ($row) {
            return [
                'no_reservasi' => $row->header->no_reservasi ?? '-',
                'no_ba' => $row->header->no_ba ?? '-',
                'material' => $row->barang->nama_barang ?? '-',
                'mid' => $row->barang->mid ?? '-',
                'qty_actual' => $row->qty_actual,
                'grade' => $row->grade,
                'tanggal' => $row->header->tgl_gi ? Carbon::parse($row->header->tgl_gi)->format('d/m/Y') : ($row->created_at ? $row->created_at->format('d/m/Y') : '-'),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData
        ]);
    }

    public function getPpicStockData(Request $request)
    {
        $materials = MasterBarangModel::select('id', 'mid', 'nama_barang', 'uom')->get();

        $sohSummary = StockOnHand::select(
            'barang_id',
            DB::raw("SUM(CASE WHEN status != 'ISSUED' THEN qty ELSE 0 END) as on_hand"),
            DB::raw("SUM(CASE WHEN status = 'RESERVED' THEN qty ELSE 0 END) as reserved")
        )
            ->groupBy('barang_id')
            ->get()
            ->keyBy('barang_id');

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $consumption = StockMovement::where('jenis', 'out')
            ->where('tanggal', '>=', $thirtyDaysAgo)
            ->select('barang_id', DB::raw("SUM(qty) / 30 as avg_daily"))
            ->groupBy('barang_id')
            ->get()
            ->keyBy('barang_id');

        $data = $materials->map(function ($m) use ($sohSummary, $consumption) {
            $soh = $sohSummary[$m->id] ?? (object)['on_hand' => 0, 'reserved' => 0];
            $cons = $consumption[$m->id] ?? (object)['avg_daily' => 0];

            $onHand = (float)$soh->on_hand;
            $reserved = (float)$soh->reserved;
            $available = $onHand - $reserved;
            $avgDaily = (float)$cons->avg_daily;

            $cover = ($avgDaily > 0) ? floor($available / $avgDaily) : 999;

            return [
                'mid' => $m->mid,
                'nama_barang' => $m->nama_barang,
                'uom' => $m->uom,
                'on_hand' => $onHand,
                'reserved' => $reserved,
                'available' => $available,
                'avg_daily' => round($avgDaily, 2),
                'cover_days' => $cover . ' Hari',
                'status_label' => $this->getCoverStatus($cover)
            ];
        });

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $paginatedData = $data->slice($start, $length)->values();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $data->count(),
            'recordsFiltered' => $data->count(),
            'data' => $paginatedData
        ]);
    }

    private function getCoverStatus($days)
    {
        if ($days <= 3) return '<span class="badge bg-danger">Critical</span>';
        if ($days <= 7) return '<span class="badge bg-warning">Warning</span>';
        return '<span class="badge bg-success">Safe</span>';
    }

    public function getPurchasingStockData(Request $request)
    {
        $materials = MasterBarangModel::select('id', 'mid', 'nama_barang', 'uom')->get();

        $sohSummary = StockOnHand::select(
            'barang_id',
            DB::raw("SUM(CASE WHEN status != 'ISSUED' THEN qty ELSE 0 END) as on_hand"),
            DB::raw("SUM(CASE WHEN status = 'RESERVED' THEN qty ELSE 0 END) as reserved")
        )
            ->groupBy('barang_id')
            ->get()
            ->keyBy('barang_id');

        $outstandingSummary = StockInboundDetail::whereNotIn('status', ['COMPLETED'])
            ->select('barang_id', DB::raw("SUM(qty) as qty"))
            ->groupBy('barang_id')
            ->get()
            ->keyBy('barang_id');

        $data = $materials->map(function ($m) use ($sohSummary, $outstandingSummary) {
            $soh = $sohSummary[$m->id] ?? (object)['on_hand' => 0, 'reserved' => 0];
            $os = $outstandingSummary[$m->id] ?? (object)['qty' => 0];

            $onHand = (float)$soh->on_hand;
            $reserved = (float)$soh->reserved;
            $available = $onHand - $reserved;
            $incoming = (float)$os->qty;

            // Mock data for Purchasing logic
            $minStock = 15000; // Placeholder
            $reorderPoint = 20000; // Placeholder

            return [
                'mid' => $m->mid,
                'nama_barang' => $m->nama_barang,
                'uom' => $m->uom,
                'available' => $available,
                'outstanding_po' => $incoming,
                'total_expected' => $available + $incoming,
                'min_stock' => $minStock,
                'reorder_point' => $reorderPoint,
                'status_label' => ($available + $incoming < $reorderPoint)
                    ? '<span class="badge bg-danger">REORDER NOW</span>'
                    : '<span class="badge bg-success">STOK CUKUP</span>'
            ];
        });

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $paginatedData = $data->slice($start, $length)->values();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $data->count(),
            'recordsFiltered' => $data->count(),
            'data' => $paginatedData
        ]);
    }

    public function getMovingAverageData(Request $request)
    {
        $days = intval($request->get('days', 30));
        if (!in_array($days, [20, 30, 40])) {
            $days = 30;
        }

        $search = $request->get('search_mid');

        $query = MasterBarangModel::select('id', 'mid', 'nama_barang', 'uom');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('mid', 'like', '%' . $search . '%')
                  ->orWhere('nama_barang', 'like', '%' . $search . '%');
            });
        }

        $recordsTotal = $query->count();

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        $materials = $query->orderBy('mid')->skip($start)->take($length)->get();

        $materialIds = $materials->pluck('id')->toArray();

        // Query total stock on hand for these materials (available stock = not ISSUED or RESERVED)
        $soh = StockOnHand::whereIn('barang_id', $materialIds)
            ->whereNotIn('status', ['ISSUED', 'RESERVED'])
            ->select('barang_id', DB::raw('SUM(qty) as available_qty'))
            ->groupBy('barang_id')
            ->get()
            ->keyBy('barang_id');

        // Query stock usage (outgoing movements) for these materials in the last X days
        $startDate = Carbon::now()->subDays($days);
        $usage = StockMovement::whereIn('barang_id', $materialIds)
            ->where('jenis', 'out')
            ->where('tanggal', '>=', $startDate)
            ->select('barang_id', DB::raw('SUM(qty) as total_used'))
            ->groupBy('barang_id')
            ->get()
            ->keyBy('barang_id');

        $data = $materials->map(function ($m) use ($soh, $usage, $days) {
            $available = (float)($soh[$m->id]->available_qty ?? 0);
            $used = (float)($usage[$m->id]->total_used ?? 0);
            $avgDaily = $used / $days;
            $coverDays = $avgDaily > 0 ? floor($available / $avgDaily) : 999;

            return [
                'mid' => $m->mid,
                'nama_barang' => $m->nama_barang,
                'uom' => $m->uom,
                'total_used' => $used,
                'avg_daily' => $avgDaily,
                'available' => $available,
                'cover_days' => $coverDays == 999 ? '999+ Hari' : $coverDays . ' Hari',
                'status_label' => $this->getCoverStatus($coverDays)
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data
        ]);
    }
}
