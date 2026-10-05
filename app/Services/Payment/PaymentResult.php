<?php

namespace App\Services\Payment;

use App\Enums\TransactionStatus;

/**
 * Hasil satu percobaan pembayaran.
 *
 * DTO ini sengaja dibuat sederhana dan tidak bergantung pada implementasi
 * gateway tertentu, sehingga penggantian penyedia pembayaran tidak mengubah
 * kode pemanggilnya.
 */
class PaymentResult
{
    public function __construct(
        public readonly bool $successful,
        public readonly TransactionStatus $status,
        public readonly string $reference,
        public readonly string $message,
        public readonly bool $requiresAction = false,
        public readonly ?string $token = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $methodLabel = null,
    ) {}

    /**
     * Pembayaran berhasil dan langsung lunas.
     */
    public static function success(string $reference, string $message = 'Pembayaran berhasil diproses.'): self
    {
        return new self(true, TransactionStatus::Paid, $reference, $message);
    }

    /**
     * Pembayaran menunggu tindakan pengguna pada halaman penyedia.
     *
     * Dipakai oleh gateway berbasis pengalihan halaman, misalnya Midtrans Snap:
     * transaksi belum lunas sampai penyedia mengirim notifikasi pembayaran.
     */
    public static function menungguTindakan(
        string $reference,
        string $token,
        string $redirectUrl,
        ?string $methodLabel = null,
    ): self {
        return new self(
            successful: false,
            status: TransactionStatus::Pending,
            reference: $reference,
            message: 'Menunggu pembayaran diselesaikan pada halaman penyedia.',
            requiresAction: true,
            token: $token,
            redirectUrl: $redirectUrl,
            methodLabel: $methodLabel,
        );
    }

    /**
     * Pembayaran ditolak oleh penyedia.
     */
    public static function failure(string $reference, string $message = 'Pembayaran ditolak oleh penyedia.'): self
    {
        return new self(false, TransactionStatus::Failed, $reference, $message);
    }

    /**
     * Pembayaran dibatalkan sebelum diproses.
     */
    public static function cancelled(string $reference, string $message = 'Pembayaran dibatalkan.'): self
    {
        return new self(false, TransactionStatus::Cancelled, $reference, $message);
    }
}
