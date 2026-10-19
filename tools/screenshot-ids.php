<?php

/**
 * Bantuan untuk skrip tangkapan layar Bab 4: cetak data yang dibutuhkan.
 *
 * Keluaran berupa baris "KUNCI=NILAI" agar mudah dibaca skrip PowerShell:
 *
 *     PLAN_ID=12
 *     SERVICE_SLUG=netflix
 *     SERVICE_ID=5
 *     ORDER_PENDING=PRIM-20261007-ABCDEF
 *     ORDER_PAID=PRIM-20261007-GHIJKL
 *     SUBSCRIPTION_ID=11
 *     COMPARE_IDS=3,7,12
 *
 * Jalankan dari dalam folder prim:
 *     php tools/screenshot-ids.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;

$pelanggan = User::query()->where('email', 'andi@prim.test')->first();

/**
 * Paket Netflix "2 Perangkat" dipakai agar seluruh gambar konsisten dengan
 * pesanan contoh yang dibuat tools/buat-pesanan-contoh.php.
 */
$paket = Plan::query()
    ->whereHas('service', fn ($q) => $q->where('slug', 'netflix'))
    ->where('is_active', true)
    ->orderBy('price')
    ->skip(1)
    ->first()
    ?? Plan::query()->where('is_active', true)->orderBy('price')->first();

/*
 * Layanan milik akun penyedia contoh.
 *
 * Panel penyedia dibatasi oleh tautan providers.user_id, jadi alamat halaman
 * paket harus memakai slug layanan milik penyedia tersebut, bukan layanan mana
 * pun pada katalog.
 */
$penyediaAkun = Provider::query()
    ->whereNotNull('user_id')
    ->with(['services' => fn ($q) => $q->orderBy('id')])
    ->first();

/*
 * Pesanan yang masih dapat dibayar: statusnya menunggu dan batas waktunya belum
 * lewat. Inilah pesanan yang menampilkan pemilih metode pembayaran.
 */
$pesananMenunggu = $pelanggan === null ? null : Transaction::query()
    ->where('user_id', $pelanggan->id)
    ->where('status', TransactionStatus::Pending->value)
    ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
    ->latest()
    ->value('order_code');

/** Pesanan yang sudah lunas: dipakai untuk gambar struk dan detail pesanan lunas. */
$pesananLunas = $pelanggan === null ? null : Transaction::query()
    ->where('user_id', $pelanggan->id)
    ->whereIn('status', [TransactionStatus::Paid->value, TransactionStatus::Accepted->value])
    ->latest('paid_at')
    ->value('order_code');

/*
 * Langganan aktif milik pelanggan, untuk halaman perpanjangan.
 *
 * Dipilih langganan yang layanannya punya paket aktif terbanyak agar halaman
 * perpanjangan memperlihatkan beberapa pilihan paket, bukan satu kartu saja.
 */
$langganan = $pelanggan === null ? null : Subscription::query()
    ->where('user_id', $pelanggan->id)
    ->where('ends_at', '>', now())
    ->with('plan.service')
    ->get()
    ->sortByDesc(
        fn ($s) => $s->plan?->service?->plans()->where('is_active', true)->count() ?? 0
    )
    ->first()?->id;

/*
 * Tiga layanan untuk halaman perbandingan. Katalog diurutkan "terbaru" dan
 * menampilkan 12 kartu per halaman, jadi calon diambil dari dua belas layanan
 * terbaru agar tombol bandingnya pasti ada di halaman pertama. Setiap layanan
 * juga harus punya paket aktif supaya tabel perbandingan terisi.
 */
$bandingkan = Service::query()
    ->whereIn('id', Service::query()->latest()->take(12)->select('id'))
    ->whereHas('plans', fn ($q) => $q->where('is_active', true))
    ->latest()
    ->take(3)
    ->pluck('id')
    ->all();

$baris = [
    'PLAN_ID' => $paket?->id ?? 1,
    'SERVICE_SLUG' => $paket?->service?->slug ?? 'netflix',
    'SERVICE_ID' => $paket?->service?->id ?? 1,
    'PROVIDER_SERVICE_SLUG' => $penyediaAkun?->services->first()?->slug ?? '',
    'ORDER_PENDING' => $pesananMenunggu ?? '',
    'ORDER_PAID' => $pesananLunas ?? '',
    'SUBSCRIPTION_ID' => $langganan ?? '',
    'COMPARE_IDS' => implode(',', $bandingkan),
];

foreach ($baris as $kunci => $nilai) {
    printf("%s=%s%s", $kunci, $nilai, PHP_EOL);
}
