<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Catalog Routes (PUBLIK)
|--------------------------------------------------------------------------
|
| Katalog layanan premium PRIM dapat diakses tanpa login, sesuai keputusan
| desain: pengunjung boleh mencari, memfilter, dan membandingkan layanan.
| Login baru diwajibkan saat masuk proses pembelian (modul Transaction).
|
*/

Route::name('catalog.')->group(function () {
    Route::view('/', 'welcome')->name('home');

    Route::get('katalog', fn () => 'Katalog — segera')->name('index');
    Route::get('katalog/{service:slug}', fn (string $service) => "Detail: {$service}")->name('show');
    Route::get('bandingkan', fn () => 'Komparasi — segera')->name('compare');
});
