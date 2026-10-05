<?php

namespace App\Services\Payment;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Gateway pembayaran Midtrans.
 *
 * Kelas ini dipakai bila MIDTRANS_SERVER_KEY diisi pada berkas .env. Bila kunci
 * belum diisi, kelas ini otomatis bekerja dalam mode simulasi sehingga seluruh
 * alur pembayaran tetap dapat didemonstrasikan tanpa akun Midtrans.
 *
 * Mode diatur oleh MIDTRANS_MODE:
 *   - "simulation" (bawaan): pembayaran diselesaikan langsung di dalam aplikasi.
 *   - "snap"              : pengguna diarahkan ke halaman pembayaran Midtrans.
 *
 * Pemanggil (TransactionService dan komponen Livewire) tidak perlu berubah saat
 * mode ditukar, karena keduanya hanya bergantung pada kontrak PaymentGateway.
 */
class MidtransGateway implements PaymentGateway
{
    public function __construct(
        private readonly ?string $serverKey = null,
        private readonly string $mode = 'simulation',
        private readonly bool $production = false,
    ) {}

    public function name(): string
    {
        return $this->menggunakanSnap() ? 'Midtrans' : 'Simulasi PRIM Pay';
    }

    /**
     * Apakah gateway benar-benar memanggil Midtrans.
     */
    public function menggunakanSnap(): bool
    {
        return $this->mode === 'snap' && filled($this->serverKey);
    }

    /**
     * Midtrans Snap mengalihkan pengguna ke halaman pembayaran penyedia.
     */
    public function requiresRedirect(): bool
    {
        return $this->menggunakanSnap();
    }

    /**
     * Kanal pembayaran yang ditawarkan.
     *
     * @return array<string, string>
     */
    public function methods(): array
    {
        return [
            'qris' => 'QRIS (semua aplikasi)',
            'ewallet_dana' => 'E-Wallet (Dana)',
            'ewallet_gopay' => 'E-Wallet (GoPay)',
            'ewallet_ovo' => 'E-Wallet (OVO)',
            'ewallet_shopeepay' => 'E-Wallet (ShopeePay)',
            'bank_transfer' => 'Bank Transfer / Virtual Account',
            'retail' => 'Gerai Retail (Alfamart, Indomaret)',
        ];
    }

    public function charge(Transaction $transaction, User $payer, string $method, bool $succeed = true): PaymentResult
    {
        if (! $this->menggunakanSnap()) {
            return $this->simulasi($transaction, $method, $succeed);
        }

        return $this->mintaSnap($transaction, $payer, $method);
    }

    /**
     * Mode simulasi: hasil ditentukan langsung tanpa memanggil penyedia.
     */
    private function simulasi(Transaction $transaction, string $method, bool $succeed): PaymentResult
    {
        $reference = 'SIM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));

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
     * Mode Snap: minta token transaksi ke Midtrans, lalu kembalikan tautan
     * pembayaran yang harus dibuka pengguna.
     */
    private function mintaSnap(Transaction $transaction, User $payer, string $method): PaymentResult
    {
        $reference = 'MT-'.$transaction->order_code;

        $payload = [
            'transaction_details' => [
                'order_id' => $reference,
                'gross_amount' => (int) $transaction->amount,
            ],
            'item_details' => [[
                'id' => (string) $transaction->plan_id,
                'price' => (int) $transaction->amount,
                'quantity' => 1,
                'name' => Str::limit($transaction->plan->service->name.' — '.$transaction->plan->groupLabel(), 50, ''),
            ]],
            'customer_details' => [
                'first_name' => Str::limit($payer->name, 20, ''),
                'email' => $payer->email,
                'phone' => $payer->phone,
            ],
            'callbacks' => [
                'finish' => route('transaction.show', $transaction->order_code),
            ],
        ];

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->acceptJson()
                ->timeout(20)
                ->post($this->endpoint(), $payload);

            if (! $response->successful()) {
                return PaymentResult::failure(
                    $reference,
                    'Midtrans menolak permintaan pembayaran: '.$response->json('error_messages.0', 'tidak diketahui')
                );
            }

            $token = (string) $response->json('token');
            $redirect = (string) $response->json('redirect_url');

            return PaymentResult::menungguTindakan($reference, $token, $redirect, $method);
        } catch (Throwable $e) {
            return PaymentResult::failure(
                $reference,
                'Tidak dapat menghubungi Midtrans: '.$e->getMessage()
            );
        }
    }

    /**
     * Alamat API Midtrans sesuai mode sandbox atau produksi.
     */
    private function endpoint(): string
    {
        return $this->production
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Nama kanal pembayaran yang mudah dibaca.
     */
    public function label(string $method): string
    {
        return $this->methods()[$method] ?? $method;
    }
}
