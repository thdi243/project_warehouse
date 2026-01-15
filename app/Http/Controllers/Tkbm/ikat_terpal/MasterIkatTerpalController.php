<?php

namespace App\Http\Controllers\Tkbm\ikat_terpal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tkbm\ikat_terpal\FeeIkatTerpal;
use App\Models\Tkbm\ikat_terpal\ProdukIkatTerpal;

class MasterIkatTerpalController extends Controller
{
    public function index()
    {
        return view('master.wrm.ikat_terpal.index');
    }

    public function storeFee(Request $request)
    {
        // Validation
        $request->validate([
            'fee' => 'nullable|numeric|min:0',
            'ppn' => 'nullable|numeric|min:0',
            'pph' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Deactivate previous active fee
            FeeIkatTerpal::where('aktif', true)->update(['aktif' => false]);

            $data = FeeIkatTerpal::create([
                'fee' => $request->fee,
                'ppn' => $request->ppn,
                'pph' => $request->pph,
                'keterangan' => $request->keterangan,
                'aktif' => true,
                'user_id' => Auth::id()
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Fee Ikat Terpal berhasil dibuat',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeProduk(Request $request)
    {
        // Validation
        $request->validate([
            'harga_pallet' => 'nullable|numeric|min:1',
            'satuan' => 'nullable|string',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Deactivate previous active fee
            ProdukIkatTerpal::where('aktif', true)->update(['aktif' => false]);

            $data = ProdukIkatTerpal::create([
                'harga_pallet' => $request->harga_pallet,
                'satuan' => $request->satuan,
                'keterangan' => $request->keterangan,
                'aktif' => true,
                'user_id' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Harga Produk Ikat Terpal berhasil dibuat',
                'data'  => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFeeAktif()
    {
        $fee = FeeIkatTerpal::with('user:id,nama_lengkap,username')
            ->where('aktif', true)->first();
        return response()->json([
            'fee'         => $fee ? $fee->fee : null,
            'ppn'         => $fee ? $fee->ppn : null,
            'pph'         => $fee ? $fee->pph : null,
            'keterangan'  => $fee ? $fee->keterangan : null,
            'user'        => $fee ? [
                'nama_lengkap' => $fee->user->nama_lengkap ?? null,
                'username'     => $fee->user->username ?? null,
            ] : null,
            'created_at'  => $fee ? $fee->created_at->format('d M Y H:i') : null,
        ]);
    }

    public function getProdukAktif()
    {
        $produk = ProdukIkatTerpal::with('user:id,nama_lengkap,username')
            ->where('aktif', true)->first();
        return response()->json([
            'harga_pallet' => $produk ? $produk->harga_pallet : null,
            'satuan'       => $produk ? $produk->satuan : null,
            'keterangan'   => $produk ? $produk->keterangan : null,
            'user'         => $produk ? [
                'nama_lengkap' => $produk->user->nama_lengkap ?? null,
                'username'     => $produk->user->username ?? null,
            ] : null,
            'created_at'   => $produk ? $produk->created_at->format('d M Y H:i') : null,
        ]);
    }
}
