<?php

namespace App\Http\Controllers\Wfg\stock_opname;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Validation\ValidationException;
use App\Models\Wfg\stock_opname\BarangWfgModel;
use App\Models\Wfg\stock_opname\StockOnHandModel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Wsp\StockOnHandModel as WspStockOnHandModel;

class StockOnHandWfgController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:wfg_barang,id',
            'qty_soh' => 'nullable|integer',
            'qty_pal' => 'nullable|integer',
            'unrest' => 'nullable|integer',
            'qi' => 'nullable|integer',
            'block' => 'nullable|integer',
            'in' => 'nullable|integer',
            'out' => 'nullable|integer',
            'penjualan' => 'nullable|integer',
            'scan_2' => 'nullable|integer',
        ]);

        try {
            $exists = StockOnHandModel::where('barang_id', $request->barang_id)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Mid Barang SOH untuk barang ini sudah diinput hari ini!',
                ], 409);
            }

            $soh = StockOnHandModel::create([
                'barang_id' => $request->barang_id,
                'user_id' => Auth::id() ?? 1,
                'qty_soh' => $request->qty_soh ?? 0,
                'qty_pal' => $request->qty_pal ?? 0,
                'qty_unrest' => $request->unrest ?? 0,
                'qty_qi' => $request->qi ?? 0,
                'qty_block' => $request->block ?? 0,
                'qty_in' => $request->in ?? 0,
                'qty_out' => $request->out ?? 0,
                'qty_penjualan' => $request->penjualan ?? 0,
                'qty_scan_2' => $request->scan_2 ?? 0,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Stock On Hand berhasil ditambahkan',
                'data' => $soh
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menambahkan Stock On Hand',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $soh = StockOnHandModel::with('barang:id,mid_barang') // kalau kamu punya relasi ke tabel barang
            ->find($id);

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

    public function getList(Request $request)
    {
        $searchTerm = $request->input('search');

        $query = StockOnHandModel::with('barang:id,mid_barang,nama_barang,qty_box', 'user:id,username');

        $query->when($searchTerm, function ($q) use ($searchTerm) {
            $q->whereHas('barang', function ($barangQuery) use ($searchTerm) {
                $barangQuery->where('nama_barang', 'like', '%' . $searchTerm . '%')
                    ->orWhere('mid_barang', 'like', '%' . $searchTerm . '%');
            });
        });

        // Urutkan dan batasi hasilnya
        $data = $query->orderBy('id', 'desc')
            ->take(50)
            ->get();

        return response()->json($data);
    }

    public function getBarang()
    {
        $barang = BarangWfgModel::select('id', 'mid_barang', 'nama_barang')->get();

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $soh = StockOnHandModel::findOrFail($id);

            $request->validate([
                'qty_soh' => 'nullable|integer',
                'qty_pal' => 'nullable|integer',
                'unrest' => 'nullable|integer',
                'qi' => 'nullable|integer',
                'block' => 'nullable|integer',
                'in' => 'nullable|integer',
                'out' => 'nullable|integer',
                'penjualan' => 'nullable|integer',
                'scan_2' => 'nullable|integer',
            ]);

            $soh->update([
                'qty_soh' => $request->qty_soh ?? $soh->qty_soh,
                'qty_pal' => $request->qty_pal ?? $soh->qty_pal,
                'qty_unrest' => $request->unrest ?? $soh->qty_unrest,
                'qty_qi' => $request->qi ?? $soh->qty_qi,
                'qty_block' => $request->block ?? $soh->qty_block,
                'qty_in' => $request->in ?? $soh->qty_in,
                'qty_out' => $request->out ?? $soh->qty_out,
                'qty_penjualan' => $request->penjualan ?? $soh->qty_penjualan,
                'qty_scan_2' => $request->scan_2 ?? $soh->qty_scan_2,
                'user_id' => Auth::id() ?? $soh->user_id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Stock On Hand berhasil diperbarui',
                'data' => $soh
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat memperbarui Stock On Hand',
                'error' => $e->getMessage() // optional, untuk debugging
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $soh = StockOnHandModel::findOrFail($id);
        $soh->delete();
        return response()->json([
            'status' => true,
            'message' => 'Stock On Hand berhasil dihapus'
        ]);
    }

    // Import dair Excel
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
            $today = now()->toDateString();

            foreach ($rows as $index => $row) {
                if ($index == 1) {
                    $header = array_map('strtolower', $row);
                    continue;
                }

                if (empty($row['A'])) continue;

                $data = array_combine($header, $row);

                if (empty($data['mid_barang'])) continue;

                $barang = BarangWfgModel::where('mid_barang', $data['mid_barang'])->first();

                if (!$barang) {
                    $notFound[] = $data['mid_barang'];
                    continue;
                }

                // Cek apakah sudah ada hari ini
                $soh = StockOnHandModel::where('barang_id', $barang->id)
                    ->whereDate('created_at', $today)
                    ->first();

                if ($soh) {
                    // Kalau sudah ada, skip (atau update jika mau)
                    continue;
                }

                // Kalau belum ada hari ini, buat baru
                StockOnHandModel::updateOrCreate(
                    [
                        'barang_id' => $barang->id,
                        'created_at' => $today, // key unik per hari
                    ],
                    [
                        'user_id' => Auth::id() ?? 1,
                        'qty_soh' => $data['qty_soh'] ?? 0,
                        'qty_pal' => $data['qty_pal'] ?? 0,
                        'qty_unrest' => $data['unrest'] ?? 0,
                        'qty_qi' => $data['qi'] ?? 0,
                        'qty_block' => $data['block'] ?? 0,
                        'qty_in' => $data['in'] ?? 0,
                        'qty_out' => $data['out'] ?? 0,
                        'qty_penjualan' => $data['penjualan'] ?? 0,
                        'qty_scan_2' => $data['scan_2'] ?? 0,
                    ]
                );

                $countSuccess++;
            }

            if (!empty($notFound)) {
                return response()->json([
                    'status' => false,
                    'message' => "Terdapat " . count($notFound) . " MID Barang yang tidak ditemukan di master barang.",
                    'not_found' => $notFound
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => "Berhasil import $countSuccess data Stock On Hand dari Excel. Data yang sudah ada hari ini tetap aman."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengimpor file Excel.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // download temlate
    public function downloadTemplate()
    {
        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul kolom (header template)
        $headers = [
            'mid_barang', // hanya ini diisi user
            'qty_soh',
            'qty_pal',
            'unrest',
            'qi',
            'block',
            'in',
            'out',
            'penjualan',
            'scan_2',
        ];

        // Isi header ke baris pertama
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '1', strtoupper($header));
            $sheet->getStyle($columnIndex . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
            $columnIndex++;
        }

        // Tambahkan contoh data (opsional)
        $sheet->setCellValue('A2', 'MID001');
        $sheet->setCellValue('B2', 100);
        $sheet->setCellValue('C2', 10);
        $sheet->setCellValue('D2', 5);
        $sheet->setCellValue('E2', 3);
        $sheet->setCellValue('F2', 2);
        $sheet->setCellValue('G2', 50);
        $sheet->setCellValue('H2', 30);
        $sheet->setCellValue('I2', 20);
        $sheet->setCellValue('J2', 0);

        // Nama file
        $fileName = 'Template_Stock_On_Hand.xlsx';

        // Buat response untuk download langsung tanpa simpan file ke server
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment;filename=\"Template_Stock_On_Hand.xlsx\"",
        ]);
    }
}
