<div class="mx-auto max-w-3xl space-y-5">
    <nav class="flex items-center gap-2 text-sm text-muted">
        <a href="{{ route('catalog.show', $plan->service->slug) }}" class="hover:text-brand" wire:navigate>
            {{ $plan->service->name }}
        </a>
        <span>/</span>
        <span class="font-medium text-ink">Konfirmasi Pemesanan</span>
    </nav>

    <div>
        <h1 class="font-display text-2xl font-bold text-ink-strong">Konfirmasi Pemesanan</h1>
        <p class="mt-1 text-sm text-muted">
            Periksa paket dan metode pembayaran sebelum melanjutkan.
        </p>
    </div>

    @if ($activeSubscription)
        <div class="rounded-xl bg-status-wait-bg p-4">
            <p class="flex items-start gap-2.5 text-sm text-status-wait-fg">
                <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" />
                <span>
                    Kamu sudah berlangganan layanan ini. Langganan
                    <strong>{{ $activeSubscription->plan->groupLabel() }}</strong> masih aktif hingga
                    {{ $activeSubscription->ends_at->translatedFormat('d F Y') }}.
                    Pembelian ini akan menyambung masa aktif mulai tanggal tersebut.
                </span>
            </p>
        </div>
    @endif

    {{-- Pemilih paket --}}
    @if ($pilihanPaket->count() > 1)
        <section class="rounded-xl bg-white p-6 shadow-brand-xs">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-base font-semibold text-ink-strong">Pilih Paket</h2>
                    <p class="mt-1 text-xs text-muted">
                        Tersedia {{ $pilihanPaket->count() }} paket untuk {{ $plan->service->name }}.
                    </p>
                </div>

                @if ($plan->is_preorder)
                    <span class="prim-badge bg-status-process-bg text-status-process-fg">Pre-order</span>
                @endif
            </div>

            {{-- Paket dikelompokkan menurut jenisnya, mis. perangkat atau durasi --}}
            @foreach ($pilihanPaket->groupBy(fn ($p) => $p->variant_group ?: 'Paket Lainnya') as $grup => $anggota)
                <div class="mt-5 first:mt-4" wire:key="grup-{{ \Illuminate\Support\Str::slug($grup) }}">
                    <p class="text-xs tracking-wide text-muted uppercase">{{ $grup }}</p>

                    <div class="mt-2 grid gap-2.5 sm:grid-cols-2">
                        @foreach ($anggota as $opsi)
                            <button
                                type="button"
                                wire:key="opsi-{{ $opsi->id }}"
                                wire:click="choosePlan({{ $opsi->id }})"
                                @class([
                                    'rounded-xl border p-3.5 text-left transition',
                                    'border-brand bg-brand-soft' => $opsi->id === $plan->id,
                                    'border-line-soft bg-white hover:border-brand/50' => $opsi->id !== $plan->id,
                                ])
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-ink-strong">
                                            {{ $opsi->groupLabel() }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-muted">
                                            {{ $opsi->durationLabel() }} ·
                                            {{ $opsi->max_devices }} perangkat
                                        </p>
                                    </div>

                                    <div class="shrink-0 text-right">
                                        <p @class([
                                            'text-sm font-bold',
                                            'text-brand' => $opsi->id === $plan->id,
                                            'text-ink-strong' => $opsi->id !== $plan->id,
                                        ])>{{ $opsi->formattedPrice() }}</p>

                                        @if ($opsi->discount_percent > 0)
                                            <p class="text-[11px] text-status-cancel-fg">
                                                Hemat {{ $opsi->discount_percent }}%
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if ($opsi->stock > 0 && $opsi->stock <= 5)
                                    <p class="mt-2 text-[11px] text-status-wait-fg">
                                        Sisa {{ $opsi->stock }} slot
                                    </p>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    {{-- Ringkasan paket terpilih --}}
    <section class="rounded-xl bg-white p-6 shadow-brand-xs">
        <h2 class="font-display text-base font-semibold text-ink-strong">Paket yang Dipilih</h2>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <x-service-logo :service="$plan->service" class="h-12 w-20 shrink-0" />

                <div>
                    <p class="font-display text-base font-semibold text-ink-strong">{{ $plan->service->name }}</p>
                    <p class="text-sm text-muted">
                        {{ $plan->groupLabel() }} · {{ $plan->durationLabel() }} · {{ $plan->max_devices }} perangkat
                    </p>
                    <p class="mt-1 text-xs text-muted">
                        Penyedia: {{ $plan->service->provider->name }} · Kategori: {{ $plan->service->category->name }}
                    </p>
                </div>
            </div>

            <p class="font-display text-xl font-bold text-brand">{{ $plan->formattedPrice() }}</p>
        </div>

        @if (! empty($plan->features))
            <ul class="mt-4 grid gap-1.5 border-t border-line-soft pt-4 text-sm sm:grid-cols-2">
                @foreach ($plan->features as $feature)
                    <li class="flex items-start gap-2">
                        <flux:icon.check class="mt-0.5 size-4 shrink-0 text-status-done-fg" />
                        <span class="text-muted">{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Metode pembayaran --}}
    <section class="rounded-xl bg-white p-6 shadow-brand-xs">
        <h2 class="font-display text-base font-semibold text-ink-strong">Metode Pembayaran</h2>
        <p class="mt-1 text-sm text-muted">Diproses melalui {{ $gatewayName }}.</p>

        <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
            @foreach ($methods as $code => $label)
                <label
                    wire:key="method-{{ $code }}"
                    @class([
                        'flex cursor-pointer items-center gap-3 rounded-xl border p-3.5 transition',
                        'border-brand bg-brand-soft' => $paymentMethod === $code,
                        'border-line-soft hover:border-brand/50' => $paymentMethod !== $code,
                    ])
                >
                    <input type="radio" wire:model.live="paymentMethod" value="{{ $code }}" class="size-4 accent-[#534AB7]">

                    <span class="min-w-0">
                        <span class="block text-sm font-medium text-ink-strong">{{ $label }}</span>
                    </span>

                    @if ($paymentMethod === $code)
                        <flux:icon.check-circle class="ml-auto size-5 shrink-0 text-brand" />
                    @endif
                </label>
            @endforeach
        </div>

        @error('paymentMethod')
            <p class="mt-2 text-sm text-status-cancel-fg">{{ $message }}</p>
        @enderror
    </section>

    {{-- Ringkasan biaya --}}
    <section class="rounded-xl bg-white p-6 shadow-brand-xs">
        <h2 class="font-display text-base font-semibold text-ink-strong">Ringkasan Biaya</h2>

        <dl class="mt-4 space-y-2.5 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-muted">{{ $plan->service->name }} — {{ $plan->groupLabel() }}</dt>
                <dd class="text-ink">{{ $plan->formattedPrice() }}</dd>
            </div>

            @if ($plan->discount_percent > 0)
                <div class="flex justify-between gap-4">
                    <dt class="text-muted">Diskon {{ $plan->discount_percent }}%</dt>
                    <dd class="text-status-done-fg">
                        − {{ $plan->compare_at_price ? 'Rp'.number_format($plan->compare_at_price - $plan->price, 0, ',', '.') : $plan->discount_percent.'%' }}
                    </dd>
                </div>
            @endif

            <div class="flex justify-between gap-4">
                <dt class="text-muted">Biaya layanan</dt>
                <dd class="text-ink">Rp0</dd>
            </div>

            <div class="flex items-center justify-between gap-4 border-t border-line-soft pt-3">
                <dt class="font-display text-base font-bold text-ink-strong">Total Pembayaran</dt>
                <dd class="font-display text-lg font-bold text-brand">{{ $plan->formattedPrice() }}</dd>
            </div>
        </dl>

        <button type="button" wire:click="checkout" class="prim-btn mt-5 w-full" wire:loading.attr="disabled">
            <flux:icon.credit-card class="size-5" wire:loading.remove />
            <span wire:loading.remove>Buat Pesanan</span>
            <span wire:loading>Memproses…</span>
        </button>

        <p class="mt-3 text-center text-xs text-muted">
            Pesanan berlaku {{ \App\Models\Transaction::PAYMENT_WINDOW_HOURS }} jam sebelum kedaluwarsa.
            Kode login dikirim setelah pembayaran diterima.
        </p>
    </section>
</div>
