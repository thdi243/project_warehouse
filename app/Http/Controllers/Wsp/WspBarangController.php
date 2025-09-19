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
            'mid_barang'  => 'required|digits:8|integer',
            'nama_barang' => 'required|string|max:50',
            'image'       => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

            // data rak
            'kode_rak'    => 'required|string|max:50',
            'nama_rak'    => 'required|string|max:50',
            'kolom_rak'   => 'required|integer',
            'level_rak'   => 'required|integer',
            'box_rak'     => 'nullable|max:50',
        ]);

        // Cek duplikat MID barang
        if (BarangModel::where('mid_barang', $request->mid_barang)->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'MID Barang sudah terdaftar. Gunakan MID lain.',
            ], 400);
        }

        $rak = RakModel::firstOrCreate(
            [
                'user_id'   => Auth::id() ?? 1,
                'kode_rak'  => trim($request->kode_rak),
                'nama_rak'  => trim($request->nama_rak),
                'kolom_rak' => (int) $request->kolom_rak,
                'level_rak' => (int) $request->level_rak,
                'box_rak'   => $request->box_rak !== null ? trim($request->box_rak) : '000',
            ]
        );

        $rakMessage = $rak->wasRecentlyCreated
            ? 'Rak baru berhasil dibuat dan barang ditempatkan di rak tersebut.'
            : 'Rak sudah ada, barang ditempatkan pada rak tersebut.';

        // Simpan barang
        $barang = BarangModel::create([
            'rak_id' => $rak->id,
            'user_id' => Auth::id() ?? 1,
            'mid_barang' => $request->mid_barang,
            'nama_barang' => $request->nama_barang,
            'image' => $request->hasFile('image')
                ? $request->file('image')->store('images/wsp', 'public')
                : null,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Barang baru berhasil diregistrasi.<br>' . $rakMessage,
            'data'    => [
                'barang' => $barang,
                'rak'    => $rak,
            ]
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Ambil barang dan rak berdasarkan ID barang atau rak
        $barang = BarangModel::with('rak', 'user')->find($id); // asumsi $id = id barang

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
            'image' => $barang->image,
            'rak_id' => $rak->id ?? null,
            'kode_rak' => $rak->kode_rak ?? null,
            'nama_rak' => $rak->nama_rak ?? null,
            'kolom_rak' => $rak->kolom_rak ?? null,
            'level_rak' => $rak->level_rak ?? null,
            'box_rak' => $rak->box_rak ?? null,
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
            'rak:id,kode_rak,nama_rak,kolom_rak,level_rak,box_rak',
            'user:id,username'
        ])
            ->get()
            ->map(function ($barang) {
                return [
                    'id'          => $barang->id,
                    'mid_barang'  => $barang->mid_barang,
                    'nama_barang' => $barang->nama_barang,
                    'lokasi'      => $barang->rak
                        ? $barang->rak->kode_rak . '.' . $barang->rak->nama_rak . '.' . $barang->rak->kolom_rak . '.' . $barang->rak->level_rak . '.' . ($barang->rak->box_rak ?? '000')
                        : null,
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

    public function getKodeRak()
    {
        $kodeRak = RakModel::select('kode_rak')
            ->distinct()
            ->pluck('kode_rak');

        if ($kodeRak->isEmpty()) {
            return response()->json([
                'status' => 'empty',
                'message' => 'Data rak kosong',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $kodeRak
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
            'midBarangEdit'  => 'required|digits:8|integer',
            'namaBarangEdit' => 'required|string|max:50',
            'imageEdit'       => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

            // data rak
            'kodeRakEdit'    => 'required|string|max:50',
            'namaRakEdit'    => 'required|string|max:50',
            'kolomRakEdit'   => 'required|integer',
            'levelRakEdit'   => 'required|integer',
            'boxRakEdit'     => 'nullable|max:50',
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

        if ($request->hasFile('imageEdit')) {
            $barang->image = $request->file('image')->store('images/wsp', 'public');
        }

        $barang->save();

        // Update atau buat rak baru
        $rak = RakModel::firstOrCreate([
            'user_id'   => Auth::id() ?? 1,
            'kode_rak'  => trim($request->kodeRakEdit),
            'nama_rak'  => trim($request->namaRakEdit),
            'kolom_rak' => (int) $request->kolomRakEdit,
            'level_rak' => (int) $request->levelRakEdit,
            'box_rak'   => $request->boxRakEdit !== null ? trim($request->boxRakEdit) : '000',
        ]);

        // Hubungkan barang dengan rak
        $barang->rak_id = $rak->id;
        $barang->save();

        return response()->json([
            'status'  => true,
            'message' => 'Data barang berhasil diupdate.',
            'data'    => [
                'barang' => $barang,
                'rak'    => $rak,
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


    // import dan download template
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

            // Skip header row
            foreach ($rows as $index => $row) {
                if ($index === 0) continue;

                // Skip empty rows
                if (empty(array_filter($row))) continue;

                $midBarang = isset($row[0]) ? (int) $row[0] : null;
                $namaBarang = isset($row[1]) ? trim($row[1]) : '';
                $kodeRak = isset($row[2]) ? trim($row[2]) : '';
                $namaRak = isset($row[3]) ? trim($row[3]) : '';
                $kolomRak = isset($row[4]) ? (int) $row[4] : null;
                $levelRak = isset($row[5]) ? (int) $row[5] : null;
                $boxRak = isset($row[6]) ? str_pad(trim($row[6]), 3, '0', STR_PAD_LEFT) : '000';

                // Validasi
                if (!$midBarang || !$namaBarang || !$kodeRak || !$namaRak || !$kolomRak || !$levelRak) {
                    $errors[] = "Baris " . ($index + 1) . ": Data tidak lengkap";
                    $errorCount++;
                    continue;
                }

                if (strlen((string)$midBarang) !== 8) {
                    $errors[] = "Baris " . ($index + 1) . ": MID Barang harus 8 digit";
                    $errorCount++;
                    continue;
                }

                if (BarangModel::where('mid_barang', $midBarang)->exists()) {
                    $errors[] = "Baris " . ($index + 1) . ": MID $midBarang sudah terdaftar";
                    $errorCount++;
                    continue;
                }

                try {
                    $rak = RakModel::firstOrCreate([
                        'user_id' => Auth::id() ?? 1,
                        'kode_rak' => $kodeRak,
                        'nama_rak' => $namaRak,
                        'kolom_rak' => $kolomRak,
                        'level_rak' => $levelRak,
                        'box_rak' => $boxRak,
                    ]);

                    BarangModel::create([
                        'rak_id' => $rak->id,
                        'user_id' => Auth::id() ?? 1,
                        'mid_barang' => $midBarang,
                        'nama_barang' => $namaBarang,
                        'image' => null,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Import berhasil! Sukses: $successCount, Gagal: $errorCount",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
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
        $sheet->setCellValue('C1', 'Kode Rak');
        $sheet->setCellValue('D1', 'Nama Rak');
        $sheet->setCellValue('E1', 'Kolom Rak');
        $sheet->setCellValue('F1', 'Level Rak');
        $sheet->setCellValue('G1', 'Box Rak');

        // Add example data
        $sheet->setCellValue('A2', 12345678);
        $sheet->setCellValue('B2', 'Contoh Barang 1');
        $sheet->setCellValue('C2', 'FL1');
        $sheet->setCellValue('D2', 'A');
        $sheet->setCellValue('E2', 1);
        $sheet->setCellValue('F2', 1);
        $sheet->setCellValue('G2', '001');

        $sheet->setCellValue('A3', 87654321);
        $sheet->setCellValue('B3', 'Contoh Barang 2');
        $sheet->setCellValue('C3', 'FL2');
        $sheet->setCellValue('D3', 'B');
        $sheet->setCellValue('E3', 2);
        $sheet->setCellValue('F3', 3);
        $sheet->setCellValue('G3', '002');

        // Auto width columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Style header
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC');

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_import_barang_' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}
