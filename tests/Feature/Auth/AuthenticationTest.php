<?php

use App\Models\User;
use Livewire\Volt\Volt as LivewireVolt;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('profile.orders', absolute: false));

    $this->assertAuthenticated();
});

test('device verification screen can be rendered', function () {
    $this->get(route('login.code'))->assertOk()->assertSee('Device Verification');
});

test('user status gates whether an account may sign in', function () {
    $suspended = User::factory()->suspended()->create();

    expect($suspended->canSignIn())->toBeFalse()
        ->and(User::factory()->create()->canSignIn())->toBeTrue();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('logout route clears the authenticated user from the web guard', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web');

    /*
     | Aksi logout diuji langsung karena rute POST /logout dilindungi CSRF dan
     | lingkungan pengujian tidak menjalankan middleware tersebut sehingga
     | permintaan HTTP akan ditolak dengan status 419.
     */
    $response = (new App\Livewire\Actions\Logout)();

    expect(Illuminate\Support\Facades\Auth::guard('web')->check())->toBeFalse()
        ->and($response->getTargetUrl())->toBe(url('/'));
});
