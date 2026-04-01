<?php

namespace App\Http\Controllers\Wrm;

use App\Http\Controllers\Controller;
use App\Models\Wrm\MasterPalletModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterPalletController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ?? '';

        $query = MasterPalletModel::query();

        if ($search) {
            $query->where('nama_pallet', 'like', "%{$search}%");
        }

        $pallets = $query->paginate(25);

        return view('master.wrm.master_pallet', compact('pallets', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'jenis_bahan' => 'required|string|max:255',
            'nama_pallet' => 'required|string|max:255'
        ]);

        $validated['created_by'] = Auth::id();

        $pallet = MasterPalletModel::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Master Pallet berhasil ditambahkan',
            'data'    => $pallet
        ]);
    }

    public function update(Request $request, $id)
    {
        $pallet = MasterPalletModel::findOrFail($id);

        $validated = $request->validate([
            // 'jenis_bahan' => 'required|string|max:255',
            'nama_pallet' => 'required|string|max:255'
        ]);

        $validated['updated_by'] = Auth::id();

        $pallet->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Master Pallet berhasil diperbarui',
            'data'    => $pallet
        ]);
    }

    public function destroy($id)
    {
        $pallet = MasterPalletModel::findOrFail($id);

        $pallet->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Master Pallet berhasil dihapus'
        ]);
    }

    public function getData(Request $request)
    {
        $search = $request->search ?? '';

        $query = MasterPalletModel::query()->with('createdBy:id,nama_lengkap', 'updatedBy:id,nama_lengkap');

        if ($search) {
            $query->where('nama_pallet', 'like', "%{$search}%");
        }

        $data = $query->paginate(25);

        return response()->json([
            'status' => true,
            'message' => 'Data master pallet berhasil diambil',
            'data' => $data
        ]);
    }
}
