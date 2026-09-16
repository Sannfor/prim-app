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
| Komponen dirujuk memakai nama bernamespace (catalog::...) yang didaftarkan
| pada App\Providers\ModuleLivewireServiceProvider.
|
*/

Route::name('catalog.')->group(function () {
    Route::livewire('katalog', 'catalog::service-list')->name('index');
    Route::livewire('katalog/{service}', 'catalog::service-detail')->name('show');
    Route::livewire('bandingkan', 'catalog::comparison')->name('compare');
});
