<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Bantuan Pengembangan — SEMENTARA
|--------------------------------------------------------------------------
|
| Dipakai oleh tools/screenshot.mjs untuk membuka halaman yang memerlukan login
| saat menyiapkan gambar rancangan antarmuka. Berkas ini hanya dimuat ketika
| APP_ENV=local dan dilindungi token acak per proses, lalu dihapus kembali
| setelah pengambilan gambar selesai.
|
| JANGAN dipakai pada lingkungan produksi.
|
*/

Route::get('/_dev/login', function (Request $request) {
    $expected = (string) env('PRIM_SCREENSHOT_TOKEN', '');

    abort_if($expected === '', 404);
    abort_unless(hash_equals($expected, (string) $request->query('token')), 403, 'Token tidak cocok.');

    $email = (string) $request->query('email');

    $user = User::query()->where('email', $email)->first();

    abort_if($user === null, 404, 'Pengguna tidak ditemukan.');
    abort_unless($user->canSignIn(), 403, 'Akun tidak aktif.');

    Auth::guard('web')->login($user);
    $request->session()->regenerate();

    $user->forceFill(['last_login_at' => now()])->save();

    return response()->json([
        'ok' => true,
        'user' => $user->email,
        'role' => $user->role->value,
    ]);
})->middleware('web');
