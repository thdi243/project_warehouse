<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\SubmitOutboundRequest;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOutbound;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use App\Models\Wrm\MasterBinModel;
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
                'bin:id,loc_id,bin,kolom,level',
                'bin.location:id,plant,s_loc,gudang,zona',
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
                'barang',
                'bin'
            ])
                ->whereIn('id', collect($request->items)->pluck('id'))
                ->get();

            $headers = [];

            foreach ($details as $detail) {

                $inbound = $detail->inbound;
                $barang  = $detail->barang;
                $bin = $detail->bin;

                // Extract location_id from bin
                $locationId = $bin->loc_id;

                // header outbound berdasarkan no_spb inbound
                if (!isset($headers[$inbound->no_spb])) {

                    $headers[$inbound->no_spb] = StockOutbound::create([
                        'no_spb'       => $inbound->no_spb,
                        'incoming_date' => $inbound->incoming_date,
                        'supplier'     => $inbound->supplier,
                        'issued_date'  => now(),
                        'qty_request'      => $request->qty_request,
                        'catatan'      => $request->catatan,
                        'created_by'   => Auth::id(),
                    ]);
                }

                $header = $headers[$inbound->no_spb];

                // simpan outbound detail dengan bin_id
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

                // stock movement dengan location_id (bukan bin_id)
                StockMovement::create([
                    'barang_id'  => $detail->barang_id,
                    'loc_id'     => $locationId,
                    'tanggal'    => now(),
                    'qty'        => $detail->qty,
                    'jenis'      => 'out',
                    'ref_type'   => 'outbound',
                    'ref_id'     => $detail->id,
                    'created_by' => Auth::id(),
                ]);

                // update stock balance dengan location_id (bukan bin_id)
                $balance = StockBalance::where('barang_id', $detail->barang_id)
                    ->where('loc_id', $locationId)
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
        $query = StockOutbound::with([
            'details.barang:id,mid,nama_barang,uom',
            'details.bin:id,loc_id,bin,kolom,level',
            'details.bin.location:id,plant,s_loc,gudang,zona'
        ]);

        if ($request->group) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('group', $request->group);
            });
        }

        if ($request->jenis_bahan) {
            $query->whereHas('details.barang', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->jenis_bahan . '%');
            });
        }

        if ($request->mid) {
            $query->whereHas('details.barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%' . $request->mid . '%');
            });
        }

        if ($request->date) {
            $query->whereDate('issued_date', $request->date);
        }

        if ($request->supplier) {
            $query->where('supplier', 'like', '%' . $request->supplier . '%');
        }

        $data = $query->latest()->paginate(25);

        return response()->json([
            'status' => true,
            'message' => 'Data outbound berhasil diambil',
            'data' => $data
        ]);
    }

    public function getOutboundDetail($id)
    {
        $details = StockOutboundDetail::with([
            'barang:id,mid,nama_barang,uom',
            'bin:id,loc_id,bin,kolom,level',
            'bin.location:id,plant,gudang,s_loc,zona'
        ])
            ->where('outbound_id', $id)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $details
        ]);
    }

    public function cancelOutbound($id)
    {
        DB::beginTransaction();

        try {

            $outbound = StockOutbound::with('details')->findOrFail($id);

            foreach ($outbound->details as $detail) {

                // kembalikan inbound status
                StockInboundDetail::where([
                    'barang_id' => $detail->barang_id,
                    'pallet_id' => $detail->pallet_id
                ])->update([
                    'status' => 'QI'
                ]);

                // Get bin to extract location_id
                $bin = MasterBinModel::find($detail->loc_id);
                if (!$bin) {
                    throw new \Exception("Bin tidak ditemukan");
                }
                $locationId = $bin->loc_id;

                // kembalikan stock balance dengan location_id
                $balance = StockBalance::where([
                    'barang_id' => $detail->barang_id,
                    'loc_id' => $locationId
                ])->first();

                if ($balance) {
                    $balance->increment('qty', $detail->qty);
                }

                // stock movement reverse dengan location_id
                StockMovement::create([
                    'barang_id' => $detail->barang_id,
                    'loc_id' => $locationId,
                    'tanggal' => now(),
                    'qty' => $detail->qty,
                    'jenis' => 'in',
                    'ref_type' => 'cancel_outbound',
                    'ref_id' => $detail->id,
                    'created_by' => Auth::id(),
                ]);
            }

            $outbound->details()->delete();
            $outbound->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Outbound berhasil dibatalkan'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function printMagicNumber($id)
    {
        $outbound = StockOutbound::with([
            'details.barang:id,mid,nama_barang,uom',
            'details.bin:id,loc_id,bin,kolom,level',
            'details.bin.location:id,plant,s_loc,gudang,zona'
        ])->findOrFail($id);

        return view('wrm.inventory.magic_number', [
            'outbound' => $outbound
        ]);
    }
}
