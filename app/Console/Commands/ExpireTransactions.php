<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\BuyerNotificationService;
use App\Services\TransactionService;
use Illuminate\Console\Command;

/**
 * Menandai transaksi pending yang melewati batas waktu pembayaran sebagai
 * kedaluwarsa, sekaligus mengakhiri masa langganan yang sudah lewat.
 *
 * Perintah ini dijadwalkan setiap jam pada routes/console.php.
 */
class ExpireTransactions extends Command
{
    protected $signature = 'prim:expire-transactions';

    protected $description = 'Tandai transaksi kedaluwarsa dan akhiri langganan yang sudah lewat masa aktifnya';

    public function handle(TransactionService $transactions, BuyerNotificationService $notifications): int
    {
        $expiredTransactions = $transactions->expireOverdueTransactions();

        $expiredSubscriptions = Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->where('ends_at', '<=', now())
            ->update([
                'status' => SubscriptionStatus::Expired->value,
                'updated_at' => now(),
            ]);

        // Notifikasi lama yang sudah dibaca dibersihkan agar daftar tetap ringkas.
        $dibersihkan = $notifications->bersihkan(hari: 30);

        $this->info("Transaksi ditandai kedaluwarsa: {$expiredTransactions}");
        $this->info("Langganan ditandai berakhir: {$expiredSubscriptions}");
        $this->info("Notifikasi lama dibersihkan: {$dibersihkan}");

        return self::SUCCESS;
    }
}
