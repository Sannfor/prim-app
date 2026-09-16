<div class="space-y-8">
    {{-- Navigasi remah --}}
    <nav class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('catalog.index') }}" class="hover:text-indigo-600" wire:navigate>Katalog</a>
        <span>/</span>
        <span>{{ $service->category->name }}</span>
        <span>/</span>
        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $service->name }}</span>
    </nav>

    {{-- Kepala layanan --}}
    <header class="rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="flex items-start gap-4">
                <span class="flex size-16 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-xl font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                    {{ Str::of($service->name)->substr(0, 2)->upper() }}
                </span>

                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $service->name }}</h1>
                    <p class="mt-1 text-sm text-zinc-500">
                        oleh {{ $service->provider->name }}
                        @if ($service->provider->website)
                            · <a href="{{ $service->provider->website }}" target="_blank" class="text-indigo-600 hover:underline">Kunjungi situs penyedia</a>
                        @endif
                    </p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <flux:badge size="sm" color="indigo">{{ $service->category->name }}</flux:badge>
                        @if ($rating !== null)
                            <flux:badge size="sm" color="amber">★ {{ $rating }} / 5</flux:badge>
                        @endif
                        <flux:badge size="sm" color="zinc">{{ $service->plans->where('is_active', true)->count() }} paket</flux:badge>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <flux:button wire:click="toggleCompare" variant="{{ $isCompared ? 'primary' : 'ghost' }}" icon="{{ $isCompared ? 'check' : 'arrows-right-left' }}">
                    {{ $isCompared ? 'Dibandingkan' : 'Bandingkan' }}
                </flux:button>
                <flux:button :href="route('catalog.compare')" variant="ghost" icon="table-cells" wire:navigate>
                    Lihat Perbandingan
                </flux:button>
            </div>
        </div>

        <p class="mt-6 max-w-3xl text-sm/relaxed text-zinc-600 dark:text-zinc-400">
            {{ $service->description }}
        </p>
    </header>

    {{-- Daftar paket --}}
    <section>
        <h2 class="text-lg font-semibold tracking-tight">Pilih paket langganan</h2>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            Harga dan fitur di bawah ini disediakan oleh penyedia layanan.
        </p>

        @if ($plans->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-zinc-300 bg-white p-10 text-center dark:border-zinc-600 dark:bg-zinc-800">
                <p class="font-medium">Belum ada paket aktif</p>
                <p class="mt-1 text-sm text-zinc-500">Penyedia layanan belum menawarkan paket untuk layanan ini.</p>
            </div>
        @else
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($plans as $plan)
                    <article
                        wire:key="plan-{{ $plan->id }}"
                        class="flex flex-col rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        <h3 class="font-semibold">{{ $plan->name }}</h3>

                        <p class="mt-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $plan->formattedPrice() }}
                        </p>

                        <p class="mt-1 text-xs text-zinc-500">
                            {{ $plan->durationLabel() }}
                            @if ($plan->monthlyPrice() !== null)
                                · setara Rp{{ Number::format($plan->monthlyPrice(), locale: 'id') }}/bulan
                            @endif
                        </p>

                        @if ($plan->description)
                            <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $plan->description }}</p>
                        @endif

                        <dl class="mt-4 flex-1 space-y-2 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="text-zinc-500">Durasi</dt>
                                <dd class="font-medium">{{ $plan->durationLabel() }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-zinc-500">Perangkat</dt>
                                <dd class="font-medium">{{ $plan->max_devices }} bersamaan</dd>
                            </div>
                        </dl>

                        @if (! empty($plan->features))
                            <ul class="mt-4 space-y-1.5 border-t border-zinc-100 pt-4 text-sm dark:border-zinc-700">
                                @foreach ($plan->features as $feature)
                                    <li class="flex items-start gap-2">
                                        <flux:icon.check class="mt-0.5 size-4 shrink-0 text-green-600" />
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <flux:button
                            :href="route('transaction.checkout', $plan->id)"
                            variant="filled"
                            icon="shopping-cart"
                            class="mt-5 w-full"
                            wire:navigate
                        >
                            Berlangganan
                        </flux:button>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Ulasan pengguna --}}
    @if ($service->reviews->isNotEmpty())
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <h2 class="text-lg font-semibold tracking-tight">Ulasan pengguna</h2>

            <div class="mt-4 space-y-4">
                @foreach ($service->reviews as $review)
                    <article wire:key="review-{{ $review->id }}" class="border-b border-zinc-100 pb-4 last:border-0 last:pb-0 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $review->user->name }}</span>
                            <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}</span>
                        </div>
                        @if ($review->comment)
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $review->comment }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
