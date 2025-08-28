<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('user.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $data = User::select('id', 'username', 'email', 'nik', 'jabatan', 'departemen', 'bagian', 'image')->get();

            $data = $data->map(function ($user) {
                $imageName = trim($user->image ?? '', '/');

                if ($imageName && !str_starts_with($imageName, 'images/users/')) {
                    $imagePath = 'images/users/' . $imageName;
                } else {
                    $imagePath = $imageName;
                }

                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    $user->image_url = Storage::url($imagePath);
                } else {
                    $user->image_url = asset('material/assets/images/users/user-dummy-img.jpg');
                }

                return $user;
            });

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditemukan',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error('User create error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data user'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'email' => 'required|email',
            'jabatan' => 'required',
            'nik' => 'required',
            'bagian' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('/images/users', 'public');
        }

        User::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'email' => $request->email,
            'nik' => $request->nik,
            'jabatan' => $request->jabatan,
            'departemen' => 'warehouse',
            'bagian' => $request->bagian,
            'image' => $imagePath,
        ]);

        return response()->json(['success' => 'User created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'ok' => true,
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'username' => 'required|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => ['nullable', 'min:6'],
            'jabatan'  => ['required'],
            'nik'      => ['required'],
            'bagian'   => ['required'],
            'departemen' => ['nullable'],
            'image'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        $user = User::findOrFail($id);

        try {
            // Siapkan data update
            $data = [
                'username'   => $request->username,
                'email'      => $request->email,
                'nik'        => $request->nik,
                'jabatan'    => $request->jabatan,
                'departemen' => $request->input('departemen', $user->departemen ?? 'warehouse'),
                'bagian'     => $request->bagian,
            ];

            // Update password jika diisi
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }

            // Handle file upload
            if ($request->hasFile('image')) {
                // Hapus file lama jika ada
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }

                $path = $request->file('image')->store('images/users', 'public');
                $data['image'] = $path;
            }

            $user->update($data);

            return response()->json([
                'ok'      => true,
                'message' => 'User berhasil diupdate',
                'data'    => $user->fresh(), // Get updated data
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('User update error: ' . $e->getMessage(), [
                'id' => $id,
                'request_data' => $request->except(['password', 'image'])
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['ok' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        try {
            $deletedFile = false;

            if ($user->image) {
                // Normalisasi path: pastikan tidak mengandung leading '/storage' atau 'public/'
                $path = $user->image;
                $path = preg_replace('#^/+#', '', $path);
                $path = preg_replace('#^storage/#', '', $path);
                $path = preg_replace('#^public/#', '', $path);

                // Jika value disimpan seperti 'images/users/...' maka ini ok
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                    $deletedFile = true;
                    // Log::info("Deleted user image from storage: {$path}", ['user_id' => $user->id]);
                } else {
                    Log::warning("User image not found on disk when deleting", ['user_id' => $user->id, 'tried_path' => $path]);
                }
            }

            $user->delete();

            return response()->json([
                'ok' => true,
                'message' => 'Data berhasil dihapus',
                'file_deleted' => $deletedFile,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
