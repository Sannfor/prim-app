<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('layouts::auth', ['title' => 'Masuk'])] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    /**
     * Proses permintaan masuk.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('profile.orders', absolute: false), navigate: true);
    }

    /**
     * Pastikan permintaan masuk belum melewati batas percobaan.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Kunci pembatas percobaan masuk.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

@php
    $inputClass = 'prim-input font-auth';
    $labelClass = 'prim-label text-ink';
@endphp

<div class="font-auth">
    <h1 class="font-auth text-[34px] font-bold leading-tight text-brand sm:text-[40px]">Login</h1>

    <p class="mt-2 text-[15px] text-ink sm:text-base">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-medium text-brand-violet hover:underline" wire:navigate>Daftar</a>
    </p>

    <x-auth-session-status class="mt-4 rounded-lg bg-status-done-bg px-3 py-2 text-sm text-status-done-fg" :status="session('status')" />

    <form wire:submit="login" class="mt-7 space-y-5">
        <div>
            <label for="email" class="{{ $labelClass }}">Email</label>

            <input
                id="email"
                type="email"
                name="email"
                wire:model="email"
                required
                autofocus
                autocomplete="email"
                placeholder="Masukkan email"
                class="{{ $inputClass }}"
            />

            @error('email')
                <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="{{ $labelClass }}">Password</label>

            <input
                id="password"
                type="password"
                name="password"
                wire:model="password"
                required
                autocomplete="current-password"
                placeholder="Masukkan password"
                class="{{ $inputClass }}"
            />

            @error('password')
                <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="text-sm font-medium text-brand-violet hover:underline"
                    wire:navigate
                >Lupa password?</a>
            @endif
        </div>

        <button type="submit" class="prim-btn prim-btn-block font-auth text-base" wire:loading.attr="disabled">
            <span wire:loading.remove>Masuk</span>
            <span wire:loading>Memproses…</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-ink">
        Punya akun dengan verifikasi perangkat?
        <a href="{{ route('login.code') }}" class="font-medium text-brand-violet hover:underline" wire:navigate>Masuk dengan kode</a>
    </p>
</div>
