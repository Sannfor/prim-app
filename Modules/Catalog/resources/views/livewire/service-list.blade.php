<div>
    {{-- Kepala halaman: latar ungu sesuai desain --}}
    <section class="prim-hero-bg">
        <div class="prim-container pt-10 pb-8">
            <h1 class="text-center font-display text-[30px] font-bold text-white sm:text-[36px] lg:text-[40px]">
                Layanan
            </h1>
        </div>
    </section>

    <section class="prim-hero-bg pb-16">
        <div class="prim-container">
            {{-- Baris pencarian & filter --}}
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                <div class="relative">
                    <flux:icon.magnifying-glass class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-muted-2" />
                    <input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari Produk"
                        class="prim-input h-[49px] pl-12"
                    >
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <select wire:model.live="category" class="prim-input h-[49px]">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $item)
                            <option value="{{ $item->slug }}">{{ $item->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="sort" class="prim-input h-[49px]">
                        <option value="terbaru">Urutkan: Terbaru</option>
                        <option value="termurah">Harga terendah</option>
                        <option value="termahal">Harga tertinggi</option>
                        <option value="nama">Nama layanan</option>
                    </select>
                </div>
            </div>

            {{-- Ringkasan hasil + aksi bandingkan --}}
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-white/90">
                    Menampilkan {{ $services->count() }} dari {{ $services->total() }} layanan
                </p>

                <div class="flex items-center gap-3">
                    <div wire:loading class="text-xs text-white/80">Memuat hasil…</div>

                    @if (count($compareSelection) > 0)
                        <a href="{{ route('catalog.compare') }}" class="prim-btn h-9 bg-white px-5 text-sm !text-brand hover:bg-white/90" wire:navigate>
                            Bandingkan ({{ count($compareSelection) }})
                        </a>
                    @endif

                    @if ($this->hasActiveFilters())
                        <button type="button" wire:click="resetFilters" class="text-sm font-medium text-white underline underline-offset-4">
                            Reset filter
                        </button>
                    @endif
                </div>
            </div>

            {{-- Grid kartu layanan --}}
            @if ($services->isEmpty())
                <div class="mt-8 rounded-xl bg-white p-12 text-center">
                    <flux:icon.magnifying-glass class="mx-auto size-8 text-muted-2" />
                    <p class="mt-3 font-medium text-ink-strong">Tidak ada layanan yang cocok</p>
                    <p class="mt-1 text-sm text-muted">Coba ubah kata kunci atau bersihkan filter yang aktif.</p>

                    @if ($this->hasActiveFilters())
                        <button type="button" wire:click="resetFilters" class="prim-btn mt-5">
                            Bersihkan filter
                        </button>
                    @endif
                </div>
            @else
                <div class="mt-6 grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($services as $service)
                        @php
                            $isCompared = in_array($service->id, $compareSelection, true);
                            $activePlans = $service->plans->where('is_active', true)->sortBy('price');
                            $firstPlan = $activePlans->first();
                            $discountedPlan = $activePlans->firstWhere(fn ($plan) => $plan->hasDiscount());
                            $preorderPlan = $activePlans->firstWhere('is_preorder', true);
                        @endphp

                        <article
                            wire:key="service-{{ $service->id }}"
                            class="prim-service-card relative transition hover:shadow-brand-sm"
                        >
                            @if ($discountedPlan)
                                <span class="prim-ribbon bg-tag">{{ $discountedPlan->discountLabel() }}</span>
                            @elseif ($preorderPlan)
                                <span class="prim-ribbon bg-brand">{{ 'Preorder' }}</span>
                            @endif

                            <div class="p-4 pb-3">
                                <x-service-logo :service="$service" class="h-14" />

                                <h2 class="mt-3.5 font-display text-base font-semibold text-ink-strong">
                                    {{ $service->name }}
                                </h2>

                                <div class="mt-2.5 space-y-2.5">
                                    @forelse ($activePlans as $plan)
                                        <div>
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-[13px] text-ink">{{ $plan->groupLabel() }}</p>
                                                <span class="prim-tag-period">{{ $plan->periodsLabel() }}</span>
                                            </div>

                                            <p class="text-sm font-semibold text-ink-strong">
                                                Rp{{ Number::format($plan->price, locale: 'id') }}
                                                <span class="text-xs font-normal text-muted">/ bln</span>
                                            </p>
                                        </div>
                                    @empty
                                        <p class="text-sm text-status-cancel-fg">Produk Varian tidak tersedia</p>
                                    @endforelse
                                </div>

                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <a
                                        href="{{ route('catalog.show', $service->slug) }}"
                                        class="text-[13px] text-muted underline-offset-2 hover:text-brand hover:underline"
                                        wire:navigate
                                    >
                                        Lihat Skema Harga
                                    </a>

                                    <button
                                        type="button"
                                        wire:click="toggleCompare({{ $service->id }})"
                                        class="flex size-7 items-center justify-center rounded-full border transition {{ $isCompared ? 'border-brand bg-brand text-white' : 'border-line-soft text-muted hover:border-brand hover:text-brand' }}"
                                        title="{{ $isCompared ? 'Lepas dari perbandingan' : 'Tambah ke perbandingan' }}"
                                    >
                                        <flux:icon :name="$isCompared ? 'check' : 'plus'" class="size-4" />
                                    </button>
                                </div>
                            </div>

                            <div class="mt-auto p-4 pt-0">
                                @if ($firstPlan)
                                    <a
                                        href="{{ route('transaction.checkout', $firstPlan) }}"
                                        class="prim-btn prim-btn-block"
                                        wire:navigate
                                    >Pesan</a>
                                @else
                                    <span class="prim-btn prim-btn-block cursor-not-allowed bg-line-soft !text-muted">
                                        Belum tersedia
                                    </span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-10 [&_a]:text-white [&_span]:text-white/80 [&_button]:text-white">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- Keterangan tambahan di luar area ungu --}}
    <section class="prim-container py-14">
        <div class="grid gap-5 sm:grid-cols-3">
            @foreach ([
                ['icon' => 'shield-check', 'title' => 'Akun bergaransi', 'desc' => 'Setiap pembelian dilindungi garansi selama masa aktif langganan.'],
                ['icon' => 'bolt', 'title' => 'Proses cepat', 'desc' => 'Pesanan diproses maksimal 1x24 jam setelah pembayaran terverifikasi.'],
                ['icon' => 'chat-bubble-left-right', 'title' => 'Dukungan langsung', 'desc' => 'Tim PRIM siap membantu melalui kanal WhatsApp resmi.'],
            ] as $item)
                <div class="prim-card flex items-start gap-4 p-5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-brand-soft">
                        <flux:icon :name="$item['icon']" class="size-5 text-brand" />
                    </span>
                    <div>
                        <h3 class="font-display text-sm font-semibold text-ink-strong">{{ $item['title'] }}</h3>
                        <p class="mt-1 text-sm text-muted">{{ $item['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
