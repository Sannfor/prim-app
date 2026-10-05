<?php

namespace Modules\Admin\Livewire;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\BuyerNotificationService;
use App\Services\RefundService;
use App\Services\TransactionService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Number;

/**
 * Pemantauan dan pengelolaan transaksi oleh administrator.
 *
 * Administrator dapat mengubah status transaksi secara manual, misalnya
 * menandai lunas setelah pembayaran diverifikasi di luar sistem. Perubahan
 * status menjadi lunas tetap memakai TransactionService agar langganan
 * pengguna ikut dibuat.
 */
#[Layout('layouts::admin')]
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
            $transaction->payment_method ?? 'qris',
            true
        );

        Flux::toast(
            variant: $result->successful ? 'success' : 'danger',
            text: $result->message,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Pembagian kode login
    |--------------------------------------------------------------------------
    */

    public ?int $kredensialTransaksiId = null;

    public string $kredensialLabel = '';

    public string $kredensialLogin = '';

    public string $kredensialSandi = '';

    public string $kredensialProfil = '';

    public string $kredensialPin = '';

    public string $kredensialCatatan = '';

    /**
     * Buka formulir pembagian kode login untuk sebuah pesanan.
     */
    public function bukaFormKredensial(int $transactionId): void
    {
        $transaction = Transaction::query()->with('plan.service')->findOrFail($transactionId);

        if (! $transaction->status->isSuccessful()) {
            Flux::toast(
                variant: 'warning',
                text: 'Kode login hanya dapat dibagikan untuk pesanan yang sudah lunas.',
            );

            return;
        }

        $this->resetValidation();
        $this->reset([
            'kredensialLogin', 'kredensialSandi', 'kredensialProfil',
            'kredensialPin', 'kredensialCatatan',
        ]);

        $this->kredensialTransaksiId = $transaction->id;
        $this->kredensialLabel = $transaction->plan->service->name.' — '.$transaction->plan->groupLabel();
    }

    public function tutupFormKredensial(): void
    {
        $this->kredensialTransaksiId = null;
        $this->resetValidation();
    }

    /**
     * Simpan kredensial dan beri tahu pembeli bahwa kodenya sudah tersedia.
     */
    public function simpanKredensial(BuyerNotificationService $notifications): void
    {
        if ($this->kredensialTransaksiId === null) {
            return;
        }

        $this->validate([
            'kredensialLogin' => ['required', 'string', 'max:255'],
            'kredensialSandi' => ['nullable', 'string', 'max:255'],
            'kredensialProfil' => ['nullable', 'string', 'max:255'],
            'kredensialPin' => ['nullable', 'string', 'max:20'],
            'kredensialCatatan' => ['nullable', 'string', 'max:500'],
        ], attributes: [
            'kredensialLogin' => 'kode login',
            'kredensialSandi' => 'kata sandi',
            'kredensialProfil' => 'nama profil',
            'kredensialPin' => 'PIN',
            'kredensialCatatan' => 'catatan',
        ]);

        $transaction = Transaction::query()->with('plan.service')->findOrFail($this->kredensialTransaksiId);

        $transaction->serviceCredentials()->create([
            'user_id' => $transaction->user_id,
            'label' => $this->kredensialLabel ?: $transaction->plan->service->name,
            'login_code' => $this->kredensialLogin,
            'password_code' => $this->kredensialSandi ?: null,
            'profile_name' => $this->kredensialProfil ?: null,
            'pin_code' => $this->kredensialPin ?: null,
            'notes' => $this->kredensialCatatan ?: null,
            'delivered_at' => now(),
        ]);

        // Pembeli diberi tahu bahwa kodenya sudah dapat dilihat.
        $jumlah = $transaction->serviceCredentials()->count();
        $notifications->kodeLoginSiap($transaction, $jumlah);

        $this->kredensialTransaksiId = null;

        Flux::toast(variant: 'success', text: 'Kode login dibagikan dan pembeli sudah diberi tahu.');
    }

    /*
    |--------------------------------------------------------------------------
    | Pengembalian dana
    |--------------------------------------------------------------------------
    */

    public ?int $refundTransaksiId = null;

    public string $refundNominal = '';

    public string $refundAlasan = '';

    /**
     * Buka formulir pengembalian dana.
     */
    public function bukaFormRefund(int $transactionId): void
    {
        $transaction = Transaction::query()->with('plan.service')->findOrFail($transactionId);

        if (! $transaction->status->isSuccessful()) {
            Flux::toast(variant: 'warning', text: 'Hanya pesanan lunas yang dapat dikembalikan dananya.');

            return;
        }

        if ($transaction->isRefunded()) {
            Flux::toast(variant: 'warning', text: 'Pesanan ini sudah pernah dikembalikan dananya.');

            return;
        }

        $this->resetValidation();
        $this->refundTransaksiId = $transaction->id;
        $this->refundNominal = (string) $transaction->amount;
        $this->refundAlasan = '';
    }

    public function tutupFormRefund(): void
    {
        $this->refundTransaksiId = null;
        $this->resetValidation();
    }

    /**
     * Proses pengembalian dana sebuah pesanan.
     */
    public function prosesRefund(RefundService $refunds): void
    {
        if ($this->refundTransaksiId === null) {
            return;
        }

        $this->validate([
            'refundNominal' => ['required', 'integer', 'min:1'],
            'refundAlasan' => ['required', 'string', 'min:5', 'max:500'],
        ], attributes: [
            'refundNominal' => 'nominal pengembalian',
            'refundAlasan' => 'alasan pengembalian',
        ]);

        $transaction = Transaction::query()->findOrFail($this->refundTransaksiId);

        if ((int) $this->refundNominal > (int) $transaction->amount) {
            $this->addError(
                'refundNominal',
                'Nominal tidak boleh melebihi total pesanan ('.$transaction->formattedAmount().').',
            );

            return;
        }

        $hasil = $refunds->kembalikan($transaction, (int) $this->refundNominal, $this->refundAlasan);

        Flux::toast(variant: $hasil['berhasil'] ? 'success' : 'danger', text: $hasil['pesan']);

        if ($hasil['berhasil']) {
            // Catat siapa pengelola yang memproses pengembalian ini.
            $transaction->forceFill(['refunded_by' => auth()->id()])->save();
            $this->refundTransaksiId = null;
        }
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

        $waiting = Transaction::query()->whereIn('status', [
            TransactionStatus::Pending->value,
            TransactionStatus::Processed->value,
            TransactionStatus::WaitingProcess->value,
            TransactionStatus::FollowUp->value,
        ])->count();

        $doneToday = Transaction::query()
            ->where('status', TransactionStatus::Paid->value)
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        $doneYesterday = Transaction::query()
            ->where('status', TransactionStatus::Paid->value)
            ->whereDate('updated_at', now()->subDay()->toDateString())
            ->count();

        return view('admin::livewire.transaction-manager', [
            'transactions' => $transactions,
            'statuses' => TransactionStatus::options(),
            'summaryCards' => [
                [
                    'label' => 'Total Pesanan',
                    'value' => Number::format(Transaction::query()->count(), locale: 'id'),
                    'hint' => 'Semua waktu',
                ],
                [
                    'label' => 'Menunggu',
                    'value' => Number::format($waiting, locale: 'id'),
                    'hint' => 'Perlu tindakan',
                ],
                [
                    'label' => 'Selesai Hari Ini',
                    'value' => Number::format($doneToday, locale: 'id'),
                    'hint' => ($doneToday - $doneYesterday >= 0 ? '+' : '').($doneToday - $doneYesterday).' dari kemarin',
                ],
                [
                    'label' => 'Dibatalkan',
                    'value' => Number::format(
                        Transaction::query()->where('status', TransactionStatus::Cancelled->value)->count(),
                        locale: 'id'
                    ),
                    'hint' => 'Sepanjang waktu',
                ],
            ],
            'summary' => [
                'paid' => Transaction::query()->where('status', TransactionStatus::Paid->value)->sum('amount'),
                'pending' => Transaction::query()->where('status', TransactionStatus::Pending->value)->count(),
                'total' => Transaction::query()->count(),
            ],
        ]);
    }
}
