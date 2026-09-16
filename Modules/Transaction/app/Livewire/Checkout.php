<?php

namespace Modules\Transaction\Livewire;

use App\Models\Plan;
use App\Services\TransactionService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman checkout: pengguna memilih paket dan metode pembayaran, lalu
 * transaksi dibuat dengan status menunggu pembayaran.
 */
#[Layout('layouts::app')]
#[Title('Checkout')]
class Checkout extends Component
{
    /**
     * Id paket yang akan dibeli.
     */
    public int $planId;

    /**
     * Kode metode pembayaran yang dipilih.
     */
    public string $paymentMethod = '';

    /**
     * Muat paket dari segmen URL dan sediakan metode pembayaran bawaan.
     */
    public function mount(Plan $plan, TransactionService $transactions): void
    {
        abort_unless($plan->is_active, 404);

        $this->planId = $plan->id;
        $this->paymentMethod = array_key_first($transactions->gateway()->methods()) ?? '';
    }

    /**
     * Buat transaksi dan arahkan pengguna ke halaman pembayaran.
     */
    public function checkout(TransactionService $transactions)
    {
        $plan = Plan::query()->whereKey($this->planId)->firstOrFail();

        abort_unless($plan->is_active, 404);

        $this->validate([
            'paymentMethod' => ['required', 'string', 'in:'.implode(',', array_keys($transactions->gateway()->methods()))],
        ], attributes: [
            'paymentMethod' => 'metode pembayaran',
        ]);

        $transaction = $transactions->checkout(
            auth()->user(),
            $plan,
            $this->paymentMethod
        );

        return redirect()->route('transaction.show', $transaction->order_code);
    }

    public function render(TransactionService $transactions): View
    {
        $plan = Plan::query()
            ->with('service.provider', 'service.category')
            ->whereKey($this->planId)
            ->firstOrFail();

        // Pengguna yang sudah punya langganan aktif atas layanan yang sama
        // diberi tahu bahwa pembelian ini akan menyambung masa aktif.
        $activeSubscription = auth()->user()
            ->activeSubscriptions()
            ->whereHas('plan', fn ($q) => $q->where('service_id', $plan->service_id))
            ->with('plan')
            ->latest('ends_at')
            ->first();

        return view('transaction::livewire.checkout', [
            'plan' => $plan,
            'methods' => $transactions->gateway()->methods(),
            'gatewayName' => $transactions->gateway()->name(),
            'activeSubscription' => $activeSubscription,
        ]);
    }
}
