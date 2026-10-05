<?php

/**
 * Pemeriksa rute ringan: memastikan halaman utama merender tanpa galat.
 *
 * Dijalankan dari dalam folder prim:
 *     php tools/smoke-routes.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = App\Models\User::query()->where('email', 'admin@prim.com')->first();
$customer = App\Models\User::query()->where('email', 'andi@prim.test')->first();
$plan = App\Models\Plan::query()->first();
$subscription = App\Models\Subscription::query()->where('user_id', $customer?->id)->first();
$transaction = App\Models\Transaction::query()->where('user_id', $customer?->id)->first();

$kelompok = [
    'TAMU' => [null, [
        '/', '/katalog', '/katalog/netflix', '/bandingkan', '/cara-berlangganan',
        '/laporan-kendala', '/login', '/register', '/kode-login', '/forgot-password',
    ]],
    'PELANGGAN' => [$customer, [
        '/profil/pesanan', '/profil/kode-login', '/langganan', '/transaksi',
        '/settings/profile', '/settings/password', '/settings/appearance',
        $plan ? '/checkout/'.$plan->id : null,
        $transaction ? '/transaksi/'.$transaction->order_code : null,
        $subscription ? '/langganan/'.$subscription->id.'/perpanjang' : null,
    ]],
    'ADMIN' => [$admin, [
        '/admin', '/admin/pesanan', '/admin/pembayaran', '/admin/pengguna',
        '/admin/produk', '/admin/laporan',
        '/admin/pengaturan/profil', '/admin/pengaturan/notifikasi',
        '/admin/kategori', '/admin/penyedia', '/admin/voucher',
    ]],
];

$gagal = 0;

foreach ($kelompok as $label => [$pengguna, $rute]) {
    echo '=== '.$label.' ==='.PHP_EOL;

    foreach (array_filter($rute) as $uri) {
        $pengguna ? Illuminate\Support\Facades\Auth::login($pengguna) : Illuminate\Support\Facades\Auth::logout();

        $request = Illuminate\Http\Request::create($uri, 'GET');
        $request->setLaravelSession($app['session']->driver());

        try {
            $status = $kernel->handle($request)->getStatusCode();
            $ok = $status === 200;

            if (! $ok) {
                $gagal++;
            }

            printf("  %-5s %s%s", $ok ? 'OK' : 'GAGAL', $uri, PHP_EOL);
        } catch (Throwable $e) {
            $gagal++;
            printf("  ERR   %s — %s%s", $uri, $e->getMessage(), PHP_EOL);
        }
    }
}

echo PHP_EOL.($gagal === 0 ? 'SEMUA RUTE BERHASIL' : $gagal.' RUTE GAGAL').PHP_EOL;
