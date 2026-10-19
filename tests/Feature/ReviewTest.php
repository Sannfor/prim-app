<?php

use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Review;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Livewire\Livewire;
use Modules\Catalog\Livewire\ServiceDetail;

/*
|--------------------------------------------------------------------------
| Test penulisan ulasan pada halaman detail layanan
|--------------------------------------------------------------------------
|
| Dokumen SRS memuat use case "Menulis ulasan dan rating" dengan prasyarat
| Pelanggan memiliki transaksi berbayar atas layanan yang akan diulas. Berkas
| ini menguji aturan tersebut beserta penolakan untuk pengguna lain.
|
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    $this->layanan = Service::query()->whereHas('plans')->firstOrFail();
    $this->paket = $this->layanan->plans()->where('is_active', true)->firstOrFail();
    $this->pelanggan = User::factory()->create();
});

/** Buat transaksi berhasil atas layanan pada pengujian ini. */
function transaksiLunas(User $user, Plan $plan): Transaction
{
    return Transaction::factory()->paid()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => $plan->price,
    ]);
}

test('pengunjung tanpa transaksi tidak melihat formulir ulasan', function () {
    Livewire::test(ServiceDetail::class, ['service' => $this->layanan->slug])
        ->assertOk()
        ->assertDontSee('Tulis Ulasan')
        ->assertDontSee('Kirim Ulasan');
});

test('pembeli layanan dapat menulis ulasan beserta ratingnya', function () {
    transaksiLunas($this->pelanggan, $this->paket);

    Livewire::actingAs($this->pelanggan)
        ->test(ServiceDetail::class, ['service' => $this->layanan->slug])
        ->assertSee('Tulis Ulasan')
        ->set('ratingUlasan', 4)
        ->set('komentarUlasan', 'Kualitasnya bagus dan prosesnya cepat.')
        ->call('simpanUlasan')
        ->assertHasNoErrors();

    $ulasan = Review::query()
        ->where('user_id', $this->pelanggan->id)
        ->where('service_id', $this->layanan->id)
        ->firstOrFail();

    expect($ulasan->rating)->toBe(4)
        ->and($ulasan->comment)->toBe('Kualitasnya bagus dan prosesnya cepat.');
});

test('ulasan yang tersimpan tampil pada daftar ulasan layanan', function () {
    transaksiLunas($this->pelanggan, $this->paket);

    Review::query()->create([
        'user_id' => $this->pelanggan->id,
        'service_id' => $this->layanan->id,
        'rating' => 5,
        'comment' => 'Sangat memuaskan.',
    ]);

    $this->get(route('catalog.show', $this->layanan->slug))
        ->assertOk()
        ->assertSee('Ulasan pengguna')
        ->assertSee('Sangat memuaskan.');
});

test('pengguna tanpa transaksi berhasil ditolak saat mengirim ulasan', function () {
    Livewire::actingAs($this->pelanggan)
        ->test(ServiceDetail::class, ['service' => $this->layanan->slug])
        ->set('ratingUlasan', 5)
        ->call('simpanUlasan')
        ->assertForbidden();

    expect(Review::query()->count())->toBe(0);
});

test('transaksi yang belum lunas tidak memberi hak mengulas', function () {
    Transaction::factory()->pending()->create([
        'user_id' => $this->pelanggan->id,
        'plan_id' => $this->paket->id,
        'amount' => $this->paket->price,
    ]);

    expect(Review::query()->count())->toBe(0);

    Livewire::actingAs($this->pelanggan)
        ->test(ServiceDetail::class, ['service' => $this->layanan->slug])
        ->assertDontSee('Tulis Ulasan')
        ->assertDontSee('Kirim Ulasan');
});

test('satu pengguna hanya boleh mengulas satu kali per layanan', function () {
    transaksiLunas($this->pelanggan, $this->paket);

    Review::query()->create([
        'user_id' => $this->pelanggan->id,
        'service_id' => $this->layanan->id,
        'rating' => 4,
    ]);

    Livewire::actingAs($this->pelanggan)
        ->test(ServiceDetail::class, ['service' => $this->layanan->slug])
        ->assertSee('sudah menulis ulasan')
        ->assertDontSee('Tulis Ulasan')
        ->set('ratingUlasan', 5)
        ->call('simpanUlasan')
        ->assertForbidden();

    expect(Review::query()->count())->toBe(1);
});

test('rating di luar rentang satu sampai lima ditolak validasi', function () {
    transaksiLunas($this->pelanggan, $this->paket);

    Livewire::actingAs($this->pelanggan)
        ->test(ServiceDetail::class, ['service' => $this->layanan->slug])
        ->set('ratingUlasan', 6)
        ->call('simpanUlasan')
        ->assertHasErrors(['ratingUlasan']);

    expect(Review::query()->count())->toBe(0);
});

test('komentar melebihi seribu karakter ditolak validasi', function () {
    transaksiLunas($this->pelanggan, $this->paket);

    Livewire::actingAs($this->pelanggan)
        ->test(ServiceDetail::class, ['service' => $this->layanan->slug])
        ->set('komentarUlasan', str_repeat('a', 1001))
        ->call('simpanUlasan')
        ->assertHasErrors(['komentarUlasan']);
});

test('transaksi atas layanan lain tidak memberi hak mengulas layanan ini', function () {
    $layananLain = Service::query()
        ->whereKeyNot($this->layanan->id)
        ->whereHas('plans')
        ->firstOrFail();

    transaksiLunas($this->pelanggan, $layananLain->plans()->firstOrFail());

    Livewire::actingAs($this->pelanggan)
        ->test(ServiceDetail::class, ['service' => $this->layanan->slug])
        ->assertDontSee('Tulis Ulasan');
});

