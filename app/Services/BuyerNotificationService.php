<?php

namespace App\Services;

use App\Enums\BuyerNotificationType;
use App\Models\BuyerNotification;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Number;

/**
 * Penerbit notifikasi dalam aplikasi untuk pembeli.
 *
 * Seluruh notifikasi dibuat lewat layanan ini agar isi pesan konsisten dan
 * pembuatan notifikasi tidak tersebar di banyak komponen.
 */
class BuyerNotificationService
{
    /**
     * Pembayaran sebuah pesanan diterima.
     */
    public function pesananLunas(Transaction $transaction): BuyerNotification
    {
        return $this->kirim(
            $transaction->user,
            BuyerNotificationType::OrderPaid,
            body: 'Pembayaran '.$transaction->formattedAmount().' untuk '
                .$transaction->plan->service->name.' telah kami terima. Langgananmu sudah aktif.',
            url: route('transaction.show', $transaction->order_code),
            transaction: $transaction,
        );
    }

    /**
     * Kredensial akun layanan sudah dibagikan untuk sebuah pesanan.
     */
    public function kodeLoginSiap(Transaction $transaction, int $jumlah): BuyerNotification
    {
        return $this->kirim(
            $transaction->user,
            BuyerNotificationType::LoginCodeReady,
            body: $jumlah.' kode login untuk '.$transaction->plan->service->name
                .' sudah tersedia. Buka halaman Kode Login untuk melihatnya.',
            url: route('profile.login-code'),
            transaction: $transaction,
        );
    }

    /**
     * Pesanan melewati batas waktu pembayaran.
     */
    public function pesananKedaluwarsa(Transaction $transaction): BuyerNotification
    {
        return $this->kirim(
            $transaction->user,
            BuyerNotificationType::OrderExpired,
            body: 'Batas waktu pembayaran untuk '.$transaction->order_code.' sudah lewat. '
                .'Silakan buat pesanan baru bila masih berminat.',
            url: route('transaction.checkout', $transaction->plan_id),
            transaction: $transaction,
        );
    }

    /**
     * Pesanan dibatalkan oleh pembeli.
     */
    public function pesananDibatalkan(Transaction $transaction): BuyerNotification
    {
        return $this->kirim(
            $transaction->user,
            BuyerNotificationType::OrderCancelled,
            body: 'Pesanan '.$transaction->order_code.' telah dibatalkan. '
                .'Voucher yang dipakai sudah dikembalikan ke kuotanya.',
            url: route('profile.orders'),
            transaction: $transaction,
        );
    }

    /**
     * Dana pembeli dikembalikan oleh pengelola.
     */
    public function danaDikembalikan(Transaction $transaction, int $nominal, ?string $alasan): BuyerNotification
    {
        $pesan = 'Dana sebesar Rp'.Number::format($nominal, locale: 'id')
            .' untuk pesanan '.$transaction->order_code.' sedang dikembalikan.';

        if (filled($alasan)) {
            $pesan .= ' Alasan: '.$alasan;
        }

        return $this->kirim(
            $transaction->user,
            BuyerNotificationType::OrderRefunded,
            body: $pesan,
            url: route('transaction.show', $transaction->order_code),
            transaction: $transaction,
        );
    }

    /**
     * Langganan pembeli akan segera berakhir.
     */
    public function langgananAkanBerakhir(User $user, Transaction $transaction, int $hariTersisa): BuyerNotification
    {
        return $this->kirim(
            $user,
            BuyerNotificationType::SubscriptionExpiring,
            body: 'Langganan '.$transaction->plan->service->name.' akan berakhir dalam '
                .$hariTersisa.' hari. Perpanjang sekarang agar akses tidak terputus.',
            url: route('subscription.index'),
            transaction: $transaction,
        );
    }

    /**
     * Buat satu notifikasi.
     *
     * Judul diambil dari jenis notifikasi, kecuali diberikan secara eksplisit.
     */
    public function kirim(
        User $user,
        BuyerNotificationType $type,
        ?string $title = null,
        ?string $body = null,
        ?string $url = null,
        ?Transaction $transaction = null,
    ): BuyerNotification {
        return BuyerNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title ?? $type->title(),
            'body' => $body,
            'url' => $url,
            'transaction_id' => $transaction?->id,
        ]);
    }

    /**
     * Tandai seluruh notifikasi pengguna sebagai sudah dibaca.
     */
    public function tandaiSemuaDibaca(User $user): int
    {
        return BuyerNotification::query()
            ->where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Hapus notifikasi yang sudah dibaca dan lebih tua dari jumlah hari tertentu.
     */
    public function bersihkan(int $hari = 30): int
    {
        return BuyerNotification::query()
            ->whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($hari))
            ->delete();
    }
}
