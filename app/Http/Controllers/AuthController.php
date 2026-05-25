<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    private $redirects = [
        'dept_head' => '/dashboard',
        'supervisor' => '/dashboard',
        'foreman' => '/dashboard',
        'operator' => '/dashboard',
        'admin' => '/dashboard',
    ];

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect($this->redirectUser(Auth::user()));
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->username;
        $password = $request->password;

        $field = is_numeric($login) ? 'nik' : 'username';

        if (Auth::attempt([
            $field => $login,
            'password' => $password
        ])) {
            $request->session()->regenerate();
            $user = Auth::user();

            $imageUrl = $user->image && url(Storage::disk('public')->exists($user->image))
                ? url(Storage::url($user->image)) // -> /storage/...
                : asset('material/assets/images/users/user-dummy-img.jpg');

            $intended = session('url.intended', $this->redirectUser($user));

            // Hapus dari session biar tidak dipakai lagi
            session()->forget('url.intended');

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => $intended,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Login gagal. Periksa username atau password Anda.',
        ], 401);
    }

    public function logout(Request $request)
    {
        // Hapus session yang ada
        Auth::logout();

        // Hapus token CSRF jika menggunakan token untuk API
        $request->session()->invalidate();
        $request->session()->flush();
        Cookie::forget('username');
        // Menghancurkan semua session yang tersimpan
        $request->session()->regenerateToken();

        // Menghapus semua cookies yang terkait dengan aplikasi
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    private function redirectUser($user)
    {
        $jabatan = strtolower($user->jabatan);
        $bagian = strtolower(trim($user->departemen));

        // Jika bukan dari departemen warehouse
        // if ($bagian !== 'warehouse') {
        //     return url('/app/stock-on-hand');
        // }

        // Jika warehouse, gunakan redirect berdasarkan jabatan
        $path = $this->redirects[$jabatan] ?? '/';

        return url($path);
    }
}
