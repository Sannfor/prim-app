<div class="space-y-6">
    <div>
        <h1 class="font-display text-2xl font-medium text-ink-strong">Pengaturan</h1>
        <p class="mt-1 text-sm text-muted">Atur notifikasi yang ingin Anda terima.</p>
    </div>

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

    <form wire:submit="save" class="space-y-5">
        @foreach (['email' => 'Notifikasi Email', 'sistem' => 'Notifikasi Sistem'] as $groupKey => $groupLabel)
            <section class="rounded-xl bg-white p-6 shadow-brand-xs">
                <h2 class="font-display text-base font-semibold text-ink-strong">{{ $groupLabel }}</h2>

                <div class="mt-2 divide-y divide-line-soft">
                    @foreach ($grouped[$groupKey] as $key => $option)
                        <label
                            wire:key="notif-{{ $key }}"
                            class="flex cursor-pointer items-start justify-between gap-6 py-4"
                        >
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-ink-strong">{{ $option['label'] }}</span>
                                <span class="mt-0.5 block text-sm text-muted">{{ $option['description'] }}</span>
                            </span>

                            <span class="relative mt-0.5 inline-flex shrink-0">
                                <input
                                    type="checkbox"
                                    wire:model="preferences.{{ $key }}"
                                    class="peer sr-only"
                                >
                                <span class="h-6 w-11 rounded-full bg-line-soft transition peer-checked:bg-brand"></span>
                                <span class="absolute top-0.5 left-0.5 size-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="flex items-center gap-3">
            <button type="submit" class="prim-btn px-7" wire:loading.attr="disabled">
                <span wire:loading.remove>Simpan Perubahan</span>
                <span wire:loading>Menyimpan…</span>
            </button>

            <button type="button" wire:click="mount" class="text-sm text-muted hover:text-ink">Kembalikan</button>
        </div>
    </form>
</div>
