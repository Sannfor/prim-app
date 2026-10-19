<?php

/**
 * Pembuat data contoh voucher untuk pengambilan gambar antarmuka.
 *
 * Halaman checkout menolak kode voucher yang sudah pernah dipakai pembeli yang
 * sama, sehingga gambar "Konfirmasi Pemesanan dengan voucher" hanya dapat
 * memperlihatkan potongan bila catatan pemakaian pembeli contoh dibersihkan.
 *
 * Skrip ini menghapus catatan pemakaian milik pembeli contoh dan mengurangi
 * used_count voucher terkait dengan jumlah yang sama, supaya penghitungnya tetap
 * konsisten dengan isi tabel voucher_redemptions. Pesanan yang sudah ada tidak
 * diubah.
 *
 * Jalankan dari dalam folder prim:
 *     php tools/buat-voucher-contoh.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;

$pelanggan = User::query()->where('email', 'andi@prim.test')->first();

if ($pelanggan === null) {
    fwrite(STDERR, "Pembeli contoh andi@prim.test tidak ditemukan.".PHP_EOL);
    exit(1);
}

$catatan = VoucherRedemption::query()->where('user_id', $pelanggan->id)->get();

foreach ($catatan as $redemption) {
    $voucher = Voucher::query()->find($redemption->voucher_id);

    if ($voucher !== null) {
        $voucher->forceFill(['used_count' => max(0, $voucher->used_count - 1)])->save();
    }

    $redemption->delete();
}

printf(
    "Catatan pemakaian voucher untuk %s dibersihkan: %d baris.%s",
    $pelanggan->email,
    $catatan->count(),
    PHP_EOL,
);

foreach (Voucher::query()->orderBy('id')->get() as $voucher) {
    printf(
        "  %-13s %-10s %-10s kuota %s/%s%s",
        $voucher->code,
        $voucher->type,
        $voucher->valueLabel(),
        $voucher->used_count,
        $voucher->usage_limit ?? '∞',
        PHP_EOL,
    );
}
