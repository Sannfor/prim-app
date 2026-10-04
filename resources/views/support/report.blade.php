@php
    /*
     | Halaman "Laporan Kendala" — frame Figma "Laporan Kendala".
     |
     | Desain mengarahkan pengguna ke kanal WhatsApp resmi PRIM dengan pesan
     | pembuka yang sudah disiapkan, bukan formulir di dalam aplikasi.
     */
    $phone = '6285828120489';
    $phoneLabel = '+62 858 2812 0489';

    $message = "Halo, saya ingin :\n"
        ."- Menanyakan detail produk atau promo\n"
        ."- Mengonfirmasi pesanan atau pembayaran\n"
        ."- Meminta bantuan terkait kendala atau layanan lainnya\n\n"
        ."Mohon dibantu ya!";

    $waLink = 'https://wa.me/'.$phone.'?text='.rawurlencode($message);

    $topics = [
        ['icon' => 'tag', 'title' => 'Detail produk & promo', 'desc' => 'Tanyakan varian paket, periode tagihan, atau promo yang sedang berlaku.'],
        ['icon' => 'receipt-percent', 'title' => 'Konfirmasi pesanan', 'desc' => 'Bantu cek status pembayaran dan kelanjutan pesananmu.'],
        ['icon' => 'wrench-screwdriver', 'title' => 'Kendala layanan', 'desc' => 'Laporkan akun yang tidak bisa dipakai atau kendala teknis lainnya.'],
        ['icon' => 'arrow-path-rounded-square', 'title' => 'Perpanjangan', 'desc' => 'Konsultasi perpanjangan langganan sebelum masa aktif berakhir.'],
    ];
@endphp

<x-layouts::app title="Laporan Kendala">
    <section class="prim-hero-bg">
        <div class="mx-auto w-full max-w-[1728px] px-6 py-14 lg:px-12">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <h1 class="font-display text-[30px] font-bold text-white sm:text-[36px]">
                        Laporan Kendala
                    </h1>

                    <p class="mt-4 max-w-lg text-sm leading-relaxed text-white/90 sm:text-base">
                        Tim PRIM siap membantu melalui WhatsApp resmi. Kirim pesan dan kendalamu
                        akan ditangani pada jam kerja, setiap hari pukul 08.00–21.00 WITA.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a
                            href="{{ $waLink }}"
                            target="_blank"
                            rel="noopener"
                            class="prim-btn h-[52px] gap-2 bg-white px-7 !text-brand hover:bg-white/90"
                        >
                            <flux:icon.chat-bubble-left-right class="size-5" />
                            Hubungi via WhatsApp
                        </a>

                        <a href="{{ route('catalog.index') }}" class="prim-btn-outline-white h-[52px] px-7" wire:navigate>
                            Kembali ke Katalog
                        </a>
                    </div>

                    <p class="mt-4 text-xs text-white/80">Nomor resmi: {{ $phoneLabel }}</p>
                </div>

                {{-- Pratinjau pesan yang akan terkirim --}}
                <div class="mx-auto w-full max-w-md">
                    <div class="rounded-2xl bg-white p-5 shadow-brand-sm">
                        <div class="flex items-center gap-3 border-b border-line-soft pb-3">
                            <span class="flex size-9 items-center justify-center rounded-full bg-status-done-bg text-status-done-fg">
                                <flux:icon.chat-bubble-oval-left class="size-5" />
                            </span>
                            <div>
                                <p class="font-display text-sm font-semibold text-ink-strong">Prim</p>
                                <p class="text-xs text-status-done-fg">online</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl rounded-tl-none bg-[#dcf8c6] p-3.5 text-[13px] leading-relaxed whitespace-pre-line text-ink-strong">
{{ $message }}
                        </div>

                        <a
                            href="{{ $waLink }}"
                            target="_blank"
                            rel="noopener"
                            class="prim-btn prim-btn-block mt-4 gap-2"
                        >
                            <flux:icon.arrow-top-right-on-square class="size-4" />
                            Continue to WhatsApp Web
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto w-full max-w-[1728px] px-6 lg:px-12">
            <h2 class="font-display text-2xl font-bold text-ink-strong">Yang bisa kami bantu</h2>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($topics as $topic)
                    <div class="prim-card p-6">
                        <span class="flex size-11 items-center justify-center rounded-xl bg-brand-soft">
                            <flux:icon :name="$topic['icon']" class="size-5 text-brand" />
                        </span>
                        <h3 class="mt-4 font-display text-base font-semibold text-ink-strong">{{ $topic['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $topic['desc'] }}</p>
                    </div>
                @endforeach
            </div>

            @auth
                <div class="mt-10 rounded-xl bg-canvas p-6">
                    <h3 class="font-display text-base font-semibold text-ink-strong">Sertakan detail pesananmu</h3>
                    <p class="mt-1 text-sm text-muted">
                        Agar penanganan lebih cepat, sebutkan kode pesanan yang tertera pada halaman Pesanan Saya.
                    </p>
                    <a href="{{ route('profile.orders') }}" class="prim-btn-ghost mt-4" wire:navigate>
                        Buka Pesanan Saya
                    </a>
                </div>
            @endauth
        </div>
    </section>
</x-layouts::app>
