<div>
    <flux:dropdown position="bottom" align="end">
        <button
            type="button"
            class="relative flex size-9 items-center justify-center rounded-full transition hover:bg-canvas"
            aria-label="Notifikasi"
        >
            <flux:icon.bell class="size-5 text-ink" />

            @if ($unreadCount > 0)
                <span class="absolute -top-0.5 -right-0.5 flex min-w-[18px] items-center justify-center rounded-full bg-status-cancel-fg px-1 text-[10px] font-medium text-white">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>

        <flux:menu class="w-[360px]">
            <div class="flex items-center justify-between gap-3 px-3 py-2">
                <div>
                    <p class="text-sm font-semibold text-ink-strong">Notifikasi</p>
                    <p class="text-xs text-muted">
                        {{ $unreadCount > 0 ? $unreadCount.' belum dibaca' : 'Semua sudah dibaca' }}
                    </p>
                </div>

                @if ($unreadCount > 0)
                    <button
                        type="button"
                        wire:click="tandaiSemua"
                        class="text-xs font-medium text-brand hover:underline"
                    >
                        Tandai semua
                    </button>
                @endif
            </div>

            <flux:menu.separator />

            @forelse ($items as $item)
                {{-- Klik menandai notifikasi dibaca, lalu Livewire mengarahkan
                     ke halaman tujuan karena buka() mengembalikan URL. --}}
                <flux:menu.item
                    as="button"
                    type="button"
                    wire:key="notif-{{ $item->id }}"
                    wire:click="buka({{ $item->id }})"
                >
                    <div class="flex w-full items-start gap-3 text-left">
                        <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg {{ $item->type->iconClasses() }}">
                            <flux:icon :name="$item->type->icon()" class="size-4" />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2">
                                <span @class([
                                    'truncate text-sm',
                                    'font-semibold text-ink-strong' => $item->isUnread(),
                                    'text-ink' => ! $item->isUnread(),
                                ])>{{ $item->title }}</span>

                                @if ($item->isUnread())
                                    <span class="size-2 shrink-0 rounded-full bg-brand"></span>
                                @endif
                            </span>

                            @if ($item->body)
                                <span class="mt-0.5 line-clamp-2 block text-xs leading-relaxed text-muted">
                                    {{ $item->body }}
                                </span>
                            @endif

                            <span class="mt-1 block text-[10px] text-muted-2">
                                {{ $item->created_at->diffForHumans() }}
                            </span>
                        </span>
                    </div>
                </flux:menu.item>
            @empty
                <div class="px-3 py-8 text-center">
                    <flux:icon.bell-slash class="mx-auto size-6 text-muted-2" />
                    <p class="mt-2 text-sm font-medium text-ink-strong">Belum ada notifikasi</p>
                    <p class="mt-0.5 text-xs text-muted">
                        Pemberitahuan pesanan dan kode login akan muncul di sini.
                    </p>
                </div>
            @endforelse
        </flux:menu>
    </flux:dropdown>
</div>
