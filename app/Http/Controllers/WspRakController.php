<?php

namespace App\Http\Controllers;

use App\Models\Wsp\RakModel;
use Illuminate\Http\Request;
use App\Models\Wsp\BarangModel;
use App\Models\Wsp\TransaksiModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Wsp\StockBarangRakModel;
use Illuminate\Support\Facades\Storage;

class WspRakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('manajemen_rak.data');
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
    public function storeRak(Request $request)
    {
        $request->validate([
            'kode_rak'   => 'required|array|max:50',
            'kode_rak.*' => 'string|max:50',
            'nama_rak'   => 'nullable|string|max:50',
            'kolom_rak'  => 'nullable|integer',
            'level_rak'  => 'nullable|integer',
            'box_rak'    => 'nullable|integer',
        ]);

        $savedData = [];

        foreach ($request->kode_rak as $kode) {
            $kode = trim($kode);

            if (RakModel::where('kode_rak', $kode)->exists()) {
                return response()->json([
                    'status'  => false,
                    'message' => "Kode Rak '$kode' sudah terdaftar. Gunakan kode lain.",
                ], 400);
            }

            $savedData[] = RakModel::create([
                'kode_rak'   => $kode,
                'nama_rak'   => $request->nama_rak ?? 'A',
                'kolom_rak'  => $request->kolom_rak ?? 1,
                'level_rak'  => $request->level_rak ?? 1,
                'box_rak'    => $request->box_rak ?? '0',
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Data Rak berhasil ditambahkan!',
            'data'    => $savedData,
        ], 200);
    }

    public function storeBarang(Request $request)
    {
        $request->validate([
            'mid_barang' => 'required|string|max:50',
            'nama_barang' => 'required|string|max:50',
            'deskripsi' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

            // data rak
            'kode_rak' => 'required|string|max:50',
            'nama_rak' => 'required|string|max:50',
            'kolom_rak' => 'required|integer',
            'level_rak' => 'required|integer',
            'box_rak' => 'nullable|max:50',

            // qty
            'qty' => 'required|integer|min:1',
        ]);

        if (BarangModel::where('mid_barang', $request->mid_barang)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'MID Barang sudah terdaftar. Gunakan MID lain.',
            ], 400);
        }

        $barang = BarangModel::create([
            'mid_barang' => $request->mid_barang,
            'nama_barang' => $request->nama_barang,
            'deskripsi' => $request->deskripsi,
            'image' => $request->hasFile('image') ? $request->file('image')->store('images/wsp', 'public') : null,
        ]);

        $rak = RakModel::firstOrCreate(
            [
                'kode_rak' => trim($request->kode_rak),
                'nama_rak' => trim($request->nama_rak),
                'kolom_rak' => (int) $request->kolom_rak,
                'level_rak' => (int) $request->level_rak,
                'box_rak' => $request->box_rak !== null ? trim($request->box_rak) : '0',
            ]
        );

        // update stock barang di rak
        $stockBarangRak = StockBarangRakModel::firstOrNew([
            'rak_id'    => $rak->id,
            'barang_id' => $barang->id
        ]);

        $stockBarangRak->stock = ($stockBarangRak->stock ?? 0) + $request->qty;
        $stockBarangRak->save();

        $transaksi = TransaksiModel::create([
            'barang_id' => $barang->id,
            'rak_id' => $rak->id,
            'stock_id' => $stockBarangRak->id,
            'user_id' => Auth::id() ?? 1, // default 1 kalau belum ada auth
            'qty' => $request->qty,
            'jenis_transaksi' => 'Register',
            'tgl_transaksi' => now(),
            'keterangan' => 'Registrasi barang baru',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Barang baru berhasil diregistrasi dan ditempatkan di rak.',
            'data' => $transaksi,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = StockBarangRakModel::with([
            'barang:id,mid_barang,nama_barang,image',
            'rak:id,kode_rak,nama_rak,kolom_rak,level_rak,box_rak',
            'transaksi' => function ($q) {
                $q->latest('created_at')->with('user:id,username')->limit(1);
            }
        ])->find($id);

        if (!$item) {
            return response()->json([
                'status' => false,
                'message' => 'Data id rak/barang tidak ditemukan.',
            ], 404);
        }

        $latestTransaksi = $item->transaksi->first();

        $data = [
            'id' => $latestTransaksi->id ?? null,
            'tgl_transaksi' => $latestTransaksi->tgl_transaksi ?? null,
            // 'jenis_transaksi' => $latestTransaksi->jenis_transaksi ?? null,
            'barang_id' => $item->barang->id ?? null,
            'nama_barang' => $item->barang->nama_barang ?? null,
            'mid_barang' => $item->barang->mid_barang ?? null,
            'qty' => $item->stock ?? 0,
            'kode_rak' => $item->rak->kode_rak ?? null,
            'nama_rak' => $item->rak->nama_rak ?? null,
            'kolom_rak' => $item->rak->kolom_rak ?? null,
            'level_rak' => $item->rak->level_rak ?? null,
            'box_rak' => $item->rak->box_rak ?? null,
            'username' => $latestTransaksi->user->username ?? null,
            'image' => $item->barang->image,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditemukan.',
            'data' => $data,
        ]);
    }

    public function getData()
    {
        $data = StockBarangRakModel::with([
            'barang:id,mid_barang,nama_barang,image',
            'rak:id,kode_rak,nama_rak,kolom_rak,level_rak,box_rak',
            'transaksi' => function ($q) {
                $q->latest('created_at')->with('user:id,username')->limit(1);
            }
        ])->get()->map(function ($item) {
            $latestTransaksi = $item->transaksi->first();

            return [
                'id' => $latestTransaksi->id ?? null,
                'tgl_transaksi' => $latestTransaksi->tgl_transaksi ?? null,
                'jenis_transaksi' => $latestTransaksi->jenis_transaksi ?? null,
                'barang_id' => $item->barang->id ?? null,
                'nama_barang' => $item->barang->nama_barang ?? null,
                'mid_barang' => $item->barang->mid_barang ?? null,
                'qty' => $item->stock ?? 0,
                'lokasi' => $item->rak
                    ? $item->rak->kode_rak . '-' .
                    $item->rak->nama_rak . '-' .
                    $item->rak->kolom_rak . '-' .
                    $item->rak->level_rak . '-' .
                    ($item->rak->box_rak ?? '0')
                    : null,
                'username' => $latestTransaksi->user->username ?? null,
                'image' => $item->barang->image ? asset('storage/' . $item->barang->image) : null,
            ];
        })
            ->sortByDesc('tgl_transaksi')
            ->values()
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditemukan.',
            'data' => $data,
        ]);
    }

    public function getDataRak()
    {
        $data = RakModel::select('id', 'kode_rak')
            ->orderBy('kode_rak')
            ->get()
            ->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditemukan.',
            'data' => $data,
        ]);
    }

    public function searchItems(Request $request)
    {
        $query = $request->input('q');

        $items = BarangModel::select('mid_barang', 'nama_barang')
            ->where('mid_barang', 'like', '%' . $query . '%')
            ->orWhere('nama_barang', 'like', '%' . $query . '%')
            ->get();

        return response()->json($items);
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
            'midBarang'   => 'required|string|max:50',
            'namaBarang'  => 'required|string|max:50',
            'deskripsi'    => 'nullable|string|max:255',
            'image'        => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

            // data rak
            'kodeRak'     => 'required|string|max:50',
            'namaRak'     => 'required|string|max:50',
            'kolomRak'    => 'required|integer',
            'levelRak'    => 'required|integer',
            'boxRak'      => 'nullable|max:50',

            // qty
            'qtyBarang'          => 'required|integer|min:1',
        ]);

        $transaksi = TransaksiModel::find($id);

        if (!$transaksi) {
            return response()->json([
                'status' => false,
                'message' => 'Data transaksi tidak ditemukan.',
            ], 404);
        }

        $barang = $transaksi->barang;
        if ($barang) {
            $barang->mid_barang = $request->midBarang;
            $barang->nama_barang = $request->namaBarang;
            $barang->deskripsi = $request->deskripsi;

            if ($request->hasFile('image')) {
                $barang->image = $request->file('image')->store('images/wsp', 'public');
            }

            $barang->save();
        }

        $rak = RakModel::firstOrCreate(
            [
                'kode_rak' => $request->kodeRak,
                'nama_rak' => $request->namaRak,
                'kolom_rak' => $request->kolomRak,
                'level_rak' => $request->levelRak,
                'box_rak' => $request->boxRak,
            ]
        );

        // Update transaksi
        $transaksi->barang_id = $barang->id;
        $transaksi->rak_id = $rak->id;
        $transaksi->user_id = Auth::id() ?? 1; // default 1 kalau belum ada auth
        $transaksi->qty = $request->qtyBarang;
        $transaksi->jenis_transaksi = 'update';
        $transaksi->tgl_transaksi = now();
        $transaksi->keterangan = 'Update data barang';
        $transaksi->save();

        return response()->json([
            'status' => true,
            'message' => 'Data transaksi berhasil diupdate.',
            'data' => $transaksi,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $transaksi = TransaksiModel::find($id);

            if (!$transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data transaksi tidak ditemukan.',
                ], 404);
            }

            $barang = $transaksi->barang;
            $stock  = $transaksi->stock;
            $deletedFile = false;

            if ($barang && $barang->image) {
                // Karena path di DB langsung "images/wsp/xxx.jpg"
                if (Storage::disk('public')->exists($barang->image)) {
                    Storage::disk('public')->delete($barang->image);
                    $deletedFile = true;
                }
            }

            $qty = $transaksi->qty;
            $jenis = $transaksi->jenis_transaksi;

            // Hapus transaksi & barang
            $transaksi->delete();

            if ($stock) {
                if ($jenis === 'in') {
                    // kalau transaksi masuk dihapus → stock harus berkurang
                    $stock->stock -= $qty;
                } elseif ($jenis === 'out') {
                    // kalau transaksi keluar dihapus → stock harus bertambah lagi
                    $stock->stock += $qty;
                }

                if ($stock->stock < 0) {
                    $stock->stock = 0;
                }

                $stock->save();
            }

            if ($barang) {
                $barang->delete();
            }

            // Hapus stock hanya kalau tidak ada transaksi lain
            if ($stock && $stock->transaksi()->count() === 0) {
                $stock->delete();
            }

            return response()->json([
                'status' => true,
                'message' => 'Data transaksi berhasil dihapus.',
                'file_deleted' => $deletedFile,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
