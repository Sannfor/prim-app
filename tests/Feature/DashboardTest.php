<?php

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Halaman akun pengguna
|--------------------------------------------------------------------------
|
| Rute /dashboard sekarang mengalihkan ke pusat pesanan pengguna, sesuai
| desain Figma yang menjadikan halaman "Profil — Pesanan" sebagai beranda
| akun setelah masuk.
|
*/

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users are redirected from dashboard to their orders page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('profile.orders', absolute: false));
});

test('authenticated users can visit the orders page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.orders'))
        ->assertOk()
        ->assertSee('Pesanan')
        ->assertSee('Belum ada pesanan');
});

test('authenticated users can visit the login code page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.login-code'))
        ->assertOk()
        ->assertSee('Kode Login')
        ->assertSee('Belum ada kode login');
});

test('guests cannot visit the orders or login code pages', function () {
    $this->get(route('profile.orders'))->assertRedirect('/login');
    $this->get(route('profile.login-code'))->assertRedirect('/login');
});
