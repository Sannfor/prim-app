{{--
    Komponen pembungkus halaman publik.

    Menyalurkan judul dan isi ke tata letak Livewire layouts/app.blade.php
    sehingga halaman Blade biasa dapat memakai sintaks komponen.
--}}
@include('layouts.app', ['title' => $title ?? null, 'slot' => $slot])
