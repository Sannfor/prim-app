<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-medium text-ink-strong">Riwayat Transaksi</h1>
            <p class="mt-1 text-sm text-muted">
                Seluruh pesanan layanan premium yang pernah Anda buat.
            </p>
        </div>

        <flux:select wire:model.live="status" class="max-w-56">
            <flux:select.option value="">Semua status</flux:select.option>
            @foreach ($statuses as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($transactions->isEmpty())
        <div class="rounded-xl border border-dashed border-line bg-white p-12 text-center">
            <flux:icon.receipt-percent class="mx-auto size-8 text-muted-2" />
            <p class="mt-3 font-medium">Belum ada transaksi</p>
            <p class="mt-1 text-sm text-muted">
                @if ($status !== '')
                    Tidak ada transaksi dengan status ini.
                @else
                    Mulai berlangganan layanan premium dari katalog PRIM.
                @endif
            </p>
            <flux:button :href="route('catalog.index')" variant="filled" class="mt-4" wire:navigate>
                Jelajahi Katalog
            </flux:button>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($transactions as $transaction)
                <article
                    wire:key="trx-{{ $transaction->id }}"
                    class="rounded-xl bg-canvas p-5"
                >
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold">{{ $transaction->plan->service->name }}</h2>
                                <flux:badge size="sm" :color="$transaction->status->badgeColor()">
                                    {{ $transaction->status->label() }}
                                </flux:badge>
                            </div>

                            <p class="mt-1 text-sm text-muted">
                                {{ $transaction->plan->name }} · {{ $transaction->plan->durationLabel() }}
                            </p>
                            <p class="mt-0.5 font-mono text-xs text-muted-2">{{ $transaction->order_code }}</p>
                        </div>

                        <div class="text-right">
                            <p class="font-bold text-brand">
                                {{ $transaction->formattedAmount() }}
                            </p>
                            <p class="mt-0.5 text-xs text-muted">
                                {{ $transaction->created_at->translatedFormat('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <flux:button
                            :href="route('transaction.show', $transaction->order_code)"
                            variant="ghost"
                            size="sm"
                            icon="eye"
                            wire:navigate
                        >
                            Lihat Detail
                        </flux:button>

                        @if ($transaction->isPayable())
                            <flux:button
                                :href="route('transaction.show', $transaction->order_code)"
                                variant="filled"
                                size="sm"
                                icon="credit-card"
                                wire:navigate
                            >
                                Bayar Sekarang
                            </flux:button>
                        @endif

                        @if ($transaction->subscription)
                            <span class="text-xs text-muted">
                                Langganan aktif hingga
                                {{ $transaction->subscription->ends_at->translatedFormat('d M Y') }}
                            </span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div>
            {{ $transactions->links() }}
        </div>
    @endif
</div>
