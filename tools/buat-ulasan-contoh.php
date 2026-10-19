<?php

/**
 * Pembuat data contoh ulasan untuk pengambilan gambar antarmuka.
 *
 * Gambar "Menulis ulasan dan rating" hanya memperlihatkan formulir bila pembeli
 * contoh memenuhi dua syarat: punya transaksi berhasil atas layanan itu dan
 * belum pernah mengulasnya. Skrip ini:
 *
 *   1. menghapus ulasan milik pembeli contoh pada layanan tersebut, agar
 *      formulir selalu tampil saat gambar diambil ulang;
 *   2. memastikan beberapa ulasan pembeli lain tersedia, agar bagian daftar
 *      ulasan tidak kosong dan gambar memperlihatkan keadaan yang wajar.
 *
 * Jalankan dari dalam folder prim:
 *     php tools/buat-ulasan-contoh.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Review;
use App\Models\Service;
use App\Models\User;

$pembeli = User::query()->where('email', 'andi@prim.test')->firstOrFail();

$layanan = Service::query()->where('slug', 'netflix')->first()
    ?? Service::query()->whereHas('plans')->firstOrFail();

// Bersihkan ulasan pembeli contoh agar formulir ulasan tampil pada gambar.
$dihapus = Review::query()
    ->where('user_id', $pembeli->id)
    ->where('service_id', $layanan->id)
    ->delete();

/*
 * Ulasan pembeli lain sebagai pengisi daftar. Dibuat hanya bila belum ada,
 * supaya jumlahnya tidak bertambah setiap kali skrip dijalankan.
 */
$contoh = [
    ['email' => 'rina@prim.test', 'rating' => 5, 'comment' => 'Akun langsung aktif setelah pembayaran, prosesnya cepat.'],
    ['email' => 'budi@prim.test', 'rating' => 4, 'comment' => 'Harga bersaing dan masa aktifnya sesuai keterangan.'],
];

foreach ($contoh as $baris) {
    $penulis = User::query()->where('email', $baris['email'])->first();

    if ($penulis === null) {
        continue;
    }

    Review::query()->updateOrCreate(
        ['user_id' => $penulis->id, 'service_id' => $layanan->id],
        ['rating' => $baris['rating'], 'comment' => $baris['comment']],
    );
}

printf(
    "Ulasan layanan %s: %d ulasan contoh siap, %d ulasan pembeli contoh dibersihkan.%s",
    $layanan->name,
    Review::query()->where('service_id', $layanan->id)->count(),
    $dihapus,
    PHP_EOL,
);
