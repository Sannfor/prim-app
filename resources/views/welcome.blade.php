@php
    use App\Models\Service;

    $featured = Service::query()
        ->active()
        ->withCatalogRelations()
        ->withMin(['plans as lowest_price' => fn ($q) => $q->where('is_active', true)], 'price')
        ->latest()
        ->take(8)
        ->get();

    // Ilustrasi hero hasil ekspor Figma (node "Prim" 718x430 pada frame Beranda).
    $heroIllustration = \App\Support\PrimAsset::figma('hero-beranda');
@endphp

<x-layouts::app title="Beranda">
    {{-- Hero: latar ungu penuh sesuai desain Figma --}}
    <section class="prim-hero-bg relative overflow-hidden">
        <div class="mx-auto grid w-full max-w-[1728px] items-center gap-10 px-6 pt-12 pb-28 lg:grid-cols-[minmax(0,44fr)_minmax(0,56fr)] lg:gap-6 lg:px-12 lg:pt-16 lg:pb-32">
            <div class="relative z-10">
                <h1 class="font-display text-[34px] leading-[1.12] font-bold text-white sm:text-[44px] lg:text-[52px]">
                    Satu Platform, Semua Layanan Premium Favoritmu
                </h1>

                <p class="mt-6 max-w-xl text-base leading-relaxed text-white/90 sm:text-lg">
                    Langsung cek pilihan layanan di PRIM sekarang juga!
                </p>

                <div class="mt-10 flex flex-wrap items-center gap-4">
                    <a href="{{ route('catalog.index') }}" class="prim-btn h-[68px] px-10 text-base sm:text-lg" wire:navigate>
                        Lihat Layanan
                    </a>

                    <a href="{{ route('catalog.how-to-subscribe') }}" class="text-base font-medium text-white underline decoration-white/40 underline-offset-4 transition hover:decoration-white" wire:navigate>
                        Cara berlangganan
                    </a>
                </div>
            </div>

            <div class="relative z-10 flex items-center justify-center">
                @if ($heroIllustration)
                    <img
                        src="{{ $heroIllustration }}"
                        alt="Ilustrasi pengguna layanan premium PRIM"
                        class="w-full max-w-[820px] object-contain"
                        fetchpriority="high"
                    >
                @endif
            </div>
        </div>

        {{-- Transisi awan di kaki hero --}}
        <x-prim-clouds class="absolute inset-x-0 bottom-0" />
    </section>

    {{-- Layanan populer --}}
    <section class="mx-auto w-full max-w-[1728px] px-6 pt-20 lg:px-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-display text-2xl font-bold text-ink-strong sm:text-3xl">Layanan Populer</h2>
                <p class="mt-2 text-sm text-muted">Pilihan layanan premium yang paling banyak dicari di PRIM.</p>
            </div>

            <a href="{{ route('catalog.index') }}" class="text-sm font-medium text-brand hover:underline" wire:navigate>
                Lihat semua layanan →
            </a>
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($featured as $service)
                <a
                    href="{{ route('catalog.show', $service->slug) }}"
                    class="prim-service-card group p-5 transition hover:shadow-brand-sm"
                    wire:navigate
                >
                    <x-service-logo :service="$service" class="h-20" />

                    <h3 class="mt-4 font-display text-lg font-semibold text-ink-strong">{{ $service->name }}</h3>

                    <p class="mt-1 text-xs text-muted">{{ $service->category?->name }}</p>

                    <p class="mt-4 text-sm text-muted">
                        @if ($service->lowest_price)
                            Mulai
                            <span class="font-semibold text-brand">Rp{{ Number::format($service->lowest_price, locale: 'id') }}</span>
                            <span class="text-xs">/ bln</span>
                        @else
                            Belum ada paket aktif
                        @endif
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Keunggulan --}}
    <section class="mx-auto w-full max-w-[1728px] px-6 py-20 lg:px-12">
        <h2 class="font-display text-2xl font-bold text-ink-strong sm:text-3xl">Kenapa memakai PRIM?</h2>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => 'magnifying-glass', 'title' => 'Pencarian Terpusat', 'desc' => 'Satu katalog untuk seluruh layanan premium, lengkap dengan filter kategori dan rentang harga.'],
                ['icon' => 'arrows-right-left', 'title' => 'Skema Harga Transparan', 'desc' => 'Setiap varian paket dan periode tagihan ditampilkan apa adanya, tanpa biaya tersembunyi.'],
                ['icon' => 'credit-card', 'title' => 'Pembayaran Fleksibel', 'desc' => 'Virtual Account, e-wallet, QRIS, hingga gerai retail dalam satu alur pemesanan.'],
                ['icon' => 'clock', 'title' => 'Riwayat Lengkap', 'desc' => 'Seluruh pesanan dan masa aktif langganan tersimpan rapi dan mudah ditelusuri.'],
            ] as $feature)
                <div class="prim-card p-6">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-brand-soft">
                        <flux:icon :name="$feature['icon']" class="size-5 text-brand" />
                    </span>
                    <h3 class="mt-4 font-display text-base font-semibold text-ink-strong">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Ajakan --}}
    <section class="mx-auto w-full max-w-[1728px] px-6 pb-24 lg:px-12">
        <div class="prim-hero-bg flex flex-wrap items-center justify-between gap-6 rounded-2xl px-8 py-10 lg:px-12">
            <div>
                <h2 class="font-display text-xl font-bold text-white sm:text-2xl">Siap berlangganan?</h2>
                <p class="mt-1 text-sm text-white/85">
                    Buat akun PRIM, pilih paket, dan bayar dengan kanal favoritmu.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('catalog.index') }}" class="prim-btn h-12 px-7" wire:navigate>Mulai Jelajahi</a>

                @guest
                    <a href="{{ route('register') }}" class="prim-btn-outline-white h-12 px-7" wire:navigate>Daftar Sekarang</a>
                @endguest
            </div>
        </div>
    </section>
</x-layouts::app>
