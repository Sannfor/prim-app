@props(['method' => []])

@php
    /*
     | Kartu kanal pembayaran pada halaman "Cara Berlangganan".
     |
     | Ukuran kartu diserahkan ke grid induknya agar lima kartu per baris tidak
     | saling menempel. Logo dibatasi dua arah (tinggi dan lebar) sehingga proporsi
     | merek yang berbeda tetap rapi di dalam kartu.
     |
     | Logo diambil dari public/images/payments/<slug>.png, hasil pemotongan sprite
     | Figma (node "payments") memakai tools/slice-payment-sprite.mjs. Selama berkas
     | belum tersedia, nama kanal ditampilkan sebagai teks.
     */
    $name = $method['name'] ?? '';
    $slug = $method['slug'] ?? \Illuminate\Support\Str::slug($name);
    $relative = 'images/payments/'.$slug.'.png';
    $hasImage = file_exists(public_path($relative));
@endphp

<div {{ $attributes->merge([
    'class' => 'flex h-[62px] w-full items-center justify-center rounded-lg bg-white px-4 shadow-brand-xs',
]) }}>
    @if ($hasImage)
        <img
            src="{{ asset($relative) }}"
            alt="{{ $name }}"
            class="max-h-7 w-auto max-w-[80%] object-contain"
            loading="lazy"
        >
    @else
        <span class="font-display text-sm font-semibold whitespace-nowrap text-ink-strong">{{ $name }}</span>
    @endif
</div>
