<?php

namespace App\Http\Controllers\Wsp\purchase_requesition;

use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Wsp\purchase_requesition\WspPurchaseRequesitionModel;

class WspPurchaseRequesitionController extends Controller
{
    public function index()
    {
        return view('wsp.purchase_requesition.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'pr_date'       => 'required|date',
            'hal'           => 'nullable|string|max:255',
            'no_doc'        => 'nullable|string|max:100',
            'requested_by'  => 'required|string|max:255',
            'department'    => 'required|string|max:255',
            'jenis'         => 'required|string|max:255',
            'detail_jenis'  => 'nullable|string|max:255',

            'items'                 => 'required|array|min:1',
            'items.*.mid'           => 'required|string',
            'items.*.qty'           => 'required|numeric|min:1',
            'items.*.keterangan'    => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $pr = WspPurchaseRequesitionModel::create([
                'pr_number'     => $request->pr_number,
                'pr_date'       => $request->pr_date,
                'hal'           => $request->hal,
                'no_doc'        => $request->no_doc,
                'requested_by'  => $request->requested_by,
                'department'    => $request->department,
                'jenis'         => $request->jenis,
                'detail_jenis'  => $request->detail_jenis,
                'status'        => 'pending',
                'user_id'       => Auth::id() ?? 1,
            ]);

            foreach ($request->items as $index => $item) {

                $barang = BarangModel::where('mid_barang', $item['mid'])->first();

                if (!$barang) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => "MID {$item['mid']} tidak ditemukan (baris ke-" . ($index + 1) . ")"
                    ], 422);
                }

                $pr->items()->create([
                    'pr_id'      => $pr->id,
                    'barang_id'  => $barang->id,
                    'qty'        => $item['qty'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'      => true,
                'message' => 'Purchase Requisition berhasil dibuat.',
                'data'    => $pr->load('items.barang')
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status'      => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $data = WspPurchaseRequesitionModel::with([
            'user:id,nama_lengkap',
            'barang:id,mid_barang,nama_barang'
        ])->find($id);

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

    public function getDataPR()
    {
        $pr = WspPurchaseRequesitionModel::with('user', 'items.barang')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'   => $pr
        ], 200);
    }

    public function searchBarang(Request $request)
    {
        $keyword = $request->keyword;

        $barang = BarangModel::where('mid_barang', 'LIKE', "%$keyword%")
            ->orWhere('nama_barang', 'LIKE', "%$keyword%")
            ->limit(10)
            ->get();

        return response()->json([
            'status' => true,
            'data'  => $barang
        ]);
    }

    public function destroy($id)
    {
        $data = WspPurchaseRequesitionModel::find($id);

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
