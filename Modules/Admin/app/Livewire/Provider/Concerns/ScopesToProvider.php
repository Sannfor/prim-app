<?php

namespace Modules\Admin\Livewire\Provider\Concerns;

use App\Models\Provider;

/**
 * Batas kepemilikan untuk seluruh komponen panel penyedia.
 *
 * Panel penyedia dipakai akun berperan provider. Komponen pada panel ini tidak
 * boleh menerima id layanan atau paket dari URL begitu saja: setiap pencarian
 * record harus melewati penyedia milik akun yang sedang masuk. Trait ini
 * menyediakan satu tempat untuk aturan tersebut supaya tidak ada komponen yang
 * lupa menerapkannya.
 */
trait ScopesToProvider
{
    /**
     * Penyedia yang diwakili akun yang sedang masuk.
     *
     * Menghentikan permintaan dengan 403 bila akun bukan penyedia, atau bila
     * akun penyedia belum dihubungkan ke baris providers mana pun.
     */
    protected function penyedia(): Provider
    {
        $user = auth()->user();

        abort_unless($user !== null, 403);

        abort_unless(
            $user->isProvider(),
            403,
            'Halaman ini hanya dapat dibuka oleh akun penyedia layanan.',
        );

        $provider = $user->provider;

        abort_if(
            $provider === null,
            403,
            'Akun penyedia ini belum dihubungkan ke data penyedia. Hubungi administrator.',
        );

        return $provider;
    }
}
