<?php

use App\Enums\Role;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Smoke test Fase 0 — struktur route PRIM
|--------------------------------------------------------------------------
|
| Memastikan seluruh route pada peta URL PRIM sudah terdaftar dan tidak ada
| route yang error (500). Katalog bersifat publik; transaksi/langganan butuh
| login; panel admin butuh peran admin.
|
*/

test('landing page dapat diakses tanpa login', function () {
    $this->get('/')->assertOk();
});

test('halaman katalog dapat diakses tanpa login', function () {
    $this->get(route('catalog.index'))->assertOk();
    $this->get(route('catalog.compare'))->assertOk();
    $this->get(route('catalog.show', 'contoh-layanan'))->assertOk();
});

test('halaman transaksi dan langganan memerlukan login', function () {
    $this->get(route('transaction.index'))->assertRedirect(route('login'));
    $this->get(route('subscription.index'))->assertRedirect(route('login'));
});

test('pengguna biasa ditolak dari panel admin', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('administrator dapat membuka seluruh halaman panel admin', function () {
    $admin = User::factory()->admin()->create();

    $routes = [
        'admin.dashboard',
        'admin.providers.index',
        'admin.categories.index',
        'admin.services.index',
        'admin.plans.index',
        'admin.users.index',
        'admin.transactions.index',
    ];

    foreach ($routes as $name) {
        $this->actingAs($admin)->get(route($name))->assertOk();
    }
});

test('factory pengguna menetapkan peran dengan benar', function () {
    expect(User::factory()->create()->role)->toBe(Role::User)
        ->and(User::factory()->admin()->create()->role)->toBe(Role::Admin)
        ->and(User::factory()->provider()->create()->role)->toBe(Role::Provider);
});
