<?php

namespace App\Http\Controllers\Wsp\StockOpname;

use App\Http\Controllers\Controller;
use App\Models\Wsp\StockOpname\WspSohModel;
use App\Models\Wsp\StockOpname\WspSoModel;
use App\Models\Wsp\StockOpname\WspSoSummariesModel;
use App\Models\Wsp\StockOpname\WspSoStatusModel;
use App\Models\Wsp\BarangModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Cache;

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

        $query->whereDate('wsp_soh.created_at', $today)
            ->where('wsp_soh.jenis_so', $jenisSo);

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('wsp_barang.mid_barang', 'like', '%' . $searchTerm . '%')
                    ->orWhere('wsp_barang.nama_barang', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->with([
            'barang:id,mid_barang,nama_barang,uom,qty_pallet',
            'user:id,username'
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

    public function show(string $id)
    {
        $soh = WspSohModel::with('barang:id,mid_barang,nama_barang')->find($id);

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
            'barang_id' => 'required|exists:wsp_barang,id',
            'unrest' => 'nullable|integer|min:0',
            'qi' => 'nullable|integer|min:0',
            'block' => 'nullable|integer|min:0',
            'jenis_so' => 'required|string|in:cycle_count,monthly',
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
            $currentYear = now()->year;
            $currentMonth = now()->month;
            $hasMonthlySo = WspSoStatusModel::where('jenis_so', 'monthly')
                ->whereYear('tgl_opname', $currentYear)
                ->whereMonth('tgl_opname', $currentMonth)
                ->whereDate('tgl_opname', '!=', $today)
                ->exists();
            if ($hasMonthlySo) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat menambah data SOH karena Stock Opname Monthly untuk bulan ini sudah pernah berjalan.'
                ], 422);
            }
        }

        try {
            $barangId = $request->barang_id;
            $barang = BarangModel::findOrFail($barangId);

            // Validasi jika MID sudah ada hari ini untuk jenis SO ini
            $exists = WspSohModel::where('barang_id', $barangId)
                ->where('jenis_so', $jenisSo)
                ->whereDate('created_at', $today)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => "Data SOH untuk MID {$barang->mid_barang} sudah ada {$periodeText}!"
                ], 422);
            }

            DB::beginTransaction();

            $unrest = (int)($request->unrest ?? 0);
            $qi     = (int)($request->qi ?? 0);
            $block  = (int)($request->block ?? 0);
            $qty_soh = $unrest + $qi + $block;

            $soh = WspSohModel::updateOrCreate(
                [
                    'barang_id' => $barangId,
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

            // Update summaries if there is a running opname today
            $sop = WspSoModel::whereDate('tgl_opname', $today)
                ->where('jenis_so', $jenisSo)
                ->first();
            if ($sop) {
                $summary = WspSoSummariesModel::where('so_id', $sop->id)
                    ->where('barang_id', $barangId)
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
                'status' => true,
                'message' => 'Stock On Hand berhasil dibuat',
                'data' => $soh
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat Stock On Hand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $soh = WspSohModel::findOrFail($id);

        $today = now()->toDateString();
        $periodeText = $soh->jenis_so === 'monthly' ? 'bulan ini' : 'hari ini';
        $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
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
        ]);

        try {
            $unrest = (int)($request->unrest ?? 0);
            $qi = (int)($request->qi ?? 0);
            $block = (int)($request->block ?? 0);
            $qty_soh = $unrest + $qi + $block;

            $soh->update([
                'qty_soh' => $qty_soh,
                'qty_unrest' => $unrest,
                'qty_qi' => $qi,
                'qty_block' => $block,
                'user_id' => Auth::id() ?? $soh->user_id,
                'last_updated' => now()
            ]);

            // Update live comparison if a session is currently running
            $sop = WspSoModel::whereDate('tgl_opname', $today)
                ->where('jenis_so', $soh->jenis_so)
                ->first();

            if ($sop) {
                $summary = WspSoSummariesModel::where('so_id', $sop->id)
                    ->where('barang_id', $soh->barang_id)
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
        $soh = WspSohModel::findOrFail($id);

        $today = now()->toDateString();
        $periodeText = $soh->jenis_so === 'monthly' ? 'bulan ini' : 'hari ini';
        $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
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
        $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
            ->where('jenis_so', $jenisSo)
            ->first();
        if ($soStatus && $soStatus->status === 'finished') {
            return response()->json([
                'status' => false,
                'message' => "Tidak dapat mengosongkan data SOH karena Stock Opname {$periodeText} telah selesai (finished) untuk jenis SO ini."
            ], 422);
        }

        try {
            $deleted = WspSohModel::whereDate('created_at', $today)
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
        $soStatus = WspSoStatusModel::whereDate('tgl_opname', $today)
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
            $hasMonthlySo = WspSoStatusModel::where('jenis_so', 'monthly')
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
                    $requiredHeaders = ['mid_barang', 'unrest', 'qual_insp', 'blocked'];
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

                $barang = BarangModel::where('mid_barang', $data['mid_barang'])->first();

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
                    'message' => 'Beberapa MID Barang tidak ditemukan di master barang WSP: ' . implode(', ', $notFoundUnique),
                    'not_found' => $notFoundUnique
                ], 422);
            }

            // Validasi jika MID sama di hari ini dan sudah ada untuk jenis SO ini
            $duplicatesInDb = [];
            $seenCombinations = [];
            $duplicatesInFile = [];

            foreach ($validData as $item) {
                $barang = $item['barang'];
                $combinationKey = $barang->mid_barang;

                // Check duplicates in file
                if (in_array($combinationKey, $seenCombinations)) {
                    $duplicatesInFile[] = "MID: {$barang->mid_barang}";
                } else {
                    $seenCombinations[] = $combinationKey;
                }

                // Check duplicates in database for today
                $exists = WspSohModel::where('barang_id', $barang->id)
                    ->where('jenis_so', $jenisSo)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($exists) {
                    $duplicatesInDb[] = "MID: {$barang->mid_barang}";
                }
            }

            if (!empty($duplicatesInFile) || !empty($duplicatesInDb)) {
                $allDuplicates = array_unique(array_merge($duplicatesInFile, $duplicatesInDb));
                return response()->json([
                    'status' => false,
                    'message' => 'Terdapat duplikasi data MID untuk hari ini: ' . implode('; ', $allDuplicates),
                    'duplicates' => $allDuplicates
                ], 422);
            }

            foreach ($validData as $item) {
                $barang = $item['barang'];
                $data = $item['data'];

                $unrest = (int)($data['unrest'] ?? 0);
                $qual_insp = (int)($data['qual_insp'] ?? 0);
                $blocked = (int)($data['blocked'] ?? 0);
                $qty_soh = $unrest + $qual_insp + $blocked;

                $soh = WspSohModel::updateOrCreate(
                    [
                        'barang_id' => $barang->id,
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
                $sop = WspSoModel::whereDate('tgl_opname', $today)
                    ->where('jenis_so', $jenisSo)
                    ->first();
                if ($sop) {
                    $summary = WspSoSummariesModel::where('so_id', $sop->id)
                        ->where('barang_id', $barang->id)
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
                'message' => "Berhasil import $countSuccess data Stock On Hand WSP dari Excel."
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
        $sheet->setCellValue('A2', '10000001'); // Sample WSP MID
        $sheet->setCellValue('B2', 50);  // Unrest
        $sheet->setCellValue('C2', 0);   // QI
        $sheet->setCellValue('D2', 0);   // Blocked

        $fileName = 'Template_Stock_On_Hand_WSP_' . date('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"{$fileName}\"",
        ]);
    }
}
