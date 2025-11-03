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

class WspBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mid_barang'  => 'required|digits_between:1,8|integer',
            'nama_barang' => 'required|string|max:255',
            'uom'         => 'required|string|max:50',
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
                'image'       => $imagePath,
            ]);

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
                    'username'    => $barang->user->username ?? null,
                    'image'       => $barang->image ? asset('storage/' . $barang->image) : null,
                ];
            })
            ->toArray();


        return response()->json([
            'status'  => true,
            'message' => 'Data barang beserta lokasi berhasil ditemukan.',
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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

        if ($request->hasFile('imageEdit')) {
            $barang->image = $request->file('image')->store('images/wsp', 'public');
        }

        $barang->save();

        // Hubungkan barang dengan rak
        $barang->save();

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
            DB::beginTransaction();

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // skip header
                if (empty(array_filter($row))) continue; // skip empty row

                $midBarang  = isset($row[0]) ? trim($row[0]) : null;
                $namaBarang = isset($row[1]) ? trim($row[1]) : null;
                $uom        = isset($row[2]) ? trim($row[2]) : null;

                // --- Validasi dasar ---
                if (!$midBarang || !$namaBarang || !$uom) {
                    $errors[] = "Baris " . ($index + 1) . ": Data tidak lengkap";
                    $errorCount++;
                    continue;
                }

                if (!is_numeric($midBarang) || strlen($midBarang) != 8) {
                    $errors[] = "Baris " . ($index + 1) . ": MID Barang harus 8 digit angka";
                    $errorCount++;
                    continue;
                }

                if (BarangModel::where('mid_barang', $midBarang)->exists()) {
                    $errors[] = "Baris " . ($index + 1) . ": MID $midBarang sudah terdaftar";
                    $errorCount++;
                    continue;
                }

                try {
                    BarangModel::create([
                        'created_by'  => Auth::id() ?? 1,
                        'mid_barang'  => $midBarang,
                        'nama_barang' => $namaBarang,
                        'uom'         => $uom,
                        'image'       => null,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => $errorCount === 0,
                'message' => "Import selesai! Sukses: $successCount, Gagal: $errorCount",
                'data' => [
                    'success_count' => $successCount,
                    'error_count'   => $errorCount,
                    'errors'        => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
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

        // Add example data
        $sheet->setCellValue('A2', 12345678);
        $sheet->setCellValue('B2', 'Contoh Barang 1');
        $sheet->setCellValue('C2', 'Pcs');

        $sheet->setCellValue('A3', 87654321);
        $sheet->setCellValue('B3', 'Contoh Barang 2');
        $sheet->setCellValue('C3', 'Pcs');

        // Auto width columns
        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Style header
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC');

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_barang_wsp_' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}
