<div class="space-y-7">
    <div>
        <h1 class="font-display text-2xl font-medium text-ink-strong">Manajemen Pesanan</h1>
        <p class="mt-1 text-sm text-muted">
            Pantau seluruh pesanan dan verifikasi pembayaran secara manual bila diperlukan.
        </p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <div class="rounded-xl bg-canvas p-5">
                <p class="text-sm text-muted">{{ $card['label'] }}</p>
                <p class="mt-1.5 font-display text-xl font-medium text-ink-strong">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-muted">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Penyaring --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex flex-wrap gap-1.5">
            <button
                type="button"
                wire:click="$set('status', '')"
                @class([
                    'rounded-full px-4 py-1.5 text-sm transition',
                    'bg-brand text-white' => $status === '',
                    'bg-canvas text-ink hover:bg-brand-soft' => $status !== '',
                ])
            >Semua</button>

            @foreach (['paid' => 'Selesai', 'processed' => 'Diproses', 'pending' => 'Menunggu', 'cancelled' => 'Dibatalkan'] as $value => $label)
                <button
                    type="button"
                    wire:click="$set('status', '{{ $value }}')"
                    @class([
                        'rounded-full px-4 py-1.5 text-sm transition',
                        'bg-brand text-white' => $status === $value,
                        'bg-canvas text-ink hover:bg-brand-soft' => $status !== $value,
                    ])
                >{{ $label }}</button>
            @endforeach
        </div>

        <div class="ml-auto w-full max-w-xs">
            <input
                type="search"
                wire:model.live.debounce.400ms="search"
                placeholder="Cari ID atau nama pelanggan…"
                class="prim-input h-10 text-sm"
            >
        </div>
    </div>

    {{-- Tabel pesanan --}}
    <div class="overflow-x-auto rounded-xl bg-white shadow-brand-xs">
        <table class="w-full min-w-[900px] text-sm">
            <thead class="prim-table-head bg-canvas">
                <tr>
                    <th class="px-4 py-3">ID Pesanan</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Metode Pembayaran</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr wire:key="trx-{{ $transaction->id }}" class="border-b border-line-soft last:border-0 hover:bg-canvas/60">
                        <td class="px-4 py-3 font-mono text-xs text-ink">
                            {{ $transaction->order_code }}
                        </td>
                        <td class="px-4 py-3 text-xs text-muted whitespace-nowrap">
                            {{ $transaction->created_at->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-ink">{{ $transaction->user->name }}</p>
                            <p class="text-xs text-muted">{{ $transaction->user->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-ink">{{ $transaction->plan->service->name }}</p>
                            <p class="text-xs text-muted">{{ $transaction->plan->groupLabel() }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink">{{ $transaction->paymentLabel() }}</td>
                        <td class="px-4 py-3 font-medium text-ink whitespace-nowrap">{{ $transaction->formattedAmount() }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$transaction->status" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                @unless ($transaction->status === \App\Enums\TransactionStatus::Paid)
                                    <button
                                        type="button"
                                        wire:click="markAsPaid({{ $transaction->id }})"
                                        class="rounded-md p-1.5 text-status-done-fg hover:bg-status-done-bg"
                                        title="Tandai selesai"
                                    >
                                        <flux:icon.check-circle class="size-4" />
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="markAsFailed({{ $transaction->id }})"
                                        class="rounded-md p-1.5 text-status-cancel-fg hover:bg-status-cancel-bg"
                                        title="Tandai gagal"
                                    >
                                        <flux:icon.x-circle class="size-4" />
                                    </button>
                                @endunless

                                <a
                                    href="{{ route('transaction.show', $transaction->order_code) }}"
                                    class="rounded-md p-1.5 text-muted hover:bg-canvas hover:text-brand"
                                    title="Lihat rincian"
                                    wire:navigate
                                >
                                    <flux:icon.eye class="size-4" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-muted">Tidak ada pesanan yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $transactions->links() }}</div>
</div>
