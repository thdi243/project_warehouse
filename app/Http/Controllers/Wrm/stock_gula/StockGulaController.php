<?php

namespace App\Http\Controllers\Wrm\stock_gula;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\StockGulaRequest;
use App\Http\Requests\Wrm\StockGulaUploadRequest;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterLocationModel;
use App\Models\Wrm\StockGula\StockGulaModel;
use App\Models\Wrm\StockGula\TempUploadModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StockGulaController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $barang = MasterBarangModel::select('id', 'mid', 'nama_barang')
            ->whereRaw('LOWER(nama_barang) LIKE ?', ['%gula%'])
            ->get();

        $location = MasterLocationModel::get();

        return view('wrm.stock_gula.index', compact('barang', 'location'));
    }

    public function indexUpload()
    {
        $barang = MasterBarangModel::select('id', 'mid', 'nama_barang')->get();

        return view('wrm.stock_gula.upload', compact('barang'));
    }

    public function indexTransfer()
    {
        return view('wrm.stock_gula.transfer');
    }

    public function selectLocationView()
    {
        $today = Carbon::now();

        $data = TempUploadModel::whereDate('incoming_date', $today)->get();

        $usedLocation = StockGulaModel::pluck('loc_id')->toArray();

        $locations = MasterLocationModel::whereNotIn('id', $usedLocation)->get();

        return view('wrm.stock_gula.after_upload', compact('data', 'locations'));
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

    public function getSpb()
    {
        $data = StockGulaModel::select('no_spb')
            ->distinct()
            ->pluck('no_spb');

        return response()->json([
            'data' => $data
        ]);
    }

    public function bySpb(Request $request)
    {
        $data = StockGulaModel::with('barang')
            ->where('no_spb', $request->spb)
            ->where('status', 'UNREST')
            ->get();

        return response()->json([
            'data' => $data
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
                'jenis_bahan'   => $data['jenis_bahan'],
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

    public function storeUpload(StockGulaUploadRequest $request)
    {
        DB::beginTransaction();

        try {
            $temps = TempUploadModel::whereIn('id', array_keys($request->loc_id))->get();
            $barangs = MasterBarangModel::whereIn('mid', $temps->pluck('mid'))
                ->get()
                ->keyBy('mid');

            foreach ($request->loc_id as $tempId => $locId) {

                $temp = $temps->firstWhere('id', $tempId);

                $barang = $barangs[$temp->mid] ?? null;

                if (!$barang) {
                    throw new \Exception("MID {$temp->mid} tidak ditemukan");
                }

                StockGulaModel::create([
                    'barang_id'     => $barang->id,
                    'no_spb'        => $temp->no_spb,
                    'pallet_id'     => $temp->pallet_id,
                    'group'         => $temp->group,
                    'qty'           => $temp->qty,
                    'incoming_date' => now(),
                    'supplier'      => $temp->supplier ?? null,
                    'status'        => $temp->status,
                    'loc_id'        => $locId,
                    'catatan'       => $temp->catatan ?? null,
                    'created_by'    => Auth::id(),
                ]);
            }

            $tempIds = array_keys($request->loc_id);

            TempUploadModel::whereIn('id', $tempIds)->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Stock gula berhasil disimpan'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function transfer(Request $request)
    {
        $ids = $request->ids;

        StockGulaModel::whereIn('id', $ids)
            ->update([
                'status' => 'TRANSFER',
                'issued_date' => now(),
                'updated_by' => Auth::id()
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Stock berhasil ditransfer'
        ]);
    }

    public function getData(Request $request)
    {
        $query = StockGulaModel::with(
            'barang:id,mid,nama_barang,uom',
            'location:id,gudang,bin,s_loc,plant'
        );

        if ($request->group) {
            $query->where('group', $request->group);
        }

        if ($request->status) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->jenis_bahan . '%');
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%' . $request->mid . '%');
            });
        }


        $data = $query->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Data stock gula berhasil diambil',
            'data' => $data
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

            $today = now()->toDateString();
            $prefixTracker = [];

            foreach ($rows as $i => $row) {

                $line = $i + 2;

                $barcode    = trim($row[0] ?? '');
                $mid        = trim($row[1] ?? '');
                $qty        = $row[2] ?? 0;
                $group      = $row[3] ?? null;
                $supplier   = $row[4] ?? null;
                $status     = strtoupper(trim($row[5] ?? ''));
                $pallet     = $row[6] ?? null;
                $catatan    = $row[7] ?? null;

                if ($mid === '') {
                    $errors[] = "Baris {$line}: MID kosong";
                    continue;
                }

                if ($qty <= 0) {
                    $errors[] = "Baris {$line}: Qty harus lebih dari 0";
                    continue;
                }

                $barcodePrefix = substr($barcode, 0, 10);

                // CEK DUPLICATE DI DATABASE
                $exist = TempUploadModel::where('mid', $mid)
                    ->whereDate('incoming_date', $today)
                    ->whereRaw('LEFT(barcode,10) = ?', [$barcodePrefix])
                    ->exists();

                if ($exist) {
                    $errors[] = "Baris {$line}: Barcode (No SPB) {$barcodePrefix}, MID {$mid} sudah ada di database hari ini";
                    continue;
                }

                // GENERATE PALLET_ID
                if (!isset($prefixTracker[$barcodePrefix])) {
                    $prefixTracker[$barcodePrefix] = 1;
                } else {
                    $prefixTracker[$barcodePrefix]++;
                }

                $pallet_id = $prefixTracker[$barcodePrefix];

                $mappedRows[] = [
                    'barcode'     => $barcode,
                    'no_spb'      => $barcodePrefix,
                    'mid'         => $mid,
                    'pallet_id'   => $pallet_id,
                    'qty'         => $qty,
                    'group'       => $group,
                    'incoming_date' => now(),
                    'supplier'    => $supplier ?? null,
                    'status'      => $status,
                    'pallet'      => $pallet ?? null,
                    'catatan'     => $catatan ?? null,

                    'created_by'  => Auth::id(),
                    'updated_by'  => Auth::id(),
                ];
            }

            if ($errors) {
                throw new \Exception(implode("\n", $errors));
            }

            // foreach ($mappedRows as $data) {
            //     TempUploadModel::create($data);
            // }
            TempUploadModel::insert($mappedRows);

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
}
