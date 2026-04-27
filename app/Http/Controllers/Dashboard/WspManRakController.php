<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use App\Http\Controllers\Controller;

class WspManRakController extends Controller
{
    public function getDataBarang()
    {
        $data = BarangModel::with([
            'rak:id,kode_rak,nama_rak,kolom_rak,level_rak,box_rak'
        ])
            ->get()
            ->map(function ($barang) {
                return [
                    'id'          => $barang->id,
                    'mid_barang'  => $barang->mid_barang,
                    'nama_barang' => $barang->nama_barang,
                    'stock' => null,
                    'lokasi'      => $barang->rak
                        ? $barang->rak->kode_rak . '.' . $barang->rak->nama_rak . '.' . $barang->rak->kolom_rak . '.' . $barang->rak->level_rak . '.' . ($barang->rak->box_rak ?? '000')
                        : null,
                ];
            })
            ->toArray();


        return response()->json([
            'status'  => true,
            'message' => 'Data barang beserta lokasi berhasil ditemukan.',
            'data'    => $data,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Ambil barang dan rak berdasarkan ID barang atau rak
        $barang = BarangModel::with(
            'rak:id,kode_rak,nama_rak,kolom_rak,level_rak,box_rak'
        )->find($id); // asumsi $id = id barang

        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Data barang tidak ditemukan.',
            ], 404);
        }

        // Ambil rak dari relasi

        $data = [
            'id'          => $barang->id,
            'mid_barang'  => $barang->mid_barang,
            'nama_barang' => $barang->nama_barang,
            'stock' => null,
            'lokasi'      => $barang->rak
                ? $barang->rak->kode_rak . '.' . $barang->rak->nama_rak . '.' . $barang->rak->kolom_rak . '.' . $barang->rak->level_rak . '.' . ($barang->rak->box_rak ?? '000')
                : null,
        ];

        // Kembalikan JSON
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditemukan.',
            'data' => $data,
        ]);
    }
}
