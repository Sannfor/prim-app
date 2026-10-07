@php
    /*
     | Tata letak halaman akun pelanggan.
     |
     | Dipakai oleh halaman Pesanan Saya, Kode Login, Langganan, Riwayat Transaksi,
     | Konfirmasi Pemesanan, dan Pengaturan.
     |
     | Berbeda dari layouts::app (halaman publik), tata letak ini sengaja dibuat
     | ramping: bilah navigasi pendek tanpa daftar menu publik, tanpa footer besar,
     | dan dengan bilah samping akun. Dengan begitu isi halaman menjadi pusat
     | perhatian pengguna, bukan kerangka situsnya.
     */
    $title = $title ?? null;
    $user = auth()->user();

    $menu = [
        ['route' => 'profile.orders', 'label' => 'Pesanan', 'icon' => 'receipt-percent', 'active' => ['profile.orders']],
        ['route' => 'profile.login-code', 'label' => 'Kode Login', 'icon' => 'key', 'active' => ['profile.login-code']],
        ['route' => 'subscription.index', 'label' => 'Langganan', 'icon' => 'arrow-path-rounded-square', 'active' => ['subscription.*']],
        ['route' => 'transaction.index', 'label' => 'Transaksi', 'icon' => 'banknotes', 'active' => ['transaction.*']],
        ['route' => 'settings.profile', 'label' => 'Pengaturan', 'icon' => 'cog-6-tooth', 'active' => ['settings.*']],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', [
            'title' => $title
                ? $title.' — '.config('app.name')
                : 'Akun Saya — '.config('app.name'),
        ])
    </head>
    <body class="min-h-screen bg-canvas text-ink antialiased" x-data="{ menuOpen: false }">
        {{-- Bilah navigasi ramping: hanya logo, tautan katalog, dan menu akun --}}
        <header class="sticky top-0 z-30 border-b border-line-soft bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-[64px] w-full max-w-[1440px] items-center gap-4 px-5 lg:px-8">
                <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-md text-ink lg:hidden"
                    @click="menuOpen = true"
                    aria-label="Buka menu akun"
                >
                    <flux:icon.bars-2 class="size-5" />
                </button>

                <a href="{{ route('home') }}" class="shrink-0" wire:navigate>
                    <x-brand-logo size="md" />
                </a>

                <flux:spacer />

                <a
                    href="{{ route('catalog.index') }}"
                    class="hidden items-center gap-2 text-sm font-medium text-ink transition hover:text-brand sm:flex"
                    wire:navigate
                >
                    <flux:icon.squares-2x2 class="size-4" />
                    Jelajahi Katalog
                </a>

                @if ($user?->isAdmin())
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="hidden items-center gap-2 text-sm font-medium text-ink transition hover:text-brand sm:flex"
                        wire:navigate
                    >
                        <flux:icon.chart-bar-square class="size-4" />
                        Panel Pengelola
                    </a>
                @endif

                {{-- Lonceng notifikasi pembeli --}}
                <livewire:buyer-notification-bell />

                <flux:dropdown position="bottom" align="end">
                    <button type="button" class="flex items-center gap-2 rounded-full py-1 pr-2 pl-1 transition hover:bg-canvas">
                        <span class="flex size-8 items-center justify-center rounded-full bg-brand text-xs font-medium text-white">
                            {{ $user?->initials() }}
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
                        <flux:menu.item :href="route('settings.profile')" icon="cog-6-tooth" wire:navigate>
                            Pengaturan
                        </flux:menu.item>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                Keluar
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </header>

        {{--
            Lebar area akun dibatasi 1120px, dan lebar kolom bilah samping
            disamakan dengan lebar bilahnya (264px). Bila kolom lebih sempit
            daripada isinya, bilah samping akan meluber menutupi kolom isi.
        --}}
        <div class="mx-auto w-full max-w-[1120px] px-5 py-8 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[264px_minmax(0,1fr)]">
                {{-- Bilah samping akun --}}
                <aside
                    class="fixed inset-y-0 left-0 z-40 w-[264px] min-w-0 overflow-y-auto border-r border-line-soft bg-white p-5 transition-transform lg:sticky lg:top-[80px] lg:z-0 lg:h-fit lg:translate-x-0 lg:rounded-xl lg:border lg:p-4 lg:shadow-brand-xs"
                    :class="menuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                >
                    <div class="flex items-center justify-between lg:hidden">
                        <x-brand-logo size="sm" />
                        <button
                            type="button"
                            class="flex size-8 items-center justify-center rounded-md text-ink"
                            @click="menuOpen = false"
                            aria-label="Tutup menu akun"
                        >
                            <flux:icon.x-mark class="size-5" />
                        </button>
                    </div>

                    <div class="mt-4 hidden items-center gap-3 rounded-lg bg-canvas p-3 lg:flex">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand text-xs font-medium text-white">
                            {{ $user?->initials() }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink-strong">{{ $user?->name }}</p>
                            <p class="truncate text-xs text-muted">{{ $user?->email }}</p>
                        </div>
                    </div>

                    <nav class="mt-4 space-y-1">
                        @foreach ($menu as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                                    'bg-brand-soft text-brand' => request()->routeIs(...$item['active']),
                                    'text-ink hover:bg-canvas' => ! request()->routeIs(...$item['active']),
                                ])
                                wire:navigate
                            >
                                <flux:icon :name="$item['icon']" class="size-5 shrink-0" />
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="mt-4 border-t border-line-soft pt-3">
                        <a
                            href="{{ route('support.report') }}"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-ink transition hover:bg-canvas"
                        >
                            <flux:icon.chat-bubble-left-right class="size-5 shrink-0" />
                            Laporan Kendala
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-status-cancel-fg transition hover:bg-status-cancel-bg"
                            >
                                <flux:icon.arrow-right-start-on-rectangle class="size-5 shrink-0" />
                                Keluar
                            </button>
                        </form>
                    </div>
                </aside>

                {{-- Lapisan gelap saat menu samping dibuka pada layar kecil --}}
                <div
                    class="fixed inset-0 z-30 bg-black/30 lg:hidden"
                    x-show="menuOpen"
                    x-cloak
                    @click="menuOpen = false"
                ></div>

                {{-- Isi halaman --}}
                <main class="min-w-0">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @fluxScripts
        <flux:toast.group />
    </body>
</html>
