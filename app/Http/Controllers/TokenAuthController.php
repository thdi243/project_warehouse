<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TokenAuthController extends Controller
{
    /**
     * Menerima token dari Portal Utama
     */
    public function receiveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'user_data' => 'required|array',
            'user_data.id' => 'required',
            'user_data.username' => 'required',
            'user_data.email' => 'required|email',
            'expires_at' => 'required|date',
        ]);

        $token = $request->input('token');
        $userData = $request->input('user_data');
        $expiresAt = $request->input('expires_at');

        // Cek apakah token sudah expired
        if (now()->isAfter($expiresAt)) {
            return response()->json([
                'success' => false,
                'message' => 'Token sudah kadaluarsa',
            ], 401);
        }

        $sessionToken = Str::random(32);

        cache()->put(
            "portal_token:{$sessionToken}",
            [
                'original_token' => $token,
                'user_data' => $userData,
            ],
            now()->addMinutes(5)
        );

        // Generate URL redirect dengan session token
        $redirectUrl = route('auth.token-login', ['session_token' => $sessionToken]);

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'session_token' => $sessionToken,
        ]);
    }

    /**
     * Login user menggunakan session token
     */
    public function loginWithToken(Request $request)
    {
        $sessionToken = $request->query('session_token');

        if (!$sessionToken) {
            return redirect()->route('login')
                ->with('error', 'Token tidak valid');
        }

        // Ambil data dari cache
        $tokenData = cache()->get("portal_token:{$sessionToken}");

        if (!$tokenData) {
            return redirect()->route('login')
                ->with('error', 'Token sudah kadaluarsa atau tidak valid');
        }

        $userData = $tokenData['user_data'];

        // Cari atau buat user berdasarkan data dari portal utama
        $user = User::where('email', $userData['email'])->first();

        if (!$user) {
            $user = User::create([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'nama_lengkap' => $userData['name'] ?? $userData['username'],
                'nik' => $userData['nik'] ?? null,
                'jabatan' => $userData['jabatan'] ?? null,
                'departemen' => $userData['departemen'] ?? null,
                'bagian' => $userData['bagian'] ?? null,
                'password' => Hash::make(Str::random(32)),
                'image' => null,
            ]);
        } else {
            $user->update([
                'nama_lengkap' => $userData['name'] ?? $user->name,
                'nik' => $userData['nik'] ?? $user->nik,
                'jabatan' => $userData['jabatan'] ?? $user->jabatan,
                'departemen' => $userData['departemen'] ?? $user->departemen,
                'bagian' => $userData['bagian'] ?? $user->bagian,
            ]);
        }

        // Login user
        Auth::login($user);
        $request->session()->regenerate();

        // Hapus token dari cache
        cache()->forget("portal_token:{$sessionToken}");

        return redirect()->route('dashboard')
            ->with('success', 'Login berhasil dari Portal Utama');
    }
}
