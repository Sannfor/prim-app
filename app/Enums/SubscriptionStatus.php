<?php

namespace App\Enums;

/**
 * Status masa aktif langganan pengguna.
 */
enum SubscriptionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    /**
     * Label bahasa Indonesia untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Expired => 'Kedaluwarsa',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Warna lencana Flux yang sesuai untuk status ini.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Expired => 'zinc',
            self::Cancelled => 'red',
        };
    }

    /**
     * Daftar nilai untuk keperluan validasi dan filter.
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
