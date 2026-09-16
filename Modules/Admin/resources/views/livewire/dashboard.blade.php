<div class="space-y-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Dashboard Pengelola</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Ringkasan kondisi katalog, transaksi, dan langganan PRIM saat ini.
            </p>
        </div>
        <flux:button :href="route('home')" variant="ghost" icon="arrow-top-right-on-square" wire:navigate>
            Lihat Situs Publik
        </flux:button>
    </div>

    {{-- Metrik utama --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($metrics as $key => $metric)
            <div wire:key="metric-{{ $key }}" class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm text-zinc-500">{{ $metric['label'] }}</p>
                    <flux:icon :name="$metric['icon']" class="size-5 text-indigo-500" />
                </div>
                <p class="mt-2 text-3xl font-bold">{{ Number::format($metric['value'], locale: 'id') }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $metric['hint'] }}</p>
            </div>
        @endforeach
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Pendapatan --}}
        <section class="rounded-xl border border-zinc-200 bg-white p-6 lg:col-span-1 dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="font-semibold">Pendapatan Bulan Ini</h2>

            <p class="mt-3 text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                Rp{{ Number::format($revenue['this_month'], locale: 'id') }}
            </p>

            <p class="mt-1 text-xs text-zinc-500">
                Bulan lalu: Rp{{ Number::format($revenue['last_month'], locale: 'id') }}
            </p>

            @if ($revenue['growth'] !== null)
                <p @class([
                    'mt-3 inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium',
                    'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' => $revenue['growth'] >= 0,
                    'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300' => $revenue['growth'] < 0,
                ])>
                    <flux:icon :name="$revenue['growth'] >= 0 ? 'arrow-trending-up' : 'arrow-trending-down'" class="size-3.5" />
                    {{ $revenue['growth'] >= 0 ? '+' : '' }}{{ $revenue['growth'] }}% dibanding bulan lalu
                </p>
            @else
                <p class="mt-3 text-xs text-zinc-500">Belum ada pembanding dari bulan lalu.</p>
            @endif
        </section>

        {{-- Diagram transaksi 14 hari --}}
        <section class="rounded-xl border border-zinc-200 bg-white p-6 lg:col-span-2 dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="font-semibold">Transaksi 14 Hari Terakhir</h2>

            <div class="mt-6 flex h-40 items-end gap-1.5">
                @foreach ($dailyTransactions as $day)
                    <div wire:key="day-{{ $day['date']->toDateString() }}" class="group flex flex-1 flex-col items-center gap-1">
                        <span class="text-[10px] font-medium text-zinc-500 opacity-0 transition group-hover:opacity-100">
                            {{ $day['count'] }}
                        </span>
                        <div
                            class="w-full rounded-t bg-indigo-500 transition hover:bg-indigo-600"
                            style="height: {{ $day['height'] }}%"
                            title="{{ $day['label'] }}: {{ $day['count'] }} transaksi"
                        ></div>
                    </div>
                @endforeach
            </div>

            <div class="mt-2 flex justify-between text-[10px] text-zinc-500">
                <span>{{ $dailyTransactions[0]['label'] }}</span>
                <span>{{ end($dailyTransactions)['label'] }}</span>
            </div>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Status transaksi --}}
        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="font-semibold">Komposisi Status Transaksi</h2>

            <div class="mt-4 space-y-3">
                @foreach ($statusBreakdown as $row)
                    <div wire:key="status-{{ $row['label'] }}">
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ $row['label'] }}</span>
                            <span class="font-medium">{{ $row['value'] }} ({{ $row['percentage'] }}%)</span>
                        </div>
                        <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ $row['percentage'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Layanan teratas --}}
        <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="font-semibold">Layanan dengan Pendapatan Tertinggi</h2>

            @if ($topServices->isEmpty())
                <p class="mt-3 text-sm text-zinc-500">Belum ada transaksi berhasil.</p>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($topServices as $service)
                        <div wire:key="top-{{ $service->name }}" class="flex items-center justify-between gap-3 text-sm">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ $service->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $service->total_transactions }} transaksi</p>
                            </div>
                            <p class="shrink-0 font-semibold text-indigo-600 dark:text-indigo-400">
                                Rp{{ Number::format((int) $service->revenue, locale: 'id') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- Langganan akan berakhir --}}
    @if ($expiringSubscriptions->isNotEmpty())
        <section class="rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-800 dark:bg-amber-950">
            <h2 class="font-semibold text-amber-900 dark:text-amber-200">Langganan Akan Berakhir (7 Hari)</h2>

            <div class="mt-4 space-y-2">
                @foreach ($expiringSubscriptions as $subscription)
                    <div wire:key="exp-{{ $subscription->id }}" class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white p-3 text-sm dark:bg-zinc-900">
                        <span class="font-medium">{{ $subscription->user->name }}</span>
                        <span class="text-zinc-600 dark:text-zinc-400">
                            {{ $subscription->plan->service->name }} — {{ $subscription->plan->name }}
                        </span>
                        <span class="text-amber-700 dark:text-amber-300">
                            {{ $subscription->daysRemaining() }} hari lagi
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Transaksi terbaru --}}
    <section class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center justify-between p-6 pb-4">
            <h2 class="font-semibold">Transaksi Terbaru</h2>
            <flux:button :href="route('admin.transactions.index')" variant="ghost" size="sm" wire:navigate>
                Lihat Semua
            </flux:button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-y border-zinc-200 bg-zinc-50 text-left dark:border-zinc-700 dark:bg-zinc-900">
                    <tr>
                        <th class="p-3 font-medium">Kode Pesanan</th>
                        <th class="p-3 font-medium">Pengguna</th>
                        <th class="p-3 font-medium">Layanan</th>
                        <th class="p-3 font-medium">Nominal</th>
                        <th class="p-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTransactions as $transaction)
                        <tr wire:key="trx-{{ $transaction->id }}" class="border-b border-zinc-100 last:border-0 dark:border-zinc-700">
                            <td class="p-3 font-mono text-xs">{{ $transaction->order_code }}</td>
                            <td class="p-3">{{ $transaction->user->name }}</td>
                            <td class="p-3">{{ $transaction->plan->service->name }}</td>
                            <td class="p-3">{{ $transaction->formattedAmount() }}</td>
                            <td class="p-3">
                                <flux:badge size="sm" :color="$transaction->status->badgeColor()">
                                    {{ $transaction->status->label() }}
                                </flux:badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-zinc-500">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
