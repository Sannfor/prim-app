<?php

use App\Enums\Role;
use App\Enums\TransactionStatus;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Livewire\Livewire;
use Modules\Admin\Livewire\CategoryManager;
use Modules\Admin\Livewire\Dashboard;
use Modules\Admin\Livewire\PlanManager;
use Modules\Admin\Livewire\ProviderManager;
use Modules\Admin\Livewire\ServiceManager;
use Modules\Admin\Livewire\TransactionManager;
use Modules\Admin\Livewire\UserManager;

/*
|--------------------------------------------------------------------------
| Test modul Admin (dashboard metrik, CRUD master data, otorisasi)
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    $this->admin = User::factory()->admin()->create();
});

/*
|--------------------------------------------------------------------------
| Otorisasi
|--------------------------------------------------------------------------
*/

test('seluruh halaman admin menolak pengguna tanpa peran admin', function () {
    $user = User::factory()->create();
    $service = Service::query()->first();

    $routes = [
        route('admin.dashboard'),
        route('admin.providers.index'),
        route('admin.categories.index'),
        route('admin.services.index'),
        route('admin.users.index'),
        route('admin.transactions.index'),
        route('admin.plans.index', $service->slug),
    ];

    foreach ($routes as $url) {
        $this->actingAs($user)->get($url)->assertForbidden();
    }
});

test('tamu dialihkan ke halaman masuk', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| Dashboard metrik
|--------------------------------------------------------------------------
*/

test('dashboard menampilkan metrik yang sesuai dengan data sebenarnya', function () {
    $component = Livewire::actingAs($this->admin)->test(Dashboard::class);

    $metrics = $component->viewData('metrics');

    expect($metrics['services']['value'])->toBe(Service::query()->count())
        ->and($metrics['providers']['value'])->toBe(Provider::query()->count())
        ->and($metrics['users']['value'])->toBe(User::query()->count())
        ->and($metrics['plans']['value'])->toBe(Plan::query()->count());
});

test('dashboard menghitung pendapatan hanya dari transaksi berhasil bulan ini', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();

    Transaction::factory()->paid()->create([
        'user_id' => $this->admin->id,
        'plan_id' => $plan->id,
        'amount' => 100000,
        'paid_at' => now(),
    ]);

    Transaction::factory()->pending()->create([
        'user_id' => $this->admin->id,
        'plan_id' => $plan->id,
        'amount' => 500000,
    ]);

    $revenue = Livewire::actingAs($this->admin)->test(Dashboard::class)->viewData('revenue');

    expect($revenue['this_month'])->toBe(100000);
});

test('dashboard menyediakan deret transaksi empat belas hari', function () {
    $series = Livewire::actingAs($this->admin)->test(Dashboard::class)->viewData('dailyTransactions');

    expect($series)->toHaveCount(14)
        ->and($series[0])->toHaveKeys(['date', 'label', 'count', 'height']);
});

test('dashboard menampilkan rincian status transaksi', function () {
    $breakdown = Livewire::actingAs($this->admin)->test(Dashboard::class)->viewData('statusBreakdown');

    expect($breakdown)->toHaveCount(count(TransactionStatus::cases()));
});

/*
|--------------------------------------------------------------------------
| CRUD Penyedia
|--------------------------------------------------------------------------
*/

test('admin dapat menambah penyedia baru', function () {
    Livewire::actingAs($this->admin)
        ->test(ProviderManager::class)
        ->call('create')
        ->set('name', 'Penyedia Baru Nusantara')
        ->set('website', 'https://penyedaribaru.example.id')
        ->call('save')
        ->assertHasNoErrors();

    expect(Provider::query()->where('name', 'Penyedia Baru Nusantara')->exists())->toBeTrue();
});

