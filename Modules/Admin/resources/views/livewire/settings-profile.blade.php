<div class="space-y-6">
    <div>
        <h1 class="font-display text-2xl font-medium text-ink-strong">Pengaturan</h1>
        <p class="mt-1 text-sm text-muted">Kelola profil dan preferensi akun pengelola.</p>
    </div>

    {{-- Tab pengaturan --}}
    <div class="flex flex-wrap gap-1.5">
        <a
            href="{{ route('admin.settings.profile') }}"
            @class([
                'rounded-full px-4 py-1.5 text-sm transition',
                'bg-brand text-white' => request()->routeIs('admin.settings.profile'),
                'bg-canvas text-ink hover:bg-brand-soft' => ! request()->routeIs('admin.settings.profile'),
            ])
            wire:navigate
        >Profile Admin</a>

        <a
            href="{{ route('admin.settings.notifications') }}"
            @class([
                'rounded-full px-4 py-1.5 text-sm transition',
                'bg-brand text-white' => request()->routeIs('admin.settings.notifications'),
                'bg-canvas text-ink hover:bg-brand-soft' => ! request()->routeIs('admin.settings.notifications'),
            ])
            wire:navigate
        >Notifikasi</a>
    </div>

    <div class="grid gap-5 lg:grid-cols-[300px_minmax(0,1fr)]">
        {{-- Kartu identitas --}}
        <div class="rounded-xl bg-white p-6 shadow-brand-xs">
            <div class="flex flex-col items-center text-center">
                <span class="flex size-20 items-center justify-center rounded-full bg-brand text-2xl font-medium text-white">
                    {{ $user->initials() }}
                </span>

                <p class="mt-4 font-display text-lg font-semibold text-ink-strong">{{ $user->name }}</p>
                <p class="text-sm text-muted">{{ $user->email }}</p>

                <span class="prim-badge mt-3 bg-status-new-bg text-status-new-fg">{{ $user->role->label() }}</span>
            </div>
        </div>

        {{-- Form informasi pribadi --}}
        <form wire:submit="save" class="rounded-xl bg-white p-6 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-ink-strong">Informasi Pribadi</h2>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="prim-label">NAMA LENGKAP</label>
                    <input id="name" type="text" wire:model="name" class="prim-input">
                    @error('name') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="prim-label">EMAIL</label>
                    <input id="email" type="email" wire:model="email" class="prim-input">
                    @error('email') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="prim-label">NO. TELEPON</label>
                    <input id="phone" type="text" wire:model="phone" class="prim-input">
                    @error('phone') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="prim-label">ALAMAT</label>
                    <input id="address" type="text" wire:model="address" class="prim-input">
                    @error('address') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="prim-label">ROLE</label>
                    <input type="text" value="{{ $user->role->label() }}" class="prim-input bg-canvas" disabled>
                </div>
            </div>

            <div class="mt-7 flex items-center gap-3">
                <button type="submit" class="prim-btn px-7" wire:loading.attr="disabled">
                    <span wire:loading.remove>Simpan Perubahan</span>
                    <span wire:loading>Menyimpan…</span>
                </button>

                <button type="button" wire:click="mount" class="text-sm text-muted hover:text-ink">Batal</button>
            </div>
        </form>
    </div>
</div>
