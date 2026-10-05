<?php

namespace App\Enums;

/**
 * Jenis notifikasi yang diterima pembeli di dalam aplikasi.
 *
 * Setiap jenis menentukan sendiri judul bawaan, ikon, dan warna lencananya,
 * sehingga penambahan jenis baru cukup dilakukan di satu tempat.
 */
enum BuyerNotificationType: string
{
    case OrderPaid = 'order_paid';
    case LoginCodeReady = 'login_code_ready';
    case OrderExpired = 'order_expired';
    case OrderCancelled = 'order_cancelled';
    case OrderRefunded = 'order_refunded';
    case SubscriptionExpiring = 'subscription_expiring';

    /**
     * Judul bawaan untuk ditampilkan pada daftar notifikasi.
     */
    public function title(): string
    {
        return match ($this) {
            self::OrderPaid => 'Pembayaran diterima',
            self::LoginCodeReady => 'Kode login siap dipakai',
            self::OrderExpired => 'Pesanan kedaluwarsa',
            self::OrderCancelled => 'Pesanan dibatalkan',
            self::OrderRefunded => 'Dana dikembalikan',
            self::SubscriptionExpiring => 'Langganan akan berakhir',
        };
    }

    /**
     * Nama ikon Flux yang mewakili jenis notifikasi ini.
     */
    public function icon(): string
    {
        return match ($this) {
            self::OrderPaid => 'check-circle',
            self::LoginCodeReady => 'key',
            self::OrderExpired => 'clock',
            self::OrderCancelled => 'x-circle',
            self::OrderRefunded => 'banknotes',
            self::SubscriptionExpiring => 'exclamation-triangle',
        };
    }

    /**
     * Warna ikon mengikuti palet status PRIM.
     */
    public function tone(): string
    {
        return match ($this) {
            self::OrderPaid, self::LoginCodeReady => 'done',
            self::OrderRefunded => 'process',
            self::OrderExpired, self::SubscriptionExpiring => 'wait',
            self::OrderCancelled => 'cancel',
        };
    }

    /**
     * Kelas latar dan warna teks untuk ikon notifikasi.
     */
    public function iconClasses(): string
    {
        return match ($this->tone()) {
            'done' => 'bg-status-done-bg text-status-done-fg',
            'process' => 'bg-status-process-bg text-status-process-fg',
            'wait' => 'bg-status-wait-bg text-status-wait-fg',
            default => 'bg-status-cancel-bg text-status-cancel-fg',
        };
    }
}