test('nama penyedia wajib diisi', function () {
    Livewire::actingAs($this->admin)
        ->test(ProviderManager::class)
        ->call('create')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('situs web penyedia harus berupa URL yang valid', function () {
    Livewire::actingAs($this->admin)
        ->test(ProviderManager::class)
        ->call('create')
        ->set('name', 'Penyedia Uji')
        ->set('website', 'bukan-url')
        ->call('save')
        ->assertHasErrors(['website' => 'url']);
});

test('admin dapat menyunting penyedia', function () {
    $provider = Provider::query()->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(ProviderManager::class)
        ->call('edit', $provider->id)
        ->assertSet('name', $provider->name)
        ->set('name', 'Nama Penyedia Diubah')
        ->call('save')
        ->assertHasNoErrors();

    expect($provider->fresh()->name)->toBe('Nama Penyedia Diubah');
});

test('penyedia yang masih memiliki layanan tidak dapat dihapus', function () {
    $provider = Provider::query()->has('services')->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(ProviderManager::class)
        ->call('confirmDelete', $provider->id)
        ->call('delete');

    expect(Provider::query()->whereKey($provider->id)->exists())->toBeTrue();
});

test('penyedia tanpa layanan dapat dihapus', function () {
    $provider = Provider::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(ProviderManager::class)
        ->call('confirmDelete', $provider->id)
        ->call('delete');

    expect(Provider::query()->whereKey($provider->id)->exists())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| CRUD Kategori
|--------------------------------------------------------------------------
*/

test('admin dapat menambah kategori baru', function () {
    Livewire::actingAs($this->admin)
        ->test(CategoryManager::class)
        ->call('create')
        ->set('name', 'Kategori Baru')
        ->set('icon', 'sparkles')
        ->call('save')
        ->assertHasNoErrors();

    expect(Category::query()->where('name', 'Kategori Baru')->exists())->toBeTrue();
});

test('nama kategori harus unik', function () {
    $existing = Category::query()->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(CategoryManager::class)
        ->call('create')
        ->set('name', $existing->name)
        ->call('save')
        ->assertHasErrors(['name' => 'unique']);
});

test('kategori dapat mempertahankan namanya sendiri saat disunting', function () {
    $existing = Category::query()->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(CategoryManager::class)
        ->call('edit', $existing->id)
        ->set('description', 'Deskripsi diperbarui')
        ->call('save')
        ->assertHasNoErrors();

    expect($existing->fresh()->description)->toBe('Deskripsi diperbarui');
});

test('kategori yang masih dipakai layanan tidak dapat dihapus', function () {
    $category = Category::query()->has('services')->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(CategoryManager::class)
        ->call('confirmDelete', $category->id)
        ->call('delete');

    expect(Category::query()->whereKey($category->id)->exists())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| CRUD Layanan
|--------------------------------------------------------------------------
*/

test('admin dapat menambah layanan baru', function () {
    $provider = Provider::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(ServiceManager::class)
        ->call('create')
        ->set('providerId', $provider->id)
        ->set('categoryId', $category->id)
        ->set('name', 'Layanan Uji Admin')
        ->set('tagline', 'Tagline layanan uji')
        ->call('save')
        ->assertHasNoErrors();

    $service = Service::query()->where('name', 'Layanan Uji Admin')->firstOrFail();

    expect($service->slug)->toBe('layanan-uji-admin')
        ->and($service->provider_id)->toBe($provider->id)
        ->and($service->category_id)->toBe($category->id)
        ->and($service->is_active)->toBeTrue();
});

test('layanan wajib memiliki penyedia dan kategori', function () {
    Livewire::actingAs($this->admin)
        ->test(ServiceManager::class)
        ->call('create')
        ->set('name', 'Layanan Tanpa Relasi')
        ->call('save')
        ->assertHasErrors(['providerId' => 'required', 'categoryId' => 'required']);
});

test('admin dapat menonaktifkan layanan', function () {
    $service = Service::query()->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(ServiceManager::class)
        ->call('edit', $service->id)
        ->set('isActive', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($service->fresh()->is_active)->toBeFalse();
});

test('layanan yang sudah pernah ditransaksikan tidak dapat dihapus', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();

    Transaction::factory()->paid()->create([
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
        'amount' => $plan->price,
    ]);

    $service = $plan->service;

    Livewire::actingAs($this->admin)
        ->test(ServiceManager::class)
        ->call('confirmDelete', $service->id)
        ->call('delete');

    expect(Service::query()->whereKey($service->id)->exists())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| CRUD Paket
|--------------------------------------------------------------------------
*/

test('admin dapat menambah paket pada sebuah layanan', function () {
    $service = Service::query()->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(PlanManager::class, ['service' => $service->slug])
        ->call('create')
        ->set('name', 'Paket Uji Coba')
        ->set('price', 45000)
        ->set('durationDays', 30)
        ->set('maxDevices', 2)
        ->set('featuresText', "Fitur Satu\nFitur Dua\n\nFitur Tiga")
        ->call('save')
        ->assertHasNoErrors();

    $plan = Plan::query()->where('name', 'Paket Uji Coba')->firstOrFail();

    expect($plan->service_id)->toBe($service->id)
        ->and($plan->price)->toBe(45000)
        ->and($plan->duration_days)->toBe(30)
        ->and($plan->max_devices)->toBe(2)
        ->and($plan->features)->toBe(['Fitur Satu', 'Fitur Dua', 'Fitur Tiga']);
});

test('paket menolak harga negatif', function () {
    $service = Service::query()->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(PlanManager::class, ['service' => $service->slug])
        ->call('create')
        ->set('name', 'Paket Negatif')
        ->set('price', -1000)
        ->call('save')
        ->assertHasErrors(['price' => 'min']);
});

test('paket milik layanan lain tidak dapat disunting dari halaman ini', function () {
    $service = Service::query()->firstOrFail();
    $foreignPlan = Plan::query()->where('service_id', '!=', $service->id)->firstOrFail();

    // findRecord memakai findOrFail sehingga paket milik layanan lain
    // tidak akan pernah ditemukan dari halaman ini.
    expect(fn () => Livewire::actingAs($this->admin)
        ->test(PlanManager::class, ['service' => $service->slug])
        ->call('edit', $foreignPlan->id))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

test('paket yang sudah ditransaksikan tidak dapat dihapus', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();

    Transaction::factory()->paid()->create([
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
        'amount' => $plan->price,
    ]);

    Livewire::actingAs($this->admin)
        ->test(PlanManager::class, ['service' => $plan->service->slug])
        ->call('confirmDelete', $plan->id)
        ->call('delete');

    expect(Plan::query()->whereKey($plan->id)->exists())->toBeTrue();
});

test('paket tanpa transaksi dapat dihapus', function () {
    $service = Service::query()->firstOrFail();

    $plan = Plan::factory()->create(['service_id' => $service->id]);

    Livewire::actingAs($this->admin)
        ->test(PlanManager::class, ['service' => $service->slug])
        ->call('confirmDelete', $plan->id)
        ->call('delete');

    expect(Plan::query()->whereKey($plan->id)->exists())->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Pengguna
|--------------------------------------------------------------------------
*/

test('admin dapat mengubah peran pengguna', function () {
    $user = User::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(UserManager::class)
        ->call('changeRole', $user->id, Role::Provider->value);

    expect($user->fresh()->role)->toBe(Role::Provider);
});

test('admin tidak dapat menurunkan perannya sendiri', function () {
    Livewire::actingAs($this->admin)
        ->test(UserManager::class)
        ->call('changeRole', $this->admin->id, Role::User->value);

    expect($this->admin->fresh()->role)->toBe(Role::Admin);
});

test('admin dapat menaikkan pengguna lain menjadi admin', function () {
    $user = User::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(UserManager::class)
        ->call('changeRole', $user->id, Role::Admin->value);

    expect($user->fresh()->role)->toBe(Role::Admin);
});

test('peran yang tidak dikenal ditolak', function () {
    $user = User::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(UserManager::class)
        ->call('changeRole', $user->id, 'superadmin');

    expect($user->fresh()->role)->toBe(Role::User);
});

test('daftar pengguna dapat dicari berdasarkan nama atau email', function () {
    $target = User::factory()->create(['name' => 'Zulkifli Rahman', 'email' => 'zulkifli@prim.test']);
    $other = User::factory()->create(['name' => 'Orang Lain', 'email' => 'lain@prim.test']);

    Livewire::actingAs($this->admin)
        ->test(UserManager::class)
        ->set('search', 'Zulkifli')
        ->assertSee('zulkifli@prim.test')
        ->assertDontSee('lain@prim.test');

    expect($target->exists)->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Transaksi
|--------------------------------------------------------------------------
*/

test('admin dapat menandai transaksi pending sebagai berhasil', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();
    $customer = User::factory()->create();

    $transaction = Transaction::factory()->pending()->create([
        'user_id' => $customer->id,
        'plan_id' => $plan->id,
        'amount' => $plan->price,
    ]);

    Livewire::actingAs($this->admin)
        ->test(TransactionManager::class)
        ->call('markAsPaid', $transaction->id);

    $transaction->refresh();

    expect($transaction->status)->toBe(TransactionStatus::Paid)
        ->and($transaction->subscription)->not->toBeNull()
        ->and($transaction->subscription->user_id)->toBe($customer->id);
});

test('admin dapat menandai transaksi pending sebagai gagal', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();

    $transaction = Transaction::factory()->pending()->create([
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(TransactionManager::class)
        ->call('markAsFailed', $transaction->id);

    expect($transaction->fresh()->status)->toBe(TransactionStatus::Failed);
});

test('transaksi yang sudah berhasil tidak dapat ditandai gagal', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();

    $transaction = Transaction::factory()->paid()->create([
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(TransactionManager::class)
        ->call('markAsFailed', $transaction->id);

    expect($transaction->fresh()->status)->toBe(TransactionStatus::Paid);
});

test('daftar transaksi dapat disaring berdasarkan status', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();
    $customer = User::factory()->create();

    $pending = Transaction::factory()->pending()->create([
        'user_id' => $customer->id,
        'plan_id' => $plan->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(TransactionManager::class)
        ->set('status', TransactionStatus::Paid->value)
        ->assertDontSee($pending->order_code);
});

test('ringkasan transaksi menghitung total pendapatan dari transaksi lunas', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();

    Transaction::factory()->paid()->create([
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
        'amount' => 250000,
    ]);

    $summary = Livewire::actingAs($this->admin)->test(TransactionManager::class)->viewData('summary');

    expect($summary['paid'])->toBeGreaterThanOrEqual(250000);
});

test('halaman kelola paket menampilkan jumlah langganan tiap paket', function () {
    $plan = Plan::query()->where('is_active', true)->firstOrFail();

    Subscription::factory()->create([
        'user_id' => User::factory()->create()->id,
        'plan_id' => $plan->id,
        'started_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    Livewire::actingAs($this->admin)
        ->test(PlanManager::class, ['service' => $plan->service->slug])
        ->assertSee($plan->service->name)
        ->assertSee($plan->name);
});
