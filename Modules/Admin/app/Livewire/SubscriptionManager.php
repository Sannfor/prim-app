<?php

namespace Modules\Admin\Livewire;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pemantauan seluruh langganan pelanggan.
 *
 * Halaman ini melengkapi dashboard dan daftar pesanan: dashboard hanya
 * menampilkan jumlah langganan aktif, sedangkan di sini administrator dapat
 * menelusuri langganan satu per satu, melihat sisa masa aktif, dan mengetahui
 * langganan yang segera berakhir maupun yang sudah berakhir.
 */
#[Layout('layouts::admin')]
#[Title('Manajemen Langganan')]
class SubscriptionManager extends Component
{
    use WithPagination;

    /**
     * Penyaring kondisi langganan: aktif, akan berakhir, berakhir, atau kosong
     * untuk semua.
     */
    #[Url(as: 'kondisi', history: true)]
    public string $status = '';

    /**
     * Kata kunci pencarian pada nama atau surel pelanggan dan nama layanan.
     */
    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'search'], true)) {
            $this->resetPage();
        }
    }

    /**
     * Terapkan penyaring kondisi ke query langganan.
     */
    private function terapkanPenyaring($query)
    {
        return $query
            ->when($this->status === 'aktif', fn ($q) => $q->where('status', SubscriptionStatus::Active->value)
                ->where('ends_at', '>', now()))
            ->when($this->status === 'akan-berakhir', fn ($q) => $q->where('status', SubscriptionStatus::Active->value)
                ->where('ends_at', '>', now())
                ->where('ends_at', '<=', now()->addDays(7)))
            ->when($this->status === 'berakhir', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('status', SubscriptionStatus::Expired->value)
                ->orWhere('ends_at', '<=', now())))
            ->when($this->search !== '', function ($q) {
                $term = '%'.$this->search.'%';

                $q->where(function ($sub) use ($term) {
                    $sub->whereHas('user', fn ($u) => $u
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term))
                        ->orWhereHas('plan.service', fn ($s) => $s->where('name', 'like', $term));
                });
            });
    }

    public function render(): View
    {
        $langganan = $this->terapkanPenyaring(
            Subscription::query()->with(['user', 'plan.service'])
        )
            ->orderByDesc('ends_at')
            ->paginate(15);

        $aktif = Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->where('ends_at', '>', now());

        $akanBerakhir = Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->where('ends_at', '>', now())
            ->where('ends_at', '<=', now()->addDays(7));

        $berakhir = Subscription::query()->where(fn ($q) => $q
            ->where('status', SubscriptionStatus::Expired->value)
            ->orWhere('ends_at', '<=', now()));

        return view('admin::livewire.subscription-manager', [
            'subscriptions' => $langganan,
            'cards' => [
                [
                    'label' => 'Langganan Aktif',
                    'value' => Number::format($aktif->count(), locale: 'id'),
                    'hint' => 'Masa aktif masih berjalan',
                ],
                [
                    'label' => 'Akan Berakhir',
                    'value' => Number::format($akanBerakhir->count(), locale: 'id'),
                    'hint' => 'Berakhir dalam 7 hari ke depan',
                ],
                [
                    'label' => 'Sudah Berakhir',
                    'value' => Number::format($berakhir->count(), locale: 'id'),
                    'hint' => 'Termasuk yang dibatalkan',
                ],
                [
                    'label' => 'Perpanjangan Otomatis',
                    'value' => Number::format(
                        (clone $aktif)->where('auto_renew', true)->count(),
                        locale: 'id'
                    ),
                    'hint' => 'Dari langganan aktif',
                ],
            ],
        ]);
    }
}
