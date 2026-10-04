<?php

/**
 * Bantuan untuk skrip tangkapan layar: cetak data yang dibutuhkan.
 *
 * Keluaran berupa satu baris "PLAN_ID|ORDER_CODE" sehingga dapat dibaca skrip
 * PowerShell dengan mudah.
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$planId = App\Models\Plan::query()
    ->whereHas('service', fn ($q) => $q->where('slug', 'netflix'))
    ->orderBy('price')
    ->value('id');

$orderCode = App\Models\Transaction::query()
    ->whereHas('user', fn ($q) => $q->where('email', 'andi@prim.test'))
    ->latest()
    ->value('order_code');

echo ($planId ?? 1).'|'.($orderCode ?? '').PHP_EOL;
