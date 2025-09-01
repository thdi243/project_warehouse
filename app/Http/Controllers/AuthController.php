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
        'dept_head' => '/dashboard/tkbm',
        'supervisor' => '/dashboard/tkbm',
        'foreman' => '/dashboard/tkbm',
        'operator' => '/dashboard/tkbm',
    ];

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {

        if (Auth::check()) {
            return response()->json([
                'success' => true,
                'message' => 'Anda sudah login.',
                'redirect' => $this->redirectUser(Auth::user()),
            ]);
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $imageUrl = $user->image && url(Storage::disk('public')->exists($user->image))
                ? url(Storage::url($user->image)) // -> /storage/...
                : asset('material/assets/images/users/user-dummy-img.jpg');

            Session::put('username', $user->username);
            Session::put('user_id', $user->id);
            Session::put('jabatan', $user->jabatan);
            Session::put('bagian', $user->bagian);
            Session::put('image_url', $imageUrl);
            Cookie::queue('username', $user->username, 60);

            Log::info('Username saved in session: ' . Session::get('username'));

            $redirectUrl = $this->redirectUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => $redirectUrl,
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

        $path = $this->redirects[$jabatan] ?? '/';

        return url($path);
    }
}
