<?php

use Livewire\Volt\Volt;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = Volt::test('auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('phone', '081200000000')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('terms', true)
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('profile.orders', absolute: false));

    $this->assertAuthenticated();
});

test('registration requires a phone number and accepted terms', function () {
    Volt::test('auth.register')
        ->set('name', 'Test User')
        ->set('email', 'tanpa-syarat@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasErrors(['phone', 'terms']);
});
