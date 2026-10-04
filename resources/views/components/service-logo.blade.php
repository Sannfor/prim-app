@props(['service' => null, 'name' => null, 'class' => 'h-16'])

@php
    /*
     | Logo merek layanan pada kartu katalog.
     |
     | Berkas logo hasil ekspor Figma diletakkan di public/images/brands/<slug>.png.
     | Selama berkas belum tersedia, komponen ini menampilkan wordmark teks agar
     | kartu tetap terbaca dan tata letaknya tidak bergeser.
     */
    $label = $name ?? $service?->name ?? '';
    $slug = $service?->slug ?? \Illuminate\Support\Str::slug($label);
    $logoUrl = \App\Support\PrimAsset::brandLogo($slug);

    $initials = \Illuminate\Support\Str::of($label)
        ->replaceMatches('/[^A-Za-z0-9 ]/', '')
        ->squish()
        ->explode(' ')
        ->take(2)
        ->map(fn ($word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
        ->implode('');
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-center ' . $class]) }}>
    @if ($logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt="{{ $label }}"
            class="max-h-full w-auto max-w-[85%] object-contain"
            loading="lazy"
        >
    @else
        <span class="flex items-center gap-2">
            <span class="flex size-10 items-center justify-center rounded-lg bg-brand-soft font-display text-sm font-bold text-brand">
                {{ $initials }}
            </span>
            <span class="font-display text-lg font-semibold leading-tight text-ink-strong">{{ $label }}</span>
        </span>
    @endif
</div>
