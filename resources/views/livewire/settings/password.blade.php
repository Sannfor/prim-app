<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts::settings', ['title' => 'Password'])] class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<div>
    <x-settings.layout heading="Kata Sandi" subheading="Gunakan kata sandi yang panjang dan tidak dipakai di layanan lain.">
        <form wire:submit="updatePassword" class="rounded-xl bg-white p-6 shadow-brand-xs">
            <div class="space-y-5">
                <div>
                    <label for="update_password_current_password" class="prim-label">Kata Sandi Saat Ini</label>
                    <input id="update_password_current_password" type="password" wire:model="current_password"
                        name="current_password" required autocomplete="current-password" class="prim-input"
                        placeholder="Masukkan kata sandi saat ini">
                    @error('current_password') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="update_password_password" class="prim-label">Kata Sandi Baru</label>
                    <input id="update_password_password" type="password" wire:model="password" name="password"
                        required autocomplete="new-password" class="prim-input" placeholder="Minimal 8 karakter">
                    @error('password') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="update_password_password_confirmation" class="prim-label">Konfirmasi Kata Sandi Baru</label>
                    <input id="update_password_password_confirmation" type="password" wire:model="password_confirmation"
                        name="password_confirmation" required autocomplete="new-password" class="prim-input"
                        placeholder="Ulangi kata sandi baru">
                    @error('password_confirmation') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-line-soft pt-5">
                <button type="submit" class="prim-btn px-7" wire:loading.attr="disabled">
                    <span wire:loading.remove>Simpan Kata Sandi</span>
                    <span wire:loading>Menyimpan…</span>
                </button>

                <x-action-message class="text-sm text-status-done-fg" on="password-updated">
                    Tersimpan.
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</div>
