<?php

namespace Modules\Admin\Livewire;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Rekap pembayaran.
 *
 * Mengikuti frame "ADMIN - pembayaran" pada desain: kartu ringkasan
 * (total pendapatan, transaksi berhasil, menunggu pembayaran, refund),
 * penyaring kanal pembayaran, dan tabel transaksi dengan aksi verifikasi.
 */
#[Layout('layouts::admin')]
#[Title('Manajemen Pembayaran')]
class PaymentManager extends Component
{
    use WithPagination;

    #[Url(as: 'kanal', history: true)]
    public string $channel = '';

    #[Url(as: 'status', history: true)]
    public string $status = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['channel', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $transactions = Transaction::query()
            ->with(['user', 'plan.service'])
            ->when($this->channel !== '', fn ($q) => $q->where('payment_method', $this->channel))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        $income = Transaction::query()
            ->whereIn('status', [TransactionStatus::Paid->value, TransactionStatus::Accepted->value])
            ->sum('amount');

        $successful = Transaction::query()
            ->whereIn('status', [TransactionStatus::Paid->value, TransactionStatus::Accepted->value])
            ->count();

        $channels = Transaction::query()
            ->whereNotNull('payment_method_label')
            ->distinct()
            ->orderBy('payment_method_label')
            ->pluck('payment_method_label');

        return view('admin::livewire.payment-manager', [
            'transactions' => $transactions,
            'channels' => $channels,
            'statuses' => TransactionStatus::options(),
            'cards' => [
                [
                    'label' => 'Total Pendapatan',
                    'value' => 'Rp'.Number::format($income, locale: 'id'),
                    'hint' => 'Dari transaksi berhasil',
                ],
                [
                    'label' => 'Transaksi Berhasil',
                    'value' => Number::format($successful, locale: 'id'),
                    'hint' => 'Termasuk pesanan diterima',
                ],
                [
                    'label' => 'Menunggu Pembayaran',
                    'value' => Number::format(
                        Transaction::query()->where('status', TransactionStatus::Pending->value)->count(),
                        locale: 'id'
                    ),
                    'hint' => 'Belum dibayar pelanggan',
                ],
                [
                    'label' => 'Dibatalkan',
                    'value' => 'Rp'.Number::format(
                        Transaction::query()->where('status', TransactionStatus::Cancelled->value)->sum('amount'),
                        locale: 'id'
                    ),
                    'hint' => 'Nilai pesanan batal',
                ],
            ],
        ]);
    }
}
