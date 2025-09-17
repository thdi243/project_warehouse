<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Wsp\TransaksiModel;
use App\Http\Controllers\Controller;
use App\Models\Wsp\StockBarangRakModel;

class RakDashboardController extends Controller
{
    public function getDataRack()
    {
        $data = StockBarangRakModel::with([
            'barang:id,mid_barang,nama_barang,image',
            'rak:id,nama_rak,kode_rak,kolom_rak,level_rak,box_rak',
        ])
            ->get()
            ->map(function ($item) {
                $latestTransaksi = TransaksiModel::where('barang_id', $item->barang_id)
                    ->latest('tgl_transaksi')
                    ->with('user:id,username')
                    ->first();

                return [
                    'barang_id'  => $item->barang->id ?? null,
                    'nama_barang' => $item->barang->nama_barang ?? null,
                    'mid_barang' => $item->barang->mid_barang ?? null,
                    'qty'      => $item->stock ?? 0,
                    'image'      => $item->barang->image ? asset('storage/' . $item->barang->image) : null,
                    'rak_id'     => $item->rak->id ?? null,
                    'kode_rak'   => $item->rak->kode_rak ?? null,
                    'nama_rak'   => $item->rak->nama_rak ?? null,
                    'kolom_rak'  => $item->rak->kolom_rak ?? null,
                    'level_rak'  => $item->rak->level_rak ?? null,
                    'box_rak'    => $item->rak->box_rak ?? '0',
                    'tgl_transaksi' => $latestTransaksi->tgl_transaksi ?? null,
                    'username' => $latestTransaksi->user->username ?? null,
                ];
            })
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditemukan.',
            'data' => $data,
        ]);
    }
}
