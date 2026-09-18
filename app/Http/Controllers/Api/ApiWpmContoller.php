<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wpm\WpmMasterBarangModel;
use App\Models\Wsp\BarangModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiWpmContoller extends Controller
{
    /**
     * Get WPM Master Barang data.
     */
    public function getMasterBarang(Request $request)
    {
        try {
            $wpm = WpmMasterBarangModel::select('mid', 'nama_barang', 'uom');
            $wsp = BarangModel::select('mid_barang as mid', 'nama_barang', 'uom');

            $union = $wpm->union($wsp);
            $query = DB::query()->fromSub($union, 'combined_master_barang');

            // Search by mid or nama_barang if filled
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('mid', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            }

            // Optional pagination, default is to return all or 100 items if not specified
            if ($request->has('paginate') && $request->boolean('paginate')) {
                $perPage = $request->input('per_page', 25);
                $data = $query->paginate($perPage);
            } else {
                $limit = $request->input('limit', 100);
                $data = $query->limit($limit)->get();
            }

            return response()->json([
                'success' => true,
                'message' => 'Data master barang berhasil diambil.',
                'data'    => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data master barang WPM.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
