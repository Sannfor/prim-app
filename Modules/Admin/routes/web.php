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

        // Pesanan, langganan, & pembayaran.
        Route::livewire('pesanan', 'admin::transaction-manager')->name('transactions.index');
        Route::livewire('langganan', 'admin::subscription-manager')->name('subscriptions.index');
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

/*
|--------------------------------------------------------------------------
| Provider Routes (ROLE: provider)
|--------------------------------------------------------------------------
|
| Panel untuk akun berperan penyedia layanan. Cakupannya sengaja sempit:
| penyedia hanya melihat dan menyunting layanan serta paket miliknya sendiri,
| sesuai F-17. Kepemilikan ditentukan oleh providers.user_id, bukan oleh
| masukan formulir, sehingga penyedia tidak dapat memindahkan layanan ke
| penyedia lain atau menyunting layanan pihak lain.
|
| Verifikasi pembayaran, pengelolaan pengguna, dan laporan tetap menjadi
| wewenang administrator pada panel di atas.
|
*/

Route::middleware(['auth', 'verified', 'role:provider'])
    ->prefix('penyedia')
    ->name('provider.')
    ->group(function () {
        Route::livewire('/', 'admin::provider.dashboard')->name('dashboard');
        Route::livewire('layanan', 'admin::provider.service-manager')->name('services.index');
        Route::livewire('layanan/{service}/paket', 'admin::provider.plan-manager')->name('plans.index');
    });
