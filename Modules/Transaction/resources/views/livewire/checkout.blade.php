<div class="mx-auto max-w-3xl space-y-6">
    <nav class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('catalog.show', $plan->service->slug) }}" class="hover:text-indigo-600" wire:navigate>
            {{ $plan->service->name }}
        </a>
        <span>/</span>
        <span class="font-medium text-zinc-800 dark:text-zinc-200">Checkout</span>
    </nav>

    <h1 class="text-2xl font-bold tracking-tight">Konfirmasi Pemesanan</h1>

    @if ($activeSubscription)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm dark:border-amber-800 dark:bg-amber-950">
            <p class="font-medium text-amber-900 dark:text-amber-200">Anda sudah berlangganan layanan ini</p>
            <p class="mt-1 text-amber-800 dark:text-amber-300">
                Langganan <strong>{{ $activeSubscription->plan->name }}</strong> masih aktif hingga
                {{ $activeSubscription->ends_at->translatedFormat('d F Y') }}.
                Pembelian ini akan memperpanjang masa aktif mulai tanggal tersebut.
            </p>
        </div>
    @endif

    {{-- Ringkasan paket --}}
    <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h2 class="font-semibold">Paket yang dipilih</h2>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-lg font-semibold">{{ $plan->service->name }}</p>
                <p class="text-sm text-zinc-500">
                    {{ $plan->name }} · {{ $plan->durationLabel() }} · {{ $plan->max_devices }} perangkat
                </p>
                <p class="mt-1 text-xs text-zinc-500">
                    Penyedia: {{ $plan->service->provider->name }} · Kategori: {{ $plan->service->category->name }}
                </p>
            </div>

            <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ $plan->formattedPrice() }}</p>
        </div>

        @if (! empty($plan->features))
            <ul class="mt-4 grid gap-1.5 border-t border-zinc-100 pt-4 text-sm sm:grid-cols-2 dark:border-zinc-700">
                @foreach ($plan->features as $feature)
                    <li class="flex items-start gap-2">
                        <flux:icon.check class="mt-0.5 size-4 shrink-0 text-green-600" />
                        <span class="text-zinc-600 dark:text-zinc-400">{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Metode pembayaran --}}
    <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <h2 class="font-semibold">Metode pembayaran</h2>
        <p class="mt-1 text-sm text-zinc-500">
            Diproses melalui {{ $gatewayName }}.
        </p>

        <div class="mt-4 space-y-2">
            @foreach ($methods as $code => $label)
                <label
                    wire:key="method-{{ $code }}"
                    @class([
                        'flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition',
                        'border-indigo-500 bg-indigo-50 dark:bg-indigo-950' => $paymentMethod === $code,
                        'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700' => $paymentMethod !== $code,
                    ])
                >
                    <input type="radio" wire:model.live="paymentMethod" value="{{ $code }}" class="size-4">
                    <span class="text-sm font-medium">{{ $label }}</span>
                </label>
            @endforeach
        </div>

        @error('paymentMethod')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </section>

    {{-- Ringkasan biaya --}}
    <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
                <dt class="text-zinc-500">Harga paket</dt>
                <dd>{{ $plan->formattedPrice() }}</dd>
            </div>
            <div class="flex justify-between border-t border-zinc-100 pt-2 text-base font-bold dark:border-zinc-700">
                <dt>Total pembayaran</dt>
                <dd class="text-indigo-600 dark:text-indigo-400">{{ $plan->formattedPrice() }}</dd>
            </div>
        </dl>

        <flux:button wire:click="checkout" variant="filled" icon="credit-card" class="mt-5 w-full">
            Buat Pesanan
        </flux:button>

        <p class="mt-3 text-center text-xs text-zinc-500">
            Pesanan berlaku {{ \App\Models\Transaction::PAYMENT_WINDOW_HOURS }} jam sebelum kedaluwarsa.
        </p>
    </section>
</div>
