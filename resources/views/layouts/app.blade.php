@php
    /*
     | Tata letak publik PRIM.
     |
     | Dipakai oleh komponen Livewire lewat atribut #[Layout('layouts::app')] dan
     | oleh halaman Blade biasa lewat komponen <x-layouts::app>.
     |
     | Catatan: berkas ini sengaja tidak memakai @props karena @props membuat
     | Blade memperlakukannya sebagai komponen anonim; Livewire membutuhkannya
     | sebagai view biasa. Nilai judul diberi nilai bawaan secara langsung.
     */
    $title = $title ?? null;
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', [
            'title' => $title
                ? $title.' — '.config('app.name')
                : config('app.name').' — Aggregator Layanan Premium',
        ])
    </head>
    <body class="min-h-screen bg-white text-ink antialiased">
        {{-- Navbar publik: latar #D3CFFF sesuai desain Figma --}}
        <header class="relative z-30 bg-brand-surface" x-data="{ open: false }">
            <div class="mx-auto flex h-[86px] w-full max-w-[1728px] items-center gap-6 px-6 lg:px-12">
                <a href="{{ route('home') }}" class="shrink-0" wire:navigate>
                    <x-brand-logo size="lg" />
                </a>

                <nav class="ml-auto hidden items-center gap-10 text-[15px] font-medium lg:flex">
                    <a
                        href="{{ route('home') }}"
                        @class([
                            'transition',
                            'text-brand' => request()->routeIs('home'),
                            'text-ink hover:text-brand' => ! request()->routeIs('home'),
                        ])
                        wire:navigate
                    >Beranda</a>

                    <a
                        href="{{ route('catalog.index') }}"
                        @class([
                            'transition',
                            'text-brand' => request()->routeIs('catalog.*'),
                            'text-ink hover:text-brand' => ! request()->routeIs('catalog.*'),
                        ])
                        wire:navigate
                    >Layanan</a>

                    <a
                        href="{{ route('catalog.how-to-subscribe') }}"
                        @class([
                            'transition',
                            'text-brand' => request()->routeIs('catalog.how-to-subscribe'),
                            'text-ink hover:text-brand' => ! request()->routeIs('catalog.how-to-subscribe'),
                        ])
                        wire:navigate
                    >Cara Berlangganan</a>

                    <a
                        href="{{ route('support.report') }}"
                        @class([
                            'transition',
                            'text-brand' => request()->routeIs('support.*'),
                            'text-ink hover:text-brand' => ! request()->routeIs('support.*'),
                        ])
                    >Laporan Kendala</a>
                </nav>

                <div class="ml-auto flex items-center gap-3 lg:ml-0">
                    @auth
                        <flux:dropdown position="bottom" align="end">
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-full py-1 pr-3 pl-1 transition hover:bg-white/40"
                            >
                                <span class="flex size-8 items-center justify-center rounded-full bg-brand text-xs font-medium text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                                <span class="hidden text-sm font-medium text-ink sm:block">
                                    {{ auth()->user()->name }}
                                </span>
                                <flux:icon.chevron-down class="size-4 text-muted" />
                            </button>

                            <flux:menu>
                                <flux:menu.item :href="route('profile.orders')" icon="receipt-percent" wire:navigate>
                                    Pesanan Saya
                                </flux:menu.item>
                                <flux:menu.item :href="route('subscription.index')" icon="arrow-path-rounded-square" wire:navigate>
                                    Langganan Saya
                                </flux:menu.item>
                                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                                    Pengaturan
                                </flux:menu.item>

                                @if ($isAdmin)
                                    <flux:menu.separator />
                                    <flux:menu.item :href="route('admin.dashboard')" icon="chart-bar-square" wire:navigate>
                                        Panel Pengelola
                                    </flux:menu.item>
                                @endif

                                <flux:menu.separator />

                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                        Keluar
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    @else
                        <a href="{{ route('login') }}" class="prim-btn-outline-white hidden lg:inline-flex" wire:navigate>
                            Login
                        </a>
                        <a href="{{ route('login') }}" class="prim-btn text-sm lg:hidden" wire:navigate>
                            Masuk
                        </a>
                    @endauth

                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-md text-ink lg:hidden"
                        @click="open = ! open"
                        aria-label="Buka menu"
                    >
                        <flux:icon.bars-2 class="size-6" x-show="! open" />
                        <flux:icon.x-mark class="size-6" x-show="open" x-cloak />
                    </button>
                </div>
            </div>

            {{-- Menu layar kecil --}}
            <div class="border-t border-white/40 bg-brand-surface px-6 pb-4 lg:hidden" x-show="open" x-cloak>
                <nav class="flex flex-col py-2 text-[15px] font-medium text-ink">
                    <a href="{{ route('home') }}" class="py-2.5" wire:navigate>Beranda</a>
                    <a href="{{ route('catalog.index') }}" class="py-2.5" wire:navigate>Layanan</a>
                    <a href="{{ route('catalog.how-to-subscribe') }}" class="py-2.5" wire:navigate>Cara Berlangganan</a>
                    <a href="{{ route('support.report') }}" class="py-2.5">Laporan Kendala</a>

                    @guest
                        <a href="{{ route('login') }}" class="py-2.5 text-brand" wire:navigate>Login</a>
                    @endguest
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="mt-auto bg-brand-surface">
            <div class="mx-auto w-full max-w-[1728px] px-6 py-12 lg:px-12">
                <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                    <div>
                        <x-brand-logo size="lg" />
                        <p class="mt-5 max-w-sm text-sm leading-relaxed text-ink/80">
                            PRIM adalah agregator layanan digital premium: satu tempat untuk mencari,
                            membandingkan, berlangganan, dan memantau masa aktif langganan Anda.
                        </p>
                    </div>

                    <div>
                        <h3 class="font-display text-sm font-semibold tracking-wide text-ink uppercase">Jelajahi</h3>
                        <ul class="mt-4 space-y-2.5 text-sm text-ink/80">
                            <li><a href="{{ route('home') }}" class="hover:text-brand" wire:navigate>Beranda</a></li>
                            <li><a href="{{ route('catalog.index') }}" class="hover:text-brand" wire:navigate>Katalog Layanan</a></li>
                            <li><a href="{{ route('catalog.how-to-subscribe') }}" class="hover:text-brand" wire:navigate>Cara Berlangganan</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-display text-sm font-semibold tracking-wide text-ink uppercase">Bantuan</h3>
                        <ul class="mt-4 space-y-2.5 text-sm text-ink/80">
                            <li><a href="{{ route('support.report') }}" class="hover:text-brand">Laporan Kendala</a></li>
                            @auth
                                <li><a href="{{ route('profile.orders') }}" class="hover:text-brand" wire:navigate>Pesanan Saya</a></li>
                                <li><a href="{{ route('subscription.index') }}" class="hover:text-brand" wire:navigate>Langganan Saya</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="hover:text-brand" wire:navigate>Masuk</a></li>
                                <li><a href="{{ route('register') }}" class="hover:text-brand" wire:navigate>Daftar</a></li>
                            @endauth
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-display text-sm font-semibold tracking-wide text-ink uppercase">Kontak</h3>
                        <ul class="mt-4 space-y-2.5 text-sm text-ink/80">
                            <li>prim.kelompok7@example.id</li>
                            <li>+62 858 2812 0489</li>
                            <li>Banjarbaru, Kalimantan Selatan</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 flex flex-col gap-2 border-t border-white/60 pt-6 text-xs text-ink/70 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} PRIM — Platform Digital Aggregator Layanan Premium.</p>
                    <p>Ilmu Komputer, FMIPA, Universitas Lambung Mangkurat.</p>
                </div>
            </div>
        </footer>

        @fluxScripts
        <flux:toast.group />
    </body>
</html>
