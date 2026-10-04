<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Bantuan (PUBLIK)
|--------------------------------------------------------------------------
|
| Halaman "Laporan Kendala" mengikuti frame Figma: pengguna diarahkan ke
| kanal WhatsApp resmi PRIM dengan pesan pembuka yang sudah disiapkan.
|
*/

Route::view('laporan-kendala', 'support.report')->name('support.report');
