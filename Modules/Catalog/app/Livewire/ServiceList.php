<?php

namespace Modules\Catalog\Livewire;

use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Katalog layanan premium PRIM dengan pencarian, filter, dan pengurutan.
 *
 * Halaman ini bersifat publik: pengunjung dapat menelusuri katalog tanpa
 * login, dan baru diarahkan masuk ketika hendak membeli paket.
 */
#[Layout('layouts::app')]
#[Title('Katalog Layanan')]
class ServiceList extends Component
{
    use WithPagination;

    /**
     * Kata kunci pencarian pada nama, tagline, atau deskripsi layanan.
     */
    #[Url(as: 'q', history: true)]
    public string $search = '';

    /**
     * Filter kategori berdasarkan slug.
     */
    #[Url(as: 'kategori', history: true)]
    public string $category = '';

    /**
     * Filter penyedia layanan berdasarkan slug.
     */
    #[Url(as: 'provider', history: true)]
    public string $provider = '';

    /**
     * Batas harga maksimum paket termurah.
     */
    #[Url(as: 'maks', history: true)]
    public ?int $maxPrice = null;

    /**
     * Urutan hasil: terbaru, termurah, termahal, atau nama.
     */
    #[Url(as: 'urut', history: true)]
    public string $sort = 'terbaru';

    /**
     * Jumlah kartu per halaman.
     */
    public int $perPage = 9;

    /**
     * Kembalikan ke halaman pertama setiap kali filter berubah.
     */
    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'category', 'provider', 'maxPrice', 'sort'], true)) {
            $this->resetPage();
        }
    }

    /**
     * Bersihkan seluruh filter dan kembali ke tampilan awal.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'provider', 'maxPrice']);
        $this->sort = 'terbaru';
        $this->resetPage();
    }

    /**
     * Apakah ada filter yang sedang aktif.
     */
    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->category !== ''
            || $this->provider !== ''
            || $this->maxPrice !== null;
    }

    /**
     * Tambah atau lepas layanan dari daftar bandingkan (maksimal 4).
     */
    public function toggleCompare(int $serviceId): void
    {
        $compare = session('compare', []);

        if (in_array($serviceId, $compare, true)) {
            $compare = array_values(array_diff($compare, [$serviceId]));
            Flux::toast(variant: 'warning', text: 'Layanan dihapus dari daftar bandingkan.');
        } elseif (count($compare) >= 4) {
            Flux::toast(variant: 'danger', text: 'Maksimal 4 layanan dapat dibandingkan sekaligus.');

            return;
        } else {
            $compare[] = $serviceId;
            Flux::toast(variant: 'success', text: 'Layanan ditambahkan ke daftar bandingkan.');
        }

        session(['compare' => $compare]);
    }

    /**
     * Daftar id layanan yang sedang dipilih untuk dibandingkan.
     *
     * @return list<int>
     */
    public function compareSelection(): array
    {
        return array_values(session('compare', []));
    }

    public function render(): View
    {
        $services = Service::query()
            ->active()
            ->withCatalogRelations()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('tagline', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($this->category !== '', function ($query) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $this->category));
            })
            ->when($this->provider !== '', function ($query) {
                $query->whereHas('provider', fn ($q) => $q->where('slug', $this->provider));
            })
            ->when($this->maxPrice !== null, function ($query) {
                $query->whereHas('plans', function ($q) {
                    $q->where('is_active', true)->where('price', '<=', $this->maxPrice);
                });
            });

        // Pengurutan dipisah dari rantai filter karena match() tidak dapat
        // dirantai langsung sebagai method builder.
        match ($this->sort) {
            'termurah' => $services->withMin(
                ['plans as lowest_price' => fn ($q) => $q->where('is_active', true)],
                'price'
            )->orderBy('lowest_price'),
            'termahal' => $services->withMin(
                ['plans as lowest_price' => fn ($q) => $q->where('is_active', true)],
                'price'
            )->orderByDesc('lowest_price'),
            'nama' => $services->orderBy('name'),
            default => $services->latest(),
        };

        $services = $services->paginate($this->perPage);

        return view('catalog::livewire.service-list', [
            'services' => $services,
            'categories' => Category::query()->withActiveServices()->orderBy('name')->get(),
            'providers' => Provider::query()->withActiveServices()->orderBy('name')->get(),
            'compareSelection' => $this->compareSelection(),
        ]);
    }
}
