<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun contoh untuk peran penyedia layanan.
 *
 * Panel penyedia menentukan hak sunting dari tautan providers.user_id, jadi
 * akun ini dihubungkan ke satu baris providers tertentu, yaitu Netflix. Akun
 * tersebut hanya dapat melihat dan menyunting layanan milik Netflix, bukan
 * layanan penyedia lain.
 *
 * Seluruh akun contoh pada proyek ini memakai kata sandi "password".
 */
class ProviderUserSeeder extends Seeder
{
    public function run(): void
    {
        $penyedia = Provider::query()
            ->whereHas('services')
            ->orderBy('id')
            ->first();

        if ($penyedia === null) {
            // Katalog belum terisi; tidak ada penyedia yang bisa diwakili.
            return;
        }

        $akun = User::updateOrCreate(
            ['email' => 'provider@prim.test'],
            [
                'name' => 'Pengelola '.$penyedia->name,
                'password' => Hash::make('password'),
                'role' => Role::Provider,
                'phone' => '0857-1100-2200',
                'email_verified_at' => now(),
            ],
        );

        /*
         * Tautan diatur lewat query builder, bukan lewat model yang sudah dimuat.
         *
         * Model $penyedia memegang nilai user_id lama di memori. Bila tautan
         * lama dibersihkan lebih dahulu, lalu model yang sama diisi ulang dengan
         * nilai yang sama seperti di memori, Eloquent menganggap kolomnya tidak
         * berubah sehingga save() tidak menulis apa pun dan baris tetap NULL.
         * Update lewat query selalu dijalankan, sehingga hasilnya idempoten.
         */
        Provider::query()->where('user_id', $akun->id)->update(['user_id' => null]);
        Provider::query()->whereKey($penyedia->id)->update(['user_id' => $akun->id]);

        $this->command?->info(sprintf(
            'Akun penyedia: %s (mewakili %s, %d layanan)',
            $akun->email,
            $penyedia->name,
            $penyedia->services()->count(),
        ));
    }
}
