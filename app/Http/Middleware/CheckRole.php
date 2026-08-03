<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $userRole = strtolower(trim($user->role));
        if (in_array($userRole, ['operator', 'worker'])) {
            $userRole = 'karyawan';
        }
        if ($userRole === 'administrator') {
            $userRole = 'admin';
        }

        $normalizedRoles = array_map(function ($r) {
            $r = strtolower(trim($r));
            if (in_array($r, ['operator', 'worker'])) return 'karyawan';
            if ($r === 'administrator') return 'admin';
            return $r;
        }, $roles);

        if (! in_array($userRole, $normalizedRoles)) {
            abort(403, 'Akses tidak diizinkan untuk peran Anda.');
        }

        return $next($request);
    }
}
