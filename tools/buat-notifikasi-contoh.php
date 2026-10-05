<?php

/**
 * Pembuat data contoh notifikasi untuk pengambilan gambar antarmuka.
 *
 * Jalankan dari dalam folder prim:
 *     php tools/buat-notifikasi-contoh.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\BuyerNotificationType;
use App\Models\BuyerNotification;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BuyerNotificationService;

$pelanggan = User::query()->where('email', 'andi@prim.test')->firstOrFail();
$layanan = app(BuyerNotificationService::class);

// Bersihkan notifikasi contoh dari pemanggilan sebelumnya.
BuyerNotification::query()->where('user_id', $pelanggan->id)->delete();

$transaksi = Transaction::query()
    ->where('user_id', $pelanggan->id)
    ->with('plan.service')
    ->orderByDesc('paid_at')
    ->get();

$lunas = $transaksi->first(fn ($t) => $t->status->isSuccessful());

if ($lunas !== null) {
    $layanan->pesananLunas($lunas);
    $layanan->kodeLoginSiap($lunas, 1);

    // Tandai notifikasi pertama sudah dibaca, agar tampilan memuat keduanya.
    BuyerNotification::query()
        ->where('user_id', $pelanggan->id)
        ->oldest()
        ->first()
        ?->markAsRead();
}

$kedaluwarsa = Transaction::query()
    ->where('user_id', $pelanggan->id)
    ->where('status', 'expired')
    ->with('plan.service')
    ->first();

if ($kedaluwarsa !== null) {
    $notifikasi = $layanan->pesananKedaluwarsa($kedaluwarsa);
    $notifikasi->forceFill(['created_at' => now()->subDays(2)])->save();
}

printf("Notifikasi untuk %s:%s", $pelanggan->email, PHP_EOL);

foreach (BuyerNotification::query()->where('user_id', $pelanggan->id)->latest()->get() as $n) {
    printf(
        "  [%s] %-22s %s%s",
        $n->isUnread() ? 'baru' : 'dibaca',
        $n->type->value,
        $n->title,
        PHP_EOL,
    );
}

printf("%sBelum dibaca: %d%s", PHP_EOL, BuyerNotification::query()->where('user_id', $pelanggan->id)->unread()->count(), PHP_EOL);
