<?php

namespace Modules\Subscription\Livewire;

use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman perpanjangan langganan.
 *
 * Menampilkan seluruh paket layanan yang sama sehingga pengguna dapat
 * memperpanjang, termasuk beralih ke paket lain pada layanan tersebut.
 * Perpanjangan tetap melalui alur checkout agar tercatat sebagai transaksi.
 */
#[Layout('layouts::account')]
class RenewSubscription extends Component
{
    /**
     * Id langganan yang akan diperpanjang.
     */
    public int $subscriptionId;

    public function mount(Subscription $subscription): void
    {
        $this->authorize('update', $subscription);

        $this->subscriptionId = $subscription->id;
    }

    /**
     * Langganan milik pengguna yang sedang masuk.
     */
    private function subscription(): Subscription
    {
        $subscription = Subscription::query()
            ->whereKey($this->subscriptionId)
            ->with('plan.service.provider')
            ->firstOrFail();

        $this->authorize('update', $subscription);

        return $subscription;
    }

    public function render(): View
    {
        $subscription = $this->subscription();
        $service = $subscription->plan->service;

        $plans = $service->plans()
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        return view('subscription::livewire.renew-subscription', [
            'subscription' => $subscription,
            'service' => $service,
            'plans' => $plans,
        ])->title('Perpanjang '.$service->name);
    }
}
