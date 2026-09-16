@props(['title' => null])

@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title ? $title.' — '.config('app.name') : config('app.name').' — Aggregator Layanan Premium'])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-900 dark:text-zinc-100">
        <flux:header container class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a href="{{ route('home') }}" class="mr-5 flex items-center gap-2" wire:navigate>
                <span class="flex size-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                    P
                </span>
                <span class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
            </a>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="squares-2x2" :href="route('catalog.index')" :current="request()->routeIs('catalog.index')" wire:navigate>
                    Katalog
                </flux:navbar.item>
                <flux:navbar.item icon="arrows-right-left" :href="route('catalog.compare')" :current="request()->routeIs('catalog.compare')" wire:navigate>
                    Bandingkan
                </flux:navbar.item>

                @auth
                    <flux:navbar.item icon="receipt-percent" :href="route('transaction.index')" :current="request()->routeIs('transaction.*')" wire:navigate>
                        Transaksi
                    </flux:navbar.item>
                    <flux:navbar.item icon="arrow-path-rounded-square" :href="route('subscription.index')" :current="request()->routeIs('subscription.*')" wire:navigate>
                        Langganan
                    </flux:navbar.item>
                @endauth
            </flux:navbar>

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile class="cursor-pointer" :initials="$user->initials()" />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ $user->initials() }}
                                </span>
                                <div class="grid flex-1 leading-tight">
                                    <span class="truncate font-semibold">{{ $user->name }}</span>
                                    <span class="truncate text-xs text-zinc-500">{{ $user->role->label() }}</span>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('subscription.index')" icon="arrow-path-rounded-square" wire:navigate>
                                Langganan Saya
                            </flux:menu.item>
                            <flux:menu.item :href="route('transaction.index')" icon="receipt-percent" wire:navigate>
                                Riwayat Transaksi
                            </flux:menu.item>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                                Pengaturan
                            </flux:menu.item>
                        </flux:menu.radio.group>

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
                <div class="flex items-center gap-2">
                    <flux:button :href="route('login')" variant="ghost" size="sm" wire:navigate>
                        Masuk
                    </flux:button>
                    <flux:button :href="route('register')" variant="primary" size="sm" wire:navigate>
                        Daftar
                    </flux:button>
                </div>
            @endauth
        </flux:header>

        {{-- Bar navigasi khusus panel pengelola --}}
        @if ($isAdmin)
            <div class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <flux:navbar class="overflow-x-auto py-1">
                        <flux:navbar.item icon="chart-bar-square" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            Ringkasan
                        </flux:navbar.item>
                        <flux:navbar.item icon="squares-2x2" :href="route('admin.services.index')" :current="request()->routeIs('admin.services.*', 'admin.plans.*')" wire:navigate>
                            Layanan
                        </flux:navbar.item>
                        <flux:navbar.item icon="tag" :href="route('admin.categories.index')" :current="request()->routeIs('admin.categories.*')" wire:navigate>
                            Kategori
                        </flux:navbar.item>
                        <flux:navbar.item icon="building-office" :href="route('admin.providers.index')" :current="request()->routeIs('admin.providers.*')" wire:navigate>
                            Penyedia
                        </flux:navbar.item>
                        <flux:navbar.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                            Pengguna
                        </flux:navbar.item>
                        <flux:navbar.item icon="receipt-percent" :href="route('admin.transactions.index')" :current="request()->routeIs('admin.transactions.*')" wire:navigate>
                            Transaksi
                        </flux:navbar.item>
                    </flux:navbar>
                </div>
            </div>
        @endif

        {{-- Menu navigasi untuk layar kecil --}}        <flux:sidebar stashable sticky class="border-r border-zinc-200 bg-white lg:hidden dark:border-zinc-700 dark:bg-zinc-800">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('home') }}" class="ml-1 flex items-center gap-2" wire:navigate>
                <span class="flex size-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                    P
                </span>
                <span class="font-semibold">{{ config('app.name') }}</span>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Jelajahi">
                    <flux:navlist.item icon="squares-2x2" :href="route('catalog.index')" :current="request()->routeIs('catalog.index')" wire:navigate>
                        Katalog
                    </flux:navlist.item>
                    <flux:navlist.item icon="arrows-right-left" :href="route('catalog.compare')" :current="request()->routeIs('catalog.compare')" wire:navigate>
                        Bandingkan
                    </flux:navlist.item>
                </flux:navlist.group>

                @auth
                    <flux:navlist.group heading="Akun Saya">
                        <flux:navlist.item icon="receipt-percent" :href="route('transaction.index')" :current="request()->routeIs('transaction.*')" wire:navigate>
                            Transaksi
                        </flux:navlist.item>
                        <flux:navlist.item icon="arrow-path-rounded-square" :href="route('subscription.index')" :current="request()->routeIs('subscription.*')" wire:navigate>
                            Langganan
                        </flux:navlist.item>
                    </flux:navlist.group>
                @endauth

                @if ($isAdmin)
                    <flux:navlist.group heading="Pengelola" class="grid">
                        <flux:navlist.item icon="chart-bar-square" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            Ringkasan
                        </flux:navlist.item>
                        <flux:navlist.item icon="squares-2x2" :href="route('admin.services.index')" :current="request()->routeIs('admin.services.*', 'admin.plans.*')" wire:navigate>
                            Layanan
                        </flux:navlist.item>
                        <flux:navlist.item icon="tag" :href="route('admin.categories.index')" :current="request()->routeIs('admin.categories.*')" wire:navigate>
                            Kategori
                        </flux:navlist.item>
                        <flux:navlist.item icon="building-office" :href="route('admin.providers.index')" :current="request()->routeIs('admin.providers.*')" wire:navigate>
                            Penyedia
                        </flux:navlist.item>
                        <flux:navlist.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                            Pengguna
                        </flux:navlist.item>
                        <flux:navlist.item icon="receipt-percent" :href="route('admin.transactions.index')" :current="request()->routeIs('admin.transactions.*')" wire:navigate>
                            Transaksi
                        </flux:navlist.item>
                    </flux:navlist.group>
                @endif
            </flux:navlist>
        </flux:sidebar>

        <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        <footer class="mt-16 border-t border-zinc-200 py-6 text-center text-sm text-zinc-500 dark:border-zinc-700">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} — Platform Digital Aggregator Layanan Premium.</p>
            <p class="mt-1 text-xs">Ilmu Komputer, FMIPA, Universitas Lambung Mangkurat.</p>
        </footer>

        @fluxScripts
        <flux:toast.group />
    </body>
</html>
