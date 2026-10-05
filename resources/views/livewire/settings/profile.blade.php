<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts::settings', ['title' => 'Profil'])] class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<div>
    <x-settings.layout heading="Profil" subheading="Perbarui nama dan alamat surel akunmu.">
        <form wire:submit="updateProfileInformation" class="rounded-xl bg-white p-6 shadow-brand-xs">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="prim-label">Nama Lengkap</label>
                    <input id="name" type="text" wire:model="name" name="name" required autofocus
                        autocomplete="name" class="prim-input" placeholder="Nama lengkap">
                    @error('name') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="email" class="prim-label">Email</label>
                    <input id="email" type="email" wire:model="email" name="email" required
                        autocomplete="email" class="prim-input" placeholder="nama@email.com">
                    @error('email') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                        <p class="mt-2 text-sm text-status-wait-fg">
                            Alamat surelmu belum diverifikasi.
                            <button type="button" wire:click.prevent="resendVerificationNotification" class="font-medium underline">
                                Kirim ulang tautan verifikasi
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-status-done-fg">
                                Tautan verifikasi baru telah dikirim ke alamat surelmu.
                            </p>
                        @endif
                    @endif
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-line-soft pt-5">
                <button type="submit" class="prim-btn px-7" wire:loading.attr="disabled">
                    <span wire:loading.remove>Simpan Perubahan</span>
                    <span wire:loading>Menyimpan…</span>
                </button>

                <x-action-message class="text-sm text-status-done-fg" on="profile-updated">
                    Tersimpan.
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</div>
