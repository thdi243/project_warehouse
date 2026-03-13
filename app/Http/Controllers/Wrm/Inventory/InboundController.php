<?php

namespace App\Http\Controllers\Wrm\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\StockGulaRequest;
use App\Http\Requests\Wrm\StockGulaUploadRequest;
use App\Models\Wrm\Inventory\StockBalance;
use App\Models\Wrm\Inventory\StockInbound;
use App\Models\Wrm\Inventory\StockInboundDetail;
use App\Models\Wrm\Inventory\StockMovement;
use App\Models\Wrm\MasterBarangModel;
use App\Models\Wrm\MasterLocationModel;
use App\Models\Wrm\StockGula\StockGulaModel;
use App\Models\Wrm\Inventory\TempUploadModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InboundController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $barang = MasterBarangModel::select('id', 'mid', 'nama_barang')
            ->whereRaw('LOWER(nama_barang) LIKE ?', ['%gula%'])
            ->get();

        $location = MasterLocationModel::get();

        return view('wrm.inventory.index', compact('barang', 'location'));
    }

    public function indexUpload()
    {
        $hasTemp = TempUploadModel::whereDate('incoming_date', now())->exists();

        if ($hasTemp) {
            return redirect()->route('wrm.inventory.select-location');
        }

        $barang = MasterBarangModel::select('id', 'mid', 'nama_barang')->get();

        return view('wrm.inventory.upload', compact('barang'));
    }

    public function selectLocationView()
    {
        $today = Carbon::now();

        $data = TempUploadModel::whereDate('incoming_date', $today)->get();

        // jika tidak ada data
        if ($data->isEmpty()) {
            return redirect()->route('wrm.inventory.index-upload');
        }

        $usedLocation = StockInboundDetail::pluck('loc_id')->toArray();

        $locations = MasterLocationModel::whereNotIn('id', $usedLocation)->get();

        return view('wrm.inventory.after_upload', compact('data', 'locations'));
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

            $headers = [];

            foreach ($request->loc_id as $tempId => $locId) {

                $temp = $temps->firstWhere('id', $tempId);

                $barang = $barangs[$temp->mid] ?? null;

                if (!$barang) {
                    throw new \Exception("MID {$temp->mid} tidak ditemukan");
                }

                if (!isset($headers[$temp->no_spb])) {
                    $headers[$temp->no_spb] = StockInbound::create([
                        'no_spb'        => $temp->no_spb,
                        'incoming_date' => now(),
                        'expired_date'  => $temp->expired_date ?? null,
                        'supplier'      => $temp->supplier ?? null,
                        'created_by'    => Auth::id(),
                    ]);
                }

                $header = $headers[$temp->no_spb];

                $detail = StockInboundDetail::create([
                    'inbound_id' => $header->id,
                    'barang_id'  => $barang->id,
                    'pallet_id'  => $temp->pallet_id,
                    'group'      => $temp->group,
                    'qty'        => $temp->qty,
                    'status'     => $temp->status,
                    'loc_id'     => $locId ?? 1,
                    'pallet'     => $temp->pallet,
                    'catatan'    => $temp->catatan,
                    'created_by' => Auth::id(),
                ]);

                StockMovement::create([
                    'barang_id'  => $barang->id,
                    'loc_id'     => $locId,
                    'tanggal'    => now(),
                    'qty'        => $temp->qty,
                    'jenis'      => 'in',
                    'ref_type'   => 'inbound',
                    'ref_id'     => $detail->id,
                    'catatan'    => $temp->catatan,
                    'created_by' => Auth::id(),
                ]);

                $balance = StockBalance::where('barang_id', $barang->id)
                    ->where('loc_id', $locId)
                    ->first();

                if ($balance) {

                    $balance->increment('qty', $temp->qty);
                } else {

                    StockBalance::create([
                        'barang_id'  => $barang->id,
                        'loc_id'     => $locId,
                        'qty'        => $temp->qty,
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            TempUploadModel::whereIn('id', array_keys($request->loc_id))->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Inventory stock berhasil disimpan'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        $query = StockInboundDetail::with([
            'barang:id,mid,nama_barang,uom',
            'location:id,gudang,bin,s_loc,plant',
            'inbound:id,no_spb,incoming_date,supplier'
        ])
            ->whereIn('status', ['UNREST', 'QI', 'BLOCKED']);

        if ($request->group) {
            $query->where('group', $request->group);
        }

        if ($request->jenis_bahan) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->jenis_bahan . '%');
            });
        }

        if ($request->mid) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('mid', 'like', '%' . $request->mid . '%');
            });
        }

        if ($request->date) {
            $query->whereHas('inbound', function ($q) use ($request) {
                $q->whereDate('incoming_date', $request->date);
            });
        }

        if ($request->supplier) {
            $query->whereHas('inbound', function ($q) use ($request) {
                $q->where('supplier', 'like', '%' . $request->supplier . '%');
            });
        }

        $data = $query->paginate(25);

        return response()->json([
            'status' => true,
            'message' => 'Data stock inventory berhasil diambil',
            'data' => $data
        ]);
    }

    public function getFilter()
    {
        $groups = StockInboundDetail::select('group')
            ->distinct()
            ->pluck('group');

        $jenisBahan = MasterBarangModel::select('nama_barang')
            ->distinct()
            ->pluck('nama_barang');

        return response()->json([
            'groups' => $groups,
            'jenis_bahan' => $jenisBahan
        ]);
    }

    public function update(StockGulaRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $detail = StockInboundDetail::findOrFail($id);

            $oldQty = $detail->qty;
            $oldLoc = $detail->loc_id;
            $barangId = $detail->barang_id;

            $detail->update([
                'pallet_id' => $request->pallet_id,
                'group'     => $request->group,
                'qty'       => $request->qty,
                'status'    => $request->status,
                'loc_id'    => $request->loc_id,
                'catatan'   => $request->catatan,
                'updated_by' => Auth::id(),
            ]);

            $movement = StockMovement::where('ref_type', 'inbound')
                ->where('ref_id', $detail->id)
                ->first();

            if ($movement) {

                $movement->update([
                    'qty'    => $request->qty,
                    'loc_id' => $request->loc_id,
                    'catatan' => $request->catatan
                ]);
            }

            $qtyDiff = $request->qty - $oldQty;

            $balance = StockBalance::where('barang_id', $barangId)
                ->where('loc_id', $oldLoc)
                ->first();

            if ($balance) {
                $balance->increment('qty', $qtyDiff);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Stock gula berhasil diperbarui',
                'data'    => $detail
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $barang = StockInboundDetail::findOrFail($id);

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
                $group      = $row[3] ?? null;
                $status     = strtoupper(trim($row[8] ?? ''));
                $supplier   = $row[9] ?? null;
                $pallet     = $row[10] ?? null;
                $catatan    = $row[11] ?? null;
                $expire     = $row[12] ?? null;

                $qty = $row[6] ?? 0;
                $qty = str_replace('.', '', $qty);
                $qty = str_replace(',', '', $qty);
                $qty = (int) $qty;

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
                    'expired_date' => $expire ?? null,
                    'supplier'    => $supplier ?? null,
                    'status'      => $status,
                    'pallet'      => $pallet ?? null,
                    'catatan'     => $catatan ?? null,

                    'created_by'  => Auth::id(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
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
