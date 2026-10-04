<div class="space-y-7">
    <div>
        <h1 class="font-display text-2xl font-medium text-ink-strong">Manajemen Pembayaran</h1>
        <p class="mt-1 text-sm text-muted">Rekap pendapatan dan verifikasi pembayaran per kanal.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="rounded-xl bg-canvas p-5">
                <p class="text-sm text-muted">{{ $card['label'] }}</p>
                <p class="mt-1.5 font-display text-xl font-medium text-ink-strong">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-muted">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <select wire:model.live="channel" class="prim-input h-10 max-w-56 text-sm">
            <option value="">Semua metode</option>
            @foreach ($channels as $item)
                <option value="{{ $item }}">{{ $item }}</option>
            @endforeach
        </select>

        <select wire:model.live="status" class="prim-input h-10 max-w-56 text-sm">
            <option value="">Semua status</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>

        <div wire:loading class="text-xs text-muted">Memuat…</div>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-brand-xs">
        <table class="w-full min-w-[860px] text-sm">
            <thead class="prim-table-head bg-canvas">
                <tr>
                    <th class="px-4 py-3">ID Transaksi</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3">Metode</th>
                    <th class="px-4 py-3">Tanggal &amp; Waktu</th>
                    <th class="px-4 py-3">Jumlah</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr wire:key="pay-{{ $transaction->id }}" class="border-b border-line-soft last:border-0 hover:bg-canvas/60">
                        <td class="px-4 py-3 font-mono text-xs">{{ $transaction->order_code }}</td>
                        <td class="px-4 py-3">
                            <p class="text-ink">{{ $transaction->user->name }}</p>
                            <p class="text-xs text-muted">{{ $transaction->plan->service->name }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink">{{ $transaction->paymentLabel() }}</td>
                        <td class="px-4 py-3 text-xs text-muted whitespace-nowrap">
                            {{ $transaction->created_at->translatedFormat('d M Y') }}
                            <span class="block">{{ $transaction->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium whitespace-nowrap">{{ $transaction->formattedAmount() }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$transaction->status" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-muted">Belum ada data pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $transactions->links() }}</div>
</div>
