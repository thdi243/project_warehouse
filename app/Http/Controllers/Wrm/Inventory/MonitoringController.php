<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Wrm\Inventory\StockInbound;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wrm\Inventory\StockOutbound;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use App\Models\Wrm\Inventory\StockTransfer;
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

    public function getSummaryPpic()
    {
        $totalSoh = StockOnHand::whereNotIn('status', ['ISSUED', 'RESERVED'])->sum('qty');
        $todayInbound = StockMovement::whereDate('tanggal', Carbon::today())
            ->where('jenis', 'in')
            ->where('ref_type', 'inbound')
            ->sum('qty');
        $draftOutbound = StockOutboundDetail::where('status', 'RESERVED')->count();

        return response()->json([
            'status' => true,
            'data' => [
                'total_soh' => number_format($totalSoh, 0, ',', '.'),
                'today_inbound' => number_format($todayInbound, 0, ',', '.'),
                'draft_outbound' => $draftOutbound,
            ]
        ]);
    }

    public function getSummaryPurchasing()
    {
        $totalSoh = StockOnHand::whereNotIn('status', ['ISSUED', 'RESERVED'])->sum('qty');

        // Aging > 30 days
        $agingLimit = Carbon::now()->subDays(30);
        $agingStock = StockOnHand::whereNotIn('status', ['ISSUED', 'RESERVED'])
            ->where('incoming_date', '<=', $agingLimit)
            ->count();

        $totalSuppliers = StockOnHand::whereNotIn('status', ['ISSUED', 'RESERVED'])->distinct('supplier')->count('supplier');

        return response()->json([
            'status' => true,
            'data' => [
                'total_soh' => number_format($totalSoh, 0, ',', '.'),
                'aging_stock' => $agingStock,
                'total_suppliers' => $totalSuppliers,
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
        $query = StockOutboundDetail::with(['outbound', 'barang', 'bin.location']);

        $recordsTotal = $query->count();

        if ($request->search['value']) {
            $search = $request->search['value'];
            $query->whereHas('outbound', function ($q) use ($search) {
                $q->where('no_outbound', 'like', "%$search%");
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
}
