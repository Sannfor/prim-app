<div class="min-w-0">
    {{-- Kepala halaman --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink-strong">Langganan Saya</h1>
            <p class="mt-1 text-sm text-muted">
                Pantau masa aktif layanan premiummu dan perpanjang sebelum berakhir.
            </p>
        </div>

        <a href="{{ route('catalog.index') }}" class="prim-btn-ghost h-10" wire:navigate>
            <flux:icon.squares-2x2 class="size-4" />
            Tambah Langganan
        </a>
    </div>

    {{-- Ringkasan --}}
    <div class="mt-5 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-brand-xs">
            <p class="text-xs tracking-wide text-muted uppercase">Langganan Aktif</p>
            <p class="mt-1.5 font-display text-2xl font-bold text-ink-strong">{{ $activeCount }}</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-brand-xs">
            <p class="text-xs tracking-wide text-muted uppercase">Akan Berakhir</p>
            <p @class([
                'mt-1.5 font-display text-2xl font-bold',
                'text-status-wait-fg' => $expiringSoonCount > 0,
                'text-ink-strong' => $expiringSoonCount === 0,
            ])>{{ $expiringSoonCount }}</p>
            <p class="mt-1 text-xs text-muted">Dalam 7 hari ke depan</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-brand-xs">
            <p class="text-xs tracking-wide text-muted uppercase">Total Langganan</p>
            <p class="mt-1.5 font-display text-2xl font-bold text-ink-strong">{{ $subscriptions->count() }}</p>
        </div>
    </div>

    {{-- Penyaring --}}
    <div class="mt-5 flex flex-wrap gap-1.5">
        @foreach ($filters as $key => $label)
            <button
                type="button"
                wire:key="filter-{{ $key ?: 'semua' }}"
                wire:click="$set('filter', '{{ $key }}')"
                @class([
                    'rounded-full px-3.5 py-1.5 text-sm transition',
                    'bg-brand text-white' => $filter === $key,
                    'bg-white text-ink hover:bg-brand-soft' => $filter !== $key,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($subscriptions->isEmpty())
        <div class="mt-5 rounded-xl bg-white p-12 text-center shadow-brand-xs">
            <flux:icon.arrow-path-rounded-square class="mx-auto size-9 text-muted-2" />
            <p class="mt-3 font-medium text-ink-strong">Tidak ada langganan pada kategori ini</p>
            <p class="mt-1 text-sm text-muted">Mulai berlangganan layanan premium dari katalog PRIM.</p>
            <a href="{{ route('catalog.index') }}" class="prim-btn mt-5" wire:navigate>Jelajahi Katalog</a>
        </div>
    @else
        <div class="mt-5 space-y-4">
            @foreach ($subscriptions as $subscription)
                @php
                    $service = $subscription->plan->service;
                    $isActive = $subscription->isActive();
                    $days = $subscription->daysRemaining();

                    // Persentase masa aktif dihitung dari selisih hari. Nilainya
                    // diberi batas bawah 1% agar bilah tetap terlihat pada
                    // langganan yang baru dimulai.
                    $selesai = max(0, (int) $subscription->started_at->diffInDays(now(), false));
                    $totalHari = max(1, (int) $subscription->started_at->diffInDays($subscription->ends_at));
                    $terpakai = min(100, max(1, (int) round(($selesai / $totalHari) * 100)));
                @endphp

                <article wire:key="sub-{{ $subscription->id }}" class="rounded-xl bg-white p-5 shadow-brand-xs">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <x-service-logo :service="$service" class="h-12 w-20 shrink-0" />

                            <div class="min-w-0">
                                <p class="font-display text-base font-semibold text-ink-strong">{{ $service->name }}</p>
                                <p class="text-xs text-muted">
                                    {{ $subscription->plan->groupLabel() }} &middot;
                                    {{ $subscription->plan->durationLabel() }}
                                </p>
                                <p class="mt-0.5 text-xs text-muted">{{ $service->provider->name }}</p>
                            </div>
                        </div>

                        <div class="text-right">
                            <x-status-badge :status="$subscription->status" />

                            @if ($isActive)
                                <p @class([
                                    'mt-2 text-sm font-semibold',
                                    'text-status-wait-fg' => $days <= 7,
                                    'text-status-done-fg' => $days > 7,
                                ])>
                                    {{ $days }} hari lagi
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Masa aktif --}}
                    @if ($isActive)
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-muted">
                                <span>Masa aktif terpakai</span>
                                <span>{{ $terpakai }}%</span>
                            </div>

                            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-canvas">
                                <div
                                    @class([
                                        'h-full rounded-full transition-all',
                                        'bg-status-cancel-fg' => $days <= 7,
                                        'bg-brand' => $days > 7,
                                    ])
                                    style="width: {{ $terpakai }}%"
                                ></div>
                            </div>

                            <div class="mt-1.5 flex justify-between text-xs text-muted">
                                <span>Mulai {{ $subscription->started_at->translatedFormat('d M Y') }}</span>
                                <span>Berakhir {{ $subscription->ends_at->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                    @endif

                    @if ($subscription->isExpiringSoon())
                        <p class="mt-3 rounded-lg bg-status-wait-bg px-3.5 py-2.5 text-xs text-status-wait-fg">
                            Langganan ini akan berakhir pada
                            {{ $subscription->ends_at->translatedFormat('d F Y') }}.
                            Perpanjang sekarang agar akses tidak terputus.
                        </p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-line-soft pt-4">
                        @if ($isActive)
                            <a
                                href="{{ route('subscription.renew', $subscription->id) }}"
                                class="prim-btn h-9 text-sm"
                                wire:navigate
                            >
                                <flux:icon.arrow-path class="size-4" />
                                Perpanjang
                            </a>
                        @endif

                        <a
                            href="{{ route('catalog.show', $service->slug) }}"
                            class="prim-btn-ghost h-9 text-sm"
                            wire:navigate
                        >
                            Lihat Layanan
                        </a>

                        @if ($isActive)
                            <button
                                type="button"
                                wire:click="toggleAutoRenew({{ $subscription->id }})"
                                @class([
                                    'inline-flex h-9 items-center gap-2 rounded-full px-4 text-sm font-medium transition',
                                    'bg-brand-soft text-brand' => $subscription->auto_renew,
                                    'border border-line-soft text-muted hover:text-ink' => ! $subscription->auto_renew,
                                ])
                            >
                                <flux:icon :name="$subscription->auto_renew ? 'check-circle' : 'pause-circle'" class="size-4" />
                                Perpanjangan Otomatis: {{ $subscription->auto_renew ? 'Aktif' : 'Mati' }}
                            </button>
                        @endif

                        @if ($subscription->transaction)
                            <a
                                href="{{ route('transaction.show', $subscription->transaction->order_code) }}"
                                class="ml-auto font-mono text-xs text-muted transition hover:text-brand"
                                wire:navigate
                            >
                                {{ $subscription->transaction->order_code }}
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

    @endif
</div>
