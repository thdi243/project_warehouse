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
use App\Models\Wrm\Inventory\StockTransferDetail;
use App\Models\Wrm\MasterBinModel;
use App\Models\Wrm\MasterSupplierModel;
use App\Models\Wrm\MasterBarangModel;
use App\Models\P2h\UserForkliftAssignmentModel;
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

        $drivers = UserForkliftAssignmentModel::active()
            ->with('user:id,nama_lengkap,username')
            ->get()
            ->map(function ($assignment) {
                return $assignment->user;
            })
            ->filter()
            ->unique('id')
            ->values();

        return view('wrm.inventory.draft_outbound_data', compact('suppliers', 'drivers'));
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
                'bin.location'
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
                $status = 'RESERVED';

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
                    'status'       => $status,
                    'expired_date' => $detail->expired_date, // Store Expired Date in detail
                    'pallet'       => $detail->pallet,
                    'created_by'   => Auth::id(),
                ]);

                // Update status stock on hand detail
                $detail->update([
                    'status' => $status
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Draft Outbound berhasil disimpan'
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
            'driver:id,nama_lengkap,username',
            'details' => function ($q) {
                $q->whereIn('status', ['RESERVED', 'BA WAITING'])->with([
                    'barang:id,mid,nama_barang,uom',
                    'bin:id,loc_id,kolom,level',
                    'bin.location:id,plant,s_loc,gudang,zona,bin'
                ]);
            }
        ])->whereHas('details', function ($q) {
            $q->whereIn('status', ['RESERVED', 'BA WAITING']);
        });

        if ($request->group) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('group', $request->group)->whereIn('status', ['RESERVED', 'BA WAITING']);
            });
        }

        if ($request->jenis_bahan) {
            $query->whereHas('details.barang', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->jenis_bahan . '%');
            })->whereHas('details', function ($q) {
                $q->whereIn('status', ['RESERVED', 'BA WAITING']);
            });
        }

        if ($request->mid) {
            $query->whereHas('details.barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%' . $request->mid . '%');
            })->whereHas('details', function ($q) {
                $q->whereIn('status', ['RESERVED', 'BA WAITING']);
            });
        }

        if ($request->date) {
            $query->whereDate('issued_date', $request->date);
        }

        if ($request->supplier) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('supplier', 'like', '%' . $request->supplier . '%')->whereIn('status', ['RESERVED', 'BA WAITING']);
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
            ->whereIn('status', ['RESERVED', 'BA WAITING'])
            ->get();

        $header = StockOutbound::find($id);

        return response()->json([
            'status' => true,
            'header' => $header,
            'data' => $details
        ]);
    }

    public function updateOutbound(Request $request, $id)
    {
        $request->validate([
            'no_reservasi' => 'required|string|max:100',
            'tgl_reservasi' => 'required|date',
            'shift' => 'required|string|max:50',
            'catatan' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $outbound = StockOutbound::findOrFail($id);
            $oldNoReservasi = $outbound->no_reservasi;
            $newNoReservasi = $request->no_reservasi;
            $newReservasiDate = Carbon::parse($request->tgl_reservasi)->setTimeFrom(now());

            $outbound->update([
                'no_reservasi' => $newNoReservasi,
                'reservasi_date' => $newReservasiDate,
                'shift' => $request->shift,
                'catatan' => $request->catatan,
                'updated_by' => Auth::id(),
            ]);

            // Sync with StockTransfer if any matching oldNoReservasi
            StockTransfer::where('no_reservasi', $oldNoReservasi)->update([
                'no_reservasi' => $newNoReservasi,
                'tgl_reservasi' => Carbon::parse($request->tgl_reservasi),
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Header draft outbound berhasil diperbarui'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelOutbound($id)
    {
        DB::beginTransaction();

        try {
            $outbound = StockOutbound::with('details')->findOrFail($id);

            foreach ($outbound->details as $detail) {
                if ($detail->status === 'ISSUED' || $detail->status === 'BA WAITING') {
                    // Reverse inventory if it was auto-issued
                    $transferDetail = StockTransferDetail::where('no_barcode', $detail->barcode)
                        ->where('barang_id', $detail->barang_id)
                        ->whereHas('header', function ($q) use ($outbound) {
                            $q->where('no_reservasi', $outbound->no_reservasi);
                        })
                        ->first();

                    if ($transferDetail) {
                        $movement = StockMovement::where('ref_type', 'stock_transfer')
                            ->where('ref_id', $transferDetail->id)
                            ->first();

                        if ($movement) {
                            $balance = StockBalance::where('barang_id', $movement->barang_id)
                                ->where('loc_id', $movement->loc_id)
                                ->first();

                            if ($balance) {
                                $balance->increment('qty', abs($movement->qty));
                                $balance->update(['updated_by' => Auth::id()]);
                            }

                            $movement->delete();
                        }

                        $header = $transferDetail->header;
                        $transferDetail->delete();

                        if ($header && $header->details()->count() === 0) {
                            $header->delete();
                        }
                    }
                }

                // Return inbound status to UNREST (available again)
                StockOnHand::where([
                    'barang_id' => $detail->barang_id,
                    'pallet_id' => $detail->pallet_id
                ])->update([
                    'status' => 'UNREST',
                    'updated_by' => Auth::id()
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

    public function cancelOutboundItem($id)
    {
        DB::beginTransaction();

        try {
            $detail = StockOutboundDetail::findOrFail($id);
            $outbound = StockOutbound::findOrFail($detail->outbound_id);

            if ($detail->status === 'ISSUED' || $detail->status === 'BA WAITING') {
                // Reverse inventory if it was auto-issued
                $transferDetail = StockTransferDetail::where('no_barcode', $detail->barcode)
                    ->where('barang_id', $detail->barang_id)
                    ->whereHas('header', function ($q) use ($outbound) {
                        $q->where('no_reservasi', $outbound->no_reservasi);
                    })
                    ->first();

                if ($transferDetail) {
                    $movement = StockMovement::where('ref_type', 'stock_transfer')
                        ->where('ref_id', $transferDetail->id)
                        ->first();

                    if ($movement) {
                        $balance = StockBalance::where('barang_id', $movement->barang_id)
                            ->where('loc_id', $movement->loc_id)
                            ->first();

                        if ($balance) {
                            $balance->increment('qty', abs($movement->qty));
                            $balance->update(['updated_by' => Auth::id()]);
                        }

                        $movement->delete();
                    }

                    $header = $transferDetail->header;
                    $transferDetail->delete();

                    if ($header && $header->details()->count() === 0) {
                        $header->delete();
                    }
                }
            }

            // Return inbound status to UNREST (available again)
            StockOnHand::where([
                'barang_id' => $detail->barang_id,
                'pallet_id' => $detail->pallet_id
            ])->update([
                'status' => 'UNREST',
                'updated_by' => Auth::id()
            ]);

            $detail->delete();

            $remainingCount = StockOutboundDetail::where('outbound_id', $outbound->id)->count();
            if ($remainingCount === 0) {
                $outbound->delete();
                $deletedHeader = true;
            } else {
                $outbound->decrement('qty_request', $detail->qty);
                $deletedHeader = false;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item outbound berhasil dibatalkan',
                'deleted_header' => $deletedHeader
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelOutboundItems(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:wrm_stock_draft_outbound_details,id'
        ]);

        $ids = $request->ids;

        DB::beginTransaction();

        try {
            $details = StockOutboundDetail::whereIn('id', $ids)->get();

            $outboundIdsToUpdate = [];

            foreach ($details as $detail) {
                $outbound = StockOutbound::findOrFail($detail->outbound_id);
                $outboundIdsToUpdate[$outbound->id] = true;

                if ($detail->status === 'ISSUED' || $detail->status === 'BA WAITING') {
                    // Reverse inventory if it was auto-issued
                    $transferDetail = StockTransferDetail::where('no_barcode', $detail->barcode)
                        ->where('barang_id', $detail->barang_id)
                        ->whereHas('header', function ($q) use ($outbound) {
                            $q->where('no_reservasi', $outbound->no_reservasi);
                        })
                        ->first();

                    if ($transferDetail) {
                        $movement = StockMovement::where('ref_type', 'stock_transfer')
                            ->where('ref_id', $transferDetail->id)
                            ->first();

                        if ($movement) {
                            $balance = StockBalance::where('barang_id', $movement->barang_id)
                                ->where('loc_id', $movement->loc_id)
                                ->first();

                            if ($balance) {
                                $balance->increment('qty', abs($movement->qty));
                                $balance->update(['updated_by' => Auth::id()]);
                            }

                            $movement->delete();
                        }

                        $header = $transferDetail->header;
                        $transferDetail->delete();

                        if ($header && $header->details()->count() === 0) {
                            $header->delete();
                        }
                    }
                }

                // Return inbound status to UNREST (available again)
                StockOnHand::where([
                    'barang_id' => $detail->barang_id,
                    'pallet_id' => $detail->pallet_id
                ])->update([
                    'status' => 'UNREST',
                    'updated_by' => Auth::id()
                ]);

                // Delete the detail item
                $detail->delete();
            }

            // Post-deletion update for headers
            $deletedHeaders = [];
            foreach (array_keys($outboundIdsToUpdate) as $outboundId) {
                $outbound = StockOutbound::find($outboundId);
                if ($outbound) {
                    $remainingCount = StockOutboundDetail::where('outbound_id', $outboundId)->count();
                    if ($remainingCount === 0) {
                        $outbound->delete();
                        $deletedHeaders[] = (int) $outboundId;
                    } else {
                        // Decrement qty_request by the sum of deleted details in this outbound
                        $deletedQty = $details->where('outbound_id', $outboundId)->sum('qty');
                        $outbound->decrement('qty_request', $deletedQty);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item-item outbound terpilih berhasil dibatalkan',
                'deleted_headers' => $deletedHeaders
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
                $q->whereIn('status', ['RESERVED', 'BA WAITING'])->with([
                    'barang:id,mid,nama_barang,uom',
                    'bin:id,loc_id,kolom,level',
                    'bin.location:id,plant,s_loc,gudang,zona,bin'
                ]);
            }
        ])->findOrFail($id);

        return view('wrm.inventory.print_outbound', [
            'outbound' => $outbound
        ]);
    }

    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id'
        ]);

        try {
            $outbound = StockOutbound::findOrFail($id);
            $outbound->update([
                'driver_id' => $request->driver_id,
                'status_transfer' => 'ASSIGNED',
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Driver forklift berhasil di-assign.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function completeTransfer($id)
    {
        DB::beginTransaction();
        try {
            $outbound = StockOutbound::with([
                'details' => function ($q) {
                    $q->where('status', 'RESERVED')->with([
                        'barang',
                        'bin.location'
                    ]);
                }
            ])->findOrFail($id);

            if ($outbound->details->isEmpty()) {
                throw new \Exception('Tidak ada item draft outbound dengan status RESERVED.');
            }

            if (!$outbound->driver_id) {
                throw new \Exception('Driver forklift belum di-assign untuk draft ini.');
            }

            $transferHeader = null;
            $ba_waiting_mids = ['20000812', '20000860', '20001270'];

            foreach ($outbound->details as $detail) {
                $status = in_array($detail->barang->mid, $ba_waiting_mids) ? 'BA WAITING' : 'ISSUED';

                // Update status in draft outbound detail
                $detail->update([
                    'status' => $status,
                    'updated_by' => Auth::id()
                ]);

                // Update status in StockOnHand
                StockOnHand::where([
                    'barang_id' => $detail->barang_id,
                    'pallet_id' => $detail->pallet_id,
                    'status' => 'RESERVED'
                ])->update([
                    'status' => $status,
                    'updated_by' => Auth::id()
                ]);

                // Create StockTransfer header if not exists
                if (!$transferHeader) {
                    $transferHeader = StockTransfer::create([
                        'no_reservasi'  => $outbound->no_reservasi,
                        'tgl_reservasi' => Carbon::parse($outbound->reservasi_date),
                        'tgl_gi'        => now(),
                        'created_by'    => Auth::id(),
                    ]);
                }

                // Create StockTransferDetail
                $transferDetail = StockTransferDetail::create([
                    'transfer_id' => $transferHeader->id,
                    'no_spb'      => $detail->no_spb,
                    'plant'       => $detail->bin->location->plant ?? null,
                    'sloc'        => $detail->bin->location->s_loc ?? null,
                    'barang_id'   => $detail->barang_id,
                    'no_barcode'  => $detail->barcode,
                    'qty_barcode' => $detail->qty,
                    'qty_actual'  => $detail->qty,
                    'uom'         => $detail->barang->uom,
                    'created_by'  => Auth::id(),
                ]);

                // Decrement Stock Balance
                $bin = $detail->bin;
                if ($bin) {
                    $locId = $bin->loc_id;

                    $balance = StockBalance::where('barang_id', $detail->barang_id)
                        ->where('loc_id', $locId)
                        ->first();

                    if ($balance) {
                        $balance->decrement('qty', $detail->qty);
                        $balance->update(['updated_by' => Auth::id()]);
                    }

                    // Record Stock Movement (out)
                    StockMovement::create([
                        'barang_id'  => $detail->barang_id,
                        'loc_id'     => $locId,
                        'tanggal'    => now(),
                        'qty'        => $detail->qty,
                        'jenis'      => 'out',
                        'ref_type'   => 'stock_transfer',
                        'ref_id'     => $transferDetail->id,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Update header status_transfer to COMPLETED
            $outbound->update([
                'status_transfer' => 'COMPLETED',
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Proses pemindahan selesai dan inventory berhasil diperbarui.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
