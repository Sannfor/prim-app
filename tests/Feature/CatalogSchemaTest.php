<?php

use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CatalogSeeder;

/*
|--------------------------------------------------------------------------
| Test skema katalog & model bisnis PRIM
|--------------------------------------------------------------------------
*/

test('seeder katalog mengisi layanan beserta paketnya', function () {
    $this->seed(CatalogSeeder::class);

    expect(Provider::count())->toBeGreaterThan(0)
        ->and(Service::count())->toBeGreaterThan(0)
        ->and(Plan::count())->toBeGreaterThan(0);

    $service = Service::query()->with('plans')->first();

    expect($service->plans)->not->toBeEmpty();
});

test('service otomatis membuat slug dari nama', function () {
    $service = Service::factory()->create(['name' => 'Layanan Uji Coba', 'slug' => null]);

    expect($service->fresh()->slug)->toBe('layanan-uji-coba');
});

test('scope active hanya mengambil layanan yang aktif', function () {
    Service::factory()->create(['name' => 'Aktif', 'is_active' => true]);
    Service::factory()->create(['name' => 'Nonaktif', 'is_active' => false]);

    expect(Service::query()->active()->pluck('name')->all())->toBe(['Aktif']);
});

test('plan memformat harga dan menghitung harga per bulan', function () {
    $plan = Plan::factory()->create([
        'price' => 120000,
        'duration_days' => 60,
    ]);

    expect($plan->formattedPrice())->toBe('Rp120.000')
        ->and($plan->monthlyPrice())->toBe(60000)
        ->and($plan->durationLabel())->toBe('2 Bulan');
});

test('plan dengan durasi tahunan memakai label bulan', function () {
    $plan = Plan::factory()->create(['duration_days' => 365]);

    expect($plan->durationLabel())->toBe('12 Bulan');
});

test('transaksi membuat kode pesanan berformat PRIM dan unik', function () {
    $first = Transaction::factory()->pending()->create();
    $second = Transaction::factory()->pending()->create();

    expect($first->order_code)->toStartWith('PRIM-')
        ->and($second->order_code)->not->toBe($first->order_code);
});

test('transaksi pending masih dapat dibayar', function () {
    $transaction = Transaction::factory()->pending()->create([
        'expires_at' => now()->addHours(5),
    ]);

    expect($transaction->isPayable())->toBeTrue()
        ->and($transaction->hasExpired())->toBeFalse()
        ->and($transaction->status)->toBe(TransactionStatus::Pending);
});

test('transaksi pending yang lewat batas waktu dianggap kedaluwarsa', function () {
    $transaction = Transaction::factory()->pending()->create([
        'expires_at' => now()->subHour(),
    ]);

    expect($transaction->hasExpired())->toBeTrue()
        ->and($transaction->isPayable())->toBeFalse();
});

test('transaksi yang sudah dibayar tidak dapat dibayar ulang', function () {
    $transaction = Transaction::factory()->paid()->create();

    expect($transaction->isPayable())->toBeFalse()
        ->and($transaction->status->isSuccessful())->toBeTrue();
});

test('scope awaitingPayment hanya mengambil pesanan yang belum lewat waktu', function () {
    $valid = Transaction::factory()->pending()->create(['expires_at' => now()->addHours(3)]);
    Transaction::factory()->pending()->create(['expires_at' => now()->subHour()]);
    Transaction::factory()->paid()->create();

    $codes = Transaction::query()->awaitingPayment()->pluck('order_code')->all();

    expect($codes)->toBe([$valid->order_code]);
});

test('langganan menghitung sisa hari dan progres masa aktif', function () {
    $subscription = Subscription::factory()->create([
        'started_at' => now()->subDays(10),
        'ends_at' => now()->addDays(20),
        'status' => SubscriptionStatus::Active,
    ]);

    expect($subscription->isActive())->toBeTrue()
        ->and($subscription->daysRemaining())->toBe(20)
        ->and($subscription->progressPercentage())->toBeBetween(25, 40);
});

test('langganan yang tinggal sedikit hari terdeteksi akan berakhir', function () {
    $subscription = Subscription::factory()->expiringSoon()->create();

    expect($subscription->isExpiringSoon())->toBeTrue()
        ->and($subscription->daysRemaining())->toBeLessThanOrEqual(7);
});

test('langganan kedaluwarsa tidak dihitung aktif', function () {
    $subscription = Subscription::factory()->expired()->create();

    expect($subscription->isActive())->toBeFalse()
        ->and($subscription->daysRemaining())->toBe(0)
        ->and(Subscription::query()->active()->whereKey($subscription->id)->exists())->toBeFalse();
});

test('relasi transaksi ke pengguna, paket, dan langganan berjalan', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $transaction = Transaction::factory()->paid()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
    ]);

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'transaction_id' => $transaction->id,
    ]);

    expect($transaction->fresh()->user->id)->toBe($user->id)
        ->and($transaction->fresh()->plan->id)->toBe($plan->id)
        ->and($transaction->fresh()->subscription->id)->toBe($subscription->id)
        ->and($user->fresh()->transactions)->toHaveCount(1)
        ->and($user->fresh()->subscriptions)->toHaveCount(1);
});

test('service dapat menghitung harga terendah dari paket aktifnya', function () {
    $service = Service::factory()->create();

    Plan::factory()->create(['service_id' => $service->id, 'price' => 90000, 'is_active' => true]);
    Plan::factory()->create(['service_id' => $service->id, 'price' => 30000, 'is_active' => true]);
    Plan::factory()->create(['service_id' => $service->id, 'price' => 10000, 'is_active' => false]);

    expect($service->fresh()->lowestPrice())->toBe(30000);
});

test('pengguna dengan peran provider dapat dikenali', function () {
    expect(User::factory()->provider()->create()->isProvider())->toBeTrue()
        ->and(User::factory()->create()->isAdmin())->toBeFalse()
        ->and(User::factory()->admin()->create()->role)->toBe(Role::Admin);
});
