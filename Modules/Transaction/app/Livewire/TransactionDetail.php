<?php

namespace Modules\Transaction\Livewire;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
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
#[Layout('layouts::app')]
class TransactionDetail extends Component
{
    /**
     * Kode pesanan dari segmen URL, mis. PRIM-20260101-ABC123.
     */
    public string $orderCode = '';

    public function mount(string $order): void
    {
        $this->orderCode = $order;
    }

    /**
     * Transaksi milik pengguna yang sedang masuk.
     */
    private function transaction(): Transaction
    {
        $transaction = Transaction::query()
            ->where('order_code', $this->orderCode)
            ->with(['plan.service.provider', 'plan.service.category', 'subscription'])
            ->firstOrFail();

        $this->authorize('view', $transaction);

        return $transaction;
    }

    /**
     * Jalankan simulasi pembayaran.
     */
    public function pay(TransactionService $transactions, bool $succeed = true)
    {
        $transaction = $this->transaction();

        $this->authorize('pay', $transaction);

        $result = $transactions->pay(
            $transaction,
            auth()->user(),
            $transaction->payment_method ?? 'transfer',
            $succeed
        );

        Flux::toast(
            variant: $result->successful ? 'success' : 'danger',
            text: $result->message,
        );

        if ($result->successful) {
            return redirect()->route('subscription.index');
        }

        return null;
    }

    public function render(TransactionService $transactions): View
    {
        $transaction = $this->transaction();

        return view('transaction::livewire.transaction-detail', [
            'transaction' => $transaction,
            'isPayable' => $transaction->isPayable(),
            'hasExpired' => $transaction->hasExpired(),
            'paymentMethodLabel' => $transaction->payment_method === null
                ? 'metode yang dipilih'
                : $transactions->gateway()->label($transaction->payment_method),
        ])->title('Pembayaran '.$transaction->order_code);
    }
}
