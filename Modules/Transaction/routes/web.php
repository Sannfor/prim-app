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
*/

Route::middleware(['auth', 'verified'])->name('transaction.')->group(function () {
    Route::get('checkout/{plan}', fn (string $plan) => "Checkout plan: {$plan}")->name('checkout');
    Route::get('transaksi', fn () => 'Riwayat transaksi — segera')->name('index');
    Route::get('transaksi/{order}', fn (string $order) => "Detail transaksi: {$order}")->name('show');
});
