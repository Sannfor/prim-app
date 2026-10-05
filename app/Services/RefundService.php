<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pengembalian dana (refund) sebuah pesanan.
 *
 * Bila Midtrans aktif, permintaan pengembalian dikirim ke API Midtrans. Bila
 * belum, pengembalian dicatat sebagai pengembalian manual oleh pengelola
 * sehingga alurnya tetap dapat didemonstrasikan.
 *
 * Pengembalian tidak menghapus pesanan: statusnya tetap "Selesai" dan diberi
 * penanda refund, supaya riwayat pembelian dan struk lama tetap utuh.
 */
class RefundService
{
    public function __construct(
        private readonly BuyerNotificationService $notifications,
    ) {}

    /**
     * Kembalikan dana sebuah pesanan.
     *
     * @param  int|null  $nominal  Jumlah yang dikembalikan; null berarti seluruhnya.
     * @param  string|null  $alasan  Alasan pengembalian untuk dicatat dan diberitahukan.
     *
     * @return array{berhasil: bool, pesan: string, nominal: int}
     */
    public function kembalikan(Transaction $transaction, ?int $nominal = null, ?string $alasan = null): array
    {
        if (! $transaction->status->isSuccessful()) {
            return $this->hasil(false, 'Hanya pesanan yang sudah lunas yang dapat dikembalikan.', 0);
        }

        if ($transaction->refunded_at !== null) {
            return $this->hasil(false, 'Pesanan ini sudah pernah dikembalikan dananya.', 0);
        }

        $nominal ??= (int) $transaction->amount;
        $nominal = max(1, min($nominal, (int) $transaction->amount));

        $melaluiMidtrans = $this->midtransAktif() && filled($transaction->payment_reference);

        if ($melaluiMidtrans) {
            $jawaban = $this->mintaMidtrans($transaction, $nominal, $alasan);

            if (! $jawaban['berhasil']) {
                return $this->hasil(false, $jawaban['pesan'], 0);
            }
        }

        DB::transaction(function () use ($transaction, $nominal, $alasan, $melaluiMidtrans) {
            $transaction->forceFill([
                'refunded_at' => now(),
                'refund_amount' => $nominal,
                'refund_reason' => $alasan,
                'notes' => $melaluiMidtrans
                    ? 'Dana dikembalikan melalui Midtrans sebesar Rp'.number_format($nominal, 0, ',', '.').'.'
                    : 'Dana dikembalikan secara manual oleh pengelola sebesar Rp'.number_format($nominal, 0, ',', '.').'.',
            ])->save();

            /*
             * Langganan dihentikan karena pembeli tidak lagi membayar layanan
             * ini. Masa aktif tidak dihapus, hanya dihentikan sejak sekarang.
             */
            $transaction->subscription?->update([
                'status' => \App\Enums\SubscriptionStatus::Cancelled,
                'cancelled_at' => now(),
            ]);
        });

        $this->notifications->danaDikembalikan($transaction->fresh(), $nominal, $alasan);

        return $this->hasil(
            true,
            'Dana Rp'.number_format($nominal, 0, ',', '.').' berhasil dikembalikan'
                .($melaluiMidtrans ? ' melalui Midtrans.' : ' dan dicatat sebagai pengembalian manual.'),
            $nominal,
        );
    }

    /**
     * Apakah Midtrans dipakai dan kuncinya tersedia.
     */
    public function midtransAktif(): bool
    {
        return filled(config('services.midtrans.server_key'))
            && config('services.midtrans.mode') === 'snap';
    }

    /**
     * Kirim permintaan pengembalian ke Midtrans.
     *
     * @return array{berhasil: bool, pesan: string}
     */
    private function mintaMidtrans(Transaction $transaction, int $nominal, ?string $alasan): array
    {
        $produksi = (bool) config('services.midtrans.production');

        $alamat = $produksi
            ? 'https://api.midtrans.com/v2/'.rawurlencode((string) $transaction->payment_reference).'/refund'
            : 'https://api.sandbox.midtrans.com/v2/'.rawurlencode((string) $transaction->payment_reference).'/refund';

        try {
            $jawaban = Http::withBasicAuth((string) config('services.midtrans.server_key'), '')
                ->acceptJson()
                ->timeout(20)
                ->post($alamat, [
                    'refund_key' => 'REF-'.$transaction->order_code,
                    'amount' => $nominal,
                    'reason' => $alasan ?: 'Pengembalian dana oleh pengelola PRIM',
                ]);

            if (! $jawaban->successful()) {
                return [
                    'berhasil' => false,
                    'pesan' => 'Midtrans menolak pengembalian dana: '
                        .$jawaban->json('status_message', 'penyebab tidak diketahui'),
                ];
            }

            return ['berhasil' => true, 'pesan' => 'Midtrans menerima permintaan pengembalian dana.'];
        } catch (Throwable $e) {
            Log::error('Gagal menghubungi Midtrans untuk pengembalian dana.', [
                'order_code' => $transaction->order_code,
                'pesan' => $e->getMessage(),
            ]);

            return ['berhasil' => false, 'pesan' => 'Tidak dapat menghubungi Midtrans: '.$e->getMessage()];
        }
    }

    /**
     * Susun hasil pengembalian.
     *
     * @return array{berhasil: bool, pesan: string, nominal: int}
     */
    private function hasil(bool $berhasil, string $pesan, int $nominal): array
    {
        return ['berhasil' => $berhasil, 'pesan' => $pesan, 'nominal' => $nominal];
    }

    /**
     * Total dana yang sudah dikembalikan untuk seorang pengguna.
     */
    public function totalDikembalikan(User $user): int
    {
        return (int) Transaction::query()
            ->where('user_id', $user->id)
            ->whereNotNull('refunded_at')
            ->sum('refund_amount');
    }
}
