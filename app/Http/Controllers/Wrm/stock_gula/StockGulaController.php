<?php

namespace App\Http\Controllers\Wrm\stock_gula;

use Illuminate\Http\Request;
use App\Models\Wrm\StockGulaModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Wrm\MasterBarangModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Requests\Wrm\StockGulaRequest;

class StockGulaController extends Controller
{
    public function index()
    {
        $barang = MasterBarangModel::select('id', 'mid', 'nama_barang')->get();

        return view('wrm.stock_gula.index', compact('barang'));
    }

    public function getBarang(Request $request)
    {
        $q = $request->q;

        $query = MasterBarangModel::select('id', 'mid', 'nama_barang');

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('mid', 'like', "%{$q}%")
                    ->orWhere('nama_barang', 'like', "%{$q}%");
            });
        } else {
            $query->latest()->limit(5);
        }

        $barang = $query->limit(20)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'mid' => $item->mid,
                'nama_barang' => $item->nama_barang,
                'text' => "{$item->mid} - {$item->nama_barang}"
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $barang
        ]);
    }


    public function store(StockGulaRequest $request)
    {
        $stock = StockGulaModel::create([
            ...$request->validated(),
            'tanggal' => now()->format('Y-m-d'),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Stock gula berhasil disimpan',
            'data'    => $stock,
        ]);
    }

    public function getData()
    {
        $barang = StockGulaModel::with('barang:id,mid,nama_barang,uom,s_loc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Data stock gula berhasil diambil',
            'data' => $barang
        ]);
    }

    public function update(StockGulaRequest $request, $id)
    {
        $stock = StockGulaModel::findOrFail($id);

        $stock->update([
            ...$request->validated(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Stock gula berhasil diperbarui',
            'data'    => $stock,
        ]);
    }

    public function destroy($id)
    {
        $barang = StockGulaModel::findOrFail($id);

        $barang->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Data stock gula berhasil dihapus',
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048'
        ]);

        DB::beginTransaction();

        try {
            $sheet = IOFactory::load($request->file('file'))->getActiveSheet();
            $rows = $sheet->toArray();

            unset($rows[0]); // hapus header

            $errors = [];
            $mappedRows = [];

            foreach ($rows as $i => $row) {

                $line = $i + 2;
                $mid = trim($row[0] ?? '');

                if ($mid === '') {
                    $errors[] = "Baris {$line}: MID kosong";
                    continue;
                }

                $barang = MasterBarangModel::where('mid', $mid)->first();

                if (!$barang) {
                    $errors[] = "Baris {$line}: MID {$mid} tidak ditemukan";
                    continue;
                }

                $mappedRows[] = [
                    'barang_id'     => $barang->id,
                    'tanggal'       => now()->format('Y-m-d'),
                    'location'      => $row[1] ?? '',
                    'no_spb'        => $row[2] ?? null,
                    'qty'           => $row[3] ?? 0,
                    'incoming_date' => $row[4] ?? null,
                    'supplier'      => $row[5] ?? '',
                    'status'        => $row[6] ?? '',
                    'gudang'        => $row[7] ?? '',
                    'pallet'        => $row[8] ?? '',
                    'catatan'       => $row[9] ?? null,
                    'expired_date'  => $row[10] ?? null,
                    'created_by'    => Auth::id(),
                ];
            }

            // kalau ada error → batal semua
            if ($errors) {
                throw new \Exception(implode("\n", $errors));
            }

            // ✅ insert setelah semua aman
            foreach ($mappedRows as $data) {
                StockGulaModel::create($data);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Upload stock gula berhasil',
                'total'   => count($mappedRows),
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Upload dibatalkan',
                'errors' => explode("\n", $e->getMessage())
            ], 422);
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            ['MID', 'LOCATION', 'NO_SPB', 'QTY', 'INCOMING_DATE', 'SUPPLIER', 'STATUS', 'GUDANG', 'PALLET', 'CATATAN', 'EXPIRED_DATE'],
            ['MID001', 'F26', 12345, 100, '30/12/2026', 'Supplier A', 'unrest', 'WRM 6', 'Pallet-01', '', ''],
            ['MID002', 'F25', 12346, 200, '30/12/2026', 'Supplier B', 'unrest', 'WRM 6', 'Pallet-02', '', ''],
        ]);

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_stock_gula_wrm.xlsx';

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $filename
        );
    }
}
