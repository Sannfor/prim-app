<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts::auth', ['title' => 'Konfirmasi Password'])] class extends Component {
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="font-auth">
    <h1 class="font-auth text-[30px] font-bold leading-tight text-brand sm:text-[36px]">Konfirmasi Password</h1>

    <p class="mt-2 text-[15px] leading-relaxed text-ink sm:text-base">
        Ini area terproteksi. Masukkan kata sandimu untuk melanjutkan.
    </p>

    <x-auth-session-status class="mt-5 rounded-lg bg-status-done-bg px-3 py-2 text-sm text-status-done-fg" :status="session('status')" />

    <form wire:submit="confirmPassword" class="mt-7 space-y-5">
        <div>
            <label for="password" class="prim-label text-ink">Password</label>
            <input id="password" type="password" name="password" wire:model="password" required
                autocomplete="current-password" placeholder="Masukkan password" class="prim-input font-auth">
            @error('password') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="prim-btn prim-btn-block font-auth text-base">Konfirmasi</button>
    </form>
</div>
