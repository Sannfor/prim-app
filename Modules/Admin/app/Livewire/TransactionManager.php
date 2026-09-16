<?php

namespace Modules\Admin\Livewire;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\TransactionService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pemantauan dan pengelolaan transaksi oleh administrator.
 *
 * Administrator dapat mengubah status transaksi secara manual, misalnya
 * menandai lunas setelah pembayaran diverifikasi di luar sistem. Perubahan
 * status menjadi lunas tetap memakai TransactionService agar langganan
 * pengguna ikut dibuat.
 */
#[Layout('layouts::app')]
#[Title('Kelola Transaksi')]
class TransactionManager extends Component
{
    use WithPagination;

    #[Url(as: 'status', history: true)]
    public string $status = '';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'search'], true)) {
            $this->resetPage();
        }
    }

    /**
     * Tandai sebuah transaksi berhasil dibayar dan aktifkan langganannya.
     */
    public function markAsPaid(int $transactionId, TransactionService $transactions): void
    {
        $transaction = Transaction::query()->with('plan')->findOrFail($transactionId);

        if ($transaction->status === TransactionStatus::Paid) {
            Flux::toast(variant: 'warning', text: 'Transaksi ini sudah berstatus berhasil.');

            return;
        }

        $result = $transactions->pay(
            $transaction,
            $transaction->user,
            $transaction->payment_method ?? 'transfer',
            true
        );

        Flux::toast(
            variant: $result->successful ? 'success' : 'danger',
            text: $result->message,
        );
    }

    /**
     * Tandai sebuah transaksi gagal.
     */
    public function markAsFailed(int $transactionId): void
    {
        $transaction = Transaction::query()->findOrFail($transactionId);

        if ($transaction->status === TransactionStatus::Paid) {
            Flux::toast(
                variant: 'danger',
                text: 'Transaksi yang sudah berhasil tidak dapat ditandai gagal.'
            );

            return;
        }

        $transaction->update([
            'status' => TransactionStatus::Failed,
            'notes' => 'Ditandai gagal oleh administrator.',
        ]);

        Flux::toast(variant: 'success', text: 'Transaksi ditandai gagal.');
    }

    public function render(): View
    {
        $transactions = Transaction::query()
            ->with(['user', 'plan.service'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(fn ($q) => $q
                    ->where('order_code', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)));
            })
            ->latest()
            ->paginate(15);

        return view('admin::livewire.transaction-manager', [
            'transactions' => $transactions,
            'statuses' => TransactionStatus::options(),
            'summary' => [
                'paid' => Transaction::query()->where('status', TransactionStatus::Paid->value)->sum('amount'),
                'pending' => Transaction::query()->where('status', TransactionStatus::Pending->value)->count(),
                'total' => Transaction::query()->count(),
            ],
        ]);
    }
}
