<?php

namespace Modules\Transaction\Livewire;

use App\Models\Plan;
use App\Models\Voucher;
use App\Services\TransactionService;
use App\Services\VoucherService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman checkout: pengguna memilih paket dan metode pembayaran, lalu
 * transaksi dibuat dengan status menunggu pembayaran.
 */
#[Layout('layouts::account')]
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
     * Kode voucher yang sedang diketik pengguna.
     */
    public string $voucherCode = '';

    /**
     * Kode voucher yang sudah diterapkan. Disimpan sebagai kode, bukan sebagai
     * model, agar keadaan komponen tetap dapat dipulihkan pada setiap permintaan.
     */
    public ?string $appliedVoucher = null;

    /**
     * Potongan yang dihasilkan voucher terpilih, dalam rupiah.
     */
    public int $appliedDiscount = 0;

    /**
     * Pesan hasil pemeriksaan voucher.
     */
    public string $voucherMessage = '';

    /**
     * Apakah pesan voucher menunjukkan keberhasilan.
     */
    public bool $voucherValid = false;

    /**
     * Muat paket dari segmen URL dan sediakan metode pembayaran bawaan.
     *
     * Parameter diterima sebagai string (id paket) lalu dicari eksplisit agar
     * tidak bergantung pada perilaku implicit binding Livewire.
     */
    public function mount(string $plan, TransactionService $transactions): void
    {
        $plan = $this->resolvePlan($plan);

        $this->planId = $plan->id;
        $this->paymentMethod = array_key_first($transactions->gateway()->methods()) ?? '';
    }

    /**
     * Cari paket aktif berdasarkan id.
     */
    private function resolvePlan(string $planId): Plan
    {
        $plan = Plan::query()->whereKey($planId)->firstOrFail();

        abort_unless($plan->is_active, 404);

        return $plan;
    }

    /**
     * Pindah ke paket lain pada layanan yang sama.
     *
     * Dipakai oleh pemilih paket pada halaman konfirmasi, sehingga pengguna dapat
     * mengubah durasi atau jumlah perangkat tanpa kembali ke katalog.
     */
    public function choosePlan(int $planId): void
    {
        $plan = $this->resolvePlan((string) $planId);

        // Paket hanya boleh diganti dalam layanan yang sama.
        if ($plan->service_id !== $this->plan()->service_id) {
            return;
        }

        $this->planId = $plan->id;
    }

    /**
     * Paket yang sedang dipilih, lengkap dengan relasinya.
     */
    private function plan(): Plan
    {
        return Plan::query()
            ->with('service.provider', 'service.category')
            ->whereKey($this->planId)
            ->firstOrFail();
    }

    /**
     * Periksa dan terapkan kode voucher yang diketik pengguna.
     */
    public function terapkanVoucher(VoucherService $vouchers): void
    {
        $this->resetErrorBag('voucherCode');

        $hasil = $vouchers->periksa($this->voucherCode, auth()->user(), $this->plan());

        $this->voucherValid = $hasil['valid'];
        $this->voucherMessage = $hasil['message'];

        if ($hasil['valid']) {
            $this->appliedVoucher = $hasil['voucher']->code;
            $this->appliedDiscount = $hasil['discount'];
            $this->voucherCode = $hasil['voucher']->code;
        } else {
            $this->appliedVoucher = null;
            $this->appliedDiscount = 0;
        }
    }

    /**
     * Batalkan voucher yang sudah diterapkan.
     */
    public function hapusVoucher(): void
    {
        $this->appliedVoucher = null;
        $this->appliedDiscount = 0;
        $this->voucherCode = '';
        $this->voucherMessage = '';
        $this->voucherValid = false;
    }

    /**
     * Buat transaksi dan arahkan pengguna ke halaman pembayaran.
     */
    public function checkout(TransactionService $transactions)
    {
        $plan = $this->resolvePlan((string) $this->planId);

        $this->validate([
            'paymentMethod' => ['required', 'string', 'in:'.implode(',', array_keys($transactions->gateway()->methods()))],
        ], attributes: [
            'paymentMethod' => 'metode pembayaran',
        ]);

        $transaction = $transactions->checkout(
            auth()->user(),
            $plan,
            $this->paymentMethod,
            $this->appliedVoucher,
        );

        return redirect()->route('transaction.show', $transaction->order_code);
    }

    public function render(TransactionService $transactions): View
    {
        $plan = $this->plan();

        // Seluruh paket aktif dari layanan yang sama ditawarkan sebagai pilihan,
        // agar pengguna tidak perlu kembali ke katalog hanya untuk mengganti paket.
        $pilihanPaket = Plan::query()
            ->where('service_id', $plan->service_id)
            ->where('is_active', true)
            ->orderByRaw('variant_group is null, variant_group')
            ->orderBy('duration_days')
            ->orderBy('max_devices')
            ->orderBy('price')
            ->get();

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
            'pilihanPaket' => $pilihanPaket,
            'methods' => $transactions->gateway()->methods(),
            'gatewayName' => $transactions->gateway()->name(),
            'activeSubscription' => $activeSubscription,
            'voucherTersedia' => Voucher::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->orderBy('code')
                ->take(4)
                ->get(),
        ]);
    }
}
