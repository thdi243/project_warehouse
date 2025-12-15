<?php

namespace App\Http\Controllers\Wsp\stock_move;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Wsp\stock_move\WspOutgoingModel;

class WspOutgoingController extends Controller
{
    public function viewOutgoing()
    {
        return view('wsp.wsp_stock.stock_move.outgoing');
    }

    public function getDataOutgoing(Request $request)
    {
        try {
            $query = WspOutgoingModel::with([
                'user:id,nama_lengkap',
            ]);

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            } elseif ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            } else {
                $query->whereDate('created_at', Carbon::today());
            }

            $data = $query->orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data Outgoing berhasil diambil.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data Outgoing.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mid'          => 'required|numeric',
            'nama_barang'  => 'required|string',
            's_loc'        => 'nullable|string',
            'unit'         => 'nullable|string',
            'material_doc' => 'required|numeric',
            'posting_date' => 'required|date',
            'qty'          => 'required|numeric',
            'mvt'          => 'nullable|numeric',
            'vendor'       => 'nullable|string',
            'batch'        => 'nullable|string',
        ]);

        try {
            $barang = BarangModel::where('mid', $validated['mid'])->first();

            if (!$barang) {
                return response()->json([
                    'success' => false,
                    'message' => 'MID tidak ditemukan di master barang'
                ], 422);
            }

            $validated['user_id'] = Auth::id() ?? 1;

            $outgoing = WspOutgoingModel::create($validated);

            return response()->json([
                'message' => 'Outgoing berhasil ditambahkan',
                'data'    => $outgoing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan outgoing',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $data = WspOutgoingModel::with(['user:id,nama_lengkap'])->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'mid'          => 'required|numeric',
            'nama_barang'  => 'required|string',
            's_loc'        => 'nullable|string',
            'unit'         => 'nullable|string',
            'material_doc' => 'required|numeric',
            'posting_date' => 'required|date',
            'qty'          => 'required|numeric',
            'mvt'          => 'nullable|numeric',
            'vendor'       => 'nullable|string',
            'batch'        => 'nullable|numeric',
        ]);

        try {
            $outgoing = WspOutgoingModel::findOrFail($id);

            $outgoing->update($validated);

            return response()->json([
                'message' => 'Outgoing berhasil diperbarui',
                'data'    => $outgoing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengupdate outgoing',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:4096',
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
            $skipped  = [];
            $invalid_mid = [];

            foreach ($rows as $index => $row) {

                // Skip header
                if ($index == 1) continue;

                // Ambil data dari kolom Excel
                $mid          = $row['A'] ?? null;
                $nama_barang  = $row['B'] ?? null;
                $s_loc        = $row['C'] ?? null;
                $unit         = $row['D'] ?? null;
                $matDoc       = $row['E'] ?? null;
                $postDate     = $row['F'] ?? null;
                $qty          = $row['G'] ?? null;
                $mvt          = $row['H'] ?? null;
                $vendor       = $row['I'] ?? null;
                $batch        = $row['J'] ?? null;

                // Jika semua kolom kosong → skip
                if (
                    empty($s_loc) && empty($unit) && empty($qty) &&
                    empty($matDoc) && empty($postDate) && empty($mid) &&
                    empty($nama_barang)
                ) {
                    continue;
                }

                // Validasi kolom wajib
                if (
                    empty($mid) ||
                    empty($nama_barang) ||
                    empty($postDate) ||
                    empty($matDoc) ||
                    empty($qty)
                ) {
                    $skipped[] = "Baris {$index}: Tidak lengkap.";
                    continue;
                }

                $cekMid = BarangModel::where('mid_barang', $mid)->first();

                if (!$cekMid) {
                    $invalid_mid[] = "Baris {$index}: MID '{$mid}' tidak ada di master barang.";
                    continue;
                }

                // Insert data
                WspOutgoingModel::create([
                    'user_id'      => Auth::id() ?? null,
                    'mid'          => $mid,
                    'nama_barang'  => $nama_barang,
                    's_loc'        => $s_loc ?: null,
                    'unit'         => $unit ?: null,
                    'material_doc' => $matDoc ?: null,
                    'posting_date' => $this->excelDate($postDate),
                    'qty'          => $qty ?: null,
                    'mvt'          => $mvt ?: null,
                    'vendor'       => $vendor ?: null,
                    'batch'        => $batch ?: null,
                ]);

                $inserted++;
            }

            if (count($invalid_mid) > 0) {
                DB::rollBack();
                return response()->json([
                    "message" => "Upload ditolak. Terdapat MID yang belum terdaftar di master barang.",
                    "error_mid" => $invalid_mid
                ], 422);
            }

            DB::commit();

            return response()->json([
                "message" => "Upload berhasil. {$inserted} data ditambahkan.",
                "skipped" => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengimpor file.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function excelDate($value)
    {
        if (empty($value)) return null;

        // Jika Excel format numeric → convert
        if (is_numeric($value)) {
            return date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
        }

        // Jika sudah berupa tanggal string → langsung return
        return date('Y-m-d', strtotime($value));
    }

    public function downloadTemplate()
    {
        $filePath = public_path('assets/templates/excel/Template_Outgoing_Wsp.xlsx');

        // cek kalau file memang ada
        if (!file_exists($filePath)) {
            abort(404, 'Template tidak ditemukan.');
        }

        $fileName = 'Template_Outgoing_Wsp_' . date('Y-m-d') . '.xlsx';

        return response()->download($filePath, $fileName);
    }

    public function destroy($id)
    {
        $data = WspOutgoingModel::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
