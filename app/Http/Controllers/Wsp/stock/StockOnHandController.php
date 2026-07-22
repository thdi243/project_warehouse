<?php

namespace App\Http\Controllers\Wsp\stock;

use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Wsp\stock_manage\StockOnHandWspModel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockOnHandController extends Controller
{
    public function getDataSOH()
    {
        try {
            // subquery: ambil last_update terbaru per barang
            $latestPerBarang = StockOnHandWspModel::select('barang_id')
                ->selectRaw('MAX(last_update) as last_update')
                ->groupBy('barang_id');


            $data = BarangModel::query()
                ->leftJoinSub($latestPerBarang, 'latest', function ($join) {
                    $join->on('wsp_barang.id', '=', 'latest.barang_id');
                })
                ->leftJoin('wsp_stock_on_hand as soh', function ($join) {
                    $join->on('wsp_barang.id', '=', 'soh.barang_id')
                        ->on('soh.last_update', '=', 'latest.last_update');
                })
                ->select([
                    'soh.id',
                    'wsp_barang.id as barang_id',
                    'wsp_barang.mid_barang',
                    'wsp_barang.nama_barang',
                    'wsp_barang.uom',
                    DB::raw('COALESCE(soh.qty_soh, 0) as qty_soh'),
                    DB::raw('COALESCE(soh.unrest, 0) as unrest'),
                    DB::raw('COALESCE(soh.qual_insp, 0) as qual_insp'),
                    DB::raw('COALESCE(soh.blocked, 0) as blocked'),
                    DB::raw('COALESCE(soh.transf, 0) as transf'),
                    'soh.last_update',
                ])
                ->orderBy('soh.last_update', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data Stock On Hand berhasil diambil.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Stock On Hand.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getBarang(Request $request)
    {
        $search = $request->search;

        $query = BarangModel::select('id', 'mid_barang', 'nama_barang');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('mid_barang', 'like', "%{$search}%");
            });
        }

        $barang = $query->limit(20)->get();

        $formatted = $barang->map(function ($item) {
            return [
                'id'   => $item->mid_barang,
                'mid_barang'  => $item->mid_barang,
                'nama_barang' => $item->nama_barang,
                'text' => $item->nama_barang,
            ];
        });

        return response()->json($formatted);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mid_barang' => 'required|string',
            'unrest'     => 'nullable|integer|min:0',
            'qual_insp'  => 'nullable|integer|min:0',
            'blocked'    => 'nullable|integer|min:0',
            'transf'     => 'nullable|integer|min:0',
        ]);

        try {
            // Cek apakah mid_barang ada di master barang
            $barang = BarangModel::where('mid_barang', $request->mid_barang)->first();
            if (!$barang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'MID Barang tidak ditemukan di master barang.',
                ], 404);
            }

            // Cek apakah data hari ini sudah ada
            $existsToday = StockOnHandWspModel::where('barang_id', $barang->id)
                ->whereDate('last_update', now())
                ->exists();

            if ($existsToday) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Stock On Hand untuk barang ini hari ini sudah ada.',
                ], 422);
            }

            $qty_soh =
                ($request->unrest ?? 0) +
                ($request->qual_insp ?? 0) +
                ($request->blocked ?? 0) +
                ($request->transf ?? 0);

            // Simpan data baru
            $soh = StockOnHandWspModel::create([
                'barang_id'   => $barang->id,
                'qty_soh'     => $qty_soh,
                'unrest'      => $request->unrest ?? 0,
                'qual_insp'   => $request->qual_insp ?? 0,
                'blocked'     => $request->blocked ?? 0,
                'transf'      => $request->transf ?? 0,
                'last_update' => now(),
                'created_by'  => Auth::id() ?? 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data Stock On Hand berhasil disimpan.',
                'data' => $soh,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $data = StockOnHandWspModel::with(['barang'])->find($id);

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => [
                'id' => $data->id,
                'barang_id' => $data->barang_id,
                'mid_barang' => $data->barang->mid_barang ?? '-',
                'nama_barang' => $data->barang->nama_barang ?? '-',
                'text' => "(" . $data->barang->mid_barang . ") " . $data->barang->nama_barang,
                'qty_soh' => $data->qty_soh,
                'unrest' => $data->unrest,
                'qual_insp' => $data->qual_insp,
                'blocked' => $data->blocked,
                'transf' => $data->transf,
                'created_by' => $data->created_by,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'mid_barang' => 'required|string',
            'unrest'     => 'nullable|integer|min:0',
            'qual_insp'  => 'nullable|integer|min:0',
            'blocked'    => 'nullable|integer|min:0',
            'transf'     => 'nullable|integer|min:0',
        ]);

        try {
            // Cek data SOH
            $soh = StockOnHandWspModel::find($id);
            if (!$soh) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Stock On Hand tidak ditemukan.',
                ], 404);
            }

            // Cek apakah mid_barang ada di master barang
            $barang = BarangModel::where('mid_barang', $request->mid_barang)->first();
            if (!$barang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'MID Barang tidak ditemukan di master barang.',
                ], 404);
            }

            $inputUnrest = $request->unrest ?? 0;

            $qty_soh =
                $inputUnrest +
                ($request->qual_insp ?? 0) +
                ($request->blocked ?? 0) +
                ($request->transf ?? 0);

            // Update data
            $soh->update([
                'barang_id'   => $barang->id,
                'qty_soh'     => $qty_soh,
                'unrest'      => $inputUnrest,
                'qual_insp'   => $request->qual_insp ?? 0,
                'blocked'     => $request->blocked ?? 0,
                'transf'      => $request->transf ?? 0,
                'last_update' => now(),
                'created_by'  => Auth::id() ?? 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data Stock On Hand berhasil diperbarui.',
                'data' => $soh,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $data = StockOnHandWspModel::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $data->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function upload(Request $request)
    {
        // Increase limits for large file processing
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (empty($rows)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'File Excel kosong atau tidak terbaca.',
                ], 422);
            }

            $userId = Auth::id() ?? 1;
            $headerRow1 = $rows[1] ?? [];
            $headerRow2 = $rows[2] ?? [];

            $template = null;
            if (isset($headerRow1['A']) && stripos($headerRow1['A'], 'MID_BARANG') !== false) {
                $template = 1;
            } elseif (isset($headerRow2['E']) && stripos($headerRow2['E'], 'Material') !== false) {
                $template = 2;
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Format template tidak dikenali.',
                ], 422);
            }

            // Step 1: Collect all unique MID_BARANG values from the file
            $midsInFile = [];
            foreach ($rows as $index => $row) {
                if ($template == 1 && $index == 1) continue;
                if ($template == 2 && $index <= 2) continue;

                $mid = trim($row[$template == 1 ? 'A' : 'E'] ?? '');
                if ($mid) {
                    $midsInFile[] = $mid;
                }
            }
            $midsInFile = array_unique($midsInFile);

            if (empty($midsInFile)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ada data MID Barang yang ditemukan dalam file.',
                ], 422);
            }

            // Step 2: Fetch all matching barang in ONE query for O(1) lookup
            $barangLookup = BarangModel::whereIn('mid_barang', $midsInFile)
                ->get(['id', 'mid_barang'])
                ->keyBy('mid_barang');



            $validData = [];
            $notFound = [];
            $now = now();

            // Step 3: Map data and validate existence
            foreach ($rows as $index => $row) {
                if ($template == 1 && $index == 1) continue;
                if ($template == 2 && $index <= 2) continue;

                $mid_barang = trim($row[$template == 1 ? 'A' : 'E'] ?? '');
                if (!$mid_barang) continue;

                $barang = $barangLookup->get($mid_barang);

                if (!$barang) {
                    $notFound[] = $mid_barang;
                    continue;
                }

                $unrest = (int) ($row[$template == 1 ? 'B' : 'F'] ?? 0);

                $qual_insp  = (int) ($row[$template == 1 ? 'C' : 'G'] ?? 0);
                $blocked    = (int) ($row[$template == 1 ? 'D' : 'H'] ?? 0);
                $transf     = (int) ($row[$template == 1 ? 'E' : 'I'] ?? 0);

                $validData[] = [
                    'barang_id'   => $barang->id,
                    'qty_soh'     => $unrest + $qual_insp + $blocked + $transf,
                    'unrest'      => $unrest,
                    'qual_insp'   => $qual_insp,
                    'blocked'     => $blocked,
                    'transf'      => $transf,
                    'last_update' => $now,
                    'created_by'  => $userId,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            // If there are any missing MIDs, reject the entire batch (current policy)
            if (!empty($notFound)) {
                return response()->json([
                    'status'        => 'error',
                    'message'       => 'Beberapa MID tidak ditemukan di master barang.',
                    'not_found'     => array_values(array_unique($notFound)),
                    'total_checked' => count($midsInFile),
                ], 422);
            }

            // Step 4: Bulk insert using transactions and chunks
            $saved = 0;
            DB::transaction(function () use ($validData, &$saved) {
                // Chunk the data to avoid database placeholder/size limits (standard is around 1000)
                $chunks = array_chunk($validData, 1000);
                foreach ($chunks as $chunk) {
                    StockOnHandWspModel::insert($chunk);
                    $saved += count($chunk);
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Upload data berhasil.',
                'saved'   => $saved,
                'total'   => count($validData),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
            ], 500);
        }
    }

    // download temlate
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'mid_barang',
            'unrest',
            'qual_insp',
            'blocked',
            'trans',
        ];

        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '1', strtoupper($header));
            $sheet->getStyle($columnIndex . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
            $columnIndex++;
        }

        $sheet->setCellValue('A2', '1160825');
        $sheet->setCellValue('B2', 886);
        $sheet->setCellValue('C2', 0);
        $sheet->setCellValue('D2', 0);
        $sheet->setCellValue('E2', 0);

        // Nama file
        $fileName = 'Template_Stock_On_Hand_Wsp_' . date('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"{$fileName}\"",
        ]);
    }

    public function exportExcel()
    {
        try {
            $latestPerBarang = StockOnHandWspModel::select('barang_id')
                ->selectRaw('MAX(last_update) as last_update')
                ->groupBy('barang_id');

            $data = BarangModel::query()
                ->leftJoinSub($latestPerBarang, 'latest', function ($join) {
                    $join->on('wsp_barang.id', '=', 'latest.barang_id');
                })
                ->leftJoin('wsp_stock_on_hand as soh', function ($join) {
                    $join->on('wsp_barang.id', '=', 'soh.barang_id')
                        ->on('soh.last_update', '=', 'latest.last_update');
                })
                ->select([
                    'wsp_barang.mid_barang',
                    'wsp_barang.nama_barang',
                    'wsp_barang.uom',
                    DB::raw('COALESCE(soh.unrest, 0) as unrest'),
                    DB::raw('COALESCE(soh.qual_insp, 0) as qual_insp'),
                    DB::raw('COALESCE(soh.blocked, 0) as blocked'),
                    'soh.last_update',
                ])
                ->orderBy('soh.last_update', 'desc')
                ->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                'MID',
                'Nama Barang',
                'UoM',
                'Unrest',
                'QI',
                'Blocked',
                'Last Updated'
            ];

            $columnIndex = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($columnIndex . '1', $header);
                $sheet->getStyle($columnIndex . '1')->getFont()->setBold(true);
                $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
                $columnIndex++;
            }

            $rowNum = 2;
            foreach ($data as $item) {
                $sheet->setCellValueExplicit('A' . $rowNum, $item->mid_barang, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('B' . $rowNum, $item->nama_barang);
                $sheet->setCellValue('C' . $rowNum, $item->uom);
                $sheet->setCellValue('D' . $rowNum, $item->unrest);
                $sheet->setCellValue('E' . $rowNum, $item->qual_insp);
                $sheet->setCellValue('F' . $rowNum, $item->blocked);
                $sheet->setCellValue('G' . $rowNum, $item->last_update ? date('d-m-Y H:i:s', strtotime($item->last_update)) : '-');
                $rowNum++;
            }

            $fileName = 'Data_Stock_On_Hand_Wsp_' . date('Y-m-d') . '.xlsx';

            return new StreamedResponse(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment;filename=\"{$fileName}\"",
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }
}
