<?php

namespace Modules\Transaction\Livewire;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Riwayat transaksi milik pengguna yang sedang masuk.
 */
#[Layout('layouts::account')]
#[Title('Riwayat Transaksi')]
class TransactionHistory extends Component
{
    use WithPagination;

    /**
     * Filter status transaksi.
     */
    #[Url(as: 'status', history: true)]
    public string $status = '';

    public function updated(string $property): void
    {
        if ($property === 'status') {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $transactions = Transaction::query()
            ->where('user_id', auth()->id())
            ->with(['plan.service.provider', 'plan.service.category', 'subscription'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(10);

        return view('transaction::livewire.transaction-history', [
            'transactions' => $transactions,
            'statuses' => TransactionStatus::options(),
        ]);
    }
}
