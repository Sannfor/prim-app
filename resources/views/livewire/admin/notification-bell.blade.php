<div>
    {{-- Tombol lonceng beserta penghitung pesanan yang perlu ditangani --}}
    <flux:dropdown position="bottom" align="end">
        <button
            type="button"
            class="relative flex size-9 items-center justify-center rounded-full transition hover:bg-canvas"
            aria-label="Notifikasi pesanan"
        >
            <flux:icon.bell class="size-5 text-ink" />

            @if ($count > 0)
                <span class="absolute -top-0.5 -right-0.5 flex min-w-[18px] items-center justify-center rounded-full bg-status-cancel-fg px-1 text-[10px] font-medium text-white">
                    {{ $count > 99 ? '99+' : $count }}
                </span>
            @endif
        </button>

        <flux:menu class="w-[340px]">
            <div class="flex items-center justify-between px-3 py-2">
                <div>
                    <p class="text-sm font-semibold text-ink-strong">Pesanan Perlu Ditangani</p>
                    <p class="text-xs text-muted">{{ $today }}</p>
                </div>

                <span class="prim-badge {{ $count > 0 ? 'bg-status-cancel-bg text-status-cancel-fg' : 'bg-status-done-bg text-status-done-fg' }}">
                    {{ $count }}
                </span>
            </div>

            <flux:menu.separator />

            @forelse ($items as $item)
                <flux:menu.item
                    :href="route('admin.transactions.index')"
                    wire:navigate
                    wire:key="notif-{{ $item->id }}"
                >
                    <div class="flex w-full items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm text-ink-strong">
                                {{ $item->plan->service->name }}
                            </p>
                            <p class="truncate text-xs text-muted">
                                {{ $item->user->name }} · {{ $item->order_code }}
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <x-status-badge :status="$item->status" class="text-[10px]" />
                            <p class="mt-1 text-[10px] text-muted">{{ $item->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </flux:menu.item>
            @empty
                <div class="px-3 py-6 text-center">
                    <flux:icon.check-circle class="mx-auto size-6 text-status-done-fg" />
                    <p class="mt-2 text-sm font-medium text-ink-strong">Tidak ada yang tertunda</p>
                    <p class="mt-0.5 text-xs text-muted">Seluruh pesanan sudah ditangani.</p>
                </div>
            @endforelse

            @if ($count > 0)
                <flux:menu.separator />

                <flux:menu.item :href="route('admin.transactions.index')" icon="arrow-right" wire:navigate>
                    Lihat semua pesanan
                </flux:menu.item>
            @endif
        </flux:menu>
    </flux:dropdown>
</div>
