<div class="mx-auto max-w-3xl space-y-6">
    <nav class="flex items-center gap-2 text-sm text-muted">
        <a href="{{ route('profile.orders') }}" class="hover:text-brand" wire:navigate>Pesanan Saya</a>
        <span>/</span>
        <span class="font-medium text-ink">{{ $transaction->order_code }}</span>
    </nav>

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-ink-strong">Detail Pesanan</h1>
            <p class="mt-1 font-mono text-sm text-muted">{{ $transaction->order_code }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-status-badge :status="$transaction->status" class="px-3 py-1.5 text-sm" />

            @if ($transaction->status->isSuccessful())
                <a href="{{ route('transaction.receipt', $transaction->order_code) }}" class="prim-btn-ghost h-10 text-sm" wire:navigate>
                    <flux:icon.printer class="size-4" />
                    Cetak Struk
                </a>
            @endif
        </div>
    </div>

    {{-- Bagian pembayaran --}}
    @if ($isPayable)
        <section class="rounded-xl bg-brand-soft p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-display text-base font-semibold text-brand">Selesaikan Pembayaran</h2>
                    <p class="mt-1 text-sm text-ink">
                        Bayar sebesar <strong>{{ $transaction->formattedAmount() }}</strong>
                        sebelum {{ $transaction->expires_at?->translatedFormat('d F Y, H:i') }} WITA.
                    </p>
                </div>

                {{-- Hitung mundur: ditegaskan bila waktunya tinggal sedikit --}}
                <span @class([
                    'prim-badge',
                    'bg-status-wait-bg text-status-wait-fg' => ($transaction->remainingPaymentHours() ?? 0) > 3,
                    'bg-status-cancel-bg text-status-cancel-fg font-medium' => ($transaction->remainingPaymentHours() ?? 0) <= 3,
                ])>
                    <flux:icon.clock class="mr-1 size-3.5" />
                    Sisa {{ $transaction->remainingPaymentTime() }}
                </span>
            </div>

            {{-- Pemilih metode pembayaran --}}
            <div class="mt-5">
                <p class="text-xs tracking-wide text-muted uppercase">Metode Pembayaran</p>

                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    @foreach ($methods as $kode => $nama)
                        <button
                            type="button"
                            wire:key="metode-{{ $kode }}"
                            wire:click="pilihMetode('{{ $kode }}')"
                            @class([
                                'flex items-center gap-2.5 rounded-lg border p-3 text-left text-sm transition',
                                'border-brand bg-white font-medium text-brand' => $paymentMethod === $kode,
                                'border-line-soft bg-white/70 text-ink hover:border-brand/50' => $paymentMethod !== $kode,
                            ])
                        >
                            <span @class([
                                'flex size-4 shrink-0 items-center justify-center rounded-full border-2',
                                'border-brand' => $paymentMethod === $kode,
                                'border-line' => $paymentMethod !== $kode,
                            ])>
                                @if ($paymentMethod === $kode)
                                    <span class="size-2 rounded-full bg-brand"></span>
                                @endif
                            </span>

                            <span class="min-w-0">{{ $nama }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            @if ($transaction->payment_url)
                {{-- Tautan pembayaran dari penyedia sudah tersedia --}}
                <div class="mt-5 rounded-xl bg-white p-4 shadow-brand-xs">
                    <p class="text-xs tracking-wide text-muted uppercase">Lanjutkan di {{ $gatewayName }}</p>
                    <p class="mt-1 text-sm text-muted">
                        Halaman pembayaran sudah disiapkan. Selesaikan pembayaran di sana,
                        lalu kembali ke halaman ini. Status akan diperbarui otomatis.
                    </p>

                    <a href="{{ $transaction->payment_url }}" class="prim-btn mt-4" target="_blank" rel="noopener">
                        <flux:icon.arrow-top-right-on-square class="size-4" />
                        Buka Halaman Pembayaran
                    </a>
                </div>
            @elseif ($usesOnlineGateway)
                {{-- Gateway daring aktif, tetapi tautan belum dibuat --}}
                <div class="mt-5 rounded-xl bg-white p-4 shadow-brand-xs">
                    <p class="text-xs tracking-wide text-muted uppercase">Pembayaran Daring</p>
                    <p class="mt-1 text-sm text-muted">
                        Tekan tombol di bawah untuk membuat sesi pembayaran pada {{ $gatewayName }}.
                        Anda akan diarahkan ke halaman pembayaran yang aman.
                    </p>

                    <button type="button" wire:click="pay(true)" class="prim-btn mt-4" wire:loading.attr="disabled">
                        <flux:icon.credit-card class="size-4" wire:loading.remove />
                        <span wire:loading.remove>Bayar Sekarang</span>
                        <span wire:loading>Menyiapkan pembayaran…</span>
                    </button>
                </div>
            @else
                {{-- Mode simulasi --}}
                <div class="mt-5 rounded-xl border border-dashed border-brand/40 bg-white p-4">
                    <p class="text-xs tracking-wide text-muted uppercase">Mode Simulasi</p>
                    <p class="mt-1 text-sm text-muted">
                        {{ $gatewayName }} belum terhubung ke penyedia pembayaran nyata. Gunakan tombol
                        di bawah untuk mendemonstrasikan hasil pembayaran. Untuk mengaktifkan pembayaran
                        daring, isi <code class="rounded bg-canvas px-1">MIDTRANS_SERVER_KEY</code> dan
                        <code class="rounded bg-canvas px-1">MIDTRANS_MODE=snap</code> pada berkas .env.
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="button" wire:click="pay(true)" class="prim-btn" wire:loading.attr="disabled">
                            <flux:icon.check-circle class="size-4" />
                            Simulasikan Berhasil
                        </button>

                        <button
                            type="button"
                            wire:click="pay(false)"
                            class="inline-flex items-center gap-2 rounded-full bg-status-cancel-fg px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-90"
                        >
                            <flux:icon.x-circle class="size-4" />
                            Simulasikan Gagal
                        </button>
                    </div>
                </div>
            @endif
        </section>
    @elseif ($hasExpired)
        <section class="rounded-xl bg-white p-5 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-status-cancel-fg">Batas Pembayaran Sudah Lewat</h2>
            <p class="mt-1 text-sm text-muted">
                Pesanan ini melewati batas waktu pembayaran
                {{ $transaction->expires_at?->translatedFormat('d F Y, H:i') }} WITA,
                sehingga tidak dapat dilanjutkan lagi. Silakan buat pesanan baru untuk paket ini.
            </p>

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('transaction.checkout', $transaction->plan_id) }}" class="prim-btn" wire:navigate>
                    <flux:icon.arrow-path class="size-4" />
                    Pesan Ulang
                </a>
                <a href="{{ route('catalog.index') }}" class="prim-btn-ghost h-10" wire:navigate>
                    Jelajahi Katalog
                </a>
            </div>
        </section>
    @elseif ($transaction->status->isSuccessful())
        <section class="rounded-xl bg-status-done-bg p-5">
            <h2 class="font-display text-base font-semibold text-status-done-fg">Pembayaran Berhasil</h2>
            <p class="mt-1 text-sm text-ink">
                Dibayar pada {{ $transaction->paid_at?->translatedFormat('d F Y, H:i') }} WITA.
                @if ($transaction->subscription)
                    Langgananmu aktif hingga
                    {{ $transaction->subscription->ends_at->translatedFormat('d F Y') }}.
                @endif
            </p>

            @if ($transaction->isRefunded())
                <div class="mt-4 rounded-lg bg-white p-4">
                    <p class="flex items-center gap-2 text-sm font-medium text-status-cancel-fg">
                        <flux:icon.banknotes class="size-4" />
                        Dana sudah dikembalikan {{ $transaction->formattedRefund() }}
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-muted">
                        Dikembalikan pada {{ $transaction->refunded_at->translatedFormat('d F Y, H:i') }} WITA.
                        @if ($transaction->refund_reason)
                            Alasan: {{ $transaction->refund_reason }}
                        @endif
                    </p>
                </div>
            @endif

            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('transaction.receipt', $transaction->order_code) }}" class="prim-btn" wire:navigate>
                    <flux:icon.printer class="size-4" />
                    Cetak Struk Digital
                </a>
                <a href="{{ route('subscription.index') }}" class="prim-btn-ghost h-10" wire:navigate>
                    Lihat Langganan Saya
                </a>
            </div>
        </section>
    @else
        <section class="rounded-xl bg-white p-5 shadow-brand-xs">
            <h2 class="font-display text-base font-semibold text-ink-strong">Pesanan Tidak Dapat Dilanjutkan</h2>
            <p class="mt-1 text-sm text-muted">
                {{ $transaction->notes ?? 'Pesanan ini tidak lagi dapat dibayar.' }}
            </p>
            <a href="{{ route('transaction.checkout', $transaction->plan_id) }}" class="prim-btn mt-4" wire:navigate>
                Pesan Ulang
            </a>
        </section>
    @endif

    {{-- Rincian --}}
    <section class="rounded-xl bg-white p-6 shadow-brand-xs">
        <h2 class="font-display text-base font-semibold text-ink-strong">Rincian Pesanan</h2>

        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Layanan</dt>
                <dd class="text-right font-medium text-ink-strong">{{ $transaction->plan->service->name }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Paket</dt>
                <dd class="text-right font-medium text-ink-strong">{{ $transaction->plan->groupLabel() }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Penyedia</dt>
                <dd class="text-right text-ink">{{ $transaction->plan->service->provider->name }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Durasi</dt>
                <dd class="text-right text-ink">{{ $transaction->plan->durationLabel() }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Perangkat</dt>
                <dd class="text-right text-ink">{{ $transaction->plan->max_devices }} perangkat</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-muted">Metode pembayaran</dt>
                <dd class="text-right text-ink">{{ $paymentMethodLabel }}</dd>
            </div>
            @if ($transaction->payment_reference)
                <div class="flex justify-between gap-4">
                    <dt class="text-muted">Referensi pembayaran</dt>
                    <dd class="text-right font-mono text-xs text-ink">{{ $transaction->payment_reference }}</dd>
                </div>
            @endif
            @if ($transaction->hasDiscount())
                <div class="flex justify-between gap-4">
                    <dt class="text-muted">Harga paket</dt>
                    <dd class="text-right text-ink">Rp{{ number_format($transaction->subtotal(), 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="flex items-center gap-1.5 text-muted">
                        <flux:icon.ticket class="size-4 text-status-done-fg" />
                        Voucher {{ $transaction->voucher?->code ?? 'diskon' }}
                    </dt>
                    <dd class="text-right font-medium text-status-done-fg">
                        − Rp{{ number_format($transaction->discount(), 0, ',', '.') }}
                    </dd>
                </div>
            @endif

            <div class="flex justify-between gap-4">
                <dt class="text-muted">Dibuat pada</dt>
                <dd class="text-right text-ink">{{ $transaction->created_at->translatedFormat('d F Y, H:i') }} WITA</dd>
            </div>
            <div class="flex justify-between gap-4 border-t border-line-soft pt-3 text-base font-bold">
                <dt class="text-ink-strong">Total</dt>
                <dd class="text-brand">{{ $transaction->formattedAmount() }}</dd>
            </div>
        </dl>

        @if ($transaction->notes)
            <p class="mt-4 rounded-lg bg-canvas p-3 text-xs leading-relaxed text-muted">
                {{ $transaction->notes }}
            </p>
        @endif
    </section>
</div>
