<?php

namespace App\Http\Controllers\Wsp;

use Illuminate\Http\Request;
use App\Models\Wsp\TransaksiModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Wsp\StockOpnameModel;
use Illuminate\Support\Facades\Auth;
use App\Models\Wsp\StockBarangRakModel;

class StockOpnameController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.rak_id'        => 'required|integer|exists:rak,id',
            'items.*.barang_id'     => 'required|integer|exists:barang,id',
            'items.*.stock_fisik'   => 'required|integer|min:0',
            'items.*.keterangan'    => 'nullable|string'
        ]);

        $results = [];

        foreach ($request->items as $item) {
            // pastikan rak + barang sudah ada di stock
            $stock = StockBarangRakModel::firstOrCreate(
                [
                    'rak_id'    => $item['rak_id'],
                    'barang_id' => $item['barang_id'],
                ],
                ['stock' => 0]
            );

            $stockSistem = $stock->stock;
            $stockFisik  = (int) $item['stock_fisik'];
            $selisih     = $stockFisik - $stockSistem;

            // simpan opname
            $opname = StockOpnameModel::create([
                'rak_id'       => $item['rak_id'],
                'barang_id'    => $item['barang_id'],
                'user_id'      => Auth::id() ?? 1,
                'stock_sistem' => $stockSistem,
                'stock_fisik'  => $stockFisik,
                'selisih'      => $selisih,
                'tgl_opname'   => now()->toDateString(),
                'keterangan'   => $item['keterangan'] ?? 'Valid',
            ]);

            // kalau ada selisih, buat adjustment transaksi
            if ($selisih !== 0) {
                TransaksiModel::create([
                    'barang_id'       => $item['barang_id'],
                    'rak_id'          => $item['rak_id'],
                    'user_id'         => Auth::id() ?? 1,
                    'stock_id'        => $stock->id,
                    'qty'             => abs($selisih),
                    'jenis_transaksi' => $selisih > 0 ? 'Update' : 'Out',
                    'tgl_transaksi'   => now()->toDateString(),
                    'keterangan'      => 'Adjustment opname #' . $opname->id,
                ]);

                // update stock sesuai hasil fisik
                $stock->stock = $stockFisik;
                $stock->save();
            }

            $results[] = $opname;
        }

        return response()->json([
            'status'  => true,
            'message' => 'Opname berhasil disimpan',
            'data'    => $results
        ]);
    }
}
