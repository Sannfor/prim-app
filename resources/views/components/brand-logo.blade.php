@props(['variant' => 'default', 'size' => 'md'])

@php
    /*
     | Logo PRIM.
     |
     | Berkas hasil ekspor Figma: resources/images/figma/logo-prim.png (dibundel
     | lewat Vite). Ukuran mengikuti proporsi pada desain: logo navbar 211x116 px
     | pada kanvas 1728 px, dan dinaikkan pada pemakaian sebagai identitas
     | halaman. Selama berkas belum ada, ditampilkan penanda teks agar tata letak
     | tetap benar.
     */
    $logoUrl = \App\Support\PrimAsset::figma('logo-prim');

    $sizes = [
        'sm' => 'h-9',
        'md' => 'h-12',
        'lg' => 'h-16',
        'xl' => 'h-24',
    ];

    $height = $sizes[$size] ?? $sizes['md'];

    $wordmark = match ($variant) {
        'light' => 'text-white',
        default => 'text-[#3f3f46]',
    };

    $mark = match ($variant) {
        'light' => 'bg-white/90 text-brand',
        default => 'bg-brand text-white',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    @if ($logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt="{{ config('app.name') }}"
            class="{{ $height }} w-auto"
        >
    @else
        <span class="inline-flex items-center gap-2">
            <span class="flex size-10 items-center justify-center rounded-full text-lg font-bold {{ $mark }}">P</span>
            <span class="font-display text-xl font-medium tracking-[0.3em] {{ $wordmark }}">{{ config('app.name') }}</span>
        </span>
    @endif
</span>
