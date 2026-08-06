<?php

namespace App\Http\Controllers\Api\Wpm;

use App\Http\Controllers\Controller;
use App\Models\Wpm\WpmMasterBarangModel;
use Illuminate\Http\Request;

class ApiWpmContoller extends Controller
{
    /**
     * Get WPM Master Barang data.
     */
    public function getMasterBarang(Request $request)
    {
        try {
            $query = WpmMasterBarangModel::select('id', 'mid', 'nama_barang', 'uom');

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
                'message' => 'Data master barang WPM berhasil diambil.',
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
