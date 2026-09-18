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

            // Search by mid or nama_barang if filled
            $search = trim($request->input('search') ?? $request->input('q') ?? '');
            if ($search !== '') {
                $wpm->where(function ($q) use ($search) {
                    $q->where('mid', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
                $wsp->where(function ($q) use ($search) {
                    $q->where('mid_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            }

            $union = $wpm->union($wsp);
            $query = DB::query()->fromSub($union, 'combined_master_barang');

            // Pagination (default 25 per page or from per_page / limit parameter)
            $perPage = (int) $request->input('per_page', $request->input('limit', 25));
            $data = $query->paginate($perPage);

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
