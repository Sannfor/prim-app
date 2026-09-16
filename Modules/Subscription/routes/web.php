<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Subscription Routes (AUTH)
|--------------------------------------------------------------------------
|
| Daftar langganan aktif, riwayat masa aktif, perpanjangan, dan pembatalan
| perpanjangan otomatis.
|
*/

Route::middleware(['auth', 'verified'])->name('subscription.')->group(function () {
    Route::get('langganan', fn () => 'Langganan — segera')->name('index');
});
