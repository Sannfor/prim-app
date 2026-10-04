<?php

namespace App\Enums;

/**
 * Status akun pengguna.
 *
 * Mengikuti kolom "Status" pada halaman Manajemen Pengguna di desain Figma:
 * Aktif, Non-aktif, dan Suspend.
 */
enum UserStatus: string
{
    case Aktif = 'aktif';
    case NonAktif = 'non_aktif';
    case Suspend = 'suspend';

    /**
     * Label bahasa Indonesia untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::NonAktif => 'Non-aktif',
            self::Suspend => 'Suspend',
        };
    }

    /**
     * Nama kelompok warna lencana pada desain.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Aktif => 'done',
            self::NonAktif => 'cancel',
            self::Suspend => 'wait',
        };
    }

    /**
     * Apakah pengguna ini boleh masuk ke aplikasi.
     */
    public function canSignIn(): bool
    {
        return $this === self::Aktif;
    }

    /**
     * Daftar pasangan nilai => label untuk komponen pilihan.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
