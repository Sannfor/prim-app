<?php

use App\Models\Category;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Service;
use Database\Seeders\CatalogSeeder;
use Livewire\Livewire;
use Modules\Catalog\Livewire\Comparison;
use Modules\Catalog\Livewire\ServiceDetail;
use Modules\Catalog\Livewire\ServiceList;

/*
|--------------------------------------------------------------------------
| Test modul Catalog (katalog publik, detail, komparasi)
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);
});

test('halaman katalog dapat dibuka tanpa login dan menampilkan layanan aktif', function () {
    $response = $this->get(route('catalog.index'));

    $response->assertOk();

    $active = Service::query()->active()->first();

    $response->assertSee($active->name);
});

test('layanan nonaktif tidak muncul di katalog', function () {
    $hidden = Service::factory()->inactive()->create(['name' => 'Layanan Rahasia']);

    $this->get(route('catalog.index'))
        ->assertOk()
        ->assertDontSee('Layanan Rahasia');

    expect($hidden->is_active)->toBeFalse();
});

test('pencarian menyaring layanan berdasarkan kata kunci', function () {
    Service::factory()->create(['name' => 'Zebra Analytics', 'slug' => 'zebra-analytics', 'tagline' => 'Analitik data']);

    Livewire::test(ServiceList::class)
        ->set('search', 'Zebra')
        ->assertSee('Zebra Analytics')
        ->assertDontSee('Nusantara Film Premium');
});

test('filter kategori hanya menampilkan layanan pada kategori tersebut', function () {
    $edukasi = Category::query()->where('slug', 'edukasi')->firstOrFail();

    Livewire::test(ServiceList::class)
        ->set('category', $edukasi->slug)
        ->assertSee('Cendekia Kelas Online')
        ->assertDontSee('Nusantara Film Premium');
});

test('filter penyedia hanya menampilkan layanan dari penyedia tersebut', function () {
    $sonata = Provider::query()->where('slug', 'sonata')->firstOrFail();

    Livewire::test(ServiceList::class)
        ->set('provider', $sonata->slug)
        ->assertSee('Sonata Music Unlimited')
        ->assertDontSee('Nusantara Film Premium');
});

test('filter harga maksimum menyaring layanan yang terlalu mahal', function () {
    Livewire::test(ServiceList::class)
        ->set('maxPrice', 30000)
        ->assertSee('Sonata Podcast Plus')
        ->assertDontSee('Nusantara Film Premium');
});

test('pengurutan harga termurah menempatkan layanan termurah di depan', function () {
    $component = Livewire::test(ServiceList::class)->set('sort', 'termurah');

    $firstService = $component->viewData('services')->first();

    $minPrice = Service::query()->active()->with('plans')->get()
        ->map(fn (Service $s) => $s->lowestPrice())
        ->filter()
        ->min();

    expect($firstService)->not->toBeNull()
        ->and($firstService->lowestPrice())->toBe($minPrice);
});

test('mengubah filter mengembalikan ke halaman pertama', function () {
    Livewire::test(ServiceList::class)
        ->call('setPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('search', 'Nusantara')
        ->assertSet('paginators.page', 1)
        ->assertOk();
});

test('tombol bandingkan menyimpan pilihan ke session', function () {
    $service = Service::query()->active()->firstOrFail();

    Livewire::test(ServiceList::class)
        ->call('toggleCompare', $service->id);

    expect(session('compare'))->toBe([$service->id]);
});

test('pilihan bandingkan dapat dilepas kembali', function () {
    $service = Service::query()->active()->firstOrFail();

    Livewire::test(ServiceList::class)
        ->call('toggleCompare', $service->id)
        ->call('toggleCompare', $service->id);

    expect(session('compare'))->toBe([]);
});

test('komparasi dibatasi maksimum empat layanan', function () {
    $ids = Service::query()->active()->pluck('id')->all();

    // Seeder menyediakan 6 layanan; ambil 5 untuk menguji batas.
    expect(count($ids))->toBeGreaterThanOrEqual(5);

    $component = Livewire::test(ServiceList::class);

    foreach (array_slice($ids, 0, 5) as $id) {
        $component->call('toggleCompare', $id);
    }

    expect(session('compare'))->toHaveCount(Comparison::MAX_SERVICES);
});

test('reset filter mengembalikan seluruh filter ke nilai awal', function () {
    Livewire::test(ServiceList::class)
        ->set('search', 'film')
        ->set('category', 'hiburan')
        ->set('sort', 'termurah')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('category', '')
        ->assertSet('sort', 'terbaru');
});

test('halaman detail layanan menampilkan seluruh paket aktif', function () {
    $service = Service::query()->where('slug', 'nusantara-film-premium')->firstOrFail();

    $this->get(route('catalog.show', $service->slug))
        ->assertOk()
        ->assertSee($service->name)
        ->assertSee('Basic 1 Bulan')
        ->assertSee('Premium 6 Bulan');
});

test('halaman detail layanan yang tidak ada menghasilkan 404', function () {
    $this->get(route('catalog.show', 'layanan-tidak-ada'))->assertNotFound();
});

test('halaman detail tidak menampilkan paket yang tidak aktif', function () {
    $service = Service::query()->where('slug', 'nusantara-film-premium')->firstOrFail();

    Plan::factory()->inactive()->create([
        'service_id' => $service->id,
        'name' => 'Paket Lama Discontinued',
    ]);

    $this->get(route('catalog.show', $service->slug))
        ->assertOk()
        ->assertDontSee('Paket Lama Discontinued');
});

test('halaman komparasi menampilkan layanan terpilih beserta baris perbandingan', function () {
    $services = Service::query()->active()->take(2)->get();

    $this->withSession(['compare' => $services->pluck('id')->all()])
        ->get(route('catalog.compare'))
        ->assertOk()
        ->assertSee($services[0]->name)
        ->assertSee($services[1]->name)
        ->assertSee('Harga termurah')
        ->assertSee('Perangkat maksimum');
});

test('halaman komparasi menampilkan keadaan kosong tanpa pilihan', function () {
    $this->get(route('catalog.compare'))
        ->assertOk()
        ->assertSee('Belum ada layanan yang dipilih');
});

test('komponen komparasi membatasi penambahan melebihi empat layanan', function () {
    $ids = Service::query()->active()->pluck('id')->take(Comparison::MAX_SERVICES)->values();

    $this->withSession(['compare' => $ids->all()]);

    $extra = Service::query()->active()->whereNotIn('id', $ids->all())->value('id');

    expect($extra)->not->toBeNull();

    $component = Livewire::test(Comparison::class)->call('add', $extra);

    expect($component->get('selected'))->toHaveCount(Comparison::MAX_SERVICES)
        ->and(session('compare'))->not->toContain($extra);
});

test('komponen komparasi dapat melepas layanan terpilih', function () {
    $ids = Service::query()->active()->pluck('id')->take(3)->values();

    // Pilihan awal disimpan di session, karena mount() memang membacanya dari sana.
    $this->withSession(['compare' => $ids->all()]);

    Livewire::test(Comparison::class)
        ->call('remove', $ids[1]);

    $remaining = session('compare');

    sort($remaining);

    expect($remaining)->toBe([$ids[0], $ids[2]]);
});

test('komponen detail layanan dapat menambah dan melepas dari perbandingan', function () {
    $service = Service::query()->where('slug', 'nusantara-film-premium')->firstOrFail();

    Livewire::test(ServiceDetail::class, ['service' => $service->slug])
        ->call('toggleCompare')
        ->assertSet('serviceSlug', $service->slug);

    expect(session('compare'))->toBe([$service->id]);
});
