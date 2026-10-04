@php
    /*
     | Halaman "Cara Berlangganan" — frame Figma "Cara Berlangganan".
     |
     | Lima langkah berlangganan ditampilkan sebagai kartu setengah lingkaran
     | (radius atas 117px) berwarna ungu, diikuti daftar kanal pembayaran.
     */
    $steps = [
        ['number' => 1, 'title' => 'Pesan Layanan', 'asset' => 'step-1'],
        ['number' => 2, 'title' => 'Pembayaran', 'asset' => 'step-2'],
        ['number' => 3, 'title' => 'Menunggu Proses', 'asset' => 'step-3'],
        ['number' => 4, 'title' => 'Pesanan Diterima', 'asset' => 'step-4'],
        ['number' => 5, 'title' => 'Selesai', 'asset' => 'step-5'],
    ];

    $methods = [
        ['name' => 'PermataBank', 'slug' => 'permata'],
        ['name' => 'BSI', 'slug' => 'bsi'],
        ['name' => 'BCA', 'slug' => 'bca'],
        ['name' => 'BNI', 'slug' => 'bni'],
        ['name' => 'Mandiri', 'slug' => 'mandiri'],
        ['name' => 'OVO', 'slug' => 'ovo'],
        ['name' => 'DANA', 'slug' => 'dana'],
        ['name' => 'ShopeePay', 'slug' => 'shopeepay'],
        ['name' => 'Alfamart', 'slug' => 'alfamart'],
        ['name' => 'Bank BRI', 'slug' => 'bri'],
        ['name' => 'LinkAja', 'slug' => 'linkaja'],
    ];
@endphp

<x-layouts::app title="Cara Berlangganan">
    <section class="prim-hero-bg">
        <div class="mx-auto w-full max-w-[1728px] px-6 pt-12 pb-20 lg:px-12">
            <h1 class="text-center font-display text-[30px] font-bold text-white sm:text-[34px]">
                Cara Berlangganan
            </h1>
            <p class="mt-2 text-center text-sm text-white/85 sm:text-base">
                Cara praktis berlangganan layanan premium di PRIM
            </p>

            {{-- Lima langkah --}}
            <ol class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($steps as $step)
                    <li class="prim-step-card relative">
                        <div class="flex h-[86px] items-end justify-center">
                            @if ($stepUrl = \App\Support\PrimAsset::figma($step['asset']))
                                <img
                                    src="{{ $stepUrl }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="h-[86px] w-auto"
                                >
                            @endif
                        </div>

                        <p class="mt-3 flex items-center justify-center gap-2 text-sm font-medium">
                            <span class="flex size-5 items-center justify-center rounded-full bg-white text-[11px] font-semibold text-brand">
                                {{ $step['number'] }}
                            </span>
                            {{ $step['title'] }}
                        </p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto w-full max-w-[1728px] px-6 lg:px-12">
            <h2 class="text-center font-display text-[26px] font-bold text-ink-strong sm:text-[30px]">
                Metode Pembayaran
            </h2>

            <p class="mx-auto mt-3 max-w-2xl text-center text-sm leading-relaxed text-muted">
                Bertransaksi di Akunmu sudah sangat mudah karena dilengkapi dengan beragam
                metode pembayaran. Mulai dari Virtual Account, E-wallet, QRIS hingga retail.
            </p>

            {{-- Lima tile per baris, sesuai susunan pada desain --}}
            <div class="mx-auto mt-10 grid max-w-4xl grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($methods as $method)
                    <x-payment-tile :method="$method" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Rincian tiap langkah --}}
    <section class="bg-canvas py-16">
        <div class="mx-auto w-full max-w-[1728px] px-6 lg:px-12">
            <h2 class="font-display text-2xl font-bold text-ink-strong">Rincian tiap langkah</h2>

            <div class="mt-8 grid gap-5 lg:grid-cols-2">
                @foreach ([
                    ['title' => 'Pesan Layanan', 'desc' => 'Pilih layanan pada katalog, tentukan varian paket dan periode tagihan, lalu tekan tombol Pesan untuk membuat pesanan.'],
                    ['title' => 'Pembayaran', 'desc' => 'Pilih kanal pembayaran yang paling nyaman, lalu selesaikan pembayaran sesuai nominal dan batas waktu yang tertera.'],
                    ['title' => 'Menunggu Proses', 'desc' => 'Setelah pembayaran terverifikasi, pesanan masuk ke antrean pemrosesan. Rata-rata selesai dalam 1x24 jam.'],
                    ['title' => 'Pesanan Diterima', 'desc' => 'Kredensial akun atau detail pesanan dikirim ke halaman Pesanan Saya dan dapat dibuka kapan saja.'],
                    ['title' => 'Selesai', 'desc' => 'Langganan aktif dan masa aktif tercatat pada halaman Langganan Saya, lengkap dengan pengingat sebelum berakhir.'],
                ] as $index => $detail)
                    <div class="prim-card flex gap-4 p-6">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand text-sm font-semibold text-white">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <h3 class="font-display text-base font-semibold text-ink-strong">{{ $detail['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $detail['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ route('catalog.index') }}" class="prim-btn h-12 px-7" wire:navigate>Lihat Katalog Layanan</a>
                <a href="{{ route('support.report') }}" class="prim-btn-ghost h-12 px-7">Laporkan Kendala</a>
            </div>
        </div>
    </section>
</x-layouts::app>
