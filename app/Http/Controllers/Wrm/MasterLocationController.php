<?php

namespace App\Http\Controllers\Wrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\MasterLocationRequest;
use App\Models\Wrm\MasterLocationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterLocationController extends Controller
{
    public function index()
    {
        return view('master.wrm.master_location');
    }

    public function store(MasterLocationRequest $request)
    {
        $stock = MasterLocationModel::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Location berhasil disimpan',
            'data'    => $stock,
        ]);
    }

    public function getData()
    {
        $location = MasterLocationModel::all();

        return response()->json([
            'status' => true,
            'data' => $location
        ]);
    }

    public function update(MasterLocationRequest $request, $id)
    {
        $location = MasterLocationModel::findOrFail($id);

        $location->update([
            ...$request->validated(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Location berhasil diperbarui',
            'data'    => $location,
        ]);
    }

    public function destroy($id)
    {
        $location = MasterLocationModel::findOrFail($id);

        $location->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Location berhasil dihapus',
        ]);
    }
}
