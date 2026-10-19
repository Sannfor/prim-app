<?php

namespace Modules\Catalog\Livewire;

use App\Enums\TransactionStatus;
use App\Models\Review;
use App\Models\Service;
use App\Models\Transaction;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman detail sebuah layanan premium beserta seluruh paketnya.
 *
 * Bersifat publik. Tombol pembelian mengarahkan pengunjung ke modul
 * Transaction, yang akan meminta login terlebih dahulu bila belum masuk.
 *
 * Halaman ini juga menjadi tempat Pelanggan menulis ulasan. Penulisan ulasan
 * dibatasi pada Pelanggan yang benar-benar sudah membeli layanan ini, sesuai
 * prasyarat pada dokumen SRS.
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
     * Nilai rating yang dipilih pada formulir ulasan (1 sampai 5).
     */
    public int $ratingUlasan = 5;

    /**
     * Isi ulasan yang sedang ditulis.
     */
    public string $komentarUlasan = '';

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

    /**
     * Apakah pengguna yang sedang masuk sudah menulis ulasan untuk layanan ini.
     */
    public function sudahMengulas(): bool
    {
        $id = auth()->id();

        if ($id === null) {
            return false;
        }

        return Review::query()
            ->where('user_id', $id)
            ->where('service_id', $this->service()->id)
            ->exists();
    }

    /**
     * Apakah pengguna berhak menulis ulasan pada layanan ini.
     *
     * Syaratnya: sudah masuk, belum pernah mengulas layanan ini, dan memiliki
     * minimal satu transaksi berhasil atas layanan ini. Aturan ini menjaga agar
     * rating katalog hanya berasal dari pembeli yang sebenarnya.
     */
    public function bolehMengulas(): bool
    {
        $id = auth()->id();

        if ($id === null || $this->sudahMengulas()) {
            return false;
        }

        return Transaction::query()
            ->where('user_id', $id)
            ->whereIn('status', [TransactionStatus::Paid->value, TransactionStatus::Accepted->value])
            ->whereHas('plan', fn ($q) => $q->where('service_id', $this->service()->id))
            ->exists();
    }

    /**
     * Simpan ulasan baru beserta nilainya.
     */
    public function simpanUlasan(): void
    {
        $this->validate(
            [
                'ratingUlasan' => ['required', 'integer', 'min:1', 'max:5'],
                'komentarUlasan' => ['nullable', 'string', 'max:1000'],
            ],
            attributes: [
                'ratingUlasan' => 'rating',
                'komentarUlasan' => 'komentar',
            ],
        );

        // Periksa ulang setelah validasi: form dapat terkirim basi atau dari
        // peramban lain yang tidak memuat tombolnya.
        abort_unless($this->bolehMengulas(), 403);

        Review::query()->create([
            'user_id' => auth()->id(),
            'service_id' => $this->service()->id,
            'rating' => $this->ratingUlasan,
            'comment' => $this->komentarUlasan !== '' ? $this->komentarUlasan : null,
        ]);

        $this->reset('komentarUlasan', 'ratingUlasan');
        $this->ratingUlasan = 5;

        Flux::toast(variant: 'success', text: 'Terima kasih, ulasan Anda sudah tersimpan.');
    }

    public function render(): View
    {
        $service = $this->service();

        return view('catalog::livewire.service-detail', [
            'service' => $service,
            'isCompared' => $this->isCompared(),
            'plans' => $service->plans->where('is_active', true)->sortBy('price'),
            'rating' => $service->averageRating(),
            'bolehMengulas' => $this->bolehMengulas(),
            'sudahMengulas' => $this->sudahMengulas(),
        ])->title($service->name);
    }
}
