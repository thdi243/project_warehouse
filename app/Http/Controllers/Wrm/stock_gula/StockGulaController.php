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
        $data = $request->validated();

        $stocks = [];

        foreach ($data['pallet_id'] as $i => $pallet) {

            $stocks[] = StockGulaModel::create([
                'barang_id'     => $data['barang_id'],
                'no_spb'        => $data['no_spb'],
                'pallet_id'     => $pallet,
                'group'         => $data['group'],
                'qty'           => $data['qty'][$i],
                'incoming_date' => now(),
                'supplier'      => $data['supplier'],
                'status'        => $data['status'],
                'gudang'        => $data['gudang'],
                'loc'           => $data['loc'] ?? 'D01',
                'catatan'       => $data['catatan'] ?? null,
                'expired_date'  => $data['expired_date'] ?? null,
                'transaksi'     => 'inbound',
                'created_by'    => Auth::id(),
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Stock gula berhasil disimpan',
            'data'    => $stocks
        ]);
    }

    public function getData(Request $request)
    {
        $query = StockGulaModel::with(
            'barang:id,mid,nama_barang,uom,s_loc',
            'group:id,group'
        );

        if ($request->group_id) {
            $query->where('group', $request->group_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%' . $request->mid . '%');
            });
        }

        $barang = $query->get();

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

                if (($row[4] ?? 0) <= 0) {
                    $errors[] = "Baris {$line}: Qty harus lebih dari 0";
                }

                $mappedRows[] = [
                    'barang_id'     => $barang->id,
                    'tanggal'       => now()->format('Y-m-d'),

                    'location'      => $row[1] ?? null,
                    'no_spb'        => $row[2] ?? null,
                    'pallet_id'     => $row[3] ?? null,
                    'qty'           => $row[4] ?? 0,
                    'group'         => $row[5] ?? null,
                    'incoming_date' => $this->parseDate($row[6] ?? null),
                    'supplier'      => $row[7] ?? null,
                    'status'        => strtoupper($row[8] ?? ''),
                    'gudang'        => $row[9] ?? null,
                    'pallet'        => $row[10] ?? null,
                    'catatan'       => $row[11] ?? null,
                    'expired_date'  => $this->parseDate($row[12] ?? null),
                    'transaksi'     => 'inbound',

                    'created_by'    => Auth::id(),
                ];
            }

            if ($errors) {
                throw new \Exception(implode("\n", $errors));
            }

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
            ['MID', 'LOC', 'NO_SPB', 'PALLET_ID', 'QTY', 'GROUP', 'INCOMING_DATE', 'SUPPLIER', 'STATUS', 'GUDANG', 'PALLET', 'CATATAN', 'EXPIRED_DATE'],
            ['MID001', 'F26', 12345, 1, 100, 'A', '30/12/2026', 'Supplier A', 'UNREST', 'WRM 6', 'HOLLO GULA', '', ''],
            ['MID002', 'F26', 12345, 2, 100, 'A', '30/12/2026', 'Supplier B', 'UNREST', 'WRM 6', 'HOLLO GULA', '', ''],
        ]);

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_stock_gula_wrm.xlsx';

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $filename
        );
    }

    private function parseDate($value)
    {
        if (!$value) return null;

        try {

            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {

            return null;
        }
    }
}
