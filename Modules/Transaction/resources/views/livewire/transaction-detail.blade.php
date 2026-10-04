<div class="mx-auto max-w-3xl space-y-6">
    <nav class="flex items-center gap-2 text-sm text-muted">
        <a href="{{ route('transaction.index') }}" class="hover:text-brand" wire:navigate>Riwayat Transaksi</a>
        <span>/</span>
        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $transaction->order_code }}</span>
    </nav>

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-medium text-ink-strong">Detail Pesanan</h1>
            <p class="mt-1 font-mono text-sm text-muted">{{ $transaction->order_code }}</p>
        </div>

        <flux:badge :color="$transaction->status->badgeColor()" size="lg">
            {{ $transaction->status->label() }}
        </flux:badge>
    </div>

    {{-- Instruksi pembayaran --}}
    @if ($isPayable)
        <section class="rounded-xl border border-indigo-200 bg-brand-soft p-5 dark:border-indigo-800 dark:bg-indigo-950">
            <h2 class="font-semibold text-indigo-900 dark:text-indigo-200">Selesaikan pembayaran</h2>
            <p class="mt-1 text-sm text-indigo-800 dark:text-indigo-300">
                Lakukan pembayaran sebesar <strong>{{ $transaction->formattedAmount() }}</strong>
                melalui {{ $paymentMethodLabel }}
                sebelum {{ $transaction->expires_at?->translatedFormat('d F Y, H:i') }} WITA.
            </p>

            <div class="mt-5 rounded-lg border border-dashed border-indigo-300 bg-white p-4 dark:border-indigo-700 dark:bg-zinc-900">
                <p class="text-xs font-medium tracking-wide text-muted uppercase">Mode simulasi</p>
                <p class="mt-1 text-sm text-muted">
                    PRIM belum terhubung ke penyedia pembayaran nyata. Gunakan tombol di bawah untuk
                    mensimulasikan hasil pembayaran.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <flux:button wire:click="pay(true)" variant="filled" icon="check-circle">
                        Simulasikan Pembayaran Berhasil
                    </flux:button>
                    <flux:button wire:click="pay(false)" variant="danger" icon="x-circle">
                        Simulasikan Pembayaran Gagal
                    </flux:button>
                </div>
            </div>
        </section>
    @elseif ($hasExpired)
        <section class="rounded-xl bg-canvas p-5">
            <h2 class="font-semibold">Pesanan sudah kedaluwarsa</h2>
            <p class="mt-1 text-sm text-muted">
                Batas waktu pembayaran telah terlewat. Silakan buat pesanan baru untuk paket ini.
            </p>
            <flux:button
                :href="route('transaction.checkout', $transaction->plan_id)"
                variant="filled"
                class="mt-4"
                wire:navigate
            >
                Pesan Ulang
            </flux:button>
        </section>
    @elseif ($transaction->status === \App\Enums\TransactionStatus::Paid)
        <section class="rounded-xl border border-green-200 bg-green-50 p-5 dark:border-green-800 dark:bg-green-950">
            <h2 class="font-semibold text-green-900 dark:text-green-200">Pembayaran berhasil</h2>
            <p class="mt-1 text-sm text-green-800 dark:text-green-300">
                Dibayar pada {{ $transaction->paid_at?->translatedFormat('d F Y, H:i') }} WITA.
                @if ($transaction->subscription)
                    Langganan Anda aktif hingga
                    {{ $transaction->subscription->ends_at->translatedFormat('d F Y') }}.
                @endif
            </p>
            <flux:button :href="route('subscription.index')" variant="filled" class="mt-4" wire:navigate>
                Lihat Langganan Saya
            </flux:button>
        </section>
    @else
        <section class="rounded-xl bg-canvas p-5">
            <h2 class="font-semibold">Pesanan tidak dapat dilanjutkan</h2>
            <p class="mt-1 text-sm text-muted">
                {{ $transaction->notes ?? 'Pesanan ini tidak lagi dapat dibayar.' }}
            </p>
            <flux:button
                :href="route('transaction.checkout', $transaction->plan_id)"
                variant="filled"
                class="mt-4"
                wire:navigate
            >
                Pesan Ulang
            </flux:button>
        </section>
    @endif

    {{-- Rincian --}}
    <section class="rounded-xl border border-zinc-200 bg-white p-6 ">
        <h2 class="font-semibold">Rincian pesanan</h2>

        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Layanan</dt>
                <dd class="text-right font-medium">{{ $transaction->plan->service->name }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Paket</dt>
                <dd class="text-right font-medium">{{ $transaction->plan->name }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Penyedia</dt>
                <dd class="text-right">{{ $transaction->plan->service->provider->name }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Durasi</dt>
                <dd class="text-right">{{ $transaction->plan->durationLabel() }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Metode pembayaran</dt>
                <dd class="text-right">{{ $paymentMethodLabel }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Dibuat pada</dt>
                <dd class="text-right">{{ $transaction->created_at->translatedFormat('d F Y, H:i') }} WITA</dd>
            </div>
            <div class="flex justify-between gap-4 border-t border-zinc-100 pt-3 text-base font-bold dark:border-zinc-700">
                <dt>Total</dt>
                <dd class="text-brand">{{ $transaction->formattedAmount() }}</dd>
            </div>
        </dl>

        @if ($transaction->notes)
            <p class="mt-4 rounded-lg bg-zinc-50 p-3 text-xs text-zinc-600 dark:bg-zinc-900 dark:text-muted-2">
                {{ $transaction->notes }}
            </p>
        @endif
    </section>
</div>
