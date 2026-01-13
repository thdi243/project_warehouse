<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Permission\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class UserPermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all(); // untuk modal

        return view('admin.userPermissions', compact('permissions'));
    }

    public function getUsersData(Request $request)
    {
        $query = $request->input('query', '');
        $page = $request->input('page', 1);

        $users = User::query()
            ->when($query, function ($q) use ($query) {
                $q->where('nama_lengkap', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('nik', 'like', "%{$query}%");
            })
            ->with('permissions:name')  // <-- KUNCI! Eager load permissions (hanya kolom name)
            ->latest()
            ->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'users' => collect($users->items())->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nama_lengkap' => $user->nama_lengkap,
                    'username' => $user->username,
                    'nik' => $user->nik ?? null, // kalau ada kolom NIK
                    'jabatan' => $user->jabatan,
                    'permissions' => $user->permissions->pluck('name')->toArray(), // array nama permission saja
                ];
            }),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
            'links' => $users->links('pagination::bootstrap-5')->toHtml(),
        ]);
    }

    // Endpoint AJAX untuk load detail permission user (untuk modal)
    public function getUserPermissions($id)
    {
        $user = User::findOrFail($id);
        $permissions = Permission::all();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->nama_lengkap ?? $user->username,
                'email' => $user->email,
                'jabatan' => $user->jabatan,
            ],
            'permissions' => $permissions->map(function ($perm) use ($user) {
                return [
                    'id' => $perm->id,
                    'name' => $perm->name,
                    'description' => $perm->description ?? 'no desc',
                    'checked' => $user->permissions->contains($perm->id),
                ];
            }),
        ]);
    }

    // Update via AJAX
    public function update(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user->permissions()->sync($request->input('permissions', []));

        return response()->json([
            'success' => true,
            'message' => 'Permission user berhasil diupdate!',
            'user_permissions' => $user->permissions->pluck('name')->implode(', ') ?: 'Tidak ada',
        ]);
    }
}
