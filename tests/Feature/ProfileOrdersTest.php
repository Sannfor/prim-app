<?php

use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

/*
|--------------------------------------------------------------------------
| Pusat pesanan & kode login pengguna
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('halaman pesanan pengguna hanya menampilkan pesanan miliknya', function () {
    $customer = User::query()->where('email', 'andi@prim.test')->firstOrFail();
    $other = User::query()->where('email', 'rina@prim.test')->firstOrFail();

    $mine = Transaction::query()->where('user_id', $customer->id)->pluck('order_code');
    $theirs = Transaction::query()->where('user_id', $other->id)->pluck('order_code');

    expect($mine)->not->toBeEmpty()
        ->and($theirs)->not->toBeEmpty();

    $response = $this->actingAs($customer)->get(route('profile.orders'));

    $response->assertOk()->assertSee($mine->first());

    // Kode pesanan milik pengguna lain tidak boleh tampil.
    foreach ($theirs as $code) {
        $response->assertDontSee($code);
    }
});

test('kode login hanya dibagikan pada pesanan yang berhasil', function () {
    // Seeder membuat kredensial hanya untuk status berhasil.
    $unpaidWithCredential = Transaction::query()
        ->whereNotIn('status', ['paid', 'accepted'])
        ->whereHas('serviceCredentials')
        ->count();

    expect($unpaidWithCredential)->toBe(0);

    $paid = Transaction::query()
        ->whereIn('status', ['paid', 'accepted'])
        ->doesntHave('serviceCredentials')
        ->count();

    expect($paid)->toBe(0);
});

test('pengguna dapat membuka kode login miliknya sendiri', function () {
    $customer = User::query()->where('email', 'andi@prim.test')->firstOrFail();

    $credential = $customer->serviceCredentials()->firstOrFail();

    $this->actingAs($customer)
        ->get(route('profile.login-code'))
        ->assertOk()
        ->assertSee($credential->label)
        ->assertDontSee($credential->login_code); // Tersembunyi sampai diminta.
});

test('pengguna tidak dapat melihat kredensial milik pengguna lain', function () {
    $customer = User::query()->where('email', 'andi@prim.test')->firstOrFail();
    $other = User::query()->where('email', 'budi@prim.test')->firstOrFail();

    $foreignCredential = $other->serviceCredentials()->firstOrFail();

    $this->actingAs($customer)
        ->get(route('profile.login-code'))
        ->assertOk()
        ->assertDontSee($foreignCredential->login_code);
});
