<div class="space-y-7">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-medium text-ink-strong">Dashboard Admin</h1>
            <p class="mt-1 text-sm text-muted">Ringkasan kondisi katalog, pesanan, dan langganan PRIM.</p>
        </div>

        <a href="{{ route('home') }}" class="prim-btn-ghost" wire:navigate>
            <flux:icon.arrow-top-right-on-square class="size-4" />
            Lihat Situs Publik
        </a>
    </div>

    {{-- Kartu metrik --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($metrics as $key => $metric)
            <div wire:key="metric-{{ $key }}" class="rounded-xl bg-canvas p-5">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm text-muted">{{ $metric['label'] }}</p>
                    <span class="flex size-9 items-center justify-center rounded-lg bg-white">
                        <flux:icon :name="$metric['icon']" class="size-4 text-brand" />
                    </span>
                </div>
                <p class="mt-2 font-display text-xl font-medium text-ink-strong">
                    {{ Number::format($metric['value'], locale: 'id') }}
                </p>
                <p class="mt-1 text-xs text-muted">{{ $metric['hint'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- Pendapatan + diagram 14 hari --}}
    <div class="grid gap-5 lg:grid-cols-3">
        <section class="rounded-xl bg-white p-6 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-ink-strong">Pendapatan Bulan Ini</h2>

            <p class="mt-3 font-display text-2xl font-bold text-brand">
                Rp{{ Number::format($revenue['this_month'], locale: 'id') }}
            </p>

            <p class="mt-1 text-xs text-muted">
                Bulan lalu: Rp{{ Number::format($revenue['last_month'], locale: 'id') }}
            </p>

            @if ($revenue['growth'] !== null)
                <p @class([
                    'mt-4 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium',
                    'bg-status-done-bg text-status-done-fg' => $revenue['growth'] >= 0,
                    'bg-status-cancel-bg text-status-cancel-fg' => $revenue['growth'] < 0,
                ])>
                    <flux:icon :name="$revenue['growth'] >= 0 ? 'arrow-trending-up' : 'arrow-trending-down'" class="size-3.5" />
                    {{ $revenue['growth'] >= 0 ? '+' : '' }}{{ $revenue['growth'] }}% dibanding bulan lalu
                </p>
            @else
                <p class="mt-4 text-xs text-muted">Belum ada pembanding dari bulan lalu.</p>
            @endif
        </section>

        <section class="rounded-xl bg-white p-6 shadow-brand-xs lg:col-span-2">
            <h2 class="font-display text-base font-semibold text-ink-strong">Pesanan 14 Hari Terakhir</h2>

            <div class="mt-8 flex h-40 items-end gap-1.5">
                @foreach ($dailyTransactions as $day)
                    <div wire:key="day-{{ $day['date']->toDateString() }}" class="group flex flex-1 flex-col items-center gap-1">
                        <span class="text-[10px] font-medium text-muted opacity-0 transition group-hover:opacity-100">
                            {{ $day['count'] }}
                        </span>
                        <div
                            class="w-full rounded-t bg-brand transition hover:bg-brand-deep"
                            style="height: {{ max(2, $day['height']) }}%"
                            title="{{ $day['label'] }}: {{ $day['count'] }} pesanan"
                        ></div>
                    </div>
                @endforeach
            </div>

            <div class="mt-2 flex justify-between text-[10px] text-muted">
                <span>{{ $dailyTransactions[0]['label'] }}</span>
                <span>{{ end($dailyTransactions)['label'] }}</span>
            </div>
        </section>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        {{-- Komposisi status --}}
        <section class="rounded-xl bg-white p-6 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-ink-strong">Komposisi Status Pesanan</h2>

            <div class="mt-5 space-y-4">
                @foreach ($statusBreakdown as $row)
                    <div wire:key="status-{{ $row['label'] }}">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink">{{ $row['label'] }}</span>
                            <span class="text-muted">{{ $row['value'] }} · {{ $row['percentage'] }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-canvas">
                            <div class="h-full rounded-full bg-brand" style="width: {{ max(1, $row['percentage']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Produk terlaris --}}
        <section class="rounded-xl bg-white p-6 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-ink-strong">Produk Terlaris</h2>

            @if ($topServices->isEmpty())
                <p class="mt-4 text-sm text-muted">Belum ada transaksi berhasil.</p>
            @else
                @php $peak = max(1, (int) $topServices->max('total_transactions')); @endphp

                <div class="mt-5 space-y-4">
                    @foreach ($topServices as $service)
                        <div wire:key="top-{{ $service->name }}">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="truncate text-ink">{{ $service->name }}</span>
                                <span class="shrink-0 text-muted">{{ $service->total_transactions }}</span>
                            </div>
                            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-canvas">
                                <div
                                    class="h-full rounded-full bg-brand"
                                    style="width: {{ max(2, round(($service->total_transactions / $peak) * 100)) }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- Langganan akan berakhir --}}
    @if ($expiringSubscriptions->isNotEmpty())
        <section class="rounded-xl bg-status-wait-bg p-6">
            <h2 class="font-display text-base font-semibold text-status-wait-fg">Langganan Akan Berakhir (7 Hari)</h2>

            <div class="mt-4 space-y-2">
                @foreach ($expiringSubscriptions as $subscription)
                    <div wire:key="exp-{{ $subscription->id }}" class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white p-3 text-sm">
                        <span class="font-medium text-ink-strong">{{ $subscription->user->name }}</span>
                        <span class="text-muted">
                            {{ $subscription->plan->service->name }} — {{ $subscription->plan->groupLabel() }}
                        </span>
                        <span class="text-status-wait-fg">{{ $subscription->daysRemaining() }} hari lagi</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Pesanan terbaru --}}
    <section class="rounded-xl bg-white shadow-brand-xs">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-display text-base font-semibold text-ink-strong">Pesanan Terbaru</h2>
            <a href="{{ route('admin.transactions.index') }}" class="text-sm text-brand hover:underline" wire:navigate>
                Lihat semua ↗
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="prim-table-head bg-canvas">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Pelanggan</th>
                        <th class="px-6 py-3">Produk</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTransactions as $transaction)
                        <tr wire:key="trx-{{ $transaction->id }}" class="border-b border-line-soft last:border-0">
                            <td class="px-6 py-3 font-mono text-xs">{{ $transaction->order_code }}</td>
                            <td class="px-6 py-3 text-ink">{{ $transaction->user->name }}</td>
                            <td class="px-6 py-3 text-ink">
                                {{ $transaction->plan->service->name }}
                                <span class="text-xs text-muted">{{ $transaction->plan->groupLabel() }}</span>
                            </td>
                            <td class="px-6 py-3 font-medium whitespace-nowrap">{{ $transaction->formattedAmount() }}</td>
                            <td class="px-6 py-3"><x-status-badge :status="$transaction->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-muted">Belum ada pesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
