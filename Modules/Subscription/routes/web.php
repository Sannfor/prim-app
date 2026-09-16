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
| Komponen dirujuk memakai nama bernamespace (subscription::...) yang
| didaftarkan pada App\Providers\ModuleLivewireServiceProvider.
|
*/

Route::middleware(['auth', 'verified'])->name('subscription.')->group(function () {
    Route::livewire('langganan', 'subscription::subscription-list')->name('index');
    Route::livewire('langganan/{subscription}/perpanjang', 'subscription::renew-subscription')->name('renew');
});
