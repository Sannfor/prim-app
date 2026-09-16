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
| Komponen dirujuk memakai nama bernamespace (admin::...) yang didaftarkan
| pada App\Providers\ModuleLivewireServiceProvider.
|
*/

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::livewire('/', 'admin::dashboard')->name('dashboard');

        Route::livewire('provider', 'admin::provider-manager')->name('providers.index');
        Route::livewire('kategori', 'admin::category-manager')->name('categories.index');
        Route::livewire('layanan', 'admin::service-manager')->name('services.index');
        Route::livewire('layanan/{service}/paket', 'admin::plan-manager')->name('plans.index');
        Route::livewire('pengguna', 'admin::user-manager')->name('users.index');
        Route::livewire('transaksi', 'admin::transaction-manager')->name('transactions.index');
    });
