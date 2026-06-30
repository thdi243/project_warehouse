<?php

namespace App\Http\Controllers\Wcp\StockOpname;

use App\Http\Controllers\Controller;
use App\Models\Wcp\StockOpname\WcpSohModel;
use App\Models\Wcp\StockOpname\WcpSoModel;
use App\Models\Wcp\StockOpname\WcpSoSummariesModel;
use App\Models\Wcp\WcpMasterBarangModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WcpStockOnHandController extends Controller
{
    public function getList(Request $request)
    {
        $searchTerm = $request->input('search');
        $perPage = 100;
        $today = now()->toDateString();

        $query = WcpSohModel::query()
            ->select('wcp_soh.*')
            ->leftJoin('wcp_master_barang', 'wcp_soh.barang_id', '=', 'wcp_master_barang.id')
            ->leftJoin('users', 'wcp_soh.user_id', '=', 'users.id');

        $query->whereDate('wcp_soh.created_at', $today);

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('wcp_master_barang.mid', 'like', '%' . $searchTerm . '%')
                    ->orWhere('wcp_master_barang.nama_barang', 'like', '%' . $searchTerm . '%');
            });
        }

        $query->with([
            'barang:id,mid,nama_barang,uom,qty_pallet',
            'user:id,username'
        ]);

        $data = $query->orderBy('wcp_soh.id', 'desc')->paginate($perPage);

        return response()->json($data);
    }

    public function getBarang()
    {
        $barang = WcpMasterBarangModel::select('id', 'mid', 'nama_barang', 'uom', 'qty_pallet')->get();

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    public function show(string $id)
    {
        $soh = WcpSohModel::with('barang:id,mid,nama_barang')->find($id);

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
            'barang_id' => 'required|exists:wcp_master_barang,id',
            'unrest' => 'nullable|integer|min:0',
            'qi' => 'nullable|integer|min:0',
            'block' => 'nullable|integer|min:0',
        ]);

        try {
            $today = now()->toDateString();
            $barangId = $request->barang_id;

            $barang = WcpMasterBarangModel::findOrFail($barangId);
            
            // Validasi jika MID sudah ada hari ini
            $exists = WcpSohModel::where('barang_id', $barangId)
                ->whereDate('created_at', $today)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => "Data SOH untuk MID {$barang->mid} sudah ada hari ini!"
                ], 422);
            }

            DB::beginTransaction();

            $unrest = (int)($request->unrest ?? 0);
            $qi     = (int)($request->qi ?? 0);
            $block  = (int)($request->block ?? 0);
            $qty_soh = $unrest + $qi + $block;

            $soh = WcpSohModel::updateOrCreate(
                [
                    'barang_id' => $barangId,
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
            $sop = WcpSoModel::whereDate('tgl_opname', $today)->first();
            if ($sop) {
                $summary = WcpSoSummariesModel::where('so_id', $sop->id)
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
        $soh = WcpSohModel::findOrFail($id);

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
            $today = Carbon::today()->toDateString();
            $sop = WcpSoModel::whereDate('tgl_opname', $today)->first();

            if ($sop) {
                $summary = WcpSoSummariesModel::where('so_id', $sop->id)
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
        $soh = WcpSohModel::findOrFail($id);
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
            $deleted = WcpSohModel::whereDate('created_at', $today)->delete();

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

                $barang = WcpMasterBarangModel::where('mid', $data['mid_barang'])->first();

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
                    'message' => 'Beberapa MID Barang tidak ditemukan di master barang WCP: ' . implode(', ', $notFoundUnique),
                    'not_found' => $notFoundUnique
                ], 422);
            }

            // Validasi jika MID sama di hari ini dan sudah ada
            $duplicatesInDb = [];
            $seenCombinations = [];
            $duplicatesInFile = [];

            foreach ($validData as $item) {
                $barang = $item['barang'];
                $combinationKey = $barang->mid;

                // Check duplicates in file
                if (in_array($combinationKey, $seenCombinations)) {
                    $duplicatesInFile[] = "MID: {$barang->mid}";
                } else {
                    $seenCombinations[] = $combinationKey;
                }

                // Check duplicates in database for today
                $exists = WcpSohModel::where('barang_id', $barang->id)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($exists) {
                    $duplicatesInDb[] = "MID: {$barang->mid}";
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

                // Check if already exists for today. If so, update it, else create it.
                $soh = WcpSohModel::updateOrCreate(
                    [
                        'barang_id' => $barang->id,
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
                $sop = WcpSoModel::whereDate('tgl_opname', $today)->first();
                if ($sop) {
                    $summary = WcpSoSummariesModel::where('so_id', $sop->id)
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
                'message' => "Berhasil import $countSuccess data Stock On Hand WCP dari Excel."
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
        $sheet->setCellValue('A2', '52000001'); // Sample WCP MID
        $sheet->setCellValue('B2', 1500); // Unrest
        $sheet->setCellValue('C2', 0); // QI
        $sheet->setCellValue('D2', 0); // Blocked

        $fileName = 'Template_Stock_On_Hand_WCP_' . date('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"{$fileName}\"",
        ]);
    }
}
