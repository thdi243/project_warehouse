<?php

namespace App\Http\Controllers\Wrm\StockOpname;

use App\Http\Controllers\Controller;
use App\Models\Wrm\StockOpname\WrmSohModel;
use App\Models\Wrm\StockOpname\WrmSoModel;
use App\Models\Wrm\StockOpname\WrmSoSummariesModel;
use App\Models\Wrm\Inventory\StockOnHand;
use App\Models\Wrm\MasterBarangModel;
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
        $perPage = 100;
        $today = now()->toDateString();

        $query = WrmSohModel::query()
            ->select('wrm_soh.*')
            ->leftJoin('wrm_master_barang', 'wrm_soh.barang_id', '=', 'wrm_master_barang.id')
            ->leftJoin('users', 'wrm_soh.user_id', '=', 'users.id');

        $query->whereDate('wrm_soh.created_at', $today);

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

        return response()->json($data);
    }

    public function getBarang()
    {
        $barang = MasterBarangModel::select('id', 'mid', 'nama_barang', 'uom', 'qty_kg')->get();

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    public function getSpbList(Request $request)
    {
        $barangId = $request->input('barang_id');
        $mid = $request->input('mid');

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

        return response()->json([
            'status' => 'success',
            'data' => $spbList
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
            'barang_id' => 'required|exists:wrm_master_barang,id',
            'no_spb' => 'required|array',
            'no_spb.*' => 'nullable|string'
        ]);

        try {
            $today = now()->toDateString();
            $barangId = $request->barang_id;
            $spbs = $request->no_spb;

            // Validasi jika MID dan no_spb sama di hari ini dan sudah ada
            $barang = MasterBarangModel::findOrFail($barangId);
            $duplicateSpbs = [];
            foreach ($spbs as $spb) {
                if (empty($spb)) continue;

                $exists = WrmSohModel::where('barang_id', $barangId)
                    ->where('no_spb', $spb)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($exists) {
                    $duplicateSpbs[] = $spb;
                }
            }

            if (!empty($duplicateSpbs)) {
                return response()->json([
                    'status' => false,
                    'message' => "Data SOH untuk MID {$barang->mid} dengan No SPB (" . implode(', ', $duplicateSpbs) . ") sudah ada hari ini!"
                ], 422);
            }

            DB::beginTransaction();

            $createdCount = 0;
            $updatedCount = 0;

            foreach ($spbs as $spb) {
                if (empty($spb)) continue;

                // Fetch actual stock from wrm_stock_on_hand
                $stockQuery = StockOnHand::where('barang_id', $barangId)
                    ->where('no_spb', $spb)
                    ->whereNotIn('status', ['ISSUED', 'RESERVED', 'BA WAITING'])
                    ->get();

                $unrest = (int)$stockQuery->where('status', 'UNREST')->sum('qty');
                $qi     = (int)$stockQuery->where('status', 'QI')->sum('qty');
                $block  = (int)$stockQuery->where('status', 'BLOCKED')->sum('qty');
                $qty_soh = $unrest + $qi + $block;

                // Check if already exists for today. Update if exists, or create new.
                $soh = WrmSohModel::updateOrCreate(
                    [
                        'barang_id' => $barangId,
                        'no_spb' => $spb,
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
                $sop = WrmSoModel::whereDate('tgl_opname', $today)->first();
                if ($sop) {
                    $summary = WrmSoSummariesModel::where('so_id', $sop->id)
                        ->where('barang_id', $barangId)
                        ->where('no_spb', $spb)
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
        $request->validate([
            'unrest' => 'nullable|integer|min:0',
            'qi' => 'nullable|integer|min:0',
            'block' => 'nullable|integer|min:0',
            'no_spb' => 'nullable|string',
        ]);

        try {
            $soh = WrmSohModel::findOrFail($id);

            // Validasi jika MID dan no_spb sama di hari ini dan sudah ada (selain record ini)
            $exists = WrmSohModel::where('barang_id', $soh->barang_id)
                ->where('no_spb', $request->no_spb)
                ->whereDate('created_at', $soh->created_at)
                ->where('id', '!=', $soh->id)
                ->exists();

            if ($exists) {
                $barang = MasterBarangModel::find($soh->barang_id);
                return response()->json([
                    'status' => false,
                    'message' => "Data SOH untuk MID {$barang->mid} dengan No SPB {$request->no_spb} sudah ada hari ini!"
                ], 422);
            }

            $unrest = (int)($request->unrest ?? 0);
            $qi = (int)($request->qi ?? 0);
            $block = (int)($request->block ?? 0);
            $qty_soh = $unrest + $qi + $block;

            $soh->update([
                'no_spb' => $request->no_spb,
                'qty_soh' => $qty_soh,
                'qty_unrest' => $unrest,
                'qty_qi' => $qi,
                'qty_block' => $block,
                'user_id' => Auth::id() ?? $soh->user_id,
                'last_updated' => now()
            ]);

            // Update live comparison if a session is currently running
            $today = Carbon::today()->toDateString();
            $sop = WrmSoModel::whereDate('tgl_opname', $today)->first();

            if ($sop) {
                $summary = WrmSoSummariesModel::where('so_id', $sop->id)
                    ->where('barang_id', $soh->barang_id)
                    ->where('no_spb', $soh->no_spb)
                    ->first();

                if ($summary) {
                    $qtySistem = $qty_soh;
                    $qtyFisik = $summary->qty_fisik ?? 0;
                    $selisih = $qtyFisik - $qtySistem;
                    $status = $selisih > 0 ? 'lebih' : ($selisih < 0 ? 'kurang' : 'match');

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

    public function destroy(string $id)
    {
        $soh = WrmSohModel::findOrFail($id);
        $soh->delete();
        return response()->json([
            'status' => true,
            'message' => 'Stock On Hand berhasil dihapus'
        ]);
    }

    public function resetAll()
    {
        try {
            $today = now()->toDateString();
            $deleted = WrmSohModel::whereDate('created_at', $today)->delete();

            return response()->json([
                'status' => true,
                'message' => "Berhasil menghapus $deleted data SOH untuk hari ini."
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
            'file' => 'required|mimes:xlsx,xls'
        ]);

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
            $today = now()->toDateString();

            foreach ($rows as $index => $row) {
                if ($index == 1) {
                    $header = array_map(fn($h) => strtolower(trim($h)), $row);
                    $requiredHeaders = ['mid_barang', 'no_spb', 'unrest', 'qual_insp', 'blocked'];
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

            // Validasi jika MID dan no_spb sama di hari ini dan sudah ada
            $duplicatesInDb = [];
            $seenCombinations = [];
            $duplicatesInFile = [];

            foreach ($validData as $item) {
                $barang = $item['barang'];
                $data = $item['data'];

                $noSpb = empty($data['no_spb']) ? null : (string)$data['no_spb'];
                $combinationKey = $barang->mid . '-' . $noSpb;

                // Check duplicates in file
                if (in_array($combinationKey, $seenCombinations)) {
                    $duplicatesInFile[] = "MID: {$barang->mid}, SPB: " . ($noSpb ?? '-');
                } else {
                    $seenCombinations[] = $combinationKey;
                }

                // Check duplicates in database for today
                $exists = WrmSohModel::where('barang_id', $barang->id)
                    ->where('no_spb', $noSpb)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($exists) {
                    $duplicatesInDb[] = "MID: {$barang->mid}, SPB: " . ($noSpb ?? '-');
                }
            }

            if (!empty($duplicatesInFile) || !empty($duplicatesInDb)) {
                $allDuplicates = array_unique(array_merge($duplicatesInFile, $duplicatesInDb));
                return response()->json([
                    'status' => false,
                    'message' => 'Terdapat duplikasi data MID dan No SPB untuk hari ini: ' . implode('; ', $allDuplicates),
                    'duplicates' => $allDuplicates
                ], 422);
            }

            foreach ($validData as $item) {
                $barang = $item['barang'];
                $data = $item['data'];

                $noSpb = empty($data['no_spb']) ? null : (int)$data['no_spb'];
                $unrest = (int)($data['unrest'] ?? 0);
                $qual_insp = (int)($data['qual_insp'] ?? 0);
                $blocked = (int)($data['blocked'] ?? 0);
                $qty_soh = $unrest + $qual_insp + $blocked;

                // Check if already exists for today. If so, update it, else create it.
                $soh = WrmSohModel::updateOrCreate(
                    [
                        'barang_id' => $barang->id,
                        'no_spb' => $noSpb,
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
                $sop = WrmSoModel::whereDate('tgl_opname', $today)->first();
                if ($sop) {
                    $summary = WrmSoSummariesModel::where('so_id', $sop->id)
                        ->where('barang_id', $barang->id)
                        ->where('no_spb', $noSpb)
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
        $sheet->setCellValue('A2', '1200001'); // Sample MID
        $sheet->setCellValue('B2', '300050123'); // Sample SPB
        $sheet->setCellValue('C2', 500); // Unrest
        $sheet->setCellValue('D2', 0); // QI
        $sheet->setCellValue('E2', 0); // Blocked

        $fileName = 'Template_Stock_On_Hand_WRM_' . date('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"{$fileName}\"",
        ]);
    }
}
