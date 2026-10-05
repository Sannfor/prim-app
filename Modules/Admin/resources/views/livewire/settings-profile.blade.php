<div class="space-y-6">
    {{-- Kepala halaman --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink-strong">Pengaturan Pengelola</h1>
            <p class="mt-1 text-sm text-muted">Kelola identitas dan preferensi akun pengelola.</p>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="prim-btn-ghost h-10" wire:navigate>
            <flux:icon.arrow-left class="size-4" />
            Kembali ke Dashboard
        </a>
    </div>

    {{-- Tab pengaturan --}}
    <div class="flex flex-wrap gap-1.5">
        <a
            href="{{ route('admin.settings.profile') }}"
            @class([
                'rounded-full px-4 py-1.5 text-sm transition',
                'bg-brand text-white' => request()->routeIs('admin.settings.profile'),
                'bg-white text-ink hover:bg-brand-soft' => ! request()->routeIs('admin.settings.profile'),
            ])
            wire:navigate
        >
            <span class="inline-flex items-center gap-2">
                <flux:icon.user-circle class="size-4" />
                Profile Admin
            </span>
        </a>

        <a
            href="{{ route('admin.settings.notifications') }}"
            @class([
                'rounded-full px-4 py-1.5 text-sm transition',
                'bg-brand text-white' => request()->routeIs('admin.settings.notifications'),
                'bg-white text-ink hover:bg-brand-soft' => ! request()->routeIs('admin.settings.notifications'),
            ])
            wire:navigate
        >
            <span class="inline-flex items-center gap-2">
                <flux:icon.bell class="size-4" />
                Notifikasi
            </span>
        </a>
    </div>

    <div class="grid gap-5 lg:grid-cols-[300px_minmax(0,1fr)]">
        {{-- Kartu identitas --}}
        <aside class="space-y-4">
            <div class="rounded-xl bg-white p-6 shadow-brand-xs">
                <div class="flex flex-col items-center text-center">
                    <span class="flex size-24 items-center justify-center rounded-2xl bg-gradient-to-br from-brand to-brand-violet font-display text-3xl font-bold text-white">
                        {{ $user->initials() }}
                    </span>

                    <p class="mt-4 font-display text-lg font-bold text-ink-strong">{{ $user->name }}</p>
                    <p class="mt-0.5 text-sm text-muted">{{ $user->email }}</p>

                    <span class="prim-badge mt-3 bg-status-new-bg text-status-new-fg">{{ $user->role->label() }}</span>
                </div>

                <dl class="mt-5 space-y-3 border-t border-line-soft pt-4 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">No. Telepon</dt>
                        <dd class="truncate text-ink">{{ $user->phone ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Status</dt>
                        <dd><x-status-badge :status="$user->statusEnum()" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Login Terakhir</dt>
                        <dd class="text-ink">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">Akun Dibuat</dt>
                        <dd class="text-ink">{{ $user->created_at->translatedFormat('d M Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl bg-brand-soft p-5">
                <p class="flex items-start gap-2.5 text-xs leading-relaxed text-brand">
                    <flux:icon.shield-check class="mt-0.5 size-4 shrink-0" />
                    Perubahan pada halaman ini memengaruhi akun yang sedang Anda pakai untuk masuk ke panel pengelola.
                </p>
            </div>
        </aside>

        {{-- Form informasi pribadi --}}
        <form wire:submit="save" class="rounded-xl bg-white p-6 shadow-brand-xs">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-display text-base font-semibold text-ink-strong">Informasi Pribadi</h2>
                    <p class="mt-1 text-xs text-muted">Perbarui data identitas akun pengelola.</p>
                </div>

                <span class="hidden rounded-full bg-canvas px-3 py-1 text-xs text-muted sm:block">
                    Terakhir diubah {{ $user->updated_at->diffForHumans() }}
                </span>
            </div>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="prim-label">Nama Lengkap</label>
                    <input id="name" type="text" wire:model="name" class="prim-input" placeholder="Nama pengelola">
                    @error('name') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="prim-label">Email</label>
                    <input id="email" type="email" wire:model="email" class="prim-input" placeholder="nama@prim.com">
                    @error('email') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="prim-label">Nomor Telepon</label>
                    <input id="phone" type="text" wire:model="phone" class="prim-input" placeholder="+62 ...">
                    @error('phone') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="prim-label">Alamat</label>
                    <input id="address" type="text" wire:model="address" class="prim-input" placeholder="Kota, provinsi">
                    @error('address') <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="role" class="prim-label">Peran</label>
                    <input id="role" type="text" value="{{ $user->role->label() }}" class="prim-input bg-canvas text-muted" disabled>
                    <p class="mt-1.5 text-xs text-muted">Peran akun tidak dapat diubah dari halaman ini.</p>
                </div>
            </div>

            <div class="mt-7 flex flex-wrap items-center gap-3 border-t border-line-soft pt-5">
                <button type="submit" class="prim-btn px-7" wire:loading.attr="disabled">
                    <span wire:loading.remove>Simpan Perubahan</span>
                    <span wire:loading>Menyimpan…</span>
                </button>

                <button type="button" wire:click="mount" class="prim-btn-ghost h-10">
                    Batalkan
                </button>
            </div>
        </form>
    </div>
</div>
