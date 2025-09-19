<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\TransaksiModel;
use App\Http\Controllers\Controller;
use App\Models\Wsp\StockBarangRakModel;

class RakDashboardController extends Controller
{
    public function getDataRack()
    {
        $data = BarangModel::with([
            'rak:id,nama_rak,kode_rak,kolom_rak,level_rak,box_rak',
            'user:id,username'
        ])->get()->map(function ($barang) {
            return [
                'id'   => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'mid_barang'  => $barang->mid_barang,
                'image'       => $barang->image ? asset('storage/' . $barang->image) : null,
                'rak_id'      => $barang->rak->id ?? null,
                'kode_rak'    => $barang->rak->kode_rak ?? null,
                'nama_rak'    => $barang->rak->nama_rak ?? null,
                'kolom_rak'   => $barang->rak->kolom_rak ?? null,
                'level_rak'   => $barang->rak->level_rak ?? null,
                'box_rak'     => $barang->rak->box_rak ?? '000',
                'username'     => $barang->user->username ?? null,
            ];
        })->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Data barang berhasil ditemukan.',
            'data'   => $data,
        ]);
    }
}
