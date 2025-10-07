<?php

namespace App\Http\Controllers\Wfg\stock_opname;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\Wfg\stock_opname\BarangWfgModel;

class BarangWfgController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('master.wfg.barang_so');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function data()
    {
        try {
            $barang = BarangWfgModel::all()->map(function ($item) {
                return [
                    'id'          => $item->id,
                    'mid_barang'  => $item->mid_barang,
                    'nama_barang' => $item->nama_barang,
                    'qty_box' => $item->qty_box,
                    'tipe_kemasan' => $item->tipe_kemasan,
                    'satuan'      => $item->satuan,
                    'status'      => $item->status,
                    'gambar'      => $item->gambar
                        ? url('storage/' . $item->gambar)
                        : url('assets/images/logo/kecap.png'),
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $barang
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data barang',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'mid_barang'   => 'integer|digits_between:1,8|unique:wfg_barang,mid_barang',
                'nama_barang'  => 'required|string|max:255',
                'status'       => 'required|in:aktif,nonaktif',
                'qty_box'      => 'required|integer|min:1', // Ditambahkan: qty_box harus diisi dan berupa integer
                'tipe_kemasan' => 'nullable|string|max:100',
                'satuan'       => 'nullable|string|max:50',
                'gambar'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Cek duplikat MID barang
            // if ($request->mid_barang && BarangWfgModel::where('mid_barang', $request->mid_barang)->exists()) {
            //     return response()->json([
            //         'status'  => false,
            //         'message' => 'MID Barang sudah terdaftar. Gunakan MID lain.',
            //     ], 400);
            // }

            $gambarPath = null;
            if ($request->hasFile('gambar')) {
                $gambarPath = $request->file('gambar')->store('images/wfg', 'public');
            }

            $barang = BarangWfgModel::create([
                'mid_barang'    => $request->mid_barang,
                'nama_barang'   => $request->nama_barang,
                'qty_box'       => $request->qty_box,
                'tipe_kemasan'  => $request->tipe_kemasan,
                'status'        => $request->status,
                'satuan'        => $request->satuan,
                'gambar'        => $gambarPath,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Barang berhasil ditambahkan',
                'data'    => $barang
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat menambahkan barang',
                'error'   => $e->getMessage(), // optional, untuk debugging
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $barang = BarangWfgModel::findOrFail($id);
        return response()->json($barang);
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
        try {
            $validated = $request->validate([
                'mid_barang'  => 'required|integer|digits_between:1,8|unique:wfg_barang,mid_barang',
                'nama_barang' => 'required|string|max:255',
                'status'      => 'required|in:aktif,nonaktif',
                'qty_box'     => 'required|integer|min:1', // Ditambahkan: qty_box harus diisi dan berupa integer
                'tipe_kemasan' => 'nullable|string|max:100',
                'satuan'      => 'nullable|string|max:50',
                'gambar'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $barang = BarangWfgModel::findOrFail($id);

            // Cek duplikat MID barang (kecuali dirinya sendiri)
            // if ($request->mid_barang && BarangWfgModel::where('mid_barang', $request->mid_barang)->where('id', '!=', $id)->exists()) {
            //     return response()->json([
            //         'status'  => false,
            //         'message' => 'MID Barang sudah digunakan oleh barang lain.',
            //     ], 400);
            // }

            $barang->update($validated);

            return response()->json([
                'status'  => true,
                'message' => 'Barang berhasil diupdate',
                'data'    => $barang
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat update barang',
                'error'   => $e->getMessage(), // untuk debug
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $barang = BarangWfgModel::findOrFail($id);
        $barang->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Barang berhasil dihapus (soft delete)'
        ]);
    }

    public function restore($id)
    {
        $barang = BarangWfgModel::withTrashed()->findOrFail($id);
        $barang->restore();

        return response()->json([
            'status'  => true,
            'message' => 'Barang berhasil direstore',
            'data' => $barang
        ]);
    }

    public function forceDelete($id)
    {
        $barang = BarangWfgModel::withTrashed()->findOrFail($id);
        $barang->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Barang berhasil dihapus permanen'
        ]);
    }
}
