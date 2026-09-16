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
    ) {}

    /**
     * Pembayaran berhasil.
     */
    public static function success(string $reference, string $message = 'Pembayaran berhasil diproses.'): self
    {
        return new self(true, TransactionStatus::Paid, $reference, $message);
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
