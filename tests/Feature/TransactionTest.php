<?php

use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Database\Seeders\CatalogSeeder;
use Livewire\Livewire;
use Modules\Transaction\Livewire\Checkout;
use Modules\Transaction\Livewire\TransactionDetail;
use Modules\Transaction\Livewire\TransactionHistory;

/*
|--------------------------------------------------------------------------
| Test modul Transaction (checkout, pembayaran simulasi, riwayat, otorisasi)
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->seed(CatalogSeeder::class);

    $this->user = User::factory()->create();
    $this->plan = Plan::query()->where('is_active', true)->firstOrFail();
});

test('halaman checkout memerlukan login', function () {
    $this->get(route('transaction.checkout', $this->plan->id))
        ->assertRedirect(route('login'));
});

test('halaman checkout menampilkan ringkasan paket dan metode pembayaran', function () {
    $this->actingAs($this->user)
        ->get(route('transaction.checkout', $this->plan->id))
        ->assertOk()
        ->assertSee($this->plan->service->name)
        ->assertSee($this->plan->name)
        ->assertSee('QRIS');
});

test('checkout paket tidak aktif menghasilkan 404', function () {
    $inactive = Plan::factory()->inactive()->create();

    $this->actingAs($this->user)
        ->get(route('transaction.checkout', $inactive->id))
        ->assertNotFound();
});

test('checkout membuat transaksi pending lalu mengarahkan ke halaman pembayaran', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Checkout::class, ['plan' => $this->plan->id])
        ->set('paymentMethod', 'qris')
        ->call('checkout')
        ->assertHasNoErrors();

    $transaction = Transaction::query()->where('user_id', $this->user->id)->firstOrFail();

    expect($transaction->status)->toBe(TransactionStatus::Pending)
        ->and($transaction->payment_method)->toBe('qris')
        ->and($transaction->amount)->toBe($this->plan->price)
        ->and($transaction->order_code)->toStartWith('PRIM-');

    $component->assertRedirect(route('transaction.show', $transaction->order_code));
});

test('checkout menolak metode pembayaran yang tidak dikenal', function () {
    Livewire::actingAs($this->user)
        ->test(Checkout::class, ['plan' => $this->plan->id])
        ->set('paymentMethod', 'bitcoin')
        ->call('checkout')
        ->assertHasErrors(['paymentMethod']);

    expect(Transaction::query()->count())->toBe(0);
});

test('pemilih paket dapat mengganti paket dalam layanan yang sama', function () {
    $lain = Plan::query()
        ->where('service_id', $this->plan->service_id)
        ->where('is_active', true)
        ->whereKeyNot($this->plan->id)
        ->first();

    // Sebagian layanan hanya punya satu paket; pengujian dilewati bila demikian.
    if ($lain === null) {
        expect(true)->toBeTrue();

        return;
    }

    Livewire::actingAs($this->user)
        ->test(Checkout::class, ['plan' => $this->plan->id])
        ->assertSet('planId', $this->plan->id)
        ->call('choosePlan', $lain->id)
        ->assertSet('planId', $lain->id)
        ->assertSee($lain->groupLabel());
});

test('pemilih paket menolak paket dari layanan yang berbeda', function () {
    $lainService = Plan::query()
        ->where('service_id', '!=', $this->plan->service_id)
        ->where('is_active', true)
        ->first();

    if ($lainService === null) {
        expect(true)->toBeTrue();

        return;
    }

    Livewire::actingAs($this->user)
        ->test(Checkout::class, ['plan' => $this->plan->id])
        ->call('choosePlan', $lainService->id)
        ->assertSet('planId', $this->plan->id);
});

test('pembayaran berhasil mengubah status dan membuat langganan aktif', function () {
    $transaction = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'amount' => $this->plan->price,
    ]);

    $result = app(TransactionService::class)->pay($transaction, $this->user, 'transfer', true);

    $transaction->refresh();

    expect($result->successful)->toBeTrue()
        ->and($transaction->status)->toBe(TransactionStatus::Paid)
        ->and($transaction->paid_at)->not->toBeNull()
        ->and($transaction->subscription)->not->toBeNull()
        ->and($transaction->subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($transaction->subscription->user_id)->toBe($this->user->id);
});

test('pembayaran gagal tidak membuat langganan', function () {
    $transaction = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    $result = app(TransactionService::class)->pay($transaction, $this->user, 'transfer', false);

    $transaction->refresh();

    expect($result->successful)->toBeFalse()
        ->and($transaction->status)->toBe(TransactionStatus::Failed)
        ->and($transaction->paid_at)->toBeNull()
        ->and($transaction->subscription)->toBeNull();
});

test('transaksi yang sudah dibayar tidak dapat dibayar ulang', function () {
    $transaction = Transaction::factory()->paid()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    $result = app(TransactionService::class)->pay($transaction, $this->user, 'transfer', true);

    expect($result->successful)->toBeFalse()
        ->and($transaction->fresh()->status)->toBe(TransactionStatus::Paid);
});

test('perpanjangan menyambung masa aktif dari langganan yang masih berjalan', function () {
    $transaction = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'amount' => $this->plan->price,
    ]);

    $service = app(TransactionService::class);
    $service->pay($transaction, $this->user, 'transfer', true);

    $first = $transaction->fresh()->subscription;
    $firstEndsAt = $first->ends_at->copy();

    // Beli lagi paket yang sama sebelum langganan pertama berakhir.
    $second = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'amount' => $this->plan->price,
    ]);

    $service->pay($second, $this->user, 'transfer', true);

    $extension = $second->fresh()->subscription;

    expect($extension->id)->not->toBe($first->id)
        ->and($extension->started_at->timestamp)->toBe($firstEndsAt->timestamp)
        ->and($extension->ends_at->greaterThan($firstEndsAt))->toBeTrue();
});

test('langganan pada layanan berbeda tidak disambung', function () {
    $otherPlan = Plan::query()
        ->where('is_active', true)
        ->whereHas('service', fn ($q) => $q->where('id', '!=', $this->plan->service_id))
        ->firstOrFail();

    $service = app(TransactionService::class);

    $first = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'amount' => $this->plan->price,
    ]);
    $service->pay($first, $this->user, 'transfer', true);

    $second = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $otherPlan->id,
        'amount' => $otherPlan->price,
    ]);
    $service->pay($second, $this->user, 'transfer', true);

    $newSubscription = $second->fresh()->subscription;

    expect($newSubscription->started_at->isToday())->toBeTrue();
});

test('perintah kedaluwarsa menandai transaksi pending yang lewat batas waktu', function () {
    $overdue = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'expires_at' => now()->subHour(),
    ]);

    $stillValid = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'expires_at' => now()->addHours(3),
    ]);

    $count = app(TransactionService::class)->expireOverdueTransactions();

    expect($count)->toBe(1)
        ->and($overdue->fresh()->status)->toBe(TransactionStatus::Expired)
        ->and($stillValid->fresh()->status)->toBe(TransactionStatus::Pending);
});

test('pengguna tidak dapat melihat transaksi milik orang lain', function () {
    $other = User::factory()->create();
    $transaction = Transaction::factory()->pending()->create([
        'user_id' => $other->id,
        'plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('transaction.show', $transaction->order_code))
        ->assertForbidden();
});

test('pengguna dapat melihat transaksinya sendiri', function () {
    $transaction = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('transaction.show', $transaction->order_code))
        ->assertOk()
        ->assertSee($transaction->order_code)
        ->assertSee('Simulasikan Berhasil');
});

test('transaksi tidak dikenal menghasilkan 404', function () {
    $this->actingAs($this->user)
        ->get(route('transaction.show', 'PRIM-19700101-XXXXXX'))
        ->assertNotFound();
});

test('tombol simulasi pembayaran berhasil mengaktifkan langganan dan mengarahkan ke struk', function () {
    $transaction = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'amount' => $this->plan->price,
        'payment_method' => 'qris',
    ]);

    Livewire::actingAs($this->user)
        ->test(TransactionDetail::class, ['order' => $transaction->order_code])
        ->call('pay', true)
        ->assertRedirect(route('transaction.receipt', $transaction->order_code));

    expect($transaction->fresh()->status)->toBe(TransactionStatus::Paid);
});

test('riwayat transaksi hanya menampilkan milik pengguna yang masuk', function () {
    $mine = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    $other = User::factory()->create();
    $theirs = Transaction::factory()->pending()->create([
        'user_id' => $other->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(TransactionHistory::class)
        ->assertSee($mine->order_code)
        ->assertDontSee($theirs->order_code);
});

test('riwayat transaksi dapat disaring berdasarkan status', function () {
    $pending = Transaction::factory()->pending()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    $paid = Transaction::factory()->paid()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(TransactionHistory::class)
        ->set('status', TransactionStatus::Paid->value)
        ->assertSee($paid->order_code)
        ->assertDontSee($pending->order_code);
});

test('langganan yang sudah ada diberitahukan pada halaman checkout', function () {
    $existing = Subscription::factory()->create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'status' => SubscriptionStatus::Active,
        'started_at' => now()->subDays(5),
        'ends_at' => now()->addDays(25),
    ]);

    $this->actingAs($this->user)
        ->get(route('transaction.checkout', $this->plan->id))
        ->assertOk()
        ->assertSee('Kamu sudah berlangganan layanan ini');

    expect($existing->isActive())->toBeTrue();
});

test('service transaction dapat dipakai ulang untuk layanan yang berbeda', function () {
    $service = Service::query()->active()->with('plans')->firstOrFail();
    $plan = $service->plans->first();

    $transaction = app(TransactionService::class)->checkout($this->user, $plan, 'ewallet');

    expect($transaction->exists)->toBeTrue()
        ->and($transaction->payment_method)->toBe('ewallet')
        ->and($transaction->expires_at)->not->toBeNull();
});
