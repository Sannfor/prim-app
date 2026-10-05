<div class="space-y-7">
    <div>
        <h1 class="font-display text-2xl font-medium text-ink-strong">Laporan Admin</h1>
        <p class="mt-1 text-sm text-muted">Ringkasan kinerja penjualan, pengguna, dan katalog PRIM.</p>
    </div>

    {{-- Kartu ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="rounded-xl bg-canvas p-5">
                <p class="text-sm text-muted">{{ $card['label'] }}</p>
                <p class="mt-1.5 font-display text-xl font-medium text-ink-strong">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-muted">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Insight bulan ini --}}
    <section class="rounded-xl bg-white p-6 shadow-brand-xs">
        <h2 class="font-display text-base font-semibold text-ink-strong">Insight Bulan Ini</h2>

        <div class="mt-5 grid gap-5 sm:grid-cols-3">
            @foreach ($insights as $insight)
                <div class="rounded-xl bg-canvas p-4">
                    <p @class([
                        'font-display text-2xl font-bold',
                        'text-status-done-fg' => $insight['positive'],
                        'text-status-cancel-fg' => ! $insight['positive'],
                    ])>
                        {{ $insight['value'] === null ? $insight['text'] : (($insight['value'] >= 0 ? '+' : '').$insight['value'].'%') }}
                    </p>
                    <p class="mt-1 text-sm font-medium text-ink-strong">{{ $insight['text'] }}</p>
                    <p class="text-xs text-muted">Dibandingkan bulan lalu</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Diagram lingkaran status pesanan --}}
    <div class="grid gap-5 lg:grid-cols-[minmax(0,340px)_minmax(0,1fr)]">
        <section class="rounded-xl bg-white p-6 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-ink-strong">Diagram Lingkaran Status</h2>
            <p class="mt-1 text-xs text-muted">Proporsi pesanan menurut status saat ini.</p>

            @php
                $warna = [
                    'done' => '#3b6d11',
                    'process' => '#0c447c',
                    'wait' => '#854f0b',
                    'cancel' => '#e24b4a',
                    'new' => '#534ab7',
                ];

                $irisan = collect($pie)->map(
                    fn ($row) => $warna[$row['tone']].' '.$row['dari'].'% '.$row['sampai'].'%'
                )->implode(', ');
            @endphp

            <div class="mt-5 flex flex-col items-center gap-6 sm:flex-row sm:items-center">
                {{-- Diagram donat: dibuat dari gradien kerucut agar tidak perlu pustaka grafik --}}
                <div class="relative size-[164px] shrink-0">
                    <div
                        class="size-full rounded-full"
                        style="background: conic-gradient({{ $irisan }});"
                        role="img"
                        aria-label="Komposisi status pesanan"
                    ></div>
                    <div class="absolute inset-[26%] flex flex-col items-center justify-center rounded-full bg-white">
                        <span class="font-display text-xl font-bold text-ink-strong">{{ array_sum(array_column($composition, 'count')) }}</span>
                        <span class="text-[11px] text-muted">pesanan</span>
                    </div>
                </div>

                {{-- Keterangan --}}
                <ul class="w-full space-y-2.5">
                    @foreach ($composition as $row)
                        @continue($row['count'] <= 0)

                        <li class="flex items-center gap-3 text-sm">
                            <span class="size-3 shrink-0 rounded-sm" style="background: {{ $warna[$row['tone']] ?? '#534ab7' }}"></span>
                            <span class="flex-1 text-ink">{{ $row['label'] }}</span>
                            <span class="text-muted">{{ $row['count'] }}</span>
                            <span class="w-12 text-right font-medium text-ink-strong">{{ $row['percentage'] }}%</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- Statistik bulan berjalan --}}
        <section class="rounded-xl bg-white p-6 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-ink-strong">Statistik Bulan Ini</h2>
            <p class="mt-1 text-xs text-muted">Angka dihitung dari pesanan pada bulan berjalan.</p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach ($statistics as $stat)
                    <div class="rounded-xl bg-canvas p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs text-muted">{{ $stat['label'] }}</p>
                            <span class="flex size-8 items-center justify-center rounded-lg bg-white">
                                <flux:icon :name="$stat['icon']" class="size-4 text-brand" />
                            </span>
                        </div>
                        <p class="mt-2 font-display text-lg font-bold text-ink-strong">{{ $stat['value'] }}</p>
                        <p class="mt-0.5 text-[11px] leading-relaxed text-muted">{{ $stat['hint'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Diagram pendapatan bulanan --}}
    <section class="rounded-xl bg-white p-6 shadow-brand-xs">
        <h2 class="font-display text-base font-semibold text-ink-strong">Pendapatan Bulanan</h2>
        <p class="mt-1 text-xs text-muted">Enam bulan terakhir, dalam rupiah.</p>

        <div class="mt-8 flex h-52 items-end gap-4">
            @foreach ($monthly as $month)
                {{-- h-full wajib: tanpa itu tinggi persentase dihitung terhadap
                     induk setinggi otomatis sehingga batang tidak terlihat. --}}
                <div class="group flex h-full flex-1 flex-col items-center justify-end gap-2">
                    <span class="text-[11px] text-muted opacity-0 transition group-hover:opacity-100">
                        Rp{{ Number::format($month['value'], locale: 'id') }}
                    </span>
                    <div
                        class="w-full max-w-[54px] rounded-t-md bg-brand transition group-hover:bg-brand-deep"
                        style="height: {{ max(2, round(($month['value'] / $monthlyPeak) * 100)) }}%"
                        title="{{ $month['label'] }}: Rp{{ Number::format($month['value'], locale: 'id') }}"
                    ></div>
                    <span class="text-xs text-muted">{{ $month['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        {{-- Rincian status dalam bentuk bilah --}}
        <section class="rounded-xl bg-white p-6 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-ink-strong">Rincian Status Pesanan</h2>
            <p class="mt-1 text-xs text-muted">Jumlah dan persentase tiap status pesanan.</p>

            @php
                $tones = [
                    'done' => 'bg-status-done-fg',
                    'process' => 'bg-status-process-fg',
                    'wait' => 'bg-status-wait-fg',
                    'cancel' => 'bg-status-cancel-fg',
                    'new' => 'bg-status-new-fg',
                ];
            @endphp

            <div class="mt-5 space-y-4">
                @foreach ($composition as $row)
                    <div wire:key="comp-{{ $row['label'] }}">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink">{{ $row['label'] }}</span>
                            <span class="text-muted">{{ $row['count'] }} · {{ $row['percentage'] }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-canvas">
                            <div
                                class="h-full rounded-full {{ $tones[$row['tone']] ?? 'bg-brand' }}"
                                style="width: {{ max(1, $row['percentage']) }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Produk terlaris --}}
        <section class="rounded-xl bg-white p-6 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-ink-strong">Produk Terlaris</h2>

            <div class="mt-5 space-y-4">
                @forelse ($topServices as $service)
                    <div wire:key="top-{{ $service->id }}">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="truncate text-ink">{{ $service->name }}</span>
                            <span class="shrink-0 text-muted">{{ $service->total_transactions }}</span>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-canvas">
                            <div
                                class="h-full rounded-full bg-brand"
                                style="width: {{ max(2, round(($service->total_transactions / $topServicePeak) * 100)) }}%"
                            ></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-muted">Belum ada data penjualan.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Pesanan terbaru --}}
    <section class="rounded-xl bg-white shadow-brand-xs">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-display text-base font-semibold text-ink-strong">Pesanan Terbaru</h2>
            <a href="{{ route('admin.transactions.index') }}" class="text-sm text-brand hover:underline" wire:navigate>
                Lihat semua ↗
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="prim-table-head bg-canvas">
                    <tr>
                        <th class="px-6 py-3">ID Pesanan</th>
                        <th class="px-6 py-3">Pelanggan</th>
                        <th class="px-6 py-3">Produk</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $transaction)
                        <tr wire:key="rep-{{ $transaction->id }}" class="border-b border-line-soft last:border-0">
                            <td class="px-6 py-3 font-mono text-xs">{{ $transaction->order_code }}</td>
                            <td class="px-6 py-3">{{ $transaction->user->name }}</td>
                            <td class="px-6 py-3">
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
