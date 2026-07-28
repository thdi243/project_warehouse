<?php

namespace App\Http\Controllers\Wsp;

use App\Models\Wsp\RakModel;
use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\TransaksiModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Wsp\StockBarangRakModel;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class WspRakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('manajemen_rak.list_barang');
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
            'plant'    => 'required|string|max:50',
            'sLoc'     => 'required|string|max:50',
            'areaRak'  => 'required|string|max:50',
            'namaRak'  => 'required|string|max:50',
            'kolomRak' => 'nullable',
            'levelRak' => 'nullable',
            'boxRak'   => 'nullable|string',
        ]);

        try {
            $data = RakModel::create([
                'created_by' => Auth::id() ?? 1,
                'plant'      => strtoupper(trim($request->plant)),
                's_loc'      => strtoupper(trim($request->sLoc)),
                'area_rak'   => strtoupper(trim($request->areaRak)),
                'nama_rak'   => strtoupper(trim($request->namaRak)),
                'kolom_rak'  => $request->kolomRak ?? null,
                'level_rak'  => $request->levelRak ?? null,
                'box_rak'    => $request->boxRak ?? null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data Rak berhasil ditambahkan!',
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data Rak dengan kombinasi tersebut sudah ada.',
                ], 422);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menyimpan data rak.',
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rak = RakModel::find($id);

        if (!$rak) {
            return response()->json([
                'status'  => false,
                'message' => 'Data Rak tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Detail Data Rak',
            'data'    => $rak
        ], 200);
    }

    public function getDataRak()
    {
        $dataRak = RakModel::with('user:id,username')->get();

        return response()->json($dataRak);
    }

    public function getFilters()
    {
        $area = RakModel::select('area_rak')
            ->distinct()
            ->pluck('area_rak');

        $nama = RakModel::select('nama_rak')
            ->distinct()
            ->pluck('nama_rak');

        return response()->json([
            'area' => $area,
            'nama' => $nama
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'plantEdit'     => 'required|string|max:50',
            'sLocEdit'      => 'required|string|max:50',
            'areaRakEdit'   => 'required|string|max:50',
            'namaRakEdit'   => 'required|string|max:50',
            'kolomRakEdit'  => 'nullable',
            'levelRakEdit'  => 'nullable',
            'boxRakEdit'    => 'nullable|string',
        ]);

        try {
            // Cari data rak berdasarkan ID
            $rak = RakModel::findOrFail($id);

            // Update datanya
            $rak->update([
                'plant'     => strtoupper(trim($request->plantEdit)),
                's_loc'     => strtoupper(trim($request->sLocEdit)),
                'area_rak'  => strtoupper(trim($request->areaRakEdit)),
                'nama_rak'  => strtoupper(trim($request->namaRakEdit)),
                'kolom_rak' => $request->kolomRakEdit ?? null,
                'level_rak' => $request->levelRakEdit ?? null,
                'box_rak'   => $request->boxRakEdit ?? null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data Rak berhasil diperbarui!',
                'data'    => $rak,
            ], 200);
        } catch (\Exception $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data Rak dengan kombinasi tersebut sudah ada.',
                ], 422);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memperbarui data rak.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rak = RakModel::findOrFail($id);
        $rak->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Data Rak berhasil dihapus!',
        ], 200);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (!is_array($rows) || count($rows) <= 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'File kosong atau tidak memiliki data.',
                ], 422);
            }

            DB::beginTransaction();

            $inserted = 0;
            $skipped = [];

            // Lewati header (baris pertama)
            foreach ($rows as $index => $row) {
                if ($index === 1) continue;

                $plant     = isset($row['A']) ? trim((string)$row['A']) : '';
                $s_loc     = isset($row['B']) ? trim((string)$row['B']) : '';
                $area_rak  = isset($row['C']) ? trim((string)$row['C']) : '';
                $nama_rak  = isset($row['D']) ? trim((string)$row['D']) : '';
                $kolom_rak = isset($row['E']) ? trim((string)$row['E']) : '';
                $level_rak = isset($row['F']) ? trim((string)$row['F']) : '';
                $box_rak   = isset($row['G']) ? trim((string)$row['G']) : '';

                if ($plant === '' && $s_loc === '' && $area_rak === '' && $nama_rak === '') {
                    continue;
                }

                if ($plant === '' || $s_loc === '' || $area_rak === '' || $nama_rak === '' || $kolom_rak === '' || $level_rak === '') {
                    $skipped[] = "Baris " . $index . ": Kolom wajib ada tidak lengkap.";
                    continue;
                }

                $plant     = strtoupper($plant);
                $s_loc     = strtoupper($s_loc);
                $area_rak  = strtoupper($area_rak);
                $nama_rak  = strtoupper($nama_rak);
                $kolom_rak = intval($kolom_rak);
                $level_rak = intval($level_rak);
                $box_rak   = $box_rak !== '' ? $box_rak : '000';

                // Cek duplikat
                $exists = RakModel::where('plant', $plant)
                    ->where('s_loc', $s_loc)
                    ->where('area_rak', $area_rak)
                    ->where('nama_rak', $nama_rak)
                    ->where('kolom_rak', $kolom_rak)
                    ->where('level_rak', $level_rak)
                    ->where('box_rak', $box_rak)
                    ->exists();

                if ($exists) {
                    $skipped[] = "Baris " . $index . ": Data rak {$plant}-{$s_loc}-{$area_rak}-{$nama_rak}-({$kolom_rak}.{$level_rak}.{$box_rak}) sudah terdaftar.";
                    continue;
                }

                RakModel::create([
                    'plant'      => $plant,
                    's_loc'      => $s_loc,
                    'area_rak'   => $area_rak,
                    'nama_rak'   => $nama_rak,
                    'kolom_rak'  => $kolom_rak ?? null,
                    'level_rak'  => $level_rak ?? null,
                    'box_rak'    => $box_rak ?? null,
                    'created_by' => Auth::id() ?? 1,
                ]);

                $inserted++;
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Upload berhasil. {$inserted} data Rak ditambahkan.",
                'skipped' => $skipped,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat mengimpor file.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Plant');
        $sheet->setCellValue('B1', 'S Loc');
        $sheet->setCellValue('C1', 'Area Rak');
        $sheet->setCellValue('D1', 'Nama Rak');
        $sheet->setCellValue('E1', 'Kolom');
        $sheet->setCellValue('F1', 'Level');
        $sheet->setCellValue('G1', 'Box');

        // Example row
        $sheet->setCellValue('A2', '1006');
        $sheet->setCellValue('B2', 'G001');
        $sheet->setCellValue('C2', 'FL1');
        $sheet->setCellValue('D2', 'A');
        $sheet->setCellValue('E2', '1');
        $sheet->setCellValue('F2', '1');
        $sheet->setCellValue('G2', '000');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Master_Rak_Wsp_' . date('Y-m-d') . '.xlsx';

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
