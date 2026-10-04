<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

/**
 * Verifikasi perangkat melalui kode sekali pakai (OTP).
 *
 * Mengikuti frame Figma "Kode Login" / "Device Verification": pengguna
 * memasukkan email, meminta kode, lalu menukarkan kode tersebut untuk
 * memverifikasi perangkat yang sedang dipakai.
 *
 * Pengiriman surel belum dikonfigurasi pada versi ini, sehingga kode
 * ditampilkan langsung di halaman sebagai sarana demonstrasi akademik.
 */
new #[Layout('layouts::auth', ['title' => 'Device Verification'])] class extends Component {
    public string $email = '';
    public string $code = '';
    public ?string $issuedCode = null;
    public bool $codeRequested = false;

    /**
     * Kirim (buat) kode verifikasi untuk email yang diberikan.
     */
    public function requestCode(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $key = 'login-code:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Terlalu banyak permintaan kode. Coba lagi beberapa saat lagi.');

            return;
        }

        RateLimiter::hit($key, 300);

        $code = (string) random_int(100000, 999999);

        session(['login_code' => $code, 'login_code_email' => $this->email]);

        $this->issuedCode = $code;
        $this->codeRequested = true;
        $this->code = '';

        $this->dispatch('code-issued');
    }

    /**
     * Cocokkan kode yang dimasukkan dengan kode yang dikirim.
     */
    public function verify(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $expected = session('login_code');
        $expectedEmail = session('login_code_email');

        if (! $expected || $expectedEmail !== $this->email || ! hash_equals((string) $expected, $this->code)) {
            $this->addError('code', 'Kode verifikasi tidak cocok atau sudah kedaluwarsa.');

            return;
        }

        session()->forget(['login_code', 'login_code_email']);

        $user = User::query()->where('email', $this->email)->first();

        if (! $user) {
            $this->addError('email', 'Akun dengan email tersebut tidak ditemukan.');

            return;
        }

        auth()->login($user);
        session()->regenerate();

        $this->redirect(route('profile.orders', absolute: false), navigate: true);
    }
}; ?>

<div class="font-auth">
    @php $inputClass = 'prim-input font-auth'; @endphp

    <h1 class="font-auth text-[30px] font-bold leading-tight text-brand sm:text-[36px]">Device Verification</h1>

        <p class="mt-2 text-[15px] leading-relaxed text-ink sm:text-base">
            Masukkan emailmu, lalu tukarkan kode untuk memverifikasi perangkat ini.
        </p>

        <form wire:submit="requestCode" class="mt-7 space-y-5">
            <div>
                <label for="email" class="{{ 'prim-label text-ink' }}">EMAIL</label>
                <input id="email" type="email" name="email" wire:model="email" required autofocus
                    autocomplete="email" placeholder="Masukkan email" class="{{ $inputClass }}">
                @error('email') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="prim-btn prim-btn-block font-auth text-base" wire:loading.attr="disabled">
                <span wire:loading.remove>Dapatkan Kode</span>
                <span wire:loading>Mengirim…</span>
            </button>
        </form>

        @if ($codeRequested)
            <div class="mt-6 rounded-xl bg-brand-soft p-4">
                <p class="text-xs tracking-wide text-brand uppercase">Kode OTP</p>
                <p class="mt-1 font-auth text-3xl font-bold tracking-[0.35em] text-brand">{{ $issuedCode }}</p>
                <p class="mt-2 text-xs leading-relaxed text-ink/80">
                    Versi demonstrasi: kode ditampilkan di halaman ini karena pengiriman surel
                    belum dikonfigurasi. Pada sistem produksi kode dikirim ke email terdaftar.
                </p>
            </div>
        @endif

        <form wire:submit="verify" class="mt-6 space-y-5">
            <div>
                <label for="code" class="prim-label text-ink">KODE OTP</label>
                <input id="code" type="text" name="code" wire:model="code" inputmode="numeric" maxlength="6"
                    placeholder="OTP ‘……’" class="{{ $inputClass }} tracking-[0.5em]">
                @error('code') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="prim-btn prim-btn-block font-auth text-base">
                Verifikasi &amp; Masuk
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-ink">
            Sudah tahu kata sandimu?
            <a href="{{ route('login') }}" class="font-medium text-brand-violet hover:underline" wire:navigate>Masuk biasa</a>
        </p>
</div>
