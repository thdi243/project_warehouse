<?php

namespace App\Http\Controllers\Wsp\StockOpname;

use App\Http\Controllers\Controller;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\RakModel;
use App\Models\Wsp\stock_manage\StockLocationModel;
use App\Models\Wsp\StockOpname\WspSoModel;
use App\Models\Wsp\StockOpname\WspSoStatusModel;
use App\Models\Wsp\StockOpname\WspSoSummariesModel;
use App\Models\Wsp\StockOpname\WspSohModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WspStockOnHandController extends Controller
{
    public function getList(Request $request)
    {
        $searchTerm = $request->input('search');
        $jenisSo = $request->input('jenis_so', 'cycle_count');
        $perPage = 100;
        $today = now()->toDateString();

        $query = WspSohModel::query()
            ->select('wsp_soh.*')
            ->leftJoin('wsp_barang', 'wsp_soh.barang_id', '=', 'wsp_barang.id')
            ->leftJoin('users', 'wsp_soh.user_id', '=', 'users.id');

        if ($jenisSo === 'monthly') {
            $query->whereYear('wsp_soh.created_at', now()->year)
                ->whereMonth('wsp_soh.created_at', now()->month);
        } else {
            $query->whereDate('wsp_soh.created_at', $today);
        }
        $query->where('wsp_soh.jenis_so', $jenisSo);

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('wsp_barang.mid_barang', 'like', '%' . $searchTerm . '%')
                    ->orWhere('wsp_barang.nama_barang', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->with([
            'barang:id,mid_barang,nama_barang,uom,qty_pallet',
            'user:id,username',
            'location.rak',
        ]);

        $data = $query->orderBy('wsp_soh.id', 'desc')->paginate($perPage);

        $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        $isFinished = $soStatus && $soStatus->status === 'finished';

        $responseData = $data->toArray();
        $responseData['is_finished'] = $isFinished;

        return response()->json($responseData);
    }

    public function getBarang()
    {
        $barang = Cache::store('redis')->remember('wsp_barang_list_soh', 3600, function () {
            return BarangModel::select('id', 'mid_barang', 'nama_barang', 'uom', 'qty_pallet')->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    public function getBarangStockLocation(Request $request)
    {
        $barangIds = StockLocationModel::where('status', 'active')->distinct()->pluck('barang_id');
        $barang = BarangModel::whereIn('id', $barangIds)->select('id', 'mid_barang', 'nama_barang', 'uom', 'qty_pallet')->get();

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    public function getRakList(Request $request)
    {
        $barangId = $request->input('barang_id');

        if (!$barangId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang ID diperlukan.'
            ], 422);
        }

        $query = StockLocationModel::where('status', 'active');
        if (is_array($barangId)) {
            $query->whereIn('barang_id', $barangId);
        } else {
            $query->where('barang_id', $barangId);
        }

        $locations = $query->with('rak')->get()->map(function ($loc) {
            if (!$loc->rak) return null;
            return [
                'id'       => $loc->id,
                'loc_id'   => $loc->id,
                'rak_id'   => $loc->rak->id,
                'barang_id' => $loc->barang_id,
                'area_rak' => $loc->rak->area_rak,
                'nama_rak' => $loc->rak->nama_rak,
                'kolom_rak' => $loc->rak->kolom_rak,
                'level_rak' => $loc->rak->level_rak,
                'bin_rak'  => $loc->rak->box_rak,
                'text'     => "{$loc->rak->plant} - {$loc->rak->s_loc} - {$loc->rak->area_rak}-{$loc->rak->nama_rak}-({$loc->rak->kolom_rak}.{$loc->rak->level_rak}.{$loc->rak->box_rak})"
            ];
        })->filter()->values();

        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }

    public function getAreaList(Request $request)
    {
        $areas = RakModel::distinct()->orderBy('area_rak', 'asc')->pluck('area_rak');
        return response()->json([
            'status' => 'success',
            'data' => $areas
        ]);
    }

    public function getNamaRakList(Request $request)
    {
        $area = $request->input('area');
        $query = RakModel::query();
        if ($area) {
            if (is_array($area)) {
                $query->whereIn('area_rak', $area);
            } else {
                $query->where('area_rak', $area);
            }
        }
        $racks = $query->distinct()->orderBy('nama_rak', 'asc')->pluck('nama_rak');
        return response()->json([
            'status' => 'success',
            'data' => $racks
        ]);
    }

    public function getBarangListByLocation(Request $request)
    {
        $area = $request->input('area');
        $namaRak = $request->input('nama_rak');

        $query = StockLocationModel::where('status', 'active');

        if ($area || $namaRak) {
            $query->whereHas('rak', function ($q) use ($area, $namaRak) {
                if ($area) {
                    if (is_array($area)) {
                        $q->whereIn('area_rak', $area);
                    } else {
                        $q->where('area_rak', $area);
                    }
                }
                if ($namaRak) {
                    if (is_array($namaRak)) {
                        $q->whereIn('nama_rak', $namaRak);
                    } else {
                        $q->where('nama_rak', $namaRak);
                    }
                }
            });
        }

        $barangIds = $query->distinct()->pluck('barang_id');
        $barang = BarangModel::whereIn('id', $barangIds)->select('id', 'mid_barang', 'nama_barang', 'uom', 'qty_pallet')->get();

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    public function show(string $id)
    {
        $soh = WspSohModel::with([
            'barang:id,mid_barang,nama_barang,uom',
            'location.rak',
        ])->find($id);

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
        // Bulk add — barang_id array + area + nama_rak (atau rak_id langsung)
        if ($request->has('barang_id') && is_array($request->barang_id)) {
            $request->validate([
                'barang_id' => 'required|array',
                'area'      => 'nullable|array',
                'nama_rak'  => 'nullable|array',
                'jenis_so'  => 'required|string|in:cycle_count,monthly',
            ]);

            $today = now()->toDateString();
            $jenisSo = $request->jenis_so;
            $periodeText = $jenisSo === 'monthly' ? 'bulan ini' : 'hari ini';

            $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
                ->where('jenis_so', $jenisSo)
                ->first();
            if ($soStatus && $soStatus->status === 'finished') {
                return response()->json([
                    'status' => false,
                    'message' => "Tidak dapat menambah data SOH karena Stock Opname {$periodeText} telah selesai (finished)."
                ], 422);
            }

            if ($jenisSo === 'monthly') {
                $currentYear  = now()->year;
                $currentMonth = now()->month;
                $hasMonthlySo = WspSoStatusModel::where('jenis_so', 'monthly')
                    ->whereYear('tgl_opname', $currentYear)
                    ->whereMonth('tgl_opname', $currentMonth)
                    ->whereDate('tgl_opname', '!=', $today)
                    ->exists();
                if ($hasMonthlySo) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Tidak dapat menambah data SOH karena Stock Opname Monthly untuk bulan ini sudah pernah berjalan.'
                    ], 422);
                }
            }

            try {
                DB::beginTransaction();

                $barangIds = $request->barang_id;

                // Resolve loc_ids dari area + nama_rak yang dipilih
                $locQuery = StockLocationModel::where('status', 'active')
                    ->whereIn('barang_id', $barangIds);

                if ($request->has('area') && $request->has('nama_rak')) {
                    $locQuery->whereHas('rak', function ($q) use ($request) {
                        $q->whereIn('area_rak', (array)$request->area)
                            ->whereIn('nama_rak', (array)$request->nama_rak);
                    });
                }

                $locationMaps = $locQuery->with('rak')->get()->groupBy('barang_id');

                $countSuccess = 0;
                $skipped = [];

                foreach ($barangIds as $barangId) {
                    $maps = $locationMaps->get($barangId, collect());

                    // Query system stock quantities
                    $systemStock = WspSohModel::where('barang_id', $barangId)->first();
                    $unrest  = $systemStock ? (int)$systemStock->unrest   : 0;
                    $qi      = $systemStock ? (int)$systemStock->qual_insp : 0;
                    $block   = $systemStock ? (int)$systemStock->blocked   : 0;
                    $qty_soh = $unrest + $qi + $block;

                    if ($maps->isEmpty()) {
                        // Barang tidak ada di stock location → simpan tanpa loc_id
                        $exists = WspSohModel::where('barang_id', $barangId)
                            ->whereNull('loc_id')
                            ->where('jenis_so', $jenisSo)
                            ->whereDate('created_at', $today)
                            ->exists();
                        if ($exists) {
                            $skipped[] = "MID barang_id:{$barangId} sudah ada.";
                            continue;
                        }
                        WspSohModel::create([
                            'barang_id'    => $barangId,
                            'loc_id'       => null,
                            'jenis_so'     => $jenisSo,
                            'user_id'      => Auth::id() ?? 1,
                            'qty_soh'      => $qty_soh,
                            'qty_unrest'   => $unrest,
                            'qty_qi'       => $qi,
                            'qty_block'    => $block,
                            'last_updated' => now(),
                        ]);
                        $countSuccess++;
                        continue;
                    }

                    foreach ($maps as $map) {
                        $exists = WspSohModel::where('barang_id', $barangId)
                            ->where('loc_id', $map->id)
                            ->where('jenis_so', $jenisSo)
                            ->whereDate('created_at', $today)
                            ->exists();

                        if ($exists) {
                            $rak = $map->rak;
                            $skipped[] = "MID barang_id:{$barangId} di Rak " .
                                ($rak ? "{$rak->area_rak}-{$rak->nama_rak}" : $map->id) . " sudah ada.";
                            continue;
                        }

                        $soh = WspSohModel::create([
                            'barang_id'    => $barangId,
                            'loc_id'       => $map->id,
                            'jenis_so'     => $jenisSo,
                            'user_id'      => Auth::id() ?? 1,
                            'qty_soh'      => $qty_soh,
                            'qty_unrest'   => $unrest,
                            'qty_qi'       => $qi,
                            'qty_block'    => $block,
                            'last_updated' => now(),
                        ]);

                        // Update summaries jika ada SO aktif hari ini
                        $sop = WspSoModel::whereDate('tgl_opname', $today)
                            ->where('jenis_so', $jenisSo)
                            ->first();
                        if ($sop) {
                            $summary = WspSoSummariesModel::where('so_id', $sop->id)
                                ->where('barang_id', $barangId)
                                ->where('loc_id', $map->id)
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
                }

                DB::commit();

                $msg = "Berhasil menambah $countSuccess data SOH manual.";
                if (!empty($skipped)) {
                    $msg .= " Beberapa item dilewati karena sudah ada.";
                }

                return response()->json(['status' => true, 'message' => $msg], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => 'Gagal menambah data SOH manual: ' . $e->getMessage()
                ], 500);
            }
        }

        // Single manual input — kirim loc_id dari edit form
        $request->validate([
            'barang_id' => 'required|exists:wsp_barang,id',
            'loc_id'    => 'nullable|exists:wsp_stock_location,id',
            'unrest'    => 'nullable|integer|min:0',
            'qi'        => 'nullable|integer|min:0',
            'block'     => 'nullable|integer|min:0',
            'jenis_so'  => 'required|string|in:cycle_count,monthly',
        ]);

        $today = now()->toDateString();
        $jenisSo = $request->jenis_so;
        $periodeText = $jenisSo === 'monthly' ? 'bulan ini' : 'hari ini';

        $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status'  => false,
                'message' => "Tidak dapat menambah data SOH karena Stock Opname {$periodeText} telah selesai (finished)."
            ], 422);
        }

        if ($jenisSo === 'monthly') {
            $currentYear  = now()->year;
            $currentMonth = now()->month;
            $hasMonthlySo = WspSoStatusModel::where('jenis_so', 'monthly')
                ->whereYear('tgl_opname', $currentYear)
                ->whereMonth('tgl_opname', $currentMonth)
                ->whereDate('tgl_opname', '!=', $today)
                ->exists();
            if ($hasMonthlySo) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tidak dapat menambah data SOH karena Stock Opname Monthly untuk bulan ini sudah pernah berjalan.'
                ], 422);
            }
        }

        try {
            $barangId = $request->barang_id;
            $locId    = $request->loc_id;
            $barang   = BarangModel::findOrFail($barangId);

            $exists = WspSohModel::where('barang_id', $barangId)
                ->where('loc_id', $locId)
                ->where('jenis_so', $jenisSo)
                ->whereDate('created_at', $today)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => false,
                    'message' => "Data SOH untuk MID {$barang->mid_barang} di lokasi tersebut sudah ada {$periodeText}!"
                ], 422);
            }

            DB::beginTransaction();

            $unrest  = (int)($request->unrest ?? 0);
            $qi      = (int)($request->qi     ?? 0);
            $block   = (int)($request->block   ?? 0);
            $qty_soh = $unrest + $qi + $block;

            $soh = WspSohModel::create([
                'barang_id'    => $barangId,
                'loc_id'       => $locId,
                'jenis_so'     => $jenisSo,
                'user_id'      => Auth::id() ?? 1,
                'qty_soh'      => $qty_soh,
                'qty_unrest'   => $unrest,
                'qty_qi'       => $qi,
                'qty_block'    => $block,
                'last_updated' => now(),
            ]);

            // Update summaries jika ada SO aktif hari ini
            $sop = WspSoModel::whereDate('tgl_opname', $today)
                ->where('jenis_so', $jenisSo)
                ->first();
            if ($sop) {
                $summary = WspSoSummariesModel::where('so_id', $sop->id)
                    ->where('barang_id', $barangId)
                    ->where('loc_id', $locId)
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

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Stock On Hand berhasil dibuat',
                'data'    => $soh
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat Stock On Hand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $soh = WspSohModel::findOrFail($id);

        $today = now()->toDateString();
        // $periodeText = $soh->jenis_so === 'monthly' ? 'bulan ini' : 'hari ini';
        // $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
        //     ->where('jenis_so', $soh->jenis_so)
        //     ->first();
        // if ($soStatus && $soStatus->status === 'finished') {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => "Tidak dapat memperbarui data SOH karena Stock Opname {$periodeText} telah selesai (finished) untuk jenis SO ini."
        //     ], 422);
        // }

        $request->validate([
            'unrest' => 'nullable|integer|min:0',
            'qi'     => 'nullable|integer|min:0',
            'block'  => 'nullable|integer|min:0',
        ]);

        try {
            $unrest  = (int)($request->unrest ?? 0);
            $qi      = (int)($request->qi     ?? 0);
            $block   = (int)($request->block   ?? 0);
            $qty_soh = $unrest + $qi + $block;

            $soh->update([
                'qty_soh'      => $qty_soh,
                'qty_unrest'   => $unrest,
                'qty_qi'       => $qi,
                'qty_block'    => $block,
                'user_id'      => Auth::id() ?? $soh->user_id,
                'last_updated' => now()
            ]);

            // Update live comparison jika ada SO aktif
            $sop = WspSoModel::whereDate('tgl_opname', $today)
                ->where('jenis_so', $soh->jenis_so)
                ->first();

            if ($sop) {
                $summary = WspSoSummariesModel::where('so_id', $sop->id)
                    ->where('barang_id', $soh->barang_id)
                    ->where('loc_id', $soh->loc_id)
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
                'status'  => true,
                'message' => 'Stock On Hand berhasil diperbarui',
                'data'    => $soh
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memperbarui Stock On Hand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $soh = WspSohModel::findOrFail($id);

        $today = now()->toDateString();
        $periodeText = $soh->jenis_so === 'monthly' ? 'bulan ini' : 'hari ini';
        $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $soh->jenis_so)
            ->first();
        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status'  => false,
                'message' => "Tidak dapat menghapus data SOH karena Stock Opname {$periodeText} telah selesai (finished) untuk jenis SO ini."
            ], 422);
        }

        $soh->delete();
        return response()->json([
            'status'  => true,
            'message' => 'Stock On Hand berhasil dihapus'
        ]);
    }

    public function resetAll(Request $request)
    {
        $today = now()->toDateString();
        $jenisSo = $request->input('jenis_so', 'cycle_count');
        $periodeText = $jenisSo === 'monthly' ? 'bulan ini' : 'hari ini';

        if ($jenisSo === 'monthly') {
            $currentYear = now()->year;
            $currentMonth = now()->month;
            $soStatus = WspSoStatusModel::where('jenis_so', 'monthly')
                ->whereYear('tgl_opname', $currentYear)
                ->whereMonth('tgl_opname', $currentMonth)
                ->where('status', 'finished')
                ->first();
        } else {
            $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
                ->where('jenis_so', $jenisSo)
                ->first();
        }

        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status'  => false,
                'message' => "Tidak dapat mengosongkan data SOH karena Stock Opname {$periodeText} telah selesai (finished) untuk jenis SO ini."
            ], 422);
        }

        try {
            $query = WspSohModel::where('jenis_so', $jenisSo);
            if ($jenisSo === 'monthly') {
                $query->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
            } else {
                $query->whereDate('created_at', $today);
            }

            $sohIds = $query->pluck('id');
            $hasTemp = \App\Models\Wsp\StockOpname\WspSoTempModel::whereIn('soh_id', $sohIds)->exists() ||
                \App\Models\Wsp\StockOpname\WspSoTempNoteModel::whereIn('soh_id', $sohIds)->exists();

            if ($hasTemp && !$request->boolean('confirm_temp')) {
                return response()->json([
                    'status' => 'confirm_temp',
                    'message' => "Terdapat data/draft input opname sementara (temp data) yang sedang berjalan untuk {$periodeText}. Jika Anda melanjutkan, data temp tersebut juga akan dihapus. Lanjutkan?"
                ]);
            }

            DB::beginTransaction();

            if ($hasTemp) {
                \App\Models\Wsp\StockOpname\WspSoTempModel::whereIn('soh_id', $sohIds)->delete();
                \App\Models\Wsp\StockOpname\WspSoTempNoteModel::whereIn('soh_id', $sohIds)->delete();
            }

            $deleted = $query->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Berhasil menghapus $deleted data SOH untuk {$periodeText}."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus data SOH: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file'     => 'required|mimes:xlsx,xls',
            'jenis_so' => 'required|string|in:cycle_count,monthly',
        ]);

        $today    = now()->toDateString();
        $jenisSo  = $request->input('jenis_so');
        $periodeText = $jenisSo === 'monthly' ? 'bulan ini' : 'hari ini';

        $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status'  => false,
                'message' => "Tidak dapat mengunggah file Excel karena Stock Opname {$periodeText} telah selesai (finished)."
            ], 422);
        }

        if ($jenisSo === 'monthly') {
            $currentYear  = now()->year;
            $currentMonth = now()->month;
            $hasMonthlySo = WspSoStatusModel::where('jenis_so', 'monthly')
                ->whereYear('tgl_opname', $currentYear)
                ->whereMonth('tgl_opname', $currentMonth)
                ->whereDate('tgl_opname', '!=', $today)
                ->exists();
            if ($hasMonthlySo) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tidak dapat mengunggah file Excel karena Stock Opname Monthly untuk bulan ini sudah pernah berjalan.'
                ], 422);
            }
        }

        try {
            $file  = $request->file('file');
            $path  = $file->getRealPath();

            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray(null, true, true, true);

            $header    = [];
            $notFound  = [];
            $validData = [];

            foreach ($rows as $index => $row) {
                if ($index == 1) {
                    $header = array_map(fn($h) => strtolower(trim($h)), $row);
                    $requiredHeaders = ['mid_barang', 'unrest', 'qual_insp', 'blocked'];
                    $missing = array_diff($requiredHeaders, $header);

                    if (!empty($missing)) {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Format file Excel tidak sesuai. Kolom berikut hilang: ' . implode(', ', $missing)
                        ], 422);
                    }
                    continue;
                }

                if (empty($row['A'])) continue;

                // Safely combine header and row to handle trailing columns
                $trimmedRow = array_map(fn($val) => $val !== null ? trim($val) : '', $row);
                $headerCount = count($header);
                $rowCount = count($trimmedRow);
                if ($rowCount > $headerCount) {
                    $trimmedRow = array_slice($trimmedRow, 0, $headerCount);
                } elseif ($rowCount < $headerCount) {
                    $trimmedRow = array_pad($trimmedRow, $headerCount, '');
                }
                $data = array_combine($header, $trimmedRow);

                if (empty($data['mid_barang'])) continue;

                $midBarang = trim($data['mid_barang']);
                // Skip comment rows in template
                if (str_starts_with($midBarang, '//')) continue;

                $barang = BarangModel::where('mid_barang', $midBarang)->first();

                if (!$barang) {
                    $notFound[] = $midBarang;
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
                    'status'   => false,
                    'message'  => 'Beberapa MID Barang tidak ditemukan di master barang WSP: ' . implode(', ', $notFoundUnique),
                    'not_found' => $notFoundUnique
                ], 422);
            }

            // Auto-expand/map SOH to location
            $toSave = [];
            foreach ($validData as $item) {
                $barang    = $item['barang'];
                $data      = $item['data'];
                $unrest    = (int)($data['unrest']    ?? 0);
                $qual_insp = (int)($data['qual_insp'] ?? 0);
                $blocked   = (int)($data['blocked']   ?? 0);

                // Read optional location columns
                $plant = isset($data['plant']) ? trim($data['plant']) : '';
                $sloc  = isset($data['s_loc']) ? trim($data['s_loc']) : '';
                $area  = isset($data['area_rak']) ? trim($data['area_rak']) : '';
                $nama  = isset($data['nama_rak']) ? trim($data['nama_rak']) : '';
                $kolom = isset($data['kolom_rak']) ? trim($data['kolom_rak']) : '';
                $level = isset($data['level_rak']) ? trim($data['level_rak']) : '';
                $box   = isset($data['box_rak']) ? trim($data['box_rak']) : '';

                $hasLocationInRow = ($plant !== '' || $sloc !== '' || $area !== '' || $nama !== '' || $kolom !== '' || $level !== '' || $box !== '');

                if ($hasLocationInRow) {
                    // Find or create the Rak record
                    $rak = RakModel::firstOrCreate([
                        'plant'     => $plant !== '' ? $plant : null,
                        's_loc'     => $sloc !== '' ? $sloc : null,
                        'area_rak'  => $area !== '' ? $area : null,
                        'nama_rak'  => $nama !== '' ? $nama : null,
                        'kolom_rak' => $kolom !== '' ? $kolom : null,
                        'level_rak' => $level !== '' ? $level : null,
                        'box_rak'   => $box !== '' ? $box : null,
                    ], [
                        'created_by' => Auth::id() ?? 1,
                    ]);

                    // Find existing Stock Location mapping by barang_id to prevent duplicates
                    $stockLoc = StockLocationModel::where('barang_id', $barang->id)->first();

                    if (!$stockLoc) {
                        $stockLoc = StockLocationModel::create([
                            'barang_id'  => $barang->id,
                            'rak_id'     => $rak->id,
                            'status'     => 'active',
                            'created_by' => Auth::id() ?? 1,
                        ]);
                    }

                    if ($stockLoc->status !== 'active') {
                        $stockLoc->status = 'active';
                        $stockLoc->save();
                    }

                    $toSave[] = [
                        'barang'    => $barang,
                        'loc_id'    => $stockLoc->id,
                        'unrest'    => $unrest,
                        'qual_insp' => $qual_insp,
                        'blocked'   => $blocked,
                    ];
                } else {
                    // Fallback to active mappings from stock_location
                    $mappings = StockLocationModel::where('barang_id', $barang->id)
                        ->where('status', 'active')
                        ->get();

                    if ($mappings->count() > 0) {
                        foreach ($mappings as $map) {
                            $toSave[] = [
                                'barang'    => $barang,
                                'loc_id'    => $map->id,
                                'unrest'    => $unrest,
                                'qual_insp' => $qual_insp,
                                'blocked'   => $blocked,
                            ];
                        }
                    } else {
                        // No mapping found -> Not Assigned
                        $toSave[] = [
                            'barang'    => $barang,
                            'loc_id'    => null,
                            'unrest'    => $unrest,
                            'qual_insp' => $qual_insp,
                            'blocked'   => $blocked,
                        ];
                    }
                }
            }

            // Cek duplikat
            $duplicatesInDb   = [];
            $seenCombinations = [];
            $duplicatesInFile = [];

            foreach ($toSave as $item) {
                $barang = $item['barang'];
                $locId  = $item['loc_id'];

                $combinationKey = "{$barang->mid_barang}|{$locId}";

                if (in_array($combinationKey, $seenCombinations)) {
                    $duplicatesInFile[] = "MID: {$barang->mid_barang} (loc_id: " . ($locId ?? 'null') . ")";
                } else {
                    $seenCombinations[] = $combinationKey;
                }

                $exists = WspSohModel::where('barang_id', $barang->id)
                    ->where('loc_id', $locId)
                    ->where('jenis_so', $jenisSo)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($exists) {
                    $duplicatesInDb[] = "MID: {$barang->mid_barang} (loc_id: " . ($locId ?? 'null') . ")";
                }
            }

            if (!empty($duplicatesInFile) || !empty($duplicatesInDb)) {
                $allDuplicates = array_unique(array_merge($duplicatesInFile, $duplicatesInDb));
                return response()->json([
                    'status'     => false,
                    'message'    => 'Terdapat duplikasi data MID + Lokasi untuk hari ini: ' . implode('; ', $allDuplicates),
                    'duplicates' => $allDuplicates
                ], 422);
            }

            $countSuccess = 0;
            foreach ($toSave as $item) {
                $barang    = $item['barang'];
                $locId     = $item['loc_id'];
                $unrest    = $item['unrest'];
                $qual_insp = $item['qual_insp'];
                $blocked   = $item['blocked'];
                $qty_soh   = $unrest + $qual_insp + $blocked;

                WspSohModel::updateOrCreate(
                    [
                        'barang_id'  => $barang->id,
                        'jenis_so'   => $jenisSo,
                        'loc_id'     => $locId,
                        'created_at' => $today
                    ],
                    [
                        'user_id'      => Auth::id() ?? 1,
                        'qty_soh'      => $qty_soh,
                        'qty_unrest'   => $unrest,
                        'qty_qi'       => $qual_insp,
                        'qty_block'    => $blocked,
                        'last_updated' => now(),
                    ]
                );

                // Update summaries
                $sop = WspSoModel::whereDate('tgl_opname', $today)
                    ->where('jenis_so', $jenisSo)
                    ->first();
                if ($sop) {
                    $summary = WspSoSummariesModel::where('so_id', $sop->id)
                        ->where('barang_id', $barang->id)
                        ->where('loc_id', $locId)
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
                'status'  => true,
                'message' => "Berhasil import $countSuccess data Stock On Hand WSP dari Excel."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengimpor file Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'mid_barang',
            'B' => 'unrest',
            'C' => 'qual_insp',
            'D' => 'blocked',
            'E' => 'plant',
            'F' => 's_loc',
            'G' => 'area_rak',
            'H' => 'nama_rak',
            'I' => 'kolom_rak',
            'J' => 'level_rak',
            'K' => 'box_rak',
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', strtoupper($header));
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sample data row
        $sheet->setCellValue('A2', '10000001');
        $sheet->setCellValue('B2', 50);
        $sheet->setCellValue('C2', 0);
        $sheet->setCellValue('D2', 0);
        $sheet->setCellValue('E2', '1006');
        $sheet->setCellValue('F2', 'G001');
        $sheet->setCellValue('G2', 'FL1');
        $sheet->setCellValue('H2', 'A');
        $sheet->setCellValue('I2', '1');
        $sheet->setCellValue('J2', '2');
        $sheet->setCellValue('K2', '01');

        $fileName = 'Template_Stock_On_Hand_WSP_' . date('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"{$fileName}\"",
        ]);
    }
}
