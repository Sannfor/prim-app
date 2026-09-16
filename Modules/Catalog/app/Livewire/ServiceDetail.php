<?php

namespace Modules\Catalog\Livewire;

use App\Models\Service;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman detail sebuah layanan premium beserta seluruh paketnya.
 *
 * Bersifat publik. Tombol pembelian mengarahkan pengunjung ke modul
 * Transaction, yang akan meminta login terlebih dahulu bila belum masuk.
 */
#[Layout('layouts::app')]
class ServiceDetail extends Component
{
    /**
     * Slug layanan dari segmen URL, mis. /katalog/nusantara-film-premium.
     *
     * Disimpan sebagai string karena properti yang bertipe model akan ditimpa
     * langsung oleh Livewire dari parameter mount dan memicu TypeError.
     */
    public string $serviceSlug = '';

    /**
     * Muat layanan beserta relasi katalognya.
     */
    public function mount(string $service): void
    {
        $this->serviceSlug = $service;
    }

    /**
     * Apakah layanan ini sedang dipilih untuk dibandingkan.
     */
    public function isCompared(): bool
    {
        return in_array($this->service()->id, session('compare', []), true);
    }

    /**
     * Tambah atau lepas layanan ini dari daftar bandingkan.
     */
    public function toggleCompare(): void
    {
        $id = $this->service()->id;
        $compare = session('compare', []);

        if (in_array($id, $compare, true)) {
            $compare = array_values(array_diff($compare, [$id]));
            Flux::toast(variant: 'warning', text: 'Layanan dihapus dari daftar bandingkan.');
        } elseif (count($compare) >= 4) {
            Flux::toast(variant: 'danger', text: 'Maksimal 4 layanan dapat dibandingkan sekaligus.');

            return;
        } else {
            $compare[] = $id;
            Flux::toast(variant: 'success', text: 'Layanan ditambahkan ke daftar bandingkan.');
        }

        session(['compare' => $compare]);
    }

    /**
     * Layanan yang sedang ditampilkan, dengan relasi katalognya.
     */
    private function service(): Service
    {
        return Service::query()
            ->where('slug', $this->serviceSlug)
            ->withCatalogRelations()
            ->firstOrFail();
    }

    public function render(): View
    {
        $service = $this->service();

        return view('catalog::livewire.service-detail', [
            'service' => $service,
            'isCompared' => $this->isCompared(),
            'plans' => $service->plans->where('is_active', true)->sortBy('price'),
            'rating' => $service->averageRating(),
        ])->title($service->name);
    }
}
