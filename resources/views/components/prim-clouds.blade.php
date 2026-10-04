@props(['height' => 'h-[150px]', 'class' => ''])

{{--
    Transisi awan di kaki hero Beranda.

    Pada desain Figma bagian ini berupa berkas gambar ("Awan"). Bila berkas
    hasil ekspor tersedia dipakai gambar itu; jika belum, dipakai siluet awan
    berbasis CSS dari kelas .prim-clouds agar halaman tetap tampil utuh.
--}}

@php
    $cloudUrl = \App\Support\PrimAsset::figma('awan');
@endphp

@if ($cloudUrl)
    <div {{ $attributes->merge(['class' => 'pointer-events-none w-full ' . $class]) }}>
        <img
            src="{{ $cloudUrl }}"
            alt=""
            aria-hidden="true"
            class="w-full {{ $height }} object-cover object-top"
        >
    </div>
@else
    <div {{ $attributes->merge(['class' => 'prim-clouds pointer-events-none w-full ' . $class]) }} aria-hidden="true"></div>
@endif
