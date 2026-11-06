<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses rute berdasarkan pengguna.role (ENUM di basis data).
 * Dipakai sebagai ->middleware('role:admin') atau 'role:admin,petugas'.
 */
class PastikanPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role->value, $peran, true)) {
            abort(403, 'Peran Anda tidak berhak membuka halaman ini.');
        }

        return $next($request);
    }
}
