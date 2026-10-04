@props(['heading' => null, 'subheading' => null])

@php
    $tabs = [
        ['route' => 'settings.profile', 'label' => 'Profil', 'icon' => 'user-circle'],
        ['route' => 'settings.password', 'label' => 'Password', 'icon' => 'lock-closed'],
        ['route' => 'settings.appearance', 'label' => 'Tampilan', 'icon' => 'swatch'],
    ];
@endphp

<div class="flex items-start gap-8 max-md:flex-col">
    <nav class="w-full shrink-0 md:w-[240px]">
        <div class="prim-card overflow-hidden p-2">
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
                    <flux:icon :name="$tab['icon']" class="size-5" />
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <div class="min-w-0 flex-1">
        @if ($heading)
            <h2 class="font-display text-xl font-semibold text-ink-strong">{{ $heading }}</h2>
        @endif

        @if ($subheading)
            <p class="mt-1 text-sm text-muted">{{ $subheading }}</p>
        @endif

        <div class="mt-6 w-full max-w-2xl">
            {{ $slot }}
        </div>
    </div>
</div>
