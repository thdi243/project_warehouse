<?php

namespace App\Http\Controllers\Wrm\StockOpname;

use App\Http\Controllers\Controller;
use App\Models\Wrm\StockOpname\WrmSohModel;
use App\Models\Wrm\StockOpname\WrmSoModel;
use App\Models\Wrm\StockOpname\WrmSoDetailModel;
use App\Models\Wrm\StockOpname\WrmSoSummariesModel;
use App\Models\Wrm\StockOpname\WrmSoTempModel;
use App\Models\Wrm\StockOpname\WrmSoTempNoteModel;
use App\Models\Wrm\StockOpname\WrmSoStatusModel;
use App\Models\Wrm\StockOpname\WrmSoApprovalModel;
use App\Models\Wrm\MasterBarangModel;
use App\Models\User;
use App\Models\NotificationsModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class WrmStockOpnameController extends Controller
{
    public function startOpname(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $jenisSo = $request->input('jenis_so', 'cycle_count');

        if ($jenisSo === 'monthly') {
            $currentYear = Carbon::parse($today)->year;
            $currentMonth = Carbon::parse($today)->month;

            $existingMonthlyThisMonth = WrmSoStatusModel::where('jenis_so', 'monthly')
                ->whereYear('tgl_opname', $currentYear)
                ->whereMonth('tgl_opname', $currentMonth)
                ->whereDate('tgl_opname', '!=', $today)
                ->exists();

            if ($existingMonthlyThisMonth) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stock Opname Monthly untuk bulan ini sudah pernah dibuat/berjalan pada hari lain.'
                ], 422);
            }
        }

        // Check if SOH exists for today for this jenis_so
        $sohCount = WrmSohModel::whereDate('created_at', $today)
            ->where('jenis_so', $jenisSo)
            ->count();

        if ($sohCount === 0) {
            return response()->json([
                'status' => false,
                'message' => 'Data SOH kosong. Silakan unggah atau isi data SOH terlebih dahulu untuk jenis SO ini.'
            ], 422);
        }

        $existing = WrmSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => true,
                'message' => 'Opname sudah dimulai sebelumnya',
                'data' => $existing
            ]);
        }

        $status = WrmSoStatusModel::create([
            'user_id' => $user->id ?? 1,
            'tgl_opname' => $today,
            'status' => 'started',
            'jenis_so' => $jenisSo,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Opname berhasil dimulai',
            'data' => $status
        ]);
    }

    private function checkSoWriteAccess($jenisSo = 'cycle_count')
    {
        $today = now()->toDateString();
        $status = WrmSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        if ($status && $status->status === 'started' && Auth::id() != $status->user_id) {
            return false;
        }
        return true;
    }

    public function getStatusOpname(Request $request)
    {
        $today = now()->toDateString();
        $jenisSo = $request->input('jenis_so', 'cycle_count');
        $status = WrmSoStatusModel::with('user')
            ->whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        $currentUser = Auth::user();

        if ($status) {
            $isOwner = $currentUser && ($currentUser->id == $status->user_id);
            return response()->json([
                'status' => $status->status,
                'is_owner' => $isOwner,
                'started_by' => $status->user->nama_lengkap ?? $status->user->username ?? 'Stock Control'
            ]);
        }

        return response()->json([
            'status' => 'idle',
            'is_owner' => true,
            'started_by' => 'Stock Control'
        ]);
    }

    public function getData(Request $request)
    {
        $search = $request->input('search');
        $today = now()->toDateString();
        $jenisSo = $request->input('jenis_so', 'cycle_count');

        // Query all SOH entered today
        $query = WrmSohModel::query()
            ->select('wrm_soh.*')
            ->leftJoin('wrm_master_barang', 'wrm_soh.barang_id', '=', 'wrm_master_barang.id')
            ->whereDate('wrm_soh.created_at', $today)
            ->where('wrm_soh.jenis_so', $jenisSo);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wrm_master_barang.mid', 'like', '%' . $search . '%')
                    ->orWhere('wrm_master_barang.nama_barang', 'like', '%' . $search . '%')
                    ->orWhere('wrm_soh.no_spb', 'like', '%' . $search . '%')
                    ->orWhere('wrm_soh.pallet', 'like', '%' . $search . '%');
            });
        }

        $sohList = $query->with(['barang', 'bin.location'])->get();

        // Pull active temp values grouped by barang_id, no_spb and pallet
        $tempData = WrmSoTempModel::whereDate('tgl_opname', $today)
            ->whereHas('soh', function ($q) use ($jenisSo) {
                $q->where('jenis_so', $jenisSo);
            })
            ->get()->groupBy(function ($item) {
                return $item->barang_id . '-' . $item->no_spb . '-' . $item->pallet;
            });

        $tempNotes = WrmSoTempNoteModel::whereDate('tgl_opname', $today)
            ->whereHas('soh', function ($q) use ($jenisSo) {
                $q->where('jenis_so', $jenisSo);
            })
            ->get()->keyBy(function ($item) {
                return $item->barang_id . '-' . $item->no_spb . '-' . $item->pallet;
            });

        $result = $sohList->map(function ($soh) use ($tempData, $tempNotes) {
            $key = $soh->barang_id . '-' . $soh->no_spb . '-' . $soh->pallet;
            $temps = $tempData->get($key);
            $note = $tempNotes->get($key);

            $isCounted = $temps && $temps->isNotEmpty();
            $qtyFull = $isCounted ? $temps->sum('qty_full') : null;
            $qtyReceh = $isCounted ? $temps->sum('qty_receh') : null;
            $summary = $isCounted ? $temps->sum('summary') : null;

            $diffStatus = null;
            if ($isCounted) {
                $diff = $summary - $soh->qty_soh;
                $diffStatus = $diff > 0 ? 'lebih' : ($diff < 0 ? 'kurang' : 'match');
            }

            return [
                'id' => $soh->id,
                'soh_id' => $soh->id,
                'barang_id' => $soh->barang_id,
                'mid' => $soh->barang->mid,
                'nama_barang' => $soh->barang->nama_barang,
                'uom' => $soh->barang->uom,
                'qty_kg' => (float)($soh->barang->qty_kg ?? 1),
                'no_spb' => $soh->no_spb,
                'pallet' => $soh->pallet,
                'qty_soh' => $soh->qty_soh,
                'qty_unrest' => $soh->qty_unrest,
                'qty_qi' => $soh->qty_qi,
                'qty_block' => $soh->qty_block,
                'qty_full' => $qtyFull,
                'qty_receh' => $qtyReceh,
                'summary' => $summary,
                'catatan' => $note ? $note->catatan : null,
                'diff_status' => $diffStatus,
                'location_text' => $soh->bin && $soh->bin->location
                    ? "{$soh->bin->location->plant} - {$soh->bin->location->s_loc} - {$soh->bin->location->gudang} - {$soh->bin->location->zona} - {$soh->bin->location->bin} - ({$soh->bin->kolom}.{$soh->bin->level})"
                    : '-',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    public function saveTemp(Request $request)
    {
        $request->validate([
            'soh_id' => 'required|exists:wrm_soh,id',
            'qty_full' => 'nullable|integer|in:0,1',
            'qty_receh' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $soh = WrmSohModel::findOrFail($request->soh_id);

        if (!$this->checkSoWriteAccess($soh->jenis_so)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        $user = Auth::user();
        $today = now()->toDateString();

        $qtyFull = $request->qty_full;
        $qtyReceh = $request->qty_receh;

        $hasQty = ($qtyFull !== null && $qtyFull !== '') || ($qtyReceh !== null && $qtyReceh !== '');
        $temp = null;

        if ($hasQty) {
            $qtyFullVal = (int)($qtyFull ?? 0);
            $qtyRecehVal = (int)($qtyReceh ?? 0);
            $summary = $qtyRecehVal;

            $temp = WrmSoTempModel::updateOrCreate(
                [
                    'soh_id' => $soh->id,
                    'tgl_opname' => $today,
                ],
                [
                    'barang_id' => $soh->barang_id,
                    'no_spb' => $soh->no_spb,
                    'pallet' => $soh->pallet,
                    'qty_full' => $qtyFullVal,
                    'qty_receh' => $qtyRecehVal,
                    'summary' => $summary,
                    'created_by' => $user->id ?? 1,
                ]
            );
        }

        // Save notes temp if present
        if ($request->has('keterangan')) {
            $catatan = trim($request->keterangan ?? '');
            if ($catatan !== '') {
                WrmSoTempNoteModel::updateOrCreate(
                    [
                        'soh_id' => $soh->id,
                        'barang_id' => $soh->barang_id,
                        'no_spb' => $soh->no_spb,
                        'pallet' => $soh->pallet,
                        'tgl_opname' => $today
                    ],
                    [
                        'catatan' => $catatan,
                        'created_by' => $user->id ?? 1,
                    ]
                );
            } else {
                WrmSoTempNoteModel::where('soh_id', $soh->id)
                    ->whereDate('tgl_opname', $today)
                    ->delete();
            }
        }

        // Hitung total summary dari semua temp untuk soh_id ini hari ini
        $allTemps = WrmSoTempModel::where('soh_id', $soh->id)
            ->whereDate('tgl_opname', $today)
            ->get();

        $totalSummary = $allTemps->sum('summary');

        return response()->json([
            'status' => 'success',
            'message' => 'Data tersimpan sementara.',
            'data' => [
                'summary' => $totalSummary,
                'record' => $temp
            ]
        ]);
    }

    public function getDataTempBatch(Request $request)
    {
        $today = now()->toDateString();
        $sohIds = array_map('intval', $request->input('soh_ids', []));
        $barangIds = array_map('intval', $request->input('barang_ids', []));

        // Get Qty
        $qtyRecords = WrmSoTempModel::with(['barang', 'soh.barang'])
            ->whereDate('tgl_opname', $today)
            ->where(function ($q) use ($sohIds, $barangIds) {
                if (!empty($sohIds)) {
                    $q->whereIn('soh_id', $sohIds);
                }
                if (!empty($barangIds)) {
                    $q->orWhereIn('barang_id', $barangIds)->whereNull('soh_id');
                }
            })
            ->get()
            ->map(function ($rec) {
                $barang = $rec->barang ?? optional(optional($rec->soh)->barang);
                if (!$barang) return null;

                return [
                    'id'          => $rec->id,
                    'soh_id'      => $rec->soh_id,
                    'barang_id'   => $rec->barang_id,
                    'mid'         => $barang->mid,
                    'nama_barang' => $barang->nama_barang,
                    'qty_full'    => $rec->qty_full,
                    'qty_receh'   => $rec->qty_receh,
                    'summary'     => (int) $rec->summary,
                    'mode'        => 'qty',
                    'created_at'  => $rec->created_at->toDateTimeString(),
                    'updated_at'  => $rec->updated_at->toDateTimeString(),
                ];
            })->filter()->values();

        // Get Note
        $noteRecords = WrmSoTempNoteModel::with(['barang', 'soh.barang'])
            ->whereDate('tgl_opname', $today)
            ->where(function ($q) use ($sohIds, $barangIds) {
                if (!empty($sohIds)) {
                    $q->whereIn('soh_id', $sohIds);
                }
                if (!empty($barangIds)) {
                    $q->orWhereIn('barang_id', $barangIds)->whereNull('soh_id');
                }
            })
            ->get()
            ->map(function ($rec) {
                $barang = $rec->barang ?? optional(optional($rec->soh)->barang);
                if (!$barang) return null;

                return [
                    'id'          => $rec->id,
                    'soh_id'      => $rec->soh_id,
                    'barang_id'   => $rec->barang_id,
                    'mid'         => $barang->mid,
                    'nama_barang' => $barang->nama_barang,
                    'keterangan'  => $rec->catatan,
                    'mode'        => 'note',
                    'created_at'  => $rec->created_at->toDateTimeString(),
                    'updated_at'  => $rec->updated_at->toDateTimeString(),
                ];
            })->filter()->values();

        // Combine both
        $data = $qtyRecords->concat($noteRecords);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function getDataTempEdit(int $sohId)
    {
        $today = now()->toDateString();

        $dataQty = WrmSoTempModel::with('barang')
            ->where('soh_id', $sohId)
            ->whereDate('tgl_opname', $today)
            ->orderBy('updated_at', 'asc')
            ->get();

        $dataNote = WrmSoTempNoteModel::where('soh_id', $sohId)
            ->whereDate('tgl_opname', $today)
            ->latest('updated_at')
            ->first();

        return response()->json([
            'status' => 'success',
            'data_qty' => $dataQty,
            'data_note' => $dataNote
        ]);
    }

    public function updateTempBatch(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.qty_full' => 'nullable|integer|in:0,1',
            'items.*.qty_receh' => 'nullable|integer|min:0',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $items = $validated['items'];
        $catatan = $validated['catatan'] ?? null;

        if (empty($items)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada item yang divalidasi.'
            ], 422);
        }

        $firstTemp = WrmSoTempModel::find($items[0]['id']);
        $jenisSo = 'cycle_count';
        if ($firstTemp && $firstTemp->soh) {
            $jenisSo = $firstTemp->soh->jenis_so;
        }

        if (!$this->checkSoWriteAccess($jenisSo)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $processedPairs = [];

            foreach ($items as $it) {
                $temp = WrmSoTempModel::with('barang', 'soh')->find($it['id']);
                if (!$temp || !$temp->barang) continue;

                $qtyFull = isset($it['qty_full']) ? (int)$it['qty_full'] : 0;
                $qtyReceh = isset($it['qty_receh']) ? (int)$it['qty_receh'] : 0;
                $summary = ($qtyFull === 1) ? $qtyReceh : 0;

                $temp->qty_full = $qtyFull;
                $temp->qty_receh = $qtyReceh;
                $temp->summary = $summary;
                $temp->save();

                $tglOpname = $temp->tgl_opname ? \Carbon\Carbon::parse($temp->tgl_opname)->toDateString() : now()->toDateString();
                $pairKey = $temp->soh_id . '|' . $tglOpname;
                $processedPairs[$pairKey] = [
                    'soh_id' => $temp->soh_id,
                    'barang_id' => $temp->barang_id,
                    'no_spb' => $temp->no_spb,
                    'pallet' => $temp->pallet,
                    'tgl_opname' => $tglOpname,
                ];
            }

            if ($catatan !== null) {
                foreach ($processedPairs as $pair) {
                    if (trim($catatan) !== '') {
                        WrmSoTempNoteModel::updateOrCreate(
                            [
                                'soh_id' => $pair['soh_id'],
                                'barang_id' => $pair['barang_id'],
                                'no_spb' => $pair['no_spb'],
                                'pallet' => $pair['pallet'],
                                'tgl_opname' => $pair['tgl_opname']
                            ],
                            [
                                'catatan' => trim($catatan),
                                'created_by' => Auth::id() ?? 1
                            ]
                        );
                    } else {
                        WrmSoTempNoteModel::where('soh_id', $pair['soh_id'])
                            ->whereDate('tgl_opname', $pair['tgl_opname'])
                            ->delete();
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diperbarui.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyTemp($id, Request $request)
    {
        $type = $request->input('tipe', 'qty');
        $jenisSo = 'cycle_count';

        if ($type === 'note') {
            $note = WrmSoTempNoteModel::where('soh_id', $id)->first();
            if ($note && $note->soh) {
                $jenisSo = $note->soh->jenis_so;
            }
        } else {
            $temp = WrmSoTempModel::with('soh')->find($id);
            if ($temp && $temp->soh) {
                $jenisSo = $temp->soh->jenis_so;
            }
        }

        if (!$this->checkSoWriteAccess($jenisSo)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        try {
            if ($type === 'note') {
                $deleted = WrmSoTempNoteModel::where('soh_id', $id)->delete();
                if (!$deleted) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Catatan tidak ditemukan.'
                    ]);
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Catatan berhasil dihapus.'
                ]);
            } else {
                $temp = WrmSoTempModel::find($id);
                if (!$temp) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data sementara tidak ditemukan.'
                    ]);
                }
                $temp->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data sementara berhasil dihapus.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
            ]);
        }
    }

    public function saveTempNew(Request $request)
    {
        $request->validate([
            'mid_barang' => 'required|exists:wrm_master_barang,mid',
            'no_spb' => 'nullable|string',
            'pallet' => 'required|string',
            'unrest' => 'required|integer|min:0',
            'qi' => 'nullable|integer|min:0',
            'blocked' => 'nullable|integer|min:0',
            'qty_full' => 'required|integer|in:0,1',
            'qty_receh' => 'required|integer|min:0',
            'jenis_so' => 'required|string|in:cycle_count,monthly',
        ]);

        $jenisSo = $request->input('jenis_so', 'cycle_count');

        if (!$this->checkSoWriteAccess($jenisSo)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        $barang = MasterBarangModel::where('mid', $request->mid_barang)->firstOrFail();
        $user = Auth::user();
        $today = now()->toDateString();

        // Validasi jika MID, no_spb dan pallet sudah ada hari ini untuk jenis SO ini
        $exists = WrmSohModel::where('barang_id', $barang->id)
            ->where('no_spb', $request->no_spb)
            ->where('pallet', $request->pallet)
            ->where('jenis_so', $jenisSo)
            ->whereDate('created_at', $today)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => "MID {$request->mid_barang} dengan No SPB " . ($request->no_spb ?? '-') . " dan Pallet {$request->pallet} sudah ada hari ini."
            ], 422);
        }

        $qtyFullVal = (int)($request->qty_full ?? 0);
        $qtyRecehVal = (int)($request->qty_receh ?? 0);
        $summary = $qtyRecehVal;

        $sohStock = \App\Models\Wrm\Inventory\StockOnHand::where('barang_id', $barang->id)
            ->where('no_spb', $request->no_spb)
            ->where('pallet', $request->pallet)
            ->first();
        $locId = $sohStock ? $sohStock->loc_id : null;

        // Create new SOH entry for today
        $soh = WrmSohModel::updateOrCreate(
            [
                'barang_id' => $barang->id,
                'no_spb'    => $request->no_spb,
                'pallet'    => $request->pallet,
                'loc_id'    => $locId,
                'jenis_so'  => $jenisSo,
                'created_at' => $today
            ],
            [
                'user_id' => $user->id ?? 1,
                'qty_soh' => (int)$request->unrest + (int)$request->qi + (int)$request->blocked,
                'qty_unrest' => $request->unrest,
                'qty_qi' => $request->qi ?? 0,
                'qty_block' => $request->blocked ?? 0,
                'last_updated' => now()
            ]
        );

        // Save to temp opname
        $temp = WrmSoTempModel::updateOrCreate(
            [
                'soh_id' => $soh->id,
                'barang_id' => $barang->id,
                'no_spb' => $request->no_spb,
                'pallet' => $request->pallet,
                'tgl_opname' => $today
            ],
            [
                'qty_full' => $qtyFullVal,
                'qty_receh' => $qtyRecehVal,
                'summary' => $summary,
                'created_by' => $user->id ?? 1,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Barang baru ditambahkan ke Stock Opname.',
            'data' => [
                'soh' => $soh,
                'temp' => $temp
            ]
        ]);
    }

    public function resetTempRow(Request $request)
    {
        $request->validate([
            'soh_id' => 'required|exists:wrm_soh,id'
        ]);

        $soh = WrmSohModel::findOrFail($request->soh_id);

        if (!$this->checkSoWriteAccess($soh->jenis_so)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        $today = now()->toDateString();
        WrmSoTempModel::where('soh_id', $request->soh_id)->whereDate('tgl_opname', $today)->delete();
        WrmSoTempNoteModel::where('soh_id', $request->soh_id)->whereDate('tgl_opname', $today)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Baris berhasil di-reset.'
        ]);
    }

    public function processOpname(Request $request)
    {
        $request->validate([
            'tgl_opname' => 'required|date',
            'mode' => 'required|in:check,final_prepare,final_submit',
            'jenis_so' => 'required|string|in:cycle_count,monthly',
            'keterangan' => 'nullable|array'
        ]);

        $jenisSo = $request->jenis_so;

        if (!$this->checkSoWriteAccess($jenisSo)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        $user = Auth::user();
        $tglOpname = $request->tgl_opname;

        // Fetch temp data for this jenis_so
        $tempData = WrmSoTempModel::with('barang')
            ->where('tgl_opname', $tglOpname)
            ->whereHas('soh', function ($q) use ($jenisSo) {
                $q->where('jenis_so', $jenisSo);
            })
            ->get();

        if ($tempData->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data opname fisik kosong. Silakan isi setidaknya satu item.'
            ]);
        }

        // Fetch all SOH today for this jenis_so
        $sohData = WrmSohModel::whereDate('created_at', $tglOpname)
            ->where('jenis_so', $jenisSo)
            ->get()
            ->keyBy(function ($item) {
                return $item->barang_id . '-' . $item->no_spb . '-' . $item->pallet;
            });

        // Pull active temp notes
        $tempNotes = WrmSoTempNoteModel::whereDate('tgl_opname', $tglOpname)
            ->whereHas('soh', function ($q) use ($jenisSo) {
                $q->where('jenis_so', $jenisSo);
            })
            ->get()
            ->pluck('catatan', 'soh_id');

        // Check if there are any items in SOH that haven't been counted yet
        $uncountedItems = [];
        foreach ($sohData as $key => $soh) {
            $counted = $tempData->firstWhere('soh_id', $soh->id);
            if (!$counted) {
                $uncountedItems[] = [
                    'mid' => $soh->barang->mid,
                    'nama_barang' => $soh->barang->nama_barang,
                    'no_spb' => $soh->no_spb,
                    'pallet' => $soh->pallet,
                    'qty_system' => $soh->qty_soh
                ];
            }
        }

        // Group temp counts by soh_id to sum their values
        $groupedTemp = $tempData->groupBy('soh_id')->map(function ($items) use ($sohData) {
            $first = $items->first();
            $soh   = $sohData->get($first->barang_id . '-' . $first->no_spb . '-' . $first->pallet);
            return [
                'soh_id'     => $first->soh_id,
                'barang_id'  => $first->barang_id,
                'no_spb'     => $first->no_spb,
                'pallet'     => $first->pallet,
                'jenis_data' => $soh ? $soh->jenis_data : null,
                'barang'     => $first->barang,
                'qty_full'   => $items->sum('qty_full'),
                'qty_receh'  => $items->sum('qty_receh'),
                'summary'    => $items->sum('summary'),
            ];
        });

        // Validate differences and comments
        $varianceIssues = [];
        $analysis = [];
        foreach ($groupedTemp as $temp) {
            $soh = $sohData->get($temp['barang_id'] . '-' . $temp['no_spb'] . '-' . $temp['pallet']);
            $qtySystem = $soh ? $soh->qty_soh : 0;
            $qtyPhysical = $temp['summary'];
            $diff = round($qtyPhysical - $qtySystem, 4);

            // Resolve comment from temp notes or manual request input
            $comment = $request->input('keterangan.' . $temp['soh_id']) ?? $tempNotes->get($temp['soh_id']);

            $status = $diff > 0 ? 'lebih' : ($diff < 0 ? 'kurang' : 'match');

            if ($diff != 0 && empty($comment)) {
                $varianceIssues[] = [
                    'mid' => $temp['barang']->mid,
                    'no_spb' => $temp['no_spb'],
                    'pallet' => $temp['pallet'],
                    'selisih' => $diff,
                ];
            }

            $analysis[] = [
                'soh_id'     => $temp['soh_id'],
                'barang_id'  => $temp['barang_id'],
                'jenis_data' => $temp['jenis_data'],
                'mid'        => $temp['barang']->mid ?? '-',
                'nama_barang' => $temp['barang']->nama_barang ?? '-',
                'no_spb'     => $temp['no_spb'],
                'pallet'     => $temp['pallet'],
                'qty_fisik'  => $qtyPhysical,
                'qty_sistem' => $qtySystem,
                'selisih'    => $diff,
                'status'     => $status,
                'keterangan' => $comment,
                'qty_full'   => $temp['qty_full'],
                'qty_receh'  => $temp['qty_receh'],
                'loc_id'     => $soh ? $soh->loc_id : null,
            ];
        }

        if ($request->mode === 'check') {
            if (count($uncountedItems) > 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Ada item yang belum di-opname!',
                    'uncounted' => $uncountedItems,
                    'analysis' => $analysis,
                    'issues' => $varianceIssues,
                ]);
            }

            if (count($varianceIssues) > 0) {
                return response()->json([
                    'status' => 'variance_unexplained',
                    'message' => 'Ada item yang berselisih tapi belum diberi keterangan!',
                    'issues' => $varianceIssues,
                    'analysis' => $analysis
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pemeriksaan selesai. Data siap disubmit final.',
                'analysis' => $analysis
            ]);
        }

        if ($request->mode === 'final_prepare') {
            if (count($uncountedItems) > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Semua item SOH harus di-opname sebelum submit final.'
                ], 422);
            }

            if (count($varianceIssues) > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Semua selisih harus diberikan keterangan sebelum submit final.'
                ], 422);
            }

            return response()->json([
                'status' => 'need_comment',
                'message' => 'Silakan isi komentar final.'
            ]);
        }

        if ($request->mode === 'final_submit') {
            $komentarFinal = $request->input('komentar_final');
            if (empty($komentarFinal)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Komentar final wajib diisi.'
                ], 422);
            }

            if (count($uncountedItems) > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Semua item SOH harus di-opname sebelum submit final.'
                ], 422);
            }

            if (count($varianceIssues) > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Semua selisih harus diberikan keterangan sebelum submit final.'
                ], 422);
            }

            DB::beginTransaction();
            try {
                $existingSop = WrmSoModel::whereDate('tgl_opname', $tglOpname)
                    ->where('jenis_so', $jenisSo)
                    ->first();
                if ($existingSop && $existingSop->no_doc) {
                    $noDoc = $existingSop->no_doc;
                } else {
                    $tanggalCarbon = \Carbon\Carbon::parse($tglOpname);
                    $count = WrmSoModel::whereMonth('tgl_opname', $tanggalCarbon->month)
                        ->whereYear('tgl_opname', $tanggalCarbon->year)
                        ->where('jenis_so', $jenisSo)
                        ->count();
                    $nextNum = $count + 1;
                    $nomor = str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                    $prefix = "WRM";
                    $bulanRomawi = $this->toRoman($tanggalCarbon->month);
                    $tahun = $tanggalCarbon->year;
                    $noDoc = "{$nomor}/{$prefix}/{$bulanRomawi}/{$tahun}";
                }

                $sop = WrmSoModel::updateOrCreate(
                    [
                        'tgl_opname' => $tglOpname,
                        'jenis_so'   => $jenisSo
                    ],
                    [
                        'user_id' => $user->id ?? 1,
                        'status' => $jenisSo === 'cycle_count' ? 'approved' : 'draft',
                        'no_doc' => $noDoc
                    ]
                );

                // Delete old details/summaries for this date and jenis_so
                WrmSoDetailModel::where('so_id', $sop->id)->delete();
                WrmSoSummariesModel::where('so_id', $sop->id)->delete();
                WrmSoApprovalModel::where('so_id', $sop->id)->delete();

                WrmSoApprovalModel::create([
                    'so_id' => $sop->id,
                    'approver_id' => $user->id,
                    'status' => $jenisSo === 'cycle_count' ? 'approved' : 'read',
                    'catatan' => $komentarFinal,
                    'action_at' => $jenisSo === 'cycle_count' ? now() : null,
                    'action_by' => $jenisSo === 'cycle_count' ? $user->id : null,
                ]);

                // Save details (individual entries from temp!)
                foreach ($tempData as $temp) {
                    $soh = $sohData->get($temp->barang_id . '-' . $temp->no_spb . '-' . $temp->pallet);
                    WrmSoDetailModel::create([
                        'so_id'      => $sop->id,
                        'barang_id'  => $temp->barang_id,
                        'no_spb'     => $temp->no_spb,
                        'pallet'     => $temp->pallet,
                        'jenis_data' => $soh ? $soh->jenis_data : null,
                        'qty_full'   => $temp->qty_full,
                        'qty_receh'  => $temp->qty_receh,
                        'created_at' => $temp->created_at,
                    ]);
                }

                // Save summaries
                foreach ($analysis as $item) {
                    WrmSoSummariesModel::create([
                        'so_id'      => $sop->id,
                        'barang_id'  => $item['barang_id'],
                        'no_spb'     => $item['no_spb'],
                        'pallet'     => $item['pallet'],
                        'jenis_data' => $item['jenis_data'],
                        'qty_fisik'  => $item['qty_fisik'],
                        'qty_sistem' => $item['qty_sistem'],
                        'selisih'    => $item['selisih'],
                        'status'     => $item['status'],
                        'keterangan' => $item['keterangan'],
                        'loc_id'     => $item['loc_id'],
                    ]);
                }

                // Update session status log
                WrmSoStatusModel::updateOrCreate(
                    [
                        'tgl_opname' => $tglOpname,
                        'jenis_so'   => $jenisSo
                    ],
                    [
                        'user_id' => $user->id ?? 1,
                        'status' => 'finished'
                    ]
                );

                // Clear temp tables for this date and jenis_so
                WrmSoTempModel::where('tgl_opname', $tglOpname)
                    ->whereHas('soh', function ($q) use ($jenisSo) {
                        $q->where('jenis_so', $jenisSo);
                    })->delete();

                WrmSoTempNoteModel::where('tgl_opname', $tglOpname)
                    ->whereHas('soh', function ($q) use ($jenisSo) {
                        $q->where('jenis_so', $jenisSo);
                    })->delete();

                DB::commit();

                $msg = $jenisSo === 'cycle_count' ?
                    'Stock Opname WRM berhasil disubmit final (Auto-Approved).' :
                    'Stock Opname WRM berhasil disubmit final. Status saat ini Draft. Silakan kirim persetujuan dari menu Report SO.';

                return response()->json([
                    'status' => 'success',
                    'message' => $msg
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan Stock Opname: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    public function getDataReport(Request $request)
    {
        $tglOpname = $request->input('tgl_opname', now()->toDateString());
        $jenisSo   = $request->input('jenis_so', 'cycle_count');

        $query = WrmSoModel::where('jenis_so', $jenisSo);
        if (strlen($tglOpname) === 7) {
            $parts = explode('-', $tglOpname);
            $query->whereYear('tgl_opname', $parts[0])
                ->whereMonth('tgl_opname', $parts[1]);
        } else {
            $query->whereDate('tgl_opname', $tglOpname);
        }
        $sop = $query->first();

        // Get all WrmSoModel records that are not approved yet (draft, pending, rejected)
        $unapprovedSops = WrmSoModel::where('status', '!=', 'approved')
            ->where('jenis_so', $jenisSo)
            ->orderBy('tgl_opname', 'asc')
            ->get(['id', 'tgl_opname', 'status', 'jenis_so']);

        if (!$sop) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan SO tidak ditemukan untuk tanggal tersebut.',
                'unapproved_sops' => $unapprovedSops
            ]);
        }

        // Load all pallet-level summaries and group by (barang_id, no_spb)
        $summaries = WrmSoSummariesModel::where('so_id', $sop->id)
            ->with(['barang:id,mid,nama_barang,uom', 'bin.location'])
            ->get();

        $grouped = $summaries->groupBy(fn($s) => $s->barang_id . '_' . $s->no_spb)
            ->map(function ($rows) {
                $first = $rows->first();

                // Get unique location texts
                $locations = $rows->map(function ($r) {
                    return $r->bin && $r->bin->location
                        ? "{$r->bin->location->gudang} - {$r->bin->location->bin}"
                        : null;
                })->filter()->unique()->implode(', ');

                return [
                    // Use the first pallet-row ID as the representative ID for backwards compat
                    'id'         => $first->id,
                    'so_id'      => $first->so_id,
                    'barang_id'  => $first->barang_id,
                    'no_spb'     => $first->no_spb,
                    'barang'     => $first->barang,
                    'qty_sistem' => $rows->sum('qty_sistem'),
                    'qty_fisik'  => $rows->sum('qty_fisik'),
                    'selisih'    => $rows->sum('selisih'),
                    'status'     => $this->aggregateStatus($rows->pluck('status')),
                    'keterangan' => $rows->pluck('keterangan')->filter()->implode('; '),
                    'pallet_count' => $rows->count(),
                    'location_text' => $locations ?: '-',
                ];
            })->values();

        return response()->json([
            'status'         => 'success',
            'sop'            => $sop,
            'data'           => $grouped,
            'jenis_so'       => $jenisSo,
            'unapproved_sops' => $unapprovedSops
        ]);
    }

    private function aggregateStatus($statuses): string
    {
        if ($statuses->contains('kurang')) return 'kurang';
        if ($statuses->contains('lebih'))  return 'lebih';
        return 'match';
    }

    public function getPendingApprovalReport(Request $request)
    {
        $user = Auth::user();

        $approvals = WrmSoApprovalModel::with([
            'approver:id,nama_lengkap,username,jabatan',
            'so:id,tgl_opname,status,user_id,jenis_so',
            'so.user:id,username,nama_lengkap',
        ])
            ->where('approver_id', $user->id)
            ->whereIn('wrm_so_approvals.status', ['pending', 'read'])
            ->join('wrm_so', 'wrm_so.id', '=', 'wrm_so_approvals.so_id')
            ->where('wrm_so.status', '!=', 'approved')
            ->orderByDesc('wrm_so.tgl_opname')
            ->orderByDesc('wrm_so_approvals.created_at')
            ->select('wrm_so_approvals.*')
            ->get();

        $items = $approvals->map(function ($approval) {
            $so = $approval->so;
            $status = strtolower($approval->status ?? 'pending');

            return [
                'id' => $approval->id,
                'so_id' => $approval->so_id,
                'tgl_opname' => $so?->tgl_opname ?? '-',
                'status_sop' => $so?->status ?? '-',
                'operator' => $so?->user?->nama_lengkap ?? $so?->user?->username ?? '-',
                'nama' => $approval->approver->nama_lengkap
                    ?? $approval->approver->username
                    ?? '-',
                'jabatan' => $approval->approver->jabatan ?? '-',
                'status' => $status,
                'catatan' => $approval->catatan,
                'action_at' => $approval->action_at
                    ? Carbon::parse($approval->action_at)->format('d M Y H:i')
                    : null,
                'requested_at' => optional($approval->created_at)->format('d M Y H:i'),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'approval_summary' => [
                'total_sop' => $items->pluck('so_id')->unique()->count(),
                'pending_count' => $items->count(),
                'read_count' => $items->where('status', 'read')->count(),
                'waiting_count' => $items->where('status', 'pending')->count(),
                'pending' => $items,
                'items' => $items,
            ],
        ]);
    }

    // Approval List
    public function getDataApproval()
    {
        $users = User::where(function ($q) {
            $q->where('jabatan', 'foreman')
                ->where('departemen', 'warehouse');
        })
            ->orWhere(function ($q) {
                $q->where('jabatan', 'supervisor')
                    ->where('departemen', 'warehouse');
            })
            ->get(['id', 'nama_lengkap', 'username', 'jabatan']);

        return response()->json([
            'foreman'     => $users->where('jabatan', 'foreman')->values(),
            'supervisors' => $users->whereIn('jabatan', ['supervisor', 'dept_head'])->values(),
        ]);
    }

    public function sendApproval(Request $request)
    {
        $request->validate([
            'so_id' => 'required|exists:wrm_so,id',
            'foreman_id' => 'required|exists:users,id',
            'supervisor_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $soId = $request->so_id;

        $so = WrmSoModel::where('id', $soId)->first();

        if (!$so) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data SO tidak ditemukan.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $existingApproval = WrmSoApprovalModel::where('so_id', $so->id)
                ->where('approver_id', $user->id)
                ->first();

            $oldNote = $existingApproval->catatan ?? null;

            WrmSoApprovalModel::updateOrCreate([
                'so_id' => $so->id,
                'approver_id' => $user->id,
            ], [
                'status' => 'approved',
                'action_at' => now(),
                'action_by' => $user->id,
                'catatan' => $oldNote,
            ]);

            $approverIds = array_unique([$request->foreman_id, $request->supervisor_id]);

            // Clear old approvals except creator
            WrmSoApprovalModel::where('so_id', $so->id)
                ->where('approver_id', '!=', $user->id)
                ->delete();

            $approvals = [];
            foreach ($approverIds as $approverId) {
                if ($approverId == $user->id) continue;

                $approvals[$approverId] = WrmSoApprovalModel::create([
                    'so_id'      => $so->id,
                    'approver_id' => $approverId,
                    'status'    => 'pending',
                    'action_at' => null,
                    'action_by' => null,
                    'catatan'   => null,
                ]);
            }

            $so->update(['status' => 'pending']);

            $title   = 'Approval SO WRM';
            $message = 'SO Warehouse Raw Material tanggal ' . $so->tgl_opname . ' menunggu persetujuan Anda.';
            $url     = route('wrm.stock_opname.report') . '?tgl_opname=' . $so->tgl_opname . '&jenis_so=' . $so->jenis_so;

            foreach ($approverIds as $approverId) {
                if ($approverId == $user->id) continue;

                NotificationsModel::where('user_id', $approverId)
                    ->where('url', $url)
                    ->delete();

                $approvalModel = $approvals[$approverId];

                NotificationsModel::create([
                    'user_id'         => $approverId,
                    'notifiable_type' => WrmSoApprovalModel::class,
                    'notifiable_id'   => $approvalModel->id,
                    'title'           => $title,
                    'message'         => $message,
                    'url'             => $url,
                    'is_read'         => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Approval berhasil dikirim ke Foreman dan Supervisor.",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim approval: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showApproval($id)
    {
        $so = WrmSoModel::findOrFail($id);
        $user = Auth::user();

        $approvals = WrmSoApprovalModel::with('approver:id,nama_lengkap,username,jabatan')
            ->where('so_id', $id)
            ->get();

        $tracking = $approvals->map(function ($approval) {
            return [
                'nama' => $approval->approver->nama_lengkap ?? '-',
                'jabatan' => $approval->approver->jabatan ?? '-',
                'status' => $approval->status,
                'catatan' => $approval->catatan,
                'action_at' => $approval->action_at ? Carbon::parse($approval->action_at)->format('Y-m-d H:i') : null,
            ];
        });

        $isApprover = $approvals->contains('approver_id', $user->id);
        $userApproval = $approvals->firstWhere('approver_id', $user->id);
        $approvalStatus = $userApproval ? $userApproval->status : null;
        $approvalNote = $userApproval ? $userApproval->catatan : null;

        return response()->json([
            'status' => 'success',
            'jenis_so' => $so->jenis_so,
            'status_sop' => $so->status,
            'approval_status' => $approvalStatus,
            'approval_note' => $approvalNote,
            'is_approver' => $isApprover,
            'is_operator' => $user->jabatan === 'operator',
            'approver_tracking' => $tracking
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'so_id' => 'required|exists:wrm_so,id',
            'status' => 'required|in:approved,rejected',
            'catatan' => $request->status === 'rejected' ? 'required|string' : 'nullable|string',
        ]);

        $user = Auth::user();

        $approval = WrmSoApprovalModel::where('so_id', $request->so_id)
            ->where('approver_id', $user->id)
            ->first();

        if (!$approval) {
            return response()->json([
                'message' => 'Anda tidak terdaftar sebagai approver untuk SO ini.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $approval->update([
                'status' => $request->status,
                'catatan' => $request->catatan,
                'action_at' => now(),
                'action_by' => $user->id,
            ]);

            // Delete user's notification
            NotificationsModel::where('notifiable_type', WrmSoApprovalModel::class)
                ->where('notifiable_id', $approval->id)
                ->where('user_id', $user->id)
                ->delete();

            $approvals = WrmSoApprovalModel::where('so_id', $request->so_id)->get();

            $allApproved = $approvals->every(fn($a) => $a->status === 'approved');
            $anyRejected = $approvals->contains(fn($a) => $a->status === 'rejected');

            if ($anyRejected) {
                $finalStatus = 'rejected';
            } elseif ($allApproved) {
                $finalStatus = 'approved';
            } else {
                $finalStatus = 'pending';
            }

            $so = WrmSoModel::findOrFail($request->so_id);
            $so->update([
                'status' => $finalStatus
            ]);

            // Notify the creator of the SO (operator) if approved or rejected
            if (in_array($finalStatus, ['approved', 'rejected']) && $so->user_id) {
                $statusText = $finalStatus === 'approved' ? 'DISETUJUI' : 'DITOLAK';
                $titleText = "SO WRM " . ($finalStatus === 'approved' ? 'Approved' : 'Rejected');
                $messageText = "Stock Opname Warehouse Raw Material tanggal " . $so->tgl_opname . " telah " . $statusText . " oleh " . $user->nama_lengkap . ".";

                if ($finalStatus === 'rejected' && $request->catatan) {
                    $messageText .= " Catatan: " . $request->catatan;
                }

                $url = route('wrm.stock_opname.report') . '?tgl_opname=' . $so->tgl_opname . '&jenis_so=' . $so->jenis_so;

                // Clear old status notifications for this SO for the creator
                NotificationsModel::where('user_id', $so->user_id)
                    ->where('url', $url)
                    ->where('title', 'like', 'SO WRM%')
                    ->delete();

                NotificationsModel::create([
                    'user_id'         => $so->user_id,
                    'notifiable_type' => WrmSoModel::class,
                    'notifiable_id'   => $so->id,
                    'title'           => $titleText,
                    'message'         => $messageText,
                    'url'             => $url,
                    'is_read'         => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $request->status === 'approved' ? 'SO berhasil disetujui.' : 'SO telah ditolak.',
                'data' => $approval
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getReportDetail(Request $request, int $id)
    {
        // $id is now the representative summary ID for a (barang_id, no_spb) group
        $representative = WrmSoSummariesModel::with('barang')->findOrFail($id);

         // Get all pallet-level rows for the same (so_id, barang_id, no_spb)
        $pallets = WrmSoSummariesModel::where('so_id', $representative->so_id)
            ->where('barang_id', $representative->barang_id)
            ->where('no_spb', $representative->no_spb)
            ->with('bin.location')
            ->get();

        // For each pallet row, also get its physical input history
        $palletsWithDetails = $pallets->map(function ($row) {
            $details = WrmSoDetailModel::where('so_id', $row->so_id)
                ->where('barang_id', $row->barang_id)
                ->where('no_spb', $row->no_spb)
                ->where('pallet', $row->pallet)
                ->get();

            return [
                'id'         => $row->id,
                'pallet'     => $row->pallet,
                'qty_sistem' => $row->qty_sistem,
                'qty_fisik'  => $row->qty_fisik,
                'selisih'    => $row->selisih,
                'status'     => $row->status,
                'keterangan' => $row->keterangan,
                'details'    => $details,
                'location_text' => $row->bin && $row->bin->location
                    ? "{$row->bin->location->plant} - {$row->bin->location->s_loc} - {$row->bin->location->gudang} - {$row->bin->location->zona} - {$row->bin->location->bin} - ({$row->bin->kolom}.{$row->bin->level})"
                    : '-',
            ];
        });

        $barang = $representative->barang;

        return response()->json([
            'status'     => 'success',
            'so_id'      => $representative->so_id,
            'barang_id'  => $representative->barang_id,
            'no_spb'     => $representative->no_spb,
            'summary'    => $representative,
            'pallets'    => $palletsWithDetails,
            'qty_kg'     => $barang ? $barang->qty_kg : 1
        ]);
    }

    public function updateReportRow(Request $request, int $id)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.qty_full' => 'nullable|integer|in:0,1',
            'items.*.qty_receh' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string|max:1000'
        ]);

        $items = $validated['items'];
        $keterangan = $validated['keterangan'] ?? null;

        DB::beginTransaction();
        try {
            $summary = WrmSoSummariesModel::with('barang')->findOrFail($id);

            // Check if SO is still in draft status
            $sopHeader = WrmSoModel::findOrFail($summary->so_id);
            // if ($sopHeader->status !== 'draft') {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Tidak dapat mengubah data karena status SO sudah ' . strtoupper($sopHeader->status) . '.'
            //     ], 422);
            // }

            $totalQtyFisik = 0;

            foreach ($items as $it) {
                $detail = WrmSoDetailModel::where('id', $it['id'])
                    ->where('so_id', $summary->so_id)
                    ->first();

                if (!$detail) continue;

                $qtyFull = isset($it['qty_full']) ? (int)$it['qty_full'] : 0;
                $qtyReceh = isset($it['qty_receh']) ? (int)$it['qty_receh'] : 0;

                if ($qtyReceh < 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Kuantitas tidak boleh negatif/minus!'
                    ], 422);
                }

                $detail->qty_full = $qtyFull;
                $detail->qty_receh = $qtyReceh;
                $detail->save();

                $totalQtyFisik += ($qtyFull === 1) ? $qtyReceh : 0;
            }

            // Calculate new summary values
            $diff = $totalQtyFisik - $summary->qty_sistem;
            $status = $diff > 0 ? 'lebih' : ($diff < 0 ? 'kurang' : 'match');

            // Update summary
            $summary->qty_fisik = $totalQtyFisik;
            $summary->selisih = $diff;
            $summary->status = $status;
            $summary->keterangan = $keterangan;
            $summary->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteReportRow(int $id)
    {
        DB::beginTransaction();
        try {
            $summary = WrmSoSummariesModel::findOrFail($id);

            // Check if SO is still in draft status
            $sopHeader = WrmSoModel::findOrFail($summary->so_id);
            if ($sopHeader->status !== 'draft') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat menghapus data karena status SO sudah ' . strtoupper($sopHeader->status) . '.'
                ], 422);
            }

            // Delete matching details
            WrmSoDetailModel::where('so_id', $summary->so_id)
                ->where('barang_id', $summary->barang_id)
                ->where('no_spb', $summary->no_spb)
                ->where('pallet', $summary->pallet)
                ->delete();

            // Delete summary
            $summary->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Item laporan berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus item laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteReportDetail(int $id)
    {
        DB::beginTransaction();
        try {
            $detail = WrmSoDetailModel::findOrFail($id);

            // Check if SO is still in draft status
            $sopHeader = WrmSoModel::findOrFail($detail->so_id);
            // if ($sopHeader->status !== 'draft') {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Tidak dapat menghapus data karena status SO sudah ' . strtoupper($sopHeader->status) . '.'
            //     ], 422);
            // }

            $detail->delete();

            // Recalculate summary
            $summary = WrmSoSummariesModel::where('so_id', $detail->so_id)
                ->where('barang_id', $detail->barang_id)
                ->where('no_spb', $detail->no_spb)
                ->where('pallet', $detail->pallet)
                ->first();

            if ($summary) {
                // Get all remaining details for this summary
                $remainingDetails = WrmSoDetailModel::where('so_id', $summary->so_id)
                    ->where('barang_id', $summary->barang_id)
                    ->where('no_spb', $summary->no_spb)
                    ->where('pallet', $summary->pallet)
                    ->get();

                $totalQtyFisik = 0;
                foreach ($remainingDetails as $det) {
                    $totalQtyFisik += ($det->qty_full === 1) ? $det->qty_receh : 0;
                }

                if ($remainingDetails->isEmpty()) {
                    // If no details left, delete summary as well
                    $summary->delete();
                } else {
                    $diff = $totalQtyFisik - $summary->qty_sistem;
                    $status = $diff > 0 ? 'lebih' : ($diff < 0 ? 'kurang' : 'match');

                    $summary->qty_fisik = $totalQtyFisik;
                    $summary->selisih = $diff;
                    $summary->status = $status;
                    $summary->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Detail berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus detail: ' . $e->getMessage()
            ], 500);
        }
    }

    private function toRoman($num)
    {
        $romans = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        return $romans[(int)$num] ?? '';
    }

    // export
    public function exportPdfSOWRM(Request $request)
    {
        $tglOpname = $request->input('tgl_opname', now()->toDateString());
        $jenisSo = $request->input('jenis_so', 'cycle_count');

        $query = WrmSoModel::where('jenis_so', $jenisSo);
        if (strlen($tglOpname) === 7) {
            $parts = explode('-', $tglOpname);
            $query->whereYear('tgl_opname', $parts[0])
                ->whereMonth('tgl_opname', $parts[1]);
        } else {
            $query->whereDate('tgl_opname', $tglOpname);
        }
        $so = $query->first();

        if (!$so) {
            return redirect()->back()->with('error', 'Data Stock Opname tidak ditemukan untuk tanggal ' . $tglOpname);
        }

        $summaries = WrmSoSummariesModel::where('so_id', $so->id)
            ->with(['barang:id,mid,nama_barang,uom', 'bin.location'])
            ->get();

        if ($summaries->isEmpty()) {
            return redirect()->back()->with('error', 'Data Stock Opname kosong untuk tanggal ' . $tglOpname);
        }

        $approvals = WrmSoApprovalModel::with('approver.signature')
            ->where('so_id', $so->id)
            ->get();

        $getSignaturePath = function ($user, $status) {
            $dummyApproved = public_path('assets/images/ttd/approved_sticker.png');

            if (strtolower($status) !== 'approved') {
                return null;
            }

            if (!$user) {
                return \Illuminate\Support\Facades\File::exists($dummyApproved) ? $dummyApproved : null;
            }

            if (isset($user->signature) && !empty($user->signature->signature)) {
                $signaturePath = public_path($user->signature->signature);
                if (\Illuminate\Support\Facades\File::exists($signaturePath)) {
                    return $signaturePath;
                }
            }

            $usernameFile = 'uploads/signatures/signature_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $user->username) . '.png';
            $filePath = public_path($usernameFile);
            if (\Illuminate\Support\Facades\File::exists($filePath)) {
                return $filePath;
            }

            return \Illuminate\Support\Facades\File::exists($dummyApproved) ? $dummyApproved : null;
        };

        $approvers = [];

        // 1. Creator / Stock Control
        $operatorApproval = $approvals->first(fn($a) => $a->approver_id == $so->user_id);
        $approvers[] = [
            'nama' => $operatorApproval?->approver?->nama_lengkap ?? $operatorApproval?->approver?->username ?? '-',
            'status' => 'approved',
            'ttd' => $getSignaturePath($operatorApproval?->approver, 'approved'),
            'catatan' => $operatorApproval?->catatan ?? '-',
            'action_at' => $so->created_at ? Carbon::parse($so->created_at)->format('d/m/Y H:i') : '',
        ];

        // 2. Foreman
        $foremanApproval = $approvals->first(fn($a) => $a->approver && $a->approver->jabatan === 'foreman');
        $approvers[] = [
            'nama' => $foremanApproval?->approver?->nama_lengkap ?? $foremanApproval?->approver?->username ?? '-',
            'status' => $foremanApproval?->status ?? 'pending',
            'ttd' => $getSignaturePath($foremanApproval?->approver, $foremanApproval?->status ?? 'pending'),
            'catatan' => $foremanApproval?->catatan ?? '',
            'action_at' => $foremanApproval?->action_at ? Carbon::parse($foremanApproval->action_at)->format('d/m/Y H:i') : '',
        ];

        // 3. Supervisor / Dept Head
        $supervisorApproval = $approvals->first(fn($a) => $a->approver && in_array($a->approver->jabatan, ['supervisor', 'dept_head']));
        $approvers[] = [
            'nama' => $supervisorApproval?->approver?->nama_lengkap ?? $supervisorApproval?->approver?->username ?? '-',
            'status' => $supervisorApproval?->status ?? 'pending',
            'ttd' => $getSignaturePath($supervisorApproval?->approver, $supervisorApproval?->status ?? 'pending'),
            'catatan' => $supervisorApproval?->catatan ?? '',
            'action_at' => $supervisorApproval?->action_at ? Carbon::parse($supervisorApproval->action_at)->format('d/m/Y H:i') : '',
        ];

        $pdf = Pdf::loadView('pdf.so_wrm_report', [
            'so' => $so,
            'summaries' => $summaries,
            'tglOpname' => $tglOpname,
            'approvers' => $approvers,
        ])->setPaper('a4', 'portrait');

        $pdf->getDomPDF()->set_option("isPhpEnabled", true);

        return $pdf->stream('SO_WRM_Report_' . $tglOpname . '.pdf');
    }
}
