<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions', 'users')->get();
        $permissions = Permission::all()->groupBy('section');
        return view('admin.roles', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'display_name' => 'required',
        ]);

        Role::create($request->all());

        return response()->json(['status' => true, 'message' => 'Role created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'display_name' => 'required',
        ]);

        $role = Role::findOrFail($id);
        $role->update($request->all());

        return response()->json(['status' => true, 'message' => 'Role updated successfully.']);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(['status' => true, 'message' => 'Role deleted successfully.']);
    }

    public function getRolePermissions($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json([
            'status' => true,
            'permissions' => $role->permissions->pluck('id')
        ]);
    }

    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->permissions()->sync($request->permissions);

        return response()->json(['status' => true, 'message' => 'Permissions assigned successfully.']);
    }

    // New: Dedicated User Roles Index
    public function userRolesIndex()
    {
        $rolesQuery = Role::query();
        
        if (auth()->user()->jabatan !== 'admin') {
            $rolesQuery->where('name', '!=', 'super-admin');
        }
        
        $roles = $rolesQuery->get();
        return view('admin.user_roles', compact('roles'));
    }

    public function getUsersData(Request $request)
    {
        $users = User::with('roles')
            ->select('id', 'nama_lengkap', 'username', 'jabatan', 'bagian');

        if ($request->search['value']) {
            $search = $request->search['value'];
            $users->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $totalData = $users->count();
        $users = $users->skip($request->start)->take($request->length)->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'nama_lengkap' => $user->nama_lengkap,
                'username' => $user->username,
                'jabatan' => $user->jabatan,
                'bagian' => $user->bagian,
                'roles' => $user->roles->pluck('display_name')->toArray(),
                'role_ids' => $user->roles->pluck('id')->toArray(),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalData,
            'data' => $data
        ]);
    }

    // User Assignment
    public function getUserRoles($userId)
    {
        $user = User::with('roles')->findOrFail($userId);
        
        $roleIds = $user->roles->pluck('id');
        
        // If not admin, remove super-admin from the returned list so it doesn't try to prepopulate the hidden option
        if (auth()->user()->jabatan !== 'admin') {
            $superAdminRole = Role::where('name', 'super-admin')->first();
            if ($superAdminRole) {
                $roleIds = $roleIds->reject(function ($id) use ($superAdminRole) {
                    return $id === $superAdminRole->id;
                })->values();
            }
        }
        
        return response()->json([
            'status' => true,
            'roles' => $roleIds
        ]);
    }

    public function assignUserRoles(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $roleIds = $request->roles ?? [];

        $superAdminRole = Role::where('name', 'super-admin')->first();

        if (auth()->user()->jabatan !== 'admin' && $superAdminRole) {
            // Check if trying to add super-admin
            if (in_array($superAdminRole->id, $roleIds)) {
                return response()->json(['status' => false, 'message' => 'Unauthorized to assign super-admin role.'], 403);
            }

            // Keep super-admin if the user already had it, since it wasn't in the form
            if ($user->roles->contains('id', $superAdminRole->id)) {
                $roleIds[] = $superAdminRole->id;
            }
        }

        $user->roles()->sync($roleIds);

        return response()->json(['status' => true, 'message' => 'Roles assigned successfully.']);
    }
}
