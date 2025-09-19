<?php

namespace App\Http\Controllers\Wsp;

use Illuminate\Http\Request;
use App\Models\Wsp\TransaksiModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Wsp\StockOnHandModel;
use Illuminate\Support\Facades\Auth;

class TransaksiWspController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'barang_id' => 'required|exists:barang,id',
            'jenis_transaksi' => 'required|in:in,out,adjustment',
            'qty' => 'required|integer',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan ke transaksi
            $transaksi = TransaksiModel::create([
                'barang_id' => $request->barang_id,
                'user_id' => Auth::id() ?? 1, // contoh pakai user login
                'qty' => $request->qty,
                'tgl_transaksi' => now(),
                'jenis_transaksi' => $request->jenis_transaksi,
                'keterangan' => $request->keterangan,
            ]);

            // 2. Update stock_on_hand
            $stock = StockOnHandModel::firstOrNew(['barang_id' => $request->barang_id]);

            if ($request->jenis_transaksi === 'in') {
                $stock->qty = ($stock->qty ?? 0) + $request->qty;
            } elseif ($request->jenis_transaksi === 'out' || $request->jenis_transaksi === 'adjustment') {
                $stock->qty = ($stock->qty ?? 0) + $request->qty;
            }

            $stock->last_update = now();

            $stock->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil disimpan',
                'data' => [
                    'transaksi' => $transaksi,
                    'stock_on_hand' => $stock,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
