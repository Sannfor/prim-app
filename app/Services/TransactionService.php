<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payment\PaymentGateway;
use App\Services\Payment\PaymentResult;
use App\Services\VoucherService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Logika bisnis transaksi dan langganan.
 *
 * Dipisahkan dari komponen Livewire agar dapat diuji langsung dan dipakai
 * ulang oleh perintah artisan (mis. penandaan transaksi kedaluwarsa).
 */
class TransactionService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly VoucherService $vouchers,
    ) {}

    /**
     * Buat transaksi baru berstatus menunggu pembayaran.
     *
     * Bila kode voucher diberikan dan memenuhi syarat, potongannya langsung
     * disimpan pada transaksi dan pemakaiannya dicatat agar kuota berkurang.
     * Kode yang tidak valid diabaikan, bukan menggagalkan pemesanan.
     *
     * @param  string|null  $paymentMethod  Metode bayar yang dipilih, bila sudah ditentukan.
     * @param  string|null  $voucherCode  Kode voucher yang ingin dipakai pembeli.
     */
    public function checkout(User $user, Plan $plan, ?string $paymentMethod = null, ?string $voucherCode = null): Transaction
    {
        $subtotal = (int) $plan->price;
        $potongan = 0;
        $voucher = null;

        if (filled($voucherCode)) {
            $hasil = $this->vouchers->periksa($voucherCode, $user, $plan);

            if ($hasil['valid']) {
                $voucher = $hasil['voucher'];
                $potongan = $hasil['discount'];
            }
        }

        $transaksi = Transaction::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'voucher_id' => $voucher?->id,
            'subtotal_amount' => $subtotal,
            'discount_amount' => $potongan,
            'amount' => $subtotal - $potongan,
            'status' => TransactionStatus::Pending,
            'payment_method' => $paymentMethod,
            'expires_at' => now()->addHours(Transaction::PAYMENT_WINDOW_HOURS),
        ]);

        if ($voucher !== null && $potongan > 0) {
            $this->vouchers->catatPemakaian($voucher, $user, $transaksi->id, $potongan);
        }

        return $transaksi;
    }

    /**
     * Batalkan pesanan yang belum dibayar dan lepas pemakaian vouchernya.
     *
     * Hanya transaksi yang masih menunggu pembayaran yang dapat dibatalkan,
     * sehingga pesanan yang sudah lunas tidak hilang begitu saja.
     */
    public function batalkan(Transaction $transaction): bool
    {
        if (! $transaction->isPayable()) {
            return false;
        }

        $transaction->forceFill([
            'status' => TransactionStatus::Cancelled,
            'notes' => 'Pesanan dibatalkan oleh pembeli sebelum dibayar.',
            'payment_token' => null,
            'payment_url' => null,
        ])->save();

        $this->vouchers->lepasPemakaian($transaction->id);

        return true;
    }

    /**
     * Proses pembayaran sebuah transaksi melalui gateway.
     *
     * Tiga kemungkinan hasil:
     *   1. Berhasil langsung  — transaksi ditandai lunas dan langganan dibuat.
     *   2. Menunggu tindakan  — token dan tautan pembayaran disimpan, transaksi
     *                           tetap menunggu sampai penyedia mengirim notifikasi.
     *   3. Gagal              — status transaksi diperbarui menjadi gagal.
     */
    public function pay(Transaction $transaction, User $payer, string $method, bool $succeed = true): PaymentResult
    {
        if (! $transaction->isPayable()) {
            return PaymentResult::failure(
                $transaction->order_code,
                'Transaksi ini sudah tidak dapat dibayar lagi.'
            );
        }

        $result = $this->gateway->charge($transaction, $payer, $method, $succeed);

        DB::transaction(function () use ($transaction, $method, $result) {
            $label = $result->methodLabel ?? $this->gateway->methods()[$method] ?? $method;

            $atribut = [
                'payment_method' => $method,
                'payment_method_label' => $label,
                'notes' => $result->message,
                'payment_reference' => $result->reference,
            ];

            if ($result->requiresAction) {
                // Belum lunas: simpan tautan pembayaran, status tetap menunggu.
                $atribut['payment_token'] = $result->token;
                $atribut['payment_url'] = $result->redirectUrl;
            } else {
                $atribut['status'] = $result->status;
                $atribut['paid_at'] = $result->successful ? now() : null;
                $atribut['payment_token'] = null;
                $atribut['payment_url'] = null;
            }

            $transaction->forceFill($atribut)->save();

            if ($result->successful) {
                $this->activateSubscription($transaction->fresh());
            }
        });

        return $result;
    }

    /**
     * Tandai transaksi lunas setelah pembayaran terkonfirmasi di luar aplikasi,
     * misalnya dari notifikasi penyedia pembayaran.
     *
     * Berbeda dari pay(), metode ini tidak memanggil gateway karena pembayaran
     * sudah terjadi di sisi penyedia.
     */
    public function markAsPaid(Transaction $transaction, ?string $reference = null): Subscription
    {
        $subscription = DB::transaction(function () use ($transaction, $reference) {
            $transaction->forceFill([
                'status' => TransactionStatus::Paid,
                'paid_at' => now(),
                'payment_reference' => $reference ?? $transaction->payment_reference,
                'notes' => 'Pembayaran terkonfirmasi oleh penyedia pembayaran.',
                'payment_token' => null,
                'payment_url' => null,
            ])->save();

            return $this->activateSubscription($transaction->fresh());
        });

        return $subscription;
    }

    /**
     * Tandai transaksi pending yang sudah lewat batas waktu sebagai kedaluwarsa.
     *
     * @return int jumlah transaksi yang diperbarui
     */
    public function expireOverdueTransactions(): int
    {
        return Transaction::query()
            ->where('status', TransactionStatus::Pending->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => TransactionStatus::Expired->value,
                'notes' => 'Pesanan kedaluwarsa karena melewati batas waktu pembayaran.',
                'updated_at' => now(),
            ]);
    }

    /**
     * Buat langganan aktif dari transaksi yang sudah lunas.
     *
     * Bila pengguna masih memiliki langganan aktif atas layanan yang sama,
     * masa aktifnya disambung dari tanggal berakhir sebelumnya.
     */
    public function activateSubscription(Transaction $transaction): Subscription
    {
        $existing = $transaction->subscription;

        if ($existing !== null) {
            return $existing;
        }

        $plan = $transaction->plan;
        $startedAt = now();

        /*
         * Bila pengguna masih punya langganan aktif atas layanan yang sama,
         * masa aktif baru disambung dari tanggal berakhir yang paling akhir.
         * Pencocokan dilakukan pada tingkat layanan, bukan paket, karena
         * perpanjangan boleh memakai paket yang berbeda.
         */
        $previousEndsAt = Subscription::query()
            ->where('user_id', $transaction->user_id)
            ->whereIn('status', [SubscriptionStatus::Active->value])
            ->where('ends_at', '>', now())
            ->whereHas('plan', fn ($q) => $q->where('service_id', $plan->service_id))
            ->max('ends_at');

        if ($previousEndsAt !== null) {
            $startedAt = Carbon::parse($previousEndsAt);
        }

        return Subscription::create([
            'user_id' => $transaction->user_id,
            'plan_id' => $plan->id,
            'transaction_id' => $transaction->id,
            'status' => SubscriptionStatus::Active,
            'started_at' => $startedAt,
            'ends_at' => $startedAt->copy()->addDays($plan->duration_days),
            'auto_renew' => false,
        ]);
    }

    /**
     * Gateway pembayaran yang sedang dipakai.
     */
    public function gateway(): PaymentGateway
    {
        return $this->gateway;
    }
}
