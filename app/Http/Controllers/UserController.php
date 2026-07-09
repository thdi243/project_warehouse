<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = \App\Models\Role::all();
        return view('user.index', compact('roles'));
    }

    public function profileIndex()
    {
        try {
            $user = User::select('id', 'nama_lengkap', 'username', 'email', 'nik', 'jabatan', 'departemen', 'bagian', 'image')
                ->findOrFail(Auth::id());

            // Proses image_url sama seperti logic Anda
            $imageName = trim($user->image ?? '', '/');

            if ($imageName && !str_starts_with($imageName, 'images/users/')) {
                $imagePath = 'images/users/' . $imageName;
            } else {
                $imagePath = $imageName;
            }

            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                $user->image_url = url(Storage::url($imagePath));
            } else {
                $user->image_url = url("material/assets/images/users/user-dummy-img.jpg");
            }

            return view('user.profile', compact('user'));
        } catch (\Exception $e) {
            Log::error('Profile index error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data profile');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $data = User::select('id', 'nama_lengkap', 'username', 'email', 'nik', 'jabatan', 'departemen', 'bagian', 'image', 'is_active')->get();

            $data = $data->map(function ($user) {
                $imageName = trim($user->image ?? '', '/');

                if ($imageName && !str_starts_with($imageName, 'images/users/')) {
                    $imagePath = 'images/users/' . $imageName;
                } else {
                    $imagePath = $imageName;
                }

                if ($imagePath && url(Storage::disk('public')->exists($imagePath))) {
                    $user->image_url = url(Storage::url($imagePath));
                } else {
                    $user->image_url = url("material/assets/images/users/user-dummy-img.jpg");
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
            'nama_lengkap' => 'nullable|string|max:255',
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'email' => 'required|email',
            'jabatan' => 'required',
            'nik' => 'required',
            'departemen' => 'required',
            'bagian' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'principal' => 'nullable|string|max:255',
            'signature' => 'nullable|string',
        ]);

        // === Upload Foto (jika ada) ===
        // $imagePath = null;
        // if ($request->hasFile('image')) {
        //     $imagePath = $request->file('image')->store('/images/users', 'public');
        // }

        // === Upload Foto (jika ada) ===
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $identifier = Str::slug($request->username);
            $uniqueSuffix = Str::random(8);
            $imageName = "profile_{$identifier}_{$uniqueSuffix}." . $file->getClientOriginalExtension();

            $relativePath = "images/users/{$imageName}";

            Storage::disk('public')->putFileAs('images/users', $file, $imageName);

            $imagePath = $relativePath;
        }

        // === Simpan User ===
        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'email' => $request->email,
            'nik' => $request->nik,
            'jabatan' => $request->jabatan,
            'departemen' => $request->departemen,
            'bagian' => $request->bagian,
            'image' => $imagePath,
        ]);

        // === Simpan Principal (jika tidak kosong) ===
        if (!empty($request->principal)) {
            $user->principal()->create([
                'principal' => strtoupper($request->principal),
            ]);
        }

        // === Simpan Signature (tanda tangan digital, jika tidak kosong) ===
        if (!empty($request->signature)) {
            $signatureData = $request->input('signature');

            // Decode base64 image
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $signatureData);
            $imageData = str_replace(' ', '+', $imageData);

            $binaryData = base64_decode($imageData);

            if ($binaryData === false) {
                // Optional: handle error decode
                return response()->json(['error' => 'Gagal decode gambar signature'], 422);
            }

            // Buat nama file yang aman dan unik (hindari overwrite kalau user ganti ttd berkali-kali)
            $identifier = Str::slug($user->username);
            $uniqueSuffix = Str::random(8);
            $signatureName = "signature_{$identifier}_{$uniqueSuffix}.png";

            // Simpan ke storage disk 'public' → storage/app/public/uploads/signatures
            $relativePath = "uploads/signatures/{$signatureName}";

            Storage::disk('public')->put($relativePath, $binaryData);

            // Optional: hapus signature lama kalau ada (biar tidak numpuk file)
            if ($user->signature && $user->signature->signature) {
                Storage::disk('public')->delete($user->signature->signature);
            }

            // Simpan path relatif ke database (tanpa 'storage/')
            $user->signature()->updateOrCreate(
                ['user_id' => $user->id],
                ['signature' => $relativePath]
            );
        }

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
            $user = User::with('principal', 'signature')->findOrFail($id);

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
            'nama_lengkap' => 'required|string|max:255',
            'username'   => 'required|unique:users,username,' . $id,
            'email'      => 'required|email|unique:users,email,' . $id,
            'password'   => 'nullable|min:6',
            'jabatan'    => 'required',
            'nik'        => 'required',
            'bagian'     => 'required',
            'departemen' => 'nullable|string',
            'principal'  => 'nullable|string|max:255',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'signature'  => 'nullable|string',
        ]);
        // dd($request['signature']);
        $user = User::findOrFail($id);

        if (!$this->canModify(Auth::user(), $user)) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda tidak memiliki izin.',
            ], 403);
        }

        try {
            // === Siapkan data dasar untuk update ===
            $data = [
                'nama_lengkap'   => $request->nama_lengkap,
                'username'   => $request->username,
                'email'      => $request->email,
                'nik'        => $request->nik,
                'jabatan'    => $request->jabatan,
                'departemen' => $request->input('departemen', $user->departemen ?? 'warehouse'),
                'bagian'     => $request->bagian,
            ];

            // === Update password jika diisi ===
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }

            // === Update foto profil jika ada file baru ===
            // if ($request->hasFile('image')) {
            //     if ($user->image && Storage::disk('public')->exists($user->image)) {
            //         Storage::disk('public')->delete($user->image);
            //     }

            //     $path = $request->file('image')->store('images/users', 'public');
            //     $data['image'] = $path;
            // }

            // === Update foto profil jika ada file baru ===
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                // Hapus file lama kalau ada
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }

                // Buat nama baru yang konsisten + unik
                $identifier = Str::slug($user->username);
                $uniqueSuffix = Str::random(8);
                $imageName = "profile_{$identifier}_{$uniqueSuffix}." . $file->getClientOriginalExtension();

                $relativePath = "images/users/{$imageName}";

                // Simpan file baru
                Storage::disk('public')->putFileAs('images/users', $file, $imageName);

                $data['image'] = $relativePath;
            }

            // === Simpan data user utama ===
            $user->update($data);

            // === Update principal hanya jika ada input valid ===
            $oldPrincipal = optional($user->principal)->principal;
            $newPrincipal = trim($request->principal);

            // Jika principal kosong → jangan update apa pun
            if ($newPrincipal !== '') {
                $newPrincipal = strtoupper($newPrincipal);

                // Update hanya jika beda dari sebelumnya
                if ($newPrincipal !== $oldPrincipal) {
                    $user->principal()->updateOrCreate(
                        ['user_id' => $user->id],
                        ['principal' => $newPrincipal]
                    );
                }
            }

            // === Update atau buat tanda tangan (jika ada input) ===
            if ($request->filled('signature') && trim($request->signature) !== '') {
                $signatureData = $request->input('signature');

                // Decode base64 image
                $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $signatureData);
                $imageData = str_replace(' ', '+', $imageData);

                $binaryData = base64_decode($imageData);

                if ($binaryData === false) {
                    // Optional: handle error decode
                    return response()->json(['message' => 'Gagal decode data signature'], 422);
                }

                // Buat nama file aman + unik (hindari overwrite kalau user upload berkali-kali)
                $identifier = Str::slug($user->username); // aman untuk nama file
                $uniqueSuffix = Str::random(8);
                $signatureName = "signature_{$identifier}_{$uniqueSuffix}.png";

                // Path relatif yang akan disimpan di DB
                $relativePath = "uploads/signatures/{$signatureName}";

                // Simpan ke storage disk 'public'
                Storage::disk('public')->put($relativePath, $binaryData);

                // Hapus signature lama kalau ada (biar tidak numpuk file sampah)
                if ($user->signature && $user->signature->signature) {
                    Storage::disk('public')->delete($user->signature->signature);
                }

                // Update atau create record signature
                $user->signature()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['signature' => $relativePath]
                );
            } elseif ($request->has('signature') && trim($request->signature) === '') {
                // User menghapus signature (kirim string kosong)
                if ($user->signature && $user->signature->signature) {
                    // Hapus file fisik dari storage
                    Storage::disk('public')->delete($user->signature->signature);

                    // Hapus record di database
                    $user->signature()->delete();
                }
            }

            return response()->json([
                'ok'      => true,
                'message' => 'User berhasil diupdate',
                'data'    => $user->fresh()
            ], 200);
        } catch (\Exception $e) {
            Log::error('User update error: ' . $e->getMessage(), [
                'id' => $id,
                'request_data' => $request->except(['password', 'image', 'signature'])
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage(),
            ], 500);
        }
    }


    private function canModify($currentUser, $targetUser)
    {
        $levels = [
            'operator' => 1,
            'foreman' => 2,
            'supervisor' => 3,
            'dept_head' => 4,
            'admin' => 5,
        ];

        $current = $levels[$currentUser->jabatan] ?? 0;
        $target  = $levels[$targetUser->jabatan] ?? 0;

        if ($current == 5) return true;
        if ($currentUser->id == $targetUser->id) return true;

        return $current > $target;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        try {
            $deletedFile = false;

            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
                $deletedFile = true;
            }

            if ($user->principal) {
                $user->principal()->delete();
            }

            if ($user->signature) {
                $user->signature()->delete();
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

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if (!$this->canModify(Auth::user(), $user)) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda tidak memiliki izin.',
            ], 403);
        }

        if ($user->id === Auth::id()) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
            ], 400);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'ok' => true,
            'message' => 'Status user berhasil diperbarui.',
            'is_active' => $user->is_active
        ]);
    }
}
