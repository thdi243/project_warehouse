<?php

namespace App\Http\Controllers\Wsp;

use App\Models\Wsp\RakModel;
use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\TransaksiModel;
use Illuminate\Support\Facades\Auth;
use App\Models\Wsp\StockBarangRakModel;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class WspRakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('manajemen_rak.list_barang');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kodeRak'  => 'required|string|max:50',
            'namaRak'  => 'nullable|string|max:50',
            'kolomRak' => 'nullable|integer',
            'levelRak' => 'nullable|integer',
            'boxRak'   => 'nullable|string',
        ]);

        try {
            $data = RakModel::create([
                'user_id'   => Auth::id() ?? 1, // kalau tabel rak ada kolom user_id
                'kode_rak'  => strtoupper(trim($request->kodeRak)),
                'nama_rak'  => strtoupper($request->namaRak ?? 'A'),
                'kolom_rak' => $request->kolomRak ?? 1,
                'level_rak' => $request->levelRak ?? 1,
                'box_rak'   => $request->boxRak ?? '000',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data Rak berhasil ditambahkan!',
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data Rak dengan kombinasi tersebut sudah ada.',
                ], 422);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menyimpan data rak.',
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rak = RakModel::find($id);

        if (!$rak) {
            return response()->json([
                'status'  => false,
                'message' => 'Data Rak tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Detail Data Rak',
            'data'    => $rak
        ], 200);
    }

    public function getDataRak()
    {
        $dataRak = RakModel::select(
            'rak.*',
            'users.username as name'
        )
            ->join('users', 'users.id', '=', 'rak.user_id')
            ->get();
        return response()->json($dataRak);
    }

    public function getFilters()
    {
        $area = RakModel::select('kode_rak')
            ->distinct()
            ->pluck('kode_rak');

        $nama = RakModel::select('nama_rak')
            ->distinct()
            ->pluck('nama_rak');

        return response()->json([
            'area' => $area,
            'nama' => $nama
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'kodeRakEdit'  => 'required|string|max:50',
            'namaRakEdit'  => 'nullable|string|max:50',
            'kolomRakEdit' => 'nullable|integer',
            'levelRakEdit' => 'nullable|integer',
            'boxRakEdit'   => 'nullable|string',
        ]);

        try {
            // Cari data rak berdasarkan ID
            $rak = RakModel::findOrFail($id);

            // Update datanya
            $rak->update([
                'user_id'   => Auth::id() ?? 1,
                'kode_rak'  => strtoupper(trim($request->kodeRakEdit)),
                'nama_rak'  => strtoupper($request->namaRakEdit ?? 'A'),
                'kolom_rak' => $request->kolomRakEdit ?? 1,
                'level_rak' => $request->levelRakEdit ?? 1,
                'box_rak'   => $request->boxRakEdit ?? 0,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data Rak berhasil diperbarui!',
                'data'    => $rak,
            ], 200);
        } catch (\Exception $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data Rak dengan kombinasi tersebut sudah ada.',
                ], 422);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memperbarui data rak.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rak = RakModel::findOrFail($id);
        $rak->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Data Rak berhasil dihapus!',
        ], 200);
    }
}
