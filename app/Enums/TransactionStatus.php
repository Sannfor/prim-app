<?php

namespace App\Enums;

/**
 * Status transaksi pembelian layanan premium pada PRIM.
 *
 * Nilai enum mengikuti daftar status yang muncul pada desain Figma, baik pada
 * panel admin ("Manajemen Pesanan": Selesai, Diproses, Menunggu, Dibatalkan)
 * maupun pada pusat pesanan pengguna (menunggu pembayaran, ditindak lanjuti,
 * pembaharuan bukti, revisi bukti, masa tenggang).
 *
 * Pembayaran masih berupa simulasi internal sehingga perpindahan status
 * dilakukan oleh sistem, bukan callback payment gateway.
 */
enum TransactionStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Paid = 'paid';
    case Failed = 'failed';
    case Accepted = 'accepted';
    case WaitingProcess = 'waiting_process';
    case FollowUp = 'follow_up';
    case ProofRenewal = 'proof_renewal';
    case ProofRevision = 'proof_revision';
    case Grace = 'grace';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    /**
     * Label bahasa Indonesia untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Processed => 'Diproses',
            self::Paid => 'Selesai',
            self::Failed => 'Gagal',
            self::Accepted => 'Pesanan Diterima',
            self::WaitingProcess => 'Menunggu Proses',
            self::FollowUp => 'Ditindak Lanjuti',
            self::ProofRenewal => 'Pembaharuan Bukti',
            self::ProofRevision => 'Revisi Bukti',
            self::Grace => 'Masa Tenggang',
            self::Expired => 'Kedaluwarsa',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Nama kelompok warna lencana pada desain.
     *
     * Dipetakan ke kelas `.prim-badge` melalui komponen Blade x-status-badge.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Paid, self::Accepted => 'done',
            self::Processed, self::WaitingProcess => 'process',
            self::Pending, self::Grace, self::FollowUp => 'wait',
            self::Failed, self::Cancelled, self::Expired, self::ProofRevision => 'cancel',
            self::ProofRenewal => 'new',
        };
    }

    /**
     * Warna lencana Flux, dipertahankan untuk komponen yang masih memakainya.
     */
    public function badgeColor(): string
    {
        return match ($this->tone()) {
            'done' => 'green',
            'process' => 'blue',
            'wait' => 'amber',
            'cancel' => 'red',
            default => 'violet',
        };
    }

    /**
     * Apakah transaksi sudah final dan tidak dapat diubah lagi.
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::Paid,
            self::Failed,
            self::Expired,
            self::Cancelled,
        ], true);
    }

    /**
     * Apakah transaksi berhasil dan berhak membuat langganan.
     */
    public function isSuccessful(): bool
    {
        return in_array($this, [self::Paid, self::Accepted], true);
    }

    /**
     * Apakah transaksi masih menunggu tindakan pelanggan atau pengelola.
     */
    public function needsAction(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Processed,
            self::WaitingProcess,
            self::FollowUp,
            self::ProofRenewal,
            self::ProofRevision,
            self::Grace,
        ], true);
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
     * Status yang ditampilkan sebagai tab pada pusat pesanan pengguna.
     *
     * @return list<self>
     */
    public static function customerTabs(): array
    {
        return [
            self::Pending,
            self::Processed,
            self::WaitingProcess,
            self::FollowUp,
            self::ProofRenewal,
            self::ProofRevision,
            self::Grace,
        ];
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
