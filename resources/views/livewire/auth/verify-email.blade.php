<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts::auth', ['title' => 'Verifikasi Email'])] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="font-auth">
    <h1 class="font-auth text-[30px] font-bold leading-tight text-brand sm:text-[36px]">Verifikasi Email</h1>

    <p class="mt-2 text-[15px] leading-relaxed text-ink sm:text-base">
        Silakan verifikasi alamat emailmu melalui tautan yang baru kami kirimkan.
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="mt-5 rounded-lg bg-status-done-bg px-3 py-2 text-sm text-status-done-fg">
            Tautan verifikasi baru telah dikirim ke alamat email yang kamu daftarkan.
        </p>
    @endif

    <div class="mt-7 space-y-3">
        <button type="button" wire:click="sendVerification" class="prim-btn prim-btn-block font-auth text-base">
            Kirim ulang email verifikasi
        </button>

        <button
            type="button"
            wire:click="logout"
            class="w-full text-center text-sm text-muted underline hover:text-ink"
        >
            Keluar
        </button>
    </div>
</div>
