<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (ROLE: admin)
|--------------------------------------------------------------------------
|
| Panel pengelola: dashboard metrik nyata dan CRUD seluruh master data PRIM.
| Dilindungi middleware 'role:admin' selain auth.
|
*/

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', fn () => 'Dashboard admin — segera')->name('dashboard');

        Route::get('provider', fn () => 'Kelola provider — segera')->name('providers.index');
        Route::get('kategori', fn () => 'Kelola kategori — segera')->name('categories.index');
        Route::get('layanan', fn () => 'Kelola layanan — segera')->name('services.index');
        Route::get('plan', fn () => 'Kelola plan — segera')->name('plans.index');
        Route::get('pengguna', fn () => 'Kelola pengguna — segera')->name('users.index');
        Route::get('transaksi', fn () => 'Kelola transaksi — segera')->name('transactions.index');
    });
