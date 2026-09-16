<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Bandingkan Layanan</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Bandingkan hingga {{ \Modules\Catalog\Livewire\Comparison::MAX_SERVICES }} layanan sekaligus: harga, durasi, jumlah perangkat, dan fitur.
            </p>
        </div>

        @if (count($selected) > 0)
            <div class="flex gap-2">
                <flux:button wire:click="clear" variant="ghost" icon="trash">
                    Kosongkan
                </flux:button>
                <flux:button :href="route('catalog.index')" variant="filled" icon="squares-2x2" wire:navigate>
                    Tambah dari Katalog
                </flux:button>
            </div>
        @endif
    </div>

    @if ($services->isEmpty())
        {{-- Keadaan kosong --}}
        <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-600 dark:bg-zinc-800">
            <flux:icon.arrows-right-left class="mx-auto size-8 text-zinc-400" />
            <p class="mt-3 font-medium">Belum ada layanan yang dipilih</p>
            <p class="mx-auto mt-1 max-w-md text-sm text-zinc-500">
                Pilih layanan dari katalog dengan menekan tombol bandingkan, lalu bandingkan
                paket, harga, dan fiturnya di sini.
            </p>
            <flux:button :href="route('catalog.index')" variant="filled" class="mt-4" wire:navigate>
                Jelajahi Katalog
            </flux:button>
        </div>
    @else
        {{-- Tabel perbandingan --}}
        <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <table class="w-full min-w-[40rem] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="w-48 p-4 text-left align-bottom font-medium text-zinc-500">Perbandingan</th>

                        @foreach ($services as $service)
                            <th wire:key="head-{{ $service->id }}" class="p-4 text-left align-bottom">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <a
                                            href="{{ route('catalog.show', $service->slug) }}"
                                            class="font-semibold hover:text-indigo-600"
                                            wire:navigate
                                        >
                                            {{ $service->name }}
                                        </a>
                                        <p class="mt-0.5 text-xs font-normal text-zinc-500">{{ $service->provider->name }}</p>
                                    </div>
                                    <flux:button
                                        wire:click="remove({{ $service->id }})"
                                        variant="subtle"
                                        size="sm"
                                        icon="x-mark"
                                        title="Lepas dari perbandingan"
                                    />
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($rows as $index => $row)
                        <tr wire:key="row-{{ $index }}" class="border-b border-zinc-100 last:border-0 dark:border-zinc-700">
                            <th class="p-4 text-left font-medium text-zinc-500">{{ $row['label'] }}</th>

                            @foreach ($row['values'] as $columnIndex => $value)
                                <td
                                    wire:key="cell-{{ $index }}-{{ $columnIndex }}"
                                    @class([
                                        'p-4',
                                        'bg-green-50 font-semibold text-green-800 dark:bg-green-950 dark:text-green-300' => $row['highlight'] === $columnIndex,
                                    ])
                                >
                                    {{ $value }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    {{-- Baris aksi pembelian --}}
                    <tr>
                        <th class="p-4 text-left font-medium text-zinc-500">Paket termurah</th>
                        @foreach ($services as $service)
                            @php $cheapest = $service->plans->where('is_active', true)->sortBy('price')->first(); @endphp
                            <td wire:key="buy-{{ $service->id }}" class="p-4">
                                @if ($cheapest)
                                    <flux:button
                                        :href="route('transaction.checkout', $cheapest->id)"
                                        variant="filled"
                                        size="sm"
                                        icon="shopping-cart"
                                        wire:navigate
                                    >
                                        {{ $cheapest->formattedPrice() }}
                                    </flux:button>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $cheapest->name }}</p>
                                @else
                                    <span class="text-zinc-500">Belum ada paket</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-xs text-zinc-500">
            Sel berwarna hijau menandai nilai paling menguntungkan pada baris tersebut.
            Perbandingan hanya mempertimbangkan paket yang berstatus aktif.
        </p>
    @endif

    {{-- Saran layanan lain --}}
    @if ($available->isNotEmpty())
        <section>
            <h2 class="text-lg font-semibold tracking-tight">Tambahkan layanan lain</h2>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($available as $service)
                    <div
                        wire:key="available-{{ $service->id }}"
                        class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ $service->name }}</p>
                            <p class="truncate text-xs text-zinc-500">{{ $service->provider->name }}</p>
                        </div>

                        <flux:button
                            wire:click="add({{ $service->id }})"
                            variant="ghost"
                            size="sm"
                            icon="plus"
                            title="Tambahkan ke perbandingan"
                        />
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
