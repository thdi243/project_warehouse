<?php

namespace App\Http\Controllers\Wsp\stock;

use App\Models\Wsp\RakModel;
use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Wsp\stock_manage\StockLocationModel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockLocationController extends Controller
{
    public function getDataStockLocation(Request $request)
    {
        try {
            $query = StockLocationModel::with([
                'barang:id,mid_barang,nama_barang,uom',
                'rak:id,area_rak,nama_rak,kolom_rak,level_rak,box_rak',
            ]);

            // Jika ingin filter berdasarkan status
            if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
                $query->where('status', $request->status);
            }

            // Ambil semua data
            $data = $query->orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data Stock Location berhasil diambil.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Stock Location.',
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
        $validated = $request->validate([
            'mid_barang' => 'required|digits_between:1,8|integer',
            'area_rak' => 'required|string',
            'nama_rak' => 'required|string',
            'kolom_rak' => 'required|string',
            'level_rak' => 'required|string',
            'box_rak' => 'nullable|string',
        ]);

        // Cari barang berdasarkan MID
        $barang = BarangModel::where('mid_barang', $validated['mid_barang'])->first();
        if (!$barang) {
            return response()->json([
                'message' => 'Barang dengan MID tersebut tidak ditemukan.'
            ], 404);
        }

        // Cari rak berdasarkan kombinasi lengkap
        $rak = RakModel::where('area_rak', $validated['area_rak'])
            ->where('nama_rak', $validated['nama_rak'])
            ->where('kolom_rak', $validated['kolom_rak'])
            ->where('level_rak', $validated['level_rak'])
            ->where('box_rak', $validated['box_rak'])
            ->first();

        if (!$rak) {
            return response()->json([
                'message' => 'Rak dengan kombinasi tersebut tidak ditemukan.'
            ], 404);
        }

        // Cek apakah kombinasi barang + rak sudah ada
        $exists = StockLocationModel::where('barang_id', $barang->id)
            ->where('rak_id', $rak->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Data dengan barang dan rak ini sudah ada.'
            ], 409);
        }

        // Simpan ke tabel stock_location
        $data = StockLocationModel::create([
            'barang_id' => $barang->id,
            'rak_id' => $rak->id,
            'status' => 'active',
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'message' => 'Data Stock Location berhasil disimpan.',
            'data' => $data
        ], 201);
    }

    public function show($id)
    {
        $data = StockLocationModel::with(['barang', 'rak'])->find($id);

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => [
                'id' => $data->id,
                'text' => $data->barang->nama_barang,
                'mid_barang' => $data->barang->mid_barang,
                'nama_barang' => $data->barang->nama_barang,
                'area_rak' => $data->rak->area_rak,
                'nama_rak' => $data->rak->nama_rak,
                'kolom_rak' => $data->rak->kolom_rak,
                'level_rak' => $data->rak->level_rak,
                'box_rak' => $data->rak->box_rak,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'mid_barang' => 'required|string',
            'area_rak' => 'required|string',
            'nama_rak' => 'required|string',
            'kolom_rak' => 'required|string',
            'level_rak' => 'required|string',
            'box_rak' => 'nullable|string',
        ]);

        $data = StockLocationModel::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $barang = BarangModel::where('mid_barang', $request->mid_barang)->first();
        if (!$barang) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }

        $rak = RakModel::where('area_rak', $request->area_rak)
            ->where('nama_rak', $request->nama_rak)
            ->where('kolom_rak', $request->kolom_rak)
            ->where('level_rak', $request->level_rak)
            ->where('box_rak', $request->box_rak)
            ->first();

        if (!$rak) {
            return response()->json(['message' => 'Kombinasi rak tidak ditemukan di master rak'], 404);
        }

        $data->update([
            'barang_id' => $barang->id,
            'rak_id' => $rak->id,
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json(['message' => 'Data berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $data = StockLocationModel::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $data->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (!is_array($rows) || count($rows) <= 1) {
                return response()->json([
                    'message' => 'File kosong atau tidak memiliki data.',
                ], 422);
            }

            DB::beginTransaction();

            $inserted = 0;
            $skipped = [];

            // Lewati header (baris pertama)
            foreach ($rows as $index => $row) {

                if ($index == 1) continue;

                $mid_barang = isset($row['A']) ? trim((string)$row['A']) : null;
                $area_rak   = isset($row['B']) ? trim((string)$row['B']) : null;
                $nama_rak   = isset($row['C']) ? trim((string)$row['C']) : null;
                $kolom_rak  = isset($row['D']) ? trim((string)$row['D']) : null;
                $level_rak  = isset($row['E']) ? trim((string)$row['E']) : null;
                $box_rak    = isset($row['F']) ? trim((string)$row['F']) : '000';

                if ($mid_barang === '' && $area_rak === '' && $nama_rak === '') {
                    continue;
                }

                if ($mid_barang === '' || $area_rak === '' || $nama_rak === '' || $kolom_rak === '' || $level_rak === '') {
                    $skipped[] = "Baris " . $index . ": Kolom wajib ada tidak lengkap.";
                    continue;
                }

                $barang = BarangModel::where('mid_barang', $mid_barang)->first();
                if (!$barang) {
                    $skipped[] = "Baris " . ($index + 2) . ": MID Barang {$mid_barang} tidak ditemukan.";
                    continue;
                }

                $area_rak  = strtoupper($area_rak);
                $nama_rak  = strtoupper($nama_rak);
                $kolom_rak = $kolom_rak !== '' ? $kolom_rak : 1;
                $level_rak = $level_rak !== '' ? $level_rak : 1;
                $box_rak   = $box_rak !== '' ? $box_rak : '000';

                // Cari atau buat rak
                $rak = RakModel::firstOrCreate([
                    'area_rak' => $area_rak,
                    'nama_rak' => $nama_rak,
                    'kolom_rak' => $kolom_rak,
                    'level_rak' => $level_rak,
                    'box_rak' => $box_rak,
                ], [
                    'created_by' => Auth::id() ?? 1,
                ]);

                $exists = StockLocationModel::where('barang_id', $barang->id)
                    ->where('rak_id', $rak->id)
                    ->exists();

                if ($exists) {
                    $skipped[] = "Baris " . ($index + 2) . ": Barang sudah ada di rak.";
                    continue;
                }

                // Simpan data baru
                StockLocationModel::create([
                    'barang_id' => $barang->id,
                    'rak_id' => $rak->id,
                    'status' => 'active',
                    'created_by' => Auth::id() ?? 1,
                ]);

                $inserted++;
            }

            DB::commit();

            return response()->json([
                'message' => "Upload berhasil. {$inserted} data ditambahkan.",
                'skipped' => $skipped,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengimpor file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // download temlate
    public function downloadTemplate()
    {
        $filePath = public_path('assets/templates/excel/Template_Stock_Location_Wsp.xlsx');

        // cek kalau file memang ada
        if (!file_exists($filePath)) {
            abort(404, 'Template tidak ditemukan.');
        }

        $fileName = 'Template_Stock_Location_Wsp_' . date('Y-m-d') . '.xlsx';

        return response()->download($filePath, $fileName);
    }
}
