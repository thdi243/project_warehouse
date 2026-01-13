<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Login dulu bro.');
        }

        // Bypass kalau punya permission 'super-admin'
        if ($user->hasPermission('super-admin')) {
            return $next($request); // langsung lolos semua check
        }

        // Cek permission biasa
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Kamu ga punya izin untuk akses fitur ini.');
    }
}
