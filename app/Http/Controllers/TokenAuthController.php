<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TokenAuthController extends Controller
{
    /**
     * SSO Callback: Menerima redirect dari Main-BAS dengan ?token=xxx
     */
    public function callback(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            Log::warning('SSO: Callback tanpa token');
            return redirect()->route('login')->with('error', 'Token tidak ditemukan');
        }

        // 1. Verifikasi token ke Main-BAS (PULL VALIDATION)
        // $mainBasUrl = env('MAIN_BAS_URL', 'http://localhost:8000');
        $mainBasUrl = env('MAIN_BAS_URL', 'http://10.11.10.130:8097');
        $secret = env('SSO_SECRET_KEY', 'BAS_SSO_SECRET_2025');

        try {
            $response = Http::withHeaders([
                'X-SSO-Secret' => $secret,
            ])->post(rtrim($mainBasUrl, '/') . '/api/sso/verify', [
                'token' => $token,
            ]);

            if (!$response->successful()) {
                Log::error('SSO: Verifikasi token gagal', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return redirect()->route('login')->with('error', 'Validasi SSO gagal');
            }

            $data = $response->json();

            if (!isset($data['success']) || !$data['success']) {
                return redirect()->route('login')->with('error', 'Token SSO tidak valid');
            }

            $userData = $data['user_data'];

            // 2. Cari atau buat user berdasarkan data dari Main-BAS
            // Cari berdasarkan username atau email untuk menghindari duplikasi (karena email bersifat unique di Warehouse)
            $user = User::where('username', $userData['username'])
                ->orWhere('email', $userData['email'])
                ->first();

            if (!$user) {
                // Auto-create user jika belum ada sama sekali
                $user = User::create([
                    'username' => $userData['username'],
                    'email' => $userData['email'] ?? null,
                    'nama_lengkap' => $userData['nama_lengkap'] ?? $userData['username'],
                    'nik' => $userData['nik'] ?? null,
                    'jabatan' => $userData['jabatan'] ?? null,
                    'departemen' => $userData['departemen'] ?? null,
                    'bagian' => $userData['bagian'] ?? null,
                    'password' => Hash::make(Str::random(32)),
                ]);
            } else {
                // Update data user dari portal utama agar sinkron
                // Jika user ditemukan via email tapi username berbeda, update username-nya juga
                $user->update([
                    'username' => $userData['username'],
                    'email' => $userData['email'] ?? $user->email,
                    'nama_lengkap' => $userData['nama_lengkap'] ?? $user->nama_lengkap,
                    'nik' => $userData['nik'] ?? $user->nik,
                    'jabatan' => $userData['jabatan'] ?? $user->jabatan,
                    'departemen' => $userData['departemen'] ?? $user->departemen,
                    'bagian' => $userData['bagian'] ?? $user->bagian,
                ]);
            }

            // 3. Login user
            Auth::login($user);
            $request->session()->regenerate();

            Log::info("SSO: Login sukses untuk user [{$user->username}] via SSO");

            // Cek apakah ada redirect path tujuan
            $next = $request->query('next');
            if ($next) {
                // Jika $next adalah URL lengkap, langsung redirect
                if (filter_var($next, FILTER_VALIDATE_URL)) {
                    return redirect()->away($next)->with('success', 'Login berhasil melalui SSO');
                }

                // Jika $next adalah path (misal: purchase-requesition/approval), 
                // pastikan ada slash di depan agar dianggap absolute path dari root
                $targetPath = '/' . ltrim($next, '/');
                return redirect($targetPath)->with('success', 'Login berhasil melalui SSO');
            }

            return redirect()->route('dashboard')
                ->with('success', 'Login berhasil melalui SSO');
        } catch (\Exception $e) {
            Log::error('SSO: Error koneksi ke Main-BAS', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('login')->with('error', 'Komunikasi SSO bermasalah');
        }
    }
}
