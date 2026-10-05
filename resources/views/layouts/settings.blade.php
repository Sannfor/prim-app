@php
    /*
     | Tata letak halaman pengaturan akun.
     |
     | Sama seperti tata letak akun, halaman ini memakai bilah navigasi ramping dan
     | tanpa footer besar. Bedanya, tidak ada bilah samping akun karena halaman
     | pengaturan membawa navigasi tabnya sendiri. Dengan begitu isi formulir
     | mendapat ruang lebih lega dan nyaman dibaca.
     */
    $title = $title ?? null;
    $user = auth()->user();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', [
            'title' => $title
                ? $title.' — '.config('app.name')
                : 'Pengaturan — '.config('app.name'),
        ])
    </head>
    <body class="min-h-screen bg-canvas text-ink antialiased">
        {{-- Bilah navigasi ramping --}}
        <header class="sticky top-0 z-30 border-b border-line-soft bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-[64px] w-full max-w-[1080px] items-center gap-4 px-5 lg:px-8">
                <a href="{{ route('home') }}" class="shrink-0" wire:navigate>
                    <x-brand-logo size="md" />
                </a>

                <flux:spacer />

                <a
                    href="{{ route('profile.orders') }}"
                    class="hidden items-center gap-2 text-sm font-medium text-ink transition hover:text-brand sm:flex"
                    wire:navigate
                >
                    <flux:icon.arrow-left class="size-4" />
                    Kembali ke Akun
                </a>

                <flux:dropdown position="bottom" align="end">
                    <button type="button" class="flex items-center gap-2 rounded-full py-1 pr-2 pl-1 transition hover:bg-canvas">
                        <span class="flex size-8 items-center justify-center rounded-full bg-brand text-xs font-medium text-white">
                            {{ $user?->initials() }}
                        </span>
                        <span class="hidden text-sm font-medium text-ink sm:block">{{ $user?->name }}</span>
                        <flux:icon.chevron-down class="size-4 text-muted" />
                    </button>

                    <flux:menu>
                        <flux:menu.item :href="route('profile.orders')" icon="receipt-percent" wire:navigate>
                            Pesanan Saya
                        </flux:menu.item>
                        <flux:menu.item :href="route('subscription.index')" icon="arrow-path-rounded-square" wire:navigate>
                            Langganan Saya
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

        <main class="mx-auto w-full max-w-[1080px] px-5 py-10 lg:px-8">
            <div class="mb-8">
                <h1 class="font-display text-2xl font-bold text-ink-strong">Pengaturan</h1>
                <p class="mt-1 text-sm text-muted">Kelola profil, keamanan, dan tampilan akunmu.</p>
            </div>

            {{ $slot }}
        </main>

        @fluxScripts
        <flux:toast.group />
    </body>
</html>
