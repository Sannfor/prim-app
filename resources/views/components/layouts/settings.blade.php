{{--
    Komponen pembungkus halaman pengaturan.
    Menyalurkan judul dan isi ke tata letak Livewire layouts/settings.blade.php.
--}}
@include('layouts.settings', ['title' => $title ?? null, 'slot' => $slot])
