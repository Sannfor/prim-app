<?php

namespace App\Enums;

/**
 * Status transaksi pembelian layanan premium pada PRIM.
 *
 * Pembayaran pada sistem ini masih berupa simulasi internal sehingga
 * perpindahan status dilakukan oleh sistem (bukan callback payment gateway).
 */
enum TransactionStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    /**
     * Label bahasa Indonesia untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Berhasil',
            self::Failed => 'Gagal',
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
            self::Pending => 'amber',
            self::Paid => 'green',
            self::Failed => 'red',
            self::Expired => 'zinc',
            self::Cancelled => 'zinc',
        };
    }

    /**
     * Apakah transaksi sudah final dan tidak dapat diubah lagi.
     */
    public function isFinal(): bool
    {
        return $this !== self::Pending;
    }

    /**
     * Apakah transaksi berhasil dan berhak membuat langganan.
     */
    public function isSuccessful(): bool
    {
        return $this === self::Paid;
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
