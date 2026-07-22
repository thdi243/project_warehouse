<?php

namespace App\Http\Controllers\Wrm\StockOpname;

use App\Http\Controllers\Controller;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wrm\Inventory\StockOutboundDetail;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\StockOpname\WrmSohModel;
use App\Models\Wrm\StockOpname\WrmSoModel;
use App\Models\Wrm\StockOpname\WrmSoStatusModel;
use App\Models\Wrm\StockOpname\WrmSoSummariesModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WrmStockOnHandController extends Controller
{
    public function getList(Request $request)
    {
        $searchTerm = $request->input('search');
        $jenisSo = $request->input('jenis_so', 'cycle_count');
        $perPage = 100;
        $today = now()->toDateString();

        $query = WrmSohModel::query()
            ->select('wrm_soh.*')
            ->leftJoin('wrm_master_barang', 'wrm_soh.barang_id', '=', 'wrm_master_barang.id')
            ->leftJoin('users', 'wrm_soh.user_id', '=', 'users.id');

        $query->whereDate('wrm_soh.created_at', $today)
            ->where('wrm_soh.jenis_so', $jenisSo);

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('wrm_master_barang.mid', 'like', '%' . $searchTerm . '%')
                    ->orWhere('wrm_master_barang.nama_barang', 'like', '%' . $searchTerm . '%')
                    ->orWhere('wrm_soh.no_spb', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->with([
            'barang:id,mid,nama_barang,uom,qty_kg',
            'user:id,username'
        ]);

        $data = $query->orderBy('wrm_soh.id', 'desc')->paginate($perPage);

        $soStatus = WrmSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        $isFinished = $soStatus && $soStatus->status === 'finished';

        $responseData = $data->toArray();
        $responseData['is_finished'] = $isFinished;

        return response()->json($responseData);
    }

    public function getBarang(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $jenisData = $request->input('jenis_data');

        if ($tanggal && $jenisData) {
            if ($jenisData === 'inbound') {
                $barangIds = StockInboundDetail::whereHas('inbound', function ($q) use ($tanggal) {
                    $q->whereDate('incoming_date', $tanggal);
                })
                    ->distinct()
                    ->pluck('barang_id');
            } else {
                $barangIds = StockOutboundDetail::whereHas('outbound', function ($q) use ($tanggal) {
                    $q->whereDate('reservasi_date', $tanggal)
                        ->where('status_transfer', 'COMPLETED');
                })
                    ->distinct()
                    ->pluck('barang_id');
            }

            $barang = MasterBarangModel::whereIn('id', $barangIds)->select('id', 'mid', 'nama_barang', 'uom', 'qty_kg')->get();
        } else {
            $barang = MasterBarangModel::select('id', 'mid', 'nama_barang', 'uom', 'qty_kg')->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    public function getSpbList(Request $request)
    {
        $barangId = $request->input('barang_id');
        $mid = $request->input('mid');
        $tanggal = $request->input('tanggal');
        $jenisData = $request->input('jenis_data');

        if ($tanggal && $jenisData && ($barangId || $mid)) {
            if (!$barangId && $mid) {
                $barang = MasterBarangModel::where('mid', $mid)->first();
                $barangId = $barang?->id;
            }

            if ($jenisData === 'inbound') {
                $query = StockInboundDetail::whereHas('inbound', function ($q) use ($tanggal) {
                    $q->whereDate('incoming_date', $tanggal);
                });

                if (is_array($barangId)) {
                    $query->whereIn('barang_id', $barangId);
                } else {
                    $query->where('barang_id', $barangId);
                }

                $spbList = $query->join('wrm_stock_inbound', 'wrm_stock_inbound.id', '=', 'wrm_stock_inbound_details.inbound_id')
                    ->distinct()
                    ->orderBy('wrm_stock_inbound.no_spb', 'asc')
                    ->pluck('wrm_stock_inbound.no_spb');
            } else {
                $query = StockOutboundDetail::whereHas('outbound', function ($q) use ($tanggal) {
                    $q->whereDate('reservasi_date', $tanggal)
                        ->where('status_transfer', 'COMPLETED');
                });

                if (is_array($barangId)) {
                    $query->whereIn('barang_id', $barangId);
                } else {
                    $query->where('barang_id', $barangId);
                }

                $spbList = $query->whereNotNull('no_spb')
                    ->where('no_spb', '!=', '')
                    ->distinct()
                    ->orderBy('no_spb', 'asc')
                    ->pluck('no_spb');
            }
        } else {
            $query = StockOnHand::whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING']);

            if ($barangId) {
                $query->where('barang_id', $barangId);
            } elseif ($mid) {
                $query->whereHas('barang', function ($q) use ($mid) {
                    $q->where('mid', $mid);
                });
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Material ID / Barang ID diperlukan.'
                ], 422);
            }

            $spbList = $query->whereNotNull('no_spb')
                ->where('no_spb', '!=', '')
                ->distinct()
                ->orderBy('no_spb', 'asc')
                ->pluck('no_spb');
        }

        return response()->json([
            'status' => 'success',
            'data' => $spbList
        ]);
    }

    public function getPalletList(Request $request)
    {
        $barangId = $request->input('barang_id');
        $noSpb = $request->input('no_spb');
        $tanggal = $request->input('tanggal');
        $jenisData = $request->input('jenis_data');

        if ($tanggal && $jenisData && $barangId && $noSpb) {
            if ($jenisData === 'inbound') {
                $query = StockInboundDetail::with('inbound:id,no_spb')
                    ->whereHas('inbound', function ($q) use ($tanggal, $noSpb) {
                        $q->whereDate('incoming_date', $tanggal);
                        if (is_array($noSpb)) {
                            $q->whereIn('no_spb', $noSpb);
                        } else {
                            $q->where('no_spb', $noSpb);
                        }
                    });

                if (is_array($barangId)) {
                    $query->whereIn('barang_id', $barangId);
                } else {
                    $query->where('barang_id', $barangId);
                }

                $palletList = $query->whereNotNull('pallet_id')
                    ->where('pallet_id', '!=', '')
                    ->get(['id', 'pallet_id', 'inbound_id'])
                    ->map(fn($d) => [
                        'no_spb'    => $d->inbound->no_spb ?? '-',
                        'pallet_id' => $d->pallet_id,
                    ])
                    ->unique(fn($i) => $i['no_spb'] . '-' . $i['pallet_id'])
                    ->sortBy(fn($i) => $i['no_spb'] . '-' . $i['pallet_id'])
                    ->values();
            } else {
                $query = StockOutboundDetail::whereHas('outbound', function ($q) use ($tanggal) {
                    $q->whereDate('reservasi_date', $tanggal)
                        ->where('status_transfer', 'COMPLETED');
                });

                if (is_array($barangId)) {
                    $query->whereIn('barang_id', $barangId);
                } else {
                    $query->where('barang_id', $barangId);
                }

                if (is_array($noSpb)) {
                    $query->whereIn('no_spb', $noSpb);
                } else {
                    $query->where('no_spb', $noSpb);
                }

                $palletList = $query->whereNotNull('pallet_id')
                    ->where('pallet_id', '!=', '')
                    ->get(['no_spb', 'pallet_id'])
                    ->map(fn($d) => [
                        'no_spb'    => $d->no_spb,
                        'pallet_id' => $d->pallet_id,
                    ])
                    ->unique(fn($i) => $i['no_spb'] . '-' . $i['pallet_id'])
                    ->sortBy(fn($i) => $i['no_spb'] . '-' . $i['pallet_id'])
                    ->values();
            }
        } else {
            $palletList = [];
        }

        return response()->json([
            'status' => 'success',
            'data'   => $palletList
        ]);
    }

    public function getPalletQty(Request $request)
    {
        $barangId = $request->input('barang_id');
        $noSpb = $request->input('no_spb');
        $pallet_id = $request->input('pallet_id');
        $tanggal = $request->input('tanggal');
        $jenisData = $request->input('jenis_data');

        $qty = 0;
        $status = 'UNREST';

        if ($tanggal && $jenisData && $barangId && $noSpb && $pallet_id) {
            if ($jenisData === 'inbound') {
                $detail = StockInboundDetail::whereHas('inbound', function ($q) use ($tanggal, $noSpb) {
                    $q->whereDate('incoming_date', $tanggal)
                        ->where('no_spb', $noSpb);
                })
                    ->where('barang_id', $barangId)
                    ->where('pallet_id', $pallet_id)
                    ->first();

                if ($detail) {
                    $qty = $detail->qty;
                    $status = $detail->status;
                }
            } else {
                $detail = StockOutboundDetail::whereHas('outbound', function ($q) use ($tanggal) {
                    $q->whereDate('reservasi_date', $tanggal)
                        ->where('status_transfer', 'COMPLETED');
                })
                    ->where('barang_id', $barangId)
                    ->where('no_spb', $noSpb)
                    ->where('pallet_id', $pallet_id)
                    ->first();

                if ($detail) {
                    $qty = $detail->qty;
                    $status = $detail->status;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'qty' => $qty,
            'status_stock' => $status
        ]);
    }

    public function show(string $id)
    {
        $soh = WrmSohModel::with('barang:id,mid,nama_barang')->find($id);

        if (!$soh) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data SOH tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $soh
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_so' => 'required|string|in:cycle_count,monthly',
        ]);

        $today = now()->toDateString();
        $jenisSo = $request->jenis_so;
        $periodeText = $jenisSo === 'monthly' ? 'bulan ini' : 'hari ini';
        $soStatus = WrmSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status' => false,
                'message' => "Tidak dapat memproses SOH karena Stock Opname {$periodeText} telah selesai (finished)."
            ], 422);
        }

        if ($jenisSo === 'monthly') {
            $currentYear = now()->year;
            $currentMonth = now()->month;
            $hasMonthlySo = WrmSoStatusModel::where('jenis_so', 'monthly')
                ->whereYear('tgl_opname', $currentYear)
                ->whereMonth('tgl_opname', $currentMonth)
                ->whereDate('tgl_opname', '!=', $today)
                ->exists();
            if ($hasMonthlySo) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat memproses SOH karena Stock Opname Monthly untuk bulan ini sudah pernah berjalan.'
                ], 422);
            }
        }

        if ($request->has('tanggal') && $request->has('jenis_data') && $request->has('barang_id') && $request->has('no_spb')) {
            $tanggal   = $request->input('tanggal');
            $jenisData = $request->input('jenis_data');
            $barangId  = $request->input('barang_id');
            $noSpb     = $request->input('no_spb');
            $palletId  = $request->input('pallet_id'); // may be array or null

            if ($jenisData === 'inbound') {
                $query = StockInboundDetail::whereHas('inbound', function ($q) use ($tanggal, $noSpb) {
                    $q->whereDate('incoming_date', $tanggal);
                    if (is_array($noSpb)) {
                        $q->whereIn('no_spb', $noSpb);
                    } else {
                        $q->where('no_spb', $noSpb);
                    }
                });

                if (is_array($barangId)) {
                    $query->whereIn('barang_id', $barangId);
                } else {
                    $query->where('barang_id', $barangId);
                }

                // Filter by selected pallets if provided
                if (!empty($palletId)) {
                    $palletId = is_array($palletId) ? $palletId : [$palletId];
                    $query->whereIn('pallet_id', $palletId);
                }

                $details = $query->get();

                $items = $details->map(function ($d) {
                    return [
                        'barang_id' => $d->barang_id,
                        'no_spb'    => $d->inbound->no_spb ?? '-',
                        'pallet_id' => $d->pallet_id ?? '-',
                        'qty'       => $d->qty,
                        'status'    => $d->status
                    ];
                })->toArray();
            } else {
                $query = StockOutboundDetail::whereHas('outbound', function ($q) use ($tanggal) {
                    $q->whereDate('reservasi_date', $tanggal)
                        ->where('status_transfer', 'COMPLETED');
                });

                if (is_array($barangId)) {
                    $query->whereIn('barang_id', $barangId);
                } else {
                    $query->where('barang_id', $barangId);
                }

                if (is_array($noSpb)) {
                    $query->whereIn('no_spb', $noSpb);
                } else {
                    $query->where('no_spb', $noSpb);
                }

                // Filter by selected pallets if provided
                if (!empty($palletId)) {
                    $palletId = is_array($palletId) ? $palletId : [$palletId];
                    $query->whereIn('pallet_id', $palletId);
                }

                $details = $query->get();

                $items = $details->map(function ($d) {
                    return [
                        'barang_id' => $d->barang_id,
                        'no_spb'    => $d->no_spb ?? '-',
                        'pallet_id' => $d->pallet_id ?? '-',
                        'qty'       => $d->qty,
                        'status'    => $d->status
                    ];
                })->toArray();
            }

            if (empty($items)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data detail transaksi komplit untuk kombinasi Barang dan SPB terpilih pada tanggal tersebut.'
                ], 422);
            }
        } else {
            $request->validate([
                'items' => 'required|array',
                'items.*.barang_id' => 'required|exists:wrm_master_barang,id',
                'items.*.no_spb' => 'nullable|string',
                'items.*.pallet_id' => 'nullable|string',
                'items.*.qty' => 'required|numeric|min:0',
                'items.*.status' => 'required|string',
            ]);
            $items = $request->items;
        }

        // Validasi existing (barang, spb, pallet, jenis_so, today)
        $existingItems = [];
        foreach ($items as $item) {
            $bId = $item['barang_id'];
            $spb = $item['no_spb'];
            $pallet = $item['pallet_id'];

            $exists = WrmSohModel::where('barang_id', $bId)
                ->where('no_spb', $spb)
                ->where('pallet', $pallet)
                ->where('jenis_so', $jenisSo)
                ->where('jenis_data', $jenisData)
                ->whereDate('created_at', $today)
                ->exists();

            if ($exists) {
                $barang = MasterBarangModel::find($bId);
                $mid = $barang ? $barang->mid : $bId;
                $existingItems[] = "Barang: {$mid}, SPB: {$spb}, Pallet: {$pallet}";
            }
        }

        if (!empty($existingItems)) {
            return response()->json([
                'status' => false,
                'message' => 'Data SOH berikut sudah terdaftar untuk hari ini'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $createdCount = 0;
            $updatedCount = 0;

            foreach ($items as $item) {
                $barangId = $item['barang_id'];
                $noSpb = $item['no_spb'];
                $pallet = $item['pallet_id'];
                $qty = (float)$item['qty'];
                $status = strtoupper($item['status']);

                $unrest = $status === 'UNREST' ? $qty : 0;
                $qi     = $status === 'QI' ? $qty : 0;
                $block  = $status === 'BLOCKED' ? $qty : 0;

                if (!in_array($status, ['UNREST', 'QI', 'BLOCKED'])) {
                    $unrest = $qty;
                }

                $qty_soh = $unrest + $qi + $block;

                $soh = WrmSohModel::updateOrCreate(
                    [
                        'barang_id' => $barangId,
                        'jenis_data' => $jenisData,
                        'no_spb'    => $noSpb,
                        'pallet'    => $pallet,
                        'jenis_so'  => $jenisSo,
                        'created_at' => $today
                    ],
                    [
                        'user_id'      => Auth::id() ?? 1,
                        'qty_soh'      => $qty_soh,
                        'qty_unrest'   => $unrest,
                        'qty_qi'       => $qi,
                        'qty_block'    => $block,
                        'last_updated' => now(),
                    ]
                );

                if ($soh->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }

                // Update summaries if there is a running opname today
                $sop = WrmSoModel::whereDate('tgl_opname', $today)
                    ->where('jenis_so', $jenisSo)
                    ->first();
                if ($sop) {
                    $summary = WrmSoSummariesModel::where('so_id', $sop->id)
                        ->where('barang_id', $barangId)
                        ->where('no_spb', $noSpb)
                        ->where('pallet', $pallet)
                        ->first();

                    if ($summary) {
                        $qtySistem = $qty_soh;
                        $qtyFisik  = $summary->qty_fisik ?? 0;
                        $selisih   = $qtyFisik - $qtySistem;
                        $summaryStatus = $selisih > 0 ? 'lebih' : ($selisih < 0 ? 'kurang' : 'match');

                        $summary->update([
                            'qty_sistem' => $qtySistem,
                            'selisih'    => $selisih,
                            'status'     => $summaryStatus,
                        ]);
                    }
                }
            }

            DB::commit();

            $message = "Berhasil memproses SOH manual. Baru: $createdCount, Diperbarui: $updatedCount.";
            return response()->json([
                'status'  => true,
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses SOH manual: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $soh = WrmSohModel::findOrFail($id);

        $today = now()->toDateString();
        $periodeText = $soh->jenis_so === 'monthly' ? 'bulan ini' : 'hari ini';
        $soStatus = WrmSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $soh->jenis_so)
            ->first();
        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status' => false,
                'message' => "Tidak dapat memperbarui data SOH karena Stock Opname {$periodeText} telah selesai (finished) untuk jenis SO ini."
            ], 422);
        }

        $request->validate([
            'unrest' => 'nullable|integer|min:0',
            'qi' => 'nullable|integer|min:0',
            'block' => 'nullable|integer|min:0',
            'no_spb' => 'nullable|string',
            'pallet' => 'nullable|string',
        ]);

        try {
            // Validasi jika MID, no_spb, dan pallet sama di hari ini dan sudah ada (selain record ini) untuk jenis SO ini
            $exists = WrmSohModel::where('barang_id', $soh->barang_id)
                ->where('no_spb', $request->no_spb)
                ->where('pallet', $request->pallet)
                ->where('jenis_so', $soh->jenis_so)
                ->whereDate('created_at', $soh->created_at)
                ->where('id', '!=', $soh->id)
                ->exists();

            if ($exists) {
                $barang = MasterBarangModel::find($soh->barang_id);
                return response()->json([
                    'status' => false,
                    'message' => "Data SOH untuk MID {$barang->mid} dengan No SPB {$request->no_spb} dan Pallet {$request->pallet} sudah ada {$periodeText} untuk jenis SO ini!"
                ], 422);
            }

            $unrest = (int)($request->unrest ?? 0);
            $qi = (int)($request->qi ?? 0);
            $block = (int)($request->block ?? 0);
            $qty_soh = $unrest + $qi + $block;

            $soh->update([
                'no_spb' => $request->no_spb,
                'pallet' => $request->pallet,
                'qty_soh' => $qty_soh,
                'qty_unrest' => $unrest,
                'qty_qi' => $qi,
                'qty_block' => $block,
                'user_id' => Auth::id() ?? $soh->user_id,
                'last_updated' => now()
            ]);

            // Update live comparison if a session is currently running
            $sop = WrmSoModel::whereDate('tgl_opname', $today)
                ->where('jenis_so', $soh->jenis_so)
                ->first();

            if ($sop) {
                $summary = WrmSoSummariesModel::where('so_id', $sop->id)
                    ->where('barang_id', $soh->barang_id)
                    ->where('no_spb', $soh->no_spb)
                    ->where('pallet', $soh->pallet)
                    ->first();

                if ($summary) {
                    $qtySistem = $qty_soh;
                    $qtyFisik  = $summary->qty_fisik ?? 0;
                    $selisih   = $qtyFisik - $qtySistem;
                    $status    = $selisih > 0 ? 'lebih' : ($selisih < 0 ? 'kurang' : 'match');

                    $summary->update([
                        'qty_sistem' => $qtySistem,
                        'selisih'    => $selisih,
                        'status'     => $status,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Stock On Hand berhasil diperbarui',
                'data' => $soh
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui Stock On Hand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchSourceDetails(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_data' => 'required|string|in:inbound,outbound',
        ]);

        $date = $request->tanggal;
        $jenisData = $request->jenis_data;

        if ($jenisData === 'inbound') {
            $details = \App\Models\Wrm\Inventory\StockInboundDetail::whereHas('inbound', function ($q) use ($date) {
                $q->whereDate('incoming_date', $date);
            })
                ->where('status', 'COMPLETED')
                ->with(['barang', 'inbound'])
                ->get();

            $result = $details->map(function ($d) {
                $noSpb = $d->inbound->no_spb ?? '-';
                return [
                    'barang_id' => $d->barang_id,
                    'mid' => $d->barang->mid ?? '-',
                    'nama_barang' => $d->barang->nama_barang ?? '-',
                    'no_spb' => $noSpb,
                    'pallet' => $d->pallet ?? '-',
                    'qty' => $d->qty,
                    'status' => $d->status,
                ];
            });
        } else {
            $details = \App\Models\Wrm\Inventory\StockOutboundDetail::whereHas('outbound', function ($q) use ($date) {
                $q->whereDate('reservasi_date', $date)
                    ->where('status_transfer', 'COMPLETED');
            })
                ->with(['barang', 'outbound'])
                ->get();

            $result = $details->map(function ($d) {
                return [
                    'barang_id' => $d->barang_id,
                    'mid' => $d->barang->mid ?? '-',
                    'nama_barang' => $d->barang->nama_barang ?? '-',
                    'no_spb' => $d->no_spb ?? '-',
                    'pallet' => $d->pallet ?? '-',
                    'qty' => $d->qty,
                    'status' => $d->status,
                ];
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }


    public function destroy(string $id)
    {
        $soh = WrmSohModel::findOrFail($id);

        $today = now()->toDateString();
        $periodeText = $soh->jenis_so === 'monthly' ? 'bulan ini' : 'hari ini';
        $soStatus = WrmSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $soh->jenis_so)
            ->first();
        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status' => false,
                'message' => "Tidak dapat menghapus data SOH karena Stock Opname {$periodeText} telah selesai (finished) untuk jenis SO ini."
            ], 422);
        }

        $soh->delete();
        return response()->json([
            'status' => true,
            'message' => 'Stock On Hand berhasil dihapus'
        ]);
    }

    public function resetAll(Request $request)
    {
        $today = now()->toDateString();
        $jenisSo = $request->input('jenis_so', 'cycle_count');
        $periodeText = $jenisSo === 'monthly' ? 'bulan ini' : 'hari ini';
        $soStatus = WrmSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status' => false,
                'message' => "Tidak dapat mengosongkan data SOH karena Stock Opname {$periodeText} telah selesai (finished) untuk jenis SO ini."
            ], 422);
        }

        try {
            $deleted = WrmSohModel::whereDate('created_at', $today)
                ->where('jenis_so', $jenisSo)
                ->delete();

            return response()->json([
                'status' => true,
                'message' => "Berhasil menghapus $deleted data SOH untuk {$periodeText}."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data SOH: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'jenis_so' => 'required|string|in:cycle_count,monthly',
        ]);

        $today = now()->toDateString();
        $jenisSo = $request->input('jenis_so');
        $periodeText = $jenisSo === 'monthly' ? 'bulan ini' : 'hari ini';
        $soStatus = WrmSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status' => false,
                'message' => "Tidak dapat mengunggah file Excel karena Stock Opname {$periodeText} telah selesai (finished)."
            ], 422);
        }

        if ($jenisSo === 'monthly') {
            $currentYear = now()->year;
            $currentMonth = now()->month;
            $hasMonthlySo = WrmSoStatusModel::where('jenis_so', 'monthly')
                ->whereYear('tgl_opname', $currentYear)
                ->whereMonth('tgl_opname', $currentMonth)
                ->whereDate('tgl_opname', '!=', $today)
                ->exists();
            if ($hasMonthlySo) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat mengunggah file Excel karena Stock Opname Monthly untuk bulan ini sudah pernah berjalan.'
                ], 422);
            }
        }

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();

            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $header = [];
            $countSuccess = 0;
            $notFound = [];
            $validData = [];

            foreach ($rows as $index => $row) {
                if ($index == 1) {
                    $header = array_map(fn($h) => strtolower(trim($h)), $row);
                    $requiredHeaders = ['mid_barang', 'no_spb', 'pallet_id', 'unrest', 'qual_insp', 'blocked'];
                    $missing = array_diff($requiredHeaders, $header);

                    if (!empty($missing)) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Format file Excel tidak sesuai. Kolom berikut hilang: ' . implode(', ', $missing)
                        ], 422);
                    }
                    continue;
                }

                if (empty($row['A'])) continue;

                $data = array_combine($header, array_map('trim', $row));

                if (empty($data['mid_barang'])) continue;

                $barang = MasterBarangModel::where('mid', $data['mid_barang'])->first();

                if (!$barang) {
                    $notFound[] = $data['mid_barang'];
                    continue;
                }

                $validData[] = [
                    'barang' => $barang,
                    'data'   => $data,
                ];
            }

            if (!empty($notFound)) {
                $notFoundUnique = array_unique($notFound);
                return response()->json([
                    'status' => false,
                    'message' => 'Beberapa MID Barang tidak ditemukan di master barang WRM: ' . implode(', ', $notFoundUnique),
                    'not_found' => $notFoundUnique
                ], 422);
            }

            // Validasi jika MID, no_spb dan pallet sama di hari ini dan sudah ada untuk jenis SO ini
            $duplicatesInDb = [];
            $seenCombinations = [];
            $duplicatesInFile = [];

            foreach ($validData as $item) {
                $barang = $item['barang'];
                $data = $item['data'];

                $noSpb = empty($data['no_spb']) ? null : (string)$data['no_spb'];
                $pallet = isset($data['pallet_id']) ? (string)$data['pallet_id'] : null;
                $combinationKey = $barang->mid . '-' . $noSpb . '-' . $pallet;

                // Check duplicates in file
                if (in_array($combinationKey, $seenCombinations)) {
                    $duplicatesInFile[] = "MID: {$barang->mid}, SPB: " . ($noSpb ?? '-') . ", Pallet: " . ($pallet ?? '-');
                } else {
                    $seenCombinations[] = $combinationKey;
                }

                // Check duplicates in database for today
                $exists = WrmSohModel::where('barang_id', $barang->id)
                    ->where('no_spb', $noSpb)
                    ->where('pallet', $pallet)
                    ->where('jenis_so', $jenisSo)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($exists) {
                    $duplicatesInDb[] = "MID: {$barang->mid}, SPB: " . ($noSpb ?? '-') . ", Pallet: " . ($pallet ?? '-');
                }
            }

            if (!empty($duplicatesInFile) || !empty($duplicatesInDb)) {
                $allDuplicates = array_unique(array_merge($duplicatesInFile, $duplicatesInDb));
                return response()->json([
                    'status' => false,
                    'message' => 'Terdapat duplikasi data MID, No SPB, dan Pallet untuk hari ini: ' . implode('; ', $allDuplicates),
                    'duplicates' => $allDuplicates
                ], 422);
            }

            foreach ($validData as $item) {
                $barang = $item['barang'];
                $data = $item['data'];

                $noSpb = empty($data['no_spb']) ? null : (string)$data['no_spb'];
                $pallet = isset($data['pallet_id']) ? (string)$data['pallet_id'] : null;
                $unrest = (int)($data['unrest'] ?? 0);
                $qual_insp = (int)($data['qual_insp'] ?? 0);
                $blocked = (int)($data['blocked'] ?? 0);
                $qty_soh = $unrest + $qual_insp + $blocked;

                // Check if already exists for today. If so, update it, else create it.
                $soh = WrmSohModel::updateOrCreate(
                    [
                        'barang_id' => $barang->id,
                        'no_spb'    => $noSpb,
                        'pallet'    => $pallet,
                        'jenis_so'  => $jenisSo,
                        'created_at' => $today
                    ],
                    [
                        'user_id' => Auth::id() ?? 1,
                        'qty_soh' => $qty_soh,
                        'qty_unrest' => $unrest,
                        'qty_qi' => $qual_insp,
                        'qty_block' => $blocked,
                        'last_updated' => now(),
                    ]
                );

                // Update summaries if there is a running opname today
                $sop = WrmSoModel::whereDate('tgl_opname', $today)
                    ->where('jenis_so', $jenisSo)
                    ->first();
                if ($sop) {
                    $summary = WrmSoSummariesModel::where('so_id', $sop->id)
                        ->where('barang_id', $barang->id)
                        ->where('no_spb', $noSpb)
                        ->where('pallet', $pallet)
                        ->first();

                    if ($summary) {
                        $qtySistem = $qty_soh;
                        $qtyFisik  = $summary->qty_fisik ?? 0;
                        $selisih   = $qtyFisik - $qtySistem;
                        $status    = $selisih > 0 ? 'lebih' : ($selisih < 0 ? 'kurang' : 'match');

                        $summary->update([
                            'qty_sistem' => $qtySistem,
                            'selisih'    => $selisih,
                            'status'     => $status,
                        ]);
                    }
                }

                $countSuccess++;
            }

            return response()->json([
                'status' => true,
                'message' => "Berhasil import $countSuccess data Stock On Hand WRM dari Excel."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengimpor file Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'mid_barang',
            'no_spb',
            'pallet_id',
            'unrest',
            'qual_insp',
            'blocked',
        ];

        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '1', strtoupper($header));
            $sheet->getStyle($columnIndex . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
            $columnIndex++;
        }

        // Sample data
        $sheet->setCellValue('A2', '20000812'); // Sample MID
        $sheet->setCellValue('B2', '9000007673'); // Sample SPB
        $sheet->setCellValue('C2', '1'); // Sample Pallet
        $sheet->setCellValue('D2', 500); // Unrest
        $sheet->setCellValue('E2', 0); // QI
        $sheet->setCellValue('F2', 0); // Blocked

        $fileName = 'Template_SO_WRM_' . date('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"{$fileName}\"",
        ]);
    }
}
