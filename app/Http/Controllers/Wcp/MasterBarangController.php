<?php

namespace App\Http\Controllers\Wcp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Wcp\WcpMasterBarangModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Requests\Wcp\MasterBarangRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterBarangController extends Controller
{
    public function index()
    {
        return view('master.wcp.master_barang');
    }

    public function store(MasterBarangRequest $request)
    {
        $barang = WcpMasterBarangModel::create([
            'mid' => $request->mid,
            'nama_barang' => $request->nama_barang,
            'uom' => $request->uom,
            'qty_pallet' => $request->qty_pallet,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Master barang WCP berhasil disimpan',
            'data'    => $barang,
        ]);
    }

    public function getData(Request $request)
    {
        $query = WcpMasterBarangModel::query();
        $query->with(['createdBy:id,username,nama_lengkap', 'updatedBy:id,username,nama_lengkap']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mid', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        $barang = $query->paginate(25);

        return response()->json([
            'status' => true,
            'data' => $barang
        ]);
    }

    public function update(MasterBarangRequest $request, $id)
    {
        $barang = WcpMasterBarangModel::findOrFail($id);

        $barang->update([
            'mid' => $request->mid,
            'nama_barang' => $request->nama_barang,
            'uom' => $request->uom,
            'qty_pallet' => $request->qty_pallet,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Master barang WCP berhasil diperbarui',
            'data'    => $barang,
        ]);
    }

    public function destroy($id)
    {
        $barang = WcpMasterBarangModel::findOrFail($id);
        $barang->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Master barang WCP berhasil dihapus',
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
            $namaBarang = trim($row[1] ?? '');
            $uom = trim($row[2] ?? '');
            $qtyPallet = trim($row[3] ?? '');

            if ($mid === '') {
                if ($namaBarang === '' && $uom === '' && $qtyPallet === '') {
                    continue;
                }
                $errors[] = "Baris {$line}: MID kosong";
                continue;
            }

            if (in_array($mid, $midsInExcel)) {
                $errors[] = "Baris {$line}: MID {$mid} duplikat di file";
                continue;
            }

            $midsInExcel[] = $mid;

            if (WcpMasterBarangModel::where('mid', $mid)->exists()) {
                $errors[] = "Baris {$line}: MID {$mid} sudah terdaftar di database";
                continue;
            }

            if ($qtyPallet === '' || !is_numeric($qtyPallet) || floatval($qtyPallet) <= 0) {
                $errors[] = "Baris {$line}: Qty Pallet harus berupa angka positif";
                continue;
            }

            $payload[] = [
                'mid' => $mid,
                'nama_barang' => $namaBarang,
                'uom' => $uom,
                'qty_pallet' => floatval($qtyPallet),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($errors)) {
            return response()->json([
                'status' => false,
                'message' => 'Upload dibatalkan, perbaiki data terlebih dahulu',
                'errors' => $errors
            ], 422);
        }

        if (empty($payload)) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada data yang diupload',
            ], 422);
        }

        DB::transaction(function () use ($payload) {
            WcpMasterBarangModel::insert($payload);
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
        $sheet->setCellValue('A1', 'MID');
        $sheet->setCellValue('B1', 'Nama Barang');
        $sheet->setCellValue('C1', 'UOM');
        $sheet->setCellValue('D1', 'Qty Full Pallet');

        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_master_barang_wcp.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
