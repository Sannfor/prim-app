<?php

/**
 * Pembuat data contoh satu pesanan yang masih menunggu pembayaran.
 *
 * Dipakai untuk mengambil gambar rancangan antarmuka halaman pembayaran pada
 * mode simulasi. Jalankan dari dalam folder prim:
 *     php tools/buat-pesanan-contoh.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;

$pelanggan = User::query()->where('email', 'andi@prim.test')->firstOrFail();

// Paket Netflix 2 Perangkat, agar gambar konsisten dengan tangkapan layar lain.
$paket = Plan::query()
    ->whereHas('service', fn ($q) => $q->where('slug', 'netflix'))
    ->where('is_active', true)
    ->orderBy('price')
    ->skip(1)
    ->first()
    ?? Plan::query()->where('is_active', true)->firstOrFail();

/*
 * Hapus pesanan contoh yang belum dibayar dari pemanggilan sebelumnya supaya
 * berkas gambar selalu memperlihatkan satu pesanan yang sama.
 */
Transaction::query()
    ->where('user_id', $pelanggan->id)
    ->where('status', TransactionStatus::Pending->value)
    ->where('notes', 'like', 'Pesanan contoh untuk pengambilan gambar%')
    ->delete();

$transaksi = Transaction::create([
    'user_id' => $pelanggan->id,
    'plan_id' => $paket->id,
    'amount' => $paket->price,
    'status' => TransactionStatus::Pending,
    'payment_method' => null,
    'expires_at' => now()->addHours(Transaction::PAYMENT_WINDOW_HOURS),
    'notes' => 'Pesanan contoh untuk pengambilan gambar antarmuka.',
]);

printf("Pesanan contoh dibuat: %s%s", $transaksi->order_code, PHP_EOL);
printf("  Layanan : %s%s", $paket->service->name, PHP_EOL);
printf("  Paket   : %s%s", $paket->groupLabel(), PHP_EOL);
printf("  Nominal : %s%s", $transaksi->formattedAmount(), PHP_EOL);
printf("  Tautan  : /transaksi/%s%s", $transaksi->order_code, PHP_EOL);
