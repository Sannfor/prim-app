<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Kelola Transaksi</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            Pantau seluruh transaksi dan verifikasi pembayaran secara manual bila diperlukan.
        </p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500">Total pendapatan</p>
            <p class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                Rp{{ Number::format($summary['paid'], locale: 'id') }}
            </p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500">Menunggu pembayaran</p>
            <p class="mt-1 text-2xl font-bold">{{ $summary['pending'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500">Total transaksi</p>
            <p class="mt-1 text-2xl font-bold">{{ $summary['total'] }}</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <flux:input
            wire:model.live.debounce.400ms="search"
            icon="magnifying-glass"
            placeholder="Cari kode pesanan, nama, atau email…"
            class="max-w-sm"
            clearable
        />

        <flux:select wire:model.live="status" class="max-w-56">
            <flux:select.option value="">Semua status</flux:select.option>
            @foreach ($statuses as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 text-left dark:border-zinc-700 dark:bg-zinc-900">
                <tr>
                    <th class="p-3 font-medium">Kode Pesanan</th>
                    <th class="p-3 font-medium">Pengguna</th>
                    <th class="p-3 font-medium">Layanan &amp; Paket</th>
                    <th class="p-3 font-medium">Nominal</th>
                    <th class="p-3 font-medium">Status</th>
                    <th class="p-3 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr wire:key="trx-{{ $transaction->id }}" class="border-b border-zinc-100 last:border-0 dark:border-zinc-700">
                        <td class="p-3">
                            <p class="font-mono text-xs">{{ $transaction->order_code }}</p>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                {{ $transaction->created_at->translatedFormat('d M Y, H:i') }}
                            </p>
                        </td>
                        <td class="p-3">
                            <p class="font-medium">{{ $transaction->user->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $transaction->user->email }}</p>
                        </td>
                        <td class="p-3">
                            <p>{{ $transaction->plan->service->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $transaction->plan->name }}</p>
                        </td>
                        <td class="p-3 font-medium">{{ $transaction->formattedAmount() }}</td>
                        <td class="p-3">
                            <flux:badge size="sm" :color="$transaction->status->badgeColor()">
                                {{ $transaction->status->label() }}
                            </flux:badge>
                        </td>
                        <td class="p-3">
                            <div class="flex justify-end gap-1">
                                @if ($transaction->status !== \App\Enums\TransactionStatus::Paid)
                                    <flux:button
                                        wire:click="markAsPaid({{ $transaction->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="check-circle"
                                        title="Tandai berhasil"
                                    />
                                    <flux:button
                                        wire:click="markAsFailed({{ $transaction->id }})"
                                        variant="ghost"
                                        size="sm"
                                        icon="x-circle"
                                        title="Tandai gagal"
                                    />
                                @endif

                                <a
                                    href="{{ route('transaction.show', $transaction->order_code) }}"
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs text-zinc-500 hover:text-indigo-600"
                                    wire:navigate
                                >
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-zinc-500">Tidak ada transaksi yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $transactions->links() }}
    </div>
</div>
