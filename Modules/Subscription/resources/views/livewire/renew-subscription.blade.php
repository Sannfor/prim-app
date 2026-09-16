<div class="mx-auto max-w-4xl space-y-6">
    <nav class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('subscription.index') }}" class="hover:text-indigo-600" wire:navigate>Langganan Saya</a>
        <span>/</span>
        <span class="font-medium text-zinc-800 dark:text-zinc-200">Perpanjang</span>
    </nav>

    <header class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h1 class="text-2xl font-bold tracking-tight">Perpanjang {{ $service->name }}</h1>

        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
            Langganan <strong>{{ $subscription->plan->name }}</strong>
            @if ($subscription->isActive())
                masih aktif hingga {{ $subscription->ends_at->translatedFormat('d F Y') }}
                ({{ $subscription->daysRemaining() }} hari lagi).
                Masa aktif baru akan disambung mulai tanggal tersebut, sehingga sisa masa aktif tidak hilang.
            @else
                sudah berakhir pada {{ $subscription->ends_at->translatedFormat('d F Y') }}.
                Masa aktif baru akan dimulai sejak pembayaran berhasil.
            @endif
        </p>
    </header>

    <section>
        <h2 class="text-lg font-semibold tracking-tight">Pilih paket perpanjangan</h2>

        @if ($plans->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-zinc-300 bg-white p-10 text-center dark:border-zinc-600 dark:bg-zinc-800">
                <p class="font-medium">Belum ada paket aktif</p>
                <p class="mt-1 text-sm text-zinc-500">Penyedia belum menawarkan paket untuk layanan ini.</p>
            </div>
        @else
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    <article
                        wire:key="renew-{{ $plan->id }}"
                        @class([
                            'flex flex-col rounded-xl border bg-white p-5 dark:bg-zinc-800',
                            'border-indigo-500 ring-1 ring-indigo-500' => $plan->id === $subscription->plan_id,
                            'border-zinc-200 dark:border-zinc-700' => $plan->id !== $subscription->plan_id,
                        ])
                    >
                        @if ($plan->id === $subscription->plan_id)
                            <flux:badge size="sm" color="indigo" class="mb-2 self-start">Paket Anda saat ini</flux:badge>
                        @endif

                        <h3 class="font-semibold">{{ $plan->name }}</h3>
                        <p class="mt-2 text-xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $plan->formattedPrice() }}
                        </p>
                        <p class="mt-1 text-xs text-zinc-500">
                            {{ $plan->durationLabel() }} · {{ $plan->max_devices }} perangkat
                        </p>

                        @if ($plan->description)
                            <p class="mt-3 flex-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $plan->description }}</p>
                        @endif

                        <flux:button
                            :href="route('transaction.checkout', $plan->id)"
                            variant="filled"
                            icon="arrow-path"
                            class="mt-4 w-full"
                            wire:navigate
                        >
                            Pilih Paket Ini
                        </flux:button>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
