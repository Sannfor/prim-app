<?php

namespace App\Enums;

/**
 * Peran pengguna pada platform PRIM.
 *
 * Sesuai dokumen SRS, PRIM memiliki tiga pihak yang berinteraksi langsung
 * dengan sistem: pelanggan (user), pengelola platform (admin), dan penyedia
 * layanan premium (provider).
 */
enum Role: string
{
    case User = 'user';
    case Admin = 'admin';
    case Provider = 'provider';

    /**
     * Label bahasa Indonesia untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'Pelanggan',
            self::Admin => 'Administrator',
            self::Provider => 'Penyedia Layanan',
        };
    }

    /**
     * Apakah peran ini boleh mengakses panel pengelola.
     */
    public function canAccessAdminPanel(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Daftar nilai untuk keperluan validasi dan dropdown.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
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
