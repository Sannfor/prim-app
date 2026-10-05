<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Hapus akun pengguna yang sedang masuk beserta seluruh datanya.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="rounded-xl border border-status-cancel-bg bg-status-cancel-bg/40 p-6">
    <h2 class="font-display text-base font-semibold text-status-cancel-fg">Hapus Akun</h2>
    <p class="mt-1 text-sm text-muted">
        Menghapus akun akan menghapus seluruh pesanan, langganan, dan kredensial milikmu
        secara permanen. Tindakan ini tidak dapat dibatalkan.
    </p>

    <flux:modal.trigger name="confirm-user-deletion">
        <button
            type="button"
            class="mt-4 inline-flex items-center gap-2 rounded-full bg-status-cancel-fg px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-90"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >
            <flux:icon.trash class="size-4" />
            Hapus Akun Saya
        </button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-5">
            <div>
                <flux:heading size="lg">Yakin ingin menghapus akun ini?</flux:heading>

                <flux:subheading>
                    Seluruh data akun akan dihapus permanen. Masukkan kata sandimu untuk
                    mengonfirmasi tindakan ini.
                </flux:subheading>
            </div>

            <div>
                <label for="password" class="prim-label">Kata Sandi</label>
                <input id="password" type="password" wire:model="password" name="password" class="prim-input"
                    placeholder="Masukkan kata sandi">
                @error('password') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <button type="button" class="prim-btn-ghost h-10">Batal</button>
                </flux:modal.close>

                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-status-cancel-fg px-5 py-2.5 text-sm font-medium text-white">
                    Hapus Permanen
                </button>
            </div>
        </form>
    </flux:modal>
</section>
