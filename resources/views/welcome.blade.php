<x-layouts::app title="Beranda">
    {{-- Hero --}}
    <section class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 px-6 py-14 text-white sm:px-12">
        <p class="mb-3 inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-medium tracking-wide uppercase">
            Aggregator Layanan Premium
        </p>
        <h1 class="max-w-3xl text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
            Temukan, bandingkan, dan berlangganan layanan digital premium dalam satu tempat.
        </h1>
        <p class="mt-5 max-w-2xl text-base/relaxed text-indigo-100">
            PRIM mengumpulkan katalog layanan dari berbagai penyedia, menyajikannya secara transparan,
            dan membantu Anda memilih paket yang paling sesuai — tanpa perlu berpindah-pindah platform.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
            <flux:button :href="route('catalog.index')" variant="filled" icon="squares-2x2" wire:navigate>
                Jelajahi Katalog
            </flux:button>
            <flux:button :href="route('catalog.compare')" variant="ghost" icon="arrows-right-left" class="text-white! hover:bg-white/10!" wire:navigate>
                Bandingkan Layanan
            </flux:button>
        </div>
    </section>

    {{-- Keunggulan --}}
    <section class="mt-14">
        <h2 class="text-xl font-semibold tracking-tight">Kenapa memakai PRIM?</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => 'magnifying-glass', 'title' => 'Pencarian Terpusat', 'desc' => 'Satu katalog untuk seluruh layanan premium, lengkap dengan filter kategori dan rentang harga.'],
                ['icon' => 'arrows-right-left', 'title' => 'Komparasi Side-by-Side', 'desc' => 'Bandingkan fitur, durasi, jumlah perangkat, dan harga beberapa paket sekaligus.'],
                ['icon' => 'credit-card', 'title' => 'Transaksi Terintegrasi', 'desc' => 'Proses pembelian terorganisir dengan status transaksi yang jelas dan dapat dipantau.'],
                ['icon' => 'clock', 'title' => 'Riwayat Lengkap', 'desc' => 'Seluruh transaksi dan masa aktif langganan tersimpan rapi dan mudah ditelusuri.'],
            ] as $feature)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:icon :name="$feature['icon']" class="size-6 text-indigo-600 dark:text-indigo-400" />
                    <h3 class="mt-3 font-semibold">{{ $feature['title'] }}</h3>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Alur pemakaian --}}
    <section class="mt-14 rounded-2xl border border-zinc-200 bg-white p-6 sm:p-8 dark:border-zinc-700 dark:bg-zinc-800">
        <h2 class="text-xl font-semibold tracking-tight">Cara kerjanya</h2>
        <ol class="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                'Telusuri katalog dan saring layanan sesuai kebutuhan.',
                'Bandingkan beberapa paket secara berdampingan.',
                'Lakukan pemesanan dan selesaikan pembayaran.',
                'Pantau masa aktif langganan dan perpanjang kapan saja.',
            ] as $index => $step)
                <li class="flex gap-3">
                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                        {{ $index + 1 }}
                    </span>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $step }}</p>
                </li>
            @endforeach
        </ol>
    </section>
</x-layouts::app>
