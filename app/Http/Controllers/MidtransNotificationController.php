<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Penerima notifikasi pembayaran Midtrans.
 *
 * Midtrans memanggil alamat ini setiap kali status pembayaran berubah. Alur:
 *   1. Notifikasi diverifikasi keasliannya memakai skema signature_key Midtrans.
 *   2. Transaksi dicari berdasarkan order_id yang dikirim.
 *   3. Status pesanan diperbarui; bila lunas, langganan ikut dibuat.
 *
 * Alamat ini dikecualikan dari proteksi CSRF karena dipanggil dari luar aplikasi
 * (lihat bootstrap/app.php).
 */
class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, TransactionService $transactions): JsonResponse
    {
        $serverKey = (string) config('services.midtrans.server_key', '');

        if ($serverKey === '') {
            return response()->json(['message' => 'Pembayaran daring belum dikonfigurasi.'], 503);
        }

        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $signature = (string) $request->input('signature_key');
        $transactionStatus = (string) $request->input('transaction_status');
        $fraudStatus = (string) $request->input('fraud_status', 'accept');

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if (! hash_equals($expected, $signature)) {
            Log::warning('Notifikasi Midtrans ditolak: signature tidak cocok.', ['order_id' => $orderId]);

            return response()->json(['message' => 'Signature tidak cocok.'], 403);
        }

        $transaction = Transaction::query()->where('payment_reference', $orderId)->first();

        if ($transaction === null) {
            Log::warning('Notifikasi Midtrans untuk pesanan yang tidak dikenal.', ['order_id' => $orderId]);

            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Pemetaan status Midtrans ke status pesanan PRIM.
        $berhasil = in_array($transactionStatus, ['capture', 'settlement'], true) && $fraudStatus === 'accept';

        if ($berhasil) {
            if (! $transaction->status->isSuccessful()) {
                $transactions->markAsPaid($transaction, $orderId);
            }

            return response()->json(['message' => 'Pembayaran diterima.']);
        }

        $baru = match ($transactionStatus) {
            'pending' => TransactionStatus::Pending,
            'deny', 'failure' => TransactionStatus::Failed,
            'cancel' => TransactionStatus::Cancelled,
            'expire' => TransactionStatus::Expired,
            'refund', 'partial_refund' => TransactionStatus::Cancelled,
            default => null,
        };

        if ($baru !== null && ! $transaction->status->isFinal()) {
            $transaction->forceFill([
                'status' => $baru,
                'notes' => 'Status diperbarui dari notifikasi Midtrans: '.$transactionStatus,
                'payment_url' => null,
                'payment_token' => null,
            ])->save();
        }

        return response()->json(['message' => 'Notifikasi diproses.']);
    }
}
