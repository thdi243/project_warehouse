<?php

namespace App\Http\Controllers\Wrm;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Wrm\MasterBarangModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Requests\Wrm\MasterBarangRequest;
use App\Models\Wrm\MasterLocationModel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterBarangController extends Controller
{
    public function index()
    {
        $location = MasterLocationModel::select(
            DB::raw('MIN(id) as id'),
            's_loc',
            'plant'
        )
            ->groupBy('s_loc', 'plant')
            ->get();

        return view('master.wrm.master_barang', compact('location'));
    }

    public function store(MasterBarangRequest $request)
    {
        $barang = MasterBarangModel::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Master barang berhasil disimpan',
            'data'    => $barang,
        ]);
    }

    public function getData(Request $request)
    {
        $status = $request->input('status', 'active');
        $query = MasterBarangModel::query()->with([
            'location:id,s_loc,plant',
            'createdBy:id,username,nama_lengkap',
            'updatedBy:id,username,nama_lengkap'
        ]);

        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        $barang = $query->get();

        return response()->json([
            'status' => true,
            'data' => $barang
        ]);
    }

    public function update(MasterBarangRequest $request, $id)
    {
        $barang = MasterBarangModel::withTrashed()->findOrFail($id);

        $barang->update([
            ...$request->validated(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Master barang berhasil diperbarui',
            'data'    => $barang,
        ]);
    }

    public function destroy($id)
    {
        $barang = MasterBarangModel::findOrFail($id);

        $barang->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Master barang berhasil dihapus',
        ]);
    }

    public function restore($id)
    {
        $barang = MasterBarangModel::withTrashed()->findOrFail($id);
        $barang->restore();

        return response()->json([
            'status'  => true,
            'message' => 'Master barang berhasil direstore',
            'data'    => $barang,
        ]);
    }

    public function forceDelete($id)
    {
        $barang = MasterBarangModel::withTrashed()->findOrFail($id);
        $barang->forceDelete();

        return response()->json([
            'status'  => true,
            'message' => 'Master barang berhasil dihapus permanen',
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        unset($rows[0]);

        $errors = [];
        $payload = [];
        $midsInExcel = [];

        foreach ($rows as $index => $row) {

            $line = $index + 1;

            $mid = trim($row[0] ?? '');

            if ($mid === '') {
                $errors[] = "Baris {$line}: MID kosong";
                continue;
            }

            // MID duplikat di dalam file
            if (in_array($mid, $midsInExcel)) {
                $errors[] = "Baris {$line}: MID {$mid} duplikat di file";
                continue;
            }

            $midsInExcel[] = $mid;

            // MID sudah ada di database
            if (MasterBarangModel::where('mid', $mid)->exists()) {
                $errors[] = "Baris {$line}: MID {$mid} sudah terdaftar";
                continue;
            }

            $payload[] = [
                'mid' => $mid,
                'nama_barang' => $row[1] ?? '',
                'uom' => $row[2] ?? '',
                's_loc' => $row[3] ?? '',
                'plant' => $row[4] ?? '',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // ADA ERROR → BATAL TOTAL
        if (!empty($errors)) {
            return response()->json([
                'status' => false,
                'message' => 'Upload dibatalkan, perbaiki data terlebih dahulu',
                'errors' => $errors
            ], 422);
        }

        // AMAN → INSERT SEKALIGUS
        DB::transaction(function () use ($payload) {
            MasterBarangModel::insert($payload);
        });

        return response()->json([
            'status' => true,
            'message' => 'Upload berhasil (' . count($payload) . ' data masuk)',
        ]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'mid');
        $sheet->setCellValue('B1', 'nama barang');
        $sheet->setCellValue('C1', 'uom');
        $sheet->setCellValue('E1', 'Plant');
        $sheet->setCellValue('D1', 'S.Loc');
        $sheet->setCellValue('F1', 'Qty Kg/Pallet');

        // Style header (opsional tapi cakep)
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_master_barang_wrm.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
