<div class="space-y-6">
    {{-- Kepala halaman --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Katalog Layanan Premium</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Telusuri, saring, dan bandingkan layanan digital dari berbagai penyedia.
            </p>
        </div>

        @if (count($compareSelection) > 0)
            <flux:button :href="route('catalog.compare')" variant="primary" icon="arrows-right-left" wire:navigate>
                Bandingkan ({{ count($compareSelection) }})
            </flux:button>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-[18rem_1fr]">
        {{-- Panel filter --}}
        <aside class="space-y-5 rounded-xl border border-zinc-200 bg-white p-5 lg:sticky lg:top-6 lg:self-start dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Filter</h2>
                @if ($this->hasActiveFilters())
                    <flux:button wire:click="resetFilters" variant="subtle" size="sm" icon="arrow-path">
                        Reset
                    </flux:button>
                @endif
            </div>

            <flux:input
                wire:model.live.debounce.400ms="search"
                icon="magnifying-glass"
                placeholder="Cari layanan…"
                label="Pencarian"
                clearable
            />

            <flux:select wire:model.live="category" label="Kategori">
                <flux:select.option value="">Semua kategori</flux:select.option>
                @foreach ($categories as $item)
                    <flux:select.option value="{{ $item->slug }}">{{ $item->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="provider" label="Penyedia">
                <flux:select.option value="">Semua penyedia</flux:select.option>
                @foreach ($providers as $item)
                    <flux:select.option value="{{ $item->slug }}">{{ $item->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model.live.debounce.500ms="maxPrice"
                type="number"
                min="0"
                step="5000"
                label="Harga maksimum (Rp)"
                placeholder="Contoh: 100000"
                description="Menampilkan layanan yang memiliki paket di bawah harga ini."
            />

            <flux:select wire:model.live="sort" label="Urutkan">
                <flux:select.option value="terbaru">Terbaru</flux:select.option>
                <flux:select.option value="termurah">Harga terendah</flux:select.option>
                <flux:select.option value="termahal">Harga tertinggi</flux:select.option>
                <flux:select.option value="nama">Nama layanan</flux:select.option>
            </flux:select>

            <div wire:loading class="text-xs text-zinc-500">Memuat hasil…</div>
        </aside>

        {{-- Hasil --}}
        <section class="space-y-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Menampilkan {{ $services->count() }} dari {{ $services->total() }} layanan.
            </p>

            @if ($services->isEmpty())
                <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-600 dark:bg-zinc-800">
                    <flux:icon.magnifying-glass class="mx-auto size-8 text-zinc-400" />
                    <p class="mt-3 font-medium">Tidak ada layanan yang cocok</p>
                    <p class="mt-1 text-sm text-zinc-500">Coba ubah kata kunci atau bersihkan filter yang aktif.</p>
                    @if ($this->hasActiveFilters())
                        <flux:button wire:click="resetFilters" variant="filled" size="sm" class="mt-4">
                            Bersihkan filter
                        </flux:button>
                    @endif
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($services as $service)
                        @php $isCompared = in_array($service->id, $compareSelection, true); @endphp

                        <article
                            wire:key="service-{{ $service->id }}"
                            class="flex flex-col rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-indigo-300 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-indigo-700"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                                        {{ Str::of($service->name)->substr(0, 2)->upper() }}
                                    </span>
                                    <div>
                                        <h3 class="font-semibold leading-tight">{{ $service->name }}</h3>
                                        <p class="text-xs text-zinc-500">{{ $service->provider->name }}</p>
                                    </div>
                                </div>
                            </div>

                            <flux:badge size="sm" color="indigo" class="mt-3 self-start">
                                {{ $service->category->name }}
                            </flux:badge>

                            <p class="mt-3 line-clamp-2 flex-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $service->tagline }}
                            </p>

                            <div class="mt-4 flex items-baseline gap-1">
                                @if ($service->lowestPrice() !== null)
                                    <span class="text-xs text-zinc-500">Mulai</span>
                                    <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                        Rp{{ Number::format($service->lowestPrice(), locale: 'id') }}
                                    </span>
                                @else
                                    <span class="text-sm text-zinc-500">Belum ada paket aktif</span>
                                @endif
                            </div>

                            <p class="mt-1 text-xs text-zinc-500">
                                {{ $service->plans->count() }} paket tersedia
                                @if ($service->averageRating() !== null)
                                    · ★ {{ $service->averageRating() }}
                                @endif
                            </p>

                            <div class="mt-4 flex gap-2">
                                <flux:button
                                    :href="route('catalog.show', $service->slug)"
                                    variant="filled"
                                    size="sm"
                                    class="flex-1"
                                    wire:navigate
                                >
                                    Lihat Detail
                                </flux:button>

                                <flux:button
                                    wire:click="toggleCompare({{ $service->id }})"
                                    variant="{{ $isCompared ? 'primary' : 'ghost' }}"
                                    size="sm"
                                    icon="{{ $isCompared ? 'check' : 'arrows-right-left' }}"
                                    title="Tambah/lepas dari perbandingan"
                                />
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pt-2">
                    {{ $services->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
