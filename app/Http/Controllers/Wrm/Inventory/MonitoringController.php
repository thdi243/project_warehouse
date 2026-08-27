<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use App\Models\Wrm\Inventory\StockOutbound;
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
        $mids = StockOnHand::whereNotIn('wrm_stock_on_hand.status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->select('wrm_master_barang.mid', 'wrm_master_barang.nama_barang')
            ->distinct()
            ->orderBy('wrm_master_barang.mid', 'asc')
            ->get();

        $groups = StockOnHand::whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->whereNotNull('group')
            ->where('group', '<>', '')
            ->select('group')
            ->distinct()
            ->orderBy('group', 'asc')
            ->pluck('group');

        $spbs = StockOnHand::whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->whereNotNull('no_spb')
            ->where('no_spb', '<>', '')
            ->select('no_spb')
            ->distinct()
            ->orderBy('no_spb', 'asc')
            ->pluck('no_spb');

        $suppliers = StockOnHand::whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->whereNotNull('supplier')
            ->where('supplier', '<>', '')
            ->select('supplier')
            ->distinct()
            ->orderBy('supplier', 'asc')
            ->pluck('supplier');

        // Dynamic list of years from wrm_stock_inbound
        $inboundYears = DB::table('wrm_stock_inbound')
            ->whereNotNull('incoming_date')
            ->selectRaw('YEAR(incoming_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($inboundYears)) {
            $inboundYears = [intval(date('Y'))];
        }

        // List of Indonesian months
        $inboundMonths = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        // Default to last 3 months
        $defaultMonths = [];
        $defaultYears = [];
        for ($i = 0; $i < 3; $i++) {
            $t = strtotime("-$i months");
            $defaultMonths[] = intval(date('n', $t));
            $defaultYears[] = intval(date('Y', $t));
        }
        $defaultMonths = array_unique($defaultMonths);
        $defaultYears = array_unique($defaultYears);

        return view('wrm.inventory.summary_stock', compact(
            'mids', 'groups', 'spbs', 'suppliers',
            'inboundYears', 'inboundMonths', 'defaultMonths', 'defaultYears'
        ));
    }

    public function getSummaryStockItemData(Request $request)
    {
        $query = StockOnHand::query()
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->selectRaw("
                SUM(CASE WHEN wrm_stock_on_hand.status = 'UNREST' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_unrest,
                SUM(CASE WHEN wrm_stock_on_hand.status = 'QI' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_qi,
                SUM(CASE WHEN wrm_stock_on_hand.status = 'BLOCKED' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_blocked
            ")
            ->whereNotIn('wrm_stock_on_hand.status', ['ISSUED', 'RESERVED', 'BA WAITING']);

        $query->addSelect('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom')
            ->groupBy('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom');
        $sortColumn = 'wrm_master_barang.mid';

        if ($request->filled('mids')) {
            $query->whereIn('wrm_master_barang.mid', (array)$request->mids);
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
        $length = $request->length ?? 15;

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

    public function getSummaryStockSpbData(Request $request)
    {
        $query = StockOnHand::query()
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->selectRaw("
                SUM(CASE WHEN wrm_stock_on_hand.status = 'UNREST' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_unrest,
                SUM(CASE WHEN wrm_stock_on_hand.status = 'QI' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_qi,
                SUM(CASE WHEN wrm_stock_on_hand.status = 'BLOCKED' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_blocked
            ")
            ->whereNotIn('wrm_stock_on_hand.status', ['ISSUED', 'RESERVED', 'BA WAITING']);

        $query->addSelect('wrm_stock_on_hand.no_spb', 'wrm_master_barang.uom')
            ->whereNotNull('wrm_stock_on_hand.no_spb')
            ->groupBy('wrm_stock_on_hand.no_spb', 'wrm_master_barang.uom');
        $sortColumn = 'wrm_stock_on_hand.no_spb';

        if ($request->filled('no_spbs')) {
            $query->whereIn('wrm_stock_on_hand.no_spb', (array)$request->no_spbs);
        }

        if ($request->filled('mids')) {
            $query->whereIn('wrm_master_barang.mid', (array)$request->mids);
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
        $length = $request->length ?? 15;

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

    public function getSummaryStockSupplierData(Request $request)
    {
        $query = StockOnHand::query()
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->selectRaw("
                SUM(CASE WHEN wrm_stock_on_hand.status = 'UNREST' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_unrest,
                SUM(CASE WHEN wrm_stock_on_hand.status = 'QI' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_qi,
                SUM(CASE WHEN wrm_stock_on_hand.status = 'BLOCKED' THEN wrm_stock_on_hand.qty ELSE 0 END) as qty_blocked
            ")
            ->whereNotIn('wrm_stock_on_hand.status', ['ISSUED', 'RESERVED', 'BA WAITING']);

        $query->addSelect('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom', 'wrm_stock_on_hand.supplier')
            ->groupBy('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom', 'wrm_stock_on_hand.supplier');

        $sortColumn = 'wrm_master_barang.mid';

        if ($request->filled('mids')) {
            $query->whereIn('wrm_master_barang.mid', (array)$request->mids);
        }

        if ($request->filled('suppliers')) {
            $query->whereIn('wrm_stock_on_hand.supplier', (array)$request->suppliers);
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
        $length = $request->length ?? 15;

        $data = $query->orderBy($sortColumn)->orderBy('wrm_stock_on_hand.supplier')->skip($start)->take($length)->get();

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

    public function getSummaryStockGroupMeta(Request $request)
    {
        $mids = $request->filled('mids') ? (array)$request->mids : ['20000812', '20000860', '20001270'];

        $groupsQuery = StockOnHand::whereNotIn('wrm_stock_on_hand.status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->whereNotNull('wrm_stock_on_hand.group')
            ->where('wrm_stock_on_hand.group', '<>', '')
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->whereIn('wrm_master_barang.mid', $mids);

        if ($request->filled('groups')) {
            $groupsQuery->whereIn('wrm_stock_on_hand.group', (array)$request->groups);
        }

        $activeGroups = $groupsQuery->select('wrm_stock_on_hand.group')
            ->distinct()
            ->orderBy('wrm_stock_on_hand.group', 'asc')
            ->pluck('wrm_stock_on_hand.group')
            ->toArray();

        $hasNoGroup = StockOnHand::whereNotIn('wrm_stock_on_hand.status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->whereIn('wrm_master_barang.mid', $mids)
            ->where(function ($q) {
                $q->whereNull('wrm_stock_on_hand.group')->orWhere('wrm_stock_on_hand.group', '');
            })
            ->exists();

        return response()->json([
            'active_groups' => $activeGroups,
            'has_no_group' => $hasNoGroup
        ]);
    }

    public function getSummaryStockGroupData(Request $request)
    {
        $mids = $request->filled('mids') ? (array)$request->mids : ['20000812', '20000860', '20001270'];

        // First fetch the active groups to build dynamic selects
        $groupsQuery = StockOnHand::whereNotIn('wrm_stock_on_hand.status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->whereNotNull('wrm_stock_on_hand.group')
            ->where('wrm_stock_on_hand.group', '<>', '')
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->whereIn('wrm_master_barang.mid', $mids);

        if ($request->filled('groups')) {
            $groupsQuery->whereIn('wrm_stock_on_hand.group', (array)$request->groups);
        }

        $activeGroups = $groupsQuery->select('wrm_stock_on_hand.group')
            ->distinct()
            ->orderBy('wrm_stock_on_hand.group', 'asc')
            ->pluck('wrm_stock_on_hand.group')
            ->toArray();

        $hasNoGroup = StockOnHand::whereNotIn('wrm_stock_on_hand.status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->whereIn('wrm_master_barang.mid', $mids)
            ->where(function ($q) {
                $q->whereNull('wrm_stock_on_hand.group')->orWhere('wrm_stock_on_hand.group', '');
            })
            ->exists();

        $selects = [
            'wrm_master_barang.mid',
            'wrm_master_barang.nama_barang',
            'wrm_master_barang.uom',
        ];

        foreach ($activeGroups as $group) {
            $alias = 'group_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $group);
            $selects[] = DB::raw("SUM(CASE WHEN wrm_stock_on_hand.group = '{$group}' THEN wrm_stock_on_hand.qty ELSE 0 END) as `{$alias}`");
        }

        if ($hasNoGroup) {
            $selects[] = DB::raw("SUM(CASE WHEN wrm_stock_on_hand.group IS NULL OR wrm_stock_on_hand.group = '' THEN wrm_stock_on_hand.qty ELSE 0 END) as `group_none`");
        }

        $selects[] = DB::raw("SUM(wrm_stock_on_hand.qty) as total_qty");

        $query = StockOnHand::query()
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->select($selects)
            ->whereNotIn('wrm_stock_on_hand.status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->groupBy('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom');

        $query->whereIn('wrm_master_barang.mid', $mids);

        // Count for pagination
        $recordsTotal = DB::query()
            ->fromSub(clone $query, 'sub')
            ->count();

        // Total per UOM for dynamic groups
        $totalsSelect = ['uom'];
        foreach ($activeGroups as $group) {
            $alias = 'group_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $group);
            $totalsSelect[] = DB::raw("SUM(`{$alias}`) as `{$alias}`");
        }
        if ($hasNoGroup) {
            $totalsSelect[] = DB::raw("SUM(group_none) as group_none");
        }
        $totalsSelect[] = DB::raw("SUM(total_qty) as total_qty");

        $totalsPerUom = DB::query()
            ->fromSub(clone $query, 'sub')
            ->select($totalsSelect)
            ->groupBy('uom')
            ->get();

        $start = $request->start ?? 0;
        $length = $request->length ?? 15;

        $data = $query->orderBy('wrm_master_barang.mid')->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
            'grand_total_per_uom' => $totalsPerUom
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
            ->whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getSummaryPpic()
    {
        // On Hand (Physical)
        $totalSoh = StockOnHand::whereNotIn('status', ['ISSUED', 'BA WAITING'])->sum('qty');

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
            ->whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING']);

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
        $length = $request->length ?? 15;
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
        $length = $request->length ?? 15;
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
        $length = $request->length ?? 15;
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
        $length = $request->length ?? 15;
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
            DB::raw("SUM(CASE WHEN status NOT IN ('ISSUED', 'BA WAITING') THEN qty ELSE 0 END) as on_hand"),
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
        $length = $request->length ?? 15;
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
            DB::raw("SUM(CASE WHEN status NOT IN ('ISSUED', 'BA WAITING') THEN qty ELSE 0 END) as on_hand"),
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
        $length = $request->length ?? 15;
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

        $query = MasterBarangModel::select('id', 'mid', 'nama_barang', 'uom');

        if ($request->filled('mids')) {
            $query->whereIn('mid', (array)$request->mids);
        }

        $recordsTotal = $query->count();

        $start = $request->start ?? 0;
        $length = $request->length ?? 15;
        $materials = $query->orderBy('mid')->skip($start)->take($length)->get();

        $materialIds = $materials->pluck('id')->toArray();

        // Query total stock on hand for these materials (available stock = not ISSUED or RESERVED)
        $soh = StockOnHand::whereIn('barang_id', $materialIds)
            ->whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING'])
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

    public function getSummaryStockInboundMonthlyMeta(Request $request)
    {
        $mids = $request->filled('mids') ? (array)$request->mids : ['20000812', '20000860', '20001270'];

        $defaultMonths = [];
        $defaultYears = [];
        for ($i = 0; $i < 3; $i++) {
            $t = strtotime("-$i months");
            $defaultMonths[] = intval(date('n', $t));
            $defaultYears[] = intval(date('Y', $t));
        }
        $defaultMonths = array_unique($defaultMonths);
        $defaultYears = array_unique($defaultYears);

        $years = $request->filled('years') ? (array)$request->years : $defaultYears;
        $months = $request->filled('months') ? (array)$request->months : $defaultMonths;

        // Generate combinations directly from filters!
        $combinations = [];
        foreach ($years as $yr) {
            foreach ($months as $mo) {
                $yr = intval($yr);
                $mo = intval($mo);
                $ym = sprintf('%04d-%02d', $yr, $mo);
                $combinations[$ym] = [
                    'ym' => $ym,
                    'year' => $yr,
                    'month' => $mo
                ];
            }
        }
        ksort($combinations);

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $meta = [];
        foreach ($combinations as $ym => $comb) {
            $meta[] = [
                'ym' => $ym,
                'year' => $comb['year'],
                'month' => $comb['month'],
                'label' => ($monthNames[$comb['month']] ?? '') . ' ' . $comb['year']
            ];
        }

        return response()->json([
            'active_month_years' => $meta
        ]);
    }

    public function getSummaryStockInboundMonthlyData(Request $request)
    {
        $mids = $request->filled('mids') ? (array)$request->mids : ['20000812', '20000860', '20001270'];

        $defaultMonths = [];
        $defaultYears = [];
        for ($i = 0; $i < 3; $i++) {
            $t = strtotime("-$i months");
            $defaultMonths[] = intval(date('n', $t));
            $defaultYears[] = intval(date('Y', $t));
        }
        $defaultMonths = array_unique($defaultMonths);
        $defaultYears = array_unique($defaultYears);

        $years = $request->filled('years') ? (array)$request->years : $defaultYears;
        $months = $request->filled('months') ? (array)$request->months : $defaultMonths;

        // Generate combinations directly from filters!
        $combinations = [];
        foreach ($years as $yr) {
            foreach ($months as $mo) {
                $yr = intval($yr);
                $mo = intval($mo);
                $ym = sprintf('%04d-%02d', $yr, $mo);
                $combinations[$ym] = [
                    'ym' => $ym,
                    'year' => $yr,
                    'month' => $mo
                ];
            }
        }
        ksort($combinations);

        $activeMonthYears = [];
        foreach ($combinations as $ym => $comb) {
            $activeMonthYears[] = (object)[
                'ym' => $ym,
                'year' => $comb['year'],
                'month' => $comb['month']
            ];
        }

        $selects = [
            'wrm_master_barang.mid',
            'wrm_master_barang.nama_barang',
            'wrm_master_barang.uom',
        ];

        foreach ($activeMonthYears as $my) {
            $alias = 'ym_' . $my->year . '_' . sprintf('%02d', $my->month);
            $selects[] = DB::raw("SUM(CASE WHEN YEAR(wrm_stock_inbound.incoming_date) = {$my->year} AND MONTH(wrm_stock_inbound.incoming_date) = {$my->month} THEN wrm_stock_inbound_details.qty ELSE 0 END) as `{$alias}`");
        }

        $selects[] = DB::raw("SUM(wrm_stock_inbound_details.qty) as total_qty");

        $query = DB::table('wrm_stock_inbound_details')
            ->join('wrm_stock_inbound', 'wrm_stock_inbound_details.inbound_id', '=', 'wrm_stock_inbound.id')
            ->join('wrm_master_barang', 'wrm_stock_inbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->select($selects)
            ->whereIn('wrm_master_barang.mid', $mids);

        if (!empty($years)) {
            $query->whereIn(DB::raw('YEAR(wrm_stock_inbound.incoming_date)'), $years);
        }
        if (!empty($months)) {
            $query->whereIn(DB::raw('MONTH(wrm_stock_inbound.incoming_date)'), $months);
        }

        $query->groupBy('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom');

        // Count for pagination
        $recordsTotal = DB::query()
            ->fromSub(clone $query, 'sub')
            ->count();

        // Totals per UOM for dynamic month columns
        $totalsSelect = ['uom'];
        foreach ($activeMonthYears as $my) {
            $alias = 'ym_' . $my->year . '_' . sprintf('%02d', $my->month);
            $totalsSelect[] = DB::raw("SUM(`{$alias}`) as `{$alias}`");
        }
        $totalsSelect[] = DB::raw("SUM(total_qty) as total_qty");

        $totalsPerUom = DB::query()
            ->fromSub(clone $query, 'sub')
            ->select($totalsSelect)
            ->groupBy('uom')
            ->get();

        $start = $request->start ?? 0;
        $length = $request->length ?? 15;

        $data = $query->orderBy('wrm_master_barang.mid')->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
            'grand_total_per_uom' => $totalsPerUom
        ]);
    }

    public function indexSummaryTransfer()
    {
        $mids = StockOutboundDetail::join('wrm_master_barang', 'wrm_stock_draft_outbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->select('wrm_master_barang.mid', 'wrm_master_barang.nama_barang')
            ->distinct()
            ->orderBy('wrm_master_barang.mid', 'asc')
            ->get();

        $groups = StockOutboundDetail::whereNotNull('group')
            ->where('group', '<>', '')
            ->select('group')
            ->distinct()
            ->orderBy('group', 'asc')
            ->pluck('group');

        $spbs = StockOutboundDetail::whereNotNull('no_spb')
            ->where('no_spb', '<>', '')
            ->select('no_spb')
            ->distinct()
            ->orderBy('no_spb', 'asc')
            ->pluck('no_spb');

        // Dynamic list of years from wrm_stock_draft_outbound
        $outboundYears = DB::table('wrm_stock_draft_outbound')
            ->whereNotNull('reservasi_date')
            ->selectRaw('YEAR(reservasi_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($outboundYears)) {
            $outboundYears = [intval(date('Y'))];
        }

        // List of Indonesian months
        $outboundMonths = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        // Default to last 3 months
        $defaultMonths = [];
        $defaultYears = [];
        for ($i = 0; $i < 3; $i++) {
            $t = strtotime("-$i months");
            $defaultMonths[] = intval(date('n', $t));
            $defaultYears[] = intval(date('Y', $t));
        }
        $defaultMonths = array_unique($defaultMonths);
        $defaultYears = array_unique($defaultYears);

        return view('wrm.inventory.summary_transfer', compact(
            'mids', 'groups', 'spbs',
            'outboundYears', 'outboundMonths', 'defaultMonths', 'defaultYears'
        ));
    }

    public function getSummaryTransferItemData(Request $request)
    {
        $query = StockOutboundDetail::query()
            ->join('wrm_master_barang', 'wrm_stock_draft_outbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->join('wrm_stock_draft_outbound', 'wrm_stock_draft_outbound_details.outbound_id', '=', 'wrm_stock_draft_outbound.id')
            ->selectRaw("
                SUM(CASE WHEN wrm_stock_draft_outbound_details.status = 'RESERVED' THEN wrm_stock_draft_outbound_details.qty ELSE 0 END) as qty_reserved,
                SUM(CASE WHEN wrm_stock_draft_outbound_details.status = 'BA WAITING' THEN wrm_stock_draft_outbound_details.qty ELSE 0 END) as qty_ba_waiting,
                SUM(CASE WHEN wrm_stock_draft_outbound_details.status = 'ISSUED' THEN wrm_stock_draft_outbound_details.qty ELSE 0 END) as qty_issued
            ");

        $query->addSelect('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom')
            ->groupBy('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom');
        $sortColumn = 'wrm_master_barang.mid';

        if ($request->filled('mids')) {
            $query->whereIn('wrm_master_barang.mid', (array)$request->mids);
        }

        if ($request->filled('start_date')) {
            $query->where('wrm_stock_draft_outbound.reservasi_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('wrm_stock_draft_outbound.reservasi_date', '<=', $request->end_date);
        }

        // Count for pagination and grand totals
        $recordsTotal = DB::query()
            ->fromSub(clone $query, 'sub')
            ->count();

        $totalsPerUom = DB::query()
            ->fromSub(clone $query, 'sub')
            ->select(
                'uom',
                DB::raw("SUM(qty_reserved) as total_reserved"),
                DB::raw("SUM(qty_ba_waiting) as total_ba_waiting"),
                DB::raw("SUM(qty_issued) as total_issued")
            )
            ->groupBy('uom')
            ->get();

        $start = $request->start ?? 0;
        $length = $request->length ?? 15;

        $data = $query->orderBy($sortColumn)->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
            'grand_total_per_uom' => $totalsPerUom->map(function ($item) {
                return [
                    'uom' => $item->uom,
                    'reserved' => $item->total_reserved ?? 0,
                    'ba_waiting' => $item->total_ba_waiting ?? 0,
                    'issued' => $item->total_issued ?? 0,
                    'all' => ($item->total_reserved ?? 0) + ($item->total_ba_waiting ?? 0) + ($item->total_issued ?? 0)
                ];
            })
        ]);
    }

    public function getSummaryTransferSpbData(Request $request)
    {
        $query = StockOutboundDetail::query()
            ->join('wrm_master_barang', 'wrm_stock_draft_outbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->join('wrm_stock_draft_outbound', 'wrm_stock_draft_outbound_details.outbound_id', '=', 'wrm_stock_draft_outbound.id')
            ->selectRaw("
                SUM(CASE WHEN wrm_stock_draft_outbound_details.status = 'RESERVED' THEN wrm_stock_draft_outbound_details.qty ELSE 0 END) as qty_reserved,
                SUM(CASE WHEN wrm_stock_draft_outbound_details.status = 'BA WAITING' THEN wrm_stock_draft_outbound_details.qty ELSE 0 END) as qty_ba_waiting,
                SUM(CASE WHEN wrm_stock_draft_outbound_details.status = 'ISSUED' THEN wrm_stock_draft_outbound_details.qty ELSE 0 END) as qty_issued
            ");

        $query->addSelect('wrm_stock_draft_outbound_details.no_spb', 'wrm_master_barang.uom')
            ->whereNotNull('wrm_stock_draft_outbound_details.no_spb')
            ->groupBy('wrm_stock_draft_outbound_details.no_spb', 'wrm_master_barang.uom');
        $sortColumn = 'wrm_stock_draft_outbound_details.no_spb';

        if ($request->filled('no_spbs')) {
            $query->whereIn('wrm_stock_draft_outbound_details.no_spb', (array)$request->no_spbs);
        }

        if ($request->filled('mids')) {
            $query->whereIn('wrm_master_barang.mid', (array)$request->mids);
        }

        if ($request->filled('start_date')) {
            $query->where('wrm_stock_draft_outbound.reservasi_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('wrm_stock_draft_outbound.reservasi_date', '<=', $request->end_date);
        }

        // Count for pagination and grand totals
        $recordsTotal = DB::query()
            ->fromSub(clone $query, 'sub')
            ->count();

        $totalsPerUom = DB::query()
            ->fromSub(clone $query, 'sub')
            ->select(
                'uom',
                DB::raw("SUM(qty_reserved) as total_reserved"),
                DB::raw("SUM(qty_ba_waiting) as total_ba_waiting"),
                DB::raw("SUM(qty_issued) as total_issued")
            )
            ->groupBy('uom')
            ->get();

        $start = $request->start ?? 0;
        $length = $request->length ?? 15;

        $data = $query->orderBy($sortColumn)->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
            'grand_total_per_uom' => $totalsPerUom->map(function ($item) {
                return [
                    'uom' => $item->uom,
                    'reserved' => $item->total_reserved ?? 0,
                    'ba_waiting' => $item->total_ba_waiting ?? 0,
                    'issued' => $item->total_issued ?? 0,
                    'all' => ($item->total_reserved ?? 0) + ($item->total_ba_waiting ?? 0) + ($item->total_issued ?? 0)
                ];
            })
        ]);
    }

    public function getSummaryTransferGroupMeta(Request $request)
    {
        $mids = $request->filled('mids') ? (array)$request->mids : ['20000812', '20000860', '20001270'];

        $groupsQuery = StockOutboundDetail::whereNotNull('wrm_stock_draft_outbound_details.group')
            ->where('wrm_stock_draft_outbound_details.group', '<>', '')
            ->join('wrm_master_barang', 'wrm_stock_draft_outbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->join('wrm_stock_draft_outbound', 'wrm_stock_draft_outbound_details.outbound_id', '=', 'wrm_stock_draft_outbound.id')
            ->whereIn('wrm_master_barang.mid', $mids);

        if ($request->filled('groups')) {
            $groupsQuery->whereIn('wrm_stock_draft_outbound_details.group', (array)$request->groups);
        }

        if ($request->filled('start_date')) {
            $groupsQuery->where('wrm_stock_draft_outbound.reservasi_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $groupsQuery->where('wrm_stock_draft_outbound.reservasi_date', '<=', $request->end_date);
        }

        $activeGroups = $groupsQuery->select('wrm_stock_draft_outbound_details.group')
            ->distinct()
            ->orderBy('wrm_stock_draft_outbound_details.group', 'asc')
            ->pluck('wrm_stock_draft_outbound_details.group')
            ->toArray();

        $hasNoGroupQuery = StockOutboundDetail::join('wrm_master_barang', 'wrm_stock_draft_outbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->join('wrm_stock_draft_outbound', 'wrm_stock_draft_outbound_details.outbound_id', '=', 'wrm_stock_draft_outbound.id')
            ->whereIn('wrm_master_barang.mid', $mids)
            ->where(function ($q) {
                $q->whereNull('wrm_stock_draft_outbound_details.group')->orWhere('wrm_stock_draft_outbound_details.group', '');
            });

        if ($request->filled('start_date')) {
            $hasNoGroupQuery->where('wrm_stock_draft_outbound.reservasi_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $hasNoGroupQuery->where('wrm_stock_draft_outbound.reservasi_date', '<=', $request->end_date);
        }

        $hasNoGroup = $hasNoGroupQuery->exists();

        return response()->json([
            'active_groups' => $activeGroups,
            'has_no_group' => $hasNoGroup
        ]);
    }

    public function getSummaryTransferGroupData(Request $request)
    {
        $mids = $request->filled('mids') ? (array)$request->mids : ['20000812', '20000860', '20001270'];

        $groupsQuery = StockOutboundDetail::whereNotNull('wrm_stock_draft_outbound_details.group')
            ->where('wrm_stock_draft_outbound_details.group', '<>', '')
            ->join('wrm_master_barang', 'wrm_stock_draft_outbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->join('wrm_stock_draft_outbound', 'wrm_stock_draft_outbound_details.outbound_id', '=', 'wrm_stock_draft_outbound.id')
            ->whereIn('wrm_master_barang.mid', $mids);

        if ($request->filled('groups')) {
            $groupsQuery->whereIn('wrm_stock_draft_outbound_details.group', (array)$request->groups);
        }

        if ($request->filled('start_date')) {
            $groupsQuery->where('wrm_stock_draft_outbound.reservasi_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $groupsQuery->where('wrm_stock_draft_outbound.reservasi_date', '<=', $request->end_date);
        }

        $activeGroups = $groupsQuery->select('wrm_stock_draft_outbound_details.group')
            ->distinct()
            ->orderBy('wrm_stock_draft_outbound_details.group', 'asc')
            ->pluck('wrm_stock_draft_outbound_details.group')
            ->toArray();

        $hasNoGroupQuery = StockOutboundDetail::join('wrm_master_barang', 'wrm_stock_draft_outbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->join('wrm_stock_draft_outbound', 'wrm_stock_draft_outbound_details.outbound_id', '=', 'wrm_stock_draft_outbound.id')
            ->whereIn('wrm_master_barang.mid', $mids)
            ->where(function ($q) {
                $q->whereNull('wrm_stock_draft_outbound_details.group')->orWhere('wrm_stock_draft_outbound_details.group', '');
            });

        if ($request->filled('start_date')) {
            $hasNoGroupQuery->where('wrm_stock_draft_outbound.reservasi_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $hasNoGroupQuery->where('wrm_stock_draft_outbound.reservasi_date', '<=', $request->end_date);
        }

        $hasNoGroup = $hasNoGroupQuery->exists();

        $selects = [
            'wrm_master_barang.mid',
            'wrm_master_barang.nama_barang',
            'wrm_master_barang.uom',
        ];

        foreach ($activeGroups as $group) {
            $alias = 'group_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $group);
            $selects[] = DB::raw("SUM(CASE WHEN wrm_stock_draft_outbound_details.group = '{$group}' THEN wrm_stock_draft_outbound_details.qty ELSE 0 END) as `{$alias}`");
        }

        if ($hasNoGroup) {
            $selects[] = DB::raw("SUM(CASE WHEN wrm_stock_draft_outbound_details.group IS NULL OR wrm_stock_draft_outbound_details.group = '' THEN wrm_stock_draft_outbound_details.qty ELSE 0 END) as `group_none`");
        }

        $selects[] = DB::raw("SUM(wrm_stock_draft_outbound_details.qty) as total_qty");

        $query = StockOutboundDetail::query()
            ->join('wrm_master_barang', 'wrm_stock_draft_outbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->join('wrm_stock_draft_outbound', 'wrm_stock_draft_outbound_details.outbound_id', '=', 'wrm_stock_draft_outbound.id')
            ->select($selects)
            ->groupBy('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom');

        $query->whereIn('wrm_master_barang.mid', $mids);

        if ($request->filled('start_date')) {
            $query->where('wrm_stock_draft_outbound.reservasi_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('wrm_stock_draft_outbound.reservasi_date', '<=', $request->end_date);
        }

        // Count for pagination
        $recordsTotal = DB::query()
            ->fromSub(clone $query, 'sub')
            ->count();

        // Total per UOM for dynamic groups
        $totalsSelect = ['uom'];
        foreach ($activeGroups as $group) {
            $alias = 'group_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $group);
            $totalsSelect[] = DB::raw("SUM(`{$alias}`) as `{$alias}`");
        }
        if ($hasNoGroup) {
            $totalsSelect[] = DB::raw("SUM(group_none) as group_none");
        }
        $totalsSelect[] = DB::raw("SUM(total_qty) as total_qty");

        $totalsPerUom = DB::query()
            ->fromSub(clone $query, 'sub')
            ->select($totalsSelect)
            ->groupBy('uom')
            ->get();

        $start = $request->start ?? 0;
        $length = $request->length ?? 15;

        $data = $query->orderBy('wrm_master_barang.mid')->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
            'grand_total_per_uom' => $totalsPerUom
        ]);
    }

    public function getSummaryTransferMonthlyMeta(Request $request)
    {
        $mids = $request->filled('mids') ? (array)$request->mids : ['20000812', '20000860', '20001270'];

        $defaultMonths = [];
        $defaultYears = [];
        for ($i = 0; $i < 3; $i++) {
            $t = strtotime("-$i months");
            $defaultMonths[] = intval(date('n', $t));
            $defaultYears[] = intval(date('Y', $t));
        }
        $defaultMonths = array_unique($defaultMonths);
        $defaultYears = array_unique($defaultYears);

        $years = $request->filled('years') ? (array)$request->years : $defaultYears;
        $months = $request->filled('months') ? (array)$request->months : $defaultMonths;

        // Generate combinations directly from filters!
        $combinations = [];
        foreach ($years as $yr) {
            foreach ($months as $mo) {
                $yr = intval($yr);
                $mo = intval($mo);
                $ym = sprintf('%04d-%02d', $yr, $mo);
                $combinations[$ym] = [
                    'ym' => $ym,
                    'year' => $yr,
                    'month' => $mo
                ];
            }
        }
        ksort($combinations);

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $meta = [];
        foreach ($combinations as $ym => $comb) {
            $meta[] = [
                'ym' => $ym,
                'year' => $comb['year'],
                'month' => $comb['month'],
                'label' => ($monthNames[$comb['month']] ?? '') . ' ' . $comb['year']
            ];
        }

        return response()->json([
            'active_month_years' => $meta
        ]);
    }

    public function getSummaryTransferMonthlyData(Request $request)
    {
        $mids = $request->filled('mids') ? (array)$request->mids : ['20000812', '20000860', '20001270'];

        $defaultMonths = [];
        $defaultYears = [];
        for ($i = 0; $i < 3; $i++) {
            $t = strtotime("-$i months");
            $defaultMonths[] = intval(date('n', $t));
            $defaultYears[] = intval(date('Y', $t));
        }
        $defaultMonths = array_unique($defaultMonths);
        $defaultYears = array_unique($defaultYears);

        $years = $request->filled('years') ? (array)$request->years : $defaultYears;
        $months = $request->filled('months') ? (array)$request->months : $defaultMonths;

        // Generate combinations directly from filters!
        $combinations = [];
        foreach ($years as $yr) {
            foreach ($months as $mo) {
                $yr = intval($yr);
                $mo = intval($mo);
                $ym = sprintf('%04d-%02d', $yr, $mo);
                $combinations[$ym] = [
                    'ym' => $ym,
                    'year' => $yr,
                    'month' => $mo
                ];
            }
        }
        ksort($combinations);

        $activeMonthYears = [];
        foreach ($combinations as $ym => $comb) {
            $activeMonthYears[] = (object)[
                'ym' => $ym,
                'year' => $comb['year'],
                'month' => $comb['month']
            ];
        }

        $selects = [
            'wrm_master_barang.mid',
            'wrm_master_barang.nama_barang',
            'wrm_master_barang.uom',
        ];

        foreach ($activeMonthYears as $my) {
            $alias = 'ym_' . $my->year . '_' . sprintf('%02d', $my->month);
            $selects[] = DB::raw("SUM(CASE WHEN YEAR(wrm_stock_draft_outbound.reservasi_date) = {$my->year} AND MONTH(wrm_stock_draft_outbound.reservasi_date) = {$my->month} THEN wrm_stock_draft_outbound_details.qty ELSE 0 END) as `{$alias}`");
        }

        $selects[] = DB::raw("SUM(wrm_stock_draft_outbound_details.qty) as total_qty");

        $query = DB::table('wrm_stock_draft_outbound_details')
            ->join('wrm_stock_draft_outbound', 'wrm_stock_draft_outbound_details.outbound_id', '=', 'wrm_stock_draft_outbound.id')
            ->join('wrm_master_barang', 'wrm_stock_draft_outbound_details.barang_id', '=', 'wrm_master_barang.id')
            ->select($selects)
            ->whereIn('wrm_master_barang.mid', $mids);

        if ($request->filled('start_date')) {
            $query->where('wrm_stock_draft_outbound.reservasi_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('wrm_stock_draft_outbound.reservasi_date', '<=', $request->end_date);
        }

        if (!empty($years)) {
            $query->whereIn(DB::raw('YEAR(wrm_stock_draft_outbound.reservasi_date)'), $years);
        }
        if (!empty($months)) {
            $query->whereIn(DB::raw('MONTH(wrm_stock_draft_outbound.reservasi_date)'), $months);
        }

        $query->groupBy('wrm_master_barang.mid', 'wrm_master_barang.nama_barang', 'wrm_master_barang.uom');

        // Count for pagination
        $recordsTotal = DB::query()
            ->fromSub(clone $query, 'sub')
            ->count();

        // Totals per UOM for dynamic month columns
        $totalsSelect = ['uom'];
        foreach ($activeMonthYears as $my) {
            $alias = 'ym_' . $my->year . '_' . sprintf('%02d', $my->month);
            $totalsSelect[] = DB::raw("SUM(`{$alias}`) as `{$alias}`");
        }
        $totalsSelect[] = DB::raw("SUM(total_qty) as total_qty");

        $totalsPerUom = DB::query()
            ->fromSub(clone $query, 'sub')
            ->select($totalsSelect)
            ->groupBy('uom')
            ->get();

        $start = $request->start ?? 0;
        $length = $request->length ?? 15;

        $data = $query->orderBy('wrm_master_barang.mid')->skip($start)->take($length)->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
            'grand_total_per_uom' => $totalsPerUom
        ]);
    }
}
