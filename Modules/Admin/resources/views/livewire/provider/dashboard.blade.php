<div class="space-y-7">
    {{-- Kepala halaman --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-medium text-ink-strong">Dashboard Penyedia</h1>
            <p class="mt-1 text-sm text-muted">
                Ringkasan layanan milik {{ $provider->name }} pada katalog PRIM.
            </p>
        </div>

        <a href="{{ route('provider.services.index') }}" class="prim-btn h-10" wire:navigate>
            <flux:icon.cube class="size-4" />
            Kelola Layanan
        </a>
    </div>

    {{-- Kartu metrik --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl bg-canvas p-5">
            <p class="text-sm text-muted">Layanan Saya</p>
            <p class="mt-1.5 font-display text-xl font-medium text-ink-strong">{{ $ringkasan['layanan'] }}</p>
            <p class="mt-1 text-xs text-muted">Terdaftar atas nama {{ $provider->name }}</p>
        </div>

        <div class="rounded-xl bg-canvas p-5">
            <p class="text-sm text-muted">Paket Langganan</p>
            <p class="mt-1.5 font-display text-xl font-medium text-ink-strong">{{ $ringkasan['paketAktif'] }}</p>
            <p class="mt-1 text-xs text-muted">Seluruh paket pada layanan Anda</p>
        </div>

        <div class="rounded-xl bg-canvas p-5">
            <p class="text-sm text-muted">Pesanan Layanan</p>
            <p class="mt-1.5 font-display text-xl font-medium text-ink-strong">{{ $ringkasan['pesanan'] }}</p>
            <p class="mt-1 text-xs text-muted">Seluruh pesanan atas layanan Anda</p>
        </div>

        <div class="rounded-xl bg-canvas p-5">
            <p class="text-sm text-muted">Pendapatan</p>
            <p class="mt-1.5 font-display text-xl font-medium text-ink-strong">
                Rp{{ number_format($ringkasan['pendapatan'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-muted">Dari pesanan berhasil</p>
        </div>
    </section>

    {{-- Layanan milik penyedia --}}
    <section class="rounded-xl bg-white p-6 shadow-brand-xs">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-display text-base font-semibold text-ink-strong">Layanan Anda</h2>
                <p class="mt-1 text-xs text-muted">
                    Hanya layanan di bawah ini yang dapat Anda kelola. Layanan penyedia lain tidak terlihat di panel ini.
                </p>
            </div>
        </div>

        @if ($services->isEmpty())
            <div class="mt-5 rounded-xl border border-dashed border-line bg-white p-10 text-center">
                <flux:icon.cube class="mx-auto size-8 text-muted-2" />
                <p class="mt-3 font-medium">Belum ada layanan</p>
                <p class="mt-1 text-sm text-muted">
                    Tambahkan layanan pertama Anda agar dapat tampil pada katalog PRIM.
                </p>
                <a href="{{ route('provider.services.index') }}" class="prim-btn mt-4" wire:navigate>
                    Tambah Layanan
                </a>
            </div>
        @else
            <div class="mt-5 space-y-3">
                @foreach ($services as $service)
                    <article wire:key="layanan-{{ $service->id }}" class="rounded-xl bg-canvas p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-ink-strong">{{ $service->name }}</h3>
                                    <flux:badge size="sm" :color="$service->is_active ? 'green' : 'zinc'">
                                        {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </flux:badge>
                                </div>
                                <p class="mt-1 font-mono text-xs text-muted">{{ $service->slug }}</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-4 text-sm">
                                <div class="text-right">
                                    <p class="text-xs text-muted">Paket</p>
                                    <p class="font-medium text-ink-strong">{{ $service->plans_count }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-muted">Ulasan</p>
                                    <p class="font-medium text-ink-strong">{{ $service->reviews_count }}</p>
                                </div>

                                <button
                                    type="button"
                                    wire:click="kelola('{{ $service->slug }}')"
                                    class="prim-btn-ghost h-9 text-sm"
                                >
                                    <flux:icon.list-bullet class="size-4" />
                                    Kelola Paket
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Pesanan terbaru atas layanan penyedia --}}
    <section class="rounded-xl bg-white p-6 shadow-brand-xs">
        <h2 class="font-display text-base font-semibold text-ink-strong">Pesanan Terbaru atas Layanan Anda</h2>
        <p class="mt-1 text-xs text-muted">
            Pesanan ini hanya untuk dipantau. Verifikasi pembayaran tetap dilakukan administrator.
        </p>

        @if ($terbaru->isEmpty())
            <p class="mt-6 text-sm text-muted">Belum ada pesanan untuk layanan Anda.</p>
        @else
            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead class="prim-table-head bg-canvas">
                        <tr>
                            <th class="px-4 py-3">ID Pesanan</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($terbaru as $transaksi)
                            <tr wire:key="pesanan-{{ $transaksi->id }}" class="border-b border-line-soft last:border-0">
                                <td class="px-4 py-3 font-mono text-xs">{{ $transaksi->order_code }}</td>
                                <td class="px-4 py-3 text-xs text-muted whitespace-nowrap">
                                    {{ $transaksi->created_at->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-ink">{{ $transaksi->user->name }}</p>
                                    <p class="text-xs text-muted">{{ $transaksi->user->email }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-ink">{{ $transaksi->plan->service->name }}</p>
                                    <p class="text-xs text-muted">{{ $transaksi->plan->groupLabel() }}</p>
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap">{{ $transaksi->formattedAmount() }}</td>
                                <td class="px-4 py-3">
                                    <x-status-badge :status="$transaksi->status" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
