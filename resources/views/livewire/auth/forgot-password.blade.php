<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts::auth', ['title' => 'Lupa Kata Sandi', 'cardClass' => 'max-w-[540px]'])] class extends Component {
    public string $email = '';

    /**
     * Kirim tautan pengaturan ulang kata sandi ke alamat email yang diberikan.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', 'Tautan pengaturan ulang kata sandi akan dikirim bila akun terdaftar.');
    }
}; ?>

<div class="font-auth">
    @php $inputClass = 'prim-input font-auth'; @endphp

    <h1 class="font-auth text-[32px] font-bold leading-tight text-brand sm:text-[38px]">Lupa Kata Sandi</h1>
        <p class="mt-2 text-[15px] leading-relaxed text-ink sm:text-base">
            Kami akan mengirim kode ke emailmu
        </p>

        <x-auth-session-status class="mt-5 rounded-lg bg-status-done-bg px-3 py-2 text-sm text-status-done-fg" :status="session('status')" />

        <form wire:submit="sendPasswordResetLink" class="mt-7 space-y-5">
            <div>
                <label for="email" class="prim-label text-ink">Email</label>

                <div class="relative">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        wire:model="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="Masukkan email"
                        class="{{ $inputClass }} pr-14"
                    >

                    <button
                        type="submit"
                        class="absolute top-1/2 right-3 flex size-9 -translate-y-1/2 items-center justify-center rounded-full text-ink transition hover:bg-canvas"
                        title="Kirim tautan"
                    >
                        <flux:icon.paper-airplane class="size-5" />
                    </button>
                </div>

                @error('email')
                    <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="prim-btn prim-btn-block font-auth text-base" wire:loading.attr="disabled">
                <span wire:loading.remove>Konfirmasi</span>
                <span wire:loading>Mengirim…</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-ink">
            Ingat kata sandimu?
            <a href="{{ route('login') }}" class="font-medium text-brand-violet hover:underline" wire:navigate>Kembali ke Login</a>
        </p>
</div>
