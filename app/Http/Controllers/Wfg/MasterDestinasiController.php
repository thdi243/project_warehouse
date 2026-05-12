<?php

namespace App\Http\Controllers\Wfg;

use App\Http\Controllers\Controller;
use App\Models\Wfg\MasterDestinasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterDestinasiController extends Controller
{
    public function index()
    {
        return view('master.wfg.destinasi');
    }

    public function data(Request $request)
    {
        $perPage = 10;
        $search = $request->input('search');

        $query = MasterDestinasi::with(['createdBy', 'updatedBy']);

        if ($search) {
            $query->where('destinasi', 'like', '%' . $search . '%');
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $paginated
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'destinasi' => 'required|string|max:255|unique:wfg_master_destinasi,destinasi',
        ]);

        MasterDestinasi::create([
            'destinasi' => $request->destinasi,
            'active' => true,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Destinasi berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'destinasi' => 'required|string|max:255|unique:wfg_master_destinasi,destinasi,' . $id,
        ]);

        $destinasi = MasterDestinasi::findOrFail($id);
        $destinasi->update([
            'destinasi' => $request->destinasi,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Destinasi berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $destinasi = MasterDestinasi::findOrFail($id);
        $destinasi->delete();

        return response()->json(['message' => 'Destinasi berhasil dihapus']);
    }
}
