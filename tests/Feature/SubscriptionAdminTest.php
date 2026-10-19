<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Livewire\Livewire;
use Modules\Admin\Livewire\SubscriptionManager;

/*
|--------------------------------------------------------------------------
| Test halaman pemantauan langganan pada panel admin
|--------------------------------------------------------------------------
|
| Dokumen SRS memuat use case "Memonitor seluruh langganan" dengan aktor
| Administrator. Berkas ini menguji halaman yang mewujudkannya, termasuk
| otorisasi, penyaring kondisi, pencarian, dan angka pada kartu ringkasan.
|
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    $this->admin = User::factory()->admin()->create();
    $this->paket = Plan::query()->where('is_active', true)->firstOrFail();
});

/** Buat langganan untuk pengguna baru dengan paket pada pengujian ini. */
function langgananBaru(array $state = [], ?User $user = null): Subscription
{
    return Subscription::factory()
        ->state($state)
        ->create([
            'user_id' => ($user ?? User::factory()->create())->id,
            'plan_id' => test()->paket->id,
        ]);
}

test('tamu dialihkan ke halaman masuk', function () {
    $this->get(route('admin.subscriptions.index'))->assertRedirect(route('login'));
});

test('halaman langganan menolak pelanggan dan penyedia', function () {
    $pelanggan = User::factory()->create();
    $penyedia = User::factory()->provider()->create();

    $this->actingAs($pelanggan)->get(route('admin.subscriptions.index'))->assertForbidden();
    $this->actingAs($penyedia)->get(route('admin.subscriptions.index'))->assertForbidden();
});

test('administrator dapat membuka halaman pemantauan langganan', function () {
    $pemilik = User::factory()->create(['name' => 'Siti Aminah']);

    langgananBaru([], $pemilik);

    $this->actingAs($this->admin)
        ->get(route('admin.subscriptions.index'))
        ->assertOk()
        ->assertSee('Manajemen Langganan')
        ->assertSee('Siti Aminah');
});

test('kartu ringkasan menghitung langganan aktif, akan berakhir, dan berakhir', function () {
    // Masa aktif ditetapkan eksplisit agar hitungan tidak bergantung pada
    // rentang acak pada factory.
    langgananBaru(['ends_at' => now()->addDays(20)]);
    langgananBaru(['ends_at' => now()->addDays(20)]);
    langgananBaru(['ends_at' => now()->addDays(20), 'auto_renew' => true]);
    subscription_expiring();
    subscription_expired();

    $komponen = Livewire::actingAs($this->admin)->test(SubscriptionManager::class);

    $cards = collect($komponen->viewData('cards'))->keyBy('label');

    expect($cards['Langganan Aktif']['value'])->toBe('4')
        ->and($cards['Akan Berakhir']['value'])->toBe('1')
        ->and($cards['Sudah Berakhir']['value'])->toBe('1')
        ->and($cards['Perpanjangan Otomatis']['value'])->toBe('1');
});

test('penyaring aktif hanya menampilkan langganan yang masih berjalan', function () {
    $aktif = langgananBaru(['ends_at' => now()->addDays(20)]);
    subscription_expired();

    Livewire::actingAs($this->admin)
        ->test(SubscriptionManager::class)
        ->set('status', 'aktif')
        ->assertViewHas('subscriptions', function ($page) use ($aktif) {
            return $page->pluck('id')->all() === [$aktif->id];
        });
});

test('penyaring akan berakhir hanya menampilkan langganan tujuh hari ke depan', function () {
    // Langganan lain dibuat jauh dari batas tujuh hari agar hasilnya pasti.
    langgananBaru(['ends_at' => now()->addDays(20)]);
    $segera = subscription_expiring();

    Livewire::actingAs($this->admin)
        ->test(SubscriptionManager::class)
        ->set('status', 'akan-berakhir')
        ->assertViewHas('subscriptions', function ($page) use ($segera) {
            return $page->pluck('id')->all() === [$segera->id];
        });
});

test('penyaring berakhir menampilkan langganan yang sudah lewat masa aktif', function () {
    langgananBaru();
    $habis = subscription_expired();

    Livewire::actingAs($this->admin)
        ->test(SubscriptionManager::class)
        ->set('status', 'berakhir')
        ->assertViewHas('subscriptions', function ($page) use ($habis) {
            return $page->pluck('id')->all() === [$habis->id];
        });
});

test('pencarian menyaring menurut nama atau surel pelanggan', function () {
    $budi = User::factory()->create(['name' => 'Budi Wahyu', 'email' => 'budi@prim.test']);
    $rina = User::factory()->create(['name' => 'Rina Kusuma', 'email' => 'rina@prim.test']);

    $milikBudi = langgananBaru([], $budi);
    langgananBaru([], $rina);

    Livewire::actingAs($this->admin)
        ->test(SubscriptionManager::class)
        ->set('search', 'budi')
        ->assertViewHas('subscriptions', function ($page) use ($milikBudi) {
            return $page->pluck('id')->all() === [$milikBudi->id];
        });
});

test('pencarian juga menyaring menurut nama layanan', function () {
    $namaLayanan = $this->paket->service->name;

    $cocok = langgananBaru();
    langgananBaru();

    $jumlah = Livewire::actingAs($this->admin)
        ->test(SubscriptionManager::class)
        ->set('search', $namaLayanan)
        ->viewData('subscriptions');

    expect($jumlah->total())->toBe(2)
        ->and($jumlah->pluck('id')->all())->toContain($cocok->id);
});

/**
 * Langganan yang tinggal beberapa hari lagi.
 */
function subscription_expiring(): Subscription
{
    return Subscription::factory()->expiringSoon()->create([
        'user_id' => User::factory()->create()->id,
        'plan_id' => test()->paket->id,
    ]);
}

/**
 * Langganan yang masa aktifnya sudah habis.
 */
function subscription_expired(): Subscription
{
    return Subscription::factory()->expired()->create([
        'user_id' => User::factory()->create()->id,
        'plan_id' => test()->paket->id,
    ]);
}
