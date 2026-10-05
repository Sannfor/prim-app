@props(['heading' => null, 'subheading' => null])

@php
    /*
     | Kerangka halaman pengaturan akun.
     |
     | Bagian kiri berisi navigasi tab pengaturan, bagian kanan berisi formulir.
     | Rancangan ini sama dengan halaman pengaturan pengelola agar seluruh halaman
     | pengaturan terasa satu keluarga.
     */
    $tabs = [
        ['route' => 'settings.profile', 'label' => 'Profil', 'icon' => 'user-circle'],
        ['route' => 'settings.password', 'label' => 'Kata Sandi', 'icon' => 'lock-closed'],
        ['route' => 'settings.appearance', 'label' => 'Tampilan', 'icon' => 'swatch'],
    ];
@endphp

<div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
    {{-- Navigasi tab --}}
    <nav class="lg:sticky lg:top-[88px] lg:h-fit">
        <div class="rounded-xl bg-white p-2 shadow-brand-xs">
            @foreach ($tabs as $tab)
                <a
                    href="{{ route($tab['route']) }}"
                    @class([
                        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                        'bg-brand-soft text-brand' => request()->routeIs($tab['route']),
                        'text-ink hover:bg-canvas' => ! request()->routeIs($tab['route']),
                    ])
                    wire:navigate
                >
                    <flux:icon :name="$tab['icon']" class="size-5 shrink-0" />
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        <div class="mt-3 rounded-xl bg-canvas p-4">
            <p class="flex items-start gap-2.5 text-xs leading-relaxed text-muted">
                <flux:icon.information-circle class="mt-0.5 size-4 shrink-0 text-brand" />
                Perubahan langsung berlaku untuk akun yang sedang kamu pakai.
            </p>
        </div>
    </nav>

    {{-- Isi --}}
    <div class="min-w-0">
        @if ($heading)
            <h2 class="font-display text-xl font-bold text-ink-strong">{{ $heading }}</h2>
        @endif

        @if ($subheading)
            <p class="mt-1 text-sm text-muted">{{ $subheading }}</p>
        @endif

        <div class="{{ $heading ? 'mt-5' : '' }} space-y-6">
            {{ $slot }}
        </div>
    </div>
</div>
