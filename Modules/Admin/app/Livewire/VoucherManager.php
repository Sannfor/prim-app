<?php

namespace Modules\Admin\Livewire;

use App\Models\Voucher;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pengelolaan voucher atau kode promo.
 *
 * Administrator dapat membuat, mengubah, mengaktifkan, dan menghapus voucher.
 * Potongan dihitung oleh VoucherService saat pembeli memakainya, sehingga aturan
 * di sini cukup mengatur syarat dan kuota.
 */
#[Layout('layouts::admin')]
#[Title('Kelola Voucher')]
class VoucherManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'semua';

    // Formulir voucher.
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $description = '';

    public string $type = Voucher::TYPE_PERCENT;

    public int $value = 10;

    public ?int $maxDiscount = 50000;

    public int $minPurchase = 0;

    public ?int $usageLimit = 100;

    public int $usageLimitPerUser = 1;

    public string $expiresAt = '';

    public bool $isActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Buka formulir untuk voucher baru.
     */
    public function buat(): void
    {
        $this->resetValidation();
        $this->reset(['editingId', 'code', 'description', 'maxDiscount']);

        $this->type = Voucher::TYPE_PERCENT;
        $this->value = 10;
        $this->minPurchase = 0;
        $this->usageLimit = 100;
        $this->usageLimitPerUser = 1;
        $this->expiresAt = now()->addMonths(3)->format('Y-m-d');
        $this->isActive = true;
        $this->showForm = true;
    }

    /**
     * Buka formulir untuk mengubah voucher yang ada.
     */
    public function ubah(int $id): void
    {
        $voucher = Voucher::query()->whereKey($id)->firstOrFail();

        $this->resetValidation();
        $this->editingId = $voucher->id;
        $this->code = $voucher->code;
        $this->description = (string) $voucher->description;
        $this->type = $voucher->type;
        $this->value = $voucher->value;
        $this->maxDiscount = $voucher->max_discount;
        $this->minPurchase = $voucher->min_purchase;
        $this->usageLimit = $voucher->usage_limit;
        $this->usageLimitPerUser = $voucher->usage_limit_per_user;
        $this->expiresAt = $voucher->expires_at?->format('Y-m-d') ?? '';
        $this->isActive = $voucher->is_active;
        $this->showForm = true;
    }

    public function tutupForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

    /**
     * Simpan voucher baru atau perubahan voucher yang ada.
     */
    public function simpan(): void
    {
        $aturan = [
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9]+$/'],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => $this->type === Voucher::TYPE_PERCENT
                ? ['required', 'integer', 'min:1', 'max:100']
                : ['required', 'integer', 'min:1000', 'max:10000000'],
            'maxDiscount' => ['nullable', 'integer', 'min:0'],
            'minPurchase' => ['required', 'integer', 'min:0'],
            'usageLimit' => ['nullable', 'integer', 'min:1'],
            'usageLimitPerUser' => ['required', 'integer', 'min:1', 'max:50'],
            'expiresAt' => ['nullable', 'date'],
            'isActive' => ['boolean'],
        ];

        $this->validate($aturan, attributes: [
            'code' => 'kode voucher',
            'value' => 'nilai potongan',
            'maxDiscount' => 'batas maksimum potongan',
            'minPurchase' => 'minimum belanja',
            'usageLimit' => 'batas kuota',
            'usageLimitPerUser' => 'batas pemakaian per pengguna',
            'expiresAt' => 'tanggal berakhir',
            'isActive' => 'status aktif',
        ]);

        // Kode harus unik, kecuali untuk voucher yang sedang diubah.
        $bentrok = Voucher::query()
            ->where('code', strtoupper(trim($this->code)))
            ->when($this->editingId, fn ($q) => $q->whereKeyNot($this->editingId))
            ->exists();

        if ($bentrok) {
            $this->addError('code', 'Kode voucher ini sudah dipakai.');

            return;
        }

        $atribut = [
            'code' => $this->code,
            'description' => $this->description ?: null,
            'type' => $this->type,
            'value' => $this->value,
            'max_discount' => $this->type === Voucher::TYPE_PERCENT ? $this->maxDiscount : null,
            'min_purchase' => $this->minPurchase,
            'usage_limit' => $this->usageLimit,
            'usage_limit_per_user' => $this->usageLimitPerUser,
            'expires_at' => $this->expiresAt !== '' ? $this->expiresAt.' 23:59:59' : null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId !== null) {
            Voucher::query()->whereKey($this->editingId)->firstOrFail()->update($atribut);
            \Flux\Flux::toast(variant: 'success', text: 'Voucher '.$atribut['code'].' diperbarui.');
        } else {
            Voucher::create($atribut + ['used_count' => 0, 'starts_at' => null]);
            \Flux\Flux::toast(variant: 'success', text: 'Voucher '.$atribut['code'].' ditambahkan.');
        }

        $this->showForm = false;
        $this->resetPage();
    }

    /**
     * Aktifkan atau nonaktifkan voucher.
     */
    public function ubahStatus(int $id): void
    {
        $voucher = Voucher::query()->whereKey($id)->firstOrFail();

        $voucher->update(['is_active' => ! $voucher->is_active]);

        \Flux\Flux::toast(
            variant: 'success',
            text: 'Voucher '.$voucher->code.' kini '.($voucher->is_active ? 'aktif' : 'tidak aktif').'.',
        );
    }

    /**
     * Hapus voucher. Voucher yang sudah pernah dipakai tidak dihapus agar
     * riwayat pemakaian pada transaksi lama tetap utuh.
     */
    public function hapus(int $id): void
    {
        $voucher = Voucher::query()->whereKey($id)->firstOrFail();

        if ($voucher->used_count > 0) {
            $voucher->update(['is_active' => false]);

            \Flux\Flux::toast(
                variant: 'warning',
                text: 'Voucher '.$voucher->code.' sudah pernah dipakai, jadi dinonaktifkan alih-alih dihapus.',
            );

            return;
        }

        $kode = $voucher->code;
        $voucher->delete();

        \Flux\Flux::toast(variant: 'success', text: 'Voucher '.$kode.' dihapus.');
    }

    public function render(): View
    {
        $daftar = Voucher::query()
            ->when($this->search !== '', function ($q) {
                $istilah = '%'.$this->search.'%';

                $q->where(fn ($sub) => $sub
                    ->where('code', 'like', $istilah)
                    ->orWhere('description', 'like', $istilah));
            })
            ->when($this->statusFilter === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->when($this->statusFilter === 'habis', fn ($q) => $q
                ->whereNotNull('usage_limit')
                ->whereColumn('used_count', '>=', 'usage_limit'))
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->paginate(10);

        return view('admin::livewire.voucher-manager', [
            'vouchers' => $daftar,
            'ringkasan' => [
                'total' => Voucher::query()->count(),
                'aktif' => Voucher::query()->where('is_active', true)->count(),
                'pemakaian' => (int) Voucher::query()->sum('used_count'),
                'totalPotongan' => (int) \App\Models\VoucherRedemption::query()->sum('discount_amount'),
            ],
        ]);
    }
}
