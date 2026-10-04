<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts::auth', ['title' => 'Ubah Password', 'cardClass' => 'max-w-[540px]'])] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Simpan kata sandi baru untuk pengguna terkait.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PasswordReset) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="font-auth">
    @php $inputClass = 'prim-input font-auth'; @endphp

    <h1 class="font-auth text-[32px] font-bold leading-tight text-brand sm:text-[38px]">Ubah Password</h1>
        <p class="mt-2 text-[15px] leading-relaxed text-ink sm:text-base">
            Tentukan kata sandi baru untuk akunmu
        </p>

        <x-auth-session-status class="mt-5 rounded-lg bg-status-done-bg px-3 py-2 text-sm text-status-done-fg" :status="session('status')" />

        <form wire:submit="resetPassword" class="mt-7 space-y-5">
            <div>
                <label for="email" class="prim-label text-ink">Email</label>
                <input id="email" type="email" name="email" wire:model="email" required autocomplete="email"
                    placeholder="Masukkan email" class="{{ $inputClass }}">
                @error('email') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="prim-label text-ink">Password Baru</label>
                <input id="password" type="password" name="password" wire:model="password" required
                    autocomplete="new-password" placeholder="Masukkan password baru" class="{{ $inputClass }}">
                @error('password') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="prim-label text-ink">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                    wire:model="password_confirmation" required autocomplete="new-password"
                    placeholder="Masukkan lagi password baru" class="{{ $inputClass }}">
                @error('password_confirmation') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="prim-btn prim-btn-block font-auth text-base" wire:loading.attr="disabled">
                <span wire:loading.remove>Konfirmasi</span>
                <span wire:loading>Menyimpan…</span>
            </button>
        </form>
</div>
