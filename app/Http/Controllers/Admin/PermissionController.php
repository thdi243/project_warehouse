<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permissions');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|unique:permissions,name|max:100',
            'section'     => 'nullable|max:100',
            'description' => 'nullable|max:255',
        ]);

        Permission::create($request->only('name', 'section', 'description'));

        return response()->json([
            'status' => 'success',
            'message' => 'Permission berhasil ditambahkan!'
        ]);
    }

    public function data(Request $request)
    {
        $perPage = $request->input('per_page', 15); // default 5 seperti kamu set
        $page = $request->input('page', 1);

        $permissions = Permission::latest()->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $permissions->items(),              // array permission
            'current_page' => $permissions->currentPage(),
            'last_page' => $permissions->lastPage(),
            'per_page' => $permissions->perPage(),
            'total' => $permissions->total(),
            'links' => $permissions->links('pagination::bootstrap-5')->toHtml(), // <-- INI KUNCI! Render jadi HTML string
        ]);
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);

        return response()->json($permission);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name'        => 'required|max:100|unique:permissions,name,' . $permission->id,
            'section'     => 'required|max:100',
            'description' => 'nullable|max:255',
        ]);

        $permission->update($request->only('name', 'section', 'description'));

        return response()->json([
            'status' => 'success',
            'message' => 'Permission diperbarui!'
        ]);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Permission dihapus!'
        ]);
    }
}
