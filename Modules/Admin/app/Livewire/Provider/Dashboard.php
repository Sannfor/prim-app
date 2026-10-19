<?php

namespace Modules\Admin\Livewire\Provider;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\Admin\Livewire\Provider\Concerns\ScopesToProvider;

/**
 * Ringkasan keadaan layanan milik satu penyedia.
 *
 * Seluruh angka dihitung dari layanan milik penyedia yang sedang masuk saja,
 * sehingga penyedia tidak melihat data penyedia lain.
 */
#[Layout('layouts::admin')]
#[Title('Dashboard Penyedia')]
class Dashboard extends Component
{
    use ScopesToProvider;

    public function render(): View
    {
        $provider = $this->penyedia();

        $services = $provider->services()->withCount(['plans', 'reviews'])->get();
        $serviceIds = $services->pluck('id');

        /*
         * Pesanan dihitung lewat relasi transaksi -> paket -> layanan, karena
         * transaksi menyimpan plan_id, bukan service_id.
         */
        $pesanan = Transaction::query()
            ->whereHas('plan', fn ($q) => $q->whereIn('service_id', $serviceIds));

        $pendapatan = (clone $pesanan)
            ->whereIn('status', ['paid', 'accepted'])
            ->sum('amount');

        $terbaru = (clone $pesanan)
            ->with(['user', 'plan.service'])
            ->latest()
            ->take(6)
            ->get();

        return view('admin::livewire.provider.dashboard', [
            'provider' => $provider,
            'services' => $services,
            'ringkasan' => [
                'layanan' => $services->count(),
                'paketAktif' => $services->sum('plans_count'),
                'pesanan' => $pesanan->count(),
                'pendapatan' => (int) $pendapatan,
            ],
            'terbaru' => $terbaru,
        ]);
    }

    /**
     * Arahkan penyedia ke halaman paket layanan yang dipilih.
     */
    public function kelola(string $slug): void
    {
        // Pastikan layanan yang dituju memang milik penyedia ini.
        $this->penyedia()->services()->where('slug', $slug)->firstOrFail();

        $this->redirectRoute('provider.plans.index', ['service' => $slug], navigate: true);
    }
}
