<?php

use App\Models\Category;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

/*
|--------------------------------------------------------------------------
| Verifikasi rute dengan data seeder nyata
|--------------------------------------------------------------------------
|
| Berbeda dari test fitur lain yang memakai factory, berkas ini mengisi basis
| data memakai DatabaseSeeder sungguhan lalu memastikan setiap halaman pada
| peta URL PRIM benar-benar dapat dibuka dengan data tersebut.
|
*/

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('seluruh halaman publik dapat dibuka dengan data seeder', function () {
    $this->get('/')->assertOk()->assertSee('Temukan, bandingkan');

    $this->get(route('catalog.index'))->assertOk()->assertSee('Katalog Layanan Premium');

    $this->get(route('catalog.index', ['q' => 'sonata']))
        ->assertOk()
        ->assertSee('Sonata Music Unlimited');

    $this->get(route('catalog.show', 'nusantara-film-premium'))
        ->assertOk()
        ->assertSee('Pilih paket langganan')
        ->assertSee('Premium 6 Bulan');

    $this->get(route('catalog.compare'))->assertOk()->assertSee('Bandingkan Layanan');
});

test('tamu dialihkan ke halaman masuk pada seluruh rute terlindungi', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();

    $urls = [
        route('transaction.index'),
        route('transaction.checkout', $plan->id),
        route('subscription.index'),
        route('admin.dashboard'),
        route('admin.services.index'),
        route('admin.categories.index'),
        route('admin.providers.index'),
        route('admin.users.index'),
        route('admin.transactions.index'),
        route('admin.plans.index', $plan->service->slug),
    ];

    foreach ($urls as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }
});

test('akun admin demo dapat membuka seluruh halaman panel pengelola', function () {
    $admin = User::query()->where('email', 'admin@prim.test')->firstOrFail();

    $this->actingAs($admin);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Pengelola')
        ->assertSee('Pendapatan Bulan Ini')
        ->assertSee('Transaksi 14 Hari Terakhir');

    $pages = [
        'admin.services.index' => 'Kelola Layanan',
        'admin.categories.index' => 'Kelola Kategori',
        'admin.providers.index' => 'Kelola Penyedia',
        'admin.users.index' => 'Kelola Pengguna',
        'admin.transactions.index' => 'Kelola Transaksi',
    ];

    foreach ($pages as $name => $heading) {
        $this->get(route($name))->assertOk()->assertSee($heading);
    }

    // Paket dikelola per layanan.
    $service = Service::query()->firstOrFail();

    $this->get(route('admin.plans.index', $service->slug))
        ->assertOk()
        ->assertSee('Paket — '.$service->name);
});

test('akun pelanggan demo dapat membuka halaman akun dan checkout', function () {
    $customer = User::query()->where('email', 'ahmadi@prim.test')->firstOrFail();
    $plan = Plan::query()->where('is_active', true)->firstOrFail();

    $this->actingAs($customer);

    $this->get(route('subscription.index'))->assertOk()->assertSee('Langganan Saya');
    $this->get(route('transaction.index'))->assertOk()->assertSee('Riwayat Transaksi');

    $this->get(route('transaction.checkout', $plan->id))
        ->assertOk()
        ->assertSee('Konfirmasi Pemesanan')
        ->assertSee($plan->name);
});

test('pelanggan demo dapat membuka detail transaksinya dari seeder', function () {
    $transaction = Transaction::query()
        ->whereHas('user', fn ($q) => $q->where('email', 'ahmadi@prim.test'))
        ->firstOrFail();

    $this->actingAs($transaction->user)
        ->get(route('transaction.show', $transaction->order_code))
        ->assertOk()
        ->assertSee($transaction->order_code);
});

test('dashboard admin menampilkan angka yang berasal dari seeder', function () {
    $admin = User::query()->where('email', 'admin@prim.test')->firstOrFail();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();

    // Angka metrik dihitung dari basis data, bukan nilai statis scaffold.
    $response->assertSee((string) Service::query()->count());
    $response->assertSee((string) Provider::query()->count());
});

test('seeder menyediakan katalog dan data demonstrasi yang lengkap', function () {
    expect(Provider::query()->count())->toBeGreaterThanOrEqual(3)
        ->and(Category::query()->count())->toBeGreaterThanOrEqual(3)
        ->and(Service::query()->count())->toBeGreaterThanOrEqual(6)
        ->and(Plan::query()->count())->toBeGreaterThanOrEqual(10)
        ->and(Transaction::query()->count())->toBeGreaterThanOrEqual(5)
        ->and(Subscription::query()->count())->toBeGreaterThanOrEqual(3)
        ->and(User::query()->where('email', 'admin@prim.test')->exists())->toBeTrue();
});

test('seeder aman dijalankan berulang kali', function () {
    $servicesBefore = Service::query()->count();
    $plansBefore = Plan::query()->count();

    $this->seed(DatabaseSeeder::class);

    expect(Service::query()->count())->toBe($servicesBefore)
        ->and(Plan::query()->count())->toBe($plansBefore);
});
