<?php

namespace Modules\Catalog\Livewire;

use App\Models\Plan;
use App\Models\Service;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Halaman komparasi side-by-side untuk 2-4 layanan premium.
 *
 * Pemilihan layanan disimpan di session sehingga pilihan dari katalog
 * ikut terbawa ke halaman ini.
 */
#[Layout('layouts::app')]
#[Title('Bandingkan Layanan')]
class Comparison extends Component
{
    /**
     * Batas maksimum layanan yang dapat dibandingkan sekaligus.
     */
    public const MAX_SERVICES = 4;

    /**
     * Id layanan yang dipilih untuk dibandingkan.
     *
     * @var list<int>
     */
    public array $selected = [];

    public function mount(): void
    {
        $this->selected = array_values(array_map('intval', session('compare', [])));
    }

    /**
     * Tambahkan layanan ke daftar komparasi.
     */
    public function add(int $serviceId): void
    {
        if (in_array($serviceId, $this->selected, true)) {
            return;
        }

        if (count($this->selected) >= self::MAX_SERVICES) {
            Flux::toast(variant: 'danger', text: 'Maksimal '.self::MAX_SERVICES.' layanan dapat dibandingkan sekaligus.');

            return;
        }

        $this->selected[] = $serviceId;
        $this->selected = array_values($this->selected);
        $this->syncSession();
    }

    /**
     * Lepas layanan dari daftar komparasi.
     */
    public function remove(int $serviceId): void
    {
        $this->selected = array_values(array_diff($this->selected, [$serviceId]));
        $this->syncSession();
    }

    /**
     * Kosongkan seluruh daftar komparasi.
     */
    public function clear(): void
    {
        $this->selected = [];
        $this->syncSession();

        Flux::toast(variant: 'warning', text: 'Daftar perbandingan dikosongkan.');
    }

    /**
     * Simpan pilihan ke session agar konsisten dengan halaman katalog.
     */
    private function syncSession(): void
    {
        session(['compare' => $this->selected]);
    }

    /**
     * Tabel perbandingan per layanan.
     *
     * @return array{
     *     services: \Illuminate\Support\Collection<int, Service>,
     *     rows: list<array{label: string, values: list<string>, highlight: ?int}>,
     *     available: \Illuminate\Support\Collection<int, Service>
     * }
     */
    private function buildComparison(): array
    {
        $services = Service::query()
            ->whereIn('id', $this->selected)
            ->withCatalogRelations()
            ->get()
            ->sortBy(fn (Service $service) => (int) array_search($service->id, $this->selected, true))
            ->values();

        $cheapestRow = $services->isNotEmpty()
            ? $services->map(fn (Service $s) => $s->lowestPrice())->filter()->min()
            : null;

        $rows = [];

        if ($services->isNotEmpty()) {
            $lowestPrices = $services->map(fn (Service $s) => $s->lowestPrice())->all();

            // array_search longgar dipakai karena nilai termurah dapat berupa int
            // maupun float tergantung hasil agregasi.
            $cheapestColumn = $cheapestRow === null ? null : array_search($cheapestRow, $lowestPrices);

            $rows[] = [
                'label' => 'Harga termurah',
                'values' => $services->map(fn (Service $s) => $s->lowestPrice() !== null
                    ? 'Rp'.Number::format($s->lowestPrice(), locale: 'id')
                    : '—')->all(),
                'highlight' => $cheapestColumn === false ? null : $cheapestColumn,
            ];

            $rows[] = [
                'label' => 'Jumlah paket',
                'values' => $services->map(fn (Service $s) => (string) $s->plans->where('is_active', true)->count())->all(),
                'highlight' => null,
            ];

            $maxDevices = $services->map(fn (Service $s) => $s->plans->where('is_active', true)->max('max_devices'))->all();
            $bestDevices = max(array_filter($maxDevices)) ?: null;

            $rows[] = [
                'label' => 'Perangkat maksimum',
                'values' => $services->map(fn (Service $s) => (string) ($s->plans->where('is_active', true)->max('max_devices') ?? '—'))->all(),
                'highlight' => $bestDevices === null ? null : (array_search($bestDevices, $maxDevices) ?: null),
            ];

            $maxDuration = $services->map(fn (Service $s) => $s->plans->where('is_active', true)->max('duration_days'))->all();
            $bestDuration = max(array_filter($maxDuration)) ?: null;

            $rows[] = [
                'label' => 'Durasi terpanjang',
                'values' => $services->map(function (Service $s) {
                    $days = $s->plans->where('is_active', true)->max('duration_days');

                    return $days === null ? '—' : $this->durationLabel((int) $days);
                })->all(),
                'highlight' => $bestDuration === null ? null : (array_search($bestDuration, $maxDuration) ?: null),
            ];

            $rows[] = [
                'label' => 'Penyedia',
                'values' => $services->map(fn (Service $s) => $s->provider->name)->all(),
                'highlight' => null,
            ];

            $rows[] = [
                'label' => 'Kategori',
                'values' => $services->map(fn (Service $s) => $s->category->name)->all(),
                'highlight' => null,
            ];

            $rows[] = [
                'label' => 'Rating pengguna',
                'values' => $services->map(fn (Service $s) => $s->averageRating() !== null
                    ? '★ '.$s->averageRating()
                    : 'Belum ada ulasan')->all(),
                'highlight' => null,
            ];

            // Baris fitur: gabungkan seluruh fitur unik dari semua paket aktif.
            $allFeatures = $services
                ->flatMap(fn (Service $s) => $s->plans->flatMap(fn (Plan $p) => $p->features ?? []))
                ->unique()
                ->sort()
                ->values();

            foreach ($allFeatures as $feature) {
                $rows[] = [
                    'label' => $feature,
                    'values' => $services->map(function (Service $s) use ($feature) {
                        $has = $s->plans->contains(fn (Plan $p) => in_array($feature, $p->features ?? [], true));

                        return $has ? '✓' : '—';
                    })->all(),
                    'highlight' => null,
                ];
            }
        }

        $available = Service::query()
            ->active()
            ->withCatalogRelations()
            ->when($this->selected !== [], fn ($q) => $q->whereNotIn('id', $this->selected))
            ->orderBy('name')
            ->limit(12)
            ->get();

        return [
            'services' => $services,
            'rows' => $rows,
            'available' => $available,
        ];
    }

    /**
     * Label durasi sederhana untuk tabel perbandingan.
     */
    private function durationLabel(int $days): string
    {
        if ($days >= 360) {
            return round($days / 30).' Bulan';
        }

        if ($days % 30 === 0) {
            return intdiv($days, 30).' Bulan';
        }

        return $days.' Hari';
    }

    public function render(): View
    {
        $comparison = $this->buildComparison();

        return view('catalog::livewire.comparison', [
            'services' => $comparison['services'],
            'rows' => $comparison['rows'],
            'available' => $comparison['available'],
        ]);
    }
}
