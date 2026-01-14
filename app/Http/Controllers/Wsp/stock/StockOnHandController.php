<?php

namespace App\Http\Controllers\Wsp\stock;

use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
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
            $latestPerBarang = StockOnHandWspModel::select('barang_id')
                ->selectRaw('MAX(last_update) as last_update')
                ->groupBy('barang_id');

            $data = StockOnHandWspModel::with(['barang:id,mid_barang,nama_barang,uom'])
                ->joinSub($latestPerBarang, 'latest', function ($join) {
                    $join->on('wsp_stock_on_hand.barang_id', '=', 'latest.barang_id')
                        ->on('wsp_stock_on_hand.last_update', '=', 'latest.last_update');
                })
                ->orderBy('wsp_stock_on_hand.last_update', 'desc')
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

            $qty_soh =
                ($request->unrest ?? 0) +
                ($request->qual_insp ?? 0) +
                ($request->blocked ?? 0) +
                ($request->transf ?? 0);

            // Update data
            $soh->update([
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
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $notFound = [];
            $userId = Auth::id() ?? null;

            $template = null;

            $headerRow1 = $rows[1] ?? [];
            $headerRow2 = $rows[2] ?? []; // lebih aman pakai row 2 untuk template kedua

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

            // Kumpulkan dulu semua data + validasi existence
            $validData = [];

            foreach ($rows as $index => $row) {
                if ($template == 1) {
                    if ($index == 1) continue; // skip header

                    $mid_barang = trim($row['A'] ?? '');
                    $unrest     = (int) ($row['B'] ?? 0);
                    $qual_insp  = (int) ($row['C'] ?? 0);
                    $blocked    = (int) ($row['D'] ?? 0);
                    $transf     = (int) ($row['E'] ?? 0);
                } else {
                    if ($index <= 2) continue; // skip header 1 & 2

                    $mid_barang = trim($row['E'] ?? '');
                    $unrest     = (int) ($row['F'] ?? 0);
                    $qual_insp  = (int) ($row['G'] ?? 0);
                    $blocked    = (int) ($row['H'] ?? 0);
                    $transf     = (int) ($row['I'] ?? 0);
                }

                if (!$mid_barang) continue;

                $barang = BarangModel::where('mid_barang', $mid_barang)->first();

                if (!$barang) {
                    $notFound[] = $mid_barang;
                    continue;
                }

                $validData[] = [
                    'barang_id'  => $barang->id,
                    'qty_soh'    => $unrest + $qual_insp + $blocked + $transf,
                    'unrest'     => $unrest,
                    'qual_insp'  => $qual_insp,
                    'blocked'    => $blocked,
                    'transf'     => $transf,
                    'last_update' => now(),
                    'created_by'  => $userId,
                ];
            }

            // Jika ada yang tidak ditemukan → langsung reject
            if (!empty($notFound)) {
                return response()->json([
                    'status'   => 'error',
                    'message'  => 'Beberapa MID tidak ditemukan di master barang.',
                    'not_found' => $notFound,
                    'total_checked' => count($rows) - ($template == 1 ? 1 : 2),
                ], 422);
            }

            $saved = 0;
            foreach ($validData as $data) {
                StockOnHandWspModel::create($data);
                $saved++;
            }

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
}
