<?php

namespace App\Services\Payment;

use App\Models\Transaction;
use App\Models\User;

/**
 * Kontrak penyedia pembayaran PRIM.
 *
 * Saat ini hanya ada satu implementasi, yaitu simulasi internal
 * (MockPaymentGateway). Bila kelak diintegrasikan dengan penyedia nyata
 * seperti Midtrans, cukup tambahkan implementasi baru tanpa mengubah
 * TransactionService maupun komponen Livewire.
 */
interface PaymentGateway
{
    /**
     * Nama gateway yang ditampilkan pada riwayat transaksi.
     */
    public function name(): string;

    /**
     * Daftar metode pembayaran yang didukung.
     *
     * @return array<string, string> pasangan kode => label
     */
    public function methods(): array;

    /**
     * Proses pembayaran sebuah transaksi.
     *
     * @param  string  $method  Kode metode pembayaran yang dipilih pengguna.
     * @param  bool  $succeed  Untuk simulasi: paksa hasil berhasil atau gagal.
     */
    public function charge(Transaction $transaction, User $payer, string $method, bool $succeed = true): PaymentResult;
}
