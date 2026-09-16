<?php

use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Modules\Subscription\Livewire\RenewSubscription;
use Modules\Subscription\Livewire\SubscriptionList;

/*
|--------------------------------------------------------------------------
| Test modul Subscription (daftar langganan, perpanjangan, expire)
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    $this->user = User::factory()->create();
    $this->plan = Plan::query()->where('is_active', true)->firstOrFail();
});

test('halaman langganan memerlukan login', function () {
    $this->get(route('subscription.index'))->assertRedirect(route('login'));
});

test('daftar langganan hanya menampilkan milik pengguna yang masuk', function () {
    $mine = Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'started_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    $other = User::factory()->create();
    Subscription::factory()->create([
        'user_id' => $other->id,
        'plan_id' => $this->plan->id,
        'started_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    $this->actingAs($this->user)
        ->get(route('subscription.index'))
        ->assertOk()
        ->assertSee($mine->plan->service->name);

    expect(Subscription::query()->where('user_id', $this->user->id)->count())->toBe(1);
});

test('daftar langganan menampilkan sisa hari masa aktif', function () {
    Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'started_at' => now()->subDays(10),
        'ends_at' => now()->addDays(20),
    ]);

    $this->actingAs($this->user)
        ->get(route('subscription.index'))
        ->assertOk()
        ->assertSee('20 hari lagi');
});

test('filter aktif menyembunyikan langganan yang sudah berakhir', function () {
    $expired = Subscription::factory()->expired()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(SubscriptionList::class)
        ->set('filter', 'aktif')
        ->assertDontSee($expired->plan->service->name);
});

test('filter kedaluwarsa menampilkan langganan yang sudah berakhir', function () {
    Subscription::factory()->expired()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(SubscriptionList::class)
        ->set('filter', 'kedaluwarsa')
        ->assertSee($this->plan->service->name);
});

test('filter akan berakhir hanya menampilkan langganan dalam tujuh hari', function () {
    Subscription::factory()->expiringSoon()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(SubscriptionList::class)
        ->set('filter', 'berakhir')
        ->assertSee('hari lagi');
});

test('perpanjangan otomatis dapat dinyalakan dan dimatikan', function () {
    $subscription = Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'auto_renew' => false,
    ]);

    Livewire::actingAs($this->user)
        ->test(SubscriptionList::class)
        ->call('toggleAutoRenew', $subscription->id);

    expect($subscription->fresh()->auto_renew)->toBeTrue();

    Livewire::actingAs($this->user)
        ->test(SubscriptionList::class)
        ->call('toggleAutoRenew', $subscription->id);

    expect($subscription->fresh()->auto_renew)->toBeFalse();
});

test('pembatalan otomatis mencatat waktu pembatalan', function () {
    $subscription = Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'auto_renew' => true,
    ]);

    Livewire::actingAs($this->user)
        ->test(SubscriptionList::class)
        ->call('cancelAutoRenew', $subscription->id);

    $subscription->refresh();

    expect($subscription->auto_renew)->toBeFalse()
        ->and($subscription->cancelled_at)->not->toBeNull()
        ->and($subscription->isActive())->toBeTrue();
});

test('pengguna tidak dapat mengubah langganan milik orang lain', function () {
    $other = User::factory()->create();
    $foreign = Subscription::factory()->create([
        'user_id' => $other->id,
        'plan_id' => $this->plan->id,
        'auto_renew' => false,
    ]);

    Livewire::actingAs($this->user)
        ->test(SubscriptionList::class)
        ->call('toggleAutoRenew', $foreign->id)
        ->assertForbidden();

    expect($foreign->fresh()->auto_renew)->toBeFalse();
});

test('halaman perpanjang menampilkan paket layanan yang sama', function () {
    $subscription = Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'started_at' => now(),
        'ends_at' => now()->addDays(25),
    ]);

    $this->actingAs($this->user)
        ->get(route('subscription.renew', $subscription->id))
        ->assertOk()
        ->assertSee('Perpanjang')
        ->assertSee($subscription->plan->service->name)
        ->assertSee('Paket Anda saat ini');
});

test('halaman perpanjang menolak langganan milik pengguna lain', function () {
    $other = User::factory()->create();
    $foreign = Subscription::factory()->create([
        'user_id' => $other->id,
        'plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('subscription.renew', $foreign->id))
        ->assertForbidden();
});

test('komponen perpanjang menyediakan paket aktif layanan tersebut', function () {
    $subscription = Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(RenewSubscription::class, ['subscription' => $subscription->id]);

    $plans = $component->viewData('plans');

    expect($plans->isNotEmpty())->toBeTrue()
        ->and($plans->every(fn ($plan) => $plan->is_active))->toBeTrue()
        ->and($plans->every(fn ($plan) => $plan->service_id === $this->plan->service_id))->toBeTrue();
});

test('langganan yang sudah berakhir ditampilkan sebagai tidak aktif', function () {
    $subscription = Subscription::factory()->expired()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    expect($subscription->isActive())->toBeFalse()
        ->and($subscription->daysRemaining())->toBe(0)
        ->and($subscription->isExpiringSoon())->toBeFalse();
});

test('perintah prim:expire-transactions mengakhiri langganan dan transaksi kedaluwarsa', function () {
    $expiredSubscription = Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'started_at' => now()->subDays(60),
        'ends_at' => now()->subDay(),
    ]);

    $activeSubscription = Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'started_at' => now()->subDays(5),
        'ends_at' => now()->addDays(25),
    ]);

    $overdueTransaction = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'expires_at' => now()->subHours(2),
    ]);

    Artisan::call('prim:expire-transactions');

    expect($expiredSubscription->fresh()->status)->toBe(SubscriptionStatus::Expired)
        ->and($activeSubscription->fresh()->status)->toBe(SubscriptionStatus::Active)
        ->and($overdueTransaction->fresh()->status)->toBe(TransactionStatus::Expired);
});

test('perintah expire melaporkan jumlah data yang diperbarui', function () {
    Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'started_at' => now()->subDays(60),
        'ends_at' => now()->subDay(),
    ]);

    Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'expires_at' => now()->subHour(),
    ]);

    $exitCode = Artisan::call('prim:expire-transactions');
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('Transaksi ditandai kedaluwarsa: 1')
        ->and($output)->toContain('Langganan ditandai berakhir: 1');
});
