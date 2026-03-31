<?php

namespace App\Http\Controllers\Wrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\MasterBinRequest;
use App\Models\Wrm\MasterBinModel;
use App\Models\Wrm\MasterLocationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterBinController extends Controller
{
    public function index()
    {
        $locations = MasterLocationModel::whereDoesntHave('bins')->get();

        return view('master.wrm.master_bin', compact('locations'));
    }

    public function store(MasterBinRequest $request)
    {
        $validated = $request->validated();
        $totalKolom = $validated['kolom'];
        $totalLevel = $validated['level'];
        $loc_id = $validated['loc_id'];
        $userId = Auth::id();

        // Check if loc_id already exists in MasterBinModel
        $exists = MasterBinModel::where('loc_id', $loc_id)->exists();
        if ($exists) {
            return response()->json([
                'status'  => false,
                'message' => 'Lokasi (Zona & Bin) ini sudah memiliki data bin. Silahkan hapus data bin lama terlebih dahulu.',
            ], 422);
        }

        // Generate matrix combinations
        $binData = [];
        for ($k = 1; $k <= $totalKolom; $k++) {
            for ($l = 1; $l <= $totalLevel; $l++) {
                $binData[] = [
                    'loc_id' => $loc_id,
                    'kolom' => $k,
                    'level' => $l,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert all combinations
        MasterBinModel::insert($binData);

        return response()->json([
            'status'  => true,
            'message' => "Bin berhasil disimpan ($totalKolom x $totalLevel = " . ($totalKolom * $totalLevel) . " kombinasi)",
            'data'    => $binData,
        ]);
    }

    public function getData()
    {
        $bin = MasterBinModel::with('location')->paginate(50);

        return response()->json([
            'status' => true,
            'data'   => $bin
        ]);
    }

    public function destroy($id)
    {
        $bin = MasterBinModel::findOrFail($id);

        $bin->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Bin berhasil dihapus',
        ]);
    }

    public function destroyByLoc($locId)
    {
        $count = MasterBinModel::where('loc_id', $locId)->count();

        if ($count === 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak ada bin yang ditemukan untuk zona-bin ini',
            ], 404);
        }

        MasterBinModel::where('loc_id', $locId)->delete();

        return response()->json([
            'status'  => true,
            'message' => "Berhasil menghapus $count bin pada zona-bin ini",
        ]);
    }
}
