<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts::auth', ['title' => 'Daftar', 'cardClass' => 'max-w-[560px]'])] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        unset($validated['terms']);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('profile.orders', absolute: false), navigate: true);
    }
}; ?>

<div class="font-auth">
    @php
        $inputClass = 'prim-input font-auth';
        $labelClass = 'prim-label text-ink';
    @endphp

    <h1 class="font-auth text-[34px] font-bold leading-tight text-brand sm:text-[40px]">Daftar</h1>

        <p class="mt-2 text-[15px] text-ink sm:text-base">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-medium text-brand-violet hover:underline" wire:navigate>Login</a>
        </p>

        <form wire:submit="register" class="mt-7 space-y-5">
            <div>
                <label for="name" class="{{ $labelClass }}">Nama Lengkap</label>
                <input id="name" type="text" name="name" wire:model="name" required autofocus autocomplete="name"
                    placeholder="Masukkan nama lengkap" class="{{ $inputClass }}">
                @error('name') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="{{ $labelClass }}">Email</label>
                <input id="email" type="email" name="email" wire:model="email" required autocomplete="email"
                    placeholder="Masukkan email" class="{{ $inputClass }}">
                @error('email') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="{{ $labelClass }}">Nomor WhatsApp</label>
                <input id="phone" type="tel" name="phone" wire:model="phone" required autocomplete="tel"
                    placeholder="Contoh: 0812-3456-7890" class="{{ $inputClass }}">
                @error('phone') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="{{ $labelClass }}">Kata Sandi</label>
                <input id="password" type="password" name="password" wire:model="password" required
                    autocomplete="new-password" placeholder="Masukkan kata sandi" class="{{ $inputClass }}">
                @error('password') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="{{ $labelClass }}">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                    wire:model="password_confirmation" required autocomplete="new-password"
                    placeholder="Masukkan ulang kata sandi" class="{{ $inputClass }}">
                @error('password_confirmation') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <label class="flex cursor-pointer items-start gap-3 text-[13px] leading-relaxed text-ink">
                <input
                    type="checkbox"
                    wire:model="terms"
                    class="mt-0.5 size-4 shrink-0 rounded border-line-input text-brand focus:ring-brand"
                >
                <span>
                    Dengan mendaftar, saya menyetujui
                    <span class="font-medium">Syarat dan Ketentuan Prim</span>,
                    <span class="font-medium">Disclaimer</span> serta
                    <span class="font-medium">Kebijakan Privasi</span>
                </span>
            </label>
            @error('terms') <p class="-mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror

            <button type="submit" class="prim-btn prim-btn-block font-auth text-base" wire:loading.attr="disabled">
                <span wire:loading.remove>Daftar</span>
                <span wire:loading>Memproses…</span>
            </button>
        </form>
</div>
