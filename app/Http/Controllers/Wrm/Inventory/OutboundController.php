<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\SubmitOutboundRequest;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOutbound;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OutboundController extends Controller
{
    public function formOutbound()
    {
        return view('wrm.inventory.outbound');
    }

    public function dataOutbound()
    {
        return view('wrm.inventory.outbound_data');
    }

    public function searchOutbound(Request $request)
    {
        $query = StockInboundDetail::select('wrm_stock_inbound_details.*')
            ->join('wrm_stock_inbound', 'wrm_stock_inbound.id', '=', 'wrm_stock_inbound_details.inbound_id')
            ->with([
                'inbound:id,no_spb,incoming_date',
                'barang:id,mid,nama_barang,uom',
                'location:id,gudang,bin,s_loc,plant',
            ])
            ->where('wrm_stock_inbound_details.status', '!=', 'ISSUED');

        // filter MID
        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%' . $request->mid . '%');
            });
        }

        // filter nama barang
        // if ($request->nama_barang) {
        //     $query->whereHas('barang', function ($q) use ($request) {
        //         $q->where('nama_barang', 'like', '%' . $request->nama_barang . '%');
        //     });
        // }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // filter group
        if ($request->group) {
            $query->where('group', $request->group);
        }

        // urutkan FIFO (incoming paling lama)
        $query->orderBy('wrm_stock_inbound.incoming_date', 'asc');

        // $data = $query->paginate(15);

        $data = $query->get();

        return response()->json([
            'status' => true,
            'message' => 'Stock outbound berhasil diambil',
            'data' => $data
        ]);
    }

    public function submitOutbound(SubmitOutboundRequest $request)
    {
        DB::beginTransaction();

        try {

            $details = StockInboundDetail::with([
                'inbound',
                'barang'
            ])
                ->whereIn('id', collect($request->items)->pluck('id'))
                ->get();

            $headers = [];

            foreach ($details as $detail) {

                $inbound = $detail->inbound;
                $barang  = $detail->barang;

                // header outbound berdasarkan no_spb inbound
                if (!isset($headers[$inbound->no_spb])) {

                    $headers[$inbound->no_spb] = StockOutbound::create([
                        'no_spb'       => $inbound->no_spb,
                        'incoming_date' => $inbound->incoming_date,
                        'supplier'     => $inbound->supplier,
                        'issued_date'  => now(),
                        'created_by'   => Auth::id(),
                    ]);
                }

                $header = $headers[$inbound->no_spb];

                // simpan outbound detail
                StockOutboundDetail::create([
                    'outbound_id' => $header->id,
                    'barang_id'   => $detail->barang_id,
                    'pallet_id'   => $detail->pallet_id,
                    'group'       => $detail->group,
                    'qty'         => $detail->qty,
                    'loc_id'      => $detail->loc_id,
                    'status'      => 'ISSUED',
                    'pallet'      => $detail->pallet,
                    'created_by'  => Auth::id(),
                ]);

                // update status inbound detail
                $detail->update([
                    'status' => 'ISSUED'
                ]);

                // stock movement
                StockMovement::create([
                    'barang_id'  => $detail->barang_id,
                    'loc_id'     => $detail->loc_id,
                    'tanggal'    => now(),
                    'qty'        => $detail->qty,
                    'jenis'      => 'out',
                    'ref_type'   => 'outbound',
                    'ref_id'     => $detail->id,
                    'created_by' => Auth::id(),
                ]);

                // update stock balance
                $balance = StockBalance::where('barang_id', $detail->barang_id)
                    ->where('loc_id', $detail->loc_id)
                    ->first();

                if (!$balance) {
                    throw new \Exception("Stock balance tidak ditemukan");
                }

                if ($balance->qty < $detail->qty) {
                    throw new \Exception("Stock tidak cukup");
                }

                $balance->decrement('qty', $detail->qty);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Outboun inventory stock berhasil disimpan'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        $query = StockOutboundDetail::with([
            'barang:id,mid,nama_barang,uom',
            'location:id,gudang,bin,s_loc,plant',
            'outbound:id,no_spb,incoming_date,supplier,issued_date'
        ])
            ->where('status', 'ISSUED');

        if ($request->group) {
            $query->where('group', $request->group);
        }

        if ($request->jenis_bahan) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->jenis_bahan . '%');
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%' . $request->mid . '%');
            });
        }

        // filter issued_date
        if ($request->date) {
            $query->whereHas('outbound', function ($q) use ($request) {
                $q->whereDate('issued_date', $request->date);
            });
        }

        if ($request->supplier) {
            $query->whereHas('outbound', function ($q) use ($request) {
                $q->where('supplier', 'like', '%' . $request->supplier . '%');
            });
        }

        $data = $query->paginate(25);

        return response()->json([
            'status' => true,
            'message' => 'Data stock outbound inventory berhasil diambil',
            'data' => $data
        ]);
    }
}
