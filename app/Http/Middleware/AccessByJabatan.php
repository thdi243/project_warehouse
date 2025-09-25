<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessByJabatan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$allowedBagians)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // kalau jabatannya foreman, spv, dept_head → akses semua
        if (in_array($user->jabatan, ['foreman', 'supervisor', 'dept_head'])) {
            return $next($request);
        }

        // kalau operator → cek bagian
        if ($user->jabatan === 'operator') {
            if (in_array($user->bagian, $allowedBagians)) {
                return $next($request);
            } else {
                abort(403, 'Unauthorized bagian');
            }
        }

        // default tolak
        abort(403, 'Unauthorized');
    }
}
