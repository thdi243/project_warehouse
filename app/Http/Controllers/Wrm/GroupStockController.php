<?php

namespace App\Http\Controllers\Wrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\GroupStockRequest;
use App\Models\Wrm\GroupStockModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupStockController extends Controller
{
    public function index()
    {
        return view('master.wrm.group_stock');
    }

    public function store(GroupStockRequest $request)
    {
        $stock = GroupStockModel::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Group stock berhasil disimpan',
            'data'    => $stock,
        ]);
    }

    public function getData()
    {
        $groupStock = GroupStockModel::all();

        return response()->json([
            'status' => true,
            'data' => $groupStock
        ]);
    }

    public function update(GroupStockRequest $request, $id)
    {
        $barang = GroupStockModel::findOrFail($id);

        $barang->update([
            ...$request->validated(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Group stock berhasil diperbarui',
            'data'    => $barang,
        ]);
    }

    public function destroy($id)
    {
        $barang = GroupStockModel::findOrFail($id);

        $barang->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Group stock berhasil dihapus',
        ]);
    }
}
