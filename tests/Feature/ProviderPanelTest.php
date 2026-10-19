<?php

use App\Enums\Role;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Modules\Admin\Livewire\Provider\Dashboard;
use Modules\Admin\Livewire\Provider\PlanManager;
use Modules\Admin\Livewire\Provider\ServiceManager;

/*
|--------------------------------------------------------------------------
| Test panel penyedia (peran provider)
|--------------------------------------------------------------------------
|
| Panel penyedia adalah panel sempit: akun berperan provider hanya boleh
| melihat dan menyunting layanan serta paket milik penyedia yang ditautkan
| ke akunnya. Berkas ini menguji otorisasi peran dan batas kepemilikan itu.
|
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    // Penyedia yang diwakili akun penyedia pada pengujian ini.
    $this->penyedia = Provider::query()->whereHas('services')->orderBy('id')->firstOrFail();
    $this->akunPenyedia = User::factory()->provider()->create();
    $this->penyedia->forceFill(['user_id' => $this->akunPenyedia->id])->save();

    // Penyedia lain, dipakai untuk menguji batas kepemilikan.
    $this->penyediaLain = Provider::query()
        ->whereKeyNot($this->penyedia->id)
        ->whereHas('services')
        ->orderBy('id')
        ->firstOrFail();

    $this->layananSendiri = $this->penyedia->services()->orderBy('id')->firstOrFail();
    $this->layananLain = $this->penyediaLain->services()->orderBy('id')->firstOrFail();
});

/*
|--------------------------------------------------------------------------
| Otorisasi peran
|--------------------------------------------------------------------------
*/

test('tamu dialihkan ke halaman masuk', function () {
    $this->get(route('provider.dashboard'))->assertRedirect(route('login'));
    $this->get(route('provider.services.index'))->assertRedirect(route('login'));
});

test('halaman penyedia menolak pengguna biasa dan administrator', function () {
    $pelanggan = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $routes = [
        route('provider.dashboard'),
        route('provider.services.index'),
        route('provider.plans.index', $this->layananSendiri->slug),
    ];

    foreach ($routes as $url) {
        $this->actingAs($pelanggan)->get($url)->assertForbidden();
        $this->actingAs($admin)->get($url)->assertForbidden();
    }
});

test('panel pengelola menolak akun penyedia', function () {
    $this->actingAs($this->akunPenyedia)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('penyedia tanpa data penyedia tertaut ditolak', function () {
    $tanpaTautan = User::factory()->provider()->create();

    $this->actingAs($tanpaTautan)
        ->get(route('provider.dashboard'))
        ->assertForbidden();
});

test('penyedia dapat membuka panelnya sendiri', function () {
    $this->actingAs($this->akunPenyedia)
        ->get(route('provider.dashboard'))
        ->assertOk()
        ->assertSee($this->penyedia->name);

    $this->actingAs($this->akunPenyedia)
        ->get(route('provider.services.index'))
        ->assertOk();

    $this->actingAs($this->akunPenyedia)
        ->get(route('provider.plans.index', $this->layananSendiri->slug))
        ->assertOk();
});

/*
|--------------------------------------------------------------------------
| Batas kepemilikan layanan
|--------------------------------------------------------------------------
*/

test('penyedia hanya melihat layanan miliknya pada tab daftar', function () {
    Livewire::actingAs($this->akunPenyedia)
        ->test(ServiceManager::class)
        ->assertViewHas('services', function ($services) {
            return $services->pluck('id')->all() === $this->penyedia->services()->pluck('id')->all();
        });
});

test('penyedia tidak dapat menyunting layanan penyedia lain', function () {
    expect(fn () => Livewire::actingAs($this->akunPenyedia)
        ->test(ServiceManager::class)
        ->call('edit', $this->layananLain->id))
        ->toThrow(ModelNotFoundException::class);
});

test('penyedia tidak dapat menghapus layanan penyedia lain', function () {
    expect(fn () => Livewire::actingAs($this->akunPenyedia)
        ->test(ServiceManager::class)
        ->call('confirmDelete', $this->layananLain->id)
        ->call('delete'))
        ->toThrow(ModelNotFoundException::class);
});

test('layanan baru selalu tercatat atas nama penyedia yang masuk', function () {
    $kategori = Category::query()->firstOrFail();

    Livewire::actingAs($this->akunPenyedia)
        ->test(ServiceManager::class)
        ->call('create')
        ->set('name', 'Layanan Uji Penyedia')
        ->set('categoryId', $kategori->id)
        ->set('isActive', true)
        ->call('save')
        ->assertHasNoErrors();

    $baru = Service::query()->where('name', 'Layanan Uji Penyedia')->firstOrFail();

    expect($baru->provider_id)->toBe($this->penyedia->id);
});

test('penyedia tidak dapat memindahkan layanannya ke penyedia lain', function () {
    $kategori = Category::query()->firstOrFail();

    /*
     * Formulir panel penyedia tidak memiliki kolom penyedia. Meskipun begitu,
     * pengujian ini memastikan penyedia tetap diambil dari akun yang masuk
     * walaupun ada masukan tambahan pada komponen.
     */
    Livewire::actingAs($this->akunPenyedia)
        ->test(ServiceManager::class)
        ->call('edit', $this->layananSendiri->id)
        ->set('categoryId', $kategori->id)
        ->call('save')
        ->assertHasNoErrors();

    expect($this->layananSendiri->fresh()->provider_id)->toBe($this->penyedia->id);
});

/*
|--------------------------------------------------------------------------
| Batas kepemilikan paket
|--------------------------------------------------------------------------
*/

test('penyedia tidak dapat membuka paket layanan penyedia lain', function () {
    expect(fn () => Livewire::actingAs($this->akunPenyedia)
        ->test(PlanManager::class, ['service' => $this->layananLain->slug]))
        ->toThrow(ModelNotFoundException::class);
});

test('penyedia dapat menambah paket pada layanannya sendiri', function () {
    Livewire::actingAs($this->akunPenyedia)
        ->test(PlanManager::class, ['service' => $this->layananSendiri->slug])
        ->call('create')
        ->set('name', 'Paket Uji Penyedia')
        ->set('price', 123000)
        ->set('durationDays', 90)
        ->set('maxDevices', 2)
        ->call('save')
        ->assertHasNoErrors();

    $paket = $this->layananSendiri->plans()->where('name', 'Paket Uji Penyedia')->firstOrFail();

    expect($paket->price)->toBe(123000)
        ->and($paket->service_id)->toBe($this->layananSendiri->id);
});

test('seluruh paket yang tampil hanya milik layanan penyedia itu', function () {
    Livewire::actingAs($this->akunPenyedia)
        ->test(PlanManager::class, ['service' => $this->layananSendiri->slug])
        ->assertViewHas('plans', function ($plans) {
            return $plans->pluck('service_id')->unique()->all() === [$this->layananSendiri->id];
        });
});
