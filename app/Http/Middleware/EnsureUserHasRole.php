<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses route hanya untuk peran tertentu.
 *
 * Contoh pemakaian: ->middleware('role:admin') atau 'role:admin,provider'.
 */
class EnsureUserHasRole
{
    /**
     * @param  string  ...$roles  Nilai peran yang diizinkan (lihat App\Enums\Role).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role instanceof Role) {
            abort(403, 'Akun Anda tidak memiliki peran yang valid.');
        }

        $allowed = array_filter(
            array_map(fn (string $role) => Role::tryFrom($role), $roles)
        );

        if ($allowed === []) {
            // Tidak ada peran valid yang diminta: perlakukan sebagai salah konfigurasi.
            abort(500, 'Middleware role dipanggil tanpa peran yang valid.');
        }

        if (! in_array($user->role, $allowed, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
