<?php

namespace App\Http\Controllers\Wsp;

use App\Models\Wsp\RakModel;
use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\TransaksiModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Wsp\StockBarangRakModel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Cache;

class WspBarangController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mid_barang'  => 'required|digits_between:1,8|integer',
            'nama_barang' => 'required|string|max:255',
            'uom'         => 'required|string|max:50',
            'qty_pallet'  => 'nullable|numeric|min:1',
            's_loc'       => 'nullable|string|max:50',
            'plant'       => 'nullable|string|max:50',
            'image'       => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        try {
            // Cek duplikat MID barang
            if (BarangModel::where('mid_barang', $request->mid_barang)->exists()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'MID Barang sudah terdaftar. Gunakan MID lain.',
                ], 409); // pakai 409 Conflict lebih semantik
            }

            // Upload gambar jika ada
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images/wsp', 'public');
            }

            // Simpan barang
            $barang = BarangModel::create([
                'created_by'     => Auth::id() ?? 1,
                'mid_barang'  => $request->mid_barang,
                'nama_barang' => $request->nama_barang,
                'uom'         => $request->uom,
                'qty_pallet'  => $request->qty_pallet ?? 1,
                's_loc'       => $request->s_loc,
                'plant'       => $request->plant,
                'image'       => $imagePath,
            ]);

            Cache::store('redis')->forget('wsp_barang_list_soh');

            return response()->json([
                'status'  => true,
                'message' => 'Barang baru berhasil diregistrasi dan disimpan.',
                'data'    => [
                    'barang' => $barang,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Ambil barang dan rak berdasarkan ID barang atau rak
        $barang = BarangModel::with('user')->find($id); // asumsi $id = id barang

        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Data barang tidak ditemukan.',
            ], 404);
        }

        // Ambil rak dari relasi
        $rak = $barang->rak;

        $data = [
            'id' => $barang->id,
            'nama_barang' => $barang->nama_barang,
            'mid_barang' => $barang->mid_barang,
            'uom' => $barang->uom,
            'qty_pallet' => $barang->qty_pallet ?? 1,
            's_loc' => $barang->s_loc,
            'plant' => $barang->plant,
            'image' => $barang->image,
            'username' => $barang->user->username ?? null,
        ];

        // Kembalikan JSON
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditemukan.',
            'data' => $data,
        ]);
    }

    public function getDataBarang()
    {
        $data = BarangModel::with([
            'user:id,username'
        ])
            ->get()
            ->map(function ($barang) {
                return [
                    'id'          => $barang->id,
                    'mid_barang'  => $barang->mid_barang,
                    'nama_barang' => $barang->nama_barang,
                    'uom'         => $barang->uom,
                    'qty_pallet'  => $barang->qty_pallet ?? 1,
                    's_loc'       => $barang->s_loc,
                    'plant'       => $barang->plant,
                    'username'    => $barang->user->username ?? null,
                    'image'       => $barang->image ? asset('storage/' . $barang->image) : null,
                ];
            })
            ->toArray();


        return response()->json([
            'status'  => true,
            'message' => 'Data barang berhasil ditemukan.',
            'data'    => $data,
        ]);
    }

    public function getDataBarangWsp(Request $request)
    {
        $search = $request->q;

        $data = BarangModel::select('id', 'mid_barang', 'nama_barang', 'uom')
            ->with([
                'latestStock' => function ($q) {
                    $q->select(
                        'wsp_stock_on_hand.id',
                        'wsp_stock_on_hand.barang_id',
                        'wsp_stock_on_hand.qty_soh'
                    );
                }
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('mid_barang', 'like', '%' . $search . '%')
                        ->orWhere('nama_barang', 'like', '%' . $search . '%');
                });
            })
            ->limit(20)
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Data barang berhasil diambil.',
            'data'    => $data,
        ]);
    }

    public function getDataRak()
    {
        // Ambil data rak langsung dari model
        $rak = RakModel::select('kode_rak', 'nama_rak', 'kolom_rak', 'level_rak')
            ->orderBy('kode_rak')
            ->get();

        if ($rak->isEmpty()) {
            return response()->json([
                'status' => 'empty',
                'message' => 'Data rak kosong',
                'data' => []
            ]);
        }

        // Kelompokkan data berdasarkan kode_rak
        $grouped = $rak->groupBy('kode_rak')->map(function ($items) {
            return [
                'nama_rak'   => $items->pluck('nama_rak')->unique()->values(),
                'kolom_rak'  => $items->pluck('kolom_rak')->unique()->values(),
                'level_rak'  => $items->pluck('level_rak')->unique()->values(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $grouped
        ]);
    }

    public function searchItems(Request $request)
    {
        $query = $request->input('q');

        $items = BarangModel::select('mid_barang', 'nama_barang')
            ->where('mid_barang', 'like', '%' . $query . '%')
            ->orWhere('nama_barang', 'like', '%' . $query . '%')
            ->get();

        return response()->json($items);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'midBarangEdit'  => 'required|digits_between:1,8|integer',
            'namaBarangEdit' => 'required|string|max:255',
            'uomEdit'        => 'required|string|max:50',
            'qtyPalletEdit'  => 'nullable|numeric|min:1',
            'sLocEdit'       => 'required|string|max:50',
            'plantEdit'      => 'nullable|string|max:50',
            'imageEdit'      => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $barang = BarangModel::find($id);
        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Data barang tidak ditemukan.',
            ], 404);
        }

        // Update data barang
        $barang->mid_barang = $request->midBarangEdit;
        $barang->nama_barang = $request->namaBarangEdit;
        $barang->uom = $request->uomEdit;
        $barang->qty_pallet = $request->qtyPalletEdit ?? 1;
        $barang->s_loc = $request->sLocEdit;
        $barang->plant = $request->plantEdit;

        if ($request->hasFile('imageEdit')) {
            $barang->image = $request->file('imageEdit')->store('images/wsp', 'public');
        }

        $barang->save();

        // Hubungkan barang dengan rak
        $barang->save();

        Cache::store('redis')->forget('wsp_barang_list_soh');

        return response()->json([
            'status'  => true,
            'message' => 'Data barang berhasil diupdate.',
            'data'    => [
                'barang' => $barang,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $barang = BarangModel::find($id);

        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Barang tidak ditemukan.',
            ], 404);
        }

        // Hapus image dari storage jika ada
        if ($barang->image && Storage::disk('public')->exists($barang->image)) {
            Storage::disk('public')->delete($barang->image);
        }

        // Hapus barang dari database (tidak menyentuh rak)
        $barang->delete();

        Cache::store('redis')->forget('wsp_barang_list_soh');

        return response()->json([
            'status' => true,
            'message' => 'Barang berhasil dihapus.',
        ]);
    }

    // Import Barang
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            $dataToProcess = [];
            $errors = [];
            $midNormList = []; // untuk cek duplikat dalam file

            for ($row = 2; $row <= $highestRow; $row++) {
                $rawMid     = trim((string) $worksheet->getCell('A' . $row)->getValue() ?? '');
                $namaBarang = trim((string) $worksheet->getCell('B' . $row)->getValue() ?? '');
                $uom        = trim((string) $worksheet->getCell('C' . $row)->getValue() ?? '');
                $s_loc      = trim((string) $worksheet->getCell('D' . $row)->getValue() ?? '');
                $plant      = trim((string) $worksheet->getCell('E' . $row)->getValue() ?? '');
                $qtyPallet  = trim((string) $worksheet->getCell('F' . $row)->getValue() ?? '');

                $qtyPalletVal = 1.0;
                if ($qtyPallet !== '' && is_numeric($qtyPallet) && floatval($qtyPallet) > 0) {
                    $qtyPalletVal = floatval($qtyPallet);
                }

                // Normalisasi MID seperti di WFG
                $midDigits = preg_replace('/\D+/', '', $rawMid);
                $midKey    = ltrim($midDigits, '0');
                if ($midKey === '') $midKey = '0';

                $midForSave = (string)((int) $midKey); // format final konsisten

                $rowErrors = [];

                // Validasi
                if ($midDigits === '' || strlen($midDigits) < 1 || strlen($midDigits) > 8) {
                    $rowErrors[] = 'MID Barang harus 1–8 digit angka.';
                }
                if (empty($namaBarang)) {
                    $rowErrors[] = 'Nama Barang wajib diisi.';
                }
                if (empty($uom)) {
                    $rowErrors[] = 'UOM wajib diisi.';
                }

                // Cek duplikat dalam file (pakai midKey yang sudah dinormalisasi)
                if (in_array($midKey, $midNormList, true)) {
                    $rowErrors[] = 'MID Barang duplikat dalam file import ini.';
                } else {
                    $midNormList[] = $midKey;
                }

                if (!empty($rowErrors)) {
                    $errors[] = [
                        'baris' => $row,
                        'data'  => compact('rawMid', 'namaBarang', 'uom', 's_loc'),
                        'error' => implode(', ', $rowErrors),
                    ];
                    continue;
                }

                $dataToProcess[] = [
                    'mid_barang'   => $midForSave,
                    'nama_barang'  => strtoupper($namaBarang),
                    'uom'          => strtoupper($uom),
                    'qty_pallet'   => $qtyPalletVal,
                    's_loc'        => strtoupper($s_loc),
                    'plant'        => strtoupper($plant),
                    'created_by'   => Auth::id() ?? 1,
                    'updated_at'   => now(),
                    'created_at'   => now(),
                ];
            }

            if (empty($dataToProcess)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tidak ada data valid untuk diimpor.',
                    'errors'  => $errors,
                ], 400);
            }

            DB::beginTransaction();
            try {
                foreach ($dataToProcess as $item) {
                    $midInt = (int) $item['mid_barang'];

                    // Cari berdasarkan nilai numeric (agar "00123" == "123")
                    $existing = BarangModel::whereRaw('CAST(mid_barang AS SIGNED) = ?', [$midInt])->first();

                    if ($existing) {
                        // Update data yang sudah ada
                        $existing->update([
                            'nama_barang' => $item['nama_barang'],
                            'uom'         => $item['uom'],
                            'qty_pallet'  => $item['qty_pallet'],
                            's_loc'       => $item['s_loc'],
                            'plant'       => $item['plant'],
                            'updated_at'  => now(),
                        ]);
                    } else {
                        // Insert baru
                        BarangModel::create($item);
                    }
                }

                DB::commit();

                Cache::store('redis')->forget('wsp_barang_list_soh');

                return response()->json([
                    'status'  => true,
                    'message' => count($dataToProcess) . ' data berhasil diimpor/update, ' . count($errors) . ' baris gagal.',
                    'errors'  => $errors,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data ke database: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'MID Barang');
        $sheet->setCellValue('B1', 'Nama Barang');
        $sheet->setCellValue('C1', 'Uom');
        $sheet->setCellValue('D1', 'SLoc');
        $sheet->setCellValue('E1', 'Plant');
        $sheet->setCellValue('F1', 'Qty Pallet');

        // Add example data
        $sheet->setCellValue('A2', 12345678);
        $sheet->setCellValue('B2', 'Contoh Barang 1');
        $sheet->setCellValue('C2', 'Pcs');
        $sheet->setCellValue('D2', 'G001');
        $sheet->setCellValue('E2', '1006');
        $sheet->setCellValue('F2', 100);

        $sheet->setCellValue('A3', 87654321);
        $sheet->setCellValue('B3', 'Contoh Barang 2');
        $sheet->setCellValue('C3', 'Pcs');
        $sheet->setCellValue('D3', 'G001');
        $sheet->setCellValue('E3', '1006');
        $sheet->setCellValue('F3', 50);

        // Auto width columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Style header
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC');

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_barang_wsp_' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }

    public function export()
    {
        // Increase limits for potential large data
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $data = BarangModel::orderBy('mid_barang', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers (sesuai dengan format import)
        $headers = ['MID Barang', 'Nama Barang', 'Uom', 'SLoc', 'Plant', 'Qty Pallet'];
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '1', $header);
            $sheet->getStyle($columnIndex . '1')->getFont()->setBold(true);
            $sheet->getStyle($columnIndex . '1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFCCCCCC');
            $columnIndex++;
        }

        // Fill data
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->mid_barang);
            $sheet->setCellValue('B' . $row, $item->nama_barang);
            $sheet->setCellValue('C' . $row, $item->uom);
            $sheet->setCellValue('D' . $row, $item->s_loc);
            $sheet->setCellValue('E' . $row, $item->plant);
            $sheet->setCellValue('F' . $row, $item->qty_pallet ?? 1);
            $row++;
        }

        // Auto width columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'export_master_barang_wsp_' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}
