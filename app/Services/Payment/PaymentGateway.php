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
     * Nama metode pembayaran yang mudah dibaca.
     *
     * @param  string  $method  Kode metode pembayaran.
     */
    public function label(string $method): string;

    /**
     * Apakah gateway memerlukan tindakan pengguna pada halaman penyedia.
     *
     * Bernilai benar untuk gateway berbasis pengalihan halaman seperti Midtrans
     * Snap, dan salah untuk gateway simulasi yang menyelesaikan pembayaran
     * langsung di dalam aplikasi.
     */
    public function requiresRedirect(): bool;

    /**
     * Proses pembayaran sebuah transaksi.
     *
     * @param  string  $method  Kode metode pembayaran yang dipilih pengguna.
     * @param  bool  $succeed  Untuk simulasi: paksa hasil berhasil atau gagal.
     */
    public function charge(Transaction $transaction, User $payer, string $method, bool $succeed = true): PaymentResult;
}
