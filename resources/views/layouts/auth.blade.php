<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', [
            'title' => ($title ?? null)
                ? $title.' — '.config('app.name')
                : 'Masuk — '.config('app.name'),
        ])
    </head>
    <body class="prim-auth-bg font-auth min-h-screen antialiased">
        {{-- Dekorasi awan samar sesuai desain (opacity 0,07) --}}
        <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -top-24 -left-24 size-[700px] rounded-full bg-white/20 blur-3xl"></div>
            <div class="absolute top-1/3 -right-32 size-[560px] rounded-full bg-white/25 blur-3xl"></div>
        </div>

        {{--
            Tata letak halaman autentikasi sesuai Figma.

            Figma memakai tiga ukuran huruf besar (H1 50-55 px, label 25 px) pada
            kanvas 1728 px. Agar tetap nyaman dibaca pada layar nyata, ukuran
            diturunkan secara proporsional: H1 40 px, label 15 px, isi kolom 16 px.
        --}}
        <div class="relative flex min-h-screen items-center justify-center px-5 py-10">
            <div class="w-full {{ $cardClass ?? 'max-w-[520px]' }} rounded-[30px] bg-white px-7 py-10 shadow-brand-lg sm:px-12 sm:py-12">
                <div class="flex justify-center">
                    <a href="{{ route('home') }}" wire:navigate>
                        <x-brand-logo size="xl" />
                    </a>
                </div>

                <div class="mt-9">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @fluxScripts
        <flux:toast.group />
    </body>
</html>
