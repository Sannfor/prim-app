<?php

namespace Modules\Subscription\Livewire;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Daftar langganan milik pengguna: yang sedang aktif, yang akan segera
 * berakhir, dan riwayat langganan yang sudah selesai.
 */
#[Layout('layouts::app')]
#[Title('Langganan Saya')]
class SubscriptionList extends Component
{
    /**
     * Filter tampilan: aktif, akan berakhir, kedaluwarsa, atau semua.
     */
    #[Url(as: 'tampil', history: true)]
    public string $filter = 'aktif';

    /**
     * Daftar filter yang tersedia beserta labelnya.
     *
     * @return array<string, string>
     */
    public function filters(): array
    {
        return [
            'aktif' => 'Sedang Aktif',
            'berakhir' => 'Akan Berakhir',
            'kedaluwarsa' => 'Sudah Berakhir',
            'semua' => 'Semua',
        ];
    }

    /**
     * Ubah preferensi perpanjangan otomatis sebuah langganan.
     */
    public function toggleAutoRenew(int $subscriptionId): void
    {
        $subscription = Subscription::query()->whereKey($subscriptionId)->firstOrFail();

        $this->authorize('update', $subscription);

        $subscription->update(['auto_renew' => ! $subscription->auto_renew]);

        Flux::toast(
            variant: 'success',
            text: $subscription->auto_renew
                ? 'Perpanjangan otomatis diaktifkan.'
                : 'Perpanjangan otomatis dimatikan.',
        );
    }

    /**
     * Batalkan perpanjangan otomatis sebuah langganan.
     *
     * Masa aktif yang sedang berjalan tetap dihormati sampai tanggal berakhir.
     */
    public function cancelAutoRenew(int $subscriptionId): void
    {
        $subscription = Subscription::query()->whereKey($subscriptionId)->firstOrFail();

        $this->authorize('update', $subscription);

        $subscription->update([
            'auto_renew' => false,
            'cancelled_at' => now(),
        ]);

        Flux::toast(variant: 'warning', text: 'Perpanjangan otomatis dibatalkan.');
    }

    /**
     * Daftar langganan sesuai filter yang dipilih.
     */
    private function subscriptions(): Collection
    {
        $query = Subscription::query()
            ->where('user_id', auth()->id())
            ->with(['plan.service.provider', 'plan.service.category', 'transaction']);

        match ($this->filter) {
            'aktif' => $query->where('status', SubscriptionStatus::Active->value)
                ->where('ends_at', '>', now()),
            'berakhir' => $query->where('status', SubscriptionStatus::Active->value)
                ->where('ends_at', '>', now())
                ->where('ends_at', '<=', now()->addDays(7)),
            'kedaluwarsa' => $query->where(fn ($q) => $q
                ->where('ends_at', '<=', now())
                ->orWhere('status', SubscriptionStatus::Expired->value)),
            default => $query,
        };

        return $query->orderByDesc('ends_at')->get();
    }

    public function render(): View
    {
        $subscriptions = $this->subscriptions();

        $all = Subscription::query()->where('user_id', auth()->id());

        return view('subscription::livewire.subscription-list', [
            'subscriptions' => $subscriptions,
            'filters' => $this->filters(),
            'activeCount' => (clone $all)->where('status', SubscriptionStatus::Active->value)
                ->where('ends_at', '>', now())->count(),
            'expiringSoonCount' => (clone $all)->where('status', SubscriptionStatus::Active->value)
                ->whereBetween('ends_at', [now(), now()->addDays(7)])->count(),
        ]);
    }
}
