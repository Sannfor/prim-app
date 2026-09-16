<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Langganan Saya</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            Pantau masa aktif layanan premium Anda dan perpanjang sebelum berakhir.
        </p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500">Langganan aktif</p>
            <p class="mt-1 text-2xl font-bold">{{ $activeCount }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500">Akan berakhir dalam 7 hari</p>
            <p class="mt-1 text-2xl font-bold {{ $expiringSoonCount > 0 ? 'text-amber-600' : '' }}">
                {{ $expiringSoonCount }}
            </p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex flex-wrap gap-2">
        @foreach ($filters as $key => $label)
            <flux:button
                wire:click="$set('filter', '{{ $key }}')"
                variant="{{ $filter === $key ? 'filled' : 'ghost' }}"
                size="sm"
            >
                {{ $label }}
            </flux:button>
        @endforeach
    </div>

    @if ($subscriptions->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-600 dark:bg-zinc-800">
            <flux:icon.arrow-path-rounded-square class="mx-auto size-8 text-zinc-400" />
            <p class="mt-3 font-medium">Tidak ada langganan pada kategori ini</p>
            <p class="mt-1 text-sm text-zinc-500">
                Mulai berlangganan layanan premium dari katalog PRIM.
            </p>
            <flux:button :href="route('catalog.index')" variant="filled" class="mt-4" wire:navigate>
                Jelajahi Katalog
            </flux:button>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($subscriptions as $subscription)
                @php
                    $service = $subscription->plan->service;
                    $isActive = $subscription->isActive();
                    $days = $subscription->daysRemaining();
                @endphp

                <article
                    wire:key="sub-{{ $subscription->id }}"
                    class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800"
                >
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                                {{ Str::of($service->name)->substr(0, 2)->upper() }}
                            </span>
                            <div>
                                <h2 class="font-semibold">{{ $service->name }}</h2>
                                <p class="text-sm text-zinc-500">{{ $subscription->plan->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $service->provider->name }}</p>
                            </div>
                        </div>

                        <div class="text-right">
                            <flux:badge size="sm" :color="$subscription->status->badgeColor()">
                                {{ $subscription->status->label() }}
                            </flux:badge>

                            @if ($isActive)
                                <p class="mt-2 text-sm font-semibold {{ $days <= 7 ? 'text-amber-600' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    {{ $days }} hari lagi
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Progres masa aktif --}}
                    @if ($isActive)
                        <div class="mt-4">
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                                <div
                                    class="h-full rounded-full {{ $days <= 7 ? 'bg-amber-500' : 'bg-indigo-500' }}"
                                    style="width: {{ $subscription->progressPercentage() }}%"
                                ></div>
                            </div>
                            <div class="mt-2 flex justify-between text-xs text-zinc-500">
                                <span>{{ $subscription->started_at->translatedFormat('d M Y') }}</span>
                                <span>{{ $subscription->ends_at->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                    @endif

                    @if ($subscription->isExpiringSoon())
                        <p class="mt-3 rounded-lg bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                            Langganan ini akan berakhir pada
                            {{ $subscription->ends_at->translatedFormat('d F Y') }}.
                            Perpanjang sekarang agar akses tidak terputus.
                        </p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <flux:button
                            :href="route('catalog.show', $service->slug)"
                            variant="ghost"
                            size="sm"
                            icon="eye"
                            wire:navigate
                        >
                            Lihat Layanan
                        </flux:button>

                        @if ($isActive)
                            <flux:button
                                :href="route('subscription.renew', $subscription->id)"
                                variant="filled"
                                size="sm"
                                icon="arrow-path"
                                wire:navigate
                            >
                                Perpanjang
                            </flux:button>

                            <flux:button
                                wire:click="toggleAutoRenew({{ $subscription->id }})"
                                variant="ghost"
                                size="sm"
                                icon="{{ $subscription->auto_renew ? 'check-circle' : 'arrow-path' }}"
                            >
                                Perpanjangan Otomatis: {{ $subscription->auto_renew ? 'Aktif' : 'Mati' }}
                            </flux:button>

                            @if ($subscription->auto_renew)
                                <flux:button
                                    wire:click="cancelAutoRenew({{ $subscription->id }})"
                                    variant="danger"
                                    size="sm"
                                    icon="x-circle"
                                >
                                    Batalkan Otomatis
                                </flux:button>
                            @endif
                        @endif

                        @if ($subscription->transaction)
                            <a
                                href="{{ route('transaction.show', $subscription->transaction->order_code) }}"
                                class="text-xs text-zinc-500 hover:text-indigo-600"
                                wire:navigate
                            >
                                Pesanan {{ $subscription->transaction->order_code }}
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
