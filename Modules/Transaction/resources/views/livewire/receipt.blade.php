<div class="min-w-0">
    {{-- Bilah aksi: tidak ikut tercetak --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <a href="{{ route('transaction.show', $order->order_code) }}" class="prim-btn-ghost h-10" wire:navigate>
            <flux:icon.arrow-left class="size-4" />
            Kembali ke Rincian Pesanan
        </a>

        <div class="flex items-center gap-2">
            <span class="hidden text-xs text-muted sm:block">
                Gunakan "Cetak" lalu pilih "Simpan sebagai PDF" bila ingin berkas digital.
            </span>

            <button
                type="button"
                onclick="window.print()"
                class="prim-btn h-10"
            >
                <flux:icon.printer class="size-4" />
                Cetak Struk
            </button>
        </div>
    </div>

    {{-- Area struk --}}
    <article class="mx-auto w-full max-w-[640px] rounded-xl bg-white p-8 shadow-brand-xs print:max-w-none print:rounded-none print:p-0 print:shadow-none">
        {{-- Kepala --}}
        <header class="flex items-start justify-between gap-6 border-b border-line-soft pb-5">
            <div>
                <x-brand-logo size="lg" />
                <p class="mt-2 text-xs leading-relaxed text-muted">
                    Platform Digital Aggregator Layanan Premium<br>
                    prim.kelompok7@example.id · +62 858 2812 0489
                </p>
            </div>

            <div class="text-right">
                <p class="font-display text-lg font-bold text-ink-strong">STRUK DIGITAL</p>
                <p class="mt-1 font-mono text-xs text-ink">{{ $nomorStruk }}</p>
                <p class="mt-1 text-xs text-muted">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WITA</p>
            </div>
        </header>

        {{-- Status --}}
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-canvas px-4 py-3">
            <div>
                <p class="text-xs text-muted">Status Pembayaran</p>
                <p class="mt-0.5 text-sm font-semibold text-ink-strong">{{ $order->status->label() }}</p>
            </div>

            <x-status-badge :status="$order->status" />

            @if ($order->paid_at)
                <div class="text-right">
                    <p class="text-xs text-muted">Dibayar pada</p>
                    <p class="mt-0.5 text-sm text-ink">{{ $order->paid_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
            @endif
        </div>

        {{-- Data pemesan --}}
        <section class="mt-5 grid gap-4 border-t border-line-soft pt-5 sm:grid-cols-2">
            <div>
                <p class="text-xs tracking-wide text-muted uppercase">Ditagihkan kepada</p>
                <p class="mt-1 text-sm font-semibold text-ink-strong">{{ $order->user->name }}</p>
                <p class="text-xs text-muted">{{ $order->user->email }}</p>
                @if ($order->user->phone)
                    <p class="text-xs text-muted">{{ $order->user->phone }}</p>
                @endif
            </div>

            <div class="sm:text-right">
                <p class="text-xs tracking-wide text-muted uppercase">Kode Pesanan</p>
                <p class="mt-1 font-mono text-sm text-ink-strong">{{ $order->order_code }}</p>
                <p class="mt-1 text-xs text-muted">Metode: {{ $order->paymentLabel() }}</p>
                @if ($order->payment_reference)
                    <p class="text-xs text-muted">Referensi: {{ $order->payment_reference }}</p>
                @endif
            </div>
        </section>

        {{-- Rincian item --}}
        <section class="mt-5 border-t border-line-soft pt-5">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line-soft text-left text-xs text-muted">
                        <th class="pb-2 font-normal">Deskripsi</th>
                        <th class="pb-2 text-right font-normal">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-line-soft">
                        <td class="py-3">
                            <p class="font-medium text-ink-strong">{{ $order->plan->service->name }}</p>
                            <p class="text-xs text-muted">
                                {{ $order->plan->groupLabel() }} · {{ $order->plan->durationLabel() }} ·
                                {{ $order->plan->max_devices }} perangkat
                            </p>
                            <p class="text-xs text-muted">Penyedia: {{ $order->plan->service->provider->name }}</p>
                        </td>
                        <td class="py-3 text-right align-top text-ink">{{ $order->formattedAmount() }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="pt-3 text-xs text-muted">Subtotal</td>
                        <td class="pt-3 text-right text-ink">Rp{{ number_format($order->subtotal(), 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="pt-1 text-xs text-muted">Biaya layanan</td>
                        <td class="pt-1 text-right text-ink">Rp0</td>
                    </tr>

                    @if ($order->hasDiscount())
                        <tr>
                            <td class="pt-1 text-xs text-muted">
                                Voucher {{ $order->voucher?->code ?? 'diskon' }}
                            </td>
                            <td class="pt-1 text-right text-status-done-fg">
                                − Rp{{ number_format($order->discount(), 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif

                    <tr class="border-t border-line-soft">
                        <td class="pt-3 font-display text-sm font-bold text-ink-strong">Total Dibayar</td>
                        <td class="pt-3 text-right font-display text-base font-bold text-brand">
                            {{ $order->formattedAmount() }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </section>

        {{-- Masa aktif langganan --}}
        @if ($order->subscription)
            <section class="mt-5 rounded-lg bg-brand-soft px-4 py-3">
                <p class="text-xs tracking-wide text-brand uppercase">Masa Aktif Langganan</p>
                <p class="mt-1 text-sm text-ink">
                    {{ $order->subscription->started_at->translatedFormat('d F Y') }} sampai
                    {{ $order->subscription->ends_at->translatedFormat('d F Y') }}
                    ({{ $order->subscription->daysRemaining() }} hari tersisa)
                </p>
            </section>
        @endif

        {{-- Catatan --}}
        @if ($order->notes)
            <section class="mt-5 border-t border-line-soft pt-5">
                <p class="text-xs tracking-wide text-muted uppercase">Catatan</p>
                <p class="mt-1 text-xs leading-relaxed text-ink">{{ $order->notes }}</p>
            </section>
        @endif

        {{-- Kaki --}}
        <footer class="mt-6 border-t border-line-soft pt-5 text-center">
            <p class="text-xs leading-relaxed text-muted">
                Struk ini diterbitkan secara elektronik oleh sistem PRIM dan sah tanpa tanda tangan basah.<br>
                Simpan struk ini sebagai bukti pembayaran yang sah. Terima kasih telah berlangganan di PRIM.
            </p>
            <p class="mt-3 text-[11px] text-muted-2">
                Ilmu Komputer, FMIPA, Universitas Lambung Mangkurat · {{ now()->format('Y') }}
            </p>
        </footer>
    </article>
</div>

{{-- Aturan cetak: sembunyikan kerangka aplikasi dan sisakan struknya saja. --}}
<style>
    @media print {
        body { background: #ffffff !important; }
        body > header { display: none !important; }
        main { padding: 0 !important; max-width: none !important; }
        .print-hide { display: none !important; }
    }
</style>
