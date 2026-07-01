<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\SubmitOutboundRequest;
use App\Models\P2h\UserForkliftAssignmentModel;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockByDate;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wrm\Inventory\StockOutbound;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use App\Models\Wrm\Inventory\StockTransfer;
use App\Models\Wrm\Inventory\StockTransferDetail;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterBinModel;
use App\Models\Wrm\MasterSupplierModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OutboundController extends Controller
{
    public function formOutbound()
    {
        $mids = StockOnHand::where('wrm_stock_on_hand.status', 'UNREST')
            ->join('wrm_master_barang', 'wrm_stock_on_hand.barang_id', '=', 'wrm_master_barang.id')
            ->select('wrm_master_barang.mid', 'wrm_master_barang.nama_barang')
            ->distinct()
            ->orderBy('wrm_master_barang.mid', 'asc')
            ->get();

        $groups = StockOnHand::where('status', 'UNREST')
            ->whereNotNull('group')
            ->where('group', '<>', '')
            ->select('group')
            ->distinct()
            ->orderBy('group', 'asc')
            ->pluck('group');

        return view('wrm.inventory.draft_outbound', compact('mids', 'groups'));
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

            // Find existing header or create a new one based on no_reservasi and date portion of reservasi_date
            $header = StockOutbound::where('no_reservasi', $request->no_reservasi)
                ->whereDate('reservasi_date', Carbon::parse($request->tgl_reservasi)->toDateString())
                ->first();

            $batchId = uniqid('batch_');

            if ($header) {
                $header->update([
                    'shift'             => $request->shift,
                    'catatan'           => $request->catatan,
                    'checklist_kondisi' => $request->checklist_kondisi ? json_encode($request->checklist_kondisi) : null,
                    'updated_by'        => Auth::id(),
                ]);
            } else {
                $header = StockOutbound::create([
                    'no_reservasi'      => $request->no_reservasi,
                    'shift'             => $request->shift,
                    'reservasi_date'    => Carbon::parse($request->tgl_reservasi)->setTimeFrom(now()),
                    'qty_request'       => $request->qty_request,
                    'catatan'           => $request->catatan,
                    'checklist_kondisi' => $request->checklist_kondisi ? json_encode($request->checklist_kondisi) : null,
                    'created_by'        => Auth::id(),
                ]);
            }

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
                    'qty_request'  => $request->qty_request,
                    'batch_id'     => $batchId,
                    'loc_id'       => $detail->loc_id,
                    'status'       => $status,
                    'expired_date' => $detail->expired_date, // Store Expired Date in detail
                    'pallet'       => $detail->pallet,
                    'soh_id'       => $detail->id,
                    'created_by'   => Auth::id(),
                ]);

                // Update status stock on hand detail
                $detail->update([
                    'status' => $status
                ]);
            }

            $header->recalculateQtyRequest();

            $this->syncHeaderStatusTransfer($header);

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
            'bin.location:id,plant,gudang,s_loc,zona,bin',
            'driver:id,nama_lengkap,username'
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
                // Return inbound status to UNREST (available again) only if RESERVED or BA WAITING
                if ($detail->status === 'RESERVED' || $detail->status === 'BA WAITING') {
                    if ($detail->soh_id) {
                        StockOnHand::where('id', $detail->soh_id)
                            ->whereIn('status', ['RESERVED', 'BA WAITING'])
                            ->update([
                                'status' => 'UNREST',
                                'updated_by' => Auth::id()
                            ]);
                    } else {
                        if ($detail->barcode) {
                            StockOnHand::where('barcode', $detail->barcode)
                                ->whereIn('status', ['RESERVED', 'BA WAITING'])
                                ->update([
                                    'status' => 'UNREST',
                                    'updated_by' => Auth::id()
                                ]);
                        } else {
                            StockOnHand::where([
                                'barang_id' => $detail->barang_id,
                                'pallet_id' => $detail->pallet_id,
                                'no_spb'    => $detail->no_spb
                            ])
                                ->whereIn('status', ['RESERVED', 'BA WAITING'])
                                ->update([
                                    'status' => 'UNREST',
                                    'updated_by' => Auth::id()
                                ]);
                        }
                    }

                    // Delete the draft detail item
                    $detail->delete();
                }
            }

            // Check if there are any remaining details (e.g. ISSUED items)
            $remainingCount = $outbound->details()->count();
            if ($remainingCount === 0) {
                $outbound->delete();
            } else {
                $outbound->recalculateQtyRequest();
                $this->syncHeaderStatusTransfer($outbound);
            }

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

            // Return inbound status to UNREST (available again) only if RESERVED or BA WAITING
            if ($detail->status === 'RESERVED' || $detail->status === 'BA WAITING') {
                if ($detail->soh_id) {
                    StockOnHand::where('id', $detail->soh_id)
                        ->whereIn('status', ['RESERVED', 'BA WAITING'])
                        ->update([
                            'status' => 'UNREST',
                            'updated_by' => Auth::id()
                        ]);
                } else {
                    if ($detail->barcode) {
                        StockOnHand::where('barcode', $detail->barcode)
                            ->whereIn('status', ['RESERVED', 'BA WAITING'])
                            ->update([
                                'status' => 'UNREST',
                                'updated_by' => Auth::id()
                            ]);
                    } else {
                        StockOnHand::where([
                            'barang_id' => $detail->barang_id,
                            'pallet_id' => $detail->pallet_id,
                            'no_spb'    => $detail->no_spb
                        ])
                            ->whereIn('status', ['RESERVED', 'BA WAITING'])
                            ->update([
                                'status' => 'UNREST',
                                'updated_by' => Auth::id()
                            ]);
                    }
                }

                $detail->delete();

                $remainingCount = StockOutboundDetail::where('outbound_id', $outbound->id)->count();
                if ($remainingCount === 0) {
                    $outbound->delete();
                    $deletedHeader = true;
                } else {
                    $outbound->recalculateQtyRequest();
                    $this->syncHeaderStatusTransfer($outbound);
                    $deletedHeader = false;
                }
            } else {
                throw new \Exception('Item yang sudah ISSUED tidak bisa dibatalkan dari draft.');
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
                if ($detail->status === 'RESERVED' || $detail->status === 'BA WAITING') {
                    $outbound = StockOutbound::findOrFail($detail->outbound_id);
                    $outboundIdsToUpdate[$outbound->id] = true;

                    if ($detail->soh_id) {
                        StockOnHand::where('id', $detail->soh_id)
                            ->whereIn('status', ['RESERVED', 'BA WAITING'])
                            ->update([
                                'status' => 'UNREST',
                                'updated_by' => Auth::id()
                            ]);
                    } else {
                        if ($detail->barcode) {
                            StockOnHand::where('barcode', $detail->barcode)
                                ->whereIn('status', ['RESERVED', 'BA WAITING'])
                                ->update([
                                    'status' => 'UNREST',
                                    'updated_by' => Auth::id()
                                ]);
                        } else {
                            StockOnHand::where([
                                'barang_id' => $detail->barang_id,
                                'pallet_id' => $detail->pallet_id,
                                'no_spb'    => $detail->no_spb
                            ])
                                ->whereIn('status', ['RESERVED', 'BA WAITING'])
                                ->update([
                                    'status' => 'UNREST',
                                    'updated_by' => Auth::id()
                                ]);
                        }
                    }

                    // Delete the detail item
                    $detail->delete();
                } else {
                    throw new \Exception('Beberapa item yang dipilih sudah ISSUED dan tidak bisa dibatalkan.');
                }
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
                        $outbound->recalculateQtyRequest();
                        $this->syncHeaderStatusTransfer($outbound);
                        $deletedHeaders = false;
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

    public function assignDriverPage($id)
    {
        $outbound = StockOutbound::with([
            'details' => function ($q) {
                $q->whereIn('status', ['RESERVED', 'BA WAITING'])->with([
                    'barang:id,mid,nama_barang,uom',
                    'bin:id,loc_id,kolom,level',
                    'bin.location:id,plant,s_loc,gudang,zona,bin',
                    'driver:id,nama_lengkap,username'
                ]);
            }
        ])->findOrFail($id);

        $drivers = UserForkliftAssignmentModel::active()
            ->with('user:id,nama_lengkap,username')
            ->get()
            ->map(function ($assignment) {
                return $assignment->user;
            })
            ->filter()
            ->unique('id')
            ->values();

        return view('wrm.inventory.draft_outbound_assign', compact('outbound', 'drivers'));
    }

    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id'
        ]);

        DB::beginTransaction();
        try {
            $outbound = StockOutbound::findOrFail($id);

            // Assign driver to ALL details that are RESERVED
            $outbound->details()->where('status', 'RESERVED')->update([
                'driver_id' => $request->driver_id,
                'updated_by' => Auth::id()
            ]);

            // Sync status_transfer & driver_id of the header
            $this->syncHeaderStatusTransfer($outbound);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Driver forklift berhasil di-assign.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function assignDriverItems(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:wrm_stock_draft_outbound_details,id',
            'driver_id' => 'required|exists:users,id'
        ]);

        DB::beginTransaction();
        try {
            // Update selected details with driver
            StockOutboundDetail::whereIn('id', $request->ids)
                ->where('status', 'RESERVED')
                ->update([
                    'driver_id' => $request->driver_id,
                    'updated_by' => Auth::id()
                ]);

            // Sync status_transfer for all affected headers
            $outboundIds = StockOutboundDetail::whereIn('id', $request->ids)
                ->pluck('outbound_id')
                ->unique();

            foreach ($outboundIds as $outboundId) {
                $outbound = StockOutbound::find($outboundId);
                if ($outbound) {
                    $this->syncHeaderStatusTransfer($outbound);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Driver forklift berhasil di-assign ke item terpilih.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function syncHeaderStatusTransfer(StockOutbound $outbound)
    {
        $outbound->load('details');
        $details = $outbound->details;

        if ($details->isEmpty()) {
            $statusTransfer = 'PENDING';
        } else {
            $allCompleted = $details->every(function ($detail) {
                return $detail->status !== 'RESERVED';
            });

            if ($allCompleted) {
                $statusTransfer = 'COMPLETED';
            } else {
                // Draft is ASSIGNED only if ALL remaining RESERVED details have a driver assigned.
                // If there's even one RESERVED detail without a driver, it is PENDING.
                $allDriversAssigned = $details->where('status', 'RESERVED')->every(function ($detail) {
                    return !is_null($detail->driver_id);
                });

                $statusTransfer = $allDriversAssigned ? 'ASSIGNED' : 'PENDING';
            }
        }

        $outbound->update([
            'status_transfer' => $statusTransfer,
            'updated_by' => Auth::id()
        ]);
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

            // Only process details that have a driver assigned
            $detailsToProcess = $outbound->details->filter(function ($detail) {
                return !is_null($detail->driver_id);
            });

            if ($detailsToProcess->isEmpty()) {
                throw new \Exception('Tidak ada item draft outbound yang sudah di-assign driver dengan status RESERVED.');
            }

            $transferHeader = null;
            $ba_waiting_mids = ['20000812', '20000860', '20001270'];

            foreach ($detailsToProcess as $detail) {
                $status = in_array(trim((string)$detail->barang->mid), $ba_waiting_mids) ? 'BA WAITING' : 'ISSUED';

                // Update status in draft outbound detail
                $detail->update([
                    'status' => $status,
                    'updated_by' => Auth::id()
                ]);

                // Delete or Update status in StockOnHand
                if ($status === 'ISSUED') {
                    if ($detail->soh_id) {
                        StockOnHand::where('id', $detail->soh_id)->delete();
                    } else {
                        StockOnHand::where([
                            'barang_id' => $detail->barang_id,
                            'pallet_id' => $detail->pallet_id,
                            'status'    => 'RESERVED'
                        ])->delete();
                    }
                } else {
                    if ($detail->soh_id) {
                        StockOnHand::where('id', $detail->soh_id)->update([
                            'status' => $status,
                            'updated_by' => Auth::id()
                        ]);
                    } else {
                        StockOnHand::where([
                            'barang_id' => $detail->barang_id,
                            'pallet_id' => $detail->pallet_id,
                            'status'    => 'RESERVED'
                        ])->update([
                            'status' => $status,
                            'updated_by' => Auth::id()
                        ]);
                    }
                }

                if ($status === 'ISSUED') {
                    // Create or find StockTransfer header
                    if (!$transferHeader) {
                        $transferHeader = StockTransfer::where('no_reservasi', $outbound->no_reservasi)->first();

                        if (!$transferHeader) {
                            $transferHeader = StockTransfer::create([
                                'no_reservasi'  => $outbound->no_reservasi,
                                'tgl_reservasi' => Carbon::parse($outbound->reservasi_date),
                                'tgl_gi'        => now(),
                                'created_by'    => Auth::id(),
                            ]);
                        }
                    }

                    // Create StockTransferDetail
                    $transferDetail = StockTransferDetail::create([
                        'transfer_id' => $transferHeader->id,
                        'no_spb'      => $detail->no_spb,
                        'plant'       => $detail->bin->location->plant ?? null,
                        'sloc'        => $detail->bin->location->s_loc ?? null,
                        'barang_id'   => $detail->barang_id,
                        'no_barcode'  => $detail->barcode,
                        'pallet_id'   => $detail->pallet_id,
                        'tgl_gr'      => $detail->incoming_date,
                        'qty_barcode' => $detail->qty,
                        'qty_actual'  => $detail->qty,
                        'uom'         => $detail->barang->uom,
                        'created_by'  => Auth::id(),
                    ]);

                    $bin = $detail->bin;
                    if ($bin) {
                        $locId = $bin->loc_id;

                        // Recalculate Stock Balance and update StockByDate
                        StockBalance::recalculate($detail->barang_id);
                        StockByDate::updateStockByDate($detail->barang_id, now());

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
                } else if ($status === 'BA WAITING') {
                    StockBalance::recalculate($detail->barang_id);
                    StockByDate::updateStockByDate($detail->barang_id, now());
                }
            }

            // Sync header status_transfer & driver_id
            $this->syncHeaderStatusTransfer($outbound);

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

    public function forkliftJobs(Request $request)
    {
        return view('wrm.inventory.forklift_jobs');
    }

    public function forkliftJobsData(Request $request)
    {
        $driverId = Auth::id();

        // Fetch details assigned to the current driver with status RESERVED
        $query = StockOutboundDetail::with([
            'outbound:id,no_reservasi,reservasi_date,shift,catatan',
            'barang:id,mid,nama_barang,uom',
            'bin:id,loc_id,kolom,level',
            'bin.location:id,plant,s_loc,gudang,zona,bin'
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'RESERVED');

        // Optional search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_spb', 'like', "%{$search}%")
                    ->orWhere('pallet_id', 'like', "%{$search}%")
                    ->orWhereHas('barang', function ($qb) use ($search) {
                        $qb->where('mid', 'like', "%{$search}%")
                            ->orWhere('nama_barang', 'like', "%{$search}%");
                    })
                    ->orWhereHas('outbound', function ($qo) use ($search) {
                        $qo->where('no_reservasi', 'like', "%{$search}%");
                    });
            });
        }

        $jobs = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $jobs
        ]);
    }

    public function forkliftJobsComplete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:wrm_stock_draft_outbound_details,id'
        ]);

        $detailIds = $request->ids;

        DB::beginTransaction();
        try {
            // Fetch the details that are assigned to the current user and are RESERVED
            $details = StockOutboundDetail::with(['barang', 'bin.location', 'outbound'])
                ->whereIn('id', $detailIds)
                ->where('driver_id', Auth::id())
                ->where('status', 'RESERVED')
                ->get();

            if ($details->isEmpty()) {
                throw new \Exception('Tidak ada item valid yang dipilih.');
            }

            $ba_waiting_mids = ['20000812', '20000860', '20001270'];
            $detailsGrouped = $details->groupBy('outbound_id');

            foreach ($detailsGrouped as $outboundId => $outboundDetails) {
                $outbound = StockOutbound::find($outboundId);
                if (!$outbound) continue;

                $transferHeader = null;

                foreach ($outboundDetails as $detail) {
                    $status = in_array(trim((string)$detail->barang->mid), $ba_waiting_mids) ? 'BA WAITING' : 'ISSUED';

                    // Update status in draft outbound detail
                    $detail->update([
                        'status' => $status,
                        'updated_by' => Auth::id()
                    ]);

                    // Delete or Update status in StockOnHand
                    if ($status === 'ISSUED') {
                        if ($detail->soh_id) {
                            StockOnHand::where('id', $detail->soh_id)->delete();
                        } else {
                            StockOnHand::where([
                                'barang_id' => $detail->barang_id,
                                'pallet_id' => $detail->pallet_id,
                                'status'    => 'RESERVED'
                            ])->delete();
                        }
                    } else {
                        if ($detail->soh_id) {
                            StockOnHand::where('id', $detail->soh_id)->update([
                                'status' => $status,
                                'updated_by' => Auth::id()
                            ]);
                        } else {
                            StockOnHand::where([
                                'barang_id' => $detail->barang_id,
                                'pallet_id' => $detail->pallet_id,
                                'status'    => 'RESERVED'
                            ])->update([
                                'status' => $status,
                                'updated_by' => Auth::id()
                            ]);
                        }
                    }

                    if ($status === 'ISSUED') {
                        // Create or find StockTransfer header
                        if (!$transferHeader) {
                            $transferHeader = StockTransfer::where('no_reservasi', $outbound->no_reservasi)->first();

                            if (!$transferHeader) {
                                $transferHeader = StockTransfer::create([
                                    'no_reservasi'  => $outbound->no_reservasi,
                                    'tgl_reservasi' => Carbon::parse($outbound->reservasi_date),
                                    'tgl_gi'        => now(),
                                    'created_by'    => Auth::id(),
                                ]);
                            }
                        }

                        // Create StockTransferDetail
                        $transferDetail = StockTransferDetail::create([
                            'transfer_id' => $transferHeader->id,
                            'no_spb'      => $detail->no_spb,
                            'plant'       => $detail->bin->location->plant ?? null,
                            'sloc'        => $detail->bin->location->s_loc ?? null,
                            'barang_id'   => $detail->barang_id,
                            'no_barcode'  => $detail->barcode,
                            'pallet_id'   => $detail->pallet_id,
                            'tgl_gr'      => $detail->incoming_date,
                            'qty_barcode' => $detail->qty,
                            'qty_actual'  => $detail->qty,
                            'uom'         => $detail->barang->uom,
                            'created_by'  => Auth::id(),
                        ]);

                        $bin = $detail->bin;
                        if ($bin) {
                            $locId = $bin->loc_id;

                            // Recalculate Stock Balance and update StockByDate
                            StockBalance::recalculate($detail->barang_id);
                            StockByDate::updateStockByDate($detail->barang_id, now());

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
                }

                // Sync header status_transfer & driver_id
                $this->syncHeaderStatusTransfer($outbound);
            }

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
