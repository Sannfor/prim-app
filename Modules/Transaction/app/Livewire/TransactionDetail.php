<?php

namespace Modules\Transaction\Livewire;

use App\Models\Transaction;
use App\Services\Payment\PaymentGateway;
use App\Services\TransactionService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman pembayaran sebuah transaksi.
 *
 * Karena PRIM memakai simulasi gateway internal, pengguna (atau penguji) dapat
 * memilih untuk mensimulasikan pembayaran yang berhasil maupun yang ditolak.
 */
#[Layout('layouts::account')]
class TransactionDetail extends Component
{
    /**
     * Kode pesanan dari segmen URL, mis. PRIM-20260101-ABC123.
     */
    public string $orderCode = '';

    /**
     * Metode pembayaran yang dipilih pada halaman ini.
     *
     * Bisa berbeda dari metode yang dipilih saat checkout, karena pengguna
     * sering baru memutuskan kanal pembayaran pada halaman pembayaran.
     */
    public string $paymentMethod = '';

    public function mount(string $order): void
    {
        $this->orderCode = $order;

        $transaksi = $this->transaction();
        $metode = array_keys(app(PaymentGateway::class)->methods());

        // Pakai metode yang sudah tersimpan bila masih dikenal, jika tidak pakai
        // kanal pertama yang tersedia.
        $this->paymentMethod = in_array($transaksi->payment_method, $metode, true)
            ? (string) $transaksi->payment_method
            : (string) ($metode[0] ?? '');
    }

    /**
     * Ganti metode pembayaran sebelum membayar.
     */
    public function pilihMetode(string $method): void
    {
        if (array_key_exists($method, app(PaymentGateway::class)->methods())) {
            $this->paymentMethod = $method;
        }
    }

    /**
     * Transaksi milik pengguna yang sedang masuk.
     */
    private function transaction(): Transaction
    {
        $transaction = Transaction::query()
            ->where('order_code', $this->orderCode)
            ->with(['plan.service.provider', 'plan.service.category', 'subscription', 'voucher'])
            ->firstOrFail();

        $this->authorize('view', $transaction);

        return $transaction;
    }

    /**
     * Jalankan pembayaran.
     *
     * Bila gateway memerlukan tindakan pengguna (mis. Midtrans Snap), pengguna
     * diarahkan ke halaman pembayaran penyedia. Bila gateway bekerja dalam mode
     * simulasi, hasilnya langsung ditentukan dan langganan langsung aktif.
     */
    public function pay(TransactionService $transactions, bool $succeed = true)
    {
        $transaction = $this->transaction();

        $this->authorize('pay', $transaction);

        // Metode yang dipilih pada halaman ini dipakai lebih dulu, karena
        // pengguna sering baru memutuskan kanal pembayaran di sini.
        $metode = $this->paymentMethod !== '' ? $this->paymentMethod : 'qris';

        $result = $transactions->pay(
            $transaction,
            auth()->user(),
            $metode,
            $succeed
        );

        // Pembayaran daring: arahkan pengguna ke halaman penyedia.
        if ($result->requiresAction && filled($result->redirectUrl)) {
            Flux::toast(variant: 'success', text: 'Mengarahkan ke halaman pembayaran…');

            return redirect()->away($result->redirectUrl);
        }

        Flux::toast(
            variant: $result->successful ? 'success' : 'danger',
            text: $result->message,
        );

        if ($result->successful) {
            return redirect()->route('transaction.receipt', $transaction->order_code);
        }

        return null;
    }

    public function render(TransactionService $transactions): View
    {
        $transaction = $this->transaction();
        $gateway = $transactions->gateway();

        // Label metode yang ditampilkan mengikuti pilihan pada halaman ini, agar
        // ringkasan rincian ikut berubah saat pengguna mengganti kanal pembayaran.
        $label = $transaction->payment_method_label
            ?? ($transaction->payment_method === null ? null : $gateway->label($transaction->payment_method));

        if ($this->paymentMethod !== '' && $transaction->isPayable()) {
            $label = $gateway->label($this->paymentMethod);
        }

        return view('transaction::livewire.transaction-detail', [
            'transaction' => $transaction,
            'isPayable' => $transaction->isPayable(),
            'hasExpired' => $transaction->hasExpired(),
            'paymentMethodLabel' => $label ?? 'metode yang dipilih',
            'methods' => $gateway->methods(),
            'gatewayName' => $gateway->name(),
            'usesOnlineGateway' => $gateway->requiresRedirect(),
        ])->title('Pembayaran '.$transaction->order_code);
    }
}
