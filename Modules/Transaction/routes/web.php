<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Transaction Routes (AUTH)
|--------------------------------------------------------------------------
|
| Checkout, pembayaran (simulasi internal), dan riwayat transaksi.
| Seluruh route di sini membutuhkan pengguna yang sudah login dan terverifikasi.
|
| Komponen dirujuk memakai nama bernamespace (transaction::...) yang
| didaftarkan pada App\Providers\ModuleLivewireServiceProvider.
|
*/

Route::middleware(['auth', 'verified'])->name('transaction.')->group(function () {
    Route::livewire('checkout/{plan}', 'transaction::checkout')->name('checkout');
    Route::livewire('transaksi', 'transaction::transaction-history')->name('index');
    Route::livewire('transaksi/{order}', 'transaction::transaction-detail')->name('show');

    // Struk digital: halaman cetak untuk pesanan yang sudah berhasil dibayar.
    Route::livewire('transaksi/{order}/struk', 'transaction::receipt')->name('receipt');
});
