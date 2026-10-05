<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (ROLE: admin)
|--------------------------------------------------------------------------
|
| Struktur menu mengikuti sidebar pada desain Figma:
| Dashboard, Pesanan, Pengguna, Produk, Pembayaran, Laporan, Pengaturan.
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

        // Pesanan & pembayaran.
        Route::livewire('pesanan', 'admin::transaction-manager')->name('transactions.index');
        Route::livewire('pembayaran', 'admin::payment-manager')->name('payments.index');

        // Pengguna.
        Route::livewire('pengguna', 'admin::user-manager')->name('users.index');

        // Produk (layanan) beserta paketnya.
        Route::livewire('produk', 'admin::service-manager')->name('services.index');
        Route::livewire('produk/{service}/paket', 'admin::plan-manager')->name('plans.index');

        // Master data pendukung.
        Route::livewire('kategori', 'admin::category-manager')->name('categories.index');
        Route::livewire('penyedia', 'admin::provider-manager')->name('providers.index');

        // Kode promo.
        Route::livewire('voucher', 'admin::voucher-manager')->name('vouchers.index');

        // Laporan & pengaturan.
        Route::livewire('laporan', 'admin::report')->name('reports.index');
        Route::livewire('pengaturan/profil', 'admin::settings-profile')->name('settings.profile');
        Route::livewire('pengaturan/notifikasi', 'admin::settings-notification')->name('settings.notifications');

        // Tautan lama dipertahankan agar rute yang sudah dipakai tidak putus.
        Route::livewire('transaksi', 'admin::transaction-manager')->name('transactions.legacy');
        Route::livewire('layanan', 'admin::service-manager')->name('services.legacy');
        Route::livewire('layanan/{service}/paket', 'admin::plan-manager')->name('plans.legacy');
    });
