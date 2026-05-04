<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\SubmitOutboundRequest;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wrm\Inventory\StockOutbound;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use App\Models\Wrm\Inventory\StockTransfer;
use App\Models\Wrm\MasterBinModel;
use App\Models\Wrm\MasterSupplierModel;
use App\Models\Wrm\MasterBarangModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class OutboundController extends Controller
{
    public function formOutbound()
    {
        return view('wrm.inventory.draft_outbound');
    }

    public function dataOutbound()
    {
        $suppliers = MasterSupplierModel::orderBy('nama')->get();
        return view('wrm.inventory.draft_outbound_data', compact('suppliers'));
    }

    public function searchOutbound(Request $request)
    {
        $query = StockOnHand::select('wrm_stock_on_hand.*')
            ->with([
                'barang:id,mid,nama_barang,uom',
                'bin:id,loc_id,kolom,level',
                'bin.location:id,plant,s_loc,gudang,zona,bin',
            ])
            ->where('wrm_stock_on_hand.status', '=', 'UNREST');

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

        // if ($request->status) {
        //     $query->where('status', $request->status);
        // }

        // filter group
        if ($request->group) {
            $query->where('group', $request->group);
        }

        // filter location
        if ($request->location) {
            $query->whereHas('bin.location', function ($q) use ($request) {
                $q->where('plant', 'like', '%' . $request->location . '%')
                    ->orWhere('gudang', 'like', '%' . $request->location . '%')
                    ->orWhere('bin', 'like', '%' . $request->location . '%');
            });
        }

        // urutkan FIFO (incoming paling lama)
        $query->orderBy('wrm_stock_on_hand.incoming_date', 'asc')
            ->orderBy('wrm_stock_on_hand.pallet_id', 'asc');

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
            $details = StockOnHand::with([
                'barang',
                'bin'
            ])
                ->whereIn('id', collect($request->items)->pluck('id'))
                ->get();

            // Create Single Header for Reservation
            $header = StockOutbound::create([
                'no_reservasi'      => $request->no_reservasi,
                'shift'             => $request->shift,
                'reservasi_date'    => Carbon::parse($request->tgl_reservasi)->setTimeFrom(now()),
                'qty_request'       => $request->qty_request,
                'catatan'           => $request->catatan,
                'checklist_kondisi' => $request->checklist_kondisi ? json_encode($request->checklist_kondisi) : null,
                'created_by'        => Auth::id(),
            ]);

            foreach ($details as $detail) {
                // Save outbound detail
                StockOutboundDetail::create([
                    'outbound_id'  => $header->id,
                    'no_spb'       => $detail->no_spb,
                    'supplier'     => $detail->supplier, // Store Supplier in detail
                    'barang_id'    => $detail->barang_id,
                    'barcode'      => $detail->barcode ?? null,
                    'pallet_id'    => $detail->pallet_id,
                    'incoming_date' => $detail->incoming_date,
                    'group'        => $detail->group ?? null,
                    'qty'          => $detail->qty,
                    'loc_id'       => $detail->loc_id,
                    'status'       => 'RESERVED',
                    'expired_date' => $detail->expired_date, // Store Expired Date in detail
                    'pallet'       => $detail->pallet,
                    'created_by'   => Auth::id(),
                ]);


                // Update status inbound detail to RESERVED
                $detail->update([
                    'status' => 'RESERVED'
                ]);

                // NOTE: No stock movement or balance update in RESERVATION concept
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Draft Outbound (Reservasi) berhasil disimpan'
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
            'details' => function ($q) {
                $q->where('status', 'RESERVED')->with([
                    'barang:id,mid,nama_barang,uom',
                    'bin:id,loc_id,kolom,level',
                    'bin.location:id,plant,s_loc,gudang,zona,bin'
                ]);
            }
        ])->whereHas('details', function ($q) {
            $q->where('status', 'RESERVED');
        });

        if ($request->group) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('group', $request->group)->where('status', 'RESERVED');
            });
        }

        if ($request->jenis_bahan) {
            $query->whereHas('details.barang', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->jenis_bahan . '%');
            })->whereHas('details', function ($q) {
                $q->where('status', 'RESERVED');
            });
        }

        if ($request->mid) {
            $query->whereHas('details.barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%' . $request->mid . '%');
            })->whereHas('details', function ($q) {
                $q->where('status', 'RESERVED');
            });
        }

        if ($request->date) {
            $query->whereDate('issued_date', $request->date);
        }

        if ($request->supplier) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('supplier', 'like', '%' . $request->supplier . '%')->where('status', 'RESERVED');
            });
        }

        if ($request->no_reservasi) {
            $query->where('no_reservasi', 'like', '%' . $request->no_reservasi . '%');
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
            'bin:id,loc_id,kolom,level',
            'bin.location:id,plant,gudang,s_loc,zona,bin'
        ])
            ->where('outbound_id', $id)
            ->where('status', 'RESERVED')
            ->get();

        $header = StockOutbound::find($id);

        return response()->json([
            'status' => true,
            'header' => $header,
            'data' => $details
        ]);
    }

    public function cancelOutbound($id)
    {
        DB::beginTransaction();

        try {
            $outbound = StockOutbound::with('details')->findOrFail($id);

            foreach ($outbound->details as $detail) {
                // Return inbound status to UNREST (available again)
                StockOnHand::where([
                    'barang_id' => $detail->barang_id,
                    'pallet_id' => $detail->pallet_id
                ])->update([
                    'status' => 'UNREST'
                ]);

                // NOTE: No stock balance increment or movement reverse in RESERVATION concept
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
            'details' => function ($q) {
                $q->where('status', 'RESERVED')->with([
                    'barang:id,mid,nama_barang,uom',
                    'bin:id,loc_id,kolom,level',
                    'bin.location:id,plant,s_loc,gudang,zona,bin'
                ]);
            }
        ])->findOrFail($id);

        return view('wrm.inventory.magic_number', [
            'outbound' => $outbound
        ]);
    }
}
