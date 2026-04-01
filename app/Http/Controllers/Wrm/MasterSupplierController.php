<?php

namespace App\Http\Controllers\Wrm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wrm\MasterSupplierRequest;
use App\Models\Wrm\MasterSupplierModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterSupplierController extends Controller
{
    public function index()
    {
        return view('master.wrm.master_supplier');
    }

    public function getData()
    {
        $suppliers = MasterSupplierModel::orderBy('nama')
            ->with('createdBy:id,nama_lengkap', 'updatedBy:id,nama_lengkap')
            ->paginate(100);

        return response()->json([
            'status' => true,
            'data'   => $suppliers
        ]);
    }

    public function getAll()
    {
        $suppliers = MasterSupplierModel::orderBy('nama')->get();

        return response()->json([
            'status' => true,
            'data'   => $suppliers
        ]);
    }

    public function store(MasterSupplierRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $supplier = MasterSupplierModel::create($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Supplier berhasil ditambahkan',
            'data'    => $supplier
        ]);
    }

    public function update(MasterSupplierRequest $request, $id)
    {
        $supplier = MasterSupplierModel::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();

        $supplier->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Supplier berhasil diperbarui',
            'data'    => $supplier
        ]);
    }

    public function destroy($id)
    {
        $supplier = MasterSupplierModel::findOrFail($id);
        $supplier->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Supplier berhasil dihapus',
        ]);
    }
}
