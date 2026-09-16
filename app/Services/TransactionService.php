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
    public function __construct(private readonly PaymentGateway $gateway) {}

    /**
     * Buat transaksi baru berstatus menunggu pembayaran.
     *
     * @param  string|null  $paymentMethod  Metode bayar yang dipilih, bila sudah ditentukan.
     */
    public function checkout(User $user, Plan $plan, ?string $paymentMethod = null): Transaction
    {
        return Transaction::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => TransactionStatus::Pending,
            'payment_method' => $paymentMethod,
            'expires_at' => now()->addHours(Transaction::PAYMENT_WINDOW_HOURS),
        ]);
    }

    /**
     * Proses pembayaran sebuah transaksi melalui gateway.
     *
     * Bila berhasil, transaksi ditandai lunas dan langganan dibuat otomatis.
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
            $transaction->forceFill([
                'payment_method' => $method,
                'status' => $result->status,
                'paid_at' => $result->successful ? now() : null,
                'notes' => $result->message,
            ])->save();

            if ($result->successful) {
                $this->activateSubscription($transaction->fresh());
            }
        });

        return $result;
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
