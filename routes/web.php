<?php

use App\Livewire\Actions\Logout;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| PRIM — Route Utama
|--------------------------------------------------------------------------
|
| Route publik (katalog & bantuan) didaftarkan oleh modulnya masing-masing.
| Berkas ini menangani beranda, pusat pesanan pengguna, dan pengaturan akun.
|
*/

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('profil/pesanan', 'profile.orders')->name('profile.orders');
    Volt::route('profil/kode-login', 'profile.login-code')->name('profile.login-code');

    // Rute /dashboard dari starter kit dipertahankan sebagai nama rute agar
    // tautan lama tidak putus, tetapi diarahkan ke pusat pesanan pengguna.
    Route::redirect('dashboard', 'profil/pesanan')->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
require __DIR__.'/support.php';

// Rute bantuan untuk pengambilan gambar rancangan antarmuka. Hanya dimuat pada
// lingkungan lokal; berkasnya dapat dihapus tanpa memengaruhi aplikasi.
if (app()->environment('local')) {
    require __DIR__.'/dev-screenshot.php';
}
