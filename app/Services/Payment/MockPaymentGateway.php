<?php

namespace App\Services\Payment;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Simulasi gateway pembayaran internal.
 *
 * Dipakai karena PRIM belum terhubung ke penyedia pembayaran nyata: pemanggil
 * menentukan sendiri apakah pembayaran disimulasikan berhasil atau gagal,
 * sehingga seluruh alur transaksi dapat didemonstrasikan tanpa akun pihak ketiga.
 */
class MockPaymentGateway implements PaymentGateway
{
    public const METHOD_TRANSFER = 'transfer';

    public const METHOD_QRIS = 'qris';

    public const METHOD_EWALLET = 'ewallet';

    public function name(): string
    {
        return 'Simulasi PRIM Pay';
    }

    /**
     * @return array<string, string>
     */
    public function methods(): array
    {
        return [
            self::METHOD_TRANSFER => 'Transfer Bank Virtual Account',
            self::METHOD_QRIS => 'QRIS',
            self::METHOD_EWALLET => 'Dompet Digital (E-Wallet)',
        ];
    }

    /**
     * Simulasi menyelesaikan pembayaran langsung, tanpa pengalihan halaman.
     */
    public function requiresRedirect(): bool
    {
        return false;
    }

    public function charge(Transaction $transaction, User $payer, string $method, bool $succeed = true): PaymentResult
    {
        $reference = $this->reference($transaction, $method);

        if (! $succeed) {
            return PaymentResult::failure(
                $reference,
                'Simulasi: pembayaran ditolak oleh penyedia untuk metode '.$this->label($method).'.'
            );
        }

        return PaymentResult::success(
            $reference,
            'Simulasi: pembayaran sebesar '.$transaction->formattedAmount().' berhasil melalui '.$this->label($method).'.'
        );
    }

    /**
     * Label metode pembayaran yang mudah dibaca.
     */
    public function label(string $method): string
    {
        return $this->methods()[$method] ?? $method;
    }

    /**
     * Nomor referensi simulasi, berformat MOCK-YYYYMMDD-XXXXXX.
     */
    private function reference(Transaction $transaction, string $method): string
    {
        return 'MOCK-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}
