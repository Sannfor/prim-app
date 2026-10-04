@php
    /*
     | Tata letak panel pengelola PRIM: sidebar ungu (#534AB7) di kiri dan
     | area konten putih, sesuai desain Figma.
     |
     | Dipakai lewat atribut #[Layout('layouts::admin')]. Berkas ini sengaja
     | tidak memakai @props karena Livewire membutuhkannya sebagai view biasa.
     */
    $title = $title ?? null;
    $heading = $heading ?? null;
    $subheading = $subheading ?? null;
    $user = auth()->user();

    $menu = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'squares-2x2', 'active' => ['admin.dashboard']],
        ['route' => 'admin.transactions.index', 'label' => 'Pesanan', 'icon' => 'shopping-cart', 'active' => ['admin.transactions.*']],
        ['route' => 'admin.users.index', 'label' => 'Pengguna', 'icon' => 'users', 'active' => ['admin.users.*']],
        ['route' => 'admin.services.index', 'label' => 'Produk', 'icon' => 'cube', 'active' => ['admin.services.*', 'admin.plans.*']],
        ['route' => 'admin.payments.index', 'label' => 'Pembayaran', 'icon' => 'credit-card', 'active' => ['admin.payments.*']],
        ['route' => 'admin.reports.index', 'label' => 'Laporan', 'icon' => 'document-chart-bar', 'active' => ['admin.reports.*']],
    ];

    $pendingCount = \App\Models\Transaction::query()
        ->whereIn('status', ['pending', 'processed', 'waiting_process', 'follow_up'])
        ->count();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', [
            'title' => ($title ?? $heading ?? 'Panel Pengelola').' — '.config('app.name'),
        ])
    </head>
    <body class="min-h-screen bg-white text-ink antialiased" x-data="{ menuOpen: false }">
        <div class="flex min-h-screen">
            {{-- Sidebar ungu --}}
            <aside
                class="fixed inset-y-0 left-0 z-40 flex w-[280px] flex-col bg-brand text-white transition-transform lg:static lg:translate-x-0"
                :class="menuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            >
                <div class="flex h-[74px] items-center px-6">
                    <a href="{{ route('admin.dashboard') }}" wire:navigate>
                        <x-brand-logo variant="light" />
                    </a>

                    <button
                        type="button"
                        class="ml-auto flex size-8 items-center justify-center rounded-md text-white lg:hidden"
                        @click="menuOpen = false"
                        aria-label="Tutup menu"
                    >
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <p class="px-6 pt-1 pb-3 text-[15px] tracking-[0.18em] text-white/80 uppercase">Menu</p>

                <nav class="flex-1 overflow-y-auto">
                    @foreach ($menu as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'prim-sidebar-item',
                                'prim-sidebar-item-active font-medium' => request()->routeIs(...$item['active']),
                            ])
                            wire:navigate
                        >
                            <flux:icon :name="$item['icon']" class="size-5 shrink-0" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="pb-6">
                    <a
                        href="{{ route('admin.settings.profile') }}"
                        @class([
                            'prim-sidebar-item',
                            'prim-sidebar-item-active font-medium' => request()->routeIs('admin.settings.*'),
                        ])
                        wire:navigate
                    >
                        <flux:icon.cog-6-tooth class="size-5 shrink-0" />
                        Pengaturan
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="prim-sidebar-item w-full text-left">
                            <flux:icon.arrow-right-start-on-rectangle class="size-5 shrink-0" />
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Lapisan gelap saat menu dibuka pada layar kecil --}}
            <div
                class="fixed inset-0 z-30 bg-black/30 lg:hidden"
                x-show="menuOpen"
                x-cloak
                @click="menuOpen = false"
            ></div>

            {{-- Area konten --}}
            <div class="flex min-w-0 flex-1 flex-col">
                {{-- Header konten --}}
                <header class="border-b border-line-soft bg-white">
                    <div class="flex flex-wrap items-center gap-4 px-6 py-4 lg:px-10">
                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-md text-ink lg:hidden"
                            @click="menuOpen = true"
                            aria-label="Buka menu"
                        >
                            <flux:icon.bars-2 class="size-6" />
                        </button>

                        <p class="text-sm text-muted">
                            {{ \Illuminate\Support\Carbon::now()->locale('id')->translatedFormat('l, j F Y') }}
                        </p>

                        <div class="ml-auto flex items-center gap-4">
                            <button type="button" class="relative flex size-9 items-center justify-center rounded-full hover:bg-canvas" aria-label="Notifikasi">
                                <flux:icon.bell class="size-5 text-ink" />
                                @if ($pendingCount > 0)
                                    <span class="absolute -top-0.5 -right-0.5 flex min-w-4 items-center justify-center rounded-full bg-status-cancel-fg px-1 text-[10px] font-medium text-white">
                                        {{ $pendingCount > 99 ? '99+' : $pendingCount }}
                                    </span>
                                @endif
                            </button>

                            <div class="flex items-center gap-3">
                                <span class="flex size-9 items-center justify-center rounded-lg bg-brand text-xs font-medium text-white">
                                    {{ $user?->initials() }}
                                </span>
                                <div class="leading-tight">
                                    <p class="text-sm font-medium text-ink-strong">{{ $user?->name }}</p>
                                    <p class="text-xs text-muted">{{ $user?->role->label() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="min-w-0 flex-1 px-6 py-7 lg:px-10">
                    @if ($heading)
                        <h1 class="font-display text-2xl font-medium text-ink-strong">{{ $heading }}</h1>
                    @endif

                    @if ($subheading)
                        <p class="mt-1 text-sm text-muted">{{ $subheading }}</p>
                    @endif

                    <div class="{{ $heading ? 'mt-6' : '' }}">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @fluxScripts
        <flux:toast.group />
    </body>
</html>
