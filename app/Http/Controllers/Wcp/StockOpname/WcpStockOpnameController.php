<?php

namespace App\Http\Controllers\Wcp\StockOpname;

use App\Http\Controllers\Controller;
use App\Models\Wcp\StockOpname\WcpSohModel;
use App\Models\Wcp\StockOpname\WcpSoModel;
use App\Models\Wcp\StockOpname\WcpSoDetailModel;
use App\Models\Wcp\StockOpname\WcpSoSummariesModel;
use App\Models\Wcp\StockOpname\WcpSoTempModel;
use App\Models\Wcp\StockOpname\WcpSoTempNoteModel;
use App\Models\Wcp\StockOpname\WcpSoStatusModel;
use App\Models\Wcp\StockOpname\WcpSoApprovalModel;
use App\Models\Wcp\WcpMasterBarangModel;
use App\Models\User;
use App\Models\NotificationsModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class WcpStockOpnameController extends Controller
{
    public function startOpname(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $existing = WcpSoStatusModel::whereDate('tgl_opname', $today)->first();

        if ($existing) {
            return response()->json([
                'status' => true,
                'message' => 'Opname sudah dimulai sebelumnya',
                'data' => $existing
            ]);
        }

        $status = WcpSoStatusModel::create([
            'user_id' => $user->id ?? 1,
            'tgl_opname' => $today,
            'status' => 'started',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Opname berhasil dimulai',
            'data' => $status
        ]);
    }

    private function checkSoWriteAccess()
    {
        $today = now()->toDateString();
        $status = WcpSoStatusModel::whereDate('tgl_opname', $today)->first();
        if ($status && $status->status === 'started' && Auth::id() != $status->user_id) {
            return false;
        }
        return true;
    }

    public function getStatusOpname(Request $request)
    {
        $today = now()->toDateString();
        $status = WcpSoStatusModel::with('user')->whereDate('tgl_opname', $today)->first();
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
            'started_by' => $status->user->nama_lengkap ?? $status->user->username ?? 'Stock Control'
        ]);
    }

    public function getData(Request $request)
    {
        $search = $request->input('search');
        $today = now()->toDateString();

        // Query all SOH entered today
        $query = WcpSohModel::query()
            ->select('wcp_soh.*')
            ->leftJoin('wcp_master_barang', 'wcp_soh.barang_id', '=', 'wcp_master_barang.id')
            ->whereDate('wcp_soh.created_at', $today);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wcp_master_barang.mid', 'like', '%' . $search . '%')
                    ->orWhere('wcp_master_barang.nama_barang', 'like', '%' . $search . '%');
            });
        }

        $sohList = $query->with('barang')->get();

        // Pull active temp values grouped by barang_id
        $tempData = WcpSoTempModel::whereDate('tgl_opname', $today)->get()->groupBy('barang_id');

        $tempNotes = WcpSoTempNoteModel::whereDate('tgl_opname', $today)->get()->keyBy('barang_id');

        $result = $sohList->map(function ($soh) use ($tempData, $tempNotes) {
            $temps = $tempData->get($soh->barang_id);
            $note = $tempNotes->get($soh->barang_id);

            $isCounted = $temps && $temps->isNotEmpty();
            $qtyFull = 0;
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
                'qty_pallet' => (float)($soh->barang->qty_pallet ?? 1),
                'qty_soh' => $soh->qty_soh,
                'qty_unrest' => $soh->qty_unrest,
                'qty_qi' => $soh->qty_qi,
                'qty_block' => $soh->qty_block,
                'qty_full' => 0,
                'qty_receh' => $qtyReceh,
                'summary' => $summary,
                'catatan' => $note ? $note->catatan : null,
                'diff_status' => $diffStatus,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    public function saveTemp(Request $request)
    {
        if (!$this->checkSoWriteAccess()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        $request->validate([
            'soh_id' => 'required|exists:wcp_soh,id',
            'qty_receh' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $soh = WcpSohModel::with('barang')->findOrFail($request->soh_id);
        $barang = $soh->barang;
        $user = Auth::user();
        $today = now()->toDateString();

        $qtyReceh = $request->qty_receh;

        $hasQty = ($qtyReceh !== null && $qtyReceh !== '');
        $temp = null;

        if ($hasQty) {
            $qtyRecehVal = (int)($qtyReceh ?? 0);
            $summary = $qtyRecehVal;

            $temp = WcpSoTempModel::create([
                'soh_id' => $soh->id,
                'barang_id' => $soh->barang_id,
                'qty_full' => 0,
                'qty_receh' => $qtyRecehVal,
                'summary' => $summary,
                'tgl_opname' => $today,
                'created_by' => $user->id ?? 1,
            ]);
        }

        // Save notes temp if present
        if ($request->has('keterangan')) {
            $catatan = trim($request->keterangan ?? '');
            if ($catatan !== '') {
                WcpSoTempNoteModel::updateOrCreate(
                    [
                        'soh_id' => $soh->id,
                        'barang_id' => $soh->barang_id,
                        'tgl_opname' => $today
                    ],
                    [
                        'catatan' => $catatan,
                        'created_by' => $user->id ?? 1,
                    ]
                );
            } else {
                WcpSoTempNoteModel::where('soh_id', $soh->id)
                    ->whereDate('tgl_opname', $today)
                    ->delete();
            }
        }

        // Hitung total summary dari semua temp untuk soh_id ini hari ini
        $allTemps = WcpSoTempModel::where('soh_id', $soh->id)
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
        $qtyRecords = WcpSoTempModel::with(['barang', 'soh.barang'])
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
                    'qty_full'    => 0,
                    'qty_receh'   => $rec->qty_receh,
                    'summary'     => (int) $rec->summary,
                    'mode'        => 'qty',
                    'created_at'  => $rec->created_at->toDateTimeString(),
                    'updated_at'  => $rec->updated_at->toDateTimeString(),
                ];
            })->filter()->values();

        // Get Note
        $noteRecords = WcpSoTempNoteModel::with(['barang', 'soh.barang'])
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

        $dataQty = WcpSoTempModel::with('barang')
            ->where('soh_id', $sohId)
            ->whereDate('tgl_opname', $today)
            ->orderBy('updated_at', 'asc')
            ->get();

        $dataNote = WcpSoTempNoteModel::where('soh_id', $sohId)
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
        if (!$this->checkSoWriteAccess()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.qty_receh' => 'nullable|integer|min:0',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $items = $validated['items'];
        $catatan = $validated['catatan'] ?? null;

        DB::beginTransaction();
        try {
            $processedPairs = [];

            foreach ($items as $it) {
                $temp = WcpSoTempModel::with('barang', 'soh')->find($it['id']);
                if (!$temp || !$temp->barang) continue;

                $qtyReceh = isset($it['qty_receh']) ? (int)$it['qty_receh'] : 0;
                $summary = $qtyReceh;

                $temp->qty_full = 0;
                $temp->qty_receh = $qtyReceh;
                $temp->summary = $summary;
                $temp->save();

                $tglOpname = $temp->tgl_opname ? \Carbon\Carbon::parse($temp->tgl_opname)->toDateString() : now()->toDateString();
                $pairKey = $temp->soh_id . '|' . $tglOpname;
                $processedPairs[$pairKey] = [
                    'soh_id' => $temp->soh_id,
                    'barang_id' => $temp->barang_id,
                    'tgl_opname' => $tglOpname,
                ];
            }

            if ($catatan !== null) {
                foreach ($processedPairs as $pair) {
                    if (trim($catatan) !== '') {
                        WcpSoTempNoteModel::updateOrCreate(
                            [
                                'soh_id' => $pair['soh_id'],
                                'barang_id' => $pair['barang_id'],
                                'tgl_opname' => $pair['tgl_opname']
                            ],
                            [
                                'catatan' => trim($catatan),
                                'created_by' => Auth::id() ?? 1
                            ]
                        );
                    } else {
                        WcpSoTempNoteModel::where('soh_id', $pair['soh_id'])
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
        if (!$this->checkSoWriteAccess()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        try {
            $type = $request->input('tipe', 'qty');

            if ($type === 'note') {
                $deleted = WcpSoTempNoteModel::where('soh_id', $id)->delete();
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
                $temp = WcpSoTempModel::find($id);
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
        if (!$this->checkSoWriteAccess()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        $request->validate([
            'mid_barang' => 'required|exists:wcp_master_barang,mid',
            'unrest' => 'required|integer|min:0',
            'qi' => 'nullable|integer|min:0',
            'blocked' => 'nullable|integer|min:0',
            'qty_receh' => 'required|integer|min:0',
        ]);

        $barang = WcpMasterBarangModel::where('mid', $request->mid_barang)->firstOrFail();
        $user = Auth::user();
        $today = now()->toDateString();

        // Validasi jika MID sudah ada hari ini
        $exists = WcpSohModel::where('barang_id', $barang->id)
            ->whereDate('created_at', $today)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => "MID {$request->mid_barang} sudah ada hari ini."
            ], 422);
        }

        $summary = $request->qty_receh;

        // Create new SOH entry for today
        $soh = WcpSohModel::updateOrCreate(
            [
                'barang_id' => $barang->id,
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
        $temp = WcpSoTempModel::updateOrCreate(
            [
                'soh_id' => $soh->id,
                'barang_id' => $barang->id,
                'tgl_opname' => $today
            ],
            [
                'qty_full' => 0,
                'qty_receh' => $request->qty_receh,
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
        if (!$this->checkSoWriteAccess()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        $request->validate([
            'soh_id' => 'required|exists:wcp_soh,id'
        ]);

        $today = now()->toDateString();
        WcpSoTempModel::where('soh_id', $request->soh_id)->whereDate('tgl_opname', $today)->delete();
        WcpSoTempNoteModel::where('soh_id', $request->soh_id)->whereDate('tgl_opname', $today)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Baris berhasil di-reset.'
        ]);
    }

    public function processOpname(Request $request)
    {
        if (!$this->checkSoWriteAccess()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Stock opname sedang dilakukan oleh user lain.'
            ], 403);
        }

        $request->validate([
            'tgl_opname' => 'required|date',
            'mode' => 'required|in:check,final_prepare,final_submit',
            'keterangan' => 'nullable|array'
        ]);

        $user = Auth::user();
        $tglOpname = $request->tgl_opname;

        // Fetch temp data
        $tempData = WcpSoTempModel::with('barang')->where('tgl_opname', $tglOpname)->get();

        if ($tempData->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data opname fisik kosong. Silakan isi setidaknya satu item.'
            ]);
        }

        // Fetch all SOH today
        $sohData = WcpSohModel::whereDate('created_at', $tglOpname)->get()->keyBy('barang_id');

        // Pull active temp notes
        $tempNotes = WcpSoTempNoteModel::whereDate('tgl_opname', $tglOpname)->get()->pluck('catatan', 'soh_id');

        // Check if there are any items in SOH that haven't been counted yet
        $uncountedItems = [];
        foreach ($sohData as $barangId => $soh) {
            $counted = $tempData->firstWhere('soh_id', $soh->id);
            if (!$counted) {
                $uncountedItems[] = [
                    'mid' => $soh->barang->mid,
                    'nama_barang' => $soh->barang->nama_barang,
                    'qty_system' => $soh->qty_soh,
                ];
            }
        }

        // Group temp counts by soh_id to sum their values
        $groupedTemp = $tempData->groupBy('soh_id')->map(function ($items) {
            $first = $items->first();
            return [
                'soh_id' => $first->soh_id,
                'barang_id' => $first->barang_id,
                'barang' => $first->barang,
                'qty_full' => 0,
                'qty_receh' => $items->sum('qty_receh'),
                'summary' => $items->sum('summary'),
            ];
        });

        // Validate differences and comments
        $varianceIssues = [];
        $analysis = [];
        foreach ($groupedTemp as $temp) {
            $soh = $sohData->get($temp['barang_id']);
            $qtySystem = $soh ? $soh->qty_soh : 0;
            $qtyPhysical = $temp['summary'];
            $diff = $qtyPhysical - $qtySystem;

            // Resolve comment from temp notes or manual request input
            $comment = $request->input('keterangan.' . $temp['soh_id']) ?? $tempNotes->get($temp['soh_id']);

            $status = $diff > 0 ? 'lebih' : ($diff < 0 ? 'kurang' : 'match');

            if ($diff !== 0 && empty($comment)) {
                $varianceIssues[] = [
                    'mid' => $temp['barang']->mid,
                    'selisih' => $diff,
                ];
            }

            $analysis[] = [
                'soh_id' => $temp['soh_id'],
                'barang_id' => $temp['barang_id'],
                'mid' => $temp['barang']->mid ?? '-',
                'nama_barang' => $temp['barang']->nama_barang ?? '-',
                'qty_fisik' => $qtyPhysical,
                'qty_sistem' => $qtySystem,
                'selisih' => $diff,
                'status' => $status,
                'keterangan' => $comment,
                'qty_full' => 0,
                'qty_receh' => $temp['qty_receh'],
            ];
        }

        if ($request->mode === 'check') {
            if (count($uncountedItems) > 0) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Ada item yang belum di-opname!',
                    'uncounted' => $uncountedItems,
                    'analysis' => $analysis
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
                $existingSop = WcpSoModel::whereDate('tgl_opname', $tglOpname)->first();
                if ($existingSop && $existingSop->no_doc) {
                    $noDoc = $existingSop->no_doc;
                } else {
                    $tanggalCarbon = \Carbon\Carbon::parse($tglOpname);
                    $count = WcpSoModel::whereMonth('tgl_opname', $tanggalCarbon->month)
                        ->whereYear('tgl_opname', $tanggalCarbon->year)
                        ->count();
                    $nextNum = $count + 1;
                    $nomor = str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                    $prefix = "WCP";
                    $bulanRomawi = $this->toRoman($tanggalCarbon->month);
                    $tahun = $tanggalCarbon->year;
                    $noDoc = "{$nomor}/{$prefix}/{$bulanRomawi}/{$tahun}";
                }

                $sop = WcpSoModel::updateOrCreate(
                    ['tgl_opname' => $tglOpname],
                    [
                        'user_id' => $user->id ?? 1,
                        'status' => 'draft',
                        'no_doc' => $noDoc
                    ]
                );

                // Delete old details/summaries for this date
                WcpSoDetailModel::where('so_id', $sop->id)->delete();
                WcpSoSummariesModel::where('so_id', $sop->id)->delete();
                WcpSoApprovalModel::where('so_id', $sop->id)->delete();

                WcpSoApprovalModel::create([
                    'so_id' => $sop->id,
                    'approver_id' => $user->id,
                    'status' => 'read',
                    'catatan' => $komentarFinal
                ]);

                // Save details (individual entries from temp!)
                foreach ($tempData as $temp) {
                    WcpSoDetailModel::create([
                        'so_id' => $sop->id,
                        'barang_id' => $temp->barang_id,
                        'qty_full' => $temp->qty_full,
                        'qty_receh' => $temp->qty_receh,
                        'created_at' => $temp->created_at,
                    ]);
                }

                // Save summaries
                foreach ($analysis as $item) {
                    WcpSoSummariesModel::create([
                        'so_id' => $sop->id,
                        'barang_id' => $item['barang_id'],
                        'qty_fisik' => $item['qty_fisik'],
                        'qty_sistem' => $item['qty_sistem'],
                        'selisih' => $item['selisih'],
                        'status' => $item['status'],
                        'keterangan' => $item['keterangan'],
                    ]);
                }

                // Update session status log
                WcpSoStatusModel::updateOrCreate(
                    ['tgl_opname' => $tglOpname],
                    [
                        'user_id' => $user->id ?? 1,
                        'status' => 'finished'
                    ]
                );

                // Clear temp tables
                WcpSoTempModel::where('tgl_opname', $tglOpname)->delete();
                WcpSoTempNoteModel::where('tgl_opname', $tglOpname)->delete();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Stock Opname WCP berhasil disubmit final. Status saat ini Draft. Silakan kirim persetujuan dari menu Report SO.'
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

        $sop = WcpSoModel::whereDate('tgl_opname', $tglOpname)->first();

        // Get all WcpSoModel records that are not approved yet (draft, pending, rejected)
        $unapprovedSops = WcpSoModel::where('status', '!=', 'approved')
            ->orderBy('tgl_opname', 'asc')
            ->get(['id', 'tgl_opname', 'status']);

        if (!$sop) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan SO tidak ditemukan untuk tanggal tersebut.',
                'unapproved_sops' => $unapprovedSops
            ]);
        }

        $data = WcpSoSummariesModel::where('so_id', $sop->id)
            ->with('barang:id,mid,nama_barang,uom')
            ->get();

        return response()->json([
            'status' => 'success',
            'sop' => $sop, // send header to check status
            'data' => $data,
            'unapproved_sops' => $unapprovedSops
        ]);
    }

    public function getPendingApprovals(Request $request)
    {
        $user = Auth::user();

        $approvals = WcpSoApprovalModel::with([
            'approver:id,nama_lengkap,username,jabatan',
            'so:id,tgl_opname,status,user_id',
            'so.user:id,username,nama_lengkap',
        ])
            ->whereIn('wcp_so_approvals.status', ['pending', 'read'])
            ->join('wcp_so', 'wcp_so.id', '=', 'wcp_so_approvals.so_id')
            ->orderByDesc('wcp_so.tgl_opname')
            ->orderByDesc('wcp_so_approvals.created_at')
            ->select('wcp_so_approvals.*')
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
            'so_id' => 'required|exists:wcp_so,id',
            'foreman_id' => 'required|exists:users,id',
            'supervisor_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $soId = $request->so_id;

        $so = WcpSoModel::where('id', $soId)->first();

        if (!$so) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data SO tidak ditemukan.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $existingApproval = WcpSoApprovalModel::where('so_id', $so->id)
                ->where('approver_id', $user->id)
                ->first();

            $oldNote = $existingApproval->catatan ?? null;

            WcpSoApprovalModel::updateOrCreate([
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
            WcpSoApprovalModel::where('so_id', $so->id)
                ->where('approver_id', '!=', $user->id)
                ->delete();

            $approvals = [];
            foreach ($approverIds as $approverId) {
                if ($approverId == $user->id) continue;

                $approvals[$approverId] = WcpSoApprovalModel::create([
                    'so_id'      => $so->id,
                    'approver_id' => $approverId,
                    'status'    => 'pending',
                    'action_at' => null,
                    'action_by' => null,
                    'catatan'   => null,
                ]);
            }

            $so->update(['status' => 'pending']);

            $title   = 'Approval SO WCP';
            $message = 'SO Co Product tanggal ' . $so->tgl_opname . ' menunggu persetujuan Anda.';
            $url     = route('wcp.stock_opname.report') . '?tgl_opname=' . $so->tgl_opname;

            foreach ($approverIds as $approverId) {
                if ($approverId == $user->id) continue;

                NotificationsModel::where('user_id', $approverId)
                    ->where('url', $url)
                    ->delete();

                $approvalModel = $approvals[$approverId];

                NotificationsModel::create([
                    'user_id'         => $approverId,
                    'notifiable_type' => WcpSoApprovalModel::class,
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
        $so = WcpSoModel::findOrFail($id);
        $user = Auth::user();

        $approvals = WcpSoApprovalModel::with('approver:id,nama_lengkap,username,jabatan')
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
            'so_id' => 'required|exists:wcp_so,id',
            'status' => 'required|in:approved,rejected',
            'catatan' => $request->status === 'rejected' ? 'required|string' : 'nullable|string',
        ]);

        $user = Auth::user();

        $approval = WcpSoApprovalModel::where('so_id', $request->so_id)
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
            NotificationsModel::where('notifiable_type', WcpSoApprovalModel::class)
                ->where('notifiable_id', $approval->id)
                ->where('user_id', $user->id)
                ->delete();

            $approvals = WcpSoApprovalModel::where('so_id', $request->so_id)->get();

            $allApproved = $approvals->every(fn($a) => $a->status === 'approved');
            $anyRejected = $approvals->contains(fn($a) => $a->status === 'rejected');

            if ($anyRejected) {
                $finalStatus = 'rejected';
            } elseif ($allApproved) {
                $finalStatus = 'approved';
            } else {
                $finalStatus = 'pending';
            }

            $so = WcpSoModel::findOrFail($request->so_id);
            $so->update([
                'status' => $finalStatus
            ]);

            // Notify the creator of the SO (operator) if approved or rejected
            if (in_array($finalStatus, ['approved', 'rejected']) && $so->user_id) {
                $statusText = $finalStatus === 'approved' ? 'DISETUJUI' : 'DITOLAK';
                $titleText = "SO WCP " . ($finalStatus === 'approved' ? 'Approved' : 'Rejected');
                $messageText = "Stock Opname Co Product tanggal " . $so->tgl_opname . " telah " . $statusText . " oleh " . $user->nama_lengkap . ".";

                if ($finalStatus === 'rejected' && $request->catatan) {
                    $messageText .= " Catatan: " . $request->catatan;
                }

                $url = route('wcp.stock_opname.report') . '?tgl_opname=' . $so->tgl_opname;

                // Clear old status notifications for this SO for the creator
                NotificationsModel::where('user_id', $so->user_id)
                    ->where('url', $url)
                    ->where('title', 'like', 'SO WCP%')
                    ->delete();

                NotificationsModel::create([
                    'user_id'         => $so->user_id,
                    'notifiable_type' => WcpSoModel::class,
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

    public function getReportDetail(int $id)
    {
        $summary = WcpSoSummariesModel::with('barang')->findOrFail($id);

        $details = WcpSoDetailModel::where('so_id', $summary->so_id)
            ->where('barang_id', $summary->barang_id)
            ->get();

        $barang = $summary->barang;

        return response()->json([
            'status' => 'success',
            'summary' => $summary,
            'details' => $details,
            'qty_pallet' => $barang ? $barang->qty_pallet : 1
        ]);
    }

    public function updateReportRow(Request $request, int $id)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.qty_receh' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string|max:1000'
        ]);

        $items = $validated['items'];
        $keterangan = $validated['keterangan'] ?? null;

        DB::beginTransaction();
        try {
            $summary = WcpSoSummariesModel::with('barang')->findOrFail($id);

            // Check if SO is still in draft status
            $sopHeader = WcpSoModel::findOrFail($summary->so_id);
            if ($sopHeader->status !== 'draft') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat mengubah data karena status SO sudah ' . strtoupper($sopHeader->status) . '.'
                ], 422);
            }

            $totalQtyFisik = 0;

            foreach ($items as $it) {
                $detail = WcpSoDetailModel::where('id', $it['id'])
                    ->where('so_id', $summary->so_id)
                    ->first();

                if (!$detail) continue;

                $qtyReceh = isset($it['qty_receh']) ? (int)$it['qty_receh'] : 0;

                if ($qtyReceh < 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Kuantitas tidak boleh negatif/minus!'
                    ], 422);
                }

                $detail->qty_full = 0;
                $detail->qty_receh = $qtyReceh;
                $detail->save();

                $totalQtyFisik += $qtyReceh;
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
            $summary = WcpSoSummariesModel::findOrFail($id);

            // Check if SO is still in draft status
            $sopHeader = WcpSoModel::findOrFail($summary->so_id);
            if ($sopHeader->status !== 'draft') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat menghapus data karena status SO sudah ' . strtoupper($sopHeader->status) . '.'
                ], 422);
            }

            // Delete matching details
            WcpSoDetailModel::where('so_id', $summary->so_id)
                ->where('barang_id', $summary->barang_id)
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
            $detail = WcpSoDetailModel::findOrFail($id);

            // Check if SO is still in draft status
            $sopHeader = WcpSoModel::findOrFail($detail->so_id);
            if ($sopHeader->status !== 'draft') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat menghapus data karena status SO sudah ' . strtoupper($sopHeader->status) . '.'
                ], 422);
            }

            $detail->delete();

            // Recalculate summary
            $summary = WcpSoSummariesModel::where('so_id', $detail->so_id)
                ->where('barang_id', $detail->barang_id)
                ->first();

            if ($summary) {
                // Get all remaining details for this summary
                $remainingDetails = WcpSoDetailModel::where('so_id', $summary->so_id)
                    ->where('barang_id', $summary->barang_id)
                    ->get();

                $totalQtyFisik = 0;
                foreach ($remainingDetails as $det) {
                    $totalQtyFisik += $det->qty_receh;
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
    public function exportPdfSOWCP(Request $request)
    {
        $tglOpname = $request->input('tgl_opname', now()->toDateString());

        $so = WcpSoModel::whereDate('tgl_opname', $tglOpname)->first();

        if (!$so) {
            return redirect()->back()->with('error', 'Data Stock Opname tidak ditemukan untuk tanggal ' . $tglOpname);
        }

        $summaries = WcpSoSummariesModel::where('so_id', $so->id)
            ->with('barang:id,mid,nama_barang,uom')
            ->get();

        if ($summaries->isEmpty()) {
            return redirect()->back()->with('error', 'Data Stock Opname kosong untuk tanggal ' . $tglOpname);
        }

        $approvals = WcpSoApprovalModel::with('approver.signature')
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

        $pdf = Pdf::loadView('pdf.so_wcp_report', [
            'so' => $so,
            'summaries' => $summaries,
            'tglOpname' => $tglOpname,
            'approvers' => $approvers,
        ])->setPaper('a4', 'portrait');

        $pdf->getDomPDF()->set_option("isPhpEnabled", true);

        return $pdf->stream('SO_WCP_Report_' . $tglOpname . '.pdf');
    }
}
