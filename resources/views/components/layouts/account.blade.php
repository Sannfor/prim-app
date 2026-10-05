{{--
    Komponen pembungkus halaman akun.
    Menyalurkan judul dan isi ke tata letak Livewire layouts/account.blade.php.
--}}
@include('layouts.account', ['title' => $title ?? null, 'slot' => $slot])
